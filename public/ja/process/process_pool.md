```
# Swoole\Process\Pool

プロセスプールは、[Swoole\Server](/server/init)のManagerによって管理されるプロセスモジュールを実現しています。複数のワークプロセスを管理することができます。このモジュールの核心機能はプロセス管理であり、`Process`の実装と比較して、`Process\Pool`はよりシンプルで、封装レベルが高く、開発者は多くのコードを書くことなくプロセス管理機能を実現できます。また、[Co\Server](/coroutine/server?id=完全な例)と組み合わせて使用することで、純粋な協程スタイルの、多核CPUを利用できるサーバープログラムを作成することができます。

## プロセス間通信

`Swoole\Process\Pool`は、プロセス間通信を以下の3つの方法で提供しています：

### メッセージキュー
`Swoole\Process\Pool->__construct`の2番目のパラメータを`SWOOLE_IPC_MSGQUEUE`に設定すると、メッセージキューを使用してプロセス間通信を行います。PHPの`sysvmsg`拡張を使用して情報を投稿することができ、メッセージの最大サイズは`65536`を超えてはなりません。

* **注意**

  * `sysvmsg`拡張を使用して情報を投稿する場合、コンストラクタには必ず`msgqueue_key`を渡す必要があります。
  * Swooleの底层は`sysvmsg`拡張の`msg_send`の2番目のパラメータ`mtype`をサポートしていませんので、任意の非`0`値を渡してください。

### Socket通信
`Swoole\Process\Pool->__construct`の2番目のパラメータを`SWOOLE_IPC_SOCKET`に設定すると、Socket通信を使用します。クライアントとサーバーが同じマシン上にない場合は、この方法を使用して通信することができます。

[Swoole\Process\Pool->listen()](/process/process_pool?id=listen)方法でポートを聴き、[Messageイベント](/process/process_pool?id=on)でクライアントからのデータを受け取り、[Swoole\Process\Pool->write()](/process/process_pool?id=write)方法でクライアントに応答を返すことができます。

Swooleは、クライアントがこの方法でデータを送信する場合、実際のデータの前に4バイト、ネットワーク字节順の長さ値を追加する必要があります。
```php
$msg = 'Hello Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```

### UnixSocket
`Swoole\Process\Pool->__construct`の2番目のパラメータを`SWOOLE_IPC_UNIXSOCK`に設定すると、UnixSocketを使用したプロセス間通信を行います。**この方法を強くお勧めします**。

この方法は比較的シンプルで、[Swoole\Process\Pool->sendMessage()](/process/process_pool?id=sendMessage)方法と[Messageイベント](/process/process_pool?id=on)を使用してプロセス間通信を完了することができます。

また、協程モードを有効にすると、[Swoole\Process\Pool->getProcess()](/process/process_pool?id=getProcess)で`Swoole\Process`オブジェクトを取得し、`Swoole\Process->exportsocket()](/process/process?id=exportsocket)で`Swoole\Coroutine\Socket`オブジェクトを取得し、このオブジェクトを使用してプロセス間通信を行うことができます。ただし、この場合[Messageイベント](/process/process_pool?id=on)を設定することはできません。

!> パラメータと環境設定については、[コンストラクタ](/process/process_pool?id=__construct)と[設定パラメータ](/process/process_pool?id=set)を参照してください。

## 定数

定数 | 説明
---|---
SWOOLE_IPC_MSGQUEUE | システム[メッセージキュー](/learn?id=什么是IPC)通信
SWOOLE_IPC_SOCKET | Socket通信
SWOOLE_IPC_UNIXSOCK | [UnixSocket](/learn?id=什么是IPC)通信(v4.4+)

## 協程サポート

v4.4.0バージョンで協程のサポートが追加されました。参考：[Swoole\Process\Pool->__construct](/process/process_pool?id=__construct)

## 使用例

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** 現在は Worker プロセスです */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n";
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
```

## 方法

### __construct()

