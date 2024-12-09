# Hochleistungs-Teilungsshared-Speicher-Tabelle

Da die Sprache PHP keine Multithreading unterstützt, verwendet Swoole einen Multiprocessmodus. Im Multiprocessmodus gibt es eine Prozess-Arbeitsspeichersperrung, und das Bearbeiten von globalen Variablen und globalen Über Variablen innerhalb von Arbeitsprozessen ist in anderen Prozessen nicht wirksam.

> Wenn `worker_num=1` festgelegt ist, gibt es keine Prozessisolierung, und Daten können mit globalen Variablen gespeichert werden.

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "Verbindung geöffnet: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

Obwohl `$fds` eine globale Variable ist, ist sie nur im aktuellen Prozess wirksam. Das Swoole-Server-Unterlagen werden mehrere `Worker` Prozesse erstellen, und die in `var_dump($fds)` gedruckten Werte sind nur einige der vernetzten `fd`.

Die entsprechende Lösung ist der Einsatz eines externen Speicherdienstes:

* Datenbanken, wie: `MySQL`, `MongoDB`
* Caching-Server, wie: `Redis`, `Memcache`
* Datenträgerdateien, bei gleichzeitiger Lese- und Schreibzugriff auf Datenträgerdateien muss ein Lock eingeleitet werden

Ordnäre Datenbank- und Datenträgerdateioperationen weisen viele `IO`-Wartetimes auf. Daher wird empfohlen:

* `Redis`, eine in-Memory-Datenbank mit sehr schneller Lese- und Schreibgeschwindigkeit, aber Probleme mit TCP-Verbindungen usw., die Leistung ist nicht die höchste.
* `/dev/shm`, ein in-Memory-Dateisystem, bei dem alle Lese- und Schreiboperationen vollständig im Speicher abgeschlossen werden, ohne `IO`-Verbrauch, sehr hohe Leistung, aber die Daten sind nicht formatiert und es gibt Probleme mit Datenausgleichung.

?> Abgesehen vom oben genannten Einsatz von Speichern wird empfohlen, Daten mit Teilungsshared-Speicher zu speichern, `Swoole\Table` ist eine ultra-hohe Leistung, konsekutive Datenstruktur, die auf Teilungsshared-Speicher und Lock basiert. Sie wird zur Lösung des Problems der gemeinsamen Nutzung und Synchronisierung von Daten in Multiprocess/Multithread-Umgebungen verwendet. Die Speicherkapazität von `Table` wird nicht vom PHP `memory_limit` kontrolliert.

!> Bitte verwenden Sie nicht die Array-Methode zum Lesen und Schreiben von `Table`, Sie müssen unbedingt die in der Dokumentation bereitgestellten APIs verwenden;  
 Das mit der Array-Methode extrahierte `Table\Row` Objekt ist ein einmaliges Objekt, bitte verlassen Sie sich nicht zu sehr darauf für viele Operationen.
Ab der Version `v4.7.0` wird die Array-Methode zum Lesen und Schreiben von `Table` nicht mehr unterstützt, und das `Table\Row` Objekt wurde entfernt.

* **Vorteile**

  * Starke Leistung, eine einzelne Thread kann pro Sekunde bis zu zwei Millionen Lesungen und Schreibungen durchführen;
  * Die Anwendungscodebase muss keine Locking durchführen, `Table` hat einen eingebauten Row-Lock-Spinlock, alle Operationen sind multithread/multiprocess sicher. Der Benutzerlayer muss sich nicht um Probleme mit Datenausgleichung kümmern;
  * Multiprocess-Unterstützung, `Table` kann für den gemeinsamen Einsatz von Daten zwischen mehreren Prozessen verwendet werden;
  * Row-Locking wird verwendet, anstatt globales Locking, nur wenn zwei Prozesse zur gleichen Zeit auf derselben CPU aktiv sind und gleichzeitig dieselbe Daten lesen, wird ein Lock-Contest stattfinden.

* **Iteration**

!> Bitte führen Sie während der Iteration keine Löschungsoperationen durch (sie können alle `key`s extrahieren und dann löschen)

