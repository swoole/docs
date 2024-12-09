# Swoole\Process

Swoole bietet ein Prozessverwaltungssystem, um die PHP-Funktion `pcntl` zu ersetzen.

> Dieser Modul ist ziemlich niedriglevel und eine Encapsulation der Prozessverwaltung des Betriebssystems. Der Benutzer muss Erfahrung mit Multiprozessierung auf Linux-Systemen haben.

Das von PHP selbst bereitgestellte `pcntl` hat viele Mängel, wie zum Beispiel:

* Es bietet keine Funktionen für Kommunikation zwischen Prozessen
* Es unterstützt keine Umleitung von Standardeingabe und -ausgabe
* Es bietet nur die primitive Schnittstelle `fork`, die leicht falsch verwendet werden kann

`Process` bietet leistungsstärkere Funktionen und eine leichter zu nutzende API, die es PHP erleichtert, mit Multiprozessierung umzugehen.

`Process` bietet folgende Merkmale:

* Es ist einfach, um Kommunikation zwischen Prozessen zu erreichen
* Es unterstützt die Umleitung von Standardeingabe und -ausgabe. In einem Unterprozess wird `echo` nicht auf dem Bildschirm ausgegeben, sondern in einen Pipe geschrieben. Die Lese von Tastatureingaben kann umgeleitet werden, um Daten aus einem Pipe zu lesen
* Es bietet die [exec](/process/process?id=exec)-Schnittstelle, mit der ein durch `Process`创建的 Prozess andere Programme ausführen kann und eine leichte Kommunikation mit dem ursprünglichen PHP-Vaterprozess möglich ist
* In einer Coroutine-Umwelt kann das `Process`-Modul nicht verwendet werden. Stattdessen kann es mit `runtime hook`+`proc_open` erreicht werden, siehe [Coroutine Prozessverwaltung](/coroutine/proc_open)


### Beispiele für die Verwendung

  * Erstellen Sie drei Unterprozesse, der Hauptkernel wartet auf die Rückkehr der Prozesse
  * Wenn der Hauptkernel unerwartet beendet wird, werden die Unterprozesse weiterhin ausgeführt, bis alle Aufgaben abgeschlossen sind und dann beendet

```php
use Swoole\Process;

for ($n = 1; $n <= 3; $n++) {
    $process = new Process(function () use ($n) {
        echo 'Child #' . getmypid() . " start and sleep {$n}s" . PHP_EOL;
        sleep($n);
        echo 'Child #' . getmypid() . ' exit' . PHP_EOL;
    });
    $process->start();
}
for ($n = 3; $n--;) {
    $status = Process::wait(true);
    echo "Recycled #{$status['pid']}, code={$status['code']}, signal={$status['signal']}" . PHP_EOL;
}
echo 'Parent #' . getmypid() . ' exit' . PHP_EOL;
```


## Eigenschaften


### pipe

Der Dateideskriptor des [unixSocket](/learn?id=什么是IPC).

```php
public int $pipe;
```


### msgQueueId

Die `id` der Nachrichtenschlange.

```php
public int $msgQueueId;
```


### msgQueueKey

Die `key` der Nachrichtenschlange.

```php
public string $msgQueueKey;
```


### pid

Die `pid` des aktuellen Prozesses.

```php
public int $pid;
```


### id

Die `id` des aktuellen Prozesses.

```php
public int $id;
```


## Konstanten

Parameter | Funktion
---|---
Swoole\Process::IPC_NOWAIT | Wenn die Nachrichtenschlange keine Daten enthält, wird sofort zurückgegeben
Swoole\Process::PIPE_READ | Schließen des Lesepipos
Swoole\Process::PIPE_WRITE | Schließen des Schreibpipos


## Methoden


### __construct()

Konstruktor.

```php
Swoole\Process->__construct(callable $function, bool $redirect_stdin_stdout = false, int $pipe_type = SOCK_DGRAM, bool $enable_coroutine = false)
```

