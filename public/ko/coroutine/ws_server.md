# WebSocket 서버

?> 완전히 코루틴 기반의 WebSocket 서버 구현으로, [코루틴/HTTP/서버](/coroutine/http_server)를 상속합니다. 하단에는 `WebSocket` 프로토콜에 대한 지원이 제공되며, 여기서 더 이상 자세히 설명하지 않겠습니다. 차이점만 언급하겠습니다.

!> 이 장은 v4.4.13 이후에 사용 가능합니다.


## 전체 예제

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    $ws->close();
                    break;
                }
                $ws->push("Hello {$frame->data}!");
                $ws->push("How are you, {$frame->data}?");
            }
        }
    });

    $server->handle('/', function (Request $request, Response $response) {
        $response->end(<<<HTML
    <h1>Swoole WebSocket Server</h1>
    <script>
var wsServer = 'ws://127.0.0.1:9502/websocket';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("Connected to WebSocket server.");
    websocket.send('hello');
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
</script>
HTML
        );
    });

    $server->start();
});
```


### 대량 전송 예제

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        global $wsObjects;
        $objectId = spl_object_id($ws);
        $wsObjects[$objectId] = $ws;
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                unset($wsObjects[$objectId]);
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    unset($wsObjects[$objectId]);
                    $ws->close();
                    break;
                }
                foreach ($wsObjects as $obj) {
                    $obj->push("Server：{$frame->data}");
                }
            }
        }
    });
    $server->start();
});
```


## 처리 흐름

* `$ws->upgrade()`：클라이언트에게 `WebSocket` 핸드셋팅 메시지를 보냅니다.
* `while(true)` 루프로 메시지 수신 및 전송을 처리합니다.
* `$ws->recv()` `WebSocket` 메시지 프레임을 수신합니다.
* `$ws->push()` 대상을 향해 데이터 프레임을 보냅니다.
* `$ws->close()` 연결을 닫습니다.

!> `$ws`는 `Swoole\Http\Response` 객체이며, 각각의 방법에 대한 사용법은 아래 문서를 참고하세요.


## 방법


### upgrade()

`WebSocket` 핸드셋팅 성공 메시지를 보냅니다.

!> 이 방법은 [비동기 스타일](/http_server)의 서버에서는 사용할 수 없습니다.

```php
Swoole\Http\Response->upgrade(): bool
```


### recv()

`WebSocket` 메시지를 수신합니다.

!> 이 방법은 [비동기 스타일](/http_server)의 서버에서는 사용할 수 없습니다. `recv` 메서드를 호출하면 현재 코루틴이 [일시정지](/coroutine?id=协程调度)되어 데이터가 도착할 때까지 코루틴 실행을 기다립니다.

```php
Swoole\Http\Response->recv(float $timeout = 0): Swoole\WebSocket\Frame | false | string
```

* **반환값**

  * 메시지를 성공적으로 수신하면 `Swoole\WebSocket\Frame` 객체를 반환합니다. 자세한 내용은 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)를 참고하세요.
  * 실패하면 `false`를 반환하며, [swoole_last_error()](/functions?id=swoole_last_error)를 사용하여 오류 코드를 확인할 수 있습니다.
  * 연결이 닫혔을 경우 공백 문자열을 반환합니다.
  * 반환값 처리는 [대량 전송 예제](/coroutine/ws_server?id=群发示例)를 참고하세요.


### push()

`WebSocket` 데이터 프레임을 보냅니다.

!> 이 방법은 [비동기 스타일](/http_server)의 서버에서는 사용할 수 없습니다. 대량의 데이터 패킷을 전송할 때는 작성 가능 상태를 감시해야 하므로, 이로 인해 여러 번의 [코루틴 교체](/coroutine?id=协程调度)이 발생할 수 있습니다.

```php
Swoole\Http\Response->push(string|object $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

* **매개변수** 

  !> `$data`가 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) 객체인 경우, 이후의 매개변수는 무시됩니다. 다양한 프레임 유형을 전송할 수 있습니다.

  * **`string|object $data`**

    * **기능** : 전송할 내용
    * **기본값** : 없음
    * **기타값** : 없음

  * **`int $opcode`**

    * **기능** : 전송하는 데이터의 형식을 지정합니다. 【기본적으로 텍스트입니다. 이진 내용을 전송하려면 `$opcode` 매개변수를 `WEBSOCKET_OPCODE_BINARY`로 설정해야 합니다】
    * **기본값** : `WEBSOCKET_OPCODE_TEXT`
    * **기타값** : `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **기능** : 전송이 완료되는지를 지정합니다.
    * **기본값** : `true`
    * **기타값** : `false`

### close()

`WebSocket` 연결을 닫습니다.

!> 이 방법은 [비동기 스타일](/http_server)의 서버에서는 사용할 수 없습니다. v4.4.15 이전 버전에서는 `Warning`을 잘못 보고 무시하면 됩니다.

```php
Swoole\Http\Response->close(): bool
```

이 방법은 직접적으로 `TCP` 연결을 끊어버리며, `Close` 프레임을 보내지 않습니다. 이는 `WebSocket\Server::disconnect()` 방법과 다릅니다.
연결을 닫기 전에 `$push()` 메서드를 사용하여 `Close` 프레임을 보내 클라이언트에 명시적으로 알릴 수 있습니다.

```php
$frame = new Swoole\WebSocket\CloseFrame;
$frame->reason = 'close';
$ws->push($frame);
$ws->close();
```
