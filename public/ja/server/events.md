# イベント

このセクションでは、Swooleのすべての回调関数を紹介します。各回调関数はPHP関数であり、対応するイベントです。

## onStart

?> **サーバーが起動した後、マスタープロセス（master）の主线程でこの関数が回调されます**

```php
function onStart(Swoole\Server $server);
```

  * **パラメータ** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

* **このイベントが発生する前に`Server`は以下の操作を行っています**

    * 起動してマネージャープロセス[Manager 进程](/learn?id=manager进程)を作成完了
    * 起動してワークERPocess[Worker 子进程](/learn?id=worker进程)を作成完了
    * 全てのTCP/UDP/[unixSocket](/learn?id=什么是IPC)ポートをリッスンしていますが、Accept接続やリクエストを始めることはありません
    * 定時器をリッスンしています

* **これから実行される**

    * メインロード[Reactor](/learn?id=reactor线程)がイベントの受信を始め、クライアントは`connect`して`Server`に接続できます

**`onStart`回调では、`echo`、印刷`Log`、プロセス名を変更するだけでよいです。他の操作（`server`関連関数などの呼び出し）は行ってはいけません（サービスがまだ準備されていないため）。`onWorkerStart`と`onStart`の回调は異なるプロセスで並行して実行されるため、順序は存在しません。**

`onStart`回调では、`$server->master_pid`と`$server->manager_pid`の値をファイルに保存することができます。これにより、これらの`PID`にシグナルを送信してシャットダウンや再起動を実現するためのスクリプトを書くことができます。

`onStart`イベントは`Master`プロセスの主线程で呼び出されます。

!> `onStart`で作成されたグローバルリソースオブジェクトは`Worker`プロセスで使用することはできません。なぜなら、`onStart`が呼び出された時、`worker`プロセスはすでに作成されているからです  
新しく作成されたオブジェクトはメインロード内にあるため、`Worker`プロセスはこのメモリエリアにアクセスすることはできません  
したがって、グローバルオブジェクトの作成コードは`Server::start`の前に置かれるべきです。典型的な例は[Swoole\Table](/memory/table?id=完整示例)です

* **セキュリティヒント**

`onStart`回调では非同期および協程のAPIを使用できますが、これは`dispatch_func`と`package_length_func`と衝突する可能性がありますので、**同時に使用しないでください**。

`onStart`では定时器を開始しないでください。もしコードで`Swoole\Server::shutdown()`操作を実行した場合、常に定时器が実行されているためにプログラムが終了することができません。

`onStart`回调では、返回される前にサーバープログラムはクライアントの接続を受け付けませんので、同期ブロッキング関数は安全に使用できます。

* **BASEモード**

[SWOOLE_BASE](/learn?id=swoole_base)モードでは`master`プロセスは存在しませんので、`onStart`イベントは発生しません。`BASE`モードでは`onStart`回调関数を使用しないでください。

```
WARNING swReactorProcess_start: The onStart event with SWOOLE_BASE is deprecated
```


## onBeforeShutdown

?> **このイベントは`Server`が正常に終了する前に発生します** 

!> Swooleバージョン >= `v4.8.0`で使用できます。このイベントでは協程APIを使用できます。

```php
function onBeforeShutdown(Swoole\Server $server);
```


* **パラメータ**

    * **`Swoole\Server $server`**
        * **機能**：Swoole\Serverオブジェクト
        * **デフォルト値**：なし
        * **その他の値**：なし


## onShutdown

?> **このイベントは`Server`が正常に終了した後に発生します**