コンストラクタです。

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **パラメータ** 

  * **`int $worker_num`**
    * **機能**：ワークプロセスの数を指定します
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $ipc_type`**
    * **機能**：プロセス間通信モード【デフォルトは`SWOOLE_IPC_NONE`で、どのプロセス間通信特性も使用しません】
    * **デフォルト値**：`SWOOLE_IPC_NONE`
    * **その他の値**：`SWOOLE_IPC_MSGQUEUE`、`SWOOLE_IPC_SOCKET`、`SWOOLE_IPC_UNIXSOCK`

    !> -`SWOOLE_IPC_NONE`を設定した場合、`onWorkerStart`回调を必ず設定し、`onWorkerStart`内でループロジックを実現する必要があります。`onWorkerStart`関数退出時にワークプロセスは直ちに終了し、その後Managerプロセスによってプロセスが再起動されます。  
    -`SWOOLE_IPC_MSGQUEUE`を設定すると、システムのメッセージキュー通信を使用し、`$msgqueue_key`で指定されたメッセージキーのKEYを指定できます。メッセージキーのKEYを指定しない場合、プライベートキューが申請されます。  
    -`SWOOLE_IPC_SOCKET`を設定すると、Socket通信を使用します。listen](/process/process_pool?id=listen)方法で聴くアドレスとポートを指定する必要があります。  
    -`SWOOLE_IPC_UNIXSOCK`を設定すると、unixSocket](/learn?id=什么是IPC)通信を使用します。協程モードでのみ使用し、**この方法を強くお勧めします**。具体的な使用方法は以下を参照してください。  
    -非`SWOOLE_IPC_NONE`を設定した場合、必ず`onMessage`回调を設定し、`onWorkerStart`はオプションとなります。

  * **`int $msgqueue_key`**
    * **機能**：メッセージキューの `key`
    * **デフォルト値**：`0`
    * **その他の値**：なし

  * **`bool $enable_coroutine`**
    * **機能**：協程サポートを有効にするかどうか【協程を使用すると`onMessage`回调を設定できません】
    * **デフォルト値**：`false`
    * **その他の値**：`true`

* **協程モード**
    
v4.4.0バージョンで`Process\Pool`モジュールは協程のサポートを追加しました。第4パラメータを`true`に設定することで有効にすることができます。協程を有効にした後、底层は`onWorkerStart`時に自動的に協程と[协程コンテナ](/coroutine/scheduler)を作成し、回调関数内で直接協程関連の`API`を使用できます。例えば：

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

協程を有効にした後、Swooleは`onMessage`イベント回调を設定することを禁止します。プロセス間通信が必要な場合は、2番目に`SWOOLE_IPC_UNIXSOCK`を設定し、[unixSocket](/learn?id=什么是IPC)通信を使用し、その後`$pool->getProcess()->exportSocket()`を使用して[Swoole\Coroutine\Socket](/coroutine_client/socket)オブジェクトを导出し、Workerプロセス間の通信を実現します。例えば：

 ```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> 具体的な使用方法は[Swoole\Coroutine\Socket](/coroutine_client/socket)と[Swoole\Process](/process/process?id=exportsocket)の関連章を参照してください。

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```

### set()

パラメータを設定します。

```php
Swoole\Process\Pool->set(array $settings): void
```


オプションパラメータ|タイプ|機能|デフォルト値
---|---|----|----
enable_coroutine|bool|協程を有効にするかどうか|false
enable_message_bus|bool|メッセージバスを有効にするかどうか。この値が`true`の場合、大きなデータを送信すると、底层ではデータを小さなブロックに分割して送信し、受信も同様です。|false
max_package_size|int|プロセスが受信できる最大データ量を制限します|2 * 1024 * 1024

* **注意**

  * `enable_message_bus`が`true`の場合、`max_package_size`は機能しません。なぜなら、底层ではデータを小さなブロックに分割して送信し、受信も同様だからです。
  * `SWOOLE_IPC_MSGQUEUE`モードでは、`max_package_size`も機能しません。底层では一度に最大65536バイトのデータ量を受け取ることができます。
  * `SWOOLE_IPC_SOCKET`モードでは、`enable_message_bus`が`false`の場合、受信したデータ量が`max_package_size`を超える場合、底层は直ちに接続を中断します。
  * `SWOOLE_IPC_UNIXSOCK`モードでは、`enable_message_bus`が`false`の場合、データが`max_package_size`を超える場合、`max_package_size`を超えるデータは切り取られます。
  * 協程モードを有効にした場合、`enable_message_bus`が`true`でも、`max_package_size`は機能しません。底层ではデータの分割（送信）と結合（受信）を適切に行いますが、そうでなければ`max_package_size`によって受信データ量が制限されます。

!> Swooleバージョン >= v4.4.4 での使用 가능


### on()

プロセスプールのコールバック関数を設定します。

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **パラメータ** 

  * **`string $event`**
    * **機能**：指定されるイベント
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`callable $function`**
    * **機能**：回调関数
    * **デフォルト値**：なし
    * **その他の値**：なし

* **イベント**

  * **onWorkerStart** 子プロセスが開始される

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Poolオブジェクト
  * @param int $workerId   WorkerId現在のワークプロセスの番号、底层では子プロセスに番号を付与します
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} is started\n";
  });
  ```

  * **onWorkerStop** 子プロセスが終了する

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Poolオブジェクト
  * @param int $workerId   WorkerId現在のワークプロセスの番号、底层では子プロセスに番号を付与します
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} stop\n";
  });
  ```

  * **onMessage** メッセージを受信する

  !> 外部からのメッセージを受け取ります。一度の接続で一度だけメッセージを投稿し、PHP-FPMのショートCONNEクションメカニズムに似ています

  ```php
  /**
    * @param \Swoole\Process\Pool $pool Poolオブジェクト
    * @param string $data メッセージデータ内容
   */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
    var_dump($data);
  });
  ```

  !> イベント名は大小写に関係なく、`WorkerStart`、`workerStart`、または`workerstart`は同じです


### listen()

SOCKETを聴きますが、`$ipc_mode = SWOOLE_IPC_SOCKET`でなければ使用できません。

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **パラメータ** 

  * **`string $host`**
    * **機能**：聴くアドレス【TCPと[unixSocket](/learn?id=什么是IPC)の2種類をサポートしています。`127.0.0.1`はTCPアドレスを聴くことを示し、`$port`を指定する必要があります。`unix:/tmp/php.sock`はunixSocket(/learn?id=什么是IPC)アドレスを聴きます】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $port`**
    * **機能**：聴くポート【TCPモードでは必ず指定する必要があります】
    * **デフォルト値**：`0`
    * **その他の値**：なし

  * **`int $backlog`**
    * **機能**：聴くキューの長さ
    * **デフォルト値**：`2048`
    * **その他の値**：なし

