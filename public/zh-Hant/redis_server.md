# Redis伺服器

一個兼容`Redis`伺服器端協定的`Server`類，可以基於此類實現`Redis`協定的小程序。

?> `Swoole\Redis\Server`繼承自[Server](/server/tcp_init)，所以`Server`提供的所有`API`和設定項目都可以使用，進程模型也是一致的。請參考[Server](/server/init)章節。

* **可用的客戶端**

  * 任意程式語言的`redis`客戶端，包括PHP的`redis`擴展和`phpredis`庫
  * [Swoole\Coroutine\Redis](/coroutine_client/redis) 協程客戶端
  * `Redis`提供的命令列工具，包括`redis-cli`、`redis-benchmark`


## 方法

`Swoole\Redis\Server`繼承自`Swoole\Server`，可以使用父類提供的所有方法。


### setHandler

?> **設置`Redis`命令字的處理器。**

!> `Redis\Server`不需要設置[onReceive](/server/events?id=onreceive)回調。只需使用`setHandler`方法設置對應命令的處理函數，收到未支持的命令後會自動向客戶端發送`ERROR`響應，消息為`ERR unknown command '$command'`。

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

* **參數** 

  * **`string $command`**
    * **功能**：命令的名稱
    * **預設值**：無
    * **其它值**：無

  * **`callable $callback`**
    * **功能**：命令的處理函數【回調函數返回字符串類型時會自動發送給客戶端】
    * **預設值**：無
    * **其它值**：無

    !> 返回的數據必須為`Redis`格式，可使用`format`靜態方法進行打包


### format

?> **格式化命令響應數據。**

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **參數** 

  * **`int $type`**
    * **功能**：數據類型，對應常量參考下文 [格式參數常量](/redis_server?id=格式參數常量)。
    * **預設值**：無
    * **其它值**：無
    
    !> 當`$type`為`NIL`類型時，不需要傳入`$value`；`ERROR`和`STATUS`類型`$value`可選；`INT`、`STRING`、`SET`、`MAP`必填。

  * **`mixed $value`**
    * **功能**：值
    * **預設值**：無
    * **其它值**：無


### send

?> **使用[Swoole\Server](/server/methods?id=send)中的`send()`方法將數據發送給客戶端。**

```php
Swoole\Server->send(int $fd, string $data): bool
```


## 常量


### 格式參數常量

主要用於`format`函數打包`Redis`響應數據


常量 | 說明
---|---
Server::NIL | 返回nil數據
Server::ERROR | 返回錯誤碼
Server::STATUS | 返回狀態
Server::INT | 返回整數，format必須傳入參數值，類型必須為整數
Server::STRING | 返回字符串，format必須傳入參數值，類型必須為字符串
Server::SET | 返回列表，format必須傳入參數值，類型必須為數組
Server::MAP | 返回Map，format必須傳入參數值，類型必須為關聯索引數組


## 使用示範


### 伺服器端

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

### 客戶端

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