Die `Table`-Klasse implementiert die Iterator- und `Countable`-Schnittstellen, und Sie können die `foreach`-Schleife verwenden, um sie zu iterieren, und die `count`-Funktion, um die Anzahl der aktuellen Zeilen zu berechnen.

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```


## Eigenschaften


### size

Erhalten Sie die maximale Anzahl von Zeilen für die Tabelle.

```php
Swoole\Table->size;
```


### memorySize

Erhalten Sie die tatsächlich verbrauchte Speichergröße in Byte.

```php
Swoole\Table->memorySize;
```


## Methoden


### __construct()

Erstellen Sie eine Speichertable.

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **Parameter** 

    * **`int $size`**
      * **Funktion**: Bestimmen Sie die maximale Anzahl von Zeilen für die Tabelle
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

      !> Da die `Table`-Infrastruktur auf Teilungsshared-Speicher basiert, kann sie sich nicht dynamisch vergrößern. Daher muss `$size` vor der Erstellung berechnet und festgelegt werden, die maximale Anzahl von Zeilen, die die `Table` speichern kann, ist direkt mit `$size` korreliert, aber nicht vollständig gleich, zum Beispiel ist die tatsächlich gespeicherte Anzahl von Zeilen für `$size` = `1024` **kleiner** als `1024`, wenn `$size` zu groß ist, wird die `Table` aufgrund von unzureichendem Maschinen-Speicher nicht erfolgreich erstellt.  

    * **`float $conflict_proportion`**
      * **Funktion**: Die maximale Proportion von Hash-Konflikten
      * **Standardwert**: `0.2` (d.h. `20%`)
      * **Andere Werte**: Minimale `0.2`, maximale `1`

  * **Kapazitätsberechnung**

      * Wenn `$size` kein Power of Two ist, wie `1024`, `8192`, `65536` usw., wird der底层 automatisch auf ein ähnliches Zahl angepasst, wenn kleiner als `1024`, dann standardmäßig zu `1024`, das heißt `1024` ist der Mindestwert. Ab der Version `v4.4.6` beträgt der Mindestwert `64`.
      * Die Gesamtgröße des Speichers, den die `Table` verbraucht, beträgt (`HashTable-Strukturlänge` + `KEY-Länge 64 Byte` + `$size Wert`) * (`1 + `$conflict_proportion Wert als Hash-Konflikt`) * (`Spaltengröße`).
      * Wenn Ihre Daten `KEY` und Hash-Konfliktrate über `20%` liegen, ist die reservierte Kapazität für Konflikte nicht ausreichend, und das Festlegen neuer Daten wird einen `Unable to allocate memory` Fehler zurückgeben und `false` zurück, der Speichervorgang wird fehlschlagen, in diesem Fall müssen Sie den `$size` Wert erhöhen und den Dienst neu starten.
      * Wenn genügend Speicher vorhanden ist, sollten Sie diesen Wert so hoch wie möglich einstellen.


### column()

Fügen Sie einer Speichertable eine Spalte hinzu.

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **Parameter** 

    * **`string $name`**
      * **Funktion**: Bestimmen Sie den Namen der Spalte
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $type`**
      * **Funktion**: Bestimmen Sie den Typ der Spalte
      * **Standardwert**: Keiner
      * **Andere Werte**: `Table::TYPE_INT`, `Table::TYPE_FLOAT`, `Table::TYPE_STRING`

    * **`int $size`**
      * **Funktion**: Bestimmen Sie die maximale Länge der String-Spalte in Byte [String-Typen müssen `$size` angeben]
      * **Einheit**: Byte
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **`$type` Typenbeschreibung**


Typ | Beschreibung
---|---
Table::TYPE_INT | Standardmäßig 8 Byte
Table::TYPE_STRING | Nachdem es festgelegt wurde, darf die festgelegte String nicht länger sein als `$size` angegeben
Table::TYPE_FLOAT | Wird 8 Byte Speicher beanspruchen


### create()

Erstellen Sie eine Speichertable. Nachdem Sie das Struktur der Tabelle definiert haben, führen Sie `create` aus, um dem Betriebssystem Speicher zu beantragen und die Tabelle zu erstellen.

```php
Swoole\Table->create(): bool
```

Nachdem Sie die `create`-Methode verwendet haben, können Sie die [memorySize](/memory/table?id=memorysize)-Eigenschaft verwenden, um die tatsächlich verbrauchte Speichergröße in Byte zu erhalten

  * **Hinweise** 

    * Vor dem Aufrufen von `create` dürfen Sie keine Lesungs- und Schreiboperationen wie `set` und `get` verwenden
    * Nach dem Aufrufen von `create` dürfen Sie keine `column`-Methoden zum Hinzufügen neuer Spalten verwenden
    * Wenn der systemspezifische Speicher nicht ausreicht und der Antrag fehlschlägt, gibt `create` `false` zurück
    * Wenn der Speicher erfolgreich beantragt wurde, gibt `create` `true` zurück

    !> Um die `Table` zu verwenden, muss sie vor dem Erstellen von Unterprozessen mit `Swoole\Process` oder `Swoole\Server` 创建;  
    Wenn Sie `Table` in einem `Server` verwenden, müssen Sie `Swoole\Table->create()` vor dem Starten des `Server` ausführen.

  * **Verwendungsvorlage**

```php
$table = new Swoole\Table(1024);
$table->column('id', Swoole\Table::TYPE_INT);
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->column('num', Swoole\Table::TYPE_FLOAT);
$table->create();

