# Swoole\Process

Swoole가 제공하는 프로세스 관리 모듈로, PHP의 `pcntl`를 대체합니다.

!> 이 모듈은 비교적 하단 레벨로, 운영 체제 프로세스 관리를 포장한 것으로, 사용자는 `Linux` 시스템 멀티 프로세스 프로그래밍 경험이 필요합니다.

PHP가 내장한 `pcntl`는 많은 부족함이 있습니다. 예를 들어:

* 프로세스 간 통신 기능이 제공되지 않습니다.
* 표준 입력과 표준 출력을 리디렉션하는 기능이 지원되지 않습니다.
* `fork`와 같은 원시적인 인터페이스만 제공되어 있어, 사용하기가 잘못될 수 있습니다.

`Process`는 `pcntl`보다 더 강력한 기능과 사용하기 쉬운 `API`를 제공하여, PHP가 멀티 프로세스 프로그래밍에서 더욱 쉽게 사용할 수 있도록 합니다.

`Process`는 다음과 같은 특징을 제공합니다:

* 프로세스 간 통신을 편리하게 구현할 수 있습니다.
* 표준 입력과 표준 출력을 리디렉션하고, 자식 프로세스 내에서 `echo`는 화면에 출력되지 않고 파이프에 쓰입니다. 키보드 입력을 파이프로 리디렉션하여 데이터를 읽을 수 있습니다.
* [exec](/process/process?id=exec) 인터페이스를 제공하여, 생성된 프로세스가 다른 프로그램을 실행할 수 있으며, 원래 PHP 부모 프로세스와 쉽게 통신할 수 있습니다.
* 코루틴 환경에서는 `Process` 모듈을 사용할 수 없으며, [runtime hook](/coroutine/proc_open) + `proc_open`을 사용하여 구현할 수 있습니다.


### 사용 예제

  * 3개의 자식 프로세스를 만들고, 부모 프로세스가 wait로 프로세스를 회수합니다.
  * 부모 프로세스가 예외로 종료될 경우, 자식 프로세스는 계속 실행하여 모든 작업을 완료한 후에 종료됩니다.

```php
use Swoole\Process;

for ($n = 1; $n <= 3; $n++) {
    $process = new Process(function () use ($n) {
        echo '자식 #' . getmypid() . " 시작 및 {$n}초休眠" . PHP_EOL;
        sleep($n);
        echo '자식 #' . getmypid() . ' 종료' . PHP_EOL;
    });
    $process->start();
}
for ($n = 3; $n--;) {
    $status = Process::wait(true);
    echo "#{$status['pid']}을 재사용하였습니다., 코드={$status['code']}, 신호={$status['signal']}" . PHP_EOL;
}
echo '부모 #' . getmypid() . ' 종료' . PHP_EOL;
```


## 속성


### pipe

[unixSocket](/learn?id= 什么是IPC)의 파일 디스크립터입니다.

```php
public int $pipe;
```


### msgQueueId

메시지 큐의 `id`입니다.

```php
public int $msgQueueId;
```


### msgQueueKey

메시지 큐의 `key`입니다.

```php
public string $msgQueueKey;
```


### pid

현재 프로세스의 `pid`입니다.

```php
public int $pid;
```


### id

현재 프로세스의 `id`입니다.

```php
public int $id;
```


## 상수

매개변수 | 역할
---|---
Swoole\Process::IPC_NOWAIT | 메시지 큐에 데이터가 없을 경우 즉시 반환
Swoole\Process::PIPE_READ | 읽기 소켓을 닫습니다.
Swoole\Process::PIPE_WRITE | 쓰기 소켓을 닫습니다.


## 방법


### __construct()

생성자입니다.

```php
Swoole\Process->__construct(callable $function, bool $redirect_stdin_stdout = false, int $pipe_type = SOCK_DGRAM, bool $enable_coroutine = false)
```

