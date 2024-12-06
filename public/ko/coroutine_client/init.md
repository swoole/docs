# 코루outine 클라이언트 <!-- {docsify-ignore-all} -->

다음 코루outine 클라이언트는 Swoole에 내장된 클래스이며, ⚠️ 표지의 클라이언트는 더 이상 사용하지 않는 것이 좋으며, PHP 원본의 기능인 [하이퍼코루outine화](/runtime)을 사용할 수 있습니다.

* [TCP/UDP/UnixSocket 클라이언트](coroutine_client/client.md)
* [Socket 클라이언트](coroutine_client/socket.md)
* [HTTP/WebSocket 클라이언트](coroutine_client/http_client.md)
* [HTTP2 클라이언트](coroutine_client/http2_client.md)
* [PostgreSQL 클라이언트](coroutine_client/postgresql.md)
* [FastCGI 클라이언트](coroutine_client/fastcgi.md)
⚠️ [Redis 클라이언트](coroutine_client/redis.md)
⚠️ [MySQL 클라이언트](coroutine_client/mysql.md)
* [시스템](/coroutine/system) 시스템 API

## 타임아웃 규칙

모든 네트워크 요청(연결 구축, 데이터 전송, 데이터 수신)은 타임아웃될 수 있으며, `Swoole` 코루outine 클라이언트는 세 가지 방식으로 타임아웃을 설정합니다:

1. 방법의 매개변수에 타임아웃 시간을 전달하여 설정하는 것, 예를 들어 [Co\Client->connect()](/coroutine_client/client?id=connect), [Co\Http\Client->recv()](/coroutine_client/http_client?id=recv), [Co\MySQL->query()](/coroutine_client/mysql?id=query) 등

!> 이러한 방식은 영향을 미치는 범위가 가장 작고(해당 함수 호출에만 적용되며), 우선순위가 가장 높습니다(해당 함수 호출은 아래의 `2`, `3` 설정은 무시합니다).

2. `Swoole` 코루outine 클라이언트 클래스의 `set()` 또는 `setOption()` 메서드를 통해 타임아웃을 설정하는 것, 예를 들어:

```php
$client = new Co\Client(SWOOLE_SOCK_TCP);
//or
$client = new Co\Http\Client("127.0.0.1", 80);
//or
$client = new Co\Http2\Client("127.0.0.1", 443, true);
$client->set(array(
    'timeout' => 0.5,//전체 타임아웃, 연결, 전송, 수신 모든 타임아웃 포함
    'connect_timeout' => 1.0,//연결 타임아웃, 첫 번째 전체 timeout를 덮습니다
    'write_timeout' => 10.0,//전송 타임아웃, 첫 번째 전체 timeout를 덮습니다
    'read_timeout' => 0.5,//수신 타임아웃, 첫 번째 전체 timeout를 덮습니다
));

//Co\Redis()는 write_timeout와 read_timeout 설정이 없습니다
$client = new Co\Redis();
$client->setOption(array(
    'timeout' => 1.0,//전체 타임아웃, 연결, 전송, 수신 모든 타임아웃 포함
    'connect_timeout' => 0.5,//연결 타임아웃, 첫 번째 전체 timeout를 덮습니다 
));

//Co\MySQL()는 set 설정 기능이 없습니다
$client = new Co\MySQL();

//Co\Socket은 setOption을 통해 설정합니다
$socket = new Co\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = array('sec'=>1, 'usec'=>500000);
$socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout);//데이터 수신 타임아웃 시간
$socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout);//연결 타임아웃과 데이터 전송 타임아웃 설정
```

!> 이러한 방식은 현재 클래스에만 영향을 미치며, 첫 번째 방식에 의해 덮입니다. 아래의 세 번째 방식 설정은 무시됩니다.

3. 위의 두 가지 방식의 타임아웃 설정 규칙이 매우 번거롭고 통일되지 않기 때문에, 개발자가 어디서나 조심스럽게 설정해야 한다는 피드백을 피하기 위해, `v4.2.10` 버전부터 모든 코루outine 클라이언트는 글로벌 통일된 타임아웃 규칙 설정을 제공합니다. 이러한 영향은 가장 크고, 우선순위가 가장 낮습니다. 다음과 같습니다:

```php
Co::set([
    'socket_timeout' => 5,
    'socket_connect_timeout' => 1,
    'socket_read_timeout' => 1,
    'socket_write_timeout' => 1,
]);
```

+ `-1` : 영원히 타임아웃되지 않습니다
+ `0` : 타임아웃 시간 변경하지 않습니다
+ `기타 0보다 큰 값` : 해당 초수의 타임아웃 타이머를 설정하며, 최대 정확도가 `1밀리초`이며, 부동소수입니다. `0.5`는 `500밀리초`를 나타냅니다.
+ `socket_connect_timeout` : TCP 연결 타임아웃 시간을 나타내며, **기본적으로 `1초`**이며, `v4.5.x` 버전부터 **기본적으로 `2초`**입니다.
+ `socket_timeout` : TCP 읽기/쓰기 작업 타임아웃 시간을 나타내며, **기본적으로 `-1`**이며, `v4.5.x` 버전부터 **기본적으로 `60초`**입니다. 읽기와 쓰기를 분리하여 설정하고 싶다면 아래의 설정을 참고하세요.
+ `socket_read_timeout` : `v4.3` 버전에서 추가되었으며, TCP**읽기** 작업 타임아웃 시간을 나타내며, **기본적으로 `-1`**이며, `v4.5.x` 버전부터 **기본적으로 `60초`**입니다.
+ `socket_write_timeout` : `v4.3` 버전에서 추가되었으며, TCP**쓰기** 작업 타임아웃 시간을 나타내며, **기본적으로 `-1`**이며, `v4.5.x` 버전부터 **기본적으로 `60초`**입니다.

!> **즉:** `v4.5.x` 이전 버전의 모든 `Swoole`가 제공하는 코루outine 클라이언트는 앞서 언급한 첫 번째, 두 번째 방식으로 타임아웃을 설정하지 않은 경우, 기본 연결 타임아웃 시간은 `1초`이며, 읽기/쓰기 작업은 영원히 타임아웃되지 않습니다;  
`v4.5.x` 버전부터 기본 연결 타임아웃 시간은 `60초`이며, 읽기/쓰기 작업 타임아웃 시간도 `60초`입니다;  
만약 중간에 글로벌 타임아웃을 변경한다면, 이미 생성된 소켓에는 영향을 미치지 않습니다.

### PHP 공식 네트워크 라이브러리 타임아웃

위에서 언급한 `Swoole`가 제공하는 코루outine 클라이언트 외에도, [하이퍼코루outine화](/runtime)에서 사용하는 것은 원본 PHP가 제공하는 방법으로, 그들의 타임아웃 시간은 [default_socket_timeout](http://php.net/manual/zh/filesystem.configuration.php) 설정에 영향을 받습니다. 개발자는 `ini_set('default_socket_timeout', 60)`와 같이 이를 별도로 설정할 수 있으며, 그 기본값은 60입니다.
