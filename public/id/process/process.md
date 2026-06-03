# Swoole\Process

Modul manajemen proses yang disediakan Swoole, sebagai pengganti `pcntl` PHP

!> Modul ini cukup low-level, merupakan bungkus dari manajemen proses OS. Pengguna harus punya pengalaman pemrograman multi-proses di sistem `Linux`.

`pcntl` bawaan `PHP` punya banyak kekurangan, seperti:

* Tidak menyediakan fungsi komunikasi antar proses
* Tidak mendukung redirect standard input dan output
* Cuma nyediain interface primitif kayak `fork`, gampang salah pakai

`Process` nyediain fungsionalitas yang lebih kuat dari `pcntl`, dengan `API` yang lebih gampang dipake, bikin PHP lebih ringan dalam pemrograman multi-proses.

`Process` punya fitur-fitur berikut:

* Bisa dengan mudah mengimplementasikan komunikasi antar proses
* Mendukung redirect standard input dan output, di child process `echo` nggak bakal cetak ke layar, tapi nulis ke pipe. Baca input keyboard bisa dialihkan jadi baca data dari pipe
* Nyediain interface [exec](/process/process?id=exec), proses yang dibuat bisa jalanin program lain, dan gampang komunikasi dengan parent process `PHP`
* Di lingkungan coroutine, modul `Process` nggak bisa dipake. Bisa pake `runtime hook` + `proc_open`, lihat [Manajemen Proses Coroutine](/coroutine/proc_open)

### Contoh Penggunaan

  * Buat 3 child process, main process pake wait buat membersihkan
  * Kalo main process exit abnormal, child process tetep jalan sampe selesai semua tugas baru exit

```php
use Swoole\Process;

for ($n = 1; $n <= 3; $n++) {
    $process = new Process(function () use ($n) {
        echo 'Child #' . getmypid() . " start and sleep {$n}s" . PHP_EOL;
        sleep($n);
        echo 'Child #' . getmypid() . ' exit' . PHP_EOL;
    });
    $process->start();
}
for ($n = 3; $n--;) {
    $status = Process::wait(true);
    echo "Recycled #{$status['pid']}, code={$status['code']}, signal={$status['signal']}" . PHP_EOL;
}
echo 'Parent #' . getmypid() . ' exit' . PHP_EOL;
```

## Properti

### pipe

Deskriptor file [unixSocket](/learn?id=apa-itu-IPC).

```php
public int $pipe;
```

### msgQueueId

`id` dari message queue.

```php
public int $msgQueueId;
```

### msgQueueKey

`key` dari message queue.

```php
public string $msgQueueKey;
```

### pid

`pid` dari proses saat ini.

```php
public int $pid;
```

### id

`id` dari proses saat ini.

```php
public int $id;
```

## Konstanta
Parameter | Fungsi
---|---
Swoole\Process::IPC_NOWAIT | Langsung balik kalo message queue kosong
Swoole\Process::PIPE_READ | Tutup read socket
Swoole\Process::PIPE_WRITE | Tutup write socket

## Method

### __construct()

Method konstruktor.

```php
Swoole\Process->__construct(callable $function, bool $redirect_stdin_stdout = false, int $pipe_type = SOCK_DGRAM, bool $enable_coroutine = false)
```

* **Parameter**

  * **`callable $function`**
    * **Fungsi**: Fungsi yang dijalankan setelah child process berhasil dibuat【level bawah otomatis simpen fungsi ke properti `callback` object】, catat, properti ini `private`.
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`bool $redirect_stdin_stdout`**
    * **Fungsi**: Redirect standard input dan output child process.【Kalo diaktifkan, output di child process nggak dicetak ke layar, tapi ditulis ke pipe main process. Baca input keyboard jadi baca data dari pipe. Default blocking read. Lihat [exec()](/process/process?id=exec)】
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`int $pipe_type`**
    * **Fungsi**: Tipe [unixSocket](/learn?id=apa-itu-IPC)【Kalo `$redirect_stdin_stdout` diaktifkan, opsi ini akan abaikan parameter user, paksa jadi `SOCK_STREAM`. Kalo di child process nggak ada komunikasi antar proses, bisa diset `0`】
    * **Default**: `SOCK_DGRAM`
    * **Nilai lain**: `0`, `SOCK_STREAM`

  * **`bool $enable_coroutine`**
    * **Fungsi**: Aktifkan coroutine di `callback function`. Kalo diaktifkan, bisa langsung pake API coroutine di fungsi child process
    * **Default**: `false`
    * **Nilai lain**: `true`
    * **Versi**: Swoole >= v4.3.0

