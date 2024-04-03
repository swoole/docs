# Swoole\Process\Pool

Process pool, based on the Manager-managed process module of [Swoole\Server](/server/init). It can manage multiple worker processes. The core function of this module is process management. Compared to the `Process` class for implementing multiple processes, `Process\Pool` is simpler and has a higher level of encapsulation. Developers do not need to write too much code to achieve process management. By using [Co\Server](/coroutine/server?id=complete-example), a pure coroutine-style server program that can utilize multi-core CPUs can be created.
## Inter-process Communication

`Swoole\Process\Pool` provides a total of three ways for inter-process communication:
### Message Queue
When the second parameter of `Swoole\Process\Pool->__construct` is set to `SWOOLE_IPC_MSGQUEUE`, it means that message queues are used for inter-process communication. The information can be delivered through the `php sysvmsg` extension, and the maximum message size should not exceed `65536`.

* **Note**

  * If you want to deliver information using the `sysvmsg` extension, the `msgqueue_key` must be passed in the constructor.
  * The Swoole core does not support the second parameter `mtype` of the `sysvmsg` extension `msg_send`. Please pass in any non-zero value.
### Socket Communication
When the second parameter of `Swoole\Process\Pool->__construct` is set to `SWOOLE_IPC_SOCKET`, it means using `Socket Communication`. If your client and server are not on the same machine, you can use this method for communication.

You can listen on a port using the [Swoole\Process\Pool->listen()](/process/process_pool?id=listen) method, receive data sent by the client using the [Message event](/process/process_pool?id=on), and respond to the client using the [Swoole\Process\Pool->write()](/process/process_pool?id=write) method.

When using this method to send data with `Swoole`, the client must prepend a 4-byte length value in network byte order before the actual data.
```php
$msg = 'Hello Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```
### UnixSocket
When the second parameter of `Swoole\Process\Pool->__construct` is set to `SWOOLE_IPC_UNIXSOCK`, it means using [UnixSocket](/learn?id=什么是IPC), **strongly recommended for inter-process communication**.

This method is relatively simple, just need to use the [Swoole\Process\Pool->sendMessage()](/process/process_pool?id=sendMessage) method and the [Message event](/process/process_pool?id=on) to complete inter-process communication.

Alternatively, after enabling 'coroutine mode', you can also obtain the `Swoole\Process` object through [Swoole\Process\Pool->getProcess()](/process/process_pool?id=getProcess), use [Swoole\Process->exportsocket()](/process/process?id=exportsocket) to get a `Swoole\Coroutine\Socket` object for inter-process communication. However, at this point, the [Message event](/process/process_pool?id=on) cannot be set.

!> For parameters and environment configuration, refer to the [constructor](/process/process_pool?id=__construct) and [configuration parameters](/process/process_pool?id=set).
## Constants

Constant | Description
---|---
SWOOLE_IPC_MSGQUEUE | System [message queue](/learn?id=What is IPC) communication
SWOOLE_IPC_SOCKET | Socket communication
SWOOLE_IPC_UNIXSOCK | [UnixSocket](/learn?id=What is IPC) communication (v4.4+)
## Coroutine Support

