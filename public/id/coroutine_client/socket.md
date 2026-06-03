# Coroutine\Socket

Modul `Swoole\Coroutine\Socket` dibandingkan dengan [server bergaya coroutine](/server/co_init) dan modul terkait [klien coroutine](/coroutine_client/init) `Socket` dapat mencapai operasi `IO` yang lebih terperinci.

!> Bisa menggunakan nama pendek `Co\Socket` untuk menyederhanakan nama kelas. Modul ini cukup low-level, pengguna disarankan memiliki pengalaman pemrograman Socket.

## Contoh Lengkap

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9601);
    while ($retval)
    {
        $n = $socket->send('hello');
        var_dump($n);

        $data = $socket->recv();
        var_dump($data);

        //Jika terjadi error atau lawan menutup koneksi, lokal juga perlu ditutup
        if ($data === '' || $data === false) {
            echo "errCode: {$socket->errCode}\n";
            $socket->close();
            break;
        }

        Coroutine::sleep(1.0);
    }

    var_dump($retval, $socket->errCode, $socket->errMsg);
});
```

## Penjadwalan Coroutine

Antarmuka operasi `IO` yang disediakan modul `Coroutine\Socket` semuanya bergaya pemrograman sinkron, dan secara otomatis menggunakan penjadwal [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine) untuk mewujudkan [IO Asinkron](/learn?id=io-sinkron-dan-io-asinkron).

## Kode Error

Saat menjalankan panggilan sistem terkait `socket`, mungkin mengembalikan error -1, dan sistem akan mengatur properti `Coroutine\Socket->errCode` ke nomor error sistem `errno`, silakan lihat dokumentasi `man` yang sesuai. Misalnya, saat `$socket->accept()` mengembalikan error, arti `errCode` bisa ditemukan di dokumentasi kode error yang tercantum di `man accept`.

## Properti

### fd

`ID` deskriptor file yang sesuai dengan `socket`

### errCode

Kode error

## Method

### __construct()

Method konstruktor. Membangun objek `Coroutine\Socket`.

```php
Swoole\Coroutine\Socket::__construct(int $domain, int $type, int $protocol);
```

!> Detailnya bisa dilihat di dokumentasi `man socket`.

  * **Parameter**

    * **`int $domain`**
      * **Fungsi**: Domain protokol【Dapat menggunakan `AF_INET`, `AF_INET6`, `AF_UNIX`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $type`**
      * **Fungsi**: Tipe【Dapat menggunakan `SOCK_STREAM`, `SOCK_DGRAM`, `SOCK_RAW`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $protocol`**
      * **Fungsi**: Protokol【Dapat menggunakan `IPPROTO_TCP`, `IPPROTO_UDP`, `IPPROTO_STCP`, `IPPROTO_TIPC`, `0`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

!> Method konstruktor memanggil panggilan sistem `socket` untuk membuat handle `socket`. Jika panggilan gagal, akan melempar exception `Swoole\Coroutine\Socket\Exception`. Juga mengatur properti `$socket->errCode`. Penyebab kegagalan panggilan sistem dapat ditentukan berdasarkan nilai properti ini.

### getOption()

Mendapatkan konfigurasi.

!> Method ini sesuai dengan panggilan sistem `getsockopt`, untuk detailnya silakan lihat dokumentasi `man getsockopt`.  
Method ini setara dengan fungsi `socket_get_option` dari ekstensi `sockets`, dapat dilihat di [dokumentasi PHP](https://www.php.net/manual/zh/function.socket-get-option.php).

!> Swoole versi >= v4.3.2

```php
Swoole\Coroutine\Socket->getOption(int $level, int $optname): mixed
```

  * **Parameter**

    * **`int $level`**
      * **Fungsi**: Menentukan tingkat protokol tempat opsi berada
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

      !> Misalnya, untuk mengambil opsi di tingkat socket, parameter `level` akan menggunakan `SOL_SOCKET`.  
      Tingkat lain dapat digunakan dengan menentukan nomor protokol untuk tingkat tersebut, seperti `TCP`. Nomor protokol dapat ditemukan menggunakan fungsi [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php).

    * **`int $optname`**
      * **Fungsi**: Opsi socket yang tersedia sama dengan fungsi [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

### setOption()

Mengatur konfigurasi.

!> Method ini sesuai dengan panggilan sistem `setsockopt`, untuk detailnya silakan lihat dokumentasi `man setsockopt`. Method ini setara dengan fungsi `socket_set_option` dari ekstensi `sockets`, dapat dilihat di [dokumentasi PHP](https://www.php.net/manual/zh/function.socket-set-option.php).

!> Swoole versi >= v4.3.2

```php
Swoole\Coroutine\Socket->setOption(int $level, int $optname, mixed $optval): bool
```

  * **Parameter**

    * **`int $level`**
      * **Fungsi**: Menentukan tingkat protokol tempat opsi berada
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

      !> Misalnya, untuk mengambil opsi di tingkat socket, parameter `level` akan menggunakan `SOL_SOCKET`.  
      Tingkat lain dapat digunakan dengan menentukan nomor protokol untuk tingkat tersebut, seperti `TCP`. Anda dapat menggunakan fungsi [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php) untuk menemukan nomor protokol.

    * **`int $optname`**
      * **Fungsi**: Opsi socket yang tersedia sama dengan fungsi [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $optval`**
      * **Fungsi**: Nilai opsi【Bisa `int`, `bool`, `string`, `array`. Tergantung pada `level` dan `optname`.】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

