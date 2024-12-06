# 설정

[Swoole\Server->set()](/server/methods?id=set) 함수는 `Server` 운영 시 다양한 매개변수를 설정하는 데 사용됩니다. 이 섹션의 모든 서브 페이지는 설정 배열의 요소입니다.

!> v4.5.5 [버전](/version/log?id=v455)부터, 기본적으로 설정된 구성 항목이 올바른지 검사를 진행하며, Swoole이 제공하지 않는 구성 항목이 설정되어 있다면 Warning가 발생합니다.

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```


### debug_mode

?> 로깅 모드를 'debug'로 설정하여 디버그 모드를 활성화합니다. 이 기능은 `--enable-debug` 옵션을编译할 때에만 활성화됩니다.

```php
$server->set([
  'debug_mode' => true
])
```


### trace_flags

?> 트레이스 로깅의 태그를 설정하여 일부 트레이스 로깅만 출력합니다. `trace_flags`는 `|` 연산자로 여러 트레이스 항목을 설정할 수 있습니다. 이 기능은 `--enable-trace-log` 옵션을编译할 때에만 활성화됩니다.

기본적으로 다음의 트레이스 항목이 지원되며, `SWOOLE_TRACE_ALL`을 사용하면 모든 항목을 트레이스합니다:

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`


### log_file

?> **Swoole 오류 로깅 파일을 지정합니다.**

?> Swoole 운영 중 발생하는 예외 정보는 이 파일에 기록되며, 기본적으로 화면에 출력됩니다.  
디바운스 모드를 활성화하면 `(daemonize => true)` standard output이 `log_file`로 리디렉션됩니다. PHP 코드에서 `echo/var_dump/print` 등 화면에 출력되는 내용은 `log_file` 파일에 기록됩니다.

  * **알림**

    * `log_file`에 기록된 로깅은 운영 시 오류 기록용이며, 장기 저장 필요가 없습니다.

    * **로깅 번호**

      ?> 로깅 정보 중 프로세스 ID 앞에는 일종의 번호가 추가되어 로깅이 생성된 스레드/프로세스 유형을 나타냅니다.

        * `#` 메인 프로세스
        * `$` 매니저 프로세스
        * `*` 워커 프로세스
        * `^` 태스크 프로세스

    * **로깅 파일 재개방**

      ?> 서버 프로그램이 운영 중 로깅 파일이 `mv`로 이동되거나 `unlink`로 삭제된 후에는 로깅 정보가 정상적으로 기록될 수 없으며, 이때 `Server`에 `SIGRTMIN` 신호를 보내 로깅 파일을 재개방할 수 있습니다.

      * Linux 플랫폼에서만 지원됩니다.
      * [UserProcess](/server/methods?id=addProcess) 프로세스는 지원되지 않습니다.

  * **주의**

    !> `log_file`는 자동으로 파일을 나누지 않으므로 이 파일을 정기적으로 청소해야 합니다. `log_file`의 출력을 관찰하면 서버의 각종 예외 정보와 경고를 얻을 수 있습니다.


### log_level

?> **Server 오류 로깅 출력 레벨을 설정합니다. 범위는 `0-6`입니다. `log_level`보다 낮은 로깅 정보는 출력되지 않습니다.**【기본값: `SWOOLE_LOG_INFO`】

레벨 상수 참조는 [로깅 레벨](/consts?id=日志等级)을 참고하세요.

  * **주의**

    !> `SWOOLE_LOG_DEBUG`와 `SWOOLE_LOG_TRACE`는 `--enable-debug-log](/environment?id=debug参数)`와 `--enable-trace-log](/environment?id=debug参数)`编译 옵션을 적용한 버전에서만 사용할 수 있습니다;  
    디바운스 모드를 활성화하면, 기본적으로 프로그램의 모든 화면 출력 내용이 [log_file](/server/setting?id=log_file)에 기록되며, 이 부분은 `log_level`에 의해 제어되지 않습니다.


### log_date_format

