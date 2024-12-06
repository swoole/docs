# 서버(코루outine 스타일) <!-- {docsify-ignore-all} -->

`Swoole\Coroutine\Server`는 [비동기 스타일](/server/init)의 서버와 달리 완전히 코루outine화된 서비스를 제공하는 서버로, [전체 예제](/coroutine/server?id=전체-예제)를 참고하세요.

## 장점:

- 이벤트 콜백 함수를 설정할 필요가 없습니다. 연결 수립, 데이터 수신, 데이터 전송, 연결 종료는 순차적이며, 비동기 스타일의 병렬 문제는 발생하지 않습니다. 예를 들어:

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

// 연결 진입 이벤트 监听
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);//此处OnConnect的协程会挂起
    Co::sleep(5);//此处sleep模拟connect比较慢的情况
    $redis->set($fd,"fd $fd connected");
});

// 데이터 수신 이벤트 监听
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);//此处onReceive的协程会挂起
    var_dump($redis->get($fd));//有可能onReceive的协程的redis连接先建立好了，上面的set还没有执行，此处get会是false，产生逻辑错误
});

// 연결 종료 이벤트 监听
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// 서버 시작
$serv->start();
```

위의 비동기 스타일의 서버는 이벤트의 순서를 보장할 수 없으며, 즉 `onConnect`이 실행되고 나서야 `onReceive`가 들어갈 수 없습니다. 코루outine화가 적용되면 `onConnect`과 `onReceive` 콜백이 자동으로 코루outine을 생성하고, I/O가 발생하면 [코루outine 스케줄러](/coroutine?id=코루outine-스케줄러)가 작동합니다. 비동기 스타일의 서버는 스케줄러의 순서를 보장할 수 없지만, 코루outine 스타일의 서버는 이 문제가 없습니다.

- 동적으로 서비스를 시작하고 중지할 수 있습니다. 비동기 스타일의 서비스는 `start()`가 호출된 후에는 아무것도 할 수 없지만, 코루outine 스타일의 서비스는 동적으로 서비스를 시작하고 중지할 수 있습니다.

## 단점:

- 코루outine 스타일의 서비스는 자동으로 여러 프로세스를 생성하지 않으므로, [Process\Pool](/process/process_pool) 모듈과 함께 사용하여 멀티코어의 이점을 발휘할 수 없습니다.
- 코루outine 스타일의 서비스는 사실 [Co\Socket](/coroutine_client/socket) 모듈의 포장이기 때문에, 코루outine 스타일을 사용하려면 소켓 프로그래밍에 어느 정도의 경험이 필요합니다.
- 현재 포장 레벨은 비동기 스타일의 서버에 비해 높지 않으며, 일부 기능은 수동으로 구현해야 합니다. 예를 들어, `reload` 기능은 신호를 감시하여 로직을 수행해야 합니다.
