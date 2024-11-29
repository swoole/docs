# 設定

[Swoole\Server->set()](/server/methods?id=set) 関数は、`Server`の運用時に各種パラメータを設定するために使用されます。このセクションのすべてのサブページは、設定配列の要素です。

!> v4.5.5 [バージョン](/version/log?id=v455) 以降では、基本が設定されたパラメータが正しいかどうかをチェックし、Swooleが提供していないパラメータが設定された場合、Warningが発生します。

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```
### debug_mode

?> ログモードを`debug`に設定し、デバッグモードを有効にする必要があります。これは、--enable-debug 编译時に設定する必要があります。

```php
$server->set([
  'debug_mode' => true
])
```
### trace_flags

?>トレースロギングのタグを設定し、一部のトレースロギングのみを印刷します。`trace_flags`は、`|`または演算子を使用して複数のトレース項目を設定できます。これは、--enable-trace-log 编译時に設定する必要があります。

基本は以下のトレース項目をサポートしており、SWOOLE_TRACE_ALLを使用してすべてのプロジェクトをトレースできます：

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
守护プロセスモードを開始した後 `(daemonize => true)`、標準出力は `log_file`にリダイレクトされます。PHPコードでの `echo/var_dump/print`などの画面への印刷内容は `log_file`ファイルに書き出されます。

  * **ヒント**

    * `log_file`内のロギングは、運用時エラー記録のためであり、長期保存の必要はありません。

    * **ログ番号**

      ?> ログ情報では、プロセスIDの前にいくつかの番号が加えられ、ログが発生したスレッド/プロセスのタイプを示します。

        * `#` マスタープロセス
        * `$` マネージャープロセス
        * `*` ワーカープロセス
        * `^` タスクプロセス

    * **ログファイルの再開**

      ?> サーバープログラムが運用中にログファイルが `mv` 移動したり `unlink` 删除された後、ログ情報は正常に書き出せなくなります。この時、Serverに `SIGRTMIN`シグナルを送信することで、ログファイルを再開することができます。

      * Linuxプラットフォームのみサポートされています
      * UserProcess [/server/methods?id=addProcess]プロセスはサポートされていません

  * **注意**

    !> `log_file`は自動でファイルを分割しませんので、定期的にこのファイルを清掃する必要があります。`log_file`の出力を観察することで、サーバーの各種例外情報や警告を得ることができます。
### log_level

?> **Serverエラーロギングの印刷レベルを設定する。範囲は`0-6`です。`log_level`より低いレベルのログ情報は出力されません。**【デフォルト値：`SWOOLE_LOG_INFO`】

対応するレベル定数については[ログレベル](/consts?id=ログレベル)を参照してください。

  * **注意**

    !> `SWOOLE_LOG_DEBUG`と`SWOOLE_LOG_TRACE`は、--enable-debug-log [/environment?id=debug参数]と--enable-trace-log [/environment?id=debug参数]で編译されたバージョンでのみ使用できます。  
    守护プロセスを有効にする場合、基本はプログラム内のすべての画面への印刷内容を[log_file](/server/setting?id=log_file)に書き出しますが、この内容は`log_level`にはコントロールされません。
### log_date_format

