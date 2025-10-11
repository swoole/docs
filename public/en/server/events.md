# Events

This section will introduce all of Swoole's callback functions. Each callback function is a PHP function corresponding to an event.
## onStart

?> **This function is called in the main thread of the master process after the server starts**

```php
function onStart(Swoole\Server $server);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Function**: Swoole\Server object
      * **Default value**: None
      * **Other values**: None

* **Before this event, the `Server` has performed the following operations**
   
    * Manager processes are created upon startup
    * Worker processes are created upon startup
    * All TCP/UDP/Unix sockets are being listened to, but not accepting connections and requests yet
    * Timers are being listened to

* **Next steps to be executed**
   
    * The main [Reactor](/learn?id=reactor线程) starts receiving events, and clients can `connect` to the `Server`

**In the `onStart` callback, only `echo`, log printing, and process name modification are allowed. No other operations should be executed (server-related functions cannot be called at this point as the service is not yet ready). The `onWorkerStart` and `onStart` callbacks are executed in parallel in different processes, with no specific order.**

You can save the values of `$server->master_pid` and `$server->manager_pid` to a file in the `onStart` callback. This allows you to write a script that sends signals to these two `PID`s to implement shutdown and restart operations.

The `onStart` event is called in the main thread of the `Master` process.

!> Global resource objects created in `onStart` cannot be used in the `Worker` process because when `onStart` is called, the `Worker` process has already been created.  
The newly created object is within the main process, and the `Worker` process cannot access this memory area.  
Therefore, the code for creating global objects needs to be placed before `Server::start`; a typical example is [Swoole\Table](/memory/table?id=complete-example).

* **Security warning**

In the `onStart` callback, you can use asynchronous and coroutine APIs, but be aware that this may conflict with `dispatch_func` and `package_length_func`. **Do not use them simultaneously**.

Do not start a timer in `onStart`. If `Swoole\Server::shutdown()` is executed in the code, the program may not exit due to a timer always being in execution.

No client connections will be accepted by the server program until after the `return` statement in the `onStart` callback, so sync blocking functions can be safely used.

* **BASE mode**

In [SWOOLE_BASE](/learn?id=swoole_base) mode, there is no `master` process, so the `onStart` event does not exist. Do not use the `onStart` callback function in `BASE` mode.

```
WARNING swReactorProcess_start: The onStart event with SWOOLE_BASE is deprecated
```
## onBeforeShutdown

?> **This event occurs `before` the `Server` normal shutdown** 

!> Available in Swoole version >= `v4.8.0`. Coroutine API can be used in this event.

```php
function onBeforeShutdown(Swoole\Server $server);
```


* **Parameters**

    * **`Swoole\Server $server`**
        * **Description**：Swoole\Server object
        * **Default value**：N/A
        * **Other values**：N/A
## onShutdown

?> **This event occurs when the `Server` is shutting down normally**

```php
function onShutdown(Swoole\Server $server);
```

  * **Parameters**

    * **`Swoole\Server $server`**
      * **Function**: Swoole\Server object
      * **Default Value**: none
      * **Other values**: none

  * **`Swoole\Server` has performed the following actions before this event**

    * All Reactor threads, `HeartbeatCheck` thread, `UdpRecv` thread have been closed
    * All `Worker` processes, [Task processes](/learn?id=taskworker进程), [User processes](/server/methods?id=addprocess) have been closed
    * All `TCP/UDP/UnixSocket` listening ports have been closed
    * The main Reactor has been closed

  !> Forcibly killing the process will not trigger `onShutdown`, such as `kill -9`  
  Use `kill -15` to send the `SIGTERM` signal to the main process in order to terminate according to the normal process flow  
  Pressing `Ctrl+C` in the command line will immediately stop the program, and `onShutdown` will not be called at the lower level

  * **Notes**

  !> Do not call any asynchronous or coroutine-related `API` in `onShutdown`, as all event loop facilities have been destroyed when `onShutdown` is triggered;  
There is no longer a coroutine environment at this point. If developers need to use coroutine-related `API`, they need to manually call `Co\run` to create a [coroutine container](/coroutine?id=什么是协程容器).
## onWorkerStart

?> **This event occurs when the Worker process/ [Task process](/learn?id=taskworker-process) starts, objects created here can be used throughout the process lifecycle.**

```php
function onWorkerStart(Swoole\Server $server, int $workerId);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Function**: Swoole\Server object
      * **Default value**: N/A
      * **Other values**: N/A

    * **`int $workerId`**
      * **Function**: `Worker` process `id` (not PID of the process)
      * **Default value**: N/A
      * **Other values**: N/A

  * `onWorkerStart/onStart` are executed concurrently without a specific order
  * You can determine if the current process is a `Worker` process or a [Task process](/learn?id=taskworker-process) by checking the `$server->taskworker` attribute
  * When `worker_num` and `task_worker_num` are set to more than `1`, each process will trigger the `onWorkerStart` event once, and you can distinguish different working processes by checking the [$worker_id](/server/properties?id=worker_id)
  * Tasks sent from `worker` processes to `task` processes are handled by the `task` processes, and after all tasks are completed, the `worker` process is notified through the [onFinish](/server/events?id=onfinish) callback function. For example, when sending notification emails to hundreds of thousands of users in the background, the status of the operation displays as "sending" until all emails are sent, then the status automatically changes to "sent".

  The following example is used to rename the Worker process/ [Task process](/learn?id=taskworker-process).

