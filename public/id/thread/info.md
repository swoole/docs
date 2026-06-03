# Method dan Properti

## Method

### __construct()
Konstruktor multi-thread

```php
Swoole\Thread->__construct(string $script_file, mixed ...$args)
```
* **Parameter**
    * `string $script_file`
        * Fungsi: File yang akan dijalankan setelah thread mulai.
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

    * `mixed $args`
        * Fungsi: Data shared yang dikirim dari main thread ke child thread, bisa didapatkan di child thread menggunakan `Swoole\Thread::getArguments()`.
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

!> Kegagalan pembuatan thread akan melempar `Swoole\Exception`, bisa ditangkap dengan `try catch`.

### join()
Main thread menunggu child thread selesai. Jika child thread masih berjalan, `join()` akan memblokir sampai child thread selesai.

```php
Swoole\Thread->join(): bool
```
* **Return value**
    * Mengembalikan `true` jika sukses, `false` jika gagal.

### joinable()
Memeriksa apakah child thread bisa di-`join()`. Ada dua kondisi method ini mengembalikan `false`:
1. Thread telah menjalankan `detach()`, tidak bisa menggunakan `join()` untuk menunggu thread selesai
2. Thread sudah selesai dan sudah di-`join()` untuk direcycle

```php
Swoole\Thread->joinable(): bool
```

### isAlive()
Memeriksa apakah thread masih hidup. Method ini bisa dipanggil kapan saja, terlepas dari apakah thread sudah menjalankan `detach()` atau `join()`.

> Method ini tidak akan memblokir

```php
Swoole\Thread->isAlive(): bool
```

* **Return value**
    * Mengembalikan `true` jika thread masih hidup, `false` jika sudah selesai.

### detach()
Melepaskan child thread dari kendali main thread, tidak perlu lagi `join()` untuk menunggu thread selesai.

```php
Swoole\Thread->detach(): bool
```
* **Return value**
    * Mengembalikan `true` jika sukses, `false` jika gagal.

### getId()
Static method, mendapatkan `ID` thread saat ini.

```php
Swoole\Thread::getId(): int
```
* **Return value**
    * Mengembalikan integer yang merepresentasikan ID thread saat ini.

### getArguments()
Static method, mendapatkan data shared yang dikirim dari main thread saat menggunakan `new Swoole\Thread()`, dipanggil di child thread.

```php
Swoole\Thread::getArguments(): ?array
```

* **Return value**
    * Di child thread mengembalikan data shared yang dikirim dari parent process.

?> Main thread tidak akan memiliki thread arguments. Kamu bisa membedakan parent dan child thread dengan mengecek apakah thread arguments kosong, sehingga mereka bisa menjalankan logika yang berbeda.
```php
use Swoole\Thread;

$args = Thread::getArguments(); // Jika main thread, $args kosong; jika child thread, $args tidak kosong
if (empty($args)) {
    # Main thread
    new Thread(__FILE__, 'child thread');
    echo "main thread\n";
} else {
    # Child thread
    var_dump($args); // Output: ['child thread']
}
```

### getInfo()
Static method, mendapatkan informasi tentang lingkungan multi-thread saat ini.

```php
Swoole\Thread::getInfo(): array
```
Array yang dikembalikan:

- `is_main_thread`: Apakah thread saat ini adalah main thread
- `is_shutdown`: Apakah thread sudah dimatikan
- `thread_num`: Jumlah thread yang aktif

### activeCount()
Static method, mendapatkan jumlah thread yang aktif saat ini

```php
Swoole\Thread::activeCount(): int
```
* **Return value**
    * Mengembalikan integer yang merepresentasikan jumlah thread aktif. Nilai selalu lebih dari atau sama dengan `1`

### yield()
Static method, melepaskan timeslice eksekusi thread saat ini. Setelah dipanggil, thread saat ini akan ditangguhkan, menunggu scheduler OS memprioritaskan thread lain, dan mengalokasikan timeslice kembali ke thread saat ini di siklus berikutnya.

```php
Swoole\Thread::yield(): void
```


### getPriority()
Static method, mendapatkan informasi scheduling thread saat ini

```php
Swoole\Thread->getPriority(): array
```
Array yang dikembalikan:

- `policy`: Kebijakan scheduling thread
- `priority`: Prioritas scheduling thread

### setPriority()
Static method, mengatur prioritas dan kebijakan scheduling thread saat ini

?> Hanya pengguna `root` yang bisa menyesuaikan, pengguna non-`root` akan ditolak

```php
Swoole\Thread->setPriority(int $priority, int $policy = -1): bool
```

