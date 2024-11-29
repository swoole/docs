# コルテナAPI

> コルテナの基本的な概念を理解した後にこのセクションを読むことをお勧めします。


## 方法


### set()

コルテナの設定を行い、コルテナ関連のオプションを設定します。

```php
Swoole\Coroutine::set(array $options);
```


パラメータ | このバージョン以降安定 | 効果 
---|---|---
max_coroutine | - |グローバルで最大のコルテナ数を設定し、制限を超えた後は基層では新しいコルテナを生成できなくなります。Server下では[server->max_coroutine](/server/setting?id=max_coroutine)が上書きされます。
stack_size/c_stack_size | - | 個々のコルテナの初期Cスタックのメモリサイズを設定します。デフォルトは2Mです。
log_level | v4.0.0 | ログレベル [詳細は](/consts?id=ログレベル)を参照してください。
trace_flags | v4.0.0 | トraceタグ [詳細は](/consts?id=トレースタグ)を参照してください。
socket_connect_timeout | v4.2.10 | 接続建立タイムアウト時間を設定します。**[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)**を参照してください。
socket_read_timeout | v4.3.0 | 読み取りタイムアウト時間を設定します。**[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)**を参照してください。
socket_write_timeout | v4.3.0 | 书込みタイムアウト時間を設定します。**[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)**を参照してください。
socket_dns_timeout | v4.4.0 | Domain Name Resolutionタイムアウト時間を設定します。**[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)**を参照してください。
socket_timeout | v4.2.10 | 送信/受信タイムアウト時間を設定します。**[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)**を参照してください。
dns_cache_expire | v4.2.11 | swoole dnsキャッシュの有効期限を秒で設定します。デフォルトは60秒です。
dns_cache_capacity | v4.2.11 | swoole dnsキャッシュの容量を設定します。デフォルトは1000です。
hook_flags | v4.4.0 | コルテナ化を一度に実行するhookの範囲を構成します。[一键协程化](/runtime)を参照してください。
enable_preemptive_scheduler | v4.4.0 | コルテナの先行採取スケジュールを有効に設定し、コルテナの最大実行時間を10msに設定します。[ini設定](/other/config)を上書きします。
dns_server | v4.5.0 | dns查询のために使用するserverを設定します。デフォルトは"8.8.8.8"です。
exit_condition | v4.5.0 | `callable`を渡し、boolを返すことができます。これによりreactorの退出条件をカスタマイズできます。例えば、コルテナの数が0になることを希望する場合は、`Co::set(['exit_condition' => function () {return Co::stats()['coroutine_num'] === 0;}]);`と書くことができます。
enable_deadlock_check | v4.6.0 | コルテナのデッドロック検出を有効に/無効に設定します。デフォルトは有効です。
deadlock_check_disable_trace | v4.6.0 | コルテナのデッドロック検出のスタックフレームを出力するかどうかに設定します。
deadlock_check_limit | v4.6.0 | コルテナのデッドロック検出時に最大出力数を制限します。
deadlock_check_depth | v4.6.0 | コルテナのデッドロック検出時に戻されるスタックフレームの数を制限します。
max_concurrency | v4.8.2 | 最大同時実行リクエスト数を設定します。


### getOptions()

設定されたコルテナ関連のオプションを取得します。

!> Swooleバージョン >= `v4.6.0` 用

```php
Swoole\Coroutine::getOptions(): null|array;
```


### create()

新しいコルテナを作成し、直ちに実行します。

```php
Swoole\Coroutine::create(callable $function, ...$args): int|false
go(callable $function, ...$args): int|false // php.iniのuse_shortname設定を参照してください。
```

* **パラメータ**

    * **`callable $function`**
      * **機能**：コルテナが実行するコードでなければなりません。`callable`でなければならず、システムが生成できるコルテナの総数は[server->max_coroutine](/server/setting?id=max_coroutine)の設定によって制限されます。
      * **デフォルト値**：なし
      * **その他の値**：なし

* **戻り値**

    * 生成に失敗した場合は`false`を返します。
    * 生成に成功した場合はコルテナの`ID`を返します。