?> **Server 로깅 시간 형식을 설정합니다.**, 형식은 [strftime](https://www.php.net/manual/zh/function.strftime.php)의 `format`을 참고하세요.

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```


### log_date_with_microseconds

?> **Server 로깅의 정확도를 설정합니다. 마이크로초 포함 여부를 설정합니다.**【기본값: `false`】


### log_rotation

?> **Server 로깅 나누기를 설정합니다.**【기본값: `SWOOLE_LOG_ROTATION_SINGLE`】

| 상수                             | 설명   | 버전 정보 |
| -------------------------------- | ------ | -------- |
| SWOOLE_LOG_ROTATION_SINGLE       | 미적용 | -        |
| SWOOLE_LOG_ROTATION_MONTHLY      | 월간   | v4.5.8   |
| SWOOLE_LOG_ROTATION_DAILY        | 일일   | v4.5.2   |
| SWOOLE_LOG_ROTATION_HOURLY       | 시간당 | v4.5.8   |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE | 분당   | v4.5.8   |


### display_errors

?> Swoole 오류 정보를 출력 / 출력하지 않도록 설정합니다.

```php
$server->set([
  'display_errors' => true
])
```


### dns_server

?> DNS 조회에 사용하는 IP 주소를 설정합니다.


### socket_dns_timeout

?> 도메인 해석 최대 시간을 설정합니다. 서버 측에서 코루outine 클라이언트를 활성화할 경우, 이 매개변수는 클라이언트의 도메인 해석 최대 시간을 제어할 수 있으며, 단위는 초입니다.


### socket_connect_timeout

?> 클라이언트 연결 최대 시간을 설정합니다. 서버 측에서 코루outine 클라이언트를 활성화할 경우, 이 매개변수는 클라이언트의 연결 최대 시간을 제어할 수 있으며, 단위는 초입니다.


### socket_write_timeout / socket_send_timeout

?> 클라이언트 쓰기 최대 시간을 설정합니다. 서버 측에서 코루outine 클라이언트를 활성화할 경우, 이 매개변수는 클라이언트의 쓰기 최대 시간을 제어할 수 있으며, 단위는 초입니다.   
이 설정은 또한 `코루outine화` 이후의 `shell_exec` 또는 [Swoole\Coroutine\System::exec()](/coroutine/system?id=exec)의 실행 최대 시간을 제어하는 데 사용됩니다.   


### socket_read_timeout / socket_recv_timeout

?> 클라이언트 읽기 최대 시간을 설정합니다. 서버 측에서 코루outine 클라이언트를 활성화할 경우, 이 매개변수는 클라이언트의 읽기 최대 시간을 제어할 수 있으며, 단위는 초입니다.


### max_coroutine / max_coro_num :id=max_coroutine

?> **현재 작업 프로세스의 최대 코루outine 수를 설정합니다.**【기본값: `100000`，Swoole 버전이 `v4.4.0-beta` 이전의 경우 기본값은 `3000`입니다】

?> `max_coroutine`를 초과하면 기본적으로 새로운 코루outine를 만들 수 없으며, 서버 측 Swoole은 `exceed max number of coroutine` 오류를 던지고, `TCP Server`는 연결을 직접 종료하며, `Http Server`는 Http의 503 상태코드를 반환합니다.

?> `Server` 프로그램에서 실제로 최대 만들 수 있는 코루outine 수는 `worker_num * max_coroutine`이며, task 프로세스와 UserProcess 프로세스의 코루outine 수는 별도로 계산됩니다.

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```


### enable_deadlock_check

?> 코루outine 死锁 검사를 활성화합니다.

```php
$server->set([
  'enable_deadlock_check' => true
]);
```


### hook_flags

?> **'일괄 코루outine화' Hook의 함수 범위를 설정합니다.**【기본값: hook 하지 않음】

!> Swoole 버전이 `v4.5+` 또는 [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 이상일 경우 사용할 수 있으며, 자세한 내용은 [일괄 코루outine화](/runtime)를 참고하세요.

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
기본적으로 다음의 코루outine화 항목이 지원되며, `SWOOLE_HOOK_ALL`을 사용하면 모든 항목을 코루outine화합니다:

* `SWOOLE_HOOK_TCP`
* `SWOOLE_HOOK_UNIX`
* `SWOOLE_HOOK_UDP`
* `SWOOLE_HOOK_UDG`
* `SWOOLE_HOOK_SSL`
* `SWOOLE_HOOK_TLS`
* `SWOOLE_HOOK_SLEEP`
* `SWOOLE_HOOK_FILE`
* `SWOOLE_HOOK_STREAM_FUNCTION`
* `SWOOLE_HOOK_BLOCKING_FUNCTION`
* `SWOOLE_HOOK_PROC`
* `SWOOLE_HOOK_CURL`
* `SWOOLE_HOOK_NATIVE_CURL`
* `SWOOLE_HOOK_SOCKETS`
* `SWOOLE_HOOK_STDIO`
* `SWOOLE_HOOK_PDO_PGSQL`
* `SWOOLE_HOOK_PDO_ODBC`
* `SWOOLE_HOOK_PDO_ORACLE`
* `SWOOLE_HOOK_PDO_SQLITE`
* `SWOOLE_HOOK_ALL`
### Enable preemptive scheduler

?> 코루outine의 실행 시간이 너무 길어 다른 코루outine이 굶어가는 것을 방지하기 위해 코루outine 선점형 스케줄러를 설정합니다. 코루outine의 최대 실행 시간은 `10ms`입니다.

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```


### c_stack_size / stack_size

?> 단일 코루outine의 초기 C 스택 메모리 크기를 설정합니다. 기본값은 `2M`입니다.


### aio_core_worker_num

?> `AIO`의 최소 작업 스레드 수를 설정합니다. 기본값은 `cpu` 코어 수입니다.


### aio_worker_num 

?> `AIO`의 최대 작업 스레드 수를 설정합니다. 기본값은 `cpu` 코어 수 * 8입니다.


### aio_max_wait_time

?> 작업 스레드가 작업을 기다리는 최대 시간을 설정합니다. 단위는 초입니다.


### aio_max_idle_time

?> 작업 스레드가 최대적으로 빈 시간을 가질 수 있는 시간을 설정합니다. 단위는 초입니다.


### reactor_num

?> **시작하는 [Reactor](/learn?id=reactor线程) 스레드 수를 설정합니다.**【기본값: `CPU` 코어 수】

?> 이 매개변수를 통해 주 프로세스 내 이벤트 처리 스레드의 수를 조정하여 멀티코어를 충분히 활용할 수 있습니다. 기본적으로 `CPU` 코어 수와 동일한 수를 활성화합니다.  
`Reactor` 스레드는 멀티코어를 활용할 수 있으며, 예를 들어 기계에 `128`개의 코어가 있을 경우, 하단에는 `128`개의 스레드가 시작됩니다.  
각 스레드는 [EventLoop](/learn?id=什么是eventloop)를 유지합니다. 스레드 사이는 무제한이며, 지시 사항은 `128`개의 코어 `CPU`가 병렬로 실행할 수 있습니다.  
운영체 스케줄링이 어느 정도의 성능 손실을 초래하기 때문에, CPU의 각 코어를 최대한 활용하기 위해 CPU 코어 수 * 2로 설정할 수 있습니다.

  * **알림**

    * `reactor_num`은 `CPU` 코어 수의 `1-4`배로 설정하는 것이 좋습니다.
    * `reactor_num`은 최대 [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 4을 초과해서는 안 됩니다.

  * **주의**


  !> -`reactor_num`은 `worker_num`보다 작거나 같아야 합니다.  

-설정한 `reactor_num`이 `worker_num`보다 클 경우, 자동으로 조정되어 `reactor_num`이 `worker_num`과 동일해집니다.  
- `8`개 코어 이상인 기계에서 `reactor_num`은 기본적으로 `8`로 설정됩니다.
	

### worker_num

?> **시작하는 `Worker` 프로세스 수를 설정합니다.**【기본값: `CPU` 코어 수】

?> `1`개의 요청이 `100ms` 소요되는 경우, `1000QPS`의 처리 능력을 제공하려면 `100`개 프로세스 또는 더 많이 설정해야 합니다.  
그러나 더 많은 프로세스를 실행하면 소모되는 메모리가 크게 증가하고, 프로세스 간의 교체 비용도 커집니다. 따라서 적당히 설정해야 합니다. 너무 크게 설정하지 마십시오.

  * **알림**

    * 비즈니스 코드가 전부 [비동기 IO](/learn?id=同步io异步io)인 경우, 여기서 `CPU` 코어 수의 `1-4`배로 설정하는 것이 가장 합리적입니다.
    * 비즈니스 코드가 [동기 IO](/learn?id=同步io异步io)인 경우, 요청 응답 시간과 시스템 부하에 따라 조정해야 합니다. 예를 들어: `100-500`
    * 기본적으로 [swoole_cpu_num()](/functions?id=swoole_cpu_num)로 설정되며, 최대 [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000을 초과해서는 안 됩니다.
    * 가정: 각 프로세스가 `40M` 메모리를 차지한다면, `100`개 프로세스는 `4G` 메모리를 차지해야 합니다.


### max_request

?> **`worker` 프로세스의 최대 작업 수를 설정합니다.**【기본값: `0` 즉 프로세스를 종료하지 않습니다】

?> 한 `worker` 프로세스가 이 수치를 초과하는 작업을 처리한 후에는 자동으로 종료하며, 프로세스가 종료되면 모든 메모리와 자원을 해제합니다.

!> 이 매개변수의 주요 기능은 프로그램 인코딩이 비규범으로 인해 발생하는 PHP 프로세스 메모리 누수 문제를 해결하는 것입니다. PHP 응용 프로그램은 느린 메모리 누수가 있지만, 구체적인 원인을 찾거나 해결할 수 없을 수 있으며, `max_request`를 설정하여 임시로 해결할 수 있습니다. 메모리 누수가 발생하는 코드를 찾고 수정해야 하며, 이 방안을 통해 해결할 수 없습니다. Swoole Tracker를 사용하여 누수되는 코드를 발견할 수 있습니다.

  * **알림**

    * max_request에 도달하면 즉시 프로세스를 종료하지 않습니다. [max_wait_time](/server/setting?id=max_wait_time)를 참고하세요.
    * [SWOOLE_BASE](/learn?id=swoole_base)에서 max_request에 도달하면 프로세스를 재시작하면 고객 연결이 끊어집니다.

  !> `worker` 프로세스 내에서 치명적인 오류가 발생하거나 인위적으로 `exit`을 실행하면 프로세스가 자동으로 종료됩니다. `master` 프로세스는 새로운 `worker` 프로세스를 시작하여 요청을 계속 처리합니다.


### max_conn / max_connection

?> **서버 프로그램이 허용하는 최대 연결 수입니다.**【기본값: `ulimit -n`】

?> 예를 들어 `max_connection => 10000`, 이 매개변수는 `Server`가 유지할 수 있는 최대 `TCP` 연결 수를 설정합니다. 이 수치를 초과한 새로운 연결은 거절됩니다.

  * **알림**

    * **기본 설정**

      * 애플리케이션 계층에서 `max_connection`이 설정되지 않으면, 하단은 `ulimit -n`의 값을 기본 설정으로 사용합니다.
      * `4.2.9` 또는 그 이상 버전에서, 하단이 `ulimit -n`이 `100000`을 초과하는 것을 감지하면 기본적으로 `100000`로 설정합니다. 이유는 일부 시스템이 `ulimit -n`을 `100만`으로 설정하여 대량의 메모리를 할당해야 하므로 시작에 실패합니다.

    * **최대 상한**

      * `max_connection`을 `1M`을 초과해서는 안 됩니다.

    * **최소 설정**    
     
      * 이 옵션을 너무 작게 설정하면 하단이 오류를 발생시킬 것이며, `ulimit -n`의 값으로 설정됩니다.
      * 최소값은 `(worker_num + task_worker_num) * 2 + 32`입니다.

    ```shell
    serv->max_connection is too small.
    ```

    * **메모리 사용량**

      * `max_connection` 매개변수를 너무 크게 조정하지 마십시오. 기계의 메모리 실제 상황에 따라 설정합니다. `Swoole`는 이 값에 따라 한 번에 큰 메모리를 할당하여 `Connection` 정보를 저장합니다. 하나의 `TCP` 연결의 `Connection` 정보는 `224`바이트를 차지합니다.

  * **주의**

  !> `max_connection`은 운영체의 `ulimit -n` 값을 초과해서는 안 되며, 그렇지 않으면 경고 메시지가 나고 `ulimit -n`의 값으로 재설정됩니다.

  ```shell
  WARN swServer_start_check: serv->max_conn is exceed the maximum value[100000].

  WARNING set_max_connection: max_connection is exceed the maximum value, it's reset to 10240
  ```


### task_worker_num

?> **[Task 프로세스](/learn?id=taskworker进程)의 수를 설정합니다.**

?> 이 매개변수를 설정하면 `task` 기능이 활성화됩니다. 따라서 `Server`는 반드시 [onTask](/server/events?id=ontask)、[onFinish](/server/events?id=onfinish) 2개의 이벤트 콜백 함수를 등록해야 합니다. 등록하지 않으면 서버 프로그램이 시작되지 않습니다.

  * **알림**

    *  [Task 프로세스](/learn?id=taskworker进程)는 동기적이고 비동기적입니다.

    * 최대값은 [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000을 초과해서는 안 됩니다.    
    
    * **계산 방법**
      * 단일 `task`의 처리 시간, 예를 들어 `100ms`라면, 한 프로세스는 1초에 `1/0.1=10`개의 task를 처리할 수 있습니다.
      * `task` 전달 속도, 예를 들어 초당 `2000`개의 `task`가 생성됩니다.
      * `2000/10=200`, 따라서 `task_worker_num => 200`을 설정하고 `200`개의 Task 프로세스를 활성화해야 합니다.

  * **주의**

    !> - [Task 프로세스](/learn?id=taskworker进程) 내에서는 `Swoole\Server->task` 메서드를 사용할 수 없습니다.
### task_ipc_mode

?> **Task 프로세스와 `Worker` 프로세스 간의 통신 방식을 설정합니다.** 【기본값: `1`】 
 
?> 먼저 [Swoole의 IPC 통신](/learn?id= 什么是IPC)에 대해 이해해 주세요.


모드 | 역할
---|---
1 | `Unix 소켓` 통신 사용 【기본 모드】
2 | `sysvmsg` 메시지 대기열 통신
3 | `sysvmsg` 메시지 대기열 통신을 사용하고 경쟁 모드로 설정

  * **알림**

    * **모드 `1`**
      * 모드 `1` 사용 시, 대상 `Task 프로세스`를 지정할 수 있는 지향 전송이 지원됩니다. [task](/server/methods?id=task)와 [taskwait](/server/methods?id=taskwait) 메서드에서 `dst_worker_id`를 사용하여 대상 `Task 프로세스를` 지정할 수 있습니다.
      * `dst_worker_id`를 `-1`로 설정하면, 기본적으로 각 [Task 프로세스](/learn?id=taskworker进程)의 상태를 검사하고 현재 여유 상태인 프로세스에 작업을 전달합니다.

    * **모드 `2`, `3`**
      * 메시지 대기열 모드는 운영체제에서 제공하는 메모리 대기열을 사용하여 데이터를 저장합니다. `message_queue_key` 메시지 대기열 `Key`가 지정되지 않으면, 프라이브 대기열이 사용되며, `Server` 프로그램이 종료된 후 메시지 대기열은 삭제됩니다.
      * 메시지 대기열 `Key`가 지정되면 `Server` 프로그램이 종료된 후에도 메시지 대기열에 저장된 데이터는 삭제되지 않으므로, 프로세스가 재 시작된 후에도 데이터를 사용할 수 있습니다.
      * 메시지 대기열 데이터를 수동으로 삭제할 수 있는 `ipcrm -q` 메시지 대기열 `ID`를 사용할 수 있습니다.
      * `모드 2`와 `모드 3`의 차이점은, `모드 2`는 지향 전송을 지원하고 `$serv->task($data, $task_worker_id)`를 사용하여 전달할 수 있는 [task 프로세스](/learn?id=taskworker进程)를 지정할 수 있습니다. `모드 3`는 완전 경쟁 모드로, [task 프로세스](/learn?id=taskworker进程)가 대기열을 경쟁하여 전달할 수 없으며, 지향 전송을 사용할 수 없고, `task/taskwait`는 대상 프로세스 `ID`를 지정할 수 없습니다. 지정된 `$task_worker_id`도 `모드 3`에서는 무효입니다.

  * **주의**

    !> -`모드 3`는 [sendMessage](/server/methods?id=sendMessage) 메서드에 영향을 미치며, [sendMessage](/server/methods?id=sendMessage)가 보낸 메시지가 어느 [task 프로세스](/learn?id=taskworker进程)에 랜덤하게 전달될 수 있습니다.  
    -메시지 대기열 통신을 사용하면, `Task 프로세스`의 처리 능력이 전달 속도보다 낮을 경우 `Worker` 프로세스의 막힘이 발생할 수 있습니다.  
    -메시지 대기열 통신을 사용한 후에는 task 프로세스가 코루틴(协程)을 지원하지 못합니다.( [task_enable_coroutine](/server/setting?id=task_enable_coroutine)를开启)


### task_max_request

?> **Task 프로세스의 최대 작업 수를 설정합니다.** 【기본값: `0`】

Task 프로세스의 최대 작업 수를 설정합니다. 이 수를 초과한 작업을 처리한 Task 프로세스는 자동으로 종료됩니다. 이 매개변수는 PHP 프로세스의 메모리가 넘어가는 것을 방지하기 위해 설정됩니다. 프로세스가 자동으로 종료되지 않도록 원한다면 `0`로 설정하세요.


### task_tmpdir

?> **Task의 임시 데이터 디렉터리를 설정합니다.** 【기본값: Linux `/tmp` 디렉터리】

?> Server에서 전달된 데이터가 `8180` 바이트를 초과할 경우, 임시 파일을 사용하여 데이터를 저장합니다. 여기서 `task_tmpdir`은 임시 파일을 저장할 위치를 설정하는 데 사용됩니다.

  * **알림**

    * 기본적으로底层은 `/tmp` 디렉터리를 사용하여 `task` 데이터를 저장합니다. 만약 당신의 Linux 커널 버전이 너무 낮아서 `/tmp` 디렉터리가 메모리 파일 시스템이 아니라면, `/dev/shm/`로 설정할 수 있습니다.
    * `task_tmpdir` 디렉터리가 존재하지 않을 경우,底层은 자동으로 생성하려고 합니다.

  * **주의**

    !> -생성에 실패하면, `Server->start`이 실패합니다.


### task_enable_coroutine

?> **Task 코루틴 지원을开启합니다.** 【기본값: `false`】, v4.2.12부터 지원됩니다.

?> 开启 시 자동으로 [onTask](/server/events?id=ontask) 콜백에서 코루틴과 [코루틴 컨테이너](/coroutine/scheduler)를 생성하고, `PHP` 코드는 코루틴 `API`를 직접 사용할 수 있습니다.

  * **예시**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    //어떤 Worker 프로세스에서 왔는지
    $task->worker_id;
    //작업의 번호
    $task->id;
    //작업의 유형, taskwait, task, taskCo, taskWaitMulti는 다른 플래그를 사용할 수 있습니다
    $task->flags;
    //작업의 데이터
    $task->data;
    //보내기 시간, v4.6.0에서 추가됨
    $task->dispatch_time;
    //코루틴 API
    co::sleep(0.2);
    //작업 완료, 종료하고 데이터를 반환합니다
    $task->finish([123, 'hello']);
});
```

  * **주의**

    !> -`task_enable_coroutine`는 [enable_coroutine](/server/setting?id=enable_coroutine)가 `true`일 때만 사용할 수 있습니다.  
    -`task_enable_coroutine`를开启하면, `Task` 작업 프로세스는 코루틴을 지원합니다.  
    -`task_enable_coroutine`를开启하지 않으면, 동기적이고 비 bloquear만 가능합니다.


### task_use_object/task_object :id=task_use_object

?> **객체 지향 스타일의 Task 콜백 형식을 사용합니다.** 【기본값: `false`】

?> `true`로 설정하면, [onTask](/server/events?id=ontask) 콜백이 객체 모드로 변경됩니다.

  * **예시**

```php
<?php

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 3,
    'task_use_object' => true,
//    'task_object' => true, // v4.6.0에서 추가된 별명
]);
$server->on('receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd' => $fd,]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    //여기서 $task는 Swoole\Server\Task 객체입니다
    $server->send($task->data['fd'], json_encode($server->stats()));
});
$server->start();
```


### dispatch_mode

?> **데이터 패킷 분배 전략을 설정합니다.** 【기본값: `2`】


모드 값 | 모드 | 역할
---|---|---
1 | 순환 모드 | 수신 시 모든 `Worker` 프로세스에 순환하여 분배
2 | 고정 모드 | 연결의 파일 디스크립터에 따라 `Worker`를 분배합니다. 이는 동일한 연결에서 온 데이터가 동일한 `Worker`에 의해 처리될 수 있도록 보장합니다
3 | 우선 모드 | 주 프로세스가 `Worker`의 바쁨 상태를 기준으로 선택하여 전달하며, 비활성 상태의 `Worker`에만 전달됩니다
4 | IP 분배 | 고객의 `IP`에 따라 모듈 해시를 하여 고정된 `Worker` 프로세스에 분배합니다.<br>같은 원천 IP의 연결 데이터는 항상 동일한 `Worker` 프로세스에 분배됩니다. 계산법은 `inet_addr_mod(ClientIP, worker_num)`입니다
5 | UID 분배 | 사용자 코드에서 [Server->bind()](/server/methods?id=bind)를 호출하여 연결을 `1`개의 `uid`에 묶어야 합니다. 그런 다음底层은 `UID`의 값에 따라 다른 `Worker` 프로세스에 분배합니다.<br>계산법은 `UID % worker_num`이며, 문자열을 `UID`로 사용하려면 `crc32(UID_STRING)`을 사용할 수 있습니다
7 | 스트림 모드 | 비활성화된 `Worker`가 연결을 `accept`하고 [Reactor](/learn?id=reactor线程)의 새로운 요청을 수락합니다

  * **알림**

    * **사용 권장 사항**
    
      * 무 상태 `Server`는 `1` 또는 `3`을 사용할 수 있으며, 동기적이고 비 bloquear `Server`는 `3`을, 비동기적이고 비 bloquear `Server`는 `1`을 사용할 수 있습니다.
      * 상태가 있는 경우에는 `2`, `4`, `5`를 사용할 수 있습니다.
      
    * **UDP 프로토콜**

      * `dispatch_mode=2/4/5`일 때 고정 분배이며,底层은 고객의 `IP`를 기반으로 다른 `Worker` 프로세스에 모듈 해시합니다.
      * `dispatch_mode=1/3`일 때는 무작위로 다른 `Worker` 프로세스에 분배됩니다.
      * `inet_addr_mod` 함수

```
    function inet_addr_mod($ip, $worker_num) {
        $ip_parts = explode('.', $ip);
        if (count($ip_parts) != 4) {
            return false;
        }
        $ip_parts = array_reverse($ip_parts);
    
        $ip_long = 0;
        foreach ($ip_parts as $part) {
            $ip_long <<= 8;
            $ip_long |= (int) $part;
        }
    
        return $ip_long % $worker_num;
    }
```
  * **기본 모드**
    * `dispatch_mode`가 [SWOOLE_BASE](/learn?id=swoole_base) 모드에서 설정될 경우 무용지물입니다. 왜냐하면 `BASE`에서는 작업을 전달하지 않기 때문입니다. 고객이 보낸 데이터를 받은 후에는 즉시 현재 스레드/프로세스에서 [onReceive](/server/events?id=onreceive) 콜백을 호출하여 `Worker` 프로세스에 전달할 필요가 없습니다.

  * **주의**

    !> -`dispatch_mode=1/3`일 때,底层은 `onConnect/onClose` 이벤트를 가로챘습니다. 이는 이 두 가지 모드에서는 `onConnect/onClose/onReceive`의 순서를 보장할 수 없기 때문입니다;  
    -요청에 응답하지 않는 서버 프로그램은 `1` 또는 `3` 모드를 사용할 수 없습니다. 예를 들어: http 서비스는 응답적이므로 `1` 또는 `3`를 사용할 수 있지만, TCP 장기 연결 상태인 경우에는 `1` 또는 `3`를 사용할 수 없습니다.
### 배치_기능

?> `배치` 함수를 설정합니다. `Swoole`의 기본에는 `6`가지의 [배치_모드](/server/setting?id=배치_모드)가 내장되어 있지만, 여전히 요구 사항을 충족시키지 못하는 경우가 있습니다. `C++` 함수나 `PHP` 함수를 작성하여 `배치` 논리를 구현할 수 있습니다.

  * **사용 방법**

```php
$server->set(array(
  '배치_기능' => '내_배치_함수',
));
```

  * **알림**

    * `배치_기능`을 설정하면 기본적으로 `배치_모드` 구성이 무시됩니다.
    * `배치_기능`에 해당하는 함수가 존재하지 않을 경우, 기본적으로 치명적 오류가 발생합니다.
    * 8K 이상의 패킷을 `배치`하려면, `배치_기능`에서 `0-8180` 바이트만을 가져올 수 있습니다.

  * **PHP 함수 작성**

    ?> `ZendVM`은 멀티스레딩 환경을 지원하지 않기 때문에, 여러 개의 [리액터](/learn?id=리액터스레드) 스레드를 설정해 놓았음에도 불구하고 동시에 하나의 `배치_기능`만 실행할 수 있습니다. 따라서 기본적으로 이 PHP 함수를 실행할 때 잠금 작업을 진행하며, 잠금 경쟁 문제가 발생할 수 있습니다. `배치_기능`에서 어떠한 블록링 작업도 수행하지 말아야 하며, 그렇지 않으면 `리액터` 스레드 그룹이 작동을 멈출 수 있습니다.

    ```php
    $server->set(array(
        '배치_기능' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd`는 고객 연결의 고유 식별자로, `Server::getClientInfo`를 사용하여 연결 정보를 가져올 수 있습니다.
    * `$type`은 데이터 유형으로, `0`은 고객에서 온 데이터 전송을 나타내고, `4`는 고객 연결이 수립되었음을 나타내며, `3`은 고객 연결이 닫혔음을 나타냅니다.
    * `$data`는 데이터 내용으로, 주의해야 할 점은: `HTTP`, `EOF`, `Length` 등의 프로토콜 처리 매개변수가 활성화되어 있다면, 기본적으로 패킷을 합성합니다. 그러나 `배치_기능` 함수에서는 데이터 패킷의 첫 8K만 전달할 수 있으며, 전체 패킷 내용을 얻을 수 없습니다.
    * 반드시 `0 - (server->worker_num - 1)`의 숫자를 반환해야 하며, 이는 데이터 패킷을 전달할 대상 작업 프로세스 `ID`를 나타냅니다.
    * `0` 미만 또는 `server->worker_num` 이상은 예외 대상 `ID`로, `배치`된 데이터는 버려집니다.

  * **C++ 함수 작성**

    **기타 PHP 확장에서, swoole_add_function을 사용하여 Swoole 엔진에 길이 함수를 등록합니다.**

    ?> C++ 함수 호출 시 기본적으로 잠금이 되지 않으며, 호출자가 스레드 안전성을自行 보장해야 합니다.

    ```c++
    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data);

    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data)
    {
        printf("cpp, type=%d, size=%d\n", data->info.type, data->info.len);
        return data->info.len % serv->worker_num;
    }

    int register_dispatch_function(swModule *module)
    {
        swoole_add_function("my_dispatch_function", (void *) dispatch_function);
    }
    ```

    * `배치` 함수는 전달할 대상 `worker` 프로세스 `id`를 반드시 반환해야 합니다.
    * 반환된 `worker_id`는 `server->worker_num`을 초과해서는 안 되며, 그렇지 않으면 기본적으로 segfault가 발생합니다.
    * 음수 `（return -1）`를 반환하면 해당 데이터 패킷을 버립니다.
    * `data`는 이벤트의 유형과 길이를 읽을 수 있습니다.
    * `conn`은 연결 정보로, `UDP` 패킷의 경우 `conn`은 `NULL`입니다.

  * **주의**

    !> -`배치_기능`은 [SWOOLE_PROCESS](/learn?id=swoole_process) 모드에서만 유효하며, [UDP/TCP/UnixSocket](/server/methods?id=__construct) 유형의 서버는 모두 유효합니다.  
    -반환된 `worker_id`는 `server->worker_num`을 초과해서는 안 되며, 그렇지 않으면 기본적으로 segfault가 발생합니다.


### 메시지_대기키

?> **메시지 대기열의 `KEY`를 설정합니다.**【기본값: `ftok($php_script_file, 1)`】

?> [task_ipc_mode](/server/setting?id=task_ipc_mode) = 2/3일 때만 사용합니다. 설정된 `Key`은 `Task` 작업 대기열의 `KEY`로만 사용하며, [Swoole의 IPC 통신](/learn?id=IPC에 대해)을 참고하세요.

?> `task` 대기열은 `server`가 종료된 후에도 파괴되지 않으며, 프로그램을 다시 시작한 후에도 [task 프로세스](/learn?id=taskworker 프로세스)는 여전히 대기열에 있는 작업을 처리합니다. 프로그램을 다시 시작한 후에 오래된 `Task` 작업을 실행하고 싶지 않은 경우, 이 메시지 대기열을 수동으로 삭제할 수 있습니다.

```shell
ipcs -q 
ipcrm -Q [msgkey]
```


### 데몬화

?> **데몬화**【기본값: `false`】

?> `daemonize => true`를 설정하면, 프로그램은 백그라운드에서 데몬으로 실행됩니다. 장기간 실행되는 서버 측 프로그램은 이 옵션을 활성화해야 합니다.  
데몬화를 활성화하지 않으면, ssh 터미널이 종료되면 프로그램이 실행을 중단합니다.

  * **알림**

    * 데몬화가 활성화되면, 표준 입력과 출력이 `log_file`로 리디렉션됩니다.
    * `log_file`가 설정되지 않은 경우, `/dev/null`로 리디렉션되며, 모든 화면 출력 정보가 버려집니다.
    * 데몬화가 활성화되면, `CWD`(현재 디렉터리) 환경 변수의 값이 변경되며, 상대 경로의 파일读写가 실패합니다. `PHP` 프로그램에서는 절대 경로를 사용해야 합니다.

    * **systemd**

      * `systemd`나 `supervisord`를 사용하여 `Swoole` 서비스를 관리할 때, `daemonize => true`를 설정하지 마십시오. 주된 이유는 `systemd`의 패턴이 `init`과 다르기 때문입니다. `init` 프로세스의 `PID`는 `1`이며, 프로그램이 `daemonize`를 사용하면 터미널에서 벗어나며, 결국 `init` 프로세스에 의해 관리되게 되어 `init`과 부모-자식 프로세스 관계가 됩니다.
      * 그러나 `systemd`는 별도의 백그라운드 프로세스를 시작하여 다른 서비스 프로세스를 `fork`하여 관리하기 때문에, `daemonize`가 필요 없으며, 오히려 `daemonize => true`를 사용하면 `Swoole` 프로그램이 해당 관리 프로세스와 부모-자식 프로세스 관계를 잃게 됩니다.


### 백업

?> **수용 대기열의 길이를 설정합니다.**

?> 예를 들어 `백업 => 128`이면, 이 매개변수는 동시에 `accept`을 기다리는 최대 연결 수를 결정합니다.

  * **TCP의 백업에 대한 내용**

    ?> `TCP`는 세 번의 핸드셋이 있는 과정이 있으며, 고객이 `syn=>서비스자` `syn+ack=>고객` `ack`를 보냅니다. 서비스자가 고객의 `ack`를 받은 후에는 연결을 `accept queue`라는 대기열에 넣습니다(주의1),  
    대기열의 크기는 `백업` 매개변수와 `somaxconn`의 최소값에 의해 결정됩니다. 최종적인 `accept queue` 대기열 크기를 확인하려면 `ss -lt` 명령어를 사용할 수 있습니다. `Swoole`의 메인 프로세스가 `accept`을 호출합니다(주의2)  
    `accept queue`에서 연결을 가져옵니다. `accept queue`가 가득 차게 되었을 때 연결은 성공할 수도 있습니다(주의4),  
    실패할 수도 있으며, 실패하면 고객의 행동은 연결이 재설정되는 것입니다(주의3)  
    또는 연결이 시간을 초과하여 종료되며, 서비스자는 실패 기록을 기록합니다. 로그를 확인하려면 `netstat -s|grep 'times the listen queue of a socket overflowed'` 명령어를 사용할 수 있습니다. 이러한 현상이 나타난다면 해당 값을 늘려야 합니다. 다행히도 `Swoole`의 SWOOLE_PROCESS 모드와 PHP-FPM/Apache 등의 소프트웨어는 다르며, 연결 대기 문제를 해결하기 위해 `백업`에 의존하지 않기 때문에 이러한 현상에 거의 만나지 않습니다.

    * 주의1: `linux2.2` 이후 핸드셋 과정은 `syn queue`와 `accept queue` 두 개의 대기열로 나뉘며, `syn queue`의 길이는 `tcp_max_syn_backlog`에 의해 결정됩니다.
    * 주의2: 고버전 커널은 `accept4`를 호출하여 한 번의 `set no block` 시스템 호출을 절약합니다.
    * 주의3: 고객은 `syn+ack` 패킷을 받은 후에 연결이 성공했다고 생각하지만, 실제로 서비스자는 여전히 반쯤 연결된 상태이며, `rst` 패킷을 고객에게 보낼 수도 있습니다. 고객의 행동은 `Connection reset by peer`입니다.
    * 주의4: 성공은 TCP의 재전송 메커니즘에 의해 결정되며, 관련된 구성은 `tcp_synack_retries`와 `tcp_abort_on_overflow`입니다.
### open_tcp_keepalive

?> TCP에서 Keep-Alive 메커니즘이 죽은 연결을 감지할 수 있습니다. 응용 계층이 죽은 연결 주기에 민감하지 않거나 핫스텝 메커니즘을 구현하지 않은 경우, 운영 체제에서 제공하는 keepalive 메커니즘을 사용하여 죽은 연결을 제거할 수 있습니다.
[Server->set()](/server/methods?id=set) 구성에서 `open_tcp_keepalive => true`를 추가하면 TCP keepalive가 활성화됩니다.
또한, keepalive의 세부 사항을 조정할 수 있는 3가지 옵션이 있습니다.

  * **옵션**

     * **tcp_keepidle**

        초 단위로, 연결이 `n`초 동안 데이터 요청이 없으면 해당 연결에 대한 탐사를 시작합니다.

     * **tcp_keepcount**

        탐사의 횟수로, 횟수가 초과되면 해당 연결을 `close`합니다.

     * **tcp_keepinterval**

        탐사의 간격 시간으로, 초 단위입니다.

  * **예시**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, //4초간 데이터 전송이 없으면 탐사를 시작합니다.
    'tcp_keepinterval' => 1, //1초마다 탐사를进行一次합니다.
    'tcp_keepcount' => 5, //탐사 횟수가 5회 초과되면 패킷을 보낸 적이 없어 연결을 close합니다.
));