### setProtocol()

Memberi `socket` kemampuan menangani protokol, dapat mengkonfigurasi apakah akan mengaktifkan enkripsi `SSL` dan mengatasi [masalah batasan paket TCP](/learn?id=masalah-batasan-paket-tcp) dll.

!> Swoole versi >= v4.3.2

```php
Swoole\Coroutine\Socket->setProtocol(array $settings): bool
```

  * **Parameter yang didukung di `$settings`**

Parameter | Tipe
---|---
open_ssl | bool
ssl_cert_file | string
ssl_key_file | string
open_eof_check | bool
open_eof_split | bool
open_mqtt_protocol | bool
open_fastcgi_protocol | bool
open_length_check | bool
package_eof | string
package_length_type | string
package_length_offset | int
package_body_offset | int
package_length_func | callable
package_max_length | int

!> Arti semua parameter di atas sepenuhnya konsisten dengan [Server->set()](/server/setting?id=open_eof_check), tidak akan diulangi di sini.

  * **Contoh**

```php
$socket->setProtocol([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,
    'package_body_offset'   => 4,
]);
```

### bind()

Mengikat alamat dan port.

!> Method ini tidak melibatkan operasi `IO`, tidak akan menyebabkan perpindahan coroutine

```php
Swoole\Coroutine\Socket->bind(string $address, int $port = 0): bool
```

  * **Parameter**

    * **`string $address`**
      * **Fungsi**: Alamat yang akan diikat【mis. `0.0.0.0`, `127.0.0.1`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port yang akan diikat【Default `0`, sistem akan secara acak menetapkan port yang tersedia. Bisa menggunakan method [getsockname](/coroutine_client/socket?id=getsockname) untuk mendapatkan `port` yang ditetapkan sistem】
      * **Default**: `0`
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Berhasil mengembalikan `true`
    * Gagal mengembalikan `false`, periksa properti `errCode` untuk mendapatkan penyebab kegagalan

### listen()

Mendengarkan `Socket`.

!> Method ini tidak melibatkan operasi `IO`, tidak akan menyebabkan perpindahan coroutine

```php
Swoole\Coroutine\Socket->listen(int $backlog = 0): bool
```

  * **Parameter**

    * **`int $backlog`**
      * **Fungsi**: Panjang antrian pendengaran【Default `0`, sistem menggunakan `epoll` untuk mewujudkan `IO` asinkron, tidak ada blocking, sehingga kepentingan `backlog` tidak terlalu tinggi】
      * **Default**: `0`
      * **Nilai lain**: Tidak ada

      !> Jika ada logika blocking atau memakan waktu dalam aplikasi, dan `accept` tidak menerima koneksi tepat waktu, koneksi baru akan menumpuk di antrian pendengaran `backlog`. Jika melebihi panjang `backlog`, layanan akan menolak koneksi baru.

  * **Nilai Kembali**

    * Berhasil mengembalikan `true`
    * Gagal mengembalikan `false`, periksa properti `errCode` untuk mendapatkan penyebab kegagalan

  * **Parameter Kernel**

    Nilai maksimum `backlog` dibatasi oleh parameter kernel `net.core.somaxconn`. Di `Linux`, alat `sysctl` dapat digunakan untuk menyesuaikan semua parameter `kernel` secara dinamis. Penyesuaian dinamis berarti nilai parameter kernel berlaku segera setelah dimodifikasi. Namun, efek ini hanya terbatas pada tingkat OS, aplikasi harus dimulai ulang agar benar-benar berlaku. Perintah `sysctl -a` akan menampilkan semua parameter kernel dan nilainya.

    ```shell
    sysctl -w net.core.somaxconn=2048
    ```

    Perintah di atas mengubah nilai parameter kernel `net.core.somaxconn` menjadi `2048`. Meskipun perubahan ini berlaku segera, akan kembali ke nilai default setelah reboot. Untuk menyimpan perubahan secara permanen, ubah `/etc/sysctl.conf`, tambahkan `net.core.somaxconn=2048`, lalu jalankan perintah `sysctl -p`.

### accept()

Menerima koneksi yang dimulai oleh klien.

Memanggil method ini akan segera menangguhkan coroutine saat ini, bergabung ke [EventLoop](/learn?id=apa-itu-eventloop) untuk mendengarkan event readable. Saat koneksi masuk melalui `Socket`, coroutine akan secara otomatis dibangunkan, dan objek `Socket` yang sesuai dengan koneksi klien akan dikembalikan.

!> Method ini harus digunakan setelah method `listen`, dan cocok untuk sisi `Server`.

