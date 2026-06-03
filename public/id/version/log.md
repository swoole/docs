# Catatan Pembaruan Versi

Mulai dari versi `v1.5`, catatan pembaruan versi yang ketat telah dibuat. Saat ini, rata-rata siklus iterasi adalah satu rilis besar setiap enam bulan, dan satu rilis kecil setiap `2-4` minggu.

## Versi PHP yang Disarankan

* 8.0
* 8.1
* 8.2
* 8.3
* 8.4

## Versi Swoole yang Disarankan
`Swoole6.x` dan `Swoole5.x`

Perbedaan keduanya: `v6.x` adalah branch yang aktif dikembangkan, `v5.x` adalah branch yang **tidak** aktif dikembangkan, hanya memperbaiki `BUG`.

!> Versi `v4.x` ke atas dapat menonaktifkan fitur coroutine dengan mengatur [enable_coroutine](/server/setting?id=enable_coroutine), menjadikannya versi non-coroutine.

## Tipe Versi

* `alpha` Versi pratinjau fitur, menandakan tugas dalam rencana pengembangan telah selesai, dibuka untuk pratinjau, mungkin mengandung banyak `BUG`
* `beta` Versi pengujian, menandakan sudah dapat digunakan untuk pengujian di lingkungan pengembangan, mungkin mengandung `BUG`
* `rc[1-n]` Versi kandidat rilis, menandakan sedang dalam siklus rilis, menjalani pengujian skala besar, selama periode ini `BUG` masih mungkin ditemukan
* Tanpa akhiran berarti versi stabil, menandakan versi ini telah selesai dikembangkan dan siap digunakan secara resmi

## Melihat Informasi Versi Saat Ini

```shell
php --ri swoole
```

## v6.0.2

### Fitur Baru:
- Menambahkan metode `Swoole\Thread::yield()`, `Swoole\Thread::activeCount()`, dan `Swoole\Thread::isAlive()`.

### Perbaikan Bug:
- Memperbaiki masalah di mode `SWOOLE_THREAD` saat menggunakan mode single-thread dan pengaturan heartbeat menyebabkan error.
- Memperbaiki masalah segmentasi error setelah mengaktifkan `swoole.enable_fiber_mock`.
- Memperbaiki masalah overflow integer di server Redis.

### Catatan:
- Server Redis saat ini hanya akan mendukung protokol `RESP2`, saat memformat string yang tidak sesuai dengan protokol tersebut, akan melempar exception, bukan mencatat log.

## v6.0.1

### Perbaikan Bug:
- Memperbaiki masalah di mode `SWOOLE_THREAD` di mana proses tidak bisa keluar normal karena event listener tidak dihapus.
- Memperbaiki masalah upload file besar saat konfigurasi `single_thread` diaktifkan.
- Memperbaiki masalah jalur file tidak ditemukan saat kompilasi jika variabel yang sama sudah didefinisikan di `config.m4`.
- Memperbaiki masalah proses tidak bisa keluar normal setelah timeout di `Swoole\Process\Pool`.
- Memperbaiki masalah crash program saat memanggil `putenv` di mode `SWOOLE_THREAD`.
- Memperbaiki masalah tidak bisa mengatur callback event untuk port terpisah di mode `SWOOLE_THREAD`.
- Memperbaiki masalah tidak bisa mendapatkan berbagai parameter runtime di event `onWorkerStart` dll di mode `SWOOLE_THREAD`.
- Memperbaiki masalah PostgreSQL yang menurun ke mode sinkron saat menerima data besar setelah coroutine.
- Mengoptimalkan logika pengecekan parameter fungsi `swoole_substr_json_decode`/`swoole_substr_unserialize`.
- Memperbaiki masalah pengaturan CPU affinity di `config.m4`.
- Memperbaiki masalah deteksi heartbeat tidak berfungsi di mode `SWOOLE_THREAD`.

### Catatan:
- Di layanan Http, jika proses restart,level bawah akan mengirim 500 Internal Server ke permintaan yang menunggu dalam antrian, setelah selesai mengirim, koneksi ditutup dan permintaan tersebut dibuang.
- Karena `stream factory` dan `stream ops` bawaan php bergantung pada konfigurasi runtime yang tidak thread-safe, dalam mode multi-thread hanya thread utama yang diizinkan mengubah konfigurasi runtime ini sebelum membuat thread anak.
- Meng-upgrade nghttp2 ke versi terbaru.

## v5.1.7

### Perbaikan Bug:
- Memperbaiki masalah PostgreSQL yang menurun ke mode sinkron saat menerima data besar setelah coroutine.
- Memperbaiki masalah exception properti saat membuat kelas dinamis `Swoole\Http2\Request`.
- Memperbaiki masalah memori error akibat fatal error dalam satu-klik coroutine.

## v6.0.0

### Fitur Baru:
- `Swoole` mendukung mode multi-thread, ketika `php` dalam mode `zts`, kompilasi `Swoole` dengan `--enable-swoole-thread`.
- Menambahkan kelas thread `Swoole\Thread`. @matyhtf
- Menambahkan thread lock `Swoole\Thread\Lock`. @matyhtf
- Menambahkan atomic count thread `Swoole\Thread\Atomic`, `Swoole\Thread\Atomic\Long`. @matyhtf
- Menambahkan container konkuren aman `Swoole\Thread\Map`, `Swoole\Thread\ArrayList`, `Swoole\Thread\Queue`. @matyhtf
- Operasi file asinkron mendukung `iouring` sebagai mesinlevel bawah, setelah menginstal `liburing` dan mengompilasi `Swoole` dengan `--enable-iouring`, fungsi `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize` akan diimplementasikan oleh `iouring`. @matyhtf @NathanFreeman
- Meng-upgrade `Boost Context` ke versi 1.84. Sekarang, CPU Loongson juga dapat mendukung coroutine. @NathanFreeman
- Menambahkan metode `Swoole\Thread\Map::find()`. @matyhtf
- Menambahkan metode `Swoole\Thread\ArrayList::find()`. @matyhtf
- Menambahkan metode `Swoole\Thread\ArrayList::offsetUnset()`. @matyhtf
- Menambahkan metode `Swoole\Process::getAffinity()`. @matyhtf
- Menambahkan metode `Swoole\Thread::setName()`. @matyhtf
- Menambahkan metode `Swoole\Thread::setAffinity()`. @matyhtf
- Menambahkan metode `Swoole\Thread::getAffinity()`. @matyhtf
- Menambahkan metode `Swoole\Thread::setPriority()`. @matyhtf
- Menambahkan metode `Swoole\Thread::getPriority()`. @matyhtf
- Menambahkan metode `Swoole\Thread::gettid()`.
- Mesin file asinkron `iouring` mendukung mode polling multi-thread `IORING_SETUP_SQPOLL`. @NathanFreeman
- Menambahkan `iouring_workers` untuk mengubah jumlah thread iouring. @NathanFreeman
- Menambahkan `iouring_flags` untuk mendukung perubahan mode kerja `iouring`. @NathanFreeman
- Menambahkan `Swoole\Thread\Barrier` untuk sinkronisasi multi-thread. @matyhtf
- Menambahkan fungsi pengaturan cookie baru. @matyhtf @NathanFreeman
- Menambahkan "coroutine lock non-blocking yang dapat reentrant", dapat digunakan antar proses/thread, dan tidak memblokir proses/thread. @NathanFreeman
- `Swoole\Coroutine\Socket::getOption()` mendukung opsi `TCP_INFO`. @matyhtf
- Klien sinkron blocking `Swoole\Client` mendukung proxy `http`. @matyhtf
- Menambahkan klien `TCP/UDP/Unixsocket` asinkron non-blocking `Swoole\Async\Client`. @matyhtf
- Mengoptimalkan metode `Swoole\Redis\Server::format()`, mendukung zero-copy memori dan struktur bertingkat redis. @matyhtf
- Mendukung alat kompresi berkinerja tinggi `Zstd`, cukup tambahkan `--enable-zstd` saat kompilasi `Swoole`, klien dan server http dapat menggunakan `zstd` untuk mengompresi atau mendekode respons. @NathanFreeman

### Perbaikan Bug:
- Memperbaiki masalah tidak bisa diinstal melalui `pecl`. @remicollet
- Memperbaiki klien `Swoole\Coroutine\FastCGI\Client` tidak bisa mengatur keepalive. @NathanFreeman
- Memperbaiki masalah error saat parameter permintaan melebihi `max_input_vars` menyebabkan proses terus restart. @NathanFreeman
- Memperbaiki masalah tidak diketahui saat menggunakan `Swoole\Event::wait()` di coroutine. @matyhtf
- Memperbaiki masalah `proc_open` tidak mendukung pty saat coroutine. @matyhtf
- Memperbaiki masalah `pdo_sqlite` mengalami segmentasi error di PHP8.3. @NathanFreeman
- Memperbaiki peringatan tidak berguna saat kompilasi `Swoole`. @Appla @NathanFreeman
- Memperbaiki error saatlevel bawah memanggil zend_fetch_resource2_ex jika `STDOUT/STDERR` sudah ditutup. @Appla @matyhtf
- Memperbaiki konfigurasi `set_tcp_nodelay` yang tidak valid. @matyhtf
- Memperbaiki masalah kadang-kadang memicu cabang yang tidak terjangkau saat upload file. @NathanFreeman
- Memperbaiki masalah pengaturan `dispatch_func` menyebabkan error dilevel bawah php. @NathanFreeman
- Memperbaiki AC_PROG_CC_C99 yang sudah usang di autoconf >= 2.70. @petk
- Menangkap exception saat pembuatan thread gagal. @matyhtf
- Memperbaiki masalah `_tsrm_ls_cache` tidak terdefinisi. @jingjingxyk
- Memperbaiki masalah kompilasi fatal error di `GCC 14`. @remicollet
- Memperbaiki masalah properti dinamis `Swoole\Http2\Request`. @guandeng
- Memperbaiki masalah sumber daya `pgsql` coroutine client kadang tidak tersedia. @NathanFreeman
- Memperbaiki masalah 503 error karena parameter terkait tidak diatur ulang saat restart proses. @matyhtf
- Memperbaiki ketidaksesuaian hasil `$request->server['request_method']` dengan `$request->getMethod()` saat `HTTP2` diaktifkan. @matyhtf
- Memperbaiki `content-type` yang salah saat upload file. @matyhtf
- Memperbaiki kesalahan kode klien coroutine `http2`. @matyhtf
- Memperbaiki masalah `Swoole\Server` kekurangan properti `worker_id`. @cjavad
- Memperbaiki kesalahan `config.m4` terkait `brotli`. @fundawang
- Memperbaiki `Swoole\Http\Response::create` tidak berfungsi di multi-thread. @matyhtf
- Memperbaiki error kompilasi di lingkungan `macos`. @matyhtf
- Memperbaiki masalah thread tidak bisa keluar dengan aman. @matyhtf
- Memperbaiki masalah variabel statis waktu respons `Swoole\Http\Response` tidak dibuat per thread di mode multi-thread. @matyhtf @NathanFreeman
- Memperbaiki masalah `Fatal error` yang disebabkan oleh fitur `timeout` di `PHP-8.4` mode ZTS. @matyhtf
- Memperbaiki hook fungsi `exit()` di `PHP-8.4`. @remicollet
- Memperbaiki masalah `Swoole\Thread::getNativeId()` tidak berfungsi di `cygwin`. @matyhtf
- Memperbaiki masalah `Swoole\Coroutine::getaddrinfo()` menyebabkan `SIGSEGV`. @matyhtf
- Memperbaiki masalah modul `runtime tcp` tidak mendukung aktivasi enkripsi SSL dinamis. @matyhtf
- Memperbaiki masalah waktu timeout tidak tepat saat klien http berjalan lama. @matyhtf
- Memperbaiki masalah mutex `Swoole\Table` tidak bisa digunakan sebelum proses keluar. @matyhtf
- Memperbaiki masalah `Swoole\Server::stop()` gagal saat menggunakan parameter bernama. @matyhtf
- Memperbaiki masalah crash karena fungsi `Swoole\Thread\Map::toArray()` tidak menyalin `key`. @matyhtf
- Memperbaiki masalah `Swoole\Thread\Map` tidak bisa menghapus kunci numerik bertingkat. @matyhtf

