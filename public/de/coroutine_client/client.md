# Coroutine TCP/UDP Client

Das `Coroutine\Client` bietet eine Umhüllung für Socket-Clients im Bereich der Übertragungsprotokolle `TCP`, `UDP` und [unixSocket](/learn?id=Was ist IPC), die es ermöglicht, mit nur einem `new Swoole\Coroutine\Client` zu beginnen.

* **Implementierungstheorie**

    * Alle Methoden des `Coroutine\Client`, die Netzwerkanforderungen beinhalten, werden von `Swoole` mit [Coroutine-Scheduler](/coroutine?id=Coroutine-Scheduler) abgewickelt, sodass die Geschäftslogik davon nichts weiß.
    * Die Verwendung ist vollständig identisch mit den synchronen Methoden des [Client](/client).
    * Die Timeouteinstellung für `connect` gilt gleichzeitig für `Connect`, `Recv` und `Send`.

* **Vererbungshierarchie**

    * Das `Coroutine\Client` steht in keiner Vererbungshierarchie zu [Client](/client), aber alle Methoden des `Client` können im `Coroutine\Client` verwendet werden. Bitte beziehen Sie sich auf [Swoole\Client](/client?id=Methoden), um dies weiter zu erläutern.
    * Im `Coroutine\Client` können Sie mit der `set` Methode [Konfigurationsoptionen](/client?id=Konfigurations) festlegen, die Verwendung ist vollständig identisch mit der des `Client->set`. Für Funktionen, die unterschiedlich verwendet werden, wird im Abschnitt `set()` separat erklärt.

* **Beispiel für die Verwendung**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

* **Protokollbehandlung**

Der Coroutine-Client unterstützt auch die Behandlung von Längen- und `EOF`-Protokollen. Die Einstellungsmethode ist genau wie bei [Swoole\Client](/client?id=Konfiguration).

```php
$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0, //Der N-te Byte ist der Wert der Paketlänge
    'package_body_offset'   => 4, //Von welchem Byte an wird die Länge berechnet
    'package_max_length'    => 2000000, //Maximale Länge des Protokolls
));
```


### connect()

Verbindet sich mit dem Remoteserver.

```php
Swoole\Coroutine\Client->connect(string $host, int $port, float $timeout = 0.5): bool
```

  * **Parameter** 

    * **`string $host`**
      * **Funktion**：Adresse des Remoteservers【Im Hintergrund wird automatisch eine Coroutine-Wechseln zur Domain-Name-Analyse durchgeführt】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $port`**
      * **Funktion**：Port des Remoteservers
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`float $timeout`**
      * **Funktion**：Zeitlimit für Netzwerk-IO; einschließlich `connect/send/recv`, wird die Verbindung automatisch `close` sein, wenn das Timeout eintritt, siehe [Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)
      * **Einheit der Werte**：Sekunden【Unterstützt floating-point-Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：`0.5s`
      * **Andere Werte**：Keine

* **Hinweise**

    * Wenn die Verbindung fehlschlägt, wird `false` zurückgegeben.
    * Nach dem Timeout zurückgegeben, überprüfen Sie `$cli->errCode` für `110`.

* **Fehlanpassungsversuche**

!> Nach einem `connect`-Fehler kann keine direkte Wiederverbindung versucht werden. Es muss zuerst mit `close` der bestehende `socket` geschlossen werden, bevor eine erneute `connect`-Versuch unternommen werden kann.

```php
//Verbindung fehlgeschlagen
if ($cli->connect('127.0.0.1', 9501) == false) {
    //Bestehenden socket schließen
    $cli->close();
    //Wiederversuch
    $cli->connect('127.0.0.1', 9501);
}
```

* **Beispiel**

```php
if ($cli->connect('127.0.0.1', 9501)) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}

