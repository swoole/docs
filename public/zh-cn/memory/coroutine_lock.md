# 协程锁 Coroutine Lock

* `Swoole 6.0` 新增了支持进程间和线程间共享的协程锁，该锁采用非阻塞设计，能够实现多进程和多线程环境下的高效协程同步。
* 在编译时启用 `--enable-iouring` 选项且 Linux 内核支持 `io_uring futex` 特性的情况下，Swoole 的协程锁将基于 `io_uring futex` 实现同步机制。此时，协程会以高效的排队方式等待锁唤醒，从而显著提升性能。
* 如果未启用 `io_uring futex`，协程锁将退化为基于指数退避的 sleep 机制，即每次获取锁失败后，等待时间按 2^n 毫秒递增（n 为失败次数）。这种方式虽然能避免忙等待，但会引入额外的 CPU 调度开销和延迟。
* 协程锁采用可重入设计，允许当前持有锁的协程多次安全地执行加锁操作。

!> 请勿在[onReceive](/server/events?id=onreceive)等回调函数中创建锁，否则内存会持续增长，造成内存泄漏。

!> 加锁和解锁必须在同一个协程中进行，否则会导致静态条件被破坏。

## 使用示例
```php
use Swoole\Coroutine\Lock;
use Swoole\Coroutine\WaitGroup;
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

$lock = new Lock();
$waitGroup = new WaitGroup();

run(function() use ($lock, $waitGroup) {
    go(function() use ($lock, $waitGroup) {
        $waitGroup->add();
        $lock->lock();
        sleep(1);
        $lock->unlock();
        $waitGroup->done();
    });
    
    go(function() use ($lock, $waitGroup) {
        $waitGroup->add();
        $lock->lock(); // 等待持有锁的协程解锁
        sleep(1);
        $lock->unlock();
        $waitGroup->done();
    });
       
    echo '锁不阻塞进程';
    $waitGroup->wait();
});
```

## 方法

### __construct()

构造函数。

```php
Swoole\Coroutine\Lock::__construct();
```

### lock()

当执行加锁操作时，如果锁已被其他协程持有，当前协程会主动让出 CPU 控制权，进入挂起状态。待持有锁的协程调用`unlock()`释放锁后，等待中的协程会被唤醒并重新尝试获取锁。

```php
Swoole\Coroutine\Lock::lock(): bool;
```

* **返回值**

    * 加锁成功返回`true`，此时可以修改共享变量。
    * 加锁失败返回`false`。

### trylock()

当调用加锁操作时，如果锁已被其他协程持有，该函数会立即返回 false，而不会挂起当前协程或让出 CPU 控制权。这种非阻塞的设计允许调用方灵活处理竞争情况，例如：重试、放弃或执行其他逻辑。

```php
Swoole\Coroutine\Lock::trylock(): bool;
```

* **返回值**

    * 加锁成功返回`true`，此时可以修改共享变量。
    * 加锁失败返回`false`，由用户决定下一步操作。

### unlock()

当持有锁的协程调用 unlock() 释放锁时：
  * 启用 `io_uring futex` 时：系统会精确唤醒等待队列中的一个协程，确保高效、有序的锁交接。
  * 未启用 `io_uring futex` 时：等待中的协程需等待其退避时间结束后，通过竞争重新尝试获取锁。

```php
Swoole\Coroutine\Lock::unlock(): bool;
```

* **返回值**

    * 解锁成功返回`true`。
    * 解锁失败返回`false`。
