# 방법


## __construct() 

비동기 I/O(Asynchronous I/O)의 TCP 서버 객체를 생성합니다.

```php
Swoole\Server::__construct(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```

  * **매개변수**

    * `string $host`

      * 기능: 감시할 IP 주소를 지정합니다.
      * 기본값: 없음
      * 기타값: 없음

      !> IPv4는 `127.0.0.1`을 사용하여 본기를 감시하고, `0.0.0.0`을 사용하면 모든 주소를 감시합니다.
      IPv6는 `::1`을 사용하여 본기를 감시하고, `::` (즉 `0:0:0:0:0:0:0:0`)를 사용하면 모든 주소를 감시합니다.

    * `int $port`

      * 기능: 감시할 포트 번호를 지정합니다. 예를 들어 `9501`입니다.
      * 기본값: 없음
      * 기타값: 없음

      !> `$sockType` 값이 [UnixSocket Stream/Dgram](/learn?id= 什么是IPC)인 경우, 이 매개변수는 무시됩니다.
      `1024` 미만의 포트를 감시하려면 `root` 권한이 필요합니다.
      이 포트가 이미 사용 중인 경우 `server->start` 시 실패합니다.

    * `int $mode`

      * 기능: 운영 모드를 지정합니다.
      * 기본값: [SWOOLE_PROCESS](/learn?id=swoole_process) 멀티 프로세스 모드(기본).
      * 기타값: [SWOOLE_BASE](/learn?id=swoole_base) 기본 모드, [SWOOLE_THREAD](/learn?id=swoole_thread) 멀티 스레드 모드(Swoole 6.0부터 사용 가능).

      ?> `SWOOLE_THREAD` 모드에서는 여기를 클릭하여 [스레드 + 서버(비동기 스타일)](/thread/thread?id=线程-服务端（异步风格）)에서 멀티 스레드 모드에서 서버를 구축하는 방법을 확인할 수 있습니다.

      !> Swoole 5부터 운영 모드의 기본값은 `SWOOLE_BASE`입니다.

    * `int $sockType`

      * 기능: 이 세트의 Server의 유형을 지정합니다.
      * 기본값: 없음
      * 기타값:
        * `SWOOLE_TCP/SWOOLE_SOCK_TCP` tcp ipv4 소켓
        * `SWOOLE_TCP6/SWOOLE_SOCK_TCP6` tcp ipv6 소켓
        * `SWOOLE_UDP/SWOOLE_SOCK_UDP` udp ipv4 소켓
        * `SWOOLE_UDP6/SWOOLE_SOCK_UDP6` udp ipv6 소켓
        * [SWOOLE_UNIX_DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) unix 소켓 dgram
        * [SWOOLE_UNIX_STREAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/stream_server.php) unix 소켓 stream 

      !> `$sock_type` | `SWOOLE_SSL`을 사용하면 `SSL` 터널 암호화를 활성화할 수 있습니다. `SSL`을 활성화하면 반드시 구성해야 합니다. [ssl_key_file](/server/setting?id=ssl_cert_file)와 [ssl_cert_file](/server/setting?id=ssl_cert_file)

  * **예시**

```php
$server = new \Swoole\Server($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP);

// UDP/TCP를 혼합하여 동시에 내부 네트워크와 외부 네트워크 포트를 감시하고, 멀티 포트 감시 참조 addlistener小节.
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP); // TCP 추가
$server->addlistener("192.168.1.100", 9503, SWOOLE_SOCK_TCP); // Web Socket 추가
$server->addlistener("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDP
$server->addlistener("/var/run/myserv.sock", 0, SWOOLE_UNIX_STREAM); // UnixSocket Stream
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP | SWOOLE_SSL); // TCP + SSL

$port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP); // 시스템이 무작위로 할당한 포트, 반환 값은 무작위로 할당된 포트입니다.
echo $port->port;
```
  

## set()

운행 시의 각종 매개변수를 설정하는 데 사용됩니다. 서버가 시작된 후에는 `$serv->setting`을 통해 `Server->set` 메서드가 설정한 매개변수 배열에 액세스할 수 있습니다.

```php
Swoole\Server->set(array $setting): void
```

!> `Server->set`은 반드시 `Server->start` 이전에 호출해야 하며, 각 구성이 의미하는 내용은 [해당 섹션](/server/setting)을 참고하세요.

  * **예시**

```php
$server->set(array(
    'reactor_num'   => 2,     // 스레드 수
    'worker_num'    => 4,     // 프로세스 수
    'backlog'       => 128,   // 리액터 대기열 길이
    'max_request'   => 50,    // 각 프로세스의 최대 요청 수
    'dispatch_mode' => 1,     // 패킷 배포 전략
));
```


## on()

`Server`의 이벤트 콜백 함수를 등록합니다.

```php
Swoole\Server->on(string $event, callable $callback): bool
```

!> `on` 메서드를 중복 호출하면 이전의 설정은 덮여집니다.

