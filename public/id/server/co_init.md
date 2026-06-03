# Server (Gaya Coroutine) <!-- {docsify-ignore-all} -->

Perbedaan antara `Swoole\Coroutine\Server` dengan server [gaya asynchronous](/server/init) adalah bahwa `Swoole\Coroutine\Server` adalah server yang diimplementasikan sepenuhnya dengan coroutine, lihat [contoh lengkap](/coroutine/server?id=contoh-lengkap).

## Kelebihan:

- Tidak perlu mengatur fungsi callback event. Membuat koneksi, menerima data, mengirim data, dan menutup koneksi berlangsung secara sekuensial tanpa masalah konkurensi seperti pada [gaya asynchronous](/server/init), contohnya:

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

// Memantau event koneksi masuk
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);// Coroutine di OnConnect akan tertunda di sini
    Co::sleep(5);// Mensimulasikan koneksi yang lambat
    $redis->set($fd,"fd $fd connected");
});

// Memantau event penerimaan data
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);// Coroutine di OnReceive akan tertunda di sini
    var_dump($redis->get($fd));// Mungkin koneksi Redis dari coroutine OnReceive sudah terbentuk sebelum set di atas dieksekusi, menghasilkan get bernilai false, terjadi kesalahan logika
});

// Memantau event penutupan koneksi
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Menjalankan server
$serv->start();
```

Server `gaya asynchronous` di atas tidak dapat menjamin urutan event, artinya tidak dapat memastikan `onConnect` selesai sebelum `onReceive` masuk. Karena setelah mengaktifkan coroutine, callback `onConnect` dan `onReceive` akan otomatis membuat coroutine, dan saat menemui IO akan terjadi [penjadwalan coroutine](/coroutine?id=penjadwalan-coroutine). Gaya asynchronous tidak dapat menjamin urutan penjadwalan, sedangkan server gaya coroutine tidak memiliki masalah ini.

- Server gaya coroutine dapat memulai dan menghentikan layanan secara dinamis, sedangkan server gaya asynchronous tidak dapat melakukan apa pun setelah `start()` dipanggil.

## Kekurangan:

- Server gaya coroutine tidak otomatis membuat banyak proses, perlu menggunakan modul [Process\Pool](/process/process_pool) untuk memanfaatkan multi-core.
- Server gaya coroutine sebenarnya adalah enkapsulasi dari modul [Co\Socket](/coroutine_client/socket), jadi menggunakan gaya coroutine memerlukan pengalaman dalam pemrograman socket.
- Saat ini tingkat enkapsulasinya tidak setinggi server gaya asynchronous, beberapa hal perlu diimplementasikan manual, misalnya fungsi `reload` perlu memantau sinyal sendiri.