```php
$server->on('WorkerStart', function ($server, $worker_id){
    global $argv;
    if($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});
```

  If you want to implement code reloading using the [Reload](/server/methods?id=reload) mechanism, you must `require` your business files in `onWorkerStart` instead of the file header. Files included before the `onWorkerStart` call will not be reloaded.

  You can place common, immutable PHP files before `onWorkerStart`. While code reloading is not possible, all Workers share this data, eliminating the need for additional memory to store this data.
Code after `onWorkerStart` needs to be stored in memory for each process

  * `$worker_id` represents the `ID` of this `Worker` process, with the range referred to in [$worker_id](/server/properties?id=worker_id)
  * [$worker_id](/server/properties?id=worker_id) is unrelated to the process `PID`, and `posix_getpid` function can be used to obtain the `PID`

  * **Coroutine Support**

    * Coroutines are automatically created in the `onWorkerStart` callback, so coroutine APIs can be called in `onWorkerStart`

  * **Note**

    !> If a fatal error occurs or `exit` is called in the code, the `Worker/Task` process will exit, and the manager process will create new processes. This may lead to a loop of continuously creating and destroying processes
## onWorkerStop

?> **This event occurs when the `Worker` process terminates. Resources allocated by the `Worker` process can be reclaimed in this function.**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
```

  * **Parameters**

    * **`Swoole\Server $server`**
      * **Description**: Swoole\Server object
      * **Default**: None
      * **Other values**: None

    * **`int $workerId`**
      * **Description**: `Worker` process `id` (not the process PID)
      * **Default**: None
      * **Other values**: None

  * **Note**

    !> -Processes that end abnormally, such as being forcefully `killed`, fatal errors, or `core dump`, will not execute the `onWorkerStop` callback function.  
    -Do not call any asynchronous or coroutine related `API` in `onWorkerStop`. The underlying event loop facilities have already been destroyed when `onWorkerStop` is triggered.
## onWorkerExit

?> Only valid when the [reload_async](/server/setting?id=reload_async) feature is enabled. See [How to restart the service correctly](/question/use?id=how-to-restart-the-service-correctly)

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
```

* **Parameters**

  * **`Swoole\Server $server`**
    * **Description**: Swoole\Server object.
    * **Default**: None
    * **Other values**: None

  * **`int $workerId`**
    * **Description**: `Worker` process ID (not PID of the process)
    * **Default**: None
    * **Other values**: None

* **Notes**

  !> - If the `Worker` process does not exit, `onWorkerExit` will continue to trigger.
  - `onWorkerExit` will be triggered within the `Worker` process; if there is an [event loop](/learn?id=what-is-eventloop) in the [Task process](/learn?id=taskworker-process), it will also trigger.
  - In `onWorkerExit`, try to remove/close asynchronous `Socket` connections as much as possible. Once the underlying system detects that the number of event listener handles in the [event loop](/learn?id=what-is-eventloop) is `0`, the process exits.
  - When there are no event handles listening in the process, this function will not be called upon process termination.
  - The `onWorkerStop` event callback will only be executed after the `Worker` process exits.
