# Coroutine\System

Enkapsulasi coroutine untuk `API` terkait sistem. Modul ini tersedia setelah rilis resmi `v4.4.6`. Sebagian besar `API` diimplementasikan berdasarkan thread pool `AIO`.

!> Untuk versi sebelum `v4.4.6`, gunakan nama pendek `Co` atau `Swoole\Coroutine`, seperti: `Co::sleep` atau `Swoole\Coroutine::sleep`  
Mulai `v4.4.6` **direkomendasikan** menggunakan `Co\System::sleep` atau `Swoole\Coroutine\System::sleep`  
Perubahan ini bertujuan untuk menstandarisasi namespace, namun tetap kompatibel ke belakang (artinya penulisan sebelum `v4.4.6` tetap bisa digunakan, tidak perlu diubah).

## Method

### statvfs()

Mendapatkan informasi sistem file.

!> Tersedia sejak Swoole versi >= v4.2.5

```php
Swoole\Coroutine\System::statvfs(string $path): array|false
```

  * **Parameter** 

    * **`string $path`**
      * **Fungsi**: Direktori tempat sistem file di-mount [seperti `/`, bisa menggunakan perintah `df` dan `mount -l`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

  * **Contoh Penggunaan**

    ```php
    Swoole\Coroutine\run(function () {
        var_dump(Swoole\Coroutine\System::statvfs('/'));
    });
    ```
  * **Contoh Output**
    
    ```php
    array(11) {
      ["bsize"]=>
      int(4096)
      ["frsize"]=>
      int(4096)
      ["blocks"]=>
      int(61068098)
      ["bfree"]=>
      int(45753580)
      ["bavail"]=>
      int(42645728)
      ["files"]=>
      int(15523840)
      ["ffree"]=>
      int(14909927)
      ["favail"]=>
      int(14909927)
      ["fsid"]=>
      int(1002377915335522995)
      ["flag"]=>
      int(4096)
      ["namemax"]=>
      int(255)
    }
    ```

### fread()

Membaca file dalam mode coroutine.

```php
Swoole\Coroutine\System::fread(resource $handle, int $length = 0): string|false
```

!> Di versi di bawah `v4.0.4`, method `fread` tidak mendukung `stream` non-file seperti `STDIN`, `Socket`. Jangan gunakan `fread` untuk resource tersebut.  
Di versi `v4.0.4` ke atas, method `fread` mendukung resource `stream` non-file. Infrastruktur akan otomatis memilih menggunakan thread pool `AIO` atau [EventLoop](/learn?id=apa-itu-eventloop).

!> Method ini sudah tidak digunakan lagi di versi `5.0` dan telah dihapus di versi `6.0`.

  * **Parameter** 

    * **`resource $handle`**
      * **Fungsi**: Handle file [harus berupa resource `stream` tipe file yang dibuka dengan `fopen`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $length`**
      * **Fungsi**: Panjang yang akan dibaca [bawaan `0`, berarti membaca seluruh konten file]
      * **Bawaan**: `0`
      * **Nilai Lain**: Tidak ada

  * **Return Value** 

    * Mengembalikan string jika berhasil, `false` jika gagal.

  * **Contoh Penggunaan**  

    ```php
    $fp = fopen(__FILE__, "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fread($fp);
        var_dump($r);
    });
    ```

### fwrite()

Menulis data ke file dalam mode coroutine.

```php
Swoole\Coroutine\System::fwrite(resource $handle, string $data, int $length = 0): int|false
```

!> Di versi di bawah `v4.0.4`, method `fwrite` tidak mendukung `stream` non-file seperti `STDIN`, `Socket`. Jangan gunakan `fwrite` untuk resource tersebut.  
Di versi `v4.0.4` ke atas, method `fwrite` mendukung resource `stream` non-file. Infrastruktur akan otomatis memilih berdasarkan tipe `stream` antara thread pool `AIO` atau [EventLoop](/learn?id=apa-itu-eventloop).

!> Method ini sudah tidak digunakan lagi di versi `5.0` dan telah dihapus di versi `6.0`.

  * **Parameter** 

    * **`resource $handle`**
      * **Fungsi**: Handle file [harus berupa resource `stream` tipe file yang dibuka dengan `fopen`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`string $data`**
      * **Fungsi**: Data yang akan ditulis [bisa berupa teks atau data biner]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $length`**
      * **Fungsi**: Panjang yang akan ditulis [bawaan `0`, berarti menulis seluruh konten `$data`, `$length` harus kurang dari panjang `$data`]
      * **Bawaan**: `0`
      * **Nilai Lain**: Tidak ada

  * **Return Value** 

    * Mengembalikan panjang data jika berhasil, `false` jika gagal.

  * **Contoh Penggunaan**  

    ```php
    $fp = fopen(__DIR__ . "/test.data", "a+");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fwrite($fp, "hello world\n", 5);
        var_dump($r);
    });
    ```

### fgets()

Membaca file baris per baris dalam mode coroutine.

Menggunakan buffer `php_stream` dengan ukuran bawaan `8192` byte, ukuran buffer bisa diatur dengan `stream_set_chunk_size`.

```php
Swoole\Coroutine\System::fgets(resource $handle): string|false
```

!> Fungsi `fgets` hanya bisa digunakan untuk resource `stream` tipe file. Tersedia sejak Swoole versi >= `v4.4.4`.

!> Method ini sudah tidak digunakan lagi di versi `5.0` dan telah dihapus di versi `6.0`.

  * **Parameter** 

    * **`resource $handle`**
      * **Fungsi**: Handle file [harus berupa resource `stream` tipe file yang dibuka dengan `fopen`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

  * **Return Value** 

    * Jika membaca `EOL` (`\r` atau `\n`), akan mengembalikan satu baris data termasuk `EOL`
    * Jika tidak membaca `EOL` tapi konten melebihi buffer `php_stream` `8192` byte, akan mengembalikan `8192` byte data tanpa `EOL`
    * Saat mencapai akhir file (`EOF`), mengembalikan string kosong, bisa gunakan `feof` untuk mengecek apakah file sudah selesai dibaca
    * Mengembalikan `false` jika gagal baca, gunakan [swoole_last_error](/functions?id=swoole_last_error) untuk kode error

  * **Contoh Penggunaan**  

    ```php
    $fp = fopen(__DIR__ . "/defer_client.php", "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fgets($fp);
        var_dump($r);
    });
    ```

### readFile()

Membaca file dalam mode coroutine.

```php
Swoole\Coroutine\System::readFile(string $filename, int $flags = 0): string|false
```

  * **Parameter** 

    * **`string $filename`**
      * **Fungsi**: Nama file
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada
    * **`int $flags`**
      * **Fungsi**: Apakah menggunakan file lock, saat ini hanya mendukung opsi `LOCK_EX`. Perhatikan bahwa infrastruktur akan mengatur shared lock saat membaca file, coroutine lain tetap bisa membaca file tapi tidak bisa menulis.
      * **Bawaan**: `0`, berarti tidak menggunakan file lock

  * **Return Value** 

    * Mengembalikan string konten jika berhasil, `false` jika gagal. Bisa gunakan [swoole_last_error](/functions?id=swoole_last_error) untuk info error.
    * Method `readFile` tidak memiliki batasan ukuran, konten yang dibaca akan disimpan di memori, jadi membaca file yang sangat besar bisa menghabiskan banyak memori.

  * **Contoh Penggunaan**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $r = Swoole\Coroutine\System::readFile($filename);
        var_dump($r);
    });
    ```

### writeFile()

Menulis file dalam mode coroutine.

```php
Swoole\Coroutine\System::writeFile(string $filename, string $fileContent, int $flags): bool
```

  * **Parameter** 

    * **`string $filename`**
      * **Fungsi**: Nama file [harus memiliki izin tulis, file akan dibuat otomatis jika tidak ada. Jika gagal membuka file, akan segera mengembalikan `false`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`string $fileContent`**
      * **Fungsi**: Konten yang akan ditulis ke file [maksimal `4M`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $flags`**
      * **Fungsi**: Opsi penulisan [bawaan akan menghapus konten file saat ini, bisa gunakan `FILE_APPEND` untuk menambahkan ke akhir file]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: `LOCK_EX` akan mengunci file secara eksklusif saat menulis, mencegah coroutine lain membaca/menulis file ini. Banyak opsi bisa digabung dengan bitwise OR `|`, seperti `FILE_APPEND | LOCK_EX`

  * **Return Value** 

    * Mengembalikan `true` jika berhasil
    * Mengembalikan `false` jika gagal

  * **Contoh Penggunaan**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename) 
    {
        $w = Swoole\Coroutine\System::writeFile($filename, "hello swoole!");
        var_dump($w);
    });
    ```

### sleep()

Memasuki keadaan menunggu.

Setara dengan fungsi `sleep` di `PHP`, bedanya `Coroutine::sleep` diimplementasikan oleh penjadwal [coroutine](/coroutine?id=penjadwalan-coroutine). Infrastruktur akan melakukan `yield` pada coroutine saat ini, menyerahkan time slice, dan menambahkan timer asinkron. Saat timeout tiba, akan melakukan `resume` pada coroutine saat ini untuk melanjutkan eksekusi.

Menggunakan interface `sleep` bisa dengan mudah mengimplementasikan fungsi timeout.

```php
Swoole\Coroutine\System::sleep(float $seconds): void
```

  * **Parameter** 

    * **`float $seconds`**
      * **Fungsi**: Waktu tidur [harus lebih dari `0`, maksimal satu hari (`86400` detik)]
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

  * **Contoh Penggunaan**  

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9502);

    $server->on('Request', function($request, $response) {
        // Tunggu 200ms sebelum mengirim respons ke browser
        Swoole\Coroutine\System::sleep(0.2);
        $response->end("<h1>Hello Swoole!</h1>");
    });

    $server->start();
    ```

