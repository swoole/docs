# 安全并发容器 Map

创建一个并发的 `Map` 结构，可作为线程参数传递给子线程。读写时在其他线程是可见的。

## 特性
- `Map`、`ArrayList`、`Queue` 会自动分配内存，不需要像 `Table` 那样固定分配
- 底层会自动加锁，是线程安全的
- 仅支持 `null/bool/int/float/string` 类型，其他类型将在写入时自动序列化，读取时反序列化
- 不支持迭代器，在迭代器中删除元素会出现内存错误
- 必须在线程创建前将 `Map`、`ArrayList`、`Queue` 对象作为线程参数传递给子线程

## 使用方法
`Swoole\Thread\Map` 实现了 `ArrayAccess` 和 `Countable` 接口，可以直接作为数组操作。

## 实例

```php
use Swoole\Thread;
use Swoole\Thread\Map;

$args = Thread::getArguments();
if (empty($args)) {
    $map = new Map;
    $thread = Thread::exec(__FILE__, $i, $map);
    sleep(1);
    $map['test'] = unique();
    $thread->join();
} else {
    $map = $args[1];
    sleep(2);
    var_dump($map['test']);
}
```

## 方法

### count()
获取元素数量

```php
Swoole\Thread\Map()->count(): int
```

### keys()
返回所有 `key`

```php
Swoole\Thread\Map()->keys(): array
```

### clean()
清空所有元素

```php
Swoole\Thread\Map()->clean(): void
```
