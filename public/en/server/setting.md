# Configuration

The [Swoole\Server->set()](/server/methods?id=set) function is used to set various parameters for the `Server` during runtime. All subpages in this section are elements of the configuration array.

!> Starting from version [v4.5.5](/version/log?id=v455), the underlying system will check if the configured options are correct. If a configuration item that is not provided by `Swoole` is set, a Warning will be generated.

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```
### debug_mode

?> Set the logging mode to `debug` for debugging purposes. This only takes effect if `--enable-debug` is enabled during compilation.

```php
$server->set([
  'debug_mode' => true
])
```
### trace_flags

Setting the tags for trace logs, only print partial trace logs. `trace_flags` supports setting multiple trace items using the `|` OR operator, which only takes effect if `--enable-trace-log` is turned on during compilation.

The following trace items are supported at the underlying level, and `SWOOLE_TRACE_ALL` can be used to trace all items:

- `SWOOLE_TRACE_SERVER`
- `SWOOLE_TRACE_CLIENT`
- `SWOOLE_TRACE_BUFFER`
- `SWOOLE_TRACE_CONN`
- `SWOOLE_TRACE_EVENT`
- `SWOOLE_TRACE_WORKER`
- `SWOOLE_TRACE_REACTOR`
- `SWOOLE_TRACE_PHP`
- `SWOOLE_TRACE_HTTP2`
- `SWOOLE_TRACE_EOF_PROTOCOL`
- `SWOOLE_TRACE_LENGTH_PROTOCOL`
- `SWOOLE_TRACE_CLOSE`
- `SWOOLE_TRACE_HTTP_CLIENT`
- `SWOOLE_TRACE_COROUTINE`
- `SWOOLE_TRACE_REDIS_CLIENT`
- `SWOOLE_TRACE_MYSQL_CLIENT`
- `SWOOLE_TRACE_AIO`
- `SWOOLE_TRACE_ALL`
### log_file

Specify the `Swoole` error log file.

Exceptions that occur during the running of `Swoole` will be recorded in this file, by default they will also be printed to the screen.  
After enabling daemon mode `(daemonize => true)`, standard output will be redirected to the `log_file`. Any content printed to the screen in PHP code using `echo/var_dump/print`, etc., will be written to the `log_file`.

  * **Tips**

    * The logs in the `log_file` are only for recording runtime errors and do not need to be stored permanently.

    * **Log Markers**

      In the log information, some markers will be added before the process ID, indicating the type of thread/process that generated the log.

        * `#` Master process
        * `$` Manager process
        * `*` Worker process
        * `^` Task process

    * **Reopen Log File**

      If the log file is moved with `mv` or deleted with `unlink` during the server program's runtime, the log information will fail to be written correctly. In this case, you can send a `SIGRTMIN` signal to the `Server` to reopen the log file.

      * Supports only the Linux platform
      * Does not support [UserProcess](/server/methods?id=addProcess) processes

  * **Note**

    The `log_file` will not automatically split files, so it needs to be cleaned regularly. By observing the output of the `log_file`, you can obtain various types of server exception information and warnings.
### log_level

?> **Set the level of `Server` error log printing, ranging from `0-6`. Log information below the `log_level` set will not be thrown.**【Default value: `SWOOLE_LOG_INFO`】

Corresponding level constants refer to [Log Level](/consts?id=Log Level)

  * **Note**

    !> `SWOOLE_LOG_DEBUG` and `SWOOLE_LOG_TRACE` are only available when compiling with [--enable-debug-log](/environment?id=debug parameters) and [--enable-trace-log](/environment?id=debug parameters) versions;  
    When `daemonize` is enabled, the underlying system will write all screen output in the program to [log_file](/server/setting?id=log_file), and this part of content is not controlled by `log_level`.
### log_date_format

