# 方法
## __construct()

非同期IOのTCPサーバーオブジェクトを作成します。

```php
Swoole\Server::__construct(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```

  * **パラメータ**

    * `string $host`

      * 機能：监聽するIPアドレスを指定します。
      * デフォルト値：なし。
      * その他：なし。

      !> IPv4では `127.0.0.1` を使用すると本機を监聽し、`0.0.0.0` を使用するとすべてのアドレスを监聽します。
      IPv6では `::1` を使用すると本機を监聽し、`::` (相当于 `0:0:0:0:0:0:0:0`) を使用するとすべてのアドレスを监聽します。

    * `int $port`

      * 機能：监听するポート番号を指定します。例えば `9501`。
      * デフォルト値：なし。
      * その他：なし。

      !> `$sockType`の値が [UnixSocket Stream/Dgram](/learn?id=什么是IPC) の場合、このパラメータは無視されます。
      `1024` 以下のポートを监听するには `root`権限が必要です。
      このポートが占有されていると `server->start` 时に失敗します。

    * `int $mode`

      * 機能：運用モードを指定します。
      * デフォルト値：[SWOOLE_PROCESS](/learn?id=swoole_process) 多プロセスモード（デフォルト）。
      * その他：[SWOOLE_BASE](/learn?id=swoole_base) 基本モード、[SWOOLE_THREAD](/learn?id=swoole_thread) 多スレッドモード（Swoole 6.0で使用可能）。

      ?> `SWOOLE_THREAD`モードでは、ここで [スレッド + サーバー端（非同期スタイル）](/thread/thread?id=スレッド-サーバー端（非同期スタイル）) 查看多スレッドモードでのサーバー端の作成方法です。

      !> Swoole5から、運用モードのデフォルト値は `SWOOLE_BASE` です。

    * `int $sockType`

      * 機能：このサーバーグループのタイプを指定します。
      * デフォルト値：なし。
      * その他：
        * `SWOOLE_TCP/SWOOLE_SOCK_TCP` tcp ipv4 socket
        * `SWOOLE_TCP6/SWOOLE_SOCK_TCP6` tcp ipv6 socket
        * `SWOOLE_UDP/SWOOLE_SOCK_UDP` udp ipv4 socket
        * `SWOOLE_UDP6/SWOOLE_SOCK_UDP6` udp ipv6 socket
        * [SWOOLE_UNIX_DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) unix socket dgram
        * [SWOOLE_UNIX_STREAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/stream_server.php) unix socket stream 

      !> `$sock_type` | `SWOOLE_SSL` を使用すると `SSL` トンネル暗号化を有効にすることができます。`SSL`を有効にした後は設定が必要です。[ssl_key_file](/server/setting?id=ssl_cert_file) 和 [ssl_cert_file](/server/setting?id=ssl_cert_file)

  * **例**

```php
$server = new \Swoole\Server($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP);

// UDP/TCPを混在して使用し、内網と外網のポートを同時に监听し、多ポート监听については addlistener 小節を参照してください。
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP); // TCPを追加
$server->addlistener("192.168.1.100", 9503, SWOOLE_SOCK_TCP); // Web Socketを追加
$server->addlistener("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDPを追加
$server->addlistener("/var/run/myserv.sock", 0, SWOOLE_UNIX_STREAM); // UnixSocket Streamを追加
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP | SWOOLE_SSL); // TCP + SSLを追加

$port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP); // システムがランダムに割り当てるポート番号を戻し、ランダムに割り当てられたポート番号を返します。
echo $port->port;
```
## set()

運用中の各種パラメータを設定するために使用されます。サーバーが起動した後、`$serv->setting`を通じて `Server->set` 方法で設定されたパラメータ配列にアクセスできます。

```php
Swoole\Server->set(array $setting): void
```

!> `Server->set`は `Server->start`の前に呼び出さなければならず、具体的な各設定の意味は[この節](/server/setting)を参照してください。

  * **例**

```php
$server->set(array(
    'reactor_num'   => 2,     // スレッド数
    'worker_num'    => 4,     // プロセス数
    'backlog'       => 128,   // Listenキューの長さを設定
    'max_request'   => 50,    // 各プロセスが受け入れる最大リクエスト数
    'dispatch_mode' => 1,     // データパケットの配布戦略
));
```
## on()

`Server`のイベント回调関数を登録します。

```php
Swoole\Server->on(string $event, callable $callback): bool
```

!> `on`方法を繰り返し呼び出すと、前の設定が上書きされます。

