# Coroutine\Http2\Client

Coroutine Http2 Client
```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $domain = 'www.zhihu.com';
    $cli = new Client($domain, 443, true);
    $cli->set([
        'timeout' => -1,
        'ssl_host_name' => $domain
    ]);
    $cli->connect();
    $req = new Request();
    $req->method = 'POST';
    $req->path = '/api/v4/answers/300000000/voters';
    $req->headers = [
        'host' => $domain,
        'user-agent' => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip'
    ];
    $req->data = '{"type":"up"}';
    $cli->send($req);
    $response = $cli->recv();
    var_dump(assert(json_decode($response->data)->error->code === 10002));
});
```
## Methods
### __construct()

Constructor method.

```php
Swoole\Coroutine\Http2\Client::__construct(string $host, int $port, bool $open_ssl = false): void
```

  * **Parameters**

    * **`string $host`**
      * **Description**: The IP address of the target host. If `$host` is a domain name, a `DNS` query will be performed at the bottom layer.
      * **Default Value**: None
      * **Other Values**: None

    * **`int $port`**
      * **Description**: The target port. Generally, `80` for `Http` and `443` for `Https`.
      * **Default Value**: None
      * **Other Values**: None

    * **`bool $open_ssl`**
      * **Description**: Whether to enable `TLS/SSL` tunnel encryption. Should be set to `true` for `https` websites.
      * **Default Value**: `false`
      * **Other Values**: `true`

  * **Note**

    !> - If you need to request external URLs, please adjust the `timeout` to a larger value, refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)  
    - `$ssl` depends on `openssl` and must be enabled when compiling `Swoole` [--enable-openssl](/environment?id=compilation-options)
### set()

Set client parameters, for other detailed configuration items, please refer to [Swoole\Client::set](/client?id=configuration) configuration options

```php
Swoole\Coroutine\Http2\Client->set(array $options): void
```
### connect()

Connect to the target server. This method takes no parameters.

After calling `connect`, the underlying system will automatically perform [coroutine scheduling](/coroutine?id=coroutine-scheduling). The `connect` function will return when the connection is successful or fails. Once the connection is established, you can use the `send` method to send requests to the server.

```php
Swoole\Coroutine\Http2\Client->connect(): bool
```

  * **Return Value**

    * Returns `true` if the connection is successful.
    * Returns `false` if the connection fails. You can check the `errCode` property to get the error code.
### stats()

Get the flow status.

```php
Swoole\Coroutine\Http2\Client->stats([$key]): array|bool
```

  * **Example**

```php
var_dump($client->stats(), $client->stats()['local_settings'], $client->stats('local_settings'));
```
### isStreamExist()

Determine whether the specified stream exists.

```php
Swoole\Coroutine\Http2\Client->isStreamExist(int $stream_id): bool
```
### send()

Send a request to the server, and the underlying layer will automatically establish an `Http2` `stream`. Multiple requests can be sent simultaneously.

```php
Swoole\Coroutine\Http2\Client->send(Swoole\Http2\Request $request): int|false
```

  * **Parameters** 

    * **`Swoole\Http2\Request $request`**
      * **Functionality**: send the Swoole\Http2\Request object
      * **Default**: N/A
      * **Other values**: N/A

  * **Return Values**

    * Returns the stream ID if successful, where the ID increments by odd numbers starting from `1`
    * Returns `false` if failed

  * **Tips**

    * **Request object**

      !> The `Swoole\Http2\Request` object has no methods; you write request-related information by setting object properties.

      * `headers`: array, `HTTP` headers
      * `method`: string, set the request method, such as `GET`, `POST`
      * `path`: string, set the URL path, such as `/index.php?a=1&b=2`, must start with /
      * `cookies`: array, set the `COOKIES`
      * `data`: sets the request body; if it is a string, it will be sent directly as `RAW form-data`
      * If `data` is an array, the underlying layer will automatically pack it into `x-www-form-urlencoded` format for the `POST` content and set the `Content-Type` as `application/x-www-form-urlencoded`
      * `pipeline`: boolean, if set to `true`, after sending the `$request`, the `stream` will not be closed, allowing you to continue writing data content

    * **pipeline**

      * By default, the `send` method ends the current `Http2 Stream` after sending the request. Enabling `pipeline` will keep the stream flow, allowing multiple calls to the `write` method to send data frames to the server. Please refer to the `write` method.
