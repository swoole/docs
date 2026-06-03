# Server TCP

?> `Swoole\Coroutine\Server` adalah kelas yang sepenuhnya [coroutine](/coroutine), digunakan untuk membuat server `TCP` coroutine, mendukung tipe TCP dan [unixSocket](/learn?id=apa-itu-IPC).

Perbedaan dengan modul [Server](/server/tcp_init):

* Pembuatan dan penghancuran dinamis, bisa mendengarkan port secara dinamis saat runtime, juga bisa menutup server secara dinamis
* Proses penanganan koneksi sepenuhnya sinkron, program bisa memproses event `Connect`, `Receive`, `Close` secara berurutan

!> Tersedia di versi 4.4 ke atas.

## Nama Pendek

Bisa menggunakan nama pendek `Co\Server`.

## Method

### __construct()

?> **Method konstruktor.**

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Parameter** 

    * **`string $host`**
      * **Fungsi**: Alamat yang didengarkan
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port yang didengarkan [jika 0, akan ditentukan secara acak oleh sistem operasi]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`bool $ssl`**
      * **Fungsi**: Apakah mengaktifkan enkripsi SSL
      * **Bawaan**: `false`
      * **Nilai Lain**: `true`

    * **`bool $reuse_port`**
      * **Fungsi**: Apakah mengaktifkan reuse port, efeknya sama dengan konfigurasi di [bagian ini](/server/setting?id=enable_reuse_port)
      * **Bawaan**: `false`
      * **Nilai Lain**: `true`
      * **Versi**: Swoole versi >= v4.4.4

  * **Petunjuk**

    * **Parameter $host mendukung 3 format**

      * `0.0.0.0/127.0.0.1`: Alamat IPv4
      * `::/::1`: Alamat IPv6
      * `unix:/tmp/test.sock`: Alamat [UnixSocket](/learn?id=apa-itu-IPC)

    * **Exception**

      * Error parameter, gagal bind alamat dan port, gagal `listen` akan melempar exception `Swoole\Exception`.

### set()

?> **Mengatur parameter pemrosesan protokol.**

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **Parameter Konfigurasi**

    * Parameter `$options` harus berupa array asosiatif satu dimensi, sama persis dengan item konfigurasi yang diterima method [setprotocol](/coroutine_client/socket?id=setprotocol).

    !> Harus diatur sebelum method [start()](/coroutine/server?id=start).

    * **Protokol Panjang**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      'package_length_offset' => 0,
      'package_body_offset' => 4,
    ]);
    ```

    * **Pengaturan Sertifikat SSL**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      'ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```

### handle()

?> **Mengatur fungsi penanganan koneksi.**

!> Harus diatur sebelum [start()](/coroutine/server?id=start).

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **Parameter** 

    * **`callable $fn`**
      * **Fungsi**: Mengatur fungsi penanganan koneksi
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada
      
  * **Contoh** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            $data = $conn->recv();
        }
    });
    ```

    !> -Setelah server berhasil `Accept` (membangun koneksi), akan otomatis membuat [coroutine](/coroutine?id=penjadwalan-coroutine) dan menjalankan `$fn`;  
    -`$fn` dieksekusi di dalam ruang coroutine anak baru, jadi tidak perlu membuat coroutine lagi di dalam fungsi;  
    -`$fn` menerima satu parameter, tipenya objek [Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection);  
    -Bisa menggunakan [exportSocket()](/coroutine/server?id=exportsocket) untuk mendapatkan objek Socket koneksi saat ini.

### shutdown()

?> **Menghentikan server.**

?> Infrastruktur mendukung pemanggilan `start` dan `shutdown` berkali-kali.

```php
Swoole\Coroutine\Server->shutdown(): bool
```

### start()

?> **Menjalankan server.**

```php
Swoole\Coroutine\Server->start(): bool
```

  * **Return Value**

    * Gagal dijalankan akan mengembalikan `false`, dan mengatur properti `errCode`
    * Berhasil dijalankan akan masuk ke loop, `Accept` koneksi
    * Setelah `Accept` (membangun koneksi) akan membuat coroutine baru, dan memanggil fungsi yang ditentukan method handle di dalam coroutine tersebut

  * **Penanganan Error**

    * Saat `Accept` (membangun koneksi) terjadi error `Too many open file`, atau gagal membuat coroutine anak, akan jeda `1` detik lalu melanjutkan `Accept`
    * Saat terjadi error, method `start()` akan kembali, informasi error akan dilaporkan sebagai `Warning`.

## Objek

### Coroutine\Server\Connection

Objek `Swoole\Coroutine\Server\Connection` menyediakan empat method:
 
#### recv()

Menerima data, jika protokol diatur, akan mengembalikan paket lengkap setiap kali.

```php
function recv(float $timeout = 0)
```

#### send()

Mengirim data.

```php
function send(string $data)
```

#### close()

Menutup koneksi.

```php
function close(): bool
```

#### exportSocket()

Mendapatkan objek Socket dari koneksi saat ini. Bisa memanggil method infrastruktur lainnya, lihat [Swoole\Coroutine\Socket](/coroutine_client/socket).

```php
function exportSocket(): Swoole\Coroutine\Socket
```

## Contoh Lengkap

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

// Modul manajemen multi-proses
$pool = new Process\Pool(2);
// Buat coroutine otomatis di setiap callback OnWorkerStart
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    // Setiap proses mendengarkan port 9501
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    // Terima sinyal 15 untuk menutup layanan
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    // Terima permintaan koneksi baru dan buat coroutine otomatis
    $server->handle(function (Connection $conn) {
        while (true) {
            // Terima data
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            // Kirim data
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    // Mulai mendengarkan port
    $server->start();
});
$pool->start();
```

!> Jika berjalan di lingkungan Cygwin, ubah ke proses tunggal. `$pool = new Swoole\Process\Pool(1);`
