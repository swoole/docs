# Swoole\Server\PipeMessage

这里是对`Swoole\Server\PipeMessage`的详细介绍。

## 属性

### $source_worker_id
返回数据来源方的`worker`进程id，该属性是一个`int`类型的整数。

```php
Swoole\Server\PipeMessage->source_worker_id
```

### $dispatch_time
返回该请求数据到达时间`dispatch_time`，该属性是一个`double`类型。

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
返回该连接携带的数据`data`，该属性是一个`string`类型的字符串。

```php
Swoole\Server\PipeMessage->data
```