* **매개변수** 

  * **`callable $function`**
    * **기능** : 자식 프로세스가 생성되고 성공한 후에 실행할 함수입니다.【하단에서 자동으로 함수를 객체의 `callback` 속성에 저장합니다. 주의합니다, 이 속성은 `private`로 Class의 사적인 것입니다.】
    * **기본값** : 없음
    * **기타값** : 없음

  * **`bool $redirect_stdin_stdout`**
    * **기능** : 자식 프로세스의 표준 입력과 표준 출력을 리디렉션합니다.【해당 옵션을 활성화하면, 자식 프로세스 내에서 출력된 내용은 화면에 출력되지 않고 부모 프로세스의 파이프에 쓰입니다. 키보드 입력을 읽는 것은 파이프에서 데이터를 읽는 것으로 변경됩니다. 기본적으로 블록 모드로 읽습니다. [exec()](/process/process?id=exec) 방법 내용을 참고하세요】
    * **기본값** : 없음
    * **기타값** : 없음

  * **`int $pipe_type`**
    * **기능** : [unixSocket](/learn?id= 什么是IPC) 유형【 `$redirect_stdin_stdout`를 활성화하면, 이 옵션은 사용자 매개변수를 무시하고 강제적으로 `SOCK_STREAM`로 설정합니다. 자식 프로세스 내에 프로세스 간 통신이 없다면, `0`로 설정할 수 있습니다】
    * **기본값** : `SOCK_DGRAM`
    * **기타값** : `0`, `SOCK_STREAM`

  * **`bool $enable_coroutine`**
    * **기능** : `callback function`에서 코루틴을 활성화합니다. 활성화되면 자식 프로세스의 함수에서 직접 코루틴 API를 사용할 수 있습니다
    * **기본값** : `false`
    * **기타값** : `true`
    * **버전 영향** : Swoole 버전 >= v4.3.0

* **[unixSocket](/learn?id= 什么是IPC) 유형**


unixSocket 유형 | 설명
---|---
0 | 생성하지 않습니다.
1 | `SOCK_STREAM](/learn?id= 什么是IPC)` 유형의 unixSocket을 생성합니다.
2 | `SOCK_DGRAM](/learn?id= 什么是IPC)` 유형의 unixSocket을 생성합니다.



### useQueue()

메시지 큐를 사용하여 프로세스 간 통신을 합니다.

```php
Swoole\Process->useQueue(int $key = 0, int $mode = SWOOLE_MSGQUEUE_BALANCE, int $capacity = -1): bool
```

* **매개변수** 

  * **`int $key`**
    * **기능** : 메시지 큐의 key입니다. 만약 전달된 값이 0보다 작거나 같다면, 하단에서 `ftok` 함수를 사용하여 현재 실행 중인 파일의 파일명을 매개변수로 하여 해당 key를 생성합니다.
    * **기본값** : `0`
    * **기타값** : `없음`

  * **`int $mode`**
    * **기능** : 프로세스 간 통신 모드입니다.
    * **기본값** : `SWOOLE_MSGQUEUE_BALANCE`, `Swoole\Process::pop()`는 큐의 첫 번째 메시지를 반환하고, `Swoole\Process::push()`는 메시지에 특정 유형을 추가하지 않습니다.
    * **기타값** : `SWOOLE_MSGQUEUE_ORIENT`, `Swoole\Process::pop()`는 큐에서 현재 `프로세스 id + 1` 유형의 메시지를 반환하고, `Swoole\Process::push()`는 메시지에 `프로세스 id + 1` 유형을 추가합니다.

  * **`int $capacity`**
    * **기능** : 메시지 큐가 저장할 수 있는 메시지의 최대 수량입니다.
    * **기본값** : `-1`
    * **기타값** : `없음`

* **주의 사항**

  * 메시지 큐에 데이터가 없을 경우, `Swoole\Porcess->pop()`는 계속해서 블록되거나, 메시지 큐에 새로운 데이터를 저장할 공간이 없을 경우, `Swoole\Porcess->push()`도 계속해서 블록됩니다. 블록하고 싶지 않다면, `$mode`의 값은 `SWOOLE_MSGQUEUE_BALANCE|Swoole\Process::IPC_NOWAIT` 또는 `SWOOLE_MSGQUEUE_ORIENT|Swoole\Process::IPC_NOWAIT`여야 합니다.


### statQueue()

메시지 큐의 상태를 가져옵니다.

```php
Swoole\Process->statQueue(): array|false
```

* **반환값** 

  * 성공 시 배열을 반환합니다. 배열에는 두 개의 키-값 쌍이 포함되어 있습니다. `queue_num`은 현재 큐 내 메시지의 총 수를 나타내고, `queue_bytes`는 현재 큐 내 메시지의 총 크기를 나타냅니다.
  * 실패 시 `false`를 반환합니다.


### freeQueue()

메시지 큐를 파괴합니다.

