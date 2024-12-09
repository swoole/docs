# Redis\Server

Eine Klasse `Server`, die den `Redis`-Server-Protokoll unterstützt und auf dieser Basis ein `Redis`-Protokoll-Serverprogramm implementieren kann.

?> `Swoole\Redis\Server` erbt von [Server](/server/tcp_init), daher können alle von `Server` bereitgestellten `API` und Konfigurationsoptionen verwendet werden, und das Prozessmodell ist konsistent. Bitte beziehen Sie sich auf die [Server](/server/init)-Kapitel.

* **Verfügbare Clients**

  * `redis`-Clients in jeder Programmiersprache, einschließlich der `redis`-Erweiterung für PHP und der `phpredis`-Bibliothek
  * [Swoole\Coroutine\Redis](/coroutine_client/redis) Coroutine-Client
  * Die von `Redis` bereitgestellten Befehlzeilenwerkzeuge, einschließlich `redis-cli` und `redis-benchmark`


## Methoden

`Swoole\Redis\Server` erbt von `Swoole\Server` und kann alle Methoden des Elternclasses verwenden.


### setHandler

?> **Legt den Handler für ein `Redis`-Befehl fest.**

!> `Redis\Server` benötigt keinen [onReceive](/server/events?id=onreceive)-Rückruf. Verwenden Sie nur die `setHandler`-Methode, um die entsprechende Befehlshandlung festzulegen. Wenn ein nicht unterstützter Befehl empfangen wird, wird automatisch eine `ERROR`-Antwort an den Client gesendet, mit dem Nachrichten `ERR unknown command '$command'`.

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

* **Parameter** 

  * **`string $command`**
    * **Funktion**：Befehlname
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

  * **`callable $callback`**
    * **Funktion**：Befehlshandlung【Wenn die Rückgabe der Callback-Funktion ein String ist, wird es automatisch an den Client gesendet】
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

    !> Die zurückgegebene Daten müssen im `Redis`-Format sein, können Sie die `format`-Statische Methode verwenden, um sie zu verpacken


### format

?> **Formatieren Sie die Daten für die Befehlsbewertung.**

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **Parameter** 

  * **`int $type`**
    * **Funktion**：Datentyp, beziehen Sie sich auf die folgenden Konstanten [Formatparameterkonstanten](/redis_server?id=Formatparameterkonstanten).
    * **Standardwert**：Keine
    * **Andere Werte**：Keine
    
    !> Wenn `$type` der `NIL`-Typ ist, ist kein `$value` erforderlich; für `ERROR` und `STATUS` Typen ist `$value` optional; für `INT`, `STRING`, `SET`, `MAP` ist `$value` erforderlich.

  * **`mixed $value`**
    * **Funktion**：Wert
    * **Standardwert**：Keine
    * **Andere Werte**：Keine


### send

?> **Verwenden Sie die `send()`-Methode von [Swoole\Server](/server/methods?id=send), um Daten an den Client zu senden.**

```php
Swoole\Server->send(int $fd, string $data): bool
```


## Konstanten


### Formatparameterkonstanten

Diese werden hauptsächlich für die `format`-Funktion verwendet, um `Redis`-Antwortdaten zu verpacken


Konstante | Beschreibung
---|---
Server::NIL | Gibt ein nil-Daten zurück
Server::ERROR | Gibt einen Fehlercode zurück
Server::STATUS | Gibt einen Status zurück
Server::INT | Gibt eine ganze Zahl zurück, format muss einen Parameterwert angeben, der Typ muss eine ganze Zahl sein
Server::STRING | Gibt einen String zurück, format muss einen Parameterwert angeben, der Typ muss ein String sein
Server::SET | Gibt eine Liste zurück, format muss einen Parameterwert angeben, der Typ muss ein Array sein
Server::MAP | Gibt ein Map zurück, format muss einen Parameterwert angeben, der Typ muss ein assoziiertes Index-Array sein


## Verwendungszweck


### Server

```php
use Swoole\Redis\Server;

define('DB_FILE', __DIR__ . '/db');

$server = new Server("127.0.0.1", 9501, SWOOLE_BASE);

if (is_file(DB_FILE)) {
    $server->data = unserialize(file_get_contents(DB_FILE));
} else {
    $server->data = array();
}

$server->setHandler('GET', function ($fd, $data) use ($server) {
    if (count($data) == 0) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'GET' command"));
    }

    $key = $data[0];
    if (empty($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    } else {
        return $server->send($fd, Server::format(Server::STRING, $server->data[$key]));
    }
});

$server->setHandler('SET', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'SET' command"));
    }

    $key = $data[0];
    $server->data[$key] = $data[1];
    return $server->send($fd, Server::format(Server::STATUS, "OK"));
});

$server->setHandler('sAdd', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sAdd' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }

    $count = 0;
    for ($i = 1; $i < count($data); $i++) {
        $value = $data[$i];
        if (!isset($server->data[$key][$value])) {
            $server->data[$key][$value] = 1;
            $count++;
        }
    }

    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('sMembers', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sMembers' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::SET, array_keys($server->data[$key])));
});

$server->setHandler('hSet', function ($fd, $data) use ($server) {
    if (count($data) < 3) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hSet' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }
    $field = $data[1];
    $value = $data[2];
    $count = !isset($server->data[$key][$field]) ? 1 : 0;
    $server->data[$key][$field] = $value;
    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('hGetAll', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hGetAll' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::MAP, $server->data[$key]));
});

$server->on('WorkerStart', function ($server) {
    $server->tick(10000, function () use ($server) {
        file_put_contents(DB_FILE, serialize($server->data));
    });
});

$server->start();
```

### Client

```shell
$ redis-cli -h 127.0.0.1 -p 9501
127.0.0.1:9501> set name swoole
OK
127.0.0.1:9501> get name
"swoole"
127.0.0.1:9501> sadd swooler rango
(integer) 1
127.0.0.1:9501> sadd swooler twosee guoxinhua
(integer) 2
127.0.0.1:9501> smembers swooler
1) "rango"
2) "twosee"
3) "guoxinhua"
127.0.0.1:9501> hset website swoole "www.swoole.com"
(integer) 1
127.0.0.1:9501> hset website swoole "swoole.com"
(integer) 0
127.0.0.1:9501> hgetall website
1) "swoole"
2) "swoole.com"
127.0.0.1:9501> test
(error) ERR unknown command 'test'
127.0.0.1:9501>
```
