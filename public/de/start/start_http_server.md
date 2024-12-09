# HTTP-Server

## Programmcode

Bitte fügen Sie den folgenden Code in `httpServer.php` ein.

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hallo Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

Ein `HTTP`-Server muss sich nur um die Antwort auf Anfragen kümmern, daher ist es nur notwendig, ein [onRequest](/http_server?id=on)-Ereignis zu überwachen. Wenn eine neue `HTTP`-Anfrage eingeht, wird dieses Ereignis ausgelöst. Die Ereignishandlerschleife hat zwei Parameter: einen `$request`-Objekt, das Informationen über die Anfrage enthält, wie zum Beispiel die Daten einer `GET`/`POST`-Anfrage.

Der andere Parameter ist ein `$response`-Objekt, mit dem man die Antwort auf die Anfrage verarbeiten kann. Die Methode `$response->end()` gibt einen Abschnitt `HTML` aus und beendet die Anfrage.

* `0.0.0.0` bedeutet, dass alle `IP`-Adressen überwacht werden sollen. Ein Server kann gleichzeitig mehrere `IP`-Adressen haben, wie zum Beispiel `127.0.0.1` (lokaler Loopback-IP), `192.168.1.100` (LAN-IP) oder `210.127.20.2` (externes IP). Hier kann man auch ausdrücklich eine einzelne `IP` angeben, um sie zu überwachen.
* `9501` ist der Port, auf dem überwacht wird. Wenn er bereits besetzt ist, wird der Programm einen tödlichen Fehler auslösen und die Ausführung beenden.

## Starten des Services

```shell
php httpServer.php
```
* Man kann einen Webbrowser öffnen und die Adresse `http://127.0.0.1:9501` besuchen, um das Ergebnis des Programms zu sehen.
* Man kann auch die Apache-Tools `ab` verwenden, um den Server unter Stress zu测试en.

## Chrome-Anfrage-Zweimal-Problem

Wenn man mit dem `Chrome`-Browser den Server besucht, wird eine zusätzliche Anfrage für `/favicon.ico` generiert. Man kann im Code auf einen `404`-Fehler reagieren.

```php
$http->on('Request', function ($request, $response) {
	if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
	}
    var_dump($request->get, $request->post);
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hallo Swoole. #' . rand(1000, 9999) . '</h1>');
});
```

## URL-Routing

Die Anwendung kann auf der `$request->server['request_uri']` basierend das Routing implementieren. Zum Beispiel: `http://127.0.0.1:9501/test/index/?a=1`, kann im Code so umgesetzt werden.

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	// Nach $controller und $action werden verschiedene Controller-Klassen und Methoden zugeordnet.
	(new $controller)->$action($request, $response);
});
```
