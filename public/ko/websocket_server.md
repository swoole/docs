# Swoole\WebSocket\Server

?> 내장된 `WebSocket` 서버 지원을 통해 몇 줄의 `PHP` 코드로 [비동기 IO](/learn?id=同步io异步io)의 멀티 프로세스 `WebSocket` 서버를 작성할 수 있습니다.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});

$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();
```

* **클라이언트**

  * `Chrome/Firefox/`고급버전`IE/Safari` 등 브라우저에는 내장된 `JS` 언어의 `WebSocket` 클라이언트가 있습니다.
  * 위챗 미니 프로그램 개발 프레임워크에는 내장된 `WebSocket` 클라이언트가 있습니다.
  * [비동기 IO](/learn?id=同步io异步io)의 `PHP` 프로그램에서는 [Swoole\Coroutine\Http](/coroutine_client/http_client)를 `WebSocket` 클라이언트로 사용할 수 있습니다.
  * `Apache/PHP-FPM` 또는 기타 동기적 막힘의 `PHP` 프로그램에서는 `swoole/framework`가 제공하는 [동기적 WebSocket 클라이언트](https://github.com/matyhtf/framework/blob/master/libs/Swoole/Client/WebSocket.php)를 사용할 수 있습니다.
  * 비 `WebSocket` 클라이언트는 `WebSocket` 서버와 통신할 수 없습니다.

* **WebSocket 클라이언트 여부를 어떻게 판단할 수 있나요?**

?> 다음의 예를 통해 연결 정보를 가져오면, 반환되는 배열 중 하나는 [websocket_status](/websocket_server?id=连接状态)가 있으며, 이를 통해 `WebSocket` 클라이언트 여부를 판단할 수 있습니다.
```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    $client = $server->getClientInfo($frame->fd);
    // 또는 $client = $server->connection_info($frame->fd);
    if (isset($client['websocket_status'])) {
        echo "WebSocket 연결";
    } else {
        echo "WebSocket 연결이 아닙니다";
    }
});
```



## 이벤트

?> `WebSocket` 서버는 [Swoole\Server](/server/methods)와 [Swoole\Http\Server](/http_server) 기반 클래스의 콜백 함수 외에도 추가로 `4`개의 콜백 함수 설정이 있습니다. 그 중:

* `onMessage` 콜백 함수는 필수
* `onOpen`, `onHandShake`, `onBeforeHandShakeResponse`(Swoole5에서 제공되는 이벤트) 콜백 함수는 선택적입니다.


### onBeforeHandshakeResponse

!> Swoole 버전 >= `v5.0.0`에서 사용할 수 있습니다.

?> **`WebSocket` 연결이 전개되기 전에 발생합니다. 사용자가 커스텀 핸드셋팅을 원하지 않지만 응답 헤더에 일부 `http header` 정보를 설정하고자 할 때, 이 이벤트를 호출할 수 있습니다.**

```php
onBeforeHandshakeResponse(Swoole\Http\Request $request, Swoole\Http\Response $response);
```


### onHandShake

?> **`WebSocket` 연결이 전개된 후 핸드셋팅이 이루어집니다. `WebSocket` 서버는 자동으로 `handshake` 핸드셋팅 과정을 진행합니다. 사용자가 핸드셋팅을 스스로 처리하고자 할 때, `onHandShake` 이벤트 콜백 함수를 설정할 수 있습니다.**

```php
onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

* **알림**

  * `onHandShake` 이벤트 콜백은 선택적입니다.
  * `onHandShake` 콜백 함수를 설정하면 더 이상 `onOpen` 이벤트가 발생하지 않으며, 응용 코드에서 스스로 처리해야 합니다. 사용자는 `$server->defer`를 사용하여 `onOpen` 로직을 호출할 수 있습니다.
  * `onHandShake`에서는 반드시 [response->status()](/http_server?id=status)를 호출하여 상태 코드를 `101`로 설정하고 [response->end()](/http_server?id=end)를 호출해야 합니다. 그렇지 않으면 핸드셋팅이 실패합니다.
  * 내장된 핸드셋팅 프로토콜은 `Sec-WebSocket-Version: 13`이며, 저버전 브라우저는 스스로 핸드셋팅을 구현해야 합니다.

