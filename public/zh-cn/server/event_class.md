# Swoole\Server\Event

这里是对`Swoole\Server\Event`的详细介绍。

## 属性

### $reactor_id
返回所在的`Reactor`线程id，该属性是一个`int`类型的整数。

```php
Swoole\Server\Event->reactor_id
```

### $fd
返回该连接的文件描述符`fd`，该属性是一个`int`类型的整数。

```php
Swoole\Server\Event->fd
```

### $dispatch_time
返回该请求数据到达时间`dispatch_time`，该属性是一个`double`类型。只有在`onReceive`事件里该属性才不为`0`。

```php
Swoole\Server\Event->dispatch_time
```

### $data
返回该客户端发送的数据`data`，该属性是一个`string`类型的字符串。只有在`onReceive`事件里该属性不为`null`。

```php
Swoole\Server\Event->data
```
