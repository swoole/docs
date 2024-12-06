# 設定

[Swoole\Server->set()](/server/methods?id=set) 関数は、`Server`の運用時に各種パラメータを設定するために使用されます。このセクションのすべてのサブページは、設定配列の要素です。

!> v4.5.5 [バージョン](/version/log?id=v455) 以降では、底層が設定されたパラメータが正しいかどうかを検出し、Swooleが提供していない設定項目が設定された場合、Warningが発生します。

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```


### debug_mode

?> ログモードを`debug`に設定し、デバッグモードを有効にするには、コンパイル時に`--enable-debug`フラグを有効にする必要があります。

```php
$server->set([
  'debug_mode' => true
])
```


### trace_flags

?>トレースロギングのタグを設定し、一部のトレースロギングのみを印刷します。`trace_flags`は `|` 演算子を使用して複数のトレース項目を設定することができます。トレースロギングを有効にするには、コンパイル時に`--enable-trace-log`フラグを有効にする必要があります。

底層では以下のトレース項目がサポートされており、`SWOOLE_TRACE_ALL`を使用してすべてのプロジェクトをトレース表示できます：

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`


### log_file

?> **Swooleエラーロギングファイルを指定する**

?> Swoole運用中に発生する例外情報は、このファイルに記録され、デフォルトでは画面に印刷されます。  
守护プロセスモードを有効にした後 `(daemonize => true)`、標準出力は`log_file`にリダイレクトされます。PHPコードでの`echo/var_dump/print`などの画面印刷内容は`log_file`ファイルに書き込まれます。

  * **ヒント**

    * `log_file`内のロギングは運用時エラー記録のためであり、長期保存の必要はありません。

    * **ログ番号**

      ?> ログ情報では、プロセスIDの前にいくつかの番号が付けられ、ログが生成されたスレッド/プロセスタイプを示します。

        * `#` Masterプロセス
        * `$` Managerプロセス
        * `*` Workerプロセス
        * `^` Taskプロセス

    * **ログファイルの再開**

      ?> サーバープログラムが運用中にログファイルが`mv`で移動されたり、`unlink`で削除された後、ログ情報は正常に書き込むことができなくなります。この場合、Serverに`SIGRTMIN`シグナルを送信することで、ログファイルを再開することができます。

      * Linuxプラットフォームのみサポートされています
      * UserProcess [](/server/methods?id=addProcess)プロセスはサポートされていません

  * **注意**

    !> `log_file`は自動的にファイルを分割しませんので、定期的にこのファイルを清掃する必要があります。`log_file`の出力を観察することで、サーバーの各種異常情報や警告を得ることができます。


### log_level

?> **Serverエラーロギングの印刷レベルを設定する。範囲は`0-6`です。`log_level`で設定されたレベルより低いロギング情報は抛出されません。**【デフォルト値：`SWOOLE_LOG_INFO`】**

対応するレベル定数については[ログレベル](/consts?id=日志等级)を参照してください。

  * **注意**

    !> `SWOOLE_LOG_DEBUG`と`SWOOLE_LOG_TRACE`は、--enable-debug-log [](/environment?id=debug参数)と--enable-trace-log [](/environment?id=debug参数)のコンパイル時にのみ使用できます。  
    守护プロセスを有効にした場合、底層はプログラム内のすべての画面印刷出力内容を[log_file](/server/setting?id=log_file)に書き込みますが、この内容は`log_level`によって制御されません。


### log_date_format