* **주의**

!> `handshake`를 스스로 처리해야 할 때에만 이 콜백 함수를 설정합니다. "커스텀" 핸드셋팅 과정을 원하지 않는 경우에는 이 콜백을 설정하지 말고 Swoole의 기본 핸드셋팅을 사용하세요. 다음은 "커스텀" `handshake` 이벤트 콜백 함수에서 반드시 갖춰져야 할 내용입니다:

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // print_r( $request->header );
    // 만약 내가 일부 커스텀 요구 조건을 충족시키지 못한다면, end로 출력하고 false를 반환하여 핸드셋팅을 실패시킵니다.
    //    $response->end();
    //     return false;

    // websocket handshake 연결 알고리즘 검증
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(
        sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        )
    );

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $response->end();
});
```

!> `onHandShake` 콜백 함수를 설정하면 더 이상 `onOpen` 이벤트가 발생하지 않으며, 응용 코드에서 스스로 처리해야 합니다. 사용자는 `$server->defer`를 사용하여 `onOpen` 로직을 호출할 수 있습니다.

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // 핸드셋팅 내용을 생략합니다.
    $response->status(101);
    $response->end();

    global $server;
    $fd = $request->fd;
    $server->defer(function () use ($fd, $server)
    {
      echo "클라이언트 연결\n";
      $server->push($fd, "안녕하세요, 환영합니다.\n");
    });
});
```


### onOpen

?> **`WebSocket` 클라이언트와 서버가 연결을 맺고 핸드셋팅을 완료하면 이 함수가 콜백됩니다.**

```php
onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request);
```

* **알림**

    * `$request`는 [HTTP](/http_server?id=httprequest) 요청 객체로, 클라이언트가 보낸 핸드셋팅 요청 정보를 포함하고 있습니다.
    * `onOpen` 이벤트 함수에서는 [push](/websocket_server?id=push)를 사용하여 클라이언트에 데이터를 보낼 수 있거나 [close](/server/methods?id=close)를 사용하여 연결을 닫을 수 있습니다.
    * `onOpen` 이벤트 콜백은 선택적입니다.


### onMessage

?> **서버가 클라이언트에서 온 데이터 프레임을 받았을 때 이 함수가 콜백됩니다.**

```php
onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
```

* **알림**

  * `$frame`는 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) 객체로, 클라이언트가 보낸 데이터 프레임 정보를 포함하고 있습니다.
  * `onMessage` 콜백은 반드시 설정되어야 하며, 설정하지 않으면 서버가 시작될 수 없습니다.
  * 클라이언트가 보낸 `ping` 프레임은 `onMessage`을 트리거하지 않으며, 하단에서 자동으로 `pong` 패킷을 응답합니다. 또한 [open_websocket_ping_frame](/websocket_server?id=open_websocket_ping_frame) 매개변수를 설정하여 수동으로 처리할 수 있습니다.

!> `$frame->data`가 텍스트 유형인 경우, 인코딩 형식은 반드시 `UTF-8`이어야 합니다. 이는 `WebSocket` 프로토콜이 규정한 것입니다.
### onRequest

?> `Swoole\WebSocket\Server`는 [Swoole\Http\Server](/http_server)를 상속하기 때문에 `Http\Server`가 제공하는 모든 `API`와 설정 항목을 사용할 수 있습니다. 자세한 내용은 [Swoole\Http\Server](/http_server) 문서를 참고하세요.

