# Server HTTP

?> Implementasi server HTTP yang sepenuhnya coroutine. `Co\Http\Server` ditulis dalam C++ untuk alasan kinerja parsing HTTP, sehingga bukan subclass dari [Co\Server](/coroutine/server) yang ditulis dalam PHP.

Perbedaan dengan [Http\Server](/http_server):

* Bisa dibuat dan dihancurkan secara dinamis saat runtime
* Penanganan koneksi dilakukan di coroutine anak terpisah, di mana `Connect`, `Request`, `Response`, `Close` untuk koneksi klien bersifat serial penuh

!> Membutuhkan `v4.4.0` atau lebih tinggi

!> Jika [HTTP2 diaktifkan](/environment?id=opsi-kompilasi) saat kompilasi, dukungan protokol HTTP2 akan diaktifkan secara bawaan, tidak perlu mengonfigurasi [open_http2_protocol](/http_server?id=open_http2_protocol) seperti di `Swoole\Http\Server` (Catatan: **Versi di bawah v4.4.16 memiliki bug yang diketahui untuk dukungan HTTP2, harap upgrade sebelum digunakan**)

## Nama Pendek

Bisa menggunakan nama pendek `Co\Http\Server`.

## Method

### __construct()

```php
Swoole\Coroutine\Http\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Parameter** 

    * **`string $host`**
      * **Fungsi**: Alamat IP yang didengarkan [jika UNIXSocket lokal, harus diisi dengan format `unix://tmp/your_file.sock`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port yang didengarkan
      * **Bawaan**: 0 (mendengarkan port kosong secara acak)
      * **Nilai Lain**: 0~65535

    * **`bool $ssl`**
      * **Fungsi**: Apakah mengaktifkan enkripsi tunnel `SSL/TLS`
      * **Bawaan**: false
      * **Nilai Lain**: true
      
    * **`bool $reuse_port`**
      * **Fungsi**: Apakah mengaktifkan fitur reuse port, setelah diaktifkan banyak layanan bisa berbagi satu port
      * **Bawaan**: false
      * **Nilai Lain**: true

### handle()

Mendaftarkan fungsi callback untuk menangani permintaan HTTP di jalur yang ditunjukkan parameter `$pattern`.

```php
Swoole\Coroutine\Http\Server->handle(string $pattern, callable $fn): void
```

!> Harus diatur sebelum [Server::start](/coroutine/server?id=start)

  * **Parameter** 

    * **`string $pattern`**
      * **Fungsi**: Mengatur jalur `URL` [seperti `/index.html`, perhatikan tidak bisa memasukkan `http://domain`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`callable $fn`**
      * **Fungsi**: Fungsi penanganan, cara penggunaan lihat callback [OnRequest](/http_server?id=on) di `Swoole\Http\Server`, tidak diulangi di sini.
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

      Contoh:

      ```php
      function callback(Swoole\Http\Request $req, Swoole\Http\Response $resp) {
          $resp->end("hello world");
      }
      ```

  * **Petunjuk**

    * Server setelah berhasil `Accept` (membangun koneksi), akan otomatis membuat coroutine dan menerima permintaan `HTTP`
    * `$fn` dieksekusi di dalam ruang coroutine anak baru, jadi tidak perlu membuat coroutine lagi di dalam fungsi
    * Klien mendukung [KeepAlive](/coroutine_client/http_client?id=keep_alive), coroutine anak akan terus menerima permintaan baru dalam loop, tanpa keluar
    * Klien tidak mendukung `KeepAlive`, coroutine anak akan berhenti menerima permintaan, keluar dan menutup koneksi

  * **Catatan**

    !> -Saat `$pattern` diatur dengan jalur yang sama, pengaturan baru akan menimpa yang lama;  
    -Jika tidak mengatur fungsi penanganan jalur root dan jalur yang diminta tidak cocok dengan `$pattern` mana pun, Swoole akan mengembalikan error `404`;  
    -`$pattern` menggunakan metode pencocokan string, tidak mendukung wildcard atau regex, tidak membedakan huruf besar/kecil, algoritma pencocokan adalah prefix match. Contoh: url `/test111` akan cocok dengan aturan `/test`, setelah cocok akan langsung keluar tanpa memeriksa konfigurasi berikutnya;  
    -Disarankan mengatur fungsi penanganan jalur root, dan menggunakan `$request->server['request_uri']` di fungsi callback untuk routing permintaan.

### start()

?> **Menjalankan server.**

```php
Swoole\Coroutine\Http\Server->start();
```

### shutdown()

?> **Menghentikan server.**

```php
Swoole\Coroutine\Http\Server->shutdown();
```

## Contoh Lengkap

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
```
