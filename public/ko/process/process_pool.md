# Swoole\Process\Pool

프로세스 풀은 [Swoole\Server](/server/init)의 Manager가 관리하는 프로세스 모듈을 기반으로 한 것으로, 여러 작업 프로세스를 관리할 수 있습니다. 이 모듈의 핵심 기능은 프로세스 관리로, `Process` 实现의 다중 프로세스에 비해 `Process\Pool`는 더 간결하며, 포장 레벨이 높아 개발자가 너무 많은 코드를 작성하지 않고도 프로세스 관리 기능을 구현할 수 있습니다. [Co\Server](/coroutine/server?id=完整示例)와 함께 사용하면 순수한 코로코어 스타일의, 다중 코어 CPU를 이용하는 서버 프로그램을 만들 수 있습니다.


## 프로세스 간 통신

`Swoole\Process\Pool`은 총 세 가지 프로세스 간 통신 방식을 제공합니다:


### 메시지 대기열
`Swoole\Process\Pool->__construct`의 두 번째 매개변수를 `SWOOLE_IPC_MSGQUEUE`로 설정하면, 메시지 대기열을 사용하여 프로세스 간 통신을 진행합니다. `php sysvmsg` 확장을 통해 정보를 전달할 수 있으며, 메시지의 최대 길이는 `65536`을 초과할 수 없습니다.

* **주의 사항**

  * `sysvmsg` 확장을 사용하여 정보를 전달하려면, 생성자에서 반드시 `msgqueue_key`를 전달해야 합니다.
  * `Swoole`의 기본 레벨은 `sysvmsg` 확장의 `msg_send`의 두 번째 매개변수 `mtype`을 지원하지 않으므로, 임의의 비 `0` 값을 전달하십시오.


### 소켓 통신
`Swoole\Process\Pool->__construct`의 두 번째 매개변수를 `SWOOLE_IPC_SOCKET`로 설정하면, 소켓 통신을 사용합니다. 클라이언트와 서버가 같은 기계에 있지 않다면, 이 방식을 사용하여 통신할 수 있습니다.

[Swoole\Process\Pool->listen()](/process/process_pool?id=listen)方法与를 통해 포트를 감시하고, [Message事件](/process/process_pool?id=on)을 통해 클라이언트에서 보내온 데이터를 수신하며, [Swoole\Process\Pool->write()](/process/process_pool?id=write)方法与를 통해 클라이언트에 응답을 보냅니다.

`Swoole`는 클라이언트가 이 방식으로 데이터를 보내면, 실제 데이터 앞에 4 바이트의 네트워크 바이트 순서 값을 추가해야 합니다.
```php
$msg = 'Hello Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```


### UnixSocket
`Swoole\Process\Pool->__construct`의 두 번째 매개변수를 `SWOOLE_IPC_UNIXSOCK`로 설정하면, [UnixSocket](/learn?id=什么是IPC)를 사용하여 프로세스 간 통신을 진행합니다. **프로세스 간 통신을 위한 강력한 추천 방식입니다**.

이 방식은 매우 간단하며, [Swoole\Process\Pool->sendMessage()](/process/process_pool?id=sendMessage)方法与와 [Message事件](/process/process_pool?id=on)만으로 프로세스 간 통신을 완료할 수 있습니다.

또는 코로코어 모드를 활성화하면, [Swoole\Process\Pool->getProcess()](/process/process_pool?id=getProcess)를 통해 `Swoole\Process` 객체를 획득하고, `Swoole\Process->exportsocket()](/process/process?id=exportsocket)를 통해 `Swoole\Coroutine\Socket` 객체를 획득하여, 이 객체를 사용하여 프로세스 간 통신을 실현할 수 있습니다. 그러나 이때는 [Message事件](/process/process_pool?id=on)을 설정할 수 없습니다.

!> 매개변수와 환경 설정은 [생성자](/process/process_pool?id=__construct)와 [설정 매개변수](/process/process_pool?id=set)를 확인하십시오.


## 상수


상수 | 설명
---|---
SWOOLE_IPC_MSGQUEUE | 시스템 [메시지 대기열](/learn?id=什么是IPC) 통신
SWOOLE_IPC_SOCKET | 소켓 통신
SWOOLE_IPC_UNIXSOCK | [UnixSocket](/learn?id=什么是IPC) 통신(v4.4+)


## 코로코어 지원

v4.4.0 버전에서 코로코어 지원이 추가되었습니다. 자세한 내용은 [Swoole\Process\Pool::__construct](/process/process_pool?id=__construct)를 참고하십시오.


## 사용 예제

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** 현재는 Worker 进程 */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n";
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
```