* **Tipe [unixSocket](/learn?id=apa-itu-IPC)**

Tipe unixSocket | Keterangan
---|---
0 | Jangan dibuat
1 | Buat unixSocket tipe [SOCK_STREAM](/learn?id=apa-itu-IPC)
2 | Buat unixSocket tipe [SOCK_DGRAM](/learn?id=apa-itu-IPC)


### useQueue()

Pake message queue untuk komunikasi antar proses.

```php
Swoole\Process->useQueue(int $key = 0, int $mode = SWOOLE_MSGQUEUE_BALANCE, int $capacity = -1): bool
```

* **Parameter**

  * **`int $key`**
    * **Fungsi**: Key message queue. Kalo masukin nilai <= 0, level bawah bakal pake fungsi `ftok` dengan nama file yang lagi dijalanin sebagai parameter buat generate key.
    * **Default**: `0`
    * **Nilai lain**: tidak ada

  * **`int $mode`**
    * **Fungsi**: Mode komunikasi antar proses,
    * **Default**: `SWOOLE_MSGQUEUE_BALANCE`, `Swoole\Process::pop()` bakal balikin pesan pertama di queue, `Swoole\Process::push()` nggak bakal nambahin tipe tertentu ke pesan.
    * **Nilai lain**: `SWOOLE_MSGQUEUE_ORIENT`, `Swoole\Process::pop()` bakal balikin data spesifik dengan tipe pesan `process id + 1`, `Swoole\Process::push()` bakal nambahin tipe `process id + 1` ke pesan.

  * **`int $capacity`**
    * **Fungsi**: Jumlah maksimum pesan yang bisa disimpan di message queue.
    * **Default**: `-1`
    * **Nilai lain**: tidak ada

* **Catatan**

  * Kalo message queue kosong, `Swoole\Process->pop()` bakal blocking terus, atau kalo message queue penuh, `Swoole\Process->push()` juga bakal blocking terus. Kalo nggak mau blocking, nilai `$mode` harus `SWOOLE_MSGQUEUE_BALANCE|Swoole\Process::IPC_NOWAIT` atau `SWOOLE_MSGQUEUE_ORIENT|Swoole\Process::IPC_NOWAIT`.

### statQueue()

Dapetin status message queue

```php
Swoole\Process->statQueue(): array|false
```

* **Return Value**

  * Balikin array kalo sukses, array berisi dua key-value: `queue_num` jumlah total pesan di queue, `queue_bytes` ukuran total pesan di queue.
  * Gagal balik `false`.

### freeQueue()

Hancurin message queue.

```php
Swoole\Process->freeQueue(): bool
```

* **Return Value**

  * Sukses balik `true`.
  * Gagal balik `false`.

### pop()

Ambil data dari message queue.

```php
Swoole\Process->pop(int $size = 65536): string|false
```

* **Parameter**

  * **`int $size`**
    * **Fungsi**: Ukuran data yang diambil.
    * **Default**: `65536`
    * **Nilai lain**: tidak ada


* **Return Value**

  * Balik `string` kalo sukses.
  * Gagal balik `false`.

* **Catatan**

  * Kalo tipe message queue `SW_MSGQUEUE_BALANCE`, balikin pesan pertama di queue.
  * Kalo tipe message queue `SW_MSGQUEUE_ORIENT`, balikin pesan pertama dengan tipe `process id saat ini + 1`.

### push()

Kirim data ke message queue.

```php
Swoole\Process->push(string $data): bool
```

* **Parameter**

  * **`string $data`**
    * **Fungsi**: Data yang dikirim.
    * **Default**: ``
    * **Nilai lain**: tidak ada


