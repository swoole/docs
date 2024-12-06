# 비동기 작업(Task) 실행

Server 프로그램에서 시간이 많이 소모되는 작업을 수행해야 할 때가 있습니다. 예를 들어 채팅 서버에서 브로드캐스팅을 보내거나, 웹 서버에서 이메일을 보낼 때가 그です. 이러한 함수를 직접 실행하면 현재 프로세스를 막아 서버의 응답 속도가 늦어집니다.

Swoole은 비동기 작업 처리 기능을 제공하며, 비동기 작업을 TaskWorker 프로세스 풀에 제출하여 현재 요청 처리에 영향을 주지 않습니다.

## 프로그램 코드

첫 번째 TCP 서버를 기반으로 하여, [onTask](/server/events?id=ontask)와 [onFinish](/server/events?id=onfinish) 두 가지 이벤트 콜백 함수를 추가하기만 하면 됩니다. 또한 task 프로세스의 수를 설정해야 하며, 작업의 시간과 양에 따라 적절한 수의 task 프로세스를 구성할 수 있습니다.

다음 코드를 task.php에 작성하세요.

```php
$serv = new Swoole\Server('127.0.0.1', 9501);

// 비동기 작업의 작업 프로세스 수를 설정합니다.
$serv->set([
    'task_worker_num' => 4
]);

// 이 콜백 함수는 worker 프로세스에서 실행됩니다.
$serv->on('Receive', function($serv, $fd, $reactor_id, $data) {
    // 비동기 작업을 제출합니다.
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id={$task_id}\n";
});

// 비동기 작업 처리 (이 콜백 함수는 task 프로세스에서 실행됩니다).
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id={$task_id}]".PHP_EOL;
    // 작업 실행 결과를 반환합니다.
    $serv->finish("{$data} -> OK");
});

// 비동기 작업 결과 처리 (이 콜백 함수는 worker 프로세스에서 실행됩니다).
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[{$task_id}] Finish: {$data}".PHP_EOL;
});

$serv->start();
```

`$serv->task()`를 호출한 후, 프로그램은 즉시 반환하고 코드를 계속 실행합니다. onTask 콜백 함수는 Task 프로세스 풀에서 비동기적으로 실행됩니다. 실행이 완료되면 `$serv->finish()`를 호출하여 결과를 반환합니다.

!> finish操作은 선택적이며, 결과를 반환하지 않아도 됩니다. onTask 이벤트에서 `return`을 통해 결과를 반환하면, 이는 Swoole\Server::finish()操作과 동일합니다.
