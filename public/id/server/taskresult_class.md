# Swoole\Server\TaskResult

Berikut adalah penjelasan detail tentang `Swoole\Server\TaskResult`.

## Properti

### $task_id
Mengembalikan id thread `Reactor` tempat tugas berada. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\TaskResult->task_id
```

### $task_worker_id
Mengembalikan dari proses `task` mana hasil eksekusi berasal. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\TaskResult->task_worker_id
```

### $dispatch_time
Mengembalikan data `data` yang dibawa oleh koneksi. Properti ini adalah `?string`.

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
Mengembalikan data `data` yang dibawa oleh koneksi. Properti ini adalah string dengan tipe `string`.

```php
Swoole\Server\StatusInfo->data
```
