# Methoden und Eigenschaften


## Methoden


### __construct()
Mehrhread-Konstruktormethode

```php
Swoole\Thread->__construct(string $script_file, mixed ...$args)
```
* **Parameter**
    * `string $script_file`
        * Funktion: Der von der Thread nach dem Start auszuführende Dateiname.
        * Standardwert: Keiner.
        * Andere Werte: Keiner.

    * `mixed $args`
        * Funktion: Die vom Hauptthread an die Unterthread übertragene gemeinsame Daten, die im Unterthread mit `Swoole\Thread::getArguments()` accessing werden können.
        * Standardwert: Keiner.
        * Andere Werte: Keiner.

!> Ein Thread-Erstellungssuccess wirft eine `Swoole\Exception` aus, die mit `try catch` abgefangen werden kann.


### join()
Der Hauptthread wartet darauf, dass der Unterthread beendet ist. Wenn der Unterthread noch läuft, blockiert die `join()` Methode, bis der Unterthread beendet ist.

```php
Swoole\Thread->join(): bool
```
* **Rückkehrwert**
    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist.


### joinable()
Prüft, ob der Unterthread bereits beendet ist.

```php
Swoole\Thread->joinable(): bool
```


#### Rückkehrwert

- `true`, wenn der Unterthread beendet ist und eine `join()`-An调用 nicht blockieren wird
- `false`, wenn der Unterthread nicht beendet ist


### detach()
Lässt den Unterthread aus der Kontrolle des Haupttreads entkommen und benötigt keine `join()`-An调用 mehr, um auf den Thread zu warten, bis er beendet ist.

```php
Swoole\Thread->detach(): bool
```
* **Rückkehrwert**
    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist.


### getId()
Statische Methode, um die `ID` des aktuellen Threads zu erhalten.

```php
Swoole\Thread::getId(): int
```
* **Rückkehrwert**
    * Gibt einen Integer zurück, der die ID des aktuellen Threads darstellt.


### getArguments()
Statische Methode, um die durch den Haupttread mit `new Swoole\Thread()` übertragene gemeinsame Daten zu erhalten, die im Unterthread aufgerufen werden können.

```php
Swoole\Thread::getArguments(): ?array
```

* **Rückkehrwert**
    * Gibt die vom Elternprozess übertragene gemeinsame Daten im Unterthread zurück.

?> Der Haupttread hat keine Thread-Parameter, und man kann unterscheiden zwischen Eltern- und Unterthread durch das Überprüfen, ob die Thread-Parameter leer sind, und unterschiedliche Logiken für sie ausführen
```php
use Swoole\Thread;

$args = Thread::getArguments(); // Wenn es der Haupttread ist, ist $args leer, wenn es ein Unterthread ist, ist $args nicht leer
if (empty($args)) {
    # Haupttread
    new Thread(__FILE__, 'child thread'); // Thread-Parameter übergeben
    echo "main thread\n";
} else {
    # Unterthread
    var_dump($args); // Ausgeben: ['child thread']
}
```


### getInfo()
Statische Methode, um Informationen über die aktuelle Mehrthreadumgebung zu erhalten.

```php
Swoole\Thread::getInfo(): array
```
Die zurückgegebene Array-Information sieht wie folgt aus:



- `is_main_thread`: Ob der aktuelle Thread der Haupttread ist

- `is_shutdown`: Ob der Thread bereits geschlossen wurde
- `thread_num`: Die Anzahl der aktiven Threads


### getPriority()
Statische Methode, um Informationen über die Thread-Planung des aktuellen Threads zu erhalten

```php
Swoole\Thread->getPriority(): array
```
Die zurückgegebene Array-Information sieht wie folgt aus:



- `policy`: Die Thread-Planungspolitik
- `priority`: Die Planungspriorität des Threads


### setPriority()
Statische Methode, um die Planungspriorität und Politik des aktuellen Threads einzustellen

?> Nur `root`-Benutzer können dies anpassen, andere Benutzer erhalten eine Ablehnung, wenn sie dies ausführen

```php
Swoole\Thread->setPriority(int $priority, int $policy = -1): bool
```

* **Parameter**
    * `int $priority`
        * Funktion: Setzt die Planungspriorität des Threads
        * Standardwert: Keiner.
        * Andere Werte: Keiner.

    * `mixed $policy`
        * Funktion: Setzt die Planungsstrategie des Threads
        * Standardwert: `-1`, bedeutet keine Anpassung der Planungspolitik.
        * Andere Werte: `Thread::SCHED_*` relevante Konstanten.

* **Rückkehrwert**
    * Erfolgreich gibt `true` zurück
    * Fehlgeschlagen gibt `false` zurück, verwenden Sie `swoole_last_error()` um Fehlerinformationen zu erhalten

> `SCHED_BATCH/SCHED_ISO/SCHED_IDLE/SCHED_DEADLINE` sind nur unter `Linux`-Systemen verfügbar  

