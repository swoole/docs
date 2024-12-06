# Swoole\Client

`Swoole\Client`는 `TCP/UDP/UnixSocket`의 클라이언트封装 코드를 제공하며, 사용 시 단지 `new Swoole\Client`만으로도 사용할 수 있습니다. `FPM/Apache` 환경에서 사용할 수 있습니다.
전통적인 [streams](https://www.php.net/streams) 시리즈 함수에 비해 다음과 같은 장점이 있습니다:

  * `stream` 함수의 기본超时 시간이 길어 상대방의 응답 시간이 길어지면 오랫동안 막힐 수 있습니다
  * `stream` 함수의 `fread`의 기본 캐시 영역 크기가 `8192`로 제한되어 `UDP`의 큰 패킷을 지원할 수 없습니다
  * `Client`는 `waitall`을 지원하여 확실한 패킷 길이를 알 때 한 번에 모두 가져올 수 있어 반복적으로 읽을 필요가 없습니다
  * `Client`는 `UDP Connect`을 지원하여 `UDP`의 패키징 문제를 해결했습니다
  * `Client`는 순수 `C` 코드로 작성되어 `socket`에만 특화되어 있으며, `stream` 함수는 매우 복잡합니다. `Client`의 성능이 더 좋습니다
  * [swoole_client_select](/client?id=swoole_client_select) 함수를 사용하여 여러 `Client`의 병렬 제어를 실현할 수 있습니다


### 전체 예제

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


## 방법


### __construct()

생성자

```php
Swoole\Client::__construct(int $sock_type, bool $is_sync = false, string $key);
```

* **매개변수** 

  * **`int $sock_type`**
    * **기능**：`socket`의 유형을 나타냅니다【`SWOOLE_SOCK_TCP`, `SWOOLE_SOCK_TCP6`, `SWOOLE_SOCK_UDP`, `SWOOLE_SOCK_UDP6`를 지원합니다。 구체적인 의미는 [이 부분](/server/methods?id=__construct)을 참고하세요】
    * **기본값**：없음
    * **기타 값**：없음

  * **`bool $is_sync`**
    * **기능**：同步 블록 모드로, 동기적으로만 작동하며 비활성화되어야 합니다. 비동기 콜백 모드를 사용하려면 `Swoole\Async\Client`를 사용하세요
    * **기본값**：`false`
    * **기타 값**：없음

  * **`string $key`**
    * **기능**：장기 연결에 사용되는 `Key`【기본적으로는 `IP:PORT`를 `key`로 사용합니다。같은 `key`라 할지라도 두 번 new해도 동일한 TCP 연결만 사용됩니다】
    * **기본값**：`IP:PORT`
    * **기타 값**：없음

!> 기본적으로는 하단에서 제공하는 매크로를 사용하여 유형을 지정할 수 있습니다. 자세한 내용은 [상수 정의](/consts)를 참고하세요

#### PHP-FPM/Apache에서 장기 연결 만들기

```php
$cli = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
```

[SWOOLE_KEEP](/client?id=swoole_keep) 플래그를 추가하면, PHP 요청이 종료되거나 `$cli->close()`가 호출될 때 생성된 `TCP` 연결이 닫히지 않습니다. 다음 `connect` 호출 시 이전에 생성된 연결을 재사용합니다. 장기 연결은 기본적으로 `ServerHost:ServerPort`를 `key`로 저장합니다. 세 번째 매개변수 내에서 `key`를 지정할 수 있습니다.

`Client` 객체가析构될 때 자동으로 [close](/client?id=close) 메서드를 호출하여 `socket`을 닫습니다

#### Server에서 Client 사용하기

  * Client는 [이벤트回调 함수](/server/events)에서만 사용해야 합니다.
  * Server는 어떤 언어로 작성된 `socket client`로도 연결할 수 있습니다. 마찬가지로 Client도 어떤 언어로 작성된 `socket server`에 연결할 수 있습니다

!> Swoole4+의 코어 환경에서 이 `Client`를 사용하면 동기 모델로 후퇴하게 됩니다.


### set()

클라이언트 매개변수를 설정하는 방법으로, [connect](/client?id=connect) 전에 실행해야 합니다.

```php
Swoole\Client->set(array $settings);
```

대상 가능한 설정 옵션은 Client - [설정 옵션](/client?id=설정)을 참고하세요


### connect()

원격 서버에 연결합니다.

```php
Swoole\Client->connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
```

* **매개변수** 

  * **`string $host`**
    * **기능**：서버 주소【자동으로 비동기적으로 도메인을 해석할 수 있으며, `$host`은 직접 도메인을 전달할 수 있습니다】
    * **기본값**：없음
    * **기타 값**：없음

  * **`int $port`**
    * **기능**：서버 포트
    * **기본값**：없음
    * **기타 값**：없음

  * **`float $timeout`**
    * **기능**：초기超时 시간을 설정합니다
    * **값 단위**：초【소수형을 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`를 나타냅니다】
    * **기본값**：`0.5`
    * **기타 값**：없음

  * **`int $sock_flag`**
    - `UDP` 유형에서만 사용하며, `udp_connect`을 활성화할지 여부를 나타냅니다. 이 옵션을 설정하면 `$host`와 `$port`을 바인딩하고, 해당 `UDP`는 지정되지 않은 `host/port`의 패킷을 버립니다.
    - `TCP` 유형에서 `$sock_flag=1`로 설정하면 비블록 `socket`로 설정하며, 이후 이 fd는 [비동기 IO](/learn?id=同步io异步io)가 되고, `connect`는 즉시 반환됩니다. `$sock_flag`를 `1`로 설정하면, `send/recv` 전에 반드시 [swoole_client_select](/client?id=swoole_client_select)을 사용하여 연결이 완료되었는지 확인해야 합니다.

* **반환값**

  * 성공 시 `true`를 반환합니다
  * 실패 시 `false`를 반환하며, `errCode` 속성을 확인하여 실패 이유를 얻을 수 있습니다

* **同步 모드**

`connect` 메서드는 연결이 성공하여 `true`를 반환할 때까지 블록됩니다. 이때부터 서버에 데이터를 보낼 수 있거나 받을 수 있습니다.

```php
if ($cli->connect('127.0.0.1', 9501)) {
      $cli->send("data");
} else {
      echo "connect failed.";
}
```

연결에 실패하면 `false`를 반환합니다

> 同步 `TCP` 클라이언트는 `close`를 실행한 후에 다시 `Connect`을 호출하여 서버에 새로운 연결을 만들 수 있습니다

* **실패 재연결**

`connect`에 실패한 후에 한 번 재연결하고자 한다면, 먼저 `close`를 호출하여 오래된 `socket`을 닫아야 합니다. 그렇지 않으면 `EINPROCESS` 오류가 반환되며, 현재의 `socket`이 서버에 연결 중이라는 점에서 클라이언트는 연결이 성공했는지 확인할 수 없으므로 다시 `connect`을 사용할 수 없습니다. `close`를 호출하면 현재의 `socket`이 닫히고, 하단에서 새로운 `socket`를 생성하여 연결을 시도합니다.

!> [SWOOLE_KEEP](/client?id=swoole_keep) 장기 연결이 활성화된 후에는 `close` 메서드의 첫 번째 매개변수를 `true`로 설정하여 장기 연결 `socket`을 강제로 종료해야 합니다

```php
if ($socket->connect('127.0.0.1', 9502) === false) {
    $socket->close(true);
    $socket->connect('127.0.0.1', 9502);
}
```

* **UDP Connect**

기본적으로 하단에서 `udp connect`는 활성화되지 않습니다. `UDP` 클라이언트가 `connect`를 호출하면, 하단에서 `socket`를 생성한 즉시 성공을 반환합니다. 이때 이 `socket`은 바인딩된 주소가 `0.0.0.0`이며, 다른 기계에서도 이 포트에 패킷을 보낼 수 있습니다.

예를 들어 `$client->connect('192.168.1.100', 9502)`를 호출하면, 운영 체제가 클라이언트 `socket`에 무작위로 포트 번호 `58232`를 할당합니다. 다른 기계, 예를 들어 `192.168.1.101`도 이 포트에 패킷을 보낼 수 있습니다.

?> `udp connect`가 활성화되지 않은 경우, `getsockname`을 호출하여 반환된 `host` 항목은 `0.0.0.0`입니다

네 번째 매개변수를 `1`로 설정하여 `udp connect`를 활성화하면, `$client->connect('192.168.1.100', 9502, 1, 1)`를 호출합니다. 이때 클라이언트와 서버 사이의 연결이 바인딩되며, 하단에서 서버의 주소에 따라 `socket`이 바인딩되는 주소를 결정합니다. 예를 들어 `192.168.1.100`에 연결되었다면, 현재 `socket`은 `192.168.1.*`의 본기 주소로 바인딩됩니다. `udp connect`가 활성화되면, 클라이언트는 더 이상 다른 호스트에서 이 포트에 보낸 패킷을 받지 않습니다.
### recv()

서버에서 데이터를 수신합니다.

```php
Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
```

* **매개변수**

  * **`int $size`**
    * **기능**: 수신 데이터의 버퍼 최대 길이【해당 매개변수를 너무 크게 설정하지 않는 것이 좋습니다. 그렇지 않으면 많은 메모리를 차지할 수 있습니다】
    * **기본값**: 없음
    * **기타 값**: 없음

  * **`int $flags`**
    * **기능**: 추가적인 매개변수를 설정할 수 있습니다【예: [Client::MSG_WAITALL](/client?id=clientmsg_waitall)】, 구체적인 매개변수는 [해당 부분](/client?id=상수)를 참고하세요
    * **기본값**: 없음
    * **기타 값**: 없음

* **귀속값**

  * 성공하여 데이터를 수신하면 문자열을 반환합니다
  * 연결이 닫혔을 경우 빈 문자열을 반환합니다
  * 실패하면 `false`를 반환하고 `$client->errCode` 속성을 설정합니다

* **EOF/Length 프로토콜**

  * 클라이언트가 `EOF/Length` 검출을 활성화하면 `$size`와 `$waitall` 매개변수를 설정할 필요가 없습니다. 확장 레이어는 전체 데이터 패킷을 반환하거나 `false`를 반환합니다, [프로토콜 해석](/client?id=프로토콜 해석) 장을 참고하세요
  * 잘못된 패킷 헤드나 패킷 헤드 중 길이 값이 [package_max_length](/server/setting?id=package_max_length) 설정보다 클 경우, `recv`는 빈 문자열을 반환합니다, PHP 코드에서는 해당 연결을 닫아야 합니다.


### send()

원격 서버에 데이터를 보냅니다, 연결이 구축된 후에만 상대방에게 데이터를 보낼 수 있습니다.

```php
Swoole\Client->send(string $data): int|false
```

* **매개변수**

  * **`string $data`**
    * **기능**: 보낼 내용【 바이너리 데이터도 지원합니다】
    * **기본값**: 없음
    * **기타 값**: 없음

* **귀속값**

  * 성공하여 보냈을 경우 보낸 데이터의 길이를 반환합니다
  * 실패하면 `false`를 반환하고 `errCode` 속성을 설정합니다

* **알림**

  * `connect`를 수행하지 않은 경우, `send`를 호출하면 경고가 발생합니다
  * 보낼 데이터에는 길이 제한이 없습니다
  * 보낼 데이터가 너무 크고 소켓 버퍼가 가득 차면 프로세스가 가로막혀 작성 가능한 상태로 기다립니다


### sendfile()

서버에 파일을 보냅니다, 이 함수는 `sendfile` 운영 체계 호출을 기반으로 구현되었습니다

```php
Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

!> `sendfile`는 UDP 클라이언트와 SSL 터널 암호화 연결에는 사용할 수 없습니다

* **매개변수**

  * **`string $filename`**
    * **기능**: 보낼 파일의 경로를 지정합니다
    * **기본값**: 없음
    * **기타 값**: 없음

  * **`int $offset`**
    * **기능**: 업로드할 파일의 오프셋【파일의 중간 부분부터 데이터 전송을 시작할 수 있습니다. 이 기능은 중단 후 재개 전송을 지원하는 데 사용할 수 있습니다.】
    * **기본값**: 없음
    * **기타 값**: 없음

  * **`int $length`**
    * **기능**: 보낼 데이터의 크기【기본적으로 전체 파일의 크기입니다】
    * **기본값**: 없음
    * **기타 값**: 없음

* **귀속값**

  * 전달된 파일이 존재하지 않을 경우 `false`를 반환합니다
  * 성공적으로 수행되면 `true`를 반환합니다

* **주의**

  * `sendfile`는 전체 파일이 전송되거나 치명적인 오류가 발생할 때까지 계속 가로막혀 있습니다



### sendto()

임의의 `IP:PORT`의 호스트에게 `UDP` 데이터 패킷을 보냅니다, `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6` 유형만 지원합니다

```php
Swoole\Client->sendto(string $ip, int $port, string $data): bool
```

* **매개변수**

  * **`string $ip`**
    * **기능**: 대상 호스트의 `IP` 주소, `IPv4/IPv6`을 지원합니다
    * **기본값**: 없음
    * **기타 값**: 없음

  * **`int $port`**
    * **기능**: 대상 호스트의 포트
    * **기본값**: 없음
    * **기타 값**: 없음

  * **`string $data`**
    * **기능**: 보낼 데이터 내용【`64K`를 초과할 수 없습니다】
    * **기본값**: 없음
    * **기타 값**: 없음


### enableSSL()

동적으로 SSL 터널 암호화가 활성화됩니다, `swoole`를 컴파일할 때 `--enable-openssl` 옵션을 사용해야만 이 함수를 사용할 수 있습니다.

```php
Swoole\Client->enableSSL(): bool
```

클라이언트가 연결을 구축할 때 명문 통신을 사용하고, 중간에 SSL 터널 암호화 통신으로 변경하고자 할 때 `enableSSL` 메서드를 사용할 수 있습니다. 처음부터 SSL인 경우 [SSL 구성](/client?id=ssl 관련)을 참고하세요. `enableSSL`를 사용하여 동적으로 SSL 터널 암호화를 활성화하려면 두 가지 조건을 충족해야 합니다:

  * 클라이언트 생성 시 유형이 `SSL`이 아닙니다
  * 클라이언트가 이미 서버와 연결을 구축했습니다

`enableSSL`를 호출하면 SSL 핸드셋 완료까지 기다립니다.

* **예시**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
//SSL 터널 암호화 활성화
if ($client->enableSSL())
{
    //핸드셋 완료, 이후에 보낸 및 받은 데이터는 암호화됩니다
    $client->send("hello world\n");
    echo $client->recv();
}
$client->close();
```



### getPeerCert()

서버의 인증서 정보를 가져옵니다, `swoole`를 컴파일할 때 `--enable-openssl` 옵션을 사용해야만 이 함수를 사용할 수 있습니다.

```php
Swoole\Client->getPeerCert(): string|false
```

* **귀속값**

  * 성공하면 `X509` 인증서 문자열 정보를 반환합니다
  * 실패하면 `false`를 반환합니다

!> 이 메서드는 SSL 핸드셋이 완료된 후에만 호출할 수 있습니다.
  
인증서 정보를 파악하기 위해 `openssl` 확장 제공하는 `openssl_x509_parse` 함수를 사용할 수 있습니다.

!> swoole를 컴파일할 때 `--enable-openssl](/environment?id=컴파일 옵션)` 옵션을 활성화해야 합니다


### verifyPeerCert()

서버의 인증서를 검증합니다, `swoole`를 컴파일할 때 `--enable-openssl` 옵션을 사용해야만 이 함수를 사용할 수 있습니다.

```php
Swoole\Client->verifyPeerCert()
```


### isConnected()

클라이언트의 연결 상태를 반환합니다

* `false`를 반환하면 현재 서버에 연결되어 있지 않습니다
* `true`를 반환하면 현재 서버에 연결되어 있습니다

```php
Swoole\Client->isConnected(): bool
```

!> `isConnected` 메서드는 응용 계층 상태를 반환하며, `Client`이 `connect`를 수행하여 성공적으로 `Server`에 연결되었으며, `close`를 수행하여 연결을 닫지 않았다는 것을 나타냅니다. `Client`은 `send`, `recv`, `close` 등의 작업을 수행할 수 있지만, 다시 `connect`를 수행할 수 없습니다.  
이것은 연결이 반드시 사용할 수 있다는 것을 의미하지 않습니다, `send` 또는 `recv`를 수행할 때 여전히 오류가 발생할 수 있습니다, 왜냐하면 응용 계층은 기본적인 `TCP` 연결 상태를 얻을 수 없기 때문입니다, `send` 또는 `recv`를 수행할 때 응용 계층과 커널이 상호 작용하여 실제 연결이 사용 가능한 상태를 얻을 수 있습니다.


### getSockName()

클라이언트 소켓의 로컬 호스트:포트를 가져옵니다.

!> 연결이 완료된 후에만 사용할 수 있습니다

```php
Swoole\Client->getsockname(): array|false
```

* **귀속값**

```php
array('host' => '127.0.0.1', 'port' => 53652);
```


### getPeerName()

대상 소켓의 IP 주소와 포트를 가져옵니다

!> `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM` 유형만 지원합니다

```php
Swoole\Client->getpeername(): array|false
```

`UDP` 프로토콜 통신 클라이언트가 한 서버에 데이터 패킷을 보낸 후, 해당 서버에서 클라이언트에게 응답을 보낼 수 없습니다. 실제 응답을 보낸 서버의 `IP:PORT`를 가져올 수 있습니다 `getpeername` 메서드를 사용합니다.

!> 이 함수는 `$client->recv()` 이후에 호출해야 합니다
### close()

연결을 닫습니다.

```php
Swoole\Client->close(bool $force = false): bool
```

* **매개변수** 

  * **`bool $force`**
    * **기능**: 연결을 강제로 닫습니다.【SWOOLE_KEEP](/client?id=swoole_keep) 장기 연결을 닫을 때 사용됩니다】
    * **기본값**: 없음
    * **기타값**: 없음

`swoole_client` 연결이 `close`되었을 경우 다시 `connect`을 시도해서는 안 됩니다. 올바른 방법은 현재의 `Client`를 파괴하고, 새로운 `Client`를 생성하여 새로운 연결을 시도하는 것입니다.

`Client` 객체는 소멸 시 자동으로 `close`됩니다.


### shutdown()

클라이언트를 닫습니다.

```php
Swoole\Client->shutdown(int $how): bool
```

* **매개변수** 

  * **`int $how`**
    * **기능**: 클라이언트를 어떻게 닫는지 설정합니다
    * **기본값**: 없음
    * **기타값**: Swoole\Client::SHUT_RDWR(읽기/쓰기 모두 닫기), SHUT_RD(읽기만 닫기), Swoole\Client::SHUT_WR(쓰기만 닫기)


### getSocket()

밑바닥의 `socket` 핸들을 얻습니다. 반환되는 객체는 `sockets` 리소스 핸들입니다.

!> 이 방법은 `sockets` 확장을 필요로 하며, 컴파일 시 [--enable-sockets](/environment?id=编译选项) 옵션을 켜야 합니다

```php
Swoole\Client->getSocket()
```

`socket_set_option` 함수를 사용하여 더 낮은 수준의 `socket` 매개변수를 설정할 수 있습니다.

```php
$socket = $client->getSocket();
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
}
```


### swoole_client_select

Swoole\Client의 병렬 처리에서 `select` 시스템 호출을 사용하여 [IO 이벤트 루프](/learn?id=什么是eventloop)를 수행합니다. 이것은 `epoll_wait`가 아닙니다. Event 模块과 달리, 이 함수는 동기적인 IO 환경에서 사용됩니다(Swoole의 Worker 프로세스에서 호출하면 Swoole 자신의 epoll [IO 이벤트 루프](/learn?id=什么是eventloop)가 실행할 기회가 없습니다).

함수原型:

```php
int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);
```

* `swoole_client_select`는 4개의 매개변수를 받아들입니다. `$read`, `$write`, `$error`는 각각 읽기/쓰기/오류 가능한 파일 디스크립터입니다.  
* 이 3개의 매개변수는 반드시 배열의 참조여야 합니다. 배열의 요소는 `swoole_client` 객체여야 합니다.
* 이 방법은 `select` 시스템 호출에 기반을 두고 있으며, 최대 `1024`개의 `socket`을 지원합니다
* `$timeout` 매개변수는 `select` 시스템 호출의 超時 시간으로, 초 단위로 받을 수 있으며, 부동소수입니다
* 기능은 PHP 원래의 `stream_select()`와 비슷하지만, stream_select은 PHP의 stream 변수 유형만 지원하며 성능이 떨어집니다.

성공 시 이벤트의 수를 반환하고, `$read`/`$write`/`$error` 배열을 수정합니다. foreach 루프를 사용하여 배열을 반복하고, `$item->recv`/`$item->send`를 호출하여 데이터를 수신/보냅니다. 또는 `$item->close()` 또는 `unset($item)`를 호출하여 `socket`을 닫습니다.

`swoole_client_select`가 `0`을 반환하면 지정된 시간 내에 어떠한 IO도 사용할 수 없으며, `select` 호출이超时하였음을 나타냅니다.

!> 이 함수는 `Apache/PHP-FPM` 환경에서 사용할 수 있습니다    

```php
$clients = array();

