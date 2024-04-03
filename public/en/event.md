# Event

The `Swoole` extension also provides interfaces to directly operate the underlying `epoll/kqueue` event loop. Other extensions' created sockets, sockets created by `stream/socket` extensions in PHP code, etc., can be added to Swoole's [EventLoop](/learn?id=what-is-eventloop),
otherwise, if a third party's `$fd` is synchronous IO, it will cause Swoole's EventLoop to not execute, [refer to the case study](/learn?id=sync-io-to-async-io).

!> The `Event` module is relatively low-level and is a basic encapsulation of `epoll`. Users are recommended to have experience in IO multiplexing programming.
## Event Priority

1. Signal handling callback functions set by `Process::signal`
2. Timer callback functions set by `Timer::tick` and `Timer::after`
3. Delayed execution functions set by `Event::defer`
4. Periodic callback functions set by `Event::cycle"
## Methods
### add()

Add a `socket` to the underlying `reactor` event listener. This function can be used in `Server` or `Client` mode.
```php
Swoole\Event::add(mixed $sock, callable $read_callback, callable $write_callback = null, int $flags = null): bool
```

!> When used in a `Server` program, it must be called after the `Worker` process starts. No asynchronous `IO` interface should be called before `Server::start`.

* **Parameters**

  * **`mixed $sock`**
    * **Function**: File descriptor, `stream` resource, `sockets` resource, `object`
    * **Default value**: None
    * **Other values**: None

  * **`callable $read_callback`**
    * **Function**: Read event callback function
    * **Default value**: None
    * **Other values**: None

  * **`callable $write_callback`**
    * **Function**: Write event callback function [This parameter can be a string function name, object+method, class static method, or anonymous function. It will call the specified function when this `socket` is readable or writable.]
    * **Default value**: None
    * **Other values**: None

  * **`int $flags`**
    * **Function**: Event type mask [Can choose to close/open read and write events, such as `SWOOLE_EVENT_READ`, `SWOOLE_EVENT_WRITE`, or `SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE`]
    * **Default value**: None
    * **Other values**: None

* **4 types of $sock**

Type | Description
---|---
int | File descriptor, includes `Swoole\Client->$sock`, `Swoole\Process->$pipe`, or other `fd`
stream resource | Resource created by `stream_socket_client`/`fsockopen`
sockets resource | Resource created in the `sockets` extension using `socket_create`, needs to be included at compile time with [./configure --enable-sockets](/environment?id=compilation-options)
object | `Swoole\Process` or `Swoole\Client`, automatically converted to [UnixSocket](/learn?id=what-is-IPC) (Process) or the client's connection socket (Swoole\Client)

* **Return Value**

  * Returns `true` if adding event listener is successful
  * Returns `false` if the addition fails, please use `swoole_last_error` to get the error code
  * The socket that has already been added cannot be added again, you can use `swoole_event_set` to modify the callback function and event type of the socket

  !> When using `Swoole\Event::add` to add a socket to the event listener, the underlying will automatically set the socket to non-blocking mode

* **Usage Example**

```php
$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Swoole\Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    // Remove the socket from the epoll event after socket processing is complete
    Swoole\Event::del($fp);
    fclose($fp);
});
echo "Finish\n";  // Swoole\Event::add does not block the process, this line will be executed sequentially
```

* **Callback Functions**

  * In the readable `($read_callback)` event callback function, functions like `fread`, `recv` must be used to read data from the socket buffer. Otherwise, the event will continue to trigger. If you do not want to continue reading, you must use `Swoole\Event::del` to remove the event listener.
  * In the writeable `($write_callback)` event callback function, after writing to the socket, you must call `Swoole\Event::del` to remove the event listener. Otherwise, the writeable event will continue to trigger.
  * If executing functions like `fread`, `socket_recv`, `socket_read`, `Swoole\Client::recv` return `false` and the error code is `EAGAIN`, it means that there is no data in the socket receive buffer. In this case, you need to add a readable listener and wait for the [EventLoop](/learn?id=what-is-eventloop) to notify.
  * If executing functions like `fwrite`, `socket_write`, `socket_send`, `Swoole\Client::send` returns `false` and the error code is `EAGAIN`, it means that the socket send buffer is full and data cannot be sent temporarily. You need to listen for writable events and wait for the [EventLoop](/learn?id=what-is-eventloop) to notify.
### set()

Modify the callback function and mask for event listening.

```php
Swoole\Event::set($fd, mixed $read_callback, mixed $write_callback, int $flags): bool
```

* **Parameters**

  * The parameters are exactly the same as [Event::add](/event?id=add). If the passed `$fd` does not exist in the [EventLoop](/learn?id=what-is-an-eventloop), it returns `false`.
  * When `$read_callback` is not `null`, it will modify the callback function for the readable event to the specified function.
  * When `$write_callback` is not `null`, it will modify the callback function for writable event to the specified function.
  * `$flags` can close/open, readable (`SWOOLE_EVENT_READ`), and writable (`SWOOLE_EVENT_WRITE`) event listening.

  !> Note that if the `SWOOLE_EVENT_READ` event is being listened to, but the `read_callback` is not currently set, the bottom layer will return `false` directly, and the addition will fail. The same applies to `SWOOLE_EVENT_WRITE`.

* **State Changes**

  * If a readable event callback is set using `Event::add` or `Event::set`, but the `SWOOLE_EVENT_READ` readable event is not being listened to, the bottom layer only saves the information of the callback function without triggering any event callbacks.
  * You can use `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)` to modify the type of events being listened to. In this case, the bottom layer will trigger a readable event.

* **Release Callback Functions**

!> Note that `Event::set` can only replace the callback functions but cannot release event callback functions. For example, `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`, passing `null` to the `read_callback` and `write_callback` in the parameters indicates that no modifications are made to the callback functions set by `Event::add`, rather than setting the event callback functions to `null`.

The bottom layer will only release the `read_callback` and `write_callback` event callback functions when you call `Event::del` to clear the event listening.
### isset()

Detect whether the `$fd` passed in has been added to event listening.

```php
Swoole\Event::isset(mixed $fd, int $events = SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE): bool
```

* **Parameters**

  * **`mixed $fd`**
    * **Functionality**: Arbitrary socket file descriptor 【refer to [Event::add](/event?id=add) documentation】
    * **Default Value**: None
    * **Other Values**: None

  * **`int $events`**
    * **Functionality**: Type of event to check
    * **Default Value**: None
    * **Other Values**: None

* **$events**

Event Type | Description
---|---
`SWOOLE_EVENT_READ` | Whether to listen for readable events
`SWOOLE_EVENT_WRITE` | Whether to listen for writable events
`SWOOLE_EVENT_READ \| SWOOLE_EVENT_WRITE` | Listen for readable or writable events

* **Usage Example**

```php
use Swoole\Event;

