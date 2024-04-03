# Methods
## __construct()

Creates a TCP Server object for [asynchronous I/O](/learn?id=sync-io-async-io).

```php
Swoole\Server::__construct(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```

  * **Parameters**

    * `string $host`

      * Function: specifies the IP address to listen on.
      * Default: none.
      * Other values: none.

      !> For IPv4, use `127.0.0.1` to listen on the local machine, and `0.0.0.0` to listen on all addresses.
      For IPv6, use `::1` to listen on the local machine, and `::` (equivalent to `0:0:0:0:0:0:0:0`) to listen on all addresses.

    * `int $port`

      * Function: specifies the port to listen on, e.g., `9501`.
      * Default: none.
      * Other values: none.

      !> If the value of `$sockType` is [UnixSocket Stream/Dgram](/learn?id=ipc), this parameter will be ignored.
      Listening on ports below `1024` requires `root` permission.
      If this port is in use, starting the server with `server->start` will fail.

    * `int $mode`

      * Function: specifies the running mode.
      * Default: [SWOOLE_PROCESS](/learn?id=swoole_process) multi-process mode (default).
      * Other values: [SWOOLE_BASE](/learn?id=swoole_base) basic mode.

      !> Starting from Swoole 5, the default mode is `SWOOLE_BASE`.

    * `int $sockType`

      * Function: specifies the Server's type.
      * Default: none.
      * Other values:
        * `SWOOLE_TCP/SWOOLE_SOCK_TCP` tcp ipv4 socket
        * `SWOOLE_TCP6/SWOOLE_SOCK_TCP6` tcp ipv6 socket
        * `SWOOLE_UDP/SWOOLE_SOCK_UDP` udp ipv4 socket
        * `SWOOLE_UDP6/SWOOLE_SOCK_UDP6` udp ipv6 socket
        * [SWOOLE_UNIX_DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) unix socket dgram
        * [SWOOLE_UNIX_STREAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/stream_server.php) unix socket stream 

      !> Using `$sock_type` | `SWOOLE_SSL` enables `SSL` tunnel encryption. When SSL is enabled, it must be configured with [ssl_key_file](/server/setting?id=ssl_cert_file) and [ssl_cert_file](/server/setting?id=ssl_cert_file).

  * **Examples**

```php
$server = new \Swoole\Server($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP);

// Can mix UDP/TCP, listen on internal and external ports, see addlistener section for multi-port listening.
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP); // Add TCP
$server->addlistener("192.168.1.100", 9503, SWOOLE_SOCK_TCP); // Add Web Socket
$server->addlistener("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDP
$server->addlistener("/var/run/myserv.sock", 0, SWOOLE_UNIX_STREAM); // UnixSocket Stream
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP | SWOOLE_SSL); // TCP + SSL

$port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP); // System randomly assigns a port, returned value is the randomly assigned port
echo $port->port;
```
## set()

Used to set various parameters at runtime. After the server starts, you can access the parameters array set by `Server->set` method through `$serv->setting`.

```php
Swoole\Server->set(array $setting): void
```

!> `Server->set` must be called before `Server->start`. For the specific meaning of each configuration, please refer to [this section](/server/setting).

  * **Example**

```php
$server->set(array(
    'reactor_num'   => 2,     // number of threads
    'worker_num'    => 4,     // number of processes
    'backlog'       => 128,   // set the length of the listen queue
    'max_request'   => 50,    // maximum number of requests per process
    'dispatch_mode' => 1,     // data packet dispatch strategy
));
```
## on()

Register event callback functions for `Server`.

```php
Swoole\Server->on(string $event, callable $callback): bool
```

!> Calling the `on` method multiple times will override the previous setting.