## 방법


### __construct()

생성자입니다.

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **매개변수** 

  * **`int $worker_num`**
    * **기능**：작업 프로세스의 수를 지정합니다
    * **기본값**：없음
    * **기타 값**：없음

  * **`int $ipc_type`**
    * **기능**：프로세스 간 통신의 방식【기본적으로 `SWOOLE_IPC_NONE`는 아무런 프로세스 간 통신 기능도 사용하지 않는 것을 나타냅니다】
    * **기본값**：`SWOOLE_IPC_NONE`
    * **기타 값**：`SWOOLE_IPC_MSGQUEUE`, `SWOOLE_IPC_SOCKET`, `SWOOLE_IPC_UNIXSOCK`

    !> - `SWOOLE_IPC_NONE`로 설정하면 반드시 `onWorkerStart` 콜백을 설정해야 하며, `onWorkerStart`에서 순환 로직을 구현해야 합니다. `onWorkerStart` 함수가 종료될 때 작업 프로세스는 즉시 종료되며, 이후에는 `Manager` 프로세스에서 프로세스를 다시 시작합니다;  
    - `SWOOLE_IPC_MSGQUEUE`로 설정하면 시스템 메시지 대기열 통신을 사용하며, `$msgqueue_key`를 설정하여 메시지 대기열의 `KEY`를 지정할 수 있습니다. 메시지 대기열 `KEY`가 설정되지 않으면 사유 대기열이 할당됩니다;  
    - `SWOOLE_IPC_SOCKET`로 설정하면 `Socket` 통신을 사용하며, [listen](/process/process_pool?id=listen)方法与를 통해 감시할 주소와 포트를 지정해야 합니다;  
    - `SWOOLE_IPC_UNIXSOCK`로 설정하면 [unixSocket](/learn?id=什么是IPC) 통신을 사용하며, 코로코어 모드에서 사용하며, **프로세스 간 통신을 위한 강력한 추천 방식입니다**, 구체적인 사용법은 다음과 같습니다;  
    - `SWOOLE_IPC_NONE`가 아닌 값으로 설정하면 반드시 `onMessage` 콜백을 설정해야 하며, `onWorkerStart`는 선택 사항이 됩니다.

  * **`int $msgqueue_key`**
    * **기능**：메시지 대기열의 `key`
    * **기본값**：`0`
    * **기타 값**：없음

  * **`bool $enable_coroutine`**
    * **기능**：코로코어 지원을 활성화 여부【코로코어를 사용하면 `onMessage` 콜백을 설정할 수 없습니다】
    * **기본값**：`false`
    * **기타 값**：`true`

* **코로코어 모드**
    
v4.4.0 버전에서 `Process\Pool` 모듈은 코로코어 지원을 추가하여, 네 번째 매개변수를 `true`로 설정하여 활성화할 수 있습니다. 코로코어를 활성화하면, 기본적으로 `onWorkerStart`에서 코로코어와 [코로코어 컨테이너](/coroutine/scheduler)가 자동으로 생성되며, 콜백 함수에서 코로코어 관련 `API`를 직접 사용할 수 있습니다. 예를 들어:

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

