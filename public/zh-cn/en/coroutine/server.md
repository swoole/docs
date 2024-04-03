# TCP Server

`Swoole\Coroutine\Server` is a fully [coroutine](/coroutine) class used to create coroutine `TCP` servers, supporting both TCP and [unixSocket](/learn?id=What is IPC) types.

Differences from the `Server` module:

* Dynamically creating and destroying - ports can be dynamically listened to, and servers can be dynamically closed during runtime.
* The process of handling connections is fully synchronous, allowing programs to sequentially handle `Connect`, `Receive`, and `Close` events.

!> Available in version 4.4 and above.
## Short Name

You can use the short name `Co\Server`.
## Methods
### __construct()

?> **Constructor method.**

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Parameters**

    * **`string $host`**
      * **Functionality**: The address to listen on.
      * **Default value**: None
      * **Other values**: None

    * **`int $port`**
      * **Functionality**: The port to listen on. If set to 0, the system will assign a random port.
      * **Default value**: None
      * **Other values**: None

    * **`bool $ssl`**
      * **Functionality**: Whether to enable SSL encryption.
      * **Default value**: `false`
      * **Other values**: `true`

    * **`bool $reuse_port`**
      * **Functionality**: Whether to enable port reuse, the effect is the same as the configuration in [this section](/server/setting?id=enable_reuse_port).
      * **Default value**: `false`
      * **Other values**: `true`
      * **Version impact**: Swoole version >= v4.4.4

  * **Tips**

    * **The $host parameter supports 3 formats**

      * `0.0.0.0/127.0.0.1`: IPv4 address
      * `::/::1`: IPv6 address
      * `unix:/tmp/test.sock`: [UnixSocket](/learn?id=什么是IPC) address

    * **Exceptions**

      * `Swoole\Exception` exception will be thrown in case of parameter errors, failed binding of address and port, and failed `listen`.  
### set()

?> **Set protocol processing parameters.**

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **Configuration Parameters**

    * The parameter `$options` must be a one-dimensional associative indexed array, exactly the same as the configuration items accepted by the [setprotocol](/coroutine_client/socket?id=setprotocol) method.

    !> Parameters must be set before calling the [start()](/coroutine/server?id=start) method.

    * **Length Protocol**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      'package_length_offset' => 0,
      'package_body_offset' => 4,
    ]);
    ```

    * **SSL Certificate Settings**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      'ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```
### handle()

?> **Set the connection handler function.**

!> Must set the handler function before [start()](/coroutine/server?id=start).

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **Parameters** 

    * **`callable $fn`**
      * **Description**: Set the connection handler function
      * **Default**: None
      * **Other values**: None
      
  * **Examples** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            $data = $conn->recv();
        }
    });
    ```

    !> -After successful `Accept` (connection establishment), the server will automatically create a [coroutine](/coroutine?id=coroutine-scheduling) and execute `$fn`;  
    -`$fn` is executed in a new child coroutine space, so there is no need to create a coroutine within the function again;  
    -`$fn` takes one parameter of type [Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection) object;  
    -You can use [exportSocket()](/coroutine/server?id=exportsocket) to get the current connection's Socket object.
### shutdown()

?> **Terminate the server.** 

?> Supports multiple calls to `start` and `shutdown` at the lower level.

```php
Swoole\Coroutine\Server->shutdown(): bool
```
### start()

?> **Start the server.**

```php
Swoole\Coroutine\Server->start(): bool
```

  * **Return Value**
    * If the start fails, it will return `false` and set the `errCode` attribute.
    * Upon successful start, it will enter a loop to `Accept` connections.
    * After an `Accept` (connection establishment), a new coroutine will be created, and the function specified in the `handle` method will be called within that coroutine.

  * **Error Handling**
    * When encountering a `Too many open file` error during `Accept` or when unable to create a child coroutine, there will be a one-second pause before continuing to `Accept`.
    * In the event of an error, the `start()` method will return, and the error message will be reported as a `Warning`.
## Objects
### Coroutine\Server\Connection

The `Swoole\Coroutine\Server\Connection` object provides four methods:
#### recv()

Receive data, if protocol processing is set up, will return a complete package each time

```php
function recv(float $timeout = 0)
```
#### send()

Sending data

```php
function send(string $data)
```
#### close()

Close the connection

```php
function close(): bool
```
#### exportSocket()

Get the current connected Socket object. You can call more low-level methods, please refer to [Swoole\Coroutine\Socket](/coroutine_client/socket)

```php
function exportSocket(): Swoole\Coroutine\Socket
```
## Complete Example

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

// Multi-process management module
$pool = new Process\Pool(2);
// Automatically create a coroutine for each OnWorkerStart callback
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    // Each process listens on port 9501
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    // Shutdown service when receiving signal 15
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    // Receive new connection requests and automatically create a coroutine
    $server->handle(function (Connection $conn) {
        while (true) {
            // Receive data
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            // Send data
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    // Start listening on the port
    $server->start();
});
$pool->start();
```

!> If running in a Cygwin environment, please change to single process. `$pool = new Swoole\Process\Pool(1);`
