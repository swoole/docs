# Prozess-/Thread-Schutzschlüssel Lock

* In PHP kann man sehr bequem einen Schalter `Swoole\Lock` erstellen, um Daten synchronisiert zu halten. Die `Lock`-Klasse unterstützt fünf Arten von Schaltern.
* Für den Mehr线程-Modus muss man den `Swoole\Thread\Lock` verwenden, dessen Schnittstelle im Vergleich zu `Swoole\Lock` genau gleich ist, nur der Namespace ist anders.


Schaltertyp | Beschreibung
---|---
SWOOLE_MUTEX | Mutex-Schalter
SWOOLE_RWLOCK | Leseschalter mit Schreibzugriff
SWOOLE_SPINLOCK | Spinnschalter
SWOOLE_FILELOCK | Dateischalter (veraltet)
SWOOLE_SEM | Semaphore (veraltet)

!> Bitte vermeiden Sie das Erstellen von Schaltern in Rückruffunktionen wie [onReceive](/server/events?id=onreceive), sonst steigt die Memory kontinuierlich an und verursacht einen Memory-Leak.


## Beispiel

```php
$lock = new Swoole\Lock(SWOOLE_MUTEX);
echo "[Master]create lock\n";
$lock->lock();
if (pcntl_fork() > 0)
{
  sleep(1);
  $lock->unlock();
} 
else
{
  echo "[Child] Wait Lock\n";
  $lock->lock();
  echo "[Child] Get Lock\n";
  $lock->unlock();
  exit("[Child] exit\n");
}
echo "[Master]release lock\n";
unset($lock);
sleep(1);
echo "[Master]exit\n";
```


## Warnung

!> In Coroutinen können Schalter nicht verwendet werden, bitte verwenden Sie sie vorsichtig und vermeiden Sie die Verwendung von APIs, die eine Coroutine-Wechsel verursachen könnten, zwischen `lock` und `unlock`.


### Fehlerbeispiel

!> Dieser Code führt unter Coroutine-Modus zu einem `100%` Deadlock.

```php
$lock = new Swoole\Lock();
$c = 2;

while ($c--) {
  go(function () use ($lock) {
      $lock->lock();
      Co::sleep(1);
      $lock->unlock();
  });
}
```


## Methoden


### __construct()

Konstruktor.

```php
Swoole\Lock::__construct(int $type = SWOOLE_MUTEX, string $lockfile = '');
```

!> Bitte vermeiden Sie das wiederholte Erstellen/Zerstören von Schalterobjekten, sonst kommt es zu einem Memory-Leak.

  * **Parameter** 

    * **`int $type`**
      * **Funktion**：Schaltertyp
      * **Standardwert**：`SWOOLE_MUTEX`【Mutex-Schalter】
      * **Andere Werte**：Nicht vorhanden

    * **`string $lockfile`**
      * **Funktion**：Gibt den Pfad für den Dateischalter an【muss angegeben werden, wenn der Typ `SWOOLE_FILELOCK` ist】
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

!> Jede Art von Schalter unterstützt unterschiedliche Methoden. Zum Beispiel können Leseschalter und Dateischalter die Methode `$lock->lock_read()` unterstützen. Abgesehen von Dateischaltern müssen alle anderen Schalterarten innerhalb des Elternprozesses erstellt werden, damit die durch `fork` erzeugten Tochterprozesse sich um den Schalter streiten können.


### lock()

Schloss Operation. Wenn ein anderer Prozess den Schalter hält, wird hier blockiert, bis der Prozess, der den Schalter hält, den Schalter mit `$lock->unlock()` freisetzt.

```php
Swoole\Lock->lock(): bool
```


### trylock()

Schloss Operation. Im Gegensatz zur `lock` Methode blockiert `trylock()` nicht und kehrt sofort zurück.

```php
Swoole\Lock->trylock(): bool
```

  * **Rückgabewert**

    * Schließen Sie erfolgreich mit `true` zurück, dann können Sie den gemeinsamen Variablen进行修改
    * Schließen Sie mit `false` zurück, was bedeutet, dass ein anderer Prozess den Schalter hält

!> Der Semaphore `SWOOLE_SEM` hat keine `trylock` Methode


### unlock()

Schloss freischalten.

```php
Swoole\Lock->unlock(): bool
```


### lock_read()

Nur-Leseschalter.

```php
Swoole\Lock->lock_read(): bool
```

* Während ein Leseschalter gehalten wird, können andere Prozesse weiterhin einen Leseschalter erhalten und Lesungen durchführen;
* Aber man kann `$lock->lock()` oder `$lock->trylock()` nicht aufrufen, diese beiden Methoden sind für exklusiven Schalter reserved, und während eines exklusiven Schalters wird von anderen Prozessen keine weitere Schalter Operation, einschließlich Leseschalter, durchgeführt;
* Wenn ein anderer Prozess einen exklusiven Schalter erhält (mit `$lock->lock()`/`$lock->trylock()`), wird `$lock->lock_read()` blockiert, bis der Prozess, der den exklusiven Schalter hält, den Schalter freisetzt.

!> Nur Schalter der Typen `SWOOLE_RWLOCK` und `SWOOLE_FILELOCK` unterstützen nur-Leseschalter


### trylock_read()

Schloss. Diese Methode ist identisch mit `$lock_read()`, aber nicht blockierend.

```php
Swoole\Lock->trylock_read(): bool
```

!> Die Anrufung wird sofort zurückkehren, und es muss überprüft werden, ob ein Schalter erhalten wurde.

### lockwait()

Schloss Operation. Die Funktion ist identisch mit der `$lock()` Methode, aber `$lockwait()` kann eine Timeoutzeit festlegen.

```php
Swoole\Lock->lockwait(float $timeout = 1.0): bool
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion**：Gibt die Timeoutzeit an
      * **Einheit der Werte**：Sekunden【Unterstützt浮点数, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：`1`
      * **Andere Werte**：Nicht vorhanden

  * **Rückgabewert**

    * Wenn der Schalter innerhalb der festgelegten Zeit nicht erhalten wird, wird `false` zurückgegeben
    * Schließen Sie erfolgreich mit `true` zurück

!> Nur Mutex-Schalter unterstützen die `lockwait` Funktion
