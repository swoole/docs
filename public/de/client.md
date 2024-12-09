# Swoole\Client

`Swoole\Client` wird kurz als `Client` bezeichnet und bietet eine Umhüllungscode für TCP/UDP/UnixSocket-Clients. Bei Verwendung ist es nur notwendig, `new Swoole\Client` zu erstellen. Es kann in Umgebungen wie FPM/Apache verwendet werden. Im Vergleich zu den traditionellen [streams](https://www.php.net/streams)-Funktionen gibt es mehrere Vorteile:

  * Die `stream`-Funktion hat eine默认-Zeitüberschreitung, die zu einem langen Antwortzeit des anderen Endes führen kann und zu langem Blockieren führen kann.
  * Die `fread`-Funktion der `stream`-Funktion hat eine default-Puffergröße von `8192`, was nicht die großen Pakete von UDP unterstützen kann.
  * Der `Client` unterstützt `waitall`, mit dem man alle Pakete auf einmal abholen kann, wenn die Größe der Pakete bekannt ist, ohne in einem Loop zu lesen.
  * Der `Client` unterstützt `UDP Connect`, was das Problem der Paketverbindung von UDP löst.
  * Der `Client` ist reines C-Code, der speziell für `sockets` zuständig ist, während die `stream`-Funktionen sehr komplex sind. Der `Client` hat eine bessere Leistung.
  * Man kann die [swoole_client_select](/client?id=swoole_client_select)-Funktion verwenden, um die gleichzeitige Kontrolle über mehrere `Client` zu erreichen.


### Vollständiges Beispiel

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


## Methoden


### __construct()

Konstruktor

```php
Swoole\Client::__construct(int $sock_type, bool $is_sync = false, string $key);
```

* **Parameter** 

  * **`int $sock_type`**
    * **Funktion**：Gibt den Typ des `socket` an【unterstützt `SWOOLE_SOCK_TCP`, `SWOOLE_SOCK_TCP6`, `SWOOLE_SOCK_UDP`, `SWOOLE_SOCK_UDP6`】. Weitere Informationen finden Sie in diesem Abschnitt [/server/methods?id=__construct]
    * **Standardwert**：Kein
    * **Andere Werte**：Keine

  * **`bool $is_sync`**
    * **Funktion**：Synchroner Blockmodus, kann nur auf `false` gesetzt werden. Wenn Sie den asynchronen Rückrufmodus verwenden möchten, verwenden Sie bitte `Swoole\Async\Client`
    * **Standardwert**：`false`
    * **Andere Werte**：Keine

  * **`string $key`**
    * **Funktion**：Gibt den `Key` für langfristige Verbindungen an【Standardmäßig wird `IP:PORT` als `key` verwendet. Derselbe `key`, selbst wenn man zweimal new setzt, wird nur eine TCP-Verbindung verwenden】
    * **Standardwert**：`IP:PORT`
    * **Andere Werte**：Keine

!> Man kann die darunter bereitgestellten Makros verwenden, um den Typ anzugeben, siehe [Konstantendefinition](/consts)

#### Erstellen von Langzeitverbindungen in PHP-FPM/Apache

```php
$cli = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
```

Wenn man das [SWOOLE_KEEP](/client?id=swoole_keep)-Flag hinzufügt, werden die bei der Erstellung von `TCP`-Verbindungen nicht geschlossen, selbst wenn der PHP-Request beendet ist oder `$cli->close()` aufgerufen wird. Bei der nächsten `connect`-Anforderung wird die zuvor erstellte Verbindung wiederverwendet. Die Art und Weise, wie Langzeitverbindungen gespeichert werden, ist standardmäßig mit `ServerHost:ServerPort` als `key`. Man kann den `key` im dritten Parameter angeben.

Das Verschwinden des `Client`-Objekts löst automatisch die [close](/client?id=close)-Methode aus, um den `socket` zu schließen.

#### Verwendung des Client in einem Server

  * Der `Client` muss innerhalb der [Ereignishandlers](/server/events) verwendet werden.
  * Der `Server` kann mit einem `socket client` in jeder Sprache verbunden werden. Ebenso kann der `Client` zu einem `socket server` in jeder Sprache verbinden

!> Bei Verwendung dieses `Client` in einem Swoole4+ Coroutine-Umwelt wird auf einen [Synchronmodus](/learn?id=同步io异步io) zurückgeschaltet.


### set()

Stellt Client-Parameter fest und muss vor [connect](/client?id=connect) ausgeführt werden.

```php
Swoole\Client->set(array $settings);
```

Die verfügbaren Konfigurationsoptionen finden Sie im Client - [Konfigurationsoptionen](/client?id=配置)


### connect()

Verbindet sich mit dem Remoteserver.

```php
Swoole\Client->connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
```

* **Parameter** 

  * **`string $host`**
    * **Funktion**：Adresse des Servers【Unterstützt die automatische asynchrone Auflösung von Domainnamen, `$host` kann direkt als Domainname eingegeben werden】
    * **Standardwert**：Kein
    * **Andere Werte**：Keine

  * **`int $port`**
    * **Funktion**：Port des Servers
    * **Standardwert**：Kein
    * **Andere Werte**：Keine

  * **`float $timeout`**
    * **Funktion**：Legt die Zeitüberschreitung fest
    * **Einheit**：Sekunden【Unterstützt浮点数, wie `1.5` bedeutet `1s`+`500ms`】
    * **Standardwert**：`0.5`
    * **Andere Werte**：Keine

  * **`int $sock_flag`**
    - Bei UDP-Typ gibt an, ob `udp_connect` aktiviert werden soll. Wenn diese Option festgelegt ist, wird der `$host` und `$port` gebunden, und dieser UDP wird Pakete von anderen Hosts an diesem Port ignorieren.
    - Bei TCP-Typ, `$sock_flag=1` bedeutet, dass ein nicht blockierender `socket` festgelegt ist, danach wird dieser fd zu einem [AsynchronIO](/learn?id=同步io异步io), und `connect` wird sofort zurückkehren. Wenn `$sock_flag` auf `1` gesetzt ist, muss man vor `send/recv` die [swoole_client_select](/client?id=swoole_client_select)-Funktion verwenden, um zu überprüfen, ob die Verbindung abgeschlossen ist.

* **Rückgabewert**

  * Erfolgreich gibt `true` zurück
  * thấtgl成功地 gibt `false` zurück, bitte überprüfen die `errCode`-Eigenschaft, um den Grund für das Scheitern zu erhalten

* **Synchronmodus**

Die `connect`-Methode wird blockieren, bis die Verbindung erfolgreich ist und `true` zurückkehrt. Zu diesem Zeitpunkt kann man Daten an den Server senden oder Daten vom Server empfangen.

```php
if ($cli->connect('127.0.0.1', 9501)) {
      $cli->send("data");
} else {
      echo "connect failed.";
}
```

Wenn die Verbindung fehlschlägt, wird `false` zurückgegeben.

> Ein synchroner TCP-Client, der die `close`-Methode ausführt, kann eine neue Verbindung zum Server herstellen, indem er erneut `Connect` aufruft.

* **Verbindungsversuch nach Misserfolg**

Wenn die Verbindung nach dem Scheitern des `connect`-Aufrufs fehlschlägt und man eine erneute Verbindung versucht, muss man zuerst die alte `socket` mit `close` schließen, sonst wird der Fehler `EINPROCESS` zurückgegeben, da der aktuelle `socket` gerade versucht, sich mit dem Server zu verbinden, und der Client weiß nicht, ob die Verbindung erfolgreich ist, daher kann er keine weitere `connect`-Anforderung ausführen. Das Aufrufen von `close` schließt den aktuellen `socket` und die Ebene erstellt neu einen `socket`, um die Verbindung zu herstellen.

!> Wenn der [SWOOLE_KEEP](/client?id=swoole_keep)-Langzeitverbindung aktiviert ist, muss der erste Parameter des Aufrufs zur `close`-Methode auf `true` gesetzt werden, um die Langzeitverbindung `socket` zwangsweise zu zerstören.

```php
if ($socket->connect('127.0.0.1', 9502) === false) {
    $socket->close(true);
    $socket->connect('127.0.0.1', 9502);
}
```

* **UDP Connect**

Standardmäßig wird die `udp connect`-Funktion nicht aktiviert. Wenn ein UDP-Client die `connect`-Funktion aufruft, wird der Socket unterhalb der Erstellung sofort erfolgreich sein. Zu diesem Zeitpunkt ist die gebundene Adresse des Sockets `0.0.0.0`, und andere Maschinen können Pakete an diesen Port senden.

Zum Beispiel `$client->connect('192.168.1.100', 9502)`, zu diesem Zeitpunkt wird das Betriebssystem einem Client-Socket zufällig einen Port `58232` zuweisen, und andere Maschinen, wie `192.168.1.101`, können auch Pakete an diesen Port senden.

?> Wenn `udp connect` nicht aktiviert ist, wird der Host-Wert, der von `getsockname` zurückgegeben wird, als `0.0.0.0` angegeben.

Wenn man den vierten Parameter auf `1` setzt, um `udp connect` zu aktivieren, `$client->connect('192.168.1.100', 9502, 1, 1)`. Zu diesem Zeitpunkt wird der Client und der Server gebunden, und die Ebene wird die Adresse des Sockets basierend auf der Adresse des Servers binden. Wenn zum Beispiel `192.168.1.100` verbunden ist, wird der aktuelle Socket an die lokale Adresse von `192.168.1.*` gebunden. Nachdem `udp connect` aktiviert wurde, wird der Client keine Pakete mehr von anderen Hosts an diesem Port empfangen.
### recv()

Vom Server wird Daten empfangen.

```php
Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
```

* **Parameter**

  * **`int $size`**
    * **Funktion**: Maximale Länge des Empfangsbuffers für die empfangenen Daten【Dieser Parameter sollte nicht zu groß festgelegt werden, da sonst viel Speicherplatz beansprucht wird】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $flags`**
    * **Funktion**: Kann zusätzliche Parameter festlegen【z.B. [Client::MSG_WAITALL](/client?id=clientmsg_waitall)】, welche Parameter Referenzieren Sie in dieser Sektion 【/client?id=Konstanten】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

* **Rückgabewert**

  * Erfolgreich: Rückkehr eines strings mit empfangenen Daten
  * Verbindung geschlossen: Rückkehr eines leeren strings
  * Fehlgeschlagen: Rückkehr von `false` und Einstellung des `$client->errCode`-Properties

* **EOF/Längeprotokoll**

  * Wenn der Client die EOF/Länge-Prüfung aktiviert hat, müssen keine `$size` und `$waitall` Parameter festgelegt werden. Die Erweiterungsebene wird den vollständigen Datenpaket zurückgeben oder `false` zurückkehren, siehe [Protokoll-Analyse](/client?id=Protokollanalyse) Kapitel.
  * Wenn ein falsches Headerfeld oder eine Länge im Headerfeld über das [package_max_length](/server/setting?id=package_max_length) festgelegt wurde, wird `recv` einen leeren string zurückkehren, und der PHP-Code sollte diese Verbindung schließen.


### send()

Daten an ein entferntes Server senden, kann nur nach dem Herstellen einer Verbindung an den Peer gesendet werden.

```php
Swoole\Client->send(string $data): int|false
```

* **Parameter**

  * **`string $data`**
    * **Funktion**: Senden von Inhalten【Unterstützt binäre Daten】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

* **Rückgabewert**

  * Erfolgreich: Rückkehr der gesendeten Datenlänge
  * Fehlgeschlagen: Rückkehr von `false` und Einstellung des `errCode`-Properties

* **Hinweis**

  * Wenn `connect` nicht ausgeführt wurde, löst das Aufrufen von `send` eine Warnung aus
  * Es gibt keine Längenbeschränkung für gesendete Daten
  * Wenn die gesendeten Daten zu groß für den Socket-Puffer sind, wird der Programm blockiert und wartet darauf, dass es beschreibbar ist


### sendfile()

Ein Dateifile an den Server senden, diese Funktion basiert auf dem `sendfile` Betriebssystemruf

```php
Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

!> sendfile kann nicht für UDP-Clients und SSL-Tunneling-Verschlüsselungen verwendet werden

* **Parameter**

  * **`string $filename`**
    * **Funktion**: Geben Sie den Pfad des zu sendenden Dateis an
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $offset`**
    * **Funktion**: Offset des hochgeladenen Dateis【Kann verwendet werden, um Daten von der Mitte des Dateis zu senden. Diese Funktion kann für die Unterstützung von Point-to-Point-Wiederherstellung verwendet werden.】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $length`**
    * **Funktion**: Größe der zu sendenden Daten【Standardmäßig die Größe des gesamten Dateis】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

* **Rückgabewert**

  * Wenn das angegebene Datei nicht existiert, wird `false` zurückgegeben
  * Erfolgreich: Rückkehr von `true`

* **Hinweis**

  * `sendfile` wird blockieren, bis das gesamte Datei gesendet ist oder ein tödlicher Fehler auftritt



### sendto()

Ein `UDP`-Datenpaket an ein beliebiges `IP:PORT`-Host senden, unterstützt nur `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6` Typen

```php
Swoole\Client->sendto(string $ip, int $port, string $data): bool
```

* **Parameter**

  * **`string $ip`**
    * **Funktion**: Die `IP`-Adresse des Zielhosts, unterstützt `IPv4/IPv6`
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $port`**
    * **Funktion**: Das Port des Zielhosts
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`string $data`**
    * **Funktion**: Der zu sendende Dateninhalt【Maximal 64K】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner


### enableSSL()

Dynamic SSL-Tunneling-Verschlüsselung aktivieren, kann nur verwendet werden, wenn beim Kompilieren von `swoole` die Option `--enable-openssl` aktiviert wurde.

```php
Swoole\Client->enableSSL(): bool
```

Wenn der Client beim Herstellen einer Verbindung mit klarer Kommunikation verwendet wird und später希望在 SSL-Tunneling-Verschlüsselung umschalten, kann die `enableSSL` Methode verwendet werden. Wenn von Anfang an SSL verwendet wurde, siehe [SSL-Konfiguration](/client?id=SSL-related). Um die SSL-Tunneling-Verschlüsselung dynamisch zu aktivieren, müssen zwei Bedingungen erfüllt sein:

  * Der Client muss beim Erstellen nicht als `SSL` festgelegt werden
  * Der Client muss bereits eine Verbindung zum Server hergestellt haben

Das Aufrufen von `enableSSL` wird blockieren, bis die SSL-Handshakes abgeschlossen sind.

* **Beispiel**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
//SSL-Tunneling-Verschlüsselung aktivieren
if ($client->enableSSL())
{
    //Handshakes abgeschlossen, jetzt sind gesendete und empfangene Daten verschlüsselt
    $client->send("hello world\n");
    echo $client->recv();
}
$client->close();
```



### getPeerCert()

Wertinformationen des Server-Zertifikats abrufen, kann nur verwendet werden, wenn beim Kompilieren von `swoole` die Option `--enable-openssl` aktiviert wurde.

```php
Swoole\Client->getPeerCert(): string|false
```

* **Rückgabewert**

  * Erfolgreich: Rückkehr eines `X509` Zertifikatsstrings
  * Fehlgeschlagen: Rückkehr von `false`

!> Dieser Methodenruf kann nur nach Abschluss des SSL-Handshakes erfolgen.
  
Möglicherweise können Sie die Zertifikatinformationen mit der `openssl` Erweiterung und der Funktion `openssl_x509_parse` analysieren.

!> muss beim Kompilieren von swoole mit [--enable-openssl](/environment?id=Kompilierungsoptionen) aktiviert werden


### verifyPeerCert()

Verifizierung des Server-Zertifikats, kann nur verwendet werden, wenn beim Kompilieren von `swoole` die Option `--enable-openssl` aktiviert wurde.

```php
Swoole\Client->verifyPeerCert()
```


### isConnected()

Rückgabe des Verbindungszustands des Clients

* Rückkehr von false, bedeutet, dass derzeit keine Verbindung zum Server besteht
* Rückkehr von true, bedeutet, dass derzeit eine Verbindung zum Server besteht

```php
Swoole\Client->isConnected(): bool
```

!> Die Methode `isConnected` gibt den AnwendungsLayer-Zustand zurück, es zeigt nur an, dass der `Client` die `connect`-Methode ausgeführt hat und erfolgreich eine Verbindung zum `Server` hergestellt hat und keine Verbindung mit `close` geschlossen hat. Der `Client` kann `send`, `recv`, `close` und andere Operationen ausführen, aber keine weitere Verbindung mit `connect` aufnehmen.  
Dies bedeutet nicht unbedingt, dass die Verbindung verwendbar ist, da beim Ausführen von `send` oder `recv` immer noch ein Fehler zurückgegeben werden kann, da der AnwendungsLayer den tatsächlichen Zustand der unteren `TCP`-Verbindung nicht kennen kann. Um den wahren Zustand der Verbindung zur Verfügung zu haben, muss der AnwendungsLayer beim Ausführen von `send` oder `recv` eine Interaktion mit dem Kern erfolgen.


### getSockName()

Gebraucht, um die lokale Host:Port des Client-Sockets zu erhalten.

!> Kann nur nach der Verbindung verwendet werden

```php
Swoole\Client->getsockname(): array|false
```

* **Rückgabewert**

```php
array('host' => '127.0.0.1', 'port' => 53652);
```


### getPeerName()

Erhalten Sie die IP-Adresse und den Port des Peer-Sockets

!> Unterstützt nur `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM` Typen

```php
Swoole\Client->getpeername(): array|false
```

Nachdem ein `UDP`-Protokoll-Kommunikationsclient ein Datenpaket an einen Server gesendet hat, ist es möglich, dass nicht von diesem Server eine Antwort an den Client gesendet wird. Mit der `getpeername` Methode können Sie die tatsächliche Antwort-Server `IP:PORT` erhalten.

!> Diese Funktion muss nach dem Aufrufen von `$client->recv()` aufgerufen werden

### Schließen()

Schließe die Verbindung.

```php
Swoole\Client->close(bool $force = false): bool
```

* **Parameter**

  * **`bool $force`**
    * **Funktion**: Erzwinge die Schließung der Verbindung【kann für das Schließen von [SWOOLE_KEEP](/client?id=swoole_keep) Langverbindungen verwendet werden】
    * **Standardwert**: Nein
    * **Andere Werte**: Nein

Wenn eine `swoole_client` Verbindung mit `close` geschlossen wurde, sollte keine weitere `connect`发起的 werden. Die richtige Praxis ist es, das aktuelle `Client` zu zerstören und ein neues `Client` zu erstellen und eine neue Verbindung zu initiieren.

Das `Client` Objekt schließt sich automatisch bei der Zerstörung.


### Schließen des Clients

Schließe den Client

```php
Swoole\Client->shutdown(int $how): bool
```

* **Parameter**

  * **`int $how`**
    * **Funktion**: Legt fest, wie der Client geschlossen wird
    * **Standardwert**: Nein
    * **Andere Werte**: Swoole\Client::SHUT_RDWR (Schließen von Lesen/Schreiben), SHUT_RD (Schließen von Lesen), Swoole\Client::SHUT_WR (Schließen von Schreiben)


### getSocket()

Erhalte das unterliegende `socket` Handle und der zurückgegebene Objekt ist ein `sockets` Ressourcen Handle.

!> Diese Methode benötigt die Abhängigkeit der `sockets` Erweiterung und muss bei der Kompilierung mit der [--enable-sockets](/environment?id= Kompilierungsoptionen) Option aktiviert werden

```php
Swoole\Client->getSocket()
```

Mit der `socket_set_option` Funktion können weitere untere `socket` Parameter festgelegt werden.

```php
$socket = $client->getSocket();
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
}
```


### swoole_client_select

Die `swoole_client_select` Funktion von `Swoole\Client` verwendet die `select` Systemanruf zur parallelen Verarbeitung von [IO-Ereigniskreisen](/learn?id= Was ist ein Eventloop), nicht `epoll_wait`. Im Gegensatz zum [Event-Modul](/event) wird diese Funktion in einem synchronen IO-Umwelt verwendet (Wenn sie in einem Swoole Worker-Prozess aufgerufen wird, führt dies dazu, dass der eigene Swoole epoll [IO-Ereigniskreis](/learn?id= Was ist ein Eventloop) keine Gelegenheit hat, auszuführen).

Funktions原型:

```php
int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);
```

* `swoole_client_select` akzeptiert vier Parameter, `$read`, `$write`, `$error` sind jeweils Dateideskriptoren für Lesbar/Schreibbar/Fehler.  
* Diese drei Parameter müssen Referenzen auf Arrayvariablen sein. Die Elemente des Arrays müssen `swoole_client` Objekte sein.
* Diese Methode basiert auf dem `select` Systemanruf und unterstützt bis zu `1024` `sockets`
* Der `$timeout` Parameter ist die Übertragungszeit für den `select` Systemanruf in Sekunden und akzeptiert eine floating-point Zahl
* Die Funktion ähnelt der PHP-native `stream_select()` Funktion, unterscheidet sich jedoch darin, dass `stream_select` nur PHP-stream Variabletypen unterstützt und schlechte Leistung aufweist.

Nach erfolgreicher Rückkehr wird die Anzahl der Ereignisse zurückgegeben und die `$read`/`$write`/`$error` Arrays werden modifiziert. Verwende `foreach` um das Array zu durchlaufen, dann ausführ die `recv`/`send` Methoden von `$item` um Daten zu senden und zu empfangen. Oder rufe `$item->close()` oder `unset($item)` auf um das `socket` zu schließen.

`swoole_client_select` gibt `0` zurück, wenn innerhalb der festgelegten Zeit keine IO verfügbar ist und der `select` Anruf abgelaufen ist.

!> Diese Funktion kann in einem `Apache/PHP-FPM` Umfeld verwendet werden    

```php
$clients = array();

for($i=0; $i< 20; $i++)
{
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //Synchron blockieren
    $ret = $client->connect('127.0.0.1', 9501, 0.5, 0);
    if(!$ret)
    {
        echo "Connect Server fail.errCode=".$client->errCode;
    }
    else
    {
    	$client->send("HELLO WORLD\n");
    	$clients[$client->sock] = $client;
    }
}

while (!empty($clients))
{
    $write = $error = array();
    $read = array_values($clients);
    $n = swoole_client_select($read, $write, $error, 0.6);
    if ($n > 0)
    {
        foreach ($read as $index => $c)
        {
            echo "Recv #{$c->sock}: " . $c->recv() . "\n";
            unset($clients[$c->sock]);
        }
    }
}
```


## Eigenschaften


### errCode

Fehlercode

```php
Swoole\Client->errCode: int
```

Wenn `connect/send/recv/close` fehlschlägt, wird automatisch der Wert von `$swoole_client->errCode` festgelegt.

Der Wert von `errCode` entspricht dem `Linux errno`. Verwende `socket_strerror`, um den Fehlercode in eine Fehlermeldung umzuwandeln.

```php
echo socket_strerror($client->errCode);
```

Siehe auch: [Liste der Linux-Fehlercodes](/other/errno?id=linux)


### sock

Das Dateideskriptor der socketverbindung.

```php
Swoole\Client->sock;
```

In PHP-Code kann dies verwendet werden

```php
$sock = fopen("php://fd/".$swoole_client->sock); 
```

* Verwende das `socket` von `Swoole\Client` in einen `stream socket` um. Du kannst Funktionen wie `fread/fwrite/fclose` verwenden, um Prozessoroperationen durchzuführen.

* [Swoole\Server](/server/methods?id=__construct) 中的`$fd` kann nicht mit dieser Methode umgewandelt werden, da `$fd` nur eine Zahl ist, der `$fd` Dateideskriptor gehört zum Hauptkernel, siehe	[SWOOLE_PROCESS](/learn?id=swoole_process) Modell.

* Der Wert von `$swoole_client->sock` kann in einen Integer umgewandelt werden, um als Arrayschlüssel verwendet zu werden.

!> Hier ist zu beachten: Der Wert der Eigenschaft `$swoole_client->sock` kann nur nach dem Aufruf von `$swoole_client->connect` erhalten werden. Bevor die Verbindung zum Server hergestellt wurde, ist der Wert dieser Eigenschaft `null`.


### reuse

Gibt an, ob diese Verbindung neu erstellt wurde oder eine bereits bestehende Verbindung wiederverwendet wird. Wird in Kombination mit [SWOOLE_KEEP](/client?id=swoole_keep) verwendet.

#### Verwendungsszenarien

Nachdem ein `WebSocket`-Client eine Verbindung zum Server hergestellt hat, muss eine Handshake durchgeführt werden. Wenn die Verbindung wiederverwendet wird, ist kein erneuter Handshake erforderlich, und der Client kann direkt WebSocket-Datenframes senden.

```php
if ($client->reuse) {
    $client->send($data);
} else {
    $client->doHandShake();
    $client->send($data);
}
```


### reuseCount

Gibt die Anzahl der Wiederverwendungen dieser Verbindung an. Wird in Kombination mit [SWOOLE_KEEP](/client?id=swoole_keep) verwendet.

```php
Swoole\Client->reuseCount;
```


### type

Gibt den Typ des `sockets` zurück, der im Konstruktor von `Swoole\Client` als `$sock_type` festgelegt wurde

```php
Swoole\Client->type;
```


### id

Gibt den Wert von `$key` zurück, der im Konstruktor von `Swoole\Client` als `$key` festgelegt wurde, und wird in Kombination mit [SWOOLE_KEEP](/client?id=swoole_keep) verwendet

```php
Swoole\Client->id;
```


### setting

Gibt die von `Swoole\Client::set()` festgelegten Einstellungen des Clients zurück

```php
Swoole\Client->setting;
```


## Konstanten


### SWOOLE_KEEP

`Swoole\Client` unterstützt die Erstellung einer TCP-Langverbindung zum Server in einem `PHP-FPM/Apache` Umfeld.使用方法:

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
```

Wenn die `SWOOLE_KEEP` Option aktiviert ist, wird ein `socket` nicht geschlossen, wenn eine Anforderung beendet wird, und die nächste `connect` verwendet automatisch die zuvor erstellte Verbindung wieder. Wenn beim Ausführen von `connect` festgestellt wird, dass die Verbindung bereits vom Server geschlossen wurde, wird eine neue Verbindung erstellt.

?> Vorteile von SWOOLE_KEEP

* `TCP` Langverbindungen können die zusätzliche IO-Belastung durch `connect` 3-Handshakes / `close` 4-挥手 reduzieren
* Reduzierung der Anzahl von `close` / `connect` Vorgängen auf dem Server


### Swoole\Client::MSG_WAITALL

  * Wenn die Client::MSG_WAITALL-Parameter festgelegt ist, muss der genaue `$size` festgelegt werden, sonst wird weiter gewartet, bis die empfangene Datenmenge `$size` erreicht ist
  * Wenn Client::MSG_WAITALL nicht festgelegt ist, darf `$size` höchstens `64K` betragen
  * Wenn ein falscher `$size` festgelegt wird, führt dies zu einem `recv` Timeout und zurück `false`

### Swoole\Client::MSG_DONTWAIT

Nicht blockierende Empfangsdaten, wird sofort zurückgegeben, egal ob Daten vorhanden sind oder nicht.

### Swoole\Client::MSG_PEEK

Blickt auf die Daten im `socket` Cache. Wenn der `MSG_PEEK` Parameter festgelegt ist, ändert die `recv` Methode, die Daten zu lesen, den Zeiger nicht, sodass die nächste Ausführung der `recv` Methode immer noch Daten von der letzten Position zurückgibt.

### Swoole\Client::MSG_OOB

Liest Out-of-Band-Daten, siehe auch "`TCP Out-of-Band-Daten`".

### Swoole\Client::SHUT_RDWR

Schließt sowohl die Leseseite als auch die Schreibseite des Clients.

### Swoole\Client::SHUT_RD

Schließt nur die Leseseite des Clients.

### Swoole\Client::SHUT_WR

Schließt nur die Schreibseite des Clients.

## Konfiguration

Der `Client` kann einige Optionen mit der `set` Methode einstellen und bestimmte Funktionen aktivieren.

### Protokoll Parsing

?> Das Protokoll-Parsing wurde gelöst, um das Problem der [TCP-Paketgrenzen](/learn?id=tcp-paketgrenzen) zu bewältigen. Die Bedeutung der entsprechenden Konfiguration ist identisch mit der von `Swoole\Server`. Weitere Informationen finden Sie im Abschnitt zur [Swoole\Server-Protokoll-Konfiguration](/server/setting?id=open_eof_check).

* **Enderzeichenprüfung**

```php
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n\r\n",
    'package_max_length' => 1024 * 1024 * 2,
));
```

* **Längenprüfung**

```php
$client->set(array(
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0, // Der N-te Byte ist der Wert der Paketlänge
    'package_body_offset' => 4, // Welche Bytes beginnen mit der Längenberechnung
    'package_max_length' => 2000000, // Maximale Protokolllänge
));
```

!> Derzeit werden [open_length_check](/server/setting?id=open_length_check) und [open_eof_check](/server/setting?id=open_eof_check) als zwei automatische Protokollverarbeitungfunktionen unterstützt;  
Nach der Konfiguration des Protokoll-Parsings wird die `recv()` Methode des Clients keine Längeparameter mehr akzeptieren und immer ein komplettes Paket zurückgeben.

* **MQTT-Protokoll**

!> Um das MQTT-Protokoll zu aktivieren, wird im [onReceive](/server/events?id=onreceive)-Rückruf das komplette MQTT-Paket empfangen.

```php
$client->set(array(
    'open_mqtt_protocol' => true,
));
```

* **Größe des Socket-Caches**	

!> Enthält den unteren OS-Socket-Cache, den Anwendungslayer-Empfangsdaten-Speichercache und den Anwendungslayer-Sende-Daten-Speichervorlagen.	

```php	
$client->set(array(	
    'socket_buffer_size' => 1024 * 1024 * 2, // 2M Cache	
));	
```

* **Deaktivierung des Nagle-Algorithmus**

```php
$client->set(array(
    'open_tcp_nodelay' => true,
));
```


### SSL-相关的

* **SSL/TLS-Zertifikatskonfiguration**

```php
$client->set(array(
    'ssl_cert_file' => $your_ssl_cert_file_path,
    'ssl_key_file' => $your_ssl_key_file_path,
));
```

* **ssl_verify_peer**

Verifiziert das Serverzertifikat.

```php
$client->set([
    'ssl_verify_peer' => true,
]);
```

Wenn aktiviert, wird überprüft, ob das Zertifikat und der Hostname übereinstimmen; wenn nicht, wird die Verbindung automatisch geschlossen.

* **Selbstsigniertes Zertifikat**

Man kann `ssl_allow_self_signed` auf `true` setzen, um selbstsignierte Zertifikate zu zulassen.

```php
$client->set([
    'ssl_verify_peer' => true,
    'ssl_allow_self_signed' => true,
]);
```

* **ssl_host_name**

Legt den Hostnamen des Servers fest, der zusammen mit `ssl_verify_peer` oder [Client::verifyPeerCert](/client?id=verifypeercert) verwendet wird.

```php
$client->set([
    'ssl_host_name' => 'www.google.com',
]);
```

* **ssl_cafile**

Wenn `ssl_verify_peer` auf `true` gesetzt ist, wird dieses Zertifikat zur Überprüfung der entfernten Zertifikate verwendet. Der Wert dieses Options ist der vollständige Pfad und der Dateiname des CA-Zertifikats im lokalen Dateisystem.

```php
$client->set([
    'ssl_cafile' => '/etc/CA',
]);
```

* **ssl_capath**

Wenn `ssl_cafile` nicht festgelegt ist oder das durch `ssl_cafile` angegebene File nicht existiert, wird in dem durch `ssl_capath` angegebenen Verzeichnis nach适用的 Zertifikaten gesucht. Dieses Verzeichnis muss ein bereits hashing-verarbeitetes Zertifikatsverzeichnis sein.

```php
$client->set([
    'ssl_capath' => '/etc/capath/',
])
```

* **ssl_passphrase**

Passwort für das lokale Zertifikat [ssl_cert_file](/server/setting?id=ssl_cert_file).

* **Beispiel**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_SSL);

$client->set(array(
    'ssl_cert_file' => __DIR__.'/ca/client-cert.pem',
    'ssl_key_file' => __DIR__.'/ca/client-key.pem',
    'ssl_allow_self_signed' => true,
    'ssl_verify_peer' => true,
    'ssl_cafile' => __DIR__.'/ca/ca-cert.pem',
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
echo "connect ok\n";
$client->send("hello world-" . str_repeat('A', $i) . "\n");
echo $client->recv();
```