$serv->on('connect', function ($serv, $fd) {
    var_dump("Client:Connect $fd");
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    var_dump($data);
});

$serv->on('close', function ($serv, $fd) {
  var_dump("close fd $fd");
});

$serv->start();
```


### heartbeat_check_interval

?> **핫스텝 검출을 활성화합니다.** 【기본값: `false`】

?> 이 옵션은 얼마마다 한 번씩 순환하는지를 나타내며, 단위는 초입니다. 예를 들어 `heartbeat_check_interval => 60`는 60초마다 모든 연결을 순환하고, 해당 연결이 120초 동안 ( `heartbeat_idle_time`가 설정되지 않은 경우 기본적으로 `interval`의 두 배) 서버에 어떠한 데이터도 보낸 적이 없다면, 해당 연결을 강제로 종료합니다. 설정하지 않으면 핫스텝이 활성화되지 않으며, 이 설정은 기본적으로 비활성화되어 있습니다.

  * **알림**
    * `Server`는 액티브적으로 클라이언트에게 핫스텝 패킷을 보내지 않고, 대신 클라이언트가 핫스텝을 보내면 수동적으로 기다립니다. 서버 측의 `heartbeat_check`는 단지 연결이 마지막으로 데이터를 보낸 시간을 감지하며, 제한을 초과하면 연결을 끊어버립니다.
    * 핫스텝 검출에 의해 끊어진 연결은 여전히 [onClose](/server/events?id=onclose) 이벤트 콜백을 트리거합니다.

  * **주의**

    !> `heartbeat_check`는 `TCP` 연결에만 지원됩니다.


### heartbeat_idle_time

?> **최대 허용 가능한 빈 시간**

?> `heartbeat_check_interval`와 함께 사용해야 합니다.

```php
array(
    'heartbeat_idle_time'      => 600, // 연결이 600초 동안 서버에 어떠한 데이터도 보낸 적이 없다면, 해당 연결을 강제로 종료합니다.
    'heartbeat_check_interval' => 60,  // 60초마다 한 번씩 순환합니다.
);
```

  * **알림**

    * `heartbeat_idle_time`를 활성화하면, 서버는 액티브적으로 클라이언트에게 데이터 패킷을 보내지 않습니다.
    * `heartbeat_idle_time`만 설정하고 `heartbeat_check_interval`를 설정하지 않으면, 기본적으로 핫스텝 검출 스레드가 생성되지 않으며, PHP 코드에서는 `heartbeat` 메서드를 수동적으로 호출하여 시간 초과된 연결을 처리할 수 있습니다.


### open_eof_check

?> **EOF 검출을 활성화합니다.** 【기본값: `false`】, [TCP 패킷 경계 문제](/learn?id=tcp%EB%A1%9C%EA%B7%BC%EC%9D%B4) 참고

?> 이 옵션은 클라이언트 연결에서 온 데이터를 감지하고, 데이터 패킷의 끝이 지정한 문자열일 경우만 `Worker` 프로세스에 전달합니다. 그렇지 않으면 데이터 패킷을 계속 결합하여 캐시 영역이 넘어가거나 시간이 초과될 때까지 중단합니다. 실패할 경우, 하단은 악의적인 연결으로 판단하고 데이터를 버리고 강제로 연결을 종료합니다.  
일반적으로 `Memcache/SMTP/POP` 등의 프로토콜은 `\r\n`로 끝나므로 이 설정을 사용할 수 있습니다. 활성화하면 `Worker` 프로세스가 한 번에 항상 하나 이상의 완전한 데이터 패킷을 받을 수 있도록 보장합니다.

```php
array(
    'open_eof_check' => true,   //EOF 검출을 활성화합니다.
    'package_eof'    => "\r\n", //EOF 설정
)
```

  * **주의**

    !> 이 설정은 `STREAM`(스트림형)의 `Socket`에만 효과적이며, 예를 들어 [TCP, Unix Socket Stream](/server/methods?id=__construct)과 같습니다.   
    EOF 검출은 데이터 중에서 EOF 문자열을 찾지 않기 때문에, `Worker` 프로세스는 동시에 여러 개의 데이터 패킷을 받을 수 있으며, 응용 계층 코드에서 `explode("\r\n", $data)`를 사용하여 데이터 패킷을 분해해야 합니다.


### open_eof_split

?> **EOF 자동 분배를 활성화합니다.**

?> `open_eof_check`가 설정되었을 경우, 여러 개의 데이터가 하나의 패킷에 합쳐질 수 있습니다. `open_eof_split` 매개변수는 이 문제를 해결할 수 있으며, [TCP 패킷 경계 문제](/learn?id=tcp%EB%A1%9C%EA%B7%BC%EC%9D%B4)를 참고하세요.

?> 이 매개변수를 설정하려면 전체 데이터 패킷의 내용을 탐색하여 EOF를 찾아야 하므로 많은 양의 CPU 자원을 소모합니다. 가정합니다. 각 데이터 패킷이 2MB이고 초당 10000개의 요청이 있다면, 이것은 20GB의 CPU 문자 매칭 지시가 발생할 수 있습니다.

```php
array(
    'open_eof_split' => true,   //EOF_SPLIT 검출을 활성화합니다.
    'package_eof'    => "\r\n", //EOF 설정
)
```

  * **알림**

    * `open_eof_split` 매개변수를 활성화하면, 하단은 데이터 패킷 중에서 EOF를 찾아 분배합니다. [onReceive](/server/events?id=onreceive)는每次에 EOF 문자열로 끝나는 하나의 데이터 패킷만 받습니다.
    * `open_eof_split` 매개변수를 활성화하면, 매개변수 `open_eof_check`가 설정되든 말든 `open_eof_split`는 효과적입니다.

    * **open_eof_check와의 차이점**
    
        * `open_eof_check`는 수신 데이터의 끝이 EOF인지만 확인하기 때문에, 그 성능이 가장 좋고 거의 소모되지 않습니다.
        * `open_eof_check`는 여러 개의 데이터 패킷이 합쳐지는 문제를 해결할 수 없으며, 예를 들어 동시에 두 개의 EOF가 있는 데이터를 보내면, 하단은 한 번에 모두 반환할 수 있습니다.
        * `open_eof_split`는 데이터를 왼쪽에서 오른쪽으로 일별로 비교하여 데이터 중의 EOF를 찾아 분배하므로 성능이 떨어집니다. 그러나每次에 하나의 데이터 패킷만 반환합니다.


### package_eof

?> **EOF 문자열을 설정합니다.** [TCP 패킷 경계 문제](/learn?id=tcp%EB%A1%9C%EA%B7%BC%EC%9D%B4) 참고

?> `open_eof_check` 또는 `open_eof_split`와 함께 사용해야 합니다.

  * **주의**

    !> `package_eof`은 최대 8바이트의 문자열만 허용됩니다.


### open_length_check

?> **패킷 길이 검출 특성을 활성화합니다.** 【기본값: `false`】, [TCP 패킷 경계 문제](/learn?id=tcp%EB%A1%9C%EA%B7%BC%EC%9D%B4) 참고

?> 패킷 길이 검출은 고정 헤더 + 패킷 본체와 같은 형식 프로토콜의 해석을 제공합니다. 활성화하면, `Worker` 프로세스의 [onReceive](/server/events?id=onreceive)는每次이 완전한 데이터 패킷을 받을 수 있도록 보장합니다.  
길이 검출 프로토콜은 길이를 계산하는 데 한 번만 필요하고, 데이터 처리는 포인터 오프셋만 이루어져 매우 높은 성능을 제공하며, **권장 사용**합니다.

  * **알림**

    * **길이 프로토콜은 3가지 옵션을 통해 프로토콜 세부 사항을 제어할 수 있습니다.**

      ?> 이 구성은 `STREAM` 유형의 `Socket`에만 효과적이며, 예를 들어 [TCP, Unix Socket Stream](/server/methods?id=__construct)과 같습니다.

      * **package_length_type**

        ?> 헤더의 어느 필드를 패킷 길이의 값으로 사용하며, 하단은 10가지 길이 유형을 지원합니다. 자세히 보기 위해 [package_length_type](/server/setting?id=package_length_type)를 참고하세요.

      * **package_body_offset**

        ?> 길이를 계산할 시작字节 위치로, 일반적으로 두 가지 상황이 있습니다:

        * `length`의 값이 전체 패킷(헤더 + 본체)을 포함할 때, `package_body_offset`는 `0`입니다.
        * 헤더 길이가 `N`바이트이고, `length`의 값이 헤더를 포함하지 않고 본체만 포함할 때, `package_body_offset`는 `N`으로 설정됩니다.

      * **package_length_offset**

        ?> `length` 길이 값이 헤더의 어느字节에 위치합니다.

        * 예시:

        ```c
        struct
        {
            uint32_t type;
            uint32_t uid;
            uint32_t length;
            uint32_t serid;
            char body[0];
        }
        ```
        
    ?> 위의 커뮤니케이션 프로토콜 설계에서, 헤더 길이는 `4`개의 정수형으로, `16`바이트이며, `length` 길이 값은 세 번째 정수형에 위치합니다. 따라서 `package_length_offset`는 `8`로 설정되고, `0-3`바이트는 `type`, `4-7`바이트는 `uid`, `8-11`바이트는 `length`, `12-15`바이트는 `serid`입니다.

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```
### 패키지 길이 유형

