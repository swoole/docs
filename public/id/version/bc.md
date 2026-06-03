# Perubahan yang Tidak Kompatibel ke Bawah

## v5.0.0
* Mengubah mode berjalan default `Server` menjadi `SWOOLE_BASE`
* Menaikkan persyaratan versi minimum `PHP` ke `8.0`
* Semua metode kelas dan fungsi menambahkan pembatasan tipe, berubah ke mode tipe kuat
* Menghapus alias kelas `PSR-0` dengan garis bawah, hanya mempertahankan nama kelas bergaya namespace, misalnya `swoole_server` harus diubah menjadi `Swoole\Server`
* `Swoole\Coroutine\Redis` dan `Swoole\Coroutine\MySQL` ditandai tidak digunakan lagi, silakan gunakan `Runtime Hook` + klien `Redis`/`MySQL` asli

## v4.8.0

- Dalam mode `BASE`, callback `onStart` akan selalu dipanggil saat proses pekerja pertama (`workerId` 0) dimulai, mendahului `onWorkerStart`. Dalam fungsi `onStart`, API coroutine selalu dapat digunakan. Jika Worker-0 mengalami error fatal dan restart, `onStart` akan dipanggil lagi.
Di versi sebelumnya, `onStart` hanya dipanggil di Worker-0 saat hanya ada satu proses pekerja. Jika ada beberapa proses pekerja, ia dijalankan di proses Manager.

## v4.7.0

- Menghapus `Table\Row`, `Table` tidak lagi mendukung pembacaan/penulisan dengan gaya array

## v4.6.0

- Menghapus batas maksimum `session id`, tidak lagi berulang
- Menonaktifkan fungsi tidak aman saat menggunakan coroutine, termasuk `pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait`
- Mengaktifkan coroutine hook secara default
- Tidak lagi mendukung PHP7.1
- `Event::rshutdown()` ditandai tidak digunakan lagi, silakan gunakan Coroutine\run

## v4.5.4

- `SWOOLE_HOOK_ALL` termasuk `SWOOLE_HOOK_CURL`
- Menghapus `ssl_method`, mendukung `ssl_protocols`

## v4.4.12

- Versi ini mendukung kompresi frame WebSocket, mengubah parameter ketiga metode push menjadi flags. Jika `strict_types` tidak diatur, kompatibilitas kode tidak terpengaruh, jika tidak akan terjadi error tipe bool tidak bisa dikonversi implisit ke int. Masalah ini akan diperbaiki di v4.4.13.

## v4.4.1

- Sinyal yang terdaftar tidak lagi digunakan sebagai syarat untuk mempertahankan event loop. **Jika program hanya mendaftarkan sinyal tanpa melakukan pekerjaan lain, program akan dianggap idle dan segera keluar** (dalam hal ini, Anda dapat mencegah proses keluar dengan mendaftarkan timer).

## v4.4.0

- Sejalan dengan resmi `PHP`, tidak lagi mendukung `PHP7.0` (@matyhtf)
- Menghapus modul `Serialize`, dipelihara di ekstensi terpisah [ext-serialize](https://github.com/swoole/ext-serialize)
- Menghapus modul `PostgreSQL`, dipelihara di ekstensi terpisah [ext-postgresql](https://github.com/swoole/ext-postgresql)
- `Runtime::enableCoroutine` tidak lagi secara otomatis kompatibel dengan lingkungan coroutine dan non-coroutine; setelah diaktifkan, semua operasi blocking harus dipanggil di dalam coroutine (@matyhtf)
- Karena diperkenalkannya driver klien coroutine `MySQL` yang baru, desain internal lebih terstandarisasi, tetapi ada beberapa perubahan kecil yang tidak kompatibel ke bawah (lihat [Log Pembaruan 4.4.0](https://wiki.swoole.com/wiki/page/p-4.4.0.html))

## v4.3.0

- Menghapus semua modul asinkron, lihat [Ekstensi Asinkron Mandiri](https://wiki.swoole.com/wiki/page/p-async_ext.html) atau [Log Pembaruan 4.3.0](https://wiki.swoole.com/wiki/page/p-4.3.0.html)

## v4.2.13

> Perubahan tidak kompatibel yang tidak terhindarkan karena masalah desain API historis

* Perubahan operasi mode berlangganan klien Redis coroutine, lihat [Mode Berlangganan](https://wiki.swoole.com/#/coroutine_client/redis?id=mode-berlangganan)

## v4.2.12

> Fitur eksperimental + Perubahan tidak kompatibel yang tidak terhindarkan karena masalah desain API historis

- Menghapus opsi konfigurasi `task_async`, digantikan oleh [task_enable_coroutine](https://wiki.swoole.com/#/server/setting?id=task_enable_coroutine)

## v4.2.5

- Menghapus dukungan untuk klien UDP di `onReceive` dan `Server::getClientInfo`

## v4.2.0

- Sepenuhnya menghapus `swoole_http2_client` asinkron, silakan gunakan klien HTTP2 coroutine

## v4.0.4

Mulai versi ini, `Http2\Client` asinkron akan memicu peringatan `E_DEPRECATED` dan akan dihapus di versi berikutnya. Silakan gunakan `Coroutine\Http2\Client` sebagai gantinya.

Properti `body` dari `Http2\Response` diubah namanya menjadi `data`. Perubahan ini untuk memastikan konsistensi antara `request` dan `response`, dan lebih sesuai dengan nama tipe frame protokol HTTP2.

Sejak versi ini, `Coroutine\Http2\Client` memiliki dukungan protokol HTTP2 yang relatif lengkap, dapat memenuhi kebutuhan aplikasi produksi tingkat perusahaan seperti `grpc`, `etcd`, dll. Oleh karena itu, serangkaian perubahan terkait HTTP2 sangat diperlukan.

## v4.0.3

Membuat `swoole_http2_response` dan `swoole_http2_request` konsisten, semua nama properti diubah ke bentuk jamak, melibatkan properti berikut:

- `headers`
- `cookies`

## v4.0.2

> Karena implementasi internal yang terlalu rumit, sulit dipelihara, dan pengguna sering salah menggunakannya, API berikut untuk sementara dihapus:

- `Coroutine\Channel::select`

Namun, parameter kedua `timeout` ditambahkan ke metode `Coroutine\Channel->pop` untuk memenuhi kebutuhan pengembangan.

## v4.0

> Karena peningkatan kernel coroutine, coroutine dapat dipanggil di mana saja dalam fungsi apa pun tanpa perlakuan khusus, maka API berikut dihapus:

- `Coroutine::call_user_func`
- `Coroutine::call_user_func_array`
