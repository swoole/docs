# Coroutine\Http2\Client

Coroutine Http2客户端

## Beispielverwendung

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $domain = 'www.zhihu.com';
    $cli = new Client($domain, 443, true);
    $cli->set([
        'timeout' => -1,
        'ssl_host_name' => $domain
    ]);
    $cli->connect();
    $req = new Request();
    $req->method = 'POST';
    $req->path = '/api/v4/answers/300000000/voters';
    $req->headers = [
        'host' => $domain,
        'user-agent' => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip'
    ];
    $req->data = '{"type":"up"}';
    $cli->send($req);
    $response = $cli->recv();
    var_dump(assert(json_decode($response->data)->error->code === 10002));
});
```

## Methoden

### __construct()

Konstruktor.

```php
Swoole\Coroutine\Http2\Client::__construct(string $host, int $port, bool $open_ssl = false): void
```

  * **Parameter** 

    * **`string $host`**
      * **Funktion**：Zielhosts-IP-Adresse【Wenn `$host` ein Domainname ist, muss eine `DNS`-Abfrage durchgeführt werden】
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

    * **`int $port`**
      * **Funktion**：Zielport【`Http` ist in der Regel Port `80`, `Https` ist in der Regel Port `443`】
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

    * **`bool $open_ssl`**
      * **Funktion**：Ob die TLS/SSL-Tunneling-Verschlüsselung aktiviert werden soll 【Für `https` Websites muss dies auf `true` gesetzt werden】
      * **Standardwert**：`false`
      * **Andere Werte**：`true`

  * **Hinweis**

    !> - Wenn Sie eine Anforderung an eine externe URL senden möchten, ändern Sie den `timeout` in einen größeren Wert, siehe [Client-Zeitüberschreitungsvorschriften](/coroutine_client/init?id=Zeitüberschreitungsvorschriften)  
    - `$ssl` benötigt die `openssl`-Bibliothek und muss bei der Erstellung von `Swoole` aktiviert werden [--enable-openssl](/environment?id=Kompilierungsoptionen)


### set()

Legt Client-Parameter fest, weitere detaillierte Konfigurationsoptionen finden Sie unter [Swoole\Client::set](/client?id=Konfiguration) Konfigurationsoptionen

```php
Swoole\Coroutine\Http2\Client->set(array $options): void
```


### connect()

Verbindet sich mit dem Zielserver. Diese Methode hat keine Parameter.

!> Nach dem Aufrufen von `connect` wird unter der Oberfläche automatisch eine [Coroutine-调度](/coroutine?id=调度) durchgeführt, und `connect` gibt zurück, wenn die Verbindung erfolgreich ist oder fehlschlägt. Nachdem die Verbindung hergestellt wurde, kann die `send`-Methode verwendet werden, um eine Anforderung an den Server zu senden.

```php
Swoole\Coroutine\Http2\Client->connect(): bool
```

  * **Rückgabewert**

    * Verbindung erfolgreich, gibt `true` zurück
    * Verbindung fehlgeschlagen, gibt `false` zurück, bitte überprüfen Sie das `errCode`-Property für den Fehlercode


### stats()

Erhält den Stream-Status.

```php
Swoole\Coroutine\Http2\Client->stats([$key]): array|bool
```

  * **Beispiel**

```php
var_dump($client->stats(), $client->stats()['local_settings'], $client->stats('local_settings'));
```


### isStreamExist()

Prüft, ob der angegebene Stream existiert.

```php
Swoole\Coroutine\Http2\Client->isStreamExist(int $stream_id): bool
```


### send()

Sendet eine Anforderung an den Server, der Boden会自动 eine `Http2`-`stream` einrichten. Es ist möglich, mehrere Anfragen gleichzeitig zu senden.

```php
Swoole\Coroutine\Http2\Client->send(Swoole\Http2\Request $request): int|false
```

  * **Parameter** 

    * **`Swoole\Http2\Request $request`**
      * **Funktion**：Senden Sie ein Swoole\Http2\Request Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

  * **Rückgabewert**

    * Erfolgreich gibt der Stream-编号 zurück, der von `1` an递增 und ungerade ist
    * Fehlgeschlagen gibt `false` zurück

  * **Hinweis**

    * **Request-Objekt**

      !> Das Swoole\Http2\Request Objekt hat keine Methoden, Informationen zur Anforderung werden durch Einstellung der Objekteigenschaften geschrieben.

      * `headers` Array, `HTTP`-Header
      * `method` String, setzt die Anforderungsweise, wie `GET`, `POST`
      * `path` String, setzt den `URL`-Pfad, wie `/index.php?a=1&b=2`, muss mit `/` beginnen
      * `cookies` Array, setzt die `COOKIES`
      * `data` setzt den Anforderungstextkörper, wenn es ein String ist, wird es direkt als `RAW form-data` gesendet
      * `data` als Array, der Boden verpackt automatisch das `x-www-form-urlencoded` Format für den `POST`-Inhalt und setzt `Content-Type` zu `application/x-www-form-urlencoded`
      * `pipeline` Boolean, wenn auf `true` gesetzt, wird nach dem Senden von `$request` der Stream nicht geschlossen, und es ist möglich, weitere Daten Frame zu senden, siehe `write` Methode.

    * **pipeline**

      * Standardmäßig schließt die `send` Methode nach dem Senden der Anforderung den aktuellen `Http2 Stream`,启用`pipeline` lässt den Boden den Stream erhalten, und es ist möglich, die `write` Methode mehrmals aufzurufen, um Daten Frame an den Server zu senden, siehe `write` Methode.


### write()

Sendet weitere Daten Frame an den Server, es ist möglich, mehrmals `write` auf denselben Stream zu verwenden, um Daten Frame zu senden.

```php
Swoole\Coroutine\Http2\Client->write(int $streamId, mixed $data, bool $end = false): bool
```

  * **Parameter** 

    * **`int $streamId`**
      * **Funktion**：Stream-编号, der von `send` zurückgegeben wird
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

    * **`mixed $data`**
      * **Funktion**：Inhalt des Daten Frame, kann ein String oder ein Array sein
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

    * **`bool $end`**
      * **Funktion**：Ob der Stream geschlossen werden soll
      * **Standardwert**：`false`
      * **Andere Werte**：`true`

  * **Beispielverwendung**

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9518);
    $cli->set(['timeout' => 1]);
    var_dump($cli->connect());

    $req3 = new Request();
    $req3->path = "/index.php";
    $req3->headers = [
        'host' => "localhost",
        "user-agent" => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip',
    ];
    $req3->pipeline = true;
    $req3->method = "POST";
    $streamId = $cli->send($req3);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    //end stream
    $cli->write($streamId, ['int' => rand(1000, 9999), 'end' => true], true);
    var_dump($cli->recv());
    $cli->close();
});
```