for($i=0; $i< 20; $i++)
{
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
    $ret = $client->connect('127.0.0.1', 9501, 0.5, 0);
    if(!$ret)
    {
        echo "Connect Server fail.errCode=".$client->errCode;
    }
    else
    {
    	$client->send("HELLO WORLD\n");
    	$clients[$client->sock] = $client;
    }
}

while (!empty($clients))
{
    $write = $error = array();
    $read = array_values($clients);
    $n = swoole_client_select($read, $write, $error, 0.6);
    if ($n > 0)
    {
        foreach ($read as $index => $c)
        {
            echo "Recv #{$c->sock}: " . $c->recv() . "\n";
            unset($clients[$c->sock]);
        }
    }
}
```


## 속성


### errCode

오류 코드

```php
Swoole\Client->errCode: int
```

`connect/send/recv/close`가 실패할 경우 자동으로 `$swoole_client->errCode`의 값이 설정됩니다.

`errCode`의 값은 `Linux errno`와 동일합니다. `socket_strerror`을 사용하여 오류 코드를 오류 메시지로 변환할 수 있습니다.

```php
echo socket_strerror($client->errCode);
```

참조: [Linux 오류 코드 목록](/other/errno?id=linux)


### sock

socket 연결의 파일 디스크립터입니다.

```php
Swoole\Client->sock;
```

PHP 코드에서 사용할 수 있습니다

```php
$sock = fopen("php://fd/".$swoole_client->sock); 
```

* `Swoole\Client`의 `socket`를 `stream socket`로 변환합니다. `fread/fwrite/fclose` 등의 함수를 사용하여 프로세스 간에 조작할 수 있습니다.

* [Swoole\Server](/server/methods?id=__construct)의 `$fd`는 이 방법으로 변환할 수 없습니다. 왜냐하면 `$fd`는 단지 숫자일 뿐이고, `$fd` 파일 디스크립터는 메인 프로세스에 속하기 때문입니다. 참조	[SWOOLE_PROCESS](/learn?id=swoole_process) 모델입니다.

* `$swoole_client->sock`는 배열의 `key`로 int로 변환할 수 있습니다.

!> 여기 주의해야 할 점은: `$swoole_client->sock` 속성의 값은 `$swoole_client->connect` 이후에만 얻을 수 있습니다. 서버에 연결하기 전에 이 속성의 값은 `null`입니다.


### reuse

이 연결이 새로 생성되었는지 또는 이미 존재하는 것을 재사용하는지를 나타냅니다. [SWOOLE_KEEP](/client?id=swoole_keep)와 함께 사용됩니다.

#### 사용 장면

`WebSocket` 클라이언트와 서버가 연결을 맺은 후 핸드셋을 진행해야 합니다. 만약 연결이 재사용된다면, 핸드셋을 다시 진행할 필요 없이 바로 `WebSocket` 데이터 프레임을 보낼 수 있습니다.

```php
if ($client->reuse) {
    $client->send($data);
} else {
    $client->doHandShake();
    $client->send($data);
}
```


### reuseCount

이 연결의 재사용 횟수를 나타냅니다. [SWOOLE_KEEP](/client?id=swoole_keep)와 함께 사용됩니다.

```php
Swoole\Client->reuseCount;
```


### type

`socket`의 유형을 나타내며, `Swoole\Client::__construct()`의 `$sock_type` 값을 반환합니다

```php
Swoole\Client->type;
```


### id

`Swoole\Client::__construct()`의 `$key` 값을 반환하며, [SWOOLE_KEEP](/client?id=swoole_keep)와 함께 사용됩니다

```php
Swoole\Client->id;
```


### setting

클라이언트 `Swoole\Client::set()`에서 설정한 구성을 반환합니다

```php
Swoole\Client->setting;
```


## 상수


### SWOOLE_KEEP

Swoole\Client는 `PHP-FPM/Apache`에서 서버端에 TCP 장기 연결을 생성할 수 있도록 지원합니다. 사용 방법:

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
```

