# Swoole\Process

Swoole's process management module, used to replace PHP's `pcntl`.  

!> This module is relatively low-level and encapsulates operating system process management. Users need to have experience in multi-process programming on `Linux` system.

The built-in `pcntl` in `PHP` has many shortcomings, such as:

* Does not provide inter-process communication functionality
* Does not support redirecting standard input and output
* Only provides raw interfaces like `fork`, which are prone to errors

`Process` provides more powerful functionality than `pcntl`, with an easier-to-use `API`, making PHP more effortless in multi-process programming.

`Process` offers the following features:

* Facilitates inter-process communication
* Supports redirecting standard input and output, where echoing in the child process does not print to the screen but writes to the pipe. Reading keyboard input can be redirected to read data from the pipe.
* Provides the [exec](/process/process?id=exec) interface, allowing created processes to execute other programs, facilitating communication between the original `PHP` parent process and the new process.
* In a coroutine environment, `Process` module cannot be used directly. Instead, you can implement it using `runtime hook`+`proc_open`, as outlined in [Coroutine Process Management](/coroutine/proc_open).
### Usage Example

  * Create 3 child processes, with the main process using wait to reclaim them.
  * If the main process exits abnormally, the child processes will continue to execute and exit after completing all tasks.

```php
use Swoole\Process;

for ($n = 1; $n <= 3; $n++) {
    $process = new Process(function () use ($n) {
        echo 'Child #' . getmypid() . " start and sleep {$n}s" . PHP_EOL;
        sleep($n);
        echo 'Child #' . getmypid() . ' exit' . PHP_EOL;
    });
    $process->start();
}
for ($n = 3; $n--;) {
    $status = Process::wait(true);
    echo "Recycled #{$status['pid']}, code={$status['code']}, signal={$status['signal']}" . PHP_EOL;
}
echo 'Parent #' . getmypid() . ' exit' . PHP_EOL;
```
## Attributes
### pipe

File descriptor of [unixSocket](/learn?id=什么是IPC).

```php
public int $pipe;
```
### msgQueueId

The `id` of the message queue.

```php
public int $msgQueueId;
```
### msgQueueKey

The `key` of the message queue.

```php
public string $msgQueueKey;
```
### pid

The `pid` of the current process.

```php
public int $pid;
```
### id

The current process `id`.

```php
public int $id;
```
## Constants
Parameter | Purpose
---|---
Swoole\Process::IPC_NOWAIT | Immediately return when there is no data in the message queue
Swoole\Process::PIPE_READ | Close the read socket
Swoole\Process::PIPE_WRITE | Close the write socket
## Methods
### __construct()

Constructor method.

```php
Swoole\Process->__construct(callable $function, bool $redirect_stdin_stdout = false, int $pipe_type = SOCK_DGRAM, bool $enable_coroutine = false)
```

