# Swoole\Process\Pool

Prozesspool, der auf der Manager-Implementierung des [Swoole\Server](/server/init) basiert und die Verwaltung von mehreren Arbeitsprozessen ermöglicht. Der Kernfunktionsbereich dieses Moduls ist die Prozessverwaltung. Im Vergleich zur `Process`-Implementierung, die mehrere Prozesse verwaltet, ist die `Process\Pool`-Implementierung einfacher, hat eine höhere Abstraktionsschicht und erfordert von Entwicklern weniger Code, um Prozessverwaltungfunktionen zu implementieren. In Kombination mit dem [Co\Server](/coroutine/server?id=vollständiges Beispiel) kann man einen reinen Coroutine-Stil-Serverprogramm erstellen, das mehrere Kerne der CPU nutzen kann.

## Prozess间通信

Die `Swoole\Process\Pool` bietet insgesamt drei Methoden für die Kommunikation zwischen Prozessen:

### Message Queue
Wenn der zweite Parameter von `Swoole\Process\Pool->__construct` auf `SWOOLE_IPC_MSGQUEUE` festgelegt ist, wird eine Message Queue zur Prozess间-Kommunikation verwendet. Informationen können über die Erweiterung `php sysvmsg` übermittelt werden, wobei die Nachricht nicht länger als `65536`字节 sein darf.

* **Hinweis**

  * Um die Erweiterung `sysvmsg` zu verwenden, muss im Konstruktor ein `msgqueue_key` übergeben werden.
  * Das untere Swoole-Layer unterstützt den zweiten Parameter `mtype` der `msg_send`-Funktion von `sysvmsg` nicht. Bitte geben Sie einen beliebigen nicht-null-Wert ein.

### Socket Communication
Wenn der zweite Parameter von `Swoole\Process\Pool->__construct` auf `SWOOLE_IPC_SOCKET` festgelegt ist, wird eine Socket-Kommunikation verwendet. Wenn Ihr Kunde und Ihr Server sich nicht auf derselben Maschine befinden, können Sie diese Methode zur Kommunikation verwenden.

Mithilfe der Methode `[Swoole\Process\Pool->listen()](/process/process_pool?id=listen)` wird auf einem Port lauscht, und mit dem `[Message-Ereignis](/process/process_pool?id=on)` werden Daten von Kunden empfangen. Die Methode `[Swoole\Process\Pool->write()](/process/process_pool?id=write)` wird verwendet, um Antworten an den Kunden zu senden.

Swoole fordert, dass Kunden, die diese Methode zur Übertragung von Daten verwenden, vor den tatsächlichen Daten eine 4-Byte-Längeswerte in Netzwerk-Byte-Reihenfolge hinzufügen müssen.
```php
$msg = 'Hallo Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```

### UnixSocket
Wenn der zweite Parameter von `Swoole\Process\Pool->__construct` auf `SWOOLE_IPC_UNIXSOCK` festgelegt ist, wird eine [UnixSocket](/learn?id=Was ist IPC) zur Prozess间-Kommunikation verwendet. **Wir empfehlen diese Methode für die Prozess间-Kommunikation sehr** .

Diese Methode ist ziemlich einfach und kann mit der Methode `[Swoole\Process\Pool->sendMessage()](/process/process_pool?id=sendMessage)` und dem `[Message-Ereignis](/process/process_pool?id=on)` zur Prozess间-Kommunikation abgeschlossen werden.

Oder nachdem der `Coroutine-Modus` aktiviert wurde, kann man auch die Methode `[Swoole\Process\Pool->getProcess()](/process/process_pool?id=getProcess)` verwenden, um ein `Swoole\Process`-Objekt zu erhalten, und die Methode `[Swoole\Process->exportsocket()](/process/process?id=exportsocket)` um ein `Swoole\Coroutine\Socket`-Objekt zu erhalten. Mit diesem Objekt kann man die Prozess间-Kommunikation durchführen. Allerdings kann bei dieser Einstellung das `[Message-Ereignis](/process/process_pool?id=on)` nicht festgelegt werden.

!> Für Parameter und Umgebungseinstellungen können Sie sich die [Konstruktormethode](/process/process_pool?id=__construct) und die [Konfigurationsparameter](/process/process_pool?id=set) ansehen.