`SWOOLE_KEEP` 옵션을 활성화하면, 요청이 끝나더라도 `socket`를 닫지 않으며, 다음 `connect` 시 자동으로 마지막에 생성한 연결을 재사용합니다. `connect`을 실행하면 연결이 이미 서버에 의해 닫혀 있다면, 새로운 연결을 생성합니다.

?> SWOOLE_KEEP의 이점

* `TCP` 장기 연결은 `connect` 3회 손잡이 교환/`close` 4회 손짓을 하는 데 따른 추가적인 IO 소비를 줄일 수 있습니다
* 서버의 `close`/`connect` 횟수를 줄입니다


### Swoole\Client::MSG_WAITALL

  * Client::MSG_WAITALL 매개변수를 설정하면 정확한 `$size`를 설정해야 합니다. 그렇지 않으면 계속 기다리다가 `$size` 만큼의 데이터를 받을 때까지 기다립니다
  * Client::MSG_WAITALL이 설정되지 않은 경우, `$size`의 최대값은 `64K`입니다
  * 잘못된 `$size`를 설정하면 `recv`이超时하여 `false`를 반환합니다
### Swoole\Client::MSG_DONTWAIT

비阻断적으로 데이터를 수신합니다. 데이터가 있든 없든 즉시 반환됩니다.