?> **Serverロギングの時間フォーマットを設定する。フォーマットは [strftime](https://www.php.net/manual/zh/function.strftime.php)の`format`を参照してください**

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```
### log_date_with_microseconds

?> **Serverロギングの精度を設定する。微秒を含むかどうか。【デフォルト値：`false`】
### log_rotation

?> **Serverロギングの分割を設定する。【デフォルト値：`SWOOLE_LOG_ROTATION_SINGLE`】

| 定数                             | 説明   | バージョン情報 |
| -------------------------------- | ------ | -------- |
| SWOOLE_LOG_ROTATION_SINGLE       | 非有効 | -        |
| SWOOLE_LOG_ROTATION_MONTHLY      | 月次   | v4.5.8   |
| SWOOLE_LOG_ROTATION_DAILY        | 日次   | v4.5.2   |
| SWOOLE_LOG_ROTATION_HOURLY       | 時次   | v4.5.8   |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE | 分次   | v4.5.8   |
### display_errors

?> Swooleエラー情報を開始 / 停止します。

```php
$server->set([
  'display_errors' => true
])
```
### dns_server

?> DNS照会用のIPアドレスを設定します。
### socket_dns_timeout

?> Domain Name Resolutionのタイムアウト時間です。サーバー側でco-routineクライアントを有効にすると、このパラメータはクライアントのDomain Name Resolutionのタイムアウト時間を制御できます。単位は秒です。
### socket_connect_timeout

?> クライアント接続のタイムアウト時間です。サーバー側でco-routineクライアントを有効にすると、このパラメータはクライアントの接続タイムアウト時間を制御できます。単位は秒です。
### socket_write_timeout / socket_send_timeout

?> クライアントのライトタイムアウト時間です。サーバー側でco-routineクライアントを有効にすると、このパラメータはクライアントのライトタイムアウト時間を制御できます。単位は秒です。   
この設定はまた、co-routine化された後の`shell_exec`または[Swoole\Coroutine\System::exec()](/coroutine/system?id=exec)の実行タイムアウト時間を制御するために使用されます。   
### socket_read_timeout / socket_recv_timeout

?> クライアントのリードタイムアウト時間です。サーバー側でco-routineクライアントを有効にすると、このパラメータはクライアントのリードタイムアウト時間を制御できます。単位は秒です。
### max_coroutine / max_coro_num :id=max_coroutine

?> **現在活跃なワーカープロセスの最大co-routine数を設定します。**【デフォルト値：`100000`、Swooleバージョンがv4.4.0-betaより小さい場合はデフォルト値は`3000`です】

?> max_coroutineを超える場合、基本は新しいco-routineを作成することができず、サーバーのSwooleは`exceed max number of coroutine`エラーを投げ出し、TCP Serverは接続を直接閉じ、Http ServerはHttpの503状態コードを返します。

?> Serverプログラム内で実際に最大で作成できるco-routine数は、`worker_num * max_coroutine`に等しく、taskプロセスとUserProcessプロセスのco-routine数は別々に計算されます。

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```
### enable_deadlock_check

?> Co-routineのデッドロックチェックを有効にします。

```php
$server->set([
  'enable_deadlock_check' => true
]);
```
### hook_flags

?> **「ワンクリックco-routine化」Hookの関数範囲を設定します。**【デフォルト値：Hookしない】

!> Swooleバージョンは `v4.5+` 或 [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 可用で、詳細は[ワンクリックco-routine化](/runtime)を参照してください。

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
基本は以下のco-routine化項目をサポートしており、SWOOLE_HOOK_ALLを使用してco-routine化を全て行うことができます：

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

?> Co-routineの先行採取スケジュールを有効にし、あるco-routineの実行時間が長くなりすぎることによる他のco-routineの餓死を防ぎます。co-routineの最大実行時間は`10ms`です。

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```
### c_stack_size / stack_size

?> 単一co-routineの初期Cスタックのメモリサイズを設定します。デフォルトは2Mです。
### aio_core_worker_num

?> AIOの最小ワークスレッド数を設定します。デフォルト値は`cpu`コア数です。
### aio_worker_num 

?> AIOの最大ワークスレッド数を設定します。デフォルト値は`cpu`コア数 * 8です。
### aio_max_wait_time

?> ワークスレッドがタスクを待つ最大時間です。単位は秒です。
### aio_max_idle_time

?> ワークスレッドの最大空闲時間です。単位は秒です。
### reactor_num

?> **起動される [Reactor](/learn?id=reactor线程) スレッド数を設定します。**【デフォルト値：`CPU`コア数】

?> このパラメータを通じて、主プロセス内のイベント処理スレッドの数を調整し、マルチコアを十分に活用できます。デフォルトでは、`CPU`コア数と同じ数のスレッドが有効になります。  
Reactorスレッドはマルチコアを利用できます。例えば、マシンに`128`コアがある場合、基本は`128`スレッドを起動します。  
各スレッドは[EventLoop](/learn?id=什么是eventloop)を維持します。スレッド間は無ロックであり、命令は`128`コアの`CPU`で並列実行できます。  
オペレーティングシステムのスケジュールにはある程度の性能損失があるため、`CPU`コア数の*2に設定することで、CPUの各コアを最大限に活用できます。

  * **ヒント**

    * `reactor_num`は`CPU`コア数の`1-4`倍に設定するのが適切です
    * `reactor_num`は最大で[swoole_cpu_num()](/functions?id=swoole_cpu_num) * 4を超えることはできません

  * **注意**
  !> -`reactor_num`は`worker_num`よりも小さい必要があります。;  -設定された`reactor_num`が`worker_num`を超える場合、自動的に調整されて`worker_num`と等しくされます。;  
-8コアを超えるマシンでは、`reactor_num`はデフォルトで`8`に設定されます。
	### worker_num

?> **起動される`Worker`プロセスの数を設定します。**【デフォルト値：`CPU`コア数】

?> 例として、1つのリクエストが`100ms`かかりますが、`1000QPS`の処理能力を提供するためには、`100`個のプロセスまたはそれ以上を構成する必要があります。  
しかし、プロセスを多くすると、占用するメモリが大幅に増加し、プロセス間の切り替えのコストも大きくなります。したがって、適切に設定するべきです。過剰な設定は避けてください。

  * **ヒント**

    * 业务コードが全[非同期IO](/learn?id=同步io异步io)の場合、ここでは`CPU`コア数の`1-4`倍に設定するのが最も合理的です
    * 业务コードが[同期IO](/learn?id=同步io异步io)の場合、リクエスト応答時間とシステム負荷に基づいて調整する必要があります。例えば：`100-500`
    * デフォルトは[swoole_cpu_num()](/functions?id=swoole_cpu_num)で、最大は[swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000までです
    * 假定各プロセスが`40M`のメモリを占用すると、`100`個のプロセスは`4G`のメモリを占用します。
### max_request

?> **workerプロセスの最大タスク数を設定します。**【デフォルト値：`0` 即ちプロセスは退出しません】

?> workerプロセスが、この数を超えるタスクを処理した後、自動的に退出します。プロセスが退出すると、すべてのメモリとリソースが解放されます

!> このパラメータの主な役割は、PHPプロセスのメモリ漏洩問題を一時的に解決することです。PHPアプリケーションにはゆっくりとしたメモリ漏洩がありますが、具体的な原因を特定したり解決することはできません。このパラメータを設定することで、メモリ漏洩を一時的に解決できますが、内存泄漏のコードを特定し修正するべきであり、この方法ではありません。Swoole Trackerを使用して漏洩したコードを発見することができます。

  * **ヒント**

    * max_requestに達しても必ずしもすぐにプロセスを閉じるわけではありません。[max_wait_time](/server/setting?id=max_wait_time)を参照してください。
    * [SWOOLE_BASE](/learn?id=swoole_base)の下では、max_requestに達するとプロセスを再起動すると、クライアントの接続が切断されます。

  !> workerプロセス内で致命的なエラーが発生したり、手動で`exit`を実行した場合、プロセスは自動的に退出します。masterプロセスは新しいworkerプロセスを再起動して、リクエストを処理し続けます
### max_conn / max_connection

?> **サーバープログラムで許可される最大接続数を設定します。**【デフォルト値：`ulimit -n`】

?> 例として、`max_connection => 10000`とすると、このパラメータはServerが維持できる最大TCP接続数を設定します。この数を超える新しい接続は拒否されます。

  * **ヒント**

    * **デフォルト設定**

      * 应用层でmax_connectionが設定されていない場合、基本は`ulimit -n`の値をデフォルト設定として使用します
      * 4.2.9またはそれ以上のバージョンでは、基本は`ulimit -n`が100000を超えることを検出した場合、デフォルトとして100000に設定します。その理由は、一部のシステムが`ulimit -n`を100万に設定しており、大量のメモリを割り当てる必要があるため、起動に失敗するためです

    * **最大上限**

      * max_connectionを1Mを超えることはお勧めしません

    * **最小設定**    
      
      * このオプションを小さく設定すると、基本はエラーを投げ出し、`ulimit -n`の値を設定します。
      * 最小値は`(worker_num + task_worker_num) * 2 + 32`です

    ```shell
    serv->max_connection is too small.
    ```

    * **メモリ占用**

      * max_connectionパラメータを大きく設定してはいけません。マシンのメモリ状況に基づいて設定してください。Swoole
### task_ipc_mode

?> **タスクプロセスと`Worker`プロセス間の通信方法を設定します。**【デフォルト値：`1`】 
 
?> SwooleでのIPC通信について先に読んでください。/learn?id=什么是IPC
モード | 効果
---|---
1 | Unixソケット通信を使用【デフォルトモード】
2 | sysvmsgメッセージキュー通信を使用
3 | sysvmsgメッセージキュー通信を使用し、競合モードに設定

  * **ヒント**

    * **モード`1`**
      * モード`1`を使用すると、ターゲット指向の配送がサポートされており、[task](/server/methods?id=task)と[taskwait](/server/methods?id=taskwait)メソッドで`dst_worker_id`を指定し、ターゲットのタスクプロセスを指定できます。
      * `dst_worker_id`を`-1`に設定すると、各タスクプロセスの状態を判断し、空闲なプロセスにタスクを配送します。

    * **モード`2`、`3`**
      * メッセージキューモードでは、オペレーティングシステムが提供するメモリキューを使用してデータを保存します。`message_queue_key`メッセージキュー`Key`を指定していなければ、プライベートキューを使用し、サーバープログラムが終了した後、メッセージキューは削除されます。
      * メッセージキュー`Key`を指定した後、サーバープログラムが終了しても、キュー内のデータは削除されません。したがって、プロセスが再起動してもデータを引き続き取得できます。
      * `ipcrm -q`メッセージキュー`ID`を使用して、手動でメッセージキューのデータを削除できます。
      * モード`2`とモード`3`の違いは、モード`2`はターゲット指向の配送をサポートしており、`$serv->task($data, $task_worker_id)`でどのタスクプロセスに配送するかを指定できます。モード`3`は完全な競合モードであり、タスクプロセスがキューを競い合い、ターゲット指向の配送が使用できなくなります。[task/taskwait]では、ターゲットプロセスのIDを指定できなくなります。たとえ`$task_worker_id`を指定しても、モード`3`では無効です。

  * **注意**

    !> -モード`3`は[sendMessage](/server/methods?id=sendMessage)メソッドに影響を与え、sendMessageが送信するメッセージがランダムにどのタスクプロセスにも取得される可能性があります。  
    -メッセージキュー通信を使用すると、タスクプロセスの処理能力が配送速度よりも低い場合、Workerプロセスがブロックされる可能性があります。  
    -メッセージキュー通信を使用すると、タスクプロセスは协程をサポートできなくなります（[task_enable_coroutine](/server/setting?id=task_enable_coroutine)を有効にする必要があります）。  
### task_max_request

?> **タスクプロセスの最大タスク数を設定します。**【デフォルト値：`0`】

タスクプロセスの最大タスク数を設定します。この値を超えるタスクを処理したタスクプロセスは自動的に退出します。このパラメータはPHPプロセスのメモリオーバーフローを防ぐためにあります。プロセスが自動的に退出したくない場合は、`0`に設定してください。
### task_tmpdir

?> **タスクのデータ一時ディレクトリを設定します。**【デフォルト値：Linux `/tmp`ディレクトリ】

?> Serverの中で、データが8180字节を超える場合、一時ファイルを使用してデータを保存します。ここでの`task_tmpdir`は、一時ファイルの保存位置を設定するために使用されます。

  * **ヒント**

    * 下層はデフォルトで`/tmp`ディレクトリを使用してタスクデータを保存しますが、Linuxカーネルバージョンが低く、`/tmp`ディレクト리가メモリファイルシステムでない場合は、`/dev/shm/`に設定できます。
    * `task_tmpdir`ディレクトリが存在しない場合、下層は自動的に作成しようとします

  * **注意**

    !> -作成に失敗した場合、`Server->start`は失敗します
### task_enable_coroutine

?> **タスクの协程サポートを有効にします。**【デフォルト値：`false`】v4.2.12からサポートされています

?> 有効にすると、自動的に[onTask](/server/events?id=ontask)回调で协程と[协程コンテナ](/coroutine/scheduler)が作成され、PHPコードは直接协程APIを使用できます。

  * **例**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    //どのWorkerプロセスから来たか
    $task->worker_id;
    //タスクの番号
    $task->id;
    //タスクの種類、taskwait, task, taskCo, taskWaitMultiは異なるflagsを使用する可能性があります
    $task->flags;
    //タスクのデータ
    $task->data;
    //配信時間、v4.6.0で追加されました
    $task->dispatch_time;
    //协程API
    co::sleep(0.2);
    //タスクを完了し、終了してデータを返す
    $task->finish([123, 'hello']);
});
```

  * **注意**

    !> -`task_enable_coroutine`は[enable_coroutine](/server/setting?id=enable_coroutine)が`true`の場合にのみ使用できます  
    -`task_enable_coroutine`を有効にすると、タスクワークプロセスは协程をサポートします  
    -`task_enable_coroutine`を無効にすると、同期ブロックのみがサポートされます
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
1 | 巡回モード | 受信すると各`Worker`プロセスに巡回して割り当てられます
2 | 固定モード | 接続のファイル記述子に基づいて`Worker`を割り当てます。これにより、同じ接続からのデータが同じ`Worker`で処理されることを保証できます
3 | 競取モード | 主プロセスは`Worker`の空闲状態に基づいて選択的に配信し、空闲状態の`Worker`にのみ配信されます
4 | IP割り当て | クライアントの`IP`に基づいて取模ハッシュを行い、固定の`Worker`プロセスに割り当てられます。<br>同じ来源IPの接続データは常に同じ`Worker`プロセスに割り当てられます。アルゴリズムは `inet_addr_mod(ClientIP, worker_num)`
5 | UID割り当て | ユーザーコードで[Server->bind()](/server/methods?id=bind)を呼び出して接続に`1`つの`uid`を割り当てます。その後、下層は`UID`の値に基づいて異なる`Worker`プロセスに割り当てます。<br>アルゴリズムは `UID % worker_num`です。文字列を`UID`として使用したい場合は、`crc32(UID_STRING)`を使用できます
7 | ストリームモード | 空闲の`Worker`は接続を受け付け、[Reactor](/learn?id=reactor线程)からの新しいリクエストを受け付けます

  * **ヒント**

    * **使用推奨**
    
      * ステートレスな`Server`は`1`または`3`を使用できます。同期ブロックの`Server`は`3`を使用し、非同期非ブロックの`Server`は`1`を使用します
      * ステートフルな使用には`2`、`4`、`5`を使用します
      
    * **UDPプロトコル**

      * `dispatch_mode=2/4/5`は固定割り当てであり、下層はクライアントの`IP`に基づいて異なる`Worker`プロセスにハッシュされます
      * `dispatch_mode=1/3`はランダムに異なる`Worker`プロセスに割り当てられます
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
    * `dispatch_mode`は[SWOOLE_BASE](/learn?id=swoole_base)モードでは無効であり、`BASE`はタスクを配達することがなく、クライアントからのデータを受信した後にすぐに現在のスレッド/プロセスで[onReceive](/server/events?id=onreceive)を回调し、`Worker`プロセスにタスクを配達する必要があります。

  * **注意**

    !> -`dispatch_mode=1/3`では、下層は`onConnect/onClose`イベントをブロックします。その理由は、これら2つのモードでは`onConnect/onClose/onReceive`の順序を保証することができないからです；  
    -リクエスト応答式でないサーバープログラムでは、モード`1`または`3`を使用しないでください。例えば：HTTPサービスは応答式であり、`1`または`3`を使用できますが、TCP長接続状態がある場合は`1`または`3`を使用できません。
### dispatch_func

?> `dispatch`関数を設定します。Swooleは下層に6種類の[dispatch_mode](/server/setting?id=dispatch_mode)を組み込んでいますが、それでも要求を満たすことができない場合は、C++関数またはPHP関数を書いて`dispatch`ロジックを実現することができます。

  * **使用方法**

```php
$server->set(array(
  'dispatch_func' => 'my_dispatch_function',
));
```

  * **ヒント**

    * `dispatch_func`を設定すると、下層は自動的に`dispatch_mode`設定を無視します
    * `dispatch_func`に対応する関数が存在しない場合、下層は致命的なエラーを抛出します
    * 8Kを超えるパケットを`dispatch`する必要がある場合、`dispatch_func`は `0-8180`字节の内容のみを取得できます

  * **PHP関数を書く**

    ?> ZendVMはマルチスレッド環境をサポートしていないため、複数の[Reactor](/learn?id=reactor线程)线程が設定されていても、同時に一つの`dispatch_func`しか実行されません。したがって、下層はこのPHP関数を実行する際にロック操作を行い、ロックの争奪問題が発生する可能性があります。`dispatch_func`ではブロック操作を一切実行しないでください。そうでなければ、Reactor线程群が停止することがあります。

    ```php
    $server->set(array(
        'dispatch_func' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd`はクライアント接続のユニーク識別子であり、`Server::getClientInfo`を使用して接続情報を取得できます
    * `$type`はデータのタイプで、`0`はクライアントからのデータ送信を表し、`4`はクライアント接続が確立され、`3`はクライアント接続が閉じられました
    * `$data`はデータ内容であり、HTTP、EOF、Lengthなどのプロトコル処理パラメータが有効に启用されている場合、下層はパケットを組み立てますが、`dispatch_func`関数ではデータパケットの最初の8K内容のみを受け取ることができ、完全なパケット内容を得ることはできません。
    * **必ず** `0 - (server->worker_num - 1)`の数字を返す必要があります。これはデータパケットが配達されるターゲットのワークプロセスIDを表します
    * 小于`0`または`server->worker_num`以上は異常なターゲットIDであり、`dispatch`されたデータは破棄されます

  * **C++関数を書く**

    **他のPHP拡張で、swoole_add_functionを使用してSwooleエンジンに長さ関数を登録します。**

    ?> C++関数呼び出し時には下層はロックを行わないため、呼び出し側でスレッド安全性を保証する必要があります

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

    * `dispatch`関数は必ず配達されるターゲットのworkerプロセスidを返す必要があります
    * 返された`worker_id`は`server->worker_num`を超えることはできず、そうでなければ下層はセグメントエラーを抛出します
    * 负数`（return -1）`を返す場合、このデータパケットは破棄されます
    * `data`はイベントの種類と長さを取得できます
    * `conn`は接続情報であり、UDPデータパケットの場合は`conn`は`NULL`です

  * **注意**

    !> -`dispatch_func`は[SWOOLE_PROCESS](/learn?id=swoole_process)モードでのみ有効であり、UDP/TCP/UnixSocket](/server/methods?id=__construct)タイプのサーバーには有効です  
    -返された`worker_id`は`server->worker_num`を超えることはできず、そうでなければ下層はセグメントエラーを抛出します
### message_queue_key

?> **メッセージキューの`KEY`を設定します。**【デフォルト値：`ftok($php_script_file, 1)`】

?> タスクIPCモード=/server/setting?id=task_ipc_mode = 2/3の場合にのみ使用します。設定された`Key`はタスクキューの`KEY`としてのみ使用し、SwooleでのIPC通信についての参照は/learn?id=什么是IPCを参照してください。

?> タスクキューはサーバーが終わった後も破壊されず、プログラムを再起動した後も、タスクプロセスはキュー内のタスクを処理し続けます。プログラムの再起動後に古いタスクを実行したくない場合は、このメッセージキューを手動で削除することができます。

```shell
ipcs -q 
ipcrm -Q [msgkey]
```
### daemonize

?> **守护进程化**【デフォルト値：`false`】

?> `daemonize => true`と設定すると、プログラムはバックグラウンドで守护进程として実行されます。長期にわたって動作するサーバー端プログラムでは、このオプションを有効にする必要があります。  
守护进程を有効にしなければ、SSH端末から退出するとプログラムが終了します。

  * **ヒント**

    * 守护进程を有効にすると、標準入力と出力は `log_file`にリダイレクトされます
    * `log_file`を指定していなければ、`/dev/null`にリダイレクトされ、画面に表示されるすべての情報が無視されます
    * 守护进程を有効にすると、`CWD`（現在のディレクトリ）環境変数の値が変わり、相対パスでファイルの読み書きが失敗します。PHPプログラムでは絶対パスを使用する必要があります

    * **systemd**

      * `systemd`または`supervisord`を使用してSwooleサービスを管理する場合、`daemonize => true`を設定しないでください。主な理由は`systemd`のメカニズムが`init`と異なります。`init`プロセスのPIDは`1`であり、プログラムに`daemonize`を設定すると、ターミナルから離れ、最終的には`init`プロセスによって管理され、`init`と親子プロセス関係になります。
      * 一方で`systemd`は別のバックグラウンドプロセスを起動し、他のサービスプロセスを`
### backlog

?> **listen队列の長さを設定する**

?> 例として`backlog => 128`とすると、これは同時に`accept`を待つ最大のコネクション数を決定します。

  * **TCPのbacklogについて**

    ?> TCPには3回の手順があります。クライアントから`syn`が服务器に到達し、`syn+ack`がクライアントに到達し、`ack`が服务器に到達すると、服务器はクライアントの`ack`を受け取った後、このコネクションを`accept queue`というキューに入れます（注1）。  
    キューの大きさは`backlog`パラメータと`somaxconn`の設定の最小値によって決定されます。最終的な`accept queue`の大きさを確認するには`ss -lt`コマンドを使用できます。Swooleの親プロセスが`accept`を呼び出します（注2）。  
    `accept queue`からコネクションを取り出します。`accept queue`がいっぱくなった後、コネクションは成功する可能性があります（注4）、  
    失敗することもあります。失敗した場合、クライアントの行動はコネクションがリセットされることです（注3）  
    また、コネクションがタイムアウトすることもあります。そして、サーバーは失敗の記録を残します。これは `netstat -s|grep 'times the listen queue of a socket overflowed'`でログを確認できます。このような現象が発生した場合は、その値を増やすべきです。幸いなことに、SwooleのSWOOLE_PROCESSモードはPHP-FPMやApacheなどのソフトウェアとは異なり、コネクションのキューを解決するために`backlog`に依存しません。したがって、上記の現象に遭遇することはほとんどありません。

    * 注1: linux2.2以降の手順は`syn queue`と`accept queue`の2つのキューに分かれており、`syn queue`の長さは`tcp_max_syn_backlog`によって決定されます。
    * 注2: 高版本的内核では`accept4`が呼び出され、システム呼び出しの`set no block`を一度節約するためです。
    * 注3: クライアントは`syn+ack`パケットを受け取るとコネクションが成功したとみなしますが、実際にはサーバーはまだ半接続状態であり、`rst`パケットをクライアントに送信することもあります。クライアントの行動は`Connection reset by peer`になります。
    * 注4: 成功はTCPのリ重来実現され、関連する設定には`tcp_synack_retries`と`tcp_abort_on_overflow`があります。
### open_tcp_keepalive

?> TCPにはKeep-Aliveメカニズムがあり、死んだコネクションを検出することができます。アプリケーション層が死んだリンクの周期に敏感でない場合や、霍克思メカニズムを実現していない場合は、オペレーティングシステムが提供するkeepaliveメカニズムを使用して死んだリンクを蹴り出すことができます。
[Server->set()](/server/methods?id=set)の構成で`open_tcp_keepalive => true`を追加することで、TCP keepaliveを有効にします。
また、keepaliveの詳細を調整するための3つのオプションがあります。

  * **オプション**

     * **tcp_keepidle**

        秒为单位で、データ要求がない状態でn秒間、そのコネクションに対して探査を開始します。

     * **tcp_keepcount**

        探査の回数で、回数を超えた後はそのコネクションを`close`します。

     * **tcp_keepinterval**

        探査の間隔時間で、秒为单位です。

  * **例**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, //4秒データ転送がないと探査を開始
    'tcp_keepinterval' => 1, //1秒ごとに探査
    'tcp_keepcount' => 5, //探査回数、5回を超えても応答がなければコネクションをclose
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

?> **Heartbeatチェックを有効にする**【デフォルト値：`false`】

?> このオプションは、何秒ごとにループするかを表します。例えば `heartbeat_check_interval => 60` 表示60秒ごとにすべての接続を巡回し、その接続が120秒以内（`heartbeat_idle_time`が設定されていない場合、デフォルトは`interval`の2倍）にサーバーに何のデータも送信していない場合、その接続は強制的に切断されます。設定されていないとHeartbeatは有効になりません。この設定はデフォルトでオフです。

  * **ヒント**
    * `Server`はHeartbeatパケットを主动向いて送信することはありませんが、クライアントからのHeartbeatを受信するのを待っています。サーバー側の`heartbeat_check`は、コネクションが最後にデータを送信した時間を検出するだけで、制限を超えていると接続を切断します。
    * Heartbeatチェックによって切断された接続は、依然として[onClose](/server/events?id=onclose)イベントのコールバックがトリガーされます

  * **注意**

    !> `heartbeat_check`はTCP接続のみをサポートします
### heartbeat_idle_time

?> **コネクションが許容する最大の空闲時間**

?> `heartbeat_check_interval`と組み合わせて使用する必要があります

```php
array(
    'heartbeat_idle_time'      => 600, // 600秒間サーバーに何のデータも送信していない場合、その接続は強制的に切断されます
    'heartbeat_check_interval' => 60,  // 60秒ごとにループします
);
```

  * **ヒント**

    * `heartbeat_idle_time`を有効にした後、サーバーはクライアントにデータパケットを送信することはありません
    * `heartbeat_idle_time`のみを設定し、`heartbeat_check_interval`を設定していないと、下層ではHeartbeat検出线程が生成されません。PHPコードでは`heartbeat`メソッドを呼び出してタイムアウトした接続を手動で処理することができます
### open_eof_check

?> **EOF検出を有効にする**【デフォルト値：`false`】参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)

?> このオプションは、クライアントからのデータがデータパケットの最後に指定された文字列で終わる場合にのみ、Workerプロセスにデータを渡すことを検出します。そうでなければ、データパケットを継続して組み立て続け、バッファが溢れたりタイムアウトした後に中止します。エラーが発生した場合、下層では悪意のある接続と見なし、データを破棄し強制的に接続を閉じます。  
一般的なMemcache/SMTP/POPなどのプロトコルは`\r\n`で終わるので、この設定を使用することができます。有効にすると、Workerプロセスは一度に一つまたは複数の完全なデータパケットを受け取ることができます。

```php
array(
    'open_eof_check' => true,   //EOF検出を有効にする
    'package_eof'    => "\r\n", //EOFを設定する
)
```

  * **注意**

    !> この設定はSTREAM（ストリーム）タイプのSocketにのみ適用されます。例えば[TCP 、Unix Socket Stream](/server/methods?id=__construct)   
    EOF検出はデータの途中からEOF文字列を探すことはなく、したがってWorkerプロセスは同時に複数のデータパケットを受け取る可能性があります。そのため、アプリケーション層のコードでは`explode("\r\n", $data)`を使用してデータパケットを分割する必要があります
### open_eof_split

?> **EOFの自動分割を有効にする**

?> `open_eof_check`を設定した後、複数のデータパケットが一つのパッケージに組み合わさる可能性があります。`open_eof_split`パラメータはこの問題を解決することができます。参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)。

?> このパラメータを設定するには、データパケットの内容全体を走査し、EOFを探さなければなりません。そのため、多くのCPUリソースを消費します。例えば、各データパケットが2MBで、毎秒10000回のリクエストがある場合、これは20GBのCPU文字列マッチング指令を生じさせる可能性があります。

```php
array(
    'open_eof_split' => true,   //EOF_SPLIT検出を有効にする
    'package_eof'    => "\r\n", //EOFを設定する
)
```

  * **ヒント**

    * `open_eof_split`パラメータを有効にした後、下層はデータパケットの途中からEOFを探し、データパケットを分割します。[onReceive](/server/events?id=onreceive)では、EOF文字列で終わるデータパケットのみを受け取ります。
    * `open_eof_split`パラメータを有効にした後、`open_eof_check`が設定されていなくても、`open_eof_split`は有効になります。

    * **open_eof_checkとの違い**
    
        * `open_eof_check`は受信データの末尾がEOFかどうかのみをチェックするため、そのパフォーマンスは最も良く、ほとんどリソースを消費しません
        * `open_eof_check`は複数のデータパケットが一つのパッケージに組み合わさる問題を解決することができず、例えばEOFが2つ同時に存在するデータを送信した場合、下層は一度に全てを返す可能性があります
        * `open_eof_split`は左から右にデータを逐字节比較し、データ内のEOFを探して分割するため、パフォーマンスは劣りますが、一度に一つのデータパケットのみを返します
### package_eof

?> **EOF文字列を設定する**。参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)

?> `open_eof_check`または`open_eof_split`と組み合わせて使用する必要があります。

  * **注意**

    !> `package_eof`は最大で8字节的文字列しか受け付けられません
### open_length_check

?> **パケット長検査特性を有効にする**【デフォルト値：`false`】参照[TCPデータパケットの境界問題](/learn?id=tcpデータパケットの境界問題)

?> パケット長検査は、固定された包头+包体のフォーマットプロトコルを解析することを提供します。有効にすると、Workerプロセスの[onReceive](/server/events?id=onreceive)は毎回完全なデータパケットを受け取ることができます。  
長度検査プロトコルは、一度だけ長さを計算し、データ処理はポインタのオフセットのみを行い、非常に高いパフォーマンスを持ちます。**推奨される使用です**。

  * **ヒント**

    * **長度プロトコルの詳細を制御するための3つのオプションがあります。**

      ?> この設定はSTREAMタイプのSocketにのみ適用されます。例えば[TCP、Unix Socket Stream](/server/methods?id=__construct)

      * **package_length_type**

        ?>包头のどのフィールドをパケット長の値として使用するかを表します。下層は10種類の長さタイプをサポートしています。参考 [package_length_type](/server/setting?id=package_length_type)

      * **package_body_offset**

        ?>長さの計算はどの字节から始めるかです。一般的には2つのケースがあります：

        * `length`の値がパケット全体（包头+包体）を含んでいる場合、`package_body_offset`は`0`です
        *包头の長さが`N`字节で、`length`の値は包头を含まず、包体のみを含んでいる場合、`package_body_offset`は`N`に設定されます

      * **package_length_offset**

        ?> `length`の長さ値は包头のどの字节にあります。

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
        
    ?> 上記の通信プロトコルの設計では、包头の長さは4つの整数で構成され、16字节です。`length`の長さ値は第3の整数に位置しています。したがって`package_length_offset`は8に設定され、0-3字节が`type`、4-7字节が`uid`、8-11字节が`length`、12-15字节が`serid`です。

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```
### package_length_type

?> **長さ値のタイプ**、文字列パラメータを受けます。PHPの [pack](http://php.net/manual/zh/function.pack.php) 関数と一致しています。

現在、Swooleは10種類のタイプをサポートしています：
文字列パラメータ | 効果
---|---
c | 有符号、1字节
C | 無符号、1字节
s | 有符号、ホスト字节順、2字节
S | 無符号、ホスト字节順、2字节
n | 無符号、ネットワーク字节順、2字节
N | 無符号、ネットワーク字节順、4字节
l | 有符号、ホスト字节順、4字节（小文字L）
L | 無符号、ホスト字节順、4字节（大文字L）
v | 無符号、リトルエendiンス、2字节
V | 無符号、リトルエendiンス、4字节
### package_length_func

?> **長さ解析関数を設定する**

?> C++またはPHPの2種類のタイプの関数をサポートしています。長さ関数は整数を返す必要があります。
戻り値 | 効果
---|---
0を返す | 長度データが不足しているため、さらにデータを受け取る必要があります
-1を返す | データが誤りであるため、下層は自動的にコネクションを閉じます
パケットの長さ値（包头と包体の総長さを含む）| 下層は自動的にパケットを組み立てて回调関数に返します

  * **ヒント**

    * **使用方法**

    ?> 実現原理は、まず小さな部分のデータを読み取り、そのデータには長さ値が含まれています。その後、この長さ値を下層に返します。その後、下層が残りのデータの受信を完了し、パケットとして組み立ててdispatchを行います。

    * **PHP長さ解析関数**

    ?> ZendVMはマルチスレッド環境で実行されることをサポートしていないため、下層はPHP長さ関数に対して自動的にMutexロックを使用し、PHP関数の並行実行を避けています。1.9.3またはそれ以降のバージョンで使用できます。

    !> 長さ解析関数の中でブロックIO操作を実行しないでください。これにより、すべての[Reactor](/learn?id=reactor线程)线程がブロックされる可能性があります

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

    ?> 他のPHP拡張では、`swoole_add_function`を使用して長さ関数をSwooleエンジンに登録します。
    
    !> C++長さ関数を呼び出す際には下層がロックをかけませんので、呼び出し側でスレッド安全性を保証する必要があります
    
    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"
    
    using namespace std;
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);
    
    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW
### open_http_protocol

?> **HTTPプロトコルの処理を有効にする。**【デフォルト値：`false`】

?> HTTPプロトコルの処理を有効にすると、Swoole\Http\Serverは自動的にこのオプションを有効にします。`false`に設定するとHTTPプロトコルの処理をオフにします。
### open_mqtt_protocol

?> **MQTTプロトコルの処理を有効にする。**【デフォルト値：`false`】

?> 有効にするとMQTTパケットの头部を解析し、workerプロセスはonReceive回调で毎回完全なMQTTデータパケットを返します。

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```
### open_redis_protocol

?> **Redisプロトコルの処理を有効にする。**【デフォルト値：`false`】

?> 有効にするとRedisプロトコルを解析し、workerプロセスはonReceive回调で毎回完全なRedisデータパケットを返します。Redis\Serverを直接使用することをお勧めします。

```php
$server->set(array(
  'open_redis_protocol' => true
));
```
### open_websocket_protocol

?> **WebSocketプロトコルの処理を有効にする。**【デフォルト値：`false`】

?> WebSocketプロトコルの処理を有効にすると、Swoole\WebSocket\Serverは自動的にこのオプションを有効にします。`false`に設定するとwebsocketプロトコルの処理をオフにします。  
WebSocketプロトコルの処理を`true`に設定すると、自動的にopen_http_protocolも`true`に設定されます。
### open_websocket_close_frame

?> **WebSocketプロトコルでのクローズフレームを有効にする。**【デフォルト値：`false`】

?> （`opcode`が`0x08`のフレーム）onMessage回调で受信

?> 有効にすると、WebSocketServerのonMessage回调でクライアントまたはサーバーから送信されたクローズフレームを受信でき、開発者は自分でそれを処理できます。

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

?> **TCPノッチを有効にする。**【デフォルト値：`false`】

?> 有効にするとTCP接続がデータを送信する際にNagleのまとめアルゴリズムをオフにし、すぐに対端TCP接続に送信されます。例えば、コマンドライン端末でコマンドを入力するとすぐにサーバーに送信する必要があり、応答速度を上げるために使用できます。NagleアルゴリズムについてはGoogleで調べください。
### open_cpu_affinity 

?> **CPUアフィニティ設定を有効にする。** 【デフォルト `false`】

?> 多核のハードウェアプラットフォームでは、この特性を有効にするとSwooleのreactor线程/workerプロセスを固定されたCPUコアにア绑定します。プロセス/スレッドが複数のコア間で切り替わるのを避け、CPUキャッシュのhit率を高めることができます。

  * **ヒント**

    * **プロセスのCPUアフィニティ設定を確認するにはtasksetコマンドを使用します：**

    ```bash
    taskset -p プロセスID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > maskはマスク番号で、bitごとに対応するCPUコアを表します。某一位が0则表示そのコアにア绑定され、プロセスはそのCPUにスケジュールされます。0であればプロセスはそのCPUにスケジュールされません。例ではpidが24666のプロセスのmask = fはCPUにア绑定されていないことを示し、オペレーティングシステムはそのプロセスを任意のCPUコアにスケジュールします。pidが24901のプロセスのmask = 8は、8を二進数に変換すると `1000`であり、そのプロセスが第4のCPUコアにア绑定されていることを示します。
### cpu_affinity_ignore

?> **I/O密集型プログラムでは、すべてのネットワーク割り込みがCPU0で処理されます。ネットワークI/Oが重い場合、CPU0の負荷が高すぎるとネットワーク割り込みがタイムリーに処理できなくなり、ネットワークの受け取りと送信の能力が低下します。**

?> このオプションをしない場合、swooleはすべてのCPUコアを使用し、下層ではreactor_idまたはworker_idとCPUコア数を模运算してCPUバンディングを設定します。内核とNICがマルチキュー特性を持っている場合、ネットワーク割り込みは複数のコアに分布し、ネットワーク割り込みの圧力を軽減できます。

```php
array('cpu_affinity_ignore' => array(0, 1)) // 引数として配列を受け取り、array(0, 1)はCPU0,CPU1を使用せず、ネットワーク割り込みを処理するために特別に空けています。
```

  * **ヒント**

    * **ネットワーク割り込みの確認方法**

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

`eth0/eth1`はネットワーク割り込みの回数であり、もし`CPU0 - CPU3`が均等に分布している場合、NICにはマルチキュー特性があることを証明しています。もしすべてあるコアに集中している場合、ネットワーク割り込みはすべてそのCPUで処理されており、そのCPUが100%を超えると、システムはネットワークリクエストを処理することができません。この場合、`cpu_affinity_ignore`設定を使用してそのCPUを空けて、ネットワーク割り込みを処理するために使用する必要があります。

図の状況では、`cpu_affinity_ignore => array(0)`を設定すべきです。

?> `top`コマンドを使用して `->` を入力し、各コアの使用率を確認できます。

  * **注意**

    !> このオプションは`open_cpu_affinity`と同時に設定する必要があります才能に生效します
### tcp_defer_accept

?> **TCPのdefer_accept特性を有効にする。**【デフォルト値：`false`】

?> 数値として設定でき、TCP接続にデータが送信された時にのみacceptをトリガーします。

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

  * **ヒント**

    * **TCPのdefer_accept特性を有効にした後、acceptとonConnectに対応する時間が変わります。5秒に設定した場合：**

      * クライアントがサーバーに接続した後、すぐにacceptはトリガーされません
      * クライアントが5秒以内にデータを送信した場合、同時に順にaccept/onConnect/onReceiveがトリガーされます
      * クライアントが5秒以内にデータを送信しなかった場合、accept/onConnectがトリガーされます
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

    !> -HTTPSアプリケーションでは、ブラウザは証明書を信頼していなければウェブページを閲覧できません；  
    -wssアプリケーションでは、WebSocket接続を開始するページはHTTPSを使用しなければなりません；  
    -ブラウザがSSL証明書を信頼していないとwssを使用できません；  
    -ファイルはPEM形式でなければならず、DER形式はサポートされていません。可使用opensslツールでの変換が可能です。

    !> Swooleを使用するには、compile時に[--enable-openssl](/environment?id=compile_options)オプションを加える必要があります

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```
### ssl_method

!> 此参数已在 [v4.5.4](/version/bc?id=_454) 版本移除，请使用`ssl_protocols`

?> **OpenSSLトンネル暗号化のアルゴリズムを設定する。**【デフォルト値：`SWOOLE_SSLv23_METHOD`】、サポートされるタイプは[SSL 加密方法](/consts?id=ssl-加密方法)を参照してください

?> ServerとClientが使用するアルゴリズムは一致していなければならず、そうでなければSSL/TLSハンドシェイクに失敗し、接続が切断されます

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```
### ssl_protocols

?> **OpenSSLトンネル暗号化のプロトコルを設定する。**【デフォルト値：`0`、すべてのプロトコルをサポート】、サポートされるタイプは[SSL 协议](/consts?id=ssl-协议)を参照してください

!> Swooleバージョン >= `v4.5.4` 可用

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```
### ssl_sni_certs

?> **SNI (Server Name Identification) 証明書を設定する**

!> Swooleバージョン >= `v4.6.0` 可用

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

?> **openssl暗号化アルゴリズムを設定する。**【デフォルト値：`EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

  * **ヒント**

    * `ssl_ciphers`を空文字列に設定すると、opensslが暗号化アルゴリズムを選択します
### ssl_verify_peer

?> **SSLでのサービス側の証明書の検証を有効にする。**【デフォルト値：`false`】

?>デフォルトではオフであり、クライアントの証明書を検証しません。有効にする場合は、同時に`ssl_client_cert_file`オプションを設定する必要があります。
### ssl_allow_self_signed

?> **自己署名証明書を許可する。**【デフォルト値：`false`】
### ssl_client_cert_file

?> **クライアント証明書で検証される根证书。**

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

!> TCPサービスで検証に失敗した場合、下層では接続を積極的に閉じるでしょう。
### ssl_compress

?> **SSL/TLS圧縮を有効にするかどうかを設定する。** Co\Clientを使用する際には、別名`ssl_disable_compression`があります
### ssl_verify_depth

?> **証明書のチェーンが深すぎて、このオプションの設定値を超えた場合、検証を終了する。**
### ssl_prefer_server_ciphers

?> **サーバー側の保護を有効にし、BEAST攻撃を防ぐ。**
### ssl_dhparam

?> **DHE暗号化套件のDiffie-Hellmanパラメータを指定する。**
### ssl_ecdh_curve

?> **ECDHキープロトコルで使用されるcurveを指定する。**

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

?> **Worker/TaskWorkerサブプロセスの所属ユーザーを設定する。**【デフォルト値：実行スクリプトのユーザー】

?> サーバーが1024以下のポートを监听する必要がある場合、root権限が必要です。しかし、プログラムがrootユーザーで実行されていると、コードに漏洞があれば、攻撃者はrootとしてリモートコマンドを実行することができ、リスクが大きいです。user項目を設定した後、メインプロセスはroot権限で実行され、サブプロセスは通常のユーザー権限で実行されます。

```php
$server->set(array(
  'user' => 'Apache'

### chroot

?> **ワーカープロセスのファイルシステムの根ディレクトリをリダイレクトします。**

?> この設定により、プロセスは実際のオペレーティングシステムのファイルシステムから隔離された読み書きを行うことができます。セキュリティを高めます。

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```
### pid_file

?> **PIDファイルの場所を設定します。**

?> Serverが起動すると自動的にmasterプロセスのPIDをファイルに書き込み、Serverが停止するとPIDファイルを自動的に削除します。

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

  * **注意**

    !> Serverが非正常に終了した場合、PIDファイルは削除されません。プロセスが本当に存在するかどうかを確認するために[Swoole\Process::kill($pid, 0)](/process/process?id=kill)を使用する必要があります
### buffer_input_size / input_buffer_size :id=buffer_input_size

?> **受信入力バッファのメモリサイズを構成します。**【デフォルト値：`2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```
### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **送信出力バッファのメモリサイズを構成します。**【デフォルト値：`2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, //数字でなければなりません
]);
```

  * **ヒント**

    !> Swooleバージョン >= `v4.6.7` の場合、デフォルト値は符号付きINTの最大値`UINT_MAX`です

    *単位はバイトで、デフォルトは`2M`です。例えば`32 * 1024 * 1024`と設定すると、一度の`Server->send`で最大`32M`バイトのデータを送信することができます
    * `Server->send`、`Http\Server->end/write`、`WebSocket\Server->push`などのデータ送信指令を呼び出すとき、一度に送信できるデータは`buffer_output_size`の構成を超えてはなりません。

    !> このパラメータは[SWOOLE_PROCESS](/learn?id=swoole_process)モードでのみ機能します。なぜなら、PROCESSモードではWorkerプロセスのデータはMasterプロセスに送信され、それからクライアントに送信されるからです。そのため、各WorkerプロセスはMasterプロセスとバッファを共有します。[参照](/learn?id=reactor线程)
### socket_buffer_size

?> **クライアント接続のバッファ長さを設定します。**【デフォルト値：`2M`】

?> `buffer_output_size`とは異なり、`buffer_output_size`はworkerプロセスの一度の`send`のサイズ制限ですが、`socket_buffer_size`はWorkerとMasterプロセス間の通信バッファの総サイズを設定するためのものです。SWOOLE_PROCESSモードを参照してください。

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, //数字でなければならず、単位はバイトです。例えば128 * 1024 *1024は、各TCPクライアント接続が最大で128Mバイトのデータを待つことを許可します
]);
```
- **データ送信バッファ**

    - Masterプロセスがクライアントに大量のデータを送信する場合、すぐに送信されるわけではありません。この時、送信されるデータはサーバー側のメモリバッファに格納されます。このパラメータはメモリバッファのサイズを調整することができます。
    
    - 送信データが多すぎてバッファがいっぱいに満たされた場合、Serverは以下のエラー情報を報告します：
    
    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```
    
    ?>バッファが溢れて`send`に失敗することは、現在のクライアントにのみ影響を与え、他のクライアントには影響しません。
    サーバーに多くの`TCP`接続がある場合、最も悪い状況では`serv->max_connection * socket_buffer_size`バイトのメモリを占有することになります。
    
    -特に外部通信のサーバープログラムでは、ネットワーク通信が遅いため、連続してデータを送信するとすぐにバッファがいっぱになります。送信されるデータはすべてServerのメモリに蓄積されます。したがって、このようなアプリケーションは設計時にネットワークの伝送能力を考慮し、まずメッセージを磁盘に保存し、クライアントがサーバーを受け取ったことを通知してから、新しいデータを送信する必要があります。
    
    -例えばビデオライブ配信サービスでは、`A`ユーザーの帯域幅は`100M`で、`1`秒間に`10M`のデータを送信することは完全に可能です。しかし、`B`ユーザーの帯域幅は`1M`だけで、`1`秒間に`10M`のデータを送信すると、`B`ユーザーは`100`秒かかるかもしれません。この時、データはすべてサーバーのメモリに蓄積されます。
    
    -データの内容の種類に応じて、異なる処理を行うことができます。丢弃可能な内容、例えばビデオライブ配信などのビジネスでは、ネットワークが悪い状況でいくつかのデータフレームを丢弃しても完全に受け入れられます。丢失できない内容、例えばWeChatのメッセージなどは、まずサーバーの磁盘に保存し、`100`件のメッセージを一組として保存します。ユーザーがこの一組のメッセージを受け取った後、次に磁盘から次の一組のメッセージを取り出してクライアントに送信します。
### enable_unsafe_event

?> **onConnect/onCloseイベントを有効にします。**【デフォルト値：`false`】

?> Swooleは[dispatch_mode](/server/setting?id=dispatch_mode)=1または3に設定した後、システムがonConnect/onReceive/onCloseの順序を保証できないため、デフォルトでonConnect/onCloseイベントをオフにしています。  
アプリケーションがonConnect/onCloseイベントが必要であり、順序問題によるセキュリティリスクを受け入れることができる場合は、enable_unsafe_eventをtrueに設定してonConnect/onCloseイベントを有効にすることができます。
### discard_timeout_request

?> **接続が閉じられたリンクのデータリクエストを丢弃します。**【デフォルト値：`true`】

?> Swooleは[dispatch_mode](/server/setting?id=dispatch_mode)=`1`または3に設定した後、システムがonConnect/onReceive/onCloseの順序を保証できないため、接続が閉じられた後でなければならなかったリクエストデータがWorkerプロセスに到達することがあります。

  * **ヒント**

    * discard_timeout_request設定のデフォルト値はtrueで、workerプロセスが閉じられた接続からのデータリクエストを受け取った場合、自動的に丢弃されます。
    * discard_timeout_requestをfalseに設定すると、接続が閉じられているかどうかにかかわらずworkerプロセスはデータリクエストを処理します。
### enable_reuse_port

?> **ポートの再利用を設定します。**【デフォルト値：`false`】

?> ポートの再利用を有効にすると、同じポートでlistenするServerプログラムを何度も起動することができます

  * **ヒント**

    * enable_reuse_port = true ポートの再利用を有効にします
    * enable_reuse_port = false ポートの再利用を無効にします

!> Linux-3.9.0以上のバージョンでのみ利用可能 Swoole4.5以上のバージョンでのみ利用可能
### enable_delay_receive

?> **acceptクライアント接続後、EventLoopに自動で参加しないように設定します。**【デフォルト値：`false`】

?> このオプションをtrueに設定すると、acceptクライアント接続後、EventLoopに自動で参加せず、onConnect回调のみがトリガーされます。workerプロセスは[$server->confirm($fd)](/server/methods?id=confirm)を呼び出して接続を確認し、その時点でfdをEventLoopに追加してデータの收发を始めたり、$server->close($fd)を呼び出してこの接続を閉じたりすることができます。

```php
//enable_delay_receiveオプションを有効にします
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        //接続を確認し、データの受信を始めます
        $server->confirm($fd);
    });
});
```
### reload_async

?> **非同期再起動のスイッチを設定します。**【デフォルト値：`true`】

?> 非同期再起動のスイッチをtrueに設定すると、非同期安全再起動機能が有効になり、workerプロセスは非同期イベントが完了するのを待ってから退出します。詳細は[サービスを正しく再起動する方法](/question/use?id=swoole如何正确的重启 service)をご覧ください。

?> reload_asyncをtrueにする主な理由は、サービスのリロード時に协程や非同期タスクが正常に終了できるようにすることです。 

```php
$server->set([
  'reload_async' => true
]);
```

  * **协程モード**

    * 4.xバージョンで[enable_coroutine](/server/setting?id=enable_coroutine)を有効にすると、基層は协程の数を検出し、現在协程がない場合にのみプロセスを退出します。enable_coroutineをtrueにすると、reload_asyncがfalseであってもforce的にreload_asyncをtrueにします。
### max_wait_time

?> **Workerプロセスがサービス停止通知を受け取った後の最大待ち時間を設定します**【デフォルト値：`3`】

?> workerがブロックしたりカッついたりして正常にreloadできないことがよくあります。これは一部の生産シナリオ、例えばコードのホットアップグレードやreloadが必要な場合などには适用できません。そのため、Swooleにはプロセス再起動のタイムアウト時間のオプションが追加されました。詳細は[サービスを正しく再起動する方法](/question/use?id=swoole如何正确的 restart service)をご覧ください。

  * **ヒント**

    * **プロセスが再起動や閉鎖の信号を受け取ったり、max_requestに達したりした場合、管理プロセスはそのworkerプロセスを再起動します。以下のステップに従います：**

      * 基層は(`max_wait_time`)秒のタイマーを追加し、タイマーがトリガーされた後、プロセスが存在するかどうかを確認します。存在する場合は、強制的にkillし、再びプロセスを立ち上げます。
      * onWorkerStop回调内で後処理を行い、max_wait_time秒以内に後処理を完了する必要があります。
      * ターゲットプロセスに順にSIGTERM信号を送信し、プロセスをkillします。

  * **注意**

    !> v4.4.x以前はデフォルトで30秒です
### tcp_fastopen

?> **TCPファストオープンの特性を有効にします。**【デフォルト値：`false`】

?> この特性は、TCP短接続の応答速度を向上させることができます。クライアントがハンドシェイクの第3段階でSYNパケットを送信する際にデータを含みます。

```php
$server->set([
  'tcp_fastopen' => true
]);
```

  * **ヒント**

    * このパラメータはlistenポートに設定できます。理解したい方は[google論文](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf)を参照してください。
### request_slowlog_file

?> **リクエストの遅延ロギングを有効にします。** v4.4.8バージョンから[取り消されました](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366)

!> この遅延ロギングの方法は同期ブロッキングのプロセスでのみ機能し、协程環境では使用できません。Swoole4はデフォルトで协程を有効にするため、enable_coroutineをオフにする必要があります。そのため、これらは使用しないでください。[Swoole Tracker](https://business.swoole.com/tracker/index)のブロッキング検出ツールを使用してください。

?> 有効にするとManagerプロセスは時計信号を設定し、定期的にすべてのTaskとWorkerプロセスを検出します。プロセスがブロッキングしてリクエストが規定の時間を超える場合、自動的にプロセスのPHP関数呼び出しスタックをプリントします。

?> 基層はptraceシステム呼び出しに基づいており、一部のシステムではptraceがオフになっているため、遅延リクエストをトレースすることができません。kernel.yama.ptrace_scopeカーネルパラメータが0でないことを確認してください。

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

  * **タイムアウト時間**

```php
$server->set([
    'request_slowlog_timeout' => 2, //リクエストのタイムアウト時間を2秒に設定します
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

!> 可写な権限を持つファイルでなければならず、そうでなければファイル作成に失敗し、基層は致命的なエラーを投げます
    ### enable_coroutine

?> **非同期スタイルのサーバー协程サポートを有効にしますか**

?> `enable_coroutine`をオフにすると、イベント回调関数では自動的に协程が生成されなくなります。协程が必要ない場合は、この設定をオフにするとパフォーマンスが向上します。参考：[Swoole协程とは](/coroutine)。

  * **設定方法**
    
    * `php.ini`で `swoole.enable_coroutine = 'Off'` を設定します（[ini設定文档](/other/config.md)を参照）。
    * `$server->set(['enable_coroutine' => false]);`の優先度はiniよりも高いです。

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
      * tick/after 定时器

!> `enable_coroutine`を有効にすると、上記の回调関数で自動的に协程が生成されます

* `enable_coroutine`をtrueに設定すると、基層は自動的に[onRequest](/http_server?id=on)回调で协程を生成し、開発者はgo関数[协程を作成](/coroutine/coroutine?id=create)を使用する必要はありません。
* `enable_coroutine`をfalseに設定すると、基層は自動的に协程を生成しません。開発者が协程を使用したい場合は、go関数で自ら协程を作成する必要があります。协程特性が必要ない場合は、Swoole1.xとの処理方法は100%同じです。
* 注意してください。この設定はSwooleがリクエストを协程で処理することを意味するものであり、イベントにブロッキング関数が含まれている場合は、事前に[一键协程化](/runtime)を有効にし、sleep、mysqlndなどのブロッキング関数や拡張を协程化する必要があります。

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    //内置协程をオフにします
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

?> **データを送信する際にバッファメモリが不足した場合、現在の协程内で[yield](/coroutine?id=协程调度)し、データ送信が完了するのを待ちます。バッファが空になると、自動的に[resume](/coroutine?id=协程调度)当前协程を再開し、データの送信を続けます。**【デフォルト値：dispatch_modeが2/4の場合に利用可能で、デフォルトで有効】

* Server/Client->sendがfalseを返し、エラーコードがSW_ERROR_OUTPUT_BUFFER_OVERFLOWの場合、PHP層にはfalseを返しませんでした。代わりに[yield](/coroutine?id=协程调度)して現在の协程を挂起します。
* Server/Clientはバッファが空になるイベントを監視し、そのイベントが発生すると、バッファ内のデータが送信され終えているため、対応する协程を[resume](/coroutine?id=协程调度)します。
* 协程が再開すると、再びServer/Client->sendを呼び出してバッファにデータを書き込むことができます。この時、バッファは空いているため、送信は必ず成功します。

改善前


### send_timeout

送信タイムアウトを設定し、`send_yield`と組み合わせて使用します。規定の時間内にデータがバッファ区に送信されなければ、下層は`false`を返し、エラーコードを`ETIMEDOUT`に設定します。エラーコードは[getLastError()](/server/methods?id=getlasterror)メソッドで取得できます。

> 型は浮動小数点で、単位は秒、最小粒度はミリ秒です。

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1.5秒
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false && $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "送信タイムアウト\n";
    }
}
```
### hook_flags

?> **「 一键协程化」Hookの関数範囲を設定します。**【デフォルト値：Hookしない】

!> Swooleバージョンは `v4.5+` 或いは [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 可用、詳細は[一键协程化](/runtime)を参照してください。

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
### buffer_high_watermark

?> **バッファの高水位線を設定します。**単位は字节です。

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```
### buffer_low_watermark