* **Parameter** 

  * **`callable $function`**
    * **Funktion**: Die Funktion, die nach dem erfolgreichen Erstellen eines Unterprozesses ausgeführt werden soll【Die Funktion wird automatisch als `callback`-Eigenschaft des Objekts gespeichert, beachten Sie jedoch, dass diese Eigenschaft privat ist】.
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`bool $redirect_stdin_stdout`**
    * **Funktion**: Umleiten der Standardeingabe und -ausgabe des Unterprozesses.【Wenn diese Option aktiviert ist, wird der in einem Unterprozess ausgegebene Inhalt nicht auf dem Bildschirm ausgegeben, sondern in einen Pipe des Hauptkerns geschrieben. Die Lese von Tastatureingaben wird in Daten aus einem Pipe geändert. Der Standardwert ist blockierend. Siehe auch [exec()](/process/process?id=exec)-Methode】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $pipe_type`**
    * **Funktion**: [unixSocket](/learn?id=什么是IPC)-Typ【Wenn `$redirect_stdin_stdout` aktiviert ist, wird dieses Argument ignoriert und zwangsläufig zu `SOCK_STREAM`. Wenn es keine Prozess间-Kommunikation im Unterprozess gibt, kann es auf `0` gesetzt werden】
    * **Standardwert**: `SOCK_DGRAM`
    * **Andere Werte**: `0`, `SOCK_STREAM`

  * **`bool $enable_coroutine`**
    * **Funktion**: Aktivieren von Coroutinen in der `callback function`. Wenn dies aktiviert ist, können Sie direkt Coroutine-API in der Funktion des Unterprozesses verwenden
    * **Standardwert**: `false`
    * **Andere Werte**: `true`
    * **Versionseinfluss**: Swoole-Version >= v4.3.0

* **[unixSocket](/learn?id=什么是IPC)-Typ**


unixSocket-Typ | Beschreibung
---|---
0 | Nicht erstellen
1 | Erstellen eines [SOCK_STREAM](/learn?id=什么是IPC)-Typs unixSocket
2 | Erstellen eines [SOCK_DGRAM](/learn?id=什么是IPC)-Typs unixSocket



### useQueue()

Verwenden Sie eine Nachrichtenschlange für Prozess间-Kommunikation.

```php
Swoole\Process->useQueue(int $key = 0, int $mode = SWOOLE_MSGQUEUE_BALANCE, int $capacity = -1): bool
```

* **Parameter** 

  * **`int $key`**
    * **Funktion**: Die `key` der Nachrichtenschlange. Wenn ein Wert kleiner oder gleich 0 übergeben wird, wird unten durch die `ftok`-Funktion eine entsprechende `key` aus dem Dateinamen des aktuellen Ausführungsprogramms generiert.
    * **Standardwert**: `0`
    * **Andere Werte**: Keiner

  * **`int $mode`**
    * **Funktion**: Prozess间-Kommunikationsmodus,
    * **Standardwert**: `SWOOLE_MSGQUEUE_BALANCE`, `Swoole\Process::pop()` gibt den ersten Nachricht der Schlange zurück, `Swoole\Process::push()` fügt keine spezifische Art von Nachricht hinzu.
    * **Andere Werte**: `SWOOLE_MSGQUEUE_ORIENT`, `Swoole\Process::pop()` gibt den ersten Nachricht der Schlange zurück, deren Art als `Prozess-ID + 1` gekennzeichnet ist, `Swoole\Process::push()` fügt eine Nachricht mit der Art `Prozess-ID + 1` hinzu.

  * **`int $capacity`**
    * **Funktion**: Die maximale Anzahl von Nachrichten, die in der Nachrichtenschlange gelagert werden dürfen.
    * **Standardwert**: `-1`
    * **Andere Werte**: Keiner

* **Hinweis**

  * Wenn die Nachrichtenschlange keine Daten enthält, wird `Swoole\Porcess->pop()` immer blockieren, oder wenn die Nachrichtenschlange keinen Platz mehr hat, um neue Daten aufzunehmen, wird `Swoole\Porcess->push()` auch immer blockieren. Wenn Sie nicht blockieren möchten, muss der Wert von `$mode` entweder `SWOOLE_MSGQUEUE_BALANCE|Swoole\Process::IPC_NOWAIT` oder `SWOOLE_MSGQUEUE_ORIENT|Swoole\Process::IPC_NOWAIT` sein.


### statQueue()

Erhalten Sie den Zustand der Nachrichtenschlange

```php
Swoole\Process->statQueue(): array|false
```

* **Rückkehrwert** 

  * Returniert ein Array für Erfolg, das zwei Schlüssel-Wert-Paare enthält: `queue_num` zeigt die Gesamtzahl der Nachrichten in der Schlange, `queue_bytes` zeigt die Gesamtgröße der Nachrichten in der Schlange.
  * Returniert `false` für Fehler.


### freeQueue()

Zerstören Sie die Nachrichtenschlange.

```php
Swoole\Process->freeQueue(): bool
```

* **Rückkehrwert** 

  * Returniert `true` für Erfolg.
  * Returniert `false` für Fehler.


### pop()

Holen Sie Daten aus der Nachrichtenschlange.

```php
Swoole\Process->pop(int $size = 65536): string|false
```

* **Parameter** 

  * **`int $size`**
    * **Funktion**: Die Größe der gelieferten Daten.
    * **Standardwert**: `65536`
    * **Andere Werte**: Keiner


* **Rückkehrwert** 

  * Returniert ein `string` für Erfolg.
  * Returniert `false` für Fehler.

* **Hinweis**

  * Wenn der Typ der Nachrichtenschlange `SW_MSGQUEUE_BALANCE` ist, wird die erste Nachricht der Schlange zurückgegeben.
  * Wenn der Typ der Nachrichtenschlange `SW_MSGQUEUE_ORIENT` ist, wird die erste Nachricht der Schlange zurückgegeben, deren Art als `Prozess-ID + 1` gekennzeichnet ist.
### push()

Wendet Daten an die Nachrichtenschlange.

```php
Swoole\Process->push(string $data): bool
```

* **Parameter**

  * **`string $data`**
    * **Funktion**: Die zu sendenden Daten.
    * **Standardwert**: ``
    * **Andere Werte**: `Keine`


* **Rückkehrwert**

  * Rückkehr `true`, wenn erfolgreich.
  * Rückkehr `false`, bei Fehlschluss.

* **Hinweis**

  * Wenn der Nachrichtenschlangentyp `SW_MSGQUEUE_BALANCE` ist, werden die Daten direkt in die Nachrichtenschlange eingefügt.
  * Wenn der Nachrichtenschlangentyp `SW_MSGQUEUE_ORIENT` ist, wird den Daten ein Typ hinzugefügt, der aus der aktuellen `Prozess-ID + 1` besteht.


### setTimeout()

Legt die Lese- und Schreib-Zeitüberschreitung für die Nachrichtenschlange fest.

```php
Swoole\Process->setTimeout(float $seconds): bool
```

* **Parameter**

  * **`float $seconds`**
    * **Funktion**: Die Zeitüberschreitung in Sekunden.
    * **Standardwert**: `Keine`
    * **Andere Werte**: `Keine`


* **Rückkehrwert**

  * Rückkehr `true`, wenn erfolgreich.
  * Rückkehr `false`, bei Fehlschluss.


### setBlocking()

Legt fest, ob die Nachrichtenschlangensockel blockiert sein sollen.

```php
Swoole\Process->setBlocking(bool $$blocking): void
```

* **Parameter**

  * **`bool $blocking`**
    * **Funktion**: Ob blockiert sein soll, `true` bedeutet Blockierung, `false` bedeutet keine Blockierung.
    * **Standardwert**: `Keine`
    * **Andere Werte**: `Keine`

* **Hinweis**

  * Der neu erstellte Prozesssockel ist standardmäßig blockiert, daher wird beim Kommunizieren über UNIX-Domänensockel das Senden oder Lesen von Nachrichten den Prozess blockieren.


### write()

Schreibt Nachrichten zwischen Vater- und Kindprozess (UNIX-Domänensockel).

```php
Swoole\Process->write(string $data): false|int
```

* **Parameter**

  * **`string $data`**
    * **Funktion**: Die zu schreibenden Daten.
    * **Standardwert**: `Keine`
    * **Andere Werte**: `Keine`


* **Rückkehrwert**

  * Rückkehr `int`, wenn erfolgreich, und zeigt die Anzahl der erfolgreich geschriebenen Bytes an.
  * Rückkehr `false`, bei Fehlschluss.


### read()

Liest Nachrichten zwischen Vater- und Kindprozess (UNIX-Domänensockel).

```php
Swoole\Process->read(int $size = 8192): false|string
```

* **Parameter**

  * **`int $size`**
    * **Funktion**: Die Größe der zu lesenden Daten.
    * **Standardwert**: `8192`
    * **Andere Werte**: `Keine`


* **Rückkehrwert**

  * Rückkehr `string`, wenn erfolgreich.
  * Rückkehr `false`, bei Fehlschluss.


### set()

Legt Parameter fest.

```php
Swoole\Process->set(array $settings): void
```

Man kann `enable_coroutine` verwenden, um zu steuern, ob Coroutinen aktiviert sind, was dem vierten Parameter der Konstruktorfunktion entspricht.

```php
Swoole\Process->set(['enable_coroutine' => true]);
```

!> Swoole-Version >= v4.4.4 verfügbar


### start()

Führt den `fork`-Systemruf aus, um einen Tochterprozess zu starten. Das Erstellen eines Prozesses unter einem `Linux`-System dauert mehrere hundert Mikrosekunden.

```php
Swoole\Process->start(): int|false
```

* **Rückkehrwert**

  * Rückkehr `PID` des Tochterprozesses, wenn erfolgreich.
  * Rückkehr `false`, bei Fehlschluss. Man kann [swoole_errno](/functions?id=swoole_errno) und [swoole_strerror](/functions?id=swoole_strerror) verwenden, um Fehlercode und Fehlermeldung zu erhalten.

* **Hinweis**

  * Der Tochterprozess erbt die Erinnerung und Dateihandle des Vaterprozesses.
  * Beim Start des Tochterprozesses werden die von der Vaterprozess ererbten [EventLoop](/learn?id=was-ist-eventloop), [Signal](/process/process?id=signal) und [Timer](/timer) beseitigt.
  
  !> Nach dem Ausführen behält der Tochterprozess die Erinnerung und Ressourcen des Vaterprozesses bei, zum Beispiel wenn im Vaterprozess eine Redis-Verbindung erstellt wurde, dann wird diese Verbindung im Tochterprozess erhalten sein und alle Operationen werden auf dieselbe Verbindung durchgeführt. Im Folgenden wird ein Beispiel erklärt

```php
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);