!> Starting from PHP 8.2, if `$event` is not an event specified by `Swoole`, PHP 8.2 will throw a warning because PHP 8.2 does not support directly setting dynamic properties.

  * **Parameters**

    * `string $event`
    
      * Description: Callback event name
      * Default: None
      * Other values: None

      !> Case-insensitive. For a list of available event callbacks, refer to [this section](/server/events). Do not add `on` in the event name string.

    * `callable $callback`

      * Description: Callback function
      * Default: None
      * Other values: None

      !> Can be a string of a function name, a class static method, an object method array, or an anonymous function. Refer to [this section](/learn?id=different-ways-to-set-callback-functions) for more details.
  
  * **Return Value**

    * Returns `true` if the operation is successful, `false` if the operation fails.

  * **Example**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('connect', function ($server, $fd){
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();
```
## addListener()

Add a listening port. In your business code, you can call [Swoole\Server->getClientInfo](/server/methods?id=getclientinfo) to find out from which port a connection originates.

```php
Swoole\Server->addListener(string $host, int $port, int $sockType): bool|Swoole\Server\Port
```

!> Listening on ports below `1024` requires `root` permission. 
If the main server is using the WebSocket or HTTP protocol, any newly added TCP port will inherit the protocol settings from the main server by default. You must call the `set` method separately to set a new protocol in order to use the new protocol. [See detailed explanation](/server/port). 
You can click [here](/server/server_port) to see detailed information about `Swoole\Server\Port`.

  * **Parameters**

    * `string $host`

      * Function: Same as the `$host` in `__construct()`
      * Default value: Same as the `$host` in `__construct()`
      * Other values: Same as the `$host` in `__construct()`

    * `int $port`

      * Function: Same as the `$port` in `__construct()`
      * Default value: Same as the `$port` in `__construct()`
      * Other values: Same as the `$port` in `__construct()`

    * `int $sockType`

      * Function: Same as the `$sockType` in `__construct()`
      * Default value: Same as the `$sockType` in `__construct()`
      * Other values: Same as the `$sockType` in `__construct()`
  
  * **Return Value**

    * Returning `Swoole\Server\Port` indicates a successful operation, returning `false` indicates a failed operation.

!> - For `Unix Socket` mode, the `$host` parameter must be a valid file path, and the `$port` parameter is ignored.  
- In `Unix Socket` mode, the client `$fd` will no longer be a number but a string representing a file path.  
- On `Linux` systems, after listening on an `IPv6` port, you can still connect using an `IPv4` address.
## listen()

This method is an alias for `addlistener`.

```php
Swoole\Server->listen(string $host, int $port, int $type): bool|Swoole\Server\Port
```
## addProcess()

Add a user-defined work process. This function is usually used to create a special work process for monitoring, reporting, or other specific tasks.

```php
Swoole\Server->addProcess(Swoole\Process $process): int
```

!> `start` does not need to be executed. The process will be automatically created and the specified child process function will be executed when the `Server` starts.

  * **Parameters**
  
    * [Swoole\Process](/process/process)

      * Functionality: `Swoole\Process` object
      * Default value: None
      * Other values: None

  * **Return Value**

    * Returns the process ID number if the operation is successful, otherwise, a fatal error will be thrown by the program.

  * **Note**

    !> - Created child processes can call various methods provided by the `$server` object, such as `getClientList/getClientInfo/stats`.                                   
    - In the `Worker/Task` process, you can call methods provided by `$process` to communicate with the child processes.        
    - In a user-defined process, you can use `$server->sendMessage` to communicate with `Worker/Task` processes.      
    - The user process cannot use the `Server->task/taskwait` interface.              
    - The user process can use interfaces such as `Server->send/close`.         
    - The user process should have a `while(true)` loop (as shown in the example below) or an [EventLoop](/learn?id=what-is-eventloop) loop (e.g., creating a timer) to prevent the user process from continuously exiting and restarting.         

  * **Lifecycle**

    ?> - The lifecycle of the user process is the same as that of the `Master` and [Manager](/learn?id=what-is-manager-process) processes and is not affected by [reload](/server/methods?id=reload).     
    - User processes are not controlled by the `reload` command and no information is sent to user processes during reloading.        
    - When shutting down the server with `shutdown`, a `SIGTERM` signal will be sent to the user process to close it.            
    - Custom processes are managed by the `Manager` process. If a fatal error occurs, the `Manager` process will reestablish it.         
    - Custom processes will not trigger events such as `onWorkerStop`. 

  * **Example**

    ```php
    $server = new Swoole\Server('127.0.0.1', 9501);
    
    /**
     * The user process implements a broadcast function, looping to receive messages from unixSocket and sending to all connections of the server
     */
    $process = new Swoole\Process(function ($process) use ($server) {
        $socket = $process->exportSocket();
        while (true) {
            $msg = $socket->recv();
            foreach ($server->connections as $conn) {
                $server->send($conn, $msg);
            }
        }
    }, false, 2, 1);
    
    $server->addProcess($process);
    
    $server->on('receive', function ($serv, $fd, $reactor_id, $data) use ($process) {
        // Broadcast the received message
        $socket = $process->exportSocket();
        $socket->send($data);
    });
    
    $server->start();
    ```

    Refer to the [Process Communication](/process/process?id=exportsocket) section.
## start()

Start the server and listen on all TCP/UDP ports.

```php
Swoole\Server->start(): bool
```

!> Tip: The example below is based on the [SWOOLE_PROCESS](/learn?id=swoole_process) mode

  * **Tips**

    - After successful startup, `worker_num+2` processes will be created: the `Master` process, the `Manager` process, and `serv->worker_num` `Worker` processes.
    - If startup fails, it will immediately return `false`.
    - After successful startup, it will enter the event loop, waiting for client connection requests. Code after the `start` method will not be executed.
    - When the server is closed, the `start` function will return `true` and continue with the execution.
    - Setting `task_worker_num` will increase the corresponding number of [Task processes](/learn?id=taskworker进程).
    - Methods listed before `start` can only be used before the `start` call, and methods listed after `start` can only be used in event callback functions such as [onWorkerStart](/server/events?id=onworkerstart), [onReceive](/server/events?id=onreceive), etc.

  * **Extensions**

    * Master Process

      * The master process has multiple [Reactor](/learn?id=reactor线程) threads, which perform network event polling based on `epoll/kqueue/select`. It forwards data to `Worker` processes for processing.
    
    * Manager Process

      * Manages all `Worker` processes. Automatically recycles `Worker` processes when their lifecycle ends or exceptions occur, and creates new `Worker` processes.
    
    * Worker Process

      * Processes the received data, including protocol parsing and responding to requests. If `worker_num` is not set, the underlying system will start `Worker` processes equal to the number of CPUs.
      * If startup fails, the extension will throw a fatal error. Check the relevant information in the `php error_log`. The `errno={number}` is a standard Linux Errno, which can be referred to in the relevant documentation.
      * If `log_file` setting is enabled, information will be printed to the specified `Log` file.

  * **Return Value**

    * Returns `true` if the operation is successful, `false` if it fails.

  * **Common Errors on Startup Failure**

    * Failure to bind a port because it is already occupied by another process.
    * Mandatory callback functions are not set, leading to startup failure.
    * Fatal PHP errors exist. Check the PHP error information in `php_errors.log`.
    * Execute `ulimit -c unlimited` to open `core dump` and check for segmentation faults.
    * Turn off `daemonize` and `log` so that error messages can be printed on the screen.
## reload()

Safely restart all Worker/Task processes.

```php
Swoole\Server->reload(bool $only_reload_taskworker = false): bool
```

!> For example: a busy backend server is always processing requests. If an administrator terminates/restarts the server program through the `kill` process, it may cause the code to be terminated halfway through execution.  
In this situation, data inconsistency may occur. In a trading system, for example, if the next step after the payment logic is shipping, and the process is terminated after the payment logic, it will result in users paying money but not receiving the shipment, which can have serious consequences.  
`Swoole` provides a mechanism for a graceful termination/restart. Administrators only need to send a specific signal to the `Server`, and the `Worker` process can safely terminate. See [How to Restart the Service Correctly](/question/use?id=swoole如何正确的重启服务).

  * **Parameters**
  
    * `bool $only_reload_taskworker`
    
      * Functionality: Only restart [Task processes](/learn?id=taskworker进程).
      * Default value: false
      * Other values: true

!> -`reload` has protection mechanism. When a `reload` is in progress, receiving a new restart signal will be discarded.
-If `user/group` is set, `Worker` processes may not have permission to send information to the `master` process. In this case, you must use the `root` account and execute the `kill` command in the shell to restart.
-The `reload` command is invalid for user processes added via [addProcess](/server/methods?id=addProcess).

  * **Return Value**
  
    * Returns `true` for a successful operation, and `false` for a failed operation.

  * **Extensions**
  
    * **Sending Signals**
    
        * `SIGTERM`: Send this signal to the main process/management process to safely terminate the server.
        * You can call `$serv->shutdown()` in PHP code to complete this operation.
        * `SIGUSR1`: Send the `SIGUSR1` signal to the main process/management process to gracefully `restart` all `Worker` processes and `TaskWorker` processes.
        * `SIGUSR2`: Send the `SIGUSR2` signal to the main process/management process to smoothly restart all `Task` processes.
        * You can call `$serv->reload()` in PHP code to complete this operation.

    ```shell
    # Restart all worker processes
    kill -USR1 main_process_PID
    
    # Restart only task processes
    kill -USR2 main_process_PID
    ```

      > [Reference: Linux Signal List](/other/signal)

    * **Process Mode**
    
        In the `Process` mode, `TCP` connections from clients are maintained in the `Master` process, and restarting or abnormal exit of `worker` processes will not affect the connection itself.

    * **Base Mode**
    
        In the `Base` mode, client connections are maintained directly in the `Worker` process, so reloading will disconnect all connections.

    !> `Base` mode does not support reload of [Task processes](/learn?id=taskworker进程).

    * **Reload Scope**
    
       The `Reload` operation can only reload the PHP files loaded by the `Worker` process after startup. Use the `get_included_files` function to list which files were loaded before `WorkerStart`. The PHP files in this list cannot be reloaded even after a `reload`. You must close the server to restart for the changes to take effect.

    ```php
    $serv->on('WorkerStart', function(Swoole\Server $server, int $workerId) {
        var_dump(get_included_files()); // Files in this array were loaded before process startup, so they cannot be reloaded
    });
    ```

    * **APC/OPcache**
    
        If `PHP` has `APC/OPcache` enabled, the `reload` operation is affected. There are `2` solutions:

        * Turn on `stat` detection for `APC/OPcache`. If a file is updated, `APC/OPcache` will automatically update the `OPCode`.
        * Execute `apc_clear_cache` or `opcache_reset` before loading files in `onWorkerStart` to refresh the `OPCode` cache.

  * **Note**
  
  !> -Graceful restart only works for PHP files that are `include/require` in [onWorkerStart](/server/events?id=onworkerstart) or [onReceive](/server/events?id=onreceive) within the `Worker` processes.
    -PHP files that are `include/require` before the `Server` startup cannot be reloaded through a graceful restart.
    -To reload the configuration of `Server` passed in through `$serv->set()`, you must close/restart the entire `Server`.
    -The `Server` can listen on an internal network port and receive remote control commands to restart all `Worker` processes.
## stop()

Stops the current `Worker` process and immediately triggers the `onWorkerStop` callback function.

```php
Swoole\Server->stop(int $workerId = -1, bool $waitEvent = false): bool
```

  * **Parameters**

    * `int $workerId`

      * Function: specify the `worker id`
      * Default value: -1, representing the current process
      * Other values: N/A

    * `bool $waitEvent`

      * Function: control the exit strategy, `false` means exit immediately, `true` means exit when the event loop is empty
      * Default value: false
      * Other values: true

  * **Return Value**

    * Returns `true` if the operation is successful, `false` if the operation fails

  * **Note**

    !> -[Asynchronous IO](/learn?id=同步io异步io) servers may have pending events when calling `stop` to exit the process. For example, if using `Swoole\MySQL->query` to send an SQL statement and waiting for the MySQL server to return the result. In this case, if the process forcefully exits, the result of the SQL execution will be lost.  
    -Setting `$waitEvent = true` will utilize an [asynchronous-safe restart](/question/use?id=swoole如何正确的重启服务) strategy at the underlying level. It notifies the `Manager` process to restart a new `Worker` to handle new requests. The old `Worker` will wait for events until the event loop is empty or exceeds `max_wait_time`, then exit the process, ensuring the safety of asynchronous events to the maximum extent.
## shutdown()

Shutdowns the server.

```php
Swoole\Server->shutdown(): bool
```

  * **Return Value**

    * Returns `true` on success, `false` on failure.

  * **Note**

    * This function can be used within a `Worker` process.
    * Sending `SIGTERM` to the master process can also achieve server shutdown.

```shell
kill -15 main_process_PID
```  
## tick()

Add the `tick` timer, which allows custom callback functions. This function is an alias of [Swoole\Timer::tick](/timer?id=tick).

```php
Swoole\Server->tick(int $millisecond, callable $callback): void
```

  * **Parameters**

    * `int $millisecond`

      * Function: interval time [in milliseconds]
      * Default value: none
      * Other values: none

    * `callable $callback`

      * Function: callback function
      * Default value: none
      * Other values: none

  * **Note**
  
    !> - After the `Worker` process stops running, all timers are automatically destroyed  
    - `tick/after` timers cannot be used before `Server->start`  
    - After `Swoole 5`, the usage of this alias has been removed; please directly use `Swoole\Timer::tick()`

  * **Example**

    * Use in [onReceive](/server/events?id=onreceive)

    ```php
    function onReceive(Swoole\Server $server, int $fd, int $reactorId, mixed $data)
    {
        $server->tick(1000, function () use ($server, $fd) {
            $server->send($fd, "hello world");
        });
    }
    ```

    * Use in [onWorkerStart](/server/events?id=onworkerstart)

    ```php
    function onWorkerStart(Swoole\Server $server, int $workerId)
    {
        if (!$server->taskworker) {
            $server->tick(1000, function ($id) {
              var_dump($id);
            });
        } else {
            //task
            $server->tick(1000);
        }
    }
    ```
## after()

Add a one-time timer that will be destroyed after execution. This function is an alias for [Swoole\Timer::after](/timer?id=after).

```php
Swoole\Server->after(int $millisecond, callable $callback)
```

  * **Parameters**

    * `int $millisecond`

      * Functionality: Execution time [milliseconds]
      * Default value: None
      * Other values: None
      * Version impact: Maximum not to exceed `86400000` in versions below `Swoole v4.2.10`

    * `callable $callback`

      * Functionality: Callback function, must be callable, the `callback` function does not accept any parameters
      * Default value: None
      * Other values: None

  * **Note**
  
    !> -The lifecycle of the timer is at the process level. When restarting or shutting down the process with `reload` or `kill`, all timers will be destroyed.  
    -If there are critical logic and data associated with certain timers, please implement in the `onWorkerStop` callback function, or refer to [How to restart the service correctly](/question/use?id=how-to-restart-the-service-correctly-in-swoole)  
    -After `Swoole5`, the usage of this alias method has been removed. Please directly use `Swoole\Timer::after()`
## defer()

Deferred execution of a function, an alias of [Swoole\Event::defer](/event?id=defer).

```php
Swoole\Server->defer(Callable $callback): void
```

  * **Parameters**

    * `Callable $callback`

      * Functionality: Callback function [required], can be an executable function variable, a string, an array, or an anonymous function.
      * Default value: None
      * Other values: None

  * **Note**

    !> -The underlying system will execute this function after the completion of the [EventLoop](/learn?id=what-is-eventloop) loop. The purpose of this function is to defer the execution of some PHP code so that the program can prioritize handling other `IO` events. For example, if a certain callback function involves CPU-intensive calculations but is not urgent, you can let the process handle other events before proceeding with the CPU-intensive calculations.
    -The underlying system does not guarantee that the `defer` function will be executed immediately. If it is a critical system logic that needs to be executed as soon as possible, consider using the `after` timer to achieve this.
    -When executing `defer` in the `onWorkerStart` callback, it will only be called after an event occurs.
    -Starting from `Swoole5`, the usage of this alias has been removed. Please directly use `Swoole\Event::defer()`.

  * **Example**

```php
function query($server, $db) {
    $server->defer(function() use ($db) {
        $db->close();
    });
}
```
## clearTimer()

Clear the `tick/after` timer, this function is an alias for [Swoole\Timer::clear](/timer?id=clear).

```php
Swoole\Server->clearTimer(int $timerId): bool
```

  * **Parameters**

    * `int $timerId`

      * Functionality: specifies the timer id
      * Default value: none
      * Other values: none

  * **Return Value**

    * Returns `true` if the operation is successful, `false` otherwise

  * **Note**

    !> - `clearTimer` can only be used to clear timers in the current process.
    - After `Swoole 5`, the usage of this alias has been deprecated. Please use `Swoole\Timer::clear()` directly.

  * **Example**

```php
$timerId = $server->tick(1000, function ($timerId) use ($server) {
    $server->clearTimer($timerId);// $id is the id of the timer
});
```
## close()

Close the client connection.

```php
Swoole\Server->close(int $fd, bool $reset = false): bool
```

  * **Parameters**

    * `int $fd`

      * Function: specify the `fd` (file descriptor) to close
      * Default value: none
      * Other values: none

    * `bool $reset`

      * Function: set to `true` to force close the connection and discard data in the send queue
      * Default value: false
      * Other values: true

  * **Return Value**

    * Returning `true` indicates success, returning `false` indicates failure

  * **Note**

  !> - Closing the connection actively by `Server` will also trigger the [onClose](/server/events?id=onclose) event
- Do not write cleanup logic after `close`. It should be placed in the [onClose](/server/events?id=onclose) callback for processing
- The `fd` of `HTTP\Server` can be obtained in the `response` within the upper-layer callback method

  * **Example**

```php
$server->on('request', function ($request, $response) use ($server) {
    $server->close($response->fd);
});
```
## send()

Send data to the client.

```php
Swoole\Server->send(int|string $fd, string $data, int $serverSocket = -1): bool
```

  * **Parameters**

    * `int|string $fd`

      * Function: Specify the file descriptor of the client or the Unix socket path
      * Default value: None
      * Other values: None

    * `string $data`

      * Function: Data to send; for TCP protocol, the maximum is 2M, can be modified by changing [buffer_output_size](/server/setting?id=buffer_output_size) to alter the maximum allowed package length
      * Default value: None
      * Other values: None

    * `int $serverSocket`

      * Function: Required when sending data to the UnixSocket DGRAM endpoint, not needed for TCP clients
      * Default value: -1, representing the current listening UDP port
      * Other values: None

  * **Return Value**

    * Returns `true` for successful operation, `false` for failed operation

  * **Tips**

    !> The sending process is asynchronous; the underlying system will automatically monitor for writeability and send data to the client gradually, meaning that the client doesn't immediately receive the data once `send` returns.

    * Security
      * The `send` operation is atomic; multiple processes simultaneously calling `send` to send data to the same TCP connection will not cause data mixing

    * Length Limitation
      * If you need to send data larger than 2M, you can write the data to a temporary file and then send it using the `sendfile` interface
      * By setting the [buffer_output_size](/server/setting?id=buffer_output_size) parameter, you can adjust the length limit for sending
      * When sending data larger than 8K, the underlying system will enable the worker process's shared memory, requiring a `Mutex->lock` operation

    * Buffer Area
      * When the buffer area of the worker process's UnixSocket [IPC](/learn?id=what-is-ipc) is full, sending 8K data will use temporary file storage
      * If continuously sending a large amount of data to the same client, if the client is unable to receive it in time, it will cause the Socket memory buffer to be filled, and the Swoole underlying system will immediately return `false`. When `false` is returned, the data can be saved to disk and sent after the client has finished receiving the data already sent

    * [Coroutine Scheduling](/coroutine?id=coroutine-scheduling)
      * When the coroutine mode is enabled with [send_yield](/server/setting?id=send_yield), `send` will automatically suspend when the buffer area is full. It will resume the coroutine when some of the data has been read by the client, then continue sending data.

    * [UnixSocket](/learn?id=what-is-ipc)
      * When listening on the UnixSocket DGRAM port, you can use `send` to send data to the endpoint.

      ```php
      $server->on("packet", function (Swoole\Server $server, $data, $addr){
          $server->send($addr['address'], 'SUCCESS', $addr['server_socket']);
      });
      ```
## sendfile()

Send a file to a `TCP` client connection.

```php
Swoole\Server->sendfile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
```

  * **Parameters**

    * `int $fd`

      * Function: Specify the file descriptor of the client
      * Default: None
      * Other values: None

    * `string $filename`

      * Function: The file path to send, returns `false` if the file does not exist
      * Default: None
      * Other values: None

    * `int $offset`

      * Function: Specify the file offset, can send data starting from a specific position in the file
      * Default: 0 【Default is `0`, indicating sending from the beginning of the file】
      * Other values: None

    * `int $length`

      * Function: Specify the length to send
      * Default: File size
      * Other values: None

  * **Return Value**

    * Returns `true` on success, `false` on failure

  * **Note**

  !> This function and `Server->send` both send data to the client, but the data sent by `sendfile` comes from the specified file
## sendto()

Send a UDP packet to any client `IP:PORT`.

```php
Swoole\Server->sendto(string $ip, int $port, string $data, int $serverSocket = -1): bool
```

  * **Parameters**

    * `string $ip`

      * Function: specify the client `ip`
      * Default: none
      * Other values: none

      ?> `$ip` is an `IPv4` or `IPv6` string, such as `192.168.1.102`. If the IP is invalid, an error will be returned.

    * `int $port`

      * Function: specify the client `port`
      * Default: none
      * Other values: none

      ?> `$port` is a network port number from `1` to `65535`, sending will fail if the port is incorrect.

    * `string $data`

      * Function: the data content to be sent, can be text or binary content
      * Default: none
      * Other values: none

    * `int $serverSocket`

      * Function: specify which port to use to send the corresponding server socket descriptor for the data packet 【can be obtained in the `$clientInfo` of the [onPacket event](/server/events?id=onpacket)】
      * Default: -1, indicates the current listening UDP port
      * Other values: none

  * **Return Value**

    * Returns `true` if the operation is successful, returns `false` if the operation fails.

      ?> The server may listen on multiple UDP ports simultaneously, refer to [Multi-Port Listening](/server/port), this parameter can specify which port to use for sending the data packet.

  * **Note**

  !> Must listen on a UDP port to send data to an `IPv4` address  
  Must listen on a UDP6 port to send data to an `IPv6` address

  * **Example**

```php
// Send a "hello world" string to host with IP address 220.181.57.216 on port 9502.
$server->sendto('220.181.57.216', 9502, "hello world");
// Send a UDP data packet to an IPv6 server
$server->sendto('2600:3c00::f03c:91ff:fe73:e98f', 9501, "hello world");
```
## sendwait()

Synchronously sends data to the client.

```php
Swoole\Server->sendwait(int $fd, string $data): bool
```

  * **Parameters**

    * `int $fd`
    
      * Function: specifies the file descriptor of the client
      * Default value: None
      * Other values: None

    * `string $data`
    
      * Function: data to be sent
      * Default value: None
      * Other values: None

  * **Return value**

    * Returns `true` if the operation is successful, returns `false` if the operation fails

  * **Tips**

    * In some special scenarios, the `Server` needs to continuously send data to the client, while the `Server->send` data sending interface is purely asynchronous, which can cause the memory send queue to become full when sending a large amount of data.

    * Using `Server->sendwait` can solve this problem. `Server->sendwait` will wait for the connection to become writable. It will only return after the data has been sent completely.

  * **Note**

  !> `sendwait` is currently only available for [SWOOLE_BASE](/learn?id=swoole_base) mode.
  `sendwait` is only used for local or internal network communication. Do not use `sendwait` for external network connections, and when `enable_coroutine` => true (default enabled), do not use this function as it will block other coroutines. Only synchronous blocking servers can use it.
## sendMessage()

Send messages to any `Worker` process or [Task process](/learn?id=taskworker-process). Can be called in non-main processes and management processes. The process that receives the message will trigger the `onPipeMessage` event.

```php
Swoole\Server->sendMessage(mixed $message, int $workerId): bool
```

  * **Parameters**

    * `mixed $message`

      * Function: the content of the message to be sent, with no length limit, but when exceeding `8K`, it will use temporary memory files
      * Default: none
      * Other values: none

    * `int $workerId`

      * Function: the `ID` of the target process, range referring to [$worker_id](/server/properties?id=worker_id)
      * Default: none
      * Other values: none

  * **Tips**

    * Calling `sendMessage` in a `Worker` process is [asynchronous I/O](/learn?id=synchronous-io-and-asynchronous-io); messages will be stored in the buffer first and sent to the [Unix socket](/learn?id=what-is-ipc) when it's writable
    * Calling `sendMessage` in a [Task process](/learn?id=taskworker-process) is by default [synchronous I/O](/learn?id=synchronous-io-and-asynchronous-io), but in some cases it may automatically switch to asynchronous I/O, see [Switching Synchronous I/O to Asynchronous I/O](/learn?id=synchronous-io-converted-to-asynchronous-io)
    * Calling `sendMessage` in a [User process](/server/methods?id=addprocess) is similar to Tasks, by default it's synchronous and blocking, see [Switching Synchronous I/O to Asynchronous I/O](/learn?id=synchronous-io-converted-to-asynchronous-io)

  * **Note**

  !> - If `sendMessage()` is [asynchronous I/O](/learn?id=synchronous-io-converted-to-asynchronous-io), do not continuously call `sendMessage()` if the receiving end process does not accept data for various reasons, as it will consume a large amount of memory resources. You can add a response mechanism and pause the calls if there is no response from the receiving end;  
-Memory files will be used on `MacOS/FreeBSD` when exceeding `2K`;  
-Registering an `onPipeMessage` event callback function is mandatory when using [sendMessage](/server/methods?id=sendMessage);  
-Setting [task_ipc_mode](/server/setting?id=task_ipc_mode) = 3 will prevent using [sendMessage](/server/methods?id=sendMessage) to send messages to specific task processes.

  * **Example**

```php
$server = new Swoole\Server('0.0.0.0', 9501);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 2,
));
$server->on('pipeMessage', function ($server, $src_worker_id, $data) {
    echo "#{$server->worker_id} message from #$src_worker_id: $data\n";
});
$server->on('task', function ($server, $task_id, $src_worker_id, $data) {
    var_dump($task_id, $src_worker_id, $data);
});
$server->on('finish', function ($server, $task_id, $data) {

});
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    if (trim($data) == 'task') {
        $server->task("async task coming");
    } else {
        $worker_id = 1 - $server->worker_id;
        $server->sendMessage("hello task process", $worker_id);
    }
});