* [onRequest](/http_server?id=on) 콜백이 설정되어 있다면 `WebSocket\Server`는 동시에 `HTTP` 서버로 작동할 수 있습니다.
* [onRequest](/http_server?id=on) 콜백이 설정되어 있지 않다면 `WebSocket\Server`는 `HTTP` 요청을 받았을 때 `HTTP 400` 오류 페이지를 반환합니다.
* `HTTP` 요청을 통해 모든 `WebSocket` 푸시를 트리거하고 싶다면, 범위 문제에 주의해야 합니다. 프로세스 전용에서는 `global` 키워드를 사용하여 `Swoole\WebSocket\Server`에 참조하고, 객체 지향에서는 `Swoole\WebSocket\Server`를 멤버 속성으로 설정할 수 있습니다.

#### 프로세스 스타일 코드

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});
$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    global $server;// 외부의 server를 호출합니다.
    // $server->connections을 통해 모든 websocket 연결의 사용자 fd를 반복하고, 모든 사용자에게 푸시합니다.
    foreach ($server->connections as $fd) {
        // 올바른 websocket 연결인지 먼저 확인해야 합니다. 그렇지 않으면 푸시가 실패할 수 있습니다.
        if ($server->isEstablished($fd)) {
            $server->push($fd, $request->get['message']);
        }
    }
});
$server->start();
```

#### 객체 지향 스타일 코드

```php
class WebSocketServer
{
    public $server;

