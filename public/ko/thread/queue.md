# 안전 병렬 컨테이너 Queue

병렬로 동작하는 `Queue` 구조체를 생성하여 스레드 매개변수로 자식스레드에 전달할 수 있습니다. 읽기 및 쓰기 작업은 다른 스레드에서 보입니다.

## 특징
- `Thread\Queue`는 FIFO(First In First Out) 데이터 구조입니다.

- `Map`, `ArrayList`, `Queue`는 자동으로 메모리를 할당하므로 `Table`처럼 고정적으로 할당할 필요가 없습니다.

- 내부적으로 자동으로 잠금을 가하며 스레드 안전합니다.

- 전달 가능한 변수 유형은 [스레드 매개변수 전달](thread/transfer.md)을 참고하세요.

- 반복器和는 `C++ std::queue`를 사용하므로, 오직 FIFO 작업만 지원합니다.

- `Map`, `ArrayList`, `Queue` 객체를 생성할 때 스레드 매개변수로 자식스레드에 전달해야 합니다.

- `Thread\Queue`는 원소만 밀어 넣고 뽑아낼 수 있으며, 원소를 무작위로 접근할 수 없습니다.

- `Thread\Queue`는 내장된 스레드 조건부변수를 가지고 있어, `push/pop` 작업 중에 다른 스레드를 깨우거나 기다릴 수 있습니다.

## 예제

```php
use Swoole\Thread;
use Swoole\Thread\Queue;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $queue = new Queue;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i, $queue);
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16)), Queue::NOTIFY_ONE);
        usleep(random_int(10000, 100000));
    }
    $n = 4;
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1];
    while (1) {
        $job = $queue->pop(-1);
        if (!$job) {
            break;
        }
        var_dump($job);
    }
}
```

## 상수



이름 | 역할
---|---
`Queue::NOTIFY_ONE` | 한 개의 스레드를 깨우기
`Queue::NOTIFY_ALL` | 모든 스레드를 깨우기


## 메서드 목록


### __construct()
안전 병렬 컨테이너 `Queue`의 생성자

```php
Swoole\Thread\Queue->__construct()
```


### push()
큐의 맨 끝에 데이터를 쓰기

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

  * **매개변수**
      * `mixed $value`
          * 기능: 쓰려는 데이터 내용입니다.
          * 기본값: 없음.
          * 기타값: 없음.

      !> 명확히 하기 위해 채널에 `null`과 `false`를 쓰지 마세요.
  
      * `int $notify`
          * 기능: 읽기를 기다리는 스레드를 깨우시킬지 여부입니다.
          * 기본값: `0`, 아무런 스레드도 깨우지 않습니다.
          * 기타값: `Swoole\Thread\Queue::NOTIFY_ONE` 한 개의 스레드를 깨우고, `Swoole\Thread\Queue::NOTIFY_ALL` 모든 스레드를 깨웁니다.



### pop()
큐의 맨 앞에서 데이터를 꺼내기

```php
Swoole\Thread\Queue()->pop(float $timeout = 0): mixed
```

* **매개변수**
    * `float $wait`
        * 기능: 기다리는 시간입니다.
        * 기본값: `0`, 기다리지 않습니다.
        * 기타값: `0`이 아닐 경우, 큐가 비어 있을 때 `$timeout`초 동안 프로세스가 `push()`데이터를 기다립니다. 부정수는 영원히 기다린다는 것을 나타냅니다.

* **반환값**
    * 큐의 맨 앞의 데이터를 반환합니다. 큐가 비어 있을 경우 직접 `NULL`을 반환합니다.

> `Queue::NOTIFY_ALL`를 사용하여 모든 스레드를 깨우면, `push()`작업에 의해 쓰인 데이터는 하나의 스레드만이 얻을 수 있습니다.


### count()
큐의 원소 개수를 반환합니다.

```php
Swoole\Thread\Queue()->count(): int
```

* **반환값**
    * 큐의 개수를 반환합니다.

### clean()
큐의 모든 원소를 제거합니다.

```php
Swoole\Thread\Queue()->clean(): void
```
