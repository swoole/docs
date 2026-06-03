# Method

## __construct()

Membuat objek TCP Server untuk [I/O asynchronous](/learn?id=sync-io-async-io).

```php
Swoole\Server::__construct(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```

  * **Parameter**

    * `string $host`

      * Fungsi: menentukan alamat ip yang akan didengarkan.
      * Default: tidak ada.
      * Nilai lain: tidak ada.

      !> Untuk IPv4 gunakan `127.0.0.1` untuk mendengarkan localhost, `0.0.0.0` untuk mendengarkan semua alamat.
      Untuk IPv6 gunakan `::1` untuk mendengarkan localhost, `::` (setara dengan `0:0:0:0:0:0:0:0`) untuk mendengarkan semua alamat.

    * `int $port`

      * Fungsi: menentukan port yang akan didengarkan, misalnya `9501`.
      * Default: tidak ada.
      * Nilai lain: tidak ada.

      !> Jika nilai `$sockType` adalah [UnixSocket Stream/Dgram](/learn?id=apa-itu-ipc), parameter ini akan diabaikan.
      Mendengarkan port di bawah `1024` memerlukan hak akses `root`.
      Jika port ini sudah digunakan, `server->start` akan gagal.

    * `int $mode`

      * Fungsi: menentukan mode operasi.
      * Default: [SWOOLE_PROCESS](/learn?id=swoole_process) mode multi-proses (default).
      * Nilai lain: [SWOOLE_BASE](/learn?id=swoole_base) mode dasar, [SWOOLE_THREAD](/learn?id=swoole_thread) mode multi-thread (tersedia di Swoole 6.0).

      ?> Dalam mode `SWOOLE_THREAD`, klik di sini [Thread + Server (gaya async)](/thread/thread?id=thread-server-gaya-async) untuk melihat cara membuat server dalam mode multi-thread.

      !> Mulai Swoole 5, nilai default mode operasi adalah `SWOOLE_BASE`.

    * `int $sockType`

      * Fungsi: menentukan tipe Server.
      * Default: tidak ada.
      * Nilai lain:
        * `SWOOLE_TCP/SWOOLE_SOCK_TCP` tcp ipv4 socket
        * `SWOOLE_TCP6/SWOOLE_SOCK_TCP6` tcp ipv6 socket
        * `SWOOLE_UDP/SWOOLE_SOCK_UDP` udp ipv4 socket
        * `SWOOLE_UDP6/SWOOLE_SOCK_UDP6` udp ipv6 socket
        * [SWOOLE_UNIX_DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) unix socket dgram
        * [SWOOLE_UNIX_STREAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/stream_server.php) unix socket stream 

      !> Menggunakan `$sock_type` | `SWOOLE_SSL` dapat mengaktifkan enkripsi tunnel `SSL`. Setelah mengaktifkan SSL, harus dikonfigurasi dengan [ssl_key_file](/server/setting?id=ssl_cert_file) dan [ssl_cert_file](/server/setting?id=ssl_cert_file)

  * **Contoh**

```php
$server = new \Swoole\Server($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP);

// Dapat mencampur UDP/TCP, mendengarkan port internal dan eksternal secara bersamaan, lihat bagian addlistener untuk multi-port.
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP); // Menambah TCP
$server->addlistener("192.168.1.100", 9503, SWOOLE_SOCK_TCP); // Menambah Web Socket
$server->addlistener("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDP
$server->addlistener("/var/run/myserv.sock", 0, SWOOLE_UNIX_STREAM); //UnixSocket Stream
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP | SWOOLE_SSL); //TCP + SSL

$port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP); // Sistem memberikan port secara acak, nilai kembali adalah port yang diberikan
echo $port->port;
```

## set()

Digunakan untuk mengatur berbagai parameter runtime. Setelah server mulai, akses array parameter yang diatur melalui method `Server->set` menggunakan `$serv->setting`.

```php
Swoole\Server->set(array $setting): void
```

!> `Server->set` harus dipanggil sebelum `Server->start`. Untuk arti spesifik setiap konfigurasi, silakan lihat [bagian ini](/server/setting)

  * **Contoh**

```php
$server->set(array(
    'reactor_num'   => 2,     // jumlah thread
    'worker_num'    => 4,     // jumlah proses
    'backlog'       => 128,   // mengatur panjang antrian Listen
    'max_request'   => 50,    // jumlah maksimum request per proses
    'dispatch_mode' => 1,     // strategi distribusi paket data
));
```

## on()

Mendaftarkan fungsi callback event untuk `Server`.

```php
Swoole\Server->on(string $event, callable $callback): bool
```

!> Memanggil method `on` berulang kali akan menimpa pengaturan sebelumnya

