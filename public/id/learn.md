# Pengetahuan Dasar

## Empat Cara Mengatur Fungsi Callback

* **Anonymous Function**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
!> Gunakan `use` untuk mengirim parameter ke anonymous function

* **Static Class Method**

```php
class A
{
    static function test($req, $resp)
    {
        echo "hello world";
    }
}
$server->on('Request', 'A::test');
$server->on('Request', array('A', 'test'));
$server->on('Request', [A::class, 'test']);
```
!> Static method yang digunakan harus `public`

* **Function**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **Object Method**

```php
class A
{
    function test($req, $resp)
    {
        echo "hello world";
    }
}

$object = new A();
$server->on('Request', array($object, 'test'));
```

!> Method yang digunakan harus `public`

## Synchronous IO dan Asynchronous IO

Di `Swoole4+`, semua kode bisnis ditulis secara sinkron (era `Swoole1.x` mendukung penulisan asinkron, tetapi sekarang client asinkron sudah dihapus; kebutuhan yang sama bisa diimplementasikan dengan coroutine client). Tidak ada beban mental sama sekali, sesuai dengan kebiasaan berpikir manusia. Namun, penulisan sinkron di tingkat bawah mungkin memiliki perbedaan `synchronous IO / asynchronous IO`.

Baik itu synchronous IO maupun asynchronous IO, `Swoole/Server` tetap bisa mempertahankan banyak koneksi client `TCP` (lihat [SWOOLE_PROCESS mode](/learn?id=swoole_process)). Layanan Anda bersifat blocking atau non-blocking tidak perlu mengatur parameter khusus—itu tergantung pada apakah kode Anda memiliki operasi synchronous IO.

**Apa itu synchronous IO:**

Contoh sederhana: saat eksekusi sampai `MySQL->query`, proses tidak melakukan apa pun dan menunggu hasil dari MySQL. Setelah hasilnya kembali, kode baru berjalan. Karena itu, kemampuan konkurensi layanan synchronous IO sangat rendah.

**Kode seperti apa yang termasuk synchronous IO:**

* Tanpa mengaktifkan [one-click coroutine](/runtime), sebagian besar operasi IO di kode Anda adalah synchronous IO. Setelah coroutine diaktifkan, operasi tersebut berubah menjadi asynchronous IO, dan proses tidak akan diam menunggu—lihat [coroutine scheduling](/coroutine?id=coroutine-scheduling).
* Beberapa `IO` tidak bisa diubah dengan one-click coroutine, misalnya `MongoDB` (kami yakin `Swoole` akan menyelesaikan ini). Perhatikan hal ini saat menulis kode.

!> [Coroutine](/coroutine) bertujuan meningkatkan konkurensi. Jika aplikasi Anda tidak membutuhkan konkurensi tinggi, atau harus menggunakan operasi IO yang tidak bisa dibuat asinkron (misalnya MongoDB di atas), Anda bisa tidak mengaktifkan [one-click coroutine](/runtime), matikan [enable_coroutine](/server/setting?id=enable_coroutine), dan perbanyak proses `Worker`. Ini akan mirip dengan model `Fpm/Apache`. Perlu dicatat, karena `Swoole` adalah proses yang menetap, bahkan synchronous IO pun akan meningkat kinerjanya secara signifikan. Banyak perusahaan menerapkan cara ini.

### Mengubah Synchronous IO menjadi Asynchronous IO

[Subbab sebelumnya](/learn?id=synchronous-io-dan-asynchronous-io) menjelaskan apa itu synchronous/asynchronous IO. Di `Swoole`, dalam beberapa kasus operasi `IO` sinkron bisa diubah menjadi asinkron.

- Setelah mengaktifkan [one-click coroutine](/runtime), operasi seperti `MySQL`, `Redis`, `Curl` akan berubah menjadi asynchronous IO.
- Menggunakan modul [Event](/event) untuk mengelola event secara manual, tambahkan `fd` ke [EventLoop](/learn?id=apa-itu-eventloop) untuk mengubahnya menjadi asynchronous IO. Contoh:

