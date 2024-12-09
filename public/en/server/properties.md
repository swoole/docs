# Properties


### $setting

The parameters set by the [Server->set()](/server/methods?id=set) function are saved to the `Server->$setting` property. The value of the running parameters can be accessed within the callback function. This property is an array of `array` type.

```php
Swoole\Server->setting
```

* **Example**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```


### $connections

A `TCP` connection iterator, which can be used to iterate over all current connections on the server. The functionality of this property is consistent with the [Server->getClientList](/server/methods?id=getclientlist) method, but it is more user-friendly.

The elements iterated over are the `fd` of individual connections.

```php
Swoole\Server->connections
```

> `$connections` is an iterator object, not a PHP array, so it cannot be accessed using `var_dump` or array indices. It can only be traversed using a `foreach` loop.

* **Base Mode**

    * In [SWOOLE_BASE](/learn?id=swoole_base) mode, cross-process operations on `TCP` connections are not supported. Therefore, in `BASE` mode, the `$connections` iterator can only be used within the current process.

* **Example**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "Current server has " . count($server->connections) . " connections\n";
```


### $host

Returns the `host` of the current server's listening address. This property is a string of type `string`.

```php
Swoole\Server->host
```


### $port

Returns the `port` of the current server's listening port. This property is an integer of type `int`.

```php
Swoole\Server->port
```


### $type

Returns the type of the current Server. This property is an integer of type `int`.

```php
Swoole\Server->type
```

> This property returns one of the following values:

- `SWOOLE_SOCK_TCP` tcp ipv4 socket

- `SWOOLE_SOCK_TCP6` tcp ipv6 socket

- `SWOOLE_SOCK_UDP` udp ipv4 socket

- `SWOOLE_SOCK_UDP6` udp ipv6 socket

- `SWOOLE_SOCK_UNIX_DGRAM` unix socket dgram
- `SWOOLE_SOCK_UNIX_STREAM` unix socket stream 


### $ssl

Returns whether the current server has `ssl` enabled. This property is a boolean of type `bool`.

```php
Swoole\Server->ssl
```


### $mode

Returns the process mode of the current server. This property is an integer of type `int`.

```php
Swoole\Server->mode
```

> This property returns one of the following values:

- `SWOOLE_BASE` single-process mode
- `SWOOLE_PROCESS` multi-process mode


### $ports

An array of listening ports. If the server is listening on multiple ports, you can iterate over `Server::$ports` to obtain all `Swoole\Server\Port` objects.

The first element of `swoole_server::$ports` is the main server port set in the constructor method.

* **Example**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```


### $master_pid

Returns the `PID` of the current server's master process.

```php
Swoole\Server->master_pid
```

> This can only be obtained after `onStart/onWorkerStart`.

* **Example**

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

Returns the `PID` of the current server's manager process. This property is an integer of type `int`.

```php
Swoole\Server->manager_pid
```

> This can only be obtained after `onStart/onWorkerStart`.

* **Example**

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

Gets the number of the current `Worker` process, including [Task processes](/learn?id=taskworker进程). This property is an integer of type `int`.

```php
Swoole\Server->worker_id
```
 * **Example**

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

 * **Note**

    * This property is the same as `$workerId` during [onWorkerStart](/server/events?id=onworkerstart).
    * The range of `Worker` process numbers is `[0, $server->setting['worker_num'] - 1]`.
    * The range of [Task processes](/learn?id=taskworker进程) numbers is `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`.

!> The value of `worker_id` remains unchanged after a worker process restarts.


### $taskworker

Indicates whether the current process is a `Task` process. This property is a boolean of type `bool`.

```php
Swoole\Server->taskworker
```

* **Return Value**

    * `true` indicates that the current process is a `Task` worker process.
    * `false` indicates that the current process is a `Worker` process.


### $worker_pid

Gets the operating system process ID of the current `Worker` process. It is the same as the return value of `posix_getpid()`. This property is an integer of type `int`.

```php
Swoole\Server->worker_pid
```