### Swoole\Client::MSG_PEEK

'socket' 버퍼 영역의 데이터를 엿보기입니다. MSG_PEEK 매개변수를 설정하면 recv가 데이터를 읽을 때 포인터를 변경하지 않으므로 다음 recv 호출은 마지막 위치에서 데이터를 반환합니다.


### Swoole\Client::MSG_OOB

오프오브바운드 데이터를 읽습니다. 자세한 내용은 "TCP 오프오드 데이터"를 찾아보세요.


### Swoole\Client::SHUT_RDWR

클라이언트의 쓰기와 읽기 terminall을 닫습니다.


### Swoole\Client::SHUT_RD

클라이언트의 읽기 terminall을 닫습니다.


### Swoole\Client::SHUT_WR

클라이언트의 쓰기 terminall을 닫습니다.


## 구성

'Client'는 'set' 메서드를 사용하여 일부 옵션을 설정하고 특정 기능을 활성화할 수 있습니다.


### 프로토콜 해석

?> TCP 패킷 경계 문제를 해결하기 위해 프로토콜 해석이 설정되었습니다. 관련 구성의 의미는 'Swoole\Server'와 동일하며, 자세한 내용은 [Swoole\Server 프로토콜](/server/setting?id=open_eof_check) 구성 섹션으로 이동하세요.

