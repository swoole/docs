# Http\Server

?> `Http\Server` mewarisi dari [Server](/server/init), jadi semua `API` dan opsi konfigurasi yang disediakan `Server` dapat digunakan, model prosesnya juga sama. Silakan lihat bab [Server](/server/init).

Dukungan server `HTTP` bawaan, hanya dengan beberapa baris kode dapat menulis server `HTTP` multi-proses konkuren tinggi, performa tinggi, [IO asinkron](/learn?id=同步io异步io).

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

Dengan menggunakan alat `Apache bench` untuk pengujian tekanan, di PC biasa `Inter Core-I5 4-core + 8G memory`, `Http\Server` dapat mencapai hampir `110 ribu QPS`.

Jauh melampaui server `Http` bawaan `PHP-FPM`, `Golang`, `Node.js`. Performa hampir setara dengan penanganan file statis `Nginx`.

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **Menggunakan Protokol HTTP2**

  * Menggunakan protokol `HTTP2` di bawah `SSL` harus menginstal `openssl`, dan versi `openssl` yang tinggi harus mendukung `TLS1.2`, `ALPN`, `NPN`
  * Saat kompilasi perlu mengaktifkan [--enable-http2](/environment?id=编译选项)
  * Mulai Swoole5, protokol http2 diaktifkan secara default

```shell
./configure --enable-openssl --enable-http2
```

Atur [open_http2_protocol](/http_server?id=open_http2_protocol) server `HTTP` menjadi `true`

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Konfigurasi Nginx + Swoole**

!> Karena dukungan `Http\Server` terhadap protokol `HTTP` tidak lengkap, disarankan hanya sebagai server aplikasi untuk menangani request dinamis, dan menambahkan `Nginx` sebagai proxy di depan.

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> Dapat membaca `$request->header['x-real-ip']` untuk mendapatkan `IP` asli klien

## Method

### on()

?> **Mendaftarkan fungsi callback event.**

?> Sama dengan [Callback Server](/server/events), perbedaannya adalah:

* `Http\Server->on` tidak menerima pengaturan callback [onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)
* `Http\Server->on` tambahan menerima 1 jenis event baru `onRequest`, request dari klien dijalankan di event `Request`

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

Setelah menerima request HTTP lengkap, fungsi ini akan dipanggil. Fungsi callback memiliki `2` parameter:

* [Swoole\Http\Request](/http_server?id=httpRequest), objek informasi request `HTTP`, berisi informasi terkait `header/get/post/cookie`
* [Swoole\Http\Response](/http_server?id=httpResponse), objek respons `HTTP`, mendukung operasi `HTTP` seperti `cookie/header/status`

!> Saat fungsi callback [onRequest](/http_server?id=on) kembali, level bawah akan menghancurkan objek `$request` dan `$response`

### start()

?> **Memulai server HTTP**

?> Setelah mulai, mulai mendengarkan port dan menerima request `HTTP` baru.

```php
Swoole\Http\Server->start();
```

## Swoole\Http\Request

Objek request `HTTP`, menyimpan informasi terkait request klien `HTTP`, termasuk `GET`, `POST`, `COOKIE`, `Header` dll.

!> Jangan menggunakan simbol `&` untuk mereferensi objek `Http\Request`

### header

?> **Informasi header request `HTTP`. Tipe array, semua `key` huruf kecil.**

```php
Swoole\Http\Request->header: array
```

