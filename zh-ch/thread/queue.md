# 并发队列

创建一个并发的 `Queue` 结构，可作为线程参数传递给子线程。读写时在其他线程时可见的。
详细特性可参考 [并发Map](thread/map.md)


## 使用方法
`Thread\Queue` 是一个先进先出的数据结构，有两个核心操作：
- `Queue::push($value)` 向队列中写入数据
- `Queue::pop()` 从队列头部中提取数据

## 注意事项
- `ArrayList` 只能追加元素，不能随机删除或赋值

## 实例代码

```php
use Swoole\Thread;
use Swoole\Thread\Queue;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $queue = new Queue;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = Thread::exec(__FILE__, $i, $queue);
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16)), Queue::NOTIFY_ONE);
        usleep(random_int(10000, 100000));
    }
    $n = 4;
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1];
    while (1) {
        $job = $queue->pop(-1);
        if (!$job) {
            break;
        }
        var_dump($job);
    }
}
```

## 方法列表

### Queue::push()

向队列中写入数据

```php
function Queue::push(mixed $value, int $notify = 0);
```

- `$value`：写入的数据内容
- `$notify`：是否通知等待读取数据的线程，`Queue::NOTIFY_ONE` 唤醒一个线程，`Queue::NOTIFY_ALL` 唤醒所有线程


### Queue::pop()

从队列头部中提取数据

```php
function Queue::pop(double $timeout = 0);
```

- `$timeout=0`：默认值，表示不等待，当队列为空时直接返回 `NULL`
- `$timeout!=0`：当队列为空时等待生产者 `push()` 数据，若 `$timeout` 为负数时表示永不超时

### Queue::count()
获取队列元素数量

### Queue::clean()
清空所有元素