> `SCHED_FIFO/SCHED_RR` Strategien für Threads sind in der Regel Echtzeit-Threads mit einer höheren Priorität als normale Threads und erhalten mehr `CPU` Zeitblöcke


### getAffinity()
Statische Methode, um die CPU-Verwandtschaft des aktuellen Threads zu erhalten

```php
Swoole\Thread->getAffinity(): array
```
Die zurückgegebene Wert ist ein Array, dessen Elemente die Anzahl der CPU-Kerne sind, zum Beispiel: `[0, 1, 3, 4]` bedeutet, dass dieser Thread auf den CPU-Kernen `0/1/3/4` ausgeführt wird


### setAffinity()
Statische Methode, um die CPU-Verwandtschaft des aktuellen Threads einzustellen

```php
Swoole\Thread->setAffinity(array $cpu_set): bool
```

* **Parameter**
    * `array $cpu_set`
        * Funktion: Eine Liste der CPU-Kerne, zum Beispiel `[0, 1, 3, 4]`
        * Standardwert: Keiner.
        * Andere Werte: Keiner.

* **Rückkehrwert**
    * Erfolgreich gibt `true` zurück
    * Fehlgeschlagen gibt `false` zurück, verwenden Sie `swoole_last_error()` um Fehlerinformationen zu erhalten


### setName()
Statische Methode, um den Namen des aktuellen Threads einzustellen. Bei der Verwendung von Werkzeugen wie `ps` und `gdb` zur Betrachtung und Fehlersuche wird ein freundlicheres Erscheinungsbild bereitgestellt.

```php
Swoole\Thread->setName(string $name): bool
```

* **Parameter**
    * `string $name`
        * Funktion: Der Name des Threads
        * Standardwert: Keiner.
        * Andere Werte: Keiner.

* **Rückkehrwert**
    * Erfolgreich gibt `true` zurück
    * Fehlgeschlagen gibt `false` zurück, verwenden Sie `swoole_last_error()` um Fehlerinformationen zu erhalten

```shell
$ ps aux|grep -v grep|grep pool.php
swoole  2226813  0.1  0.1 423860 49024  pts/6   Sl+  17:38  0:00 php pool.php

$ ps -T -p 2226813
   PID   SPID TTY          TIME CMD
2226813 2226813 pts/6     00:00:00 Master Thread
2226813 2226814 pts/6     00:00:00 Worker Thread 0
2226813 2226815 pts/6     00:00:00 Worker Thread 1
2226813 2226816 pts/6     00:00:00 Worker Thread 2
2226813 2226817 pts/6     00:00:00 Worker Thread 3
```


### getNativeId()
Erhalten Sie die systemische `ID` des Threads, die einer Thread-ID ähnelt und einem Prozess-PID.

```php
Swoole\Thread->getNativeId(): int
```

Diese Funktion ruft unter `Linux`-Systemen die systemische Anrufung `gettid()` auf, um eine ähnliche ID wie die Betriebssystem-Thread-ID zu erhalten, die ein Kurzinteger ist. Wenn der Prozess-Thread zerstört wird, könnte er vom Betriebssystem entfernt werden.

Diese `ID` kann für die Verwendung mit `gdb`, `strace` zur Fehlersuche verwendet werden, zum Beispiel `gdb -p $tid`. Darüber hinaus kann man Informationen über die Ausführung des Threads durch das Lesen von `/proc/{PID}/task/{ThreadNativeId}` erhalten.


## Eigenschaften


### id

Durch diese Objekteigenschaft wird die `ID` des Unterthreads erhalten, die eine `int`-Typ ist.

> Diese Eigenschaft kann nur im Elternthread verwendet werden, Unterthreads können kein `$thread`-Objekt erhalten und sollten die statische Methode `Thread::getId()` verwenden, um die ID des Threads zu erhalten

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```


## Konstanten

Name | Funktion
---|---
`Thread::HARDWARE_CONCURRENCY` | Die Anzahl der Hardware-Concurrenzthreads, in der Regel die Anzahl der CPU-Kerne
`Thread::API_NAME` | Der Name der Thread-API, zum Beispiel `POSIX Threads`
`Thread::SCHED_OTHER` | Die Thread-Planungspolitik `SCHED_OTHER`
`Thread::SCHED_FIFO` | Die Thread-Planungspolitik `SCHED_FIFO`
`Thread::SCHED_RR` | Die Thread-Planungspolitik `SCHED_RR`
`Thread::SCHED_BATCH` | Die Thread-Planungspolitik `SCHED_BATCH`
`Thread::SCHED_ISO` | Die Thread-Planungspolitik `SCHED_ISO`
`Thread::SCHED_IDLE` | Die Thread-Planungspolitik `SCHED_IDLE`
`Thread::SCHED_DEADLINE` | Die Thread-Planungspolitik `SCHED_DEADLINE`
