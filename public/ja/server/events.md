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

* **このイベント之前に`Server`は以下の操作を行っています**

    * 起動してマスタープロセス（Managerプロセス）の作成が完了しました[Managerプロセス](/learn?id=managerプロセス)
    * 起動してワークプロセス（Workerプロセス）の作成が完了しました[Workerプロセス](/learn?id=workerプロセス)
    * 全てのTCP/UDP/[unixSocket](/learn?id=何 IPC)ポートをlistenしているが、Accept接続やリクエストを開始していません
    * 定時器をlistenしています

* **これから実行される**

    * メインロード[Reactor](/learn?id=reactorプロセス)がイベントの受信を始め、クライアントは`connect`して`Server`に接続できます

**`onStart`回调では、`echo`、印刷`Log`、プロセス名を変更するしか許されていません。他の操作（`server`関連関数などの呼び出し）は行ってはいけません（サービスがまだ準備されていないため）。`onWorkerStart`と`onStart`の回调は異なるプロセスで並行して実行されるため、順序は存在しません。**

`onStart`回调では、`$server->master_pid`と`$server->manager_pid`の値をファイルに保存することができます。これにより、これらの`PID`にシグナルを送信してシャットダウンや再起動を実現するためのスクリプトを書くことができます。

`onStart`イベントは`Master`プロセスの主线程で呼び出されます。

!> `onStart`で作成されたグローバルリソースオブジェクトは`Worker`プロセスでは使用できません。なぜなら、`onStart`が呼び出された時、`worker`プロセスはすでに作成されているからです  
新しく作成されたオブジェクトはメインロード内にあるため、`Worker`プロセスはこのメモリエリアにアクセスすることはできません  
したがって、グローバルオブジェクトの作成コードは`Server::start`の前に置かれるべきです。典型的な例は[Swoole\Table](/memory/table?id=完全な例)です

* **セキュリティ注意**

`onStart`回调では非同期およびコ协程のAPIを使用できますが、これは`dispatch_func`と`package_length_func`と衝突する可能性がありますので、**同時に使用しないでください**。

`onStart`では定时器を起動しないでください。もしコードで`Swoole\Server::shutdown()`操作を実行した場合、常に定时器が実行されているためにプログラムは終了することができません。

`onStart`回调は`return`の前にサーバープログラムはクライアント接続を受け付けていませんが、同期ブロッキング関数は安全に使用できます。

* **BASEモード**

[SWOOLE_BASE](/learn?id=swoole_base)モードでは`master`プロセスは存在しませんので、`onStart`イベントは発生しません。`BASE`モードでは`onStart`回调関数を使用しないでください。

```
WARNING swReactorProcess_start: The onStart event with SWOOLE_BASE is deprecated
```
## onBeforeShutdown

?> **このイベントは`Server`が正常に終了する前に発生します** 

!> Swooleバージョン >= `v4.8.0`で利用できます。このイベントではコ协程APIを使用できます。

```php
function onBeforeShutdown(Swoole\Server $server);
```


* **パラメータ**

    * **`Swoole\Server $server`**
        * **機能**：Swoole\Serverオブジェクト
        * **デフォルト値**：なし
        * **その他の値**：なし
## onShutdown

?> **このイベントは`Server`が正常に終了した時に発生します**

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
    * 全ての`Worker`プロセス、 [Taskプロセス](/learn?id=taskworkerプロセス)、[Userプロセス](/server/methods?id=addprocess)を閉じています
    * 全ての`TCP/UDP/UnixSocket`监听ポートを`close`しています
    * メインロード[Reactor](/learn?id=reactor线程)を閉じています

  !> プロセスを強制的に`kill`することは`onShutdown`を回调しません。例えば`kill -9`のように  
  正常なプロセス終了には、メインロードに`SIGTERM`シグナルを送信する`kill -15`を使用する必要があります  
  コマンドラインで`Ctrl+C`でプログラムを中断すると、すぐに停止し、下層では`onShutdown`が回调されません

  * **注意点**

  !> `onShutdown`では、いかなる非同期または协程関連の`API`も呼び出さないでください。`onShutdown`がトリガーされた時、下層はすべてのイベントループ施設を破壊しています。  
この時点で、协程環境は存在しません。開発者が协程関連の`API`を使用する必要がある場合は、手動で`Co\run`を呼び出して[协程コンテナ](/coroutine?id=什么是协程容器)を作成する必要があります。
## onWorkerStart

