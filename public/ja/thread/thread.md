```
# Swoole\Thread <!-- {docsify-ignore-all} -->

6.0 版からマルチスレッドサポートが提供され、マルチプロセスに代わるスレッド APIを使用することができます。マルチプロセスと比較して、Threadはより豊かな並発データコンテナを提供し、ゲームサーバーや通信サーバーの開発に便利です。

- PHPはZTSモードでなければならず、PHPをコンパイルする際には`--enable-zts`を加える必要があります。
- Swooleをコンパイルする際には`--enable-swoole-thread`のコンパイルオプションを加える必要があります。

## リソースの隔離

SwooleのスレッドはNode.js Worker Threadと似ており、サブスレッドでは新しいZendVM環境が作成されます。サブスレッドは親スレッドから何のリソースも継承しませんので、以下の内容は空になり、再作成または設定する必要があります。

- 既に読み込まれたPHPファイルは、再`include/require`して読み込む必要があります。
- `autoload`関数は再登録する必要があります。
- クラス、関数、定数は空になり、PHPファイルを再読み込んで作成する必要があります。
-グローバル変数、例えば`$GLOBALS`、`$_GET/$_POST`などはリセットされます。
- クラスの静的な属性、関数の静的な変数は初期値にリセットされます。
- 一部の`php.ini`オプション、例えば`error_reporting()`はサブスレッドで再設定する必要があります。

## 利用できない機能

マルチスレッドモードでは、以下の特徴はメインメインスレッドでのみ操作でき、サブスレッドでは実行できません：

- `swoole_async_set()`スレッドパラメータを変更する
- `Swoole\Runtime::enableCoroutine()`と`Swoole\Runtime::setHookFlags()`
-シグナルリスナーを設定するのはメインメインスレッドのみで、`Process::signal()`や`Coroutine\System::waitSignal()`はサブスレッドでは使用できません
- 非同期サーバーを作成するのはメインメインスレッドのみで、`Server`、`Http\Server`、`WebSocket\Server`などはサブスレッドでは使用できません

それ以外にも、マルチスレッドモードでRuntime Hookを有効にすると、关闭することができません。

## 致命的なエラー
メインメインスレッドが終了した場合、アクティブなサブスレッドが存在する限り、致命的なエラーが抛出され、退出の状態コードは`200`で、エラー情報如下：
```
Fatal Error: 2 active threads are running, cannot exit safely.
```

## スレッドサポートの有無を確認する

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)`はスレッドセーフティが有効であることを意味します。

```shell
php --ri swoole

swoole
Swoole => enabled
thread => enabled
```

`thread => enabled`はマルチスレッドサポートが有効であることを意味します。

### マルチスレッドの作成
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

// メインメインスレッドはスレッドパラメータがなく、$argsはnullです
if (empty($args)) {
    # メインメインスレッド
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # サブスレッド
    echo "Thread #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```

### スレッド + サーバー（非同期スタイル）

- 全てのワークプロセスはスレッドで実行され、`Worker`、`Task Worker`、`User Process`を含みます。

- `SWOOLE_THREAD`実行モードが追加され、有効にするとプロセスではなくスレッドで実行されます。

- [bootstrap](/server/setting?id=bootstrap)と[init_arguments](/server/setting?id=init_arguments)の2つの設定が追加され、ワークスレッドの入口スクリプトファイル、スレッド共有データを設定するために使用されます。
- サーバーはメインメインスレッドで作成しなければならず、回调関数内で新しいスレッドを作成して他のタスクを実行することができます。
- `Server::addProcess()`プロセスオブジェクトは標準入力出力の重定向をサポートしていません。

```php
use Swoole\Process;
use Swoole\Thread;
use Swoole\Http\Server;

$http = new Server("0.0.0.0", 9503, SWOOLE_THREAD);
$http->set([
    'worker_num' => 2,
    'task_worker_num' => 3,
    'bootstrap' => __FILE__,
    // init_argumentsを通じてスレッド間のデータ共有を実現します。
    'init_arguments' => function () use ($http) {
        $map = new Swoole\Thread\Map;
        return [$map];
    }
]);

$http->on('Request', function ($req, $resp) use ($http) {
    $resp->end('hello world');
});

$http->on('pipeMessage', function ($http, $srcWorkerId, $msg) {
    echo "[worker#" . $http->getWorkerId() . "]\treceived pipe message[$msg] from " . $srcWorkerId . "\n";
});

$http->addProcess(new Process(function () {
   echo "user process, id=" . Thread::getId();
   sleep(2000);
}));

$http->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
    var_dump($taskId, $srcWorkerId, $data);
    return ['result' => uniqid()];
});

$http->on('Finish', function ($server, $taskId, $data) {
    var_dump($taskId, $data);
});

$http->on('WorkerStart', function ($serv, $wid) {
    // Swoole\Thread::getArguments()を通じて設定されたinit_argumentsで受け取れる共有データ
    var_dump(Thread::getArguments(), $wid);
});

$http->on('WorkerStop', function ($serv, $wid) {
    var_dump('stop: T' . Thread::getId());
});

$http->start();
```
