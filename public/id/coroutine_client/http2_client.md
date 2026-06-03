# Coroutine\Http2\Client

Klien Http2 Coroutine

## Contoh Penggunaan

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $domain = 'www.zhihu.com';
    $cli = new Client($domain, 443, true);
    $cli->set([
        'timeout' => -1,
        'ssl_host_name' => $domain
    ]);
    $cli->connect();
    $req = new Request();
    $req->method = 'POST';
    $req->path = '/api/v4/answers/300000000/voters';
    $req->headers = [
        'host' => $domain,
        'user-agent' => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip'
    ];
    $req->data = '{"type":"up"}';
    $cli->send($req);
    $response = $cli->recv();
    var_dump(assert(json_decode($response->data)->error->code === 10002));
});
```

## Method

### __construct()

Method konstruktor.

```php
Swoole\Coroutine\Http2\Client::__construct(string $host, int $port, bool $open_ssl = false): void
```

  * **Parameter**

    * **`string $host`**
      * **Fungsi**: Alamat IP host target【Jika `$host` adalah nama domain, akan dilakukan query `DNS` di tingkat bawah】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port target【`Http` biasanya port `80`, `Https` biasanya port `443`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`bool $open_ssl`**
      * **Fungsi**: Apakah akan mengaktifkan enkripsi tunnel `TLS/SSL`【situs `https` harus diatur ke `true`】
      * **Default**: `false`
      * **Nilai lain**: `true`

  * **Catatan**

    !> -Jika Anda perlu meminta URL eksternal, ubah `timeout` ke nilai yang lebih besar, lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)  
    -`$ssl` memerlukan `openssl`, harus diaktifkan saat mengompilasi `Swoole` [--enable-openssl](/environment?id=opsi-kompilasi)

### set()

Mengatur parameter klien, untuk item konfigurasi detail lainnya silakan lihat opsi konfigurasi [Swoole\Client::set](/client?id=konfigurasi)

```php
Swoole\Coroutine\Http2\Client->set(array $options): void
```

### connect()

Menghubungkan ke server target. Method ini tidak memiliki parameter.

!> Setelah memanggil `connect`, sistem akan secara otomatis melakukan [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine). Fungsi `connect` akan kembali saat koneksi berhasil atau gagal. Setelah koneksi terbentuk, Anda dapat menggunakan method `send` untuk mengirim permintaan ke server.

```php
Swoole\Coroutine\Http2\Client->connect(): bool
```

  * **Nilai Kembali**

    * Koneksi berhasil, mengembalikan `true`
    * Koneksi gagal, mengembalikan `false`, periksa properti `errCode` untuk mendapatkan kode error

### stats()

Mendapatkan status aliran.

```php
Swoole\Coroutine\Http2\Client->stats([$key]): array|bool
```

  * **Contoh**

```php
var_dump($client->stats(), $client->stats()['local_settings'], $client->stats('local_settings'));
```

### isStreamExist()

Menentukan apakah aliran yang ditentukan ada.

```php
Swoole\Coroutine\Http2\Client->isStreamExist(int $stream_id): bool
```

### send()

Mengirim permintaan ke server, sistem akan secara otomatis membuat `stream` `Http2`. Dapat mengirim beberapa permintaan secara bersamaan.

```php
Swoole\Coroutine\Http2\Client->send(Swoole\Http2\Request $request): int|false
```

  * **Parameter**

    * **`Swoole\Http2\Request $request`**
      * **Fungsi**: mengirim objek Swoole\Http2\Request
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Berhasil mengembalikan nomor stream, nomor dimulai dari `1` dan bertambah dengan angka ganjil
    * Gagal mengembalikan `false`

  * **Petunjuk**

    * **Objek Request**

      !> Objek `Swoole\Http2\Request` tidak memiliki method; Anda menulis informasi terkait permintaan dengan mengatur properti objek.

      * `headers` array, header `HTTP`
      * `method` string, mengatur method permintaan, seperti `GET`, `POST`
      * `path` string, mengatur path `URL`, seperti `/index.php?a=1&b=2`, harus dimulai dengan /
      * `cookies` array, mengatur `COOKIES`
      * `data` mengatur `body` permintaan, jika berupa string akan langsung dikirim sebagai `RAW form-data`
      * `data` jika berupa array, sistem akan secara otomatis mengemasnya menjadi format `x-www-form-urlencoded` untuk konten `POST` dan mengatur `Content-Type` menjadi `application/x-www-form-urlencoded`
      * `pipeline` boolean, jika diatur ke `true`, setelah mengirim `$request`, `stream` tidak akan ditutup, dan Anda dapat terus menulis konten data

    * **pipeline**

      * Secara default, method `send` mengakhiri `Http2 Stream` saat ini setelah mengirim permintaan. Mengaktifkan `pipeline` akan mempertahankan aliran stream, memungkinkan beberapa panggilan method `write` untuk mengirim data frame ke server. Silakan lihat method `write`.

### write()

Mengirim lebih banyak data frame ke server, Anda dapat memanggil write beberapa kali untuk menulis data frame ke stream yang sama.

```php
Swoole\Coroutine\Http2\Client->write(int $streamId, mixed $data, bool $end = false): bool
```

  * **Parameter**

    * **`int $streamId`**
      * **Fungsi**: Nomor stream, dikembalikan oleh method `send`
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`mixed $data`**
      * **Fungsi**: Konten data frame, bisa berupa string atau array
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`bool $end`**
      * **Fungsi**: Apakah akan menutup stream
      * **Default**: `false`
      * **Nilai lain**: `true`

  * **Contoh Penggunaan**

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9518);
    $cli->set(['timeout' => 1]);
    var_dump($cli->connect());

    $req3 = new Request();
    $req3->path = "/index.php";
    $req3->headers = [
        'host' => "localhost",
        "user-agent" => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip',
    ];
    $req3->pipeline = true;
    $req3->method = "POST";
    $streamId = $cli->send($req3);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    //end stream
    $cli->write($streamId, ['int' => rand(1000, 9999), 'end' => true], true);
    var_dump($cli->recv());
    $cli->close();
});
```

