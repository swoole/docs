# Coroutine-API

> Es wird empfohlen, zuerst den [Übersicht](/coroutine) zu betrachten, um die grundlegenden Konzepte der Coroutinen zu verstehen, bevor Sie diesen Abschnitt lesen.


## Methoden


### set()

Coroutine-Einstellung, um Coroutine-相关的 Optionen zu setzen.

```php
Swoole\Coroutine::set(array $options);
```


Parameter | Stabil seit dieser Version | Funktion 
---|---|---
max_coroutine | - | Legt die maximale Anzahl von Coroutinen global fest, über die Grenze hinaus kann das untere Level keine neuen Coroutinen mehr erstellen, unter Server wird es durch [server->max_coroutine](/server/setting?id=max_coroutine) überschattet.
stack_size/c_stack_size | - | Legt die Größe der anfänglichen C-Stack-Speicher für eine einzelne Coroutine fest,默认 ist 2M
log_level | v4.0.0 |loglevel [siehe](/consts?id=loglevel)
trace_flags | v4.0.0 |追踪标签 [siehe](/consts?id=tracking_tags)
socket_connect_timeout | v4.2.10 | Verbindungstimeout, **siehe[Client-Timeout-Regeln](/coroutine_client/init?id=timeout_rules)**
socket_read_timeout | v4.3.0 | Lesetimeout, **siehe[Client-Timeout-Regeln](/coroutine_client/init?id=timeout_rules)**
socket_write_timeout | v4.3.0 | writetimeout, **siehe[Client-Timeout-Regeln](/coroutine_client/init?id=timeout_rules)**
socket_dns_timeout | v4.4.0 | Domainname-Resolution-Timeout, **siehe[Client-Timeout-Regeln](/coroutine_client/init?id=timeout_rules)**
socket_timeout | v4.2.10 | Senden/Empfangen-Timeout, **siehe[Client-Timeout-Regeln](/coroutine_client/init?id=timeout_rules)**
dns_cache_expire | v4.2.11 | Legt die Gültigkeitsdauer des swoole dns-Caches fest, in Sekunden,默认 60 Sekunden
dns_cache_capacity | v4.2.11 | Legt die Kapazität des swoole dns-Caches fest,默认 1000
hook_flags | v4.4.0 | Konfigurierung der Hook-Bereich für den一键协程化, siehe[一键协程化](/runtime)
enable_preemptive_scheduler | v4.4.0 | Legt das启用了协程抢占式调度 fest, die maximale Ausführungszeit einer Coroutine beträgt 10ms, es wird die[ini-Konfiguration](/other/config) überschattet.
dns_server | v4.5.0 | Legt den DNS-Server für die DNS-Abfrage fest,默认 "8.8.8.8"
exit_condition | v4.5.0 | Geben Sie einen`callable`zurück, der ein boolesches Ergebnis zurückgibt, Sie können das Ausgangskriterium für den Reactor selbst definieren. Zum Beispiel: Ich möchte, dass das Programm nur dann beendet wird, wenn die Anzahl der Coroutinen gleich 0 ist, dann können Sie schreiben `Co::set(['exit_condition' => function () {return Co::stats()['coroutine_num'] === 0;}]);`
enable_deadlock_check | v4.6.0 | Legt fest, ob die Überwachung von Coroutine-Deadlocks eingeschaltet ist, standardmäßig eingeschaltet
deadlock_check_disable_trace | v4.6.0 | Legt fest, ob die Ausgabe von Stack-Frames für die Coroutine-Deadlock-Überwachung deaktiviert ist
deadlock_check_limit | v4.6.0 | Legt die maximale Anzahl der Ausgaben für die Coroutine-Deadlock-Überwachung fest
deadlock_check_depth | v4.6.0 | Legt die Anzahl der zurückgegebenen Stack-Frames für die Coroutine-Deadlock-Überwachung fest
max_concurrency | v4.8.2 | Maximale Anzahl der gleichzeitigen Anfragen


### getOptions()

Holt die gesetzten Coroutine-相关的 Optionen ab.

!> Swoole-Version >= `v4.6.0` verfügbar

```php
Swoole\Coroutine::getOptions(): null|array;
```


### create()

Erstellt eine neue Coroutine und führt sie sofort aus.

