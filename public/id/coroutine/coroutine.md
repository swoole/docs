# API Coroutine

> Sebaiknya baca [ikhtisar](/coroutine) terlebih dahulu untuk memahami konsep dasar coroutine sebelum membaca bagian ini.

## Method

### set()

Pengaturan coroutine, mengatur opsi terkait coroutine.

```php
Swoole\Coroutine::set(array $options);
```

| Parameter                      | Stabil Sejak Versi | Fungsi                                                                                                                                                   |
|-------------------------------|---------|----------------------------------------------------------------------------------------------------------------------------------------------------------|
| max_coroutine                 | -       | Mengatur jumlah maksimum global coroutine. Jika terlampaui, coroutine baru tidak dapat dibuat. Di Server akan ditimpa oleh [server->max_coroutine](/server/setting?id=max_coroutine). |
| stack_size/c_stack_size       | -       | Mengatur ukuran memori C stack awal untuk satu coroutine, bawaan `2M`.                                                                                  |
| log_level                     | v4.0.0  | Level log, [lihat detail](/consts?id=logging-level).                                                                                                    |
| trace_flags                   | v4.0.0  | Trace flags, [lihat detail](/consts?id=trace-flags).                                                                                                    |
| socket_connect_timeout        | v4.2.10 | Timeout koneksi, **lihat [aturan timeout klien](/coroutine_client/init?id=timeout-rules)**.                                                             |
| socket_read_timeout           | v4.3.0  | Timeout baca, **lihat [aturan timeout klien](/coroutine_client/init?id=timeout-rules)**.                                                                |
| socket_write_timeout          | v4.3.0  | Timeout tulis, **lihat [aturan timeout klien](/coroutine_client/init?id=timeout-rules)**.                                                               |
| socket_dns_timeout            | v4.4.0  | Timeout resolusi domain, **lihat [aturan timeout klien](/coroutine_client/init?id=timeout-rules)**.                                                     |
| socket_timeout                | v4.2.10 | Timeout kirim/terima, **lihat [aturan timeout klien](/coroutine_client/init?id=timeout-rules)**.                                                        |
| dns_cache_expire              | v4.2.11 | Mengatur waktu kedaluwarsa cache DNS Swoole, dalam detik, bawaan 60 detik.                                                                              |
| dns_cache_capacity            | v4.2.11 | Mengatur kapasitas cache DNS Swoole, bawaan 1000.                                                                                                       |
| hook_flags                    | v4.4.0  | Konfigurasi cakupan hook coroutine satu-klik, lihat [Coroutine Satu-Klik](/runtime).                                                                    |
| enable_preemptive_scheduler   | v4.4.0  | Mengaktifkan penjadwalan preemptive coroutine, waktu eksekusi maksimum coroutine adalah 10ms, akan menimpa [konfigurasi ini](/other/config).            |
| dns_server                    | v4.5.0  | Mengatur server untuk query DNS, bawaan `"8.8.8.8"`.                                                                                                   |
| exit_condition                | v4.5.0  | Masukan `callable` yang mengembalikan bool, kustomisasi kondisi keluar reactor. Contoh: `Co::set(['exit_condition' => function () {return Co::stats()['coroutine_num'] === 0;}]);` |
| enable_deadlock_check         | v4.6.0  | Mengatur apakah akan mengaktifkan deteksi deadlock coroutine, bawaan aktif.                                                                             |
| deadlock_check_disable_trace  | v4.6.0  | Mengatur apakah akan menampilkan stack frame deteksi deadlock coroutine.                                                                                |
| deadlock_check_limit          | v4.6.0  | Membatasi jumlah output maksimum saat deteksi deadlock coroutine.                                                                                       |
| deadlock_check_depth          | v4.6.0  | Membatasi jumlah stack frame yang dikembalikan saat deteksi deadlock coroutine.                                                                         |
| max_concurrency               | v4.8.2  | Jumlah maksimum permintaan konkuren.                                                                                                                    |

### getOptions()

Mendapatkan opsi terkait coroutine yang telah diatur.

!> Tersedia sejak Swoole versi >= `v4.6.0`