## onConnect

?> **Callback in the worker process when a new connection is established.**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Description**: Swoole\Server object
      * **Default**: None
      * **Other values**: None

    * **`int $fd`**
      * **Description**: File descriptor of the connection
      * **Default**: None
      * **Other values**: None

    * **`int $reactorId`**
      * **Description**: ID of the Reactor thread where the connection is
      * **Default**: None
      * **Other values**: None

  * **Note**

    !> `onConnect/onClose` callbacks occur in the Worker process, not the main process.  
    For the `UDP` protocol, there is only the [onReceive](/server/events?id=onreceive) event, no `onConnect/onClose` events.

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

      * In this mode, `onConnect/onReceive/onClose` may be delivered to different processes. Connection-related `PHP` object data cannot be initialized in the [onConnect](/server/events?id=onconnect) callback, and data cannot be cleaned up in [onClose](/server/events?id=onclose).
      * `onConnect/onReceive/onClose` may execute concurrently, which may lead to exceptions.
## onReceive

?> **This function is called back when data is received, in the `worker` process.**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Functionality**: Swoole\Server object
      * **Default value**: None
      * **Other values**: None

    * **`int $fd`**
      * **Functionality**: File descriptor of the connection
      * **Default value**: None
      * **Other values**: None

    * **`int $reactorId`**
      * **Functionality**: `ID` of the [Reactor](/learn?id=reactor-thread) thread where the TCP connection resides
      * **Default value**: None
      * **Other values**: None

    * **`string $data`**
      * **Functionality**: Data content received, may be text or binary content
      * **Default value**: None
      * **Other values**: None

  * **Regarding packet integrity under the `TCP` protocol, refer to [TCP packet boundary issue](/learn?id=tcp-packet-boundary-issue)**

    * Using configured settings such as `open_eof_check/open_length_check/open_http_protocol` provided by the underlying layer can ensure the integrity of data packets
    * Without using underlying protocol processing, analyze data, merge/split data packets in PHP code after [onReceive](/server/events?id=onreceive) event.

    For example: In the code, you can add a `$buffer = array()`, use `$fd` as a `key` to store context data. Each time data is received, concatenate strings, `$buffer[$fd] .= $data`, and then check if `$buffer[$fd]` string forms a complete data packet.

    By default, the same `fd` will be allocated to the same `Worker`, so data can be concatenated. When using `dispatch_mode = 3`, request data is preemptive, and data from the same `fd` might be assigned to different processes, thus the previously mentioned data packet concatenation method cannot be used.

  * **Multiple port listening, refer to [this section](/server/port)**

    When the main server has protocols set, additional ports listened to will inherit the settings of the main server by default. You need to explicitly call the `set` method to reset the protocol for the port.    

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9501);
    $port2 = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $port2->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        echo "[#".$server->worker_id."]\tClient[$fd]: $data\n";
    });
    ```

    Although the `on` method is called to register the callback function [onReceive](/server/events?id=onreceive), since the `set` method is not called to override the main server's protocol, the newly listened port `9502` still uses the `HTTP` protocol. When connecting to port `9502` with a `telnet` client and sending a string, the server won't trigger the [onReceive](/server/events?id=onreceive) event.

  * **Note**

    !> Without enabling automatic protocol selection, the maximum data received in a single [onReceive](/server/events?id=onreceive) event is `64K`  
    With automatic protocol handling enabled, [onReceive](/server/events?id=onreceive) will receive complete data packets, with a maximum length not exceeding [package_max_length](/server/setting?id=package_max_length)  
    Binary format is supported, `$data` might be binary data
## onPacket

?> **Callback this function when receiving `UDP` data packets, it occurs in the `worker` process.**

```php
function onPacket(Swoole\Server $server, string $data, array $clientInfo);
```

  * **Parameters**

    * **`Swoole\Server $server`**
      * **Function**: Swoole\Server object
      * **Default value**: none
      * **Other values**: none

    * **`string $data`**
      * **Function**: The received data content, which can be text or binary content
      * **Default value**: none
      * **Other values**: none

    * **`array $clientInfo`**
      * **Function**: Client information including `address/port/server_socket` and other client information data, [see UDP server](/start/start_udp_server)
      * **Default value**: none
      * **Other values**: none

  * **Note**

    !> When the server listens on both `TCP/UDP` ports, receiving data of the `TCP` protocol will trigger [onReceive](/server/events?id=onreceive) callback; receiving `UDP` data packets will trigger the `onPacket` callback. The automatic protocol handling set by the server, such as `EOF` or `Length`, ([see TCP data packet boundary problem](/learn?id=tcp数据包边界问题)), is invalid for the `UDP` port because `UDP` packets themselves have message boundaries and do not require additional protocol processing.
## onClose

?> **This function is called in the `Worker` process after a `TCP` client connection is closed.**

```php
function onClose(Swoole\Server $server, int $fd, int $reactorId);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Description**: Swoole\Server object
      * **Default**: None
      * **Other values**: None

    * **`int $fd`**
      * **Description**: File descriptor of the connection
      * **Default**: None
      * **Other values**: None

    * **`int $reactorId`**
      * **Description**: From which `reactor` thread, negative for actively closed by `close`
      * **Default**: None
      * **Other values**: None

  * **Tips**

    * **Active Closing**

      * When the server actively closes the connection, this parameter will be set to `-1`, which can be used to distinguish whether the close is initiated by the server or the client by checking `$reactorId < 0`.
      * Only actively calling the `close` method in PHP code is considered as active closing.

    * **Heartbeat Detection**

      * [Heartbeat detection](/server/setting?id=heartbeat_check_interval) is notified to close by the heartbeat detection thread. When closed, the `$reactorId` parameter in [onClose](/server/events?id=onclose) is not `-1`.

  * **Note**

    !> If a fatal error occurs in the [onClose](/server/events?id=onclose) callback function, it will cause connection leaks. Using the `netstat` command will show a large number of `CLOSE_WAIT` state `TCP` connections.
    -Whether `close` is initiated by the client or the server actively calls `$server->close()` to close the connection, this event will be triggered. Therefore, as long as the connection is closed, this function will always be called.  
    -[getClientInfo](/server/methods?id=getClientInfo) method can still be called in [onClose](/server/events?id=onclose) to obtain connection information. The `TCP` connection will be closed only after the execution of [onClose](/server/events?id=onclose) callback function is completed.  
    -When [onClose](/server/events?id=onclose) is called here, it means that the client connection has been closed, so there is no need to execute `$server->close($fd)`. Executing `$server->close($fd)` in the code will throw a PHP error warning.
