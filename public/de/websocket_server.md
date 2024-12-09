# Swoole\WebSocket\Server

?> Mit dem eingebauten `WebSocket`-Server können Sie mit nur wenigen Zeilen `PHP`-Code einen [asynchronen IO](/learn?id=同步io异步io)-Multi-Process-`WebSocket`-Server schreiben.

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

  * Browsers wie `Chrome/Firefox/` Higher Version `IE/Safari` haben eingebauten `WebSocket`-Clients in der JavaScript-Sprache
  * Das WeChat Mini-Program-Entwicklungskit hat einen eingebauten `WebSocket`-Client
  * In [asynchronen IO](/learn?id=同步io异步io)-`PHP`-Programmen kann man den [Swoole\Coroutine\Http](/coroutine_client/http_client) als `WebSocket`-Client verwenden
  * In `Apache/PHP-FPM` oder anderen synchron blockierenden `PHP`-Programmen kann man den [Synchronen WebSocket-Client](https://github.com/matyhtf/framework/blob/master/libs/Swoole/Client/WebSocket.php) aus dem `swoole/framework` verwenden
  * Nicht-`WebSocket`-Clients können nicht mit einem `WebSocket`-Server kommunizieren

* **Wie man herausfindet, ob eine Verbindung ein WebSocket-Client ist**

?> Indem man die [Beispiel](/server/methods?id=getclientinfo) verwendet, um Verbindungsinformationen zu erhalten, gibt es in dem zurückgelieferten Array ein Element namens [websocket_status](/websocket_server?id=连接状态). Anhand dieses Status kann man bestimmen, ob es sich um eine `WebSocket`-Verbindung handelt.
```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    $client = $server->getClientInfo($frame->fd);
    // 或者 $client = $server->connection_info($frame->fd);
    if (isset($client['websocket_status'])) {
        echo "es ist eine websocket Verbindung";
    } else {
        echo "ist keine websocket Verbindung";
    }
});
```

## Ereignisse

?> Neben den Rückruffunktionen der [Swoole\Server](/server/methods) und [Swoole\Http\Server](/http_server) Base Class hat der `WebSocket`-Server vier zusätzliche Rückruffunktionen zum Einstellen. Darunter:

* Die `onMessage` Rückruffunktion ist erforderlich
* Die Rückruffunktionen `onOpen`, `onHandShake` und `onBeforeHandShakeResponse` (Swoole 5 bietet dieses Ereignis) sind optional


### onBeforeHandShakeResponse

!> Swoole Version >= `v5.0.0` verfügbar

?> **Dieser Ereignis tritt vor der `WebSocket`-Verbindung auf. Wenn Sie das Handshake-Prozess nicht personalisieren müssen, aber einige `http header` Informationen in den Antwortkopf setzen möchten, dann können Sie dieses Ereignis auslösen.**

```php
onBeforeHandShakeResponse(Swoole\Http\Request $request, Swoole\Http\Response $response);
```


### onHandShake

?> **Dieses Ereignis wird nach dem Handshake der `WebSocket`-Verbindung aufgetreten. Der `WebSocket`-Server führt automatisch den Handshake-Prozess durch. Wenn der Benutzer den Handshake-Prozess selbst bearbeiten möchte, kann er die `onHandShake` Ereignis-Rückruffunktion einrichten.**

```php
onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

* **Hinweis**

  * Die `onHandShake` Ereignis-Rückruffunktion ist optional
  * Nachdem die `onHandShake` Rückruffunktion eingerichtet wurde, wird das `onOpen` Ereignis nicht mehr ausgelöst. Die Anwendungscode muss dies selbst verarbeiten, indem er die `$server->defer` verwendet, um die `onOpen` Logik aufzurufen
  * Bei der `onHandShake` müssen Sie [response->status()](/http_server?id=status) verwenden, um den Statuscode auf `101` zu setzen und [response->end()](/http_server?id=end) aufzurufen, sonst wird der Handshake fehlschlagen.
  * Das integrierte Handshake-Protokoll lautet `Sec-WebSocket-Version: 13`. Browsers niedrigerer Version müssen das Handshake selbst implementieren

* **Wichtig**

!> Wenn Sie das Handshake selbst bearbeiten müssen, dann richten Sie diese Rückruffunktion ein. Wenn Sie den Handshake-Prozess nicht "personalisieren" möchten, dann richten Sie diese Rückruffunktion nicht ein und verwenden Sie das Standard-Handshake von Swoole. Im Folgenden müssen Sie die "custom" `onHandShake` Ereignis-Rückruffunktion haben:

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // print_r( $request->header );
    // Wenn ich einige benutzerdefinierte Bedingungen nicht erfüllen, dann gebe ich end aus, gebe false zurück, der Handshake schlägt fehl
    //    $response->end();
    //     return false;

    // WebSocket Handshake Connection Algorithm Verification
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
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

!> Nachdem die `onHandShake` Rückruffunktion eingerichtet wurde, wird das `onOpen` Ereignis nicht mehr ausgelöst. Die Anwendungscode muss dies selbst verarbeiten, indem er die `$server->defer` verwendet, um die `onOpen` Logik aufzurufen

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    //省略了握手内容
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

?> **Wenn ein `WebSocket`-Client eine Verbindung zum Server herstellt und das Handshake abgeschlossen hat, wird diese Funktion aufgerufen.**

```php
onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request);
```

* **Hinweis**

    * `$request` ist ein [HTTP](/http_server?id=httprequest) Anforderungstextobjekt, das Informationen über die Handshakeanfrage des Clients enthält
    * In der `onOpen` Ereignisfunktion kann man [push](/websocket_server?id=push) verwenden, um Daten an den Client zu senden oder [close](/server/methods?id=close) zu verwenden, um die Verbindung zu schließen
    * Die `onOpen` Ereignis-Rückruffunktion ist optional


### onMessage

?> **Wenn der Server eine Datenframe von einem Client erhält, wird diese Funktion aufgerufen.**

```php
onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
```

* **Hinweis**

  * `$frame` ist ein [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) Objekt, das Informationen über das von einem Client gesendete Datenframe enthält
  * Die `onMessage` Rückruffunktion muss eingerichtet werden, sonst kann der Server nicht gestartet werden
  * Ping-Frames, die von einem Client gesendet werden, lösen das `onMessage` Ereignis nicht aus, der Bodenlayer会自动 antworten mit einem Pong-Paket, es ist auch möglich, das [open_websocket_ping_frame](/websocket_server?id=open_websocket_ping_frame) Parameter einzustellen, um das Handeln manuell zu verarbeiten

!> Wenn `$frame->data` ein Texttyp ist, ist die Kodierung format immer `UTF-8`, dies ist durch das `WebSocket`-Protokoll festgelegt

### onRequest

?> `Swoole\WebSocket\Server` ist eine Ableitung von [Swoole\Http\Server](/http_server), daher können alle `API` und Konfigurationsoptionen von `Http\Server` verwendet werden. Bitte beziehen Sie sich auf die [Swoole\Http\Server](/http_server)-Kapitel.

* Wenn der [onRequest](/http_server?id=on)-Rückruf festgelegt ist, kann der `WebSocket\Server` auch als `HTTP`-Server verwendet werden
* Wenn der [onRequest](/http_server?id=on)-Rückruf nicht festgelegt ist, wird der `WebSocket\Server` bei Erhalt einer `HTTP`-Anfrage eine `HTTP 400`-Fehlerseite zurücksenden
* Wenn Sie alle `WebSocket`-Push-Bitte durch Empfang einer `HTTP`-Anfrage auslösen möchten, beachten Sie das Problem des Kontextes. Bei prozeduralem Code sollten Sie eine Referenz auf `Swoole\WebSocket\Server` als `global` verwenden, und bei objektorientiertem Code können Sie `Swoole\WebSocket\Server` als Member-Attribute einrichten

#### Prozeduraler Codestil

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
    global $server;//call external server
    // $server->connections durchlaufen alle websocket-verbindungen der Benutzer fd, um allen Benutzern zu pushen
    foreach ($server->connections as $fd) {
        // muss zuerst überprüft werden, ob es sich um eine korrekte websocket-verbindung handelt, sonst könnte der push fehlschlagen
        if ($server->isEstablished($fd)) {
            $server->push($fd, $request->get['message']);
        }
    }
});
$server->start();
```

