# Event

Bagian ini akan memperkenalkan semua fungsi callback Swoole. Setiap fungsi callback adalah fungsi PHP, yang sesuai dengan sebuah event.

## onStart

?> **Dipanggil setelah startup di thread utama proses master (master)**

```php
function onStart(Swoole\Server $server);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Sebelum event ini `Server` telah melakukan operasi berikut:**

    * Membuat dan menyelesaikan [Manager process](/learn?id=manager-process)
    * Membuat dan menyelesaikan [Worker child process](/learn?id=worker-process)
    * Mendengarkan semua port TCP/UDP/[unixSocket](/learn?id=apa-itu-ipc), tetapi belum mulai Menerima koneksi dan permintaan
    * Mendengarkan timer

* **Selanjutnya yang akan dilakukan:**

    * [Reactor](/learn?id=reactor-thread) utama mulai menerima event, klien dapat `connect` ke `Server`

**Di callback `onStart`, hanya diperbolehkan `echo`, mencetak `Log`, mengubah nama proses. Tidak boleh melakukan operasi lain (tidak boleh memanggil fungsi terkait `server`, dll., karena layanan belum siap). `onWorkerStart` dan `onStart` dieksekusi secara paralel di proses yang berbeda, tidak ada urutan.**

Anda dapat menyimpan nilai `$server->master_pid` dan `$server->manager_pid` ke dalam file di callback `onStart`. Dengan begitu, Anda dapat menulis skrip untuk mengirim sinyal ke dua `PID` ini untuk melakukan operasi shutdown dan restart.

Event `onStart` dipanggil di thread utama proses `Master`.

!> Objek resource global yang dibuat di `onStart` tidak dapat digunakan di proses `Worker`, karena saat panggilan `onStart` terjadi, proses `worker` sudah dibuat  
Objek yang baru dibuat berada di proses utama, proses `Worker` tidak dapat mengakses area memori ini  
Oleh karena itu, kode pembuatan objek global harus ditempatkan sebelum `Server::start`, contoh tipikal adalah [Swoole\Table](/memory/table?id=contoh-lengkap)

* **Peringatan Keamanan**

Di callback `onStart`, API asynchronous dan coroutine dapat digunakan, tetapi perlu diperhatikan bahwa ini mungkin bertentangan dengan `dispatch_func` dan `package_length_func`, **jangan gunakan bersamaan**.

Jangan memulai timer di `onStart`, jika kode menjalankan `Swoole\Server::shutdown()`, karena selalu ada timer yang berjalan, program tidak akan bisa keluar.

Callback `onStart` sebelum `return`, program server tidak akan menerima koneksi klien mana pun, sehingga fungsi blocking synchronous dapat digunakan dengan aman.

* **Mode BASE**

Dalam mode [SWOOLE_BASE](/learn?id=swoole_base) tidak ada proses `master`, sehingga event `onStart` tidak ada, jangan gunakan callback `onStart` dalam mode `BASE`.

```
WARNING swReactorProcess_start: The onStart event with SWOOLE_BASE is deprecated
```

## onBeforeShutdown

?> **Event ini terjadi sebelum `Server` berakhir secara normal**

!> Tersedia di Swoole versi >= `v4.8.0`. API coroutine dapat digunakan dalam event ini.

```php
function onBeforeShutdown(Swoole\Server $server);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

## onShutdown

?> **Event ini terjadi saat `Server` berakhir secara normal**

