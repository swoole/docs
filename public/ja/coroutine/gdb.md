# コレージのデバッグ

Swooleコレージを使用する際には、以下の方法でデバッグを行うことができます。

## GDBデバッグ

### gdbに入る <!-- {docsify-ignore} -->

```shell
gdb php test.php
```

### gdbinit <!-- {docsify-ignore} -->

```shell
(gdb) source /path/to/swoole-src/gdbinit
```

###ブレークポイントを設定する <!-- {docsify-ignore} -->

例えば `co::sleep` 関数

```shell
(gdb) b zim_swoole_coroutine_util_sleep
```

### 現在のプロセスのすべてのコレージと状態を印刷する <!-- {docsify-ignore} -->

```shell
(gdb) co_list 
coroutine 1 SW_CORO_YIELD
coroutine 2 SW_CORO_RUNNING
```

### 現在の実行中のコレージの呼び出しスタックを印刷する <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 
coroutine cid:[2]
[0x7ffff148a100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff148a0a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:7 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10
```

### 指定のコレージIDの呼び出しスタックを印刷する <!-- {docsify-ignore} -->

``` shell
(gdb) co_bt 1
[0x7ffff1487100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff14870a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:3 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10 
```

### 全局コレージの状態を印刷する <!-- {docsify-ignore} -->

```shell
(gdb) co_status 
	 stack_size: 2097152
	 call_stack_size: 1
	 active: 1
	 coro_num: 2
	 max_coro_num: 3000
	 peak_coro_num: 2
```

## PHPコードのデバッグ

現在のプロセスのすべてのコレージを巡り、呼び出しスタックを印刷します。

```php
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> `4.1.0`またはそれ以上のバージョンが必要です

* 迭代器を返し、`foreach`で巡ったり、`iterator_to_array`で配列に変換することができます

```php
use Swoole\Coroutine;
$coros = Coroutine::listCoroutines();
foreach($coros as $cid)
{
	var_dump(Coroutine::getBackTrace($cid));
}
```
