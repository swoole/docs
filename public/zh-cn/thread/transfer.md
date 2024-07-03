# 数据类型
这里列出了可以在线程间共享的数据类型和注意事项。

## 基础类型
- `null/bool/int/float` 类型变量，内存尺寸小于 `16 Bytes`，作为值进行传递。

## 字符串
- 将内存拷贝字符串，传递给线程或存储到 `ArrayList`、`Queue`、`Map`。

## socket和php stream
- 在写入时将进行 `dup(fd)` 操作，与原有资源分离，互不影响，对原有的资源进行 `close` 操作不会影响到新的资源。
- 
- 在读取时进行 `dup(fd)` 操作，在读取的子线程 `VM` 内构建新的 `PHP Stream`/`Swoole\Coroutine\Socket`。

- 在删除时进行 `close(fd)` 操作，释放文件句柄。

这意味着 `PHP Stream`或者`Swoole\Coroutine\Socket` 会存在 `3` 次引用计数，分别是：
- `PHP Stream`/`Swoole\Coroutine\Socket` 初始创建时所在的线程。

- `ArrayList`、`Queue`、`Map` 容器。

- 读取 `ArrayList`、`Queue`、`Map` 容器的子线程。

当没有任何线程或容器持有此资源，引用计数减为 `0` 时，`PHP Stream`/`Swoole\Coroutine\Socket` 资源才会被真正地释放。

## 数组
使用 `array_is_list()` 进行判断数组的类型，若为数字索引数组则转为 `ArrayList`，关联索引数组转为 `Map`。

- 将会遍历整个数组，将元素插入到 `ArrayList` 或 `Map` 中
- 支持多维数组，递归遍历多维数组转为嵌套结构的 `ArrayList` 或 `Map`

例子：
```php
$array = [
    'a' => random_int(1, 999999999999999999),
    'b' => random_bytes(128),
    'c' => uniqid(),
    'd' => time(),
    'e' => [
        'key' => 'value',
        'hello' => 'world',
    ];
];

$map = new Map($array);

// $map['e'] 是一个新的 Map 对象，包含两个元素，key、hello，值为 'value' 和 'world'
var_dump($map['e']);
```

## 对象
### 线程资源对象

例如 `Thread\Lock`、`Thread\Atomic`、`Thread\ArrayList`、`Thread\Map` 等，可直接存储到 `ArrayList`、`Queue`、`Map` 中。
此操作仅仅是将对象的引用存储到容器中，不会进行对象的拷贝。

将对象写入到 `ArrayList` 或 `Map` 时，只是对线程资源增加一次引用计数，不会拷贝。当对象的引用计数为 `0` 时，会被释放。

例子：

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // 当前引用计数为 1
$map['lock'] = $lock; // 当前引用计数为 2
unset($map['lock']); // 当前引用计数为 1
unset($lock); // 当前引用计数为 0，Lock 对象被释放
```

### 普通 PHP 对象
将在写入时自动序列化，读取时反序列化。请注意若对象包含不可序列化类型，将会抛出异常。
