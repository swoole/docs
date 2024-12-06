# 기초 지식


## 네 가지 콜백 함수 설정 방법

* **익명 함수**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
!> `use`를 사용하여 익명 함수에 매개변수를 전달할 수 있다.

* **클래스 정적 방법**

```php
class A
{
    static function test($req, $resp)
    {
        echo "hello world";
    }
}
$server->on('Request', 'A::Test');
$server->on('Request', array('A', 'Test'));
```
!> 해당 정적 방법은 반드시 `public`이어야 한다.

* **함수**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **객체 방법**

```php
class A
{
    function test($req, $resp)
    {
        echo "hello world";
    }
}

$object = new A();
$server->on('Request', array($object, 'test'));
```

!> 해당 방법은 반드시 `public`이어야 한다.


## 동기/비동기 IO

`Swoole4+`에서는 모든 비즈니스 코드가 동기적으로 작성됩니다(Swoole1.x 시대에만 비동기적 작성이 가능했으며, 지금은 비동기적 클라이언트가 제거되었으므로 해당 요구사항은 완전히 코로코 클라이언트로 구현할 수 있습니다). 마음의 부담이 전혀 없고 인간의 사고 습관에 맞습니다. 그러나 동기적 작성의底层에는 `동기/비동기 IO`가 구분될 수 있습니다.

동기/비동기 IO가 아니라면, `Swoole/Server`는 대량의 `TCP` 클라이언트 연결을 유지할 수 있습니다(참고[SWOOLE_PROCESS 모델](/learn?id=swoole_process)). 당신의 서비스가 블록적이든 비블록적이든 별도의 구성이 필요 없으며, 코드 내에 동기적 IO 운영이 있는지 여부에 달려 있습니다.

**동기 IO란 무엇인가: **
 
간단한 예로 MySQL->query를 실행할 때, 이 프로세스는 아무것도 하지 않고 MySQL에서 결과를 기다린다. 결과가 돌아오면 다음 코드를 실행하기 때문에 동기 IO 서비스의 병렬 능력은 매우 나쁩니다.

**어떤 코드는 동기 IO인가: **

 * [하이엔드 코로코화](/runtime)가 적용되지 않은 경우, 코드 내 대부분의 IO 작업은 동기 IO입니다. 코로코화되면 동기 IO가 비동기 IO로 변하고, 프로세스는 멍하니 기다리지 않게 됩니다. 참고[코로코 스케줄러](/coroutine?id=코로코 스케줄러).
 * 일부 IO는 하이엔드 코로코화할 수 없고 동기 IO를 비동기 IO로 변환할 수 없는 경우가 있습니다. 예를 들어 MongoDB(Swoole가 이 문제를 해결할 것이라고 믿습니다). 코드 작성 시 주의해야 합니다.

!> [코로코](/coroutine)는 병렬성을 향상시키기 위한 것이며, 만약 제 응용이 고 병렬성이 없거나 일부 IO 작업을 비동기화할 수 없는 경우(예: 위의 MongoDB)라면, [하이엔드 코로코화](/runtime)를 적용하지 않고 [enable_coroutine](/server/setting?id=enable_coroutine)를 비활성화하고 더 많은 `Worker` 프로세스를 실행하면 됩니다. 이것은 Fpm/Apache와 같은 모델입니다. 주목할 만한 것은 Swoole가 상주 프로세스이기 때문에 동기 IO 성능도 크게 향상될 수 있으며, 실제 응용에서도 많은 회사가 이렇게 하고 있습니다.


### 동기 IO를 비동기 IO로 변환하기

[이전 장](/learn?id=同步io异步io)에서 동기/비동기 IO가 무엇인지 소개했습니다. Swoole에서 일부 동기 IO 작업은 비동기 IO로 변환할 수 있습니다.
 
 - [하이엔드 코로코화](/runtime)가 적용되면 MySQL, Redis, Curl 등의 작업이 비동기 IO로 변환됩니다.
 - [Event](/event) 모듈을 사용하여 이벤트를 수동으로 관리하고 fd를 [EventLoop](/learn?id=什么是eventloop)에 추가하면 비동기 IO가 됩니다. 예:

