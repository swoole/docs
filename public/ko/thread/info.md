# 방법과 속성


## 방법


### __construct()
멀티스레드 생성 방법

```php
Swoole\Thread->__construct(string $script_file, mixed ...$args)
```
* **매개변수**
    * `string $script_file`
        * 기능: 스레드 시작 후 실행할 파일입니다.
        * 기본값: 없음.
        * 기타값: 없음.

    * `mixed $args`
        * 기능: 메인스레드가 자식스레드에 전달하는 공유 데이터로, 자식스레드에서 `Swoole\Thread::getArguments()`를 사용하여 얻을 수 있습니다.
        * 기본값: 없음.
        * 기타값: 없음.

!> 스레드 생성이 실패하면 `Swoole\Exception`가 던져지고, 이를 `try catch`로 포착할 수 있습니다.


### join()
메인스레드가 자식스레드의 종료를 기다립니다. 자식스레드가 아직 실행 중이면 `join()`은 막히고, 자식스레드가 종료될 때까지 기다립니다.

```php
Swoole\Thread->join(): bool
```
* **반환값**
    * 성공 시 `true`를, 실패 시 `false`를 반환합니다.


### joinable()
자식스레드가 이미 종료되었는지 확인합니다.

```php
Swoole\Thread->joinable(): bool
```


#### 반환값

- `true`이면 자식스레드가 종료되어 `join()`호출시 막히지 않습니다.
- `false`이면 아직 종료되지 않았습니다.


### detach()
자식스레드를 메인스레드의 관리에서 벗어놓고, 더 이상 `join()`을 사용하여 스레드 종료를 기다릴 필요가 없습니다.

```php
Swoole\Thread->detach(): bool
```
* **반환값**
    * 성공 시 `true`를, 실패 시 `false`를 반환합니다.


### getId()
정적 방법으로 현재 스레드의 `ID`를 얻습니다.

```php
Swoole\Thread::getId(): int
```
* **반환값**
    * 현재 스레드의 ID를 나타내는 정수 반환합니다.


### getArguments()
정적 방법으로 메인스레드가 `new Swoole\Thread()`을 사용할 때 전달한 공유 데이터를 얻습니다. 자식스레드에서 호출합니다.

```php
Swoole\Thread::getArguments(): ?array
```

* **반환값**
    * 자식스레드에서 부모 프로세스가 전달한 공유 데이터를 반환합니다.

?> 메인스레드는 스레드 매개변수가 없으며, 스레드 매개변수가 비어 있는지 확인하여 부모와 자식 스레드를 구분하고, 다른 논리를 실행할 수 있습니다.
```php
use Swoole\Thread;

$args = Thread::getArguments(); // 메인스레드의 경우 $args는 비어 있고, 자식스레드의 경우 $args는 가득합니다.
if (empty($args)) {
    # 메인스레드
    new Thread(__FILE__, 'child thread'); // 스레드 매개변수를 전달합니다.
    echo "main thread\n";
} else {
    # 자식스레드
    var_dump($args); // 출력: ['child thread']
}
```


### getInfo()
정적 방법으로 현재 멀티스레드 환경의 정보를 얻습니다.

```php
Swoole\Thread::getInfo(): array
```
반환되는 배열 정보는 다음과 같습니다:



- `is_main_thread` : 현재 스레드가 메인스레드인지 여부

- `is_shutdown` : 스레드가 이미 종료되었는지 여부

- `thread_num` : 현재 활발한 스레드 수


### getPriority()
정적 방법으로 현재 스레드의 스케줄링 정보를 얻습니다.

```php
Swoole\Thread->getPriority(): array
```
반환되는 배열 정보는 다음과 같습니다:



- `policy` : 스레드 스케줄링 정책

- `priority` : 스레드의 스케줄링 우선순위


### setPriority()
정적 방법으로 현재 스레드의 스케줄링 우선순위와 정책을 설정합니다.

?> `root` 유저만 사용할 수 있으며, `root`이 아닐 경우 실행이 거절됩니다.

```php
Swoole\Thread->setPriority(int $priority, int $policy = -1): bool
```

* **매개변수**
    * `int $priority`
        * 기능: 스레드 스케줄링 우선순위를 설정합니다.
        * 기본값: 없음.
        * 기타값: 없음.

    * `mixed $policy`
        * 기능: 스레드 스케줄링 우선 정책을 설정합니다.
        * 기본값: `-1`, 즉 스케줄링 정책을 변경하지 않습니다.
        * 기타값: `Thread::SCHED_*` 관련 상수입니다.

* **반환값**
    * 성공 시 `true`를, 실패 시 `false`를 반환합니다.