!> 基層は子コルテナのコードを優先して実行するため、子コルテナが挂起している場合にのみ、`Coroutine::create`が返回し、現在のコルテナのコードの実行を続けます。

  * **実行順序**

    コルテナ内で`go`を使用して新しいコルテナを嵌套して作成します。Swooleのコルテナはプロセス内単一スレッドモデルであるため：

    * `go`を使用して作成された子コルテナは優先して実行され、子コルテナが実行終了したり挂起したりすると、再び親コルテナに戻り、下位のコードを実行します。
    * 子コルテナが挂起した後、親コルテナが退出しても、子コルテナの実行に影響しません。

    ```php
    \Co\run(function() {
        go(function () {
            Co::sleep(3.0);
            go(function () {
                Co::sleep(2.0);
                echo "co[3] end\n";
            });
            echo "co[2] end\n";
        });

        Co::sleep(1.0);
        echo "co[1] end\n";
    });
    ```

* **コルテナのコスト**

  各コルテナは相互に独立しており、個別のメモリ空間（スタックメモリ）が必要です。PHP-7.2バージョンでは、基層はコルテナの変数を保存するために8Kの`stack`を割り当てます。`zval`のサイズは16Byteであり、したがって8Kの`stack`は最大で512個の変数を保存できます。コルテナのスタックメモリの使用量が8Kを超えると、ZendVMは自動的に拡張します。

  コルテナが退出すると、申請された`stack`メモリが解放されます。

  * PHP-7.1、PHP-7.0ではデフォルトで256Kのスタックメモリが割り当てられます。
  * `Co::set(['stack_size' => 4096])`を呼び出すことで、デフォルトのスタックメモリサイズを変更できます。



### defer()

`defer`はリソースの解放に使用され、**コルテナが閉じる前**(つまりコルテナ関数が実行される終了時)に呼び出されます。たとえ例外が発生しても、登録された`defer`は実行されます。

!> Swooleバージョン >= 4.2.9

```php
Swoole\Coroutine::defer(callable $function);
defer(callable $function); // 短名API
```

!> 注意すべき点は、呼び出し順序が逆であることです（後進先出）。つまり、最初に登録されたものが最後に実行され、これはリソース解放の正しい論理に合致しています。先に申請されたリソースを解放すると、後に申請されたリソースが解放されにくくなります。

  * **例**

```php
go(function () {
    defer(function () use ($db) {
        $db->close();
    });
});
```


### exists()

指定されたコルテナが存在するかどうかを判断します。

```php
Swoole\Coroutine::exists(int $cid = 0): bool
```

!> Swooleバージョン >= v4.3.0

  * **例**

```php
\Co\run(function () {
    go(function () {
        go(function () {
            Co::sleep(0.001);
            var_dump(Co::exists(Co::getPcid())); // 1: true
        });
        go(function () {
            Co::sleep(0.003);
            var_dump(Co::exists(Co::getPcid())); // 3: false
        });
        Co::sleep(0.002);
        var_dump(Co::exists(Co::getPcid())); // 2: false
    });
});
```


### getCid()

現在のコルテナのユニークな`ID`を取得します。その別名は`getuid`であり、プロセス内で唯一の正の整数です。

```php
Swoole\Coroutine::getCid(): int
```

* **戻り値**

    * 成功した場合は現在のコルテナ `ID`を返します。
    * 現在コルテナ環境にない場合は`-1`を返します。
### getPcid()

現在の協程の親`ID`を取得します。

```php
Swoole\Coroutine::getPcid([$cid]): int
```

!> Swooleバージョン >= v4.3.0

* **パラメータ**

    * **`int $cid`**
      * **機能**：協程 cid，パラメータは省略可能で、ある協程の`id`を渡すことでその親`id`を取得できます
      * **デフォルト値**：現在の協程
      * **その他の値**：なし

  * **例**

```php
var_dump(Co::getPcid());
\Co\run(function () {
    var_dump(Co::getPcid());
    go(function () {
        var_dump(Co::getPcid());
        go(function () {
            var_dump(Co::getPcid());
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
        });
        var_dump(Co::getPcid());
    });
    var_dump(Co::getPcid());
});
var_dump(Co::getPcid());

// --EXPECT--

// bool(false)
// int(-1)
// int(1)
// int(2)
// int(3)
// int(3)
// int(3)
// int(1)
// int(-1)
// bool(false)
```

