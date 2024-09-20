# 数据类型
这里列出了可以在线程间共享的数据类型和注意事项。

## 基础类型
- `null/bool/int/float` 类型变量，内存尺寸小于 `16 Bytes`，作为值进行传递。

## 字符串
- 对字符串进行内存拷贝，存储到 `ArrayList`、`Queue`、`Map`。

## Socket 资源

### 支持的类型列表
- `Co\Socket`
- `PHP Stream`
- `PHP Socket(ext-sockets)`，需开启 `--enable-sockets` 编译参数

### 不支持的类型
- `Swoole\Client`
- `Swoole\Server`
- `Swoole\Coroutine\Client`
- `Swoole\Coroutine\Http\Client`
- `pdo` 连接
- `redis` 连接
- 其他的特殊 `Socket` 资源类型

### 资源复制

- 在写入时将进行 `dup(fd)` 操作，与原有资源分离，互不影响，对原有的资源进行 `close` 操作不会影响到新的资源
- 在读取时进行 `dup(fd)` 操作，在读取的子线程 `VM` 内构建新的 `Socket` 资源
- 在删除时进行 `close(fd)` 操作，释放文件句柄

这意味着 `Socket` 资源 会存在 `3` 次引用计数，分别是：
- `Socket` 资源初始创建时所在的线程
- `ArrayList`、`Queue`、`Map` 容器
- 读取 `ArrayList`、`Queue`、`Map` 容器的子线程

当没有任何线程或容器持有此资源，引用计数减为 `0` 时，`Socket` 资源才会被真正地释放。引用计数不为 `0`，
即使执行了 `close` 操作，也不会关闭连接，不会影响其他线程或数据容器持有的 `Socket` 资源。

若希望忽略引用计数，直接关闭 `Socket`，可使用 `shutdown()` 方法，例如：
- `stream_socket_shutdown()`
- `Socket::shutdown()`
- `socket_shutdown()`

> `shutdown` 操作将影响所有线程持有的 `Socket` 资源，执行后将不再可用，无法执行 `read/write` 操作

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
