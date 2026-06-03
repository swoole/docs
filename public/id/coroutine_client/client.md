# Klien TCP/UDP Coroutine

`Coroutine\Client` menyediakan kode enkapsulasi untuk protokol transport `TCP`, `UDP`, dan [unixSocket](/learn?id=apa-itu-IPC) untuk [Klien Socket](/coroutine_client/socket), cukup menggunakan `new Swoole\Coroutine\Client`.

* **Prinsip Implementasi**

    * Semua method `Coroutine\Client` yang melibatkan permintaan jaringan akan menjalani penjadwalan coroutine oleh `Swoole`, yang tidak perlu diketahui oleh lapisan bisnis.
    * Penggunaan dan method mode sinkron dari `Coroutine\Client` sepenuhnya konsisten dengan [Client](/client).
    * Pengaturan timeout `connect` juga berlaku untuk timeout `Connect`, `Recv`, dan `Send`.

* **Hubungan Pewarisan**

    * `Coroutine\Client` tidak mewarisi dari [Client](/client), tetapi semua method yang disediakan `Client` dapat digunakan di `Coroutine\Client`. Silakan lihat [Swoole\Client](/client?id=method) untuk informasi lebih detail. Tidak akan disebutkan lebih lanjut di sini.
    * Di `Coroutine\Client`, method `set` dapat digunakan untuk mengatur [opsi konfigurasi](/client?id=konfigurasi), dan penggunaannya sepenuhnya konsisten dengan `Client->set`. Untuk fungsi yang berbeda penggunaannya, akan dijelaskan secara terpisah di bagian fungsi `set()`.

* **Contoh Penggunaan**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

* **Penanganan Protokol**

Klien coroutine juga mendukung penanganan protokol panjang dan `EOF`, dan metode pengaturannya sepenuhnya konsisten dengan [Swoole\Client](/client?id=konfigurasi).

```php
$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0, //Byte ke-N adalah nilai panjang paket
    'package_body_offset'   => 4, //Mulai byte ke berapa untuk menghitung panjang
    'package_max_length'    => 2000000, //Panjang maksimum protokol
));
```

### connect()

Menghubungkan ke server jarak jauh.

```php
Swoole\Coroutine\Client->connect(string $host, int $port, float $timeout = 0.5): bool
```

  * **Parameter**

    * **`string $host`**
      * **Fungsi**: Alamat server jarak jauh【Sistem akan secara otomatis melakukan penjadwalan coroutine untuk meresolusi domain ke alamat IP】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port server jarak jauh
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Timeout untuk I/O jaringan; termasuk `connect/send/recv`, saat timeout terjadi, koneksi akan otomatis di-`close`, lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Satuan nilai**: detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: `0.5s`
      * **Nilai lain**: Tidak ada

* **Catatan**

    * Jika koneksi gagal, akan mengembalikan `false`
    * Saat timeout kembali, periksa `$cli->errCode` adalah `110`

* **Percobaan Ulang Gagal**

!> Setelah `connect` gagal, jangan langsung mencoba menyambung ulang. Harus menggunakan `close` untuk menutup `socket` yang ada, lalu lakukan `connect` ulang.

```php
//Koneksi gagal
if ($cli->connect('127.0.0.1', 9501) == false) {
    //Tutup socket yang ada
    $cli->close();
    //Coba ulang
    $cli->connect('127.0.0.1', 9501);
}
```

* **Contoh**

```php
if ($cli->connect('127.0.0.1', 9501)) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}

if ($cli->connect('/tmp/rpc.sock')) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}
```

### isConnected()

Mengembalikan status koneksi Client

```php
Swoole\Coroutine\Client->isConnected(): bool
```

  * **Nilai Kembali**

    * Mengembalikan `false`, berarti saat ini tidak terhubung ke server
    * Mengembalikan `true`, berarti saat ini terhubung ke server

!> Method `isConnected` mengembalikan status lapisan aplikasi, hanya menunjukkan bahwa `Client` telah menjalankan `connect` dan berhasil terhubung ke `Server`, dan belum menjalankan `close` untuk menutup koneksi. `Client` dapat menjalankan operasi `send`, `recv`, `close`, tetapi tidak dapat menjalankan `connect` lagi.  
Ini tidak menjamin koneksi pasti dapat digunakan; saat menjalankan `send` atau `recv` masih mungkin mengembalikan error, karena lapisan aplikasi tidak dapat memperoleh status koneksi `TCP` di tingkat bawah. Status ketersediaan koneksi yang sebenarnya hanya diperoleh saat lapisan aplikasi berinteraksi dengan kernel saat menjalankan `send` atau `recv`.

