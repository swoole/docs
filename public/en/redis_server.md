# Redis\Server

A `Server` class that is compatible with the `Redis` server-side protocol, which can be used to implement a server program based on the `Redis` protocol.

?> `Swoole\Redis\Server` inherits from [Server](/server/tcp_init), so all the APIs and configuration options provided by `Server` can be used, and the process model is also the same. Please refer to the [Server](/server/init) section.

- **Available Clients**
  
  - `redis` clients in any programming language, including PHP's `redis` extension and the `phpredis` library
  - [Swoole\Coroutine\Redis](/coroutine_client/redis) coroutine client
  - Command-line tools provided by Redis, including `redis-cli` and `redis-benchmark`
## Methods

`Swoole\Redis\Server` inherits from `Swoole\Server` and can use all the methods provided by the parent class.
### setHandler

**Set the handler for Redis commands.**

`Redis\Server` does not need to set the [onReceive](/server/events?id=onreceive) callback. Just use the `setHandler` method to set the processing function for the corresponding command. If an unsupported command is received, an `ERROR` response will automatically be sent to the client with the message `ERR unknown command '$command'`.

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

- **Parameters**
  - **`string $command`**
    - **Description**: Name of the command
    - **Default value**: None
    - **Other values**: None

  - **`callable $callback`**
    - **Description**: Handler function for the command [when the callback function returns a string type, it will be automatically sent to the client]
    - **Default value**: None
    - **Other values**: None

    **Note**: The data returned must be in Redis format; you can use the `format` static method to pack it.
### format

?> **Format command response data.**

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **Parameters**

  * **`int $type`**
    * **Description**: Data type, corresponding constants can be found in [Format Parameter Constants](/redis_server?id=format-parameter-constants).
    * **Default value**: None
    * **Other values**: None
    
    !> When `$type` is of `NIL` type, `$value` is not required; `$value` is optional for `ERROR` and `STATUS` types; required for `INT`, `STRING`, `SET`, `MAP`.

  * **`mixed $value`**
    * **Description**: Value
    * **Default value**: None
    * **Other values**: None
### send

?> **Use the `send()` method from [Swoole\Server](/server/methods?id=send) to send data to the client.**

```php
Swoole\Server->send(int $fd, string $data): bool
```
## Constants
### Format Parameter Constants

Mainly used for packing `Redis` response data in the `format` function.

Constants | Description
---|---
Server::NIL | Returns nil data
Server::ERROR | Returns error code
Server::STATUS | Returns status
Server::INT | Returns integer, `format` must pass in parameter value, type must be integer
Server::STRING | Returns string, `format` must pass in parameter value, type must be string
Server::SET | Returns list, `format` must pass in parameter value, type must be array
Server::MAP | Returns Map, `format` must pass in parameter value, type must be associative indexed array
## Example of Use
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
        $server->data[$key] = array();
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
        $server->data[$key] = array();
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
