# Coroutine\Socket

Das `Swoole\Coroutine\Socket`-Modul ermöglicht im Vergleich zu den [koroutine-stilen Servern](/server/co_init) und [koroutine-Kllienten](/coroutine_client/init) die Durchführung einer feineren Kontrolle über einige `IO` Operationen.

!> Mit `Co\Socket` kann man die Klassenname verkürzen. Dieses Modul ist ziemlich niedriglevel, und es ist ratsam, dass die Nutzer über Erfahrung im Socket-Programmieren verfügen.


## Vollständiges Beispiel

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9601);
    while ($retval)
    {
        $n = $socket->send('hello');
        var_dump($n);

        $data = $socket->recv();
        var_dump($data);

        //Fehler auftreten oder das另一方 die Verbindung schließt, muss auch dies Seite schließen
        if ($data === '' || $data === false) {
            echo "errCode: {$socket->errCode}\n";
            $socket->close();
            break;
        }

        Coroutine::sleep(1.0);
    }

    var_dump($retval, $socket->errCode, $socket->errMsg);
});
```


## Koroutine-Planung

Die vom `Coroutine\Socket`-Modul bereitgestellten `IO` Operationen sind alle synchron gestaltet, und der Bodenautomatisch mit dem [Koroutine-Planer](/coroutine?id=协程调度) umgesetzt wird, um [Asynchrone IO](/learn?id=同步io异步io) zu ermöglichen.


## Fehlermuster

Beim Ausführen von `socket`-bezogenen Systemaufrufen kann ein Fehlercode -1 zurückgegeben werden, und der Boden setzt das `Coroutine\Socket->errCode` Attribut auf den Systemfehlercode `errno`. Bitte beziehen Sie sich auf die entsprechende `man`-Dokumentation. Zum Beispiel können Sie den Sinn der `errCode` beim Rückkehrwert `$socket->accept()` aus der `man accept`-Dokumentation entnehmen.


## Eigenschaften


### fd

Die Dateideskriptor-ID des `sockets`


### errCode

Fehlercode


## Methoden


### __construct()

Konstruktor. Erstellt ein `Coroutine\Socket` Objekt.

```php
Swoole\Coroutine\Socket::__construct(int $domain, int $type, int $protocol);
```

!> Weitere Informationen finden Sie in der `man socket`-Dokumentation.

  * **Parameter** 

    * **`int $domain`**
      * **Funktion**: Protokolldomain【Möglich sind `AF_INET`, `AF_INET6`, `AF_UNIX`】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $type`**
      * **Funktion**: Typ【Möglich sind `SOCK_STREAM`, `SOCK_DGRAM`, `SOCK_RAW`】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $protocol`**
      * **Funktion**: Protokoll【Möglich sind `IPPROTO_TCP`, `IPPROTO_UDP`, `IPPROTO_STCP`, `IPPROTO_TIPC`, `0`】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

!> Der Konstruktor ruft den `socket`-Systemaufruf auf, um ein `socket`-Handle zu erstellen. Bei Misserfolg wird eine `Swoole\Coroutine\Socket\Exception`-Ausnahme geworfen und das `$socket->errCode` Attribut festgelegt. Die Ursache für den Systemaufruffehler kann durch den Wert dieses Attributs ermittelt werden.


### getOption()

Konfiguration abrufen.

!> Diese Methode entspricht dem `getsockopt`-Systemaufruf, siehe auch die `man getsockopt`-Dokumentation.  
Diese Methode ist äquivalent zur `socket_get_option`-Funktion der `sockets`-Erweiterung, siehe auch die [PHP-Dokumentation](https://www.php.net/manual/zh/function.socket-get-option.php).

!> Swoole-Version >= v4.3.2

```php
Swoole\Coroutine\Socket->getOption(int $level, int $optname): mixed
```

  * **Parameter** 

    * **`int $level`**
      * **Funktion**: Spezifiziertes Level für die Option
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

      !> Zum Beispiel, um eine Option auf Socket-Level abzurufen, wird der `level` Parameter mit `SOL_SOCKET` verwendet.  
      Andere Ebenen können durch Angeben des Protokollnummers für diese Ebene verwendet werden, zum Beispiel `TCP`. Die [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php)-Funktion kann verwendet werden, um das Protokollnummer zu finden.

    * **`int $optname`**
      * **Funktion**: Verfügbare Socket-Optionen sind identisch mit den Socket-Optionen der [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)-Funktion
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner


### setOption()

Konfiguration anpassen.

!> Diese Methode entspricht dem `setsockopt`-Systemaufruf, siehe auch die `man setsockopt`-Dokumentation. Diese Methode ist äquivalent zur `socket_set_option`-Funktion der `sockets`-Erweiterung, siehe auch die [PHP-Dokumentation](https://www.php.net/manual/zh/function.socket-set-option.php).

!> Swoole-Version >= v4.3.2

```php
Swoole\Coroutine\Socket->setOption(int $level, int $optname, mixed $optval): bool
```

  * **Parameter** 

    * **`int $level`**
      * **Funktion**: Spezifiziertes Level für die Option
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

      !> Zum Beispiel, um eine Option auf Socket-Level abzurufen, wird der `level` Parameter mit `SOL_SOCKET` verwendet.  
      Andere Ebenen können durch Angeben des Protokollnummers für diese Ebene verwendet werden, zum Beispiel `TCP`. Die [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php)-Funktion kann verwendet werden, um das Protokollnummer zu finden.

    * **`int $optname`**
      * **Funktion**: Verfügbare Socket-Optionen sind identisch mit den Socket-Optionen der [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)-Funktion
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $optval`**
      * **Funktion**: Wert der Option 【Kann `int`, `bool`, `string`, `array` sein. Abhängig von `level` und `optname`】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner


### setProtocol()

