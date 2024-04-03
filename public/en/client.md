# Swoole\Client

`Swoole\Client`, hereinafter referred to as `Client`, provides encapsulated code for client-side `TCP/UDP`, `socket` operations. It can be used by simply executing `new Swoole\Client`. It can be used in `FPM/Apache` environments.  
Compared to traditional [streams](https://www.php.net/streams) functions, `Client` has several advantages:

* `stream` functions have traps and bugs in timeout settings. If not handled properly, it can cause long-term blocking on the server side.
* The default maximum length limit for `fread` in `stream` functions is only `8192`, which cannot support large packets in `UDP`.
* `Client` supports `waitall`, which can fetch the complete data at once when the packet length is known, eliminating the need for looping over reads.
* `Client` supports `UDP Connect`, solving the packet concatenation issue in `UDP`.
* `Client` is written in pure `C` for specialized `socket` handling. `stream` functions are very complex. `Client` performs better in terms of performance.
* `Client` supports persistent connections.
* You can use the [swoole_client_select](/client?id=swoole_client_select) function to control concurrency of multiple `Client`s.
### Complete Example

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```

!> `prework` multithreading mode is not supported by `Apache`
## Method
### __construct()

Constructor

```php
Swoole\Client::__construct(int $sock_type, int $is_sync = SWOOLE_SOCK_SYNC, string $key);
```

* **Parameters**

  * **`int $sock_type`**
    * **Function** : Represents the type of `socket`【supports `SWOOLE_SOCK_TCP`, `SWOOLE_SOCK_TCP6`, `SWOOLE_SOCK_UDP`, `SWOOLE_SOCK_UDP6`】. Refer to [this section](/server/methods?id=__construct) for specific meanings.
    * **Default Value** : None
    * **Other Values** : None

  * **`int $is_sync`**
    * **Function** : Synchronous blocking mode, currently only this one type. This parameter is kept for compatibility with the API.
    * **Default Value** : `SWOOLE_SOCK_SYNC`
    * **Other Values** : None

  * **`string $key`**
    * **Function** : Used for the `key` of long connections【default uses `IP:PORT` as the `key`. Same `key`, even if new twice, will use only one TCP connection】.
    * **Default Value** : `IP:PORT`
    * **Other Values** : None

!> You can use the macros provided by the underlying to specify the type, refer to [constant definitions](/consts)
#### Creating Persistent Connections in PHP-FPM/Apache

```php
$cli = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
```

By adding the [SWOOLE_KEEP](/client?id=swoole_keep) flag, the created `TCP` connection will not be closed when the PHP request ends or when calling `$cli->close()`. The next time `connect` is called, the previously created connection will be reused. By default, the persistent connection is stored with the key being `ServerHost:ServerPort`. You can specify a key within the third parameter.

The `Client` object's destructor automatically calls the [close](/client?id=close) method to close the `socket`.
#### Using Client in Server

  * `Client` must be used in event [callback functions](/server/events).
  * `Server` can connect using a `socket client` written in any language. Similarly, `Client` can also connect to a `socket server` written in any language.

!> Using this `Client` in the `Swoole4+` coroutine environment will revert to a [synchronous model](/learn?id=sync-io-vs-async-io).
### set()

Set client parameters, which must be executed before [connect](/client?id=connect).

```php
Swoole\Client->set(array $settings);
```

Refer to Client - [configuration options](/client?id=configuration) for available configuration options.
### connect()

Connect to a remote server.

```php
Swoole\Client->connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
```

* **Parameters**

  * **`string $host`**
    * **Purpose**: server address 【supports automatic asynchronous domain name resolution, can directly pass in a domain name for `$host`] 
    * **Default**: none
    * **Other values**: none

  * **`int $port`**
    * **Purpose**: server port
    * **Default**: none
    * **Other values**: none

  * **`float $timeout`**
    * **Purpose**: set timeout
    * **Unit**: seconds 【supports floating point, e.g. `1.5` represents `1s` + `500ms`] 
    * **Default**: `0.5` 
    * **Other values**: none

  * **`int $sock_flag`**
    - For `UDP` type, it indicates whether `udp_connect` is enabled. After setting this option, `$host` and `$port` will be bound. Non-specified data packets from the host/port will be discarded.
    - For `TCP` type, `$sock_flag=1` indicates setting a non-blocking `socket`. Afterwards, this file descriptor will become asynchronous IO, and `connect` will return immediately. If `$sock_flag` is set to `1`, then before using `send/recv`, you must use [swoole_client_select](/client?id=swoole_client_select) to check if the connection is complete. 

* **Return Value**

  * Returns `true` on success
  * Returns `false` on failure, check the `errCode` attribute to get the reason for failure

* **Synchronous Mode**

The `connect` method will block until it successfully connects and returns `true`. At this point, you can send data to the server or receive data from it.

```php
if ($cli->connect('127.0.0.1', 9501)) {
      $cli->send("data");
} else {
      echo "connect failed.";
}
```

It will return `false` if the connection fails.

> After executing `close` on a synchronous `TCP` client, you can initiate a `connect` to create a new connection to the server.

* **Reconnection on Failure**

If `connect` fails and you wish to reconnect once, you must first `close` the old socket. Otherwise, it will return an `EINPROCESS` error since the current socket is still trying to connect to the server and the client does not know if the connection was successful. Thus, it cannot perform `connect` again. Calling `close` will close the current socket, and a new socket will be created by the underlying layer for reconnection.

!> When using [SWOOLE_KEEP](/client?id=swoole_keep) for long connections, the first parameter of the `close` call must be set to `true` to forcibly destroy the long connection socket.

```php
if ($socket->connect('127.0.0.1', 9502) === false) {
    $socket->close(true);
    $socket->connect('127.0.0.1', 9502);
}
```

* **UDP Connect**

By default, the underlying layer does not enable `udp connect`. When a UDP client executes `connect`, the underlying layer immediately returns success after creating the socket. At this point, the socket is bound to the address `0.0.0.0`, and any other endpoint can send data packets to this port.

For example, `$client->connect('192.168.1.100', 9502)`, in this case, the operating system assigns a random port `58232` to the client socket. Other machines, such as `192.168.1.101`, can also send data packets to this port.

?> When `udp connect` is not enabled, calling `getsockname` will return a `host` item of `0.0.0.0`.

Set the 4th parameter to `1` to enable `udp connect`, `$client->connect('192.168.1.100', 9502, 1, 1)`. In this case, the client and the server will be bound together, and the underlying layer will bind the socket to the address of the server. For example, if you connect to `192.168.1.100`, the current socket will be bound to the local address of `192.168.1.*`. After enabling `udp connect`, the client will no longer receive data packets sent by other hosts to this port.
### recv()

Receives data from the server.

```php
Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
```

* **Parameters**

  * **`int $size`**
    * **Function**: The maximum length of the receive data buffer 【Avoid setting this parameter too large, as it may consume a large amount of memory】.
    * **Default Value**: None
    * **Other Values**: None

  * **`int $flags`**
    * **Function**: Additional configurable parameters, such as [Client::MSG_WAITALL](/client?id=clientmsg_waitall). Refer to [this section](/client?id=constants) for specific parameter values.
    * **Default Value**: None
    * **Other Values**: None

* **Return Value**

  * Returns a string if data is successfully received.
  * Returns an empty string if the connection is closed.
  * Returns `false` on failure and sets the `$client->errCode` property.

* **EOF/Length Protocol**

  * When the client enables `EOF/Length` detection, there is no need to set the `$size` and `$waitall` parameters. The extension layer will return the complete data package or `false`. Refer to the [Protocol Analysis](/client?id=protocol_analysis) section.
  * If an incorrect package header or a length value that exceeds the [package_max_length](/server/setting?id=package_max_length) setting is received, `recv` will return an empty string, and the PHP code should close this connection.
### send()

Send data to a remote server, data can only be sent to the peer after the connection is established.

```php
Swoole\Client->send(string $data): int|false
```

* **Parameters**

  * **`string $data`**
    * **Functionality**: Content to be sent [supports binary data]
    * **Default Value**: None
    * **Other Values**: None

* **Return Value**

  * Returns the length of the successfully sent data
  * Returns `false` on failure, and sets the `errCode` property

* **Note**

  * If `connect` is not executed, calling `send` will trigger a warning
  * There is no length limit for the data to be sent
  * If the data to be sent is too large and the Socket buffer is full, the program will block and wait for it to be writable.
### sendfile()

Send file to the server, this function is based on the `sendfile` operating system call.

```php
Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

!> sendfile cannot be used for UDP clients and SSL tunnel encrypted connections

* **Parameters** 

  * **`string $filename`**
    * **Description**: Specifies the path of the file to be sent
    * **Default**: None
    * **Other values**: None

  * **`int $offset`**
    * **Description**: Offset of the file to upload (can specify to transfer data from the middle of the file. This feature can be used to support resumable uploads.)
    * **Default**: None
    * **Other values**: None

  * **`int $length`**
    * **Description**: Size of data to send (default is the size of the entire file)
    * **Default**: None
    * **Other values**: None

* **Return Value**

  * Returns `false` if the specified file does not exist
  * Returns `true` on successful execution

* **Note**

  * `sendfile` will block until the entire file is sent or a fatal error occurs
### sendto()

Send a UDP data packet to a host with any `IP:PORT`, only supports `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6` type.

```php
Swoole\Client->sendto(string $ip, int $port, string $data): bool
```

* **Parameters**

  * **`string $ip`**
    * **Description**: IP address of the destination host, supports IPv4/IPv6
    * **Default value**: N/A
    * **Other values**: N/A

  * **`int $port`**
    * **Description**: Port of the destination host
    * **Default value**: N/A
    * **Other values**: N/A

  * **`string $data`**
    * **Description**: Data content to be sent [should not exceed `64K`]
    * **Default value**: N/A
    * **Other values**: N/A
### enableSSL()

Enable dynamic SSL tunnel encryption. This function can only be used if `--enable-openssl` is enabled during the compilation of `swoole`.

```php
Swoole\Client->enableSSL(): bool
```

If the client communicates in plaintext when establishing the connection and later wishes to switch to SSL encryption, it can use the `enableSSL` method. If SSL is enabled from the beginning, please refer to [SSL configuration](/client?id=ssl-related). To dynamically enable SSL tunnel encryption using `enableSSL`, two conditions must be met:

  * The client type must be non-SSL when created.
  * The client has already established a connection with the server.

Calling `enableSSL` will block and wait for the SSL handshake to complete.

* **Example**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
// Enable SSL tunnel encryption
if ($client->enableSSL()) {
    // Handshake completed, data sent and received at this point is encrypted
    $client->send("hello world\n");
    echo $client->recv();
}
$client->close();
```
### getPeerCert()

Retrieve the server-side certificate information. This function can only be used if `--enable-openssl` is enabled when compiling `Swoole`.

```php
Swoole\Client->getPeerCert(): string|false
```

* **Return Value**
  * Return a `X509` certificate string information if successful
  * Return `false` if failed

!> This method can only be called after the SSL handshake is completed.

You can use the `openssl_x509_parse` function provided by the `openssl` extension to parse the certificate information.

!> Enable [--enable-openssl](/environment?id=Compilation-Options) when compiling Swoole.
### verifyPeerCert()

Verify the server-side certificate. This function can only be used if `--enable-openssl` is enabled when compiling `swoole`.

```php
Swoole\Client->verifyPeerCert()
```
### isConnected()

Returns the connection status of the Client.

* Returns false, indicating that the client is not currently connected to the server.
* Returns true, indicating that the client is currently connected to the server.

```php
Swoole\Client->isConnected(): bool
```

!> The `isConnected` method returns the application-layer status, only indicating that the `Client` has executed `connect` and successfully connected to the `Server`, without executing `close` to close the connection. The `Client` can execute operations like `send`, `recv`, `close`, but cannot execute `connect` again.
This does not necessarily mean the connection is available. Errors may still occur when executing `send` or `recv` because the application layer cannot obtain the status of the underlying `TCP` connection. In order to obtain the actual connection availability status, application layer communication with the kernel must occur when executing `send` or `recv`.
### getSockName()

Used to get the local host:port of the client socket.

!> Must be used after connecting.

```php
Swoole\Client->getsockname(): array|false
```

* **Return Value**

```php
array('host' => '127.0.0.1', 'port' => 53652);
```
### getPeerName()

Obtain the IP address and port of the peer socket.

!> Only supports `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM` types.

```php
Swoole\Client->getpeername(): array|false
```

In the UDP protocol communication, after a client sends a data packet to a server, it might not necessarily be the server that responds to the client. You can use the `getpeername` method to obtain the actual IP:PORT of the responding server.

!> This function must be called after `$client->recv()`.
### close()

Close the connection.

```php
Swoole\Client->close(bool $force = false): bool
```

* **Parameters**

  * **`bool $force`**
    * **Functionality**: Forcefully close the connection [can be used to close [SWOOLE_KEEP](/client?id=swoole_keep) long connection]
    * **Default value**: None
    * **Other values**: None

Do not initiate a `connect` after a `swoole_client` connection has been closed. The correct approach is to destroy the current `Client`, create a new `Client`, and establish a new connection.

The `Client` object will automatically `close` when it is destructed.
### shutdown()

Close the client

```php
Swoole\Client->shutdown(int $how): bool
```

* **Parameters** 

  * **`int $how`**
    * **Function**：Set how to close the client
    * **Default Value**：None
    * **Other Values**：Swoole\Client::SHUT_RDWR (close read and write), SHUT_RD (close read), Swoole\Client::SHUT_WR (close write)
### getSocket()

Obtain the underlying `socket` handle, and the returned object is a `sockets` resource handle.

!> This method requires the `sockets` extension and needs [--enable-sockets](/environment?id=编译选项) option to be enabled during compilation.

```php
Swoole\Client->getSocket()
```

Using the `socket_set_option` function, you can set some lower-level `socket` parameters.

```php
$socket = $client->getSocket();
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
}
```
### swoole_client_select

Swoole\Client uses the `select` system call for parallel processing in the IO event loop, not `epoll_wait`. Unlike the [Event module](/event), this function is used in a synchronous IO environment (calling it in a Swoole Worker process will prevent Swoole's own `epoll` IO event loop from executing).

Function prototype:

```php
int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);
```

- `swoole_client_select` takes 4 parameters: `$read`, `$write`, `$error`, which are file descriptors for readable/writable/error.  
- These 3 parameters must be arrays passed by reference. The elements of the arrays must be `swoole_client` objects.
- This method is based on the `select` system call and supports a maximum of `1024` sockets.
- The `$timeout` parameter is the timeout for the `select` system call, in seconds, as a floating-point number.
- Its functionality is similar to PHP's built-in `stream_select()`, but `stream_select` only supports PHP's stream variable type and has poor performance.

After a successful call, it will return the number of events and modify the `$read`/`$write`/`$error` arrays. Iterate through the arrays using `foreach`, and then execute `$item->recv`/`$item->send` to send/receive data. Or call `$item->close()` or `unset($item)` to close the socket.

`swoole_client_select` returns `0` if there are no available IO operations within the specified time, indicating a timeout on the `select` call.

!> This function can be used in an `Apache/PHP-FPM` environment    

```php
$clients = array();

