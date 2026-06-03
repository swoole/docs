# Daftar Fungsi

Swoole selain menyediakan fungsi terkait komunikasi jaringan, juga menyediakan beberapa fungsi untuk memperoleh informasi sistem yang dapat digunakan oleh program PHP.

## swoole_set_process_name()

Digunakan untuk mengatur nama proses. Setelah mengubah nama proses, perintah `ps` tidak lagi menampilkan `php your_file.php`, melainkan string yang telah ditentukan.

Fungsi ini menerima satu parameter string.

Fungsi ini memiliki fungsi yang sama dengan [cli_set_process_title](https://www.php.net/manual/zh/function.cli-set-process-title.php) yang disediakan oleh PHP 5.5. Namun `swoole_set_process_name` dapat digunakan pada versi di atas PHP 5.2. Kompatibilitas `swoole_set_process_name` lebih rendah dibandingkan `cli_set_process_title`, oleh karena itu jika fungsi `cli_set_process_title` tersedia, maka sebaiknya gunakan `cli_set_process_title`.

```php
function swoole_set_process_name(string $name): void
```

Contoh penggunaan:

```php
swoole_set_process_name("swoole server");
```

### Cara Mengganti Nama Setiap Proses Swoole Server <!-- {docsify-ignore} -->

* Ubah nama proses utama saat [onStart](/server/events?id=onstart) dipanggil
* Ubah nama proses manajer (`manager`) saat [onManagerStart](/server/events?id=onmanagerstart) dipanggil
* Ubah nama proses worker saat [onWorkerStart](/server/events?id=onworkerstart) dipanggil

!> Kernel Linux versi lama dan Mac OSX tidak mendukung penggantian nama proses

## swoole_strerror()

Mengonversi kode error menjadi pesan error.

Prototipe fungsi:

```php
function swoole_strerror(int $errno, int $error_type = 1): string
```

Tipe error:

* `1`: `Unix Errno` standar, dihasilkan oleh kesalahan panggilan sistem, seperti `EAGAIN`, `ETIMEDOUT`, dll.
* `2`: Kode error `getaddrinfo`, dihasilkan oleh operasi `DNS`
* `9`: Kode error internal Swoole, diperoleh menggunakan `swoole_last_error()`

Contoh penggunaan:

```php
var_dump(swoole_strerror(swoole_last_error(), 9));
```

## swoole_version()

Mendapatkan nomor versi ekstensi swoole, misalnya `1.6.10`

```php
function swoole_version(): string
```

Contoh penggunaan:

```php
var_dump(SWOOLE_VERSION); // Variabel global SWOOLE_VERSION juga merepresentasikan versi ekstensi swoole
var_dump(swoole_version());
/**
Nilai kembalian:
string(6) "1.9.23"
string(6) "1.9.23"
**/
```

## swoole_errno()

Mendapatkan kode error dari panggilan sistem terbaru, setara dengan variabel `errno` di `C/C++`.

```php
function swoole_errno(): int
```

Nilai kode error tergantung pada sistem operasi. Gunakan `swoole_strerror` untuk mengonversi error menjadi pesan error.

## swoole_get_local_ip()

Fungsi ini digunakan untuk mendapatkan alamat IP dari semua antarmuka jaringan pada mesin ini.

```php
function swoole_get_local_ip(): array
```

Contoh penggunaan:

```php
// Mendapatkan alamat IP semua antarmuka jaringan pada mesin ini
$list = swoole_get_local_ip();
print_r($list);
/**
Return Value
Array
(
      [eno1] => 10.10.28.228
      [br-1e72ecd47449] => 172.20.0.1
      [docker0] => 172.17.0.1
)
**/
```

!> Catatan
* Saat ini hanya mengembalikan alamat IPv4, hasilnya akan memfilter alamat loopback lokal 127.0.0.1.
* Array hasil adalah array asosiatif dengan nama interface sebagai kunci. Contohnya `array("eth0" => "192.168.1.100")`
* Fungsi ini akan memanggil panggilan sistem `ioctl` secara langsung untuk mendapatkan informasi antarmuka, tanpa cache di tingkat bawah

## swoole_clear_dns_cache()

Menghapus cache DNS bawaan swoole, berlaku untuk `swoole_client` dan `swoole_async_dns_lookup`.

```php
function swoole_clear_dns_cache()
```

## swoole_get_local_mac()

Mendapatkan alamat `Mac` dari kartu jaringan lokal.

```php
function swoole_get_local_mac(): array
```

* Mengembalikan alamat `Mac` dari semua kartu jaringan jika berhasil dipanggil

```php
array(4) {
  ["lo"]=>
  string(17) "00:00:00:00:00:00"
  ["eno1"]=>
  string(17) "64:00:6A:65:51:32"
  ["docker0"]=>
  string(17) "02:42:21:9B:12:05"
  ["vboxnet0"]=>
  string(17) "0A:00:27:00:00:00"
}
```

## swoole_cpu_num()

Mendapatkan jumlah inti CPU pada mesin ini.

```php
function swoole_cpu_num(): int
```

* Mengembalikan jumlah inti CPU jika berhasil dipanggil, misalnya:

```shell
php -r "echo swoole_cpu_num();"
```

## swoole_last_error()

Mendapatkan kode error terbaru dari lapisan bawah Swoole.

```php
function swoole_last_error(): int
```

Gunakan `swoole_strerror(swoole_last_error(), 9)` untuk mengonversi kode error menjadi pesan error. Untuk daftar lengkap kode error, lihat [Daftar Kode Error Swoole](/other/errno?id=swoole)

## swoole_mime_type_add()

Menambahkan tipe mime baru ke tabel tipe mime bawaan.

```php
function swoole_mime_type_add(string $suffix, string $mime_type): bool
```

## swoole_mime_type_set()

Memodifikasi suatu tipe mime, mengembalikan `false` jika gagal (misalnya tidak ada).

```php
function swoole_mime_type_set(string $suffix, string $mime_type): bool
```

## swoole_mime_type_delete()

Menghapus suatu tipe mime, mengembalikan `false` jika gagal (misalnya tidak ada).

```php
function swoole_mime_type_delete(string $suffix): bool
```

## swoole_mime_type_get()

Mendapatkan tipe mime yang sesuai dengan nama file.

```php
function swoole_mime_type_get(string $filename): string
```

## swoole_mime_type_exists()

Memeriksa apakah tipe mime yang sesuai dengan ekstensi (suffix) ada.

```php
function swoole_mime_type_exists(string $suffix): bool
```

## swoole_substr_json_decode()

Deserialisasi JSON tanpa salinan (zero-copy), selain `$offset` dan `$length`, parameter lainnya sama dengan [json_decode](https://www.php.net/manual/en/function.json-decode.php).

!> Tersedia di Swoole versi >= `v4.5.6`, mulai versi `v4.5.7` perlu menambahkan parameter [--enable-swoole-json](/environment?id=通用参数) saat kompilasi. Untuk skenario penggunaan, lihat [Swoole 4.5.6 Mendukung Zero-Copy JSON atau PHP Deserialization](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_json_decode(string $packet, int $offset, int $length, bool $assoc = false, int $depth = 512, int $options = 0)
```

* **Contoh**

```php
$val = json_encode(['hello' => 'swoole']);
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(json_decode(substr($str, 4, $l), true));
var_dump(swoole_substr_json_decode($str, 4, $l, true));
```

## swoole_substr_unserialize()

Deserialisasi PHP tanpa salinan (zero-copy), selain `$offset` dan `$length`, parameter lainnya sama dengan [unserialize](https://www.php.net/manual/en/function.unserialize.php).

!> Tersedia di Swoole versi >= `v4.5.6`. Untuk skenario penggunaan, lihat [Swoole 4.5.6 Mendukung Zero-Copy JSON atau PHP Deserialization](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_unserialize(string $packet, int $offset, int $length, array $options= [])
```

* **Contoh**

```php
$val = serialize('hello');
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(unserialize(substr($str, 4, $l)));
var_dump(swoole_substr_unserialize($str, 4, $l));
```

## swoole_error_log()

Mengeluarkan informasi error ke dalam log. `$level` adalah [level log](/consts?id=日志等级).

!> Tersedia di Swoole versi >= `v4.5.8`

```php
function swoole_error_log(int $level, string $msg)
```

## swoole_clear_error()

Menghapus error pada socket atau error pada kode error terakhir.

!> Tersedia di Swoole versi >= `v4.6.0`

```php
function swoole_clear_error()
```

## swoole_coroutine_socketpair()

Versi korutin dari [socket_create_pair](https://www.php.net/manual/en/function.socket-create-pair.php).

!> Tersedia di Swoole versi >= `v4.6.0`

```php
function swoole_coroutine_socketpair(int $domain , int $type , int $protocol): array|bool
```

## swoole_async_set

Fungsi ini dapat mengatur opsi terkait I/O asinkron.

```php
function swoole_async_set(array $settings)
```

- enable_signalfd mengaktifkan atau menonaktifkan penggunaan fitur `signalfd`
- enable_coroutine mengaktifkan atau menonaktifkan korutin bawaan, [lihat detailnya](/server/setting?id=enable_coroutine)
- aio_core_worker_num mengatur jumlah minimum proses AIO
- aio_worker_num mengatur jumlah maksimum proses AIO

## swoole_error_log_ex()

Menulis log dengan level dan kode error yang ditentukan.

```php
function swoole_error_log_ex(int $level, int $error, string $msg)
```

!> Tersedia di Swoole versi >= `v4.8.1`

## swoole_ignore_error()

Mengabaikan log error untuk kode error yang ditentukan.

```php
function swoole_ignore_error(int $error)
```

!> Tersedia di Swoole versi >= `v4.8.1`