Lassen Sie das `socket` die Protokollverarbeitung Capabilities erwerben, und Sie können konfigurieren, ob die Übertragung mit SSL verschlüsselt wird und lösen Sie Probleme mit dem [TCP-Paket-Grenzproblem](/learn?id=tcp数据包边界问题) usw.

!> Swoole-Version >= v4.3.2

```php
Swoole\Coroutine\Socket->setProtocol(array $settings): bool
```

  * **$settings Unterstützte Parameter**


Parameter | Typ
---|---
open_ssl | bool
ssl_cert_file | string
ssl_key_file | string
open_eof_check | bool
open_eof_split | bool
open_mqtt_protocol | bool
open_fastcgi_protocol | bool
open_length_check | bool
package_eof | string
package_length_type | string
package_length_offset | int
package_body_offset | int
package_length_func | callable
package_max_length | int

!> Alle oben genannten Parameter haben die gleiche Bedeutung wie die im [Server->set()](/server/setting?id=open_eof_check) und werden hier nicht weiter ausgeführt.

  * **Beispiel**

```php
$socket->setProtocol([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024,
    'package_length_type'   => 'N',
    *  'package_length_offset' => 0,
    *  'package_body_offset'   => 4,
]);
```


### bind()

Adresse und Port binden.

!> Diese Methode führt keine `IO` Operationen durch und löst daher keinen Wechsel der Koroutine aus

```php
Swoole\Coroutine\Socket->bind(string $address, int $port = 0): bool
```

  * **Parameter** 

    * **`string $address`**
      * **Funktion**: Bindeadresse 【Zum Beispiel `0.0.0.0`, `127.0.0.1`】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $port`**
      * **Funktion**: : Bindeport 【Standardwert ist `0`, der System wählt zufällig einen verfügbaren Port, der mit der [getsockname](/coroutine_client/socket?id=getsockname) Methode ermittelt werden kann】
      * **Standardwert**: `0`
      * **Andere Werte**: Keiner

  * **Rückgabewert** 

    * Bei erfolgreicher Bindung wird `true` zurückgegeben
    * Bei Misserfolg wird `false` zurückgegeben, bitte überprüfen Sie das `$socket->errCode` Attribut, um die Ursache des Fehlers zu ermitteln

### listen()

Hören Sie auf ein `Socket`.

!> Diese Methode führt keine `IO`-Operationen durch und verursacht keinen Wechsel der Coroutine

```php
Swoole\Coroutine\Socket->listen(int $backlog = 0): bool
```

  * **Parameter** 

    * **`int $backlog`**
      * **Funktion** : Die Länge der Warteschlange überwachen【Standardwert ist `0`, der Systemunterboden verwendet `epoll` zur asynchronen `IO`, es gibt keine Blockierung, daher ist die Wichtigkeit von `backlog` nicht hoch】
      * **Standardwert** : `0`
      * **Andere Werte** : Keine

      !> Wenn es im Anwendungsbereich Blockierungen oder zeitaufwendige Logiken gibt und die `accept` Verbindung nicht rechtzeitig akzeptiert wird, werden neue Verbindungen in der `backlog` Warteschlange gesammelt. Wenn sie die Länge von `backlog` überschreitet, wird der Service neue Verbindungen ablehnen, die eingehen möchten

  * **Rückgabewert** 

    * Wenn die Bindung erfolgreich ist, wird `true` zurückgegeben
    * Wenn die Bindung fehlschlägt, wird `false` zurückgegeben, bitte überprüfen Sie das `errCode` Attribut, um den Grund für das Scheitern zu erhalten

  * **Kernel-Parameter** 

    Die maximale Größe von `backlog` ist durch den Kernel-Parameter `net.core.somaxconn` begrenzt, und auf einem `Linux`-System kann das Werkzeug `sysctl` alle Kernel-Parameter dynamisch anpassen. Die dynamische Anpassung ist sofort wirksam, nachdem der Kernel-Parameterwert geändert wurde. Diese Wirksamkeit ist jedoch nur auf OS-Ebene begrenzt, und der Anwendungsprozess muss neu gestartet werden, um die Änderungen wirklich zu aktivieren. Die Befehl `sysctl -a` zeigt alle Kernel-Parameter und ihre Werte an.

    ```shell
    sysctl -w net.core.somaxconn=2048
    ```

    Der obige Befehl ändert den Wert des Kernel-Parameters `net.core.somaxconn` auf `2048`. Obwohl diese Änderung sofort wirksam ist, wird sie nach einem Neustart des Systems auf den Standardwert zurückgesetzt. Um die Änderung dauerhaft zu bewahren, muss der `/etc/sysctl.conf`-Datei bearbeitet werden, indem `net.core.somaxconn=2048` hinzugefügt wird, und dann der Befehl `sysctl -p` ausgeführt, um die Änderungen zu aktivieren.


### accept()

Akzeptieren Sie eine Verbindung, die von einem Client initiiert wurde.

Der Aufruf dieser Methode wird die aktuelle Coroutine sofort blockieren und der Coroutine zur Überwachung von Lesevorgängen im [EventLoop](/learn?id=什么是eventloop) hinzufügen. Wenn das `Socket` lesbar ist und eine Ankunft von Verbindungen vorliegt, wird die Coroutine automatisch geweckt und ein `Socket`-Objekt für den entsprechenden Client zurückgegeben.

!> Diese Methode muss nach dem Einsatz der `listen` Methode verwendet werden und ist für den `Server`-Endpunkt geeignet.

```php
Swoole\Coroutine\Socket->accept(float $timeout = 0): Coroutine\Socket|false;
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion** : Timeout festlegen【Nachdem der Timeout-Parameter festgelegt wurde, wird im unteren Layer ein Timer eingerichtet. Wenn innerhalb der festgelegten Zeit keine Clientverbindung eintrifft, wird die `accept` Methode `false` zurückgeben】
      * **Wertbereich** : Sekunden【Unterstützt floating-point Werte, wie `1.5`, was `1s`+`500ms` bedeutet】
      * **Standardwert** : Siehe [Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)
      * **Andere Werte** : Keine

  * **Rückgabewert** 

    * Wenn das Timeout oder die `accept` Systemanrufung einen Fehler zurückgibt, wird `false` zurückgegeben. Verwenden Sie das `errCode` Attribut, um den Fehlercode zu erhalten, wobei der Timeout-Fehlercode `ETIMEDOUT` ist
    * Erfolgreich zurückgegeben wird ein `socket` für die Clientverbindung, der ebenfalls ein `Swoole\Coroutine\Socket`-Objekt ist. An ihm können Operationen wie `send`, `recv`, `close` usw. durchgeführt werden

  * **Beispiel**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
$socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
$socket->bind('127.0.0.1', 9601);
$socket->listen(128);

    while(true) {
        echo "Accept: \n";
        $client = $socket->accept();
        if ($client === false) {
            var_dump($socket->errCode);
        } else {
            var_dump($client);
        }
    }
});
```


### connect()

Verbinden Sie sich mit dem Zielserver.

Der Aufruf dieser Methode wird eine asynchrone `connect` Systemanrufung initiieren und die aktuelle Coroutine blockieren. Im unteren Layer wird auf Schreibbar gewartet und wenn die Verbindung abgeschlossen ist oder fehlschlägt, wird die Coroutine wieder fortgesetzt.

Diese Methode ist für den `Client`-Endpunkt geeignet und unterstützt `IPv4`, `IPv6` und [unixSocket](/learn?id=什么是IPC).

```php
Swoole\Coroutine\Socket->connect(string $host, int $port = 0, float $timeout = 0): bool
```

  * **Parameter** 

    * **`string $host`**
      * **Funktion** : Die Adresse des Zielservers【Zum Beispiel `127.0.0.1`, `192.168.1.100`, `/tmp/php-fpm.sock`, `www.baidu.com` usw., es können IP-Adressen, Unix-Socket-Pfade oder Domainnamen eingegeben werden. Wenn es sich um einen Domainnamen handelt, wird der untere Layer automatisch eine asynchrone `DNS`-Abfrage durchführen und keine Blockierung verursachen】
      * **Standardwert** : Keine
      * **Andere Werte** : Keine

    * **`int $port`**
      * **Funktion** : Die Portnummer des Zielservers【Wenn das `Socket`-`domain` `AF_INET` oder `AF_INET6` ist, muss der Port festgelegt werden】
      * **Standardwert** : Keine
      * **Andere Werte** : Keine

    * **`float $timeout`**
      * **Funktion** : Timeout festlegen【Im unteren Layer wird ein Timer eingerichtet. Wenn innerhalb der festgelegten Zeit keine Verbindung hergestellt werden kann, wird die `connect` Methode `false` zurückgeben】
      * **Wertbereich** : Sekunden【Unterstützt floating-point Werte, wie `1.5`, was `1s`+`500ms` bedeutet】
      * **Standardwert** : Siehe [Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)
      * **Andere Werte** : Keine

  * **Rückgabewert** 

    * Wenn das Timeout oder die `connect` Systemanrufung einen Fehler zurückgibt, wird `false` zurückgegeben. Verwenden Sie das `errCode` Attribut, um den Fehlercode zu erhalten, wobei der Timeout-Fehlercode `ETIMEDOUT` ist
    * Erfolgreich zurückgegeben wird `true`


### checkLiveness()

Prüfen Sie durch eine Systemanrufung, ob die Verbindung noch lebendig ist (unwirksam bei abnormaler Trennung, kann nur eine Trennung durch ein normales Schließen des anderen Endes erkennen)

!> Swoole-Version >= `v4.5.0` verfügbar

```php
Swoole\Coroutine\Socket->checkLiveness(): bool
```

  * **Rückgabewert** 

    * Wenn die Verbindung noch lebendig ist, wird `true` zurückgegeben, sonst `false`


### send()

Daten an das另一方 senden.

!> Die `send` Methode führt sofort eine `send` Systemanrufung durch, um Daten zu senden. Wenn die `send` Systemanrufung einen Fehler `EAGAIN` zurückgibt, wird der untere Layer automatisch auf Schreibbar warten und die aktuelle Coroutine blockieren. Wenn das Schreibbare Ereignis eintritt, wird die `send` Systemanrufung erneut ausgeführt, um Daten zu senden, und die Coroutine wird geweckt.  

!> Wenn Sie zu schnell `send` und zu langsam `recv` machen, führt dies letztendlich dazu, dass der Betriebssystem-Puffer voll ist. Die aktuelle Coroutine hängt in der `send` Methode fest. Sie können den Puffer angemessen vergrößern, [/proc/sys/net/core/wmem_max und SO_SNDBUF](https://stackoverflow.com/questions/21856517/whats-the-practical-limit-on-the-size-of-single-packet-transmitted-over-domain)

```php
Swoole\Coroutine\Socket->send(string $data, float $timeout = 0): int|false
```

  * **Parameter** 

    * **`string $data`**
      * **Funktion** : Die zu sendenden Dateninhalte【Können Text oder Binärdaten sein】
      * **Standardwert** : Keine
      * **Andere Werte** : Keine

    * **`float $timeout`**
      * **Funktion** : Timeout festlegen
      * **Wertbereich** : Sekunden【Unterstützt floating-point Werte, wie `1.5`, was `1s`+`500ms` bedeutet】
      * **Standardwert** : Siehe [Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)
      * **Andere Werte** : Keine

  * **Rückgabewert** 

    * Wenn die Übertragung erfolgreich ist, wird die Anzahl der geschriebenen字节 zurückgegeben. **Bitte beachten Sie, dass die tatsächlich geschriebenen Daten möglicherweise kleiner als die Länge des `$data`-Parameters sind**. Die Anwendungscodeblock muss vergleichen, ob der Rückgabewert gleich der Länge von `strlen($data)` ist, um zu bestimmen, ob die Übertragung abgeschlossen ist
    * Wenn die Übertragung fehlschlägt, wird `false` zurückgegeben und das `errCode` Attribut wird festgelegt
### sendAll()

Sende Daten an den peer. Im Gegensatz zum `send` Methoden, sendet `sendall` Daten so vollständig wie möglich, bis alle Daten erfolgreich gesendet wurden oder ein Fehler auftritt und die Übertragung abgebrochen wird.

!> Die `sendall` Methode führt sofort mehrere `send` Systemaufrufe aus, um Daten zu senden. Wenn der `send` Systemaufruf die Fehlermeldung `EAGAIN` zurückgibt, wartet die Basisebene automatisch auf schreibbare Ereignisse und hält die aktuelle Coroutine an, bis das schreibbare Ereignis ausgelöst wird, um den `send` Systemaufruf erneut auszuführen und Daten zu senden, bis die Übertragung abgeschlossen ist oder ein Fehler auftritt, was die entsprechende Coroutine weckt.  

!> Swoole Version >= v4.3.0

```php
Swoole\Coroutine\Socket->sendAll(string $data, float $timeout = 0) : int | false;
```

  * **Parameter** 

    * **`string $data`**
      * **Funktion**: Der zu sendende Dateninhalt【kann Text- oder Binärdaten sein】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`float $timeout`**
      * **Funktion**: Festlegen der Übertragungszeit
      * **Einheit**: Sekunden【unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**: Siehe [Client-Übertragungszeitregeln](/coroutine_client/init?id=Übertragungszeitregeln)
      * **Andere Werte**: Keiner

  * **Rückkehrwert** 

    * `sendall` stellt sicher, dass alle Daten erfolgreich gesendet wurden, aber während der Übertragung kann der peer die Verbindung trennen, und in diesem Fall wurden möglicherweise nur einige Daten erfolgreich gesendet. Der Rückkehrwert gibt die Länge der erfolgreich gesendeten Daten zurück. Die Anwendungscodebene muss den Rückkehrwert mit `strlen($data)` vergleichen, um zu überprüfen, ob die Übertragung abgeschlossen ist, und ob eine Fortsetzung der Übertragung erforderlich ist, basierend auf den Geschäftsanforderungen.
    * Bei einem Übertragungsfehler wird `false` zurückgegeben, und die `errCode`-Eigenschaft wird festgelegt.


### peek()

Blick in den Lesebufffer, tương相当于 das Systemaufruf `recv(length, MSG_PEEK)`.

!> `peek` ist sofort abgeschlossen und hängt keine Coroutine an, aber es gibt die Kosten für einen Systemaufruf

```php
Swoole\Coroutine\Socket->peek(int $length = 65535): string|false
```

  * **Parameter** 

    * **`int $length`**
      * **Funktion**: Angeben der Größe des Speichers für die kopierte peeked Daten (Beachten Sie: Hier wird Speicher zugewiesen, eine zu große Länge kann zu einem Speicherausbruch führen)
      * **Einheit**: Byte
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Rückkehrwert** 

    * Bei Erfolg wird die peeked Daten zurückgegeben
    * Bei Misserfolg wird `false` zurückgegeben und die `errCode`-Eigenschaft wird festgelegt


### recv()

Empfangen von Daten.

!> Die `recv` Methode hängt sofort die aktuelle Coroutine an und wartet auf ein lesbares Ereignis. Nachdem der peer Daten gesendet hat, wird das lesbare Ereignis ausgelöst, der `recv` Systemaufruf wird ausgeführt, um Daten aus dem `socket` Cache zu empfangen, und die entsprechende Coroutine wird geweckt.

```php
Swoole\Coroutine\Socket->recv(int $length = 65535, float $timeout = 0): string|false
```

  * **Parameter** 

    * **`int $length`**
      * **Funktion**: Angeben der Größe des Speichers für die empfangenen Daten (Beachten Sie: Hier wird Speicher zugewiesen, eine zu große Länge kann zu einem Speicherausbruch führen)
      * **Einheit**: Byte
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`float $timeout`**
      * **Funktion**: Festlegen der Übertragungszeit
      * **Einheit**: Sekunden【unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**: Siehe [Client-Übertragungszeitregeln](/coroutine_client/init?id=Übertragungszeitregeln)
      * **Andere Werte**: Keiner

  * **Rückkehrwert** 

    * Bei Erfolg wird die tatsächlich empfangene Daten zurückgegeben
    * Bei Misserfolg wird `false` zurückgegeben und die `errCode`-Eigenschaft wird festgelegt
    * Bei Übertragungszeitüberschreitung wird der Fehlercode `ETIMEDOUT` zurückgegeben

!> Der Rückkehrwert ist nicht unbedingt gleich der erwarteten Länge, es ist notwendig, die Länge der empfangenen Daten bei diesem Aufruf selbst zu überprüfen. Wenn sichergestellt werden muss, dass bei einem Aufruf eine spezifische Länge von Daten empfangen wird, verwenden Sie die `recvAll` Methode oder rufen Sie selbst in einem Loop Daten ab  
Für Probleme mit der TCP-Paketgrenze siehe die `setProtocol()` Methode oder verwenden Sie `sendto()`;


### recvAll()

Empfangen von Daten. Im Gegensatz zu `recv` wird `recvAll` so vollständig wie möglich antworten, bis die Übertragung abgeschlossen ist oder ein Fehler auftritt und die Übertragung abgebrochen wird.

!> Die `recvAll` Methode hängt sofort die aktuelle Coroutine an und wartet auf ein lesbares Ereignis. Nachdem der peer Daten gesendet hat, wird das lesbare Ereignis ausgelöst, der `recv` Systemaufruf wird ausgeführt, um Daten aus dem `socket` Cache zu empfangen, und das Verhalten wird wiederholt, bis die erwartete Länge der Daten empfangen wurde oder ein Fehler auftritt, was die entsprechende Coroutine weckt.

!> Swoole Version >= v4.3.0

```php
Swoole\Coroutine\Socket->recvAll(int $length = 65535, float $timeout = 0): string|false
```

  * **Parameter** 

    * **`int $length`**
      * **Funktion**: Die erwartete Größe der empfangenen Daten (Beachten Sie: Hier wird Speicher zugewiesen, eine zu große Länge kann zu einem Speicherausbruch führen)
      * **Einheit**: Byte
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`float $timeout`**
      * **Funktion**: Festlegen der Übertragungszeit
      * **Einheit**: Sekunden【unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**: Siehe [Client-Übertragungszeitregeln](/coroutine_client/init?id=Übertragungszeitregeln)
      * **Andere Werte**: Keiner

  * **Rückkehrwert** 

    * Bei Erfolg wird die tatsächlich empfangene Daten zurückgegeben, und die Länge des zurückgegebenen Strings ist gleich der Parameterlänge
    * Bei Misserfolg wird `false` zurückgegeben und die `errCode`-Eigenschaft wird festgelegt
    * Bei Übertragungszeitüberschreitung wird der Fehlercode `ETIMEDOUT` zurückgegeben


### readVector()

Teilweise Empfang von Daten.

!> Die `readVector` Methode führt sofort den `readv` Systemaufruf aus, um Daten zu lesen. Wenn der `readv` Systemaufruf die Fehlermeldung `EAGAIN` zurückgibt, wartet die Basisebene automatisch auf lesbare Ereignisse und hält die aktuelle Coroutine an, bis das lesbare Ereignis ausgelöst wird, um den `readv` Systemaufruf erneut auszuführen und Daten zu lesen, was die entsprechende Coroutine weckt.  

!> Swoole Version >= v4.5.7

```php
Swoole\Coroutine\Socket->readVector(array $io_vector, float $timeout = 0): array|false
```

  * **Parameter** 

    * **`array $io_vector`**
      * **Funktion**: Die erwartete Größe der zu empfangenden Datenabschnitte
      * **Einheit**: Byte
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`float $timeout`**
      * **Funktion**: Festlegen der Übertragungszeit
      * **Einheit**: Sekunden【unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**: Siehe [Client-Übertragungszeitregeln](/coroutine_client/init?id=Übertragungszeitregeln)
      * **Andere Werte**: Keiner

  * **Rückkehrwert**

    * Bei Erfolg wird der empfangene Datenabschnitt zurückgegeben
    * Bei Misserfolg wird eine leere Array zurückgegeben und die `errCode`-Eigenschaft wird festgelegt
    * Bei Übertragungszeitüberschreitung wird der Fehlercode `ETIMEDOUT` zurückgegeben

  * **Beispiel** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// Wenn der peer helloworld gesendet hat
$ret = $socket->readVector([5, 5]);
// Dann ist $ret ['hello', 'world']
```