* **끝에 대한 검출**

```php
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n\r\n",
    'package_max_length' => 1024 * 1024 * 2,
));
```

* **길이 검출**

```php
$client->set(array(
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0, // N 번째 바이트가 패킷 길이의 값입니다.
    'package_body_offset' => 4, // 길이를 계산할 시작 바이트 번호
    'package_max_length' => 2000000, // 프로토콜 최대 길이
));
```

!> 현재 [open_length_check](/server/setting?id=open_length_check)와 [open_eof_check](/server/setting?id=open_eof_check) 두 가지 자동 프로토콜 처리 기능이 지원됩니다.  
프로토콜 해석을 설정하면 클라이언트의 'recv()' 메서드는 길이 매개변수를 받아들이지 않고, 매번 완전한 패킷을 반환합니다.

* **MQTT 프로토콜**

!> MQTT 프로토콜 해석을 활성화하면 [onReceive](/server/events?id=onreceive) 콜백이 완전한 MQTT 패킷을 받습니다.

```php
$client->set(array(
    'open_mqtt_protocol' => true,
));
```

* **소켓 버퍼 크기**	

!> 소켓의 기본 운영 체제 버퍼, 응용 계층 수신 데이터 메모리 버퍼, 응용 계층 전송 데이터 메모리 버퍼를 포함합니다.	

