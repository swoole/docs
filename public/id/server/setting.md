# Konfigurasi

Fungsi [Swoole\Server->set()](/server/methods?id=set) digunakan untuk mengatur berbagai parameter runtime `Server`. Semua sub-halaman di bagian ini adalah elemen dari array konfigurasi.

!> Mulai dari versi [v4.5.5](/version/log?id=v455), level bawah akan mendeteksi apakah item konfigurasi yang diatur benar. Jika mengatur item konfigurasi yang bukan disediakan oleh `Swoole`, akan menghasilkan Warning.

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```

### debug_mode

?> Mengatur mode log ke mode debug `debug`, hanya berfungsi jika kompilasi menggunakan `--enable-debug`.

```php
$server->set([
  'debug_mode' => true
])
```

### trace_flags

?> Mengatur label log trace, hanya mencetak sebagian log trace. `trace_flags` mendukung penggunaan operator `|` atau untuk mengatur beberapa item trace. Hanya berfungsi jika kompilasi menggunakan `--enable-trace-log`.

Level bawah mendukung item trace berikut, dapat menggunakan `SWOOLE_TRACE_ALL` untuk melacak semua item:

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`

### log_file

?> **Menentukan file log error `Swoole`**

?> Informasi error yang terjadi selama runtime `Swoole` akan dicatat ke file ini, secara default akan dicetak ke layar.  
Setelah mengaktifkan mode daemon `(daemonize => true)`, output standar akan dialihkan ke `log_file`. Konten yang dicetak ke layar seperti `echo/var_dump/print` dalam kode PHP akan ditulis ke file `log_file`.

* **Tips**

    * Log di `log_file` hanya untuk mencatat error runtime, tidak perlu disimpan dalam jangka panjang.

    * **Label Log**

        ?> Dalam informasi log, sebelum process ID akan ditambahkan beberapa label, yang menunjukkan tipe thread/proses tempat log dihasilkan.

        * `#` Proses Master
        * `$` Proses Manager
        * `*` Proses Worker
        * `^` Proses Task

    * **Membuka Ulang File Log**

        ?> Selama program server berjalan, jika file log dipindahkan (`mv`) atau dihapus (`unlink`), informasi log tidak dapat ditulis dengan normal. Pada saat ini, dapat mengirim sinyal `SIGRTMIN` ke `Server` untuk membuka ulang file log.

        * Hanya mendukung platform `Linux`
        * Tidak mendukung proses [UserProcess](/server/methods?id=addProcess)

* **Catatan**

    !> `log_file` tidak akan membagi file secara otomatis, jadi perlu membersihkan file ini secara berkala. Dengan mengamati output `log_file`, dapat memperoleh berbagai informasi error dan peringatan server.

### log_level

?> **Mengatur level pencetakan log error `Server`, rentang `0-6`. Informasi log di bawah level `log_level` tidak akan dikeluarkan.** 【Default：`SWOOLE_LOG_INFO`】

Konstanta level terkait lihat [Tingkat Log](/consts?id=tingkat-log)

* **Catatan**

    !> `SWOOLE_LOG_DEBUG` dan `SWOOLE_LOG_TRACE` hanya tersedia saat dikompilasi dengan [--enable-debug-log](/environment?id=parameter-debug) dan [--enable-trace-log](/environment?id=parameter-debug);  
    Saat mengaktifkan daemon `daemonize`, level bawah akan menulis semua output cetak layar dalam program ke [log_file](/server/setting?id=log_file), konten ini tidak dikontrol oleh `log_level`.

### log_date_format

