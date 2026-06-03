# Klien HTTP/WebSocket Coroutine

Klien `HTTP` versi coroutine ditulis murni dalam `C` tanpa bergantung pada pustaka ekstensi pihak ketiga, dengan kinerja sangat tinggi.

* Mendukung fitur `Http-Chunk`, `Keep-Alive`, dan format `form-data`
* Versi protokol `HTTP` adalah `HTTP/1.1`
* Mendukung upgrade ke klien `WebSocket`
* Dukungan format kompresi `gzip` memerlukan pustaka `zlib`
* Klien hanya mengimplementasikan fitur inti, disarankan menggunakan [Saber](https://github.com/swlib/saber) untuk proyek aktual

## Properti

### errCode

Kode status error. Saat `connect/send/recv/close` gagal atau timeout, nilai `Swoole\Coroutine\Http\Client->errCode` akan diatur secara otomatis.

```php
Swoole\Coroutine\Http\Client->errCode: int
```

Nilai `errCode` sama dengan `Linux errno`. Anda dapat menggunakan `socket_strerror` untuk mengonversi kode error menjadi pesan error.

```php
// Jika connect ditolak, kode error adalah 111
// Jika timeout, kode error adalah 110
echo socket_strerror($client->errCode);
```

!> Referensi: [Daftar Kode Error Linux](/other/errno?id=linux)

### body

Menyimpan body respons dari permintaan terakhir.

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```

### statusCode

Kode status HTTP, seperti 200, 404, dll. Jika kode status negatif, itu menunjukkan ada masalah koneksi. [Pelajari lebih lanjut](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```

## Method

### __construct()

Method konstruktor.

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

  * **Parameter**

    * **`string $host`**
      * **Fungsi**: Alamat host server target【bisa IP atau domain, sistem secara otomatis melakukan resolusi domain. Jika berupa UNIX Socket lokal, harus diisi dengan format seperti `unix://tmp/your_file.sock`. Jika domain, tidak perlu mengisi header protokol `http://` atau `https://`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port host server target
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`bool $ssl`**
      * **Fungsi**: Apakah akan mengaktifkan enkripsi tunnel `SSL/TLS`. Jika server target menggunakan HTTPS, parameter `$ssl` harus diatur ke `true`
      * **Default**: `false`
      * **Nilai lain**: Tidak ada

  * **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

### set()

Mengatur parameter klien.

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

Method ini sepenuhnya konsisten dengan parameter yang diterima oleh `Swoole\Client->set`, silakan lihat dokumentasi method [Swoole\Client->set](/client?id=set).

`Swoole\Coroutine\Http\Client` menambahkan beberapa opsi tambahan untuk mengontrol klien `HTTP` dan `WebSocket`.

#### Opsi Tambahan

##### Kontrol Timeout

Atur opsi `timeout` untuk mengaktifkan deteksi timeout permintaan HTTP. Satuan dalam detik, dengan dukungan milidetik sebagai granularitas terkecil.

```php
$http->set(['timeout' => 3.0]);
```

* Jika koneksi timeout atau server menutup koneksi, `statusCode` akan diatur ke `-1`
* Jika server gagal merespons dalam waktu yang ditentukan, permintaan timeout, dan `statusCode` akan diatur ke `-2`
* Setelah permintaan timeout, koneksi di tingkat bawah akan otomatis diputus
* Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)

##### keep_alive

Atur opsi `keep_alive` untuk mengaktifkan atau menonaktifkan koneksi persisten HTTP.

```php
$http->set(['keep_alive' => false]);
```

##### websocket_mask

> Karena peraturan RFC, konfigurasi ini diaktifkan secara default mulai v4.4.0, tetapi dapat menyebabkan penurunan kinerja. Jika server tidak memiliki persyaratan ketat, dapat diatur ke false untuk menonaktifkannya.

Mengaktifkan atau menonaktifkan masking untuk klien `WebSocket`. Diaktifkan secara default. Saat diaktifkan, masking diterapkan pada data yang dikirim oleh klien WebSocket untuk transformasi data.

