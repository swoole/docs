# INI configuration

Configuration | Default Value | Purpose
---|---|---
swoole.enable_coroutine | On | Turn on/off the built-in coroutine, see more details [here](/server/setting?id=enable_coroutine).
swoole.display_errors | On | Enable/disable displaying `Swoole` error messages.
swoole.unixsock_buffer_size | 8M | Set the size of the `Socket` buffer for inter-process communication, equivalent to [socket_buffer_size](/server/setting?id=socket_buffer_size).
swoole.use_shortname | On | Enable/disable short aliases, see more details [here](/other/alias?id=coroutine-short-names).
swoole.enable_preemptive_scheduler | Off | Prevent some coroutines from consuming CPU time for too long in a tight loop (10ms of CPU time), preventing other coroutines from getting [scheduled](/coroutine?id=coroutine-scheduling), see [example](https://github.com/swoole/swoole-src/tree/master/tests/swoole_coroutine_scheduler/preemptive).
swoole.enable_library | On | Enable/disable the extension's built-in library
