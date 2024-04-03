# UDP Server

## Program Code

Please write the following code in udpServer.php.

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

// Listen for data receiving event.
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Server: {$data}");
});

// Start the server
$server->start();
```

UDP Server is different from TCP Server as UDP does not have the concept of connection. After starting the server, clients do not need to connect; they can directly send data packets to the server listening on port 9502. The corresponding event is `onPacket`.

* `$clientInfo` holds client-related information, which is an array containing the client's IP address and port.
* Use the `$server->sendto` method to send data to the client.
!> By default Docker uses TCP protocol for communication. If you need to use UDP protocol, you must configure Docker network accordingly.  
```shell
docker run -p 9502:9502/udp <image-name>
```

## Start Service

```shell
php udpServer.php
```

You can use `netcat -u` to connect and test the UDP server.

```shell
netcat -u 127.0.0.1 9502
hello
Server: hello
```
