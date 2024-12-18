# HTTP сервер

## Программа код

Пожалуйста, напишите следующий код в httpServer.php.

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Привет, Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

`HTTP` сервер должен обращать внимание только на ответ на запрос, поэтому необходимо слушать только один [onRequest](/http_server?id=on) событие. Когда поступает новый `HTTP` запрос, это событие будет активировано. В функции обратной связи события есть `2` параметра: первый - объект `$request`, который содержит информацию о запросе, такую как данные `GET/POST` запроса.

Другой параметр - объект `$response`, с помощью которого можно отвечать на запрос от `$request`. Метод `$response->end()` означает вывод куска `HTML` контента и завершение этого запроса.

* `0.0.0.0` означает прослушивание всех `IP` адресов, у одного сервера может быть несколько `IP`, например, `127.0.0.1` - локальный обратный IP, `192.168.1.100` - локальный сетевой IP, `210.127.20.2` - внешний сетевой IP, здесь также можно указать для прослушивания отдельный `IP`.
* `9501` - порт, на котором сервер слушает, если он занят, программа抛出ает смертельную ошибку и прекращает выполнение.

## Запуск сервиса

```shell
php httpServer.php
```
* Можно открыть браузер и посетить `http://127.0.0.1:9501` чтобы увидеть результат программы.
* Также можно использовать инструмент Apache `ab` для нагрузочного тестирования сервера.

## Проблема с двумя запросами в Chrome

При использовании браузера Chrome для посещения сервера генерируется дополнительный запрос, `/favicon.ico`, в коде можно ответить ошибкой `404`.

```php
$http->on('Request', function ($request, $response) {
	if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
	}
    var_dump($request->get, $request->post);
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Привет, Swoole. #' . rand(1000, 9999) . '</h1>');
});
```

## URL роутинг

Приложение может реализовать роутинг на основе `$request->server['request_uri']`. Например: `http://127.0.0.1:9501/test/index/?a=1`, в коде можно реализовать такой роутинг.

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	// по $controller, $action маппировать на разные контроллеры и методы.
	(new $controller)->$action($request, $response);
});
```