?> **길이 값의 유형**을 받아들입니다. 한 개의 문자 매개변수를 받습니다. 이는 PHP의 [pack](http://php.net/manual/ko/function.pack.php) 함수와 일치합니다.

현재 Swoole은 다음과 같은 10가지 유형을 지원합니다:


문자 매개변수 | 역할
---|---
c | 유기적, 1바이트
C | 무기적, 1바이트
s | 유기적, 호스트字节順序, 2바이트
S | 무기적, 호스트字节順序, 2바이트
n | 무기적, 네트워크字节順序, 2바이트
N | 무기적, 네트워크字节順序, 4바이트
l | 유기적, 호스트字节順序, 4바이트(소문자 L)
L | 무기적, 호스트字节顺序, 4바이트(대문자 L)
v | 무기적, 소니어字节順序, 2바이트
V | 무기적, 소니어字节順序, 4바이트


### 패키지 길이 해석 함수

?> **패키지 길이를 설정하는 해석 함수**를 설정합니다.

?> C++ 또는 PHP의 2가지 유형의 함수를 지원합니다. 길이 함수는 반드시 정수를 반환해야 합니다.


반환 값 | 역할
---|---
0을 반환 | 길이 데이터가 부족하여 더 많은 데이터를 수신해야 합니다.
-1을 반환 | 데이터 오류로, 하단에서 자동으로 연결을 닫습니다.
패키지 길이 값(패키지 헤드와 패키지 본체의 총 길이를 포함)을 반환 | 하단에서 자동으로 패키지를 조립하여 콜백 함수에 반환합니다.

  * **알림**

    * **사용 방법**

    ?> 실제 원리는 작은 부분의 데이터를 먼저 읽고, 이 데이터에는 길이 값이 포함되어 있습니다. 그런 다음 이 길이를 하단에 반환합니다. 이후 하단에서 남은 데이터를 수신하고 패키지를 조립하여 dispatch합니다.

    * **PHP 길이 해석 함수**

    ?> ZendVM이 멀티스레드 환경에서 실행되지 않기 때문에, 하단에서 자동으로 PHP 길이 함수에 대한 Mutex 잠금기를 사용하여 병렬로 PHP 함수를 실행하는 것을 방지합니다. 1.9.3 또는 그 이상 버전에서 사용할 수 있습니다.

    !> 길이 해석 함수에서 블록적인 IO 작업을 수행하지 마십시오. 이로 인해 모든 [Reactor](/learn?id=reactor_thread) 스레드가 블록될 수 있습니다.

    ```php
    $server = new Swoole\Server("127.0.0.1", 9501);
    
    $server->set(array(
        'open_length_check'   => true,
        'dispatch_mode'       => 1,
        'package_length_func' => function ($data) {
          if (strlen($data) < 8) {
              return 0;
          }
          $length = intval(trim(substr($data, 0, 8)));
          if ($length <= 0) {
              return -1;
          }
          return $length + 8;
        },
        'package_max_length'  => 2000000,  //프로토콜 최대 길이
    ));
    
    $server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        var_dump($data);
        echo "#{$server->worker_id}>> received length=" . strlen($data) . "\n";
    });
    
    $server->start();
    ```

    * **C++ 길이 해석 함수**

    ?> 다른 PHP 확장에서, Swoole 엔진에 길이 함수를 등록하기 위해 `swoole_add_function`를 사용합니다.
    
    !> C++ 길이 함수 호출 시 하단에서 잠금기를 추가하지 않으므로, 호출자가 스레드 안전성을 보장해야 합니다.
    
    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"
    
    using namespace std;
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);
    
    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW_OK;
    }
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length)
    {
        printf("cpp, size=%d\n", length);
        return 100;
    }
    ```


### 패키지 최대 길이

?> **최대 데이터 패키지 크기를 설정합니다. 단위는 바이트입니다.**【기본값: `2M` 즉 `2 * 1024 * 1024` , 최소값은 `64K`】

?> [open_length_check](/server/setting?id=open_length_check)/[open_eof_check](/server/setting?id=open_eof_check)/[open_eof_split](/server/setting?id=open_eof_split)/[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol) 등 프로토콜 해석이 활성화되면, Swoole 하단에서 데이터 패키지를 조립합니다. 이때 패키지가 완전히 수신되지 않은 상태에서 모든 데이터는 메모리에 보존되어 있습니다.  
그래서 `package_max_length`을 설정해야 합니다. 하나의 데이터 패키지가 최대 허용하는 메모리 크기입니다. 만약 동시에 1만 개의 TCP 연결이 데이터를 보내면, 각 패키지가 `2M`이라면, 가장 극한의 경우 20GB의 메모리 공간을 차지하게 됩니다.

  * **알림**

    * `open_length_check`: 패키지 길이가 `package_max_length`을 초과할 경우, 해당 데이터를 직접 버리고 연결을 닫으며 어떠한 메모리도 차지하지 않습니다;
    * `open_eof_check`: 패키지 길이를 미리 알 수 없기 때문에, 수신된 데이터는 여전히 메모리에 보존되어 지속적으로 증가합니다. 메모리 사용량이 `package_max_length`을 초과할 경우, 해당 데이터를 직접 버리고 연결을 닫습니다;
    * `open_http_protocol`: GET 요청은 최대 8K를 허용하며, 설정 변경이 불가능합니다. POST 요청은 `Content-Length`을 검사하고, `Content-Length`이 `package_max_length`을 초과하는 경우, 해당 데이터를 직접 버리고 HTTP 400 오류를 보내고 연결을 닫습니다;

  * **주의**

    !> 이 매개변수를 너무 크게 설정하지 않는 것이 좋습니다. 그렇지 않으면 많은 메모리를 차지하게 됩니다.


### open_http_protocol

?> **HTTP 프로토콜 처리를 활성화합니다.**【기본값: `false`】

?> HTTP 프로토콜 처리를 활성화하면, [Swoole\Http\Server](/http_server)가 자동으로 이 옵션을 활성화합니다. `false`로 설정하면 HTTP 프로토콜 처리를 비활성화합니다.


### open_mqtt_protocol

?> **MQTT 프로토콜 처리를 활성화합니다.**【기본값: `false`】

?> 활성화 시 MQTT 헤드를 해석하고, worker 프로세스는 [onReceive](/server/events?id=onreceive)에서 각각 완전한 MQTT 데이터 패키지를 반환합니다.

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```


