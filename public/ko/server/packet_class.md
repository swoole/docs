# 스와일로\서버\패킷

여기는 `Swoole\Server\Packet`에 대한 상세한 소개입니다.


## 속성


### $server_socket
서비스 端의 파일 디스크립터 `fd`를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Packet->server_socket
```


### $server_port
서비스 端의 수신을 기다리는 포트 `server_port`를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Packet->server_port
```


### $dispatch_time
해당 요청 데이터의 도착 시간 `dispatch_time`를 반환합니다. 이 속성은 `double` 유형입니다.

```php
Swoole\Server\Packet->dispatch_time
```


### $address
클라이언트의 주소 `address`를 반환합니다. 이 속성은 `string` 유형의 문자열입니다.

```php
Swoole\Server\Packet->address
```


### $port
클라이언트의 수신을 기다리는 포트 `port`를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server\Packet->port
```

### $data
클라이언트가 전달한 데이터 `data`를 반환합니다. 이 속성은 `string` 유형의 문자열입니다.

```php
Swoole\Server\Packet->data
```
