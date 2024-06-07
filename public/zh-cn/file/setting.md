# 配置

`Swoole`设置了几个关键参数影响`异步`文件操作的特性，可以通过`swoole_async_set`或者`Swoole\Server->set()`来设置。

示例：

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

?> 设置线程池的最小线程数，默认值为`CPU核数`。

### aio_worker_num

?> 设置线程池的最大线程数，默认值为`CPU核数 * 8`。

### aio_max_wait_time

?> 设置线程池中的线程的最大等待时间，默认值为`0`。

### aio_max_idle_time

?> 设置线程池中的线程的空闲时间，默认值为`1s`。

### iouring_entries

?> 设置`io_uring`的队列大小，默认为`8192`，如果传入的值不是`2的次方数`，内核会修改为最接近的，大于该值的`2的次方数`。

!> 如果传入的值过大，内核会抛出异常并且终止程序。

!> 当系统安装了`liburing`和编译`Swoole`开启了`--enable-iouring`之后才能使用。