!> Um Daten Frame in Abschnitten zu senden, muss die `$request->pipeline` beim Senden der Anforderung auf `true` gesetzt werden  
Nachdem ein Daten Frame mit `end` auf `true` gesendet wurde, wird der Stream geschlossen und es ist nicht mehr möglich, Daten an diesen Stream zu senden.


### recv()

Empfängt eine Anforderung.

!> Beim Aufrufen dieser Methode wird eine [Coroutine-调度](/coroutine?id=调度) durchgeführt

```php
Swoole\Coroutine\Http2\Client->recv(float $timeout): Swoole\Http2\Response;
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion**：Legt die Zeitüberschreitung fest, siehe [Client-Zeitüberschreitungsvorschriften](/coroutine_client/init?id=Zeitüberschreitungsvorschriften)
      * **Einheit des Wertes**：Sekunden【Unterstützt float-Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

  * **Rückgabewert**

Erfolgreich gibt es ein Swoole\Http2\Response Objekt zurück

```php
/**@var $resp Swoole\Http2\Response */
var_dump($resp->statusCode); // Der von服务器 gesendete Http-Statuscode, wie 200, 502 usw.
var_dump($resp->headers); // Die von server gesendeten Headerinformationen
var_dump($resp->cookies); // Die von server gesetzten COOKIE Informationen
var_dump($resp->set_cookie_headers); // Die von server zurückgelieferten ursprünglichen COOKIE Informationen, einschließlich domain und path
var_dump($resp->data); // Der von server gesendete Antwortkörper
```

!> Wenn die Swoole-Version < [v4.0.4](/version/bc?id=_404) ist, ist das `data` Attribut das `body` Attribut; wenn die Swoole-Version < [v4.0.3](/version/bc?id=_403) ist, sind `headers` und `cookies` singular.
### read()

Entweder identisch mit `recv()`, der Unterschied liegt darin, dass für Antworten vom Typ `pipeline`, `read` in mehreren Teilen erfolgen kann, um Speicher zu sparen oder Push-Informationen so schnell wie möglich zu empfangen, während `recv` immer alle Frames zu einer vollständigen Antwort zusammenfügt, bevor sie zurückgegeben wird.

!> Bei der Ausführung dieser Methode wird eine [Coroutine-调度](/coroutine?id=协程调度) generiert

```php
Swoole\Coroutine\Http2\Client->read(float $timeout): Swoole\Http2\Response;
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion**: Legt die Timeoutzeit fest, siehe [Client-Timeout-Regeln](/coroutine_client/init?id=超时规则)
      * **Einheit der Werte**: Sekunden 【Unterstützt floating-point-Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Rückgabewert**

    Erfolgreich zurückkehrt ein Swoole\Http2\Response-Objekt


### goaway()

Das GOAWAY-Frame wird verwendet, um eine Verbindung zu schließen oder einen schwerwiegenden Fehlerstatus zu signalisieren.

```php
Swoole\Coroutine\Http2\Client->goaway(int $error_code = SWOOLE_HTTP2_ERROR_NO_ERROR, string $debug_data): bool
```


### ping()

Das PING-Frame ist ein Mechanismus, um die minimale Round-Trip-Zeit von der Seite des Absenders zu messen und zu bestimmen, ob eine freie Verbindung immer noch gültig ist.

```php
Swoole\Coroutine\Http2\Client->ping(): bool
```

### close()

Schließt die Verbindung.

```php
Swoole\Coroutine\Http2\Client->close(): bool
```