```php
//inotify를 이용해 파일 변경을 감시합니다
$fd = inotify_init();
//fd를 Swoole의 EventLoop에 추가합니다
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd);//파일이 변경되면 변경된 내용을 읽습니다.
    var_dump($var);
});
```

위의 코드에서 Swoole\Event::add를 호출하지 않으면 IO를 비동기화하지 않고 inotify_read()를 직접 호출하면 Worker 프로세스가 막히고 다른 요청은 처리되지 않습니다.

 - Swoole\Server의 [sendMessage()](/server/methods?id=sendMessage) 메서드를 사용하여 프로세스 간 통신을进行时, 기본적으로 sendMessage은 동기 IO이지만 일부 경우 Swoole에 의해 비동기 IO로 변환됩니다. User 프로세스를 예로 들어보겠습니다:

```php
$serv = new Swoole\Server("0.0.0.0", 9501, SWOOLE_BASE);
$serv->set(
    [
        'worker_num' => 1,
    ]
);

$serv->on('pipeMessage', function ($serv, $src_worker_id, $data) {
    echo "#{$serv->worker_id} message from #$src_worker_id: $data\n";
    sleep(10);//sendMessage에서 온 데이터를 받지 않고 버퍼가 빨리 차있게 됩니다.
});

$serv->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data) {

});

//상황1: 동기 IO(기본 행동)
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//기본적으로, 버퍼가 차있게 된 후, 여기는 막힙니다.
    }
}, false);

//상황2: enable_coroutine 매개변수를 통해 UserProcess 프로세스의 코로코 지원을 활성화하면, 다른 코로코가 EventLoop의 스케줄러를 받지 못할 수 있도록 방지하기 위해 Swoole는 sendMessage을 비동기 IO로 변환합니다.
$enable_coroutine = true;
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//버퍼가 차있게 된 후, 프로세스가 막히지 않고 오류가 발생합니다.
    }
}, false, 1, $enable_coroutine);

//상황3: UserProcess 프로세스 내에서 비동기 콜백(예: 타이머 설정, Swoole\Event::add 등)이 설정되어 있다면, 다른 콜백 함수가 EventLoop의 스케줄러를 받지 못할 수 있도록 방지하기 위해 Swoole는 sendMessage을 비동기 IO로 변환합니다.
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    swoole_timer_tick(2000, function ($interval) use ($worker, $serv) {
        echo "timer\n";
    });
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//버퍼가 차있게 된 후, 프로세스가 막히지 않고 오류가 발생합니다.
    }
}, false);

$serv->addProcess($userProcess);

$serv->start();
```

 - [Task 프로세스](/learn?id=taskworker进程)는 sendMessage()를 통해 프로세스 간 통신하는 것과 같지만, 차이점은 task 프로세스의 코로코 지원을 Server의 [task_enable_coroutine](/server/setting?id=task_enable_coroutine) 설정으로 활성화하며, `상황3`는 존재하지 않습니다. 즉, task 프로세스가 비동기 콜백을 활성화하여 sendMessage을 비동기 IO로 만들지는 않습니다.


## EventLoop란 무엇인가

EventLoop는 즉, 이벤트 루프라고도 하는 것으로, 간단히 epoll_wait를 이해할 수 있습니다. 모든 발생할 이벤트의 핸들(fd)를 epoll_wait에 등록하고, 이 이벤트에는 읽기, 쓰기, 오류 등이 포함됩니다.

해당 프로세스는 epoll_wait라는 커널 함수에 막혀 있으며, 이벤트(또는 타임아웃)가 발생하면 epoll_wait가 막힌 상태에서 결과를 돌려주어 해당 PHP 함수를 콜백합니다. 예를 들어, 클라이언트에서 온 데이터를 받았을 때, onReceive 콜백 함수가 호출됩니다.

많은 fd가 epoll_wait에 등록되어 있고 동시에 많은 이벤트가 발생할 때, epoll_wait 함수가 돌아가면서 해당 콜백 함수를 차례로 호출합니다. 이것을 한바퀴 이벤트 루프라고 하며, 즉 IO 멀티플렉싱입니다. 그리고 다시 epoll_wait를 호출하여 다음 이벤트 루프를 진행합니다.
## TCP 패킷 경계 문제

