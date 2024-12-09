# UDP-Server

## Programmcode

Bitte fügen Sie den folgenden Code in udpServer.php ein.

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

// Warten auf das Empfangsereignis von Daten.
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Server：{$data}");
});

// Starten des Servers
$server->start();
```

UDP-Server unterscheiden sich von TCP-Serveren darin, dass bei UDP kein Konzept von Verbindungen besteht. Nachdem der Server gestartet wurde, müssen sich Clients nicht verbinden, sondern können direkt Datenpakete an den vom Server 监听的 9502-Port senden. Das entsprechende Ereignis ist onPacket.

* `$clientInfo` enthält Informationen über den Client, es ist ein Array mit IP-Adresse und Port des Clients usw.
* Die Methode `$server->sendto` wird verwendet, um Daten an den Client zu senden.
!> Docker verwendet standardmäßig das TCP-Protokoll zur Kommunikation, wenn Sie das UDP-Protokoll verwenden möchten, müssen Sie dies durch die Konfiguration der Docker-Netzwerke erreichen.  
```shell
docker run -p 9502:9502/udp <image-name>
```

## Starten des Dienstes

```shell
php udpServer.php
```

Um den UDP-Server zu testen, kann man ihn mit `netcat -u` verbinden.

```shell
netcat -u 127.0.0.1 9502
hello
Server: hello
```