$server->start();
```
## exist()

Check if the connection corresponding to `fd` exists.

```php
Swoole\Server->exist(int $fd): bool
```

  * **Parameters**

    * `int $fd`

      * Function: file descriptor
      * Default value: none
      * Other values: none

  * **Return Value**

    * Returns `true` if exists, `false` if not exists

  * **Note**
  
    * This interface is calculated based on shared memory and does not perform any `IO` operations
## pause()

Stop receiving data.

```php
Swoole\Server->pause(int $fd): bool
```

  * **Parameters**

    * `int $fd`

      * Function: specify file descriptor
      * Default value: none
      * Other values: none

  * **Return Value**

    * Returns `true` for success and `false` for failure.

  * **Notes**

    * After calling this function, the connection will be removed from the [EventLoop](/learn?id=what-is-eventloop) and will no longer receive data from the client.
    * This function does not affect the processing of the send queue.
    * Only in `SWOOLE_PROCESS` mode, after calling `pause`, some data may have already arrived at the `Worker` process, so it may still trigger the [onReceive](/server/events?id=onreceive) event.
## resume()

Resumes data reception. Used in pairs with the `pause` method.

```php
Swoole\Server->resume(int $fd): bool
```

  * **Parameters**

    * `int $fd`

      * Description: Specifies the file descriptor
      * Default: None
      * Other values: None

  * **Return Value**

    * Returns `true` if the operation is successful, `false` if the operation fails

  * **Tips**

    * After calling this function, the connection will be re-added to the [EventLoop](/learn?id=what-is-an-event-loop), continuing to receive client data
## getCallback()

Get the callback function for the specified event on the Server

```php
Swoole\Server->getCallback(string $event_name): \Closure|string|null|array
```

  * **Parameters**

    * `string $event_name`

      * Function: event name, do not need to add 'on', case-insensitive
      * Default value: None
      * Other values: Refer to [Events](/server/events)

  * **Return Value**

    * When the corresponding callback function exists, returns `Closure` / `string` / `array` based on different [ways to set callback functions](/learn?id=four-ways-to-set-callback-functions)
    * When the corresponding callback function does not exist, returns `null`
## getClientInfo()

Get connection information, alias is `Swoole\Server->connection_info()`

```php
Swoole\Server->getClientInfo(int $fd, int $reactorId = -1, bool $ignoreError = false): false|array
```

  * **Parameters**

    * `int $fd`

      * Function: Specify the file descriptor
      * Default value: None
      * Other values: None

    * `int $reactorId`

      * Function: The ID of the [Reactor](/learn?id=reactor线程) thread in which the connection is located, currently has no effect, solely to maintain API compatibility
      * Default value: -1
      * Other values: None

    * `bool $ignoreError`

      * Function: Whether to ignore errors; if set to `true`, it will return the connection information even if the connection is closed, `false` means it will return false when the connection is closed
      * Default value: false
      * Other values: None

  * **Tips**

    * Client certificate

      * The certificate can only be obtained in the process triggered by [onConnect](/server/events?id=onconnect)
      * Format is in `x509` format, certificate information can be obtained using the `openssl_x509_parse` function

    * When using [dispatch_mode](/server/setting?id=dispatch_mode) = 1/3 configuration, considering that this data packet distribution strategy is used for stateless services, relevant information will be directly deleted from memory after the connection is disconnected, so `Server->getClientInfo` cannot obtain related connection information.

  * **Return Value**

    * Returns `false` on failure
    * Returns an `array` containing client information on success

```php
$fd_info = $server->getClientInfo($fd);
var_dump($fd_info);

