# 사건

`Swoole` 확장은 또한 기본적인 `epoll/kqueue` 사건 순환을 직접 조작할 수 있는 인터페이스를 제공합니다. 다른 확장이 만든 `socket`, `PHP` 코드에서 `stream/socket` 확장이 만든 `socket` 등을 `Swoole`의 [EventLoop](/학습?id=무슨것이eventloop인가요)에 등록할 수 있습니다.
그렇지 않으면 제3자의 `$fd`가 동기적 IO인 경우 `Swoole`의 EventLoop이 실행되지 못할 수 있습니다. [참조 사례](/학습?id=동기적io를비동기적io로전환하다)

!> `Event` 모듈은 비교적 하단에 있으며, `epoll`의 초기 포장입니다. 사용자는 IO 멀티플렉스 프로그래밍 경험이 있는 것이 좋습니다.


## 사건 우선순위

1. `Process::signal`을 통해 설정된 신호 처리 콜백 함수
2. `Timer::tick`와 `Timer::after`를 통해 설정된 타이머 콜백 함수
3. `Event::defer`을 통해 설정된 지연 실행 함수
4. `Event::cycle`을 통해 설정된 주기 콜백 함수


## 방법


### add()

하단의 `reactor` 사건 감시에 `socket`을 등록합니다. 이 함수는 `Server` 또는 `Client` 모드에서 사용할 수 있습니다.
```php
Swoole\Event::add(mixed $sock, callable $read_callback, callable $write_callback = null, int $flags = null): bool
```

!> `Server` 프로그램에서 사용할 때는 `Worker` 프로세스가 시작된 후에 사용해야 합니다. `Server::start` 이전에는 어떤 비동기적 `IO` 인터페이스도 호출해서는 안 됩니다.

* **매개변수** 

  * **`mixed $sock`**
    * **기능**： 파일 디스크립터, `stream` 자원, `sockets` 자원, `object`
    * **기본값**： 없음
    * **기타 값**： 없음

  * **`callable $read_callback`**
    * **기능**： 읽기 가능 사건 콜백 함수
    * **기본값**： 없음
    * **기타 값**： 없음

  * **`callable $write_callback`**
    * **기능**： 쓰기 가능 사건 콜백 함수【해당 `$socket`가 읽기 가능하거나 쓰기 가능할 때 지정한 함수를 콜백합니다.】
    * **기본값**： 없음
    * **기타 값**： 없음

  * **`int $flags`**
    * **기능**： 사건 유형의 마스크【读取/写入 가능 사건을 끌어내거나 끌어들일 수 있는지 여부를 선택할 수 있습니다. 예: `SWOOLE_EVENT_READ`, `SWOOLE_EVENT_WRITE`, 혹은 `SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE`】
    * **기본값**： 없음
    * **기타 값**： 없음

* **$sock 4가지 유형**


유형 | 설명
---|---
int | 파일 디스크립터, `Swoole\Client->$sock`, `Swoole\Process->$pipe` 혹은 다른 `$fd` 포함
stream 자원 | `stream_socket_client`/`fsockopen`으로 만든 자원
sockets 자원 | `sockets` 확장에서 `socket_create`으로 만든 자원, 컴파일 시 [./configure --enable-sockets](/환경?id=컴파일 옵션)을 추가해야 합니다.
object | `Swoole\Process` 혹은 `Swoole\Client`, 하단에서 자동으로 [UnixSocket](/학습?id=무슨것이IPC인가요)로 변환(`Process`) 혹은 클라이언트 연결의 `$socket` (`Swoole\Client`)

* **귀속값**

  * 사건 감시 등록에 성공하면 `true`를 반환합니다.
  * 등록에 실패하면 `false`를 반환하며, `swoole_last_error`를 사용하여 오류 코드를 얻을 수 있습니다.
  * 이미 등록된 `$socket`는 중복 등록이 불가능하며, `swoole_event_set`를 사용하여 `$socket`에 해당하는 콜백 함수와 사건 유형을 수정할 수 있습니다.

  !> `Swoole\Event::add`를 사용하여 `$socket`를 사건 감시에 등록하면, 하단에서 자동으로 해당 `$socket`를 비비활성 모드로 설정합니다.

* **사용 예시**

