# Swoole\Async\Client

`Swoole\Async\Client`는 `Swoole\Client`의 하위클래스로, 비동기적이고 비 bloquear된 `TCP/UDP/UnixSocket` 네트워크 클라이언트입니다. 비동기 클라이언트는 동기적으로 기다리지 않고 이벤트 콜백 함수를 설정해야 합니다.



- 비동기 클라이언트는 일부 동기적이고 블록적인 클라이언트의 메서드를 호출할 수 있습니다.  
- `6.0` 이상 버전에서만 사용할 수 있습니다.



## 전체 예제

```php
$cli = new Swoole\Async\Client(SWOOLE_SOCK_TCP);

$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());
    $client->send(RandStr::gen(1024, RandStr::ALL));
});

$client->on("receive", function(Swoole\Async\Client $client, string $data){
    $recv_len = strlen($data);
    $client->send(RandStr::gen(1024, RandStr::ALL));
    $client->close();
    Assert::false($client->isConnected());
});

$client->on("error", function(Swoole\Async\Client $client) {
    echo "error";
});

$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});

$client->connect("127.0.0.1", 9501, 0.2);
```


## 메서드

이 페이지에서는 `Swoole\Client`와 차이가 있는 메서드만 나열하고, 하위클래스가 수정하지 않은 메서드는 [동기적이고 블록적인 클라이언트](client.md)를 참고하세요.


### __construct()

생성자, 부모类的构造方法 참조

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> 비동기 클라이언트의 두 번째 매개변수는 반드시 `true`여야 합니다.


### on()

`Client`의 이벤트 콜백 함수를 등록합니다.

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> `on` 메서드를 중복 호출하면 이전 설정은 덮여집니다.

  * **매개변수**

    * `string $event`

      * 기능: 콜백 이벤트 이름, 대소문 무관
      * 默认값: 없음
      * 기타값: 없음

    * `callable $callback`

      * 기능: 콜백 함수
      * 默认값: 없음
      * 기타값: 없음

      !> 함수 이름의 문자열, 클래스 정적 방법, 객체 방법 배열, 익명 함수 참조[이 절](/learn?id=콜백 함수 설정 방법).
  
  * **반환값**

    * 성공 시 `true`, 실패 시 `false` 반환합니다.



### isConnected()
현재 클라이언트가 서버와 연결을 맺었는지를 확인합니다.

```php
Swoole\Async\Client->isConnected(): bool
```

* 연결 성공 시 `true`, 실패 시 `false` 반환합니다.


### sleep()
일시적으로 데이터 수신을 중지합니다. 호출 시 이벤트 루프에서 이 클라이언트를 제거하고, 데이터 수신 이벤트는 다시 발생하지 않습니다. `wakeup()` 메서드를 호출하여 수신을 재개할 수 있습니다.

```php
Swoole\Async\Client->sleep(): bool
```

* 성공 시 `true`, 실패 시 `false` 반환합니다.


### wakeup()
데이터 수신을 재개합니다. 호출 시 이벤트 루프에 다시 등록됩니다.

```php
Swoole\Async\Client->wakeup(): bool
```

* 성공 시 `true`, 실패 시 `false` 반환합니다.


### enableSSL()
`SSL/TLS` 암호화를 동적으로 활성화합니다. 일반적으로 `startTLS` 클라이언트에 사용됩니다. 연결이 맺힌 후에 먼저 명문 데이터를 전송한 다음 암호화 전송을 시작합니다.

```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* 이 함수는 `connect` 성공 후에만 호출할 수 있습니다.
* 비동기 클라이언트는 `$callback`을 설정해야 하며, `SSL` 핸드셋업이 완료된 후에 이 콜백을 호출합니다.
* 성공 시 `true`, 실패 시 `false` 반환합니다.


## 콜백 이벤트


### connect
연결이 완료된 후에 발생합니다. `HTTP` 또는 `Socks5` 프록시 및 `SSL` 터널 암호화가 설정되어 있다면, 프록시 핸드셋업이 완료되고 `SSL` 암호화 핸드셋업이 완료된 후에 발생합니다.

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

이 이벤트 콜백 후에 `isConnected()`를 사용하면 `true`가 반환됩니다.



### error 
연결이 실패한 후에 발생합니다. `client->errCode`를读取하여 오류 정보를 얻을 수 있습니다.
```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```



- `connect`와 `error`는 둘 중 하나만 발생합니다. 연결이 성공하거나 실패하면 결과가 하나만 있습니다.

- `Client::connect()`는 직접 `false`를 반환할 수 있으며, 이는 연결 실패를 의미합니다. 이때는 `error` 콜백이 실행되지 않습니다. 반드시 `connect` 호출의 반환값을 확인하세요.

- `error` 이벤트는 비동기적 결과로, 연결을 시작한 후 `error` 이벤트가 발생하기까지 일정한 `IO` 대기 시간이 있습니다.
- `connect`가 실패하여 즉시 실패하는 경우, 이 오류는 운영체계에서 직접 발생하며, 중간에 어떠한 `IO` 대기 시간도 없습니다.


### receive
데이터를 수신한 후에 발생합니다.

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```



- 어떠한 프로토콜도 설정하지 않은 경우, 예를 들어 `EOF` 또는 `LENGTH`, 최대 반환 데이터 길이는 `64K`입니다.

- 프로토콜 처리 매개변수가 설정되어 있다면, 최대 데이터 길이는 `package_max_length` 매개변수에 설정된 값이며, 기본은 `2M`입니다.
- `$data`는 반드시 비어 있지 않습니다. 시스템 오류를 받거나 연결이 닫힐 경우, `close` 이벤트가 발생합니다.

### close
연결이 닫힐 때 발생합니다.

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```