$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    Swoole\Event::del($fp);
    fclose($fp);
}, null, SWOOLE_EVENT_READ);
var_dump(Event::isset($fp, SWOOLE_EVENT_READ)); //returns true
var_dump(Event::isset($fp, SWOOLE_EVENT_WRITE)); //returns false
var_dump(Event::isset($fp, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)); //returns true
```
### write()

Used for sockets created with the PHP extension `stream/sockets`, it is used to send data to the peer using functions like `fwrite` or `socket_send`. When sending a large amount of data, if the socket write buffer is full, it will either block the send operation or return an [EAGAIN](/other/errno?id=linux) error.

The `Event::write` function can make the data sending of `stream/sockets` resources **asynchronous**. When the buffer is full or an [EAGAIN](/other/errno?id=linux) error occurs, Swoole's underlying layer will add the data to the send queue and monitor the socket for writability. When the socket is writable, Swoole will automatically write the data.

```php
Swoole\Event::write(mixed $fd, mixed $data): bool
```

* **Parameters** 

  * **`mixed $fd`**
    * **Function** : Any socket file descriptor 【Refer to the [Event::add](/event?id=add) documentation】
    * **Default Value** : None
    * **Other Values** : None

  * **`mixed $data`**
    * **Function** : Data to be sent 【The length of the data to be sent must not exceed the size of the `Socket` buffer】
    * **Default Value** : None
    * **Other Values** : None

!> `Event::write` cannot be used on `stream/sockets` resources with tunnel encryption such as `SSL/TLS`  
After a successful `Event::write` operation, the `$socket` will automatically be set to non-blocking mode.

* **Usage Example**

```php
use Swoole\Event;