?> **Serverロギング時間のフォーマットを設定する**。フォーマットは [strftime](https://www.php.net/manual/zh/function.strftime.php)の`format`を参照してください。

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```


### log_date_with_microseconds

?> **Serverロギングの精度を設定する。微秒を含むかどうか**【デフォルト値：`false`】


### log_rotation

?> **Serverロギングの分割を設定する**【デフォルト値：`SWOOLE_LOG_ROTATION_SINGLE`】

| 定数                             | 説明   | バージョン情報 |
| -------------------------------- | ------ | ------------ |
| SWOOLE_LOG_ROTATION_SINGLE       | 非有効 | -            |
| SWOOLE_LOG_ROTATION_MONTHLY      | 月次   | v4.5.8       |
| SWOOLE_LOG_ROTATION_DAILY        | 日次   | v4.5.2       |
| SWOOLE_LOG_ROTATION_HOURLY       | 時次   | v4.5.8       |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE | 分次   | v4.5.8       |


### display_errors

?> Swooleエラー情報を表示/非表示します。

```php
$server->set([
  'display_errors' => true
])
```


### dns_server

?> DNS照会用のIPアドレスを設定します。


### socket_dns_timeout

?> ドメイン解析のタイムアウト時間を設定します。サーバー側で协程クライアントを有効にした場合、このパラメータはクライアントのドメイン解析タイムアウト時間を制御でき、単位は秒です。


### socket_connect_timeout

?> クライアント接続のタイムアウト時間を設定します。サーバー側で协程クライアントを有効にした場合、このパラメータはクライアントの接続タイムアウト時間を制御でき、単位は秒です。


### socket_write_timeout / socket_send_timeout

?> クライアントの写入タイムアウト時間を設定します。サーバー側で协程クライアントを有効にした場合、このパラメータはクライアントの写入タイムアウト時間を制御でき、単位は秒です。   
この設定はまた、`协程化`後の`shell_exec`または[Swoole\Coroutine\System::exec()](/coroutine/system?id=exec)の実行タイムアウト時間を制御するために使用できます。   


### socket_read_timeout / socket_recv_timeout

?> クライアントの读取タイムアウト時間を設定します。サーバー側で协程クライアントを有効にした場合、このパラメータはクライアントの读取タイムアウト時間を制御でき、単位は秒です。


### max_coroutine / max_coro_num :id=max_coroutine

?> **現在的工作プロセスの最大协程数を設定します。**【デフォルト値：`100000`、Swooleバージョンが`v4.4.0-beta`未満の場合のデフォルト値は`3000`】

?> `max_coroutine`を超える場合、底層では新しい协程を创建することができず、サーバー側のSwooleは`exceed max number of coroutine`エラーを投げ出し、`TCP Server`は接続を直接閉じ、`Http Server`はHttpの503ステータコードを返します。

?> `Server`プログラム内で実際に最大で创建できる协程数は `worker_num * max_coroutine` であり、taskプロセスとUserProcessプロセスの协程数は別々に計算されます。

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```


### enable_deadlock_check

?> 协程デッドロック検出を有効にします。

```php
$server->set([
  'enable_deadlock_check' => true
]);
```


### hook_flags

?> **「ワンクリック协程化」Hookの関数範囲を設定します。**【デフォルト値：Hookしない】

!> Swooleバージョンは `v4.5+` 或 [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 可用であり、詳細は[ワンクリック协程化](/runtime)を参照してください。

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
底層では以下の协程化項目がサポートされており、`SWOOLE_HOOK_ALL`を使用して协程化を全て行います：

* `SWOOLE_HOOK_TCP`
* `SWOOLE_HOOK_UNIX`
* `SWOOLE_HOOK_UDP`
* `SWOOLE_HOOK_UDG`
* `SWOOLE_HOOK_SSL`
* `SWOOLE_HOOK_TLS`
* `SWOOLE_HOOK_SLEEP`
* `SWOOLE_HOOK_FILE`
* `SWOOLE_HOOK_STREAM_FUNCTION`
* `SWOOLE_HOOK_BLOCKING_FUNCTION`
* `SWOOLE_HOOK_PROC`
* `SWOOLE_HOOK_CURL`
* `SWOOLE_HOOK_NATIVE_CURL`
* `SWOOLE_HOOK_SOCKETS`
* `SWOOLE_HOOK_STDIO`
* `SWOOLE_HOOK_PDO_PGSQL`
* `SWOOLE_HOOK_PDO_ODBC`
* `SWOOLE_HOOK_PDO_ORACLE`
* `SWOOLE_HOOK_PDO_SQLITE`
* `SWOOLE_HOOK_ALL`
### enable_preemptive_scheduler

?> コルoutineの割り込みスケジュールを有効に設定し、あるコルoutineが長時間実行されることによる他のコルoutineの餓死を防ぎます。コルoutineの最大実行時間は`10ms`です。

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```

### c_stack_size / stack_size

?> 各コルoutineの初期Cスタックのメモリサイズを設定します。デフォルトは2Mです。

### aio_core_worker_num

?> AIOの最小作業スレッド数を設定します。デフォルトはCPUコア数です。

### aio_worker_num 

?> AIOの最大作業スレッド数を設定します。デフォルトはCPUコア数 * 8です。

### aio_max_wait_time

?> 作業スレッドがタスクを待つ最大時間です。単位は秒です。

### aio_max_idle_time

?> 作業スレッドの最大空闲時間です。単位は秒です。

### reactor_num

?> **開始される[Reactor](/learn?id=reactor线程)スレッド数を設定します。**【デフォルト値：CPUコア数】

?> このパラメータを通じて、プロセス内のイベント処理スレッドの数を調整し、マルチコアを十分に活用します。デフォルトでは、CPUコア数と同じ数量的のスレッドが有効になります。  
Reactorスレッドはマルチコアを利用できます。例えば、マシンに128個のコアがある場合、下層では128個のスレッドが起動します。  
各スレッドは[EventLoop](/learn?id=什么是eventloop)を維持します。スレッド間は無鎖であり、命令は128個のコアのCPUで並列実行できます。  
オペレーティングシステムのスケジュールにはある程度の性能損失があるため、CPUコア数の*2に設定することで、CPUの各コアを最大限に活用できます。

  * **ヒント**

    * `reactor_num`はCPUコア数の`1-4`倍に設定することをお勧めします
    * `reactor_num`は[swoole_cpu_num()](/functions?id=swoole_cpu_num) * 4を超えることはありません

  * **注意**


  !> -`reactor_num`は`worker_num`よりも小さくしなければなりません；  

-設定された`reactor_num`が`worker_num`を上回る場合、自動的に調整されて`reactor_num`は`worker_num`と等しくなります；  
-8コアを超えるマシンでは`reactor_num`はデフォルトで8に設定されます。
	

### worker_num

?> **開始される`Worker`プロセスの数を設定します。**【デフォルト値：CPUコア数】

?> 例として、1つのリクエストが100msかかりますが、1000QPSの処理能力を提供するためには、100個のプロセスやそれ以上を構成する必要があります。  
しかし、プロセスを多く開くと、占用するメモリが大幅に増加し、プロセス間の切り替えのコストも大きくなります。したがって、ここでは適切であるべきであり、過度に設定してはいけません。

  * **ヒント**

    * 业务コードが全[非同期IO](/learn?id=同步io异步io)の場合、ここではCPUコア数の`1-4`倍に設定するのが最も合理的です
    * 业务コードが[同期IO](/learn?id=同步io异步io)の場合、リクエストの応答時間とシステムの負荷に基づいて調整する必要があります。例えば：`100-500`
    * デフォルトは[swoole_cpu_num()](/functions?id=swoole_cpu_num)に設定され、最大は[swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000を超えません
    * 假定各プロセスが40Mのメモリを占用すると、100個のプロセスは4Gのメモリを占用する必要があります。


### max_request

?> **`worker`プロセスの最大タスク数を設定します。**【デフォルト値：`0` 即ちプロセスは退出しません】

?> 一つの`worker`プロセスがこの数を超えるタスクを処理した後、自動的に退出します。プロセスが退出すると、すべてのメモリとリソースが解放されます

!> このパラメータの主な役割は、PHPプロセスのメモリ漏洩問題を解決することです。PHPアプリケーションにはゆっくりとしたメモリ漏洩がありますが、具体的な原因を特定したり解決することはできません。この設定で一時的に解決できますが、メモリ漏洩のコードを特定し修正することによって、この方法ではなく、Swoole Trackerを使用して漏洩のコードを発見することができます。

  * **ヒント**

    * max_requestに達しても必ずしもすぐにプロセスを閉じるわけではありません。[max_wait_time](/server/setting?id=max_wait_time)を参照してください。
    * [SWOOLE_BASE](/learn?id=swoole_base)の下では、max_requestに達してプロセスを再起動すると、クライアントの接続が切断されます。

  !> `worker`プロセス内で致命的なエラーが発生したり、手動で`exit`を実行した場合、プロセスは自動的に退出します。`master`プロセスは新しい`worker`プロセスを再起動してリクエストを処理を続けます


### max_conn / max_connection

?> **サーバープログラムで、最大許可される接続数を設定します。**【デフォルト値：`ulimit -n`】

?> 例として、`max_connection => 10000`とすると、このパラメータは`Server`が維持できる最大TCP接続数を設定します。この数を超える新しい接続は拒否されます。

  * **ヒント**

    * **デフォルト設定**

      * 应用层で`max_connection`が設定されていない場合、下層は`ulimit -n`の値をデフォルト設定として使用します
      * `4.2.9`またはそれ以降のバージョンでは、下層が`ulimit -n`が`100000`を超えることを検出した場合、デフォルトは`100000`に設定されます。その理由は、一部のシステムが`ulimit -n`を`100万`に設定し、大量のメモリを割り当てる必要があるため、起動に失敗するためです

    * **最大上限**

      * `max_connection`を`1M`を超えることはお勧めしません

    * **最小設定**    
     
      * このオプションを小さく設定すると、下層はエラーを投げ出し、`ulimit -n`の値を設定します。
      * 最小値は`(worker_num + task_worker_num) * 2 + 32`です

    ```shell
    serv->max_connection is too small.
    ```

    * **メモリ占用**

      * `max_connection`パラメータを大きく調整してはいけません。マシンのメモリの実際の状況に基づいて設定してください。Swooleはこの数に基づいて、一度に大きなメモリを割り当てて`Connection`情報を保存します。TCP接続の`Connection`情報は224Byteを占用します。

  * **注意**

  !> `max_connection`はオペレーティングシステムの`ulimit -n`の値を超えることはできません。そうでなければ警告情報を表示し、`ulimit -n`の値にリセットされます

  ```shell
  WARN swServer_start_check: serv->max_conn is exceed the maximum value[100000].

  WARNING set_max_connection: max_connection is exceed the maximum value, it's reset to 10240
  ```


### task_worker_num

?> **[Taskプロセス](/learn?id=taskworker进程)の数を構成します。**

?> このパラメータを設定した後、`task`機能を有効にします。したがって、`Server`は[onTask](/server/events?id=ontask)、[onFinish](/server/events?id=onfinish)の2つのイベント回调関数を登録しなければなりません。登録されていないと、サーバープログラムは起動できません。

  * **ヒント**

    * [Taskプロセス](/learn?id=taskworker进程)は同期阻塞です

    * 最大値は[swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000を超えてはいけません    
    
    * **計算方法**
      * 単一の`task`の処理時間は、例えば`100ms`であれば、1秒間に1/0.1=10個のtaskを処理できます
      * `task`の投入速度は、例えば毎秒2000個の`task`を生じさせる場合
      * 2000/10=200であり、200個のTaskプロセスを設置する必要があります。すなわち、`task_worker_num => 200`と設定します

  * **注意**

    !> - [Taskプロセス](/learn?id=taskworker进程)内では`Swoole\Server->task`メソッドを使用することはできません
### task_ipc_mode

?> **タスクプロセスと`Worker`プロセス間の通信方法を設定します。**【デフォルト値：`1`】 
 
?> SwooleでのIPC通信について先に読んでください。


モード | 効果
---|---
1 | Unixソケット通信【デフォルトモード】
2 | sysvmsgメッセージキュー通信
3 | sysvmsgメッセージキュー通信で競合モードに設定

  * **ヒント**

    * **モード`1`**
      * モード`1`を使用すると、ターゲット指向の配信がサポートされ、[タスク](/server/methods?id=task)と[taskwait](/server/methods?id=taskwait)メソッドで`dst_worker_id`を指定し、ターゲットのタスクプロセスを指定できます。
      * `dst_worker_id`を`-1`に設定すると、各タスクプロセスの状態を判断し、空闲なプロセスにタスクを配信します。

    * **モード`2`、`3`**
      * メッセージキューモードでは、オペレーティングシステムが提供するメモリキューを使用してデータを保存します。`message_queue_key` 消息キュー`Key`を指定していなければ、プライベートキューを使用し、サーバープログラムが終了した後、消息キューは削除されます。
      * 消息キュー`Key`を指定した後、サーバープログラムが終了しても、消息キュー内のデータは削除されません。したがって、プロセスが再起動した後でもデータを取り出すことができます。
      * `ipcrm -q`消息キュー`ID`で手动で消息キューのデータを削除することができます。
      * モード`2`とモード`3`の違いは、モード`2`はターゲット指向の配信をサポートし、`$serv->task($data, $task_worker_id)`でどのタスクプロセスに配信するかを指定できます。モード`3`は完全な競合モードで、タスクプロセスがキューを競い合い、ターゲット指向の配信は使用できず、`task/taskwait`ではターゲットプロセスのIDを指定することはできず、たとえ`$task_worker_id`を指定しても、モード`3`では無効です。

  * **注意**

    !> -`モード3`は[sendMessage](/server/methods?id=sendMessage)メソッドに影響を与え、[sendMessage](/server/methods?id=sendMessage)が送信するメッセージがランダムに特定のタスクプロセスによって取得される可能性があります。  
    -消息キュー通信を使用すると、タスクプロセスの処理能力が配信速度よりも低い場合、`Worker`プロセスがブロッキングされる可能性があります。  
    -消息キュー通信を使用すると、タスクプロセスは协程をサポートできなくなります（[task_enable_coroutine](/server/setting?id=task_enable_coroutine)を有効にする必要があります）。  


### task_max_request

?> **タスクプロセスの最大タスク数を設定します。**【デフォルト値：`0`】

タスクプロセスの最大タスク数を設定します。この数を超えるタスクを処理したタスクプロセスは自動的に終了します。このパラメータはPHPプロセスのメモリオーバーフローを防ぐためにあります。プロセスが自動的に終了しない場合は`0`に設定できます。


### task_tmpdir

?> **タスクのデータ一時ディレクトリを設定します。**【デフォルト値：Linux `/tmp` 目录】

?> サーバー内で、データが`8180`バイトを超える場合、一時ファイルを使用してデータを保存します。ここでの`task_tmpdir`は、一時ファイルの保存位置を設定するために使用されます。

  * **ヒント**

    * ベースでは、`/tmp`ディレクトリをデフォルトで使用してタスクデータを保存しますが、Linuxカーネルバージョンが古い場合や`/tmp`ディレクト리가メモリファイルシステムでない場合は、`/dev/shm/`に設定できます。
    * `task_tmpdir`ディレクトリが存在しない場合、ベースは自動的に作成しようとします。

  * **注意**

    !> -作成に失敗した場合、`Server->start`は失敗します。


### task_enable_coroutine

?> **タスク协程サポートを有効にします。**【デフォルト値：`false`】v4.2.12からサポートされています

?> 有効にすると、自動的に[onTask](/server/events?id=ontask)回调で协程と[协程コンテナ](/coroutine/scheduler)が作成され、PHPコードは直接协程`API`を使用できます。

  * **例**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    //どの Worker プロセスから来たか
    $task->worker_id;
    //タスクの番号
    $task->id;
    //タスクの種類、taskwait, task, taskCo, taskWaitMultiは異なるflagsを使用する可能性があります
    $task->flags;
    //タスクのデータ
    $task->data;
    //配信時間、v4.6.0で追加されました
    $task->dispatch_time;
    //协程 API
    co::sleep(0.2);
    //タスクを完了し、終了してデータを返す
    $task->finish([123, 'hello']);
});
```

  * **注意**

    !> -`task_enable_coroutine`は[enable_coroutine](/server/setting?id=enable_coroutine)が`true`の場合にのみ使用できます  
    -`task_enable_coroutine`を有効にすると、タスクワークプロセスは协程をサポートします  
    -`task_enable_coroutine`を無効にすると、同期ブロッキングのみがサポートされます


### task_use_object/task_object :id=task_use_object

?> **オブジェクト指向スタイルのタスク回调フォーマットを使用します。**【デフォルト値：`false`】

?> `true`に設定すると、[onTask](/server/events?id=ontask)回调はオブジェクトモードになります。

  * **例**

```php
<?php

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 3,
    'task_use_object' => true,
//    'task_object' => true, // v4.6.0で追加された別名
]);
$server->on('receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd' => $fd,]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    //ここでの$taskはSwoole\Server\Taskオブジェクトです
    $server->send($task->data['fd'], json_encode($server->stats()));
});
$server->start();
```


### dispatch_mode

?> **データパケットの配布戦略です。**【デフォルト値：`2`】


モード値 | モード | 効果
---|---|---
1 | 巡回モード | 受信すると巡回して各`Worker`プロセスに配分されます
2 | 固定モード | 接続のファイル記述子に基づいて`Worker`を割り当てます。これにより、同じ接続からのデータが同じ`Worker`によって処理されることを保証できます
3 | 奪取モード | メインプロセスは`Worker`の忙しさが基にして選択的に配信を行い、空闲状態の`Worker`にのみ配信を行います
4 | IP割り当て | クライアントの`IP`に基づいてモデムハッシュを行い、固定の`Worker`プロセスに割り当てられます。<br>同じ来源IPの接続データは常に同じ`Worker`プロセスに割り当てられます。アルゴリズムは `inet_addr_mod(ClientIP, worker_num)`
5 | UID割り当て | クライアントコード内で[Server->bind()](/server/methods?id=bind)を呼び出して接続に`1`つの`uid`を割り当てます。その後、ベースは`UID`の値に基づいて異なる`Worker`プロセスに割り当てます。<br>アルゴリズムは `UID % worker_num`です。文字列を`UID`として使用したい場合は、`crc32(UID_STRING)`を使用できます
7 | ストリームモード | 空闲の`Worker`は接続を受け付け、[Reactor](/learn?id=reactor线程)からの新しいリクエストを受け付けます

  * **ヒント**

    * **使用提案**
    
      * ステートレスサーバーは`1`または`3`を使用でき、同期ブロッキングサーバーは`3`を使用し、非同期非ブロッキングサーバーは`1`を使用します
      * ステートfulな使用には`2`、`4`、`5`を使用します
      
    * **UDPプロトコル**

      * `dispatch_mode=2/4/5`の場合は固定割り当てされ、ベースはクライアントIPに基づいてモデムハッシュを行い、異なる`Worker`プロセスに割り当てられます
      * `dispatch_mode=1/3`の場合はランダムに異なる`Worker`プロセスに割り当てられます
      * `inet_addr_mod`関数

```
    function inet_addr_mod($ip, $worker_num) {
        $ip_parts = explode('.', $ip);
        if (count($ip_parts) != 4) {
            return false;
        }
        $ip_parts = array_reverse($ip_parts);
    
        $ip_long = 0;
        foreach ($ip_parts as $part) {
            $ip_long <<= 8;
            $ip_long |= (int) $part;
        }
    
        return $ip_long % $worker_num;
    }
```
  * **Baseモード**
    * `dispatch_mode`は[SWOOLE_BASE](/learn?id=swoole_base)モードで無効であり、なぜなら`BASE`ではタスクを配達する必要がないため、クライアントからのデータを受け取った後、すぐに現在のスレッド/プロセスで[onReceive](/server/events?id=onreceive)を回调し、`Worker`プロセスに配達する必要がないからです。

  * **注意**

    !> -`dispatch_mode=1/3`の場合は、ベースは`onConnect/onClose`イベントをブロックします。その理由は、これら2つのモードでは`onConnect/onClose/onReceive`の順序を保証できないからです；  
    -リクエスト応答式でないサーバープログラムは、モード`1`または`3`を使用しないでください。例えば：HTTPサービスは応答式であり、`1`または`3`を使用できますが、TCP長接続状態がある場合は`1`または`3`を使用できません。
### dispatch_func

?> `dispatch`関数を設定する。Swooleは底层に6種類の[dispatch_mode](/server/setting?id=dispatch_mode)を内蔵していますが、それでも要件を満たすことができない場合は、C++関数やPHP関数を書いて`dispatch`ロジックを実現することができます。

  * **使用方法**

```php
$server->set(array(
  'dispatch_func' => 'my_dispatch_function',
));
```

  * **ヒント**

    * `dispatch_func`を設定すると、底层は自動的に`dispatch_mode`設定を無視します
    * `dispatch_func`に対応する関数が存在しない場合、底层は致命的なエラーを抛出します
    * 8Kを超えるパケットを`dispatch`したい場合は、`dispatch_func`は `0-8180`バイトの内容しか取得できません

  * **PHP関数を書く**

    ?> ZendVMはマルチスレッド環境をサポートしていないため、複数の[Reactor](/learn?id=reactor线程)线程を設定していても、同時に一つの`dispatch_func`しか実行できません。そのため、底层はこのPHP関数を実行する際にロックをかけ、ロックの争奪問題が発生する可能性があります。`dispatch_func`では任何のブロック操作を行わないでください。そうでなければ、Reactor线程群が停止することがあります。

    ```php
    $server->set(array(
        'dispatch_func' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd`はクライアント接続のユニークな識別子であり、`Server::getClientInfo`を使用して接続情報を取得できます
    * `$type`はデータのタイプで、`0`はクライアントからのデータ送信を表し、`4`はクライアント接続が確立され、`3`はクライアント接続が閉じられました
    * `$data`はデータ内容で、HTTP、EOF、Lengthなどのプロトコル処理パラメータが有効に启用されていると、底层ではパケットを組み立てますが、`dispatch_func`関数ではデータパケットの最初の8K内容のみを渡すことができ、完全なパケット内容を得ることはできません。
    * **必ず** `0 - (server->worker_num - 1)`の数字を返す必要があります。これはデータパケットが配達されるべきワークプロセスのIDを表します
    * `0`未満または`server->worker_num`以上は異常なターゲットIDであり、`dispatch`されたデータは破棄されます

  * **C++関数を書く**

    **他のPHP拡張で、swoole_add_functionを使用してSwooleエンジンに長さ関数を登録します。**

    ?> C++関数呼び出し時には底层はロックをかけませんので、呼び出し側でスレッド安全性を保証する必要があります

    ```c++
    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data);

    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data)
    {
        printf("cpp, type=%d, size=%d\n", data->info.type, data->info.len);
        return data->info.len % serv->worker_num;
    }

    int register_dispatch_function(swModule *module)
    {
        swoole_add_function("my_dispatch_function", (void *) dispatch_function);
    }
    ```

    * `dispatch`関数は配達されるべき`worker`プロセスの`id`を返さなければなりません
    * 返された`worker_id`は`server->worker_num`を超えてはいけません。そうでなければ底层はセグメント違反を抛出します
    * 负数`（return -1）`を返すことは、このデータパケットを破棄することを意味します
    * `data`からはイベントのタイプと長さが読み取れます
    * `conn`は接続情報で、UDPデータパケットの場合は`conn`は`NULL`です

  * **注意**

    !> -`dispatch_func`は[SWOOLE_PROCESS](/learn?id=swoole_process)モードでのみ有効であり、UDP/TCP/UnixSocket](/server/methods?id=__construct)タイプのサーバーでも有効です  
    -返された`worker_id`は`server->worker_num`を超えてはいけません。そうでなければ底层はセグメント違反を抛出します


### message_queue_key

?> **メッセージキーの設定**。【デフォルト値：`ftok($php_script_file, 1)`】

?> [task_ipc_mode](/server/setting?id=task_ipc_mode)が2/3の場合にのみ使用します。設定された`Key`はTaskタスクキーのみであり、Swoole下のIPC通信についての参照は[こちら](/learn?id=什么是IPC)。

?> `task`キーのタスクは、`server`が終わった後も破壊されず、プログラムを再起動した後も、 [taskプロセス](/learn?id=taskworkerプロセス)はキーのタスクを継続して処理します。古い`Task`タスクが再起動後に実行されるのを望まない場合は、このメッセージキーの手动削除が可能です。

```shell
ipcs -q 
ipcrm -Q [msgkey]
```


### daemonize

?> **守护进程化**【デフォルト値：`false`】

?> `daemonize => true`と設定すると、プログラムはバックグラウンドで守护进程として実行されます。長期にわたって動作するサーバー端プログラムでは、このオプションを有効にする必要があります。  
守护进程を有効にしなければ、SSHターミナルが切断された後、プログラムは停止してしまいます。

  * **ヒント**

    * 守护进程を有効にすると、標準入力と出力は `log_file`にリダイレクトされます
    * `log_file`を指定していない場合は、 `/dev/null`にリダイレクトされ、画面に表示されるすべての情報が丢弃されます
    * 守护进程を有効にすると、`CWD`（現在のディレクトリ）環境変数の値が変わり、相対パスでファイルの読み書きが失敗します。PHPプログラムでは絶対パスを使用する必要があります

    * **systemd**

      * `systemd`や`supervisord`を使用してSwooleサービスを管理する場合、`daemonize => true`を設定しないでください。主な理由は`systemd`のメカニズムが`init`とは異なるからです。`init`プロセスのPIDは`1`であり、プログラムが`daemonize`设置为すると、ターミナルから離れ、最終的には`init`プロセスによって管理され、`init`との関係は父子プロセスになります。
      * 一方で`systemd`は別のバックグラウンドプロセスを起動し、他のサービスプロセスを`fork`して管理するため、`daemonize`は必要ありません。逆に`daemonize => true`を設定すると、Swooleプログラムとその管理プロセスとの父子プロセス関係を失います。


### backlog

?> **Listenキューの長さを設定する**

?> 例として`backlog => 128`とすると、これは同時に`accept`を待つ最大接続数を決定します。

  * **TCPの`backlog`について**

    ?> TCPには3回の手順があり、クライアントは`syn=>サーバー` `syn+ack=>クライアント` `ack`を送ります。サーバーがクライアントの`ack`を受け取ると、接続を`accept queue`というキューに入れます（注1）、  
    キューの大きさは`backlog`パラメータと`somaxconn`の最小値によって決定されます。最終的な`accept queue`の大きさは`ss -lt`コマンドで確認できます。Swooleの主任務プロセスが`accept`を呼び出します（注2）  
    `accept queue`から接続を取り出します。 `accept queue`がいっぱいの場合、接続は成功する可能性があります（注4）、  
    成功するとも失敗する可能性があり、失敗した場合はクライアントは接続がリセットされる（注3）  
    または接続タイムアウトが発生し、サーバーは失敗記録を残します。 `netstat -s|grep 'times the listen queue of a socket overflowed'`でログを確認できます。このような現象が発生した場合は、その値を増やす必要があります。幸いなことにSwooleのSWOOLE_PROCESSモードはPHP-FPM/Apacheなどのソフトウェアとは異なり、`backlog`に頼って接続キューの問題を解決する必要がないため、このような現象に遭遇することはほとんどありません。

    * 注1:`linux2.2`以降のハンドシェイクプロセスは`syn queue`と`accept queue`の2つのキューに分かれており、`syn queue`の長さは`tcp_max_syn_backlog`によって決定されます。
    * 注2:新しいバージョンカーネルでは`accept4`を呼び出し、一度の`set no block`システム呼び出しを節約するために使用されます。
    * 注3:クライアントは`syn+ack`パケットを受け取ると接続が成功したとみなしますが、実際にはサーバーはまだ半接続状態であり、`rst`パケットを送信する可能性があります。クライアントは`Connection reset by peer`として表現します。
    * 注4:成功はTCPの再伝送メカニズムを通じて行われ、関連する設定には`tcp_synack_retries`と`tcp_abort_on_overflow`があります。
### open_tcp_keepalive

?> TCPにはKeep-Aliveメカニズムがあり、切断された接続を検出することができます。アプリケーション層が切断された接続に対して敏感ではなく、またはHeartbeatメカニズムを実現していない場合、オペレーティングシステムが提供するkeepaliveメカニズムを使用して切断された接続を削除することができます。
[Server->set()](/server/methods?id=set)の構成に`open_tcp_keepalive => true`を加えることで、TCP keepaliveを有効にします。
また、keepaliveの詳細を調整するための3つのオプションがあります。

  * **オプション**

     * **tcp_keepidle**

        秒为单位で、`n`秒以内にデータ要求がない接続は、この接続に対して探査を始めます。

     * **tcp_keepcount**

        探査の回数で、回数を超えた後はこの接続を`close`します。

     * **tcp_keepinterval**

        探査の間隔時間で、秒为单位です。

  * **例**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, //4秒データ転送がないと探査開始
    'tcp_keepinterval' => 1, //1秒ごとに探査
    'tcp_keepcount' => 5, //探査回数で5回連続で応答がなければ接続をclose
));

