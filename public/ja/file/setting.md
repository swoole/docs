# 設定

`Swoole`はいくつかの重要なパラメータを設定して、非同期ファイル操作の特性に影響を与えます。これらは`swoole_async_set`または`Swoole\Server->set()`を通じて設定できます。

例：

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

?> スレッドプール内の最小スレッド数を設定します。デフォルト値は`CPUコア数`です。

### aio_worker_num

?> スレッドプール内の最大スレッド数を設定します。デフォルト値は`CPUコア数 * 8`です。

### aio_max_wait_time

?> スレッドプール内のスレッドの最大待ち時間を設定します。デフォルト値は`0`です。

### aio_max_idle_time

?> スレッドプール内のスレッドの空闲時間を設定します。デフォルト値は`1秒`です。

### iouring_entries

?> `io_uring`のキューサイズを設定します。デフォルト値は`8192`です。渡された値が`2のべき乗数`でなければ、カーネルはその値に最も近い、それ以上の`2のべき乗数`に変更します。

!> 渡された値が大きすぎると、カーネルは例外を投げ出し、プログラムを終了させます。

!> `liburing`がシステムにインストールされ、`Swoole`をコンパイルする際に`--enable-iouring`フラグを有効にしなければ使用できません。

!> `liburing`がシステムにインストールされ、`Swoole v6.0`以上のバージョンでコンパイルし、`--enable-iouring`フラグを有効にしなければ使用できません。