Support for coroutines has been added in version `v4.4.0`, please refer to [Swoole\Process\Pool::__construct](/process/process_pool?id=__construct) for more information.
## Usage Example

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** Current is the Worker process */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
```
## Methods
### __construct()

Constructor method.

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **Parameters**

  * **`int $worker_num`**
    * **Function**: Specify the number of worker processes.
    * **Default**: None
    * **Other values**: None

  * **`int $ipc_type`**
    * **Function**: Mode of inter-process communication (default is `SWOOLE_IPC_NONE` for no inter-process communication features).
    * **Default**: `SWOOLE_IPC_NONE`
    * **Other values**: `SWOOLE_IPC_MSGQUEUE`, `SWOOLE_IPC_SOCKET`, `SWOOLE_IPC_UNIXSOCK`

    !> -When set to `SWOOLE_IPC_NONE`, `onWorkerStart` callback must be set, and a looping logic must be implemented in `onWorkerStart`. When the `onWorkerStart` function exits, the worker process will immediately exit, and it will be restarted by the `Manager` process afterwards.  
    -Setting to `SWOOLE_IPC_MSGQUEUE` indicates using system message queue communication, where `$msgqueue_key` can be set to specify the message queue `KEY`. If the message queue `KEY` is not set, a private queue will be requested.  
    -Setting to `SWOOLE_IPC_SOCKET` indicates using `Socket` for communication, requiring the use of the [listen](/process/process_pool?id=listen) method to specify the listening address and port.  
    -Setting to `SWOOLE_IPC_UNIXSOCK` indicates using [unixSocket](/learn?id=What_is_IPC) for communication, which is used in coroutine mode. **It is strongly recommended to use this method for inter-process communication**, see usage details below.  
    -When using a non-`SWOOLE_IPC_NONE` setting, `onMessage` callback must be set, and `onWorkerStart` becomes optional.

  * **`int $msgqueue_key`**
    * **Function**: Message queue `key`.
    * **Default**: `0`
    * **Other values**: None

  * **`bool $enable_coroutine`**
    * **Function**: Whether to enable coroutine support (after using coroutine, it will not be possible to set the `onMessage` callback).
    * **Default**: `false`
    * **Other values**: `true`

* **Coroutine Mode**

In version `v4.4.0`, the `Process\Pool` module added support for coroutines. You can enable it by setting the 4th parameter to `true`. With coroutines enabled, the underlying layer will automatically create a coroutine and a [coroutine scheduler](/coroutine/scheduler) at `onWorkerStart`, enabling the direct use of coroutine-related APIs in the callback function, for example:

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

When coroutines are enabled, Swoole will prohibit setting the `onMessage` event callback. If inter-process communication is needed, set the second parameter to `SWOOLE_IPC_UNIXSOCK` to use [unixSocket](/learn?id=What_is_IPC) for communication. Then use `$pool->getProcess()->exportSocket()` to export the [Swoole\Coroutine\Socket](/coroutine_client/socket) object for implementing communication between `Worker` processes. Example:

```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> For detailed usage, refer to the chapters related to [Swoole\Coroutine\Socket](/coroutine_client/socket) and [Swoole\Process](/process/process?id=exportsocket).

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```
### set()

Setting parameters.

```php
Swoole\Process\Pool->set(array $settings): void
```

Optional parameters|Type|Function|Default value
---|---|----|----
enable_coroutine|bool|Controls whether to enable coroutines|false
enable_message_bus|bool|Enable message bus. When this value is `true`, large data will be split into small chunks and sent to the other end|false
max_package_size|int|Restricts the maximum amount of data the process can receive|2 * 1024 * 1024

* **Note**

  * When `enable_message_bus` is `true`, `max_package_size` is not effective because the data will be split into small chunks and sent/received.
  * In `SWOOLE_IPC_MSGQUEUE` mode, `max_package_size` is not effective either, as the maximum amount of data received at one time is `65536`.
  * In `SWOOLE_IPC_SOCKET` mode, if `enable_message_bus` is `false` and the received data exceeds `max_package_size`, the connection will be terminated.
  * In `SWOOLE_IPC_UNIXSOCK` mode, if `enable_message_bus` is `false` and the data is larger than `max_package_size`, the excess data will be truncated.
  * If coroutine mode is enabled and `enable_message_bus` is `true`, `max_package_size` is not effective. Data will be split and merged accordingly by the underlying mechanism. If not, the data receiving is limited by `max_package_size`.

!> Available in Swoole version >= v4.4.4
### on()

Set the callback function for the process pool.

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **Parameters** 

  * **`string $event`**
    * **Description**：Specify the event
    * **Default value**：None
    * **Other values**：None

  * **`callable $function`**
    * **Description**：Callback function
    * **Default value**：None
    * **Other values**：None

* **Events**

  * **onWorkerStart** Worker process starts

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Pool object
  * @param int $workerId   WorkerId: current working process number, the underlying system will assign numbers to child processes
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} is started\n";
  });
  ```

  * **onWorkerStop** Worker process stops

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Pool object
  * @param int $workerId   WorkerId: current working process number, the underlying system will assign numbers to child processes
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} stop\n";
  });
  ```

  * **onMessage** Message received

  !> Received messages externally. Only one message can be delivered for each connection, similar to the short connection mechanism of `PHP-FPM`

  ```php
  /**
    * @param \Swoole\Process\Pool $pool Pool object
    * @param string $data Message data content
   */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
    var_dump($data);
  });
  ```

  !> Event names are case-insensitive. `WorkerStart`, `workerStart`, or `workerstart` are all the same.
### listen()

Listen to a `SOCKET`, which can only be used when `$ipc_mode = SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **Parameters**

  * **`string $host`**
    * **Function**: The address to listen to. Supports both `TCP` and [unixSocket](/learn?id=what-is-ipc) types. Use `127.0.0.1` for listening to a `TCP` address with a specified `$port`. Use `unix:/tmp/php.sock` to listen to an [unixSocket](/learn?id=what-is-ipc) address.
    * **Default**: None
    * **Other Values**: None

  * **`int $port`**
    * **Function**: The port to listen to. Must be specified for `TCP` mode.
    * **Default**: `0`
    * **Other Values**: None

  * **`int $backlog`**
    * **Function**: The length of the listening queue.
    * **Default**: `2048`
    * **Other Values**: None

* **Return Value**

  * Returns `true` if successful in listening.
  * Returns `false` if listening fails, and you can call `swoole_errno` to get the error code. When listening fails, calling `start` will immediately return `false`.

