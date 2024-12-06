# Redis\Server

`Redis` 서버 프로토콜을 호환하는 `Server` 클래스로, 이 클래스를 기반으로 `Redis` 프로토콜의 서버 프로그램을 구현할 수 있습니다.

?> `Swoole\Redis\Server`는 [Server](/server/tcp_init)를 상속하여, `Server`가 제공하는 모든 `API`와 구성 요소는 사용할 수 있으며, 프로세스 모델도 일관됩니다. 자세한 내용은 [Server](/server/init) 장을 참고하세요.

* **대상 클라이언트**

  * PHP의 `redis` 확장 및 `phpredis` 라이브러리와 같은 모든 프로그래밍 언어의 `redis` 클라이언트
  * [Swoole\Coroutine\Redis](/coroutine_client/redis) 코루틴 클라이언트
  * `Redis`가 제공하는 명령행 도구, `redis-cli`, `redis-benchmark` 포함


## 방법

`Swoole\Redis\Server`는 `Swoole\Server`를 상속하여 부모클래스가 제공하는 모든 방법을 사용할 수 있습니다.


### setHandler

?> **`Redis` 명령어를 처리하는 핸들을 설정합니다.**

!> `Redis\Server`는 [onReceive](/server/events?id=onreceive) 콜백을 설정할 필요가 없습니다. 단지 `setHandler` 메서드를 사용하여 해당 명령에 대한 처리 함수를 설정하면 되며, 지원하지 않는 명령을 받으면 자동으로 클라이언트에게 `ERROR` 응답을 보냅니다. 메시지는 `ERR unknown command '$command'`입니다.

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

* **매개변수** 

  * **`string $command`**
    * **기능**：명령의 이름
    * **기본값**：없음
    * **기타 값**：없음

  * **`callable $callback`**
    * **기능**：명령 처리 함수【콜백 함수가 문자열을 반환할 경우 자동으로 클라이언트에 보냅니다】
    * **기본값**：없음
    * **기타 값**：없음

    !> 반환된 데이터는 반드시 `Redis` 포맷이어야 하며, `format` 정적 메서드를 사용하여 포장할 수 있습니다.


### format

?> **명령 응답 데이터를 포맷화합니다.**

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **매개변수** 

  * **`int $type`**
    * **기능**：데이터 유형, 다음의 [포맷 매개변수 상수](/redis_server?id=포맷 매개변수 상수) 참조.
    * **기본값**：없음
    * **기타 값**：없음
    
    !> `$type`이 `NIL` 유형일 경우 `$value`는 전달할 필요가 없습니다; `ERROR` 및 `STATUS` 유형의 `$value`는 선택적입니다; `INT`, `STRING`, `SET`, `MAP`는 필수입니다.

  * **`mixed $value`**
    * **기능**：값
    * **기본값**：없음
    * **기타 값**：없음


### send

?> **[Swoole\Server](/server/methods?id=send)의 `send()` 메서드를 사용하여 데이터를 클라이언트에게 보냅니다.**

```php
Swoole\Server->send(int $fd, string $data): bool
```


## 상수


### 포맷 매개변수 상수

주로 `format` 함수에서 `Redis` 응답 데이터를 포장하는 데 사용됩니다.


상수 | 설명
---|---
Server::NIL | nil 데이터를 반환합니다
Server::ERROR | 오류 코드를 반환합니다
Server::STATUS | 상태를 반환합니다
Server::INT | 정수를 반환합니다, format은 반드시 매개변수를 전달해야 하며 유형은 정수여야 합니다
Server::STRING | 문자열을 반환합니다, format은 반드시 매개변수를 전달해야 하며 유형은 문자열이어야 합니다
Server::SET | 리스트를 반환합니다, format은 반드시 매개변수를 전달해야 하며 유형은 배열이어야 합니다
Server::MAP | 맵을 반환합니다, format은 반드시 매개변수를 전달해야 하며 유형은 관련 인덱스 배열이어야 합니다


## 사용 예제


### 서버 측

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

### 클라이언트 측

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
