# Basic knowledge
## Four ways to set callback functions

* **Anonymous Function**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
!> Parameters can be passed to the anonymous function using `use`

* **Static Class Method**

```php
class A
{
    static function test($req, $resp)
    {
        echo "hello world";
    }
}
$server->on('Request', 'A::test');
$server->on('Request', array('A', 'test'));
```
!> The corresponding static method must be `public`

* **Function**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **Object Method**

```php
class A
{
    function test($req, $resp)
    {
        echo "hello world";
    }
}

$object = new A();
$server->on('Request', array($object, 'test'));
```

!> The corresponding method must be `public`
## Synchronous IO/Asynchronous IO

Under `Swoole4+`, all business code is written synchronously (`Swoole1.x` era supported asynchronous writing style, but now asynchronous clients have been removed, corresponding requirements can be implemented using coroutine clients). There is no mental burden at all, following human thinking habits. However, the synchronous writing may involve `synchronous IO/asynchronous IO` at the underlying level.

Whether it is synchronous IO/asynchronous IO, `Swoole/Server` can maintain a large number of `TCP` client connections (refer to [SWOOLE_PROCESS mode](/learn?id=swoole_process)). Whether your service is blocking or non-blocking does not require configuring specific parameters separately; it depends on whether there are synchronous IO operations in your code.

**What is synchronous IO:**

A simple example is when the process reaches `MySQL->query`, it does nothing and waits for MySQL to return the result. After receiving the result, the code continues to execute. Therefore, the concurrency capability of synchronous IO services is poor.

**What kind of code is synchronous IO:**

- When you have not enabled [one-click coroutine](/runtime), most of the IO operations in your code are synchronous IO. After enabling coroutine, they will become asynchronous IO, and the process will not wait idle but continue execution, refer to [coroutine scheduling](/coroutine?id=coroutine-scheduling).
- There are some IO operations that cannot be one-click coroutinized, making it impossible to turn synchronous IO into asynchronous IO, for example, `MongoDB` (believe `Swoole` will solve this issue). Be cautious when writing code in such cases.

