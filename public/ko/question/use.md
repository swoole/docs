# 사용 질문

## Swoole 성능은 어떠한가요?

> QPS 비교

Nginx 정적 페이지, Golang HTTP 프로그램, PHP7+Swoole HTTP 프로그램에 대한 압박 테스트를 Apache-Bench 도구(ab)를 사용하여 수행했습니다. 같은 기계에서, 100개의 병행을 통해 총 100만 번의 HTTP 요청을 하는 베이스 테스트에서 QPS 비교는 다음과 같습니다:

| 소프트웨어 | QPS | 소프트웨어 버전 |
| --- | --- | --- |
| Nginx | 164489.92 | nginx/1.4.6 (Ubuntu) |
| Golang | 166838.68 | go version go1.5.2 linux/amd64 |
| PHP7+Swoole | 287104.12 | Swoole-1.7.22-alpha |
| Nginx-1.9.9 | 245058.70 | nginx/1.9.9 |

!> 주의: Nginx-1.9.9의 테스트에서는 access_log를 비활성화하고, 정적 파일을 메모리에 캐시하기 위해 open_file_cache를 활성화했습니다.

> 테스트 환경

* CPU: Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* 메모리: 16G
* 드라이브: 128G SSD
* 운영 체제: Ubuntu14.04 (Linux 3.16.0-55-generic)

> 압박 테스트 방법

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> VHOST 구성

```nginx
server {
    listen 80 default_server;
    root /data/webroot;
    index index.html;
}
```

> 테스트 페이지

```html
<h1>Hello World!</h1>
```

> 프로세스 수