```php
Swoole\Coroutine\Socket->accept(float $timeout = 0): Coroutine\Socket|false;
```

  * **Parameter**

    * **`float $timeout`**
      * **Fungsi**: Mengatur timeout【Setelah mengatur parameter timeout, timer akan diatur di tingkat bawah. Jika tidak ada koneksi klien yang datang dalam waktu yang ditentukan, method `accept` akan mengembalikan `false`】
      * **Satuan nilai**: Detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Mengembalikan `false` saat timeout atau error dalam panggilan sistem `accept`. Bisa menggunakan properti `errCode` untuk mendapatkan kode error. Untuk error timeout, kode error adalah `ETIMEDOUT`
    * Mengembalikan `socket` koneksi klien saat berhasil, juga bertipe `Swoole\Coroutine\Socket`. Operasi seperti `send`, `recv`, `close` dapat dilakukan padanya.

  * **Contoh**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
$socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
$socket->bind('127.0.0.1', 9601);
$socket->listen(128);

    while(true) {
        echo "Accept: \n";
        $client = $socket->accept();
        if ($client === false) {
            var_dump($socket->errCode);
        } else {
            var_dump($client);
        }
    }
});
```

### connect()

Menghubungkan ke server target.

Memanggil method ini akan memulai panggilan sistem `connect` asinkron, menangguhkan coroutine saat ini, dan mendengarkan keterbacaan di tingkat bawah. Saat koneksi berhasil atau gagal, coroutine akan dilanjutkan.

Method ini cocok untuk sisi `Client`, mendukung `IPv4`, `IPv6`, dan [unixSocket](/learn?id=apa-itu-ipc).

```php
Swoole\Coroutine\Socket->connect(string $host, int $port = 0, float $timeout = 0): bool
```

  * **Parameter**

    * **`string $host`**
      * **Fungsi**: Alamat server target【mis. `127.0.0.1`, `192.168.1.100`, `/tmp/php-fpm.sock`, `www.baidu.com`, dll. Bisa memberikan alamat `IP`, path `Unix Socket`, atau nama domain. Jika nama domain, resolusi `DNS` asinkron akan dilakukan secara otomatis di tingkat bawah tanpa menyebabkan blocking】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port server target【Port harus diatur saat `domain` dari `Socket` adalah `AF_INET` atau `AF_INET6`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur periode timeout【Sistem di tingkat bawah akan mengatur timer, jika koneksi tidak dapat dibuat dalam waktu yang ditentukan, `connect` akan mengembalikan `false`】
      * **Satuan**: Detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Mengembalikan `false` saat timeout atau error dalam panggilan sistem `connect`, dan dapat menggunakan properti `errCode` untuk mendapatkan kode error, di mana kode error timeout adalah `ETIMEDOUT`
    * Mengembalikan `true` saat berhasil

### checkLiveness()

Memeriksa ketersediaan koneksi melalui panggilan sistem (tidak valid saat terputus secara tidak normal, hanya dapat mendeteksi pemutusan koneksi lawan saat close normal).

!> Tersedia di Swoole versi >= `v4.5.0`

```php
Swoole\Coroutine\Socket->checkLiveness(): bool
```

  * **Nilai Kembali**

    * Mengembalikan `true` saat koneksi aktif, sebaliknya `false`

### send()

Mengirim data ke lawan.

!> Method `send` akan segera menjalankan panggilan sistem `send` untuk mengirim data. Saat panggilan sistem `send` mengembalikan error `EAGAIN`, sistem akan secara otomatis mendengarkan event writable, menangguhkan coroutine saat ini, menunggu event writable terpicu, lalu menjalankan ulang panggilan sistem `send` untuk mengirim data, dan membangunkan coroutine.

!> Jika `send` terlalu cepat dan `recv` terlalu lambat, pada akhirnya buffer sistem operasi akan penuh. Coroutine saat ini akan ditangguhkan di method `send`, dan Anda dapat menyesuaikan buffer, [/proc/sys/net/core/wmem_max dan SO_SNDBUF](https://stackoverflow.com/questions/21856517/whats-the-practical-limit-on-the-size-of-single-packet-transmitted-over-domain)

```php
Swoole\Coroutine\Socket->send(string $data, float $timeout = 0): int|false
```

  * **Parameter**

    * **`string $data`**
      * **Fungsi**: Konten data yang akan dikirim【bisa teks atau data biner】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout
      * **Satuan**: detik【mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Berhasil mengirim mengembalikan jumlah byte yang ditulis, **perhatikan bahwa data yang ditulis mungkin kurang dari panjang parameter `$data`**. Kode lapisan aplikasi perlu membandingkan nilai kembali dengan `strlen($data)` untuk menentukan apakah pengiriman selesai
    * Gagal mengirim mengembalikan `false`, dan mengatur properti `errCode`

### sendAll()

Mengirim data ke lawan. Tidak seperti method `send`, `sendAll` akan berusaha mengirim data selengkap mungkin, mengirim semua data berhasil atau berhenti saat menemui error.

!> Method `sendAll` akan segera menjalankan beberapa panggilan sistem `send` untuk mengirim data. Saat panggilan sistem `send` mengembalikan error `EAGAIN`, sistem akan secara otomatis mendengarkan event writable, menangguhkan coroutine saat ini, menunggu event writable terpicu, lalu menjalankan ulang panggilan sistem `send` untuk mengirim data hingga semua data terkirim atau terjadi error, membangunkan coroutine yang sesuai.

!> Swoole versi >= v4.3.0

```php
Swoole\Coroutine\Socket->sendAll(string $data, float $timeout = 0) : int | false;
```

  * **Parameter**

    * **`string $data`**
      * **Fungsi**: Konten data yang akan dikirim【bisa teks atau data biner】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur nilai timeout
      * **Satuan**: Detik (mendukung float, mis. `1.5` berarti `1s`+`500ms`)
      * **Default**: Lihat [Aturan Timeout Klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * `sendAll` memastikan semua data terkirim berhasil. Namun, selama `sendAll`, lawan mungkin memutuskan koneksi. Pada saat itu, sebagian data mungkin telah berhasil terkirim. Nilai kembali akan menjadi panjang data yang berhasil terkirim. Kode lapisan aplikasi perlu membandingkan apakah nilai kembali sama dengan `strlen($data)` untuk menentukan apakah pengiriman selesai, dan berdasarkan kebutuhan bisnis, memutuskan apakah perlu melanjutkan pengiriman.
    * Gagal mengirim mengembalikan `false`, dan mengatur properti `errCode`

### peek()

Mengintip data di buffer baca, setara dengan `recv(length, MSG_PEEK)` dalam panggilan sistem.

!> `peek` selesai seketika, tidak menangguhkan coroutine, tetapi ada satu overhead panggilan sistem

```php
Swoole\Coroutine\Socket->peek(int $length = 65535): string|false
```

  * **Parameter**

    * **`int $length`**
      * **Fungsi**: Menentukan ukuran memori untuk menyalin data yang diintip (perhatikan: memori akan dialokasikan di sini, panjang yang terlalu besar dapat menyebabkan kehabisan memori)
      * **Satuan**: byte
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Berhasil mengintip mengembalikan data
    * Gagal mengintip mengembalikan `false`, dan mengatur properti `errCode`

### recv()

Menerima data.

!> Method `recv` akan segera menangguhkan coroutine saat ini dan mendengarkan event readable, menunggu lawan mengirim data, saat event readable terpicu, menjalankan panggilan sistem `recv` untuk mengambil data dari buffer socket, dan membangunkan coroutine.

```php
Swoole\Coroutine\Socket->recv(int $length = 65535, float $timeout = 0): string|false
```

  * **Parameter**

    * **`int $length`**
      * **Fungsi**: Menentukan ukuran memori untuk menerima data (perhatikan: memori akan dialokasikan di sini, panjang yang terlalu besar dapat menyebabkan kehabisan memori)
      * **Satuan**: byte
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout
      * **Satuan**: detik【mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Berhasil menerima mengembalikan data aktual
    * Gagal menerima mengembalikan `false`, dan mengatur properti `errCode`
    * Timeout penerimaan, kode error adalah `ETIMEDOUT`

!> Nilai kembali belum tentu sama dengan panjang yang diharapkan, perlu memeriksa sendiri panjang data yang diterima pada pemanggilan ini. Jika perlu memastikan mendapatkan data dengan panjang tertentu dalam satu pemanggilan, gunakan method `recvAll` atau lakukan perulangan sendiri.  
Untuk masalah batasan paket TCP, lihat method `setProtocol()`, atau gunakan `sendto()`.

### recvAll()

Menerima data. Berbeda dengan `recv`, `recvAll` akan berusaha menerima data dengan panjang respons selengkap mungkin, sampai penerimaan selesai atau terjadi error.

!> Method `recvAll` akan segera menangguhkan coroutine saat ini dan mendengarkan event readable. Saat data dikirim dari lawan, saat event readable terpicu, akan menjalankan panggilan sistem `recv` untuk mengambil data dari buffer `socket`, mengulangi perilaku ini sampai menerima data dengan panjang yang ditentukan atau terjadi error, lalu membangunkan coroutine.

!> Swoole versi >= v4.3.0

```php
Swoole\Coroutine\Socket->recvAll(int $length = 65535, float $timeout = 0): string|false
```

  * **Parameter**

    * **`int $length`**
      * **Fungsi**: Ukuran data yang diharapkan akan diterima (perhatikan: memori dialokasikan di sini, panjang yang berlebihan dapat menyebabkan kehabisan memori)
      * **Satuan**: byte
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout
      * **Satuan**: detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Jika berhasil diterima, mengembalikan data aktual, dan panjang string yang dikembalikan sesuai dengan panjang parameter
    * Jika gagal diterima, mengembalikan `false` dan mengatur properti `errCode`
    * Jika timeout, kode error adalah `ETIMEDOUT`

### readVector()

Menerima data dalam segmen.

!> Method `readVector` akan segera menjalankan panggilan sistem `readv` untuk membaca data. Saat panggilan sistem `readv` mengembalikan error `EAGAIN`, sistem akan secara otomatis mendengarkan event readable, menangguhkan coroutine saat ini, menunggu event readable terpicu, lalu menjalankan ulang panggilan sistem `readv` untuk membaca data, dan membangunkan coroutine.

!> Swoole versi >= v4.5.7

```php
Swoole\Coroutine\Socket->readVector(array $io_vector, float $timeout = 0): array|false
```

  * **Parameter**

    * **`array $io_vector`**
      * **Fungsi**: Ukuran data segmen yang diharapkan akan diterima
      * **Satuan**: Byte
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur waktu timeout
      * **Satuan**: Detik【mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Data segmen berhasil diterima
    * Mengembalikan array kosong saat gagal menerima, dan mengatur properti `errCode`
    * Timeout, kode error adalah `ETIMEDOUT`

  * **Contoh**

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// Jika lawan mengirim helloworld
$ret = $socket->readVector([5, 5]);
// Maka, $ret adalah ['hello', 'world']
```