### open_redis_protocol

?> **Redis 프로토콜 처리를 활성화합니다.**【기본값: `false`】

?> 활성화 시 Redis 프로토콜을 해석하고, worker 프로세스는 [onReceive](/server/events?id=onreceive)에서 각각 완전한 Redis 데이터 패키지를 반환합니다. Redis\Server를 직접 사용하는 것이 좋습니다.

```php
$server->set(array(
  'open_redis_protocol' => true
));
```


### open_websocket_protocol

?> **WebSocket 프로토콜 처리를 활성화합니다.**【기본값: `false`】

?> WebSocket 프로토콜 처리를 활성화하면, [Swoole\WebSocket\Server](websocket_server)가 자동으로 이 옵션을 활성화합니다. `false`로 설정하면 websocket 프로토콜 처리를 비활성화합니다.  
`open_websocket_protocol` 옵션을 `true`로 설정하면, 자동으로 `open_http_protocol` 옵션을 `true`로 설정합니다.


### open_websocket_close_frame

?> **WebSocket 프로토콜의 닫기 프레임을 활성화합니다.**【기본값: `false`】

?> (opcode가 `0x08`인 프레임) onMessage 콜백에서 수신

?> 활성화 시 WebSocketServer의 onMessage 콜백에서 클라이언트 또는 서버에서 보낸 닫기 프레임을 수신할 수 있으며, 개발자는 이를 처리할 수 있습니다.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->set(array("open_websocket_close_frame" => true));

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {});

$server->start();
```
### open_tcp_nodelay

?> **`open_tcp_nodelay`를 활성화합니다.** 【기본값: `false`】

?> 활성화 시 TCP 연결에서 데이터를 보내면 Nagle 알고리즘을 비활성화하고 즉시 상대방 TCP 연결으로 보냅니다. 명령어 창과 같은 일부 장면에서는 명령어를 입력하자마자 서버로 즉시 전달하여 응답 속도를 향상시킬 수 있습니다. Nagle 알고리즘에 대해서는 자주 찾아보시기 바랍니다.


### open_cpu_affinity 

?> **CPU 친화성 설정을 활성화합니다.** 【기본 `false`】

?> 멀티코어 하드웨어 플랫폼에서 이 기능을 활성화하면 Swoole의 reactor线程/worker进程를 고정된 코어에 바绑定합니다. 이는 프로세스/타임의 실행이 여러 코어 사이에서 교차하는 것을 피하고 CPU Cache의 적중률을 향상시킵니다.

  * **알림**

    * **프로세스의 CPU 친화성 설정을 확인하기 위해 taskset 명령어 사용:**

    ```bash
    taskset -p 进程ID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > mask는 마스크 숫자로, bit 단위로 각각의 CPU 코어에 해당합니다. 만약 특정 비트가 0이면 해당 코어를 바인딩하고, 프로세스는 이 CPU에 배정됩니다. 비트가 0이면 프로세스는 이 CPU에 배정되지 않습니다. 예시에서 pid가 24666인 프로세스의 mask = f는 CPU에 바인딩되지 않았음을 나타내며, 운영체계는 이 프로세스를 임의의 CPU 코어에 배정합니다. pid가 24901인 프로세스의 mask = 8은 8을 이진으로 변환하면 `1000`이므로 이 프로세스는 제4의 CPU 코어에 바인딩되어 있습니다.