```php
Swoole\Coroutine::getOptions(): null|array;
```

### create()

Membuat coroutine baru dan langsung menjalankannya.

```php
Swoole\Coroutine::create(callable $function, ...$args): int|false
go(callable $function, ...$args): int|false // Lihat konfigurasi use_shortname di php.ini
```

* **Parameter**

    * **`callable $function`**
      * **Fungsi**: Kode yang akan dieksekusi oleh coroutine, harus berupa `callable`. Jumlah total coroutine yang bisa dibuat dibatasi oleh [server->max_coroutine](/server/setting?id=max_coroutine).
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

* **Nilai Kembalian**

    * Mengembalikan `false` jika gagal dibuat
    * Mengembalikan `ID` coroutine jika berhasil dibuat

!> Karena infrastruktur akan memprioritaskan eksekusi kode coroutine anak, `Coroutine::create` baru akan kembali ketika coroutine anak ditangguhkan, lalu melanjutkan eksekusi kode coroutine saat ini.

  * **Urutan Eksekusi**

    Membuat coroutine baru bersarang di dalam coroutine menggunakan `go`. Karena model coroutine Swoole adalah single-process single-thread:

    * Coroutine anak yang dibuat dengan `go` akan dieksekusi terlebih dahulu. Setelah coroutine anak selesai atau ditangguhkan, eksekusi akan kembali ke coroutine induk.
    * Jika coroutine induk keluar setelah coroutine anak ditangguhkan, hal itu tidak memengaruhi eksekusi coroutine anak.

    ```php
    \Co\run(function() {
        go(function () {
            Co::sleep(3.0);
            go(function () {
                Co::sleep(2.0);
                echo "co[3] end\n";
            });
            echo "co[2] end\n";
        });

        Co::sleep(1.0);
        echo "co[1] end\n";
    });
    ```

* **Overhead Coroutine**

  Setiap coroutine independen dan membutuhkan ruang memori sendiri (stack memory). Di PHP-7.2, infrastruktur mengalokasikan `8K` stack untuk menyimpan variabel coroutine. Ukuran `zval` adalah `16 byte`, jadi stack `8K` maksimal bisa menyimpan `512` variabel. Memori stack coroutine akan otomatis diperluas jika melebihi `8K`.

  Memori stack yang dialokasikan saat coroutine keluar akan dibebaskan.

  * `PHP-7.1`, `PHP-7.0` secara bawaan mengalokasikan `256K` stack memory
  * Bisa panggil `Co::set(['stack_size' => 4096])` untuk mengubah ukuran stack memory bawaan

### defer()

`defer` digunakan untuk pelepasan resource, akan dipanggil **sebelum coroutine ditutup** (yaitu saat fungsi coroutine selesai dieksekusi), bahkan jika exception dilempar, `defer` yang sudah terdaftar tetap akan dieksekusi.

!> Swoole versi >= 4.2.9

```php
Swoole\Coroutine::defer(callable $function);
defer(callable $function); // API nama pendek
```

!> Perlu diperhatikan, urutan pemanggilannya adalah terbalik (LIFO), yaitu `defer` yang didaftarkan lebih awal akan dieksekusi lebih akhir. Urutan terbalik ini sesuai dengan logika pelepasan resource yang benar, karena resource yang dialokasikan kemudian mungkin bergantung pada resource yang dialokasikan sebelumnya.

  * **Contoh**

```php
go(function () {
    defer(function () use ($db) {
        $db->close();
    });
});
```

### exists()

Memeriksa apakah coroutine yang ditentukan ada.

```php
Swoole\Coroutine::exists(int $cid = 0): bool
```

!> Swoole versi >= v4.3.0

  * **Contoh**

```php
\Co\run(function () {
    go(function () {
        go(function () {
            Co::sleep(0.001);
            var_dump(Co::exists(Co::getPcid())); // 1: true
        });
        go(function () {
            Co::sleep(0.003);
            var_dump(Co::exists(Co::getPcid())); // 3: false
        });
        Co::sleep(0.002);
        var_dump(Co::exists(Co::getPcid())); // 2: false
    });
});
```

