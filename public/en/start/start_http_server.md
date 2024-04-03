# HTTP Server

## Program Code

Please write the following code into httpServer.php.

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

The `HTTP` server only needs to focus on request responses, so it only needs to listen to the [onRequest](/http_server?id=on) event. This event is triggered when a new `HTTP` request arrives. The event callback function has `2` parameters: `$request` object, which contains request information such as `GET/POST` data, and a `$response` object, through which you can respond to the request. The response can be completed by manipulating the `$response` object. The `$response->end()` method outputs an `HTML` content and ends the request.

* `0.0.0.0` means listening on all IP addresses. A server may have multiple IP addresses, such as `127.0.0.1` for local loopback IP, `192.168.1.100` for LAN IP, and `210.127.20.2` for WAN IP. You can also specify to listen on a single IP address.
* `9501` is the port to listen to. If it is occupied, the program will throw a fatal error and halt execution.

## Start Service

```shell
php httpServer.php
```

* You can open a browser and visit `http://127.0.0.1:9501` to see the program's output.
* You can also use the Apache `ab` tool to stress test the server.

## Chrome Double Request Issue

When using the `Chrome` browser to access the server, an extra request for `/favicon.ico` is generated. This issue can be handled by responding with a `404` error in the code.

```php
$http->on('Request', function ($request, $response) {
    if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
    }
    var_dump($request->get, $request->post);
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});
```

## URL Routing

The application can implement URL routing based on `$request->server['request_uri']`. For example: when accessing `http://127.0.0.1:9501/test/index/?a=1`, the code can implement URL routing like this.

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
    // Map to different controller class and method based on $controller, $action.
    (new $controller)->$action($request, $response);
});
```