$serv->on('connect', function ($serv, $fd) {
    var_dump("Client:Connect $fd");
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    var_dump($data);
});

$serv->on('close', function ($serv, $fd) {
  var_dump("close fd $fd");
});

$serv->start();
```


### heartbeat_check_interval

?> **Heartbeat検出を有効にする**【デフォルト値：`false`】

?> このオプションは、何秒ごとに一回ループすることを表します。例えば `heartbeat_check_interval => 60` 表示60秒ごとにすべての接続を巡回し、その接続が120秒以内（`heartbeat_idle_time`が設定されていない場合は、デフォルトで`interval`の2倍）にサーバーにデータを送信していない場合、この接続は強制的に切断されます。設定されていないと、Heartbeatは有効になりません。この設定はデフォルトでオフです。

  * **ヒント**
    * `Server`は主动向きのHeartbeatパケットを送信することはありません。代わりに、クライアントからのHeartbeatを受けるだけです。サーバー側の`heartbeat_check`は、接続が最後にデータを送信した時間を検出するだけで、制限を超えていると接続を切断します。
    * Heartbeat検出によって切断された接続は、[onClose](/server/events?id=onclose)イベント回调が引き起こされます

  * **注意**

    !> `heartbeat_check`はTCP接続のみをサポートします


### heartbeat_idle_time

?> **接続の最大允許空闲時間**

?> `heartbeat_check_interval`と組み合わせて使用する必要があります

```php
array(
    'heartbeat_idle_time'      => 600, // 600秒間サーバーにデータを送信していない接続は強制的に切断されます
    'heartbeat_check_interval' => 60,  // 60秒ごとに一回ループ
);
```

  * **ヒント**

    * `heartbeat_idle_time`を有効にすると、サーバーは主动向きのデータパケットを送信することはありません。
    * `heartbeat_idle_time`のみを設定しても、`heartbeat_check_interval`が設定されていないと、基層はHeartbeat検出スレッドを構築しません。PHPコードでは、`heartbeat`方法を手動で呼び出してタイムアウトした接続を処理することができます


### open_eof_check

?> **EOF検出を有効にする**【デフォルト値：`false`】参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)

?> このオプションは、クライアントからの接続が送信するデータを検出し、データパケットの終わりが指定された文字列の場合にのみWorkerプロセスに渡します。そうでなければ、データパケットを継続して組み立て、バッファが溢れるかタイムアウトするまで続けます。エラーが発生した場合は、基層は悪意のある接続と見なし、データを破棄し強制的に接続を切断します。  
一般的なMemcache/SMTP/POPなどのプロトコルは`\r\n`で終わるので、この設定を使用することができます。有効にすると、Workerプロセスは一度に一つまたは複数の完全なデータパケットを受け取ることができます。

```php
array(
    'open_eof_check' => true,   //EOF検出を有効にする
    'package_eof'    => "\r\n", //EOFを設定する
)
```

  * **注意**

    !> この設定はSTREAM（ストリーム型）のSocketにのみ適用されます。例えばTCP、Unix Socket Stream(/server/methods?id=__construct)などです   
    EOF検出はデータの途中からEOF文字列を探すことはしませんので、Workerプロセスは同時に複数のデータパケットを受け取る可能性があります。アプリケーション層のコードでは、`explode("\r\n", $data)`を使用してデータパケットを分割する必要があります


### open_eof_split

?> **EOFの自動分包を有効にする**

?> `open_eof_check`が設定された後、複数のデータが一つのパッケージに合并される可能性があります。`open_eof_split`パラメータはこの問題を解決することができます。参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)。

?> このパラメータを設定するには、データパケットの内容全体を走査し、EOFを探さなければなりません。そのため、大量のCPUリソースを消費します。例えば、各データパケットが2MBで、毎秒10000の要求がある場合、これは20GBのCPU文字列マッチング指令を生じさせる可能性があります。

```php
array(
    'open_eof_split' => true,   //EOF_SPLIT検出を有効にする
    'package_eof'    => "\r\n", //EOFを設定する
)
```

  * **ヒント**

    * `open_eof_split`パラメータを有効にすると、基層はデータパケットの途中からEOFを探し、データパケットを分割します。[onReceive](/server/events?id=onreceive)では、EOF文字列で終わるデータパケットのみを受け取ります。
    * `open_eof_split`パラメータを有効にすると、`open_eof_check`が設定されていなくても、`open_eof_split`は有効になります。

    * **open_eof_checkとの違い**
    
        * `open_eof_check`は受信データの末尾がEOFかどうかのみをチェックするため、パフォーマンスが最も良く、ほとんどリソースを消費しません。
        * `open_eof_check`は複数のデータパケットが合并される問題を解決することができません。例えば、EOFを持つ2つのデータを同時に送信した場合、基層は一度に全部返す可能性があります。
        * `open_eof_split`は左から右にデータを逐字比較し、EOFを探してデータパケットを分割します。パフォーマンスは低いです。しかし、毎回一つのデータパケットのみが返されます。


### package_eof

?> **EOF文字列を設定する。**参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)

?> `open_eof_check`または`open_eof_split`と組み合わせて使用する必要があります。

  * **注意**

    !> `package_eof`は最大で8Byteの文字列を許容します


### open_length_check

?> **パッケージ長さ検出特性を有効にする**【デフォルト値：`false`】参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)

?> パッケージ長さ検出は、固定された包头+ボディというフォーマットのプロトコルを解析することを提供します。有効にすると、Workerプロセスの[onReceive](/server/events?id=onreceive)は毎回完全なデータパケットを受け取ることができます。  
長さ検出プロトコルは、一度だけ長さを計算し、データ処理はポインタのオフセットのみを行い、非常に高いパフォーマンスを持ちます。**推奨される使用です**。

  * **ヒント**

    * **長さプロトコルは3つのオプションでプロトコルの詳細を制御できます。**

      ?> この設定はSTREAM型（ストリーム）のSocketにのみ適用されます。例えばTCP、Unix Socket Stream(/server/methods?id=__construct)などです

      * **package_length_type**

        ?>包头のどのフィールドを長さの値として使用するか、基層は10種類の長さタイプをサポートしています。参考：[package_length_type](/server/setting?id=package_length_type)

      * **package_body_offset**

        ?>長さの計算はどの字节から始めるか、通常2つのケースがあります：

        * `length`の値がパケット全体（包头+ボディ）を含んでいる場合、`package_body_offset`は`0`です
        *包头の長さが`N`Byteで、`length`の値は包头を含まず、ボディのみを含んでいる場合、`package_body_offset`は`N`に設定されます

      * **package_length_offset**

        ?> `length`の長さ値が包头のどの字节にありますか。

        * 例：

        ```c
        struct
        {
            uint32_t type;
            uint32_t uid;
            uint32_t length;
            uint32_t serid;
            char body[0];
        }
        ```
        
    ?> 上記の通信プロトコルの設計では、包头の長さは4つの整数型で、16Byteです。`length`の長さ値は第3の整数型に位置しています。したがって、`package_length_offset`は8に設定され、`0-3`Byteが`type`、`4-7`Byteが`uid`、`8-11`Byteが`length`、`12-15`Byteが`serid`です。

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```
### パッケージの長さタイプ

