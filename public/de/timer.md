# Timer

Ein Timer mit Präzision in Millisekunden. Er wird unter der Basis von `epoll_wait` und `setitimer` realisiert und verwendet eine MinHeap-Datenstruktur, die es ermöglicht, eine große Anzahl von Timern hinzuzufügen.

* In synchronen IO-Prozessen wird `setitimer` und Signals verwendet, wie zum Beispiel in den Prozessen `Manager` und `TaskWorker`
* In asynchronen IO-Prozessen wird `epoll_wait`/`kevent`/`poll`/`select` für die Timeoutzeit verwendet


## Leistung

Die Timertabelle wird unter der Basis einer MinHeap-Datenstruktur realisiert. Die Hinzufügung und Löschung von Timern sind reine Speicheroperationen, daher ist die Leistung sehr hoch.

> In den offiziellen Benchmark-Skripten [timer.php](https://github.com/swoole/benchmark/blob/master/timer.php) dauert es etwa `0,08s`, um `100.000` Timer mit zufälligen Zeiten hinzuzufügen oder zu löschen.

```shell
~/workspace/swoole/benchmark$ php timer.php
add 100000 timer :0.091133117675781s
del 100000 timer :0.084658145904541s
```

!> Timertable sind Speicheroperationen, ohne `IO`-Verbrauch


## Unterschiede

`Timer` unterscheidet sich von PHPs eigener `pcntl_alarm`. `pcntl_alarm` wird auf der Grundlage von `Clock-Signal + tick`-Funktion realisiert und hat einige Mängel:

  * Maximale Unterstützung nur bis zur Sekunde, während `Timer` bis Millisekundenbereich reicht
  * Nicht unterstützt, mehrere Timer gleichzeitig einzustellen
  * `pcntl_alarm` hängt von `declare(ticks = 1)` ab, was eine schlechte Leistung ist


## Null-Millisekunden-Timer

Die Basis unterstützt keine Timer mit einer Zeitparameter von `0`. Dies unterscheidet sich von Programmiersprachen wie `Node.js`. In `Swoole` kann dies mit [Swoole\Event::defer](/event?id=defer) simuliert werden.

```php
Swoole\Event::defer(function () {
  echo "hello\n";
});
```

!> Der oben genannte Code hat genau den gleichen Effekt wie `setTimeout(func, 0)` in `JS`.


## Aliase

`tick()`, `after()`, `clear()` haben alle eine Funktionsstil-Aliase


Klasse-Statische Methode | Funktionsstil-Alias
---|---
`Swoole\Timer::tick()` | `swoole_timer_tick()`
`Swoole\Timer::after()` | `swoole_timer_after()`
`Swoole\Timer::clear()` | `swoole_timer_clear()`


## Methoden


### tick()

Ein Intervall-Clock-Timer einrichten.

Im Gegensatz zum `after` Timer wird der `tick` Timer kontinuierlich ausgelöst und wird erst durch das Aufrufen von [Timer::clear](/timer?id=clear) gelöscht.

```php
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int
```

!> 1. Timer ist nur im aktuellen Prozessraum gültig  
   2. Timer ist rein asynchron implementiert und kann nicht mit Funktionen aus [Synchron IO](/learn?id=同步io异步io) verwendet werden, sonst wird die Ausführungzeit des Timers durcheinander geraten  
   3. Es kann zu einigen Fehlern in der Ausführung des Timer-Callback-Funktions kommen

  * **Parameter** 

    * **`int $msec`**
      * **Funktion** : Angeben der Zeit
      * **Einheit der Wert** : Millisekunden【Zum Beispiel `1000` bedeutet `1` Sekunde, in Versionen unter `v4.2.10` darf die maximale nicht überschreiten `86400000`】
      * **Standardwert** : Keiner
      * **Andere Werte** : Keiner

    * **`callable $callback_function`**
      * **Funktion** : Die durchzuführende Funktion, wenn die Zeit abgelaufen ist, muss einrufbar sein
      * **Standardwert** : Keiner
      * **Andere Werte** : Keiner

    * **`...$params`**
      * **Funktion** : Daten an die auszuführende Funktion übergeben【Dieser Parameter ist auch optional】
      * **Standardwert** : Keiner
      * **Andere Werte** : Keiner
      
      !> Mit der `use`-Syntax von anonymen Funktionen können Parameter an die Callback-Funktion übergeben werden

  * **$callback_function Callback-Funktion** 

    ```php
    callbackFunction(int $timer_id, ...$params);
    ```

      * **`int $timer_id`**
        * **Funktion** : Die `ID` des Timers【Kann verwendet werden, um diesen Timer mit [Timer::clear](/timer?id=clear) zu löschen】
        * **Standardwert** : Keiner
        * **Andere Werte** : Keiner

      * **`...$params`**
        * **Funktion** : Der dritte Parameter `$param`, der von `Timer::tick` übergeben wurde
        * **Standardwert** : Keiner
        * **Andere Werte** : Keiner

  * **Erweiterungen**

    * **Timer-Korrektur**

      Die Ausführungszeit des Timer-Callback-Funktions beeinflusst nicht die Zeit der nächsten Timer-Ausführung. Beispiel: Ein `tick` Timer mit einer Zeit von `0,002s` und einer Dauer von `10ms` wird das erste Mal nach `0,012s` ausführen. Wenn die Callback-Funktion `5ms` dauert, wird der nächste Timer immer noch nach `0,022s` ausgelöst, nicht nach `0,027s`.
      
      Wenn jedoch die Ausführungszeit des Timer-Callback-Funktions zu lang ist und sogar die Zeit der nächsten Timer-Ausführung überspannt. Die Basis führt eine Zeitkorrektur durch, wirft überholte Handlungen weg und löst den Timer zur nächsten Zeit aus. Wie im obigen Beispiel, wenn die Callback-Funktion nach `0,012s` `15ms` dauert, sollte der Timer nach `0,022s` eine Timer-Callback auslösen. Tatsächlich wird der Timer erst nach `0,027s` zurückkehren, zu dieser Zeit ist der Timer längst abgelaufen. Die Basis löst den Timer-Callback erneut nach `0,032s` aus.
    
    * **Coroutine-Modus**

      Im Coroutine-Umwelt wird im Callback von `Timer::tick` automatisch ein Coroutine erstellt, und man kann direkt die Coroutine-API verwenden, ohne einen Coroutine mit `go` zu erstellen.
      
      !> Es ist möglich, [enable_coroutine](/timer?id=close-timer-co) zu setzen, um die automatische Erstellung von Coroutines zu deaktivieren

  * **Nutzungsbeispiele**

    ```php
    Swoole\Timer::tick(1000, function(){
        echo "timeout\n";
    });
    ```

    * **Richtiges Beispiel**

    ```php
    Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
        echo "timer_id #$timer_id, nach 3000ms.\n";
        echo "param1 ist $param1, param2 ist $param2.\n";

        Swoole\Timer::tick(14000, function ($timer_id) {
            echo "timer_id #$timer_id, nach 14000ms.\n";
        });
    }, "A", "B");
    ```

    * **Falsches Beispiel**

    ```php
    Swoole\Timer::tick(3000, function () {
        echo "nach 3000ms.\n";
        sleep(14);
        echo "nach 14000ms.\n";
    });
    ```


### after()

Führe die Funktion nach einer指定的时间 aus. Die `Swoole\Timer::after` -Funktion ist ein einmaliger Timer, der nach seiner Ausführung zerstört wird.

Diese Funktion unterscheidet sich von der in der PHP-Standardbibliothek bereitgestellten `sleep` -Funktion, da `after` nicht blockierend ist. Während ein `sleep` -Aufruf den aktuellen Prozess blockiert und es dem Prozess unmöglich macht, neue Anforderungen zu verarbeiten.

```php
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int
```

  * **Parameter** 

    * **`int $msec`**
      * **Funktion** : Angeben der Zeit
      * **Einheit der Wert** : Millisekunden【Zum Beispiel `1000` bedeutet `1` Sekunde, in Versionen unter `v4.2.10` darf die maximale nicht überschreiten `86400000`】
      * **Standardwert** : Keiner
      * **Andere Werte** : Keiner

    * **`callable $callback_function`**
      * **Funktion** : Die durchzuführende Funktion, wenn die Zeit abgelaufen ist, muss einrufbar sein.
      * **Standardwert** : Keiner
      * **Andere Werte** : Keiner

    * **`...$params`**
      * **Funktion** : Daten an die auszuführende Funktion übergeben【Dieser Parameter ist auch optional】
      * **Standardwert** : Keiner
      * **Andere Werte** : Keiner
      
      !> Mit der `use`-Syntax von anonymen Funktionen können Parameter an die Callback-Funktion übergeben werden

  * **Rückgabewert**

    * Bei erfolgreicher Ausführung wird die Timer-ID zurückgegeben, um den Timer zu annullieren, kann die [Swoole\Timer::clear](/timer?id=clear) -Methode verwendet werden

  * **Erweiterungen**

    * **Coroutine-Modus**

      Im Coroutine-Umwelt wird im Callback von `Swoole\Timer::after](/timer?id=after) automatisch ein Coroutine erstellt, und man kann direkt die Coroutine-API verwenden, ohne einen Coroutine mit `go` zu erstellen.
      
      !> Es ist möglich, [enable_coroutine](/timer?id=close-timer-co) zu setzen, um die automatische Erstellung von Coroutines zu deaktivieren

  * **Nutzungsbeispiele**

```php
$str = "Swoole";
Swoole\Timer::after(1000, function() use ($str) {
    echo "Hallo, $str\n";
});
```
### clear()

Verwende den Timer-ID, um den Timer zu löschen.

```php
Swoole\Timer::clear(int $timer_id): bool
```

  * **Parameter** 

    * **`int $timer_id`**
      * **Funktion**：Timer-ID 【Wird zurückgegeben, wenn [Timer::tick](/timer?id=tick) oder [Timer::after](/timer?id=after) aufgerufen wird】
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

!> `Swoole\Timer::clear` kann nicht für das Löschen von Timern in anderen Prozessen verwendet werden, es wirkt nur auf den aktuellen Prozess

  * **Beispiel**

```php
$timer = Swoole\Timer::after(1000, function () {
    echo "timeout\n";
});

var_dump(Swoole\Timer::clear($timer));
var_dump($timer);

// Ausgabe: bool(true) int(1)
// Nicht ausgegeben: timeout
```


### clearAll()

Lösche alle Timern im aktuellen Worker-Prozess.

!> Verfügbar ab Swoole-Version >= `v4.4.0`

```php
Swoole\Timer::clearAll(): bool
```


### info()

Gebe Informationen über den `timer` zurück.

!> Verfügbar ab Swoole-Version >= `v4.4.0`

```php
Swoole\Timer::info(int $timer_id): array
```

  * **Rückgabewert**

```php
array(5) {
  ["exec_msec"]=>
  int(6000)
  ["exec_count"]=> // v4.8.0 hinzugefügt
  int(5)
  ["interval"]=>
  int(1000)
  ["round"]=>
  int(0)
  ["removed"]=>
  bool(false)
}
```


### list()

Gebe einen Iterator zurück, der alle Timern im aktuellen Worker-Prozess iterieren kann. Mit `foreach` kann man die IDs aller Timern durchlaufen.

!> Verfügbar ab Swoole-Version >= `v4.4.0`

```php
Swoole\Timer::list(): Swoole\Timer\Iterator
```

  * **Beispiel**

```php
foreach (Swoole\Timer::list() as $timer_id) {
    var_dump(Swoole\Timer::info($timer_id));
}
```


### stats()

Zeige den Zustand der Timer an.

!> Verfügbar ab Swoole-Version >= `v4.4.0`

```php
Swoole\Timer::stats(): array
```

  * **Rückgabewert**

```php
array(3) {
  ["initialized"]=>
  bool(true)
  ["num"]=>
  int(1000)
  ["round"]=>
  int(1)
}
```


### set()

Stelle Timer-Parameter fest.

```php
Swoole\Timer::set(array $array): void
```

!> Diese Methode wurde in der Version `v4.6.0` als veraltet gekennzeichnet.

## Schließen von Coroutinen :id=close-timer-co

Standardmäßig werden beim Ausführen der Rückruffunktion des Timers Coroutinen automatisch erstellt. Es ist möglich, die Schließung von Coroutinen für den Timer einzeln festzulegen.

```php
swoole_async_set([
  'enable_coroutine' => false,
]);
```