병행이 없는 경우 [빠른 시작 중의 코드](/start/start_tcp_server)는 정상적으로 작동할 수 있지만, 병행이 높아지면 TCP 패킷 경계 문제가 발생합니다. `TCP` 프로토콜은 하위 메커니즘에서 `UDP` 프로토콜의 순서와 패킷 손실 재전송 문제를 해결했지만, `UDP`에 비해 새로운 문제가 발생했습니다. `TCP` 프로토콜은 스트림 방식이며, 패킷에는 경계가 없습니다. 응용 프로그램이 `TCP`로 통신하면 이러한 어려움을 마주해야 합니다. 이것을 흔히 TCP 패킷 붙이기 문제라고 합니다.

`TCP` 통신은 스트림 방식이기 때문에, `1`개의 대형 패킷을 수신할 때, 여러 패킷으로 나누어 보낼 수 있습니다. 여러 차례의 `Send`는 하위에서 한 번에 통합하여 보낼 수도 있습니다. 여기서 해결해야 할 두 가지 작업이 필요합니다:

* 패킷 분할: `Server`이 여러 패킷을 수신했을 때, 패킷을 분할해야 합니다.
* 패킷 합성: `Server`이 수신한 데이터가 패킷의 일부일 때, 데이터를 캐시하고 완전한 패킷으로 합쳐야 합니다.

따라서 TCP 네트워크 통신 시에는 통신 프로토콜을 설정해야 합니다. 흔히 사용되는 TCP 일반 네트워크 통신 프로토콜에는 `HTTP`, `HTTPS`, `FTP`, `SMTP`, `POP3`, `IMAP`, `SSH`, `Redis`, `Memcache`, `MySQL` 등이 있습니다.

주목할 만한 것은, Swoole이 많은 흔한 일반 프로토콜의 해석을 내장하고 있어, 이러한 프로토콜의 서버의 TCP 패킷 경계 문제를 해결하기 위해 간단한 설정만으로도 됩니다. 자세한 내용은 [open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol)를 참고하세요.

일반 프로토콜 외에도 사용자 정의 프로토콜을 만들 수 있으며, Swoole은 `2`가지 유형의 사용자 정의 네트워크 통신 프로토콜을 지원합니다.

* **EOF 종료 자표 프로토콜**

`EOF` 프로토콜은 각 패킷의 끝에 특수 문자 시리즈를 추가하여 패킷이 끝났음을 나타냅니다. 예를 들어 `Memcache`, `FTP`, `SMTP`는 모두 `\r\n`을 종료자로 사용합니다. 데이터를 보낼 때는 패킷 끝에 `\r\n`을 추가하기만 하면 됩니다. `EOF` 프로토콜을 사용하면, 패킷 중간에 `EOF`가 나타나지 않도록 해야 하며, 그렇지 않으면 패킷 분할 오류가 발생합니다.

`Server`와 `Client`의 코드에서는 단 두 개의 매개변수를 설정하면 `EOF` 프로토콜을 사용할 수 있습니다.

```php
$server->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
```

그러나 위의 `EOF` 설정은 성능이 좋지 않을 수 있으며, Swoole은 각 바이트를 탐색하여 데이터가 `\r\n`인지 확인합니다. 위의 방법 외에도 다음과 같이 설정할 수 있습니다.

```php
$server->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
```
이러한 설정은 훨씬 더 나은 성능을 가져다주며, 데이터를 탐색할 필요가 없지만, 패킷 분할 문제만 해결할 수 있고, 패킷 합성 문제는 해결할 수 없습니다. 즉, `onReceive`에서 한 번에 클라이언트에서 보낸 여러 요청을 수신할 수 있으며, 이를 스스로 분할해야 합니다. 예를 들어 `$data = explode("\r\n", $data)`와 같이 말이죠. 이러한 설정의 가장 큰 용도는, 요청 응답식의 서비스(예: 터미널에서 명령을 입력하는 것)에서 데이터 분할 문제를 고려할 필요가 없다는 것입니다. 이유는 클라이언트가 한 번의 요청을 보낸 후에는, 서버에서 현재 요청에 대한 응답 데이터를 반환할 때까지 기다리며, 두 번째 요청을 보낼 수 없기 때문입니다. 즉, 동시에 두 개의 요청을 보내지 않습니다.

