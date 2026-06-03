# Swoole\Async\Client

`Swoole\Async\Client`, selanjutnya disebut `Client`, adalah klien jaringan asinkron non-blocking `TCP/UDP/UnixSocket`. Klien asinkron perlu mengatur fungsi callback event, bukan menunggu secara sinkron.

- Klien asinkron adalah subclass dari `Swoole\Client`, dapat memanggil beberapa method dari klien blocking sinkron  
- Hanya tersedia di versi `6.0` atau lebih

## Contoh Lengkap

```php
$cli = new Swoole\Async\Client(SWOOLE_SOCK_TCP);

$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());
    $client->send(RandStr::gen(1024, RandStr::ALL));
});

$client->on("receive", function(Swoole\Async\Client $client, string $data){
    $recv_len = strlen($data);
    $client->send(RandStr::gen(1024, RandStr::ALL));
    $client->close();
    Assert::false($client->isConnected());
});

$client->on("error", function(Swoole\Async\Client $client) {
    echo "error";
});

$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});

$client->connect("127.0.0.1", 9501, 0.2);
```

## Method

Halaman ini hanya mencantumkan method yang berbeda dari `Swoole\Client`. Untuk method yang tidak dimodifikasi oleh subclass, lihat [klien blocking sinkron](client.md).

### __construct()

Method konstruktor, lihat konstruktor kelas induk

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> Parameter kedua klien asinkron harus `true`

### on()

Mendaftarkan fungsi callback event `Client`.

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> Memanggil method `on` berulang kali akan menimpa pengaturan sebelumnya

* **Parameter**

    * `string $event`
      * Fungsi: Nama event callback, tidak sensitif huruf besar/kecil
      * Default: Tidak ada
      * Nilai Lain: Tidak ada

    * `callable $callback`
      * Fungsi: Fungsi callback
      * Default: Tidak ada
      * Nilai Lain: Tidak ada

      !> Dapat berupa string nama fungsi, method statis kelas, array method objek, fungsi anonim. Lihat [bagian ini](/learn?id=几种设置回调函数的方式).

* **Return Value**

    * Mengembalikan `true` jika operasi berhasil, `false` jika gagal.

### isConnected()
Menentukan apakah klien saat ini sudah terhubung dengan server.

```php
Swoole\Async\Client->isConnected(): bool
```

* Mengembalikan `true` jika terhubung, `false` jika tidak terhubung

### sleep()

Menghentikan sementara penerimaan data. Setelah dipanggil, akan dihapus dari event loop dan tidak lagi memicu event penerimaan data, kecuali method `wakeup()` dipanggil untuk melanjutkan.

```php
Swoole\Async\Client->sleep(): bool
```

* Mengembalikan `true` jika operasi berhasil, `false` jika gagal

### wakeup()

Melanjutkan penerimaan data. Setelah dipanggil, akan ditambahkan ke event loop.

```php
Swoole\Async\Client->wakeup(): bool
```

* Mengembalikan `true` jika operasi berhasil, `false` jika gagal

### enableSSL()

Mengaktifkan enkripsi `SSL/TLS` secara dinamis, biasanya digunakan untuk klien `startTLS`. Kirim data teks biasa dulu setelah koneksi dibuat, lalu mulai transmisi terenkripsi.

```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* Fungsi ini hanya bisa dipanggil setelah `connect` berhasil
* Klien asinkron harus mengatur `$callback`, yang akan dipanggil setelah jabat tangan `SSL` selesai
* Mengembalikan `true` jika operasi berhasil, `false` jika gagal

## Event Callback

### connect
Terpicu setelah koneksi dibuat. Jika proxy `HTTP` atau `Socks5` dan enkripsi tunnel `SSL` diatur, akan terpicu setelah jabat tangan proxy selesai dan jabat tangan enkripsi `SSL` selesai.

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

Setelah event callback ini, menggunakan `isConnected()` akan mengembalikan `true`

### error
Terpicu saat koneksi gagal dibuat. Dapat membaca `$client->errCode` untuk mendapatkan informasi error.

```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```

- Perhatikan bahwa `connect` dan `error` hanya akan terpicu salah satu, koneksi berhasil atau gagal, hanya ada satu hasil
- `Client::connect()` mungkin langsung mengembalikan `false`, menandakan koneksi gagal, saat itu callback `error` tidak akan dijalankan, pastikan memeriksa nilai kembali panggilan `connect`
- Event `error` adalah hasil asinkron, ada waktu tunggu `IO` antara inisiasi koneksi hingga event `error` terpicu
- `connect` gagal langsung adalah kegagalan seketika, dipicu langsung oleh sistem operasi, tidak ada waktu tunggu `IO`

### receive
Terpicu setelah data diterima

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```

- Jika tidak ada protokol yang diatur, seperti `EOF` atau `LENGTH`, panjang data maksimum yang dikembalikan adalah `64K`
- Jika parameter pemrosesan protokol diatur, panjang data maksimum ditentukan oleh parameter `package_max_length`, default `2M`
- `$data` pasti tidak kosong. Jika ada error sistem atau koneksi ditutup, event `close` akan terpicu

### close
Terpicu saat koneksi ditutup

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```