## Konstanten


Konstante | Beschreibung
---|---
SWOOLE_IPC_MSGQUEUE | System[Nachrichtenqueue](/learn?id=Was ist IPC)-Kommunikation
SWOOLE_IPC_SOCKET | SOCKET-Kommunikation
SWOOLE_IPC_UNIXSOCK | [UnixSocket](/learn?id=Was ist IPC)-Kommunikation (v4.4+)


## Coroutine-Unterstützung

Seit der Version `v4.4.0` gibt es Unterstützung für Coroutinen. Bitte beziehen Sie sich auf [Swoole\Process\Pool::__construct](/process/process_pool?id=__construct).


## Beispiel

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** Derzeit ist es ein Worker-Prozess */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n";
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
```


## Methoden


### __construct()

Konstruktormethode.

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **Parameter** 

  * **`int $worker_num`**
    * **Funktion** : Bestimmt die Anzahl der Arbeitsprozesse
    * **Standardwert** : kein
    * **Andere Werte** : kein

  * **`int $ipc_type`**
    * **Funktion** : Modus für die Prozess间-Kommunikation【Standardwert ist `SWOOLE_IPC_NONE`, was bedeutet, dass keine Prozess间-Kommunikationsmerkmale verwendet werden】
    * **Standardwert** : `SWOOLE_IPC_NONE`
    * **Andere Werte** : `SWOOLE_IPC_MSGQUEUE`, `SWOOLE_IPC_SOCKET`, `SWOOLE_IPC_UNIXSOCK`

    !> - Wenn auf `SWOOLE_IPC_NONE` festgelegt ist, muss die `onWorkerStart`-Callback festgelegt werden und im `onWorkerStart` muss eine Loop-Logik implementiert werden. Wenn die `onWorkerStart`-Funktion beendet wird, wird der Arbeitsprozess sofort beendet und dann vom Manager-Prozess neu gestartet;  
    - Wenn auf `SWOOLE_IPC_MSGQUEUE` festgelegt ist, wird eine System-Nachrichtenqueue zur Kommunikation verwendet, und der `$msgqueue_key` kann festgelegt werden, um den `KEY` der Nachrichtenqueue zu bestimmen. Wenn keine Nachrichtenqueue `KEY` festgelegt ist, wird eine private Queue beantragt;  
    - Wenn auf `SWOOLE_IPC_SOCKET` festgelegt ist, wird ein `Socket` zur Kommunikation verwendet, und es ist notwendig, mit der Methode `[listen](/process/process_pool?id=listen)` die Adresse und den Port festzulegen, an dem zugehört wird;  
    - Wenn auf `SWOOLE_IPC_UNIXSOCK` festgelegt ist, wird eine [unixSocket](/learn?id=Was ist IPC) zur Kommunikation verwendet, die in einem Coroutine-Modus verwendet wird, **wir empfehlen diese Methode sehr für die Prozess间-Kommunikation**, und die spezifische Verwendung finden Sie im Folgenden;  
    - Wenn ein Wert außer `SWOOLE_IPC_NONE` festgelegt ist, muss das `onMessage`-Callback festgelegt werden, und das `onWorkerStart` wird zu einem optionalen Callback.

  * **`int $msgqueue_key`**
    * **Funktion** : Der `key` der Nachrichtenqueue
    * **Standardwert** : `0`
    * **Andere Werte** : kein

  * **`bool $enable_coroutine`**
    * **Funktion** : Ob Coroutine-Unterstützung aktiviert ist【Wenn Coroutinen aktiviert sind, kann das `onMessage`-Callback nicht mehr festgelegt werden】
    * **Standardwert** : `false`
    * **Andere Werte** : `true`

* **Coroutine-Modus**
    
Seit der Version `v4.4.0` unterstützt das `Process\Pool`-Modul Coroutinen und kann durch Einstellen des vierten Parameters auf `true` aktiviert werden. Nachdem Coroutinen aktiviert wurden, wird unterhalb des `onWorkerStart`-Callbacks automatisch ein Coroutine und ein [Coroutine-Scheduler](/coroutine/scheduler) erstellt. In den Callback-Funktionen können direkt Coroutine-bezogene `APIs` verwendet werden, zum Beispiel:

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

Nachdem Coroutinen aktiviert wurden, verbietet Swoole das Einstellen des `onMessage`-Ereignis-Callbacks. Für die Prozess间-Kommunikation muss der zweite Parameter auf `SWOOLE_IPC_UNIXSOCK` festgelegt werden, um eine [unixSocket](/learn?id=Was ist IPC) zur Kommunikation zu verwenden, und dann wird das [Swoole\Coroutine\Socket](/coroutine_client/socket)-Objekt mit der Methode `$pool->getProcess()->exportSocket()` exportiert, um die Prozess间-Kommunikation zwischen `Worker`-Prozessen zu ermöglichen. Zum Beispiel:

 ```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> Für spezifische Verwendungsmöglichkeiten können Sie sich die Abschnitte [Swoole\Coroutine\Socket](/coroutine_client/socket) und [Swoole\Process](/process/process?id=exportsocket) ansehen.

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```
### set()

Legt Parameter fest.

```php
Swoole\Process\Pool->set(array $settings): void
```


Optionale Parameter | Typ | Funktion | Standardwert
---|---|---|---
enable_coroutine | bool | Controlliert, ob Coroutinen aktiviert sind | false
enable_message_bus | bool | Aktiviert den Message Bus, wenn dieser Wert `true` ist, wird bei Senden großer Daten in kleinere Blöcke unterteilt und diese Stück für Stück an den Empfänger gesendet | false
max_package_size | int | Limitiert die maximale Menge an Daten, die ein Prozess empfangen kann | 2 * 1024 * 1024

* **Hinweis**

  * Wenn `enable_message_bus` auf `true` gesetzt ist, hat `max_package_size` keine Wirkung, da die Daten unterteilt und gesendet werden, und das gilt auch für das Empfangen von Daten.
  * Im Modus `SWOOLE_IPC_MSGQUEUE` hat `max_package_size` keine Wirkung, da der Boden层 unter 65536 Daten pro Sitzung empfangen kann.
  * Im Modus `SWOOLE_IPC_SOCKET`, wenn `enable_message_bus` auf `false` gesetzt ist und die empfangenen Datenmenge größer als `max_package_size` ist, wird die Verbindung vom Boden layer direkt abgebrochen.
  * Im Modus `SWOOLE_IPC_UNIXSOCK`, wenn `enable_message_bus` auf `false` gesetzt ist und die Daten größer als `max_package_size` sind, werden die über `max_package_size` hinausgehenden Daten abgeschnitten.
  * Wenn der Coroutine-Modus aktiviert ist und `enable_message_bus` auf `true` gesetzt ist, hat `max_package_size` keine Wirkung. Der Boden layer wird sicherstellen, dass Daten zerteilt (gesendet) und zusammengeführt (empfangen) werden, sonst wird die Empfangsdatenmenge nach `max_package_size` eingeschränkt

!> Swoole Version >= v4.4.4 verfügbar


### on()

Legt eine Rückruffunktion für den Prozesspool fest.

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **Parameter** 

  * **`string $event`**
    * **Funktion**: Spezifiziert das Ereignis
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`callable $function`**
    * **Funktion**: Rückruffunktion
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

* **Ereignisse**

  * **onWorkerStart** Unterprozess gestartet

    ```php
    /**
    * @param \Swoole\Process\Pool $pool Pool-Objekt
    * @param int $workerId   ID des aktuellen Arbeitsprozesses, der Boden layer wird Unterprozesse numéroieren
    */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
        echo "Arbeitsprozess#{$workerId} ist gestartet\n";
    });
    ```

  * **onWorkerStop** Unterprozess beendet

    ```php
    /**
    * @param \Swoole\Process\Pool $pool Pool-Objekt
    * @param int $workerId   ID des aktuellen Arbeitsprozesses, der Boden layer wird Unterprozesse numéroieren
    */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
        echo "Arbeitsprozess#{$workerId} beendet\n";
    });
    ```

  * **onMessage** Nachricht empfangen

    !> Empfängt eine von außen übergebene Nachricht. Eine Verbindung kann nur einmal Nachrichten übergeben, ähnlich dem kurzen Verbindungsmechanismus von `PHP-FPM`

    ```php
    /**
      * @param \Swoole\Process\Pool $pool Pool-Objekt
      * @param string $data Inhaltsdaten der Nachricht
     */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
        var_dump($data);
    });
    ```

    !> Der Ereignisname ist nicht case-sensitive, `WorkerStart`, `workerStart` oder `workerstart` sind gleichbedeutend


### listen()

Hört auf `SOCKET`, kann nur verwendet werden, wenn `$ipc_mode = SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **Parameter** 

  * **`string $host`**
    * **Funktion**: IP-Adresse überwachen【unterstützt TCP und [unixSocket](/learn?id=Was ist IPC) beiden Typen. `127.0.0.1` bedeutet TCP-Adresse überwachen, `$port` muss angegeben werden. `unix:/tmp/php.sock` überwacht [unixSocket](/learn?id=Was ist IPC)-Adresse】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $port`**
    * **Funktion**: Überwachungsport【muss im TCP-Modus angegeben werden】
    * **Standardwert**: `0`
    * **Andere Werte**: Keiner

  * **`int $backlog`**
    * **Funktion**: Länge der Warteschlange für Überwachung
    * **Standardwert**: `2048`
    * **Andere Werte**: Keiner

* **Rückgabewert**

  * Erfolgreiche Überwachung gibt `true` zurück
  * Überwachung scheitert gibt `false` zurück, kann mit `swoole_errno` den Fehlercode abrufen. Nach einem Überwachungsfehler gibt es sofort einen Rückgang beim Aufrufen von `start`

* **Kommunikationsprotokoll**

    Wenn Daten an die Überwachungsport gesendet werden, muss der Client vor dem Request eine 4 Byte große, network byte order Längeswerte hinzufügen. Das Protokollformat lautet:

```php
// $msg Die zu sendenden Daten
$packet = pack('N', strlen($msg)) . $msg;
```

* **Benutzungsbeispiel**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```


