# Swoole\Server\TaskResult

여기는 `Swoole\Server\TaskResult`에 대한 자세한 소개입니다.


## 속성


### $task_id
해당 작업이 수행된 `Reactor` 스레드의 ID를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\TaskResult->task_id
```


### $task_worker_id
해당 실행 결과가 어느 `task` 프로세스에서 온지를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\TaskResult->task_worker_id
```


### $dispatch_time
해당 연결이 전달한 데이터 `data`를 반환합니다. 이 속성은 `?string` 유형의 문자열입니다.

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
해당 연결이 전달한 데이터 `data`를 반환합니다. 이 속성은 `string` 유형의 문자열입니다.

```php
Swoole\Server\StatusInfo->data
```
