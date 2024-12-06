# 프로그래밍 참고 사항

이 장은 코루틴 프로그래밍과 동기화 프로그래밍의 차이점과 주의해야 할 사항을 자세히 설명합니다.


## 주의 사항

* 코드에서 `sleep` 및 기타 수면 함수를 실행하지 말아야 합니다. 이로 인해 전체 프로세스가 막힐 수 있습니다; 코루틴에서는 [Co::sleep()](/coroutine/system?id=sleep)를 사용하거나 [일괄 코루틴화](/runtime) 후에 `sleep`를 사용할 수 있습니다; 참고: [sleep/usleep의 영향](/getting_started/notice?id=sleepusleep의 영향)
* `exit/die`는 위험하며, `Worker` 프로세스의 종료를 초래할 수 있습니다; 참고: [exit/die 함수의 영향](/getting_started/notice?id=exitdie函数的影响)
* 치명적인 오류를 포착할 수 있도록 `register_shutdown_function`을 사용하여 프로세스가 비정상적으로 종료될 때 청소 작업을 수행할 수 있습니다; 참고: [Server 운영 중 치명적 오류 포착](/getting_started/notice?id=捕获server运行期致命错误)
* `PHP` 코드에서 예외가 던지면, 콜백 함수에서 `try/catch`를 사용하여 예외를 포착해야 합니다. 그렇지 않으면 작업 프로세스가 종료될 수 있습니다; 참고: [예외와 오류 포착](/getting_started/notice?id=捕获异常和错误)
* `set_exception_handler`는 지원되지 않으며, 예외를 처리해야 할 때는 `try/catch` 방식으로 해야 합니다;
* `Worker` 프로세스는 동일한 `Redis` 또는 `MySQL`와 같은 네트워크 서비스 클라이언트를 공유해서는 안 됩니다. `Redis/MySQL` 연결을 만드는 관련 코드는 `onWorkerStart` 콜백 함수에 배치할 수 있습니다. 참고: [하나의 Redis 또는 MySQL 연결을 공유할 수 있는지](/question/use?id=是否可以共用一个redis或mysql连接)


## 코루틴 프로그래밍

`Coroutine` 기능을 사용하는 경우, [코루틴 프로그래밍 참고 사항](/coroutine/notice)를 주의 깊게 읽어 주십시오.


## 병렬 프로그래밍

`동기화 막힘` 모드와 달리, `코루틴` 모드에서 프로그램은 **병렬로 실행**됩니다. 동시에 `Server`에는 여러 요청이 존재하므로 **응용 프로그램은 각 클라이언트나 요청에 대해 다른 자원과 컨텍스트를 만들어야 합니다**. 그렇지 않으면 다른 클라이언트와 요청 사이에서 데이터와 논리적 혼란을 초래할 수 있습니다.


## 클래스/함수 중복 정의

초보자들은 이 오류를 잘 저지를 수 있습니다. `Swoole`는 메모리에 상주하기 때문에, 클래스/함수 정의 파일을 로딩한 후에는 해제되지 않습니다. 따라서 클래스/함수를 포함하는 php 파일을 포함할 때는 반드시 `include_once` 또는 `require_once`를 사용해야 하며, 그렇지 않으면 `cannot redeclare function/class`의 치명적 오류가 발생합니다.


## 메모리 관리

!> `Server` 또는 기타 상주 프로세스를 작성할 때는 특별히 주의해야 합니다.

`PHP` 가디언 프로세스와 일반 `Web` 프로그램의 변수 수명 주기, 메모리 관리 방식은 완전히 다릅니다. `Server`가 시작된 후 메모리 관리의 기본 원리는 일반 php-cli 프로그램과 동일합니다. 자세한 내용은 `Zend VM` 메모리 관리 관련 글을 참고하십시오.


### 지역 변수

이벤트 콜백 함수가 반환된 후, 모든 지역 객체와 변수는 즉시 회수되며, `unset`을 할 필요가 없습니다. 변수가 자원 유형인 경우, 해당 자원도 PHP 하층에서 해제됩니다.