### cpu_affinity_ignore

?> **I/O 집중형 프로그램에서 모든 네트워크 중단은 CPU0에서 처리됩니다. 네트워크 I/O가 매우 무거우면 CPU0의 부하가 너무 높아 네트워크 중단을 즉시 처리할 수 없게 되어 네트워크 수신과 전송 능력이 떨어집니다.**

?> 이 옵션을 설정하지 않으면 swoole은 모든 CPU 코어를 사용하며, 하단은 reactor_id나 worker_id와 CPU 코어 수를 모듈로 하여 CPU 바인딩을 설정합니다.  
코어와 네트워크 카드가 멀티 큐 특성을 가지고 있다면 네트워크 중단이 여러 코어에 분산되어 네트워크 중단의 압력을 완화할 수 있습니다.

```php
array('cpu_affinity_ignore' => array(0, 1)) // 파라미터로 배열을 전달합니다. array(0, 1)은 CPU0,CPU1을 사용하지 않고 네트워크 중단을 처리하기 위해 비워두었습니다.
```

  * **알림**

    * **네트워크 중단 확인하기**

```shell
[~]$ cat /proc/interrupts 
           CPU0       CPU1       CPU2       CPU3       
  0: 1383283707          0          0          0    IO-APIC-edge  timer
  1:          3          0          0          0    IO-APIC-edge  i8042
  3:         11          0          0          0    IO-APIC-edge  serial
  8:          1          0          0          0    IO-APIC-edge  rtc
  9:          0          0          0          0   IO-APIC-level  acpi
 12:          4          0          0          0    IO-APIC-edge  i8042
 14:         25          0          0          0    IO-APIC-edge  ide0
 82:         85          0          0          0   IO-APIC-level  uhci_hcd:usb5
 90:         96          0          0          0   IO-APIC-level  uhci_hcd:usb6
114:    1067499          0          0          0       PCI-MSI-X  cciss0
130:   96508322          0          0          0         PCI-MSI  eth0
138:     384295          0          0          0         PCI-MSI  eth1
169:          0          0          0          0   IO-APIC-level  ehci_hcd:usb1, uhci_hcd:usb2
177:          0          0          0          0   IO-APIC-level  uhci_hcd:usb3
185:          0          0          0          0   IO-APIC-level  uhci_hcd:usb4
NMI:      11370       6399       6845       6300 
LOC: 1383174675 1383278112 1383174810 1383277705 
ERR:          0
MIS:          0
```

`eth0/eth1`는 네트워크 중단의 횟수입니다. `CPU0 - CPU3`가 평균적으로 분포되어 있다면 네트워크 카드가 멀티 큐 특성을 가지고 있음을 의미합니다. 만약 모든 것이 한 코어에 집중되어 있다면 네트워크 중단이 이 CPU에서 모두 처리되고, 이 CPU가 100%를 넘으면 시스템은 네트워크 요청을 처리할 수 없습니다. 이때는 `cpu_affinity_ignore` 설정을 사용하여 이 CPU를 비워두어 네트워크 중단을 처리하기 위해 사용해야 합니다.

상황에 따른 설정은 `cpu_affinity_ignore => array(0)`이어야 합니다.

?> `top` 명령어의 `->`을 입력하여 `1`을 입력하면 각 코어의 사용률을 확인할 수 있습니다.

  * **주의**

    !> 이 옵션은 `open_cpu_affinity`와 함께 설정해야만 효과가 발생합니다.


### tcp_defer_accept

?> **`tcp_defer_accept` 기능을 활성화합니다.** 【기본값: `false`】

?> 수치로 설정하여 TCP 연결에 데이터가 보낼 때만 `accept`를 트리거할 수 있습니다.

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

  * **알림**

    * **`tcp_defer_accept` 기능을 활성화하면 `accept`와 [onConnect](/server/events?id=onconnect)이 해당되는 시간에 변화가 생깁니다. 만약 `5`초로 설정한다면:**

      * 클라이언트가 서버에 연결된 후에는 즉시 `accept`가 트리거되지 않습니다.
      * `5초` 이내에 클라이언트가 데이터를 보내면 `accept/onConnect/onReceive`이 순차적으로 트리거됩니다.
      * `5초` 이내에 클라이언트가 어떠한 데이터도 보내면 `accept/onConnect`만 트리거됩니다.


### ssl_cert_file / ssl_key_file :id=ssl_cert_file

?> **SSL 터널 암호화를 설정합니다.**

?> 값으로 파일 이름 문자열을 지정하여 cert 인증서와 key 개인키의 경로를 나타냅니다.

  * **알림**

    * **`PEM`에서 `DER` 포맷으로 변환하기**

    ```shell
    openssl x509 -in cert.crt -outform der -out cert.der
    ```

    * **`DER`에서 `PEM` 포맷으로 변환하기**

    ```shell
    openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
    ```

  * **주의**

    !> -`HTTPS` 애플리케이션의 브라우저는 인증서를 신뢰해야만 웹 페이지를 탐색할 수 있습니다;  
    -`wss` 애플리케이션에서는 `WebSocket` 연결을 시작하는 페이지가 `HTTPS`를 사용해야 합니다;  
    -브라우저가 `SSL` 인증서를 신뢰하지 않으면 `wss`를 사용할 수 없습니다;  
    -파일은 `PEM` 포맷이어야 하며, `DER` 포맷은 지원되지 않습니다. `openssl` 도구를 사용하여 변환할 수 있습니다.

    !> `SSL`을 사용하려면 Swoole을编译할 때 [--enable-openssl](/environment?id=编译选项) 옵션을 추가해야 합니다.

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```


### ssl_method

!> 이 매개변수는 [v4.5.4](/version/bc?id=_454) 버전에서 제거되었습니다. `ssl_protocols`를 사용하세요.

?> **OpenSSL 터널 암호화 알고리즘을 설정합니다.** 【기본값: `SWOOLE_SSLv23_METHOD`】, 지원되는 유형은 [SSL 암호화 방법](/consts?id=ssl-加密方法)을 참고하세요.

?> `Server`와 `Client`가 사용하는 알고리즘이 일관되어야 하며, 그렇지 않으면 `SSL/TLS` 핸드셋이 실패하고 연결이 끊어질 것입니다.

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```
### ssl_protocols

?> **OpenSSL 터널 암호화에 사용하는 프로토콜을 설정합니다.**【기본값: `0` (모든 프로토콜 지원)】 지원되는 유형은 [SSL 프로토콜](/consts?id=ssl-protocol)을 참고하세요.

!> Swoole 버전이 `v4.5.4` 이상일 경우 사용할 수 있습니다.

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```


### ssl_sni_certs

?> **SNI(Server Name Identification) 인증서를 설정합니다.**

!> Swoole 버전이 `v4.6.0` 이상일 경우 사용할 수 있습니다.

```php
$server->set([
    'ssl_cert_file' => __DIR__ . '/server.crt',
    'ssl_key_file' => __DIR__ . '/server.key',
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'ssl_sni_certs' => [
        'cs.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_cs_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_cs_key.pem',
        ],
        'uk.php.net' => [
            'ssl_cert_file' =>  __DIR__ . '/sni_server_uk_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_uk_key.pem',
        ],
        'us.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_us_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_us_key.pem',
        ],
    ]
]);
```


### ssl_ciphers

?> **OpenSSL 암호화 알고리즘을 설정합니다.**【기본값: `EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

  * **알림**

    * `ssl_ciphers`가 공백 문자열로 설정되면 `openssl`이 암호화 알고리즘을 자동 선택합니다.


### ssl_verify_peer

?> **SSL로 인증된 상대방 인증서를 설정합니다.**【기본값: `false`】

?> 기본적으로 비활성화되어, 클라이언트 인증서를 검증하지 않습니다. 활성화하려면 동시에 `ssl_client_cert_file` 옵션을 설정해야 합니다.


### ssl_allow_self_signed

?> **자신签署된 인증서를 허용합니다.**【기본값: `false`】


### ssl_client_cert_file

?> **클라이언트 인증서가 사용되는 루트 인증서를 설정합니다.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set(array(
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,
    'ssl_allow_self_signed' => true,
    'ssl_client_cert_file'  => __DIR__ . '/config/ca.crt',
));
```

!> `TCP` 서비스에서 인증에 실패하면, 하단에서 연결을 적극적으로 종료합니다.


### ssl_compress

?> **SSL/TLS 압축을 사용할지 여부를 설정합니다.** [Co\Client](/coroutine_client/client)를 사용할 때는 별명 `ssl_disable_compression`이 있습니다.


### ssl_verify_depth

?> **인증서 체인이 너무 깊어 이 옵션의 설정값을 초과하면 검증을 중단합니다.**


### ssl_prefer_server_ciphers

?> **서버 측 보호를 활성화하여 BEAST 공격을 방어합니다.**


### ssl_dhparam

?> **DHE 암호화 알고리즘에 사용되는 `Diffie-Hellman` 매개변수를 지정합니다.**


### ssl_ecdh_curve

?> **ECDH 키 교환에 사용되는 `curve`를 지정합니다.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_compress'                => true,
    'ssl_verify_depth'            => 10,
    'ssl_prefer_server_ciphers'   => true,
    'ssl_dhparam'                 => '',
    'ssl_ecdh_curve'              => '',
]);
```


### user

?> **`Worker/TaskWorker` 서브 프로세스의 소유 사용자를 설정합니다.**【기본값: 실행 스크립트의 사용자】

?> `Server`가 `1024` 이하의 포트를 감시하려면 `root` 권한이 필요합니다. 그러나 프로그램이 `root` 사용자에서 실행되고, 코드에 취약점이 있다면 공격자는 `root` 권한으로 원격 명령을 실행할 수 있어 위험합니다. `user` 옵션을 설정하면 메인 프로세스가 `root` 권한으로 실행되고, 서브 프로세스가 일반 사용자 권한으로 실행됩니다.

```php
$server->set(array(
  'user' => 'Apache'
));
```

  * **주의**

    !> - `root` 사용자로 시작할 경우에만 유효  
    - `user/group` 구성 요소를 사용하여 작업 프로세스를 일반 사용자로 설정하면, 작업 프로세스가 `shutdown`/[reload](/server/methods?id=reload) 메서드를 호출하여 서비스를 종료하거나 재加载할 수 없습니다. 오직 `root` 계정에서 `shell` 터미널에서 `kill` 명령을 실행해야 합니다.


### group

?> **`Worker/TaskWorker` 서브 프로세스의 프로세스 사용자 그룹을 설정합니다.**【기본값: 실행 스크립트의 사용자 그룹】

?> `user` 구성과 동일하며, 이 구성은 프로세스의 소유 사용자 그룹을 변경하여 서버 프로그램의 보안성을 향상시킵니다.

```php
$server->set(array(
  'group' => 'www-data'
));
```

  * **주의**

    !> - `root` 사용자로 시작할 경우에만 유효


### chroot

?> **`Worker` 프로세스의 파일 시스템 루트 디렉터리를 재지정합니다.**

