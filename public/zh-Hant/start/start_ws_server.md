# WebSocket伺服器

## 程式碼

請將以下程式碼寫入websocketServer.php。

```php
//建立WebSocket Server物件，監聽0.0.0.0:9502端口。
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

//監聽WebSocket連接打開事件。
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

//監聽WebSocket訊息事件。
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

//監聽WebSocket連接關閉事件。
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
```

* 客戶端向伺服器端發送資訊時，伺服器端觸發`onMessage`事件回調。
* 伺服器端可以調用`$server->push()`向某個客戶端（使用$fd識別符）發送訊息。

## 運行程式

```shell
php websocketServer.php
```

可以使用Chrome瀏覽器進行測試，JS程式碼為：

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

WebSocket伺服器除了提供WebSocket功能之外，實際上也可以處理HTTP長連接。只需要增加[onRequest](/http_server?id=on)事件監聽即可實現Comet方案HTTP長輪詢。

!> 詳細使用方法參考[Swoole\WebSocket](/websocket_server)。