## onTask

?> **Called inside the `task` process. The `worker` process can use the [task](/server/methods?id=task) function to deliver new tasks to the `task_worker` process. The current [Task process](/learn?id=taskworker-process) switches to busy state when calling the [onTask](/server/events?id=ontask) callback function, meaning it will no longer receive new tasks. When the [onTask](/server/events?id=ontask) function returns, the process switches to idle state to continue receiving new tasks.**

```php
function onTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Description**: Swoole\Server object
      * **Default value**: None
      * **Other values**: None

    * **`int $task_id`**
      * **Description**: ID of the `task` process executing the task. (`$task_id` and `$src_worker_id` combined are globally unique, tasks delivered by different `worker` processes may have the same `ID`)
      * **Default value**: None
      * **Other values**: None

    * **`int $src_worker_id`**
      * **Description**: ID of the `worker` process delivering the task. (`$task_id` and `$src_worker_id` combined are globally unique, tasks delivered by different `worker` processes may have the same `ID`)
      * **Default value**: None
      * **Other values**: None

    * **`mixed $data`**
      * **Description**: Data content of the task
      * **Default value**: None
      * **Other values**: None

  * **Tips**

    * **Starting from v4.2.12, if [task_enable_coroutine](/server/setting?id=task_enable_coroutine) is enabled, the callback function prototype is**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); // Completes the task, ends it, and returns data
      });
      ```

    * **Return the execution result to the `worker` process**
    
      * **In the [onTask](/server/events?id=ontask) function, `return` a string to send this content back to the `worker` process. This will trigger the [onFinish](/server/events?id=onfinish) function in the `worker` process, indicating that the delivered `task` has been completed. Alternatively, you can use `Swoole\Server->finish()` to trigger the [onFinish](/server/events?id=onfinish) function without needing to `return`.**

      * The variable returned can be any non-`null` `PHP` variable.

  * **Note**

    !> If an error occurs and causes the [onTask](/server/events?id=ontask) function to exit fatally, or if it is forcibly killed by an external process, the current task will be discarded without affecting other tasks in the queue.
