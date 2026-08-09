# Runtime

Dibandingkan dengan `Swoole1.x`, `Swoole4+` menyediakan korutin sebagai senjata andalan, semua kode bisnis sinkron tetapi `IO` level bawah asinkron, menjamin konkurensi sambil menghindari logika kode yang tersebar akibat callback asinkron tradisional dan terjebak dalam banyak lapisan callback yang membuat kode tidak terawat. Untuk mencapai efek ini, semua request `IO` harus [IO asinkron](/learn?id=同步io异步io). Sedangkan klien `MySQL`, `Redis` dll yang disediakan di era `Swoole1.x` meskipun IO asinkron, tetapi menggunakan cara pemrograman callback asinkron, bukan korutin, sehingga di era `Swoole4` klien-klien ini dihapus.

Untuk mengatasi masalah dukungan korutin pada klien-klien ini, tim pengembang Swoole melakukan banyak pekerjaan:

- Awalnya, untuk setiap jenis klien dibuat klien korutin, lihat [klien korutin](/coroutine_client/init), tetapi cara ini memiliki 3 masalah:
  * Implementasi rumit, protokol detail setiap klien sangat kompleks, ingin mendukung sempurna membutuhkan kerja besar.
  * Pengguna perlu mengubah banyak kode, misalnya query `MySQL` asli menggunakan `PDO` asli PHP, sekarang perlu menggunakan method [Swoole\Coroutine\MySQL](/coroutine_client/mysql).
  * Sulit mencakup semua operasi, misalnya `proc_open()`, `sleep()` dll juga bisa memblokir dan menyebabkan program menjadi sinkron blocking.

- Mengatasi masalah di atas, tim pengembang Swoole mengubah pendekatan implementasi, menggunakan cara `Hook` fungsi PHP asli untuk mengimplementasikan klien korutin. Dengan satu baris kode, kode IO sinkron dapat diubah menjadi [IO asinkron](/learn?id=同步io异步io) yang dapat dijadwalkan korutin, yaitu `Satu-klik Korutinisasi`.

!> Fitur ini stabil mulai versi `v4.3`. Fungsi yang bisa `dikorutinisasi` juga semakin banyak, sehingga beberapa klien korutin yang ditulis sebelumnya sudah tidak direkomendasikan lagi, detail lihat [klien korutin](/coroutine_client/init). Contoh: di `v4.3+` mendukung `korutinisasi` operasi file (`file_get_contents`, `fread` dll), jika menggunakan versi `v4.3+` bisa langsung menggunakan `korutinisasi` daripada menggunakan [operasi file korutin](/coroutine/system) yang disediakan Swoole.

## Prototipe Fungsi

Atur rentang fungsi yang akan `dikorutinisasi` melalui `flags`

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // v4.4+ gunakan method ini.
// atau
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

Untuk mengaktifkan beberapa `flags` sekaligus, gunakan operator `|`

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

!> Fungsi yang di-`Hook` harus digunakan di [wadah korutin](/coroutine/scheduler)

#### Pertanyaan Umum :id=runtime-qa

!> **Mana yang digunakan `Swoole\Runtime::enableCoroutine()` atau `Co::set(['hook_flags'])`**

* `Swoole\Runtime::enableCoroutine()` dapat mengatur flags secara dinamis setelah layanan mulai (runtime). Setelah dipanggil, berlaku global dalam proses saat ini, harus diletakkan di awal proyek untuk mendapatkan cakupan 100%;
* `Co::set()` dapat dipahami seperti `ini_set()` PHP, perlu dipanggil sebelum [Server->start()](/server/methods?id=start) atau [Co\run()](/coroutine/scheduler), jika tidak `hook_flags` yang diatur tidak akan berlaku. Di versi `v4.4+` sebaiknya gunakan cara ini untuk mengatur `flags`;
* Baik `Co::set(['hook_flags'])` maupun `Swoole\Runtime::enableCoroutine()` sebaiknya hanya dipanggil sekali, panggilan berulang akan ditimpa.

