# WebSocket 서버


## 프로그램 코드

다음 코드를 websocketServer.php에 작성하세요.

```php
//WebSocket 서버 객체를 생성하여 0.0.0.0:9502 포트를 감시합니다.
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

//WebSocket 연결 열기 이벤트를 감시합니다.
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

//WebSocket 메시지 이벤트를 감시합니다.
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

//WebSocket 연결 닫기 이벤트를 감시합니다.
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
```

* 클라이언트가 서버에 메시지를 보낼 때, 서버는 `onMessage` 이벤트 콜백이 트리거됩니다.
* 서버는 `$server->push()`를 호출하여 특정 클라이언트( `$fd` 식별자로 식별)에 메시지를 보낼 수 있습니다.


## 프로그램 실행

```shell
php websocketServer.php
```

Chrome 브라우저를 사용하여 테스트할 수 있습니다. JS 코드는 다음과 같습니다:

```javascript
var wsServer = 'ws://127.0.0.1:9502';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
	console.log("Connected to WebSocket server.");
};

websocket.onclose = function (evt) {
	console.log("Disconnected");
};

websocket.onmessage = function (evt) {
	console.log('Retrieved data from server: ' + evt.data);
};

websocket.onerror = function (evt, e) {
	console.log('Error occured: ' + evt.data);
};
```

## Comet

WebSocket 서버는 WebSocket 기능 외에도 실제로 HTTP 장기 연결을 처리할 수 있습니다. [onRequest](/http_server?id=on) 이벤트 감시를 추가하기만 하면 Comet 방식의 HTTP 장기 Polling을 구현할 수 있습니다.

!> 자세한 사용 방법은 [Swoole\WebSocket](/websocket_server)를 참고하세요.