* **Contoh**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```

### server

?> **Informasi server terkait request `HTTP`.**

?> Setara dengan array `$_SERVER` di `PHP`. Berisi method request `HTTP`, path `URL`, IP klien dll.

```php
Swoole\Http\Request->server: array
```

Semua `key` array adalah huruf kecil, dan konsisten dengan array `$_SERVER` di `PHP`

* **Contoh**

```php
echo $request->server['request_time'];
```

key | Penjelasan
---|---
query_string | Parameter `GET` request, misal: `id=1&cid=2` Jika tidak ada parameter `GET`, item ini tidak ada
request_method | Method request, `GET/POST` dll
request_uri | Alamat akses tanpa parameter `GET`, misal `/favicon.ico`
path_info | Sama dengan `request_uri`
request_time | `request_time` diatur di `Worker`, di mode [SWOOLE_PROCESS](/learn?id=swoole_process) ada proses `dispatch`, sehingga mungkin ada selisih dengan waktu penerimaan paket sebenarnya. Terutama saat jumlah request melebihi kapasitas pemrosesan server, `request_time` mungkin jauh tertinggal dari waktu penerimaan paket sebenarnya. Dapat menggunakan method `$server->getClientInfo` untuk mendapatkan `last_time` guna waktu penerimaan paket yang akurat.
request_time_float | Timestamp mulai request, dalam mikrodetik, tipe `float`, misal `1576220199.2725`
server_protocol | Nomor versi protokol server, `HTTP`: `HTTP/1.0` atau `HTTP/1.1`, `HTTP2`: `HTTP/2`
server_port | Port yang didengarkan server
remote_port | Port klien
remote_addr | Alamat `IP` klien
master_time | Waktu komunikasi terakhir koneksi

### get

?> **Parameter `GET` request `HTTP`, setara dengan `$_GET` di `PHP`, format array.**

```php
Swoole\Http\Request->get: array
```

* **Contoh**

```php
// Misal: index.php?hello=123
echo $request->get['hello'];
// Mendapatkan semua parameter GET
var_dump($request->get);
```

* **Catatan**

!> Untuk mencegah serangan `HASH`, parameter `GET` maksimal tidak boleh melebihi `128`

### post

?> **Parameter `POST` request `HTTP`, format array**

```php
Swoole\Http\Request->post: array
```

* **Contoh**

```php
echo $request->post['hello'];
```

* **Catatan**

!> - Ukuran gabungan `POST` dan `Header` tidak boleh melebihi pengaturan [package_max_length](/server/setting?id=package_max_length), jika tidak akan dianggap request berbahaya  
- Jumlah maksimum parameter `POST` tidak boleh melebihi `128`

### cookie

?> **Informasi `COOKIE` yang dibawa request `HTTP`, format array pasangan kunci-nilai.**

```php
Swoole\Http\Request->cookie: array
```

* **Contoh**

```php
echo $request->cookie['username'];
```

### files

?> **Informasi upload file.**

?> Tipe array dua dimensi dengan nama `form` sebagai `key`. Sama dengan `$_FILES` di `PHP`. Ukuran file maksimum tidak boleh melebihi nilai yang diatur [package_max_length](/server/setting?id=package_max_length). Karena Swoole menggunakan memori saat mengurai pesan, semakin besar pesan, semakin besar penggunaan memori, jadi jangan gunakan `Swoole\Http\Server` untuk menangani upload file besar atau desain fitur resumable upload oleh pengguna sendiri.

```php
Swoole\Http\Request->files: array
```

* **Contoh**

```php
Array
(
    [name] => facepalm.jpg // Nama file yang dimasukkan saat upload browser
    [type] => image/jpeg // Tipe MIME
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // File sementara upload, nama file diawali /tmp/swoole.upfile
    [error] => 0
    [size] => 15476 // Ukuran file
)
```

* **Catatan**

!> Saat objek `Swoole\Http\Request` dihancurkan, file sementara upload akan otomatis dihapus

### getContent()

!> Versi Swoole >= `v4.5.0` tersedia, di versi rendah dapat menggunakan alias `rawContent` (alias ini akan dipertahankan secara permanen, kompatibel ke bawah)

?> **Mendapatkan body `POST` mentah.**

?> Digunakan untuk request HTTP `POST` non-format `application/x-www-form-urlencoded`. Mengembalikan data `POST` mentah, fungsi ini setara dengan `fopen('php://input')` di `PHP`

```php
Swoole\Http\Request->getContent(): string|false
```

* **Nilai Kembali**

    * Berhasil mengembalikan pesan, jika konteks koneksi tidak ada mengembalikan `false`

!> Beberapa situasi server tidak perlu mengurai parameter request HTTP `POST`, melalui konfigurasi [http_parse_post](/http_server?id=http_parse_post), dapat menonaktifkan penguraian data `POST`.

### getData()

?> **Mendapatkan pesan request `Http` asli lengkap, perhatikan tidak bisa digunakan di `Http2`. Termasuk `Http Header` dan `Http Body`**

```php
Swoole\Http\Request->getData(): string|false
```

* **Nilai Kembali**

    * Berhasil mengembalikan pesan, jika konteks koneksi tidak ada atau dalam mode `Http2` mengembalikan `false`

### create()

?> **Membuat objek `Swoole\Http\Request`.**

!> Versi Swoole >= `v4.6.0` tersedia

```php
Swoole\Http\Request->create(array $options): Swoole\Http\Request
```

* **Parameter**

    * **`array $options`**
      * **Fungsi**: Parameter opsional, digunakan untuk mengatur konfigurasi objek `Request`

| Parameter                                            | Nilai Default | Penjelasan                                                          |
| ---------------------------------------------------- | ------------- | ------------------------------------------------------------------ |
| [parse_cookie](/http_server?id=http_parse_cookie)    | true          | Mengatur apakah mengurai `Cookie`                                    |
| [parse_body](/http_server?id=http_parse_post)        | true          | Mengatur apakah mengurai `Http Body`                                 |
| [parse_files](/http_server?id=http_parse_files)      | true          | Mengatur sakelar penguraian upload file                              |
| enable_compression                                   | true, jika server tidak mendukung pesan terkompresi, default false | Mengatur apakah mengaktifkan kompresi |
| compression_level                                    | 1             | Mengatur level kompresi, rentang 1-9, semakin tinggi level semakin kecil ukuran setelah kompresi, tetapi konsumsi CPU lebih banyak |
| upload_tmp_dir                                       | /tmp          | Lokasi penyimpanan file sementara, untuk upload file |

* **Nilai Kembali**

    * Mengembalikan objek `Swoole\Http\Request`

* **Contoh**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```