!> PHP 8.2부터 동적으로 속성을 직접 설정하는 것이 지원되지 않으며, `$event`가 `Swoole`가 정의한 이벤트가 아닐 경우 경고가 발생합니다.

  * **매개변수**

    * `string $event`

      * 기능: 콜백 이벤트 이름
      * 기본값: 없음
      * 기타값: 없음

      !> 대소문이 무관하며, 구체적인 이벤트 콜백은 [해당 섹션](/server/events)를 참고하세요. 이벤트 이름 문자열에는 `on`을 붙이지 않습니다.

    * `callable $callback`

      * 기능: 콜백 함수
      * 기본값: 없음
      * 기타값: 없음

      !> 함수 이름의 문자열, 클래스 정적 메서드, 객체 메서드 배열, 익명 함수 참고 [해당 섹션](/learn?id=几种设置回调函数的方式)。
  
  * **반환값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다.

  * **예시**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('connect', function ($server, $fd){
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();
```


## addListener()

감시할 포트를 추가합니다. 비즈니스 코드에서는 [Swoole\Server->getClientInfo](/server/methods?id=getclientinfo)를 호출하여 특정 연결이 어느 포트에서 온지를 확인할 수 있습니다.

```php
Swoole\Server->addListener(string $host, int $port, int $sockType): bool|Swoole\Server\Port
```

!> `1024` 이하의 포트를 감시하려면 `root` 권한이 필요합니다.  
메인 서버가 `WebSocket` 또는 `HTTP` 프로토콜인 경우, 새로 추가된 `TCP` 포트는 기본적으로 메인 `Server`의 프로토콜 설정을 이어받습니다. 새로운 프로토콜을 사용하려면 별도로 `set` 메서드를 호출하여 프로토콜을 설정해야 합니다. [상세 설명 참고](/server/port).
[여기](/server/server_port)를 클릭하여 `Swoole\Server\Port`의 상세 설명을 확인할 수 있습니다. 

  * **매개변수**

    * `string $host`

      * 기능: `__construct()`의 `$host`와 동일
      * 기본값: `__construct()`의 `$host`와 동일
      * 기타값: `__construct()`의 `$host`와 동일

    * `int $port`

      * 기능: `__construct()`의 `$port`와 동일
      * 기본값: `__construct()`의 `$port`와 동일
      * 기타값: `__construct()`의 `$port`와 동일

    * `int $sockType`

      * 기능: `__construct()`의 `$sockType`와 동일
      * 기본값: `__construct()`의 `$sockType`와 동일
      * 기타값: `__construct()`의 `$sockType`와 동일
  
  * **반환값**

    * 성공 시 `Swoole\Server\Port`을, 실패 시 `false`를 반환합니다.
!> -`유닉 소켓` 모드에서 `$host` 매개변수는 접근 가능한 파일 경로를 작성해야 하며, `$port` 매개변수는 무시됩니다.  

-`유닉 소켓` 모드에서, 클라이언트 `$fd`는 더 이상 숫자가 아니라 파일 경로의 문자열이 됩니다.  
-`리눅스` 시스템에서 `IPv6` 포트를 감시한 후 `IPv4` 주소로 연결을 수행할 수도 있습니다.


## listen()

이 방법은 `addlistener`의 별명입니다.

```php
Swoole\Server->listen(string $host, int $port, int $type): bool|Swoole\Server\Port
```


## addProcess()

사용자 정의 작업 프로세스를 추가합니다. 이 함수는 일반적으로 모니터링, 보고 또는 기타 특별한 작업을 수행하기 위해 특별한 작업 프로세스를 만드는 데 사용됩니다.

```php
Swoole\Server->addProcess(Swoole\Process $process): int
```

!> `start`를 실행할 필요가 없습니다. `Server`가 시작될 때 자동으로 프로세스를 만들고 지정된 서브 프로세스 함수를 실행합니다.

  * **매개변수**
  
    * [Swoole\Process](/process/process)

      * 기능: `Swoole\Process` 객체
      * 기본값: 없음
      * 기타 값: 없음

  * **반환값**

    * 성공 시 프로세스 ID 번호를 반환하며, 그렇지 않을 경우 프로그램은 치명적인 오류를 던집니다.

  * **주의**

    !> -생성된 서브 프로세스는 `$server` 객체가 제공하는 각종 메서드를 호출할 수 있습니다. 예를 들어 `getClientList/getClientInfo/stats` 등입니다.                                   
    -`Worker/Task` 프로세스에서는 `$process`가 제공하는 메서드를 호출하여 서브 프로세스와 통신할 수 있습니다.        
    -사용자 정의 프로세스에서는 `$server->sendMessage`을 호출하여 `Worker/Task` 프로세스와 통신할 수 있습니다.      
    -사용자 프로세스 내에서는 `Server->task/taskwait` 인터페이스를 사용할 수 없습니다.              
    -사용자 프로세스 내에서는 `Server->send/close` 등의 인터페이스를 사용할 수 있습니다.         
    -사용자 프로세스 내에서는 `while(true)`(아래 예와 같음) 또는 [EventLoop](/learn?id= 什么是eventloop) 루프(예를 들어 타이머를 만드는 것과 같은)를 수행해야 하며, 그렇지 않으면 사용자 프로세스는 계속해서 종료되고 재시작합니다.         

  * **생명주기**

    ?> -사용자 프로세스의 수명주기는 `Master`와 [Manager](/learn?id=manager进程)와 같으며, [reload](/server/methods?id=reload)에 영향을 받지 않습니다.     
    -사용자 프로세스는 `reload` 지시에 의해 제어되지 않으며, `reload` 시 사용자 프로세스에는 어떠한 메시지도 전달되지 않습니다.        
    -`shutdown`로 서버를 종료할 때, 사용자 프로세스에는 `SIGTERM` 신호를 전달하여 사용자 프로세스를 종료합니다.            
    -커스텀 프로세스는 `Manager` 프로세스에 위임되며, 치명적인 오류가 발생하면 `Manager` 프로세스는 새로운 프로세스를 다시 만듭니다.         
    -커스텀 프로세스는 `onWorkerStop` 등의 이벤트를 트리거하지 않습니다. 

  * **예제**

    ```php
    $server = new Swoole\Server('127.0.0.1', 9501);
    
    /**
     * 사용자 프로세스는 브로드캐스트 기능을 구현하여, 유닉 소켓 메시지를 순차적으로 수신하고, 서버의 모든 연결에 병행하여 전달합니다.
     */
    $process = new Swoole\Process(function ($process) use ($server) {
        $socket = $process->exportSocket();
        while (true) {
            $msg = $socket->recv();
            foreach ($server->connections as $conn) {
                $server->send($conn, $msg);
            }
        }
    }, false, 2, 1);
    
    $server->addProcess($process);
    
    $server->on('receive', function ($serv, $fd, $reactor_id, $data) use ($process) {
        // 수신한 메시지를 모든에게 전송합니다.
        $socket = $process->exportSocket();
        $socket->send($data);
    });
    
    $server->start();
    ```

    [Process 프로세스 간 통신 장면](/process/process?id=exportsocket)을 참고하세요.


## start()

서버를 시작하고 모든 `TCP/UDP` 포트를 감시합니다.

```php
Swoole\Server->start(): bool
```

!> 힌트: 다음은 [SWOOLE_PROCESS](/learn?id=swoole_process) 모드를 예로 들었습니다.

  * **힌트**

    - 성공 시 `worker_num+2` 개의 프로세스를 생성합니다. `Master` 프로세스 + `Manager` 프로세스 + `serv->worker_num` 개의 `Worker` 프로세스.  
    - 실패 시 즉시 `false`를 반환합니다.
    - 성공 시 이벤트 루프에 진입하여 클라이언트 연결 요청을 기다립니다. `start` 메서드 이후의 코드는 실행되지 않습니다.  
    - 서버가 종료되면, `start` 함수는 `true`를 반환하고 계속해서 아래의 코드를 실행합니다.  
    - `task_worker_num`가 설정되어 있다면 해당 수량의 [Task 프로세스](/learn?id=taskworker进程)가 증가합니다.   
    - 메서드 리스트 중 `start` 이전의 메서드는 `start` 호출 전에만 사용할 수 있으며, `start` 이후의 메서드는 [onWorkerStart](/server/events?id=onworkerstart), [onReceive](/server/events?id=onreceive) 등의 이벤트 콜백 함수에서만 사용할 수 있습니다.

  * **확장**
  
    * Master 주 프로세스

      * 주 프로세스 내에는 여러 개의 [Reactor](/learn?id=reactor线程)线程이 있으며, `epoll/kqueue/select` 기반으로 네트워크 이벤트를 순회합니다. 데이터를 받은 후 `Worker` 프로세스로 전송하여 처리합니다.
    
    * Manager 프로세스

      * 모든 `Worker` 프로세스를 관리하며, `Worker` 프로세스의 수명 주기가 종료되거나 예외가 발생하면 자동으로 회수하고 새로운 `Worker` 프로세스를 만듭니다.
    
    * Worker 프로세스

      * 받은 데이터를 처리하며, Protocols 해석 및 응답 요청 포함합니다. `worker_num`가 설정되지 않은 경우, 기본적으로 CPU 수량과 동일한 `Worker` 프로세스를 시작합니다.
      * 실패 확장은 치명적인 오류를 던집니다. `php error_log` 관련 정보를 확인해 주세요. `errno={number}`는 표준적인 `Linux Errno`이며, 관련 문서를 참고하세요.
      * `log_file` 설정이开启되어 있다면, 정보는 지정한 `Log` 파일에 출력됩니다.

  * **반환값**

    * 성공 시 `true`를 반환하고, 실패 시 `false`를 반환합니다.

  * **시작 실패 常见错误**

    * `bind` 포트 실패, 이유는 다른 프로세스가 이미 이 포트를 차지하고 있기 때문입니다.
    * 필수 콜백 함수가 설정되지 않아 실패합니다.
    * `PHP` 코드에 치명적인 오류가 존재합니다. `php_errors.log`을 확인해 주세요.
    * `ulimit -c unlimited`을 실행하여 `core dump`을 열어, segfault가 발생하는지 확인해 주세요.
    * `daemonize`를 종료하고 `log`를 끄어, 오류 메시지가 화면에 출력될 수 있도록 합니다.


## reload()

모든 Worker/Task 프로세스를 안전하게 재시작합니다.

```php
Swoole\Server->reload(bool $only_reload_taskworker = false): bool
```

!> 예를 들어: 바쁜 백엔드 서버는 항상 요청을 처리하고 있는데, 관리자가 프로세스를 `kill`하여 서버 프로그램을 종료/재시작하려고 하면, 코드가 절반만 실행 중에 종료될 수 있습니다.  
이런 상황에서는 데이터의 일관성이 깨질 수 있습니다. 예를 들어 거래 시스템에서, 결제 논리의 다음 단계는 발송입니다. 결제 논리 이후 프로세스가 종료되었다면, 사용자가 화폐를 지불했지만 발송하지 않았을 수 있습니다. 이로 인한 결과는 매우 심각합니다.  
`Swoole`은 유연한 종료/재시작 메커니즘을 제공하여, 관리자는 `Server`에 특정한 신호를 보내면 `Server`의 `Worker` 프로세스가 안전하게 끝날 수 있습니다. [Service를 올바르게 재시작하는 방법](/question/use?id=swoole如何正确的重启服务)을 참고하세요.

  * **매개변수**
  
    * `bool $only_reload_taskworker`

      * 기능: [Task 프로세스](/learn?id=taskworker进程)만 재시작할 것인지 여부
      * 기본값: false
      * 기타 값: true


!> -`reload`에는 보호 메커니즘이 있으며, 한 번의 `reload`이 진행 중인 동안 새로운 재시작 신호를 받으면 버립니다.

- `user/group`가 설정되어 있다면, `Worker` 프로세스는 `master` 프로세스에 메시지를 보낼 권한이 없을 수 있으며, 이러한 경우에는 `root` 계정을 사용하여 `shell`에서 `kill` 명령을 실행하여 재시작해야 합니다.
- `reload` 명령은 [addProcess](/server/methods?id=addProcess)에 추가된 사용자 프로세스에는 효과가 없습니다.

  * **반환값**

    * 성공 시 `true`를 반환하고, 실패 시 `false`를 반환합니다.
       
  * **확장**
  
    * **신호 전송**
    
        * `SIGTERM`: 주 프로세스/관리 프로세스에 이 신호를 보내면 서버는 안전하게 종료됩니다.
        * PHP 코드에서는 `$serv->shutdown()`를 호출하여 이 작업을 완료할 수 있습니다.
        * `SIGUSR1`: 주 프로세스/관리 프로세스에 `SIGUSR1` 신호를 보내면 모든 `Worker` 프로세스와 `TaskWorker` 프로세스를 원활하게 `restart`합니다.
        * `SIGUSR2`: 주 프로세스/관리 프로세스에 `SIGUSR2` 신호를 보내면 모든 `Task` 프로세스를 원활하게 재시작합니다.
        * PHP 코드에서는 `$serv->reload()`를 호출하여 이 작업을 완료할 수 있습니다.
        
    ```shell
    # 모든 worker 프로세스를 재시작합니다.
    kill -USR1 주 프로세스PID
    
    # Task 프로세스만 재시작합니다.
    kill -USR2 주 프로세스PID
    ```
      
      > [참조: Linux 신호 목록](/other/signal)

    * **Process 모드**
    
        `Process`에서 시작된 프로세스에서, 클라이언트의 `TCP` 연결은 `Master` 프로세스 내에서 유지되며, `worker` 프로세스의 재시작 및 예외 종료는 연결 자체에 영향을 미치지 않습니다.

    * **Base 모드**
    
        `Base` 모드에서, 클라이언트 연결은 직접 `Worker` 프로세스 내에서 유지되므로, `reload` 시 모든 연결이 끊어집니다.

    !> `Base` 모드는 [Task 프로세스](/learn?id=taskworker进程)의 `reload`을 지원하지 않습니다.
    
    * **Reload 유효 범위**

      `Reload` 작업은 `Worker` 프로세스가 시작된 후에 로딩된 PHP 파일만 재载入할 수 있습니다. `get_included_files` 함수를 사용하여, `WorkerStart` 이전에 로딩된 PHP 파일이 어떤 것인지를 나열할 수 있습니다. 이 목록에 있는 PHP 파일은, `reload` 작업을 수행해도 재载入할 수 없습니다. 서버를 종료하고 재시작해야만 효과가 적용됩니다.

    ```php
    $serv->on('WorkerStart', function(Swoole\Server $server, int $workerId) {
        var_dump(get_included_files()); // 이 배열에 있는 파일은 프로세스 시작 전에 로딩되었기 때문에, reload할 수 없습니다.
    });
    ```

    * **APC/OPcache**
    
        PHP에 `APC/OPcache`가开启了이라면, `reload` 로딩에 영향을 받습니다. 두 가지 해결책이 있습니다.
        
        * APC/OPcache의 `stat` 검사를 열어, 파일이 업데이트 될 경우 APC/OPcache가 자동으로 OPCode를 업데이트합니다.
        * `onWorkerStart`에서 파일(require, include 등)을 로딩하기 전에 `apc_clear_cache` 또는 `opcache_reset`를 실행하여 OPCode 캐시를 초기화합니다.

  * **주의**

    !> -원활한 재시작은 `Worker` 프로세스에서 `include/require`하는 PHP 파일, 예를 들어 `/server/events?id=onworkerstart` 또는 `/server/events?id=onreceive`에만 효과적입니다.
    - `Server`가 시작되기 전에 이미 `include/require`된 PHP 파일은, 원활한 재시작을 통해 재로딩할 수 없습니다.
    - `Server`의 구성 즉 `$serv->set()`에서 전달된 매개변수 설정은, 전체 `Server`를 종료/재시작해야만 재로딩할 수 있습니다.
    - `Server`는 내부 네트워크 포트를 감시할 수 있으며, 그런 다음 원격의 제어 명령을 수신하여 모든 `Worker` 프로세스를 재시작할 수 있습니다.
## 멈추기()

현재 `Worker` 프로세스를 중지시키고 즉시 `onWorkerStop` 콜백 함수를 트리거합니다.

```php
Swoole\Server->stop(int $workerId = -1, bool $waitEvent = false): bool
```

  * **매개변수**

    * `int $workerId`

      * 기능: 지정 `worker id`
      * 기본값: -1, 현재 프로세스를 나타냅니다
      * 기타값: 없습니다

    * `bool $waitEvent`

      * 기능: 종료 전략을 제어합니다. `false`는 즉시 종료하고 `true`는 이벤트 루프가 비어 있을 때까지 기다린 후 종료합니다
      * 기본값: false
      * 기타값: true

  * **반환값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

  * **알림**

    !> -[비동기 IO](/learn?id=同步io异步io) 서버는 `stop`을 호출하여 프로세스를 종료할 때, 여전히 대기 중인 이벤트가 있을 수 있습니다. 예를 들어 `Swoole\MySQL->query`를 사용하여 `SQL` 문장을 보냈지만, 아직 `MySQL` 서버에서 결과를 반환하기를 기다리고 있습니다. 이때 프로세스를 강제로 종료하면 `SQL`의 실행 결과가 손실됩니다.  
    - `$waitEvent = true`를 설정하면,底层은 [비동기 안전 재시작](/question/use?id=swoole如何正确的重启服务) 전략을 사용합니다. 먼저 `Manager` 프로세스에 알리면, 새로운 `Worker`를 재시작하여 새로운 요청을 처리합니다. 현재의 오래된 `Worker`는 이벤트를 기다리다가 이벤트 루프가 비거나 `max_wait_time`이 초과될 때까지 프로세스를 종료하며, 비동기 이벤트의 안전성을 최대한 보장합니다.


## 중지()

서비스를 닫습니다.

```php
Swoole\Server->shutdown(): bool
```

  * **반환값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

  * **알림**

    * 이 함수는 `Worker` 프로세스 내에서 사용할 수 있습니다.
    * 메인 프로세스에 `SIGTERM`을 보내기도 서비스의 중지를 실현할 수 있습니다.

```shell
kill -15 主进程PID
```


## tick()

`tick` 타이머를 추가하여 사용자 정의 콜백 함수를 사용할 수 있습니다. 이 함수는 [Swoole\Timer::tick](/timer?id=tick)의 별명입니다.

```php
Swoole\Server->tick(int $millisecond, callable $callback): void
```

  * **매개변수**

    * `int $millisecond`

      * 기능: 간격 시간【밀리초】
      * 기본값: 없습니다
      * 기타값: 없습니다

    * `callable $callback`

      * 기능: 콜백 함수
      * 기본값: 없습니다
      * 기타값: 없습니다

  * **주의**
  
    !> -`Worker` 프로세스가 실행을 종료하면 모든 타이머가 자동으로 파괴됩니다  
    -`tick/after` 타이머는 `Server->start` 이전에 사용할 수 없습니다  
    -`Swoole5` 이후, 이 별명 사용법이 삭제되었습니다. 직접 `Swoole\Timer::tick()`를 사용해 주세요

  * **예제**

    * [onReceive](/server/events?id=onreceive)에서 사용하는 예

    ```php
    function onReceive(Swoole\Server $server, int $fd, int $reactorId, mixed $data)
    {
        $server->tick(1000, function () use ($server, $fd) {
            $server->send($fd, "hello world");
        });
    }
    ```

    * [onWorkerStart](/server/events?id=onworkerstart)에서 사용하는 예

    ```php
    function onWorkerStart(Swoole\Server $server, int $workerId)
    {
        if (!$server->taskworker) {
            $server->tick(1000, function ($id) {
              var_dump($id);
            });
        } else {
            //task
            $server->tick(1000);
        }
    }
    ```


## after()

일회성 타이머를 추가하여 실행이 완료되면 즉시 파괴됩니다. 이 함수는 [Swoole\Timer::after](/timer?id=after)의 별명입니다.

```php
Swoole\Server->after(int $millisecond, callable $callback)
```

  * **매개변수**

    * `int $millisecond`

      * 기능: 실행 시간【밀리초】
      * 기본값: 없습니다
      * 기타값: 없습니다
      * 버전 영향: `Swoole v4.2.10` 이하 버전에서 최대 86400000을 초과할 수 없습니다

    * `callable $callback`

      * 기능: 콜백 함수, 호출 가능한 것이어야 합니다. `callback` 함수는 어떠한 매개변수도 받지 않습니다
      * 기본값: 없습니다
      * 기타값: 없습니다

  * **주의**
  
    !> -타이머의 수명은 프로세스 단위입니다. `reload` 또는 `kill`로 프로세스를 재시작하거나 종료하면 모든 타이머가 파괴됩니다  
    -타이머에 중요한 논리나 데이터가 있을 경우, `onWorkerStop` 콜백 함수에서 구현하거나 [서비스를 올바르게 재시작하는 방법](/question/use?id=swoole如何正确的重启服务)을 참고해 주세요  
    -`Swoole5` 이후, 이 별명 사용법이 삭제되었습니다. 직접 `Swoole\Timer::after()`를 사용해 주세요


## defer()

함수를 지연하여 실행합니다. 이 함수는 [Swoole\Event::defer](/event?id=defer)의 별명입니다.

```php
Swoole\Server->defer(Callable $callback): void
```

  * **매개변수**

    * `Callable $callback`

      * 기능: 콜백 함수【필수】, 실행 가능한 함수 변수일 수도 있고, 문자열, 배열, 익명 함수일 수도 있습니다
      * 기본값: 없습니다
      * 기타값: 없습니다

  * **주의**

    !> -底层은[EventLoop](/learn?id=什么是eventloop) 루프가 완료된 후에 이 함수를 실행합니다. 이 함수의 목적은 일부 PHP 코드를 지연하여 실행하고, 프로그램이 다른 `IO` 이벤트를 우선 처리하도록 하는 것입니다. 예를 들어 어떤 콜백 함수가 CPU 집약적인 계산을 해야 하고 급하지 않다면, 프로세스가 다른 이벤트를 처리한 후에 CPU 집약적인 계산을 진행할 수 있습니다  
    -底层은 `defer`의 함수가 즉시 실행될 것이라고 보장하지 않습니다. 시스템의 핵심 논리이거나 급하게 실행해야 하는 경우, `after` 타이머를 사용해 주세요  
    -`onWorkerStart` 콜백에서 `defer`를 사용하면, 이벤트가 발생할 때까지 기다려야 콜백이 이루어집니다
    -`Swoole5` 이후, 이 별명 사용법이 삭제되었습니다. 직접 `Swoole\Event::defer()`를 사용해 주세요

  * **예제**

```php
function query($server, $db) {
    $server->defer(function() use ($db) {
        $db->close();
    });
}
```


## clearTimer()

`tick/after` 타이머를 제거합니다. 이 함수는 [Swoole\Timer::clear](/timer?id=clear)의 별명입니다.

```php
Swoole\Server->clearTimer(int $timerId): bool
```

  * **매개변수**

    * `int $timerId`

      * 기능: 지정한 타이머 ID
      * 기본값: 없습니다
      * 기타값: 없습니다

  * **반환값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

  * **주의**

    !> -`clearTimer`는 현재 프로세스의 타이머만 제거할 수 있습니다     
    -`Swoole5` 이후, 이 별명 사용법이 삭제되었습니다. 직접 `Swoole\Timer::clear()`를 사용해 주세요 

  * **예제**

```php
$timerId = $server->tick(1000, function ($timerId) use ($server) {
    $server->clearTimer($timerId);//$id는 타이머의 ID입니다
});
```


## close()

클라이언트 연결을 닫습니다.

```php
Swoole\Server->close(int $fd, bool $reset = false): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 닫을 `fd` (파일 디스크립터)를 지정합니다
      * 기본값: 없습니다
      * 기타값: 없습니다

    * `bool $reset`

      * 기능: `true`로 설정하면 강제로 연결을 닫고, 보낸 메시지 큐를 버립니다
      * 기본값: false
      * 기타값: true

  * **반환값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

  * **주의**


  !> -`서버`가 주도적으로`close` 연결을 할 경우에도 `[onClose](/server/events?id=onclose)` 이벤트가 발생합니다  

- `close` 이후에 청소 로직을 작성하지 마십시오. 이것은 `[onClose](/server/events?id=onclose)` 콜백에서 처리해야 합니다  
- `HTTP\Server`의 `fd`는 상층 콜백 방법의 `response`에서 얻습니다

  * **예시**

```php
$server->on('request', function ($request, $response) use ($server) {
    $server->close($response->fd);
});
```


## send()

클라이언트에 데이터를 보냅니다.

```php
Swoole\Server->send(int|string $fd, string $data, int $serverSocket = -1): bool
```

  * **매개변수**

    * `int|string $fd`

      * 기능: 지정된 클라이언트의 파일 디스크립터나 unix 소켓 경로
      * 기본값: 없음
      * 기타 값: 없음

    * `string $data`

      * 기능: 보낼 데이터, `TCP` 프로토콜은 최대 `2M`을 초과할 수 없으며, [buffer_output_size](/server/setting?id=buffer_output_size)를 변경하여 보낼 수 있는 최대 패킷 길이를 변경할 수 있습니다
      * 기본값: 없음
      * 기타 값: 없음

    * `int $serverSocket`

      * 기능: [UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) 대상을 대상으로 데이터를 보낼 때 이 매개변수를 필요로 합니다, TCP 클라이언트는 필수적으로 작성하지 않습니다
      * 기본값:-1, 현재 监听 중인 udp 포트를 나타냅니다
      * 기타 값: 없음

  * **귀속값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

  * **알림**

    !> 보낼 수 있는 과정은 비동기적이며, 저층은 자동으로 쓰기 가능 상태를 감지하고 데이터를 클라이언트에게 점차 보냅니다. 즉, `send`가 반환된 즉시 대상이 데이터를 받았다는 의미는 아닙니다.

    * 보안성
      * `send` 작업은 원자성이 있으며, 여러 프로세스가 동시에 같은 `TCP` 연결에 데이터를 보내면 데이터가 섞이지 않습니다

    * 길이 제한
      * `2M`을 초과하는 데이터를 보내려면, 임시 파일에 데이터를 쓰고 나서 `sendfile` 인터페이스를 통해 보낼 수 있습니다
      * [buffer_output_size](/server/setting?id=buffer_output_size) 매개변수를 설정하여 보낼 수 있는 길이 제한을 변경할 수 있습니다
      * `8K` 이상의 데이터를 보낼 때, 저층은 `Worker` 프로세스의 공유 메모리를 사용하게 되며, 한 번의 `Mutex->lock` 작업이 필요합니다

    * 버퍼
      * `Worker` 프로세스의 [unixSocket](/learn?id= 什么是IPC) 버퍼가 가득 찰 경우, `8K`의 데이터를 보낼 때 임시 파일 저장 사용을 적용합니다
      * 같은 클라이언트에게 연속해서 대량의 데이터를 보내면, 클라이언트가 받지 못해 `Socket` 메모리 버퍼가 가득 차게 되며, Swoole 저층은 즉시 `false`를 반환합니다. `false`일 때는 데이터를 디스크에 저장하여 클라이언트가 보낸 데이터를 모두 받을 때까지 기다렸다가 다시 보낼 수 있습니다

    * [코루outine 스케줄링](/coroutine?id=协程调度)
      * 코루outine 모드에서 [send_yield](/server/setting?id=send_yield)가开启되어 있을 경우 `send`가 버퍼가 가득 찰을 때 자동으로 중단하고, 대상이 일부를 읽어간 후 코루outine을 회복하여 데이터 보낼 수 있습니다.

    * [UnixSocket](/learn?id=什么是IPC)
      * [UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) 포트를 监听할 때, `send`를 통해 대상에게 데이터를 보낼 수 있습니다.

      ```php
      $server->on("packet", function (Swoole\Server $server, $data, $addr){
          $server->send($addr['address'], 'SUCCESS', $addr['server_socket']);
      });
      ```


## sendfile()

TCP 클라이언트 연결에 파일을 보냅니다.

```php
Swoole\Server->sendfile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 지정된 클라이언트의 파일 디스크립터
      * 기본값: 없음
      * 기타 값: 없음

    * `string $filename`

      * 기능: 보낼 파일 경로, 파일이 존재하지 않을 경우 `false`를 반환합니다
      * 기본값: 없음
      * 기타 값: 없음

    * `int $offset`

      * 기능: 파일의 오프셋을 지정하여, 파일의 특정 위치부터 데이터를 보낼 수 있습니다
      * 기본값: 0 【기본적으로 `0`은 파일의 헤드 부분부터 보낼 것을 나타냅니다】
      * 기타 값: 없음

    * `int $length`

      * 기능: 보낼 길이를 지정합니다
      * 기본값: 파일의 크기
      * 기타 값: 없음

  * **귀속값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

  * **주의**

  !> 이 함수와 `Server->send`는 모두 클라이언트에게 데이터를 보냅니다만, 차이점은 `sendfile`의 데이터가 지정된 파일에서 온 것입니다


## sendto()

임의의 클라이언트 `IP:PORT`에 `UDP` 패킷을 보냅니다.

```php
Swoole\Server->sendto(string $ip, int $port, string $data, int $serverSocket = -1): bool
```

  * **매개변수**

    * `string $ip`

      * 기능: 지정된 클라이언트의 `ip`
      * 기본값: 없음
      * 기타 값: 없음

      ?> `$ip`은 `IPv4` 혹은 `IPv6` 문자열로, 예를 들어 `192.168.1.102`와 같습니다. 만약 `IP`이 잘못되어 있다면 오류를 반환합니다

    * `int $port`

      * 기능: 지정된 클라이언트의 `port`
      * 기본값: 없음
      * 기타 값: 없음

      ?> `$port`은 `1-65535`의 네트워크 포트 번호로, 포트가 잘못되어 있다면 보낼 수 없게 됩니다

    * `string $data`

      * 기능: 보낼 데이터 내용으로, 텍스트나 이진 콘텐츠가 될 수 있습니다
      * 기본값: 없음
      * 기타 값: 없음

    * `int $serverSocket`

      * 기능: 데이터 패킷을 보낼 때 사용하는 포트의 해당 포트 `server_socket` 디스크립터를 지정합니다【[onPacket 이벤트](/server/events?id=onpacket)의 `$clientInfo`에서 얻을 수 있습니다】
      * 기본값:-1, 현재 监听 중인 udp 포트를 나타냅니다
      * 기타 값: 없음

  * **귀속값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

      ?> 서버는 동시에 여러 개의 `UDP` 포트를监听할 수 있으며, [다중 포트监听](/server/port)을 참고하세요, 이 매개변수는 어떤 포트에서 데이터 패킷을 보낼지 지정할 수 있습니다

  * **주의**

  !> `UDP`의 포트를 监听后에만 `IPv4` 주소에 데이터를 보낼 수 있습니다  
  `UDP6`의 포트를 监听后에만 `IPv6` 주소에 데이터를 보낼 수 있습니다

  * **예시**

```php
// IP 주소가 220.181.57.216인 호스트의 9502번 포트에 "hello world" 문자열을 보냅니다.
$server->sendto('220.181.57.216', 9502, "hello world");
// IPv6 서버에 UDP 패킷을 보냅니다
$server->sendto('2600:3c00::f03c:91ff:fe73:e98f', 9501, "hello world");
```


## sendwait()

클라이언트에게 동기적으로 데이터를 보냅니다.

```php
Swoole\Server->sendwait(int $fd, string $data): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 지정된 클라이언트의 파일 디스크립터
      * 기본값: 없음
      * 기타 값: 없음

    * `string $data`

      * 기능: 보낼 데이터
      * 기본값: 없음
      * 기타 값: 없음

  * **귀속값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

  * **알림**

    * 일부 특수한 상황에서, `Server`는 연속해서 클라이언트에게 데이터를 보내려고 합니다만, `Server->send` 데이터 보낼 수 있는 인터페이스는 순수 비동기적이며, 대량의 데이터를 보내면 메모리 보낼 수 있는 큐가 가득 차게 됩니다.

    * `Server->sendwait`를 사용하면 이러한 문제를 해결할 수 있습니다. `Server->sendwait`는 연결이 쓸 수 있을 때까지 기다리며, 데이터가 모두 보낸 후에야 반환합니다.

  * **주의**

  !> `sendwait`는 현재 [SWOOLE_BASE](/learn?id=swoole_base) 모드에서만 사용할 수 있습니다  
  `sendwait`는 본토나 내부 네트워크 통신에만 사용하며, 외부 네트워크 연결에서는 `sendwait`를 사용할 수 없습니다. `enable_coroutine`=>true(기본적으로开启)인 경우에도 이 함수를 사용할 수 없으며, 다른 코루outine을 막아들일 수 있습니다. 오직 동기적으로 막힐 수 있는 서버에서만 사용할 수 있습니다.
## sendMessage()

임의의 `Worker` 프로세스나 [Task 프로세스](/학습?id=taskworker 프로세스)에 메시지를 보냅니다. 주 프로세스와 관리 프로세스에서 호출할 수 있습니다. 메시지를 받은 프로세스는 `onPipeMessage` 이벤트를 트리거합니다.

```php
Swoole\Server->sendMessage(mixed $message, int $workerId): bool
```

  * **매개변수**

    * `mixed $message`

      * 기능: 보낼 메시지의 데이터 내용으로, 길이 제한이 없으나 `8K` 초과 시 임시 파일을 사용할 수 있습니다.
      * 기본값: 없음
      * 기타값: 없음

    * `int $workerId`

      * 기능: 대상 프로세스의 `ID`로, 범위는 [$worker_id](/서버/속성?id=worker_id)을 참고하세요.
      * 기본값: 없음
      * 기타값: 없음

  * **알림**

    * `Worker` 프로세스 내에서 `sendMessage`을 호출하면 [비동기 IO](/학습?id=동기io 비동기io)이며, 메시지는 먼저 버퍼에 저장되고, 가질 수 있을 때 [unixSocket](/학습?id=IPC에무슨것이있나요)에 이 메시지를 보냅니다.
    * [Task 프로세스](/학습?id=taskworker 프로세스) 내에서 `sendMessage`을 호출하면 기본적으로 [동기 IO](/학습?id=동기io 비동기io)이지만, 일부 상황에서는 자동으로 비동기 IO로 전환되는데, 자세한 내용은 [동기 IO에서 비동기 IO로 전환되는 것](/학습?id=동기io 비동기io)을 참고하세요.
    * [User 프로세스](/서버/메서드?id=addprocess) 내에서 `sendMessage`을 호출하면 Task와 동일하며, 기본적으로 동기적이고 막힙니다. 자세한 내용은 [동기 IO에서 비동기 IO로 전환되는 것](/학습?id=동기io 비동기io)을 참고하세요.

  * **주의**


  !> - `sendMessage()`이 [비동기 IO](/학습?id=동기io 비동기io)인 경우, 상대방 프로세스가 여러 가지 이유로 데이터를 받지 않으면 계속해서 `sendMessage()`을 호출하면 많은 메모리 자원을 차지하게 됩니다. 응답 메커니즘을 추가하여 상대방이 응답하지 않을 경우 호출을 중단할 수 있습니다;  

- `MacOS/FreeBSD`에서 `2K` 초과 시 임시 파일을 사용합니다;  

- [sendMessage](/서버/메서드?id=sendMessage)를 사용하려면 `onPipeMessage` 이벤트 리액션 함수를 등록해야 합니다;  
- [task_ipc_mode](/서버/설정?id=task_ipc_mode) = 3을 설정하면 특정 Task 프로세스에 [sendMessage](/서버/메서드?id=sendMessage)을 사용하여 메시지를 보낼 수 없습니다.

  * **예제**

```php
$server = new Swoole\Server('0.0.0.0', 9501);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 2,
));
$server->on('pipeMessage', function ($server, $src_worker_id, $data) {
    echo "#{$server->worker_id} message from #$src_worker_id: $data\n";
});
$server->on('task', function ($server, $task_id, $src_worker_id, $data) {
    var_dump($task_id, $src_worker_id, $data);
});
$server->on('finish', function ($server, $task_id, $data) {

});
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    if (trim($data) == 'task') {
        $server->task("async task coming");
    } else {
        $worker_id = 1 - $server->worker_id;
        $server->sendMessage("hello task process", $worker_id);
    }
});

$server->start();
```


## exist()

해당 `fd`에 해당하는 연결이 존재하는지 확인합니다.

```php
Swoole\Server->exist(int $fd): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 파일 디스크립터
      * 기본값: 없음
      * 기타값: 없음

  * **반환값**

    * 존재할 경우 `true`, 존재하지 않을 경우 `false`를 반환합니다

  * **알림**
  
    * 이 인터페이스는 공유 메모리를 기반으로 계산하며, 어떠한 `IO` 운영도 하지 않습니다


## pause()

데이터 수신을 중지합니다.

```php
Swoole\Server->pause(int $fd): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 지정한 파일 디스크립터
      * 기본값: 없음
      * 기타값: 없음

  * **반환값**

    * 성공 시 `true`, 실패 시 `false`를 반환합니다

  * **알림**

    * 이 함수를 호출하면 해당 연결이 [EventLoop](/학습?id=무엇이eventloop인가)에서 제거되며, 더 이상 클라이언트의 데이터를 수신하지 않습니다.
    * 이 함수는 전송 큐의 처리를 영향을 미치지 않습니다
    * `SWOOLE_PROCESS` 모드에서만 사용할 수 있으며, `pause`를 호출한 후에는 일부 데이터가 이미 `Worker` 프로세스에 도착할 수 있기 때문에 여전히 [onReceive](/서버/이벤트?id=onreceive) 이벤트가 트리거될 수 있습니다


## resume()

데이터 수신을 재개합니다. `pause`方法与 함께 사용됩니다.

```php
Swoole\Server->resume(int $fd): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 지정한 파일 디스크립터
      * 기본값: 없음
      * 기타값: 없음

  * **반환값**

    * 성공 시 `true`, 실패 시 `false`를 반환합니다

  * **알림**

    * 이 함수를 호출하면 해당 연결이 다시 [EventLoop](/학습?id=무엇이eventloop인가)에 추가되어 클라이언트의 데이터 수신을 재개합니다


## getCallback()

Server의 지정된 이름의 콜백 함수를 가져옵니다.

```php
Swoole\Server->getCallback(string $event_name): \Closure|string|null|array
```

  * **매개변수**

    * `string $event_name`

      * 기능: 이벤트 이름으로, `on`을 붙이지 않고, 대소문 구분이 없습니다.
      * 기본값: 없음
      * 기타값: [이벤트](/서버/이벤트)를 참고하세요.

  * **반환값**

    * 해당 콜백 함수가 존재할 경우, 다양한 [콜백 함수 설정 방법](/학습?id=콜백함수설정법)에 따라 `Closure` / `string` / `array`를 반환합니다.
    * 해당 콜백 함수가 존재하지 않을 경우, `null`을 반환합니다.


## getClientInfo()

연결 정보를 가져옵니다. 별명은 `Swoole\Server->connection_info()`입니다.

```php
Swoole\Server->getClientInfo(int $fd, int $reactorId = -1, bool $ignoreError = false): false|array
```

  * **매개변수**

    * `int $fd`

      * 기능: 지정한 파일 디스크립터
      * 기본값: 없음
      * 기타값: 없음

    * `int $reactorId`

      * 기능: 연결이 있는 [Reactor](/학습?id=reactor스레드) 스레드의 `ID`로, 현재는 아무런 기능도 없으며 단지 API 호환성을 유지하기 위해서입니다.
      * 기본값: -1
      * 기타값: 없음

    * `bool $ignoreError`

      * 기능: 오류를 무시할지 여부로, `true`로 설정하면 연결이 닫힌 상태에서도 연결 정보를 반환하고, `false`로 설정하면 연결이 닫히면 `false`를 반환합니다.
      * 기본값: false
      * 기타값: 없음

  * **알림**

    * 클라이언트 인증서

      * [onConnect](/서버/이벤트?id=onconnect)이 트리거되는 프로세스에서만 인증서를 가져올 수 있습니다.
      * 형식은 `x509`이며, `openssl_x509_parse` 함수를 사용하여 인증서 정보를 가져올 수 있습니다.

    * [dispatch_mode](/서버/설정?id=dispatch_mode) = 1/3을 설정했을 때, 이러한 패킷 분배 전략이 무状态 서비스를 사용하는 경우, 연결이 끊어지면 관련 정보가 직접 메모리에서 삭제되므로 `Server->getClientInfo`는 관련 연결 정보를 가져올 수 없습니다.

  * **반환값**

    * 호출에 실패하면 `false`를 반환합니다.
    * 호출에 성공하면 클라이언트 정보를 포함한 `array`를 반환합니다.

```php
$fd_info = $server->getClientInfo($fd);
var_dump($fd_info);

array(15) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(25)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(39136)
  ["remote_ip"]=>
  string(9) "127.0.0.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1677322106)
  ["last_time"]=>
  int(1677322106)
  ["last_recv_time"]=>
  float(1677322106.901918)
  ["last_send_time"]=>
  float(0)
  ["last_dispatch_time"]=>
  float(0)
  ["close_errno"]=>
  int(0)
  ["recv_queued_bytes"]=>
  int(78)
  ["send_queued_bytes"]=>
  int(0)
}
```
매개변수 | 역할
---|---
server_port | 서버 수신 포트
server_fd | 서버 fd
socket_fd | 클라이언트 fd
socket_type | 소켓 유형
remote_port | 클라이언트 포트
remote_ip | 클라이언트 IP
reactor_id | 어느 Reactor 스레드에서 온 것인지
connect_time | 클라이언트가 서버에 연결된 시간, 초 단위로 마스터 프로세스가 설정함
last_time | 마지막으로 데이터를 받은 시간, 초 단위로 마스터 프로세스가 설정함
last_recv_time | 마지막으로 데이터를 받은 시간, 초 단위로 마스터 프로세스가 설정함
last_send_time | 마지막으로 데이터를 보낸 시간, 초 단위로 마스터 프로세스가 설정함
last_dispatch_time | worker 프로세스가 데이터를 받은 시간
close_errno | 연결이 비정상적으로 닫힌 경우의 오류 코드, 연결이 비정상적으로 닫히면 close_errno의 값은 비zero이며, Linux 오류 정보 목록을 참고할 수 있음
recv_queued_bytes | 처리 대기 중인 데이터 양
send_queued_bytes | 전송 대기 중인 데이터 양
websocket_status | [선택 사항] WebSocket 연결 상태, 서버가 Swoole\WebSocket\Server인 경우 이 추가 정보를 제공함
uid | [선택 사항] bind을 사용하여 사용자 ID를 연결에 묶을 경우 이 추가 정보를 제공함
ssl_client_cert | [선택 사항] SSL 터널 암호화 사용 및 클라이언트가 인증서를 설정한 경우 이 추가 정보를 제공함




## getClientList()

현재 `Server`의 모든 클라이언트 연결을 탐색하는 방법으로, `Server::getClientList` 메서드는 공유 메모리를 기반으로 하며, `IOWait`가 존재하지 않아 탐색 속도가 매우 빠름. 또한 `getClientList`는 모든 `TCP` 연결을 반환하며, 현재 `Worker` 프로세스의 `TCP` 연결만이 아닙니다. 별명은 `Swoole\Server->connection_list()`입니다.

```php
Swoole\Server->getClientList(int $start_fd = 0, int $pageSize = 10): false|array
```

  * **매개변수**

    * `int $start_fd`

      * 기능: 시작 fd 지정
      * 기본값: 0
      * 기타값: 없음

    * `int $pageSize`

      * 기능: 한 페이지에 몇 개를 가져옴, 최대 100을 초과할 수 없음
      * 기본값: 10
      * 기타값: 없음

  * **반환값**

    * 성공 시fd의 숫자 배열을 반환하며, 배열은 작은 것부터 큰 것 순으로 정렬됨. 마지막 fd는 새로운 시작 fd로 삼아 다시 가져옴
    * 실패 시 `false` 반환

  * **알림**

    * [Server::$connections](/server/properties?id=connections) 이터레이터를 사용하여 연결을 탐색하는 것이 권장됨
    * `getClientList`은 `TCP` 클라이언트에만 사용 가능하며, `UDP` 서버는 클라이언트 정보를 스스로 유지해야 함
    * [SWOOLE_BASE](/learn?id=swoole_base) 모드에서는 현재 프로세스의 연결만을 가져올 수 있음

  * **예시**
  
```php
$start_fd = 0;
while (true) {
  $conn_list = $server->getClientList($start_fd, 10);
  if ($conn_list === false || count($conn_list) === 0) {
      echo "finish\n";
      break;
  }
  $start_fd = end($conn_list);
  var_dump($conn_list);
  foreach ($conn_list as $fd) {
      $server->send($fd, "broadcast");
  }
}
```


## bind()

클라이언트 연결을 사용자 정의의 `UID`에 묶습니다. [dispatch_mode](/server/setting?id=dispatch_mode)=5로 설정하여 이 값으로 `hash` 고정 배치를 할 수 있습니다. 이는 특정 `UID`의 모든 연결이 동일한 `Worker` 프로세스에 할당될 것을 보장합니다.

```php
Swoole\Server->bind(int $fd, int $uid): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 연결의 `fd` 지정
      * 기본값: 없음
      * 기타값: 없음

    * `int $uid`

      * 기능: 묶을 `UID`, 비 `0`의 숫자여야 함
      * 기본값: 없음
      * 기타값: `UID`은 최대 `4294967295`, 최소 `-2147483648` 미만이 될 수 없음

  * **반환값**

    * 성공 시 `true` 반환, 실패 시 `false` 반환

  * **알림**

    * `$serv->getClientInfo($fd)`를 사용하여 연결이 묶인 `UID`의 값을 확인할 수 있음
    * 기본의 [dispatch_mode](/server/setting?id=dispatch_mode)=2 설정에서, `Server`는 `socket fd`에 따라 연결 데이터를 다른 `Worker` 프로세스에 할당합니다. `fd`는 불안정하므로, 클라이언트가 연결을 끊고 다시 연결하면, `fd`가 변경되어 해당 클라이언트의 데이터가 다른 `Worker`로 할당됩니다. `bind`를 사용하면 사용자 정의의 `UID`에 따라 할당할 수 있습니다. 연결이 끊어지고 다시 연결해도 동일 `UID`의 `TCP` 연결 데이터는 동일한 `Worker` 프로세스에 할당됩니다.

    * 시계 문제

      * 클라이언트가 서버에 연결한 후 연속해서 여러 패킷을 보내면, 시계 문제가 발생할 수 있습니다. `bind` 작업 시, 이후의 패킷은 이미 `dispatch`되었을 수 있으며, 이러한 데이터 패킷은 여전히 `fd`로 모듈하여 현재 프로세스에 할당됩니다. 오직 `bind` 이후에 새로 받은 패킷만이 `UID`로 모듈하여 할당됩니다.
      * 따라서 `bind` 메커니즘을 사용하려면 네트워크 통신 프로토콜에 인사 단계를 설계해야 합니다. 클라이언트가 성공적으로 연결한 후에는 먼저 인사 요청을 보내고, 이후 클라이언트는 아무런 패킷도 보내지 않습니다. 서버가 `bind`를 완료하고 응답한 후에 클라이언트는 새로운 요청을 보냅니다.

    * 재바인딩

      * 일부 경우, 비즈니스 로직은 사용자의 연결을 재바인딩하여 `UID`를 변경해야 합니다. 이때는 연결을 끊고, 새로운 `TCP` 연결을 구축하고 인사를 하여 새로운 `UID`에 바인딩할 수 있습니다.

    * 부정수 `UID` 바인딩

      * 만약 바인딩된 `UID`가 부정수라면, 기본적으로 `32비트 무자리 정수`로 변환되며, PHP 계층에서는 `32비트 유자리 정수`로 변환해야 합니다. 다음과 같은 식으로 사용할 수 있습니다:
      
  ```php
  $uid = -10;
  $server->bind($fd, $uid);
  $bindUid = $server->connection_info($fd)['uid'];
  $bindUid = $bindUid >> 31 ? (~($bindUid - 1) & 0xFFFFFFFF) * -1 : $bindUid;
  var_dump($bindUid === $uid);
  ```

  * **주의**


!> - `dispatch_mode=5`로 설정될 경우에만 유효함  

- `UID`가 묶이지 않은 경우에는 기본적으로 `fd`로 모듈하여 할당함  
- 동일한 연결은 한 번만 `bind`될 수 있으며, 이미 `UID`가 묶여 있다면 다시 `bind`을 호출하면 `false`를 반환함

  * **예시**

```php
$serv = new Swoole\Server('0.0.0.0', 9501);

$serv->fdlist = [];

$serv->set([
    'worker_num' => 4,
    'dispatch_mode' => 5,   //uid dispatch
]);

$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "{$fd} connect, worker:" . $serv->worker_id . PHP_EOL;
});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    $conn = $serv->connection_info($fd);
    print_r($conn);
    echo "worker_id: " . $serv->worker_id . PHP_EOL;
    if (empty($conn['uid'])) {
        $uid = $fd + 1;
        if ($serv->bind($fd, $uid)) {
            $serv->send($fd, "bind {$uid} success");
        }
    } else {
        if (!isset($serv->fdlist[$fd])) {
            $serv->fdlist[$fd] = $conn['uid'];
        }
        print_r($serv->fdlist);
        foreach ($serv->fdlist as $_fd => $uid) {
            $serv->send($_fd, "{$fd} say:" . $data);
        }
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "{$fd} Close". PHP_EOL;
    unset($serv->fdlist[$fd]);
});

$serv->start();
```
## stats()

현재 `Server`의 활동 중인 `TCP` 연결 수, 시작 시간 등의 정보를 얻고, `accept/close`(연결 구축/해제)의 총 횟수 등의 정보를 제공합니다.

```php
Swoole\Server->stats(): array
```

  * **예시**

```php
array(25) {
  ["start_time"]=>
  int(1677310656)
  ["connection_num"]=>
  int(1)
  ["abort_count"]=>
  int(0)
  ["accept_count"]=>
  int(1)
  ["close_count"]=>
  int(0)
  ["worker_num"]=>
  int(2)
  ["task_worker_num"]=>
  int(4)
  ["user_worker_num"]=>
  int(0)
  ["idle_worker_num"]=>
  int(1)
  ["dispatch_count"]=>
  int(1)
  ["request_count"]=>
  int(0)
  ["response_count"]=>
  int(1)
  ["total_recv_bytes"]=>
  int(78)
  ["total_send_bytes"]=>
  int(165)
  ["pipe_packet_msg_id"]=>
  int(3)
  ["session_round"]=>
  int(1)
  ["min_fd"]=>
  int(4)
  ["max_fd"]=>
  int(25)
  ["worker_request_count"]=>
  int(0)
  ["worker_response_count"]=>
  int(1)
  ["worker_dispatch_count"]=>
  int(1)
  ["task_idle_worker_num"]=>
  int(4)
  ["tasking_num"]=>
  int(0)
  ["coroutine_num"]=>
  int(1)
  ["coroutine_peek_num"]=>
  int(1)
  ["task_queue_num"]=>
  int(1)
  ["task_queue_bytes"]=>
  int(1)
}
```


매개변수 | 역할
---|---
start_time | 서버 시작 시간
connection_num | 현재 연결 수
abort_count | 거절된 연결 수
accept_count | 수락된 연결 수
close_count | 닫힌 연결 수
worker_num  | 실행 중인 worker 프로세스 수
task_worker_num  | 실행 중인 task_worker 프로세스 수【`v4.5.7`부터 사용 가능】
user_worker_num  | 실행 중인 task worker 프로세스 수
idle_worker_num | 빈 worker 프로세스 수
dispatch_count | Server가 Worker에 보낸 패킷 수【`v4.5.7`부터 사용 가능하며, [SWOOLE_PROCESS](/learn?id=swoole_process) 모드에서만 유효】
request_count | Server가 받은 요청 수【onReceive, onMessage, onRequest, onPacket의 네 가지 데이터 요청만을 계산하여 request_count를 증가시킵니다】
response_count | Server가 보낸 응답 수
total_recv_bytes| 데이터 수신 총字节수
total_send_bytes | 데이터 전송 총字节수
pipe_packet_msg_id | 프로세스 간 통신 id
session_round | 시작 session id
min_fd | 최소 연결 fd
max_fd | 최대 연결 fd
worker_request_count | 현재 Worker 프로세스가 받은 요청 수【worker_request_count가 max_request를 초과하면 worker 프로세스가 종료됩니다】
worker_response_count | 현재 Worker 프로세스의 응답 수
worker_dispatch_count | master 프로세스가 현재 Worker 프로세스에 작업을 전달하는 카운트로, master 프로세스가 dispatch를 할 때 카운트가 증가합니다
task_idle_worker_num | 빈 task 프로세스 수
tasking_num | 작업 중인 task 프로세스 수
coroutine_num | 현재 코루틴 수【코루틴을 위한 것】
coroutine_peek_num | 전체 코루틴 수
task_queue_num | 메시지 큐에 있는 task 수【Task를 위한 것】
task_queue_bytes | 메시지 큐의 메모리 사용字节수【Task를 위한 것】


## task()

비동기 작업을 `task_worker` 풀에 전달합니다. 이 함수는 비 bloquear이며, 완료되면 즉시 반환합니다. `Worker` 프로세스는 새로운 요청을 계속 처리할 수 있습니다. `Task` 기능을 사용하려면 먼저 `task_worker_num`을 설정해야 하며, `Server`의 [onTask](/server/events?id=ontask)와 [onFinish](/server/events?id=onfinish) 이벤트 콜백 함수를 설정해야 합니다.

```php
Swoole\Server->task(mixed $data, int $dstWorkerId = -1, callable $finishCallback): int
```

  * **매개변수**

    * `mixed $data`

      * 기능: 전달할 작업 데이터로, 시리얼라이즈 가능한 PHP 변수여야 합니다.
      * 기본값: 없음
      * 기타 값: 없음

    * `int $dstWorkerId`

      * 기능: 전달할 작업을 특정 [Task 프로세스](/learn?id=taskworker 프로세스)에 전달할 수 있으며, 전달할 Task 프로세스의 `ID`를 지정하면 됩니다. 범위는 `[0, $server->setting['task_worker_num']-1]`입니다.
      * 기본값: `-1`【기본적으로 `-1`은 무작위 전달을 의미하며, 내부적으로 자동으로 빈 [Task 프로세스](/learn?id=taskworker 프로세스)를 선택합니다】
      * 기타 값: `[0, $server->setting['task_worker_num']-1]`

    * `callable $finishCallback`

      * 기능: `finish` 콜백 함수로, 작업에 콜백이 설정되어 있다면 Task가 결과를 반환할 때 지정한 콜백 함수를 직접 실행하고, Server의 [onFinish](/server/events?id=onfinish) 콜백은 더 이상 실행되지 않습니다. 이 콜백은 Worker 프로세스에서만 작업을 전달할 경우에만 발동합니다.
      * 기본값: `null`
      * 기타 값: 없음

  * **반환값**

    * 성공 시, 정수 `$task_id`가 반환되며, 이는 해당 작업의 `ID`를 나타냅니다. `finish` 콜백이 설정되어 있다면 [onFinish](/server/events?id=onfinish) 콜백에서 `$task_id` 매개변수를 지닌다.
    * 실패 시, `false`가 반환되며, `$task_id`는 `0`일 수도 있으므로 반드시 `===` 연산자로 실패 여부를 확인해야 합니다.

  * **주의사항**

    * 이 기능은 느린 작업을 비동기로 실행하기 위해 사용되며, 예를 들어 채팅실 서버에서는 브로드캐스트를 수행하는 데 사용할 수 있습니다. 작업이 완료될 때, [task 프로세스](/learn?id=taskworker 프로세스)에서 `$serv->finish("finish")`를 호출하여 worker 프로세스에 해당 작업이 완료되었다는 것을 알립니다. 물론 `Swoole\Server->finish`는 선택적입니다.
    * `task`는 기본적으로 [unixSocket](/learn?id= 什么是IPC) 통신을 사용하며, 전용 메모리를 가지고 있어 IO 소모가 없습니다. 단일 프로세스의 읽기/쓰기 성능은 초당 `100만`에 달할 수 있으며, 다른 프로세스가 다른 `unixSocket`를 통해 통신함으로써 멀티코어의 최대한 활용을 가능하게 합니다.
    * 대상 [Task 프로세스](/learn?id=taskworker 프로세스)를 지정하지 않은 경우, `task` 메서드를 호출하면 [Task 프로세스](/learn?id=taskworker 프로세스)의 부하 상태를 판단하고, 내부적으로는 빈 상태인 [Task 프로세스](/learn?id=taskworker 프로세스)에만 작업을 전달합니다. 모든 [Task 프로세스](/learn?id=taskworker 프로세스)가 바쁜 상태라면, 내부적으로는 작업을 순차적으로 각 프로세스에 전달합니다. 현재 대기 중인 작업 수를 얻으려면 [server->stats](/server/methods?id=stats) 메서드를 사용할 수 있습니다.
    * 세 번째 매개변수로 직접 [onFinish](/server/events?id=onfinish) 함수를 설정할 수 있으며, 작업에 콜백이 설정되어 있다면 Task가 결과를 반환할 때 지정한 콜백 함수를 직접 실행하고, Server의 [onFinish](/server/events?id=onfinish) 콜백은 더 이상 실행되지 않습니다. 이 콜백은 Worker 프로세스에서만 작업을 전달할 경우에만 발동합니다.

    ```php
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    ```

    * `$task_id`는 `0-42억`의 정수로, 현재 프로세스 내에서 유일합니다.
    * 기본적으로 `task` 기능을 시작하지 않으며, 수동적으로 `task_worker_num`을 설정해야 합니다.
    * `TaskWorker`의 수는 [Server->set()](/server/methods?id=set) 매개변수에서 조정할 수 있으며, 예를 들어 `task_worker_num => 64`는 비동기 작업을 수락하기 위해 `64`개의 프로세스를 시작한다는 것을 나타냅니다.

  * **구성 매개변수**

    * `Server->task/taskwait/finish` 3가지 메서드는 전달된 `$data` 데이터가 `8K`를 초과할 경우 임시 파일을 사용하여 저장합니다. 임시 파일의 내용이
    [server->package_max_length](/server/setting?id=package_max_length)을 초과할 경우, 내부적으로 경고가 발생합니다. 이 경고는 데이터 전달에 영향을 미치지 않지만, 너무 큰 `Task`는 성능 문제를 가질 수 있습니다.
    
    ```shell
    WARN: task package is too big.
    ```

  * **단일 방향 작업**

    * `Master`, `Manager`, `UserProcess` 프로세스에서 전달된 작업은 단일 방향이며, `TaskWorker` 프로세스에서는 `return` 또는 `Server->finish()` 메서드를 사용하여 결과 데이터를 반환할 수 없습니다.

  * **주의**
  !> -`task` 방법은 [task 프로세스](/학습?id=taskworker 프로세스)에서 호출할 수 없습니다.  

- `task`를 사용하려면 `Server`에 [onTask](/server/이벤트?id=ontask)와 [onFinish](/server/이벤트?id=onfinish) 콜백을 설정해야 합니다. 그렇지 않으면 `Server->start`가 실패합니다.  

- `task`操作的 횟수는 [onTask](/server/이벤트?id=ontask) 처리 속도를 초과해야 합니다. 전달容量이 처리 능력을 초과하면 `task` 데이터가 캐시 영역을 차지하여 `Worker` 프로세스가 막힐 수 있습니다. `Worker` 프로세스는 새로운 요청을 수신할 수 없습니다.  
- [addProcess](/server/메서드?id=addProcess)로 추가된 사용자 프로세스에서는 `task`를 통해 일방적으로 작업을 전달할 수 있지만, 결과 데이터를 반환할 수 없습니다. `Worker/Task` 프로세스와 통신하기 위해 [sendMessage](/server/메서드?id=sendMessage) 인터페이스를 사용하세요.

  * **예시**

```php
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 4,
));

$server->on('Receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    echo "接收数据" . $data . "\n";
    $data    = trim($data);
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    $task_id = $server->task($data, 0);
    $server->send($fd, "分发任务，任务id为$task_id\n");
});

$server->on('Task', function (Swoole\Server $server, $task_id, $reactor_id, $data) {
    echo "Tasker进程接收到数据";
    echo "#{$server->worker_id}\tonTask: [PID={$server->worker_pid}]: task_id=$task_id, data_len=" . strlen($data) . "." . PHP_EOL;
    $server->finish($data);
});

$server->on('Finish', function (Swoole\Server $server, $task_id, $data) {
    echo "Task#$task_id finished, data_len=" . strlen($data) . PHP_EOL;
});

$server->on('workerStart', function ($server, $worker_id) {
    global $argv;
    if ($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]}: task_worker");
    } else {
        swoole_set_process_name("php {$argv[0]}: worker");
    }
});

$server->start();
```


## taskwait()

`taskwait`는 `task` 방법과 같은 역할을 하며, [task 프로세스](/학습?id=taskworker 프로세스) 풀에 비동기적인 작업을 전달하여 실행합니다. `task`와 달리 `taskwait`는 동기적으로 기다리는 것으로, 작업이 완료되거나 시간 초과가 되면 반환됩니다. `$result`는 작업 실행의 결과로, `$server->finish` 함수에서 발행됩니다. 이 작업이 시간 초과하는 경우, 여기서 `false`를 반환합니다.

```php
Swoole\Server->taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1): mixed
```

  * **매개변수**

    * `mixed $data`

      * 기능: 전달하는 작업 데이터로, 어떤 유형이든 가능하며, 문자열이 아닐 경우 자동으로 직렬화됩니다.
      * 기본값: 없음
      * 기타 값: 없음

    * `float $timeout`

      * 기능: 시간 초과값으로, 부동소수점으로, 단위는 초이며, 최소 지원 단위는 `1ms`입니다. 지정된 시간 내에 [Task 프로세스](/학습?id=taskworker 프로세스)에서 데이터가 반환되지 않으면, `taskwait`는 `false`를 반환하고, 이후의 작업 결과 데이터는 처리되지 않습니다.
      * 기본값: 0.5
      * 기타 값: 없음

    * `int $dstWorkerId`

      * 기능: 전달할 [Task 프로세스](/학습?id=taskworker 프로세스)를 지정할 수 있으며, 전달할 Task 프로세스의 `ID`만으로 지정하면 됩니다. 범위는 `[0, $server->setting['task_worker_num']-1]`입니다.
      * 기본값: -1【기본적으로 `-1`은 무작위 전달을 의미하며, 하단에서 자동으로 한 개의 여유로운 [Task 프로세스](/학습?id=taskworker 프로세스)를 선택합니다】
      * 기타 값: `[0, $server->setting['task_worker_num']-1]`

  * **반환값**

      * `false`를 반환하면 전달 실패를 의미합니다.
      * `onTask` 이벤트에서 `finish` 방법이나 `return`을 실행하면, `taskwait`는 `onTask`에서 전달한 결과를 반환합니다.

  * **알림**

    * **코어 모드**

      * `4.0.4` 버전부터 `taskwait` 방법은 [코어 디스패치](/coroutine?id=코어 디스패치)를 지원하며, 코어에서 `Server->taskwait()`를 호출할 경우 자동으로 [코어 디스패치](/coroutine?id=코어 디스패치)가 이루어지며, 더 이상 방해받지 않습니다.
      * [코어 디스패치](/coroutine?id=코어 디스패치) 기계를 통해 `taskwait`는 병렬 호출을 실현할 수 있습니다.
      * `onTask` 이벤트에서는 `return`이나 `Server->finish`이 하나만 존재해야 하며, 그렇지 않으면 나머지 `return`나 `Server->finish`이 실행된 후에는 task[1] has expired 경고가 발생합니다.

    * **동기 모드**

      * 동기 방해 모드에서, `taskwait`는 [UnixSocket](/학습?id=IPC란 무엇인가) 통신과 공유 메모리를 사용하여 데이터를 `Worker` 프로세스에 반환하며, 이 과정은 동기적으로 방해됩니다.

    * **특이 사항**

      * `onTask`에서 어떠한 [동기 IO](/학습?id=동기io 비동기io) 작업도 없을 경우, 하단에는 프로세스 교체 비용이 단지 `2`회뿐이며, `IO` 대기 없이 발생하지 않으므로, 이러한 경우 `taskwait`는 비방해로 간주될 수 있습니다. 실제로 `onTask`에서 단지 PHP 배열을 읽고 쓰는 작업만을 수행하고, `10만 번의 taskwait` 작업을 진행하면, 총 소모 시간은 단 1초이며, 평균每次 소모량은 10마이크로초입니다.

  * **주의**


  !> -`Swoole\Server::finish`, `taskwait`를 사용하지 마세요  
- `taskwait` 방법은 [task 프로세스](/학습?id=taskworker 프로세스)에서 호출할 수 없습니다.


## taskWaitMulti()

병렬로 여러 개의 `task` 비동기 작업을 실행하는 방법으로, 이 방법은 [코어 디스패치](/coroutine?id=코어 디스패치)를 지원하지 않으므로 다른 코어가 시작될 수 있습니다. 코어 환경에서는 다음의 `taskCo`를 사용해야 합니다.

```php
Swoole\Server->taskWaitMulti(array $tasks, float $timeout = 0.5): false|array
```

  * **매개변수**

    * `array $tasks`

      * 기능: 숫자 인덱스 배열이어야 하며, 연관 인덱스 배열은 지원되지 않습니다. 하단에서 `$tasks`를 반복하여 [Task 프로세스](/학습?id=taskworker 프로세스)에 작업을 하나씩 전달합니다.
      * 기본값: 없음
      * 기타 값: 없음

    * `float $timeout`

      * 기능: 부동소수점으로, 단위는 초입니다.
      * 기본값: 0.5초
      * 기타 값: 없음

  * **반환값**

    * 작업이 완료되거나 시간 초과하면 결과 배열을 반환합니다. 결과 배열의 각 작업 결과는 `$tasks`와 일치하는 순서로, 예를 들어 `$tasks[2]`에 해당하는 결과는 `$result[2]`입니다.
    * 특정 작업이 시간 초과하여 실행되지 않더라도 다른 작업은 영향을 받지 않으며, 반환된 결과 데이터에는 시간 초과한 작업이 포함되지 않습니다.

  * **주의**

  !> - 최대 병렬 작업 수는 `1024`을 초과할 수 없습니다.

  * **예시**

```php
$tasks[] = mt_rand(1000, 9999); // 작업 1
$tasks[] = mt_rand(1000, 9999); // 작업 2
$tasks[] = mt_rand(1000, 9999); // 작업 3
var_dump($tasks);

// 모든 Task 결과를 기다리며, 시간 초과는 10초
$results = $server->taskWaitMulti($tasks, 10.0);

if (!isset($results[0])) {
    echo "작업 1이 실행超时了\n";
}
if (isset($results[1])) {
    echo "작업 2의 실행 결과는{$results[1]}\n";
}
if (isset($results[2])) {
    echo "작업 3의 실행 결과는{$results[2]}\n";
}
```
## taskCo()

병렬적으로 `Task`을 실행하고 [코루outine 스케줄링](/coroutine?id=코루outine%EC%96%B4%EB%A1%9C)을 수행하여 코루outine 환경에서 `taskWaitMulti` 기능을 지원합니다.

```php
Swoole\Server->taskCo(array $tasks, float $timeout = 0.5): false|array
```
  
* `$tasks` 작업 리스트로 반드시 배열이어야 합니다. 내부적으로 배열을 반복하며 각 요소를 `Task` 프로세스 풀에 `task`으로 제출합니다.
* `$timeout`超时 시간으로 기본값은 `0.5`초입니다. 지정된 시간 내에 작업이 모두 완료되지 않으면 즉시 중단하고 결과를 반환합니다.
* 작업이 완료되거나 초과할 경우 결과 배열을 반환합니다. 결과 배열에서 각 작업의 결과는 `$tasks`와 대응합니다. 예를 들어 `$tasks[2]`의 결과는 `$result[2]`에 해당합니다.
* 특정 작업이 실패하거나 초과할 경우 해당 결과 배열 항목은 `false`입니다. 예를 들어 `$tasks[2]`가 실패하면 `$result[2]`의 값은 `false`입니다.

!> 최대 병렬 작업 수는 `1024`을 초과할 수 없습니다.  

  * **스케줄링 과정**

    * `$tasks` 리스트의 각 작업은 무작위로 `Task` 작업 프로세스에 제출됩니다. 제출이 완료되면 현재 코루outine을 `yield`하여 제시하고 `$timeout`초의 타이머를 설정합니다.
    * `onFinish`에서 해당 작업의 결과를 수집하여 결과 배열에 저장합니다. 모든 작업이 결과를 반환했는지 확인합니다. 그렇지 않으면 계속 기다립니다. 그렇다면 해당 코루outine을 `resume`하여 실행을 복구하고超时 타이머를 제거합니다.
    * 지정된 시간 내에 작업이 모두 완료되지 않으면 타이머가 먼저 작동하며, 내부적으로 대기 상태를 제거합니다. 완료되지 않은 작업의 결과를 `false`로 표시하고 즉시 해당 코루outine을 `resume`합니다.

  * **예시**

```php
$server = new Swoole\Http\Server("127.0.0.1", 9502, SWOOLE_BASE);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $serv, $task_id, $worker_id, $data) {
    echo "#{$serv->worker_id}\tonTask: worker_id={$worker_id}, task_id=$task_id\n";
    if ($serv->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "hello world";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('Test End, Result: ' . var_export($result, true));
});

$server->start();
```


## finish()

[Task 프로세스](/learn?id=taskworker%E3%80%82)에서 `Worker` 프로세스에 전달된 작업이 완료되었음을 알리는 데 사용됩니다. 이 함수는 `Worker` 프로세스에 결과 데이터를 전달할 수 있습니다.

```php
Swoole\Server->finish(mixed $data): bool
```

  * **매개변수**

    * `mixed $data`

      * 기능: 작업 처리의 결과 내용
      * 기본값: 없음
      * 기타 값: 없음

  * **반환값**

    * 성공 시 `true`를 반환하고 실패 시 `false`를 반환합니다.

  * **주의사항**
    * `finish` 메서드는 연속적으로 여러 번 호출될 수 있으며, `Worker` 프로세스는 여러 번 [onFinish](/server/events?id=onfinish) 이벤트를 트리거합니다.
    * [onTask](/server/events?id=ontask) 콜백 함수에서 `finish` 메서드를 호출한 후에도 `return`된 데이터는 [onFinish](/server/events?id=onfinish) 이벤트를 트리거합니다.
    * `Server->finish`는 선택적입니다. `Worker` 프로세스가 작업 실행 결과를 신경 쓰지 않는 경우 이 함수를 호출할 필요가 없습니다.
    * [onTask](/server/events?id=ontask) 콜백 함수에서 `return` 문자열을 사용하는 것은 `finish`를 호출하는 것과 동일합니다.

  * **주의사항**

  !> `Server->finish` 함수를 사용하려면 `Server`에 [onFinish](/server/events?id=onfinish) 콜백 함수를 설정해야 합니다. 이 함수는 [Task 프로세스](/learn?id=taskworker%E3%80%82)의 [onTask](/server/events?id=ontask) 콜백에서만 사용할 수 있습니다.


## heartbeat()

[heartbeat_check_interval](/server/setting?id=heartbeat_check_interval)의 수동 검출과 달리, 이 방법은 서버의 모든 연결을 적극적으로 검출하고 약속된 시간을 초과한 연결을 찾아냅니다. `if_close_connection`가 지정되면 자동으로 초과한 시간의 연결을 닫습니다. 지정되지 않으면 연결의 `fd` 배열만 반환합니다.

```php
Swoole\Server->heartbeat(bool $ifCloseConnection = true): bool|array
```

  * **매개변수**

    * `bool $ifCloseConnection`

      * 기능: 초과한 시간의 연결을 닫을지 여부
      * 기본값: true
      * 기타 값: false

  * **반환값**

    * 성공 시 닫힌 `$fd`의 연속 배열을 반환하고 실패 시 `false`를 반환합니다.

  * **예시**

```php
$closeFdArrary = $server->heartbeat();
```


## getLastError()

최근의 오류를 얻어 오류 코드를 반환합니다. 비즈니스 코드에서는 오류 코드의 유형에 따라 다른 로직을 실행할 수 있습니다.

```php
Swoole\Server->getLastError(): int
```

  * **반환값**


오류 코드 | 설명
---|---
1001 | `Server` 측에서 연결이 이미 닫혀 있습니다. 이 오류는 일반적으로 코드에서 `$server->close()`를 사용하여 연결을 닫은 후에도 `$server->send()`를 사용하여 해당 연결에 데이터를 보낸 경우 발생합니다.
1002 | `Client` 측에서 연결이 닫혀서 `Socket`이 닫혀서 상대방에게 데이터를 보낼 수 없습니다.
1003 | `close`가 실행 중이며, [onClose](/server/events?id=onclose) 콜백 함수에서는 `$server->send()`를 사용할 수 없습니다.
1004 | 연결이 닫혀 있습니다.
1005 | 연결이 존재하지 않습니다. 전달된 `$fd`가 잘못된 가능성이 있습니다.
1007 | 초과 시간의 데이터를 받았습니다. `TCP`가 연결을 닫은 후에는 일부 데이터가 [unixSocket](/learn?id=IPC%E3%80%82) 캐시 영역에 남아 있는데, 이 부분의 데이터는 버려집니다.
1008 | 보낼 수 없는 상태로 보낸 메시지가缓存되어 있습니다. 이 오류는 상대방이 데이터를 즉시 수신하지 못해 보낸 메시지 캐시 영역이 꽉 차 있음을 나타냅니다.
1202 | 보낸 데이터가 [server->buffer_output_size](/server/setting?id=buffer_output_size) 설정보다 큽니다.
9007 | dispatch_mode가 3일 때만 발생합니다. 현재 사용할 수 있는 프로세스가 없음을 나타내며, worker_num을 늘릴 수 있습니다.


## getSocket()

이 메서드를 호출하면 기본적인 `socket` 핸들을 얻을 수 있으며, 반환되는 객체는 `sockets` 리소스 핸들입니다.

```php
Swoole\Server->getSocket(): false|\Socket
```

!> 이 방법은 PHP의 `sockets` 확장을 필요로 하며, Swoole을 컴파일할 때 `--enable-sockets` 옵션을 활성화해야 합니다.

  * **侦听端口**

    * `listen` 메서드로 증가된 포트는 `Swoole\Server\Port` 객체가 제공하는 `getSocket` 메서드를 사용하여 사용할 수 있습니다.

    ```php
    $port = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $socket = $port->getSocket();
    ```

    * `socket_set_option` 함수를 사용하여 더 기본적인 몇 가지 `socket` 매개변수를 설정할 수 있습니다.

    ```php
    $socket = $server->getSocket();
    if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
        echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
    }
    ```

  * **멀티캐스트 지원**

    * `socket_set_option`를 사용하여 `MCAST_JOIN_GROUP` 매개변수를 설정하면 `Socket`을 멀티캐스트에 가입시켜 네트워크 멀티캐스트 데이터 패킷을 수신할 수 있습니다.

```php
$server = new Swoole\Server('0.0.0.0', 9905, SWOOLE_BASE, SWOOLE_SOCK_UDP);
$server->set(['worker_num' => 1]);
$socket = $server->getSocket();

$ret = socket_set_option(
    $socket,
    IPPROTO_IP,
    MCAST_JOIN_GROUP,
    array(
        'group' => '224.10.20.30', // 表示组播地址
        'interface' => 'eth0' // 表示网络接口的名称，可以为数字或字符串，如eth0、wlan0
    )
);

if ($ret === false) {
    throw new RuntimeException('Unable to join multicast group');
}

$server->on('Packet', function (Swoole\Server $server, $data, $addr) {
    $server->sendto($addr['address'], $addr['port'], "Swoole: $data");
    var_dump($addr, strlen($data));
});

$server->start();
```
## 보호()

클라이언트 연결을 보호 상태로 설정하여, 핫스테이 스레드에 의해 끊어지지 않도록 합니다.

```php
Swoole\Server->protect(int $fd, bool $is_protected = true): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 클라이언트 연결 `fd` 지정
      * 기본값: 없음
      * 기타값: 없음

    * `bool $is_protected`

      * 기능: 설정하는 상태
      * 기본값: true 【보호 상태를 나타냅니다】
      * 기타값: false 【보호되지 않음을 나타냅니다】

  * **귀속값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다


## 확인()

연결을 확인하고, [enable_delay_receive](/server/setting?id=enable_delay_receive)와 함께 사용할 수 있습니다. 클라이언트가 연결을 맺은 후에는 읽기 가능 이벤트를 감지하지 않고, 오직 [onConnect](/server/events?id=onconnect) 이벤트 콜백만을 트리거합니다. [onConnect](/server/events?id=onconnect) 콜백에서 `confirm`을 호출하여 연결을 확인하면, 이후에 서버는 읽기 가능 이벤트를 감지하고 클라이언트 연결에서 온 데이터를 수신할 수 있습니다.

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다

```php
Swoole\Server->confirm(int $fd): bool
```

  * **매개변수**

    * `int $fd`

      * 기능: 연결의 고유 식별자
      * 기본값: 없음
      * 기타값: 없음

  * **귀속값**
  
    * 성공 시 `true`를, `$fd`에 해당하는 연결이 존재하지 않거나 이미 닫혀 있거나 이미 감시 중인 경우 `false`를 반환하며, 확인에 실패합니다

  * **사용처**
  
    이 방법은 일반적으로 서버를 보호하고, 트래픽 오버플로우 공격을 받지 않도록 사용됩니다. 클라이언트 연결이 맺힐 때 [onConnect](/server/events?id=onconnect) 함수가 트리거되며,的来源`IP`를 판단하여, 서버에 데이터를 보낼 수 있는지 여부를 결정할 수 있습니다.

  * **예시**
    
```php
// Server 객체를 생성하고 127.0.0.1:9501 포트를 감시하도록 합니다
$serv = new Swoole\Server("127.0.0.1", 9501); 
$serv->set([
    'enable_delay_receive' => true,
]);

// 연결 진입 이벤트를 감시합니다
$serv->on('Connect', function ($serv, $fd) {  
    // 여기서 이 $fd를 검사하고 문제가 없으면 confirm을 합니다
    $serv->confirm($fd);
});

// 데이터 수신 이벤트를 감시합니다
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "Server: ".$data);
});

