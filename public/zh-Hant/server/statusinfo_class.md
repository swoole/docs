# Swoole\Server\StatusInfo

這裡是對`Swoole\Server\StatusInfo`的詳細介紹。

## 屬性

### $worker_id
返回當前`worker`進程id，該屬性是一個`int`類型的整數。

```php
Swoole\Server\StatusInfo->worker_id
```

### $worker_pid
返回當前`worker`進程父進程id，該屬性是一個`int`類型的整數。

```php
Swoole\Server\StatusInfo->worker_pid
```

### $status
返回進程狀態`status`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\StatusInfo->status
```

### $exit_code
返回進程退出狀態碼`exit_code`，該屬性是一個`int`類型的整數，範圍是`0-255`。

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
進程退出的信號`signal`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\StatusInfo->signal
```