!> Mulai `PHP 8.2`, tidak lagi mendukung pengaturan properti dinamis secara langsung. Jika `$event` bukan event yang ditentukan `Swoole`, akan memunculkan peringatan

  * **Parameter**

    * `string $event`

      * Fungsi: nama event callback
      * Default: tidak ada
      * Nilai lain: tidak ada

      !> Tidak case-sensitive. Untuk daftar event callback, lihat [bagian ini](/server/events). Jangan tambahkan `on` pada string nama event

    * `callable $callback`

      * Fungsi: fungsi callback
      * Default: tidak ada
      * Nilai lain: tidak ada

      !> Dapat berupa string nama fungsi, method statis kelas, array method objek, fungsi anonim. Lihat [bagian ini](/learn?id=berbagai-cara-mengatur-fungsi-callback).

  * **Nilai Kembali**

    * Mengembalikan `true` jika operasi berhasil, `false` jika gagal.

  * **Contoh**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('connect', function ($server, $fd){
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();
```

## addListener()

Menambah port yang didengarkan. Dalam kode bisnis, panggil [Swoole\Server->getClientInfo](/server/methods?id=getclientinfo) untuk mengetahui dari port mana suatu koneksi berasal.

```php
Swoole\Server->addListener(string $host, int $port, int $sockType): bool|Swoole\Server\Port
```

!> Mendengarkan port di bawah `1024` memerlukan hak akses `root`  
Jika server utama menggunakan protokol `WebSocket` atau `HTTP`, port `TCP` baru yang didengarkan secara default akan mewarisi pengaturan protokol dari server utama. Harus memanggil method `set` secara terpisah untuk mengatur protokol baru agar protokol baru digunakan [Lihat penjelasan detail](/server/port).  
Klik [di sini](/server/server_port) untuk melihat penjelasan detail `Swoole\Server\Port`.

  * **Parameter**

    * `string $host`

      * Fungsi: Sama dengan `$host` di `__construct()`
      * Default: Sama dengan `$host` di `__construct()`
      * Nilai lain: Sama dengan `$host` di `__construct()`

    * `int $port`

      * Fungsi: Sama dengan `$port` di `__construct()`
      * Default: Sama dengan `$port` di `__construct()`
      * Nilai lain: Sama dengan `$port` di `__construct()`

    * `int $sockType`

      * Fungsi: Sama dengan `$sockType` di `__construct()`
      * Default: Sama dengan `$sockType` di `__construct()`
      * Nilai lain: Sama dengan `$sockType` di `__construct()`

  * **Nilai Kembali**

    * Mengembalikan `Swoole\Server\Port` jika berhasil, mengembalikan `false` jika gagal.

!> -Dalam mode `Unix Socket`, parameter `$host` harus berupa path file yang dapat diakses, parameter `$port` diabaikan  
-Dalam mode `Unix Socket`, `$fd` client bukan lagi angka, melainkan string path file  
-Di sistem `Linux`, setelah mendengarkan port `IPv6`, koneksi menggunakan alamat `IPv4` juga dapat dilakukan

## listen()

Method ini adalah alias dari `addlistener`.

```php
Swoole\Server->listen(string $host, int $port, int $type): bool|Swoole\Server\Port
```

## addProcess()

Menambahkan proses kerja kustom. Fungsi ini biasanya digunakan untuk membuat proses kerja khusus untuk monitoring, pelaporan, atau tugas khusus lainnya.

```php
Swoole\Server->addProcess(Swoole\Process $process): int
```

!> Tidak perlu menjalankan `start`. Proses akan otomatis dibuat saat `Server` mulai, dan akan menjalankan fungsi child process yang ditentukan

  * **Parameter**

    * [Swoole\Process](/process/process)

      * Fungsi: objek `Swoole\Process`
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan nomor id proses jika berhasil, jika tidak program akan melempar fatal error.

  * **Catatan**

    !> -Child process yang dibuat dapat memanggil berbagai method yang disediakan objek `$server`, seperti `getClientList/getClientInfo/stats`.                                   
    -Dalam proses `Worker/Task`, dapat memanggil method yang disediakan `$process` untuk berkomunikasi dengan child process.        
    -Dalam proses kustom, dapat menggunakan `$server->sendMessage` untuk berkomunikasi dengan proses `Worker/Task`.      
    -User process tidak dapat menggunakan interface `Server->task/taskwait`.              
    -User process dapat menggunakan interface `Server->send/close`.         
    -User process harus memiliki perulangan `while(true)` (seperti contoh di bawah) atau perulangan [EventLoop](/learn?id=apa-itu-eventloop) (misalnya membuat timer), jika tidak user process akan terus keluar dan restart.         
    -Harus menggunakan `addProcess` sebelum `Server->start()` dijalankan, jika tidak user process tidak akan berjalan.

  * **Siklus Hidup**

    ?> -Siklus hidup user process sama dengan `Master` dan [Manager](/learn?id=proses-manager), tidak terpengaruh oleh [reload](/server/methods?id=reload).     
    -User process tidak dikendalikan oleh perintah `reload`, saat reload tidak ada informasi yang dikirim ke user process.        
    -Saat shutdown server, sinyal `SIGTERM` akan dikirim ke user process untuk menutupnya.            
    -Proses kustom akan dikelola oleh proses `Manager`. Jika terjadi fatal error, proses `Manager` akan membuat ulang.         
    -Proses kustom juga tidak memicu event seperti `onWorkerStop`.

  * **Contoh**

    ```php
    $server = new Swoole\Server('127.0.0.1', 9501);
    
    /**
     * User process mengimplementasikan fungsi broadcast, menerima pesan unixSocket secara loop, dan mengirim ke semua koneksi server
     */
    $process = new Swoole\Process(function ($process) use ($server) {
        $socket = $process->exportSocket();
        while (true) {
            $msg = $socket->recv();
            foreach ($server->connections as $conn) {
                $server->send($conn, $msg);
            }
        }
    }, false, 2, 1);
    
    $server->addProcess($process);
    
    $server->on('receive', function ($serv, $fd, $reactor_id, $data) use ($process) {
        // Broadcast pesan yang diterima
        $socket = $process->exportSocket();
        $socket->send($data);
    });
    
    $server->start();
    ```

    Lihat bagian [Komunikasi Antar Proses](/process/process?id=exportsocket).

## start()

Memulai server, mendengarkan semua port `TCP/UDP`.

```php
Swoole\Server->start(): bool
```

!> Tips: berikut contoh berdasarkan mode [SWOOLE_PROCESS](/learn?id=swoole_process)

  * **Tips**

    - Setelah berhasil mulai, akan membuat `worker_num+2` proses: proses `Master` + proses `Manager` + `serv->worker_num` proses `Worker`.
    - Jika gagal mulai, akan langsung mengembalikan `false`.
    - Setelah berhasil mulai, akan masuk ke event loop, menunggu permintaan koneksi client. Kode setelah method `start` tidak akan dijalankan.
    - Saat server ditutup, fungsi `start` mengembalikan `true` dan melanjutkan eksekusi ke bawah.
    - Menyetel `task_worker_num` akan menambah jumlah [Task proses](/learn?id=proses-taskworker) yang sesuai.
    - Method sebelum `start` dalam daftar method hanya dapat digunakan sebelum `start` dipanggil. Method setelah `start` hanya dapat digunakan dalam fungsi callback event seperti [onWorkerStart](/server/events?id=onworkerstart), [onReceive](/server/events?id=onreceive), dll.

  * **Ekstensi**

    * Master Process

      * Dalam proses master terdapat beberapa thread [Reactor](/learn?id=thread-reactor), melakukan polling event jaringan berbasis `epoll/kqueue/select`. Setelah menerima data, meneruskannya ke proses `Worker` untuk diproses.

    * Manager Process

      * Mengelola semua proses `Worker`, secara otomatis mendaur ulang proses `Worker` yang siklus hidupnya berakhir atau mengalami error, dan membuat proses `Worker` baru.

    * Worker Process

      * Memproses data yang diterima, termasuk mengurai protokol dan merespons permintaan. Jika `worker_num` tidak diatur, level bawah akan menjalankan proses `Worker` sebanyak jumlah `CPU`.
      * Jika gagal mulai, ekstensi akan melempar fatal error, periksa informasi terkait di `php error_log`. `errno={number}` adalah `Linux Errno` standar, lihat dokumentasi terkait.
      * Jika pengaturan `log_file` diaktifkan, informasi akan dicetak ke file `Log` yang ditentukan.

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Kesalahan Umum Saat Gagal Mulai**

    * Gagal `bind` port, karena proses lain sudah menggunakan port tersebut.
    * Fungsi callback wajib belum diatur, menyebabkan kegagalan mulai.
    * Kode `PHP` mengandung fatal error, periksa informasi error PHP `php_errors.log`.
    * Jalankan `ulimit -c unlimited`, aktifkan `core dump`, periksa apakah ada segment fault.
    * Matikan `daemonize`, matikan `log`, agar pesan error dapat dicetak ke layar.

## reload()

Restart semua proses Worker/Task dengan aman.

```php
Swoole\Server->reload(bool $only_reload_taskworker = false): bool
```

!> Contoh: server backend yang sibuk setiap saat memproses permintaan. Jika administrator menghentikan/restart program server melalui proses `kill`, dapat menyebabkan kode berhenti di tengah eksekusi.  
Dalam situasi ini dapat terjadi inkonsistensi data. Dalam sistem transaksi, misalnya logika pembayaran dilanjutkan dengan pengiriman barang, jika proses dihentikan setelah logika pembayaran, menyebabkan pengguna membayar tetapi tidak mendapat barang, konsekuensinya sangat serius.  
`Swoole` menyediakan mekanisme penghentian/restart yang aman. Administrator hanya perlu mengirim sinyal tertentu ke `Server`, proses `Worker` dapat berhenti dengan aman. Lihat [Cara merestart service dengan benar](/question/use?id=cara-merestart-service-dengan-benar-di-swoole).

  * **Parameter**

    * `bool $only_reload_taskworker`

      * Fungsi: hanya restart [Task proses](/learn?id=proses-taskworker)
      * Default: false
      * Nilai lain: true

!> -`reload` memiliki mekanisme proteksi. Saat `reload` sedang berlangsung, sinyal restart baru yang diterima akan diabaikan.  
-Jika `user/group` diatur, proses `Worker` mungkin tidak memiliki izin untuk mengirim informasi ke proses `master`. Dalam kasus ini, harus menggunakan akun `root` dan menjalankan perintah `kill` di `shell` untuk restart.  
-Perintah `reload` tidak berlaku untuk user process yang ditambahkan melalui [addProcess](/server/methods?id=addProcess).

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Ekstensi**

    * **Mengirim Sinyal**

        * `SIGTERM`: Kirim sinyal ini ke proses utama/proses manajemen untuk menghentikan server dengan aman.
        * Dalam kode PHP, panggil `$serv->shutdown()` untuk menyelesaikan operasi ini.
        * `SIGUSR1`: Kirim sinyal `SIGUSR1` ke proses utama/proses manajemen untuk `restart` semua proses `Worker` dan `TaskWorker` secara lancar.
        * `SIGUSR2`: Kirim sinyal `SIGUSR2` ke proses utama/proses manajemen untuk merestart semua proses `Task` secara lancar.
        * Dalam kode PHP, panggil `$serv->reload()` untuk menyelesaikan operasi ini.

    ```shell
    # Restart semua worker process
    kill -USR1 PID_proses_utama

    # Hanya restart task process
    kill -USR2 PID_proses_utama
    ```

      > [Referensi: Daftar Sinyal Linux](/other/signal)

    * **Mode Process**

        Dalam mode `Process`, koneksi `TCP` dari client dipertahankan dalam proses `Master`. Restart atau keluar error dari proses `worker` tidak memengaruhi koneksi itu sendiri.

    * **Mode Base**

        Dalam mode `Base`, koneksi client dipertahankan langsung dalam proses `Worker`, sehingga reload akan memutus semua koneksi.

    !> Mode `Base` tidak mendukung reload [Task proses](/learn?id=proses-taskworker)

    * **Lingkup Reload**

      Operasi `Reload` hanya dapat memuat ulang file PHP yang dimuat oleh proses `Worker` setelah mulai. Gunakan fungsi `get_included_files` untuk mendaftar file PHP mana yang dimuat sebelum `WorkerStart`. File PHP dalam daftar ini tidak dapat dimuat ulang meskipun dilakukan `reload`. Perlu menutup server dan restart agar perubahan berlaku.

    ```php
    $serv->on('WorkerStart', function(Swoole\Server $server, int $workerId) {
        var_dump(get_included_files()); // File dalam array ini dimuat sebelum proses mulai, jadi tidak bisa di-reload
    });
    ```

    * **APC/OPcache**

        Jika `PHP` mengaktifkan `APC/OPcache`, reload akan terpengaruh. Ada `2` solusi:

        * Aktifkan deteksi `stat` pada `APC/OPcache`. Jika file diperbarui, `APC/OPcache` akan otomatis memperbarui `OPCode`.
        * Jalankan `apc_clear_cache` atau `opcache_reset` sebelum memuat file di `onWorkerStart` untuk menyegarkan cache `OPCode`.

  * **Catatan**

    !> -Restart lancar hanya berlaku untuk file PHP yang di-`include/require` dalam [onWorkerStart](/server/events?id=onworkerstart) atau [onReceive](/server/events?id=onreceive) dalam proses `Worker`.
    -File PHP yang sudah di-`include/require` sebelum `Server` mulai tidak dapat dimuat ulang melalui restart lancar.
    -Untuk konfigurasi `Server` yang diatur melalui `$serv->set()`, harus menutup/restart seluruh `Server` untuk memuat ulang.
    -`Server` dapat mendengarkan port internal, lalu menerima perintah kontrol jarak jauh untuk merestart semua proses `Worker`.

## stop()

Menghentikan proses `Worker` saat ini dan segera memicu fungsi callback `onWorkerStop`.

```php
Swoole\Server->stop(int $workerId = -1, bool $waitEvent = false): bool
```

  * **Parameter**

    * `int $workerId`

      * Fungsi: menentukan `worker id`
      * Default: -1, berarti proses saat ini
      * Nilai lain: tidak ada

    * `bool $waitEvent`

      * Fungsi: mengontrol strategi keluar, `false` berarti keluar segera, `true` berarti menunggu event loop kosong baru keluar
      * Default: false
      * Nilai lain: true
      * Setelah versi `v6.1.0`, parameter ini telah dihapus, logika keluar dikendalikan oleh opsi `reload_async`

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Tips**

    !> -Server [I/O asynchronous](/learn?id=sync-io-async-io) saat memanggil `stop` untuk keluar dari proses, mungkin masih ada event yang menunggu. Misalnya menggunakan `Swoole\MySQL->query`, mengirim pernyataan `SQL`, tetapi masih menunggu hasil dari server MySQL. Jika proses dipaksa keluar, hasil eksekusi `SQL` akan hilang.  
    -Mengatur `$waitEvent = true` akan menggunakan strategi [restart aman asynchronous](/question/use?id=cara-merestart-service-dengan-benar-di-swoole). Pertama memberi tahu proses `Manager` untuk memulai `Worker` baru guna menangani permintaan baru. `Worker` lama akan menunggu event hingga event loop kosong atau melebihi `max_wait_time`, lalu keluar, memaksimalkan keamanan event asynchronous.

## shutdown()

Menutup server.

```php
Swoole\Server->shutdown(): bool
```

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Tips**

    * Fungsi ini dapat digunakan dalam proses `Worker`.
    * Mengirim `SIGTERM` ke proses utama juga dapat menutup server.

```shell
kill -15 PID_proses_utama
```

## tick()

Menambahkan timer `tick` dengan fungsi callback kustom. Fungsi ini adalah alias dari [Swoole\Timer::tick](/timer?id=tick).

```php
Swoole\Server->tick(int $millisecond, callable $callback): void
```

  * **Parameter**

    * `int $millisecond`

      * Fungsi: interval waktu [milidetik]
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `callable $callback`

      * Fungsi: fungsi callback
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Catatan**

    !> -Setelah proses `Worker` berhenti, semua timer akan otomatis dihapus  
    -Timer `tick/after` tidak dapat digunakan sebelum `Server->start`  
    -Setelah `Swoole 5`, penggunaan alias ini telah dihapus; gunakan langsung `Swoole\Timer::tick()`

  * **Contoh**

    * Penggunaan dalam [onReceive](/server/events?id=onreceive)

    ```php
    function onReceive(Swoole\Server $server, int $fd, int $reactorId, mixed $data)
    {
        $server->tick(1000, function () use ($server, $fd) {
            $server->send($fd, "hello world");
        });
    }
    ```

    * Penggunaan dalam [onWorkerStart](/server/events?id=onworkerstart)

    ```php
    function onWorkerStart(Swoole\Server $server, int $workerId)
    {
        if (!$server->taskworker) {
            $server->tick(1000, function ($id) {
              var_dump($id);
            });
        } else {
            //task
            $server->tick(1000);
        }
    }
    ```

## after()

Menambahkan timer satu kali yang akan dihapus setelah dijalankan. Fungsi ini adalah alias dari [Swoole\Timer::after](/timer?id=after).

```php
Swoole\Server->after(int $millisecond, callable $callback)
```

  * **Parameter**

    * `int $millisecond`

      * Fungsi: waktu eksekusi [milidetik]
      * Default: tidak ada
      * Nilai lain: tidak ada
      * Versi: maksimum tidak boleh melebihi `86400000` di versi di bawah `Swoole v4.2.10`

    * `callable $callback`

      * Fungsi: fungsi callback, harus dapat dipanggil, fungsi `callback` tidak menerima parameter apapun
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Catatan**

    !> -Siklus hidup timer adalah level proses. Saat menggunakan `reload` atau `kill` untuk merestart/mematikan proses, semua timer akan dihapus  
    -Jika ada timer dengan logika dan data penting, implementasikan dalam fungsi callback `onWorkerStop`, atau lihat [Cara merestart service dengan benar](/question/use?id=cara-merestart-service-dengan-benar-di-swoole)  
    -Setelah `Swoole 5`, penggunaan alias ini telah dihapus; gunakan langsung `Swoole\Timer::after()`

## defer()

Menunda eksekusi suatu fungsi, alias dari [Swoole\Event::defer](/event?id=defer).

```php
Swoole\Server->defer(Callable $callback): void
```

  * **Parameter**

    * `Callable $callback`

      * Fungsi: fungsi callback [wajib], dapat berupa variabel fungsi yang dapat dieksekusi, string, array, atau fungsi anonim
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Catatan**

    !> -Sistem level bawah akan menjalankan fungsi ini setelah [EventLoop](/learn?id=apa-itu-eventloop) selesai. Tujuan fungsi ini adalah untuk menunda eksekusi beberapa kode PHP, sehingga program dapat memprioritaskan penanganan event `IO` lainnya. Misalnya, jika suatu fungsi callback membutuhkan komputasi CPU intensif tetapi tidak mendesak, biarkan proses menangani event lain terlebih dahulu baru melakukan komputasi CPU intensif  
    -Sistem tidak menjamin fungsi `defer` akan segera dijalankan. Jika ini adalah logika kritis sistem yang perlu segera dijalankan, gunakan timer `after`  
    -Saat menjalankan `defer` dalam callback `onWorkerStart`, harus menunggu event terjadi baru akan dipanggil  
    -Setelah `Swoole 5`, penggunaan alias ini telah dihapus; gunakan langsung `Swoole\Event::defer()`

  * **Contoh**

```php
function query($server, $db) {
    $server->defer(function() use ($db) {
        $db->close();
    });
}
```

## clearTimer()

Menghapus timer `tick/after`, fungsi ini adalah alias dari [Swoole\Timer::clear](/timer?id=clear).

```php
Swoole\Server->clearTimer(int $timerId): bool
```

  * **Parameter**

    * `int $timerId`

      * Fungsi: menentukan id timer
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Catatan**

    !> -`clearTimer` hanya dapat digunakan untuk menghapus timer di proses saat ini     
    -Setelah `Swoole 5`, penggunaan alias ini telah dihapus; gunakan langsung `Swoole\Timer::clear()`

  * **Contoh**

```php
$timerId = $server->tick(1000, function ($timerId) use ($server) {
    $server->clearTimer($timerId);//$id adalah id timer
});
```

## close()

Menutup koneksi client.

```php
Swoole\Server->close(int $fd, bool $reset = false): bool
```

  * **Parameter**

    * `int $fd`

      * Fungsi: menentukan `fd` (file descriptor) yang akan ditutup
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `bool $reset`

      * Fungsi: jika diatur ke `true`, akan memaksa menutup koneksi dan membuang data dalam antrian pengiriman
      * Default: false
      * Nilai lain: true

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Catatan**

  !> -`Server` yang menutup koneksi secara aktif juga akan memicu event [onClose](/server/events?id=onclose)  
  -Jangan menulis logika pembersihan setelah `close`. Letakkan dalam callback [onClose](/server/events?id=onclose)  
  -`fd` dari `HTTP\Server` dapat diperoleh melalui `response` dalam method callback tingkat atas

  * **Contoh**

```php
$server->on('request', function ($request, $response) use ($server) {
    $server->close($response->fd);
});
```

## send()

Mengirim data ke client.

```php
Swoole\Server->send(int|string $fd, string $data, int $serverSocket = -1): bool
```

  * **Parameter**

    * `int|string $fd`

      * Fungsi: menentukan file descriptor client atau path unix socket
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `string $data`

      * Fungsi: data yang dikirim, untuk protokol `TCP` maksimum tidak boleh melebihi `2M`, dapat diubah dengan [buffer_output_size](/server/setting?id=buffer_output_size)
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `int $serverSocket`

      * Fungsi: diperlukan saat mengirim data ke [UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php), client TCP tidak perlu mengisi
      * Default: -1, berarti port udp yang sedang didengarkan
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Tips**

    !> Proses pengiriman bersifat asynchronous; level bawah akan otomatis memantau penulisan dan mengirim data ke client secara bertahap, artinya client tidak langsung menerima data setelah `send` kembali.

    * Keamanan
      * Operasi `send` bersifat atomik. Beberapa proses secara bersamaan memanggil `send` ke koneksi `TCP` yang sama tidak akan menyebabkan pencampuran data.

    * Batasan Panjang
      * Jika perlu mengirim data lebih dari `2M`, tulis data ke file sementara, lalu kirim melalui interface `sendfile`.
      * Dengan mengatur parameter [buffer_output_size](/server/setting?id=buffer_output_size) dapat mengubah batasan panjang pengiriman.
      * Saat mengirim data lebih dari `8K`, level bawah akan mengaktifkan memori bersama proses `Worker`, perlu melakukan operasi `Mutex->lock`.

    * Buffer
      * Saat buffer [unixSocket](/learn?id=apa-itu-ipc) proses `Worker` penuh, pengiriman data `8K` akan menggunakan penyimpanan file sementara.
      * Jika terus mengirim data dalam jumlah besar ke client yang sama dan client tidak sempat menerima, buffer memori `Socket` akan penuh. Swoole level bawah akan segera mengembalikan `false`. Saat `false`, data dapat disimpan ke disk dan dikirim setelah client selesai menerima data yang sudah dikirim.

    * [Penjadwalan Coroutine](/coroutine?id=penjadwalan-coroutine)
      * Dalam mode coroutine dengan [send_yield](/server/setting?id=send_yield) diaktifkan, `send` akan otomatis tertunda saat buffer penuh. Coroutine akan dilanjutkan setelah sebagian data dibaca oleh lawan, dan terus mengirim data.

    * [UnixSocket](/learn?id=apa-itu-ipc)
      * Saat mendengarkan port [UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php), dapat menggunakan `send` untuk mengirim data ke lawan.

      ```php
      $server->on("packet", function (Swoole\Server $server, $data, $addr){
          $server->send($addr['address'], 'SUCCESS', $addr['server_socket']);
      });
      ```

## sendfile()

Mengirim file ke koneksi client `TCP`.

```php
Swoole\Server->sendfile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
```

  * **Parameter**

    * `int $fd`

      * Fungsi: menentukan file descriptor client
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `string $filename`

      * Fungsi: path file yang akan dikirim, mengembalikan `false` jika file tidak ada
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `int $offset`

      * Fungsi: menentukan offset file, dapat mengirim data mulai dari posisi tertentu dalam file
      * Default: 0 [Default `0`, berarti mulai dari awal file]
      * Nilai lain: tidak ada

    * `int $length`

      * Fungsi: menentukan panjang yang akan dikirim
      * Default: ukuran file
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Catatan**

  !> Fungsi ini dan `Server->send` sama-sama mengirim data ke client, bedanya data `sendfile` berasal dari file yang ditentukan.

## sendto()

Mengirim paket `UDP` ke `IP:PORT` client mana pun.

```php
Swoole\Server->sendto(string $ip, int $port, string $data, int $serverSocket = -1): bool
```

  * **Parameter**

    * `string $ip`

      * Fungsi: menentukan `ip` client
      * Default: tidak ada
      * Nilai lain: tidak ada

      ?> `$ip` adalah string `IPv4` atau `IPv6`, misalnya `192.168.1.102`. Jika `IP` tidak valid, akan mengembalikan error.

    * `int $port`

      * Fungsi: menentukan `port` client
      * Default: tidak ada
      * Nilai lain: tidak ada

      ?> `$port` adalah nomor port jaringan `1-65535`, jika port salah, pengiriman akan gagal.

    * `string $data`

      * Fungsi: konten data yang akan dikirim, dapat berupa teks atau biner
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `int $serverSocket`

      * Fungsi: menentukan port mana yang digunakan untuk mengirim paket data, deskriptor `server_socket` [dapat diperoleh di `$clientInfo` pada event [onPacket](/server/events?id=onpacket)]
      * Default: -1, berarti port udp yang sedang didengarkan
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

      ?> Server mungkin mendengarkan beberapa port `UDP` secara bersamaan, lihat [Multi-port Listening](/server/port). Parameter ini dapat menentukan port mana yang digunakan untuk mengirim paket data.

  * **Catatan**

  !> Harus mendengarkan port `UDP` untuk dapat mengirim data ke alamat `IPv4`  
  Harus mendengarkan port `UDP6` untuk dapat mengirim data ke alamat `IPv6`

  * **Contoh**

```php
// Mengirim string "hello world" ke host dengan IP 220.181.57.216 port 9502.
$server->sendto('220.181.57.216', 9502, "hello world");
// Mengirim paket UDP ke server IPv6
$server->sendto('2600:3c00::f03c:91ff:fe73:e98f', 9501, "hello world");
```

## sendwait()

Mengirim data ke client secara sinkron.

```php
Swoole\Server->sendwait(int $fd, string $data): bool
```

  * **Parameter**

    * `int $fd`

      * Fungsi: menentukan file descriptor client
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `string $data`

      * Fungsi: data yang akan dikirim
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Tips**

    * Dalam beberapa skenario khusus, `Server` perlu terus mengirim data ke client, sedangkan interface pengiriman data `Server->send` murni asynchronous. Pengiriman data dalam jumlah besar dapat menyebabkan antrian pengiriman memori penuh.

    * Menggunakan `Server->sendwait` dapat mengatasi masalah ini. `Server->sendwait` akan menunggu koneksi dapat ditulis. Hanya akan kembali setelah data selesai dikirim.

  * **Catatan**

  !> `sendwait` saat ini hanya tersedia untuk mode [SWOOLE_BASE](/learn?id=swoole_base)  
  `sendwait` hanya digunakan untuk komunikasi lokal atau internal. Jangan gunakan `sendwait` untuk koneksi eksternal, dan saat `enable_coroutine` => true (default aktif) jangan gunakan fungsi ini karena akan memblokir coroutine lain. Hanya server blocking sinkron yang dapat menggunakannya.

## sendMessage()

Mengirim pesan ke proses `Worker` mana pun atau [Task proses](/learn?id=proses-taskworker). Dapat dipanggil di non-main process dan management process. Proses yang menerima pesan akan memicu event `onPipeMessage`.

```php
Swoole\Server->sendMessage(mixed $message, int $workerId): bool
```

  * **Parameter**

    * `mixed $message`

      * Fungsi: konten data pesan yang akan dikirim, tanpa batasan panjang, tetapi jika melebihi `8K` akan menggunakan file memori sementara
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `int $workerId`

      * Fungsi: `ID` proses target, rentang lihat [$worker_id](/server/properties?id=worker_id)
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Tips**

    * Memanggil `sendMessage` dalam proses `Worker` bersifat [I/O asynchronous](/learn?id=sync-io-async-io); pesan akan disimpan di buffer terlebih dahulu dan dikirim ke [unixSocket](/learn?id=apa-itu-ipc) saat bisa ditulis.
    * Memanggil `sendMessage` dalam [Task proses](/learn?id=proses-taskworker) secara default bersifat [I/O sinkron](/learn?id=sync-io-async-io), tetapi dalam beberapa kasus dapat otomatis beralih ke I/O asynchronous, lihat [Mengubah I/O Sinkron menjadi I/O Asynchronous](/learn?id=sync-io-converted-to-async-io).
    * Memanggil `sendMessage` dalam [User process](/server/methods?id=addprocess) sama seperti Task, defaultnya sinkron dan blocking, lihat [Mengubah I/O Sinkron menjadi I/O Asynchronous](/learn?id=sync-io-converted-to-async-io).

  * **Catatan**

  !> - Jika `sendMessage()` bersifat [I/O asynchronous](/learn?id=sync-io-converted-to-async-io), jangan terus memanggil `sendMessage()` jika proses lawan tidak menerima data karena berbagai alasan, karena akan menghabiskan banyak sumber daya memori. Dapat menambahkan mekanisme respons, jika lawan tidak merespons, hentikan pemanggilan.  
  -Di `MacOS/FreeBSD`, jika melebihi `2K` akan menggunakan penyimpanan file sementara.  
  -Menggunakan [sendMessage](/server/methods?id=sendMessage) harus mendaftarkan fungsi callback event `onPipeMessage`.  
  -Mengatur [task_ipc_mode](/server/setting?id=task_ipc_mode) = 3 akan mencegah penggunaan [sendMessage](/server/methods?id=sendMessage) untuk mengirim pesan ke task process tertentu.

  * **Contoh**

```php
$server = new Swoole\Server('0.0.0.0', 9501);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 2,
));
$server->on('pipeMessage', function ($server, $src_worker_id, $data) {
    echo "#{$server->worker_id} message from #$src_worker_id: $data\n";
});
$server->on('task', function ($server, $task_id, $src_worker_id, $data) {
    var_dump($task_id, $src_worker_id, $data);
});
$server->on('finish', function ($server, $task_id, $data) {

});
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    if (trim($data) == 'task') {
        $server->task("async task coming");
    } else {
        $worker_id = 1 - $server->worker_id;
        $server->sendMessage("hello task process", $worker_id);
    }
});

