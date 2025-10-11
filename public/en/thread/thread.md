# Swoole\Thread

Starting from version `6.0`, multiple thread support has been provided, which can be used to replace multiple processes using thread API. Compared to multiple processes, `Thread` provides a richer concurrent data container, making it more convenient for developing game servers and communication servers.

## Compilation
- `PHP` must be in `ZTS` mode, and when compiling `PHP`, `--enable-zts` needs to be added
- When compiling `Swoole`, the `--enable-swoole-thread` compilation option needs to be added

## Viewing Information

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)` indicates that thread safety is enabled

```shell
php --ri swoole
php --ri swoole

swoole

Swoole => enabled
...
thread => enabled
...
```

`thread => enabled` indicates that multiple thread support is enabled

## Creating Threads

```php
use Swoole\Thread;

$thread = new Thread(__DIR__ . '/task.php',array ...$argv);
```
Arguments passed to the thread can be retrieved inside the script using `Thread::getArguments()`.


Please note that the created child thread will not inherit any resources from the parent thread. Therefore, in the child thread, the following contents have been cleared and need to be recreated or set:
- PHP files that have been loaded need to be reloaded with `include/require`
- `autoload`
- Classes, functions, constants will be cleared and need to be reloaded from the PHP file
- Global variables, such as `$GLOBALS`, `$_GET/$_POST`, etc., will be cleared
- Static properties of classes, static variables of functions will be reset to initial values
- `php.ini` options, e.g., `error_reporting()`, need to be reset in the child thread

Data must be passed to the child thread using thread parameters. New threads can still be created in the child thread.

### Parameters
- `$script_file`: The script to execute after the thread starts
- `...$argv`: Passing thread parameters, must be serializable variables, unable to pass `resource` resource handles, `Thread::getArguments()` can be used in the child thread to retrieve

### Return Value
Returns a `Thread` object, in the parent thread, operations like `join()` can be performed on the child thread.

When the thread object is destructed, `join()` will be automatically executed to wait for the child thread to exit. This may cause blocking, and the `$thread->detach()` method can be used to detach the child thread from the parent thread and run independently.

### Example
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

$threads = [];
if (empty($args)) {
    # Parent thread
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__ , $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # Child thread
    echo "Thread #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```

## Constants
- `Thread::HARDWARE_CONCURRENCY` retrieves the number of concurrent threads supported by the hardware system, i.e. the number of CPU cores.

## Methods

### isAlive()
Check if this thread is still running or not.
```php
if ($thread->isAlive()) {
    echo "Thread is still running.\n";
}
```

### join()
Blocks the main thread until the thread finishes execution.
```php
$thread->join();
echo "Thread finished and joined to current thread.\n";
```

### joinable()
Returns true if the thread is joinable (not joined or detached).

### detach()
Separate this thread from the main thread (the calling thread), allowing its execution to continue independently.
This thread will run in the background, and the main thread (the calling thread) will not wait for it to finish.

### getExitStatus()
Retrieve the exit status of the thread after it finishes.


* **Scheduling and CPU Control**
Swoole threads support various scheduling policies. You can combine `setAffinity` and `setPriority` to optimize thread performance on multicore systems.

| Constant           | Description                  |
|------------------|------------------------------|
| `SCHED_OTHER`     | Default policy               |
| `SCHED_FIFO`      | First-in-first-out           |
| `SCHED_RR`        | Round-robin                  |
| `SCHED_BATCH`     | Batch scheduling             |
| `SCHED_ISO`       | ISO scheduling               |
| `SCHED_IDLE`      | Run when CPU is idle         |
| `SCHED_DEADLINE`  | Deadline scheduling          |

* 
