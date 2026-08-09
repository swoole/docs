# Coroutine\Barrier

Di [Swoole Library](https://github.com/swoole/library), infrastruktur menyediakan alat manajemen konkurensi coroutine yang lebih praktis: `Coroutine\Barrier`, atau penghalang coroutine. Diimplementasikan berdasarkan PHP reference counting dan Coroutine API.

Dibandingkan dengan [Coroutine\WaitGroup](/coroutine/wait_group), `Coroutine\Barrier` lebih sederhana penggunaannya. Cukup dengan melewatkan parameter atau sintaks `use` pada closure, lalu gunakan di fungsi coroutine anak.

!> Tersedia sejak Swoole versi >= v4.5.5.

## Contoh Penggunaan

```php
use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;

run(function () {
    $barrier = Barrier::make();

    $count = 0;
    $N = 4;

    foreach (range(1, $N) as $i) {
        Coroutine::create(function () use ($barrier, &$count) {
            System::sleep(0.5);
            $count++;
        });
    }

    Barrier::wait($barrier);
    
    assert($count == $N);
});
```

## Alur Eksekusi

* Pertama, gunakan `Barrier::make()` untuk membuat barrier coroutine baru
* Di coroutine anak, gunakan sintaks `use` untuk melewatkan barrier, menambah reference count
* Di posisi yang perlu menunggu, tambahkan `Barrier::wait($barrier)`, ini akan otomatis menangguhkan coroutine saat ini, menunggu coroutine anak yang mereferensi barrier ini keluar
* Saat coroutine anak keluar, reference count objek `$barrier` akan berkurang, hingga mencapai `0`
* Saat semua coroutine anak selesai memproses tugas dan keluar, reference count objek `$barrier` menjadi `0`, di fungsi destruktor objek `$barrier`, infrastruktur akan otomatis melanjutkan coroutine yang ditangguhkan, kembali dari fungsi `Barrier::wait($barrier)`

`Coroutine\Barrier` adalah pengontrol konkurensi yang lebih mudah digunakan dibandingkan [WaitGroup](/coroutine/wait_group) dan [Channel](/coroutine/channel), secara signifikan meningkatkan pengalaman pengguna dalam pemrograman konkuren PHP.
