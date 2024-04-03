# Atomic counter for inter-process communication

`Atomic` is a class provided by the underlying `Swoole` framework for atomic counting operations, making it easy to increment or decrement integers without locks.

* Using shared memory enables counting operations across different processes.
* Based on CPU atomic instructions from `gcc/clang`, no locking is required.
* Must be created before calling `Server->start` in server programs to be used in `Worker` processes.
* By default, it uses a `32`-bit unsigned type, but if a `64`-bit signed integer is needed, you can use `Swoole\Atomic\Long`.

!> Avoid creating counters in callbacks like [onReceive](/server/events?id=onreceive) to prevent continuous memory growth and memory leaks.

!> `64`-bit signed long integer atomic counting is supported with `new Swoole\Atomic\Long`. `Atomic\Long` does not support `wait` and `wakeup` methods.
## Complete example

```php
$atomic = new Swoole\Atomic();

$serv = new Swoole\Server('127.0.0.1', '9501');
$serv->set([
    'worker_num' => 1,
    'log_file' => '/dev/null'
]);
$serv->on("start", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStart", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStop", function ($serv) {
    echo "shutdown\n";
});
$serv->on("Receive", function () {
    
});
$serv->start();
```  
## Methods
### __construct()

Constructor. Creates an atomic counter object.

```php
Swoole\Atomic::__construct(int $init_value = 0);
```

  * **Parameters** 

    * **`int $init_value`**
      * **Function**：Specifies the initial value
      * **Default value**：`0`
      * **Other values**：none

!> - `Atomic` can only operate on `32`-bit unsigned integers, with a maximum support of `4.2` billion, and does not support negative numbers;
- When using atomic counters in a `Server`, it must be created before calling `Server->start`;  
- When using atomic counters in a [Process](/process/process), it must be created before calling `Process->start`.
### add()

Increase the count.

```php
Swoole\Atomic->add(int $add_value = 1): int
```

  * **Parameters** 

    * **`int $add_value`**
      * **Description**: The value to be added 【must be a positive integer】
      * **Default Value**: `1`
      * **Other Values**: N/A

  * **Return Value**

    * The resulting value after the `add` method operation

!> Adding to the original value will cause overflow if it exceeds `4.2` billion, and the high bits will be discarded.
### sub()

Decrease the count.

```php
Swoole\Atomic->sub(int $sub_value = 1): int
```

  * **Parameters** 

    * **`int $sub_value`**
      * **Description**: The value to be subtracted 【must be a positive integer】
      * **Default Value**: `1`
      * **Other Values**: N/A

  * **Return Value**

    * Returns the resulting value after the `sub` method operation

!> If subtracting from the original value results in a number below 0, it will overflow and the high bits will be discarded.
### get()

Get the value of the current count.

```php
Swoole\Atomic->get(): int
```

  * **Return Value**

    * Returns the current numerical value
### set()

Set the current value to the specified number.

```php
Swoole\Atomic->set(int $value): void
```

* **Parameters**

  * **`int $value`**
    * **Function**: Specifies the target number to set
    * **Default value**: None
    * **Other values**: None
### cmpset()

If the current value is equal to parameter `1`, then set the current value to parameter `2`.

```php
Swoole\Atomic->cmpset(int $cmp_value, int $set_value): bool
```

  * **Parameters** 

    * **`int $cmp_value`**
      * **Description**: If the current value equals `$cmp_value`, return `true` and set the current value to `$set_value`, return `false` if it does not【Must be an integer less than `4.2` billion】
      * **Default**: None
      * **Other**: None

    * **`int $set_value`**
      * **Description**: If the current value equals `$cmp_value`, return `true` and set the current value to `$set_value`, return `false` if it does not【Must be an integer less than `42` billion】
      * **Default**: None
      * **Other**: None
### wait()

Set to wait state.

!> The program enters a waiting state when the value of the atomic counter is 0. Another process calling `wakeup` can awaken the program again. Based on the `Linux Futex` implementation at the lower level, with this feature, a waiting, notification, and locking function can be implemented using only `4` bytes of memory. In platforms that do not support `Futex`, the underlying implementation will use a loop with `usleep(1000)` to simulate it.

```php
Swoole\Atomic->wait(float $timeout = 1.0): bool
```

  * **Parameters**

    * **`float $timeout`**
      * **Function**: Specify the timeout period [setting as `-1` means no timeout, it will wait until another process wakes it up].
      * **Unit**: Seconds [supports floating point, e.g., `1.5` represents `1s`+`500ms`].
      * **Default Value**: `1`
      * **Other Values**: None

  * **Return Value**

    * Timeout returns `false`, with error code `EAGAIN`, can be obtained using the `swoole_errno` function.
    * Success returns `true`, indicating that another process has successfully awakened the current lock through `wakeup`.

  * **Coroutine Environment**

  `wait` will block the entire process instead of just a coroutine, so do not use `Atomic->wait()` in a coroutine environment to avoid process hanging.

!> - When using the `wait/wakeup` feature, the value of an atomic counter can only be `0` or `1`, otherwise it may not function normally.
- If the value of the atomic counter is `1`, it means that there is no need to enter a waiting state, and the resource is currently available. The `wait` function will return `true` immediately.

  * **Usage example**

    ```php
    $n = new Swoole\Atomic;
    if (pcntl_fork() > 0) {
        echo "master start\n";
        $n->wait(1.5);
        echo "master end\n";
    } else {
        echo "child start\n";
        sleep(1);
        $n->wakeup();
        echo "child end\n";
    }
    ```  
### wakeup()

Wake up other processes that are in a waiting state.

```php
Swoole\Atomic->wakeup(int $n = 1): bool
```

  * **Parameters** 

    * **`int $n`**
      * **Description**: Number of processes to wake up
      * **Default Value**: N/A
      * **Other Values**: N/A

* If the current atomic count is `0`, it means no process is `wait`-ing, and `wakeup` will immediately return `true`.
* If the current atomic count is `1`, it means there is a process currently `wait`-ing, and `wakeup` will wake up the waiting process and return `true`.
* After the woken process returns, the atomic count is set to `0`, and then you can call `wakeup` again to wake up other processes that are `wait`-ing.
