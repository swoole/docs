# Swoole\Thread

从 `6.0` 版本开始提供了多线程支持，可使用线程 API 来代替多进程。相比多进程，`Thread` 提供了更丰富的并发数据容器，
在开发游戏服务器、通信服务器方面更方便。

## 编译
- `PHP` 必须为 `ZTS` 模式，编译 `PHP` 时需要加入 `--enable-zts`
- 编译 `Swoole` 时需要增加 `--enable-swoole-thread` 编译选项

## 兼容性问题

`--enable-swoole-thread` 编译参数开启后，部分特性在子线程中无法使用：

- `Runtime::enableCoroutine()/setHookFlags()`：无法在子线程中开启或关闭协程 `Hook`
- `swoole_async_set()` 无法在子线程中被调用

## 查看信息

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)` 表示已启用线程安全

```shell
php --ri swoole

swoole

Swoole => enabled
...
thread => enabled
...
```

`thread => enabled` 表示已开启多线程支持

## 创建线程

```php
new Swoole\Thread(string $script_file, array ...$argv);
```

`Swoole` 线程与 `Node.js` `Worker Thread` 是相似的，在子线程中会创建一个全新的 `ZendVM` 环境。 子线程不会从父线程继承任何资源，因此在子线程中下列内容已被清空，需要重新创建或设置：
- 已加载的 `PHP` 文件，需要重新 `include/require` 加载
- `autoload` 函数，需要重新注册
- 类、函数、常量，将被清空，需重新加载 `PHP` 文件创建
- 全局变量，例如 `$GLOBALS`、`$_GET/$_POST` 等，将被清空
- 类的静态属性、函数的静态变量，将重置为初始值
- `php.ini` 选项，例如 `error_reporting()` 需要在子线程中重新设置

必须使用线程参数传递数据给子线程。在子线程中依然可以创建新的线程。

### 参数
- `$script_file`: 线程启动后要执行的脚本
- `...$argv`：传递线程参数，必须是可序列化的变量，无法传递 `resource` 资源句柄，在子线程中可使用 `Swoole\Thread::getArguments()` 获取

### 返回值
返回 `Thread` 对象，在父线程中可对子线程进行 `join()` 等操作。

线程对象析构时会自动执行 `join()` 等待子线程退出。这可能会引起阻塞，可使用 `$thread->detach()` 方法
使子线程脱离父线程，独立运行。

### 异常
- `Swoole\Exception`：线程创建失败抛出此异常，请检查错误信息获取失败原因


### 实例
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

if (empty($args)) {
    # 父线程
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Swoole\Thread(__FILE__, $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # 子线程
    echo "Thread #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```


## 常量
- `Thread::HARDWARE_CONCURRENCY` 获取硬件系统支持的并发线程数，即 `CPU` 核数

## Server
- 所有工作进程将使用线程来运行，包括 `Worker`、`Task Worker`、`User Process`
- 新增 `SWOOLE_THREAD` 运行模式，启用后将使用线程代替进程运行
- 增加了 `bootstrap` 和 `init_arguments` 两项配置，用于设置工作线程的入口脚本文件、线程初始化参数

```php
$http = new Swoole\Http\Server("0.0.0.0", 9503, SWOOLE_THREAD);
$http->set([
    'worker_num' => 2,
    'task_worker_num' => 3,
    'bootstrap' => __FILE__,
    'init_arguments' => function () use ($http) {
        $map = new Swoole\Thread\Map;
        return [$map];
    }
]);

$http->on('Request', function ($req, $resp) use ($http) {
    $resp->end('hello world');
});

$http->on('pipeMessage', function ($http, $srcWorkerId, $msg) {
    echo "[worker#" . $http->getWorkerId() . "]\treceived pipe message[$msg] from " . $srcWorkerId . "\n";
});

$http->addProcess(new \Swoole\Process(function () {
   echo "user process, id=" . \Swoole\Thread::getId();
   sleep(2000);
}));

$http->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
    var_dump($taskId, $srcWorkerId, $data);
    return ['result' => uniqid()];
});

$http->on('Finish', function ($server, $taskId, $data) {
    var_dump($taskId, $data);
});

$http->on('WorkerStart', function ($serv, $wid) {
    var_dump(\Swoole\Thread::getArguments(), $wid);
});

$http->on('WorkerStop', function ($serv, $wid) {
    var_dump('stop: T' . \Swoole\Thread::getId());
});

$http->start();
```