    public function __construct()
    {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
        $this->server->on('open', function (Swoole\WebSocket\Server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // HTTP 요청을 받았을 때 get에서 message 매개변수의 값을 가져와 사용자에게 푸시합니다.
            // $this->server->connections을 통해 모든 websocket 연결의 사용자 fd를 반복하고, 모든 사용자에게 푸시합니다.
            foreach ($this->server->connections as $fd) {
                // 올바른 websocket 연결인지 먼저 확인해야 합니다. 그렇지 않으면 푸시가 실패할 수 있습니다.
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        $this->server->start();
    }
}

new WebSocketServer();
```

### onDisconnect

?> **WebSocket 연결이 닫힐 때만 이 이벤트가 발동합니다.**

!> Swoole 버전 >= `v4.7.0`에서 사용할 수 있습니다.

```php
onDisconnect(Swoole\WebSocket\Server $server, int $fd)
```

!> `onDisconnect` 이벤트 콜백이 설정되어 있다면, 비 WebSocket 요청이나 [onRequest](/websocket_server?id=onrequest)에서 `$response->close()` 메서드를 호출하면 콜백이 발동합니다. 반면에 [onRequest](/websocket_server?id=onrequest) 이벤트에서 정상적으로 처리되어 종료되면 `onClose`나 `onDisconnect` 이벤트는 호출되지 않습니다.  

## 방법

`Swoole\WebSocket\Server`는 [Swoole\Server](/server/methods)의 자식으로, 따라서 `Server`의 모든 방법을 사용할 수 있습니다.

주의해야 할 점은 `WebSocket` 서버가 클라이언트에게 데이터를 보낼 때는 `Swoole\WebSocket\Server::push` 메서드를 사용해야 하며, 이 메서드는 `WebSocket` 프로토콜을 포장합니다. 반면에 [Swoole\Server->send()](/server/methods?id=send) 메서드는 원시 `TCP` 전송 인터페이스입니다.

[Swoole\WebSocket\Server->disconnect()](/websocket_server?id=disconnect) 메서드는 서버에서 `WebSocket` 연결을 적극적으로 닫을 수 있으며, 닫기 상태 코드(closed frame status code)와 닫기 이유를 지정할 수 있습니다(UTF-8 인코딩의 문자열로, 길이가 125字节 미만이어야 합니다). 지정되지 않은 경우 상태 코드는 `1000`이며, 닫기 이유는 비어 있습니다.

### push

?> **`WebSocket` 클라이언트 연결에 데이터를 푸시할 때, 최대 길이는 `2M`을 초과할 수 없습니다.**

```php
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool

// v4.4.12 버전부터는 flags 매개변수가 추가되었습니다.
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): bool
```

* **매개변수** 

  * **`int $fd`**

    * **기능**: 클라이언트 연결의 `ID` 【지정된 `$fd`가 해당하는 `TCP` 연결이 `WebSocket` 클라이언트가 아니라면, 전송에 실패합니다】
    * **기본값**: 없음
    * **기타 값**: 없음

  * **`Swoole\WebSocket\Frame|string $data`**

    * **기능**: 전송할 데이터 내용
    * **기본값**: 없음
    * **기타 값**: 없음

  !> Swoole 버전 >= v4.2.0에서 전달된 `$data`가 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) 객체인 경우, 이후의 매개변수는 무시됩니다.

  * **`int $opcode`**

    * **기능**: 전송 데이터 내용의 형식을 지정합니다 【기본적으로 텍스트입니다. 이진 내용을 전송하려면 `$opcode` 매개변수를 `WEBSOCKET_OPCODE_BINARY`로 설정해야 합니다】
    * **기본값**: `WEBSOCKET_OPCODE_TEXT`
    * **기타 값**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **기능**: 전송이 완료되는지를 나타냅니다
    * **기본값**: `true`
    * **기타 값**: `false`

* **반환값**

  * 성공 시 `true`를, 실패 시 `false`를 반환합니다

!> v4.4.12 버전부터, `finish` 매개변수(bool型)는 `flags` 매개변수(int型)로 변경되어 `WebSocket` 압축을 지원하게 되었습니다. `finish`은 `SWOOLE_WEBSOCKET_FLAG_FIN` 값으로 `1`로 설정되며, 기존의 bool型 값은隐式적으로 int型으로 변환됩니다. 이 변경은 하향 호환에 영향을 주지 않습니다. 또한 압축 `flag`은 `SWOOLE_WEBSOCKET_FLAG_COMPRESS`입니다.

!> [BASE 모드](/learn?id=base 모드의 제한: )는 프로세스 간에 `push`를 사용하여 데이터를 전송하는 것을 지원하지 않습니다.


### exist

?> **`WebSocket` 클라이언트가 존재하고 상태가 `Active`인지를 확인합니다.**

!> v4.3.0 이후로, 이 `API`는 연결이 존재하는지를 확인하기 위해만 사용됩니다. `WebSocket` 연결인지 확인하려면 `isEstablished` 메서드를 사용하세요.

```php
Swoole\WebSocket\Server->exist(int $fd): bool
```

* **반환값**

  * 연결이 존재하고 `WebSocket` 핸드셋이 완료된 경우 `true`를 반환합니다.
  * 연결이 존재하지 않거나 핸드셋이 완료되지 않은 경우 `false`를 반환합니다.
### 패키지

?> **WebSocket 메시지를 패키징합니다.**

```php
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true, bool $mask = false): string

// v4.4.12 버전부터는 flags 매개변수가 변경되었습니다.
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string

Swoole\WebSocket\Frame::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
```

* **매개변수** 

  * **`Swoole\WebSocket\Frame|string $data $data`**

    * **기능**：메시지 내용
    * **기본값**：없음
    * **기타값**：없음

  * **`int $opcode`**

    * **기능**：보낼 데이터 내용의 형식을 지정합니다. 【기본은 텍스트입니다. 이진 내용을 보내면 `$opcode` 매개변수를 `WEBSOCKET_OPCODE_BINARY`로 설정해야 합니다】
    * **기본값**：`WEBSOCKET_OPCODE_TEXT`
    * **기타값**：`WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **기능**：프레임이 완료되었는지 여부
    * **기본값**：없음
    * **기타값**：없음

    !> `v4.4.12` 버전부터 `finish` 매개변수(bool 유형)는 `flags` 매개변수(int 유형)로 변경되어 `WebSocket` 압축을 지원하게 되었습니다. `finish`은 `SWOOLE_WEBSOCKET_FLAG_FIN` 값이 `1`일 때 해당합니다. 기존의 bool 유형의 값은 int 유형으로 암시적으로 변환되며, 이 변경은 하향 호환에 영향을 주지 않습니다.

  * **`bool $mask`**