* **Parameters**

  * **`callable $function`**
    * **Description**: The function to execute after the child process is created. (The function will be automatically saved to the object's `callback` property.) Note that this property is private to the class.
    * **Default Value**: None
    * **Other Values**: None

  * **`bool $redirect_stdin_stdout`**
    * **Description**: Redirect the standard input and output of the child process. (When this option is enabled, output within the child process will not be printed to the screen but will be written to the main process pipe. Reading keyboard input will involve reading data from the pipe. Default is blocking read. Refer to the [exec()](/process/process?id=exec) method for more details.)
    * **Default Value**: None
    * **Other Values**: None

  * **`int $pipe_type`**
    * **Description**: Type of [unixSocket](/learn?id=What_is_IPC). (If `$redirect_stdin_stdout` is enabled, this option will ignore user parameters and force `SOCK_STREAM`. If there is no inter-process communication within the child process, it can be set to `0`.)
    * **Default Value**: `SOCK_DGRAM`
    * **Other Values**: `0`, `SOCK_STREAM`

  * **`bool $enable_coroutine`**
    * **Description**: Enable coroutine in the callback function. (When enabled, coroutine API can be directly used in the child process function.)
    * **Default Value**: `false`
    * **Other Values**: `true`
    * **Version Impact**: Swoole version >= v4.3.0

* **[unixSocket](/learn?id=What_is_IPC) Types**

UnixSocket Type | Description
---|---
0 | Do not create
1 | Create a unixSocket of type [SOCK_STREAM](/learn?id=What_is_IPC)
2 | Create a unixSocket of type [SOCK_DGRAM](/learn?id=What_is_IPC)
### useQueue()

Using message queues for inter-process communication.

```php
Swoole\Process->useQueue(int $key = 0, int $mode = SWOOLE_MSGQUEUE_BALANCE, int $capacity = -1): bool
```

* **Parameters** 

  * **`int $key`**
    * **Description**: Key of the message queue. If a value less than or equal to 0 is passed, the underlying layer will generate the corresponding key using the `ftok` function with the file name of the current executing file as the parameter.
    * **Default value**: `0`
    * **Other values**: `N/A`

  * **`int $mode`**
    * **Description**: Inter-process communication mode.
    * **Default value**: `SWOOLE_MSGQUEUE_BALANCE`, `Swoole\Process::pop()` will return the first message in the queue, `Swoole\Process::push()` will not add a specific type to the message.
    * **Other values**: `SWOOLE_MSGQUEUE_ORIENT`, `Swoole\Process::pop()` will return specific data in the queue with the message type as `process id + 1`, `Swoole\Process::push()` will add the type `process id + 1` to the message.

  * **`int $capacity`**
    * **Description**: Maximum number of messages allowed to be stored in the message queue.
    * **Default value**: `-1`
    * **Other values**: `N/A`

* **Note**

  * When the message queue has no data, `Swoole\Process->pop()` will block continuously, or if the message queue does not have space to accommodate new data, `Swoole\Process->push()` will also block continuously. If you do not want to block, the value of `$mode` must be `SWOOLE_MSGQUEUE_BALANCE|Swoole\Process::IPC_NOWAIT` or `SWOOLE_MSGQUEUE_ORIENT|Swoole\Process::IPC_NOWAIT`.
### statQueue()

Get the status of the message queue

```php
Swoole\Process->statQueue(): array|false
```

* **Return Value** 
  
  * Returns an array if successful. The array contains two key-value pairs: `queue_num` indicates the total number of messages currently in the queue, and `queue_bytes` indicates the total size of messages in the queue. 
  * Returns `false` on failure.
### freeQueue()

Destroy the message queue.

```php
Swoole\Process->freeQueue(): bool
```

* **Return Value**

  * Returns `true` on success.
  * Returns `false` on failure.
### pop()

Get data from the message queue.

```php
Swoole\Process->pop(int $size = 65536): string|false
```

* **Parameters** 

  * **`int $size`**
    * **Description**: Size of data to get.
    * **Default Value**: `65536`
    * **Other Values**: `N/A`

* **Return Value** 

  * Returns `string` on success.
  * Returns `false` on failure.

* **Note**

  * When the message queue type is `SW_MSGQUEUE_BALANCE`, the first message in the queue is returned.
  * When the message queue type is `SW_MSGQUEUE_ORIENT`, the first message of the type `current process id + 1` is returned.
### push()

Send data to the message queue.

```php
Swoole\Process->push(string $data): bool
```

* **Parameters** 

  * **`string $data`**
    * **Description**: Data to be sent.
    * **Default**: ``
    * **Other values**: `None`

* **Return Value**

  * Returns `true` on success.
  * Returns `false` on failure.

* **Notes**

  * When the message queue type is `SW_MSGQUEUE_BALANCE`, the data will be inserted directly into the message queue.
  * When the message queue type is `SW_MSGQUEUE_ORIENT`, the data will be added with a type, which is the current `process id + 1`.
### setTimeout()

Set message queue read/write timeout.

```php
Swoole\Process->setTimeout(float $seconds): bool
```

* **Parameters**

  * **`float $seconds`**
    * **Function**: timeout duration
    * **Default value**: `None`
    * **Other values**: `None`

* **Return value**

  * Returns `true` on success.
  * Returns `false` on failure.
### setBlocking()

Set whether the message queue socket is blocking.

```php
Swoole\Process->setBlocking(bool $blocking): void
```

- **Parameters**
  - **`bool $blocking`**
    - **Function**: Whether to block, `true` for blocking, `false` for non-blocking
    - **Default Value**: N/A
    - **Other Values**: N/A

- **Note**
  - Newly created process sockets are blocking by default, so when doing UNIX domain socket communication, sending or reading messages will cause the process to block.
### write()

Write messages between parent and child processes (UNIX domain socket).

```php
Swoole\Process->write(string $data): false|int
```

* **Parameters**

  * **`string $data`**
    * **Description**: Data to be written
    * **Default value**: `None`
    * **Other values**: `None`

* **Return Value**

  * Return `int` indicating the number of bytes successfully written if successful.
  * Return `false` if failed.
### read()

Read message between parent and child processes (UNIX domain socket).

```php
Swoole\Process->read(int $size = 8192): false|string
```

* **Parameters** 

  * **`int $size`**
    * **Description**：Size of the data to read
    * **Default**：`8192`
    * **Other values**：`N/A`


* **Return Value** 

  * Returns `string` on success.
  * Returns `false` on failure.
### set()

Setting parameters.

```php
Swoole\Process->set(array $settings): void
```

You can use `enable_coroutine` to control whether to enable coroutine, which has the same effect as the fourth parameter of the constructor.

```php
Swoole\Process->set(['enable_coroutine' => true]);
```

!> Available in Swoole version >= v4.4.4
### start()

Call the `fork` system call to start a child process. Creating a process on the `Linux` system takes several hundred microseconds.

```php
Swoole\Process->start(): int|false
```

* **Return Value**

  * Returns the `PID` of the child process on success
  * Returns `false` on failure. You can use [swoole_errno](/functions?id=swoole_errno) and [swoole_strerror](/functions?id=swoole_strerror) to get the error code and message.

* **Note**

  * The child process inherits the memory and file handles from the parent process.
  * When the child process starts, it clears the inherited [EventLoop](/learn?id=什么是eventloop), [Signal](/process/process?id=signal), and [Timer](/timer) from the parent process.
  
  !> After execution, the child process will maintain the memory and resources of the parent process. For example, if a Redis connection is created in the parent process, the child process will retain this object, and all operations will be performed on the same connection. The example below illustrates this:

```php
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);

function callback_function() {
    swoole_timer_after(1000, function () {
        echo "hello world\n";
    });
    global $redis; // uses the same connection
};

swoole_timer_tick(1000, function () {
    echo "parent timer\n";
}); // not inherited

Swoole\Process::signal(SIGCHLD, function ($sig) {
    while ($ret = Swoole\Process::wait(false)) {
        // create a new child process
        $p = new Swoole\Process('callback_function');
        $p->start();
    }
});

// create a new child process
$p = new Swoole\Process('callback_function');

$p->start();
```

!> 1. The child process will automatically clear the timers created by [Swoole\Timer::tick](/timer?id=tick), signal listeners set by [Process::signal](/process/process?id=signal), and event listeners added by [Swoole\Event::add](/event?id=add) from the parent process.  
2. The child process will inherit the `$redis` connection object created by the parent process, and both the parent and child processes will use the same connection.
### exportSocket()

Export `unixSocket` as `Swoole\Coroutine\Socket` object, and then use the methods of the `Swoole\Coroutine\Socket` object for inter-process communication. For specific usage, please refer to [Coroutine\socket](/coroutine_client/socket) and [IPC communication](/learn?id=what-is-ipc).

```php
Swoole\Process->exportSocket(): Swoole\Coroutine\Socket|false
```

!> Calling this method multiple times will return the same object;  
The `socket` exported by `exportSocket()` is a new `fd`, closing the exported `socket` will not affect the original process pipe.  
Since it is a `Swoole\Coroutine\Socket` object, it must be used in a [coroutine container](/coroutine/scheduler), so the `$enable_coroutine` parameter of Swoole\Process constructor must be true.  
Similarly, if the parent process wants to use the `Swoole\Coroutine\Socket` object, it needs to manually call `Coroutine\run()` to create a coroutine container.

* **Return Value**

  * Returns `Coroutine\Socket` object on success
  * Returns `false` if the process has not created unixSocket, operation fails

* **Usage Example**

Implement a simple parent-child process communication:

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    echo $socket->recv();
    $socket->send("hello master\n");
    echo "proc1 stop\n";
}, false, 1, true);