for($i=0; $i< 20; $i++)
{
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); // Synchronous blocking
    $ret = $client->connect('127.0.0.1', 9501, 0.5, 0);
    if(!$ret)
    {
        echo "Connect Server fail. errCode=".$client->errCode;
    }
    else
    {
        $client->send("HELLO WORLD\n");
        $clients[$client->sock] = $client;
    }
}

while (!empty($clients))
{
    $write = $error = array();
    $read = array_values($clients);
    $n = swoole_client_select($read, $write, $error, 0.6);
    if ($n > 0)
    {
        foreach ($read as $index => $c)
        {
            echo "Recv #{$c->sock}: " . $c->recv() . "\n";
            unset($clients[$c->sock]);
        }
    }
}
```
## Properties
### errCode

Error code

```php
Swoole\Client->errCode: int
```

When `connect/send/recv/close` fails, the value of `$swoole_client->errCode` will be automatically set.

The value of `errCode` is equal to `Linux errno`. You can use `socket_strerror` to convert the error code to an error message.

```php
echo socket_strerror($client->errCode);
```

References: [Linux Error Code List](/other/errno?id=linux)
### sock

File descriptor of the socket connection.

```php
Swoole\Client->sock;
```

In PHP code, you can use:

```php
$sock = fopen("php://fd/".$swoole_client->sock); 
```

* Convert the `socket` of `Swoole\Client` to a `stream socket`. Functions like `fread/fwrite/fclose` can be used for operations.

* Cannot use this method to convert `$fd` in [Swoole\Server](/server/methods?id=__construct) because `$fd` is just a number; the file descriptor `$fd` belongs to the main process, refer to [SWOOLE_PROCESS](/learn?id=swoole_process) mode.

* `$swoole_client->sock` can be converted to an integer to serve as an array `key`.

!> It is important to note that the value of the `$swoole_client->sock` attribute can only be obtained after `$swoole_client->connect`. Before connecting to the server, the value of this attribute is `null`.
### reuse

Indicates whether this connection is newly created or reuses an existing one. Used in conjunction with [SWOOLE_KEEP](/client?id=swoole_keep).
#### Use Case

After establishing a connection between the WebSocket client and server, the handshake needs to be performed. If the connection is being reused, there is no need to perform the handshake again. You can directly send the WebSocket data frame.

```php
if ($client->reuse) {
    $client->send($data);
} else {
    $client->doHandShake();
    $client->send($data);
}
```
### reuseCount

Indicates the number of times this connection has been reused. Used in conjunction with [SWOOLE_KEEP](/client?id=swoole_keep).

```php
Swoole\Client->reuseCount;
```
### type

Represents the type of `socket` and will return the value of `$sock_type` in `Swoole\Client::__construct()`.

```php
Swoole\Client->type;
```
### id

Returns the value of `$key` in `Swoole\Client::__construct()`, used in conjunction with [SWOOLE_KEEP](/client?id=swoole_keep)

```php
Swoole\Client->id;
```
### setting

Returns the configuration set by `Swoole\Client::set()` on the client side.

```php
Swoole\Client->setting;
```
## Constants
### SWOOLE_KEEP

Swoole\Client supports creating a TCP long connection to the server in `PHP-FPM/Apache`. You can use it like this:

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
```