?> **バッファの低水位線を設定します。**単位は字节です。

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```
### tcp_user_timeout

?> TCP_USER_TIMEOUTオプションはTCP層のsocketオプションで、データ包が送信された後にACK確認を受け取らない最大の時間です。単位はミリ秒です。具体的な内容はmanページを参照してください。

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10秒
]);
```

!> Swooleバージョン >= `v4.5.3-alpha` 可用
### stats_file

?> **[stats()](/server/methods?id=stats)の内容を書き込むファイルのpathを設定します。設定すると、自動的に[onWorkerStart](/server/events?id=onworkerstart)時にタイマーを設定し、定期的に[stats()](/server/methods?id=stats)の内容を指定されたファイルに書き込みます**

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

?> **単一スレッドに設定します。** 启用すると Reactor スレッドは Masterプロセス内の Master スレッドと合併し、Master スレッドが論理を処理します。PHP ZTS下で、`SWOOLE_PROCESS`モードを使用する場合、この値を`true`に設定する必要があります。

```php
$server->set([
    'single_thread' => true,
]);
```

!> Swooleバージョン >= `v4.2.13` 可用
### max_queued_bytes

?> **受信バッファの最大キュー長さを設定します。** 超出すると受信を停止します。

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Swooleバージョン >= `v4.5.0` 可用
### admin_server