$proc1->start();

// Parent process creates a coroutine container
run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    $socket->send("hello pro1\n");
    var_dump($socket->recv());
});
Process::wait(true);
```

A more complex communication example:

```php
use Swoole\Process;
use Swoole\Timer;
use function Swoole\Coroutine\run;

$process = new Process(function ($proc) {
    Timer::tick(1000, function () use ($proc) {
        $socket = $proc->exportSocket();
        $socket->send("hello master\n");
        echo "child timer\n";
    });
}, false, 1, true);

$process->start();

run(function() use ($process) {
    Process::signal(SIGCHLD, static function ($sig) {
        while ($ret = Swoole\Process::wait(false)) {
            /* clean up then event loop will exit */
            Process::signal(SIGCHLD, null);
            Timer::clearAll();
        }
    });
    /* your can run your other async or coroutine code here */
    Timer::tick(500, function () {
        echo "parent timer\n";
    });

    $socket = $process->exportSocket();
    while (1) {
        var_dump($socket->recv());
    }
});
```
!> Note that the default type is `SOCK_STREAM`, and you need to handle TCP data packet boundary issues, refer to the `setProtocol()` method in [Coroutine\socket](/coroutine_client/socket).

Using `SOCK_DGRAM` type for IPC communication can avoid dealing with TCP data packet boundary issues, refer to [IPC communication](/learn?id=what-is-ipc):

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

//Even if it is a SOCK_DGRAM type socket for IPC communication, you do not need to use sendto/recvfrom functions, send/recv will suffice.
$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    while (1) {
        var_dump($socket->send("hello master\n"));
    }
    echo "proc1 stop\n";
}, false, 2, 1);//passing 2 as the pipe type parameter for SOCK_DGRAM

$proc1->start();

run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    Swoole\Coroutine::sleep(5);
    var_dump(strlen($socket->recv()));//only receives one "hello master\n" string in one recv, will not receive multiple "hello master\n" strings
});

Process::wait(true);
```
### name()