### exec()

Menjalankan perintah shell. Infrastruktur otomatis melakukan [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine).

```php
Swoole\Coroutine\System::exec(string $cmd): array
```

  * **Parameter** 

    * **`string $cmd`**
      * **Fungsi**: Perintah `shell` yang akan dijalankan
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

  * **Return Value**

    * Mengembalikan `false` jika gagal, mengembalikan array jika berhasil, berisi kode status keluar proses, sinyal, dan konten output.

    ```php
    array(
        'code'   => 0,  // Kode status keluar proses
        'signal' => 0,  // Sinyal
        'output' => '', // Konten output
    );
    ```

  * **Contoh Penggunaan**  

    ```php
    Swoole\Coroutine\run(function() {
        $ret = Swoole\Coroutine\System::exec("md5sum ".__FILE__);
    });
    ```

  * **Catatan**

  !> Jika eksekusi perintah script terlalu lama, akan menyebabkan timeout keluar. Dalam kasus ini, bisa diatasi dengan memperbesar [socket_read_timeout](/coroutine_client/init?id=aturan-timeout).

### gethostbyname()

Meresolusi nama domain menjadi IP. Implementasi simulasi berdasarkan thread pool sinkron, infrastruktur otomatis melakukan [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine).

```php
Swoole\Coroutine\System::gethostbyname(string $domain, int $family = AF_INET, float $timeout = -1): string|false
```

  * **Parameter** 

    * **`string $domain`**
      * **Fungsi**: Nama domain
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $family`**
      * **Fungsi**: Family alamat [`AF_INET` mengembalikan alamat `IPv4`, `AF_INET6` mengembalikan alamat `IPv6`]
      * **Bawaan**: `AF_INET`
      * **Nilai Lain**: `AF_INET6`

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: `-1`
      * **Nilai Lain**: Tidak ada

  * **Return Value**

    * Mengembalikan alamat `IP` yang sesuai dengan domain jika berhasil, `false` jika gagal. Bisa gunakan [swoole_last_error](/functions?id=swoole_last_error) untuk info error.

  * **Ekstensi**

    * **Kontrol Timeout**

      Parameter `$timeout` bisa mengontrol waktu tunggu coroutine. Jika tidak ada hasil dalam waktu yang ditentukan, coroutine akan segera mengembalikan `false` dan melanjutkan eksekusi ke bawah. Dalam implementasi infrastruktur, tugas asinkron ini akan ditandai sebagai `cancel`, dan `gethostbyname` tetap akan dieksekusi di thread pool `AIO`.

      Bisa memodifikasi `/etc/resolv.conf` untuk mengatur timeout fungsi `C` dasar `gethostbyname` dan `getaddrinfo`. Lihat [Mengatur Timeout dan Retry DNS](/learn_other?id=dns-resolution-timeout-and-retries)

  * **Contoh Penggunaan**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::gethostbyname("www.baidu.com", AF_INET, 0.5);
        echo $ip;
    });
    ```