```php
function onShutdown(Swoole\Server $server);
```

  * **パラメータ**

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **これ以前に`Swoole\Server`は以下の操作を行っています**

    * 全ての[Reactor](/learn?id=reactor线程)线程、`HeartbeatCheck`线程、`UdpRecv`线程を閉じています
    * 全ての`Worker`プロセス、 [Taskプロセス](/learn?id=taskworker进程)、[Userプロセス](/server/methods?id=addprocess)を閉じています
    * 全ての`TCP/UDP/UnixSocket`监听ポートを`close`しています
    * メインロード[Reactor](/learn?id=reactor线程)を閉じています

  !> 強制的にプロセスを`kill`することは`onShutdown`を回调しません。例えば`kill -9`のように  
  正常なプロセス終了のためにメインロードに`SIGTERM`シグナルを送信するためには`kill -15`を使用する必要があります  
  コマンドラインで`Ctrl+C`でプログラムを中断すると、すぐに停止し、基層では`onShutdown`が回调されません

  * **注意点**

  !> `onShutdown`では、异步または协程関連の`API`を呼び出さないでください。`onShutdown`が触发された時、基層はすべてのイベントループ施設を破壊しています。  
この時点で协程環境は存在しません。開発者が协程関連の`API`を使用する必要がある場合は、手動で`Co\run`を呼び出して[协程容器](/coroutine?id=什么是协程容器)を作成する必要があります。


## onWorkerStart

?> **このイベントは Workerプロセス/ [Taskプロセス](/learn?id=taskworker进程)が起動した時に発生し、ここで作成されたオブジェクトはプロセスのライフサイクル内に使用できます。**