When enabling the `SWOOLE_KEEP` option, a request will not close the socket after finishing, and the next time you `connect`, it will automatically reuse the previously created connection. If `connect` detects that the connection has been closed by the server, it will create a new connection.

?> Advantages of `SWOOLE_KEEP`

* A TCP long connection can reduce additional IO consumption caused by `connect` 3-way handshake / `close` 4-way handshake.
* Reduce the number of server-side `close`/`connect` operations.
### Swoole\Client::MSG_WAITALL

- If the Client::MSG_WAITALL parameter is set, an accurate `$size` must be specified. Otherwise, it will wait continuously until the received data length reaches `$size`.
- When Client::MSG_WAITALL is not set, the maximum value for `$size` is `64K`.
- Setting an incorrect `$size` will cause the `recv` to timeout and return `false`.
### Swoole\Client::MSG_DONTWAIT

Non-blocking reception of data, it will return immediately regardless of whether there is data or not.
### Swoole\Client::MSG_PEEK

Peek at the data in the `socket` buffer. When the `MSG_PEEK` parameter is set, reading data with `recv` will not modify the pointer. Therefore, the next call to `recv` will still return data starting from the previous position.
### Swoole\Client::MSG_OOB

Read out-of-band data, please search for "`TCP out-of-band data`".
### Swoole\Client::SHUT_RDWR