function callback_function() {
    swoole_timer_after(1000, function () {
        echo "hello world\n";
    });
    global $redis;//gleiche Verbindung
};

swoole_timer_tick(1000, function () {
    echo "parent timer\n";
});//wird nicht erbt

Swoole\Process::signal(SIGCHLD, function ($sig) {
    while ($ret = Swoole\Process::wait(false)) {
        // create a new child process
        $p = new Swoole\Process('callback_function');
        $p->start();
    }
});

// create a new child process
$p = new Swoole\Process('callback_function');

$p->start();
```

!> 1. Nach dem Start des Tochterprozesses werden automatisch die von der Vaterprozess erstellten [Swoole\Timer::tick](/timer?id=tick)-Timern, [Process::signal](/process/process?id=signal)-Signalbeobachter und [Swoole\Event::add](/event?id=add)-Ereignisbeobachter beseitigt;  
2. Der Tochterprozess erbt das von der Vaterprozess erstellte `$redis`-Verbindungselement, und Vater- und Tochterprozess verwenden dieselbe Verbindung.


### exportSocket()

 导出 `unixSocket` als `Swoole\Coroutine\Socket`-Objekt aus und nutzen dann die Methoden des `Swoole\Coroutine\Socket`-Objekts für进程间通讯. Weitere Informationen finden Sie unter [Coroutine\socket](/coroutine_client/socket) und [IPC通讯](/learn?id=什么是IPC).

```php
Swoole\Process->exportSocket(): Swoole\Coroutine\Socket|false
```

!> Mehrfache Aufrufe dieses Methoden返回的对象 ist dasselbe;  
`exportSocket()` 导出的 `socket` ist ein neuer `fd`, und das Schließen des exportierten `socket` beeinflusst nicht die ursprüngliche Pipe des Prozesses.  
Da es sich um ein `Swoole\Coroutine\Socket`-Objekt handelt, muss es im [Coroutine-Container](/coroutine/scheduler) verwendet werden, daher muss der Parameter `$enable_coroutine` in der Konstruktur von Swoole\Process auf `true` gesetzt werden.  
Wenn der gleiche Vaterprozess ein `Swoole\Coroutine\Socket`-Objekt verwenden möchte, muss er manuell einen Coroutine-Container mit `Coroutine\run()` erstellen.

* **Rückkehrwert**

  * Erfolgreich gibt es ein `Coroutine\Socket`-Objekt zurück
  * Prozess hat keinen unixSocket erstellt, Operation失败, gibt es `false` zurück

* **Verwendungsvorlage**

Implementiert eine einfache Kommunikation zwischen Vater- und Kindprozess:  

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    echo $socket->recv();
    $socket->send("hello master\n");
    echo "proc1 stop\n";
}, false, 1, true);

$proc1->start();

//Vaterprozess erstellt einen Coroutine-Container
run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    $socket->send("hello pro1\n");
    var_dump($socket->recv());
});
Process::wait(true);
```

