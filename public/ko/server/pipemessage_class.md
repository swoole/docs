# Swoole\Server\PipeMessage

이곳은 `Swoole\Server\PipeMessage`에 대한 상세한 소개입니다.


## 속성


### $source_worker_id
데이터의 원래 제공자 측의 `worker` 프로세스 ID를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\PipeMessage->source_worker_id
```


### $dispatch_time
해당 요청 데이터의 도착 시간 `dispatch_time`을 반환합니다. 이 속성은 `double` 유형입니다.

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
해당 연결이 전달하는 데이터 `data`를 반환합니다. 이 속성은 `string` 유형의 문자열입니다.

```php
Swoole\Server\PipeMessage->data
```