### getCid()

Mendapatkan `ID` unik dari coroutine saat ini, aliasnya adalah `getuid`, merupakan bilangan bulat positif unik dalam satu proses.

```php
Swoole\Coroutine::getCid(): int
```

* **Nilai Kembalian**

    * Mengembalikan `ID` coroutine saat ini jika berhasil
    * Mengembalikan `-1` jika sedang tidak berada di lingkungan coroutine

### getPcid()

Mendapatkan `ID` induk dari coroutine saat ini.

```php
Swoole\Coroutine::getPcid([$cid]): int
```

!> Swoole versi >= v4.3.0

* **Parameter**

    * **`int $cid`**
      * **Fungsi**: ID coroutine, parameter opsional. Bisa memasukkan `id` coroutine tertentu untuk mendapatkan `id` induknya.
      * **Bawaan**: Coroutine saat ini
      * **Nilai Lain**: Tidak ada

  * **Contoh**

```php
var_dump(Co::getPcid());
\Co\run(function () {
    var_dump(Co::getPcid());
    go(function () {
        var_dump(Co::getPcid());
        go(function () {
            var_dump(Co::getPcid());
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
        });
        var_dump(Co::getPcid());
    });
    var_dump(Co::getPcid());
});
var_dump(Co::getPcid());

// --EXPECT--

// bool(false)
// int(-1)
// int(1)
// int(2)
// int(3)
// int(3)
// int(3)
// int(1)
// int(-1)
// bool(false)
```

!> Memanggil `getPcid` di luar coroutine bersarang akan mengembalikan `-1` (dibuat dari luar ruang coroutine)  
Memanggil `getPcid` di dalam non-coroutine akan mengembalikan `false` (tidak ada coroutine induk)  
`0` adalah `id` yang dicadangkan, tidak akan muncul di nilai kembali

!> Coroutine tidak memiliki hubungan induk-anak yang nyata; coroutine beroperasi secara independen dan terisolasi. `Pcid` ini bisa dipahami sebagai `id` coroutine yang membuat coroutine saat ini.

  * **Penggunaan**

    * **Merangkai beberapa stack panggilan coroutine**

```php
\Co\run(function () {
    go(function () {
        $ptrace = Co::getBackTrace(Co::getPcid());
        var_dump(array_merge($ptrace, Co::getBackTrace(Co::getCid())));
    });
});
```

### getContext()

Mendapatkan objek konteks dari coroutine saat ini.

```php
Swoole\Coroutine::getContext([int $cid = 0]): Swoole\Coroutine\Context
```

!> Swoole versi >= v4.3.0

* **Parameter**

    * **`int $cid`**
      * **Fungsi**: `CID` coroutine, parameter opsional
      * **Bawaan**: `CID` coroutine saat ini
      * **Nilai Lain**: Tidak ada

  * **Fungsi**

    * Konteks otomatis dibersihkan setelah coroutine keluar (jika tidak ada referensi coroutine lain atau variabel global)
    * Tanpa overhead registrasi dan pemanggilan `defer` (tidak perlu mendaftarkan method pembersihan, tidak perlu memanggil fungsi untuk membersihkan)
    * Tanpa overhead kalkulasi hash untuk konteks yang diimplementasikan dengan array PHP (bermanfaat saat jumlah coroutine sangat besar)
    * `Co\Context` menggunakan `ArrayObject`, memenuhi berbagai kebutuhan penyimpanan (bisa sebagai objek, juga bisa dioperasikan sebagai array)

  * **Contoh**