```php
$http->set(['websocket_mask' => false]);
```

##### websocket_compression

> Memerlukan `v4.4.12` atau versi yang lebih tinggi

Saat diatur ke `true`, **memungkinkan** frame dikompresi menggunakan zlib. Apakah kompresi benar-benar dapat dilakukan tergantung pada apakah server dapat menangani kompresi (ditentukan oleh informasi handshake, lihat `RFC-7692`).

Untuk benar-benar mengompresi frame tertentu, perlu menggunakan parameter flags `SWOOLE_WEBSOCKET_FLAG_COMPRESS`. Untuk penggunaan spesifik, [lihat bagian ini](/websocket_server?id=kompresi-frame-websocket-(rfc-7692))

```php
$http->set(['websocket_compression' => true]);
```

##### write_func
> Memerlukan `v5.1.0` atau versi yang lebih tinggi

Atur fungsi callback `write_func`, mirip dengan opsi `WRITE_FUNCTION` di `CURL`, dapat digunakan untuk menangani konten respons streaming, misalnya output `Event Stream` dari `OpenAI ChatGPT`.

> Setelah mengatur `write_func`, Anda tidak akan bisa menggunakan method `getContent()` untuk mendapatkan konten respons, dan `$client->body` juga akan kosong  
> Di fungsi callback `write_func`, Anda dapat menggunakan `$client->close()` untuk berhenti menerima konten respons dan menutup koneksi

```php
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->set(['write_func' => function ($client, $data) {
    var_dump($data);
}]);
$cli->get('/');
```

### setMethod()

Mengatur method permintaan. Hanya berlaku untuk permintaan saat ini, pengaturan method akan segera dibersihkan setelah mengirim permintaan.

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **Parameter**

    * **`string $method`**
      * **Fungsi**: Mengatur method
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

      !> Harus berupa nama method yang sesuai dengan standar `HTTP`. Jika `$method` diatur salah, permintaan mungkin ditolak oleh server `HTTP`.

  * **Contoh**

```php
$http->setMethod("PUT");
```

### setHeaders()

Mengatur header permintaan HTTP.

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **Parameter**

    * **`array $headers`**
      * **Fungsi**: Mengatur header permintaan【Harus berupa array asosiatif, sistem akan secara otomatis memetakannya ke format header standar `HTTP` dengan format `$key`: `$value`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

!> Header `HTTP` yang diatur oleh `setHeaders` berlaku permanen untuk setiap permintaan selama masa hidup objek `Coroutine\Http\Client`. Memanggil ulang `setHeaders` akan menimpa pengaturan sebelumnya.

### setCookies()

Mengatur `Cookie`, nilai akan di-encode dengan `urlencode`. Jika ingin mempertahankan informasi asli, gunakan `setHeaders` untuk mengatur `header` bernama `Cookie` sendiri.

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **Parameter**

    * **`array $cookies`**
      * **Fungsi**: Mengatur `COOKIE`【Harus berupa array asosiatif】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

!> - `COOKIE` yang diatur akan disimpan terus selama masa hidup objek klien  
- `COOKIE` yang diatur oleh server akan digabungkan ke dalam array `cookies`, dan Anda dapat membaca informasi `COOKIE` klien `HTTP` saat ini dengan mengakses properti `$client->cookies`  
- Memanggil ulang method `setCookies` akan menimpa status `Cookies` saat ini, yang akan membuang `COOKIE` yang sebelumnya ditetapkan server serta `COOKIE` yang sebelumnya diatur secara manual

### setData()

Mengatur body permintaan HTTP.

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **Parameter**

    * **`string|array $data`**
      * **Fungsi**: Mengatur body permintaan
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Petunjuk**

    * Jika `$data` diatur dan `$method` tidak diatur, sistem akan secara otomatis mengaturnya sebagai POST
    * Jika `$data` adalah array dan `Content-Type` adalah `urlencoded`, sistem akan secara otomatis menjalankan `http_build_query`
    * Jika Anda telah menggunakan `addFile` atau `addData` yang mengaktifkan format `form-data`, saat `$data` berupa string akan diabaikan (karena formatnya berbeda), tetapi saat berupa array, sistem akan menambahkan field dari array dalam format `form-data`