$server->start();
```

## exist()

Memeriksa apakah koneksi yang sesuai dengan `fd` ada.

```php
Swoole\Server->exist(int $fd): bool
```

  * **Parameter**

    * `int $fd`

      * Fungsi: file descriptor
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika ada, `false` jika tidak ada.

  * **Tips**

    * Interface ini berbasis komputasi memori bersama, tanpa operasi `IO` apapun.

## pause()

Berhenti menerima data.

```php
Swoole\Server->pause(int $fd): bool
```

  * **Parameter**

    * `int $fd`

      * Fungsi: menentukan file descriptor
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Tips**

    * Setelah memanggil fungsi ini, koneksi akan dihapus dari [EventLoop](/learn?id=apa-itu-eventloop) dan tidak lagi menerima data client.
    * Fungsi ini tidak memengaruhi pemrosesan antrian pengiriman.
    * Hanya dalam mode `SWOOLE_PROCESS`, setelah memanggil `pause`, mungkin masih ada data yang sudah sampai di proses `Worker`, sehingga masih dapat memicu event [onReceive](/server/events?id=onreceive).

## resume()

Melanjutkan penerimaan data. Digunakan berpasangan dengan method `pause`.

```php
Swoole\Server->resume(int $fd): bool
```

  * **Parameter**

    * `int $fd`

      * Fungsi: menentukan file descriptor
      * Default: tidak ada
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Tips**

    * Setelah memanggil fungsi ini, koneksi akan ditambahkan kembali ke [EventLoop](/learn?id=apa-itu-eventloop) dan terus menerima data client.

## getCallback()

Mendapatkan fungsi callback untuk event tertentu pada Server.

```php
Swoole\Server->getCallback(string $event_name): \Closure|string|null|array
```

  * **Parameter**

    * `string $event_name`

      * Fungsi: nama event, tidak perlu menambahkan `on`, tidak case-sensitive
      * Default: tidak ada
      * Nilai lain: Lihat [Event](/server/events)

  * **Nilai Kembali**

    * Jika fungsi callback yang sesuai ada, mengembalikan `Closure` / `string` / `array` berdasarkan [cara pengaturan fungsi callback](/learn?id=empat-cara-mengatur-fungsi-callback).
    * Jika fungsi callback yang sesuai tidak ada, mengembalikan `null`.

## getClientInfo()

Mendapatkan informasi koneksi, alias `Swoole\Server->connection_info()`.

```php
Swoole\Server->getClientInfo(int $fd, int $reactorId = -1, bool $ignoreError = false): false|array
```

  * **Parameter**

    * `int $fd`

      * Fungsi: menentukan file descriptor
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `int $reactorId`

      * Fungsi: `ID` thread [Reactor](/learn?id=thread-reactor) tempat koneksi berada, saat ini tidak berpengaruh, hanya untuk kompatibilitas API
      * Default: -1
      * Nilai lain: tidak ada

    * `bool $ignoreError`

      * Fungsi: apakah mengabaikan error. Jika diatur ke `true`, akan mengembalikan informasi koneksi meskipun koneksi ditutup. `false` berarti mengembalikan false saat koneksi ditutup.
      * Default: false
      * Nilai lain: tidak ada

  * **Tips**

    * Sertifikat client

      * Hanya dapat diperoleh dalam proses yang dipicu oleh [onConnect](/server/events?id=onconnect)
      * Formatnya `x509`, informasi sertifikat dapat diperoleh menggunakan fungsi `openssl_x509_parse`.

    * Saat menggunakan konfigurasi [dispatch_mode](/server/setting?id=dispatch_mode) = 1/3, karena strategi distribusi paket data ini digunakan untuk layanan tanpa status, informasi terkait akan langsung dihapus dari memori setelah koneksi terputus, sehingga `Server->getClientInfo` tidak dapat memperoleh informasi koneksi terkait.

  * **Nilai Kembali**

    * Mengembalikan `false` jika gagal.
    * Mengembalikan `array` berisi informasi client jika berhasil.

```php
$fd_info = $server->getClientInfo($fd);
var_dump($fd_info);