* **Return Value**

  * Balik `true` kalo sukses.
  * Gagal balik `false`.

* **Catatan**

  * Kalo tipe message queue `SW_MSGQUEUE_BALANCE`, data langsung dimasukin ke message queue.
  * Kalo tipe message queue `SW_MSGQUEUE_ORIENT`, data bakal ditambahin tipe, yaitu `process id saat ini + 1`.

### setTimeout()

Set timeout baca/tulis message queue.

```php
Swoole\Process->setTimeout(float $seconds): bool
```

* **Parameter**

  * **`float $seconds`**
    * **Fungsi**: Waktu timeout
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada


* **Return Value**

  * Sukses balik `true`.
  * Gagal balik `false`.

### setBlocking()

Set apakah socket message queue blocking.

```php
Swoole\Process->setBlocking(bool $$blocking): void
```

* **Parameter**

  * **`bool $blocking`**
    * **Fungsi**: Apakah blocking, `true` blocking, `false` non-blocking
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

* **Catatan**

  * Socket proses yang baru dibuat default-nya blocking, jadi pas komunikasi UNIX domain socket, kirim atau baca pesan bakal bikin proses blocking.

### write()

Nulis pesan antar parent dan child process (UNIX domain socket).

```php
Swoole\Process->write(string $data): false|int
```

* **Parameter**

  * **`string $data`**
    * **Fungsi**: Data yang mau ditulis
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada


* **Return Value**

  * Sukses balik `int`, jumlah byte yang berhasil ditulis.
  * Gagal balik `false`.

### read()

Baca pesan antar parent dan child process (UNIX domain socket).

```php
Swoole\Process->read(int $size = 8192): false|string
```

* **Parameter**

  * **`int $size`**
    * **Fungsi**: Ukuran data yang mau dibaca
    * **Default**: `8192`
    * **Nilai lain**: tidak ada


* **Return Value**

  * Sukses balik `string`.
  * Gagal balik `false`.

### set()

Set parameter.

```php
Swoole\Process->set(array $settings): void
```

Bisa pake `enable_coroutine` buat ngontrol apakah mau aktifin coroutine, sama kayak parameter ke-empat konstruktor.

```php
Swoole\Process->set(['enable_coroutine' => true]);
```

!> Tersedia di Swoole >= v4.4.4

### start()

Jalanin system call `fork`, mulai child process. Di sistem `Linux` butuh ratusan mikrodetik buat bikin proses.

```php
Swoole\Process->start(): int|false
```

* **Return Value**

  * Sukses balik `PID` child process
  * Gagal balik `false`. Bisa pake [swoole_errno](/functions?id=swoole_errno) dan [swoole_strerror](/functions?id=swoole_strerror) buat dapetin kode error dan pesan error.

* **Catatan**

  * Child process bakal mewarisi memory dan file handle dari parent process
  * Child process pas mulai bakal bersihin [EventLoop](/learn?id=apa-itu-eventloop), [Signal](/process/process?id=signal), dan [Timer](/timer) yang diwarisi dari parent process

  !> Setelah dijalankan, child process bakal pertahankan memory dan resource parent process. Misalnya kalo di parent process dibuat koneksi redis, di child process object ini bakal dipertahankan, semua operasi pake koneksi yang sama. Contoh di bawah:

```php
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);

function callback_function() {
    swoole_timer_after(1000, function () {
        echo "hello world\n";
    });
    global $redis;//koneksi yang sama
};

swoole_timer_tick(1000, function () {
    echo "parent timer\n";
});//nggak diwarisi

Swoole\Process::signal(SIGCHLD, function ($sig) {
    while ($ret = Swoole\Process::wait(false)) {
        // buat child process baru
        $p = new Swoole\Process('callback_function');
        $p->start();
    }
});

// buat child process baru
$p = new Swoole\Process('callback_function');

$p->start();
```

!> 1. Child process pas mulai otomatis bersihin timer [Swoole\Timer::tick](/timer?id=tick), signal [Process::signal](/process/process?id=signal), dan event listener [Swoole\Event::add](/event?id=add) dari parent process;  
2. Child process bakal mewarisi object koneksi `$redis` yang dibuat parent process, parent dan child pake koneksi yang sama.

