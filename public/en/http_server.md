# Http\Server

The `Http\Server` inherits from [Server](/server/init), so all the APIs and configuration options provided by `Server` can be used, and the process model is the same. Please refer to the [Server](/server/init) section.

Support for built-in HTTP servers allows you to write a high-concurrency, high-performance, asynchronous IO multi-process HTTP server in just a few lines of code.

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

By using the `Apache bench` tool for stress testing, on a normal PC with an `Inter Core-I5 4-core + 8G memory`, the `Http\Server` can achieve nearly `110,000 QPS`.

Far surpassing `PHP-FPM`, `Golang`, and the built-in `HTTP` servers of `Node.js`. The performance is almost equivalent to `Nginx` for static file handling.

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **Using the HTTP2 protocol**

  * Using the `HTTP2` protocol under `SSL` requires the installation of `openssl`, and a higher version of `openssl` must support `TLS1.2`, `ALPN`, and `NPN`
  * Compilation requires enabling [--enable-http2](/environment?id=compile-options)
  * Starting from Swoole 5, the `http2` protocol is enabled by default.

```shell
./configure --enable-openssl --enable-http2
```

Set the `open_http2_protocol` of the `HTTP` server to `true`

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Nginx + Swoole Configuration**

!> Due to the incomplete support for the `HTTP` protocol by `Http\Server`, it is recommended to use it only as an application server for handling dynamic requests and to add `Nginx` as a proxy on the front end.

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> You can get the client's real `IP` by reading `$request->header['x-real-ip']`.
## Methods
### on()

?> **Register event callback functions.**