* **戻り値**

  * 成功して聴き入れた場合`true`を返します
  * 聴き入れに失敗した場合`false`を返し、swoole_errnoでエラーコードを取得できます。聴き入れに失敗した場合、`start`を呼び出すとすぐに`false`を返します

* **通信プロトコル**

    聴き入れたポートにデータを送信する際、クライアントはデータの前に4バイト、ネットワーク字节順の長さ値を追加する必要があります。プロトコルのフォーマットは以下の通りです：

```php
// $msg 送信したいデータ
$packet = pack('N', strlen($msg)) . $msg;
```

* **使用例**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```


### write()

対端にデータを書き出しますが、`$ipc_mode`が`SWOOLE_IPC_SOCKET`でなければ使用できません。

```php
Swoole\Process\Pool->write(string $data): bool
```

!> この方法はメモリ操作であり、IO消費はありません。データの送信操作は同期して非阻塞IOです

* **パラメータ** 

  * **`string $data`**
### sendMessage()

ターゲットプロセスにデータを送信するには、`$ipc_mode`が`SWOOLE_IPC_UNIXSOCK`でなければ使用できません。

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **引数**

  * **`string $data`**
    * **機能**：送信したいデータ
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $dst_worker_id`**
    * **機能**：ターゲットプロセスのID
    * **デフォルト値**：`0`
    * **その他の値**：なし

