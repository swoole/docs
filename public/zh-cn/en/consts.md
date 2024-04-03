# Constants

!> This does not include all constants. To view all constants, please visit or install: [ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)

## Swoole

Constant | Description
---|---
SWOOLE_VERSION | Current version number of Swoole, in string format, such as 1.6.0

## Constructor Parameters

Constant | Description
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | Use Base mode, where business code runs directly in the Reactor process
[SWOOLE_PROCESS](/learn?id=swoole_process) | Use Process mode, where business code runs in the Worker process

## Socket Types

Constant | Description
---|---
SWOOLE_SOCK_TCP | Create a TCP socket
SWOOLE_SOCK_TCP6 | Create a TCP IPv6 socket
SWOOLE_SOCK_UDP | Create a UDP socket
SWOOLE_SOCK_UDP6 | Create a UDP IPv6 socket
SWOOLE_SOCK_UNIX_DGRAM | Create a Unix datagram socket
SWOOLE_SOCK_UNIX_STREAM | Create a Unix stream socket
SWOOLE_SOCK_SYNC | Synchronous client

## SSL Encryption Methods

Constant | Description
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD (Default encryption method) | -
SWOOLE_SSLv23_SERVER_METHOD | -
SWOOLE_SSLv23_CLIENT_METHOD | -
SWOOLE_TLSv1_METHOD | -
SWOOLE_TLSv1_SERVER_METHOD | -
SWOOLE_TLSv1_CLIENT_METHOD | -
SWOOLE_TLSv1_1_METHOD | -
SWOOLE_TLSv1_1_SERVER_METHOD | -
SWOOLE_TLSv1_1_CLIENT_METHOD | -
SWOOLE_TLSv1_2_METHOD | -
SWOOLE_TLSv1_2_SERVER_METHOD | -
SWOOLE_TLSv1_2_CLIENT_METHOD | -
SWOOLE_DTLSv1_METHOD | -
SWOOLE_DTLSv1_SERVER_METHOD | -
SWOOLE_DTLSv1_CLIENT_METHOD | -
SWOOLE_DTLS_SERVER_METHOD | -
SWOOLE_DTLS_CLIENT_METHOD | -

!> `SWOOLE_DTLSv1_METHOD`, `SWOOLE_DTLSv1_SERVER_METHOD`, `SWOOLE_DTLSv1_CLIENT_METHOD` have been removed in Swoole version >= `v4.5.0`.

## SSL Protocols

Constant | Description
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

!> Available in Swoole version >= `v4.5.4`

## Log Levels

Constant | Description
---|---
SWOOLE_LOG_DEBUG | Debug log, only for kernel development debugging
SWOOLE_LOG_TRACE | Trace log, can be used to trace system issues, debug logs are carefully set to carry key information
SWOOLE_LOG_INFO | Normal information, only for information display
SWOOLE_LOG_NOTICE | Notice information, system may have some behavior like restart, shutdown
SWOOLE_LOG_WARNING | Warning information, system may have some issues
SWOOLE_LOG_ERROR | Error information, critical errors occurred in the system, need immediate resolution
SWOOLE_LOG_NONE | Equivalent to turning off log information, log information will not be thrown

!> `SWOOLE_LOG_DEBUG` and `SWOOLE_LOG_TRACE` logs must be used after compiling the Swoole extension with [--enable-debug-log](/environment?id=debug参数) or [--enable-trace-log](/environment?id=debug参数). In normal versions, even if `log_level = SWOOLE_LOG_TRACE` is set, these logs cannot be printed.

## Trace Flags

When running a service online, there are always a large number of requests being processed, resulting in a huge number of logs thrown at the bottom level. You can use `trace_flags` to set trace log tags and only print some trace logs. `trace_flags` supports setting multiple trace items using the `|` OR operator.

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

The following trace items are supported at the bottom level, you can use `SWOOLE_TRACE_ALL` to trace all items:

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`