```php
// Memantau perubahan file menggunakan inotify
$fd = inotify_init();
// Tambahkan $fd ke EventLoop Swoole
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd); // Membaca file yang berubah setelah perubahan file.
    var_dump($var);
});
```

Kode di atas, jika tidak memanggil `Swoole\Event::add` untuk membuat IO asinkron, maka `inotify_read()` langsung akan memblokir proses Worker, dan permintaan lain tidak akan diproses.

- Gunakan method [sendMessage()](/server/methods?id=sendMessage) milik `Swoole\Server` untuk komunikasi antar proses. Secara default `sendMessage` adalah synchronous IO, tetapi dalam beberapa kasus `Swoole` akan mengubahnya menjadi asynchronous IO. Contoh menggunakan [User Process](/server/methods?id=addprocess):

```php
$serv = new Swoole\Server("0.0.0.0", 9501, SWOOLE_BASE);
$serv->set(
    [
        'worker_num' => 1,
    ]
);

$serv->on('pipeMessage', function ($serv, $src_worker_id, $data) {
    echo "#{$serv->worker_id} message from #$src_worker_id: $data\n";
    sleep(10);// Jika tidak menerima data dari sendMessage, buffer akan cepat penuh
});

$serv->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data) {

});

// Kasus 1: Synchronous IO (perilaku default)
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));// Defaultnya, jika buffer penuh, akan blocking di sini
    }
}, false);

// Kasus 2: Aktifkan dukungan coroutine untuk UserProcess melalui parameter enable_coroutine.
// Untuk mencegah coroutine lain tidak mendapat jadwal dari EventLoop,
// Swoole akan mengubah sendMessage menjadi asynchronous IO
$enable_coroutine = true;
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));// Jika buffer penuh, tidak blocking, tapi akan error
    }
}, false, 1, $enable_coroutine);

// Kasus 3: Di dalam UserProcess, jika ada async callback (misal timer, Swoole\Event::add, dll.),
// untuk mencegah callback lain tidak mendapat jadwal dari EventLoop,
// Swoole akan mengubah sendMessage menjadi asynchronous IO
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    swoole_timer_tick(2000, function ($interval) use ($worker, $serv) {
        echo "timer\n";
    });
    while (1) {
        var_dump($serv->sendMessage("big string", 0));// Jika buffer penuh, tidak blocking, tapi akan error
    }
}, false);

$serv->addProcess($userProcess);

$serv->start();
```

- Hal yang sama berlaku untuk komunikasi antar proses [Task Process](/learn?id=taskworker-process) melalui `sendMessage()`. Bedanya, dukungan coroutine di task process diaktifkan melalui konfigurasi Server [task_enable_coroutine](/server/setting?id=task_enable_coroutine), dan tidak ada `kasus 3`. Artinya, task process tidak akan mengubah sendMessage menjadi asynchronous IO hanya karena ada async callback.

## Apa itu EventLoop

`EventLoop`, atau event loop, bisa dipahami sederhananya sebagai `epoll_wait`. Semua handle (fd) dari event yang akan terjadi dimasukkan ke dalam `epoll_wait`, termasuk event seperti readable, writable, error, dll.

Proses yang bersangkutan akan terblokir pada fungsi kernel `epoll_wait`. Ketika terjadi event (atau timeout), fungsi `epoll_wait` akan keluar dari blocking dan mengembalikan hasil, lalu memanggil fungsi PHP yang sesuai. Misalnya, saat menerima data dari client, akan memanggil callback `onReceive`.

Ketika banyak fd dimasukkan ke dalam `epoll_wait` dan banyak event terjadi bersamaan, fungsi `epoll_wait` saat kembali akan memanggil callback yang sesuai satu per satu. Ini disebut satu putaran event loop, yaitu IO multiplexing. Kemudian akan memblokir lagi untuk memanggil `epoll_wait` ke putaran event loop berikutnya.

## Masalah Batas Paket TCP

