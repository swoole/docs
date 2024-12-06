# 다포트 감시

`Swoole\Server`는 여러 포트를 감시할 수 있으며, 각 포트는 다른 프로토콜 처리 방식을 설정할 수 있습니다. 예를 들어 80번 포트는 HTTP 프로토콜을 처리하고, 9507번 포트는 TCP 프로토콜을 처리합니다. `SSL/TLS` 전송 암호화도 특정 포트에만启用될 수 있습니다.

!> 예를 들어 주 서버가 WebSocket 또는 HTTP 프로토콜인 경우, 새로 감시하는 TCP 포트(listen([/server/methods?id=listen]의 반환값, 즉[Swoole\Server\Port](server/server_port.md) 객체, 이후 Swoole\Server\Port으로 줄임)는 기본적으로 주 Server의 프로토콜 설정을 상속하므로, 새로운 프로토콜을 사용하기 위해서는 `port` 객체의 `set` 메서드와 `on` 메서드를 별도로 호출해야 합니다. 


## 새로운 포트 감시

```php
//port 객체를 반환합니다.
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```


## 네트워크 프로토콜 설정

```php
//port 객체의 set 메서드를 호출합니다.
$port1->set([
	'open_length_check' => true,
	'package_length_type' => 'N',
	'package_length_offset' => 0,
	'package_max_length' => 800000,
]);

$port3->set([
	'open_eof_split' => true,
	'package_eof' => "\r\n",
	'ssl_cert_file' => 'ssl.cert',
	'ssl_key_file' => 'ssl.key',
]);
```


## 콜백 함수 설정

```php
//각 port의 콜백 함수를 설정합니다.
$port1->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});

$port1->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});

$port1->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$port2->on('packet', function ($serv, $data, $addr) {
    var_dump($data, $addr);
});
```


## Http/WebSocket

`Swoole\Http\Server`와 `Swoole\WebSocket\Server`는 상속 하위클래스를 사용하여 구현되었기 때문에, `Swoole\Server` 인스턴스의 `listen` 메서드를 호출하여 HTTP 또는 WebSocket 서버를 만들 수 없습니다.

서버의 주요 기능이 `RPC`인 경우에도 간단한 웹 관리 인터페이스를 제공하고자 하는 경우가 있습니다. 이러한 경우에는 먼저 `HTTP/WebSocket` 서버를 만들고 나서 원시 TCP 포트에 대한 `listen`을 수행할 수 있습니다.


### 예제

```php
$http_server = new Swoole\Http\Server('0.0.0.0',9998);
$http_server->set(['daemonize'=> false]);
$http_server->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

//다중으로 1개의 TCP 포트를 감시하고, 외부에 TCP 서비스를 제공하며, TCP 서버의 콜백을 설정합니다.
$tcp_server = $http_server->listen('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
//기본적으로 새로 감시하는 포트 9999은 주 서버의 설정을 상속하며, HTTP 프로토콜입니다.
//주 서버의 설정을 덮어씌우기 위해서는 set 메서드를 호출해야 합니다.
$tcp_server->set([]);
$tcp_server->on('receive', function ($server, $fd, $threadId, $data) {
    echo $data;
});

$http_server->start();
```

이런 코드를 통해 HTTP 서비스를 제공하면서 동시에 TCP 서비스를 제공하는 서버를 구축할 수 있으며, 더 구체적인 우아한 코드 조합은 여러분이 직접 구현합니다.


## TCP, HTTP, WebSocket 다 프로토콜 포트 복합 설정

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_websocket_protocol' => true, // 이 포트가 WebSocket 프로토콜을 지원하도록 설정합니다.
]);
```

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_http_protocol' => false, // 이 포트의 HTTP 프로토콜 기능을 비활성화합니다.
]);
```

마찬가지로 `open_http_protocol`, `open_http2_protocol`, `open_mqtt_protocol` 등의 매개변수가 있습니다.


## 선택적 매개변수

* 포트 `port`가 `set` 메서드를 호출하지 않은 경우, 프로토콜 처리 옵션을 설정한 포트는 주 서버의 관련 설정을 상속합니다.
* 주 서버가 `HTTP/WebSocket` 서버인 경우, 프로토콜 매개변수를 설정하지 않은 경우, 감시하는 포트는 여전히 `HTTP` 또는 `WebSocket` 프로토콜로 설정되며, 포트에 대한 [onReceive](/server/events?id=onreceive) 콜백은 실행되지 않습니다.
* 주 서버가 `HTTP/WebSocket` 서버인 경우, 포트가 `set` 메서드를 호출하여 설정 매개변수를 설정하면, 주 서버의 프로토콜 설정이 초기화됩니다. 감시하는 포트는 `TCP` 프로토콜로 변경됩니다. 감시하는 포트가 여전히 `HTTP/WebSocket` 프로토콜을 사용하고자 하는 경우, 설정에 `open_http_protocol => true`와 `open_websocket_protocol => true`를 추가해야 합니다.

**`port`가 `set` 메서드를 통해 설정할 수 있는 매개변수는 다음과 같습니다:**

* 소켓 매개변수: 예를 들어 `backlog`, `open_tcp_keepalive`, `open_tcp_nodelay`, `tcp_defer_accept` 등
* 프로토콜 관련: 예를 들어 `open_length_check`, `open_eof_check`, `package_length_type` 등
* SSL 인증서 관련: 예를 들어 `ssl_cert_file`, `ssl_key_file` 등

구체적인 내용은 [설정 장章节](/server/setting)을 참고하세요.


## 선택적 콜백

`port`가 `on` 메서드를 호출하지 않은 경우, 콜백 함수를 설정한 포트는 기본적으로 주 서버의 콜백 함수를 사용합니다. `port`가 `on` 메서드를 통해 설정할 수 있는 콜백은 다음과 같습니다:
 

### TCP 서버

* onConnect
* onClose
* onReceive


### UDP 서버

* onPacket
* onReceive
    

### HTTP 서버

* onRequest
    

### WebSocket 서버

* onMessage
* onOpen
* onHandshake

!> 다른 감시 포트의 콜백 함수는 여전히 같은 `Worker` 프로세스 공간에서 실행됩니다.

## 다포트의 연결 탐색

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9514, SWOOLE_BASE);

$tcp = $server->listen("0.0.0.0", 9515, SWOOLE_SOCK_TCP);
$tcp->set([]);

$server->on("open", function ($serv, $req) {
    echo "new WebSocket Client, fd={$req->fd}\n";
});

$server->on("message", function ($serv, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $serv->push($frame->fd, "this is server OnMessage");
});

$tcp->on('receive', function ($server, $fd, $reactor_id, $data) {
    //9514번 포트의 연결만 탐색합니다. $tcp가 아니라 $server를 사용하기 때문입니다.
    $websocket = $server->ports[0];
    foreach ($websocket->connections as $_fd) {
        var_dump($_fd);
        if ($server->exist($_fd)) {
            $server->push($_fd, "this is server onReceive");
        }
    }
    $server->send($fd, 'receive: '.$data);
});

$server->start();
```