array(15) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(25)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(39136)
  ["remote_ip"]=>
  string(9) "127.0.0.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1677322106)
  ["last_time"]=>
  int(1677322106)
  ["last_recv_time"]=>
  float(1677322106.901918)
  ["last_send_time"]=>
  float(0)
  ["last_dispatch_time"]=>
  float(0)
  ["close_errno"]=>
  int(0)
  ["recv_queued_bytes"]=>
  int(78)
  ["send_queued_bytes"]=>
  int(0)
}
```

Parameter | Keterangan
---|---
server_port | Port listening server
server_fd | Server fd
socket_fd | Client fd
socket_type | Tipe socket
remote_port | Port client
remote_ip | IP client
reactor_id | Dari thread Reactor mana
connect_time | Waktu client terhubung ke Server, dalam detik, diatur oleh master process
last_time | Waktu terakhir menerima data, dalam detik, diatur oleh master process
last_recv_time | Waktu terakhir menerima data, dalam detik, diatur oleh master process
last_send_time | Waktu terakhir mengirim data, dalam detik, diatur oleh master process
last_dispatch_time | Waktu worker process menerima data
close_errno | Kode error saat koneksi ditutup. Jika koneksi ditutup secara error, close_errno bernilai non-nol, lihat daftar informasi error Linux
recv_queued_bytes | Jumlah data yang menunggu diproses
send_queued_bytes | Jumlah data yang menunggu dikirim
websocket_status | [Opsional] Status koneksi WebSocket, ditambahkan saat server adalah Swoole\WebSocket\Server
uid | [Opsional] Ditambahkan saat user ID diikat menggunakan bind
ssl_client_cert | [Opsional] Ditambahkan saat enkripsi tunnel SSL digunakan dan client mengatur sertifikat

## getClientList()

Melintasi semua koneksi client dari `Server` saat ini. Method `Server::getClientList` berbasis memori bersama, tanpa `IOWait`, sehingga kecepatan lintasnya sangat cepat. Selain itu, `getClientList` mengembalikan semua koneksi `TCP`, bukan hanya koneksi `TCP` dari proses `Worker` saat ini. Alias `Swoole\Server->connection_list()`.

```php
Swoole\Server->getClientList(int $start_fd = 0, int $pageSize = 10): false|array
```

  * **Parameter**

    * `int $start_fd`

      * Fungsi: menentukan `fd` awal
      * Default: 0
      * Nilai lain: tidak ada

    * `int $pageSize`

      * Fungsi: jumlah data per halaman, maksimum tidak boleh melebihi `100`
      * Default: 10
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Jika berhasil, mengembalikan array indeks numerik, dengan elemen adalah `$fd` yang diperoleh. Array akan diurutkan dari kecil ke besar. `$fd` terakhir digunakan sebagai `start_fd` baru untuk pengambilan selanjutnya.
    * Mengembalikan `false` jika gagal.

  * **Tips**

    * Disarankan menggunakan iterator [Server::$connections](/server/properties?id=connections) untuk melintasi koneksi.
    * `getClientList` hanya tersedia untuk client `TCP`. Server `UDP` perlu menyimpan informasi client sendiri.
    * Dalam mode [SWOOLE_BASE](/learn?id=swoole_base), hanya dapat memperoleh koneksi dari proses saat ini.

  * **Contoh**

```php
$start_fd = 0;
while (true) {
  $conn_list = $server->getClientList($start_fd, 10);
  if ($conn_list === false || count($conn_list) === 0) {
      echo "finish\n";
      break;
  }
  $start_fd = end($conn_list);
  var_dump($conn_list);
  foreach ($conn_list as $fd) {
      $server->send($fd, "broadcast");
  }
}
```

## bind()

Mengikat koneksi dengan `UID` yang ditentukan pengguna. Mengatur [dispatch_mode](/server/setting?id=dispatch_mode)=5 akan menggunakan nilai ini untuk alokasi tetap `hash`. Ini memastikan bahwa semua koneksi dari `UID` tertentu akan dialokasikan ke proses `Worker` yang sama.

```php
Swoole\Server->bind(int $fd, int $uid): bool
```

  * **Parameter**

    * `int $fd`

      * Fungsi: menentukan `fd` koneksi
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `int $uid`

      * Fungsi: `UID` yang akan diikat, harus berupa angka bukan `0`
      * Default: tidak ada
      * Nilai lain: `UID` maksimum tidak boleh melebihi `4294967295`, minimum tidak boleh kurang dari `-2147483648`

  * **Nilai Kembali**

    * Mengembalikan `true` jika berhasil, `false` jika gagal.

  * **Tips**

    * Dapat menggunakan `$serv->getClientInfo($fd)` untuk melihat nilai `UID` yang diikat pada koneksi.
    * Dalam pengaturan default [dispatch_mode](/server/setting?id=dispatch_mode)=2, `Server` akan mendistribusikan data koneksi ke proses `Worker` yang berbeda berdasarkan `socket fd`. Karena `fd` tidak stabil (berubah saat client reconnect setelah disconnect), data client ini dapat dialokasikan ke `Worker` lain. Dengan menggunakan `bind`, distribusi dapat dilakukan berdasarkan `UID` yang ditentukan pengguna. Bahkan jika disconnect dan reconnect, data koneksi `TCP` dengan `UID` yang sama akan dialokasikan ke proses `Worker` yang sama.

    * Masalah Urutan

      * Setelah client terhubung ke server dan mengirim beberapa paket secara berurutan, mungkin ada masalah urutan. Dalam operasi `bind`, paket berikutnya mungkin sudah di-dispatch, dan paket data ini masih akan dialokasikan ke proses saat ini berdasarkan modulo `fd`. Hanya paket data yang diterima setelah `bind` yang akan dialokasikan berdasarkan modulo `UID`.
      * Oleh karena itu, jika ingin menggunakan mekanisme `bind`, protokol komunikasi jaringan perlu merancang langkah handshake. Setelah client berhasil terhubung, kirim permintaan handshake terlebih dahulu, lalu client jangan mengirim paket apapun. Setelah server selesai `bind` dan merespons, client baru dapat mengirim permintaan baru.

    * Pengikatan Ulang

      * Dalam beberapa kasus, logika bisnis mungkin memerlukan koneksi pengguna untuk diikat ulang dengan `UID` baru. Dalam hal ini, dapat memutus koneksi, membuat koneksi `TCP` baru dan handshake, lalu mengikat ke `UID` baru.

    * Mengikat `UID` Negatif

      * Jika `UID` yang diikat adalah negatif, akan diubah menjadi `unsigned 32-bit integer` di level bawah. Di lapisan PHP, perlu diubah menjadi `signed 32-bit integer`. Dapat menggunakan:

  ```php
  $uid = -10;
  $server->bind($fd, $uid);
  $bindUid = $server->connection_info($fd)['uid'];
  $bindUid = $bindUid >> 31 ? (~($bindUid - 1) & 0xFFFFFFFF) * -1 : $bindUid;
  var_dump($bindUid === $uid);
  ```

  * **Catatan**

!> -Hanya efektif saat mengatur `dispatch_mode=5`  
-Saat `UID` belum diikat, distribusi default menggunakan modulo `fd`  
-Satu koneksi hanya dapat di-`bind` sekali. Jika sudah terikat `UID`, memanggil `bind` lagi akan mengembalikan `false`

  * **Contoh**

```php
$serv = new Swoole\Server('0.0.0.0', 9501);

