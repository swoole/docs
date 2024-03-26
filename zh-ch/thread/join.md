# 线程管理

## Thread::join()

等待子线程退出。若子线程仍在运行，`join()` 会阻塞。

```php
$thread = Thread::exec(__FILE__, $i);
$thread->join();
```

## Thread::joinable()

检查子线程是否已退出。

### 返回值
- `true` 表示子线程已退出，这时调用 `join()` 不会引起阻塞
- `false` 表示未退出

```php
$thread = Thread::exec(__FILE__, $i);
var_dump($thread->joinable());
```

## Thread::detach()

使子线程脱离父线程的掌控，不再需要 `join()` 等待线程退出，回收资源。

```php
$thread = Thread::exec(__FILE__, $i);
$thread->detach();
unset($thread);
```

## Thread::getId()

静态方法，获取当前线程的 `ID`，在子线程中调用

```php
var_dump(Thread::getId());
```

## Thread::getId()

静态方法，获取当前线程的 `ID`，在子线程中调用

```php
var_dump(Thread::getId());
```

## Thread::getArguments()

静态方法，获取当前线程的参数，在子线程中调用，父线程在 `Thread::exec()` 时传入。

```php
var_dump(Thread::getArguments());
```

## Thread::$id

通过此对象属性获取子线程的 `ID`

```php
$thread = Thread::exec(__FILE__, $i);
var_dump($thread->id);
```