* **Communication Protocol**

    When sending data to the listening port, the client must add a length value of 4 bytes in network byte order before the request. The protocol format is:

```php
// $msg is the data to be sent
$packet = pack('N', strlen($msg)) . $msg;
```

* **Usage Example**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```
### write()

Write data to the other end, it can only be used when `$ipc_mode` is `SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->write(string $data): bool
```

!> This method is a memory operation, with no `IO` consumption. The data sending operation is synchronous and blocking `IO`.

* **Parameters**

  * **`string $data`**
    * **Description**: The data content to be written. Multiple calls to `write` are allowed. The underlying system will write all the data to the `socket` after the `onMessage` function exits, and then `close` the connection.
    * **Default value**: None
    * **Other values**: None

* **Usage examples**

  * **Server side**

    ```php
    $pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
    
    $pool->on("Message", function ($pool, $message) {
        echo "Message: {$message}\n";
        $pool->write("hello ");
        $pool->write("world ");
        $pool->write("\n");
    });
    
    $pool->listen('127.0.0.1', 8089);
    $pool->start();
    ```

  * **Client side**

    ```php
    $fp = stream_socket_client("tcp://127.0.0.1:8089", $errno, $errstr) or die("error: $errstr\n");
    $msg = json_encode(['data' => 'hello', 'uid' => 1991]);
    fwrite($fp, pack('N', strlen($msg)) . $msg);
    sleep(1);
    // It will display hello world\n
    $data = fread($fp, 8192);
    var_dump(substr($data, 4, unpack('N', substr($data, 0, 4))[1]));
    fclose($fp);
    ```
### sendMessage()

Send data to the target process, which can only be used when `$ipc_mode` is `SWOOLE_IPC_UNIXSOCK`.

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **Parameters**

  * **`string $data`**
    * **Function**: Data to be sent
    * **Default Value**: None
    * **Other Values**: None

  * **`int $dst_worker_id`**
    * **Function**: Target process ID
    * **Default Value**: `0`
    * **Other Values**: None

* **Return Value**

  * Returns `true` on success
  * Returns `false` on failure

* **Note**

  * If the data to be sent is larger than `max_package_size` and `enable_message_bus` is `false`, the target process will truncate the data when receiving it

```php
<?php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => false, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(2048)


$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => true, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(6000)
```
### start()

Start the worker process.

```php
Swoole\Process\Pool->start(): bool
```

!> If started successfully, the current process enters the `wait` state to manage the worker processes;  
If start fails, it returns `false`, and you can get the error code using `swoole_errno`.

* **Example**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
```

* **Process Management**

  - When a worker process encounters a fatal error or exits actively, the manager will recycle it to avoid zombie processes.
  - After a worker process exits, the manager will automatically relaunch and create a new worker process.
  - When the main process receives the `SIGTERM` signal, it will stop forking new processes and kill all running worker processes.
  - When the main process receives the `SIGUSR1` signal, it will kill running worker processes one by one and restart new ones.

* **Signal Handling**

  Only the signal processing of the main process (manager process) has been set at the lower level, and no signals have been set for `Worker` worker processes. Developers need to implement signal listening themselves.

  - For asynchronous mode in worker processes, use [Swoole\Process::signal](/process/process?id=signal) to listen for signals.
  - For synchronous mode in worker processes, use `pcntl_signal` and `pcntl_signal_dispatch` to listen for signals.

  In worker processes, it is recommended to listen for the `SIGTERM` signal. When the main process needs to terminate a worker process, it will send the `SIGTERM` signal to it. If the worker process does not listen for the `SIGTERM` signal, the underlying system will forcibly terminate the process, leading to loss of some logic.

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```  
### stop()

Remove the current process socket from the event loop. This function only works after starting the coroutine.

```php
Swoole\Process\Pool->stop(): bool
```
### shutdown()

Terminate the working process.

```php
Swoole\Process\Pool->shutdown(): bool
```
### getProcess()

Get the current working process object. Returns a [Swoole\Process](/process/process) object.

!> Available in Swoole version >= `v4.2.0`

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **Parameters**

  * **`int $worker_id`**
    * **Functionality**: Specify the `worker` to get the process for【Optional parameter, default is the current `worker`】
    * **Default value**: None
    * **Other values**: None

!> Must be called after `start`, in the `onWorkerStart` of the working process or other callback functions;  
The returned `Process` object is a singleton, calling `getProcess()` multiple times in the working process will return the same object.

* **Usage example**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```
### detach()

Detaches the current Worker process in the process pool from management. The underlying layer will immediately create a new process, and the old process will no longer handle data, leaving the lifecycle management to the application layer.

!> Available in Swoole version >= `v4.7.0`

```php
Swoole\Process\Pool->detach(): bool
```