## onFinish

?> **This callback function is triggered in the worker process when the task initiated by the worker process is completed in the task process. The [task process](/learn?id=taskworker-process) sends the result of the task processing to the worker process via the `Swoole\Server->finish()` method.**

```php
function onFinish(Swoole\Server $server, int $task_id, mixed $data)
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Function** : Swoole\Server object
      * **Default Value** : N/A
      * **Other Values** : N/A

    * **`int $task_id`**
      * **Function** : The ID of the `task` process executing the task
      * **Default Value** : N/A
      * **Other Values** : N/A

    * **`mixed $data`**
      * **Function** : The result content of the task processing
      * **Default Value** : N/A
      * **Other Values** : N/A

  * **Note**

    !> - If the `finish` method is not called or no result is returned in the [onTask](/server/events?id=ontask) event of the [task process](/learn?id=taskworker-process), the [onFinish](/server/events?id=onfinish) event will not be triggered in the worker process.  
    - The worker process executing the logic of [onFinish](/server/events?id=onfinish) and the worker process issuing the `task` tasks are the same process.
## onPipeMessage

When a working process receives a message sent by `$server->sendMessage()` trigger the `onPipeMessage` event. Both `worker` and `task` processes may trigger the `onPipeMessage` event.

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Function**：Swoole\Server object
      * **Default Value**：None
      * **Other values**：None

    * **`int $src_worker_id`**
      * **Function**：From which `Worker` process the message comes
      * **Default Value**：None
      * **Other values**：None

    * **`mixed $message`**
      * **Function**：Message content, can be any PHP type
      * **Default Value**：None
      * **Other values**：None
## onWorkerError

?> **This function will be called in the Manager process when a Worker/Task process encounters an exception.**

!> This function is mainly used for alerting and monitoring. Once an Worker process exits abnormally, it may be due to a fatal error or a process core dump. Record logs or send alert messages to prompt developers to take appropriate actions.

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

  * **Parameters** 

    * **`Swoole\Server $server`**
      * **Functionality**: Swoole\Server object
      * **Default Value**: None
      * **Other Values**: None

    * **`int $worker_id`**
      * **Functionality**: ID of the exception worker process
      * **Default Value**: None
      * **Other Values**: None

    * **`int $worker_pid`**
      * **Functionality**: PID of the exception worker process
      * **Default Value**: None
      * **Other Values**: None

    * **`int $exit_code`**
      * **Functionality**: Exit status code, ranging from `0` to `255`
      * **Default Value**: None
      * **Other Values**: None

    * **`int $signal`**
      * **Functionality**: Signal of the process exit
      * **Default Value**: None
      * **Other Values**: None

  * **Common Errors**

    * `signal = 11`: Indicates that the Worker process encountered a `segment fault` error, which might trigger a underlying `BUG`. Please collect `core dump` information and `valgrind` memory detection logs, [report this issue to the Swoole development team](/other/issue).
    * `exit_code = 255`: Indicates a `Fatal Error` occurred in the Worker process. Please check the PHP error log, identify the problematic PHP code, and resolve it.
    * `signal = 9`: Indicates the `Worker` was forcefully `Killed` by the system. Check if there was a manual `kill -9` operation. Also, check if there is an `OOM (Out of memory)` in the `dmesg` information.
    * If there is an `OOM`, it may be due to excessive memory allocation. 1. Check the `Server`'s `setting` configuration to see if [socket_buffer_size](/server/setting?id=socket_buffer_size) allocation is too large; 2. Check if a very large [Swoole\Table](/memory/table) memory module was created.
## onManagerStart

?> **This event is triggered when the manager process starts**

```php
function onManagerStart(Swoole\Server $server);
```

  * **Tips**

    * You can modify the manager process's name in this callback function.
    * Before version `4.2.12`, timers could not be added to the `manager` process, tasks could not be dispatched, and coroutines could not be used.
    * Starting from version `4.2.12`, the `manager` process can use timers implemented based on signals for synchronization.
    * The `manager` process can call the [sendMessage](/server/methods?id=sendMessage) interface to send messages to other worker processes.

    * **Startup Sequence**

      * `Task` and `Worker` processes have been created.
      * The status of the `Master` process is unknown because `Manager` and `Master` run in parallel, and the readiness of the `Master` process cannot be confirmed when the `onManagerStart` callback occurs.

    * **BASE Mode**

      * In [SWOOLE_BASE](/learn?id=swoole_base) mode, if the `worker_num`, `max_request`, and `task_worker_num` parameters are set, the underlying system will create a `manager` process to manage worker processes. Consequently, the `onManagerStart` and `onManagerStop` event callbacks will be triggered.
## onManagerStop

?> **Triggered when the manager process ends**

```php
function onManagerStop(Swoole\Server $server);
```

 * **Note**

  * When `onManagerStop` is triggered, it indicates that the `Task` and `Worker` processes have already ended their execution and have been reclaimed by the `Manager` process.
## onBeforeReload

?> This event is triggered before the `Reload` of the Worker process, and the callback is in the Manager process.

```php
function onBeforeReload(Swoole\Server $server);
```

  * **Parameters**

    * **`Swoole\Server $server`**
      * **Description**: Swoole\Server object
      * **Default value**: None
      * **Other values**: None
## onAfterReload

?> This event is triggered after the Worker process `Reload`, and the callback is in the Manager process.

```php
function onAfterReload(Swoole\Server $server);
```

  * **Parameters**

    * **`Swoole\Server $server`**
      * **Description**: Swoole\Server object
      * **Default**: N/A
      * **Other values**: N/A
## Event Execution Order

* All event callbacks occur after `$server->start`.
* The last event when the server shutdown program terminates is `onShutdown`.
* After the server starts successfully, `onStart/onManagerStart/onWorkerStart` will be executed concurrently in different processes.
* `onReceive/onConnect/onClose` are triggered in the `Worker` process.
* When `Worker/Task` processes start/stop, `onWorkerStart/onWorkerStop` is called respectively.
* The `onTask` event only occurs in the [task process](/learn?id=taskworker-process).
* The `onFinish` event only occurs in the `Worker` process.
* The execution order of the `onStart/onManagerStart/onWorkerStart` events is indeterminate.
## Object-oriented Style

When [event_object](/server/setting?id=event_object) is enabled, the following event callbacks will use object-oriented style.
#### [Swoole\Server\Event](/server/event_class)

* [onConnect](/server/events?id=onconnect)
* [onReceive](/server/events?id=onreceive)
* [onClose](/server/events?id=onclose)

```php
$server->on('Connect', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});

