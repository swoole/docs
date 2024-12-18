# множественный порт прослушивания

`Swoole\Server` может прослушивать несколько портов, каждый из которых может быть настроен для обработки различных протоколов. Например, порт 80 обрабатывает HTTP-протокол, а порт 9507 обрабатывает TCP-протокол. Трафик `SSL/TLS` также может быть зашифрован только для конкретных портов.

!> Например, если главный сервер работает по протоколу WebSocket или HTTP, новые TCP-порта (возвращение метода [listen](/server/methods?id=listen), то есть объект [Swoole\Server\Port](server/server_port.md), который далее будет называться port) по умолчанию будут наследовать настройкиprotocols главного Server. Чтобы включить новый протокол, необходимо отдельно вызвать метод `set` объекта port и метод `on`.


## Прослушивание нового порта

```php
//возвращает объект port
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```


## Установка сетевого протокола

```php
//вызов метода set объекта port
$port1->set([
	'open_length_check' => true,
	'package_length_type' => 'N',
	'package_length_offset' => 0,
	'package_max_length' => 800000,
]);

$port3->set([
	'open_eof_split' => true,
	'package_eof' => "\r\n",
	'ssl_cert_file' => 'ssl.cert',
	'ssl_key_file' => 'ssl.key',
]);
```


## Установка callback-функции

```php
//установка callback-функции для каждого port
$port1->on('connect', function ($serv, $fd){
    echo "Клиент: Соединение.\n";
});

$port1->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});

$port1->on('close', function ($serv, $fd) {
    echo "Клиент: Отключение.\n";
});

$port2->on('packet', function ($serv, $data, $addr) {
    var_dump($data, $addr);
});
```


## Http/WebSocket

`Swoole\Http\Server` и `Swoole\WebSocket\Server`, поскольку они реализованы с использованием наследных подclasses, не могут быть созданы с помощью метода `listen` инстанции `Swoole\Server` для HTTP или WebSocket серверов.

Если основная функция сервера - это `RPC`, но вы хотите предоставить простой веб-административный интерфейс. В таких случаях сначала можно создать HTTP/WebSocket сервер, а затем прослушать порт nativo TCP.


### Пример

```php
$http_server = new Swoole\Http\Server('0.0.0.0',9998);
$http_server->set(['daemonize'=> false]);
$http_server->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Привет, Swoole. №".rand(1000, 9999)."</h1>");
});

//Много прослушивать один TCP порт, предоставляет внешний TCP сервис, а также устанавливатьcallback TCP сервера
$tcp_server = $http_server->listen('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
//По умолчанию новый порт 9999 наследует настройки главного server, он тоже является HTTP протоколом
//Необходимо вызвать метод set override настройки главного server
$tcp_server->set([]);
$tcp_server->on('receive', function ($server, $fd, $threadId, $data) {
    echo $data;
});

$http_server->start();
```

С помощью такого кода можно создать сервер, который предоставляет внешний HTTP сервис, а также предоставляет внешний TCP сервис. Более конкретное изящное сочетание кода следует реализовать вам самим.


## Сочетанные настройки множественного порта для TCP, HTTP, WebSocket

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_websocket_protocol' => true, // Установите, чтобы этот порт поддерживал протокол WebSocket
]);
```

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_http_protocol' => false, // Установите, чтобы этот порт关闭 функция HTTP-протокола
]);
```

Точно так же есть: `open_http_protocol`, `open_http2_protocol`, `open_mqtt_protocol` и другие параметры.


## Пояснительные параметры

* Если порт прослушивания `port` не вызвал метод `set`, то порт прослушивания, который устанавливает опции обработки протокола, будет наследовать соответствующие настройки главного сервера.
* Если главный сервер является HTTP/WebSocket сервером и не установлены параметры протокола, то прослушиваемый порт по-прежнему будет установлен на HTTP или WebSocket протокол и не будет выполнять callback [onReceive](/server/events?id=onreceive), установленный для порта.
* Если главный сервер является HTTP/WebSocket сервером и порт прослушивания invokes `set` для установки конфигурационных параметров, то настройки главного сервера будут cleared. Прослушиваемый порт станет TCP-протоколом. Если прослушиваемый порт хочет продолжать использовать HTTP/WebSocket протокол, необходимо добавить в конфигурацию `open_http_protocol => true` и `open_websocket_protocol => true`.

**Параметы, которые может установить `port` с помощью `set`:**

* Сетевые параметры: такие как `backlog`, `open_tcp_keepalive`, `open_tcp_nodelay`, `tcp_defer_accept` и т.д.
* Протоколовые параметры: такие как `open_length_check`, `open_eof_check`, `package_length_type` и т.д.
*パラаметры по SSL сертификату: такие как `ssl_cert_file`, `ssl_key_file` и т.д.

Для подробной информации смотрите [chapters о конфигурации](/server/setting).


## Пояснительные callback

Если порт прослушивания `port` не вызвал метод `on`, то порт прослушивания сстановил callback-функцию будет использовать回调-функцию главного сервера по умолчанию. Callbacks, которые может установить `port` с помощью метода `on`:
 

### TCP сервер

* onConnect
* onClose
* onReceive


### UDP сервер

* onPacket
* onReceive
    

### HTTP сервер

* onRequest
    

### WebSocket сервер

* onMessage
* onOpen
* onHandshake

!> Callbacks для различных портов прослушивания все еще выполняются в одной и той же памяти `Worker` процесса.

## Перебор соединений под множественным портами

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9514, SWOOLE_BASE);

$tcp = $server->listen("0.0.0.0", 9515, SWOOLE_SOCK_TCP);
$tcp->set([]);

$server->on("open", function ($serv, $req) {
    echo "новый клиент WebSocket, fd={$req->fd}\n";
});

$server->on("message", function ($serv, $frame) {
    echo "получено от {$frame->fd}:{$frame->data}, opcode:{$frame->opcode}, fin:{$frame->finish}\n";
    $serv->push($frame->fd, "это сервер наMessage");
});

$tcp->on('receive', function ($server, $fd, $reactor_id, $data) {
    // Только перебирать соединения порта 9514, потому что используется $server, а не $tcp
    $websocket = $server->ports[0];
    foreach ($websocket->connections as $_fd) {
        var_dump($_fd);
        if ($server->exist($_fd)) {
            $server->push($_fd, "это сервер наReceive");
    }
});

$server->start();
```
