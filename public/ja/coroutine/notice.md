# コルテ联运用の须知

Swooleの[コルテン](/coroutine)特性を使用する際は、本章节の编程须知をよく読んでください。

## プログラミングパラダイム

* コルテン内部ではグローバル変数の使用を禁止します
* コルテンは`use`キーワードを使用して外部変数を現在のスコープに導入することは禁止し、参照的使用も禁止します
* コルテン間の通信は必ず[チャネル](/coroutine/channel)を使用する必要があります

!> コルテン間の通信では、グローバル変数や現在のスコープへの外部変数の参照を使用しないでください。代わりに`Channel`を使用してください。

* プロジェクトで`zend_execute_ex`または`zend_execute_internal`を拡張して`hook`した場合は、Cスタックに特に注意が必要です。`Co::set](/coroutine/coroutine?id=set)を使用してCスタックのサイズを再設定することができます

!> `hook`したこれらのエントリ関数の後、ほとんどの場合、平坦なPHP指令呼び出しが`C`関数呼び出しに変わり、Cスタックの消費が増加します。

## コルテンの終了

Swooleの低版本では、コルテン内で`exit`を強制的に使用すると、メモリエラーを引き起こし、予期せぬ結果や`coredump`を引き起こす可能性があります。Swooleサービス内で`exit`を使用すると、サービスプロセス全体が終了し、内部のコルテンがすべて異常で終了し、深刻な問題を引き起こします。Swooleは長い間開発者に`exit`の使用を禁止してきましたが、開発者は例外を投げるという非公式な方法を使用し、トップレベルの`catch`で`exit`と同じ終了ロジックを実現することができます

!> v4.2.2バージョン以降は、HTTPサーバーを作成していないスクリプトが現在のコルテンのみがある状況で`exit`で終了することを許可しています

Swoole **v4.1.0**バージョン以降は、直接`コルテン`や`サービスイベントループ`でPHPの`exit`を使用することをサポートしており、この時、基層は自動的にキャッチ可能な`Swoole\ExitException`を投げます。開発者は必要な位置でキャッチし、ネイティブPHPと同じ終了ロジックを実現することができます。

### Swoole\ExitException

`Swoole\ExitException`は`Exception`を継承し、`getStatus`と`getFlags`の2つの新しいメソッドを追加しました:

```php
namespace Swoole;

class ExitException extends \Exception
{
	public function getStatus(): mixed
	public function getFlags(): int
}
```

#### getStatus()

`exit($status)`で終了した時に渡された`status`パラメータを取得し、任意の変数タイプをサポートします。

```php
public function getStatus(): mixed
```

#### getFlags()

`exit`で退出した時の環境情報マスクを取得します。

```php
public function getFlags(): int
```

現在、以下のマスクがあります:

| 定数 | 説明 |
| -- | -- |
| SWOOLE_EXIT_IN_COROUTINE | コルテン内で退出 |
| SWOOLE_EXIT_IN_SERVER | Server内で退出 |

### 使用方法

#### 基本的な使用

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

function route()
{
    controller();
}

function controller()
{
    your_code();
}

function your_code()
{
    Coroutine::sleep(.001);
    exit(1);
}

run(function () {
    try {
        route();
    } catch (\Swoole\ExitException $e) {
        var_dump($e->getMessage());
        var_dump($e->getStatus() === 1);
        var_dump($e->getFlags() === SWOOLE_EXIT_IN_COROUTINE);
    }
});
```

#### ステータスコード付きの退出

```php
use function Swoole\Coroutine\run;

$exit_status = 0;
run(function () {
    try {
        exit(123);
    } catch (\Swoole\ExitException $e) {
        global $exit_status;
        $exit_status = $e->getStatus();
    }
});
var_dump($exit_status);
```

## 例外処理

コルテンプログラミングでは、直接`try/catch`を使用して例外を処理できます。**ただし、必ずコルテン内でキャッチし、他のコルテンで例外をキャッチすることはできません**。

!> 应用層で`throw`された`Exception`だけでなく、基層のいくつかのエラーもキャッチできます。例えば、`function`、`class`、`method`が存在しない場合などです。

### 失敗する例

以下のコードでは、`try/catch`と`throw`が異なるコルテンにあります。コルテン内でこの例外をキャッチすることはできません。コルテンが終了した時、キャッチされていない例外が発見され、致命的なエラーを引き起こします。

```bash
PHP Fatal error:  Uncaught RuntimeException
```

```php
try {
	Swoole\Coroutine::create(function () {
		throw new \RuntimeException(__FILE__, __LINE__);
	});
}
catch (\Throwable $e) {
	echo $e;
}
```

### 正しい例

コルテン内で例外をキャッチします。

```php
function test() {
	throw new \RuntimeException(__FILE__, __LINE__);
}

Swoole\Coroutine::create(function () {
	try {
		test();
	}
	catch (\Throwable $e) {
		echo $e;
	}
});
```

## __get / __set 魔法方法ではコルテンチャージを発生させないでください