```php
Swoole\Process->freeQueue(): bool
```

* **반환값** 

  * 성공 시 `true`를 반환합니다.
  * 실패 시 `false`를 반환합니다.


### pop()

메시지 큐에서 데이터를 가져옵니다.

```php
Swoole\Process->pop(int $size = 65536): string|false
```

* **매개변수** 

  * **`int $size`**
    * **기능** : 가져오는 데이터의 크기입니다.
    * **기본값** : `65536`
    * **기타값** : `없음`


* **반환값** 

  * 성공 시 `string`을 반환합니다.
  * 실패 시 `false`를 반환합니다.

* **주의 사항**

  * 메시지 큐 유형이 `SW_MSGQUEUE_BALANCE`인 경우, 큐의 첫 번째 메시지를 반환합니다.
  * 메시지 큐 유형이 `SW_MSGQUEUE_ORIENT`인 경우, 큐의 첫 번째 `프로세스 id + 1` 유형의 메시지를 반환합니다.
### 푸시()

메시지 큐에 데이터를 보냅니다.

```php
Swoole\Process->push(string $data): bool
```

* **매개변수**

  * **`string $data`**
    * **기능**: 보낼 데이터입니다.
    * **기본값**: ``
    * **기타값**: `없음`


* **반환값**

  * 성공 시 `true`를 반환합니다.
  * 실패 시 `false`를 반환합니다.

* **주의사항**

  * 메시지 큐의 유형이 `SW_MSGQUEUE_BALANCE`일 경우, 데이터는 메시지 큐에 직접 삽입됩니다.
  * 메시지 큐의 유형이 `SW_MSGQUEUE_ORIENT`일 경우, 데이터는 현재 `프로세스 ID + 1`의 유형을 가진 것으로 추가됩니다.


### setTimeout()

메시지 큐의 읽기/쓰기 시간을 설정합니다.

```php
Swoole\Process->setTimeout(float $seconds): bool
```

* **매개변수**

  * **`float $seconds`**
    * **기능**: 시간 초과값
    * **기본값**: `없음`
    * **기타값**: `없음`


* **반환값**

  * 성공 시 `true`를 반환합니다.
  * 실패 시 `false`를 반환합니다.


### setBlocking()

메시지 큐 소켓이 막힐지 여부를 설정합니다.

```php
Swoole\Process->setBlocking(bool $$blocking): void
```

* **매개변수**

  * **`bool $blocking`**
    * **기능**: 막힐지 여부, `true`는 막히고 `false`는 막히지 않습니다
    * **기본값**: `없음`
    * **기타값**: `없음`

* **주의사항**

  * 새로 생성된 프로세스 소켓은 기본적으로 막혀 있어서, UNIX 도메인 소켓 통신을 할 때는 메시지를 보내면서 프로세스를 막고, 메시지를 읽으면 프로세스를 막힙니다.


### write()

부모 프로세스와 자식 프로세스 간 메시지 쓰기(UNIX 도메인 소켓).

```php
Swoole\Process->write(string $data): false|int
```

* **매개변수**

  * **`string $data`**
    * **기능**: 쓰려는 데이터
    * **기본값**: `없음`
    * **기타값**: `없음`


* **반환값**

  * 성공 시 성공 쓰인 바이트 수를 `int`로 반환합니다.
  * 실패 시 `false`를 반환합니다.


### read()

부모 프로세스와 자식 프로세스 간 메시지 읽기(UNIX 도메인 소켓).

```php
Swoole\Process->read(int $size = 8192): false|string
```

* **매개변수**

  * **`int $size`**
    * **기능**: 읽으려는 데이터의 크기
    * **기본값**: `8192`
    * **기타값**: `없음`


* **반환값**

  * 성공 시 `string`을 반환합니다.
  * 실패 시 `false`를 반환합니다.


### set()

매개변수를 설정합니다.

```php
Swoole\Process->set(array $settings): void
```

`enable_coroutine`를 이용하여 코루틴을 사용할지 여부를 제어할 수 있으며, 이는 생성자의 네 번째 매개변수의 역할과 동일합니다.

```php
Swoole\Process->set(['enable_coroutine' => true]);
```

!> Swoole 버전 >= v4.4.4에서 사용할 수 있습니다.


### start()

`fork` 시스템 호출을 실행하여 자식 프로세스를 시작합니다. `Linux` 시스템에서 프로세스를 생성하는 데는 수백 마이크로초가 소요됩니다.

