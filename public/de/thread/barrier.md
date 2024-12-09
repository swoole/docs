# Faden für die gleichzeitige Ausführung von Threads Barriere

`Thread\Barrier` ist ein Mechanismus zur Synchronisation von Threads. Es ermöglicht es mehreren Threads, sich an bestimmten Punkten zu synchronisieren, um sicherzustellen, dass alle Threads ihre Aufgaben vor einem bestimmten kritischen Punkt (Barriere) abgeschlossen haben. Nur wenn alle beteiligten Threads diese Barriere erreicht haben, können sie das nachfolgende Code ausführen.

Zum Beispiel haben wir `4` Threads erstellt und möchten, dass diese Threads nach ihrer vollständigen Bereitstellung gemeinsam Aufgaben ausführen, ähnlich wie bei einem Läuferwettbewerb, bei dem der Startschuss des Schiedsrichters alle Läufer gleichzeitig loslässt. Dies kann mit `Thread\Barrier` erreicht werden.

## Beispiel
```php
use Swoole\Thread;
use Swoole\Thread\Barrier;

const N = 4;
$args = Thread::getArguments();

if (empty($args)) {
    $barrier = new Barrier(N);
    $n = N;
    $threads = [];
    while($n--) {
        $threads[] = new Thread(__FILE__, $barrier, $n);
    }
} else {
    $barrier = $args[0];
    $n = $args[1];
    // Warten auf die Bereitstellung aller Threads
    $barrier->wait();
    echo "thread $n ist running\n";
}
```

## Methoden

### __construct()
Konstruktor

```php
Thread\Barrier()->__construct(int $count): void
```

  * **Parameter**
      * `int $count`
          * Funktion: Anzahl der Threads, muss größer als `1` sein.
          * Standardwert: Keiner.
          * Andere Werte: Keiner.
  
Die Anzahl der Threads, die die `wait`-Operation ausführen, muss mit der eingestellten计数 übereinstimmen, sonst werden alle Threads blockiert.

### wait()

Blockieren und warten auf andere Threads, bis alle Threads im `wait` Zustand sind, dann werden alle wartenden Threads gleichzeitig geweckt und das nachfolgende Code ausführen.

```php
Thread\Barrier()->wait(): void
```
