# 코루틴 TCP/UDP 클라이언트

`Coroutine\Client`는 `TCP`, `UDP`, [unixSocket](/learn?id= 什么是IPC) 전송 프로토콜의 [Socket 클라이언트](/coroutine_client/socket) 포장 코드를 제공하며, 사용 시 단지 `new Swoole\Coroutine\Client`만으로도 됩니다.

* **실현 원리**

    * `Coroutine\Client`의 모든 네트워크 요청 관련 방법은 `Swoole`가 [코루틴 스케줄러](/coroutine?id=코루틴 스케줄러)를 진행하여, 비즈니스 계층은 이를 인식할 필요가 없습니다.
    * 사용 방법은 [Client](/client) 동기식 방법과 완전히 동일합니다.
    * `connect`超时 설정은 `Connect`, `Recv`, `Send`超时에도 동시에 적용됩니다.

* **상속 관계**

    * `Coroutine\Client`와 [Client](/client)는 상속 관계가 아니지만, `Client`가 제공하는 모든 방법은 `Coroutine\Client`에서 사용할 수 있습니다. 자세한 내용은 [Swoole\Client](/client?id=method)를 참고하세요.
    * `Coroutine\Client`에서는 `set` 方法로 [설정 옵션](/client?id=설정)을 설정할 수 있으며, 사용 방법은 `Client->set`와 완전히 동일합니다. 다르게 사용하는 함수에 대해서는 `set()` 함수小节에서 별도로 설명합니다.

* **사용 예시**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

* **プロト콜 처리**

코루틴 클라이언트는 또한 길이와 `EOF` 프로토콜 처리를 지원하며, 설정 방법은 [Swoole\Client](/client?id=설정)와 완전히 동일합니다.

```php
$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0, //第N个字节是包长度的值
    'package_body_offset'   => 4, //第几个字节开始计算长度
    'package_max_length'    => 2000000, //协议最大长度
));
```


### connect()

원격 서버에 연결합니다.

```php
Swoole\Coroutine\Client->connect(string $host, int $port, float $timeout = 0.5): bool
```

  * **매개변수** 

    * **`string $host`**
      * **기능**：원격 서버의 주소【底层会自动进行协程切换解析域名为IP地址】
      * **기본값**：무관
      * **기타값**：무관

    * **`int $port`**
      * **기능**：원격 서버 포트
      * **기본값**：무관
      * **기타값**：무관

    * **`float $timeout`**
      * **기능**：네트워크 IO의超时시간; `connect/send/recv` 포함,超时 발생 시 연결은 자동으로 `close`됩니다, 참고[클라이언트超时규칙](/coroutine_client/init?id=超时规则)
      * **값 단위**：초【소수형 지원, 예: `1.5`는 `1s`+`500ms`을 의미합니다】
      * **기본값**：`0.5s`
      * **기타값**：무관

* **알림**

    * 연결에 실패하면 `false`를 반환합니다
    *超时 후 반환, `$cli->errCode`를 확인하여 `110`이면超时입니다

* **실패 재시도**

!> `connect` 연결에 실패한 후에는 직접 재연결을 할 수 없습니다. 기존의 `socket`를 `close`하여야 하고, 그 후에 `connect` 재시도를 시도할 수 있습니다.

```php
//connect 실패
if ($cli->connect('127.0.0.1', 9501) == false) {
    //기존socket 닫기
    $cli->close();
    //재시도
    $cli->connect('127.0.0.1', 9501);
}
```

* **예시**

```php
if ($cli->connect('127.0.0.1', 9501)) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}

if ($cli->connect('/tmp/rpc.sock')) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}
```


### isConnected()

클라이언트의 연결 상태를 반환합니다.

```php
Swoole\Coroutine\Client->isConnected(): bool
```

  * **반환값**

    * `false` 반환 시 현재 서버에 연결하지 않았음을 나타냅니다
    * `true` 반환 시 현재 서버에 연결되어 있음을 나타냅니다
    
!> `isConnected` 方法는 응용 계층 상태를 반환하며, `Client`가 `connect`을 성공하여 `Server`에 연결되었음을 나타내며, `close`를 호출하여 연결이 닫히지 않았음을 나타냅니다. `Client`는 `send`, `recv`, `close` 등의 작업을 수행할 수 있지만, 다시 `connect`을 수행할 수 없습니다.  
이것은 연결이 반드시 사용 가능하다는 것을 의미하지 않습니다. `send` 또는 `recv`를 수행할 때에도 오류가 반환될 수 있으며, 이는 응용 계층이 하위 `TCP` 연결의 상태를 얻을 수 없기 때문입니다. `send` 또는 `recv`를 수행할 때 응용 계층이 커널과 상호 작용하여 진정한 연결 사용 가능 상태를 얻을 수 있습니다.


### send()

데이터를 보냅니다.

