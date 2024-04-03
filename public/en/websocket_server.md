# Swoole\WebSocket\Server

Using the built-in `WebSocket` server support, you can write an asynchronous IO multi-process `WebSocket` server in just a few lines of `PHP` code.

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

* **Client**

  * `Chrome/Firefox/` and other high versions of `IE/Safari` browsers come with a built-in `WebSocket` client in the `JS` language.
  * `WebSocket` client built into the WeChat mini-program development framework.
  * In [asynchronous IO](/learn?id=同步io异步io) `PHP` programs, [Swoole\Coroutine\Http](/coroutine_client/http_client) can be used as a `WebSocket` client.
  * For `Apache/PHP-FPM` or other synchronous blocking `PHP` programs, you can use the [synchronized WebSocket client](https://github.com/matyhtf/framework/blob/master/libs/Swoole/Client/WebSocket.php) provided by `swoole/framework`.
  * Non-WebSocket clients cannot communicate with the WebSocket server.

* **How to determine if a connection is a WebSocket client**

To determine whether a connection is a WebSocket client or not, you can use the following example to get connection information and check the `websocket_status` field in the returned array.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    $client = $server->getClientInfo($frame->fd);
    // Alternatively, you can use $client = $server->connection_info($frame->fd);
    if (isset($client['websocket_status'])) {
        echo "It is a WebSocket connection";
    } else {
        echo "It is not a WebSocket connection";
    }
});
```
## Events

?> Besides receiving callback functions from the [Swoole\Server](/server/methods) and [Swoole\Http\Server](/http_server) basic classes, the `WebSocket` server adds four additional callback function settings. Among them:

* The `onMessage` callback function is required.
* The `onOpen`, `onHandShake`, and `onBeforeHandShakeResponse` (provided in Swoole 5) callback functions are optional.
### onBeforeHandshakeResponse

!> Available only for Swoole version >= `v5.0.0`

?> **Occurs before the WebSocket connection is established. If you do not need to customize the handshake process but want to set some `http header` information in the response header, you can call this event.**

```php
onBeforeHandshakeResponse(Swoole\Http\Request $request, Swoole\Http\Response $response);
```
### onHandShake

?> **After the `WebSocket` connection is established, a handshake is performed. The `WebSocket` server will automatically perform the handshake process. If the user wants to handle the handshake process by themselves, they can set the `onHandShake` event callback function.**

```php
onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

* **Tips**

  * The `onHandShake` event callback is optional.
  * After setting the `onHandShake` callback function, the `onOpen` event will not be triggered. Application code needs to handle this separately, and you can use `$server->defer` to call the `onOpen` logic.
  * In `onHandShake`, you must call [response->status()](/http_server?id=status) to set the status code to `101` and call [response->end()](/http_server?id=end) to respond. Otherwise, the handshake will fail.
  * The built-in handshake protocol is `Sec-WebSocket-Version: 13`, lower version browsers need to implement the handshake themselves.

* **Note**