### getaddrinfo()

Melakukan resolusi DNS, mencari alamat `IP` yang sesuai dengan nama domain.

Berbeda dengan `gethostbyname`, `getaddrinfo` mendukung lebih banyak pengaturan parameter dan bisa mengembalikan banyak hasil `IP`.

```php
Swoole\Coroutine\System::getaddrinfo(string $domain, int $family = AF_INET, int $socktype = SOCK_STREAM, int $protocol = STREAM_IPPROTO_TCP, string $service = null, float $timeout = -1): array|false
```

  * **Parameter** 

    * **`string $domain`**
      * **Fungsi**: Nama domain
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $family`**
      * **Fungsi**: Family alamat [`AF_INET` mengembalikan alamat `IPv4`, `AF_INET6` mengembalikan alamat `IPv6`]
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada
      
      !> Lihat dokumentasi `man getaddrinfo` untuk pengaturan parameter lainnya.

    * **`int $socktype`**
      * **Fungsi**: Tipe protokol
      * **Bawaan**: `SOCK_STREAM`
      * **Nilai Lain**: `SOCK_DGRAM`, `SOCK_RAW`

    * **`int $protocol`**
      * **Fungsi**: Protokol
      * **Bawaan**: `STREAM_IPPROTO_TCP`
      * **Nilai Lain**: `STREAM_IPPROTO_UDP`, `STREAM_IPPROTO_STCP`, `STREAM_IPPROTO_TIPC`, `0`

    * **`string $service`**
      * **Fungsi**:
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: `-1`
      * **Nilai Lain**: Tidak ada

  * **Return Value**

    * Mengembalikan array berisi banyak alamat `IP` jika berhasil, `false` jika gagal.

  * **Contoh Penggunaan**  

    ```php
    Swoole\Coroutine\run(function () {
        $ips = Swoole\Coroutine\System::getaddrinfo("www.baidu.com");
        var_dump($ips);
    });
    ```

### dnsLookup()

Query alamat domain.

Berbeda dengan `Coroutine\System::gethostbyname`, `Coroutine\System::dnsLookup` diimplementasikan langsung berdasarkan komunikasi jaringan klien `UDP`, bukan menggunakan fungsi `gethostbyname` dari `libc`.

!> Tersedia sejak Swoole versi >= `v4.4.3`, infrastruktur akan membaca `/etc/resolve.conf` untuk mendapatkan alamat server `DNS`, saat ini hanya mendukung resolusi domain `AF_INET(IPv4)`. Mulai Swoole versi >= `v4.7` bisa menggunakan parameter ketiga untuk mendukung `AF_INET6(IPv6)`.

```php
Swoole\Coroutine\System::dnsLookup(string $domain, float $timeout = 5, int $type = AF_INET): string|false
```

  * **Parameter** 

    * **`string $domain`**
      * **Fungsi**: Nama domain
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: `5`
      * **Nilai Lain**: Tidak ada

    * **`int $type`**
        * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
        * **Bawaan**: `AF_INET`
        * **Nilai Lain**: `AF_INET6`

    !> Parameter `$type` tersedia sejak Swoole versi >= `v4.7`.

  * **Return Value**

    * Mengembalikan alamat IP yang sesuai jika berhasil
    * Mengembalikan `false` jika gagal, bisa gunakan [swoole_last_error](/functions?id=swoole_last_error) untuk info error

  * **Error Umum**

    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED`: Domain ini tidak bisa di-resolve, query gagal
    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT`: Timeout resolusi, server DNS mungkin bermasalah, tidak bisa mengembalikan hasil dalam waktu yang ditentukan

  * **Contoh Penggunaan**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::dnsLookup("www.baidu.com");
        echo $ip;
    });
    ```