```php
function test()
{
	$a = new Object;
	$b = fopen('/data/t.log', 'r+');
	$c = new swoole_client(SWOOLE_SYNC);
	$d = new swoole_client(SWOOLE_SYNC);
	global $e;
	$e['client'] = $d;
}
```

* `$a`, `$b`, `$c`는 모두 지역 변수로, 이 함수가 `return`될 때, 이 `3`개의 변수는 즉시 해제되며, 해당 메모리는 즉시 해제되고, 열린 IO 자원 파일 핸들은 즉시 닫힙니다.
* `$d`도 지역 변수이지만, `return` 전에 전역 변수 `$e`에 저장되어 있기 때문에 해제되지 않습니다. `unset($e['client'])`를 실행하고, `$d` 변수를 참조하는 다른 `PHP 변수`가 없을 경우, `$d`는 해제됩니다.


### 전역 변수

`PHP`에는 `3`가지 전역 변수가 있습니다.

* `global` 키워드로 선언된 변수
* `static` 키워드로 선언된 클래스 정적 변수, 함수 정적 변수
* `PHP`의 초기화 전역 변수, 예를 들어 `$_GET`, `$_POST`, `$GLOBALS` 등

전역 변수와 객체, 클래스 정적 변수, `Server` 객체에 보존되는 변수는 해제되지 않습니다. 이러한 변수와 객체의 파기 작업을程序员가 스스로 처리해야 합니다.

```php
class Test
{
	static $array = array();
	static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
	Test::$array[] = $fd;
	Test::$string .= $data;
}
```

* 이벤트 콜백 함수에서는 비 지역 변수의 `array` 유형의 값에 특별히 주의해야 합니다. 일부 작업, 예를 들어 `TestClass::$array[] = "string"`은 메모리 누수의 원인이 될 수 있으며, 심각한 경우 메모리 오버플로우가 발생할 수 있습니다. 필요한 경우 대규모 배열을 청소하는 것에 주의해야 합니다.

* 이벤트 콜백 함수에서는 비 지역 변수의 문자열을 연결하는 작업에서 메모리 누수의 원인이 될 수 있으며, 예를 들어 `TestClass::$string .= $data`는 메모리 누수의 원인이 될 수 있으며, 심각한 경우 메모리 오버플로우가 발생할 수 있습니다.


### 해결책

* 동기화 막힘 및 요청 반응형 무状态的 `Server` 프로그램은 [max_request](/server/setting?id=max_request)와 [task_max_request](/server/setting?id=task_max_request)를 설정할 수 있습니다. [Worker 프로세스](/learn?id=worker进程) / [Task 프로세스](/learn?id=taskworker进程)가 실행을 종료하거나 작업 제한에 도달한 후에 프로세스는 자동으로 종료되며, 해당 프로세스의 모든 변수/객체/자원은 해제되어 회수됩니다.
* 프로그램 내에서 `onClose` 또는 타이머를 설정하여 적시에 `unset`을 사용하여 변수를 청소하고 자원을 회수합니다.


## 프로세스 격리

프로세스 격리는 많은 초보자들이 자주 만나는 문제입니다. 글로벌 변수의 값을 수정했는데 왜 효과가 없는 걸까요? 이유는 글로벌 변수가 다른 프로세스에 있어서 메모리가 격리되어 있기 때문입니다. 그래서 효과가 없습니다.

그러므로 `Swoole`를 사용하여 `Server` 프로그램을 개발할 때는 `프로세스 격리` 문제를 이해해야 합니다. `Swoole\Server` 프로그램의 다른 `Worker` 프로세스 사이는 격리되어 있으며, 프로그래밍 시 글로벌 변수, 타이머, 이벤트 리스닝을 조작할 때는 현재 프로세스 내에서만 유효합니다.