### write()

Send more data frames to the server, you can call write multiple times to write data frames to the same stream.

```php
Swoole\Coroutine\Http2\Client->write(int $streamId, mixed $data, bool $end = false): bool
```

  * **Parameters** 

    * **`int $streamId`**
      * **Description**: Stream ID returned by the `send` method
      * **Default Value**: None
      * **Other Values**: None

    * **`mixed $data`**
      * **Description**: Content of the data frame, can be a string or an array
      * **Default Value**: None
      * **Other Values**: None

    * **`bool $end`**
      * **Description**: Whether to close the stream
      * **Default Value**: `false`
      * **Other Values**: `true`

  * **Usage Example**

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9518);
    $cli->set(['timeout' => 1]);
    var_dump($cli->connect());

    $req3 = new Request();
    $req3->path = "/index.php";
    $req3->headers = [
        'host' => "localhost",
        "user-agent" => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip',
    ];
    $req3->pipeline = true;
    $req3->method = "POST";
    $streamId = $cli->send($req3);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    //end stream
    $cli->write($streamId, ['int' => rand(1000, 9999), 'end' => true], true);
    var_dump($cli->recv());
    $cli->close();
});
```

!> If you want to use `write` to send data frames in segments, you must set `$request->pipeline` to `true` when making the `send` request.  
Once a data frame with `end` set to `true` is sent, the stream will be closed, and you cannot call `write` to send more data to this stream.
### recv()

Receives a request.

!> Calling this method will trigger [coroutine scheduling](/coroutine?id=coroutine-scheduling)

```php
Swoole\Coroutine\Http2\Client->recv(float $timeout): Swoole\Http2\Response;
```

  * **Parameters** 

    * **`float $timeout`**
      * **Function**: Sets the timeout period, refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Value Unit**: seconds【Supports floating-point numbers, e.g., `1.5` represents `1s` + `500ms`】
      * **Default Value**: None
      * **Other Values**: None

  * **Return Value**

Returns Swoole\Http2\Response object upon success

```php
/**@var $resp Swoole\Http2\Response */
var_dump($resp->statusCode); // The HTTP status code sent by the server, such as 200, 502, etc.
var_dump($resp->headers); // Header information sent by the server
var_dump($resp->cookies); // Cookie information set by the server
var_dump($resp->set_cookie_headers); // Raw COOKIE information returned by the server, including domain and path items
var_dump($resp->data); // Response body sent by the server
```

!> Prior to Swoole version < [v4.0.4](/version/bc?id=_404), the `data` attribute was `body`; and prior to Swoole version < [v4.0.3](/version/bc?id=_403), `headers` and `cookies` were in singular form.
### read()

Similar to `recv()`, the difference is that for responses of `pipeline` type, `read` allows to read data in multiple times. Each time reading a part of the content to save memory or to quickly receive push information, while `recv` always concatenates all frames into a complete response before returning.

!> Calling this method will result in [coroutine scheduling](/coroutine?id=Coroutine_Scheduling)

```php
Swoole\Coroutine\Http2\Client->read(float $timeout): Swoole\Http2\Response;
```

  * **Parameters**

    * **`float $timeout`**
      * **Function**: Set the timeout period, refer to [client timeout rules](/coroutine_client/init?id=Timeout_Rules)
      * **Unit**: Seconds [Supports floating point numbers, such as `1.5` representing `1s` + `500ms`]
      * **Default Value**: None
      * **Other Values**: None

  * **Return Value**

    Upon success, it returns a Swoole\Http2\Response object.
### goaway()

The GOAWAY frame is used to initiate connection closure or send a signal of a severe error state.

```php
Swoole\Coroutine\Http2\Client->goaway(int $error_code = SWOOLE_HTTP2_ERROR_NO_ERROR, string $debug_data): bool
```
### ping()

A PING frame is a mechanism used to measure the minimum round trip time from the sender and to determine if an idle connection is still alive.

```php
Swoole\Coroutine\Http2\Client->ping(): bool
```
### close()

Close the connection.

```php
Swoole\Coroutine\Http2\Client->close(): bool
```