Eine komplexere Kommunikationsexample:

```php
use Swoole\Process;
use Swoole\Timer;
use function Swoole\Coroutine\run;

$process = new Process(function ($proc) {
    Timer::tick(1000, function () use ($proc) {
        $socket = $proc->exportSocket();
        $socket->send("hello master\n");
        echo "child timer\n";
    });
}, false, 1, true);

$process->start();

run(function() use ($process) {
    Process::signal(SIGCHLD, static function ($sig) {
        while ($ret = Swoole\Process::wait(false)) {
            /* clean up then event loop will exit */
            Process::signal(SIGCHLD, null);
            Timer::clearAll();
        }
    });
    /* your can run your other async or coroutine code here */
    Timer::tick(500, function () {
        echo "parent timer\n";
    });

    $socket = $process->exportSocket();
    while (1) {
        var_dump($socket->recv());
    }
});
```
!> Beachten Sie, dass der Standardtyp `SOCK_STREAM` ist und man Probleme mit der Grenzen TCP-Datenpakete bearbeiten muss, siehe [Coroutine\socket](/coroutine_client/socket) Methode `setProtocol()`.  

Um Probleme mit den Grenzen TCP-Datenpakete zu vermeiden, verwendet man den `SOCK_DGRAM`-Typ für die IPC-Kommunikation, siehe [IPC通讯](/learn?id=什么是IPC):

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

