# 스레드 동기화 실행 장애물 Barrier

`Thread\Barrier`는 스레드 동기화 메커니즘의 한 종류입니다. 특정 지점에서 여러 스레드를 동기화시켜 모든 스레드가 특정 임계점(장애물)에 도달하기 전에 자신의 작업을 완료하도록 합니다. 모든 참여하는 스레드가 이 장애물에 도달할 때까지 기다리다가 이후의 코드를 실행할 수 있습니다.

예를 들어, 우리는 `4`개의 스레드를 만들고자 합니다. 이 스레드들이 모두 준비가 되어 함께 작업을 수행하고자 하는데, 마치 육상 경기에서 심판의 총검과 같아서 신호가 나면 동시에 출발하는 것입니다. 이것은 `Thread\Barrier`를 이용하여 실현할 수 있습니다.

## 예제
```php
use Swoole\Thread;
use Swoole\Thread\Barrier;

const N = 4;
$args = Thread::getArguments();

if (empty($args)) {
    $barrier = new Barrier(N);
    $n = N;
    $threads = [];
    while($n--) {
        $threads[] = new Thread(__FILE__, $barrier, $n);
    }
} else {
    $barrier = $args[0];
    $n = $args[1];
    // 모든 스레드 준비 기다림
    $barrier->wait();
    echo "thread $n is running\n";
}
```

## 방법

### __construct()
생성자

```php
Thread\Barrier()->__construct(int $count): void
```

  * **매개변수**
      * `int $count`
          * 기능: 스레드 수, `1`보다 클 수 없습니다.
          * 默认값: 없음
          * 기타값: 없음
  
`wait` 연산을 수행하는 스레드 수가 설정된 카운트와 일치하지 않으면 모든 스레드가 막힐 것입니다.

### wait()

기타 스레드를 막아두고 모든 스레드가 `wait` 상태가 될 때까지 기다립니다. 이후 모든 기다리고 있는 스레드가 동시에 깨어나 하단의 코드를 계속 실행합니다.

```php
Thread\Barrier()->wait(): void
```