> `SCHED_BATCH/SCHED_ISO/SCHED_IDLE/SCHED_DEADLINE`는 `Linux` 시스템에서만 사용할 수 있습니다.  

> `SCHED_FIFO/SCHED_RR` 정책의 스레드는 일반적으로 실시간 스레드로, 우선순위가 일반 스레드보다 높아 더 많은 `CPU` 시간을 할당받습니다.


### getAffinity()
정적 방법으로 현재 스레드의 `CPU` 친화성을 얻습니다.

```php
Swoole\Thread->getAffinity(): array
```
반환되는 값은 배열로, 요소는 `CPU` 코어 수입니다. 예를 들어 `[0, 1, 3, 4]`는 이 스레드가 `CPU`의 `0/1/3/4` 코어에서 실행될 것임을 나타냅니다.


### setAffinity()
정적 방법으로 현재 스레드의 `CPU` 친화성을 설정합니다.

```php
Swoole\Thread->setAffinity(array $cpu_set): bool
```

* **매개변수**
    * `array $cpu_set`
        * 기능: `CPU` 코어의 리스트입니다. 예를 들어 `[0, 1, 3, 4]`
        * 기본값: 없음.
        * 기타값: 없음.

* **반환값**
    * 성공 시 `true`를, 실패 시 `false`를 반환합니다.


### setName()
정적 방법으로 현재 스레드의 이름을 설정합니다. `ps`, `gdb` 등의 도구를 사용하여 확인하고 디버그할 때 더 친화적인 표시를 제공합니다.

```php
Swoole\Thread->setName(string $name): bool
```

* **매개변수**
    * `string $name`
        * 기능: 스레드 이름
        * 기본값: 없음.
        * 기타값: 없음.

* **반환값**
    * 성공 시 `true`를, 실패 시 `false`를 반환합니다.

```shell
$ ps aux | grep -v grep | grep pool.php
swoole   2226813  0.1  0.1 423860 49024 pts/6    Sl+  17:38   0:00 php pool.php

$ ps -T -p 2226813
    PID    SPID TTY          TIME CMD
2226813 2226813 pts/6     00:00:00 Master Thread
2226813 2226814 pts/6     00:00:00 Worker Thread 0
2226813 2226815 pts/6     00:00:00 Worker Thread 1
2226813 2226816 pts/6     00:00:00 Worker Thread 2
2226813 2226817 pts/6     00:00:00 Worker Thread 3
```


### getNativeId()
스레드의 시스템 `ID`를 얻어 반환합니다. 이는 정수로, 프로세스의 `PID`와 유사합니다.

```php
Swoole\Thread->getNativeId(): int
```

이 함수는 `Linux` 시스템에서만 사용할 수 있으며, `gettid()` 시스템 호출을 통해 운영체의 스레드 `ID`와 유사한 짧은 정수를 얻습니다. 프로세스 스레드가 파괴될 때 운영체에 의해 사용될 수 있습니다.

이 `ID`는 `gdb`, `strace` 디버깅에 사용될 수 있습니다. 예를 들어 `gdb -p $tid`을 통해 디버그할 수 있습니다. 또한 `/proc/{PID}/task/{ThreadNativeId}`를 통해 스레드의 실행 정보를 읽을 수 있습니다.


## 속성


### id

이 객체 속성을 통해 자식스레드의 `ID`를 얻을 수 있으며, 이 속성은 `int` 유형입니다.

> 이 속성은 부모스레드에서만 사용하며, 자식스레드는 `$thread` 객체를 획득할 수 없으며, 스레드의 `ID`를 얻으려면 `Thread::getId()` 정적 방법을 사용해야 합니다.

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```


## 상수

이름 | 역할
---|---
`Thread::HARDWARE_CONCURRENCY` | 하드웨어 병렬 스레드 수, 일반적으로 `CPU` 코어 수입니다.
`Thread::API_NAME` | 스레드 `API` 이름, 예를 들어 `POSIX Threads`입니다.
`Thread::SCHED_OTHER` | 스레드 스케줄링 정책 `SCHED_OTHER`입니다.
`Thread::SCHED_FIFO` | 스레드 스케줄링 정책 `SCHED_FIFO`입니다.
`Thread::SCHED_RR` | 스레드 스케줄링 정책 `SCHED_RR`입니다.
`Thread::SCHED_BATCH` | 스레드 스케줄링 정책 `SCHED_BATCH`입니다.
`Thread::SCHED_ISO` | 스레드 스케줄링 정책 `SCHED_ISO`입니다.
`Thread::SCHED_IDLE` | 스레드 스케줄링 정책 `SCHED_IDLE`입니다.
`Thread::SCHED_DEADLINE` | 스레드 스케줄링 정책 `SCHED_DEADLINE`입니다.