?> **Mengatur format waktu log `Server`**, format referensi [strftime](https://www.php.net/manual/en/function.strftime.php) `format`

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```

### log_date_with_microseconds

?> **Mengatur presisi log `Server`, apakah menyertakan mikrodetik**【Default：`false`】

### log_rotation

?> **Mengatur rotasi log `Server`**【Default：`SWOOLE_LOG_ROTATION_SINGLE`】

| Konstanta                        | Keterangan | Informasi Versi |
| -------------------------------- | ---------- | --------------- |
| SWOOLE_LOG_ROTATION_SINGLE       | Tidak aktif | -               |
| SWOOLE_LOG_ROTATION_MONTHLY      | Bulanan    | v4.5.8          |
| SWOOLE_LOG_ROTATION_DAILY        | Harian     | v4.5.2          |
| SWOOLE_LOG_ROTATION_HOURLY       | Per jam    | v4.5.8          |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE | Per menit  | v4.5.8          |

### display_errors

?> Mengaktifkan / menonaktifkan informasi error `Swoole`.

```php
$server->set([
  'display_errors' => true
])
```

### dns_server

?> Mengatur alamat `ip` untuk query `dns`.

### socket_dns_timeout

?> Waktu timeout resolusi nama domain. Jika klien coroutine diaktifkan di sisi server, parameter ini dapat mengontrol waktu timeout resolusi nama domain klien, dalam satuan detik.

### socket_connect_timeout

?> Waktu timeout koneksi klien. Jika klien coroutine diaktifkan di sisi server, parameter ini dapat mengontrol waktu timeout koneksi klien, dalam satuan detik.

### socket_write_timeout / socket_send_timeout

?> Waktu timeout tulis klien. Jika klien coroutine diaktifkan di sisi server, parameter ini dapat mengontrol waktu timeout tulis klien, dalam satuan detik.  
Konfigurasi ini juga dapat digunakan untuk mengontrol waktu timeout eksekusi `shell_exec` atau [Swoole\Coroutine\System::exec()](/coroutine/system?id=exec) setelah `coroutine`.

### socket_read_timeout / socket_recv_timeout

?> Waktu timeout baca klien. Jika klien coroutine diaktifkan di sisi server, parameter ini dapat mengontrol waktu timeout baca klien, dalam satuan detik.

### max_coroutine / max_coro_num :id=max_coroutine

?> **Mengatur jumlah maksimum coroutine dalam proses kerja saat ini.**【Default：`100000`，untuk versi Swoole lebih kecil dari `v4.4.0-beta` nilai default adalah `3000`】

?> Melebihi `max_coroutine`, level bawah tidak dapat membuat coroutine baru. Swoole di sisi server akan melempar error `exceed max number of coroutine`, `TCP Server` akan langsung menutup koneksi, `Http Server` akan mengembalikan kode status HTTP 503.

?> Dalam program `Server`, jumlah maksimum coroutine yang sebenarnya dapat dibuat sama dengan `worker_num * max_coroutine`. Jumlah coroutine proses task dan UserProcess dihitung secara terpisah.

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```

### enable_deadlock_check

?> Mengaktifkan deteksi deadlock coroutine.

```php
$server->set([
  'enable_deadlock_check' => true
]);
```

### enable_preemptive_scheduler

?> Mengatur untuk mengaktifkan penjadwalan preemptive coroutine, untuk menghindari salah satu coroutine yang berjalan terlalu lama menyebabkan coroutine lain kelaparan. Waktu eksekusi maksimum coroutine adalah `10ms`.

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```

### c_stack_size / stack_size

?> Mengatur ukuran memori stack C awal untuk satu coroutine, default 2M.

### aio_core_worker_num

?> Mengatur jumlah minimum thread kerja `AIO`, nilai default adalah jumlah inti `cpu`.

### aio_worker_num

?> Mengatur jumlah maksimum thread kerja `AIO`, nilai default adalah jumlah inti `cpu` * 8.

### aio_max_wait_time

?> Waktu maksimum thread kerja menunggu tugas, dalam satuan detik.

### aio_max_idle_time

?> Waktu idle maksimum thread kerja, dalam satuan detik.

### iouring_entries

?> Mengatur ukuran antrian `io_uring`, default `8192`. Jika nilai yang diberikan bukan `pangkat 2`, kernel akan mengubahnya ke pangkat 2 terdekat yang lebih besar dari nilai tersebut.

!> Jika nilai yang diberikan terlalu besar, kernel akan melempar error dan menghentikan program.

!> Hanya dapat digunakan jika sistem memiliki `liburing` dan kompilasi `Swoole` menggunakan `--enable-iouring`.

### iouring_workers

?> Mengatur jumlah thread kerja `io_uring`, nilai default adalah `Jumlah inti CPU * 4`.

!> Jika nilai yang diberikan terlalu besar, kernel akan melempar error dan menghentikan program.

!> Hanya dapat digunakan jika sistem memiliki `liburing` dan kompilasi `Swoole` menggunakan `--enable-iouring`.

### iouring_flag

?> Mengatur mode kerja `io_uring`, nilai default adalah `SWOOLE_IOURING_DEFAULT`.

- `SWOOLE_IOURING_DEFAULT`, mode interrupt-driven. Kirim permintaan `I/O` melalui panggilan sistem `io_uring_enter`, lalu periksa langsung status antrian completion untuk menentukan apakah sudah selesai.
- `SWOOLE_IOURING_SQPOLL`, mode polling kernel. Kernel akan membuat thread kernel untuk mengirim dan memanen permintaan `I/O`, hampir sepenuhnya menghilangkan context switching antara user-mode dan kernel-mode, kinerja lebih baik.

!> Jika mode yang diberikan salah, kernel akan menggunakan `SWOOLE_IOURING_DEFAULT` mode interrupt-driven.

### reactor_num

?> **Mengatur jumlah thread [Reactor](/learn?id=reactor-thread) yang akan dimulai.**【Default：jumlah inti `CPU`】

?> Melalui parameter ini, jumlah thread pemrosesan event dalam proses utama dapat diatur untuk memanfaatkan multi-core secara penuh. Default akan mengaktifkan jumlah yang sama dengan jumlah inti `CPU`.  
Thread `Reactor` dapat memanfaatkan multi-core, misalnya: mesin memiliki `128` core, maka level bawah akan memulai `128` thread.  
Setiap thread akan mempertahankan satu [EventLoop](/learn?id=apa-itu-eventloop). Antar thread tidak ada lock, instruksi dapat dieksekusi secara paralel oleh `128` core `CPU`.  
Mengingat penjadwalan sistem operasi memiliki tingkat penurunan kinerja tertentu, dapat diatur ke CPU core * 2, untuk memaksimalkan penggunaan setiap core CPU.

* **Tips**

    * `reactor_num` disarankan diatur ke `1-4` kali jumlah inti `CPU`
    * `reactor_num` maksimum tidak boleh melebihi [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 4

* **Catatan**

    !> -`reactor_num` harus kurang dari atau sama dengan `worker_num`;  
    -Jika `reactor_num` yang diatur lebih besar dari `worker_num`, akan otomatis disesuaikan sehingga `reactor_num` sama dengan `worker_num`;  
    -Pada mesin dengan lebih dari `8` core, `reactor_num` default diatur ke `8`.

### worker_num

?> **Mengatur jumlah proses `Worker` yang akan dimulai.**【Default：jumlah inti `CPU`】

?> Jika `1` permintaan memakan waktu `100ms`, untuk menyediakan kapasitas pemrosesan `1000QPS`, harus mengkonfigurasi `100` proses atau lebih.  
Tetapi semakin banyak proses yang dibuka, memori yang digunakan akan meningkat secara signifikan, dan overhead perpindahan antar proses akan semakin besar. Jadi atur secukupnya saja. Jangan terlalu besar.

* **Tips**

    * Jika kode bisnis sepenuhnya [asynchronous IO](/learn?id=synchronous-io-asynchronous-io), atur ke `1-4` kali jumlah inti `CPU` adalah yang paling masuk akal
    * Jika kode bisnis [synchronous IO](/learn?id=synchronous-io-asynchronous-io), perlu disesuaikan berdasarkan waktu respons permintaan dan beban sistem, misalnya: `100-500`
    * Default diatur ke [swoole_cpu_num()](/functions?id=swoole_cpu_num), maksimum tidak boleh melebihi [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000
    * Asumsikan setiap proses memakan `40M` memori, `100` proses perlu memakan `4G` memori.

### max_request

?> **Mengatur jumlah maksimum tugas untuk proses `worker`.**【Default：`0` yaitu proses tidak akan keluar】

?> Setelah proses `worker` selesai memproses melebihi jumlah tugas ini, akan otomatis keluar. Setelah keluar, proses akan membebaskan semua memori dan resource.

!> Parameter ini terutama digunakan untuk mengatasi masalah kebocoran memori proses PHP akibat kode program yang tidak sesuai standar. Aplikasi PHP memiliki kebocoran memori lambat, tetapi tidak dapat menemukan penyebab spesifik atau tidak dapat diperbaiki. Dapat diatasi sementara dengan mengatur `max_request`. Namun perlu menemukan kode yang bocor memori dan memperbaikinya, bukan melalui solusi ini. Dapat menggunakan Swoole Tracker untuk menemukan kode yang bocor.

* **Tips**

    * Mencapai max_request belum tentu segera menutup proses, lihat [max_wait_time](/server/setting?id=max_wait_time).
    * Di [SWOOLE_BASE](/learn?id=swoole_base), mencapai max_request dan me-restart proses akan menyebabkan koneksi klien terputus.

    !> Ketika terjadi fatal error dalam proses `worker` atau `exit` dijalankan secara manual, proses akan otomatis keluar. Proses `master` akan memulai proses `worker` baru untuk terus memproses permintaan.

### max_conn / max_connection

?> **Jumlah maksimum koneksi yang diizinkan untuk program server.**【Default：`ulimit -n`】

?> Misalnya `max_connection => 10000`, parameter ini digunakan untuk mengatur berapa banyak koneksi `TCP` maksimum yang dapat dipertahankan oleh `Server`. Setelah melebihi jumlah ini, koneksi yang baru masuk akan ditolak.

* **Tips**

    * **Pengaturan Default**

        * Jika lapisan aplikasi tidak mengatur `max_connection`, level bawah akan menggunakan nilai `ulimit -n` sebagai pengaturan default
        * Di versi `4.2.9` atau yang lebih baru, ketika level bawah mendeteksi `ulimit -n` melebihi `100000`, akan default diatur ke `100000`. Alasannya adalah beberapa sistem mengatur `ulimit -n` menjadi `1 juta`, perlu mengalokasikan memori dalam jumlah besar, yang menyebabkan kegagalan startup.

    * **Batas Maksimum**

        * Jangan mengatur `max_connection` melebihi `1M`

    * **Pengaturan Minimum**

        * Jika opsi ini diatur terlalu kecil, level bawah akan melempar error dan mengaturnya ke nilai `ulimit -n`.
        * Nilai minimum adalah `(worker_num + task_worker_num) * 2 + 32`

    ```shell
    serv->max_connection is too small.
    ```

    * **Penggunaan Memori**

        * Parameter `max_connection` jangan diatur terlalu besar, atur sesuai dengan kondisi memori mesin yang sebenarnya. `Swoole` akan mengalokasikan satu blok memori besar sekaligus berdasarkan nilai ini untuk menyimpan informasi `Connection`. Informasi `Connection` untuk satu koneksi `TCP` membutuhkan `224` byte.

* **Catatan**

    !> `max_connection` maksimum tidak boleh melebihi nilai `ulimit -n` sistem operasi, jika tidak akan melaporkan peringatan, dan direset ke nilai `ulimit -n`.

    ```shell
    WARN swServer_start_check: serv->max_conn is exceed the maximum value[100000].

    WARNING set_max_connection: max_connection is exceed the maximum value, it's reset to 10240
    ```

### task_worker_num

?> **Mengkonfigurasi jumlah [Task process](/learn?id=taskworker-process).**

?> Setelah mengkonfigurasi parameter ini, fungsi `task` akan diaktifkan. Oleh karena itu, `Server` harus mendaftarkan `2` fungsi callback event [onTask](/server/events?id=ontask) dan [onFinish](/server/events?id=onfinish). Jika tidak mendaftar, program server tidak akan dapat dimulai.

* **Tips**

    * [Task process](/learn?id=taskworker-process) bersifat synchronous blocking

    * Nilai maksimum tidak boleh melebihi [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000

    * **Metode Perhitungan**
        * Waktu pemrosesan satu `task`, misalnya `100ms`, maka satu proses dalam 1 detik dapat menangani `1/0.1=10` task
        * Kecepatan pengiriman `task`, misalnya menghasilkan `2000` `task` per detik
        * `2000/10=200`, perlu mengatur `task_worker_num => 200`, mengaktifkan `200` proses Task

* **Catatan**

    !> -Method `Swoole\Server->task` tidak dapat digunakan di dalam [Task process](/learn?id=taskworker-process)

### task_ipc_mode

?> **Mengatur cara komunikasi antara [Task process](/learn?id=taskworker-process) dan proses `Worker`.**【Default：`1`】

?> Silakan baca terlebih dahulu [Komunikasi IPC di Swoole](/learn?id=apa-itu-ipc).

Mode | Fungsi
---|---
1 | Menggunakan komunikasi `Unix Socket`【Mode default】
2 | Menggunakan komunikasi antrian `sysvmsg`
3 | Menggunakan komunikasi antrian `sysvmsg`, dan diatur ke mode rebutan

* **Tips**

    * **Mode `1`**
        * Menggunakan mode `1`, mendukung pengiriman terarah, dapat menggunakan `dst_worker_id` di method [task](/server/methods?id=task) dan [taskwait](/server/methods?id=taskwait) untuk menentukan target `Task process`.
        * Saat `dst_worker_id` diatur ke `-1`, level bawah akan memeriksa status setiap [Task process](/learn?id=taskworker-process), dan mengirim tugas ke proses yang sedang idle.

    * **Mode `2`、`3`**
        * Mode antrian pesan menggunakan antrian memori yang disediakan sistem operasi untuk menyimpan data. Jika tidak menentukan `mssage_queue_key` antrian pesan `Key`, akan menggunakan antrian privat, dan antrian pesan akan dihapus setelah program `Server` berhenti.
        * Setelah menentukan antrian pesan `Key`, data dalam antrian pesan tidak akan dihapus setelah program `Server` berhenti, sehingga setelah proses di-restart, data masih dapat diambil.
        * Dapat menggunakan `ipcrm -q` ID antrian pesan untuk menghapus data antrian pesan secara manual.
        * Perbedaan antara `Mode 2` dan `Mode 3` adalah, `Mode 2` mendukung pengiriman terarah, `$serv->task($data, $task_worker_id)` dapat menentukan [task process](/learn?id=taskworker-process) mana yang akan dikirim. `Mode 3` adalah mode rebutan penuh, [task process](/learn?id=taskworker-process) akan merebut antrian, pengiriman terarah tidak dapat digunakan, `task/taskwait` tidak dapat menentukan target process `ID`, meskipun `$task_worker_id` ditentukan, itu tidak akan valid dalam `mode 3`.

* **Catatan**

    !> -`Mode 3` akan mempengaruhi method [sendMessage](/server/methods?id=sendMessage), sehingga pesan yang dikirim oleh [sendMessage](/server/methods?id=sendMessage) akan diambil secara acak oleh salah satu [task process](/learn?id=taskworker-process).  
    -Menggunakan komunikasi antrian pesan, jika kemampuan pemrosesan `Task process` lebih rendah dari kecepatan pengiriman, dapat menyebabkan proses `Worker` terblokir.  
    -Setelah menggunakan komunikasi antrian pesan, proses task tidak dapat mendukung coroutine (mengaktifkan [task_enable_coroutine](/server/setting?id=task_enable_coroutine)).

### task_max_request

?> **Mengatur jumlah maksimum tugas untuk [task process](/learn?id=taskworker-process).**【Default：`0`】

Mengatur jumlah maksimum tugas untuk proses task. Setelah proses task selesai memproses melebihi jumlah tugas ini, akan otomatis keluar. Parameter ini untuk mencegah overflow memori proses PHP. Jika tidak ingin proses keluar otomatis, dapat diatur ke 0.

### task_tmpdir

?> **Mengatur direktori sementara data task.**【Default：direktori Linux `/tmp`】

?> Di `Server`, jika data yang dikirim melebihi `8180` byte, file sementara akan digunakan untuk menyimpan data. `task_tmpdir` di sini digunakan untuk mengatur lokasi penyimpanan file sementara.

* **Tips**

    * Level bawah default akan menggunakan direktori `/tmp` untuk menyimpan data `task`. Jika versi kernel `Linux` Anda terlalu rendah, direktori `/tmp` bukan filesystem memori, dapat diatur ke `/dev/shm/`
    * Jika direktori `task_tmpdir` tidak ada, level bawah akan mencoba membuatnya secara otomatis

* **Catatan**

    !> -Jika pembuatan gagal, `Server->start` akan gagal

### task_enable_coroutine

?> **Mengaktifkan dukungan coroutine `Task`.**【Default：`false`】，didukung sejak v4.2.12

?> Setelah diaktifkan, secara otomatis akan membuat coroutine dan [coroutine container](/coroutine/scheduler) dalam callback [onTask](/server/events?id=ontask). Kode `PHP` dapat langsung menggunakan `API` coroutine.

* **Contoh**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    //Dari proses Worker mana
    $task->worker_id;
    //Nomor tugas
    $task->id;
    //Tipe tugas, taskwait, task, taskCo, taskWaitMulti mungkin menggunakan flags yang berbeda
    $task->flags;
    //Data tugas
    $task->data;
    //Waktu pengiriman, ditambahkan di v4.6.0
    $task->dispatch_time;
    //API Coroutine
    co::sleep(0.2);
    //Menyelesaikan tugas, selesai dan mengembalikan data
    $task->finish([123, 'hello']);
});
```

* **Catatan**

    !> -`task_enable_coroutine` hanya dapat digunakan jika [enable_coroutine](/server/setting?id=enable_coroutine) adalah `true`  
    -Mengaktifkan `task_enable_coroutine`, proses kerja `Task` mendukung coroutine  
    -Tidak mengaktifkan `task_enable_coroutine`, hanya mendukung synchronous blocking

### task_use_object/task_object :id=task_use_object

?> **Menggunakan format callback Task berorientasi objek.**【Default：`false`】

?> Jika diatur ke `true`, callback [onTask](/server/events?id=ontask) akan berubah menjadi mode objek.

* **Contoh**

```php
<?php

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 3,
    'task_use_object' => true,
//    'task_object' => true, // alias yang ditambahkan di v4.6.0
]);
$server->on('receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd' => $fd,]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    //$task di sini adalah objek Swoole\Server\Task
    $server->send($task->data['fd'], json_encode($server->stats()));
});
$server->start();
```

### dispatch_mode

?> **Strategi distribusi paket data.**【Default：`2`】

Nilai Mode | Mode | Fungsi
---|---|---
1 | Round Robin | Setelah menerima, akan mendistribusikan secara round robin ke setiap proses `Worker`
2 | Mode Tetap | Mendistribusikan `Worker` berdasarkan file descriptor koneksi. Dengan cara ini, data dari koneksi yang sama hanya akan diproses oleh `Worker` yang sama
3 | Mode Preemptive | Proses utama akan memilih berdasarkan status sibuk/idle `Worker`, hanya akan mengirim ke `Worker` yang sedang idle
4 | Distribusi IP | Berdasarkan `IP` klien melakukan modulo `hash`, dialokasikan ke proses `Worker` tetap.<br>Dapat memastikan data koneksi dari sumber IP yang sama selalu dialokasikan ke proses `Worker` yang sama. Algoritma `inet_addr_mod(ClientIP, worker_num)`
5 | Distribusi UID | Perlu memanggil [Server->bind()](/server/methods?id=bind) dalam kode pengguna untuk mengikat `1` `uid` ke koneksi. Kemudian level bawah mendistribusikan ke proses `Worker` yang berbeda berdasarkan nilai `UID`.<br>Algoritma `UID % worker_num`, jika perlu menggunakan string sebagai `UID`, dapat menggunakan `crc32(UID_STRING)`
7 | Mode stream | `Worker` yang idle akan `accept` koneksi, dan menerima permintaan baru dari [Reactor](/learn?id=reactor-thread)

* **Tips**

    * **Saran Penggunaan**

        * `Server` tanpa status dapat menggunakan `1` atau `3`, `Server` synchronous blocking menggunakan `3`, asynchronous non-blocking menggunakan `1`
        * Dengan status menggunakan `2`、`4`、`5`

    * **Protokol UDP**

        * `dispatch_mode=2/4/5` adalah alokasi tetap, level bawah menggunakan modulo `IP` klien untuk hash ke proses `Worker` yang berbeda
        * `dispatch_mode=1/3` dialokasikan secara acak ke proses `Worker` yang berbeda
        * Fungsi `inet_addr_mod`

```
    function inet_addr_mod($ip, $worker_num) {
        $ip_parts = explode('.', $ip);
        if (count($ip_parts) != 4) {
            return false;
        }
        $ip_parts = array_reverse($ip_parts);

        $ip_long = 0;
        foreach ($ip_parts as $part) {
            $ip_long <<= 8;
            $ip_long |= (int) $part;
        }

        return $ip_long % $worker_num;
    }
```

* **Mode Base**
    * Konfigurasi `dispatch_mode` tidak valid dalam mode [SWOOLE_BASE](/learn?id=swoole_base), karena `BASE` tidak ada pengiriman tugas. Saat menerima data dari klien, akan langsung memanggil callback [onReceive](/server/events?id=onreceive) di thread/proses saat ini, tidak perlu mengirim ke proses `Worker`.

* **Catatan**

    !> -`dispatch_mode=1/3`, level bawah akan menonaktifkan event `onConnect/onClose`, karena dalam 2 mode ini tidak dapat menjamin urutan `onConnect/onClose/onReceive`;  
    -Program server yang non-request-response, jangan gunakan mode `1` atau `3`. Contoh: layanan http bersifat response, dapat menggunakan `1` atau `3`, yang memiliki status koneksi TCP panjang tidak boleh menggunakan `1` atau `3`.

### dispatch_func

?> Mengatur fungsi `dispatch`. `Swoole` level bawah memiliki `6` jenis [dispatch_mode](/server/setting?id=dispatch_mode) bawaan. Jika masih belum memenuhi kebutuhan, dapat menulis fungsi `C++` atau `PHP` untuk mengimplementasikan logika `dispatch`.

* **Cara Penggunaan**

```php
$server->set(array(
  'dispatch_func' => 'my_dispatch_function',
));
```

* **Tips**

    * Setelah mengatur `dispatch_func`, level bawah secara otomatis akan mengabaikan konfigurasi `dispatch_mode`
    * Jika fungsi yang sesuai dengan `dispatch_func` tidak ada, level bawah akan melempar fatal error
    * Jika perlu `dispatch` paket yang melebihi 8K, `dispatch_func` hanya dapat memperoleh konten `0-8180` byte

* **Menulis Fungsi PHP**

    ?> Karena `ZendVM` tidak mendukung lingkungan multi-thread, meskipun beberapa thread [Reactor](/learn?id=reactor-thread) diatur, hanya satu `dispatch_func` yang dapat dieksekusi pada satu waktu. Oleh karena itu, level bawah akan melakukan operasi penguncian saat mengeksekusi fungsi PHP ini, mungkin ada masalah perebutan kunci. Jangan melakukan operasi blocking apa pun di `dispatch_func`, karena akan menyebabkan grup thread `Reactor` berhenti bekerja.

    ```php
    $server->set(array(
        'dispatch_func' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd` adalah pengidentifikasi unik koneksi klien, dapat menggunakan `Server::getClientInfo` untuk mendapatkan informasi koneksi
    * `$type` tipe data, `0` menunjukkan data dari klien, `4` menunjukkan koneksi klien dibuat, `3` menunjukkan koneksi klien ditutup
    * `$data` konten data, perlu diperhatikan: jika mengaktifkan parameter pemrosesan protokol seperti `HTTP`, `EOF`, `Length` dll., level bawah akan melakukan penggabungan paket. Tetapi di fungsi `dispatch_func`, hanya dapat meneruskan 8K konten pertama dari paket data, tidak dapat mendapatkan konten paket lengkap.
    * **Harus** mengembalikan angka `0 - (server->worker_num - 1)`, menunjukkan proses kerja target pengiriman paket data `ID`
    * Kurang dari `0` atau lebih besar atau sama dengan `server->worker_num` adalah target `ID` yang error, data `dispatch` akan dibuang

* **Menulis Fungsi C++**

    **Di ekstensi PHP lainnya, gunakan swoole_add_function untuk mendaftarkan fungsi panjang ke engine Swoole.**

    ?> Saat fungsi C++ dipanggil, level bawah tidak akan mengunci, pemanggil harus menjamin keamanan thread sendiri

    ```c++
    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data);

    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data)
    {
        printf("cpp, type=%d, size=%d\n", data->info.type, data->info.len);
        return data->info.len % serv->worker_num;
    }

    int register_dispatch_function(swModule *module)
    {
        swoole_add_function("my_dispatch_function", (void *) dispatch_function);
    }
    ```

    * Fungsi `dispatch` harus mengembalikan target `worker` process `id` tujuan pengiriman
    * `worker_id` yang dikembalikan tidak boleh melebihi `server->worker_num`, jika tidak level bawah akan melempar segment error
    * Mengembalikan negatif `(return -1)` berarti membuang paket data ini
    * `data` dapat membaca tipe dan panjang event
    * `conn` adalah informasi koneksi, jika paket `UDP`, `conn` adalah `NULL`

* **Catatan**

    !> -`dispatch_func` hanya valid dalam mode [SWOOLE_PROCESS](/learn?id=swoole_process), server tipe [UDP/TCP/UnixSocket](/server/methods?id=__construct) semuanya valid  
    -`worker_id` yang dikembalikan tidak boleh melebihi `server->worker_num`, jika tidak level bawah akan melempar segment error

### message_queue_key

?> **Mengatur `KEY` antrian pesan.**【Default：`ftok($php_script_file, 1)`】

?> Hanya digunakan saat [task_ipc_mode](/server/setting?id=task_ipc_mode) = 2/3. `Key` yang diatur hanya sebagai `KEY` antrian tugas `Task`, lihat [Komunikasi IPC di Swoole](/learn?id=apa-itu-ipc).

?> Antrian `task` tidak akan dihapus setelah `server` berakhir. Setelah memulai ulang program, [task process](/learn?id=taskworker-process) akan terus memproses tugas dalam antrian. Jika tidak ingin menjalankan tugas `Task` lama setelah restart program, dapat menghapus antrian pesan ini secara manual.

```shell
ipcs -q 
ipcrm -Q [msgkey]
```

### daemonize

?> **Menjalankan sebagai daemon**【Default：`false`】

?> Saat mengatur `daemonize => true`, program akan berjalan di latar belakang sebagai proses daemon. Program server yang berjalan lama harus mengaktifkan ini.  
Jika tidak mengaktifkan daemon, saat terminal ssh keluar, program akan dihentikan.

* **Tips**

    * Setelah mengaktifkan daemon, input dan output standar akan dialihkan ke `log_file`
    * Jika tidak mengatur `log_file`, akan dialihkan ke `/dev/null`, semua informasi cetakan layar akan dibuang
    * Setelah mengaktifkan daemon, nilai variabel lingkungan `CWD` (direktori saat ini) akan berubah, pembacaan/penulisan file dengan path relatif akan error. Dalam program `PHP`, harus menggunakan path absolut

    * **systemd**

        * Saat menggunakan `systemd` atau `supervisord` untuk mengelola layanan `Swoole`, jangan atur `daemonize => true`. Alasan utamanya adalah mekanisme `systemd` berbeda dengan `init`. `PID` dari proses `init` adalah `1`. Setelah program menggunakan `daemonize`, akan terlepas dari terminal, akhirnya dikelola oleh proses `init`, hubungan menjadi hubungan parent-child.
        * Tetapi `systemd` memulai proses latar belakang terpisah, melakukan `fork` sendiri untuk mengelola proses layanan lainnya, sehingga tidak perlu `daemonize`. Sebaliknya, menggunakan `daemonize => true` akan membuat program `Swoole` kehilangan hubungan parent-child dengan proses manajemen tersebut.

### backlog

?> **Mengatur panjang antrian `Listen`**

?> Misalnya `backlog => 128`, parameter ini akan menentukan berapa banyak koneksi yang menunggu `accept` secara bersamaan.

* **Tentang `backlog` `TCP`**

    ?> `TCP` memiliki proses three-way handshake, klien `syn=>server` `syn+ack=>klien` `ack`, saat server menerima `ack` dari klien, akan menempatkan koneksi ke dalam antrian yang disebut `accept queue` (catatan 1),  
    Ukuran antrian ditentukan oleh minimum antara parameter `backlog` dan konfigurasi `somaxconn`, dapat menggunakan perintah `ss -lt` untuk melihat ukuran akhir antrian `accept queue`, proses utama `Swoole` memanggil `accept` (catatan 2)  
    untuk mengambil dari `accept queue`. Saat `accept queue` penuh, koneksi mungkin berhasil (catatan 4),  
    atau mungkin gagal, setelah gagal, gejala yang terlihat klien adalah koneksi direset (catatan 3)  
    atau koneksi timeout, sementara server akan mencatat catatan kegagalan, dapat dilihat melalui `netstat -s|grep 'times the listen queue of a socket overflowed`. Jika fenomena di atas muncul, Anda harus memperbesar nilai ini. Untungnya, mode SWOOLE_PROCESS dari `Swoole` berbeda dengan perangkat lunak seperti `PHP-FPM/Apache`, tidak bergantung pada `backlog` untuk mengatasi masalah antrian koneksi. Jadi pada dasarnya tidak akan mengalami fenomena di atas.

    * Catatan 1: Setelah `linux2.2`, proses handshake dibagi menjadi dua antrian: `syn queue` dan `accept queue`. Panjang `syn queue` ditentukan oleh `tcp_max_syn_backlog`.
    * Catatan 2: Kernel versi tinggi memanggil `accept4`, untuk menghemat satu panggilan sistem `set no block`.
    * Catatan 3: Klien setelah menerima paket `syn+back` menganggap koneksi berhasil, sebenarnya server masih dalam status setengah terhubung, mungkin mengirim paket `rst` ke klien, yang terlihat oleh klien sebagai `Connection reset by peer`.
    * Catatan 4: Keberhasilan melalui mekanisme retransmisi TCP, konfigurasi terkait adalah `tcp_synack_retries` dan `tcp_abort_on_overflow`.

### open_tcp_keepalive

?> Dalam `TCP` ada mekanisme `Keep-Alive` yang dapat mendeteksi koneksi mati. Jika lapisan aplikasi tidak sensitif terhadap siklus koneksi mati atau tidak mengimplementasikan mekanisme heartbeat, dapat menggunakan mekanisme `keepalive` yang disediakan sistem operasi untuk memutus koneksi mati.
Dalam konfigurasi [Server->set()](/server/methods?id=set), tambahkan `open_tcp_keepalive => true` untuk mengaktifkan `TCP keepalive`.
Selain itu, ada `3` opsi untuk menyesuaikan detail `keepalive`.

* **Opsi**

     * **tcp_keepidle**

        Satuan detik, jika koneksi tidak ada permintaan data dalam `n` detik, akan mulai melakukan probe pada koneksi ini.

     * **tcp_keepcount**

        Jumlah probe, setelah melebihi jumlah akan `close` koneksi ini.

     * **tcp_keepinterval**

        Interval waktu probe, satuan detik.

* **Contoh**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, //4 detik tanpa transmisi data, lakukan deteksi
    'tcp_keepinterval' => 1, //probe setiap 1 detik
    'tcp_keepcount' => 5, //jumlah probe, setelah 5 kali tanpa balasan, tutup koneksi ini
));

