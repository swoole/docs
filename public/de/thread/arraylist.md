# Sicherheitskonzentrationscontainer List

Erstellen Sie eine parallele `List`-Struktur, die als Thread-Parameter an Unter线程 weitergegeben werden kann. Die Lese- und Schreibvorgänge sind für andere Threads sichtbar.

## Funktionen
- `Map`, `ArrayList`, `Queue` werden automatisch memory zugeordnet, es ist nicht notwendig, sie wie `Table` fest zu belegen.

- Der Boden wird automatisch abgesperrt, es ist thread-sicher.

- Referenz für die übertragbaren Variablentypen finden Sie unter [Datenarten](thread/transfer.md)

- Iteratoren werden nicht unterstützt, verwenden Sie stattdessen `toArray()`

- `Map`, `ArrayList`, `Queue` Objekte müssen vor der Thread-Erstellung als Thread-Parameter an Unter线程 weitergegeben werden

- `Thread\ArrayList` implementiert die `ArrayAccess` und `Countable` Schnittstellen, es kann direkt wie ein Array operiert werden

- `Thread\ArrayList` unterstützt nur numerische Indizes, für Nicht-Numeriken wird eine Zwangsumwandlung durchgeführt

## Beispiel
```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new ArrayList;
    $thread = new Thread(__FILE__, $i, $list);
    sleep(1);
    $list[] = unique();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```

- Hinzufügen oder Bearbeiten: `$list[$index] = $value`

- Löschen: `unset($list[$index])`

- Lesen: `$value = $list[$index]`
- Längenabfrage: `count($list)`

## Löschungen
Bitte beachten Sie, dass Löschvorgänge eine Massenbewegung des `List` verursachen können. Zum Beispiel, wenn ein `List` 1000 Elemente hat und `unset($list[4])` aufgerufen wird, muss eine Massenbewegung von `$list[5:999]` durchgeführt werden, um den leeren Platz nach dem Löschvorgang von `$list[4]` zu füllen. Aber es wird nicht tief kopiert, nur die Pointers werden bewegt.

> Wenn der `List` sehr groß ist, kann das Löschen von Elementen an der Vorderfront erhebliche CPU-Ressourcen verbrauchen

## Methoden

### __construct()
Konstruktor für den sicheren parallelen Konzentrationscontainer `ArrayList`

```php
Swoole\Thread\ArrayList->__construct(?array $values = null)
```

- `$values` ist optional, durchlaufen Sie das Array, um die Werte aus dem Array hinzuzufügen

-认可和, dass nur Array-Typen akzeptiert werden, assoziative Arrays werden抛出 ein Ausnahme
- Assoziative Arrays müssen mit `array_values` in einen Array-Typ umgewandelt werden

### incr()
Lassen Sie die Werte in der `ArrayList` sicher inkrementieren, Support für float oder integer, wenn auf andere Typen ein inkrementiert wird, wird automatisch zu integer umgewandelt, initialisiert auf `0`, und dann wird das inkrementiert.

```php
Swoole\Thread\ArrayList->incr(int $index, mixed $value = 1) : int | float
```

* **Parameter**
    * `int $index`
        * Funktion: Index-Zahl, muss ein gültiger Index-Adresse sein, sonst wird eine Ausnahme geworfen.
        * Standardwert: Keine.
        * Andere Werte: Keine.

    * `mixed $value`
        * Funktion: Der Wert, der inkrementiert werden soll.
        * Standardwert: 1.
        * Andere Werte: Keine.

* **Rückgabewert**
    * Gibt den inkrementierten Wert zurück.


### decr()
Lassen Sie die Werte in der `ArrayList` sicher dekrementieren, Support für float oder integer, wenn auf andere Typen ein dekrementiert wird, wird automatisch zu integer umgewandelt, initialisiert auf `0`, und dann wird das dekrementiert.

```php
Swoole\Thread\ArrayList->(int $index, $value = 1) : int | float
```

* **Parameter**
    * `int $index`
        * Funktion: Index-Zahl, muss ein gültiger Index-Adresse sein, sonst wird eine Ausnahme geworfen.
        * Standardwert: Keine.
        * Andere Werte: Keine.

    * `mixed $value`
        * Funktion: Der Wert, der dekrementiert werden soll.
        * Standardwert: 1.
        * Andere Werte: Keine.

* **Rückgabewert**
    * Gibt den dekrementierten Wert zurück.


### count()
Holen Sie sich die Anzahl der Elemente in der `ArrayList`

```php
Swoole\Thread\ArrayList()->count(): int
```

* **Rückgabewert**
    * Gibt die Anzahl der Elemente in der Liste zurück.


### toArray()
Konvertieren Sie die `ArrayList` in ein Array

```php
Swoole\Thread\ArrayList()->toArray(): array
```

* **Rückgabewert** Typ-Array, enthält alle Elemente der `ArrayList`.

### clean()
Leeren Sie alle Elemente

```php
Swoole\Thread\ArrayList()->clean(): void
```
