# 코루틴/소켓

`Swoole\Coroutine\Socket` 모듈은 [코루틴 스타일 서버](/server/co_init)와 [코루틴 클라이언트](/coroutine_client/init) 관련 모듈 `Socket`에 비해 더 세밀한 `IO` 작업을 실행할 수 있습니다.

!> `Co\Socket` 짧은 이름을 사용하여 클래스명을 단순화할 수 있습니다. 이 모듈은 매우 저수준이기 때문에 사용자는 소켓 프로그래밍 경험이 필요합니다.


## 전체 예제

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9601);
    while ($retval)
    {
        $n = $socket->send('hello');
        var_dump($n);

        $data = $socket->recv();
        var_dump($data);

        //에러가 발생하거나 상대방이 연결을 종료하면 본측도 연결을 종료해야 합니다.
        if ($data === '' || $data === false) {
            echo "errCode: {$socket->errCode}\n";
            $socket->close();
            break;
        }

        Coroutine::sleep(1.0);
    }

    var_dump($retval, $socket->errCode, $socket->errMsg);
});
```


## 코루틴 스케줄링

`Coroutine\Socket` 모듈이 제공하는 `IO` 작업 인터페이스는 모두 동기 프로그래밍 스타일이며, 저수준에서 자동으로 [코루틴 스케줄러](/coroutine?id=코루틴 스케줄러)를 사용하여 [비동기 IO](/learn?id=동기io비동기io)를 실행합니다.


## 오류 코드

`socket` 관련 시스템 호출을 실행할 때 -1 오류가 반환될 수 있으며, 저수준에서 `Coroutine\Socket->errCode` 속성을 시스템 오류 번호 `errno`로 설정합니다. 자세한 내용은 해당 `man` 문서를 참고하세요. 예를 들어 `$socket->accept()`가 오류를 반환할 때, `errCode`의 의미는 `man accept`에서 나와 있는 오류 코드 문서를 참고하세요.


## 속성


### fd

`socket`에 해당하는 파일 디스크립터 `ID`


### errCode

오류 코드


## 메서드


### __construct()

생성자입니다. `Coroutine\Socket` 객체를 생성합니다.

```php
Swoole\Coroutine\Socket::__construct(int $domain, int $type, int $protocol);
```

!> 자세한 내용은 `man socket` 문서를 참고하세요.

  * **매개변수** 

    * **`int $domain`**
      * **기능**：プロト콜 도메인【`AF_INET`、`AF_INET6`、`AF_UNIX`를 사용할 수 있습니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`int $type`**
      * **기능**：유형【`SOCK_STREAM`、`SOCK_DGRAM`、`SOCK_RAW`를 사용할 수 있습니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`int $protocol`**
      * **기능**：프로토콜【`IPPROTO_TCP`、`IPPROTO_UDP`、`IPPROTO_STCP`、`IPPROTO_TIPC`、`0`를 사용할 수 있습니다】
      * **기본값**：없음
      * **기타 값**：없음

!> 생성자는 `socket` 시스템 호출을 호출하여 `socket` 핸들을 생성합니다. 호출이 실패하면 `Swoole\Coroutine\Socket\Exception` 예외를 던집니다. 그리고 `$socket->errCode` 속성을 설정합니다. 이 속성의 값을 통해 시스템 호출이 실패한 이유를 얻을 수 있습니다.


### getOption()

구성 옵션을 가져옵니다.

!> 이 방법은 `getsockopt` 시스템 호출에 해당하며, 자세한 내용은 `man getsockopt` 문서를 참고하세요.  
이 방법은 `sockets` 확장의 `socket_get_option` 기능과 동일하며, 자세한 내용은 [PHP 문서](https://www.php.net/manual/zh/function.socket-get-option.php)를 참고하세요.

!> Swoole 버전 >= v4.3.2

```php
Swoole\Coroutine\Socket->getOption(int $level, int $optname): mixed
```

  * **매개변수** 

    * **`int $level`**
      * **기능**：옵션이 있는 프로토콜 수준을 지정합니다
      * **기본값**：없음
      * **기타 값**：없음

      !> 예를 들어, 소켓 수준에서 옵션을检索하려면 `SOL_SOCKET`의 `level` 매개변수를 사용할 것입니다.  
      다른 수준을 사용하려면 해당 수준의 프로토콜 번호를 지정할 수 있습니다. 예를 들어 `TCP`입니다. [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php) 함수를 사용하여 프로토콜 번호를 찾을 수 있습니다.

    * **`int $optname`**
      * **기능**：사용 가능한 소켓 옵션은 [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php) 함수의 소켓 옵션과 동일합니다
      * **기본값**：없음
      * **기타 값**：없음


### setOption()

구성 옵션을 설정합니다.

!> 이 방법은 `setsockopt` 시스템 호출에 해당하며, 자세한 내용은 `man setsockopt` 문서를 참고하세요. 이 방법은 `sockets` 확장의 `socket_set_option` 기능과 동일하며, 자세한 내용은 [PHP 문서](https://www.php.net/manual/zh/function.socket-set-option.php)를 참고하세요.

!> Swoole 버전 >= v4.3.2

```php
Swoole\Coroutine\Socket->setOption(int $level, int $optname, mixed $optval): bool
```

  * **매개변수** 

    * **`int $level`**
      * **기능**：옵션이 있는 프로토콜 수준을 지정합니다
      * **기본값**：없음
      * **기타 값**：없음

      !> 예를 들어, 소켓 수준에서 옵션을 설정하려면 `SOL_SOCKET`의 `level` 매개변수를 사용할 것입니다.  
      다른 수준을 사용하려면 해당 수준의 프로토콜 번호를 지정할 수 있습니다. 예를 들어 `TCP`입니다. [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php) 함수를 사용하여 프로토콜 번호를 찾을 수 있습니다.

    * **`int $optname`**
      * **기능**：사용 가능한 소켓 옵션은 [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php) 함수의 소켓 옵션과 동일합니다
      * **기본값**：없음
      * **기타 값**：없음

    * **`int $optval`**
      * **기능**：옵션의 값 【`int`、`bool`、`string`、`array`일 수 있습니다. `level`와 `optname`에 따라 결정됩니다.】
      * **기본값**：없음
      * **기타 값**：없음


### setProtocol()

`socket`이 프로토콜 처리 능력을 갖추게 하여, `SSL` 암호화 전송을 사용할 수 있도록 설정하고 [TCP 패킷 경계 문제](/learn?id=tcp패킷경계문제)를 해결할 수 있습니다.

!> Swoole 버전 >= v4.3.2

```php
Swoole\Coroutine\Socket->setProtocol(array $settings): bool
```

  * **$settings 지원하는 매개변수**


매개변수 | 타입
---|---
open_ssl | bool
ssl_cert_file | string
ssl_key_file | string
open_eof_check | bool
open_eof_split | bool
open_mqtt_protocol | bool
open_fastcgi_protocol | bool
open_length_check | bool
package_eof | string
package_length_type | string
package_length_offset | int
package_body_offset | int
package_length_func | callable
package_max_length | int

!> 위의 모든 매개변수의 의미는 [Server->set()](/server/setting?id=open_eof_check)와 완전히 동일하며, 여기서 다시 설명하지 않겠습니다.

  * **예제**

```php
$socket->setProtocol([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,
    'package_body_offset'   => 4,
]);
```


### bind()

주소와 포트를 바인딩합니다.

!> 이 방법은 `IO` 작업이 없으므로 코루틴 전환을 일으키지 않습니다.

```php
Swoole\Coroutine\Socket->bind(string $address, int $port = 0): bool
```

  * **매개변수** 

    * **`string $address`**
      * **기능**：바인딩할 주소【예: `0.0.0.0`、`127.0.0.1`】
      * **기본값**：없음
      * **기타 값**：없음

    * **`int $port`**
      * **기능**：：바인딩할 포트【기본적으로 `0`이며, 시스템은 사용 가능한 포트를 무작위로 바인딩합니다. [getsockname](/coroutine_client/socket?id=getsockname) 방법을 사용하여 시스템이 할당한 `port`를 얻을 수 있습니다】
      * **기본값**：`0`
      * **기타 값**：없음

  * **반환값** 

    * 바인딩 성공 시 `true`를 반환합니다.
    * 바인딩 실패 시 `false`를 반환하며, `errCode` 속성을 확인하여 실패 이유를 얻을 수 있습니다.
### listen()

`Socket`를 감시합니다.

!> 이 방법은 `IO` 운영이 없으므로 코루틴 전환을 일으키지 않습니다.

```php
Swoole\Coroutine\Socket->listen(int $backlog = 0): bool
```

  * **매개변수** 

    * **`int $backlog`**
      * **기능** : 대기열의 길이를 감시합니다.【기본값은 `0`이며, 시스템은 기본적으로 `epoll`을 사용하여 비동기 `IO`를 구현하고 있어, блок이 발생하지 않으므로 `backlog`의 중요성이 높지 않습니다】
      * **기본값** : `0`
      * **기타 값** : 없음

      !> 응용에서 블록이나 시간 소모적인 논리가 존재하고, `accept`이 연결을 즉시 받아들이지 못하면, 새로 생성된 연결은 `backlog` 대기열에 쌓이고, `backlog`의 길이를 초과하면 서비스는 새로운 연결을 거부합니다.

  * **귀속값** 

    * 성공 시 `true`를 반환합니다.
    * 실패 시 `false`를 반환하며, `errCode` 속성을 통해 실패 이유를 확인할 수 있습니다.

  * **内核 매개변수** 

    `backlog`의 최대값은内核 매개변수 `net.core.somaxconn`에 의해 제한되며, `Linux`에서는 `sysctl` 도구를 사용하여 모든 `kernel` 매개변수를 동적으로 조정할 수 있습니다. 동적 조정은 `kernel` 매개변수 값이 변경된 즉시 적용됩니다. 그러나 이 적용은 `OS` 수준에서만 유효하며, 응용을 재시작해야만 진정으로 적용됩니다. 명령어 `sysctl -a`는 모든内核 매개변수와 그 값을 표시합니다.

    ```shell
    sysctl -w net.core.somaxconn=2048
    ```

    위의 명령어는内核 매개변수 `net.core.somaxconn`의 값을 `2048`로 변경합니다. 이러한 변경은 즉시 적용될 수 있지만, 기계가 재시작되면 기본값으로 복구됩니다. 변경을 영구히 유지하려면 `/etc/sysctl.conf`를 수정하여 `net.core.somaxconn=2048`를 추가하고 명령어 `sysctl -p`를 실행하여 적용해야 합니다.


### accept()

클라이언트에서 시작된 연결을 받아들입니다.

이 방법을 호출하면 현재 코루틴이 즉시 정지하고 [EventLoop](/learn?id=什么是eventloop)에 의해 읽기 가능 이벤트를 감시하게 됩니다. `Socket`이 읽기에 준비되어 새로운 연결이 도착하면 해당 코루틴이 자동으로 깨어나고, 해당 클라이언트 연결의 `Socket` 객체를 반환합니다.

!> 이 방법은 `listen` 방법을 사용한 후에 사용해야 하며, `Server` 측에서 사용할 수 있습니다.

```php
Swoole\Coroutine\Socket->accept(float $timeout = 0): Coroutine\Socket|false;
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능** :超时 시간을 설정합니다.【超时 매개변수를 설정하면, 하단에서 타이머를 설정하고, 지정한 시간 내에 클라이언트 연결이 도착하지 않으면 `accept` 방법이 `false`를 반환합니다】
      * **값의 단위** : 초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500밀리초`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값** 

    *超时 또는 `accept` 시스템 호출이 오류를 발생시켰을 때 `false`를 반환하며, `errCode` 속성을 통해 오류 코드를 확인할 수 있습니다. 여기서超时 오류 코드는 `ETIMEDOUT`입니다.
    * 성공 시 클라이언트 연결의 `socket`을 반환하며, 유형은 동일하게 `Swoole\Coroutine\Socket` 객체입니다. 이를 통해 `send`, `recv`, `close` 등의 작업을 수행할 수 있습니다.

  * **예시**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
$socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
$socket->bind('127.0.0.1', 9601);
$socket->listen(128);

    while(true) {
        echo "Accept: \n";
        $client = $socket->accept();
        if ($client === false) {
            var_dump($socket->errCode);
        } else {
            var_dump($client);
        }
    }
});
```