* **戻り値**

  * 送信成功で`true`を返す
  * 送信失敗で`false`を返す

* **注意**

  * 送信データが`max_package_size`を超えていて、かつ`enable_message_bus`が`false`の場合、ターゲットプロセスがデータを受け取る際にはデータを切り取ります

```php
<?php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => false, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(2048)


$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => true, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(6000)
```

### start()

ワークプロセスを起動します。

```php
Swoole\Process\Pool->start(): bool
```

!> 起動成功すると、現在のプロセスは`wait`状態になり、ワークプロセスを管理します；  
起動に失敗すると、`false`を返し、`swoole_errno`を使用してエラーコードを取得できます。

* **使用例**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
```

* **プロセス管理**

  * あるワークプロセスが致命的なエラーに遭遇したり、自発的に退出したりした場合、マネージャーはリソースを回収し、ゾンビプロセスが発生するのを防ぎます
  * ワークプロセスが退出した後、マネージャーは自動的に再起動し、新しいワークプロセスを作成します
  * メインプロセスが`SIGTERM`シグナルを受け取ると、新しいプロセスの`fork`を停止し、すべての実行中のワークプロセスを`kill`します
  * メインプロセスが`SIGUSR1`シグナルを受け取ると、実行中のワークプロセスを一つずつ`kill`し、新しいワークプロセスを再起動します

* **シグナル処理**

  *ベースレベルではメインプロセス（マネージャープロセス）のシグナル処理のみが設定されており、`Worker`ワークプロセスのシグナルは設定されていません。シグナルの監視は開発者が自己实现する必要があります。

  - ワークプロセスが非同期モードの場合、[Swoole\Process::signal](/process/process?id=signal)を使用してシグナルを監視してください
  - ワークプロセスが同期モードの場合、`pcntl_signal`と`pcntl_signal_dispatch`を使用してシグナルを監視してください

  ワークプロセスでは`SIGTERM`シグナルを監視する必要があります。メインプロセスがこのプロセスを終了する必要がある場合、このプロセスに`SIGTERM`シグナルを送信します。ワークプロセスが`SIGTERM`シグナルを監視していない場合、ベースレベルは強制的に現在のプロセスを終了し、一部のロジックが損なわれる可能性があります。

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```

### stop()

現在のプロセスのソケットをイベントループから移除し、コーラスを開始した後にのみこの関数に作用します。

```php
Swoole\Process\Pool->stop(): bool
```

### shutdown()

ワークプロセスを終了します。

```php
Swoole\Process\Pool->shutdown(): bool
```

### getProcess()

現在のワークプロセスを取得します。戻り値は[Swoole\Process](/process/process)オブジェクトです。

!> Swooleバージョン >= `v4.2.0`で利用可能

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **引数**

  * **`int $worker_id`**
    * **機能**：指定して `worker`を取得 【オプションで、デフォルトは現在の `worker`】
    * **デフォルト値**：なし
    * **その他の値**：なし

!> `start`の後に、ワークプロセスの`onWorkerStart`またはその他の回调関数内で呼び出す必要があります；  
戻り値の`Process`オブジェクトはシングルのため、ワークプロセス内で`getProcess()`を繰り返し呼び出すと、同じオブジェクトが返されます。

* **使用例**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```

### detach()

プロセスプール内の現在のワークプロセスを管理から離れさせます。ベースレベルは直ちに新しいプロセスを作成し、古いプロセスはデータの処理を停止し、アプリケーション層のコードがライフサイクルを自己管理します。

!> Swooleバージョン >= `v4.7.0`で利用可能

```php
Swoole\Process\Pool->detach(): bool
```