* **고정 헤드 + 패키지 몸체 프로토콜**

고정 헤드 방식은 매우 일반적이며, 서버 측 프로그램에서 자주 볼 수 있습니다. 이러한 프로토콜의 특징은 패키지가 항상 헤드 + 몸체로 구성되어 있다는 것입니다. 헤드는 몸체 또는 전체 패키지의 길이를 나타내는 필드로 지정되며, 길이는 일반적으로 `2`바이트/`4`바이트 정수로 표시됩니다. 서버는 헤드를 수신한 후, 길이 값에 따라 완전한 패키지를 더 이상接收해야 할 데이터의 정확한 양을 제어할 수 있습니다. Swoole의 설정은 이러한 프로토콜을 잘 지원하며, 모든 상황에 대응하기 위해 `4`개의 매개변수를 유연하게 설정할 수 있습니다.

`Server`는 [onReceive](/server/events?id=onreceive) 콜백 함수에서 패키지를 처리하며, 프로토콜 처리를 설정하면 완전한 패키지만을 수신할 때 [onReceive](/server/events?id=onreceive) 이벤트가 발생합니다. 클라이언트는 프로토콜 처리를 설정한 후, [$client->recv()](/client?id=recv)에 길이를 전달할 필요가 없습니다. `recv` 함수는 완전한 패키지를 수신하거나 오류가 발생한 후에 반환됩니다.

```php
$server->set(array(
    'open_length_check' => true,
    'package_max_length' => 81920,
    'package_length_type' => 'n', //see php pack()
    'package_length_offset' => 0,
    'package_body_offset' => 2,
));
```

!> 각 구체적인 설정의 의미는 `서버/클라이언트` 장의 [설정](/server/setting?id=open_length_check)小节을 참고하세요.


## IPC란 무엇인가요?

같은 호스트상의 두 개의 프로세스 간의 통신(단순히 IPC라 함)은 많은 방법이 있으며, Swoole에서는 `Unix Socket`와 `sysvmsg` 두 가지 방식을 사용합니다. 이제 각각을 소개하겠습니다:


- **Unix Socket**  

    전체적으로 UNIX Domain Socket, 약자로 `UDS`라고 불리며, 소켓의 API(socket, bind, listen, connect, read, write, close 등)를 사용합니다. TCP/IP과 달리 IP와 포트를 지정할 필요가 없으며, 파일 이름으로 나타납니다(예: FPM과 Nginx 사이의 `/tmp/php-fcgi.sock`). UDS는 Linux 커널에서 실현된 전용 메모리 통신으로, 어떠한 `IO`도 소모하지 않습니다. `1`개의 프로세스가 `write`, `1`개의 프로세스가 `read`하고, 각각 `1024`바이트의 데이터를 읽고 쓴 테스트에서, `100만`회의 통신은 단지 `1.02초`만에 이루어졌으며, 매우 강력합니다. Swoole에서 기본적으로 사용하는 것도 이러한 IPC 방식입니다.  
      
    * **`SOCK_STREAM`과 `SOCK_DGRAM`**  

        - Swoole에서 UDS 통신에는 두 가지 유형이 있는데, `SOCK_STREAM`과 `SOCK_DGRAM`로 이해할 수 있습니다. 이것은 간단히 TCP와 UDP의 차이로, `SOCK_STREAM` 유형을 사용할 때도 [TCP 패킷 경계 문제](/learn?id=tcp数据包边界问题)를 고려해야 합니다.   
        - `SOCK_DGRAM` 유형을 사용할 때는 TCP 패킷 경계 문제를 고려할 필요가 없습니다. 각 `send()`의 데이터는 경계가 있으며, 얼마나 큰 데이터를 보내면 그만큼의 데이터를 수신하고, 전송 과정에서의 패킷 손실이나 혼란 문제는 없습니다. `send`의 쓰기와 `recv`의 읽기는 순서가 완전히 일치합니다. `send`가 성공적으로 반환되면 반드시 `recv`할 수 있습니다. 

    IPC에서 전달하는 데이터가 작을 때는 `SOCK_DGRAM` 방식을 사용하는 것이 매우 적합합니다. **IP 패킷은 최대 64k의 제한이 있기 때문에, IPC를 위해 `SOCK_DGRAM`을 사용할 때 한 번에 보낼 수 있는 데이터의 크기는 64k를 초과할 수 없으며, 동시에 수신 속도가 너무 느려 운영 체인의 버퍼가 가득 차서 패킷을 버릴 수 있다는 점에 주의해야 합니다. UDP는 패킷을 허용하기 때문에 버퍼를 적절히 늘릴 수 있습니다.**


