# WebSocket сервер

?> Полный реализацией WebSocket сервера с использованием协程, наследующийся от [Coroutine\Http\Server](/coroutine/http_server). В качестве основы используется поддержка протокола `WebSocket`, о которой здесь не будет говорить, а лишь выделим различия.

!> Эта глава стала доступна после версии v4.4.13.


## Полный пример

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    $ws->close();
                    break;
                }
                $ws->push("Привет, {$frame->data}!");
                $ws->push("Как ты там, {$frame->data}?");
            }
        }
    });

    $server->handle('/', function (Request $request, Response $response) {
        $response->end(<<<HTML
    <h1>Сервер WebSocket Swoole</h1>
    <script>
var wsServer = 'ws://127.0.0.1:9502/websocket';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("Подключен к серверу WebSocket.");
    websocket.send('hello');
};

websocket.onclose = function (evt) {
    console.log("Отключен");
};

websocket.onmessage = function (evt) {
    console.log('Получена информация от сервера: ' + evt.data);
};

websocket.onerror = function (evt, e) {
    console.log('Произошла ошибка: ' + evt.data);
};
</script>
HTML
        );
    });

    $server->start();
});
```


### Пример отправки сообщений всем клиентам

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        global $wsObjects;
        $objectId = spl_object_id($ws);
        $wsObjects[$objectId] = $ws;
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                unset($wsObjects[$objectId]);
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    unset($wsObjects[$objectId]);
                    $ws->close();
                    break;
                }
                foreach ($wsObjects as $obj) {
                    $obj->push("Сервер：{$frame->data}");
                }
            }
        }
    });
    $server->start();
});
```


## Процесс обработки

* `$ws->upgrade()`： отправляет клиенту сообщение о успешном рукопожатии WebSocket
* Цикл `while(true)` обрабатывает прием и отправку сообщений
* `$ws->recv()` принимает фрейм WebSocket сообщения
* `$ws->push()` отправляет данные фрейма стороне назначения
* `$ws->close()` закрывает соединение

!> `$ws` - это объект класса `Swoole\Http\Response`, подробное описание методов смотрите ниже.


## Методы


### upgrade()

Отправляет сообщение о успешном рукопожатии WebSocket.

!> Этот метод не следует использовать в серверах, основанных на асинхронном стиле (/http_server)

```php
Swoole\Http\Response->upgrade(): bool
```


### recv()

Получает сообщение WebSocket.

!> Этот метод не следует использовать в серверах, основанных на асинхронном стиле (/http_server), вызов метода `recv` приведет к [застое](/coroutine?id=协程调度) текущего协心力, и协心力 будет возобновлена, когда данные будут готовы

```php
Swoole\Http\Response->recv(float $timeout = 0): Swoole\WebSocket\Frame | false | string
```

* **Возвращаемое значение**

  * Если успешно получено сообщение, возвращается объект класса `Swoole\WebSocket\Frame`, подробности смотрите [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)
  * Если неудачно, возвращается `false`, для получения ошибки используйте [swoole_last_error()](/functions?id=swoole_last_error)
  * Если соединение закрыто, возвращается пустая строка
  * Подробности обработки возвращаемого значения смотрите [Пример отправки сообщений всем клиентам](/coroutine/ws_server?id=群发示例)


### push()

Отправляет фрейм данных WebSocket.

!> Этот метод не следует использовать в серверах, основанных на асинхронном стиле (/http_server), при отправке больших пакетов необходимо следить за доступностью для письма, что может привести к множеству [переключений协心力](/coroutine?id=协程调度)

```php
Swoole\Http\Response->push(string|object $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

* **Параметры** 

  !> Если传入я `$data` является объектом класса [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), то следующие параметры будут игнорированы, поддерживается отправка различных типов фреймов

  * **`string|object $data`**

    * **Функция**: Содержание, которое необходимо отправить
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $opcode`**

    * **Функция**: Указывает формат отправляемого данных 【по умолчанию - текст. Для отправки бинарных данных `$opcode` должен быть установлен на `WEBSOCKET_OPCODE_BINARY`】
    * **По умолчанию**: `WEBSOCKET_OPCODE_TEXT`
    * **Другие значения**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Функция**: Указывает, завершено ли отправку
    * **По умолчанию**: `true`
    * **Другие значения**: `false`

### close()

Закрывает соединение WebSocket.

!> Этот метод не следует использовать в серверах, основанных на асинхронном стиле (/http_server), в версиях до v4.4.15 может быть ошибочно сообщено `Warning`, игнорирование этого достаточно.

```php
Swoole\Http\Response->close(): bool
```

Этот метод напрямую прерывает `TCP` соединение и не отправляет фрейм `Close`, в отличие от метода `WebSocket\Server::disconnect()`. Перед закрытием соединения можно использовать метод `push()` для отправки фрейма `Close`, чтобы активно уведомить клиент.

```php
$frame = new Swoole\WebSocket\CloseFrame;
$frame->reason = 'close';
$ws->push($frame);
$ws->close();
```
