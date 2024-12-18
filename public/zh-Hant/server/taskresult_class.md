# Swoole\Server\TaskResult

這裡是對`Swoole\Server\TaskResult`的詳細介紹。

## 屬性

### $task_id
返回所在的`Reactor`線程id，該屬性是一個`int`類型的整數。

```php
Swoole\Server\TaskResult->task_id
```

### $task_worker_id
返回該執行結果來自哪個`task`進程，該屬性是一個`int`類型的整數。

```php
Swoole\Server\TaskResult->task_worker_id
```

### $dispatch_time
返回該連接攜帶的數據`data`，該屬性是一個`?string`類型的字符串。

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
返回該連接攜帶的數據`data`，該屬性是一個`string`類型的字符串。

```php
Swoole\Server\StatusInfo->data
```
