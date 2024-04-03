# Timer

A millisecond-precision timer. The underlying implementation is based on `epoll_wait` and `setitimer`, and the data structure uses a `min heap`, which can support adding a large number of timers.

* Used in synchronous I/O processes with `setitimer` and signals, such as the `Manager` and `TaskWorker` processes
* Used in asynchronous I/O processes with timeout implementation using `epoll_wait`/`kevent`/`poll`/`select`
## Performance

The underlying implementation of the timer uses a minimum heap data structure. The addition and deletion of timers are all done in memory, so the performance is very high.

> In the official benchmark script [timer.php](https://github.com/swoole/benchmark/blob/master/timer.php), adding or deleting `100,000` timers with random time consumes around `0.08s`.

```shell
~/workspace/swoole/benchmark$ php timer.php
add 100000 timer :0.091133117675781s
del 100000 timer :0.084658145904541s
```

!> Timers are memory operations and have no `IO` consumption.
## Differences

`Timer` and `pcntl_alarm` in `PHP` itself are different. `pcntl_alarm` is based on `clock signal + tick` function implementation which has some shortcomings:

  * The maximum support is only up to seconds, while `Timer` can go down to milliseconds
  * Does not support setting multiple timer programs at the same time
  * `pcntl_alarm` depends on `declare(ticks = 1)`, which has poor performance
## Zero-millisecond Timer

The underlying system does not support timers with a time parameter of `0`. This is different from languages like `Node.js`. In `Swoole`, you can use [Swoole\Event::defer](/event?id=defer) to achieve similar functionality.

```php
Swoole\Event::defer(function () {
  echo "hello\n";
});
```

!> The above code has the exact same effect as `setTimeout(func, 0)` in `JS`.
## Aliases

`tick()`, `after()`, and `clear()` all have function-style aliases

Static method | Function-style alias
---|---
`Swoole\Timer::tick()` | `swoole_timer_tick()`
`Swoole\Timer::after()` | `swoole_timer_after()`
`Swoole\Timer::clear()` | `swoole_timer_clear()`
## Methods
### tick()

Set an interval clock timer.

Different from the `after` timer, the `tick` timer will continue to trigger until it is cleared by calling [Timer::clear](/timer?id=clear).

```php
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int
```

!> 1. The timer is only valid within the current process space.
   2. The timer is purely asynchronous, cannot be used with functions that involve synchronous I/O, otherwise the timing of the timer will be inaccurate.
   3. There may be certain errors in the timer during its execution.

  * **Parameters** 

    * **`int $msec`**
      * **Function**: Specify the time.
      * **Value unit**: Milliseconds [e.g. `1000` represents `1` second, for versions below `v4.2.10` the maximum value should not exceed `86400000`]
      * **Default value**: None
      * **Other values**: None

    * **`callable $callback_function`**
      * **Function**: Function to be executed after the time expires, must be callable.
      * **Default value**: None
      * **Other values**: None

    * **`...$params`**
      * **Function**: Pass data to the executing function [this parameter is optional].
      * **Default value**: None
      * **Other values**: None
      
      !> You can use the `use` syntax of anonymous functions to pass parameters to the callback function.

  * **Callback Function** 

    ```php
    callbackFunction(int $timer_id, ...$params);
    ```

      * **`int $timer_id`**
        * **Function**: ID of the timer [can be used to clear this timer using [Timer::clear](/timer?id=clear)].
        * **Default value**: None
        * **Other values**: None

      * **`...$params`**
        * **Function**: The third parameter `$param` passed by `Timer::tick`.
        * **Default value**: None
        * **Other values**: None

  * **Extensions**

    * **Timer Correction**

      The execution time of the timer callback function does not affect the timing of the next timer execution. For example, setting a `tick` timer of `10ms` after `0.002s`, the first callback will be executed at `0.012s.`, if the callback function takes `5ms` to execute, the next timer will still trigger at `0.022s`, not at `0.027s`.

      However, if the execution time of the timer callback function is too long, even covering the time of the next timer execution, the underlying system will perform time correction, discarding the expired behavior and triggering the timer callback at the next available time. For example, if the callback function at `0.012s` takes `15ms` to execute, causing the timer at `0.022s` to be delayed, the timer callback will be triggered again at `0.032s`.

    * **Coroutine Mode**

      In a coroutine environment, a coroutine will automatically be created in the `Timer::tick` callback, allowing the direct use of coroutine-related APIs without the need to call `go` to create a coroutine.
      
      !> You can set [enable_coroutine](/timer?id=close-timer-co) to disable the automatic creation of coroutines.

  * **Usage Example**

    ```php
    Swoole\Timer::tick(1000, function(){
        echo "timeout\n";
    });
    ```

    * **Correct Example**

    ```php
    Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
        echo "timer_id #$timer_id, after 3000ms.\n";
        echo "param1 is $param1, param2 is $param2.\n";

        Swoole\Timer::tick(14000, function ($timer_id) {
            echo "timer_id #$timer_id, after 14000ms.\n";
        });
    }, "A", "B");
    ```

    * **Incorrect Example**

    ```php
    Swoole\Timer::tick(3000, function () {
        echo "after 3000ms.\n";
        sleep(14);
        echo "after 14000ms.\n";
    });
    ```  
### after()

Execute the function after a specified time. The `Swoole\Timer::after` function is a one-time timer that will be destroyed after execution.

This function is non-blocking as opposed to the `sleep` function provided by the PHP standard library. Calling `after` will not block the current process, while `sleep` will cause the current process to enter a blocked state, preventing it from handling new requests.

```php
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int
```

  * **Parameters** 

    * **`int $msec`**
      * **Purpose**: Specifies the time
      * **Unit**: Milliseconds [e.g.,`1000` for `1` second, in versions below `v4.2.10`, the maximum cannot exceed `86400000`]
      * **Default**: None
      * **Other Values**: None

    * **`callable $callback_function`**
      * **Purpose**: The function to be executed when the time is up, must be callable.
      * **Default**: None
      * **Other Values**: None

    * **`...$params`**
      * **Purpose**: Pass data to the execution function [this parameter is optional]
      * **Default**: None
      * **Other Values**: None
      
      !> You can pass parameters to the callback function using the use syntax of anonymous functions.

  * **Return Value**

    * Returns the timer `ID` on successful execution; to cancel the timer, use [Swoole\Timer::clear](/timer?id=clear)

  * **Extensions**

    * **Coroutine Mode**

      In a coroutine environment, a coroutine will be automatically created in the callback of [Swoole\Timer::after](/timer?id=after), allowing you to directly use coroutine-related `APIs` without needing to call `go` to create a coroutine.
      
      !> You can set [enable_coroutine](/timer?id=close-timer-co) to disable the automatic creation of coroutines.

  * **Usage Example**

```php
$str = "Swoole";
Swoole\Timer::after(1000, function() use ($str) {
    echo "Hello, $str\n";
});
```
### clear()

Use the timer `ID` to delete the timer.

```php
Swoole\Timer::clear(int $timer_id): bool
```

  * **Parameters** 

    * **`int $timer_id`**
      * **Function**：Timer `ID`【After calling [Timer::tick](/timer?id=tick) or [Timer::after](/timer?id=after), an integer ID will be returned】
      * **Default value**：None
      * **Other values**：None

!> `Swoole\Timer::clear` cannot be used to clear timers in other processes; it only affects the current process.

  * **Example**

```php
$timer = Swoole\Timer::after(1000, function () {
    echo "timeout\n";
});

var_dump(Swoole\Timer::clear($timer));
var_dump($timer);

// Output: bool(true) int(1)
// No output: timeout
```
### clearAll()

Clear all timers in the current Worker process.

!> Available since Swoole version `v4.4.0`

```php
Swoole\Timer::clearAll(): bool
```
### info()

Returns information about a `timer`.

!> Available for Swoole version >= `v4.4.0`

```php
Swoole\Timer::info(int $timer_id): array
```

  * **Return Value**

```php
array(5) {
  ["exec_msec"]=>
  int(6000)
  ["exec_count"]=> // Added in v4.8.0
  int(5)
  ["interval"]=>
  int(1000)
  ["round"]=>
  int(0)
  ["removed"]=>
  bool(false)
}
```
### list()

Returns a timer iterator, which can be used to traverse all `timer` ids in the current Worker process using `foreach`.

!> Available in Swoole version >= `v4.4.0`

```php
Swoole\Timer::list(): Swoole\Timer\Iterator
```

  * **Usage Example**

```php
foreach (Swoole\Timer::list() as $timer_id) {
    var_dump(Swoole\Timer::info($timer_id));
}
```
### stats()

View the status of the timer.

!> Available for Swoole version >= `v4.4.0`

```php
Swoole\Timer::stats(): array
```

  * **Return Value**

```php
array(3) {
  ["initialized"]=>
  bool(true)
  ["num"]=>
  int(1000)
  ["round"]=>
  int(1)
}
```
### set()

Set the parameters related to the timer.

```php
Swoole\Timer::set(array $array): void
```

!> This method has been marked as deprecated since version `v4.6.0`.
## Close Coroutine: ID=close-timer-co

By default, when a timer is triggered, a coroutine is automatically created to execute the callback function. You can disable coroutine creation for timers by setting it separately.

```php
swoole_async_set([
  'enable_coroutine' => false,
]);
```
