# Coroutine <!-- {docsify-ignore-all} -->

Bagian ini memperkenalkan beberapa konsep dasar korutin dan pertanyaan umum.

Mulai versi 4.0, `Swoole` menyediakan fitur lengkap `Coroutine` + `Channel`, menghadirkan model pemrograman `CSP` yang baru.

1. Developer dapat menulis kode sinkron tanpa sadar mencapai efek dan performa [IO asinkron](/learn?id=同步io异步io), menghindari logika kode yang tersebar akibat callback asinkron tradisional dan terjebak dalam banyak lapisan callback yang membuat kode tidak terawat
2. Karena level bawah mengenkapsulasi korutin, dibandingkan dengan framework korutin tradisional di lapisan `PHP`, developer tidak perlu menggunakan keyword [yield](https://www.php.net/manual/zh/language.generators.syntax.php) untuk menandai operasi korutin `IO`, sehingga tidak perlu memahami secara mendalam semantik `yield` atau mengubah setiap level panggilan menjadi `yield`, ini sangat meningkatkan efisiensi pengembangan
3. Menyediakan berbagai [klien korutin](/coroutine_client/init) lengkap yang dapat memenuhi kebutuhan sebagian besar developer.

## Apa itu Coroutine

Korutin bisa dipahami sederhana sebagai thread, hanya saja thread ini berada di ruang pengguna, tidak memerlukan partisipasi sistem operasi, biaya pembuatan, penghancuran, dan peralihan sangat rendah. Berbeda dengan thread, korutin tidak dapat memanfaatkan CPU multi-inti. Untuk memanfaatkan CPU multi-inti, perlu bergantung pada model multi-proses `Swoole`.

## Apa itu Channel

`Channel` dapat dipahami sebagai antrian pesan, khusus untuk korutin. Banyak korutin berkomunikasi melalui operasi `push` dan `pop` untuk memproduksi dan mengonsumsi pesan dalam antrian, mengirim atau menerima data untuk komunikasi antar korutin. Perlu dicatat bahwa `Channel` tidak bisa lintas proses, hanya dapat berkomunikasi antar korutin dalam satu proses `Swoole`. Aplikasi paling tipikal adalah [connection pool](/coroutine/conn_pool) dan [panggilan konkuren](/coroutine/multi_call).

## Apa itu Wadah Korutin

Gunakan `Coroutine::create` atau `go()` untuk membuat korutin (lihat [bagian alias](/other/alias?id=协程短名称)), di korutin yang dibuat barulah bisa menggunakan `API` korutin, dan korutin harus dibuat di dalam wadah korutin, lihat [wadah korutin](/coroutine/scheduler).

## Penjadwalan Korutin

Di sini akan dijelaskan secara sederhana apa itu penjadwalan korutin. Pertama, setiap korutin bisa dipahami sebagai thread. Kita tahu multi-threading untuk meningkatkan konkurensi program, begitu pula multi-korutin.

Setiap request pengguna akan membuat korutin, request selesai maka korutin berakhir. Jika ada ribuan request konkuren secara bersamaan, pada suatu saat di dalam proses bisa ada ribuan korutin, karena sumber daya CPU terbatas, kode korutin mana yang harus dijalankan?

Proses keputusan kode korutin mana yang akan dijalankan CPU adalah `penjadwalan korutin`. Bagaimana strategi penjadwalan `Swoole`?

- Pertama, saat menjalankan kode korutin, jika menemui `Co::sleep()` atau menghasilkan `IO` jaringan, misalnya `MySQL->query()`, ini pasti proses yang memakan waktu, `Swoole` akan menempatkan fd koneksi MySQL ini ke [EventLoop](/learn?id=什么是eventloop).
    * Kemudian CPU korutin ini diberikan ke korutin lain: **yaitu `yield` (ditangguhkan)**
    * Menunggu data MySQL kembali lalu melanjutkan eksekusi korutin ini: **yaitu `resume` (dilanjutkan)**

- Kedua, jika kode korutin memiliki kode intensif CPU, dapat mengaktifkan [enable_preemptive_scheduler](/other/config), Swoole akan memaksa korutin ini melepaskan CPU.

## Prioritas Korutin Induk-Anak

Korutin anak (yaitu logika di dalam `go()`) dijalankan terlebih dahulu sampai terjadi `yield` korutin (di `Co::sleep()`), lalu [penjadwalan korutin](/coroutine?id=协程调度) ke korutin luar.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

echo "main start\n";
run(function () {
    echo "coro " . Coroutine::getcid() . " start\n";
    Coroutine::create(function () {
        echo "coro " . Coroutine::getcid() . " start\n";
        Coroutine::sleep(.2);
        echo "coro " . Coroutine::getcid() . " end\n";
    });
    echo "coro " . Coroutine::getcid() . " do not wait children coroutine\n";
    Coroutine::sleep(.1);
    echo "coro " . Coroutine::getcid() . " end\n";
});
echo "end\n";

/*
main start
coro 1 start
coro 2 start
coro 1 do not wait children coroutine
coro 1 end
coro 2 end
end
*/
```

## Hal yang Perlu Diperhatikan

Hal-hal yang perlu diperhatikan sebelum menggunakan Swoole:

### Variabel Global

Korutin membuat logika asinkron menjadi sinkron, tetapi peralihan antar korutin terjadi secara implisit, sehingga konsistensi variabel global dan variabel `static` tidak dapat dijamin sebelum dan sesudah peralihan korutin.

Di `PHP-FPM`, parameter request, parameter server dll dapat diperoleh melalui variabel global. Di dalam `Swoole`, **tidak dapat** memperoleh parameter atribut apa pun melalui variabel `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER` dll yang diawali dengan `$_`.

Dapat menggunakan [context](/coroutine/coroutine?id=getcontext) dengan id korutin untuk isolasi, mencapai isolasi variabel global.

### Berbagi Koneksi TCP Multi-Korutin

[Lihat](/question/use?id=client-has-already-been-bound-to-another-coroutine)