### wait()

Setara dengan [Process::wait](/process/process?id=wait) asli, bedanya API ini adalah versi coroutine yang akan menangguhkan coroutine. Bisa menggantikan fungsi `Swoole\Process::wait` dan `pcntl_wait`.

!> Tersedia sejak Swoole versi >= `v4.5.0`

```php
Swoole\Coroutine\System::wait(float $timeout = -1): array|false
```

* **Parameter** 

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout, negatif berarti tidak pernah timeout
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: `-1`
      * **Nilai Lain**: Tidak ada

* **Return Value**

  * Jika berhasil, mengembalikan array berisi `PID` proses anak, kode status keluar, dan sinyal `KILL`
  * Jika gagal, mengembalikan `false`

!> Setelah setiap proses anak dijalankan, proses induk harus mengirimkan coroutine untuk memanggil `wait()` (atau `waitPid()`) untuk memulihkannya, jika tidak proses anak akan menjadi zombie process, membuang-buang resource proses sistem operasi.  
Jika menggunakan coroutine, proses harus dibuat terlebih dahulu, lalu coroutine di dalam proses. Jangan sebaliknya, jika tidak, melakukan fork dengan coroutine akan menjadi sangat kompleks dan sulit ditangani oleh infrastruktur.

* **Contoh**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::wait();
    assert($status['pid'] === $process->pid);
    var_dump($status);
});
```

### waitPid()

Pada dasarnya sama dengan method `wait` di atas, bedanya API ini bisa menentukan proses tertentu untuk ditunggu.

!> Tersedia sejak Swoole versi >= `v4.5.0`

```php
Swoole\Coroutine\System::waitPid(int $pid, float $timeout = -1): array|false
```

* **Parameter** 

    * **`int $pid`**
      * **Fungsi**: ID proses
      * **Bawaan**: `-1` (berarti proses apa pun, setara dengan method `wait`)
      * **Nilai Lain**: Bilangan natural apa pun

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout, negatif berarti tidak pernah timeout
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: `-1`
      * **Nilai Lain**: Tidak ada

* **Return Value**

  * Jika berhasil, mengembalikan array berisi `PID` proses anak, kode status keluar, dan sinyal `KILL`
  * Jika gagal, mengembalikan `false`

!> Setelah setiap proses anak dijalankan, proses induk harus mengirimkan coroutine untuk memanggil `wait()` (atau `waitPid()`) untuk memulihkannya, jika tidak proses anak akan menjadi zombie process.

* **Contoh**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::waitPid($process->pid);
    var_dump($status);
});
```

