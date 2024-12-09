# Sicherheits-Konflikt-Containers Map

Erstellen Sie eine konforme `Map`-Struktur, die als Thread-Parameter an Unter-Threads weitergegeben werden kann. Die Lese- und Schreibvorgänge sind in anderen Threads sichtbar.

## Merkmale
- `Map`, `ArrayList`, `Queue` werden automatisch memory allocate, es ist nicht notwendig, wie bei `Table` eine feste Allokation vorzunehmen.

- Der Boden wird automatisch gesperrt, es ist thread-sicher.

- Die übertragbaren Variabientypen finden Sie unter [Datenarten](thread/transfer.md).

- Es werden keine Iteratoren unterstützt, es können `keys()`, `values()`, `toArray()` verwendet werden.

- Es ist notwendig, die `Map`, `ArrayList`, `Queue` Objekte vor der Erstellung des Threads als Thread-Parameter an Unter-Threads weiterzugeben.

- `Thread\Map` implementiert die `ArrayAccess` und `Countable` Schnittstellen, es kann direkt wie ein Array behandelt werden.

## Beispiel
```php
use Swoole\Thread;
use Swoole\Thread\Map;

$args = Thread::getArguments();
if (empty($args)) {
    $map = new Map;
    $thread = new Thread(__FILE__, $i, $map);
    sleep(1);
    $map['test'] = unique();
    $thread->join();
} else {
    $map = $args[1];
    sleep(2);
    var_dump($map['test']);
}
```

- Hinzufügen oder Bearbeiten: `$map[$key] = $value`

- Löschen: `unset($map[$key])`

- Lesen: `$value = $map[$key]`
- Länge abrufen: `count($map)`

## Methoden

### __construct()
Konstruktor für den sicheren Konflikt-Container `Map`

```php
Swoole\Thread\Map->__construct(?array $values = null)
```

- `$values` optional, durchlaufen Sie das Array, um die Werte des Arrays zur `Map` hinzuzufügen

### add()
Schreiben Sie Daten in die `Map`

```php
Swoole\Thread\Map->add(mixed $key, mixed $value) : bool
```
  * **Parameter**
      * `mixed $key`
          * Funktion: Der zu hinzufügende Schlüssel.
          * Standardwert: Keine.
          * Andere Werte: Keine.
  
      * `mixed $value`
          * Funktion: Der zu hinzufügender Wert.
          * Standardwert: Keine.
          * Andere Werte: Keine.
  
  * **Rückgabewert**
      * Wenn `$key` bereits vorhanden ist, wird `false` zurückgegeben, sonst wird `true` zurückgegeben, um anzugeben, dass das Hinzufügen erfolgreich war.


### update()
Aktualisieren Sie Daten in der `Map`

```php
Swoole\Thread\Map->update(mixed $key, mixed $value) : bool
```

  * **Parameter**
      * `mixed $key`
          * Funktion: Der zu aktualisierende Schlüssel.
          * Standardwert: Keine.
          * Andere Werte: Keine.
  
      * `mixed $value`
          * Funktion: Der zu aktualisierende Wert.
          * Standardwert: Keine.
          * Andere Werte: Keine.
  
  * **Rückgabewert**
      * Wenn `$key` nicht vorhanden ist, wird `false` zurückgegeben, sonst wird `true` zurückgegeben, um anzugeben, dass die Aktualisierung erfolgreich war


### incr()
Lassen Sie die Daten in der `Map` sicher inkrementieren, es werden floats oder integers unterstützt, wenn mit anderen Typen ein Inkrementvorgang durchgeführt wird, wird dieser automatisch zu einem Integer umgewandelt, initialisiert auf `0`, und dann wird der Inkrementvorgang durchgeführt

```php
Swoole\Thread\Map->incr(mixed $key, mixed $value = 1) : int | float
```
* **Parameter**
    * `mixed $key`
        * Funktion: Der zu inkrementierende Schlüssel, wenn er nicht vorhanden ist, wird er automatisch erstellt und initialisiert auf `0`.
        * Standardwert: Keine.
        * Andere Werte: Keine.

    * `mixed $value`
        * Funktion: Der zu inkrementierende Wert.
        * Standardwert: 1.
        * Andere Werte: Keine.

* **Rückgabewert**
    * Gibt den inkrementierten Wert zurück.


### decr()
Lassen Sie die Daten in der `Map` sicher dekrementieren, es werden floats oder integers unterstützt, wenn mit anderen Typen ein Dekrementvorgang durchgeführt wird, wird dieser automatisch zu einem Integer umgewandelt, initialisiert auf `0`, und dann wird der Dekrementvorgang durchgeführt

```php
Swoole\Thread\Map->decr(mixed $key, mixed $value = 1) : int | float
```
* **Parameter**
    * `mixed $key`
        * Funktion: Der zu dekrementierende Schlüssel, wenn er nicht vorhanden ist, wird er automatisch erstellt und initialisiert auf `0`.
        * Standardwert: Keine.
        * Andere Werte: Keine.

    * `mixed $value`
        * Funktion: Der zu dekrementierende Wert.
        * Standardwert: 1.
        * Andere Werte: Keine.

* **Rückgabewert**
    * Gibt den dekrementierten Wert zurück.


### count()
Holen Sie sich die Anzahl der Elemente

```php
Swoole\Thread\Map()->count(): int
```

  * **Rückgabewert**
      * Gibt die Anzahl der Elemente in der Map zurück.


### keys()
Geben Sie alle `key` zurück

```php
Swoole\Thread\Map()->keys(): array
```

  * **Rückgabewert**
    * Gibt alle `key` der `Map` zurück


### values()
Geben Sie alle `value` zurück

```php
Swoole\Thread\Map()->values(): array
```

* **Rückgabewert**
    * Gibt alle `value` der `Map` zurück


### toArray()
Konvertieren Sie die `Map` in ein Array

```php
Swoole\Thread\Map()->toArray(): array
```

### clean()
Leeren Sie alle Elemente

```php