//IPC通讯 ist auch bei einem SOCK_DGRAM-Typen socket nicht mit sendto/recvfrom-Funktionen, sondern einfach mit send/recv möglich.
$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    while (1) {
        var_dump($socket->send("hello master\n"));
    }
    echo "proc1 stop\n";
}, false, 2, 1);//Konstruktor pipe type wird als 2 übergeben, was SOCK_DGRAM bedeutet

$proc1->start();

run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    Swoole\Coroutine::sleep(5);
    var_dump(strlen($socket->recv()));//Einmalige recv erhält nur einen "hello master\n"-String, es gibt nicht mehrere "hello master\n"-Strings
});

Process::wait(true);
```
### name()

Ändere den Namen des Prozesses. Diese Funktion ist ein Synonym für [swoole_set_process_name](/functions?id=swoole_set_process_name).

```php
Swoole\Process->name(string $name): bool
```

> Nach der Ausführung von `exec` wird der Prozessname vom neuen Programm neu festgelegt; Die `name`-Methode sollte in der Rückruffunktion des auf `start` folgenden Tochterprozesses verwendet werden.


### exec()

Führe ein externes Programm aus, diese Funktion ist eine Umhüllung des `exec`-Systemrufs.

```php
Swoole\Process->exec(string $execfile, array $args);
```

* **Parameter** 

  * **`string $execfile`**
    * **Funktion**: Geben Sie den absoluten Pfad zum ausführbaren Programm an, wie `"/usr/bin/python"`
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`array $args`**
    * **Funktion**: Die Argumentliste für `exec`【z.B. `array('test.py', 123)` entspricht `python test.py 123`】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

Nach erfolgreicher Ausführung wird der Code Segment des aktuellen Prozesses durch das neue Programm ersetzt. Die Tochterprocess verwandelt sich in ein anderes Programm. Der Elternprozess und der aktuelle Prozess sind immer noch ein Vater-Kind-Prozess-Verhältnis.

Zwischen dem Elternprozess und dem neuen Prozess kann über Standard-Eingabe/Ausgabe kommuniziert werden, Standard-Eingabe/Ausgabe-Umleitung muss aktiviert werden.

> `$execfile` muss mit dem absoluten Pfad angegeben werden, sonst wird ein Fehler des fehlenden Dateis gemeldet;  
Da der `exec`-Systemruf das angegebene Programm verwendet, um das aktuelle Programm zu überschreiben, muss der Tochterprozess die Standard-Eingabe/Ausgabe lesen und schreiben, um mit dem Elternprozess zu kommunizieren;  
Wenn nicht `redirect_stdin_stdout = true` angegeben ist, können nach der Ausführung von `exec` der Tochterprozess und der Elternprozess nicht kommunizieren.

* **Anwendungsvorlage**

Beispiel 1: Man kann im Tochterprozess, der von [Swoole\Process](/server/init) erstellt wurde, den [Swoole\Server](/server/init) verwenden, aber aus Sicherheitsgründen muss man nach der Erstellung des Prozesses mit `$process->start` die `$worker->exec()`-Methode aufrufen, um ihn auszuführen. Der Code lautet wie folgt:

```php
$process = new Swoole\Process('callback_function', true);

$pid = $process->start();

function callback_function(Swoole\Process $worker)
{
    $worker->exec('/usr/local/bin/php', array(__DIR__.'/swoole_server.php'));
}