?> **Set the `Server` log time format**, format reference to [strftime](https://www.php.net/manual/zh/function.strftime.php)'s `format`

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```
### log_date_with_microseconds

Setting for the precision of the `Server` log, whether to include microseconds. **Default Value: `false`**
### log_rotation

?> **Set `Server` log rotation** [Default value: `SWOOLE_LOG_ROTATION_SINGLE`]

| Constant                        | Description | Version  |
| ------------------------------- | ----------- | -------- |
| SWOOLE_LOG_ROTATION_SINGLE      | Not enabled | -        |
| SWOOLE_LOG_ROTATION_MONTHLY     | Monthly     | v4.5.8   |
| SWOOLE_LOG_ROTATION_DAILY       | Daily       | v4.5.2   |
| SWOOLE_LOG_ROTATION_HOURLY      | Hourly      | v4.5.8   |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE| Every minute| v4.5.8   |
### display_errors

?> Turn on/off error messages for `Swoole`.

```php
$server->set([
  'display_errors' => true
])
```
### dns_server

?> Set the `IP` address for `dns` query.
### socket_dns_timeout

?> Domain name resolution timeout. If the coroutines client is enabled on the server side, this parameter can control the domain name resolution timeout of the client, in seconds.
### socket_connect_timeout

?> Client connection timeout, if the server enables coroutine client, this parameter can control the client's connection timeout, in seconds.
### socket_write_timeout / socket_send_timeout

?> Client write timeout, if you enable coroutine client on the server side, this parameter can control the client's write timeout time, the unit is in seconds.  
This configuration can also be used to control the execution timeout of `shell_exec` after `coroutines` or [Swoole\Coroutine\System::exec()](/coroutine/system?id=exec).
### socket_read_timeout / socket_recv_timeout

?> Client read timeout, if coroutine client is enabled on the server, this parameter can control the client's read timeout, in seconds.
### max_coroutine / max_coro_num :id=max_coroutine

?> **Set the maximum number of coroutines for the current working process.** *(Default: `100000`, default value is `3000` for Swoole version below `v4.4.0-beta`)*

?> If exceeded, the underlying layer will be unable to create new coroutines. The Swoole server will throw an `exceed max number of coroutine` error, `TCP Server` will directly close the connection, and `Http Server` will return an HTTP status code of 503.

?> In a `Server` program, the actual maximum number of coroutines that can be created is equal to `worker_num * max_coroutine`, with the coroutine count handled separately for task processes and UserProcess processes.

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```
### enable_deadlock_check

?> Enable coroutine deadlock detection.

```php
$server->set([
  'enable_deadlock_check' => true
]);
```
### hook_flags

?> **Set the function scope for `Coroutine Hook`.** 【Default value: Do not hook】

!> Swoole version `v4.5+` or [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) is available, for more details please refer to [Coroutine](/runtime)

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
The underlying supports the following coroutine items, using `SWOOLE_HOOK_ALL` to indicate all coroutines:

* `SWOOLE_HOOK_TCP`
* `SWOOLE_HOOK_UNIX`
* `SWOOLE_HOOK_UDP`
* `SWOOLE_HOOK_UDG`
* `SWOOLE_HOOK_SSL`
* `SWOOLE_HOOK_TLS`
* `SWOOLE_HOOK_SLEEP`
* `SWOOLE_HOOK_FILE`
* `SWOOLE_HOOK_STREAM_FUNCTION`
* `SWOOLE_HOOK_BLOCKING_FUNCTION`
* `SWOOLE_HOOK_PROC`
* `SWOOLE_HOOK_CURL`
* `SWOOLE_HOOK_NATIVE_CURL`
* `SWOOLE_HOOK_SOCKETS`
* `SWOOLE_HOOK_STDIO`
* `SWOOLE_HOOK_PDO_PGSQL`
* `SWOOLE_HOOK_PDO_ODBC`
* `SWOOLE_HOOK_PDO_ORACLE`
* `SWOOLE_HOOK_PDO_SQLITE`
* `SWOOLE_HOOK_ALL`
### enable_preemptive_scheduler

?> Enable preemptive scheduling of coroutines to prevent starvation of other coroutines when one coroutine takes too long to execute. The maximum execution time for a coroutine is `10ms`.

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```
### c_stack_size / stack_size

?> Set the memory size of the initial C stack for a single coroutine, which is 2M by default.
### aio_core_worker_num

?> Set the minimum number of `AIO` working threads, with a default value of the number of CPUs.
### aio_worker_num

?> Set the maximum number of `AIO` working threads, default value is the number of `cpu` cores * 8.
### aio_max_wait_time

?> Maximum time for worker thread to wait for a task, in seconds.
### aio_max_idle_time

?> Maximum idle time of the worker thread, in seconds.
### reactor_num

?> **Set the number of [Reactor](/learn?id=reactor-thread) threads to start.**【Default value: `CPU` cores】

?> Adjust the number of event processing threads within the main process to make full use of multiple cores. By default, the same number of `CPU` cores will be enabled.  
The `Reactor` threads can utilize multiple cores, for example: if the machine has `128` cores, then `128` threads will be started at the lower level.  
Each thread will maintain an [EventLoop](/learn?id=what-is-eventloop). The threads are lockless, and instructions can be executed in parallel by the `128` core `CPU`.  
Considering that there is a certain performance loss in operating system scheduling, it can be set to CPU cores * 2 in order to maximize the utilization of each CPU core.

  * **Tips**

    * It is recommended to set `reactor_num` to `1-4` times the number of CPU cores.
    * `reactor_num` should not exceed [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 4.

  * **Note**

  !> -`reactor_num` must be less than or equal to `worker_num`;  
-If the set `reactor_num` is greater than `worker_num`, it will automatically adjust to make `reactor_num` equal to `worker_num`;  
-On machines with more than `8` cores, the default setting of `reactor_num` is `8`.
### worker_num

?> **Set the number of `Worker` processes to start.** 【Default: `CPU` cores】

?> If one request takes `100ms` to process and you want to provide a processing capacity of `1000 QPS`, then there must be `100` processes or more configured.  
However, the more processes that are started, the significantly more memory will be occupied, and the overhead of process switching will increase. Therefore, set it appropriately here. Do not configure it too large.

  * **Tips**

    * If the business code is fully [asynchronous I/O](/learn?id=sync-io-vs-async-io), setting this to `1-4` times the number of CPU cores is the most reasonable.
    * If the business code is [synchronous I/O](/learn?id=sync-io-vs-async-io), it needs to be adjusted based on request response times and system load, for example: `100-500`.
    * Default setting is [swoole_cpu_num()](/functions?id=swoole_cpu_num), with a maximum not to exceed [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000.
    * Assuming each process consumes `40M` memory, `100` processes will require `4G` memory.
### max_request

?> **Set the maximum number of tasks for the `worker` process.** [Default value: `0` means the process will not exit]

?> A `worker` process will automatically exit after handling more tasks than this value, releasing all memory and resources when it exits.

!> The main purpose of this parameter is to solve PHP process memory leaks caused by improper programming. If a PHP application has slow memory leaks that cannot be identified or resolved, you can temporarily solve it by setting `max_request`. It is necessary to find and fix the memory leak code instead of relying on this solution, or you can use Swoole Tracker to discover the leaking code.

  * **Tips**

    * Reaching `max_request` does not immediately close the process; refer to [max_wait_time](/server/setting?id=max_wait_time).
    * Under [SWOOLE_BASE](/learn?id=swoole_base), restarting the process when reaching `max_request` can cause client connections to disconnect.

  !> When a fatal error occurs within the `worker` process or `exit` is manually executed, the process will exit automatically. The `master` process will start a new `worker` process to continue handling requests.
### max_conn / max_connection

?> **Server program, maximum allowed number of connections.**【Default value: `ulimit -n`】

?> For example, if `max_connection => 10000`, this parameter is used to set how many TCP connections the `Server` can maintain at most. When this number is exceeded, new incoming connections will be rejected.

  * **Hint**
  
    * **Default settings**
    
      * If the application layer does not set `max_connection`, the underlying layer will use the value of `ulimit -n` as the default setting.
      * Starting from version `4.2.9`, if the underlying layer detects that `ulimit -n` exceeds `100000`, it will default it to `100000`. This is because some systems set `ulimit -n` to `1 million`, requiring a large amount of memory allocation, which leads to startup failures.

    * **Maximum limit**
    
      * Do not set `max_connection` to be over `1M`.

    * **Minimum setting**
    
      * If this option is set too small, the underlying layer will throw an error and set it to the value of `ulimit -n`.
      * The minimum value is `(worker_num + task_worker_num) * 2 + 32`.

    ```shell
    serv->max_connection is too small.
    ```

    * **Memory usage**
    
      * Do not adjust the `max_connection` parameter too large. Set it based on the actual memory situation of the machine. `Swoole` will allocate a large memory block to store `Connection` information based on this value. The `Connection` information of a TCP connection requires `224` bytes.

  * **Caution**
  
  !> The `max_connection` must not exceed the value of the operating system's `ulimit -n`, otherwise a warning message will be reported, and it will be reset to the value of `ulimit -n`.

  ```shell
  WARN swServer_start_check: serv->max_conn is exceed the maximum value[100000].
  
  WARNING set_max_connection: max_connection is exceed the maximum value, it's reset to 10240
  ```
### task_worker_num

Configure the number of [Task processes](/learn?id=taskworker-process) to run.

By setting this parameter, the `task` functionality will be enabled. Therefore, the `Server` must register the [onTask](/server/events?id=ontask) and [onFinish](/server/events?id=onfinish) event callback functions. If these are not registered, the server program will fail to start.

  * **Tips**

    * [Task processes](/learn?id=taskworker-process) are synchronous and blocking.
    
    * The maximum value should not exceed [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000.
    
    * **Calculation**
    
      * If each `task` takes `100ms` to process, then one process can handle `1/0.1=10` tasks in one second.
      
      * If `2000` tasks are created per second.
      
      * `2000/10=200`, therefore set `task_worker_num => 200` to enable `200` Task processes.

  * **Note**
  
    !> - The `Swoole\Server->task` method cannot be used within [Task processes](/learn?id=taskworker-process).
### task_ipc_mode

?> **Set the method of communication between the [Task worker](/learn?id=taskworker-process) and the `Worker` processes.**【Default value: `1`】 

?> Please read about [IPC communication under Swoole](/learn?id=what-is-ipc) first.

Mode | Description
---|---
1 | Use `Unix Socket` communication【Default mode】
2 | Use `sysvmsg` message queue communication
3 | Use `sysvmsg` message queue communication and set to grab mode

  * **Tips**

    * **Mode `1`**
      * When using mode `1`, it supports targeted delivery. You can use `dst_worker_id` in the [task](/server/methods?id=task) and [taskwait](/server/methods?id=taskwait) methods to specify the target `Task worker`.
      * When `dst_worker_id` is set to `-1`, the underlying system will determine the status of each [Task worker] and deliver tasks to the currently idle worker.

    * **Modes `2`, `3`**
      * Message queue mode uses memory queues provided by the operating system to store data. If the `message_queue_key` is not specified, a private queue will be used, which will be deleted after the `Server` program terminates.
      * If a message queue `Key` is specified, the data in the message queue will not be deleted after the `Server` program terminates, allowing processes to retrieve the data even after a restart.
      * You can manually delete message queue data using `ipcrm -q` with the message queue `ID`.
      * The difference between `Mode 2` and `Mode 3` is that `Mode 2` supports targeted delivery. `$serv->task($data, $task_worker_id)` can specify which [Task worker] to deliver to. `Mode 3` is a complete grab mode where the [Task workers] will grab tasks from the queue, making targeted delivery impossible. Even if `$task_worker_id` is specified, it will be ignored under `Mode 3`.

  * **Note**

    !> - Mode 3 will affect the [sendMessage](/server/methods?id=sendmessage) method, causing messages sent by [sendMessage](/server/methods?id=sendmessage) to be randomly picked up by a specific [Task worker].
    - When using message queue communication, if the processing capability of the `Task worker` is lower than the delivery speed, it may cause the `Worker` processes to block.
    - After using message queue communication, task workers cannot support coroutines (enabling [task_enable_coroutine](/server/setting?id=task_enable_coroutine)).
### task_max_request

?> **Set the maximum number of tasks for [task processes](/learn?id=taskworker-process).** 【Default value: `0`】

Set the maximum number of tasks for task processes. A task process will automatically exit after processing more tasks than this number. This parameter is to prevent PHP process memory overflow. If you do not want the process to exit automatically, you can set it to 0.
### task_tmpdir

?> **Set the temporary directory for task data.** 【Default value: Linux `/tmp` directory】

?> In `Server`, if the submitted data exceeds `8180` bytes, a temporary file will be used to store the data. The `task_tmpdir` is used to set the location for storing temporary files.

  * **Tips**

    * By default, the underlying system uses the `/tmp` directory to store `task` data. If your Linux kernel version is too low and the `/tmp` directory is not a memory file system, you can set it to `/dev/shm/`.
    * If the `task_tmpdir` directory does not exist, the underlying system will attempt to create it automatically.

  * **Note**

    !> If creation fails, `Server->start` will fail.
### task_enable_coroutine

?> **Enable `Task` coroutine support.** 【Default value: `false`】, supported since v4.2.12

?> When enabled, automatically creates coroutines and a coroutine scheduler in the [onTask](/server/events?id=ontask) callback, allowing PHP code to directly use the coroutine `API`.

  * **Example**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    // Which Worker process the task came from
    $task->worker_id;
    // Task ID
    $task->id;
    // Task type, taskwait, task, taskCo, taskWaitMulti may use different flags
    $task->flags;
    // Task data
    $task->data;
    // Delivery time, added in v4.6.0
    $task->dispatch_time;
    // Coroutine API
    co::sleep(0.2);
    // Complete the task, finish and return data
    $task->finish([123, 'hello']);
});
```

  * **Note**

    !> - `task_enable_coroutine` can only be used when [enable_coroutine](/server/setting?id=enable_coroutine) is `true`  
    - When `task_enable_coroutine` is enabled, `Task` worker processes support coroutines  
    - When `task_enable_coroutine` is not enabled, only synchronous blocking is supported
### task_use_object/task_object :id=task_use_object

?> **Task callback format using object-oriented style.** 【Default value: `false`】

?> Set to `true`, the [onTask](/server/events?id=ontask) callback will switch to object mode.

  * **Example**

```php
<?php

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 3,
    'task_use_object' => true,
//    'task_object' => true, // Alias added in version 4.6.0
]);
$server->on('receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd' => $fd,]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    // Here $task is an instance of Swoole\Server\Task object
    $server->send($task->data['fd'], json_encode($server->stats()));
});
$server->start();
```
### dispatch_mode

?> **Packet distribution strategy.** 【Default value: `2`】

Mode | Mode | Function
---|---|---
1 | Round robin mode | Each `Worker` process will be allocated sequentially for every received connection
2 | Fixed mode | Allocate `Worker` based on the connection's file descriptor. This ensures that data from the same connection will be processed by the same `Worker` only
3 | Preemptive mode | The main process will choose delivery based on the workload status of the `Worker`, delivering only to idle `Workers`
4 | IP hash | Allocate based on client `IP` using modulo `hash`, assigning to a specific `Worker` process.<br>This ensures that data from the same source IP connection will always be assigned to the same `Worker` process. Algorithm: `inet_addr_mod(ClientIP, worker_num)`
5 | UID hash | Requires binding a connection to a unique `uid` by calling [Server->bind()](/server/methods?id=bind) in the user code. Then, the underlying system allocates to different `Worker` processes based on the value of `UID`.<br>Algorithm: `UID % worker_num`. To use strings as `UID`, you can use `crc32(UID_STRING)`
7 | Stream mode | Idle `Workers` will `accept` connections and handle new requests from the [Reactor](/learn?id=reactor-thread)

  * **Tips**

    * **Recommendations**
    
      * Stateless servers can use `1` or `3`, synchronous blocking servers use `3`, and asynchronous non-blocking servers use `1`
      * Stateful servers use `2`, `4`, `5`
      
    * **UDP Protocol**

      * For `dispatch_mode=2/4/5`, fixed allocation is employed, hashing client `IP` to different `Worker` processes
      * For `dispatch_mode=1/3`, random allocation to different `Worker` processes
      * `inet_addr_mod` function

```
    function inet_addr_mod($ip, $worker_num) {
        $ip_parts = explode('.', $ip);
        if (count($ip_parts) != 4) {
            return false;
        }
        $ip_parts = array_reverse($ip_parts);
    
        $ip_long = 0;
        foreach ($ip_parts as $part) {
            $ip_long <<= 8;
            $ip_long |= (int) $part;
        }
    
        return $ip_long % $worker_num;
    }
```

    * **BASE Mode**

      * The `dispatch_mode` configuration is ineffective in [SWOOLE_BASE](/learn?id=swoole_base) mode because `BASE` does not involve task dispatching. Upon receiving data from a client, it immediately triggers the [onReceive](/server/events?id=onreceive) callback in the current thread/process without the need to dispatch to a `Worker` process.

  * **Note**

    !> - When `dispatch_mode=1/3`, the `onConnect/onClose` events will be disabled at the underlying level, as these modes do not guarantee the order of `onConnect/onClose/onReceive`.
    - For non-request-response server programs, do not use modes `1` or `3`. For example, for an HTTP service (request-response), you can use `1` or `3`, but for TCP persistent connections, `1` or `3` should not be used.
### dispatch_func

?> Set the `dispatch` function. `Swoole` has built-in `6` types of [dispatch_mode](/server/setting?id=dispatch_mode). If they cannot meet your needs, you can write a `C++` function or `PHP` function to implement the `dispatch` logic.

  * **Usage**

```php
$server->set(array(
  'dispatch_func' => 'my_dispatch_function',
));
```

  * **Tips**

    * After setting `dispatch_func`, the underlying layer will automatically ignore the `dispatch_mode` configuration.
    * If the function corresponding to `dispatch_func` does not exist, a fatal error will be thrown by the underlying layer.
    * If you need to dispatch a package larger than 8K, `dispatch_func` can only get content from `0-8180` bytes.

  * **Write PHP Function**

    ?> Since `ZendVM` does not support a multi-threaded environment, even if multiple [Reactor](/learn?id=reactor线程) threads are set up, only one `dispatch_func` can be executed at any given time. Therefore, the underlying layer will lock when executing this PHP function, which may lead to contention issues with the lock. Do not perform any blocking operations in `dispatch_func`, as it may cause the `Reactor` thread group to stop working.

    ```php
    $server->set(array(
        'dispatch_func' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd` is a unique identifier for the client connection and can use `Server::getClientInfo` to retrieve connection information.
    * `$type` indicates the type of data. `0` for data sent from the client, `4` for a client connection establishment, and `3` for a client connection closure.
    * `$data` is the data content. Note that if protocols such as `HTTP`, `EOF`, `Length`, are enabled, the underlying layer will concatenate packets. However, only the first 8K content of the packet is passed to the `dispatch_func` function, not the complete packet content.
    * **Must** return a number from `0` to `(server->worker_num - 1)` to indicate the target working process `ID` for the data packet delivery.
    * An `ID` less than `0` or greater than or equal to `server->worker_num` is considered an exception, and the `dispatched` data will be discarded.

  * **Write C++ Function**

    **In other PHP extensions, use swoole_add_function to register the length function to the Swoole engine.**

    ?> When a C++ function is called, the underlying layer does not lock. The caller needs to ensure thread safety.

    ```c++
    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data);

    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data)
    {
        printf("cpp, type=%d, size=%d\n", data->info.type, data->info.len);
        return data->info.len % serv->worker_num;
    }

    int register_dispatch_function(swModule *module)
    {
        swoole_add_function("my_dispatch_function", (void *) dispatch_function);
    }
    ```

    * The `dispatch` function must return the target `worker` process `id` for delivery.
    * The returned `worker_id` must not exceed `server->worker_num`, otherwise the underlying layer will throw a segmentation fault.
    * Returning a negative number `(return -1)` indicates discarding this data packet.
    * `data` can read the event type and length.
    * `conn` contains connection information. If it is a `UDP` packet, `conn` will be `NULL`.

  * **Note**

    !> - `dispatch_func` is only effective in [SWOOLE_PROCESS](/learn?id=swoole_process) mode, applicable to servers of type [UDP/TCP/UnixSocket](/server/methods?id=__construct).  
    - The returned `worker_id` must not exceed `server->worker_num`, otherwise the underlying layer will throw a segmentation fault.
### message_queue_key

?> **Set the `KEY` of the message queue.**【Default value: `ftok($php_script_file, 1)`】

?> Used only when [task_ipc_mode](/server/setting?id=task_ipc_mode) = 2/3. The set `Key` is only used as the `KEY` of the `Task` task queue, refer to [IPC Communication in Swoole](/learn?id=what-is-ipc).

?> The `task` queue will not be destroyed after the `server` ends. When the program is restarted, [task processes](/learn?id=taskworker-process) will continue to process tasks in the queue. If you do not want the program to execute old `Task` tasks after restarting, you can manually delete this message queue.

```shell
ipcs -q 
ipcrm -Q [msgkey]
```
### daemonize

?> **Daemonize**【default: `false`】

?> When `daemonize => true` is set, the program will run in the background as a daemon. This must be enabled for long-running server-side programs.  
If daemonize is not enabled, the program will be terminated when the ssh terminal exits.

  * **Note**

    * When daemonize is enabled, standard input and output will be redirected to `log_file`
    * If `log_file` is not set, it will be redirected to `/dev/null`, and all print screen information will be discarded.
    * After enabling daemonize, the value of the `CWD` (current working directory) environment variable will change, causing errors in relative file read/write operations. Absolute paths must be used in PHP programs.

    * **systemd**

      * When managing the `Swoole` service with `systemd` or `supervisord`, do not set `daemonize => true`. The main reason is that the mechanism of `systemd` is different from `init`. The `PID` of the `init` process is `1`, and after the program uses `daemonize`, it detaches from the terminal and is ultimately managed by the `init` process, changing the relationship to a parent-child process.
      * However, `systemd` starts a separate background process that `forks` to manage other service processes, so `daemonize` is not needed. Setting `daemonize => true` will make the `Swoole` program lose its parent-child process relationship with this managing process.
### backlog

Set the `Listen` queue length

For example, `backlog => 128`, this parameter will determine how many connections waiting for `accept` can be handled simultaneously.

* **About the `backlog` of TCP**

  `TCP` has a three-way handshake process: client `syn => server`, `syn+ack => client`, `ack`. When the server receives the client's `ack`, it will put the connection into a queue called the `accept queue` (note 1),
  The size of the queue is determined by the `backlog` parameter and the minimum value configured in `somaxconn`. You can check the final size of the `accept queue` by using the `ss -lt` command. The main process of `Swoole` calls `accept` (note 2)
  To take connections from the `accept queue`. When the `accept queue` is full, the connection may succeed (note 4),
  or it may fail. If it fails, the client's behavior is that the connection is reset (note 3)
  or times out, and the server will log the failed records. You can check the log by using `netstat -s|grep 'times the listen queue of a socket overflowed`. If you encounter the above phenomena, you should adjust this value larger. Fortunately, `Swoole` in SWOOLE_PROCESS mode is different from software like `PHP-FPM/Apache` and does not rely on `backlog` to solve the connection queuing problem. So you are less likely to encounter the above phenomena.

    * Note 1: After `linux2.2`, the handshake process is divided into two queues, `syn queue` and `accept queue`, the length of `syn queue` is determined by `tcp_max_syn_backlog`.
    * Note 2: In higher version kernels, `accept4` is called to save one `set no block` system call.
    * Note 3: The client considers the connection successful when it receives a `syn+ack` packet, but in reality, the server is still in a half-connected state and may send an `rst` packet to the client. The client's behavior will be `Connection reset by peer`.
    * Note 4: Success is determined by TCP's retransmission mechanism, related configurations include `tcp_synack_retries` and `tcp_abort_on_overflow`.
### open_tcp_keepalive

?> There is a `Keep-Alive` mechanism in `TCP` to detect dead connections. If the application layer is insensitive to dead connections or does not implement a heartbeat mechanism, you can use the operating system's provided `keepalive` mechanism to kick off dead connections. Adding `open_tcp_keepalive => true` in the [Server->set()](/server/methods?id=set) configuration indicates enabling `TCP keepalive`. Additionally, there are `3` options to adjust the details of `keepalive`.

  * **Options**

     * **tcp_keepidle**

        Unit in seconds, if there is no data request in `n` seconds, it will start probing this connection.

     * **tcp_keepcount**

        Number of probes, once exceeding this count, it will `close` this connection.

     * **tcp_keepinterval**

        Interval time for probing, unit in seconds.

  * **Example**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, // Check if no data transmission for 4 seconds
    'tcp_keepinterval' => 1, // Probe every 1 second
    'tcp_keepcount' => 5, // Number of probes, close the connection if no response after 5 attempts
));