$server->on('Receive', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});

$server->on('Close', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```
#### [Swoole\Server\Packet](/server/packet_class)

* [onPacket](/server/events?id=onpacket)

```php
$server->on('Packet', function (Swoole\Server $serv, Swoole\Server\Packet $object) {
    var_dump($object);
});
```
#### [Swoole\Server\PipeMessage](/server/pipemessage_class)

* [onPipeMessage](/server/events?id=onpipemessage)

```php
$server->on('PipeMessage', function (Swoole\Server $serv, Swoole\Server\PipeMessage $msg) {
    var_dump($msg);
    $object = $msg->data;
    $serv->sendto($object->address, $object->port, $object->data, $object->server_socket);
});
```
#### [Swoole\Server\StatusInfo](/server/statusinfo_class)

* [onWorkerError](/server/events?id=onworkererror)

```php
$serv->on('WorkerError', function (Swoole\Server $serv, Swoole\Server\StatusInfo $info) {
    var_dump($info);
});
```
The `onTask` event occurs when a task is received by the server. In the provided PHP code snippet, the `onTask` event is being registered using an anonymous function that takes two parameters - an instance of `Swoole\Server` and an instance of `Swoole\Server\Task`. The `var_dump` function is used to output information about the task being processed by the server.
#### [Swoole\Server\TaskResult](/server/taskresult_class)

* [onFinish](/server/events?id=onfinish)

```php
$server->on('Finish', function (Swoole\Server $serv, Swoole\Server\TaskResult $result) {
    var_dump($result);
});
```
