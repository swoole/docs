# Debugging Coroutines

When using `Swoole` coroutines, you can use the following methods for debugging:

## GDB Debugging

### Enter gdb <!-- {docsify-ignore} -->

```shell
gdb php test.php
```

### gdbinit <!-- {docsify-ignore} -->

```shell
(gdb) source /path/to/swoole-src/gdbinit
```

### Set Breakpoints <!-- {docsify-ignore} -->

For example, the `co::sleep` function

```shell
(gdb) b zim_swoole_coroutine_util_sleep
```

### Print all coroutines and their states in the current process <!-- {docsify-ignore} -->

```shell
(gdb) co_list 
coroutine 1 SW_CORO_YIELD
coroutine 2 SW_CORO_RUNNING
```

### Print the call stack of the currently running coroutine <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 
coroutine cid:[2]
[0x7ffff148a100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff148a0a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:7 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10
```

### Print the call stack of a specified coroutine id <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 1
[0x7ffff1487100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff14870a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:3 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10 
```

### Print the status of all global coroutines <!-- {docsify-ignore} -->

```shell
(gdb) co_status 
	 stack_size: 2097152
	 call_stack_size: 1
	 active: 1
	 coro_num: 2
	 max_coro_num: 3000
	 peak_coro_num: 2
```

## PHP Code Debugging

Iterate through all coroutines in the current process and print their call stacks.

```php
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> Requires version `4.1.0` or higher

* Returns an iterator that can be traversed with `foreach`, or converted to an array with `iterator_to_array`

```php
use Swoole\Coroutine;
$coros = Coroutine::listCoroutines();
foreach($coros as $cid)
{
	var_dump(Coroutine::getBackTrace($cid));
}
```
