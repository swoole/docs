# Coroutine API

> We recommend reading the [overview](/coroutine) first to understand the basic concepts of coroutines before looking at this section.
## Method
### set()

Coroutine setting, setting coroutine-related options.

```php
Swoole\Coroutine::set(array $options);
```

Parameter | Stable After This Version | Function 
---|---|---
max_coroutine | - | Set the global maximum number of coroutines. When exceeded, the underlying layer will be unable to create new coroutines, and it will be overridden by [server->max_coroutine](/server/setting?id=max_coroutine) under the `Server`.
stack_size/c_stack_size | - | Set the memory size of the initial C stack of a single coroutine, default is 2M.
log_level | v4.0.0 | Log level, see [details](/consts?id=logging-level).
trace_flags | v4.0.0 | Trace flags, see [details](/consts?id=trace-flags).
socket_connect_timeout | v4.2.10 | Connection establishment timeout, **refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)**.
socket_read_timeout | v4.3.0 | Read timeout, **refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)**.
socket_write_timeout | v4.3.0 | Write timeout, **refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)**.
socket_dns_timeout | v4.4.0 | Domain name resolution timeout, **refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)**.
socket_timeout | v4.2.10 | Send/receive timeout, **refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)**.
dns_cache_expire | v4.2.11 | Set the expiration time of the Swoole DNS cache, in seconds, default is 60 seconds.
dns_cache_capacity | v4.2.11 | Set the capacity of the Swoole DNS cache, default is 1000.
hook_flags | v4.4.0 | One-click coroutine hook range configuration, refer to [One-click Coroutine](/runtime).
enable_preemptive_scheduler | v4.4.0 | Enable coroutine preemptive scheduling, where the maximum execution time of a coroutine is 10ms, and it will override the [ini configuration](/other/config).
dns_server | v4.5.0 | Set the server for DNS queries, default is "8.8.8.8".
exit_condition | v4.5.0 | Pass a `callable` that returns a bool to customize the condition for reactor exit. For example: if you want the program to exit only when the number of coroutines is 0, you can write `Co::set(['exit_condition' => function () {return Co::stats()['coroutine_num'] === 0;}]);`.
enable_deadlock_check | v4.6.0 | Set whether to enable coroutine deadlock detection, enabled by default.
deadlock_check_disable_trace | v4.6.0 | Set whether to output stack frames of coroutine deadlock detection.
deadlock_check_limit | v4.6.0 | Limit the maximum output count when detecting coroutine deadlocks.
deadlock_check_depth | v4.6.0 | Limit the number of stack frames to return when detecting coroutine deadlocks.
max_concurrency | v4.8.2 | Maximum number of concurrent requests.
### getOptions()

Get the coroutine related options that have been set.

!> Available only in Swoole version >= `v4.6.0`

```php
Swoole\Coroutine::getOptions(): null|array;
```
### create()

Create a new coroutine and execute it immediately.

```php
Swoole\Coroutine::create(callable $function, ...$args): int|false
go(callable $function, ...$args): int|false // Refer to the use_shortname configuration in php.ini
```

* **Parameters**

    * **`callable $function`**
      * **Description**: The code to be executed by the coroutine, must be a `callable`. The total number of coroutines the system can create is limited by the [server->max_coroutine](/server/setting?id=max_coroutine) setting.
      * **Default**: None
      * **Other values**: None

* **Return Values**

    * Returns `false` if creation fails
    * Returns the `ID` of the coroutine if creation is successful

!> Because the underlying system will prioritize the execution of child coroutine code, `Coroutine::create` will only return when the child coroutine is suspended, continuing the execution of the current coroutine's code.

  * **Execution Order**

    Create new coroutines nested in a coroutine using `go`. Since Swoole's coroutine follows a single-process, single-thread model:

    * Coroutines created using `go` will be executed first. After a child coroutine finishes execution or is suspended, it will return to the parent coroutine to continue executing the code.
    * If the parent coroutine exits after the child coroutine is suspended, it does not affect the execution of the child coroutine.

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

* **Coroutine Overhead**

  Each coroutine is independent and requires its own memory space (stack memory). In PHP-7.2 version, the underlying system allocates an `8K` stack to store coroutine variables. The size of a `zval` is `16 bytes`, meaning an `8K` stack can hold a maximum of `512` variables. The coroutine stack memory will automatically expand when it exceeds `8K`.

  The stack memory allocated defaults to `256K` in PHP-7.1 and PHP-7.0.
  You can modify the default stack memory size by calling `Co::set(['stack_size' => 4096])`.