// 연결 닫힘 이벤트를 감시합니다
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// 서버를 시작합니다
$serv->start(); 
```


## getWorkerId()

현재 `Worker` 프로세스의 `id` (프로세스의 `PID`가 아닙니다)를 가져옵니다. [onWorkerStart](/server/events?id=onworkerstart) 시의 `$workerId`와 일치합니다.

```php
Swoole\Server->getWorkerId(): int|false
```

!> Swoole 버전 >= `v4.5.0RC1`에서 사용할 수 있습니다


## getWorkerPid()

특정 `Worker` 프로세스의 `PID`를 가져옵니다.

```php
Swoole\Server->getWorkerPid(int $worker_id = -1): int|false
```

  * **매개변수**

    * `int $worker_id`

      * 기능: 특정 프로세스의 pid를 가져옵니다
      * 기본값: -1 【기존 프로세스를 나타냅니다】
      * 기타값: 없음

!> Swoole 버전 >= `v4.5.0RC1`에서 사용할 수 있습니다


## getWorkerStatus()

`Worker` 프로세스의 상태를 가져옵니다.

```php
Swoole\Server->getWorkerStatus(int $worker_id = -1): int|false
```

!> Swoole 버전 >= `v4.5.0RC1`에서 사용할 수 있습니다

  * **매개변수**

    * `int $worker_id`

      * 기능: 프로세스 상태를 가져옵니다
      * 기본값: -1 【기존 프로세스를 나타냅니다】
      * 기타값: 없음

  * **귀속값**
  
    * `Worker` 프로세스 상태를 반환합니다, 프로세스 상태 값을 참고합니다
    * `Worker` 프로세스가 아닐 경우 또는 프로세스가 존재하지 않을 경우 `false`를 반환합니다

  * **프로세스 상태 값**

    상수 | 값 | 설명 | 버전 의존
    ---|---|---|---
    SWOOLE_WORKER_BUSY | 1 | 바쁨 | v4.5.0RC1
    SWOOLE_WORKER_IDLE | 2 | 여유 | v4.5.0RC1
    SWOOLE_WORKER_EXIT | 3 | [reload_async](/server/setting?id=reload_async)가 활성화되어 있을 경우, 동일한 worker_id에 두 개의 프로세스가 있을 수 있습니다. 하나는 새로운 것이고 다른 하나는 오래된 것입니다. 오래된 프로세스가 읽는 상태코드는 EXIT입니다. | v4.5.5


## getManagerPid()

현재 서비스의 `Manager` 프로세스의 `PID`를 가져옵니다.

```php
Swoole\Server->getManagerPid(): int
```

!> Swoole 버전 >= `v4.5.0RC1`에서 사용할 수 있습니다


## getMasterPid()

현재 서비스의 `Master` 프로세스의 `PID`를 가져옵니다.

```php
Swoole\Server->getMasterPid(): int
```

!> Swoole 버전 >= `v4.5.0RC1`에서 사용할 수 있습니다


## addCommand()

커스텀 명령어 `command`을 추가합니다.

```php
Swoole\Server->addCommand(string $name, int $accepted_process_types, Callable $callback): bool
```

!> -Swoole 버전 >= `v4.8.0`에서 사용할 수 있습니다         
  -해당 함수는 서비스가 시작되기 전에만 호출할 수 있으며, 동일한 명령어가 존재하는 경우에는 직접 `false`를 반환합니다

* **매개변수**

    * `string $name`

        * 기능: `command` 명령어 이름
        * 기본값: 없음
        * 기타값: 없음

    * `int $accepted_process_types`

      * 기능: 요청을 수용하는 프로세스 유형, 여러 프로세스 유형을 지원하고자 할 경우 `|`를 연결할 수 있습니다. 예를 들어 `SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_MANAGER`
      * 기본값: 없음
      * 기타값:
        * `SWOOLE_SERVER_COMMAND_MASTER` 마스터 프로세스
        * `SWOOLE_SERVER_COMMAND_MANAGER` 매니저 프로세스
        * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` 이벤트 워커 프로세스
        * `SWOOLE_SERVER_COMMAND_TASK_WORKER` 작업 워커 프로세스

    * `callable $callback`

        * 기능: 콜백 함수로, 두 개의 인자를 가집니다. 하나는 `Swoole\Server`의 인스턴이고, 다른 하나는 사용자가 정의한 변수입니다. 이 변수는 `Swoole\Server::command()`의 네 번째 매개변수로 전달되는 것입니다.
        * 기본값: 없음
        * 기타값: 없음