!> If you need to handle the handshake by yourself, set this callback function only then. If you don't need to customize the handshake process, then do not set this callback and use `Swoole`'s default handshake. Below are the requirements for a "custom" handshake event callback function:

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // print_r( $request->header );
    // if (If certain custom requirements are not met, return end output, return false, handshake failed) {
    //    $response->end();
    //     return false;
    // }

    // WebSocket handshake connection algorithm verification
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $pattern = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($pattern, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
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

!> After setting the `onHandShake` callback function, the `onOpen` event will not be triggered. Application code needs to handle this separately, and you can use `$server->defer` to call the `onOpen` logic.

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // Omitted handshake content
    $response->status(101);
    $response->end();

    global $server;
    $fd = $request->fd;
    $server->defer(function () use ($fd, $server)
    {
      echo "Client connected\n";
      $server->push($fd, "hello, welcome\n");
    });
});
```
### onOpen

?> **This function will be called when the `WebSocket` client establishes a connection with the server and completes the handshake.**

```php
onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request);
```

* **Note**

    * `$request` is an [HTTP](/http_server?id=httprequest) request object, containing the handshake request information sent by the client.
    * Within the `onOpen` event function, you can use [push](/websocket_server?id=push) to send data to the client or [close](/server/methods?id=close) to close the connection.
    * The `onOpen` event callback is optional.
### onMessage

?> **This function will be called back when the server receives a data frame from the client.**

```php
onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
```

* **Note**

  * `$frame` is an object of [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), which contains information about the data frame sent by the client.
  * The `onMessage` callback must be set. If not set, the server will not be able to start.
  * Sending a `ping` frame from the client will not trigger `onMessage`. The underlying system will automatically reply with a `pong` frame. You can also set the [open_websocket_ping_frame](/websocket_server?id=open_websocket_ping_frame) parameter for manual handling.

!> If `$frame->data` is of text type, the encoding format must be `UTF-8`, as required by the WebSocket protocol.
### onRequest

The `Swoole\WebSocket\Server` inherits from [Swoole\Http\Server](/http_server), so all APIs and configuration options provided by `Http\Server` can be used. Please refer to the [Swoole\Http\Server](/http_server) section.

- If [onRequest](/http_server?id=on) callback is set, `WebSocket\Server` can also be used as an HTTP server.
- If [onRequest](/http_server?id=on) callback is not set, `WebSocket\Server` will return an HTTP 400 error page when receiving an HTTP request.
- If you want to trigger all WebSocket pushes by receiving an HTTP request, you need to pay attention to the scope issue. For procedural programming, use `global` to refer to `Swoole\WebSocket\Server`. For object-oriented programming, you can set `Swoole\WebSocket\Server` as a member attribute.
### Procedural Style Code

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
    global $server;//calling external server
    // $server->connections traverse all websocket connection users' fds, push to all users
    foreach ($server->connections as $fd) {
        // Need to check if it is a correct websocket connection, otherwise pushing may fail
        if ($server->isEstablished($fd)) {
            $server->push($fd, $request->get['message']);
        }
    }
});
$server->start();
```
#### Object-oriented style code

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
            // Receive HTTP request and get the value of 'message' from get, then push it to users
            // Loop through all websocket connections' fds and push to all users
            foreach ($this->server->connections as $fd) {
                // Need to check if it is a valid websocket connection to avoid push failure
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

?> **This event is only triggered when a non-WebSocket connection is closed.**

!> Available in Swoole version >= `v4.7.0`

```php
onDisconnect(Swoole\WebSocket\Server $server, int $fd)
```

!> If the `onDisconnect` event callback is set, it will be triggered for non-WebSocket requests or when the `$response->close()` method is called in [onRequest](/websocket_server?id=onrequest). However, in a normal completion within the [onRequest](/websocket_server?id=onrequest) event, `onClose` or `onDisconnect` events will not be called.
## Methods

`Swoole\WebSocket\Server` is a subclass of [Swoole\Server](/server/methods), so all methods of `Server` can be called.

It is important to note that when a `WebSocket` server sends data to a client, the `Swoole\WebSocket\Server::push` method should be used, as this method will package the data according to the `WebSocket` protocol. The [Swoole\Server->send()](/server/methods?id=send) method is the original `TCP` sending interface.

The [Swoole\WebSocket\Server->disconnect()](/websocket_server?id=disconnect) method can close a `WebSocket` connection from the server side. You can specify the [closing status code](/websocket_server?id=websocket-close-frame-status-codes) (according to the `WebSocket` protocol, valid status codes are an integer in decimal representation, with possible values of `1000` or `4000-4999`) and the closing reason (a string encoded in `utf-8` with a byte length not exceeding `125`). If not specified, the status code is `1000` and the reason is empty.
### push

?> **Push data to the WebSocket client connection, with a maximum length of `2M`.**

```php
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool

// Changed to use flags parameter in v4.4.12
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): bool
```

* **Parameters** 

  * **`int $fd`**

    * **Description**: ID of the client connection 【If the specified `$fd` does not correspond to a WebSocket client TCP connection, the push will fail】
    * **Default**: None
    * **Other values**: None

  * **`Swoole\WebSocket\Frame|string $data`**

    * **Description**: Data content to be sent
    * **Default**: None
    * **Other values**: None

  !> For Swoole version >= v4.2.0, if the `$data` passed in is a [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) object, its subsequent parameters will be ignored

  * **`int $opcode`**

    * **Description**: Specifies the format of the data to be sent 【Defaults to text. For sending binary content, set the `$opcode` parameter to `WEBSOCKET_OPCODE_BINARY`】
    * **Default**: `WEBSOCKET_OPCODE_TEXT`
    * **Other values**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Description**: Indicates whether the sending is complete
    * **Default**: `true`
    * **Other values**: `false`

* **Return Value**

  * Returns `true` on success, `false` on failure

!> Since version `v4.4.12`, the `finish` parameter (boolean) was changed to the `flags` parameter (integer) to support WebSocket compression. The `finish` corresponds to a value of `1` in `SWOOLE_WEBSOCKET_FLAG_FIN`, the original boolean value will be implicitly converted to an integer, this change is backwards compatible. Additionally, the compression flag is `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

!> [BASE mode](/learn?id=base模式的限制：) does not support cross-process `push` for sending data.
### exist

?> **Determine whether the `WebSocket` client exists and is in an `Active` state.**

!> Starting from `v4.3.0`, this `API` is only used to check if a connection exists. Please use `isEstablished` to determine if it is a `WebSocket` connection.

```php
Swoole\WebSocket\Server->exist(int $fd): bool
```

* **Return Value**

  * If the connection exists and the `WebSocket` handshake has been completed, it returns `true`.
  * If the connection does not exist or the handshake has not been completed, it returns `false`.
### pack

?> **Pack WebSocket messages.**

```php
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true, bool $mask = false): string

