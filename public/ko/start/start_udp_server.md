# UDP 서버


## 프로그램 코드

다음 코드를 udpServer.php에 작성하세요.

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

//데이터 수신 이벤트를 감시합니다.
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Server：{$data}");
});

//서버 시작
$server->start();
```

UDP 서버는 TCP 서버와 달리 연결 개념이 없습니다. Server가 시작되면 클라이언트는 Connect할 필요 없이 직접 Server가 경청하는 9502 포트에 데이터 팩을 보낼 수 있습니다. 해당 이벤트는 onPacket입니다.

* `$clientInfo`는 클라이언트 관련 정보로, 클라이언트의 IP와 포트 등의 내용을 포함하는 배열입니다.
* `$server->sendto` 메서드를 호출하여 클라이언트에 데이터를 보냅니다.
!> Docker는 기본적으로 TCP 프로토콜로 통신을 사용합니다. 만약 UDP 프로토콜을 사용해야 한다면 Docker 네트워크 설정을 통해 구현해야 합니다.  
```shell
docker run -p 9502:9502/udp <image-name>
```

## 서비스를 시작합니다

```shell
php udpServer.php
```

UDP 서버는 `netcat -u`를 사용하여 연결 테스트를 할 수 있습니다.

```shell
netcat -u 127.0.0.1 9502
hello
Server: hello
```