### parse()

?> **Mengurai paket data request `HTTP`, mengembalikan panjang paket data yang berhasil diurai.**

!> Versi Swoole >= `v4.6.0` tersedia

```php
Swoole\Http\Request->parse(string $data): int|false
```

* **Parameter**

    * **`string $data`**
      * Pesan yang akan diurai

* **Nilai Kembali**

    * Berhasil mengurai mengembalikan panjang pesan yang diurai, konteks koneksi tidak ada atau konteks sudah berakhir mengembalikan `false`

### isCompleted()

?> **Mendapatkan apakah paket data request `HTTP` saat ini sudah mencapai akhir.**

!> Versi Swoole >= `v4.6.0` tersedia

```php
Swoole\Http\Request->isCompleted(): bool
```

* **Nilai Kembali**

    * `true` berarti sudah akhir, `false` berarti konteks koneksi sudah berakhir atau belum sampai akhir

* **Contoh**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n";
$data .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n";
$data .= "Accept-Encoding: gzip, deflate, br\r\n";
$data .= "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,ja;q=0.6\r\n";
$data .= "Cookie: env=pretest; phpsessid=fcccs2af8673a2f343a61a96551c8523d79ea; username=hantianfeng\r\n";

/** @var Request $req */
$req = Request::create(['parse_cookie' => false]);
var_dump($req);

var_dump($req->isCompleted());
var_dump($req->parse($data));

var_dump($req->parse("\r\n"));
var_dump($req->isCompleted());

var_dump($req);
// Penguraian cookie dimatikan, jadi akan null
var_dump($req->cookie);
```

### getMethod()

?> **Mendapatkan method request `HTTP` saat ini.**

!> Versi Swoole >= `v4.6.2` tersedia

```php
Swoole\Http\Request->getMethod(): string|false
```
* **Nilai Kembali**

    * Berhasil mengembalikan method request huruf besar, `false` berarti konteks koneksi tidak ada

```php
var_dump($request->server['request_method']);
var_dump($request->getMethod());
```

## Swoole\Http\Response

Objek respons `HTTP`, dengan memanggil method objek ini, mengirim respons `HTTP`.

?> Saat objek `Response` dihancurkan, jika belum memanggil [end](/http_server?id=end) untuk mengirim respons `HTTP`, level bawah akan otomatis menjalankan `end("")`;

!> Jangan menggunakan simbol `&` untuk mereferensi objek `Http\Response`

### header() :id=setheader

?> **Mengatur informasi Header respons HTTP** [alias `setHeader`]

```php
Swoole\Http\Response->header(string $key, string $value, bool $format = true): bool;
```

* **Parameter** 

  * **`string $key`**
    * **Fungsi**: `Key` dari `HTTP` header
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`string $value`**
    * **Fungsi**: `Value` dari `HTTP` header
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`bool $format`**
    * **Fungsi**: Apakah perlu memformat `Key` sesuai konvensi `HTTP` [default `true` akan otomatis diformat]
    * **Nilai Default**: `true`
    * **Nilai Lain**: Tidak ada

* **Nilai Kembali** 

  * Gagal mengatur, mengembalikan `false`
  * Berhasil mengatur, mengembalikan `true`

* **Catatan**

   - Pengaturan `header` harus dilakukan sebelum method `end`
   - `$key` harus sepenuhnya sesuai dengan konvensi `HTTP`, setiap kata diawali huruf besar, tidak boleh mengandung karakter China, garis bawah atau karakter khusus lainnya
   - `$value` harus diisi
   - Jika `$ucwords` diatur `true`, level bawah akan otomatis memformat `$key` sesuai konvensi
   - Pengaturan ulang header `HTTP` dengan `$key` yang sama akan menimpa, diambil yang terakhir
   - Jika klien mengatur `Accept-Encoding`, maka server tidak bisa mengatur respons `Content-Length`, `Swoole` mendeteksi situasi ini akan mengabaikan nilai `Content-Length` dan mengeluarkan peringatan
   - Jika `Content-Length` sudah diatur, tidak boleh memanggil `Swoole\Http\Response::write()`, `Swoole` mendeteksi situasi ini akan mengabaikan nilai `Content-Length` dan mengeluarkan peringatan

!> Swoole versi >= `v4.6.0`, mendukung pengaturan ulang header `HTTP` dengan `$key` yang sama, dan `$value` mendukung berbagai tipe, seperti `array`, `object`, `int`, `float`, level bawah akan melakukan konversi `toString`, dan akan menghapus spasi akhir serta baris baru.

* **Contoh**

```php
$response->header('content-type', 'image/jpeg', true);

