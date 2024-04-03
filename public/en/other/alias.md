# Function Alias Summary

## Coroutine Short Name

Simplify the writing of coroutine related APIs. You can modify the `php.ini` setting `swoole.use_shortname=On/Off` to enable/disable short names, with the default being enabled.

All class names prefixed with `Swoole\Coroutine` are mapped to `Co`. In addition, there are some other mappings as follows:

### Create Coroutine

```php
// Swoole\Coroutine::create is equivalent to the go function
go(function () {
    Co::sleep(0.5);
    echo 'hello';
});
go('test');
go([$object, 'method']);
```

### Channel Operations

```php
// Coroutine\Channel can be abbreviated to chan
$c = new chan(1);
$c->push($data);
$c->pop();
```

### Deferred Execution

```php
// Swoole\Coroutine::defer can be directly used as defer
defer(function () use ($db) {
    $db->close();
});
```

## Short Name Methods

!> In this way, `go` and `defer` are available starting from Swoole version >= `v4.6.3`

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\defer;

run(function () {
    defer(function () {
        echo "co1 end\n";
    });
    sleep(1);
    go(function () {
        usleep(100000);
        defer(function () {
            echo "co2 end\n";
        });
        echo "co2\n";
    });
    echo "co1\n";
});
```

## Coroutine System API

In version `4.4.4`, coroutine APIs related to system operations have been moved from the `Swoole\Coroutine` class to the `Swoole\Coroutine\System` class, as a standalone new module. For backward compatibility, alias methods above the `Coroutine` class have been retained.

* For example, `Swoole\Coroutine::sleep` corresponds to `Swoole\Coroutine\System::sleep`
* For example, `Swoole\Coroutine::fgets` corresponds to `Swoole\Coroutine\System::fgets`

## Class Short Alias Mapping

!> It is recommended to use namespace style.

| Underline Class Naming Style | Namespace Style             |
| ---------------------------  | --------------------------- |
| swoole_server                | Swoole\Server               |
| swoole_client                | Swoole\Client               |
| swoole_process               | Swoole\Process              |
| swoole_timer                 | Swoole\Timer                |
| swoole_table                 | Swoole\Table                |
| swoole_lock                  | Swoole\Lock                 |
| swoole_atomic                | Swoole\Atomic               |
| swoole_atomic_long           | Swoole\Atomic\Long          |
| swoole_buffer                | Swoole\Buffer               |
| swoole_redis                 | Swoole\Redis                |
| swoole_error                 | Swoole\Error                |
| swoole_event                 | Swoole\Event                |
| swoole_http_server           | Swoole\Http\Server          |
| swoole_http_client           | Swoole\Http\Client          |
| swoole_http_request          | Swoole\Http\Request         |
| swoole_http_response         | Swoole\Http\Response        |
| swoole_websocket_server      | Swoole\WebSocket\Server     |
| swoole_connection_iterator   | Swoole\Connection\Iterator  |
| swoole_exception             | Swoole\Exception            |
| swoole_http2_request         | Swoole\Http2\Request        |
| swoole_http2_response        | Swoole\Http2\Response       |
| swoole_process_pool          | Swoole\Process\Pool         |
| swoole_redis_server          | Swoole\Redis\Server         |
| swoole_runtime               | Swoole\Runtime              |
| swoole_server_port           | Swoole\Server\Port          |
| swoole_server_task           | Swoole\Server\Task          |
| swoole_table_row             | Swoole\Table\Row            |
| swoole_timer_iterator        | Swoole\Timer\Iterator       |
| swoole_websocket_closeframe  | Swoole\Websocket\Closeframe |
| swoole_websocket_frame       | Swoole\Websocket\Frame      |
