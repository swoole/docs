# Swoole\Server\StatusInfo

This is a detailed introduction to `Swoole\Server\StatusInfo`.

## Properties

### $worker_id
Returns the current `worker` process id, which is an `int` type integer.

```php
Swoole\Server\StatusInfo->worker_id
```

### $worker_pid
Returns the parent process id of the current `worker` process, which is an `int` type integer.

```php
Swoole\Server\StatusInfo->worker_pid
```

### $status
Returns the process status `status`, which is an `int` type integer.

```php
Swoole\Server\StatusInfo->status
```

### $exit_code
Returns the process exit status code `exit_code`, which is an `int` type integer with a range of `0-255`.

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
The signal of the process exit `signal`, which is an `int` type integer.

```php
Swoole\Server\StatusInfo->signal
```