- **sysvmsg**
     
    즉, Linux에서 제공하는 `메시지 대기열`입니다. 이러한 IPC 방식은 파일 이름을 `key`로 사용하여 통신합니다. 이러한 방식은 매우 불편하며, 실제 프로젝트에서 많이 사용되지 않기 때문에 자세히 소개하지 않습니다.

    * **이런 IPC 방식은 두 가지 상황에서만 유용합니다:**

        - 데이터를 잃어버리지 않도록 하기 위해서입니다. 전체 서비스가 다운되었다가 다시 시작하면, 대기열에 있는 메시지도 여전히 남아 있어 계속해서 소비할 수 있습니다. **하지만 이 경우에도 더러운 데이터 문제가 발생할 수 있습니다.**
        - 외부에서 데이터를 전달할 수 있습니다. 예를 들어 Swoole의 `Worker 프로세스`가 `Task 프로세스`에게 메시지 대기열을 통해 작업을 전달하거나, 제3자의 프로세스도 메시지를 대기열에 넣어 `Task`이 소비할 수 있으며, 심지어 명령어를 통해 대기열에 메시지를 직접 추가할 수도 있습니다.
## 메인 프로세스, 리액터 스레드, 워커 프로세스, 작업 프로세스, 매니저 프로세스의 차이와 연관성 :id=diff-process


### 메인 프로세스

* 메인 프로세스는 멀티스레드 프로세스로, [프로세스/스레드 구조도](/server/init?id=프로세스스레드구조도)를 참고하세요.


### 리액터 스레드

* 리액터 스레드는 메인 프로세스에서 생성된 스레드입니다.
* 클라이언트의 `TCP` 연결을 유지하고, 네트워크 `IO`, 프로토콜 처리, 데이터 수신을 담당합니다.
* 어떤 PHP 코드도 실행하지 않습니다.
* `TCP` 클라이언트에서 온 데이터를 버퍼링하고, 합치며, 하나의 완전한 요청 패킷으로 분해합니다.


### 워커 프로세스

* 리액터 스레드가 전달한 요청 패킷을 받아 PHP 콜백 함수를 실행하여 데이터를 처리합니다.
* 응답 데이터를 생성하고 리액터 스레드에 병행하여 전송되며, 리액터 스레드가 `TCP` 클라이언트에게 보냅니다.
* 비동기 비블록 모드일 수도 있고, 동기 블록 모드일 수도 있습니다.
* 워커는 멀티 프로세스 방식으로 실행됩니다.


### 작업 워커 프로세스

* 워커 프로세스가 Swoole\Server->[task](/server/methods?id=task)/[taskwait](/server/methods?id=taskwait)/[taskCo](/server/methods?id=taskCo)/[taskWaitMulti](/server/methods?id=taskWaitMulti) 방법을 통해 전달한 작업을 받아 처리합니다.
* 작업을 처리하고 결과 데이터를 워커 프로세스에 반환합니다( [Swoole\Server->finish](/server/methods?id=finish)을 사용합니다).
* 완전히**동기 블록** 모드입니다.
* 작업 워커는 멀티 프로세스 방식으로 실행되며，[task 완전 예제](/start/start_task)을 참고하세요.


### 매니저 프로세스

* 워커/`task` 프로세스의 생성/재생성을 담당합니다.

그들 사이의 관계는 리액터가 `nginx`와 같고, 워커가 `PHP-FPM`와 같다고 이해할 수 있습니다. 리액터 스레드는 네트워크 요청을 비동기 병렬로 처리한 다음 워커 프로세스에 전달하여 처리합니다. 리액터와 워커는 [unixSocket](/learn?id= 什么是IPC)를 통해 통신합니다.