### defer()

`defer` is used for resource release and will be called just before the coroutine is closed (i.e., when the coroutine function is finished executing). Even if an exception is thrown, registered `defer` will still be executed.

!> Swoole version >= 4.2.9

```php
Swoole\Coroutine::defer(callable $function);
defer(callable $function); // Short API
```

!> It is important to note that the calling order is in reverse order (last-in, first-out). In other words, the `defer` registered later will be executed first, following the last-in, first-out logic. This reverse order corresponds to the correct logic of resource release, as resources allocated later may be dependent on those allocated earlier. If the earlier resources are released first, the later resources may become difficult to release.

  * **Example**

```php
go(function () {
    defer(function () use ($db) {
        $db->close();
    });
});
```
### exists()

Checks whether the specified coroutine exists.

```php
Swoole\Coroutine::exists(int $cid = 0): bool
```

!> Swoole version >= v4.3.0

  * **Example**

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

Obtain the unique `ID` of the current coroutine, its alias is `getuid`, and it is a unique positive integer within the process.

```php
Swoole\Coroutine::getCid(): int
```

* **Return Value**
    * Returns the current coroutine `ID` on success.
    * Returns `-1` if not currently within a coroutine environment.
### getPcid()

Get the parent `ID` of the current coroutine.

```php
Swoole\Coroutine::getPcid([$cid]): int
```

!> Swoole version >= v4.3.0

* **Parameters**

    * **`int $cid`**
      * **Description**: Coroutine `id`, parameter optional. You can pass in the `id` of a specific coroutine to get its parent `id`.
      * **Default Value**: Current coroutine
      * **Other Values**: None

  * **Example**

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

// --EXPECT--

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

!> Calling `getPcid` outside coroutine context will return `-1` (created outside of coroutine space)  
Calling `getPcid` inside non-coroutine will return `false` (no parent coroutine)  
`0` is reserved `id` and will not appear in the return value

!> Coroutines do not have a real parent-child relationship between them; they operate independently and in isolation. This `Pcid` can be understood as the coroutine `id` that created the current coroutine.

  * **Usage**

    * **Connect multiple coroutine call stacks**

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

Get the context object of the current coroutine.

```php
Swoole\Coroutine::getContext([int $cid = 0]): Swoole\Coroutine\Context
```

!> Swoole version >= v4.3.0

