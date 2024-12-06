# 커루outine API

> 먼저 [개요](/coroutine)를 보시면 커루outine의 기본 개념을 이해하고 이 섹션을 볼 수 있습니다.


## 방법


### set()

커루outine 설정, 커루outine 관련 옵션을 설정합니다.

```php
Swoole\Coroutine::set(array $options);
```


매개변수 | 이 버전 이후 안정적 | 역할 
---|---|---
max_coroutine | - | 전역 최대 커루outine 수를 설정하며, 제한을 초과하면 하단에서 새로운 커루outine를 만들 수 없게 되고, Server에서는 [server->max_coroutine](/server/setting?id=max_coroutine)가 덮어씌웁니다.
stack_size/c_stack_size | - | 단일 커루outine의 초기 C 스택 메모리 크기를 설정하며, 기본은 2M입니다.
log_level | v4.0.0 | 로그 레벨 [상세](/consts?id=로그 레벨)
trace_flags | v4.0.0 | 추적 태그 [상세](/consts?id=추적 태그)
socket_connect_timeout | v4.2.10 | 연결 시간 초과 시간, **[클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간 초과 규칙)** 참고
socket_read_timeout | v4.3.0 | 읽기 시간 초과, **[클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간 초과 규칙)** 참고
socket_write_timeout | v4.3.0 | 쓰기 시간 초과, **[클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간 초과 규칙)** 참고
socket_dns_timeout | v4.4.0 | 도메인 해석 시간 초과, **[클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간 초과 규칙)** 참고
socket_timeout | v4.2.10 | 전송/수신 시간 초과, **[클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간 초과 규칙)** 참고
dns_cache_expire | v4.2.11 | swoole dns 캐시 만료 시간 설정, 단위 초, 기본 60초
dns_cache_capacity | v4.2.11 | swoole dns 캐시 용량 설정, 기본 1000
hook_flags | v4.4.0 | 원터치 커루outine화 hook 범위 설정, [원터치 커루outine화](/runtime) 참고
enable_preemptive_scheduler | v4.4.0 | 커루outine 강제 우선 실행 스케줄러를 설정하며, 커루outine 최대 실행 시간은 10ms로 [ini 설정](/other/config)을 덮어씌웁니다.
dns_server | v4.5.0 | dns 조회에 사용하는 server를 설정하며, 기본은 "8.8.8.8"입니다.
exit_condition | v4.5.0 | `callable`을 전달하여 bool을 반환하며, reaktor 종료 조건을 사용자 정의할 수 있습니다. 예를 들어, 커루outine 수가 0이 되었을 때만 프로그램을 종료하고 싶다면, 다음과 같이 작성할 수 있습니다. `Co::set(['exit_condition' => function () {return Co::stats()['coroutine_num'] === 0;}]);`
enable_deadlock_check | v4.6.0 | 커루outine 데드락 검사를 사용하는지 여부를 설정하며, 기본적으로 사용합니다.
deadlock_check_disable_trace | v4.6.0 | 커루outine 데드락 검사의 스택 프레임 출력을 사용하는지 여부를 설정합니다.
deadlock_check_limit | v4.6.0 | 커루outine 데드락 검사 시 최대 출력 수를 제한합니다.
deadlock_check_depth | v4.6.0 | 커루outine 데드락 검사 시 반환되는 스택 프레임 수를 제한합니다.
max_concurrency | v4.8.2 | 최대 병행 요청 수


### getOptions()

설정한 커루outine 관련 옵션을 가져옵니다.