```php
Swoole\Coroutine\Client->send(string $data): int|bool
```

  * **매개변수** 

    * **`string $data`**
    
      * **기능**：보낼 데이터로, 반드시 문자열 타입이어야 하며, 이진 데이터도 지원됩니다
      * **기본값**：무관
      * **기타값**：무관

  * 성공 시 `Socket` 버퍼에 쓰인 바이트 수를 반환하며, 하단은 가능한 한 모든 데이터를 보냅니다. 반환된 바이트 수가 전달된 `$data`의 길이와 다르다면, 아마도 상대방이 `Socket`을 닫은 것일 것이며, 다음 `send` 또는 `recv` 호출 시 해당 오류 코드를 반환할 것입니다.

  * 실패 시 `false`를 반환하며, `$client->errCode`를 이용하여 오류 원인을 확인할 수 있습니다.


### recv()

서버에서 데이터를 수신하는 방법입니다.

```php
Swoole\Coroutine\Client->recv(float $timeout = 0): string|bool
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능**：초기화 시간을 설정합니다.
      * **값의 단위**：초(예: `1.5`는 `1초 + 500밀리초`를 나타냅니다)
      * **기본값**：기존의 `set`에서 전달한 `timeout` 설정에 따라 결정됩니다.
      * **기타값**：기타 값은 없습니다.

    !> 초기화 시간을 설정하면, 먼저 전달된 매개변수에 해당하는 값이 우선시되고, 그 다음에는 `set`에서 전달한 `timeout`가 적용됩니다. 초기화 시간 초과로 인한 오류 코드는 `ETIMEDOUT`입니다.

  * **반환값**

    * `package_max_length`가 설정되어 있다면, 전체 데이터를 반환하며, 길이는 `package_max_length`에 제한됩니다.
    * `package_max_length`가 설정되지 않았다면, 최대 `64KB`의 데이터를 반환합니다.
    * `package_max_length`가 설정되지 않거나, 프로토콜이 설정되지 않은 경우에는 원본 데이터를 반환하며, PHP 코드에서 네트워크 프로토콜 처리를 직접 구현해야 합니다.
    * `recv`가 빈 문자열을 반환하면, 서버에서 연결을 종료하였다는 것을 의미하며, 이를 위해서는 `close`를 호출해야 합니다.
    * `recv`가 실패하면 `false`를 반환하며, 이를 통해 `$client->errCode`를 확인하여 오류 원인을 파악할 수 있습니다. 자세한 처리 방법은 아래의 [전체적인 예시](/coroutine_client/client?id=전체적인_예시)를 참고하세요.


### close()

연결을 종료합니다.

!> `close`는 블록하지 않으며 즉시 반환됩니다. 닫기 작업에는 코루틴 전환이 발생하지 않습니다.

```php
Swoole\Coroutine\Client->close(): bool
```


### peek()

데이터를 미리보기합니다.

!> `peek`는 직접적으로 `socket`를 조작하기 때문에 [코루틴 스케줄러](/coroutine?id=코루틴_스케줄러)를 유발하지 않습니다.

```php
Swoole\Coroutine\Client->peek(int $length = 65535): string
```

  * **알림**

    * `peek`는 커널의 `socket` 버퍼에 보유 중인 데이터만을 미리보기하기 때문에 오프셋을 고려하지 않습니다. `peek`를 사용한 후에도 `recv`를 호출하면 해당 데이터를 여전히 읽을 수 있습니다.
    * `peek`는 비록 비블록이지만 즉시 반환됩니다. `socket` 버퍼에 데이터가 있을 경우에는 해당 데이터 내용을 반환합니다. 버퍼가 비어 있을 경우에는 `false`를 반환하고 `$client->errCode`를 설정합니다.
    * 연결이 이미 닫혀 있다면 `peek`는 빈 문자열을 반환합니다.
### set()

클라이언트 매개변수를 설정합니다.

```php
Swoole\Coroutine\Client->set(array $settings): bool
```

  * **구성 매개변수**

    * 자세한 내용은 [Swoole\Client](/client?id=set)를 참고하세요.

* **[Swoole\Client](/client?id=set)와의 차이점**
    
    코루틴 클라이언트는 더 세밀한 시간 제어를 제공합니다. 다음을 설정할 수 있습니다:
    
    * `timeout`: 총 시간 제어, 연결, 전송, 수신 모든 시간 제어 포함
    * `connect_timeout`: 연결 시간 제어
    * `read_timeout`: 수신 시간 제어
    * `write_timeout`: 전송 시간 제어
    * 자세한 내용은 [클라이언트 시간 제어 규칙](/coroutine_client/init?id=시간제어규칙)을 참고하세요.

* **예제**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'timeout' => 0.5,
        'connect_timeout' => 1.0,
        'write_timeout' => 10.0,
        'read_timeout' => 0.5,
    ));

    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

### 전체 예제

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // 전체적으로 빈 문자열이면 바로 연결을 종료합니다
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // 비즈니스 로직과 오류 코드에 따라 처리할 수 있습니다. 예를 들어:
                    // 시간 초과가 아닐 경우에는 연결을 유지하고, 그렇지 않을 경우에는 연결을 종료합니다
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```