`PHP-FPM` 애플리케이션에서는 종종 작업을 `Redis` 등 큐에 비동기적으로 전달하고 백그라운드에서 일부 PHP 프로세스를 실행하여 이러한 작업을 비동기적으로 처리합니다. Swoole이 제공하는 `TaskWorker`는 작업의 전달, 큐, PHP 작업 처리 프로세스 관리를 하나로 결합한 더 완벽한 솔루션입니다. 기본적으로 제공되는 API를 통해 비동기 작업 처리를 매우 간단하게 실현할 수 있습니다. 또한 `TaskWorker`는 작업이 완료된 후에 결과를 워커로 반환할 수도 있습니다.

Swoole의 `Reactor`, `Worker`, `TaskWorker`는 서로 긴밀하게 결합되어 더 고급 사용 방식을 제공할 수 있습니다.

더 쉽게 비유하자면, 서버가 공장이 있다면 리액터는 판매자이고, 클라이언트의 주문을 받습니다. 워커는 노동자이며, 판매자가 주문을 받은 후에 워커가 일하여 고객이 원하는 것을 생산합니다. 작업 워커는 행정 직원이 되어 워커가 잡다한 일을 도와주어 워커가 집중해서 일할 수 있도록 할 수 있습니다.

그림:

![process_demo](_images/server/process_demo.png)


## Server의 세 가지 운영 모드 소개

Swoole\Server 구현자의 세 번째 매개변수에는 [SWOOLE_BASE](/learn?id=swoole_base), [SWOOLE_PROCESS](/learn?id=swoole_process) 및 [SWOOLE_THREAD](/learn?id=swoole_thread) 세 가지 상수를 채울 수 있으며, 다음은 이 세 가지 모드의 차이점과 장단점을 차례로 소개합니다.


### SWOOLE_PROCESS

SWOOLE_PROCESS 모드의 Server는 모든 클라이언트의 TCP 연결이 [주 프로세스](/learn?id=reactor线程)와建立的이며, 내부 구현이 비교적 복잡하고, 프로세스 간 통신 및 프로세스 관리 메커니즘이 많이 사용됩니다. 비즈니스 로직이 매우 복잡한 상황에 적합합니다. Swoole은 완벽한 프로세스 관리 및 메모리 보호 메커니즘을 제공합니다.
비즈니스 로직이 매우 복잡한 경우에도 장기적으로 안정적으로 실행할 수 있습니다.

Swoole은 [리액터](/learn?id=reactor线程) 스레드에서 `Buffer` 기능을 제공하여 많은 느린 연결과 문자별로 악의적인 클라이언트를 대응할 수 있습니다.

#### 프로세스 모드의 장점:

* 연결과 데이터 요청 전송이 분리되어 있어 일부 연결의 데이터량이 많고 다른 연결의 데이터량이 적어 워커 프로세스가 불균형해질 수 없습니다.
* 워커 프로세스에서 치명적인 오류가 발생했을 때, 연결은 절단되지 않습니다.
* 단일 연결 병렬을 실현할 수 있으며, 소수의 `TCP` 연결만 유지하고, 요청은 여러 워커 프로세스에서 병렬로 처리될 수 있습니다.

#### 프로세스 모드의 단점:

* 2차 IPC的开销이 존재하며, `master` 프로세스와 `worker` 프로세스는 [unixSocket](/learn?id=什么是IPC)를 통해 통신해야 합니다.
* `SWOOLE_PROCESS`는 PHP ZTS를 지원하지 않으며, 이러한 상황에서는 `SWOOLE_BASE`를 사용하거나 [single_thread](/server/setting?id=single_thread)을 true로 설정해야 합니다.


### SWOOLE_BASE

SWOOLE_BASE는 전통적인 비동기 비블록 `Server`입니다. `Nginx`와 `Node.js` 등의 프로그램과 완전히 동일합니다.

[worker_num](/server/setting?id=worker_num) 매개변수는 BASE 모드에도 유효하며, 여러 개의 `Worker` 프로세스를 시작합니다.

