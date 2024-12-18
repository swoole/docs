# Итоговый список псевдонимов функций


## Короткие имена кусков

Упрощение написания названий соответствующих `API` кусков. Можно изменить настройки в `php.ini` на `swoole.use_shortname=On/Off` чтобы включить/отключить короткие имена, по умолчанию они включены.

Все классы с префиксом `Swoole\Coroutine` маппируются на `Co`. Кроме того, есть следующие маппировки:


### Создание кусков

```php
//Swoole\Coroutine::create эквивалентен функции go
go(function () {
	Co::sleep(0.5);
	echo 'hello';
});
go('test');
go([$object, 'method']);
```


### Операции с каналами

```php
//Coroutine\Channel можно укоротить до chan
$c = new chan(1);
$c->push($data);
$c->pop();
```


### Отложенное выполнение

```php
//Swoole\Coroutine::defer можно использовать прямо как defer
defer(function () use ($db) {
    $db->close();
});
```


## Короткие методы имени

!> В следующем случае `go` и `defer` доступны в версиях Swoole >= `v4.6.3`

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


## System API кусков

В версии `4.4.4` API кусков, связанные с системными операциями, были перенесены из класса `Swoole\Coroutine` в класс `Swoole\Coroutine\System`. Они стали отдельной новой модуль. Для обратной совместимости на нижнем уровне все еще сохраняются псевдонимы методов над классом `Coroutine`.

* Например, `Swoole\Coroutine::sleep` эквивалентен `Swoole\Coroutine\System::sleep`
* Например, `Swoole\Coroutine::fgets` эквивалентен `Swoole\Coroutine\System::fgets`

## Маппировки коротких псевдонимов классов

!> Рекомендуется использовать стиль с命名 spaces.

| Стиль класса с подчеркиваниями | Стиль с命名 spaces                  |
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