?> **admin_serverサービスを設定し、Swoole Dashboard ([http://dashboard.swoole.com/])でサービスの情報を確認するために使用します。**

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Swooleバージョン >= `v4.8.0` 可用
### bootstrap

?> **マルチスレッドモードでのプログラムの入口ファイルを指定します。デフォルトは現在実行されているスクリプトのファイル名です。**

!> Swooleバージョン >= `v6.0` 、 `PHP`は`ZTS`モードで、Swooleを编译する際に`--enable-swoole-thread`を有効にしました。

```php
$server->set([
    'bootstrap' => __FILE__,
]);
```
### init_arguments

?> **マルチスレッドでのデータ共有データを設定します。この設定には回调関数が必要で、サーバーが起動する時に自動的に実行されます**

!> Swooleには多くのスレッド安全なコンテナが内蔵されています。[並行Map](/thread/map)、[並行List](/thread/arraylist)、[並行キュー](/thread/queue)などがあり、関数内で安全でない変数を返却してはいけません。

!> Swooleバージョン >= `v6.0` 、 `PHP`は`ZTS`モードで、Swooleを编译する際に`--enable-swoole-thread`を有効にしました。

```php
$server->set([
    'init_arguments' => function() { return new Swoole\Thread\Map(); },
]);

$server->on('request', function($request, $response) {
    $map = Swoole\Thread::getArguments();
});
```
