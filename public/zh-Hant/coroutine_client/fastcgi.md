# 協程FastCGI客戶端

PHP-FPM使用了高效的二進制協議：`FastCGI協議`進行通訊, 通過FastCGI客戶端，則可以直接與PHP-FPM服務進行交互而無需通過任何HTTP反向代理

[PHP源碼目錄](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)

## 簡單使用示例

[更多示例代碼](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> 以下示例代碼需要在協程中調用

### 快速調用

```php
#greeter.php
echo 'Hello ' . ($_POST['who'] ?? 'World');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // FPM監聽地址, 也可以是形如 unix:/tmp/php-cgi.sock 的unixsocket地址
    '/tmp/greeter.php', // 想要執行的入口文件
    ['who' => 'Swoole'] // 附帶的POST信息
);
```

### PSR風格

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1:9000', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withScriptFilename(__DIR__ . '/greeter.php')
        ->withMethod('POST')
        ->withBody(['who' => 'Swoole']);
    $response = $client->execute($request);
    echo "Result: {$response->getBody()}\n";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### 複雜調用

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
    echo "Result: \n{$response->getBody()}";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### 一鍵代理WordPress

!> 此用法無生產意義, 生產中proxy可用於代理部分舊API接口的HTTP請求到舊的FPM服務上 (而不是代理整站)

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # WordPress項目根目錄
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # 這裡端口需要和WordPress配置一致, 一般不會特定指定端口, 就是80
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], #靜態資源路徑
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # 建立代理對象
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # 一鍵代理請求
});
$server->start();
```

## 方法

### call

靜態方法, 直接創建一個新的客戶端連接, 向FPM服務器發起請求並接收響應正文

!> FPM只支持短連接, 所以在通常情況下, 創建持久化對象沒有太大的意義

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **參數** 

    * **`string $url`**
      * **功能**：FPM監聽地址【如`127.0.0.1:9000`、`unix:/tmp/php-cgi.sock`等】
      * **默認值**：無
      * **其它值**：無

    * **`string $path`**
      * **功能**：想要執行的入口文件
      * **默認值**：無
      * **其它值**：無

    * **`$data`**
      * **功能**：附帶的請求數據
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間【默認為 -1 表示永不超時】
      * **值單位**：秒【支持浮點型，如 1.5 表示 1s+500ms】
      * **默認值**：`-1`
      * **其它值**：無

  * **返回值** 

    * 返回服務器響應的主體內容(body)
    * 發生錯誤時將拋出`Swoole\Coroutine\FastCGI\Client\Exception`異常

### __construct

客戶端對象的構造方法, 指定目標FPM服務器

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **參數** 

    * **`string $host`**
      * **功能**：目標服務器的地址【如`127.0.0.1`、`unix://tmp/php-fpm.sock`等】
      * **默認值**：無
      * **其它值**：無

    * **`int $port`**
      * **功能**：目標服務器端口【目標地址為UNIXSocket時無需傳入】
      * **默認值**：無
      * **其它值**：無

### execute

執行請求, 返回響應

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **參數** 

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **功能**：包含請求信息的對象, 通常使用`Swoole\FastCGI\HttpRequest`來模擬HTTP請求, 有特殊需求時才會使用FPM協議的原始請求類`Swoole\FastCGI\Request`
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間【默認為`-1`表示永不超時】
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：`-1`
      * **其它值**：無

  * **返回值** 

    * 返回和請求對象類型對標的Response對象, 如`Swoole\FastCGI\HttpRequest`會返回`Swoole\FastCGI\HttpResponse對象`, 包含了FPM服務器的響應信息
    * 發生錯誤時將拋出`Swoole\Coroutine\FastCGI\Client\Exception`異常

## 相關請求/響應類

由於library無法引入PSR龐大的依賴實現和擴展加載總是在PHP代碼執行之前, 所以相關的請求響應對象並沒有繼承PSR接口, 但盡量以PSR的風格實現以期開發者能夠快速上手使用

FastCGI模擬HTTP請求響應的類的相關源碼地址如下, 非常簡單, 代碼即文檔:

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
