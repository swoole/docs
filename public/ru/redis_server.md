# Redis\Server

Класс `Server`, совместимый с протоколом сервера `Redis`, который может быть использован для реализации программы сервера,遵循ющего протокол `Redis`.

?> Класс `Swoole\Redis\Server` наследуется от [Server](/server/tcp_init), поэтому все API и настройки, предоставляемые `Server`, могут быть использованы, а модель процессов также одинакова. Пожалуйста, обратитесь к главе [Server](/server/init).

* **Поддерживаемые клиенты**

  * Клиенты `redis` любой программной языка, включая расширение `redis` для PHP и библиотеку `phpredis`
  * Корoutine клиент [Swoole\Coroutine\Redis](/coroutine_client/redis)
  * Командные инструменты `Redis`, включая `redis-cli` и `redis-benchmark`


## Методы

`Swoole\Redis\Server` наследуется от `Swoole\Server` и может использовать все методы, предоставляемые родительским классом.


### setHandler

?> **Установить обработчик команды `Redis`.**

!> Для `Redis\Server` не требуется установка回调-функции [onReceive](/server/events?id=onreceive). Просто используйте метод `setHandler` для установки функции обработки соответствующей команды. Если 收到 нес支持的 команда, сервер автоматически отправит клиенту ответ `ERROR` с сообщением `ERR unknown command '$command'`.

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

* **Параметры** 

  * **`string $command`**
    * **Функция**: Название команды
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`callable $callback`**
    * **Функция**: Функция обработки команды [回调 функция возвращает строковый тип, который будет автоматически отправлен клиенту]
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

    !> Возвращаемая данные должны соответствовать формату `Redis`, можно использовать статический метод `format` для упаковки


### format

?> **Форматировать данные ответа команды. **

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **Параметры** 

  * **`int $type`**
    * **Функция**: Тип данных, см. константы ниже [Константы параметров формы](/redis_server?id=Константы параметров формы).
    * **По умолчанию**: Нет
    * **Другие значения**: Нет
    
    !> Когда `$type` равен типу `NIL`, не требуется передать `$value`; для типов `ERROR` и `STATUS` `$value` необязателен; для типов `INT`, `STRING`, `SET`, `MAP` он обязательно требуется.

  * **`mixed $value`**
    * **Функция**: Значение
    * **По умолчанию**: Нет
    * **Другие значения**: Нет


### send

?> **Использовать метод `send()` из [Swoole\Server](/server/methods?id=send) для отправки данных клиенту.**

```php
Swoole\Server->send(int $fd, string $data): bool
```


## Константы


### Константы параметров формы

Основное использование для функции `format` для упаковки данных ответа `Redis`


Константа | Описание
---|---
Server::NIL | Возвращает данные NIL
Server::ERROR | Возвращает код ошибки
Server::STATUS | Возвращает статус
Server::INT | Возвращает целое число, для `format` необходимо передать значение, тип должен быть целым числом
Server::STRING | Возвращает строку, для `format` необходимо передать значение, тип должен быть строкой
Server::SET | Возвращает список, для `format` необходимо передать значение, тип должен быть массивом
Server::MAP | Возвращает карту, для `format` необходимо передать значение, тип должен быть ассоциативным индексным массивом


## Примеры использования


### Сервер

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

### Клиент

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