### Optimasi Kernel:
- Menghapus pengecekan tidak berguna pada `socket structs`. @petk
- Meng-upgrade Swoole Library. @deminy
- `Swoole\Http\Response` menambahkan dukungan untuk status code 451. @abnegate
- Menyinkronkan kode operasi `file` antar versi PHP yang berbeda. @NathanFreeman
- Menyinkronkan kode operasi `pdo` antar versi PHP yang berbeda. @NathanFreeman
- Mengoptimalkan kode `Socket::ssl_recv()`. @matyhtf
- Mengoptimalkan config.m4, beberapa konfigurasi dapat mengatur lokasi library dependensi melalui `pkg-config`. @NathanFreeman
- Mengoptimalkan masalah penggunaan array dinamis saat `mengurai header permintaan`. @NathanFreeman
- Mengoptimalkan masalah siklus hidup file descriptor `fd` di mode multi-thread. @matyhtf
- Mengoptimalkan beberapa logika dasar coroutine. @matyhtf
- Meng-upgrade versi database oracle untuk pengujian CI. @gvenzl
- Mengoptimalkan logika terkait `sendfile` dilevel bawah. @matyhtf
- Mengganti `PHP_DEF_HAVE` dengan `AC_DEFINE_UNQUOTED` di `config.m4`. @petk
- Mengoptimalkan logika terkait `heartbeat`, `shutdown`, dan `stop` server di mode multi-thread. @matyhtf
- Mengoptimalkan agar tidak perlu menautkan `librt` saat versi glibc di atas 2.17. @matyhtf
- Memperkuat klien `http` agar dapat menerima header permintaan duplikat. @matyhtf
- Mengoptimalkan `Swoole\Http\Response::write()`. @matyhtf
- `Swoole\Http\Response::write()` sekarang dapat mengirim protokol `http2`. @matyhtf
- Kompatibel dengan `PHP8.4`. @matyhtf @NathanFreeman
- Menambahkan kemampuan penulisan asinkron socket dilevel bawah. @matyhtf
- Mengoptimalkan `Swoole\Http\Response`. @NathanFreeman
- Mendukung berbagi socket native php di mode multi-thread. @matyhtf
- Mengoptimalkan layanan file statis, memperbaiki masalah jalur file statis yang salah. @matyhtf
- Server `Asinkron` mode multi-thread mendukung restart worker thread. @matyhtf
- Server `Asinkron` mode multi-thread mendukung pengaktifan timer di thread `Manager`. @matyhtf
- Kompatibel dengan ekstensi `curl` `PHP-8.4`. @matyhtf @NathanFreeman
- Menulis ulang kode penggunaan `iouring` dilevel bawah Swoole. @matyhtf @NathanFreeman
- Mengoptimalkan timer agar proses sinkron tidak bergantung pada sinyal. @matyhtf
- Mengoptimalkan metode `Swoole\Coroutine\System::waitSignal()`, memungkinkan mendengarkan beberapa sinyal sekaligus. @matyhtf

### Tidak Lagi Didukung:
- Tidak lagi mendukung `PHP 8.0`.
- Tidak lagi mendukung klien coroutine `Swoole\Coroutine\MySQL`.
- Tidak lagi mendukung klien coroutine `Swoole\Coroutine\Redis`.
- Tidak lagi mendukung klien coroutine `Swoole\Coroutine\PostgreSQL`.
- Menghapus metode `Swoole\Coroutine\System::fread()`, `Swoole\Coroutine\System::fwrite()`, dan `Swoole\Coroutine\System::fgets()`.

## v5.1.5

### Perbaikan Bug:
- Memperbaiki kebutuhan menggunakan `zend_ini_parse_quantity` untuk mengurai string numerik saat versi php lebih besar dari 8.2. @matyhtf
- Memperbaiki masalah sumber daya `pdo_pgsql` kadang tidak tersedia saat coroutine. @NathanFreeman
- Memperbaiki masalah referensi file header `pdo_pgsql` saat coroutine. @NathanFreeman
- Memperbaiki pemeriksaan jalur relatif yang salah untuk menghindari bypass validasi jalur. @matyhtf
- Memperbaiki masalah jumlah konkurensi yang tidak akurat saat restart proses di lingkungan konkurensi tinggi. @matyhtf

### Optimasi Kernel:
- Menyinkronkan beberapa kode terkait `php8.3 curl`. @NathanFreeman
- Memperbaiki error tes inti modul `process`. @NathanFreeman
- Semua koneksi harus ditutup pada tahap `PHP RSHUTDOWN` dalam mode `SWOOLE_BASE`. @matyhtf
- Mengoptimalkan kode kernel. @matyhtf

## v6.0.0-beta

### Fitur Baru:
- Menambahkan metode `Swoole\Thread\Map::find()`. @matyhtf
- Menambahkan metode `Swoole\Thread\ArrayList::find()`. @matyhtf
- Menambahkan metode `Swoole\Thread\ArrayList::offsetUnset()`. @matyhtf
- Menambahkan metode `Swoole\Process::getAffinity()`. @matyhtf
- Menambahkan metode `Swoole\Thread::setName()`. @matyhtf
- Menambahkan metode `Swoole\Thread::setAffinity()`. @matyhtf
- Menambahkan metode `Swoole\Thread::getAffinity()`. @matyhtf
- Menambahkan metode `Swoole\Thread::setPriority()`. @matyhtf
- Menambahkan metode `Swoole\Thread::getPriority()`. @matyhtf
- Menambahkan metode `Swoole\Thread::gettid()`.
- Mesin file asinkron `iouring` mendukung mode polling multi-thread `IORING_SETUP_SQPOLL`. @NathanFreeman
- Menambahkan `iouring_workers` untuk mengubah jumlah thread iouring. @NathanFreeman
- Menambahkan `iouring_flags` untuk mendukung perubahan mode kerja `iouring`. @NathanFreeman
- Menambahkan `Swoole\Thread\Barrier` untuk sinkronisasi multi-thread. @matyhtf
- Menambahkan fungsi pengaturan cookie baru. @matyhtf @NathanFreeman

### Perbaikan Bug:
- Memperbaiki masalah properti dinamis `Swoole\Http2\Request`. @guandeng
- Memperbaiki masalah sumber daya `pgsql` coroutine client kadang tidak tersedia. @NathanFreeman
- Memperbaiki masalah 503 error karena parameter terkait tidak diatur ulang saat restart proses. @matyhtf
- Memperbaiki ketidaksesuaian hasil `$request->server['request_method']` dengan `$request->getMethod()` saat `HTTP2` diaktifkan. @matyhtf
- Memperbaiki `content-type` yang salah saat upload file. @matyhtf
- Memperbaiki kesalahan kode klien coroutine `http2`. @matyhtf
- Memperbaiki masalah `Swoole\Server` kekurangan properti `worker_id`. @cjavad
- Memperbaiki kesalahan `config.m4` terkait `brotli`. @fundawang
- Memperbaiki `Swoole\Http\Response::create` tidak berfungsi di multi-thread. @matyhtf
- Memperbaiki error kompilasi di lingkungan `macos`. @matyhtf
- Memperbaiki masalah thread tidak bisa keluar dengan aman. @matyhtf
- Memperbaiki masalah variabel statis waktu respons `Swoole\Http\Response` tidak dibuat per thread di mode multi-thread. @matyhtf @NathanFreeman

### Optimasi Kernel:
- Meng-upgrade versi database oracle untuk pengujian CI. @gvenzl
- Menulis ulang dan mengoptimalkan kode terkaitlevel bawah swoole. @matyhtf
- Mengoptimalkan logika terkait `sendfile` dilevel bawah. @matyhtf
- Mengoptimalkan penguraian parameter. @matyhtf
- Mengganti `PHP_DEF_HAVE` dengan `AC_DEFINE_UNQUOTED` di `config.m4`. @petk
- Mengoptimalkan logika terkait `heartbeat`, `shutdown`, dan `stop` server di mode multi-thread. @matyhtf
- Mengoptimalkan agar tidak perlu menautkan `librt` saat versi glibc di atas 2.17. @matyhtf
- Memperkuat klien `http` agar dapat menerima header permintaan duplikat. @matyhtf
- Mengoptimalkan `Swoole\Http\Response::write()`. @matyhtf
- `Swoole\Http\Response::write()` sekarang dapat mengirim protokol `http2`. @matyhtf
- Kompatibel dengan `PHP8.4`. @matyhtf @NathanFreeman
- Menambahkan kemampuan penulisan asinkron socket dilevel bawah. @matyhtf
- Mengoptimalkan `Swoole\Http\Response`. @NathanFreeman
- Mengoptimalkan informasi error dilevel bawah. @matyhtf
- Mendukung berbagi socket native php di mode multi-thread. @matyhtf
- Mengoptimalkan layanan file statis, memperbaiki masalah jalur file statis yang salah. @matyhtf

### Tidak Lagi Didukung:
- Menghapus metode `Swoole\Coroutine\System::fread()`, `Swoole\Coroutine\System::fwrite()`, dan `Swoole\Coroutine\System::fgets()`.

## v6.0.0-alpha

### Fitur Baru
- `Swoole` mendukung mode multi-thread, ketika `php` dalam mode `zts`, kompilasi `Swoole` dengan `--enable-swoole-thread`.
- Menambahkan kelas manajemen thread `Swoole\Thread`. @matyhtf
- Menambahkan thread lock `Swoole\Thread\Lock`. @matyhtf
- Menambahkan atomic count thread `Swoole\Thread\Atomic`, `Swoole\Thread\Atomic\Long`. @matyhtf
- Menambahkan container konkuren aman `Swoole\Thread\Map`, `Swoole\Thread\ArrayList`, `Swoole\Thread\Queue`. @matyhtf
- Operasi file asinkron mendukung `iouring` sebagai mesinlevel bawah, setelah menginstal `liburing` dan mengompilasi `Swoole` dengan `--enable-iouring`, fungsi `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize` akan diimplementasikan oleh `iouring`. @matyhtf @NathanFreeman
- Meng-upgrade `Boost Context` ke versi 1.84. Sekarang, CPU Loongson juga dapat menggunakan coroutine. @NathanFreeman

### Perbaikan Bug
- Memperbaiki masalah tidak bisa diinstal melalui `pecl`. @remicollet
- Memperbaiki klien `Swoole\Coroutine\FastCGI\Client` tidak bisa mengatur keepalive. @NathanFreeman
- Memperbaiki masalah error saat parameter permintaan melebihi `max_input_vars` menyebabkan proses terus restart. @NathanFreeman
- Memperbaiki masalah tidak diketahui saat menggunakan `Swoole\Event::wait()` di coroutine. @matyhtf
- Memperbaiki masalah `proc_open` tidak mendukung pty saat coroutine. @matyhtf
- Memperbaiki masalah `pdo_sqlite` mengalami segmentasi error di PHP8.3. @NathanFreeman
- Memperbaiki peringatan tidak berguna saat kompilasi `Swoole`. @Appla @NathanFreeman
- Memperbaiki error saatlevel bawah memanggil zend_fetch_resource2_ex jika `STDOUT/STDERR` sudah ditutup. @Appla @matyhtf
- Memperbaiki konfigurasi `set_tcp_nodelay` yang tidak valid. @matyhtf
- Memperbaiki masalah kadang-kadang memicu cabang yang tidak terjangkau saat upload file. @NathanFreeman
- Memperbaiki masalah pengaturan `dispatch_func` menyebabkan error dilevel bawah php. @NathanFreeman
- Memperbaiki AC_PROG_CC_C99 yang sudah usang di autoconf >= 2.70. @petk
- Menangkap exception saat pembuatan thread gagal. @matyhtf
- Memperbaiki masalah `_tsrm_ls_cache` tidak terdefinisi. @jingjingxyk
- Memperbaiki masalah kompilasi fatal error di `GCC 14`. @remicollet

