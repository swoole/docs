# Coroutine Lock

* `Swoole 6.0` nambahin coroutine lock yang bisa dishare antar proses dan thread. Lock ini desainnya non-blocking, bisa mewujudkan sinkronisasi coroutine yang efisien di lingkungan multi-proses dan multi-thread.
* Kalo compile pake `--enable-iouring` dan kernel Linux dukung `io_uring futex`, coroutine lock Swoole bakal berbasis `io_uring futex` buat sinkronisasi. Coroutine bakal nunggu lock dengan antrian yang efisien, performa naik signifikan.
* Kalo `io_uring futex` nggak diaktifkan, coroutine lock bakal pake mekanisme sleep berbasis exponential backoff, yaitu setiap kali gagal dapet lock, waktu tunggu naik 2^n milidetik (n = jumlah kegagalan). Cara ini walau hindari busy waiting, tapi nambah overhead CPU dan delay.
* Coroutine lock desainnya reentrant, ngizinin coroutine yang megang lock buat ngelakuin operasi lock berkali-kali dengan aman.

!> Jangan bikin lock di callback kayak [onReceive](/server/events?id=onreceive), nanti memory terus naik, bocor memory.

!> Lock dan unlock harus dilakukan di coroutine yang sama, kalo nggak kondisi statis bakal rusak.

## Contoh Penggunaan
```php
use Swoole\Coroutine\Lock;
use Swoole\Coroutine\WaitGroup;
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

$lock = new Lock();
$waitGroup = new WaitGroup();

run(function() use ($lock, $waitGroup) {
    go(function() use ($lock, $waitGroup) {
        $waitGroup->add();
        $lock->lock();
        sleep(1);
        $lock->unlock();
        $waitGroup->done();
    });
    
    go(function() use ($lock, $waitGroup) {
        $waitGroup->add();
        $lock->lock(); // nunggu coroutine yang megang lock buat unlock
        sleep(1);
        $lock->unlock();
        $waitGroup->done();
    });
       
    echo 'Lock tidak nge-block proses';
    $waitGroup->wait();
});
```

## Method

### __construct()

Konstruktor.

```php
Swoole\Coroutine\Lock::__construct();
```

### lock()

Pas operasi lock dilakukan, kalo lock lagi dipegang coroutine lain, coroutine saat ini bakal secara aktif lepas kendali CPU dan masuk status gantung. Setelah coroutine yang megang lock panggil `unlock()` buat lepas lock, coroutine yang nunggu bakal dibangunin dan coba dapetin lock lagi.

```php
Swoole\Coroutine\Lock::lock(): bool;
```

* **Return Value**

    * Lock sukses balik `true`, saat itu bisa ubah variable shared.
    * Lock gagal balik `false`.

### trylock()

Pas operasi lock dilakukan, kalo lock lagi dipegang coroutine lain, fungsi ini langsung balik `false`, tanpa gantung coroutine atau lepas kendali CPU. Desain non-blocking ini ngasih fleksibilitas ke pemanggil buat handle kompetisi, misalnya: coba lagi, menyerah, atau jalanin logic lain.

```php
Swoole\Coroutine\Lock::trylock(): bool;
```

* **Return Value**

    * Lock sukses balik `true`, saat itu bisa ubah variable shared.
    * Lock gagal balik `false`, terserah user mau ngapain selanjutnya.

### unlock()

Pas coroutine yang megang lock panggil `unlock()` buat lepas lock:
  * Kalo `io_uring futex` diaktifkan: sistem bakal bangunin satu coroutine dari antrian tunggu secara tepat, memastikan perpindahan lock yang efisien dan teratur.
  * Kalo `io_uring futex` nggak diaktifkan: coroutine yang nunggu harus nunggu sampe waktu backoff-nya selesai, lalu bersaing lagi buat dapetin lock.

```php
Swoole\Coroutine\Lock::unlock(): bool;
```

* **Return Value**

    * Unlock sukses balik `true`.
    * Unlock gagal balik `false`.