Close the read and write end of the client.
### `Swoole\Client::SHUT_RD`

Close the reading end of the client.
### Swoole\Client::SHUT_WR

Close the write end of the client.
## Configuration

`Client` can use the `set` method to configure some options and enable certain features.
### Protocol Parsing

To solve the [TCP packet boundary problem](/learn?id=tcp数据包边界问题), the significance of related configurations is consistent with `Swoole\Server`. For more details, please refer to the configuration section of [Swoole\Server protocol](/server/setting?id=open_eof_check).

* **End of Line Detection**

```php
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n\r\n",
    'package_max_length' => 1024 * 1024 * 2,
));
```

* **Length Detection**

```php
$client->set(array(
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0, // The Nth byte is the value of the package length
    'package_body_offset' => 4, // Start calculating the length from which byte
    'package_max_length' => 2000000, // Maximum length of the protocol
));
```

!> Currently supports 2 types of automatic protocol processing functionalities: [open_length_check](/server/setting?id=open_length_check) and [open_eof_check](/server/setting?id=open_eof_check);  
After configuring the protocol parsing, the `recv()` method of the client will not accept a length parameter, and it will always return a complete data packet.

* **MQTT Protocol**

!> By enabling `MQTT` protocol parsing, the [onReceive](/server/events?id=onreceive) callback will receive complete `MQTT` data packets.

