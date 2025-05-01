# 锁 Lock

* 使用`Swoole`提供的锁可以很方便代码中实现数据同步。
* 多进程模式下请使用`Swoole\Lock`，多线程模式下请使用`Swoole\Thread\Lock`，除了命名空间不一样，其接口完全一致。
* `swoole6.0`引入协程锁`Swoole\Coroutine\Lock`，使得跨进程/线程之间的协程加锁不再阻塞进程，协程锁接口与多进程锁和多线程锁略有不同。
* 该协程锁默认通过`原子计数`和`sleep`实现可重入的互斥锁，如果`linux`内核为6.7以上，`liburing`版本为2.6以上，编译时`swoole6.0`开启了`--enable-iouring`，协程锁的底层将会替换成`io_uring`的`futex`特性，相关的配置参数可以查看[文件异步操作 - 配置](/file/setting?id=iouring_entries)。

## 使用示例

```php
$lock = new Swoole\Lock(SWOOLE_MUTEX);
echo "[Master]create lock\n";
$lock->lock();
if (pcntl_fork() > 0)
{
  sleep(1);
  $lock->unlock();
} 
else
{
  echo "[Child] Wait Lock\n";
  $lock->lock();
  echo "[Child] Get Lock\n";
  $lock->unlock();
  exit("[Child] exit\n");
}
echo "[Master]release lock\n";
unset($lock);
sleep(1);
echo "[Master]exit\n";
```

## 方法

### __construct()

构造函数。

```php
Swoole\Lock::__construct(int $type = SWOOLE_MUTEX, string $lockfile = '');
```

!> 不要循环创建/销毁锁的对象，否则会发生内存泄漏。

!> 协程锁的构造函数不需要传入任何参数。

!> 每一种类型的锁支持的方法都不一样。如读写锁、文件锁可以支持`$lock->lock_read()`。另外除文件锁外，其他类型的锁必须在父进程内创建，这样`fork`出的子进程之间才可以互相争抢锁。

  * **参数** 

    * **`int $type`**
      * **功能**：锁的类型
      * **默认值**：`SWOOLE_MUTEX`【互斥锁】
      * **其它值**：`读写锁 SWOOLE_RWLOCK`，`自旋锁 SWOOLE_SPINLOCK`，`文件锁 SWOOLE_FILELOCK （已废弃）`，`信号量 SWOOLE_SEM（已废弃）`

    * **`string $lockfile`**
      * **功能**：指定文件锁的路径【当类型为`SWOOLE_FILELOCK`时必须传入】
      * **默认值**：无
      * **其它值**：无


### lock()

加锁操作。如果有其他进程持有锁，那这里将进入阻塞，直到持有锁的进程`unlock()`释放锁。

```php
Swoole\Lock->lock(): bool
```
* 如果是协程锁，当其他`进程`的协程持有锁，那当前协程`不会阻塞`，而是会让出CPU，等待`唤醒`。

### trylock()

加锁操作。与`lock`方法不同的是，`trylock()`不会阻塞，它会立即返回。

```php
Swoole\Lock->trylock(): bool
```

  * **返回值**

    * 加锁成功返回`true`，此时可以修改共享变量
    * 加锁失败返回`false`，表示有其他进程持有锁

!> `SWOOlE_SEM` 信号量没有`trylock`方法

### lock_read()

只读加锁。

```php
Swoole\Lock->lock_read(): bool
```

* 在持有读锁的过程中，其他进程依然可以获得读锁，可以继续发生读操作；
* 但不能`$lock->lock()`或`$lock->trylock()`，这两个方法是获取独占锁，在独占锁加锁时，其他进程无法再进行任何加锁操作，包括读锁；
* 当另外一个进程获得了独占锁(调用`$lock->lock()`/`$lock->trylock()`)时，`$lock->lock_read()`会发生阻塞，直到持有独占锁的进程释放锁。

!> 协程锁没有`lock_read()`函数。

!> 只有`SWOOLE_RWLOCK`和`SWOOLE_FILELOCK`类型的锁支持只读加锁。

### trylock_read()

加锁。此方法与`lock_read()`相同，但是非阻塞的。

```php
Swoole\Lock->trylock_read(): bool
```

!> 协程锁没有`trylock_read()`函数。

!> 调用会立即返回，必须检测返回值以确定是否拿到了锁。

### lockwait()

加锁操作。作用与`lock()`方法一致，但`lockwait()`可以设置超时时间。

```php
Swoole\Lock->lockwait(float $timeout = 1.0): bool
```

  * **参数** 

    * **`float $timeout`**
      * **功能**：指定超时时间
      * **值单位**：秒【支持浮点型，如`1.5`表示`1s`+`500ms`】
      * **默认值**：`1`
      * **其它值**：无

  * **返回值**

    * 在规定的时间内未获得锁，返回`false`
    * 加锁成功返回`true`

!> 协程锁没有`lockwait()`函数。

!> 只有`Mutex`类型的锁支持`lockwait`

### unlock()

释放锁。

```php
Swoole\Lock->unlock(): bool
```

## 注意
!> 请勿在[onReceive](/server/events?id=onreceive)等回调函数中创建锁，否则内存会持续增长，造成内存泄漏。

!> `原子计数`和`sleep`实现的协程锁比起`io_uring`实现的协程锁，性能会较差，因为它会带来不必要的上下文切换。

!> 加锁和解锁必须在同一个进程/线程/协程中完成，不然会破坏竞争条件，无法有效实现互斥。

!> 如果不是使用协程锁，此代码在协程模式下`100%`死锁。

```php
$lock = new Swoole\Lock();
$c = 2;

while ($c--) {
  go(function () use ($lock) {
      $lock->lock();
      Co::sleep(1);
      $lock->unlock();
  });
}
```