理由：[PHP7カーネルの分析を参照](https://github.com/pangudashu/php7-internal/blob/40645cfe087b373c80738881911ae3b178818f11/3/zend_object.md)

> **Note:** クラスに__get()メソッドが存在する場合、オブジェクトをインスタ化してプロパティのメモリを割り当てる時（つまり:properties_table）には、HashTable类型的额外のzvalが割り当てられます。各__get($var)呼び出しでは、入力された$varの名前をこのハッシュテーブルに入れます。この処理の目的は、循環呼び出しを防ぐためです。例えば：
> 
> ***public function __get($var) { return $this->$var; }***
>
> この状況では、__get()呼び出し時に存在しないプロパティにアクセスしているため、__get()メソッド内で再帰呼び出しが発生します。もし要求された$varに対して判断を行わなければ、無限に再帰し続けます。したがって、__get()を呼び出す前に、まず現在の$varが__get()の中でいるかどうかを判断します。もしすでに__get()の中にある場合は、再度__get()を呼び出さず、そうでなければ$varをハッシュテーブルのキーとして挿入し、ハッシュ値を：*guard |= IN_ISSETに設定します。__get()呼び出し完了後、ハッシュ値を：*guard &= ~IN_ISSETに設定します。
>
> このHashTableは__get()のためだけでなく、他の魔法方法にも使用されるため、そのハッシュ値のタイプはzend_longです。異なる魔法方法が異なるビット位置を占有します。次に、すべてのオブジェクトがこのHashTableを割り当てるわけではありません。オブジェクトが作成される際には、***zend_class_entry.ce_flags***が***ZEND_ACC_USE_GUARDS***を含んでいるかどうかによって決定されます。クラスがコンパイルされる際に、__get()、__set()、__unset()、__isset()メソッドが定義されていることが発見された場合、ce_flagsにこのマスクを適用します。

コルテンチャージが外れた後、次の呼び出しは循環呼び出しとして判断され、これはPHPの**特性**によるものです。PHP開発チームとのコミュニケーションの後も、現時点では解決策はありません。

注意：魔法方法の中で、コルテンチャージを引き起こすコードはありませんが、コルテンの優先順位を強制的に設定した後も、魔法方法がコルテンチャージに強制的に切り替わることがあります。

提案：自分で`get`/`set`メソッドを実現し、明示的に呼び出してください。

元の問題リンク：[#2625](https://github.com/swoole/swoole-src/issues/2625)

## 重大なエラー

以下の行動は重大なエラーを引き起こします。

### 複数のコルテンで一つの接続を共有する

同期ブロックプログラムとは異なり、コルテンは並行してリクエストを処理するため、同時に多くのリクエストが処理される可能性があります。一度クライアント接続を共有すると、異なるコルテン間でデータの混在を引き起こす可能性があります。参考：[複数のコルテンでTCP接続を共有する](/question/use?id=client-has-already-been-bound-to-another-coroutine)
### コルテックスを保存するためにクラスの静的な変数/グローバル変数を使用することの危険性

複数のコルテックスが並行して実行されるため、クラスの静的な変数/グローバル変数を使用してコルテックスの上下文内容を保存することはできません。局所変数を使用するのは安全です。なぜなら、局所変数の値は自動的にコルテックスのスタックに保存され、他のコルテックスはコルテックスの局所変数にアクセスできないからです。

#### 間違い例

```php
$server = new Swoole\Http\Server('127.0.0.1', 9501);

$_array = [];
$server->on('request', function ($request, $response) {
    global $_array;
    // request /a（コルテックス 1）
    if ($request->server['request_uri'] == '/a') {
        $_array['name'] = 'a';
        co::sleep(1.0);
        echo $_array['name'];
        $response->end($_array['name']);
    }
    // request /b（コルテックス 2）
    else {
        $_array['name'] = 'b';
        $response->end();
    }
});
$server->start();
```

`2`つの並行なリクエストを開始します。

```shell
curl http://127.0.0.1:9501/a
curl http://127.0.0.1:9501/b
```

* コルテックス `1`ではグローバル変数 `$_array['name']`の値を `a`に設定します
* コルテックス `1`は `co::sleep`を呼び出して停止します
* コルテックス `2`が実行され、`$_array['name']`の値を `b`に設定し、コルテックス `2`を終了します
* その時、タイマーのリターンがあり、基層でコルテックス `1`の実行を再開します。しかし、コルテックス `1`の論理には上下文の依存関係があります。再び `$_array['name']`の値を印刷すると、プログラムは期待される `a`であるべきですが、この値はコルテックス `2`によって変更されており、実際の結果は `b`になります。これにより論理的なエラーが生じます
*同様に、クラスの静的な変数 `Class::$array`、グローバルオブジェクトのプロパティ `$object->array`、その他のスーパグローバル変数 `$GLOBALS`などを使用して、コルテックスプログラムで上下文を保存することは非常に危険です。予期しない行動が起こり得ます。

![](../_images/coroutine/notice-1.png)

#### 正しい例：Contextを使用して上下文を管理する

`Context`クラスを使用してコルテックスの上下文を管理することができます。`Context`クラスでは、`Coroutine::getuid`を使用してコルテックスのIDを取得し、異なるコルテックス間のグローバル変数を隔離し、コルテックスが終了したときに上下文データをクリーンアップします。

```php
use Swoole\Coroutine;

class Context
{
    protected static $pool = [];

    static function get($key)
    {
        $cid = Coroutine::getuid();
        if ($cid < 0)
        {
            return null;
        }
        if(isset(self::$pool[$cid][$key])){
            return self::$pool[$cid][$key];
        }
        return null;
    }

    static function put($key, $item)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            self::$pool[$cid][$key] = $item;
        }

    }

    static function delete($key = null)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            if($key){
                unset(self::$pool[$cid][$key]);
            }else{
                unset(self::$pool[$cid]);
            }
        }
    }
}
```

使用方法：

```php
use Swoole\Coroutine\Context;

$server = new Swoole\Http\Server('127.0.0.1', 9501);

$server->on('request', function ($request, $response) {
    if ($request->server['request_uri'] == '/a') {
        Context::put('name', 'a');
        co::sleep(1.0);
        echo Context::get('name');
        $response->end(Context::get('name'));
        // コルテックスを終了したときにクリーンアップ
        Context::delete('name');
    } else {
        Context::put('name', 'b');
        $response->end();
        // コルテックスを終了したときにクリーンアップ
        Context::delete();
    }
});
$server->start();
```
