# Methods and Properties


## Methods


### __construct()
Multi-threaded constructor

```php
Swoole\Thread->__construct(string $script_file, mixed ...$args)
```
* **Parameters**
    * `string $script_file`
        * Function: The file to be executed after the thread starts.
        * Default value: None.
        * Other values: None.

    * `mixed $args`
        * Function: Shared data passed from the main thread to the child thread, which can be obtained using `Swoole\Thread::getArguments()` in the child thread.
        * Default value: None.
        * Other values: None.

!> Thread creation failure will throw a `Swoole\Exception`, which can be caught using a `try-catch` block.


### join()
The main thread waits for the child thread to exit. If the child thread is still running, `join()` will block until the child thread exits.

```php
Swoole\Thread->join(): bool
```
* **Return value**
    * Returns `true` if the operation is successful, returns `false` if the operation fails.


### joinable()
Check if the child thread has exited.

```php
Swoole\Thread->joinable(): bool
```


#### Return values

- `true` means the child thread has exited, and calling `join()` will not cause blocking
- `false` means it has not exited


### detach()
Detach the child thread from the main thread's control, no longer needing `join()` to wait for the thread to exit.

```php
Swoole\Thread->detach(): bool
```
* **Return value**
    * Returns `true` if the operation is successful, returns `false` if the operation fails.


### getId()
Static method to get the current thread's `ID`.

```php
Swoole\Thread::getId(): int
```
* **Return value**
    * Returns an integer representing the current thread's ID.


### getArguments()
Static method to get the shared data passed from the main thread when using `new Swoole\Thread()`, called in the child thread.

```php
Swoole\Thread::getArguments(): ?array
```

* **Return value**
    * Returns the shared data passed by the parent process in the child thread.

?> The main thread will not have any thread arguments. You can distinguish between parent and child threads by checking if the thread arguments are empty, allowing them to execute different logic.
```php
use Swoole\Thread;

$args = Thread::getArguments(); // If it's the main thread, $args is empty, if it's a child thread, $args is not empty
if (empty($args)) {
    # Main thread
    new Thread(__FILE__, 'child thread'); // Pass thread arguments
    echo "main thread\n";
} else {
    # Child thread
    var_dump($args); // Output: ['child thread']
}
```


### getInfo()
Static method to get information about the current multi-threaded environment.

```php
Swoole\Thread::getInfo(): array
```
The returned array contains the following information:



- `is_main_thread`: Whether the current thread is the main thread

- `is_shutdown`: Whether the thread has been shut down
- `thread_num`: The number of active threads


### getPriority()
Static method to get the scheduling information of the current thread

```php
Swoole\Thread->getPriority(): array
```
The returned array contains the following information:



- `policy`: Thread scheduling policy
- `priority`: Thread scheduling priority


### setPriority()
Static method to set the scheduling priority and policy of the current thread

?> Only `root` users can adjust this, and non-`root` users will have their operations rejected.

```php
Swoole\Thread->setPriority(int $priority, int $policy = -1): bool
```

* **Parameters**
    * `int $priority`
        * Function: Set the thread scheduling priority
        * Default value: None.
        * Other values: None.

    * `mixed $policy`
        * Function: Set the thread scheduling priority policy
        * Default value: `-1`, indicating no adjustment to the scheduling policy.
        * Other values: `Thread::SCHED_*` related constants.

* **Return value**
    * Returns `true` on success
    * Returns `false` on failure, use `swoole_last_error()` to get error information

> `SCHED_BATCH/SCHED_ISO/SCHED_IDLE/SCHED_DEADLINE` are only available on `Linux` systems  

> Threads with `SCHED_FIFO/SCHED_RR` policies are generally real-time threads with higher priority than normal threads and can obtain more CPU time slices


### getAffinity()
Static method to get the CPU affinity of the current thread

```php
Swoole\Thread->getAffinity(): array
```
The returned value is an array with elements representing the number of CPU cores, for example: `[0, 1, 3, 4]` indicates that this thread will be scheduled to run on CPU cores `0/1/3/4`.


### setAffinity()
Static method to set the CPU affinity of the current thread

```php
Swoole\Thread->setAffinity(array $cpu_set): bool
```

* **Parameters**
    * `array $cpu_set`
        * Function: List of CPU cores, for example `[0, 1, 3, 4]`
        * Default value: None.
        * Other values: None.

* **Return value**
    * Returns `true` on success
    * Returns `false` on failure, use `swoole_last_error()` to get error information


### setName()
Static method to set the name of the current thread. This provides a more friendly display when using tools like `ps` and `gdb` for viewing and debugging.

```php
Swoole\Thread->setName(string $name): bool
```

* **Parameters**
    * `string $name`
        * Function: Thread name
        * Default value: None.
        * Other values: None.

* **Return value**
    * Returns `true` on success
    * Returns `false` on failure, use `swoole_last_error()` to get error information

```shell
$ ps aux | grep -v grep | grep pool.php
swoole   2226813   0.1   0.1 423860 49024 pts/6    Sl+  17:38   0:00 php pool.php

$ ps -T -p 2226813
    PID    SPID TTY          TIME CMD
2226813 2226813 pts/6    00:00:00 Master Thread
2226813 2226814 pts/6    00:00:00 Worker Thread 0
2226813 2226815 pts/6    00:00:00 Worker Thread 1
2226813 2226816 pts/6    00:00:00 Worker Thread 2
2226813 2226817 pts/6    00:00:00 Worker Thread 3
```


### getNativeId()
Get the system `ID` of the thread, returning an integer similar to the process's `PID`.

```php
Swoole\Thread->getNativeId(): int
```

This function calls the `gettid()` system call on `Linux` systems to obtain a system thread `ID`, which is a short integer. It may be reclaimed by the operating system when the process thread is destroyed.

This `ID` can be used for debugging with `gdb` and `strace`, for example `gdb -p $tid`. Additionally, you can read `/proc/{PID}/task/{ThreadNativeId}` to get information about the thread's execution.


## Properties


### id

Use this object property to get the `ID` of the child thread, which is of type `int`.

> This property is only available in the parent thread. Child threads cannot obtain the `$thread` object and should use the `Thread::getId()` static method to get the thread's `ID`.

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```


## Constants

Name | Function
---|---
`Thread::HARDWARE_CONCURRENCY` | The number of hardware concurrency threads, typically equal to the number of CPU cores
`Thread::API_NAME` | The name of the thread API, such as `POSIX Threads`
`Thread::SCHED_OTHER` | Thread scheduling policy `SCHED_OTHER`
`Thread::SCHED_FIFO` | Thread scheduling policy `SCHED_FIFO`
`Thread::SCHED_RR` | Thread scheduling policy `SCHED_RR`
`Thread::SCHED_BATCH` | Thread scheduling policy `SCHED_BATCH`
`Thread::SCHED_ISO` | Thread scheduling policy `SCHED_ISO`
`Thread::SCHED_IDLE` | Thread scheduling policy `SCHED_IDLE`
`Thread::SCHED_DEADLINE` | Thread scheduling policy `SCHED_DEADLINE`