### exportSocket()

Export `unixSocket` jadi object `Swoole\Coroutine\Socket`, lalu pake method `Swoole\Coroutine\socket` buat komunikasi antar proses. Detailnya lihat [Coroutine\socket](/coroutine_client/socket) dan [Komunikasi IPC](/learn?id=apa-itu-IPC).

```php
Swoole\Process->exportSocket(): Swoole\Coroutine\Socket|false
```

!> Panggil method ini berkali-kali, object yang dibalikin sama;  
`socket` yang di-export `exportSocket()` adalah `fd` baru, nutup socket yang di-export nggak bakal pengaruh ke pipeline asli proses.  
Karena ini object `Swoole\Coroutine\Socket`, harus dipake di [coroutine container](/coroutine/scheduler), jadi parameter konstruktor Swoole\Process `$enable_coroutine` harus true.  
Parent process juga kalo mau pake object `Swoole\Coroutine\Socket`, perlu manual `Coroutine\run()` buat bikin coroutine container.

* **Return Value**

  * Sukses balik object `Coroutine\Socket`
  * Proses belum bikin unixSocket, operasi gagal, balik `false`

* **Contoh Penggunaan**

Implementasi komunikasi parent-child sederhana:

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    echo $socket->recv();
    $socket->send("hello master\n");
    echo "proc1 stop\n";
}, false, 1, true);

$proc1->start();

//Parent process bikin coroutine container
run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    $socket->send("hello pro1\n");
    var_dump($socket->recv());
});
Process::wait(true);
```

Contoh komunikasi yang lebih kompleks:

```php
use Swoole\Process;
use Swoole\Timer;
use function Swoole\Coroutine\run;

$process = new Process(function ($proc) {
    Timer::tick(1000, function () use ($proc) {
        $socket = $proc->exportSocket();
        $socket->send("hello master\n");
        echo "child timer\n";
    });
}, false, 1, true);

$process->start();

run(function() use ($process) {
    Process::signal(SIGCHLD, static function ($sig) {
        while ($ret = Swoole\Process::wait(false)) {
            /* bersihin, event loop bakal exit */
            Process::signal(SIGCHLD, null);
            Timer::clearAll();
        }
    });
    /* bisa jalanin kode async atau coroutine lain di sini */
    Timer::tick(500, function () {
        echo "parent timer\n";
    });

    $socket = $process->exportSocket();
    while (1) {
        var_dump($socket->recv());
    }
});
```
!> Catat default tipenya `SOCK_STREAM`, perlu handle masalah boundary paket TCP, lihat method `setProtocol()` di [Coroutine\socket](/coroutine_client/socket).

Pake tipe `SOCK_DGRAM` buat komunikasi IPC bisa hindari masalah boundary paket TCP, lihat [Komunikasi IPC](/learn?id=apa-itu-IPC):

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

//Komunikasi IPC, meskipun socket tipe SOCK_DGRAM, nggak perlu pake fungsi sendto/recvfrom, send/recv aja cukup.
$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    while (1) {
        var_dump($socket->send("hello master\n"));
    }
    echo "proc1 stop\n";
}, false, 2, 1);//pipe type diisi 2 yaitu SOCK_DGRAM

$proc1->start();

run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    Swoole\Coroutine::sleep(5);
    var_dump(strlen($socket->recv()));//sekali recv cuma dapet satu string "hello master\n", nggak bakal dapet banyak
});

Process::wait(true);
```

### name()

Ubah nama proses. Fungsi ini adalah alias dari [swoole_set_process_name](/functions?id=swoole_set_process_name).

```php
Swoole\Process->name(string $name): bool
```

!> Setelah `exec` dijalankan, nama proses bakal diatur ulang sama program baru; method `name` harus dipake di callback child process setelah `start`.

### exec()

Jalanin program eksternal. Fungsi ini adalah bungkus dari system call `exec`.

```php
Swoole\Process->exec(string $execfile, array $args);
```

