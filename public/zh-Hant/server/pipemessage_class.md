# Swoole\Server\PipeMessage

這裡是對`Swoole\Server\PipeMessage`的詳細介紹。

## 屬性

### $source_worker_id
返回數據來源方的`worker`進程id，該屬性是一個`int`類型的整數。

```php
Swoole\Server\PipeMessage->source_worker_id
```

### $dispatch_time
返回該請求數據到達時間`dispatch_time`，該屬性是一個`double`類型。

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
返回該連接攜帶的數據`data`，該屬性是一個`string`類型的字符串。

```php
Swoole\Server\PipeMessage->data
```