Kode di [Mulai Cepat](/start/start_tcp_server) berjalan normal tanpa konkurensi, tetapi saat konkurensi tinggi akan muncul masalah batas paket TCP. Protokol `TCP` secara mekanisme internal menyelesaikan masalah urutan dan retransmisi packet loss dari `UDP`, tetapi dibandingkan `UDP`, `TCP` membawa masalah baru. Protokol `TCP` bersifat stream, paket data tidak memiliki batas, dan aplikasi yang menggunakan komunikasi `TCP` akan menghadapi masalah ini, yang dikenal sebagai TCP sticky packet problem.

Karena komunikasi `TCP` bersifat stream, saat menerima `1` paket besar, paket tersebut bisa dipecah menjadi beberapa paket untuk dikirim. Beberapa `Send` di tingkat bawah juga bisa digabung menjadi satu pengiriman. Diperlukan dua operasi untuk mengatasinya:

* Packet splitting: `Server` menerima beberapa paket data, perlu memisahkannya
* Packet merging: Data yang diterima `Server` hanya sebagian dari paket, perlu di-cache dan digabung menjadi paket utuh

Karena itu, komunikasi jaringan TCP perlu menetapkan protokol komunikasi. Protokol komunikasi TCP umum meliputi `HTTP`, `HTTPS`, `FTP`, `SMTP`, `POP3`, `IMAP`, `SSH`, `Redis`, `Memcache`, `MySQL`.

Perlu dicatat, Swoole sudah memiliki parser bawaan untuk banyak protokol umum guna mengatasi masalah batas paket TCP di server-protokol tersebut. Cukup dengan konfigurasi sederhana—lihat [open_http_protocol](/server/setting?id=open_http_protocol) / [open_http2_protocol](/http_server?id=open_http2_protocol) / [open_websocket_protocol](/server/setting?id=open_websocket_protocol) / [open_mqtt_protocol](/server/setting?id=open_mqtt_protocol)

Selain protokol umum, Anda juga bisa menentukan protokol kustom. `Swoole` mendukung `2` jenis protokol komunikasi jaringan kustom.

* **EOF Protocol**

Prinsip protokol `EOF` adalah menambahkan karakter khusus di akhir setiap paket data untuk menandakan paket selesai. Misalnya, `Memcache`, `FTP`, `SMTP` menggunakan `\r\n` sebagai penanda akhir. Saat mengirim data, cukup tambahkan `\r\n` di akhir paket. Saat menggunakan protokol `EOF`, pastikan `EOF` tidak muncul di tengah paket data, karena dapat menyebabkan kesalahan pemisahan paket.

Dalam kode `Server` dan `Client`, cukup atur `2` parameter untuk menggunakan protokol `EOF`.

```php
$server->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
```

Namun konfigurasi `EOF` di atas memiliki kinerja lebih rendah karena Swoole akan memeriksa setiap byte untuk melihat apakah data adalah `\r\n`. Selain cara di atas, bisa juga diatur seperti ini:

```php
$server->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
```

Konfigurasi ini memiliki kinerja lebih baik karena tidak perlu memeriksa semua byte, tetapi hanya bisa menyelesaikan masalah `packet splitting`, bukan `packet merging`. Artinya, mungkin `onReceive` akan menerima beberapa permintaan dari client sekaligus, dan Anda perlu memisahkannya sendiri, misalnya `explode("\r\n", $data)`. Kegunaan utama konfigurasi ini adalah untuk layanan tipe request-response (misalnya mengetik perintah di terminal), di mana tidak perlu memikirkan pemisahan data. Soalnya, client harus menunggu respons server sebelum mengirim permintaan kedua dan tidak akan mengirim `2` permintaan bersamaan.

* **Fixed-length Header + Body Protocol**

Metode fixed-length header sangat umum dan sering terlihat di program server. Protokol ini bercirikan setiap paket data selalu terdiri dari header + body. Header berisi field yang menentukan panjang body atau seluruh paket, biasanya direpresentasikan dengan `2-byte` / `4-byte` integer. Setelah menerima header, server dapat mengontrol secara tepat berapa banyak data yang masih perlu diterima untuk mendapatkan paket lengkap. Konfigurasi `Swoole` mendukung protokol ini dengan baik, dan Anda bisa mengatur `4` parameter secara fleksibel untuk menangani semua situasi.