### readVectorAll()

Menerima data dalam segmen.

!> Method `readVectorAll` akan segera menjalankan beberapa panggilan sistem `readv` untuk membaca data. Saat panggilan sistem `readv` mengembalikan error `EAGAIN`, sistem akan secara otomatis mendengarkan event readable, menangguhkan coroutine saat ini, menunggu event readable terpicu, lalu menjalankan ulang panggilan sistem `readv` untuk membaca data sampai data selesai dibaca atau terjadi error, membangunkan coroutine yang sesuai.

!> Swoole versi >= v4.5.7

```php
Swoole\Coroutine\Socket->readVectorAll(array $io_vector, float $timeout = 0): array|false
```

  * **Parameter**

    * **`array $io_vector`**
      * **Fungsi**: Ukuran data segmen yang diharapkan akan diterima
      * **Satuan**: Byte
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur timeout
      * **Satuan**: Detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Jika berhasil, mengembalikan data segmen
    * Jika gagal menerima, mengembalikan array kosong dan mengatur properti `errCode`
    * Jika timeout terjadi, kode error adalah `ETIMEDOUT`

### writeVector()

Mengirim data dalam segmen.

!> Method `writeVector` akan segera menjalankan panggilan sistem `writev` untuk mengirim data. Saat panggilan sistem `writev` mengembalikan error `EAGAIN`, sistem akan secara otomatis mendengarkan event writable, menangguhkan coroutine saat ini, menunggu event writable terpicu, lalu menjalankan ulang panggilan sistem `writev` untuk mengirim data, dan membangunkan coroutine.

