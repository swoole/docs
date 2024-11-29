# プログラミングの注意点

このセクションでは、協程プログラミングと同期プログラミングの違い、および注意すべき点について詳しく説明します。

## 注意すべき点

* コードの中で `sleep` やその他の睡眠関数を実行してはいけません。これにより、プロセス全体がブロックされます。協程では [Co::sleep()](/coroutine/system?id=sleep) を使用するか、[一键协程化](/runtime) 后に `sleep` を使用することができます。参考：[sleep/usleepの影響](/getting_started/notice?id=sleepusleepの影響)
* `exit/die` は危険であり、`Worker` プロセスを終了させます。参考：[exit/die関数の影響](/getting_started/notice?id=exitdie函数的影响)
* 致命的なエラーをキャッチするには `register_shutdown_function` を使用できます。プロセスが異常に終了した時にいくつかの清掃作業を行うことができます。参考：[Server运行期致命错误を捕获](/getting_started/notice?id=捕获server运行期致命错误)
* PHPコード中に例外が投げられた場合、回调関数内で `try/catch` を使って例外をキャッチしなければなりません。そうでなければ、ワークプロセスが終了します。参考：[例外とエラーの捕获](/getting_started/notice?id=捕获异常和错误)
* `set_exception_handler`はサポートされておらず、`try/catch`で例外を処理しなければなりません。
* `Worker`プロセスは同じ `Redis` や `MySQL` などのネットワークサービスクライアントを共有してはいけません。`Redis/MySQL`の接続作成に関するコードは `onWorkerStart` コールバック関数に置けるでしょう。参考：[RedisやMySQLの接続を共有できるか](/question/use?id=是否可以共用1个redis或mysql连接)

## 協程プログラミング

`Coroutine` 特性を使用する際は、[協程プログラミングの注意点](/coroutine/notice)をよく読んでください。

## 并発プログラミング

同期ブロックモードとは異なり、`協程`モードではプログラムは**並発して実行**されます。同じ時点で `Server`には複数のリクエストが存在するため、**アプリケーションは各クライアントやリクエストに対して、異なるリソースやコンテキストを作成しなければなりません**。さもなければ、異なるクライアントやリクエスト間でデータや論理的な混乱が生じる可能性があります。

## クラス/関数の重複定義

初心者にはこの間違いを犯しやすいです。Swooleは常駐メモリであるため、クラス/関数定義のファイルをロードした後、解放されません。したがって、クラス/関数を導入するPHPファイルでは、`include_once`または`require_once`を使用しなければならず、そうでなければ`cannot redeclare function/class`という致命的なエラーが発生します。

## メモリ管理

!> Serverやその他の常駐プロセスを書く際は特に注意が必要です。

PHP守护プロセスと通常のWebプログラムの変数のライフサイクル、メモリ管理の方法は全く異なります。Serverが起動した後、メモリ管理の基本原則は通常のphp-cliプログラムと同じです。具体的な内容は、Zend VMのメモリ管理に関する記事を参照してください。

### 局部変数

イベント回调関数が戻った後、すべての局部オブジェクトと変数はすぐにリサイクルされ、`unset`する必要はありません。変数がリソース类型であれば、対応するリソースもPHP底层で解放されます。

```php
function test()
{
	$a = new Object;
	$b = fopen('/data/t.log', 'r+');
	$c = new swoole_client(SWOOLE_SYNC);
	$d = new swoole_client(SWOOLE_SYNC);
	global $e;
	$e['client'] = $d;
}
```

* `$a`, `$b`, `$c`はすべて局部変数であり、この関数が`return`された時、これら3つの変数は直ちに解放され、対応するメモリも直ちに解放され、開いたIOリソースのファイルハンドラは直ちに閉じられます。
* `$d`も局部変数ですが、`return`前にそれをグローバル変数`$e`に保存したため、解放されません。`unset($e['client'])`被执行し、もはや他のPHP変数が`$d`変数を参照していない場合、`$d`は解放されます。

### グローバル変数

PHPには3種類のグローバル変数があります。