?> The difference between this and [Server's callbacks](/server/events) is:

  * `Http\Server->on` does not support setting callbacks for [onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)
  * `Http\Server->on` also supports a new event type `onRequest`, which triggers when a client request is received.

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

This function will be called once a complete HTTP request is received. The callback takes `2` parameters:

* [Swoole\Http\Request](/http_server?id=httpRequest), the HTTP request object containing headers, GET/POST data, cookies, etc.
* [Swoole\Http\Response](/http_server?id=httpResponse), the HTTP response object enabling HTTP operations like cookies, headers, status, etc.

!> When the [onRequest](/http_server?id=on) callback function returns, the `$request` and `$response` objects will be destroyed at the underlying level.
### start()

?> **Start HTTP server**

?> Start listening on the port and receive new `HTTP` requests.

```php
Swoole\Http\Server->start();
```
## Swoole\Http\Request

`HTTP` request object, which contains information related to the client's `HTTP` request, including `GET`, `POST`, `COOKIE`, `Header`, etc.

!> Do not use the `&` symbol to reference the `Http\Request` object
### header

?> **Header information of the HTTP request. It's an array, and all keys are lowercase.**

```php
Swoole\Http\Request->header: array
```

* **Example**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```
### server

?> **Server information related to `HTTP` requests.**

?> Equivalent to the `$_SERVER` array in `PHP`. It includes information such as the method of the `HTTP` request, the URL path, client IP address, etc.

```php
Swoole\Http\Request->server: array
```

All keys in the array are lowercase and consistent with the `$_SERVER` array in `PHP`.

* **Example**

```php
echo $request->server['request_time'];
```

key | Description
---|---
query_string | Query parameters in the request, e.g., `id=1&cid=2`. If there are no `GET` parameters, this item does not exist.
request_method | The request method, e.g., `GET/POST`.
request_uri | The access address without `GET` parameters, e.g., `/favicon.ico`.
path_info | Same as `request_uri`.
request_time | `request_time` is set in the `Worker`, in [SWOOLE_PROCESS](/learn?id=swoole_process) mode there is a `dispatch` process, so there may be a deviation from the actual packet reception time. Especially when the request volume exceeds the server's processing capacity, `request_time` may lag far behind the actual packet reception time. You can use the `$server->getClientInfo` method to get `last_time` for accurate packet reception time.
request_time_float | Timestamp when the request started, in microseconds, `float` type, e.g., `1576220199.2725`.
server_protocol | The server protocol version number, for `HTTP` it is: `HTTP/1.0` or `HTTP/1.1`, for `HTTP2` it is: `HTTP/2`.
server_port | The port the server is listening on.
remote_port | The client's port.
remote_addr | The client's IP address.
master_time | Time of the last communication connection.
### get

?> **`GET` parameters of `HTTP` request, equivalent to `$_GET` in `PHP`, in an array format.**

```php
Swoole\Http\Request->get: array
```

* **Example**

```php
// For example: index.php?hello=123
echo $request->get['hello'];
// Get all GET parameters
var_dump($request->get);
```

* **Note**

!> To prevent `HASH` attacks, the maximum allowed `GET` parameters is not more than `128`
### post

?> **The `POST` parameters of the `HTTP` request are in array format**

```php
Swoole\Http\Request->post: array
```

* **Example**

```php
echo $request->post['hello'];
```

* **Note**

!> - The combined size of `POST` and `Header` should not exceed the setting of [package_max_length](/server/setting?id=package_max_length), otherwise it will be considered as a malicious request  
- The maximum number of `POST` parameters should not exceed `128`
### cookie

?> **`COOKIE` information carried by `HTTP` requests, in the format of an array of key-value pairs.**

```php
Swoole\Http\Request->cookie: array
```

* **Example**

```php
echo $request->cookie['username'];
```
### Files

?> **Upload file information.**

?> It is a two-dimensional array with the type of `form` and the name as `key`. Same as `$_FILES` in `PHP`. The maximum file size cannot exceed the value set by [package_max_length](/server/setting?id=package_max_length). Because Swoole consumes memory when parsing messages, the larger the message, the more memory it consumes, so please do not use `Swoole\Http\Server` to handle large file uploads or design user-initiated resumable download functionality.

```php
Swoole\Http\Request->files: array
```

* **Example**

```php
Array
(
    [name] => facepalm.jpg // The file name passed when uploaded from the browser
    [type] => image/jpeg // MIME type
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // Uploaded temporary file, file name starting with /tmp/swoole.upfile
    [error] => 0
    [size] => 15476 // File size
)
```

* **Note**

!> When the `Swoole\Http\Request` object is destroyed, the uploaded temporary file will be automatically deleted
### getContent()

!> Available in Swoole version >= `v4.5.0`, an alias `rawContent` can be used in lower versions (this alias will be permanently retained for backward compatibility).

?> **Get the raw `POST` body.**

?> Used for non-`application/x-www-form-urlencoded` format of HTTP `POST` requests. Returns the raw `POST` data, this function is equivalent to `PHP`'s `fopen('php://input')`.

```php
Swoole\Http\Request->getContent(): string|false
```

  * **Return Value**

    * Returns the message if successful, returns `false` if the context connection does not exist.

!> In some cases, the server does not need to parse HTTP `POST` request parameters. By using the [http_parse_post](/http_server?id=http_parse_post) configuration, `POST` data parsing can be disabled.
### getData()

?> **Get the complete original `Http` request message, note that it cannot be used under `Http2`. Including `Http Header` and `Http Body`**

```php
Swoole\Http\Request->getData(): string|false
```

  * **Return value**

    * Returns the message if successful. Returns `false` if the context connection does not exist or operating in `Http2` mode.
### create()

?> **Create a `Swoole\Http\Request` object.**

!> Available in Swoole version >= `v4.6.0`

```php
Swoole\Http\Request->create(array $options): Swoole\Http\Request
```

  * **Parameters**

    * **`array $options`**
      * **Function**: Optional parameter used to configure the `Request` object

| Parameter                                          | Default Value | Description                                                     |
| --------------------------------------------------- | ------------- | --------------------------------------------------------------- |
| [parse_cookie](/http_server?id=http_parse_cookie)   | true          | Set whether to parse `Cookie`                                   |
| [parse_body](/http_server?id=http_parse_post)       | true          | Set whether to parse `Http Body`                                 |
| [parse_files](/http_server?id=http_parse_files)     | true          | Set the switch for parsing uploaded files                        |
| enable_compression                                 | true          | Set whether to enable compression, default to false if server does not support compressed messages |
| compression_level                                  | 1             | Set compression level, range is 1-9, higher level compresses more but consumes more CPU |
| upload_tmp_dir                                     | /tmp          | Temp file storage location for file uploads                      |

  * **Return Value**

    * Returns a `Swoole\Http\Request` object

* **Example**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```
### parse()

?> **Parses the HTTP request data package and returns the length of the successfully parsed data package.**

!> Available in Swoole version >= `v4.6.0`

```php
Swoole\Http\Request->parse(string $data): int|false
```

  * **Parameters**

    * **`string $data`**
      * The message to be parsed

  * **Return Value**

    * Returns the length of the successfully parsed data on success, `false` if the connection context does not exist or if the context has already ended.
### isCompleted()

?> **Check if the current `HTTP` request data packet has reached the end.**

!> Available since Swoole version >= `v4.6.0`

```php
Swoole\Http\Request->isCompleted(): bool
```

  * **Return value**

    * `true` means it is the end, `false` means the connection context has ended or has not reached the end

* **Example**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n";
$data .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n";
$data .= "Accept-Encoding: gzip, deflate, br\r\n";
$data .= "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,ja;q=0.6\r\n";
$data .= "Cookie: env=pretest; phpsessid=fcccs2af8673a2f343a61a96551c8523d79ea; username=hantianfeng\r\n";

/** @var Request $req */
$req = Request::create(['parse_cookie' => false]);
var_dump($req);

var_dump($req->isCompleted());
var_dump($req->parse($data));

var_dump($req->parse("\r\n"));
var_dump($req->isCompleted());

var_dump($req);
// Cookie parsing is disabled, so it will be null
var_dump($req->cookie);
```
### getMethod()

?> **Get the current `HTTP` request method.**

!> Available since Swoole version `v4.6.2`

```php
Swoole\Http\Request->getMethod(): string|false
```

  * **Return Value**

    * Returns the uppercase request method if successful, `false` indicates that the connection context does not exist.

```php
var_dump($request->server['request_method']);
var_dump($request->getMethod());
```
## Swoole\Http\Response

`HTTP` response object, used to send `HTTP` responses by calling methods of this object.

When the `Response` object is destroyed and no `HTTP` response is sent by calling [end](/http_server?id=end), the underlying system will automatically execute `end("")`.

Please do not use the `&` symbol to reference the `Http\Response` object.
### header() :id=setheader

?> **Set the Header information of the HTTP response** [alias `setHeader`]

```php
Swoole\Http\Response->header(string $key, string $value, bool $format = true): bool;
```

* **Parameters** 

  * **`string $key`**
    * **Function**: Key of the HTTP header
    * **Default**: None
    * **Other values**: None

  * **`string $value`**
    * **Function**: Value of the HTTP header
    * **Default**: None
    * **Other values**: None

  * **`bool $format`**
    * **Function**: Whether to format the `Key` according to HTTP conventions [defaults to `true` and will be formatted automatically]
    * **Default**: `true`
    * **Other values**: None

* **Return Value** 

  * Returns `false` if the setting fails
  * Returns `true` if the setting is successful

* **Note**

   - The `header` setting must be done before the `end` method
   - The `$key` must strictly comply with HTTP conventions, with the first letter of each word capitalized, no Chinese characters, underscores, or other special characters
   - The `$value` must be provided
   - If `$ucwords` is set to `true`, the underlying system will automatically format `$key` according to conventions
   - Overriding a header with the same `$key` will replace the previous one with the latest setting
   - If the client sets `Accept-Encoding`, the server cannot set `Content-Length` in the response. If `Swoole` detects this, it will ignore the `Content-Length` value and issue a warning
   - When `Content-Length` is set in the response, `Swoole` will issue a warning if `Swoole\Http\Response::write()` is called, because it will ignore the `Content-Length` value

!> For Swoole version >= `v4.6.0`, it supports overriding headers with the same `$key` and `$value` can be of multiple types such as `array`, `object`, `int`, `float`. The underlying system will perform a `toString` conversion and remove trailing spaces and newlines.

* **Example**

```php
$response->header('content-type', 'image/jpeg', true);

$response->header('Content-Length', '100002 ');
$response->header('Test-Value', [
    "a\r\n",
    'd5678',
    "e  \n ",
    null,
    5678,
    3.1415926,
]);
$response->header('Foo', new SplFileInfo('bar'));
```
### trailer()

?> **Attach `Header` information to the end of the `HTTP` response, only available in `HTTP2`, used for message integrity check, digital signatures, etc.**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **Parameters** 

  * **`string $key`**
    * **Functionality**: Key of the `HTTP` header
    * **Default Value**: None
    * **Other Values**: None

  * **`string $value`**
    * **Functionality**: Value of the `HTTP` header
    * **Default Value**: None
    * **Other Values**: None

* **Return Value** 

  * Returns `false` if setting fails
  * Returns `true` if setting is successful

* **Note**

  !> Overwriting the same `$key` header multiple times will replace it with the last one.

* **Example**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```
### cookie()

?> **Set the `cookie` information of the `HTTP` response. Alias `setCookie`. The parameters of this method are consistent with `PHP`'s `setcookie`.**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **Parameters** 

    * **`string $key`**
      * **Function**：`Key` of the `Cookie`
      * **Default**：N/A
      * **Other values**：N/A

    * **`string $value`**
      * **Function**：Value of the `Cookie`
      * **Default**：N/A
      * **Other values**：N/A
  
    * **`int $expire`**
      * **Function**：`Expiration time` of the `Cookie`
      * **Default**：0, never expires
      * **Other values**：N/A

    * **`string $path`**
      * **Function**：`Server path for the Cookie`
      * **Default**：/
      * **Other values**：N/A

    * **`string $domain`**
      * **Function**：`Domain for the Cookie`
      * **Default**：''
      * **Other values**：N/A

    * **`bool $secure`**
      * **Function**：`Whether to transmit the Cookie via a secure HTTPS connection`
      * **Default**：''
      * **Other values**：N/A

    * **`bool $httponly`**
      * **Function**：`Allow or disallow JavaScript access to a Cookie with the HttpOnly attribute`, `true` means disallowed, `false` means allowed
      * **Default**：false
      * **Other values**：N/A

    * **`string $samesite`**
      * **Function**：`Restrict third-party Cookies to reduce security risks`, optional values are `Strict`, `Lax`, `None`
      * **Default**：''
      * **Other values**：N/A

    * **`string $priority`**
      * **Function**：`Cookie priority, lower priority Cookies will be deleted first if the number of Cookies exceeds the limit`, optional values are `Low`, `Medium`, `High`
      * **Default**：''
      * **Other values**：N/A
  
  * **Return Value** 

    * If setting fails, return `false`
    * If setting succeeds, return `true`

* **Note**

  !> - The `cookie` setting must be done before the [end](/http_server?id=end) method  
  - The `$samesite` parameter is supported from version `v4.4.6`, the `$priority` parameter is supported from version `v4.5.8`  
  - `Swoole` will automatically urlencode the `$value`, you can use the `rawCookie()` method to disable encoding on the `$value`  
  - `Swoole` allows setting multiple `COOKIE` with the same `$key`
### rawCookie()

?> **Set the `cookie` information of the `HTTP` response**

!> The parameters of `rawCookie()` are the same as the `cookie()` function mentioned above, except that it does not perform encoding.
### status()

?> **Send `Http` status code. Alias `setStatusCode()`**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **Parameters**

  * **`int $http_status_code`**
    * **Function**: Set `HttpCode`
    * **Default**: None
    * **Other values**: None

  * **`string $reason`**
    * **Function**: Status code reason
    * **Default**: ''
    * **Other values**: None

  * **Return Value**

    * If the setting fails, return `false`
    * If the setting succeeds, return `true`

* **Tips**

  * If only the first parameter `$http_status_code` is passed, it must be a valid `HttpCode`, such as `200`, `502`, `301`, `404`, etc. Otherwise, it will be set to a `200` status code
  * If the second parameter `$reason` is set, `$http_status_code` can be any numerical value, including undefined `HttpCode`, such as `499`
  * The `status` method must be executed before [$response->end()](/http_server?id=end) is called
### gzip()

!> This method was deprecated in `4.1.0` or higher versions. Please refer to [http_compression](/http_server?id=http_compression); the `gzip` method has been replaced by the `http_compression` configuration option in newer versions.  
The main reason is that the `gzip()` method does not check the `Accept-Encoding` header sent by the browser client. If the client does not support `gzip` compression, forcing it may cause the client to be unable to decompress.  
The new `http_compression` configuration option will automatically choose whether to compress based on the client's `Accept-Encoding` header and will automatically select the best compression algorithm.

?> **Enable `HTTP GZIP` compression. Compression can reduce the size of `HTML` content, effectively save network bandwidth, and improve response time. `gzip` must be executed before sending content in `write/end` to avoid errors.**
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **Parameters** 
   
     * **`int $level`**
       * **Description**: Compression level; the higher the level, the smaller the compressed size, but it consumes more `CPU`. 
       * **Default**: 1
       * **Other values**: `1-9`

!> After calling the `gzip` method, the underlying system will automatically add `HTTP` encoding headers. Additional `HTTP` headers should not be set in PHP code; images in `jpg/png/gif` formats have already been compressed and do not need to be compressed again.

!> The `gzip` functionality depends on the `zlib` library. When compiling swoole, the underlying system will check if `zlib` exists. If it does not exist, the `gzip` method will be unavailable. You can install the `zlib` library using `yum` or `apt-get`:

```shell
sudo apt-get install libz-dev
```
### redirect()

?> **Sends an `Http` redirect. Calling this method will automatically `end` and end the response.**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **Parameters** 
    * **`string $url`**
      * **Description**: The new address to redirect to, sent as the `Location` header
      * **Default Value**: None
      * **Other Values**: None

    * **`int $http_code`**
      * **Description**: Status code [default is `302` for temporary redirect, pass `301` for permanent redirect]
      * **Default Value**: `302`
      * **Other Values**: None

  * **Return Value** 

    * Returns `true` if successful, `false` if failed or connection context does not exist

* **Example**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```
### write()

?> **Enable `Http Chunk` segmentation to send response content to the browser.**

?> You can refer to the standard documentation of `Http` for more information about `Http Chunk`.

```php
Swoole\Http\Response->write(string $data): bool
```

  * **Parameters** 

    * **`string $data`**
      * **Function**: The data content to send.【The maximum length should not exceed `2M`, controlled by the [buffer_output_size](/server/setting?id=buffer_output_size) configuration item】
      * **Default Value**: None
      * **Other values**: None

  * **Return Value** 
  
    * If the call is successful, it returns `true`; if the call fails or the connection context does not exist, it returns `false`.

* **Tips**

  * After using `write` to send data in segments, the [end](/http_server?id=end) method will not accept any parameters. Calling `end` will simply send a `Chunk` of length `0` to indicate the completion of data transmission.
  * If `Content-Length` is set using the Swoole\Http\Response::header() method and then this method is called, `Swoole` will ignore the setting of `Content-Length` and throw a warning.
  * This function cannot be used with `Http2`, otherwise a warning will be thrown.
  * If the client supports response compression, `Swoole\Http\Response::write()` will force compression to be disabled.
### sendfile()

?> **Send file to the browser.**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **Parameters** 

    * **`string $filename`**
      * **Function**: The name of the file to send. [If the file does not exist or has no access rights, `sendfile` will fail.]
      * **Default Value**: None
      * **Other Values**: None

    * **`int $offset`**
      * **Function**: The offset of the file to upload. [Can specify to start transferring data from the middle part of the file. This feature can be used to support resumable file uploads.]
      * **Default Value**: `0`
      * **Other Values**: None

    * **`int $length`**
      * **Function**: The size of the data to send.
      * **Default Value**: The size of the file
      * **Other Values**: None

  * **Return Value** 

      * If the call is successful, it returns `true`; if the call fails or the connection context does not exist, it returns `false`.

* **Tips**

  * The underlying system cannot infer the MIME format of the file to be sent, so application code needs to specify the `Content-Type`.
  * Do not use the `write` method to send `Http-Chunk` before calling `sendfile`.
  * After calling `sendfile`, the underlying system will automatically execute `end`.
  * `sendfile` does not support `gzip` compression.

* **Example**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```
### end()

?> **Sending the `Http` response body and ending the request processing.**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **Parameters**
  
    * **`string $html`**
      * **Function**: Content to be sent
      * **Default value**: None
      * **Other values**: None

  * **Return Value**

    * If successful, returns `true`; if failed or connection context does not exist, returns `false`

* **Tips**

  * `end` can only be called once. If you need to send data to the client multiple times, please use the [write](/http_server?id=write) method
  * If the client has enabled [KeepAlive](/coroutine_client/http_client?id=keep_alive), the connection will be maintained, and the server will wait for the next request
  * If the client has not enabled `KeepAlive`, the server will cut off the connection
  * The content to be sent by `end` is limited by [output_buffer_size](/server/setting?id=buffer_output_size), which defaults to `2M`. If it exceeds this limit, the response will fail with the following error:

!> The solution is to use [sendfile](/http_server?id=sendfile), [write](/http_server?id=write), or adjust [output_buffer_size](/server/setting?id=buffer_output_size)

```bash
WARNING finish (ERRNO 1203): The length of data [262144] exceeds the output buffer size [131072], please use the sendfile, chunked transfer mode, or adjust the output_buffer_size
```
### detach()

?> **Separate the response object.** After using this method, the `$response` object will not be automatically destroyed when it ends, and it is used in conjunction with [Http\Response::create](/http_server?id=create) and [Server->send](/server/methods?id=send).

```php
Swoole\Http\Response->detach(): bool
```

  * **Return Value** 

    * If successful, returns `true`; if failed or connection context does not exist, returns `false`

* **Example** 

  * **Cross-process response**

  ?> In some cases, it is necessary to send a response to the client in a [Task process](/learn?id=taskworker-process). In this case, `detach` can be used to make the `$response` object independent. The `$response` object can be reconstructed in the [Task process](/learn?id=taskworker-process), and an HTTP request response can be initiated.

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->set(['task_worker_num' => 1, 'worker_num' => 1]);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->task(strval($resp->fd));
  });

  $http->on('finish', function () {
      echo "task finish";
  });

  $http->on('task', function ($serv, $task_id, $worker_id, $data) {
      var_dump($data);
      $resp = Swoole\Http\Response::create($data);
      $resp->end("in task");
      echo "async task\n";
  });

  $http->start();
  ```

  * **Send arbitrary content**

  ?> In some special scenarios, it is necessary to send specific response content to the client. The `end` method provided by the `Http\Response` object cannot meet the requirements. You can use `detach` to separate the response object, then assemble the HTTP protocol response data on your own, and use `Server->send` to send the data.

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```
### create()