!> Swoole versi >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVector(array $io_vector, float $timeout = 0): int|false
```

  * **Parameter**

    * **`array $io_vector`**
      * **Fungsi**: Data segmen yang akan dikirim
      * **Satuan**: byte
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur timeout
      * **Satuan**: detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Jika pengiriman berhasil, mengembalikan jumlah byte yang ditulis. **Perhatikan bahwa data yang ditulis mungkin kurang dari total panjang parameter `$io_vector`**. Kode lapisan aplikasi perlu membandingkan apakah nilai kembali sama dengan total panjang parameter `$io_vector` untuk menentukan apakah pengiriman selesai.
    * Jika pengiriman gagal, mengembalikan `false` dan mengatur properti `errCode`.

  * **Contoh**

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// Ini akan mengirim 'helloworld' ke lawan sesuai urutan dalam array
$socket->writeVector(['hello', 'world']);
```

### writeVectorAll()

Mengirim data ke lawan. Berbeda dengan method `writeVector`, `writeVectorAll` akan berusaha mengirim data selengkap mungkin, baik berhasil mengirim semua data atau berhenti saat menemui error.

!> Method `writeVectorAll` akan segera menjalankan beberapa panggilan sistem `writev` untuk mengirim data. Saat panggilan sistem `writev` mengembalikan error `EAGAIN`, sistem akan secara otomatis mendengarkan event writable, menangguhkan coroutine saat ini, menunggu event writable terpicu, lalu menjalankan ulang panggilan sistem `writev` untuk mengirim data sampai semua data terkirim atau terjadi error, membangunkan coroutine yang sesuai.

!> Swoole versi >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVectorAll(array $io_vector, float $timeout = 0): int|false
```

  * **Parameter**

    * **`array $io_vector`**
      * **Fungsi**: Segmen data yang akan dikirim
      * **Satuan**: Byte
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Mengatur timeout
      * **Satuan**: Detik【mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [Aturan Timeout Klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * `writeVectorAll` memastikan semua data terkirim berhasil. Namun, koneksi mungkin ditutup oleh lawan selama `writeVectorAll`. Dalam kasus ini, sebagian data mungkin telah berhasil terkirim. Nilai kembali akan menunjukkan panjang data yang berhasil ini. Kode lapisan aplikasi perlu membandingkan nilai kembali ini dengan total panjang parameter `$io_vector` untuk menentukan apakah pengiriman selesai, dan memutuskan apakah perlu melanjutkan berdasarkan kebutuhan bisnis.
    * Mengembalikan `false` untuk kegagalan pengiriman, dan mengatur properti `errCode`.

  * **Contoh**

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// Ini akan mengirim 'helloworld' ke lawan sesuai urutan dalam array
$socket->writeVectorAll(['hello', 'world']);
```

### recvPacket()

Untuk objek Socket yang telah mengatur protokol melalui method `setProtocol`, Anda dapat memanggil method ini untuk menerima paket data protokol yang lengkap.

!> Swoole versi >= v4.4.0