```php
Swoole\Coroutine::create(callable $function, ...$args): int|false
go(callable $function, ...$args): int|false // Referenz zur php.ini-Einstellung use_shortname
```

* **Parameter**

    * **`callable $function`**
      * **Funktion**: Der von der Coroutine auszuführende Code muss ein `callable` sein, die Gesamtzahl der Coroutinen, die vom System erstellt werden können, ist durch die[server->max_coroutine](/server/setting?id=max_coroutine)-Einstellung begrenzt
      * **Standardwert**: Keine
      * **Andere Werte**: Keine

* **Rückgabewert**

    * Erstellung失败返回`false`
    * Erstellung成功返回Coroutine的`ID`

!> Da das untere Level zuerst den Code der child-Coroutine ausführt, wird `Coroutine::create` nur zurückgegeben, wenn die child-Coroutine aufgehängt ist, und der Code der aktuellen Coroutine wird weiter ausgeführt.

  * **Ausführungsreihenfolge**

    In einer Coroutine wird mit `go` eine neue Coroutine嵌套 erstellt. Da Swoole Coroutines ein einzelnes Prozess-Einzelthread-Modell sind:

    * Die mit `go` erstellten child-Coroutines werden zuerst ausgeführt, wenn die child-Coroutine fertig ist oder aufgehängt ist, wird der Code der parent-Coroutine weiter ausgeführt
    * Wenn die child-Coroutine aufgehängt ist und die parent-Coroutine beendet wird, beeinflusst dies nicht die Ausführung der child-Coroutine

    ```php
    \Co\run(function() {
        go(function () {
            Co::sleep(3.0);
            go(function () {
                Co::sleep(2.0);
                echo "co[3] end\n";
            });
            echo "co[2] end\n";
        });

        Co::sleep(1.0);
        echo "co[1] end\n";
    });
    ```

* **Coroutine-Overhead**

  Jede Coroutine ist unabhängig voneinander und benötigt einen eigenen Speicherraum (Stack-Speicher). In der `PHP-7.2`version wird von der unteren Ebene standardmäßig ein `8K`-Stack zugeteilt, um die Variablen der Coroutine zu speichern, die Größe von `zval` beträgt `16字节`, daher kann ein `8K`-Stack bis zu `512` Variablen aufbewahren. Wenn der Coroutine-Stack-Speicher mehr als `8K` einnimmt, wird der `ZendVM` automatisch erweitert.

  Wenn eine Coroutine beendet wird, wird der angeforderte `Stack`-Speicher freigesetzt.

  * `PHP-7.1`, `PHP-7.0` bieten standardmäßig einen `256K`-Stack-Speicher an
  * Sie können `Co::set(['stack_size' => 4096])` nennen, um den Standard-Stack-Speicher zu ändern



### defer()

`defer` wird für die Freisetzung von Ressourcen verwendet und wird **vor dem Schließen der Coroutine** (d.h. wenn die Coroutine-Funktion fertig ist) aufgerufen, selbst wenn ein Ausnahme geworfen wird, wird die registrierte `defer` ausgegeben.

!> Swoole-Version >= 4.2.9

```php
Swoole\Coroutine::defer(callable $function);
defer(callable $function); // Kurzname-API
```

!> Zu beachten ist, dass die Reihenfolge der Aufrufe rückwärts ist (spät in früh aus), das heißt, was später registriert wurde, wird zuerst ausgeführt, rückwärts entspricht der richtigen Logik für die Ressourcenfreisetzung, da die später angeforderten Ressourcen möglicherweise auf den zuvor angeforderten Ressourcen basieren, zum Beispiel, wenn man zuerst die zuerst angeforderten Ressourcen freisetzt, könnten die später angeforderten Ressourcen schwer zu freisetzen sein.

  * **Beispiel**

```php
go(function () {
    defer(function () use ($db) {
        $db->close();
    });
});
```


### exists()

Bestimmt, ob eine spezifische Coroutine existiert.

```php
Swoole\Coroutine::exists(int $cid = 0): bool
```

!> Swoole-Version >= v4.3.0

  * **Beispiel**

