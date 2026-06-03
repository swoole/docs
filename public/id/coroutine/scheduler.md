# Coroutine\Scheduler

?> Semua [coroutine](/coroutine) harus [dibuat](/coroutine/coroutine?id=create) di dalam `coroutine container`. Saat program `Swoole` dijalankan, sebagian besar kasus akan otomatis membuat `coroutine container`. Ada tiga cara untuk menjalankan program dengan `Swoole`:

   - Memanggil method [start](/server/methods?id=start) dari program server [gaya asinkron](/server/init). Cara ini akan otomatis membuat `coroutine container` di [fungsi callback event](/server/events), lihat [enable_coroutine](/server/setting?id=enable_coroutine).
   - Memanggil method `start` dari 2 modul manajemen proses yang disediakan `Swoole`, yaitu [Process](/process/process) dan [Process\Pool](/process/process_pool). Cara ini akan membuat `coroutine container` saat proses dimulai, lihat parameter `enable_coroutine` di konstruktor kedua modul ini.
   - Cara lain dengan menulis coroutine secara langsung untuk menjalankan program, perlu membuat coroutine container terlebih dahulu (fungsi `Coroutine\run()`, bisa dipahami seperti fungsi `main` di Java, C), contoh:

* **Menjalankan layanan `HTTP` full coroutine**

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
echo 1;//tidak akan dieksekusi
```

* **Menambahkan 2 coroutine konkuren untuk melakukan sesuatu**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function() {
        var_dump(file_get_contents("http://www.xinhuanet.com/"));
    });

    Coroutine::create(function() {
        Coroutine::sleep(1);
        echo "done\n";
    });
});
echo 1;//akan dieksekusi
```

!> Tersedia di `Swoole v4.4+`.

!> Tidak boleh menyarangkan `Coroutine\run()`.  
Jika ada event yang belum diproses setelah `Coroutine\run()`, maka kode setelahnya tidak akan dieksekusi. Sebaliknya, jika tidak ada event, eksekusi akan berlanjut ke bawah, dan `Coroutine\run()` bisa dipanggil lagi.

Fungsi `Coroutine\run()` di atas sebenarnya adalah pembungkus untuk kelas `Swoole\Coroutine\Scheduler` (kelas penjadwal coroutine). Untuk detailnya, lihat method `Swoole\Coroutine\Scheduler`:

### set()

?> **Mengatur parameter runtime coroutine.**

?> Merupakan alias dari method `Coroutine::set`. Lihat dokumentasi [Coroutine::set](/coroutine/coroutine?id=set).

```php
Swoole\Coroutine\Scheduler->set(array $options): bool
```

  * **Contoh**

```php
$sch = new Swoole\Coroutine\Scheduler;
$sch->set(['max_coroutine' => 100]);
```

### getOptions()

?> **Mendapatkan parameter runtime coroutine yang telah diatur.** Tersedia sejak Swoole versi >= `v4.6.0`

?> Merupakan alias dari method `Coroutine::getOptions`. Lihat dokumentasi [Coroutine::getOptions](/coroutine/coroutine?id=getoptions).

```php
Swoole\Coroutine\Scheduler->getOptions(): null|array
```

### add()

?> **Menambahkan tugas.**

```php
Swoole\Coroutine\Scheduler->add(callable $fn, ... $args): bool
```

  * **Parameter** 

    * **`callable $fn`**
      * **Fungsi**: Fungsi callback
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`... $args`**
      * **Fungsi**: Parameter opsional, akan diteruskan ke coroutine
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

  * **Contoh**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;
$scheduler->add(function ($a, $b) {
    Coroutine::sleep(1);
    echo assert($a == 'hello') . PHP_EOL;
    echo assert($b == 12345) . PHP_EOL;
    echo "Done.\n";
}, "hello", 12345);

$scheduler->start();
```
  
  * **Catatan**

    !> Berbeda dengan fungsi `go`, coroutine yang ditambahkan di sini tidak akan langsung dieksekusi, melainkan menunggu sampai method `start` dipanggil untuk dijalankan bersama. Jika program hanya menambahkan coroutine tanpa memanggil `start`, fungsi coroutine `$fn` tidak akan dieksekusi.

### parallel()

?> **Menambahkan tugas paralel.**

?> Berbeda dengan method `add`, method `parallel` akan membuat coroutine paralel. Saat `start` dipanggil, akan menjalankan `$num` coroutine `$fn` secara bersamaan.

```php
Swoole\Coroutine\Scheduler->parallel(int $num, callable $fn, ... $args): bool
```

  * **Parameter** 

    * **`int $num`**
      * **Fungsi**: Jumlah coroutine yang akan dijalankan
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`callable $fn`**
      * **Fungsi**: Fungsi callback
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`... $args`**
      * **Fungsi**: Parameter opsional, akan diteruskan ke coroutine
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

  * **Contoh**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;

$scheduler->parallel(10, function ($t, $n) {
    Coroutine::sleep($t);
    echo "Co ".Coroutine::getCid()."\n";
}, 0.05, 'A');

$scheduler->start();
```

### start()

?> **Menjalankan program.**

?> Menelusuri tugas coroutine yang ditambahkan oleh method `add` dan `parallel`, lalu menjalankannya.

```php
Swoole\Coroutine\Scheduler->start(): bool
```

  * **Return Value**

    * Jika berhasil dijalankan, semua tugas yang ditambahkan akan dieksekusi, saat semua coroutine keluar, `start` akan mengembalikan `true`
    * Jika gagal, mengembalikan `false`, penyebabnya mungkin sudah berjalan atau sudah ada scheduler lain yang tidak bisa dibuat lagi.