```php
$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Swoole\Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    //socket 처리 완료 후, epoll 사건에서 socket을 제거합니다.
    Swoole\Event::del($fp);
    fclose($fp);
});
echo "Finish\n";  //Swoole\Event::add는 프로세스를 막지 않으므로 이 줄의 코드는 순서대로 실행됩니다.
```

* **콜백 함수**

  * 읽기 가능(`$read_callback`) 사건 콜백 함수에서는 반드시 `fread`, `recv` 등의 함수를 사용하여 `$socket` 캐시 영역의 데이터를 읽어야 합니다. 그렇지 않으면 사건이 지속적으로 발동합니다. 더 이상 읽고 싶지 않을 경우에는 `Swoole\Event::del`를 사용하여 사건 감시를 제거해야 합니다.
  * 쓰기 가능(`$write_callback`) 사건 콜백 함수에서는 `$socket`에 데이터를 쓴 후에 반드시 `Swoole\Event::del`를 사용하여 사건 감시를 제거해야 합니다. 그렇지 않으면 쓰기 가능 사건이 지속적으로 발동합니다.
  * `fread`, `socket_recv`, `socket_read`, `Swoole\Client::recv`이 `false`를 반환하고 오류 코드가 `EAGAIN`인 경우, 현재 `$socket`의 수신 캐시 영역에 아무런 데이터도 없음을 의미하며, 이때는 [EventLoop](/학습?id=무슨것이eventloop인가요) 통지를 기다리며 읽기 가능 감시를 추가해야 합니다.
  * `fwrite`, `socket_write`, `socket_send`, `Swoole\Client::send`이 `false`를 반환하고 오류 코드가 `EAGAIN`인 경우, 현재 `$socket`의 전송 캐시 영역이 가득 차 있어서 일시적으로 데이터를 전송할 수 없음을 의미하며, 이때는 쓰기 가능 사건을 감시하여 [EventLoop](/학습?id=무슨것이eventloop인가요) 통지를 기다려야 합니다.


### set()

사건 감시의 콜백 함수와 마스크를 수정합니다.

```php
Swoole\Event::set($fd, mixed $read_callback, mixed $write_callback, int $flags): bool
```

* **매개변수** 

  * 매개변수는 [Event::add](/event?id=add)와 완전히 동일합니다. `$fd`가 [EventLoop](/학습?id=무슨것이eventloop인가요)에 존재하지 않을 경우 `false`를 반환합니다.
  * `$read_callback`가 `null`이 아닐 경우, 지정된 함수를 사용하여 읽기 가능 사건 콜백 함수를 수정합니다.
  * `$write_callback`가 `null`이 아닐 경우, 지정된 함수를 사용하여 쓰기 가능 사건 콜백 함수를 수정합니다.
  * `$flags`는 읽기 가능(``SWOOLE_EVENT_READ``)과 쓰기 가능(``SWOOLE_EVENT_WRITE``) 사건 감시를 끌어내거나 끌어들일 수 있습니다.  

  !> 주의: ``SWOOLE_EVENT_READ`` 사건을 감시하고 있지만 현재는 ``read_callback``를 설정하지 않은 경우, 하단에서 직접적으로 ``false``를 반환하고 추가 실패합니다. ``SWOOLE_EVENT_WRITE``도 마찬가지입니다.

* **상태 변경**

  * ``Event::add`` 또는 ``Event::set``를 사용하여 읽기 가능 사건 콜백을 설정했지만, ``SWOOLE_EVENT_READ`` 읽기 가능 사건을 감시하지 않은 경우, 하단에서 콜백 함수의 정보를 저장할 뿐 사건 콜백을 생성하지 않습니다.
  * ``Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)``를 사용하여 감시하는 사건 유형을 변경하면, 하단에서 읽기 가능 사건이 발동합니다.

* **콜백 함수 해제**

!> 주의 ``Event::set``는 콜백 함수를 교체할 수 있지만, 사건 콜백 함수를 해제할 수는 없습니다. 예를 들어 ``Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)``에서 매개변수로 전달된 ``read_callback``와 ``write_callback``가 모두 ``null``이면, ``Event::add``에서 설정한 콜백 함수를 수정하지 않으려는 것이지, 사건 콜백 함수를 ``null``로 만드는 것이 아닙니다.