### write()

Schreibt Daten an den Peer, kann nur verwendet werden, wenn `$ipc_mode` auf `SWOOLE_IPC_SOCKET` gesetzt ist.

```php
Swoole\Process\Pool->write(string $data): bool
```

!> Diese Methode ist eine Memory-Operation, ohne `IO`-Verbrauch, das Senden von Daten ist ein synchroner blockierender `IO`

* **Parameter** 

  * **`string $data`**
    * **Funktion**: Zu schreibende Dateninhalt【kann wiederholt aufgerufen werden, der Boden layer wird die Daten nach dem Verlassen der `onMessage`-Funktion vollständig in den `socket` schreiben und die Verbindung schließen】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

* **Benutzungsbeispiel**

  * **Server**

    ```php
    $pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
    
    $pool->on("Message", function ($pool, $message) {
        echo "Message: {$message}\n";
        $pool->write("hello ");
        $pool->write("world ");
        $pool->write("\n");
    });
    
    $pool->listen('127.0.0.1', 8089);
    $pool->start();
    ```

  * **Anrufer**

    ```php
    $fp = stream_socket_client("tcp://127.0.0.1:8089", $errno, $errstr) or die("error: $errstr\n");
    $msg = json_encode(['data' => 'hello', 'uid' => 1991]);
    fwrite($fp, pack('N', strlen($msg)) . $msg);
    sleep(1);
    // wird hello world\n anzeigen
    $data = fread($fp, 8192);
    var_dump(substr($data, 4, unpack('N', substr($data, 0, 4))[1]));
    fclose($fp);
    ```


