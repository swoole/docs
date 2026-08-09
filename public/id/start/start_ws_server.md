# WebSocket Server

## Kode Program

Tulis kode berikut ke dalam websocketServer.php.

```php
// Buat object WebSocket Server, listen di 0.0.0.0:9502.
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

// Listen event Open (koneksi WebSocket terbuka).
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

// Listen event Message (ada pesan masuk).
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

// Listen event Close (koneksi WebSocket ditutup).
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
```

* Pas client ngirim pesan ke server, server bakal picu event callback `onMessage`.
* Server bisa panggil `$server->push()` buat ngirim pesan ke client tertentu (pake identifier `$fd`).

## Menjalankan Program

```shell
php websocketServer.php
```

Kamu bisa test pake Chrome browser, kode JS-nya:

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

Selain WebSocket, server WebSocket juga bisa nanganin HTTP long connection. Tinggal tambahin event listener [onRequest](/http_server?id=on) buat implementasi solusi Comet (HTTP long polling).

!> Buat detail pemakaian, lihat [Swoole\WebSocket](/websocket_server).