?> **このイベントは Workerプロセス/ [Taskプロセス](/learn?id=taskworkerプロセス)が起動した時に発生し、ここで作成されたオブジェクトはプロセスのライフサイクル内で使用できます。**

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
  * `$server->taskworker`プロパティを通じて、現在が`Worker`プロセスか [Taskプロセス](/learn?id=taskworkerプロセス)かを判断できます
  * `worker_num`と`task_worker_num`が`1`を超えている場合、各プロセスは一度ずつ`onWorkerStart`イベントがトリガーされます。[$worker_id](/server/properties?id=worker_id)を判断することで、異なるワークプロセスを区別できます
  * `worker`プロセスから`task`プロセスにタスクを送信し、`task`プロセスが全てのタスクを処理した後、[onFinish](/server/events?id=onfinish)回调関数を通じて`worker`プロセスに通知します。例えば、バックグラウンドで十万人のユーザーに通知メールを群发した場合、操作が完了した後の状態は「送信中」と表示されます。この時、他の操作を続けることができ、メール群发が完了すると、操作の状態は自動的に「送信済み」に変わります。

  以下の例は、Workerプロセス/ [Taskプロセス](/learn?id=taskworkerプロセス)の名前を変更するためです。

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

  [Reload](/server/methods?id=reload)メカニズムを使用してコードを再読み込む場合、`onWorkerStart`の中でビジネスファイルを`require`する必要があります。ファイルの最初に含まれることはできません。`onWorkerStart`の呼び出し前に含まれたファイルは、コードを再読み込むことはありません。

  公用的で変化しないPHPファイルを`onWorkerStart`の前に置くことができます。こうするとコードを再読み込むことはできませんが、すべての`Worker`は共有されているため、これらのデータを保存するための追加のメモリは必要ありません。
`onWorkerStart`の後のコードは、各プロセスでメモリに一つずつ保存する必要があります

  * `$worker_id`は、この`Worker`プロセスの`ID`を表します。範囲は[$worker_id](/server/properties?id=worker_id)を参照してください
  * [$worker_id](/server/properties?id=worker_id)とプロセスの`PID`は関係ありません。`posix_getpid`関数を使用して`PID`を取得できます

  * **协程サポート**

    * `onWorkerStart`回调関数では自動的に协程が作成されるため、`onWorkerStart`では协程`API`を呼び出すことができます

  * **注意**

    !> 致命的なエラーが発生したり、コードで`exit`を主动的に呼び出したりした場合、`Worker/Task`プロセスは終了し、管理プロセスは新しいプロセスを再作成します。これにより、プロセスが絶えず作成され、破壊される死のループを引き起こす可能性があります
## onWorkerStop

?> **このイベントは`Worker`プロセスが終了した時に発生します。この関数では、`Worker`プロセスが申請した各種リソースを回収することができます。**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
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

  * **注意**

    !> -プロセスが異常に終了した場合（例えば強制的に`kill`されたり、致命的なエラーが発生したり、`core dump`したなど）、`onWorkerStop`回调関数は実行されません。  
    - `onWorkerStop`では、いかなる非同期または协程関連の`API`も呼び出さないでください。`onWorkerStop`がトリガーされた時、下層はすべての[イベントループ](/learn?id=什么是eventloop)施設を破壊しています。
## onWorkerExit

?> **[reload_async](/server/setting?id=reload_async)特性が有効にされている場合にのみ有効です。 [サービスを正しく再起動する方法](/question/use?id=swoole如何正确的重启服务)を参照してください**

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
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

  * **注意**

    !> -`Worker`プロセスが終了していないため、`onWorkerExit`は継続してトリガーされます  
    -`onWorkerExit`は`Worker`プロセス内でトリガーされ、 [Taskプロセス](/learn?id=taskworkerプロセス)内で[イベントループ](/learn?id=什么是eventloop)が存在する場合もトリガーされます  
    -`onWorkerExit`ではできるだけ非同期の`Socket`接続を移除/閉じてください。最終的に、下層が[イベントループ](/learn?id=什么是eventloop)内のイベント监听ハンドルの数を`0`と検出した時点でプロセスを終了します  
    -プロセスにイベントハンドルが监听していない場合、プロセスが終わるとこの関数は回调されません  
    - `Worker`プロセスの終了を待ってから`onWorkerStop`イベント回调が実行されます
## onConnect