### Optimasi Kernel
- Menghapus pengecekan tidak berguna pada `socket structs`. @petk
- Meng-upgrade Swoole Library. @deminy
- `Swoole\Http\Response` menambahkan dukungan untuk status code 451. @abnegate
- Menyinkronkan kode operasi `file` antar versi PHP yang berbeda. @NathanFreeman
- Menyinkronkan kode operasi `pdo` antar versi PHP yang berbeda. @NathanFreeman
- Mengoptimalkan kode `Socket::ssl_recv()`. @matyhtf
- Mengoptimalkan config.m4, beberapa konfigurasi dapat mengatur lokasi library dependensi melalui `pkg-config`. @NathanFreeman
- Mengoptimalkan masalah penggunaan array dinamis saat `mengurai header permintaan`. @NathanFreeman
- Mengoptimalkan masalah siklus hidup file descriptor `fd` di mode multi-thread. @matyhtf
- Mengoptimalkan beberapa logika dasar coroutine. @matyhtf

### Tidak Lagi Didukung
- Tidak lagi mendukung `PHP 8.0`.
- Tidak lagi mendukung klien coroutine `Swoole\Coroutine\MySQL`.
- Tidak lagi mendukung klien coroutine `Swoole\Coroutine\Redis`.
- Tidak lagi mendukung klien coroutine `Swoole\Coroutine\PostgreSQL`.

## v5.1.3
### Perbaikan Bug:
- Memperbaiki masalah tidak bisa diinstal melalui `pecl`.
- Memperbaiki klien `Swoole\Coroutine\FastCGI\Client` tidak bisa mengatur keepalive.
- Memperbaiki masalah error saat parameter permintaan melebihi `max_input_vars` menyebabkan proses terus restart.
- Memperbaiki masalah tidak diketahui saat menggunakan `Swoole\Event::wait()` di coroutine.
- Memperbaiki masalah `proc_open` tidak mendukung pty saat coroutine.
- Memperbaiki masalah `pdo_sqlite` mengalami segmentasi error di PHP8.3.
- Memperbaiki peringatan tidak berguna saat kompilasi `Swoole`.
- Memperbaiki error saatlevel bawah memanggil zend_fetch_resource2_ex jika `STDOUT/STDERR` sudah ditutup.
- Memperbaiki konfigurasi `set_tcp_nodelay` yang tidak valid.
- Memperbaiki masalah kadang-kadang memicu cabang yang tidak terjangkau saat upload file.
- Memperbaiki masalah pengaturan `dispatch_func` menyebabkan error dilevel bawah php.
- Memperbaiki AC_PROG_CC_C99 yang sudah usang di autoconf >= 2.70.

### Optimasi Kernel:
- Menghapus pengecekan tidak berguna pada `socket structs`.
- Meng-upgrade Swoole Library.
- `Swoole\Http\Response` menambahkan dukungan untuk status code 451.
- Menyinkronkan kode operasi `file` antar versi PHP yang berbeda.
- Menyinkronkan kode operasi `pdo` antar versi PHP yang berbeda.
- Mengoptimalkan kode `Socket::ssl_recv()`.
- Mengoptimalkan config.m4, beberapa konfigurasi dapat mengatur lokasi library dependensi melalui `pkg-config`.
- Mengoptimalkan masalah penggunaan array dinamis saat `mengurai header permintaan`.

## v5.1.2

### Perbaikan Bug
- Mendukung embedded sapi.
- Memperbaiki masalah kompatibilitas ZEND_CHECK_STACK_LIMIT di PHP 8.3.
- Memperbaiki error tidak adanya header Content-Range saat range request mengembalikan seluruh konten file.
- Memperbaiki cookie yang terpotong.
- Memperbaiki crash native-curl di PHP 8.3.
- Memperbaiki error errno yang tidak valid setelah Server::Manager::wait().
- Memperbaiki kesalahan ketik HTTP2.

### Optimasi
- Mengoptimalkan kinerja HTTP server.
- Menambahkan CLOSE_SERVICE_RESTART, CLOSE_TRY_AGAIN_LATER, CLOSE_BAD_GATEWAY sebagai alasan penutupan websocket yang valid.

## v5.1.1
### Perbaikan Bug
- Memperbaiki masalah kebocoran memori `http coroutine client`.
- Memperbaiki masalah `pdo_odbc` tidak bisa di-coroutine-kan.
- Memperbaiki masalah eksekusi `socket_import_stream()` yang salah.
- Memperbaiki masalah `Context::parse_multipart_data()` tidak bisa menangani body permintaan kosong.
- Memperbaiki masalah parameter `PostgreSQL coroutine client` tidak berfungsi.
- Memperbaiki bug crash `curl` saat destruksi.
- Memperbaiki masalah ketidakcocokan `Swoole5.x` dengan `xdebug` versi baru.
- Memperbaiki masalah `kelas tidak ditemukan` akibat peralihan coroutine selama autoload.
- Memperbaiki masalah tidak bisa mengompilasi `swoole` di `OpenBSD`.

## v5.1.0

### Fitur Baru
- Menambahkan dukungan coroutine untuk `pdo_pgsql`
- Menambahkan dukungan coroutine untuk `pdo_odbc`
- Menambahkan dukungan coroutine untuk `pdo_oci`
- Menambahkan dukungan coroutine untuk `pdo_sqlite`
- Menambahkan konfigurasi koneksi pool untuk `pdo_pgsql`, `pdo_odbc`, `pdo_oci`, `pdo_sqlite`

### Peningkatan
- Meningkatkan kinerja `Http\Server`, dapat meningkat hingga `60%` dalam kasus ekstrem

### Perbaikan
- Memperbaiki kebocoran memori setiap permintaan pada klien coroutine `WebSocket`
- Memperbaiki masalah `http coroutine server` keluar secara tidak graceful menyebabkan klien tidak keluar
- Memperbaiki masalah opsi `--enable-thread-context` saat kompilasi menyebabkan `Process::signal()` tidak berfungsi
- Memperbaiki masalah penghitungan koneksi yang salah saat proses keluar tidak normal dalam mode `SWOOLE_BASE`
- Memperbaiki kesalahan tanda tangan fungsi `stream_select()`
- Memperbaiki kesalahan sensitivitas huruf besar/kecil pada informasi MIME file
- Memperbaiki kesalahan ejaan `Http2\Request::$usePipelineRead` yang menyebabkan peringatan di lingkungan PHP8.2
- Memperbaiki masalah kebocoran memori dalam mode `SWOOLE_BASE`
- Memperbaiki masalah kebocoran memori saat mengatur waktu kadaluarsa cookie dengan `Http\Response::cookie()`
- Memperbaiki masalah kebocoran koneksi dalam mode `SWOOLE_BASE`

### Kernel
- Memperbaiki masalah tanda tangan fungsi `php_url_encode` di Swoole pada PHP 8.3
- Memperbaiki masalah opsi unit test
- Mengoptimalkan dan menulis ulang kode
- Kompatibel dengan PHP8.3
- Tidak mendukung kompilasi pada sistem operasi 32-bit

## v5.0.3

### Peningkatan
- Menambahkan opsi `--with-nghttp2_dir` untuk menggunakan library `nghttp2` dalam sistem
- Mendukung opsi terkait panjang atau ukuran byte
- Menambahkan fungsi `Process\Pool::sendMessage()`
- `Http\Response:cookie()` mendukung `max-age`

### Perbaikan
- Memperbaiki kebocoran memori yang disebabkan oleh event `Server task/pipemessage/finish`

### Kernel
- Konflik header respons `http` tidak lagi memicu error
- Penutupan koneksi `Server` tidak lagi memicu error

## v5.0.2

### Peningkatan
- Mendukung konfigurasi pengaturan default untuk `http2`
- Mendukung `xdebug` versi 8.1 atau lebih tinggi
- Menulis ulang curl native untuk mendukung handle curl dengan banyak socket, seperti protokol curl FTP
- Menambahkan parameter `who` di `Process::setPriority/getPriority`
- Menambahkan metode `Coroutine\Socket::getBoundCid()`
- Menyesuaikan nilai default parameter `length` dalam metode `Coroutine\Socket::recvLine/recvWithBuffer` menjadi `65536`
- Menulis ulang fitur keluar lintas-coroutine untuk pembebasan memori yang lebih aman dan memperbaiki masalah crash saat fatal error
- Menambahkan atribut `socket` untuk `Coroutine\Client`, `Coroutine\Http\Client`, `Coroutine\Http2\Client`, memungkinkan operasi langsung pada resource socket
- Mendukung pengiriman file kosong dari `Http\Server` ke klien `http2`
- Mendukung restart graceful untuk `Coroutine\Http\Server`. Saat server dimatikan, koneksi klien tidak lagi ditutup paksa, hanya berhenti mendengarkan permintaan baru
- Menambahkan `pcntl_rfork` dan `pcntl_sigwaitinfo` ke daftar fungsi tidak aman, akan dinonaktifkan saat container coroutine dimulai
- Menulis ulang manajer proses mode `SWOOLE_BASE`, perilaku shutdown dan reload akan konsisten dengan `SWOOLE_PROCESS`

## v5.0.1

### Peningkatan
- Mendukung `PHP-8.2`, meningkatkan penanganan exception coroutine, kompatibilitas dengan `ext-soap`
- Menambahkan dukungan `LOB` untuk klien coroutine `pgsql`
- Meningkatkan klien `websocket`, header upgrade menyertakan `websocket` bukan menggunakan `=`
- Mengoptimalkan klien `http`, menonaktifkan `keep-alive` saat server mengirim `connection close`
- Mengoptimalkan klien `http`, menonaktifkan penambahan header `Accept-Encoding` tanpa library kompresi
- Meningkatkan informasi debug, mengatur kata sandi sebagai parameter sensitif di `PHP-8.2`
- Memperkuat `Server::taskWaitMulti()`, tidak memblokir di lingkungan coroutine
- Mengoptimalkan fungsi log, tidak lagi mencetak ke layar saat gagal menulis ke file log

### Perbaikan
- Memperbaiki masalah kompatibilitas parameter untuk `Coroutine::printBackTrace()` dan `debug_print_backtrace()`
- Memperbaiki dukungan resource socket di `Event::add()`
- Memperbaiki error kompilasi saat `zlib` tidak tersedia
- Memperbaiki masalah crash saat membongkar task server ketika parsing string yang tidak terduga
- Memperbaiki masalah penambahan timer kurang dari `1ms` dipaksa menjadi `0`
- Memperbaiki masalah crash yang disebabkan oleh `Table::getMemorySize()` sebelum menambahkan kolom
- Mengubah nama parameter kadaluarsa dalam metode `Http\Response::setCookie()` menjadi `expires`

## v5.0.0

### Fitur Baru
- Menambahkan opsi `max_concurrency` untuk `Server`
- Menambahkan opsi `max_retries` untuk `Coroutine\Http\Client`
- Menambahkan opsi global `name_resolver`. Menambahkan opsi `upload_max_filesize` untuk `Server`
- Menambahkan metode `Coroutine::getExecuteTime()`
- Menambahkan mode dispatch `SWOOLE_DISPATCH_CONCURRENT_LB` untuk `Server`
- Memperkuat sistem tipe, menambahkan tipe untuk semua parameter dan nilai kembali fungsi
- Mengoptimalkan penanganan error, semua konstruktor akan melempar exception saat gagal
- Menyesuaikan mode default `Server` menjadi `SWOOLE_BASE`
- Memindahkan klien coroutine `pgsql` ke library inti. Mencakup semua perbaikan bug dari branch `4.8.x`

### Penghapusan
- Menghapus nama kelas bergaya `PSR-0`
- Menghapus fitur penambahan otomatis `Event::wait()` dalam fungsi shutdown
- Menghapus alias `Server::tick/after/clearTimer/defer`
- Menghapus `--enable-http2/--enable-swoole-json`, disesuaikan menjadi aktif default

