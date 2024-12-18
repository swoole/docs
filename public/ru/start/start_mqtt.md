# сервер MQTT (Internet вещей)

При включении опции [open_mqtt_protocol](/server/setting?id=open_mqtt_protocol), она анализирует заголовок `MQTT`, и каждый раз, когда происходит событие [onReceive](/server/events?id=onreceive) рабочего процесса, он возвращает полный пакет данных `MQTT`.

Можно использовать Swoole как сервер или клиент MQTT для реализации полноценной решения для интернета вещей (IOT).

> Полный анализ протокола MQTT и клиент с协程ами можно использовать по ссылке [simps/mqtt](https://github.com/simps/mqtt)

## Source code

Пожалуйста, перепишите следующий код в mqttServer.php

```php
function decodeValue($data)
{
    return 256 * ord($data[0]) + ord($data[1]);
}

function decodeString($data)
{
    $length = decodeValue($data);
    return substr($data, 2, $length);
}

function mqttGetHeader($data)
{
    $byte = ord($data[0]);

    $header['type'] = ($byte & 0xF0) >> 4;
    $header['dup'] = ($byte & 0x08) >> 3;
    $header['qos'] = ($byte & 0x06) >> 1;
    $header['retain'] = $byte & 0x01;

    return $header;
}

function eventConnect($header, $data)
{
    $connect_info['protocol_name'] = decodeString($data);
    $offset = strlen($connect_info['protocol_name']) + 2;

    $connect_info['version'] = ord(substr($data, $offset, 1));
    $offset += 1;

    $byte = ord($data[$offset]);
    $connect_info['willRetain'] = ($byte & 0x20 == 0x20);
    $connect_info['willQos'] = ($byte & 0x18 >> 3);
    $connect_info['willFlag'] = ($byte & 0x04 == 0x04);
    $connect_info['cleanStart'] = ($byte & 0x02 == 0x02);
    $offset += 1;

    $connect_info['keepalive'] = decodeValue(substr($data, $offset, 2));
    $offset += 2;
    $connect_info['clientId'] = decodeString(substr($data, $offset));
    return $connect_info;
}

$server = new Swoole\Server('127.0.0.1', 9501, SWOOLE_BASE);

$server->set([
    'open_mqtt_protocol' => true, // Включить протокол MQTT
    'worker_num' => 1,
]);

$server->on('Connect', function ($server, $fd) {
    echo "Клиент: Соединение.\n";
});

$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $header = mqttGetHeader($data);
    var_dump($header);

    if ($header['type'] == 1) {
        $resp = chr(32) . chr(2) . chr(0) . chr(0);
        eventConnect($header, substr($data, 2));
        $server->send($fd, $resp);
    } elseif ($header['type'] == 3) {
        $offset = 2;
        $topic = decodeString(substr($data, $offset));
        $offset += strlen($topic) + 2;
        $msg = substr($data, $offset);
        echo "Клиентское сообщение: {$topic}\n----------\n{$msg}\n";
        //file_put_contents(__DIR__.'/data.log', $data);
    }
    echo "Получена длина=" . strlen($data) . "\n";
});

$server->on('Close', function ($server, $fd) {
    echo "Клиент: Отключение.\n";
});

$server->start();
```
