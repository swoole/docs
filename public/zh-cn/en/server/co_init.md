# Server (Coroutine Style) <!-- {docsify-ignore-all} -->

The difference between `Swoole\Coroutine\Server` and the [asynchronous style](/server/init) server lies in the fact that `Swoole\Coroutine\Server` is a server fully implemented in coroutine style, refer to the [complete example](/coroutine/server?id=complete-example).

## Advantages:

- No need to set event callback functions. Establishing connections, receiving data, sending data, and closing connections all happen sequentially without the concurrency issues of [asynchronous style](/server/init), for example:

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

// Listen for connection events
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379); // The Coroutine of OnConnect will suspend here
    Co::sleep(5); // Simulating a slow connect situation
    $redis->set($fd, "fd $fd connected");
});

// Listen for data receive events
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379); // The Coroutine of OnReceive will suspend here
    var_dump($redis->get($fd)); // It is possible that the Redis connection of the OnReceive coroutine is established before the set above is executed, resulting in a logical error
});

// Listen for connection close events
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Start the server
$serv->start();
```

In the asynchronous style server above, it cannot guarantee the order of events, meaning it cannot ensure that `onConnect` is executed before entering `onReceive`. This is because after enabling coroutine, both `onConnect` and `onReceive` callbacks will automatically create coroutines, leading to coroutine scheduling when encountering IO operations, causing an issue with the scheduling order, which is not a problem in the coroutine style server.

- The coroutine style server can dynamically start and stop services, while the asynchronous style server is unable to perform any actions once `start()` is called.

## Disadvantages:

- The coroutine style server does not automatically create multiple processes and requires the use of the [Process\Pool](/process/process_pool) module to make use of multiple cores.
- The coroutine style server is actually a encapsulation of the [Co\Socket](/coroutine_client/socket) module, so using coroutine style requires some experience in socket programming.
- Currently, the level of encapsulation is not as high as that of the asynchronous style server, so some functionalities need to be manually implemented, such as handling `reload` functionality by listening to signals.