* **Parameter**

  * **`string $execfile`**
    * **Fungsi**: Path absolut file yang bisa dijalankan, contoh `"/usr/bin/python"`
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`array $args`**
    * **Fungsi**: Daftar argumen `exec`【contoh `array('test.py', 123)`, sama kayak `python test.py 123`】
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

Setelah sukses, segmen kode proses saat ini bakal diganti sama program baru. Child process berubah jadi program lain. Parent process dan proses saat ini tetep hubungan parent-child.

Parent process dan proses baru bisa komunikasi lewat standard input output, redirect stdin/stdout harus diaktifkan.

!> `$execfile` harus pake path absolut, kalo nggak bakal error file not found;  
Karena system call `exec` bakal nimpa program saat ini dengan program yang ditentukan, child process perlu baca/tulis standard output buat komunikasi dengan parent process;  
Kalo nggak diset `redirect_stdin_stdout = true`, setelah `exec` dijalankan, child process dan parent process nggak bisa komunikasi.

* **Contoh Penggunaan**

Contoh 1: Bisa pake [Swoole\Server](/server/init) di child process yang dibuat `Swoole\Process`, tapi demi keamanan harus panggil `$worker->exec()` setelah `$process->start`. Kodenya:

```php
$process = new Swoole\Process('callback_function', true);

$pid = $process->start();

function callback_function(Swoole\Process $worker)
{
    $worker->exec('/usr/local/bin/php', array(__DIR__.'/swoole_server.php'));
}

Swoole\Process::wait();
```

Contoh 2: Jalanin program Yii

```php
$process = new \Swoole\Process(function (\Swoole\Process $childProcess) {
    // cara nulis kayak gini nggak didukung
    // $childProcess->exec('/usr/local/bin/php /var/www/project/yii-best-practice/cli/yii t/index -m=123 abc xyz');

    // Bungkus system call exec
    // Path absolut
    // Parameter harus dipisah-pisah di array
    $childProcess->exec('/usr/local/bin/php', ['/var/www/project/yii-best-practice/cli/yii', 't/index', '-m=123', 'abc', 'xyz']); // system call exec
});
$process->start(); // mulai child process
```

Contoh 3: Parent process komunikasi dengan child process `exec` pake standard input output:

```php
// exec - komunikasi pipe dengan proses exec
use Swoole\Process;
use function Swoole\Coroutine\run;

$process = new Process(function (Process $worker) {
    $worker->exec('/bin/echo', ['hello']);
}, true, 1, true); // perlu aktifkan redirect stdin/stdout

$process->start();

run(function() use($process) {
    $socket = $process->exportSocket();
    echo "from exec: " . $socket->recv() . "\n";
});
```

Contoh 4: Jalanin perintah shell

Method `exec` beda sama `shell_exec` yang disediakan `PHP`. Ini lebih low-level, bungkus system call. Kalo perlu jalanin perintah `shell`, pake cara ini:

```php
$worker->exec('/bin/sh', array('-c', "cp -rf /data/test/* /tmp/test/"));
```

### close()

Buat nutup [unixSocket](/learn?id=apa-itu-IPC) yang sudah dibuat.

```php
Swoole\Process->close(int $which): bool
```

* **Parameter**

  * **`int $which`**
    * **Fungsi**: Karena unixSocket full-duplex, tentuin sisi mana yang ditutup【default `0` nutup baca dan tulis, `1`: nutup tulis, `2` nutup baca】
    * **Default**: `0`, nutup read dan write socket.
    * **Nilai lain**: `Swoole/Process::SW_PIPE_CLOSE_READ` nutup read socket, `Swoole/Process::SW_PIPE_CLOSE_WRITE` nutup write socket,

!> Ada kasus khusus di mana object `Process` nggak bisa di-release. Kalo terus-terusan bikin proses, bisa bocor koneksi. Panggil fungsi ini bisa langsung nutup `unixSocket`, release resource.

### exit()

Keluar dari child process.

```php
Swoole\Process->exit(int $status = 0);
```

* **Parameter**

  * **`int $status`**
    * **Fungsi**: Status code keluar proses【Kalo `0` artinya normal, bakal lanjut execute cleanup】
    * **Default**: `0`
    * **Nilai lain**: tidak ada

