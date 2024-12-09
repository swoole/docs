# WebSocket-Server

?> Eine vollständig koordinierten Implementierung eines WebSocket-Servers, die von [Coroutine\Http\Server](/coroutine/http_server) erbt und einen Support für das `WebSocket`-Protokoll im Hintergrund bietet, wird hier nicht weiter ausgeführt, sondern nur die Unterschiede erwähnt.

!> Dieser Abschnitt ist ab Version 4.4.13 verfügbar.


## Vollständiges Beispiel

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
                $ws->push("Hallo {$frame->data}!");
                $ws->push("Wie geht es dir, {$frame->data}?");
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
    console.log("Verbunden mit dem WebSocket-Server.");
    websocket.send('hello');
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
</script>
HTML
        );
    });

    $server->start();
});
```


### Massen-Beispiel

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


## Arbeitsablauf

* `$ws->upgrade()`：Sende eine `WebSocket`-Handshake-Erfolgsnachricht an den Client
* `while(true)`-Schleife zum Empfang und Senden von Nachrichten
* `$ws->recv()`：Empfange ein `WebSocket`-Nachrichtsframe
* `$ws->push()`：Sende ein Datenframe an den Peer
* `$ws->close()`：Schließe die Verbindung

!> `$ws` ist ein `Swoole\Http\Response`-Objekt, und die spezifischen Methoden für jedes Objekt finden Sie im folgenden Text.


## Methoden


### upgrade()

Sende eine `WebSocket`-Handshake-Erfolgsnachricht.

!> Diese Methode sollte nicht für [asynchrone Server](/http_server) verwendet werden

```php
Swoole\Http\Response->upgrade(): bool
```


### recv()

Empfange eine `WebSocket`-Nachricht.

!> Diese Methode sollte nicht für [asynchrone Server](/http_server) verwendet werden, und die调用`recv`-Methode wird [anhalten](/coroutine?id=协程调度), bis Daten eingetroffen sind, um den Kontext der Koordination wiederherzustellen

```php
Swoole\Http\Response->recv(float $timeout = 0): Swoole\WebSocket\Frame | false | string
```

* **Rückgabetypen**

  * Erfolg beim Empfang einer Nachricht, zurückgeben Sie ein `Swoole\WebSocket\Frame`-Objekt, siehe [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)
  * Fehler zurückgeben Sie `false`, verwenden Sie [swoole_last_error()](/functions?id=swoole_last_error) zum Abrufen der Fehlermeldungscode
  * Verbindung geschlossen zurückgeben Sie eine leere Zeichenfolge
  * Rückgabetypenverarbeitung finden Sie im [Massen-Beispiel](/coroutine/ws_server?id=群发示例)


### push()

Sende ein `WebSocket`-Datenframe.

!> Diese Methode sollte nicht für [asynchrone Server](/http_server) verwendet werden, und beim Senden großer Pakete muss auf Schreibbarigkeit gewartet werden, was zu mehreren [Koordinationswechseln](/coroutine?id=协程调度) führen kann

```php
Swoole\Http\Response->push(string|object $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

* **Parameter** 

  !> Wenn das eingegebene `$data` ein [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) -Objekt ist, werden die nachfolgenden Parameter ignoriert und es wird unterstützt, verschiedene Frame-Typen zu senden

  * **`string|object $data`**

    * **Funktion**：Der zu sendende Inhalt
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

  * **`int $opcode`**

    * **Funktion**：Gibt den Format des zu sendenden Dateninhaltes an 【Standard ist Text. Für den Senden von binären Inhalten sollte der `$opcode`-Parameter auf `WEBSOCKET_OPCODE_BINARY` gesetzt werden】
    * **Standardwert**：`WEBSOCKET_OPCODE_TEXT`
    * **Andere Werte**：`WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Funktion**：Gibt an, ob die Übertragung abgeschlossen ist
    * **Standardwert**：`true`
    * **Andere Werte**：`false`

### close()

Schließe die `WebSocket`-Verbindung.

!> Diese Methode sollte nicht für [asynchrone Server](/http_server) verwendet werden, und in Versionen vor 4.4.15 wird möglicherweise ein `Warning`-Fehler gemeldet, der ignoriert werden kann.

```php
Swoole\Http\Response->close(): bool
```

Diese Methode wird die `TCP`-Verbindung direkt trennen und sendet keinen `Close`-Frame, was sich von der Methode `WebSocket\Server::disconnect()` unterscheidet.
Sie können vor dem Schließen der Verbindung die `$push()`-Methode verwenden, um einen `Close`-Frame zu senden und dem Client aktiv mitzuteilen.

```php
$frame = new Swoole\WebSocket\CloseFrame;
$frame->reason = 'close';
$ws->push($frame);
$ws->close();
```
