# HTTP 伺服器

## 程式碼

請將以下程式碼寫入httpServer.php。

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

`HTTP`伺服器只需要關注請求響應即可，所以只需要監聽一個[onRequest](/http_server?id=on)事件。當有新的`HTTP`請求進入就會觸發此事件。事件回調函數有`2`個參數，一個是`$request`物件，包含了請求的相關資訊，如`GET/POST`請求的數據。

另外一個是`response`物件，對`request`的響應可以通過操作`response`物件來完成。`$response->end()`方法表示輸出一段`HTML`內容，並結束此請求。

* `0.0.0.0` 表示監聽所有`IP`地址，一台伺服器可能同時有多个`IP`，如`127.0.0.1`本地回環IP、`192.168.1.100`局域網IP、`210.127.20.2` 外網IP，這裡也可以單獨指定監聽一個IP
* `9501` 監聽的端口，如果被占用程式會拋出致命錯誤，中斷執行。

## 啟動服務

```shell
php httpServer.php
```
* 可以打開瀏覽器，訪問`http://127.0.0.1:9501`查看程式的結果。
* 也可以使用Apache `ab`工具對伺服器進行壓力測試。

## Chrome 請求兩次問題

使用`Chrome`瀏覽器訪問伺服器，會產生額外的一次請求，`/favicon.ico`，可以在程式碼中響應`404`錯誤。

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

## URL 路由

應用程式可以根據`$request->server['request_uri']`實現路由。如：`http://127.0.0.1:9501/test/index/?a=1`，程式碼中可以這樣實現`URL`路由。

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	//根據 $controller, $action 映射到不同的控制器類別和方法。
	(new $controller)->$action($request, $response);
});
```