Change the process name. This function is an alias of [swoole_set_process_name](/functions?id=swoole_set_process_name).

```php
Swoole\Process->name(string $name): bool
```

!> After executing `exec`, the process name will be reset by the new program. The `name` method should be used in the child process callback function after `start`.
### exec()

Execute an external program. This function is a wrapper for the `exec` system call.

```php
Swoole\Process->exec(string $execfile, array $args);
```

* **Parameters**

  * **`string $execfile`**
    * **Function**: Specifies the absolute path of the executable file, such as `"/usr/bin/python"`.
    * **Default**: None
    * **Other values**: None

  * **`array $args`**
    * **Function**: List of arguments for `exec` 【e.g., `array('test.py', 123)`, equivalent to `python test.py 123`】
    * **Default**: None
    * **Other values**: None

After successful execution, the code segment of the current process will be replaced by the new program. The child process transforms into another program. The parent process and the current process still maintain a parent-child relationship.

Communication between the parent process and the new process can be done through standard input and output, and standard input/output redirection must be enabled.

!> The `$execfile` must use an absolute path, otherwise a file not found error will occur.  
Since the `exec` system call will overwrite the current program with the specified program, the child process needs to read and write standard output to communicate with the parent process.  
If `redirect_stdin_stdout = true` is not specified, communication between the child process and the parent process will not be possible after `exec` is executed.

* **Usage Examples**

Example 1: You can use [Swoole\Server](/server/init) in a child process created by `Swoole\Process`, but for security reasons, you must call `$worker->exec()` after creating the process with `$process->start`. The code is as follows:

```php
$process = new Swoole\Process('callback_function', true);

$pid = $process->start();

function callback_function(Swoole\Process $worker)
{
    $worker->exec('/usr/local/bin/php', array(__DIR__.'/swoole_server.php'));
}

Swoole\Process::wait();
```

Example 2: Starting a Yii program

```php
$process = new \Swoole\Process(function (\Swoole\Process $childProcess) {
    // This format is not supported
    // $childProcess->exec('/usr/local/bin/php /var/www/project/yii-best-practice/cli/yii t/index -m=123 abc xyz');

    // Encapsulating the exec system call
    // Absolute path
    // Parameters must be separately put into an array
    $childProcess->exec('/usr/local/bin/php', ['/var/www/project/yii-best-practice/cli/yii', 't/index', '-m=123', 'abc', 'xyz']); // exec system call
});
$process->start(); // Start the child process
```

Example 3: Communication between parent process and `exec` child process using standard input/output:

```php
// exec - Communication with exec process using pipes
use Swoole\Process;
use function Swoole\Coroutine\run;

$process = new Process(function (Process $worker) {
    $worker->exec('/bin/echo', ['hello']);
}, true, 1, true); // Standard input/output redirection is required

$process->start();

run(function() use($process) {
    $socket = $process->exportSocket();
    echo "from exec: " . $socket->recv() . "\n";
});
```

Example 4: Executing a shell command

