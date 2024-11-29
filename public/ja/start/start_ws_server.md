## Websocketサーバー

## プログラムコード

以下のコードをwebsocketServer.phpに書き込んでください。

```php
<?php
// Websocketサーバーを作成し、0.0.0.0:9502ポートで待ちます。
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

// Websocket接続が開いたイベントを待ちます。
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

// Websocketメッセージのイベントを待ちます。
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

// Websocket接続が閉じたイベントを待ちます。
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
?>
```

* クライアントがサーバーにメッセージを送信すると、サーバーは`onMessage`イベントの回调がトリガーされます。
* サーバーは`$server->push()`を呼び出して、特定のクライアント（$fd識別子を使用）にメッセージを送信することができます。

## プログラム実行

```shell
php websocketServer.php
```

Chromeブラウザを使用してテストすることができます。JavaScriptコードは以下の通りです：

```javascript
var wsServer = 'ws://127.0.0.1:9502';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
	console.log("WebSocketサーバーに接続しました。");
};

websocket.onclose = function (evt) {
	console.log("切断されました。");
};

websocket.onmessage = function (evt) {
	console.log('サーバーからデータを受け取りました: ' + evt.data);
};

websocket.onerror = function (evt, e) {
	console.log('エラーが発生しました: ' + evt.data);
};
```

## Comet

WebsocketサーバーはWebsocket機能を提供するだけでなく、実際にはHTTPの長连接も処理することができます。CometプロトコルのHTTP長轮询を実現するには、[onRequest](/http_server?id=on)イベントの监听を追加するだけで済みます。

!> 詳細な使用方法については[Swoole\WebSocket](/websocket_server)を参照してください。
