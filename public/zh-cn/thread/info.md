# 方法与属性

## 方法

### __construct()
多线程构造方法

```php
Swoole\Thread->__construct(string $script_file, mixed ...$args)
```
* **参数**
    * `string $script_file`
        * 功能：线程启动后要执行的文件。
        * 默认值：无。
        * 其它值：无。

    * `mixed $args`
        * 功能：主线程传递给子线程的共享数据，在子线程中可使用 `Swoole\Thread::getArguments()` 获取。
        * 默认值：无。
        * 其它值：无。

!> 线程创建失败会抛出`Swoole\Exception`，可以通过`try catch`捕获它。

### join()
主线程等待子线程退出。若子线程仍在运行，`join()` 会阻塞，直到子线程退出。

```php
Swoole\Thread->join(): bool
```
* **返回值**
    * 返回`true`表示操作成功，返回`false`表示操作失败。

### joinable()
检查子线程是否已退出。

```php
Swoole\Thread->joinable(): bool
```

#### 返回值
- `true` 表示子线程已退出，这时调用 `join()` 不会引起阻塞
- `false` 表示未退出

### detach()
使子线程脱离主线程的掌控，不再需要 `join()` 等待线程退出。

```php
Swoole\Thread->detach(): bool
```
* **返回值**
    * 返回`true`表示操作成功，返回`false`表示操作失败。

### getId()
静态方法，获取当前线程的 `ID`。

```php
Swoole\Thread::getId(): int
```
* **返回值**
    * 返回int类型的整数，表示当前线程的id。

### getArguments()
静态方法，获取由主线程使用`new Swoole\Thread()` 时传递过来的共享数据，在子线程中调用。

```php
Swoole\Thread::getArguments(): ?array
```

* **返回值**
    * 子线程中返回父进程传递过来的共享数据。

?> 主线程不会有任何线程参数，可以通过判断线程参数是否为空来分辨父子线程，让他们执行不同的逻辑
```php
use Swoole\Thread;

$args = Thread::getArguments(); // 如果是主线程，$args为空，如果是子线程，$args不为空
if (empty($args)) {
    # 主线程
    new Thread(__FILE__, 'child thread'); // 传递线程参数
    echo "main thread\n";
} else {
    # 子线程
    var_dump($args); // 输出: ['child thread']
}
```

### getInfo()
静态方法，获取当前多线程环境的信息。

```php
Swoole\Thread::getInfo(): array
```
返回数组信息如下：

- `is_main_thread`：当前的线程是否为主线程
- `is_shutdown`：线程是否已关闭
- `thread_num`：当前活跃的线程数量

### getPriority()
获取线程调度的信息

```php
Swoole\Thread->getPriority(): array
```
返回数组信息如下：

- `policy`：线程调度策略
- `priority`：线程的调度优先级

### setPriority()
设置线程调度优先级和策略

?> 仅`root`用户可以调整，非`root`用户执行将被拒绝执行

```php
Swoole\Thread->setPriority(int $priority, int $policy = -1): bool
```

* **参数**
    * `int $priority`
        * 功能：设置线程调度优先级
        * 默认值：无。
        * 其它值：无。

    * `mixed $policy`
        * 功能：设置线程调度优先策略
        * 默认值：`-1`，表示不调整调度策略。
        * 其它值：`Thread::SCHED_*` 相关常量。

* **返回值**
    * 成功返回`true`
    * 失败返回`false`，使用`swoole_last_error()`获取错误信息

### getAffinity()
获取线程`CPU`亲缘性

```php
Swoole\Thread->getAffinity(): array
```
返回值为数组，元素为`CPU`核数，例如：`[0, 1, 3, 4]` 表示此线程将被调度到`CPU`的`0/1/3/4`核心运行

### setAffinity()
设置线程`CPU`亲缘性

```php
Swoole\Thread->setAffinity(array $cpu_set): bool
```

* **参数**
    * `array $cpu_set`
        * 功能：`CPU`核心的列表，例如`[0, 1, 3, 4]`
        * 默认值：无。
        * 其它值：无。

* **返回值**
    * 成功返回`true`
    * 失败返回`false`，使用`swoole_last_error()`获取错误信息

### setName()
设置线程的名称

```php
Swoole\Thread->setName(string $name): bool
```

* **参数**
    * `string $name`
        * 功能：线程名称
        * 默认值：无。
        * 其它值：无。

* **返回值**
    * 成功返回`true`
    * 失败返回`false`，使用`swoole_last_error()`获取错误信息

### getNativeId()
获取线程线程的系统 `ID`，将返回一个整数，类似于进程的 `PID`。

```php
Swoole\Thread->getNativeId(): int
```

## 属性

### id

通过此对象属性获取子线程的 `ID`，该属性是`int`类型。

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```

## 常量

名称 | 作用
---|---
`Thread::HARDWARE_CONCURRENCY` | 硬件并发线程数量，一般为`CPU`核数
`Thread::API_NAME` | 线程 `API` 名称，例如 `POSIX Threads`
`Thread::SCHED_OTHER` | 线程调度策略 `SCHED_OTHER`
`Thread::SCHED_FIFO` | 线程调度策略 `SCHED_FIFO`
`Thread::SCHED_RR` | 线程调度策略 `SCHED_RR`
`Thread::SCHED_BATCH` | 线程调度策略 `SCHED_BATCH`
`Thread::SCHED_ISO` | 线程调度策略 `SCHED_ISO`
`Thread::SCHED_IDLE` | 线程调度策略 `SCHED_IDLE`
`Thread::SCHED_DEADLINE` | 线程调度策略 `SCHED_DEADLINE`
