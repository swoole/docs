# Coroutine HTTP/WebSocket Client

Die untere Ebene des Coroutine-HTTP-Clients ist in reiner C-Sprache geschrieben und hängt von keiner dritten Partei-Erweiterungslibrary ab, was eine äußerst hohe Leistung ermöglicht.

* Unterstützung für Http-Chunking, Keep-Alive-Funktion, Unterstützung für form-data-Format
* Die HTTP-Protokollversion beträgt HTTP/1.1
* Unterstützung für den Aufstieg zum WebSocket-Client
* Unterstützung für gzip-Komprimierung erfordert die Abhängigkeit von der zlib-Bibliothek
* Der Client implementiert nur die Kernfunktionen, in echten Projekten wird empfohlen, [Saber](https://github.com/swlib/saber) zu verwenden


## Eigenschaften


### errCode

Fehlerstatuscode. Wenn `connect/send/recv/close` fehlschlägt oder ein Timeout auftritt, wird automatisch der Wert von `Swoole\Coroutine\Http\Client->errCode` festgelegt

```php
Swoole\Coroutine\Http\Client->errCode: int
```

Der Wert von `errCode` entspricht dem Linux errno. Der Fehlercode kann mit `socket_strerror` in eine Fehlermeldung umgewandelt werden.

```php
// Wenn die Verbindung abgelehnt wird, ist der Fehlercode 111
// Wenn das Timeout eintritt, ist der Fehlercode 110
echo socket_strerror($client->errCode);
```

!> Referenz: [Liste der Linux-Fehlercodes](/other/errno?id=linux)


### body

Speichert den Körper der letzten Antwortnachricht.

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```


### statusCode

HTTP-Statuscode, wie 200, 404 usw. Wenn der Statuscode negativ ist, bedeutet dies, dass es ein Problem mit der Verbindung gibt. [Lesen Sie mehr](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```


## Methoden


### __construct()

Konstruktor.

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

  * **Parameter** 

    * **`string $host`**
      * **Funktion**: Zielserver-Hostadresse【kann IP-Adresse oder Domainname sein, die Basis会自动 für Domainname-Auflösung sorgen, wenn es sich um eine lokale UNIXSocket handelt, sollte es in der Form `unix://tmp/your_file.sock` angegeben werden; wenn es sich um einen Domainname handelt, ist es nicht erforderlich, das Protokollheader `http://` oder `https://` zu schreiben】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $port`**
      * **Funktion**: Zielserver-Hostport
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`bool $ssl`**
      * **Funktion**: Ob `SSL/TLS` Tunnel-Verschlüsselung aktiviert werden soll, wenn der Zielserver https ist, muss das `$ssl`-Parameter auf `true` gesetzt werden
      * **Standardwert**: `false`
      * **Andere Werte**: Keiner

  * **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```


### set()

Legt Client-Parameter fest.

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

Diese Methode nimmt die Parameter an, die auch von `Swoole\Client->set` akzeptiert werden, und kann Referenzen zu den Dokumenten zur [Swoole\Client->set](/client?id=set)-Methode finden.

`Swoole\Coroutine\Http\Client` fügt einige zusätzliche Optionen hinzu, um die Kontrolle über HTTP- und WebSocket-Clients zu erlangen.

#### Zusätzliche Optionen

##### Timeout-Steuerung

Legt das `timeout`-Option fest, um die Überwachung von HTTP-Anfrage-Timeouts zu aktivieren. Die Einheit ist Sekunden, die kleinste Granularität unterstützt Millisekunden.

```php
$http->set(['timeout' => 3.0]);
```

* Wenn die VerbindungTimeout oder der Server die Verbindung schließt, wird `statusCode` auf `-1` gesetzt
* Wenn der Server innerhalb der vereinbarten Zeit keine Antwort zurückgibt, wird die Anfrage aufgrund von Timeout auf `-2` gesetzt
* Nach Timeout wird die Verbindung von unten automatisch geschlossen
* Referenz[Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)

##### keep_alive

Legt das `keep_alive`-Option fest, um HTTP-Langzeitverbindungen zu aktivieren oder zu deaktivieren.

```php
$http->set(['keep_alive' => false]);
```

##### websocket_mask

> Aufgrund der RFC-Bestimmungen ist diese Einstellung ab v4.4.0 standardmäßig aktiviert, führt aber zu einer Leistungseinbuße, wenn der Server dies nicht zwingt, kann sie auf `false` gesetzt werden, um sie zu deaktivieren

Aktiviert oder deaktiviert die Maskierung für WebSocket-Kunden. Standardmäßig ist sie aktiviert. Wenn aktiviert, wird das gesendete Daten vom WebSocket-Client mit einer Maske verarbeitet.

```php
$http->set(['websocket_mask' => false]);
```

##### websocket_compression

> Erfordert `v4.4.12` oder höher

Wenn auf `true` gesetzt, wird **erlaubt**, Frames mit zlib zu komprimieren, ob dies möglich hängt von der Fähigkeit des Servers ab, Kompressionen zu verarbeiten (entnommen aus der Handshake-Information, siehe `RFC-7692`)

Um tatsächlich einen bestimmten Frame zu komprimieren, muss das Flags-Parameter `SWOOLE_WEBSOCKET_FLAG_COMPRESS` verwendet werden, siehe [diese Abschnitt](/websocket_server?id=websocket-Frame-Kompression-(rfc-7692)) für die spezifische Verwendungsweise

```php
$http->set(['websocket_compression' => true]);
```

##### write_func
> Erfordert `v5.1.0` oder höher

Legt eine `write_func`-Rückruffunktion fest, ähnlich wie die `WRITEFUNCTION`-Option von `CURL`, die verwendet werden kann, um fließendes Antwortinhalt zu verarbeiten,
beispielsweise den `Event Stream`-Ausgabeinhalt von `OpenAI ChatGPT`.

> Nachdem die `write_func` festgelegt wurde, kann die Methode `getContent()` nicht mehr verwendet werden, um den Antwortinhalt zu erhalten, und `$client->body` wird auch leer sein  
> In der `write_func` Rückruffunktion kann die Methode `$client->close()` verwendet werden, um den Empfang von Antwortinhalt zu stoppen und die Verbindung zu schließen

```php
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->set(['write_func' => function ($client, $data) {
    var_dump($data);
}]);
$cli->get('/');
```


### setMethod()

Legt die HTTP-Methode fest. Ist nur für die aktuelle Anfrage gültig, wird die Methode nach dem Senden der Anfrage sofort gelöscht.

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **Parameter** 

    * **`string $method`**
      * **Funktion**: Legt die Methode fest 
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

      !> Muss der Name einer Methode sein, die den HTTP-Standard entspricht, eine falsche Einstellung von `$method` kann dazu führen, dass die HTTP-Server die Anfrage ablehnt

  * **Beispiel**

```php
$http->setMethod("PUT");
```


### setHeaders()

Legt HTTP-Anfrageheader fest.

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **Parameter** 

    * **`array $headers`**
      * **Funktion**: Legt die Anfrageheader fest 【muss ein Schlüssel-Wert-Array sein, die Basis wird automatisch in das `$key`: `$value` Format der HTTP-Standardheader umgewandelt】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

!> Die mit `setHeaders` festgelegten HTTP-Header sind für den gesamten Lebenszyklus des `Coroutine\Http\Client` Objekts bei jeder Anfrage dauerhaft gültig; eine erneute Aufruf von `setHeaders` überschreibt die vorherige Einstellung


### setCookies()

Legt `Cookie` fest, der Wert wird URL-编码, um die ursprüngliche Information zu erhalten, bitte verwenden Sie `setHeaders`, um einen Header namens `Cookie` festzulegen.

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **Parameter** 

    * **`array $cookies`**
      * **Funktion**: Legt die `COOKIE` fest 【muss ein Schlüssel-Wert-Array sein】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner
!> -Nach dem Einstellen von `COOKIE` wird diese während der Lebensdauer des Client-Objekts persistierend gespeichert  

-Von der Serverseite festgelegte `COOKIE` werden in die `cookies`-Array integriert und können über die Eigenschaft `$client->cookies` accessed werden, um die aktuellen `HTTP`-Client-`COOKIE`-Informationen zu erhalten  
-Das wiederholte Aufrufen der `setCookies`-Methode überschreibt den aktuellen `Cookies`-Status und verwerft zuvor von der Serverseite gesendete `COOKIE` sowie zuvor festgelegte `COOKIE`


### setData()

Legt den Körper des HTTP-Anfragepakets fest.

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **Parameter** 

    * **`string|array $data`**
      * **Funktion**：Legt den Anfragepaketkörper fest
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

  * **Hinweise**

    * Nachdem `$data` festgelegt wurde und `$method` nicht festgelegt ist, wird der底层 automatisch auf POST gesetzt
    * Wenn `$data` ein Array ist und die `Content-Type` auf `urlencoded` festgelegt ist, wird der底层 automatisch `http_build_query` durchführen
    * Wenn `addFile` oder `addData` verwendet werden, um die `form-data`-Format zu aktivieren, wird der Wert von `$data`, wenn es ein String ist, ignoriert (da das Format unterschiedlich ist), aber wenn es ein Array ist, wird der底层 das Array in `form-data`-Format anhängen


### addFile()

Fügt ein POST-Datei hinzu.

!> Das Nutzen von `addFile`会自动 die `Content-Type` für das POST zu `form-data` ändern. Der `addFile`-Unterboden basiert auf `sendfile` und unterstützt asynchron den Senden großer Dateien.

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **Parameter** 

    * **`string $path`**
      * **Funktion**：Der Pfad zur Datei【Pflichtparameter, kann keine leere Datei oder eine nicht vorhandene Datei sein】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $name`**
      * **Funktion**：Der Name des Formulars【Pflichtparameter, der `key` im `FILES`-Parameter】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $mimeType`**
      * **Funktion**：Die MIME-Format der Datei【Optionale Parameter, der untere Schicht wird automatisch aus der Dateierweiterung abgeleitet】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $filename`**
      * **Funktion**：Der Dateiname【Optionaler Parameter】
      * **Standardwert**：`basename($path)`
      * **Andere Werte**：Keine

    * **`int $offset`**
      * **Funktion**：Der Offset für das Senden der Datei【Optionaler Parameter, kann verwendet werden, um Daten von der Mitte der Datei zu beginnen. Diese Funktion kann für die Unterstützung von Anforderungspause verwendet werden.】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`int $length`**
      * **Funktion**：Die Größe der gesendeten Daten【Optionaler Parameter】
      * **Standardwert**：Standardmäßig die Größe der gesamten Datei
      * **Andere Werte**：Keine

  * **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```


### addData()

Verwendet einen String zum Erstellen des Inhalts der hochgeladenen Datei. 

!> `addData` ist in Versionen ab `v4.1.0` verfügbar

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **Parameter** 

    * **`string $data`**
      * **Funktion**：Inhalt der Daten【Pflichtparameter, die maximale Länge darf nicht überschreiten [buffer_output_size](/server/setting?id=buffer_output_size)】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $name`**
      * **Funktion**：Der Name des Formulars【Pflichtparameter, der `key` im `$_FILES`-Parameter】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $mimeType`**
      * **Funktion**：Die MIME-Format der Datei【Optionaler Parameter, standardmäßig `application/octet-stream`】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $filename`**
      * **Funktion**：Der Dateiname【Optionaler Parameter, standardmäßig `$name`】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

  * **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```


### get()

Stellt eine GET-Anfrage.

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **Parameter** 

    * **`string $path`**
      * **Funktion**：Legt den `URL`-Pfad fest【z.B. `/index.html`, beachten Sie hier, dass Sie nicht `http://domain` eingeben können】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

  * **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

!> Das Nutzen von `get` ignoriert die vom `setMethod` festgelegte Anfragemethode und verwendet zwangsläufig `GET`


### post()

Stellt eine POST-Anfrage.

```php
Swoole\Coroutine\Http\Client->post(string $path, mixed $data): void
```

  * **Parameter** 

    * **`string $path`**
      * **Funktion**：Legt den `URL`-Pfad fest【z.B. `/index.html`, beachten Sie hier, dass Sie nicht `http://domain` eingeben können】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`mixed $data`**
      * **Funktion**：Daten des Anfragepakets
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

      !> Wenn `$data` ein Array ist, verpackt der底层 automatisch den Inhalt als `x-www-form-urlencoded`-Format für die POST-Anfrage und setzt die `Content-Type` auf `application/x-www-form-urlencoded`

  * **Hinweise**

    !> Das Nutzen von `post` ignoriert die vom `setMethod` festgelegte Anfragemethode und verwendet zwangsläufig `POST`

  * **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```


### upgrade()

Upgrades zu einem `WebSocket`-Verbindung.

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **Parameter** 

    * **`string $path`**
      * **Funktion**：Legt den `URL`-Pfad fest【z.B. `/` , beachten Sie hier, dass Sie nicht `http://domain` eingeben können】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

  * **Hinweise**

    * In einigen Fällen ist die Anforderung erfolgreich, aber `upgrade` gibt `true` zurück, aber der Server setzt keinen HTTP-Statuscode von `101` fest, sondern `200` oder `403`, was darauf hindeutet, dass der Server die Handshakeanfrage abgelehnt hat
    * Nach einem erfolgreichen `WebSocket`-Handshake kann man Nachrichten an den Server senden, indem man die `push`-Methode verwendet, und Nachrichten empfangen, indem man `recv` aufruft
    * `upgrade` erzeugt eine [Coroutine-调度](/coroutine?id=协程调度)

  * **Beispiel**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### push()

Wird eine Nachricht an den `WebSocket`-Server geschickt.

!> Die `push`-Methode darf nur nach einem erfolgreichen `upgrade` ausgeführt werden  
Die `push`-Methode erzeugt keine [Coroutine-Zeitplanung](/coroutine?id=coroutinetim Planning), sie gibt sofort zurück, nachdem der Sendebufffer geschrieben wurde

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **Parameter** 

    * **`mixed $data`**
      * **Funktion**：Der zu sendende Dateninhalt【Standardmäßig in `UTF-8` Textformat, wenn es sich um andere Formate oder Binärdaten handelt, verwenden Sie `WEBSOCKET_OPCODE_BINARY`】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

      !> Ab Swoole-Version >= v4.2.0 kann `$data` ein [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) Objekt verwenden, das verschiedene Frame-Typen unterstützt

    * **`int $opcode`**
      * **Funktion**：Typ der Operation
      * **Standardwert**：`WEBSOCKET_OPCODE_TEXT`
      * **Andere Werte**：Keine

      !> `$opcode` muss ein gültiger `WebSocket OPCode` sein, sonst wird ein Fehler zurückgegeben und eine Fehlermeldung mit `opcode max 10` ausgegeben

    * **`int|bool $finish`**
      * **Funktion**：Typ der Operation
      * **Standardwert**：`SWOOLE_WEBSOCKET_FLAG_FIN`
      * **Andere Werte**：Keine

      !> Ab Version v4.4.12 wurde das `finish`-Parameter (Typ `bool`) zu `flags` (Typ `int`) um die Unterstützung für `WebSocket`-Kompression zu ermöglichen, wobei `finish` dem Wert `1` für `SWOOLE_WEBSOCKET_FLAG_FIN` entspricht. Der ursprüngliche `bool` Typ wird implizit in einen `int` Typ umgewandelt, dieser Wechsel ist rückwärts kompatibel und hat keine Auswirkungen. Darüber hinaus ist der Kompression `flag` `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

  * **Rückgabewert**

    * Bei erfolgreicher 보ung wird `true` zurückgegeben
    * Bei nicht vorhandener Verbindung, geschlossener Verbindung oder unvollendeter `WebSocket` wird bei 보ung eines Fehlers `false` zurückgegeben

  * **Fehlercodes**


Fehlercode | Beschreibung
---|---
8502 | Falscher OPCode
8503 | Nicht an Server verbunden oder Verbindung bereits geschlossen
8504 | Handshake failed


### recv()

Empfängt eine Nachricht. Nur für `WebSocket` geeignet, muss in Kombination mit `upgrade()` verwendet werden, siehe Beispiel

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion**: Dieser Parameter ist nur gültig, wenn nach dem Aufrufen von `upgrade()` eine `WebSocket`-Verbindung aufgebaut wird
      * **Einheit des Wertes**: Sekunden【Unterstützt floating-point Werte, wie zum Beispiel `1.5`, was `1s`+`500ms` bedeutet】
      * **Standardwert**: Siehe [Client-Zeitüberschreitungsvorschriften](/coroutine_client/init?id=Zeitüberschreitungsvorschriften)
      * **Andere Werte**: Keine

      !> Bei Einstellung einer Zeitüberschreitung wird zuerst der spezifizierte Parameter verwendet, gefolgt von der im `set`-Methoden übergebenen `timeout`-Konfiguration
  
  * **Rückgabewert**

    * Bei erfolgreicher Ausführung wird ein Frame-Objekt zurückgegeben
    * Bei Fehlgeschlagenen Ausführungen wird `false` zurückgegeben, und es wird der `errCode`-Eigenschaft von `Swoole\Coroutine\Http\Client` überprüft. Wenn bei einem Coroutine-Client keine `onClose`-Callback vorhanden ist und die Verbindung geschlossen wurde, wird bei Empfang einer Nachricht `false` zurückgegeben und `errCode=0`.
 
  * **Beispiel**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```


### download()

Lädt ein File über HTTP herunter.

!> Im Gegensatz zur `get`-Methode schreibt die `download`-Methode das Empfangene Daten in den Datenträger, anstatt den HTTP Body im Speicher zu zusammenfügen. Daher benötigt die `download`-Methode nur eine kleine Menge an Speicher, um riesige Dateien herunterzuladen.

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename,  int $offset = 0): bool
```

  * **Parameter** 

    * **`string $path`**
      * **Funktion**: Legt den `URL`-Pfad fest
      * **Standardwert**: Kein
      * **Andere Werte**: Keine

    * **`string $filename`**
      * **Funktion**: Gibt den Pfad des zu schreibenden Dateis an, in dem das heruntergeladene Inhalt geschrieben wird【Wird automatisch in die `downloadFile`-Eigenschaft geschrieben】
      * **Standardwert**: Kein
      * **Andere Werte**: Keine

    * **`int $offset`**
      * **Funktion**: Gibt die Offsetposition im Dateis an, an der geschrieben werden soll【Diese Option kann für die Unterstützung von Anhangsdateien verwendet werden, zusammen mit dem HTTP-Header `Range:bytes=$offset`】
      * **Standardwert**: Kein
      * **Andere Werte**: Keine

      !> Wenn `$offset` bei `0` ist und das File bereits vorhanden ist, wird der Dateiaufbau unten automatisch geleert

  * **Rückgabewert**

    * Bei erfolgreicher Ausführung wird `true` zurückgegeben
    * Bei fehlgeschlagenem Öffnen des Files oder beim Scheitern von `fseek()` im unteren Level wird `false` zurückgegeben

  * **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```