    * **기능**：마스크를 설정할지 여부 【`v4.4.12`부터 이 매개변수를 제거했습니다】
    * **기본값**：없음
    * **기타값**：없음

* **귀속값**

  * 패키진된 `WebSocket` 데이터 패킷을 반환하며, 이를 `Swoole\Server`의 [send()](/server/methods?id=send) 메서드를 통해 상대방에게 보낼 수 있습니다.

* **예시**

```php
$ws = new Swoole\Server('127.0.0.1', 9501 , SWOOLE_BASE);

$ws->set(array(
    'log_file' => '/dev/null'
));

$ws->on('WorkerStart', function (\Swoole\Server $serv) {
});

$ws->on('receive', function ($serv, $fd, $threadId, $data) {
    $sendData = "HTTP/1.1 101 Switching Protocols\r\n";
    $sendData .= "Upgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: IFpdKwYy9wdo4gTldFLHFh3xQE0=\r\n";
    $sendData .= "Sec-WebSocket-Version: 13\r\nServer: swoole-http-server\r\n\r\n";
    $sendData .= Swoole\WebSocket\Server::pack("hello world\n");
    $serv->send($fd, $sendData);
});

$ws->start();
```


### 언패키지

?> **WebSocket 데이터 프레임을 해석합니다.**

```php
Swoole\WebSocket\Server::unpack(string $data): Swoole\WebSocket\Frame|false;
```

* **매개변수** 

  * **`string $data`**

    * **기능**：메시지 내용
    * **기본값**：없음
    * **기타값**：없음

* **귀속값**

  * 해석에 실패하면 `false`를 반환하고, 해석에 성공하면 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) 객체를 반환합니다.


### 연결 해제

?> **액티브적으로 WebSocket 클라이언트에게 닫는 프레임을 보내고 해당 연결을 종료합니다.**

!> Swoole 버전 >= `v4.0.3`에서 사용할 수 있습니다.

```php
Swoole\WebSocket\Server->disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
```

* **매개변수** 

  * **`int $fd`**

    * **기능**：클라이언트 연결의 `ID` 【指定된 `$fd`가 해당하는 `TCP` 연결이 `WebSocket` 클라이언트가 아니라면, 전송에 실패합니다】
    * **기본값**：없음
    * **기타값**：없음

  * **`int $code`**

    * **기능**：연결 종료 상태 코드 【RFC6455에 따르면, 응용 프로그램이 연결을 종료할 때 상태 코드는 `1000` 또는 `4000-4999` 사이입니다】
    * **기본값**：`SWOOLE_WEBSOCKET_CLOSE_NORMAL`
    * **기타값**：없음

  * **`string $reason`**

    * **기능**：연결 종료 이유 【`utf-8` 형식의 문자열로, 바이트 길이가 `125`을 초과하지 않습니다】
    * **기본값**：없음
    * **기타값**：없음

* **귀속값**

  * 성공적으로 전송되면 `true`를 반환하고, 전송에 실패하거나 상태 코드가 잘못되면 `false`를 반환합니다.


### 연결 확인

?> **해당 연결이 유효한 WebSocket 클라이언트 연결인지 확인합니다.**

?> 이 함수는 `exist` 메서드와 다릅니다. `exist` 메서드는 `TCP` 연결만 확인할 수 있으며, 완료된 핸드셋을 가진 WebSocket 클라이언트인지 확인할 수 없습니다.

```php
Swoole\WebSocket\Server->isEstablished(int $fd): bool
```

* **매개변수** 

  * **`int $fd`**

    * **기능**：클라이언트 연결의 `ID` 【指定된 `$fd`가 해당하는 `TCP` 연결이 `WebSocket` 클라이언트가 아니라면, 전송에 실패합니다】
    * **기본값**：없음
    * **기타값**：없음

* **귀속값**

  * 유효한 연결이면 `true`를 반환하고, 그렇지 않으면 `false`를 반환합니다.


## WebSocket 데이터 프레임 프레임 클래스


### Swoole\WebSocket\Frame

