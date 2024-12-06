# 안전 병렬 컨테이너 Map

병렬로 동작하는 `Map` 구조체를 생성하여 스레드 매개변수로 자식스레드에 전달할 수 있습니다. 읽기 및 쓰기 작업은 다른 스레드에서 보입니다.




## 특징
- `Map`, `ArrayList`, `Queue`는 자동으로 메모리를 할당하므로 `Table`처럼 고정적으로 할당할 필요가 없습니다.


- 내부적으로 자동으로 잠금을 가하며 스레드 안전합니다.


- 전달 가능한 변수 유형은 [데이터 유형](thread/transfer.md)을 참고하세요.


- 반복器和는 지원되지 않으며, 대신 `keys()`, `values()`, `toArray()`를 사용할 수 있습니다.


- `Map`, `ArrayList`, `Queue` 객체를 생성하기 전에 스레드 매개변수로 자식스레드에 전달해야 합니다.


- `Thread\Map`은 `ArrayAccess`와 `Countable` 인터페이스를 구현하고 있어 직접 배열처럼 사용할 수 있습니다.


## 예제
```php
use Swoole\Thread;
use Swoole\Thread\Map;

$args = Thread::getArguments();
if (empty($args)) {
    $map = new Map;
    $thread = new Thread(__FILE__, $i, $map);
    sleep(1);
    $map['test'] = unique();
    $thread->join();
} else {
    $map = $args[1];
    sleep(2);
    var_dump($map['test']);
}
```



- 추가 및 수정: `$map[$key] = $value`

- 삭제: `unset($map[$key])`

- 읽기: `$value = $map[$key]`
- 길이 얻기: `count($map)`


## 방법


### __construct()
안전 병렬 컨테이너 `Map`의 생성자

```php
Swoole\Thread\Map->__construct(?array $values = null)
```


- `$values`는 선택적이며, 배열을 탐색하여 배열의 값을 `Map`에 추가합니다.


### add()
`Map`에 데이터를 작성합니다.

```php
Swoole\Thread\Map->add(mixed $key, mixed $value) : bool
```
  * **매개변수**
      * `mixed $key`
          * 기능: 추가할 키입니다.
          * 기본값: 없습니다.
          * 기타 값: 없습니다.
  
      * `mixed $value`
          * 기능: 추가할 값입니다.
          * 기본값: 없습니다.
          * 기타 값: 없습니다.
  
  * **반환값**
      * `$key`가 이미 존재하는 경우 `false`를 반환하고, 그렇지 않은 경우 성공적으로 추가되어 `true`를 반환합니다.


### update()
`Map`의 데이터를 업데이트합니다.

```php
Swoole\Thread\Map->update(mixed $key, mixed $value) : bool
```

  * **매개변수**
      * `mixed $key`
          * 기능: 업데이트할 키입니다.
          * 기본값: 없습니다.
          * 기타 값: 없습니다.
  
      * `mixed $value`
          * 기능: 업데이트할 값입니다.
          * 기본값: 없습니다.
          * 기타 값: 없습니다.
  
  * **반환값**
      * `$key`가 존재하지 않을 경우 `false`를 반환하고, 그렇지 않은 경우 성공적으로 업데이트되어 `true`를 반환합니다.


### incr()
`Map`의 데이터를 안전하게 증가시킵니다. 부동소수점 또는 정수형을 지원하며, 다른 유형으로 증가조작을 할 경우 자동으로 정수형으로 변환되어 `0`으로 초기화된 다음 증가조작을 합니다.

```php
Swoole\Thread\Map->incr(mixed $key, mixed $value = 1) : int | float
```
* **매개변수**
    * `mixed $key`
        * 기능: 증가할 키입니다. 존재하지 않으면 자동으로 생성되어 `0`으로 초기화됩니다.
        * 기본값: 없습니다.
        * 기타 값: 없습니다.

    * `mixed $value`
        * 기능: 증가할 값입니다.
        * 기본값: `1`입니다.
        * 기타 값: 없습니다.

* **반환값**
    * 증가된 값을 반환합니다.


### decr()
`Map`의 데이터를 안전하게 감소시킵니다. 부동소수점 또는 정수형을 지원하며, 다른 유형으로 감소조작을 할 경우 자동으로 정수형으로 변환되어 `0`으로 초기화된 다음 감소조작을 합니다.

```php
Swoole\Thread\Map->decr(mixed $key, mixed $value = 1) : int | float
```
* **매개변수**
    * `mixed $key`
        * 기능: 감소할 키입니다. 존재하지 않으면 자동으로 생성되어 `0`으로 초기화됩니다.
        * 기본값: 없습니다.
        * 기타 값: 없습니다.

    * `mixed $value`
        * 기능: 감소할 값입니다.
        * 기본값: `1`입니다.
        * 기타 값: 없습니다.

* **반환값**
    * 감소된 값을 반환합니다.


### count()
원소 수를 가져옵니다.

```php
Swoole\Thread\Map()->count(): int
```

  * **반환값**
      * `Map`의 원소 수를 반환합니다.


### keys()
모든 `key`를 반환합니다.

```php
Swoole\Thread\Map()->keys(): array
```

  * **반환값**
    * `Map`의 모든 `key`를 반환합니다.


### values()
모든 `value`를 반환합니다.

```php
Swoole\Thread\Map()->values(): array
```

* **반환값**
    * `Map`의 모든 `value`를 반환합니다.


### toArray()
`Map`을 배열로 변환합니다.

```php
Swoole\Thread\Map()->toArray(): array
```

### clean()
모든 원소를 제거합니다.

```php
Swoole\Thread\Map()->clean(): void
```