```php
\Co\run(function () {
    go(function () {
        go(function () {
            Co::sleep(0.001);
            var_dump(Co::exists(Co::getPcid())); // 1: true
        });
        go(function () {
            Co::sleep(0.003);
            var_dump(Co::exists(Co::getPcid())); // 3: false
        });
        Co::sleep(0.002);
        var_dump(Co::exists(Co::getPcid())); // 2: false
    });
});
```


### getCid()

Holt die einzigartige `ID` der aktuellen Coroutine ab, deren Alias `getuid` ist, ein einzigartiger positiver Integer innerhalb eines Prozesses.

```php
Swoole\Coroutine::getCid(): int
```

* **Rückgabewert**

    * Erfolgreich gibt es die `ID` der aktuellen Coroutine zurück
    * Wenn man sich nicht in einer Coroutine-Umwelt befindet, dann wird `-1` zurückgegeben

### getPcid()

Erhalten Sie die Parent-ID der aktuellen Coroutine.

```php
Swoole\Coroutine::getPcid([$cid]): int
```

> Swoole-Version >= v4.3.0

* **Parameter**

    * **`int $cid`**
      * **Funktion**: Coroutine cid, Parameter default, kann die `id` einer bestimmten Coroutine eingegeben werden, um ihre Parent-`id` zu erhalten
      * **Standardwert**: Aktuelle Coroutine
      * **Andere Werte**: Keine

  * **Beispiel**

```php
var_dump(Co::getPcid());
\Co\run(function () {
    var_dump(Co::getPcid());
    go(function () {
        var_dump(Co::getPcid());
        go(function () {
            var_dump(Co::getPcid());
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
        });
        var_dump(Co::getPcid());
    });
    var_dump(Co::getPcid());
});
var_dump(Co::getPcid());

// --Erwartung--

// bool(false)
// int(-1)
// int(1)
// int(2)
// int(3)
// int(3)
// int(3)
// int(1)
// int(-1)
// bool(false)
```

> Bei nicht geschachtelten Coroutine-Anrufen von `getPcid` wird `-1` zurückgegeben (erstellt aus Nicht-Coroutine-Raum)  
Ein Aufruf von `getPcid` innerhalb einer Nicht-Coroutine wird `false` zurückgeben (keine übergeordnete Coroutine)  
`0` als reservierte `id`, wird nicht in den Rückgabewerten erscheinen

> Zwischen Coroutinen besteht keine wesentliche ständige Eltern-Kind-Beziehung, Coroutinen sind voneinander isoliert und funktionieren unabhängig, diese `Pcid` kann als die `id` der Coroutine verstanden werden, die die aktuelle Coroutine 创建 hat

  * **Nutzung**

    * **Verknüpfen mehrerer Coroutine-Anrufstacke**

```php
\Co\run(function () {
    go(function () {
        $ptrace = Co::getBackTrace(Co::getPcid());
        // balababala
        var_dump(array_merge($ptrace, Co::getBackTrace(Co::getCid())));
    });
});
```


### getContext()

Holen Sie sich das Kontextobjekt der aktuellen Coroutine.

```php
Swoole\Coroutine::getContext([int $cid = 0]): Swoole\Coroutine\Context
```

> Swoole-Version >= v4.3.0

* **Parameter**

    * **`int $cid`**
      * **Funktion**: Coroutine `CID`, optionaler Parameter
      * **Standardwert**: Aktuelle Coroutine `CID`
      * **Andere Werte**: Keine

  * **Wirkung**

    * Kontext wird automatisch gereinigt, wenn eine Coroutine beendet ist (wenn es keine anderen Coroutinen oder globale Variablen gibt, die darauf verweisen)
    * Keine Kosten für `defer` Registrierung und Aufruf (Keine Notwendigkeit, eine Cleanup-Methode zu registrieren oder eine Funktion zum Cleanup aufzurufen)
    * Keine Kosten für Hash-Berechnung des Kontexts, der auf PHP-Arrays basiert (ist vorteilhaft, wenn es eine große Anzahl von Coroutinen gibt)
    * `Co\Context` verwendet `ArrayObject`, um verschiedene Speichungsbedürfnisse zu erfüllen (es ist sowohl ein Objekt als auch operierbar wie ein Array)

  * **Beispiel**

