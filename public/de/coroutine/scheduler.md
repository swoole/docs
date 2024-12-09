# Coroutine\Scheduler

?> Alle [Coroutinen](/coroutine) müssen innerhalb des [Coroutine-Containers](/coroutine/coroutine?id=create) [erstellt](/coroutine/coroutine?id=create) werden. Bei der Ausführung des Swoole-Programms wird in der Regel automatisch ein Coroutine-Container erstellt. Es gibt drei Möglichkeiten, ein Swoole-Programm zu starten:

   - Wenn Sie die [Asynchrone Stil](/server/init)-Serverprogrammstartmethode [start](/server/methods?id=start) aufrufen, wird in der Ereignis回调funktion ein Coroutine-Container erstellt. Referenz [enable_coroutine](/server/setting?id=enable_coroutine).
   - Wenn Sie die [Process](/process/process)- und [Process\Pool](/process/process_pool)-Prozessverwaltungss 模块startmethoden [start](/process/process_pool?id=start) von Swoole aufrufen, wird beim Start eines Prozesses ein Coroutine-Container erstellt. Referenz zu den `enable_coroutine` Parametern in den Konstruktoren dieser beiden Module.
   - Andere direkte Coroutine-Methoden zum Programmstart sind nicht möglich, es muss zuerst ein Coroutine-Container erstellt werden (Funktion `Coroutine\run()`, die als Hauptfunktion von Java oder C verstanden werden kann), zum Beispiel:

* **Ein voll Coroutine-HTTP-Dienst starten**

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
echo 1;//Wird nicht ausgeführt
```

* **Zwei Coroutinen hinzufügen und gleichzeitig etwas tun**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function() {
        var_dump(file_get_contents("http://www.xinhuanet.com/"));
    });

    Coroutine::create(function() {
        Coroutine::sleep(1);
        echo "done\n";
    });
});
echo 1;//Kann ausgeführt werden
```

!> In Swoole Version `v4.4+` verfügbar.

!> kann nicht in `Coroutine\run()` eingebettet werden.  
Wenn die Logik in `Coroutine\run()` nach unbearbeiteten Ereignissen fortgesetzt wird, nachdem `Coroutine\run()` beendet wurde, wird der nachfolgende Code nicht ausgeführt. Im Gegensatz dazu wird der Code weiter ausgeführt, wenn es keine Ereignisse mehr gibt, und es ist möglich, erneut `Coroutine\run()` aufzurufen.

Die obige `Coroutine\run()`-Funktion ist tatsächlich eine Encapsulation der `Swoole\Coroutine\Scheduler` Klasse (Klasse für Coroutine-Scheduler). Wer die Details verstehen möchte, kann die Methoden der `Swoole\Coroutine\Scheduler` betrachten:


### set()

?> **Stellen Sie Coroutine-Laufzeitparameter fest.** 

?> Ist ein Synonym für die `Coroutine::set`-Methode. Bitte beziehen Sie sich auf die [Coroutine::set](/coroutine/coroutine?id=set)-Dokumentation

```php
Swoole\Coroutine\Scheduler->set(array $options): bool
```

  * **Beispiel**

```php
$sch = new Swoole\Coroutine\Scheduler;
$sch->set(['max_coroutine' => 100]);
```


### getOptions()

?> **Holen Sie sich die festgelegten Coroutine-Laufzeitparameter.** Swoole Version >= `v4.6.0` verfügbar

?> Ist ein Synonym für die `Coroutine::getOptions`-Methode. Bitte beziehen Sie sich auf die [Coroutine::getOptions](/coroutine/coroutine?id=getoptions)-Dokumentation

```php
Swoole\Coroutine\Scheduler->getOptions(): null|array
```


### add()

?> **Fügen Sie Aufgaben hinzu.** 

```php
Swoole\Coroutine\Scheduler->add(callable $fn, ... $args): bool
```

  * **Parameter** 

    * **`callable $fn`**
      * **Funktion**: Rückruffunktion
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`... $args`**
      * **Funktion**: Optionaler Parameter, der an die Coroutine übergeben wird
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Beispiel**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;
$scheduler->add(function ($a, $b) {
    Coroutine::sleep(1);
    echo assert($a == 'hello') . PHP_EOL;
    echo assert($b == 12345) . PHP_EOL;
    echo "Done.\n";
}, "hello", 12345);

$scheduler->start();
```
  
  * **Hinweis**

    !> Im Gegensatz zur `go`-Funktion werden hier hinzugefügte Coroutinen nicht sofort ausgeführt, sondern warten darauf, dass die `start`-Methode aufgerufen wird, um gemeinsam zu starten und auszuführen. Wenn im Programm nur Coroutinen hinzugefügt wurden und nicht mit `start` gestartet wurde, wird die Coroutine-Funktion `$fn` nicht ausgeführt.


### parallel()

?> **Fügen Sie parallele Aufgaben hinzu.** 

?> Im Gegensatz zur `add`-Methode wird die `parallel`-Methode parallele Coroutinen erstellen. Bei der Ausführung werden `$num` Coroutinen von `$fn` gleichzeitig gestartet und parallel ausgeführt.

```php
Swoole\Coroutine\Scheduler->parallel(int $num, callable $fn, ... $args): bool
```

  * **Parameter** 

    * **`int $num`**
      * **Funktion**: Anzahl der zu startenden Coroutinen
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`callable $fn`**
      * **Funktion**: Rückruffunktion
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`... $args`**
      * **Funktion**: Optionaler Parameter, der an die Coroutine übergeben wird
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Beispiel**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;

$scheduler->parallel(10, function ($t, $n) {
    Coroutine::sleep($t);
    echo "Co ".Coroutine::getCid()."\n";
}, 0.05, 'A');

$scheduler->start();
```

### start()

?> **Starten Sie das Programm.** 

?> Erfahren Sie alle durch `add` und `parallel` hinzugefügten Coroutine-Arbeitsaufgaben und führen Sie sie aus.

```php
Swoole\Coroutine\Scheduler->start(): bool
```

  * **Rückgabewert**

    * Wenn der Start erfolgreich ist und alle hinzugefügten Aufgaben ausgeführt werden, wird `start` mit `true` zurückgeben, wenn alle Coroutinen beendet sind
    * Wenn der Start fehlschlägt, wird `false` zurückgegeben, möglicherweise weil bereits gestartet wurde oder ein anderer Scheduler bereits existiert und ein weiterer Scheduler nicht mehr erstellt werden kann