### readVectorAll()

Teilweise Empfang von Daten.

!> Die `readVectorAll` Methode führt sofort mehrere `readv` Systemaufrufe aus, um Daten zu lesen. Wenn der `readv` Systemaufruf die Fehlermeldung `EAGAIN` zurückgibt, wartet die Basisebene automatisch auf lesbare Ereignisse und hält die aktuelle Coroutine an, bis das lesbare Ereignis ausgelöst wird, um den `readv` Systemaufruf erneut auszuführen und Daten zu lesen, bis die Datenlesung abgeschlossen ist oder ein Fehler auftritt, was die entsprechende Coroutine weckt.

!> Swoole Version >= v4.5.7

```php
Swoole\Coroutine\Socket->readVectorAll(array $io_vector, float $timeout = 0): array|false
```

  * **Parameter** 

    * **`array $io_vector`**
      * **Funktion**: Die erwartete Größe der zu empfangenden Datenabschnitte
      * **Einheit**: Byte
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`float $timeout`**
      * **Funktion**: Festlegen der Übertragungszeit
      * **Einheit**: Sekunden【unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**: Siehe [Client-Übertragungszeitregeln](/coroutine_client/init?id=Übertragungszeitregeln)
      * **Andere Werte**: Keiner

  * **Rückkehrwert**

    * Bei Erfolg wird der empfangene Datenabschnitt zurückgegeben
    * Bei Misserfolg wird eine leere Array zurückgegeben und die `errCode`-Eigenschaft wird festgelegt
    * Bei Übertragungszeitüberschreitung wird der Fehlercode `ETIMEDOUT` zurückgegeben