```php
function func(callable $fn, ...$args)
{
    go(function () use ($fn, $args) {
        $fn(...$args);
        echo 'Coroutine#' . Co::getCid() . ' exit' . PHP_EOL;
    });
}

/**
* Kompatibilität für niedrigere Versionen
* @param object|Resource $object
* @return int
*/
function php_object_id($object)
{
    static $id = 0;
    static $map = [];
    $hash = spl_object_hash($object);
    return $map[$hash] ?? ($map[$hash] = ++$id);
}

class Resource
{
    public function __construct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' constructed' . PHP_EOL;
    }

    public function __destruct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' destructed' . PHP_EOL;
    }
}

$context = new Co\Context();
assert($context instanceof ArrayObject);
assert(Co::getContext() === null);
func(function () {
    $context = Co::getContext();
    assert($context instanceof Co\Context);
    $context['resource1'] = new Resource;
    $context->resource2 = new Resource;
    func(function () {
        Co::getContext()['resource3'] = new Resource;
        Co::yield();
        Co::getContext()['resource3']->resource4 = new Resource;
        Co::getContext()->resource5 = new Resource;
    });
});
Co::resume(2);

Swoole\Event::wait();

// --Erwartung--
// Resource#1 constructed
// Resource#2 constructed
// Resource#3 constructed
// Coroutine#1 exit
// Resource#2 destructed
// Resource#1 destructed
// Resource#4 constructed
// Resource#5 constructed
// Coroutine#2 exit
// Resource#5 destructed
// Resource#3 destructed
// Resource#4 destructed
```


### yield()

Heben Sie manuell die Ausführung der aktuellen Coroutine auf. Anstatt auf IO basierend auf der [Coroutine-Zeitplanung](/coroutine?id=coroutinetimeplanung).

Diese Methode hat einen anderen Namen: `Coroutine::suspend()`

> Muss mit der `Coroutine::resume()`-Methode配对 verwendet werden. Nachdem die Coroutine `yield` wurde, muss sie von einer anderen externen Coroutine `resume` werden, sonst wird eine Coroutine-Leake verursacht, und die aufgehängte Coroutine wird nie ausgeführt.

```php
Swoole\Coroutine::yield();
```

  * **Beispiel**

```php
$cid = go(function () {
    echo "co 1 start\n";
    Co::yield();
    echo "co 1 end\n";
});

go(function () use ($cid) {
    echo "co 2 start\n";
    Co::sleep(0.5);
    Co::resume($cid);
    echo "co 2 end\n";
});
Swoole\Event::wait();
```


### resume()

Manually resume a coroutine to allow it to continue running, not based on IO [Coroutine Scheduling](/coroutine?id=coroutinescheduling).

!> When the current coroutine is in a suspended state, another coroutine can use `resume` to awaken the current coroutine again

```php
Swoole\Coroutine::resume(int $coroutineId);
```

* **Parameters**

    * **`int $coroutineId`**
      * **Function**: The ID of the coroutine to resume
      * **Default value**: None
      * **Other values**: None

  * **Example**

```php
$id = go(function(){
    $id = Co::getuid();
    echo "start coro $id\n";
    Co::suspend();
    echo "resume coro $id @1\n";
    Co::suspend();
    echo "resume coro $id @2\n";
});
echo "start to resume $id @1\n";
Co::resume($id);
echo "start to resume $id @2\n";
Co::resume($id);
echo "main\n";
Swoole\Event::wait();

// --EXPECT--
// start coro 1
// start to resume 1 @1
// resume coro 1 @1
// start to resume 1 @2
// resume coro 1 @2
// main
```


### list()

Blättern Sie alle Coroutinen im aktuellen Prozess durch.

```php
Swoole\Coroutine::list(): Swoole\Coroutine\Iterator
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> In Versionen unter `v4.3.0` muss die Methode `listCoroutines` verwendet werden, in neueren Versionen wurde der Name der Methode verkürzt und `listCoroutines` als Synonym festgelegt. `list` ist in Versionen `v4.1.0` oder höher verfügbar.

* **Return Value**

    * Gibt einen Iterator zurück, der mit `foreach` durchlaufen werden kann oder in ein Array umgewandelt werden kann

```php
$coros = Swoole\Coroutine::listCoroutines();
foreach($coros as $cid)
{
    var_dump(Swoole\Coroutine::getBackTrace($cid));
}
```


### stats()

Holen Sie sich die Status der Coroutine.

```php
Swoole\Coroutine::stats(): array
```

* **Return Value**


key | Function
---|---
event_num | Current number of reactor events
signal_listener_num | Current number of signal listeners
aio_task_num | Number of asynchronous IO tasks (here aio refers to file IO or dns, not including other network IO, the same below)
aio_worker_num | Number of asynchronous IO worker threads
c_stack_size | C stack size for each coroutine
coroutine_num | Current number of running coroutines
coroutine_peak_num | Peak number of running coroutines
coroutine_last_cid | ID of the last created coroutine

  * **Example**

```php
var_dump(Swoole\Coroutine::stats());