?> **Construct a new `Swoole\Http\Response` object.**

!> Before using this method, be sure to call the `detach` method to detach the old `$response` object, otherwise it may result in sending response content to the same request twice.

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

  * **Parameters** 

    * **`int $server`**
      * **Function**: `Swoole\Server` or `Swoole\Coroutine\Socket` object, an array (containing at most two elements: the first is a `Swoole\Server` object, and the second is a `Swoole\Http\Request` object), or a file descriptor
      * **Default**: -1
      * **Other values**: None

    * **`int $fd`**
      * **Function**: File descriptor. If the `$server` parameter is a `Swoole\Server` object, then `$fd` is required.
      * **Default**: -1
      * 
      * **Other values**: None

  * **Return Value** 

    * Returns a new `Swoole\Http\Response` object on success, and `false` on failure

* **Example**

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->detach();
    // Example 1
    $resp2 = Swoole\Http\Response::create($req->fd);
    // Example 2
    $resp2 = Swoole\Http\Response::create($http, $req->fd);
    // Example 3
    $resp2 = Swoole\Http\Response::create([$http, $req]);
    // Example 4
    $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, IPPROTO_IP);
    $socket->connect('127.0.0.1', 9501)
    $resp2 = Swoole\Http\Response::create($socket);
    $resp2->end("hello world");
});