```php
Swoole\Process->start(): int|false
```

* **반환값**

  * 성공 시 자식 프로세스의 `PID`를 반환합니다.
  * 실패 시 `false`를 반환합니다. [swoole_errno](/functions?id=swoole_errno)와 [swoole_strerror](/functions?id=swoole_strerror)를 이용하여 오류 코드와 오류 메시지를 얻을 수 있습니다.

* **주의사항**

  * 자식 프로세스는 부모 프로세스의 메모리와 파일 핸들을 상속받습니다.
  * 자식 프로세스가 시작될 때, 부모 프로세스에서 상속받은 [EventLoop](/learn?id=什么是eventloop), [Signal](/process/process?id=signal), [Timer](/timer)를 초기화합니다.
  
  !> 실행 후 자식 프로세스는 부모 프로세스의 메모리와 자원을 유지합니다. 예를 들어 부모 프로세스에서 redis 연결을 만들었다면, 자식 프로세스에서도 해당 객체를 유지하며, 모든 조작은 같은 연결에 대한 것입니다. 다음은 이를 설명하는 예입니다.

```php
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);

function callback_function() {
    swoole_timer_after(1000, function () {
        echo "hello world\n";
    });
    global $redis;//같은 연결
};

swoole_timer_tick(1000, function () {
    echo "parent timer\n";
});//상속받지 않음

Swoole\Process::signal(SIGCHLD, function ($sig) {
    while ($ret = Swoole\Process::wait(false)) {
        // create a new child process
        $p = new Swoole\Process('callback_function');
        $p->start();
    }
});

// create a new child process
$p = new Swoole\Process('callback_function');

$p->start();
```

!> 1. 자식 프로세스가 시작되면 자동으로 부모 프로세스에서 [Swoole\Timer::tick](/timer?id=tick)로 생성한 타이머, [Process::signal](/process/process?id=signal)로 등록한 신호, [Swoole\Event::add](/event?id=add)로 등록한 이벤트 리스너를 제거합니다;  
2. 자식 프로세스는 부모 프로세스가 생성한 `$redis` 연결 객체를 상속받으며, 부모와 자식 프로세스가 사용하는 연결은 같습니다.


### exportSocket()

`unixSocket`를 `Swoole\Coroutine\Socket` 객체로 내보내며, 이후 `Swoole\Coroutine\socket` 객체의 방법을 이용하여 프로세스 간 통신을 합니다. 구체적인 사용법은 [Coroutine\socket](/coroutine_client/socket)와 [IPC通讯](/learn?id=什么是IPC)를 참고하세요.

```php
Swoole\Process->exportSocket(): Swoole\Coroutine\Socket|false
```

!> 이 방법을 여러 번 호출해도 반환되는 객체는 같습니다;  
`exportSocket()`로 내보낸 `socket`은 새로운 `fd`이며, 내보낸 `socket`을 닫을 경우 프로세스의 원래 파이프는 영향을 받지 않습니다.  
`Swoole\Coroutine\Socket` 객체이기 때문에 [코루틴 컨테이너](/coroutine/scheduler)에서 사용해야 하며, 따라서 Swoole\Process 생성자의 `$enable_coroutine` 매개변수는 반드시 `true`여야 합니다.  
같은 부모 프로세스에서 `Swoole\Coroutine\Socket` 객체를 사용하고 싶다면, 수동적으로 `Coroutine\run()`을 호출하여 코루틴 컨테이너를 생성해야 합니다.

* **반환값**

  * 성공 시 `Coroutine\Socket` 객체를 반환합니다.
  * 프로세스에 unixSocket가 생성되지 않았거나 조작에 실패하여 `false`를 반환합니다.

* **사용 예시**

간단한 부모-자식 프로세스 간 통신을 구현한 예:  

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    echo $socket->recv();
    $socket->send("hello master\n");
    echo "proc1 stop\n";
}, false, 1, true);

$proc1->start();

//부모 프로세스에서 코루틴 컨테이너를 생성합니다
run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    $socket->send("hello pro1\n");
    var_dump($socket->recv());
});
Process::wait(true);
```

간단한 통신 예시:

```php
use Swoole\Process;
use Swoole\Timer;
use function Swoole\Coroutine\run;

$process = new Process(function ($proc) {
    Timer::tick(1000, function () use ($proc) {
        $socket = $proc->exportSocket();
        $socket->send("hello master\n");
        echo "child timer\n";
    });
}, false, 1, true);

