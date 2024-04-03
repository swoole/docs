# Programming Tips

This section will provide a detailed introduction to the differences between coroutine programming and synchronous programming, as well as important points to note.
## Notes

* Do not execute `sleep` or other sleep functions in the code, as this will cause the entire process to be blocked; you can use [Co::sleep()](/coroutine/system?id=sleep) in coroutines or use `sleep` after [one-click coroutine transformation](/runtime); reference: [Impact of sleep/usleep](/getting_started/notice?id=sleepusleep的影响)
* `exit/die` is dangerous and will cause the `Worker` process to exit; reference: [Impact of exit/die functions](/getting_started/notice?id=exitdie函数的影响)
* You can use `register_shutdown_function` to capture fatal errors and perform some cleanup work when the process exits abnormally; reference: [Capturing fatal errors during Server runtime](/getting_started/notice?id=捕获server运行期致命错误)
* If exceptions are thrown in PHP code, you must catch exceptions in callback functions with `try/catch` to prevent the worker process from exiting; reference: [Capturing exceptions and errors](/getting_started/notice?id=捕获异常和错误)
* `set_exception_handler` is not supported; exceptions must be handled using `try/catch`.
* Workers processes should not share the same `Redis` or `MySQL` client for networking services; the code for creating connections to `Redis/MySQL` can be placed in the `onWorkerStart` callback function. Reference: [Can a single Redis or MySQL connection be shared](/question/use?id=是否可以共用1个redis或mysql连接)
## Coroutine Programming

Use the `Coroutine` feature, please read [coroutine programming notes](/coroutine/notice) carefully.
## Concurrent Programming

Please note that unlike the `synchronous blocking` mode, in the `coroutine` mode, the program is **concurrently executed**. At the same time, there will be multiple requests in `Server`, so **the application must create different resources and contexts for each client or request**; otherwise, there may be data and logic confusion between different clients and requests.
## Duplicate Class/Function Definition

Novices are very prone to making this mistake. Since `Swoole` resides in memory, the class/function definitions loaded from files will not be released. Therefore, when importing PHP files that contain class/function definitions, it is necessary to use `include_once` or `require_once` to avoid a fatal error of `cannot redeclare function/class`.
## Memory Management

!> Pay special attention when writing `Server` or other long-running processes.

The variable lifecycle and memory management of PHP daemon processes are completely different from regular web programs. The underlying memory management principles after the `Server` starts are the same as regular PHP-cli programs. For more details, please refer to articles on memory management in the `Zend VM`.
### Local Variables

After the event callback function returns, all local objects and variables will be released automatically without needing to `unset` them. If a variable is a resource type, the corresponding resource will also be released by PHP underlying.

```php
function test()
{
	$a = new Object;
	$b = fopen('/data/t.log', 'r+');
	$c = new swoole_client(SWOOLE_SYNC);
	$d = new swoole_client(SWOOLE_SYNC);
	global $e;
	$e['client'] = $d;
}
```

* `$a`, `$b`, `$c` are all local variables. When this function `return`s, these `3` variables will be immediately released, and the corresponding memory will be immediately freed. The open I/O resource file handle will also be closed immediately.
* `$d` is also a local variable, but it is saved to the global variable `$e` before `return`, so it will not be released. When `unset($e['client'])` is executed, and there are no other PHP variables still referencing the `$d` variable, then `$d` will be released.
### Global Variables

In `PHP`, there are `3` types of global variables.

* Variables declared using the `global` keyword.
* Class static variables and function static variables declared using the `static` keyword.
* `PHP` superglobal variables, including `$_GET`, `$_POST`, `$GLOBALS`, etc.

Global variables, objects, and class static variables stored on the `Server` object will not be released automatically. Programmers need to handle the destruction of these variables and objects themselves.

```php
class Test
{
	static $array = array();
	static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
	Test::$array[] = $fd;
	Test::$string .= $data;
}
```

* In event callback functions, special attention should be paid to non-local variable arrays. Certain operations like `TestClass::$array[] = "string"` may cause memory leaks and in severe cases, lead to memory overflow. It is essential to clean up large arrays when necessary.

* In event callback functions, concatenating non-local variable strings must be carefully done to avoid memory leaks, for example, `TestClass::$string .= $data`. There is a possibility of memory leakage, and in severe cases, memory overflow can occur.
### Solution