```php
function func(callable $fn, ...$args)
{
    go(function () use ($fn, $args) {
        $fn(...$args);
        echo 'Coroutine#' . Co::getCid() . ' exit' . PHP_EOL;
    });
}

/**
* Compatibility for lower version
* @param object|Resource $object
* @return int
*/
function php_object_id($object)
{
    static $id = 0;
    static $map = [];
    $hash = spl_object_hash($object);
    return $map[$hash] ?? ($map[$hash] = ++$id);
}

class Resource
{
    public function __construct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' constructed' . PHP_EOL;
    }

    public function __destruct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' destructed' . PHP_EOL;
    }
}

$context = new Co\Context();
assert($context instanceof ArrayObject);
assert(Co::getContext() === null);
func(function () {
    $context = Co::getContext();
    assert($context instanceof Co\Context);
    $context['resource1'] = new Resource;
    $context->resource2 = new Resource;
    func(function () {
        Co::getContext()['resource3'] = new Resource;
        Co::yield();
        Co::getContext()['resource3']->resource4 = new Resource;
        Co::getContext()->resource5 = new Resource;
    });
});
Co::resume(2);

Swoole\Event::wait();

// --EXPECT--
// Resource#1 constructed
// Resource#2 constructed
// Resource#3 constructed
// Coroutine#1 exit
// Resource#2 destructed
// Resource#1 destructed
// Resource#4 constructed
// Resource#5 constructed
// Coroutine#2 exit
// Resource#5 destructed
// Resource#3 destructed
// Resource#4 destructed
```

### yield()

Secara manual menyerahkan hak eksekusi coroutine saat ini. Bukan berdasarkan [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine) berbasis IO.

Method ini memiliki alias lain: `Coroutine::suspend()`

!> Harus digunakan berpasangan dengan method `Coroutine::resume()`. Setelah coroutine `yield`, harus di-`resume` oleh coroutine eksternal lain, jika tidak akan terjadi kebocoran coroutine.

```php
Swoole\Coroutine::yield();
```

  * **Contoh**

```php
$cid = go(function () {
    echo "co 1 start\n";
    Co::yield();
    echo "co 1 end\n";
});

go(function () use ($cid) {
    echo "co 2 start\n";
    Co::sleep(0.5);
    Co::resume($cid);
    echo "co 2 end\n";
});
Swoole\Event::wait();
```

### resume()

Secara manual melanjutkan coroutine agar terus berjalan, bukan berdasarkan [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine) berbasis IO.

!> Saat coroutine saat ini dalam keadaan ditangguhkan, coroutine lain bisa menggunakan `resume` untuk membangunkannya kembali.

```php
Swoole\Coroutine::resume(int $coroutineId);
```

* **Parameter**

    * **`int $coroutineId`**
      * **Fungsi**: `ID` coroutine yang akan dilanjutkan
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

  * **Contoh**

```php
$id = go(function(){
    $id = Co::getuid();
    echo "start coro $id\n";
    Co::suspend();
    echo "resume coro $id @1\n";
    Co::suspend();
    echo "resume coro $id @2\n";
});
echo "start to resume $id @1\n";
Co::resume($id);
echo "start to resume $id @2\n";
Co::resume($id);
echo "main\n";
Swoole\Event::wait();

// --EXPECT--
// start coro 1
// start to resume 1 @1
// resume coro 1 @1
// start to resume 1 @2
// resume coro 1 @2
// main
```

### list()

Menelusuri semua coroutine dalam proses saat ini.

```php
Swoole\Coroutine::list(): Swoole\Coroutine\Iterator
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> Sebelum `v4.3.0` gunakan `listCoroutines`. `list` tersedia mulai `v4.1.0`.

* **Nilai Kembalian**

    * Mengembalikan iterator, bisa ditelusuri dengan `foreach`, atau dikonversi ke array dengan `iterator_to_array`

```php
$coros = Swoole\Coroutine::listCoroutines();
foreach($coros as $cid)
{
    var_dump(Swoole\Coroutine::getBackTrace($cid));
}
```

### stats()

Mendapatkan status coroutine.

```php
Swoole\Coroutine::stats(): array
```

* **Nilai Kembalian**

key | Fungsi
---|---
event_num | Jumlah event reactor saat ini
signal_listener_num | Jumlah listener sinyal saat ini
aio_task_num | Jumlah tugas IO asinkron (aio di sini merujuk pada file IO atau DNS, bukan network IO lainnya)
aio_worker_num | Jumlah thread pekerja IO asinkron
c_stack_size | Ukuran C stack setiap coroutine
coroutine_num | Jumlah coroutine yang sedang berjalan
coroutine_peak_num | Puncak jumlah coroutine yang berjalan
coroutine_last_cid | ID coroutine terakhir yang dibuat

  * **Contoh**

```php
var_dump(Swoole\Coroutine::stats());