### addFile()

Menambahkan file POST.

!> Saat menggunakan `addFile`, `Content-Type` dari `POST` akan otomatis berubah menjadi `form-data`. `addFile` didasarkan pada `sendfile` di tingkat bawah dan dapat mendukung pengiriman file yang sangat besar secara asinkron.

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **Parameter**

    * **`string $path`**
      * **Fungsi**: Path file【Parameter wajib, tidak boleh file kosong atau file yang tidak ada】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $name`**
      * **Fungsi**: Nama form【Parameter wajib, `key` dalam parameter `FILES`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $mimeType`**
      * **Fungsi**: Format `MIME` file【Parameter opsional, sistem akan secara otomatis menentukannya berdasarkan ekstensi file】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $filename`**
      * **Fungsi**: Nama file【Parameter opsional】
      * **Default**: `basename($path)`
      * **Nilai lain**: Tidak ada

    * **`int $offset`**
      * **Fungsi**: Offset pengunggahan file【Parameter opsional, dapat menentukan untuk mulai mentransfer data dari bagian tengah file. Fitur ini dapat digunakan untuk mendukung resume transmisi.】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $length`**
      * **Fungsi**: Ukuran data yang akan dikirim【Parameter opsional】
      * **Default**: Default ke ukuran seluruh file
      * **Nilai lain**: Tidak ada

  * **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```

### addData()

Membangun konten file yang diunggah menggunakan string.

!> `addData` tersedia di versi `v4.1.0` ke atas

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **Parameter**

    * **`string $data`**
      * **Fungsi**: Konten data【Parameter wajib, panjang maksimum tidak boleh melebihi [buffer_output_size](/server/setting?id=buffer_output_size)】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $name`**
      * **Fungsi**: Nama form【Parameter wajib, `key` dalam parameter `$_FILES`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $mimeType`**
      * **Fungsi**: Format `MIME` file【Parameter opsional, default `application/octet-stream`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $filename`**
      * **Fungsi**: Nama file【Parameter opsional, default `$name`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```

### get()

Melakukan permintaan GET.

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **Parameter**

    * **`string $path`**
      * **Fungsi**: Mengatur path `URL`【mis. `/index.html`, perhatikan bahwa tidak bisa menggunakan `http://domain` di sini】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

!> Menggunakan `get` akan mengabaikan method permintaan yang diatur oleh `setMethod` dan memaksa menggunakan `GET`.

### post()

Melakukan permintaan POST.

