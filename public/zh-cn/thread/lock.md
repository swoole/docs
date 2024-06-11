# 线程互斥锁
专门用于线程同步的互斥锁，其接口与 `Swoole/Lock` 完全一致，
但是 `Swoole/Thread/Lock` 是基于线程的互斥锁，而 `Swoole/Lock` 是基于进程的互斥锁。


`Swoole/Thread/Lock` 可以安全地动态创建和销毁，并且可通过 `ArrayList`、`Map`、`Queue` 或者作为线程参数传递给其他线程。

## 构造对象

```php
function Swoole\Thread\Lock::__construct(int $type = SWOOLE_MUTEX)
```
- `$type`：锁的类型，默认的类型为 `SWOOLE_MUTEX`

## 方法
参考 [Swoole\Lock](memory/lock.md)

## 实例
```php
use Swoole\Thread;
use Swoole\Thread\Lock;

$args = Thread::getArguments();

if (empty($args)) {
    $lock = new Lock;
    $lock->lock();
    $thread = Thread::exec(__FILE__, $lock);
    $lock->lock();
    echo "main thread\n";
    $thread->join();
} else {
    $lock = $args[0];
    sleep(1);
    $lock->unlock();
}

```