$worker = new Swoole\Process(function () {}, false, false);
$worker->start();

//$serv = new Swoole\Server('127.0.0.1', 9501);
//$serv->start();
```
### set()

Legt die Daten einer Zeile fest. Die `Table` nutzt eine `key-value` Art und Weise, um Daten zu访问ieren.

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **Parameter** 

    * **`string $key`**
      * **Funktion**：Die `key` der Daten
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

      !> dieselbe `$key` bezieht sich auf dieselbe Zeiledaten, wenn `set` die gleiche `$key` verwendet wird, wird die vorherige Daten überschrieben, die maximale Länge der `$key` darf nicht mehr als 63 Byte betragen

    * **`array $value`**
      * **Funktion**：Die `value` der Daten
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

      !> Muss ein Array sein, muss genau mit dem in der Felddefinition festgelegten `$name` übereinstimmen

  * **Rückgabewert**

    * Erfolgreich zurückgegeben wird `true`
    * Bei Misserfolg wird `false` zurückgegeben, möglicherweise aufgrund zu vieler Hash-Konflikte, die zur Dynamik der Raumzuweisung keinen Speicher mehr zur Verfügung stellen können, kann der zweite Parameter des Konstruktors erhöht werden

!> -`Table->set()` kann alle Felderwerte festlegen, aber auch nur einzelne Felder ändern;  
   -`Table->set()` vor der Einstellung, alle Felder der Zeiledaten sind leer;  
   -`set`/`get`/`del` sind mit row-lock ausgestattet, daher ist es nicht notwendig, einen Lock einzufordern;  
   -**Schlüssel ist nicht binary-sicher, muss ein String-Typ sein, binary Daten dürfen nicht übergeben werden.**
    
  * **Anwendungsvorlage**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **Festlegen einer über die maximale Länge hinausgehenden Zeichenfolge**
    
    Wenn eine über die beim Festlegen der Spalte festgelegte maximale Größe hinausgehende Zeichenfolge übergeben wird, wird sie von unten automatisch abgeschnitten.
    
    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * Die Spalte `str_value` hat eine maximale Größe von 5 Byte, aber `set` hat eine über `5` Byte hinausgehende Zeichenfolge festgelegt
    * Von unten werden automatisch 5 Byte Daten abgeschnitten, der endgültige Wert von `str_value` ist `world`

!> Ab Version `v4.3` wurde die untere Ebene auf die Memory-Länge ausgerichtet. Die Länge der Zeichenfolge muss ein Vielfaches von 8 sein, wie zum Beispiel eine Länge von 5 wird automatisch auf 8 Byte ausgerichtet, daher ist der Wert von `str_value` `world 12`


### incr()

Atomische Zählungssteigerung.

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **Parameter** 

    * **`string $key`**
      * **Funktion**：Die `key` der Daten【Wenn die Zeile, die der `$key` entspricht, nicht existiert, ist der Standardwert der Spalte `0`】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`string $column`**
      * **Funktion**：Gibt den Namen der Spalte an【Wird nur für Floating-Point- und Integer-Felder unterstützt】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`string $incrby`**
      * **Funktion**：Increment 【Wenn die Spalte `int` ist, muss `$incrby` ein Integer sein, wenn die Spalte `float` ist, muss `$incrby` ein Float sein】
      * **Standardwert**：`1`
      * **Andere Werte**：Keine

  * **Rückgabewert**

    Gibt das endgültige Ergebniswert zurück


### decr()

Atomische Zählungsabnahme.

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **Parameter** 

    * **`string $key`**
      * **Funktion**：Die `key` der Daten【Wenn die Zeile, die der `$key` entspricht, nicht existiert, ist der Standardwert der Spalte `0`】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`string $column`**
      * **Funktion**：Gibt den Namen der Spalte an【Wird nur für Floating-Point- und Integer-Felder unterstützt】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`string $decrby`**
      * **Funktion**：Increment 【Wenn die Spalte `int` ist, muss `$decrby` ein Integer sein, wenn die Spalte `float` ist, muss `$decrby` ein Float sein】
      * **Standardwert**：`1`
      * **Andere Werte**：Keine

  * **Rückgabewert**

    Gibt das endgültige Ergebniswert zurück

    !> Wenn der Wert `0` ist, wird die Abnahme zu einem negativen Zahl


### get()

Holt eine Zeile Daten.

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **Parameter** 

    * **`string $key`**
      * **Funktion**：Die `key` der Daten【Muss ein String-Typ sein】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`string $field`**
      * **Funktion**：Wenn `$field` angegeben ist, wird nur der Wert dieser Felder zurückgegeben, anstatt das gesamte Record
      * **Standardwert**：Kein
      * **Andere Werte**：Keine
      
  * **Rückgabewert**

    * Wenn `$key` nicht existiert, wird `false` zurückgegeben
    * Erfolgreich zurückgegeben wird ein Ergebnisarray
    * Wenn `$field` angegeben ist, wird nur der Wert dieser Felder zurückgegeben, anstatt das gesamte Record


### exist()

Prüft, ob in der table eine bestimmte key existiert.

```php
Swoole\Table->exist(string $key): bool
```

  * **Parameter** 

    * **`string $key`**
      * **Funktion**：Die `key` der Daten【Muss ein String-Typ sein】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine


### count()

Gibt die Anzahl der Einträge in der table zurück.

```php
Swoole\Table->count(): int
```


### del()

Löscht Daten.

!> `Key` ist nicht binary-sicher, muss ein String-Typ sein, binary Daten dürfen nicht übergeben werden; **Bitte löschen Sie nicht während des Iterierens**.

```php
Swoole\Table->del(string $key): bool
```

  * **Rückgabewert**

    * Wenn die Daten, die der `$key` entsprechen, nicht existieren, wird `false` zurückgegeben
    * Erfolgreich gelöscht wird `true` zurückgegeben


### stats()

Holt den Zustand der `Swoole\Table`.

```php
Swoole\Table->stats(): array
```

!> Swoole Version >= `v4.8.0` verfügbar


## Hilfsfunktion :id=swoole_table

Erleichtert es den Benutzern, schnell eine `Swoole\Table` zu erstellen.

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Swoole Version >= `v4.6.0` verfügbar. `$fields` ist im Format `foo:i/foo:s:num/foo:f`

| Kurzname | Vollname   | Typ               |
| -------- | ---------- | ------------------ |
| i        | int        | Table::TYPE_INT    |
| s        | string     | Table::TYPE_STRING |
| f        | float      | Table::TYPE_FLOAT  |

Beispiel:

```php
$table = swoole_table(1024, 'fd:int, reactor_id:i, data:s:64');
var_dump($table);

$table = new Swoole\Table(1024, 0.25);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();
var_dump($table);
```

## Vollständiges Beispiel

```php
<?php
$table = new Swoole\Table(1024);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();

$serv = new Swoole\Server('127.0.0.1', 9501);
$serv->set(['dispatch_mode' => 1]);
$serv->table = $table;

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {

	$cmd = explode(" ", trim($data));

	//get
	if ($cmd[0] == 'get')
	{
		//get self
		if (count($cmd) < 2)
		{
			$cmd[1] = $fd;
		}
		$get_fd = intval($cmd[1]);
		$info = $serv->table->get($get_fd);
		$serv->send($fd, var_export($info, true)."\n");
	}
	//set
	elseif ($cmd[0] == 'set')
	{
		$ret = $serv->table->set($fd, array('reactor_id' => $data, 'fd' => $fd, 'data' => $cmd[1]));
		if ($ret === false)
		{
			$serv->send($fd, "ERROR\n");
		}
		else
		{
			$serv->send($fd, "OK\n");
		}
	}
	else
	{
		$serv->send($fd, "command error.\n");
	}
});

$serv->start();
```
