# Swoole\Server\TaskResult

这里是对`Swoole\Server\TaskResult`的详细介绍。

## 属性

### $task_id
返回所在的`Reactor`线程id，该属性是一个`int`类型的整数。

```php
Swoole\Server\TaskResult->task_id
```

### $task_worker_id
返回该执行结果来自哪个`task`进程，该属性是一个`int`类型的整数。

```php
Swoole\Server\TaskResult->task_worker_id
```

### $dispatch_time
返回该连接携带的数据`data`，该属性是一个`?string`类型的字符串。

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
返回该连接携带的数据`data`，该属性是一个`string`类型的字符串。

```php
Swoole\Server\StatusInfo->data
```