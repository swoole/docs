# TCP-Server

?> `Swoole\Coroutine\Server` ist eine vollständig [Koordination](/koordination) class, die zum Erstellen von Koordinations-`TCP`-Server verwendet wird, die TCP und [unixSocket](/lernen?id=was_ist_ipc) Typen unterstützt.

Im Gegensatz zum [Server](/server/tcp_init) Modul:

* Dynamische Erstellung und Zerstörung, kann im Betrieb dynamisch Ports überwachen und den Server auch dynamisch schließen
* Der Prozess der Verbindungshandhabung ist vollständig synchron, der Programm kann die Ereignisse `Connect`, `Receive`, `Close` sequenziell verarbeiten

!> Available in Version 4.4 und höher


## Kurzname

Möglich ist der Kurzname `Co\Server`.


## Methoden


### __construct()

?> **Konstruktor.** 

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Parameter** 

    * **`string $host`**
      * **Funktion**: Überwachungsadresse
      * **Standardwert**: Keine
      * **Andere Werte**: Keine

    * **`int $port`**
      * **Funktion**: Überwachungsport【Wenn für 0, wird vom Betriebssystem ein zufälliger Port zugewiesen】
      * **Standardwert**: Keine
      * **Andere Werte**: Keine

    * **`bool $ssl`**
      * **Funktion**: Ob SSL-Verschlüsselung eingeschaltet ist
      * **Standardwert**: `false`
      * **Andere Werte**: `true`

    * **`bool $reuse_port`**
      * **Funktion**: Ob Port-Wiederverwendung eingeschaltet ist, der Effekt ist gleich wie in der [dieser Abschnitt](/server/setting?id=enable_reuse_port) konfiguriert
      * **Standardwert**: `false`
      * **Andere Werte**: `true`
      * **Versionseinfluss**: Swoole-Version >= v4.4.4

  * **Hinweise**

    * **$host Parameter unterstützt 3 Formate**

      * `0.0.0.0/127.0.0.1`: IPv4-Adresse
      * `::/::1`: IPv6-Adresse
      * `unix:/tmp/test.sock`: [UnixSocket](/lernen?id=was_ist_ipc) Adresse

    * **Ausnahmen**

      * Wenn Parameterfehler, Bindungsadresse und Portversuch scheitern oder `listen` scheitern, wird eine `Swoole\Exception` Ausnahme geworfen.


### set()

?> **Festlegen der Protokollbehandlungsparameter.** 

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **Konfigurationsparameter**

    * Der Parameter `$options` muss ein eindimensionales assoziiertes Index-Array sein, das genau den Konfigurationselementen entspricht, die von der [setprotocol](/coroutine_client/socket?id=setprotocol) Methode akzeptiert werden.

    !> Muss vor der [start()](/coroutine/server?id=start) Methode festgelegt werden

    * **Längeprotokoll**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      * package_length_offset' => 0,
      'package_body_offset' => 4,
    ]);
    ```

    * **SSL-Zertifikatsstellung**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      'ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```


### handle()

?> **Festlegen der Verbindungshandhabungsfunktion.** 

!> Muss vor der [start()](/coroutine/server?id=start) Methode festgelegt werden

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **Parameter** 

    * **`callable $fn`**
      * **Funktion**: Festlegen der Verbindungshandhabungsfunktion
      * **Standardwert**: Keine
      * **Andere Werte**: Keine
      
  * **Beispiel** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            $data = $conn->recv();
        }
    });
    ```

    !> - Wenn der Server nach dem `Accept` (Verbindung aufbauen) erfolgreich ist, wird automatisch ein [Koordination](/koordination?id=koordination_scheduler) erstellt und die `$fn` ausgeführt;  
    - `$fn` wird in einem neuen Unterkoordinationsspace ausgeführt, daher ist es im Funktionsinneren nicht notwendig, eine Koordination zu erstellen;  
    - `$fn` akzeptiert ein Argument, dessen Typ der [Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection) Objekt ist;  
    - Mit [exportSocket()](/coroutine/server?id=exportsocket) kann man das Socket-Objekt der aktuellen Verbindung erhalten


### shutdown()

?> **Beenden des Servers.** 

?> Der Boden unterstützt mehrere Aufrufe von `start` und `shutdown`

```php
Swoole\Coroutine\Server->shutdown(): bool
```


### start()

?> **Starten des Servers.** 

```php
Swoole\Coroutine\Server->start(): bool
```

  * **Rückgabewert**

    * Starten scheitert wird `false` zurückgegeben und die `errCode` Eigenschaft wird festgelegt
    * Starten erfolgreich, wird in einen Loop eintreten, `Accept` Verbindung aufbauen
    * Nach dem `Accept` (Verbindung aufbauen) wird ein neuer Koordination erstellt und die im `handle` Methode angegebene Funktion in der Koordination ausgeführt

  * **Fehlerbehandlung**

    * Wenn beim `Accept` (Verbindung aufbauen) ein `Too many open files` Fehler auftritt oder keine Unterkoordinationen erstellt werden können, wird der Prozess für `1` Sekunde angehalten und dann fortgesetzt
    * Bei Fehlern wird die `start()` Methode zurückgegeben, und die Fehlermeldung wird in Form einer `Warning` ausgegeben.


## Objekte


### Coroutine\Server\Connection

Das `Swoole\Coroutine\Server\Connection` Objekt bietet vier Methoden:
 
#### recv()

Empfangen von Daten, wenn ein Protokollbehandlung festgelegt ist, wird jedes Mal ein vollständiger Paket zurückgegeben

```php
function recv(float $timeout = 0)
```

#### send()

Daten senden

```php
function send(string $data)
```

#### close()

Verbindung schließen

```php
function close(): bool
```

#### exportSocket()

Das Socket-Objekt der aktuellen Verbindung erhalten. Mehr grundlegende Methoden können aufgerufen werden, siehe [Swoole\Coroutine\Socket](/coroutine_client/socket)

```php
function exportSocket(): Swoole\Coroutine\Socket
```

## Vollständiges Beispiel

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

//Prozesspool-Verwaltungseinheit
$pool = new Process\Pool(2);
//Lassen Sie jede OnWorkerStart-Rückruffunktion automatisch eine Koordination erstellen
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    //Jeder Prozess überwacht Port 9501
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    //Empfangen von Signal 15 zum Schließen des Diensts
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    //Neue Verbindungsanfrage empfangen und automatisch eine Koordination erstellen
    $server->handle(function (Connection $conn) {
        while (true) {
            //Daten empfangen
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            //Daten senden
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    //Port überwachen beginnen
    $server->start();
});
$pool->start();
```

!> Bitte ändern Sie dies in einem Cygwin-Umwelt zu einem einzelnen Prozess. `$pool = new Swoole\Process\Pool(1);`