### writeVector()

Teilweise Übertragung von Daten.

!> Die `writeVector` Methode führt sofort eine `writev` Systemanruf durch, um Daten zu senden. Wenn die `writev` Systemanruf einen Fehler `EAGAIN` zurückgibt, wartet der untere Level automatisch auf Schreibbare Ereignisse und hält die aktuelle Coroutine an, bis das Schreibbare Ereignis eintritt, um den `writev` Systemanruf erneut auszuführen und Daten zu senden, und weckt dann die entsprechende Coroutine.  

!> Swoole Version >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVector(array $io_vector, float $timeout = 0): int|false
```

  * **Parameter** 

    * **`array $io_vector`**
      * **Funktion**：Erwartete segmentierte Daten zum Senden
      * **Einheit des Wertes**：Byte
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`float $timeout`**
      * **Funktion**：Einstellung der Übertragungszeit
      * **Einheit des Wertes**：Sekunden【Unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：Siehe [Client-Übertragungsregeln](/coroutine_client/init?id=Übertragungsregeln)
      * **Andere Werte**：Keine

  * **Rückgabewert**

    * Bei erfolgreicher Übertragung wird die Anzahl der übertragenen Bytes zurückgegeben, **bitte beachten Sie, dass die tatsächlich übertragenen Daten möglicherweise kleiner als die Gesamtlänge des `$io_vector` Parameters sind**, die Anwendungslayer-Code muss vergleichen, ob der Rückgabewert der Gesamtlänge des `$io_vector` Parameters entspricht, um zu überprüfen, ob die Übertragung abgeschlossen ist
    * Bei Übertragungsschaden wird `false` zurückgegeben und die `errCode` Eigenschaft wird festgelegt

  * **Beispiel** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// In diesem Fall werden die Daten in der Reihenfolge des Arrays an den Empfänger gesendet, was tatsächlich "helloworld" sendet
$socket->writeVector(['hello', 'world']);
```


