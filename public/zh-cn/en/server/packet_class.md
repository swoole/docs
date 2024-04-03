# Swoole\Server\Packet

Here is a detailed introduction to `Swoole\Server\Packet`.

## Properties

### $server_socket
Returns the server file descriptor `fd`, which is an integer of type `int`.

```php
Swoole\Server\Packet->server_socket
```

### $server_port
Returns the server listening port `server_port`, which is an integer of type `int`.

```php
Swoole\Server\Packet->server_port
```

### $dispatch_time
Returns the time the request data arrived `dispatch_time`, which is a double type.

```php
Swoole\Server\Packet->dispatch_time
```

### $address
Returns the client address `address`, which is a string type.

```php
Swoole\Server\Packet->address
```

### $port
Returns the client listening port `port`, which is an integer of type `int`.

```php
Swoole\Server\Packet->port
```

### $data
Returns the data passed by the client `data`, which is a string type.

```php
Swoole\Server\Packet->data
```