TCP 연결 요청이 들어올 때, 모든 Worker 프로세스가 이 연결을 경쟁하여 최종적으로 하나의 worker 프로세스가 성공하여 클라이언트와 직접 TCP 연결을 구축하고, 이후 이 연결의 모든 데이터 수신은 이 worker와 직접 통신하며, 주 프로세스의 Reactor 스레드를 거치지 않습니다.

* BASE 모드에는 [Master](/learn?id=manager进程) 프로세스의 역할이 없으며, 오직 [Manager](/learn?id=manager进程) 프로세스의 역할만 있습니다.
* 각 [Worker](/learn?id=worker_process) 프로세스는 동시에 [SWOOLE_PROCESS](/learn?id=swoole_process) 모드의 [Reactor](/learn?id=reactor线程) 스레드와 [Worker](/learn?id=worker_process) 프로세스의 두 가지 책임을 지고 있습니다.
* BASE 모드에서 [Manager](/learn?id=manager_process) 프로세스는 선택적이며, `worker_num=1`가 설정되고 [Task](/server/methods?id=task) 및 [MaxRequest](/server/settings?id=max_request) 기능이 사용되지 않을 경우, 하단에서 직접 하나의 별도의 [Worker](/learn?id=worker_process) 프로세스를 만들 것이지 [Manager](/learn?id=manager_process) 프로세스를 만들지 않습니다.

#### BASE 모드의 장점:

* BASE 모드에는 IPC开销이 없어 성능이 더 좋습니다.
* BASE 모드의 코드는 더 간단하고 실수하기 어렵습니다.

#### BASE 모드의 단점:

* TCP 연결은 [Worker](/learn?id=worker_process) 프로세스에서 유지되므로 특정 [Worker](/learn?id=worker_process) 프로세스가 마비 될 경우, 해당 [Worker](/learn?id=worker_process) 내의 모든 연결이 닫힐 것입니다.
* 소수의 TCP 장기 연결은 모든 [Worker](/learn?id=worker_process) 프로세스를 활용할 수 없습니다.
* TCP 연결은 [Worker](/learn?id=worker_process)와 연결되어 있어 장기 연결 애플리케이션에서 일부 연결의 데이터량이 많을 경우, 해당 연결이 있는 [Worker](/learn?id=worker_process) 프로세스의 부하가 매우 높을 수 있습니다. 그러나 일부 연결의 데이터량이 작기 때문에 [Worker](/learn?id=worker_process) 프로세스의 부하가 매우 낮을 수 있으며, 다른 [Worker](/learn?id=worker_process) 프로세스에서는 균형을 이룰 수 없습니다.
* 콜백 함수 내에 블록操作的이 있을 경우 Server가 동기 모드로 후퇴할 수 있으며, 이때 TCP의 [backlog](/server/settings?id=backlog) 대기열이 가득 찰 수 있습니다.

#### BASE 모드의 적합한 사용 장면:

클라이언트 간에 상호 작용이 필요 없는 경우 BASE 모드를 사용할 수 있습니다. 예를 들어 `Memcache` 、 `HTTP` 서버 등이 있습니다.

#### BASE 모드의 제한:

BASE 모드에서 [Server 방법](/server/methods) 중에서 [send](/server/methods?id=send) 및 [close](/server/methods?id=close)를 제외하고는 다른 방법은 모두**프록세스 간 실행**을 지원하지 않습니다.

!> v4.5.x 버전의 BASE 모드에서는 `send` 방법만 프로세스 간 실행을 지원합니다; v4.6.x 버전에서는 `send` 및 `close` 방법만 지원됩니다.
### SWOOLE_THREAD

Swoole_THREAD는 `Swoole 6.0`에서 도입된 새로운 운영 모드로, PHP zts 모드를 이용하여 이제 멀티스레드 모드의 서비스를 실행할 수 있게 되었습니다.

[worker_num](/server/setting?id=worker_num) 매개변수는 THREAD 모드에도 여전히 유효하며, 단지 멀티 프로세스 생성이 멀티스레드 생성으로 바뀌고, 여러 개의 Worker 스레드가 시작됩니다.

하나의 프로세스만이 있으며, 자식 프로세스는 클라이언트의 요청을 처리하기 위해 자식 스레드로 변환됩니다.