### Tidak Lagi Didukung
- Klien coroutine `Coroutine\Redis` dan `Coroutine\MySQL` tidak digunakan lagi secara default

## v4.8.13

### Peningkatan
- Menulis ulang curl native untuk mendukung handle curl dengan banyak socket, seperti protokol curl FTP
- Mendukung pengaturan manual konfigurasi `http2`
- Meningkatkan `WebSocket client`, header upgrade menyertakan `websocket` bukan `equal`
- Mengoptimalkan klien HTTP, menonaktifkan `keep-alive` saat server mengirim penutupan koneksi
- Meningkatkan informasi debug, mengatur kata sandi sebagai parameter sensitif di PHP-8.2
- Mendukung `HTTP Range Requests`

### Perbaikan
- Memperbaiki masalah kompatibilitas parameter di `Coroutine::printBackTrace()` dan `debug_print_backtrace()`
- Memperbaiki masalah parsing panjang yang salah saat server `WebSocket` mengaktifkan protokol `HTTP2` dan `WebSocket` secara bersamaan
- Memperbaiki masalah kebocoran memori saat `send_yield` terjadi di `Server::send()`, `Http\Response::end()`, `Http\Response::write()`, dan `WebSocket/Server::push()`
- Memperbaiki masalah crash yang disebabkan oleh `Table::getMemorySize()` sebelum menambahkan kolom

## v4.8.12

### Peningkatan
- Mendukung PHP8.2
- Fungsi `Event::add()` mendukung resource socket
- `Http\Client::sendfile()` mendukung file lebih dari 4G
- `Server::taskWaitMulti()` mendukung lingkungan coroutine

### Perbaikan
- Memperbaiki masalah penerimaan `multipart body` yang salah akan memicu pesan error
- Memperbaiki error yang disebabkan oleh waktu timeout timer kurang dari `1ms`
- Memperbaiki masalah deadlock yang disebabkan oleh disk penuh

## v4.8.11

### Peningkatan
- Mendukung mekanisme pertahanan keamanan `Intel CET`
- Menambahkan properti `Server::$ssl`
- Saat mengompilasi `swoole` menggunakan `pecl`, menambahkan atribut `enable-cares`
- Menulis ulang interpreter `multipart_parser`

### Perbaikan
- Memperbaiki segmentasi error akibat exception koneksi persisten `pdo`
- Memperbaiki segmentasi error akibat penggunaan coroutine di destruktor
- Memperbaiki pesan error yang salah di `Server::close()`

## v4.8.10

### Perbaikan

- Mengatur ulang parameter timeout `stream_select` menjadi `0` saat kurang dari `1ms`
- Memperbaiki kegagalan kompilasi akibat penambahan `-Werror=format-security` saat kompilasi
- Memperbaiki segmentasi error `Swoole\Coroutine\Http\Server` akibat penggunaan `curl`

## v4.8.9

### Peningkatan

- Mendukung opsi `http_auto_index` di server `Http2`

### Perbaikan

- Mengoptimalkan parser `Cookie`, mendukung opsi `HttpOnly`
- Memperbaiki #4657, masalah tipe kembali metode Hook `socket_create`
- Memperbaiki kebocoran memori `stream_select`

### Pembaruan CLI

- `CygWin` menyertakan rantai sertifikat SSL, memperbaiki error autentikasi SSL
- Diperbarui ke `PHP-8.1.5`

## v4.8.8

### Optimasi

- Mengurangi SW_IPC_BUFFER_MAX_SIZE menjadi 64k
- Mengoptimalkan pengaturan header_table_size http2

### Perbaikan

- Memperbaiki banyak error socket saat menggunakan enable_static_handler untuk mengunduh file statis
- Memperbaiki error NPN server http2

## v4.8.7

### Peningkatan

- Menambahkan dukungan curl_share

### Perbaikan

- Memperbaiki error simbol tidak terdefinisi pada arsitektur arm32
- Memperbaiki kompatibilitas `clock_gettime()`
- Memperbaiki masalah server mode PROCESS gagal mengirim saat kernel kekurangan blok memori besar

## v4.8.6

### Perbaikan

- Menambahkan prefiks untuk nama API boost/context
- Mengoptimalkan opsi konfigurasi

## v4.8.5

### Perbaikan

- Mengembalikan tipe parameter Table
- Memperbaiki crash saat menerima data yang salah menggunakan protokol Websocket

## v4.8.4

### Perbaikan

- Memperbaiki kompatibilitas hook socket dengan PHP-8.1
- Memperbaiki kompatibilitas Table dengan PHP-8.1
- Memperbaiki masalah parsing parameter `POST` dengan `Content-Type` `application/x-www-form-urlencoded` di server HTTP gaya coroutine yang tidak sesuai harapan dalam beberapa kasus

## v4.8.3

### API Baru

- Menambahkan metode `Coroutine\Socket::isClosed()`

### Perbaikan

- Memperbaiki masalah kompatibilitas curl native hook di php8.1
- Memperbaiki masalah kompatibilitas socket hook di php8
- Memperbaiki nilai kembali fungsi socket hook yang salah
- Memperbaiki masalah Http2Server sendfile tidak bisa mengatur content-type
- Mengoptimalkan kinerja date header HttpServer, menambahkan cache

## v4.8.2

### Perbaikan

- Memperbaiki masalah kebocoran memori hook `proc_open`
- Memperbaiki masalah kompatibilitas curl native hook dengan PHP-8.0 dan PHP-8.1
- Memperbaiki masalah tidak bisa menutup koneksi secara normal di proses Manager
- Memperbaiki masalah proses Manager tidak bisa menggunakan `sendMessage`
- Memperbaiki masalah parsing data POST sangat besar yang abnormal di `Coroutine\Http\Server`
- Memperbaiki masalah tidak bisa keluar langsung saat fatal error di lingkungan PHP 8
- Menyesuaikan konfigurasi coroutine `max_concurrency`, hanya diizinkan digunakan di `Co::set()`
- Menyesuaikan `Coroutine::join()` untuk mengabaikan coroutine yang tidak ada

## v4.8.1

### API Baru

