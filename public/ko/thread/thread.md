# Swoole\Thread <!-- {docsify-ignore-all} -->

6.0 버전부터 멀티스레드 지원이 제공되며, 스레드 `API`를 사용하여 멀티프로세스를 대체할 수 있습니다. 멀티프로세스에 비해 `Thread`은 더 풍부한 병렬 데이터 컨테이너를 제공하고, 게임 서버나 통신 서버 개발 시 더욱 편리합니다.



- `PHP`은 `ZTS` 모드여야 하며, `PHP`编译시 `--enable-zts` 옵션을 추가해야 합니다.
- `Swoole`编译시 `--enable-swoole-thread` 컴파일 옵션을 추가해야 합니다.


## 자원 격리

`Swoole` 스레드는 `Node.js Worker Thread`와 유사하며, 자식스레드에서는 완전히 새로운 `ZendVM` 환경이 만들어집니다. 자식스레드는 부모스레드로부터 어떠한 자원도 상속하지 않으므로, 자식스레드에서는 다음 내용이 초기화되어 있어 재생성하거나 설정해야 합니다.



- 이미 로딩된 `PHP` 파일은 `include/require`로 재로딩해야 합니다.

- `autoload` 함수는 재등록해야 합니다.

- 클래스, 함수, 상수는 초기화되어 있어 `PHP` 파일을 재로딩하여 생성해야 합니다.

- 글로벌 변수, 예를 들어 `$GLOBALS`, `$_GET/$_POST` 등은 재설정됩니다.

- 클래스의 정적 속성, 함수의 정적 변수는 초기값으로 재설정됩니다.

- 일부 `php.ini` 옵션, 예를 들어 `error_reporting()`은 자식스레드에서 재설정해야 합니다.


## 사용할 수 없는 기능

멀티스레드 모드에서 다음 기능은 메인스레드에서만 작동하며, 자식스레드에서는 실행할 수 없습니다:



- `swoole_async_set()` 스레드 매개변수를 변경하는 것

- `Swoole\Runtime::enableCoroutine()` 및 `Swoole\Runtime::setHookFlags()`

- 신호 监听은 메인스레드에서만 설정할 수 있으며, `Process::signal()` 및 `Coroutine\System::waitSignal()`은 자식스레드에서 사용할 수 없습니다.
- 비동기 서버는 메인스레드에서만 만들 수 있으며, `Server`, `Http\Server`, `WebSocket\Server` 등은 자식스레드에서 사용할 수 없습니다.

이 외에도, 멀티스레드 모드에서 `Runtime Hook`가 활성화되면 중지할 수 없습니다.


## 치명적 오류
메인스레드가 종료될 때, 활발한 자식스레드가 여전히 존재하는 경우 치명적 오류가 발생하며, 종료 상태코드는 `200`이며, 오류 메시지는 다음과 같습니다:
```
Fatal Error: 2 active threads are running, cannot exit safely.
```


## 스레드 지원이 활성화 되었는지 확인 방법

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)`는 스레드 안전이 활성화되어 있음을 나타냅니다.

```shell
php --ri swoole

swoole
Swoole => enabled
thread => enabled
```

`thread => enabled`는 멀티스레드 지원이 활성화되어 있음을 나타냅니다.


### 멀티스레드 생성
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

// 메인스레드에는 스레드 매개변수가 없으므로 $args는 null
if (empty($args)) {
    # 메인스레드
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # 자식스레드
    echo "Thread #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```




### 스레드 + 서버(비동기 스타일)

- 모든 작업 프로세스는 스레드를 통해 실행되며, `Worker`, `Task Worker`, `User Process`를 포함합니다.

- `SWOOLE_THREAD` 운영 모드가 추가되었으며, 활성화되면 프로세스를 스레드로 대체하여 실행합니다.

- [bootstrap](/server/setting?id=bootstrap)와 [init_arguments](/server/setting?id=init_arguments) 두 가지 설정이 추가되어 작업 스레드의 입구 스크립트 파일, 스레드 공유 데이터를 설정할 수 있습니다.
- `Server`는 메인스레드에서만 생성해야 하며, 콜백 함수 내에서 새로운 `Thread`을 생성하여 다른 작업을 수행할 수 있습니다.
- `Server::addProcess()` 프로세스 객체는 표준 입출부 재정향을 지원하지 않습니다.

```php
use Swoole\Process;
use Swoole\Thread;
use Swoole\Http\Server;

$http = new Server("0.0.0.0", 9503, SWOOLE_THREAD);
$http->set([
    'worker_num' => 2,
    'task_worker_num' => 3,
    'bootstrap' => __FILE__,
    // init_arguments를 통해 스레드 간의 데이터 공유를 구현합니다.
    'init_arguments' => function () use ($http) {
        $map = new Swoole\Thread\Map;
        return [$map];
    }
]);

$http->on('Request', function ($req, $resp) use ($http) {
    $resp->end('hello world');
});

$http->on('pipeMessage', function ($http, $srcWorkerId, $msg) {
    echo "[worker#" . $http->getWorkerId() . "]\treceived pipe message[$msg] from " . $srcWorkerId . "\n";
});

$http->addProcess(new Process(function () {
   echo "user process, id=" . Thread::getId();
   sleep(2000);
}));

$http->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
    var_dump($taskId, $srcWorkerId, $data);
    return ['result' => uniqid()];
});

$http->on('Finish', function ($server, $taskId, $data) {
    var_dump($taskId, $data);
});

$http->on('WorkerStart', function ($serv, $wid) {
    // Swoole\Thread::getArguments()를 통해 설정된 init_arguments에서 전달되는 공유 데이터를 가져옵니다.
    var_dump(Thread::getArguments(), $wid);
});

$http->on('WorkerStop', function ($serv, $wid) {
    var_dump('stop: T' . Thread::getId());
});

$http->start();
```