```php
Swoole\Coroutine\Socket->recvPacket(float $timeout = 0): string|false
```

  * **Parameter**
    * **`float $timeout`**
      * **Fungsi**: mengatur waktu timeout
      * **Satuan**: detik【mendukung float, mis. `1.5` berarti `1s`+`500ms`】
      * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Berhasil menerima mengembalikan paket data protokol lengkap
    * Gagal menerima mengembalikan `false`, dan mengatur properti `errCode`
    * Timeout penerimaan, kode error adalah `ETIMEDOUT`

### recvLine()

Digunakan untuk mengatasi masalah kompatibilitas [socket_read](https://www.php.net/manual/en/function.socket-read.php)

```php
Swoole\Coroutine\Socket->recvLine(int $length = 65535, float $timeout = 0): string|false
```

### recvWithBuffer()

Digunakan untuk mengatasi masalah pembuatan banyak panggilan sistem saat menerima byte demi byte dengan `recv(1)`

```php
Swoole\Coroutine\Socket->recvWithBuffer(int $length = 65535, float $timeout = 0): string|false
```

### recvfrom()

Menerima data, dan mengatur alamat dan port host sumber. Digunakan untuk `socket` tipe `SOCK_DGRAM`.

!> Method ini akan menyebabkan [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine), sistem akan segera menangguhkan coroutine saat ini dan mendengarkan event readable. Saat event readable terpicu, data diterima dan panggilan sistem `recvfrom` dijalankan untuk mendapatkan paket data.

```php
Swoole\Coroutine\Socket->recvfrom(array &$peer, float $timeout = 0): string|false
```

* **Parameter**

    * **`array $peer`**
        * **Fungsi**: Alamat dan port lawan, tipe referensi.【Diatur menjadi array yang berisi `address` dan `port` saat fungsi berhasil kembali】
        * **Default**: Tidak ada
        * **Nilai lain**: Tidak ada

    * **`float $timeout`**
        * **Fungsi**: Mengatur timeout.【Jika tidak ada data yang kembali dalam waktu yang ditentukan, method `recvfrom` akan mengembalikan `false`】
        * **Satuan**: Detik【Mendukung float, mis. `1.5` berarti `1s`+`500ms`】
        * **Default**: Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
        * **Nilai lain**: Tidak ada

* **Nilai Kembali**

    * Jika data berhasil diterima, mengembalikan konten data dan mengatur `$peer` sebagai array
    * Jika gagal, mengembalikan `false`, mengatur properti `errCode`, dan tidak mengubah konten `$peer`

* **Contoh**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    while (true) {
        $peer = null;
        $data = $socket->recvfrom($peer);
        echo "[Server] recvfrom[{$peer['address']}:{$peer['port']}] : $data\n";
        $socket->sendto($peer['address'], $peer['port'], "Swoole: $data");
    }
});
```

### sendto()

Mengirim data ke alamat dan port yang ditentukan. Digunakan untuk `socket` tipe `SOCK_DGRAM`.

!> Method ini tidak memiliki [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine), sistem akan segera memanggil `sendto` untuk mengirim data ke host target. Method ini tidak mendengarkan ketersediaan tulis. `sendto` mungkin mengembalikan `false` karena buffer penuh, jadi perlu ditangani sendiri atau gunakan method `send`.

```php
Swoole\Coroutine\Socket->sendto(string $address, int $port, string $data): int|false
```

  * **Parameter**

    * **`string $address`**
      * **Fungsi**: Alamat `IP` host target atau path [unixSocket](/learn?id=apa-itu-ipc)【`sendto` tidak mendukung nama domain. Saat menggunakan `AF_INET` atau `AF_INET6`, alamat IP yang valid harus diberikan, jika tidak, pengiriman akan gagal】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`int $port`**
      * **Fungsi**: Port host target【Atur ke `0` saat mengirim siaran】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $data`**
      * **Fungsi**: Data yang akan dikirim【Bisa teks atau konten biner. Perhatikan bahwa panjang maksimum paket yang dikirim untuk `SOCK_DGRAM` adalah `64K`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

    * Berhasil mengirim mengembalikan jumlah byte yang dikirim
    * Gagal mengirim mengembalikan `false`, dan mengatur properti `errCode`

  * **Contoh**

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
$socket->sendto('127.0.0.1', 9601, 'Hello');
```

### getsockname()

Mendapatkan informasi alamat dan port socket.

!> Method ini tidak memiliki overhead [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine).

```php
Swoole\Coroutine\Socket->getsockname(): array|false
```

  * **Nilai Kembali**

    * Berhasil mengembalikan array yang berisi `address` dan `port`
    * Gagal mengembalikan `false`, dan mengatur properti `errCode`

### getpeername()

Mendapatkan informasi alamat dan port lawan dari `socket`, hanya untuk `socket` tipe `SOCK_STREAM` yang memiliki koneksi.

?> Method ini tidak memiliki overhead [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine).

```php
Swoole\Coroutine\Socket->getpeername(): array|false
```

  * **Nilai Kembali**

    * Berhasil mengembalikan array yang berisi `address` dan `port`
    * Gagal mengembalikan `false`, dengan properti `errCode` diatur

### close()

Menutup `Socket`.

!> Saat objek `Swoole\Coroutine\Socket` di-destruksi, method `close` akan otomatis dijalankan tanpa overhead [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine).

```php
Swoole\Coroutine\Socket->close(): bool
```

  * **Nilai Kembali**

    * Berhasil menutup mengembalikan `true`
    * Gagal mengembalikan `false`

### isClosed()

Apakah `Socket` sudah ditutup.

```php
Swoole\Coroutine\Socket->isClosed(): bool
```

## Konstanta

Setara dengan konstanta yang disediakan oleh ekstensi `sockets`, dan tidak akan bertentangan dengan ekstensi `sockets`.

!> Nilai mungkin berbeda di sistem yang berbeda. Kode berikut hanya untuk ilustrasi, jangan gunakan nilai-nilai ini.

```php
define ('AF_UNIX', 1);
define ('AF_INET', 2);