!> 非嵌套协程での`getPcid`呼び出しは`-1`を返す (非協程空間で作成された)  
非協程内で`getPcid`を呼び出すと`false`を返す (親協程がない)  
`0`は予約された`id`として、戻り値には現れない

!> 協程間には実質的な持続的な親子関係はなく、協程は相互に隔離され、独立して動作する。この`Pcid`は、現在の協程を作成した協程の`id`と理解できる

  * **用途**

    * **複数の協程呼び出しスタックを串串联ねる**

```php
\Co\run(function () {
    go(function () {
        $ptrace = Co::getBackTrace(Co::getPcid());
        // balababala
        var_dump(array_merge($ptrace, Co::getBackTrace(Co::getCid())));
    });
});
```


### getContext()

現在の協程の上下文オブジェクトを取得します。

```php
Swoole\Coroutine::getContext([int $cid = 0]): Swoole\Coroutine\Context
```

!> Swooleバージョン >= v4.3.0

* **パラメータ**

    * **`int $cid`**
      * **機能**：協程 `CID`，オプションパラメータ
      * **デフォルト値**：現在の協程 `CID`
      * **その他の値**：なし

  * **役割**

    * 協程が退出した後、上下文は自動的に清掃されます (他の協程やグローバル変数の参照がない場合)
    * `defer`の登録や呼び出しのコストはありません (清掃方法を登録したり、関数を呼び出して清掃する必要はありません)
    * PHP配列実装の上下文のハッシュ計算コストはありません (協程の数が多い場合に一定の利点があります)
    * `Co\Context`は`ArrayObject`を使用しており、さまざまなストレージニーズを満たします (オブジェクトであると同時に、配列として操作することもできます)

  * **例**

```php
function func(callable $fn, ...$args)
{
    go(function () use ($fn, $args) {
        $fn(...$args);
        echo 'Coroutine#' . Co::getCid() . ' exit' . PHP_EOL;
    });
}

/**
* 低いバージョンのための互換性
* @param object|Resource $object
* @return int
*/
function php_object_id($object)
{
    static $id = 0;
    static $map = [];
    $hash = spl_object_hash($object);
    return $map[$hash] ?? ($map[$hash] = ++$id);
}

class Resource
{
    public function __construct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' constructed' . PHP_EOL;
    }

    public function __destruct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' destructed' . PHP_EOL;
    }
}

$context = new Co\Context();
assert($context instanceof ArrayObject);
assert(Co::getContext() === null);
func(function () {
    $context = Co::getContext();
    assert($context instanceof Co\Context);
    $context['resource1'] = new Resource;
    $context->resource2 = new Resource;
    func(function () {
        Co::getContext()['resource3'] = new Resource;
        Co::yield();
        Co::getContext()['resource3']->resource4 = new Resource;
        Co::getContext()->resource5 = new Resource;
    });
});
Co::resume(2);

Swoole\Event::wait();

// --EXPECT--
// Resource#1 constructed
// Resource#2 constructed
// Resource#3 constructed
// Coroutine#1 exit
// Resource#2 destructed
// Resource#1 destructed
// Resource#4 constructed
// Resource#5 constructed
// Coroutine#2 exit
// Resource#5 destructed
// Resource#3 destructed
// Resource#4 destructed
```


### yield()

現在の協程の実行権を手動で譲ります。これはIOに基づく[協程スケジュール](/coroutine?id=協程スケジュール)ではありません。

この方法は別の別名を持っています：`Coroutine::suspend()`

!> `Coroutine::resume()`方法与 함께使用しなければなりません。この協程が`yield`した後、他の外部の協程によって`resume`されなければ、協程の漏れが発生し、挂起された協程は決して実行されません。

```php
Swoole\Coroutine::yield();
```

  * **例**

```php
$cid = go(function () {
    echo "co 1 start\n";
    Co::yield();
    echo "co 1 end\n";
});

go(function () use ($cid) {
    echo "co 2 start\n";
    Co::sleep(0.5);
    Co::resume($cid);
    echo "co 2 end\n";
});
Swoole\Event::wait();
```


