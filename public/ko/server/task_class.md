# Swoole\Server\Task

여기에 `Swoole\Server\Task`에 대한 자세한 소개가 있습니다. 이 클래스는 매우 간단하지만, `new Swoole\Server\Task()`를 통해 `Task` 객체를 얻을 수는 없습니다. 이러한 객체는 전혀 서버 정보를 포함하지 않으며, `Swoole\Server\Task`의 어떠한 메서드를 실행해도 치명적인 오류가 발생합니다.

```shell
/home/task.php에서 Swoole\Server\Task의 잘못된 인스턴스입니다.
```

## 속성

### $data
`worker` 프로세스가 `task` 프로세스에 전달하는 데이터 `data`로, 이 속성은 `string` 유형의 문자열입니다.

```php
Swoole\Server\Task->data
```

### $dispatch_time
해당 데이터가 `task` 프로세스에 도착한 시간 `dispatch_time`을 나타내는 속성으로, 이 속성은 `double` 유형입니다.

```php
Swoole\Server\Task->dispatch_time
```

### $id
해당 데이터가 `task` 프로세스에 도착한 시간 `dispatch_time`을 나타내는 속성으로, 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Task->id
```

### $worker_id
해당 데이터가 어느 `worker` 프로세스에서 온지를 나타내는 속성으로, 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Task->worker_id
```

### $flags
해당 비동기 작업의 일부 플래그 정보 `flags`로, 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Task->flags
```

?> `flags`가 반환하는 결과는 다음과 같은 유형들입니다:  
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCK는 이것이 `Worker` 프로세스가 `task` 프로세스에 보낸 것이 아니라는 것을 나타내며, 이때 `onTask` 이벤트에서 `Swoole\Server::finish()`를 호출하면 경고가 발생합니다.  
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCK는 `Swoole\Server::finish()`의 마지막 콜백 함수가 `null`이 아니라는 것을 나타내며, `onFinish` 이벤트는 실행되지 않고 이 콜백 함수만 실행됩니다. 
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCK는 작업을 코루틴 형태로 처리할 것이라는 것을 나타냅니다. 
  - SW_TASK_NONBLOCK는 기본값으로, 위의 세 가지 상황이 모두 적용되지 않은 경우입니다.

## 메서드

### finish()

[Task 프로세스](/learn?id=taskworker进程)에서 `Worker` 프로세스에 작업이 완료되었음을 알리는 데 사용됩니다. 이 함수는 결과 데이터를 `Worker` 프로세스에 전달할 수 있습니다.

```php
Swoole\Server\Task->finish(mixed $data): bool
```

  * **매개변수**

    * `mixed $data`: 작업 처리의 결과 내용
    * 기본값: 없음
    * 기타 값: 없음

  * **설명**
    * `finish` 메서드는 여러 번 연속으로 호출될 수 있으며, `Worker` 프로세스는 여러 번 [onFinish](/server/events?id=onfinish) 이벤트를 트리거합니다.
    * [onTask](/server/events?id=ontask) 콜백 함수에서 `finish` 메서드를 호출한 후에도 `return` 데이터는 [onFinish](/server/events?id=onfinish) 이벤트를 트리거합니다.
    * `Swoole\Server\Task->finish`는 선택적입니다. `Worker` 프로세스가 작업 실행 결과를 신경 쓰지 않는다면 이 함수를 호출할 필요가 없습니다.
    * [onTask](/server/events?id=ontask) 콜백 함수에서 `return` 문자열을 사용하는 것은 `finish`를 호출하는 것과 동일합니다.

  * **주의**

  !> `Swoole\Server\Task->finish` 함수를 사용하려면 `Server`에 [onFinish](/server/events?id=onfinish) 콜백 함수를 설정해야 합니다. 이 함수는 [Task 프로세스](/learn?id=taskworker进程)의 [onTask](/server/events?id=ontask) 콜백에서만 사용할 수 있습니다.

### pack()

주어진 데이터를序列화합니다.

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

  * **매개변수**

    * `mixed $data`: 작업 처리의 결과 내용
    * 기본값: 없음
    * 기타 값: 없음

  * **반환값**
    * 성공 시序列화된 결과를 반환합니다. 

### unpack()

주어진 데이터를 역序列화합니다.

```php
Swoole\Server\Task->unpack(string $data): mixed
```

  * **매개변수**

    * `string $data`: 역序列화할 데이터
    * 기본값: 없음
    * 기타 값: 없음

  * **반환값**
    * 성공 시 역序列화된 결과를 반환합니다. 

## 사용 예시
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
