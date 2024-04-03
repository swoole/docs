# Coroutine Programming Tips

When using the Swoole [coroutine](/coroutine) feature, please carefully read this chapter for programming tips.
## Programming Paradigms

* Global variables are not allowed within coroutines.
* The `use` keyword is used to introduce external variables into the current scope within coroutines, and using references is prohibited.
* Communication between coroutines must be done using [Channel](/coroutine/channel).

!> This means that communication between coroutines should not involve global variables or referencing external variables into the current scope; instead, use `Channel`.

* If the project has extended and hooked `zend_execute_ex` or `zend_execute_internal`, special attention should be paid to the C stack. You can utilize [Co::set](/coroutine/coroutine?id=set) to reset the size of the C stack.

!> After hooking into these two entry functions, most of the time, flat PHP instruction calls will be converted to `C` function calls, increasing the consumption of the C stack.
## Exiting Coroutine

In older versions of Swoole, using `exit` to forcefully exit the script within a coroutine could lead to memory errors resulting in unexpected outcomes or `coredump`. In a Swoole service, using `exit` would cause the entire service process to exit, terminating all internal coroutines and leading to serious issues. Swoole has long prohibited developers from using `exit`, but developers can use throwing exceptions as an unconventional way to achieve similar exit logic as `exit` by catching them at the top level.

In version **v4.2.2** and above, scripts (without creating an `http_server`) are allowed to `exit` with only the current coroutine.

Starting from Swoole version **v4.1.0**, direct support was provided for using PHP's `exit` within `coroutines` and `service event loops`. In this case, the underlying system automatically throws a catchable `Swoole\ExitException`, allowing developers to catch it where necessary and implement the same exit logic as standard PHP.
### Swoole\ExitException

`Swoole\ExitException` inherits from `Exception` and adds two new methods `getStatus` and `getFlags`:

```php
namespace Swoole;

class ExitException extends \Exception
{
	public function getStatus(): mixed
	public function getFlags(): int
}
```
#### getStatus()

Obtains the `status` parameter passed during the `exit($status)` call, supports any variable type.

```php
public function getStatus(): mixed
```
#### getFlags()

Obtain the environment information mask when exiting.

```php
public function getFlags(): int
```

Currently, the following masks are available:

| Constant | Description |
| -- | -- |
| SWOOLE_EXIT_IN_COROUTINE | Exit in coroutine |
| SWOOLE_EXIT_IN_SERVER | Exit in Server |
### Instructions
#### Basic Usage

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

function route()
{
    controller();
}

function controller()
{
    your_code();
}

function your_code()
{
    Coroutine::sleep(.001);
    exit(1);
}

run(function () {
    try {
        route();
    } catch (\Swoole\ExitException $e) {
        var_dump($e->getMessage());
        var_dump($e->getStatus() === 1);
        var_dump($e->getFlags() === SWOOLE_EXIT_IN_COROUTINE);
    }
});
```
#### Exit with Status Code

```php
use function Swoole\Coroutine\run;

$exit_status = 0;
run(function () {
    try {
        exit(123);
    } catch (\Swoole\ExitException $e) {
        global $exit_status;
        $exit_status = $e->getStatus();
    }
});
var_dump($exit_status);
```
## Exception Handling

In coroutine programming, exceptions can be handled directly using `try/catch`. **However, exceptions must be caught within the coroutine and cannot be caught across coroutines**.

!> Not only exceptions thrown at the application layer, but also some underlying errors like non-existent `function`s, `class`es, or `method`s can be caught.
### Error Example

In the following code, `try/catch` and `throw` are in different coroutines, and the exception cannot be caught within the coroutine. When the coroutine exits, finding an uncaught exception will result in a fatal error.

```bash
PHP Fatal error:  Uncaught RuntimeException
```

```php
try {
    Swoole\Coroutine::create(function () {
        throw new \RuntimeException(__FILE__, __LINE__);
    });
}
catch (\Throwable $e) {
    echo $e;
}
```
### Correct Example

Capturing exceptions in a coroutine.

```php
function test() {
	throw new \RuntimeException(__FILE__, __LINE__);
}