$serv->fdlist = [];

$serv->set([
    'worker_num' => 4,
    'dispatch_mode' => 5,   //uid dispatch
]);

$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "{$fd} connect, worker:" . $serv->worker_id . PHP_EOL;
});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    $conn = $serv->connection_info($fd);
    print_r($conn);
    echo "worker_id: " . $serv->worker_id . PHP_EOL;
    if (empty($conn['uid'])) {
        $uid = $fd + 1;
        if ($serv->bind($fd, $uid)) {
            $serv->send($fd, "bind {$uid} success");
        }
    } else {
        if (!isset($serv->fdlist[$fd])) {
            $serv->fdlist[$fd] = $conn['uid'];
        }
        print_r($serv->fdlist);
        foreach ($serv->fdlist as $_fd => $uid) {
            $serv->send($_fd, "{$fd} say:" . $data);
        }
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "{$fd} Close". PHP_EOL;
    unset($serv->fdlist[$fd]);
});

$serv->start();
```

## stats()

Mendapatkan informasi seperti jumlah koneksi `TCP` aktif `Server` saat ini, waktu mulai, total jumlah `accept/close` (membangun/menutup koneksi), dll.

```php
Swoole\Server->stats(): array
```

  * **Contoh**

```php
array(25) {
  ["start_time"]=>
  int(1677310656)
  ["connection_num"]=>
  int(1)
  ["abort_count"]=>
  int(0)
  ["accept_count"]=>
  int(1)
  ["close_count"]=>
  int(0)
  ["worker_num"]=>
  int(2)
  ["task_worker_num"]=>
  int(4)
  ["user_worker_num"]=>
  int(0)
  ["idle_worker_num"]=>
  int(1)
  ["dispatch_count"]=>
  int(1)
  ["request_count"]=>
  int(0)
  ["response_count"]=>
  int(1)
  ["total_recv_bytes"]=>
  int(78)
  ["total_send_bytes"]=>
  int(165)
  ["pipe_packet_msg_id"]=>
  int(3)
  ["session_round"]=>
  int(1)
  ["min_fd"]=>
  int(4)
  ["max_fd"]=>
  int(25)
  ["worker_request_count"]=>
  int(0)
  ["worker_response_count"]=>
  int(1)
  ["worker_dispatch_count"]=>
  int(1)
  ["task_idle_worker_num"]=>
  int(4)
  ["tasking_num"]=>
  int(0)
  ["coroutine_num"]=>
  int(1)
  ["coroutine_peek_num"]=>
  int(1)
  ["task_queue_num"]=>
  int(1)
  ["task_queue_bytes"]=>
  int(1)
}
```

Parameter | Keterangan
---|---
start_time | Waktu server mulai
connection_num | Jumlah koneksi saat ini
abort_count | Jumlah koneksi yang ditolak
accept_count | Jumlah koneksi yang diterima
close_count | Jumlah koneksi yang ditutup
worker_num  | Jumlah worker process yang berjalan
task_worker_num  | Jumlah task worker process yang berjalan [Tersedia sejak `v4.5.7`]
user_worker_num  | Jumlah task worker process kustom
idle_worker_num | Jumlah worker process idle
dispatch_count | Jumlah paket yang dikirim Server ke Worker [Tersedia sejak `v4.5.7`, hanya efektif dalam mode [SWOOLE_PROCESS](/learn?id=swoole_process)]
request_count | Jumlah permintaan yang diterima Server [Hanya data request yang ditangani oleh onReceive, onMessage, onRequest, onPacket yang dihitung]
response_count | Jumlah respons yang dikirim Server
total_recv_bytes| Total data yang diterima
total_send_bytes | Total data yang dikirim
pipe_packet_msg_id | ID komunikasi antar proses
session_round | Session id awal
min_fd | File descriptor koneksi terkecil
max_fd | File descriptor koneksi terbesar
worker_request_count | Jumlah permintaan yang diterima Worker process saat ini [Worker process akan keluar jika worker_request_count melebihi max_request]
worker_response_count | Jumlah respons Worker process saat ini
worker_dispatch_count | Hitungan tugas yang dikirim dari master process ke Worker process saat ini, bertambah saat dispatch oleh [master process](/learn?id=thread-reactor)
task_idle_worker_num | Jumlah task process idle
tasking_num | Jumlah task process yang sedang bekerja
coroutine_num | Jumlah coroutine saat ini [Untuk Coroutine], informasi lebih lanjut lihat [bagian ini](/coroutine/gdb)
coroutine_peek_num | Jumlah total coroutine
task_queue_num | Jumlah task dalam antrian pesan [Untuk Task]
task_queue_bytes | Penggunaan memori antrian task dalam byte [Untuk Task]

## task()

Mengirim tugas asynchronous ke pool `task_worker`. Fungsi ini non-blocking, akan kembali segera setelah eksekusi. Proses `Worker` dapat terus menangani permintaan baru. Untuk menggunakan fitur `Task`, harus mengatur `task_worker_num` terlebih dahulu, dan harus mengatur fungsi callback event [onTask](/server/events?id=ontask) dan [onFinish](/server/events?id=onfinish) pada `Server`.

```php
Swoole\Server->task(mixed $data, int $dstWorkerId = -1, callable $finishCallback): int
```

  * **Parameter**

    * `mixed $data`

      * Fungsi: data tugas yang akan dikirim, harus berupa variabel PHP yang dapat diserialisasi
      * Default: tidak ada
      * Nilai lain: tidak ada

    * `int $dstWorkerId`

      * Fungsi: dapat menentukan [Task proses](/learn?id=proses-taskworker) tujuan, masukkan `ID` Task proses saja, rentangnya `[0, $server->setting['task_worker_num']-1]`
      * Default: -1 [Default `-1` berarti pengiriman acak, level bawah akan otomatis memilih [Task proses](/learn?id=proses-taskworker) yang idle]
      * Nilai lain: `[0, $server->setting['task_worker_num']-1]`

    * `callable $finishCallback`

      * Fungsi: fungsi callback `finish`. Jika tugas mengatur fungsi callback, saat Task mengembalikan hasil, fungsi callback yang ditentukan akan langsung dijalankan, dan callback [onFinish](/server/events?id=onfinish) dari `Server` tidak akan dijalankan. Hanya dapat dipicu saat mengirim tugas dari proses `Worker`.
      * Default: `null`
      * Nilai lain: tidak ada

  * **Nilai Kembali**

    * Jika berhasil, mengembalikan integer `$task_id`, yang merupakan `ID` tugas ini. Jika ada callback finish, [onFinish](/server/events?id=onfinish) akan membawa parameter `$task_id`.
    * Jika gagal, mengembalikan `false`. `$task_id` mungkin `0`, jadi harus menggunakan `===` untuk memeriksa kegagalan.

  * **Tips**

    * Fitur ini digunakan untuk menjalankan tugas lambat secara asynchronous, misalnya server chat room dapat menggunakannya untuk mengirim broadcast. Saat tugas selesai, panggil `$serv->finish("finish")` dalam [task process](/learn?id=proses-taskworker) untuk memberi tahu worker process bahwa tugas telah selesai. Tentu saja `Swoole\Server->finish` bersifat opsional.
    * `task` menggunakan komunikasi [unixSocket](/learn?id=apa-itu-ipc) di level bawah, sepenuhnya berbasis memori, tanpa konsumsi `IO`. Kinerja baca/tulis proses tunggal dapat mencapai `1 juta/s`. Proses yang berbeda menggunakan unixSocket yang berbeda untuk komunikasi, memaksimalkan pemanfaatan multi-core.
    * Jika target [Task proses](/learn?id=proses-taskworker) tidak ditentukan, memanggil method `task` akan memeriksa status sibuk/idle [Task proses](/learn?id=proses-taskworker). Level bawah hanya akan mengirim tugas ke [Task proses](/learn?id=proses-taskworker) yang sedang idle. Jika semua [Task proses](/learn?id=proses-taskworker) sibuk, level bawah akan melakukan polling untuk mengirim tugas ke setiap proses. Gunakan method [server->stats](/server/methods?id=stats) untuk mendapatkan jumlah tugas yang sedang mengantri.
    * Parameter ketiga dapat langsung mengatur fungsi [onFinish](/server/events?id=onfinish). Jika tugas mengatur fungsi callback, saat Task mengembalikan hasil, fungsi callback yang ditentukan akan langsung dijalankan, dan callback [onFinish](/server/events?id=onfinish) dari `Server` tidak akan dijalankan. Hanya dapat dipicu saat mengirim tugas dari proses `Worker`.

    ```php
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    ```

    * `$task_id` adalah integer dari `0-4,2 milyar`, unik dalam proses saat ini.
    * Secara default, fitur `task` tidak aktif. Perlu mengatur `task_worker_num` secara manual untuk mengaktifkan fitur ini.
    * Jumlah `TaskWorker` diatur dalam parameter [Server->set()](/server/methods?id=set), misalnya `task_worker_num => 64` berarti menjalankan `64` proses untuk menerima tugas asynchronous.

  * **Parameter Konfigurasi**

    * `Server->task/taskwait/finish` `3` method ini, jika data `$data` yang dikirim melebihi `8K` akan menggunakan file sementara untuk menyimpan. Jika konten file sementara melebihi [server->package_max_length](/server/setting?id=package_max_length), level bawah akan memunculkan peringatan. Peringatan ini tidak memengaruhi pengiriman data, tetapi Task yang terlalu besar mungkin memiliki masalah kinerja.

    ```shell
    WARN: task package is too big.
    ```

  * **Tugas Satu Arah**

    * Tugas yang dikirim dari proses `Master`, `Manager`, `UserProcess` bersifat satu arah. Dalam `TaskWorker` process, tidak dapat menggunakan `return` atau `Server->finish()` untuk mengembalikan data hasil.

  * **Catatan**

  !> -Method `task` tidak dapat dipanggil dalam [task process](/learn?id=proses-taskworker)  
  -Menggunakan `task` harus mengatur callback [onTask](/server/events?id=ontask) dan [onFinish](/server/events?id=onfinish) untuk `Server`, jika tidak `Server->start` akan gagal  
  -Jumlah operasi `task` harus kurang dari kecepatan pemrosesan [onTask](/server/events?id=ontask). Jika kapasitas pengiriman melebihi kapasitas pemrosesan, data `task` akan memenuhi buffer, menyebabkan proses `Worker` terblokir. Proses `Worker` tidak akan dapat menerima permintaan baru  
  -Dalam user process yang ditambahkan dengan [addProcess](/server/method?id=addProcess), dapat menggunakan `task` untuk mengirim tugas satu arah, tetapi tidak dapat mengembalikan data hasil. Gunakan interface [sendMessage](/server/methods?id=sendMessage) untuk berkomunikasi dengan proses `Worker/Task`

  * **Contoh**

```php
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 4,
));

