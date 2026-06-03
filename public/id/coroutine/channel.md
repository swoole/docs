# Coroutine\Channel

> Sebaiknya baca [ikhtisar](/coroutine) terlebih dahulu untuk memahami konsep dasar coroutine sebelum membaca bagian ini.

Channel, digunakan untuk komunikasi antar coroutine, mendukung banyak coroutine produsen dan banyak coroutine konsumen. Infrastruktur otomatis menangani perpindahan dan penjadwalan coroutine.

## Prinsip Implementasi

  * Channel mirip dengan `Array` di `PHP`, hanya menggunakan memori, tanpa alokasi resource tambahan. Semua operasi adalah operasi memori, tanpa konsumsi `IO`.
  * Infrastruktur menggunakan PHP reference counting, tanpa copy memori. Bahkan mengirim string atau array besar tidak menghasilkan overhead performa tambahan.
  * `channel` berdasarkan reference counting, zero-copy.

## Contoh Penggunaan

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;

run(function(){
    $channel = new Channel(1);
    Coroutine::create(function () use ($channel) {
        for($i = 0; $i < 10; $i++) {
            Coroutine::sleep(1.0);
            $channel->push(['rand' => rand(1000, 9999), 'index' => $i]);
            echo "{$i}\n";
        }
    });
    Coroutine::create(function () use ($channel) {
        while(1) {
            $data = $channel->pop(2.0);
            if ($data) {
                var_dump($data);
            } else {
                assert($channel->errCode === SWOOLE_CHANNEL_TIMEOUT);
                break;
            }
        }
    });
});
```

## Method

### __construct()

Method konstruktor channel.

```php
Swoole\Coroutine\Channel::__construct(int $capacity = 1)
```

  * **Parameter** 

    * **`int $capacity`**
      * **Fungsi**: Mengatur kapasitas [harus bilangan bulat >= `1`]
      * **Bawaan**: `1`
      * **Nilai Lain**: Tidak ada

!> Infrastruktur menggunakan PHP reference counting untuk menyimpan variabel, buffer hanya perlu memori sebesar `$capacity * sizeof(zval)`, di `PHP7` ukuran `zval` adalah `16` byte. Contoh: `$capacity = 1024`, maka `Channel` maksimal akan memakan `16K` memori.

!> Saat digunakan di `Server`, harus dibuat setelah [onWorkerStart](/server/events?id=onworkerstart).

### push()

Menulis data ke channel.

```php
Swoole\Coroutine\Channel->push(mixed $data, float $timeout = -1): bool
```

  * **Parameter** 

    * **`mixed $data`**
      * **Fungsi**: Data push [bisa berupa variabel PHP apa pun, termasuk anonymous function dan resource]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

      !> Untuk menghindari ambiguitas, jangan menulis `null` atau `false` ke channel.

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout
      * **Satuan**: Detik [mendukung float, seperti `1.5` berarti `1s` + `500ms`]
      * **Bawaan**: `-1`
      * **Nilai Lain**: Tidak ada
      * **Versi**: Swoole versi >= v4.2.12

      !> Saat channel penuh, `push` akan menangguhkan coroutine saat ini. Jika dalam waktu yang ditentukan tidak ada konsumen yang mengonsumsi data, akan terjadi timeout, infrastruktur akan melanjutkan coroutine saat ini, panggilan `push` akan segera mengembalikan `false`, gagal menulis.

  * **Return Value**

    * Mengembalikan `true` jika berhasil
    * Mengembalikan `false` jika channel ditutup, bisa gunakan `$channel->errCode` untuk kode error

  * **Ekstensi**

    * **Channel Penuh**

      * Otomatis `yield` coroutine saat ini. Setelah coroutine konsumen lain `pop` mengonsumsi data, channel bisa ditulis, akan kembali `resume` coroutine saat ini.
      * Saat banyak coroutine produsen `push` bersamaan, infrastruktur otomatis mengantre dan `resume` coroutine produsen tersebut secara berurutan.

    * **Channel Kosong**

      * Otomatis membangunkan salah satu coroutine konsumen
      * Saat banyak coroutine konsumen `pop` bersamaan, infrastruktur otomatis mengantre dan `resume` coroutine konsumen tersebut secara berurutan.

!> `Coroutine\Channel` menggunakan memori lokal, memori antar proses berbeda terisolasi. Hanya bisa melakukan `push` dan `pop` di coroutine berbeda dalam proses yang sama.

### pop()

Membaca data dari channel.

```php
Swoole\Coroutine\Channel->pop(float $timeout = -1): mixed
```

  * **Parameter** 

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout
      * **Satuan**: Detik [mendukung float, seperti `1.5` berarti `1s` + `500ms`]
      * **Bawaan**: `-1` [berarti tidak pernah timeout]
      * **Nilai Lain**: Tidak ada
      * **Versi**: Swoole versi >= v4.0.3

  * **Return Value**

    * Nilai kembali bisa berupa variabel PHP apa pun, termasuk anonymous function dan resource
    * Mengembalikan `false` jika channel ditutup

  * **Ekstensi**

    * **Channel Penuh**

      * Setelah `pop` mengonsumsi data, akan otomatis membangunkan salah satu coroutine produsen untuk menulis data baru
      * Saat banyak coroutine produsen `push` bersamaan, infrastruktur otomatis mengantre dan `resume` secara berurutan.

    * **Channel Kosong**

      * Otomatis `yield` coroutine saat ini. Setelah coroutine produsen lain `push` memproduksi data, channel bisa dibaca, akan kembali `resume` coroutine saat ini.
      * Saat banyak coroutine konsumen `pop` bersamaan, infrastruktur otomatis mengantre dan `resume` secara berurutan.

### stats()

Mendapatkan status channel.

```php
Swoole\Coroutine\Channel->stats(): array
```

  * **Return Value**

    Mengembalikan array, channel buffer akan mencakup `4` item informasi, channel non-buffer mengembalikan `2` item informasi.
    
    - `consumer_num` Jumlah konsumen, menandakan channel saat ini kosong, ada `N` coroutine yang menunggu coroutine lain memanggil `push` untuk memproduksi data
    - `producer_num` Jumlah produsen, menandakan channel saat ini penuh, ada `N` coroutine yang menunggu coroutine lain memanggil `pop` untuk mengonsumsi data
    - `queue_num` Jumlah elemen di dalam channel

```php
array(
  "consumer_num" => 0,
  "producer_num" => 1,
  "queue_num" => 10
);
```

### close()

Menutup channel. Dan membangunkan semua coroutine yang menunggu baca/tulis.

```php
Swoole\Coroutine\Channel->close(): bool
```

!> Membangunkan semua coroutine produsen, method `push` mengembalikan `false`; membangunkan semua coroutine konsumen, method `pop` mengembalikan `false`.

### length()

Mendapatkan jumlah elemen di dalam channel.

```php
Swoole\Coroutine\Channel->length(): int
```

### isEmpty()

Menentukan apakah channel saat ini kosong.

```php
Swoole\Coroutine\Channel->isEmpty(): bool
```

### isFull()

Menentukan apakah channel saat ini penuh.

```php
Swoole\Coroutine\Channel->isFull(): bool
```

## Properti

### capacity

Kapasitas buffer channel.

Kapasitas yang diatur di [konstruktor](/coroutine/channel?id=__construct) akan disimpan di sini, namun **jika kapasitas yang diatur kurang dari 1**, variabel ini akan sama dengan 1.

```php
Swoole\Coroutine\Channel->capacity: int
```

### errCode

Mendapatkan kode error.

```php
Swoole\Coroutine\Channel->errCode: int
```

  * **Return Value**

Nilai | Konstanta | Fungsi
---|---|---
0 | SWOOLE_CHANNEL_OK | Bawaan, berhasil
-1 | SWOOLE_CHANNEL_TIMEOUT | Timeout, pop gagal (timeout)
-2 | SWOOLE_CHANNEL_CLOSED | Channel sudah ditutup, melanjutkan operasi channel