if ($cli->connect('/tmp/rpc.sock')) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}
```


### isConnected()

Gibt den Verbindungsstatus des Clients zurück

```php
Swoole\Coroutine\Client->isConnected(): bool
```

  * **Rückgabewert**

    * Gibt `false` zurück, wenn derzeit keine Verbindung zum Server besteht
    * Gibt `true` zurück, wenn derzeit eine Verbindung zum Server besteht
    
!> Die Methode `isConnected` gibt den Zustand der Anwendungsebene zurück, sie zeigt nur, dass der `Client` eine Verbindung zu einem `Server` aufgebaut hat und erfolgreich verbunden ist, ohne die Verbindung zu schließen. Der `Client` kann Operationen wie `send`, `recv`, `close` usw. ausführen, aber keine weitere Verbindung aufbauen.  
Dies bedeutet nicht unbedingt, dass die Verbindung nutzbar ist, da es möglich ist, dass Fehler beim Ausführen von `send` oder `recv` zurückgegeben werden, da die Anwendungsebene den Zustand der unteren `TCP`-Verbindung nicht ermitteln kann. Um den wahren Zustand der Verbindung zur Verfügung zu haben, muss die Anwendungsebene beim Ausführen von `send` oder `recv` eine Interaktion mit dem Kern erfolgen.


### send()

Sendet Daten.

```php
Swoole\Coroutine\Client->send(string $data): int|bool
```

  * **Parameter** 

    * **`string $data`**
    
      * **Funktion**：Die zu sendenden Daten, müssen ein String-Typ sein, Binärdaten werden unterstützt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * Wenn die Sendung erfolgreich ist, wird die Anzahl der字节 zurückgegeben, die in den `Socket`-Puffer geschrieben wurden. Der Boden versucht, alle Daten so weit wie möglich zu senden. Wenn die Anzahl der zurückgegebenen字节 nicht mit der Länge der eingehenden `$data` übereinstimmt, könnte dies daran liegen, dass das Peer die Verbindung geschlossen hat. Bei der nächsten Ausführung von `send` oder `recv` wird der entsprechende Fehlercode zurückgegeben.

  * Wenn die Sendung fehlschlägt, wird `false` zurückgegeben, und man kann den Fehlergrund mit `$client->errCode` ermitteln.


### recv()

Die `recv`-Methode wird zum Empfang von Daten vom Server verwendet.

```php
Swoole\Coroutine\Client->recv(float $timeout = 0): string|bool
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion**：Gibt die Timeoutzeit an
      * **Einheit der Werte**：Sekunden【Unterstützt floating-point-Werte, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：Siehe [Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)
      * **Andere Werte**：Keine

    !> Bei Timeout wird zuerst der angegebene Parameter verwendet, gefolgt von dem in der `set` Methode eingestellten `timeout`. Ein Timeout-Fehlercode ist `ETIMEDOUT`.

  * **Rückgabewert**

    * Wenn [Kommunikationsprotokoll](/client?id=Protokoll-Analyse) festgelegt ist, wird `recv` vollständige Daten zurückgeben, die Länge ist durch [package_max_length](/server/setting?id=package_max_length) begrenzt
    * Wenn kein Kommunikationsprotokoll festgelegt ist, kann `recv` höchstens `64K` Daten zurückgeben
    * Wenn kein Kommunikationsprotokoll festgelegt ist, werden die Rohdaten zurückgegeben, und es muss in der `PHP`-Code eine eigene Behandlung des Netzwerkprotokolls implementiert werden
    * Ein leeres String wird zurückgegeben, wenn das Service-Side die Verbindung aktiv geschlossen hat, und es muss mit `close` geschlossen werden
    * Wenn `recv` fehlschlägt, wird `false` zurückgegeben, und man kann den Fehlergrund mit `$client->errCode` ermitteln. Bei der Handhabung siehe [Vollständiges Beispiel](/coroutine_client/client?id=Vollständiges Beispiel) unten.


### close()

Schließt die Verbindung.

!> Bei `close` gibt es keine Blockierung, es wird sofort zurückgegeben. Die Schließoperation verursacht keinen Coroutine-Wechsel.

```php
Swoole\Coroutine\Client->close(): bool
```


### peek()

Blickt auf Daten.

!> Die `peek`-Methode manipuliert direkt den `socket`, daher wird dies keinen [Coroutine-Scheduler](/coroutine?id=Coroutine-Scheduler) auslösen.

```php
Swoole\Coroutine\Client->peek(int $length = 65535): string
```

  * **Hinweise**

    * Die `peek`-Methode wird nur zum Blick auf die Daten im Kernel-`socket`-Puffer verwendet und verursacht keinen Offset. Nachdem `peek` verwendet wurde, kann `recv` immer noch diese Daten lesen.
    * Die `peek`-Methode ist nicht blockierend, sie wird sofort zurückgegeben. Wenn im `socket`-Puffer Daten vorhanden sind, werden diese zurückgegeben. Wenn der Puffer leer ist, wird `false` zurückgegeben und `$client->errCode` wird festgelegt.
    * Wenn die Verbindung bereits geschlossen wurde, wird bei `peek` ein leeres String zurückgegeben.
### set()

Legt Client-Parameter fest.

```php
Swoole\Coroutine\Client->set(array $settings): bool
```

  * **Konfigurationsparameter**

    * Bitte beziehen Sie sich auf [Swoole\Client](/client?id=set).

* **Unterschied zu [Swoole\Client](/client?id=set)**
    
    Die Coroutine-Client bietet eine feinere Kontrolle über die Timeouts. Es können festgelegt werden:
    
    * `timeout`: Gesamt-Timeout, einschließlich Verbindung, Senden, Empfangen aller Timeouts
    * `connect_timeout`: Verbindungstimeout
    * `read_timeout`: Empfangs-Timeout
    * `write_timeout`: Senden-Timeout
    * Bitte beziehen Sie sich auf [Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)

* **Beispiel**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'timeout' => 0.5,
        'connect_timeout' => 1.0,
        'write_timeout' => 10.0,
        'read_timeout' => 0.5,
    ));

    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

### Vollständiges Beispiel

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // Gleichheit mit Leere direkt Verbindung schließen
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // Kann je nach Geschäftslogik und Fehlercode selbst bearbeitet werden, zum Beispiel:
                    // Wenn Timeout dann keine Verbindung schließen, bei anderen Fällen direkt Verbindung schließen
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```