array(15) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(25)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(39136)
  ["remote_ip"]=>
  string(9) "127.0.0.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1677322106)
  ["last_time"]=>
  int(1677322106)
  ["last_recv_time"]=>
  float(1677322106.901918)
  ["last_send_time"]=>
  float(0)
  ["last_dispatch_time"]=>
  float(0)
  ["close_errno"]=>
  int(0)
  ["recv_queued_bytes"]=>
  int(78)
  ["send_queued_bytes"]=>
  int(0)
}
```

Parameter | Description
---|---
server_port | Server listening port
server_fd | Server fd
socket_fd | Client fd
socket_type | Socket type
remote_port | Client port
remote_ip | Client IP
reactor_id | Which Reactor thread the connection comes from
connect_time | Time when the client connected to the Server, in seconds, set by the master process
last_time | Time of the last data received, in seconds, set by the master process
last_recv_time | Time of the last data received, in seconds, set by the master process
last_send_time | Time of the last data sent, in seconds, set by the master process
last_dispatch_time | Time when the worker process received data
close_errno | Error code when the connection is closed; if the connection is closed abnormally, close_errno is a non-zero value, can refer to Linux error information list
recv_queued_bytes | Amount of data queued for processing
send_queued_bytes | Amount of data queued for sending
websocket_status | [Optional] WebSocket connection status; this information is added additionally when the server is Swoole\WebSocket\Server
uid | [Optional] This information is added additionally when a user ID is bound using bind
ssl_client_cert | [Optional] This information is added additionally when using SSL tunnel encryption and the client sets a certificate
## getClientList()

Traverse all client connections of the current `Server`. The `Server::getClientList` method is based on shared memory, with no `IOWait`, so the traversal speed is very fast. Additionally, `getClientList` will return all `TCP` connections, not just the `TCP` connections of the current `Worker` process. An alias is `Swoole\Server->connection_list()`.

```php
Swoole\Server->getClientList(int $start_fd = 0, int $pageSize = 10): false|array
```

* **Parameters**
  
  * `int $start_fd`
    
    * Function: Specify the starting `fd`
    * Default Value: 0
    * Other Values: None

  * `int $pageSize`
    
    * Function: Number of entries to retrieve per page, with a maximum limit of `100`
    * Default Value: 10
    * Other Values: None

* **Return Value**
  
  * Upon successful call, it will return a numeric indexed array, with elements being the retrieved `$fd`. The array will be sorted from small to large. The last `$fd` serves as the new `start_fd` for further retrieval.
  * Returns `false` in case of failure

* **Tips**
  
  * It is recommended to use the [Server::$connections](/server/properties?id=connections) iterator to iterate through connections.
  * `getClientList` is only available for `TCP` clients; `UDP` servers need to manage client information on their own.
  * In [SWOOLE_BASE](/learn?id=swoole_base) mode, only the connections of the current process can be retrieved.

* **Example**

```php
$start_fd = 0;
while (true) {
  $conn_list = $server->getClientList($start_fd, 10);
  if ($conn_list === false || count($conn_list) === 0) {
      echo "finish\n";
      break;
  }
  $start_fd = end($conn_list);
  var_dump($conn_list);
  foreach ($conn_list as $fd) {
      $server->send($fd, "broadcast");
  }
}
```
## bind()

Binds the connection with a user-defined `UID`. Setting [dispatch_mode](/server/setting?id=dispatch_mode) to 5 will make the `hash` fixed allocation based on this value. It ensures that all connections of a certain `UID` will be assigned to the same `Worker` process.

```php
Swoole\Server->bind(int $fd, int $uid): bool
```

  * **Parameters**

    * `int $fd`

      * Function: specifies the connection `fd`
      * Default: none
      * Other values: none

    * `int $uid`

      * Function: the `UID` to bind, must be a non-zero number
      * Default: none
      * Other values: `UID` cannot exceed a maximum of `4294967295` and a minimum of `-2147483648`

  * **Return Value**

    * Returns `true` if successful, `false` if failed

  * **Tips**

    * You can use `$serv->getClientInfo($fd)` to check the value of the `UID` bound to the connection.
    * Under the default [dispatch_mode](/server/setting?id=dispatch_mode)=2 settings, the `Server` will distribute connection data to different `Worker` processes based on the `socket fd`. Since `fd` is unstable and changes when a client reconnects after disconnecting, the data of this client will be assigned to another `Worker`. By using `bind`, you can distribute based on the user-defined `UID`. Even in the case of disconnection and reconnection, the TCP connection data with the same `UID` will be allocated to the same `Worker` process.

    * Timing Issue

      * When a client connects to the server and sends multiple packets continuously, there may be a timing issue. In the `bind` operation, subsequent packets may have already been dispatched, and these data packets will still be allocated to the current process based on `fd` modulo. Only the data packets received after the `bind` will be allocated based on `UID` modulo.
      * Therefore, if you want to use the `bind` mechanism, the network communication protocol needs to design a handshake step. After the client successfully connects, it should first send a handshake request and then not send any packets. After the server has finished binding and responded, the client can then send new requests.

    * Rebinding

      * In certain cases, the business logic may require a user connection to be rebound with a new `UID`. In this case, you can disconnect the connection, establish a new TCP connection and handshake, and bind it to the new `UID`.

    * Binding a negative `UID`

      * If a negative `UID` is bound, it will be converted to a `32-bit unsigned integer` at the lower level. In the PHP layer, it needs to be converted to a `32-bit signed integer`. You can use:
      
  ```php
  $uid = -10;
  $server->bind($fd, $uid);
  $bindUid = $server->connection_info($fd)['uid'];
  $bindUid = $bindUid >> 31 ? (~($bindUid - 1) & 0xFFFFFFFF) * -1 : $bindUid;
  var_dump($bindUid === $uid);
  ```

  * **Note**

!> -Effective only when setting `dispatch_mode=5`  
-When `UID` is not bound, allocation is performed based on `fd` modulo by default  
-A connection can only be `bind` once; if a `UID` is already bound, calling `bind` again will return `false`

  * **Example**

```php
$serv = new Swoole\Server('0.0.0.0', 9501);