?> `v4.2.0` 버전에서, 서버와 클라이언트가 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) 객체를 보낼 수 있는 지원이 추가되었습니다.  
`v4.4.12` 버전에서, WebSocket 압축 프레임을 지원하기 위해 `flags` 속성이 추가되었으며, 새로운 서브클래스 [Swoole\WebSocket\CloseFrame](/websocket_server?id=swoolewebsocketcloseframe)이 추가되었습니다.

일반적인 `frame` 객체는 다음과 같은 속성을 가지고 있습니다.


상수 | 설명 
---|--- 
fd |  클라이언트의 `socket id`, `$server->push`를 사용하여 데이터를 푸시할 때 필요합니다    
data | 데이터 내용, 텍스트 내용이든 이진 데이터이든 가능하며, `opcode`의 값을 통해 판단할 수 있습니다   
opcode | `WebSocket`의 [데이터 프레임 유형](/websocket_server?id=데이터프레임유형), `WebSocket` 프로토콜 표준 문서를 참고할 수 있습니다    
finish | 데이터 프레임이 완료되었는지 여부를 나타내며, `WebSocket` 요청은 여러 개의 데이터 프레임으로 나뉘어 전송될 수 있습니다(밑바닥에서 자동으로 데이터 프레임을 합병하여, 이제 받았던 데이터 프레임이 불완전한 것을 걱정할 필요가 없습니다)  

이 클래스는 자체적으로 [Swoole\WebSocket\Frame::pack()](/websocket_server?id=pack)와 [Swoole\WebSocket\Frame::unpack()](/websocket_server?id=unpack)를 가지고 있어, websocket 메시지를 패키징하고 압축하는 데 사용할 수 있으며, 매개변수 설명은 `Swoole\WebSocket\Server::pack()` 및 `Swoole\WebSocket\Server::unpack()`와 일치합니다.


### Swoole\WebSocket\CloseFrame

일반적인 `클로 프레임 close frame` 객체는 다음과 같은 속성을 가지고 있습니다.


