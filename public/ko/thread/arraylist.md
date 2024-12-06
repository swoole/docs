# 안전 병렬 컨테이너 리스트

병렬로 작동하는 `리스트` 구조체를 만들어 스레드 매개변수로 자식스레드에 전달할 수 있습니다. 읽기 및 쓰기 작업은 다른 스레드에서 보입니다.




## 특징
- `맵`, `아바타리스트`, `큐`는 자동으로 메모리를 할당하므로 `테이블`처럼 고정적으로 할당할 필요가 없습니다.


- 기본적으로 자동으로 잠금을 가하며 스레드 안전합니다.


- 전달 가능한 변수 유형은 [데이터 유형](thread/transfer.md)을 참고하세요.


- 반복器和는 사용할 수 없으며, 대신 `toArray()`를 사용할 수 있습니다.


- 스레드 생성 전에 `맵`, `아바타리스트`, `큐` 객체를 자식스레드 매개변수로 전달해야 합니다.


- `Thread\ArrayList`은 `ArrayAccess`와 `Countable` 인터페이스를 구현하여 직접 배열처럼 사용할 수 있습니다.


- `Thread\ArrayList`은 숫자 인덱스만을 지원하며, 비숫자는 강제 변환을 한 번 거쳐 사용됩니다.


## 예시
```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new ArrayList;
    $thread = new Thread(__FILE__, $i, $list);
    sleep(1);
    $list[] = unique();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```



- 추가 및 수정: `$list[$index] = $value`

- 삭제: `unset($list[$index])`

- 읽기: `$value = $list[$index]`
- 길이 얻기: `count($list)`


## 삭제
주의해야 할 점은 삭제 작업은 `리스트`의 대량 전진 작업을 유발한다는 것입니다. 예를 들어 `리스트`에 `1000`개의 요소가 있을 때, `unset($list[4])`를 하면 `$list[5:999]`의 대량 이관 작업이 필요하며, `$list[4]`가 삭제되어 생긴 공백을 메울 것입니다. 그러나 요소를 깊이 복제하지 않고 그 포인터만 이동합니다.

> `리스트`가 클 경우, 앞쪽 요소를 삭제하면 많은 `CPU` 자원을 소모할 수 있습니다.


## 방법


### __construct()
안전 병렬 컨테이너 `아바타리스트`의 생성자

```php
Swoole\Thread\ArrayList->__construct(?array $values = null)
```



- `$values`는 선택적이며, 배열을 탐색하여 배열의 값을 `아바타리스트`에 추가합니다.

- 오직 `list` 유형의 배열만을 받아들입니다. 대괄호 배열은 받아들이지 않으며, 그렇지 않을 경우 예외가 발생합니다. 대괄호 배열은 `array_values`를 사용하여 `list` 유형의 배열로 변환해야 합니다.


### incr()
`아바타리스트`의 데이터를 안전하게 증가시킵니다. 부동소수 또는 정수를 지원하며, 다른 유형으로 증가 작업을 수행하면 자동으로 정수로 변환되고 `0`으로 초기화된 다음 증가 작업을 수행합니다.

```php
Swoole\Thread\ArrayList->incr(int $index, mixed $value = 1) : int | float
```

* **매개변수**
    * `int $index`
        * 기능: 인덱스 숫자이며, 유효한 인덱스 주소여야 합니다. 그렇지 않을 경우 예외가 발생합니다.
        * 默认값: 없음.
        * 기타 값: 없음.

    * `mixed $value`
        * 기능: 증가해야 할 값입니다.
        * 默认값: 1.
        * 기타 값: 없음.

* **반환값**
    * 증가된 값을 반환합니다.


### decr()
`아바타리스트`의 데이터를 안전하게 감소시킵니다. 부동소수 또는 정수를 지원하며, 다른 유형으로 감소 작업을 수행하면 자동으로 정수로 변환되고 `0`으로 초기화된 다음 감소 작업을 수행합니다.

```php
Swoole\Thread\ArrayList->(int $index, $value = 1) : int | float
```

* **매개변수**
    * `int $index`
        * 기능: 인덱스 숫자이며, 유효한 인덱스 주소여야 합니다. 그렇지 않을 경우 예외가 발생합니다.
        * 默认값: 없음.
        * 기타 값: 없음.

    * `mixed $value`
        * 기능: 감소해야 할 값입니다.
        * 默认값: 1.
        * 기타 값: 없음.

* **반환값**
    * 감소된 값을 반환합니다.


### count()
`아바타리스트`의 요소 수를 가져옵니다.

```php
Swoole\Thread\ArrayList()->count(): int
```

* **반환값**
    * `리스트`의 요소 수를 반환합니다.


### toArray()
`아바타리스트`를 배열로 변환합니다.

```php
Swoole\Thread\ArrayList()->toArray(): array
```

* **반환값**
    * `아바타리스트`의 모든 요소를 포함하는 유형 배열을 반환합니다.

### clean()
모든 요소를 비웁니다.

```php
Swoole\Thread\ArrayList()->clean(): void
```