```php	
$client->set(array(	
    'socket_buffer_size' => 1024 * 1024 * 2, // 2M 버퍼	
));	
```

* **Nagle 합병 알고리즘 비활성화**

```php
$client->set(array(
    'open_tcp_nodelay' => true,
));
```


### SSL 관련

* **SSL/TLS 인증서 구성**

```php
$client->set(array(
    'ssl_cert_file' => $your_ssl_cert_file_path,
    'ssl_key_file' => $your_ssl_key_file_path,
));
```

* **ssl_verify_peer**

서버 측 인증서를 검증합니다.

```php
$client->set([
    'ssl_verify_peer' => true,
]);
```

활성화하면 인증서와 호스트 도메인이 일치하는지 검증하고, 일치하지 않을 경우 자동으로 연결을 종료합니다.

* **자체 인증서**

'ssl_allow_self_signed'을 'true'로 설정하면 자체 인증서를 허용합니다.

```php
$client->set([
    'ssl_verify_peer' => true,
    'ssl_allow_self_signed' => true,
]);
```

* **ssl_host_name**

서버 호스트 이름을 설정합니다. 'ssl_verify_peer' 구성과 함께 사용하거나 [Client::verifyPeerCert](/client?id=verifypeercert)와 함께 사용합니다.

```php
$client->set([
    'ssl_host_name' => 'www.google.com',
]);
```