```php
$client->set(array(
    'open_mqtt_protocol' => true,
));
```

* **Socket Buffer Size**	

!> This includes `socket` underlying OS buffer, application layer receive data memory cache, and application layer send data memory buffer.	

```php	
$client->set(array(	
    'socket_buffer_size' => 1024 * 1024 * 2, // 2M buffer size	
));	
```

* **Disabling Nagle Algorithm**

```php
$client->set(array(
    'open_tcp_nodelay' => true,
));
```
### SSL-related

* **SSL/TLS Certificate Configuration**

```php
$client->set(array(
    'ssl_cert_file' => $your_ssl_cert_file_path,
    'ssl_key_file' => $your_ssl_key_file_path,
));
```

* **ssl_verify_peer**

Verifies the server-side certificate.

```php
$client->set([
    'ssl_verify_peer' => true,
]);
```

When enabled, it will verify if the certificate matches the hostname; if not, the connection will be automatically closed.

* **Self-Signed Certificate**

You can set `ssl_allow_self_signed` to `true` to allow self-signed certificates.

```php
$client->set([
    'ssl_verify_peer' => true,
    'ssl_allow_self_signed' => true,
]);
```

* **ssl_host_name**

Sets the server's hostname, to be used in conjunction with the `ssl_verify_peer` configuration or with [Client::verifyPeerCert](/client?id=verifypeercert).

