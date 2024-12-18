# свойства


### $setting

Функция [Server->set()](/server/methods?id=set) устанавливает параметры, которые сохраняются в свойстве `Server->$setting`. В обратном вызове можно получить значение аргументов выполнения. Это свойство является массивом массивов типа `array`.

```php
Swoole\Server->setting
```

  * **Пример**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```


### $connections

Итератор `TCP` соединений, который позволяет обходить все текущие соединения на сервере с помощью `foreach`. Функция `Swoole\Server->getClientList()` предоставляет аналогичные возможности, но с более удобным интерфейсом.

Элементы итератора представляют собой `fd` одного соединения.

```php
Swoole\Server->connections
```

!> Свойство `$connections` - это объект итератора, а не PHP-массив, поэтому его нельзя посещать с помощью `var_dump` или индексации массива, его можно только обходить с помощью `foreach`.

  * **Базовый режим**

    * В режиме [SWOOLE_BASE](/learn?id=swoole_base) поддерживается работа с `TCP` соединениями только внутри одного процесса, поэтому в `BASE` режиме итератор `$connections` может использоваться только внутри текущего процесса.

  * **Пример**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "Текущий сервер имеет " . count($server->connections) . " соединений\n";
```


### $host

Возвращает Host-адрес сервера, который слушает текущий сервер, это свойство является строкой типа `string`.

```php
Swoole\Server->host
```


### $port

Возвращает порт, на котором слушает текущий сервер, это свойство является целым числом типа `int`.

```php
Swoole\Server->port
```


### $type

Возвращает тип текущего Server, это свойство является целым числом типа `int`.

```php
Swoole\Server->type
```

!> Этот свойство может возвращать одно из следующих значений:

- `SWOOLE_SOCK_TCP` tcp ipv4 сокет

- `SWOOLE_SOCK_TCP6` tcp ipv6 сокет

- `SWOOLE_SOCK_UDP` udp ipv4 сокет

- `SWOOLE_SOCK_UDP6` udp ipv6 сокет

- `SWOOLE_SOCK_UNIX_DGRAM` unix сокет datagram
- `SWOOLE_SOCK_UNIX_STREAM` unix сокет stream 


### $ssl

Возвращает,Activates SSL на текущем сервере, это свойство является布尔ским типом.

```php
Swoole\Server->ssl
```


### $mode

Возвращает режим работы текущего сервера, это свойство является целым числом типа `int`.

```php
Swoole\Server->mode
```


!> Этот свойство может возвращать одно из следующих значений:

- `SWOOLE_BASE` Одиночный процессный режим
- `SWOOLE_PROCESS` многопроцессный режим


### $ports

Массив слушаемых портов, если сервер слушает несколько портов, можно получить все объекты `Swoole\Server\Port`, обходя `Server::$ports`.

Среди них `swoole_server::$ports[0]` - это основной порт, установленный в конструкторе.

  * **Пример**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```


### $master_pid

Возвращает ID главного процесса текущего сервера.

```php
Swoole\Server->master_pid
```

!> Можно получить только после `onStart/onWorkerStart`

  * **Пример**

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

Возвращает ID управляющего процесса текущего сервера, это свойство является целым числом типа `int`.

```php
Swoole\Server->manager_pid
```

!> Можно получить только после `onStart/onWorkerStart`

  * **Пример**

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

Получает номер текущего `Worker` процесса, включая [Task进程](/learn?id=taskworker进程), это свойство является целым числом типа `int`.

```php
Swoole\Server->worker_id
```
  * **Пример**

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

  * **Напоминание**

    * Это свойство идентично `$workerId` во время [onWorkerStart](/server/events?id=onworkerstart).
    * номера `Worker` процессов находятся в диапазоне `[0, $server->setting['worker_num'] - 1]`.
    * номера [Task进程](/learn?id=taskworker进程) находятся в диапазоне `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`.

!> После перезапуска рабочих процессов значение `worker_id` остается неизменным


### $taskworker

Исходит, является ли текущий процесс `Task` процессом, это свойство является布尔ским типом.

```php
Swoole\Server->taskworker
```

  * **Возвращаемое значение**

    * `true` означает, что текущий процесс является `Task` рабочим процессом
    * `false` означает, что текущий процесс является `Worker` процессом


### $worker_pid

Получает операционный системный ID процесса текущего `Worker` процесса. Identично возвращаемому значению `posix_getpid()`, это свойство является целым числом типа `int`.

```php
Swoole\Server->worker_pid
```