$serv->on('connect', function ($serv, $fd) {
    var_dump("Client:Connect $fd");
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    var_dump($data);
});

$serv->on('close', function ($serv, $fd) {
  var_dump("close fd $fd");
});

$serv->start();
```
### heartbeat_check_interval

Enable heartbeat check **[Default: `false`]**

This option indicates how often to poll, in seconds. For example, if `heartbeat_check_interval => 60`, it means every `60` seconds, all connections will be traversed. If a connection has not sent any data to the server within `120` seconds (double the default `heartbeat_idle_time` when not set), the connection will be forcibly closed. If not configured, heartbeat will not be enabled, this configuration is off by default.

  * **Tips**
    * The `Server` does not actively send heartbeat packets to the client, but passively waits for the client to send heartbeats. The server's `heartbeat_check` only checks the time of the last data sent on the connection. If it exceeds the limit, the connection will be severed.
    * Connections severed by the heartbeat check will still trigger the [onClose](/server/events?id=onclose) event callback.

  * **Note**

    !> `heartbeat_check` only supports `TCP` connections.
### heartbeat_idle_time

?> **Maximum allowed idle time for a connection**

?> Needs to be used in conjunction with `heartbeat_check_interval`

```php
array(
    'heartbeat_idle_time'      => 600, // Represents that if a connection does not send any data to the server within 600 seconds, the connection will be forcibly closed
    'heartbeat_check_interval' => 60,  // Represents checking every 60 seconds
);
```

  * **Note**

    * When `heartbeat_idle_time` is enabled, the server does not actively send data packets to the client
    * If only `heartbeat_idle_time` is set without setting `heartbeat_check_interval`, the underlying system will not create a heartbeat detection thread, and `PHP` code can call the `heartbeat` method to manually handle timed-out connections
### open_eof_check

?> **Open `EOF` check** [default: `false`], refer to [TCP packet boundary problem](/learn?id=tcp数据包边界问题)

?> This option will check the data sent by the client connection. The data packet will only be delivered to the `Worker` process when the end of the packet is the specified string. Otherwise, it will keep concatenating the data packet until it exceeds the buffer or timeout before aborting. In case of an error, the underlying system will consider it as a malicious connection, discard the data, and forcefully close the connection.  
Common protocols like `Memcache/SMTP/POP` end with `\r\n`, so this configuration can be used. Enabling this ensures that the `Worker` process always receives one or more complete data packets at once.

```php
array(
    'open_eof_check' => true,   // Open EOF check
    'package_eof'    => "\r\n", // Set EOF
)
```

  * **Note**

    !> This configuration is only effective for `STREAM` type `Socket`, such as [TCP, Unix Socket Stream](/server/methods?id=__construct)   
    `EOF` check does not search for the `eof` string in the middle of the data, so the `Worker` process may receive multiple data packets at the same time. You need to manually use `explode("\r\n", $data)` in the application layer code to split the data packets.
### open_eof_split

Enable `EOF` automatic splitting

When `open_eof_check` is set, multiple pieces of data may be combined into one packet. The `open_eof_split` parameter can solve this problem. Refer to [TCP packet boundary issues](/learn?id=tcp数据包边界问题).

Setting this parameter requires traversing the entire content of the data packet to find `EOF`, which will consume a lot of `CPU` resources. Suppose each data packet is `2M`, with `10000` requests per second, this may generate `20G` CPU character matching instructions.

```php
array(
    'open_eof_split' => true,   // Enable EOF_SPLIT check
    'package_eof'    => "\r\n", // Set EOF
)
```

  * **Tips**

    * When the `open_eof_split` parameter is enabled, the underlying layer will search for `EOF` in the middle of the data packet and split it. [onReceive](/server/events?id=onreceive) receives only one data packet ending with the `EOF` string each time.
    * After enabling the `open_eof_split` parameter, it will take effect regardless of whether the `open_eof_check` parameter is set.

    * **Difference from `open_eof_check`**
    
        * `open_eof_check` only checks if the end of the received data is `EOF`, so it has the best performance with almost no consumption.
        * `open_eof_check` cannot solve the problem of multiple data packets being combined, such as sending two data with `EOF` at the same time, the bottom layer may return all at once.
        * `open_eof_split` compares the data byte by byte from left to right to find `EOF` in the data for packet splitting, which has poor performance. However, it will only return one data packet at a time. 
### package_eof

?> **Set the `EOF` string.** See [TCP packet boundary issues](/learn?id=tcp数据包边界问题)

?> Need to work with `open_eof_check` or `open_eof_split`.


  * **Attention**

    !> `package_eof` only allows up to `8` bytes of string to be entered.
### open_length_check

?> **Enable package length check feature**[Default: `false`], refer to [TCP data packet boundary issue](/learn?id=tcp数据包边界问题)

?> The length check provides parsing for fixed header + body format protocols. When enabled, it ensures that the `Worker` process receives a complete data packet each time in the [onReceive](/server/events?id=onreceive) event.  
For length-checking protocols, only the length needs to be calculated once, and data processing involves only pointer shifting, resulting in high performance. **Recommended for use**.

  * **Tip**

    * **The length protocol provides 3 options to control protocol details.**

      ?> This configuration is only valid for `STREAM` type `Socket`, such as [TCP, Unix Socket Stream](/server/methods?id=__construct)

      * **package_length_type**

        ?> A field in the package header serves as the package length value, supporting 10 types of length values. Please refer to [package_length_type](/server/setting?id=package_length_type)

      * **package_body_offset**

        ?> Indicates from which byte to start calculating length, generally with two scenarios:

        * If the `length` value includes the entire package (header + body), `package_body_offset` is `0`
        * If the header length is `N` bytes and the `length` value includes only the body without the header, set `package_body_offset` to `N`

      * **package_length_offset**

        ?> Indicates the position of the `length` value in the header.

        * Example:

        ```c
        struct
        {
            uint32_t type;
            uint32_t uid;
            uint32_t length;
            uint32_t serid;
            char body[0];
        }
        ```

    ?> In the above communication protocol design, the header length consists of `4` integers, `16` bytes, with the `length` value at the position of the third integer. Therefore, the `package_length_offset` is set to `8`, where bytes `0-3` represent `type`, bytes `4-7` represent `uid`, bytes `8-11` represent `length`, and bytes `12-15` represent `serid`.

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```  
### package_length_type

