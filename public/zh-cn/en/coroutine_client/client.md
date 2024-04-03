# Coroutine TCP/UDP client

`Coroutine\Client` provides encapsulated code for `TCP`, `UDP`, and unixSocket transmission protocols for [Socket clients](/coroutine_client/socket), using `new Swoole\Coroutine\Client` is all that is needed.

* **Implementation Principle**
  
    * All methods of `Coroutine\Client` involving network requests will undergo coroutine scheduling by `Swoole`, which the business layer does not need to be aware of.
    * The usage and synchronous mode methods of `Coroutine\Client` are completely consistent with [Client](/client).
    * `connect` timeout setting also applies to `Connect`, `Recv`, and `Send` timeouts.

* **Inheritance Relationship**

    * `Coroutine\Client` is not inherited from [Client](/client), but all methods provided by `Client` can be used in `Coroutine\Client`. Please refer to [Swoole\Client](/client?id=methods) for more detailed information. It will not be mentioned further here.
    * In `Coroutine\Client`, the `set` method can be used to set [configuration options](/client?id=configurations), and the usage is completely consistent with `Client->set`. For functions that differ in usage, they will be separately explained in the `set()` function section.

* **Usage Example**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

* **Protocol Handling**

The coroutine client also supports length and `EOF` protocol handling, and the setup method is completely consistent with [Swoole\Client](/client?id=configuration).

```php
$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0, //Byte N is the value of the package length
    'package_body_offset'   => 4, //Calculate the length starting from the Nth byte
    'package_max_length'    => 2000000, //Maximum length of the protocol
));
```
### connect()

Connect to a remote server.

```php
Swoole\Coroutine\Client->connect(string $host, int $port, float $timeout = 0.5): bool
```

  * **Parameters**

    * **`string $host`**
      * **Description**: Address of the remote server【Underlying will automatically switch coroutines to resolve domain names to IP addresses】.
      * **Default**: None
      * **Other values**: None

    * **`int $port`**
      * **Description**: Port of the remote server.
      * **Default**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Description**: Timeout for network I/O operations including `connect/send/recv`. When a timeout occurs, the connection will be automatically `close`. Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules).
      * **Unit**: seconds【Supports floating point numbers, e.g., `1.5` means `1s` + `500ms`】.
      * **Default**: `0.5s`
      * **Other values**: None

* **Note**

    * If the connection fails, it will return `false`.
    * On timeout, check `$cli->errCode` for `110`.

* **Failed Retry**

!> After a failed `connect`, do not attempt to reconnect directly. You must use `close` to close the existing `socket`, and then retry with `connect`.

```php
// Connection failed
if ($cli->connect('127.0.0.1', 9501) == false) {
    // Close the existing socket
    $cli->close();
    // Retry
    $cli->connect('127.0.0.1', 9501);
}
```

* **Examples**

```php
if ($cli->connect('127.0.0.1', 9501)) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}

if ($cli->connect('/tmp/rpc.sock')) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}
```
### isConnected()

Returns the connection status of the Client

```php
Swoole\Coroutine\Client->isConnected(): bool
```

  * **Return Value**

    * Returns `false` if currently not connected to the server.
    * Returns `true` if currently connected to the server.
    
!> The `isConnected` method returns the application-layer status, only indicating that the `Client` has executed `connect` and successfully connected to the `Server`, and has not executed `close` to close the connection. The `Client` can still perform operations like `send`, `recv`, `close`, but cannot execute `connect` again.  
This does not guarantee that the connection is necessarily usable; errors may still occur when performing `send` or `recv` because the application layer cannot access the underlying `TCP` connection status. Real connection availability status is obtained only when the application layer interacts with the kernel during `send` or `recv`.
### send()

Send data.

```php
Swoole\Coroutine\Client->send(string $data): int|bool
```

  * **Parameters** 

    * **`string $data`**
    
      * **Purpose**: The data to be sent, must be of string type, supports binary data.
      * **Default**: N/A
      * **Other values**: N/A

  * Returns the number of bytes written to the `Socket` buffer upon successful sending. The underlying system will attempt to send out all data as much as possible. If the number of bytes returned is different from the length of `$data` passed in, it may be due to the `Socket` being closed by the peer, and an error code will be returned upon the next call to `send` or `recv`.

  * Returns false upon sending failure, and the error reason can be obtained using `$client->errCode`.
### recv()

The `recv` method is used to receive data from the server.

```php
Swoole\Coroutine\Client->recv(float $timeout = 0): string|bool
```

  * **Parameters** 

    * **`float $timeout`**
      * **Function**: Set the timeout period
      * **Value Unit**: Seconds [Supports floating-point numbers, e.g., `1.5` represents `1s` + `500ms`]
      * **Default value**: Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other values**: None

    !> When setting a timeout, the specified parameter is given priority, followed by the `timeout` configuration passed in the `set` method. The error code for a timeout is `ETIMEDOUT`.

  * **Return Value**

    * With a set [communication protocol](/client?id=protocol-parsing), `recv` will return complete data, limited in length by [package_max_length](/server/setting?id=package_max_length)
    * If no communication protocol is set, `recv` will return a maximum of `64K` data
    * Without a communication protocol set, `recv` returns the raw data, requiring implementation of network protocol handling in the PHP code
    * An empty string returned by `recv` indicates the server actively closed the connection, requiring a `close`
    * If `recv` fails, it returns `false`, and you can check `$client->errCode` for the error reason. Refer to the [complete example](/coroutine_client/client?id=complete-example) below for handling methods.
### close()

Close the connection.

!> `close` is not blocking and will return immediately. The close operation does not involve coroutine switching.

```php
Swoole\Coroutine\Client->close(): bool
```
### peek()

Peek into the data.

!> The `peek` method operates directly on the `socket`, so it does not trigger [Coroutine Scheduling](/coroutine?id=coroutine-scheduling).

```php
Swoole\Coroutine\Client->peek(int $length = 65535): string
```

* **Tips**

  * The `peek` method is only used to peek at the data in the kernel `socket` buffer without shifting. After using `peek`, calling `recv` can still read this portion of the data.
  * `peek` method is non-blocking and will return immediately. When there is data in the socket buffer, it will return the data content. If the buffer is empty, it returns `false` and sets `$client->errCode`.
  * If the connection is closed, `peek` will return an empty string.
### set()

Set client parameters.

```php
Swoole\Coroutine\Client->set(array $settings): bool
```

* **Configuration Parameters**

  * Please refer to [Swoole\Client](/client?id=set).

* **Differences from [Swoole\Client](/client?id=set)**

  The coroutine client provides finer-grained timeout control. You can set:
  
  * `timeout`: Total timeout, including connection, sending, and receiving timeouts
  * `connect_timeout`: Connection timeout
  * `read_timeout`: Receiving timeout
  * `write_timeout`: Sending timeout
  * Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)

* **Example**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'timeout' => 0.5,
        'connect_timeout' => 1.0,
        'write_timeout' => 10.0,
        'read_timeout' => 0.5,
    ));

    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```
### Complete Example

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // If totally empty, close the connection directly
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // You can handle based on business logic and error codes, for example:
                    // Do not close the connection if timed out, close for other cases
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```
