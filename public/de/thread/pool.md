# Threadpool

Threadpool, der die Ausführung mehrerer Arbeits-Threads aufrechterhalten kann, erstellen, neu starten und Schließen von Unter-Threads automatisch.


## Methoden


### __construct()

Konstruktor.

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **Parameter** 
  * `string $workerThreadClass`: Die Klasse, die die Arbeits-Threads ausführt
  * `int $worker_num`: Die Anzahl der Arbeits-Threads festlegen



### withArguments()

Legt die Parameter für die Arbeits-Threads fest, die in der Methode `run($args)` zugänglich sind.

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```



### withAutoloader()

Lädt das `autoload`-Datei

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **Parameter** 
  * `string $autoloader`: Der Pfad zur `PHP`-Datei des `autoload`-Funktors


> Wenn `Composer` verwendet wird, wird der `vendor/autoload.php` automatisch erkannt und in den Arbeitsprozessen geladen, sodass man ihn nicht manuell angeben muss


### withClassDefinitionFile()

Legt die Definition der Arbeits-Threadklasse fest, **dieses File darf nur `namespace`, `use` und `class definition`-Code enthalten, keine ausführbaren Code-Snippets**.

Die Arbeits-Threadklasse muss von der `Swoole\Thread\Runnable`-Basisklasse erben und die `run(array $args)`-Methode implementieren.

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **Parameter** 
  * `string $classFile`: Der Pfad zur `PHP`-Datei der Arbeits-Threadklasse

Wenn die Arbeits-Threadklasse im `autoload`-Verzeichnis liegt, ist dies nicht erforderlich


### start()

Startet alle Arbeits-Threads

```php
Swoole\Thread\Pool::start(): void;
```



### shutdown()

Schließt den Threadpool

```php
Swoole\Thread\Pool::shutdown(): void;
```


## Beispiel
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```


## Thread\Runnable

Die Arbeits-Threadklasse muss diese Klasse erben.


### run(array $args)

Diese Methode muss überschrieben werden, `$args` sind die Parameter, die der Threadpool-Objekt mit der `withArguments()`-Methode übergeben hat.


### shutdown()

Schließt den Threadpool


### $id 
Die Nummer des aktuellen Threads, der Bereich ist `0~(Gesamtzahl der Threads-1)`. Wenn ein Thread neu gestartet wird, ist die Nummer des neuen Nachfolgethreads identisch mit der des alten Threads.


### Beispiel

```php
use Swoole\Thread\Runnable;

class TestThread extends Runnable
{
    public function run($uuid, $map): void
    {
        $map->incr('thread', 1);

        for ($i = 0; $i < 5; $i++) {
            usleep(10000);
            $map->incr('sleep');
        }

        if ($map['sleep'] > 50) {
            $this->shutdown();
        }
    }
}
```