!> [Coroutine](/coroutine) is designed to increase concurrency. If your application does not have high concurrency or must use certain operations that cannot be made asynchronous (such as MongoDB mentioned earlier), you can completely avoid enabling [one-click coroutine](/runtime), disable [enable_coroutine](/server/setting?id=enable_coroutine), and increase the number of `Worker` processes. This will be similar to the `Fpm/Apache` model. It is worth mentioning that since `Swoole` is a [resident process](https://course.swoole-cloud.com/course-video/80), even synchronous IO performance will be significantly improved. In practice, many companies are adopting this approach.
### Convert synchronous IO to asynchronous IO

The previous section introduced what synchronous/asynchronous IO is. Under `Swoole`, in some cases synchronous `IO` operations can be converted to asynchronous IO:

- After enabling [Coroutine Support](/runtime), operations on `MySQL`, `Redis`, `Curl`, etc. will become asynchronous IO.
- By utilizing the [Event](/event) module to manually manage events, you can add the `$fd` to the [EventLoop](/learn?id=what-is-eventloop), converting it to asynchronous IO. For example:

```php
// Monitor file changes using inotify
$fd = inotify_init();
// Add $fd to Swoole's EventLoop
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd); // Read the changed file after a file change.
    var_dump($var);
});
```

In the above code, if `Swoole\Event::add` is not called to make the IO asynchronous, directly calling `inotify_read()` will block the Worker process, causing other requests to go unprocessed.

- Use `Swoole\Server`'s [sendMessage()](/server/methods?id=sendMessage) method for inter-process communication. By default, `sendMessage` is synchronous IO, but in some cases, `Swoole` will convert it to asynchronous IO. Here's an example using [User Process](/server/methods?id=addprocess):

```php
// Three different scenarios are illustrated in the code snippet, explaining how sendMessage can be converted to asynchronous IO in certain cases.
```

- Similarly, inter-process communication using `sendMessage()` in the [Task Worker Process](/learn?id=taskworker-process) is similar. The difference is that enabling coroutine support in the task process is done through the Server's [task_enable_coroutine](/server/setting?id=task_enable_coroutine) configuration and does not involve `Case 3`. This means the task process will not convert `sendMessage` to asynchronous IO just because asynchronous callback is enabled.
## What is EventLoop

The so-called `EventLoop`, also known as event loop, can be simply understood as `epoll_wait`, which adds all handles (fds) of events to `epoll_wait`, including events like readable, writable, error, etc.

The corresponding process is blocked on the `epoll_wait` kernel function. When an event occurs (or times out), the `epoll_wait` function will end the blocking and return the result, triggering the corresponding PHP function callback, for example, when receiving data from a client, it calls back the `onReceive` callback function.

When a large number of fds are placed in `epoll_wait` and many events occur simultaneously, the `epoll_wait` function will sequentially call the corresponding callback functions when it returns, which is called a round of event loop, namely IO multiplexing. Then it will block again to call `epoll_wait` for the next round of event loop.
## TCP Packet Boundary Issue

The code in [Fast Start](/start/start_tcp_server) can run normally without concurrency, but when concurrency is high, there will be TCP packet boundary issues. The `TCP` protocol solves the problems of order and packet loss retransmission in the underlying mechanism compared to the `UDP` protocol, but it also brings new issues. Since the `TCP` protocol is stream-oriented, data packets do not have boundaries, and applications using `TCP` communication will face these challenges, commonly known as TCP packet boundary problems.

Because `TCP` communication is stream-oriented, when receiving a large data packet, it may be split into multiple packets for transmission. Multiple `Send` operations at the lower level may also be combined into a single send operation. Two operations are needed here to solve this:

* Packet splitting: The server received multiple data packets and needs to split them.
* Packet merging: The data received by the server is only part of the packet and needs to cache the data, merging it into a complete packet.

Therefore, when working with TCP network communication, a communication protocol needs to be established. Common general TCP network communication protocols include `HTTP`, `HTTPS`, `FTP`, `SMTP`, `POP3`, `IMAP`, `SSH`, `Redis`, `Memcache`, `MySQL`.

It is worth mentioning that Swoole has built-in parsers for many common protocols to address the TCP packet boundary issues of these servers. It just requires simple configuration. Refer to [open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol).

In addition to common protocols, custom protocols can also be defined. Swoole supports two types of custom network communication protocols.

* **EOF Protocol**

The principle behind the `EOF` protocol is to add a special character at the end of each data packet to indicate the end of the packet. Protocols like `Memcache`, `FTP`, `SMTP` use `\r\n` as an end-of-packet marker. When sending data, just add `\r\n` at the end of the packet. When using the `EOF` protocol, be sure that the `EOF` does not appear in the middle of the data packet, as it can cause packet splitting errors.

In the code for the `Server` and `Client`, only two parameters need to be set to use the `EOF` protocol processing.

```php
$server->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
```

However, the performance of the above `EOF` configuration may be poor as Swoole will iterate over each byte to check for `\r\n`. Besides the method above, it can be configured as follows.

```php
$server->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
```
This set of configurations offer better performance as it does not need to iterate over data, but it can only solve the `packet splitting` problem, not the `packet merging` problem. This means that in `onReceive`, you may receive several requests from the client in one go and need to split them manually, for example using `explode("\r\n", $data)`. The main use of this configuration is for request-response style services (such as typing commands in a terminal) where there is no need to split the data. This is because the client must wait for the server to respond to a request before sending another one, avoiding sending two requests simultaneously.

* **Fixed-length Header + Body Protocol**

The fixed-length header approach is very common in server-side programs. This protocol's characteristic is that a data packet always consists of a header and a body. The header specifies the length of the body or the entire packet, and the length is usually represented using a 2-byte/4-byte integer. After receiving the header, the server can precisely control how much more data needs to be received to complete the data packet. Swoole's configuration can nicely support this protocol, and you can flexibly set 4 parameters to handle all situations.

In the `Server` within the [onReceive](/server/events?id=onreceive) callback function, when a protocol is set up, the `onReceive` event is triggered only when a complete data packet is received. When a protocol is set up for the client, calling [$client->recv()](/client?id=recv) no longer requires passing the length, and the `recv` function returns after receiving a complete data packet or encountering an error.

```php
$server->set(array(
    'open_length_check' => true,
    'package_max_length' => 81920,
    'package_length_type' => 'n', //see php pack()
    'package_length_offset' => 0,
    'package_body_offset' => 2,
));
```

!> For the specific meaning of each configuration, refer to the [Server/Client](/server/setting?id=open_length_check) section in the [Configuration](/server/setting?id=open_length_check) subsection.
## What is IPC

There are many ways for inter-process communication (IPC) between two processes on the same host. Swoole uses 2 methods: `Unix Socket` and `sysvmsg`. Below are the introductions for each:

- **Unix Socket**  

    Also known as UNIX Domain Socket, abbreviated as `UDS`, it uses socket API (socket, bind, listen, connect, read, write, close, etc.). Unlike TCP/IP, it does not require specifying an IP and port; instead, it is represented by a filename (e.g., `/tmp/php-fcgi.sock` between FPM and Nginx). UDS is a Linux kernel implementation for full in-memory communication without any IO overhead. In a test of 1 process writing and 1 process reading, each transferring 1024 bytes of data, 1 million communications only take 1.02 seconds. It is very powerful and the default IPC method used in Swoole.

    * **`SOCK_STREAM` and `SOCK_DGRAM`**  

        - There are two types of UDS communication in Swoole: `SOCK_STREAM` and `SOCK_DGRAM`, which can be simplified as the difference between TCP and UDP. When using `SOCK_STREAM`, the [TCP packet boundary problem](/learn?id=tcp数据包边界问题) still needs to be considered.
        - When using `SOCK_DGRAM`, the TCP packet boundary problem is not a concern. Each data sent via `send()` is boundary-preserving, meaning the size of data sent is the size received, with no issues of packet loss or disorder during transmission. The order of `send` writes and `recv` reads is completely consistent. Successful `send` guarantees successful `recv`.

    For IPC with small data transmissions, `SOCK_DGRAM` is very suitable. Due to the maximum 64k limit of each IP packet, when using `SOCK_DGRAM` for IPC, single data transmission cannot exceed 64k. Also, if the receiving speed is too slow and the OS buffer becomes full, packets may be dropped since UDP allows packet loss. Adjusting the buffer size accordingly is essential.

- **sysvmsg**
     
    This refers to the `message queue` provided by Linux. This IPC method uses a filename as a key for communication. It is not very flexible and is not widely used in practical projects.

    * **This IPC method is only useful in two scenarios:**

        - Preventing data loss: If the entire service crashes, the messages in the queue remain intact and can be consumed upon restarting. However, there is still a risk of dirty data.
        - External message delivery: For example, in Swoole, Worker processes can deliver tasks to Task processes via message queues, third-party processes can also deliver tasks to the queue for the Task to process, and even manually adding messages to the queue from the command line is possible.
This text is already in English and does not require translation.
### Master Process

* The Master process is a multi-threaded process, refer to [Process/Thread Structure Diagram](/server/init?id=process-thread-structure-diagram)
### Reactor Thread

* The Reactor thread is a thread created in the Master process.
* It is responsible for maintaining client TCP connections, handling network IO, managing protocols, and sending/receiving data.
* It does not execute any PHP code.
* It buffers, concatenates, and splits the data sent by TCP clients into complete request data packets.
### Worker Process

* Receive request data packets delivered by the `Reactor` thread and execute `PHP` callback functions to process the data
* Generate response data and send it to the `Reactor` thread, which in turn sends it to the `TCP` client
* Can operate in asynchronous non-blocking mode or synchronous blocking mode
* The `Worker` runs as multiple processes
### TaskWorker Process

* Receives tasks delivered by the `Worker` process via Swoole\Server->task/taskwait/taskCo/taskWaitMulti methods.
* Processes tasks and returns the result data to the `Worker` process (using Swoole\Server->finish).
* Operates in **synchronous blocking** mode.
* The `TaskWorker` runs in multiple processes. [Task full example](/start/start_task)
### Manager Process

* Responsible for creating/recycling `worker`/`task` processes

The relationship between them can be understood as `Reactor` being `nginx`, and `Worker` being `PHP-FPM`. The `Reactor` thread asynchronously processes network requests in parallel, and then forwards them to the `Worker` process for handling. Communication between `Reactor` and `Worker` is done through [unixSocket](/learn?id=what-is-ipc).

In applications using `PHP-FPM`, tasks are often asynchronously dispatched to queues like `Redis`, and some `PHP` processes are started in the background to handle these tasks asynchronously. The `TaskWorker` provided by `Swoole` is a more complete solution that integrates task dispatching, queues, and management of `PHP` task processing processes into one. By using the provided low-level `API`, asynchronous task processing can be easily implemented. Additionally, `TaskWorker` can return a result feedback to the `Worker` after the task is completed.

The `Reactor`, `Worker`, and `TaskWorker` of `Swoole` can be closely combined to provide more advanced usage.

A more colloquial analogy would be, if a `Server` is a factory, then `Reactor` is sales, receiving customer orders. `Worker` is the worker, who, when receiving an order from sales, works to produce what the customer wants. And `TaskWorker` can be understood as administrative staff, who can help `Worker` with miscellaneous tasks, allowing `Worker` to focus on their work.

As shown in the diagram:

![process_demo](_images/server/process_demo.png)
## Introduction to the two operation modes of Server

In the third parameter of the `Swoole\Server` constructor, two constant values can be filled - [SWOOLE_BASE](/learn?id=swoole_base) or [SWOOLE_PROCESS](/learn?id=swoole_process). Below we will introduce the differences and advantages and disadvantages of these two modes.
### SWOOLE_PROCESS

In SWOOLE_PROCESS mode, all client TCP connections of the `Server` are established with the main process, which has a complex internal implementation involving a lot of inter-process communication and process management mechanisms. This mode is suitable for scenarios with very complex business logic. Swoole provides comprehensive process management and memory protection mechanisms in this mode. Even in cases of very complex business logic, it can operate stably for long periods.

Swoole provides `Buffer` functionality in the Reactor thread to handle a large number of slow connections and malicious clients sending data byte by byte.
#### Advantages of process mode:

* The connection and data request transmission are separate, which prevents imbalance in the `Worker` process due to varying data volumes in different connections.
* When a fatal error occurs in a `Worker` process, the connection will not be disconnected.
* Single connection concurrency can be implemented, maintaining only a few `TCP` connections, and requests can be processed concurrently in multiple `Worker` processes.
#### Disadvantages of Process Mode:

* There is an overhead of `2` times IPC. `Master` process and `Worker` process need to communicate using [unixSocket](/learn?id=What is IPC)
* `SWOOLE_PROCESS` does not support PHP ZTS. In this case, you can only use `SWOOLE_BASE` or set [single_thread](/server/setting?id=single_thread) to true
### SWOOLE_BASE

The SWOOLE_BASE mode is a traditional asynchronous non-blocking `Server`. It is completely identical to programs like `Nginx` and `Node.js`.

The [worker_num](/server/setting?id=worker_num) parameter is still valid for the `BASE` mode, and will start multiple `Worker` processes.

When a TCP connection request comes in, all worker processes compete for this connection, and eventually one worker process successfully establishes a TCP connection with the client, and all data transmission and reception of this connection are directly communicated with this worker, bypassing the main process's Reactor thread forwarding.

- In `BASE` mode, there is no `Master` process role, only a [Manager](/learn?id=manager-process) process role.
- Each `Worker` process simultaneously assumes responsibilities of both the [Reactor](/learn?id=reactor-thread) thread in SWOOLE_PROCESS mode and the `Worker` process.
- In `BASE` mode, the `Manager` process is optional. When `worker_num=1` is set and neither the `Task` nor `MaxRequest` features are used, a single `Worker` process will be created directly at the bottom layer without creating a `Manager` process.
#### Advantages of BASE mode:

* BASE mode has no IPC overhead, hence better performance
* BASE mode code is simpler and less prone to errors
#### Disadvantages of the BASE mode:

* TCP connections are maintained in the Worker process, so when a Worker process fails, all connections within that Worker will be closed.
* A small number of TCP long connections cannot utilize all Worker processes.
* TCP connections are bound to Workers. In applications with long connections where some connections have large data volume, the Worker process where these connections exist will have a very high load. However, connections with small data volume will lead to low load on the Worker process, resulting in an imbalance across different Worker processes.
* If there are blocking operations in callback functions, the server will degrade to synchronous mode, which can lead to the TCP backlog queue being filled up.
#### Applicable scenarios of BASE mode:

If there is no need for interaction between client connections, the `BASE` mode can be used. Examples include `Memcache`, `HTTP` servers, etc.
#### Restrictions of the BASE mode:

In the `BASE` mode, [Server methods](/server/methods) other than [send](/server/methods?id=send) and [close](/server/methods?id=close) **do not support** cross-process execution.

!> In the v4.5.x version, only the `send` method supports cross-process execution in the `BASE` mode; in the v4.6.x version, only the `send` and `close` methods support it.
## What are the differences between Process, Process Pool, and UserProcess: id=process-diff
### Process

[Process](/process/process) is the process management module provided by Swoole, used to replace PHP's `pcntl`.

* It can easily achieve inter-process communication.
* Supports redirecting standard input and output, so that in the child process, `echo` will not print to the screen but will write to the pipe. Reading keyboard input can be redirected to read data from the pipe.
* Provides the [exec](/process/process?id=exec) interface, the created process can execute other programs, and it can easily communicate with the original `PHP` parent process.

!> The `Process` module cannot be used in coroutine environment. You can use `runtime hook`+`proc_open` to achieve it. Refer to [Coroutine Process Management](/coroutine/proc_open).
### Process\Pool

[Process\Pool](/process/process_pool) encapsulates the process management module of the server into a PHP class, supporting the use of Swoole's process manager in PHP code.

In practical projects, it is often necessary to write long-running scripts, such as multi-process queue consumers based on `Redis`, `Kafka`, `RabbitMQ`, multi-process crawlers, and so on. Developers need to use the `pcntl` and `posix` related extension libraries to implement multi-process programming. However, developers also need to have a deep understanding of Linux system programming, otherwise problems are likely to occur. Using the process manager provided by Swoole can greatly simplify the work of programming multi-process scripts.

- Ensure the stability of working processes;
- Support signal processing;
- Support message queue and `TCP-Socket` message delivery functions;
### UserProcess

`UserProcess` is a user-defined working process added using [addProcess](/server/methods?id=addprocess). It is typically used to create a special working process for monitoring, reporting, or other specific tasks.

Although `UserProcess` is managed by the [Manager process](/learn?id=manager-process), it is relatively independent compared to the [Worker process](/learn?id=worker-process) and is designed for executing custom functions.