array(1) {
  ["c_stack_size"]=>
  int(2097152)
  ["coroutine_num"]=>
  int(132)
  ["coroutine_peak_num"]=>
  int(2)
}
```
### getBackTrace()

Erhalten Sie die Callstack der Coroutine-Funktion.

```php
Swoole\Coroutine::getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
```

!> Swoole-Version >= v4.1.0

* **Parameter**

    * **`int $cid`**
      * **Funktion**：Die `CID` der Coroutine
      * **Standardwert**：Die aktuelle Coroutine `CID`
      * **Andere Werte**：Nicht vorhanden

    * **`int $options`**
      * **Funktion**：Einstellungen festlegen
      * **Standardwert**：`DEBUG_BACKTRACE_PROVIDE_OBJECT` 【Ob die Indizes für `object` gefüllt werden sollen】
      * **Andere Werte**：`DEBUG_BACKTRACE_IGNORE_ARGS` 【Ob die Indizes für args ignoriert werden sollen, einschließlich aller Parameter von function/method, was den Speicherverbrauch senken kann】

    * **`int limit`**
      * **Funktion**：Die Anzahl der zurückzugebenden Stackframes beschränken
      * **Standardwert**：`0`
      * **Andere Werte**：Nicht vorhanden

* **Rückgabewert**

    * Wenn die angegebene Coroutine nicht existiert, wird `false` zurückgegeben
    * Erfolgreich zurückgegeben ein Array, Format ist dasselbe wie der Rückgabewert der [debug_backtrace](https://www.php.net/manual/zh/function.debug-backtrace.php)-Funktion

  * **Beispiel**

```php
function test1() {
    test2();
}

function test2() {
    while(true) {
        Co::sleep(10);
        echo __FUNCTION__." \n";
    }
}
\Co\run(function () {
    $cid = go(function () {
        test1();
    });

    go(function () use ($cid) {
        while(true) {
            echo "BackTrace[$cid]:\n-----------------------------------------------\n";
            //Return Array, muss selbst formatiert werden
            var_dump(Co::getBackTrace($cid))."\n";
            Co::sleep(3);
        }
    });
});
Swoole\Event::wait();
```


### printBackTrace()

Drucken Sie die Callstack der Coroutine-Funktion. Parameter und `getBackTrace` sind gleich.

!> Swoole-Version >= `v4.6.0` verfügbar

```php
Swoole\Coroutine::printBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0);
```


### getElapsed()

Holen Sie sich die Laufzeit der Coroutine, um sie für die Analyse, Statistik oder zur Identifizierung von Zombie-Coroutinen zu verwenden

!> Swoole-Version >= `v4.5.0` verfügbar

```php
Swoole\Coroutine::getElapsed([$cid]): int
```
* **Parameter**

    * **`int $cid`**
      * **Funktion**：Optionaler Parameter, die `CID` der Coroutine
      * **Standardwert**：Die aktuelle Coroutine `CID`
      * **Andere Werte**：Nicht vorhanden

* **Rückgabewert**

    * Die Laufzeit der Coroutine in Float-Werten, mit Millisekunden-Genauigkeit


### cancel()

Für die Beendigung einer bestimmten Coroutine verwendet, kann jedoch keine Beendigung für die aktuelle Coroutine durchführen

!> Swoole-Version >= `v4.7.0` verfügbar

```php
Swoole\Coroutine::cancel($cid): bool
```
* **Parameter**

    * **`int $cid`**
        * **Funktion**：Die `CID` der Coroutine
        * **Standardwert**：Nicht vorhanden
        * **Andere Werte**：Nicht vorhanden

* **Rückgabewert**

    * Erfolgreich true zurückgegeben, bei Misserfolg false
    * Bei Beendigung失败kann [swoole_last_error()](/functions?id=swoole_last_error) verwendet werden, um Fehlerinformationen zu erhalten


### isCanceled()

Um zu überprüfen, ob die aktuelle Operation manuell abgebrochen wurde

!> Swoole-Version >= `v4.7.0` verfügbar

```php
Swoole\Coroutine::isCanceled(): bool
```

* **Rückgabewert**

    * Bei manueller Beendigung eines normalen Endes true zurückgegeben, bei Misserfolg false

#### Beispiel

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

run(function () {
    $chan = new Coroutine\Channel(1);
    $cid = Coroutine::getCid();
    go(function () use ($cid) {
        System::sleep(0.002);
        assert(Coroutine::cancel($cid) === true);
    });

    assert($chan->push("hello world [1]", 100) === true);
    assert(Coroutine::isCanceled() === false);
    assert($chan->errCode === SWOOLE_CHANNEL_OK);

    assert($chan->push("hello world [2]", 100) === false);
    assert(Coroutine::isCanceled() === true);
    assert($chan->errCode === SWOOLE_CHANNEL_CANCELED);

    echo "Fertig\n";
});
```