$http->start();
```
### isWritable()

?> **Determine if the `Swoole\Http\Response` object has been ended (`end`) or detached (`detach`).**

```php
Swoole\Http\Response->isWritable(): bool
```

  * **Return Value**

    * Returns `true` if the `Swoole\Http\Response` object has not been ended or detached, otherwise returns `false`.


!> Available since Swoole version >= `v4.6.0`

* **Example**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->isWritable()); // true
    $resp->end('hello');
    var_dump($resp->isWritable()); // false
    $resp->setStatusCode(403); // http response is unavailable (maybe it has been ended or detached)
});

$http->start();
```
## Configuration options
### http_parse_cookie

?> **Configuration for the `Swoole\Http\Request` object, disabling `Cookie` parsing, will keep the raw `Cookies` information untouched in the `header`. Enabled by default**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```
### http_parse_post

?> **Configurations for `Swoole\Http\Request` object, setting the switch for POST message parsing, enabled by default**

* When set to `true`, it automatically parses the request body of `Content-Type: x-www-form-urlencoded` into the `$_POST` array.
* When set to `false`, it disables POST parsing.

```php
$server->set([
    'http_parse_post' => false,
]);
```
### http_parse_files

?> **Configured for `Swoole\Http\Request` object, set the upload file parsing switch. Enabled by default**

```php
$server->set([
    'http_parse_files' => false,
]);
```
### http_compression

?> **Configuration for the `Swoole\Http\Response` object to enable compression. Enabled by default.**

!> - `http-chunk` does not support separate compression for segments. If the [write](/http_server?id=write) method is used, compression will be forcibly disabled.  
- `http_compression` is available in `v4.1.0` or higher versions

```php
$server->set([
    'http_compression' => false,
]);
```

Currently, three compression formats are supported: `gzip`, `br`, and `deflate`. The underlying system will automatically select the compression method based on the `Accept-Encoding` header sent by the browser client (compression algorithm priority: `br` > `gzip` > `deflate`).

**Dependencies:**

`gzip` and `deflate` rely on the `zlib` library, which Swoole compiles will check for during installation.

You can install the `zlib` library using `yum` or `apt-get`:

```shell
sudo apt-get install libz-dev
```

The `br` compression format depends on Google's `brotli` library. To install it, please search for "install brotli on linux". During Swoole compilation, the underlying system will check for the existence of `brotli`.
### http_compression_level / compression_level / http_gzip_level

?> **Compression level, configuration for `Swoole\Http\Response` object**
  
!> `$level` Compression level, range from `1-9`, higher level results in smaller size after compression but consumes more `CPU`. Default is `1`, maximum is `9`
### http_compression_min_length / compression_min_length

?> **Set the minimum number of bytes for enabling compression for the `Swoole\Http\Response` object. Compression is only enabled for responses exceeding this value. The default is 20 bytes.**

!> Available for Swoole version >= `v4.6.3`

```php
$server->set([
    'compression_min_length' => 128,
]);
```
### upload_tmp_dir

?> **Set the temporary directory for uploading files. The directory must not exceed `220` bytes in length**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```
### upload_max_filesize

