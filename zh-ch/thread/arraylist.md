# 并发 List

创建一个并发的 `List` 结构，可作为线程参数传递给子线程。读写时在其他线程时可见的。
详细特性可参考 [并发Map](thread/map.md)


## 使用方法
`Thread\AraryList` 实现了 `ArrayAccess` 接口，可以直接作为数组操作。

## 注意事项
- `ArrayList` 只能追加元素，不能随机删除或赋值

## 实例

```php
use Swoole\Thread;
use Swoole\Thread\AraryList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new AraryList;
    $thread = Thread::exec(__FILE__, $i, $list);
    sleep(1);
    $list[] = unique();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```
