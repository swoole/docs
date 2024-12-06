# 사건

이 섹션에서는 Swoole의 모든 콜백 함수를 소개합니다. 각 콜백 함수는 하나의 PHP 함수로, 하나의 사건에 해당합니다.


## onStart

?> **서버가 시작된 후 메인 프로세스(master)의 메인 스레드에서 이 함수를 콜백합니다**

```php
function onStart(Swoole\Server $server);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능** : Swoole\Server 객체
      * **기본값** : 없음
      * **기타값** : 없음

* **이 사건 이전에 `Server`은 다음과 같은 작업을 수행했습니다**

    * 시작하여 [Manager 프로세스](/learn?id=manager进程)를 생성 완료
    * 시작하여 [Worker 서브 프로세스](/learn?id=worker进程)를 생성 완료
    * 모든 TCP/UDP/[unixSocket](/learn?id= 什么是IPC) 포트를 감시하고 있지만, Accept 연결 및 요청을 시작하지 않았습니다
    * 타이머를 감시하고 있습니다

* **이후에 수행할 작업**

    * 메인 [Reactor](/learn?id=reactor线程)이 사건을 수신하기 시작하여, 클라이언트가 `Server`에 `connect`할 수 있습니다

**`onStart` 콜백에서는 `echo`, 로그 출력, 프로세스 이름 수정만 허용됩니다. 다른 작업(서버 관련 함수 등)을 수행해서는 안 되며(`server` 관련 함수를 호출할 수 없다는 것은 서비스가 아직 준비되지 않았다는 것을 의미합니다). `onWorkerStart`와 `onStart` 콜백은 다른 프로세스에서 병행하여 실행되며, 순서는 없습니다.**

`onStart` 콜백에서 `$server->master_pid`와 `$server->manager_pid`의 값을 파일에 저장할 수 있습니다. 이렇게 하면 이 두 `PID`에 신호를 보내어 정지 및 재시작 작업을 수행할 수 있는 스크립트를 작성할 수 있습니다.

`onStart` 사건은 `Master` 프로세스의 메인 스레드에서 호출됩니다.

!> `onStart`에서 생성된 글로벌 자원 객체는 `Worker` 프로세스에서 사용할 수 없습니다. 왜냐하면 `onStart`가 호출될 때 `worker` 프로세스는 이미 생성되었기 때문입니다  
새로 생성된 객체는 메인 프로세스 내에 있으며, `Worker` 프로세스는 이 메모리 영역에 액세스할 수 없습니다  
따라서 글로벌 객체 생성의 코드는 `Server::start`보다 먼저 배치되어야 합니다. 전형적인 예는 [Swoole\Table](/memory/table?id=完整示例)입니다

* **안전 알림**

`onStart` 콜백에서는 비동기 및 코루outine의 API를 사용할 수 있지만, 이는 `dispatch_func`와 `package_length_func`와 충돌할 수 있으므로 **동시에 사용할 수 없습니다**.

`onStart`에서 타이머를 시작하지 마십시오. 코드에서 `Swoole\Server::shutdown()` 작업을 수행하면, 항상 타이머가 실행 중인ため 프로그램이 종료되지 않을 수 있습니다.

`onStart` 콜백은 `return` 이전에 서버 프로그램이 클라이언트 연결을 받지 않으므로, 동기적이고 블록적인 함수를 안전하게 사용할 수 있습니다.

* **BASE 모드**

[SWOOLE_BASE](/learn?id=swoole_base) 모드에는 `master` 프로세스가 없으므로 `onStart` 사건이 존재하지 않으며, `BASE` 모드에서 `onStart` 콜백 함수를 사용할 수 없습니다.

```
WARNING swReactorProcess_start: The onStart event with SWOOLE_BASE is deprecated
```


## onBeforeShutdown

?> **이 사건은 `Server`가 정상적으로 종료되기 전에 발생합니다** 

!> Swoole 버전 >= `v4.8.0`에서 사용할 수 있습니다. 이 사건에서는 코루outine API를 사용할 수 있습니다.

```php
function onBeforeShutdown(Swoole\Server $server);
```


* **매개변수**

    * **`Swoole\Server $server`**
        * **기능** : Swoole\Server 객체
        * **기본값** : 없음
        * **기타값** : 없음


## onShutdown

?> **이 사건은 `Server`가 정상적으로 종료될 때 발생합니다**

```php
function onShutdown(Swoole\Server $server);
```

  * **매개변수**

    * **`Swoole\Server $server`**
      * **기능** : Swoole\Server 객체
      * **기본값** : 없음
      * **기타값** : 없음

  * **이전에 `Swoole\Server`은 다음과 같은 작업을 수행했습니다**

    * 모든 [Reactor](/learn?id=reactor线程) 스레드, `HeartbeatCheck` 스레드, `UdpRecv` 스레드를 종료했습니다
    * 모든 `Worker` 프로세스, [Task 프로세스](/learn?id=taskworker进程), [User 프로세스](/server/methods?id=addprocess)를 종료했습니다
    * 모든 `TCP/UDP/UnixSocket` 수신 포트를 `close`했습니다
    * 메인 [Reactor](/learn?id=reactor线程)를 종료했습니다

  !> 프로세스를 강제로 `kill`하면 `onShutdown` 콜백이 되지 않습니다. 예를 들어 `kill -9`을 사용하면 됩니다  
  `SIGTERM` 신호를 메인 프로세스에 보내어 정상적인 프로세스 종료 흐름을 따라 종료하려면 `kill -15`를 사용해야 합니다  
  명령 프롬프트에서 `Ctrl+C`을 사용하여 프로그램을 중단하면 즉시 멈추며, 하단에서 `onShutdown`가 호출되지 않습니다

  * **주의 사항**

  !> `onShutdown`에서 비동기 또는 코루outine 관련 `API`를 호출하지 마십시오. `onShutdown`가 트리거될 때 하단은 이미 모든 사건 루프 시설을 파괴했습니다;  
이때는 이미 코루outine 환경이 존재하지 않으므로, 개발자가 코루outine 관련 `API`를 사용하려면 수동으로 `Co\run`을 호출하여 [코루outine 컨테이너](/coroutine?id=什么是协程容器)를 생성해야 합니다.


## onWorkerStart

?> **이 사건은 Worker 프로세스/ [Task 프로세스](/learn?id=taskworker进程)가 시작될 때 발생하며, 여기서 생성된 객체는 프로세스 수명 동안 사용할 수 있습니다.**

```php
function onWorkerStart(Swoole\Server $server, int $workerId);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능** : Swoole\Server 객체
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $workerId`**
      * **기능** : `Worker`  프로세스 `id` (프로세스의 PID가 아닙니다)
      * **기본값** : 없음
      * **기타값** : 없음

  * `onWorkerStart/onStart`은 병행하여 실행되며, 순서는 없습니다
  * `$server->taskworker` 속성을 통해 현재 `Worker` 프로세스인지 [Task 프로세스](/learn?id=taskworker进程)인지 확인할 수 있습니다
  * `worker_num`과 `task_worker_num`이 `1`을 초과할 경우, 각 프로세스마다 한 번씩 `onWorkerStart` 사건이 발생하며, [$worker_id](/server/properties?id=worker_id)를 통해 다른 작업 프로세스를 구분할 수 있습니다
  * `worker` 프로세스가 `task` 프로세스에 작업을 전달하고, `task` 프로세스가 모든 작업을 처리한 후 [onFinish](/server/events?id=onfinish) 콜백 함수를 통해 `worker` 프로세스에 통지합니다. 예를 들어, 백그라운드에서 십만 명의 사용자에게 알림 이메일을 전송하는 작업을 완료하면, 작업 상태가 전송 중으로 표시되며, 이때 다른 작업을 계속할 수 있습니다. 이메일 전송이 완료되면, 작업 상태가 자동으로 전송 완료로 변경됩니다.

  다음의 예는 Worker 프로세스/ [Task 프로세스](/learn?id=taskworker进程)의 이름을 변경하는 데 사용됩니다.

```php
$server->on('WorkerStart', function ($server, $worker_id){
    global $argv;
    if($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});
```

  [Reload](/server/methods?id=reload) 메커니즘을 사용하여 코드를 재装入하려면, `onWorkerStart`에서 비즈니스 파일을 `require`해야 하며, 파일 상단에서 하지 않습니다. `onWorkerStart` 호출 전에 이미 포함된 파일은 코드를 재装入하지 않습니다.

  공통적이고 변하지 않는 php 파일을 `onWorkerStart`보다 앞에 배치할 수 있습니다. 이렇게 하면 코드를 재装入할 수는 없지만, 모든 `Worker`는 공유되므로 이러한 데이터를 저장하기 위한 추가 메모리가 필요 없습니다.
`onWorkerStart` 이후의 코드는 각 프로세스에서 메모리에 한 번씩 보존해야 합니다.

  * `$worker_id`는 이 `Worker` 프로세스의 `ID`를 나타내며, 범위는 [$worker_id](/server/properties?id=worker_id)를 참고하세요
  * [$worker_id](/server/properties?id=worker_id)와 프로세스 `PID`는 관련이 없으며, `posix_getpid` 함수를 사용하여 `PID`를 얻을 수 있습니다

  * **코루outine 지원**

    * `onWorkerStart` 콜백 함수에서 자동으로 코루outine가 생성되므로, `onWorkerStart`에서 코루outine `API`를 사용할 수 있습니다

  * **주의 사항**

    !> 치명적인 오류가 발생하거나 코드에서 `exit`를 명시적으로 호출하면, `Worker/Task` 프로세스가 종료되며, 관리 프로세스는 새로운 프로세스를 다시 생성합니다. 이로 인해 죽은 소용돌이 현상이 발생할 수 있으며, 프로세스를 지속적으로 생성하고 파괴할 수 있습니다
## onWorkerStop

?> **이 이벤트는 `Worker` 프로세스가 종료될 때 발생합니다. 이 함수에서는 `Worker` 프로세스가 신청한 각종 자원을 회수할 수 있습니다.**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능** : Swoole\Server 객체
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $workerId`**
      * **기능** : `Worker` 프로세스 `id` (프로세스의 PID가 아님)
      * **기본값** : 없음
      * **기타값** : 없음

  * **주의**

    !> -프로세스가 비정상적으로 종료될 경우, 예를 들어 강제 `kill`, 치명적인 오류, `core dump` 등에서 `onWorkerStop` 콜백 함수를 실행할 수 없습니다.  
    - `onWorkerStop`에서 비동기 또는 코루outine 관련 `API`를 호출하지 마십시오. `onWorkerStop`가 트리거될 때 이미 모든 [이벤트 루프](/learn?id=무엇이eventloop인가요) 시설이 파괴되어 있습니다.


## onWorkerExit

?> **[reload_async](/server/setting?id=reload_async) 기능이 활성화된 후에만 유효합니다. 자세한 내용은 [서비스를 올바르게 재시작하는 방법](/question/use?id=swoole로서비스를올바르게재시작하는 방법)을 참조하세요.**

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능** : Swoole\Server 객체
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $workerId`**
      * **기능** : `Worker` 프로세스 `id` (프로세스의 PID가 아님)
      * **기본값** : 없음
      * **기타값** : 없음

  * **주의**

    !> - `Worker` 프로세스가 종료되지 않았을 경우, `onWorkerExit`는 계속해서 트리거됩니다.  
    - `onWorkerExit`는 `Worker` 프로세스 내에서 트리거되며, [Task 프로세스](/learn?id=taskworker프로세스) 내에 [이벤트 루프](/learn?id=무엇이eventloop인가요)가 존재하는 경우에도 트리거됩니다.  
    - `onWorkerExit`에서는 가능한 한 비동기적인 `Socket` 연결을 제거/비활성화하고, 마침내底层에서 이벤트 루프의 이벤트 监听 handle 수가 `0`이 되도록 프로세스를 종료합니다.  
    - 프로세스에 이벤트 handle가监听 중이 없을 경우, 프로세스가 종료될 때 이 함수를 콜백하지 않습니다.  
    - `Worker` 프로세스가 종료될 때까지 기다렸다가 `onWorkerStop` 이벤트 콜백이 실행됩니다.


## onConnect

?> **새로운 연결이 들어올 때, worker 프로세스에서 콜백됩니다.**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능** : Swoole\Server 객체
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $fd`**
      * **기능** : 연결의 파일 디스크립터
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $reactorId`**
      * **기능** : 연결이 있는 [Reactor](/learn?id=reactor스레드) 스레드 `ID`
      * **기본값** : 없음
      * **기타값** : 없음

  * **주의**

    !> `onConnect/onClose` 이 두 콜백은 `Worker` 프로세스 내에서 발생하며, 메인 프로세스가 아닙니다.  
    `UDP` 프로토콜 하에는 [onReceive](/server/events?id=onreceive) 이벤트만 있으며, `onConnect/onClose` 이벤트는 없습니다.

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

      * 이 모드에서는 `onConnect/onReceive/onClose`가 다른 프로세스에 전달될 수 있습니다. 연결 관련의 `PHP` 객체 데이터는 [onConnect](/server/events?id=onconnect) 콜백에서 데이터를 초기화하고, [onClose](/server/events?id=onclose)에서 데이터를 청소할 수 없습니다.
      * `onConnect/onReceive/onClose` 이 세 가지 이벤트는 병렬로 실행될 수 있으며, 예기치 못한 상황을 초래할 수 있습니다.


## onReceive

?> **데이터를 수신했을 때 이 함수가 콜백되며, `worker` 프로세스에서 발생합니다.**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능** : Swoole\Server 객체
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $fd`**
      * **기능** : 연결의 파일 디스크립터
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $reactorId`**
      * **기능** : `TCP` 연결이 있는 [Reactor](/learn?id=reactor스레드) 스레드 `ID`
      * **기본값** : 없음
      * **기타값** : 없음

    * **`string $data`**
      * **기능** : 수신된 데이터 내용, 텍스트나 이진 데이터일 수 있습니다
      * **기본값** : 없음
      * **기타값** : 없음

  * **`TCP` 프로토콜 하의 패킷 완전성에 대해서는 [TCP 패킷 경계 문제](/learn?id=tcp패킷경계문제)를 참조하세요.**

    *底层에서 제공하는 `open_eof_check/open_length_check/open_http_protocol` 등의 설정으로 패킷의 완전성을 보장할 수 있습니다.
    *底层의 프로토콜 처리를 사용하지 않고, [onReceive](/server/events?id=onreceive) 후 PHP 코드에서 데이터를 분석하고, 패킷을 합성/분할할 수 있습니다.

    예를 들어: 코드에서 `$buffer = array()`를 늘어놓고, `$fd`를 `key`로 사용하여 컨텍스트 데이터를 저장할 수 있습니다. 매번 데이터를 수신할 때마다 문자열을 합성하여 `$buffer[$fd] .= $data`를 사용하고, `$buffer[$fd]` 문자열이 완전한 패킷인지 확인합니다.

    기본적으로 동일한 `fd`는 동일한 `Worker`에 할당되므로 데이터를 합칠 수 있습니다. [dispatch_mode](/server/setting?id=dispatch_mode) = 3을 사용하면, 요청 데이터는 절차적으로 이루어지며, 동일한 `fd`에서 온 데이터는 다른 프로세스에 분배될 수 있으므로 위의 패킷 합성 방법을 사용할 수 없습니다.

  * **다중 포트 감시, [이 장](/server/port)을 참조하세요.**

    메인 서버가 프로토콜을 설정한 후에 추가로 감시하는 포트는 기본적으로 메인 서버의 설정을 이어받습니다. 포트의 프로토콜을 재설정하려면 명시적으로 `set` 메서드를 호출해야 합니다.    

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9501);
    $port2 = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $port2->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        echo "[#".$server->worker_id."]\tClient[$fd]: $data\n";
    });
    ```

    여기서는 `on` 메서드를 호출하여 [onReceive](/server/events?id=onreceive) 콜백 함수를 등록했지만, 메인 서버의 프로토콜을 덮어씌우지 않은 상태로 `set` 메서드를 호출하지 않았기 때문에 새로 감시하는 `9502` 포트는 여전히 `HTTP` 프로토콜을 사용합니다. `telnet` 클라이언트를 사용하여 `9502` 포트에 문자열을 보내면 서버는 [onReceive](/server/events?id=onreceive)를 트리거하지 않습니다.

  * **주의**

    !> 자동 프로토콜 옵션을 미적용한 경우, [onReceive](/server/events?id=onreceive)에서 한 번에 수신하는 데이터는 최대 `64K`입니다.  
    자동 프로토콜 처리 옵션을 활성화하면, [onReceive](/server/events?id=onreceive)에서 완전한 패킷을 수신하며, 최대 [package_max_length](/server/setting?id=package_max_length)을 초과하지 않습니다.  
    이진 형식도 지원되며, `$data`는 이진 데이터일 수 있습니다.
## onPacket

?> **UDP 데이터 팩을 수신했을 때 이 함수가 콜백됩니다. worker 프로세스에서 발생합니다.**

```php
function onPacket(Swoole\Server $server, string $data, array $clientInfo);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능**: Swoole\Server 객체
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $data`**
      * **기능**: 수신된 데이터 내용, 텍스트나 이진 내용일 수 있음
      * **기본값**: 없음
      * **기타값**: 없음

    * **`array $clientInfo`**
      * **기능**: 클라이언트 정보에는 주소/포트/서버 소켓 등의 다양한 클라이언트 정보가 포함되어 있습니다. [UDP 서버 참조](/start/start_udp_server)
      * **기본값**: 없음
      * **기타값**: 없음

  * **주의**

    !> TCP/UDP 포트를 동시에 감시하는 서버는 TCP 프로토콜의 데이터를 수신했을 때 [onReceive](/server/events?id=onreceive)에 콜백되고, UDP 데이터 팩을 수신했을 때는 onPacket에 콜백됩니다. 서버가 설정한 EOF나 Length 등의 자동 프로토콜 처리([TCP 패킷 경계 문제 참조](/learn?id=tcp数据包边界问题))는 UDP 포트에 적용되지 않습니다. 왜냐하면 UDP 패킷 자체에 메시지 경계가 있기 때문입니다. 추가적인 프로토콜 처리는 필요하지 않습니다.


## onClose

?> **TCP 클라이언트 연결이 닫힌 후 worker 프로세스에서 이 함수가 콜백됩니다.**

```php
function onClose(Swoole\Server $server, int $fd, int $reactorId);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능**: Swoole\Server 객체
      * **기본값**: 없음
      * **기타값**: 없음

    * **`int $fd`**
      * **기능**: 연결의 파일 디스크립터
      * **기본값**: 없음
      * **기타값**: 없음

    * **`int $reactorId`**
      * **기능**: 어느 reactor 스레드에서 온 것인지, 적극적으로 close를 통해 닫을 때는 부정수입니다.
      * **기본값**: 없음
      * **기타값**: 없음

  * **알림**

    * **액티브 닫기**

      * 서버가 직접 연결을 닫을 때, 하단에서 이 매개변수를 `-1`로 설정합니다. `$reactorId < 0`을 비교하여 닫기는 서버 측에서 시작되었는지 클라이언트 측에서 시작되었는지 구분할 수 있습니다.
      * PHP 코드에서 직접 `close` 메서드를 호출하여 닫는 것을 액티브 닫기로 간주합니다.

    * **핼로윈 검사**

      * [핼로윈 검사](/server/setting?id=heartbeat_check_interval)는 핼로윈 검사 스레드가 닫기를 알리며, 닫을 때 [onClose](/server/events?id=onclose)의 `$reactorId` 매개변수는 `-1`가 아닙니다.

  * **주의**

    !> - [onClose](/server/events?id=onclose) 콜백 함수가 치명적인 오류를 일으켜 연결이 누수될 수 있습니다. `netstat` 명령어를 사용하면 많은 `CLOSE_WAIT` 상태의 `TCP` 연결을 볼 수 있습니다.
    - 클라이언트가 닫기를 시작하거나 서버 측에서 `$server->close()`를 호출하여 연결을 닫을 때, 이 이벤트가 트리거됩니다. 따라서 연결이 닫히면 이 함수가 반드시 콜백됩니다.  
    - [onClose](/server/events?id=onclose)에서 여전히 [getClientInfo](/server/methods?id=getClientInfo) 메서드를 호출하여 연결 정보를 가져올 수 있으며, [onClose](/server/events?id=onclose) 콜백 함수가 실행된 후에야 `TCP` 연결을 닫을 수 있습니다.  
    - 여기서 [onClose](/server/events?id=onclose)가 콜백되면 클라이언트 연결이 이미 닫힌 것으로 보이기 때문에 `$server->close($fd)`를 실행할 필요가 없습니다. 코드에서 `$server->close($fd)`를 실행하면 PHP 오류 경고가 발생합니다.


## onTask

?> **task 프로세스 내에서 호출됩니다. worker 프로세스는 [task](/server/methods?id=task) 메서드를 사용하여 task_worker 프로세스에 새로운 작업을 전달할 수 있습니다. 현재의 [Task 프로세스](/learn?id=taskworker进程)는 [onTask](/server/events?id=ontask) 콜백 함수를 호출할 때 프로세스 상태를 바쁨으로 전환하며, 이때는 새로운 Task를 더 이상 수신하지 않습니다. [onTask](/server/events?id=ontask) 함수가 반환될 때 프로세스 상태를 여유로 전환한 다음 새로운 Task를 수신을 계속합니다.**

```php
function onTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능**: Swoole\Server 객체
      * **기본값**: 없음
      * **기타값**: 없음

    * **`int $task_id`**
      * **기능**: 작업을 실행하는 `task` 프로세스의 `id` 【`$task_id`와 `$src_worker_id`이 결합되어야 전역적으로 유일합니다. 다른 `worker` 프로세스가 전달하는 작업 `ID`는 같을 수 있습니다】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`int $src_worker_id`**
      * **기능**: 작업을 전달하는 `worker` 프로세스의 `id` 【`$task_id`와 `$src_worker_id`이 결합되어야 전역적으로 유일합니다. 다른 `worker` 프로세스가 전달하는 작업 `ID`는 같을 수 있습니다】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`mixed $data`**
      * **기능**: 작업의 데이터 내용
      * **기본값**: 없음
      * **기타값**: 없음

  * **알림**

    * **v4.2.12부터 [task_enable_coroutine](/server/setting?id=task_enable_coroutine)가 활성화되면 콜백 함수의 프로토타입은 다음과 같습니다.**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); // 작업 완료, 종료하고 데이터를 반환합니다.
      });
      ```

    * **`worker` 프로세스에 실행 결과 반환**

      * **[onTask](/server/events?id=ontask) 함수에서 `return` 문자를 사용하면, 이 내용을 `worker` 프로세스에 반환합니다. `worker` 프로세스에서는 [onFinish](/server/events?id=onfinish) 함수가 트리거되며, 전달된 `task`이 완료되었음을 나타냅니다. 물론, `Swoole\Server->finish()`를 통해 [onFinish](/server/events?id=onfinish) 함수를 트리거할 수도 있으며, 더 이상 `return`할 필요가 없습니다.**

      * `return`할 수 있는 변수는 `null`이 아닌任意의 PHP 변수입니다.

  * **주의**

    !> [onTask](/server/events?id=ontask) 함수가 실행 중에 치명적인 오류로 종료되거나 외부 프로세스에 의해 강제로 `kill`되면, 현재의 작업은 버려지지만, 대기 중인 다른 `Task`는 영향을 받지 않습니다.


## onFinish

?> **이 콜백 함수는 worker 프로세스에서 호출되며, worker 프로세스가 전달한 작업이 task 프로세스에서 완료될 때, [task 프로세스](/learn?id=taskworker进程)는 `Swoole\Server->finish()` 메서드를 통해 task 프로세스의 작업 처리 결과를 worker 프로세스에 보냅니다.**

```php
function onFinish(Swoole\Server $server, int $task_id, mixed $data)
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능**: Swoole\Server 객체
      * **기본값**: 없음
      * **기타값**: 없음

    * **`int $task_id`**
      * **기능**: 작업을 실행하는 `task` 프로세스의 `id`
      * **기본값**: 없음
      * **기타값**: 없음

    * **`mixed $data`**
      * **기능**: 작업 처리 결과 내용
      * **기본값**: 없음
      * **기타값**: 없음

  * **주의**

    !> - [task 프로세스](/learn?id=taskworker进程)의 [onTask](/server/events?id=ontask) 이벤트에서 `finish` 메서드를 호출하지 않거나 결과를 `return`하지 않으면, worker 프로세스는 [onFinish](/server/events?id=onfinish)를 트리거하지 않습니다.  
    - [onFinish](/server/events?id=onfinish) 로직을 실행하는 worker 프로세스와 작업을 전달하는 worker 프로세스는 동일한 프로세스입니다.