Nginx는 4개의 Worker 프로세스를 시작했습니다
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12月07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12月07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12月07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12月07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12月07   0:45 nginx: worker process
```

> Golang

테스트 코드

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

PHP7은 `OPcache` 가속기를 활성화했습니다.

테스트 코드

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **글로벌 웹 프레임워크 권위 있는 성능 테스트 Techempower Web Framework Benchmarks**

최신 점수 테스트 결과 주소: [techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swoole는 **동적 언어 1위**를 달성했습니다

데이터베이스 IO 작업 테스트, 기본 비즈니스 코드 사용, 특별한 최적화 없음

**MySQL을 사용하는 모든 정적 언어 프레임워크보다 성능 우수**


## Swoole는 어떻게 TCP 장기 연결을 유지할까요?

TCP 장기 연결 유지에 대한 두 가지 구성[tcp_keepalive](/server/setting?id=open_tcp_keepalive)와[heartbeat](/server/setting?id=heartbeat_check_interval)가 있습니다.


## Swoole는 어떻게 서비스를 올바르게 재시작할까요?

일상 개발에서, PHP 코드를 수정한 후에 종종 서비스를 재시작하여 코드가 적용되도록 해야 합니다. 바쁜 백엔드 서버는 항상 요청을 처리하고 있으며, 관리자가 프로세스를 `kill`하여 서버 프로그램을 종료하거나 재시작하면, 코드가 절반 실행 중에 종료될 수 있으며, 전체 비즈니스 논리의 완전성을 보장할 수 없습니다.

`Swoole`는 유연한 종료/재시작 메커니즘을 제공하여, 관리자는 `Server`에 특정 신호를 보낼 수도 있고, `reload` 메서드를 호출하여 작업 프로세스를 종료하고 다시 시작할 수 있습니다. 자세한 내용은 [reload()](/server/methods?id=reload)를 참고하세요.
 
그러나 몇 가지 주의해야 할 점이 있습니다:

첫째, 새로 수정된 코드는 `OnWorkerStart` 이벤트에서 다시 로딩해야만 적용됩니다. 예를 들어, 어떤 클래스가 `OnWorkerStart` 이전에 composer의 autoload을 통해 이미 로딩된 경우에는 안 됩니다.

둘째, `reload`는 두 가지 매개변수[max_wait_time](/server/setting?id=max_wait_time)와[reload_async](/server/setting?id=reload_async)와 함께 사용해야 합니다. 이 두 매개변수를 설정하면 `비동기 안전 재시작`이 가능합니다.

이 특성이 없다면, Worker 프로세스는 재시작 신호를 받거나 [max_request](/server/setting?id=max_request)에 도달하면 즉시 서비스를 중단합니다. 이때 Worker 프로세스 내에 여전히 이벤트 监听이 있을 수 있으며, 이러한 비동기 작업은 버려질 것입니다. 위의 매개변수를 설정하면 먼저 새로운 Worker를 만들고, 옛 Worker는 모든 이벤트를 완료한 후에 자체적으로 종료합니다. 즉, `reload_async`입니다.

옛 Worker가 계속 종료되지 않으면,底层에는 타이머가 추가되어 약속된 시간([max_wait_time](/server/setting?id=max_wait_time)초) 내에 옛 Worker가 종료되지 않으면,底层은 강제로 종료하고 [WARNING](/question/use?id=forced-to-terminate) 오류가 발생합니다.

예시:

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tClient[$fd] receive data: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

위의 코드에서 `reload_async`가 없다면, onReceive에서 생성한 타이머는 잃어버리고, 타이머의 콜백 함수를 처리할 기회가 없습니다.


### 프로세스 종료 이벤트

비동기 재시작 특성을 지원하기 위해,底层에는 [onWorkerExit](/server/events?id=onWorkerExit) 이벤트가 추가되었습니다. 옛 `Worker`가 종료될 때, `onWorkerExit` 이벤트가 트리거됩니다. 이 이벤트 콜백 함수에서, 응용 계층은 일부 장기 연결 `Socket`를 청소하려고 시도할 수 있습니다. 이벤트 루프에 fd가 없거나 [max_wait_time](/server/setting?id=max_wait_time)에 도달하면 프로세스를 종료합니다.

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

동시에 [Swoole Plus](https://www.swoole.com/swoole_plus)에서는 파일 변경을 감지하는 기능이 추가되어 있어, 수동적으로 reload하거나 신호를 보내지 않고도 파일 변경 시 자동으로 worker를 재시작할 수 있습니다.
## 왜 send 후 바로 close를 하면 안전하지 않나요?

send 후 바로 close를 하면 안전하지 않습니다. 이는 서버 측이든 클라이언트 측이든 마찬가지입니다.

send 작전이 성공한다는 것은 데이터가 성공적으로 운영체의 소켓 캐시에 쓰여졌다는 것을 의미하며, 상대방이 실제로 데이터를 받았다는 것을 의미하지 않습니다. 운영체가 실제로 성공적으로 보냈는지, 상대방 서버가 받았는지, 서버 측 프로그램이 처리했는지 확실히 보장할 수 없습니다.

> close 후의 논리는 아래의 linger 설정 관련 내용을 참고하세요.

이 논리는 전화 통화와 같습니다. A가 B에게 무언가를 말하고 나서 전화를 끊습니다. 그렇다면 B가 들었는지는 A는 모릅니다. A가 말을 다하고 나서 B가 좋다고 하면서 전화를 끊으면 그것은 확실히 안전합니다.

linger 설정

`socket`이 close될 때, 버퍼에 여전히 데이터가 있는지를 발견하면 운영체의 하단층은 `linger` 설정을 기반으로 어떻게 처리할지 결정합니다.

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0, close할 때 즉시 반환하며, 하단층은 보낸未完成의 데이터를 보낸 후에 자원을 해제합니다. 즉, 우아하게 종료합니다.
* l_onoff != 0, l_linger = 0, close할 때 즉시 반환하지만, 보낸未完成의 데이터는 보내지 않고,RST 패킷을 통해 소켓 문Descriptor를 강제로 종료합니다. 즉, 강제로 종료합니다.
* l_onoff !=0, l_linger > 0, close할 때 즉시 반환하지 않고, 커널은 일정 시간 지연합니다. 이 시간은 l_linger 값에 의해 결정됩니다.超时시간이 도래하기 전에 보낸未完成의 데이터(FIN 패킷 포함)를 보내며 다른 쪽에서 확인을 받으면 close는 올바르게 반환하고 소켓 문Descriptor는 우아하게 종료합니다. 그렇지 않으면 close는 직접 오류 값을 반환하고, 보낸 미수의 데이터가 손실되고 소켓 문Descriptor는 강제로 종료됩니다. 소켓 문Descriptor가 비Blocking형으로 설정되어 있다면 close는 직접 값을 반환합니다.


## client가 이미 다른 coroutines에 바인딩되어 있다

TCP 연결에 대해 Swoole 하단층은 동시에 하나의 coroutines만이 읽기 작전, 하나의 coroutines만이 쓰기 작전을 할 수 있도록 허용합니다. 즉, 하나의 TCP에 대해 여러 coroutines가 읽기/쓰기 작전을 하는 것은 불가능하며, 하단층은 바인딩 오류를 던집니다:

```shell
Fatal error: Uncaught Swoole\Error: Socket#6 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed 
```

재현 코드:

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

해결책 참고: https://wenda.swoole.com/detail/107474

!> 이 제약은 모든 멀티 coroutines 환경에 적용되며, 가장 흔한 것은 [onReceive](/server/events?id=onreceive) 등의 콜백 함수에서 하나의 TCP 연결을 공유하는 것입니다. 왜냐하면 이러한 콜백 함수는 자동으로 coroutines를 생성하기 때문입니다.
그런데 커넥션 풀이 필요한 경우 어떻게 하나요? `Swoole`에는 [커넥션 풀](/coroutine/conn_pool)이 내장되어 있어 바로 사용할 수 있고, 또는 `channel`을 사용하여 커넥션 풀을 수동으로 포장할 수 있습니다.


## 정의되지 않은 함수 Co\run()에 대한 호출

본 문서의 대부분의 예는 `Co\run()`을 사용하여 코루틴 컨테이너를 만듭니다. [코루틴 컨테이너란 무엇인가요?](/coroutine?id=코루틴 컨테이너란 무엇인가요?)

다음과 같은 오류가 발생하면:

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

이는 `Swoole` 확장 버전이 `v4.4.0` 미만인 경우 또는 [코루틴 간소 이름](/other/alias?id=코루틴 간소 이름)을 수동으로 종료한 경우를 의미합니다. 다음과 같은 해결책을 제공합니다:

* 버전이 낮다면 확장 버전을 `>= v4.4.0`로 업그레이드하거나 `go` 키워드를 사용하여 `Co\run`을 대체하여 코루틴을 만듭니다;
* 코루틴 간소 이름이 종료되었다면 [코루틴 간소 이름](/other/alias?id=코루틴 간소 이름)을 활성화합니다;
* `Coroutine::create` 메서드를 사용하여 `Co\run` 또는 `go`를 대체하여 코루틴을 만듭니다;
* 전체 이름을 사용합니다: `Swoole\Coroutine\run`;


## Redis나MySQL 커넥션을 하나만 공유할 수 있나요?

절대로 할 수 없습니다. 각 프로세스는 별도로 `Redis`, `MySQL`, `PDO` 커넥션을 만들어야 하며, 다른 저장소 클라이언트도 마찬가지입니다. 이유는 하나의 커넥션을 공유하면 어떤 프로세스가 결과를 처리할지 보장할 수 없기 때문입니다. 커넥션을 가진 프로세스는 이론적으로 이 커넥션에 대해 읽기/쓰기를 할 수 있으며, 이로 인해 데이터가 혼란스러워집니다.

**따라서 여러 프로세스 사이에서는 절대로 커넥션을 공유해서는 안 됩니다**

* [Swoole\Server](/server/init)에서, [onWorkerStart](/server/events?id=onworkerstart) 콜백에서 커넥션 객체를 만듭니다.
* [Swoole\Process](/process/process)에서, [Swoole\Process->start](/process/process?id=start) 후에 자식 프로세스의 콜백 함수에서 커넥션 객체를 만듭니다.
* 이 문제에 대한 정보는 `pcntl_fork`를 사용하는 프로그램에도 적용됩니다.

예시:

```php
$server = new Swoole\Server('0.0.0.0', 9502);