array(1) {
  ["c_stack_size"]=>
  int(2097152)
  ["coroutine_num"]=>
  int(132)
  ["coroutine_peak_num"]=>
  int(2)
}
```

### getBackTrace()

Mendapatkan stack panggilan fungsi coroutine.

```php
Swoole\Coroutine::getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
```

!> Swoole versi >= v4.1.0

* **Parameter**

    * **`int $cid`**
      * **Fungsi**: `CID` coroutine
      * **Bawaan**: `CID` coroutine saat ini
      * **Nilai Lain**: Tidak ada

    * **`int $options`**
      * **Fungsi**: Mengatur opsi
      * **Bawaan**: `DEBUG_BACKTRACE_PROVIDE_OBJECT` [apakah mengisi indeks `object`]
      * **Nilai Lain**: `DEBUG_BACKTRACE_IGNORE_ARGS` [apakah mengabaikan indeks args, termasuk semua parameter function/method, bisa menghemat memori]

    * **`int limit`**
      * **Fungsi**: Membatasi jumlah stack frame yang dikembalikan
      * **Bawaan**: `0`
      * **Nilai Lain**: Tidak ada

* **Nilai Kembalian**

    * Jika coroutine yang ditentukan tidak ada, akan mengembalikan `false`
    * Jika berhasil mengembalikan array, formatnya sama dengan nilai kembali fungsi [debug_backtrace](https://www.php.net/manual/zh/function.debug-backtrace.php)

  * **Contoh**

```php
function test1() {
    test2();
}

function test2() {
    while(true) {
        Co::sleep(10);
        echo __FUNCTION__." \n";
    }
}
\Co\run(function () {
    $cid = go(function () {
        test1();
    });

    go(function () use ($cid) {
        while(true) {
            echo "BackTrace[$cid]:\n-----------------------------------------------\n";
            var_dump(Co::getBackTrace($cid))."\n";
            Co::sleep(3);
        }
    });
});
Swoole\Event::wait();
```

### printBackTrace()

Mencetak stack panggilan fungsi coroutine. Parameter sama dengan `getBackTrace`.

!> Tersedia sejak Swoole versi >= `v4.6.0`

```php
Swoole\Coroutine::printBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0);
```

### getElapsed()

Mendapatkan waktu berjalan coroutine untuk analisis statistik atau menemukan coroutine zombie.

!> Tersedia sejak Swoole versi >= `v4.5.0`

```php
Swoole\Coroutine::getElapsed([$cid]): int
```
* **Parameter**

    * **`int $cid`**
      * **Fungsi**: Parameter opsional, `CID` coroutine
      * **Bawaan**: `CID` coroutine saat ini
      * **Nilai Lain**: Tidak ada

* **Nilai Kembalian**

    * Waktu berjalan coroutine dalam float, presisi milidetik

### cancel()

Digunakan untuk membatalkan suatu coroutine, tapi tidak bisa membatalkan coroutine saat ini.

!> Tersedia sejak Swoole versi >= `v4.7.0`

```php
Swoole\Coroutine::cancel(int $cid, bool $throw_exception = false): bool
```
* **Parameter**

    * **`int $cid`**
        * **Fungsi**: `CID` coroutine
        * **Bawaan**: Tidak ada
        * **Nilai Lain**: Tidak ada
    * **`bool $throw_exception`**
      * **Fungsi**: Membatalkan coroutine, dan melempar exception `Swoole\Coroutine\CanceledException` di dalam coroutine yang dibatalkan
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada
      * Tersedia setelah versi `v6.1.0`

* **Nilai Kembalian**

    * Mengembalikan `true` jika berhasil, `false` jika gagal
    * Jika gagal, panggil [swoole_last_error()](/functions?id=swoole_last_error) untuk melihat info error

#### Catatan
* Saat coroutine sedang membaca/menulis file, tidak bisa dibatalkan, `cancel()` akan gagal dengan kode error `SWOOLE_ERROR_CO_CANNOT_CANCEL`
* Jika `$throw_exception` diatur ke `true`, fungsi ini akan selalu mengembalikan `true`
* Coroutine yang dibatalkan harus menangkap exception `Swoole\Coroutine\CanceledException`, jika tidak akan menghasilkan `Fatal Error` dan menyebabkan proses keluar

```php
Co\run(function () {
    $cid = Co\go(function () {
        try {
            while (true) {
                System::sleep(0.1);
                echo "co 2 running\n";
            }
            var_dump('end');
        } catch (Swoole\Coroutine\CanceledException $e) {
            var_dump('cancelled');
        }
    });

    System::sleep(0.3);
    Co::cancel($cid, true);
    System::sleep(0.2);
    echo "co 1 end\n";
});
```

### isCanceled()

Digunakan untuk mengecek apakah operasi saat ini dibatalkan secara manual.

!> Tersedia sejak Swoole versi >= `v4.7.0`

```php
Swoole\Coroutine::isCanceled(): bool
```

* **Nilai Kembalian**

    * Jika pembatalan manual berakhir normal, mengembalikan `true`, jika gagal mengembalikan `false`

#### Contoh

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

run(function () {
    $chan = new Coroutine\Channel(1);
    $cid = Coroutine::getCid();
    go(function () use ($cid) {
        System::sleep(0.002);
        assert(Coroutine::cancel($cid) === true);
    });

    assert($chan->push("hello world [1]", 100) === true);
    assert(Coroutine::isCanceled() === false);
    assert($chan->errCode === SWOOLE_CHANNEL_OK);

    assert($chan->push("hello world [2]", 100) === false);
    assert(Coroutine::isCanceled() === true);
    assert($chan->errCode === SWOOLE_CHANNEL_CANCELED);

    echo "Done\n";
});
```