### getCookies()

Gibt den `cookie`-Inhalt der `HTTP`-Antwort zurück.

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> Der Cookie-Information wird nach dem解码 mit urldecode entschlüsselt, um die ursprünglichen Cookie-Informationen zu erhalten, siehe unten für die eigene Analyse

#### Um kopf鼎ere Cookies oder die ursprünglichen Headerinformationen von Cookies zu erhalten

```php
var_dump($client->set_cookie_headers);
```


### getHeaders()

Gibt die Kopfinformationen der `HTTP`-Antwort zurück.

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```


### getStatusCode()

Gibt den Statuscode der `HTTP`-Antwort zurück.

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **Hinweis**

    * **Wenn der Statuscode negativ ist, bedeutet dies, dass es ein Problem mit der Verbindung gibt.**


Statuscode | v4.2.10 und höher versionen entsprechen den oben genannten Konstanten | Beschreibung

---|---|---

-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | Verbindung Timeout, Server hört nicht auf dem Port oder Netzwerkverlust, spezifische Netzwerkfehlercode kann aus $errCode ausgelesen werden

-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | Anforderung Timeout, Server hat die Antwort innerhalb der festgelegten timeout-Zeit nicht zurückgegeben

-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | Nach dem Senden der Clientanfrage hat der Server die Verbindung gewaltsam geschlossen
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | Client sendet fehlgeschlagen (dieser Constant ist für Swoole-Versionen >= `v4.5.9` verfügbar, für Versionen davor bitte den Statuscode verwenden)


### getBody()

Gibt den Körperinhalt der `HTTP`-Antwort zurück.

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```


### close()

Schließt die Verbindung.

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> Nach dem Schließen, wenn Sie weitere `get`, `post` oder andere Methoden anfordern, wird Swoole Ihnen helfen, sich wieder mit dem Server zu verbinden.


### execute()

Eine noch niedrigere Ebene der `HTTP`-Anfragemethode, die in der Code muss mit [setMethod](/coroutine_client/http_client?id=setmethod) und [setData](/coroutine_client/http_client?id=setdata) und anderen Schnittstellen zum Einstellen der Anfragemethode und des Dateninhalts verwendet werden.

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **Beispiel**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```
## Funktionen

Um die Verwendung von `Coroutine\Http\Client` zu erleichtern, wurden drei Funktionen hinzugefügt:

!> Swoole-Version >= `v4.6.4` verfügbar


### request()

Stellt eine Anforderung mit einer spezifizierten HTTP-Methode ein.

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```


### post()

Wird verwendet, um eine `POST`-Anfrage zu stellen.

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```


### get()

Wird verwendet, um eine `GET`-Anfrage zu stellen.

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```

### Beispielverwendung

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