//onWorkerStart 콜백에서 redis/mysql 커넥션을 만들어야 합니다.
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```


## 연결이 이미 닫혔다는 문제

다음과 같은 알림이 나타납니다.

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

이것은 서버가 응답할 때, 클라이언트가 이미 연결을 끊었기 때문입니다.

일반적인 경우에는:

* 브라우저가 페이지를 미리 보다가 끊어버린다.(아직 완전히 불러지지 않았을 때 끊어짐)
* ab 압박 테스트 중에 중도에 취소된다.
* wrk 기반 시간의 압박 테스트 (시간이 초과되면 완료되지 않은 요청이 취소됨)

이러한 경우는 모두 정상적인 현상이며, 무시할 수 있습니다. 따라서 이 오류의 수준은 NOTICE입니다.

다른 상황에서 이유 없이 대량의 연결이 끊어지는 경우에는 주의해야 합니다.

```bash
WARNING swWorker_discard_data (ERRNO 1007): [2] received the wrong data[21 bytes] from socket#75

WARNING Worker_discard_data (ERRNO 1007): [2] ignore data[5 bytes] received from session#2
```

마찬가지로, 이 오류도 연결이 이미 닫혔다는 것을 나타내며, 받은 데이터는 버려집니다. [discard_timeout_request](/server/setting?id=discard_timeout_request) 참조.


## connected 속성과 연결 상태가 일관되지 않는 경우

4.x 코루틴 버전 이후로, `connected` 속성은 더 이상 실시간으로 업데이트되지 않으며, [isConnect](/client?id=isconnected) 메서드는 더 이상 신뢰할 수 없습니다.


### 이유

코루틴의 목표는 동기적이고 블록형 프로그래밍 모델과 일치하는 것입니다. 동기적이고 블록형 모델에서는 실시간으로 연결 상태가 업데이트되는 개념이 없으며, PDO, curl 등이 모두 그렇습니다. 연결 개념이 없으며, IO 작전에서 오류를 반환하거나 예외를 던져야만 연결이 끊어졌다는 것을 알 수 있습니다.

Swoole 하단층의 일반적인 방법은, IO 오류가 발생할 때, false(또는 연결이 끊어졌다는 것을 나타내는 공백 내용)를 반환하고, 클라이언트 객체에 해당하는 오류 코드와 오류 메시지를 설정합니다.
### 주의

이전 비동기 버전에서 `connected` 속성이 "실시간"으로 업데이트되는 것을 지원했지만, 실제로는 신뢰할 수 없으며, 연결은 당신이 확인한 후에 바로 끊어질 수 있습니다.


## 연결 거부는 무슨 일인가요?

telnet을 이용하여 127.0.0.1 9501을 접속하면 Connection refused가 발생합니다. 이는 서버가 해당 포트를 감시하지 않는다는 것을 나타냅니다.

* 프로그램이 성공적으로 실행되는지 확인합니다: ps aux
* 포트가 감시 중인지를 확인합니다: netstat -lp
* 네트워크 통신 과정이 정상적인지 확인합니다: tcpdump traceroute


## 자원이 임시로 사용할 수 없습니다 [11]

클라이언트 swoole_client가 `recv`을 호출할 때 다음과 같은 오류가 발생합니다:

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```