```php
function onWorkerStart(Swoole\Server $server, int $workerId);
```

  * **パラメータ** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $workerId`**
      * **機能**：`Worker` 进程 `id`（プロセスの PIDではなく）
      * **デフォルト値**：なし
      * **その他の値**：なし

  * `onWorkerStart/onStart`は並行して実行されるため、順序はありません
  * `$server->taskworker`属性を通じて、現在が`Worker`プロセスか [Taskプロセス](/learn?id=taskworker进程)かを判断できます
  * `worker_num`と`task_worker_num`が`1`を超えている場合、各プロセスは一度ずつ`onWorkerStart`イベントがトリガーされます。[$worker_id](/server/properties?id=worker_id)を通じて異なるワークプロセスを識別できます
  * `worker`プロセスから`task`プロセスにタスクを送信し、`task`プロセスが全てのタスクを処理した後、[onFinish](/server/events?id=onfinish)回调関数を通じて`worker`プロセスに通知します。例えば、バックグラウンドで十万人のユーザーに通知メールを群发する場合、操作が完了した後に操作の状態は「送信中」と表示されます。この時、他の操作を続けることができ、メール群发が完了すると、操作の状態は自動的に「送信済み」に変わります。

  以下の例は、Workerプロセス/ [Taskプロセス](/learn?id=taskworker进程)の名前を変更するためのものです。

```php
$server->on('WorkerStart', function ($server, $worker_id){
    global $argv;
    if($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});
```

  [Reload](/server/methods?id=reload)メカニズムを使用してコードを再読み込む場合、`onWorkerStart`の中でビジネスファイルを`require`する必要があります。ファイルの最初に含まれることはできません。`onWorkerStart`の呼び出し前にすでに含まれているファイルは、コードを再読み込むことはありません。

  公用的で変わらないPHPファイルを`onWorkerStart`の前に置くことができます。こうするとコードを再読み込むことはできませんが、すべての`Worker`は共有されているため、これらのデータを保存するための追加のメモリは必要ありません。
`onWorkerStart`の後のコードは、各プロセスごとにメモリに保存する必要があります

  * `$worker_id`は、この`Worker`プロセスの`ID`を表します。その範囲は[$worker_id](/server/properties?id=worker_id)を参照してください
  * [$worker_id](/server/properties?id=worker_id)とプロセスの`PID`は関係ありません。`posix_getpid`関数を使用して`PID`を取得することができます

  * **协程サポート**

    * `onWorkerStart`回调関数では自動的に协程が作成されるため、`onWorkerStart`では协程`API`を呼び出すことができます

  * **注意**

    !> 致命的なエラーが発生したり、コード内で`exit`を主动的に呼び出したりした場合、`Worker/Task`プロセスは終了し、管理プロセスは新しいプロセスを再構築します。これにより、プロセスの作成と破壊を絶えず繰り返す死のループを引き起こす可能性があります
## onWorkerStop

?> **このイベントは`Worker`プロセスが終了した時に発生します。この関数では`Worker`プロセスが申請した各種リソースを回収することができます。**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $workerId`**
      * **機能**：`Worker` プロセスの `id`（プロセスの PIDではなく）
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **注意**

    !> -プロセスが異常で終了した場合（例えば強制的に`kill`されたり、致命的なエラーや`core dump`が発生した場合）、`onWorkerStop`回调関数は実行されません。  
    - `onWorkerStop`では、非同期または協程関連の`API`を呼び出さないでください。`onWorkerStop`がトリガーされた時、基層はすでにすべての[イベントループ](/learn?id=什么是eventloop)施設を破壊しています。


## onWorkerExit

?> **[reload_async](/server/setting?id=reload_async)特性が有効にされている場合にのみ機能します。正しいサービスの再起動方法については[こちら](/question/use?id=swoole如何正确的重启服务)を参照してください**

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $workerId`**
      * **機能**：`Worker` プロセスの `id`（プロセスの PIDではなく）
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **注意**

    !> -`Worker`プロセスが正常に退出していない場合、`onWorkerExit`は継続してトリガーされます  
    -`onWorkerExit`は`Worker`プロセス内でトリガーされ、[Taskプロセス](/learn?id=taskworker进程)内に[イベントループ](/learn?id=什么是eventloop)が存在する場合もトリガーされます  
    -`onWorkerExit`では、できるだけ非同期の`Socket`接続を移除/閉じるようにしてください。最終的に基層は[イベントループ](/learn?id=什么是eventloop)内のイベントハンドルの数を`0`と検出した時にプロセスを退出させます  
    -プロセスがイベントハンドルを 监听していない場合、プロセスが終わる時にこの関数は回调されません  
    - `Worker`プロセスの退出を待ってから`onWorkerStop`イベントの回调が実行されます


## onConnect

?> **新しい接続が入った時に、workerプロセス内で回调されます。**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $fd`**
      * **機能**：接続のファイル記述子
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $reactorId`**
      * **機能**：接続が存在する[Reactor](/learn?id=reactor线程)プロセスの `ID`
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **注意**

    !> `onConnect/onClose`の2つの回调は`Worker`プロセス内で発生し、メインプロセスではありません。  
    `UDP`プロトコル下では[onReceive](/server/events?id=onreceive)イベントのみがあり、`onConnect/onClose`イベントはありません。

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

      * このモードでは`onConnect/onReceive/onClose`が異なるプロセスに配送される可能性があります。接続に関連するPHPオブジェクトデータは、[onConnect](/server/events?id=onconnect)回调で初期化されたデータや[onClose](/server/events?id=onclose)で清理されたデータを実現できません。
      * `onConnect/onReceive/onClose`の3つのイベントは同時に実行される可能性があり、異常を引き起こす可能性があります。


## onReceive

?> **データを受け取った時にこの関数が回调され、workerプロセス内で発生します。**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $fd`**
      * **機能**：接続のファイル記述子
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $reactorId`**
      * **機能**：`TCP`接続が存在する[Reactor](/learn?id=reactor线程)プロセスの `ID`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $data`**
      * **機能**：受け取ったデータの内容であり、テキストまたはバイナリコンテンツである可能性があります
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **TCPプロトコル下のパケット完全性について**

    * 下層が提供する`open_eof_check/open_length_check/open_http_protocol`などの設定を使用することでパケットの完全性を保証できます。
    * 下層のプロトコル処理不使用で、[onReceive](/server/events?id=onreceive)後にPHPコード内でデータ分析を行い、パケットを結合/分割することができます。

    例えば：コード内で `$buffer = array()`を増加させ、`$fd`を`key`として上下文データを保存します。毎回データを受け取って文字列を結合し、`$buffer[$fd] .= $data`となります。その後、`$buffer[$fd]`の文字列が完全なパケットであるかどうかを判断します。

   デフォルトでは、同じ`fd`は同じ`Worker`に割り当てられるため、データは結合することができます。[dispatch_mode](/server/setting?id=dispatch_mode) = 3を使用すると、要求データは先行抢占式であり、同じ`fd`からのデータは異なるプロセスに分割される可能性があります。したがって、上記のパケット結合方法は使用できません。

  * **マルチポート监听について**

    メインブラットフォームがプロトコルを設定した後、追加で监听されるポートはデフォルトでメインブラットフォームの設定を継承します。ポートのプロトコルを再設定する必要がある場合は、`set`方法を显式的に呼び出してください。    

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9501);
    $port2 = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $port2->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        echo "[#".$server->worker_id."]\tClient[$fd]: $data\n";
    });
    ```

    ここでは`on`方法で[onReceive](/server/events?id=onreceive)回调関数を登録しましたが、メインブラットフォームのプロトコルを覆すために`set`方法を呼び出していませんが、新しく监听された`9502`ポートは依然として`HTTP`プロトコルを使用しています。`telnet`クライアントで`9502`ポートに接続して文字列を送信すると、サーバーは[onReceive](/server/events?id=onreceive)をトリガーしません。

  * **注意**

    !> 自動プロトコルオプションが有効になっていない場合、`onReceive](/server/events?id=onreceive)`で一度に受け取るデータの最大値は`64K`です  
    自動プロトコル処理オプションが有効にされている場合、`onReceive](/server/events?id=onreceive)`で完全なパケットを受け取り、最大は[package_max_length](/server/setting?id=package_max_length)を超えません  
    二进制フォーマットをサポートしており、`$data`はバイナリデータである可能性があります
## onPacket

?> **UDPデータパックを受信した際にこの関数が呼びられます。これはworkerプロセスで発生します。**

```php
function onPacket(Swoole\Server $server, string $data, array $clientInfo);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $data`**
      * **機能**：受信したデータの内容。テキストやバイナリ都有可能です。
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`array $clientInfo`**
      * **機能**：クライアント情報には、アドレス/ポート/サーバーソケットなど、多くのクライアント情報データが含まれています。[UDPサーバー](/start/start_udp_server)を参照してください。
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **注意**

    !> TCP/UDPポートを同時に監視している場合、TCPプロトコルのデータを受信すると[onReceive](/server/events?id=onreceive)が呼びられ、UDPデータパックを受信すると`onPacket`が呼びられます。サーバーで設定されたEOFやLengthなどの自動プロトコル処理([TCPデータパックの境界問題](/learn?id=tcp数据包边界问题)を参照)はUDPポートには無効です。なぜなら、UDPパケットにはメッセージの境界があり、追加のプロトコル処理が必要ないからです。


## onClose

?> **TCPクライアントの接続が閉まった後、workerプロセスでこの関数が呼びられます。**

```php
function onClose(Swoole\Server $server, int $fd, int $reactorId);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $fd`**
      * **機能**：接続のファイル記述子
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $reactorId`**
      * **機能**：どのreactorスレッドから来たのか。積極的にcloseして閉じた場合は負数です。
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **ヒント**

    * **積極的な閉鎖**

      * サーバーが積極的に接続を閉じる場合、下層はこのパラメータを`-1`に設定します。`$reactorId < 0`来判断することで、閉鎖がサーバー側から始まったのかクライアント側から始まったのかを区別できます。
      * PHPコードで積極的に`close`方法を呼び出すことが積極的な閉鎖と見なされます。

    * ** Heartbeat検出**

      * [Heartbeat検出](/server/setting?id=heartbeat_check_interval)はHeartbeat検出スレッドが閉鎖を通知し、閉鎖時には[onClose](/server/events?id=onclose)の`$reactorId`パラメータは`-1`ではありません。

  * **注意**

    !> - [onClose](/server/events?id=onclose)回调関数が致命的なエラーが発生して退出したり、外部プロセスに強制的に`kill`されたりした場合、現在のタスクは破棄されますが、他のキュー中のタスクには影響しません。
    -クライアントが`close`を呼び出したり、サーバー側が`$server->close()`を呼び出して接続を閉じたりすると、このイベントがトリガーされます。したがって、接続が閉じられると必ずこの関数が呼びられます。  
    - [onClose](/server/events?id=onclose)回调関数内で[getClientInfo](/server/methods?id=getClientInfo)メソッドを呼び出して接続情報を取得することができますが、`onClose](/server/events?id=onclose)`回调関数が実行された後でなければ`close`を呼び出してTCP接続を閉じることはありません。  
    - ここで[onClose](/server/events?id=onclose)が回调されるということは、クライアントの接続がすでに閉じられているということなので、`$server->close($fd)`を実行する必要はありません。コード内で`$server->close($fd)`を実行するとPHPエラー警告が抛出されます。


