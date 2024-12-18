# Отладка координационных функций

При использовании координационных функций `Swoole` можно использовать следующие методы для отладки

## Отладка с использованием GDB


### Вхождение в gdb <!-- {docsify-ignore} -->

```shell
gdb php test.php
```


### 初始изация gdbinit <!-- {docsify-ignore} -->

```shell
(gdb) source /path/to/swoole-src/gdbinit
```


### Установка остановки <!-- {docsify-ignore} -->

Например, функция `co::sleep`

```shell
(gdb) b zim_swoole_coroutine_util_sleep
```


### Вывод всех координационных функций и их состояния в текущем процессе <!-- {docsify-ignore} -->

```shell
(gdb) co_list 
coroutine 1 SW_CORO_YIELD
coroutine 2 SW_CORO_RUNNING
```


### Вывод стека вызовов текущей исполнительной координации <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 
coroutine cid:[2]
[0x7ffff148a100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff148a0a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:7 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10
```


### Вывод стека вызовов указанной идентификационной записи координации <!-- {docsify-ignore} -->

``` shell
(gdb) co_bt 1
[0x7ffff1487100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff14870a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:3 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10 
```


### Вывод состояния всех глобальных координационных функций <!-- {docsify-ignore} -->

```shell
(gdb) co_status 
	 stack_size: 2097152
	 call_stack_size: 1
	 active: 1
	 coro_num: 2
	 max_coro_num: 3000
	 peak_coro_num: 2
```

## Отладка PHP-кода

Пройдите через все координации текущего процесса и выведите стек вызовов.

```php
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> Требуется версия `4.1.0` или выше

* Возвращает итератор, который можно использовать с `foreach`, или преобразовать в массив с помощью `iterator_to_array`

```php
use Swoole\Coroutine;
$coros = Coroutine::listCoroutines();
foreach($coros as $cid)
{
	var_dump(Coroutine::getBackTrace($cid));
}
```
