# Coroutine\Barrier

In der [Swoole Library](https://github.com/swoole/library) wird ein bequemerer Werkzeug für die parallele Verwaltung von Coroutinen bereitgestellt: Der `Coroutine\Barrier` Coroutine Barrier, auch bekannt als Coroutine Fence. Er basiert auf PHP Reference Counting und Coroutine API.

Im Vergleich zum [Coroutine\WaitGroup](/coroutine/wait_group) ist die Verwendung des `Coroutine\Barrier` einfacher, es reicht aus, ihn über Parameter zu übergeben oder die `use` Syntax von Closures zu verwenden, um die Coroutine-Funktionen einzuführen.

!> Swoole Version >= v4.5.5 ist erforderlich.


## Beispiel für die Verwendung

```php
use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;

run(function () {
    $barrier = Barrier::make();

    $count = 0;
    $N = 4;

    foreach (range(1, $N) as $i) {
        Coroutine::create(function () use ($barrier, &$count) {
            System::sleep(0.5);
            $count++;
        });
    }

    Barrier::wait($barrier);
    
    assert($count == $N);
});
```

## Ausführungsablauf

* Zuerst wird mit `Barrier::make()` eine neue Coroutine Barrier erstellt
* In den Child Coroutines wird der Barrier über die `use` Syntax weitergegeben, was die Referenzanzahl erhöht
* An dem Punkt, an dem gewartet werden muss, wird `Barrier::wait($barrier)` eingefügt, was die aktuelle Coroutine automatisch anhält, bis die Child Coroutines, die den Barrier referenzieren, fertig sind
* Wenn ein Child Coroutine fertig ist, wird die Referenzanzahl des `$barrier` Objekts verringert, bis sie `0` erreicht hat
* Wenn alle Child Coroutines ihre Aufgaben abgeschlossen haben und verlassen, ist die Referenzanzahl des `$barrier` Objekts `0`, und im Destrukturfunktions der `$barrier` Objekte werden die angehaltenen Coroutines automatisch wieder fortgesetzt, und der Code hinter `Barrier::wait($barrier)` wird zurückgegeben

Der `Coroutine\Barrier` ist ein einfacherer Kontextsteuerung控制器 als [WaitGroup](/coroutine/wait_group) und [Channel](/coroutine/channel), der die Benutzererfahrung bei der parallelen Programmierung in PHP erheblich verbessert.
