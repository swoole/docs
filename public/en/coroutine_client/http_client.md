# Coroutine HTTP/WebSocket Client

The underlying of coroutine version of the `HTTP` client is written in pure `C` without relying on any third-party extension library, with extremely high performance.

* Supports `Http-Chunk`, `Keep-Alive` features, and `form-data` format
* The `HTTP` protocol version is `HTTP/1.1`
* Supports upgrading to a `WebSocket` client
* Support for `gzip` compression format requires dependency on the `zlib` library
* The client only implements core features, it is recommended to use [Saber](https://github.com/swlib/saber) in actual projects
## Properties
### errCode

Error status code. When `connect/send/recv/close` fails or times out, the value of `Swoole\Coroutine\Http\Client->errCode` will be automatically set.

```php
Swoole\Coroutine\Http\Client->errCode: int
```

The value of `errCode` is equal to `Linux errno`. You can use `socket_strerror` to convert the error code to an error message.

```php
// If connect is refused, the error code is 111
// If it times out, the error code is 110
echo socket_strerror($client->errCode);
```

!> Reference: [Linux Error Code List](/other/errno?id=linux)
### body

Stores the response body of the last request.

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```
### statusCode

HTTP status code, such as 200, 404, etc. If the status code is a negative number, it indicates that there is a connection issue. [Learn more](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```
## Methods
### __construct()

Constructor method.

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

* **Parameters** 

    * **`string $host`**
        * **Function**: Target server host address [can be an IP or domain name, the underlying system automatically performs domain resolution. If it is a local UNIX socket, it should be filled in the format like `unix://tmp/your_file.sock`. If it is a domain name, there is no need to fill in the protocol header `http://` or `https://`]
        * **Default Value**: None
        * **Other Values**: None

    * **`int $port`**
        * **Function**: Target server port
        * **Default Value**: None
        * **Other Values**: None

    * **`bool $ssl`**
        * **Function**: Whether to enable `SSL/TLS` tunnel encryption. If the target server is using HTTPS, the `$ssl` parameter must be set to `true`
        * **Default Value**: `false`
        * **Other Values**: None

* **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```
### set()

Set client parameters.

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

This method is completely consistent with the parameters received by `Swoole\Client->set`, please refer to the documentation of the [Swoole\Client->set](/client?id=set) method.

`Swoole\Coroutine\Http\Client` has added some additional options to control the `HTTP` and `WebSocket` clients.
#### Extra Options
##### Timeout Control

Set the `timeout` option to enable HTTP request timeout detection. The unit is in seconds, with support for milliseconds as the smallest granularity.

```php
$http->set(['timeout' => 3.0]);
```

- If the connection times out or the server closes the connection, the `statusCode` will be set to `-1`.
- If the server fails to respond within the specified time, the request times out, and the `statusCode` will be set to `-2`.
- After a request times out, the underlying connection will be automatically severed.
- Refer to the [client timeout rules](/coroutine_client/init?id=timeout-rules)
##### keep_alive

Set the `keep_alive` option to enable or disable HTTP persistent connections.

```php
$http->set(['keep_alive' => false]);
```
##### websocket_mask

> Due to RFC regulations, this configuration is enabled by default starting from v4.4.0, but it may lead to performance loss. If the server side does not have strict requirements, it can be set to false to disable it.

Enables or disables masking for the `WebSocket` client. It is enabled by default. When enabled, masking is applied to the data sent by the WebSocket client for data transformation.

```php
$http->set(['websocket_mask' => false]);
```
##### websocket_compression

> Requires `v4.4.12` or higher version

When set to `true`, **allows** frames to be compressed using zlib. Whether compression can actually be performed depends on whether the server can handle compression (determined by handshake information, see `RFC-7692`).

To actually compress a specific frame, it is necessary to use the `SWOOLE_WEBSOCKET_FLAG_COMPRESS` flag parameter. For specific usage methods, [see this section](/websocket_server?id=websocket-frame-compression-(rfc-7692))

```php
$http->set(['websocket_compression' => true]);
```
### setMethod()

Set the request method. It is only valid for the current request, and the method setting will be cleared immediately after sending the request.

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **Parameters** 

    * **`string $method`**
      * **Functionality**: Set the method 
      * **Default value**: None
      * **Other values**: None

      !> It must be a method name that complies with the `HTTP` standard. If the `$method` is set incorrectly, the request may be rejected by the `HTTP` server.

  * **Example**

```php
$http->setMethod("PUT");
```
### setHeaders()

Set HTTP request headers.

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **Parameters**

    * **`array $headers`**
      * **Functionality**: Set the request headers. [Must be an associative array, the underlying logic will automatically convert it to the correct format `$key`: `$value` of the HTTP standard header format.]
      * **Default Value**: None
      * **Other Values**: None

!> The `HTTP` headers set by `setHeaders` are permanently valid for each request during the lifetime of the `Coroutine\Http\Client` object. Re-calling `setHeaders` will override the previous settings.
### setCookies()

Sets `Cookie`, the value will be `urlencode` encoded. If you want to keep the original information, please use `setHeaders` to set a `header` named `Cookie` by yourself.

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **Parameters** 

    * **`array $cookies`**
      * **Description**: Set `COOKIE` [must be an associative array of key-value pairs]
      * **Default**: None
      * **Other values**: None

!> - The set `COOKIE` will be continuously saved during the client object's lifetime  
- `COOKIE`s set by the server will be merged into the `cookies` array, and you can read the current `HTTP` client's `COOKIE` information by accessing the `$client->cookies` property  
- Repeated calls to the `setCookies` method will overwrite the current `Cookies` state, which will discard previously set server-side `COOKIE`s as well as previously set `COOKIE`s.
### setData()

Set the body of the HTTP request.

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **Parameters** 

    * **`string|array $data`**
      * **Function**: Set the body of the request
      * **Default**: None
      * **Other values**: None

  * **Tips**

    * If `$data` is set and `$method` is not set, the underlying layer will automatically set it as POST
    * If `$data` is an array and the `Content-Type` is `urlencoded`, the underlying layer will automatically execute `http_build_query`
    * If you have used `addFile` or `addData` resulting in enabling the `form-data` format, when `$data` is a string, it will be ignored (as the formats differ), but when it is an array, the underlying layer will append the fields from the array in the `form-data` format.
### addFile()

Add a POST file.

!> When using `addFile`, the `Content-Type` of `POST` will automatically change to `form-data`. `addFile` is based on `sendfile` at the underlying level and can support asynchronous sending of very large files.

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **Parameters** 

    * **`string $path`**
      * **Purpose**: Path of the file. [Required parameter, cannot be an empty file or a non-existent file]
      * **Default value**: None
      * **Other values**: None

    * **`string $name`**
      * **Purpose**: Name of the form. [Required parameter, the key in the `FILES` parameter]
      * **Default value**: None
      * **Other values**: None

    * **`string $mimeType`**
      * **Purpose**: MIME type of the file. [Optional parameter, the underlying system will automatically infer based on the file extension]
      * **Default value**: None
      * **Other values**: None

    * **`string $filename`**
      * **Purpose**: Name of the file. [Optional parameter]
      * **Default value**: `basename($path)`
      * **Other values**: None

    * **`int $offset`**
      * **Purpose**: Offset of uploading the file. [Optional parameter, can specify to start transmitting data from the middle of the file. This feature can be used to support resume transmission.]
      * **Default value**: None
      * **Other values**: None

    * **`int $length`**
      * **Purpose**: Size of the data to send. [Optional parameter]
      * **Default value**: Default to the size of the entire file
      * **Other values**: None

  * **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```
### addData()

Build the content of the uploaded file using a string.

!> `addData` is available in version `v4.1.0` and above

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **Parameters** 

    * **`string $data`**
      * **Function**: Data content【Required parameter, maximum length should not exceed [buffer_output_size](/server/setting?id=buffer_output_size)】
      * **Default value**: None
      * **Other values**: None

    * **`string $name`**
      * **Function**: Name of the form【Required parameter, `key` in `$_FILES` parameters】
      * **Default value**: None
      * **Other values**: None

    * **`string $mimeType`**
      * **Function**: MIME format of the file【Optional parameter, defaults to `application/octet-stream`】
      * **Default value**: None
      * **Other values**: None

    * **`string $filename`**
      * **Function**: File name【Optional parameter, defaults to `$name`】
      * **Default value**: None
      * **Other values**: None

  * **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```
### get()

Initiate a GET request.

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **Parameters** 

    * **`string $path`**
      * **Function**: Set the URL path [e.g. `/index.html`, note that you cannot pass `http://domain` here]
      * **Default**: None
      * **Other values**: None

  * **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

!> Using `get` will ignore the request method set by `setMethod` and force the use of `GET`.
### post()

Initiate a POST request.

```php
Swoole\Coroutine\Http\Client->post(string $path, mixed $data): void
```

  * **Parameters** 

    * **`string $path`**
      * **Description**：Set the URL path 【such as `/index.html`, Note that you cannot pass `http://domain` here】
      * **Default value**：None
      * **Other values**：None

    * **`mixed $data`**
      * **Description**：The body data of the request
      * **Default value**：None
      * **Other values**：None

      !> If `$data` is an array, the underlying system will automatically package it as `x-www-form-urlencoded` format of POST content and set `Content-Type` as `application/x-www-form-urlencoded`.

  * **Note**

    !> Using `post` will ignore the request method set by `setMethod` and forcibly use `POST`.

  * **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```
### upgrade()

Upgrade to a `WebSocket` connection.

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **Parameters** 

    * **`string $path`**
      * **Function** : Set the `URL` path [such as `/`, note that you cannot pass in `http://domain` here]
      * **Default Value** : None
      * **Other Values** : None

  * **Tips**

    * In some cases, although the request is successful and `upgrade` returns `true`, the server did not set the HTTP status code to `101`, but rather `200` or `403`, indicating that the server refused the handshake request.
    * After a successful WebSocket handshake, you can use the `push` method to push messages to the server side, and you can also call `recv` to receive messages.
    * `upgrade` will trigger a [coroutine scheduling](/coroutine?id=coroutine-scheduling).

  * **Example**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### push()

Push messages to the `WebSocket` server.

!> The `push` method can only be executed after the `upgrade` is successful.  
The `push` method does not trigger coroutine scheduling, it returns immediately after writing to the send buffer.

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **Parameters** 

    * **`mixed $data`**
      * **Function**: The data content to be sent. Default is in `UTF-8` text format. For other encoding or binary data, please use `WEBSOCKET_OPCODE_BINARY`.
      * **Default value**: None
      * **Other values**: None

      !> Swoole version >= v4.2.0, `$data` can use the [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) object, supports sending various frame types.

    * **`int $opcode`**
      * **Function**: Operation type.
      * **Default value**: `WEBSOCKET_OPCODE_TEXT`
      * **Other values**: None

      !> `$opcode` must be a valid `WebSocket OPCode`, otherwise it will fail and print the error message `opcode max 10`.

    * **`int|bool $finish`**
      * **Function**: Operation type.
      * **Default value**: `SWOOLE_WEBSOCKET_FLAG_FIN`
      * **Other values**: None

      !> Since `v4.4.12`, the `finish` parameter (a `bool` type) has been changed to `flags` (an `int` type) to support `WebSocket` compression. `finish` corresponding `SWOOLE_WEBSOCKET_FLAG_FIN` value is `1`. The original `bool` type value will be implicitly converted to `int` type, this change is backward compatible without impact. Additionally, the compression `flag` is `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

  * **Return Value**

    * If successfully sent, returns `true`.
    * If the connection does not exist, is closed, or the `WebSocket` is not completed, sending fails and returns `false`.

  * **Error Codes**

Error Code | Description
---|---
8502 | Invalid OPCode
8503 | Not connected to the server or the connection has been closed
8504 | Handshake failed
### recv()

Receiving messages. Used only for `WebSocket` and needs to be used in conjunction with `upgrade()`. See the example.

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **Parameters**

    * **`float $timeout`**
      * **Function**: This parameter is only valid when calling `upgrade()` to upgrade to a `WebSocket` connection.
      * **Value Unit**: Seconds [supports floating points, e.g., `1.5` represents `1s` + `500ms`]
      * **Default Value**: Refer to the [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other Values**: None

      !> Set a timeout. The specified parameter is prioritized first, followed by the `timeout` configuration passed in the `set` method.

  * **Return Value**

    * Returns the frame object if successful.
    * Returns `false` on failure. Check the `Swoole\Coroutine\Http\Client`'s `errCode` property. The coroutine client does not have an `onClose` callback. When the connection is closed, `recv` returns `false` with `errCode=0`.

  * **Example**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### download()

Download files via HTTP.

!> The difference between `download` and `get` methods is that `download` will write the received data to the disk instead of concatenating the HTTP body in memory. Therefore, `download` uses a small amount of memory to complete the download of very large files.

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename, int $offset = 0): bool
```

  * **Parameters** 

    * **`string $path`**
      * **Function**: Set the URL path.
      * **Default value**: None.
      * **Other values**: None.

    * **`string $filename`**
      * **Function**: Specify the file path to write the downloaded content to [will be automatically written to the `downloadFile` property].
      * **Default value**: None.
      * **Other values**: None.

    * **`int $offset`**
      * **Function**: Specify the offset for writing to the file [this option can be used to support resumable downloads, can be combined with the `Range: bytes=$offset` HTTP header].
      * **Default value**: None.
      * **Other values**: None.

      !> When `$offset` is `0`, if the file already exists, the underlying system will automatically empty this file.

  * **Return Value**

    * Returns `true` on success.
    * Returns `false` on failure to open the file or failure of `fseek()` by the underlying system.

  * **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```
### getCookies()

Get the content of the `cookie` from the `HTTP` response.

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> The cookie information will be decoded using urldecode. To get the original cookie information, please parse it yourself according to the following text.
#### Get the duplicate `Cookie` or original `Cookie` headers

```php
var_dump($client->set_cookie_headers);
````

### getHeaders()

Returns the header information of the `HTTP` response.

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```
### getStatusCode()

Get the status code of the HTTP response.

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **Hints**

    * **If the status code is negative, it indicates a connection issue.**

Status Code | Constant Corresponding to version 4.2.10 and above | Description
---|---|---
-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | Connection timeout, the server is not listening on the port or there is a network failure, you can read $errCode to get the specific network error code
-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | Request timeout, the server did not return a response within the specified timeout time
-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | After the client's request is sent, the server forcefully disconnects the connection
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | Client send failed (this constant is available in Swoole version >= `v4.5.9`, for versions less than this, please use the status code)
### getBody()

Get the body content of the HTTP response.

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```
### close()

Close the connection.

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> After calling `close`, if you call methods like `get`, `post` again, Swoole will help you reconnect to the server.
### execute()

A lower-level `HTTP` request method that requires calling interfaces such as [setMethod](/coroutine_client/http_client?id=setmethod) and [setData](/coroutine_client/http_client?id=setdata) in the code to set the request method and data.

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **Example**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```
## Functions

To facilitate the use of `Coroutine\Http\Client`, three functions have been added:

Note: Available for Swoole version >= `v4.6.4`.
### request()

Initiate a request with a specified request method.

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```
### post()

Used to make a `POST` request.

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```
### get()

Used to make a `GET` request.

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```
### Usage Example

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
