# Konfigurasi

`Swoole` mengatur beberapa parameter kunci yang memengaruhi karakteristik operasi file `asinkron`, bisa diatur melalui `swoole_async_set` atau `Swoole\Server->set()`.

Contoh:

```php
<?php
swoole_async_set([
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024,
    'iouring_workers' => 16,
    'iouring_flag' => SWOOLE_IOURING_SQPOLL
]);

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024,
    'iouring_workers' => 16,
    'iouring_flag' => SWOOLE_IOURING_SQPOLL
]);
```

### aio_core_worker_num

?> Mengatur jumlah minimum thread di thread pool, nilai default adalah `jumlah inti CPU`.

### aio_worker_num

?> Mengatur jumlah maksimum thread di thread pool, nilai default adalah `jumlah inti CPU * 8`.

### aio_max_wait_time

?> Mengatur waktu tunggu maksimum thread di thread pool, nilai default adalah `0`.

### aio_max_idle_time

?> Mengatur waktu idle thread di thread pool, nilai default adalah `1s`.

### iouring_entries

?> Mengatur ukuran antrian `io_uring`, default `8192`. Jika nilai yang diberikan bukan `pangkat 2`, kernel akan mengubahnya ke pangkat 2 terdekat yang lebih besar dari nilai tersebut.

!> Jika nilai yang diberikan terlalu besar, kernel akan melempar exception dan menghentikan program.

!> Hanya bisa digunakan jika sistem menginstal `liburing` dan kompilasi `Swoole` mengaktifkan `--enable-iouring`.

### iouring_workers

?> Mengatur jumlah worker thread `io_uring`, nilai default adalah `jumlah inti CPU * 4`.

!> Jika nilai yang diberikan terlalu besar, kernel akan melempar exception dan menghentikan program.

!> Hanya bisa digunakan jika sistem menginstal `liburing` dan kompilasi `Swoole` mengaktifkan `--enable-iouring`.

### iouring_flag

?> Mengatur mode kerja `io_uring`, nilai default adalah `SWOOLE_IOURING_DEFAULT`.

- `SWOOLE_IOURING_DEFAULT`, mode interrupt-driven. Mengirim request `I/O` melalui system call `io_uring_enter`, lalu langsung memeriksa status completion queue untuk menentukan apakah sudah selesai.
- `SWOOLE_IOURING_SQPOLL`, mode kernel polling. Kernel akan membuat kernel thread untuk mengirim dan memanen request `I/O`, hampir sepenuhnya menghilangkan context switch user/kernel, performa lebih baik.

!> Jika mode yang diberikan salah, kernel akan menggunakan `SWOOLE_IOURING_DEFAULT` mode interrupt-driven.

!> `SWOOLE_IOURING_SQPOLL` mengorbankan sebagian performa CPU untuk mendapatkan performa baca/tulis disk (IOPS) yang lebih tinggi, sehingga QPS mungkin lebih rendah dari mode default.

!> Jika tekanan server tidak besar, bisa menggunakan `SWOOLE_IOURING_DEFAULT` untuk QPS lebih tinggi. Jika bottleneck performa server ada di disk, bisa menggunakan `SWOOLE_IOURING_SQPOLL`.
