# 함수 별명 모음

## 코루틴 간소명

코루틴 관련 `API`의 이름을 간결하게 쓰기 위해 사용됩니다. `php.ini`에서 `swoole.use_shortname=On/Off` 설정을 변경하여 간소명을 사용할지 말지 선택할 수 있으며, 기본은 사용합니다.

모든 `Swoole\Coroutine` 접두사의 클래스명은 `Co`로 매핑됩니다. 또한 다음과 같은 몇 가지 매핑도 있습니다:

### 코루틴 생성

```php
//Swoole\Coroutine::create은 go 함수와 동일
go(function () {
	Co::sleep(0.5);
	echo 'hello';
});
go('test');
go([$object, 'method']);
```

### 채널 조작

```php
//Coroutine\Channel은 간단히 chan으로 줄일 수 있습니다
$c = new chan(1);
$c->push($data);
$c->pop();
```

### 지연 실행

```php
//Swoole\Coroutine::defer는 직접 defer를 사용할 수 있습니다
defer(function () use ($db) {
    $db->close();
});
```

## 간소명 방법

!> 다음 방식에서 `go`와 `defer`는 Swoole 버전 >= `v4.6.3`에서 사용할 수 있습니다

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\defer;

run(function () {
    defer(function () {
        echo "co1 end\n";
    });
    sleep(1);
    go(function () {
        usleep(100000);
        defer(function () {
            echo "co2 end\n";
        });
        echo "co2\n";
    });
    echo "co1\n";
});
```

## 코루틴 시스템 API

`4.4.4` 버전부터 시스템 관련 코루틴 `API`는 `Swoole\Coroutine` 클래스에서 `Swoole\Coroutine\System` 클래스로 이관되었습니다. 새로운 모듈로 독립되었습니다. 하향 호환을 위해 여전히 `Coroutine` 클래스 위에 별명 방법을 유지하고 있습니다.

* 예를 들어 `Swoole\Coroutine::sleep`는 `Swoole\Coroutine\System::sleep`에 해당합니다
* 예를 들어 `Swoole\Coroutine::fgets`는 `Swoole\Coroutine\System::fgets`에 해당합니다

## 클래스 간소 별명 매핑 관계

!>命名 공간 스타일을 사용하는 것이 권장됩니다.

| 대시 스타일의 클래스명 | 命名 공간 스타일 |
| ------------------------ | ---------------- |
| swoole_server            | Swoole\Server    |
| swoole_client            | Swoole\Client    |
| swoole_process           | Swoole\Process   |
| swoole_timer             | Swoole\Timer     |
| swoole_table             | Swoole\Table     |
| swoole_lock              | Swoole\Lock      |
| swoole_atomic            | Swoole\Atomic    |
| swoole_atomic_long       | Swoole\Atomic\Long|
| swoole_buffer            | Swoole\Buffer    |
| swoole_redis             | Swoole\Redis     |
| swoole_error             | Swoole\Error     |
| swoole_event             | Swoole\Event     |
| swoole_http_server       | Swoole\Http\Server|
| swoole_http_client       | Swoole\Http\Client|
| swoole_http_request      | Swoole\Http\Request|
| swoole_http_response     | Swoole\Http\Response|
| swoole_websocket_server  | Swoole\WebSocket\Server|
| swoole_connection_iterator| Swoole\Connection\Iterator|
| swoole_exception         | Swoole\Exception  |
| swoole_http2_request     | Swoole\Http2\Request|
| swoole_http2_response    | Swoole\Http2\Response|
| swoole_process_pool      | Swoole\Process\Pool|
| swoole_redis_server      | Swoole\Redis\Server|
| swoole_runtime           | Swoole\Runtime   |
| swoole_server_port       | Swoole\Server\Port|
| swoole_server_task       | Swoole\Server\Task|
| swoole_table_row         | Swoole\Table\Row  |
| swoole_timer_iterator    | Swoole\Timer\Iterator|
| swoole_websocket_closeframe| Swoole\Websocket\Closeframe|
| swoole_websocket_frame    | Swoole\Websocket\Frame|