$process->start();

run(function() use ($process) {
    Process::signal(SIGCHLD, static function ($sig) {
        while ($ret = Swoole\Process::wait(false)) {
            /* clean up then event loop will exit */
            Process::signal(SIGCHLD, null);
            Timer::clearAll();
        }
    });
    /* your can run your other async or coroutine code here */
    Timer::tick(500, function () {
        echo "parent timer\n";
    });

    $socket = $process->exportSocket();
    while (1) {
        var_dump($socket->recv());
    }
});
```
!> 기본 유형은 `SOCK_STREAM`이므로 TCP 패킷 경계 문제를 처리해야 합니다. [Coroutine\socket](/coroutine_client/socket)의 `setProtocol()` 방법을 참고하세요.  

`SOCK_DGRAM` 유형으로 IPC 통신을 할 경우, TCP 패킷 경계 문제를 처리할 필요가 없으므로, [IPC通讯](/learn?id=什么是IPC)를 참고하세요:

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

//IPC 통신은 SOCK_DGRAM 유형의 소켓을 사용하더라도 sendto / recvfrom 함수를 사용하지 않고, send/recv만으로 충분합니다.
$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    while (1) {
        var_dump($socket->send("hello master\n"));
    }
    echo "proc1 stop\n";
}, false, 2, 1);//생성자 pipe type를 2로 전달하면 SOCK_DGRAM이 됩니다.

$proc1->start();

run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    Swoole\Coroutine::sleep(5);
    var_dump(strlen($socket->recv()));//한 번의 recv는 "hello master\n" 문자열만 받을 것이며, 여러 개의 "hello master\n" 문자열이 받히지는 않습니다.
});

Process::wait(true);
```
### 이름()

프로세스 이름을 변경합니다. 이 함수는 [swoole_set_process_name](/functions?id=swoole_set_process_name)의 별명입니다.

```php
Swoole\Process->name(string $name): bool
```

!> `exec`를 실행한 후에는 프로세스 이름이 새 프로그램에 의해 다시 설정됩니다; `name` 메서드는 `start` 이후의 자식 프로세스 콜백 함수에서 사용해야 합니다.


### exec()

외부 프로그램을 실행합니다. 이 함수는 `exec` 시스템 호출의 포장입니다.

```php
Swoole\Process->exec(string $execfile, array $args);
```

* **매개변수** 

  * **`string $execfile`**
    * **기능** : 실행 가능한 파일의 절대 경로 지정, 예를 들어 `"/usr/bin/python"`
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`array $args`**
    * **기능** : `exec`의 매개변수 리스트【예를 들어 `array('test.py', 123)`는 `python test.py 123`과 동일】
    * **기본값** : 없음
    * **기타 값** : 없음

성공 시 현재 프로세스의 코드 세그먼트가 새 프로그램에 의해 교체됩니다. 자식 프로세스는 다른 프로그램으로 변모합니다. 부모 프로세스와 현재 프로세스는 여전히 부모-자식 프로세스 관계입니다.

부모 프로세스와 새 프로세스는 표준 입출력을 통해 통신할 수 있으며, 표준 입출력 리디렉션을 활성화해야 합니다.

!> `$execfile`은 절대 경로를 사용해야 하며, 그렇지 않으면 파일이 존재하지 않는 오류가 발생합니다;  
`exec` 시스템 호출은 지정된 프로그램이 현재 프로그램을 덮어 쓰기 때문에 자식 프로세스는 표준 출력을 읽고 부모 프로세스와 통신해야 합니다;  
`redirect_stdin_stdout = true`가 지정되지 않은 경우, `exec`를 실행한 후 자식 프로세스와 부모 프로세스는 통신할 수 없습니다.

* **사용 예시**

예 1: [Swoole\Server](/server/init)를 Swoole\Process에서 생성한 자식 프로세스에서 사용할 수 있지만, 안전을 위해 `$process->start`로 프로세스를 생성한 후에 `$worker->exec()`를 호출하여 실행해야 합니다. 코드는 다음과 같습니다:

```php
$process = new Swoole\Process('callback_function', true);

$pid = $process->start();

function callback_function(Swoole\Process $worker)
{
    $worker->exec('/usr/local/bin/php', array(__DIR__.'/swoole_server.php'));
}

Swoole\Process::wait();
```

