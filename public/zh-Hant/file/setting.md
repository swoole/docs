# 配置

`Swoole`設定了幾個關鍵參數來影響`異步`檔案操作的特性，可以透過`swoole_async_set`或者`Swoole\Server->set()`來設定。

示範：

```php
<?php
swoole_async_set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);
```

### aio_core_worker_num

?> 設定線程池的最小線程數，預設值為`CPU核數`。

### aio_worker_num

?> 設定線程池的最大線程數，預設值為`CPU核數 * 8`。

### aio_max_wait_time

?> 設定線程池中的線程的最大等待時間，預設值為`0`。

### aio_max_idle_time

?> 設定線程池中的線程的空閒時間，預設值為`1s`。

### iouring_entries

?> 設定`io_uring`的隊列大小，默認為`8192`，如果傳入的值不是`2的次方數`，內核會修改為最接近的，大於該值的`2的次方數`。

!> 如果傳入的值過大，內核會拋出異常並終止程式。

!> 當系統安裝了`liburing`並編譯`Swoole`開啟了`--enable-iouring`之後才能使用。

!> 當系統安裝了`liburing`並編譯``Swoole v6.0`以上的版本，開啟了`--enable-iouring`之後才能使用。