?> **新しい接続が入った時に、workerプロセスで回调されます。**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

  * **パラメータ** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $fd`**
      * **機能**：接続のファイル記述子
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $reactorId`**
      * **機能**：接続がある[Reactor](/learn?id=reactor线程)プロセスのID
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **注意**

    !> `onConnect/onClose`の2つの回调は`Worker`プロセス内で発生し、マスタープロセスではありません。  
    `UDP`プロトコルの下では[onReceive](/server/events?id=onreceive)イベントしか発生せず、`onConnect/onClose`イベントは発生しません

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

      * このモードでは`onConnect/onReceive/onClose`は異なるプロセスに投递される可能性があります。接続関連のPHPオブジェクトデータは、`onConnect](/server/events?id=onconnect)回调で初期化されたデータや`onClose](/server/events?id=onclose)で 清理されたデータを実現することはできません。[onConnect](/server/events?id=onconnect)回调で初期化されたデータや`onClose](/server/events?id=onclose)で 清理されたデータを実現することはできません。
      * `onConnect/onReceive/onClose`の3つのイベントは並行して実行される可能性があり、異常を引き起こす可能性があります。
## onReceive

?> **データを受け取った時にこの関数が回调され、workerプロセス内で発生します。**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

  * **パラメータ** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $fd`**
      * **機能**：接続のファイル記述子
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $reactorId`**
      * **機能**：TCP接続がある[Reactor](/learn?id=reactor线程)プロセスのID
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $data`**
      * **機能**：受け取ったデータの内容で、テキストであれバイナリであれ
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **TCPプロトコルのパケット完全性について**

    * 下層が提供する`open_eof_check/open_length_check/open_http_protocol`などの設定を使用することで、データパケットの完全性を保証できます
    * 下層のプロトコル処理を使用せず、`onReceive](/server/events?id=onreceive)の後でPHPコードでデータ分析を行い、データパケットを分割/合体させることができます。

    例えば、コードでは `$buffer = array()` 初始化し、`$fd`をkeyとして上下文データを保存します。毎回データを受け取ると、`$buffer[$fd] .= $data`と拼接し、その後 `$buffer[$fd]`が完全なデータパケットかどうかを判断します。

    デフォルトでは、同じ`fd`は同じ`Worker`に割り当てられますので、データは拼接することができます。[dispatch_mode](/server/setting?id=dispatch_mode) = 3の場合、リクエストデータは先行抢占式であり、同じ`fd`からのデータは異なるプロセスに分かれる可能性があります。そのため、上記のデータパケット拼接方法は使用できません。

  * **マルチポート监听について**

    メインノードがプロトコルを設定した後、追加で监听されたポートはデフォルトでメインノードの設定を継承します。ポートのプロトコルを再設定する必要がある場合は、`set`方法を明示的に呼び出してください。    

    ```php
   
## onPacket

?> **UDPデータパックを受信した時にこの関数が回调され、`worker`プロセスで発生します。**

```php
function onPacket(Swoole\Server $server, string $data, array $clientInfo);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $data`**
      * **機能**：受信したデータの内容、テキストまたはバイナリ内容かもしれません
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`array $clientInfo`**
      * **機能**：クライアント情報には、`address/port/server_socket`など、クライアントに関するさまざまな情報が含まれています。[UDPサーバー](/start/start_udp_server)を参照してください
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **注意**

    !> サーバーが`TCP/UDP`ポートを同時に监听している場合、`TCP`プロトコルのデータを受信すると[onReceive](/server/events?id=onreceive)が回调され、`UDP`データパックを受信すると`onPacket`が回调されます。サーバーが設定した`EOF`や`Length`などの自動プロトコル処理([TCPデータパックの境界問題](/learn?id=tcpデータパックの境界問題)を参照)は`UDP`ポートには無効です。なぜなら`UDP`パケットにはメッセージの境界があり、追加のプロトコル処理が必要ないからです。
## onClose

?> **TCPクライアントの接続が閉まった後、`Worker`プロセスでこの関数が回调されます。**

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
      * **機能**：どの`reactor`スレッドから来たのか、積極的に`close`した場合は負数です
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **ヒント**

    * **積極的な閉鎖**

      * サーバーが積極的に接続を閉じた場合、下層はこのパラメータを`-1`に設定します。`$reactorId < 0`来判断して、閉鎖がサーバー側から始まったのかクライアント側から始まったのかを区別できます。
      * PHPコードで積極的に`close`方法を呼び出すことだけが、積極的な閉鎖と見なされます。

    * **Heartbeat検出**

      * [Heartbeat検出](/server/setting?id=heartbeat_check_interval)はHeartbeat検出スレッドが閉鎖を通知し、閉鎖時には[onClose](/server/events?id=onclose)の`$reactorId`パラメータは`-1`ではありません。

  * **注意**

    !> -[onClose](/server/events?id=onclose)回调関数が致命的なエラーが発生した場合、接続が漏れる可能性があります。`netstat`コマンドを実行すると、多くの`CLOSE_WAIT`状態の`TCP`接続が見られます。
    -クライアントが`close`を呼び出したり、サーバー側が`$server->close()`を呼び出して接続を閉じたりすると、このイベントがトリガーされます。したがって、接続が閉じられると必ずこの関数が回调されます。  
    - [onClose](/server/events?id=onclose)の中で[getClientInfo](/server/methods?id=getClientInfo)メソッドを呼び出して接続情報を取得することができますが、`[onClose](/server/events?id=onclose)`回调関数が実行された後でなければ、TCP接続を閉じることはありません。  
    - ここで[onClose](/server/events?id=onclose)が回调されるということは、クライアントの接続がすでに閉じられているということなので、`$server->close($fd)`を実行する必要はありません。コードで`$server->close($fd)`を実行すると、PHPエラー警告が抛出されます。
