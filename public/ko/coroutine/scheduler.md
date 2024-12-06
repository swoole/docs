# 코루틴/스케줄러

?> 모든 [코루틴](/coroutine)은 `코루틴 컨테이너` 안에서 [생성](/coroutine/coroutine?id=create)해야 합니다. `Swoole` 프로그램이 시작될 때 대부분의 경우 자동으로 `코루틴 컨테이너`가 생성됩니다. `Swoole`로 프로그램을 시작하는 방법은 총 세 가지입니다:

   - [비동기 스타일](/server/init)의 서버 프로그램의 [start](/server/methods?id=start) 메서드를 호출하는 방법으로, 이 시작 방식은 이벤트 콜백에서 `코루틴 컨테이너`를 생성합니다. 자세한 내용은 [enable_coroutine](/server/setting?id=enable_coroutine)를 참고하세요.
   - `Swoole`가 제공하는 두 개의 프로세스 관리 모듈인 [Process](/process/process)와 [Process\Pool](/process/process_pool)의 [start](/process/process_pool?id=start) 메서드를 호출하는 방법으로, 이 시작 방식은 프로세스가 시작될 때 `코루틴 컨테이너`를 생성합니다. 이 두 모듈의 생성자의 `enable_coroutine` 매개변수에 참고하세요.
   - 다른 직접 코루틴을 작성하여 프로그램을 시작하는 방법으로, 먼저 `코루틴 컨테이너`를 생성해야 합니다(`Coroutine\run()` 함수, 이는 java, c의 `main` 함수와 같습니다). 예를 들어:

* **전체 코루틴 HTTP 서비스를 시작합니다**

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
echo 1;//실행되지 않습니다
```

* **2개의 코루틴을 병행하여 일을 합니다**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function() {
        var_dump(file_get_contents("http://www.xinhuanet.com/"));
    });

    Coroutine::create(function() {
        Coroutine::sleep(1);
        echo "done\n";
    });
});
echo 1;//실행됩니다
```

!> `Swoole v4.4+` 버전에서 사용할 수 있습니다.

!> `Coroutine\run()`를 중첩해서 사용할 수 없습니다.  
`Coroutine\run()` 안의 로직에 미처 처리하지 않은 이벤트가 `Coroutine\run()` 이후에 발생하면 [EventLoop](learn?id=什么是eventloop)에서 진행되며, 이후의 코드는 실행되지 않습니다. 반대로, 이벤트가 없을 경우 계속해서 아래로 진행되며, 다시 `Coroutine\run()`를 사용할 수 있습니다.

위의 `Coroutine\run()` 함수는 사실 `Swoole\Coroutine\Scheduler` 클래스(코루틴 스케줄러 클래스)의 밀봉입니다. 세부 사항을 알고 싶은 학생들은 `Swoole\Coroutine\Scheduler`의 메서드를 확인해보세요:


### set()

?> **코루틴 실행 시 매개변수를 설정합니다.** 

?> `Coroutine::set` 메서드의 별명입니다. 자세한 내용은 [Coroutine::set](/coroutine/coroutine?id=set) 문서를 참고하세요.

```php
Swoole\Coroutine\Scheduler->set(array $options): bool
```

  * **예제**

```php
$sch = new Swoole\Coroutine\Scheduler;
$sch->set(['max_coroutine' => 100]);
```


### getOptions()

?> **설정된 코루틴 실행 시 매개변수를 가져옵니다.** Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다

?> `Coroutine::getOptions` 메서드의 별명입니다. 자세한 내용은 [Coroutine::getOptions](/coroutine/coroutine?id=getoptions) 문서를 참고하세요.

```php
Swoole\Coroutine\Scheduler->getOptions(): null|array
```


### add()

?> **미션을 추가합니다.** 

```php
Swoole\Coroutine\Scheduler->add(callable $fn, ... $args): bool
```

  * **매개변수** 

    * **`callable $fn`**
      * **기능**：콜백 함수
      * **기본값**：없음
      * **기타값**：없음

    * **`... $args`**
      * **기능**：선택적 매개변수로 코루틴에 전달됩니다
      * **기본값**：없음
      * **기타값**：없음

  * **예제**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;
$scheduler->add(function ($a, $b) {
    Coroutine::sleep(1);
    echo assert($a == 'hello') . PHP_EOL;
    echo assert($b == 12345) . PHP_EOL;
    echo "Done.\n";
}, "hello", 12345);

$scheduler->start();
```
  
  * **주의**

    !> `go` 함수와 달리, 여기서 추가한 코루틴은 즉시 실행되지 않고, `start` 메서드를 호출할 때 함께 시작하여 실행됩니다. 프로그램에서 코루틴만 추가하고 `start`를 호출하지 않는 경우, 코루틴 함수 `$fn`은 실행되지 않습니다.


### parallel()

?> **병행 미션을 추가합니다.** 

?> `add` 메서드와 달리, `parallel` 메서드는 병행 코루틴을 생성합니다. `start`할 때 `$num`개의 `$fn` 코루틴을 동시에 시작하여 병행으로 실행합니다.

```php
Swoole\Coroutine\Scheduler->parallel(int $num, callable $fn, ... $args): bool
```

  * **매개변수** 

    * **`int $num`**
      * **기능**：코루틴을 시작하는 개수
      * **기본값**：없음
      * **기타값**：없음

    * **`callable $fn`**
      * **기능**：콜백 함수
      * **기본값**：없음
      * **기타값**：없음

    * **`... $args`**
      * **기능**：선택적 매개변수로 코루틴에 전달됩니다
      * **기본값**：없음
      * **기타값**：없음

  * **예제**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;

$scheduler->parallel(10, function ($t, $n) {
    Coroutine::sleep($t);
    echo "Co ".Coroutine::getCid()."\n";
}, 0.05, 'A');

$scheduler->start();
```

### start()

?> **프로그램을 시작합니다.** 

?> `add`와 `parallel` 메서드로 추가한 코루틴 미션을 탐색하고 실행합니다.

```php
Swoole\Coroutine\Scheduler->start(): bool
```

  * **반환값**

    * 성공적으로 시작하면 모든 추가한 미션이 실행되고, 모든 코루틴이 종료될 때 `start`는 `true`를 반환합니다
    * 시작에 실패하면 `false`를 반환하며, 이유는 이미 시작되었거나 다른 스케줄러가 이미 생성되어 더 이상 생성할 수 없기 때문입니다