### resume()

手動で特定の協程を再開させ、それを実行させます。これはIOに基づく[協程スケジュール](/coroutine?id=協程スケジュール)ではありません。

!>現在の協程が挂起状態の場合、別の協程内で`resume`を使用して現在の協程を再び呼び覚ますことができます

```php
Swoole\Coroutine::resume(int $coroutineId);
```

* **パラメータ**

    * **`int $coroutineId`**
      * **機能**：再開したい協程の`ID`
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **例**

```php
$id = go(function(){
    $id = Co::getuid();
    echo "start coro $id\n";
    Co::suspend();
    echo "resume coro $id @1\n";
    Co::suspend();
    echo "resume coro $id @2\n";
});
echo "start to resume $id @1\n";
Co::resume($id);
echo "start to resume $id @2\n";
Co::resume($id);
echo "main\n";
Swoole\Event::wait();

// --EXPECT--
// start coro 1
// start to resume 1 @1
// resume coro 1 @1
// start to resume 1 @2
// resume coro 1 @2
// main
```


### list()

現在のプロセス内のすべての協程を列挙します。

```php
Swoole\Coroutine::list(): Swoole\Coroutine\Iterator
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> `v4.3.0`以下のバージョンでは`listCoroutines`を使用する必要があり、新しいバージョンではその方法の名前を短縮し、`listCoroutines`を別名としました。`list`は`v4.1.0`またはそれ以上のバージョンで利用できます。

* **戻り値**

    * 迭代器が返され、`foreach`で列挙したり、`iterator_to_array`で配列に変換することができます

```php
$coros = Swoole\Coroutine::listCoroutines();
foreach($coros as $cid)
{
    var_dump(Swoole\Coroutine::getBackTrace($cid));
}
```


### stats()

協程の状態を取得します。

```php
Swoole\Coroutine::stats(): array
```

* **戻り値**


key | 役割
---|---
event_num |現在のreactorイベント数
signal_listener_num |現在登録されているシグナルリスナーの数
aio_task_num |非同期IOタスク数 (ここでのaioはファイルIOやdnsを指し、その他のネットワークIOは含まれません)
aio_worker_num |非同期IOワークスレッド数
c_stack_size |各協程のCスタックサイズ
coroutine_num |現在実行中の協程数
coroutine_peak_num |現在実行中の協程数のピーク
coroutine_last_cid |最後に作成された協程のid

  * **例**

```php
var_dump(Swoole\Coroutine::stats());

array(1) {
  ["c_stack_size"]=>
  int(2097152)
  ["coroutine_num"]=>
  int(132)
  ["coroutine_peak_num"]=>
  int(2)
}
```
### getBackTrace()

協程関数の呼び出しスタックを取得します。

```php
Swoole\Coroutine::getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
```

!> Swooleバージョン >= v4.1.0

* **パラメータ**

    * **`int $cid`**
      * **機能**：協程の `CID`
      * **デフォルト値**：現在の協程 `CID`
      * **その他の値**：なし

    * **`int $options`**
      * **機能**：オプションを設定する
      * **デフォルト値**：`DEBUG_BACKTRACE_PROVIDE_OBJECT` 【`object`の索引を埋めるかどうかの設定】
      * **その他の値**：`DEBUG_BACKTRACE_IGNORE_ARGS` 【引数（関数/メソッドのすべてを含む）を無視し、メモリ使用量を節約する】

    * **`int limit`**
      * **機能**：戻されるスタックフレームの数を制限する
      * **デフォルト値**：`0`
      * **その他の値**：なし

* **戻り値**

    * 指定された協程が存在しない場合、`false`を返す
    * 成功した場合は、[debug_backtrace](https://www.php.net/manual/zh/function.debug-backtrace.php) 関数の戻り値と同じ形式の配列を返す

  * **例**

```php
function test1() {
    test2();
}