**Length value type**, accepts a character parameter, consistent with the `pack` function in `PHP`.

Currently, `Swoole` supports `10` types:

Character Parameter | Purpose
---|---
c | Signed, 1 byte
C | Unsigned, 1 byte
s | Signed, host byte order, 2 bytes
S | Unsigned, host byte order, 2 bytes
n | Unsigned, network byte order, 2 bytes
N | Unsigned, network byte order, 4 bytes
l | Signed, host byte order, 4 bytes (lowercase L)
L | Unsigned, host byte order, 4 bytes (uppercase L)
v | Unsigned, little-endian byte order, 2 bytes
V | Unsigned, little-endian byte order, 4 bytes
### package_length_func

?> **Set length parsing function**

?> Supports `2` types of functions in `C++` or `PHP`. The length function must return an integer.

Return value | Purpose
---|---
Return 0 | Insufficient length data, need to receive more data
Return -1 | Data error, the underlying connection will be automatically closed
Return the package length value (including the total length of the package head and body) | The underlying layer will automatically concatenate the package and return it to the callback function

  * **Tips**

    * **Usage**

    ?> The principle is to first read a small amount of data, which contains a length value within this segment of data. Then return this length to the underlying layer. The underlying layer will then complete the reception of the remaining data and combine it into a package for `dispatch`.

    * **PHP length parsing function**

    ?> Since `ZendVM` does not support running in a multi-threaded environment, the underlying layer will automatically use `Mutex` mutex lock to lock the `PHP` length function, avoiding concurrent execution of `PHP` functions. Available in `1.9.3` or higher.

    !> Do not perform blocking `IO` operations in the length parsing function, as this may cause all [Reactor](/learn?id=reactor-thread) threads to block.

    ```php
    $server = new Swoole\Server("127.0.0.1", 9501);
    
    $server->set(array(
        'open_length_check'   => true,
        'dispatch_mode'       => 1,
        'package_length_func' => function ($data) {
          if (strlen($data) < 8) {
              return 0;
          }
          $length = intval(trim(substr($data, 0, 8)));
          if ($length <= 0) {
              return -1;
          }
          return $length + 8;
        },
        'package_max_length'  => 2000000,  // Maximum protocol length
    ));
    
    $server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        var_dump($data);
        echo "#{$server->worker_id}>> received length=" . strlen($data) . "\n";
    });
    
    $server->start();
    ```

    * **C++ length parsing function**

    ?> In other PHP extensions, use `swoole_add_function` to register the length function with the `Swoole` engine.
    
    !> The underlying layer does not lock when calling the C++ length function, and the caller must ensure thread safety.

    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"
    
    using namespace std;
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);
    
    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW_OK;
    }
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length)
    {
        printf("cpp, size=%d\n", length);
        return 100;
    }
    ```  
### package_max_length

?> **Set the maximum packet size in bytes.** 【Default value: `2M` which is `2 * 1024 * 1024`, minimum value is `64K`】

?> After enabling [open_length_check](/server/setting?id=open_length_check)/[open_eof_check](/server/setting?id=open_eof_check)/[open_eof_split](/server/setting?id=open_eof_split)/[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol) and other protocol parsing settings, Swoole's underlying layer will concatenate data packets. When a data packet is not fully received, all data is temporarily stored in memory. Therefore, it is necessary to set `package_max_length`, the maximum memory size allowed for a data packet. In a hypothetical scenario where there are 10,000 TCP connections sending data with each data packet size of 2M, it would potentially consume up to 20G of memory.

  * **Tips**

    * `open_length_check`: If a packet length exceeds `package_max_length`, the data will be discarded, and the connection will be closed without consuming any memory.
    * `open_eof_check`: Since the packet length cannot be determined in advance, received data will continue to accumulate in memory. Once the memory usage exceeds `package_max_length`, the data will be discarded, and the connection will be closed.
    * `open_http_protocol`: The maximum size allowed for a `GET` request is 8K and cannot be modified. For `POST` requests, the `Content-Length` is checked, and if it exceeds `package_max_length`, the data will be discarded, an HTTP 400 error will be sent, and the connection will be closed.

  * **Note**

    !> This parameter should not be set too large, as it may consume a significant amount of memory.
### open_http_protocol

?> **Enable the `HTTP` protocol processing.** 【Default value: `false`】

?> Enable the `HTTP` protocol processing, [Swoole\Http\Server](/http_server) will automatically enable this option. Setting to `false` means turning off `HTTP` protocol processing.
### open_mqtt_protocol

?> **Enable `MQTT` protocol processing.** 【Default value: `false`】

?> When enabled, the `MQTT` packet header will be parsed, and the `worker` process will return a complete `MQTT` data packet each time it [receives](/server/events?id=onreceive).

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```
### open_redis_protocol