!> PHP 8.2から、動的プロパティを直接設定することはサポートされなくなりました。もし `$event`が `Swoole`で定義されたイベントでなければ、警告が抛出されます。

  * **パラメータ**

    * `string $event`

      * 機能：回调イベント名
      * デフォルト値：なし
      * その他：なし

      !> 大文字と小文字は区別されません。具体的なイベント回调については[この節](/server/events)を参照してください。イベント名は字符串で `on`を加える必要はありません。

    * `callable $callback`

      * 機能：回调関数
      * デフォルト値：なし
      * その他：なし

      !> 関数の名前の文字列、クラスの静的方法、オブジェクトの方法の配列、匿名関数を参照してください。[この節](/learn?id=几种设置回调函数的方式)。
  
  * **戻り値**

    * 操作が成功した場合は `true`を返し、操作に失敗した場合は `false`を返します。

  * **例**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('connect', function ($server, $fd){
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();
```
## addListener()

监听ポートを追加します。ビジネスコードでは、[Swoole\Server->getClientInfo](/server/methods?id=getclientinfo)を呼び出すことで、特定の接続がどのポートから来たのかを取得できます。

```php
Swoole\Server->addListener(string $host, int $port, int $sockType): bool|Swoole\Server\Port
```

!> `1024` 以下のポートを监听するには `root`権限が必要です。  
メインブラットは `WebSocket`または `HTTP`プロトコルであり、新しく监听された `TCP`ポートはデフォルトでメインブラットのプロトコルの設定を継承します。新しいプロトコルを有効にするためには、別途 `set` 方法で設定しなければなりません。[詳細な説明](/server/port)をご覧ください。  
[Swoole\Server\Port](https://wiki.swoole.com/#/server/port?id=%e6%9e%b6%e6%9e%84%e7%ae%a1%e7%90%86)の詳細な説明をご覧ください。 

  * **パラメータ**

    * `string $host`

      * 機能：`__construct()`の `$host`と同じ
      * デフォルト値：`__construct()`の `$host`と同じ
      * その他：`__construct()`の `$host`と同じ

    * `int $port`

      * 機能：`__construct()`の `$port`と同じ
      * デフォルト値：`__construct()`の `$port`と同じ
      * その他：`__construct()`の `$port`と同じ

    * `int $sockType`

      * 機能：`__construct()`の `$sockType`と同じ
      * デフォルト値：`__construct()`の `$sockType`と同じ
      * その他：`__construct()`の `$sockType`と同じ
  
  * **戻り値**

    * 操作が成功した場合は `Swoole\Server\Port`を返し、操作に失敗した場合は `false`を返します。
!> - Unix Socketモードでは `$host`パラメータはアクセス可能なファイルパスを填写しなければならず、`$port`パラメータは無視されます。  
- Unix Socketモードでは、クライアントの `$fd`は数字ではなく、ファイルパスの文字列になります。  
- LinuxシステムでIPv6ポートを监听后、IPv4アドレスで接続を行うことができます。
## listen()

この方法は `addlistener`の別名です。

```php
Swoole\Server->listen(string $host, int $port, int $type): bool|Swoole\Server\Port
```
## addProcess()

ユーザー定義のワークプロセスを追加します。この関数は通常、監視、報告またはその他の特別なタスクのために特別なワークプロセスを作成するために使用されます。

```php
Swoole\Server->addProcess(Swoole\Process $process): int
```

!> `start`は実行する必要はありません。サーバーが起動すると自動的にプロセスが作成され、指定されたサブプロセス関数が実行されます。

  * **パラメータ**
  
    * [Swoole\Process](/process/process)

      * 機能：`Swoole\Process`オブジェクト
      * デフォルト値：なし
      * その他：なし

  * **戻り値**

    * プロセスIDを返す表示操作が成功し、そうでなければプログラムは致命的なエラーを抛出します。

  * **注意**

    !> -作成されたサブプロセスは`$server`オブジェクトが提供する各方法、例えば`getClientList/getClientInfo/stats`を呼び出すことができます。                                   
    - Worker/Taskプロセスでは`$process`が提供する方法を呼び出してサブプロセスと通信することができます。        
    - ユーザー定義プロセスでは`$server->sendMessage`を呼び出してWorker/Taskプロセスと通信することができます。      
    - ユーザープロセス内では`Server->task/taskwait`インターフェースを使用することはできません。              
    - ユーザープロセス内では`Server->send/close`などのインターフェースを使用することができます。         
    - ユーザープロセス内では`while(true)`(以下の例のように)または[EventLoop](/learn?id=什么是eventloop)ループ(例えばタイマーを作成する)が必要であり、そうでなければユーザープロセスは絶えず終了して再起動します。         

  * **ライフサイクル**

    ?> - ユーザープロセスの生存期間はMasterと [Manager](/learn?id=manager进程)と同じであり、[reload](/server/methods?id=reload)の影響を受けません。     
    - ユーザープロセスは`reload`指令には制御されず、`reload`時にユーザープロセスに何の情報も送信しません。        
    - `shutdown`でサーバーを閉じる際には、ユーザープロセスに`SIGTERM`シグナルを送信し、ユーザープロセスを閉じます。            
    - 自定义プロセスはManagerプロセスにホストされ、致命的なエラーが発生した場合、Managerプロセスが再構築一个新的自定义プロセス。         
    - 自定义プロセスは`onWorkerStop`などのイベントには触发しません。 

  * **例**

    ```php
    $server = new Swoole\Server('127.0.0.1', 9501);
    
    /**
     * ユーザープロセスは放送機能を実現しており、unixSocketのメッセージをループして受け取り、サーバーのすべての接続に並発して送信します
     */
    $process = new Swoole\Process(function ($process) use ($server) {
        $socket = $process->exportSocket();
        while (true) {
            $msg = $socket->recv();
            foreach ($server->connections as $conn) {
                $server->send($conn, $msg);
            }
        }
    }, false, 2, 1);
    
    $server->addProcess($process);
    
    $server->on('receive', function ($serv, $fd, $reactor_id, $data) use ($process) {
        // 受け取ったメッセージを群发
        $socket = $process->exportSocket();
        $socket->send($data);
    });
    
    $server->start();
    ```

    [Processプロセス間通信章节](/process/process?id=exportsocket)を参照してください。
## start()

サーバーを起動し、すべての`TCP/UDP`ポートを监听します。

```php
Swoole\Server->start(): bool
```

!> 提示:以下は [SWOOLE_PROCESS](/learn?id=swoole_process) モードを例に示しています

  * **提示**

    - 起動成功すると `worker_num+2`個のプロセスが作成されます。`Master`プロセス+`Manager`プロセス+`serv->worker_num`個の`Worker`プロセス。  
    - 起動に失敗するとすぐに `false`を返します。
    - 起動成功するとイベントループに入り、クライアントの接続要求を待機します。`start`方法の後のコードは実行されません。  
    - サーバーが閉鎖された後、`start`関数は `true`を返し、さらに下に実行されます。  
    - `task_worker_num`が設定されていると、対応する数量的 [Taskプロセス](/learn?id=taskworker进程)が増加します。   
    - 方法リストの中で`start`の前の方法は`start`呼び出し前にのみ使用でき、`start`の後の方法は[onWorkerStart](/server/events?id=onworkerstart)、[onReceive](/server/events?id=onreceive)などのイベント回调関数内でのみ使用できます。

  * **拡張**
  
    * Master 主プロセス

      * 主プロセスには複数の[Reactor](/learn?id=reactor线程)线程があり、`epoll/kqueue/select`に基づいてネットワークイベントをポーリングします。データを受け取った後、それを`Worker`プロセスに転送して処理します。
    
    * Manager 进程

      * すべての`Worker`プロセスを管理し、`Worker`プロセスのライフサイクルが終わったり異常が発生したりした場合、自動的に回収し、新しい`Worker`プロセスを作成します。
    
    * Worker 进程

      * 受信したデータを処理し、プロトコルの解析とリクエストへの応答が含まれます。`worker_num`が設定されていない場合、基層ではCPUの数だけ`Worker`プロセスが起動します。
      * 起動に失敗すると拡張内で致命的なエラーが抛出され、Please check the PHP error_log related information. `errno={number}`は標準の`Linux Errno`であり、関連文書を参照してください。
      * `log_file`設定が有効にされていると、情報は指定された`Log`ファイルに印刷されます。

  * **戻り値**

    * 操作が成功した場合は `true`を返し、操作に失敗した場合は `false`を返します

  * **起動失敗の一般的なエラー**

    * `bind`ポートに失敗しました。その理由は、他のプロセスがこのポートを占有しているからです。
    * 必須の回调関数を設定していないため、起動に失敗しました。
    * PHPコードに致命的なエラーがあります。PHP error_logの関連情報をご覧ください。
    * `ulimit -c unlimited`を実行し、core dumpを開いて、セグメントエラーがないかを確認してください。
    * `daemonize`を閉じたり、`log`を閉じたりして、エラー情報が画面に印刷されるようにしてください。
```
!> -`リロード`には保護メカニズムがあり、一度`リロード`が進行中の場合、新しい再起動信号を受け取っても無視されます。-もし`user/group`が設定されている場合、`Worker`プロセスは`master`プロセスに情報を送信する権限がない可能性があります。このような状況では、`root`アカウントを使用し、`シェル`で`kill`コマンドを実行して再起動する必要があります。
-`リロード`コマンドは[addProcess](/server/methods?id=addProcess)で追加されたユーザープロセスには無効です。

  * **戻り値**

    * `true`を返す表示操作が成功し、`false`を返す表示操作が失敗しました
       
  * **拡張**
  
    * **信号を送信する**
    
        * `SIGTERM`: 主プロセス/管理プロセスにこの信号を送ると、サーバーは安全に終了します。
        * PHPコードでは`$serv->shutdown()`を呼び出すことでこの操作を完了できます。
        * `SIGUSR1`: 主プロセス/管理プロセスに`SIGUSR1`信号を送ると、すべての`Worker`プロセスと`TaskWorker`プロセスをスムーズに`再起動`します。
        * `SIGUSR2`: 主プロセス/管理プロセスに`SIGUSR2`信号を送ると、すべての`Task`プロセスをスムーズに再起動します。
        * PHPコードでは`$serv->reload()`を呼び出すことでこの操作を完了できます。
        
    ```shell
    # すべてのworkerプロセスを再起動する
    kill -USR1 主プロセスPID
    
    # taskプロセスのみを再起動する
    kill -USR2 主プロセスPID
    ```
      
      > [参考：Linux信号一覧](/other/signal)

    * **プロセスモード**
    
        `Process`で起動されたプロセスでは、クライアントからの`TCP`接続は`Master`プロセス内で維持され、`worker`プロセスの再起動や異常終了は接続自体に影響を与えません。

    * **ベースモード**
    
        `Base`モードでは、クライアント接続は直接`Worker`プロセス内で維持されるため、`リロード`時にはすべての接続が切断されます。

    !> `Base`モードでは[Taskプロセス](/learn?id=taskworkerプロセス)の`リロード`はサポートされていません
    
    * **リロード有効範囲**

      `リロード`操作は、`Worker`プロセスが起動後に読み込んだPHPファイルのみを再読み込むことができます。`get_included_files`関数を使用して、どのPHPファイルが`WorkerStart`の前に読み込まれたかを列挙します。このリストに含まれるPHPファイルは、たとえ`リロード`操作を行ったとしても再読み込むことはできません。サーバーを閉じて再起動させる必要があります。

    ```php
    $serv->on('WorkerStart', function(Swoole\Server $server, int $workerId) {
        var_dump(get_included_files()); //この配列に含まれるファイルはプロセス起動前に読み込まれたため、リロードできません
    });
    ```

    * **APC/OPcache**
    
        PHPで`APC/OPcache`が有効の場合、`リロード`の再読み込みは影響を受けます。2つの解決策があります。
        
        * `APC/OPcache`の`stat`検出を有効にし、ファイルが更新された場合 自动的に`OPCode`を更新します。
        * `onWorkerStart`でファイル（require、includeなど）を読み込む前に`apc_clear_cache`または`opcache_reset`を実行して`OPCode`キャッシュをクリアします。

  * **注意**

    !> -スムーズな再起動は[onWorkerStart](/server/events?id=onworkerstart)または[onReceive](/server/events?id=onreceive)など、`Worker`プロセス内で`include/require`されるPHPファイルにのみ有効です。
    -`Server`が起動する前にすでに`include/require`されたPHPファイルは、スムーズな再起動を通じて再読み込むことはできません。
    -`Server`の構成、すなわち`$serv->set()`で渡されたパラメータ設定については、整个`Server`を閉じて再起動させる必要があります。
    -`Server`は内部ネットワークポートを监听し、リモートの制御コマンドを受け取って、すべての`Worker`プロセスを再起動させることができます。
## stop()

現在の`Worker`プロセスを停止させ、直ちに`onWorkerStop`のコールバック関数をトリガーします。

```php
Swoole\Server->stop(int $workerId = -1, bool $waitEvent = false): bool
```

  * **パラメータ**

    * `int $workerId`

      * 機能：指定する `worker id`
      * 既定値：-1、現在のプロセスを表す
      * その他：なし

    * `bool $waitEvent`

      * 機能：退出戦略を制御する、`false`は直ちに退出することを表し、`true`はイベントループが空になるのを待ってから退出することを表す
      * 既定値：false
      * その他：true

  * **戻り値**

    * `true`を返す表示操作が成功し、`false`を返す表示操作が失敗しました

  * **ヒント**

    !> -[非同期IO](/learn?id=同期io非同期io)サーバーが`stop`を呼び出してプロセスを退出させる場合、まだ待っているイベントがある可能性があります。例えば、`Swoole\MySQL->query`を使用し、`SQL`文を送信したが、まだMySQLサーバーからの結果を待っています。この時、プロセスを強制的に退出させると、`SQL`の実行結果が失われます。  
    -`$waitEvent = true`を設定すると、基層は[非同期安全再起動](/question/use?id=swooleどのように正しくサービスを再起動するか)戦略を使用します。まず`Manager`プロセスに通知し、新しい`Worker`を再起動して新しいリクエストを処理します。現在の古い`Worker`はイベントを待ち、イベントループが空になるか、`max_wait_time`を超えた後、プロセスを退出し、非同期イベントの安全性を最大限に保証します。
## shutdown()

サービスを閉じます。

```php
Swoole\Server->shutdown(): bool
```

  * **戻り値**

    * `true`を返す表示操作が成功し、`false`を返す表示操作が失敗しました

  * **ヒント**

    * この関数は`Worker`プロセス内で使用できます。
    * 主プロセスに`SIGTERM`を送信することもサービスの閉じることができます。

```shell
kill -15 主プロセスPID
```
## tick()

`tick`タイマーを追加し、カスタムのコールバック関数を定義できます。この関数は [Swoole\Timer::tick](/timer?id=tick) の別名です。

```php
Swoole\Server->tick(int $millisecond, callable $callback): void
```

  * **パラメータ**

    * `int $millisecond`

      * 機能：間隔時間【ミリ秒】
      * 既定値：なし
      * その他：なし

    * `callable $callback`

      * 機能：コールバック関数
      * 既定値：なし
      * その他：なし

  * **注意**
  
    !> -`Worker`プロセスが終了した後、すべてのタイマーは自動的に破壊されます  
    -`tick/after`タイマーは`Server->start`の前に使用することはできません  
    -`Swoole5`以降、この別名の使用方法は削除されましたので、直接`Swoole\Timer::tick()`を使用してください

  * **例**

    * [onReceive](/server/events?id=onreceive) 内で使用する

    ```php
    function onReceive(Swoole\Server $server, int $fd, int $reactorId, mixed $data)
    {
        $server->tick(1000, function () use ($server, $fd) {
            $server->send($fd, "hello world");
        });
    }
    ```

    * [onWorkerStart](/server/events?id=onworkerstart) 内で使用する

    ```php
    function onWorkerStart(Swoole\Server $server, int $workerId)
    {
        if (!$server->taskworker) {
            $server->tick(1000, function ($id) {
              var_dump($id);
            });
        } else {
            //task
            $server->tick(1000);
        }
    }
    ```
## after()

一回限りのタイマーを追加し、実行が完了すると自動的に破壊されます。この関数は [Swoole\Timer::after](/timer?id=after) の別名です。

```php
Swoole\Server->after(int $millisecond, callable $callback)
```

  * **パラメータ**

    * `int $millisecond`

      * 機能：実行時間【ミリ秒】
      * 既定値：なし
      * その他：なし
      * バージョン影響：`Swoole v4.2.10` 以下のバージョンでは最大 86400000までです

    * `callable $callback`

      * 機能：コールバック関数、呼び出し可能なものでなければならず、`callback` 関数は引数を受け取っていません
      * 既定値：なし
      * その他：なし

  * **注意**
  
    !> -タイマーのライフサイクルはプロセスレベルであり、`reload`または`kill`でプロセスを再起動/停止させる場合、すべてのタイマーが自動的に破壊されます  
    -重要なロジックやデータを持つタイマーがある場合は、`onWorkerStop`のコールバック関数内で実現するか、[どのように正しくサービスを再起動するか](/question/use?id=swooleどのように正しくサービスを再起動するか)を参照してください  
    -`Swoole5`以降、この別名の使用方法は削除されましたので、直接`Swoole\Timer::after()`を使用してください
## defer()

関数の実行を遅らせることができ、これは [Swoole\Event::defer](/event?id=defer) の別名です。

```php
Swoole\Server->defer(Callable $callback): void
```

  * **パラメータ**

    * `Callable $callback`

      * 機能：コールバック関数【必須】、実行可能な関数変数、文字列、配列、匿名関数均可
      * 既定値：なし
      * その他：なし

  * **注意**

    !> -基層は[EventLoop](/learn?id=什么是eventloop)のループが完了した後にこの関数を実行します。この関数の目的は、一部のPHPコードを遅延実行させ、プログラムが他の`IO`イベントを優先的に処理することです。例えば、CPU集約的な計算があり、緊急ではない回调関数は、プロセスが他のイベントを処理した後にCPU集約的な計算を行うことができます  
    -基層は`defer`の関数が直ちに実行されるとは保証していません。システムの重要なロジックであれば、できるだけ早く実行する必要がありますので、`after`タイマーを使用してください  
    -`onWorkerStart`回调で`defer`を実行する場合、イベントが発生するのを待ってから回调される必要があります
    -`Swoole5`以降、この別名の使用方法は削除されましたので、直接`Swoole\Event::defer()`を使用してください

  * **例**

```php
function query($server, $db) {
    $server->defer(function() use ($db) {
        $db->close();
    });
}
```
## clearTimer()

`tick/after`タイマーをクリアします。この関数は [Swoole\Timer::clear](/timer?id=clear) の別名です。

```php
Swoole\Server->clearTimer(int $timerId): bool
```

  * **パラメータ**

    * `int $timerId`

      * 機能：指定タイマーID
      * 既定値：なし
      * その他：なし

  * **戻り値**

    * `true`を返す表示操作が成功し、`false`を返す表示操作が失敗しました

  * **注意**

    !> -`clearTimer`は現在のプロセスのタイマーのみをクリアするために使用できます     
    -`Swoole5`以降、この別名の使用方法は削除されましたので、直接`Swoole\Timer::clear()`を使用してください 

  * **例**

```php
$timerId = $server->tick(1000, function ($timerId) use ($server) {
    $server->clearTimer($timerId);//$idはタイマーのIDです
});
```
## close()

クライアントの接続を閉じます。

```php
Swoole\Server->close(int $fd, bool $reset = false): bool
```

  * **パラメータ**

    * `int $fd`

      * 機能：閉じたい `fd` (ファイル記述子)
      * 既定値：なし
      * その他：なし

    * `bool $reset`

      * 機能：`true`を設定すると強制的に接続を閉じ、送信キューのデータを破棄します
      * 既定値：false
      * その他：true

  * **戻り値**

    * `true`を返す表示操作が成功し、`false`を返す表示操作が失敗しました

  * **注意**
  !> -`Server`が主動的に`close`接続しても、[onClose](/server/events?id=onclose)イベントがトリガーされます  -`close`の後でクリーンアップロジックを書かないでください。それは[onClose](/server/events?id=onclose)のコールバックで処理されるべきです  
-`HTTP\Server`の`fd`は、上層の回调方法の`response`で取得できます

  * **例**

```php
$server->on('request', function ($request, $response) use ($server) {
    $server->close($response->fd);
});
```
## send()

クライアントにデータを送信します。

```php
Swoole\Server->send(int|string $fd, string $data, int $serverSocket = -1): bool
```

  * **パラメータ**

    * `int|string $fd`

      * 機能：指定するクライアントのファイル記述子またはunix socketパス
      * 既定値：なし
      * その他：なし

    * `string $data`

      * 機能：送信するデータ、`TCP`プロトコルでは最大で2Mまで、[buffer_output_size](/server/setting?id=buffer_output_size)を変更して送信允许の最大パケット長さを変更できます
      * 既定値：なし
      * その他：なし

    * `int $serverSocket`

      * 機能：UnixSocket DGRAM [https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php]にデータを送信する必要がある場合にこのパラメータが必要で、TCPクライアントには必要ありません
      * 既定値：-1、現在监听しているudpポートを表す
      * その他：なし

  * **戻り値**

    * `true`を返す表示操作が成功し、`false`を返す表示操作が失敗しました

  * **ヒント**

    !> 送信プロセスは非同期であり、基層は自動的に可写を監視し、データを徐々にクライアントに送信します。つまり、`send`が戻った後でクライアントがデータを受け取ったわけではありません。

    * セキュリティ
      * `send`操作は原子性を持っており、複数のプロセスが同時に`send`を呼び出して同じ`TCP`接続にデータを送信しても、データの混在は発生しません。

    * 長さの制限
      * 2Mを超えるデータを送信したい場合は、データを一時ファイルに書き込み、その後`sendfile`インターフェースを通じて送信することができます
      * [buffer_output_size](/server/setting?id=buffer_output_size)パラメータを設定して送信長さの制限を変更できます
      * 8Kを超えるデータを送信するとき、基層は`Worker`プロセスの共有メモリを使用し、一度`Mutex->lock`操作を行う必要があります

    * キャッシュエリア
      * `Worker`プロセスの[unixSocket](/learn?id=什么是IPC)キャッシュエリアが満杯の場合、8Kのデータを送信するために一時ファイルストレージが使用されます
      * 同一クライアントに連続して大量のデータを送信すると、クライアントが受信できないために`Socket`メモリキャッシュエリアが満ち、Swoole基層は直ちに`false`を返します。`false`の場合、データは磁盘に保存され、クライアントが送信されたデータをすべて受信した後に再度送信されます。

    * [协程调度](/coroutine?id=协程调度)
      * 协程モードで[send_yield](/server/setting?id=send_yield)が有効に設定されている場合、`send`がキャッシュエリアが満杯に遭遇すると自動的に挂起し、データの一部がクライアントに読まれると协程を復帰させ、データの送信を続けます。

    * [UnixSocket](/
## sendto()

任意のクライアントの`IP:ポート`に`UDP`データパックを送信します。

```php
Swoole\Server->sendto(string $ip, int $port, string $data, int $serverSocket = -1): bool
```

  * **引数**

    * `string $ip`

      * 機能：クライアントの `ip`を指定する
      * 默认値：なし
      * その他：なし

      ?> `$ip`は`IPv4`または`IPv6`の文字列で、例えば`192.168.1.102`です。IPが不正な場合はエラーが返されます

    * `int $port`

      * 機能：クライアントの `port`を指定する
      * 默认値：なし
      * その他：なし

      ?> `$port`は `1-65535`のネットワークポート番号で、ポートが間違っている場合は送信に失敗します

    * `string $data`

      * 機能：送信したいデータ内容で、テキストでもバイナリでもよい
      * 默认値：なし
      * その他：なし

    * `int $serverSocket`

      * 機能：どのポートでデータパックを送信するかを示す対応するポート`server_socket`の記述子【[onPacketイベント](/server/events?id=onpacket)の`$clientInfo`から取得できる】
      * 默认値：-1、現在listenしているudpポートを表す
      * その他：なし

  * **戻り値**

    * 成功した場合は`true`、失敗した場合は`false`を返す

      ?> サーバーは複数の`UDP`ポートを同時にlistenすることができ、[多ポートlisten](/server/port)を参照してください。このパラメータではどのポートでデータパックを送信するか指定できます

  * **注意**

  !> `UDP`のポートをlistenしていないと、`IPv4`アドレスにデータを送信することはできません  
  `UDP6`のポートをlistenしていないと、`IPv6`アドレスにデータを送信することはできません

  * **例**

```php
// IPアドレスが220.181.57.216でポート9502を持つホストに"hello world"という文字列を送信します。
$server->sendto('220.181.57.216', 9502, "hello world");
// IPv6サーバーにUDPデータパックを送信します
$server->sendto('2600:3c00::f03c:91ff:fe73:e98f', 9501, "hello world");
```
## sendwait()

クライアントに同期してデータを送信します。

```php
Swoole\Server->sendwait(int $fd, string $data): bool
```

  * **引数**

    * `int $fd`

      * 機能：クライアントのファイル記述子を指定する
      * 默认値：なし
      * その他：なし

    * `string $data`

      * 機能：送信したいデータ
      * 默认値：なし
      * その他：なし

  * **戻り値**

    * 成功した場合は`true`、失敗した場合は`false`を返す

  * **ヒント**

    * 特定のシナリオでは、`Server`は連続してクライアントにデータを送信する必要がありますが、「Server->send」データ送信インターフェースは純的异步であり、大量のデータ送信会导致内存发送队列塞满。

    * 「Server->sendwait」を使用することでこの問題を解決できます。「Server->sendwait」は接続が書込可能になるのを待って、データが送信されるまで返しません。

  * **注意**

  !> `sendwait`は現在[SWOOLE_BASE](/learn?id=swoole_base)モードでのみ使用できます  
  `sendwait`は本機または内網通信にのみ使用し、外網接続では`sendwait`を使用しないでください。`enable_coroutine`=>true(デフォルトで有効)の場合もこの関数を使用しないでください。それは他の协程を固まらせることがあります。同期ブロッキングのサーバーでのみ使用できます。
## sendMessage()

任意の`Worker`プロセスや[Taskプロセス](/learn?id=taskworker进程)にメッセージを送信します。非マスタープロセスや管理プロセスでのみ呼び出せます。メッセージを受け取ったプロセスは`onPipeMessage`イベントがトリガーされます。

```php
Swoole\Server->sendMessage(mixed $message, int $workerId): bool
```

  * **引数**

    * `mixed $message`

      * 機能：送信するメッセージのデータ内容で、長さは制限されていませんが、8Kを超える場合はメモリ臨時ファイルを開始します
      * 默认値：なし
      * その他：なし

    * `int $workerId`

      * 機能：ターゲットプロセスの`ID`で、範囲は[$worker_id](/server/properties?id=worker_id)を参照してください
      * 默认値：なし
      * その他：なし

  * **ヒント**

    * `Worker`プロセス内で`sendMessage`を呼び出すと[异步IO](/learn?id=同步io异步io)であり、メッセージはまずバッファに保存され、書込可能になったときに[unixSocket](/learn?id=什么是IPC)にこのメッセージを送信します
    * [Taskプロセス](/learn?id=taskworker进程)内で`sendMessage`を呼び出すとデフォルトは[同步IO](/learn?id=同步io异步io)ですが、いくつかの状況では自動的に异步IOに変わります。[同步IOから异步IOへの変換](/learn?id=同步io转换成异步io)を参照してください
    * [Userプロセス](/server/methods?id=addprocess)内で`sendMessage`を呼び出すとTaskと同じで、デフォルトは同期ブロッキングです。[同步IOから异步IOへの変換](/learn?id=同步io转换成异步io)を参照してください

  * **注意**
  !> - `sendMessage()`が[异步IO](/learn?id=同步io转换成异步io)の場合、対端プロセスが何らかの理由でデータを受け取らない場合、`sendMessage()`を連続して呼び出さないでください。これは大量のメモリリソースを占有することがあります。応答メカニズムを追加することで、対端が応答しない場合は呼び出しを停止することができます。 - `MacOS/FreeBSD`では2Kを超えると臨時ファイルを使用します。 - [sendMessage](/server/methods?id=sendMessage)を使用するためには、`onPipeMessage`イベントの回调関数を登録しなければなりません。  
- [task_ipc_mode](/server/setting?id=task_ipc_mode) = 3を設定すると、特定のtaskプロセスに[sendMessage](/server/methods?id=sendMessage)でメッセージを送信することができません。

  * **例**

```php
$server = new Swoole\Server('0.0.0.0', 9501);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 2,
));
$server->on('pipeMessage', function ($server, $src_worker_id, $data) {
    echo "#{$server->worker_id} message from #$src_worker_id: $data\n";
});
$server->on('task', function ($server, $task_id, $src_worker_id, $data) {
    var_dump($task_id, $src_worker_id, $data);
});
$server->on('finish', function ($server, $task_id, $data) {

});
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    if (trim($data) == 'task') {
        $server->task("async task coming");
    } else {
        $worker_id = 1 - $server->worker_id;
        $server->sendMessage("hello task process", $worker_id);
    }
});

$server->start();
```
## exist()

指定された`fd`に対応する接続が存在かどうかを確認します。

```php
Swoole\Server->exist(int $fd): bool
```

  * **引数**

    * `int $fd`

      * 機能：ファイル記述子
      * 默认値：なし
      * その他：なし

  * **戻り値**

    * 存在する場合は`true`、存在しない場合は`false`を返す

  * **ヒント**  

    * このインターフェースは共有メモリに基づいて計算され、いかなる`IO`操作もありません
## pause()

データの受信を停止します。

```php
Swoole\Server->pause(int $fd): bool
```

  * **引数**

    * `int $fd`

      * 機能：指定されたファイル記述子
      * 默认値：なし
      * その他：なし

  * **戻り値**

    * 操作が成功した場合は`true`、失敗した場合は`false`を返す

  * **ヒント**

    * この関数を呼び出すと、接続を[EventLoop](/learn?id=什么是eventloop)から取り除き、クライアントからのデータを受信しなくなります。
    * この関数は送信キューの処理には影響しません
    * `SWOOLE_PROCESS`モードでのみ使用でき、`pause`を呼び出した後、一部のデータがすでに`Worker`プロセスに到達しているため、まだ[onReceive](/server/events?id=onreceive)イベントがトリガーされる可能性があります
## resume()

データの受信を再開します。`pause`メソッドとペアで使用します。

```php
Swoole\Server->resume(int $fd): bool
```

  * **引数**

    * `int $fd`

      * 機能：指定されたファイル記述子
      * 默认値：なし
      * その他：なし

  * **戻り値**

    * 操作が成功した場合は`true`、失敗した場合は`false`を返す

  * **ヒント**

    * この関数を呼び出すと、接続を再び[EventLoop](/learn?id=什么是eventloop)に追加し、クライアントからのデータの受信を再開します
## getCallback()

サーバーで指定された名前の回调関数を取得します。

```php
Swoole\Server->getCallback(string $event_name): \Closure|string|null|array
```

  * **引数**

    * `string $event_name`

      * 機能：イベント名で、「on」を加える必要がなく、大文字小文字を区別しません
      * 默认値：なし
      * その他：参照 [イベント](/server/events)

  * **戻り値**

    * 対応する回调関数が存在する場合は、異なる[回调関数の設定方法](/learn?id=四种设置回调函数的方式)に応じて `Closure` / `string` / `array`を返します
    * 対応する回调関数がない場合は、`null`を返します
## getClientInfo()

接続情報を取得します。別名は`Swoole\Server->connection_info()`です。

```php
Swoole\Server->getClientInfo(int $fd, int $reactorId = -1, bool $ignoreError = false): false|array
```

  * **引数**

    * `int $fd`

      * 機能：指定されたファイル記述子
      * 默认値：なし
      * その他：なし

    * `int $reactorId`

      * 機能：接続が存在する[Reactor](/learn?id=reactor线程)の线程IDで、現在は何の役にも立たませんが、API互換性を保つために存在します
      * 默认値：-1
      * その他：なし

    * `bool $ignoreError`

      * 機能：エラーを無視するかどうかで、trueに設定すると接続が閉じられたとしても接続情報を返すことができ、falseでは接続が閉じられたらfalseを返します
      * 默认値：false
      * その他：なし

  * **ヒント**

    * クライアント証明書

      * [onConnect](/server/events?id=onconnect)イベントがトリガーされたプロセスでのみ取得できます
      * 形式は`x509`で、`openssl_x509_parse`関数を使用して証明書の情報を取得できます

    * [dispatch_mode](/server/setting?id=dispatch_mode) = 1/3を設定した場合、このデータパケットの分发戦略は無状态サービスに使用されるため、接続が切断された後、関連情報はメモリから直接削除されます。そのため、`Server->getClientInfo`では関連する接続情報を取得することはできません。

  * **戻り値**

    * 呼び出しに失敗した場合は`false`を返す
    * 呼び出しに成功した場合は、クライアント情報を含む`array`を返す

```php
$fd_info = $server->getClientInfo($fd);
var_dump($fd_info);

array(15) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(25)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(39136)
  ["remote_ip"]=>
  string(9) "127.0.0.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1677322106)
  ["last_time"]=>
  int(1677322106)
  ["last_recv_time"]=>
  float(1677322106.901918)
  ["last_send_time"]=>
  float(0)
  ["last_dispatch_time"]=>
  float(0)
  ["close_errno"]=>
  int(0)
  ["recv_queued_bytes"]=>
  int(78)
  ["send_queued_bytes"]=>
  int(0)
}
```
引数 | 効果
---|---
server_port | サーバーがlistenしているポート
server_fd | サーバーのfd
socket_fd | クライアントのfd
socket_type | ソケットの種類
remote_port | クライアントのポート
remote_ip | クライアントのIP
reactor_id | どのReactorプロセスから来たか
connect_time | クライアントがサーバーに接続した時間、秒で、masterプロセスによって設定されます
last_time |最後にデータを受け取った時間、秒で、masterプロセスによって設定されます
last_recv_time |最後にデータを受け取った時間、秒で、masterプロセスによって設定されます
last_send_time |最後にデータを送信した時間、秒で、masterプロセスによって設定されます
last_dispatch_time | workerプロセスがデータを受け取った時間
close_errno | 接続が閉じた時のエラーコード、接続が異常に閉じた場合はclose_errnoは非ゼロで、Linuxのエラー情報参照できます
recv_queued_bytes | 处理を待っているデータ量
send_queued_bytes | 送信を待っているデータ量
websocket_status | [オプション] WebSocket接続の状態で、サーバーがSwoole\WebSocket\Serverの場合に追加される情報です
uid | [オプション] bindでユーザーIDを割り当てた場合に追加される情報です
ssl_client_cert | [オプション] SSLトンネルを使用し、クライアントが証明書を設定した場合に追加される情報です


## getClientList()

現在の`Server`のすべてのクライアント接続を列挙します。`Server::getClientList`方法は共有メモリに基づいており、IOWaitは存在せず、列挙速度は非常に速いです。また、`getClientList`はすべての`TCP`接続を返すため、現在の`Worker`プロセスの`TCP`接続に限られません。別名は`Swoole\Server->connection_list()`です。

```php
Swoole\Server->getClientList(int $start_fd = 0, int $pageSize = 10): false|array
```

  * **引数**

    * `int $start_fd`

      * 機能：開始する`fd`を指定します
      * 默认値：0
      * その他：なし

    * `int $pageSize`

      * 機能：ページごとに何件を取得するか、最大で100までです
      * 默认値：10
      * その他：なし

  * **戻り値**

    * 成功した場合は数字の索引配列を返し、要素は取得された`$fd`です。配列は从小到大にソートされています。最後の`$fd`は新しい`start_fd`として再び取得を試みます
    * 失敗した場合は`false`を返す

  * **ヒント**

    * [Server::$connections](/server/properties?id=connections)のイテレーターを使用して接続を列挙することをお勧めします
    * `getClientList`は`TCP`クライアントにのみ使用でき、`UDP`サーバーはクライアント情報を自己保存する必要があります
    * [SWOOLE_BASE](/learn?id=swoole_base)モードでは、現在プロセス内の接続のみ取得できます

!> - `dispatch_mode=5`を設定した場合にのみ有効です。  - `UID`がバインドされていない場合は、デフォルトで`fd`を使って割り当てられます。  
- 同一の接続は一度しか`bind`できません。もしすでに`UID`をバインドしていた場合、再び`bind`すると`false`が返されます。

  * **例**

```php
$serv = new Swoole\Server('0.0.0.0', 9501);

$serv->fdlist = [];

$serv->set([
    'worker_num' => 4,
    'dispatch_mode' => 5,   //uid dispatch
]);

$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "{$fd} connect, worker:" . $serv->worker_id . PHP_EOL;
});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    $conn = $serv->connection_info($fd);
    print_r($conn);
    echo "worker_id: " . $serv->worker_id . PHP_EOL;
    if (empty($conn['uid'])) {
        $uid = $fd + 1;
        if ($serv->bind($fd, $uid)) {
            $serv->send($fd, "bind {$uid} success");
        }
    } else {
        if (!isset($serv->fdlist[$fd])) {
            $serv->fdlist[$fd] = $conn['uid'];
        }
        print_r($serv->fdlist);
        foreach ($serv->fdlist as $_fd => $uid) {
            $serv->send($_fd, "{$fd} say:" . $data);
        }
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "{$fd} Close". PHP_EOL;
    unset($serv->fdlist[$fd]);
});

$serv->start();
```
## stats()

現在の`Server`の活動している`TCP`接続数、起動時間などの情報を取得できます。`accept/close`（接続的建立/切断）の総次数などの情報もあります。

```php
Swoole\Server->stats(): array
```

  * **例**

```php
array(25) {
  ["start_time"]=>
  int(1677310656)
  ["connection_num"]=>
  int(1)
  ["abort_count"]=>
  int(0)
  ["accept_count"]=>
  int(1)
  ["close_count"]=>
  int(0)
  ["worker_num"]=>
  int(2)
  ["task_worker_num"]=>
  int(4)
  ["user_worker_num"]=>
  int(0)
  ["idle_worker_num"]=>
  int(1)
  ["dispatch_count"]=>
  int(1)
  ["request_count"]=>
  int(0)
  ["response_count"]=>
  int(1)
  ["total_recv_bytes"]=>
  int(78)
  ["total_send_bytes"]=>
  int(165)
  ["pipe_packet_msg_id"]=>
  int(3)
  ["session_round"]=>
  int(1)
  ["min_fd"]=>
  int(4)
  ["max_fd"]=>
  int(25)
  ["worker_request_count"]=>
  int(0)
  ["worker_response_count"]=>
  int(1)
  ["worker_dispatch_count"]=>
  int(1)
  ["task_idle_worker_num"]=>
  int(4)
  ["tasking_num"]=>
  int(0)
  ["coroutine_num"]=>
  int(1)
  ["coroutine_peek_num"]=>
  int(1)
  ["task_queue_num"]=>
  int(1)
  ["task_queue_bytes"]=>
  int(1)
}
```
引数 | 効果
---|---
start_time | サーバーが起動した時間
connection_num | 現在の接続数
abort_count | 接続を拒否した回数
accept_count | 接続を受け入れた回数
close_count | 接続を閉じた回数
worker_num  | 起動したworkerプロセスの数
task_worker_num  | 起動したtask_workerプロセスの数【`v4.5.7`で利用可能】
user_worker_num  | 起動したtask workerプロセスの数
idle_worker_num | 空闲のworkerプロセスの数
dispatch_count | ServerがWorkerに送信したパケットの数【`v4.5.7`で利用可能、[SWOOLE_PROCESS](/learn?id=swoole_process)モードでのみ有効】
request_count | Serverが受け取ったリクエストの数【onReceive、onMessage、onRequset、onPacketの4種類のデータリクエストのみがrequest_countを計算】
response_count | Serverが返したレスポンスの数
total_recv_bytes| 受信したデータの総字节数
total_send_bytes | 送信したデータの総字节数
pipe_packet_msg_id | プロセス間通信ID
session_round | 起始session ID
min_fd | 最小の接続fd
max_fd | 最大的连接fd
worker_request_count |現在のWorkerプロセスが受け取ったリクエストの数【worker_request_countがmax_requestを超えるとworkerプロセスは退出する】
worker_response_count |現在のWorkerプロセスが返したレスポンスの数
worker_dispatch_count |masterプロセスが現在のWorkerプロセスにタスクを投じるカウンターであり、[masterプロセス](/learn?id=reactor线程)でdispatchを行う時にカウンターを増やす。
task_idle_worker_num | 空闲のtaskプロセスの数
tasking_num | 作業中のtaskプロセスの数
coroutine_num |現在のcoロoutineの数【Coroutine用】、より多くの情報を取得するには[このセクション](/coroutine/gdb)を参照してください。
coroutine_peek_num | 全てのcoロoutineの数
task_queue_num | メッセージキュー内のtaskの数【Task用】
task_queue_bytes | メッセージキューの内存使用量字节数【Task用】
## task()

非阻塞的に[task_worker]プールに非同期タスクを投じる。この関数はすぐに戻ります。`Worker`プロセスは新しいリクエストを処理し続けることができます。[Task]機能を使用するには、まず`task_worker_num`を設定し、さらにServerの[onTask](/server/events?id=ontask)と[onFinish](/server/events?id=onfinish)イベントのコールバック関数を設定する必要があります。

```php
Swoole\Server->task(mixed $data, int $dstWorkerId = -1, callable $finishCallback): int
```

  * **引数**

    * `mixed $data`

      * 機能：投じるタスクデータは、シリアライズ可能なPHP変数でなければなりません。
      * 既定値：なし
      * 他の値：なし

    * `int $dstWorkerId`

      * 機能：どの[Taskプロセス](/learn?id=taskworkerプロセス)に投じるかを指定できます。Taskプロセスの`ID`を渡すだけでよいのですが、範囲は`[0, $server->setting['task_worker_num']-1]`です。
      * 既定値：-1【デフォルトでは`-1`で、ランダムに選択される。底層は自動的に空闲の[Taskプロセス](/learn?id=taskworkerプロセス)を選択する】
      * 他の値：`[0, $server->setting['task_worker_num']-1]`

    * `callable $finishCallback`

      * 機能：`finish`回调関数です。タスクに回调関数を設定した場合、Taskが結果を返す際には指定した回调関数を直接実行し、Serverの[onFinish](/server/events?id=onfinish)回调は実行されません。ただし、Workerプロセスでタスクを投じる場合にのみトリガーされます。
      * 既定値：`null`
      * 他の値：なし

  * **戻り値**

    * 成功すると、整数值の`$task_id`が戻ります。これはこのタスクのIDを表します。もし`finish`回调を設定していた場合、`onFinish](/server/events?id=onfinish)回调には`$task_id`パラメータが渡されます。
    * 失敗すると、`false`が戻ります。`$task_id`は`0`になる可能性がありますが、失敗したかどうかを判断するには`===`を使用する必要があります。

  * **ヒント**

    * この機能は、遅いタスクを非同期で実行するために使用できます。例えば、チャットルームサーバーでは、放送を送信するために使用することができます。タスクが完了したとき、[taskプロセス](/learn?id=taskworkerプロセス)で`$serv->finish("finish")`を呼び出して、workerプロセスにこのタスクが完了したことを伝えることができます。もちろん、`Swoole\Server->finish`はオプションです。
    * `task`は底層で[unixSocket](/learn?id=什么是IPC)通信を使用し、全内存を使用しており、IO消費はありません。単プロセスの読み書き性能は`100万/s`に達し、異なるプロセスが異なる`unixSocket`通信を使用することで、多コアを最大限に活用できます。
    * ターゲット[Taskプロセス](/learn?id=taskworkerプロセス)を指定していなければ、`task`メソッドは[Taskプロセス](/learn?id=taskworkerプロセス)の忙碌状態を判断し、底層は空闲状態の[Taskプロセス](/learn?id=taskworkerプロセス)にのみタスクを投じるでしょう。すべての[Taskプロセス](/learn?id=taskworkerプロセス)が忙碌している場合、底層はタスクをポーリングして各プロセスに投じます。[server->stats](/server/methods?id=stats)メソッドを使用して、現在キューに入っているタスクの数を取得できます。
    * 第三引数は、直接[onFinish](/server/events?id=onfinish)関数を設定することができます。タスクに回调関数を設定した場合、Taskが結果を返す際には指定した回调関数を直接実行し、Serverの[onFinish](/server/events?id=onfinish)回调は実行されません。ただし、Workerプロセスでタスクを投じる場合にのみトリガーされます。

    ```php
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    ```

    * `$task_id`は`0-42`億の整数で、現在のプロセス内で唯一です。
    * `task`機能をデフォルトでは起動しません。この機能を使用するためには、手動で`task_worker_num`を設定する必要があります。
    * `TaskWorker`の数を[Server->set()](/server/methods?id=set)パラメータで調整できます。例えば、`task_worker_num => 64`とすると、`64`個のプロセスを起動して非同期タスクを受け付けることができます。

  * **設定パラメータ**

    * `Server->task/taskwait/finish`の3つの方法は、渡された`$data`データが`8K`を超える場合に一時ファイルを使用して保存されます。一時ファイルの内容が
    [server->package_max_length](/server/setting?id=package_max_length)を超える場合、底層は警告を発します。この警告はデータの投函に影響を与えませんが、大きな`Task`は性能問題が発生する可能性があります。
    
    ```shell
    WARN: task package is too big.
    ```

  * **単方向タスク**

    * `Master`、`Manager`、`UserProcess`プロセスから投じられたタスクは単方向であり、`TaskWorker`プロセスでは`return`や`Server->finish()`メソッドを使用して結果データ返回することはできません。

  * **注意**
  !> - `task`方法は[taskプロセス](/learn?id=taskworkerプロセス)で呼び出せません。  - `task`を使用するためには、Serverに[onTask](/server/events?id=ontask)と[onFinish](/server/events?id=onfinish)のcallbackを設定する必要があります。そうでなければ、`Server->start`は失敗します。  - `task`操作の回数は[onTask](/server/events?id=ontask)の処理速度を超えてはなりません。投函容量が処理能力を超えると、`task`データがキャッシュエリアを満たし、`Worker`プロセスがブロックされる可能性があります。`Worker`プロセスは新しいリクエストを受け取ることはできません。  
- [addProcess](/server/method?id=addProcess)で追加されたユーザープロセスでは、`task`を使用して単方向にタスクを投じることはできますが、結果データの返回することはできません。タスク/Workerプロセスと通信するには[sendMessage](/server/methods?id=sendMessage)インターフェースを使用してください。

  * **例**

```php
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 4,
));

$server->on('Receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    echo "接收数据" . $data . "\n";
    $data    = trim($data);
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    $task_id = $server->task($data, 0);
    $server->send($fd, "分发任务，任务id为$task_id\n");
});