?> **Set the maximum size of uploaded files**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```
### enable_static_handler

Enable static file request handling, which needs to be used in conjunction with `document_root`. Default is `false`.
### http_autoindex

Enabling the `http autoindex` function, which is not enabled by default.
### http_index_files

Used in conjunction with `http_autoindex` to specify the list of files to be indexed.

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```
### http_compression_types / compression_types

?> **Configuration for setting response types to be compressed, applicable to `Swoole\Http\Response` object**

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Available in Swoole version `v4.8.12` or later
### static_handler_locations

?> **Set the paths for static handlers. Type is array, not enabled by default.**

!> Available in Swoole version >= `v4.4.0`

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* Similar to the `location` directive in `Nginx`, you can specify one or more paths as static paths. Only requests with a URL under the specified paths will enable the static file handler; otherwise, it will be considered a dynamic request.
* The `location` item must start with a forward slash (/).
* Supports multiple levels of paths, such as `/app/images`.
* When `static_handler_locations` is enabled, if the corresponding file for a request does not exist, it will directly return a 404 error.
### open_http2_protocol

?> **Enable `HTTP2` protocol parsing** 【Default value: `false`】

!> You need to enable the [--enable-http2](/environment?id=compile-options) option at compile time. Starting from `Swoole 5`, http2 is compiled by default.
### document_root