?> **Enable `Redis` protocol handling.**【Default value: `false`】

?> When enabled, the `Redis` protocol will be parsed, and the `worker` process will return a complete `Redis` data packet each time [onReceive](/server/events?id=onreceive) is triggered. It is recommended to use [Redis\Server](/redis_server)

```php
$server->set(array(
  'open_redis_protocol' => true
));
```
### open_websocket_protocol

?> **Enable the `WebSocket` protocol handling.**【Default value: `false`】

?> Enable the `WebSocket` protocol handling, the [Swoole\WebSocket\Server](websocket_server) will automatically enable this option. Set to `false` to disable `websocket` protocol handling.  
By setting the `open_websocket_protocol` option to `true`, the `open_http_protocol` will also be automatically set to `true`.
### open_websocket_close_frame

Enable closing frames in the websocket protocol. (Default value: `false`)

Receiving frames with `opcode` `0x08` in the `onMessage` callback.

After enabling this option, you can receive closing frames sent by clients or servers in the `onMessage` callback of `WebSocketServer`, and developers can handle them as needed.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->set(array("open_websocket_close_frame" => true));

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {});

$server->start();
```
### open_tcp_nodelay

?> **Enable `open_tcp_nodelay`.**【default value: `false`】

?> When enabled, the `Nagle` merge algorithm will be turned off when sending data over `TCP` connections, which will immediately be sent to the remote TCP connection. In some scenarios, such as command line terminals where a command needs to be sent to the server immediately, enabling this option can improve response speed. Please Google the Nagle algorithm for more information.
### open_cpu_affinity

- Enable CPU affinity setting. Default value is `false`.

- When this feature is enabled on a multi-core hardware platform, it binds the `Swoole` `reactor threads` / `worker processes` to a fixed core. This can avoid processes/threads switching between multiple cores at runtime, and improve the hit rate of CPU cache.

  - **Tips**

    - **Use the taskset command to view a process's CPU affinity setting:**

    ```bash
    taskset -p processID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > The mask is a mask number where each bit corresponds to a CPU core. If a bit is set to `0`, it means the process is bound to that core and will be scheduled to run on that CPU. If it's set to `1`, the process won't be scheduled to run on that CPU. In the example, for the process with `pid` of `24666`, `mask = f` indicates it's not bound to a specific CPU and the OS will schedule it on any core. For the process with `pid` of `24901`, `mask = 8`, where `8` in binary is `1000`, indicating that this process is bound to the fourth CPU core.