### sendMessage()

Sendet Daten an ein bestimmtes Arbeitsprozess-ID, kann nur verwendet werden, wenn `$ipc_mode` auf `SWOOLE_IPC_UNIXSOCK` gesetzt ist.

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **Parameter** 

  * **`string $data`**
    * **Funktion**: Zu sendende Daten
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $dst_worker_id`**
    * **Funktion**: Ziel-Arbeitsprozess-ID
    * **Standardwert**: `0`
    * **Andere Werte**: Keiner

* **Rückgabewert**

  * Senden erfolgreich gibt `true` zurück
  * Senden scheitert gibt `false` zurück

* **Hinweis**

  * Wenn die gesendeten Daten größer als `max_package_size` sind und `enable_message_bus` auf `false` gesetzt ist, wird der Zielprozess beim Empfang der Daten die Daten截断.

```php
<?php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => false, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(2048)


$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => true, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(6000)
```
### start()

Starte den Arbeitsprozess.

```php
Swoole\Process\Pool->start(): bool
```

!> Wenn der Start erfolgreich ist, betritt der aktuelle Prozess den `wait` Zustand und verwaltet die Arbeitsprozesse;  
Wenn der Start fehlschlägt, wird `false` zurückgegeben, und man kann den Fehlercode mit `swoole_errno` abrufen.

* **Beispiel für die Verwendung**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} ist gestartet\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} ist gestoppt\n";
});

$pool->start();
```

