# TCP Server

## Program Code

Please write the following code into `tcpServer.php`.

```php
// Create a Server object listening on 127.0.0.1:9501.
$server = new Swoole\Server('127.0.0.1', 9501);

// Listen for the 'Connect' event.
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// Listen for the 'Receive' event.
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// Listen for the 'Close' event.
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// Start the server.
$server->start(); 
```

This creates a TCP server that listens on port `9501` on the local machine. The logic is straightforward: when a client's socket sends a `hello` string over the network, the server responds with `Server: hello`.

Since `Server` is an asynchronous server, the program is written by listening to events. When a specific event occurs, the underlying layer will invoke the specified functions. For example, when a new TCP connection is established, the `onConnect` event callback will be executed, and when a connection sends data to the server, the `onReceive` function will be called.

* The server can handle connections from thousands of clients simultaneously, and `$fd` is the unique identifier for a client connection.
* Using `$server->send()` method, data can be sent to a client connection identified by `$fd`.
* `$server->close()` method can forcefully close a specific client connection.
* Clients may disconnect actively, which triggers the `onClose` event callback.

## Running the Program

```shell
php tcpServer.php
```

Run the `server.php` program in the command line. Once started successfully, you can use the `netstat` tool to see that it is listening on port `9501`.

You can now connect to the server using `telnet/netcat` tools.

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```

## Simple Checks for Inability to Connect to the Server

* In Linux, use `netstat -an | grep <port>` to see if the port is open and in a `Listening` state.
* Confirm the firewall settings after the previous step.
* Pay attention to the IP address the server is using; if it is `127.0.0.1`, clients can only connect using `127.0.0.1`.
* If using Alibaba Cloud or Tencent Cloud services, ensure that the necessary ports are open in the security group settings.

## TCP Data Packet Boundary Issue

Refer to [TCP Data Packet Boundary Issue](/learn?id=tcp数据包边界问题).