해당 사건 감시를 제거하기 위해서만 ``Event::del``를 호출하면 하단에서 ``read_callback``와 ``write_callback`` 사건 콜백 함수가 해제됩니다.


### isset()

입력된 `$fd`가 사건 감시에 등록되어 있는지를 검사합니다.

```php
Swoole\Event::isset(mixed $fd, int $events = SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE): bool
```

* **매개변수** 

  * **`mixed $fd`**
    * **기능**： 임의의 socket 파일 디스크립터【[Event::add](/event?id=add) 문서 참고】
    * **기본값**： 없음
    * **기타 값**： 없음

  * **`int $events`**
    * **기능**： 검사하는 사건 유형
    * **기본값**： 없음
    * **기타 값**： 없음

* **$events**
사건 유형 | 설명
---|---
`SWOOLE_EVENT_READ` | 읽기 가능 사건을 감시하고 있는지 여부
`SWOOLE_EVENT_WRITE` | 쓰기 가능 사건을 감시하고 있는지 여부
`SWOOLE_EVENT_READ \| SWOOLE_EVENT_WRITE` | 읽기 가능 또는 쓰기 가능 사건을 감시하고 있는지 여부

* **사용 예시**

```php
use Swoole\Event;

$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    Swoole\Event::del($fp);
    fclose($fp);
}, null, SWOOLE_EVENT_READ);
var_dump(Event::isset($fp, SWOOLE_EVENT_READ)); //true 반환
var_dump(Event::isset($fp, SWOOLE_EVENT_WRITE)); //false 반환
var_dump(Event::isset($fp, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)); //true 반환
```


### write()

PHP의 내장 `stream/sockets` 확장으로 생성된 소켓에 사용하여 `fwrite/socket_send` 등의 함수를 통해 상대방에 데이터를 보냅니다. 보낼 데이터 양이 많거나 소켓 쓰기 버퍼가 가득 차면, 보낼 수 없어 막힐 경우나 [EAGAIN](/other/errno?id=linux) 오류를 반환합니다.

`Event::write` 함수는 `stream/sockets` 자원의 데이터 보낼 수 있는 것을 **비동기**로 만들 수 있으며, 버퍼가 가득 차거나 [EAGAIN](/other/errno?id=linux)이 반환될 경우, Swoole 하단층은 데이터를 보낼 대기열에 넣고 쓰기 가능하게 감시합니다. 소켓이 쓸 수 있을 때 Swoole 하단층은 자동으로 작성합니다.

```php
Swoole\Event::write(mixed $fd, miexd $data): bool
```

* **매개변수** 

  * **`mixed $fd`**
    * **기능** : 임의의 소켓 파일 설명자 【참조 [Event::add](/event?id=add) 문서】
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`miexd $data`**
    * **기능** : 보낼 데이터 【보낼 데이터의 길이는 `Socket` 버퍼 크기를 초과할 수 없습니다】
    * **기본값** : 없음
    * **기타 값** : 없음

!> `Event::write`는 `SSL/TLS` 등 터널 암호화가 있는 `stream/sockets` 자원에는 사용할 수 없습니다  
`Event::write` 작동에 성공하면 자동으로 해당 `$socket`를 비활성화 모드로 설정합니다

* **사용 예시**

```php
use Swoole\Event;

$fp = stream_socket_client('tcp://127.0.0.1:9501');
$data = str_repeat('A', 1024 * 1024*2);

Event::add($fp, function($fp) {
     echo fread($fp);
});

Event::write($fp, $data);
```

#### SOCKET 버퍼가 가득 차면 Swoole의 하단 논리

지속적으로 `SOCKET`에 쓰면 상대방이 읽어주지 않으면 `SOCKET` 버퍼가 가득 차게 됩니다. Swoole 하단층은 데이터를 메모리 버퍼에 보관하고, 쓰기 가능 사건이 발생할 때까지 `SOCKET`에 쓰지 않습니다.

메모리 버퍼도 가득 차면, 이때 Swoole 하단층은 `pipe buffer overflow, reactor will block.` 오류를 던지고 막힐 상태가 됩니다.

