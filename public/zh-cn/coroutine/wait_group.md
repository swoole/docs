# Coroutine\WaitGroup

在`Swoole4`中可以使用[Channel](/coroutine/channel)实现协程间的通信、依赖管理、协程同步。基于[Channel](/coroutine/channel)可以很容易地实现`Golang`的`sync.WaitGroup`功能。

!> `WaitGroup`只能用于单进程或者单线程中的多协程同步，主要作用还是让主协程等待全部子协程完成任务。

## 使用示例

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
    //启动第一个协程
    Coroutine::create(function () use ($wg, &$result) {
        //启动一个协程客户端client，请求淘宝首页
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
    //启动第二个协程
    Coroutine::create(function () use ($wg, &$result) {
        //启动一个协程客户端client，请求百度首页
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

    //挂起当前协程，等待所有任务完成后恢复
    $wg->wait();
    //这里 $result 包含了 2 个任务执行结果
    var_dump($result);
});
```

## 方法

### __construct
`WaitGroup`的构造函数。

```php
Swoole\Coroutine\WaitGroup->__construct()
```

### add
在子协程中使用，表示引用计数加1。

```php
Swoole\Coroutine\WaitGroup->add(): void
```

### done
在子协程中使用，表示任务已完成，引用计数减一。

```php
Swoole\Coroutine\WaitGroup->done(): void
```

### wait
在主协程中使用，主协程会等待子协程全部任务完成之后再退出。

```php
Swoole\Coroutine\WaitGroup->wait(): void
```