### send()

Mengirim data.

```php
Swoole\Coroutine\Client->send(string $data): int|bool
```

  * **Parameter**

    * **`string $data`**
      * **Fungsi**: Data yang akan dikirim, harus bertipe string, mendukung data biner
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * Pengiriman berhasil mengembalikan jumlah byte yang ditulis ke buffer `Socket`. Sistem akan berusaha mengirim semua data semaksimal mungkin. Jika jumlah byte yang dikembalikan berbeda dari panjang `$data` yang diberikan, mungkin `Socket` telah ditutup oleh lawan, dan kode error akan dikembalikan pada pemanggilan `send` atau `recv` berikutnya.

  * Pengiriman gagal mengembalikan false, dapat menggunakan `$client->errCode` untuk mendapatkan penyebab error.

### recv()

Method recv digunakan untuk menerima data dari server.

```php
Swoole\Coroutine\Client->recv(float $timeout = 0): string|bool
```

  * **Parameter**

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout
      * **Satuan nilai**: detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

    !> Saat mengatur timeout, prioritaskan parameter yang ditentukan, lalu gunakan konfigurasi `timeout` yang diberikan di method `set`. Kode error untuk timeout adalah `ETIMEDOUT`

  * **Nilai Kembali**

    * Dengan pengaturan [protokol komunikasi](/client?id=protokol-parsing), `recv` akan mengembalikan data lengkap, panjang dibatasi oleh [package_max_length](/server/setting?id=package_max_length)
    * Tanpa pengaturan protokol komunikasi, `recv` mengembalikan maksimum data `64K`
    * Tanpa pengaturan protokol komunikasi mengembalikan data mentah, perlu implementasi penanganan protokol jaringan sendiri di kode `PHP`
    * `recv` mengembalikan string kosong berarti server menutup koneksi secara aktif, perlu `close`
    * `recv` gagal, mengembalikan `false`, periksa `$client->errCode` untuk mendapatkan penyebab error, cara penanganannya lihat [contoh lengkap](/coroutine_client/client?id=contoh-lengkap) di bawah

### close()

Menutup koneksi.

!> `close` tidak memblokir, akan segera kembali. Operasi penutupan tidak ada perpindahan coroutine.

```php
Swoole\Coroutine\Client->close(): bool
```

### peek()

Mengintip data.

!> Method `peek` beroperasi langsung pada `socket`, sehingga tidak memicu [Penjadwalan Coroutine](/coroutine?id=penjadwalan-coroutine).

```php
Swoole\Coroutine\Client->peek(int $length = 65535): string
```

  * **Petunjuk**

    * Method `peek` hanya digunakan untuk mengintip data di buffer `socket` kernel tanpa menggeser. Setelah menggunakan `peek`, memanggil `recv` masih dapat membaca data ini.
    * Method `peek` bersifat non-blocking, akan segera kembali. Jika ada data di buffer socket, akan mengembalikan konten data. Jika buffer kosong, mengembalikan `false` dan mengatur `$client->errCode`.
    * Jika koneksi ditutup, `peek` akan mengembalikan string kosong.

### set()

Mengatur parameter klien.

```php
Swoole\Coroutine\Client->set(array $settings): bool
```

  * **Parameter Konfigurasi**

    * Silakan lihat [Swoole\Client](/client?id=set).

* **Perbedaan dengan [Swoole\Client](/client?id=set)**

    Klien coroutine menyediakan kontrol timeout yang lebih terperinci. Dapat mengatur:

    * `timeout`: Total timeout, termasuk timeout koneksi, pengiriman, dan penerimaan
    * `connect_timeout`: Timeout koneksi
    * `read_timeout`: Timeout penerimaan
    * `write_timeout`: Timeout pengiriman
    * Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)

* **Contoh**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'timeout' => 0.5,
        'connect_timeout' => 1.0,
        'write_timeout' => 10.0,
        'read_timeout' => 0.5,
    ));

    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

### Contoh Lengkap

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // Jika benar-benar kosong, tutup koneksi langsung
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // Dapat menangani berdasarkan logika bisnis dan kode error, misalnya:
                    // Jika timeout jangan tutup koneksi, tutup untuk kasus lain
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```