$serv->on('connect', function ($serv, $fd) {
    var_dump("Client:Connect $fd");
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    var_dump($data);
});

$serv->on('close', function ($serv, $fd) {
  var_dump("close fd $fd");
});

$serv->start();
```

### heartbeat_check_interval

?> **Mengaktifkan deteksi heartbeat**【Default：`false`】

?> Opsi ini menunjukkan seberapa sering melakukan polling, satuan detik. Misalnya `heartbeat_check_interval => 60`, berarti setiap `60` detik, melintasi semua koneksi. Jika koneksi dalam `120` detik (saat `heartbeat_idle_time` tidak diatur, default adalah dua kali interval), tidak mengirim data apa pun ke server, koneksi ini akan dipaksa ditutup. Jika tidak dikonfigurasi, heartbeat tidak akan diaktifkan. Konfigurasi ini default dimatikan.

* **Tips**
    * `Server` tidak secara aktif mengirim paket heartbeat ke klien, tetapi secara pasif menunggu klien mengirim heartbeat. `heartbeat_check` di sisi server hanya mendeteksi waktu pengiriman data terakhir dari koneksi. Jika melebihi batas, koneksi akan diputus.
    * Koneksi yang diputus oleh deteksi heartbeat tetap akan memicu callback event [onClose](/server/events?id=onclose)

* **Catatan**

    !> `heartbeat_check` hanya mendukung koneksi `TCP`

### heartbeat_idle_time

?> **Waktu idle maksimum yang diizinkan untuk koneksi**

?> Perlu digunakan bersama dengan `heartbeat_check_interval`

```php
array(
    'heartbeat_idle_time'      => 600, // menunjukkan bahwa jika koneksi tidak mengirim data apa pun ke server dalam 600 detik, koneksi ini akan dipaksa ditutup
    'heartbeat_check_interval' => 60,  // menunjukkan polling setiap 60 detik
);
```

* **Tips**

    * Setelah mengaktifkan `heartbeat_idle_time`, server tidak akan secara aktif mengirim paket data ke klien
    * Jika hanya mengatur `heartbeat_idle_time` tanpa mengatur `heartbeat_check_interval`, level bawah tidak akan membuat thread deteksi heartbeat. Dalam kode `PHP` dapat memanggil method `heartbeat` untuk menangani koneksi yang timeout secara manual.

### open_eof_check

?> **Mengaktifkan deteksi `EOF`**【Default：`false`】，lihat [Masalah Batas Paket Data TCP](/learn?id=masalah-batas-paket-data-tcp)

?> Opsi ini akan mendeteksi data yang dikirim dari koneksi klien. Hanya saat akhir paket data adalah string yang ditentukan, data akan dikirim ke proses `Worker`. Jika tidak, data akan terus digabungkan, sampai melebihi buffer atau timeout baru akan dihentikan. Saat terjadi error, level bawah akan menganggapnya sebagai koneksi berbahaya, membuang data dan memaksa menutup koneksi.  
Protokol umum seperti `Memcache/SMTP/POP` semuanya diakhiri dengan `\r\n`, dapat menggunakan konfigurasi ini. Setelah diaktifkan, dapat memastikan proses `Worker` selalu menerima satu atau lebih paket data lengkap sekaligus.

```php
array(
    'open_eof_check' => true,   //mengaktifkan deteksi EOF
    'package_eof'    => "\r\n", //mengatur EOF
)
```

* **Catatan**

    !> Konfigurasi ini hanya valid untuk `Socket` tipe `STREAM` (stream), seperti [TCP, Unix Socket Stream](/server/methods?id=__construct)   
    Deteksi `EOF` tidak mencari string `eof` dari tengah data, sehingga proses `Worker` mungkin menerima beberapa paket data sekaligus, perlu melakukan `explode("\r\n", $data)` sendiri dalam kode lapisan aplikasi untuk memisahkan paket data.

### open_eof_split

?> **Mengaktifkan pemisahan paket otomatis `EOF`**

?> Setelah mengatur `open_eof_check`, mungkin terjadi beberapa data digabung dalam satu paket. Parameter `open_eof_split` dapat mengatasi masalah ini, lihat [Masalah Batas Paket Data TCP](/learn?id=masalah-batas-paket-data-tcp).

?> Mengatur parameter ini perlu melintasi seluruh konten paket data, mencari `EOF`, sehingga akan menghabiskan banyak resource `CPU`. Asumsikan setiap paket data `2M`, 10.000 permintaan per detik, ini dapat menghasilkan `20G` instruksi pencocokan karakter `CPU`.

```php
array(
    'open_eof_split' => true,   //mengaktifkan deteksi EOF_SPLIT
    'package_eof'    => "\r\n", //mengatur EOF
)
```

* **Tips**

    * Setelah mengaktifkan parameter `open_eof_split`, level bawah akan mencari `EOF` dari tengah paket data, dan memisahkan paket data. [onReceive](/server/events?id=onreceive) setiap kali hanya menerima satu paket data yang diakhiri dengan string `EOF`.
    * Setelah mengaktifkan parameter `open_eof_split`, terlepas dari apakah parameter `open_eof_check` diatur atau tidak, `open_eof_split` akan tetap berlaku.

    * **Perbedaan dengan `open_eof_check`**

        * `open_eof_check` hanya memeriksa apakah akhir data yang diterima adalah `EOF`, sehingga kinerjanya paling baik, hampir tanpa konsumsi
        * `open_eof_check` tidak dapat mengatasi masalah penggabungan beberapa paket data, misalnya mengirim dua data dengan `EOF` secara bersamaan, level bawah mungkin mengembalikan semuanya sekaligus
        * `open_eof_split` akan membandingkan data byte per byte dari kiri ke kanan, mencari `EOF` dalam data untuk memisahkan paket, kinerjanya lebih buruk. Tetapi setiap kali hanya mengembalikan satu paket data

### package_eof

?> **Mengatur string `EOF`.** Lihat [Masalah Batas Paket Data TCP](/learn?id=masalah-batas-paket-data-tcp)

?> Perlu digunakan bersama dengan `open_eof_check` atau `open_eof_split`.

* **Catatan**

    !> `package_eof` maksimum hanya diizinkan memasukkan string `8` byte

### open_length_check

?> **Mengaktifkan fitur deteksi panjang paket**【Default：`false`】，lihat [Masalah Batas Paket Data TCP](/learn?id=masalah-batas-paket-data-tcp)

?> Deteksi panjang paket menyediakan parsing format protokol header tetap + body. Setelah diaktifkan, dapat memastikan proses `Worker` [onReceive](/server/events?id=onreceive) setiap kali menerima satu paket data lengkap.  
Protokol deteksi panjang hanya perlu menghitung panjang sekali, pemrosesan data hanya melakukan offset pointer, kinerja sangat tinggi, **disarankan digunakan**.

* **Tips**

    * **Protokol panjang menyediakan 3 opsi untuk mengontrol detail protokol.**

        ?> Konfigurasi ini hanya valid untuk `Socket` tipe `STREAM`, seperti [TCP, Unix Socket Stream](/server/methods?id=__construct)

        * **package_length_type**

          ?> Beberapa field dalam header paket sebagai nilai panjang paket, level bawah mendukung 10 jenis panjang. Silakan lihat [package_length_type](/server/setting?id=package_length_type)

        * **package_body_offset**

          ?> Mulai dari byte ke berapa untuk menghitung panjang, umumnya ada 2 situasi:

            * Nilai `length` mencakup seluruh paket (header + body), `package_body_offset` adalah `0`
            * Panjang header adalah `N` byte, nilai `length` tidak termasuk header, hanya mencakup body, `package_body_offset` diatur ke `N`

        * **package_length_offset**

          ?> Nilai panjang `length` berada di byte ke berapa dalam header.

            * Contoh:

            ```c
            struct
            {
                uint32_t type;
                uint32_t uid;
                uint32_t length;
                uint32_t serid;
                char body[0];
            }
            ```

        ?> Dalam desain protokol komunikasi di atas, panjang header adalah `4` integer, `16` byte, nilai `length` berada pada integer ke-`3`. Oleh karena itu `package_length_offset` diatur ke `8`, `0-3` byte adalah `type`, `4-7` byte adalah `uid`, `8-11` byte adalah `length`, `12-15` byte adalah `serid`.

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```

