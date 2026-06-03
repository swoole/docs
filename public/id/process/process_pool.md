# Swoole\Process\Pool

Process pool, berdasarkan modul Manager [Swoole\Server](/server/init) untuk manajemen proses. Bisa mengelola banyak worker process. Fungsi inti modul ini adalah manajemen proses. Dibanding pake `Process` buat multi-proses, `Process\Pool` lebih sederhana, enkapsulasi lebih tinggi, developer nggak perlu nulis banyak kode buat manajemen proses. Bareng [Co\Server](/coroutine/server?id=contoh-lengkap) bisa bikin program server pure coroutine yang manfaatin multi-core CPU.

## Komunikasi Antar Proses

`Swoole\Process\Pool` nyediain tiga cara komunikasi antar proses:

### Message Queue
Kalo parameter ke-2 `Swoole\Process\Pool->__construct` diset `SWOOLE_IPC_MSGQUEUE`, artinya pake message queue buat komunikasi antar proses. Bisa kirim pesan lewat ekstensi `php sysvmsg`, maksimum pesan `65536`.

* **Catatan**

  * Kalo mau pake ekstensi `sysvmsg`, di konstruktor harus dikasih `msgqueue_key`
  * `Swoole` level bawah nggak dukung parameter ke-2 `mtype` dari `msg_send` ekstensi `sysvmsg`, kasih nilai bukan `0` aja

### Socket Communication
Kalo parameter ke-2 `Swoole\Process\Pool->__construct` diset `SWOOLE_IPC_SOCKET`, artinya pake `Socket Communication`. Kalo client dan server nggak di mesin yang sama, bisa pake cara ini.

Levat method [Swoole\Process\Pool->listen()](/process/process_pool?id=listen) buat mendengarkan port, pake [Message event](/process/process_pool?id=on) buat nerima data dari client, pake [Swoole\Process\Pool->write()](/process/process_pool?id=write) buat balikin respon ke client.

`Swoole` mengharuskan client pas kirim data pake cara ini, harus nambahin 4 byte panjang value dengan network byte order di depan data.
```php
$msg = 'Hello Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```

### UnixSocket
Kalo parameter ke-2 `Swoole\Process\Pool->__construct` diset `SWOOLE_IPC_UNIXSOCK`, artinya pake [UnixSocket](/learn?id=apa-itu-IPC), **sangat disarankan pake cara ini buat komunikasi antar proses**.

Cara ini gampang, tinggal pake method [Swoole\Process\Pool->sendMessage()](/process/process_pool?id=sendMessage) dan [Message event](/process/process_pool?id=on) buat komunikasi antar proses.

Atau kalo `mode coroutine` diaktifkan, bisa dapetin object `Swoole\Process` lewat [Swoole\Process\Pool->getProcess()](/process/process_pool?id=getProcess), pake [Swoole\Process->exportsocket()](/process/process?id=exportsocket) dapetin object `Swoole\Coroutine\Socket`, pake object ini buat komunikasi antar proses. Tapi kalo gitu [Message event](/process/process_pool?id=on) nggak bisa diset.

!> Parameter dan konfigurasi lingkungan bisa liat [konstruktor](/process/process_pool?id=__construct) dan [parameter konfigurasi](/process/process_pool?id=set)

## Konstanta

Konstanta | Keterangan
---|---
SWOOLE_IPC_MSGQUEUE | Komunikasi [message queue](/learn?id=apa-itu-IPC) sistem
SWOOLE_IPC_SOCKET | Komunikasi SOCKET
SWOOLE_IPC_UNIXSOCK | Komunikasi [UnixSocket](/learn?id=apa-itu-IPC) (v4.4+)

## Dukungan Coroutine

Di versi `v4.4.0` nambahin dukungan coroutine, lihat [Swoole\Process\Pool::__construct](/process/process_pool?id=__construct)