$server->on('Task', function (Swoole\Server $server, $task_id, $reactor_id, $data) {
    echo "Tasker进程接收到数据";
    echo "#{$server->worker_id}\tonTask: [PID={$server->worker_pid}]: task_id=$task_id, data_len=" . strlen($data) . "." . PHP_EOL;
    $server->finish($data);
});

$server->on('Finish', function (Swoole\Server $server, $task_id, $data) {
    echo "Task#$task_id finished, data_len=" . strlen($data) . PHP_EOL;
});

$server->on('workerStart', function ($server, $worker_id) {
    global $argv;
    if ($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]}: task_worker");
    } else {
        swoole_set_process_name("php {$argv[0]}: worker");
    }
});

$server->start();
```
## taskwait()

`taskwait`は`task`メソッドと同じ機能で、[taskプロセス](/learn?id=taskworkerプロセス)プールに非同期タスクを投じて実行します。`task`と異なり、`taskwait`は同期して待つもので、タスクが完了するかタイムアウトするまで待ちます。`$result`はタスク実行の結果で、`$server->finish`関数によって送出されます。このタスクがタイムアウトした場合、ここでは`false`が返ります。

```php
Swoole\Server->taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1): mixed
```

  * **引数**

    * `mixed $data`

      * 機能：投じるタスクデータは、任意のタイプでなければなりません。文字列以外のタイプは、底層で自動的にシリアライズされます。
      * 既定値：なし
      * 他の値：なし

    * `float $timeout`

      * 機能：タイムアウト時間で、浮点型です。単位は秒で、最小支持粒度は`1ms`です。規定時間内に[Taskプロセス](/learn?id=taskworkerプロセス)がデータ返回しない場合、`taskwait`は`false`を返し、後続のタスク結果データの処理を続けません。
      * 既定値：0.5
      * 他の値：なし

    * `int $dstWorkerId`

      * 機能：どの[Taskプロセス](/learn?id=taskworkerプロセス)に投じるかを指定できます。Taskプロセスの`ID`を渡すだけでよいのですが、範囲は`[0, $server->setting['task_worker_num']-1]`です。
      * 既定値：-1【デフォルトでは`-1`で、ランダムに選択されます。底層は自動的に空闲の[Taskプロセス](/learn?id=taskworkerプロセス)を選択します】
      * 他の値：`[0, $server->setting['task_worker_num']-1]`

  * **戻り値**

     
```php
// Swoole\Server::finishについては、taskwaitを使用しないでください。
// taskwait方法はtaskプロセス（/learn?id=taskworkerプロセス）で呼び出すことができません。
## taskWaitMulti()