### package_length_type

?> **Tipe nilai panjang**, menerima satu parameter karakter, konsisten dengan fungsi [pack](http://php.net/manual/en/function.pack.php) `PHP`.

Saat ini `Swoole` mendukung `10` jenis:

Parameter Karakter | Fungsi
---|---
c | Signed, 1 byte
C | Unsigned, 1 byte
s | Signed, host byte order, 2 byte
S | Unsigned, host byte order, 2 byte
n | Unsigned, network byte order, 2 byte
N | Unsigned, network byte order, 4 byte
l | Signed, host byte order, 4 byte (huruf L kecil)
L | Unsigned, host byte order, 4 byte (huruf L besar)
v | Unsigned, little-endian byte order, 2 byte
V | Unsigned, little-endian byte order, 4 byte

### package_length_func

?> **Mengatur fungsi parsing panjang**

?> Mendukung `2` jenis fungsi: `C++` atau `PHP`. Fungsi panjang harus mengembalikan integer.

Angka Kembalian | Fungsi
---|---
Mengembalikan 0 | Data panjang tidak cukup, perlu menerima lebih banyak data
Mengembalikan -1 | Error data, level bawah akan otomatis menutup koneksi
Mengembalikan nilai panjang paket (termasuk total panjang header dan body) | Level bawah akan otomatis menggabungkan paket dan mengembalikannya ke fungsi callback

* **Tips**

    * **Cara Penggunaan**

        ?> Prinsip implementasi adalah membaca sebagian kecil data terlebih dahulu, di dalam data ini terdapat nilai panjang. Kemudian mengembalikan panjang ini ke level bawah. Kemudian level bawah menyelesaikan penerimaan data sisanya dan menggabungkannya menjadi satu paket untuk di `dispatch`.

    * **Fungsi Parsing Panjang PHP**

        ?> Karena `ZendVM` tidak mendukung berjalan di lingkungan multi-thread, level bawah akan secara otomatis menggunakan `Mutex` untuk mengunci fungsi panjang `PHP`, menghindari eksekusi fungsi `PHP` secara concurrent. Tersedia di versi `1.9.3` atau yang lebih baru.

        !> Jangan melakukan operasi blocking `IO` dalam fungsi parsing panjang, dapat menyebabkan semua thread [Reactor](/learn?id=reactor-thread) terblokir.

    ```php
    $server = new Swoole\Server("127.0.0.1", 9501);

    $server->set(array(
        'open_length_check'   => true,
        'dispatch_mode'       => 1,
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
        'package_max_length'  => 2000000,  //panjang maksimum protokol
    ));

    $server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        var_dump($data);
        echo "#{$server->worker_id}>> received length=" . strlen($data) . "\n";
    });

    $server->start();
    ```

    * **Fungsi Parsing Panjang C++**

        ?> Di ekstensi PHP lainnya, gunakan `swoole_add_function` untuk mendaftarkan fungsi panjang ke engine `Swoole`.

        !> Saat fungsi panjang C++ dipanggil, level bawah tidak akan mengunci, pemanggil harus menjamin keamanan thread sendiri.

    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"

    using namespace std;

    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);

    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW_OK;
    }

    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length)
    {
        printf("cpp, size=%d\n", length);
        return 100;
    }
    ```

### package_max_length

?> **Mengatur ukuran maksimum paket data, dalam satuan byte.**【Default：`2M` yaitu `2 * 1024 * 1024`, minimum `64K`】

?> Setelah mengaktifkan parsing protokol [open_length_check](/server/setting?id=open_length_check)/[open_eof_check](/server/setting?id=open_eof_check)/[open_eof_split](/server/setting?id=open_eof_split)/[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol) dll., level bawah `Swoole` akan melakukan penggabungan paket data. Saat paket data belum diterima lengkap, semua data disimpan di memori.  
Oleh karena itu perlu mengatur `package_max_length`, ukuran memori maksimum yang diizinkan untuk satu paket data. Jika ada 10.000 koneksi `TCP` yang mengirim data secara bersamaan, setiap paket data `2M`, maka dalam kondisi paling ekstrem, akan memakan `20G` ruang memori.

* **Tips**

    * `open_length_check`: Saat menemukan panjang paket melebihi `package_max_length`, akan langsung membuang data ini, dan menutup koneksi, tidak akan memakan memori apa pun;
    * `open_eof_check`: Karena tidak dapat mengetahui panjang paket terlebih dahulu, data yang diterima tetap akan disimpan ke memori, terus bertambah. Saat memori yang digunakan melebihi `package_max_length`, akan langsung membuang data ini, dan menutup koneksi;
    * `open_http_protocol`: Permintaan `GET` maksimum diizinkan `8K`, dan tidak dapat mengubah konfigurasi. Permintaan `POST` akan mendeteksi `Content-Length`, jika `Content-Length` melebihi `package_max_length`, akan langsung membuang data ini, mengirim error `http 400`, dan menutup koneksi;

* **Catatan**

    !> Parameter ini jangan diatur terlalu besar, karena akan memakan memori yang besar

### open_http_protocol

?> **Mengaktifkan pemrosesan protokol `HTTP`.**【Default：`false`】

?> Mengaktifkan pemrosesan protokol `HTTP`, [Swoole\Http\Server](/http_server) akan otomatis mengaktifkan opsi ini. Diatur ke `false` berarti menonaktifkan pemrosesan protokol `HTTP`.

### open_mqtt_protocol

?> **Mengaktifkan pemrosesan protokol `MQTT`.**【Default：`false`】

?> Setelah diaktifkan, akan mem-parsing header `MQTT`, proses `worker` [onReceive](/server/events?id=onreceive) setiap kali akan mengembalikan satu paket data `MQTT` lengkap.

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```