!> 버퍼가 가득 차면 `false`를 반환하는 것은 원자적 작동이며, 전체적으로 성공하거나 실패만 합니다


### del()

`reactor`에서 감시하고 있는 `socket`를 제거합니다. `Event::del`은 `Event::add`와 함께 사용해야 합니다.

```php
Swoole\Event::del(mixed $sock): bool
```

!> `socket`의 `close` 작동 전에 `Event::del`를 사용하여 사건 감시를 제거해야 하며, 그렇지 않으면 메모리 누수 발생할 수 있습니다

* **매개변수** 

  * **`mixed $sock`**
    * **기능** : `socket`의 파일 설명자
    * **기본값** : 없음
    * **기타 값** : 없음


### exit()

이벤트 포인트를 종료합니다.

!> 이 함수는 `Client` 프로그램에서만 유효합니다

```php
Swoole\Event::exit(): void
```


### defer()

다음 이벤트 루프 시작 시 실행할 함수를 정의합니다. 

```php
Swoole\Event::defer(mixed $callback_function);
```

!> `Event::defer`의 콜백 함수는 현재 `EventLoop`의 이벤트 루프가 종료되고 다음 이벤트 루프가 시작되기 전에 실행됩니다.

* **매개변수** 

  * **`mixed $callback_function`**
    * **기능** : 시간이 만료될 후에 실행할 함수 【대상은 호출 가능한 함수여야 합니다. 콜백 함수는 어떠한 매개변수도 받지 않으며, 익명 함수의 `use` 문법을 이용하여 콜백 함수에 매개변수를 전달할 수 있습니다; `$callback_function`이 실행 중에 새로운 `defer` 작업을 추가해도, 이번 이벤트 루프 내에서 실행이 완료됩니다】
    * **기본값** : 없음
    * **기타 값** : 없음

* **사용 예시**

```php
Swoole\Event::defer(function(){
    echo "After EventLoop\n";
});
```


### cycle()

이벤트 루프 주기 실행 함수를 정의합니다. 이 함수는 각 이벤트 루프가 종료될 때 한 번 호출됩니다. 

```php
Swoole\Event::cycle(callable $callback, bool $before = false): bool
```

* **매개변수** 

  * **`callable $callback_function`**
    * **기능** : 설정할 콜백 함수 【`$callback`가 `null`이면 `cycle` 함수를 제거하고, `cycle` 함수가 설정되어 있다면, 재설정 시 이전 설정을 덮어씌웁니다】
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`bool $before`**
    * **기능** : [EventLoop](/learn?id=什么是eventloop) 이전에 해당 함수를 호출합니다
    * **기본값** : 없음
    * **기타 값** : 없음

!> `before=true`와 `before=false` 두 개의 콜백 함수가 동시에 존재할 수 있습니다.

  * **사용 예시**

```php
Swoole\Timer::tick(2000, function ($id) {
    var_dump($id);
});

Swoole\Event::cycle(function () {
    echo "hello [1]\n";
    Swoole\Event::cycle(function () {
        echo "hello [2]\n";
        Swoole\Event::cycle(null);
    });
});
```


### wait()

이벤트 감시를 시작합니다.

!> 이 함수는 PHP 프로그램의 마지막에 위치시켜야 합니다

```php
Swoole\Event::wait();
```

* **사용 예시**

```php
Swoole\Timer::tick(1000, function () {
    echo "hello\n";
});

Swoole\Event::wait();
```

### dispatch()

이벤트 감시를 시작합니다.

!> `reactor->wait` 작동을 한 번만 수행하며, `Linux` 플랫폼에서는 마치 수동으로 `epoll_wait`를 한 번 호출하는 것과 같습니다. `Event::dispatch`와 달리, `Event::wait`는 하단에서 루프를 유지합니다.

```php
Swoole\Event::dispatch();
```

* **사용 예시**

```php
while(true)
{
    Event::dispatch();
}
```

이 함수의 목적은 일부 프레임워크와 호환성을 위해서입니다, 예를 들어 `amp`은 프레임워크 내부에서 자체적으로 `reactor`의 루프를 제어하고 있으며, `Event::wait`를 사용하면 Swoole 하단에서 제어를 유지하게 되어 프레임워크 측에 제어를 넘길 수 없습니다.