!> Pekerjaan cleanup meliputi:

  * `shutdown_function` PHP
  * Destructor object (`__destruct`)
  * Fungsi `RSHUTDOWN` ekstensi lain

Kalo `$status` bukan `0`, artinya exit abnormal, proses bakal langsung dihentikan, nggak jalanin cleanup terkait.

Di parent process, jalanin `Process::wait` bisa dapet event exit dan status code child process.

### kill()

Kirim sinyal ke proses dengan `pid` tertentu.

```php
Swoole\Process::kill(int $pid, int $signo = SIGTERM): bool
```

* **Parameter**

  * **`int $pid`**
    * **Fungsi**: Process `pid`
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`int $signo`**
    * **Fungsi**: Sinyal yang dikirim【`$signo=0`, bisa detek apakah proses ada, nggak kirim sinyal】
    * **Default**: `SIGTERM`
    * **Nilai lain**: tidak ada

### signal()

Set mendengarkan sinyal asynchronous.

```php
Swoole\Process::signal(int $signo, callable $callback): bool
```

Method ini berdasarkan `signalfd` dan [EventLoop](/learn?id=apa-itu-eventloop) yang merupakan `IO` asynchronous, nggak bisa dipake di program blocking, karena callback yang didaftar nggak bakal dijadwal;

Program synchronous blocking bisa pake `pcntl_signal` dari ekstensi `pcntl`;

Kalo sinyal ini udah diset callback-nya, set ulang bakal timpa history.

* **Parameter**

  * **`int $signo`**
    * **Fungsi**: Sinyal
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`callable $callback`**
    * **Fungsi**: Callback function【Kalo `$callback` `null`, hapus mendengarkan sinyal】
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

!> Di [Swoole\Server](/server/init) ada sinyal tertentu yang nggak bisa diset mendengarkan, kayak `SIGTERM` dan `SIGALAM`

* **Contoh Penggunaan**

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
```

!> Di versi `v4.4.0`, kalo [EventLoop](/learn?id=apa-itu-eventloop) proses cuma punya event mendengarkan sinyal, tanpa event lain (misalnya Timer), proses bakal langsung exit.

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
Swoole\Event::wait();
```

Program di atas nggak bakal masuk [EventLoop](/learn?id=apa-itu-eventloop), `Swoole\Event::wait()` bakal langsung balik, dan exit proses.

### wait()

Membersihkan child process yang udah selesai jalan.

!> Swoole >= `v4.5.0` disarankan pake versi coroutine `wait()`, lihat [Swoole\Coroutine\System::wait()](/coroutine/system?id=wait)

```php
Swoole\Process::wait(bool $blocking = true): array|false
```

* **Parameter**

  * **`bool $blocking`**
    * **Fungsi**: Apakah blocking wait【default blocking】
    * **Default**: `true`
    * **Nilai lain**: `false`

* **Return Value**

  * Sukses balik array berisi `PID` child process, status code exit, sinyal `KILL`
  * Gagal balik `false`

!> Setiap child process selesai, parent process harus panggil `wait()` buat membersihkan, kalo nggak child process bakal jadi zombie process, boros resource proses OS.  
Kalo parent process punya tugas lain dan nggak bisa blocking di `wait`, parent process harus daftar sinyal `SIGCHLD` buat `wait` proses yang exit.  
Pas sinyal SIGCHLD terjadi, mungkin ada banyak child process yang exit barengan; `wait()` harus diset non-blocking, loop `wait` sampe balik `false`.

* **Contoh**

```php
Swoole\Process::signal(SIGCHLD, function ($sig) {
    // harus false, mode non-blocking
    while ($ret = Swoole\Process::wait(false)) {
        echo "PID={$ret['pid']}\n";
    }
});
```

### daemon()

Ubah proses saat ini jadi daemon.

```php
Swoole\Process::daemon(bool $nochdir = true, bool $noclose = true): bool
```

* **Parameter**

  * **`bool $nochdir`**
    * **Fungsi**: Apakah pindah direktori saat ini ke root【`true` berarti jangan pindah direktori】
    * **Default**: `true`
    * **Nilai lain**: `false`

  * **`bool $noclose`**
    * **Fungsi**: Apakah nutup file descriptor stdin/stdout【`true` berarti jangan nutup】
    * **Default**: `true`
    * **Nilai lain**: `false`