이 오류는 서버 측에서 정해진 시간 내에 데이터를 반환하지 않아 수신超时가 발생했다는 것을 나타냅니다.

* tcpdump를 사용하여 네트워크 통신 과정을 확인하고, 서버가 데이터를 보냈는지 확인합니다
* 서버의 `$serv->send` 함수는 반환된 값이 true인지 확인해야 합니다
* 외부 네트워크 통신 시 시간이 많이 소요되는 경우 swoole_client의超时 시간을 늘려야 합니다


## 작업 종료超时, 강제 종료 :id=forced-to-terminate

다음과 같은 오류가 발견됩니다:

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```

이는 약속된 시간 ([max_wait_time](/server/setting?id=max_wait_time)초) 내에 이 Worker가 종료되지 않아 Swoole 하단이 강제로 이 프로세스를 종료한 것을 나타냅니다.

다음 코드로 재현할 수 있습니다:

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```


## 파이프가 깨진 신호에 대한 콜백 함수를 찾을 수 없습니다: 13

다음과 같은 오류가 발견됩니다:

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```

이는 이미 끊어진 연결에 데이터를 보냈다는 것을 나타내며, 일반적으로는 보낸 값을 확인하지 않고, 실패해도 계속 보내려고 하는ため입니다.


## Swoole를 배우기 위해 알아야 할 기본 지식


### 멀티 프로세스/멀티 스레드

* `Linux` 운영체의 프로세스와 스레드 개념을 이해합니다
* `Linux` 프로세스/스레드 교체 스케줄링의 기본 지식을 이해합니다
* 프로세스 간 통신의 기본 지식을 이해합니다, 예를 들어 파이프, `UnixSocket`, 메시지 큐, 공유 메모리


### SOCKET

* `SOCKET`의 기본 운영을 이해합니다, 예를 들어 `accept/connect`, `send/recv`, `close`, `listen`, `bind`
* `SOCKET`의 수신 버퍼, 전송 버퍼, 블록/비블록,超时 등의 개념을 이해합니다


### IO 멀티플렉스

* `select`/`poll`/`epoll`를 이해합니다
* `select`/`epoll` 기반의 이벤트 루프, `Reactor` 모델을 이해합니다
* 읽기 가능 이벤트, 쓰기 가능 이벤트를 이해합니다


### TCP/IP 네트워크 프로토콜

* `TCP/IP` 프로토콜을 이해합니다
* `TCP`, `UDP` 전송 프로토콜을 이해합니다


### 디버그 도구

* [gdb](/other/tools?id=gdb)를 사용하여 `Linux` 프로그램을 디버그합니다
* [strace](/other/tools?id=strace)를 사용하여 프로세스의 시스템 호출을 추적합니다
* [tcpdump](/other/tools?id=tcpdump)를 사용하여 네트워크 통신 과정을 추적합니다
* 기타 `Linux` 시스템 도구, 예를 들어 ps, [lsof](/other/tools?id=lsof), top, vmstat, netstat, sar, ss 등


## Swoole\Curl\Handler 객체를 정수로 변환할 수 없습니다

[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)를 사용하는 동안 다음과 같은 오류가 발생합니다:

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```