`Server` memproses paket data di fungsi callback [onReceive](/server/events?id=onreceive). Setelah protokol diatur, event [onReceive](/server/events?id=onreceive) hanya akan dipicu ketika paket data lengkap diterima. Client setelah mengatur protokol, panggilan [$client->recv()](/client?id=recv) tidak perlu lagi memberikan panjang data—fungsi `recv` akan kembali setelah menerima paket lengkap atau terjadi error.

```php
$server->set(array(
    'open_length_check' => true,
    'package_max_length' => 81920,
    'package_length_type' => 'n', //lihat php pack()
    'package_length_offset' => 0,
    'package_body_offset' => 2,
));
```

!> Untuk arti setiap konfigurasi, lihat bagian [Configuration](/server/setting?id=open_length_check) di bab `Server/Client`

## Apa itu IPC

Ada banyak cara komunikasi antar dua proses dalam satu host (IPC). Swoole menggunakan `2` cara: `Unix Socket` dan `sysvmsg`. Berikut penjelasannya:

- **Unix Socket**

    Nama lengkap UNIX Domain Socket, disingkat `UDS`. Menggunakan API socket (socket, bind, listen, connect, read, write, close, dll.). Tidak seperti TCP/IP, UDS tidak perlu menentukan ip dan port, melainkan menggunakan nama file (misalnya `/tmp/php-fcgi.sock` antara FPM dan Nginx). UDS adalah implementasi kernel Linux untuk komunikasi full-memory tanpa konsumsi `IO`. Dalam pengujian `1` proses `write` dan `1` proses `read`, masing-masing `1024` byte data, `100` ribu komunikasi hanya membutuhkan `1,02` detik. Fungsinya sangat kuat. `Swoole` menggunakan cara IPC ini secara default.

    * **`SOCK_STREAM` dan `SOCK_DGRAM`**

        - Di `Swoole`, komunikasi `UDS` memiliki dua tipe: `SOCK_STREAM` dan `SOCK_DGRAM`. Sederhananya, ini seperti perbedaan antara TCP dan UDP. Saat menggunakan `SOCK_STREAM`, Anda tetap perlu mempertimbangkan [TCP packet boundary problem](/learn?id=masalah-batas-paket-tcp).
        - Saat menggunakan `SOCK_DGRAM`, Anda tidak perlu memikirkan masalah batas paket TCP. Setiap data yang dikirim dengan `send()` memiliki batas—ukuran data yang dikirim sama dengan yang diterima, tanpa masalah packet loss atau ketidakurutan dalam transmisi. Urutan `send` menulis dan `recv` membaca sepenuhnya konsisten. Jika `send` berhasil, pasti bisa di-`recv`.

    Untuk IPC dengan data yang relatif kecil, `SOCK_DGRAM` sangat cocok. **Karena setiap paket `IP` memiliki batas maksimal 64k, saat menggunakan `SOCK_DGRAM` untuk IPC, pengiriman data sekali tidak boleh melebihi 64k. Perhatikan juga bahwa jika kecepatan menerima paket terlalu lambat, buffer OS akan penuh dan paket akan dibuang—karena UDP mengizinkan packet loss. Anda bisa memperbesar ukuran buffer**.

- **sysvmsg**

    Yaitu `message queue` yang disediakan Linux. Cara `IPC` ini menggunakan nama file sebagai `key` untuk berkomunikasi. Cara ini sangat tidak fleksibel dan jarang digunakan dalam proyek nyata.

    * **Cara IPC ini hanya berguna dalam dua skenario:**

        - Mencegah kehilangan data: jika seluruh service mati, pesan dalam antrean tetap ada dan bisa dilanjutkan setelah restart. **Namun tetap ada masalah dirty data**.
        - Pengiriman data dari luar: misalnya, `Worker process` di Swoole mengirim tugas ke `Task process` melalui message queue. Proses pihak ketiga juga bisa mengirim tugas ke antrean untuk dikonsumsi Task, bahkan Anda bisa menambahkan pesan secara manual ke antrean dari command line.

## Perbedaan Master Process, Reactor Thread, Worker Process, Task Process, Manager Process :id=diff-process

### Master Process