function test2() {
    while(true) {
        Co::sleep(10);
        echo __FUNCTION__." \n";
    }
}
\Co\run(function () {
    $cid = go(function () {
        test1();
    });

    go(function () use ($cid) {
        while(true) {
            echo "BackTrace[$cid]:\n-----------------------------------------------\n";
            //配列を返すが、出力は自分でフォーマットする必要がある
            var_dump(Co::getBackTrace($cid))."\n";
            Co::sleep(3);
        }
    });
});
Swoole\Event::wait();
```


### printBackTrace()

協程関数の呼び出しスタックを印刷します。パラメータは`getBackTrace`と同じです。

!> Swooleバージョン >= `v4.6.0` 有効

```php
Swoole\Coroutine::printBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0);
```


### getElapsed()

協程の運用時間を取得し、分析統計やゾンビ協程の発見に使用します。

!> Swooleバージョン >= `v4.5.0` 有効

```php
Swoole\Coroutine::getElapsed([$cid]): int
```
* **パラメータ**

    * **`int $cid`**
      * **機能**：オプションで、協程の `CID`
      * **デフォルト値**：現在の協程 `CID`
      * **その他の値**：なし

* **戻り値**

    * 協程が運用した時間の浮点数、ミリ秒精度です。


### cancel()

特定の協程をキャンセルしますが、現在の協程にはキャンセル操作を发起することはできません。

!> Swooleバージョン >= `v4.7.0` 有効

```php
Swoole\Coroutine::cancel($cid): bool
```
* **パラメータ**

    * **`int $cid`**
        * **機能**：協程の `CID`
        * **デフォルト値**：なし
        * **その他の値**：なし

* **戻り値**

    * 成功した場合は `true` を返し、失敗した場合は `false` を返します。
    *キャンセルに失敗した場合は、[swoole_last_error()](/functions?id=swoole_last_error) を使ってエラー情報を確認することができます。


### isCanceled()

現在の操作が手動でキャンセルされたかどうかを判断します。

!> Swooleバージョン >= `v4.7.0` 有効

```php
Swoole\Coroutine::isCanceled(): bool
```

* **戻り値**

    * 手動でキャンセルされた正常に終了した場合は `true`を返し、失敗した場合は `false`を返します。

#### 例

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

run(function () {
    $chan = new Coroutine\Channel(1);
    $cid = Coroutine::getCid();
    go(function () use ($cid) {
        System::sleep(0.002);
        assert(Coroutine::cancel($cid) === true);
    });

    assert($chan->push("hello world [1]", 100) === true);
    assert(Coroutine::isCanceled() === false);
    assert($chan->errCode === SWOOLE_CHANNEL_OK);

    assert($chan->push("hello world [2]", 100) === false);
    assert(Coroutine::isCanceled() === true);
    assert($chan->errCode === SWOOLE_CHANNEL_CANCELED);

    echo "Done\n";
});
```


### enableScheduler()

一時的に協程の先行调度を有効にします。

!> Swooleバージョン >= `v4.4.0` 有効

```php
Swoole\Coroutine::enableScheduler();
```


### disableScheduler()

一時的に協程の先行调度を無効にします。

!> Swooleバージョン >= `v4.4.0` 有効

```php
Swoole\Coroutine::disableScheduler();
```


### getStackUsage()

現在のPHPスタックのメモリ使用量を取得します。

!> Swooleバージョン >= `v4.8.0` 有効

```php
Swoole\Coroutine::getStackUsage([$cid]): int
```

* **パラメータ**

    * **`int $cid`**
        * **機能**：オプションで、協程の `CID`
        * **デフォルト値**：現在の協程 `CID`
        * **その他の値**：なし


### join()

複数の協程を並行して実行します。

!> Swooleバージョン >= `v4.8.0` 有効

```php
Swoole\Coroutine::join(array $cid_array, float $timeout = -1): bool
```

* **パラメータ**

    * **`array $cid_array`**
        * **機能**：実行したい協程の `CID` 数组
        * **デフォルト値**：なし
        * **その他の値**：なし

    * **`float $timeout`**
        * **機能**：全体的なタイムアウト時間であり、タイムアウト後は直ちに戻ります。しかし、実行中の協程は最後まで実行され、中止されません。
        * **デフォルト値**：-1
        * **その他の値**：なし

* **戻り値**

    * 成功した場合は `true` を返し、失敗した場合は `false` を返します。
    * 取消に失敗した場合は、[swoole_last_error()](/functions?id=swoole_last_error) を使ってエラー情報を確認することができます。

