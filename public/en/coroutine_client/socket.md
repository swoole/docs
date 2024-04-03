# Coroutine \ Socket

The `Swoole\Coroutine\Socket` module can achieve finer-grained `IO` operations compared to the [coroutine-style server](/server/co_init) and [coroutine client](/coroutine_client/init) respective modules `Socket`.

!> You can use `Co\Socket` as a shorthand to simplify the class name. This module is relatively low-level, users are advised to have socket programming experience.
## Complete Example

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9601);
    while ($retval) {
        $n = $socket->send('hello');
        var_dump($n);

        $data = $socket->recv();
        var_dump($data);

        // If an error occurs or the peer closes the connection, the local end also needs to be closed
        if ($data === '' || $data === false) {
            echo "errCode: {$socket->errCode}\n";
            $socket->close();
            break;
        }

        Coroutine::sleep(1.0);
    }

    var_dump($retval, $socket->errCode, $socket->errMsg);
});
```
## Coroutine Scheduling

The `Coroutine\Socket` module provides synchronous programming style interfaces for `IO` operations, and utilizes coroutine scheduling underneath to achieve asynchronous IO automatically.
## Error Code

When executing `socket` related system calls, it may return -1 error, and the underlying will set the `Coroutine\Socket->errCode` attribute to the system error number `errno`, please refer to the corresponding `man` document. For example, when `$socket->accept()` returns an error, the meaning of `errCode` can be found in the error code documentation listed in `man accept`.
## Properties
### fd

The file descriptor `ID` corresponding to the `socket`
### errCode

Error Code
## Methods
### __construct()

Constructor method. Constructs a `Coroutine\Socket` object.

```php
Swoole\Coroutine\Socket::__construct(int $domain, int $type, int $protocol);
```

!> For details, refer to the `man socket` documentation.

  * **Parameters** 

    * **`int $domain`**
      * **Description**: Protocol domain【Can use `AF_INET`, `AF_INET6`, `AF_UNIX`】
      * **Default value**: None
      * **Other values**: None

    * **`int $type`**
      * **Description**: Type【Can use `SOCK_STREAM`, `SOCK_DGRAM`, `SOCK_RAW`】
      * **Default value**: None
      * **Other values**: None

    * **`int $protocol`**
      * **Description**: Protocol【Can use `IPPROTO_TCP`, `IPPROTO_UDP`, `IPPROTO_STCP`, `IPPROTO_TIPC`, `0`】
      * **Default value**: None
      * **Other values**: None

!> The constructor method calls the `socket` system call to create a `socket` handle. If the call fails, it throws a `Swoole\Coroutine\Socket\Exception` exception. It also sets the `$socket->errCode` property. The reason for the system call failure can be determined based on the value of this property.
### getOption()

Retrieve configuration.

!> This method corresponds to the `getsockopt` system call, for more details please refer to the `man getsockopt` documentation.  
This method is equivalent to the `socket_get_option` function of the `sockets` extension, you can refer to the [PHP documentation](https://www.php.net/manual/en/function.socket-get-option.php) for more information.

!> Swoole version >= v4.3.2

```php
Swoole\Coroutine\Socket->getOption(int $level, int $optname): mixed
```

  * **Parameters**

    * **`int $level`**
      * **Function** : Specifies the protocol level where the option resides.
      * **Default Value** : None
      * **Other Values** : None

      !> For example, to retrieve an option at the socket level, the `level` parameter will use `SOL_SOCKET`.  
      Other levels can be used by specifying the protocol number for that level, such as `TCP`. Protocol numbers can be found using the [getprotobyname](https://www.php.net/manual/en/function.getprotobyname.php) function.

    * **`int $optname`**
      * **Function** : Available socket options are the same as those in the [socket_get_option()](https://www.php.net/manual/en/function.socket-get-option.php) function.
      * **Default Value** : None
      * **Other Values** : None
### setOption()

Set configuration.

!> This method corresponds to the `setsockopt` system call. For more details, refer to the `man setsockopt` document. This method is equivalent to the `socket_set_option` function in the `sockets` extension, which can be seen in the [PHP documentation](https://www.php.net/manual/en/function.socket-set-option.php).

!> Swoole version >= v4.3.2

```php
Swoole\Coroutine\Socket->setOption(int $level, int $optname, mixed $optval): bool
```

  * **Parameters**

    * **`int $level`**
      * **Description**: Specify the protocol level where the option resides.
      * **Default**: None
      * **Other values**: None

      !> For example, to retrieve options at the socket level, the `level` parameter will use `SOL_SOCKET`.  
      Other levels can be used by specifying the protocol number of that level, such as `TCP`. You can use the [getprotobyname](https://www.php.net/manual/en/function.getprotobyname.php) function to find the protocol number.

    * **`int $optname`**
      * **Description**: Available socket options are the same as those of the [socket_get_option()](https://www.php.net/manual/en/function.socket-get-option.php) function.
      * **Default**: None
      * **Other values**: None

    * **`int $optval`**
      * **Description**: Value of the option 【It can be `int`, `bool`, `string`, `array` based on `level` and `optname`.】
      * **Default**: None
      * **Other values**: None
### setProtocol()

Gives the `socket` the ability to handle protocols, can configure whether to enable `SSL` encryption transmission and solve [TCP packet boundary issue](/learn?id=tcp-packet-boundary-issue) etc.

!> Swoole version >= v4.3.2

```php
Swoole\Coroutine\Socket->setProtocol(array $settings): bool
```

  * **Supported parameters in `$settings`**

Parameter | Type
---|---
open_ssl | bool
ssl_cert_file | string
ssl_key_file | string
open_eof_check | bool
open_eof_split | bool
open_mqtt_protocol | bool
open_fastcgi_protocol | bool
open_length_check | bool
package_eof | string
package_length_type | string
package_length_offset | int
package_body_offset | int
package_length_func | callable
package_max_length | int

!> The meanings of all the above parameters are completely consistent with [Server->set()](/server/setting?id=open_eof_check), and will not be repeated here.

  * **Example**

```php
$socket->setProtocol([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,
    'package_body_offset'   => 4,
]);
```
### bind()

Bind the address and port.

!> This method does not involve `IO` operations, and will not cause coroutine switching.

```php
Swoole\Coroutine\Socket->bind(string $address, int $port = 0): bool
```

  * **Parameters** 

    * **`string $address`**
      * **Description**: The address to bind, such as `0.0.0.0` or `127.0.0.1`.
      * **Default**: None
      * **Other values**: None

    * **`int $port`**
      * **Description**: The port to bind. Defaults to `0`, which will result in the system assigning an available port randomly. Can use the [getsockname](/coroutine_client/socket?id=getsockname) method to retrieve the system-assigned `port`.
      * **Default**: `0`
      * **Other values**: None

  * **Return Value** 

    * Returns `true` if binding is successful.
    * Returns `false` if binding fails. Check the `errCode` attribute to get the reason for failure.
### listen()

Listen on a `Socket`.

!> This method does not involve any `IO` operations and does not cause coroutine switches.

```php
Swoole\Coroutine\Socket->listen(int $backlog = 0): bool
```

  * **Parameters** 

    * **`int $backlog`**
      * **Function**: Length of the listen queue【Default is `0`, asynchronous `IO` is implemented using `epoll` at the system level, so the significance of `backlog` is not high】
      * **Default**: `0`
      * **Other values**: N/A

      !> If there is blocking or time-consuming logic in the application, and `accept` does not accept connections in time, newly created connections will accumulate in the `backlog` listen queue. If the backlog length is exceeded, the service will reject new connections.

  * **Return Value** 

    * Returns `true` on successful binding
    * Returns `false` on failure, check the `errCode` attribute to get the reason for the failure

  * **Kernel Parameters** 

    The maximum value of `backlog` is limited by the kernel parameter `net.core.somaxconn`, and in Linux, the `sysctl` tool can be used to dynamically adjust all kernel parameters. Dynamic adjustment means that the kernel parameter value takes effect immediately after being modified. However, this effect is limited to the OS level, and the application must be restarted to truly take effect. The command `sysctl -a` will display all kernel parameters and values.

    ```shell
    sysctl -w net.core.somaxconn=2048
    ```

    The above command changes the value of the kernel parameter `net.core.somaxconn` to `2048`. Although this change takes effect immediately, it will revert to the default value after rebooting the machine. To permanently retain the change, modify `/etc/sysctl.conf`, add `net.core.somaxconn=2048`, then execute the command `sysctl -p` to take effect.
### accept()

Accept the connection initiated by the client.

Calling this method will immediately suspend the current coroutine, join the [EventLoop](/learn?id=what-is-an-event-loop) to listen for readable events. When a connection comes in through the `Socket`, the coroutine will be automatically awakened, and the `Socket` object corresponding to the client connection will be returned.

!> This method must be used after using the `listen` method and is suitable for the `Server` side.

```php
Swoole\Coroutine\Socket->accept(float $timeout = 0): Coroutine\Socket|false;
```

  * **Parameters**

    * **`float $timeout`**
      * **Function**: Set timeout 【After setting the timeout parameter, a timer will be set at the lower level. If no client connection arrives within the specified time, the `accept` method will return `false`】
      * **Unit of Value**: Seconds 【Supports floating point numbers, such as `1.5` representing `1s` + `500ms`】
      * **Default Value**: Refer to the [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other Values**: None

  * **Return Value**

    * Returns `false` when there is a timeout or an error in the `accept` system call. You can use the `errCode` attribute to get the error code. In case of a timeout error, the error code is `ETIMEDOUT`.
    * Returns the `socket` of the client connection on success, also of type `Swoole\Coroutine\Socket` object. Operations like `send`, `recv`, `close` can be performed on it.

  * **Example**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
$socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
$socket->bind('127.0.0.1', 9601);
$socket->listen(128);

    while(true) {
        echo "Accept: \n";
        $client = $socket->accept();
        if ($client === false) {
            var_dump($socket->errCode);
        } else {
            var_dump($client);
        }
    }
});
```  
### connect()

