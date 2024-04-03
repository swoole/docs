# Coroutine\Scheduler

All [coroutines](/coroutine) must be [created](/coroutine/coroutine?id=create) inside a `coroutine container`. In most cases, when a `Swoole` program starts, it will automatically create a `coroutine container`. There are three ways to start a program with `Swoole`:

   - Call the [start](/server/methods?id=start) method of a server program in an asynchronous style. This method of starting will create a `coroutine container` in the event callback, refer to [enable_coroutine](/server/setting?id=enable_coroutine).
   - Call the `start` method of the two process management modules provided by `Swoole`, [Process](/process/process) and [Process\Pool](/process/process_pool). This method of starting will create a `coroutine container` when the process starts, referring to the `enable_coroutine` parameter in the constructors of these two modules.
   - Start the program directly by writing coroutines. In this case, you need to create a coroutine container first (using the `Coroutine\run()` function, which can be understood as the `main` function in languages like Java or C), for example:

* **Start a full coroutine `HTTP` service**

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
echo 1;//won't be executed
```

* **Add 2 concurrent coroutines to do something**

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
echo 1;//will be executed
```

!> Available in `Swoole v4.4+`.

!> Nesting `Coroutine\run()` is not allowed.  
If there are unhandled events after `Coroutine\run()`, the subsequent code will not be executed. Conversely, if there are no events, the execution will continue going downwards, allowing another `Coroutine\run()`.

The `Coroutine\run()` function mentioned above is actually a wrapper for the `Swoole\Coroutine\Scheduler` class (coroutine scheduler class). For those who are interested in details, you can refer to the methods of `Swoole\Coroutine\Scheduler`.
### set()

?> **Set the parameters for coroutine runtime.**

?> It is an alias of the `Coroutine::set` method. Please refer to the [Coroutine::set](/coroutine/coroutine?id=set) documentation

```php
Swoole\Coroutine\Scheduler->set(array $options): bool
```

  * **Example**

```php
$sch = new Swoole\Coroutine\Scheduler;
$sch->set(['max_coroutine' => 100]);
```
### getOptions()

?> **Get the runtime options set for coroutines.** Available since Swoole version `v4.6.0`

?> This is an alias of the `Coroutine::getOptions` method. Please refer to the [Coroutine::getOptions](/coroutine/coroutine?id=getoptions) documentation

```php
Swoole\Coroutine\Scheduler->getOptions(): null|array
```
### add()

?> **Add a task.**

```php
Swoole\Coroutine\Scheduler->add(callable $fn, ... $args): bool
```

  * **Parameters**

    * **`callable $fn`**
      * **Description**: Callback function
      * **Default value**: None
      * **Other values**: None

    * **`... $args`**
      * **Description**: Optional parameters to be passed to the coroutine
      * **Default value**: None
      * **Other values**: None

  * **Example**

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
  
  * **Note**

    !> Unlike the `go` function, the coroutine added here will not be executed immediately. Instead, they wait to be started together and executed when the `start` method is called. If only coroutines are added in the program and `start` is not called to initiate execution, the coroutine function `$fn` will not be executed.
### parallel()

?> **Add parallel tasks.**

?> Different from the `add` method, the `parallel` method will create parallel coroutines. It will start `$num` number of `$fn` coroutines at the same time when `start` is called, executing them in parallel.

```php
Swoole\Coroutine\Scheduler->parallel(int $num, callable $fn, ... $args): bool
```

  * **Parameters**

    * **`int $num`**
      * **Function**: Number of coroutines to start
      * **Default Value**: None
      * **Other Values**: None

    * **`callable $fn`**
      * **Function**: Callback function
      * **Default Value**: None
      * **Other Values**: None

    * **`... $args`**
      * **Function**: Optional parameters passed to the coroutine
      * **Default Value**: None
      * **Other Values**: None

  * **Example**

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

?> **Start the program.**

?> Traverses the coroutine tasks added by the `add` and `parallel` methods, and executes them.

```php
Swoole\Coroutine\Scheduler->start(): bool
```

  - **Return Value**
    - If started successfully, all added tasks will be executed, and when all coroutines exit, `start` will return `true`.
    - If start fails, returns `false`, which may be because it has already started or another scheduler has been created and cannot be created again.