$server->on('Receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    echo "Menerima data" . $data . "\n";
    $data    = trim($data);
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    $task_id = $server->task($data, 0);
    $server->send($fd, "Mendistribusikan tugas, id tugas adalah $task_id\n");
});
```
## taskwait()

`taskwait` adalah method dengan tujuan yang sama seperti method `task`, digunakan untuk mengirimkan tugas asynchronous ke pool [task worker](/learn?id=taskworker-process) untuk dieksekusi. Tidak seperti `task`, `taskwait` adalah fungsi synchronous, ia menunggu sampai tugas selesai atau timeout. `$result` adalah hasil eksekusi tugas, dikirim oleh fungsi `$server->finish`. Jika tugas timeout, ia akan mengembalikan `false`.

```php
Swoole\Server->taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1): mixed
```

* **Parameter**

    * `mixed $data`

        * Fungsi: Data tugas yang akan dikirim, bisa berupa tipe apa pun, tipe non-string akan diserialisasi secara otomatis oleh sistem.
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

    * `float $timeout`

        * Fungsi: Durasi timeout, tipe float, dalam detik, mendukung granularitas minimal `1ms`. Jika tidak ada data yang dikembalikan dari [task worker](/learn?id=taskworker-process) yang ditentukan dalam waktu yang ditentukan, `taskwait` akan mengembalikan `false` dan tidak akan memproses data hasil tugas berikutnya.
        * Nilai Default: 0.5
        * Nilai Lain: Tidak ada

    * `int $dstWorkerId`

        * Fungsi: Tentukan [task worker](/learn?id=taskworker-process) yang akan menerima tugas. Cukup berikan ID task worker, dalam rentang `[0, $server->setting['task_worker_num']-1]`.
        * Nilai Default: -1 (Nilai default `-1` berarti kirim secara acak, sistem akan secara otomatis memilih [task worker](/learn?id=taskworker-process) yang sedang tidak sibuk.)
        * Nilai Lain: `[0, $server->setting['task_worker_num']-1]`

* **Nilai Kembalian**

    * Mengembalikan `false` menunjukkan kegagalan pengiriman tugas.
    * Jika method `finish` dijalankan dalam event `onTask` atau melakukan `return`, maka `taskwait` akan mengembalikan hasil yang dikirim oleh event `onTask`.

* **Tips**

    * **Mode Coroutine**

        * Mulai dari versi `4.0.4`, method `taskwait` akan mendukung [penjadwalan coroutine](/coroutine?id=coroutine-scheduling). Ketika `Server->taskwait()` dipanggil dalam coroutine, ia akan secara otomatis menjalani [penjadwalan coroutine](/coroutine?id=coroutine-scheduling), menghindari blocking wait.
        * Memanfaatkan [penjadwal coroutine](/coroutine?id=coroutine-scheduler), `taskwait` dapat mencapai panggilan concurrent.
        * Hanya boleh ada satu return atau satu `Server->finish` dalam event `onTask`, jika tidak, peringatan expired task[1] akan dihasilkan setelah return atau `Server->finish` berlebih dijalankan.

    * **Mode Synchronous**

        * Dalam mode blocking synchronous, `taskwait` memerlukan komunikasi [UnixSocket](/learn?id=what-is-ipc) dan shared memory untuk mengembalikan data ke proses `Worker`, dan proses ini bersifat synchronous dan blocking.

    * **Kasus Khusus**

        * Jika tidak ada operasi [synchronous I/O](/learn?id=synchronous-io-asynchronous-io) dalam event [onTask](/server/events?id=ontask), dengan hanya `2` kali perpindahan proses di level bawah, dan tidak ada wait `IO` yang dihasilkan. Dalam kasus ini, `taskwait` dapat dianggap non-blocking. Dalam pengujian aktual, hanya membaca dan menulis array `PHP` dalam event [onTask](/server/events?id=ontask), dengan `100.000` operasi `taskwait` hanya memakan waktu `1` detik, rata-rata `10` mikrodetik per operasi.

* **Perhatian**

    !> - Jangan gunakan `Swoole\Server::finish` dengan `taskwait`.  
    - Method `taskwait` tidak dapat dipanggil di [task worker](/learn?id=taskworker-process).
## taskWaitMulti()

Menjalankan beberapa tugas asynchronous secara concurrent. Method ini tidak mendukung penjadwalan coroutine dan dapat menyebabkan coroutine lain ikut berjalan. Di lingkungan coroutine, Anda perlu menggunakan `taskCo` seperti yang dijelaskan di bawah.

```php
Swoole\Server->taskWaitMulti(array $tasks, float $timeout = 0.5): false|array
```

* **Parameter**

    * `array $tasks`

        * Fungsi: Harus berupa array dengan indeks numerik, tidak mendukung array asosiatif. Sistem akan melakukan iterasi melalui `$tasks` dan mengirimkan setiap tugas ke [Task Worker Process](/learn?id=taskworker-process) satu per satu.
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

    * `float $timeout`

        * Fungsi: Angka floating point yang menunjukkan waktu dalam detik.
        * Nilai Default: 0.5 detik
        * Nilai Lain: Tidak ada

* **Nilai Kembalian**

    * Ketika tugas selesai atau timeout, ia mengembalikan array hasil. Urutan hasil dalam array sesuai dengan urutan tugas dalam `$tasks`, contoh: `$result[2]` sesuai dengan `$tasks[2]`.
    * Timeout dari tugas tertentu tidak akan memengaruhi tugas lain. Array hasil tidak akan menyertakan tugas yang timeout.

* **Catatan**

    !> -Jumlah maksimum tugas concurrent tidak boleh melebihi `1024`.

* **Contoh**

```php
$tasks[] = mt_rand(1000, 9999); // Tugas 1
$tasks[] = mt_rand(1000, 9999); // Tugas 2
$tasks[] = mt_rand(1000, 9999); // Tugas 3
var_dump($tasks);

