# WebSocket Server

?> A fully coroutine-based WebSocket server implementation, inheriting from [Coroutine\Http\Server](/coroutine/http_server). It provides underlying support for the `WebSocket` protocol, without going into details here, just highlighting the differences.

!> This section is available after v4.4.13.
## Complete Example

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
### Batch Send Example

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
                    $obj->push("Server: {$frame->data}");
                }
            }
        }
    });
    $server->start();
});
```
## Processing Flow

* `$ws->upgrade()`: Send a WebSocket handshake message to the client
* Use a `while(true)` loop to handle message receiving and sending
* `$ws->recv()`: Receive WebSocket message frames
* `$ws->push()`: Send data frames to the peer
* `$ws->close()`: Close the connection

!> `$ws` is an object of type `Swoole\Http\Response`. Refer to the following section for specific usage methods of each function.
## Methods
### upgrade()

Sends a successful `WebSocket` handshake message.

!> Do not use this method in servers that follow an [asynchronous style](/http_server).

```php
Swoole\Http\Response->upgrade(): bool
```
### recv()

Receives a `WebSocket` message.

!> Do not use this method in [asynchronous style](/http_server) servers. Calling the `recv` method will [suspend](/coroutine?id=Coroutine_Scheduling) the current coroutine, waiting to resume the coroutine's execution when data arrives.

```php
Swoole\Http\Response->recv(float $timeout = 0): Swoole\WebSocket\Frame | false | string
```

* **Return Value**

  * If a message is successfully received, it returns a `Swoole\WebSocket\Frame` object. Refer to [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe).
  * If it fails, it returns `false`. Use [swoole_last_error()](/functions?id=swoole_last_error) to get the error code.
  * If the connection is closed, it returns an empty string.
  * For handling the return value, refer to [broadcast example](/coroutine/ws_server?id=Broadcast_Example).
### push()

Send a `WebSocket` data frame.

!> This method should not be used in [asynchronous style](/http_server) servers. When sending large data packets, listening for writeability is required, causing multiple [coroutine switches](/coroutine?id=coroutine-scheduling).

```php
Swoole\Http\Response->push(string|object $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

* **Parameters**

  !> If the `$data` passed in is a [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) object, subsequent parameters will be ignored, supporting the sending of various frame types.

  * **`string|object $data`**

    * **Functionality**: Content to be sent
    * **Default**: None
    * **Other values**: None

  * **`int $opcode`**

    * **Functionality**: Specifies the format of the data content to be sent. Default is text. To send binary content, the `$opcode` parameter needs to be set to `WEBSOCKET_OPCODE_BINARY`.
    * **Default**: `WEBSOCKET_OPCODE_TEXT`
    * **Other values**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Functionality**: Whether the sending is complete
    * **Default**: `true`
    * **Other values**: `false`
### close()

Close the `WebSocket` connection.

!> Do not use this method in servers with an [asynchronous style](/http_server). In versions before v4.4.15, there may be a `Warning`, which can be ignored.

```php
Swoole\Http\Response->close(): bool
```
