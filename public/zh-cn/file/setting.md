# 配置

`Swoole`设置了几个关键参数影响`异步`文件操作的特性，可以通过`swoole_async_set`或者`Swoole\Server->set()`来设置。

示例：

```php
<?php
swoole_async_set([
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024,
    'iouring_workers' => 16,
    'iouring_flag' => SWOOLE_IOURING_SQPOLL
]);

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024,
    'iouring_workers' => 16,
    'iouring_flag' => SWOOLE_IOURING_SQPOLL
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

!> 当系统安装了`liburing`并且编译`Swoole`时开启`--enable-iouring`选项才能使用。

### iouring_workers

?> 设置`io_uring`的工作线程数，默认值是`CPU 核数 * 4`。

!> 如果传入的值过大，内核会抛出异常并且终止程序。

!> 当系统安装了`liburing`并且编译`Swoole`时开启`--enable-iouring`选项才能使用。

### iouring_flag

?> 设置`io_uring`的工作模式，默认值为`SWOOLE_IOURING_DEFAULT`。

- `SWOOLE_IOURING_DEFAULT`，中断驱动模式，可通过系统调用`io_uring_enter`提交`I/O`请求，然后直接检查完成队列状态判断是否完成。
- `SWOOLE_IOURING_SQPOLL`，内核轮询模式，内核会创建内核线程用于提交和收割`I/O`请求，几乎完全消除用户态内核态上下文切换，性能较好。

!> 如果传入的模式不正确，内核会统一使用`SWOOLE_IOURING_DEFAULT`中断驱动模式。

!> `SWOOLE_IOURING_SQPOLL`是通过牺牲一部分CPU性能换取更高的磁盘读写性能（IOPS），因此QPS会比默认模式差。 

!> 如果服务压力不大，可以使用`SWOOLE_IOURING_DEFAULT`获取更高的QPS，如果确定服务性能瓶颈是在磁盘，可以使用`SWOOLE_IOURING_SQPOLL`。 