?> **長さ値のタイプ**は、文字列パラメータを受け取り、PHPの[pack](http://php.net/manual/zh/function.pack.php)関数と一致しています。

現在、Swooleは以下の10種類のタイプをサポートしています：


文字列パラメータ | 効果
---|---
c | 有符号、1字节
C | 無符号、1字节
s | 有符号、ホストバイト順、2字节
S | 無符号、ホストバイト順、2字节
n | 無符号、ネットワークバイト順、2字节
N | 無符号、ネットワークバイト順、4字节
l | 有符号、ホストバイト順、4字节（小文字のL）
L | 無符号、ホストバイト順、4字节（大文字のL）
v | 無符号、小端字节順、2字节
V | 無符号、小端字节順、4字节


### パッケージの長さ関数設定

?> **長さ解析関数を設定する**

?> C++またはPHPの2種類のタイプの関数をサポートしています。長さ関数は整数を返す必要があります。


戻り値 | 効果
---|---
0を返す | 長度データが不足しているため、さらにデータを受け取る必要があります
-1を返す | データが誤りであり、基層は自動的に接続を閉じます
パッケージの長さ値（包头とボディの総長さを含む）| 基層は自動的にパッケージを組み立てて回调関数に返します

  * **ヒント**

    * **使用方法**

    ?> 原理は、まず小さな部分のデータを読み取り、そのデータには長さ値が含まれています。その後、この長さ値を基層に返します。その後、基層が残りのデータを受け取り、パッケージとして組み立ててdispatchを行います。

    * **PHP長さ解析関数**

    ?> ZendVMはマルチスレッド環境で実行できないため、基層は自動的にMutexロックを使用してPHP長さ関数をロックし、PHP関数の並行実行を避けます。1.9.3またはそれ以上のバージョンで利用できます。

    !> 長度解析関数では、IO操作をブロックすることはできません。そうでなければ、すべての[Reactor](/learn?id=reactor线程)线程がブロックされる可能性があります

    ```php
    $server = new Swoole\Server("127.0.0.1", 9501);
    
    $server->set(array(
        'open_length_check'   => true,
        'dispatch_mode'       => 1,
        'package_length_func' => function ($data) {
          if (strlen($data) < 8) {
              return 0;
          }
          $length = intval(trim(substr($data, 0, 8)));
          if ($length <= 0) {
              return -1;
          }
          return $length + 8;
        },
        'package_max_length'  => 2000000,  //プロトコルの最大長さ
    ));
    
    $server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        var_dump($data);
        echo "#{$server->worker_id}>> received length=" . strlen($data) . "\n";
    });
    
    $server->start();
    ```

    * **C++長さ解析関数**

    ?> 他のPHP拡張では、swoole_add_functionを使用して長さ関数をSwooleエンジンに登録します。
    
    !> C++長さ関数を呼び出す際には、基層がロックを加えません。呼び出し側でスレッド安全性を保証する必要があります
    
    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"
    
    using namespace std;
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);
    
    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW_OK;
    }
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length)
    {
        printf("cpp, size=%d\n", length);
        return 100;
    }
    ```


### パッケージの最大長さ

?> **データパケットの最大サイズを設定します。単位は字节です。**【デフォルト値：`2M` 即ち `2 * 1024 * 1024`、最小値は`64K`】

?> [open_length_check](/server/setting?id=open_length_check)/[open_eof_check](/server/setting?id=open_eof_check)/[open_eof_split](/server/setting?id=open_eof_split)/[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol)などのプロトコルの解析を有効にすると、Swoole基層はデータパケットを組み立てます。この時、データパケットが完全には受け取られていない場合、すべてのデータはメモリに保存されています。  
そのため、`package_max_length`を設定する必要があります。データパケットが最大で使用できるメモリサイズです。もし1万個のTCP接続が同時にデータを送信し、各データパケットが`2M`である場合、最も極端な状況では、`20G`のメモリスペースを占有することになります。

  * **ヒント**

    * `open_length_check`：パケットの長さがおそらく`package_max_length`を超える場合、そのデータを直接丢弃し、接続を閉じます。メモリは占有しません；
    * `open_eof_check`：データパケットの長さが事前に分かりませんので、受け取ったデータはメモリに保存され、継続して増加します。メモリ使用量がおそらく`package_max_length`を超える場合、そのデータを直接丢弃し、接続を閉じます；
    * `open_http_protocol`：GET请求は最大で`8K`を許容し、設定を変更することはできません。POST请求では`Content-Length`が検出され、`Content-Length`がおそらく`package_max_length`を超える場合、そのデータを直接丢弃し、HTTP 400エラーを送信し、接続を閉じます；

  * **注意**

    !> このパラメータは大きすぎると、大きなメモリを占有することになります


### open_http_protocol

?> **HTTPプロトコルの処理を有効にします。**【デフォルト値：`false`】

?> HTTPプロトコルの処理を有効にすると、[Swoole\Http\Server](/http_server)は自動的にこのオプションを有効にします。`false`に設定するとHTTPプロトコルの処理を閉じます。


### open_mqtt_protocol

?> **MQTTプロトコルの処理を有効にします。**【デフォルト値：`false`】

?> 有効にするとMQTT包头を解析し、workerプロセスは[onReceive](/server/events?id=onreceive)で毎回完全なMQTTデータパケットを返します。

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```