$serv->fdlist = [];

$serv->set([
    'worker_num' => 4,
    'dispatch_mode' => 5,   //uid dispatch
]);

$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "{$fd} connect, worker:" . $serv->worker_id . PHP_EOL;
});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    $conn = $serv->connection_info($fd);
    print_r($conn);
    echo "worker_id: " . $serv->worker_id . PHP_EOL;
    if (empty($conn['uid'])) {
        $uid = $fd + 1;
        if ($serv->bind($fd, $uid)) {
            $serv->send($fd, "bind {$uid} success");
        }
    } else {
        if (!isset($serv->fdlist[$fd])) {
            $serv->fdlist[$fd] = $conn['uid'];
        }
        print_r($serv->fdlist);
        foreach ($serv->fdlist as $_fd => $uid) {
            $serv->send($_fd, "{$fd} say:" . $data);
        }
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "{$fd} Close". PHP_EOL;
    unset($serv->fdlist[$fd]);
});

$serv->start();
```
## stats()

Get information such as the number of active TCP connections, start time of the `Server`, total number of `accept`/`close` operations (connection establishment/closure), and more.

```php
Swoole\Server->stats(): array
```

  * **Example**

```php
array(25) {
  ["start_time"]=>
  int(1677310656)
  ["connection_num"]=>
  int(1)
  ["abort_count"]=>
  int(0)
  ["accept_count"]=>
  int(1)
  ["close_count"]=>
  int(0)
  ["worker_num"]=>
  int(2)
  ["task_worker_num"]=>
  int(4)
  ["user_worker_num"]=>
  int(0)
  ["idle_worker_num"]=>
  int(1)
  ["dispatch_count"]=>
  int(1)
  ["request_count"]=>
  int(0)
  ["response_count"]=>
  int(1)
  ["total_recv_bytes"]=>
  int(78)
  ["total_send_bytes"]=>
  int(165)
  ["pipe_packet_msg_id"]=>
  int(3)
  ["session_round"]=>
  int(1)
  ["min_fd"]=>
  int(4)
  ["max_fd"]=>
  int(25)
  ["worker_request_count"]=>
  int(0)
  ["worker_response_count"]=>
  int(1)
  ["worker_dispatch_count"]=>
  int(1)
  ["task_idle_worker_num"]=>
  int(4)
  ["tasking_num"]=>
  int(0)
  ["coroutine_num"]=>
  int(1)
  ["coroutine_peek_num"]=>
  int(1)
  ["task_queue_num"]=>
  int(1)
  ["task_queue_bytes"]=>
  int(1)
}
```

Parameter | Description
---|---
start_time | The time the server started
connection_num | Number of current connections
abort_count | Number of connections rejected
accept_count | Number of connections accepted
close_count | Number of closed connections
worker_num  | Number of worker processes started
task_worker_num  | Number of task worker processes started [Available from `v4.5.7`]
user_worker_num  | Number of user-defined task worker processes started
idle_worker_num | Number of idle worker processes
dispatch_count | Number of packets sent from Server to Worker [Available from `v4.5.7`, effective only in [SWOOLE_PROCESS](/learn?id=swoole_process) mode]
request_count | Number of requests received by the Server [Calculated for data requests handled by `onReceive`, `onMessage`, `onRequest`, `onPacket` events only]
response_count | Number of responses sent by the Server
total_recv_bytes| Total data received
total_send_bytes | Total data sent
pipe_packet_msg_id | ID for inter-process communication
session_round | Initial session ID
min_fd | Minimum connection file descriptor
max_fd | Maximum connection file descriptor
worker_request_count | Number of requests received by the current Worker process [Worker processes will exit when `worker_request_count` exceeds `max_request`]
worker_response_count | Number of responses sent by the current Worker process
worker_dispatch_count | Count of tasks delivered from master process to the current Worker process, incremented during dispatch by master process
task_idle_worker_num | Number of idle task processes
tasking_num | Number of active task processes
coroutine_num | Current number of coroutines [For Coroutine], for more information refer to [this section](/coroutine/gdb)
coroutine_peek_num | Total number of coroutines
task_queue_num | Number of tasks in the message queue [For Task]
task_queue_bytes | Memory usage in bytes of the task message queue [For Task]
## task()

Deliver an asynchronous task to the `task_worker` pool. This function is non-blocking, it will return immediately after execution. The `Worker` process can continue to handle new requests. To use the `Task` feature, you must first set `task_worker_num` and set the [onTask](/server/events?id=ontask) and [onFinish](/server/events?id=onfinish) event callbacks of the `Server`.

```php
Swoole\Server->task(mixed $data, int $dstWorkerId = -1, callable $finishCallback): int
```

  * **Parameters**

    * `mixed $data`

      * Function: Data of the task to be delivered, must be a serializable PHP variable
      * Default: None
      * Other values: None

    * `int $dstWorkerId`

      * Function: Specify which [Task process](/learn?id=taskworker-process) you want to deliver the task to, pass the ID of the Task process, range is `[0, $server->setting['task_worker_num']-1]`
      * Default: -1 (Default value `-1` indicates random delivery, the underlying system will automatically select an available [Task process](/learn?id=taskworker-process))
      * Other values: `[0, $server->setting['task_worker_num']-1]`

    * `callable $finishCallback`

      * Function: `finish` callback function, if the task is set with a callback function, the specified callback function will be executed directly when the Task returns the result, and the [onFinish](/server/events?id=onfinish) callback of the `Server` will not be executed. This is only triggered when a task is delivered in the `Worker` process.
      * Default: `null`
      * Other values: None

  * **Return Value**

    * On successful call, the return value is an integer `$task_id`, representing the ID of this task. If there is a `finish` callback, the [onFinish](/server/events?id=onfinish) callback will carry the `$task_id` parameter.
    * On failure, the return value is `false`, the `$task_id` may be `0`, so use `===` to check for failure

  * **Tips**

    * This feature is used to asynchronously execute slow tasks, for example, in a chat room server, it can be used to broadcast messages. When the task is completed, call `$serv->finish("finish")` in the [Task process](/learn?id=taskworker-process) to inform the `Worker` process that the task has been completed. Of course, `Swoole\Server->finish` is optional.
    * `task` uses [unixSocket](/learn?id=IPC) communication at a full memory level, without `IO` consumption. The reading and writing performance of a single process can reach `1 million/s`, different processes use different `unixSocket` communication, maximizing the utilization of multiple cores.
    * If no target [Task process](/learn?id=taskworker-process) is specified, the `task` method will check the busy/idle status of the [Task process](/learn?id=taskworker-process), the underlying system will only deliver tasks to idle [Task processes](/learn?id=taskworker-process). If all [Task processes](/learn?id=taskworker-process) are busy, the system will cyclically deliver tasks to different processes. You can use the [server->stats](/server/methods?id=stats) method to get the current number of queued tasks.
    * For the third parameter, you can directly set the [onFinish](/server/events?id=onfinish) function. If the task is set with a callback function, the specified callback function will be executed directly when the Task returns the result, and the `Server`'s [onFinish](/server/events?id=onfinish) callback will not be executed. This is only triggered when a task is delivered in the `Worker` process

    ```php
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    ```

    * `$task_id` is an integer from `0-4.2 billion`, unique within the current process
    * By default, the `task` feature is not enabled, you need to manually set `task_worker_num` to activate this feature
    * The number of `TaskWorker` can be adjusted in the parameters of [Server->set()](/server/methods?id=set), such as `task_worker_num => 64`, which means to start `64` processes to receive asynchronous tasks

  * **Configuration Parameters**

    * When the data passed to the `Server->task/taskwait/finish` `3` methods exceeds `8K`, a temporary file will be used to save it. When the content of the temporary file exceeds [server->package_max_length](/server/setting?id=package_max_length), the underlying system will issue a warning. This warning does not affect the delivery of data, but excessively large tasks may have performance issues.
    
    ```shell
    WARN: task package is too big.
    ```

  * **One-way Task**

    * Tasks delivered from the `Master`, `Manager`, `UserProcess` processes are one-way, and you cannot return results data using `return` or `Server->finish()` methods in the `TaskWorker` process.

  * **Note**

  !> - The `task` method cannot be called in the [Task process](/learn?id=taskworker-process)  
- For using `task`, the `Server` must set the [onTask](/server/events?id=ontask) and [onFinish](/server/events?id=onfinish) callbacks, otherwise `Server->start` will fail
- The number of `task` operations must be less than the processing speed of [onTask](/server/events?id=ontask). If the delivery capacity exceeds the processing capability, the `task` data will fill up the cache area, causing the `Worker` process to block. The `Worker` process will not be able to receive new requests.
- In user processes added using [addProcess](/server/method?id=addProcess), you can use `task` to unidirectionally deliver tasks, but cannot return result data. Please use the [sendMessage](/server/methods?id=sendMessage) interface to communicate with `Worker/Task` processes.

  * **Example**

```php
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 4,
));