* **Parameters**

    * **`int $cid`**
      * **Functionality**: Coroutine `CID`, optional parameter
      * **Default value**: Current coroutine `CID`
      * **Other values**: None

  * **Effects**

    * Automatically cleans up context after coroutine exits (if there are no other coroutine or global variable references)
    * No cost for registering and calling `defer` (no need to register cleanup methods, no need to call cleanup functions)
    * No hash computation cost for PHP array implementation of context (beneficial when there are a large number of coroutines)
    * `Co\Context` uses `ArrayObject`, meeting various storage needs (it's both an object and can be operated as an array)

  * **Example**

```php
function func(callable $fn, ...$args)
{
    go(function () use ($fn, $args) {
        $fn(...$args);
        echo 'Coroutine#' . Co::getCid() . ' exit' . PHP_EOL;
    });
}

/**
* Compatibility for lower version
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

// --EXPECT--
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

Manually yield the execution right of the current coroutine. Instead of IO-based [coroutine scheduling](/coroutine?id=coroutine-scheduling).

This method has another alias: `Coroutine::suspend()`

!> Must be used in pairs with the `Coroutine::resume()` method. After a coroutine `yield`, it must be `resume` by another external coroutine, or a coroutine leak will occur, and the suspended coroutine will never execute.

```php
Swoole\Coroutine::yield();
```

  * **Example**

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

Manually resume a coroutine to allow it to continue running, not based on IO [coroutine scheduling](/coroutine?id=coroutine-scheduling).

!> When the current coroutine is suspended, another coroutine can use `resume` to awaken the current coroutine again.

```php
Swoole\Coroutine::resume(int $coroutineId);
```

* **Parameters**

    * **`int $coroutineId`**
      * **Function**: The `ID` of the coroutine to be resumed
      * **Default**: None
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

Traverse all coroutines within the current process.

```php
Swoole\Coroutine::list(): Swoole\Coroutine\Iterator
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> Prior to `v4.3.0`, use `listCoroutines`, the abbreviated method name and alias for `listCoroutines` were introduced in newer versions. `list` method is available starting from `v4.1.0`.

* **Return Value**

    * Returns an iterator, which can be traversed using `foreach` loop or converted to an array using `iterator_to_array`

```php
$coros = Swoole\Coroutine::listCoroutines();
foreach($coros as $cid)
{
    var_dump(Swoole\Coroutine::getBackTrace($cid));
}
```
### stats()

Get coroutine status.

```php
Swoole\Coroutine::stats(): array
```

* **Return Values**

key | Description
---|---
event_num | current number of reactor events
signal_listener_num | current number of signal listeners
aio_task_num | number of asynchronous IO tasks (aio here refers to file IO or dns, not other network IO)
aio_worker_num | number of asynchronous IO worker threads
c_stack_size | size of the C stack for each coroutine
coroutine_num | current number of running coroutines
coroutine_peak_num | peak number of running coroutines
coroutine_last_cid | id of the last created coroutine

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

Get coroutine function call stack.

```php
Swoole\Coroutine::getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
```

!> Swoole version >= v4.1.0

* **Parameters**

    * **`int $cid`**
      * **Function** : Coroutine ID (`CID`)
      * **Default Value** : Current coroutine `CID`
      * **Other Values** : N/A

    * **`int $options`**
      * **Function** : Set options
      * **Default Value** : `DEBUG_BACKTRACE_PROVIDE_OBJECT` (whether to fill in the `object` index)
      * **Other Values** : `DEBUG_BACKTRACE_IGNORE_ARGS` (whether to ignore the args index, including all function/method parameters, to save memory overhead)

    * **`int limit`**
      * **Function** : Limit the number of stack frames returned
      * **Default Value** : `0`
      * **Other Values** : N/A

* **Return Value**

    * If the specified coroutine does not exist, it will return `false`
    * Returns an array on success, with the format identical to the return value of the [debug_backtrace](https://www.php.net/manual/zh/function.debug-backtrace.php) function

  * **Example**

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
            // Returns an array, needs to be manually formatted for output
            var_dump(Co::getBackTrace($cid))."\n";
            Co::sleep(3);
        }
    });
});
Swoole\Event::wait();
```
### printBackTrace()

Print the coroutine function call stack. Parameters are the same as `getBackTrace`.

!> Available in Swoole version >= `v4.6.0`

```php
Swoole\Coroutine::printBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0);
```
### getElapsed()

Get the running time of the coroutine for analysis, statistics, or finding zombie coroutines.

!> Available for Swoole version >= `v4.5.0`

```php
Swoole\Coroutine::getElapsed([$cid]): int
```

* **Parameters**

    * **`int $cid`**
      * **Description**: Optional parameter, the `CID` of the coroutine
      * **Default Value**: Current coroutine `CID`
      * **Other Values**: None

* **Return Value**

    * Float number representing the time the coroutine has been running, with millisecond precision.
### cancel()

Used to cancel a coroutine, but cannot initiate a cancellation operation on the current coroutine.

!> Available in Swoole version >= `v4.7.0`

```php
Swoole\Coroutine::cancel($cid): bool
```
* **Parameters**

    * **`int $cid`**
        * **Functionality**: Coroutine's `CID`
        * **Default**: None
        * **Other values**: None

* **Return Values**

    * Returns `true` on success, and `false` on failure
    * If cancellation fails, you can use [swoole_last_error()](/functions?id=swoole_last_error) to view the error message
### isCanceled()

Used to determine whether the current operation was manually canceled.

!> Available in Swoole version >= `v4.7.0`

```php
Swoole\Coroutine::isCanceled(): bool
```

* **Return Value**

    * If manually canceled and ended properly, `true` will be returned. If failed, `false` will be returned.
#### Example

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

    echo "Done\n";
});
```
### enableScheduler()

Temporary enable coroutine preemptive scheduling.

!> Available since Swoole version `v4.4.0`

```php
Swoole\Coroutine::enableScheduler();
```
### disableScheduler()

Temporarily disable coroutine preemptive scheduling.

!> Available in Swoole version >= `v4.4.0`

```php
Swoole\Coroutine::disableScheduler();
```
### getStackUsage()

Get the memory usage of the current PHP stack.

!> Available from Swoole version `v4.8.0` onward.

```php
Swoole\Coroutine::getStackUsage([$cid]): int
```

* **Parameters**

    * **`int $cid`**
        * **Description**: Optional parameter, the Coroutine ID (`CID`)
        * **Default Value**: Current Coroutine ID
        * **Other Values**: N/A
