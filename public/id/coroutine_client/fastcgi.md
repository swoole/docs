# Klien FastCGI Coroutine

PHP-FPM menggunakan protokol biner yang efisien: `Protokol FastCGI` untuk berkomunikasi. Dengan menggunakan klien FastCGI, Anda dapat berinteraksi langsung dengan layanan PHP-FPM tanpa melalui proxy reverse HTTP apa pun.

[Direktori kode sumber PHP](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)

## Contoh Penggunaan Sederhana

[Contoh kode lainnya](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> Contoh kode berikut perlu dipanggil dalam coroutine.

### Panggilan Cepat

```php
#greeter.php
echo 'Hello ' . ($_POST['who'] ?? 'World');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // Alamat listening FPM, bisa juga berupa alamat unixsocket seperti unix:/tmp/php-cgi.sock
    '/tmp/greeter.php', // File entry yang ingin dijalankan
    ['who' => 'Swoole'] // Informasi POST tambahan
);
```

### Gaya PSR

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1:9000', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withScriptFilename(__DIR__ . '/greeter.php')
        ->withMethod('POST')
        ->withBody(['who' => 'Swoole']);
    $response = $client->execute($request);
    echo "Result: {$response->getBody()}\n";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### Panggilan Kompleks

```php
#var.php
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
```

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withDocumentRoot(__DIR__)
        ->withScriptFilename(__DIR__ . '/var.php')
        ->withScriptName('var.php')
        ->withMethod('POST')
        ->withUri('/var?foo=bar&bar=char')
        ->withHeader('X-Foo', 'bar')
        ->withHeader('X-Bar', 'char')
        ->withBody(['foo' => 'bar', 'bar' => 'char']);
    $response = $client->execute($request);
    echo "Result: \n{$response->getBody()}";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### Proxy Satu Klik WordPress

!> Penggunaan ini tidak memiliki arti produksi. Dalam produksi, proxy dapat digunakan untuk memproksi permintaan HTTP dari beberapa API lama ke layanan FPM lama (bukan memproksi seluruh situs).

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # Direktori root proyek WordPress
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # Port di sini harus sesuai dengan konfigurasi WordPress, umumnya tidak ada port khusus, yaitu 80
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], # Path sumber daya statis
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # Membuat objek proxy
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # Proxy permintaan satu klik
});
$server->start();
```

## Method

### call

Static method, langsung membuat koneksi klien baru, mengirim permintaan ke server FPM dan menerima body respons.

!> FPM hanya mendukung koneksi pendek, jadi dalam keadaan normal, membuat objek persisten tidak terlalu berguna.

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **Parameter**

    * **`string $url`**
      * **Fungsi**: Alamat listening FPM【mis. `127.0.0.1:9000`, `unix:/tmp/php-cgi.sock`, dll.】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $path`**
      * **Fungsi**: File entry yang ingin dijalankan
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`$data`**
      * **Fungsi**: Data permintaan tambahan
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout【Default -1 berarti tidak pernah timeout】
      * **Satuan**: Detik【mendukung float, mis. 1.5 berarti 1s+500ms】
      * **Default**: `-1`
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Mengembalikan konten utama (body) dari respons server
    * Akan melempar exception `Swoole\Coroutine\FastCGI\Client\Exception` saat terjadi error

### __construct

Method konstruktor objek klien, menentukan server FPM target.

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **Parameter**

    * **`string $host`**
      * **Fungsi**: Alamat server target【mis. `127.0.0.1`, `unix://tmp/php-fpm.sock`, dll.】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port server target【Tidak diperlukan saat alamat target adalah UNIXSocket】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

### execute

Menjalankan permintaan, mengembalikan respons.

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **Parameter**

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **Fungsi**: Objek yang berisi informasi permintaan, biasanya menggunakan `Swoole\FastCGI\HttpRequest` untuk simulasi permintaan HTTP, gunakan kelas permintaan asli `Swoole\FastCGI\Request` untuk kebutuhan khusus
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout【Default `-1` berarti tidak pernah timeout】
      * **Satuan**: detik【mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: `-1`
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Mengembalikan objek Response yang sesuai dengan tipe objek permintaan. Misalnya, `Swoole\FastCGI\HttpRequest` akan mengembalikan objek `Swoole\FastCGI\HttpResponse`, yang berisi informasi respons dari server FPM
    * Akan melempar exception `Swoole\Coroutine\FastCGI\Client\Exception` saat terjadi error

## Kelas Request/Response Terkait

Karena library tidak dapat memperkenalkan dependensi besar dari implementasi PSR dan loading ekstensi selalu terjadi sebelum eksekusi kode PHP, objek request dan response terkait tidak mewarisi antarmuka PSR. Namun, mereka diimplementasikan dengan gaya PSR sebisa mungkin agar pengembang dapat cepat memulai.

Kode sumber untuk kelas yang mensimulasikan request dan response HTTP di FastCGI dapat ditemukan di tautan berikut, sangat sederhana, kode adalah dokumentasi:

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
