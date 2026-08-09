# Swoole\Server\StatusInfo

Berikut adalah penjelasan detail tentang `Swoole\Server\StatusInfo`.

## Properti

### $worker_id
Mengembalikan id proses `worker` saat ini. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\StatusInfo->worker_id
```

### $worker_pid
Mengembalikan id proses induk dari proses `worker` saat ini. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\StatusInfo->worker_pid
```

### $status
Mengembalikan status proses `status`. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\StatusInfo->status
```

### $exit_code
Mengembalikan kode status keluar proses `exit_code`. Properti ini adalah integer dengan tipe `int`, rentang `0-255`.

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
Sinyal keluar proses `signal`. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\StatusInfo->signal
```