* Master process adalah proses multi-thread, lihat [Process/Thread Structure Diagram](/server/init?id=process-thread-structure-diagram)

### Reactor Thread

* Reactor thread adalah thread yang dibuat di dalam Master process
* Bertanggung jawab memelihara koneksi `TCP` client, menangani network `IO`, memproses protokol, mengirim dan menerima data
* Tidak mengeksekusi kode `PHP` apa pun
* Menyangga, menggabung, dan memisahkan data dari `TCP` client menjadi paket request yang lengkap

### Worker Process

* Menerima paket data request dari `Reactor` thread dan mengeksekusi fungsi callback `PHP` untuk memproses data
* Menghasilkan data respons dan mengirimkannya ke `Reactor` thread, yang kemudian mengirimkannya ke `TCP` client
* Bisa dalam mode asynchronous non-blocking atau synchronous blocking
* `Worker` berjalan sebagai multi-process

### TaskWorker Process

* Menerima tugas dari `Worker` process melalui method Swoole\Server->[task](/server/methods?id=task) / [taskwait](/server/methods?id=taskwait) / [taskCo](/server/methods?id=taskCo) / [taskWaitMulti](/server/methods?id=taskWaitMulti)
* Memproses tugas dan mengembalikan hasil data ke `Worker` process (menggunakan [Swoole\Server->finish](/server/methods?id=finish))
* Sepenuhnya dalam mode **synchronous blocking**
* `TaskWorker` berjalan sebagai multi-process, [contoh task lengkap](/start/start_task)

### Manager Process

* Bertanggung jawab membuat/mendaur ulang proses `worker` / `task`

Hubungan mereka bisa dipahami dengan: `Reactor` itu seperti `nginx`, `Worker` itu seperti `PHP-FPM`. `Reactor` thread memproses request jaringan secara async dan paralel, lalu meneruskannya ke `Worker` process untuk diproses. Komunikasi antara `Reactor` dan `Worker` dilakukan melalui [unixSocket](/learn?id=apa-itu-ipc).

Dalam aplikasi `PHP-FPM`, tugas sering dikirim secara asinkron ke antrean seperti `Redis`, dan beberapa proses `PHP` dijalankan di latar belakang untuk memproses tugas-tugas tersebut. `TaskWorker` yang disediakan `Swoole` adalah solusi yang lebih lengkap, menggabungkan pengiriman tugas, antrean, dan manajemen proses pemrosesan tugas `PHP` menjadi satu kesatuan. Melalui `API` tingkat bawah yang disediakan, pemrosesan tugas asinkron bisa diimplementasikan dengan sangat mudah. Selain itu, `TaskWorker` bisa mengembalikan hasil ke `Worker` setelah tugas selesai.

`Reactor`, `Worker`, dan `TaskWorker` di `Swoole` dapat digabungkan secara erat untuk menyediakan penggunaan tingkat yang lebih tinggi.

Analogi sederhana: anggap `Server` adalah pabrik, maka `Reactor` adalah sales yang menerima pesanan pelanggan. `Worker` adalah pekerja yang memproduksi barang setelah sales menerima pesanan. Sedangkan `TaskWorker` bisa dipahami sebagai staf administrasi yang membantu `Worker` mengerjakan tugas-tugas sampingan, sehingga `Worker` bisa fokus bekerja.

Seperti pada gambar:

![process_demo](_images/server/process_demo.png)

## Pengertian Tiga Mode Operasi Server

Di parameter ketiga konstruktor `Swoole\Server`, Anda bisa mengisi `3` nilai konstanta -- [SWOOLE_BASE](/learn?id=swoole_base), [SWOOLE_PROCESS](/learn?id=swoole_process), dan [SWOOLE_THREAD](/learn?id=swoole_thread). Berikut perbedaan serta kelebihan dan kekurangan ketiga mode tersebut.

### SWOOLE_PROCESS

Di mode SWOOLE_PROCESS, semua koneksi TCP client `Server` terhubung dengan [Master Process](/learn?id=reactor-thread). Implementasi internalnya cukup kompleks, menggunakan banyak mekanisme komunikasi antar proses dan manajemen proses. Cocok untuk skenario dengan logika bisnis yang sangat kompleks. `Swoole` menyediakan mekanisme manajemen proses dan perlindungan memori yang lengkap. Bahkan dengan logika bisnis yang sangat rumit, server bisa berjalan stabil dalam jangka panjang.

