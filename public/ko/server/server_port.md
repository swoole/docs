# Swoole\Server\Port

여기에 `Swoole\Server\Port`에 대한 자세한 소개가 있습니다.


## 속성


### $host
청취하는 호스트 주소를 반환합니다. 이 속성은 `string` 유형의 문자열입니다.

```php
Swoole\Server\Port->host
```


### $port
청취하는 호스트 포트를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Port->port
```


### $type
이 `server` 유형의 세트를 반환합니다. 이 속성은 열거로, `SWOOLE_TCP`, `SWOOLE_TCP6`, `SWOOLE_UDP`, `SWOOLE_UDP6`, `SWOOLE_UNIX_DGRAM`, `SWOOLE_UNIX_STREAM` 중 하나를 반환합니다.

```php
Swoole\Server\Port->type
```


### $sock
청취하는 소켓을 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Port->sock
```


### $ssl
`ssl` 암호화가 활성화되어 있는지를 반환합니다. 이 속성은 `bool` 유형입니다.

```php
Swoole\Server\Port->ssl
```


### $setting
해당 포트에 대한 설정을 반환합니다. 이 속성은 `array`의 배열입니다.

```php
Swoole\Server\Port->setting
```


### $connections
해당 포트에 연결된 모든 연결을 반환합니다. 이 속성은 이터레이터입니다.

```php
Swoole\Server\Port->connections
```


## 방법


### set() 

`Swoole\Server\Port` 운영 시 다양한 매개변수를 설정하는 데 사용됩니다. 사용 방법은 [Swoole\Server->set()](/server/methods?id=set)와 같습니다.

```php
Swoole\Server\Port->set(array $setting): void
```


### on() 

`Swoole\Server\Port`의 콜백 함수를 설정하는 데 사용됩니다. 사용 방법은 [Swoole\Server->on()](/server/methods?id=on)와 같습니다.

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```


### getCallback() 

설정된 콜백 함수를 반환합니다.

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

  * **매개변수**

    * `string $name`

      * 기능: 콜백 이벤트 이름
      * 기본값: 없음
      * 기타 값: 없음

  * **반환값**

    * 콜백 함수가 설정되어 있으면 해당 함수를 반환하고, `null`이 반환되면 해당 콜백 함수가 존재하지 않음을 나타냅니다.


### getSocket() 

현재 소켓 `fd`를 PHP의 `Socket` 객체로 변환합니다.

```php
Swoole\Server\Port->getSocket(): Socket|false
```

  * **반환값**

    * `Socket` 객체가 반환되면 성공을 나타내고, `false`가 반환되면 실패를 나타냅니다.

!> 주의: `Swoole`을编译할 때 `--enable-sockets` 옵션을 활성화해야만 이 기능이 사용할 수 있습니다.
