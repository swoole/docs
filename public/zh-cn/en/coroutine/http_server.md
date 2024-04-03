# HTTP Server

?> The fully coroutine-based HTTP server implementation, `Co\Http\Server`, is written in C++ for performance reasons related to HTTP parsing. Therefore, it is not a subclass of [Co\Server](/coroutine/server) written in PHP.

Differences from [Http\Server](/http_server):

* Can be dynamically created and destroyed during runtime
* Processing of connections is done in separate child coroutines, where the sequence of `Connect`, `Request`, `Response`, and `Close` for client connections is fully serial

!> Requires `v4.4.0` or higher

!> If [HTTP2 is enabled](/environment?id=编译选项) during compilation, HTTP2 protocol support will be enabled by default. No need to configure [open_http2_protocol](/http_server?id=open_http2_protocol) as in `Swoole\Http\Server` (Note: **For versions below v4.4.16, there are known bugs with HTTP2 support. Please upgrade before using**)

## Short Alias

You can use the short alias `Co\Http\Server`.

## Methods

### __construct()

```php
Swoole\Coroutine\Http\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Parameters**

    * **`string $host`**
      * **Purpose**: IP address to listen on 【If using a local UNIX socket, it should be formatted as `unix://tmp/your_file.sock`】
      * **Default value**: None
      * **Other values**: None

    * **`int $port`**
      * **Purpose**: Port to listen on 
      * **Default value**: 0 (listens on a random available port)
      * **Other values**: 0~65535

    * **`bool $ssl`**
      * **Purpose**: Whether to enable `SSL/TLS` tunnel encryption
      * **Default value**: false
      * **Other values**: true
      
    * **`bool $reuse_port`**
      * **Purpose**: Whether to enable port reuse feature, allowing multiple services to share a port
      * **Default value**: false
      * **Other values**: true

### handle()

Registers a callback function to handle HTTP requests under the path indicated by the parameter `$pattern`.

```php
Swoole\Coroutine\Http\Server->handle(string $pattern, callable $fn): void
```

!> The handler function must be set before calling [Server::start](/coroutine/server?id=start)

  * **Parameters**

    * **`string $pattern`**
      * **Purpose**: Sets the URL path 【e.g., `/index.html`, note that full URLs like `http://domain` cannot be passed here】
      * **Default value**: None
      * **Other values**: None

    * **`callable $fn`**
      * **Purpose**: Handler function, usage similar to the [OnRequest](/http_server?id=on) callback in `Swoole\Http\Server`. Not elaborating further here.
      * **Default value**: None
      * **Other values**: None      

      Example:

      ```php
      function callback(Swoole\Http\Request $req, Swoole\Http\Response $resp) {
          $resp->end("hello world");
      }
      ```

  * **Notes**

    * Upon successful `Accept` (connection establishment), the server will automatically create a coroutine to handle the HTTP request
    * The `$fn` is executed in a new child coroutine space, so there is no need to create a coroutine again inside the function
    * When the client supports [KeepAlive](/coroutine_client/http_client?id=keep_alive), the child coroutine will continue accepting new requests in a loop without exiting
    * When the client does not support `KeepAlive`, the child coroutine will stop accepting requests and exit by closing the connection

  * **Caution**

    !> - When setting the same path for `$pattern`, the new configuration will override the old one;  
    - If no handler function is set for the root path and the requested path does not match any configured `$pattern`, Swoole will return a `404` error;  
    - The `$pattern` uses string matching, does not support wildcards or regular expressions, is case-insensitive, and uses a prefix matching algorithm. For example, if the URL is `/test111`, it will match the rule set for `/test`, and once a match is found, the matching will be skipped for subsequent configurations;  
    - It is recommended to set a handler function for the root path and use `$request->server['request_uri']` in the callback function for request routing.

### start()

?> **Start the server.** 

```php
Swoole\Coroutine\Http\Server->start();
```

### shutdown()

?> **Shutdown the server.** 

```php
Swoole\Coroutine\Http\Server->shutdown();
```

## Complete Example

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