### enableScheduler()

Temporär die Coroutine-Preemptive-调度er öffnen.

!> Swoole-Version >= `v4.4.0` verfügbar

```php
Swoole\Coroutine::enableScheduler();
```


### disableScheduler()

Temporär die Coroutine-Preemptive-调度er schließen.

!> Swoole-Version >= `v4.4.0` verfügbar

```php
Swoole\Coroutine::disableScheduler();
```


### getStackUsage()

Holen Sie sich den Speicherverbrauch der aktuellen PHP-Stack.

!> Swoole-Version >= `v4.8.0` verfügbar

```php
Swoole\Coroutine::getStackUsage([$cid]): int
```

* **Parameter**

    * **`int $cid`**
        * **Funktion**：Optionaler Parameter, die `CID` der Coroutine
        * **Standardwert**：Die aktuelle Coroutine `CID`
        * **Andere Werte**：Nicht vorhanden


### join()

Führen Sie mehrere Coroutinen gleichzeitig aus.

!> Swoole-Version >= `v4.8.0` verfügbar

```php
Swoole\Coroutine::join(array $cid_array, float $timeout = -1): bool
```

* **Parameter**

    * **`array $cid_array`**
        * **Funktion**：Array mit den `CID`s der zu ausführenden Coroutinen
        * **Standardwert**：Nicht vorhanden
        * **Andere Werte**：Nicht vorhanden

    * **`float $timeout`**
        * **Funktion**：Die Gesamtzeit für das Timeout, nach dem sofortige Rückkehr erfolgen soll. Aber laufende Coroutinen werden bis zum Ende ausgeführt, ohne abgebrochen zu werden
        * **Standardwert**：-1
        * **Andere Werte**：Nicht vorhanden

* **Rückgabewert**

    * Erfolgreich true zurückgegeben, bei Misserfolg false
    * Bei Beendigung失败kann [swoole_last_error()](/functions?id=swoole_last_error) verwendet werden, um Fehlerinformationen zu erhalten

* **Anwendungsexempel**

```php
use Swoole\Coroutine;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

run(function () {
    $status = Coroutine::join([
        go(function () use (&$result) {
            $result['baidu'] = strlen(file_get_contents('https://www.baidu.com/'));
        }),
        go(function () use (&$result) {
            $result['google'] = strlen(file_get_contents('https://www.google.com/'));
        })
    ], 1);
    var_dump($result, $status, swoole_strerror(swoole_last_error(), 9));
});
```


## Funktionen


### batch()

Führen Sie mehrere Coroutinen gleichzeitig aus und geben Sie die Rückkehrwerte dieser Coroutine-Methoden zurück.

!> Swoole-Version >= `v4.5.2` verfügbar

```php
Swoole\Coroutine\batch(array $tasks, float $timeout = -1): array
```

* **Parameter**

    * **`array $tasks`**
      * **Funktion**：Array mit den Methoden回调s, wenn ein `key` angegeben ist, werden auch die Rückkehrwerte durch diesen `key` angegeben
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`float $timeout`**
      * **Funktion**：Die Gesamtzeit für das Timeout, nach dem sofortige Rückkehr erfolgen soll. Aber laufende Coroutinen werden bis zum Ende ausgeführt, ohne abgebrochen zu werden
      * **Standardwert**：-1
      * **Andere Werte**：Nicht vorhanden