$response->header('Content-Length', '100002 ');
$response->header('Test-Value', [
    "a\r\n",
    'd5678',
    "e  \n ",
    null,
    5678,
    3.1415926,
]);
$response->header('Foo', new SplFileInfo('bar'));
```

### trailer()

?> **Menambahkan informasi `Header` ke akhir respons `HTTP`, hanya tersedia di `HTTP2`, digunakan untuk pemeriksaan integritas pesan, tanda tangan digital dll.**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **Parameter** 

  * **`string $key`**
    * **Fungsi**: `Key` dari `HTTP` header
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`string $value`**
    * **Fungsi**: `Value` dari `HTTP` header
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Nilai Kembali** 

  * Gagal mengatur, mengembalikan `false`
  * Berhasil mengatur, mengembalikan `true`

* **Catatan**

  !> Pengaturan ulang header `Http` dengan `$key` yang sama akan menimpa, diambil yang terakhir.

* **Contoh**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```

### cookie()

?> **Mengatur informasi `cookie` respons `HTTP`. Alias `setCookie`. Parameter method ini konsisten dengan `setcookie` di `PHP`.**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

* **Parameter** 

    * **`string $key`**
      * **Fungsi**: `Key` dari `Cookie`
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`string $value`**
      * **Fungsi**: `Value` dari `Cookie`
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $expire`**
      * **Fungsi**: `Waktu kedaluwarsa` dari `Cookie`
      * **Nilai Default**: 0, tidak kedaluwarsa
      * **Nilai Lain**: Tidak ada

    * **`string $path`**
      * **Fungsi**: `Path server untuk Cookie`
      * **Nilai Default**: /
      * **Nilai Lain**: Tidak ada

    * **`string $domain`**
      * **Fungsi**: `Domain untuk Cookie`
      * **Nilai Default**: ''
      * **Nilai Lain**: Tidak ada

    * **`bool $secure`**
      * **Fungsi**: `Apakah mentransmisikan Cookie melalui koneksi HTTPS yang aman`
      * **Nilai Default**: ''
      * **Nilai Lain**: Tidak ada

    * **`bool $httponly`**
      * **Fungsi**: `Apakah mengizinkan JavaScript browser mengakses Cookie dengan atribut HttpOnly`, `true` berarti tidak mengizinkan, `false` berarti mengizinkan
      * **Nilai Default**: false
      * **Nilai Lain**: Tidak ada

    * **`string $samesite`**
      * **Fungsi**: `Membatasi Cookie pihak ketiga untuk mengurangi risiko keamanan`, nilai opsional `Strict`, `Lax`, `None`
      * **Nilai Default**: ''
      * **Nilai Lain**: Tidak ada

    * **`string $priority`**
      * **Fungsi**: `Prioritas Cookie, saat jumlah Cookie melebihi batas, prioritas rendah akan dihapus lebih dulu`, nilai opsional `Low`, `Medium`, `High`
      * **Nilai Default**: ''
      * **Nilai Lain**: Tidak ada

* **Nilai Kembali** 

    * Gagal mengatur, mengembalikan `false`
    * Berhasil mengatur, mengembalikan `true`

* **Catatan**

  !> - Pengaturan `cookie` harus dilakukan sebelum method [end](/http_server?id=end)  
  - Parameter `$samesite` didukung mulai versi `v4.4.6`, parameter `$priority` didukung mulai versi `v4.5.8`  
  - `Swoole` akan otomatis melakukan `urlencode` pada `$value`, dapat menggunakan method `rawCookie()` untuk menonaktifkan pengkodean `$value`  
  - `Swoole` mengizinkan pengaturan beberapa `COOKIE` dengan `$key` yang sama

### rawCookie()

?> **Mengatur informasi `cookie` respons `HTTP`**

!> Parameter `rawCookie()` sama dengan `cookie()` di atas, hanya saja tidak melakukan pengkodean

### status()

?> **Mengirim kode status `Http`. Alias `setStatusCode()`**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **Parameter** 

  * **`int $http_status_code`**
    * **Fungsi**: Mengatur `HttpCode`
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`string $reason`**
    * **Fungsi**: Alasan kode status
    * **Nilai Default**: ''
    * **Nilai Lain**: Tidak ada

  * **Nilai Kembali** 

    * Gagal mengatur, mengembalikan `false`
    * Berhasil mengatur, mengembalikan `true`

* **Catatan**

  * Jika hanya memasukkan parameter pertama `$http_status_code` harus berupa `HttpCode` yang valid, seperti `200`, `502`, `301`, `404` dll, jika tidak akan diatur ke kode status `200`
  * Jika mengatur parameter kedua `$reason`, `$http_status_code` dapat berupa nilai numerik apa pun, termasuk `HttpCode` yang tidak terdefinisi, seperti `499`
  * Method `status` harus dijalankan sebelum [$response->end()](/http_server?id=end)

### gzip()

!> Method ini tidak digunakan lagi di `4.1.0` atau versi lebih tinggi, silakan lihat [http_compression](/http_server?id=http_compression); di versi baru gunakan opsi konfigurasi `http_compression` sebagai pengganti method `gzip`.  
Alasan utamanya adalah method `gzip()` tidak memeriksa header `Accept-Encoding` yang dimasukkan browser klien, jika klien tidak mendukung kompresi `gzip`, memaksakan penggunaan dapat menyebabkan klien tidak bisa mendekompresi.  
Opsi konfigurasi baru `http_compression` akan secara otomatis memilih apakah akan kompresi berdasarkan header `Accept-Encoding` klien, dan secara otomatis memilih algoritma kompresi terbaik.

?> **Mengaktifkan kompresi `HTTP GZIP`. Kompresi dapat mengurangi ukuran konten `HTML`, menghemat bandwidth jaringan, dan meningkatkan waktu respons. Harus menjalankan `gzip` sebelum mengirim konten dengan `write/end`, jika tidak akan melempar error.**
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **Parameter** 
    
     * **`int $level`**
       * **Fungsi**: Level kompresi, semakin tinggi level semakin kecil ukuran setelah kompresi, tetapi konsumsi `CPU` lebih banyak.
       * **Nilai Default**: 1
       * **Nilai Lain**: `1-9`

!> Setelah memanggil method `gzip`, level bawah akan otomatis menambahkan header pengkodean `Http`, kode PHP seharusnya tidak mengatur header `Http` terkait lagi; gambar format `jpg/png/gif` sudah terkompresi, tidak perlu kompresi lagi

!> Fungsi `gzip` bergantung pada library `zlib`, saat mengompilasi swoole level bawah akan mendeteksi apakah sistem memiliki `zlib`, jika tidak ada, method `gzip` tidak akan tersedia. Dapat menggunakan `yum` atau `apt-get` untuk menginstal library `zlib`:

```shell
sudo apt-get install libz-dev
```

### redirect()

?> **Mengirim pengalihan `Http`. Memanggil method ini akan otomatis `end` dan mengakhiri respons.**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

* **Parameter** 

    * **`string $url`**
      * **Fungsi**: Alamat baru pengalihan, dikirim sebagai header `Location`
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $http_code`**
      * **Fungsi**: Kode status [default `302` pengalihan sementara, masukkan `301` berarti pengalihan permanen]
      * **Nilai Default**: `302`
      * **Nilai Lain**: Tidak ada

  * **Nilai Kembali** 

    * Berhasil dipanggil, mengembalikan `true`, gagal atau konteks koneksi tidak ada, mengembalikan `false`