### open_redis_protocol

?> **Redisプロトコルの処理を有効にします。**【デフォルト値：`false`】

?> 有効にするとRedisプロトコルを解析し、workerプロセスは[onReceive](/server/events?id=onreceive)で毎回完全なRedisデータパケットを返します。Redis\Serverを直接使用することをお勧めします。

```php
$server->set(array(
  'open_redis_protocol' => true
));
```


### open_websocket_protocol

?> **WebSocketプロトコルの処理を有効にします。**【デフォルト値：`false`】

?> WebSocketプロトコルの処理を有効にすると、[Swoole\WebSocket\Server](websocket_server)は自動的にこのオプションを有効にします。`false`に設定するとwebsocketプロトコルの処理を閉じます。  
`open_websocket_protocol`オプションを`true`に設定すると、自動的に`open_http_protocol`オプションも`true`に設定されます。


### open_websocket_close_frame

?> **WebSocketプロトコルでのクローズフレームを有効にします。**【デフォルト値：`false`】

?> （`opcode`が`0x08`のフレーム）`onMessage`回调で受け取ります

?> 有効にすると、WebSocketServerの`onMessage`回调で、クライアントまたはサーバーから送信されたクローズフレームを受け取ることができます。開発者は自分でそれを処理することができます。

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->set(array("open_websocket_close_frame" => true));

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {});

$server->start();
```
### open_tcp_nodelay

?> **TCPノンブロッキングを有効にする。**【デフォルト値：`false`】

?> etkinすると、TCP接続がデータを送信する際にNagleの合并为オフにし、すぐに対端のTCP接続に送信されます。例えば、コマンド行ターミナルでコマンドを入力すると、すぐにサーバーに送信する必要があり、応答速度を向上させることができます。Nagleアルゴリズムについては、自分でGoogleで調べてください。

### open_cpu_affinity 

?> **CPUアフィニティ設定を有効にする。** 【デフォルト `false`】

?> 多核のハードウェアプラットフォームでは、この機能 etkinにするとSwooleのreactor线程/workerプロセスを特定のCPUコアに固定します。プロセス/スレッドのランタイムが複数のコア間で切り替わるのを避け、CPUキャッシュのhit率を向上させることができます。

  * **ヒント**

    * **プロセスのCPUアフィニティ設定を確認するにはtasksetコマンドを使用する：**

    ```bash
    taskset -p プロセスID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > maskはマスク番号で、bitによって各CPUコアを表します。某一位が0であれば、このコアにプロセスが割り当てられ、プロセスはこのCPUでスケジュールされます。0であれば、プロセスはこのCPUでスケジュールされません。例では、pidが24666のプロセスのmaskはfで、CPUに割り当てられておらず、オペレーティングシステムがこのプロセスを任意のCPUコアにスケジュールします。pidが24901のプロセスのmaskは8で、8を二進数に変換すると `1000` であり、このプロセスは第4のCPUコアに割り当てられています。