複数のtask 非同期タスクを並行して実行します。この方法は[コ协程スケジュール](/coroutine?id=协程调度)をサポートしていません。そのため、他のコ协程が開始される可能性があります。コ协程環境では、以下のtaskCoを使用する必要があります。

```php
Swoole\Server->taskWaitMulti(array $tasks, float $timeout = 0.5): false|array
```

  * **引数**

    * `array $tasks`

      * 機能：数字索引の配列でなければならず、連想索引の配列はサポートされていません。底層では$tasksを遍歴し、各タスクを[Taskプロセス](/learn?id=taskworkerプロセス)に個別に投送します。
      * 默认値：なし
      * 他の値：なし

    * `float $timeout`

      * 機能：浮点数で、単位は秒です。
      * 默认値：0.5秒
      * 他の値：なし

  * **戻り値**

    * タスクが完了したりタイムアウトしたりした場合、結果の配列が返されます。結果の配列中の各タスクの結果は$tasksに対応しており、例えば$tasks[2]に対応する結果は$result[2]です。
    * あるタスクがタイムアウトしても他のタスクには影響せず、返された結果データにはタイムアウトしたタスクは含まれません。

  * **注意**

  !> 最大同時実行タスク数は1024を超えてはいけません。

  * **例**

```php
$tasks[] = mt_rand(1000, 9999); //タスク1
$tasks[] = mt_rand(1000, 9999); //タスク2
$tasks[] = mt_rand(1000, 9999); //タスク3
var_dump($tasks);

//すべてのTask結果を待って、タイムアウトは10秒です。
$results = $server->taskWaitMulti($tasks, 10.0);

if (!isset($results[0])) {
    echo "タスク1がタイムアウトしました\n";
}
if (isset($results[1])) {
    echo "タスク2の実行結果は{$results[1]}\n";
}
if (isset($results[2])) {
    echo "タスク3の実行結果は{$results[2]}\n";
}
```
## taskCo()