```php
Swoole\Coroutine\Http\Client->post(string $path, mixed $data): void
```

  * **Parameter**

    * **`string $path`**
      * **Fungsi**: Mengatur path `URL`【mis. `/index.html`, perhatikan bahwa tidak bisa menggunakan `http://domain` di sini】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`mixed $data`**
      * **Fungsi**: Data body permintaan
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

      !> Jika `$data` adalah array, sistem akan secara otomatis mengemasnya sebagai konten `POST` format `x-www-form-urlencoded` dan mengatur `Content-Type` menjadi `application/x-www-form-urlencoded`

  * **Catatan**

    !> Menggunakan `post` akan mengabaikan method permintaan yang diatur oleh `setMethod` dan memaksa menggunakan `POST`

  * **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```

### upgrade()

Meningkatkan ke koneksi `WebSocket`.

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **Parameter**

    * **`string $path`**
      * **Fungsi**: Mengatur path `URL`【mis. `/`, perhatikan bahwa tidak bisa menggunakan `http://domain` di sini】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Petunjuk**

    * Dalam beberapa kasus, meskipun permintaan berhasil dan `upgrade` mengembalikan `true`, server tidak mengatur kode status HTTP menjadi `101`, melainkan `200` atau `403`, yang menunjukkan server menolak permintaan handshake.
    * Setelah handshake WebSocket berhasil, Anda dapat menggunakan method `push` untuk mengirim pesan ke server, dan juga dapat memanggil `recv` untuk menerima pesan.
    * `upgrade` akan memicu [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine).

  * **Contoh**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```

### push()

Mengirim pesan ke server `WebSocket`.

!> Method `push` hanya dapat dijalankan setelah `upgrade` berhasil.  
Method `push` tidak memicu penjadwalan coroutine, akan segera kembali setelah menulis ke buffer pengiriman.

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **Parameter**

    * **`mixed $data`**
      * **Fungsi**: Konten data yang akan dikirim【Default dalam format teks `UTF-8`. Untuk format encoding lain atau data biner, gunakan `WEBSOCKET_OPCODE_BINARY`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

      !> Swoole versi >= v4.2.0, `$data` dapat menggunakan objek [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), mendukung pengiriman berbagai tipe frame.

    * **`int $opcode`**
      * **Fungsi**: Tipe operasi
      * **Default**: `WEBSOCKET_OPCODE_TEXT`
      * **Nilai lain**: Tidak ada

      !> `$opcode` harus berupa `WebSocket OPCode` yang valid, jika tidak akan gagal dan mencetak pesan error `opcode max 10`

    * **`int|bool $finish`**
      * **Fungsi**: Tipe operasi
      * **Default**: `SWOOLE_WEBSOCKET_FLAG_FIN`
      * **Nilai lain**: Tidak ada

      !> Sejak `v4.4.12`, parameter `finish` (tipe `bool`) diubah menjadi `flags` (tipe `int`) untuk mendukung kompresi `WebSocket`. `finish` yang sesuai dengan `SWOOLE_WEBSOCKET_FLAG_FIN` bernilai `1`. Nilai `bool` asli akan secara implisit dikonversi ke `int`, perubahan ini kompatibel ke belakang tanpa dampak. Selain itu, `flag` kompresi adalah `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

  * **Return Value**

    * Jika berhasil dikirim, mengembalikan `true`
    * Jika koneksi tidak ada, ditutup, atau `WebSocket` belum selesai, pengiriman gagal dan mengembalikan `false`

  * **Kode Error**

Kode Error | Keterangan
---|---
8502 | OPCode salah
8503 | Tidak terhubung ke server atau koneksi telah ditutup
8504 | Handshake gagal

### recv()