### cpu_affinity_ignore

In IO-intensive programs, all network interrupts are processed using CPU0. If the network IO is heavy, high load on CPU0 may cause network interrupts to be unable to be processed in a timely manner, resulting in a decrease in network packet sending and receiving capabilities.

If this option is not set, Swoole will use all CPU cores, and the underlying system will set CPU bindings based on the reactor_id or worker_id and the number of CPU cores. If the kernel and NIC have multi-queue features, network interrupts will be distributed across multiple cores, which can alleviate the pressure on network interrupts.

```php
array('cpu_affinity_ignore' => array(0, 1)) // Accepts an array as a parameter, where array(0, 1) means not using CPU0 and CPU1, specifically left free to handle network interrupts.
```

  * **Note**

    * **Viewing network interrupts**

```shell
[~]$ cat /proc/interrupts 
           CPU0       CPU1       CPU2       CPU3       
  0: 1383283707          0          0          0    IO-APIC-edge  timer
  1:          3          0          0          0    IO-APIC-edge  i8042
  3:         11          0          0          0    IO-APIC-edge  serial
  8:          1          0          0          0    IO-APIC-edge  rtc
  9:          0          0          0          0   IO-APIC-level  acpi
 12:          4          0          0          0    IO-APIC-edge  i8042
 14:         25          0          0          0    IO-APIC-edge  ide0
 82:         85          0          0          0   IO-APIC-level  uhci_hcd:usb5
 90:         96          0          0          0   IO-APIC-level  uhci_hcd:usb6
...
```

`eth0/eth1` denotes the number of network interrupts. If `CPU0 - CPU3` is evenly distributed, it indicates that the NIC has multi-queue features. If they are all concentrated on one core, it means that network interrupts are all processed by that CPU. Once this CPU exceeds 100%, the system will be unable to handle network requests. In such cases, you need to use `cpu_affinity_ignore` to set aside this CPU specifically for handling network interrupts.

In the scenario shown above, `cpu_affinity_ignore => array(0)` should be set.

It's possible to use the `top` command `->` enter `1` to view the usage of each core.

  * **Caution**

    !> This option must be set concurrently with `open_cpu_affinity` for it to take effect.
### tcp_defer_accept

?> **Enable the `tcp_defer_accept` feature** [default value: `false`]

?> It can be set to a numerical value, indicating that `accept` should only be triggered when a `TCP` connection has data to send.

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

  * **Tips**

    * **After enabling the `tcp_defer_accept` feature, the timing of `accept` and [onConnect](/server/events?id=onconnect) will change. If set to `5` seconds:**

      * The server will not immediately trigger `accept` after a client connects
      * If the client sends data within `5` seconds, `accept/onConnect/onReceive` will be triggered sequentially
      * If no data is sent by the client within `5` seconds, `accept/onConnect` will be triggered.
### ssl_cert_file / ssl_key_file :id=ssl_cert_file

?> **Set up SSL tunnel encryption.**

?> Set the value to a file name string, specifying the paths of the cert certificate and key private key.

  * **Tips**

    * **Convert `PEM` to `DER` format**

    ```shell
    openssl x509 -in cert.crt -outform der -out cert.der
    ```

    * **Convert `DER` to `PEM` format**

    ```shell
    openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
    ```

  * **Note**

    !> - Browsers must trust the certificate for `HTTPS` applications to browse web pages;
    - In `wss` applications, the page initiating the `WebSocket` connection must use `HTTPS`;
    - Browsers will not trust `SSL` certificates, causing `wss` not to work;
    - Files must be in `PEM` format, `DER` format is not supported, you can use the `openssl` tool for conversion.

    !> To use SSL, you must include the [--enable-openssl](/environment?id=编译选项) option when compiling Swoole.

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```
### ssl_method

!> This parameter has been removed in [v4.5.4](/version/bc?id=_454), please use `ssl_protocols` instead.

?> **Set the encryption method for OpenSSL tunnels.** 【Default value: `SWOOLE_SSLv23_METHOD`】, refer to [SSL encryption methods](/consts?id=ssl-encryption-methods) for supported types.

?> The algorithms used by `Server` and `Client` must be consistent, otherwise the `SSL/TLS` handshake will fail and the connection will be terminated.

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```
### ssl_protocols

?> **Set the protocol for OpenSSL tunnel encryption.** [Default value: `0`, which supports all protocols], supported types refer to [SSL Protocol](/consts?id=ssl-protocols)

!> Available in Swoole version >= `v4.5.4`

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```
### ssl_sni_certs

?> **Set SNI (Server Name Identification) certificates**

!> Available for Swoole version >= `v4.6.0`

```php
$server->set([
    'ssl_cert_file' => __DIR__ . '/server.crt',
    'ssl_key_file' => __DIR__ . '/server.key',
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'ssl_sni_certs' => [
        'cs.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_cs_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_cs_key.pem',
        ],
        'uk.php.net' => [
            'ssl_cert_file' =>  __DIR__ . '/sni_server_uk_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_uk_key.pem',
        ],
        'us.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_us_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_us_key.pem',
        ],
    ]
]);
```
### ssl_ciphers

?> **Set the openssl encryption algorithm.** 【Default value: `EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

  * **Note**

    * When `ssl_ciphers` is set to an empty string, `openssl` will choose the encryption algorithm by itself.
### ssl_verify_peer

?> **Verify the peer's SSL certificate.**【Default value: `false`】

?> By default, this is turned off, which means client certificates are not verified. If enabled, the `ssl_client_cert_file` option must be set.
### ssl_allow_self_signed

?> **Allow self-signed certificates.** [Default value: `false`]
### ssl_client_cert_file

?> **Root certificate used to verify client certificate.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set(array(
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,
    'ssl_allow_self_signed' => true,
    'ssl_client_cert_file'  => __DIR__ . '/config/ca.crt',
));
```

!> If the verification fails for a `TCP` service, the underlying layer will actively close the connection.
### ssl_compress

?> **Set whether to enable SSL/TLS compression.** When used in [Co\Client](/coroutine_client/client), it has an alias `ssl_disable_compression`.
### ssl_verify_depth

?> **If the certificate chain is too deep and exceeds the value set in this option, the verification will be terminated.**
### ssl_prefer_server_ciphers

?> **Enable server-side protection to prevent BEAST attacks.**
### ssl_dhparam

?> **Specify the `Diffie-Hellman` parameters for the DHE cipher.**
### ssl_ecdh_curve

?> **Specify the `curve` used in ECDH key exchange.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_compress'                => true,
    'ssl_verify_depth'            => 10,
    'ssl_prefer_server_ciphers'   => true,
    'ssl_dhparam'                 => '',
    'ssl_ecdh_curve'              => '',
]);
```
### user

?> **Set the user that the `Worker/TaskWorker` child process belongs to.** 【Default value: executing script user】