?> 이 설정은 프로세스의 파일 시스템读写가 실제 운영 체제의 파일 시스템과 격리되도록 하여 보안성을 향상시킵니다.

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```


### pid_file

?> **PID 파일 주소를 설정합니다.**

?> `Server`가 시작될 때 자동으로 `master` 프로세스의 `PID`를 파일에 쓰고, `Server`가 종료될 때 자동으로 `PID` 파일을 삭제합니다.

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

  * **주의**

    !> 사용 시 `Server`가 비정상적으로 종료될 경우, `PID` 파일이 삭제되지 않으므로 [Swoole\Process::kill($pid, 0)](/process/process?id=kill)를 사용하여 프로세스가 실제로 존재하는지 확인해야 합니다.


### buffer_input_size / input_buffer_size :id=buffer_input_size

?> **입력 버퍼 메모리 크기를 설정합니다.**【기본값: `2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```


### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **출력 버퍼 메모리 크기를 설정합니다.**【기본값: `2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, //반드시 숫자여야 함
]);
```

  * **알림**

    !> Swoole 버전이 `v4.6.7` 이상일 경우, 기본값은 무符号INT의 최대값 `UINT_MAX`입니다.

    * 단위는 바이트이며, 기본값은 `2M`입니다. 예를 들어 `32 * 1024 * 1024`을 설정하면, 한 번의 `Server->send`에서 최대 `32M` 바이트의 데이터를 보낼 수 있습니다.
    * `Server->send`, `Http\Server->end/write`, `WebSocket\Server->push` 등의 데이터 전송 명령을 호출할 때, 한 번에 전송할 최대 데이터는 `buffer_output_size` 설정에 제한됩니다.

    !> 이 매개변수는 [SWOOLE_PROCESS](/learn?id=swoole_process) 모드에서만 작동합니다. 왜냐하면 PROCESS 모드에서 Worker 프로세스의 데이터는 메인 프로세스에 전달되어야 하고, 메인 프로세스가 클라이언트에게 전달하기 때문에, 각 Worker 프로세스는 메인 프로세스와 별도의 버퍼를 확보합니다. [참조](/learn?id=reactor线程)


### socket_buffer_size

?> **클라이언트 연결의 버퍼 길이를 설정합니다.**【기본값: `2M`】

?> `buffer_output_size`와 달리, `buffer_output_size`는 Worker 프로세스의 한 번의 `send` 크기 제한이지만, `socket_buffer_size`는 `Worker`와 `Master` 프로세스 간의 통신 버퍼의 총 크기를 설정하는 것으로, [SWOOLE_PROCESS](/learn?id=swoole_process) 모드를 참고하세요.

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, //반드시 숫자여야 함, 단위는 바이트, 예를 들어 128 * 1024 *1024는 각 TCP 클라이언트 연결에 최대 128M의 전송 대기 데이터를 허용합니다.
]);
```
- **데이터 전송 캐시 영역**

    - Master 프로세스가 클라이언트에게 대량의 데이터를 전송할 때, 즉시 전송하지는 않습니다. 이때 전송되는 데이터는 서버 측의 메모리 캐시 영역에 보존됩니다. 이 매개변수는 메모리 캐시 영역의 크기를 조정할 수 있습니다.
    
    - 전송된 데이터가 너무 많아 캐시 영역이 차있어지면 `Server`는 다음과 같은 오류 메시지를 출력합니다:
    
    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```
    
    ?>전송 버퍼가 차서 `send` 실패하여 현재 클라이언트만 영향을 받으며, 다른 클라이언트는 영향을 받지 않습니다.
    서버에 많은 `TCP` 연결이 있을 경우, 최악의 경우 `serv->max_connection * socket_buffer_size` 바이트의 메모리를 차지합니다.
    
    - 특히 외부 통신을 하는 서버 프로그램은 네트워크 통신이 느리므로, 지속적으로 연속해서 데이터를 전송하면 캐시 영역이 금방 차있습니다. 전송된 데이터는 모두 `Server`의 메모리에 쌓입니다. 따라서 이러한 응용은 설계 시 네트워크의 전송 능력을 고려하여 미리 메시지를 디스크에 저장하고, 클라이언트가 서버에 수락을 완료했음을 알리면 새로운 데이터를 전송해야 합니다.
    
    - 비디오 라이브 방송 서비스와 같은 경우, `A` 사용자의 대역폭은 `100M`이며, `1초` 안에 `10M`의 데이터를 전송하는 것은 전혀 문제가 되지 않습니다. `B` 사용자의 대역폭은 `1M`만 있으므로, `1초` 안에 `10M`의 데이터를 전송하면 `B` 사용자는 `100초` 이상 걸려야 데이터를 전수할 수 있습니다. 이때 데이터는 모두 서버 메모리에 쌓입니다.
    
    - 데이터 내용의 유형에 따라 다른 처리를 할 수 있습니다. 버릴 수 있는 내용인 비디오 라이브 방송 등의 경우, 네트워크가 나쁠 때 일부 데이터 프레임을 버리는 것은 전혀 문제되지 않습니다. 잃어버릴 수 없는 내용인 위챗 메시지와 같은 경우, 먼저 서버의 디스크에 저장하고, `100`개의 메시지를 한 세트로 나눕니다. 사용자가 이 세트의 메시지를 모두 수락한 후에야 다음 세트의 메시지를 디스크에서 꺼내 클라이언트에게 전송합니다.


### enable_unsafe_event

?> **`onConnect/onClose` 이벤트를 활성화합니다.**【기본값: `false`】

?> Swoole이 [dispatch_mode](/server/setting?id=dispatch_mode)를 `1` 또는 `3`로 설정하면, 시스템이 `onConnect/onReceive/onClose`의 순서를 보장할 수 없으므로, 기본적으로 `onConnect/onClose` 이벤트를 비활성화합니다;  
응용 프로그램이 `onConnect/onClose` 이벤트가 필요하고, 순서 문제가 가져올 수 있는 안전 위험을 받아들일 수 있다면, `enable_unsafe_event`를 `true`로 설정하여 `onConnect/onClose` 이벤트를 활성화할 수 있습니다.


### discard_timeout_request

?> **연결이 닫힌 연결의 데이터 요청을 버립니다.**【기본값: `true`】

?> Swoole이 [dispatch_mode](/server/setting?id=dispatch_mode)를 `1` 또는 `3`로 설정하면, 시스템이 `onConnect/onReceive/onClose`의 순서를 보장할 수 없으므로, 연결이 닫힌 후에 일부 요청 데이터가 `Worker` 프로세스에 도달할 수 있습니다.

  * **알림**

    * `discard_timeout_request`의 기본 설정은 `true`로, `worker` 프로세스가 닫힌 연결의 데이터 요청을 받은 경우 자동으로 버린다는 것을 의미합니다.
    * `discard_timeout_request`를 `false`로 설정하면, 연결이 닫힌 상태가 아니라면 `Worker` 프로세스가 데이터 요청을 처리합니다.


### enable_reuse_port

?> **포트 재사용을 설정합니다.**【기본값: `false`】

?> 포트 재사용을 활성화하면 같은 포트를 감시하는 Server 프로그램을 중복해서 시작할 수 있습니다

  * **알림**

    * `enable_reuse_port = true`을 설정하면 포트 재사용이 가능합니다.
    * `enable_reuse_port = false`를 설정하면 포트 재사용이 불가능합니다.

!> `Linux-3.9.0` 이상 버전의 커널에서만 사용할 수 있습니다. `Swoole4.5` 이상 버전에서만 사용할 수 있습니다.


### enable_delay_receive

?> **`accept` 클라이언트 연결 후에 자동으로 [EventLoop](/learn?id= 什么是eventloop)에 가입하지 않도록 설정합니다.**【기본값: `false`】

?> 이 옵션을 `true`로 설정하면, `accept` 클라이언트 연결 후에 자동으로 [EventLoop](/learn?id= 什么是eventloop)에 가입하지 않고, 오직 [onConnect](/server/events?id=onconnect) 콜백만 트리거됩니다. `worker` 프로세스는 [$server->confirm($fd)](/server/methods?id=confirm)를 호출하여 연결을 확인하고, 이때야 `$fd`를 [EventLoop](/learn?id= 什么是eventloop)에 가입하여 데이터 수신을 시작할 수 있으며, `$server->close($fd)`를 호출하여 해당 연결을 닫을 수도 있습니다.

```php
//enable_delay_receive 옵션을 활성화합니다.
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        //연결 확인, 데이터 수신 시작
        $server->confirm($fd);
    });
});
```


### reload_async

?> **비동기 재시작 스위치를 설정합니다.**【기본값: `true`】

?> 비동기 재시작 스위치를 설정합니다. `true`로 설정하면 비동기 안전 재시작 기능이 활성화되어 `Worker` 프로세스가 비동기 이벤트가 완료된 후에만 종료됩니다. 자세한 내용은 [서비스를 올바르게 재시작하는 방법](/question/use?id=swoole如何正确的重启服务)을 참고하세요.

?> `reload_async`가 활성화되는 주요 목적은 서비스 재가중 시 코루outine나 비동기 작업이 정상적으로 종료될 수 있도록 보장하는 것입니다. 

```php
$server->set([
  'reload_async' => true
]);
```

  * **코루outine 모드**

    * `4.x` 버전에서 [enable_coroutine](/server/setting?id=enable_coroutine)를 활성화하면, 저수준에서 코루outine 수를 추가로 감지하여, 현재 코루outine가 없을 경우에만 프로세스를 종료합니다. 활성화 시, `reload_async => false`에도 불구하고 강제적으로 `reload_async`를 열어냅니다.


### max_wait_time

?> **`Worker` 프로세스가 서비스 중지 통지를 받은 후 최대 대기 시간을 설정합니다.**【기본값: `3`】

?> 종종 `worker` 프로세스가 막히거나 멈춰서 `worker`가 정상적으로 `reload`할 수 없어 일부 생산 환경에서 만족스럽지 못합니다. 예를 들어 코드 핫스위칭을 위해 `reload` 프로세스를 필요로 합니다. 그래서 Swoole은 프로세스 재시작超时 시간의 옵션을 추가했습니다. 자세한 내용은 [서비스를 올바르게 재시작하는 방법](/question/use?id=swoole如何正确的重启服务)을 참고하세요.

  * **알림**

    * **관리 프로세스가 재시작, 종료 신호를 받은 후 또는 `max_request`에 도달한 경우, 관리 프로세스는 해당 `worker` 프로세스를 다시 시작합니다. 다음과 같은 몇 가지 단계로 이루어집니다:**

      * 저수준에서 (`max_wait_time`)초의 타이머를 추가하여, 타이머가 트리거되면 프로세스가 여전히 존재하는지를 확인합니다. 존재한다면 강제로 죽이고 다시 프로세스를 뽑습니다.
      * `onWorkerStop` 콜백 안에서 마무리 작업을 해야 하며, `max_wait_time`초 안에 마무리해야 합니다.
      * 대상 프로세스에 차례로 `SIGTERM` 신호를 보내 프로세스를 죽입니다.

  * **주의**

    !> `v4.4.x` 이전에는 기본적으로 `30`초였습니다.


### tcp_fastopen

?> **TCP 빠른 핫스탠딩 특성을 활성화합니다.**【기본값: `false`】

?> 이 기능은 `TCP` 짧은 연결의 응답 속도를 향상시킬 수 있으며, 클라이언트가 핫스탠딩의 세 번째 단계를 완료하고 `SYN` 패킷을 보내면 데이터를 함께 보냅니다.

```php
$server->set([
  'tcp_fastopen' => true
]);
```

  * **알림**

    * 이 매개변수는 감시하는 포트에 설정할 수 있으며, 더 깊이 이해하고 싶은 학생들은 [google 논문](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf)를 확인하실 수 있습니다.


### request_slowlog_file

?> **요청 슬로그를 활성화합니다.** `v4.4.8` 버전부터 [제거되었습니다](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366)

!> 이 슬로그 방안은 동기적인 막힘 있는 프로세스에서만 작동하며, 코루outine 환경에서는 사용할 수 없습니다. Swoole4는 기본적으로 코루outine를 활성화하고 있으므로, `enable_coroutine`를 비활성화하지 않는 이상은 사용하지 않는 것이 좋습니다. 대신 [Swoole Tracker](https://business.swoole.com/tracker/index)의 막힘 검출 도구를 사용하세요.

?> 활성화 시 `Manager` 프로세스는 시계 신호를 설정하여 모든 `Task`와 `Worker` 프로세스를 정기적으로 감지합니다. 프로세스가 막혀 요청이 지정된 시간을 초과하면 자동으로 프로세스의 `PHP` 함수 호출 스택을 출력합니다.

?> 저수준은 `ptrace` 시스템 호출을 기반으로 구현되어 있으며, 일부 시스템은 `ptrace`를 비활성화하여 슬로그를 추적할 수 없습니다. `kernel.yama.ptrace_scope` 커널 매개변수가 `0`인지 확인해 주십시오.

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

  * **초과 시간**

```php
$server->set([
    'request_slowlog_timeout' => 2, // 요청 초과 시간을 2초로 설정합니다.
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

!> 반드시 쓰기 권한이 있는 파일이어야 하며, 그렇지 않으면 파일을 만들 수 없어 저수준에서 치명적인 오류가 발생합니다.
### Enable_coroutine

?> **비동기 스타일 서버의 코루틴 지원을 사용 여부 설정**

?> `enable_coroutine`가 Off로 설정될 경우 [이벤트 콜백 함수](/server/events)에서 자동으로 코루틴을 생성하지 않게 됩니다. 코루틴을 사용하지 않는다면 이 설정이 성능을 향상시킬 수 있습니다. [Swoole 코루틴とは 무엇인가요?](/coroutine)를 참고하세요.

  * **설정 방법**
    
    * `php.ini`에서 `swoole.enable_coroutine = 'Off'` (참고: [ini 설정 문서](/other/config.md))
    * `$server->set(['enable_coroutine' => false]);` 이 경로는 ini보다 우선 적용됩니다.

  * **`enable_coroutine` 옵션의 영향을 받는 범위**

      * onWorkerStart
      * onConnect
      * onOpen
      * onReceive
      * [setHandler](/redis_server?id=sethandler)
      * onPacket
      * onRequest
      * onMessage
      * onPipeMessage
      * onFinish
      * onClose
      * tick/after 타이머

!> `enable_coroutine`가开启되면 위의 콜백 함수에서 자동으로 코루틴이 생성됩니다.

* `enable_coroutine`가 `true`로 설정될 경우, 기본적으로 [onRequest](/http_server?id=on) 콜백에서 코루틴이 생성되므로 개발자는 `go` 함수를 사용하여 [코루틴 생성](/coroutine/coroutine?id=create)할 필요가 없습니다.
* `enable_coroutine`가 `false`로 설정될 경우, 기본적으로 코루틴이 생성되지 않으므로 개발자가 코루틴을 사용하려면 반드시 `go` 함수를 사용하여 코루틴을 생성해야 합니다. 코루틴 기능이 필요 없을 경우, 처리 방식은 `Swoole1.x`와 완전히 동일합니다.
* 주의하세요, 이开启는 Swoole이 요청을 처리하기 위해 코루틴을 사용할 것임을 의미합니다. 이벤트 내에 block하는 함수가 포함되어 있다면, 미리 [하이브리드 코루틴](/runtime)을开启하여 `sleep`, `mysqlnd`와 같은 block하는 함수나 확장을 코루틴화해야 합니다.

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    //기본적으로 코루틴을 비활성화합니다.
    'enable_coroutine' => false,
]);