### enableScheduler()

Mengaktifkan sementara penjadwalan preemptive coroutine.

!> Tersedia sejak Swoole versi >= `v4.4.0`

```php
Swoole\Coroutine::enableScheduler();
```

### disableScheduler()

Menonaktifkan sementara penjadwalan preemptive coroutine.

!> Tersedia sejak Swoole versi >= `v4.4.0`

```php
Swoole\Coroutine::disableScheduler();
```

### getStackUsage()

Mendapatkan penggunaan memori stack PHP saat ini.

!> Tersedia sejak Swoole versi >= `v4.8.0`

```php
Swoole\Coroutine::getStackUsage([$cid]): int
```

* **Parameter**

    * **`int $cid`**
        * **Fungsi**: Parameter opsional, `CID` coroutine
        * **Bawaan**: `CID` coroutine saat ini
        * **Nilai Lain**: Tidak ada

### join()

Menjalankan banyak coroutine secara konkuren.

!> Tersedia sejak Swoole versi >= `v4.8.0`

```php
Swoole\Coroutine::join(array $cid_array, float $timeout = -1): bool
```

* **Parameter**

    * **`array $cid_array`**
        * **Fungsi**: Array `CID` coroutine yang akan dijalankan
        * **Bawaan**: Tidak ada
        * **Nilai Lain**: Tidak ada

    * **`float $timeout`**
        * **Fungsi**: Total waktu timeout, setelah timeout akan segera kembali. Coroutine yang sedang berjalan akan tetap selesai, tidak dihentikan.
        * **Bawaan**: -1
        * **Nilai Lain**: Tidak ada

* **Nilai Kembalian**

    * Mengembalikan `true` jika berhasil, `false` jika gagal
    * Jika gagal, panggil [swoole_last_error()](/functions?id=swoole_last_error) untuk info error

* **Contoh Penggunaan**

```php
use Swoole\Coroutine;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

run(function () {
    $status = Coroutine::join([
        go(function () use (&$result) {
            $result['baidu'] = strlen(file_get_contents('https://www.baidu.com/'));
        }),
        go(function () use (&$result) {
            $result['google'] = strlen(file_get_contents('https://www.google.com/'));
        })
    ], 1);
    var_dump($result, $status, swoole_strerror(swoole_last_error(), 9));
});
```