* **귀속값**

    * 성공 시 `true`를, 실패 시 `false`를 반환합니다

## command()

정의된 커스텀 명령어 `command`을 호출합니다.

```php
Swoole\Server->command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true): false|string|array
```

!>Swoole 버전 >= `v4.8.0`에서 사용할 수 있으며, `SWOOLE_PROCESS` 및 `SWOOLE_BASE` 모드에서, 해당 함수는 `master` 프로세스에만 사용할 수 있습니다.  


* **매개변수**

    * `string $name`

        * 기능: `command` 명령어 이름
        * 기본값: 없음
        * 기타값: 없음

    * `int $process_id`

        * 기능: 프로세스 ID
        * 기본값: 없음
        * 기타값: 없음

    * `int $process_type`

        * 기능: 프로세스 요청 유형, 다음의 다른 값 중 하나만 선택할 수 있습니다.
        * 기본값: 없음
        * 기타값:
          * `SWOOLE_SERVER_COMMAND_MASTER` 마스터 프로세스
          * `SWOOLE_SERVER_COMMAND_MANAGER` 매니저 프로세스
          * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` 이벤트 워커 프로세스
          * `SWOOLE_SERVER_COMMAND_TASK_WORKER` 작업 워커 프로세스

    * `mixed $data`

        * 기능: 요청 데이터로, 해당 데이터는 반序列화 가능해야 합니다
        * 기본값: 없음
        * 기타값: 없음

    * `bool $json_decode`

        * 기능: `json_decode`을 사용하여 해석할지 여부
        * 기본값: true
        * 기타값: false
  
  * **사용 예시**
    ```php
    <?php
    use Swoole\Http\Server;
    use Swoole\Http\Request;
    use Swoole\Http\Response;

    $server = new Server('127.0.0.1', 9501, SWOOLE_BASE);
    $server->addCommand('test_getpid', SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_EVENT_WORKER,
        function ($server, $data) {
          var_dump($data);
          return json_encode(['pid' => posix_getpid()]);
        });
    $server->set([
        'log_file' => '/dev/null',
        'worker_num' => 2,
    ]);

    $server->on('start', function (Server $serv) {
        $result = $serv->command('test_getpid', 0, SWOOLE_SERVER_COMMAND_MASTER, ['type' => 'master']);
        Assert::eq($result['pid'], $serv->getMasterPid());
        $result = $serv->command('test_getpid', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::eq($result['pid'], $serv->getWorkerPid(1));
        $result = $serv->command('test_not_found', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::false($result);

        $serv->shutdown();
    });

    $server->on('request', function (Request $request, Response $response) {
    });
    $server->start();
    ```