* **Contoh**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```

### write()

?> **Mengaktifkan `Http Chunk` untuk mengirim konten respons ke browser secara bertahap.**

?> Tentang `Http Chunk` dapat merujuk ke dokumen standar protokol `Http`.

```php
Swoole\Http\Response->write(string $data): bool
```

* **Parameter** 

    * **`string $data`**
      * **Fungsi**: Data yang akan dikirim [panjang maksimum tidak boleh melebihi `2M`, dikontrol oleh opsi konfigurasi [buffer_output_size](/server/setting?id=buffer_output_size)]
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

* **Nilai Kembali** 

    * Berhasil dipanggil, mengembalikan `true`, gagal atau konteks koneksi tidak ada, mengembalikan `false`

* **Catatan**

  * Setelah menggunakan `write` untuk mengirim data bertahap, method [end](/http_server?id=end) tidak akan menerima parameter apa pun, memanggil `end` hanya akan mengirim `Chunk` dengan panjang `0` yang menandakan data selesai dikirim
  * Jika melalui Swoole\Http\Response::header() mengatur `Content-Length`, lalu memanggil method ini, `Swoole` akan mengabaikan pengaturan `Content-Length` dan mengeluarkan peringatan
  * `Http2` tidak bisa menggunakan fungsi ini, jika tidak akan mengeluarkan peringatan
  * Jika klien mendukung kompresi respons, `Swoole\Http\Response::write()` akan memaksa menonaktifkan kompresi

### sendfile()

?> **Mengirim file ke browser.**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

* **Parameter** 

    * **`string $filename`**
      * **Fungsi**: Nama file yang akan dikirim [file tidak ada atau tidak ada izin akses `sendfile` akan gagal]
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $offset`**
      * **Fungsi**: Offset upload file [dapat menentukan mulai transmisi data dari bagian tengah file. Fitur ini dapat digunakan untuk mendukung resumable upload]
      * **Nilai Default**: `0`
      * **Nilai Lain**: Tidak ada

    * **`int $length`**
      * **Fungsi**: Ukuran data yang dikirim
      * **Nilai Default**: Ukuran file
      * **Nilai Lain**: Tidak ada