## onPipeMessage

?> **일반적으로 `$server->sendMessage()` 메서드를 통해 전달되는 [unixSocket](/learn?id= 什么是IPC) 메시지가 작업 프로세스에 도착하면 `onPipeMessage` 이벤트가 발생합니다. `worker/task` 프로세스 모두 `onPipeMessage` 이벤트를 발생시킬 수 있습니다**

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능**：Swoole\Server 객체
      * **기본값**：없음
      * **기타값**：없음

    * **`int $src_worker_id`**
      * **기능**：메시지가 어느 `Worker` 프로세스에서 온 것인지
      * **기본값**：없음
      * **기타값**：없음

    * **`mixed $message`**
      * **기능**：메시지 내용은 어떤 PHP 유형이든 가능합니다
      * **기본값**：없음
      * **기타값**：없음


## onWorkerError

?> **`Worker/Task` 프로세스에서 예외가 발생하면 `Manager` 프로세스 내에서 이 함수가 콜백됩니다.**

!> 이 함수는 주로 경보 및 모니터링을 위해 사용되며, `Worker` 프로세스가 비정상적으로 종료될 경우 대부분 치명적인 오류나 프로세스 코어 덤프가 발생했을 가능성이 높습니다. 로그를 기록하거나 경보 정보를 보내 개발자에게 해당 처리를 시키는 것을 권장합니다.

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

  * **매개변수** 

    * **`Swoole\Server $server`**
      * **기능**：Swoole\Server 객체
      * **기본값**：없음
      * **기타값**：없음

    * **`int $worker_id`**
      * **기능**：예외가 발생한 `worker` 프로세스의 `id`
      * **기본값**：없음
      * **기타값**：없음

    * **`int $worker_pid`**
      * **기능**：예외가 발생한 `worker` 프로세스의 `pid`
      * **기본값**：없음
      * **기타값**：없음

    * **`int $exit_code`**
      * **기능**：종료 상태 코드, 범위는 `0～255`
      * **기본값**：없음
      * **기타값**：없음

    * **`int $signal`**
      * **기능**：프로세스 종료 신호
      * **기본값**：없음
      * **기타값**：없음

  * **일반적인 오류**

    * `signal = 11`：`Worker` 프로세스에서 `segment fault` 단편적 오류가 발생했으며, 하층의 `BUG`를 유발했을 가능성이 높습니다. `core dump` 정보와 `valgrind` 메모리 검사 로그를 수집하고 [Swoole 개발팀에 이 문제를 보고](/other/issue)
    * `exit_code = 255`：`Worker` 프로세스에서 `Fatal Error` 치명적 오류가 발생했습니다. PHP의 오류 로그를 확인하고 문제가 있는 PHP 코드를 찾아 해결하세요
    * `signal = 9`：`Worker`가 시스템에 의해 강제로 `Kill`되었습니다. 인위적인 `kill -9` 작업이 있었는지 확인하고 `dmesg` 정보에서 `OOM(Out of memory)`이 존재하는지 확인하세요
    * `OOM`이 존재하면 너무 큰 메모리가 할당되었습니다. 1. `Server`의 `setting` 구성이 [socket_buffer_size](/server/setting?id=socket_buffer_size) 등이 너무 큰지 확인합니다; 2. 매우 큰 [Swoole\Table](/memory/table) 메모리 모듈이 만들어졌는지 확인합니다.


