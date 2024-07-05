# 方法与属性

## 方法

### __construct()
多线程构造方法

```php
Swoole\Thread->join(string $script_file, mixed ...$args)
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
主线程等待子线程退出。若子线程仍在运行，`join()` 会阻塞。

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
使子线程脱离主线程的掌控，不再需要 `join()` 等待线程退出，回收资源，需要在子线程中使用。

```php
Swoole\Thread->detach(): bool
```
* **返回值**
    * 返回`true`表示操作成功，返回`false`表示操作失败。

### getId()
静态方法，获取当前线程的 `ID`，在子线程中调用。

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
} else {
    # 子线程
    var_dump($args); // 输出: ['child thread']
}
```

### getTsrmInfo()
静态方法，获取当前线程的 `TSRM` 信息。

```php
Swoole\Thread::getTsrmInfo(): array
```
返回数组信息如下：

- `is_main_thread`：是否为主线程

- `api_name`：线程 `API` 名称，例如 `POSIX Threads`

- `is_shutdown`：线程是否已关闭

## 属性

### id

通过此对象属性获取子线程的 `ID`，该属性是一个`int`类型的。

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```