## onTask

?> **`task`プロセス内で呼び出されます。`worker`プロセスは[task](/server/methods?id=task)関数を使用して、新しいタスクを`task_worker`プロセスに投じることができます。現在の [Taskプロセス](/learn?id=taskworkerプロセス)は[onTask](/server/events?id=ontask)回调関数を呼び出す時にプロセス状態を忙碌に切り替え、その後は新しいタスクを受け付けなくなります。[onTask](/server/events?id=ontask)関数が戻ると、プロセス状態を空闲に切り替えて再び新しいタスクを受け付けます。**

```php
function onTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $task_id`**
      * **機能**：タスクを実行する `task` プロセスの `id`【`$task_id`と`$src_worker_id`を組み合わせて初めてグローバルに唯一です。異なる `worker` プロセスが投じるタスクの `ID`は同じになる可能性があります】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $src_worker_id`**
      * **機能**：タスクを投じる `worker` プロセスの `id`【`$task_id`と`$src_worker_id`を組み合わせて初めてグローバルに唯一です。異なる `worker` プロセスが投じるタスクの `ID`は同じになる可能性があります】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`mixed $data`**
      * **機能**：タスクのデータ内容
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **ヒント**

    * **v4.2.12から [task_enable_coroutine](/server/setting?id=task_enable_coroutine)が有効にすると、回调関数の原型は以下のようになります**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); //タスクを完了し、結果を返し終結します
      });
      ```

    * **workerプロセスに実行結果を戻す**

      * **[onTask](/server/events?id=ontask)関数の中で `return` 文字列を返すことで、この内容を `worker` プロセスに戻すことができます。`worker` プロセス内で[onFinish](/server/events?id=onfinish)関数がトリガーされ、投じられた `task`が完了したことを示します。もちろん、`Swoole\Server->finish()`を呼び出して[onFinish](/server/events?id=onfinish)関数をトリガーすることもできますが、それには `return`する必要はありません**

      * `return`する変数は、任意の非 `null` PHP変数です。

  * **注意**

    !> [onTask](/server/events?id=ontask)関数が実行中に致命的なエラーが発生して退出したり、外部プロセスによって強制的に`kill`されたりした場合、現在のバッチタスクは破棄されますが、他のキューに入っているタスクには影響しません。
## onFinish

?> **この回调関数はworkerプロセスで呼び出され、workerプロセスが投じたタスクがtaskプロセスで完了した時、 [taskプロセス](/learn?id=taskworkerプロセス)は`Swoole\Server->finish()`メソッドを通じてタスク処理の結果をworkerプロセスに送信します。**

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

    !> - [taskプロセス](/learn?id=taskworkerプロセス)の[onTask](/server/events?id=ontask)イベントでは`finish`メソッドを呼び出さなかったり、結果を`return`しなかったりした場合、workerプロセスは[onFinish](/server/events?id=onfinish)をトリガーしません  
    - [onFinish](/server/events?id=onfinish)ロジックを実行するworkerプロセスは、タスクを発行したworkerプロセスと同じプロセスです。
## onPipeMessage