코로코어를 활성화하면 Swoole는 `onMessage` 이벤트 콜백을 설정하는 것을 금지합니다. 프로세스 간 통신이 필요하다면 두 번째 매개변수를 `SWOOLE_IPC_UNIXSOCK`로 설정하여 [unixSocket](/learn?id=什么是IPC) 통신을 사용하고, `$pool->getProcess()->exportSocket()`를 통해 [Swoole\Coroutine\Socket](/coroutine_client/socket) 객체를 导出하여, `Worker` 프로세스 간 통신을 실현할 수 있습니다. 예를 들어:

 ```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> 구체적인 사용법은 [Swoole\Coroutine\Socket](/coroutine_client/socket)와 [Swoole\Process](/process/process?id=exportsocket) 관련 장을 참고하십시오.

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```
### set()

설정 매개변수입니다.

```php
Swoole\Process\Pool->set(array $settings): void
```


선택적 매개변수 | 타입 | 기능 | 기본값
---|---|----|----
enable_coroutine | bool | 코루틴을 사용하는지 여부를 제어합니다. | false
enable_message_bus | bool | 메시지 버스를 사용하는지 여부를 제어합니다. 이 값이 `true`이면, 대량의 데이터를 보내면 하단에서 작은 데이터 덩어리로 나누어 한 덩어리씩 상대방에게 보냅니다. | false
max_package_size | int | 프로세스가 받을 수 있는 최대 데이터 양을 제한합니다. | 2 * 1024 * 1024

* **주의 사항**

  * `enable_message_bus`가 `true`일 경우, `max_package_size`는 효과가 없습니다. 왜냐하면 하단에서 데이터를 작은 덩어리로 나누어 보냈기 때문입니다. 데이터 수신도 마찬가지입니다.
  * `SWOOLE_IPC_MSGQUEUE` 모드에서, `max_package_size`는 효과가 없습니다. 하단은 한 번에 최대 65536바이트의 데이터를 받을 수 있습니다.
  * `SWOOLE_IPC_SOCKET` 모드에서, `enable_message_bus`가 `false`일 경우, 받는 데이터 양이 `max_package_size`을 초과하면 하단은 연결을 중단합니다.
  * `SWOOLE_IPC_UNIXSOCK` 모드에서, `enable_message_bus`가 `false`일 경우, 데이터가 `max_package_size`을 초과하면 초과하는 데이터는 잘려납니다.
  * 코루틴 모드가 활성화되어 있을 경우, `enable_message_bus`가 `true`일 때도 `max_package_size`는 효과가 없습니다. 하단은 데이터의 분할(보내기)와 합병(수신)을 처리합니다. 그렇지 않으면 `max_package_size`에 따라 수신 데이터 양이 제한됩니다.

!> Swoole 버전 >= v4.4.4에서 사용할 수 있습니다.


### on()

프로세스 풀回调 함수를 설정합니다.

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **매개변수** 

  * **`string $event`**
    * **기능**：지정된 이벤트
    * **기본값**：없음
    * **기타값**：없음

  * **`callable $function`**
    * **기능**：대상 함수
    * **기본값**：없음
    * **기타값**：없음

* **이벤트**

  * **onWorkerStart** 자식 프로세스 시작

    ```php
    /**
    * @param \Swoole\Process\Pool $pool Pool 객체
    * @param int $workerId   WorkerId 현재 작업 프로세스의 번호, 하단은 자식 프로세스에 번호를 붙입니다.
    */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
        echo "Worker#{$workerId} is started\n";
    });
    ```

  * **onWorkerStop** 자식 프로세스 종료

    ```php
    /**
    * @param \Swoole\Process\Pool $pool Pool 객체
    * @param int $workerId   WorkerId 현재 작업 프로세스의 번호, 하단은 자식 프로세스에 번호를 붙입니다.
    */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
        echo "Worker#{$workerId} stop\n";
    });
    ```

  * **onMessage** 메시지 수신

    !> 외부에서 전달된 메시지를 받습니다. 한 연결에 한 번만 메시지를 전달할 수 있으며, 이는 `PHP-FPM`의 짧은 연결 메커니즘과 유사합니다.

    ```php
    /**
      * @param \Swoole\Process\Pool $pool Pool 객체
      * @param string $data 메시지 데이터 내용
     */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
        var_dump($data);
    });
    ```

    !> 이벤트 이름은 대소문이 구분되지 않습니다. `WorkerStart`, `workerStart`, `workerstart`는 모두 같습니다.