### package_length_func

Legt eine Längeberechnungsfunktion fest, die die gleiche Verwendungsweise wie die [package_length_func](/server/setting?id=package_length_func) von `Swoole\Server` hat. Wird in Kombination mit [open_length_check](/server/setting?id=open_length_check) verwendet. Die Längefunktion muss eine ganze Zahl zurückgeben.

* Rückkehr `0`: Nicht genug Daten, weitere Daten müssen empfangen werden
* Rückkehr `-1`: Datenfehler, Verbindung wird automatisch vom unteren Layer geschlossen
* Rückkehr der Gesamtlänge des Pakets (einschließlich Kopf und Körper des Pakets), der untere Layer wird das Paket automatisch zusammenfügen und an die Rückruffunktion zurückgeben

Standardmäßig liest der untere Layer maximal `8K` Daten, und wenn die Länge des Paketzahns kleiner ist, kann dies zu einem Memory-Copy-Verbrauch führen. Man kann das `package_body_offset`-Parameter festlegen, sodass der untere Layer nur den Paketkopf zum Längenanalysieren liest.

* **Beispiel**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check' => true,
    'package_length_func' => function ($data) {
        if (strlen($data) < 8) {
            return 0;
        }
        $length = intval(trim(substr($data, 0, 8)));
        if ($length <= 0) {
            return -1;
        }
        return $length + 8;
    },
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