`Swoole` menyediakan fungsionalitas `Buffer` di [Reactor](/learn?id=reactor-thread) thread untuk menangani banyak koneksi lambat dan client jahat yang mengirim byte demi byte.

#### Kelebihan mode process:

* Koneksi dan pengiriman data request terpisah, sehingga tidak ada ketidakseimbangan beban `Worker` process akibat perbedaan volume data koneksi
* Saat terjadi error fatal di `Worker` process, koneksi tidak terputus
* Bisa menerapkan konkurensi satu koneksi—hanya mempertahankan sedikit koneksi `TCP`, request bisa diproses secara konkuren di beberapa `Worker` process

#### Kekurangan mode process:

* Ada overhead `2` kali `IPC`. `Master` process dan `Worker` process perlu berkomunikasi menggunakan [unixSocket](/learn?id=apa-itu-ipc)
* `SWOOLE_PROCESS` tidak mendukung PHP ZTS. Dalam kasus ini, Anda hanya bisa menggunakan `SWOOLE_BASE` atau mengatur [single_thread](/server/setting?id=single_thread) menjadi true

### SWOOLE_BASE

Mode SWOOLE_BASE adalah `Server` asynchronous non-blocking tradisional. Sepenuhnya sama dengan program seperti `Nginx` dan `Node.js`.

Parameter [worker_num](/server/setting?id=worker_num) tetap berlaku di mode `BASE`—akan menjalankan beberapa `Worker` process.

Ketika ada koneksi TCP masuk, semua Worker process bersaing untuk koneksi tersebut, dan akhirnya satu worker process berhasil membuat koneksi TCP langsung dengan client. Setelah itu, semua pengiriman dan penerimaan data koneksi ini langsung berkomunikasi dengan worker tersebut, tanpa melalui Reactor thread Master process.

* Di mode `BASE` tidak ada peran `Master` process, hanya ada peran [Manager](/learn?id=manager-process).
* Setiap `Worker` process menjalankan dua peran sekaligus: [Reactor](/learn?id=reactor-thread) thread ala SWOOLE_PROCESS dan `Worker` process.
* Di mode `BASE`, `Manager` process bersifat opsional. Saat `worker_num=1` dan tidak menggunakan fitur `Task` dan `MaxRequest`, Swoole akan langsung membuat satu `Worker` process tanpa membuat `Manager` process.

#### Kelebihan mode BASE:

* Mode `BASE` tidak memiliki overhead `IPC`, kinerjanya lebih baik
* Kode mode `BASE` lebih sederhana, tidak mudah error

#### Kekurangan mode BASE:

* Koneksi `TCP` dipertahankan di `Worker` process, jadi jika suatu `Worker` process mati, semua koneksi di dalamnya akan ditutup
* Sedikit koneksi `TCP` panjang tidak bisa memanfaatkan semua `Worker` process
* Koneksi `TCP` terikat dengan `Worker`. Dalam aplikasi koneksi panjang, beberapa koneksi memiliki volume data besar sehingga beban `Worker` process tempat koneksi tersebut berada menjadi sangat tinggi. Sementara koneksi lain volumenya kecil, membuat beban `Worker` process rendah. Tidak ada keseimbangan antar `Worker` process.
* Jika ada operasi blocking dalam fungsi callback, `Server` akan terdegradasi ke mode sinkron, yang dapat menyebabkan antrean [backlog](/server/setting?id=backlog) TCP penuh.

#### Skenario yang cocok untuk mode BASE:

Jika tidak perlu interaksi antar koneksi client, mode `BASE` bisa digunakan. Contoh: server `Memcache`, `HTTP`, dll.

#### Batasan mode BASE:

Di mode `BASE`, [method Server](/server/methods) selain [send](/server/methods?id=send) dan [close](/server/methods?id=close) **tidak mendukung** eksekusi lintas proses.