$fp = stream_socket_client('tcp://127.0.0.1:9501');
$data = str_repeat('A', 1024 * 1024*2);

Event::add($fp, function ($fp) {
     echo fread($fp);
});

Event::write($fp, $data);
```
#### Swoole's underlying logic when the SOCKET buffer is full

When continuously writing to the SOCKET while the peer is not reading fast enough, the SOCKET buffer will become full. Swoole's underlying logic will store the data in a memory buffer until a write event is triggered to write to SOCKET.

If the memory buffer is also full, Swoole's underlying logic will throw an error `pipe buffer overflow, reactor will block.` and enter a blocking state.

Note: When the buffer is full, returning `false` is an atomic operation, indicating that either the entire write was successful or failed completely.
### del()

Remove the listening `socket` from the `reactor`. `Event::del` should be used in pairs with `Event::add`.

```php
Swoole\Event::del(mixed $sock): bool
```

!> You must use `Event::del` to remove the event listener before the `close` operation of the `socket`, otherwise it may cause memory leaks.

* **Parameters**

  * **`mixed $sock`**
    * **Functionality**: File descriptor of the socket
    * **Default value**: None
    * **Other values**: None
### exit()

Exit the event loop.

!> This function is only valid in the `Client` program.

```php
Swoole\Event::exit(): void
```
### defer()

Execute the function at the beginning of the next event loop.

```php
Swoole\Event::defer(mixed $callback_function);
```

!> The callback function of `Event::defer` will be executed after the current `EventLoop`'s event loop ends and before the next event loop begins.

* **Parameters**

  * **`mixed $callback_function`**
    * **Functionality**: The function to be executed when the time expires 【It must be callable. The callback function does not accept any arguments. You can pass parameters to the callback function using the `use` syntax of anonymous functions; adding new `defer` tasks during the execution of the `$callback_function` function will still be completed within the current event loop】
    * **Default**: None
    * **Other values**: None

* **Usage Example**

```php
Swoole\Event::defer(function(){
    echo "After EventLoop\n";
});
```
### cycle()

Define a function to be executed at the end of each event loop cycle. This function will be called after each round of event loop completes.

```php
Swoole\Event::cycle(callable $callback, bool $before = false): bool
```

* **Parameters**

  * **`callable $callback_function`**
    * **Functionality**: Callback function to be set. The `$callback` being `null` means clearing the `cycle` function. If `cycle` function is already set, setting it again will override the previous setting.
    * **Default value**: None
    * **Other values**: None

  * **`bool $before`**
    * **Functionality**: Call this function before the [EventLoop](/learn?id=什么是eventloop)
    * **Default value**: None
    * **Other values**: None

!> Both `before=true` and `before=false` callback functions can coexist.

  * **Usage Example**

```php
Swoole\Timer::tick(2000, function ($id) {
    var_dump($id);
});

Swoole\Event::cycle(function () {
    echo "hello [1]\n";
    Swoole\Event::cycle(function () {
        echo "hello [2]\n";
        Swoole\Event::cycle(null);
    });
});
```
### wait()

Start event listening.

!> Please place this function at the end of the PHP program

```php
Swoole\Event::wait();
```

* **Usage Example**

```php
Swoole\Timer::tick(1000, function () {
    echo "hello\n";
});

Swoole\Event::wait();
```
### dispatch()

Start event listener.

!> Only perform the `reactor->wait` operation once, which is equivalent to manually calling `epoll_wait` once on the `Linux` platform. Different from `Event::dispatch`, `Event::wait` maintains a loop internally.

```php
Swoole\Event::dispatch();
```

* **Usage Example**

```php
while(true)
{
    Event::dispatch();
}
```

The purpose of this function is to be compatible with some frameworks, such as `amp`, which internally controls the `reactor` loop in the framework. Using `Event::wait`, Swoole maintains control at the bottom, so it cannot be yielded to the framework.