* **Nilai Kembali** 

      * Berhasil dipanggil, mengembalikan `true`, gagal atau konteks koneksi tidak ada, mengembalikan `false`

* **Catatan**

  * Level bawah tidak dapat menyimpulkan format MIME file yang akan dikirim, oleh karena itu kode aplikasi perlu menentukan `Content-Type`
  * Sebelum memanggil `sendfile` tidak boleh menggunakan method `write` untuk mengirim `Http-Chunk`
  * Setelah memanggil `sendfile`, level bawah akan otomatis menjalankan `end`
  * `sendfile` tidak mendukung kompresi `gzip`

* **Contoh**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```

### end()

?> **Mengirim body respons `Http` dan mengakhiri pemrosesan request.**

```php
Swoole\Http\Response->end(string $html): bool
```

* **Parameter** 

    * **`string $html`**
      * **Fungsi**: Konten yang akan dikirim
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

* **Nilai Kembali** 

    * Berhasil dipanggil, mengembalikan `true`, gagal atau konteks koneksi tidak ada, mengembalikan `false`

* **Catatan**

  * `end` hanya bisa dipanggil sekali, jika perlu mengirim data ke klien beberapa kali, gunakan method [write](/http_server?id=write)
  * Jika klien mengaktifkan [KeepAlive](/coroutine_client/http_client?id=keep_alive), koneksi akan dipertahankan, server akan menunggu request berikutnya
  * Jika klien tidak mengaktifkan `KeepAlive`, server akan memutus koneksi
  * Konten yang akan dikirim `end`, karena dibatasi [output_buffer_size](/server/setting?id=buffer_output_size), default `2M`, jika lebih dari batas ini maka respons akan gagal dan melempar error berikut:

!> Solusinya: gunakan [sendfile](/http_server?id=sendfile), [write](/http_server?id=write) atau atur [output_buffer_size](/server/setting?id=buffer_output_size)

```bash
WARNING finish (ERRNO 1203): The length of data [262144] exceeds the output buffer size[131072], please use the sendfile, chunked transfer mode or adjust the output_buffer_size
```

### detach()

?> **Memisahkan objek respons.** Setelah menggunakan method ini, saat objek `$response` dihancurkan tidak akan otomatis [end](/http_server?id=httpresponse), digunakan bersama [Http\Response::create](/http_server?id=create) dan [Server->send](/server/methods?id=send).

```php
Swoole\Http\Response->detach(): bool
```

* **Nilai Kembali** 

    * Berhasil dipanggil, mengembalikan `true`, gagal atau konteks koneksi tidak ada, mengembalikan `false`

* **Contoh** 

  * **Respons Lintas Proses**

  ?> Beberapa situasi, perlu mengirim respons ke klien di [Task process](/learn?id=taskworker进程). Ini dapat menggunakan `detach` untuk membuat objek `$response` independen. Di [Task process](/learn?id=taskworker进程) dapat membangun ulang `$response`, mengirim respons request `Http`.

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->set(['task_worker_num' => 1, 'worker_num' => 1]);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->task(strval($resp->fd));
  });

  $http->on('finish', function () {
      echo "task finish";
  });

  $http->on('task', function ($serv, $task_id, $worker_id, $data) {
      var_dump($data);
      $resp = Swoole\Http\Response::create($data);
      $resp->end("in task");
      echo "async task\n";
  });

  $http->start();
  ```

  * **Mengirim Konten Apa Pun**

  ?> Beberapa skenario khusus, perlu mengirim konten respons khusus ke klien. Method `end` bawaan objek `Http\Response` tidak dapat memenuhi kebutuhan, dapat menggunakan `detach` untuk memisahkan objek respons, lalu merakit sendiri data respons protokol HTTP, dan menggunakan `Server->send` untuk mengirim data.

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```

### create()

?> **Membuat objek `Swoole\Http\Response` baru.**

!> Sebelum menggunakan method ini, pastikan memanggil method `detach` untuk memisahkan objek `$response` lama, jika tidak dapat menyebabkan pengiriman konten respons dua kali untuk request yang sama.

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

* **Parameter** 

    * **`int $server`**
      * **Fungsi**: Objek `Swoole\Server` atau `Swoole\Coroutine\Socket`, array (array hanya bisa dua parameter, pertama objek `Swoole\Server`, kedua objek `Swoole\Http\Request`), atau deskriptor file
      * **Nilai Default**: -1
      * **Nilai Lain**: Tidak ada

    * **`int $fd`**
      * **Fungsi**: Deskriptor file. Jika parameter `$server` adalah objek `Swoole\Server`, `$fd` wajib diisi
      * **Nilai Default**: -1
      * **Nilai Lain**: Tidak ada

* **Nilai Kembali** 

    * Berhasil dipanggil mengembalikan objek `Swoole\Http\Response` baru, gagal mengembalikan `false`

* **Contoh**

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->detach();
    // Contoh 1
    $resp2 = Swoole\Http\Response::create($req->fd);
    // Contoh 2
    $resp2 = Swoole\Http\Response::create($http, $req->fd);
    // Contoh 3
    $resp2 = Swoole\Http\Response::create([$http, $req]);
    // Contoh 4
    $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, IPPROTO_IP);
    $socket->connect('127.0.0.1', 9501)
    $resp2 = Swoole\Http\Response::create($socket);
    $resp2->end("hello world");
});

$http->start();
```

