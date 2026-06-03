# Swoole\Client

`Swoole\Client`, selanjutnya disebut `Client`, menyediakan kode enkapsulasi untuk klien `TCP/UDP/UnixSocket`. Cukup gunakan `new Swoole\Client` untuk menggunakannya. Dapat digunakan di lingkungan `FPM/Apache`.  
Dibandingkan dengan fungsi [streams](https://www.php.net/streams) tradisional, ada beberapa keunggulan:

* Fungsi `stream` memiliki waktu tunggu default yang lama, jika respons lawan lama, dapat menyebabkan pemblokiran berkepanjangan
* `fread` pada fungsi `stream` memiliki batas panjang buffer default `8192`, tidak dapat mendukung paket besar `UDP`
* `Client` mendukung `waitall`, bisa mengambil data sekaligus jika panjang paket sudah pasti, tidak perlu membaca berulang kali
* `Client` mendukung `UDP Connect`, mengatasi masalah penggabungan paket `UDP`
* `Client` adalah kode `C` murni yang khusus menangani `socket`, fungsi `stream` sangat kompleks. `Client` memiliki performa lebih baik
* Dapat menggunakan fungsi [swoole_client_select](/client?id=swoole_client_select) untuk mengontrol konkurensi beberapa `Client`

### Contoh Lengkap

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```

## Method

### __construct()

Method konstruktor

```php
Swoole\Client::__construct(int $sock_type, bool $is_sync = false, string $key);
```

* **Parameter** 

  * **`int $sock_type`**
    * **Fungsi**: Menentukan jenis `socket` [mendukung `SWOOLE_SOCK_TCP`, `SWOOLE_SOCK_TCP6`, `SWOOLE_SOCK_UDP`, `SWOOLE_SOCK_UDP6`]. Lihat [bagian ini](/server/methods?id=__construct) untuk penjelasan lebih lanjut
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`bool $is_sync`**
    * **Fungsi**: Mode sinkron blocking, hanya bisa diisi `false`. Untuk mode asynchronous callback, gunakan `Swoole\Async\Client`
    * **Default**: `false`
    * **Nilai Lain**: Tidak ada

  * **`string $key`**
    * **Fungsi**: `Key` untuk koneksi panjang [secara default menggunakan `IP:PORT` sebagai `key`. `key` yang sama, meskipun new dua kali, tetap hanya menggunakan satu koneksi TCP]
    * **Default**: `IP:PORT`
    * **Nilai Lain**: Tidak ada

!> Gunakan makro yang disediakan oleh level bawah untuk menentukan jenis, lihat [definisi konstanta](/consts)

#### Membuat Koneksi Panjang di PHP-FPM/Apache

```php
$cli = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
```

Setelah menambahkan flag [SWOOLE_KEEP](/client?id=swoole_keep), koneksi `TCP` yang dibuat tidak akan ditutup saat request PHP selesai atau saat memanggil `$cli->close()`. Saat berikutnya `connect` dipanggil, akan menggunakan koneksi yang dibuat sebelumnya. Cara penyimpanan koneksi panjang secara default menggunakan `ServerHost:ServerPort` sebagai `key`. Bisa menentukan `key` di parameter ke-`3`.

Destruktor objek `Client` otomatis memanggil method [close](/client?id=close) untuk menutup `socket`

#### Menggunakan Client di Server

* `Client` harus digunakan di [fungsi callback](/server/events) event.
* `Server` dapat terhubung dengan `socket client` dari bahasa pemrograman apa pun. Begitu pula `Client` dapat terhubung ke `socket server` dari bahasa pemrograman apa pun.

!> Menggunakan `Client` ini di lingkungan korutin `Swoole4+` akan menurun ke [model sinkron](/learn?id=同步io异步io).

### set()

Mengatur parameter klien, harus dijalankan sebelum [connect](/client?id=connect).

```php
Swoole\Client->set(array $settings);
```

Lihat [opsi konfigurasi](/client?id=配置) Client untuk opsi yang tersedia.

### connect()

Terhubung ke server jarak jauh.

```php
Swoole\Client->connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
```

* **Parameter** 

  * **`string $host`**
    * **Fungsi**: Alamat server [mendukung resolusi domain asinkron otomatis, `$host` bisa langsung diisi domain]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $port`**
    * **Fungsi**: Port server
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`float $timeout`**
    * **Fungsi**: Mengatur waktu tunggu
    * **Satuan**: Detik [mendukung float, misal `1.5` berarti `1s` + `500ms`]
    * **Default**: `0.5`
    * **Nilai Lain**: Tidak ada

  * **`int $sock_flag`**
    * Untuk tipe `UDP`, menandakan apakah mengaktifkan `udp_connect`. Setelah diaktifkan, `$host` dan `$port` akan diikat, paket dari `host/port` yang tidak ditentukan akan dibuang.
    * Untuk tipe `TCP`, `$sock_flag=1` berarti mengatur `socket` non-blocking, setelah itu fd ini akan menjadi [IO asinkron](/learn?id=同步io异步io), `connect` akan segera kembali. Jika `$sock_flag` diatur ke `1`, maka sebelum `send/recv` harus menggunakan [swoole_client_select](/client?id=swoole_client_select) untuk memeriksa apakah koneksi selesai.

* **Return Value**

  * Mengembalikan `true`
  * Mengembalikan `false`, periksa properti `errCode` untuk penyebab kegagalan

* **Mode Sinkron**

Method `connect` akan memblokir hingga koneksi berhasil dan mengembalikan `true`. Setelah itu bisa mengirim atau menerima data dari server.

```php
if ($cli->connect('127.0.0.1', 9501)) {
      $cli->send("data");
} else {
      echo "connect failed.";
}
```

Jika koneksi gagal, akan mengembalikan `false`

> Klien `TCP` sinkron setelah menjalankan `close`, dapat melakukan `Connect` lagi untuk membuat koneksi baru ke server

* **Koneksi Ulang Saat Gagal**

Jika `connect` gagal dan ingin mencoba koneksi ulang, harus melakukan `close` terlebih dahulu untuk menutup `socket` lama, jika tidak akan mengembalikan error `EINPROCESS` karena `socket` saat ini sedang terhubung ke server, klien tidak tahu apakah koneksi berhasil, sehingga tidak dapat menjalankan `connect` lagi. Memanggil `close` akan menutup `socket` saat ini, dan level bawah akan membuat `socket` baru untuk koneksi.

!> Saat mengaktifkan [SWOOLE_KEEP](/client?id=swoole_keep), parameter pertama dari `close` harus diatur ke `true` untuk memaksa menghancurkan koneksi panjang `socket`

```php
if ($socket->connect('127.0.0.1', 9502) === false) {
    $socket->close(true);
    $socket->connect('127.0.0.1', 9502);
}
```

* **UDP Connect**

Secara default level bawah tidak mengaktifkan `udp connect`. Saat klien `UDP` menjalankan `connect`, level bawah akan segera mengembalikan sukses setelah membuat `socket`. Pada saat ini `socket` terikat ke alamat `0.0.0.0`, lawan mana pun dapat mengirim paket ke port ini.

Misal `$client->connect('192.168.1.100', 9502)`, sistem operasi akan memberikan port acak `58232` untuk klien `socket`. Mesin lain seperti `192.168.1.101` juga dapat mengirim paket ke port ini.

?> Tanpa `udp connect`, `getsockname` akan mengembalikan `host` `0.0.0.0`

Atur parameter ke-`4` menjadi `1` untuk mengaktifkan `udp connect`, `$client->connect('192.168.1.100', 9502, 1, 1)`. Maka klien dan server akan terikat, level bawah akan mengikat `socket` berdasarkan alamat server. Misalnya terhubung ke `192.168.1.100`, `socket` akan terikat ke alamat lokal `192.168.1.*`. Setelah mengaktifkan `udp connect`, klien tidak akan lagi menerima paket dari host lain ke port ini.

### recv()

Menerima data dari server.

```php
Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
```

* **Parameter** 

  * **`int $size`**
    * **Fungsi**: Panjang maksimum buffer data yang diterima [jangan diatur terlalu besar, karena akan memakan banyak memori]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $flags`**
    * **Fungsi**: Parameter tambahan [seperti [Client::MSG_WAITALL](/client?id=clientmsg_waitall)], lihat [bagian ini](/client?id=常量) untuk parameter spesifik
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Berhasil menerima data mengembalikan string
  * Koneksi ditutup mengembalikan string kosong
  * Mengembalikan `false`, dan mengatur properti `$client->errCode`

* **Protokol EOF/Length**

  * Jika klien mengaktifkan deteksi `EOF/Length`, tidak perlu mengatur `$size` dan `$waitall`. Lapisan ekstensi akan mengembalikan paket lengkap atau `false`, lihat bab [Protokol Analisis](/client?id=协议解析).
  * Saat menerima header paket yang salah atau nilai panjang melebihi [package_max_length](/server/setting?id=package_max_length), `recv` akan mengembalikan string kosong, kode PHP harus menutup koneksi ini.

### send()

Mengirim data ke server jarak jauh, harus setelah koneksi dibuat.

```php
Swoole\Client->send(string $data): int|false
```

* **Parameter** 

  * **`string $data`**
    * **Fungsi**: Konten yang dikirim [mendukung data biner]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Berhasil mengirim, mengembalikan panjang data terkirim
  * Mengembalikan `false`, dan mengatur properti `errCode`

* **Catatan**

  * Jika belum menjalankan `connect`, memanggil `send` akan memicu peringatan
  * Data yang dikirim tidak memiliki batasan panjang
  * Jika data terlalu besar dan buffer Socket penuh, program akan memblokir menunggu dapat menulis

### sendfile()

Mengirim file ke server, fungsi ini berdasarkan panggilan sistem operasi `sendfile`

```php
Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

!> sendfile tidak bisa digunakan untuk klien UDP dan koneksi terenkripsi SSL tunnel

* **Parameter** 

  * **`string $filename`**
    * **Fungsi**: Menentukan path file yang akan dikirim
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $offset`**
    * **Fungsi**: Offset upload file [dapat menentukan mulai transmisi data dari bagian tengah file. Fitur ini dapat digunakan untuk mendukung resumable upload]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $length`**
    * **Fungsi**: Ukuran data yang dikirim [default ukuran seluruh file]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Jika file yang dimasukkan tidak ada, akan mengembalikan `false`
  * Eksekusi berhasil mengembalikan `true`

* **Catatan**

  * `sendfile` akan memblokir hingga seluruh file terkirim atau terjadi error fatal

### sendto()

Mengirim paket `UDP` ke host `IP:PORT` mana pun, hanya mendukung tipe `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6`

```php
Swoole\Client->sendto(string $ip, int $port, string $data): bool
```

* **Parameter** 

  * **`string $ip`**
    * **Fungsi**: Alamat `IP` host tujuan, mendukung `IPv4/IPv6`
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $port`**
    * **Fungsi**: Port host tujuan
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`string $data`**
    * **Fungsi**: Data yang akan dikirim [tidak boleh melebihi `64K`]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

### enableSSL()

Mengaktifkan enkripsi tunnel SSL secara dinamis, hanya dapat digunakan jika mengompilasi `swoole` dengan `--enable-openssl`.

```php
Swoole\Client->enableSSL(): bool
```

Klien menggunakan komunikasi teks biasa saat membuat koneksi, dan ingin beralih ke enkripsi `SSL` di tengah jalan, dapat menggunakan method `enableSSL`. Jika sejak awal sudah SSL, lihat [konfigurasi SSL](/client?id=ssl相关). Menggunakan `enableSSL` untuk mengaktifkan enkripsi tunnel `SSL` secara dinamis, perlu memenuhi dua syarat:

* Tipe klien saat dibuat harus non-`SSL`
* Klien sudah terhubung dengan server

Memanggil `enableSSL` akan memblokir menunggu jabat tangan `SSL` selesai.

* **Contoh**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
// Aktifkan enkripsi tunnel SSL
if ($client->enableSSL())
{
    // Jabat tangan selesai, data yang dikirim dan diterima sudah terenkripsi
    $client->send("hello world\n");
    echo $client->recv();
}
$client->close();
```

### getPeerCert()

Mendapatkan informasi sertifikat server, hanya dapat digunakan jika mengompilasi `swoole` dengan `--enable-openssl`.

```php
Swoole\Client->getPeerCert(): string|false
```

* **Return Value**

  * Mengembalikan string informasi sertifikat `X509`
  * Mengembalikan `false`

!> Method ini hanya bisa dipanggil setelah jabat tangan SSL selesai.

Dapat menggunakan fungsi `openssl_x509_parse` dari ekstensi `openssl` untuk mengurai informasi sertifikat.

!> Perlu mengaktifkan [--enable-openssl](/environment?id=编译选项) saat mengompilasi swoole

### verifyPeerCert()

Memverifikasi sertifikat server, hanya dapat digunakan jika mengompilasi `swoole` dengan `--enable-openssl`.

```php
Swoole\Client->verifyPeerCert()
```

### isConnected()

Mengembalikan status koneksi Client

* Mengembalikan false, berarti saat ini tidak terhubung ke server
* Mengembalikan true, berarti saat ini terhubung ke server

```php
Swoole\Client->isConnected(): bool
```

!> Method `isConnected` mengembalikan status lapisan aplikasi, hanya menunjukkan bahwa `Client` telah menjalankan `connect` dan berhasil terhubung ke `Server`, dan belum menjalankan `close` untuk menutup koneksi.  
`Client` dapat menjalankan `send`, `recv`, `close` dll, tetapi tidak dapat menjalankan `connect` lagi. Ini tidak berarti koneksi pasti tersedia, saat menjalankan `send` atau `recv` masih mungkin mengembalikan error, karena lapisan aplikasi tidak bisa mendapatkan status koneksi `TCP` level bawah, hanya saat `send` atau `recv` terjadi interaksi dengan kernel, barulah status ketersediaan koneksi yang sebenarnya didapatkan.

### getSockName()

Digunakan untuk mendapatkan host:port lokal socket klien.

!> Hanya dapat digunakan setelah koneksi

```php
Swoole\Client->getsockname(): array|false
```

* **Return Value**

```php
array('host' => '127.0.0.1', 'port' => 53652);
```

### getPeerName()

Mendapatkan alamat IP dan port socket lawan.

!> Hanya mendukung tipe `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM`

```php
Swoole\Client->getpeername(): array|false
```

Setelah klien protokol `UDP` mengirim paket ke server, mungkin bukan server tersebut yang merespons. Dapat menggunakan method `getpeername` untuk mendapatkan `IP:PORT` server yang sebenarnya merespons.

!> Fungsi ini harus dipanggil setelah `$client->recv()`

### close()

Menutup koneksi.

```php
Swoole\Client->close(bool $force = false): bool
```

* **Parameter** 

  * **`bool $force`**
    * **Fungsi**: Memaksa menutup koneksi [dapat digunakan untuk menutup koneksi panjang [SWOOLE_KEEP](/client?id=swoole_keep)]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

Setelah koneksi `swoole_client` di-`close`, jangan melakukan `connect` lagi. Cara yang benar adalah menghancurkan `Client` saat ini, membuat `Client` baru, dan membuat koneksi baru.

Objek `Client` akan otomatis `close` saat di-destruksi.

### shutdown()

Menutup klien

```php
Swoole\Client->shutdown(int $how): bool
```

* **Parameter** 

  * **`int $how`**
    * **Fungsi**: Mengatur cara menutup klien
    * **Default**: Tidak ada
    * **Nilai Lain**: Swoole\Client::SHUT_RDWR (tutup baca dan tulis), SHUT_RD (tutup baca), Swoole\Client::SHUT_WR (tutup tulis)

### getSocket()

Mendapatkan handle `socket` level bawah, objek yang dikembalikan adalah handle resource `sockets`.

!> Method ini membutuhkan ekstensi `sockets`, dan perlu mengaktifkan opsi [--enable-sockets](/environment?id=编译选项) saat kompilasi

```php
Swoole\Client->getSocket()
```

Dapat menggunakan fungsi `socket_set_option` untuk mengatur parameter `socket` yang lebih rendah.

```php
$socket = $client->getSocket();
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
}
```

### swoole_client_select

Swoole\Client menggunakan panggilan sistem `select` untuk [event loop IO](/learn?id=什么是eventloop) dalam pemrosesan paralel, bukan epoll_wait. Berbeda dengan [Event module](/event), fungsi ini digunakan di lingkungan IO sinkron (jika dipanggil di proses Worker Swoole, akan menyebabkan epoll [event loop IO](/learn?id=什么是eventloop) Swoole sendiri tidak mendapat kesempatan untuk dijalankan).

Prototipe fungsi:

```php
int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);
```

* `swoole_client_select` menerima 4 parameter, `$read`, `$write`, `$error` masing-masing adalah deskriptor file yang dapat dibaca/ditulis/error.  
* 3 parameter ini harus berupa referensi variabel array. Elemen array harus berupa objek `swoole_client`.
* Method ini berdasarkan panggilan sistem `select`, maksimal mendukung `1024` `socket`
* Parameter `$timeout` adalah waktu tunggu panggilan sistem `select`, satuan detik, menerima float
* Fungsinya mirip dengan `stream_select()` asli PHP, bedanya stream_select hanya mendukung tipe variabel stream PHP, dan performanya buruk.

Setelah berhasil dipanggil, akan mengembalikan jumlah event, dan memodifikasi array `$read`/`$write`/`$error`. Gunakan foreach untuk melintasi array, lalu jalankan `$item->recv`/`$item->send` untuk mengirim/menerima data. Atau panggil `$item->close()` atau `unset($item)` untuk menutup `socket`.

`swoole_client_select` mengembalikan `0` berarti dalam waktu yang ditentukan, tidak ada IO yang tersedia, panggilan `select` telah timeout.

!> Fungsi ini dapat digunakan di lingkungan `Apache/PHP-FPM`    

```php
$clients = array();

for($i=0; $i< 20; $i++)
{
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); // sinkron blocking
    $ret = $client->connect('127.0.0.1', 9501, 0.5, 0);
    if(!$ret)
    {
        echo "Connect Server fail.errCode=".$client->errCode;
    }
    else
    {
    	$client->send("HELLO WORLD\n");
    	$clients[$client->sock] = $client;
    }
}

while (!empty($clients))
{
    $write = $error = array();
    $read = array_values($clients);
    $n = swoole_client_select($read, $write, $error, 0.6);
    if ($n > 0)
    {
        foreach ($read as $index => $c)
        {
            echo "Recv #{$c->sock}: " . $c->recv() . "\n";
            unset($clients[$c->sock]);
        }
    }
}
```

## Properti

### errCode

Kode error

```php
Swoole\Client->errCode: int
```

Saat `connect/send/recv/close` gagal, nilai `$swoole_client->errCode` akan diatur secara otomatis.

Nilai `errCode` sama dengan `Linux errno`. Dapat menggunakan `socket_strerror` untuk mengubah kode error menjadi pesan error.

```php
echo socket_strerror($client->errCode);
```

Referensi: [Daftar Kode Error Linux](/other/errno?id=linux)

### sock

Deskriptor file koneksi socket.

```php
Swoole\Client->sock;
```

Dalam kode PHP dapat menggunakan

```php
$sock = fopen("php://fd/".$swoole_client->sock); 
```

* Mengubah `socket` dari `Swoole\Client` menjadi `stream socket`. Dapat memanggil fungsi `fread/fwrite/fclose` dll.

* `$fd` di [Swoole\Server](/server/methods?id=__construct) tidak bisa menggunakan method ini, karena `$fd` hanya angka, deskriptor file `$fd` milik proses utama, lihat mode [SWOOLE_PROCESS](/learn?id=swoole_process).

* `$swoole_client->sock` dapat diubah menjadi int sebagai `key` array.

!> Perlu diperhatikan: nilai properti `$swoole_client->sock` hanya bisa didapatkan setelah `$swoole_client->connect`. Sebelum terhubung ke server, nilai properti ini adalah `null`.

### reuse

Menunjukkan apakah koneksi ini baru dibuat atau menggunakan kembali yang sudah ada. Digunakan bersama [SWOOLE_KEEP](/client?id=swoole_keep).

#### Skenario Penggunaan

Setelah klien `WebSocket` terhubung ke server, perlu melakukan jabat tangan. Jika koneksi menggunakan kembali, tidak perlu jabat tangan lagi, langsung kirim frame data `WebSocket`.

```php
if ($client->reuse) {
    $client->send($data);
} else {
    $client->doHandShake();
    $client->send($data);
}
```

### reuseCount

Menunjukkan berapa kali koneksi ini digunakan kembali. Digunakan bersama [SWOOLE_KEEP](/client?id=swoole_keep).

```php
Swoole\Client->reuseCount;
```

### type

Menunjukkan jenis `socket`, akan mengembalikan nilai `$sock_type` dari `Swoole\Client::__construct()`

```php
Swoole\Client->type;
```

### id

Mengembalikan nilai `$key` dari `Swoole\Client::__construct()`, digunakan bersama [SWOOLE_KEEP](/client?id=swoole_keep)

```php
Swoole\Client->id;
```

### setting

Mengembalikan konfigurasi yang diatur oleh `Swoole\Client::set()`

```php
Swoole\Client->setting;
```

## Konstanta

### SWOOLE_KEEP

Swoole\Client mendukung pembuatan koneksi TCP panjang ke server di `PHP-FPM/Apache`. Cara penggunaan:

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
```

Setelah mengaktifkan opsi `SWOOLE_KEEP`, request selesai tidak akan menutup `socket`, saat `connect` berikutnya akan otomatis menggunakan koneksi yang dibuat sebelumnya. Jika `connect` mendeteksi koneksi sudah ditutup server, maka `connect` akan membuat koneksi baru.

?> Keunggulan SWOOLE_KEEP

* Koneksi panjang `TCP` dapat mengurangi konsumsi IO tambahan dari `connect` 3-way handshake / `close` 4-way handshake
* Mengurangi jumlah `close`/`connect` di sisi server

### Swoole\Client::MSG_WAITALL

* Jika menetapkan parameter Client::MSG_WAITALL, harus menentukan `$size` yang akurat, jika tidak akan menunggu terus sampai panjang data yang diterima mencapai $size
* Tanpa Client::MSG_WAITALL, `$size` maksimal `64K`
* Jika `$size` salah, akan menyebabkan `recv` timeout, mengembalikan `false`

### Swoole\Client::MSG_DONTWAIT

Menerima data secara non-blocking, akan segera kembali baik ada data maupun tidak.

### Swoole\Client::MSG_PEEK

Mengintip data di buffer `socket`. Dengan parameter `MSG_PEEK`, `recv` membaca data tidak akan mengubah pointer, sehingga panggilan `recv` berikutnya masih akan mengembalikan data dari posisi sebelumnya.

### Swoole\Client::MSG_OOB

Membaca data out-of-band, silakan cari "`TCP out-of-band data`".

### Swoole\Client::SHUT_RDWR

Menutup sisi baca dan tulis klien.

### Swoole\Client::SHUT_RD

Menutup sisi baca klien.

### Swoole\Client::SHUT_WR

Menutup sisi tulis klien.

## Konfigurasi

`Client` dapat menggunakan method `set` untuk mengatur beberapa opsi, mengaktifkan fitur tertentu.

### Analisis Protokol

?> Analisis protokol untuk mengatasi [masalah batas paket TCP](/learn?id=tcp数据包边界问题), arti konfigurasi terkait sama dengan `Swoole\Server`. Detail lihat bab konfigurasi [Protokol Swoole\Server](/server/setting?id=open_eof_check).

* **Deteksi EOF**

```php
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n\r\n",
    'package_max_length' => 1024 * 1024 * 2,
));
```

* **Deteksi Panjang**

```php
$client->set(array(
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0, // Byte ke-N adalah nilai panjang paket
    'package_body_offset' => 4, // Mulai hitung panjang dari byte ke berapa
    'package_max_length' => 2000000, // Panjang maksimum protokol
));
```

!> Saat ini mendukung [open_length_check](/server/setting?id=open_length_check) dan [open_eof_check](/server/setting?id=open_eof_check) 2 fungsi pemrosesan protokol otomatis;  
Setelah mengonfigurasi analisis protokol, method `recv()` klien tidak akan menerima parameter panjang, dan akan selalu mengembalikan paket data lengkap.

* **Protokol MQTT**

!> Mengaktifkan analisis protokol `MQTT`, callback [onReceive](/server/events?id=onreceive) akan menerima paket `MQTT` lengkap.

```php
$client->set(array(
    'open_mqtt_protocol' => true,
));
```

* **Ukuran Buffer Socket**	

!> Termasuk buffer OS level bawah `socket`, buffer memori penerima data lapisan aplikasi, buffer memori pengirim data lapisan aplikasi.	

```php	
$client->set(array(	
    'socket_buffer_size' => 1024 * 1024 * 2, // buffer 2M	
));	
```

* **Nonaktifkan Algoritma Nagle**

```php
$client->set(array(
    'open_tcp_nodelay' => true,
));
```

### SSL

* **Konfigurasi Sertifikat SSL/TLS**

```php
$client->set(array(
    'ssl_cert_file' => $your_ssl_cert_file_path,
    'ssl_key_file' => $your_ssl_key_file_path,
));
```

* **ssl_verify_peer**

Memverifikasi sertifikat server.

```php
$client->set([
    'ssl_verify_peer' => true,
]);
```

Setelah diaktifkan akan memverifikasi apakah sertifikat cocok dengan domain host, jika tidak, koneksi akan otomatis ditutup

* **Sertifikat Self-Signed**

Atur `ssl_allow_self_signed` ke `true` untuk mengizinkan sertifikat self-signed.

```php
$client->set([
    'ssl_verify_peer' => true,
    'ssl_allow_self_signed' => true,
]);
```

* **ssl_host_name**

Mengatur nama host server, digunakan bersama konfigurasi `ssl_verify_peer` atau [Client::verifyPeerCert](/client?id=verifypeercert).

```php
$client->set([
    'ssl_host_name' => 'www.google.com',
]);
```

* **ssl_cafile**

Saat `ssl_verify_peer` diatur ke `true`, digunakan untuk memverifikasi sertifikat `CA` yang digunakan untuk sertifikat jarak jauh. Nilai opsi ini adalah path lengkap dan nama file sertifikat `CA` di sistem file lokal.

```php
$client->set([
    'ssl_cafile' => '/etc/CA',
]);
```

* **ssl_capath**

Jika `ssl_cafile` tidak diatur, atau file yang ditunjuk `ssl_cafile` tidak ada, maka akan mencari sertifikat yang sesuai di direktori yang ditentukan `ssl_capath`. Direktori ini harus merupakan direktori sertifikat yang sudah di-hash.

```php
$client->set([
    'ssl_capath' => '/etc/capath/',
])
```

* **ssl_passphrase**

Password file sertifikat lokal [ssl_cert_file](/server/setting?id=ssl_cert_file).

* **Contoh**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_SSL);

$client->set(array(
    'ssl_cert_file' => __DIR__.'/ca/client-cert.pem',
    'ssl_key_file' => __DIR__.'/ca/client-key.pem',
    'ssl_allow_self_signed' => true,
    'ssl_verify_peer' => true,
    'ssl_cafile' => __DIR__.'/ca/ca-cert.pem',
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
echo "connect ok\n";
$client->send("hello world-" . str_repeat('A', $i) . "\n");
echo $client->recv();
```