Swoole\Process::wait();
```

Beispiel 2: Starten Sie ein Yii-Programm

```php
$process = new \Swoole\Process(function (\Swoole\Process $childProcess) {
    // Diese Schreibweise wird nicht unterstützt
    // $childProcess->exec('/usr/local/bin/php /var/www/project/yii-best-practice/cli/yii t/index -m=123 abc xyz');

    // Umhüllung des exec-Systemrufs
    // Absoluter Pfad
    // Die Parameter müssen separat in ein Array gelegt werden
    $childProcess->exec('/usr/local/bin/php', ['/var/www/project/yii-best-practice/cli/yii', 't/index', '-m=123', 'abc', 'xyz']); // exec-Systemruf
});
$process->start(); // Starten Sie den Tochterprozess
```

Beispiel 3: Elternprozess und `exec`-Tochterprozess kommunizieren über Standard-Eingabe/Ausgabe:

```php
// exec - Kommunizieren Sie mit dem exec-Prozess über eine Pipe
use Swoole\Process;
use function Swoole\Coroutine\run;

$process = new Process(function (Process $worker) {
    $worker->exec('/bin/echo', ['hello']);
}, true, 1, true); // Standard-Eingabe/Ausgabe-Umleitung muss aktiviert werden

$process->start();

run(function() use($process) {
    $socket = $process->exportSocket();
    echo "from exec: " . $socket->recv() . "\n";
});
```

Beispiel 4: Ausführen einer Shell-Befehl

Die `exec`-Methode unterscheidet sich von der von PHP bereitgestellten `shell_exec`, sie ist eine tiefere Umhüllung des Systemrufs. Wenn Sie einen Shell-Befehl ausführen möchten, verwenden Sie die folgende Methode:

```php
$worker->exec('/bin/sh', array('-c', "cp -rf /data/test/* /tmp/test/"));
```


### close()

Für das Schließen einer gut eingerichteten [unixSocket](/learn?id=什么是IPC). 

```php
Swoole\Process->close(int $which): bool
```

* **Parameter** 

  * **`int $which`**
    * **Funktion**: Da die unixSocket full-duplex ist, geben Sie an, welcher Endpunkt geschlossen werden soll【Standardwert ist `0`, was bedeutet, dass sowohl Lesen als auch Schreiben geschlossen werden, `1`: Schreiben schließen, `2` Lesen schließen】
    * **Standardwert**: `0`, schließen Sie读写-Socket.
    * **Andere Werte**: `Swoole/Process::SW_PIPE_CLOSE_READ` schließen Sie den Lesesocket, `Swoole/Process::SW_PIPE_CLOSE_WRITE` schließen Sie den Schreibsocket,

!> In einigen besonderen Fällen kann das `Process`-Objekt nicht freigesetzt werden, und die kontinuierliche Erstellung von Prozessen kann zu einem Verbindungslecks führen. Wenn Sie diese Funktion aufrufen, können Sie den unixSocket direkt schließen und Ressourcen freisetzen.


### exit()

Verlasse den Tochterprozess.

```php
Swoole\Process->exit(int $status = 0);
```

* **Parameter** 

  * **`int $status`**
    * **Funktion**: Statuscode für den Prozessausstieg【Wenn `$status` `0` ist, bedeutet dies ein normales Ende, und die Reinigung wird fortgesetzt】
    * **Standardwert**: `0`
    * **Andere Werte**: Keiner

!> Die Reinigung umfasst:

  * PHPs `shutdown_function`
  * Objektdestruction (`__destruct`)
  * Andere Erweiterungen der `RSHUTDOWN` Funktion

Wenn `$status` nicht `0` ist, bedeutet dies ein außergewöhnlicher Ausstieg, und der Prozess wird sofort beendet, ohne die Reinigung zu beenden, die mit dem Prozessende verbunden ist.

Im Elternprozess kann man den Zustandskode und das Ereignis des Tochterprozesses, der beendet wurde, mit `Process::wait` erhalten.


### kill()

Sende ein Signal an den Prozess mit dem angegebenen `pid`.

```php
Swoole\Process::kill(int $pid, int $signo = SIGTERM): bool
```

* **Parameter** 

  * **`int $pid`**
    * **Funktion**: Prozess `pid`
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $signo`**
    * **Funktion**: Sendedes Signals【`$signo=0`, kann verwendet werden, um zu überprüfen, ob der Prozess existiert, ohne ein Signal zu senden】
    * **Standardwert**: `SIGTERM`
    * **Andere Werte**: Keiner


### signal()

Stellen Sie eine asynchrone Signalüberwachung ein.

```php
Swoole\Process::signal(int $signo, callable $callback): bool
```

