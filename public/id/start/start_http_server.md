# HTTP Server

## Kode Program

Tulis kode berikut ke dalam httpServer.php.

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

Server `HTTP` cuma perlu fokus ke request dan response, jadi cukup listening ke event [onRequest](/http_server?id=on) aja. Event ini bakal kepanggil setiap ada request `HTTP` baru masuk. Callback function-nya punya `2` parameter: object `$request` yang isinya informasi request kayak data `GET/POST`, dan object `$response` yang bisa dipake buat ngirim response balik. `$response->end()` gunanya buat ngeluarin konten `HTML` dan sekaligus ngakhirin request.

* `0.0.0.0` artinya listening di semua alamat `IP`. Sebuah server bisa punya beberapa `IP`, misalnya `127.0.0.1` buat local loopback, `192.168.1.100` buat LAN, `210.127.20.2` buat WAN. Kamu juga bisa specify cuma satu `IP` tertentu.
* `9501` adalah port yang di-dengarkan. Kalo port-nya udah dipake program lain, bakal muncul fatal error dan eksekusi berhenti.

## Menjalankan Service

```shell
php httpServer.php
```

* Buka browser dan akses `http://127.0.0.1:9501` buat liat hasilnya.
* Bisa juga pake tool `ab` dari Apache buat stress test server.

## Masalah Chrome Request Dobel

Pas pake browser `Chrome`, bakal ada request tambahan ke `/favicon.ico`. Ini bisa dihandle dengan ngembaliin response `404` di kode.

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

Aplikasi bisa implementasi URL routing berdasarkan `$request->server['request_uri']`. Contoh: `http://127.0.0.1:9501/test/index/?a=1`, di kode bisa kayak gini.

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	// Map ke controller dan method yang berbeda berdasarkan $controller, $action.
	(new $controller)->$action($request, $response);
});
```
