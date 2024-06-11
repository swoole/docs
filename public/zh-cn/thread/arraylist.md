# 安全并发容器 List

创建一个并发的 `List` 结构，可作为线程参数传递给子线程。读写时在其他线程是可见的。

## 特性
- `Map`、`ArrayList`、`Queue` 会自动分配内存，不需要像 `Table` 那样固定分配
- 底层会自动加锁，是线程安全的
- 仅支持 `null/bool/int/float/string` 类型，其他类型将在写入时自动序列化，读取时反序列化
- 不支持迭代器，在迭代器中删除元素会出现内存错误
- 必须在线程创建前将 `Map`、`ArrayList`、`Queue` 对象作为线程参数传递给子线程

## 使用方法
`Swoole\Thread\ArrayList` 实现了 `ArrayAccess` 和 `Countable`接口，可以直接作为数组操作。

## 注意事项
- `ArrayList` 只能追加元素，不能随机删除或赋值

## 实例

```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new ArrayList;
    $thread = new Thread(__FILE__, $i, $list);
    sleep(1);
    $list[] = unique();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```

## 方法

### incr()

使 `ArrayList` 中的数据安全地自增

```php
function ArrayList::incr(int $index, $value = 1) : int | float
```

- 支持浮点型或整型，若对其他类型进行自增操作，将会自动转为整型，初始化为 `0`，再进行自增操作
- `$index` 索引数字，必须是有效的索引地址，否则会抛出异常
- `$value` 自增的值，如果不传递则默认为 `1`
- 返回自增后的值

### decr()

使 `ArrayList` 中的数据安全地自减

```php
function ArrayList::decr(int $index, $value = -1) : int | float
```

参考 `ArrayList::incr()`

### count()
获取元素数量

```php
Swoole\Thread\ArrayList()->count(): int
```

### clean()
清空所有元素

```php
Swoole\Thread\ArrayList()->clean(): void
```