## Opsi

Opsi yang didukung `flags`:

### SWOOLE_HOOK_ALL

Aktifkan semua jenis flags berikut (tidak termasuk CURL)

!> Mulai v4.5.4, `SWOOLE_HOOK_ALL` termasuk `SWOOLE_HOOK_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); // tidak termasuk CURL
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); // benar-benar korutinisasi semua jenis, termasuk CURL
```

### SWOOLE_HOOK_TCP

Mulai `v4.1` mendukung, stream tipe TCP Socket, termasuk yang paling umum seperti `Redis`, `PDO`, `Mysqli` dan operasi koneksi TCP menggunakan fungsi seri [streams](https://www.php.net/streams) PHP, semua bisa di-`Hook`. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//membuat 100 korutin
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//di sini terjadi penjadwalan korutin, cpu beralih ke korutin berikutnya, tidak memblokir proses
            $redis->get('key');//di sini terjadi penjadwalan korutin, cpu beralih ke korutin berikutnya, tidak memblokir proses
        });
    }
});
```

Kode di atas menggunakan class `Redis` asli, tapi sebenarnya sudah menjadi `IO asinkron`. `Co\run()` adalah membuat [wadah korutin](/coroutine/scheduler), `go()` adalah membuat korutin. Kedua operasi ini sudah otomatis dilakukan di [kelas Swoole\Server](/server/init) yang disediakan Swoole, tidak perlu manual, lihat [enable_coroutine](/server/setting?id=enable_coroutine).

Artinya programmer `PHP` tradisional dapat menulis program konkuren tinggi, performa tinggi dengan kode logika yang paling familiar, seperti berikut:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);//di sini terjadi penjadwalan korutin, cpu beralih ke korutin berikutnya (request berikutnya), tidak memblokir proses
      $redis->get('key');//di sini terjadi penjadwalan korutin, cpu beralih ke korutin berikutnya (request berikutnya), tidak memblokir proses
});

$http->start();
```

### SWOOLE_HOOK_UNIX

Mulai `v4.2` mendukung. Stream tipe `Unix Stream Socket`. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UNIX]);

Co\run(function () {
    $socket = stream_socket_server(
        'unix://swoole.sock',
        $errno,
        $errstr,
        STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_accept($socket)) {
    }
});
```

### SWOOLE_HOOK_UDP

Mulai `v4.2` mendukung. Stream tipe UDP Socket. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDP]);

Co\run(function () {
    $socket = stream_socket_server(
        'udp://0.0.0.0:6666',
        $errno,
        $errstr,
        STREAM_SERVER_BIND
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_recvfrom($socket, 1, 0)) {
    }
});
```

### SWOOLE_HOOK_UDG

Mulai `v4.2` mendukung. Stream tipe Unix Dgram Socket. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDG]);

Co\run(function () {
    $socket = stream_socket_server(
        'udg://swoole.sock',
        $errno,
        $errstr,
        STREAM_SERVER_BIND
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_recvfrom($socket, 1, 0)) {
    }
});
```

### SWOOLE_HOOK_SSL

Mulai `v4.2` mendukung. Stream tipe SSL Socket. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SSL]);

Co\run(function () {
    $host = 'host.domain.tld';
    $port = 1234;
    $timeout = 10;
    $cert = '/path/to/your/certchain/certchain.pem';
    $context = stream_context_create(
        array(
            'ssl' => array(
                'local_cert' => $cert,
            )
        )
    );
    if ($fp = stream_socket_client(
        'ssl://' . $host . ':' . $port,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    )) {
        echo "connected\n";
    } else {
        echo "ERROR: $errno - $errstr \n";
    }
});
```

### SWOOLE_HOOK_TLS