* 다른 프로세스 중에서 PHP 변수는 공유되지 않습니다. 글로벌 변수라도 A 프로세스 내에서 값을 수정했을 때, B 프로세스 내에서는 무효합니다
* 다른 Worker 프로세스 내에서 데이터를 공유하려면 `Redis`, `MySQL`, `파일`, `Swoole\Table`, `APCu`, `shmget` 등의 도구를 사용할 수 있습니다
* 다른 프로세스의 파일 핸들은 격리되어 있기 때문에 A 프로세스에서 생성한 소켓 연결이나 열린 파일은 B 프로세스 내에서 무효하며, 심지어 그 fd를 B 프로세스에 보낼 수도 없습니다

예시:

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$i = 1;

$server->on('Request', function ($request, $response) {
	global $i;
    $response->end($i++);
});

$server->start();
```

다중 프로세스 서버에서 `$i` 변수는 글로벌 변수(`global`)이지만, 프로세스 격리의 이유로 인해 작동하지 않습니다. 가정에 4개의 작업 프로세스가 있다고 가정합니다. A 프로세스에서 `$i++`를 수행하면 실제로 A 프로세스 내의 `$i`만이 `2`가 되며, 나머지 다른 3개 프로세스 내의 `$i` 변수의 값은 여전히 `1`입니다.

올바른 방법은 `Swoole`가 제공하는 [Swoole\Atomic](/memory/atomic) 또는 [Swoole\Table](/memory/table) 데이터 구조를 사용하여 데이터를 저장하는 것입니다. 위의 코드는 `Swoole\Atomic`을 사용하여 실행할 수 있습니다.

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$atomic = new Swoole\Atomic(1);

$server->on('Request', function ($request, $response) use ($atomic) {
    $response->end($atomic->add(1));
});

$server->start();
```

!> `Swoole\Atomic` 데이터는 공유 메모리에 구축되어 있으며, `add` 방법으로 `1`을 추가할 때, 다른 작업 프로세스 내에서도 유효합니다

`Swoole`가 제공하는 [Table](/memory/table), [Atomic](/memory/atomic), [Lock](/memory/lock) 구성 요소는 다중 프로세스 프로그래밍에 사용될 수 있지만, `Server->start` 이전에 생성해야 합니다. 또한 `Server`가 유지하는 `TCP` 클라이언트 연결도 프로세스 간에서 조작할 수 있습니다, 예를 들어 `Server->send`와 `Server->close`입니다.
## stat 캐시 청소

PHP 하단에서 `stat` 시스템 호출에 `Cache`가 추가되어 `stat`, `fstat`, `filemtime` 등의 함수를 사용할 때, 하단에서 캐시를 명중할 수 있으며, 역사적 데이터를 반환할 수 있습니다.