?> If the server needs to listen on ports below `1024`, it must have `root` permissions. However, if the program runs under the `root` user, in case of vulnerabilities in the code, attackers can execute remote commands as `root`, posing a great risk. By configuring the `user` field, you can run the main process with `root` permissions and the child processes with normal user permissions.

```php
$server->set(array(
  'user' => 'Apache'
));
```

  * **Note**

    !> -Only effective when started with the `root` user  
    -After setting the working process to a regular user with the `user/group` configuration option, you will not be able to shut down or restart the service by calling the `shutdown`/[reload](/server/methods?id=reload) method in the working process. You can only use the `root` account in the shell terminal to execute the `kill` command.
### group

Set the process user group for the `Worker/TaskWorker` child processes.

By setting it to the same value as the `user` configuration, this setting modifies the user group to which the process belongs, enhancing the security of the server program.

```php
$server->set(array(
  'group' => 'www-data'
));
```

* **Note**

  !> This setting is only effective when starting with the `root` user.
### chroot

?> **Redirect the file system root directory for the `Worker` process.**

?> This setting allows for the process to isolate its file system read and write operations from the actual operating system file system, thus improving security.

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```
### pid_file

?> **Set the path of the PID file.**

?> Automatically write the PID of the `master` process to the file when the `Server` is started, and automatically delete the PID file when the `Server` is closed.

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

  * **Note**

    !> Please note that if the `Server` does not end normally, the PID file will not be deleted. You need to use [Swoole\Process::kill($pid, 0)](/process/process?id=kill) to check if the process really exists
### buffer_input_size / input_buffer_size: id=buffer_input_size

?> **Configure the size of the input buffer to receive input.**【Default value: `2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```
### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **Configure the memory size of the output buffer for sending.** 【Default value: `2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, // Must be a number
]);
```

  * **Hint**

    !> When Swoole version is >= `v4.6.7`, the default value is the maximum unsigned INT value `UINT_MAX`

    * The unit is in bytes, default is `2M`. For example, setting `32 * 1024 * 1024` means the maximum data allowed to be sent in one `Server->send` is `32M` bytes.
    * When calling `Server->send`, `Http\Server->end/write`, `WebSocket\Server->push`, etc., sending data instructions, the maximum data that can be sent in `one shot` must not exceed the `buffer_output_size` configuration.

    !> This parameter only takes effect in [SWOOLE_PROCESS](/learn?id=swoole_process) mode because in the PROCESS mode, data from Worker processes needs to be sent to the main process before sending to clients. Hence, each Worker process will have a buffer area opened with the main process. [Reference](/learn?id=reactor线程)
### socket_buffer_size

?> **Configuration of the length of the buffer for client connections.**【Default value: `2M`】

?> Different from `buffer_output_size`, where `buffer_output_size` is the size limit for a `single` send operation in the worker process, `socket_buffer_size` is used to set the total buffer size for communication between `Worker` and `Master` processes, refer to the [SWOOLE_PROCESS](/learn?id=swoole_process) mode.

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 * 1024, // Must be a number, in bytes, such as 128 * 1024 * 1024 means each TCP client connection can have a maximum of 128M data pending for sending
]);
```

- **Data Sending Buffer**

    - When the Master process sends a large amount of data to a client, it may not be sent out immediately. The data to be sent is stored in a memory buffer on the server side. This parameter can adjust the size of the memory buffer.
    
    - If too much data is sent and the buffer is full, the `Server` will report an error message like this:
    
    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```
    
    ?> When the sending buffer is full, causing a send failure, it only affects the current client, and does not affect others. When the server has a large number of TCP connections, in the worst case, it will occupy `serv->max_connection * socket_buffer_size` bytes of memory.
    
    - Especially for servers involved in external communication, when network communication is slow, if you continuously send data, the buffer will quickly fill up. The sent data will accumulate in the server's memory. Therefore, such applications should consider the network transmission capacity in the design, store the message in the disk first, and send new data after the client notifies the server that it has received it.
    
    - For example, in a live video streaming service, if `User A` has a bandwidth of `100M`, sending `10M` of data in 1 second is completely fine. `User B` has a bandwidth of only `1M`, if `10M` of data is sent in 1 second, `User B` may need `100` seconds to receive it all. In this case, the data will all accumulate in the server's memory.
    
    - Different types of data can be processed differently. If it is disposable content, such as video streaming, it is acceptable to discard some data frames in case of poor network conditions. If the content must not be lost, such as WeChat messages, it can be first stored on the server's disk, in groups of `100` messages for example. When the user completes receiving this group of messages, the server can then retrieve the next group of messages from the disk and send them to the client.
### enable_unsafe_event

?> **Enable `onConnect/onClose` events.** [Default: `false`]

?> After `dispatch_mode` is set to 1 or 3 in the configuration, `Swoole` cannot guarantee the order of `onConnect/onReceive/onClose` events, so by default, `onConnect/onClose` events are disabled;  
If the application requires `onConnect/onClose` events and can accept the security risks that may come with order issues, you can set `enable_unsafe_event` to `true` to enable `onConnect/onClose` events.
### discard_timeout_request

?> **Discard data requests for closed connections.** [Default Value: `true`]

?> When `Swoole` configures [dispatch_mode](/server/setting?id=dispatch_mode) to `1` or `3`, the system cannot guarantee the order of `onConnect/onReceive/onClose`, so there may be some request data that arrives in the `Worker` process after the connection is closed.

  * **Note**

    * The `discard_timeout_request` configuration is set to `true` by default, meaning that if the `worker` process receives a data request from a closed connection, it will be automatically discarded.
    * If `discard_timeout_request` is set to `false`, it means that the `Worker` process will handle data requests regardless of whether the connection is closed.
### enable_reuse_port

Setting to enable port reuse. (Default value: `false`)

Enabling port reuse allows for starting multiple Server programs listening on the same port.

  * **Tips**

    * `enable_reuse_port = true` to enable port reuse
    * `enable_reuse_port = false` to disable port reuse

This feature is only available on kernel versions `Linux-3.9.0` and above, and `Swoole4.5` and above.
### enable_delay_receive

**Set `accept` client connection without automatically joining [EventLoop](/learn?id=what-is-eventloop).** 【Default value: `false`】

By setting this option to `true`, the client connection after `accept` will not automatically join the [EventLoop](/learn?id=what-is-eventloop), only trigger the [onConnect](/server/events?id=onconnect) callback. The `worker` process can call [$server->confirm($fd)](/server/methods?id=confirm) to confirm the connection, at which point `fd` will be added to the [EventLoop](/learn?id=what-is-eventloop) to start data transmission and reception, or call `$server->close($fd)` to close this connection.

```php
// Enable the enable_delay_receive option
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        // Confirm the connection to start receiving data
        $server->confirm($fd);
    });
});
```
### reload_async

?> **Set asynchronous restart switch.**【Default value: `true`】

?> Set the asynchronous restart switch. When set to `true`, the asynchronous safe restart feature will be enabled, and the `Worker` process will wait for asynchronous events to complete before exiting. For more information, please refer to [How to restart the service correctly](/question/use?id=swoole如何正确的重启服务)

?> The main purpose of enabling `reload_async` is to ensure that coroutines or asynchronous tasks can end normally during service reloads.

```php
$server->set([
  'reload_async' => true
]);
```

  * **Coroutine mode**

    * In `4.x` version, when [enable_coroutine](/server/setting?id=enable_coroutine) is enabled, an additional check of the number of coroutines will be added at the bottom. The process will only exit when there are no coroutines, and even if `reload_async => false` is opened, `reload_async` will be forcibly turned on.
### max_wait_time

?> **Set the maximum waiting time for the `Worker` process after receiving a stop service notification**【Default: `3`】

?> It is common to encounter situations where the `worker` process is blocked and cannot be reloaded properly, which fails to meet certain production scenarios such as code hot reloading requiring process reloads. Therefore, Swoole has introduced an option for process restart timeout. For more details, please refer to [How to properly restart a service](/question/use?id=swoole如何正确的重启服务)

  * **Tips**

    * **When the management process receives a restart or shutdown signal, or reaches `max_request`, the management process will restart the `worker` process. The process includes the following steps:**

      * A timer for (`max_wait_time`) seconds is set at the bottom layer. Once the timer is triggered, the process checks if it still exists. If it does, it will be forcibly killed, and a new process will be brought up.
      * Finalization work needs to be done in the `onWorkerStop` callback within `max_wait_time` seconds.
      * The target process is sequentially sent a `SIGTERM` signal to kill the process.

  * **Note**

    !> Prior to `v4.4.x`, the default was `30` seconds
### tcp_fastopen

?> **Enable TCP fast handshake feature.** [Default value: `false`]

?> This feature can improve the response speed of `TCP` short connections by sending data with the `SYN` packet when the client completes the third step of the handshake.

```php
$server->set([
  'tcp_fastopen' => true
]);
```

  * **Tip**

    * This parameter can be set on the listening port. Students who want to deepen their understanding can refer to the [Google paper](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf)
### request_slowlog_file

Enable slow request log. Removed since `v4.4.8` [version](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366).

Due to the fact that this slow log solution only works in synchronous blocking processes and cannot be used in coroutine environments, and since Swoole 4 enables coroutines by default unless `enable_coroutine` is disabled, it is advised not to use this feature. Instead, consider using the [Swoole Tracker](https://business.swoole.com/tracker/index) blocking detection tool.

Enabling this will cause the `Manager` process to set a clock signal that continuously monitors all `Task` and `Worker` processes. If a process blocks and the request exceeds the specified time, the PHP function call stack of the process will be automatically printed.

Based on the `ptrace` system call implementation, some systems may have `ptrace` disabled, preventing tracking of slow requests. Verify if the `kernel.yama.ptrace_scope` kernel parameter is set to `0`.

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

  * **Timeout Setting**

```php
$server->set([
    'request_slowlog_timeout' => 2, // Set request timeout to 2 seconds
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

Make sure the file has write permissions, as failure to create the file due to lack of permissions will result in a fatal error at the lower level.
### enable_coroutine

?> **Enable coroutine support for async style servers**

?> When `enable_coroutine` is turned off, no coroutine will be automatically created in [event callback functions](/server/events), which can slightly improve performance if coroutines are not needed. Refer to [What is Swoole Coroutine](/coroutine).

  * **Configuration method**
    
    * Configure in `php.ini` `swoole.enable_coroutine = 'Off'` (see [ini configuration document](/other/config.md) )
    * `$server->set(['enable_coroutine' => false]);` takes precedence over `ini`

  * **Scope of `enable_coroutine` option**

      * onWorkerStart
      * onConnect
      * onOpen
      * onReceive
      * [setHandler](/redis_server?id=sethandler)
      * onPacket
      * onRequest
      * onMessage
      * onPipeMessage
      * onFinish
      * onClose
      * tick/after timers

!> When `enable_coroutine` is enabled, coroutine will be automatically created in the above callback functions

* When `enable_coroutine` is set to `true`, coroutines are automatically created in the [onRequest](/http_server?id=on) callback, developers do not need to use `go` function to [create coroutine](/coroutine/coroutine?id=create)
* When `enable_coroutine` is set to `false`, coroutines are not automatically created, developers must use `go` to create coroutines themselves. If coroutine features are not needed, the behavior is 100% consistent with `Swoole 1.x`
* Note that enabling this does not mean Swoole will handle requests with coroutines. If there are blocking functions in the events, you need to enable [Coroutine Hook](/runtime) in advance, and make functions like `sleep`, `mysqlnd` coroutine-friendly.

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    // Disable built-in coroutine
    'enable_coroutine' => false,
]);