### writeVectorAll()

Daten an den Empfänger senden. Im Gegensatz zur `writeVector` Methode wird bei `writeVectorAll` versucht, Daten so vollständig wie möglich zu senden, bis alle Daten erfolgreich gesendet wurden oder ein Fehler auftritt und die Übertragung abgebrochen wurde.

!> Die `writeVectorAll` Methode führt sofort mehrere `writev` Systemanrufe durch, um Daten zu senden. Wenn die `writev` Systemanruf einen Fehler `EAGAIN` zurückgibt, wartet der untere Level automatisch auf Schreibbare Ereignisse und hält die aktuelle Coroutine an, bis das Schreibbare Ereignis eintritt, um den `writev` Systemanruf erneut auszuführen und Daten zu senden, bis die Daten gesendet sind oder ein Fehler auftritt, und weckt dann die entsprechende Coroutine.

!> Swoole Version >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVectorAll(array $io_vector, float $timeout = 0): int|false
```

  * **Parameter** 

    * **`array $io_vector`**
      * **Funktion**：Erwartete segmentierte Daten zum Senden
      * **Einheit des Wertes**：Byte
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`float $timeout`**
      * **Funktion**：Einstellung der Übertragungszeit
      * **Einheit des Wertes**：Sekunden【Unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：Siehe [Client-Übertragungsregeln](/coroutine_client/init?id=Übertragungsregeln)
      * **Andere Werte**：Keine

  * **Rückgabewert**

    * `writeVectorAll` stellt sicher, dass alle Daten erfolgreich gesendet werden, aber während des `writeVectorAll` Prozesses kann der Empfänger die Verbindung trennen, in diesem Fall wurden möglicherweise nur einige Daten erfolgreich gesendet, der Rückgabewert wird die Länge dieser erfolgreich gesendeten Daten zurückgeben, die Anwendungslayer-Code muss vergleichen, ob der Rückgabewert der Gesamtlänge des `$io_vector` Parameters entspricht, um zu überprüfen, ob die Übertragung abgeschlossen ist, und ob eine Fortsetzung der Übertragung nach dem Geschäftszweck erforderlich ist.
    * Bei Übertragungsschaden wird `false` zurückgegeben und die `errCode` Eigenschaft wird festgelegt

  * **Beispiel** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// In diesem Fall werden die Daten in der Reihenfolge des Arrays an den Empfänger gesendet, was tatsächlich "helloworld" sendet
$socket->writeVectorAll(['hello', 'world']);
```


### recvPacket()

Für Socket-Objekte, die bereits über die `setProtocol` Methode ein Protokoll festgelegt haben, kann diese Methode verwendet werden, um einen vollständigen Protokolldatenpaket zu empfangen.

!> Swoole Version >= v4.4.0

```php
Swoole\Coroutine\Socket->recvPacket(float $timeout = 0): string|false
```

  * **Parameter** 
    * **`float $timeout`**
      * **Funktion**：Einstellung der Übertragungszeit
      * **Einheit des Wertes**：Sekunden【Unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：Siehe [Client-Übertragungsregeln](/coroutine_client/init?id=Übertragungsregeln)
      * **Andere Werte**：Keine

  * **Rückgabewert** 

    * Bei erfolgreicher Empfang wird ein vollständiges Protokolldatenpaket zurückgegeben
    * Bei Empfangsfailur wird `false` zurückgegeben und die `errCode` Eigenschaft wird festgelegt
    * Bei Übertragungszeitüberschreitung wird der Fehlercode `ETIMEDOUT` zurückgegeben


### recvLine()

