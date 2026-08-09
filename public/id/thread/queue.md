# Container Konkuren Aman Queue

Membuat struktur `Queue` konkuren, bisa dikirim sebagai parameter thread ke child thread. Operasi baca/tulis terlihat di thread lain.

## Karakteristik
- `Thread\Queue` adalah struktur data first-in-first-out.

- `Map`, `ArrayList`, `Queue` akan mengalokasikan memori secara otomatis, tidak perlu alokasi tetap seperti `Table`.

- Sistem akan mengunci secara otomatis, aman untuk thread

- Tipe variabel yang bisa dikirim lihat [Thread Parameter Transfer](thread/transfer.md)

- Tidak mendukung iterator, menggunakan `C++ std::queue`, hanya mendukung operasi first-in-first-out

- Object `Map`, `ArrayList`, `Queue` harus dikirim sebagai parameter thread ke child thread sebelum thread dibuat

- `Thread\Queue` hanya bisa push/pop elemen, tidak bisa mengakses elemen secara acak

- `Thread\Queue` memiliki built-in thread condition variable, bisa membangunkan/menunggu thread lain di operasi `push/pop`

## Contoh

```php
use Swoole\Thread;
use Swoole\Thread\Queue;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $queue = new Queue;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i, $queue);
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16)), Queue::NOTIFY_ONE);
        usleep(random_int(10000, 100000));
    }
    $n = 4;
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1];
    while (1) {
        $job = $queue->pop(-1);
        if (!$job) {
            break;
        }
        var_dump($job);
    }
}
```

## Konstanta


Nama | Fungsi
---|---
`Queue::NOTIFY_ONE` | Membangunkan satu thread
`Queue::NOTIFY_ALL` | Membangunkan semua thread

## Method

### __construct()
Konstruktor container konkuren aman `Queue`

```php
Swoole\Thread\Queue->__construct()
```

### push()
Menulis data ke bagian belakang antrian

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

  * **Parameter**
      * `mixed $value`
          * Fungsi: Konten data yang akan ditulis.
          * Nilai default: Tidak ada.
          * Nilai lain: Tidak ada.

      !> Untuk menghindari ambiguitas, jangan menulis `null` dan `false` ke dalam channel
  
      * `int $notify`
          * Fungsi: Apakah akan memberi tahu thread yang menunggu membaca data.
          * Nilai default: `0`, tidak akan membangunkan thread mana pun
          * Nilai lain: `Swoole\Thread\Queue::NOTIFY_ONE` membangunkan satu thread, `Swoole\Thread\Queue::NOTIFY_ALL` membangunkan semua thread.


### pop()
Mengambil data dari bagian depan antrian

```php
Swoole\Thread\Queue()->pop(float $timeout = 0): mixed
```

* **Parameter**
    * `float $wait`
        * Fungsi: Waktu tunggu.
        * Nilai default: `0`, berarti tidak menunggu.
        * Nilai lain: Jika tidak `0`, berarti saat antrian kosong akan menunggu producer `push()` data dalam `$timeout` detik. Jika negatif, berarti tidak pernah timeout.

* **Return value**
    * Mengembalikan data dari depan antrian, saat antrian kosong langsung mengembalikan `NULL`.

> Saat menggunakan `Queue::NOTIFY_ALL` untuk membangunkan semua thread, hanya satu thread yang bisa mendapatkan data yang ditulis oleh operasi `push()`

### count()
Mendapatkan jumlah elemen antrian

```php
Swoole\Thread\Queue()->count(): int
```

* **Return value**
    * Mengembalikan jumlah antrian.

### clean()
Menghapus semua elemen

```php
Swoole\Thread\Queue()->clean(): void
```