이유는 hook 이후의 curl이 더 이상 resource 유형이 아니라 object 유형이기 때문에 정수로 변환할 수 없기 때문입니다.

!> `int` 문제는 SDK 쪽에서 코드를 수정하는 것이 좋습니다. PHP8에서는 curl이 더 이상 resource 유형이 아니라 object 유형입니다.

해결 방법은 세 가지가 있습니다:

1. [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)를 사용하지 않습니다. 그러나 [v4.5.4](/version/log?id=v454) 버전부터 [SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all)이 기본적으로 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)를 포함하고 있으므로 [SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL](https://wiki.swoole.com/#/environment?id=%e6%98%af%e9%80%89%e6%8c%89%e5%90%8d%e4%b9%89%e6%8c%89%e5%90%8d%e4%b9%89%e7%a0%81)로 설정하여 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)를 비활성화할 수 있습니다.

2. Guzzle의 SDK를 사용하여 Handler를 대체하여 코루틴화를 구현할 수 있습니다.

3. Swoole `v4.6.0` 버전부터는 [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)를 사용하여 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)를 대체할 수 있습니다.


## 한 번에 코루틴화와 Guzzle 7.0+를 동시에 사용할 때, 요청을 시작한 후 결과를 터미널에 직접 출력합니다 :id=hook_guzzle

복제 가능한 코드는 다음과 같습니다

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// v4.5.4 이전의 버전
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// 요청 결과는 직접 출력이 되며, 그대로 인쇄되지 않습니다
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> 해결 방법은 앞서 언급한 문제와 동일합니다. 그러나 이 문제는 Swoole 버전이 `v4.5.8` 이상이 되었을 때 수정되었습니다.


## 오류: 버퍼 공간이 부족합니다[55]

이 오류는 무시할 수 있습니다. 이 오류는 [socket_buffer_size](/server/setting?id=socket_buffer_size) 옵션이 너무 큰 경우, 일부 시스템에서 수용하지 않아 프로그램 실행에 영향을 미치지 않습니다.


## GET/POST 요청의 최대 크기


### GET 요청 최대 8192

GET 요청은 하나의 Http 헤더만 있어, Swoole 하단은 고정 크기의 메모리 버퍼 8K를 사용하며, 변경할 수 없습니다. 요청이 올바른 Http 요청이 아닐 경우 오류가 발생합니다. 하단은 다음과 같은 오류를 던집니다:

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```

### POST 파일 업로드

최대 크기는 [package_max_length](/server/setting?id=package_max_length) 구성 항목에 의해 제한되며, 기본은 2M입니다. 새로운 값을 전달하여 [Server->set](/server/methods?id=set)를 호출하여 크기를 변경할 수 있습니다. Swoole 하단은 전용 메모리를 사용하므로 너무 크게 설정하면 많은 병렬 요청으로 인해 서버 자원을 고갈시킬 수 있습니다.

계산 방법: `최대 메모리 사용량` = `최대 병렬 요청 수` * `package_max_length`
