# Configuration

Configuration | Default Value | Function
---|---|---
swoole.enable_coroutine | On | Toggle built-in coroutines, [see details](/server/setting?id=enable_coroutine).
swoole.display_errors | On | Enable/disable Swoole error messages.
swoole.unixsock_buffer_size | 8M | Set the buffer size for inter-process communication sockets, equivalent to [socket_buffer_size](/server/setting?id=socket_buffer_size).
swoole.use_shortname | On | Whether to enable short aliases, [see details](/other/alias?id=coroutine_short_name).
swoole.enable_preemptive_scheduler | Off | Can prevent certain coroutines from monopolizing CPU time too long (10ms of CPU time) causing other coroutines to not get [scheduled](/coroutine?id=coroutine_scheduling), [example](https://github.com/swoole/swoole-src/tree/master/tests/swoole_coroutine_scheduler/preemptive).
swoole.enable_library | On | Enable/disable built-in extensions of the library.
