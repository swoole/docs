# TCP 서버

?> `Swoole\Coroutine\Server`는 완전히[코루틴](/coroutine)화된 클래스로, 코루틴 `TCP` 서버를 생성하기 위해 사용하며, TCP와[unixSocket](/learn?id= 什么是IPC) 타입을 지원합니다.

[Server](/server/tcp_init) 모듈과는 다음과 같은 차이점이 있습니다:

* 동적으로 생성과 소멸하며, 실행 중에 동적으로 포트를 감시할 수도 있고, 동적으로 서버를 종료할 수도 있습니다
* 연결 처리 과정은 완전히 동기적이며, 프로그램은 `Connect`、`Receive`、`Close` 이벤트를 순차적으로 처리할 수 있습니다

!> 4.4 이상 버전에서 사용할 수 있습니다


## 짧은 이름

`Co\Server`라는 짧은 이름을 사용할 수 있습니다.


## 방법


### __construct()

?> **생성자입니다.** 

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **매개변수** 

    * **`string $host`**
      * **기능**：수신하는 주소
      * **기본값**：없음
      * **기타 값**：없음

    * **`int $port`**
      * **기능**：수신하는 포트【0이면 운영체제가 무작위로 포트를 할당합니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`bool $ssl`**
      * **기능**：SSL 암호화 사용 여부
      * **기본값**：`false`
      * **기타 값**：`true`

    * **`bool $reuse_port`**
      * **기능**：포트 재사용 사용 여부, 효과는[이 섹션](/server/setting?id=enable_reuse_port)의 설정과 같습니다
      * **기본값**：`false`
      * **기타 값**：`true`
      * **버전 영향**：Swoole 버전 >= v4.4.4

  * **알림**

    * **$host 매개변수는 3가지 형식에서 지원됩니다**

      * `0.0.0.0/127.0.0.1`: IPv4 주소
      * `::/::1`: IPv6 주소
      * `unix:/tmp/test.sock`: [UnixSocket](/learn?id= 什么是IPC) 주소

    * **예외**

      * 매개변수 오류, 바인딩 주소 및 포트 실패, `listen` 실패 시 `Swoole\Exception` 예외가 던집니다.


### set()

?> **プロト콜 처리 매개변수를 설정합니다.** 

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **구성 매개변수**

    * 매개변수 `$options`는 일차원 관련 키 배열이어야 하며, [setprotocol](/coroutine_client/socket?id=setprotocol) 方法에서 받아들인 구성 항목과 완전히 일치해야 합니다.

    !> [start()](/coroutine/server?id=start) 方法를 호출하기 전에 매개변수를 설정해야 합니다

    * **길이 프로토콜**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      * 'package_length_offset' => 0,
      'package_body_offset' => 4,
    ]);
    ```

    * **SSL 인증서 설정**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      'ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```


### handle()

?> **연결 처리 함수를 설정합니다.** 

!> [start()](/coroutine/server?id=start)를 호출하기 전에 처리 함수를 설정해야 합니다

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **매개변수** 

    * **`callable $fn`**
      * **기능**：연결 처리 함수를 설정합니다
      * **기본값**：없음
      * **기타 값**：없음
      
  * **예시** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            $data = $conn->recv();
        }
    });
    ```

    !> -서버는 `Accept`(연결 구축) 성공 후 자동으로 [코루틴](/coroutine?id=协程调度)를 생성하고 `$fn`을 실행합니다;  
    -`$fn`은 새로운 서브 코루틴 공간에서 실행되므로, 함수 내에서 코루틴을 다시 생성할 필요가 없습니다;  
    -`$fn`은 한 개의 매개변수를 받으며, 유형은 [Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection) 객체입니다;  
    -현재 연결의 Socket 객체를 얻을 수 있는 [exportSocket()](/coroutine/server?id=exportsocket)를 사용할 수 있습니다


### shutdown()

?> **서버를 종료합니다.** 

?> 기본적으로 `start`와 `shutdown`를 여러 번 호출할 수 있습니다

```php
Swoole\Coroutine\Server->shutdown(): bool
```


### start()

?> **서버를 시작합니다.** 

```php
Swoole\Coroutine\Server->start(): bool
```

  * **귀속값**

    * 시작에 실패하면 `false`를 반환하고 `errCode` 속성을 설정합니다
    * 성공하면 루프에 진입하여 `Accept` 연결을 합니다
    * `Accept`(연결 구축) 후에는 새로운 코루틴을 생성하고, 코루틴에서 `handle` 方法에 지정된 함수를 실행합니다

  * **오류 처리**

    * `Accept`(연결 구축)에서 `Too many open file` 오류가 발생하거나 서브 코루틴을 생성할 수 없을 경우, 1초간 정지한 후에 `Accept`을 계속합니다
    * 오류가 발생하면, `start()` 方法는 반환하고, 오류 정보는 `Warning` 형태로 출력됩니다.


## 객체


### Coroutine\Server\Connection

`Swoole\Coroutine\Server\Connection` 객체는 네 가지 방법을 제공합니다:
 
#### recv()

데이터를 수신합니다, 프로토콜 처리가 설정되어 있다면, 매번 완전한 패킷을 반환합니다

```php
function recv(float $timeout = 0)
```

#### send()

데이터를 전송합니다

```php
function send(string $data)
```

#### close()

연결을 종료합니다

```php
function close(): bool
```

#### exportSocket()

현재 연결의 Socket 객체를 얻습니다. 더 많은 기본적인 방법을 호출할 수 있습니다, 자세한 내용은 [Swoole\Coroutine\Socket](/coroutine_client/socket)를 참고하세요

```php
function exportSocket(): Swoole\Coroutine\Socket
```

## 전체 예시

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

//다중 프로세스 관리 모듈
$pool = new Process\Pool(2);
//각 OnWorkerStart 콜백이 자동으로 코루틴을 생성하도록 설정합니다
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    //각 프로세스는 9501번 포트를 감시합니다
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    //15번 신호를 받아 서비스를 종료합니다
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    //새로운 연결 요청을 받고 자동으로 코루틴을 생성합니다
    $server->handle(function (Connection $conn) {
        while (true) {
            //데이터를 수신합니다
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            //데이터를 전송합니다
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    //포트를 감시하기 시작합니다
    $server->start();
});
$pool->start();
```

!> Cygwin 환경에서 실행할 경우 단일 프로세스로 수정해야 합니다. `$pool = new Swoole\Process\Pool(1);`