Taskを並行して実行し、[协程调度](/coroutine?id=协程调度)を行うことで、コ协程環境でのtaskWaitMulti機能をサポートします。

```php
Swoole\Server->taskCo(array $tasks, float $timeout = 0.5): false|array
```
  
* `$tasks`タスクリストは、配列でなければなりません。底層では配列を遍歴し、各要素を`task`としてTaskプロセスプールに投送します。
* `$timeout`タイムアウト時間は、默认为`0.5`秒です。指定された時間内にタスクがすべて完了しなかった場合、直ちに中止し結果を返します。
* タスクが完了したりタイムアウトしたりした場合、結果の配列が返されます。結果の配列中の各タスクの結果は$tasksに対応しており、例えば$tasks[2]に対応する結果は$result[2]です。
* タスクが失敗したりタイムアウトしたりした場合、対応する結果配列の項目は`false`になります。例えば、$tasks[2]が失敗した場合、$result[2]の値は`false`になります。

!> 最大同時実行タスク数は1024を超えてはいけません  

  * **スケジュールプロセス**

    * `$tasks`リストの各タスクはランダムにTaskワークプロセスに投送されます。投送が完了した後、`yield`で現在のコ协程を譲り、$timeout秒のタイマーを設定します。
    * `onFinish`で対応するタスクの結果を収集し、結果配列に保存します。すべてのタスクが結果を返したかどうかを判断します。もしそうでなければ、引き続き待ちます。もしそうであれば、対応するコ协程を`resume`して実行を再開し、タイムアウトタイマーをクリアします。
    * 指定された時間内にタスクがすべて完了しなかった場合、タイマーが先にトリガーし、底層で待ち状態をクリアします。完了しなかったタスクの結果を`false`としてマークし、すぐに対応するコ协程を`resume`します。

  * **例**