Swoole\Coroutine::create(function () {
	try {
		test();
	}
	catch (\Throwable $e) {
		echo $e;
	}
});
```
## Coroutines switching is not allowed in the __get / __set magic methods

Reason: [Reference PHP7 Internal Analysis](https://github.com/pangudashu/php7-internal/blob/40645cfe087b373c80738881911ae3b178818f11/3/zend_object.md)

> **Note:** If a class has a __get() method, an additional zval of type HashTable is allocated when allocating memory for object properties_table. Each time __get($var) is called, the name of the input $var is stored in this hash table. This is done to prevent recursive calls. For example:
> 
> ***public function __get($var) { return $this->$var; }***
>
> In this case, calling __get() accesses a non-existing property, which will recursively call __get() within the method. If there is no check on the input $var requested, the recursion will continue indefinitely. Therefore, before calling __get(), it will first check if the current $var is already in __get(). If so, __get() will not be called again. Otherwise, the $var is inserted as a key into that HashTable, and the hash value is set to: *guard |= IN_ISSET. After calling __get(), the hash value is set to: *guard &= ~IN_ISSET.
>
> This HashTable is not only used for __get(), other magic methods also use it. Therefore, the hash value type is zend_long, and different magic methods occupy different bits. Moreover, not all objects will have this HashTable allocated. The allocation is determined when creating objects based on ***zend_class_entry.ce_flags*** whether it contains ***ZEND_ACC_USE_GUARDS***. While compiling the class, if __get(), __set(), __unset(), __isset() methods are defined, ce_flags will be marked with this mask.

After a coroutine switch, the next call will be identified as a recursive call, caused by this PHP **feature**, which currently has no solution even after communication with the PHP development team.

Note: Although there is no code that leads to coroutine switches in the magic methods, when preemption scheduling of coroutines is enabled, magic methods may still be forcibly switched by coroutines.

Recommendation: Implement `get`/`set` methods explicitly

Original issue link: [#2625](https://github.com/swoole/swoole-src/issues/2625)
## Serious Error

The following actions will result in a serious error.
### Sharing a Connection Among Multiple Coroutines

Unlike synchronous blocking programs, coroutines handle requests concurrently. Therefore, multiple requests may be processed in parallel at the same time. Sharing a client connection among them will result in data corruption between different coroutines. Refer to: [Shared TCP Connection in Multiple Coroutines](/question/use?id=client-has-already-been-bound-to-another-coroutine)
### Using class static variables/global variables to save context

Multiple coroutines are executed concurrently, so class static variables/global variables cannot be used to save coroutine context. It is safe to use local variables because the values of local variables are automatically saved in the coroutine stack, and other coroutines cannot access the local variables of a coroutine.
#### Error example

```php
$server = new Swoole\Http\Server('127.0.0.1', 9501);

$_array = [];
$server->on('request', function ($request, $response) {
    global $_array;
    // Request /a (coroutine 1 )
    if ($request->server['request_uri'] == '/a') {
        $_array['name'] = 'a';
        co::sleep(1.0);
        echo $_array['name'];
        $response->end($_array['name']);
    }
    // Request /b (coroutine 2 )
    else {
        $_array['name'] = 'b';
        $response->end();
    }
});
$server->start();
```

Send `2` concurrent requests.

```shell
curl http://127.0.0.1:9501/a
curl http://127.0.0.1:9501/b
```

* The value of the global variable `$_array['name']` is set to `a` in coroutine `1`.
* Coroutine `1` calls `co::sleep` and suspends.
* Coroutine `2` executes and sets the value of `$_array['name']` to `b`, then coroutine `2` ends.
* At this point, the timer returns and resumes the execution of coroutine `1`. There is a contextual dependency in the logic of coroutine `1`. When trying to print the value of `$_array['name']` again, the expected value should be `a`, but this value has been modified by coroutine `2`, resulting in the actual result being `b`, leading to a logical error.
* Similarly, using class static variables `Class::$array`, global object properties `$object->array`, other superglobals like `$GLOBALS`, etc., to store context in coroutine programs is very dangerous. Unexpected behavior may occur.

![](../_images/coroutine/notice-1.png)
#### Correct example: managing context with Context

You can use a `Context` class to manage coroutine context. In the `Context` class, `Coroutine::getuid` is used to get the coroutine `ID`, then isolate global variables between different coroutines and clean up context data when the coroutine exits.

```php
use Swoole\Coroutine;

class Context
{
    protected static $pool = [];

    static function get($key)
    {
        $cid = Coroutine::getuid();
        if ($cid < 0)
        {
            return null;
        }
        if(isset(self::$pool[$cid][$key])){
            return self::$pool[$cid][$key];
        }
        return null;
    }

    static function put($key, $item)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            self::$pool[$cid][$key] = $item;
        }

    }

    static function delete($key = null)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            if($key){
                unset(self::$pool[$cid][$key]);
            }else{
                unset(self::$pool[$cid]);
            }
        }
    }
}
```

Usage:

```php
use Swoole\Coroutine\Context;

$server = new Swoole\Http\Server('127.0.0.1', 9501);

$server->on('request', function ($request, $response) {
    if ($request->server['request_uri'] == '/a') {
        Context::put('name', 'a');
        co::sleep(1.0);
        echo Context::get('name');
        $response->end(Context::get('name'));
        // Clean up on coroutine exit
        Context::delete('name');
    } else {
        Context::put('name', 'b');
        $response->end();
        // Clean up on coroutine exit
        Context::delete();
    }
});
$server->start();
```