// Changed to flags parameter in version v4.4.12
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string

Swoole\WebSocket\Frame::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
```

* **Parameters** 

  * **`Swoole\WebSocket\Frame|string $data $data`**

    * **Function**：Message content
    * **Default value**：None
    * **Other values**：None

  * **`int $opcode`**

    * **Function**：Specify the format of the data being sent 【Default is text. To send binary content, set the `$opcode` parameter to `WEBSOCKET_OPCODE_BINARY`.】
    * **Default value**：`WEBSOCKET_OPCODE_TEXT`
    * **Other values**：`WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Function**：Whether the frame is finished
    * **Default value**：None
    * **Other values**：None

    !> Starting from version `v4.4.12`, the `$finish` parameter (boolean type) has been changed to the `$flags` parameter (integer type) to support WebSocket compression. The `finish` corresponds to the value `SWOOLE_WEBSOCKET_FLAG_FIN` as `1`. The original boolean value will be implicitly converted to an integer, and this change is backward compatible with no impact.

  * **`bool $mask`**

    * **Function**：Whether to set a mask【Removed in `v4.4.12`】
    * **Default value**：None
    * **Other values**：None

* **Return value**

  * Returns the packed WebSocket data packet, which can be sent to the opposite end using the [send()](/server/methods?id=send) method of the `Swoole\Server` base class.

* **Example**

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
### unpack

?> **Parse `WebSocket` data frames.**

```php
Swoole\WebSocket\Server::unpack(string $data): Swoole\WebSocket\Frame|false;
```

* **Parameters**

  * **`string $data`**

    * **Description**: message content
    * **Default value**: none
    * **Other values**: none

* **Return Value**

  * Returns `false` on failure, returns [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) object on successful parsing
### disconnect

?> **Send a close frame to the `WebSocket` client actively and close the connection.**

!> Available in Swoole version >= `v4.0.3`

```php
Swoole\WebSocket\Server->disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
```

* **Parameters**

  * **`int $fd`**

    * **Function**: Client connection ID 【If the specified `$fd` does not correspond to a WebSocket client's TCP connection, the disconnection attempt will fail】
    * **Default Value**: N/A
    * **Other Values**: N/A

  * **`int $code`**

    * **Function**: Status code for closing the connection 【According to `RFC6455`, the range of valid application-specific status codes is `1000` or between `4000` and `4999`】
    * **Default Value**: `SWOOLE_WEBSOCKET_CLOSE_NORMAL`
    * **Other Values**: N/A

  * **`string $reason`**

    * **Function**: Reason for closing the connection 【`utf-8` format string with a byte length not exceeding `125`】
    * **Default Value**: N/A
    * **Other Values**: N/A

* **Return Value**

  * Returns `true` on successful sending and `false` on failure or when the status code is invalid.
### isEstablished

?> **Check if the connection is a valid WebSocket client connection.**

?> This function is different from the `exist` method, where the `exist` method only determines if it is a TCP connection and cannot determine if it is a WebSocket client with completed handshake.

```php
Swoole\WebSocket\Server->isEstablished(int $fd): bool
```

* **Parameters** 

  * **`int $fd`**

    * **Function** : Client connection ID [if the specified `$fd` corresponds to a TCP connection that is not a WebSocket client, it will fail.]
    * **Default value** : None
    * **Other values** : None

