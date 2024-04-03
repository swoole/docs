# Multi-port listening

`Swoole\Server` can listen on multiple ports, and each port can be set to handle different protocols. For example, port 80 can handle the HTTP protocol, while port 9507 can handle the TCP protocol. Encrypted transmission using SSL/TLS can also be enabled only for specific ports.

For example, if the main server uses the WebSocket or HTTP protocol, a new TCP port that is listened on (the return value of the `listen` method, referred to as the `Swoole\Server\Port` object hereinafter) will inherit the protocol setting of the main server by default. You must separately call the `set` method and `on` method of the `port` object to set a new protocol in order to enable the new protocol.
## Listen on New Ports

```php
// Return port object
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```  
## Set network protocol

```php
// Call the set method of the port object
$port1->set([
	'open_length_check' => true,
	'package_length_type' => 'N',
	'package_length_offset' => 0,
	'package_max_length' => 800000,
]);

$port3->set([
	'open_eof_split' => true,
	'package_eof' => "\r\n",
	'ssl_cert_file' => 'ssl.cert',
	'ssl_key_file' => 'ssl.key',
]);
```
## Setting Callback Functions

```php
// Set callback functions for each port
$port1->on('connect', function ($serv, $fd){
    echo "Client: Connect.\n";
});

$port1->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});

$port1->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$port2->on('packet', function ($serv, $data, $addr) {
    var_dump($data, $addr);
});
```

!> You can click [here](/server/server_port) to see detailed explanations of `Swoole\Server\Port`.
## Http/WebSocket

`Swoole\Http\Server` and `Swoole\WebSocket\Server` cannot create HTTP or WebSocket servers by calling the `listen` method of a `Swoole\Server` instance because they are implemented using subclass inheritance.

If the main function of the server is `RPC`, but you also want to provide a simple web management interface, you can first create an `HTTP/WebSocket` server in such a scenario, and then call the `listen` method to listen on the native TCP port.
### Example

```php
$http_server = new Swoole\Http\Server('0.0.0.0', 9998);
$http_server->set(['daemonize' => false]);
$http_server->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
});

// Listen to an additional TCP port, provide TCP service externally, and set a callback for the TCP server
$tcp_server = $http_server->listen('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
// The newly listened port 9999 will inherit the settings of the main server by default, also using the HTTP protocol.
// Need to call the set method to override the settings of the main server
$tcp_server->set([]);
$tcp_server->on('receive', function ($server, $fd, $threadId, $data) {
    echo $data;
});

$http_server->start();
```

With this code, you can set up a server that provides HTTP services externally and also provides TCP services externally. More specific and elegant code combinations can be implemented by yourself.
## Setting up TCP, HTTP, and WebSocket protocols on multiple ports

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_websocket_protocol' => true, // Enable WebSocket protocol support for this port
]);
```

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_http_protocol' => false, // Disable HTTP protocol functionality for this port
]);
```

Similarly, there are parameters such as `open_http_protocol`, `open_http2_protocol`, `open_mqtt_protocol`, etc.
## Optional Parameters

* If the listening port `port` does not call the `set` method to set protocol processing options, it will inherit the relevant configuration of the main server.
* If the main server is an `HTTP/WebSocket` server and no protocol parameters are set, the listening port will still be set to `HTTP` or `WebSocket` protocol, and the [onReceive](/server/events?id=onreceive) callback set for the port will not be executed.
* If the main server is an `HTTP/WebSocket` server and the listening port calls `set` to set configuration parameters, the protocol settings of the main server will be cleared. The listening port will become a `TCP` protocol. If you still want the listening port to use the `HTTP/WebSocket` protocol, you need to add `open_http_protocol => true` and `open_websocket_protocol => true` in the configuration.

**Parameters that can be set through `set` for `port` include:**

* Socket parameters such as `backlog`, `open_tcp_keepalive`, `open_tcp_nodelay`, `tcp_defer_accept`, etc.
* Protocol-related settings such as `open_length_check`, `open_eof_check`, `package_length_type`, etc.
* SSL certificate-related settings such as `ssl_cert_file`, `ssl_key_file`, etc.

For more details, please refer to the [configuration section](/server/setting).
## Optional Callback

If `port` does not call the `on` method to set a callback function for the listening port, the default callback function of the main server will be used. Callback functions that can be set by using the `on` method for `port` include:
### TCP Server

* onConnect
* onClose
* onReceive
### UDP Server

* onPacket
* onReceive
### HTTP Server

* onRequest
### WebSocket server

* onMessage
* onOpen
* onHandshake

!> Callback functions for different listening ports are still executed in the same `Worker` process space.
## Connection traversal under multiple ports

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9514, SWOOLE_BASE);

$tcp = $server->listen("0.0.0.0", 9515, SWOOLE_SOCK_TCP);
$tcp->set([]);

$server->on("open", function ($serv, $req) {
    echo "new WebSocket Client, fd={$req->fd}\n";
});

$server->on("message", function ($serv, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $serv->push($frame->fd, "this is server OnMessage");
});

$tcp->on('receive', function ($server, $fd, $reactor_id, $data) {
    // Traverse connections only on port 9514, as we are using $server, not $tcp
    $websocket = $server->ports[0];
    foreach ($websocket->connections as $_fd) {
        var_dump($_fd);
        if ($server->exist($_fd)) {
            $server->push($_fd, "this is server onReceive");
        }
    }
    $server->send($fd, 'receive: ' . $data);
});

$server->start();
```