Mulai `v4.2` mendukung. Stream tipe `TLS Socket`, [referensi](https://www.php.net/manual/en/context.ssl.php).

Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```

### SWOOLE_HOOK_SLEEP

Mulai `v4.2` mendukung. `Hook` fungsi `sleep`, termasuk `sleep`, `usleep`, `time_nanosleep`, `time_sleep_until`. Karena granularitas minimum timer level bawah adalah `1ms`, saat menggunakan fungsi sleep presisi tinggi seperti `usleep`, jika diatur kurang dari `1ms`, akan langsung menggunakan panggilan sistem `sleep`. Mungkin menyebabkan blocking tidur yang sangat singkat. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SLEEP]);

Co\run(function () {
    go(function () {
        sleep(1);
        echo '1' . PHP_EOL;
    });
    go(function () {
        echo '2' . PHP_EOL;
    });
});
```
Output
```
2
1
```

### SWOOLE_HOOK_FILE

Mulai `v4.3` mendukung.

#### `Penanganan korutinisasi` operasi file, fungsi yang didukung:

* `fopen`
* `fread`, `fgets`, `fgetc`
* `fwrite`, `fputs`
* `file_get_contents`, `file_put_contents`, `readfile`
* `unlink`, `mkdir`, `rmdir`
* `opendir`, `readdir`, `closedir`, `scandir`
* Fungsi pustaka standar `PHP` lain tentang operasi file disk

Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```

#### Menonaktifkan `HOOK` File

Karena setelah mengaktifkan `HOOK` file, semua operasi file menjadi asinkron non-blocking, termasuk operasi `autoload` dan `include` dll. Jika operasi ini terjadi pada titik penjadwalan korutin, dapat menyebabkan masalah yang tidak terduga. Untuk itu, nonaktifkan opsi `hook` file:

```php
# Aktifkan semua opsi korutin HOOK kecuali HOOK file
Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL & ~SWOOLE_HOOK_FILE);
```

Setelah menonaktifkan `HOOK` file, semua operasi file menjadi sinkron blocking, tidak terjadi peralihan korutin, dapat digunakan dengan aman untuk `autoload`, `include` atau logika analisis kode sumber `PHP` lainnya.
Dalam keadaan HOOK file dimatikan, dapat menggunakan cara berikut untuk membaca/menulis file asinkron:
- `System::readFile()`: Membaca konten file secara asinkron
- `System::writeFile()`: Menulis konten file secara asinkron

Sejak versi `6.1`, juga dapat menggunakan `fopen('async.file://path/to/file', 'rw')` untuk membuka stream file asinkron.
```php
Co\run(function () {
    # Ini adalah stream file asinkron, semua operasi baca/tulis pada resource ini adalah asinkron non-blocking
    $fp = fopen("async.file:///tmp/test.txt", "w+");
    fwrite($fp, "Hello World\n");
    fdatasync($fp);
    fclose($fp);
});
```

#### Kunci File
Saat membaca/menulis file secara konkuren, perlu menggunakan kunci file untuk menjamin konsistensi data. Saat memanggil `flock()` akan menghasilkan penjadwalan korutin. Contoh:

```php
Co\run(function () {
    $fp = fopen("/tmp/test.txt", "w+");
    flock($fp, LOCK_EX);
    fwrite($fp, "Hello World\n");
    flock($fp, LOCK_UN);
    fclose($fp);
});
```

`flock()` mendukung tiga jenis kunci: `LOCK_SH`, `LOCK_EX`, `LOCK_UN`. Gunakan `LOCK_SH` untuk operasi baca saja, `LOCK_EX` untuk operasi baca/tulis, `LOCK_UN` untuk melepaskan kunci.

Perhatikan bahwa `flock()` tidak dikontrol oleh opsi `SWOOLE_HOOK_FILE`, melainkan `SWOOLE_HOOK_STDIO`. Jadi meskipun `SWOOLE_HOOK_FILE` dimatikan, `flock()` tetap dikorutinisasi.
Jika perlu menonaktifkan korutinisasi `flock()`, nonaktifkan opsi `SWOOLE_HOOK_STDIO`.

```php
Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL & ~SWOOLE_HOOK_FILE & ~SWOOLE_HOOK_STDIO);
```

### SWOOLE_HOOK_STREAM_FUNCTION

Mulai `v4.4` mendukung. `Hook` `stream_select()`. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_STREAM_FUNCTION]);

Co\run(function () {
    $fp1 = stream_socket_client("tcp://www.baidu.com:80", $errno, $errstr, 30);
    $fp2 = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
    if (!$fp1) {
        echo "$errstr ($errno) \n";
    } else {
        fwrite($fp1, "GET / HTTP/1.0\r\nHost: www.baidu.com\r\nUser-Agent: curl/7.58.0\r\nAccept: */*\r\n\r\n");
        $r_array = [$fp1, $fp2];
        $w_array = $e_array = null;
        $n = stream_select($r_array, $w_array, $e_array, 10);
        $html = '';
        while (!feof($fp1)) {
            $html .= fgets($fp1, 1024);
        }
        fclose($fp1);
    }
});
```