### open_redis_protocol

?> **Mengaktifkan pemrosesan protokol `Redis`.**【Default：`false`】

?> Setelah diaktifkan, akan mem-parsing protokol `Redis`, proses `worker` [onReceive](/server/events?id=onreceive) setiap kali akan mengembalikan satu paket `Redis` lengkap. Disarankan langsung menggunakan [Redis\Server](/redis_server)

```php
$server->set(array(
  'open_redis_protocol' => true
));
```

### open_websocket_protocol

?> **Mengaktifkan pemrosesan protokol `WebSocket`.**【Default：`false`】

?> Mengaktifkan pemrosesan protokol `WebSocket`, [Swoole\WebSocket\Server](websocket_server) akan otomatis mengaktifkan opsi ini. Diatur ke `false` berarti menonaktifkan pemrosesan protokol `websocket`.  
Mengatur opsi `open_websocket_protocol` ke `true` setelahnya, akan otomatis mengatur protokol `open_http_protocol` juga menjadi `true`.

### open_websocket_close_frame

?> **Mengaktifkan frame penutup dalam protokol websocket.**【Default：`false`】

?> (Frame dengan `opcode` `0x08`) diterima dalam callback `onMessage`

?> Setelah diaktifkan, dapat menerima frame penutup yang dikirim oleh klien atau server dalam callback `onMessage` di `WebSocketServer`. Pengembang dapat memprosesnya sendiri.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->set(array("open_websocket_close_frame" => true));

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {});

$server->start();
```

### open_tcp_nodelay

?> **Mengaktifkan `open_tcp_nodelay`.**【Default：`false`】

?> Setelah diaktifkan, saat koneksi `TCP` mengirim data, algoritma penggabungan `Nagle` akan dimatikan, data akan segera dikirim ke koneksi TCP lawan. Dalam beberapa skenario, seperti terminal command line, mengetik perintah perlu segera dikirim ke server, dapat meningkatkan kecepatan respons. Silakan Google algoritma Nagle sendiri.

### open_cpu_affinity

?> **Mengaktifkan pengaturan CPU affinity.**【Default `false`】

?> Di platform hardware multi-core, mengaktifkan fitur ini akan mengikat `reactor thread`/`worker process` `Swoole` ke core yang tetap. Ini dapat menghindari proses/thread berpindah-pindah antar core saat runtime, meningkatkan tingkat hit `CPU` `Cache`.

* **Tips**

    * **Menggunakan perintah taskset untuk melihat pengaturan CPU affinity proses:**

    ```bash
    taskset -p ProcessID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > mask adalah angka mask, dihitung per `bit`, setiap `bit` sesuai dengan satu core `CPU`. Jika suatu bit adalah `0` berarti mengikat core ini, proses akan dijadwalkan ke `CPU` ini. Contohnya, proses dengan `pid` `24666` memiliki `mask = f` yang berarti tidak terikat ke `CPU`, sistem operasi akan menjadwalkan proses ini ke core `CPU` mana pun. Proses dengan `pid` `24901` memiliki `mask = 8`, `8` dalam biner adalah `1000`, berarti proses ini terikat ke core `CPU` ke-`4`.

### cpu_affinity_ignore

?> **Dalam program intensif IO, semua interupsi jaringan ditangani oleh CPU0. Jika jaringan IO sangat berat, beban CPU0 yang terlalu tinggi dapat menyebabkan interupsi jaringan tidak dapat ditangani tepat waktu, sehingga kemampuan mengirim dan menerima paket jaringan akan menurun.**