Menerima pesan. Hanya digunakan untuk `WebSocket` dan perlu digunakan bersama `upgrade()`. Lihat contoh.

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **Parameter**

    * **`float $timeout`**
      * **Fungsi**: Parameter ini hanya berlaku saat memanggil `upgrade()` untuk meningkatkan ke koneksi `WebSocket`
      * **Satuan**: Detik【mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

      !> Atur timeout. Parameter yang ditentukan diprioritaskan terlebih dahulu, diikuti oleh konfigurasi `timeout` yang diberikan di method `set`.

  * **Return Value**

    * Mengembalikan objek frame
    * Mengembalikan `false`. Periksa properti `errCode` dari `Swoole\Coroutine\Http\Client`. Klien coroutine tidak memiliki callback `onClose`. Saat koneksi ditutup, `recv` mengembalikan `false` dengan `errCode=0`.

  * **Contoh**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```

### ping()
Mengirim paket heartbeat `WebSocket`.

```php
Swoole\Coroutine\Http\Client->ping(string $data = ''): bool
```
  * **Parameter**

    * **`string $data`**
      * **Fungsi**: Konten data paket heartbeat
      * **Default**: String kosong
      * **Nilai lain**: Tidak ada

  * **Return Value**

    * Berhasil mengirim mengembalikan `true`
    * Koneksi tidak ada, ditutup, atau `WebSocket` belum selesai, pengiriman gagal mengembalikan `false`

### disconnect()
Menutup koneksi `WebSocket`.

```php
Swoole\Coroutine\Http\Client->disconnect(int $code = 1000, string $reason = ''): bool
```
  * **Parameter**

    * **`int $code`**
      * **Fungsi**: Kode penutupan
      * **Default**: `1000`
      * **Nilai lain**: Tidak ada

    * **`string $reason`**
      * **Fungsi**: Alasan penutupan
      * **Default**: String kosong
      * **Nilai lain**: Tidak ada

  * **Return Value**

    * Berhasil menutup mengembalikan `true`
    * Koneksi tidak ada, ditutup, atau `WebSocket` belum selesai, gagal menutup mengembalikan `false`

### download()

Mengunduh file melalui HTTP.

!> Perbedaan antara `download` dan `get` adalah `download` akan menulis data yang diterima ke disk, bukan menggabungkan HTTP Body di memori. Oleh karena itu, `download` hanya menggunakan sedikit memori untuk menyelesaikan pengunduhan file yang sangat besar.

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename, int $offset = 0): bool
```

  * **Parameter**

    * **`string $path`**
      * **Fungsi**: Mengatur path URL
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $filename`**
      * **Fungsi**: Menentukan path file untuk menulis konten yang diunduh【akan secara otomatis ditulis ke properti `downloadFile`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $offset`**
      * **Fungsi**: Menentukan offset untuk menulis ke file【opsi ini dapat digunakan untuk mendukung unduhan resumable, dapat dikombinasikan dengan header HTTP `Range: bytes=$offset`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

      !> Saat `$offset` adalah `0`, jika file sudah ada, sistem akan secara otomatis mengosongkan file ini.

  * **Return Value**

    * Mengembalikan `true`
    * Gagal membuka file atau `fseek()` di tingkat bawah gagal mengembalikan `false`

  * **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```

### getCookies()

Mendapatkan konten `cookie` dari respons `HTTP`.

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> Informasi Cookie akan di-decode dengan urldecode. Untuk mendapatkan informasi Cookie asli, silakan parsing sendiri sesuai petunjuk di bawah.

#### Mendapatkan `Cookie` duplikat atau header `Cookie` asli

```php
var_dump($client->set_cookie_headers);
```

### getHeaders()

Mengembalikan informasi header dari respons `HTTP`.

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```

### getStatusCode()

Mendapatkan kode status dari respons HTTP.

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **Petunjuk**

    * **Jika kode status negatif, itu menunjukkan ada masalah koneksi.**

Kode Status | Konstanta versi v4.2.10 ke atas | Keterangan
---|---|---
-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | Timeout koneksi, server tidak mendengarkan port atau jaringan hilang, dapat membaca $errCode untuk mendapatkan kode error jaringan spesifik
-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | Timeout permintaan, server tidak mengembalikan respons dalam waktu timeout yang ditentukan
-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | Setelah permintaan klien dikirim, server memutus koneksi secara paksa
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | Klien gagal mengirim (konstanta ini tersedia di Swoole versi >= `v4.5.9`, untuk versi di bawahnya gunakan kode status)

### getBody()

Mendapatkan konten body dari respons HTTP.

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```

### close()

Menutup koneksi.

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> Setelah `close`, jika Anda memanggil method seperti `get`, `post` lagi, Swoole akan membantu Anda menyambung ulang ke server.

### execute()

Method permintaan `HTTP` yang lebih low-level, perlu memanggil antarmuka seperti [setMethod](/coroutine_client/http_client?id=setmethod) dan [setData](/coroutine_client/http_client?id=setdata) di kode untuk mengatur method dan data permintaan.

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **Contoh**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```

## Fungsi

Untuk memudahkan penggunaan `Coroutine\Http\Client`, ditambahkan tiga fungsi:

!> Swoole versi >= `v4.6.4` tersedia

### request()

Melakukan permintaan dengan method permintaan yang ditentukan.

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```

### post()

Digunakan untuk melakukan permintaan `POST`.

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```

### get()

Digunakan untuk melakukan permintaan `GET`.

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```

### Contoh Penggunaan

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