!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::getOptions(): null|array;
```


### create()

새로운 커루outine를 생성하고 즉시 실행합니다.

```php
Swoole\Coroutine::create(callable $function, ...$args): int|false
go(callable $function, ...$args): int|false // php.ini의 use_shortname 설정 참고
```

* **매개변수**

    * **`callable $function`**
      * **기능**: 커루outine가 실행하는 코드로, 반드시 `callable`이어야 하며, 시스템이 생성 가능한 커루outine 총수는 [server->max_coroutine](/server/setting?id=max_coroutine) 설정에 의해 제한됩니다.
      * **기본값**: 없음
      * **기타 값**: 없음

* **반환값**

    * 생성 실패 시 `false`를 반환합니다.
    * 생성 성공 시 커루outine의 `ID`를 반환합니다.

!> 하단이 자식 커루outine의 코드를 우선 실행하기 때문에, `Coroutine::create`가 반환될 때는 자식 커루outine가 일시적으로 중단되고, 현재 커루outine의 코드가 계속 실행됩니다.

  * **실행 순서**

    커루outine 내에서 `go`를 사용하여 새로운 커루outine를 생성합니다. Swoole의 커루outine는 단일 프로세스 단일 스레드 모델이기 때문에:

    * `go`를 사용하여 생성한 자식 커루outine는 우선 실행되며, 자식 커루outine가 완료되거나 일시적으로 중단될 때, 다시 부모 커루outine로 돌아가 아래의 코드를 실행합니다.
    * 자식 커루outine가 일시적으로 중단된 후에 부모 커루outine가 종료되어도 자식 커루outine의 실행에 영향을 미치지 않습니다.

    ```php
    \Co\run(function() {
        go(function () {
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
    });
    ```

* **커루outine 오버 헤드**

  각 커루outine는 독립적이며 별도의 메모리 공간(스택 메모리)이 필요합니다. `PHP-7.2`에서 하단은 커루outine의 변수를 저장하기 위해 `8K`의 `stack`을 할당하며, `zval`의 크기는 `16바이트`이므로 `8K`의 `stack`은 최대 `512`개의 변수를 저장할 수 있습니다. 커루outine 스택 메모리가 `8K`을 초과하면 `ZendVM`이 자동으로 확장합니다.

  커루outine가 종료될 때는 신청한 `stack` 메모리를 해제합니다.

  * `PHP-7.1`, `PHP-7.0`의 경우 기본적으로 `256K`의 스택 메모리를 할당합니다.
  * `Co::set(['stack_size' => 4096])`를 호출하여 기본 스택 메모리 크기를 변경할 수 있습니다.



### defer()

`defer`는 자원의 해제를 위해 사용되며, **커루outine가 닫힐 때**(즉, 커루outine 함수가 실행이 완료될 때)에 호출됩니다. 비록 예외가 발생하더라도 등록된 `defer`는 실행됩니다.

!> Swoole 버전 >= 4.2.9

```php
Swoole\Coroutine::defer(callable $function);
defer(callable $function); // 짧은 이름 API
```

!> 주의해야 할 점은, 그것의 호출 순서는 역전적입니다(후입선출), 즉 먼저 등록한 것이 후에 실행됩니다. 역전적 호출 순서는 자원 해제의 올바른 논리에 부합하며, 후에 신청한 자원은 선입력된 자원에 기반을 두고 있을 수 있습니다. 예를 들어, 선입력된 자원을 먼저 해제하면, 후에 신청한 자원이 해제되기 어렵습니다.

  * **예시**

```php
go(function () {
    defer(function () use ($db) {
        $db->close();
    });
});
```


### exists()

특정 커루outine가 존재하는지를 확인합니다.

```php
Swoole\Coroutine::exists(int $cid = 0): bool
```

!> Swoole 버전 >= v4.3.0

  * **예시**

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

현재 커루outine의 유일한 `ID`를 가져옵니다. 그것의 별명은 `getuid`이며, 프로세스 내에서 유일한 정수입니다.

```php
Swoole\Coroutine::getCid(): int
```

* **반환값**

    * 성공 시 현재 커루outine `ID`를 반환합니다.
    * 현재 커루outine 환경이 아닐 경우 `-1`을 반환합니다.
### getPcid()

현재 코루틴의 부모 `ID`를 가져옵니다.

```php
Swoole\Coroutine::getPcid([$cid]): int
```

!> Swoole 버전 >= v4.3.0

* **매개변수**

    * **`int $cid`**
      * **기능**：코루틴 cid, 매개변수 기본값은 현재 코루틴
      * **기본값**：현재 코루틴
      * **기타 값**：없음

  * **예제**

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

// -- EXPECT--

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

!> 비자식 코루틴에서 `getPcid`를 호출하면 `-1` (비코루틴 공간에서 생성된)를 반환합니다  
비코루틴 내부에서 `getPcid`를 호출하면 `false` (부모 코루틴이 없음)을 반환합니다  
`0`은 예약된 `id`로, 반환값에는 나타나지 않습니다

!> 코루틴 사이에는 실질적인 지속적인 부모-자식 관계가 없으며, 코루틴은 서로 격리되어 독립적으로 작동합니다. 이 `Pcid`는 현재 코루틴을 생성한 코루틴의 `id`로 이해할 수 있습니다

  * **사용처**

    * **여러 코루틴 호출 스택을 연결하기**

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

현재 코루틴의 컨텍스트 객체를 가져옵니다.

```php
Swoole\Coroutine::getContext([int $cid = 0]): Swoole\Coroutine\Context
```

!> Swoole 버전 >= v4.3.0

* **매개변수**

    * **`int $cid`**
      * **기능**：코루틴 `CID`, 선택적 매개변수
      * **기본값**：현재 코루틴 `CID`
      * **기타 값**：없음

  * **역할**

    * 코루틴이 종료되면 컨텍스트는 자동으로 청소됩니다 (다른 코루틴이나 글로벌 변수에 의존하지 않는 경우)
    * `defer` 등록 및 호출의 오버 헤드가 없습니다 (청소 메서드를 등록할 필요가 없고, 함수를 호출하여 청소할 필요가 없습니다)
    * PHP 배열로 구현된 컨텍스트의 해시 계산 오버 헤드가 없습니다 (코루틴 수가 매우 많을 때는 이점이 있습니다)
    * `Co\Context`은 `ArrayObject`를 사용하여 다양한 저장 요구를 충족합니다 (객체이면서도 배열처럼 조작할 수 있습니다)

  * **예제**

```php
function func(callable $fn, ...$args)
{
    go(function () use ($fn, $args) {
        $fn(...$args);
        echo 'Coroutine#' . Co::getCid() . ' exit' . PHP_EOL;
    });
}

/**
* 저가 버전과의 호환성
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

현재 코루틴의 실행권을 수동적으로 포기합니다. 이는 IO 기반의 [코루틴 스케줄링](/coroutine?id=코루틴%E1%84%89%E6%9C%9F)이 아닙니다.

이 방법은 또 다른 별명을 가지고 있습니다: `Coroutine::suspend()`

!> `Coroutine::resume()`方法与 함께 사용해야 합니다. 이 코루틴이 `yield`한 후에는 다른 외부 코루틴에서 `resume`해야 하며, 그렇지 않으면 코루틴 누수와 함께 정지된 코루틴은 결코 실행되지 않습니다.

```php
Swoole\Coroutine::yield();
```

  * **예제**

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

특정 코루틴을 수동적으로 재개하여 실행을 계속 시킵니다. 이는 IO 기반의 [코루틴 스케줄링](/coroutine?id=코루틴%E1%84%89%E6%9C%9F)이 아닙니다.

!> 현재 코루틴이 정지된 상태일 때, 다른 코루틴에서 `resume`하여 현재 코루틴을 다시 깨울 수 있습니다

```php
Swoole\Coroutine::resume(int $coroutineId);
```

* **매개변수**

    * **`int $coroutineId`**
      * **기능**：재개할 코루틴 `ID`
      * **기본값**：없음
      * **기타 값**：없음

  * **예제**

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

현재 프로세스 내의 모든 코루틴을 탐历합니다.

```php
Swoole\Coroutine::list(): Swoole\Coroutine\Iterator
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> `v4.3.0` 이하 버전에서는 `listCoroutines`를 사용해야 하며, 새로운 버전에서는 해당 메서드의 이름을 축소하고 `listCoroutines`를 별명으로 설정했습니다. `list`는 `v4.1.0` 이상 버전에서 사용할 수 있습니다.

* **반환값**

    * 반환되는 이터레이터는 `foreach`를 통해 탐历하거나 `iterator_to_array`를 통해 배열로 전환할 수 있습니다

```php
$coros = Swoole\Coroutine::listCoroutines();
foreach($coros as $cid)
{
    var_dump(Swoole\Coroutine::getBackTrace($cid));
}
```


### stats()

코루틴 상태를 가져옵니다.

```php
Swoole\Coroutine::stats(): array
```

* **반환값**


key | 역할
---|---
event_num | 현재 reactor 이벤트 수
signal_listener_num | 현재 监听 signal 수
aio_task_num | 비동기 IO 작업 수 (여기서 aio는 파일 IO나 dns를 가리키며, 기타 네트워크 IO는 포함되지 않습니다, 이하와 동일)
aio_worker_num | 비동기 IO 작업 스레드 수
c_stack_size | 각 코루틴의 C 스택 크기
coroutine_num | 현재 실행 중인 코루틴 수
coroutine_peak_num | 현재 실행 중인 코루틴 수의 피크
coroutine_last_cid | 마지막으로 생성된 코루틴의 id

  * **예제**

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

코루outine 함수의 호출 스택을 가져옵니다.

```php
Swoole\Coroutine::getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
```

!> Swoole 버전 >= v4.1.0

* **매개변수**

    * **`int $cid`**
      * **기능**: 코루outine의 `CID`
      * **기본값**: 현재 코루outine `CID`
      * **기타값**: 없음

    * **`int $options`**
      * **기능**: 옵션을 설정합니다.
      * **기본값**: `DEBUG_BACKTRACE_PROVIDE_OBJECT` 【`object`의 인덱스를 제공할지 여부】
      * **기타값**: `DEBUG_BACKTRACE_IGNORE_ARGS` 【args의 인덱스를 무시할지 여부, 모든 function/method의 매개변수를 포함하여 메모리 낭비를 줄일 수 있습니다】

    * **`int limit`**
      * **기능**: 반환 스택 프레임의 개수를 제한합니다.
      * **기본값**: `0`
      * **기타값**: 없음

* **반환값**

    * 지정된 코루outine이 존재하지 않을 경우 `false`를 반환합니다.
    * 성공 시 [debug_backtrace](https://www.php.net/manual/ko/function.debug-backtrace.php) 함수의 반환과 동일한 형식의 배열을 반환합니다.

  * **예시**

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
            //반환된 배열은 자체적으로 형식화하여 출력해야 합니다.
            var_dump(Co::getBackTrace($cid))."\n";
            Co::sleep(3);
        }
    });
});
Swoole\Event::wait();
```


### printBackTrace()

코루outine 함수의 호출 스택을 출력합니다. 매개변수는 `getBackTrace`와 동일합니다.

!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::printBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0);
```


### getElapsed()

코루outine이 실행된 시간을 가져와 분석, 통계 수집 또는 좀비 코루outine을 찾는 데 사용됩니다.

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::getElapsed([$cid]): int
```
* **매개변수**

    * **`int $cid`**
      * **기능**: 선택적 매개변수로, 코루outine의 `CID`
      * **기본값**: 현재 코루outine `CID`
      * **기타값**: 없음

* **반환값**

    * 코루outine이 실행된 시간을 반환합니다. 부동소수점으로, 밀리초 단위의 정확도입니다.


### cancel()

특정 코루outine을 취소합니다. 하지만 현재 실행 중인 코루outine에 대한 취소 작업은 불가능합니다.

!> Swoole 버전 >= `v4.7.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::cancel($cid): bool
```
* **매개변수**

    * **`int $cid`**
        * **기능**: 코루outine의 `CID`
        * **기본값**: 없음
        * **기타값**: 없음

* **반환값**

    * 성공 시 `true`를 반환하고, 실패 시 `false`를 반환합니다.
    * 취소 실패 시 [swoole_last_error()](/functions?id=swoole_last_error)를 호출하여 오류 정보를 확인할 수 있습니다.


### isCanceled()

현재 실행 중인 작업이 수동으로 취소되었는지를 확인합니다.

!> Swoole 버전 >= `v4.7.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::isCanceled(): bool
```

* **반환값**

    * 수동 취소가 정상적으로 종료될 경우 `true`를 반환하고, 실패 시 `false`를 반환합니다.

#### 예시

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

코루outine의 절차적 스케줄링을 임시로 활성화합니다.

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::enableScheduler();
```


### disableScheduler()

코루outine의 절차적 스케줄링을 임시로 비활성화합니다.

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::disableScheduler();
```


### getStackUsage()

현재 PHP 스택의 메모리 사용량을 가져옵니다.

!> Swoole 버전 >= `v4.8.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::getStackUsage([$cid]): int
```

* **매개변수**

    * **`int $cid`**
        * **기능**: 선택적 매개변수로, 코루outine의 `CID`
        * **기본값**: 현재 코루outine `CID`
        * **기타값**: 없음


### join()

여러 코루outine를 병행하여 실행합니다.

!> Swoole 버전 >= `v4.8.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine::join(array $cid_array, float $timeout = -1): bool
```

* **매개변수**

    * **`array $cid_array`**
        * **기능**: 실행해야 할 코루outine의 `CID` 배열
        * **기본값**: 없음
        * **기타값**: 없음

    * **`float $timeout`**
        * **기능**: 총 타임아웃 시간으로, 타임아웃이 발생하면 즉시 반환합니다. 하지만 실행 중인 코루outine은 완료될 때까지 계속 실행됩니다.
        * **기본값**: -1
        * **기타값**: 없음

* **반환값**

    * 성공 시 `true`를 반환하고, 실패 시 `false`를 반환합니다.
    * 취소 실패 시 [swoole_last_error()](/functions?id=swoole_last_error)를 호출하여 오류 정보를 확인할 수 있습니다.

* **사용 예시**

```php
use Swoole\Coroutine;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

run(function () {
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


## 함수


### batch()

여러 코루outine를 병행하여 실행하고, 배열을 통해 해당 코루outine의 메서드 반환값을 반환합니다.

!> Swoole 버전 >= `v4.5.2`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\batch(array $tasks, float $timeout = -1): array
```

* **매개변수**

    * **`array $tasks`**
      * **기능**: 전달되는 메서드 콜백의 배열입니다. `key`가 지정되어 있다면 반환값도 해당 `key`에 의해 지정됩니다.
      * **기본값**: 없음
      * **기타값**: 없음

    * **`float $timeout`**
      * **기능**: 총 타임아웃 시간으로, 타임아웃이 발생하면 즉시 반환합니다. 하지만 실행 중인 코루outine은 완료될 때까지 계속 실행됩니다.
      * **기본값**: -1
      * **기타값**: 없음

* **반환값**

    * 콜백의 반환값이 포함된 배열을 반환합니다. `$tasks` 매개변수에서 `key`가 지정되어 있다면 반환값도 해당 `key`에 의해 지정됩니다.

* **사용 예시**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\batch;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = batch([
        'file_put_contents' => function () {
            return file_put_contents(__DIR__ . '/greeter.txt', "Hello,Swoole.");
        },
        'gethostbyname' => function () {
            return gethostbyname('localhost');
        },
        'file_get_contents' => function () {
            return file_get_contents(__DIR__ . '/greeter.txt');
        },
        'sleep' => function () {
            sleep(1);
            return true; // 0.1초의 타임아웃을 초과하여 즉시 반환합니다. 하지만 실행 중인 코루outine은 완료될 때까지 계속 실행됩니다.
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
### 병렬()

여러 코루틴을 병행하여 실행합니다.

!> Swoole 버전 >= `v4.5.3`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\parallel(int $n, callable $fn): void
```

* **매개변수**

    * **`int $n`**
      * **기능** : 최대 코루틴 수를 `$n`으로 설정합니다.
      * **기본값** : 없음
      * **기타값** : 없음

    * **`callable $fn`**
      * **기능** : 실행할 콜백 함수입니다.
      * **기본값** : 없음
      * **기타값** : 없음

* **사용 예시**

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

[array_map](https://www.php.net/manual/zh/function.array-map.php)와 유사하게 배열의 각 요소에 콜백 함수를 적용합니다.

!> Swoole 버전 >= `v4.5.5`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\map(array $list, callable $fn, float $timeout = -1): array
```

* **매개변수**

    * **`array $list`**
      * **기능** : `$fn` 함수를 적용할 배열입니다.
      * **기본값** : 없음
      * **기타값** : 없음

    * **`callable $fn`**
      * **기능** : `$list` 배열의 각 요소에 적용할 콜백 함수입니다.
      * **기본값** : 없음
      * **기타값** : 없음

    * **`float $timeout`**
      * **기능** : 총超时 시간을 설정합니다.超时되면 즉시 반환합니다. 그러나 실행 중인 코루틴은 완료할 수 있도록 계속 실행됩니다.
      * **기본값** : -1
      * **기타값** : 없음

* **사용 예시**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\map;

function fatorial(int $n): int
{
    return array_product(range($n, 1));
}

Coroutine\run(function () {
    $results = map([2, 3, 4], 'fatorial'); 
    print_r($results);
});
```

### deadlock_check()

코루틴의 데드락을 검사합니다. 호출 시 관련 스택 정보를 출력합니다.

기본적으로 **사용 중**이며, [EventLoop](learn?id=什么是eventloop)이 종료된 후에 코루틴 데드락이 존재하는 경우, 저수준에서 자동으로 호출됩니다.

[Coroutine::set](/coroutine/coroutine?id=set)에서 `enable_deadlock_check`를 설정하여 비활성화할 수 있습니다.

!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\deadlock_check();
```
