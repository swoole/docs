# Eigenschaften


### $setting

Die durch die [Server->set()](/server/methoden?id=set)-Funktion gesetzten Parameter werden auf die `Server::$setting`-Eigenschaft gespeichert. In der Rückruffunktion kann man den Wert des Laufenden Parameters访问ieren. Diese Eigenschaft ist ein `array`-Typ.

```php
Swoole\Server->setting
```

  * **Beispiel**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```


### $connections

Ein `TCP`-Verbindungsiteratorklasse, der es ermöglicht, alle aktuellen Verbindungen des Servers mit einem `foreach`-Loop zu iterieren. Die Funktion dieser Eigenschaft ist identisch mit der [Server->getClientList](/server/methoden?id=getclientlist)-Methode, ist jedoch freundlichere.

Die durchliefenen Elemente sind die `fd` der einzelnen Verbindungen.

```php
Swoole\Server->connections
```

!> `$connections` ist ein Iteratorklasse-Objekt und kein PHP-Array, daher kann es nicht mit `var_dump` oder Array-Indizes访问iert werden, sondern nur durch `foreach` iteriert.

  * **Base-Modus**

    * Im [SWOOLE_BASE](/learn?id=swoole_base)-Modus wird die Übertragung von `TCP`-Verbindungen zwischen Prozessen nicht unterstützt. Daher kann in einem `BASE`-Modus der `$connections`-Iteratorklasse nur innerhalb des aktuellen Prozesses verwendet werden.

  * **Beispiel**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "Derzeit gibt es " . count($server->connections) . " Verbindungen auf dem Server\n";
```


### $host

Gibt die `host` der aktuellen Server-Adresse zurück, die diese Eigenschaft ist ein `string`-Typ.

```php
Swoole\Server->host
```


### $port

Gibt den `port` der aktuellen Server-Adresse zurück, die diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server->port
```


### $type

Gibt den Typ des aktuellen Servers zurück, die diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server->type
```

!> Diese Eigenschaft gibt eines der folgenden Werte zurück:

- `SWOOLE_SOCK_TCP` tcp ipv4 socket

- `SWOOLE_SOCK_TCP6` tcp ipv6 socket

- `SWOOLE_SOCK_UDP` udp ipv4 socket

- `SWOOLE_SOCK_UDP6` udp ipv6 socket

- `SWOOLE_SOCK_UNIX_DGRAM` unix socket dgram
- `SWOOLE_SOCK_UNIX_STREAM` unix socket stream 


### $ssl

Gibt an, ob derzeit SSL auf dem Server aktiviert ist, die diese Eigenschaft ist ein `bool`-Typ.

```php
Swoole\Server->ssl
```


### $mode

Gibt den Prozesses模式 des aktuellen Servers zurück, die diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server->mode
```


!> Diese Eigenschaft gibt eines der folgenden Werte zurück:

- `SWOOLE_BASE` Einzelprozessmodus
- `SWOOLE_PROCESS` Mehrprozessmodus


### $ports

Eine Array von Ports, die angehört werden. Wenn der Server mehrere Ports hört, kann man alle `Swoole\Server\Port` Objekte durch Iterieren von `Server::$ports` erhalten.

Darin ist `swoole_server::$ports[0]` der vom Konstruktor festgelegte Hauptserverport.

  * **Beispiel**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```


### $master_pid

Gibt die `PID` des aktuellen Server-Hauptkerns zurück.

```php
Swoole\Server->master_pid
```

!> Kann nur nach `onStart/onWorkerStart` erhalten werden

  * **Beispiel**

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

Gibt die `PID` des aktuellen Server-Manager-Prozesses zurück, die diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server->manager_pid
```

!> Kann nur nach `onStart/onWorkerStart` erhalten werden

  * **Beispiel**

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

Erhalten Sie die Nummer des aktuellen `Worker`-Prozesses, einschließlich [Task-Prozesse](/learn?id=taskworkerprocess), die diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server->worker_id
```
  * **Beispiel**

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

  * **Hinweis**

    * Diese Eigenschaft ist identisch mit der `$workerId` bei [onWorkerStart](/server/events?id=onworkerstart).
    * Die Nummer des `Worker`-Prozesses liegt im Bereich `[0, $server->setting['worker_num'] - 1]`.
    * Die Nummer des [Task-Prozesses](/learn?id=taskworkerprocess) liegt im Bereich `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`.

!> Nach dem Neustart des Arbeitsprozesses ändert sich der Wert von `worker_id` nicht


### $taskworker

Gibt an, ob der aktuelle Prozess ein `Task`-Prozess ist, die diese Eigenschaft ist ein `bool`-Typ.

```php
Swoole\Server->taskworker
```

  * **Rückgabewert**

    * `true` bedeutet, dass der aktuelle Prozess ein `Task`-Arbeitsprozess ist
    * `false` bedeutet, dass der aktuelle Prozess ein `Worker`-Prozess ist


### $worker_pid

Erhalten Sie die Prozess-ID des aktuellen `Worker`-Prozesses. Es ist gleich dem Rückgabewert von `posix_getpid()`, die diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server->worker_pid
```