### SWOOLE_HOOK_BLOCKING_FUNCTION

Mulai `v4.4` mendukung. `Blocking function` di sini termasuk: `gethostbyname`, `exec`, `shell_exec`. Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```

### SWOOLE_HOOK_PROC

Mulai `v4.4` mendukung. Korutinisasi fungsi `proc*`, termasuk: `proc_open`, `proc_close`, `proc_get_status`, `proc_terminate`.

Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PROC]);

Co\run(function () {
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin, proses anak membaca darinya
        1 => array("pipe", "w"),  // stdout, proses anak menulis ke sana
    );
    $process = proc_open('php', $descriptorspec, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], 'I am process');
        fclose($pipes[0]);

        while (true) {
            echo fread($pipes[1], 1024);
        }

        fclose($pipes[1]);
        $return_value = proc_close($process);
        echo "command returned $return_value" . PHP_EOL;
    }
});
```

### SWOOLE_HOOK_CURL

Mulai [v4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) atau `v4.5` resmi didukung.

#### Fungsi `CURL` yang didukung:

* `curl_init`
* `curl_setopt`
* `curl_exec`
* `curl_multi_getcontent`
* `curl_setopt_array`
* `curl_error`
* `curl_getinfo`
* `curl_errno`
* `curl_close`
* `curl_reset`

Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_CURL]);

Co\run(function () {
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, "https://www.xinhuanet.com/");  
    curl_setopt($ch, CURLOPT_HEADER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);  
    curl_close($ch);
    var_dump($result);
});
```

### SWOOLE_HOOK_NATIVE_CURL

`Penanganan korutinisasi` untuk `CURL`, berbeda dengan `SWOOLE_HOOK_CURL`, `SWOOLE_HOOK_NATIVE_CURL` diimplementasikan berdasarkan library `libcurl`, mendukung semua fungsi `CURL`.

- Sebelum digunakan, perlu mengaktifkan opsi [--enable-swoole-curl](/environment?id=通用参数) saat kompilasi
- Opsi ini bersifat mutual eksklusif dengan [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_all), tidak bisa diaktifkan bersamaan
- Saat menggunakan opsi `SWOOLE_HOOK_ALL`, `SWOOLE_HOOK_NATIVE_CURL` diprioritaskan

!> Versi `Swoole` >= `v4.6.0` tersedia

```php
Co::set(['hook_flags' => SWOOLE_HOOK_NATIVE_CURL]);

Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_NATIVE_CURL]);
```

Contoh:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

Co\run(function () {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://httpbin.org/get");
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    var_dump($result);
});
```

### SWOOLE_HOOK_SOCKETS

`Penanganan korutinisasi` untuk ekstensi `sockets`.