## onTask

?> **taskプロセス内で呼び出されます。workerプロセスは[task](/server/methods?id=task)関数を使用してtask_workerプロセスに新しいタスクを投じることができます。現在の [Taskプロセス](/learn?id=taskworker进程)は[onTask](/server/events?id=ontask)回调関数を呼び出す際にプロセス状態を忙碌に切り替えます。この時、新しいTaskは受け付けられなくなりますが、[onTask](/server/events?id=ontask)関数が戻るとプロセス状態を空闲に切り替えて再び新しい`Task`を受け付けます。**

```php
function onTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $task_id`**
      * **機能**：タスクを実行する `task` プロセスの `id`【`$task_id`と`$src_worker_id`を組み合わせて初めてグローバルに唯一です。異なる`worker`プロセスが投じるタスクの`ID`は同じになる可能性があります】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $src_worker_id`**
      * **機能**：タスクを投じる `worker` プロセスの `id`【`$task_id`と`$src_worker_id`を組み合わせて初めてグローバルに唯一です。異なる`worker`プロセスが投じるタスクの`ID`は同じになる可能性があります】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`mixed $data`**
      * **機能**：タスクのデータ内容
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **ヒント**

    * **v4.2.12から [task_enable_coroutine](/server/setting?id=task_enable_coroutine)が有効にすると回调関数の原型は以下のようになります**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); //タスクを完了し、結果を終了して返す
      });
      ```

    * **workerプロセスに実行結果を戻す**

      * **[onTask](/server/events?id=ontask)関数内で `return` 文字列を使用すると、この内容を `worker` プロセスに戻します。`worker` プロセスでは [onFinish](/server/events?id=onfinish) 関数がトリガーされ、投じられた `task` が完了したことを示します。もちろん、`Swoole\Server->finish()` を使用して [onFinish](/server/events?id=onfinish) 関数をトリガーすることもできますが、それには `return` を必要としません**

      * `return`する変数は、任意の非 `null` PHP変数です。

  * **注意**

    !> **[onTask](/server/events?id=ontask)関数が致命的なエラーで退出したり、外部プロセスに強制的に`kill`されたりした場合、現在のタスクは破棄されますが、他のキュー中のタスクには影響しません。**


