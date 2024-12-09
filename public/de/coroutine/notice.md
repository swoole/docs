# Co-runtime Programmings Knowhow

Please read this chapter carefully when using Swoole [co-runtime](/co-runtime) features.

## Programming Paradigm

* Inside co-runtime, global variables are prohibited.
* Use the `use` keyword to introduce external variables into the current scope, but do not use references.
* Communication between co-routines must use [Channel](/co-runtime/channel).

!> That is, do not use global variables or references to external variables in co-routine communication, but instead use `Channel`.

* If your project hooks into `zend_execute_ex` or `zend_execute_internal`, you need to pay special attention to the C stack. You can use [Co::set](/co-runtime/co?id=set) to reset the size of the C stack.

!> After hooking these two entry functions, most of the time, flat PHP instruction calls will be transformed into `C` function calls, increasing the consumption of the C stack.

## Exiting a Co-routine

In older versions of Swoole, using `exit` in a co-routine to forcibly exit the script can lead to memory errors and unpredictable results or even a `coredump`. Using `exit` in a Swoole service will cause the entire service process to exit and all internal co-routines to terminate exceptionally, leading to serious issues. For a long time, Swoole has prohibited developers from using `exit`, but developers can use an unconventional method of throwing exceptions to achieve the same exit logic as `exit` at the top level with a `catch`.

!> Starting from version v4.2.2, scripts (not creating an `http_server`) are allowed to exit with `exit` only if there is only one current co-routine.

Swoole **v4.1.0** and above directly support using PHP's `exit` in `co-routines` and `service event loops`. At this point, the underlying will automatically throw a catchable `Swoole\ExitException`, and developers can catch and implement the same exit logic as native PHP where needed.

### Swoole\ExitException

`Swoole\ExitException` inherits from `Exception` and adds two methods, `getStatus` and `getFlags`:

```php
namespace Swoole;

class ExitException extends \Exception
{
	public function getStatus(): mixed
	public function getFlags(): int
}
```

#### getStatus()

Retrieves the `status` parameter passed to the `exit($status)` function at the time of exit, supporting any variable type.

```php
public function getStatus(): mixed
```

#### getFlags()

Retrieves the environment information mask at the time of exit.

```php
public function getFlags(): int
```

Currently, there are the following masks:

| Constant | Description |
| -- | -- |
| SWOOLE_EXIT_IN_COROUTINE | Exited in a co-routine |
| SWOOLE_EXIT_IN_SERVER | Exited in a server |

### Usage

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

#### Exiting with a Status Code

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

In co-runtime programming, you can directly use `try/catch` to handle exceptions. **However, exceptions must be caught within the co-routine and cannot be caught across co-routines**.

!> Not only are `Exception` thrown by the application layer, but some underlying errors can also be caught, such as the nonexistence of `function`, `class`, or `method`.

### Error Example

In the following code, `try/catch` and `throw` are in different co-routines, and the exception cannot be caught within the co-routine. When the co-routine exits, an uncaught exception will cause a fatal error.

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

Catch the exception within the co-routine.

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

## No Co-routine Switching in __get / __set Magic Methods

