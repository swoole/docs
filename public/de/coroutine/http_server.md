# HTTP-Server

?> Eine vollständig koordinierten Implementierung des HTTP-Servers, `Co\Http\Server`, ist aus Performancegründen für die HTTP-Analyse in C++ geschrieben und daher keine Unterklasse des von PHP geschriebenen [Co\Server](/coroutine/server).

Unterschiede gegenüber [Http\Server](/http_server):

* Können zur Laufzeit dynamisch erstellt und zerstört werden
* Die Verbindungshandhabung wird in einer separaten Unterkoordinate erledigt, die `Connect`, `Request`, `Response` und `Close` der Clientverbindungen sind vollständig seriell

!> Erfordert Version `v4.4.0` oder höher

!> Wenn beim编译zeit [HTTP2](/environment?id=compiler_options) aktiviert ist, wird die Unterstützung für das HTTP2-Protokoll standardmäßig启用, ohne dass man wie bei `Swoole\Http\Server` die [open_http2_protocol](/http_server?id=open_http2_protocol) konfigurieren muss (Hinweis: Known BUGs in der HTTP2-Unterstützung für Versionen unter `v4.4.16`, bitte nach dem Upgrade verwenden)


## Kurzname

Der Kurzname `Co\Http\Server` kann verwendet werden.


## Methoden


### __construct()

```php
Swoole\Coroutine\Http\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Parameter** 

    * **`string $host`**
      * **Funktion**: IP-Adresse, auf der das Server lauscht【Wenn es sich um eine lokale UNIX-Socket handelt, sollte es in der Form `unix://tmp/your_file.sock` angegeben werden】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $port`**
      * **Funktion**: lauschen Sie auf dem Port 
      * **Standardwert**: 0 (Lauschen Sie zufällig auf einem freien Port)
      * **Andere Werte**: 0~65535

    * **`bool $ssl`**
      * **Funktion**: Ob `SSL/TLS` Tunnel-Verschlüsselung aktiviert werden soll
      * **Standardwert**: false
      * **Andere Werte**: true
      
    * **`bool $reuse_port`**
      * **Funktion**: Ob die Port-Wiederverwendung-Funktion aktiviert werden soll, die es mehreren Diensten ermöglicht, denselben Port zu verwenden
      * **Standardwert**: false
      * **Andere Werte**: true


### handle()

Rufen Sie eine Rückruffunktion registrieren, um HTTP-Anfragen zu verarbeiten, die unter dem durch `$pattern` angegebenen Pfad eingehen.

```php
Swoole\Coroutine\Http\Server->handle(string $pattern, callable $fn): void
```

!> Muss vor [Server::start](/coroutine/server?id=start) als Behandlungsvariable festgelegt werden

  * **Parameter** 

    * **`string $pattern`**
      * **Funktion**: Legt den `URL`-Pfad fest【Zum Beispiel `/index.html`, beachten Sie, dass hier nicht `http://domain` eingegeben werden kann】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`callable $fn`**
      * **Funktion**: Behandlungsvariable, Referenz zur Verwendung von `Swoole\Http\Server`s [OnRequest](/http_server?id=on) Rückruf, wird hier nicht weiter ausgeführt
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner      

      Beispiel:

      ```php
      function callback(Swoole\Http\Request $req, Swoole\Http\Response $resp) {
          $resp->end("hello world");
      }
      ```

  * **Hinweise**

    * Der Server会自动 eine Koordinate erstellen und HTTP-Anfragen entgegennehmen, nachdem er erfolgreich `Accept` (Verbindung aufbauen) ist
    * `$fn` wird innerhalb eines neuen Unterkoordinatenspaces ausgeführt, daher ist es im Funktionstempo nicht notwendig, eine Koordinate erneut zu erstellen
    * Wenn der Client [KeepAlive](/coroutine_client/http_client?id=keep_alive) unterstützt, wird die Unterkoordinate weiterhin neue Anfragen entgegennehmen und nicht verlassen
    * Wenn der Client `KeepAlive` nicht unterstützt, wird die Unterkoordinate Anfragen nicht mehr entgegennehmen und die Verbindung schließen

  * **Wichtig**

    !> - Wenn `$pattern` den gleichen Pfad festlegt, wird das neue Setup das alte Setup überschreiben;  
    - Wenn keine Behandlungsvariable für den Wurzelpfad festgelegt ist und der angefragte Pfad keinen匹配 `$pattern` findet, wird Swoole einen `404` Fehler zurückgeben;  
    - `$pattern` verwendet eine String-Matching-Methode, unterstützt keine Wildcards oder reguläre Ausdrücke, ist nicht case-sensitive, das Matching-Algorithmus ist ein Präfix-Matching, zum Beispiel: Wenn die URL `test111` ist, wird sie zumRule `/test` matchen, und wenn sie gepaart wird, wird das Matching beendet und die nachfolgenden Einstellungen ignoriert;  
    - Es wird empfohlen, eine Behandlungsvariable für den Wurzelpfad festzulegen und im Rückruffunktionsbody `$request->server['request_uri']` zu verwenden, um die Anforderung zu routen.


### start()

?> **Server starten.** 

```php
Swoole\Coroutine\Http\Server->start();
```


### shutdown()

?> **Server beenden.** 

```php
Swoole\Coroutine\Http\Server->shutdown();
```

## Vollständiges Beispiel

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
```