예 2: Yii 프로그램을 시작합니다

```php
$process = new \Swoole\Process(function (\Swoole\Process $childProcess) {
    // 이러한 작법은 지원되지 않습니다
    // $childProcess->exec('/usr/local/bin/php /var/www/project/yii-best-practice/cli/yii t/index -m=123 abc xyz');

    // exec 시스템 호출을 포장합니다
    // 절대 경로
    // 매개변수는 배열로 나눠져야 합니다
    $childProcess->exec('/usr/local/bin/php', ['/var/www/project/yii-best-practice/cli/yii', 't/index', '-m=123', 'abc', 'xyz']); // exec 시스템 호출
});
$process->start(); // 자식 프로세스를 시작합니다
```

예 3: 부모 프로세스와 `exec` 자식 프로세스가 표준 입출력으로 통신합니다:

```php
// exec - exec 프로세스와 파이프 통신
use Swoole\Process;
use function Swoole\Coroutine\run;

$process = new Process(function (Process $worker) {
    $worker->exec('/bin/echo', ['hello']);
}, true, 1, true); // 표준 입출력 리디렉션을 활성화해야 합니다

$process->start();

run(function() use($process) {
    $socket = $process->exportSocket();
    echo "from exec: " . $socket->recv() . "\n";
});
```

예 4: 셸 명령을 실행합니다

`exec` 메서드는 `PHP`에서 제공하는 `shell_exec`와 다르며, 더 낮은 수준의 시스템 호출 포장입니다. 셸 명령을 실행하려면 다음과 같은 방법을 사용하세요:

```php
$worker->exec('/bin/sh', array('-c', "cp -rf /data/test/* /tmp/test/"));
```


### close()

생성한 [unixSocket](/learn?id= 什么是IPC)를 닫습니다. 

```php
Swoole\Process->close(int $which): bool
```

* **매개변수** 

  * **`int $which`**
    * **기능** : unixSocket는 전향적이므로 어느 쪽을 닫을지 지정합니다【기본은 `0`로, 읽기와 쓰기를 동시에 닫습니다, `1` : 쓰기를 닫습니다, `2` 쓰기를 닫습니다】
    * **기본값** : `0` , 읽기와 쓰기를 모두 닫습니다.
    * **기타 값** : `Swoole/Process::SW_PIPE_CLOSE_READ` 읽기 소켓을 닫습니다, `Swoole/Process::SW_PIPE_CLOSE_WRITE` 쓰기 소켓을 닫습니다,

!> 일부 특별한 상황에서 `Process` 객체는 해제할 수 없으며, 프로세스를 지속적으로 생성하면 연결이 누수됩니다. 이 함수를 호출하면 직접적으로 unixSocket를 닫고 자원을 해제할 수 있습니다.


### exit()

자식 프로세스를 종료합니다.

```php
Swoole\Process->exit(int $status = 0);
```

* **매개변수** 

  * **`int $status`**
    * **기능** : 프로세스 종료 상태 코드【`0`이면 정상 종료이며, 청소 작업을 계속 수행합니다】
    * **기본값** : `0`
    * **기타 값** : 없음

!> 청소 작업에는 다음이 포함됩니다:

  * PHP의 `shutdown_function`
  * 객체 해체 (`__destruct`)
  * 기타 확장의 `RSHUTDOWN` 함수

`$status`가 `0`이 아니면 비정상 종료를 의미하며, 프로세스를 즉시 종료하고 관련 종료 작업을 수행하지 않습니다.

부모 프로세스에서 `Process::wait`를 호출하면 자식 프로세스의 종료 이벤트와 상태 코드를 얻을 수 있습니다.


### kill()

지정된 `pid` 프로세스에 신호를 보냅니다.

```php
Swoole\Process::kill(int $pid, int $signo = SIGTERM): bool
```

* **매개변수** 

  * **`int $pid`**
    * **기능** : 프로세스 `pid`
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`int $signo`**
    * **기능** : 보낼 신호【`$signo=0`은 프로세스가 존재하는지 확인할 수 있으며, 신호를 보내지 않습니다】
    * **기본값** : `SIGTERM`
    * **기타 값** : 없음


### signal()

비동기 신호 수신을 설정합니다.

```php
Swoole\Process::signal(int $signo, callable $callback): bool
```

