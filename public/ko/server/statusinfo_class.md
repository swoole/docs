# Swoole\Server\StatusInfo

여기는 `Swoole\Server\StatusInfo`에 대한 자세한 소개입니다.


## 속성


### $worker_id
현재 `worker` 프로세스 ID를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\StatusInfo->worker_id
```


### $worker_pid
현재 `worker` 프로세스의 부모 프로세스 ID를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\StatusInfo->worker_pid
```


### $status
프로세스 상태 `status`를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\StatusInfo->status
```


### $exit_code
프로세스 종료 상태 코드 `exit_code`를 반환합니다. 이 속성은 `int` 유형의 정수로, 범위는 `0-255`입니다.

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
프로세스가 종료된 신호 `signal`을 반환합니다. 이 속성은 `int` 유형의 정수입니다.