#### THREAD 모드의 장점:
* 프로세스 간 통신이 더 간단하며, 추가적인 IPC 통신 소모가 없습니다.
* 디버깅이 더 편리하며, 하나의 프로세스이기 때문에 `gdb -p`가 더 간단합니다.
* 코루outine 병렬 IO 프로그래밍의 편리함을 가지면서도 멀티스레드 병행 실행과 공유 메모리 스택의 이점을 가질 수 있습니다.

#### THREAD 모드의 단점:
* Crash가 발생했을 때 또는 Process::exit()를 호출하면 전체 프로세스가 종료되며, 클라이언트에서는 오류 재시도, 연결 끊기 재접속 등 장애 복구 로직을 잘 준비해야 하며, 또한 supervisor와 docker/k8s를 사용하여 프로세스 종료 후 자동 재시동을 해야 합니다.
* ZTS와 잠금 연산에 부가 비용이 발생할 수 있으며, 성능은 NTS 멀티 프로세스 병행 모델보다 약 10% 떨어질 수 있습니다. 무状态 서비스의 경우에는 여전히 NTS 멀티 프로세스 운영 방식을 권장합니다.
* 스레드 간에서 객체와 자원을 전달하는 것이 지원되지 않습니다.

#### THREAD 모드의 적합한 사용场景:
* THREAD 모드는 게임 서버, 통신 서버 개발에 더 효율적입니다.


## Process, Process\Pool, UserProcess의 차이점은 무엇인가 :id=process-diff


### Process

[Process](/process/process)는 Swoole가 제공하는 프로세스 관리 모듈로, PHP의 `pcntl`를 대체합니다.
 
* 프로세스 간 통신을 편리하게 구현할 수 있습니다;
* 표준 입출력을 리디렉션할 수 있으며, 자식 프로세스 내에서 `echo`는 화면에 출력되지 않고 파이프에 쓰입니다. 키보드 입력을 파이프로 읽을 수 있도록 리디렉션할 수 있습니다;
* [exec](/process/process?id=exec) 인터페이스를 제공하여, 생성된 프로세스가 다른 프로그램을 실행할 수 있으며, 원래의 PHP 부모 프로세스와 쉽게 통신할 수 있습니다;

!> 코루outine 환경에서는 `Process` 모듈을 사용할 수 없으며, `runtime hook`+`proc_open`을 사용하여 구현할 수 있습니다. 자세한 내용은 [코루outine 프로세스 관리](/coroutine/proc_open)를 참고하세요.


### Process\Pool

[Process\Pool](/process/process_pool)는 Server의 프로세스 관리 모듈을 PHP 클래스로 포장하여, PHP 코드에서 Swoole의 프로세스 관리자를 사용할 수 있도록 지원합니다.

실제 프로젝트에서는 `Redis`, `Kafka`, `RabbitMQ`를 기반으로 한 멀티 프로세스 대기자, 멀티 프로세스 크래WL러 등의 장기 실행 스크립트를 작성하는 것이 자주 필요합니다. 개발자는 `pcntl`와 `posix` 관련 확장 라이브러리를 사용하여 멀티 프로세스 프로그래밍을 구현해야 하지만, 이를 통해 심각한 Linux 시스템 프로그래밍 지식을 갖추지 못한 개발자는 쉽게 문제에 직면할 수 있습니다. Swoole가 제공하는 프로세스 관리자를 사용하면 멀티 프로세스 스크립트 프로그래밍 작업을 크게 단순화할 수 있습니다.

* 작업 프로세스의 안정성을 보장합니다;
* 신호 처리 지원합니다;
* 메시지 대기 및 TCP-Socket 메시지 전달 기능을 지원합니다;

### UserProcess

`UserProcess`는 [addProcess](/server/methods?id=addprocess)를 사용하여 추가된 사용자 정의 작업 프로세스로, 일반적으로 모니터링, 보고 또는 기타 특별한 작업을 수행하기 위해 사용됩니다.

`UserProcess`는 [Manager 프로세스](/learn?id=manager进程)에 위탁되지만, [Worker 프로세스](/learn?id=worker进程)와 비교할 때는 더 독립적인 프로세스로, 사용자 정의 기능을 실행하기 위해 사용됩니다.