The `exec` method is different from `shell_exec` provided by PHP; it is a lower-level system call wrapper. If you need to execute a shell command, please use the following method:

```php
$worker->exec('/bin/sh', array('-c', "cp -rf /data/test/* /tmp/test/"));
```
### close()

Used to close the created [unixSocket](/learn?id=什么是IPC).

```php
Swoole\Process->close(int $which): bool
```

* **Parameters**

  * **`int $which`**
    * **Function**: Since unixSocket is full-duplex, specify which end to close [default is `0` for closing both read and write, `1`: close write, `2` close read].
    * **Default value**: `0`, close both reading and writing sockets.
    * **Other values**: `Swoole/Process::SW_PIPE_CLOSE_READ` close the read socket, `Swoole/Process::SW_PIPE_CLOSE_WRITE` close the write socket.

!> There are some special cases where `Process` objects cannot be released, and if processes are continuously created, it may cause connection leaks. Calling this function can directly close the `unixSocket` and release resources.
### exit()

Exit the child process.

```php
Swoole\Process->exit(int $status = 0);
```

* **Parameters**

  * **`int $status`**
    * **Functionality**: Status code for exiting the process【If it is `0`, it means normal termination and will continue to execute cleanup work】
    * **Default value**: `0`
    * **Other values**: None

!> Cleanup work includes:

  * PHP's `shutdown_function`
  * Object destruct (`__destruct`)
  * Other extensions' `RSHUTDOWN` functions

If `$status` is not `0`, it indicates abnormal termination; the process will be terminated immediately without executing the related process termination cleanup work.

In the parent process, executing `Process::wait` allows obtaining the child process's exit event and status code.
### kill()

Send signal to the specified `pid` process.

```php
Swoole\Process::kill(int $pid, int $signo = SIGTERM): bool
```

* **Parameters**

  * **`int $pid`**
    * **Function**: Process `pid`
    * **Default Value**: None
    * **Other Values**: None

  * **`int $signo`**
    * **Function**: Signal sent【`$signo=0`, can check if the process exists without sending a signal】
    * **Default Value**: `SIGTERM`
    * **Other Values**: None
### signal()

Set up asynchronous signal monitoring.

```php
Swoole\Process::signal(int $signo, callable $callback): bool
```

This method is based on `signalfd` and [EventLoop](/learn?id=what-is-eventloop) for asynchronous `IO`, and cannot be used in blocking programs, as it will result in the registered callback functions not being scheduled properly;

For synchronous blocking programs, you can use `pcntl_signal` provided by the `pcntl` extension;

When setting a callback function for a signal that is already set, re-setting it will override the previous setting.

* **Parameters** 

  * **`int $signo`**
    * **Description**: Signal
    * **Default**: None
    * **Other values**: None

  * **`callable $callback`**
    * **Description**: Callback function [`$callback` is `null`, signal monitoring will be removed]
    * **Default**: None
    * **Other values**: None

!> Certain signals, like `SIGTERM` and `SIGALRM`, cannot be set as monitored signals in [Swoole\Server](/server/init).

* **Usage Example**

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
```

!> In version `v4.4.0`, if an Swoole process's [EventLoop](/learn?id=what-is-eventloop) only has signal monitoring events and no other events (e.g., Timer timers, etc.), the process will exit directly.

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
Swoole\Event::wait();
```

In the above example, the program will not enter the [EventLoop](/learn?id=what-is-eventloop), `Swoole\Event::wait()` will return immediately and exit the process.
### wait()

Reap the child process that has finished running.

!> It is recommended to use the coroutine version of `wait()` when the Swoole version is `v4.5.0` or later, refer to [Swoole\Coroutine\System::wait()](/coroutine/system?id=wait)

```php
Swoole\Process::wait(bool $blocking = true): array|false
```

* **Parameters** 

  * **`bool $blocking`**
    * **Function**: Specify whether to block waiting (default is blocking)
    * **Default Value**: `true`
    * **Other Values**: `false`

* **Return Value**

  * If successful, it will return an array containing the child process's `PID`, exit status code, and the signal `KILL` that caused the exit.
  * Returns `false` on failure.

!> After each child process finishes, the parent process must call `wait()` to reap it; otherwise, the child process will become a zombie process, wasting the operating system's process resources.  
If the parent process has other tasks to do and cannot block waiting, it must register the `SIGCHLD` signal to call `wait` on the exiting process.  
When the SIGCHLD signal occurs, multiple child processes may have exited at the same time; `wait()` must be set to non-blocking, loop `wait` execution until it returns `false`.

