# Coroutine Client <!-- {docsify-ignore-all} -->

The following coroutine clients are built-in classes in Swoole, where those marked with ⚠️ are not recommended for further use. You can use PHP native functions + [one-click coroutine](/runtime) instead.

* [TCP/UDP/UnixSocket Client](coroutine_client/client.md)
* [Socket Client](coroutine_client/socket.md)
* [HTTP/WebSocket Client](coroutine_client/http_client.md)
* [HTTP2 Client](coroutine_client/http2_client.md)
* [PostgreSQL Client](coroutine_client/postgresql.md)
* [FastCGI Client](coroutine_client/fastcgi.md)
* ⚠️ [Redis Client](coroutine_client/redis.md)
* ⚠️ [MySQL Client](coroutine_client/mysql.md)
* [System](/coroutine/system) System API
## Timeout Rules

All network requests (establishing connection, sending data, receiving data) may timeout. `Swoole` coroutine client provides three ways to set timeout:

1. Pass the timeout duration as a parameter of the method, such as [Co\Client->connect()](/coroutine_client/client?id=connect), [Co\Http\Client->recv()](/coroutine_client/http_client?id=recv), [Co\MySQL->query()](/coroutine_client/mysql?id=query), and so on.

   !> This way has the smallest scope of impact (only effective for the current function call), with the highest priority (the current function call will ignore timeout settings below `2` and `3).

2. Set timeout through the `set()` or `setOption()` method of the `Swoole` coroutine client class, for example:

   ```php
   $client = new Co\Client(SWOOLE_SOCK_TCP);
   //or
   $client = new Co\Http\Client("127.0.0.1", 80);
   //or
   $client = new Co\Http2\Client("127.0.0.1", 443, true);
   $client->set(array(
       'timeout' => 0.5, //total timeout, including connection, sending, and receiving timeouts
       'connect_timeout' => 1.0, //connection timeout, will override the first total timeout
       'write_timeout' => 10.0, //send timeout, will override the first total timeout
       'read_timeout' => 0.5, //receive timeout, will override the first total timeout
   ));

   //Co\Redis() does not have write_timeout and read_timeout configurations
   $client = new Co\Redis();
   $client->setOption(array(
       'timeout' => 1.0, //total timeout, including connection, sending, and receiving timeouts
       'connect_timeout' => 0.5, //connection timeout, will override the first total timeout
   ));

   //Co\MySQL() does not have set configurations
   $client = new Co\MySQL();

   //Co\Socket configuration through setOption
   $socket = new Co\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
   $timeout = array('sec' => 1, 'usec' => 500000);
   $socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout); //receive data timeout
   $socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout); //connection and send data timeout configuration
   ```

   !> This way only affects the current class and will be overridden by the first way, ignoring the configurations of the third way below.

3. As can be seen, the timeout setting rules in the above `2` ways are complicated and inconsistent. In order to avoid developers needing to be cautious everywhere, starting from version `v4.2.10`, all coroutine clients provided a globally unified timeout setting rule with the largest impact and the lowest priority, as follows:

   ```php
   Co::set([
       'socket_timeout' => 5,
       'socket_connect_timeout' => 1,
       'socket_read_timeout' => 1,
       'socket_write_timeout' => 1,
   ]);
   ```

   + `-1`: indicates no timeout
   + `0`: indicates no change in timeout
   + `any other value > 0`: represents setting a timeout timer for the corresponding number of seconds, with a maximum precision of `1 millisecond`, which is a floating-point number; `0.5` represents `500 milliseconds`
   + `socket_connect_timeout`: represents the timeout for establishing a TCP connection, **default is `1 second`**, starting from version `v4.5.x` **default is `2 seconds`**
   + `socket_timeout`: represents the timeout for TCP read/write operations, **default is `-1`**, starting from version `v4.5.x` **default is `60 seconds`**. To set read and write timeouts separately, refer to the configuration below
   + `socket_read_timeout`: introduced in `v4.3`, represents the timeout for TCP **read** operations, **default is `-1`**, starting from version `v4.5.x` **default is `60 seconds`**
   + `socket_write_timeout`: introduced in `v4.3`, represents the timeout for TCP **write** operations, **default is `-1`**, starting from version `v4.5.x` **default is `60 seconds`**

   !> **That is:** before `v4.5.x` versions of `Swoole` coroutine clients, if no timeout was set using the first or second method mentioned above, the default connection timeout was `1 second`, while read/write operations never timed out; from version `v4.5.x` onwards, the default connection timeout is `60 seconds`, and read/write operations timeout is `60 seconds`. If the global timeout is modified midway, it will not take effect on already created sockets.
### PHP official network library timeout

In addition to the coroutine client provided by `Swoole` mentioned above, in [Coroutine All-in-One](/runtime), the native PHP method is used, and its timeout is affected by the [default_socket_timeout](http://php.net/manual/zh/filesystem.configuration.php) configuration. Developers can individually set it through `ini_set('default_socket_timeout', 60)`, with a default value of 60.
