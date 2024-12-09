# Zusammenfassung von Funktionsaliassen


## Kurznamen für Coroutinen

Die Namen der `API`s, die mit Coroutinen zu tun haben, werden vereinfacht. Sie können das `php.ini`-Setting `swoole.use_shortname=On/Off` ändern, um die Verwendung von kurzen Namen ein- oder auszuschalten. Der Standard ist eingeschaltet.

Alle Klassen, die mit `Swoole\Coroutine` beginnen, werden auf `Co` abgebildet. Darüber hinaus gibt es die folgenden Zuordnungen:


### Coroutine erstellen

```php
//Swoole\Coroutine::create ist gleichbedeutend mit der go-Funktion
go(function () {
	Co::sleep(0.5);
	echo 'hello';
});
go('test');
go([$object, 'method']);
```


### Kanal Operation

```php
//Coroutine\Channel kann auf chan verkürzt werden
$c = new chan(1);
$c->push($data);
$c->pop();
```


### verzögerte Ausführung

```php
//Swoole\Coroutine::defer kann direkt mit defer verwendet werden
defer(function () use ($db) {
    $db->close();
});
```


## Kurzname-Methoden

!> In der folgenden Art und Weise sind `go` und `defer` in Swoole-Versionen >= `v4.6.3` verfügbar

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

In der Version `4.4.4` wurden die Coroutine-System-API's, die mit Systemoperationen zu tun haben, von der `Swoole\Coroutine`-Klasse in die `Swoole\Coroutine\System`-Klasse umgezogen. Sie bilden einen neuen Modul. Um die Abwärtskompatibilität zu gewährleisten, wurden unterhalb der `Coroutine`-Klasse immer noch die Aliaskonzepte beibehalten.

* Zum Beispiel `Swoole\Coroutine::sleep` ist gleichbedeutend mit `Swoole\Coroutine\System::sleep`
* Zum Beispiel `Swoole\Coroutine::fgets` ist gleichbedeutend mit `Swoole\Coroutine\System::fgets`

## Klassen-Kurzalias-Zuordnungen

!> empfohlen wird der Einsatz des Namensraumstils.

| Unterstrich-Klassennamenstil                | Namensraumstil                  |
| --------------------------- | --------------------------- |
| swoole_server               | Swoole\Server               |
| swoole_client               | Swoole\Client               |
| swoole_process              | Swoole\Process              |
| swoole_timer                | Swoole\Timer                |
| swoole_table                | Swoole\Table                |
| swoole_lock                 | Swoole\Lock                 |
| swoole_atomic               | Swoole\Atomic               |
| swoole_atomic_long          | Swoole\Atomic\Long          |
| swoole_buffer               | Swoole\Buffer               |
| swoole_redis                | Swoole\Redis                |
| swoole_error                | Swoole\Error                |
| swoole_event                | Swoole\Event                |
| swoole_http_server          | Swoole\Http\Server          |
| swoole_http_client          | Swoole\Http\Client          |
| swoole_http_request         | Swoole\Http\Request         |
| swoole_http_response        | Swoole\Http\Response        |
| swoole_websocket_server     | Swoole\WebSocket\Server     |
| swoole_connection_iterator  | Swoole\Connection\Iterator  |
| swoole_exception            | Swoole\Exception            |
| swoole_http2_request        | Swoole\Http2\Request        |
| swoole_http2_response       | Swoole\Http2\Response       |
| swoole_process_pool         | Swoole\Process\Pool         |
| swoole_redis_server         | Swoole\Redis\Server         |
| swoole_runtime              | Swoole\Runtime              |
| swoole_server_port          | Swoole\Server\Port          |
| swoole_server_task          | Swoole\Server\Task          |
| swoole_table_row            | Swoole\Table\Row            |
| swoole_timer_iterator       | Swoole\Timer\Iterator       |
| swoole_websocket_closeframe | Swoole\Websocket\Closeframe |
| swoole_websocket_frame      | Swoole\Websocket\Frame      |