Diese Methode basiert auf `signalfd` und [EventLoop](/learn?id=什么是eventloop) ist asynchron IO, sie kann nicht für blockierende Programme verwendet werden, was dazu führen kann, dass die registrierten Überwachungsrückruffunktionen nicht verarbeitet werden;

Blockierende Programme können die von der `pcntl` Erweiterung bereitgestellte `pcntl_signal` verwenden;

Wenn bereits eine Rückruffunktion für dieses Signal festgelegt wurde, wird sie bei erneuter Einstellung überschrieben.

* **Parameter** 

  * **`int $signo`**
    * **Funktion**: Signal
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`callable $callback`**
    * **Funktion**: Rückruffunktion【Wenn `$callback` `null` ist, bedeutet dies das Entfernen der Signalüberwachung】
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

!> In [Swoole\Server](/server/init) können einige Signalüberwachungen nicht eingerichtet werden, wie `SIGTERM` und `SIGALRM`

* **Anwendungsvorlage**

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
```

!> In der Version `v4.4.0`, wenn im EventLoop des Prozesses nur Ereignisse für Signalüberwachung vorhanden sind, keine anderen Ereignisse (z.B. Timer-Ereignisse usw.), wird der Prozess direkt beendet.

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
Swoole\Event::wait();
```

Der oben genannte Programm wird nicht in den [EventLoop](/learn?id=什么是eventloop) eintreten, `Swoole\Event::wait()` wird sofort zurückkehren und den Prozess beenden.
### wait()

Recyceln Sie das beendete Kindprozes.

!> Bei Swoole-Versionen >= `v4.5.0` wird die Coroutine-Version von `wait()` empfohlen, siehe [Swoole\Coroutine\System::wait()](/coroutine/system?id=wait)

```php
Swoole\Process::wait(bool $blocking = true): array|false
```

* **Parameter** 

  * **`bool $blocking`**
    * **Funktion**: Geben Sie an, ob die Wartezeit blockierend sein soll【Standardmäßig blockierend】
    * **Standardwert**：`true`
    * **Andere Werte**：`false`

* **Rückgabewert**

  * Bei erfolgreicher Operation wird ein Array mit der `PID` des Kindprozesses, dem Exitstatuscode und dem Signal zurückgegeben, das zum `KILL` verwendet wurde
  * Bei Misserfolg wird `false` zurückgegeben

!> Nach jedem beendeten Kindprozess muss der Elternprozess einmal `wait()` aufrufen, um zu recyceln, sonst wird das Kindprozess zu einem Zombieprozess und verschwendet Ressourcen des Betriebssystems. Wenn der Elternprozess andere Aufgaben hat und nicht blockiert werden kann, muss der Elternprozess das Signal `SIGCHLD` registrieren, um den beendeten Prozess mit `wait()` zu verarbeiten. Wenn das `SIGCHILD`-Signal auftritt, können möglicherweise mehrere Kindprozesse gleichzeitig beendet werden; `wait()` muss in nicht blockierender Weise eingerichtet werden und in einem Loop wiederholt werden, bis `false` zurückgegeben wird.

* **Beispiel**

```php
Swoole\Process::signal(SIGCHLD, function ($sig) {
    //Muss false sein, nicht blockierendes Modus
    while ($ret = Swoole\Process::wait(false)) {
        echo "PID={$ret['pid']}\n";
    }
});
```


### daemon()

Verwandeln Sie den aktuellen Prozess in einen Daemon.

```php
Swoole\Process::daemon(bool $nochdir = true, bool $noclose = true): bool
```

* **Parameter** 

  * **`bool $nochdir`**
    * **Funktion**: Ob der aktuelle Arbeitsverzeichnis in das Wurzelverzeichnis gewechselt werden soll【Wird `true` angegeben, wird das aktuelle Arbeitsverzeichnis nicht in das Wurzelverzeichnis gewechselt】
    * **Standardwert**：`true`
    * **Andere Werte**：`false`

  * **`bool $noclose`**
    * **Funktion**: Ob die Standardinput- und Standardoutput-Dateideskriptoren geschlossen werden sollen【Wird `true` angegeben, werden die Standardinput- und Standardoutput-Dateideskriptoren nicht geschlossen】
    * **Standardwert**：`true`
    * **Andere Werte**：`false`

!> Beim Verwandeln in einen Daemon ändert sich die `PID` des Prozesses, die man mit `getmypid()` abrufen kann, um die aktuelle `PID` zu erhalten


### alarm()