### package_length_func

Mengatur fungsi kalkulasi panjang, cara penggunaan sama persis dengan [package_length_func](/server/setting?id=package_length_func) di `Swoole\Server`. Digunakan bersama [open_length_check](/server/setting?id=open_length_check). Fungsi panjang harus mengembalikan integer.

* Mengembalikan `0`, data tidak cukup, perlu menerima lebih banyak data
* Mengembalikan `-1`, data error, level bawah akan otomatis menutup koneksi
* Mengembalikan nilai panjang total paket (termasuk panjang total header dan body paket), level bawah akan otomatis menggabungkan paket dan mengembalikannya ke fungsi callback

Secara default level bawah maksimal membaca data `8K`, jika panjang header kecil mungkin ada konsumsi salinan memori. Atur parameter `package_body_offset`, level bawah hanya membaca header untuk analisis panjang.

* **Contoh**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check' => true,
    'package_length_func' => function ($data) {
        if (strlen($data) < 8) {
            return 0;
        }
        $length = intval(trim(substr($data, 0, 8)));
        if ($length <= 0) {
            return -1;
        }
        return $length + 8;
    },
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```

### socks5_proxy

Konfigurasi proxy socks5.

!> Mengatur hanya satu opsi tidak valid, setiap kali harus mengatur `host` dan `port`; `socks5_username`, `socks5_password` adalah parameter opsional. `socks5_port`, `socks5_password` tidak boleh `null`.

```php
$client->set(array(
    'socks5_host' => '192.168.1.100',
    'socks5_port' => 1080,
    'socks5_username' => 'username',
    'socks5_password' => 'password',
));
```

### http_proxy

Konfigurasi proxy HTTP.

!> `http_proxy_port`, `http_proxy_password` tidak boleh `null`.

* **Pengaturan Dasar**

```php
$client->set(array(
    'http_proxy_host' => '192.168.1.100',
    'http_proxy_port' => 1080,
));
```

* **Pengaturan Autentikasi**

```php
$client->set(array(
    'http_proxy_user' => 'test',
    'http_proxy_password' => 'test_123456',
));
```

### bind

!> Hanya mengatur bind_port tidak valid, atur bind_port dan bind_address secara bersamaan

?> Jika mesin memiliki beberapa kartu jaringan, mengatur parameter `bind_address` dapat memaksa `Socket` klien terikat ke alamat jaringan tertentu.  
Mengatur `bind_port` memungkinkan `Socket` klien menggunakan port tetap untuk terhubung ke server luar.

```php
$client->set(array(
    'bind_address' => '192.168.1.100',
    'bind_port' => 36002,
));
```

### Lingkup

Item konfigurasi `Client` di atas juga berlaku untuk klien berikut:

* [Swoole\Coroutine\Client](/coroutine_client/client)
* [Swoole\Coroutine\Http\Client](/coroutine_client/http_client)
* [Swoole\Coroutine\Http2\Client](/coroutine_client/http2_client)