### isWritable()

?> **Memeriksa apakah objek `Swoole\Http\Response` sudah berakhir (`end`) atau sudah dipisahkan (`detach`).**

```php
Swoole\Http\Response->isWritable(): bool
```

* **Nilai Kembali** 

    * Objek `Swoole\Http\Response` belum berakhir atau belum dipisahkan mengembalikan `true`, jika tidak mengembalikan `false`

!> Versi Swoole >= `v4.6.0` tersedia

* **Contoh**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->isWritable()); // true
    $resp->end('hello');
    var_dump($resp->isWritable()); // false
    $resp->setStatusCode(403); // http response is unavailable (maybe it has been ended or detached)
});

$http->start();
```

## Opsi Konfigurasi

### http_parse_cookie

?> **Konfigurasi untuk objek `Swoole\Http\Request`, menonaktifkan penguraian `Cookie`, akan menyimpan informasi `Cookies` mentah yang belum diolah di `header`. Default aktif**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```

### http_parse_post

?> **Konfigurasi untuk objek `Swoole\Http\Request`, mengatur sakelar penguraian pesan POST, default aktif**

* Saat diatur `true` akan otomatis mengurai body request `Content-Type: x-www-form-urlencoded` ke array `POST`.
* Saat diatur `false` akan menonaktifkan penguraian `POST`.

```php
$server->set([
    'http_parse_post' => false,
]);
```

### http_parse_files

?> **Konfigurasi untuk objek `Swoole\Http\Request`, mengatur sakelar penguraian upload file. Default aktif**

```php
$server->set([
    'http_parse_files' => false,
]);
```

### http_compression

?> **Konfigurasi untuk objek `Swoole\Http\Response`, mengaktifkan kompresi. Default aktif.**

!> - `http-chunk` tidak mendukung kompresi terpisah per segmen, jika menggunakan method [write](/http_server?id=write), kompresi akan dipaksa dimatikan.  
- `http_compression` tersedia di `v4.1.0` atau versi lebih tinggi

```php
$server->set([
    'http_compression' => false,
]);
```

Saat ini mendukung tiga format kompresi: `gzip`, `br`, `deflate`. Level bawah akan secara otomatis memilih metode kompresi berdasarkan header `Accept-Encoding` yang dimasukkan browser klien (prioritas algoritma kompresi: `br` > `gzip` > `deflate`).

**Ketergantungan:**

`gzip` dan `deflate` bergantung pada library `zlib`, saat mengompilasi `Swoole` level bawah akan mendeteksi apakah sistem memiliki `zlib`.

Dapat menggunakan `yum` atau `apt-get` untuk menginstal library `zlib`:

```shell
sudo apt-get install libz-dev
```

Format kompresi `br` bergantung pada library `brotli` dari `google`, cara instalasi silakan cari `install brotli on linux`, saat mengompilasi `Swoole` level bawah akan mendeteksi apakah sistem memiliki `brotli`.

### http_compression_level / compression_level / http_gzip_level

?> **Level kompresi, konfigurasi untuk objek `Swoole\Http\Response`**

!> `$level` Level kompresi, rentang `1-9`, semakin tinggi level semakin kecil ukuran setelah kompresi, tetapi konsumsi `CPU` lebih banyak. Default `1`, maksimal `9`

### http_compression_min_length / compression_min_length

