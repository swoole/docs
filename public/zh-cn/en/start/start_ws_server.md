# WebSocket Server

## Program Code

Please write the following code into `websocketServer.php`.

```php
// Create a WebSocket Server object and listen on 0.0.0.0:9502.
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

// Listen to the WebSocket connection open event.
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

// Listen to the WebSocket message event.
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

// Listen to the WebSocket connection close event.
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
```

* When the client sends a message to the server, the server triggers the `onMessage` event callback.
* The server can use `$server->push()` to send a message to a specific client (identified by `$fd`).

## Running the Program

```shell
php websocketServer.php
```

You can test it using the Chrome browser with the following JavaScript code:

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
	console.log('Error occurred: ' + evt.data);
};
```

## Comet

In addition to providing WebSocket functionality, the WebSocket server can also handle HTTP long connections. By adding an [onRequest](/http_server?id=on) event listener, it can implement the Comet solution for HTTP long polling.

!> For detailed usage, refer to [Swoole\WebSocket](/websocket_server).
