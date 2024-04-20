# 安全并发容器 Queue

创建一个并发的 `Queue` 结构，可作为线程参数传递给子线程。读写时在其他线程是可见的。

## 特性
- `Map`、`ArrayList`、`Queue` 会自动分配内存，不需要像 `Table` 那样固定分配
- 底层会自动加锁，是线程安全的
- 仅支持 `null/bool/int/float/string` 类型，其他类型将在写入时自动序列化，读取时反序列化
- 不支持迭代器，在迭代器中删除元素会出现内存错误
- 必须在线程创建前将 `Map`、`ArrayList`、`Queue` 对象作为线程参数传递给子线程


## 使用方法
`Swoole\Thread\Queue` 是一个先进先出的数据结构，有两个核心操作：
- `Swoole\Thread\Queue::push($value)` 向队列中写入数据
- `Swoole\Thread\Queue::pop()` 从队列头部中提取数据

## 注意事项
- `Swoole\Thread\Queue` 只能追加元素，不能随机删除或赋值

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

### push()

向队列中写入数据

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

- `$value`：写入的数据内容
- `$notify`：是否通知等待读取数据的线程，`Swoole\Thread\Queue::NOTIFY_ONE` 唤醒一个线程，`Swoole\Thread\Queue::NOTIFY_ALL` 唤醒所有线程


### pop()

从队列头部中提取数据

```php
Swoole\Thread\Queue()->pop(float $wait = 0): mixed
```

- `$wait`：默认值0，表示不等待，当队列为空时直接返回 `NULL`， 如果不为0， 表示当队列为空时等待生产者 `push()` 数据，若 `$timeout` 为负数时表示永不超时

### count()
获取队列元素数量

```php
Swoole\Thread\Queue()->count(): int
```

### clean()
清空所有元素

```php
Swoole\Thread\Queue()->clean(): void
```