$server->on("request", function ($request, $response) {
    if ($request->server['request_uri'] == '/coro') {
        go(function () use ($response) {
            co::sleep(0.2);
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });
    } else {
        $response->header("Content-Type", "text/plain");
        $response->end("Hello World\n");
    }
});

$server->start();
```
### send_yield

When the memory in the buffer is insufficient during data transmission, directly [yield](/coroutine?id=coroutine-scheduling) in the current coroutine, wait for the data to be sent, and automatically [resume](/coroutine?id=coroutine-scheduling) the current coroutine when the buffer is cleared, and continue sending data. **[Default value: available when [dispatch_mod](/server/setting?id=dispatch_mode) is 2/4 and enabled by default]**

- When `Server/Client->send` returns `false` with error code `SW_ERROR_OUTPUT_BUFFER_OVERFLOW`, it does not return `false` to the `PHP` layer, but suspends the current coroutine with [yield](/coroutine?id=coroutine-scheduling).
- The `Server/Client` listens to the event of buffer clearance. After this event is triggered, the data in the buffer has been sent out, then [resume](/coroutine?id=coroutine-scheduling) the corresponding coroutine.
- After the coroutine is resumed, continue to call `Server/Client->send` to write data into the buffer. At this time, because the buffer is empty, the sending process is bound to succeed.

Before improvement

```php
for ($i = 0; $i < 100; $i++) {
    // When the buffer is full, it will return false directly and report output buffer overflow error
    $server->send($fd, $data_2m);
}
```

After improvement

```php
for ($i = 0; $i < 100; $i++) {
    // When the buffer is full, it will yield the current coroutine, and resume to continue execution after sending is completed
    $server->send($fd, $data_2m);
}
```

!> This feature will change the default behavior of the underlying system and can be manually turned off.

```php
$server->set([
    'send_yield' => false,
]);
```

  * __Affected Scope__

    * [Swoole\Server::send](/server/methods?id=send)
    * [Swoole\Http\Response::write](/http_server?id=write)
    * [Swoole\WebSocket\Server::push](/websocket_server?id=push)
    * [Swoole\Coroutine\Client::send](/coroutine_client/client?id=send)
    * [Swoole\Coroutine\Http\Client::push](/coroutine_client/http_client?id=push)
### send_timeout

Set the send timeout, used in conjunction with `send_yield`. When data cannot be sent to the cache within the specified time, the underlying function returns `false` and sets the error code to `ETIMEDOUT`, which can be retrieved using the [getLastError()](/server/methods?id=getlasterror) method.

> The type is float, the unit is seconds, and the minimum granularity is milliseconds

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1.5 seconds
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false and $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "Send timed out\n";
    }
}
```
### hook_flags

?> **Set the function scope for `one-click coroutine` Hook.** [Default value: no hook]

!> Available for Swoole version `v4.5+` or [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x), see details [one-click coroutine](/runtime)

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
### buffer_high_watermark

?> **Set the buffer high watermark, in bytes.**

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```
### buffer_low_watermark

?> **Set the low watermark of the buffer, in bytes.**

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```
### tcp_user_timeout

?> The TCP_USER_TIMEOUT option is a socket option at the TCP layer, with a value indicating the maximum time in milliseconds to wait for an ACK confirmation after a data packet is sent. Please refer to the man page for details.

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10 seconds
]);
```

!> Available in Swoole version >= `v4.5.3-alpha`
### stats_file

?> **Specify the file path where the content of [stats()](/server/methods?id=stats) will be written. After setting this, a timer will be automatically set on [onWorkerStart](/server/events?id=onworkerstart) to periodically write the content of [stats()](/server/methods?id=stats) into the specified file.**

```php
$server->set([
    'stats_file' => __DIR__ . '/stats.log',
]);
```

!> Available in Swoole version >= `v4.5.5`
### event_object

?> **When this option is set, the event callback will use [object style](/server/events?id=callback-object).** 【Default value: `false`】

```php
$server->set([
    'event_object' => true,
]);
```

!> Available in Swoole version >= `v4.6.0`
### start_session_id

?> **Set the starting session ID**

```php
$server->set([
    'start_session_id' => 10,
]);
```

!> Available in Swoole version >= `v4.6.0`
### single_thread

?> **Set to single thread.** When enabled, the Reactor thread will be merged with the Master thread in the Master process, and the logic will be handled by the Master thread. In PHP ZTS, if using SWOOLE_PROCESS mode, be sure to set this value to `true`.

```php
$server->set([
    'single_thread' => true,
]);
```

!> Available in Swoole version >= `v4.2.13`
### max_queued_bytes

?> **Set the maximum queue length of the receive buffer.** If exceeded, stop receiving.

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Available in Swoole version >= `v4.5.0`
### admin_server

?> **Set up the admin_server service to view service information in [Swoole Dashboard](http://dashboard.swoole.com/).**

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Available for Swoole version >= `v4.8.0`