?> Jika tidak mengatur opsi ini, swoole akan menggunakan semua core CPU. Level bawah akan mengatur pengikatan CPU berdasarkan reactor_id atau worker_id modulo jumlah core CPU.  
Jika kernel dan kartu jaringan memiliki fitur multi-queue, interupsi jaringan akan didistribusikan ke multi-core, dapat mengurangi tekanan interupsi jaringan.

```php
array('cpu_affinity_ignore' => array(0, 1)) // Menerima array sebagai parameter, array(0, 1) berarti tidak menggunakan CPU0, CPU1, khusus dikosongkan untuk menangani interupsi jaringan.
```

* **Tips**

    * **Melihat interupsi jaringan**

```shell
[~]$ cat /proc/interrupts 
           CPU0       CPU1       CPU2       CPU3       
  0: 1383283707          0          0          0    IO-APIC-edge  timer
  1:          3          0          0          0    IO-APIC-edge  i8042
  3:         11          0          0          0    IO-APIC-edge  serial
  8:          1          0          0          0    IO-APIC-edge  rtc
  9:          0          0          0          0   IO-APIC-level  acpi
 12:          4          0          0          0    IO-APIC-edge  i8042
 14:         25          0          0          0    IO-APIC-edge  ide0
 82:         85          0          0          0   IO-APIC-level  uhci_hcd:usb5
 90:         96          0          0          0   IO-APIC-level  uhci_hcd:usb6
114:    1067499          0          0          0       PCI-MSI-X  cciss0
130:   96508322          0          0          0         PCI-MSI  eth0
138:     384295          0          0          0         PCI-MSI  eth1
169:          0          0          0          0   IO-APIC-level  ehci_hcd:usb1, uhci_hcd:usb2
177:          0          0          0          0   IO-APIC-level  uhci_hcd:usb3
185:          0          0          0          0   IO-APIC-level  uhci_hcd:usb4
NMI:      11370       6399       6845       6300 
LOC: 1383174675 1383278112 1383174810 1383277705 
ERR:          0
MIS:          0
```

`eth0/eth1` adalah jumlah interupsi jaringan. Jika `CPU0 - CPU3` terdistribusi secara merata, itu membuktikan kartu jaringan memiliki fitur multi-queue. Jika semuanya terpusat pada satu core, itu berarti semua interupsi jaringan diproses oleh `CPU` ini. Setelah `CPU` ini melebihi `100%`, sistem tidak akan dapat memproses permintaan jaringan. Pada saat ini, perlu menggunakan `cpu_affinity_ignore` untuk mengosongkan `CPU` ini, khusus untuk menangani interupsi jaringan.

Seperti pada gambar di atas, harus diatur `cpu_affinity_ignore => array(0)`

?> Dapat menggunakan perintah `top` `->` masukkan `1`, untuk melihat penggunaan setiap core

* **Catatan**

    !> Opsi ini harus diatur bersamaan dengan `open_cpu_affinity` agar berlaku

### tcp_defer_accept

?> **Mengaktifkan fitur `tcp_defer_accept`**【Default：`false`】

?> Dapat diatur ke nilai numerik, menunjukkan bahwa `accept` hanya dipicu saat koneksi `TCP` memiliki data yang dikirim.

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

* **Tips**

    * **Setelah mengaktifkan fitur `tcp_defer_accept`, waktu yang sesuai untuk `accept` dan [onConnect](/server/events?id=onconnect) akan berubah. Jika diatur ke `5` detik:**

        * Klien terhubung ke server tidak akan segera memicu `accept`
        * Dalam `5` detik, klien mengirim data, saat ini akan memicu `accept/onConnect/onReceive` secara berurutan
        * Dalam `5` detik, klien tidak mengirim data apa pun, saat ini akan memicu `accept/onConnect`

### ssl_cert_file / ssl_key_file :id=ssl_cert_file

?> **Mengatur enkripsi tunnel SSL.**

?> Nilai yang diatur adalah string nama file, menentukan path cert certificate dan key private key.

* **Tips**

    * **Konversi format `PEM` ke `DER`**

    ```shell
    openssl x509 -in cert.crt -outform der -out cert.der
    ```

    * **Konversi format `DER` ke `PEM`**

    ```shell
    openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
    ```

* **Catatan**

    !> -Aplikasi `HTTPS` browser harus mempercayai certificate untuk dapat browsing web;  
    -Dalam aplikasi `wss`, halaman yang memulai koneksi `WebSocket` harus menggunakan `HTTPS`;  
    -Jika browser tidak mempercayai `SSL` certificate, tidak dapat menggunakan `wss`;  
    -File harus dalam format `PEM`, tidak mendukung format `DER`, dapat menggunakan alat `openssl` untuk konversi.

    !> Menggunakan `SSL` harus menambahkan opsi [--enable-openssl](/environment?id=opsi-kompilasi) saat mengkompilasi `Swoole`

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```

### ssl_method

!> Parameter ini telah dihapus di versi [v4.5.4](/version/bc?id=_454), gunakan `ssl_protocols`

?> **Mengatur algoritma enkripsi tunnel OpenSSL.**【Default：`SWOOLE_SSLv23_METHOD`], jenis yang didukung lihat [Metode Enkripsi SSL](/consts?id=metode-enkripsi-ssl)

?> Algoritma yang digunakan `Server` dan `Client` harus konsisten, jika tidak, handshake `SSL/TLS` akan gagal, koneksi akan diputus.

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```

### ssl_protocols

?> **Mengatur protokol enkripsi tunnel OpenSSL.**【Default：`0`, mendukung semua protokol], jenis yang didukung lihat [Protokol SSL](/consts?id=protokol-ssl)

!> Tersedia di Swoole versi >= `v4.5.4`

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```

### ssl_sni_certs

?> **Mengatur certificate SNI (Server Name Identification)**

!> Tersedia di Swoole versi >= `v4.6.0`

```php
$server->set([
    'ssl_cert_file' => __DIR__ . '/server.crt',
    'ssl_key_file' => __DIR__ . '/server.key',
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'ssl_sni_certs' => [
        'cs.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_cs_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_cs_key.pem',
        ],
        'uk.php.net' => [
            'ssl_cert_file' =>  __DIR__ . '/sni_server_uk_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_uk_key.pem',
        ],
        'us.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_us_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_us_key.pem',
        ],
    ]
]);
```

### ssl_ciphers

?> **Mengatur algoritma enkripsi openssl.**【Default：`EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

* **Tips**

    * Jika `ssl_ciphers` diatur ke string kosong, `openssl` akan memilih algoritma enkripsi sendiri

### ssl_verify_peer

?> **Pengaturan SSL server untuk memverifikasi certificate lawan.**【Default：`false`】

?> Default dimatikan, yaitu tidak memverifikasi certificate klien. Jika diaktifkan, harus mengatur opsi `ssl_client_cert_file` secara bersamaan.

### ssl_allow_self_signed

?> **Mengizinkan self-signed certificate.**【Default：`false`】

### ssl_client_cert_file

?> **Root certificate, digunakan untuk memverifikasi certificate klien.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set(array(
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,
    'ssl_allow_self_signed' => true,
    'ssl_client_cert_file'  => __DIR__ . '/config/ca.crt',
));
```

!> Jika verifikasi `TCP` gagal, level bawah akan secara otomatis menutup koneksi.

### ssl_compress

?> **Mengatur apakah akan mengaktifkan kompresi `SSL/TLS`.** Saat digunakan di [Co\Client](/coroutine_client/client), memiliki alias `ssl_disable_compression`

### ssl_verify_depth

?> **Jika rantai certificate terlalu dalam, melebihi nilai yang ditetapkan opsi ini, maka verifikasi akan dihentikan.**

### ssl_prefer_server_ciphers

?> **Mengaktifkan perlindungan sisi server, mencegah serangan BEAST.**

### ssl_dhparam

?> **Menentukan parameter `Diffie-Hellman` untuk cipher DHE.**

### ssl_ecdh_curve

?> **Menentukan `curve` yang digunakan dalam pertukaran kunci ECDH.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_compress'                => true,
    'ssl_verify_depth'            => 10,
    'ssl_prefer_server_ciphers'   => true,
    'ssl_dhparam'                 => '',
    'ssl_ecdh_curve'              => '',
]);
```

### user

?> **Mengatur pengguna yang memiliki proses `Worker/TaskWorker`.**【Default：pengguna yang menjalankan script】

?> Jika server perlu mendengarkan port di bawah `1024`, harus memiliki hak `root`. Tetapi jika program berjalan di bawah pengguna `root`, begitu ada celah dalam kode, penyerang dapat menjalankan perintah jarak jauh sebagai `root`, risikonya besar. Setelah mengonfigurasi item `user`, proses utama dapat berjalan dengan hak `root`, sementara proses anak berjalan dengan hak pengguna biasa.

```php
$server->set(array(
  'user' => 'Apache'
));
```

* **Catatan**

    !> -Hanya efektif saat dijalankan dengan pengguna `root`  
    -Setelah menggunakan item konfigurasi `user/group` untuk mengatur proses kerja sebagai pengguna biasa, tidak akan dapat memanggil method `shutdown`/[reload](/server/methods?id=reload) dalam proses kerja untuk menutup atau me-restart layanan. Hanya dapat menggunakan akun `root` di terminal `shell` untuk menjalankan perintah `kill`.

### group

?> **Mengatur grup pengguna proses `Worker/TaskWorker`.**【Default：grup pengguna yang menjalankan script】

?> Sama seperti konfigurasi `user`, konfigurasi ini mengubah grup pengguna proses, meningkatkan keamanan program server.

```php
$server->set(array(
  'group' => 'www-data'
));
```

* **Catatan**

    !> Hanya efektif saat dijalankan dengan pengguna `root`

### chroot

?> **Mengarahkan ulang root filesystem proses `Worker`.**

