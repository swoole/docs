# 安全并发容器 Queue

创建一个并发的 `Queue` 结构，可作为线程参数传递给子线程。读写时在其他线程是可见的。

## 特性
- `Thread\Queue` 是一个先进先出的数据结构。

- `Map`、`ArrayList`、`Queue` 会自动分配内存，不需要像 `Table` 那样固定分配。

- 底层会自动加锁，是线程安全的

- 可传递的变量类型参考 [线程参数传递](thread/transfer.md)

- 不支持迭代器，底层使用了 `C++ std::queue`，仅支持先进先出操作

- 必须在线程创建前将 `Map`、`ArrayList`、`Queue` 对象作为线程参数传递给子线程

- `Thread\Queue` 只能压入、弹出元素，不能随机访问元素

- `Thread\Queue` 内置了线程条件变量，可在 `push/pop` 操作中唤醒、等待其他线程

## 示例

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
        $threads[] = new Thread(__FILE__, $i, $queue);
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

## 常量


名称 | 作用
---|---
`Queue::NOTIFY_ONE` | 唤醒一个线程
`Queue::NOTIFY_ALL` | 唤醒所有线程

## 方法列表

### __construct()
安全并发容器 `Queue` 构造函数

```php
Swoole\Thread\Queue->__construct()
```

### push()
向队列尾部中写入数据

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

  * **参数**
      * `mixed $value`
          * 功能：写入的数据内容。
          * 默认值：无。
          * 其它值：无。

      !> 为避免产生歧义，请勿向通道中写入`null`和`false`
  
      * `int $notify`
          * 功能：是否通知等待读取数据的线程。
          * 默认值：`0`，不会唤醒任何线程
          * 其它值：`Swoole\Thread\Queue::NOTIFY_ONE` 唤醒一个线程，`Swoole\Thread\Queue::NOTIFY_ALL` 唤醒所有线程。


### pop()
从队列头部中提取数据

```php
Swoole\Thread\Queue()->pop(float $timeout = 0): mixed
```

* **参数**
    * `float $wait`
        * 功能：超时时间。
        * 默认值：`0`，表示不等待。
        * 其它值：如果不为`0`， 表示当队列为空时在`$timeout`秒内等待生产者 `push()` 数据，为负数时表示永不超时。

* **返回值**
    * 返回队列头部数据，当队列为空时直接返回 `NULL`。

> 使用`Queue::NOTIFY_ALL`唤醒所有线程时，只有一个线程可以获得`push()`操作写入的数据

### count()
获取队列元素数量

```php
Swoole\Thread\Queue()->count(): int
```

* **返回值**
    * 返回队列数量。

### clean()
清空所有元素

```php
Swoole\Thread\Queue()->clean(): void
```