이 방법은 `signalfd`와 [EventLoop](/learn?id=什么是eventloop)를 기반으로 한 비동기 `IO`로, 막힘 있는 프로그램에서는 사용할 수 없으며, 등록된 수신 콜백 함수가 스케줄되지 않을 수 있습니다;

동기 막힘의 프로그램은 `pcntl` 확장으로 제공되는 `pcntl_signal`을 사용할 수 있습니다;

이미 이 신호의 콜백 함수가 설정되어 있다면, 재설정 시 이전 설정은 덮여집니다.

* **매개변수** 

  * **`int $signo`**
    * **기능** : 신호
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`callable $callback`**
    * **기능** : 콜백 함수【`$callback`가 `null`이면 신호 수신을 제거합니다】
    * **기본값** : 없음
    * **기타 값** : 없음

!> [Swoole\Server](/server/init)에서는 일부 신호 수신을 설정할 수 없으며, 예를 들어 `SIGTERM`과 `SIGALRM`은 사용할 수 없습니다

* **사용 예시**

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
```

!> `v4.4.0` 버전에서 프로세스의 [EventLoop](/learn?id=什么是eventloop)에 신호 수신 이벤트만 있을 경우, 다른 이벤트(예: Timer 타이머 등)이 없다면, 프로세스는 즉시 종료합니다.

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
Swoole\Event::wait();
```

상기 프로그램은 [EventLoop](/learn?id=什么是eventloop)에 진입하지 않고, `Swoole\Event::wait()`는 즉시 반환하며, 프로세스를 종료합니다.
### 대기()

실행 중인 자식 프로세스를 회수합니다.

!> Swoole 버전 >= `v4.5.0`에서 코루outine 버전의 `wait()`를 사용하는 것이 권장됩니다. 자세한 내용은 [Swoole\Coroutine\System::wait()](/coroutine/system?id=wait)를 참고하세요.

```php
Swoole\Process::wait(bool $blocking = true): array|false
```

* **매개변수**

  * **`bool $blocking`**
    * **기능**: Blocking 여부 지정 【기본은 Blocking】
    * **기본값**: `true`
    * **기타값**: `false`

* **반환값**

  * 성공 시 자식 프로세스의 `PID`, 종료 상태 코드, 어떤 신호로 `KILL`되었는지가 포함된 배열을 반환합니다.
  * 실패 시 `false`를 반환합니다.

!> 자식 프로세스가 모두 종료되면, 부모 프로세스는 반드시 `wait()`를 한 번씩 호출하여 회수해야 합니다. 그렇지 않으면 자식 프로세스가 zombie 프로세스가 되어 운영체의 프로세스 자원을 낭비하게 됩니다. 부모 프로세스가 다른 작업을 해야 `wait`에 Blocked될 수 없다면, 부모 프로세스는 종료된 프로세스에 대해 `SIGCHLD` 신호를 등록하여 `wait`을 수행해야 합니다.
`SIGCHILD` 신호가 발생할 때 여러 자식 프로세스가 동시에 종료될 수 있습니다. `wait()`를 비Blocking으로 설정하고 반복해서 `wait`을 호출하여 `false`가 반환될 때까지 계속해야 합니다.

* **예시**

```php
Swoole\Process::signal(SIGCHLD, function ($sig) {
    // 비Blocking 모드여야 합니다.
    while ($ret = Swoole\Process::wait(false)) {
        echo "PID={$ret['pid']}\n";
    }
});
```


### 데몬()

현재 프로세스를 데몬 프로세스로 변환합니다.

```php
Swoole\Process::daemon(bool $nochdir = true, bool $noclose = true): bool
```

* **매개변수**

  * **`bool $nochdir`**
    * **기능**: 현재 디렉토리를 루트 디렉토리로 전환할지 여부 【`true`이면 전환하지 않음】
    * **기본값**: `true`
    * **기타값**: `false`

  * **`bool $noclose`**
    * **기능**: 표준 입출구 파일 디스크립터를 닫을지 여부 【`true`이면 닫지 않음】
    * **기본값**: `true`
    * **기타값**: `false`

!> 데몬 프로세스로 변환 시, 해당 프로세스의 `PID`가 변경됩니다. 현재 `PID`를 얻을 수 있는 `getmypid()` 함수를 사용할 수 있습니다.


### 알람()