### cpu_affinity_ignore

?> **I/O密集型プログラムでは、すべてのネットワーク割り込みがCPU0で処理されます。ネットワークI/Oが重い場合、CPU0の負荷が高くなりすぎると、ネットワーク割り込みがタイムリーに処理できなくなり、ネットワークの受け取りと送信の能力が低下します。**

?> このオプションを設定しなければ、SwooleはすべてのCPUコアを使用し、下層ではreactor_idまたはworker_idとCPUコア数を模运算してCPUバウンドを設定します。カーネルとNICにはマルチキュー特性がある場合、ネットワーク割り込みは複数のコアに分布し、ネットワーク割り込みの圧力を軽減できます。

```php
array('cpu_affinity_ignore' => array(0, 1)) // 配列を引数として受け取り、array(0, 1)はCPU0とCPU1を使用せず、ネットワーク割り込みを処理するために特別に空けています。
```

  * **ヒント**

    * **ネットワーク割り込みを確認する**

```shell
[~]$ cat /proc/interrupts 
           CPU0       CPU1       CPU2       CPU3       
  0: 1383283707          0          0          0    IO-APIC-edge  timer
  1:          3          0          0          0    IO-APIC-edge  i8042
  3:         11          0          0          0    IO-APIC-edge  serial
  8:          1          0          0          0    IO-APIC-edge  rtc
  9:          0          0          0          0   IO-APIC-level  acpi
 12:          4          0          0          0    IO-APIC-edge  i8042
 14:         25          0          0          0    IO-APIC-edge  ide0
 82:         85          0          0          0   IO-APIC-level  uhci_hcd:usb5
 90:         96          0          0          0   IO-APIC-level  uhci_hcd:usb6
114:    1067499          0          0          0       PCI-MSI-X  cciss0
130:   96508322          0          0          0         PCI-MSI  eth0
138:     384295          0          0          0         PCI-MSI  eth1
169:          0          0          0          0   IO-APIC-level  ehci_hcd:usb1, uhci_hcd:usb2
177:          0          0          0          0   IO-APIC-level  uhci_hcd:usb3
185:          0          0          0          0   IO-APIC-level  uhci_hcd:usb4
NMI:      11370       6399       6845       6300 
LOC: 1383174675 1383278112 1383174810 1383277705 
ERR:          0
MIS:          0
```

`eth0/eth1`はネットワーク割り込みの回数です。もし`CPU0 - CPU3`が均等に分布している場合、NICにはマルチキュー特性があることを証明しています。もしすべてあるコアに集中している場合、ネットワーク割り込みがすべてこのCPUで処理されており、このCPUが100%を超えると、システムはネットワークリクエストを処理できなくなります。この場合、`cpu_affinity_ignore`設定を使用してこのCPUを空けて、ネットワーク割り込みを処理するために使用する必要があります。

図の状況では、`cpu_affinity_ignore => array(0)`と設定すべきです。

?> `top`コマンドを使用して `->` を入力して、各コアの使用率を確認することができます。

  * **注意**

    !> このオプションは `open_cpu_affinity`と同時に設定しなければ効果を発揮しません。

### tcp_defer_accept

?> **TCPのdefer_accept特性を有効にする。**【デフォルト値：`false`】

?> 数値を設定することで、TCP接続にデータが送信された時にのみacceptをトリガーすることができます。

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

  * **ヒント**

    * **TCPのdefer_accept特性を有効にした後、acceptと[onConnect](/server/events?id=onconnect)に対応する時間が変わります。5秒に設定した場合：**

      * クライアントがサーバーに接続した後、すぐにacceptはトリガーされません。
      * クライアントが5秒以内にデータを送信すると、同時にaccept/onConnect/onReceiveが順次トリガーされます。
      * クライアントが5秒以内にデータを送信しなかった場合、accept/onConnectがトリガーされます。

### ssl_cert_file / ssl_key_file :id=ssl_cert_file

?> **SSLトンネル暗号化を設定する。**

?> 値をファイル名文字列として設定し、cert証明書とkey秘密鍵の路径を指定します。

  * **ヒント**

    * **PEMからDERへの変換**

    ```shell
    openssl x509 -in cert.crt -outform der -out cert.der
    ```

    * **DERからPEMへの変換**

    ```shell
    openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
    ```

  * **注意**

    !> -`HTTPS`アプリケーションでは、ブラウザが証明書を信頼している必要があります。そうでなければウェブページを閲覧できません。  
    -`wss`アプリケーションでは、`WebSocket`接続を開始するページは `HTTPS`を使用する必要があります。  
    -ブラウザがSSL証明書を信頼していないと、`wss`を使用できません。  
    - ファイルはPEM形式でなければならず、DER形式はサポートされていません。`openssl`ツールを使用して変換することができます。

    !> `SSL`を使用するには、Swooleをコンパイルする際に[--enable-openssl](/environment?id=compile_options)オプションを加える必要があります。

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```

### ssl_method

!> 此パラメータは [v4.5.4](/version/bc?id=_454)のバージョンで削除されました。`ssl_protocols`を使用してください。

?> **OpenSSLトンネル暗号化のアルゴリズムを設定する。**【デフォルト値：`SWOOLE_SSLv23_METHOD`】、サポートされるタイプについては[SSL暗号化方法](/consts?id=ssl-encryption-methods)を参照してください。

?> `Server`と`Client`が使用するアルゴリズムは一致している必要があります。そうでなければ`SSL/TLS`ハンドシェイクに失敗し、接続が切断されます。

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```
### ssl_protocols

?> **OpenSSLトンネル暗号化に使用するプロトコルを設定します。**【デフォルト値：`0`、すべてのプロトコルをサポート】、サポートされるタイプは[SSLプロトコル](/consts?id=ssl-protocol)を参照してください。

!> Swooleバージョン >= `v4.5.4`で利用可能

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```

### ssl_sni_certs

?> **SNI（サーバー名識別）証明書を設定します。**

!> Swooleバージョン >= `v4.6.0`で利用可能

```php
$server->set([
    'ssl_cert_file' => __DIR__ . '/server.crt',
    'ssl_key_file' => __DIR__ . '/server.key',
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'ssl_sni_certs' => [
        'cs.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_cs_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_cs_key.pem',
        ],
        'uk.php.net' => [
            'ssl_cert_file' =>  __DIR__ . '/sni_server_uk_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_uk_key.pem',
        ],
        'us.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_us_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_us_key.pem',
        ],
    ]
]);
```

### ssl_ciphers

?> **OpenSSL暗号化アルゴリズムを設定します。**【デフォルト値：`EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

  * **ヒント**

    * `ssl_ciphers`を空文字列に設定すると、`openssl`が暗号化アルゴリズムを選択します

### ssl_verify_peer

?> **SSLで対端の証明書を検証します。**【デフォルト値：`false`】

?>デフォルトではオフで、クライアントの証明書を検証しません。オンにすると、同時に`ssl_client_cert_file`オプションを設定する必要があります

### ssl_allow_self_signed

?> **自己署名の証明書を許可します。**【デフォルト値：`false`】

### ssl_client_cert_file

?> **クライアント証明書で使用する根証明書を設定します。**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set(array(
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,
    'ssl_allow_self_signed' => true,
    'ssl_client_cert_file'  => __DIR__ . '/config/ca.crt',
));
```

!> TCPサービスで検証に失敗した場合、基層で接続を自ら閉じるようになります。

### ssl_compress

?> **SSL/TLS圧縮を有効にします。** [Co\Client](/coroutine_client/client)を使用する際は、別名`ssl_disable_compression`があります

### ssl_verify_depth

?> **証明書のチェーンが深すぎて、このオプションの値を超えた場合、検証を終了します。**

### ssl_prefer_server_ciphers

?> **サーバー側の保護を有効にし、BEAST攻撃を防ぎます。**

### ssl_dhparam

?> **DHEパケットキーのDiffie-Hellmanパラメータを指定します。**

### ssl_ecdh_curve

?> **ECDHキーの交換に使用するcurveを指定します。**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_compress'                => true,
    'ssl_verify_depth'            => 10,
    'ssl_prefer_server_ciphers'   => true,
    'ssl_dhparam'                 => '',
    'ssl_ecdh_curve'              => '',
]);
```

### user

?> **Worker/TaskWorkerサブプロセスの所属ユーザーを设置します。**【デフォルト値：スクリプトを実行するユーザー】

?> サーバーが`1024`以下のポートを监听する必要がある場合は、`root`権限が必要です。しかし、プログラムが`root`ユーザーで実行されれば、コードに漏洞があれば攻撃者は`root`としてリモートコマンドを実行することができ、リスクが非常に高くなります。`user`項目を設定した後、マスタープロセスは`root`権限で実行され、サブプロセスは通常のユーザー権限で実行されます。

```php
$server->set(array(
  'user' => 'Apache'
));
```

  * **注意**

    !> - `root`ユーザーで起動した場合にのみ有効  
    - `user/group`設定項目を使用して作業プロセスを通常のユーザーに設定した後、作業プロセスが`shutdown`/[reload](/server/methods?id=reload)方法でサービスを閉じたり再起動したりすることはできません。サービスを閉じるか再起動するには、`root`アカウントで`shell`ターミナルで`kill`コマンドを実行する必要があります。

### group

?> **Worker/TaskWorkerサブプロセスのプロセスユーザーグループを设置します。**【デフォルト値：スクリプトを実行するユーザーグループ】

?> `user`設定と同じで、この設定はプロセスの所属ユーザーグループを変更し、サーバープログラムの安全性を高めます。