* `global`キーワードで宣言された変数
* `static`キーワードで宣言されたクラスの静的な変数、関数の静的な変数
* PHPの超グローバル変数、`$_GET`、`$_POST`、`$GLOBALS`など

グローバル変数やオブジェクト、クラスの静的な変数は、Serverオブジェクト上に保存される変数であり、解放されません。これらの変数やオブジェクトの破壊作業は、プログラマーが自ら行う必要があります。

```php
class Test
{
	static $array = array();
	static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
	Test::$array[] = $fd;
	Test::$string .= $data;
}
```

* イベント回调関数では、非局所変数の`array`タイプの値に特に注意が必要です。一部の操作、例えば `TestClass::$array[] = "string"` などはメモリ漏洩を引き起こす可能性があり、重大な場合はメモリオーバーフローが発生する可能性があります。必要に応じて、大きな配列を清掃することに注意してください。

* イベント回调関数では、非局所変数の文字列を接続する操作には、メモリ漏洩に注意が必要です。例えば `TestClass::$string .= $data` などはメモリ漏洩を引き起こす可能性があり、重大な場合はメモリオーバーフローが発生する可能性があります。

### 解決策

* 同期ブロックでリクエストに応答する無状态的Serverプログラムでは、[max_request](/server/setting?id=max_request)と[task_max_request](/server/setting?id=task_max_request)を設定することができます。Workerプロセス/[Taskプロセス](/learn?id=taskworker进程)が終了したり、タスクの上限に達したりした時にプロセスは自動的に終了し、そのプロセスのすべての変数/オブジェクト/リソースは解放されリサイクルされます。
* プログラム内で`onClose`またはタイマーを設定し、タイムリーに`unset`を使用して変数を清掃し、リソースを回収します。

## プロセス隔離

プロセス隔離は多くの初心者によくある問題です。グローバル変数の値を変更しても効果が現れないのはなぜでしょうか？その理由は、グローバル変数は異なるプロセスにあり、メモリ空間が隔離されているためです。したがって、無効です。

Swooleを使用してServerプログラムを開発する場合、プロセス隔離の問題を理解する必要があります。Swoole\Serverプログラムの異なるWorkerプロセスは隔離されており、プログラミング時にグローバル変数、タイマー、イベントリスナーを操作することは、現在のプロセス内でのみ有効です。

* 異なるプロセス内のPHP変数は共有されません。たとえグローバル変数であっても、プロセスA内でその値を変更しても、プロセスB内では無効です。
* 異なるWorkerプロセス間でデータを共有する必要がある場合は、Redis、MySQL、ファイル、Swoole\Table、APCu、shmgetなどのツールを使用することができます。
* 異なるプロセスのファイルハンドラは隔離されているため、プロセスAで作成されたSocket接続や開いたファイルは、プロセスB内で無効であり、たとえそのfdをプロセスBに送信しても使用できません。

例：

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$i = 1;

$server->on('Request', function ($request, $response) {
	global $i;
    $response->end($i++);
});

$server->start();
```

多プロセスサーバーの中で、`$i`変数はグローバル変数（`global`）ですが、プロセス隔離のためには、たとえ4つのワークプロセスがあったとしても、プロセス1で `$i++`を行った場合、実際にはプロセス1内の `$i`だけが `2`になり、他の3つのプロセス内の `$i` 変数の値は `1`のままです。

正しい方法は、Swooleが提供する[Swoole\Atomic](/memory/atomic)または[Swoole\Table](/memory/table)データ構造を使用してデータを保存することです。上記のコードでは、`Swoole\Atomic`を使用して実現できます。

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$atomic = new Swoole\Atomic(1);

$server->on('Request', function ($request, $response) use ($atomic) {
    $response->end($atomic->add(1));
});

$server->start();
```

!> `Swoole\Atomic`データは共有メモリに基づいて構築されており、`add`方法で `1`を加える時、他のワークプロセス内でも有効です。

