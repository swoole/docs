# 安全并发容器 List

建立一个并发的 `List` 结构，能够作为线程参数传递给子线程。读写时在其他线程是可见的。

## 特性
- `Map`、`ArrayList`、`Queue` 会自动分配内存，不需要像 `Table` 那样固定分配。

- 底层会自动加锁，是线程安全的。

- 可传递的变量类型参考 [数据类型](thread/transfer.md)

- 不支持迭代器，可使用 `toArray()` 来代替

- 必须在线程创建前将 `Map`、`ArrayList`、`Queue` 对象作为线程参数传递给子线程

- `Thread\ArrayList` 实现了 `ArrayAccess` 和 `Countable`接口，可以直接作为数组操作

- `Thread\ArrayList` 仅支持数字索引下标操作，非数字将进行一次强制转换操作

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

- 增加或修改：`$list[$index] = $value`

- 删除：`unset($list[$index])`

- 读取：`$value = $list[$index]`
- 获取长度：`count($list)`

## 删除
请注意删除操作会引起`List`批量前移操作，例如`List`有`1000`个元素，当`unset($list[4])`时，
将需要`$list[5:999]`进行批量迁移操作，填补删除`$list[4]`产生的空位。但不会深度复制元素，仅移动其指针。

> 若`List`较大时，删除靠前的元素，可能会消耗较多的`CPU`资源

## 方法

### __construct()
安全并发容器 `ArrayList` 构造函数

```php
Swoole\Thread\ArrayList->__construct(?array $values = null)
```

- `$values` 可选，遍历数组将数组中的值添加到 `ArrayList` 中

- 只接受 `list` 类型的数组，不接受关联数组，否则将抛出异常
- 关联数组需使用 `array_values` 转换为 `list` 类型的数组

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
获取 `ArrayList` 元素数量

```php
Swoole\Thread\ArrayList()->count(): int
```

* **返回值**
    * 返回List中的元素数量。

### toArray()
将 `ArrayList` 转换为数组

```php
Swoole\Thread\ArrayList()->toArray(): array
```

* **返回值** 类型数组，返回 `ArrayList` 中的所有元素。

### clean()
清空所有元素

```php
Swoole\Thread\ArrayList()->clean(): void
```
