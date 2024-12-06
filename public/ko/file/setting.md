# 설정

`Swoole`는 몇 가지 핵심 매개변수를 설정하여 비동기 파일 조작의 특성을 변경할 수 있으며, `swoole_async_set` 또는 `Swoole\Server->set()`를 통해 설정할 수 있습니다.

예시:

```php
<?php
swoole_async_set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);
```

### aio_core_worker_num

?> 스레드 풀의 최소 스레드 수를 설정하며, 기본값은 `CPU 코어 수`입니다.

### aio_worker_num

?> 스레드 풀의 최대 스레드 수를 설정하며, 기본값은 `CPU 코어 수 * 8`입니다.

### aio_max_wait_time

?> 스레드 풀 내 스레드의 최대 대기 시간을 설정하며, 기본값은 `0`입니다.

### aio_max_idle_time

?> 스레드 풀 내 스레드의 최대 여유 시간을 설정하며, 기본값은 `1초`입니다.

### iouring_entries

?> `io_uring`의 대기队列 크기를 설정하며, 기본값은 `8192`입니다. 전달된 값이 `2의 거듭제곱`이 아닐 경우, 커널은 그값에 가장 가까운, 그 값보다 큰 `2의 거듭제곱`으로 수정합니다.

!> 전달된 값이 너무 크면 커널은 예외를 던지고 프로그램을 종료합니다.

!> 시스템에 `liburing`이 설치되어 있고 `Swoole`을编译할 때 `--enable-iouring` 옵션을 켜야 사용할 수 있습니다.

!> 시스템에 `liburing`이 설치되어 있고 `Swoole v6.0` 이상 버전을编译하여 `--enable-iouring` 옵션을 켜야 사용할 수 있습니다.