## Contoh Penggunaan

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** Ini adalah Worker process */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
```

## Method

### __construct()

Konstruktor.

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **Parameter**

  * **`int $worker_num`**
    * **Fungsi**: Jumlah worker process
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`int $ipc_type`**
    * **Fungsi**: Mode komunikasi antar proses【default `SWOOLE_IPC_NONE` artinya nggak pake fitur IPC】
    * **Default**: `SWOOLE_IPC_NONE`
    * **Nilai lain**: `SWOOLE_IPC_MSGQUEUE`, `SWOOLE_IPC_SOCKET`, `SWOOLE_IPC_UNIXSOCK`

    !> -Kalo `SWOOLE_IPC_NONE`, harus set callback `onWorkerStart`, dan harus implement logic loop di `onWorkerStart`. Pas `onWorkerStart` exit, worker process langsung exit, nanti Manager process bakal jalanin ulang;  
    -`SWOOLE_IPC_MSGQUEUE` artinya pake message queue sistem, bisa set `$msgqueue_key` buat specify `KEY` message queue; kalo nggak diset, bakal pake queue private;  
    -`SWOOLE_IPC_SOCKET` artinya pake `Socket`, perlu pake method [listen](/process/process_pool?id=listen) buat specify alamat dan port;  
    -`SWOOLE_IPC_UNIXSOCK` artinya pake [unixSocket](/learn?id=apa-itu-IPC), dipake di mode coroutine, **sangat disarankan pake cara ini**, detailnya liat di bawah;  
    -Kalo pake selain `SWOOLE_IPC_NONE`, harus set callback `onMessage`, `onWorkerStart` jadi opsional.

  * **`int $msgqueue_key`**
    * **Fungsi**: `key` message queue
    * **Default**: `0`
    * **Nilai lain**: tidak ada

  * **`bool $enable_coroutine`**
    * **Fungsi**: Aktifkan coroutine atau nggak【kalo pake coroutine, `onMessage` callback nggak bisa diset】
    * **Default**: `false`
    * **Nilai lain**: `true`

* **Mode Coroutine**

Di versi `v4.4.0`, modul `Process\Pool` nambahin dukungan coroutine. Bisa aktifkan dengan set parameter ke-4 jadi `true`. Kalo coroutine diaktifkan, level bawah bakal otomatis bikin coroutine dan [coroutine container](/coroutine/scheduler) pas `onWorkerStart`, di callback bisa langsung pake `API` coroutine, contoh:

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

Kalo coroutine diaktifkan, Swoole bakal larang set callback event `onMessage`. Kalo perlu komunikasi antar proses, set parameter ke-2 jadi `SWOOLE_IPC_UNIXSOCK` buat pake [unixSocket](/learn?id=apa-itu-IPC), lalu pake `$pool->getProcess()->exportSocket()` buat export object [Swoole\Coroutine\Socket](/coroutine_client/socket), implement komunikasi antar `Worker` process. Contoh:

 ```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> Detailnya lihat [Swoole\Coroutine\Socket](/coroutine_client/socket) dan [Swoole\Process](/process/process?id=exportsocket).

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```

### set()

Set parameter.

```php
Swoole\Process\Pool->set(array $settings): void
```

Parameter opsional|Tipe|Fungsi|Default
---|---|---|---
enable_coroutine|bool|Aktifkan coroutine|false
enable_message_bus|bool|Aktifkan message bus. Kalo `true`, data besar bakal dipecah jadi potongan kecil, dikirim satu per satu|false
max_package_size|int|Maksimal data yang bisa diterima proses|2 * 1024 * 1024

* **Catatan**

  * Kalo `enable_message_bus` `true`, `max_package_size` nggak berfungsi, karena data bakal dipecah-potong.
  * Di mode `SWOOLE_IPC_MSGQUEUE`, `max_package_size` juga nggak berfungsi, level bawah maksimal nerima `65536`.
  * Di mode `SWOOLE_IPC_SOCKET`, kalo `enable_message_bus` `false` dan data yang diterima lebih besar dari `max_package_size`, level bawah bakal langsung putus koneksi.
  * Di mode `SWOOLE_IPC_UNIXSOCK`, kalo `enable_message_bus` `false` dan data lebih besar dari `max_package_size`, data lebihan bakal dipotong.
  * Kalo mode coroutine diaktifkan dan `enable_message_bus` `true`, `max_package_size` juga nggak berfungsi. Level bawah bakal ngatur pemecahan (kirim) dan penggabungan (terima) data. Kalo nggak, penerimaan data dibatasi `max_package_size`

!> Swoole >= v4.4.4

### on()

Set callback function process pool.

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **Parameter**

  * **`string $event`**
    * **Fungsi**: Tentukan event
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`callable $function`**
    * **Fungsi**: Callback function
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

* **Event**

  * **onWorkerStart** Child process mulai

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Object Pool
   * @param int $workerId   WorkerId nomor worker process saat ini, internal bakal kasih nomor
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} is started\n";
  });
  ```

  * **onWorkerStop** Child process selesai

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Object Pool
   * @param int $workerId   WorkerId nomor worker process saat ini, internal bakal kasih nomor
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} stop\n";
  });
  ```

  * **onMessage** Penerimaan pesan

  !> Nerima pesan dari luar. Satu koneksi cuma bisa kirim satu pesan, mirip mekanisme short connection `PHP-FPM`

  ```php
  /**
    * @param \Swoole\Process\Pool $pool Object Pool
    * @param string $data Isi data pesan
   */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
    var_dump($data);
  });
  ```

  !> Nama event nggak case-sensitive, `WorkerStart`, `workerStart` atau `workerstart` sama aja.

### listen()

Mendengarkan `SOCKET`, cuma bisa dipake kalo `$ipc_mode = SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **Parameter**

  * **`string $host`**
    * **Fungsi**: Alamat yang didengarkan【dukung dua tipe: `TCP` dan [unixSocket](/learn?id=apa-itu-IPC). `127.0.0.1` mendengarkan alamat `TCP`, perlu specify `$port`. `unix:/tmp/php.sock` mendengarkan [unixSocket](/learn?id=apa-itu-IPC)】
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`int $port`**
    * **Fungsi**: Port yang didengarkan【wajib di mode `TCP`】
    * **Default**: `0`
    * **Nilai lain**: tidak ada

  * **`int $backlog`**
    * **Fungsi**: Panjang antrian koneksi
    * **Default**: `2048`
    * **Nilai lain**: tidak ada

