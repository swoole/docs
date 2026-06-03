# Memasang Swoole

Ekstensi `Swoole` dibuat mengikuti standar ekstensi `PHP` pada umumnya. Gunakan `phpize` untuk menghasilkan skrip deteksi kompilasi, `./configure` untuk deteksi konfigurasi kompilasi, `make` untuk kompilasi, dan `make install` untuk instalasi.

* Kalau gak ada kebutuhan khusus, pastikan kompilasi dan pasang `Swoole` versi terbaru dari [Swoole](https://github.com/swoole/swoole-src/releases/).
* Kalau user saat ini bukan `root`, mungkin gak punya izin nulis di direktori instalasi `PHP`, jadi perlu pake `sudo` atau `su` saat instalasi.
* Kalau update kode langsung di branch `git`, sebelum kompilasi ulang wajib jalanin `make clean`.
* Cuma mendukung tiga sistem operasi: `Linux` (kernel 2.3.32 ke atas), `FreeBSD`, dan `MacOS`.
* Sistem Linux versi lawas (kayak `CentOS 6`) bisa pake `devtools` dari `RedHat` untuk kompilasi, [dokumentasi referensi](https://blog.csdn.net/ppdouble/article/details/52894271).
* Di platform `Windows`, bisa pake `WSL (Windows Subsystem for Linux)` atau `CygWin`.
* Beberapa ekstensi gak kompatibel sama ekstensi `Swoole`, lihat [konflik ekstensi](/getting_started/extension).

## Persiapan Instalasi

Sebelum instalasi, pastikan sistem sudah punya software berikut:

- Versi `4.8` butuh `PHP-7.2` atau lebih tinggi
- Versi `5.0` butuh `PHP-8.0` atau lebih tinggi
- Versi `6.0` butuh `PHP-8.1` atau lebih tinggi
- `gcc-4.8` atau lebih tinggi
- `make`
- `autoconf`

## Instalasi Cepat

> 1. Unduh kode sumber swoole

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2. Kompilasi dan instal dari kode sumber

Setelah unduh paket kode sumber, masuk ke direktori kode sumber di terminal, lalu jalankan perintah berikut untuk kompilasi dan instalasi:

!> ubuntu gak ada phpize bisa jalanin: `sudo apt-get install php-dev` buat instal phpize

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3. Aktifkan ekstensi

Setelah kompilasi dan instalasi berhasil, tambahin `extension=swoole.so` di `php.ini` buat mengaktifkan ekstensi Swoole

## Contoh Kompilasi Lengkap Lanjutan

!> Developer yang baru pertama kali kenal Swoole, coba dulu kompilasi simpel di atas. Kalau ada kebutuhan lebih lanjut, sesuaikan parameter kompilasi di contoh bawah sesuai kebutuhan dan versi. [Referensi parameter kompilasi](/environment?id=编译选项)

Skrip di bawah bakal unduh dan kompilasi kode sumber dari branch `master`. Pastikan semua dependensi udah terinstall, kalau gak bakal muncul berbagai error dependensi.

```shell
mkdir -p ~/build && \
cd ~/build && \
rm -rf ./swoole-src && \
curl -o ./tmp/swoole.tar.gz https://github.com/swoole/swoole-src/archive/master.tar.gz -L && \
tar zxvf ./tmp/swoole.tar.gz && \
mv swoole-src* swoole-src && \
cd swoole-src && \
phpize && \
./configure \
--enable-openssl --enable-sockets --enable-mysqlnd --enable-swoole-curl --enable-cares --enable-swoole-pgsql && \
sudo make && sudo make install
```

## PECL

> Catatan: Rilis PECL lebih lambat dari rilis GitHub

Proyek Swoole udah masuk ke library ekstensi resmi PHP. Selain unduh dan kompilasi manual, bisa juga pake perintah `pecl` dari PHP untuk unduh dan instal sekali jalan:

```shell
pecl install swoole
```

Saat instal Swoole lewat PECL, proses instalasi bakal nanya apakah mau aktifin fitur tertentu. Ini juga bisa dikasih sebelum jalanin instalasi, contohnya:

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#atau
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```

## PIE

Proyek Swoole juga mendukung instalasi lewat PIE, tools instalasi ekstensi PHP yang baru.

```shell
pie install swoole/swoole:5.1.5
```

Saat instal Swoole lewat PIE, proses instalasi bakal nanya apakah mau aktifin fitur tertentu. Ini juga bisa dikasih sebelum jalanin instalasi, contohnya:

```shell
pie install swoole/swoole:5.1.5 --enable-socket --enable-swoole-curl
```

## Tambahin Swoole ke php.ini

Terakhir, setelah kompilasi dan instalasi berhasil, ubah `php.ini` dengan nambahin:

```ini
extension=swoole.so
```

Cek apakah `swoole.so` udah ke-load dengan `php -m`. Kalau belum, mungkin path `php.ini`-nya salah.
Bisa pake `php --ini` buat cari path absolut `php.ini`. Bagian `Loaded Configuration File` nunjukin file php.ini yang di-load. Kalau nilainya `none`, berarti gak ada file `php.ini` yang di-load, dan harus bikin sendiri.

!> Dukungan versi `PHP` ngikutin jadwal maintain resmi dari PHP, lihat [Jadwal Dukungan Versi PHP](http://php.net/supported-versions.php)

## Kompilasi di Platform Lain

Platform ARM (Raspberry Pi)

* Pake kompilasi silang `GCC`
* Saat kompilasi `Swoole`, perlu ubah manual `Makefile` dengan hapus parameter kompilasi `-O2`

Platform MIPS (Router OpenWrt)

* Pake kompilasi silang GCC

Windows WSL

`Windows 10` punya dukungan subsistem `Linux`, jadi `Swoole` bisa dipake di lingkungan `BashOnWindows`. Perintah instalasi:

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> Di lingkungan `WSL`, opsi `daemonize` harus dimatiin
Untuk `WSL` versi di bawah `17101`, setelah `configure` perlu ubah `config.h` buat matiin `HAVE_SIGNALFD`

## Docker Image Resmi

- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)

## Opsi Kompilasi

Ini parameter tambahan buat konfigurasi kompilasi `./configure`, dipake buat ngaktifin fitur tertentu.

### Parameter Umum

#### --enable-openssl

Mengaktifkan dukungan `SSL`, parameter ini bakal dihapus setelah versi `6.2` dan jadi wajib, `SSL/TLS` bakal selalu tersedia.

> Pake dynamic library `libssl.so` dari sistem operasi

#### --with-openssl-dir

Mengaktifkan dukungan `SSL` dan specify path library `openssl`, perlu dikasih parameter path, contoh: `--with-openssl-dir=/opt/openssl/`.
Parameter ini masih berlaku setelah versi `6.2`, tapi cuma buat ngubah path default library `openssl`. Kalau gak diset, bakal pake library `openssl` bawaan sistem.

#### --enable-http2

Mengaktifkan dukungan `HTTP2`

> Tergantung library `nghttp2`. Setelah versi `V4.3.0` gak perlu install dependensi lagi, udah built-in. Tapi tetep perlu tambahin parameter kompilasi ini buat aktifin dukungan `http2`. `Swoole5` udah aktifin parameter ini secara default.

#### --enable-swoole-json

Mengaktifkan dukungan buat [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode), mulai `Swoole5` parameter ini udah aktif secara default.

> Tergantung ekstensi `json`, tersedia sejak versi `v4.5.7`

#### --enable-swoole-curl

Mengaktifkan dukungan buat [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl). Pastiin `php` dan `Swoole` pake shared library dan header `libcurl` yang sama, kalo gak bakal muncul masalah yang gak terduga.

> Tersedia sejak versi `v4.6.0`. Kalau kompilasi error `curl/curl.h: No such file or directory`, lihat [masalah instalasi](/question/install?id=libcurl)

#### --enable-cares

Mengaktifkan dukungan `c-ares`

> Tergantung library `c-ares`, tersedia sejak versi `v4.7.0`. Kalau kompilasi error `ares.h: No such file or directory`, lihat [masalah instalasi](/question/install?id=libcares)

#### --with-jemalloc-dir

Mengaktifkan dukungan `jemalloc`

#### --enable-brotli

Mengaktifkan dukungan kompresi `libbrotli`

#### --with-brotli-dir

Mengaktifkan dukungan kompresi `libbrotli` dan specify path library `libbrotli`, perlu dikasih parameter path, contoh: `--with-brotli-dir=/opt/brotli/`

#### --enable-swoole-pgsql

Mengaktifkan coroutine database `PostgreSQL`.

> Sebelum `Swoole5.0` pake coroutine client buat coroutine `PostgreSQL`. Setelah `Swoole5.1`, selain pake coroutine client, bisa juga pake native `pdo_pgsql` buat coroutine `PostgreSQL`.

#### --with-swoole-odbc

Mengaktifkan coroutine buat `pdo_odbc`. Setelah parameter ini diaktifin, semua database yang mendukung interface `odbc` bisa pake coroutine.

> Tersedia sejak versi `v5.1.0`, butuh library `unixodbc-dev`

Contoh konfigurasi:

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

Mengaktifkan coroutine buat `pdo_oci`. Setelah parameter ini diaktifin, semua operasi CRUD di database `oracle` bakal nge-trigger coroutine.

> Tersedia sejak versi `v5.1.0`

#### --enable-swoole-sqlite

Mengaktifkan coroutine buat `pdo_sqlite`. Setelah parameter ini diaktifin, semua operasi CRUD di database `sqlite` bakal nge-trigger coroutine.

> Tersedia sejak versi `v5.1.0`

#### --enable-swoole-thread

Mengaktifkan mode multi-thread `swoole`. Dengan opsi kompilasi ini, `Swoole` bakal berubah dari model multi-process single-thread jadi single-process multi-thread.

> Tersedia sejak versi `v6.0`, dan `PHP` harus dalam mode `ZTS`

#### --enable-iouring

Dengan opsi kompilasi ini, file async processing `swoole` bakal berubah dari simulasi thread pool jadi `iouring`.
`iouring` adalah fitur eksklusif sistem `Linux` yang bisa ningkatin performa `IO` filesystem secara signifikan. Tapi butuh kernel versi tinggi, cek dulu apakah kernel `Linux` yang dipake mendukung `iouring`.

> Tersedia sejak versi `v6.0`, dan butuh library `liburing`. Kalau performa disk udah bagus, performa kedua mode gak jauh beda. Cuma kalo tekanan `I/O` besar, mode `iouring` bakal lebih unggul dari mode async thread.

Kalau pake `iouring` di container `docker` dan muncul error `Create io_uring failed, the error code is 38`, coba solusi berikut:

1. Upgrade kernel container `docker` ke versi `5.1.0` ke atas
2. Pake parameter `--privileged` buat jalanin container
3. Tambahin parameter `--security-opt seccomp:unconfined` pas jalan, biar container `docker` bisa pake fitur `io_uring`

#### --enable-uring-socket
Kalau diaktifin, `io_uring` bakal dipake sebagai ganti `epoll/kqueue` buat handle `socket`, performa konkurensi bakal naik drastis. Mempengaruhi semua implementasi `Swoole\Coroutine\Socket`.

Termasuk:
- `Swoole\Coroutine\Socket`
- `Swoole\Coroutine\Client`
- `Swoole\Coroutine\Server`
- `Swoole\Coroutine\Http\Client`
- `Swoole\Coroutine\Http\Server`
- `Swoole\Coroutine\Http2\Client`
- `PHP Stream Runtime Hook`, termasuk ekstensi `pdo-mysql`, `mysqli`, `redis`

Semua modul di atas pake `uring-socket`, performa konkurensi bakal naik drastis.

Untuk modul server asynchronous kayak `Swoole\Server`, `Swoole\Http\Server`, `Swoole\WebSocket\Server`, `Event`, `Timer`, serta `curl`, `pdo_pgsql` dll, gak berpengaruh dan tetep pake `epoll/kqueue`.

> Tersedia sejak versi `v6.2`, opsi ini tergantung library `liburing`, cuma berlaku kalo `--enable-iouring` diaktifin

#### --enable-zstd

Dengan opsi kompilasi ini, server dan client `http` bisa pake tools kompresi berperforma tinggi `Zstd` buat kompresi respons.

> Tersedia sejak versi `v6.0`, dan butuh library `libzstd`.

### Parameter Khusus

!> **Kalo gak ada alasan historis, gak disarankan diaktifin**

#### --enable-mysqlnd

Mengaktifkan dukungan `mysqlnd` dan method `Coroutine\MySQL::escape`. Dengan parameter ini, `PHP` harus punya modul `mysqlnd`, kalo gak `Swoole` gak bakal bisa jalan.

> Tergantung ekstensi `mysqlnd`

#### --enable-sockets

Nambahin dukungan buat resource `sockets` di PHP. Dengan parameter ini, [Swoole\Event::add](/event?id=add) bisa nambahin koneksi dari ekstensi `sockets` ke [event loop](/learn?id=什么是eventloop) Swoole.
Method [getSocket()](/server/methods?id=getsocket) di `Server` dan `Client` juga tergantung parameter kompilasi ini.

> Tergantung ekstensi `sockets`. Setelah versi `v4.3.2`, peran parameter ini berkurang karena [Coroutine\Socket](/coroutine_client/socket) bawaan Swoole udah bisa ngelakuin sebagian besar tugas.

### Parameter Debug

!> **Jangan diaktifin di production**

#### --enable-debug

Mengaktifkan mode debug. Pake `gdb` buat tracing perlu nambahin parameter ini pas kompilasi `Swoole`.

#### --enable-debug-log

Mengaktifkan log DEBUG kernel. **(Swoole versi >= 4.2.0)**

#### --enable-trace-log

Mengaktifkan trace log. Dengan opsi ini, swoole bakal nge-print berbagai log debug detail. Cuma dipake pas pengembangan kernel.

#### --enable-swoole-coro-time

Mengaktifkan kalkulasi waktu jalan coroutine. Dengan opsi ini, bisa pake Swoole\Coroutine::getExecuteTime() buat ngitung waktu eksekusi coroutine, gak termasuk waktu tunggu I/O.

### Parameter Kompilasi PHP

#### --enable-swoole

Mengaktifkan kompilasi statis ekstensi Swoole ke dalam PHP. Dengan langkah-langkah di bawah, opsi `--enable-swoole` bakal muncul.

```shell
cp -r /home/swoole-src /home/php-src/ext
cd /home/php-src
./buildconf --force
./configure --help | grep swoole
```

!> Opsi ini dipake pas kompilasi PHP, bukan Swoole

## Pertanyaan Umum

* [Masalah Umum Instalasi Swoole](/question/install)
