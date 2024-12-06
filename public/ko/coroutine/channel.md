# 코루틴/채널

> 먼저 [개요](/coroutine)를 확인하여 코루틴의 기본 개념을 이해한 후 이 섹션을 살펴보시기 바랍니다.

채널은 코루틴 간 통신에 사용되며, 여러 생산자 코루틴과 소비자 코루틴을 지원합니다. 기본적으로 코루틴의 교체와 스케줄링이 자동으로 실행됩니다.


## 구현 원리

  * 채널은 `PHP`의 `Array`와 유사하며, 메모리만 차지하고 다른 추가 자원이 할당되지 않습니다. 모든 작업은 메모리 작업이며, `IO` 소모가 없습니다.
  * 기본적으로 `PHP`의 참조 카운팅을 사용하여 실행하며, 메모리 복사가 발생하지 않습니다. 거대한 문자열이나 배열을 전달해도 추가 성능 소모가 발생하지 않습니다.
  * 채널은 참조 카운팅을 기반으로 하며, 이관이 없습니다.


## 사용 예제

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;

run(function(){
    $channel = new Channel(1);
    Coroutine::create(function () use ($channel) {
        for($i = 0; $i < 10; $i++) {
            Coroutine::sleep(1.0);
            $channel->push(['rand' => rand(1000, 9999), 'index' => $i]);
            echo "{$i}\n";
        }
    });
    Coroutine::create(function () use ($channel) {
        while(1) {
            $data = $channel->pop(2.0);
            if ($data) {
                var_dump($data);
            } else {
                assert($channel->errCode === SWOOLE_CHANNEL_TIMEOUT);
                break;
            }
        }
    });
});
```


## 방법


### __construct()

채널 생성자입니다.

```php
Swoole\Coroutine\Channel::__construct(int $capacity = 1)
```

  * **매개변수** 

    * **`int $capacity`**
      * **기능**：캐시区 용량을 설정합니다. 【적어도 `1` 이상의 정수여야 합니다】
      * **기본값**：`1`
      * **기타 값**：없음

!> 기본적으로 PHP 참조 카운팅을 사용하여 변수를 저장하며, 버퍼 영역은 `$capacity * sizeof(zval)` 바이트의 메모리만 차지합니다. PHP7에서 `zval`은 `16`바이트입니다. 예를 들어 `$capacity = 1024` 인 경우, 채널은 최대 `16K`의 메모리를 차지합니다.

!> `Server`에서 사용할 때는 [onWorkerStart](/server/events?id=onworkerstart) 이후에 생성이 가능합니다.


### push()

채널에 데이터를 작성합니다.

```php
Swoole\Coroutine\Channel->push(mixed $data, float $timeout = -1): bool
```

  * **매개변수** 

    * **`mixed $data`**
      * **기능**：데이터를 push합니다. 【PHP의 모든 유형의 변수, 익명 함수 및 자원을 포함할 수 있습니다】
      * **기본값**：없음
      * **기타 값**：없음

      !> 명확히 하기 위해 채널에 `null`과 `false`를 작성하지 않는 것이 좋습니다.

    * **`float $timeout`**
      * **기능**：초기화 시간을 설정합니다.
      * **단위**：초 【소수도 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`를 나타냅니다】
      * **기본값**：`-1` 【무제한입니다】
      * **기타 값**：없음
      * **버전 영향**：Swoole 버전 >= v4.2.12

      !> 채널이 가득 찰 경우, `push`는 현재 코루틴을 일시정지하고, 약속된 시간 내에 다른 소비자가 데이터를 소비하지 않으면 초기화가 발생합니다. 기본적으로 현재 코루틴을 복구하고, `push` 호출은 즉시 `false`를 반환하며, 작성에 실패합니다.

  * **반환값**

    * 성공 시 `true`를 반환합니다.
    * 채널이 닫힐 경우 실패하여 `false`를 반환하며, `$channel->errCode`를 통해 오류 코드를 얻을 수 있습니다.

  * **확장**

    * **채널이 가득 찼을 때**

      * 자동으로 현재 코루틴을 `yield`하고, 다른 소비자 코루틴이 데이터를 소비한 후에 채널이 작성 가능해지고, 현재 코루틴이 다시 `resume`됩니다.
      * 여러 생산자 코루틴이 동시에 `push`할 때, 기본적으로 대기열을 유지하며, 순서대로 생산자 코루틴을 하나씩 `resume`합니다.

    * **채널이 비어 있을 때**

      * 자동으로 하나의 소비자 코루틴을 깨웁니다.
      * 여러 소비자 코루틴이 동시에 `pop`할 때, 기본적으로 대기열을 유지하며, 순서대로 소비자 코루틴을 하나씩 `resume`합니다.

