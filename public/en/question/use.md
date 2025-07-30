Sorry, could you please provide more context or let me know what specific questions or issues you would like help with? Thank you.
## How is the performance of Swoole

> QPS comparison

Use Apache-Bench tool (ab) to perform pressure tests on Nginx static pages, Golang HTTP programs, and PHP7+Swoole HTTP programs. In benchmark tests of performing 1 million HTTP requests with a concurrency of 100 on the same machine, the QPS comparison is as follows:

| Software | QPS | Software Version |
| --- | --- | --- |
| Nginx | 164489.92 | nginx/1.4.6 (Ubuntu) |
| Golang | 166838.68 | go version go1.5.2 linux/amd64 |
| PHP7+Swoole | 287104.12 | Swoole-1.7.22-alpha |
| Nginx-1.9.9 | 245058.70 | nginx/1.9.9 |

!> Note: For the test of Nginx-1.9.9, access_log has been disabled, and open_file_cache has been used to cache static files in memory.

> Test environment

* CPU: Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* Memory: 16G
* Disk: 128G SSD
* Operating System: Ubuntu 14.04 (Linux 3.16.0-55-generic)

> Pressure test method

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> VHOST configuration

```nginx
server {
    listen 80 default_server;
    root /data/webroot;
    index index.html;
}
```

> Test page

```html
<h1>Hello World!</h1>
```

> Number of processes

