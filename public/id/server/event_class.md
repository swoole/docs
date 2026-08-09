# Swoole\Server\Event

Berikut adalah penjelasan detail tentang `Swoole\Server\Event`.

## Properti

### $reactor_id
Mengembalikan id thread `Reactor` tempatnya berada. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Event->reactor_id
```

### $fd
Mengembalikan file descriptor `fd` dari koneksi tersebut. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Event->fd
```

### $dispatch_time
Mengembalikan waktu kedatangan data permintaan `dispatch_time`. Properti ini adalah `double`. Properti ini hanya tidak bernilai `0` di event `onReceive`.

```php
Swoole\Server\Event->dispatch_time
```

### $data
Mengembalikan data `data` yang dikirim oleh klien. Properti ini adalah string dengan tipe `string`. Properti ini tidak `null` hanya di event `onReceive`.

```php
Swoole\Server\Event->data
```
