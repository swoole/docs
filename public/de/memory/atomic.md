# Prozess-/Thread-Freies Zählwerk Atomic

`Atomic` ist eine Klasse für atomische Zähloperationen, die von `Swoole` am unteren Level bereitgestellt wird und es ermöglicht, die atomische Erhöhung und Senkung ganzer Zahlen bequem durchzuführen.

* Mit gemeinsamen Speicher können Sie die Zählung zwischen verschiedenen Prozessen verändern
* Basierend auf den `CPU`-Atom-Befehlen von `gcc/clang`, ohne Lock
* In Serverprogrammen muss die Erstellung vor `Server->start` erfolgen, um sie im `Worker`-Prozess verwenden zu können
* Standardmäßig wird ein `32`-Bit-Unsigniert-Typ verwendet, wenn Sie einen `64`-Bit-Signiert-Integer benötigen, können Sie `Swoole\Atomic\Long` verwenden
* Im Mehr线程modus müssen Sie `Swoole\Thread\Atomic` und `Swoole\Thread\Atomic\Long` verwenden, abgesehen davon, dass die Namespaces unterschiedlich sind, ist ihre Schnittstelle mit `Swoole\Atomic` und `Swoole\Atomic\Long` vollständig identisch.

!> Bitte vermeiden Sie die Erstellung eines Zählers in Rückruffunktionen wie [onReceive](/server/events?id=onreceive), sonst wird der Speicher kontinuierlich wachsen und eine Speicherlecke verursachen.

!> Unterstützung für atomische Zählungen mit `64`-Bit-Signiert-Langintergern, Sie müssen `new Swoole\Atomic\Long` verwenden, um zu erstellen. `Atomic\Long` unterstützt keine `wait` und `wakeup` Methoden.


## Vollständiges Beispiel

```php
$atomic = new Swoole\Atomic();

$serv = new Swoole\Server('127.0.0.1', '9501');
$serv->set([
    'worker_num' => 1,
    'log_file' => '/dev/null'
]);
$serv->on("start", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStart", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStop", function ($serv) {
    echo "shutdown\n";
});
$serv->on("Receive", function () {
    
});
$serv->start();
```


## Methoden


### __construct()

Konstruktor. Erstellt ein Objekt für atomische Zählung.

```php
Swoole\Atomic::__construct(int $init_value = 0);
```

  * **Parameter** 

    * **`int $init_value`**
      * **Funktion**：Gibt die initialisierte Zahl an
      * **Standardwert**：`0`
      * **Andere Werte**：Nicht vorhanden


!> -`Atomic` kann nur mit `32`-Bit-Unsignierten整数 umgehen, unterstützt bis zu `4,2 Milliarden`, keine negativen Zahlen;  

-Um in einem `Server` einen Atomzähler zu verwenden, muss er vor `Server->start` erstellt werden;  
-Um in einem [Process](/process/process) einen Atomzähler zu verwenden, muss er vor `Process->start` erstellt werden.


### add()

Zählt um.

```php
Swoole\Atomic->add(int $add_value = 1): int
```

  * **Parameter** 

    * **`int $add_value`**
      * **Funktion**：Die Zahl, die hinzugefügt werden soll【muss ein positives Integer sein】
      * **Standardwert**：`1`
      * **Andere Werte**：Nicht vorhanden

  * **Rückgabewert**

    * Nach erfolgreicher Operation des `add`-Methoden wird der resultierende Wert zurückgegeben

!> Wenn der ursprüngliche Wert mit dem hinzugefügten Wert über `4,2 Milliarden` hinausgeht, wird überfließen, und die höheren Bits werden ignoriert.


### sub()

Zählt ab.

```php
Swoole\Atomic->sub(int $sub_value = 1): int
```

  * **Parameter** 

    * **`int $sub_value`**
      * **Funktion**：Die Zahl, die abgezogen werden soll【muss ein positives Integer sein】
      * **Standardwert**：`1`
      * **Andere Werte**：Nicht vorhanden

  * **Rückgabewert**

    * Nach erfolgreicher Operation des `sub`-Methoden wird der resultierende Wert zurückgegeben

!> Wenn der ursprüngliche Wert mit dem abgezogenen Wert unter `0` liegt, wird überfließen, und die höheren Bits werden ignoriert.


### get()

Holt den aktuellen Wert der Zählung.

```php
Swoole\Atomic->get(): int
```

  * **Rückgabewert**

    * Gibt den aktuellen Wert zurück


### set()

Setzt den aktuellen Wert auf die angegebene Zahl.

