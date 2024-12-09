# Konfiguration

Die `Swoole`-Konfiguration legt mehrere Schlüsselparameter fest, die die Merkmale der `asynchronen`-Dateioperationen beeinflussen können und durch `swoole_async_set` oder `Swoole\Server->set()` festgelegt werden können.

Beispiel:

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

?> Legt die minimale Anzahl der Threads im Threadpool fest, der Standardwert ist `CPU-Kernanzahl`.

### aio_worker_num

?> Legt die maximale Anzahl der Threads im Threadpool fest, der Standardwert ist `CPU-Kernanzahl * 8`.

### aio_max_wait_time

?> Legt die maximale Wartezeit für Threads im Threadpool fest, der Standardwert ist `0`.

### aio_max_idle_time

?> Legt die maximale Idle-Zeit für Threads im Threadpool fest, der Standardwert ist `1s`.

### iouring_entries

?> Legt die Größe der `io_uring`-Warteschlange fest, der Standardwert ist `8192`. Wenn der angegebene Wert kein Potenz von 2 ist, wird der Kernwert auf das nächste, größere Potenz von 2 angepasst.

!> Wenn der angegebene Wert zu groß ist, wird der Kern einen Ausnahme抛出 und das Programm beenden.

!> Die Verwendung ist nur möglich, wenn das System `liburing` installiert hat und `Swoole` mit `--enable-iouring`编译.

!> Die Verwendung ist nur möglich, wenn das System `liburing` installiert hat und `Swoole v6.0` oder höher编译, mit `--enable-iouring` aktiviert.