```php
$client->set([
    'ssl_host_name' => 'www.google.com',
]);
```

* **ssl_cafile**

Used to verify the remote certificate when `ssl_verify_peer` is set to `true`. This option specifies the full path and filename of the `CA` certificate in the local file system.

```php
$client->set([
    'ssl_cafile' => '/etc/CA',
]);
```

* **ssl_capath**

If `ssl_cafile` is not set, or the file pointed to by `ssl_cafile` does not exist, the appropriate certificate will be searched in the directory specified by `ssl_capath`. This directory must be a directory of certificates that has been hashed.

```php
$client->set([
    'ssl_capath' => '/etc/capath/',
])
```

* **ssl_passphrase**

Password for the local certificate file [ssl_cert_file](/server/setting?id=ssl_cert_file).

* **Example**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_SSL);

$client->set(array(
    'ssl_cert_file' => __DIR__.'/ca/client-cert.pem',
    'ssl_key_file' => __DIR__.'/ca/client-key.pem',
    'ssl_allow_self_signed' => true,
    'ssl_verify_peer' => true,
    'ssl_cafile' => __DIR__.'/ca/ca-cert.pem',
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
echo "connect ok\n";
$client->send("hello world-" . str_repeat('A', $i) . "\n");
echo $client->recv();
```
### package_length_func

Set the length calculation function, which methods are completely consistent with [package_length_func](/server/setting?id=package_length_func) in `Swoole\Server`. It is used in conjunction with [open_length_check](/server/setting?id=open_length_check). The length function must return an integer.

* Return `0`: indicates insufficient data, more data needs to be received.
* Return `-1`: indicates data error, the underlying layer will automatically close the connection.
* Return the total length value of the package (including the total length of the package header and body): the underlying layer will automatically concatenate the package and return it to the callback function.

By default, the underlying layer will read up to `8K` of data. If the length of the package header is small, there may be memory copy overhead. You can set the `package_body_offset` parameter, and the underlying layer will only read the package header for length parsing.

* **Example**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check' => true,
    'package_length_func' => function ($data) {
        if (strlen($data) < 8) {
            return 0;
        }
        $length = intval(trim(substr($data, 0, 8)));
        if ($length <= 0) {
            return -1;
        }
        return $length + 8;
    },
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```
### socks5_proxy

Configuring a socks5 proxy.

!> Setting only one option is invalid. Each time, `host` and `port` must be set; `socks5_username` and `socks5_password` are optional parameters. `socks5_port` and `socks5_password` cannot be `null`.

```php
$client->set(array(
    'socks5_host' => '192.168.1.100',
    'socks5_port' => 1080,
    'socks5_username' => 'username',
    'socks5_password' => 'password',
));
```
### http_proxy

Configure HTTP proxy.

!> `http_proxy_port` and `http_proxy_password` cannot be `null`.

* **Basic Settings**

```php
$client->set(array(
    'http_proxy_host' => '192.168.1.100',
    'http_proxy_port' => 1080,
));
```

* **Authentication Settings**

```php
$client->set(array(
    'http_proxy_user' => 'test',
    'http_proxy_password' => 'test_123456',
));
```
### bind

!> Setting only `bind_port` is ineffective, please set both `bind_port` and `bind_address` together.

?> In the case where the machine has multiple network cards, setting the `bind_address` parameter can force the client `Socket` to bind to a specific network address.  
Setting `bind_port` allows the client `Socket` to connect to an external server using a fixed port.

```php
$client->set(array(
    'bind_address' => '192.168.1.100',
    'bind_port' => 36002,
));
```
### Scope

The above `Client` configuration options also apply to the following clients:

* [Swoole\Coroutine\Client](/coroutine_client/client)
* [Swoole\Coroutine\Http\Client](/coroutine_client/http_client)
* [Swoole\Coroutine\Http2\Client](/coroutine_client/http2_client)
