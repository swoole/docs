# Swoole\WebSocket\Server

?> С помощью встроенного сервера `WebSocket` можно написать многопроцессный `WebSocket` сервер с [асинхронным вводом/выводом](/learn?id=синхронный_и_асинхронный_io) всего за несколько строк PHP-кода.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: успешное рукопожатие с fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "получено от {$frame->fd}:{$frame->data}, opcode:{$frame->opcode}, fin:{$frame->finish}\n";
    $server->push($frame->fd, "это сервер");
});

$server->on('close', function ($server, $fd) {
    echo "клиент {$fd} закрылся\n";
});

$server->start();
```

* **Клиенты**

  * В браузерах вроде `Chrome/Firefox/` новых версий `IE/Safari` встроен `WebSocket` клиент на языке `JS`
  * В рамках фреймворка разработки микроблогов WeChat встроен `WebSocket` клиент
  * В [асинхронных](/learn?id=синхронный_и_асинхронный_io) PHP-программах можно использовать [Swoole\Coroutine\Http](/coroutine_client/http_client) в качестве `WebSocket` клиента
  * В `Apache/PHP-FPM` или других синхронных блокирующих PHP-программах можно использовать [синхронный WebSocket клиент](https://github.com/matyhtf/framework/blob/master/libs/Swoole/Client/WebSocket.php), предоставляемый `swoole/framework`
  * Не `WebSocket` клиенты не могут общаться с `WebSocket` сервером

* **Как определить, является ли соединение клиентом `WebSocket`**

?> Используя [следующий пример](/server/methods?id=getclientinfo) для получения информации о соединении, в возвращаемом массиве есть элемент [websocket_status](/websocket_server?id=состояние_соединения), который позволяет определить, является ли это `WebSocket` соединением.
```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    $client = $server->getClientInfo($frame->fd);
    // или $client = $server->connection_info($frame->fd);
    if (isset($client['websocket_status'])) {
        echo "этоwebsocket соединение";
    } else {
        echo "это не websocket соединение";
    }
});
```



## События

?> Помимо обработки回调-функций, основанных на [Swoole\Server](/server/methods) и [Swoole\Http\Server](/http_server), `WebSocket` сервер дополнительно поддерживает `4` дополнительные回调-функции. Среди них:

* `onMessage` - обязательная回调-функция
* `onOpen`, `onHandShake` и `onBeforeHandShakeResponse` (предложены в Swoole 5) - необязательные回调-функции


### onBeforeHandShakeResponse

!> Доступно с версии Swoole >= `v5.0.0`

?> **Произходит до установления соединения `WebSocket`. Если вам не нужно настраивать собственный процесс рукопожатия, но вы хотите добавить некоторые `http header` информации в ответ, то вы можете использовать это событие.**

```php
onBeforeHandShakeResponse(Swoole\Http\Request $request, Swoole\Http\Response $response);
```


### onHandShake

?> **Произходит после установления соединения `WebSocket`. `WebSocket` сервер автоматически выполняет процесс рукопожатия, но если пользователь хочет самостоятельно обрабатывать рукопожатие, он может настроить回调-функцию `onHandShake`.**

```php
onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

* **Примечание**

  *回调-функция `onHandShake` является необязательной
  * После установки回调-функции `onHandShake` событие `onOpen` больше не будет вызвано, и приложению необходимо самостоятельно обработать его, используя `$server->defer` для вызова логики `onOpen`
  * В `onHandShake` необходимо вызвать [response->status()](/http_server?id=status) для установки статуса как `101` и [response->end()](/http_server?id=end) для ответа, иначе рукопожатие потерпит неудачу.
  * Встроенный протокол рукопожатия - `Sec-WebSocket-Version: 13`, для старых браузеров необходимо самостоятельно реализовать рукопожатие

* **Наблюдение**

