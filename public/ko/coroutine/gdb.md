# 디버깅 코루틴

`Swoole` 코루틴을 사용할 때 다음 방법으로 디버깅을 수행할 수 있습니다.


## GDB 디버깅


### gdb 진입 <!-- {docsify-ignore} -->

```shell
gdb php test.php
```


### gdbinit <!-- {docsify-ignore} -->

```shell
(gdb) source /path/to/swoole-src/gdbinit
```


### 중단점 설정 <!-- {docsify-ignore} -->

예를 들어 `co::sleep` 함수

```shell
(gdb) b zim_swoole_coroutine_util_sleep
```


### 현재 프로세스의 모든 코루틴 및 상태 출력 <!-- {docsify-ignore} -->

```shell
(gdb) co_list 
coroutine 1 SW_CORO_YIELD
coroutine 2 SW_CORO_RUNNING
```


### 현재 실행 중인 코루틴의 호출 스택 출력 <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 
coroutine cid:[2]
[0x7ffff148a100] Swoole\Coroutine->sleep(0.500000) [내부 함수]
[0x7ffff148a0a0] {클로저}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:7 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [내부 함수]
[0x7ffff141e030] (메인) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10
```


### 지정된 코루틴 id의 호출 스택 출력 <!-- {docsify-ignore} -->

``` shell
(gdb) co_bt 1
[0x7ffff1487100] Swoole\Coroutine->sleep(0.500000) [내부 함수]
[0x7ffff14870a0] {클로저}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:3 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [내부 함수]
[0x7ffff141e030] (메인) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10 
```


### 전역 코루틴의 상태 출력 <!-- {docsify-ignore} -->

```shell
(gdb) co_status 
	 stack_size: 2097152
	 call_stack_size: 1
	 active: 1
	 coro_num: 2
	 max_coro_num: 3000
	 peak_coro_num: 2
```

## PHP 코드 디버깅

현재 프로세스 내의 모든 코루틴을 순회하고 호출 스택을 출력합니다.

```php
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> `4.1.0` 이상 버전 필요

* 반복자를 반환하며, `foreach`로 순회하거나 `iterator_to_array`로 배열로 변환할 수 있습니다.

```php
use Swoole\Coroutine;
$coros = Coroutine::listCoroutines();
foreach($coros as $cid)
{
	var_dump(Coroutine::getBackTrace($cid));
}
```
