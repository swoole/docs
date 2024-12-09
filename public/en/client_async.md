# Swoole\Async\Client

The `Swoole\Async\Client`, referred to as `Client` hereafter, is an asynchronous non-blocking network client supporting `TCP/UDP/UnixSocket`. Unlike synchronous clients, asynchronous clients require setting up event callback functions instead of waiting synchronously.

- The asynchronous client is a subclass of `Swoole\Client` and can call some methods of the synchronous blocking client.
- Available only in versions `6.0` and above.

## Complete Example

```php
$cli = new Swoole\Async\Client(SWOOLE_SOCK_TCP);

$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());
    $client->send(RandStr::gen(1024, RandStr::ALL));
});

$client->on("receive", function(Swoole\Async\Client $client, string $data){
    $recv_len = strlen($data);
    $client->send(RandStr::gen(1024, RandStr::ALL));
    $client->close();
    Assert::false($client->isConnected());
});

$client->on("error", function(Swoole\Async\Client $client) {
    echo "error";
});

$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});

$client->connect("127.0.0.1", 9501, 0.2);
```

## Methods

This page only lists methods that differ from `Swoole\Client`. For methods not modified by subclasses, please refer to [Synchronous Blocking Client](client.md).

### __construct()

Constructor, refer to parent class constructor.

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> The second parameter of the asynchronous client must be `true`.

### on()

Register an event callback function for the `Client`.

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> Calling `on` multiple times will overwrite the previous settings.

  * **Parameters**

    * `string $event`

      * Function: Callback event name, case-insensitive.
      * Default value: None.
      * Other values: None.

    * `callable $callback`

      * Function: Callback function.
      * Default value: None.
      * Other values: None.

      !> Can be a string of a function name, a class static method, an array of object methods, or an anonymous function. Refer to [this section](/learn?id=several_ways_to_set_callbacks).
  
  * **Return Value**

    * Returns `true` if the operation is successful, returns `false` otherwise.

### isConnected()
Determine whether the current client has established a connection with the server.

```php
Swoole\Async\Client->isConnected(): bool
```

* Returns `true` if connected, returns `false` if not connected.

### sleep()
Temporarily stop receiving data. After calling, the client will be removed from the event loop and no longer trigger data reception events unless the `wakeup()` method is called to resume.

```php
Swoole\Async\Client->sleep(): bool
```

* Returns `true` if the operation is successful, returns `false` otherwise.

### wakeup()
Resume receiving data. After calling, the client will be added back to the event loop.

```php
Swoole\Async\Client->wakeup(): bool
```

* Returns `true` if the operation is successful, returns `false` otherwise.

### enableSSL()
Dynamically enable `SSL/TLS` encryption, typically used for `startTLS` clients. Send plaintext data first after establishing a connection, then initiate encrypted transmission.

```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* This function can only be called after a successful `connect`.
* The asynchronous client must set `$callback`, which will be invoked after the `SSL` handshake is complete.
* Returns `true` if the operation is successful, returns `false` otherwise.

## Callback Events

### connect
Triggered after a connection is established. If an `HTTP` or `Socks5` proxy and `SSL` tunnel encryption are set, it will be triggered after the proxy handshake is complete and the `SSL` encryption handshake is completed.

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

After this event callback, using `isConnected()` will return `true`.

### error
Triggered when a connection fails to establish. You can obtain error information by reading `$client->errCode`.
```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```

- Please note that either the `connect` or `error` event will be triggered, but not both. There can only be one outcome: a successful or failed connection.

- `Client::connect()` may directly return `false`, indicating a failed connection, at which point the `error` callback will not be executed. Be sure to check the return value of the `connect` call.

- The `error` event is asynchronous; there will be a certain `IO` wait time between initiating the connection and the `error` event being triggered.
- A failed `connect` indicates an immediate failure, triggered directly by the operating system without any `IO` wait time.

### receive
Triggered after data is received.

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```

- If no protocol is set, such as `EOF` or `LENGTH`, the maximum returned data length is `64K`.

- If protocol handling parameters are set, the maximum data length is determined by the `package_max_length` parameter, which defaults to `2M`.
- `$data` will not be empty. If a system error or connection close is received, the `close` event will be triggered.

### close
Triggered when the connection is closed.

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```