## onManagerStart

?> **관리 프로세스가 시작될 때 이 이벤트가 발생합니다**

```php
function onManagerStart(Swoole\Server $server);
```

  * **알림**

    * 이 콜백 함수에서는 관리 프로세스의 이름을 변경할 수 있습니다.
    * `4.2.12` 이전 버전에서는 `manager` 프로세스에서 타이머를 추가하거나, task 작업을 전달하거나, 코어 사용이 불가능합니다.
    * `4.2.12` 이상 버전에서는 `manager` 프로세스에서 신호 기반의 동기화 모드 타이머를 사용할 수 있습니다
    * `manager` 프로세스에서는 [sendMessage](/server/methods?id=sendMessage) 인터페이스를 사용하여 다른 작업 프로세스에 메시지를 보낼 수 있습니다

    * **시작 순서**

      * `Task`와 `Worker` 프로세스가 이미 생성되었습니다
      * `Master` 프로세스의 상태는 알 수 없습니다. 왜냐하면 `Manager`과 `Master`은 병렬이기 때문에 `onManagerStart` 콜백이 발생해도 `Master` 프로세스가 준비되어 있는지 확신할 수 없습니다

    * **BASE 모드**

      * [SWOOLE_BASE](/learn?id=swoole_base) 모드에서 `worker_num`, `max_request`, `task_worker_num` 매개변수가 설정되어 있다면, 하층에서 `manager` 프로세스를 생성하여 작업 프로세스를 관리합니다. 따라서 `onManagerStart`와 `onManagerStop` 이벤트 콜백이 발생합니다.