?> Pengaturan ini dapat mengisolasi proses baca/tulis filesystem dari filesystem sistem operasi yang sebenarnya. Meningkatkan keamanan.

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```

### pid_file

?> **Mengatur alamat file pid.**

?> Saat `Server` dimulai, secara otomatis menulis `PID` proses `master` ke file. Saat `Server` ditutup, secara otomatis menghapus file `PID`.

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

* **Catatan**

    !> Perlu diperhatikan bahwa jika `Server` berakhir tidak normal, file `PID` tidak akan dihapus. Perlu menggunakan [Swoole\Process::kill($pid, 0)](/process/process?id=kill) untuk mendeteksi apakah proses benar-benar ada.

### buffer_input_size / input_buffer_size :id=buffer_input_size

?> **Mengatur ukuran memori buffer input.**【Default：`2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```

### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **Mengatur ukuran memori buffer output pengiriman.**【Default：`2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, //harus berupa angka
]);
```

* **Tips**

    !> Swoole versi >= `v4.6.7`, nilai default adalah nilai maksimum unsigned INT `UINT_MAX`

    * Satuan byte, default `2M`, misalnya mengatur `32 * 1024 * 1024` berarti satu kali `Server->send` maksimum diizinkan mengirim `32M` byte data
    * Saat memanggil `Server->send`, `Http\Server->end/write`, `WebSocket\Server->push` dan instruksi pengiriman data lainnya, data maksimum yang dikirim `sekali` tidak boleh melebihi konfigurasi `buffer_output_size`.

    !> Parameter ini hanya berlaku untuk mode [SWOOLE_PROCESS](/learn?id=swoole_process), karena dalam mode PROCESS, data proses Worker harus dikirim ke proses utama kemudian ke klien, sehingga setiap proses Worker dan proses utama membuka buffer. [Lihat](/learn?id=reactor-thread)

### socket_buffer_size

?> **Mengatur panjang buffer koneksi klien.**【Default：`2M`】

?> Berbeda dengan `buffer_output_size`, `buffer_output_size` adalah batasan ukuran `sekali` send dari proses worker. `socket_buffer_size` digunakan untuk mengatur total ukuran buffer komunikasi antara proses `Worker` dan `Master`, lihat mode [SWOOLE_PROCESS](/learn?id=swoole_process).

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, //harus berupa angka, satuan byte, misalnya 128 * 1024 *1024 berarti setiap koneksi klien TCP maksimum diizinkan memiliki 128M data yang akan dikirim
]);
```

- **Buffer Pengiriman Data**

    - Saat proses Master mengirim data dalam jumlah besar ke klien, data tidak dapat segera dikirim. Saat ini, data yang dikirim akan disimpan di buffer memori sisi server. Parameter ini dapat menyesuaikan ukuran buffer memori.

    - Jika data yang dikirim terlalu banyak, setelah buffer penuh, `Server` akan melaporkan informasi error berikut:

    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```

    ?> Kegagalan `send` akibat buffer penuh hanya memengaruhi klien saat ini, klien lain tidak terpengaruh.  
    Saat server memiliki banyak koneksi `TCP`, dalam kondisi terburuk, akan memakan memori sebesar `serv->max_connection * socket_buffer_size` byte.

    - Terutama program server komunikasi eksternal, komunikasi jaringan lambat. Jika terus-menerus mengirim data, buffer akan cepat penuh. Data yang dikirim akan menumpuk di memori `Server`. Oleh karena itu, aplikasi semacam ini harus mempertimbangkan kapasitas transmisi jaringan dari segi desain, simpan pesan ke disk terlebih dahulu, dan kirim data baru setelah klien memberi tahu server bahwa penerimaan selesai.

    - Seperti layanan live streaming video, bandwidth pengguna `A` adalah `100M`, mengirim `10M` data dalam `1` detik sangat mungkin. Bandwidth pengguna `B` hanya `1M`, jika mengirim `10M` data dalam `1` detik, pengguna `B` mungkin memerlukan `100` detik untuk menerima selesai. Pada saat ini, semua data akan menumpuk di memori server.

    - Dapat melakukan pemrosesan yang berbeda berdasarkan jenis konten data. Jika konten dapat dibuang, seperti layanan live streaming video, membuang beberapa frame data dalam kondisi jaringan buruk sepenuhnya bisa diterima. Jika konten tidak boleh hilang, seperti pesan WeChat, dapat disimpan ke disk server terlebih dahulu, dengan grup `100` pesan. Setelah pengguna menerima grup pesan ini, ambil grup pesan berikutnya dari disk dan kirim ke klien.

### enable_unsafe_event

?> **Mengaktifkan event `onConnect/onClose`.**【Default：`false`】

?> Setelah `Swoole` mengkonfigurasi [dispatch_mode](/server/setting?id=dispatch_mode)=1 atau `3`, karena sistem tidak dapat menjamin urutan `onConnect/onReceive/onClose`, event `onConnect/onClose` dimatikan secara default.  
Jika aplikasi memerlukan event `onConnect/onClose`, dan dapat menerima risiko keamanan yang mungkin ditimbulkan oleh masalah urutan, dapat mengatur `enable_unsafe_event` menjadi `true` untuk mengaktifkan event `onConnect/onClose`.

### discard_timeout_request

?> **Membuang permintaan data dari koneksi yang sudah ditutup.**【Default：`true`】

?> Setelah `Swoole` mengkonfigurasi [dispatch_mode](/server/setting?id=dispatch_mode)=`1` atau `3`, sistem tidak dapat menjamin urutan `onConnect/onReceive/onClose`, sehingga mungkin ada beberapa data permintaan yang sampai ke proses `Worker` setelah koneksi ditutup.

* **Tips**

    * Konfigurasi `discard_timeout_request` default `true`, berarti jika proses `worker` menerima data permintaan dari koneksi yang sudah ditutup, akan otomatis dibuang.
    * Jika `discard_timeout_request` diatur ke `false`, berarti terlepas dari apakah koneksi ditutup, proses `Worker` akan tetap memproses permintaan data.

### enable_reuse_port

?> **Mengatur reuse port.**【Default：`false`】

?> Setelah mengaktifkan reuse port, dapat memulai ulang program Server yang mendengarkan port yang sama.

* **Tips**

    * `enable_reuse_port = true` mengaktifkan reuse port
    * `enable_reuse_port = false` menonaktifkan reuse port

!> Hanya tersedia di kernel `Linux-3.9.0` atau yang lebih baru, dan `Swoole4.5` atau yang lebih baru.

### enable_delay_receive

?> **Mengatur agar setelah `accept` koneksi klien, tidak akan otomatis bergabung ke [EventLoop](/learn?id=apa-itu-eventloop).**【Default：`false`】

?> Setelah mengatur opsi ini ke `true`, setelah `accept` koneksi klien, tidak akan otomatis bergabung ke [EventLoop](/learn?id=apa-itu-eventloop), hanya memicu callback [onConnect](/server/events?id=onconnect). Proses `worker` dapat memanggil [$server->confirm($fd)](/server/methods?id=confirm) untuk mengonfirmasi koneksi. Saat itu, `fd` akan ditambahkan ke [EventLoop](/learn?id=apa-itu-eventloop) untuk mulai melakukan pengiriman dan penerimaan data. Juga dapat memanggil `$server->close($fd)` untuk menutup koneksi ini.

```php
//mengaktifkan opsi enable_delay_receive
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        //mengonfirmasi koneksi, mulai menerima data
        $server->confirm($fd);
    });
});
```

### reload_async

?> **Mengatur sakelar restart asynchronous.**【Default：`true`】

?> Mengatur sakelar restart asynchronous. Jika diatur ke `true`, fitur restart aman asynchronous akan diaktifkan. Proses `Worker` akan menunggu event asynchronous selesai sebelum keluar. Informasi detail lihat [Cara me-restart service dengan benar](/question/use?id=cara-me-restart-service-swoole-dengan-benar)

?> Tujuan utama mengaktifkan `reload_async` adalah untuk memastikan bahwa coroutine atau tugas asynchronous dapat berakhir secara normal saat service di-reload.

```php
$server->set([
  'reload_async' => true
]);
```

* **Mode Coroutine**

    * Di versi `4.x`, saat [enable_coroutine](/server/setting?id=enable_coroutine) diaktifkan, level bawah akan menambahkan deteksi jumlah coroutine. Saat tidak ada coroutine sama sekali, proses baru akan keluar. Saat diaktifkan, meskipun `reload_async => false`, `reload_async` akan tetap dipaksa aktif.

### max_wait_time

?> **Mengatur waktu tunggu maksimum setelah proses `Worker` menerima notifikasi penghentian service**【Default：`3`】

?> Sering ditemui masalah karena `worker` terblokir sehingga `worker` tidak dapat `reload` secara normal, tidak dapat memenuhi beberapa skenario produksi, seperti merilis pembaruan kode panas yang perlu `reload` proses. Oleh karena itu, Swoole menambahkan opsi waktu timeout restart proses. Informasi detail lihat [Cara me-restart service dengan benar](/question/use?id=cara-me-restart-service-swoole-dengan-benar)

* **Tips**

    * **Setelah proses manajemen menerima sinyal restart/tutup atau mencapai `max_request`, proses manajemen akan me-restart proses `worker`. Terdiri dari beberapa langkah berikut:**

        * Level bawah akan menambahkan timer (`max_wait_time`) detik. Setelah timer terpicu, periksa apakah proses masih ada. Jika ya, akan dipaksa dimatikan, dan process baru akan diambil.
        * Perlu melakukan pekerjaan penutupan di callback `onWorkerStop`, harus selesai dalam `max_wait_time` detik.
        * Kirim sinyal `SIGTERM` ke proses target secara berurutan, matikan proses.

* **Catatan**

    !> Sebelum `v4.4.x`, default adalah `30` detik

### tcp_fastopen

?> **Mengaktifkan fitur TCP fast open.**【Default：`false`】

?> Fitur ini dapat meningkatkan kecepatan respons koneksi pendek `TCP`. Saat klien menyelesaikan langkah ketiga handshake, mengirim paket `SYN` sambil membawa data.

```php
$server->set([
  'tcp_fastopen' => true
]);
```

* **Tips**

    * Parameter ini dapat diatur ke port listening. Untuk pemahaman lebih dalam, lihat [google paper](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf)

### request_slowlog_file

?> **Mengaktifkan log permintaan lambat.** Mulai dari versi `v4.4.8` [telah dihapus](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366)

!> Karena solusi slow log ini hanya efektif dalam proses blocking synchronous, tidak dapat digunakan di lingkungan coroutine. Sementara Swoole4 default mengaktifkan coroutine, kecuali menonaktifkan `enable_coroutine`. Jadi jangan gunakan ini, gunakan alat deteksi blocking [Swoole Tracker](https://business.swoole.com/tracker/index).

?> Setelah diaktifkan, proses `Manager` akan mengatur sinyal clock, secara berkala mendeteksi semua proses `Task` dan `Worker`. Jika proses terblokir menyebabkan permintaan melebihi waktu yang ditentukan, akan secara otomatis mencetak stack panggilan fungsi `PHP` dari proses.

?> Level bawah diimplementasikan berdasarkan panggilan sistem `ptrace`. Beberapa sistem mungkin menonaktifkan `ptrace`, sehingga tidak dapat melacak permintaan lambat. Pastikan parameter kernel `kernel.yama.ptrace_scope` adalah `0`.

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

* **Waktu Timeout**

```php
$server->set([
    'request_slowlog_timeout' => 2, // mengatur waktu timeout permintaan menjadi 2 detik
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

!> File harus memiliki izin writable, jika tidak, level bawah akan melempar fatal error saat pembuatan file gagal.

### enable_coroutine

?> **Apakah akan mengaktifkan dukungan coroutine untuk server gaya asynchronous**

?> Saat `enable_coroutine` dimatikan, coroutine tidak akan dibuat secara otomatis dalam [fungsi callback event](/server/events). Jika tidak perlu menggunakan coroutine, mematikan ini akan meningkatkan kinerja. Lihat [Apa itu Swoole Coroutine](/coroutine).

* **Metode Konfigurasi**

    * Di `php.ini` konfigurasi `swoole.enable_coroutine = 'Off'` (lihat [dokumentasi ini](/other/config.md))
    * `$server->set(['enable_coroutine' => false]);` prioritasnya lebih tinggi dari ini

* **Lingkup Pengaruh Opsi `enable_coroutine`**

    * onWorkerStart
    * onConnect
    * onOpen
    * onReceive
    * [setHandler](/redis_server?id=sethandler)
    * onPacket
    * onRequest
    * onMessage
    * onPipeMessage
    * onFinish
    * onClose
    * tick/after timer

!> Setelah mengaktifkan `enable_coroutine`, coroutine akan dibuat secara otomatis dalam fungsi callback di atas.

* Saat `enable_coroutine` diatur ke `true`, level bawah secara otomatis membuat coroutine dalam callback [onRequest](/http_server?id=on). Pengembang tidak perlu menggunakan fungsi `go` sendiri untuk [membuat coroutine](/coroutine/coroutine?id=create).
* Saat `enable_coroutine` diatur ke `false`, level bawah tidak akan membuat coroutine secara otomatis. Jika pengembang ingin menggunakan coroutine, harus menggunakan `go` sendiri untuk membuat coroutine. Jika tidak perlu menggunakan fitur coroutine, cara pemrosesannya 100% konsisten dengan `Swoole1.x`.
* Perhatikan, mengaktifkan ini hanya berarti Swoole akan memproses permintaan melalui coroutine. Jika event berisi fungsi blocking, perlu mengonfigurasi `hook_flags` terlebih dahulu atau mengaktifkan [one-click coroutine](/runtime), untuk mengaktifkan coroutine pada fungsi blocking seperti `sleep`, `mysqlnd` atau ekstensi.

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    //menonaktifkan coroutine bawaan
    'enable_coroutine' => false,
]);

