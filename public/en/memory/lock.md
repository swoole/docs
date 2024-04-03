# Inter-process Lock

In `PHP` code, it is very convenient to create a lock for data synchronization. The `Lock` class supports `5` types of locks.

Lock Type | Description
---|---
SWOOLE_MUTEX | Mutex lock
SWOOLE_RWLOCK | Read-write lock
SWOOLE_SPINLOCK | Spin lock
SWOOLE_FILELOCK | File lock (deprecated)
SWOOLE_SEM | Semaphore (deprecated)

!> Do not create a lock in callback functions like [onReceive](/server/events?id=onreceive), as it will cause memory to continuously increase, leading to memory leaks.

## Usage Example

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

## Warning

!> Locks cannot be used in coroutines. Use them with caution and avoid using APIs that may cause coroutine switches between `lock` and `unlock` operations.

### Error Example

!> This code will result in `100%` deadlock in coroutine mode. Refer to [this article](https://course.swoole-cloud.com/article/2).

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

## Methods

### __construct()

Constructor.

```php
Swoole\Lock::__construct(int $type = SWOOLE_MUTEX, string $lockfile = '');
```

!> Do not create/destroy lock objects in a loop, as it will result in memory leaks.

  * **Parameters** 

    * **`int $type`**
      * **Function**: Type of lock
      * **Default value**: `SWOOLE_MUTEX`【Mutex lock】
      * **Other values**: None

    * **`string $lockfile`**
      * **Function**: Specify the file path for file lock 【Must be passed when type is `SWOOLE_FILELOCK`】
      * **Default value**: None
      * **Other values**: None

!> Each type of lock supports different methods. For example, read-write locks and file locks can support `$lock->lock_read()`. Also, except for file locks, other types of locks must be created in the parent process so that the child processes forked from it can compete for locks.

### lock()

Lock operation. If another process holds the lock, this will block until the process holding the lock releases it.

```php
Swoole\Lock->lock(): bool
```

### trylock()

Lock operation. Unlike the `lock` method, `trylock()` does not block and will return immediately.

```php
Swoole\Lock->trylock(): bool
```

  * **Return Value**

    * Returns `true` if the lock is acquired successfully, allowing modification of shared variables
    * Returns `false` if the lock acquisition fails, indicating that another process holds the lock

!> `SWOOlE_SEM` Semaphore does not have a `trylock` method.

### unlock()

Release the lock.

```php
Swoole\Lock->unlock(): bool
```

### lock_read()

Read-only lock.

```php
Swoole\Lock->lock_read(): bool
```

* While holding a read lock, other processes can still obtain read locks and perform read operations.
* However, it is not possible to call `$lock->lock()` or `$lock->trylock()` during this time. These two methods acquire exclusive locks, meaning that other processes cannot perform any lock operations, including read locks.
* When another process acquires an exclusive lock by calling `$lock->lock()`/`$lock->trylock()`, `$lock->lock_read()` will block until the process with the exclusive lock releases it.

!> Only `SWOOLE_RWLOCK` and `SWOOLE_FILELOCK` types of locks support read-only locks.

### trylock_read()

Lock operation. This method is the same as `lock_read()`, but non-blocking.

```php
Swoole\Lock->trylock_read(): bool
```

!> It returns immediately upon calling, and you must check the return value to determine if the lock was acquired.

### lockwait()

Lock operation. Similar to the `lock()` method, but `lockwait()` allows setting a timeout.

```php
Swoole\Lock->lockwait(float $timeout = 1.0): bool
```

  * **Parameter** 

    * **float $timeout**
      * **Function**: Specify the timeout
      * **Value Unit**: Seconds【Supports floating point values, e.g., `1.5` represents `1s`+`500ms`】
      * **Default value**: `1`
      * **Other values**: None

  * **Return Value**

    * Returns `false` if the lock is not acquired within the specified time
    * Returns `true` if the lock is acquired successfully

!> Only `Mutex` type of locks support `lockwait`.
