# 調試協程

使用`Swoole`協程時，可以使用以下的方法進行調試

## GDB調試

### 進入 gdb <!-- {docsify-ignore} -->

```shell
gdb php test.php
```

### gdbinit <!-- {docsify-ignore} -->

```shell
(gdb) source /path/to/swoole-src/gdbinit
```

### 設定斷點 <!-- {docsify-ignore} -->

例如 `co::sleep` 函數

```shell
(gdb) b zim_swoole_coroutine_util_sleep
```

### 打印當前進程的所有協程和狀態 <!-- {docsify-ignore} -->

```shell
(gdb) co_list 
coroutine 1 SW_CORO_YIELD
coroutine 2 SW_CORO_RUNNING
```

### 打印當前運行時協程的調用栈 <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 
coroutine cid:[2]
[0x7ffff148a100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff148a0a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:7 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10
```

### 打印指定協程id的調用栈 <!-- {docsify-ignore} -->

``` shell
(gdb) co_bt 1
[0x7ffff1487100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff14870a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:3 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10 
```

### 打印全局協程的狀態 <!-- {docsify-ignore} -->

```shell
(gdb) co_status 
	 stack_size: 2097152
	 call_stack_size: 1
	 active: 1
	 coro_num: 2
	 max_coro_num: 3000
	 peak_coro_num: 2
```

## PHP代码调试

遍歷當前進程內的所有協程，並打印調用栈。

```php
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> 需要`4.1.0`或更高版本

* 回傳迭代器，可使用`foreach`遍歷，或使用`iterator_to_array`轉為數組

```php
use Swoole\Coroutine;
$coros = Coroutine::listCoroutines();
foreach($coros as $cid)
{
	var_dump(Coroutine::getBackTrace($cid));
}
```