## onFinish

?> **この回调関数はworkerプロセスで呼び出されます。workerプロセスが投じたタスクがtaskプロセスで完了したとき、 [taskプロセス](/learn?id=taskworker进程)は`Swoole\Server->finish()`メソッドを通じてタスク処理の結果をworkerプロセスに送信します。**

```php
function onFinish(Swoole\Server $server, int $task_id, mixed $data)
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $task_id`**
      * **機能**：タスクを実行する `task` プロセスの `id`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`mixed $data`**
      * **機能**：タスク処理の結果内容
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **注意**

    !> - [taskプロセス](/learn?id=taskworker进程)の[onTask](/server/events?id=ontask)イベントで`finish`メソッドを呼び出さなかったり、結果を`return`しなかったりした場合、workerプロセスは[onFinish](/server/events?id=onfinish)をトリガーしません。  
    - [onFinish](/server/events?id=onfinish)ロジックを実行するworkerプロセスは、タスクを投じたworkerプロセスと同じです。
## onPipeMessage

?> **ワークプロセスが `$server->sendMessage()` によって送信された[unixSocket](/learn?id=何 IPC)メッセージを受け取ると `onPipeMessage` イベントがトリガーされます。`worker/task` プロセスはいずれも `onPipeMessage` イベントをトリガーする可能性があります**

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

  * **パラメータ** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $src_worker_id`**
      * **機能**：メッセージがどの `Worker` プロセスから来たか
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`mixed $message`**
      * **機能**：メッセージの内容は、どんなPHPタイプでもよい
      * **デフォルト値**：なし
      * **その他の値**：なし


## onWorkerError

?> **`Worker/Task` プロセスが例外を発生させた後、`Manager` プロセス内でこの関数が回调されます。**

!> この関数は主にアラームとモニタリングに使用され、Workerプロセスが異常で終了したことが確認された場合、それは致命的なエラーやプロセスのCore Dumpが発生した可能性が高いことを意味します。ログを記録したり、アラーム情報を送信して開発者に対応を促すことができます。

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

  * **パラメータ** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $worker_id`**
      * **機能**：異常 `worker` プロセスの `id`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $worker_pid`**
      * **機能**：異常 `worker` プロセスの `pid`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $exit_code`**
      * **機能**：退出の状態コード、範囲は `0～255`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $signal`**
      * **機能**：プロセスが退出したシグナル
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **一般的なエラー**

    * `signal = 11`：これは `Worker` プロセスが `segment fault` 段错误を発生させたことを示しており、基盤の `BUG`をトリガーした可能性があります。core dump情報とvalgrindのメモリ検出ロギングを収集し、[Swoole開発チームにこの問題を報告](/other/issue)してください。
    * `exit_code = 255`：これはWorkerプロセスがFatal Error致命的なエラーを発生させたことを示しています。PHPのエラーログをチェックし、問題のあるPHPコードを見つけ、解決してください。
    * `signal = 9`：これは `Worker`がシステムによって強制的に `Kill`されたことを示しています。人为的な `kill -9` 操作があるかどうかを確認し、dmesg情報に `OOM（Out of memory）`が存在するかどうかを確認してください。
    * OOMがある場合、過大なメモリが割り当てられています。1. Serverのsetting設定をチェックし、socket_buffer_size](/server/setting?id=socket_buffer_size)などが過大に割り当てられているかどうかを確認してください；2.非常に大きな[Swoole\Table](/memory/table)メモリモジュールが作成されているかどうかを確認してください。


## onManagerStart

?> **マネージャープロセスが起動したときにこのイベントがトリガーされます**

```php
function onManagerStart(Swoole\Server $server);
```

  * **ヒント**

    * この回调関数では、マネージャープロセスの名前を変更することができます。
    * `4.2.12` 以前のバージョンでは、managerプロセスにはタイマーを追加したり、taskタスクを投递したり、协程を使用することはできません。
    * `4.2.12` 以降のバージョンでは、managerプロセスはシグナルベースの同期モードタイマーを使用できます
    * managerプロセスでは、[sendMessage](/server/methods?id=sendMessage)インターフェースを呼び出して他のワークプロセスにメッセージを送信することができます

    * **起動順序**

      * `Task`と`Worker`プロセスが既に作成されています
      * `Master`プロセスの状態は不明です。なぜなら`Manager`と`Master`は並行しており、`onManagerStart`回调が発生しても`Master`プロセスが準備ができていないかどうかは確定できません

    * **BASEモード**

      * [SWOOLE_BASE](/learn?id=swoole_base)モードで、`worker_num`、`max_request`、`task_worker_num`パラメータが設定されている場合、基層はmanagerプロセスを作成してワークプロセスを管理します。そのため、`onManagerStart`と`onManagerStop`イベントの回调がトリガーされます。


## onManagerStop

?> **マネージャープロセスが終了したときにこのイベントがトリガーされます**

```php
function onManagerStop(Swoole\Server $server);
```

 * **ヒント**

  * `onManagerStop`がトリガーされたということは、`Task`と`Worker`プロセスが実行を終了し、`Manager`プロセスによって回収されたことを意味します。


## onBeforeReload

?> **ワークプロセスの`Reload`の前にこのイベントがトリガーされ、Managerプロセス内で回调されます**

```php
function onBeforeReload(Swoole\Server $server);
```

  * **パラメータ**

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし


## onAfterReload

?> **ワークプロセスの`Reload`の後にこのイベントがトリガーされ、Managerプロセス内で回调されます**

```php
function onAfterReload(Swoole\Server $server);
```

  * **パラメータ**

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし


## イベントの実行順序

* 全てのイベント回调は `$server->start` の後に行われます
* サーバーが閉じてプログラムが終了したときの最後のイベントは `onShutdown` です
* サーバーが起動して成功した場合、`onStart/onManagerStart/onWorkerStart` は異なるプロセス内で並行して実行されます
* `onReceive/onConnect/onClose` は `Worker` プロセス内でトリガーされます
* `Worker/Task` プロセスが起動/終了する際にはそれぞれ一度ずつ `onWorkerStart/onWorkerStop` が呼び出されます
* [onTask](/server/events?id=ontask) イベントは [taskプロセス](/learn?id=taskworkerプロセス) 内でのみ発生します
* [onFinish](/server/events?id=onfinish) イベントは `worker` プロセス内でのみ発生します
* `onStart/onManagerStart/onWorkerStart`の3つのイベントの実行順序は不確定です

## 面向对象スタイル

[event_object](/server/setting?id=event_object)を有効にした後、以下のイベント回调のパラメータが変更されます。

* クライアント接続 [onConnect](/server/events?id=onconnect)
```php
$server->on('Connect', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* 受信データ [onReceive](/server/events?id=onreceive)
```php
$server->on('Receive', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* 接続閉鎖 [onClose](/server/events?id=onclose)
```php
$server->on('Close', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```


* UDPパケット受信 [onPacket](/server/events?id=onpacket)
```php
$server->on('Packet', function (Swoole\Server $serv, Swoole\Server\Packet $object) {
    var_dump($object);
});
```


* プロセス間通信 [onPipeMessage](/server/events?id=onpipemessage)
```php
$server->on('PipeMessage', function (Swoole\Server $serv, Swoole\Server\PipeMessage $msg) {
    var_dump($msg);
    $object = $msg->data;
    $serv->sendto($object->address, $object->port, $object->data, $object->server_socket);
});
```


* プロセスエラー [onWorkerError](/server/events?id=onworkererror)
```php
$serv->on('WorkerError', function (Swoole\Server $serv, Swoole\Server\StatusInfo $info) {
    var_dump($info);
});
```


* taskプロセスがタスクを受け取る [onTask](/server/events?id=ontask)
```php
$server->on('Task', function (Swoole\Server $serv, Swoole\Server\Task $task) {
    var_dump($task);
});
```


* workerプロセスがtaskプロセスの処理結果を受け取る [onFinish](/server/events?id=onfinish)
```php
$server->on('Finish', function (Swoole\Server $serv, Swoole\Server\TaskResult $result) {
    var_dump($result);
});
```

* [Swoole\Server\Event](/server/event_class)
* [Swoole\Server\Packet](/server/packet_class)
* [Swoole\Server\PipeMessage](/server/pipemessage_class)
* [Swoole\Server\StatusInfo](/server/statusinfo_class)
* [Swoole\Server\Task](/server/task_class)
* [Swoole\Server\TaskResult](/server/taskresult_class)