### waitSignal()

Listener sinyal versi coroutine, akan memblokir coroutine saat ini sampai sinyal terpicu. Bisa menggantikan fungsi `Swoole\Process::signal` dan `pcntl_signal`.

!> Tersedia sejak Swoole versi >= `v4.5.0`

```php
Swoole\Coroutine\System::waitSignal(int $signo, float $timeout = -1): bool
```

  * **Parameter** 

    * **`int $signo`**
      * **Fungsi**: Tipe sinyal
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Konstanta seri SIG, seperti `SIGTERM`, `SIGKILL`, dll.

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout, negatif berarti tidak pernah timeout
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: `-1`
      * **Nilai Lain**: Tidak ada

  * **Return Value**

    * Mengembalikan `true` jika menerima sinyal
    * Mengembalikan `false` jika timeout tidak menerima sinyal

  * **Contoh**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    Coroutine\run(function () {
        $bool = System::waitSignal(SIGUSR1);
        var_dump($bool);
    });
});
$process->start();
sleep(1);
$process::kill($process->pid, SIGUSR1);
```

### waitEvent()

Listener sinyal versi coroutine, akan memblokir coroutine saat ini sampai sinyal terpicu. Menunggu event IO, bisa menggantikan fungsi terkait `swoole_event`.

!> Tersedia sejak Swoole versi >= `v4.5`

```php
Swoole\Coroutine\System::waitEvent(mixed $socket, int $events = SWOOLE_EVENT_READ, float $timeout = -1): int | false
```

* **Parameter** 

    * **`mixed $socket`**
      * **Fungsi**: File descriptor (tipe apa pun yang bisa dikonversi ke fd, seperti objek Socket, resource, dll.)
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`int $events`**
      * **Fungsi**: Tipe event
      * **Bawaan**: `SWOOLE_EVENT_READ`
      * **Nilai Lain**: `SWOOLE_EVENT_WRITE` atau `SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE`

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout, negatif berarti tidak pernah timeout
      * **Satuan**: Detik, presisi minimal milidetik (`0.001` detik)
      * **Bawaan**: `-1`
      * **Nilai Lain**: Tidak ada

* **Return Value**

  * Mengembalikan jumlah tipe event yang terpicu (mungkin beberapa bit), terkait dengan nilai yang dimasukkan di parameter `$events`
  * Mengembalikan `false` jika gagal, bisa gunakan [swoole_last_error](/functions?id=swoole_last_error) untuk info error

* **Contoh**

> Kode blocking sinkron bisa diubah menjadi non-blocking coroutine melalui API ini.

```php
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = stream_socket_client('tcp://www.qq.com:80', $errno, $errstr, 30);
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
    assert($events === SWOOLE_EVENT_WRITE);
    fwrite($client, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ);
    assert($events === SWOOLE_EVENT_READ);
    $response = fread($client, 8192);
    echo $response;
});
```