!> Если вам нужно самостоятельно обрабатывать `handshake`, то установите эту回调-функцию. Если вам не нужно "настроить" собственный процесс рукопожатия, то не устанавливайте эту回调, используйте встроенный `Swoole` рукопожатие. Вот что необходимо в回调-функции `onHandShake` "настроенного" рукопожатия:

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // print_r( $request->header );
    // if (если это не удовлетворяет некоторым моим требованиям на индивидуализацию, то возвращаем end для вывода, возвращаем false, рукопожатие терпит неудачу) {
    //    $response->end();
    //     return false;
    // }

    // алгоритм проверки соединения WebSocket
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(
        sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        )
    );

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // Соединение WebSocket с 'ws://127.0.0.1:9502/'
    // потерпело неудачу: Ошибка во время рукопожатия WebSocket:
    // Ответ не должен включать 'Sec-WebSocket-Protocol' header, если он не представлен в запросе: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $response->end();
});
```

!> После установки回调-функции `onHandShake` событие `onOpen` больше не будет вызвано, и приложению необходимо самостоятельно обработать его, используя `$server->defer` для вызова логики `onOpen`

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // опущено содержание рукопожатия
    $response->status(101);
    $response->end();

    global $server;
    $fd = $request->fd;
    $server->defer(function () use ($fd, $server)
    {
      echo "Клиент подключился\n";
      $server->push($fd, "привет, добро пожаловать\n");
    });
});
```


### onOpen

?> **Когда `WebSocket` клиент устанавливает соединение с сервером и завершает рукопожатие, будет вызвана эта функция.**

```php
onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request);
```

* **Примечание**

    * `$request` - это объект [HTTP](/http_server?id=httprequest), содержащий информацию о запросе на рукопожатие, отправленном клиентом
    * В функции события `onOpen` можно вызвать [push](/websocket_server?id=push) для отправки данных клиенту или [close](/server/methods?id=close) для закрытия соединения
    *回调-функция `onOpen` является необязательной


### onMessage

?> **Когда сервер получает данные от клиента в виде фрейма, будет вызвана эта функция.**

```php
onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
```

* **Примечание**

  * `$frame` - это объект [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), содержащий информацию о фрейме, отправленном клиентом
  *回调-функция `onMessage` должна быть установлена, иначе сервер не сможет начать работу
  * Фреймы `ping` от клиента не вызовут `onMessage`, нижестоящая слойка автоматически ответит `pong`, также можно настроить параметр [open_websocket_ping_frame](/websocket_server?id=open_websocket_ping_frame) для ручного обработки

!> Если `$frame->data` является текстовым типом, то его кодировка обязательно должна быть `UTF-8`, это указано в протоколе `WebSocket`
### onRequest

?> `Swoole\WebSocket\Server` наследуется от [Swoole\Http\Server](/http_server), поэтому все `API` и настройки, предоставляемые `Http\Server`, можно использовать. Пожалуйста, ознакомьтесь с разделом [Swoole\Http\Server](/http_server).

* Если установлен callback [onRequest](/http_server?id=on), то `WebSocket\Server` также может служить как `HTTP` сервер
* Если не установлен callback [onRequest](/http_server?id=on), то `WebSocket\Server`, получая `HTTP` запрос, вернет страницу ошибки `HTTP 400`
* Если вы хотите активировать все `WebSocket` push-вещания по получению `HTTP` запроса, обратите внимание на проблемы с контекстом. Для процедурного стиля используйте `global` для ссылки на `Swoole\WebSocket\Server`, для объектно-ориентированного стиля вы можете установить `Swoole\WebSocket\Server` в качестве члена класса

#### Код в процедурном стиле

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});
$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    global $server;//Зовем внешний server
    // $server->connections обходит все WebSocket соединения пользователей по их fd, и делает push ко всем пользователям
    foreach ($server->connections as $fd) {
        // Необходимо сначала проверить, является ли это правильным WebSocket соединением, иначе push может потерпеть неудачу
        if ($server->isEstablished($fd)) {
            $server->push($fd, $request->get['message']);
        }
    }
});
$server->start();
```

#### Код в объектно-ориентированном стиле

```php
class WebSocketServer
{
    public $server;

