# Swoole\Server\Task

Berikut adalah penjelasan detail tentang `Swoole\Server\Task`. Class ini sangat sederhana, tetapi Anda tidak bisa mendapatkan objek `Task` dengan menggunakan `new Swoole\Server\Task()`. Objek semacam itu sama sekali tidak mengandung informasi server apa pun, dan menjalankan method `Swoole\Server\Task` apa pun akan menghasilkan fatal error.

```shell
Invalid instance of Swoole\Server\Task in /home/task.php on line 3
```

## Properti

### $data
Data `data` yang dikirimkan dari proses `worker` ke proses `task`. Properti ini adalah string dengan tipe `string`.

```php
Swoole\Server\Task->data
```

### $dispatch_time
Mengembalikan waktu `dispatch_time` saat data mencapai proses `task`. Properti ini adalah `double`.

```php
Swoole\Server\Task->dispatch_time
```

### $id
Mengembalikan id tugas. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Task->id
```

### $worker_id
Mengembalikan dari proses `worker` mana data berasal. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Task->worker_id
```

### $flags
Beberapa informasi flag `flags` dari tugas asynchronous ini. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Task->flags
```

?> Hasil yang dikembalikan oleh `flags` adalah salah satu dari tipe berikut:
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCK menunjukkan bahwa ini bukan dikirim oleh proses `Worker` ke proses `task`. Jika `Swoole\Server::finish()` dipanggil dalam event `onTask`, akan ada peringatan.
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCK menunjukkan bahwa fungsi callback terakhir dalam `Swoole\Server::finish()` bukan null, event `onFinish` tidak akan dieksekusi, dan hanya fungsi callback ini yang akan dieksekusi.
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCK menunjukkan bahwa tugas akan diproses dalam bentuk coroutine.
  - SW_TASK_NONBLOCK nilai default, ketika ketiga kondisi di atas tidak terpenuhi.

## Method

### finish()

Digunakan dalam [Task process](/learn?id=taskworker-process) untuk memberi tahu proses `Worker` bahwa tugas yang dikirim telah selesai. Fungsi ini dapat mengirimkan data hasil ke proses `Worker`.

```php
Swoole\Server\Task->finish(mixed $data): bool
```

* **Parameter**

    * `mixed $data`

        * Fungsi: Konten hasil dari pemrosesan tugas
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

* **Tips**
    * Method `finish` dapat dipanggil beberapa kali secara berurutan, proses `Worker` akan memicu event [onFinish](/server/events?id=onfinish) beberapa kali
    * Setelah memanggil method `finish` dalam fungsi callback [onTask](/server/events?id=ontask), data `return` tetap akan memicu event [onFinish](/server/events?id=onfinish)
    * `Swoole\Server\Task->finish` bersifat opsional. Jika proses `Worker` tidak peduli dengan hasil eksekusi tugas, fungsi ini tidak perlu dipanggil
    * Dalam fungsi callback [onTask](/server/events?id=ontask), `return` string sama dengan memanggil `finish`

* **Catatan**

    !> Menggunakan fungsi `Swoole\Server\Task->finish` harus mengatur callback [onFinish](/server/events?id=onfinish) untuk `Server`. Fungsi ini hanya dapat digunakan dalam callback [onTask](/server/events?id=ontask) dari [Task process](/learn?id=taskworker-process)

### pack()

Menserialisasi data yang diberikan.

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

* **Parameter**

    * `mixed $data`

        * Fungsi: Konten hasil dari pemrosesan tugas
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

* **Nilai Kembalian**
    * Mengembalikan hasil serialisasi jika berhasil.

### unpack()

Mendeserialisasi data yang diberikan.

```php
Swoole\Server\Task->unpack(string $data): mixed
```

* **Parameter**

    * `string $data`

        * Fungsi: Data yang akan dideserialisasi
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

* **Nilai Kembalian**
    * Mengembalikan hasil deserialisasi jika berhasil.

## Contoh Penggunaan
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