Um [socket_read](https://www.php.net/manual/en/function.socket-read.php)-Kompatibilitätsproblemen zu begegnen

```php
Swoole\Coroutine\Socket->recvLine(int $length = 65535, float $timeout = 0): string|false
```


### recvWithBuffer()

Um das Problem zu lösen, dass beim Einsatz von `recv(1)` für den逐字节Empfang eine große Anzahl von Systemanrufen erzeugt wird

```php
Swoole\Coroutine\Socket->recvWithBuffer(int $length = 65535, float $timeout = 0): string|false
```


### recvfrom()

Empfangen von Daten und Einstellung der Adresse und des Ports desRemotehosts. Für `SOCK_DGRAM`-Typen von `socket`.

!> Diese Methode löst das [Coroutine-调度](/coroutine?id=协程调度) aus, der untere Level wird sofort die aktuelle Coroutine anhalten und auf Lesbare Ereignisse warten. Bei Erreichen eines Lesbaren Ereignisses werden Daten mit dem `recvfrom` Systemanruf empfangen.

```php
Swoole\Coroutine\Socket->recvfrom(array &$peer, float $timeout = 0): string|false
```

* **Parameter**

    * **`array $peer`**
        * **Funktion**：Adresse und Port des Remotehosts, Referenztyp.【Wenn die Funktion erfolgreich zurückkehrt, wird es als Array festgelegt, einschließlich `address` und `port` zwei Elemente】
        * **Standardwert**：Keine
        * **Andere Werte**：Keine

    * **`float $timeout`**
        * **Funktion**：Einstellung der Übertragungszeit【Wenn innerhalb der festgelegten Zeit keine Daten zurückgekehrt sind, wird die `recvfrom` Methode `false` zurückgeben】
        * **Einheit des Wertes**：Sekunden【Unterstützt floating-point Werte, wie `1.5` bedeutet `1s`+`500ms`】
        * **Standardwert**：Siehe [Client-Übertragungsregeln](/coroutine_client/init?id=Übertragungsregeln)
        * **Andere Werte**：Keine

* **Rückgabewert**

    * Bei erfolgreicher Empfang wird der Empfangsinhalt zurückgegeben und `$peer` wird als Array festgelegt
    * Bei Fehlgeschlagenen Empfang wird `false` zurückgegeben und die `errCode` Eigenschaft wird festgelegt, `$peer` wird nicht geändert

* **Beispiel**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    while (true) {
        $peer = null;
        $data = $socket->recvfrom($peer);
        echo "[Server] recvfrom[{$peer['address']}:{$peer['port']}] : $data\n";
        $socket->sendto($peer['address'], $peer['port'], "Swoole: $data");
    }
});
```


### sendto()

Daten an eine spezifische Adresse und Port senden. Für `SOCK_DGRAM`-Typen von `socket`.

!> Diese Methode hat kein [Coroutine-调度](/coroutine?id=协程调度), der untere Level wird sofort den `sendto` aufrufen, um Daten an das Zielhost zu senden. Diese Methode überwacht nicht für Schreibbare Ereignisse, der `sendto` kann aufgrund eines vollen Pufferbereichs `false` zurückgeben, was selbst bearbeitet werden muss oder die `send` Methode verwendet werden muss.

```php
Swoole\Coroutine\Socket->sendto(string $address, int $port, string $data): int|false
```

  * **Parameter** 

    * **`string $address`**
      * **Funktion**：IP-Adresse des Zielhosts oder [unixSocket](/learn?id=什么是IPC) - Pfad【`sendto` unterstützt keine Domainnamen, wenn `AF_INET` oder `AF_INET6` verwendet werden, muss eine gültige IP-Adresse angegeben werden, sonst wird der Senden fehlschlagen】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`int $port`**
      * **Funktion**：Port des Zielhosts【Beim Senden von Broadcast kann der Port `0` sein】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $data`**
      * **Funktion**：Gesendete Daten【Kann Text oder Binärinhalt sein, beachten Sie, dass die maximale Länge eines `SOCK_DGRAM`-Sendepackets 64K beträgt】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

  * **Rückgabewert** 

    * Bei erfolgreicher Übertragung wird die Anzahl der übertragenen Bytes zurückgegeben
    * Bei Übertragungsschaden wird `false` zurückgegeben und die `errCode` Eigenschaft wird festgelegt

  * **Beispiel** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
$socket->sendto('127.0.0.1', 9601, 'Hello');
```
### getsockname()

Erhalten Sie Informationen über die Adresse und den Port des Sockets.

!> Diese Methode hat keine [Coroutine-Zeitplanung](/coroutine?id=coroutinetimeplanung) Overhead.

```php
Swoole\Coroutine\Socket->getsockname(): array|false
```

  * **Rückgabewert** 

    * Bei erfolgreicher Ausführung wird ein Array mit `address` und `port` zurückgegeben
    * Bei Misserfolg wird `false` zurückgegeben und die `errCode`-Eigenschaft wird festgelegt


### getpeername()

Erhalten Sie Informationen über die Remoteadresse und den Port des Sockets, nur für `SOCK_STREAM`-Typen mit verbundenen Sockets geeignet.

?> Diese Methode hat keine [Coroutine-Zeitplanung](/coroutine?id=coroutinetimeplanung) Overhead.

```php
Swoole\Coroutine\Socket->getpeername(): array|false
```

  * **Rückgabewert** 

    * Bei erfolgreicher Ausführung wird ein Array mit `address` und `port` zurückgegeben
    * Bei Misserfolg wird `false` zurückgegeben und die `errCode`-Eigenschaft wird festgelegt


### close()

Schließen Sie das Socket.

!> Wenn ein `Swoole\Coroutine\Socket`-Objekt beim Zerfallvorgang automatisch die `close`-Methode aufruft, hat diese Methode keine [Coroutine-Zeitplanung](/coroutine?id=coroutinetimeplanung) Overhead.

```php
Swoole\Coroutine\Socket->close(): bool
```

  * **Rückgabewert** 

    * Bei erfolgreicher Schließung wird `true` zurückgegeben
    * Bei Misserfolg wird `false` zurückgegeben
    

### isClosed()

Ist das Socket bereits geschlossen?

```php
Swoole\Coroutine\Socket->isClosed(): bool
```

## Konstanten

Entsprechen den Konstanten, die von der `sockets`-Erweiterung bereitgestellt werden, und stoßen nicht mit der `sockets`-Erweiterung zusammen.

!> Die Werte können unter verschiedenen Systemen variieren, die folgenden Codes sind nur Beispiele und sollten nicht verwendet werden.

```php
define ('AF_UNIX', 1);
define ('AF_INET', 2);