### join()

Concurrently executes multiple coroutines.

!> Available since Swoole version >= `v4.8.0`

```php
Swoole\Coroutine::join(array $cid_array, float $timeout = -1): bool
```

* **Parameters**

    * **`array $cid_array`**
        * **Functionality**: Array of CIDs of coroutines to be executed
        * **Default Value**: None
        * **Other Values**: None

    * **`float $timeout`**
        * **Functionality**: Total timeout period, the function will return immediately after the timeout. Coroutines that are running will continue to execute until completion, not being aborted
        * **Default Value**: -1
        * **Other Values**: None

* **Return Value**

    * Returns `true` on success, `false` on failure
    * To get error information in case of failure, use [swoole_last_error()](/functions?id=swoole_last_error)

* **Usage Example**

```php
use Swoole\Coroutine;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

run(function () {
    $result = [];
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
## Functions
### batch()

Execute multiple coroutines concurrently, and return the return values of these coroutine methods through an array.

!> Available with Swoole version >= `v4.5.2`

```php
Swoole\Coroutine\batch(array $tasks, float $timeout = -1): array
```

* **Parameters**

    * **`array $tasks`**
      * **Description**: Array of method callbacks. If a `key` is specified, the return value will be associated with that `key`.
      * **Default**: None
      * **Other values**: None

    * **`float $timeout`**
      * **Description**: Total timeout. After the timeout, the function will immediately return. However, coroutines that are still running will continue to execute to completion without aborting.
      * **Default**: -1
      * **Other values**: None

* **Return Value**

    * Returns an array containing the return values of the callbacks. If a `key` is specified in the `$tasks` parameter, the return value will be associated with that `key`.

* **Example**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\batch;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = batch([
        'file_put_contents' => function () {
            return file_put_contents(__DIR__ . '/greeter.txt', "Hello, Swoole.");
        },
        'gethostbyname' => function () {
            return gethostbyname('localhost');
        },
        'file_get_contents' => function () {
            return file_get_contents(__DIR__ . '/greeter.txt');
        },
        'sleep' => function () {
            sleep(1);
            return true; // Returns NULL because it exceeded the set timeout of 0.1 seconds. After the timeout, the function will immediately return. However, coroutines will continue to run to completion.
        },
        'usleep' => function () {
            usleep(1000);
            return true;
        },
    ], 0.1);
    $use = microtime(true) - $use;
    echo "Use {$use}s, Result:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Use {$end_time}s, Done\n";
```
### parallel()

Execute multiple coroutines concurrently.

!> Available since Swoole version >= `v4.5.3`

```php
Swoole\Coroutine\parallel(int $n, callable $fn): void
```

* **Parameters**

    * **`int $n`**
      * **Function**: Set the maximum number of coroutines to `$n`.
      * **Default**: None
      * **Other values**: None

    * **`callable $fn`**
      * **Function**: The callback function to be executed.
      * **Default**: None
      * **Other values**: None

* **Usage Example**

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

Similar to [array_map](https://www.php.net/manual/en/function.array-map.php), applies a callback function to each element of an array.

!> Available since Swoole version `v4.5.5`

```php
Swoole\Coroutine\map(array $list, callable $fn, float $timeout = -1): array
```

* **Parameters**

    * **`array $list`**
      * **Description**: The array to run the `$fn` function on.
      * **Default Value**: None
      * **Other Values**: None

    * **`callable $fn`**
      * **Description**: The callback function to be applied to each element in `$list`.
      * **Default Value**: None
      * **Other Values**: None

    * **`float $timeout`**
      * **Description**: The total timeout, after which it will return immediately. Running coroutines will continue to execute until completion and will not be terminated.
      * **Default Value**: -1
      * **Other Values**: None

* **Usage Example**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\map;

function factorial(int $n): int
{
    return array_product(range($n, 1));
}

Coroutine\run(function () {
    $results = map([2, 3, 4], 'factorial'); 
    print_r($results);
});
```
### deadlock_check()

Coroutine deadlock check, calling this function will output related stack information;

Default **enabled**, after [EventLoop](learn?id=what-is-eventloop) termination, if there are deadlock coroutines, the underlying system will automatically call this function;

You can disable it by setting `enable_deadlock_check` in [Coroutine::set](/coroutine/coroutine?id=set).

!> Swoole version >= `v4.6.0` available

```php
Swoole\Coroutine\deadlock_check();
```