### socks5_proxy

Konfiguriert einen SOCKS5-Proxy.

!> Es ist nicht möglich, nur eine Option einzustellen, es müssen sowohl `host` als auch `port` festgelegt werden; `socks5_username` und `socks5_password` sind optionale Parameter. `socks5_port` und `socks5_password` dürfen nicht `null` sein.

```php
$client->set(array(
    'socks5_host' => '192.168.1.100',
    'socks5_port' => 1080,
    'socks5_username' => 'username',
    'socks5_password' => 'password',
));
```


### http_proxy

Konfiguriert einen HTTP-Proxy.

!> `http_proxy_port` und `http_proxy_password` dürfen nicht `null` sein.

* **Grundlegende Einstellung**

```php
$client->set(array(
    'http_proxy_host' => '192.168.1.100',
    'http_proxy_port' => 1080,
));
```

* **Verifizierungs Einstellung**

```php
$client->set(array(
    'http_proxy_user' => 'test',
    'http_proxy_password' => 'test_123456',
));
```


### bind

!> Es ist nicht möglich, nur `bind_port` einzustellen, bitte stellen Sie gleichzeitig `bind_port` und `bind_address` fest

?> Wenn ein Gerät mehrere Netzwerkkarten hat, kann das Festlegen des `bind_address`-Parameters erzwingen, dass der Client-Socket an einer bestimmten Netzwerkadresse gebunden wird.  
Das Festlegen von `bind_port` ermöglicht es dem Client-Socket, über einen festen Port an ein externes Servernetzwerk zu verbinden.

```php
$client->set(array(
    'bind_address' => '192.168.1.100',
    'bind_port' => 36002,
));
```
### Geltungsbereich

Die oben genannten `Client` Konfigurationsoptionen gelten ebenfalls für die folgenden Clients:

  * [Swoole\Coroutine\Client](/coroutine_client/client)
  * [Swoole\Coroutine\Http\Client](/coroutine_client/http_client)
  * [Swoole\Coroutine\Http2\Client](/coroutine_client/http2_client)