Reason: [Reference to PHP7 Kernel Analysis](https://github.com/pangudashu/php7-internal/blob/40645cfe087b373c80738881911ae3b178818f11/3/zend_object.md)

> **Note:** If a class has a `__get()` method, then when an object is instantiated and property memory (i.e., properties_table) is allocated, an additional zval is allocated, with the type being HashTable. Each time `__get($var)` is called, the input `$var` name is stored in this hash table. The purpose of this is to prevent recursive calls. For example:
> 
> ***public function __get($var) { return $this->$var; }***
>
> This is a case where `__get()` is called again while accessing a non-existent property, which would result in recursive calls within `__get()`. Therefore, before calling `__get()`, it first checks whether the current `$var` is already in `__get()`. If it is, `__get()` will not be called again; otherwise, `$var` is inserted as a key into the hash table, and the hash value is set to: *guard |= IN_ISSET. After `__get()` is called, the hash value is set to: *guard &= ~IN_ISSET.
>
> This HashTable is not only used by `__get()`, but other magic methods as well, so its hash value type is zend_long, with different magic methods occupying different bit positions; secondly, not all objects will allocate this HashTable additionally. During object creation, whether to allocate it depends on whether the ***zend_class_entry.ce_flags*** contains ***ZEND_ACC_USE_GUARDS***. If `__get()`, `__set()`, `__unset()`, or `__isset()` methods are defined during class compilation, the ce_flags will be marked with this flag.

After a co-routine switchout, the next call will be judged as a recursive call, which is a PHP **feature**. After communication with the PHP development team, there is still no solution for the time being.

Note: Although there is no code in the magic method that can cause a co-routine switch, setting up a co-routine preemptive scheduling may still forcefully switch the magic method's co-routine.

Recommendation: Implement your own `get`/`set` methods and call them explicitly.

Original issue link: [#2625](https://github.com/swoole/swoole-src/issues/2625)

## Severe Errors

The following behaviors will cause severe errors.

### Sharing a Connection Among Multiple Co-routines

Unlike synchronous blocking programs, co-routines handle requests concurrently, so there may be many requests being processed in parallel at the same time. Once a client connection is shared, it can lead to data confusion between different co-routines. Reference: [Multiple co-routines sharing a TCP connection](/question/use?id=client-has-already-been-bound-to-another-coroutine)
### Verwenden von Klassenstaticen Variablen/Globalen Variablen zum Speichern des Kontextes

Mehrere Co-routines werden parallel ausgeführt, daher können keine Klassenstaticen Variablen/Globalen Variablen verwendet werden, um den Kontextinhalt der Co-routine zu speichern. Der Gebrauch lokaler Variablen ist sicher, da die Werte lokaler Variablen automatisch im Co-routine-Stack gespeichert werden und andere Co-routines den lokalen Variablen einer Co-routine nicht zugreifen können.

#### Fehlerbeispiel

```php
$server = new Swoole\Http\Server('127.0.0.1', 9501);

$_array = [];
$server->on('request', function ($request, $response) {
    global $_array;
    // Anfrage /a (Co-routine 1)
    if ($request->server['request_uri'] == '/a') {
        $_array['name'] = 'a';
        co::sleep(1.0);
        echo $_array['name'];
        $response->end($_array['name']);
    }
    // Anfrage /b (Co-routine 2)
    else {
        $_array['name'] = 'b';
        $response->end();
    }
});
$server->start();
```

Stellen Sie zwei parallele Anfragen ein.

```shell
curl http://127.0.0.1:9501/a
curl http://127.0.0.1:9501/b
```

* In der Co-routine `1` wird der globale Variablen `$_array['name']` auf den Wert `a` gesetzt
* Die Co-routine `1` ruft `co::sleep` auf, um zu schlafen
* Die Co-routine `2` wird ausgeführt, setzt `$_array['name']` auf den Wert `b` und beendet die Co-routine
* Zu diesem Zeitpunkt kehrt der Timer zurück, und der untere Layer fortsetzt den Betrieb der Co-routine `1`. In der Logik der Co-routine `1` gibt es eine Abhängigkeit vom Kontext. Wenn der Wert von `$_array['name']` erneut gedruckt wird, erwartet das Programm den Wert `a`, aber dieser Wert wurde bereits von der Co-routine `2` geändert, und der tatsächliche Ergebnis ist `b`, was zu einem logischen Fehler führt
* Ebenso ist es sehr gefährlich, den Kontext im Co-routine-Programm mit Klassenstaticen Variablen wie `Class::$array`, globalen Objektattributen wie `$object->array` oder anderen globalen Variablen wie `$GLOBALS` zu speichern. Unerwartetes Verhalten kann auftreten.

![](../_images/coroutine/notice-1.png)

#### Richtige Beispiel: Verwenden von Context zum Verwalten des Kontextes

Man kann eine `Context`-Klasse verwenden, um den Kontext der Co-routine zu verwalten. In der `Context`-Klasse wird der Co-routine-ID mit `Coroutine::getuid` erhalten und dann die globalen Variablen zwischen verschiedenen Co-routines isoliert, und wenn eine Co-routine beendet wird, wird der Kontextdaten bereinigt.

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

Benutzung:

```php
use Swoole\Coroutine\Context;

$server = new Swoole\Http\Server('127.0.0.1', 9501);

$server->on('request', function ($request, $response) {
    if ($request->server['request_uri'] == '/a') {
        Context::put('name', 'a');
        co::sleep(1.0);
        echo Context::get('name');
        $response->end(Context::get('name'));
        // Co-routine beenden und Kontext bereinigen
        Context::delete('name');
    } else {
        Context::put('name', 'b');
        $response->end();
        // Co-routine beenden und Kontext bereinigen
        Context::delete();
    }
});
$server->start();
```