$server->on('Receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    echo "Received data " . $data . "\n";
    $data    = trim($data);
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    $task_id = $server->task($data, 0);
    $server->send($fd, "Task distributed, task id is $task_id\n");
});

$server->on('Task', function (Swoole\Server $server, $task_id, $reactor_id, $data) {
    echo "Task process received data";
    echo "#{$server->worker_id}\tonTask: [PID={$server->worker_pid}]: task_id=$task_id, data_len=" . strlen($data) . "." . PHP_EOL;
    $server->finish($data);
});

$server->on('Finish', function (Swoole\Server $server, $task_id, $data) {
    echo "Task#$task_id finished, data_len=" . strlen($data) . PHP_EOL;
});

$server->on('workerStart', function ($server, $worker_id) {
    global $argv;
    if ($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]}: task_worker");
    } else {
        swoole_set_process_name("php {$argv[0]}: worker");
    }
});

$server->start();
```  
## taskwait()

`taskwait` is the method with the same purpose as the `task` method, used to deliver an asynchronous task to the [task worker](/learn?id=taskworker-process) pool for execution. Unlike `task`, `taskwait` is a synchronous function, it waits until the task is completed or times out. `$result` is the result of the task execution, sent out by the `$server->finish` function. If the task times out, it will return `false`.

```php
Swoole\Server->taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1): mixed
```

  * **Parameters**

    * `mixed $data`

      * Function: Data of the task to deliver, can be of any type, non-string types will be serialized automatically by the underlying system.
      * Default Value: None
      * Other Values: None

    * `float $timeout`

      * Function: Timeout duration, float type, in seconds, supporting a minimum granularity of `1ms`. If no data is returned from the designated [task worker](/learn?id=taskworker-process) within the specified time, `taskwait` will return `false` and will not process subsequent task result data.
      * Default Value: 0.5
      * Other Values: None

    * `int $dstWorkerId`

      * Function: Specify the [task worker](/learn?id=taskworker-process) to which the task is to be delivered. Simply provide the ID of the task worker, within the range `[0, $server->setting['task_worker_num']-1]`.
      * Default Value: -1 (Default value of `-1` means randomly deliver, the system will automatically select an idle [task worker](/learn?id=taskworker-process).)
      * Other Values: `[0, $server->setting['task_worker_num']-1]`

  *  **Return Value**

      * Returning `false` indicates task delivery failure.
      * If the `finish` method is executed in the `onTask` event or a `return` is performed, then `taskwait` will return the result delivered by the `onTask` event.

  * **Tips**

    * **Coroutine Mode**

      * Starting from version `4.0.4`, the `taskwait` method will support [coroutine scheduling](/coroutine? id=coroutine-scheduling). When `Server->taskwait()` is called in a coroutine, it will automatically undergo [coroutine scheduling](/coroutine? id=coroutine-scheduling), avoiding blocking waits.
      * Leveraging the [coroutine scheduler](/coroutine? id=coroutine-scheduler), `taskwait` can achieve concurrent calls.
       * There should be only one return or one `Server->finish` in the `onTask` event, otherwise an expired task[1] warning will be generated after any excess return or `Server->finish` is executed.

    * **Synchronous Mode**

      * In synchronous blocking mode, `taskwait` requires the use of [UnixSocket](/learn? id=what-is-ipc) communication and shared memory to return data to the `Worker` process, and this process is synchronous and blocking.

    * **Special Cases**

      * If there are no [synchronous I/O](/learn? id=synchronous-io-asynchronous-io) operations in the [onTask](/server/events? id=ontask) event, with only `2` process switches at the bottom, and no `IO` wait is generated. In this case, `taskwait` can be considered non-blocking. In actual testing, only reading and writing `PHP` arrays in the [onTask](/server/events? id=ontask) event, with `100,000` `taskwait` operations taking only `1` second, averaging `10` microseconds per operation.

  * **Attention**

  !> - Do not use `Swoole\Server::finish` with `taskwait`.  
- The `taskwait` method cannot be called in the [task worker](/learn? id=taskworker-process).
## taskWaitMulti()

Concurrently execute multiple asynchronous tasks. This method does not support coroutine scheduling and may cause other coroutines to start. In coroutine environment, you need to use `taskCo` as described below.

```php
Swoole\Server->taskWaitMulti(array $tasks, float $timeout = 0.5): false|array
```

  * **Parameters**

    * `array $tasks`

      * Function: Must be a numerically indexed array, does not support associative arrays. The underlying system will iterate through `$tasks` and deliver each task to [Task Worker Process](/learn?id=taskworker-process) one by one.
      * Default: None
      * Other values: None

    * `float $timeout`

      * Function: Floating point number indicating time in seconds.
      * Default: 0.5 seconds
      * Other values: None

  * **Return Value**

    * When tasks are completed or timed out, it returns an array of results. The order of results in the array corresponds to the order of tasks in `$tasks`, for example: `$result[2]` corresponds to `$tasks[2]`.
    * Timeout of a specific task will not affect other tasks. The results array will not include tasks that have timed out.

  * **Note**

  !> -The maximum number of concurrent tasks should not exceed `1024`.

  * **Example**

```php
$tasks[] = mt_rand(1000, 9999); // Task 1
$tasks[] = mt_rand(1000, 9999); // Task 2
$tasks[] = mt_rand(1000, 9999); // Task 3
var_dump($tasks);

