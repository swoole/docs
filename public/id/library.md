# Library

Setelah versi 4, Swoole mengintegrasikan modul [Library](https://github.com/swoole/library), **menggunakan kode PHP untuk menulis fungsionalitas kernel**, sehingga infrastruktur dasar menjadi lebih stabil dan andal.

!> Modul ini juga bisa diinstal secara terpisah melalui composer. Saat menginstal secara terpisah, Anda perlu menonaktifkan library bawaan ekstensi dengan mengatur `swoole.enable_library=Off` di `php.ini`.

Saat ini menyediakan komponen alat berikut:

- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) untuk menunggu tugas coroutine konkuren, [dokumentasi](/coroutine/wait_group)
- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI) FastCGI client, [dokumentasi](/coroutine_client/fastcgi)
- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php) Coroutine Server, [dokumentasi](/coroutine/server)
- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php) Coroutine barrier, [dokumentasi](/coroutine/barrier)

- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl) CURL dengan dukungan coroutine, [dokumentasi](/runtime?id=swoole_hook_curl)
- [Database](https://github.com/swoole/library/tree/master/src/core/Database) Enkapsulasi tingkat lanjut untuk berbagai connection pool database dan object proxy, [dokumentasi](/coroutine/conn_pool?id=database)
- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) Connection pool mentah, [dokumentasi](/coroutine/conn_pool?id=connectionpool)
- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php) Process manager, [dokumentasi](/process/process_manager)

- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php), [ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php), [MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php) Pemrograman Array dan String berorientasi objek

- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php) Menyediakan beberapa fungsi coroutine, [dokumentasi](/coroutine/coroutine?id=functions)
- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php) Konstanta konfigurasi umum
- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php) Kode status HTTP

## Kode Contoh

[Examples](https://github.com/swoole/library/tree/master/examples)