* **Example**

```php
Swoole\Process::signal(SIGCHLD, function ($sig) {
    // Must be set to false for non-blocking mode
    while ($ret = Swoole\Process::wait(false)) {
        echo "PID={$ret['pid']}\n";
    }
});
```
### daemon()

Transforms the current process into a daemon.

```php
Swoole\Process::daemon(bool $nochdir = true, bool $noclose = true): bool
```

* **Parameters**

  * **`bool $nochdir`**
    * **Function**: Whether to switch the current directory to the root directory [if `true`, do not switch the current directory to the root directory]
    * **Default Value**: `true`
    * **Other Values**: `false`

  * **`bool $noclose`**
    * **Function**: Whether to close standard input and output file descriptors [if `true`, do not close standard input and output file descriptors]
    * **Default Value**: `true`
    * **Other Values**: `false`

!> When transforming into a daemon process, the `PID` of the process will change. You can use `getmypid()` to get the current `PID`.
### alarm()

High-precision timer, which is an encapsulation of the operating system `setitimer` system call, can set timers at the microsecond level. The timer will trigger a signal and needs to be used in conjunction with [Process::signal](/process/process?id=signal) or `pcntl_signal`.

!> `alarm` cannot be used simultaneously with [Timer](/timer).

```php
Swoole\Process->alarm(int $time, int $type = 0): bool
```

* **Parameters**

  * **`int $time`**
    * **Function**: Timer interval time [if negative, it means to clear the timer]
    * **Unit**: Microseconds
    * **Default Value**: None
    * **Other Values**: None

  * **`int $type`**
    * **Function**: Timer type
    * **Default Value**: `0`
    * **Other Values**:

Timer Type | Description
---|---
0 | Represents real time, triggers `SIGALAM` signal
1 | Represents user mode CPU time, triggers `SIGVTALAM` signal
2 | Represents user mode + kernel mode time, triggers `SIGPROF` signal

* **Return Value**

  * Returns `true` if set successfully
  * Returns `false` if failed, you can use `swoole_errno` to get the error code

* **Usage Example**

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

run(function () {
    Process::signal(SIGALRM, function () {
        static $i = 0;
        echo "#{$i}\talarm\n";
        $i++;
        if ($i > 20) {
            Process::alarm(-1);
            Process::kill(getmypid());
        }
    });

    //100ms
    Process::alarm(100 * 1000);

    while(true) {
        sleep(0.5);
    }
});
```
### setAffinity()

Set the CPU affinity to bind the process to a specific CPU core. 

The function of this method is to only run the process on certain CPU cores, freeing up certain CPU resources to run more critical programs.

```php
Swoole\Process->setAffinity(array $cpus): bool
```

* **Parameters** 

  * **`array $cpus`**
    * **Description**：Bind CPU cores 【e.g., `array(0,2,3)` indicates binding to CPU0/CPU2/CPU3】
    * **Default value**：None
    * **Other values**：None

!> - The elements in `$cpus` cannot exceed the number of CPU cores;  
- The `CPU-ID` must not exceed (`Number of CPU cores - 1`);  
- This function requires operating system support for setting CPU affinity;  
- You can use [swoole_cpu_num()](/functions?id=swoole_cpu_num) to get the current server's number of CPU cores.  
### setPriority()

Set the priority for process, process group, and user process.

!> Available in Swoole version >= `v4.5.9`

```php
Swoole\Process->setPriority(int $which, int $priority): bool
```

* **Parameters** 

  * **`int $which`**
    * **Description**: Determines the type of priority to be modified
    * **Default**: None
    * **Other values**:

| Constant      | Description|
| ------------- | ---------- |
| PRIO_PROCESS  | Process    |
| PRIO_PGRP     | Process group|
| PRIO_USER     | User process|

  * **`int $priority`**
    * **Description**: Priority. The lower the value, the higher the priority
    * **Default**: None
    * **Other values**: `[-20, 20]`

* **Return Value**

  * If `false` is returned, you can use [swoole_errno](/functions?id=swoole_errno) and [swoole_strerror](/functions?id=swoole_strerror) to get the error code and error message.
### getPriority()

Get the priority of a process.

!> Available since Swoole version `v4.5.9`

```php
Swoole\Process->getPriority(int $which): int
```