## onManagerStop

?> **관리 프로세스가 종료될 때 이 이벤트가 발생합니다**

```php
function onManagerStop(Swoole\Server $server);
```

 * **알림**

  * `onManagerStop`가 발생하면, `Task`와 `Worker` 프로세스의 실행이 종료되었으며, `Manager` 프로세스에 의해 회수되었습니다.


## onBeforeReload

?> **Worker 프로세스 `Reload` 이전에 이 이벤트가 발생하며, Manager 프로세스에서 콜백됩니다**

```php
function onBeforeReload(Swoole\Server $server);
```

  * **매개변수**

    * **`Swoole\Server $server`**
      * **기능**：Swoole\Server 객체
      * **기본값**：없음
      * **기타값**：없음


## onAfterReload

?> **Worker 프로세스 `Reload` 이후에 이 이벤트가 발생하며, Manager 프로세스에서 콜백됩니다**

```php
function onAfterReload(Swoole\Server $server);
```

  * **매개변수**

    * **`Swoole\Server $server`**
      * **기능**：Swoole\Server 객체
      * **기본값**：없음
      * **기타값**：없음


## 이벤트 실행 순서

* 모든 이벤트 콜백은 `$server->start` 이후에 발생합니다
* 서버가 종료될 때 마지막 이벤트는 `onShutdown`입니다
* 서버가 성공적으로 시작되면, `onStart/onManagerStart/onWorkerStart`는 다른 프로세스에서 병렬로 실행됩니다
* `onReceive/onConnect/onClose`는 `Worker` 프로세스에서 발생합니다
* `Worker/Task` 프로세스가 시작/종료될 때 각각 `onWorkerStart/onWorkerStop`가 한 번씩 호출됩니다
* [onTask](/server/events?id=ontask) 이벤트는 [task 프로세스](/learn?id=taskworker 프로세스)에서만 발생합니다
* [onFinish](/server/events?id=onfinish) 이벤트는 `worker` 프로세스에서만 발생합니다
* `onStart/onManagerStart/onWorkerStart` 3가지 이벤트의 실행 순서는 확실하지 않습니다

