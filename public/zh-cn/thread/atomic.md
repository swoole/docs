# 线程原子计数
专门用于线程同步的原子计数，其接口与 `Swoole/Atomic` 、`Swoole/Atomic/Long` 完全一致，

- `Swoole/Thread/Atomic`：`32` 位原子计数
- `Swoole/Thread/Atomic/Long`：`64` 位原子计数

`Swoole/Thread/Atomic` 和 `Swoole/Thread/Atomic/Long` 可以安全地动态创建和销毁，
并且可通过 `ArrayList`、`Map`、`Queue` 或者作为线程参数传递给其他线程。

## 构造对象

```php
function Swoole\Thread\Atomic::__construct(int $value = 0)
function Swoole\Thread\Atomic\Long::__construct(int $value = 0)
```
- `$value`：初始化参数，默认为 `0`

## 方法
参考 [swoole\atomic](memory/atomic.md)

## 实例
```php
<?php

use Swoole\Thread;
use Swoole\Thread\Atomic;
use Swoole\Thread\Atomic\Long;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $a1 = new Atomic;
    $a2 = new Long;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = Thread::exec(__FILE__, $i, $a1, $a2);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($a1->get(), $a2->get());
} else {
    $a1 = $args[1];
    $a2 = $args[2];

    $a1->add(3);
    $a2->add(7);
}
```
