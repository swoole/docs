# TCP 서버

## 프로그램 코드

다음 코드를 tcpServer.php에 작성하세요.

```php
// Server 객체를 생성하여 127.0.0.1:9501 포트를 감시합니다.
$server = new Swoole\Server('127.0.0.1', 9501);

// 연결 입장 이벤트를 감시합니다.
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// 데이터 수신 이벤트를 감시합니다.
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// 연결 종료 이벤트를 감시합니다.
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// 서버를 시작합니다.
$server->start(); 
```

이렇게 하면 `TCP` 서버가 생성되어 본machine의 `9501` 포트를 감시하게 됩니다. 그逻辑은 매우 간단하며, 클라이언트 Socket이 네트워크를 통해 `hello` 문자열을 보내면, 서버는 `Server: hello` 문자열로 응답합니다.

`Server`는 비동기 서버이므로 이벤트를 감시하는 방식으로 프로그램을 작성합니다. 해당 이벤트가 발생하면底层이 지정된 함수를 호출합니다. 새로운 `TCP` 연결이 들어올 경우 [onConnect](/server/events?id=onconnect) 이벤트 콜백이 실행되고, 어떤 연결이 서버에 데이터를 보내면 [onReceive](/server/events?id=onreceive) 함수가 콜백됩니다.

* 서버는 수천 수만 개의 클라이언트 연결을 동시에 처리할 수 있으며, `$fd`는 클라이언트 연결의 유일한 식별자입니다.
* `$server->send()` 메서드를 호출하여 클라이언트 연결에 데이터를 보내면, 매개변수는 `$fd` 클라이언트 식별자입니다.
* `$server->close()` 메서드를 호출하여 특정 클라이언트 연결을 강제로 종료할 수 있습니다.
* 클라이언트가 연결을 자발적으로 종료할 수 있으며, 이때 [onClose](/server/events?id=onclose) 이벤트 콜백이 트리거됩니다.

## 프로그램 실행

```shell
php tcpServer.php
```

명령행에서 `server.php` 프로그램을 실행하고 성공적으로 시작되면 `netstat` 도구를 사용하여 이미 `9501` 포트를 감시하고 있는지 확인할 수 있습니다.

이제 `telnet/netcat` 도구를 사용하여 서버에 연결할 수 있습니다.

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```

## 서버에 연결할 수 없는 간단한 검사 방법

* `Linux`에서 `netstat -an | grep 포트` 명령을 사용하여 포트가 이미 열려 있는지 `Listening` 상태인지를 확인합니다.
* 앞의 단계를 확인한 후, 방화벽 문제를 확인합니다.
* 서버가 사용하는 IP 주소에 주의하세요. 만약 `127.0.0.1` 회귀 주소라면, 클라이언트는 `127.0.0.1`만으로 연결할 수 있습니다.
* 알리바바 서비스나 텐센트 서비스를 사용하는 경우, 개발용 포트를 안전 권한 그룹에서 설정해야 합니다.

## TCP 패킷 경계 문제입니다.

[TCP 패킷 경계 문제](/learn?id=tcp数据包边界问题)를 참고하세요.