```php
Swoole\Atomic->set(int $value): void
```

  * **Parameter** 

    * **`int $value`**
      * **Funktion**：Gibt die zu setzt Zielzahl an
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden


### cmpset()

Wenn der aktuelle Wert gleich dem Parameter `1` ist, wird der aktuelle Wert auf den Parameter `2` gesetzt.   

```php
Swoole\Atomic->cmpset(int $cmp_value, int $set_value): bool
```

  * **Parameter** 

    * **`int $cmp_value`**
      * **Funktion**：Wenn der aktuelle Wert gleich `$cmp_value` ist, wird `true` zurückgegeben und der aktuelle Wert auf `$set_value` gesetzt, wenn nicht gleich, wird `false` zurückgegeben【muss ein Integer sein, der kleiner als `4,2 Milliarden` ist】
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`int $set_value`**
      * **Funktion**：Wenn der aktuelle Wert gleich `$cmp_value` ist, wird `true` zurückgegeben und der aktuelle Wert auf `$set_value` gesetzt, wenn nicht gleich, wird `false` zurückgegeben【muss ein Integer sein, der kleiner als `4,2 Milliarden` ist】
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden


### wait()

Setzt in einen wartenden Zustand.

!> Wenn der Wert des atomischen Zählers `0` ist, tritt der Prozess in einen wartenden Zustand ein. Ein anderer Prozess kann den Prozess durch Aufrufen von `wakeup` erneut wecken. Der Boden ist auf der `Linux Futex`-Funktion basierend, und mit dieser Funktion kann mit nur `4` Byte Speicher eine Wartungs-, Benachrichtigungs- und Schließfunktionalität implementiert werden. Unter Plattformen, die keine `Futex` unterstützen, wird der Boden durch einen Loop `usleep(1000)` simuliert implementiert.

```php
Swoole\Atomic->wait(float $timeout = 1.0): bool
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion**：Gibt die Timeoutzeit an【Wenn `-1` angegeben wird, bedeutet dies, dass es kein Timeout gibt und der Prozess wird weiterhin warten, bis er von einem anderen Prozess geweckt wird】
      * **Wertbereich**：Sekunden【Unterstützt浮点数, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：`1`
      * **Andere Werte**：Nicht vorhanden

  * **Rückgabewert** 

    * Wenn das Timeout erreicht wird, wird `false` zurückgegeben, der Fehlercode ist `EAGAIN`, und Sie können die Funktion `swoole_errno` verwenden, um den Fehlercode zu erhalten
    * Wenn erfolgreich, wird `true` zurückgegeben, was bedeutet, dass ein anderer Prozess den aktuellen Lock erfolgreich durch Aufrufen von `wakeup` geweckt hat

  * **Coroutine-Umwelt**

  `wait` wird den gesamten Prozess blockieren und nicht nur eine Coroutine, daher verwenden Sie bitte nicht `Atomic->wait()` in einer Coroutine-Umwelt, um einen Prozess hängen zu lassen.


!> -Wenn Sie die `wait/wakeup`-Funktion verwenden, kann der Wert des atomischen Zählers nur `0` oder `1` sein, sonst kann er nicht richtig verwendet werden;  
-Natürlich bedeutet ein Wert des atomischen Zählers von `1`, dass keine Prozesse im `wait` Zustand sind, der Ressource ist derzeit verfügbar. Die `wait` Funktion wird sofort `true` zurückgeben.

  * **Benutzungsbeispiel**

    ```php
    $n = new Swoole\Atomic;
    if (pcntl_fork() > 0) {
        echo "master start\n";
        $n->wait(1.5);
        echo "master end\n";
    } else {
        echo "child start\n";
        sleep(1);
        $n->wakeup();
        echo "child end\n";
    }
    ```

### wakeup()

Weckt andere Prozesse aus, die im wartenden Zustand sind.

```php
Swoole\Atomic->wakeup(int $n = 1): bool
```

  * **Parameter** 

    * **`int $n`**
      * **Funktion**：Die Anzahl der zu weckenden Prozesse
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

* Wenn der aktuelle Wert des atomischen Zählers `0` ist, bedeutet dies, dass kein Prozess im `wait` Zustand ist, `wakeup` wird sofort `true` zurückgeben;
* Wenn der aktuelle Wert des atomischen Zählers `1` ist, bedeutet dies, dass derzeit ein Prozess im `wait` Zustand ist, `wakeup` wird den wartenden Prozess wecken und `true` zurückgeben;
* Nachdem der geweckte Prozess zurückgekehrt ist, wird der Wert des atomischen Zählers auf `0` gesetzt, und nun kann `wakeup` erneut verwendet werden, um andere Prozesse im `wait` Zustand zu wecken.