// Wait for all task results to return, timeout set to 10s
$results = $server->taskWaitMulti($tasks, 10.0);

if (!isset($results[0])) {
    echo "Task 1 timed out\n";
}
if (isset($results[1])) {
    echo "Result of Task 2 is {$results[1]}\n";
}
if (isset($results[2])) {
    echo "Result of Task 3 is {$results[2]}\n";
}
```
## taskCo()

Concurrently execute `Task` and perform coroutine scheduling, used to support the `taskWaitMulti` functionality in a coroutine environment.

```php
Swoole\Server->taskCo(array $tasks, float $timeout = 0.5): false|array
```

* `$tasks`: List of tasks, must be an array. The underlying system will iterate through the array, delivering each element as a `task` to the `Task` process pool.
* `$timeout`: Timeout time, default is `0.5` seconds. If not all tasks are completed within the specified time, the process will immediately terminate and return the results.
* Upon completion of tasks or timeout, return an array of results. The order of each task result in the result array corresponds to `$tasks`, for example: the result corresponding to `$tasks[2]` is `$result[2]`.
* If a specific task fails or times out, the corresponding item in the result array will be `false`, for example: if `$tasks[2]` fails, then the value of `$result[2]` will be `false`.

!> The maximum number of concurrent tasks must not exceed `1024`

  * **Scheduling Process**

    * Each task in the `$tasks` list will be randomly delivered to a `Task` worker process. After delivery, `yield` suspends the current coroutine and sets a timer for `$timeout` seconds.
    * In `onFinish`, collect the corresponding task results and save them to the result array. Check if all tasks have returned results. If not, continue waiting. If yes, `resume` to restore the execution of the corresponding coroutine and clear the timeout timer.
    * If not all tasks are completed within the specified time, the timer will trigger first, the system will clear the waiting status. Unfinished task results will be marked as `false`, and the corresponding coroutine will be immediately resumed.

  * **Example**

```php
$server = new Swoole\Http\Server("127.0.0.1", 9502, SWOOLE_BASE);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $serv, $task_id, $worker_id, $data) {
    echo "#{$serv->worker_id}\tonTask: worker_id={$worker_id}, task_id=$task_id\n";
    if ($serv->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "hello world";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('Test End, Result: ' . var_export($result, true));
});

$server->start();
```
## finish()

Used in the [Task Worker process](/learn?id=taskworker-process) to notify the `Worker` process that the delivered task has been completed. This function can pass result data to the `Worker` process.

```php
Swoole\Server->finish(mixed $data): bool
```

  * **Parameters**

    * `mixed $data`

      * Function: Result content of the task processing
      * Default value: None
      * Other values: None

  * **Return Value**

    * Returns `true` on success, `false` on failure

  * **Tips**
    * The `finish` method can be called multiple times consecutively, triggering the [onFinish](/server/events?id=onfinish) event multiple times in the `Worker` process
    * After calling the `finish` method in the [onTask](/server/events?id=ontask) callback function, the `return` data will still trigger the [onFinish](/server/events?id=onfinish) event
    * `Server->finish` is optional. If the `Worker` process does not need to care about the task execution result, this function does not need to be called
    * Returning a string in the [onTask](/server/events?id=ontask) callback function is equivalent to calling `finish`

  * **Note**

  !> When using the `Server->finish` function, an [onFinish](/server/events?id=onfinish) callback function must be set for the `Server`. This function can only be used in the [Task Worker process](/learn?id=taskworker-process) in the [onTask](/server/events?id=ontask) callback.
## heartbeat()

Different from the passive detection of [heartbeat_check_interval](/server/setting?id=heartbeat_check_interval), this method actively checks all connections of the server and identifies the connections that have exceeded the agreed time. If `if_close_connection` is specified, it will automatically close the timed-out connections. If not specified, it will only return an array of connection `fd`.

```php
Swoole\Server->heartbeat(bool $ifCloseConnection = true): bool|array
```

  * **Parameters**
  
    * `bool $ifCloseConnection`
    
      * Functionality: Whether to close timed-out connections
      * Default value: true
      * Other values: false

  * **Return Value**
  
    * If successful, it will return a continuous array containing closed `$fd`
    * If not successful, it will return `false`

  * **Example**

```php
$closeFdArrary = $server->heartbeat();
```
## getLastError()

Retrieve the error code of the most recent operation error. In the business code, different logic can be executed based on the error code type.

```php
Swoole\Server->getLastError(): int
```

  * **Return Values**

Error Code | Explanation
---|---
1001 | The connection has been closed by the `Server`. This error generally occurs when the code has already executed `$server->close()` to close a connection but still calls `$server->send()` to send data to that connection.
1002 | The connection has been closed by the `Client`, and the `Socket` is closed, unable to send data to the peer.
1003 | Currently closing, `send()` cannot be used in the [onClose](/server/events?id=onclose) callback function.
1004 | The connection has been closed.
1005 | Connection does not exist; the provided `$fd` may be incorrect.
1007 | Timeout data received. After `TCP` closes the connection, some data may remain in the [unixSocket](/learn?id=什么是IPC) buffer, and that data will be discarded.
1008 | Sending buffer is full and cannot proceed with the `send` operation. This error indicates that the peer of this connection is unable to receive data promptly, causing the sending buffer to fill up.
1202 | The data sent exceeds the [server->buffer_output_size](/server/setting?id=buffer_output_size) setting.
9007 | Only occurs when using [dispatch_mode](/server/setting?id=dispatch_mode)=3, indicating that currently no available processes are present. You may increase the `worker_num` process count.
## getSocket()

Calling this method can obtain the underlying `socket` handle, and the returned object is a `sockets` resource handle.

```php
Swoole\Server->getSocket(): false|\Socket
```

!> This method requires the `sockets` extension of PHP and the `--enable-sockets` option to be enabled when compiling `Swoole`.

  * **Listening Port**

    * Ports added using the `listen` method can use the `getSocket` method provided by the `Swoole\Server\Port` object.

    ```php
    $port = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $socket = $port->getSocket();
    ```

    * Using the `socket_set_option` function can set some lower-level `socket` parameters.

    ```php
    $socket = $server->getSocket();
    if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
        echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
    }
    ```

  * **Support for Multicast**

    * Using `socket_set_option` to set the `MCAST_JOIN_GROUP` parameter can join the `Socket` to multicast and listen for multicast network packets.

```php
$server = new Swoole\Server('0.0.0.0', 9905, SWOOLE_BASE, SWOOLE_SOCK_UDP);
$server->set(['worker_num' => 1]);
$socket = $server->getSocket();