- Menambahkan fungsi `swoole_error_log_ex()` dan `swoole_ignore_error()` (#4440) (@matyhtf)

### Peningkatan

- Memigrasikan admin API di ext-swoole_plus ke ext-swoole (#4441) (@matyhtf)
- Admin server menambahkan perintah get_composer_packages (swoole/library@07763f46) (swoole/library@8805dc05) (swoole/library@175f1797) (@sy-records) (@yunbaoi)
- Menambahkan batasan permintaan metode POST untuk operasi tulis (swoole/library@ac16927c) (@yunbaoi)
- Admin server mendukung mendapatkan informasi metode kelas (swoole/library@690a1952) (@djw1028769140) (@sy-records)
- Mengoptimalkan kode admin server (swoole/library#128) (swoole/library#131) (@sy-records)
- Admin server mendukung permintaan konkuren ke banyak target dan API (swoole/library#124) (@sy-records)
- Admin server mendukung mendapatkan informasi antarmuka (swoole/library#130) (@sy-records)
- SWOOLE_HOOK_CURL mendukung CURLOPT_HTTPPROXYTUNNEL (swoole/library#126) (@sy-records)

### Perbaikan

- Metode `join` melarang pemanggilan konkuren pada coroutine yang sama (#4442) (@matyhtf)
- Memperbaiki masalah pelepasan kunci atomik Table yang tidak terduga (#4446) (@Txhua) (@matyhtf)
- Memperbaiki opsi helper yang hilang (swoole/library#123) (@sy-records)
- Memperbaiki parameter perintah `get_static_property_value` yang salah (swoole/library#129) (@sy-records)

## v4.8.0

### Perubahan Tidak Kompatibel

- Dalam mode base, callback `onStart` akan selalu dipicu saat proses pekerja pertama (worker id 0) dimulai, mendahului eksekusi `onWorkerStart` (#4389) (@matyhtf)

### API Baru

- Menambahkan metode `Co::getStackUsage()` (#4398) (@matyhtf) (@twose)
- Menambahkan beberapa API `Coroutine\Redis` (#4390) (@chrysanthemum)
- Menambahkan metode `Table::stats()` (#4405) (@matyhtf)
- Menambahkan metode `Coroutine::join()` (#4406) (@matyhtf)

### Fitur Baru

- Mendukung perintah server (#4389) (@matyhtf)
- Mendukung callback event `Server::onBeforeShutdown` (#4415) (@matyhtf)

### Peningkatan

- Mengatur kode error saat Websocket pack gagal (swoole/swoole-src@d27c5a5) (@matyhtf)
- Menambahkan field `Timer::exec_count` (#4402) (@matyhtf)
- Hook mkdir mendukung penggunaan konfigurasi ini open_basedir (#4407) (@NathanFreeman)
- Menambahkan skrip vendor_init.php ke library (swoole/library@6c40b02) (@matyhtf)
- SWOOLE_HOOK_CURL mendukung CURLOPT_UNIX_SOCKET_PATH (swoole/library#121) (@sy-records)
- Client mendukung pengaturan item konfigurasi ssl_ciphers (#4432) (@amuluowin)
- Menambahkan informasi baru ke `Server::stats()` (#4410) (#4412) (@matyhtf)

### Perbaikan

- Memperbaiki URL decode yang tidak perlu pada nama file saat upload file (swoole/swoole-src@a73780e) (@matyhtf)
- Memperbaiki masalah HTTP2 max_frame_size (#4394) (@twose)
- Memperbaiki bug curl_multi_select #4393 (#4418) (@matyhtf)
- Memperbaiki opsi coroutine yang hilang (#4425) (@sy-records)
- Memperbaiki masalah koneksi tidak bisa ditutup saat buffer pengiriman penuh (swoole/swoole-src@2198378) (@matyhtf)

## v4.7.1

### Peningkatan

- `System::dnsLookup` mendukung query `/etc/hosts` (#4341) (#4349) (@zmyWL) (@NathanFreeman)
- Menambahkan dukungan boost context untuk mips64 (#4358) (@dixyes)
- `SWOOLE_HOOK_CURL` mendukung opsi `CURLOPT_RESOLVE` (swoole/library#107) (@sy-records)
- `SWOOLE_HOOK_CURL` mendukung opsi `CURLOPT_NOPROGRESS` (swoole/library#117) (@sy-records)
- Menambahkan dukungan boost context untuk riscv64 (#4375) (@dixyes)

### Perbaikan

- Memperbaiki error memori yang terjadi di PHP-8.1 pada shutdown (#4325) (@twose)
- Memperbaiki kelas yang tidak dapat diserialisasi di 8.1.0beta1 (#4335) (@remicollet)
- Memperbaiki masalah pembuatan direktori rekursif di banyak coroutine gagal (#4337) (@NathanFreeman)
- Memperbaiki masalah timeout sesekali dengan native curl saat mengirim file besar, dan masalah crash saat menggunakan API file coroutine di CURL WRITEFUNCTION (#4360) (@matyhtf)
- Memperbaiki masalah `PDOStatement::bindParam()` mengharapkan parameter 1 berupa string (swoole/library#116) (@sy-records)

## v4.7.0

### API Baru

- Menambahkan metode `Process\Pool::detach()` (#4221) (@matyhtf)
- `Server` mendukung fungsi callback `onDisconnect` (#4230) (@matyhtf)
- Menambahkan metode `Coroutine::cancel()` dan `Coroutine::isCanceled()` (#4247) (#4249) (@matyhtf)
- `Http\Client` mendukung opsi `http_compression` dan `body_decompression` (#4299) (@matyhtf)

### Peningkatan

- Mendukung klien MySQL coroutine untuk mengetik field secara ketat saat `prepare` (#4238) (@Yurunsoft)
- DNS mendukung library `c-ares` (#4275) (@matyhtf)
- `Server` mendukung konfigurasi deteksi heartbeat untuk port yang berbeda saat mendengarkan multi-port (#4290) (@matyhtf)
- `dispatch_mode` `Server` mendukung mode `SWOOLE_DISPATCH_CO_CONN_LB` dan `SWOOLE_DISPATCH_CO_REQ_LB` (#4318) (@matyhtf)
- `ConnectionPool::get()` mendukung parameter `timeout` (swoole/library#108) (@leocavalcante)
- Hook Curl mendukung opsi `CURLOPT_PRIVATE` (swoole/library#112) (@sy-records)
- Mengoptimalkan deklarasi fungsi metode `PDOStatementProxy::setFetchMode()` (swoole/library#109) (@yespire)

### Perbaikan

- Memperbaiki masalah exception saat membuat banyak coroutine menggunakan thread context (8ce5041) (@matyhtf)
- Memperbaiki masalah kehilangan file header php_swoole.h saat menginstal Swoole (#4239) (@sy-records)
- Memperbaiki masalah kompatibilitas mundur EVENT_HANDSHAKE (#4248) (@sy-records)
- Memperbaiki masalah makro SW_LOCK_CHECK_RETURN yang mungkin memanggil fungsi dua kali (#4302) (@zmyWL)
- Memperbaiki masalah `Atomic\Long` pada chip M1 (e6fae2e) (@matyhtf)
- Memperbaiki masalah kehilangan nilai kembali di `Coroutine\go()` (swoole/library@1ed49db) (@matyhtf)
- Memperbaiki masalah tipe nilai kembali `StringObject` (swoole/library#111) (swoole/library#113) (@leocavalcante) (@sy-records)

### Kernel

- Melarang hook fungsi yang sudah dinonaktifkan oleh PHP (#4283) (@twose)

### Pengujian

- Menambahkan build di lingkungan `Cygwin` (#4222) (@sy-records)
- Menambahkan tes kompilasi untuk `alpine 3.13` dan `3.14` (#4309) (@limingxinleo)

## v4.6.7

### Peningkatan

- Proses Manager dan proses Task sinkron mendukung pemanggilan fungsi `Process::signal()` (#4190) (@matyhtf)

### Perbaikan

- Memperbaiki masalah sinyal tidak bisa didaftarkan berulang kali (#4170) (@matyhtf)
- Memperbaiki kegagalan kompilasi di OpenBSD/NetBSD (#4188) (#4194) (@devnexen)
- Memperbaiki masalah kehilangan event `onClose` dalam kasus khusus saat mendengarkan event tulis (#4204) (@matyhtf)
- Memperbaiki masalah Symfony HttpClient menggunakan native curl (#4204) (@matyhtf)
- Memperbaiki masalah metode `Http\Response::end()` selalu mengembalikan true (swoole/swoole-src@66fcc35) (@matyhtf)
- Memperbaiki PDOStatementProxy menghasilkan PDOException (swoole/library#104) (@twose)

### Kernel

- Menulis ulang worker buffer, menambahkan flag msg id ke event data (#4163) (@matyhtf)
- Mengubah level log Request Entity Too Large menjadi warning (#4175) (@sy-records)
- Mengganti fungsi inet_ntoa dan inet_aton (#4199) (@remicollet)
- Mengubah nilai default output_buffer_size menjadi UINT_MAX (swoole/swoole-src@46ab345) (@matyhtf)

## v4.6.6

### Peningkatan

- Mendukung pengiriman sinyal SIGTERM ke proses Manager setelah proses Master keluar di FreeBSD (#4150) (@devnexen)
- Mendukung kompilasi statis Swoole ke dalam PHP (#4153) (@matyhtf)
- Mendukung SNI menggunakan proxy HTTP (#4158) (@matyhtf)

### Perbaikan

- Memperbaiki error koneksi asinkron klien sinkron (#4152) (@matyhtf)
- Memperbaiki kebocoran memori yang disebabkan oleh Hook native curl multi (swoole/swoole-src@91bf243) (@matyhtf)

## v4.6.5

### API Baru

- Menambahkan metode `count` di WaitGroup (swoole/library#100) (@sy-records) (@deminy)

### Peningkatan

- Mendukung native curl multi (#4093) (#4099) (#4101) (#4105) (#4113) (#4121) (#4147) (swoole/swoole-src@cd7f51c) (@matyhtf) (@sy-records) (@huanghantao)
- Mengizinkan pengaturan header menggunakan array di Response dengan HTTP/2

### Perbaikan

- Memperbaiki build NetBSD (#4080) (@devnexen)
- Memperbaiki build OpenBSD (#4108) (@devnexen)
- Memperbaiki build illumos/solaris, hanya alias anggota (#4109) (@devnexen)
- Memperbaiki kasus di mana deteksi heartbeat koneksi SSL tidak efektif saat handshake belum selesai (#4114) (@matyhtf)
- Memperbaiki error di Http\Client saat menggunakan proxy dengan `host:port` di `host` (#4124) (@Yurunsoft)
- Memperbaiki pengaturan header dan cookie di Swoole\Coroutine\Http::request (swoole/library#103) (@leocavalcante) (@deminy)

### Kernel

- Mendukung asm context di BSD (#4082) (@devnexen)
- Menggunakan arc4random_buf untuk mengimplementasikan getrandom di FreeBSD (#4096) (@devnexen)
- Mengoptimalkan konteks darwin arm64: menghapus workaround menggunakan label (#4127) (@devnexen)

### Pengujian

- Menambahkan skrip build untuk alpine (#4104) (@limingxinleo)

## v4.6.4

### API Baru

- Menambahkan fungsi Coroutine\Http::request, Coroutine\Http::post, Coroutine\Http::get (swoole/library#97) (@matyhtf)

### Peningkatan

- Menambahkan dukungan build ARM 64 (#4057) (@devnexen)
- Menambahkan dukungan pengaturan open_http_protocol di server TCP Swoole (#4063) (@matyhtf)
- Menambahkan dukungan pengaturan hanya sertifikat untuk klien SSL (91704ac) (@matyhtf)
- Menambahkan dukungan untuk opsi tcp_defer_accept di FreeBSD (#4049) (@devnexen)

### Perbaikan

- Memperbaiki masalah kurangnya otorisasi proxy saat menggunakan Coroutine\Http\Client (edc0552) (@matyhtf)
- Memperbaiki masalah alokasi memori di Swoole\Table (3e7770f) (@matyhtf)
- Memperbaiki masalah crash saat menggunakan Coroutine\Http2\Client untuk koneksi konkuren (630536d) (@matyhtf)
- Memperbaiki masalah enable_ssl_encrypt di DTLS (842733b) (@matyhtf)
- Memperbaiki kebocoran memori di Coroutine\Barrier (swoole/library#94) (@Appla) (@FMiS)
- Memperbaiki error offset yang disebabkan oleh urutan CURLOPT_PORT dan CURLOPT_URL (swoole/library#96) (@sy-records)
- Memperbaiki error di `Table::get($key, $field)` saat tipe field adalah float (08ea20c) (@matyhtf)
- Memperbaiki kebocoran memori di Swoole\Table (d78ca8c) (@matyhtf)

## v4.4.24

### Perbaikan

- Memperbaiki crash saat koneksi konkuren di klien http2 (#4079)

## v4.6.3

### API Baru

- Menambahkan fungsi Swoole\Coroutine\go (swoole/library@82f63be) (@matyhtf)
- Menambahkan fungsi Swoole\Coroutine\defer (swoole/library@92fd0de) (@matyhtf)

### Peningkatan

- Menambahkan opsi compression_min_length untuk server HTTP (#4033) (@matyhtf)
- Mengizinkan pengaturan header HTTP Content-Length di lapisan aplikasi (#4041) (@doubaokun)

### Perbaikan

- Memperbaiki coredump saat program mencapai batas buka file (swoole/swoole-src@709813f) (@matyhtf)
- Memperbaiki masalah JIT dinonaktifkan (#4029) (@twose)
- Memperbaiki error parameter di `Response::create()` (swoole/swoole-src@a630b5b) (@matyhtf)
- Memperbaiki pelaporan yang salah `task_worker_id` saat mengirim task di platform ARM (#4040) (@doubaokun)
- Memperbaiki masalah coredump saat mengaktifkan native curl hook di PHP8 (#4042)(#4045) (@Yurunsoft) (@matyhtf)
- Memperbaiki error memori di luar batas pada fase shutdown saat fatal error (#4050) (@matyhtf)

### Kernel

- Mengoptimalkan ssl_connect/ssl_shutdown (#4030) (@matyhtf)
- Keluar dari proses langsung saat fatal error (#4053) (@matyhtf)

## v4.6.2

### API Baru

- Menambahkan metode `Http\Request\getMethod()` (#3987) (@luolaifa000)
- Menambahkan metode `Coroutine\Socket->recvLine()` (#4014) (@matyhtf)
- Menambahkan metode `Coroutine\Socket->readWithBuffer()` (#4017) (@matyhtf)

### Peningkatan

- Meningkatkan metode `Response\create()` agar dapat digunakan independen dari Server (#3998) (@matyhtf)
- Mendukung `Coroutine\Redis->hExists` mengembalikan tipe bool setelah mengatur compatibility_mode (swoole/swoole-src@b8cce7c) (@matyhtf)
- Mendukung pengaturan opsi PHP_NORMAL_READ untuk `socket_read` (swoole/swoole-src@b1a0dcc) (@matyhtf)

### Perbaikan

- Memperbaiki masalah coredump `Coroutine::defer` di PHP8 (#3997) (@huanghantao)
- Memperbaiki pengaturan `Coroutine\Socket::errCode` yang salah saat menggunakan thread context (swoole/swoole-src@004d08a) (@matyhtf)
- Memperbaiki kegagalan kompilasi Swoole di macOS terbaru (#4007) (@matyhtf)
- Memperbaiki masalah passing URL sebagai parameter ke `md5_file` menyebabkan null pointer di PHP stream context (#4016) (@ZhiyangLeeCN)

### Kernel

- Menggunakan AIO thread pool untuk hook stdio (menyelesaikan masalah perlakuan stdio sebagai socket yang menyebabkan masalah multi-coroutine) (#4002) (@matyhtf)
- Menulis ulang HttpContext (#3998) (@matyhtf)
- Menulis ulang `Process::wait()` (#4019) (@matyhtf)

## v4.6.1

### Peningkatan

- Menambahkan opsi kompilasi `--enable-thread-context` (#3970) (@matyhtf)
- Memeriksa keberadaan koneksi saat beroperasi pada session_id (#3993) (@matyhtf)
- Meningkatkan CURLOPT_PROXY (swoole/library#87) (@sy-records)

### Perbaikan

- Memperbaiki versi PHP minimum dalam instalasi pecl (#3979) (@remicollet)
- Memperbaiki opsi `--enable-swoole-json` dan `--enable-swoole-curl` yang hilang dalam instalasi pecl (#3980) (@sy-records)
- Memperbaiki masalah keamanan thread openssl (b516d69f) (@matyhtf)
- Memperbaiki enableSSL coredump (#3990) (@huanghantao)

### Kernel

- Mengoptimalkan ipc writev untuk menghindari coredump saat data event kosong (9647678) (@matyhtf)

## v4.5.11

### Peningkatan

- Mengoptimalkan Swoole\Table (#3959) (@matyhtf)
- Meningkatkan CURLOPT_PROXY (swoole/library#87) (@sy-records)

### Perbaikan

- Memperbaiki masalah semua kolom tidak bisa dibersihkan saat Table ditambah dan dikurangi (#3956) (@matyhtf) (@sy-records)
- Memperbaiki error `clock_id_t` selama kompilasi (49fea171) (@matyhtf)
- Memperbaiki bug fread (#3972) (@matyhtf)
- Memperbaiki crash multi-thread ssl (7ee2c1a0) (@matyhtf)
- Kompatibel dengan format uri yang salah menyebabkan error (swoole/library#80) (@sy-records)
- Memperbaiki error parameter trigger_error (swoole/library#86) (@sy-records)

## v4.6.0

### Perubahan Tidak Kompatibel

- Menghapus batas maksimum `session id` untuk menghindari duplikasi (#3879) (@matyhtf)
- Menonaktifkan fitur tidak aman saat menggunakan coroutine, termasuk `pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait` (#3880) (@matyhtf)
- Mengaktifkan coroutine hook secara default (#3903) (@matyhtf)

### Penghapusan

- Tidak lagi mendukung PHP7.1 (4a963df) (9de8d9e) (@matyhtf)

### Tidak Lagi Didukung

- Menandai `Event::rshutdown()` sebagai tidak digunakan lagi, gunakan `Coroutine\run` sebagai gantinya (#3881) (@matyhtf)

### API Baru

- Mendukung setPriority/getPriority (#3876) (@matyhtf)
- Mendukung native-curl hook (#3863) (@matyhtf) (@huanghantao)
- Mendukung passing parameter gaya objek dalam fungsi callback event Server secara default (#3888) (@matyhtf)
- Mendukung hook ekstensi sockets (#3898) (@matyhtf)
- Mendukung header duplikat (#3905) (@matyhtf)
- Mendukung SSL sni (#3908) (@matyhtf)
- Mendukung hook stdio (#3924) (@matyhtf)
- Mendukung opsi capture_peer_cert dari stream_socket (#3930) (@matyhtf)
- Menambahkan Http\Request::create/parse/isCompleted (#3938) (@matyhtf)
- Menambahkan Http\Response::isWritable (db56827) (@matyhtf)

### Peningkatan

- Semua akurasi waktu Server diubah dari int menjadi double (#3882) (@matyhtf)
- Memeriksa situasi EINTR fungsi poll di swoole_client_select (#3909) (@shiguangqi)
- Menambahkan deteksi deadlock coroutine (#3911) (@matyhtf)
- Mendukung penutupan koneksi di proses lain menggunakan mode SWOOLE_BASE (#3916) (@matyhtf)
- Mengoptimalkan kinerja komunikasi antara proses master Server dan worker, mengurangi penyalinan memori (#3910) (@huanghantao) (@matyhtf)

### Perbaikan

- Saat Coroutine\Channel ditutup, pop semua data di dalamnya (960431d) (@matyhtf)
- Memperbaiki error memori saat menggunakan JIT (#3907) (@twose)
- Memperbaiki error kompilasi `port->set()` dtls (#3947) (@Yurunsoft)
- Memperbaiki error connection_list (#3948) (@sy-records)
- Memperbaiki ssl verify (#3954) (@matyhtf)
- Memperbaiki masalah Table tidak dapat membersihkan semua kolom saat increment dan decrement (#3956) (@matyhtf) (@sy-records)
- Memperbaiki kegagalan kompilasi dengan LibreSSL 2.7.5 (#3962) (@matyhtf)
- Memperbaiki konstanta tidak terdefinisi CURLOPT_HEADEROPT dan CURLOPT_PROXYHEADER (swoole/library#77) (@sy-records)

### Kernel

- Mengabaikan sinyal SIGPIPE secara default (9647678) (@matyhtf)
- Mendukung menjalankan coroutine PHP dan coroutine C secara bersamaan (c94bfd8) (@matyhtf)
- Menambahkan tes get_elapsed (#3961) (@luolaifa000)
- Menambahkan tes get_init_msec (#3964) (@luffluo)

## v4.5.10

### Perbaikan

- Memperbaiki coredump yang disebabkan oleh Event::cycle (93901dc) (@matyhtf)
- Kompatibel dengan PHP8 (f0dc6d3) (@matyhtf)
- Memperbaiki error connection_list (#3948) (@sy-records)

## v4.4.23

### Perbaikan

- Memperbaiki error data saat Swoole\Table decrement (bcd4f60d)(0d5e72e7) (@matyhtf)
- Memperbaiki pesan error klien sinkron (#3784)
- Memperbaiki masalah overflow memori saat parsing batas data form (#3858)
- Memperbaiki bug channel di mana data yang ada tidak bisa di-pop setelah ditutup

## v4.5.9

### Peningkatan

- Menambahkan konstanta SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED ke Coroutine\Http\Client (#3873) (@sy-records)

### Perbaikan

- Kompatibel dengan PHP8 (#3868) (#3869) (#3872) (@twose) (@huanghantao) (@doubaokun)
- Memperbaiki konstanta tidak terdefinisi CURLOPT_HEADEROPT dan CURLOPT_PROXYHEADER (swoole/library#77) (@sy-records)
- Memperbaiki CURLOPT_USERPWD (swoole/library@7952a7b) (@twose)

## v4.5.8

### API Baru

- Menambahkan fungsi swoole_error_log, mengoptimalkan log_rotation (swoole/swoole-src@67d2bff) (@matyhtf)
- readVector dan writeVector mendukung SSL (#3857) (@huanghantao)

### Peningkatan

- Saat proses anak keluar, biarkan System::wait keluar dari blocking (#3832) (@matyhtf)
- DTLS mendukung paket 16K (#3849) (@matyhtf)
- Metode Response::cookie mendukung parameter priority (#3854) (@matyhtf)
- Mendukung lebih banyak opsi CURL (swoole/library#71) (@sy-records)
- Menangani masalah header HTTP CURL yang tidak membedakan huruf besar/kecil menyebabkan overwrite (swoole/library#76) (@filakhtov) (@twose) (@sy-records)

### Perbaikan

- Memperbaiki masalah penanganan error EAGAIN di readv_all dan writev_all (#3830) (@huanghantao)
- Memperbaiki peringatan kompilasi PHP8 (swoole/swoole-src@03f3fb0) (@matyhtf)
- Memperbaiki masalah keamanan biner di Swoole\Table (#3842) (@twose)
- Memperbaiki masalah overwrite file dalam mode append untuk System::writeFile di MacOS (swoole/swoole-src@a71956d) (@matyhtf)
- Memperbaiki masalah dengan CURLOPT_WRITEFUNCTION di CURL (swoole/library#74) (swoole/library#75) (@sy-records)
- Memperbaiki masalah overflow memori saat parsing HTTP form-data (#3858) (@twose)
- Memperbaiki masalah di mana `is_callable()` tidak dapat mengakses metode privat kelas di PHP8 (#3859) (@twose)

### Kernel

- Menulis ulang fungsi alokasi memori, menggunakan SwooleG.std_allocator (#3853) (@matyhtf)
- Menulis ulang pipeline (#3841) (@matyhtf)

## v4.5.7

### API Baru

- Menambahkan metode writeVector, writeVectorAll, readVector, readVectorAll ke klien Coroutine\Socket (#3764) (@huanghantao)

### Peningkatan

- Menambahkan task_worker_num dan dispatch_count ke server->stats (#3771) (#3806) (@sy-records) (@matyhtf)
- Menambahkan dependensi ekstensi termasuk json, mysqlnd, sockets (#3789) (@remicollet)
- Membatasi nilai minimum uid untuk server->bind ke INT32_MIN (#3785) (@sy-records)
- Menambahkan opsi kompilasi untuk swoole_substr_json_decode untuk mendukung offset negatif (#3809) (@matyhtf)
- Mendukung opsi CURLOPT_TCP_NODELAY untuk CURL (swoole/library#65) (@sy-records) (@deminy)

### Perbaikan

- Memperbaiki informasi koneksi klien sinkron yang salah (#3784) (@twose)
- Memperbaiki masalah hook fungsi scandir (#3793) (@twose)
- Memperbaiki error di coroutine barrier (swoole/library#68) (@sy-records)

### Kernel

- Menggunakan boost.stacktrace untuk mengoptimalkan print-backtrace (#3788) (@matyhtf)

## v4.5.6

### API Baru

- Menambahkan [swoole_substr_unserialize](/functions?id=swoole_substr_unserialize) dan [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode) (#3762) (@matyhtf)

### Peningkatan

- Mengubah metode `onAccept` dari `Coroutine\Http\Server` menjadi private (dfcc83b) (@matyhtf)

### Perbaikan

- Memperbaiki masalah coverity (#3737) (#3740) (@matyhtf)
- Memperbaiki beberapa masalah di lingkungan Alpine (#3738) (@matyhtf)
- Memperbaiki swMutex_lockwait (0fc5665) (@matyhtf)
- Memperbaiki kegagalan instalasi PHP 8.1 (#3757) (@twose)

### Kernel

- Menambahkan deteksi liveness untuk `Socket::read/write/shutdown` (#3735) (@matyhtf)
- Mengubah tipe session_id dan task_id menjadi int64 (#3756) (@matyhtf)

## v4.5.5

!> Versi ini menambahkan fitur deteksi [opsi konfigurasi](/server/setting). Jika opsi yang tidak disediakan oleh Swoole diatur, Warning akan dihasilkan.

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->set(['foo' => 'bar']);

$http->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

$http->start();
```

### API Baru

- Menambahkan Process\Manager, mengubah Process\ProcessManager menjadi alias (swoole/library#eac1ac5) (@matyhtf)
- Mendukung server HTTP2 GOAWAY (#3710) (@doubaokun)
- Menambahkan fungsi `Co\map()` (swoole/library#57) (@leocavalcante)

### Peningkatan

- Mendukung klien http2 unix socket (#3668) (@sy-records)
- Mengatur status proses pekerja menjadi SW_WORKER_EXIT setelah proses pekerja keluar (#3724) (@matyhtf)
- Menambahkan send_queued_bytes dan recv_queued_bytes ke nilai kembali `Server::getClientInfo()` (#3721) (#3731) (@matyhtf) (@Yurunsoft)
- Server mendukung opsi konfigurasi stats_file (#3725) (@matyhtf) (@Yurunsoft)

### Perbaikan

- Memperbaiki masalah kompilasi di PHP8 (zend_compile_string change) (#3670) (@twose)
- Memperbaiki masalah kompilasi di PHP8 (ext/sockets compatibility) (#3684) (@twose)
- Memperbaiki masalah kompilasi di PHP8 (php_url_encode_hash_ex change) (#3713) (@remicollet)
- Memperbaiki konversi tipe error dari 'const char*' ke 'char*' (#3686) (@remicollet)
- Memperbaiki masalah klien HTTP2 tidak berfungsi di bawah proxy HTTP (#3677) (@matyhtf) (@twose)
- Memperbaiki masalah korupsi data saat PDO reconnect (swoole/library#54) (@sy-records)
- Memperbaiki error parsing port untuk server UDP menggunakan ipv6
- Memperbaiki masalah timeout tidak valid untuk Lock::lockwait

## v4.5.4

### Perubahan Tidak Kompatibel

- SWOOLE_HOOK_ALL termasuk SWOOLE_HOOK_CURL (#3606) (@matyhtf)
- Menghapus ssl_method, menambahkan ssl_protocols (#3639) (@Yurunsoft)

### API Baru

- Menambahkan metode firstKey dan lastKey untuk array (swoole/library#51) (@sy-records)

### Peningkatan

- Menambahkan item konfigurasi open_websocket_ping_frame, open_websocket_pong_frame untuk server Websocket (#3600) (@Yurunsoft)

### Perbaikan

- Memperbaiki masalah fseek ftell yang salah saat ukuran file lebih besar dari 2G (#3619) (@Yurunsoft)
- Memperbaiki masalah Socket barrier (#3627) (@matyhtf)
- Memperbaiki masalah http proxy handshake (#3630) (@matyhtf)
- Memperbaiki masalah parsing HTTP Header error saat menerima data chunk dari peer (#3633) (@matyhtf)
- Memperbaiki masalah kegagalan asersi zend_hash_clean (#3634) (@twose)
- Memperbaiki masalah tidak bisa menghapus broken fd dari event loop (#3650) (@matyhtf)
- Memperbaiki masalah coredump akibat menerima paket tidak valid (#3653) (@matyhtf)
- Memperbaiki bug array_key_last (swoole/library#46) (@sy-records)

### Kernel

- Optimasi kode (#3615) (#3617) (#3622) (#3635) (#3640) (#3641) (#3642) (#3645) (#3658) (@matyhtf)
- Mengurangi operasi memori yang tidak perlu saat menulis data ke Swoole Table (#3620) (@matyhtf)
- Menulis ulang AIO (#3624) (@Yurunsoft)
- Mendukung readlink/opendir/readdir/closedir hook (#3628) (@matyhtf)
- Mengoptimalkan swMutex_create, mendukung SW_MUTEX_ROBUST (#3646) (@matyhtf)

## v4.5.3

### API Baru

- Menambahkan `Swoole\Process\ProcessManager` (swoole/library#88f147b) (@huanghantao)
- Menambahkan ArrayObject::append, StringObject::equals (swoole/library#f28556f) (@matyhtf)
- Menambahkan [Coroutine::parallel](/coroutine/coroutine?id=parallel) (swoole/library#6aa89a9) (@matyhtf)
- Menambahkan [Coroutine\Barrier](/coroutine/barrier) (swoole/library#2988b2a) (@matyhtf)

### Peningkatan

- Menambahkan `usePipelineRead` untuk mendukung streaming klien http2 (#3354) (@twose)
- Saat mengunduh file dengan klien http, jangan buat file sebelum menerima data (#3381) (@twose)
- Klien http mendukung konfigurasi `bind_address` dan `bind_port` (#3390) (@huanghantao)
- Klien http mendukung konfigurasi `lowercase_header` (#3399) (@matyhtf)
- `Swoole\Server` mendukung konfigurasi `tcp_user_timeout` (#3404) (@huanghantao)
- `Coroutine\Socket` menambahkan event barrier untuk mengurangi peralihan coroutine (#3409) (@matyhtf)
- Menambahkan `memory allocator` untuk swString tertentu (#3418) (@matyhtf)
- cURL mendukung `__toString` (swoole/library#38) (@twose)
- Mendukung pengaturan `wait count` langsung di konstruktor WaitGroup (swoole/library#2fb228b8) (@matyhtf)
- Menambahkan `CURLOPT_REDIR_PROTOCOLS` (swoole/library#46) (@sy-records)
- Server Http1.1 mendukung trailer (#3485) (@huanghantao)
- Coroutine dengan waktu tidur kurang dari 1ms akan menyerahkan coroutine saat ini (#3487) (@Yurunsoft)
- Http static handler mendukung file symbolic linked (#3569) (@LeiZhang-Hunter)
- Segera tutup koneksi WebSocket setelah memanggil metode close di Server (#3570) (@matyhtf)
- Mendukung hook stream_set_blocking (#3585) (@Yurunsoft)
- Server HTTP2 asinkron mendukung flow control (#3486) (@huanghantao) (@matyhtf)
- Melepaskan buffer socket setelah menjalankan fungsi callback onPackage (#3551) (@huanghantao) (@matyhtf)

### Perbaikan

- Memperbaiki WebSocket coredump, menangani status error protokol (#3359) (@twose)
- Memperbaiki error null pointer di fungsi swSignalfd_setup dan wait_signal (#3360) (@twose)
- Memperbaiki masalah pemanggilan `Swoole\Server::close` dengan dispatch_func menyebabkan error (#3365) (@twose)
- Memperbaiki masalah inisialisasi di fungsi format `Swoole\Redis\Server::format` di format_buffer (#3369) (@matyhtf) (@twose)
- Memperbaiki masalah mendapatkan alamat MAC di MacOS (#3372) (@twose)
- Memperbaiki kasus uji MySQL (#3374) (@qiqizjl)
- Memperbaiki beberapa masalah kompatibilitas PHP8 (#3384) (#3458) (#3578) (#3598) (@twose)
- Memperbaiki php_error_docref yang hilang, timeout_event, dan masalah nilai kembali di socket write hook (#3383) (@twose)
- Memperbaiki masalah server asinkron tidak bisa menutup Server di fungsi callback `WorkerStart` (#3382) (@huanghantao)
- Memperbaiki masalah coredump potensial di thread heartbeat saat memanipulasi conn->socket (#3396) (@huanghantao)
- Memperbaiki masalah logika di send_yield (#3397) (@twose) (@matyhtf)
- Memperbaiki masalah kompilasi di Cygwin64 (#3400) (@twose)
- Memperbaiki atribut finish yang tidak valid di WebSocket (#3410) (@matyhtf)
- Memperbaiki status error transaksi MySQL yang hilang (#3429) (@twose)
- Memperbaiki perilaku `stream_select` yang tidak konsisten setelah hook (#3440) (@Yurunsoft)
- Memperbaiki masalah sinyal `SIGCHLD` hilang saat membuat proses anak dengan `Coroutine\System` (#3446) (@huanghantao)
- Memperbaiki masalah dukungan SSL di `sendwait` (#3459) (@huanghantao)
- Memperbaiki beberapa masalah di `ArrayObject` dan `StringObject` (swoole/library#44) (@matyhtf)
- Memperbaiki informasi exception mysqli yang salah (swoole/library#45) (@sy-records)
- Memperbaiki masalah `Swoole\Client` tidak bisa mendapatkan `errCode` yang benar setelah mengatur `open_eof_check` (#3478) (@huanghantao)
- Memperbaiki berbagai masalah di MacOS dengan `atomic->wait()`/`wakeup()` (#3476) (@Yurunsoft)
- Memperbaiki masalah status berhasil dikembalikan saat `Client::connect` ditolak (#3484) (@matyhtf)
- Memperbaiki masalah nullptr_t tidak dideklarasikan di lingkungan alpine (#3488) (@limingxinleo)
- Memperbaiki masalah double-free saat mengunduh file di HTTP Client (#3489) (@Yurunsoft)
- Memperbaiki masalah kebocoran memori yang disebabkan oleh tidak dirilisnya `Server\Port` saat `Server` dihancurkan (#3507) (@twose)
- Memperbaiki masalah parsing protokol MQTT (318e33a) (84d8214) (80327b3) (efe6c63) (@GXhua) (@sy-records)
- Memperbaiki masalah coredump yang disebabkan oleh metode `Coroutine\Http\Client->getHeaderOut` (#3534) (@matyhtf)
- Memperbaiki kehilangan pesan error setelah kegagalan verifikasi SSL (#3535) (@twose)
- Memperbaiki tautan yang salah di README untuk `Swoole benchmark` (#3536) (@sy-records) (@santalex)
- Memperbaiki masalah injeksi header menggunakan `CRLF` di `HTTP header/cookie` (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)
- Memperbaiki error variabel yang disebutkan di issue #3463 (#3547) (chromium1337) (@huanghantao)
- Memperbaiki typo yang disebutkan di pr #3463 (#3547) (@deminy)
- Memperbaiki masalah frame->fd kosong di server WebSocket coroutine (#3549) (@huanghantao)
- Memperbaiki masalah kebocoran koneksi yang disebabkan oleh penilaian salah di thread heartbeat mengenai status koneksi (#3534) (@matyhtf)
- Memperbaiki masalah blocking sinyal di `Process\Pool` (#3582) (@huanghantao) (@matyhtf)
- Memperbaiki masalah di `SAPI` saat menggunakan send headers (#3571) (@twose) (@sshymko)
- Memperbaiki unset `errCode` dan `errMsg` saat eksekusi `CURL` gagal (swoole/library#1b6c65e) (@sy-records)
- Memperbaiki masalah coredump di `swoole_socket_coro` accept setelah memanggil metode `setProtocol` (#3591) (@matyhtf)

### Kernel

- Menggunakan gaya C++ (#3349) (#3351) (#3454) (#3479) (#3490) (@huanghantao) (@matyhtf)
- Menambahkan `Swoole known strings` untuk meningkatkan kinerja pembacaan properti objek `PHP` (#3363) (@huanghantao)
- Beberapa optimasi kode (#3350) (#3356) (#3357) (#3423) (#3426) (#3461) (#3463) (#3472) (#3557) (#3583) (@huanghantao) (@twose) (@matyhtf)
- Optimasi kode tes di beberapa tempat (#3416) (#3481) (#3558) (@matyhtf)
- Menyederhanakan tipe `int` dari `Swoole\Table` (#3407) (@matyhtf)
- Menambahkan `sw_memset_zero` dan mengganti fungsi `bzero` (#3419) (@CismonX)
- Mengoptimalkan modul log (#3432) (@matyhtf)
- Menulis ulang beberapa bagian libswoole (#3448) (#3473) (#3475) (#3492) (#3494) (#3497) (#3498) (#3526) (@matyhtf)
- Menulis ulang beberapa penyertaan file header (#3457) (@matyhtf) (@huanghantao)
- Menambahkan `Channel::count()` dan `Channel::get_bytes()` (f001581) (@matyhtf)
- Menambahkan `scope guard` (#3504) (@huanghantao)
- Menambahkan tes cakupan libswoole (#3431) (@huanghantao)
- Menambahkan tes untuk lib-swoole/ext-swoole di lingkungan MacOS (#3521) (@huanghantao)
- Menambahkan tes untuk lib-swoole/ext-swoole di lingkungan Alpine (#3537) (@limingxinleo)

## v4.5.2

[v4.5.2](https://github.com/swoole/swoole-src/releases/tag/v4.5.2), ini adalah versi perbaikan bug, tanpa perubahan yang tidak kompatibel.

### Peningkatan

- Mendukung `Server->set(['log_rotation' => SWOOLE_LOG_ROTATION_DAILY])` untuk menghasilkan log harian (#3311) (@matyhtf)
- Mendukung `swoole_async_set(['wait_signal' => true])`, reaktor tidak akan keluar saat ada pendengar sinyal (#3314) (@matyhtf)
- Mendukung `Server->sendfile` untuk mengirim file kosong (#3318) (@twose)
- Mengoptimalkan pesan peringatan worker idle/busy (#3328) (@huanghantao)
- Mengoptimalkan konfigurasi header Host di bawah proxy HTTPS (gunakan ssl_host_name untuk konfigurasi) (#3343) (@twose)
- SSL secara default menggunakan mode ecdh auto (#3316) (@matyhtf)
- Klien SSL menggunakan silent exit saat koneksi terputus (#3342) (@huanghantao)

### Perbaikan

- Memperbaiki masalah `Server->taskWait` di platform OSX (#3330) (@matyhtf)
- Memperbaiki bug dalam parsing protokol MQTT (8dbf506b) (@guoxinhua) (2ae8eb32) (@twose)
- Memperbaiki masalah overflow untuk tipe integer Content-Length (#3346) (@twose)
- Memperbaiki masalah pemeriksaan panjang paket PRI yang hilang (#3348) (@twose)
- Memperbaiki masalah CURLOPT_POSTFIELDS tidak bisa diatur ke kosong (swoole/library@ed192f64) (@twose)
- Memperbaiki masalah objek koneksi terbaru tidak bisa dirilis hingga koneksi berikutnya diterima (swoole/library@1ef79339) (@twose)

### Kernel

- Fitur penulisan zero-copy Socket (#3327) (@twose)
- Menggunakan swoole_get_last_error/swoole_set_last_error sebagai pengganti pembacaan/penulisan variabel global (e25f262a) (@matyhtf) (#3315) (@huanghantao)

## v4.5.1

[v4.5.1](https://github.com/swoole/swoole-src/releases/tag/v4.5.1) adalah versi perbaikan bug yang melengkapi tag tidak digunakan lagi yang seharusnya diperkenalkan di `v4.5.0`.

### Peningkatan

- Mendukung konfigurasi bindto di socket_context di bawah hook (#3275) (#3278) (@codinghuang)
- Mendukung resolusi DNS otomatis untuk alamat client::sendto (#3292) (@codinghuang)
- Process->exit(0) akan langsung menyebabkan proses keluar. Untuk menjalankan shutdown_functions sebelum keluar, gunakan exit yang disediakan oleh PHP (a732fe56) (@matyhtf)
- Mendukung konfigurasi `log_date_format` untuk mengubah format tanggal log, `log_date_with_microseconds` menampilkan mikrodetik di timestamp log (baf895bc) (@matyhtf)
- Mendukung CURLOPT_CAINFO dan CURLOPT_CAPATH (swoole/library#32) (@sy-records)
- Mendukung CURLOPT_FORBID_REUSE (swoole/library#33) (@sy-records)

### Perbaikan

- Memperbaiki kegagalan build di 32-bit (#3276) (#3277) (@remicollet) (@twose)
- Memperbaiki masalah kehilangan pesan error EISCONN saat Coroutine Client reconnect (#3280) (@codinghuang)
- Memperbaiki bug potensial di modul Table (d7b87b65) (@matyhtf)
- Memperbaiki masalah null pointer di Server karena perilaku tidak terdefinisi (defensive programming) (#3304) (#3305) (@twose)
- Memperbaiki masalah error null pointer yang dihasilkan setelah mengaktifkan konfigurasi heartbeat (#3307) (@twose)
- Memperbaiki konfigurasi mysqli tidak berfungsi (swoole/library#35)
- Memperbaiki masalah parsing saat respons mengandung header non-standar (spasi hilang) (swoole/library#27) (@Yurunsoft)

### Tidak Lagi Didukung

- Menandai metode seperti Coroutine\System::(fread/fgets/fwrite) sebagai tidak digunakan lagi (gunakan fitur hook sebagai gantinya, langsung menggunakan fungsi file yang disediakan oleh PHP) (c7c9bb40) (@twose)

### Kernel

- Menggunakan zend_object_alloc untuk mengalokasikan memori untuk objek kustom (cf1afb25) (@twose)
- Beberapa optimasi, menambahkan lebih banyak opsi konfigurasi untuk modul log (#3296) (@matyhtf)
- Banyak pekerjaan optimasi kode dan penambahan unit test (swoole/library) (@deminy)

## v4.5.0

[v4.5.0](https://github.com/swoole/swoole-src/releases/tag/v4.5.0), ini adalah pembaruan versi besar yang hanya menghapus beberapa modul yang sudah tidak digunakan lagi di v4.4.x.

### API Baru

- Menambahkan dukungan DTLS, sekarang fitur ini dapat digunakan untuk membangun aplikasi WebRTC (#3188) (@matyhtf)
- Klien `FastCGI` bawaan, dapat memproksi permintaan ke FPM atau memanggil aplikasi FPM menggunakan satu baris kode (swoole/library#17) (@twose)
- `Co::wait`, `Co::waitPid` (untuk memulihkan proses anak), `Co::waitSignal` (untuk menunggu sinyal) (#3158) (@twose)
- `Co::waitEvent` (untuk menunggu event tertentu pada socket) (#3197) (@twose)
- `Co::set(['exit_condition' => $callable])` (untuk menyesuaikan kondisi keluar program) (#2918) (#3012) (@twose)
- `Co::getElapsed` (mendapatkan waktu coroutine telah berjalan untuk analisis, statistik, atau menemukan zombie coroutine) (#3162) (@doubaokun)
- `Socket::checkLiveness` (memeriksa liveness koneksi menggunakan panggilan sistem), `Socket::peek` (mengintip buffer baca) (#3057) (@twose)
- `Socket->setProtocol(['open_fastcgi_protocol' => $bool])` (dukungan unpacking FastCGI bawaan) (#3103) (@twose)
- `Server::get(Master|Manager|Worker)Pid`, `Server::getWorkerId` (mendapatkan informasi tentang singleton server asinkron dan worker-nya) (#2793) (#3019) (@matyhtf)
- `Server::getWorkerStatus` (mendapatkan status proses worker, mengembalikan konstanta SWOOLE_WORKER_BUSY, SWOOLE_WORKER_IDLE) (#3225) (@matyhtf)
- `Server->on('beforeReload', $callable)` dan `Server->on('afterReload', $callable)` (event reload server, terjadi di proses manager) (#3130) (@hantaohuang)
- Handler file statis `Http\Server` sekarang mendukung konfigurasi `http_index_files` dan `http_autoindex` (#3171) (@hantaohuang)
- Metode `Http2\Client->read(float $timeout = -1)` mendukung pembacaan respons streaming (#3011) (#3117) (@twose)
- `Http\Request->getContent` (alias untuk metode rawContent) (#3128) (@hantaohuang)
- `swoole_mime_type_(add|set|delete|get|exists)()` (API terkait mime, dapat menambah, menghapus, mengambil, dan memeriksa tipe mime bawaan) (#3134) (@twose)

### Peningkatan

- Mengoptimalkan penyalinan memori antara proses master dan worker (hingga peningkatan kinerja empat kali lipat dalam kasus ekstrem) (#3075) (#3087) (@hantaohuang)
- Mengoptimalkan logika dispatch WebSocket (#3076) (@matyhtf)
- Mengoptimalkan penyalinan memori satu kali saat membangun frame WebSocket (#3097) (@matyhtf)
- Mengoptimalkan modul verifikasi SSL (#3226) (@matyhtf)
- Memisahkan proses SSL accept dan SSL handshake untuk menyelesaikan masalah klien SSL lambat yang dapat menyebabkan server coroutine macet (#3214) (@twose)
- Menambahkan dukungan untuk arsitektur MIPS (#3196) (@ekongyun)
- Klien UDP sekarang dapat secara otomatis menyelesaikan nama domain yang masuk (#3236) (#3239) (@huanghantao)
- Menambahkan dukungan untuk beberapa opsi yang umum digunakan di Coroutine\Http\Server (#3257) (@twose)
- Menambahkan dukungan pengaturan cookie selama handshake WebSocket (#3270) (#3272) (@twose)
- Mendukung CURLOPT_FAILONERROR (swoole/library#20) (@sy-records)
- Mendukung CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY (swoole/library#22) (@sy-records)
- Mendukung CURLOPT_HTTPGET (swoole/library@d730bd08) (@shiguangqi)

### Penghapusan

- Menghapus metode `Runtime::enableStrictMode` (b45838e3) (@twose)
- Menghapus kelas `Buffer` (559a49a8) (@twose)

### Terkait Kernel

- API C++ baru: fungsi coroutine::async menerima lambda untuk memulai tugas thread asinkron (#3127) (@matyhtf)
- Menulis ulang fd tipe integer di event-APIlevel bawah menjadi objek swSocket (#3030) (@matyhtf)
- Semua file C inti telah dikonversi ke file C++ (#3030) (71f987f3) (@matyhtf)
- Serangkaian optimasi kode (#3063) (#3067) (#3115) (#3135) (#3138) (#3139) (#3151) (#3168) (@hantaohuang)
- Optimasi standarisasi file header (#3051) (@matyhtf)
- Menulis ulang item konfigurasi `enable_reuse_port` agar lebih terstandarisasi (#3192) (@matyhtf)
- Menulis ulang API terkait Socket agar lebih terstandarisasi (#3193) (@matyhtf)
- Mengurangi panggilan sistem yang tidak perlu melalui prediksi buffer (3b5aa85d) (@matyhtf)
- Menghapus penyegaran timerlevel bawah swServerGS::now, langsung menggunakan fungsi time untuk mendapatkan waktu (#3152) (@hantaohuang)
- Mengoptimalkan konfigurator protokol (#3108) (@twose)
- Gaya inisialisasi struktur C yang lebih kompatibel (#3069) (@twose)
- Menyatukan bit fields sebagai tipe uchar (#3071) (@twose)
- Mendukung pengujian paralel untuk kecepatan yang lebih baik (#3215) (@twose)

### Perbaikan

- Memperbaiki crash WebSocket, menangani status error protokol (#3359) (@twose)
- Memperbaiki error null pointer di fungsi swSignalfd_setup dan wait_signal (#3360) (@twose)
- Memperbaiki masalah pemanggilan `Swoole\Server::close` dengan dispatch_func menyebabkan error (#3365) (@twose)
- Memperbaiki masalah inisialisasi di fungsi format_format `Swoole\Redis\Server::format` di format_buffer (#3369) (@matyhtf) (@twose)
- Memperbaiki masalah mendapatkan alamat MAC di MacOS (#3372) (@twose)
- Memperbaiki kasus uji MySQL (#3374) (@qiqizjl)
- Memperbaiki beberapa masalah kompatibilitas PHP8 (#3384) (#3458) (#3578) (#3598) (@twose)
- Memperbaiki php_error_docref yang hilang, timeout_event, dan masalah nilai kembali di socket write hook (#3383) (@twose)
- Memperbaiki masalah server asinkron tidak bisa menutup Server di fungsi callback `WorkerStart` (#3382) (@huanghantao)
- Memperbaiki masalah coredump potensial di thread heartbeat saat memanipulasi conn->socket (#3396) (@huanghantao)
- Memperbaiki masalah logika di send_yield (#3397) (@twose) (@matyhtf)
- Memperbaiki masalah kompilasi di Cygwin64 (#3400) (@twose)
- Memperbaiki atribut finish yang tidak valid di WebSocket (#3410) (@matyhtf)
- Memperbaiki status error transaksi MySQL yang hilang (#3429) (@twose)
- Memperbaiki perilaku `stream_select` yang tidak konsisten setelah hook (#3440) (@Yurunsoft)
- Memperbaiki masalah sinyal `SIGCHLD` hilang saat membuat proses anak dengan `Coroutine\System` (#3446) (@huanghantao)
- Memperbaiki masalah dukungan SSL di `sendwait` (#3459) (@huanghantao)
- Memperbaiki beberapa masalah di `ArrayObject` dan `StringObject` (swoole/library#44) (@matyhtf)
- Memperbaiki informasi exception mysqli yang salah (swoole/library#45) (@sy-records)
- Memperbaiki masalah `Swoole\Client` tidak bisa mendapatkan `errCode` yang benar setelah mengatur `open_eof_check` (#3478) (@huanghantao)
- Memperbaiki berbagai masalah di MacOS dengan `atomic->wait()`/`wakeup()` (#3476) (@Yurunsoft)
- Memperbaiki masalah status berhasil dikembalikan saat `Client::connect` ditolak (#3484) (@matyhtf)
- Memperbaiki masalah nullptr_t tidak dideklarasikan di lingkungan alpine (#3488) (@limingxinleo)
- Memperbaiki masalah double-free saat mengunduh file di HTTP Client (#3489) (@Yurunsoft)
- Memperbaiki masalah kebocoran memori yang disebabkan oleh tidak dirilisnya `Server\Port` saat `Server` dihancurkan (#3507) (@twose)
- Memperbaiki masalah parsing protokol MQTT (318e33a) (84d8214) (80327b3) (efe6c63) (@GXhua) (@sy-records)
- Memperbaiki masalah coredump yang disebabkan oleh metode `Coroutine\Http\Client->getHeaderOut` (#3534) (@matyhtf)
- Memperbaiki kehilangan pesan error setelah kegagalan verifikasi SSL (#3535) (@twose)
- Memperbaiki tautan yang salah di README untuk `Swoole benchmark` (#3536) (@sy-records) (@santalex)
- Memperbaiki masalah injeksi header menggunakan `CRLF` di `HTTP header/cookie` (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)
- Memperbaiki error variabel yang disebutkan di issue #3463 (#3547) (chromium1337) (@huanghantao)
- Memperbaiki typo yang disebutkan di pr #3463 (#3547) (@deminy)
- Memperbaiki masalah frame->fd kosong di server WebSocket coroutine (#3549) (@huanghantao)
- Memperbaiki masalah kebocoran koneksi yang disebabkan oleh penilaian salah di thread heartbeat mengenai status koneksi (#3534) (@matyhtf)
- Memperbaiki masalah blocking sinyal di `Process\Pool` (#3582) (@huanghantao) (@matyhtf)
- Memperbaiki masalah di `SAPI` saat menggunakan send headers (#3571) (@twose) (@sshymko)
- Memperbaiki unset `errCode` dan `errMsg` saat eksekusi `CURL` gagal (swoole/library#1b6c65e) (@sy-records)
- Memperbaiki masalah coredump di `swoole_socket_coro` accept setelah memanggil metode `setProtocol` (#3591) (@matyhtf)