[clearstatcache](https://www.php.net/manual/ko/function.clearstatcache.php) 함수를 사용하여 파일 `stat` 캐시를 청소할 수 있습니다.


## mt_rand 랜덤수

Swoole에서 부모 프로세스 내에서 `mt_rand`를 호출하면, 다른 자식 프로세스 내에서 `mt_rand`를 호출해도 같은 결과가 반환되므로, 각 자식 프로세스 내에서 `mt_srand`를 호출하여 재배기를 해야 합니다.

!> `shuffle`와 `array_rand` 등 랜덤수에 의존하는 PHP 함수도 동일하게 영향을 받습니다.  

예시:

```php
mt_rand(0, 1);

// 시작
$worker_num = 16;

// 프로세스 분할
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

// 비동기 프로세스 실행
function child_async(Swoole\Process $worker) {
    mt_srand(); // 재배기
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
```


## 예외 및 오류捕获


###捕获 가능한 예외/오류

PHP에는 대략 세 가지 유형의捕获 가능한 예외/오류가 있습니다.

1. `Error`: PHP 커널이 발생시킨 오류 전용 유형으로, 클래스가 존재하지 않거나, 함수가 존재하지 않거나, 함수 매개변수가 잘못되었을 때 이 유형의 오류가 발생합니다. PHP 코드에서는 `Error` 클래스를 예외로 사용해서는 안됩니다.
2. `Exception`: 응용 개발자가 사용해야 할 예외의 기초 클래스입니다.
3. `ErrorException`: 이 예외 기초 클래스는 PHP의 `Warning`/`Notice` 등의 정보를 `set_error_handler`를 통해 예외로 변환하는 데 전용되어 있습니다. PHP의 미래 계획은 모든 `Warning`/`Notice`를 예외로 전환하여, PHP 프로그램이 다양한 오류를 더 잘 및 더 통제할 수 있도록 하는 것입니다.

!> 위의 모든 클래스는 `Throwable` 인터페이스를 구현하고 있으므로, `try {} catch(Throwable $e) {}`를 통해 모든 발생 가능한 예외/오류를捕获할 수 있습니다.

예시1:
```php
try {
	test();
} 
catch(Throwable $e) {
	var_dump($e);
}
```
예시2:
```php
try {
	test();
}
catch (Error $e) {
	var_dump($e);
}
catch(Exception $e) {
	var_dump($e);
}
```


###捕获 불가한 fatal error 및 예외

PHP 오류의 중요한 수준 중 하나로, 예외/오류가捕获되지 않았을 때, 메모리가 부족할 때 또는 일부编译 오류(상속된 클래스가 존재하지 않음) 등은 `E_ERROR` 수준으로 `Fatal Error`를 발생시킵니다. 이것은 프로그램에서 되돌릴 수 없는 오류가 발생할 때만 트리거됩니다. PHP 프로그램은 이러한 수준의 오류를捕获할 수 없으며, 오직 `register_shutdown_function`을 통해 후속에 일부 처리 작업을 수행할 수 있습니다.


###协程에서 runtime 예외/오류捕获

Swoole4의协程 프로그래밍에서, 특정协程의 코드에서 오류가 발생하면 전체 프로세스를 종료하고 프로세스의 모든协程이 실행을 중단합니다.协程의 최상위 공간에서 먼저 `try/catch`를 통해 예외/오류를捕获하여 오류가 발생한协程만 종료할 수 있습니다.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    //协程1의 오류는协程2에 영향을 미치지 않습니다.
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
```


### Server runtime fatal error捕获

Server가 runtime에 fatal error를 발생시킬 경우, 클라이언트 연결은 응답을 받을 수 없습니다. 예를 들어 Web 서버의 경우, fatal error가 발생하면 클라이언트에게 HTTP 500 오류 메시지를 보낼 수 있어야 합니다.

PHP에서는 `register_shutdown_function` + `error_get_last` 두 개의 함수를 통해 fatal error를捕获하고, 오류 메시지를 클라이언트 연결에 보낼 수 있습니다.

 구체적인 코드 예시는 다음과 같습니다:

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    register_shutdown_function(function () use ($response) {
        $error = error_get_last();
        var_dump($error);
        switch ($error['type'] ?? null) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                // 로그 또는 전송:
                // error_log($message);
                // $server->send($fd, $error['message']);
                $response->status(500);
                $response->end($error['message']);
                break;
        }
    });
    exit(0);
});
$http->start();
```


## 사용 영향


### sleep/usleep의 영향

비동기 IO 프로그램에서는 **sleep/usleep/time_sleep_until/time_nanosleep를 사용할 수 없습니다.** (아래에서 `sleep`는 모든 수면 함수를 가리킵니다.)

* `sleep` 함수는 프로세스를 수면 방해에 빠뜨립니다.
* 지정된 시간 후에만 운영체제가 현재 프로세스를 다시 깨웁니다.
* `sleep` 중에는 신호만이 중단할 수 있습니다.
* Swoole의 신호 처리기는 `signalfd`를 기반으로 구현되어 있기 때문에 신호를 보내려 해도 `sleep`를 중단시킬 수 없습니다.

Swoole에서 제공하는 [Swoole\Event::add](/event?id=add), [Swoole\Timer::tick](/timer?id=tick), [Swoole\Timer::after](/timer?id=after), [Swoole\Process::signal](/process/process?id=signal)는 프로세스가 `sleep` 상태가 되면 작동을 중단합니다. [Swoole\Server](/server/tcp_init)도 새로운 요청을 처리할 수 없습니다.

#### 예시

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    sleep(100);
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> [onReceive](/server/events?id=onreceive) 이벤트에서 `sleep` 함수를 실행하면, Server는 100초 동안 더 이상 어떤 클라이언트 요청도 받을 수 없습니다.


### exit/die 함수의 영향

Swoole 프로그램에서는 `exit/die`를 사용할 수 없습니다. PHP 코드에 `exit/die`가 있을 경우, 현재 작업 중인 [Worker 프로세스](/learn?id=worker进程), [Task 프로세스](/learn?id=taskworker进程), [User 프로세스](/server/methods?id=addprocess), 그리고 `Swoole\Process` 프로세스는 즉시 종료됩니다.

`exit/die`를 사용하면 Worker 프로세스가 예외로 종료되어 Master 프로세스에 의해 다시 생성되며, 결국 프로세스가 계속 종료되고 다시 시작하며 많은 경고 로그가 발생합니다.

`try/catch` 방식으로 `exit/die`를 대체하여 실행을 중단하고 PHP 함수 호출 스택에서 뛰어내리는 것이 권장됩니다.

```php
Swoole\Coroutine\run(function () {
    try
    {
        exit(0);
    } catch (Swoole\ExitException $e)
    {
        echo $e->getMessage()."\n";
    }
});
```

!> Swoole\ExitException은 Swoole v4.1.0 이상 버전에서 직접적으로协程와 Server에서 PHP의 `exit`를 사용할 수 있도록 지원하며, 이때 하단에서 자동으로捕获 가능한 Swoole\ExitException를 던집니다. 개발자는 필요한 위치에서捕获하여 원래 PHP와 같은 종료 논리를 구현할 수 있습니다. 구체적인 사용 방법은 [协程 종료](/coroutine/notice?id=退出协程)을 참고하세요.

예외 처리 방식은 `exit/die`보다 친화적입니다. 왜냐하면 예외는 통제 가능하기 때문입니다. `exit/die`는 통제 불가합니다. 가장 바깥에서 `try/catch`를 통해 예외를捕获하면 현재의 작업만 종료할 수 있습니다. Worker 프로세스는 새로운 요청을 계속 처리할 수 있고, `exit/die`는 프로세스를 직접 종료하여 현재 프로세스가 보관한 모든 변수와 자원은 모두 파괴됩니다. 프로세스 내에 다른 작업이 더 처리해야 한다면, `exit/die`를 만나면 모두 버려집니다.
### while循环의 영향

비동기 프로그램이 죽은 고리 Loop에 부딪힐 경우, 이벤트는 발동하지 못합니다. 비동기 IO 프로그램은 `Reactor 모델`을 사용하며, 실행 중에는 반드시 `reactor->wait`에서 대기해야 합니다. 만약 죽은 고리 Loop에 빠지면, 프로그램의 제어권이 `while` 문 안에 있어 `reactor`가 제어권을 얻지 못하고 이벤트를 감지할 수 없으므로 IO 이벤트 콜백 함수도 발동하지 못합니다.

!> 밀집 계산의 코드는 어떠한 IO 운영도 하지 않기 때문에 비활성화되지 않을 수 없습니다.  

#### 실습 프로그램

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactorId, $data) {
    $i = 0;
    while(1)
    {
        $i++;
    }
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> [onReceive](/server/events?id=onreceive) 이벤트에서 죽은 고리 Loop가 실행되어 `server`는 더 이상 어떤 클라이언트 요청도 받을 수 없으며, 고리가 끝나기를 기다려야만 새로운 이벤트 처리를 계속할 수 있습니다.