/**
 * Nur verfügbar, wenn mit IPv6-Unterstützung Compiliert.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('AF_INET6', 10);
define ('SOCK_STREAM', 1);
define ('SOCK_DGRAM', 2);
define ('SOCK_RAW', 3);
define ('SOCK_SEQPACKET', 5);
define ('SOCK_RDM', 4);
define ('MSG_OOB', 1);
define ('MSG_WAITALL', 256);
define ('MSG_CTRUNC', 8);
define ('MSG_TRUNC', 32);
define ('MSG_PEEK', 2);
define ('MSG_DONTROUTE', 4);

/**
 * Nicht auf Windows-Plattformen verfügbar.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOR', 128);

/**
 * Nicht auf Windows-Plattformen verfügbar.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOF', 512);
define ('MSG_CONFIRM', 2048);
define ('MSG_ERRQUEUE', 8192);
define ('MSG_NOSIGNAL', 16384);
define ('MSG_DONTWAIT', 64);
define ('MSG_MORE', 32768);
define ('MSG_WAITFORONE', 65536);
define ('MSG_CMSG_CLOEXEC', 1073741824);
define ('SO_DEBUG', 1);
define ('SO_REUSEADDR', 2);

/**
 * Diese Konstante ist nur auf Plattformen verfügbar, die die `<b>SO_REUSEPORT</b>`-Socket-Option unterstützen: dies
 * umfasst Mac OS X und FreeBSD, aber nicht Linux oder Windows.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SO_REUSEPORT', 15);
define ('SO_KEEPALIVE', 9);
define ('SO_DONTROUTE', 5);
define ('SO_LINGER', 13);
define ('SO_BROADCAST', 6);
define ('SO_OOBINLINE', 10);
define ('SO_SNDBUF', 7);
define ('SO_RCVBUF', 8);
define ('SO_SNDLOWAT', 19);
define ('SO_RCVLOWAT', 18);
define ('SO_SNDTIMEO', 21);
define ('SO_RCVTIMEO', 20);
define ('SO_TYPE', 3);
define ('SO_ERROR', 4);
define ('SO_BINDTODEVICE', 25);
define ('SOL_SOCKET', 1);
define ('SOMAXCONN', 128);

/**
 * Verwendet, um das Nagle-TCP-Algorithmus zu deaktivieren.
 * Hinzugefügt in PHP 5.2.7.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('TCP_NODELAY', 1);
define ('PHP_NORMAL_READ', 1);
define ('PHP_BINARY_READ', 2);
define ('MCAST_JOIN_GROUP', 42);
define ('MCAST_LEAVE_GROUP', 45);
define ('MCAST_BLOCK_SOURCE', 43);
define ('MCAST_UNBLOCK_SOURCE', 44);
define ('MCAST_JOIN_SOURCE_GROUP', 46);
define ('MCAST_LEAVE_SOURCE_GROUP', 47);
define ('IP_MULTICAST_IF', 32);
define ('IP_MULTICAST_TTL', 33);
define ('IP_MULTICAST_LOOP', 34);
define ('IPV6_MULTICAST_IF', 17);
define ('IPV6_MULTICAST_HOPS', 18);
define ('IPV6_MULTICAST_LOOP', 19);
define ('IPV6_V6ONLY', 27);

/**
 * Operation not permitted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPERM', 1);

/**
 * No such file or directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOENT', 2);

/**
 * Interrupted system call.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINTR', 4);

/**
 * I/O error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIO', 5);

/**
 * No such device or address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENXIO', 6);

/**
 * Arg list too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_E2BIG', 7);

/**
 * Bad file number.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADF', 9);

/**
 * Try again.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAGAIN', 11);

/**
 * Out of memory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEM', 12);

/**
 * Permission denied.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EACCES', 13);

/**
 * Bad address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EFAULT', 14);

/**
 * Block device required.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTBLK', 15);

/**
 * Device or resource busy.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBUSY', 16);

/**
 * File exists.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EEXIST', 17);

/**
 * Cross-device link.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXDEV', 18);

/**
 * No such device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODEV', 19);

/**
 * Not a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTDIR', 20);

/**
 * Is a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISDIR', 21);

/**
 * Invalid argument.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINVAL', 22);

/**
 * File table overflow.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENFILE', 23);

/**
 * Too many open files.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMFILE', 24);

/**
 * Not a typewriter.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTTY', 25);

/**
 * No space left on device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSPC', 28);

/**
 * Illegal seek.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESPIPE', 29);

/**
 * Read-only file system.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EROFS', 30);

/**
 * Too many links.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMLINK', 31);

/**
 * Broken pipe.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPIPE', 32);

/**
 * File name too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENAMETOOLONG', 36);

/**
 * No record locks available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLCK', 37);

/**
 * Function not implemented.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSYS', 38);

/**
 * Directory not empty.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTEMPTY', 39);

/**
 * Too many symbolic links encountered.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELOOP', 40);

/**
 * Operation would block.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EWOULDBLOCK', 11);

/**
 * No message of desired type.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMSG', 42);

/**
 * Identifier removed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIDRM', 43);

/**
 * Channel number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECHRNG', 44);

/**
 * Level 2 not synchronized.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2NSYNC', 45);

/**
 * Level 3 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3HLT', 46);

/**
 * Level 3 reset.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3RST', 47);

/**
 * Link number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELNRNG', 48);

/**
 * Protocol driver not attached.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUNATCH', 49);

/**
 * No CSI structure available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOCSI', 50);

/**
 * Level 2 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2HLT', 51);

/**
 * Invalid exchange.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADE', 52);

/**
 * Invalid request descriptor.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADR', 53);

/**
 * Exchange full.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXFULL', 54);

/**
 * No anode.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOANO', 55);

/**
 * Invalid request code.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADRQC', 56);

/**
 * Invalid slot.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADSLT', 57);

/**
 * Device not a stream.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSTR', 60);

/**
 * No data available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODATA', 61);

/**
 * Timer expired.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIME', 62);

/**
 * Out of streams resources.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSR', 63);

/**
 * Machine is not on the network.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENONET', 64);

/**
 * Object is remote.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMOTE', 66);

/**
 * Link has been severed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLINK', 67);

/**
 * Advertise error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EADV', 68);

/**
 * Srmount error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESRMNT', 69);

/**
 * Communication error on send.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECOMM', 70);

/**
 * Protocol error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTO', 71);

/**
 * Multihop attempted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMULTIHOP', 72);

