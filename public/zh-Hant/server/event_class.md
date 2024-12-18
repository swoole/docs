# Swoole\Server\Event

這裡是對`Swoole\Server\Event`的詳細介紹。

## 屬性

### $reactor_id
返回所在的`Reactor`線程id，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Event->reactor_id
```

### $fd
返回該連接的文件描述符`fd`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Event->fd
```

### $dispatch_time
返回該請求數據到達時間`dispatch_time`，該屬性是一個`double`類型。只有在`onReceive`事件裡該屬性才不為`0`。

```php
Swoole\Server\Event->dispatch_time
```

### $data
返回該客戶端發送的數據`data`，該屬性是一個`string`類型的字符串。只有在`onReceive`事件裡該屬性才不為`null`。
