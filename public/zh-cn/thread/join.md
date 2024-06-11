# 线程管理

## 属性

### $id

通过此对象属性获取子线程的 `ID`，该属性是一个`int`类型的。

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```

## 方法

### join()

等待子线程退出。若子线程仍在运行，`join()` 会阻塞。

```php
Swoole\Thread->join(): bool
```

```php
$thread = Swoole\new Swoole\Thread(__FILE__, 1);
$thread->join();
```

### joinable()

检查子线程是否已退出。

```php
Swoole\Thread->joinable(): bool
```

#### 返回值
- `true` 表示子线程已退出，这时调用 `join()` 不会引起阻塞
- `false` 表示未退出

```php
$thread = Swoole\new Swoole\Thread(__FILE__, $i);
var_dump($thread->joinable());
```

### detach()

使子线程脱离父线程的掌控，不再需要 `join()` 等待线程退出，回收资源。

```php
Swoole\Thread->detach(): bool
```

```php
$thread = Swoole\new Swoole\Thread(__FILE__, $i);
$thread->detach();
unset($thread);
```

### getId()

静态方法，获取当前线程的 `ID`，在子线程中调用

```php
Swoole\Thread->getId(): int
```

```php
var_dump(Swoole\Thread::getId());
```

### getArguments()

静态方法，获取由父进程使用`new Swoole\Thread()` 时传递过来的共享数据，在子线程中调用。

```php
Swoole\Thread::getArguments(): array
```

```php
var_dump(Swoole\Thread::getArguments());
```

### getTsrmInfo()
静态方法，获取当前线程的 `TSRM` 信息。返回一个数组，包含以下信息：

- `is_main_thread`：是否为主线程
- `api_name`：线程 `API` 名称，例如 `POSIX Threads`
- `is_shutdown`：线程是否已关闭
