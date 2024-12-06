# 코루틴/위드그룹

`Swoole4`에서는 [채널](/coroutine/channel)을 사용하여 코루틴 간의 통신, 의존 관리, 코루틴 동기화를 실현할 수 있습니다. [채널](/coroutine/channel)을 기반으로 `Golang`의 `sync.WaitGroup` 기능을 쉽게 구현할 수 있습니다.

## 구현 코드

> 이 기능은 PHP로 작성된 기능이며, C/C++ 코드가 아닙니다. 구현 원본은 [라이브러리](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php)에 있습니다.

* `add` 메서드는 카운트를 증가시킵니다.
* `done`는 작업이 완료되었음을 나타냅니다.
* `wait`는 모든 작업이 완료될 때까지 기다리며 현재 코루틴의 실행을 재개합니다.
* `WaitGroup` 객체는 재사용 가능하며, `add`, `done`, `wait` 후에 다시 사용할 수 있습니다.

## 사용 예제

```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $wg = new WaitGroup();
    $result = [];

    $wg->add();
    // 첫 번째 코루틴 시작
    Coroutine::create(function () use ($wg, &$result) {
        // 코루틴 클라이언트 client를 시작하여 타오바오 홈페이지에 요청을 보냅니다.
        $cli = new Client('www.taobao.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.taobao.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['taobao'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    $wg->add();
    // 두 번째 코루틴 시작
    Coroutine::create(function () use ($wg, &$result) {
        // 코루틴 클라이언트 client를 시작하여 바이두 홈페이지에 요청을 보냅니다.
        $cli = new Client('www.baidu.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.baidu.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['baidu'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    // 현재 코루틴을 일시정지하고 모든 작업이 완료될 때까지 기다립니다.
    $wg->wait();
    // 여기서 $result에는 2개의 작업 실행 결과가 포함되어 있습니다.
    var_dump($result);
});
```