!> Jika ingin menggunakan `write` untuk mengirim data frame dalam segmen, Anda harus mengatur `$request->pipeline` ke `true` saat melakukan permintaan `send`  
Setelah data frame dengan `end` `true` dikirim, stream akan ditutup, dan Anda tidak dapat lagi memanggil `write` untuk mengirim data ke stream ini.

### recv()

Menerima permintaan.

!> Memanggil method ini akan memicu [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine)

```php
Swoole\Coroutine\Http2\Client->recv(float $timeout): Swoole\Http2\Response;
```

  * **Parameter**

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout, lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Satuan**: detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

Berhasil mengembalikan objek Swoole\Http2\Response

```php
/**@var $resp Swoole\Http2\Response */
var_dump($resp->statusCode); // Kode status Http yang dikirim server, seperti 200, 502, dll.
var_dump($resp->headers); // Informasi Header yang dikirim server
var_dump($resp->cookies); // Informasi COOKIE yang diatur server
var_dump($resp->set_cookie_headers); // Informasi COOKIE mentah yang dikembalikan server, termasuk domain dan path
var_dump($resp->data); // Body respons yang dikirim server
```

!> Sebelum Swoole versi < [v4.0.4](/version/bc?id=_404), properti `data` adalah `body`; sebelum Swoole versi < [v4.0.3](/version/bc?id=_403), `headers` dan `cookies` dalam bentuk tunggal.

### read()

Mirip dengan `recv()`, perbedaannya adalah untuk respons tipe `pipeline`, `read` memungkinkan pembacaan beberapa kali. Setiap kali membaca sebagian konten untuk menghemat memori atau menerima informasi push dengan cepat, sedangkan `recv` selalu menggabungkan semua frame menjadi respons lengkap sebelum kembali.

!> Memanggil method ini akan menghasilkan [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine)

```php
Swoole\Coroutine\Http2\Client->read(float $timeout): Swoole\Http2\Response;
```

  * **Parameter**

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout, lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Satuan**: Detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    Berhasil mengembalikan objek Swoole\Http2\Response

### goaway()

Frame GOAWAY digunakan untuk memulai penutupan koneksi atau mengirim sinyal status error serius.

```php
Swoole\Coroutine\Http2\Client->goaway(int $error_code = SWOOLE_HTTP2_ERROR_NO_ERROR, string $debug_data): bool
```

### ping()

Frame PING adalah mekanisme yang digunakan untuk mengukur waktu round-trip minimum dari pengirim dan untuk menentukan apakah koneksi idle masih aktif.

```php
Swoole\Coroutine\Http2\Client->ping(): bool
```

### close()

Menutup koneksi.

```php
Swoole\Coroutine\Http2\Client->close(): bool
```