### listen()

`SOCKET`를 감시합니다. `$ipc_mode = SWOOLE_IPC_SOCKET`일 때만 사용할 수 있습니다.

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **매개변수** 

  * **`string $host`**
    * **기능**：감시할 주소【TCP와 [unixSocket](/learn?id= 什么是IPC) 두 가지 유형을 지원합니다. `127.0.0.1`은 TCP 주소를 감시하며, `$port`를 지정해야 합니다. `unix:/tmp/php.sock`는 [unixSocket](/learn?id= 什么是IPC) 주소를 감시합니다】
    * **기본값**：없음
    * **기타값**：없음

  * **`int $port`**
    * **기능**：감시할 포트【TCP 모드에서는 지정해야 합니다】
    * **기본값**：`0`
    * **기타값**：없음

  * **`int $backlog`**
    * **기능**：감시 대기열의 길이
    * **기본값**：`2048`
    * **기타값**：없음

* **반환값**

  * 성공적으로 감시하면 `true`을 반환합니다.
  * 감시 실패하면 `false`을 반환하며, `swoole_errno`를 호출하여 오류 코드를 얻을 수 있습니다. 감시 실패 후에 `start`를 호출하면 즉시 `false`을 반환합니다.

* **통신プロト콜**

    감시 포트에 데이터를 보내면, 클라이언트는 요청 전에 네트워크 바이트 순서의 길이값을 4바이트로 추가해야 합니다. 프로토콜 형식은 다음과 같습니다.

```php
// $msg 보낼 데이터
$packet = pack('N', strlen($msg)) . $msg;
```

* **사용 예시**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```


### write()

대상 프로세스에 데이터를 쓰는 방법입니다. `$ipc_mode`가 `SWOOLE_IPC_SOCKET`일 때만 사용할 수 있습니다.

```php
Swoole\Process\Pool->write(string $data): bool
```

!> 이 방법은 메모리 작업이며, `IO` 소비가 없습니다. 데이터 보낼 때는 동기적이고 비동기적 `IO`가 차단됩니다.

* **매개변수** 

  * **`string $data`**
    * **기능**：쓰려는 데이터 내용【`write`를 여러 번 호출할 수 있으며, 하단은 `onMessage` 함수가 종료될 때까지 데이터를 모두 `socket`에 쓰고 연결을 `close`합니다】
    * **기본값**：없음
    * **기타값**：없음

* **사용 예시**

  * **서버 측**

    ```php
    $pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
    
    $pool->on("Message", function ($pool, $message) {
        echo "Message: {$message}\n";
        $pool->write("hello ");
        $pool->write("world ");
        $pool->write("\n");
    });
    
    $pool->listen('127.0.0.1', 8089);
    $pool->start();
    ```

  * **클라이언트 측**

    ```php
    $fp = stream_socket_client("tcp://127.0.0.1:8089", $errno, $errstr) or die("error: $errstr\n");
    $msg = json_encode(['data' => 'hello', 'uid' => 1991]);
    fwrite($fp, pack('N', strlen($msg)) . $msg);
    sleep(1);
    //hello world\n이 출력됩니다.
    $data = fread($fp, 8192);
    var_dump(substr($data, 4, unpack('N', substr($data, 0, 4))[1]));
    fclose($fp);
    ```


### sendMessage()

목표 프로세스에 데이터를 보냅니다. `$ipc_mode`가 `SWOOLE_IPC_UNIXSOCK`일 때만 사용할 수 있습니다.

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **매개변수** 

  * **`string $data`**
    * **기능**：보낼 데이터
    * **기본값**：없음
    * **기타값**：없음

  * **`int $dst_worker_id`**
    * **기능**：목표 프로세스 ID
    * **기본값**：`0`
    * **기타값**：없음

* **반환값**

  * 성공적으로 보내면 `true`을 반환합니다.
  * 보낼 수 없으면 `false`을 반환합니다.

* **주의 사항**

  * 보낼 데이터가 `max_package_size`을 초과하고 `enable_message_bus`가 `false`인 경우, 대상 프로세스는 데이터를 수신할 때 데이터를 잘라낼 것입니다.

```php
<?php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => false, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(2048)