!> Versi `Swoole` >= `v4.6.0` tersedia

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SOCKETS]);
```

### SWOOLE_HOOK_STDIO

`Penanganan korutinisasi` untuk `STDIO`.

!> Versi Swoole >= `v4.6.2` tersedia

```php
Co::set(['hook_flags' => SWOOLE_HOOK_STDIO]);
```

Contoh:

```php
use Swoole\Process;
Co::set(['socket_read_timeout' => -1, 'hook_flags' => SWOOLE_HOOK_STDIO]);
$proc = new Process(function ($p) {
    Co\run(function () use($p) {
        $p->write('start'.PHP_EOL);
        go(function() {
            co::sleep(0.05);
            echo "sleep\n";
        });
        echo fread(STDIN, 1024);
    });
}, true, SOCK_STREAM);
$proc->start();
echo $proc->read();
usleep(100000);
$proc->write('hello world'.PHP_EOL);
echo $proc->read();
echo $proc->read();
Process::wait();
```

### SWOOLE_HOOK_PDO_PGSQL

`Penanganan korutinisasi` untuk `pdo_pgsql`.

!> Versi `Swoole` >= `v5.1.0` tersedia

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

Contoh:
```php
<?php
function test()
{
    $dbname   = "test";
    $username = "test";
    $password = "test";
    try {
        $dbh = new PDO("pgsql:dbname=$dbname;host=127.0.0.1:5432", $username, $password);
        $dbh->exec('create table test (id int)');
        $dbh->exec('insert into test values(1)');
        $dbh->exec('insert into test values(2)');
        $res = $dbh->query("select * from test");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['trace_flags' => SWOOLE_HOOK_PDO_PGSQL]);

Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_ODBC

`Penanganan korutinisasi` untuk `pdo_odbc`.

!> Versi `Swoole` >= `v5.1.0` tersedia

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

Contoh:
```php
<?php
function test()
{
    $username = "test";
    $password = "test";
    try {
        $dbh = new PDO("odbc:mysql-test");
        $res = $dbh->query("select sleep(1) s");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['trace_flags' => SWOOLE_TRACE_CO_ODBC, 'log_level' => SWOOLE_LOG_DEBUG]);

Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_ORACLE

`Penanganan korutinisasi` untuk `pdo_oci`.

!> Versi `Swoole` >= `v5.1.0` tersedia

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

Contoh:
```php
<?php
function test()
{
	$tsn = 'oci:dbname=127.0.0.1:1521/xe;charset=AL32UTF8';
	$username = "test";
	$password = "test";
    try {
        $dbh = new PDO($tsn, $username, $password);
        $dbh->exec('create table test (id int)');
        $dbh->exec('insert into test values(1)');
        $dbh->exec('insert into test values(2)');
        $res = $dbh->query("select * from test");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_SQLITE
`Penanganan korutinisasi` untuk `pdo_sqlite`.

!> Versi `Swoole` >= `v5.1.0` tersedia

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_SQLITE]);
```

* **Catatan**

!> Saat `swoole` mengkorutinisasi database `sqlite`, menggunakan mode `serialisasi` untuk menjamin [thread safety](https://www.sqlite.org/threadsafe.html).  
Jika mode thread yang ditentukan saat kompilasi database `sqlite` adalah mode single-thread, `swoole` tidak dapat mengkorutinisasi `sqlite` dan akan melempar peringatan, tetapi tidak mempengaruhi penggunaan, hanya saja tidak akan terjadi peralihan korutin selama proses tambah, hapus, ubah, dan query. Dalam situasi ini hanya bisa mengompilasi ulang `sqlite` dan menentukan mode thread menjadi `serialisasi` atau `multi-thread`, [alasan](https://www.sqlite.org/compile.html#threadsafe).     
Semua koneksi `sqlite` yang dibuat di lingkungan korutin adalah `serialized`, koneksi `sqlite` yang dibuat di lingkungan non-korutin secara default sama dengan mode thread `sqlite`.   
Jika mode thread `sqlite` adalah `multi-thread`, maka koneksi yang dibuat di lingkungan non-korutin tidak bisa dibagikan ke banyak korutin, karena koneksi database dalam mode `multi-thread`, dan saat digunakan di lingkungan korutin tidak akan ditingkatkan menjadi `serialisasi`.   
Mode thread default `sqlite` adalah `serialisasi`, [penjelasan serialisasi](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized), [mode thread default](https://www.sqlite.org/compile.html#threadsafe).      

Contoh:
```php
<?php
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

Co::set(['hook_flags'=> SWOOLE_HOOK_PDO_SQLITE]);

run(function() {
    for($i = 0; $i <= 5; $i++) {
        go(function() use ($i) {
            $db = new PDO('sqlite::memory:');
            $db->query('select randomblob(99999999)');
            var_dump($i);
        });
    }
});
```

## Method

### setHookFlags()

Mengatur rentang fungsi yang akan di-`Hook` melalui `flags`

!> Versi `Swoole` >= `v4.5.0` tersedia

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```

### getHookFlags()

Mendapatkan `flags` dari konten yang telah di-`Hook`, mungkin tidak konsisten dengan `flags` yang dimasukkan saat mengaktifkan `Hook` (karena `flags` yang gagal di-`Hook` akan dibersihkan)

!> Versi `Swoole` >= `v4.4.12` tersedia

```php
Swoole\Runtime::getHookFlags(): int
```

## Daftar Hook Umum

### Daftar Tersedia

* Ekstensi `redis`
* Ekstensi `mysqli`, `pdo_mysql` (perlu mengaktifkan `mysqlnd`)
* Ekstensi `curl`
* `file_get_contents`, `fopen`
* `stream_socket_client` (`predis`, `php-amqplib`)
* `stream_socket_server`
* `stream_select` (perlu versi `4.3.2` ke atas)
* `fsockopen`
* `proc_open` (perlu versi `4.4.0` ke atas)
* Ekstensi `soap`
* `pdo_pgsql` (perlu versi `v5.1.0` ke atas)
* `pdo_oci` (perlu versi `v5.1.0` ke atas)
* `pdo_odbc` (perlu versi `v5.1.0` ke atas)

### Daftar Tidak Tersedia

!> **Tidak mendukung korutinisasi** berarti korutin akan diturunkan ke mode blocking, saat itu menggunakan korutin tidak ada artinya

* Ekstensi `mysql`: level bawah menggunakan `libmysqlclient`
* Ekstensi `mongodb`: level bawah menggunakan `mongo-c-client`
* `pdo_firebird`, level bawah menggunakan library klien `C` `firebird`, hanya mendukung `IO` sinkron blocking
* `php-amqp`, level bawah menggunakan `librabbitmq`, hanya mendukung `IO` sinkron blocking
* `ftp`, level bawah menggunakan `poll()` menunggu `Socket`, tidak mendukung korutinisasi

## Perubahan API

Di `v4.3` dan sebelumnya, `API` `Runtime::enableCoroutine()` memerlukan `2` parameter

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```

- `$enable`: Mengaktifkan atau menonaktifkan korutinisasi.
- `$flags`: Memilih tipe yang akan `dikorutinisasi`, bisa multi pilih, default semua. Hanya efektif saat `$enable = true`.

!> `Runtime::enableCoroutine(false)` menonaktifkan semua pengaturan Hook korutin yang diatur sebelumnya.

Setelah versi `v4.4`, `API` `Runtime::enableCoroutine()` berubah menjadi hanya perlu `1` parameter

```php
Swoole\Runtime::enableCoroutine(int $flags = SWOOLE_HOOK_ALL);
```
- Perubahan: parameter `$enable` dihapus, `$flags` = `0` berarti menonaktifkan semua pengaturan Hook korutin.