* **Parameter**
    * `int $priority`
        * Fungsi: Mengatur prioritas scheduling thread
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

    * `mixed $policy`
        * Fungsi: Mengatur kebijakan prioritas scheduling thread
        * Nilai default: `-1`, berarti tidak menyesuaikan kebijakan scheduling.
        * Nilai lain: Konstanta terkait `Thread::SCHED_*`.

* **Return value**
    * Sukses mengembalikan `true`
    * Gagal mengembalikan `false`, gunakan `swoole_last_error()` untuk mendapatkan informasi error

> `SCHED_BATCH/SCHED_ISO/SCHED_IDLE/SCHED_DEADLINE` hanya tersedia di sistem `Linux`

> Thread dengan kebijakan `SCHED_FIFO/SCHED_RR` umumnya adalah real-time thread, prioritasnya lebih tinggi dari thread biasa, bisa mendapatkan lebih banyak timeslice `CPU`

### getAffinity()
Static method, mendapatkan afinitas `CPU` thread saat ini

```php
Swoole\Thread->getAffinity(): array
```
Nilai yang dikembalikan berupa array, elemennya adalah nomor inti `CPU`, contoh: `[0, 1, 3, 4]` berarti thread ini akan dijadwalkan ke inti `CPU` `0/1/3/4`

### setAffinity()
Static method, mengatur afinitas `CPU` thread saat ini

```php
Swoole\Thread->setAffinity(array $cpu_set): bool
```

* **Parameter**
    * `array $cpu_set`
        * Fungsi: Daftar inti `CPU`, contoh `[0, 1, 3, 4]`
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

* **Return value**
    * Sukses mengembalikan `true`
    * Gagal mengembalikan `false`, gunakan `swoole_last_error()` untuk mendapatkan informasi error

### setName()
Static method, mengatur nama thread saat ini. Memberikan tampilan yang lebih ramah saat menggunakan alat seperti `ps` dan `gdb`.

```php
Swoole\Thread->setName(string $name): bool
```

* **Parameter**
    * `string $name`
        * Fungsi: Nama thread
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

* **Return value**
    * Sukses mengembalikan `true`
    * Gagal mengembalikan `false`, gunakan `swoole_last_error()` untuk mendapatkan informasi error

```shell
$ ps aux|grep -v grep|grep pool.php
swoole   2226813  0.1  0.1 423860 49024 pts/6    Sl+  17:38   0:00 php pool.php

$ ps -T -p 2226813
    PID    SPID TTY          TIME CMD
2226813 2226813 pts/6    00:00:00 Master Thread
2226813 2226814 pts/6    00:00:00 Worker Thread 0
2226813 2226815 pts/6    00:00:00 Worker Thread 1
2226813 2226816 pts/6    00:00:00 Worker Thread 2
2226813 2226817 pts/6    00:00:00 Worker Thread 3
```

### getNativeId()
Mendapatkan `ID` sistem thread, mengembalikan integer, mirip dengan `PID` process.

```php
Swoole\Thread->getNativeId(): int
```

Fungsi ini memanggil system call `gettid()` di sistem `Linux`, mendapatkan `ID` thread OS, berupa short integer. Saat thread process dimusnahkan, ID ini mungkin akan digunakan kembali oleh OS.

`ID` ini bisa digunakan untuk debugging dengan `gdb`, `strace`, contoh `gdb -p $tid`. Juga bisa membaca `/proc/{PID}/task/{ThreadNativeId}` untuk mendapatkan informasi eksekusi thread.

## Properti

### id

Mendapatkan `ID` child thread melalui properti object ini, bertipe `int`.

> Properti ini hanya bisa digunakan di parent thread. Child thread tidak bisa mendapatkan object `$thread`, harus menggunakan static method `Thread::getId()` untuk mendapatkan `ID` thread

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```

## Konstanta

Nama | Fungsi
---|---
`Thread::HARDWARE_CONCURRENCY` | Jumlah thread konkurensi hardware, biasanya jumlah inti `CPU`
`Thread::API_NAME` | Nama `API` thread, misalnya `POSIX Threads`
`Thread::SCHED_OTHER` | Kebijakan scheduling thread `SCHED_OTHER`
`Thread::SCHED_FIFO` | Kebijakan scheduling thread `SCHED_FIFO`
`Thread::SCHED_RR` | Kebijakan scheduling thread `SCHED_RR`
`Thread::SCHED_BATCH` | Kebijakan scheduling thread `SCHED_BATCH`
`Thread::SCHED_ISO` | Kebijakan scheduling thread `SCHED_ISO`
`Thread::SCHED_IDLE` | Kebijakan scheduling thread `SCHED_IDLE`
`Thread::SCHED_DEADLINE` | Kebijakan scheduling thread `SCHED_DEADLINE`
