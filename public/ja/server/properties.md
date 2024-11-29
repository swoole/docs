# 属性
### $setting

[Server->set()](/server/methods?id=set)関数で設定されたパラメータは`Server->$setting`プロパティに保存されます。回调関数内で実行パラメータの値にアクセスできます。このプロパティは`array`型の配列です。

```php
Swoole\Server->setting
```

  * **例**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```
### $connections

`TCP`接続のイテレーターであり、サーバー上の現在のすべての接続を`foreach`で遍历することができます。このプロパティの機能は[Server->getClientList](/server/methods?id=getclientlist)と同じですが、より使いやすいです。

遍历される要素は、単一接続の`fd`です。

```php
Swoole\Server->connections
```

!> `$connections`プロパティはイテレーターオブジェクトであり、PHP配列ではないため、`var_dump`や配列下标でアクセスすることはできず、`foreach`での遍历操作のみが可能です

  * **基本モード**

    * [SWOOLE_BASE](/learn?id=swoole_base)モードでは、プロセス間での`TCP`接続操作をサポートしていません。したがって、`BASE`モードでは、当前プロセス内でのみ`$connections`イテレーターを使用できます

  * **例**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "現在のサーバーには " . count($server->connections) . " 个の接続があります\n";
```
### $host

現在サーバーが监听しているホストアドレスの`host`を返します。このプロパティは`string`型の文字列です。

```php
Swoole\Server->host
```
### $port

現在サーバーが监听している端口号の`port`を返します。このプロパティは`int`型の整数です。

```php
Swoole\Server->port
```
### $type

現在のServerのタイプ`type`を返します。このプロパティは`int`型の整数です。

```php
Swoole\Server->type
```!> このプロパティは以下の値のいずれか返回します- `SWOOLE_SOCK_TCP` tcp ipv4 socket- `SWOOLE_SOCK_TCP6` tcp ipv6 socket- `SWOOLE_SOCK_UDP` udp ipv4 socket- `SWOOLE_SOCK_UDP6` udp ipv6 socket- `SWOOLE_SOCK_UNIX_DGRAM` unix socket dgram
- `SWOOLE_SOCK_UNIX_STREAM` unix socket stream 
### $ssl

現在サーバーがsslを開始しているかどうかを返します。このプロパティは`bool`型です。

```php
Swoole\Server->ssl
```
### $mode

現在のサーバーのプロセスモード`mode`を返します。このプロパティは`int`型の整数です。

```php
Swoole\Server->mode
```
!> このプロパティは以下の値のいずれか返回します- `SWOOLE_BASE` 単プロセスモード
- `SWOOLE_PROCESS` 多プロセスモード
### $ports

监听ポートの配列であり、サーバーが複数のポートで监听している場合は、`Server::$ports`を遍历することですべての`Swoole\Server\Port`オブジェクトを取得できます。

その中で`swoole_server::$ports[0]`はコンストラクタで設定されたマスターレベルのポートです。

  * **例**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```
### $master_pid

現在サーバーのマスタープロセスの`PID`を返します。

```php
Swoole\Server->master_pid
```

!> `onStart/onWorkerStart`の後にのみ取得できます

  * **例**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->master_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```
### $manager_pid

現在サーバーのマネージャープロセスの`PID`を返します。このプロパティは`int`型の整数です。

```php
Swoole\Server->manager_pid
```

!> `onStart/onWorkerStart`の後にのみ取得できます

  * **例**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->manager_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```
    ### $worker_id

現在の`Worker`プロセスの番号を取得します。これには[Taskプロセス](/learn?id=taskworkerプロセス)も含まれます。このプロパティは`int`型の整数です。

```php
Swoole\Server->worker_id
```
  * **例**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num' => 8,
    'task_worker_num' => 4,
]);
$server->on('WorkerStart', function ($server, int $workerId) {
    if ($server->taskworker) {
        echo "task workerId：{$workerId}\n";
        echo "task worker_id：{$server->worker_id}\n";
    } else {
        echo "workerId：{$workerId}\n";
        echo "worker_id：{$server->worker_id}\n";
    }
});
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
});
$server->on('Task', function ($serv, $task_id, $reactor_id, $data) {
});
$server->start();
```

  * **ヒント**

    * このプロパティは[onWorkerStart](/server/events?id=onworkerstart)時の`$workerId`と同じです。
    * `Worker`プロセスの番号の範囲は`[0, $server->setting['worker_num'] - 1]`です。
    * [Taskプロセス](/learn?id=taskworkerプロセス)の番号の範囲は `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`です。

!> ワーカープロセスが再起動した後も`worker_id`の値は変わらない
### $taskworker

現在のプロセスが`Task`プロセスかどうかを返します。このプロパティは`bool`型です。

```php
Swoole\Server->taskworker
```

  * **返回値**

    * `true`は現在のプロセスが`Task`ワーカープロセスであることを示します。
    * `false`は現在のプロセスが`Worker`プロセスであることを示します。

### $worker_pid

現在の`Worker`プロセスのオペレーティングシステムのプロセスIDを取得します。これは`posix_getpid()`の返回値と同じです。このプロパティは`int`型の整数です。

```php
Swoole\Server->worker_pid
```