Nginx has 4 Worker processes enabled
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12月07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12月07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12月07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12月07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12月07   0:45 nginx: worker process
```

> Golang

Test code

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

PHP7 has enabled the `OPcache` accelerator.

Test code

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **TechEmpower Web Framework Benchmarks** - authoritative performance testing of global web frameworks

Latest benchmark score results: [TechEmpower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swoole leads in being **the first in dynamic languages**

Database IO operation tests using basic business code with no special optimizations

**Outperforms all static language frameworks (using MySQL instead of PostgreSQL)**
## How does Swoole maintain TCP long connections

There are 2 sets of configurations for maintaining TCP long connections, [tcp_keepalive](/server/setting?id=open_tcp_keepalive) and [heartbeat](/server/setting?id=heartbeat_check_interval).
## How to properly restart the Swoole service

In daily development, after modifying PHP code, it is often necessary to restart the service to make the code effective. A busy backend server is always processing requests. If an administrator terminates/restarts the server program through the `kill` process, it may cause the code to terminate right in the middle of execution, which cannot guarantee the integrity of the entire business logic.

`Swoole` provides a mechanism for graceful termination/restart. Administrators only need to send a specific signal to the `Server` or call the `reload` method, and the worker process can be terminated and restarted. Please refer to [reload()](/server/methods?id=reload) for details.

However, there are a few points to note:

Firstly, it is important to ensure that the newly modified code must be reloaded in the `OnWorkerStart` event to take effect. For example, if a class is autoloaded through Composer before `OnWorkerStart`, it will not work.

Secondly, `reload` needs to be combined with two parameters, [max_wait_time](/server/setting?id=max_wait_time) and [reload_async](/server/setting?id=reload_async). With these two parameters set, `asynchronous safe restart` can be achieved.

Without this feature, when a Worker process receives a restart signal or reaches [max_request](/server/setting?id=max_request), it will immediately stop the service, and there may still be event listeners in the `Worker` process, causing asynchronous tasks to be discarded. Setting the above parameters will first create a new `Worker`, and the old `Worker` will exit on its own after completing all events, i.e., `reload_async`.

If the old `Worker` does not exit, the underlying layer will add a timer, and if the old `Worker` does not exit within the specified time ([max_wait_time](/server/setting?id=max_wait_time) seconds), the underlying layer will forcefully terminate it, resulting in a [WARNING](/question/use?id=forced-to-terminate) error.

Example:

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tClient[$fd] receive data: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

For example, in the above code, if `reload_async` is not used, the timer created in `onReceive` will be lost, and there will be no opportunity to handle the callback functions in the timer.
### Process Exit Event

In order to support the asynchronous restart feature, a new [onWorkerExit](/server/events?id=onWorkerExit) event has been added at the underlying level. When the old `Worker` is about to exit, the `onWorkerExit` event will be triggered. In the callback function of this event, the application layer can attempt to clean up certain long-lived `Socket` connections until there are no more fds in the [event loop](/learn?id=what-is-eventloop) or the process exits after reaching the [max_wait_time](/server/setting?id=max_wait_time).

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

Moreover, in [Swoole Plus](https://www.swoole.com/swoole_plus), a feature for detecting file changes has been added. This allows workers to automatically restart without the need for manual reload or signal sending when files change.
## Why is it unsafe to close immediately after sending?

It is unsafe to close immediately after sending, whether on the server side or the client side.

Successful sending only indicates that the data has been written to the operating system's socket buffer, not that the receiving end has actually received the data. There is no way to guarantee whether the OS has successfully sent, the server has received, or the server-side program has processed the data.

> For the logic after closing, please see the related linger settings below.

This logic is similar to a phone conversation. A tells B something, and once A finishes speaking, A hangs up. A does not know if B heard it. If A finishes, and then B confirms, and then B hangs up, it is definitely safe.

Linger Settings

When a socket is closed, if there is still data in the buffer, the OS will handle it based on the linger setting.

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0: The close returns immediately, and the OS will send any unsent data before releasing resources, leading to a graceful exit.
* l_onoff != 0, l_linger = 0: The close returns immediately but does not send unsent data; instead, it forcefully closes the socket descriptor with an RST packet, resulting in a forceful exit.
* l_onoff !=0, l_linger > 0: The close does not return immediately; the kernel delays for a period determined by l_linger. If the unsent data (including FIN packet) is sent and acknowledged by the other end before the timeout, the close returns successfully, leading to a graceful exit of the socket descriptor. Otherwise, the close will return an error, the unsent data is lost, and the socket descriptor is forcefully closed. If the socket descriptor is set as non-blocking, the close will return a value directly.
## client has already been bound to another coroutine

For a `TCP` connection, Swoole's underlying mechanism only allows one coroutine for reading and one coroutine for writing at a time. This means that multiple coroutines cannot read or write to the same TCP connection simultaneously, otherwise the underlying layer will throw a binding error:

```shell
Fatal error: Uncaught Swoole\Error: Socket#6 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed 
```

Reproducible code:

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

Solution reference: https://wenda.swoole.com/detail/107474

!> This limitation is effective in all multi-coroutine environments. One common scenario is sharing a TCP connection in callbacks like [onReceive](/server/events?id=onreceive), as these callbacks automatically create a coroutine. For cases requiring connection pooling, `Swoole` provides a built-in [connection pool](/coroutine/conn_pool) which can be directly utilized, or you can manually manage a connection pool using `channel`.
## Call to undefined function Co\run()

Most of the examples in this document use `Co\run()` to create a coroutine container. [Understand what a coroutine container is](/coroutine?id=what-is-a-coroutine-container)

If you encounter the following errors:

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

It means that your `Swoole` extension version is lower than `v4.4.0` or you have manually disabled [coroutine short names](/other/alias?id=coroutine-short-names). Here are some solutions:

* If the version is too low, upgrade the extension version to `>= v4.4.0` or use the `go` keyword instead of `Co\run` to create a coroutine;
* If coroutine short names are disabled, please enable [coroutine short names](/other/alias?id=coroutine-short-names);
* Replace `Co\run` or `go` with `Coroutine::create` to create a coroutine;
* Use the full name: `Swoole\Coroutine\run`.
## Can we share one Redis or MySQL connection

Absolutely not. Each process must create its own `Redis`, `MySQL`, `PDO` connection, and the same goes for other storage clients. The reason is if you share one connection, then it cannot be guaranteed which process will handle the returned results. In theory, any process holding the connection can read and write to this connection, which would result in data corruption.

**Therefore, under no circumstances should connections be shared between multiple processes.**

- In [Swoole\Server](/server/init), connection objects should be created in the [onWorkerStart](/server/events?id=onworkerstart) event.
- In [Swoole\Process](/process/process), connection objects should be created in the callback function of the child process after [Swoole\Process->start](/process/process?id=start).
- The information described in this issue is also applicable to programs using `pcntl_fork`.

Example:

```php
$server = new Swoole\Server('0.0.0.0', 9502);

// Redis/MySQL connection must be created in the onWorkerStart event
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```
## Connection Closed Issue

Such as the following prompt

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

When the server responds, the client has already disconnected, causing this issue.

Common scenarios include:

* Browser constantly refreshing the page (before it is fully loaded)
* Cancelling ab testing halfway through
* wrk time-based stress testing (requests not completed before the time cutoff will be cancelled)

The above scenarios are all normal phenomena and can be ignored, hence the error level for this is NOTICE.

If a large number of connections are inexplicably disconnected due to other reasons, then attention is required.

```bash
WARNING swWorker_discard_data (ERRNO 1007): [2] received the wrong data[21 bytes] from socket#75