* For a synchronized, blocking, and stateless `Server` program, you can set [max_request](/server/setting?id=max_request) and [task_max_request](/server/setting?id=task_max_request). When the [Worker process](/learn?id=worker-process) / [Task process](/learn?id=taskworker-process) finishes running or reaches the task limit, the process will automatically exit, and all the variables/objects/resources of that process will be released and recycled.
* Within the program, use `unset` to clean up variables and recycle resources in `onClose` or by setting `timers` promptly.
## Process Isolation

Process isolation is a common issue encountered by many beginners. Why does changing the value of a global variable not take effect? The reason is that the memory space of global variables is isolated among different processes, so the change is not effective.

Therefore, when developing server programs using `Swoole`, you need to understand the issue of process isolation. The different `Worker` processes in a `Swoole\Server` program are isolated, so when working with global variables, timers, and event listeners in programming, they are only effective within the current process.

* PHP variables are not shared among different processes. Even if it is a global variable, modifying its value in process A will not affect process B.
* If you need to share data among different Worker processes, you can use tools such as `Redis`, `MySQL`, `files`, `Swoole\Table`, `APCu`, `shmget`, etc.
* File handles in different processes are isolated. Therefore, a socket connection created or a file opened in process A is not valid in process B. Even sending its file descriptor to process B will not make it usable.

Example:

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$i = 1;

$server->on('Request', function ($request, $response) {
    global $i;
    $response->end($i++);
});

$server->start();
```

In a multi-process server, although the `$i` variable is declared as a global variable (`global`), due to process isolation, if there are `4` Worker processes, performing `$i++` in `process 1`, only the `$i` in `process 1` will become `2`. The values of `$i` in the other `3` processes will still be `1`.

The correct approach is to use [Swoole\Atomic](/memory/atomic) or [Swoole\Table](/memory/table) data structures provided by Swoole to store data. In the above code, `Swoole\Atomic` can be used for this purpose.

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$atomic = new Swoole\Atomic(1);

$server->on('Request', function ($request, $response) use ($atomic) {
    $response->end($atomic->add(1));
});

$server->start();
```

!> The data managed by `Swoole\Atomic` is built on shared memory. When using the `add` method to increment by `1`, the change is also effective in other Worker processes.

The [Table](/memory/table), [Atomic](/memory/atomic), and [Lock](/memory/lock) components provided by Swoole can be used for multi-process programming, but they must be created before calling `Server->start`. Moreover, the TCP client connections maintained by the `Server` can also be operated across processes, such as with `Server->send` and `Server->close`.
## stat Cache Clearing

PHP's underlying `stat` system call has a `Cache`. When using functions like `stat`, `fstat`, `filemtime`, etc., the underlying system may hit the cache and return historical data.