// Tunggu semua hasil tugas kembali, timeout diatur ke 10 detik
$results = $server->taskWaitMulti($tasks, 10.0);

if (!isset($results[0])) {
    echo "Tugas 1 timeout\n";
}
if (isset($results[1])) {
    echo "Hasil Tugas 2 adalah {$results[1]}\n";
}
if (isset($results[2])) {
    echo "Hasil Tugas 3 adalah {$results[2]}\n";
}
```
## taskCo()

Menjalankan `Task` secara concurrent dan melakukan penjadwalan coroutine, digunakan untuk mendukung fungsionalitas `taskWaitMulti` di lingkungan coroutine.

```php
Swoole\Server->taskCo(array $tasks, float $timeout = 0.5): false|array
```

* `$tasks`: Daftar tugas, harus berupa array. Sistem akan melakukan iterasi melalui array, mengirimkan setiap elemen sebagai `task` ke pool proses `Task`.
* `$timeout`: Waktu timeout, default `0.5` detik. Jika tidak semua tugas selesai dalam waktu yang ditentukan, proses akan segera berhenti dan mengembalikan hasil.
* Setelah tugas selesai atau timeout, mengembalikan array hasil. Urutan setiap hasil tugas dalam array hasil sesuai dengan `$tasks`, contoh: hasil yang sesuai dengan `$tasks[2]` adalah `$result[2]`.
* Jika tugas tertentu gagal atau timeout, item yang sesuai dalam array hasil akan menjadi `false`, contoh: jika `$tasks[2]` gagal, maka nilai `$result[2]` akan menjadi `false`.

!> Jumlah maksimum tugas concurrent tidak boleh melebihi `1024`

* **Proses Penjadwalan**

    * Setiap tugas dalam daftar `$tasks` akan dikirim secara acak ke proses `Task` worker. Setelah dikirim, `yield` menangguhkan coroutine saat ini dan mengatur timer untuk `$timeout` detik.
    * Di `onFinish`, kumpulkan hasil tugas yang sesuai dan simpan ke array hasil. Periksa apakah semua tugas telah mengembalikan hasil. Jika belum, lanjutkan menunggu. Jika sudah, `resume` untuk melanjutkan eksekusi coroutine yang sesuai dan hapus timer timeout.
    * Jika tidak semua tugas selesai dalam waktu yang ditentukan, timer akan terpicu terlebih dahulu, sistem akan menghapus status waiting. Hasil tugas yang belum selesai akan ditandai sebagai `false`, dan coroutine yang sesuai akan segera dilanjutkan.

* **Contoh**

```php
$server = new Swoole\Http\Server("127.0.0.1", 9502, SWOOLE_BASE);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $serv, $task_id, $worker_id, $data) {
    echo "#{$serv->worker_id}\tonTask: worker_id={$worker_id}, task_id=$task_id\n";
    if ($serv->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "hello world";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('Test End, Result: ' . var_export($result, true));
});

$server->start();
```
## finish()

Digunakan dalam proses [Task Worker](/learn?id=taskworker-process) untuk memberi tahu proses `Worker` bahwa tugas yang dikirim telah selesai. Fungsi ini dapat mengirimkan data hasil ke proses `Worker`.

```php
Swoole\Server->finish(mixed $data): bool
```

* **Parameter**

    * `mixed $data`

        * Fungsi: Konten hasil dari pemrosesan tugas
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

* **Nilai Kembalian**

    * Mengembalikan `true` jika berhasil, `false` jika gagal

* **Tips**
    * Method `finish` dapat dipanggil beberapa kali secara berurutan, memicu event [onFinish](/server/events?id=onfinish) beberapa kali di proses `Worker`
    * Setelah memanggil method `finish` dalam fungsi callback [onTask](/server/events?id=ontask), data `return` tetap akan memicu event [onFinish](/server/events?id=onfinish)
    * `Server->finish` bersifat opsional. Jika proses `Worker` tidak perlu peduli dengan hasil eksekusi tugas, fungsi ini tidak perlu dipanggil
    * Mengembalikan string dalam fungsi callback [onTask](/server/events?id=ontask) sama dengan memanggil `finish`

* **Catatan**

    !> Saat menggunakan fungsi `Server->finish`, fungsi callback [onFinish](/server/events?id=onfinish) harus diatur untuk `Server`. Fungsi ini hanya dapat digunakan dalam proses [Task Worker](/learn?id=taskworker-process) di callback [onTask](/server/events?id=ontask).
## heartbeat()

Berbeda dengan deteksi pasif [heartbeat_check_interval](/server/setting?id=heartbeat_check_interval), method ini secara aktif memeriksa semua koneksi server dan mengidentifikasi koneksi yang telah melebihi waktu yang disepakati. Jika `if_close_connection` ditentukan, ia akan secara otomatis menutup koneksi yang timeout. Jika tidak ditentukan, ia hanya akan mengembalikan array fd koneksi.

```php
Swoole\Server->heartbeat(bool $ifCloseConnection = true): bool|array
```

* **Parameter**

    * `bool $ifCloseConnection`

        * Fungsi: Apakah akan menutup koneksi yang timeout
        * Nilai Default: true
        * Nilai Lain: false

* **Nilai Kembalian**

    * Jika berhasil, akan mengembalikan array kontinu yang berisi `$fd` yang ditutup
    * Jika tidak berhasil, akan mengembalikan `false`

* **Contoh**

```php
$closeFdArrary = $server->heartbeat();
```
## getLastError()

Mendapatkan kode error dari kesalahan operasi terbaru. Dalam kode bisnis, logika yang berbeda dapat dijalankan berdasarkan tipe kode error.

```php
Swoole\Server->getLastError(): int
```

* **Nilai Kembalian**

Kode Error | Penjelasan
---|---
1001 | Koneksi telah ditutup oleh `Server`. Error ini biasanya terjadi ketika kode sudah menjalankan `$server->close()` untuk menutup koneksi tetapi masih memanggil `$server->send()` untuk mengirim data ke koneksi tersebut.
1002 | Koneksi telah ditutup oleh `Client`, dan `Socket` ditutup, tidak dapat mengirim data ke lawan bicara.
1003 | Sedang dalam proses penutupan, `send()` tidak dapat digunakan dalam fungsi callback [onClose](/server/events?id=onclose).
1004 | Koneksi telah ditutup.
1005 | Koneksi tidak ada; `$fd` yang diberikan mungkin salah.
1007 | Menerima data yang timeout. Setelah `TCP` menutup koneksi, beberapa data mungkin tersisa di buffer [unixSocket](/learn?id=apa-itu-ipc), dan data tersebut akan dibuang.
1008 | Buffer pengiriman penuh dan tidak dapat melakukan operasi `send`. Error ini menunjukkan bahwa lawan bicara dari koneksi ini tidak dapat menerima data dengan cepat, menyebabkan buffer pengiriman penuh.
1202 | Data yang dikirim melebihi pengaturan [server->buffer_output_size](/server/setting?id=buffer_output_size).
9007 | Hanya terjadi saat menggunakan [dispatch_mode](/server/setting?id=dispatch_mode)=3, menunjukkan bahwa saat ini tidak ada proses yang tersedia. Anda dapat meningkatkan jumlah proses `worker_num`.
## getSocket()

Memanggil method ini dapat memperoleh handle `socket` yang mendasarinya, dan objek yang dikembalikan adalah resource handle `sockets`.

```php
Swoole\Server->getSocket(): false|\Socket
```

!> Method ini memerlukan ekstensi `sockets` PHP dan opsi `--enable-sockets` saat mengkompilasi `Swoole`.

* **Port Listening**

    * Port yang ditambahkan menggunakan method `listen` dapat menggunakan method `getSocket` yang disediakan oleh objek `Swoole\Server\Port`.

    ```php
    $port = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $socket = $port->getSocket();
    ```

    * Menggunakan fungsi `socket_set_option` dapat mengatur beberapa parameter `socket` level rendah.

    ```php
    $socket = $server->getSocket();
    if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
        echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
    }
    ```

* **Dukungan Multicast**

    * Menggunakan `socket_set_option` untuk mengatur parameter `MCAST_JOIN_GROUP` dapat menggabungkan `Socket` ke multicast dan mendengarkan paket jaringan multicast.

```php
$server = new Swoole\Server('0.0.0.0', 9905, SWOOLE_BASE, SWOOLE_SOCK_UDP);
$server->set(['worker_num' => 1]);
$socket = $server->getSocket();

