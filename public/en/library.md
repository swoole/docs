# Library

After version 4, Swoole integrates the [Library](https://github.com/swoole/library) module, **using PHP code to write kernel functionality**, making the underlying infrastructure more stable and reliable.

!> This module can also be installed separately via composer. When installing it separately, you need to close the extension's built-in library by configuring `swoole.enable_library=Off` in `php.ini`.

Currently, the following tools/components are provided:

- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) for waiting for concurrent coroutine tasks, [documentation](/coroutine/wait_group)
- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI) FastCGI client, [documentation](/coroutine_client/fastcgi)
- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php) coroutine server, [documentation](/coroutine/server)
- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php) coroutine barrier, [documentation](/coroutine/barrier)

- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl) coroutine enabled CURL, [documentation](/runtime?id=swoole_hook_curl)
- [Database](https://github.com/swoole/library/tree/master/src/core/Database) advanced encapsulation of various database connection pools and object proxies, [documentation](/coroutine/conn_pool?id=database)
- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) raw connection pool, [documentation](/coroutine/conn_pool?id=connectionpool)
- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php) process manager, [documentation](/process/process_manager)

- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php), [ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php), [MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php) object-oriented style programming for Arrays and Strings

- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php) provide some coroutine functions, [documentation](/coroutine/coroutine?id=functions)
- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php) common configuration constants
- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php) HTTP status codes

## Sample Code

[Examples](https://github.com/swoole/library/tree/master/examples)