* **使用例**

```php
use Swoole\Coroutine;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

run(function () {
    $status = Coroutine::join([
        go(function () use (&$result) {
            $result['baidu'] = strlen(file_get_contents('https://www.baidu.com/'));
        }),
        go(function () use (&$result) {
            $result['google'] = strlen(file_get_contents('https://www.google.com/'));
        })
    ], 1);
    var_dump($result, $status, swoole_strerror(swoole_last_error(), 9));
});
```


## 関数


### batch()

複数の協程を並行して実行し、配列を通じてこれらの協程の戻り値を取得します。

!> Swooleバージョン >= `v4.5.2` 有効

```php
Swoole\Coroutine\batch(array $tasks, float $timeout = -1): array
```

* **パラメータ**

    * **`array $tasks`**
      * **機能**：回调関数の配列を渡す。`key`が指定されている場合、戻り値もその`key`で指向される
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：全体的なタイムアウト時間であり、タイムアウト後は直ちに戻ります。しかし、実行中の協程は最後まで実行され、中止されません。
      * **デフォルト値**：-1
      * **その他の値**：なし

* **戻り値**

    * `key`が指定されている場合、回调の戻り値もその`key`で指向される配列を返します。

* **使用例**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\batch;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = batch([
        'file_put_contents' => function () {
            return file_put_contents(__DIR__ . '/greeter.txt', "Hello,Swoole.");
        },
        'gethostbyname' => function () {
            return gethostbyname('localhost');
        },
        'file_get_contents' => function () {
            return file_get_contents(__DIR__ . '/greeter.txt');
        },
        'sleep' => function () {
            sleep(1);
            return true; // 0.1秒のタイムアウトを超えたため、NULLを返しますが、タイムアウト後に実行中の協程は最後まで実行されます。
        },
        'usleep' => function () {
            usleep(1000);
            return true;
        },
    ], 0.1);
    $use = microtime(true) - $use;
    echo "Use {$use}s, Result:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Use {$end_time}s, Done\n";
```
### parallel()

複数の协程を並行して実行します。

!> Swooleバージョン >= `v4.5.3` での利用が推奨されます

```php
Swoole\Coroutine\parallel(int $n, callable $fn): void
```

* **引数**

    * **`int $n`**
      * **機能**：最大协程数を`$n`に設定します
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`callable $fn`**
      * **機能**：実行したい回调関数です
      * **デフォルト値**：なし
      * **その他の値**：なし

* **使用例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\parallel;

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = [];
    parallel(2, function () use (&$results) {
        System::sleep(0.2);
        $results[] = System::gethostbyname('localhost');
    });
    $use = microtime(true) - $use;
    echo "Use {$use}s, Result:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Use {$end_time}s, Done\n";
```

### map()

[array_map](https://www.php.net/manual/zh/function.array-map.php)と同様に、配列の各要素に回调関数を適用します。

!> Swooleバージョン >= `v4.5.5` での利用が推奨されます

```php
Swoole\Coroutine\map(array $list, callable $fn, float $timeout = -1): array
```

* **引数**

    * **`array $list`**
      * **機能**：`$fn`関数を適用する配列です
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`callable $fn`**
      * **機能**：`$list`配列の各要素に適用する回调関数です
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：全体的なタイムアウト時間です。タイムアウトするとすぐに戻りますが、進行中の协程は完了し続けるため、中断しません。
      * **デフォルト値**：-1
      * **その他の値**：なし

* **使用例**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\map;

function fatorial(int $n): int
{
    return array_product(range($n, 1));
}

Coroutine\run(function () {
    $results = map([2, 3, 4], 'fatorial'); 
    print_r($results);
});
```

### deadlock_check()

协程の死锁をチェックし、呼び出された时会に関連するスタック情報を出力します。

デフォルトでは**有効**で、EventLoopが終了した後、协程の死锁が存在する場合は、自動的に呼び出されます。

Coroutine::setでの`enable_deadlock_check`の設定を`false`にすることで、チェックをオフにすることができます。

!> Swooleバージョン >= `v4.6.0` での利用が推奨されます

```php
Swoole\Coroutine\deadlock_check();
```
