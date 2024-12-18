# 线程同步执行屏障 Barrier

`Thread\Barrier` 是一种线程同步的机制。它允许多个线程在特定的点上进行同步，确保所有线程在某个临界点（障碍）之前都完成了自己的任务。只有当所有参与的线程都到达这个障碍时，它们才能继续执行后续的代码。

例如我们创建了`4`个线程，希望这些线程全部就绪后一起执行任务，就像跑步比赛中裁判的发令枪，在发出信号之后同时起跑。这就可以用`Thread\Barrier`实现。

## 示例
```php
use Swoole\Thread;
use Swoole\Thread\Barrier;

const N = 4;
$args = Thread::getArguments();

if (empty($args)) {
    $barrier = new Barrier(N);
    $n = N;
    $threads = [];
    while($n--) {
        $threads[] = new Thread(__FILE__, $barrier, $n);
    }
} else {
    $barrier = $args[0];
    $n = $args[1];
    // 等待所有线程就绪
    $barrier->wait();
    echo "thread $n is running\n";
}
```

## 方法

### __construct()
构造方法

```php
Thread\Barrier()->__construct(int $count): void
```

  * **参数**
      * `int $count`
          * 功能：线程数量，必须大于`1`。
          * 默认值：无。
          * 其它值：无。
  
执行`wait`操作的线程数量必须与设置的计数一致，否则所有线程均会阻塞。

### wait()

阻塞等待其他线程，直到所有线程均处于`wait`状态时，会同时唤醒所有等待的线程，继续向下执行。

```php
Thread\Barrier()->wait(): void
```
