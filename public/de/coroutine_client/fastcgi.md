# Coroutine FastCGI Client

PHP-FPM verwendet ein effizientes Binärprotokoll: das `FastCGI-Protokoll` zur Kommunikation. Durch den FastCGI-Client kann man direkt mit dem PHP-FPM-Dienst interagieren, ohne durch einen HTTP-Reverseproxy gehen zu müssen.

[PHP-Quellcode-Verzeichnis](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)


## Einfaches Beispiel

[Es gibt mehr Beispielcode](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> Der folgende Beispielcode muss innerhalb einer Coroutine aufgerufen werden


### Schnellanruf

```php
#greeter.php
echo 'Hallo ' . ($_POST['wer'] ?? 'Welt');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // FPM-Listening-Adresse, kann auch eine Unix-Socket-Adresse wie unix:/tmp/php-cgi.sock sein
    '/tmp/greeter.php', // Die zu ausführende Eingangsfähre
    ['wer' => 'Swoole'] // Zusätzliche POST-Daten
);
```


### PSR-Stil

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1:9000', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withScriptFilename(__DIR__ . '/greeter.php')
        ->withMethod('POST')
        ->withBody(['wer' => 'Swoole']);
    $response = $client->execute($request);
    echo "Ergebnis: {$response->getBody()}\n";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Fehler: {$exception->getMessage()}\n";
}
```


### Komplexanruf

```php
#var.php
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
```

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withDocumentRoot(__DIR__)
        ->withScriptFilename(__DIR__ . '/var.php')
        ->withScriptName('var.php')
        ->withMethod('POST')
        ->withUri('/var?foo=bar&bar=char')
        ->withHeader('X-Foo', 'bar')
        ->withHeader('X-Bar', 'char')
        ->withBody(['foo' => 'bar', 'bar' => 'char']);
    $response = $client->execute($request);
    echo "Ergebnis: \n{$response->getBody()}";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Fehler: {$exception->getMessage()}\n";
}
```


### Ein-Klick-Proxy für WordPress

!> Diese Verwendung hat keinen Produktionssinn, in der Produktion kann ein Proxy dazu verwendet werden, HTTP-Anforderungen an alte API-Endpunkte zu proxyen, um sie an den alten FPM-Dienst zu senden (anstatt den gesamten Standort zu proxyen)

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # WordPress-Projekt-Wurzelverzeichnis
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # Hier muss die Portnummer mit der WordPress-Konfiguration übereinstimmen, normalerweise wird nicht speziell ein Port angegeben, es ist einfach 80
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], # Statische Ressourcenpfade
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # Erstellen eines Proxy-Objekts
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # Ein-Klick-Proxyanfrage
});
$server->start();
```


## Methoden


### call

Statische Methode, erstellen Sie direkt eine neue Client-Verbindung, senden Sie eine Anfrage an den FPM-Server und erhalten Sie den Antworttext

!> FPM unterstützt nur Kurzverbindungen, daher ist es in der Regel nicht sehr sinnvoll, dauerhafte Objekte zu erstellen

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **Parameter** 

    * **`string $url`**
      * **Funktion**：FPM-Listening-Adresse【z.B.`127.0.0.1:9000`、`unix:/tmp/php-cgi.sock` usw.**]
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $path`**
      * **Funktion**：Die zu ausführende Eingangsfähre
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`$data`**
      * **Funktion**：Zusätzliche Anfragedaten
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`float $timeout`**
      * **Funktion**：Timeout-Zeit festlegen【Standardwert ist `-1`, was bedeutet, dass es nie超时 wird】
      * **Wertbereich**：Sekunden【Unterstützt Fließkommazahlen, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：`-1`
      * **Andere Werte**：Keine

  * **Rückkehrwert** 

    * Gibt den Hauptinhalt der Antwort des Servers zurück(body)
    * Wenn ein Fehler auftritt, wird eine `Swoole\Coroutine\FastCGI\Client\Exception`-Ausnahme geworfen


### __construct

Konstruktor des Client-Objekts, geben Sie das Ziel-FPM-Server an

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **Parameter** 

    * **`string $host`**
      * **Funktion**：Adresse des Zielservers【z.B.`127.0.0.1`、`unix://tmp/php-fpm.sock` usw.**]
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`int $port`**
      * **Funktion**：Zielserver-Port【Wenn die Zieladresse ein UNIXSocket ist, ist dies nicht erforderlich】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine


### execute

Führen Sie die Anfrage aus, erhalten Sie die Antwort

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **Parameter** 

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **Funktion**：Objekt, das Anfrageinformationen enthält, normalerweise wird `Swoole\FastCGI\HttpRequest` verwendet, um HTTP-Anfragen zu simulieren, und nur bei speziellen Anforderungen wird das ursprüngliche FPM-Protokoll-Anfrageobjekt `Swoole\FastCGI\Request` verwendet
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`float $timeout`**
      * **Funktion**：Timeout-Zeit festlegen【Standardwert ist `-1`, was bedeutet, dass es nie超时 wird】
      * **Wertbereich**：Sekunden【Unterstützt Fließkommazahlen, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：`-1`
      * **Andere Werte**：Keine

  * **Rückkehrwert** 

    * Gibt ein Response-Objekt zurück, das dem Typ der Anfrageobjekts entspricht, zum Beispiel wird `Swoole\FastCGI\HttpRequest` ein `Swoole\FastCGI\HttpResponse`-Objekt zurückgeben, das die Antwortinformationen des FPM-Servers enthält
    * Wenn ein Fehler auftritt, wird eine `Swoole\Coroutine\FastCGI\Client\Exception`-Ausnahme geworfen

## Verwandte Anforderung/Antwortklassen

Da das library keine große Abhängigkeit von PSR einführen kann und die Erweiterungslade immer vor der Ausführung von PHP-Code stattfindet, haben die entsprechenden Anforderung- und Antwortobjekte die PSR-Interfaces nicht geerbt, aber sie wurden so weit wie möglich im Stil von PSR umgesetzt, um Entwicklern es zu erleichtern, schnell anfangen zu verwenden.

Die Quellcode-Adressen für die Klassen, die HTTP-Anfragen und Antworten nachahmen, sind wie folgt:

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
