# UDP сервер

## Программа код

Пожалуйста, напишите следующий код в udpServer.php.

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

// Слушать событие приема данных.
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Сервер: {$data}");
});

// Запустить сервер
$server->start();
```

UDP-сервер отличается от TCP-сервера тем, что у UDP нет концепции соединения. После запуска Server клиенты не должны соединяться, они могут напрямую отправлять пакеты данных на слушаемый сервером порт 9502. Соответствующее событие - onPacket.

* `$clientInfo` - это информация о клиенте, это массив, содержащий IP-адрес и порт клиента и другие данные.
* Метод `$server->sendto` используется для отправки данных клиенту.
!> Docker по умолчанию использует протокол TCP для коммуникации, если вам нужен протокол UDP, вам необходимо настроить сеть Docker для этого.  
```shell
docker run -p 9502:9502/udp <image-name>
```

## Запустить сервис

```shell
php udpServer.php
```

Для тестирования UDP-сервера можно использовать `netcat -u`.

```shell
netcat -u 127.0.0.1 9502
hello
Сервер: hello
```