```php
$server = new Swoole\Http\Server("127.0.0.1", 9502, SWOOLE_BASE);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $serv, $task_id, $worker_id, $data) {
    echo "#{$serv->worker_id}\tonTask: worker_id={$worker_id}, task_id=$task_id\n";
    if ($serv->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "hello world";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('Test End, Result: ' . var_export($result, true));
});

$server->start();
```
## finish()

Taskプロセス（/learn?id=taskworkerプロセス）でWorkerプロセスに通知し、投送されたタスクが完了したことを示します。この関数はWorkerプロセスに結果データを渡すことができます。

```php
Swoole\Server->finish(mixed $data): bool
```

  * **引数**

    * `mixed $data`

      * 機能：タスク処理の結果内容
      * 默认値：なし
      * 他の値：なし

  * **戻り値**

    * 操作が成功した場合は`true`を返し、操作に失敗した場合は`false`を返します。

  * **ヒント**
    * `finish`方法は連続して何度も呼び出すことができます。Workerプロセスは何度も[onFinish](/server/events?id=onfinish)イベントをトリガーします。
    * [onTask](/server/events?id=ontask)回调関数内で`finish`方法を呼び出した後も、`return`データは[onFinish](/server/events?id=onfinish)イベントをトリガーします。
    * `Server->finish`はオプションです。Workerプロセスがタスク実行の結果に関心がない場合は、この関数を呼び出す必要はありません。
    * [onTask](/server/events?id=ontask)回调関数内で`return`文字列を返すことは、`finish`と同等です。

  * **注意**

  !> `Server->finish`関数を使用するためには、Serverに[onFinish](/server/events?id=onfinish)回调関数を設定する必要があります。この関数はTaskプロセスの[onTask](/server/events?id=ontask)回调内でのみ使用できます。