    public function __construct()
    {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
        $this->server->on('open', function (Swoole\WebSocket\Server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // Получаем значение параметра message из HTTP запроса get, и отправляем его пользователям
            // $this->server->connections обходит все WebSocket соединения пользователей по их fd, и делает push ко всем пользователям
            foreach ($this->server->connections as $fd) {
                // Необходимо сначала проверить, является ли это правильным WebSocket соединением, иначе push может потерпеть неудачу
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        $this->server->start();
    }
}

new WebSocketServer();
```


### onDisconnect

?> **Этот eventos будет срабатывать только при закрытии не WebSocket соединения.**

!> С版本的 Swoole >= `v4.7.0` доступно

```php
onDisconnect(Swoole\WebSocket\Server $server, int $fd)
```

!> Когда установлен callback onDisconnect, не WebSocket запросы или когда в [onRequest](/websocket_server?id=onrequest) методе вызван `$response->close()`, будет вызваться callback onDisconnect. Однако, если [onRequest](/websocket_server?id=onrequest) событие заканчивается нормально, не будут вызваны события onClose или onDisconnect.  


## Методы

`Swoole\WebSocket\Server` является подклассом [Swoole\Server](/server/methods), поэтому можно использовать все методы `Server`.

Следует отметить, что для отправки данных с сервера WebSocket клиентам следует использовать метод `Swoole\WebSocket\Server::push`, который упакует данные в соответствии с протоколом WebSocket. В то время как метод [Swoole\Server->send()](/server/methods?id=send) является исходным интерфейсом для отправки TCP.

Метод [Swoole\WebSocket\Server->disconnect()](/websocket_server?id=disconnect) позволяет активно закрыть WebSocket-соединение с сервера, можно указать [код состояния закрытия](/websocket_server?id=websocket关闭帧状态码) (по протоколу WebSocket, возможны целые числа в десятичной системе от 1000 до 4999) и причину закрытия (строка, закодированная в utf-8, длина не более 125 байтов). Если не указано, код состояния равен 1000, причина закрытия пустая.


### push

?> **Передать данные WebSocket-клиенту, максимальная длина не должна превышать 2M.**

```php
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool

// v4.4.12 версии изменился на параметр flags
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): bool
```

* **Параметры** 

  * **`int $fd`**

    * **Функция**: ID клиента-соединения 【Если указанный `$fd` не относится к WebSocket-клиенту, операция потерпит неудачу】
    * **По умолчанию**: нет
    * **Другие значения**: нет

  * **`Swoole\WebSocket\Frame|string $data`**

    * **Функция**: содержание данных для отправки
    * **По умолчанию**: нет
    * **Другие значения**: нет

  !> С версии Swoole >= v4.2.0, если `$data` является объектом [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), то следующие параметры будут игнорированы

  * **`int $opcode`**

    * **Функция**: Указывает формат отправляемого данных 【По умолчанию - текст. Для отправки бинарных данных необходимо установить `$opcode` на `WEBSOCKET_OPCODE_BINARY`】
    * **По умолчанию**: `WEBSOCKET_OPCODE_TEXT`
    * **Другие значения**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Функция**: Указывает на завершение отправки
    * **По умолчанию**: `true`
    * **Другие значения**: `false`

* **Возвращаемое значение**

  * Успешное завершение операции возвращает `true`, неудачное - `false`

!> Начиная с версии v4.4.12, параметр `finish` (тип `bool`) был заменен на параметр `flags` (тип `int`) для поддержки сжатия WebSocket, где `finish` соответствует значению `SWOOLE_WEBSOCKET_FLAG_FIN` равному `1`, и существующие значения типа `bool` будут скрыто конвертированы в тип `int`, что не влияет на совместимость вниз. Кроме того, флаг сжатия `flag` равен `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

!> [Базовый режим](/learn?id=ограничения базового режима:) Не поддерживает отправку данных через процесс `push`.


### exist

?> **Проверить, существует ли WebSocket-клиент и находится ли его состояние в активной. **

!> После v4.3.0, этот `API` используется только для проверки наличия соединения, пожалуйста, используйте `isEstablished` для проверки, является ли это соединением WebSocket

```php
Swoole\WebSocket\Server->exist(int $fd): bool
```

* **Возвращаемое значение**

  * Если соединение существует и завершило WebSocket-х握手, возвращается `true`
  * Если соединение не существует или еще не завершило握手, возвращается `false`
### пакетирование

?> **Пакетирование сообщений WebSocket.**

```php
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true, bool $mask = false): string

// В версии 4.4.12 параметр finish был изменен на flags
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string

Swoole\WebSocket\Frame::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
```

* **Параметры** 

  * **`Swoole\WebSocket\Frame|string $data $data`**

    * **Функция**: Содержание сообщения
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $opcode`**

    * **Функция**: Указывает формат отправляемого данных. 【По умолчанию - текст. Для отправки бинарных данных параметр `$opcode` должен быть установлен на `WEBSOCKET_OPCODE_BINARY`】
    * **По умолчанию**: `WEBSOCKET_OPCODE_TEXT`
    * **Другие значения**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Функция**: Окончание фрейма
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

    !> Начиная с версии `v4.4.12`, параметр `finish` (тип `bool`) был изменен на `flags` (тип `int`) для поддержки сжатия `WebSocket`. Значение `finish` равно `1` для `SWOOLE_WEBSOCKET_FLAG_FIN`, и существующие значения типа `bool` будутimplicitly преобразованы в тип `int`. Это изменение совместимо и не влияет на работу старых версий.

  * **`bool $mask`**

    * **Функция**: Установка маски 【В версии `v4.4.12` этот параметр был удален】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Возвращает упакованный пакет данных `WebSocket`, который может быть отправлен стороне получателя с помощью метода [send()](/server/methods?id=send) базового класса `Swoole\Server`

* **Пример**

```php
$ws = new Swoole\Server('127.0.0.1', 9501 , SWOOLE_BASE);

$ws->set(array(
    'log_file' => '/dev/null'
));

$ws->on('WorkerStart', function (\Swoole\Server $serv) {
});

$ws->on('receive', function ($serv, $fd, $threadId, $data) {
    $sendData = "HTTP/1.1 101 Switching Protocols\r\n";
    $sendData .= "Upgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: IFpdKwYy9wdo4gTldFLHFh3xQE0=\r\n";
    $sendData .= "Sec-WebSocket-Version: 13\r\nServer: swoole-http-server\r\n\r\n";
    $sendData .= Swoole\WebSocket\Server::pack("hello world\n");
    $serv->send($fd, $sendData);
});

$ws->start();
```


### Распаковка

?> **Разбор фрейма данных WebSocket.**

```php
Swoole\WebSocket\Server::unpack(string $data): Swoole\WebSocket\Frame|false;
```

* **Параметры** 

  * **`string $data`**

    * **Функция**: Содержание сообщения
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Если распаковка失败, возвращается `false`, если успешна - объект [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)


### Отключение

?> **Собственный запрос на отключение от клиента WebSocket и закрытие этого соединения.**

!> Версия Swoole >= `v4.0.3` доступна

```php
Swoole\WebSocket\Server->disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
```

* **Параметры** 

  * **`int $fd`**

    * **Функция**: ID клиента соединения 【Если указанный `$fd` не относится к клиенту WebSocket, отправка будет неудачной】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $code`**

    * **Функция**: Код состояния закрытия соединения 【Согласно RFC6455, для закрытия соединения приложения, значение кодов должно быть в диапазоне от `1000` до `4999`】
    * **По умолчанию**: `SWOOLE_WEBSOCKET_CLOSE_NORMAL`
    * **Другие значения**: Нет

  * **`string $reason`**

    * **Функция**: Причина закрытия соединения 【Формат字符串 `utf-8`, длина в bytes не должна превышать `125`】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Если отправка успешна, возвращается `true`, в случае неудачи или неправильного кода состояния - `false`


### Проверка установившегося соединения

?> **Проверка, является ли соединение действительным клиентским соединением WebSocket.**

?> Этот метод отличается от метода `exist`, который просто проверяет, является ли это TCP-соединение, и не может определить, является ли это завершившимся WebSocket-клиентом.

```php
Swoole\WebSocket\Server->isEstablished(int $fd): bool
```

* **Параметры** 

  * **`int $fd`**

    * **Функция**: ID клиента соединения 【Если указанный `$fd` не относится к клиенту WebSocket, отправка будет неудачной】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Если соединение действительно, возвращается `true`, в противном случае - `false`


## Класс фрейма данных WebSocket


### Swoole\WebSocket\Frame

?> В версии `v4.2.0` был добавлен Support для отправки и приема объектов класса [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) как на сервере, так и на клиенте  
В версии `v4.4.12` был добавлен атрибут `flags`, чтобы поддерживать сжатые фреймы WebSocket, а также был создан новый подкласс [Swoole\WebSocket\CloseFrame](/websocket_server?id=swoolewebsocketcloseframe)

Обычный объект `frame` имеет следующие атрибуты


Константы | Описание 
---|--- 
fd | ID клиента, который используется при отправке данных с помощью `$server->push`    
data | Содержание данных, которое может быть текстом или бинарным, и его можно определить по значению `opcode`    
 opcode | [Тип фрейма WebSocket](/websocket_server?id=数据帧类型), можно посмотреть в стандартном документе протокола WebSocket    
finish | Определяет, является ли фрейм полным. Объекты WebSocket-запроса могут быть разделены на несколько фреймов для отправки (под一层 уже реализован автоматический сбор фреймов, так что теперь не нужно беспокоиться о неполных фреймах, которые вы можете получить)  

Этот класс включает в себя методы [Swoole\WebSocket\Frame::pack()](/websocket_server?id=pack) и [Swoole\WebSocket\Frame::unpack()](/websocket_server?id=unpack) для упаковки и распаковки сообщений websocket, а также параметры, описанные в `Swoole\WebSocket\Server::pack()` и `Swoole\WebSocket\Server::unpack()`


### Swoole\WebSocket\CloseFrame

Обычный объект `закрывающий фрейм close frame` имеет следующие атрибуты


Константы | Описание 
---|--- 
opcode | [Тип фрейма WebSocket](/websocket_server?id=数据帧类型), можно посмотреть в стандартном документе протокола WebSocket    
code | [Состояние закрытия фрейма WebSocket](/websocket_server?id=WebSocket断开状态码), можно посмотреть в [документе по ошибкам в протоколе websocket](https://developer.mozilla.org/zh-CN/docs/Web/API/CloseEvent)    
reason | Причина закрытия. Если она не указана явно, то пустая

Если сервер должен принимать `закрывающие фреймы`, необходимо включить параметр [open_websocket_close_frame](/websocket_server?id=open_websocket_close_frame) с помощью `$server->set`


## Константы


### Типы фреймов


Константы | Соответствующие значения | Описание
---|---|---
WEBSOCKET_OPCODE_TEXT | 0x1 | Данные текстового содержания UTF-8
WEBSOCKET_OPCODE_BINARY | 0x2 | Бинарные данные
WEBSOCKET_OPCODE_CLOSE | 0x8 | Тип данных закрытия фрейма
WEBSOCKET_OPCODE_PING | 0x9 | Тип данных ping
WEBSOCKET_OPCODE_PONG | 0xa | Тип данных pong

### Состояние соединения


Константа | Соответствующий значение | Описание
---|---|---
WEBSOCKET_STATUS_CONNECTION | 1 | Соединение в ожидании рукопожатия
WEBSOCKET_STATUS_HANDSHAKE | 2 | В процессе рукопожатия
WEBSOCKET_STATUS_ACTIVE | 3 | Рукопожатие успешно завершено, ожидаем отправку данных браузером
WEBSOCKET_STATUS_CLOSING | 4 | Соединение находится в процессе закрытия рукопожатия, скоро будет закрыто


### Статус кодов закрытия WebSocket


Константа | Соответствующий значение | Описание
---|---|---
WEBSOCKET_CLOSE_NORMAL | 1000 | Нормальное закрытие, соединение успешно завершило свою задачу
WEBSOCKET_CLOSE_GOING_AWAY | 1001 | Сервер завершает соединение
WEBSOCKET_CLOSE_PROTOCOL_ERROR | 1002 | Ошибка протокола, соединение прерывается
WEBSOCKET_CLOSE_DATA_ERROR | 1003 | Ошибка данных, например, ожидалось текстовое данные, но было получено бинарное
WEBSOCKET_CLOSE_STATUS_ERROR | 1005 | Ожидаемый статус код не был получен
WEBSOCKET_CLOSE_ABNORMAL | 1006 | Не было отправлено сообщение об закрытии
WEBSOCKET_CLOSE_MESSAGE_ERROR | 1007 | Соединение прерывается из-за получения данных несоответствующего формата (например, текстовое сообщение содержит данные, не соответствующие UTF-8).
WEBSOCKET_CLOSE_POLICY_ERROR | 1008 | Соединение прерывается из-за получения данных, не соответствующих договоренности. Это общий статус код, используемый в случаях, когда не подходят статусы 1003 и 1009.
WEBSOCKET_CLOSE_MESSAGE_TOO_BIG | 1009 | Соединение прерывается из-за получения слишком большого фрейма данных
WEBSOCKET_CLOSE_EXTENSION_MISSING | 1010 | Клиент ожидает, что сервер согласится на один или несколько расширений, но сервер их не обрабатывает, поэтому клиент прерывает соединение
WEBSOCKET_CLOSE_SERVER_ERROR | 1011 | Клиент не может завершить запрос из-за непредвиденной ситуации, поэтому сервер прерывает соединение.
WEBSOCKET_CLOSE_TLS | 1015 | Запасной. Описывает закрытие соединения из-за неспособности завершить TLS-рукопожатие (например, невозможно проверить сертификат сервера).


## Опции

?> `Swoole\WebSocket\Server` является подклассом `Server`, который может принимать настройки с помощью метода [Swoole\WebSocker\Server::set()](/server/methods?id=set) и устанавливать некоторые параметры.


### websocket_subprotocol

?> **Установка подProtокола `WebSocket`.**

После установки в ответ на рукопожатие HTTP-header будет добавлен `Sec-WebSocket-Protocol: {$websocket_subprotocol}`. Для более подробного использования смотрите соответствующие RFC документы по протоколу WebSocket.

```php
$server->set([
    'websocket_subprotocol' => 'chat',
]);
```


### open_websocket_close_frame

?> **Включение приема кадров закрытия (фреймы с `opcode` 0x08) в коллбеке `onMessage` WebSocket, по умолчанию `false`.**

После включения можно в коллбеке `onMessage` `Swoole\WebSocket\Server` получать кадры закрытия, отправленные клиентом или сервером, и разработчик может их самостоятельно обрабатывать.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_close_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Получен кадр закрытия: Код {$frame->code} Причина {$frame->reason}\n";
    } else {
        echo "Получено сообщение: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_ping_frame

?> **Включение приема кадров Ping (фреймы с `opcode` 0x09) в коллбеке `onMessage` WebSocket, по умолчанию `false`.**

После включения можно в коллбеке `onMessage` `Swoole\WebSocket\Server` получать кадры Ping, отправленные клиентом или сервером, и разработчик может их самостоятельно обрабатывать.

!> Версия Swoole >= `v4.5.4` доступна

```php
$server->set([
    'open_websocket_ping_frame' => true,
]);
```

!> Когда значение равно `false`, на уровне будет автоматически отправлен кадр Pong, но если установить его как `true`, то разработчику придется самостоятельно отвечать кадрами Pong.

* **Пример**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_ping_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x09) {
        echo "Получен кадр Ping: Код {$frame->opcode}\n";
        // Ответ на Pong кадр
        $pongFrame = new Swoole\WebSocket\Frame;
        $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
        $server->push($frame->fd, $pongFrame);
    } else {
        echo "Получено сообщение: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_pong_frame

?> **Включение приема кадров Pong (фреймы с `opcode` 0x0A) в коллбеке `onMessage` WebSocket, по умолчанию `false`.**

После включения можно в коллбеке `onMessage` `Swoole\WebSocket\Server` получать кадры Pong, отправленные клиентом или сервером, и разработчик может их самостоятельно обрабатывать.

!> Версия Swoole >= `v4.5.4` доступна

```php
$server->set([
    'open_websocket_pong_frame' => true,
]);
```

* **Пример**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_pong_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0xa) {
        echo "Получен кадр Pong: Код {$frame->opcode}\n";
    } else {
        echo "Получено сообщение: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### websocket_compression

?> **Включение сжатия данных**

Когда установлено в `true`, разрешается сжатие фреймов с использованием `zlib`. Способность к сжатию зависит от способности клиента обрабатывать сжатые данные (исходя из информации о рукопожатии, см. RFC-7692). Для настоящего сжатия конкретного фрейма необходимо использовать флаг `SWOOLE_WEBSOCKET_FLAG_COMPRESS`, подробности использования см. в этом разделе [/websocket_server?id=websocket帧压缩-（rfc-7692）]

!> Версия Swoole >= `v4.4.12` доступна


## Прочее

!> Соответствующий примерный код можно найти в [WebSockets Unit Tests](https://github.com/swoole/swoole-src/tree/master/tests/swoole_websocket_server)


### сжатие WebSocket фреймов (RFC-7692)

?> Прежде всего, вам нужно настроить `'websocket_compression' => true` для включения сжатия (во время рукопожатия WebSocket будет обмениваться информацией о поддержке сжатия с противником), затем вы можете использовать флаг `SWOOLE_WEBSOCKET_FLAG_COMPRESS` для сжатия конкретного фрейма

#### Пример

* **Сервер**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->set(['websocket_compression' => true]);
$server->on('message', function (Server $server, Frame $frame) {
    $server->push(
        $frame->fd,
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
    // $server->push($frame->fd, $frame); // Или сервер может просто прямо пересылать фрейм клиента
});
$server->start();
```

* **Клиент**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->set(['websocket_compression' => true]);
    $cli->upgrade('/');
    $cli->push(
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
});
```
### Отправка фрейма Ping

?> Поскольку WebSocket является долгосрочным соединением, если в течение определенного времени не происходит общение, соединение может быть прерано. В таких случаях необходимо механизм сердцебиения, протокол WebSocket включает в себя фреймы Ping и Pong, которые можно отправлять регулярно для поддержания долгосрочного соединения.

#### Пример

* **Служебный сервер**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->on('message', function (Server $server, Frame $frame) {
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    $server->push($frame->fd, $pingFrame);
});
$server->start();
```

* **Клиент**

```php
use Swoole\WebSocket\Frame;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->upgrade('/');
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    // Отправка PING
    $cli->push($pingFrame);
    
    // Получение PONG
    $pongFrame = $cli->recv();
    var_dump($pongFrame->opcode === WEBSOCKET_OPCODE_PONG);
});
```