* **Return Value**

  * Sukses mendengarkan, balik `true`
  * Gagal mendengarkan, balik `false`, bisa panggil `swoole_errno` dapetin kode error. Kalo gagal mendengarkan, pas panggil `start` bakal langsung balik `false`

* **Protokol Komunikasi**

    Pas kirim data ke port yang didengarkan, client harus nambahin 4 byte panjang value network byte order sebelum request. Format protokol:

```php
// $msg data yang dikirim
$packet = pack('N', strlen($msg)) . $msg;
```

* **Contoh Penggunaan**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```

### write()

Nulis data ke lawan bicara, cuma bisa dipake kalo `$ipc_mode` `SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->write(string $data): bool
```

!> Method ini operasi memory, tanpa `IO`, operasi kirim data adalah `IO` synchronous blocking

* **Parameter**

  * **`string $data`**
    * **Fungsi**: Data yang ditulis【bisa panggil `write` berkali-kali, level bawah bakal tulis semua data ke `socket` pas `onMessage` selesai, lalu `close` koneksi】
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

* **Contoh Penggunaan**

  * **Server**

    ```php
    $pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
    
    $pool->on("Message", function ($pool, $message) {
        echo "Message: {$message}\n";
        $pool->write("hello ");
        $pool->write("world ");
        $pool->write("\n");
    });
    
    $pool->listen('127.0.0.1', 8089);
    $pool->start();
    ```

  * **Client**

    ```php
    $fp = stream_socket_client("tcp://127.0.0.1:8089", $errno, $errstr) or die("error: $errstr\n");
    $msg = json_encode(['data' => 'hello', 'uid' => 1991]);
    fwrite($fp, pack('N', strlen($msg)) . $msg);
    sleep(1);
    //bakal nampilin hello world\n
    $data = fread($fp, 8192);
    var_dump(substr($data, 4, unpack('N', substr($data, 0, 4))[1]));
    fclose($fp);
    ```

### sendMessage()

Kirim data ke proses target, cuma bisa dipake kalo `$ipc_mode` `SWOOLE_IPC_UNIXSOCK`.

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **Parameter**

  * **`string $data`**
    * **Fungsi**: Data yang dikirim
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`int $dst_worker_id`**
    * **Fungsi**: ID proses target
    * **Default**: `0`
    * **Nilai lain**: tidak ada

* **Return Value**

  * Kirim sukses balik `true`
  * Kirim gagal balik `false`

* **Catatan**

  * Kalo data yang dikirim lebih besar dari `max_package_size` dan `enable_message_bus` `false`, proses target pas nerima data bakal memotong data

```php
<?php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => false, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(2048)


