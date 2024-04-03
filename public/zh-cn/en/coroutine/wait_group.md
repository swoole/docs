# Coroutine\WaitGroup

In `Swoole4`, you can use [Channel](/coroutine/channel) to achieve communication between coroutines, dependency management, and coroutine synchronization. Based on [Channel](/coroutine/channel), you can easily implement the functionality of `sync.WaitGroup` in `Golang`.

## Implementation Code

> This functionality is implemented using PHP, not C/C++ code. The source code for the implementation is located in [Library](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php).

* The `add` method increases the counter.
* `done` indicates that the task is completed.
* `wait` waits for all tasks to complete and resumes the execution of the current coroutine.
* `WaitGroup` objects can be reused; `add`, `done`, and `wait` can be used again after initial usage.

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
        // Start a coroutine client, request the homepage of Taobao
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
        // Start a coroutine client, request the homepage of Baidu
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

    // Suspend the current coroutine, wait for all tasks to complete, then resume
    $wg->wait();
    // Here, $result contains the results of 2 tasks
    var_dump($result);
});
```