### connect()

목표 서버에 연결합니다.

이 방법을 호출하면 비동기적 `connect` 시스템 호출을 시작하고 현재 코루틴을 정지시킵니다. 하단은 쓰기에 준비되어 있으며, 연결이 완료되거나 실패하면 해당 코루틴을 복구합니다.

이 방법은 `Client` 측에서 사용하며, `IPv4`, `IPv6`, [unixSocket](/learn?id=什么是IPC)를 지원합니다.

```php
Swoole\Coroutine\Socket->connect(string $host, int $port = 0, float $timeout = 0): bool
```

  * **매개변수** 

    * **`string $host`**
      * **기능** : 대상 서버의 주소【`127.0.0.1`, `192.168.1.100`, `/tmp/php-fpm.sock`, `www.baidu.com` 등, IP 주소, Unix Socket 경로 또는 도메인을 전달할 수 있습니다. 도메인이 전달되면 하단은 자동으로 비동기적 DNS 해제를 수행하여 블록이 발생하지 않습니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`int $port`**
      * **기능** : 대상 서버 포트【`Socket`의 `domain`이 `AF_INET` 또는 `AF_INET6`일 경우 반드시 포트를 설정해야 합니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** : 연결超时 시간을 설정합니다.【하단에서 타이머를 설정하고, 지정한 시간 내에 연결이 구축되지 못하면 `connect`는 `false`를 반환합니다】
      * **값의 단위** : 초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500밀리초`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값** 

    *超时 또는 `connect` 시스템 호출이 오류를 발생시켰을 때 `false`를 반환하며, `errCode` 속성을 통해 오류 코드를 확인할 수 있습니다. 여기서超时 오류 코드는 `ETIMEDOUT`입니다.
    * 성공 시 `true`를 반환합니다.


### checkLiveness()

해당 연결이 살아있는지 확인하기 위해 시스템 호출을 합니다.(예외적으로 중단될 경우에는 작동하지 않으며, 상대방이 정상적으로 close한 연결의 끊임을 감지할 수 있습니다)

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\Socket->checkLiveness(): bool
```

  * **귀속값** 

    * 연결이 살아있는 경우 `true`를 반환하고, 그렇지 않은 경우 `false`를 반환합니다.


