# 安全并发容器 List

创建一个并发的 `List` 结构，可作为线程参数传递给子线程。读写时在其他线程是可见的。

## 特性
- `Map`、`ArrayList`、`Queue` 会自动分配内存，不需要像 `Table` 那样固定分配。

- 底层会自动加锁，是线程安全的。

- 可传递的变量类型参考 [数据类型](thread/transfer.md)。

- 不支持迭代器，在迭代器中删除元素会出现内存错误。

- 必须在线程创建前将 `Map`、`ArrayList`、`Queue` 对象作为线程参数传递给子线程。

- `Swoole\Thread\ArrayList` 实现了 `ArrayAccess` 和 `Countable`接口，可以直接作为数组操作。

- `ArrayList` 只能追加元素，不能随机删除或赋值。

## 示例
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

### __construct()
安全并发容器 `ArrayList` 构造函数

```php
Swoole\Thread\ArrayList->__construct()
```

### incr()
使 `ArrayList` 中的数据安全地自增，支持浮点型或整型，若对其他类型进行自增操作，将会自动转为整型，初始化为 `0`，再进行自增操作。

```php
Swoole\Thread\ArrayList->incr(int $index, mixed $value = 1) : int | float
```

* **参数**
    * `int $index`
        * 功能：索引数字，必须是有效的索引地址，否则会抛出异常。
        * 默认值：无。
        * 其它值：无。

    * `mixed $value`
        * 功能：需要自增的值。
        * 默认值：1。
        * 其它值：无。

* **返回值**
    * 返回自增后的值。

### decr()
使 `ArrayList` 中的数据安全地自减，支持浮点型或整型，若对其他类型进行自减操作，将会自动转为整型，初始化为 `0`，再进行自减操作。

```php
Swoole\Thread\ArrayList->(int $index, $value = 1) : int | float
```

* **参数**
    * `int $index`
        * 功能：索引数字，必须是有效的索引地址，否则会抛出异常。
        * 默认值：无。
        * 其它值：无。

    * `mixed $value`
        * 功能：需要自减的值。
        * 默认值：1。
        * 其它值：无。

* **返回值**
    * 返回自减后的值。

### count()
获取ArrayList元素数量

```php
Swoole\Thread\ArrayList()->count(): int
```

* **返回值**
    * 返回List中的元素数量。

### clean()
清空所有元素

```php
Swoole\Thread\ArrayList()->clean(): void
```
