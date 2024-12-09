# Coroutine\WaitGroup

In `Swoole4`, you can use [Channel](/coroutine/channel) to implement inter-coroutine communication, dependency management, and coroutine synchronization. Based on [Channel](/coroutine/channel), it is easy to implement the `sync.WaitGroup` functionality from `Golang`.

## Implementation Code

> This feature is written in PHP and not C/C++ code. The implementation source code can be found in the [Library](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php).

* The `add` method increments the counter.
* The `done` method indicates that a task has been completed.
* The `wait` method waits for all tasks to complete and then resumes the execution of the current coroutine.
* `WaitGroup` objects can be reused; after calling `add`, `done`, and `wait`, they can be used again.

## Usage Example

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
    // Start the first coroutine
    Coroutine::create(function () use ($wg, &$result) {
        // Start a coroutine client to request the homepage of Taobao
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
    // Start the second coroutine
    Coroutine::create(function () use ($wg, &$result) {
        // Start a coroutine client to request the homepage of Baidu
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

    // Suspend the current coroutine and wait for all tasks to complete before resuming
    $wg->wait();
    // Here $result contains the execution results of 2 tasks
    var_dump($result);
});
```