Ein hochgenauer Timer, der eine Umhüllung des Betriebssystems `setitimer` ist und Timern im Mikrosekundentakt einrichten kann. Der Timer löst ein Signal aus und muss mit [Process::signal](/process/process?id=signal) oder `pcntl_signal` verwendet werden.

!> `alarm` kann nicht mit [Timer](/timer) gleichzeitig verwendet werden

```php
Swoole\Process->alarm(int $time, int $type = 0): bool
```

* **Parameter** 

  * **`int $time`**
    * **Funktion**: Intervallzeit des Timers【Wenn negativ, wird der Timer gelöscht】
    * **Einheit**：Mikrosekunden
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

  * **`int $type`**
    * **Funktion**: Timertyp
    * **Standardwert**：`0`
    * **Andere Werte**：


Timertyp | Beschreibung
---|---
0 | Reale Zeit, löst das `SIGALRM`-Signal aus
1 | Benutzerzeit, löst das `SIGVTALRM`-Signal aus
2 | Benutzerzeit + Kernzeit, löst das `SIGPROF`-Signal aus

* **Rückgabewert**

  * Bei erfolgreicher Einstellung wird `true` zurückgegeben
  * Bei Misserfolg wird `false` zurückgegeben, und man kann mit `swoole_errno` den Fehlercode erhalten

* **Beispiel**

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

run(function () {
    Process::signal(SIGALRM, function () {
        static $i = 0;
        echo "#{$i}\talarm\n";
        $i++;
        if ($i > 20) {
            Process::alarm(-1);
            Process::kill(getmypid());
        }
    });

    //100ms
    Process::alarm(100 * 1000);

    while(true) {
        sleep(0.5);
    }
});
```


### setAffinity()

Richten Sie die CPU-Affinität ein und binden Sie den Prozess an bestimmte CPU-Kerne. 

Diese Funktion ermöglicht es dem Prozess, nur auf bestimmten CPU-Kernen zu laufen und bestimmte CPU-Ressourcen für wichtigere Programme freizugeben.

```php
Swoole\Process->setAffinity(array $cpus): bool
```

* **Parameter** 

  * **`array $cpus`**
    * **Funktion**: Binden Sie CPU-Kerne 【Zum Beispiel `array(0,2,3)` bedeutet das Binden an CPU0/CPU2/CPU3】
    * **Standardwert**：Keine
    * **Andere Werte**：Keine


!> - Die Elemente in `$cpus` dürfen nicht mehr als die Anzahl der CPU-Kerne betragen;  

- Die CPU-ID darf nicht größer als (die Anzahl der CPU-Kerne - 1) sein;  

- Diese Funktion erfordert Unterstützung des Betriebssystems für das Einstellen der CPU-Bindung;  
- Mit [swoole_cpu_num()](/functions?id=swoole_cpu_num) kann man die Anzahl der CPU-Kerne des aktuellen Servers abrufen.


### getAffinity()
Erhalten Sie die CPU-Affinität des Prozesses

```php
Swoole\Process->getAffinity(): array
```
Der Rückgabewert ist ein Array, dessen Elemente die Anzahl der CPU-Kerne sind, zum Beispiel: `[0, 1, 3, 4]` bedeutet, dass dieser Prozess auf den CPU-Kernen `0/1/3/4` ausgeführt wird


### setPriority()

Richten Sie die Priorität von Prozessen, Prozessgruppen und Benutzerprozessen ein.

!> Swoole-Version >= `v4.5.9` verfügbar

```php
Swoole\Process->setPriority(int $which, int $priority): bool
```

* **Parameter** 

  * **`int $which`**
    * **Funktion**: Bestimmen Sie den Typ der Prioritätsänderung
    * **Standardwert**：Keine
    * **Andere Werte**：


| Konstante         | Beschreibung     |
| ------------ | -------- |
| PRIO_PROCESS | Prozess     |
| PRIO_PGRP    | Prozessgruppe   |
| PRIO_USER    | Benutzerprozess |

  * **`int $priority`**
    * **Funktion**: Priorität. Je kleiner der Wert, desto höher die Priorität
    * **Standardwert**：Keine
    * **Andere Werte**：`[-20, 20]`

* **Rückgabewert**

  * Wenn `false` zurückgegeben wird, können Sie mit [swoole_errno](/functions?id=swoole_errno) und [swoole_strerror](/functions?id=swoole_strerror) den Fehlercode und die Fehlermeldung erhalten.

### getPriority()

Holen Sie sich die Priorität des Prozesses.

!> Swoole-Version >= `v4.5.9` verfügbar

```php
Swoole\Process->getPriority(int $which): int
```