## Fungsi

### batch()

Menjalankan banyak coroutine secara konkuren, dan mengembalikan nilai kembali dari method coroutine tersebut melalui array.

!> Tersedia sejak Swoole versi >= `v4.5.2`

```php
Swoole\Coroutine\batch(array $tasks, float $timeout = -1): array
```

* **Parameter**

    * **`array $tasks`**
      * **Fungsi**: Array callback method. Jika `key` ditentukan, nilai kembali juga akan diarahkan oleh `key` tersebut.
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Total waktu timeout. Setelah timeout akan segera kembali. Coroutine yang berjalan akan tetap selesai tanpa dihentikan.
      * **Bawaan**: -1
      * **Nilai Lain**: Tidak ada

* **Nilai Kembalian**

    * Mengembalikan array berisi nilai kembali callback. Jika parameter `$tasks` menentukan `key`, nilai kembali akan diarahkan oleh `key` tersebut.

* **Contoh Penggunaan**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\batch;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = batch([
        'file_put_contents' => function () {
            return file_put_contents(__DIR__ . '/greeter.txt', "Hello,Swoole.");
        },
        'gethostbyname' => function () {
            return gethostbyname('localhost');
        },
        'file_get_contents' => function () {
            return file_get_contents(__DIR__ . '/greeter.txt');
        },
        'sleep' => function () {
            sleep(1);
            return true;
        },
        'usleep' => function () {
            usleep(1000);
            return true;
        },
    ], 0.1);
    $use = microtime(true) - $use;
    echo "Use {$use}s, Result:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Use {$end_time}s, Done\n";
```

### parallel()

Menjalankan banyak coroutine secara konkuren.

!> Tersedia sejak Swoole versi >= `v4.5.3`

```php
Swoole\Coroutine\parallel(int $n, callable $fn): void
```

* **Parameter**

    * **`int $n`**
      * **Fungsi**: Mengatur jumlah maksimum coroutine menjadi `$n`
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`callable $fn`**
      * **Fungsi**: Fungsi callback yang akan dijalankan
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

* **Contoh Penggunaan**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\parallel;

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = [];
    parallel(2, function () use (&$results) {
        System::sleep(0.2);
        $results[] = System::gethostbyname('localhost');
    });
    $use = microtime(true) - $use;
    echo "Use {$use}s, Result:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Use {$end_time}s, Done\n";
```

### map()

Mirip dengan [array_map](https://www.php.net/manual/en/function.array-map.php), menerapkan fungsi callback ke setiap elemen array.

!> Tersedia sejak Swoole versi >= `v4.5.5`

```php
Swoole\Coroutine\map(array $list, callable $fn, float $timeout = -1): array
```

* **Parameter**

    * **`array $list`**
      * **Fungsi**: Array untuk menjalankan fungsi `$fn`
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`callable $fn`**
      * **Fungsi**: Fungsi callback yang akan diterapkan ke setiap elemen di `$list`
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Total waktu timeout, setelah timeout akan segera kembali. Coroutine yang berjalan akan tetap selesai tanpa dihentikan.
      * **Bawaan**: -1
      * **Nilai Lain**: Tidak ada

* **Contoh Penggunaan**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\map;

function factorial(int $n): int
{
    return array_product(range($n, 1));
}

Coroutine\run(function () {
    $results = map([2, 3, 4], 'factorial'); 
    print_r($results);
});
```

### deadlock_check()

Deteksi deadlock coroutine, saat dipanggil akan menampilkan informasi stack terkait.

Bawaan **aktif**, setelah [EventLoop](learn?id=apa-itu-eventloop) berakhir, jika ada deadlock coroutine, infrastruktur akan otomatis memanggilnya.

Bisa dinonaktifkan dengan mengatur `enable_deadlock_check` di [Coroutine::set](/coroutine/coroutine?id=set).

!> Tersedia sejak Swoole versi >= `v4.6.0`

```php
Swoole\Coroutine\deadlock_check();
```