```php
$server->set(array(
  'group' => 'www-data'
));
```

  * **注意**

    !> - `root`ユーザーで起動した場合にのみ有効

### chroot

?> **Workerプロセスのファイルシステムの根ディレクトリをリダイレクトします。**

?> この設定により、プロセスは実際のオペレーティングシステムのファイルシステムから隔離された読み書きを行うことができ、安全性を高めます。

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```

### pid_file

?> **PIDファイルの位置を设置します。**

?> Serverが起動すると自動的にmasterプロセスのPIDをファイルに書き込み、Serverが閉じると自動的にPIDファイルを削除します。

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

  * **注意**

    !> 使用する際は、Serverが非正常に終了した場合でもPIDファイルは削除されませんので、[Swoole\Process::kill($pid, 0)](/process/process?id=kill)を使用してプロセスが実際に存在するかどうかを検出する必要があります

### buffer_input_size / input_buffer_size :id=buffer_input_size

?> **入力バッファの内存サイズを配置します。**【デフォルト値：`2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```

### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **出力バッファの内存サイズを配置します。**【デフォルト値：`2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, //数字でなければなりません
]);
```

  * **ヒント**

    !> Swooleバージョン >= `v4.6.7`では、デフォルト値は無符号INTの最大値`UINT_MAX`です

    *単位はバイトで、デフォルトは`2M`です。例えば`32 * 1024 * 1024`と設定すると、単回の`Server->send`で最大`32M`バイトのデータを送信することができます
    * `Server->send`、`Http\Server->end/write`、`WebSocket\Server->push`などのデータ送信コマンドを呼び出す際、単回の最大送信データは`buffer_output_size`設定を超えてはなりません。

    !> このパラメータは[SWOOLE_PROCESS](/learn?id=swoole_process)モードでのみ機能します。なぜなら、PROCESSモードではWorkerプロセスのデータはMasterプロセスに送信され、Masterプロセスがクライアントに送信するため、各WorkerプロセスはMasterプロセスとバッファを開設するためです。[参照](/learn?id=reactor线程)

### socket_buffer_size

?> **クライアント接続のバッファ長さを配置します。**【デフォルト値：`2M`】

?> `buffer_output_size`とは異なり、`buffer_output_size`はWorkerプロセスの単回`send`のサイズ制限ですが、`socket_buffer_size`はWorkerとMasterプロセス間の通信バッファの総サイズを設定するためです。SWOOLE_PROCESSモードを参照してください。

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, //数字でなければならず、単位はバイトです。例えば128 * 1024 *1024と設定すると、各TCPクライアント接続で最大128Mバイトの待送データが許されます
]);
```
- **データ転送バッファ**

    - Masterプロセスがクライアントに大量のデータを転送する際、すぐに転送されるわけではありません。この時、転送されるデータはサーバー側のメモリバッファに格納されます。このパラメータはメモリバッファのサイズを調整することができます。
    
    - 転送データが多すぎると、バッファがいっぱいに満たされた後、Serverは以下のエラー情報を報告します：
    
    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```
    
    ?>転送バッファがいっぱいに塞がれたために`send`が失敗しますが、これは現在のクライアントにのみ影響を与え、他のクライアントには影響しません。
    サーバーに多くの`TCP`接続がある場合、最悪の場合は`serv->max_connection * socket_buffer_size`バイトのメモリを使用します。
    
    -特に外部通信のサーバープログラムでは、ネットワーク通信が遅いため、連続してデータを転送すると、バッファはすぐにいっぱいになります。転送されたデータはすべてServerのメモリに蓄積されます。そのため、このようなアプリケーションは設計時にネットワークの伝送能力を考慮し、まずメッセージをディスクに保存し、クライアントがサーバーを受け取ったことを通知してから、新しいデータを転送するべきです。
    
    -例えばビデオライブ配信サービスでは、Aユーザーの帯域幅は`100M`で、1秒間に`10M`のデータを転送するのは完全に可能です。しかし、Bユーザーの帯域幅は`1M`だけで、1秒間に`10M`のデータを転送すると、Bユーザーは100秒かかるかもしれません。この時、データはすべてサーバーのメモリに蓄積されます。
    
    -データの内容の種類に応じて、異なる処理を行うことができます。取り消し可能な内容、例えばビデオライブ配信などのサービスでは、ネットワークが悪い場合はいくつかのデータフレームを捨てることが完全に受け入れられます。取り消し不可な内容、例えばWeChatのメッセージなどは、まずサーバーのディスクに保存し、100件のメッセージを1組としてください。ユーザーがこの一組のメッセージを受け取った後、次にディスクから次の一組のメッセージを取り出してクライアントに転送します。

### enable_unsafe_event

?> **onConnect/onCloseイベントを有効にする。**【デフォルト値：`false`】

?> Swooleは設定 [dispatch_mode](/server/setting?id=dispatch_mode)=1または3の場合、システムはonConnect/onReceive/onCloseの順序を保証できないため、デフォルトでonConnect/onCloseイベントをオフにしています。
アプリケーションでonConnect/onCloseイベントが必要であり、順序問題によるセキュリティリスクを受け入れることができる場合は、enable_unsafe_eventをtrueに設定してonConnect/onCloseイベントを有効にすることができます。

### discard_timeout_request

?> **閉じられた接続のデータリクエストを丢弃する。**【デフォルト値：`true`】

?> Swooleは設定[dispatch_mode](/server/setting?id=dispatch_mode)=`1`または3の場合、システムはonConnect/onReceive/onCloseの順序を保証でき므로、接続が閉じた後にworkerプロセスに到達するリクエストデータがある可能性があります。

  * **ヒント**

    * discard_timeout_requestのデフォルト設定はtrueで、workerプロセスが閉じられた接続からのデータリクエストを受け取った場合、自動的に丢弃されます。
    * discard_timeout_requestをfalseに設定すると、接続が閉じているかどうかにかかわらずworkerプロセスはデータリクエストを処理します。

### enable_reuse_port

?> **ポートの再利用を設定する。**【デフォルト値：`false`】

?> ポートの再利用を有効にすると、同じポートをlistenするServerプログラムを何度も起動できます。

  * **ヒント**

    * enable_reuse_port = trueではポートの再利用を有効にします。
    * enable_reuse_port = falseではポートの再利用を無効にします。

!> Linux-3.9.0以上のバージョンでのみ利用可能 Swoole4.5以上のバージョンでのみ利用可能

### enable_delay_receive

?> **acceptしたクライアント接続をEventLoopに自動追加しないようにする。**【デフォルト値：`false`】

?> このオプションをtrueに設定すると、acceptしたクライアント接続はEventLoopに自動的に追加されず、onConnect回调のみがトリガーされます。workerプロセスは[$server->confirm($fd)](/server/methods?id=confirm)を呼び出して接続を確認し、その時点でfdをEventLoopに追加してデータの收发を始めたり、$server->close($fd)を呼び出して接続を閉じたりすることができます。

```php
//enable_delay_receiveオプションを有効にする
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        //接続を確認し、データ受信を始めます
        $server->confirm($fd);
    });
});
```

### reload_async

?> **非同期再起動のスイッチを設定する。**【デフォルト値：`true`】

?> 非同期再起動のスイッチをtrueに設定すると、非同期安全再起動機能が有効になり、workerプロセスは非同期イベントが完了するのを待ってから退出します。詳細は[サービスを正しく再起動する方法](/question/use?id=swoole如何正确的重启 service)をご覧ください。

?> reload_asyncをtrueにする主な理由は、サービスを再読み込みする際に、コーラスや非同期タスクが正常に終了できるようにすることです。

```php
$server->set([
  'reload_async' => true
]);
```

  * **コーラスモード**

    * 4.xバージョンで[enable_coroutine](/server/setting?id=enable_coroutine)を有効にした場合、基層はコーラスの数を検出する额外機能が加わります。現在コーラスがない場合のみプロセスが退出します。enable_coroutineを有効にしても、reload_async => falseであっても、reload_asyncを強制的に有効にします。

### max_wait_time

?> **Workerプロセスがサービスの停止通知を受け取った後の最大待ち時間を設定する**【デフォルト値：`3`】

?> workerがブロックしてカク Dingしているためにworkerが正常にreloadできないことがあります。これは一部の生産シナリオ、例えばコードのホットアップ데이트が必要な場合を満たすことができません。そのため、Swooleにはプロセスの再起動タイムアウトオプションが追加されました。詳細は[サービスを正しく再起動する方法](/question/use?id=swoole如何正确的 restart service)をご覧ください。

  * **ヒント**

    * **プロセスが再起動、閉鎖のシグナルを受け取ったり、max_requestに達したりした場合、管理プロセスはそのworkerプロセスを再起動します。以下のステップに従います：**

      * 基層は(`max_wait_time`)秒のタイマーを追加し、タイマーがトリガーされた後、プロセスが存在するかどうかを確認します。存在する場合は、強制的に殺し、新たにプロセスを立ち上げます。
      * onWorkerStop回调内で後始末を行い、max_wait_time秒以内に後始末を完了する必要があります。
      * ターゲットプロセスに順にSIGTERMシグナルを送信し、プロセスを殺します。

  * **注意**

    !> v4.4.x以前はデフォルトで30秒です


### tcp_fastopen

?> **TCPのファストオープンの特性を有効にする。**【デフォルト値：`false`】

?> この特性は、TCPのショートCONNEクトの応答速度を向上させることができます。クライアントがハンドシェイクの第3段階でSYNパケットを送信する際にデータを含みます。

```php
$server->set([
  'tcp_fastopen' => true
]);
```

  * **ヒント**

    * このパラメータは监听ポートに設定できます。深く理解したい方は[google論文](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf)をご覧ください。


### request_slowlog_file

?> **リクエストのSlowLogを有効にする。** v4.4.8バージョンから[廃止](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366)

!>[このSlowLogの方法は同期ブロッキングのプロセスでのみ機能し、コーラス環境では使用できません。Swoole4はデフォルトでコーラスを有効にするため、enable_coroutineをオフにするしかありません。そのため、使用しないでください。[Swoole Tracker](https://business.swoole.com/tracker/index)のブロッキング検出ツールを使用してください。

?> 有効にすると、Managerプロセスは時計シグナルを設定し、定期的にすべてのTaskとWorkerプロセスをチェックします。プロセスがブロッキングしてリクエストが規定の時間を超える場合、自動的にプロセスのPHP関数呼び出しスタックをプリントします。

?> 基層はptraceシステム呼び出しに基づいており、一部のシステムではptraceがオフされているため、SlowRequestを追跡することができません。kernel.yama.ptrace_scopeカーネルパラメータが0でないことを確認してください。

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

  * **タイムアウト時間**

```php
$server->set([
    'request_slowlog_timeout' => 2, // リクエストのタイムアウト時間を2秒に設定
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

!>[書き込める権限のないファイルでは、ファイルを作成することが失敗し、基層は致命的なエラーを投げます。
### enable_coroutine

?> **非同期スタイルのサーバーでコーラブのサポートを有効にするかどうか**

?> `enable_coroutine` をオフにすると、イベント回调関数 `/server/events` では自動的にコーラブが生成されなくなります。コーラブを使わない場合は、この設定は性能を少し向上させることができます。Swooleコーラブについての詳細は `/coroutine` をご覧ください。

  * **設定方法**
    
    * `php.ini`で `swoole.enable_coroutine = 'Off'` （[ini設定文档](/other/config.md)を参照）
    * `$server->set(['enable_coroutine' => false]);`はiniよりも優先されます。

  * **`enable_coroutine`オプションの影響範囲**

      * onWorkerStart
      * onConnect
      * onOpen
      * onReceive
      * [setHandler](/redis_server?id=sethandler)
      * onPacket
      * onRequest
      * onMessage
      * onPipeMessage
      * onFinish
      * onClose
      * tick/after 定時器

!> `enable_coroutine`をONにすると、上記の回调関数で自動的にコーラブが生成されます。

* `enable_coroutine`を`true`にすると、底層では自動的に[onRequest](/http_server?id=on)回调でコーラブが生成されます。開発者は自分で`go`関数[create](/coroutine/coroutine?id=create)してコーラブを生成する必要はありません。
* `enable_coroutine`を`false`にすると、底層では自動的にコーラブが生成されません。開発者がコーラブを使用したい場合は、自分で`go`関数でコーラブを生成しなければなりません。コーラブ特性を使わない場合は、`Swoole1.x`と100%同じ処理になります。
* 注意：この設定は、Swooleがリクエストをコーラブで処理することを意味します。イベントにブロック関数が含まれている場合は、事前に[一键协程化](/runtime)をONにして、`sleep`や`mysqlnd`などのブロック関数や拡張をコーラブ化する必要があります。

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    //内蔵コーラブをオフにする
    'enable_coroutine' => false,
]);

$server->on("request", function ($request, $response) {
    if ($request->server['request_uri'] == '/coro') {
        go(function () use ($response) {
            co::sleep(0.2);
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });
    } else {
        $response->header("Content-Type", "text/plain");
        $response->end("Hello World\n");
    }
});

$server->start();
```


### send_yield

?> **データを送信する際にバッファーのメモリが不足した場合、現在のコーラブ内で[yield](/coroutine?id=协程调度)し、データ送信が完了するのを待って、バッファーが空になるまで自動的に[resume](/coroutine?id=协程调度)当前コーラブし、`send`データを続ける。**【デフォルト値：dispatch_mod `/server/setting?id=dispatch_mode`が2/4の時に利用可能で、デフォルトでON】

* `Server/Client->send`が`false`を返し、エラーコードが`SW_ERROR_OUTPUT_BUFFER_OVERFLOW`の場合、PHP層には`false`を返しず、現在のコーラブを[yield](/coroutine?id=协程调度)して挂ける
* `Server/Client`はバッファーが空になるイベントを監視し、そのイベントが発生した後は、バッファー内のデータが送信され完毕しているため、対応するコーラブを[resume](/coroutine?id=协程调度)する
* コーラブが回復した後、再び`Server/Client->send`を呼び出してバッファーにデータを書き込むが、この時バッファーは空いているため、送信は必ず成功する

改善前

```php
for ($i = 0; $i < 100; $i++) {
    //バッファーがいっぱいに満たされた場合は直接falseを返し、output buffer overflowのerrorを報告します
    $server->send($fd, $data_2m);
}
```

改善後

```php
for ($i = 0; $i < 100; $i++) {
    //バッファーがいっぱいに満たされた場合は、現在のコーラブでyieldし、送信が完了した後にresumeして以下を続ける
    $server->send($fd, $data_2m);
}
```

!> この特性は底層のデフォルト行動を変更するため、手動でオフにすることができます

```php
$server->set([
    'send_yield' => false,
]);
```

  * __影響範囲__

    * [Swoole\Server::send](/server/methods?id=send)
    * [Swoole\Http\Response::write](/http_server?id=write)
    * [Swoole\WebSocket\Server::push](/websocket_server?id=push)
    * [Swoole\Coroutine\Client::send](/coroutine_client/client?id=send)
    * [Swoole\Coroutine\Http\Client::push](/coroutine_client/http_client?id=push)


### send_timeout

送信タイムアウトを設定し、`send_yield`と組み合わせて使用します。指定された時間内にデータがバッファーに送信されなければ、底層は`false`を返し、エラーコードを`ETIMEDOUT`に設定します。エラーコードは[getLastError()](/server/methods?id=getlasterror)メソッドで取得できます。

> 型は浮動小数点型で、単位は秒です。最小粒度はミリ秒です。

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1.5秒
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false and $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "送信タイムアウト\n";
    }
}
```


### hook_flags

?> **「 一键协程化」Hookの関数範囲を設定します。**【デフォルト値：Hookしない】

!> Swooleバージョンは `v4.5+` 或 [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 可用、詳細は[一键协程化](/runtime)をご覧ください。

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```


### buffer_high_watermark

?> **バッファーの高水位線（byte为单位）を設定します。**

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```


### buffer_low_watermark

?> **バッファーの低水位線（byte为单位）を設定します。**

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```


### tcp_user_timeout

?> TCP_USER_TIMEOUTオプションはTCP層のsocketオプションで、データ包が送信された後にACK確認を受け取らない最大の時間です。単位はミリ秒です。具体的なことはmanページを参照してください。

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10秒
]);
```

!> Swooleバージョン >= `v4.5.3-alpha` 可用


### stats_file

?> **[stats()](/server/methods?id=stats)の内容を書き込むファイルのpathを設定します。設定すると、自動的に[onWorkerStart](/server/events?id=onworkerstart)時にタイマーを設定し、定期的に[stats()](/server/methods?id=stats)の内容を指定されたファイルに書き出します**

```php
$server->set([
    'stats_file' => __DIR__ . '/stats.log',
]);
```

!> Swooleバージョン >= `v4.5.5` 可用


### event_object

?> **このオプションを設定すると、イベント回调は[对象スタイル](/server/events?id=回调对象)を使用します。**【デフォルト値：`false`】

```php
$server->set([
    'event_object' => true,
]);
```

!> Swooleバージョン >= `v4.6.0` 可用


### start_session_id

?> **開始 session IDを設定します。**

```php
$server->set([
    'start_session_id' => 10,
]);
```

!> Swooleバージョン >= `v4.6.0` 可用


### single_thread

?> **単一スレッドに設定します。** 启用後、ReactorスレッドはMasterプロセス内のMasterスレッドと合併し、Masterスレッドが論理を処理します。PHP ZTSの場合、`SWOOLE_PROCESS`モードを使用する場合、この値を`true`に設定する必要があります。

```php
$server->set([
    'single_thread' => true,
]);
```

!> Swooleバージョン >= `v4.2.13` 可用
### max_queued_bytes

?> **受信バッファーの最大キュー長さを設定します。** 超えたら受信を停止します。

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Swooleバージョン >= `v4.5.0` 用


### admin_server

?> **Swoole Dashboard([http://dashboard.swoole.com/])でサービスの情報を確認するためにadmin_serverサービスを設定します。**

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Swooleバージョン >= `v4.8.0` 用


### bootstrap

?> **マルチスレッドモードでのプログラムの入口ファイルで、デフォルトは現在実行されているスクリプトのファイル名です。**

!> Swooleバージョン >= `v6.0` 、 `PHP`は`ZTS`モードで、Swooleを编译する時に`--enable-swoole-thread`を有効にしました

```php
$server->set([
    'bootstrap' => __FILE__,
]);
```

### init_arguments

?> **マルチスレッドでのデータ共有データを設定します。この設定には回调関数が必要で、サーバーが起動する時に自動的にこの関数が実行されます**

!> Swooleには多くのスレッド安全なコンテナが組み込まれています。[並行Map](/thread/map)、[並行List](/thread/arraylist)、[並行キュー](/thread/queue)などがあり、関数内で安全でない変数を返却しないでください。

!> Swooleバージョン >= `v6.0` 、 `PHP`は`ZTS`モードで、Swooleを编译する時に`--enable-swoole-thread`を有効にしました

```php
$server->set([
    'init_arguments' => function() { return new Swoole\Thread\Map(); },
]);

$server->on('request', function($request, $response) {
    $map = Swoole\Thread::getArguments();
});
```
