# Swoole\Server\Packet

這裡是對`Swoole\Server\Packet`的詳細介紹。

## 屬性

### $server_socket
返回服務端文件描述符`fd`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Packet->server_socket
```

### $server_port
返回服務端監聽端口`server_port`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Packet->server_port
```

### $dispatch_time
返回該請求數據到達時間`dispatch_time`，該屬性是一個`double`類型。

```php
Swoole\Server\Packet->dispatch_time
```

### $address
返回客戶端地址`address`，該屬性是一個`string`類型的字符串。

```php
Swoole\Server\Packet->address
```

### $port
返回客戶端監聽端口`port`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Packet->port
```

### $data
返回客戶端的傳遞的數據`data`，該屬性是一個`string`類型的字符串。

```php
Swoole\Server\Packet->data
```
