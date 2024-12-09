# sicherer Kontextcontainer Queue

Erstellen Sie ein kontextunsicheres `Queue`-Struktur, das als Parameter an Unter threade weitergegeben werden kann. Die Lese- und Schreibvorgänge sind für andere Threads sichtbar, wenn sie im Hintergrund stattfinden.

## Merkmale
- `Thread\Queue` ist eine First-In-First-Out (FIFO)-Datenstruktur.

- `Map`, `ArrayList`, `Queue` werden automatisch memory zugeordnet, es ist nicht notwendig, sie wie `Table` fest zu belegen.

- Der Boden wird automatisch gesperrt, es ist threadsafe.

- Referenz für übertragbare Variablenarten finden Sie unter [Thread-Parameterübertragung](thread/transfer.md).

- Es werden keine Iteratoren unterstützt, der Boden verwendet `C++ std::queue`, unterstützt nur FIFO-Operationen.

- `Map`, `ArrayList`, `Queue` müssen vor der Erstellung von Threads als Thread-Parameter an Unter threade weitergegeben werden.

- `Thread\Queue` kann nur Elemente einfügen und entfernen, nicht zufällig Elemente访问ieren.

- `Thread\Queue` hat eine integrierte thread condition variable, die andere Threads bei `push/pop` Operationen wecken oder warten lässt.

## Beispiel

```php
use Swoole\Thread;
use Swoole\Thread\Queue;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $queue = new Queue;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i, $queue);
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16)), Queue::NOTIFY_ONE);
        usleep(random_int(10000, 100000));
    }
    $n = 4;
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1];
    while (1) {
        $job = $queue->pop(-1);
        if (!$job) {
            break;
        }
        var_dump($job);
    }
}
```

## Konstanten



Name | Funktion
---|---
`Queue::NOTIFY_ONE` | Weckt einen Thread
`Queue::NOTIFY_ALL` | Weckt alle Threads


## Methodenliste


### __construct()
Konstruktor für den sicheren kontextuellen Container `Queue`

```php
Swoole\Thread\Queue->__construct()
```


### push()
Schreibt Daten am Ende der Queue

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

  * **Parameter**
      * `mixed $value`
          * Funktion: Der zu schreibende Dateninhalt.
          * Standardwert: Keiner.
          * Andere Werte: Keiner.

      !> Um Missverständnisse zu vermeiden, geben Sie nicht `null` und `false` in den Kanal ein
  
      * `int $notify`
          * Funktion: Ob Threads, die auf Lesevorgänge warten, geweckt werden sollen.
          * Standardwert: `0`, keine Threads wecken
          * Andere Werte: `Swoole\Thread\Queue::NOTIFY_ONE` weckt einen Thread, `Swoole\Thread\Queue::NOTIFY_ALL` weckt alle Threads.



### pop()
Extrahiert Daten vom Kopf der Queue

```php
Swoole\Thread\Queue()->pop(float $timeout = 0): mixed
```

* **Parameter**
    * `float $wait`
        * Funktion: Die Wartezeit.
        * Standardwert: `0`, bedeutet keine Wartezeit.
        * Andere Werte: Wenn nicht `0`, bedeutet es, dass der Produzent `push()` Daten innerhalb von `$timeout` Sekunden wartet, wenn die Queue leer ist, negativ bedeutet es, dass es nie超时.

* **Rückgabe**
    * Gibt Daten vom Kopf der Queue zurück, wenn die Queue leer ist, wird direkt `NULL` zurückgegeben.

> Wenn `Queue::NOTIFY_ALL` verwendet wird, um alle Threads zu wecken, erhält nur ein Thread Daten, die von der `push()` Operation geschrieben wurden


### count()
Gibt die Anzahl der Elemente in der Queue zurück

```php
Swoole\Thread\Queue()->count(): int
```

* **Rückgabe**
    * Gibt die Anzahl der Elemente in der Queue zurück.

### clean()
Löscht alle Elemente

```php
Swoole\Thread\Queue()->clean(): void
```