$ret = socket_set_option(
    $socket,
    IPPROTO_IP,
    MCAST_JOIN_GROUP,
    array(
        'group' => '224.10.20.30', // Represents the multicast address
        'interface' => 'eth0' // Represents the name of the network interface, which can be a number or a string, such as eth0, wlan0
    )
);

if ($ret === false) {
    throw new RuntimeException('Unable to join multicast group');
}

$server->on('Packet', function (Swoole\Server $server, $data, $addr) {
    $server->sendto($addr['address'], $addr['port'], "Swoole: $data");
    var_dump($addr, strlen($data));
});

$server->start();
```
## protect()

Set the client connection to a protected state, not being disconnected by the heartbeat thread.

```php
Swoole\Server->protect(int $fd, bool $is_protected = true): bool
```

  * **Parameters**

    * `int $fd`

      * Function: specify the client connection `fd`
      * Default value: none
      * Other values: none

    * `bool $is_protected`

      * Function: the state to be set
      * Default value: true (denotes protected state)
      * Other values: false (denotes unprotected state)

  * **Return value**

    * Returns `true` indicating a successful operation, returns `false` indicating a failed operation.
## confirm()

Confirm the connection, used in conjunction with [enable_delay_receive](/server/setting?id=enable_delay_receive). When the client establishes a connection, it does not listen for readable events, only triggers the [onConnect](/server/events?id=onconnect) event callback. In the [onConnect](/server/events?id=onconnect) callback, execute `confirm` to confirm the connection. At this point, the server will start listening for readable events to receive data from the connected clients.

!> Available for Swoole version >= `v4.5.0`

```php
Swoole\Server->confirm(int $fd): bool
```

  * **Parameters**

    * `int $fd`

      * Function: Unique identifier of the connection
      * Default: None
      * Others: None

  * **Return Value**
  
    * Returns `true` if confirmation is successful
    * Returns `false` if the connection corresponding to `$fd` does not exist, is closed, or is already in listening state; indicating confirmation failure

  * **Purpose**
  
    This method is generally used to protect the server from receiving traffic overload attacks. When a client connection is received, the [onConnect](/server/events?id=onconnect) function is triggered. It can be used to check the source `IP` and decide whether to allow sending data to the server.

  * **Example**
    
```php
// Create a Server object, listen on 127.0.0.1:9501
$serv = new Swoole\Server("127.0.0.1", 9501); 
$serv->set([
    'enable_delay_receive' => true,
]);

// Listen for connection events
$serv->on('Connect', function ($serv, $fd) {  
    // Check $fd here and confirm if okay
    $serv->confirm($fd);
});

// Listen for data receive events
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "Server: " . $data);
});

// Listen for connection close events
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Start the server
$serv->start(); 
```
## getWorkerId()

Obtain the `id` of the current `Worker` process (not the process's `PID`), consistent with `$workerId` at [onWorkerStart](/server/events?id=onworkerstart).

```php
Swoole\Server->getWorkerId(): int|false
```

!> Available for Swoole version >= `v4.5.0RC1`
## getWorkerPid()

Get the `PID` of the specified `Worker` process

```php
Swoole\Server->getWorkerPid(int $worker_id = -1): int|false
```

  * **Parameters**

    * `int $worker_id`

      * Function: get the `pid` of the specified process
      * Default value: -1 (represents the current process)
      * Other values: N/A

!> Available since Swoole version >= `v4.5.0RC1`
## getWorkerStatus()

Obtain the status of the `Worker` process

```php
Swoole\Server->getWorkerStatus(int $worker_id = -1): int|false
```

!> Available in Swoole version >= `v4.5.0RC1`

  * **Parameters**

    * `int $worker_id`

      * Function: Get process status
      * Default value: -1, [-1 represents the current process]
      * Other values: None

  * **Return Value**
  
    * Returns the status of the `Worker` process, refer to the process status values
    * Returns `false` if it is not a `Worker` process or the process does not exist

  * **Process Status Values**

    Constant | Value | Description | Version Dependency
    ---|---|---|---
    SWOOLE_WORKER_BUSY | 1 | Busy | v4.5.0RC1
    SWOOLE_WORKER_IDLE | 2 | Idle | v4.5.0RC1
    SWOOLE_WORKER_EXIT | 3 | In the case where [reload_async](/server/setting?id=reload_async) is enabled, there may be 2 processes for the same worker_id, one new and one old. The old process will read an EXIT status code. | v4.5.5
## getManagerPid()

Get the `PID` of the `Manager` process for the current service.

```php
Swoole\Server->getManagerPid(): int
```

!> Available in Swoole version `v4.5.0RC1` or higher
## getMasterPid()

Get the `PID` of the `Master` process of the current service.

```php
Swoole\Server->getMasterPid(): int
```

!> Available in Swoole version >= `v4.5.0RC1`
## addCommand()

Add a custom command `command`

```php
Swoole\Server->addCommand(string $name, int $accepted_process_types, Callable $callback): bool
```

!> -Available in Swoole version >= `v4.8.0`  
  -This function can only be called before the service is started. If there is a command with the same name, it will return `false` directly.

* **Parameters**

    * `string $name`

        * Function: Name of the `command`
        * Default value: None
        * Other values: None

    * `int $accepted_process_types`

      * Function: Process types that accept requests. If you want to support multiple process types, you can connect them with `|`, for example, `SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_MANAGER`
      * Default value: None
      * Other values:
        * `SWOOLE_SERVER_COMMAND_MASTER` master process
        * `SWOOLE_SERVER_COMMAND_MANAGER` manager process
        * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` worker process
        * `SWOOLE_SERVER_COMMAND_TASK_WORKER` task process

    * `callable $callback`

        * Function: Callback function. It has two parameters, one is the class of `Swoole\Server`, and the other is a user-defined variable. This variable is passed through the fourth parameter of `Swoole\Server::command()`.
        * Default value: None
        * Other values: None

* **Return Value**

    * Returning `true` indicates that adding a custom command was successful, returning `false` indicates failure
## command()

Call the defined custom command `command`

```php
Swoole\Server->command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true): false|string|array
```

!>Available in Swoole version >= `v4.8.0`. In `SWOOLE_PROCESS` and `SWOOLE_BASE` modes, this function can only be used in the `master` process.

* **Parameters**

    * `string $name`

        * Description: Name of the `command`
        * Default: None
        * Other: None

    * `int $process_id`

        * Description: Process ID
        * Default: None
        * Other: None

    * `int $process_type`

        * Description: Type of process request, only one of the following values can be chosen.
        * Default: None
        * Other values:
          * `SWOOLE_SERVER_COMMAND_MASTER` master process
          * `SWOOLE_SERVER_COMMAND_MANAGER` manager process
          * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` worker process
          * `SWOOLE_SERVER_COMMAND_TASK_WORKER` task process

    * `mixed $data`

        * Description: Data of the request, this data must be serializable
        * Default: None
        * Other: None

    * `bool $json_decode`

        * Description: Whether to decode using `json_decode`
        * Default: true
        * Other values: false

  * **Usage Example**
    ```php
    <?php
    use Swoole\Http\Server;
    use Swoole\Http\Request;
    use Swoole\Http\Response;

    $server = new Server('127.0.0.1', 9501, SWOOLE_BASE);
    $server->addCommand('test_getpid', SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_EVENT_WORKER,
        function ($server, $data) {
          var_dump($data);
          return json_encode(['pid' => posix_getpid()]);
        });
    $server->set([
        'log_file' => '/dev/null',
        'worker_num' => 2,
    ]);

    $server->on('start', function (Server $serv) {
        $result = $serv->command('test_getpid', 0, SWOOLE_SERVER_COMMAND_MASTER, ['type' => 'master']);
        Assert::eq($result['pid'], $serv->getMasterPid());
        $result = $serv->command('test_getpid', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::eq($result['pid'], $serv->getWorkerPid(1));
        $result = $serv->command('test_not_found', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::false($result);

        $serv->shutdown();
    });

    $server->on('request', function (Request $request, Response $response) {
    });
    $server->start();
    ```