?> **Mengatur byte minimum untuk mengaktifkan kompresi, konfigurasi untuk objek `Swoole\Http\Response`, hanya mengaktifkan kompresi jika melebihi nilai opsi ini. Default 20 byte.**

!> Versi Swoole >= `v4.6.3` tersedia

```php
$server->set([
    'compression_min_length' => 128,
]);
```

### upload_tmp_dir

?> **Mengatur direktori sementara upload file. Panjang direktori maksimum tidak boleh melebihi `220` byte**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```

### upload_max_filesize

?> **Mengatur nilai maksimum upload file**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```

### enable_static_handler

Mengaktifkan fungsi penanganan request file statis, perlu digunakan bersama `document_root`. Default `false`

### http_autoindex

Mengaktifkan fungsi `http autoindex`. Default tidak aktif

### http_index_files

Digunakan bersama `http_autoindex`, menentukan daftar file yang perlu diindeks

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```

### http_compression_types / compression_types

?> **Mengatur tipe respons yang perlu dikompresi, konfigurasi untuk objek `Swoole\Http\Response`**

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Versi Swoole >= `v4.8.12` tersedia

### static_handler_locations

?> **Mengatur path untuk penangan statis. Tipe array, default tidak aktif.**

!> Versi Swoole >= `v4.4.0` tersedia

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* Mirip dengan direktif `location` di `Nginx`, dapat menentukan satu atau lebih path sebagai path statis. Hanya `URL` di path yang ditentukan akan mengaktifkan penangan file statis, jika tidak akan dianggap request dinamis.
* Item `location` harus diawali /
* Mendukung path bertingkat, seperti `/app/images`
* Setelah mengaktifkan `static_handler_locations`, jika file yang diminta tidak ada, akan langsung mengembalikan error 404

### open_http2_protocol

?> **Mengaktifkan penguraian protokol `HTTP2`** [Nilai default: `false`]

!> Perlu mengaktifkan opsi [--enable-http2](/environment?id=编译选项) saat kompilasi, mulai `Swoole5` http2 dikompilasi secara default.

### document_root

?> **Konfigurasi direktori root file statis, digunakan bersama `enable_static_handler`.**

!> Fitur ini cukup sederhana, jangan digunakan langsung di lingkungan publik

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // Untuk versi v4.4.0 ke bawah, harus path absolut
    'enable_static_handler' => true,
]);
```

* Setelah mengatur `document_root` dan mengatur `enable_static_handler` menjadi `true`, saat level bawah menerima request `Http` akan memeriksa dulu apakah file ada di path document_root, jika ada akan langsung mengirim konten file ke klien, tidak memicu callback [onRequest](/http_server?id=on).
* Saat menggunakan fitur penanganan file statis, kode PHP dinamis dan file statis harus diisolasi, file statis disimpan di direktori tertentu

### max_concurrency

?> **Dapat membatasi jumlah request konkuren maksimum layanan `HTTP1/2`, setelah melebihi akan mengembalikan error `503`. Nilai default 4294967295, yaitu nilai maksimum unsigned int**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```

### worker_max_concurrency

?> **Setelah mengaktifkan korutinisasi satu-klik, proses `worker` akan terus menerima request. Untuk menghindari tekanan berlebih, kita dapat mengatur `worker_max_concurrency` untuk membatasi jumlah eksekusi request proses `worker`. Saat jumlah request melebihi nilai ini, proses `worker` akan menyimpan sementara request berlebih di antrian. Nilai default 4294967295, yaitu nilai maksimum unsigned int. Jika tidak mengatur `worker_max_concurrency` tetapi mengatur `max_concurrency`, level bawah akan otomatis mengatur `worker_max_concurrency` sama dengan `max_concurrency`**

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Versi Swoole >= `v5.0.0` tersedia

### http2_header_table_size

?> Mendefinisikan ukuran maksimum `header table` koneksi jaringan HTTP/2.

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```

### http2_enable_push

?> Konfigurasi ini digunakan untuk mengaktifkan atau menonaktifkan push HTTP2.

```php
$server->set([
  'http2_enable_push' => 0x2
])
```

### http2_max_concurrent_streams

?> Mengatur jumlah maksimum stream multipleks yang diterima dalam setiap koneksi jaringan HTTP/2.

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```

### http2_init_window_size

?> Mengatur ukuran inisialisasi jendela kontrol aliran HTTP/2.

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```

### http2_max_frame_size

?> Mengatur ukuran maksimum body dari satu frame protokol HTTP/2 yang dikirim melalui koneksi jaringan HTTP/2.

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```

### http2_max_header_list_size

?> Mengatur ukuran maksimum header yang dapat dikirim dalam request pada stream HTTP/2.

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