* **Return value**

  * Returns `true` if it is a valid connection, otherwise returns `false`
## Websocket Frame Class
### Swoole\WebSocket\Frame

?> Support for sending [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) objects from the server and client was added in version `v4.2.0`.  
In version `v4.4.12`, the `flags` property was added to support `WebSocket` compression frames, along with a new subclass [Swoole\WebSocket\CloseFrame](/websocket_server?id=swoolewebsocketcloseframe).

A regular `frame` object has the following properties:

Constant | Description
---|---
fd | The `socket id` of the client, needed when pushing data with `$server->push`
data | Data content, which can be text or binary data, identified by the value of `opcode`
opcode | The [data frame type](/websocket_server?id=数据帧类型) in `WebSocket`, refer to the `WebSocket` protocol standard document
finish | Indicates if the data frame is complete; a `WebSocket` request might be sent in multiple data frames (automatic merging of data frames is already implemented at the lower level, so there is no need to worry about receiving incomplete data frames)

This class comes with [Swoole\WebSocket\Frame::pack()](/websocket_server?id=pack) and [Swoole\WebSocket\Frame::unpack()](/websocket_server?id=unpack) for packing and unpacking `WebSocket` messages, with the same parameter explanations as `Swoole\WebSocket\Server::pack()` and `Swoole\WebSocket\Server::unpack()`.
### Swoole\WebSocket\CloseFrame

An ordinary `close frame` object has the following properties