## heartbeat()

[heartbeat_check_interval](/server/setting?id=heartbeat_check_interval)の受動的な検出とは異なり、この方法はサーバー上のすべての接続を積極的に検出し、約定時間を超えた接続を見つけ出します。`if_close_connection`が指定された場合、自動的にタイムアウトした接続を閉じます。指定されていない場合は、接続の`fd`配列のみを返します。

```php
Swoole\Server->heartbeat(bool $ifCloseConnection = true): bool|array
```

  * **引数**

    * `bool $ifCloseConnection`

      * 機能：タイムアウトした接続を閉じるかどうか
      * 默认値：true
      * 他の値：false

  * **戻り値**

    * 成功した呼び出しは、閉じられた`$fd`の連続した配列を返します。
    * 失敗した呼び出しは`false`を返します。

  * **例**

```php
$closeFdArrary = $server->heartbeat();
```
## getLastError()

最近の一回的操作の错误码を取得します。业务コードでは、error码の種類に基づいて異なるロジックを実行することができます。

```php
Swoole\Server->getLastError(): int
```

  * **戻り値**
错误码 | 解释
---|---
1001 | 连接已经被`Server`端关闭了，出现这个错误一般是代码中已经执行了`$server->close()`关闭了某个连接，但仍然调用`$server->send()`向这个连接发送数据
1002 | 连接已被`Client`端关闭了，`Socket`已关闭无法发送数据到对端
1003 | 正在执行`close`，[onClose](/server/events?id=onclose)回调函数中不得使用`$server->send()`
1004 | 连接已关闭
1005 | 连接不存在，传入`$fd` 可能是错误的
1007 | 接收到了超时的数据，`TCP`关闭连接后，可能会有部分数据残留在[unixSocket](/learn?id=什么是IPC)缓存区内，这部分数据会被丢弃
1008 | 发送缓存区已满无法执行`send`操作，出现这个错误表示这个连接的对端无法及时收数据导致发送缓存区已塞满
1202 | 发送的数据超过了 [server->buffer_output_size](/server/setting?id=buffer_output_size) 设置
9007 | 仅在使用[dispatch_mode](/server/setting?id=dispatch_mode)=3时出现，表示当前没有可用的进程，可以调大`worker_num`进程数量
## getSocket()

