# Coroutine FastCGI Client

PHP-FPM uses an efficient binary protocol, `FastCGI protocol`, for communication. By using the FastCGI client, you can directly interact with PHP-FPM service without going through any HTTP reverse proxy.

[PHP source code directory](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)
## Simple Usage Example

[More sample code](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> The following sample code needs to be called in a coroutine.
### Quick Invocation

```php
#greeter.php
echo 'Hello ' . ($_POST['who'] ?? 'World');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // FPM listening address, can also be a Unix socket address like unix:/tmp/php-cgi.sock
    '/tmp/greeter.php', // Entry file to execute
    ['who' => 'Swoole'] // Additional POST information
);
```
### PSR Style

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
### Complex Invocation

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
### One-click Proxy for WordPress

!> This usage has no actual production significance. In production, the proxy can be used to proxy HTTP requests of some old API interfaces to the old FPM service (instead of proxying the entire site).

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # Root directory of the WordPress project
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # The port here needs to match the WordPress configuration, generally no specific port is specified, just port 80
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], # Static resource paths
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # Create a proxy object
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # One-click proxy request
});
$server->start();
```
## Methods
### call

Static method, directly creates a new client connection, initiates a request to the FPM server and receives the response body.

!> FPM only supports short connections, so in most cases, creating persistent objects doesn't make much sense.

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **Parameters** 

    * **`string $url`**
      * **Description**: FPM listening address [e.g., `127.0.0.1:9000`, `unix:/tmp/php-cgi.sock`, etc.]
      * **Default value**: None
      * **Other values**: None

    * **`string $path`**
      * **Description**: Entry file to execute
      * **Default value**: None
      * **Other values**: None

    * **`$data`**
      * **Description**: Additional request data
      * **Default value**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Description**: Set timeout period [default is -1 meaning no timeout]
      * **Unit**: Seconds [supports floating-point, e.g., 1.5 means 1s+500ms]
      * **Default value**: `-1`
      * **Other values**: None

  * **Return Value** 

    * Returns the main content (body) of the server response
    * Throws `Swoole\Coroutine\FastCGI\Client\Exception` exception on error.
### __construct

Constructor of the client object, specifying the target FPM server

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **Parameters** 

    * **`string $host`**
      * **Description**: Address of the target server【e.g. `127.0.0.1`, `unix://tmp/php-fpm.sock`, etc.】
      * **Default Value**: None
      * **Other values**: None

    * **`int $port`**
      * **Description**: Port of the target server【Not required when the target address is a UNIXSocket】
      * **Default Value**: None
      * **Other values**: None
### execute

Execute a request and return a response

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **Parameters** 

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **Description**：Object containing request information, usually use `Swoole\FastCGI\HttpRequest` to simulate an HTTP request, use the original request class `Swoole\FastCGI\Request` for special requirements
      * **Default**：None
      * **Other values**：None

    * **`float $timeout`**
      * **Description**：Set the timeout period [default is `-1` which means no timeout]
      * **Unit**：seconds [supports floating point numbers, e.g. `1.5` means `1s`+`500ms`]
      * **Default**：`-1`
      * **Other values**：None

  * **Return Value** 

    * Returns a Response object corresponding to the type of the request object. For example, a `Swoole\FastCGI\HttpRequest` will return a `Swoole\FastCGI\HttpResponse` object, which contains the response information from the FPM server
    * Throws `Swoole\Coroutine\FastCGI\Client\Exception` exception in case of an error
## Related Request/Response Classes

Due to the fact that the library cannot introduce the huge dependencies of the PSR implementation and extension loading always occurs before PHP code execution, the related request and response objects do not inherit from PSR interfaces. However, they are implemented in the style of PSR as much as possible, so that developers can quickly get started.

The source code for the classes simulating HTTP request and response in FastCGI can be found at the following links. It is very simple, as the code serves as documentation:

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