### send()

대상을 향해 데이터를 보냅니다.

!> `send` 방법은 즉시 `send` 시스템 호출을 수행하여 데이터를 보냅니다. `send` 시스템 호출이 오류 `EAGAIN`을 반환할 경우, 하단은 자동으로 쓰기에 준비되어 있으며, 현재 코루틴을 정지시킵니다. 쓰기 가능 이벤트가 발생할 때, 다시 `send` 시스템 호출을 수행하여 데이터를 보냅니다. 그리고 해당 코루틴을 깨웁니다.  

!> 만약 `send`이 너무 빠르고 `recv`이 느리면 최종적으로 운영체의 버퍼가 가득 차게 되고, 현재 코루틴은 `send` 방법에 매달려 있습니다. 버퍼의 크기를 적절히 늘릴 수 있습니다.,[/proc/sys/net/core/wmem_max와 SO_SNDBUF](https://stackoverflow.com/questions/21856517/whats-the-practical-limit-on-the-size-of-single-packet-transmitted-over-domain)

```php
Swoole\Coroutine\Socket->send(string $data, float $timeout = 0): int|false
```

  * **매개변수** 

    * **`string $data`**
      * **기능** : 보낼 데이터 내용입니다.【텍스트 또는 이진 데이터가 될 수 있습니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** :超时 시간을 설정합니다.
      * **값의 단위** : 초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500밀리초`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값** 

    * 성공 시 보낸 바이트 수를 반환합니다. **실제로 보낸 데이터의 길이는 `$data` 매개변수의 길이와 동일하지 않을 수 있습니다**. 응용 계층의 코드는 귀속값과 `strlen($data)`를 비교하여 보낸 작업이 완료되었는지 확인해야 합니다.
    * 실패 시 `false`를 반환하고, `errCode` 속성을 설정합니다.
### sendAll()

대상을 향해 데이터를 전송합니다. `send` 방법과 달리, `sendAll`은 가능한 한 완전한 데이터를 전송하려고 노력하며, 모든 데이터가 성공적으로 전송되거나 오류로 인해 중단될 때까지 계속됩니다.

!> `sendAll` 방법은 즉시 여러 번의 `send` 시스템 호출을 수행하여 데이터를 전송하고, `send` 시스템 호출이 `EAGAIN` 오류를 반환할 경우, 하단은 자동으로 쓰기 가능 이벤트를 감지하고 현재 코루틴을 일시적으로 중지시켜, 쓰기 가능 이벤트가 발생할 때까지 기다립니다. 이후 `send` 시스템 호출을 다시 수행하여 데이터를 전송하고, 데이터 전송이 완료되거나 오류가 발생할 때까지 계속됩니다. 해당 코루틴이 깨어나게 됩니다.  

!> Swoole 버전 >= v4.3.0

```php
Swoole\Coroutine\Socket->sendAll(string $data, float $timeout = 0) : int | false;
```

  * **매개변수** 

    * **`string $data`**
      * **기능** : 전송할 데이터 내용 【텍스트 또는 이진 데이터가 될 수 있습니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** :超时 시간 설정
      * **값 단위** : 초 【소수형을 지원합니다. 예: `1.5`는 `1초`+`500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값** 

    * `sendAll`은 데이터가 모두 성공적으로 전송될 것을 보장하지만, `sendAll`이 진행 중에 대상이 연결을 끊을 수 있습니다. 이때 일부 데이터가 성공적으로 전송될 수 있으며, 귀속값은 성공적으로 전송된 데이터의 길이를 반환합니다. 응용 레이어 코드는 귀속값과 `strlen($data)`가 동일한지 비교하여 전송이 완료되었는지 확인해야 하며, 비즈니스 요구에 따라 연속 전송이 필요한지 결정해야 합니다.
    * 전송 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.


### peek()

독자 버퍼의 데이터를 엿보기, 시스템 호출 중의 `recv(length, MSG_PEEK)`과 같습니다.

!> `peek`는 즉시 완료되며, 코루틴을 일시적으로 중지하지 않지만, 한 번의 시스템 호출 비용이 발생합니다.

```php
Swoole\Coroutine\Socket->peek(int $length = 65535): string|false
```

  * **매개변수** 

    * **`int $length`**
      * **기능** : 복사할 엿보기 데이터의 메모리 크기를 지정합니다 (주의: 여기에서 메모리를 할당하므로, 너무 큰 길이로 인해 메모리가 고갈될 수 있습니다)
      * **값 단위** : 바이트
      * **기본값** : 없음
      * **기타 값** : 없음

  * **귀속값** 

    * 엿보기 성공 시 데이터를 반환합니다.
    * 엿보기 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.


### recv()

데이터를 수신합니다.

!> `recv` 방법은 현재 코루틴을 즉시 중지하고 읽기 가능 이벤트를 감지하며, 대상이 데이터를 보낸 후에 읽기 가능 이벤트가 발생할 때까지 기다립니다. 이후 `recv` 시스템 호출을 수행하여 `socket` 버퍼의 데이터를 획득하고 해당 코루틴을 깨웁니다.

```php
Swoole\Coroutine\Socket->recv(int $length = 65535, float $timeout = 0): string|false
```

  * **매개변수** 

    * **`int $length`**
      * **기능** : 수신할 데이터의 메모리 크기를 지정합니다 (주의: 여기에서 메모리를 할당하므로, 너무 큰 길이로 인해 메모리가 고갈될 수 있습니다)
      * **값 단위** : 바이트
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** :超时 시간 설정
      * **값 단위** : 초 【소수형을 지원합니다. 예: `1.5`는 `1초`+`500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값** 

    * 수신 성공 시 실제 데이터를 반환합니다.
    * 수신 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.
    * 수신超时, 오류 코드는 `ETIMEDOUT`입니다.

!> 귀속값이 예상 길이와 반드시 일치하지 않을 수 있으며, 해당 호출에서 수신된 데이터의 길이를 직접 확인해야 합니다. 한 번의 호출에서 지정된 길이의 데이터를 확실히 얻으려면 `recvAll` 방법을 사용하거나 직접 루프를 통해 얻어야 합니다.  
TCP 패킷 경계 문제는 `setProtocol()` 방법을 참고하거나 `sendto()`를 사용하세요;


### recvAll()

데이터를 수신합니다. `recv`와 달리, `recvAll`은 가능한 한 완전한 응답 길이의 데이터를 수신하려고 노력하며, 수신이 완료되거나 오류로 인해 실패할 때까지 계속됩니다.

!> `recvAll` 방법은 현재 코루틴을 즉시 중지하고 읽기 가능 이벤트를 감지하며, 대상이 데이터를 보낸 후에 읽기 가능 이벤트가 발생할 때까지 기다립니다. 이후 `recv` 시스템 호출을 수행하여 `socket` 버퍼의 데이터를 획득하고 해당 코루틴을 깨웁니다. 이 행위를 반복하여 지정된 길이의 데이터를 수신하거나 오류로 인해 중단될 때까지 계속됩니다.

!> Swoole 버전 >= v4.3.0

```php
Swoole\Coroutine\Socket->recvAll(int $length = 65535, float $timeout = 0): string|false
```

  * **매개변수** 

    * **`int $length`**
      * **기능** : 기대되는 수신 데이터의 크기 (주의: 여기에서 메모리를 할당하므로, 너무 큰 길이로 인해 메모리가 고갈될 수 있습니다)
      * **값 단위** : 바이트
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** :超时 시간 설정
      * **값 단위** : 초 【소수형을 지원합니다. 예: `1.5`는 `1초`+`500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값** 

    * 수신 성공 시 실제 데이터를 반환하며, 반환된 문자열의 길이가 매개변수 길이와 일치합니다.
    * 수신 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.
    * 수신超时, 오류 코드는 `ETIMEDOUT`입니다.


### readVector()

데이터를 분단적으로 수신합니다.

!> `readVector` 방법은 즉시 `readv` 시스템 호출을 수행하여 데이터를 읽습니다. `readv` 시스템 호출이 `EAGAIN` 오류를 반환할 경우, 하단은 자동으로 읽기 가능 이벤트를 감지하고 현재 코루틴을 일시적으로 중지시켜, 읽기 가능 이벤트가 발생할 때까지 기다립니다. 이후 `readv` 시스템 호출을 다시 수행하여 데이터를 읽고 해당 코루틴을 깨웁니다.  

!> Swoole 버전 >= v4.5.7

```php
Swoole\Coroutine\Socket->readVector(array $io_vector, float $timeout = 0): array|false
```

  * **매개변수** 

    * **`array $io_vector`**
      * **기능** : 기대되는 분단 데이터의 크기
      * **값 단위** : 바이트
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** :超时 시간 설정
      * **값 단위** : 초 【소수형을 지원합니다. 예: `1.5`는 `1초`+`500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값**

    * 수신 성공 시 분단적으로 수신된 데이터를 반환합니다.
    * 수신 실패 시 빈 배열을 반환하고 `errCode` 속성을 설정합니다.
    * 수신超时, 오류 코드는 `ETIMEDOUT`입니다.

  * **예시** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 대상이 helloworld를 보냈다면
$ret = $socket->readVector([5, 5]);
// 그렇다면, $ret는 ['hello', 'world']입니다
```


### readVectorAll()

데이터를 분단적으로 수신합니다.

!> `readVectorAll` 방법은 즉시 여러 번의 `readv` 시스템 호출을 수행하여 데이터를 읽습니다. `readv` 시스템 호출이 `EAGAIN` 오류를 반환할 경우, 하단은 자동으로 읽기 가능 이벤트를 감지하고 현재 코루틴을 일시적으로 중지시켜, 읽기 가능 이벤트가 발생할 때까지 기다립니다. 이후 `readv` 시스템 호출을 다시 수행하여 데이터를 읽고 해당 코루틴을 깨웁니다. 이 행위를 반복하여 데이터读取가 완료되거나 오류가 발생할 때까지 계속됩니다.

!> Swoole 버전 >= v4.5.7

```php
Swoole\Coroutine\Socket->readVectorAll(array $io_vector, float $timeout = 0): array|false
```

  * **매개변수** 

    * **`array $io_vector`**
      * **기능** : 기대되는 분단 데이터의 크기
      * **값 단위** : 바이트
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** :超时 시간 설정
      * **값 단위** : 초 【소수형을 지원합니다. 예: `1.5`는 `1초`+`500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **귀속값**

    * 수신 성공 시 분단적으로 수신된 데이터를 반환합니다.
    * 수신 실패 시 빈 배열을 반환하고 `errCode` 속성을 설정합니다.
    * 수신超时, 오류 코드는 `ETIMEDOUT`입니다.
### writeVector()

데이터를 분절하여 전송합니다.

!> `writeVector` 방법은 즉시 `writev` 시스템 호출을 실행하여 데이터를 전송하고, `writev` 시스템 호출이 오류 `EAGAIN`을 반환할 경우, 기본적으로는 쓰기 가능 이벤트를 감지하고 현재 코루틴을 일시적으로 중지시켜, 쓰기 가능 이벤트가 발생할 때까지 기다립니다. 이후 `writev` 시스템 호출을 재실행하여 데이터를 전송하고 해당 코루틴을 깨웁니다.  

!> Swoole 버전 >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVector(array $io_vector, float $timeout = 0): int|false
```

  * **매개변수** 

    * **`array $io_vector`**
      * **기능** : 전송할 분절된 데이터
      * **값의 단위** : 바이트
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** : 超时 시간 설정
      * **값의 단위** : 초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **반환값**

    * 전송 성공 시 반환되는 바이트 수는 실제 작성된 데이터가 `$io_vector` 매개변수의 총 길이보다 짧을 수 있으므로, 응용 계층 코드는 반환값과 `$io_vector` 매개변수의 총 길이를 비교하여 전송이 완료되었는지 여부를 판단해야 합니다.
    * 전송 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.

  * **예시** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 이때는 배열 안의 순서대로 상대방에게 전송하며, 실제로는 helloworld를 전송합니다
$socket->writeVector(['hello', 'world']);
```


### writeVectorAll()

상대방에게 데이터를 전송합니다. `writeVector` 방법과 달리, `writeVectorAll`은 가능한 한 완전한 데이터를 전송하려고 노력하며, 모든 데이터가 성공적으로 전송되거나 오류로 인해 중단될 때까지 계속됩니다.

!> `writeVectorAll` 방법은 즉시 여러 번의 `writev` 시스템 호출을 실행하여 데이터를 전송하고, `writev` 시스템 호출이 오류 `EAGAIN`을 반환할 경우, 기본적으로는 쓰기 가능 이벤트를 감지하고 현재 코루틴을 일시적으로 중지시켜, 쓰기 가능 이벤트가 발생할 때까지 기다립니다. 이후 `writev` 시스템 호출을 재실행하여 데이터를 전송하고, 데이터 전송이 완료되거나 오류가 발생할 때까지 계속됩니다.

!> Swoole 버전 >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVectorAll(array $io_vector, float $timeout = 0): int|false
```

  * **매개변수** 

    * **`array $io_vector`**
      * **기능** : 전송할 분절된 데이터
      * **값의 단위** : 바이트
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** : 超时 시간 설정
      * **값의 단위** : 초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **반환값**

    * `writeVectorAll`은 데이터가 모두 성공적으로 전송될 것을 보장하지만, `writeVectorAll`이 진행 중에 상대방이 연결을 끊을 수 있습니다. 이때는 일부 데이터가 성공적으로 전송될 수 있으며, 반환값은 성공적으로 전송된 데이터의 길이를 나타냅니다. 응용 계층 코드는 반환값과 `$io_vector` 매개변수의 총 길이를 비교하여 전송이 완료되었는지 여부를 판단해야 합니다. 그리고 비즈니스 요구에 따라 재전송이 필요한지 여부를 결정해야 합니다.
    * 전송 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.

  * **예시** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 이때는 배열 안의 순서대로 상대방에게 전송하며, 실제로는 helloworld를 전송합니다
$socket->writeVectorAll(['hello', 'world']);
```


### recvPacket()

`setProtocol` 방법을 통해 프로토콜을 설정한 Socket 객체에 대해, 완전한 프로토콜 패킷을 수신할 수 있는 방법입니다.

!> Swoole 버전 >= v4.4.0

```php
Swoole\Coroutine\Socket->recvPacket(float $timeout = 0): string|false
```

  * **매개변수** 
    * **`float $timeout`**
      * **기능** : 수신超时 시간 설정
      * **값의 단위** : 초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`를 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

  * **반환값** 

    * 수신 성공 시 완전한 프로토콜 패킷을 반환합니다.
    * 수신 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.
    * 수신超时 시 오류코드가 `ETIMEDOUT`가 됩니다.


### recvLine()

[socket_read](https://www.php.net/manual/en/function.socket-read.php) 호환성 문제를 해결하기 위한 방법입니다.

```php
Swoole\Coroutine\Socket->recvLine(int $length = 65535, float $timeout = 0): string|false
```


### recvWithBuffer()

`recv(1)`를 사용하여 한 바이트씩 수신할 때 발생하는 많은 시스템 호출 문제를 해결하기 위한 방법입니다.

```php
Swoole\Coroutine\Socket->recvWithBuffer(int $length = 65535, float $timeout = 0): string|false
```


### recvfrom()

데이터를 수신하고, 발신원 호스트의 주소와 포트를 설정합니다. `SOCK_DGRAM` 유형의 `socket`에 사용됩니다.

!> 이 방법은 [코루틴 스케줄링](/coroutine?id=协程调度)을 유발하며, 기본적으로는 현재 코루틴을 즉시 중지시키고 읽기 가능 이벤트를 감지합니다. 읽기 가능 이벤트가 발생하면 데이터를 수신하고 `recvfrom` 시스템 호출을 실행하여 패킷을 획득합니다.

```php
Swoole\Coroutine\Socket->recvfrom(array &$peer, float $timeout = 0): string|false
```

* **매개변수**

    * **`array $peer`**
        * **기능** : 상대방 주소와 포트, 참조 타입입니다.【함수가 성공적으로 반환될 경우 배열로 설정되며, `address`와 `port` 두 가지 요소를 포함합니다】
        * **기본값** : 없음
        * **기타 값** : 없음

    * **`float $timeout`**
        * **기능** : 수신超时 시간 설정【정해진 시간 내에 데이터가 반환되지 않으면 `recvfrom` 방법은 `false`를 반환합니다】
        * **값의 단위** : 초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`를 나타냅니다】
        * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
        * **기타 값** : 없음

* **반환값**

    * 성공적으로 데이터를 수신하면 데이터 내용을 반환하고 `$peer`을 배열로 설정합니다.
    * 실패하면 `false`를 반환하고 `errCode` 속성을 설정하며, `$peer` 내용은 변경되지 않습니다.

* **예시**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    while (true) {
        $peer = null;
        $data = $socket->recvfrom($peer);
        echo "[Server] recvfrom[{$peer['address']}:{$peer['port']}] : $data\n";
        $socket->sendto($peer['address'], $peer['port'], "Swoole: $data");
    }
});
```


### sendto()

특정 주소와 포트에 데이터를 전송합니다. `SOCK_DGRAM` 유형의 `socket`에 사용됩니다.

!> 이 방법은 [코루틴 스케줄링](/coroutine?id=协程调度)이 되지 않으며, 기본적으로는 즉시 대상 호스트에 `sendto`를 호출하여 데이터를 전송합니다. 이 방법은 쓰기 가능 이벤트를 감지하지 않으므로, `sendto`가 캐시 영역이 가득 차서 `false`를 반환할 수 있으며, 이를 처리하거나 `send` 방법을 사용해야 합니다.

```php
Swoole\Coroutine\Socket->sendto(string $address, int $port, string $data): int|false
```

  * **매개변수** 

    * **`string $address`**
      * **기능** : 대상 호스트의 `IP` 주소 또는 [unixSocket](/learn?id=什么是IPC) 경로【`sendto`는 도메인을 지원하지 않으며, `AF_INET` 또는 `AF_INET6`을 사용할 때는 유효한 `IP` 주소를 전달해야 하며, 그렇지 않으면 전송이 실패합니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`int $port`**
      * **기능** : 대상 호스트의 포트【브로드캐스트를 전송할 때는 `0`을 사용할 수 있습니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`string $data`**
      * **기능** : 전송할 데이터【텍스트 또는 이진 콘텐츠일 수 있으며, 주의해야 할 점은 `SOCK_DGRAM` 전송 패킷의 최대 길이가 `64K`인 것입니다】
      * **기본값** : 없음
      * **기타 값** : 없음

  * **반환값** 

    * 성공적으로 전송 시 전송된 바이트 수를 반환합니다.
    * 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.

  * **예시** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
$socket->sendto('127.0.0.1', 9601, 'Hello');
```
### getsockname()

소켓의 주소와 포트 정보를 가져옵니다.

!> 이 방법은 [코루outine 스케줄러](/coroutine?id=코루outine%EC%97%90%EA%B0%80)에 의한 부하가 없습니다.

```php
Swoole\Coroutine\Socket->getsockname(): array|false
```

  * **반환값** 

    * 성공 시 `address`와 `port`이 포함된 배열을 반환합니다.
    * 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.


### getpeername()

`socket`의 상대방 주소와 포트 정보를 가져옵니다. 이는 `SOCK_STREAM` 유형의 연결이 있는 `socket`에만 사용됩니다.

?> 이 방법은 [코루outine 스케줄러](/coroutine?id=코루outine%EC%97%90%EA%B0%80)에 의한 부하가 없습니다.

```php
Swoole\Coroutine\Socket->getpeername(): array|false
```

  * **반환값** 

    * 성공 시 `address`와 `port`이 포함된 배열을 반환합니다.
    * 실패 시 `false`를 반환하고 `errCode` 속성을 설정합니다.


### close()

`Socket`를 닫습니다.

!> `Swoole\Coroutine\Socket` 객체가 소멸될 때 자동으로 `close`를 실행한다면, 이 방법은 [코루outine 스케줄러](/coroutine?id=코루outine%EC%97%90%EA%B0%80)에 의한 부하가 없습니다.

```php
Swoole\Coroutine\Socket->close(): bool
```

  * **반환값** 

    * 닫기 성공 시 `true`를 반환합니다.
    * 실패 시 `false`를 반환합니다.
    

### isClosed()

`Socket`가 이미 닫혔는지를 확인합니다.

```php
Swoole\Coroutine\Socket->isClosed(): bool
```

## 상수

`sockets` 확장 제공하는 상수와 동일하며, `sockets` 확장과 충돌하지 않습니다.

!> 다른 시스템에서 값이 다를 수 있습니다. 다음 코드는 예시일 뿐, 실제 사용하지 말아 주십시오.

```php
define ('AF_UNIX', 1);
define ('AF_INET', 2);

/**
 * IPv6 지원이编译된 경우에만 사용할 수 있습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('AF_INET6', 10);
define ('SOCK_STREAM', 1);
define ('SOCK_DGRAM', 2);
define ('SOCK_RAW', 3);
define ('SOCK_SEQPACKET', 5);
define ('SOCK_RDM', 4);
define ('MSG_OOB', 1);
define ('MSG_WAITALL', 256);
define ('MSG_CTRUNC', 8);
define ('MSG_TRUNC', 32);
define ('MSG_PEEK', 2);
define ('MSG_DONTROUTE', 4);

/**
 * Windows 플랫폼에서는 사용할 수 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOR', 128);

/**
 * Windows 플랫폼에서는 사용할 수 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOF', 512);
define ('MSG_CONFIRM', 2048);
define ('MSG_ERRQUEUE', 8192);
define ('MSG_NOSIGNAL', 16384);
define ('MSG_DONTWAIT', 64);
define ('MSG_MORE', 32768);
define ('MSG_WAITFORONE', 65536);
define ('MSG_CMSG_CLOEXEC', 1073741824);
define ('SO_DEBUG', 1);
define ('SO_REUSEADDR', 2);

/**
 * PHP 5.4.10 이후의 플랫폼에서만 사용할 수 있는 상수로, <b>SO_REUSEPORT</b> 소켓 옵션을 지원합니다: 이는 Mac OS X와 FreeBSD를 포함하지만, Linux이나 Windows는 포함하지 않습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SO_REUSEPORT', 15);
define ('SO_KEEPALIVE', 9);
define ('SO_DONTROUTE', 5);
define ('SO_LINGER', 13);
define ('SO_BROADCAST', 6);
define ('SO_OOBINLINE', 10);
define ('SO_SNDBUF', 7);
define ('SO_RCVBUF', 8);
define ('SO_SNDLOWAT', 19);
define ('SO_RCVLOWAT', 18);
define ('SO_SNDTIMEO', 21);
define ('SO_RCVTIMEO', 20);
define ('SO_TYPE', 3);
define ('SO_ERROR', 4);
define ('SO_BINDTODEVICE', 25);
define ('SOL_SOCKET', 1);
define ('SOMAXCONN', 128);

/**
 * Nagle TCP 알고리즘을 비활성화하는 데 사용됩니다.
 * PHP 5.2.7에서 추가되었습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('TCP_NODELAY', 1);
define ('PHP_NORMAL_READ', 1);
define ('PHP_BINARY_READ', 2);
define ('MCAST_JOIN_GROUP', 42);
define ('MCAST_LEAVE_GROUP', 45);
define ('MCAST_BLOCK_SOURCE', 43);
define ('MCAST_UNBLOCK_SOURCE', 44);
define ('MCAST_JOIN_SOURCE_GROUP', 46);
define ('MCAST_LEAVE_SOURCE_GROUP', 47);
define ('IP_MULTICAST_IF', 32);
define ('IP_MULTICAST_TTL', 33);
define ('IP_MULTICAST_LOOP', 34);
define ('IPV6_MULTICAST_IF', 17);
define ('IPV6_MULTICAST_HOPS', 18);
define ('IPV6_MULTICAST_LOOP', 19);
define ('IPV6_V6ONLY', 27);

/**
 * 권한이 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPERM', 1);

/**
 * 파일이나 디렉터리가 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOENT', 2);

/**
 * 시스템 호출이 중단되었습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINTR', 4);

/**
 * I/O 오류입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIO', 5);

/**
 * 장치나 주소가 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENXIO', 6);

/**
 * 매개변수 리스트가 너무 길습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_E2BIG', 7);

/**
 * 잘못된 파일 번호입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADF', 9);

/**
 * 다시 시도하십시오.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAGAIN', 11);

/**
 * 메모리가 부족합니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEM', 12);

/**
 * 권한이 거부되었습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EACCES', 13);

/**
 * 잘못된 주소입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EFAULT', 14);

/**
 * 블록 장치가 필요합니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTBLK', 15);

/**
 * 장치나 자원이 바쁩니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBUSY', 16);

/**
 * 파일이 존재합니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EEXIST', 17);

/**
 * 장치 간의 링크입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXDEV', 18);

/**
 * 장치가 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODEV', 19);

/**
 * 디렉터리가 아닙니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTDIR', 20);

/**
 * 디렉터리입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISDIR', 21);

/**
 * 잘못된 매개변수입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINVAL', 22);

/**
 * 파일 테이블이 오버플로우합니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENFILE', 23);

/**
 * 열린 파일이 너무 많습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMFILE', 24);

/**
 * 타입WRITER가 아닙니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTTY', 25);

/**
 * 장치에 공간이 부족합니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSPC', 28);

/**
 * 불법의 seek입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESPIPE', 29);

/**
 * 읽기 전용 파일 시스템입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EROFS', 30);

/**
 * 링크가 너무 많습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMLINK', 31);

/**
 * 파이프가 깨졌습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPIPE', 32);

/**
 * 파일 이름이 너무 길습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENAMETOOLONG', 36);

/**
 * 원하는 유형의 메시지가 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLCK', 37);

/**
 * 구현되지 않은 기능입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSYS', 38);

/**
 * 디렉터리가 비어 있지 않습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTEMPTY', 39);

/**
 * 너무 많은 심볼릭 링크를 만났습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELOOP', 40);

/**
 * 운영이 막힐 것입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EWOULDBLOCK', 11);

/**
 * 원하는 유형의 메시지가 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMSG', 42);

/**
 * 식별자가 제거되었습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIDRM', 43);

/**
 * 채널 번호가 범위 밖입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECHRNG', 44);

/**
 * 레벨 2가 동기화되지 않았습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2NSYNC', 45);

/**
 * 레벨 3이 멈춰 있습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3HLT', 46);

/**
 * 레벨 3이 재설정되었습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3RST', 47);

/**
 * 링크 번호가 범위 밖입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELNRNG', 48);

/**
 * 프로토콜 드라이버가 연결되지 않았습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUNATCH', 49);

/**
 * CSI 구조가 사용할 수 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOCSI', 50);

/**
 * 레벨 2가 멈춰 있습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2HLT', 51);

/**
 * 잘못된 교환입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADE', 52);

/**
 * 잘못된 요청 묘사자입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADR', 53);

/**
 * 교환이 가득합니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXFULL', 54);

/**
 * 아노드가 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOANO', 55);

/**
 * 잘못된 요청 코드입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADRQC', 56);

/**
 * 잘못된 슬롯입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADSLT', 57);

/**
 * 장치가 스트림이 아닙니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSTR', 60);

/**
 * 데이터가 사용할 수 없습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODATA', 61);

/**
 * 타이머가 만료되었습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIME', 62);

/**
 * 스트림 자원이 부족합니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSR', 63);

/**
 * 기계가 네트워크에 연결되어 있지 않습니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENONET', 64);

/**
 * 객체가 원격입니다.
 * @link http://php.net/manual/en/sockets.constants.php
 */