$server->on("request", function ($request, $response) {
    if ($request->server['request_uri'] == '/coro') {
        go(function () use ($response) {
            co::sleep(0.2);
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });
    } else {
        $response->header("Content-Type", "text/plain");
        $response->end("Hello World\n");
    }
});

$server->start();
```

### hook_flags

?> **Mengatur ruang lingkup fungsi Hook `one-click coroutine`.**【Default：tidak hook】

!> Tersedia di Swoole versi `v4.5+` atau [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x). Detail lihat [One-click Coroutine](/runtime)

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```

Level bawah mendukung item coroutine berikut, dapat menggunakan `SWOOLE_HOOK_ALL` untuk coroutine semua:

* `SWOOLE_HOOK_TCP`
* `SWOOLE_HOOK_UNIX`
* `SWOOLE_HOOK_UDP`
* `SWOOLE_HOOK_UDG`
* `SWOOLE_HOOK_SSL`
* `SWOOLE_HOOK_TLS`
* `SWOOLE_HOOK_SLEEP`
* `SWOOLE_HOOK_FILE`
* `SWOOLE_HOOK_STREAM_FUNCTION`
* `SWOOLE_HOOK_BLOCKING_FUNCTION`
* `SWOOLE_HOOK_PROC`
* `SWOOLE_HOOK_CURL`
* `SWOOLE_HOOK_NATIVE_CURL`
* `SWOOLE_HOOK_SOCKETS`
* `SWOOLE_HOOK_STDIO`
* `SWOOLE_HOOK_PDO_PGSQL`
* `SWOOLE_HOOK_PDO_ODBC`
* `SWOOLE_HOOK_PDO_ORACLE`
* `SWOOLE_HOOK_PDO_SQLITE`
* `SWOOLE_HOOK_ALL`

### send_yield

?> **Saat memori buffer tidak mencukupi saat mengirim data, langsung [yield](/coroutine?id=penjadwalan-coroutine) dalam coroutine saat ini, menunggu pengiriman data selesai. Saat buffer kosong, secara otomatis [resume](/coroutine?id=penjadwalan-coroutine) coroutine saat ini, melanjutkan `send` data.**【Default: tersedia saat [dispatch_mode](/server/setting?id=dispatch_mode) 2/4, dan default aktif】

* Jika `Server/Client->send` mengembalikan `false` dan kode error adalah `SW_ERROR_OUTPUT_BUFFER_OVERFLOW`, tidak mengembalikan `false` ke lapisan `PHP`, tetapi [yield](/coroutine?id=penjadwalan-coroutine) untuk menangguhkan coroutine saat ini.
* `Server/Client` memonitor apakah event buffer kosong. Setelah event ini terpicu, data dalam buffer telah dikirim selesai. Pada saat ini, [resume](/coroutine?id=penjadwalan-coroutine) coroutine yang sesuai.
* Setelah coroutine pulih, lanjutkan memanggil `Server/Client->send` untuk menulis data ke buffer. Karena buffer sudah kosong, pengiriman pasti berhasil.

Sebelum perbaikan

```php
for ($i = 0; $i < 100; $i++) {
    //Saat buffer penuh, akan langsung mengembalikan false, dan melaporkan error output buffer overflow
    $server->send($fd, $data_2m);
}
```

Setelah perbaikan

```php
for ($i = 0; $i < 100; $i++) {
    //Saat buffer penuh, akan yield coroutine saat ini, setelah pengiriman selesai resume untuk melanjutkan eksekusi
    $server->send($fd, $data_2m);
}
```

!> Fitur ini akan mengubah perilaku default level bawah, dapat dimatikan secara manual.

```php
$server->set([
    'send_yield' => false,
]);
```

* **Lingkup Pengaruh**

    * [Swoole\Server::send](/server/methods?id=send)
    * [Swoole\Http\Response::write](/http_server?id=write)
    * [Swoole\WebSocket\Server::push](/websocket_server?id=push)
    * [Swoole\Coroutine\Client::send](/coroutine_client/client?id=send)
    * [Swoole\Coroutine\Http\Client::push](/coroutine_client/http_client?id=push)

### send_timeout

Mengatur timeout pengiriman, digunakan bersama dengan `send_yield`. Jika data tidak berhasil dikirim ke buffer dalam waktu yang ditentukan, level bawah mengembalikan `false`, dan mengatur kode error menjadi `ETIMEDOUT`. Kode error dapat diperoleh dengan method [getLastError()](/server/methods?id=getlasterror).

> Tipe float, satuan detik, granularitas minimal milidetik

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1.5 detik
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false and $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "Timeout pengiriman\n";
    }
}
```

### buffer_high_watermark

?> **Mengatur batas atas buffer, dalam satuan byte.**

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```

### buffer_low_watermark

?> **Mengatur batas bawah buffer, dalam satuan byte.**

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```

### tcp_user_timeout

?> Opsi TCP_USER_TIMEOUT adalah opsi socket level TCP. Nilainya adalah waktu maksimum setelah paket data dikirim tanpa menerima konfirmasi ACK, dalam satuan milidetik. Silakan lihat dokumentasi man untuk detailnya.

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10 detik
]);
```

!> Tersedia di Swoole versi >= `v4.5.3-alpha`

### stats_file

?> **Menentukan path file untuk menulis konten [stats()](/server/methods?id=stats). Setelah diatur, secara otomatis akan mengatur timer di [onWorkerStart](/server/events?id=onworkerstart) untuk secara berkala menulis konten [stats()](/server/methods?id=stats) ke file yang ditentukan.**

```php
$server->set([
    'stats_file' => __DIR__ . '/stats.log',
]);
```

!> Tersedia di Swoole versi >= `v4.5.5`

### event_object

?> **Setelah mengatur opsi ini, callback event akan menggunakan [gaya objek](/server/events?id=callback-object).**【Default：`false`】

```php
$server->set([
    'event_object' => true,
]);
```

!> Tersedia di Swoole versi >= `v4.6.0`

### start_session_id

?> **Mengatur session ID awal**

```php
$server->set([
    'start_session_id' => 10,
]);
```

!> Tersedia di Swoole versi >= `v4.6.0`

### single_thread

?> **Mengatur ke thread tunggal.** Setelah diaktifkan, thread Reactor akan bergabung dengan thread Master dalam proses Master, diproses oleh thread Master. Di PHP ZTS, jika menggunakan mode `SWOOLE_PROCESS`, pasti perlu mengatur nilai ini ke `true`.

```php
$server->set([
    'single_thread' => true,
]);
```

!> Tersedia di Swoole versi >= `v4.2.13`

### max_queued_bytes

?> **Mengatur panjang maksimum antrian buffer penerima.** Jika melebihi, akan berhenti menerima.

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Tersedia di Swoole versi >= `v4.5.0`

### admin_server

?> **Mengatur layanan admin_server, digunakan untuk melihat informasi layanan di [Swoole Dashboard](http://dashboard.swoole.com/).**

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Tersedia di Swoole versi >= `v4.8.0`

### bootstrap

?> **File entry program dalam mode multi-thread, default adalah nama file script yang sedang dijalankan.**

!> Tersedia di Swoole versi >= `v6.0`, `PHP` dalam mode `ZTS`, kompilasi `Swoole` dengan `--enable-swoole-thread`

```php
$server->set([
    'bootstrap' => __FILE__,
]);
```

### init_arguments

?> **Mengatur data berbagi data multi-thread. Konfigurasi ini memerlukan fungsi callback. Server akan secara otomatis menjalankan fungsi ini saat startup.**

!> Swoole memiliki banyak container thread-safe, [Concurrent Map](/thread/map), [Concurrent List](/thread/arraylist), [Concurrent Queue](/thread/queue). Jangan mengembalikan variabel yang tidak aman dalam fungsi.

!> Tersedia di Swoole versi >= `v6.0`, `PHP` dalam mode `ZTS`, kompilasi `Swoole` dengan `--enable-swoole-thread`

```php
$server->set([
    'init_arguments' => function() { return new Swoole\Thread\Map(); },
]);

$server->on('request', function($request, $response) {
    $map = Swoole\Thread::getArguments();
});
```
