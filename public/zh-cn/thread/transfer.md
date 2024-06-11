# 线程参数传递

## 基础类型
`null/bool/int/float` 类型变量，内存尺寸小于 `16 Bytes`，作为值进行传递

## 字符串
将内存拷贝字符串，传递给线程或存储到 `ArrayList`、`Queue`、`Map`

## `PHP Stream`/`Swoole\Coroutine\Socket`
- 在写入时将进行 `dup(fd)` 操作，与原有资源分离，互不影响，对原有的资源进行 `close` 操作不会影响到新的资源
- 在读取时再进行 `dup(fd)` 操作，在读取的子线程 `VM` 内构建新的 `PHP Stream`/`Swoole\Coroutine\Socket`
- 在删除时会进行 `close(fd)` 操作，释放文件句柄

这意味着 `PHP Stream`/`Swoole\Coroutine\Socket` 会存在 `3` 次引用计数，分别是：
- `PHP Stream`/`Swoole\Coroutine\Socket` 初始创建时所在的线程
- `ArrayList`、`Queue`、`Map` 容器
- 读取 `ArrayList`、`Queue`、`Map` 容器的子线程

当没有任何线程或容器持有此资源，引用计数减为 `0` 时，`PHP Stream`/`Swoole\Coroutine\Socket` 资源才会被真正地释放

## 对象或数组
对象或数组将在写入时自动序列化，读取时反序列化。请注意若对象或数组中包含不可序列化类型，将会抛出异常。