$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => true, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(6000)
```

### start()

Mulai worker process.

```php
Swoole\Process\Pool->start(): bool
```

!> Mulai sukses, proses saat ini masuk status `wait`, mengelola worker process;  
Mulai gagal, balik `false`, bisa pake `swoole_errno` dapetin kode error.

* **Contoh Penggunaan**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
```

* **Manajemen Proses**

  * Kalo worker process kena fatal error atau exit sendiri, manager bakal membersihkan, hindari zombie process
  * Setelah worker process exit, manager bakal otomatis buat worker process baru
  * Main process nerima sinyal `SIGTERM` bakal berhenti `fork` proses baru, dan `kill` semua worker process yang jalan
  * Main process nerima sinyal `SIGUSR1` bakal `kill` satu-satu worker process yang jalan, lalu restart worker process baru

* **Penanganan Sinyal**

  Level bawah cuma set penanganan sinyal di main process (manager process), nggak set sinyal untuk `Worker` process. Developer perlu implement sendiri mendengarkan sinyal.

  - Worker process mode asynchronous, pake [Swoole\Process::signal](/process/process?id=signal) buat mendengarkan sinyal
  - Worker process mode synchronous, pake `pcntl_signal` dan `pcntl_signal_dispatch` buat mendengarkan sinyal

  Di worker process harus mendengarkan sinyal `SIGTERM`. Kalo main process perlu matiin proses itu, bakal kirim sinyal `SIGTERM`. Kalo worker process nggak mendengarkan `SIGTERM`, level bawah bakal paksa matiin proses, sebagian logic bisa ilang.

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```

### stop()

Keluarin socket proses saat ini dari event loop. Fungsi ini cuma berguna kalo coroutine diaktifkan.

```php
Swoole\Process\Pool->stop(): bool
```

### shutdown()

Hentikan worker process.

```php
Swoole\Process\Pool->shutdown(): bool
```

### getProcess()

Dapetin object worker process saat ini. Balikin object [Swoole\Process](/process/process).

!> Swoole >= `v4.2.0`

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **Parameter**

  * **`int $worker_id`**
    * **Fungsi**: Tentukan `worker` yang mau diambil【opsional, default `worker` saat ini】
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

!> Harus dipanggil setelah `start`, di `onWorkerStart` worker process atau callback lain;  
Object `Process` yang dibalikin adalah singleton, panggil `getProcess()` berkali-kali di worker process bakal balikin object yang sama.

* **Contoh Penggunaan**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```

### detach()

Lepas Worker process saat ini dari mengelola pool. Level bawah bakal langsung buat proses baru, proses lama nggak bakal ngolah data lagi, lifecycle diurus kode aplikasi sendiri.

!> Swoole >= `v4.7.0`

```php
Swoole\Process\Pool->detach(): bool
```