Connect to the target server.

Calling this method initiates an asynchronous `connect` system call, suspends the current coroutine, and listens for writeability underneath. When the connection is established or fails, the coroutine is resumed.

This method is suitable for the `Client` side, supporting `IPv4`, `IPv6`, and [unixSocket](/learn?id=what-is-ipc).

```php
Swoole\Coroutine\Socket->connect(string $host, int $port = 0, float $timeout = 0): bool
```

  * **Parameters** 

    * **`string $host`**
      * **Description**: Address of the target server 【such as `127.0.0.1`, `192.168.1.100`, `/tmp/php-fpm.sock`, `www.baidu.com`, etc. You can pass an `IP` address, `Unix Socket` path, or domain name. If it is a domain name, asynchronous `DNS` resolution will be automatically conducted at the underlying level without causing blocking】
      * **Default**: None
      * **Other values**: None

    * **`int $port`**
      * **Description**: Port of the target server 【The port must be set when the `domain` of the `Socket` is `AF_INET` or `AF_INET6`】
      * **Default**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Description**: Set the timeout period 【The underlying system will set a timer, and if the connection cannot be established within the specified time, `connect` will return `false`】
      * **Unit**: Seconds 【Supports floating-point numbers, e.g., `1.5` represents `1s` + `500ms`】
      * **Default**: Refer to the [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other values**: None

  * **Return Value** 

    * Returns `false` in case of timeout or error during the `connect` system call, and you can use the `errCode` property to retrieve the error code, where the timeout error code is `ETIMEDOUT`.
    * Returns `true` on success.
### checkLiveness()

Checks the liveness of the connection through a system call (invalid when disconnected abnormally, can only detect the disconnection of the peer during a normal close).

!> Available in Swoole version >= `v4.5.0`

```php
Swoole\Coroutine\Socket->checkLiveness(): bool
```

  * **Return Value** 
    * Returns `true` when the connection is live, otherwise `false`
### send()

Send data to the peer.

!> The `send` method will immediately execute the `send` system call to send data. When the `send` system call returns an `EAGAIN` error, the underlying system will automatically listen for write events, suspend the current coroutine, wait for the write event to be triggered, re-execute the `send` system call to send data, and wake up the coroutine. 

!> If `send` is too fast and `recv` is too slow, it can eventually lead to the operating system buffer being full. The current coroutine will be suspended in the `send` method, and you can adjust the buffer appropriately, [/proc/sys/net/core/wmem_max and SO_SNDBUF](https://stackoverflow.com/questions/21856517/whats-the-practical-limit-on-the-size-of-single-packet-transmitted-over-domain)

```php
Swoole\Coroutine\Socket->send(string $data, float $timeout = 0): int|false
```

  * **Parameters** 

    * **`string $data`**
      * **Function**: The content of the data to be sent [can be text or binary data].
      * **Default**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Function**: Set the timeout period.
      * **Unit**: seconds [supports floating-point, e.g., `1.5` represents `1s` + `500ms`].
      * **Default**: Refer to the [client timeout rules](/coroutine_client/init?id=timeout-rules).
      * **Other values**: None

  * **Return Value** 

    * Returns the number of bytes written on successful send. **Please note that the actual data written may be less than the length of the `$data` parameter**. Application-layer code needs to compare the return value with `strlen($data)` to determine if the send is complete.
    * Returns `false` on send failure and sets the `errCode` attribute.
### `sendAll()`

Send data to the other end. Unlike the `send` method, `sendAll` will try to send data as completely as possible, sending all data successfully or terminating upon encountering an error.

!> The `sendAll` method will immediately execute multiple `send` system calls to send data. When the `send` system call returns the error `EAGAIN`, the underlying system will automatically listen for write events, suspend the current coroutine, wait for the write event to be triggered, and then re-execute the `send` system call to send data until all data is sent or an error occurs, waking up the corresponding coroutine.

!> Swoole version >= v4.3.0

```php
Swoole\Coroutine\Socket->sendAll(string $data, float $timeout = 0) : int | false;
```

  * **Parameters**

    * **`string $data`**
      * **Function**: Data content to be sent - can be text or binary data.
      * **Default**: None
      * **Other Values**: None

    * **`float $timeout`**
      * **Function**: Set the timeout value.
      * **Unit**: Seconds (supports floating-point numbers, e.g. `1.5` represents `1s` + `500ms`).
      * **Default**: See [Client Timeout Rules](/coroutine_client/init?id=timeout-rules).
      * **Other Values**: None

  * **Return Value**

    * `sendAll` ensures that all data is sent successfully. However, during `sendAll`, the other end may disconnect the connection. At this time, part of the data may have been sent successfully. The return value will be the length of this successfully sent data. Application layer code needs to compare whether the return value is equal to `strlen($data)` to determine if it has been sent completely and based on business requirements, decide if it needs to be resumed.
    * Returns `false` on sending failure and sets the `errCode` property.
### peek()

Peek at the data in the read buffer, equivalent to `recv(length, MSG_PEEK)` in system calls.

In PHP:

```php
Swoole\Coroutine\Socket->peek(int $length = 65535): string|false
```

* **Parameters**

  * **`int $length`**
    * **Description**: Specify the size of memory used to copy the peeked data (note: memory will be allocated here, overly large length may lead to memory exhaustion)
    * **Unit**: bytes
    * **Default**: None
    * **Other values**: None

* **Return Value**

  * Data if peek is successful
  * `false` if peek fails, and set `errCode` attribute
### recv()

Receiving data.

!> The `recv` method suspends the current coroutine immediately, listens for read events, waits for the peer to send data, triggers the read event, performs the `recv` system call to retrieve the data from the socket buffer, and wakes up the coroutine.

```php
Swoole\Coroutine\Socket->recv(int $length = 65535, float $timeout = 0): string|false
```

  * **Parameters** 

    * **`int $length`**
      * **Function**: Specifies the size of memory used for receiving data (note: memory will be allocated here, too large a length may lead to memory exhaustion)
      * **Unit**: bytes
      * **Default Value**: N/A
      * **Other Values**: N/A

    * **`float $timeout`**
      * **Function**: Sets the timeout period
      * **Unit**: seconds [supports floating point, e.g. `1.5` represents `1s` + `500ms`]
      * **Default Value**: Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other Values**: N/A

  * **Return Value** 

    * Returns actual data upon successful reception
    * Returns `false` on reception failure, and sets the `errCode` property
    * In case of reception timeout, the error code is `ETIMEDOUT`

!> The return value may not necessarily equal the expected length. It is necessary to check the length of the data received in this call. If you need to ensure getting data of a specified length in a single call, please use the `recvAll` method or loop to retrieve it yourself.  
For TCP data packet boundary issues, refer to the `setProtocol()` method or use `sendto()`.
### recvAll()

Receives data. Different from `recv`, `recvAll` will try to receive the full length of data in response as much as possible, until the reception is completed or encounters an error.

!> The `recvAll` method will immediately suspend the current coroutine and listen for readable events. When data is sent from the peer, upon triggering the readable event, it will execute the `recv` system call to retrieve data from the `socket` buffer, repeating this behavior until receiving the specified length of data or encountering an error, and then waking up the coroutine.

!> Swoole version >= v4.3.0

```php
Swoole\Coroutine\Socket->recvAll(int $length = 65535, float $timeout = 0): string|false
```

  * **Parameters**

    * **`int $length`**
      * **Function**: Expected size of data to receive (note: memory is allocated here, excessively large lengths may lead to memory exhaustion)
      * **Unit of Value**: bytes
      * **Default Value**: None
      * **Other Values**: None

    * **`float $timeout`**
      * **Function**: Sets the timeout period
      * **Unit of Value**: seconds [Supports floating point, e.g., `1.5` represents `1s`+`500ms`]
      * **Default Value**: Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other Values**: None

  * **Return Value**

    * If received successfully, it returns the actual data, and the length of the returned string matches the parameter length.
    * If the reception fails, it returns `false` and sets the `errCode` attribute.
    * If timed out, the error code is `ETIMEDOUT`.
### readVector()

Receive data in segments.

!> The `readVector` method will immediately execute the `readv` system call to read data. When the `readv` system call returns an error `EAGAIN`, the underlying layer will automatically listen for readable events, suspend the current coroutine, wait for the readable event to trigger, re-execute the `readv` system call to read data, and wake up the coroutine.

!> Swoole version >= v4.5.7

```php
Swoole\Coroutine\Socket->readVector(array $io_vector, float $timeout = 0): array|false
```

  * **Parameters** 

    * **`array $io_vector`**
      * **Description**: Expected size of segmented data to be received
      * **Unit**: Bytes
      * **Default value**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Description**: Set the timeout period
      * **Unit**: Seconds [supports floating point numbers, e.g. `1.5` represents `1s` + `500ms`]
      * **Default value**: Refer to the [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other values**: None

  * **Return Value**

    * Segmented data received successfully
    * Returns an empty array on receive failure, and sets the `errCode` attribute
    * Indicates a timeout with error code `ETIMEDOUT`

  * **Example** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// If the peer sends 'helloworld'
$ret = $socket->readVector([5, 5]);
// Then, $ret will be ['hello', 'world']
```
### readVectorAll()

Receive data in segments.

!> The `readVectorAll` method will immediately execute multiple `readv` system calls to read data. When the `readv` system call returns an `EAGAIN` error, the underlying system will automatically listen for readable events, suspend the current coroutine, wait for the readable event to trigger, then re-execute the `readv` system call to read data until the data is read completely or an error occurs, waking up the corresponding coroutine.

!> Swoole version >= v4.5.7

```php
Swoole\Coroutine\Socket->readVectorAll(array $io_vector, float $timeout = 0): array|false
```

  * **Parameters** 

    * **`array $io_vector`**
      * **Description**: Expected size of segment data to receive
      * **Unit**: Bytes
      * **Default**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Description**: Set timeout
      * **Unit**: Seconds 【Supports floating points, e.g., `1.5` represents `1s` + `500ms`】
      * **Default**: Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other values**: None

  * **Return Value**

    * If successful, it returns segmented data.
    * If failed to receive, it returns an empty array and sets the `errCode` property.
    * If timeout occurs, the error code is `ETIMEDOUT`.
### writeVector()

Send data in segments.

!> The `writeVector` method will immediately execute the `writev` system call to send data. When the `writev` system call returns an error `EAGAIN`, the underlying layer will automatically listen for writeable events, suspend the current coroutine, wait for the writeable event to be triggered, re-execute the `writev` system call to send data, and wake up the coroutine.

!> Swoole version >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVector(array $io_vector, float $timeout = 0): int|false
```

  * **Parameters** 

    * **`array $io_vector`**
      * **Description**: Segmented data to be sent
      * **Unit**: bytes
      * **Default**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Description**: Set timeout
      * **Unit**: seconds【Supports floating point numbers, e.g., `1.5` represents `1s` + `500ms`】
      * **Default**: Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other values**: None

  * **Return Value**

    * If the sending is successful, it returns the number of bytes written. **Please note that the actual data written may be less than the total length of the `$io_vector` parameter**. The application layer code needs to compare whether the return value equals the total length of the `$io_vector` parameter to determine if the sending is complete.
    * If the sending fails, it returns `false` and sets the `errCode` property.

  * **Example** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// At this point, it will send 'helloworld' to the peer in the order specified in the array
$socket->writeVector(['hello', 'world']);
```
### writeVectorAll()

Send data to the peer. Different from the `writeVector` method, `writeVectorAll` will try to send the data as complete as possible, either successfully sending all data or terminating upon encountering an error.

!> The `writeVectorAll` method will immediately execute multiple `writev` system calls to send data. When the `writev` system call returns an `EAGAIN` error, the underlying system will automatically listen for write events, suspend the current coroutine, wait for the write event to trigger, and then re-execute the `writev` system call to send data until all data is sent or an error is encountered, waking up the corresponding coroutine.

!> Swoole version >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVectorAll(array $io_vector, float $timeout = 0): int|false
```

  * **Parameters**

    * **`array $io_vector`**
      * **Function**: Segments of data to be sent
      * **Unit**: Bytes
      * **Default**: N/A
      * **Other values**: N/A

    * **`float $timeout`**
      * **Function**: Set timeout
      * **Unit**: Seconds [supports floating point numbers, e.g., `1.5` represents `1s` + `500ms`]
      * **Default**: Refer to [Client Timeout Rules](/coroutine_client/init?id=timeout-rules)
      * **Other values**: N/A

  * **Return Value**

    * `writeVectorAll` ensures that all data is sent successfully. However, the connection may be closed by the peer during `writeVectorAll`. In this case, some of the data may have been sent successfully. The return value will indicate the length of this successful data. The application layer code needs to compare this return value with the total length of the `$io_vector` parameter to determine if the sending is complete, and decide if resuming is needed based on business requirements.
    * Returns `false` for sending failure, and sets `errCode` property.

  * **Example**

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// This will send 'helloworld' to the peer according to the order in the array
$socket->writeVectorAll(['hello', 'world']);
```
### recvPacket()

For a Socket object with a protocol set via the `setProtocol` method, you can call this method to receive a complete protocol data packet.

!> Swoole version >= v4.4.0

```php
Swoole\Coroutine\Socket->recvPacket(float $timeout = 0): string|false
```

  * **Parameters** 
    * **`float $timeout`**
      * **Functionality**: set the timeout period
      * **Value Unit**: seconds 【supports float, e.g., `1.5` representing `1s` + `500ms`】
      * **Default Value**: Refer to the [client timeout rules](/coroutine_client/init?id=timeout-rules)
      * **Other Values**: N/A

  * **Return Value** 

    * Returns a complete protocol data packet upon successful reception.
    * Returns `false` with the `errCode` attribute set upon unsuccessful reception.
    * Returns `ETIMEDOUT` as the error code in case of timeout.
### recvLine()

Used to solve compatibility issues with [socket_read](https://www.php.net/manual/en/function.socket-read.php)

```php
Swoole\Coroutine\Socket->recvLine(int $length = 65535, float $timeout = 0): string|false
```
### recvWithBuffer()

Used to solve the problem of generating a large number of system calls when receiving byte by byte with `recv(1)`

```php
Swoole\Coroutine\Socket->recvWithBuffer(int $length = 65535, float $timeout = 0): string|false
```
### recvfrom()

Receive data and set the address and port of the source host. Used for `SOCK_DGRAM` type of `socket`.

!> This method will cause [coroutine scheduling](/coroutine?id=coroutine-scheduling), the underlying will suspend the current coroutine immediately and listen for readable events. When a readable event is triggered, the data is received and the `recvfrom` system call is executed to obtain the data packet.

```php
Swoole\Coroutine\Socket->recvfrom(array &$peer, float $timeout = 0): string|false
```

* **Parameters**

    * **`array $peer`**
        * **Function**: The address and port of the peer, reference type. 【Set to an array containing `address` and `port` when the function returns successfully】
        * **Default Value**: None
        * **Other Values**: None

    * **`float $timeout`**
        * **Function**: Set the timeout. 【If no data is returned within the specified time, the `recvfrom` method will return `false`】
        * **Unit**: Seconds 【Supports floating point numbers, e.g., `1.5` means `1s` + `500ms`】
        * **Default Value**: Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)
        * **Other Values**: None

* **Return Value**

    * If data is successfully received, return the data content and set `$peer` as an array
    * If failed, return `false`, set the `errCode` property, and do not modify the content of `$peer`

* **Example**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    while (true) {
        $peer = null;
        $data = $socket->recvfrom($peer);
        echo "[Server] recvfrom[{$peer['address']}:{$peer['port']}] : $data\n";
        $socket->sendto($peer['address'], $peer['port'], "Swoole: $data");
    }
});
```
### sendto()

Send data to the specified address and port. Used for `SOCK_DGRAM` type of `socket`.

!> This method does not have coroutine scheduling, the underlying method will immediately call `sendto` to send data to the target host. This method does not monitor for write availability. `sendto` may return `false` due to a full buffer, so you need to handle it yourself or use the `send` method.

```php
Swoole\Coroutine\Socket->sendto(string $address, int $port, string $data): int|false
```

  * **Parameters** 

    * **`string $address`**
      * **Description**：IP address of the target host or unixSocket path【`sendto` does not support domain names. When using `AF_INET` or `AF_INET6`, a valid IP address must be passed, otherwise, the send will fail】
      * **Default**：N/A
      * **Other values**：N/A

    * **`int $port`**
      * **Description**：Port of the target host【Set to `0` when sending broadcasts】
      * **Default**：N/A
      * **Other values**：N/A

    * **`string $data`**
      * **Description**：Data to send【Can be text or binary content. Note that the maximum length of the sent package for `SOCK_DGRAM` is `64K`】
      * **Default**：N/A
      * **Other values**：N/A

  * **Return Value** 

    * Returns the number of bytes sent on success
    * Returns `false` on failure and sets the `errCode` property

  * **Example** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
$socket->sendto('127.0.0.1', 9601, 'Hello');
```
### getsockname()

Obtain the address and port information of the socket.

!> This method has no coroutine scheduling overhead.

```php
Swoole\Coroutine\Socket->getsockname(): array|false
```

  * **Return Value**

    * Returns an array containing `address` and `port` if successful
    * Returns `false` and sets the `errCode` property if the call fails
### getpeername()

Obtain the peer address and port information of a `socket`, only for `SOCK_STREAM` type sockets with connections.

?> This method has no [coroutine scheduling](/coroutine?id=coroutine-scheduling) overhead.

```php
Swoole\Coroutine\Socket->getpeername(): array|false
```

  * **Return Value** 

    * Returns an array containing `address` and `port` upon successful call
    * Returns `false` upon failure, with `errCode` property set
### close()

Close the `Socket`.

!> When the `Swoole\Coroutine\Socket` object is destructed, the `close` method will be automatically executed without [Coroutine Scheduling](/coroutine?id=Coroutine-Scheduling) overhead.

```php
Swoole\Coroutine\Socket->close(): bool
```

  * **Return Value**

    * Returns `true` on successful closing.
    * Returns `false` on failure.
### isClosed()

Returns whether the `Socket` is closed.

```php
Swoole\Coroutine\Socket->isClosed(): bool
```
## Constants

Equivalent to constants provided by the `sockets` extension, and will not conflict with the `sockets` extension.

!> Values may vary on different systems. The following code is for illustration purposes only, please do not use these values.

```php
define ('AF_UNIX', 1);
define ('AF_INET', 2);

/**
 * Only available if compiled with IPv6 support.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('AF_INET6', 10);
define ('SOCK_STREAM', 1);
define ('SOCK_DGRAM', 2);
define ('SOCK_RAW', 3);
define ('SOCK_SEQPACKET', 5);
define ('SOCK_RDM', 4);
define ('MSG_OOB', 1);
define ('MSG_WAITALL', 256);
define ('MSG_CTRUNC', 8);
define ('MSG_TRUNC', 32);
define ('MSG_PEEK', 2);
define ('MSG_DONTROUTE', 4);

/**
 * Not available on Windows platforms.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOR', 128);

/**
 * Not available on Windows platforms.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOF', 512);
define ('MSG_CONFIRM', 2048);
define ('MSG_ERRQUEUE', 8192);
define ('MSG_NOSIGNAL', 16384);
define ('MSG_DONTWAIT', 64);
define ('MSG_MORE', 32768);
define ('MSG_WAITFORONE', 65536);
define ('MSG_CMSG_CLOEXEC', 1073741824);
define ('SO_DEBUG', 1);
define ('SO_REUSEADDR', 2);

/**
 * This constant is only available in PHP 5.4.10 or later on platforms that
 * support the <b>SO_REUSEPORT</b> socket option: this
 * includes Mac OS X and FreeBSD, but does not include Linux or Windows.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SO_REUSEPORT', 15);
define ('SO_KEEPALIVE', 9);
define ('SO_DONTROUTE', 5);
define ('SO_LINGER', 13);
define ('SO_BROADCAST', 6);
define ('SO_OOBINLINE', 10);
define ('SO_SNDBUF', 7);
define ('SO_RCVBUF', 8);
define ('SO_SNDLOWAT', 19);
define ('SO_RCVLOWAT', 18);
define ('SO_SNDTIMEO', 21);
define ('SO_RCVTIMEO', 20);
define ('SO_TYPE', 3);
define ('SO_ERROR', 4);
define ('SO_BINDTODEVICE', 25);
define ('SOL_SOCKET', 1);
define ('SOMAXCONN', 128);

/**
 * Used to disable Nagle TCP algorithm.
 * Added in PHP 5.2.7.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('TCP_NODELAY', 1);
define ('PHP_NORMAL_READ', 1);
define ('PHP_BINARY_READ', 2);
define ('MCAST_JOIN_GROUP', 42);
define ('MCAST_LEAVE_GROUP', 45);
define ('MCAST_BLOCK_SOURCE', 43);
define ('MCAST_UNBLOCK_SOURCE', 44);
define ('MCAST_JOIN_SOURCE_GROUP', 46);
define ('MCAST_LEAVE_SOURCE_GROUP', 47);
define ('IP_MULTICAST_IF', 32);
define ('IP_MULTICAST_TTL', 33);
define ('IP_MULTICAST_LOOP', 34);
define ('IPV6_MULTICAST_IF', 17);
define ('IPV6_MULTICAST_HOPS', 18);
define ('IPV6_MULTICAST_LOOP', 19);
define ('IPV6_V6ONLY', 27);

/**
 * Operation not permitted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPERM', 1);

/**
 * No such file or directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOENT', 2);

/**
 * Interrupted system call.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINTR', 4);

/**
 * I/O error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIO', 5);

/**
 * No such device or address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENXIO', 6);

/**
 * Arg list too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_E2BIG', 7);

/**
 * Bad file number.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADF', 9);

/**
 * Try again.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAGAIN', 11);

/**
 * Out of memory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEM', 12);

/**
 * Permission denied.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EACCES', 13);

/**
 * Bad address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EFAULT', 14);

/**
 * Block device required.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTBLK', 15);

/**
 * Device or resource busy.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBUSY', 16);

/**
 * File exists.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EEXIST', 17);

/**
 * Cross-device link.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXDEV', 18);

/**
 * No such device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODEV', 19);

/**
 * Not a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTDIR', 20);

/**
 * Is a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISDIR', 21);

/**
 * Invalid argument.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINVAL', 22);

/**
 * File table overflow.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENFILE', 23);

/**
 * Too many open files.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMFILE', 24);

/**
 * Not a typewriter.
```
* @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTTY', 25);

/**
 * No space left on device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSPC', 28);

/**
 * Illegal seek.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESPIPE', 29);

/**
 * Read-only file system.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EROFS', 30);

/**
 * Too many links.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMLINK', 31);

/**
 * Broken pipe.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPIPE', 32);

/**
 * File name too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENAMETOOLONG', 36);

/**
 * No record locks available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLCK', 37);

/**
 * Function not implemented.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSYS', 38);

/**
 * Directory not empty.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTEMPTY', 39);

/**
 * Too many symbolic links encountered.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELOOP', 40);

/**
 * Operation would block.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EWOULDBLOCK', 11);

/**
 * No message of desired type.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMSG', 42);

/**
 * Identifier removed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIDRM', 43);

/**
 * Channel number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECHRNG', 44);

/**
 * Level 2 not synchronized.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2NSYNC', 45);

/**
 * Level 3 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3HLT', 46);

/**
 * Level 3 reset.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3RST', 47);

/**
 * Link number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELNRNG', 48);

/**
 * Protocol driver not attached.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUNATCH', 49);

/**
 * No CSI structure available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOCSI', 50);

/**
 * Level 2 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2HLT', 51);

/**
 * Invalid exchange.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADE', 52);

/**
 * Invalid request descriptor.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADR', 53);

/**
 * Exchange full.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXFULL', 54);

/**
 * No anode.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOANO', 55);

/**
 * Invalid request code.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADRQC', 56);

/**
 * Invalid slot.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADSLT', 57);

/**
 * Device not a stream.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSTR', 60);

/**
 * No data available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODATA', 61);

/**
 * Timer expired.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIME', 62);

/**
 * Out of streams resources.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSR', 63);

/**
 * Machine is not on the network.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENONET', 64);

/**
 * Object is remote.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMOTE', 66);

/**
 * Link has been severed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLINK', 67);

/**
 * Advertise error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EADV', 68);

/**
 * Srmount error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESRMNT', 69);

/**
 * Communication error on send.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECOMM', 70);

/**
 * Protocol error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTO', 71);

/**
 * Multihop attempted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMULTIHOP', 72);

/**
 * Not a data message.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADMSG', 74);

/**
 * Name not unique on network.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTUNIQ', 76);

/**
 * File descriptor in bad state.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADFD', 77);

/**
 * Remote address changed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMCHG', 78);

/**
 * Interrupted system call should be restarted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ERESTART', 85);

/**
 * Streams pipe error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
SOCKET_ESTRPIPE is defined as 86.

SOCKET_EUSERS: Too many users. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENOTSOCK: Socket operation on non-socket. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EDESTADDRREQ: Destination address required. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EMSGSIZE: Message too long. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EPROTOTYPE: Protocol wrong type for socket. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENOPROTOOPT is defined as 92.  
SOCKET_EPROTONOSUPPORT: Protocol not supported. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ESOCKTNOSUPPORT: Socket type not supported. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EOPNOTSUPP: Operation not supported on transport endpoint. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EPFNOSUPPORT: Protocol family not supported. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EAFNOSUPPORT: Address family not supported by protocol. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EADDRINUSE is defined as 98.  
SOCKET_EADDRNOTAVAIL: Cannot assign requested address. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENETDOWN: Network is down. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENETUNREACH: Network is unreachable. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENETRESET: Network dropped connection because of reset. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ECONNABORTED: Software caused connection abort. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ECONNRESET: Connection reset by peer. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENOBUFS: No buffer space available. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EISCONN: Transport endpoint is already connected. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENOTCONN: Transport endpoint is not connected. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ESHUTDOWN: Cannot send after transport endpoint shutdown. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ETOOMANYREFS: Too many references: cannot splice. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ETIMEDOUT: Connection timed out. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ECONNREFUSED: Connection refused. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EHOSTDOWN: Host is down. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EHOSTUNREACH: No route to host. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EALREADY: Operation already in progress. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EINPROGRESS: Operation now in progress. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EISNAM: Is a named type file. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EREMOTEIO: Remote I/O error. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EDQUOT: Quota exceeded. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_ENOMEDIUM: No medium found. [Source](http://php.net/manual/en/sockets.constants.php)  
SOCKET_EMEDIUMTYPE: Wrong medium type. [Source](http://php.net/manual/en/sockets.constants.php)  

IPPROTO_IP is defined as 0.  
IPPROTO_IPV6 is defined as 41.  
SOL_TCP is defined as 6.  
SOL_UDP is defined as 17.  
IPV6_UNICAST_HOPS is defined as 16.  
IPV6_RECVPKTINFO is defined as 49.  
IPV6_PKTINFO is defined as 50.  
IPV6_RECVHOPLIMIT is defined as 51.  
IPV6_HOPLIMIT is defined as 52.  
IPV6_RECVTCLASS is defined as 66.  
IPV6_TCLASS is defined as 67.  
SCM_RIGHTS is defined as 1.  
SCM_CREDENTIALS is defined as 2.  
SO_PASSCRED is defined as 16.  
