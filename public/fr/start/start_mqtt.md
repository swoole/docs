# Serveur MQTT (Internet des Objets)

En activant l'option [open_mqtt_protocol](/server/setting?id=open_mqtt_protocol), la décompression de la tête `MQTT` est effectuée, et l'événement [onReceive](/server/events?id=onreceive) du processus Worker renvoie un paquet de données `MQTT` complet à chaque fois.

Il est possible d'utiliser Swoole comme serveur ou client MQTT pour mettre en œuvre une solution complète de réseau IoT (Internet des Objets).

> Pour une analyse complète du protocole MQTT et un client asynchrone, utilisez [simps/mqtt](https://github.com/simps/mqtt)

## Code du programme

Veuillez écrire le code suivant dans mqttServer.php

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
    'open_mqtt_protocol' => true, // Activer le protocole MQTT
    'worker_num' => 1,
]);

$server->on('Connect', function ($server, $fd) {
    echo "Client:Connect.\n";
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
        echo "client msg: {$topic}\n----------\n{$msg}\n";
        //file_put_contents(__DIR__.'/data.log', $data);
    }
    echo "received length=" . strlen($data) . "\n";
});

$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

$server->start();
```