!> Pas berubah jadi daemon, `PID` proses bakal berubah. Bisa pake `getmypid()` buat dapetin `PID` saat ini

### alarm()

Timer presisi tinggi, bungkus system call `setitimer` OS, bisa set timer level mikrodetik. Timer bakal picu sinyal, harus dipake bareng [Process::signal](/process/process?id=signal) atau `pcntl_signal`.

!> `alarm` nggak bisa dipake bareng [Timer](/timer)

```php
Swoole\Process->alarm(int $time, int $type = 0): bool
```

* **Parameter**

  * **`int $time`**
    * **Fungsi**: Interval timer【kalo negatif, hapus timer】
    * **Satuan**: Mikrodetik
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`int $type`**
    * **Fungsi**: Tipe timer
    * **Default**: `0`
    * **Nilai lain**:

Tipe Timer | Keterangan
---|---
0 | Waktu real, picu sinyal `SIGALAM`
1 | CPU time user mode, picu sinyal `SIGVTALAM`
2 | CPU time user + kernel mode, picu sinyal `SIGPROF`

* **Return Value**

  * Set sukses balik `true`
  * Gagal balik `false`, bisa pake `swoole_errno` dapetin kode error

* **Contoh Penggunaan**

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

run(function () {
    Process::signal(SIGALRM, function () {
        static $i = 0;
        echo "#{$i}\talarm\n";
        $i++;
        if ($i > 20) {
            Process::alarm(-1);
            Process::kill(getmypid());
        }
    });

    //100ms
    Process::alarm(100 * 1000);

    while(true) {
        sleep(0.5);
    }
});
```

### setAffinity()

Set afinitas `CPU`, bisa bind proses ke core `CPU` tertentu.

Fungsi ini biar proses cuma jalan di beberapa core `CPU`, kasih resource `CPU` tertentu buat program yang lebih penting.

```php
Swoole\Process->setAffinity(array $cpus): bool
```

* **Parameter**

  * **`array $cpus`**
    * **Fungsi**: Bind core `CPU`【contoh `array(0,2,3)` bind ke `CPU0/CPU2/CPU3`】
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

!> - Elemen `$cpus` nggak boleh lebih dari jumlah core `CPU`;  
- `CPU-ID` nggak boleh lebih dari (jumlah core `CPU` - `1`);  
- Fungsi ini tetapi system operasi harus dukung bind `CPU`;  
- Pake [swoole_cpu_num()](/functions?id=swoole_cpu_num) buat dapetin jumlah core `CPU` server.

### getAffinity()
Dapetin afinitas `CPU` proses

```php
Swoole\Process->getAffinity(): array
```
Return value array, elemennya adalah core `CPU`, contoh: `[0, 1, 3, 4]` artinya proses ini bakal dijadwal ke core `CPU` `0/1/3/4`

### setPriority()

Set prioritas proses, process group, dan user process.

!> Swoole >= `v4.5.9`

```php
Swoole\Process->setPriority(int $which, int $priority): bool
```

* **Parameter**

  * **`int $which`**
    * **Fungsi**: Nentuin tipe prioritas yang diubah
    * **Default**: tidak ada
    * **Nilai lain**:

| Konstanta      | Keterangan |
| ------------- | ---------- |
| PRIO_PROCESS  | Proses     |
| PRIO_PGRP     | Process group |
| PRIO_USER     | User process |

  * **`int $priority`**
    * **Fungsi**: Prioritas. Makin kecil nilainya, makin tinggi prioritas
    * **Default**: tidak ada
    * **Nilai lain**: `[-20, 20]`

* **Return Value**

  * Kalo balik `false`, bisa pake [swoole_errno](/functions?id=swoole_errno) dan [swoole_strerror](/functions?id=swoole_strerror) dapetin kode error dan pesan error.

### getPriority()

Dapetin prioritas proses.

!> Swoole >= `v4.5.9`

```php
Swoole\Process->getPriority(int $which): int
```
