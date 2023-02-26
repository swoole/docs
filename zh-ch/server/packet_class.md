# Swoole\Server\Packet

这里是对`Swoole\Server\Packet`的详细介绍。

## 属性

### $server_socket
返回服务端文件描述符`fd`，该属性是一个`int`类型的整数。

```php
Swoole\Server\Packet->server_socket
```

### $server_port
返回服务端监听端口`server_port`，该属性是一个`int`类型的整数。

```php
Swoole\Server\Packet->server_port
```

### $dispatch_time
返回该请求数据到达时间`dispatch_time`，该属性是一个`double`类型。

```php
Swoole\Server\Packet->dispatch_time
```

### $address
返回客户端地址`address`，该属性是一个`string`类型的字符串。

```php
Swoole\Server\Packet->address
```

### $port
返回客户端监听端口`port`，该属性是一个`int`类型的整数。

```php
Swoole\Server\Packet->port
```

### $data
返回客户端的传递的数据`data`，该属性是一个`string`类型的字符串。

```php
Swoole\Server\Packet->data
```