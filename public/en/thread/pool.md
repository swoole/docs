# Thread Pool

A thread pool can maintain the operation of multiple worker threads, automatically creating, restarting, and closing child threads.


## Methods


### __construct()

Constructor.

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **Parameters** 
  * `string $workerThreadClass`: The class that the worker threads run.
  * `int $worker_num`: Specifies the number of worker threads.



### withArguments()

Set the arguments for the worker threads, which can be obtained in the `run($args)` method.

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```



### withAutoloader()

Load the `autoload` file

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **Parameters** 
  * `string $autoloader`: The path to the `PHP` `autoload` file.


> If `Composer` is used, the underlying system can automatically deduce and load `vendor/autoload.php` in the worker processes without manual specification.


### withClassDefinitionFile()

Set the definition file for the worker thread class. **This file must only contain `namespace`, `use`, and `class definition` code, and must not contain executable code snippets.**

The worker thread class must inherit from the `Swoole\Thread\Runnable` base class and implement the `run(array $args)` method.

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **Parameters** 
  * `string $classFile`: The path to the `PHP` file defining the worker thread class.

If the worker thread class is in the `autoload` path, it is not necessary to set this.


### start()

Start all worker threads

```php
Swoole\Thread\Pool::start(): void;
```



### shutdown()

Close the thread pool

```php
Swoole\Thread\Pool::shutdown(): void;
```


## Example
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```


## Thread\Runnable

Worker thread classes must inherit from this class.


### run(array $args)

This method must be overridden. `$args` are the parameters passed to the thread pool object using the `withArguments()` method.


### shutdown()

Close the thread pool


### $id 
The current thread's number, ranging from `0~(total number of threads-1)`. When a thread restarts, the new successor thread has the same thread number as the old one.


### Example

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