Constant | Description
---|---
opcode | The [data frame type](/websocket_server?id=Data_Frame_Types) of `WebSocket`, refer to the WebSocket protocol standards document
code | The [status code](/websocket_server?id=WebSocket_Disconnection_Status_Codes) of `WebSocket` close frame, refer to the error codes defined in the [WebSocket protocol](https://developer.mozilla.org/zh-CN/docs/Web/API/CloseEvent)
reason | The reason for the closure, empty if not explicitly specified

If the server needs to receive a `close frame`, the `open_websocket_close_frame` parameter needs to be enabled through `$server->set`.
## Constants
### Frame Types

Constants | Value | Description
---|---|---
WEBSOCKET_OPCODE_TEXT | 0x1 | UTF-8 encoded text data
WEBSOCKET_OPCODE_BINARY | 0x2 | binary data
WEBSOCKET_OPCODE_CLOSE | 0x8 | close frame type data
WEBSOCKET_OPCODE_PING | 0x9 | ping type data
WEBSOCKET_OPCODE_PONG | 0xa | pong type data
### Connection Status

Constant | Value | Description
---|---|---
WEBSOCKET_STATUS_CONNECTION | 1 | Connection entered handshake awaiting
WEBSOCKET_STATUS_HANDSHAKE | 2 | Handshake in progress
WEBSOCKET_STATUS_ACTIVE | 3 | Handshake successful, awaiting browser to send data frames
WEBSOCKET_STATUS_CLOSING | 4 | Connection closing handshake in progress, about to close
### WebSocket Close Frame Status Codes

Constant | Value | Description
---|---|---
WEBSOCKET_CLOSE_NORMAL | 1000 | Normal closure; the connection has been successfully completed.
WEBSOCKET_CLOSE_GOING_AWAY | 1001 | The server is terminating the connection.
WEBSOCKET_CLOSE_PROTOCOL_ERROR | 1002 | The connection is closed due to a protocol error.
WEBSOCKET_CLOSE_DATA_ERROR | 1003 | The connection is closed due to a data error, e.g., receiving non-text data when text data is expected.
WEBSOCKET_CLOSE_STATUS_ERROR | 1005 | Indicates that no status code was received.
WEBSOCKET_CLOSE_ABNORMAL | 1006 | The connection was closed unexpectedly, no closing frame was sent.
WEBSOCKET_CLOSE_MESSAGE_ERROR | 1007 | The connection is closed due to receiving messages in a format that is not consistent (e.g., non-UTF-8 data in text messages).
WEBSOCKET_CLOSE_POLICY_ERROR | 1008 | The connection is closed due to receiving data that does not conform to the agreed-upon protocol. This is a generic status code for scenarios that do not fit 1003 and 1009.
WEBSOCKET_CLOSE_MESSAGE_TOO_BIG | 1009 | The connection is closed due to receiving a too big data frame.
WEBSOCKET_CLOSE_EXTENSION_MISSING | 1010 | The client expects the server to establish one or more extensions, but the server did not comply, so the client closed the connection.
WEBSOCKET_CLOSE_SERVER_ERROR | 1011 | The server closed the connection because the client encountered an unexpected condition preventing it from fulfilling the request.
WEBSOCKET_CLOSE_TLS | 1015 | Reserved. Indicates that the connection closed because the TLS handshake could not be completed (e.g., unable to validate the server certificate).
## Options

?> `Swoole\WebSocket\Server` is a subclass of `Server`, which can use the [Swoole\WebSocker\Server::set()](/server/methods?id=set) method to pass configuration options and set certain parameters.
### websocket_subprotocol

?> **Set the `WebSocket` subprotocol.**

?> After setting, the `HTTP` header of the handshake response will include `Sec-WebSocket-Protocol: {$websocket_subprotocol}`. For specific usage, please refer to the `WebSocket` protocol-related `RFC` documents.

```php
$server->set([
    'websocket_subprotocol' => 'chat',
]);
```
### open_websocket_close_frame

?> **Enable the reception of close frames (frames with `opcode` of `0x08`) in the `onMessage` callback in the `WebSocket` protocol, default is `false`.**

?> When enabled, you can receive close frames sent by clients or servers in the `onMessage` callback of `Swoole\WebSocket\Server`, and developers can handle them accordingly.

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

?> **Enable to receive a `Ping` frame (frame with `opcode` of `0x09`) in the `onMessage` callback in `WebSocket` protocol, defaults to `false`.**

?> When enabled, the `Swoole\WebSocket\Server` can receive `Ping` frames sent by clients or servers in the `onMessage` callback, allowing developers to handle them accordingly.

!> Available in Swoole version >= `v4.5.4`

```php
$server->set([
    'open_websocket_ping_frame' => true,
]);
```

!> When the value is `false`, the underlying system automatically replies with a `Pong` frame. If set to `true`, developers need to manually reply with a `Pong` frame.

* **Example**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_ping_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x09) {
        echo "Ping frame received: Code {$frame->opcode}\n";
        // Reply with Pong frame
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

Enable receiving `Pong` frames (frames with `opcode` `0x0A`) in the `onMessage` callback of the `WebSocket` protocol, default is `false`.

When enabled, you can receive `Pong` frames sent by clients or servers in the `onMessage` callback of `Swoole\WebSocket\Server`, and developers can handle them on their own.

Swoole version >= `v4.5.4` is required.

```php
$server->set([
    'open_websocket_pong_frame' => true,
]);
```

* **Example**

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

Enable Data Compression

When set to `true`, it allows frames to be compressed using `zlib`. Whether compression can be performed depends on whether the client can handle compression (determined based on the handshake information, see `RFC-7692`). To actually compress a specific frame, you need to work with the `flags` parameter `SWOOLE_WEBSOCKET_FLAG_COMPRESS`. For specific usage methods, [refer to this section](/websocket_server?id=websocket-frame-compression-rfc-7692).

Swoole version >= `v4.4.12` is required.
## Others

!> Related sample code can be found in [WebSocket unit test](https://github.com/swoole/swoole-src/tree/master/tests/swoole_websocket_server)
### WebSocket Frame Compression (RFC-7692)

?> First, you need to configure `'websocket_compression' => true` to enable compression (when `WebSocket` handshake will exchange compression support information with the peer), then you can use `flag SWOOLE_WEBSOCKET_FLAG_COMPRESS` to compress a specific frame.
#### Example

* **Server Side**

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
    // $server->push($frame->fd, $frame); // Or the server can directly forward the client's frame object as it is
});
$server->start();
```

* **Client Side**

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
### Sending Ping Frames

?> Since WebSocket is a long-term connection, the connection may be disconnected if there is no communication for a certain period of time. In this case, a heartbeat mechanism is needed, and the WebSocket protocol includes Ping and Pong frames. Ping frames can be sent periodically to maintain the long-term connection.
#### Example

* **Server**

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

* **Client**

```php
use Swoole\WebSocket\Frame;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->upgrade('/');
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    // Send PING
    $cli->push($pingFrame);
    
    // Receive PONG
    $pongFrame = $cli->recv();
    var_dump($pongFrame->opcode === WEBSOCKET_OPCODE_PONG);
});
```