$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => true, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(6000)
```
### 시작()

작업 프로세스를 시작합니다.

```php
Swoole\Process\Pool->start(): bool
```

!> 성공 시 현재 프로세스가 `wait` 상태에 들어가 작업 프로세스를 관리합니다;  
실패 시 `false`를 반환하며, `swoole_errno`를 사용하여 오류 코드를 확인할 수 있습니다.

* **사용 예시**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
```

* **프로세스 관리**

  * 어떤 작업 프로세스가 치명적인 오류를 만나거나 자발적으로 종료될 경우 관리자는 재생성하여 좀비 프로세스를 방지합니다
  * 작업 프로세스가 종료된 후에는 관리자가 자동으로 시작하고 새로운 작업 프로세스를 만듭니다
  * 메인 프로세스가 `SIGTERM` 신호를 받으면 새로운 프로세스를 `fork`하지 않고 모두 실행 중인 작업 프로세스를 `kill`합니다
  * 메인 프로세스가 `SIGUSR1` 신호를 받으면 실행 중인 작업 프로세스를 하나씩 `kill`하고 새로운 작업 프로세스를 시작합니다

* **신호 처리**

  기본적으로는 메인 프로세스(관리 프로세스)에만 신호 처리가 설정되어 있으며, `Worker` 작업 프로세스에는 신호 처리가 설정되어 있지 않습니다. 개발자는 신호 감시를 스스로 구현해야 합니다.

  - 작업 프로세스가 비동기 모드인 경우 [Swoole\Process::signal](/process/process?id=signal)을 사용하여 신호를 감시하세요
  - 작업 프로세스가 동기 모드인 경우 `pcntl_signal`과 `pcntl_signal_dispatch`를 사용하여 신호를 감시하세요

  작업 프로세스에서는 `SIGTERM` 신호를 감시해야 합니다. 메인 프로세스가 해당 프로세스를 종료할 필요가 있을 때 이 프로세스에 `SIGTERM` 신호를 보냅니다. 작업 프로세스가 `SIGTERM` 신호를 감시하지 않으면 기본적으로 현재 프로세스를 강제로 종료하게 되어 일부 논리가 손상될 수 있습니다.

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```


### 중지()

현재 프로세스의 소켓을 이벤트 루프에서 제거하고, 코어가 시작된 후에 이 함수는 효과가 있습니다.

```php
Swoole\Process\Pool->stop(): bool
```


### 종료()

작업 프로세스를 종료합니다.

```php
Swoole\Process\Pool->shutdown(): bool
```


### getProcess()

현재 작업 프로세스 객체를 가져옵니다. [Swoole\Process](/process/process) 객체를 반환합니다.

!> Swoole 버전 >= `v4.2.0`에서 사용할 수 있습니다

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **매개변수** 

  * **`int $worker_id`**
    * **기능**: 지정된 `worker`를 가져옵니다 【선택적 매개변수,  默认 현재 `worker`】
    * **기본값**: 없음
    * **기타 값**: 없음

!> `start` 이후에, 작업 프로세스의 `onWorkerStart` 또는 기타 콜백 함수에서 호출해야 합니다;  
반환되는 `Process` 객체는 싱글턴 방식으로, 작업 프로세스에서 중복해서 `getProcess()`를 호출해도 동일한 객체를 반환합니다.

* **사용 예시**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```

### 분리()

프로세스 풀 내의 현재 Worker 프로세스를 관리에서 분리시킵니다. 기본적으로 즉시 새로운 프로세스를 만들고, 오래된 프로세스는 더 이상 데이터를 처리하지 않으며, 애플리케이션 계층 코드가 수명 주기를 스스로 관리합니다.

!> Swoole 버전 >= `v4.7.0`에서 사용할 수 있습니다

```php
Swoole\Process\Pool->detach(): bool
```