WARNING Worker_discard_data (ERRNO 1007): [2] ignore data[5 bytes] received from session#2
```

Similarly, this error also indicates that the connection has been closed, and the received data will be discarded. Refer to [discard_timeout_request](/server/setting?id=discard_timeout_request)
## Inconsistency between the `connected` attribute and connection status

Since the 4.x coroutine version, the `connected` attribute will no longer be updated in real time, and the [isConnect](/client?id=isconnected) method is no longer reliable.
### Reason

The goal of coroutines is to be consistent with synchronous blocking programming models. In synchronous blocking models, there is no concept of real-time updating of connection status. For example, PDO, curl, etc., do not have the concept of connections. It is only when errors are returned or exceptions are thrown during I/O operations that a connection disconnection can be discovered.

The general practice at the underlying level of Swoole is that when there is an I/O error, it returns false (or empty content to indicate disconnection) and sets the corresponding error code and error message on the client object.
### Note

Although the previous asynchronous version supports "real-time" updates of the `connected` attribute, it is not actually reliable. The connection may disconnect immediately after you check it.
## What does "Connection refused" mean

When "Connection refused" occurs while trying to telnet to 127.0.0.1 port 9501, it means that the server is not listening on this port.

* Check if the program is running successfully: ps aux
* Check if the port is being listened on: netstat -lp
* Check if the network communication process is normal: tcpdump traceroute
## Resource temporarily unavailable [11]

The client `swoole_client` reported an error when calling `recv`:

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```

This error indicates that the server did not return any data within the specified time frame, resulting in a receive timeout.

- You can use `tcpdump` to check the network communication process and see if the server is sending data.
- Check if the `$serv->send` function on the server is returning `true`.
- In cases of longer delays in external network communication, consider increasing the timeout setting for `swoole_client`.
## worker exit timeout, forced to terminate :id=forced-to-terminate

You may encounter the following error message:

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```

This error message indicates that the Worker did not exit within the specified time ([max_wait_time](/server/setting?id=max_wait_time) seconds) and the Swoole framework forcefully terminated the process.

You can reproduce this issue with the following code snippet:

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```
I found an error message like this:

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```

This usually indicates that data was sent to a disconnected connection, often because the return value of the sending operation was not checked. If the send operation fails, sending should not continue.
## What basic knowledge do you need to master Swoole
### Multiprocessing/Multithreading

* Understand the concepts of processes and threads in the `Linux` operating system
* Understand the basics of process/thread switching and scheduling in `Linux`
* Understand the basics of interprocess communication, such as pipes, `UnixSocket`, message queues, shared memory
### SOCKET

* Understand basic operations of `SOCKET` such as `accept/connect`, `send/recv`, `close`, `listen`, `bind`
* Understand concepts like receive buffer, send buffer, blocking/non-blocking, timeout, etc. of `SOCKET`
### IO Multiplexing

* Understand `select` / `poll` / `epoll`
* Understand event loop based on `select` / `epoll`, `Reactor` model
* Understand readable events, writable events
### TCP/IP Network Protocol

* Understand the `TCP/IP` protocol
* Understand the `TCP` and `UDP` transport protocols
### Debugging Tools

* Use [gdb](/other/tools?id=gdb) to debug `Linux` programs
* Use [strace](/other/tools?id=strace) to trace system calls of processes
* Use [tcpdump](/other/tools?id=tcpdump) to trace network communication processes
* Other `Linux` system tools such as ps, [lsof](/other/tools?id=lsof), top, vmstat, netstat, sar, ss, etc.
## Object of class Swoole\Curl\Handler could not be converted to int

An error occurred when using [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl):

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```

The reason is that the hooked cURL is no longer of resource type, but of object type, so it cannot be converted to int.

!> It is recommended to contact the SDK provider to modify the code. In PHP 8, cURL is no longer of resource type but object type.

There are three solutions:

1. Do not enable [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl). However, starting from version [v4.5.4](/version/log?id=v454), [SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all) includes [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) by default. You can set it as `SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL` to disable [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl).

2. Use Guzzle SDK, which allows for replacing Handler to enable coroutine support.

3. Starting from Swoole `v4.6.0`, you can use [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl) as a replacement for [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl).
## Use one-click coroutine and Guzzle 7.0+ at the same time, and output the result directly on the terminal :id=hook_guzzle

Reproduction code as follows

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// For versions prior to v4.5.4
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// The request result will be output directly instead of being printed
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> The solution is the same as the previous issue. However, this problem has been fixed in Swoole version >= `v4.5.8`.
## Error: No buffer space available[55]

You can ignore this error. This error occurs when the `socket_buffer_size` option is set too large, which some systems do not accept, but it does not affect the program's operation.
## Maximum Size of GET/POST Requests
### Maximum 8192 for GET Requests

GET requests have only one HTTP header. Swoole uses a fixed-size memory cache of 8K at the bottom layer, and it cannot be modified. If the request is not a correct HTTP request, an error will occur. The following error will be thrown at the bottom layer:

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```
### POST File Upload

The maximum size is limited by the [package_max_length](/server/setting?id=package_max_length) configuration item, which defaults to 2M. You can call [Server->set](/server/methods?id=set) to pass in a new value to modify the size. Swoole is fully memory-based at the underlying level, so setting it too large may lead to a server resource exhaustion due to a large number of concurrent requests.

Formula: `Max memory usage` = `Max concurrent requests` * `package_max_length`