#### Objektorientierter Codestil

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
            // HTTP-Anfrage empfangen und den Wert des message-Parameters aus dem GET-Objekt abrufen, um den Benutzern zu pushen
            // $this->server->connections durchlaufen alle websocket-verbindungen der Benutzer fd, um allen Benutzern zu pushen
            foreach ($this->server->connections as $fd) {
                // muss zuerst überprüft werden, ob es sich um eine korrekte websocket-verbindung handelt, sonst könnte der push fehlschlagen
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

?> **Dieses Ereignis wird nur ausgelöst, wenn eine nicht-WebSocket-Verbindung geschlossen wird.**

!> Swoole Version >= `v4.7.0` verfügbar

```php
onDisconnect(Swoole\WebSocket\Server $server, int $fd)
```

!> Wenn der `onDisconnect`-Ereignishandler festgelegt ist, wird der Handler für nicht-WebSocket-Anfragen oder wenn im [onRequest](/websocket_server?id=onrequest)-Rückruf die Methode `$response->close()` aufgerufen wird, ausgelöst. Normalerweise wird bei einem normalen Abschluss im [onRequest](/websocket_server?id=onrequest)-Ereignis weder der `onClose`- noch der `onDisconnect`-Ereignis aufgerufen.  


## Methoden

`Swoole\WebSocket\Server` ist eine Unterklasse von [Swoole\Server](/server/methods), daher können alle Methoden von `Server` verwendet werden.

Es ist zu beachten, dass Daten an `WebSocket`-Client-Verbindungen von einem `WebSocket`-Server gesendet werden sollten, indem die Methode `Swoole\WebSocket\Server::push` verwendet wird. Diese Methode verpackt die Daten gemäß dem `WebSocket`-Protokoll. Die Methode [Swoole\Server->send()](/server/methods?id=send) ist jedoch die ursprüngliche TCP-Sendefunktion.

Die Methode [Swoole\WebSocket\Server->disconnect()](/websocket_server?id=disconnect) kann von einem Server aus eine `WebSocket`-Verbindung aktiv schließen. Es ist möglich, einen [Schließstatuscode](/websocket_server?id=websocket关闭帧状态码) (nach dem `WebSocket`-Protokoll ein dezimales Integer, das 1000 oder ein Wert zwischen 4000 und 4999 sein kann) und einen Schließgrund (ein UTF-8-Kodierter String mit einer Länge von bis zu 125 Bytes) anzugeben. Wenn keine Werte angegeben werden, ist der Statuscode 1000 und der Grund für den Schließvorgang leer.


### push

?> **Daten an eine `WebSocket`-Client-Verbindung senden, wobei die Länge maximal 2MB betragen darf.**

```php
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool

// v4.4.12 Version wurde zu flags Parameter geändert
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): bool
```

* **Parameter** 

  * **`int $fd`**

    * **Funktion**：ID der Clientverbindung 【Wenn der angegebene `$fd` nicht auf eine `WebSocket`-Clientverbindung verweist, wird der Vorgang fehlschlagen】
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

  * **`Swoole\WebSocket\Frame|string $data`**

    * **Funktion**：Dateninhalt, der gesendet werden soll
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

  !> Ab Version v4.2.0 wird, wenn `$data` ein Objekt der Klasse [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) ist, der nachfolgende Parameter ignoriert

  * **`int $opcode`**

    * **Funktion**：Gibt die Formatierung des zu sendenden Dateninhalts an 【Standard ist Text. Für den Versand von Binärdaten sollte `$opcode` auf `WEBSOCKET_OPCODE_BINARY` festgelegt werden】
    * **Standardwert**：`WEBSOCKET_OPCODE_TEXT`
    * **Andere Werte**：`WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Funktion**：Gibt an, ob der Versand abgeschlossen ist
    * **Standardwert**：`true`
    * **Andere Werte**：`false`

* **Rückgabewert**

  * Erfolgreicher Vorgang gibt `true` zurück, fehlgeschlagener Vorgang gibt `false` zurück

!> Ab Version v4.4.12 wurde der `finish`-Parameter (Typ `bool`) zu einem `flags`-Parameter (Typ `int`) um die Unterstützung für `WebSocket`-Kompression zu ermöglichen. Der `finish`-Wert entspricht dem Integerwert `1` für `SWOOLE_WEBSOCKET_FLAG_FIN`. Der ursprüngliche `bool`-Wert wird implizit in einen `int` umgewandelt, dieser Wechsel hat keine Auswirkungen auf die Abwärtskompatibilität. Darüber hinaus ist der Kompressionflag `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

!> [BASE-Modus](/learn?id=base_modus_beschränkungen:) unterstützt keine Übertragungen von `push` über Prozessgrenzen hinweg.


### exist

?> **Prüft, ob eine `WebSocket`-Clientverbindung besteht und ob ihr Zustand aktiv ist.**

!> Ab `v4.3.0` wird diese `API` nur zur Überprüfung der Verbindung verwendet, bitte verwenden Sie `isEstablished`, um zu überprüfen, ob es sich um eine `WebSocket`-Verbindung handelt

```php
Swoole\WebSocket\Server->exist(int $fd): bool
```

* **Rückgabewert**

  * Verbindung besteht und `WebSocket`-Handshake abgeschlossen, gibt `true` zurück
  * Verbindung existiert nicht oder Handshake nicht abgeschlossen, gibt `false` zurück
### Packen

?> **WebSocket-Nachrichten verpacken.**

```php
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true, bool $mask = false): string

// Seit Version v4.4.12 wurde der Parameter 'finish' (bool) durch den Parameter 'flags' (int) ersetzt, um die WebSocket-Kompression zu unterstützen. Bei 'finish' ist der Wert 'true' gleichbedeutend mit '1' im int-Wert. Die Umwandlung von bool-Werten in int-Werte ist rückwärts kompatibel und hat keine Auswirkungen.

Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string

Swoole\WebSocket\Frame::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
```

* **Parameter** 

  * **`Swoole\WebSocket\Frame|string $data $data`**

    * **Funktion**：Nachrichtinhalt
    * **Standardwert**：Kein
    * **Andere Werte**：Kein

  * **`int $opcode`**

    * **Funktion**：Gibt die Form der gesendeten Dateninhalt an 【Standardmäßig Text. Für den Versand von binären Inhalten muss der `$opcode`-Parameter auf `WEBSOCKET_OPCODE_BINARY` festgelegt werden】
    * **Standardwert**：`WEBSOCKET_OPCODE_TEXT`
    * **Andere Werte**：`WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Funktion**：Gibt an, ob das Frame fertig ist
    * **Standardwert**：Kein
    * **Andere Werte**：Kein

  * **`bool $mask`**

    * **Funktion**：Gibt an, ob ein Maske festgelegt ist 【Seit v4.4.12 wurde dieser Parameter entfernt】
    * **Standardwert**：Kein
    * **Andere Werte**：Kein

* **Rückgabewert**

  * Gibt den verpackten WebSocket-Datenpaket zurück, das über die [send()](/server/methods?id=send)-Methode der Swoole\Server-Basisklasse an den Peer gesendet werden kann

* **Beispiel**

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


### Unpacken

?> **WebSocket-Datenframe analysieren.**

```php
Swoole\WebSocket\Server::unpack(string $data): Swoole\WebSocket\Frame|false;
```

* **Parameter** 

  * **`string $data`**

    * **Funktion**：Nachrichtinhalt
    * **Standardwert**：Kein
    * **Andere Werte**：Kein

* **Rückgabewert**

  * Bei Fehlgeschlagen des Analyseprozesses wird `false` zurückgegeben, bei erfolgreicher Analyse wird ein Objekt der Klasse [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) zurückgegeben


### Trennen

?> **Aktiv ein WebSocket-Client-Verbindung mit einem Schließen-Frame trennen und die Verbindung schließen.**

!> Swoole-Version >= `v4.0.3` verfügbar

```php
Swoole\WebSocket\Server->disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
```

* **Parameter** 

  * **`int $fd`**

    * **Funktion**：ID des Client-Verbindungs 【Wenn der angegebene `$fd` nicht einer WebSocket-Client-Verbindung entspricht, wird der Sendevorgang fehlschlagen】
    * **Standardwert**：Kein
    * **Andere Werte**：Kein

  * **`int $code`**

    * **Funktion**：Statuscode für die Verbindungsschließung 【Gemäß RFC6455, für Anwendungs-Verbindungsschließungszustände liegen die Werte zwischen `1000` und `4999`】
    * **Standardwert**：`SWOOLE_WEBSOCKET_CLOSE_NORMAL`
    * **Andere Werte**：Kein

  * **`string $reason`**

    * **Funktion**：Grund für die Verbindungsschließung 【Ein `utf-8` Format-String mit einer Byte-Länge von bis zu `125`】
    * **Standardwert**：Kein
    * **Andere Werte**：Kein

* **Rückgabewert**

  * Bei erfolgreicher Sendung wird `true` zurückgegeben, bei Fehlschlagen oder ungültigem Statuscode wird `false` zurückgegeben


### IstEstablished

?> **Prüfen, ob eine Verbindung eine gültige WebSocket-Clientverbindung ist.**

?> Diese Funktion unterscheidet sich von der `exist` Methode, da die `exist` Methode nur prüft, ob es sich um eine TCP-Verbindung handelt und nicht, ob es sich um eine WebSocket-Clientverbindung handelt, die bereits die Handshake abgeschlossen hat.

```php
Swoole\WebSocket\Server->isEstablished(int $fd): bool
```

* **Parameter** 

  * **`int $fd`**

    * **Funktion**：ID des Client-Verbindungs 【Wenn der angegebene `$fd` nicht einer WebSocket-Client-Verbindung entspricht, wird der Sendevorgang fehlschlagen】
    * **Standardwert**：Kein
    * **Andere Werte**：Kein

* **Rückgabewert**

  * Wenn es sich um eine gültige Verbindung handelt, wird `true` zurückgegeben, sonst `false`


## WebSocket-Datenframe-Klasse


### Swoole\WebSocket\Frame

?> Seit der Version `v4.2.0` gibt es Unterstützung für das Senden von Objekten der Klasse [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) sowohl vom Server als auch vom Client  
Seit der Version `v4.4.12` wurde das `flags`-Property hinzugefügt, um die Unterstützung für komprimierte WebSocket-Frames zu ermöglichen, und es wurde eine neue Unterklasse [Swoole\WebSocket\CloseFrame](/websocket_server?id=swoolewebsocketcloseframe) hinzugefügt

Ein normales `frame` Objekt hat folgende Eigenschaften


Konstante | Beschreibung 
---|--- 
fd |  Das `socket id` des Clients, das bei der Verwendung von `$server->push` zum Versenden von Daten benötigt wird    
data | Der Inhaltsdaten, der sowohl Textinhalt als auch Binärdaten sein kann, und es kann durch den Wert von `opcode` bestimmt werden, ob es sich um Text oder Binärdaten handelt   
opcode | Die [Datenframe-Typ](/websocket_server?id=数据帧类型) für WebSocket, siehe auch das [WebSocket-Protokollstandarddokument](https://developer.mozilla.org/de/docs/Web/API/CloseEvent)    
finish | Gibt an, ob das Datenframe vollständig ist, eine WebSocket-Anfrage kann in mehrere Datenframes unterteilt werden (die Basisklasse hat bereits die automatische Zusammenlegung von Datenframes implementiert, daher müssen Sie sich keine Sorgen machen, unvollständige Datenframes zu erhalten)  

Diese Klasse verfügt über die Methoden [Swoole\WebSocket\Frame::pack()](/websocket_server?id=pack) und [Swoole\WebSocket\Frame::unpack()](/websocket_server?id=unpack), um WebSocket-Nachrichten zu verpacken und zu entpacken, die Parameterbeschreibungen entsprechen denen von `Swoole\WebSocket\Server::pack()` und `Swoole\WebSocket\Server::unpack()`


### Swoole\WebSocket\CloseFrame

Ein normales `Schließen-Frame close frame` Objekt hat folgende Eigenschaften


Konstante | Beschreibung 
---|--- 
opcode | Die [Datenframe-Typ](/websocket_server?id=数据帧类型) für WebSocket, siehe auch das [WebSocket-Protokollstandarddokument](https://developer.mozilla.org/de/docs/Web/API/CloseEvent)    
code | Der [Schließen-Frame-Statuscode](/websocket_server?id=WebSocket断开状态码) für WebSocket, siehe auch die [Fehlercodes im WebSocket-Protokoll](https://developer.mozilla.org/de/docs/Web/API/CloseEvent)    
reason | Der Grund für das Schließen, wenn kein klarer Grund angegeben ist, ist es leer

Wenn der Server ein `close frame` empfangen muss, muss er das [open_websocket_close_frame](/websocket_server?id=open_websocket_close_frame)-Parameter über `$server->set` aktivieren
### Verbindungszustand


Konstante | Corresponding Value | Beschreibung
---|---|---
WEBSOCKET_STATUS_CONNECTION | 1 | Verbindung wartet auf Handshake
WEBSOCKET_STATUS_HANDSHAKE | 2 | Handshake wird durchgeführt
WEBSOCKET_STATUS_ACTIVE | 3 | Handshake erfolgreich, Browser wartet auf Datenrahmen
WEBSOCKET_STATUS_CLOSING | 4 | Verbindung führt Schließ handshake durch, kurz vor Schließen


### WebSocket Schließrahmen Statuscodes


Konstante | Corresponding Value | Beschreibung
---|---|---
WEBSOCKET_CLOSE_NORMAL | 1000 | Normaler Schließvorgang, die Verbindung wurde abgeschlossen
WEBSOCKET_CLOSE_GOING_AWAY | 1001 | Server trennt ab
WEBSOCKET_CLOSE_PROTOCOL_ERROR | 1002 | Protokollfehler, Verbindung wird unterbrochen
WEBSOCKET_CLOSE_DATA_ERROR | 1003 | Datenfehler, zum Beispiel wurde Textdaten erwartet, aber Binärdaten empfangen
WEBSOCKET_CLOSE_STATUS_ERROR | 1005 | Es wurde kein erwarteter Statuscode empfangen
WEBSOCKET_CLOSE_ABNORMAL | 1006 | Kein Schließrahmen gesendet
WEBSOCKET_CLOSE_MESSAGE_ERROR | 1007 | Verbindung wird aufgrund von nicht konformem Empfangsdaten unterbrochen (z.B. Textnachricht enthält nicht UTF-8 Daten).
WEBSOCKET_CLOSE_POLICY_ERROR | 1008 | Verbindung wird aufgrund von nicht konformem Empfangsdaten unterbrochen. Dies ist ein allgemeiner Statuscode, der für Szenarien verwendet wird, in denen Statuscodes 1003 und 1009 nicht geeignet sind
WEBSOCKET_CLOSE_MESSAGE_TOO_BIG | 1009 | Verbindung wird aufgrund zu großer Datenrahmen unterbrochen
WEBSOCKET_CLOSE_EXTENSION_MISSING | 1010 | Client erwartet, dass Server eine oder mehrere Erweiterungen vereinbart, aber Server hat sie nicht bearbeitet, daher Client trennt ab
WEBSOCKET_CLOSE_SERVER_ERROR | 1011 | Client kann aufgrund unerwarteter Situationen seinen Request nicht abschließen, daher Server trennt ab.
WEBSOCKET_CLOSE_TLS | 1015 | Vorbehalten. Indicates that the connection was closed because the TLS handshake could not be completed (e.g., server certificate could not be verified).


## Optionen

?> `Swoole\WebSocket\Server` ist eine Unterklasse von `Server` und kann mit der Methode [Swoole\WebSocker\Server::set()](/server/methods?id=set) Konfigurationsoptionen übergeben werden, um bestimmte Parameter einzustellen.


### websocket_subprotocol

?> **Legt das `WebSocket`-Subprotokoll fest.**

?> Nach der Einstellung wird der `HTTP`-Header der Handshake-Antwort mit `Sec-WebSocket-Protocol: {$websocket_subprotocol}` ergänzt. Weitere Gebrauchsanweisungen finden Sie in den entsprechenden `RFC`-Dokumenten zum `WebSocket`-Protokoll.

```php
$server->set([
    'websocket_subprotocol' => 'chat',
]);
```


### open_websocket_close_frame

?> **Aktiviert die Empfang von Schließrahmen (Rahmen mit `opcode` 0x08) im `onMessage`-Callback, standardmäßig false.**

?> Nachdem dies aktiviert wurde, können im `onMessage`-Callback des `Swoole\WebSocket\Server` Schließrahmen, die von einem Client oder Server gesendet werden, empfangen und von Entwicklern selbst bearbeitet werden.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_close_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Schließrahmen empfangen: Code {$frame->code} Grund {$frame->reason}\n";
    } else {
        echo "Nachricht empfangen: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_ping_frame

?> **Aktiviert die Empfang von Ping-Rahmen (Rahmen mit `opcode` 0x09) im `onMessage`-Callback, standardmäßig false.**

?> Nachdem dies aktiviert wurde, können im `onMessage`-Callback des `Swoole\WebSocket\Server` Ping-Rahmen, die von einem Client oder Server gesendet werden, empfangen und von Entwicklern selbst bearbeitet werden.

!> Swoole-Version >= `v4.5.4` verfügbar

```php
$server->set([
    'open_websocket_ping_frame' => true,
]);
```

!> Wenn der Wert false ist,会自动 eine Pong-Rahmen antworten, aber wenn er auf true gesetzt ist, muss der Entwickler den Pong-Rahmen selbst antworten.

* **Beispiel**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_ping_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x09) {
        echo "Ping-Rahmen empfangen: Code {$frame->opcode}\n";
        // Pong-Rahmen antworten
        $pongFrame = new Swoole\WebSocket\Frame;
        $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
        $server->push($frame->fd, $pongFrame);
    } else {
        echo "Nachricht empfangen: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_pong_frame

?> **Aktiviert die Empfang von Pong-Rahmen (Rahmen mit `opcode` 0x0A) im `onMessage`-Callback, standardmäßig false.**

?> Nachdem dies aktiviert wurde, können im `onMessage`-Callback des `Swoole\WebSocket\Server` Pong-Rahmen, die von einem Client oder Server gesendet werden, empfangen und von Entwicklern selbst bearbeitet werden.

!> Swoole-Version >= `v4.5.4` verfügbar

```php
$server->set([
    'open_websocket_pong_frame' => true,
]);
```

* **Beispiel**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_pong_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0xa) {
        echo "Pong-Rahmen empfangen: Code {$frame->opcode}\n";
    } else {
        echo "Nachricht empfangen: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### websocket_compression

?> **Datenkompression aktivieren**

?> Wenn true, ist es möglich, die Rahmen mit `zlib` zu komprimieren, ob dies möglich ist, hängt von der Fähigkeit des Clients ab, Kompressionen zu verarbeiten (entnommen aus der Handshake-Information, siehe `RFC-7692`). Um tatsächlich einen bestimmten Rahmen zu komprimieren, muss das Flag `SWOOLE_WEBSOCKET_FLAG_COMPRESS` verwendet werden, 자세ige Gebrauchsanweisungen finden Sie in dieser Sektion [WebSocket-Rahmenkompression (RFC-7692)](/websocket_server?id=websocket帧压缩-（rfc-7692）).

!> Swoole-Version >= `v4.4.12` verfügbar


## Sonstiges

!> Beispiele können in den [WebSocket-Einheitentests](https://github.com/swoole/swoole-src/tree/master/tests/swoole_websocket_server) gefunden werden


### WebSocket-Rahmenkompression (RFC-7692)

?> Zuerst müssen Sie `'websocket_compression' => true` einrichten, um die Kompression zu aktivieren (während des WebSocket-Handshakes wird mit dem Peer Informationen über die Kompressionsunterstützung ausgetauscht), danach können Sie das Flag `SWOOLE_WEBSOCKET_FLAG_COMPRESS` verwenden, um einen bestimmten Rahmen zu komprimieren

#### Beispiel

* **Server**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->set(['websocket_compression' => true]);
$server->on('message', function (Server $server, Frame $frame) {
    $server->push(
        $frame->fd,
        'Hallo Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
    // $server->push($frame->fd, $frame); // Oder der Server kann den Rahmen des Clients direkt unverändert weiterleiten
});
$server->start();
```

* **Client**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->set(['websocket_compression' => true]);
    $cli->upgrade('/');
    $cli->push(
        'Hallo Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
});
```
### Senden eines Ping-Frames

?> Da WebSockets eine langfristige Verbindung sind, kann die Verbindung unterbrochen werden, wenn es eine bestimmte Zeit lang keine Kommunikation gibt. In diesem Fall ist ein Heartbeat-Mechanismus erforderlich. Das WebSockets-Protokoll umfasst zwei Frames: Ping und Pong, die periodisch zum Aufrechterhalten der langfristigen Verbindung gesendet werden können.

#### Beispiel

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
    // Senden eines PINGS
    $cli->push($pingFrame);
    
    // Empfangen eines PONGs
    $pongFrame = $cli->recv();
    var_dump($pongFrame->opcode === WEBSOCKET_OPCODE_PONG);
});
```
