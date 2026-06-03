# Swoole\Server\Packet

Berikut adalah penjelasan detail tentang `Swoole\Server\Packet`.

## Properti

### $server_socket
Mengembalikan file descriptor `fd` server. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Packet->server_socket
```

### $server_port
Mengembalikan port listening `server_port` server. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Packet->server_port
```

### $dispatch_time
Mengembalikan waktu kedatangan data permintaan `dispatch_time`. Properti ini adalah `double`.

```php
Swoole\Server\Packet->dispatch_time
```

### $address
Mengembalikan alamat klien `address`. Properti ini adalah string dengan tipe `string`.

```php
Swoole\Server\Packet->address
```

### $port
Mengembalikan port listening `port` klien. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Packet->port
```

### $data
Mengembalikan data `data` yang dikirimkan oleh klien. Properti ini adalah string dengan tipe `string`.

```php
Swoole\Server\Packet->data
```