* **Prozessverwaltung**

  * Wenn ein Arbeitsprozess einen tödlichen Fehler aufweist oder freiwillig verlässt, wird der Verwalter ihn sammeln, um Zombieprozesse zu vermeiden
  * Nachdem ein Arbeitsprozess beendet wurde, wird der Verwalter automatisch einen neuen Arbeitsprozess starten
  * Wenn der Hauptkernel das `SIGTERM`-Signal erhält, wird er den `fork`-Prozess stoppen und alle laufenden Arbeitsprozesse `killen`
  * Wenn der Hauptkernel das `SIGUSR1`-Signal erhält, wird er die laufenden Arbeitsprozesse nacheinander `killen` und neue Arbeitsprozesse starten

* **Signalbehandlung**

  Der Boden hat nur die Signalbehandlung für den Hauptkernel (Verwalterprozess) eingerichtet und nicht für die `Worker`-Arbeitsprozesse. Die Entwickler müssen die Signalüberwachung selbst implementieren.

  - Wenn der Arbeitsprozess asynchron ist, verwenden Sie [Swoole\Process::signal](/process/process?id=signal) um Signale zu überwachen
  - Wenn der Arbeitsprozess synchron ist, verwenden Sie `pcntl_signal` und `pcntl_signal_dispatch` um Signale zu überwachen

  In Arbeitsprozessen sollte das `SIGTERM`-Signal überwacht werden, da der Hauptkernel dieses Signal an den Prozess senden wird, wenn er den Prozess beenden möchte. Wenn der Arbeitsprozess das `SIGTERM`-Signal nicht überwacht, wird der Boden den aktuellen Prozess zwingen, beendet zu werden, was zu einem Verlust einiger Logiken führen kann.

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} ist gestartet\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```


### stop()

Verschiebe das Socket des aktuellen Prozesses aus dem Ereignisloop heraus, dieser Funktion hat nur nach dem Aktivieren von Coroutinen eine Bedeutung.

```php
Swoole\Process\Pool->stop(): bool
```


### shutdown()

Beende die Arbeitsprozesse.

```php
Swoole\Process\Pool->shutdown(): bool
```


### getProcess()

Erhalte das aktuelle Arbeitsprozessobjekt. Gibt ein Objekt der Klasse [Swoole\Process](/process/process) zurück.

!> Swoole Version >= `v4.2.0` verfügbar

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **Parameter** 

  * **`int $worker_id`**
    * **Funktion**: Bestimmt das zu erhaltende `worker` 【Optionale Parameter, Standard ist der aktuelle `worker`】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

!> Muss nach dem `start` aufgerufen werden, und nur in der `onWorkerStart`- oder einer anderen Rückruffunktion des Arbeitsprozesses;  
Das zurückgegebene `Process`-Objekt ist ein Singleton-Modell, und wiederholtes Aufrufen von `getProcess()` im Arbeitsprozess wird dasselbe Objekt zurückgeben.

* **Beispiel für die Verwendung**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```

### detach()

Verschiede den aktuellen Worker-Prozess aus der Prozesspoolverwaltung, der Boden wird sofort einen neuen Prozess erstellen, der alte Prozess behandelt keine Daten mehr und die Lebenszyklusverwaltung liegt in den Händen der Anwendungscodebene.

!> Swoole Version >= `v4.7.0` verfügbar

```php
Swoole\Process\Pool->detach(): bool
```
