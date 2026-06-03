# Swoole\Server\PipeMessage

Berikut adalah penjelasan detail tentang `Swoole\Server\PipeMessage`.

## Properti

### $source_worker_id
Mengembalikan id proses `worker` dari sumber data. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\PipeMessage->source_worker_id
```

### $dispatch_time
Mengembalikan waktu kedatangan data permintaan `dispatch_time`. Properti ini adalah `double`.

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
Mengembalikan data `data` yang dibawa oleh koneksi. Properti ini adalah string dengan tipe `string`.

```php
Swoole\Server\PipeMessage->data
```
