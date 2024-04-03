# Incompatible Changes Below
## v5.0.0
* Changed the default running mode of `Server` to `SWOOLE_BASE`
* Raised the minimum `PHP` version requirement to `8.0`
* Added type declarations to all class methods and functions, switching to strict typing mode
* Removed the underlined `PSR-0` class aliases, only retaining the namespace-style class names; for example, `swoole_server` must be changed to `Swoole\Server`
* Deprecated `Swoole\Coroutine\Redis` and `Swoole\Coroutine\MySQL`; please use `Runtime Hook`+ native `Redis`/`MySQL` clients instead
## v4.8.0

- In `BASE` mode, the `onStart` callback will always be triggered when the first worker process (with `workerId` as `0`) is started, before `onWorkerStart`. In the `onStart` function, coroutine `API` can always be used. When `Worker-0` encounters a fatal error and restarts, `onStart` will be called again.
In previous versions, `onStart` would be triggered in `Worker-0` when there is only one worker process. If there are multiple worker processes, it would be executed in the `Manager` process.
## v4.7.0

- Removed `Table\Row`, `Table` no longer supports reading and writing in array format
## v4.6.0

- Removed the maximum limit of `session id`, no longer repetitive
- Unsafe functions will be disabled when using coroutines, including `pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait`
- Coroutine hook is enabled by default
- No longer support PHP7.1
- `Event::rshutdown()` is marked as deprecated, please use Coroutine\run instead
## v4.5.4

- `SWOOLE_HOOK_ALL` includes `SWOOLE_HOOK_CURL`
- Removed `ssl_method`, support `ssl_protocols`
## v4.4.12

- This version added support for WebSocket frame compression and changed the third parameter of the `push` method to `flags`. If `strict_types` is not set, the code compatibility will not be affected; otherwise, there will be a type error where a bool cannot be implicitly converted to int. This issue will be fixed in v4.4.13.
## v4.4.1

- Registered signals are no longer used as conditions to maintain the event loop. **If the program only registers signals without performing other work, it will be considered idle and exit immediately** (in this case, you can prevent the process from exiting by registering a timer).
## v4.4.0

- Align with `PHP` official version, no longer support `PHP7.0` (@matyhtf)
- Remove `Serialize` module, maintain in separate [ext-serialize](https://github.com/swoole/ext-serialize) extension
- Remove `PostgreSQL` module, maintain in separate [ext-postgresql](https://github.com/swoole/ext-postgresql) extension
- `Runtime::enableCoroutine` will no longer automatically be compatible with both coroutine and non-coroutine environments; once enabled, all blocking operations must be called within a coroutine (@matyhtf)
- Due to the introduction of a brand new coroutine `MySQL` client driver, the underlying design is more standardized, but there are some minor backward incompatible changes (see [4.4.0 Changelog](https://wiki.swoole.com/wiki/page/p-4.4.0.html))
## v4.3.0

- Removed all async modules, please refer to [Independent Asynchronous Extensions](https://wiki.swoole.com/wiki/page/p-async_ext.html) or [4.3.0 Update Log](https://wiki.swoole.com/wiki/page/p-4.3.0.html)
## v4.2.13

> Unavoidable incompatible changes due to historical API design issues

* Changes in coroutine Redis client subscription mode operations, please see [Subscription Mode](https://wiki.swoole.com/#/coroutine_client/redis?id=%e8%ae%a2%e9%98%85%e6%a8%a1%e5%bc%8f)
## v4.2.12

> Experimental feature + Inevitable incompatible changes due to historical API design issues

- Removed the `task_async` configuration option, replaced by [task_enable_coroutine](https://wiki.swoole.com/#/server/setting?id=task_enable_coroutine)
## v4.2.5

- Removed support for UDP clients in `onReceive` and `Server::getClientInfo`
## v4.2.0

- Removed async `swoole_http2_client` completely, please use coroutine HTTP2 client
## v4.0.4

Starting from this version, the asynchronous `Http2\Client` will trigger `E_DEPRECATED` warnings and will be removed in the next version. Please use `Coroutine\Http2\Client` instead.

The `body` property of `Http2\Response` has been renamed to `data`. This modification is to ensure consistency between `request` and `response` and is more in line with the frame type names of the HTTP2 protocol.

From this version onwards, `Coroutine\Http2\Client` has relatively complete support for the HTTP2 protocol, which can meet the needs of enterprise-level production applications such as `grpc`, `etcd`, etc. Therefore, the series of changes regarding HTTP2 are very necessary.
## v4.0.3

Keep `swoole_http2_response` and `swoole_http2_request` consistent, all property names are modified to plural form, involving the following properties

- `headers`
- `cookies`
## v4.0.2

> Due to the complexity of the underlying implementation and the frequent misunderstandings by users, the following API is temporarily removed:

- `Coroutine\Channel::select`

But at the same time, the second parameter `timeout` has been added to the `Coroutine\Channel->pop` method to meet development needs.
## v4.0

> Due to the upgrade of coroutine kernel, coroutines can now be called anywhere in any function without special treatment, so the following APIs have been removed:

- `Coroutine::call_user_func`
- `Coroutine::call_user_func_array`