상수 | 설명 
---|--- 
opcode |  `WebSocket`의 [데이터 프레임 유형](/websocket_server?id=데이터프레임유형), `WebSocket` 프로토콜 표준 문서를 참고할 수 있습니다    
code | `WebSocket`의 [클로 프레임 상태 코드](/websocket_server?id=WebSocket종료상태코드), [websocket 프로토콜에서 정의한 오류 코드](https://developer.mozilla.org/ko-KR/docs/Web/API/CloseEvent)를 참고할 수 있습니다    
reason |  클로 이유, 명확히 지정되지 않았다면 비어 있습니다.

서버가 `클로 프레임`을 수신해야 할 경우, `$server->set`를 통해 [open_websocket_close_frame](/websocket_server?id=open_websocket_close_frame) 매개변수를 활성화해야 합니다.


## 상수


### 데이터 프레임 유형


상수 | 해당값 | 설명
---|---|---
WEBSOCKET_OPCODE_TEXT | 0x1 | UTF-8 텍스트 문자 데이터
WEBSOCKET_OPCODE_BINARY | 0x2 | 이진 데이터
WEBSOCKET_OPCODE_CLOSE | 0x8 | 클로 프레임 유형 데이터
WEBSOCKET_OPCODE_PING | 0x9 | 핑 고유 메시지 유형 데이터
WEBSOCKET_OPCODE_PONG | 0xa | 펑 고유 메시지 유형 데이터

### 연결 상태


상수 | 해당 값 | 설명
---|---|---
WEBSOCKET_STATUS_CONNECTION | 1 | 연결이 수동 Handshake 대기 중
WEBSOCKET_STATUS_HANDSHAKE | 2 | Handshake 중
WEBSOCKET_STATUS_ACTIVE | 3 | Handshake 성공 후 브라우저에서 데이터 프레임을 보내기 대기 중
WEBSOCKET_STATUS_CLOSING | 4 | 연결이 닫는 Handshake 중이며 곧 닫힐 예정


### WebSocket 닫기 프레임 상태 코드


상수 | 해당 값 | 설명
---|---|---
WEBSOCKET_CLOSE_NORMAL | 1000 | 정상적으로 닫힐 수 있는 연결, 이 링크는 작업을 완료하였습니다.
WEBSOCKET_CLOSE_GOING_AWAY | 1001 | 서버에서 연결을 끊으셨습니다.
WEBSOCKET_CLOSE_PROTOCOL_ERROR | 1002 | 프로토콜 오류로 연결이 중단되었습니다.
WEBSOCKET_CLOSE_DATA_ERROR | 1003 | 데이터 오류로, 예를 들어 텍스트 데이터가 필요하지만 이진 데이터를 받았습니다.
WEBSOCKET_CLOSE_STATUS_ERROR | 1005 | 예상된 상태 코드를 받지 못했습니다.
WEBSOCKET_CLOSE_ABNORMAL | 1006 | 닫기 프레임을 전송하지 않았습니다.
WEBSOCKET_CLOSE_MESSAGE_ERROR | 1007 | 형식이 맞지 않는 데이터를 받았기 때문에 연결이 끊겼습니다. (예: 텍스트 메시지에는 UTF-8이 아닐 수 있는 데이터가 포함되어 있습니다.)
WEBSOCKET_CLOSE_POLICY_ERROR | 1008 | 약속에 부합하지 않는 데이터를 받았기 때문에 연결이 끊겼습니다. 이는 일반적인 상태 코드로, 1003과 1009 상태 코드가 적합하지 않은 경우에 사용됩니다.
WEBSOCKET_CLOSE_MESSAGE_TOO_BIG | 1009 | 너무 큰 데이터 프레임을 받았기 때문에 연결이 끊겼습니다.
WEBSOCKET_CLOSE_EXTENSION_MISSING | 1010 | 클라이언트는 서버와 하나 이상의 확장을 협약하기를 기대했지만, 서버가 이를 처리하지 않아 클라이언트가 연결을 끊었습니다.
WEBSOCKET_CLOSE_SERVER_ERROR | 1011 | 클라이언트가 예상치 못한 상황에 의해 요청을 완료하지 못하게 되어 서버가 연결을 끊었습니다.
WEBSOCKET_CLOSE_TLS | 1015 | 예약. TLS Handshake를 완료할 수 없어 연결이 닫혔다는 것을 나타냅니다. (예: 서버 인증서를 검증할 수 없었습니다.)


## 옵션

?> `Swoole\WebSocket\Server`는 `Server`의 서브클래스로, [Swoole\WebSocker\Server::set()](/server/methods?id=set) 메서드를 사용하여 설정 옵션을 전달하고 일부 매개변수를 설정할 수 있습니다.


### websocket_subprotocol

?> **WebSocket 서브 프로토콜을 설정합니다.**

?> 설정 시 Handshake 응답의 HTTP 헤더에 `Sec-WebSocket-Protocol: {$websocket_subprotocol}`가 추가됩니다. 구체적인 사용 방법은 WebSocket 프로토콜 관련 RFC 문서를 참고하세요.

```php
$server->set([
    'websocket_subprotocol' => 'chat',
]);
```


### open_websocket_close_frame

?> **WebSocket 프로토콜의 닫기 프레임(opcode가 `0x08`인 프레임)을 `onMessage` 콜백에서 수신하도록 설정합니다. 기본값은 `false`입니다.**

?>开启了之后，可以在`Swoole\WebSocket\Server`的`onMessage`回调中接收到客户端或服务端发送的关闭帧，开发者可自行对其进行处理。

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_close_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_ping_frame

?> **WebSocket 프로토콜의 Ping 프레임(opcode가 `0x09`인 프레임)을 `onMessage` 콜백에서 수신하도록 설정합니다. 기본값은 `false`입니다.**

?>开启了之后，可以在`Swoole\WebSocket\Server`的`onMessage`回调中接收到客户端或服务端发送的Ping帧，开发者可自行对其进行处理。

!> Swoole版本 >= `v4.5.4` 可用

```php
$server->set([
    'open_websocket_ping_frame' => true,
]);
```

!> 值为`false`时底层会自动回复`Pong`帧，但如果设为`true`后则需要开发者自行回复`Pong`帧。

* **示例**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_ping_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x09) {
        echo "Ping frame received: Code {$frame->opcode}\n";
        // 回复 Pong 帧
        $pongFrame = new Swoole\WebSocket\Frame;
        $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
        $server->push($frame->fd, $pongFrame);
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_pong_frame

?> **WebSocket 프로토콜의 Pong 프레임(opcode가 `0x0A`인 프레임)을 `onMessage` 콜백에서 수신하도록 설정합니다. 기본값은 `false`입니다.**

?>开启了之后，可以在`Swoole\WebSocket\Server`的`onMessage`回调中接收到客户端或服务端发送的Pong帧，开发者可自行对其进行处理。

!> Swoole版本 >= `v4.5.4` 可用

```php
$server->set([
    'open_websocket_pong_frame' => true,
]);
```

* **示例**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_pong_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0xa) {
        echo "Pong frame received: Code {$frame->opcode}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### websocket_compression

?> **데이터 압축을 활성화합니다.**

?> `true`로 설정 시 프레임에 대해 `zlib` 압축을 허용합니다. 압축이 가능할지는 클라이언트가 압축을 처리할 수 있는지 여부에 따라 결정됩니다. (Handshakes 정보를 참고하여, RFC-7692 참조) 특정 프레임에 대한 압축을 실제로 적용하려면 `flags` 매개변수에 `SWOOLE_WEBSOCKET_FLAG_COMPRESS`를 설정해야 합니다. 구체적인 사용 방법은 이 절의 [WebSocket 프레임 압축 (RFC-7692)](/websocket_server?id=websocket帧压缩-（rfc-7692）)를 참고하세요.

!> Swoole版本 >= `v4.4.12` 可用


## 기타

!> 관련 예제 코드는 [WebSocket 단위 테스트](https://github.com/swoole/swoole-src/tree/master/tests/swoole_websocket_server)에서 확인할 수 있습니다.


### WebSocket 프레임 압축 (RFC-7692)

?> 먼저, 압축을 활성화하기 위해 `'websocket_compression' => true`를 설정해야 합니다. (WebSocket Handshake 시 상대방과 압축 지원 정보를 교환합니다.) 이후에는 `flag SWOOLE_WEBSOCKET_FLAG_COMPRESS`를 사용하여 특정 프레임에 압축을 적용할 수 있습니다.

#### 예제

* **서버**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->set(['websocket_compression' => true]);
$server->on('message', function (Server $server, Frame $frame) {
    $server->push(
        $frame->fd,
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
    // $server->push($frame->fd, $frame); // 或者 服务端可以直接原封不动转发客户端的帧对象
});
$server->start();
```

* **클라이언트**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->set(['websocket_compression' => true]);
    $cli->upgrade('/');
    $cli->push(
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
});
```
### Ping 프레임 전송

?> WebSocket는 장기 연결이기 때문에 일정 시간 동안 통신이 없으면 연결이 끊힐 수 있습니다. 이때는 핫스탠딩 메커니즘이 필요합니다. WebSocket 프로토콜은 Ping과 Pong 두 가지 프레임을 포함하고 있어 정기적으로 Ping 프레임을 전송하여 장기 연결을 유지할 수 있습니다.

#### 예제

* **서버**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->on('message', function (Server $server, Frame $frame) {
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    $server->push($frame->fd, $pingFrame);
});
$server->start();
```

* **클라이언트**

```php
use Swoole\WebSocket\Frame;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->upgrade('/');
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    // PING 전송
    $cli->push($pingFrame);
    
    // PONG 수신
    $pongFrame = $cli->recv();
    var_dump($pongFrame->opcode === WEBSOCKET_OPCODE_PONG);
});
```