!> Di versi v4.5.x mode `BASE`, hanya method `send` yang mendukung eksekusi lintas proses; di versi v4.6.x, hanya method `send` dan `close` yang mendukung.

### SWOOLE_THREAD

SWOOLE_THREAD adalah mode operasi baru yang diperkenalkan di `Swoole 6.0`. Dengan memanfaatkan mode `PHP ZTS`, sekarang kita bisa menjalankan service dengan mode multi-thread.

Parameter [worker_num](/server/setting?id=worker_num) tetap berlaku di mode `THREAD`, hanya saja bukan membuat multi-process, melainkan membuat multi-thread. Beberapa `Worker` thread akan dijalankan.

Hanya akan ada satu proses. Sub-proses akan berubah menjadi sub-thread yang bertanggung jawab menerima request client.

#### Kelebihan mode THREAD:
* Komunikasi antar proses lebih sederhana, tanpa overhead komunikasi IPC tambahan
* Debug program lebih mudah—karena hanya satu proses, `gdb -p` lebih sederhana
* Memiliki kemudahan pemrograman IO konkuren dengan coroutine, sekaligus keunggulan eksekusi paralel multi-thread dan berbagi memori stack

#### Kekurangan mode THREAD:
* Jika terjadi Crash atau memanggil Process::exit(), seluruh proses akan keluar. Anda perlu menyiapkan logika error retry dan reconnection di client, serta menggunakan supervisor dan docker/k8s untuk restart otomatis setelah proses keluar.
* `ZTS` dan operasi lock mungkin memiliki overhead tambahan—kinerja bisa sekitar `10%` lebih rendah dibanding model multi-process `NTS`. Jika service Anda stateless, tetap disarankan menggunakan model multi-process `NTS`.
* Tidak mendukung pengiriman objek dan resource antar thread.

#### Skenario yang cocok untuk mode THREAD:
* Mode THREAD lebih efisien untuk pengembangan game server dan communication server.

## Perbedaan Process, Process\Pool, dan UserProcess :id=process-diff

### Process

[Process](/process/process) adalah modul manajemen proses yang disediakan Swoole untuk menggantikan `pcntl` PHP.

* Memudahkan komunikasi antar proses
* Mendukung redirect input dan output standar—di child process, `echo` tidak akan mencetak ke layar, tetapi menulis ke pipe. Membaca input keyboard bisa dialihkan menjadi membaca data dari pipe.
* Menyediakan interface [exec](/process/process?id=exec)—proses yang dibuat bisa menjalankan program lain dan berkomunikasi dengan mudah dengan parent process `PHP`.

!> Modul `Process` tidak bisa digunakan di lingkungan coroutine. Gunakan `runtime hook` + `proc_open` untuk mengatasinya. Lihat [Coroutine Process Management](/coroutine/proc_open)

### Process\Pool

[Process\Pool](/process/process_pool) membungkus modul manajemen proses Server ke dalam class PHP, mendukung penggunaan process manager Swoole dalam kode PHP.

Dalam proyek nyata, Anda sering perlu menulis script yang berjalan lama, seperti consumer antrean multi-process berbasis `Redis`, `Kafka`, `RabbitMQ`, crawler multi-process, dan lain-lain. Developer perlu menggunakan ekstensi `pcntl` dan `posix` untuk pemrograman multi-process, tetapi juga harus memiliki pemahaman mendalam tentang pemrograman sistem `Linux`—jika tidak, masalah mudah muncul. Menggunakan process manager dari Swoole bisa menyederhanakan pemrograman script multi-process secara signifikan.

* Menjamin stabilitas work process
* Mendukung signal handling
* Mendukung message queue dan pengiriman pesan `TCP-Socket`

### UserProcess

`UserProcess` adalah work process kustom yang ditambahkan melalui [addProcess](/server/methods?id=addprocess). Biasanya digunakan untuk membuat work process khusus untuk monitoring, pelaporan, atau tugas spesifik lainnya.

Meskipun `UserProcess` dikelola oleh [Manager process](/learn?id=manager-process), ia adalah proses yang relatif independen dibandingkan [Worker process](/learn?id=worker-process) dan digunakan untuk menjalankan fungsi kustom.