Swooleが提供する[Table](/memory/table)、[Atomic](/memory/atomic)、[Lock](/memory/lock)コンポーネントは、多プロセスプログラミングに使用できますが、Server->startの前に作成しなければなりません。また、Serverが維持するTCPクライアント接続も、プロセスを越えて操作できます。例えば、Server->sendとServer->closeです。
## statキャッシュの清除

PHPの基本的な部分では、「stat」システム呼び出しに「Cache」が追加されました。「stat」、「fstat」、「filemtime」などの関数を使用すると、基本的な部分がキャッシュに当たることがあり、歴史的なデータが返される可能性があります。

[clearstatcache](https://www.php.net/manual/en/function.clearstatcache.php)関数を使用して、ファイルの「stat」キャッシュを清除することができます。

## mt_rand乱数

Swooleの中で親プロセス内でmt_randを呼び出した場合、異なる子プロセス内でmt_randを呼び出しても同じ結果が返されるため、各子プロセス内でmt_srandを呼び出して再種まきする必要があります。

!> shuffleやarray_randなどの乱数に依存するPHP関数も同様の影響を受けます  

例：

```php
mt_rand(0, 1);

//開始
$worker_num = 16;

//プロセスfork
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

//非同期でプロセスを実行
function child_async(Swoole\Process $worker) {
    mt_srand(); //再種まき
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
```

## 例外とエラーのキャッチ

###キャッチ可能な例外/エラー

PHPには大まかに3種類のキャッチ可能な例外/エラーがあります。

1. `Error`：PHPカーネルが投げける専門のタイプのエラーで、クラスが存在しない、関数が存在しない、関数のパラメータが間違っているなど、このタイプのエラーが投げられます。PHPコードでは「Errorクラス」を使って例外として投げけるべきではありません。
2. `Exception`：アプリケーション開発者が使用すべき例外の基盤クラスです。
3. `ErrorException`：この例外の基盤クラスは、「Warning」/「Notice」などの情報を「set_error_handler」を通じて例外に変換する責任があります。将来的には、「Warning」/「Notice」をすべて例外に変換する予定です。これにより、PHPプログラムはより良く、より制御可能하게さまざまなエラーを処理できるようになります。

!> 上記のすべてのクラスは「Throwable」インターフェースを実現しており、「try {} catch(Throwable $e) {}」で投げられるすべての例外/エラーをキャッチできます。

例1：
```php
try {
	test();
} 
catch(Throwable $e) {
	var_dump($e);
}
```
例2：
```php
try {
	test();
}
catch (Error $e) {
	var_dump($e);
}
catch(Exception $e) {
	var_dump($e);
}
```

###キャッチ不能な致命的なエラーと例外

PHPのエラーの重要なレベルで、例外/エラーがキャッチされなかった場合、メモリ不足の場合、または一部のコンパイル時エラー（継承されたクラスが存在しない）など、E_ERRORレベルでFatal Errorが投げられます。これはプログラムが遡及不可能なエラーが発生した場合にトリガーされます。PHPプログラムはこのレベルのエラーをキャッチすることはできず、「register_shutdown_function」を通じて後続でいくつかの処理を行うことしかできません。

### 协程での実行時例外/エラーのキャッチ

Swoole4での协程プログラミングでは、ある协程のコードでエラーが発生すると、プロセス全体が終了し、プロセスのすべての协程が実行を停止します。协程のトップレベルで「try/catch」を行い、例外/エラーをキャッチし、出错した协程のみを終了させることができます。

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    //协程1のエラーは协程2に影響を与えません
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
```

### Server実行時の致命的なエラーのキャッチ

Serverが実行時に致命的なエラーが発生すると、クライアントの接続には応答が得られなくなります。例えばWebサーバーでは、致命的なエラーが発生した場合はクライアントにHTTP 500エラー情報を送信する必要があります。

PHPでは、「register_shutdown_function」と「error_get_last」の2つの関数を使用して致命的なエラーをキャッチし、エラー情報をクライアントの接続に送信することができます。

具体的なコード例は以下の通りです：

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    register_shutdown_function(function () use ($response) {
        $error = error_get_last();
        var_dump($error);
        switch ($error['type'] ?? null) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                // log or send:
                // error_log($message);
                // $server->send($fd, $error['message']);
                $response->status(500);
                $response->end($error['message']);
                break;
        }
    });
    exit(0);
});
$http->start();
```

## 使用の影響

### sleep/usleepの影響

非同期IOプログラムでは、「sleep/usleep/time_sleep_until/time_nanosleep」の使用を禁止しています。（以下、「sleep」はすべての睡眠関数を指します）

*「sleep」関数はプロセスを睡眠状態に陥れます。
*指定された時間までオペレーティングシステムが現在のプロセスを再唤醒することはありません。
*「sleep」中は、信号だけが中断することができます。
*Swooleの信号処理は「signalfd」に基づいて実現されているため、信号を送信しても「sleep」を中断することはできません。

Swooleが提供する[Swoole\Event::add](/event?id=add)、[Swoole\Timer::tick](/timer?id=tick)、[Swoole\Timer::after](/timer?id=after)、[Swoole\Process::signal](/process/process?id=signal)は、プロセスが「sleep」した後、動作を停止します。[Swoole\Server](/server/tcp_init)も新しい要求を処理することができません。

#### 例

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    sleep(100);
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> [onReceive](/server/events?id=onreceive)イベントで「sleep」関数を実行すると、Serverは100秒間、クライアントからの要求を一切受け付けられなくなります。

### exit/die関数の影響

Swooleプログラムでは、「exit/die」の使用を禁止しています。PHPコードに「exit/die」がある場合、現在活動している[Workerプロセス](/learn?id=worker进程)、[Taskプロセス](/learn?id=taskworker进程)、[Userプロセス](/server/methods?id=addprocess)、およびSwoole\Processプロセスは直ちに終了します。

「exit/die」を使用すると、Workerプロセスは例外により終了し、masterプロセスによって再び再起動されます。これにより、プロセスが絶えず終了し、再起動し、多くの警告ログが生成されます。

「try/catch」を使用することをお勧めし、「exit/die」を置き換えて、PHP関数呼び出しスタックから中断执行します。

```php
Swoole\Coroutine\run(function () {
    try
    {
        exit(0);
    } catch (Swoole\ExitException $e)
    {
        echo $e->getMessage()."\n";
    }
});
```

!> Swoole\ExitExceptionはSwoole v4.1.0以降に直接サポートされており、协程とServerでPHPの「exit」を使用できます。この時、基層は自動的にキャッチ可能なSwoole\ExitExceptionを投げます。開発者は必要な位置でキャッチし、原生PHPと同じ退出ロジックを実現できます。具体的な使用方法については[退出协程](/coroutine/notice?id=退出协程)を参照してください。

例外処理は「exit/die」よりもフレンドリーです。なぜなら、例外は制御可能であり、「exit/die」は制御不能だからです。最も外層で「try/catch」を行い、例外をキャッチし、現在のタスクのみを終了させることができます。Workerプロセスは新しい要求を処理し続けることができ、「exit/die」はプロセスを直接終了させ、現在プロセスが保存しているすべての変数とリソースが破壊されます。プロセス内で他のタスクがある場合、「exit/die」に遭遇してもすべてを丢弃します。
### whileループの影響

非同期プログラムがデッドロックに遭遇すると、イベントはトリガーされなくなります。非同期IOプログラムは`Reactorモデル`を使用しており、実行中は`reactor->wait`でポーリングしなければなりません。デッドロックに遭遇すると、プログラムのコントロールは`while`ループにあり、`reactor`はコントロールを失い、イベントを検出できなくなります。したがって、IOイベントの回调関数もトリガーされません。

!> 密集計算のコードにはIO操作がないため、ブロッキングとは言えません  

#### 实例プログラム

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactorId, $data) {
    $i = 0;
    while(1)
    {
        $i++;
    }
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> [onReceive](/server/events?id=onreceive)イベントでデッドロックが発生し、`server`はこれ以上のクライアントリクエストを受け取ることができず、ループが終わるのを待って初めて新しいイベントを処理することができます。