고정Precision 타이머로, 운영체의 `setitimer` 시스템 호출을 포장한 것으로, 마이크로초 단위의 타이머를 설정할 수 있습니다. 타이머는 신호를 트리거하며, [Process::signal](/process/process?id=signal)나 `pcntl_signal`와 함께 사용해야 합니다.

!> `alarm`은 [Timer](/timer)와 동시에 사용할 수 없습니다.

```php
Swoole\Process->alarm(int $time, int $type = 0): bool
```

* **매개변수**

  * **`int $time`**
    * **기능**: 타이머 간격 시간 【부정수는 타이머를 제거함】
    * **값의 단위**: 마이크로초
    * **기본값**: 없음
    * **기타값**: 없음

  * **`int $type`**
    * **기능**: 타이머 유형
    * **기본값**: `0`
    * **기타값**:


타이머 유형 | 설명
---|---
0 | 실제 시간을 나타내며, `SIGALRM` 신호를 트리거함
1 | 사용자 공간 CPU 시간을 나타내며, `SIGVTALRM` 신호를 트리거함
2 | 사용자 공간과 커널 공간의 시간을 나타내며, `SIGPROF` 신호를 트리거함

* **반환값**

  * 성공 시 `true`를 반환합니다.
  * 실패 시 `false`를 반환하며, `swoole_errno`를 이용하여 오류 코드를 얻을 수 있습니다.

* **사용 예시**

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

run(function () {
    Process::signal(SIGALRM, function () {
        static $i = 0;
        echo "#{$i}\talarm\n";
        $i++;
        if ($i > 20) {
            Process::alarm(-1);
            Process::kill(getmypid());
        }
    });

    // 100ms
    Process::alarm(100 * 1000);

    while(true) {
        sleep(0.5);
    }
});
```


### 세팅애프리티()

CPU 친화성을 설정하여 프로세스를 특정 CPU 코어에 묶습니다. 

해당 함수의 기능은 프로세스를 일부 CPU 코어에서만 실행하도록 하여, 일부 CPU 자원을 더 중요한 프로세스에 할당하는 것입니다.

```php
Swoole\Process->setAffinity(array $cpus): bool
```

* **매개변수**

  * **`array $cpus`**
    * **기능**: CPU 코어를 묶음 【예: `array(0,2,3)`는 CPU0/CPU2/CPU3를 묶음】
    * **기본값**: 없음
    * **기타값**: 없음


!> - `$cpus` 내의 요소는 CPU 코어 수를 초과할 수 없습니다;  

- `CPU-ID`는 (CPU 코어 수 - `1`)을 초과할 수 없습니다;  

- 해당 함수는 운영체가 CPU 묶기를 지원해야 합니다;  
- [swoole_cpu_num()](/functions?id=swoole_cpu_num)를 이용하여 현재 서버의 CPU 코어 수를 얻을 수 있습니다.


### getAffinity()
프로세스의 CPU 친화성을 가져옵니다.

```php
Swoole\Process->getAffinity(): array
```
반환값은 배열로, 요소는 CPU 코어 수입니다. 예를 들어: `[0, 1, 3, 4]`는 이 프로세스가 CPU의 `0/1/3/4` 코어에서 실행될 것임을 나타냅니다.


### setPriority()

프로세스, 프로세스 그룹, 사용자 프로세스의 우선위를 설정합니다.

!> Swoole 버전 >= `v4.5.9`에서 사용할 수 있습니다.

```php
Swoole\Process->setPriority(int $which, int $priority): bool
```

* **매개변수**

  * **`int $which`**
    * **기능**: 우선위를 변경할 대상 결정
    * **기본값**: 없음
    * **기타값**:


| 상수         | 설명     |
| ------------ | -------- |
| PRIO_PROCESS | 프로세스 |
| PRIO_PGRP    | 프로세스 그룹 |
| PRIO_USER    | 사용자 프로세스 |

  * **`int $priority`**
    * **기능**: 우선위. 값이 작을수록 우선위가 높아집니다
    * **기본값**: 없음
    * **기타값**: `[-20, 20]`

* **반환값**

  * `false`가 반환될 경우, [swoole_errno](/functions?id=swoole_errno)와 [swoole_strerror](/functions?id=swoole_strerror)를 이용하여 오류 코드와 오류 메시지를 얻을 수 있습니다.

### getPriority()

프로세스의 우선위를 가져옵니다.

!> Swoole 버전 >= `v4.5.9`에서 사용할 수 있습니다.

```php
Swoole\Process->getPriority(int $which): int
```
