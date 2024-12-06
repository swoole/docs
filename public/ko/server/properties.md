# 속성


### $setting

[Server->set()](/server/methods?id=set) 함수에 설정된 매개변수가 `Server::$setting` 속성에 저장됩니다. 콜백 함수에서 실행 매개변수의 값에 액세스할 수 있습니다. 이 속성은 `array` 유형의 배열입니다.

```php
Swoole\Server->setting
```

  * **예시**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```


### $connections

`TCP` 연결의 이터레이터로, `$server->connections`를 이용하여 현재 서버의 모든 연결을 `foreach` 루프를 통해 탐색할 수 있습니다. 이 속성은 [Server->getClientList](/server/methods?id=getclientlist)와 동일한 기능을 수행하지만 사용이 더욱 친화적입니다.

반복되는 요소는 단일 연결의 `fd`입니다.

```php
Swoole\Server->connections
```

!> `$connections` 속성은 이터레이터 객체로, PHP 배열이 아니므로 `var_dump`나 배열 인덱스를 이용하여 액세스할 수 없으며, 오직 `foreach` 루프를 통해만 탐색할 수 있습니다.

  * **기본 모드**

    * [SWOOLE_BASE](/learn?id=swoole_base) 모드에서는 `TCP` 연결을 교차 프로세스 간에서 사용할 수 없으므로, `BASE` 모드에서는 현재 프로세스 내에서만 `$connections` 이터레이터를 사용할 수 있습니다.

  * **예시**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "현재 서버에는 " . count($server->connections) . " 개의 연결이 있습니다\n";
```


### $host

현재 서버가 감시하는 호스트 주소의 `host`을 반환합니다. 이 속성은 `string` 유형의 문자열입니다.

```php
Swoole\Server->host
```


### $port

현재 서버가 감시하는 포트의 `port`를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server->port
```


### $type

현재 Server의 유형 `type`을 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server->type
```

!> 이 속성은 다음 값 중 하나를 반환합니다.

- `SWOOLE_SOCK_TCP` tcp ipv4 소켓

- `SWOOLE_SOCK_TCP6` tcp ipv6 소켓

- `SWOOLE_SOCK_UDP` udp ipv4 소켓

- `SWOOLE_SOCK_UDP6` udp ipv6 소켓

- `SWOOLE_SOCK_UNIX_DGRAM` unix 소켓 datagram
- `SWOOLE_SOCK_UNIX_STREAM` unix 소켓 stream 


### $ssl

현재 서버에서 `ssl`이 시작된지를 반환합니다. 이 속성은 `bool` 유형입니다.

```php
Swoole\Server->ssl
```


### $mode

현재 서버의 프로세스 모드 `mode`를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server->mode
```

!> 이 속성은 다음 값 중 하나를 반환합니다.

- `SWOOLE_BASE` 단일 프로세스 모드
- `SWOOLE_PROCESS` 멀티 프로세스 모드


### $ports

감시하는 포트 배열로, 서버가 여러 포트를 감시하는 경우 `Server::$ports`를 탐색하여 모든 `Swoole\Server\Port` 객체를 얻을 수 있습니다.

그중 `swoole_server::$ports[0]`는 생성자에서 설정된 메인 서버 포트입니다.

  * **예시**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```


### $master_pid

현재 서버의 메인 프로세스의 `PID`를 반환합니다.

```php
Swoole\Server->master_pid
```

!> `onStart/onWorkerStart` 이후에만 얻을 수 있습니다.

  * **예시**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->master_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```


### $manager_pid

현재 서버의 관리 프로세스의 `PID`를 반환합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server->manager_pid
```

!> `onStart/onWorkerStart` 이후에만 얻을 수 있습니다.

  * **예시**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->manager_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```    


### $worker_id

현재 `Worker` 프로세스의 번호를 얻습니다. [Task 프로세스](/learn?id=taskworker进程)도 포함됩니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server->worker_id
```
  * **예시**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num' => 8,
    'task_worker_num' => 4,
]);
$server->on('WorkerStart', function ($server, int $workerId) {
    if ($server->taskworker) {
        echo "task workerId：{$workerId}\n";
        echo "task worker_id：{$server->worker_id}\n";
    } else {
        echo "workerId：{$workerId}\n";
        echo "worker_id：{$server->worker_id}\n";
    }
});
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
});
$server->on('Task', function ($serv, $task_id, $reactor_id, $data) {
});
$server->start();
```
  * **알림**

    * 이 속성은 [onWorkerStart](/server/events?id=onworkerstart) 시의 `$workerId`와 동일합니다.
    * `Worker` 프로세스 번호 범위는 `[0, $server->setting['worker_num'] - 1]`입니다.
    * [Task 프로세스](/learn?id=taskworker进程) 번호 범위는 `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`입니다.

!> 작업 프로세스가 재시작된 후 `worker_id`의 값은 변경되지 않습니다.


### $taskworker

현재 프로세스가 `Task` 프로세스인지를 반환합니다. 이 속성은 `bool` 유형입니다.

```php
Swoole\Server->taskworker
```

  * **반환값**

    * `true`는 현재 프로세스가 `Task` 작업 프로세스임을 나타냅니다.
    * `false`는 현재 프로세스가 `Worker` 프로세스임을 나타냅니다.


### $worker_pid

현재 `Worker` 프로세스의 운영 체제 프로세스 `ID`를 얻습니다. 이는 `posix_getpid()`의 반환값과 동일합니다. 이 속성은 `int` 유형의 정수입니다.

```php
Swoole\Server->worker_pid
```
