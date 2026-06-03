# Debug Coroutine

Saat menggunakan coroutine `Swoole`, bisa menggunakan metode berikut untuk debugging.

## Debug GDB

### Masuk ke gdb <!-- {docsify-ignore} -->

```shell
gdb php test.php
```

### gdbinit <!-- {docsify-ignore} -->

```shell
(gdb) source /path/to/swoole-src/gdbinit
```

### Mengatur breakpoint <!-- {docsify-ignore} -->

Misalnya fungsi `co::sleep`

```shell
(gdb) b zim_swoole_coroutine_util_sleep
```

### Mencetak semua coroutine dan status dalam proses saat ini <!-- {docsify-ignore} -->

```shell
(gdb) co_list 
coroutine 1 SW_CORO_YIELD
coroutine 2 SW_CORO_RUNNING
```

### Mencetak stack panggilan coroutine yang sedang berjalan <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 
coroutine cid:[2]
[0x7ffff148a100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff148a0a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:7 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10
```

### Mencetak stack panggilan berdasarkan ID coroutine tertentu <!-- {docsify-ignore} -->

``` shell
(gdb) co_bt 1
[0x7ffff1487100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff14870a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:3 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10 
```

### Mencetak status coroutine global <!-- {docsify-ignore} -->

```shell
(gdb) co_status 
	 stack_size: 2097152
	 call_stack_size: 1
	 active: 1
	 coro_num: 2
	 max_coro_num: 3000
	 peak_coro_num: 2
```

## Debug Kode PHP

Menelusuri semua coroutine dalam proses saat ini, dan mencetak stack panggilan.

```php
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> Membutuhkan `4.1.0` atau versi yang lebih baru

* Mengembalikan iterator, bisa ditelusuri dengan `foreach`, atau dikonversi ke array dengan `iterator_to_array`

```php
use Swoole\Coroutine;
$coros = Coroutine::listCoroutines();
foreach($coros as $cid)
{
	var_dump(Coroutine::getBackTrace($cid));
}
```