この方法を呼び出すと、底層の`socket`句柄を取得できます。返されるオブジェクトは`sockets`リソース句柄です。

```php
Swoole\Server->getSocket(): false|\Socket
```

!> この方法ではPHPの`sockets`拡張を依赖しており、Swooleをコンパイルする際には`--enable-sockets`オプションを有効にする必要があります。

  * **监听端口**

    * `listen`方法で増加したポートは、`Swoole\Server\Port`オブジェクトの`getSocket`方法を使用して取得できます。

    ```php
    $port = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $socket = $port->getSocket();
    ```

    * `socket_set_option`関数を使用して、より底層のいくつかの`socket`パラメータを設定できます。

    ```php
    $socket = $server->getSocket();
    if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
        echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
    }
    ```

  * **支持组播**

    * `socket_set_option`で`MCAST_JOIN_GROUP`パラメータを設定することで、Socketをマルチキャストに加入させ、ネットワークマルチキャストデータパケットを受信できます。

```php
$server = new Swoole\Server('0.0.0.0', 9905, SWOOLE_BASE, SWOOLE_SOCK_UDP);
$server->set(['worker_num' => 1]);
$socket = $server->getSocket();

$ret = socket_set_option(
    $socket,
    IPPROTO_IP,
    MCAST_JOIN_GROUP,
    array(
        'group' => '224.10.20.30', // 表示组播地址
        'interface' => 'eth0' // 表示网络接口的名称，可以为数字或字符串，如eth0、wlan0
    )
);

if ($ret === false) {
    throw new RuntimeException('Unable to join multicast group');
}

$server->on('Packet', function (Swoole\Server $server, $data, $addr) {
    $server->sendto($addr['address'], $addr['port'], "Swoole: $data");
    var_dump($addr, strlen($data));
});

$server->start();
```
## protect()

クライアント接続を保護状態に設定し、ヒートビート线程によって切断されないようにします。

```php
Swoole\Server->protect(int $fd, bool $is_protected = true): bool
```

  * **引数**

    * `int $fd`

      * 機能：指定クライアント接続の`fd`
      * 默认値：无
      * 他の値：无

    * `bool $is_protected`

      * 機能：設定する状態
      * 默认値：true 【保護状態】
      * 他の値：false 【非保護】

  * **戻り値**

    * 操作が成功した場合は`true`を返し、操作に失敗した場合は`false`を返します。
## confirm()

接続を確認し、[enable_delay_receive](/server/setting?id=enable_delay_receive)と組み合わせて使用します。クライアントが接続を確立した後、読み取りイベントを監視せず、[onConnect](/server/events?id=onconnect)イベント回调のみをトリガーし、[onConnect](/server/events?id=onconnect)回调内で`confirm`を呼び出して接続を確認します。これにより、サーバーは読み取りイベントを監視し、クライアントからの接続データを受信します。

!> Swooleバージョン >= `v4.5.0` 用可用

```php
Swoole\Server->confirm(int $fd): bool
```

  * **引数**

    * `int $fd`

      * 機能：接続のユニーク識別子
      * 默认値：无
      * 他の値：无

  * **戻り値**
  
    * 確認に成功した場合は`true`を返します。
    * `$fd`に対応する接続が存在しない、すでに閉じられている、または既に監視状態にある場合は、`false`を返し、確認に失敗します。

  * **用途**
  
    この方法は一般的にサーバーを保護するために使用され、流量過負荷攻撃からサーバーを守ることを防ぎます。クライアントからの接続が[onConnect](/server/events?id=onconnect)関数でトリガーされた後、送信元IPを判断し、サーバーにデータを送信することを許可するかどうかを決定できます。

  * **例**
    
```php
//Server对象を作成し、127.0.0.1:9501ポートで待ちます。
$serv = new Swoole\Server("127.0.0.1", 9501); 
$serv->set([
    'enable_delay_receive' => true,
]);

//接続进入イベントを待ちます。
$serv->on('Connect', function ($serv, $fd) {  
    //ここでこの$fdをチェックし、問題がなければconfirmを呼び出します。
    $serv->confirm($fd);
});

//データ受信イベントを待ちます。
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "Server: ".$data);
});

//接続閉じイベントを待ちます。
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

//サーバーを開始します。
$serv->start(); 
```
## getWorkerId()

現在のWorkerプロセスのid（プロセスのPIDではなく）を取得します。これは[onWorkerStart](/server/events?id=onworkerstart)時の$workerIdと一致します。

```php
Swoole\Server->getWorkerId(): int|false
```

!> Swooleバージョン >= `v4.5.0RC1` 用可用
## getWorkerPid()

指定されたWorkerプロセスのPIDを取得します。

```php
Swoole\Server->getWorkerPid(int $worker_id = -1): int|false
```

  * **引数**

    * `int $worker_id`

      * 機能：指定プロセスのpidを取得
      * 默认値：-1、【-1は現在のプロセスを表す】
      * 他の値：无

!> Swooleバージョン >= `v4.5.0RC1` 用可用
## getWorkerStatus()

Workerプロセスの状態を取得します。

```php
Swoole\Server->getWorkerStatus(int $worker_id = -1): int|false
```

!> Swooleバージョン >= `v4.5.0RC1` 用可用

  * **引数**

    * `int $worker_id`

      * 機能：プロセスの状態を取得
      * 默认値：-1、【-1は現在のプロセスを表す
## addCommand()

カスタムコマンド「command」を追加する

```php
Swoole\Server->addCommand(string $name, int $accepted_process_types, Callable $callback): bool
```

!> -Swooleバージョン >= `v4.8.0` での利用可能         
  -この関数はサービスが開始される前にのみ呼び出し可能であり、同名のコマンドが存在すると直接`false`を返す

* **引数**

    * `string $name`

        * 機能：「command」の名前
        * 默认値：なし
        * その他：なし

    * `int $accepted_process_types`

      * 機能：受け入れるプロセスタイプ。複数のプロセスタイプをサポートしたい場合は「|」で接続できます。例えば`SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_MANAGER`
      * 默认値：なし
      * その他：
        * `SWOOLE_SERVER_COMMAND_MASTER` masterプロセス
        * `SWOOLE_SERVER_COMMAND_MANAGER` managerプロセス
        * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` workerプロセス
        * `SWOOLE_SERVER_COMMAND_TASK_WORKER` taskプロセス

    * `callable $callback`

        * 機能：回调関数。この関数は2つの引数を持っています。一つは`Swoole\Server`のクラス、もう一つはユーザー定義の変数です。この変数は`Swoole\Server::command()`の第4引数として渡されます。
        * 默认値：なし
        * その他：なし

* **戻り値**

    * `true`を返す場合、カスタムコマンドを追加成功。`false`を返す場合、失敗。
## command()

定義されたカスタムコマンド「command」を呼び出す

```php
Swoole\Server->command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true): false|string|array
```

!>Swooleバージョン >= `v4.8.0` での利用可能、`SWOOLE_PROCESS`と`SWOOLE_BASE`モードでは、この関数は`master`プロセスでのみ使用可能。  

* **引数**

    * `string $name`

        * 機能：「command」の名前
        * 默认値：なし
        * その他：なし

    * `int $process_id`

        * 機能：プロセスID
        * 默认値：なし
        * その他：なし

    * `int $process_type`

        * 機能：プロセス请求タイプ、以下の他の値はいずれか一つを選択してください。
        * 默认値：なし
        * その他：
          * `SWOOLE_SERVER_COMMAND_MASTER` masterプロセス
          * `SWOOLE_SERVER_COMMAND_MANAGER` managerプロセス
          * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` workerプロセス
          * `SWOOLE_SERVER_COMMAND_TASK_WORKER` taskプロセス

    * `mixed $data`

        * 機能：请求データ、このデータはシリアライズ可能でなければなりません。
        * 默认値：なし
        * その他：なし

    * `bool $json_decode`

        * 機能：`json_decode`を使用して解析するかどうか
        * 默认値：true
        * その他：false
  
  * **使用例**
    ```php
    <?php
    use Swoole\Http\Server;
    use Swoole\Http\Request;
    use Swoole\Http\Response;

    $server = new Server('127.0.0.1', 9501, SWOOLE_BASE);
    $server->addCommand('test_getpid', SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_EVENT_WORKER,
        function ($server, $data) {
          var_dump($data);
          return json_encode(['pid' => posix_getpid()]);
        });
    $server->set([
        'log_file' => '/dev/null',
        'worker_num' => 2,
    ]);

    $server->on('start', function (Server $serv) {
        $result = $serv->command('test_getpid', 0, SWOOLE_SERVER_COMMAND_MASTER, ['type' => 'master']);
        Assert::eq($result['pid'], $serv->getMasterPid());
        $result = $serv->command('test_getpid', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::eq($result['pid'], $serv->getWorkerPid(1));
        $result = $serv->command('test_not_found', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::false($result);

        $serv->shutdown();
    });

    $server->on('request', function (Request $request, Response $response) {
    });
    $server->start();
    ```
