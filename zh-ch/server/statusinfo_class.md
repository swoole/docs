# Swoole\Server\StatusInfo

这里是对`Swoole\Server\StatusInfo`的详细介绍。

## 属性

### $worker_id
返回当前`worker`进程id，该属性是一个`int`类型的整数。

```php
Swoole\Server\StatusInfo->worker_id
```

### $worker_pid
返回当前`worker`进程父进程id，该属性是一个`int`类型的整数。

```php
Swoole\Server\StatusInfo->worker_pid
```

### $status
返回进程状态`status`，该属性是一个`int`类型的整数。

```php
Swoole\Server\StatusInfo->status
```

### $exit_code
返回进程退出状态码`exit_code`，该属性是一个`int`类型的整数，范围是`0-255`。

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
进程退出的信号`signal`，该属性是一个`int`类型的整数。

```php
Swoole\Server\StatusInfo->signal
```