!> `Coroutine\Channel`은 로컬 메모리를 사용하며, 다른 프로세스 간의 메모리는 격리되어 있습니다. 오직 같은 프로세스의 다른 코루틴 내에서만 `push`와 `pop` 작업을 수행할 수 있습니다. 


### pop()

채널에서 데이터를 읽습니다.

```php
Swoole\Coroutine\Channel->pop(float $timeout = -1): mixed
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능**：초기화 시간을 설정합니다.
      * **단위**：초 【소수도 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`를 나타냅니다】
      * **기본값**：`-1` 【무제한입니다】
      * **기타 값**：없음
      * **버전 영향**：Swoole 버전 >= v4.0.3

  * **반환값**

    * 반환값은 PHP의 모든 유형의 변수, 익명 함수 및 자원을 포함할 수 있습니다.
    * 채널이 닫힐 경우 실패하여 `false`를 반환합니다.

  * **확장**

    * **채널이 가득 찼을 때**

      * `pop`가 데이터를 소비한 후에 자동으로 하나의 생산자 코루틴을 깨우고, 새로운 데이터를 작성하게 합니다.
      * 여러 생산자 코루틴이 동시에 `push`할 때, 기본적으로 대기열을 유지하며, 순서대로 생산자 코루틴을 하나씩 `resume`합니다.

    * **채널이 비어 있을 때**

      * 자동으로 현재 코루틴을 `yield`하고, 다른 생산자 코루틴이 데이터를 작성한 후에 채널이 읽을 수 있게 되고, 현재 코루틴이 다시 `resume`됩니다.
      * 여러 소비자 코루틴이 동시에 `pop`할 때, 기본적으로 대기열을 유지하며, 순서대로 소비자 코루틴을 하나씩 `resume`합니다.


### stats()

채널의 상태를 가져옵니다.

```php
Swoole\Coroutine\Channel->stats(): array
```

  * **반환값**

    반환하는 배열에는 버퍼 채널이 포함되는 `4`가지 정보와 버퍼가 없는 채널이 포함되는 `2`가지 정보가 있습니다.
    
    - `consumer_num` 소비자 수, 현재 채널이 비어 있고 다른 코루틴이 `push` 메서드를 호출하여 데이터를 생산하는 데 기다리는 `N`개의 코루틴이 있는 것을 나타냅니다.
    - `producer_num` 생산자 수, 현재 채널이 가득 찼고 다른 코루틴이 `pop` 메서드를 호출하여 데이터를 소비하는 데 기다리는 `N`개의 코루틴이 있는 것을 나타냅니다.
    - `queue_num` 채널의 요소 수

```php
array(
  "consumer_num" => 0,
  "producer_num" => 1,
  "queue_num" => 10
);
```


### close()

채널을 닫습니다. 그리고 읽기 및 쓰기를 기다리는 모든 코루틴을 깨웁니다.

```php
Swoole\Coroutine\Channel->close(): bool
```

!> 모든 생산자 코루틴을 깨우고, `push` 메서드가 `false`를 반환합니다; 모든 소비자 코루틴을 깨우고, `pop` 메서드가 `false`를 반환합니다


### length()

채널의 요소 수를 가져옵니다.

```php
Swoole\Coroutine\Channel->length(): int
```


### isEmpty()

현재 채널이 비어 있는지를 확인합니다.

```php
Swoole\Coroutine\Channel->isEmpty(): bool
```


### isFull()

현재 채널이 가득 찼는지를 확인합니다.

```php
Swoole\Coroutine\Channel->isFull(): bool
```


## 속성


### capacity

채널 버퍼의 용량입니다.

[생성자](/coroutine/channel?id=__construct)에서 설정한 용량은 이곳에 유지되지만 **설정된 용량이 `1`보다 작을 경우** 이 변수는 `1`과 같게 됩니다.

```php
Swoole\Coroutine\Channel->capacity: int
```
### errCode

오류 코드를 가져옵니다.

```php
Swoole\Coroutine\Channel->errCode: int
```

  * **반환값**


값 | 해당 상수 | 역할
---|---|---

0 | SWOOLE_CHANNEL_OK | 기본 성공
-1 | SWOOLE_CHANNEL_TIMEOUT | pop이 실패했을 때(시간 초과)
-2 | SWOOLE_CHANNEL_CLOSED | channel이 닫혀 있어 더 이상 channel을 사용할 수 없습니다.
