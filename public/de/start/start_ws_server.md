# WebSocket-Server

## Programmcode

Bitte schreiben Sie den folgenden Code in websocketServer.php.

```php
// Erstellen eines WebSocket Server Objekts, das auf dem Port 9502 auf dem Host 0.0.0.0 lauscht.
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

// Lassen Sie sich das Ereignis für die Eröffnung einer WebSocket-Verbindung auslösen.
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

// Lassen Sie sich das Ereignis für eine Nachricht an einer WebSocket-Verbindung auslösen.
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

// Lassen Sie sich das Ereignis für die Schließung einer WebSocket-Verbindung auslösen.
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} ist geschlossen\n";
});

$ws->start();
```

* Wenn ein Client Informationen an den Server sendet, wird das Ereignis `onMessage` auf dem Server ausgelöst.
* Der Server kann die Methode `$server->push()` verwenden, um einer bestimmten Client-Seite (mit dem Identifikator $fd) eine Nachricht zu senden.

## Ausführen des Programms

```shell
php websocketServer.php
```

Sie können es mit dem Chrome Browser testen, indem Sie folgenden JavaScript-Code verwenden:

```javascript
var wsServer = 'ws://127.0.0.1:9502';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
	console.log("Verbunden mit dem WebSocket-Server.");
};

websocket.onclose = function (evt) {
	console.log("Verknüpft");
};

websocket.onmessage = function (evt) {
	console.log('Daten vom Server empfangen: ' + evt.data);
};

websocket.onerror = function (evt, e) {
	console.log('Fehler aufgetreten: ' + evt.data);
};
```

## Comet

Neben der Bereitstellung der WebSocket-Funktion kann ein WebSocket-Server tatsächlich auch HTTP-Langzeitverbindungen bearbeiten. Es reicht aus, das Ereignis [onRequest](/http_server?id=on) hinzuzufügen, um das Comet-Schema für HTTP-Langzeitabfrage zu implementieren.

!> Ausführliche Gebrauchsanweisungen finden Sie unter [Swoole\WebSocket](/websocket_server).