## 객체 지향 스타일

[event_object](/server/setting?id=event_object)가 활성화되면 다음 이벤트 콜백의 매개변수가 변경됩니다.

* 고객 연결 [onConnect](/server/events?id=onconnect)
```php
$server->on('Connect', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* 수신 데이터 [onReceive](/server/events?id=onreceive)
```php
$server->on('Receive', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* 연결 종료 [onClose](/server/events?id=onclose)
```php
$server->on('Close', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```


* UDP 패킷 수신 [onPacket](/server/events?id=onpacket)
```php
$server->on('Packet', function (Swoole\Server $serv, Swoole\Server\Packet $object) {
    var_dump($object);
});
```


* 프로세스 간 통신 [onPipeMessage](/server/events?id=onpipemessage)
```php
$server->on('PipeMessage', function (Swoole\Server $serv, Swoole\Server\PipeMessage $msg) {
    var_dump($msg);
    $object = $msg->data;
    $serv->sendto($object->address, $object->port, $object->data, $object->server_socket);
});
```


* 프로세스에서 예외 발생 [onWorkerError](/server/events?id=onworkererror)
```php
$serv->on('WorkerError', function (Swoole\Server $serv, Swoole\Server\StatusInfo $info) {
    var_dump($info);
});
```


* task 프로세스에서 작업 수락 [onTask](/server/events?id=ontask)
```php
$server->on('Task', function (Swoole\Server $serv, Swoole\Server\Task $task) {
    var_dump($task);
});
```


* worker 프로세스에서 task 프로세스의 처리 결과 수락 [onFinish](/server/events?id=onfinish)
```php
$server->on('Finish', function (Swoole\Server $serv, Swoole\Server\TaskResult $result) {
    var_dump($result);
});
```

* [Swoole\Server\Event](/server/event_class)
* [Swoole\Server\Packet](/server/packet_class)
* [Swoole\Server\PipeMessage](/server/pipemessage_class)
* [Swoole\Server\StatusInfo](/server/statusinfo_class)
* [Swoole\Server\Task](/server/task_class)
* [Swoole\Server\TaskResult](/server/taskresult_class)