$ret = socket_set_option(
    $socket,
    IPPROTO_IP,
    MCAST_JOIN_GROUP,
    array(
        'group' => '224.10.20.30', // Menunjukkan alamat multicast
        'interface' => 'eth0' // Menunjukkan nama interface jaringan, bisa berupa angka atau string, seperti eth0, wlan0
    )
);

if ($ret === false) {
    throw new RuntimeException('Unable to join multicast group');
}

$server->on('Packet', function (Swoole\Server $server, $data, $addr) {
    $server->sendto($addr['address'], $addr['port'], "Swoole: $data");
    var_dump($addr, strlen($data));
});

$server->start();
```
## protect()

Mengatur koneksi klien ke status terlindungi, agar tidak diputus oleh thread heartbeat.

```php
Swoole\Server->protect(int $fd, bool $is_protected = true): bool
```

* **Parameter**

    * `int $fd`

        * Fungsi: menentukan `fd` koneksi klien
        * Nilai Default: tidak ada
        * Nilai Lain: tidak ada

    * `bool $is_protected`

        * Fungsi: status yang akan diatur
        * Nilai Default: true (menunjukkan status terlindungi)
        * Nilai Lain: false (menunjukkan status tidak terlindungi)

* **Nilai Kembalian**

    * Mengembalikan `true` menunjukkan operasi berhasil, mengembalikan `false` menunjukkan operasi gagal.
## confirm()

Konfirmasi koneksi, digunakan bersama dengan [enable_delay_receive](/server/setting?id=enable_delay_receive). Ketika klien membuat koneksi, ia tidak mendengarkan event yang dapat dibaca, hanya memicu callback event [onConnect](/server/events?id=onconnect). Di callback [onConnect](/server/events?id=onconnect), jalankan `confirm` untuk mengonfirmasi koneksi. Pada saat itu, server akan mulai mendengarkan event yang dapat dibaca untuk menerima data dari klien yang terhubung.

!> Tersedia untuk Swoole versi >= `v4.5.0`

```php
Swoole\Server->confirm(int $fd): bool
```

* **Parameter**

    * `int $fd`

        * Fungsi: Pengidentifikasi unik koneksi
        * Nilai Default: Tidak ada
        * Lainnya: Tidak ada

* **Nilai Kembalian**

    * Mengembalikan `true` jika konfirmasi berhasil
    * Mengembalikan `false` jika koneksi yang sesuai dengan `$fd` tidak ada, ditutup, atau sudah dalam status mendengarkan; menunjukkan kegagalan konfirmasi

* **Tujuan**

    Method ini umumnya digunakan untuk melindungi server dari serangan lalu lintas berlebih. Ketika koneksi klien diterima, fungsi [onConnect](/server/events?id=onconnect) dipicu. Ini dapat digunakan untuk memeriksa `IP` sumber dan memutuskan apakah akan mengizinkan pengiriman data ke server.

* **Contoh**

```php
// Membuat objek Server, mendengarkan di 127.0.0.1:9501
$serv = new Swoole\Server("127.0.0.1", 9501); 
$serv->set([
    'enable_delay_receive' => true,
]);

// Mendengarkan event koneksi
$serv->on('Connect', function ($serv, $fd) {  
    // Periksa $fd di sini dan konfirmasi jika ok
    $serv->confirm($fd);
});

// Mendengarkan event penerimaan data
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "Server: " . $data);
});

// Mendengarkan event penutupan koneksi
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Memulai server
$serv->start(); 
```
## getWorkerId()

Mendapatkan `id` dari proses `Worker` saat ini (bukan `PID` proses), sesuai dengan `$workerId` di [onWorkerStart](/server/events?id=onworkerstart).

```php
Swoole\Server->getWorkerId(): int|false
```

!> Tersedia untuk Swoole versi >= `v4.5.0RC1`
## getWorkerPid()

Mendapatkan `PID` dari proses `Worker` yang ditentukan

```php
Swoole\Server->getWorkerPid(int $worker_id = -1): int|false
```

* **Parameter**

    * `int $worker_id`

        * Fungsi: mendapatkan `pid` dari proses yang ditentukan
        * Nilai Default: -1 (mewakili proses saat ini)
        * Nilai Lain: N/A

!> Tersedia sejak Swoole versi >= `v4.5.0RC1`
## getWorkerStatus()

Mendapatkan status proses `Worker`

```php
Swoole\Server->getWorkerStatus(int $worker_id = -1): int|false
```

!> Tersedia di Swoole versi >= `v4.5.0RC1`

* **Parameter**

    * `int $worker_id`

        * Fungsi: Mendapatkan status proses
        * Nilai Default: -1, [-1 mewakili proses saat ini]
        * Nilai Lain: Tidak ada

* **Nilai Kembalian**

    * Mengembalikan status proses `Worker`, lihat nilai status proses
    * Mengembalikan `false` jika bukan proses `Worker` atau proses tidak ada

* **Nilai Status Proses**

    Konstanta | Nilai | Deskripsi | Ketergantungan Versi
    ---|---|---|---
    SWOOLE_WORKER_BUSY | 1 | Sibuk | v4.5.0RC1
    SWOOLE_WORKER_IDLE | 2 | Diam | v4.5.0RC1
    SWOOLE_WORKER_EXIT | 3 | Dalam kasus di mana [reload_async](/server/setting?id=reload_async) diaktifkan, mungkin ada 2 proses untuk worker_id yang sama, satu baru dan satu lama. Proses lama akan membaca kode status EXIT. | v4.5.5
## getManagerPid()

Mendapatkan `PID` dari proses `Manager` untuk layanan saat ini.

```php
Swoole\Server->getManagerPid(): int
```

!> Tersedia di Swoole versi `v4.5.0RC1` atau lebih tinggi
## getMasterPid()

Mendapatkan `PID` dari proses `Master` layanan saat ini.

```php
Swoole\Server->getMasterPid(): int
```

!> Tersedia di Swoole versi >= `v4.5.0RC1`
## addCommand()

Menambahkan perintah kustom `command`

```php
Swoole\Server->addCommand(string $name, int $accepted_process_types, Callable $callback): bool
```

!> -Tersedia di Swoole versi >= `v4.8.0`  
  -Fungsi ini hanya dapat dipanggil sebelum layanan dimulai. Jika sudah ada perintah dengan nama yang sama, akan mengembalikan `false` langsung.

* **Parameter**

    * `string $name`

        * Fungsi: Nama `command`
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

    * `int $accepted_process_types`

        * Fungsi: Tipe proses yang menerima permintaan. Jika Anda ingin mendukung beberapa tipe proses, Anda dapat menghubungkannya dengan `|`, contoh, `SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_MANAGER`
        * Nilai Default: Tidak ada
        * Nilai Lain:
            * `SWOOLE_SERVER_COMMAND_MASTER` proses master
            * `SWOOLE_SERVER_COMMAND_MANAGER` proses manager
            * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` process worker
            * `SWOOLE_SERVER_COMMAND_TASK_WORKER` proses task

    * `callable $callback`

        * Fungsi: Fungsi callback. Memiliki dua parameter, satu adalah kelas `Swoole\Server`, dan yang lainnya adalah variabel yang ditentukan pengguna. Variabel ini dilewatkan melalui parameter keempat dari `Swoole\Server::command()`.
        * Nilai Default: Tidak ada
        * Nilai Lain: Tidak ada

* **Nilai Kembalian**

    * Mengembalikan `true` menunjukkan penambahan perintah kustom berhasil, mengembalikan `false` menunjukkan kegagalan
## command()

Memanggil perintah kustom `command` yang telah ditentukan

```php
Swoole\Server->command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true): false|string|array
```

!> Tersedia di Swoole versi >= `v4.8.0`. Dalam mode `SWOOLE_PROCESS` dan `SWOOLE_BASE`, fungsi ini hanya dapat digunakan di proses `master`.

* **Parameter**

    * `string $name`

        * Deskripsi: Nama `command`
        * Nilai Default: Tidak ada
        * Lainnya: Tidak ada

    * `int $process_id`

        * Deskripsi: ID Proses
        * Nilai Default: Tidak ada
        * Lainnya: Tidak ada

    * `int $process_type`

        * Deskripsi: Tipe permintaan proses, hanya salah satu dari nilai berikut yang dapat dipilih.
        * Nilai Default: Tidak ada
        * Nilai Lain:
            * `SWOOLE_SERVER_COMMAND_MASTER` proses master
            * `SWOOLE_SERVER_COMMAND_MANAGER` proses manager
            * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` process worker
            * `SWOOLE_SERVER_COMMAND_TASK_WORKER` proses task

    * `mixed $data`

        * Deskripsi: Data permintaan, data ini harus dapat diserialisasi
        * Nilai Default: Tidak ada
        * Lainnya: Tidak ada

    * `bool $json_decode`

        * Deskripsi: Apakah akan mendekode menggunakan `json_decode`
        * Nilai Default: true
        * Nilai Lain: false

* **Contoh Penggunaan**
    ```php
    <?php
    use Swoole\Http\Server;
    use Swoole\Http\Request;
    use Swoole\Http\Response;

    $server = new Server('127.0.0.1', 9501, SWOOLE_BASE);
    $server->addCommand('test_getpid', SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_EVENT_WORKER,
        function ($server, $data) {
          var_dump($data);
          return json_encode(['pid' => posix_getpid()]);
        });
    $server->set([
        'log_file' => '/dev/null',
        'worker_num' => 2,
    ]);

    $server->on('start', function (Server $serv) {
        $result = $serv->command('test_getpid', 0, SWOOLE_SERVER_COMMAND_MASTER, ['type' => 'master']);
        Assert::eq($result['pid'], $serv->getMasterPid());
        $result = $serv->command('test_getpid', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::eq($result['pid'], $serv->getWorkerPid(1));
        $result = $serv->command('test_not_found', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::false($result);

        $serv->shutdown();
    });

    $server->on('request', function (Request $request, Response $response) {
    });
    $server->start();
    ```