You can use the [clearstatcache](https://www.php.net/manual/en/function.clearstatcache.php) function to clear the file `stat` cache.
## mt_rand random number

In `Swoole`, if `mt_rand` is called in the parent process, calling `mt_rand` in different child processes will return the same result, so it is necessary to call `mt_srand` to reseed in each child process.

!> Functions in `PHP` like `shuffle` and `array_rand` that rely on random numbers will be affected as well.

Example:

```php
mt_rand(0, 1);

//Start
$worker_num = 16;

//fork processes
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

//asynchronously execute processes
function child_async(Swoole\Process $worker) {
    mt_srand(); //reseed
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
```
## Catching Exceptions and Errors
### Catchable Exceptions/Errors

There are roughly three types of catchable exceptions/errors in `PHP`:

1. `Error`: a special type of error thrown by the `PHP` core, such as class not found, function not found, or function parameter errors. `Error` should not be used in `PHP` code as an exception.
2. `Exception`: the base class of exceptions that application developers should use.
3. `ErrorException`: this exception class is specifically responsible for converting `PHP` `Warning`/`Notice` messages into exceptions through the `set_error_handler` function. In the future, `PHP` is likely to convert all `Warning`/`Notice` messages into exceptions for better error handling and control in `PHP` programs.

!> All of the above classes implement the `Throwable` interface. This means that by using `try {} catch(Throwable $e) {}`, you can catch all throwable exceptions and errors.

Example 1:
```php
try {
	test();
} 
catch(Throwable $e) {
	var_dump($e);
}
```

Example 2:
```php
try {
	test();
}
catch (Error $e) {
	var_dump($e);
}
catch(Exception $e) {
	var_dump($e);
}
```
### Uncatchable Fatal Errors and Exceptions

One important level of `PHP` errors, such as when an exception/error is uncaught, when memory runs out, or some compile-time errors (like inheriting from a non-existent class), will throw a `Fatal Error` at level `E_ERROR`. This occurs when an irreversible error happens in the program, and `PHP` is not able to catch such an error at this level. Instead, it can only handle some operations later using `register_shutdown_function`.
### Capturing Runtime Exceptions/Errors in Coroutine

In `Swoole4` coroutine programming, throwing an error in the code of a coroutine will cause the entire process to exit, terminating the execution of all coroutines in the process. At the top level space of a coroutine, you can use a `try/catch` block to catch exceptions/errors, and only terminate the coroutine where the error occurred.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    // Error in Coroutine 1 does not affect Coroutine 2
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
```
### Capture Server Runtime Fatal Errors

Once a fatal error occurs during the runtime of `Server`, client connections will not receive a response. For example, for a web server, if there is a fatal error, it should send an `HTTP 500` error message to the client.

In PHP, you can capture fatal errors using the combination of the `register_shutdown_function` and `error_get_last` functions, and send the error information to the client connection.

Below is the specific example code:

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    register_shutdown_function(function () use ($response) {
        $error = error_get_last();
        var_dump($error);
        switch ($error['type'] ?? null) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                // log or send:
                // error_log($message);
                // $server->send($fd, $error['message']);
                $response->status(500);
                $response->end($error['message']);
                break;
        }
    });
    exit(0);
});
$http->start();
```
## Use Cases
### Effects of sleep/usleep

In asynchronous IO programs, **do not use sleep/usleep/time_sleep_until/time_nanosleep**. (Hereafter, the term `sleep` is used to refer to all sleep functions)

- The `sleep` function will cause the process to enter a sleeping block.
- The operating system will only wake up the current process after the specified time.
- During `sleep`, only signals can interrupt.
- Since Swoole's signal handling is based on `signalfd`, even sending a signal cannot interrupt `sleep`.

The [Swoole\Event::add](/event?id=add), [Swoole\Timer::tick](/timer?id=tick), [Swoole\Timer::after](/timer?id=after), and [Swoole\Process::signal](/process/process?id=signal) provided by Swoole will stop working after the process `sleeps`. [Swoole\Server](/server/tcp_init) will also not be able to handle new requests.
#### Example

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    sleep(100);
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> In the [onReceive](/server/events?id=onreceive) event, the `sleep` function is executed, causing the `Server` to not receive any client requests for 100 seconds.
### Impact of the exit/die function

In `Swoole` programs, using `exit/die` is prohibited. If there are `exit/die` statements in PHP code, the current working [Worker process](/learn?id=worker-process), [Task process](/learn?id=task-worker-process), [User process](/server/methods?id=addprocess), and `Swoole\Process` process will immediately exit.

Using `exit/die` causes the `Worker` process to exit due to an exception, then the `master` process will restart it, leading to a continuous cycle of processes exiting and restarting, generating a large number of alert logs.

It is recommended to use the `try/catch` method instead of `exit/die` to achieve interrupting execution and exiting the PHP function call stack.

```php
Swoole\Coroutine\run(function () {
    try
    {
        exit(0);
    } catch (Swoole\ExitException $e)
    {
        echo $e->getMessage()."\n";
    }
});
```

!> `Swoole\ExitException` is directly supported in Swoole `v4.1.0` and above for using PHP `exit` in coroutines and `Server`. In this case, a catchable `Swoole\ExitException` will be automatically thrown by the underlying layer, allowing developers to catch and implement exit logic similar to native PHP. Refer to [Exiting Coroutines](/coroutine/notice?id=exiting-coroutines) for specific usage.

Handling exceptions is friendlier than using `exit/die` because exceptions are controllable while `exit/die` is not. By using `try/catch` on the outermost layer, you can catch exceptions, only terminating the current task. Worker processes can continue handling new requests, while `exit/die` will cause the process to exit directly, destroying all variables and resources saved in that process. If there are other tasks to be processed within the process, encountering `exit/die` will discard them all.
### The Impact of While Loop

If an asynchronous program encounters an infinite loop, the events will not be triggered. Asynchronous IO programs use the `Reactor model`, where polling must occur at `reactor->wait` during runtime. If an infinite loop is encountered, the program control will be stuck in the `while` loop, and the `reactor` won't be able to obtain control, leading to the inability to detect events. Consequently, the IO event callback functions will not be triggered.

!> Code with intensive computation but without any IO operations cannot be considered blocking.
#### Example Program

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactorId, $data) {
    $i = 0;
    while(1)
    {
        $i++;
    }
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> A deadlock is caused by an infinite loop in the [onReceive](/server/events?id=onreceive) event, preventing `server` from receiving any new client requests. The server must wait for the loop to finish in order to continue processing new events.
