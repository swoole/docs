# 타이머 Timer

밀리초 단위의 정밀한 타이머입니다. 하단은 `epoll_wait`와 `setitimer`을 기반으로 구현되었으며, 데이터 구조는 `최소 힙`을 사용하여 대량의 타이머를 추가할 수 있습니다.

* 동기식 IO 프로세스에서 `setitimer`와 신호를 사용하여 구현되며, 예를 들어 `Manager`와 `TaskWorker` 프로세스와 같습니다.
* 비동기식 IO 프로세스에서는 `epoll_wait`/`kevent`/`poll`/`select`의 超時 시간을 사용하여 구현됩니다.


## 성능

하단은 최소 힙 데이터 구조로 타이머를 구현하여, 타이머의 추가와 삭제는 모두 메모리 작업이기 때문에 성능이 매우 높습니다.

> 공식적인 벤치마크 테스트 스크립트 [timer.php](https://github.com/swoole/benchmark/blob/master/timer.php)에서는 `10만`개의 무작위 시간의 타이머를 추가하거나 삭제하는 데 약 `0.08초`가 소요됩니다.

```shell
~/workspace/swoole/benchmark$ php timer.php
add 100000 timer :0.091133117675781s
del 100000 timer :0.084658145904541s
```

!> 타이머는 메모리 작업이며, `IO` 소모가 없습니다.


## 차이점

`Timer`와 PHP 자체의 `pcntl_alarm`은 다릅니다. `pcntl_alarm`은 `시계 신호 + tick` 함수를 기반으로 구현되어 있으며 몇 가지 결함이 있습니다:

  * 최대은 초 단위까지만 지원하며, `Timer`는 밀리초 단위까지 지원됩니다.
  * 동시에 여러 타이머 프로그램을 설정하는 것을 지원하지 않습니다.
  * `pcntl_alarm`은 `declare(ticks = 1)`에 의존하여 성능이 매우 나쁩니다.


## 제로 밀리초 타이머

하단은 시간 매개변수가 `0`인 타이머를 지원하지 않습니다. 이것은 `Node.js`와 같은 프로그래밍 언어와 다릅니다. `Swoole`에서는 [Swoole\Event::defer](/event?id=defer)를 사용하여 비슷한 기능을 구현할 수 있습니다.

```php
Swoole\Event::defer(function () {
  echo "hello\n";
});
```

!> 위의 코드는 `JS`의 `setTimeout(func, 0)`와 완전히 동일한 효과를 납니다.


## 별명

`tick()`、`after()`、`clear()` 모두 함수 스타일의 별명을 가지고 있습니다.


클래스 정적 메서드 | 함수 스타일 별명
---|---
`Swoole\Timer::tick()` | `swoole_timer_tick()`
`Swoole\Timer::after()` | `swoole_timer_after()`
`Swoole\Timer::clear()` | `swoole_timer_clear()`


## 메서드


### tick()

정기적인 타이머를 설정합니다.

`after` 타이머와 달리 `tick` 타이머는 지속적으로 발동하며, [Timer::clear](/timer?id=clear)를 호출하여 제거할 때까지 계속됩니다.

```php
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int
```

!> 1. 타이머는 현재 프로세스 공간 내에서만 유효합니다.  
   2. 타이머는 순수 비동기식으로 구현되었으며, [同步IO](/learn?id=同步io异步io)의 함수와 함께 사용할 수 없습니다. 그렇지 않으면 타이머의 실행 시간에 혼란이 발생할 수 있습니다.  
   3. 타이머가 실행 중일 때 발생할 수 있는 일정한 오차가 있습니다.

  * **매개변수** 

    * **`int $msec`**
      * **기능**：시간 지정
      * **값 단위**：밀리초【예: `1000`은 `1초`를 나타내며, `v4.2.10` 이하 버전은 최대 `86400000`을 초과할 수 없습니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`callable $callback_function`**
      * **기능**：시간이 만료된 후에 실행되는 함수이며, 호출 가능한 것이어야 합니다.
      * **기본값**：없음
      * **기타 값**：없음

    * **`...$params`**
      * **기능**：실행 함수에 데이터를 전달하는 【해당 매개변수는 옵션입니다】
      * **기본값**：없음
      * **기타 값**：없음
      
      !> 익명 함수의 `use` 문법을 사용하여 매개변수를 콜백 함수에 전달할 수 있습니다.

  * **$callback_function 콜백 함수** 

    ```php
    callbackFunction(int $timer_id, ...$params);
    ```

      * **`int $timer_id`**
        * **기능**：타이머의 `ID` 【해당 타이머를 [Timer::clear](/timer?id=clear)로 제거할 수 있습니다】
        * **기본값**：없음
        * **기타 값**：없음

      * **`...$params`**
        * **기능**：`Timer::tick`에서 전달된 세 번째 매개변수 `$param`
        * **기본값**：없음
        * **기타 값**：없음

  * **확장**

    * **타이머 교정**

      타이머 콜백 함수의 실행 시간은 다음 타이머 실행 시간에 영향을 미치지 않습니다. 예를 들어, `0.002초`에 `10ms`의 `tick` 타이머를 설정하면, 첫 번째는 `0.012초`에 콜백 함수를 실행합니다. 콜백 함수가 `5ms` 실행하면, 다음 타이머는 `0.022초`에 발동하는 것이지 `0.027초`가 아닙니다.
      
      하지만 타이머 콜백 함수의 실행 시간이 너무 길어 다음 타이머 실행 시간을 덮을 경우, 하단은 시간을 교정하고 지나간 행동을 버리며 다음 시간에 콜백을 수행합니다. 위의 예에서 `0.012초`에 콜백 함수가 `15ms` 실행하면, 본래 `0.022초`에 타이머 콜백이 발생해야 합니다. 실제로 이번 타이머는 `0.027초`에 돌아와야 하지만, 이때 타이머는 이미 만료되었습니다. 하단은 `0.032초`에 다시 타이머 콜백을 수행합니다.
    
    * **코루드 모드**

      코루드 환경에서 `Timer::tick` 콜백에서는 자동으로 코루드를 생성하므로, 코루드 관련 `API`를 직접 사용할 수 있으며, `go`를 호출하여 코루드를 생성할 필요가 없습니다.
      
      !> [enable_coroutine](/timer?id=close-timer-co)를 설정하여 자동 코루드 생성을 비활성화할 수 있습니다.

  * **사용 예시**

    ```php
    Swoole\Timer::tick(1000, function(){
        echo "timeout\n";
    });
    ```

    * **정확한 예시**

    ```php
    Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
        echo "timer_id #$timer_id, after 3000ms.\n";
        echo "param1 is $param1, param2 is $param2.\n";

        Swoole\Timer::tick(14000, function ($timer_id) {
            echo "timer_id #$timer_id, after 14000ms.\n";
        });
    }, "A", "B");
    ```

    * **잘못된 예시**

    ```php
    Swoole\Timer::tick(3000, function () {
        echo "after 3000ms.\n";
        sleep(14);
        echo "after 14000ms.\n";
    });
    ```


### after()

지정된 시간 후에 함수를 실행합니다. `Swoole\Timer::after` 함수는 일회성 타이머로, 실행이 완료되면 즉시 파괴됩니다.

이 함수는 PHP 표준 라이브러리에서 제공하는 `sleep` 함수와 다릅니다. `after`는 비阻断적입니다. 반면에 `sleep`는 호출 후 현재 프로세스를 블록시켜 새로운 요청을 처리할 수 없게 됩니다.

```php
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int
```

  * **매개변수** 

    * **`int $msec`**
      * **기능**：시간 지정
      * **값 단위**：밀리초【예: `1000`은 `1초`를 나타내며, `v4.2.10` 이하 버전은 최대 `86400000`을 초과할 수 없습니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`callable $callback_function`**
      * **기능**：시간이 만료된 후에 실행되는 함수이며, 호출 가능한 것이어야 합니다.
      * **기본값**：없음
      * **기타 값**：없음

    * **`...$params`**
      * **기능**：실행 함수에 데이터를 전달하는 【해당 매개변수는 옵션입니다】
      * **기본값**：없음
      * **기타 값**：없음
      
      !> 익명 함수의 `use` 문법을 사용하여 매개변수를 콜백 함수에 전달할 수 있습니다.

  * **반환값**

    * 실행 성공 시 타이머 `ID`를 반환하며, 타이머를 취소하려면 [Swoole\Timer::clear](/timer?id=clear)를 호출할 수 있습니다.

  * **확장**

    * **코루드 모드**

      코루드 환경에서 [Swoole\Timer::after](/timer?id=after) 콜백에서는 자동으로 코루드를 생성하므로, 코루드 관련 `API`를 직접 사용할 수 있으며, `go`를 호출하여 코루드를 생성할 필요가 없습니다.
      
      !> [enable_coroutine](/timer?id=close-timer-co)를 설정하여 자동 코루드 생성을 비활성화할 수 있습니다.

  * **사용 예시**

```php
$str = "Swoole";
Swoole\Timer::after(1000, function() use ($str) {
    echo "Hello, $str\n";
});
```
### clear()

타이머 ID를 사용하여 타이머를 삭제합니다.

```php
Swoole\Timer::clear(int $timer_id): bool
```

  * **매개변수** 

    * **`int $timer_id`**
      * **기능**: 타이머 ID【[Timer::tick](/timer?id=tick)、[Timer::after](/timer?id=after)를 호출하면 정수 ID가 반환됩니다】
      * **기본값**: 없음
      * **기타 값**: 없음

!> `Swoole\Timer::clear`는 다른 프로세스의 타이머를 삭제할 수 없으며, 현재 프로세스에만 적용됩니다.

  * **사용 예시**

```php
$timer = Swoole\Timer::after(1000, function () {
    echo "timeout\n";
});

var_dump(Swoole\Timer::clear($timer));
var_dump($timer);

// 출력: bool(true) int(1)
// timeout 출력: 없음
```


### clearAll()

현재 Worker 프로세스 내의 모든 타이머를 삭제합니다.

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
Swoole\Timer::clearAll(): bool
```


### info()

타이머의 정보를 반환합니다.

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
Swoole\Timer::info(int $timer_id): array
```

  * **반환값**

```php
array(5) {
  ["exec_msec"]=>
  int(6000)
  ["exec_count"]=> // v4.8.0 추가
  int(5)
  ["interval"]=>
  int(1000)
  ["round"]=>
  int(0)
  ["removed"]=>
  bool(false)
}
```


### list()

타이머 반복자를 반환하여, 현재 Worker 프로세스 내의 모든 타이머 ID를 `foreach`循环으로遍历할 수 있습니다.

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
Swoole\Timer::list(): Swoole\Timer\Iterator
```

  * **사용 예시**

```php
foreach (Swoole\Timer::list() as $timer_id) {
    var_dump(Swoole\Timer::info($timer_id));
}
```


### stats()

타이머 상태를 확인합니다.

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
Swoole\Timer::stats(): array
```

  * **반환값**

```php
array(3) {
  ["initialized"]=>
  bool(true)
  ["num"]=>
  int(1000)
  ["round"]=>
  int(1)
}
```


### set()

타이머 관련 매개변수를 설정합니다.

```php
Swoole\Timer::set(array $array): void
```

!> 이 방법은 `v4.6.0` 버전부터 비권장입니다.

## 코루outine 종료 :id=close-timer-co

타이머가 백그라운드에서 실행하는 코루outine는 기본적으로 자동으로 생성되나, 개별로 타이머를 종료할 수 있습니다.

```php
swoole_async_set([
  'enable_coroutine' => false,
]);
```