You need to set the root directory for static files and use it with `enable_static_handler`.

Please do not use this feature directly in a public network environment.

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // For versions below v4.4.0, this must be an absolute path
    'enable_static_handler' => true,
]);
```

- After setting `document_root` and enabling `enable_static_handler` to `true`, when the underlying layer receives an `HTTP` request, it will first check if the file exists under the `document_root` path. If it exists, it will directly send the file content to the client without triggering the [onRequest](/http_server?id=on) callback.
- When using the static file handling feature, you should isolate dynamic PHP code from static files and store static files in a specific directory.
### max_concurrency

?> **Can limit the maximum number of concurrent requests for the `HTTP1/2` service. After exceeding this number, a `503` error will be returned. The default value is 4294967295, which is the maximum value of an unsigned integer.**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```
### worker_max_concurrency

When one-click coroutine is enabled, the `worker` process will continuously receive requests. To avoid excessive pressure, we can set `worker_max_concurrency` to limit the number of requests executed by the `worker` process. When the number of requests exceeds this value, the `worker` process will temporarily store the excess requests in a queue. The default value is 4294967295, which is the maximum value of an unsigned int. If `worker_max_concurrency` is not set but `max_concurrency` is set, the underlying system will automatically set `worker_max_concurrency` equal to `max_concurrency`.

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

Swoole version >= `v5.0.0` is required.
### http2_header_table_size

?> Defines the maximum `header table` size for HTTP/2 network connections.

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```
### http2_enable_push

?> This configuration is used to enable or disable HTTP/2 push.

```php
$server->set([
  'http2_enable_push' => 0x2
])
```
### http2_max_concurrent_streams

?> Sets the maximum number of multiplexed streams accepted in each HTTP/2 network connection.

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```
### http2_init_window_size

?> Set the initial size of the HTTP/2 traffic control window.

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```
### http2_max_frame_size

?> Set the maximum size of the body of a single HTTP/2 protocol frame sent over an HTTP/2 network connection.

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```
### http2_max_header_list_size

?> Sets the maximum size of headers allowed in HTTP/2 streams.

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