$server->on("request", function ($request, $response) {
    if ($request->server['request_uri'] == '/coro') {
        go(function () use ($response) {
            co::sleep(0.2);
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });
    } else {
        $response->header("Content-Type", "text/plain");
        $response->end("Hello World\n");
    }
});

$server->start();
```


### Send_yield

?> **데이터를 보낼 때 버퍼 메모리가 부족하면, 현재 코루틴 내에서 [yield](/coroutine?id=协程调度)하여 데이터 전송이 완료될 때까지 기다린 후, 버퍼가 비워질 경우 자동으로 [resume](/coroutine?id=协程调度)하여 현재 코루틴을 계속해서 `send`数据处理합니다.**【기본값: dispatch_mod이 2/4일 때 사용 가능하며, 기본적으로开启】

* `Server/Client->send`가 `false`를 반환하고 오류코드가 `SW_ERROR_OUTPUT_BUFFER_OVERFLOW`인 경우, PHP 계층에는 `false`를 반환하지 않고 [yield](/coroutine?id=协程调度)하여 현재 코루틴을 중지합니다.
* `Server/Client`는 버퍼가 비워지는 이벤트를 감시하고, 해당 이벤트가 발생하면 버퍼 내의 데이터가 모두 전송되었음을 의미합니다. 이때 해당 코루틴을 [resume](/coroutine?id=协程调度)합니다.
* 코루틴이 회복되면, 다시 `Server/Client->send`를 호출하여 버퍼에 데이터를 쓰고, 이때 버퍼가 비어 있기 때문에 전송은 반드시 성공합니다.

개선 전

```php
for ($i = 0; $i < 100; $i++) {
    //버퍼가 가득 차 있을 경우 바로 `false`를 반환하고 오류 메시지가 "output buffer overflow" 발생합니다.
    $server->send($fd, $data_2m);
}
```

개선 후

```php
for ($i = 0; $i < 100; $i++) {
    //버퍼가 가득 차 있을 경우 현재 코루틴을 `yield`하여 전송이 완료될 때까지 기다린 후, 회복하여 다음 코드 실행을 계속합니다.
    $server->send($fd, $data_2m);
}
```

!> 이 기능은 기본적인底层 동작을 변경하므로 수동으로 비활성화할 수 있습니다.

```php
$server->set([
    'send_yield' => false,
]);
```

  * __영향 범위__

    * [Swoole\Server::send](/server/methods?id=send)
    * [Swoole\Http\Response::write](/http_server?id=write)
    * [Swoole\WebSocket\Server::push](/websocket_server?id=push)
    * [Swoole\Coroutine\Client::send](/coroutine_client/client?id=send)
    * [Swoole\Coroutine\Http\Client::push](/coroutine_client/http_client?id=push)


### Send_timeout

전송超时 시간을 설정합니다. `send_yield`와 함께 사용하여, 지정한 시간 내에 데이터가 버퍼에 전송되지 않으면,底层에서 `false`를 반환하고 오류코드를 `ETIMEDOUT`로 설정합니다. [getLastError()](/server/methods?id=getlasterror) 메서드를 사용하여 오류코드를 확인할 수 있습니다.

> 유형: 부동소수, 단위: 초, 최소 단위: 밀리초

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1.5초
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false and $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "전송超时\n";
    }
}
```


### Hook_flags

?> **하이브리드 코루틴의 Hook 함수 범위를 설정합니다.**【기본값: hook 하지 않음】

!> Swoole 버전이 `v4.5+` 또는 [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 이상일 경우 사용할 수 있으며, 자세한 내용은 [하이브리드 코루틴](/runtime)을 참고하세요.

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```


### Buffer_high_watermark

?> **버퍼의 고수준 선을 설정합니다. 단위는 바이트입니다.**

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```


### Buffer_low_watermark

?> **버퍼의 저수준 선을 설정합니다. 단위는 바이트입니다.**

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```


### Tcp_user_timeout

?> TCP_USER_TIMEOUT 옵션은 TCP 계층의 소켓 옵션으로, 데이터 패킷이 보낸 후 ACK 확인을 받지 못한 최대 시간을 나타내며, 단위는 밀리초입니다. 자세한 내용은 man 문서를 참고하세요.

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10초
]);
```

!> Swoole 버전이 `v4.5.3-alpha` 이상일 경우 사용할 수 있습니다.


### Stats_file

?> **[stats()](/server/methods?id=stats)의 내용을 쓰는 파일 경로를 설정합니다. 설정 시 자동으로 [onWorkerStart](/server/events?id=onworkerstart) 시 定时器가 설정되어 [stats()](/server/methods?id=stats)의 내용이 정해진 파일에 자동으로 쓰입니다.**

```php
$server->set([
    'stats_file' => __DIR__ . '/stats.log',
]);
```

!> Swoole 버전이 `v4.5.5` 이상일 경우 사용할 수 있습니다.


### Event_object

?> **해당 옵션을 설정하면, 이벤트 콜백이 [객체 스타일](/server/events?id=回调对象)을 사용합니다.**【기본값: `false`】

```php
$server->set([
    'event_object' => true,
]);
```

!> Swoole 버전이 `v4.6.0` 이상일 경우 사용할 수 있습니다.


### Start_session_id

?> **시작 session ID를 설정합니다.**

```php
$server->set([
    'start_session_id' => 10,
]);
```

!> Swoole 버전이 `v4.6.0` 이상일 경우 사용할 수 있습니다.


### Single_thread

?> **싱글스레드 모드를 설정합니다.** 이 옵션을 활성화하면 Reactor 스레드가 Master 프로세스의 Master 스레드와 합쳐져 Master 스레드가 로직을 처리합니다. PHP ZTS 환경에서 `SWOOLE_PROCESS` 모드를 사용하는 경우 반드시 이 옵션을 `true`로 설정해야 합니다.

```php
$server->set([
    'single_thread' => true,
]);
```

!> Swoole 버전이 `v4.2.13` 이상일 경우 사용할 수 있습니다.
### 최대 대기 바이트 수

?> **수신 버퍼의 최대 대기 길이를 설정합니다. ** 초과하면 수신을 중단합니다.

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다.


### 관리 서버

?> **Swoole Dashboard([http://dashboard.swoole.com/])에서 서비스 정보를 확인하기 위해 사용하는 관리 서버를 설정합니다.](http://dashboard.swoole.com/%EF%BC%89%E4%B8%AD%E7%94%A8%E4%BA%86%E7%94%A8%E4%BA%86%E3%80%82)

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Swoole 버전 >= `v4.8.0`에서 사용할 수 있습니다.


### 부트스트랩

?> **멀티스레드 모드에서 실행하는 프로그램의 진입 파일입니다. 기본은 현재 실행 중인 스크립트 파일의 이름입니다.**

!> Swoole 버전 >= `v6.0` , `PHP`이`ZTS` 모드인 경우, Swoole을编译할 때 `--enable-swoole-thread` 옵션을 사용해야 합니다.

```php
$server->set([
    'bootstrap' => __FILE__,
]);
```

### 초기화 인자

?> **멀티스레드에서 데이터를 공유하기 위한 데이터를 설정합니다. 이 설정은 콜백 함수를 필요로 하며, 서버가 시작될 때 자동으로 실행됩니다.**

!> Swoole에는 많은 스레드 안전 컨테이너가 내장되어 있습니다. [병렬 매트릭스](/thread/map), [병렬 리스트](/thread/arraylist), [병렬 큐](/thread/queue) 등은 안전하지 않은 변수를 함수에서 반환하지 마십시오.

!> Swoole 버전 >= `v6.0` , `PHP`이`ZTS` 모드인 경우, Swoole을编译할 때 `--enable-swoole-thread` 옵션을 사용해야 합니다.

```php
$server->set([
    'init_arguments' => function() { return new Swoole\Thread\Map(); },
]);

$server->on('request', function($request, $response) {
    $map = Swoole\Thread::getArguments();
});
```
