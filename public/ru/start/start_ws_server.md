# WebSockets сервер

## Код программы

Пожалуйста, перепишите следующий код в websocketServer.php.

```php
// Создание объекта WebSockets сервера, слушающего на порту 9502 по адресу 0.0.0.0.
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

// Слушание события открытия WebSocket-соединения.
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

// Слушание события получения сообщения WebSocket.
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

// Слушание события закрытия WebSocket-соединения.
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
```

* Когда клиент отправляет сообщение серверу, сервер активирует обратный вызов события `onMessage`.
* Сервер может вызвать `$server->push()` для отправки сообщения определенному клиенту (используя идентификатор $fd).

## Запуск программы

```shell
php websocketServer.php
```

Для тестирования можно использовать браузер Chrome с следующим JavaScript-кода:

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

Кроме функций WebSockets, сервер на самом деле также может обрабатывать длинные HTTP-соединения. Для этого достаточно добавить прослушивание события [onRequest](/http_server?id=on) для реализации Comet-схемы длинного запроса HTTP.

!> Для подробного руководства по использованию смотрите [Swoole\WebSocket](/websocket_server).
