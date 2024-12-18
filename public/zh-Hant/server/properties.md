# 属性


### $setting

[Server->set()](/server/methods?id=set)函數所設定的參數會保存到`Server->$setting`屬性上。在回調函數中可以訪問運行參數的值。該屬性是一個`array`類型的數組。

```php
Swoole\Server->setting
```

  * **示範**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```


### $connections

`TCP`連接迭代器，可以使用`foreach`遍歷伺服器當前所有的連接，此屬性的功能與[Server->getClientList](/server/methods?id=getclientlist)是一致的，但是更加友好。

遍歷的元素為單個連接的`fd`。

```php
Swoole\Server->connections
```

!> `$connections`屬性是一個迭代器物件，不是PHP數組，所以不能用`var_dump`或者數組下標來訪問，只能通過`foreach`進行遍歷操作

  * **Base 模式**

    * [SWOOLE_BASE](/learn?id=swoole_base) 模式下不支持跨進程操作`TCP`連接，因此在`BASE`模式中，只能在當前進程內使用`$connections`迭代器

  * **示範**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "當前伺服器共有 " . count($server->connections) . " 個連接\n";
```


### $host

返回當前伺服器監聽的主機地址的`host`，該屬性是一個`string`類型的字符串。

```php
Swoole\Server->host
```


### $port

返回當前伺服器監聽的端口的`port`，該屬性是一個`int`類型的整數。

```php
Swoole\Server->port
```


### $type

返回當前Server 的類型`type`，該屬性是一個`int`類型的整數。

```php
Swoole\Server->type
```

!> 該屬性返回會返回下列的值的其中一個

- `SWOOLE_SOCK_TCP` tcp ipv4 socket

- `SWOOLE_SOCK_TCP6` tcp ipv6 socket

- `SWOOLE_SOCK_UDP` udp ipv4 socket

- `SWOOLE_SOCK_UDP6` udp ipv6 socket

- `SWOOLE_SOCK_UNIX_DGRAM` unix socket dgram
- `SWOOLE_SOCK_UNIX_STREAM` unix socket stream 


### $ssl

返回當前伺服器是否啟動`ssl`，該屬性是一個`bool`類型。

```php
Swoole\Server->ssl
```


### $mode

返回當前伺服器的進程模式`mode`，該屬性是一個`int`類型的整數。

```php
Swoole\Server->mode
```


!> 該屬性返回會返回下列的值的其中一個

- `SWOOLE_BASE` 单進程模式
- `SWOOLE_PROCESS` 多進程模式


### $ports

監聽端口數組，如果伺服器監聽了多個端口可以遍歷`Server::$ports`得到所有`Swoole\Server\Port`物件。

其中`swoole_server::$ports[0]`為構造方法所設置的主伺服器端口。

  * **示範**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```


### $master_pid

返回當前伺服器主進程的`PID`。

```php
Swoole\Server->master_pid
```

!> 只能夠在`onStart/onWorkerStart`之後獲取到

  * **示範**

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

返回當前伺服器管理進程的`PID`，該屬性是一個`int`類型的整數。

```php
Swoole\Server->manager_pid
```

!> 只能夠在`onStart/onWorkerStart`之後獲取到

  * **示範**

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

得到當前`Worker`進程的編號，包括 [Task進程](/learn?id=taskworker進程)，該屬性是一個`int`類型的整數。

```php
Swoole\Server->worker_id
```
  * **示範**

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

  * **提示**

    * 這個屬性與[onWorkerStart](/server/events?id=onworkerstart)時的`$workerId`是相同的。
    * `Worker`進程編號範圍是`[0, $server->setting['worker_num'] - 1]`
    * [Task進程](/learn?id=taskworker進程)編號範圍是 `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`

!> 工作進程重啟後`worker_id`的值是不變的


### $taskworker

當前進程是否是 `Task` 進程，該屬性是一個`bool`類型。

```php
Swoole\Server->taskworker
```

  * **返回值**

    * `true`表示當前的進程是`Task`工作進程
    * `false`表示當前的進程是`Worker`進程


### $worker_pid

得到當前`Worker`進程的作業系統進程`ID`。與`posix_getpid()`的返回值相同，該屬性是一個`int`類型的整數。

```php
Swoole\Server->worker_pid
```