/**
 * Only available if compiled with IPv6 support.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('AF_INET6', 10);
define ('SOCK_STREAM', 1);
define ('SOCK_DGRAM', 2);
define ('SOCK_RAW', 3);
define ('SOCK_SEQPACKET', 5);
define ('SOCK_RDM', 4);
define ('MSG_OOB', 1);
define ('MSG_WAITALL', 256);
define ('MSG_CTRUNC', 8);
define ('MSG_TRUNC', 32);
define ('MSG_PEEK', 2);
define ('MSG_DONTROUTE', 4);

/**
 * Not available on Windows platforms.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOR', 128);

/**
 * Not available on Windows platforms.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOF', 512);
define ('MSG_CONFIRM', 2048);
define ('MSG_ERRQUEUE', 8192);
define ('MSG_NOSIGNAL', 16384);
define ('MSG_DONTWAIT', 64);
define ('MSG_MORE', 32768);
define ('MSG_WAITFORONE', 65536);
define ('MSG_CMSG_CLOEXEC', 1073741824);
define ('SO_DEBUG', 1);
define ('SO_REUSEADDR', 2);

/**
 * This constant is only available in PHP 5.4.10 or later on platforms that
 * support the <b>SO_REUSEPORT</b> socket option: this
 * includes Mac OS X and FreeBSD, but does not include Linux or Windows.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SO_REUSEPORT', 15);
define ('SO_KEEPALIVE', 9);
define ('SO_DONTROUTE', 5);
define ('SO_LINGER', 13);
define ('SO_BROADCAST', 6);
define ('SO_OOBINLINE', 10);
define ('SO_SNDBUF', 7);
define ('SO_RCVBUF', 8);
define ('SO_SNDLOWAT', 19);
define ('SO_RCVLOWAT', 18);
define ('SO_SNDTIMEO', 21);
define ('SO_RCVTIMEO', 20);
define ('SO_TYPE', 3);
define ('SO_ERROR', 4);
define ('SO_BINDTODEVICE', 25);
define ('SOL_SOCKET', 1);
define ('SOMAXCONN', 128);

/**
 * Used to disable Nagle TCP algorithm.
 * Added in PHP 5.2.7.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('TCP_NODELAY', 1);
define ('PHP_NORMAL_READ', 1);
define ('PHP_BINARY_READ', 2);
define ('MCAST_JOIN_GROUP', 42);
define ('MCAST_LEAVE_GROUP', 45);
define ('MCAST_BLOCK_SOURCE', 43);
define ('MCAST_UNBLOCK_SOURCE', 44);
define ('MCAST_JOIN_SOURCE_GROUP', 46);
define ('MCAST_LEAVE_SOURCE_GROUP', 47);
define ('IP_MULTICAST_IF', 32);
define ('IP_MULTICAST_TTL', 33);
define ('IP_MULTICAST_LOOP', 34);
define ('IPV6_MULTICAST_IF', 17);
define ('IPV6_MULTICAST_HOPS', 18);
define ('IPV6_MULTICAST_LOOP', 19);
define ('IPV6_V6ONLY', 27);

/**
 * Operation not permitted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPERM', 1);

/**
 * No such file or directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOENT', 2);

/**
 * Interrupted system call.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINTR', 4);

/**
 * I/O error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIO', 5);

/**
 * No such device or address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENXIO', 6);

/**
 * Arg list too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_E2BIG', 7);

/**
 * Bad file number.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADF', 9);

/**
 * Try again.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAGAIN', 11);

/**
 * Out of memory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEM', 12);

/**
 * Permission denied.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EACCES', 13);

/**
 * Bad address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EFAULT', 14);

/**
 * Block device required.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTBLK', 15);

/**
 * Device or resource busy.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBUSY', 16);

/**
 * File exists.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EEXIST', 17);

/**
 * Cross-device link.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXDEV', 18);

/**
 * No such device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODEV', 19);

/**
 * Not a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTDIR', 20);

/**
 * Is a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISDIR', 21);

/**
 * Invalid argument.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINVAL', 22);

/**
 * File table overflow.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENFILE', 23);

/**
 * Too many open files.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMFILE', 24);

/**
 * Not a typewriter.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTTY', 25);

/**
 * No space left on device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSPC', 28);

/**
 * Illegal seek.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESPIPE', 29);

/**
 * Read-only file system.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EROFS', 30);

/**
 * Too many links.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMLINK', 31);

/**
 * Broken pipe.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPIPE', 32);

/**
 * File name too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENAMETOOLONG', 36);

/**
 * No record locks available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLCK', 37);

/**
 * Function not implemented.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSYS', 38);

/**
 * Directory not empty.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTEMPTY', 39);

/**
 * Too many symbolic links encountered.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELOOP', 40);

/**
 * Operation would block.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EWOULDBLOCK', 11);

/**
 * No message of desired type.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMSG', 42);

/**
 * Identifier removed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIDRM', 43);

/**
 * Channel number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECHRNG', 44);

/**
 * Level 2 not synchronized.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2NSYNC', 45);

/**
 * Level 3 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3HLT', 46);

/**
 * Level 3 reset.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3RST', 47);

/**
 * Link number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELNRNG', 48);

/**
 * Protocol driver not attached.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUNATCH', 49);

/**
 * No CSI structure available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOCSI', 50);

/**
 * Level 2 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2HLT', 51);

/**
 * Invalid exchange.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADE', 52);

/**
 * Invalid request descriptor.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADR', 53);

/**
 * Exchange full.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXFULL', 54);

/**
 * No anode.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOANO', 55);

/**
 * Invalid request code.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADRQC', 56);

/**
 * Invalid slot.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADSLT', 57);

/**
 * Device not a stream.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSTR', 60);

/**
 * No data available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODATA', 61);

/**
 * Timer expired.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIME', 62);

/**
 * Out of streams resources.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSR', 63);

/**
 * Machine is not on the network.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENONET', 64);

/**
 * Object is remote.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMOTE', 66);

/**
 * Link has been severed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLINK', 67);

/**
 * Advertise error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EADV', 68);

/**
 * Srmount error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESRMNT', 69);

/**
 * Communication error on send.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECOMM', 70);

/**
 * Protocol error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTO', 71);

/**
 * Multihop attempted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMULTIHOP', 72);

/**
 * Not a data message.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADMSG', 74);

/**
 * Name not unique on network.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTUNIQ', 76);

/**
 * File descriptor in bad state.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADFD', 77);

/**
 * Remote address changed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMCHG', 78);

/**
 * Interrupted system call should be restarted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ERESTART', 85);

/**
 * Streams pipe error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESTRPIPE', 86);

/**
 * Too many users.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUSERS', 87);

/**
 * Socket operation on non-socket.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTSOCK', 88);

/**
 * Destination address required.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EDESTADDRREQ', 89);

/**
 * Message too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMSGSIZE', 90);

/**
 * Protocol wrong type for socket.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTOTYPE', 91);
define ('SOCKET_ENOPROTOOPT', 92);

/**
 * Protocol not supported.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTONOSUPPORT', 93);

/**
 * Socket type not supported.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESOCKTNOSUPPORT', 94);

/**
 * Operation not supported on transport endpoint.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EOPNOTSUPP', 95);

/**
 * Protocol family not supported.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPFNOSUPPORT', 96);

/**
 * Address family not supported by protocol.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAFNOSUPPORT', 97);
define ('SOCKET_EADDRINUSE', 98);

/**
 * Cannot assign requested address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EADDRNOTAVAIL', 99);

/**
 * Network is down.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENETDOWN', 100);

/**
 * Network is unreachable.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENETUNREACH', 101);

/**
 * Network dropped connection because of reset.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENETRESET', 102);

/**
 * Software caused connection abort.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECONNABORTED', 103);

/**
 * Connection reset by peer.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECONNRESET', 104);

/**
 * No buffer space available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOBUFS', 105);

/**
 * Transport endpoint is already connected.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISCONN', 106);

/**
 * Transport endpoint is not connected.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTCONN', 107);

/**
 * Cannot send after transport endpoint shutdown.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESHUTDOWN', 108);

/**
 * Too many references: cannot splice.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETOOMANYREFS', 109);

/**
 * Connection timed out.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIMEDOUT', 110);

/**
 * Connection refused.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECONNREFUSED', 111);

/**
 * Host is down.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EHOSTDOWN', 112);

/**
 * No route to host.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EHOSTUNREACH', 113);

/**
 * Operation already in progress.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EALREADY', 114);

/**
 * Operation now in progress.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINPROGRESS', 115);

/**
 * Is a named type file.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISNAM', 120);

/**
 * Remote I/O error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMOTEIO', 121);

/**
 * Quota exceeded.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EDQUOT', 122);

/**
 * No medium found.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEDIUM', 123);

/**
 * Wrong medium type.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMEDIUMTYPE', 124);
define ('IPPROTO_IP', 0);
define ('IPPROTO_IPV6', 41);
define ('SOL_TCP', 6);
define ('SOL_UDP', 17);
define ('IPV6_UNICAST_HOPS', 16);
define ('IPV6_RECVPKTINFO', 49);
define ('IPV6_PKTINFO', 50);
define ('IPV6_RECVHOPLIMIT', 51);
define ('IPV6_HOPLIMIT', 52);
define ('IPV6_RECVTCLASS', 66);
define ('IPV6_TCLASS', 67);
define ('SCM_RIGHTS', 1);
define ('SCM_CREDENTIALS', 2);
define ('SO_PASSCRED', 16);
```
