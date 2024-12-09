# Kontextschwellenkunden <!-- {docsify-ignore-all} -->

Die folgenden Kontextschwellenkunden sind in Swoole eingebaut und die mit ⚠️ markierten sollten nicht weiter verwendet werden, da sie durch die nativen PHP-Funktionen+[Schnellkontextierung](/runtime) ersetzt werden können.

* [TCP/UDP/UnixSocket-Kunden](coroutine_client/client.md)
* [Socket-Kunden](coroutine_client/socket.md)
* [HTTP/WebSocket-Kunden](coroutine_client/http_client.md)
* [HTTP2-Kunden](coroutine_client/http2_client.md)
* [PostgreSQL-Kunden](coroutine_client/postgresql.md)
* [FastCGI-Kunden](coroutine_client/fastcgi.md)
⚠️ [Redis-Kunden](coroutine_client/redis.md)
⚠️ [MySQL-Kunden](coroutine_client/mysql.md)
* [System](/coroutine/system) System-API


## Timeout-Regeln

Alle Netzwerkanforderungen (Verbindung aufbauen, Daten senden, Daten empfangen) können Timeout haben. Es gibt drei Möglichkeiten, um Timeouts bei Swoole-Kontextschwellenkunden einzustellen:

1. Durch Passieren der Timeoutzeit als Parameter an die Methode, zum Beispiel [Co\Client->connect()](/coroutine_client/client?id=connect), [Co\Http\Client->recv()](/coroutine_client/http_client?id=recv), [Co\MySQL->query()](/coroutine_client/mysql?id=query) usw.

!> Diese Methode hat den kleinsten Einflussbereich (wirkt nur auf die aktuelle Funktionsein调用), hat die höchste Priorität (die aktuelle Funktionsein调用 ignoriert die nachfolgenden Einstellungen `2` und `3`).

2. Durch die `set()` oder `setOption()` Methode der Swoole-Kontextschwellenkundenklasse Timeouts einstellen, zum Beispiel:

```php
$client = new Co\Client(SWOOLE_SOCK_TCP);
//oder
$client = new Co\Http\Client("127.0.0.1", 80);
//oder
$client = new Co\Http2\Client("127.0.0.1", 443, true);
$client->set(array(
    'timeout' => 0.5,//Gesamt-Timeout, einschließlich Verbindung, Senden, Empfangen aller Timeouts
    'connect_timeout' => 1.0,//Verbindungs-Timeout, überschreibt das erste Gesamt-Timeout
    'write_timeout' => 10.0,//Senden-Timeout, überschreibt das erste Gesamt-Timeout
    'read_timeout' => 0.5,//Empfangs-Timeout, überschreibt das erste Gesamt-Timeout
));

//Co\Redis() hat keine write_timeout und read_timeout Einstellungen
$client = new Co\Redis();
$client->setOption(array(
    'timeout' => 1.0,//Gesamt-Timeout, einschließlich Verbindung, Senden, Empfangen aller Timeouts
    'connect_timeout' => 0.5,//Verbindungs-Timeout, überschreibt das erste Gesamt-Timeout 
));

//Co\MySQL() hat keine set Einstellungsmöglichkeit
$client = new Co\MySQL();

//Co\Socket wird durch setOption konfiguriert
$socket = new Co\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = array('sec'=>1, 'usec'=>500000);
$socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout);//Empfangszeitausfall für Daten
$socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout);//Verbindungszeitausfall und Sendenzeitausfall für Daten konfiguriert
```

!> Diese Methode hat nur Auswirkungen auf die aktuelle Klasse, wird von der ersten Methode überschattet und ignoriert die nachfolgende dritte Einstellung.

3. Wie man sieht, sind die oben genannten Timeout-Einstellungsregeln für die zweite Methode sehr mühsam und nicht einheitlich. Um Entwicklern zu vermeiden, überall vorsichtig einzustellen, bieten alle Kontextschwellenkunden ab Version `v4.2.10` eine globale einheitliche Timeout-Regel. Dieser Einfluss ist am größten, die Priorität ist am niedrigsten, wie folgt:

```php
Co::set([
    'socket_timeout' => 5,
    'socket_connect_timeout' => 1,
    'socket_read_timeout' => 1,
    'socket_write_timeout' => 1,
]);
```

+ `-1`: bedeutet nie Timeout
+ `0`: bedeutet keine Timeoutzeitänderung
+ `andere Werte größer als 0`: bedeuten die Einstellung eines Timeout-Zählers in Sekunden, die größte Genauigkeit beträgt `1 Millisekunde`, es ist ein Gleitkomma, `0.5` steht für `500 Millisekunden`
+ `socket_connect_timeout`: bedeutet die Timeoutzeit für die TCP-Verbindung aufbauen, **Standard ist `1 Sekunde`**, ab Version `v4.5.x` ist es **Standard `2 Sekunden`**
+ `socket_timeout`: bedeutet die Timeoutzeit für TCP-Lese-/Schreibvorgänge, **Standard ist `-1`**, ab Version `v4.5.x` ist es **Standard `60 Sekunden`** . Wenn man separate Lese- und Schreibzeiten einstellen möchte, siehe die folgende Konfiguration
+ `socket_read_timeout`: wurde in Version `v4.3` hinzugefügt, bedeutet die Timeoutzeit für TCP**Lesen**vorgänge, **Standard ist `-1`**, ab Version `v4.5.x` ist es **Standard `60 Sekunden`**
+ `socket_write_timeout`: wurde in Version `v4.3` hinzugefügt, bedeutet die Timeoutzeit für TCP**Schreiben**vorgänge, **Standard ist `-1`**, ab Version `v4.5.x` ist es **Standard `60 Sekunden`**

!> **Das heißt:** Bei allen Swoole-Kontextschwellenkunden vor der Version `v4.5.x`, wenn keine Timeouts mit den ersten beiden Methoden eingestellt wurden, ist die Standardverbindungszeit `1s`, und es gibt nie Timeouts für Lesevorgänge und Schreibvorgänge;  
Ab der Version `v4.5.x` ist die Standardverbindungszeit `60 Sekunden`, und die Timeoutzeiten für Lesevorgänge und Schreibvorgänge sind ebenfalls `60 Sekunden`;  
Wenn man die globalen Timeouts mittendrin ändert, sind sie für bereits erstellte Sockets nicht wirksam.

### PHP offizielles Netzwerkbibliothek Timeout

Neben den oben genannten Swoole-Kontextschwellenkunden wird in [Schnellkontextierung](/runtime) die nativa PHP-Methode verwendet, deren Timeoutzeit durch die [default_socket_timeout](http://php.net/manual/zh/filesystem.configuration.php)-Konfiguration beeinflusst wird. Entwickler können es durch `ini_set('default_socket_timeout', 60)` einzeln einstellen, wobei der Standardwert 60 ist.