```php
function onShutdown(Swoole\Server $server);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Sebelum ini `Swoole\Server` telah melakukan operasi berikut:**

    * Telah menutup semua thread [Reactor](/learn?id=reactor-thread), thread `HeartbeatCheck`, thread `UdpRecv`
    * Telah menutup semua proses `Worker`, [Task process](/learn?id=taskworker-process), [User process](/server/methods?id=addprocess)
    * Telah `close` semua port listening `TCP/UDP/UnixSocket`
    * Telah menutup [Reactor](/learn?id=reactor-thread) utama

    !> Memaksa `kill` proses tidak akan memanggil callback `onShutdown`, seperti `kill -9`  
    Perlu menggunakan `kill -15` untuk mengirim sinyal `SIGTERM` ke proses utama agar dapat menghentikan sesuai alur normal  
    Menggunakan `Ctrl+C` di command line untuk menginterupsi program akan segera berhenti, dan level bawah tidak akan memanggil callback `onShutdown`

* **Hal yang Perlu Diperhatikan**

    !> Jangan memanggil API asynchronous atau coroutine apa pun di `onShutdown`, saat `onShutdown` dipicu, level bawah telah menghancurkan semua fasilitas event loop;  
    Saat itu lingkungan coroutine sudah tidak ada, jika pengembang perlu menggunakan API terkait coroutine, harus memanggil `Co\run` secara manual untuk membuat [coroutine container](/coroutine?id=apa-itu-coroutine-container).

## onWorkerStart

?> **Event ini terjadi saat proses Worker / [Task process](/learn?id=taskworker-process) dimulai. Objek yang dibuat di sini dapat digunakan dalam siklus hidup proses.**

```php
function onWorkerStart(Swoole\Server $server, int $workerId);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $workerId`**
        * **Fungsi**: `id` proses `Worker` (bukan PID proses)
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* `onWorkerStart/onStart` dieksekusi secara concurrent, tidak ada urutan
* Dapat menggunakan properti `$server->taskworker` untuk menentukan apakah saat ini proses `Worker` atau [Task process](/learn?id=taskworker-process)
* Jika `worker_num` dan `task_worker_num` diatur lebih dari `1`, setiap proses akan memicu event `onWorkerStart` sekali, dapat dibedakan dengan [$worker_id](/server/properties?id=worker_id)
* Proses `worker` mengirim tugas ke proses `task`, setelah proses `task` menyelesaikan semua tugas, proses `worker` diberitahu melalui fungsi callback [onFinish](/server/events?id=onfinish). Misalnya, di backend mengirim email pemberitahuan massal ke seratus ribu pengguna, setelah operasi selesai, status operasi ditampilkan sebagai "sedang dikirim", saat ini dapat melanjutkan operasi lain, dan setelah email selesai dikirim, status operasi secara otomatis berubah menjadi "terkirim".

Contoh berikut digunakan untuk mengganti nama proses Worker / [Task process](/learn?id=taskworker-process).

```php
$server->on('WorkerStart', function ($server, $worker_id){
    global $argv;
    if($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});
```

Jika ingin menggunakan mekanisme [Reload](/server/methods?id=reload) untuk memuat ulang kode, harus `require` file bisnis Anda di `onWorkerStart`, bukan di awal file. File yang sudah disertakan sebelum `onWorkerStart` dipanggil, tidak akan dimuat ulang.

File php yang umum dan tidak sering berubah dapat ditempatkan sebelum `onWorkerStart`. Meskipun tidak dapat dimuat ulang, file tersebut dibagi oleh semua `Worker`, tanpa perlu memori tambahan untuk menyimpan data ini.
Kode setelah `onWorkerStart` perlu disimpan di memori oleh setiap proses.

* `$worker_id` menunjukkan `ID` proses `Worker` ini, rentang referensi [$worker_id](/server/properties?id=worker_id)
* [$worker_id](/server/properties?id=worker_id) tidak ada hubungannya dengan `PID` proses, dapat menggunakan fungsi `posix_getpid` untuk mendapatkan `PID`

* **Dukungan Coroutine**

    * Di fungsi callback `onWorkerStart`, coroutine akan dibuat secara otomatis, sehingga `onWorkerStart` dapat memanggil API coroutine

* **Catatan**

    !> Jika terjadi fatal error atau panggilan `exit` secara aktif dalam kode, proses `Worker/Task` akan keluar, proses manajemen akan membuat proses baru. Ini dapat menyebabkan infinite loop, terus-menerus membuat dan menghancurkan proses.

## onWorkerStop

?> **Event ini terjadi saat proses `Worker` dihentikan. Di fungsi ini, berbagai resource yang diminta oleh proses `Worker` dapat dibebaskan.**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $workerId`**
        * **Fungsi**: `id` proses `Worker` (bukan PID proses)
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Catatan**

    !> -Proses berakhir tidak normal, seperti dipaksa `kill`, fatal error, `core dump`, tidak dapat menjalankan callback `onWorkerStop`.  
    -Jangan memanggil API asynchronous atau coroutine apa pun di `onWorkerStop`, saat `onWorkerStop` dipicu, level bawah telah menghancurkan semua fasilitas [event loop](/learn?id=apa-itu-eventloop).

## onWorkerExit

?> **Hanya efektif setelah mengaktifkan fitur [reload_async](/server/setting?id=reload_async). Lihat [Cara me-restart service dengan benar](/question/use?id=cara-me-restart-service-swoole-dengan-benar)**

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $workerId`**
        * **Fungsi**: `id` proses `Worker` (bukan PID proses)
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Catatan**

    !> -Jika proses `Worker` tidak keluar, `onWorkerExit` akan terus terpicu  
    -`onWorkerExit` akan terpicu di dalam proses `Worker`, jika di [Task process](/learn?id=taskworker-process) terdapat [event loop](/learn?id=apa-itu-eventloop), juga akan terpicu  
    -Di `onWorkerExit`, sebisa mungkin hapus/tutup koneksi `Socket` asynchronous, akhirnya level bawah mendeteksi jumlah handle event listener di [event loop](/learn?id=apa-itu-eventloop) adalah `0`, maka proses akan keluar  
    -Jika proses tidak memiliki event handle yang sedang didengarkan, saat proses berakhir, fungsi ini tidak akan dipanggil  
    -Menunggu proses `Worker` keluar baru kemudian menjalankan callback event `onWorkerStop`

## onConnect

?> **Saat ada koneksi baru masuk, dipanggil di proses worker.**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $fd`**
        * **Fungsi**: File descriptor koneksi
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $reactorId`**
        * **Fungsi**: `ID` thread [Reactor](/learn?id=reactor-thread) tempat koneksi berada
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Catatan**

    !> `onConnect/onClose` adalah `2` callback yang terjadi di dalam proses `Worker`, bukan di proses utama.  
    Protokol `UDP` hanya memiliki event [onReceive](/server/events?id=onreceive), tidak ada event `onConnect/onClose`.

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

        * Dalam mode ini, `onConnect/onReceive/onClose` dapat dikirim ke proses yang berbeda. Data objek `PHP` terkait koneksi tidak dapat diinisialisasi di callback [onConnect](/server/events?id=onconnect), dan dibersihkan di [onClose](/server/events?id=onclose).
        * `3` jenis event `onConnect/onReceive/onClose` dapat dieksekusi secara concurrent, yang dapat menyebabkan anomali.

## onReceive

?> **Saat menerima data, fungsi ini dipanggil, terjadi di proses `worker`.**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $fd`**
        * **Fungsi**: File descriptor koneksi
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $reactorId`**
        * **Fungsi**: `ID` thread [Reactor](/learn?id=reactor-thread) tempat koneksi `TCP` berada
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`string $data`**
        * **Fungsi**: Isi data yang diterima, bisa berupa teks atau konten biner
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Tentang integritas paket dalam protokol `TCP`, lihat [TCP Data Packet Boundary Issue](/learn?id=masalah-batas-paket-data-tcp)**

    * Menggunakan konfigurasi `open_eof_check/open_length_check/open_http_protocol` yang disediakan level bawah dapat menjamin integritas paket data.
    * Jika tidak menggunakan pemrosesan protokol level bawah, lakukan analisis data sendiri dalam kode PHP setelah [onReceive](/server/events?id=onreceive), gabung/pisah paket data.

    Contoh: dapat menambahkan `$buffer = array()` dalam kode, menggunakan `$fd` sebagai `key`, untuk menyimpan data konteks. Setiap kali menerima data, lakukan penggabungan string, `$buffer[$fd] .= $data`, kemudian periksa apakah string `$buffer[$fd]` merupakan paket data yang lengkap.

    Secara default, `fd` yang sama akan dialokasikan ke `Worker` yang sama, sehingga data dapat digabungkan. Saat menggunakan [dispatch_mode](/server/setting?id=dispatch_mode) = 3, data permintaan bersifat preemptive, data dari `fd` yang sama dapat dibagi ke proses yang berbeda, sehingga metode penggabungan data di atas tidak dapat digunakan.

* **Listening multi-port, lihat [bagian ini](/server/port)**

    Setelah server utama mengatur protokol, port tambahan yang didengarkan secara default akan mewarisi pengaturan server utama. Perlu secara eksplisit memanggil method `set` untuk mengatur ulang protokol port.

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9501);
    $port2 = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $port2->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        echo "[#".$server->worker_id."]\tClient[$fd]: $data\n";
    });
    ```

    Meskipun di sini memanggil method `on` untuk mendaftarkan callback [onReceive](/server/events?id=onreceive), tetapi karena tidak memanggil method `set` untuk menimpa protokol server utama, port `9502` yang baru tetap menggunakan protokol `HTTP`. Menggunakan klien `telnet` untuk terhubung ke port `9502` dan mengirim string tidak akan memicu [onReceive](/server/events?id=onreceive) di server.

* **Catatan**

    !> Tanpa mengaktifkan opsi protokol otomatis, [onReceive](/server/events?id=onreceive) satu kali menerima data maksimal `64K`  
    Dengan mengaktifkan opsi pemrosesan protokol otomatis, [onReceive](/server/events?id=onreceive) akan menerima paket data lengkap, maksimal tidak melebihi [package_max_length](/server/setting?id=package_max_length)  
    Mendukung format biner, `$data` bisa berupa data biner.

## onPacket

?> **Saat menerima paket data `UDP`, fungsi ini dipanggil, terjadi di proses `worker`.**

```php
function onPacket(Swoole\Server $server, string $data, array $clientInfo);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`string $data`**
        * **Fungsi**: Isi data yang diterima, bisa berupa teks atau konten biner
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`array $clientInfo`**
        * **Fungsi**: Informasi klien mencakup `address/port/server_socket` dan berbagai data informasi klien lainnya, [lihat Server UDP](/start/start_udp_server)
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Catatan**

    !> Saat server mendengarkan port `TCP/UDP` secara bersamaan, data protokol `TCP` yang diterima akan memanggil callback [onReceive](/server/events?id=onreceive), dan paket data `UDP` yang diterima akan memanggil callback `onPacket`. Pemrosesan protokol otomatis seperti `EOF` atau `Length` yang diatur server ([lihat Masalah Batas Paket Data TCP](/learn?id=masalah-batas-paket-data-tcp)), tidak berlaku untuk port `UDP`, karena paket `UDP` sendiri memiliki batas pesan, tidak memerlukan pemrosesan protokol tambahan.

## onClose

?> **Setelah koneksi klien `TCP` ditutup, fungsi ini dipanggil di proses `Worker`.**

```php
function onClose(Swoole\Server $server, int $fd, int $reactorId);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $fd`**
        * **Fungsi**: File descriptor koneksi
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $reactorId`**
        * **Fungsi**: Berasal dari thread `reactor` mana, bernilai negatif saat `close` dilakukan secara aktif
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Tips**

    * **Penutupan Aktif**

        * Saat server secara aktif menutup koneksi, level bawah akan mengatur parameter ini menjadi `-1`, dapat dibedakan dengan memeriksa `$reactorId < 0` apakah penutupan dilakukan oleh server atau klien.
        * Hanya jika secara aktif memanggil method `close` dalam kode `PHP` dianggap sebagai penutupan aktif

    * **Deteksi Heartbeat**

        * [Deteksi Heartbeat](/server/setting?id=heartbeat_check_interval) adalah pemberitahuan penutupan dari thread deteksi heartbeat, saat penutupan, parameter `$reactorId` dari [onClose](/server/events?id=onclose) bukan `-1`

* **Catatan**

    !> -Jika terjadi fatal error di fungsi callback [onClose](/server/events?id=onclose), dapat menyebabkan kebocoran koneksi. Melalui perintah `netstat` akan terlihat banyak koneksi `TCP` dengan status `CLOSE_WAIT`.  
    -Baik klien yang memulai `close` maupun server yang secara aktif memanggil `$server->close()` untuk menutup koneksi, akan memicu event ini. Oleh karena itu, selama koneksi ditutup, pasti akan memanggil fungsi ini.  
    -Di [onClose](/server/events?id=onclose) masih dapat memanggil method [getClientInfo](/server/methods?id=getClientInfo) untuk mendapatkan informasi koneksi. Setelah eksekusi callback [onClose](/server/events?id=onclose) selesai, baru akan memanggil `close` untuk menutup koneksi `TCP`.  
    -Saat callback [onClose](/server/events?id=onclose) di sini dipanggil, itu berarti koneksi klien sudah ditutup, jadi tidak perlu menjalankan `$server->close($fd)`. Menjalankan `$server->close($fd)` dalam kode akan melemparkan peringatan error `PHP`.

## onTask

?> **Dipanggil di dalam proses `task`. Proses `worker` dapat menggunakan fungsi [task](/server/methods?id=task) untuk mengirim tugas baru ke proses `task_worker`. Proses [Task](/learn?id=taskworker-process) saat ini saat memanggil fungsi callback [onTask](/server/events?id=ontask) akan mengubah status proses menjadi sibuk, dan tidak akan menerima Task baru lagi. Saat fungsi [onTask](/server/events?id=ontask) kembali, status proses akan berubah kembali menjadi idle dan terus menerima `Task` baru.**

```php
function onTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $task_id`**
        * **Fungsi**: `id` proses `task` yang menjalankan tugas【`$task_id` dan `$src_worker_id` digabungkan baru bersifat unik secara global, `ID` tugas yang dikirim oleh proses `worker` yang berbeda mungkin sama】
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $src_worker_id`**
        * **Fungsi**: `id` proses `worker` yang mengirim tugas【`$task_id` dan `$src_worker_id` digabungkan baru bersifat unik secara global, `ID` tugas yang dikirim oleh proses `worker` yang berbeda mungkin sama】
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`mixed $data`**
        * **Fungsi**: Konten data tugas
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Tips**

    * **Mulai v4.2.12, jika [task_enable_coroutine](/server/setting?id=task_enable_coroutine) diaktifkan, prototipe fungsi callback adalah**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); //menyelesaikan tugas, selesai dan mengembalikan data
      });
      ```

    * **Mengembalikan hasil eksekusi ke proses `worker`**

        * **Dalam fungsi [onTask](/server/events?id=ontask), `return` string berarti mengembalikan konten ini ke proses `worker`. Proses `worker` akan memicu fungsi [onFinish](/server/events?id=onfinish), menandakan bahwa `task` yang dikirim telah selesai. Tentu saja, Anda juga dapat memicu fungsi [onFinish](/server/events?id=onfinish) melalui `Swoole\Server->finish()`, tanpa perlu `return`**

        * Variabel `return` bisa berupa variabel `PHP` apa pun yang bukan `null`

* **Catatan**

    !> Saat fungsi [onTask](/server/events?id=ontask) dieksekusi dan mengalami fatal error, atau dipaksa `kill` oleh proses eksternal, tugas saat ini akan dibuang, tetapi tidak akan memengaruhi `Task` lain yang sedang mengantri.

## onFinish

?> **Fungsi callback ini dipanggil di proses worker. Saat tugas yang dikirim oleh proses `worker` selesai di proses `task`, [task process](/learn?id=taskworker-process) akan mengirimkan hasil pemrosesan tugas ke proses `worker` melalui method `Swoole\Server->finish()`.**

```php
function onFinish(Swoole\Server $server, int $task_id, mixed $data)
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $task_id`**
        * **Fungsi**: `id` proses `task` yang menjalankan tugas
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`mixed $data`**
        * **Fungsi**: Konten hasil pemrosesan tugas
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Catatan**

    !> -Jika event [onTask](/server/events?id=ontask) dari [task process](/learn?id=taskworker-process) tidak memanggil method `finish` atau `return` hasil, maka proses `worker` tidak akan memicu [onFinish](/server/events?id=onfinish)  
    -Proses `worker` yang menjalankan logika [onFinish](/server/events?id=onfinish) adalah proses yang sama dengan proses `worker` yang mengirim tugas `task`.

## onPipeMessage

?> **Saat proses kerja menerima pesan [unixSocket](/learn?id=apa-itu-ipc) yang dikirim oleh `$server->sendMessage()`, event `onPipeMessage` akan terpicu. Proses `worker/task` mungkin memicu event `onPipeMessage`.**

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $src_worker_id`**
        * **Fungsi**: Dari proses `Worker` mana pesan berasal
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`mixed $message`**
        * **Fungsi**: Konten pesan, bisa berupa tipe PHP apa pun
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

## onWorkerError

?> **Saat terjadi error pada proses `Worker/Task`, fungsi ini akan dipanggil di dalam proses `Manager`.**

!> Fungsi ini terutama digunakan untuk alarm dan monitoring. Begitu menemukan proses Worker keluar secara abnormal, kemungkinan besar menemui fatal error atau proses Core Dump. Dengan mencatat log atau mengirim informasi alarm untuk memberi tahu pengembang agar melakukan penanganan yang sesuai.

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $worker_id`**
        * **Fungsi**: `id` proses `worker` yang mengalami error
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $worker_pid`**
        * **Fungsi**: `pid` proses `worker` yang mengalami error
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $exit_code`**
        * **Fungsi**: Kode status keluar, rentang `0～255`
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`int $signal`**
        * **Fungsi**: Sinyal keluar proses
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Kesalahan Umum**

    * `signal = 11`: Menunjukkan proses `Worker` mengalami `segment fault`, mungkin memicu `BUG` level bawah. Harap kumpulkan informasi `core dump` dan log deteksi memori `valgrind`, [laporkan masalah ini ke tim pengembang Swoole](/other/issue)
    * `exit_code = 255`: Menunjukkan proses Worker mengalami `Fatal Error`. Harap periksa log error PHP, temukan kode PHP yang bermasalah, dan selesaikan.
    * `signal = 9`: Menunjukkan `Worker` dipaksa `Kill` oleh sistem. Harap periksa apakah ada operasi `kill -9` yang dilakukan, periksa informasi `dmesg` apakah ada `OOM (Out of memory)`.
    * Jika ada `OOM`, alokasi memori terlalu besar. 1. Periksa konfigurasi `setting` `Server`, apakah [socket_buffer_size](/server/setting?id=socket_buffer_size) dll. dialokasikan terlalu besar; 2. Apakah membuat modul memori [Swoole\Table](/memory/table) yang sangat besar.

## onManagerStart

?> **Saat proses manajemen dimulai, event ini terpicu.**

```php
function onManagerStart(Swoole\Server $server);
```

* **Tips**

    * Di fungsi callback ini, nama proses manajemen dapat diubah.
    * Di versi sebelum `4.2.12`, proses `manager` tidak dapat menambahkan timer, tidak dapat mengirim task, tidak dapat menggunakan coroutine.
    * Di versi `4.2.12` atau yang lebih baru, proses `manager` dapat menggunakan timer mode synchronous berbasis sinyal.
    * Di proses `manager` dapat memanggil antarmuka [sendMessage](/server/methods?id=sendMessage) untuk mengirim pesan ke proses kerja lain.

    * **Urutan Startup**

        * Proses `Task` dan `Worker` sudah dibuat
        * Status proses `Master` tidak jelas, karena `Manager` dan `Master` berjalan paralel, saat callback `onManagerStart` terjadi, tidak dapat memastikan apakah proses `Master` sudah siap.

    * **Mode BASE**

        * Dalam mode [SWOOLE_BASE](/learn?id=swoole_base), jika parameter `worker_num`, `max_request`, `task_worker_num` diatur, level bawah akan membuat proses `manager` untuk mengelola proses kerja. Oleh karena itu, callback event `onManagerStart` dan `onManagerStop` akan terpicu.

## onManagerStop

?> **Saat proses manajemen berakhir, event ini terpicu.**

```php
function onManagerStop(Swoole\Server $server);
```

* **Tips**

    * Saat `onManagerStop` terpicu, itu berarti proses `Task` dan `Worker` telah selesai berjalan dan telah dibebaskan oleh proses `Manager`.

## onBeforeReload

?> **Sebelum proses Worker di `Reload`, event ini terpicu, dipanggil di proses Manager.**

```php
function onBeforeReload(Swoole\Server $server);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

## onAfterReload

?> **Setelah proses Worker di `Reload`, event ini terpicu, dipanggil di proses Manager.**

```php
function onAfterReload(Swoole\Server $server);
```

* **Parameter**

    * **`Swoole\Server $server`**
        * **Fungsi**: Objek Swoole\Server
        * **Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

## Urutan Eksekusi Event

* Semua callback event terjadi setelah `$server->start`
* Saat server ditutup dan program berakhir, event terakhir adalah `onShutdown`
* Setelah server berhasil dimulai, `onStart/onManagerStart/onWorkerStart` akan dieksekusi secara concurrent di proses yang berbeda
* `onReceive/onConnect/onClose` terpicu di proses `Worker`
* Saat proses `Worker/Task` dimulai/berakhir, `onWorkerStart/onWorkerStop` akan dipanggil masing-masing satu kali
* Event [onTask](/server/events?id=ontask) hanya terjadi di [task process](/learn?id=taskworker-process)
* Event [onFinish](/server/events?id=onfinish) hanya terjadi di proses `worker`
* Urutan eksekusi `onStart/onManagerStart/onWorkerStart` `3` event tidak dapat ditentukan

## Gaya Berorientasi Objek

Setelah mengaktifkan [event_object](/server/setting?id=event_object), parameter callback event berikut akan berubah.

* Koneksi klien [onConnect](/server/events?id=onconnect)
```php
$server->on('Connect', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* Menerima data [onReceive](/server/events?id=onreceive)
```php
$server->on('Receive', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* Koneksi ditutup [onClose](/server/events?id=onclose)
```php
$server->on('Close', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* UDP menerima paket [onPacket](/server/events?id=onpacket)
```php
$server->on('Packet', function (Swoole\Server $serv, Swoole\Server\Packet $object) {
    var_dump($object);
});
```

* Komunikasi antar proses [onPipeMessage](/server/events?id=onpipemessage)
```php
$server->on('PipeMessage', function (Swoole\Server $serv, Swoole\Server\PipeMessage $msg) {
    var_dump($msg);
    $object = $msg->data;
    $serv->sendto($object->address, $object->port, $object->data, $object->server_socket);
});
```

* Proses mengalami error [onWorkerError](/server/events?id=onworkererror)
```php
$serv->on('WorkerError', function (Swoole\Server $serv, Swoole\Server\StatusInfo $info) {
    var_dump($info);
});
```

* Proses task menerima tugas [onTask](/server/events?id=ontask)
```php
$server->on('Task', function (Swoole\Server $serv, Swoole\Server\Task $task) {
    var_dump($task);
});
```

* Proses worker menerima hasil pemrosesan dari proses task [onFinish](/server/events?id=onfinish)
```php
$server->on('Finish', function (Swoole\Server $serv, Swoole\Server\TaskResult $result) {
    var_dump($result);
});
```

* [Swoole\Server\Event](/server/event_class)
* [Swoole\Server\Packet](/server/packet_class)
* [Swoole\Server\PipeMessage](/server/pipemessage_class)
* [Swoole\Server\StatusInfo](/server/statusinfo_class)
* [Swoole\Server\Task](/server/task_class)
* [Swoole\Server\TaskResult](/server/taskresult_class)
