# TCP-Server


## Programmcode

Bitte schreiben Sie den folgenden Code in tcpServer.php.

```php
// Erstellen Sie ein Server-Objekt, das auf dem Port 9501 des lokalen Hosts lauscht.
$server = new Swoole\Server('127.0.0.1', 9501);

// Lassen Sie sich auf das Ereignis des Connects eines Clients warten.
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// Lassen Sie sich auf das Ereignis des Empfangs von Daten warten.
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// Lassen Sie sich auf das Ereignis des Schließens einer Verbindung warten.
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// Starten Sie den Server
$server->start(); 
```

So wird ein `TCP`-Server erstellt, der auf dem lokalen Computer auf Port `9501` lauscht. Sein lógica ist sehr einfach: Wenn ein Client via Netzwerk eine `hello`-Zeichenfolge sendet, antwortet der Server mit einer `Server: hello`-Zeichenfolge.

Der `Server` ist ein asynchroner Server, daher wird der Programmcode durch das Warten auf Ereignisse geschrieben. Wenn das entsprechende Ereignis eintritt, wird der angegebene Funktion von unten aus aktiviert. Zum Beispiel wird bei einem neuen `TCP`-Verbindungsaufbau die [onConnect](/server/events?id=onconnect)-Ereignis-Callback funcioniert, und wenn eine Verbindung Daten an den Server sendet, wird die [onReceive](/server/events?id=onreceive)-Funktion aufgerufen.

* Der Server kann gleichzeitig von tausenden von Clients verbunden werden, `$fd` ist die einzigartige Kennung für die Clientverbindung.
* Um Daten an eine Clientverbindung zu senden, ruft man die `$server->send()` Methode auf, wobei der Parameter `$fd` die Clientkennung ist.
* Um eine Clientverbindung zwangsweise zu schließen, ruft man die `$server->close()` Methode auf.
* Ein Client kann die Verbindung auch aktiv schließen, was das [onClose](/server/events?id=onclose)-Ereignis-Callback auslöst.


## Ausführen des Programms

```shell
php tcpServer.php
```

Führen Sie das `server.php`-Programm aus der Befehlzeile aus, um den Server zu starten. Nach dem erfolgreichen Start kann man mit dem `netstat`-Tool sehen, dass der Server bereits auf Port `9501` lauscht.

Jetzt kann man die Server mit einem `telnet/netcat`-Tool verbinden.

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```


## Einfache Methode zur Überprüfung der Verbindung zum Server

* Unter `Linux` kann man mit `netstat -an | grep 端口` sehen, ob der Port bereits geöffnet und in einem `Listening`-Status ist.
* Nach der Bestätigung im vorherigen Schritt sollte man auch die Firewall überprüfen.
* Achten Sie darauf, welche IP-Adresse der Server verwendet. Wenn es sich um die lokale IP-Adresse `127.0.0.1` handelt, können Clients nur mit `127.0.0.1` verbunden werden.
* Wenn Sie Alibaba Cloud- oder Tencent Cloud-Dienste verwenden, müssen Sie die Entwicklungsports im Sicherheitsgruppen-Setting einrichten.

## TCP-Datagrammgrenzenproblem.

Siehe [TCP-Datagrammgrenzenproblem](/learn?id=tcp数据包边界问题).
