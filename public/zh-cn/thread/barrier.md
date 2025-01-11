# 同步执行屏障 Barrier

## 线程同步屏障

`Thread\Barrier` 是一种线程同步的机制。它允许多个线程在特定的点上进行同步，确保所有线程在某个临界点（障碍）之前都完成了自己的任务。只有当所有参与的线程都到达这个障碍时，它们才能继续执行后续的代码。

例如我们创建了`4`个线程，希望这些线程全部就绪后一起执行任务，就像跑步比赛中裁判的发令枪，在发出信号之后同时起跑。这就可以用`Thread\Barrier`实现。

!> 该线程同步屏障会导致线程挂起，因此不能用于多协程同步。

### 示例
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

### 方法

#### __construct()
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

#### wait()

阻塞等待其他线程，直到所有线程均处于`wait`状态时，会同时唤醒所有等待的线程，继续向下执行。

```php
Thread\Barrier()->wait(): void
```

## 协程同步屏障

在 [Swoole Library](https://github.com/swoole/library) 中底层提供了一个更便捷的协程并发管理工具：`Coroutine\Barrier` 协程屏障，或者叫协程栅栏。基于 `PHP` 引用计数和 `Coroutine API` 实现。

相比于[Coroutine\WaitGroup](/coroutine/wait_group)，`Coroutine\Barrier`使用更简单一些，只需通过参数传递或者闭包的`use`语法，引入子协程函数上即可。

!> Swoole 版本 >= v4.5.5 时可用。

!> 该协程屏障只能用于单进程或者单线程中的多协程同步，主要作用还是让主协程等待全部子协程完成任务后退出。

### 使用示例

```php
use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;

run(function () {
    $barrier = Barrier::make();

    $count = 0;
    $N = 4;

    foreach (range(1, $N) as $i) {
        Coroutine::create(function () use ($barrier, &$count) {
            System::sleep(0.5);
            $count++;
        });
    }

    Barrier::wait($barrier);
    
    assert($count == $N);
});
```

### 方法

#### make()
生成一个协程屏障。

```php
Coroutine\Barrier::make(): self
```

#### wait()
等待所有协程就绪

```php
Coroutine\Barrier::wait(Barrier &$barrier, float $timeout = -1): void
```

* **参数**
    * `int $barrier`
        * 功能：由`Coroutine\Barrier::make()`返回的协程屏障。
        * 默认值：无。
        * 其它值：无。
    * `float $timeout`
        * 功能：超时时间。
        * 默认值：-1，表示永不超时。
        * 其它值：无。

### 执行流程

* 先使用`Barrier::make()`创建了一个新的协程屏障
* 在子协程用使用`use`语法传递屏障，增加引用计数
* 在需要等待的位置加入`Barrier::wait($barrier)`，这时会自动挂起当前协程，等待引用该协程屏障的子协程退出
* 子协程退出时会减少`$barrier`对象的引用计数，直到为`0`
* 当所有子协程完成了任务处理并退出时，`$barrier`对象引用计数为`0`，在`$barrier`对象析构函数中底层会自动恢复挂起的协程，从`Barrier::wait($barrier)`函数中返回

`Coroutine\Barrier` 是一个比 [WaitGroup](/coroutine/wait_group) 和 [Channel](/coroutine/channel) 更易用的并发控制器，大幅提升了 `PHP` 并发编程的用户体验。