?> **ワークプロセスが `$server->sendMessage()` によって送信された[unixSocket](/learn?id=什么是IPC)メッセージを受信した時に `onPipeMessage` イベントがトリガーされます。`worker/task` プロセスはどちらも `onPipeMessage` イベントをトリガーする可能性があります**

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $src_worker_id`**
      * **機能**：メッセージがどの `Worker` プロセスから来たのか
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`mixed $message`**
      * **機能**：メッセージの内容、任意のPHPタイプです
      * **デフォルト値**：なし
      * **その他の値**：なし
## onWorkerError

?> **`Worker/Task`プロセスが例外を発生させた後、`Manager`プロセス内でこの関数が回调されます。**

!> この関数は主にアラームとモニタリングに使用され、Workerプロセスが異常で退出したことが確認された場合、それは致命的なエラーやプロセスのCore Dumpが発生した可能性が高いことを意味します。ログを記録したり、アラーム情報を送信して開発者に対応を促すことができます。

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

  * **引数** 

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $worker_id`**
      * **機能**：異常を発生させた `worker` プロセスの `id`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $worker_pid`**
      * **機能**：異常を発生させた `worker` プロセスの `pid`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $exit_code`**
      * **機能**：退出の状態コード、範囲は `0～255`です
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $signal`**
      * **機能**：プロセスが退出したシグナル
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **一般的なエラー**

    * `signal = 11`：これは `Worker`プロセスが `segment fault` 段错误を発生させたことを示しており、基層の `BUG` 可能に引き起こされたことを意味します。`core dump`情報と `valgrind` メモリ検出ログを収集し、[Swoole開発チームにこの問題を報告](/other/issue)してください。
    * `exit_code = 255`：これはWorkerプロセスが `Fatal Error` 致命的なエラーを発生させたことを示しており、PHPのエラーログをチェックし、問題のあるPHPコードを見つけ出し、解決してください。
    * `signal = 9`：これは `Worker`がシステムによって強制的に `Kill`されたことを示しており、誰かが `kill -9` 操作を行ったかを確認し、`dmesg` 信息で `OOM（Out of memory）` がないかを確認してください。
    * `OOM` がある場合、過大なメモリが割り当てられています。1. `Server`の `setting` 配置をチェックし、`socket_buffer_size](/server/setting?id=socket_buffer_size)などが過大に割り当てられているかどうかを確認してください；2. 非常に大きな [Swoole\Table](/memory/table) メモリモジュールが作成されているかどうかを確認してください。
## onManagerStart

?> **マネージャープロセスが起動した時にこのイベントがトリガーされます**

```php
function onManagerStart(Swoole\Server $server);
```

  * **ヒント**

    * この回调関数では、マネージャープロセスの名前を変更することができます。
    * `4.2.12` 以前のバージョンでは、`manager`プロセスではタイマーを追加したり、タスクを投じたり、协程を使用することはできません。
    * `4.2.12` 或ちそれ以降のバージョンでは、`manager`プロセスはシグナルベースの同期モードのタイマーを使用できます
    * `manager`プロセスでは、[sendMessage](/server/methods?id=sendMessage)インターフェースを使用して、他のワークプロセスにメッセージを送信することができます

    * **起動順序**

      * `Task`と`Worker`プロセスが既に作成されています
      * `Master`プロセスの状態は不明です。なぜなら`Manager`と`Master`は並行しており、`onManagerStart`回调が発生しても`Master`プロセスが準備ができていないとは限りません

    * **BASEモード**

      * [SWOOLE_BASE](/learn?id=swoole_base)モードで、`worker_num`、`max_request`、`task_worker_num`パラメータが設定されている場合、基層は`manager`プロセスを作成してワークプロセスを管理します。そのため、`onManagerStart`と`onManagerStop`イベントの回调がトリガーされます。
## onManagerStop

?> **マネージャープロセスが終了した時にこのイベントがトリガーされます**

```php
function onManagerStop(Swoole\Server $server);
```

 * **ヒント**

  * `onManagerStop`がトリガーされたということは、`Task`と`Worker`プロセスが実行を終了し、`Manager`プロセスによって回収されたことを意味します。
## onBeforeReload

?> **Workerプロセスの`Reload`の前にこのイベントがトリガーされ、Managerプロセスで回调されます**

```php
function onBeforeReload(Swoole\Server $server);
```

  * **引数**

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし
## onAfterReload

?> **Workerプロセスの`Reload`の後にこのイベントがトリガーされ、Managerプロセスで回调されます**

```php
function onAfterReload(Swoole\Server $server);
```

  * **引数**

    * **`Swoole\Server $server`**
      * **機能**：Swoole\Serverオブジェクト
      * **デフォルト値**：なし
      * **その他の値**：なし
## イベントの実行順序

* すべてのイベントの回调は `$server->start` を実行した後に行われます
* サーバーがプログラムを終了させる時、最後のイベントは `onShutdown` です
* サーバーが起動に成功した後、`onStart/onManagerStart/onWorkerStart`は異なるプロセス内で並行して実行されます
* `onReceive/onConnect/onClose`は `Worker`プロセスでトリガーされます
* `Worker/Task`プロセスの起動/終了時にはそれぞれ一度ずつ `onWorkerStart/onWorkerStop`が呼び出されます
* [onTask](/server/events?id=ontask)イベントは [taskプロセス](/learn?id=taskworkerプロセス) 内でのみ発生します
* [onFinish](/server/events?id=on