* **Rückgabewert**

    * Ein Array zurückgegeben, das die Rückkehrwerte der Callbacks enthält. Wenn im `$tasks`-Parameter ein `key` angegeben ist, werden auch die Rückkehrwerte durch diesen `key` angegeben

* **Anwendungsexempel**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\batch;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = batch([
        'file_put_contents' => function () {
            return file_put_contents(__DIR__ . '/greeter.txt', "Hello,Swoole.");
        },
        'gethostbyname' => function () {
            return gethostbyname('localhost');
        },
        'file_get_contents' => function () {
            return file_get_contents(__DIR__ . '/greeter.txt');
        },
        'sleep' => function () {
            sleep(1);
            return true; // NULL zurückgegeben, da das Timeout von 0,1 Sekunden überschritten wurde, wird sofort zurückgegeben. Aber laufende Coroutinen werden bis zum Ende ausgeführt, ohne abgebrochen zu werden.
        },
        'usleep' => function () {
            usleep(1000);
            return true;
        },
    ], 0.1);
    $use = microtime(true) - $use;
    echo "Verbrauch {$use}s, Ergebnis:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Verbrauch {$end_time}s, Fertig\n";
```
### parallel()

Führt mehrere Coroutinen gleichzeitig aus.

!> Swoole-Version >= `v4.5.3` verfügbar

```php
Swoole\Coroutine\parallel(int $n, callable $fn): void
```

* **Parameter**

    * **`int $n`**
      * **Funktion**: Legt die maximale Anzahl von Coroutinen auf `$n` fest
      * **Standardwert**: Keine
      * **Andere Werte**: Keine

    * **`callable $fn`**
      * **Funktion**: Die Callback-Funktion, die für jedes Element der Coroutine-Liste ausgeführt werden soll
      * **Standardwert**: Keine
      * **Andere Werte**: Keine

* **Beispiel**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\parallel;

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = [];
    parallel(2, function () use (&$results) {
        System::sleep(0.2);
        $results[] = System::gethostbyname('localhost');
    });
    $use = microtime(true) - $use;
    echo "Use {$use}s, Result:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Use {$end_time}s, Done\n";
```

### map()

Ähnlich wie [array_map](https://www.php.net/manual/zh/function.array-map.php), wird für jedes Element der Array eine Callback-Funktion angewendet.

!> Swoole-Version >= `v4.5.5` verfügbar

```php
Swoole\Coroutine\map(array $list, callable $fn, float $timeout = -1): array
```

* **Parameter**

    * **`array $list`**
      * **Funktion**: Die Array, auf das die `$fn`-Funktion angewendet wird
      * **Standardwert**: Keine
      * **Andere Werte**: Keine

    * **`callable $fn`**
      * **Funktion**: Die Callback-Funktion, die für jedes Element der `$list`-Array ausgeführt werden soll
      * **Standardwert**: Keine
      * **Andere Werte**: Keine

    * **`float $timeout`**
      * **Funktion**: Die Gesamtzeit, nach der sofort zurückgegeben wird. Coroutinen, die bereits laufen, werden jedoch bis zum Ende ausgeführt und nicht abgebrochen
      * **Standardwert**: -1
      * **Andere Werte**: Keine

* **Beispiel**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\map;

function fatorial(int $n): int
{
    return array_product(range($n, 1));
}

Coroutine\run(function () {
    $results = map([2, 3, 4], 'fatorial'); 
    print_r($results);
});
```

### deadlock_check()

Prüft auf Deadlocks in Coroutinen aus und gibt entsprechende Stack-Informationen aus;

Standardmäßig **eingeschaltet**, wird nach Beendigung des [EventLoop](learn?id=什么是eventloop) automatisch aufgerufen, wenn Deadlocks in Coroutinen bestehen;

Kann durch Einstellen von `enable_deadlock_check` in [Coroutine::set](/coroutine/coroutine?id=set) ausgeschaltet werden.

!> Swoole-Version >= `v4.6.0` verfügbar

```php
Swoole\Coroutine\deadlock_check();
```