* **ssl_cafile**

'ssl_verify_peer'를 'true'로 설정하면 원격 인증서를 검증하는 데 사용되는 'CA' 인증서입니다. 이 옵션의 값은 로컬 파일 시스템에서 'CA' 인증서의 전체 경로 및 파일 이름입니다.

```php
$client->set([
    'ssl_cafile' => '/etc/CA',
]);
```

* **ssl_capath**

'ssl_cafile'가 설정되지 않았거나 'ssl_cafile'가 가리키는 파일이 존재하지 않을 경우, 'ssl_capath'가 지정한 디렉토리에서 적합한 인증서를 탐색합니다. 이 디렉토리는 이미 해시 처리가된 인증서 디렉토리여야 합니다.

```php
$client->set([
    'ssl_capath' => '/etc/capath/',
])
```

* **ssl_passphrase**

로컬 인증서[ssl_cert_file](/server/setting?id=ssl_cert_file) 파일의 암호입니다.

* **예제**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_SSL);

$client->set(array(
    'ssl_cert_file' => __DIR__.'/ca/client-cert.pem',
    'ssl_key_file' => __DIR__.'/ca/client-key.pem',
    'ssl_allow_self_signed' => true,
    'ssl_verify_peer' => true,
    'ssl_cafile' => __DIR__.'/ca/ca-cert.pem',
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
echo "connect ok\n";
$client->send("hello world-" . str_repeat('A', $i) . "\n");
echo $client->recv();
```


### package_length_func

길이 계산 함수를 설정합니다. 'Swoole\Server'의 [package_length_func](/server/setting?id=package_length_func) 사용 방법과 완전히 동일합니다. [open_length_check](/server/setting?id=open_length_check)와 함께 사용합니다. 길이 함수는 반드시 정수를 반환해야 합니다.

* `0`을 반환하면 데이터가 부족하여 더 많은 데이터를 수신해야 합니다.
* `-1`을 반환하면 데이터가 잘못되어 기본적으로 연결을 자동으로 종료합니다.
* 패킷의 총 길이값(헤더와 바디의 총 길이를 포함한)을 반환하면 기본적으로 패킷을 조립한 후 콜백 함수에 반환합니다.

기본적으로 기본 층은 최대 `8K`의 데이터를 읽지만, 헤더의 길이가 작을 수록 메모리 복제의 소모가 발생할 수 있습니다. 'package_body_offset' 매개변수를 설정하여 기본 층이 헤더만 읽고 길이를 해석하도록 할 수 있습니다.

* **예제**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check' => true,
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
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


### socks5_proxy

SOCKS5 프록시를 구성합니다.

!> 단일 옵션을 설정하는 것은 무효하며, 'host'와 'port'를 동시에 설정해야 합니다. 'socks5_username'과 'socks5_password'는 선택적 매개변수입니다. 'socks5_port'와 'socks5_password'는 `null`이 허용되지 않습니다.

```php
$client->set(array(
    'socks5_host' => '192.168.1.100',
    'socks5_port' => 1080,
    'socks5_username' => 'username',
    'socks5_password' => 'password',
));
```


### http_proxy

HTTP 프록시를 구성합니다.

!> 'http_proxy_port'와 'http_proxy_password'는 `null`이 허용되지 않습니다.

* **기본 설정**

```php
$client->set(array(
    'http_proxy_host' => '192.168.1.100',
    'http_proxy_port' => 1080,
));
```

* **인증 설정**

```php
$client->set(array(
    'http_proxy_user' => 'test',
    'http_proxy_password' => 'test_123456',
));
```


### bind

!> 단일 bind_port만 설정하는 것은 무효하며, bind_port와 bind_address를 동시에 설정해야 합니다.

?> 기계에 여러 네트워크 인터페이스가 있을 경우, bind_address 매개변수를 설정하여 클라이언트 Socket이 특정 네트워크 주소에 바인딩되도록 강제합니다.  
bind_port를 설정하면 클라이언트 Socket이 외부 서버에 고정 포트를 사용하여 연결할 수 있습니다.

```php
$client->set(array(
    'bind_address' => '192.168.1.100',
    'bind_port' => 36002,
));
```
### 적용 범위

위의 `Client` 설정 항목은 다음과 같은 고객에서도 동일하게 적용됩니다.

  * [Swoole\Coroutine\Client](/coroutine_client/client)
  * [Swoole\Coroutine\Http\Client](/coroutine_client/http_client)
  * [Swoole\Coroutine\Http2\Client](/coroutine_client/http2_client)
