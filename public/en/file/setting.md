# Configuration

The `Swoole` library sets several key parameters that affect the characteristics of asynchronous file operations, which can be configured using `swoole_async_set` or `Swoole\Server->set()`.

Example:

```php
<?php
swoole_async_set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);
```

### aio_core_worker_num

?> Sets the minimum number of threads in the thread pool, with a default value of `CPU core count`.

### aio_worker_num

?> Sets the maximum number of threads in the thread pool, with a default value of `CPU core count * 8`.

### aio_max_wait_time

?> Sets the maximum wait time for threads in the thread pool, with a default value of `0`.

### aio_max_idle_time

?> Sets the idle time for threads in the thread pool, with a default value of `1s`.

### iouring_entries

?> Sets the size of the `io_uring` queue, with a default value of `8192`. If the value passed is not a power of 2, the kernel will round it up to the nearest power of 2.

!> If the value passed is too large, the kernel may throw an exception and terminate the program.

!> This feature can only be used if the system has `liburing` installed and `Swoole` is compiled with `--enable-iouring`.

!> This feature can only be used if the system has `liburing` installed and `Swoole v6.0` or above is compiled with `--enable-iouring`.
