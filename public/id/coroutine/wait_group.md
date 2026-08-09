# Coroutine\WaitGroup

Di `Swoole4`, bisa menggunakan [Channel](/coroutine/channel) untuk mencapai komunikasi antar coroutine, manajemen ketergantungan, dan sinkronisasi coroutine. Berdasarkan [Channel](/coroutine/channel), bisa dengan mudah mengimplementasikan fungsi `sync.WaitGroup` dari `Golang`.

## Kode Implementasi

> Fungsionalitas ini ditulis menggunakan PHP, bukan kode C/C++. Kode sumber implementasi ada di [Library](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php).

* Method `add` menambah penghitung
* `done` menandakan tugas selesai
* `wait` menunggu semua tugas selesai, lalu melanjutkan eksekusi coroutine saat ini
* Objek `WaitGroup` bisa digunakan kembali, bisa dipakai lagi setelah `add`, `done`, `wait`

## Contoh Penggunaan

```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $wg = new WaitGroup();
    $result = [];

    $wg->add();
    // Mulai coroutine pertama
    Coroutine::create(function () use ($wg, &$result) {
        // Mulai client coroutine, request halaman utama Taobao
        $cli = new Client('www.taobao.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.taobao.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['taobao'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    $wg->add();
    // Mulai coroutine kedua
    Coroutine::create(function () use ($wg, &$result) {
        // Mulai client coroutine, request halaman utama Baidu
        $cli = new Client('www.baidu.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.baidu.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['baidu'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    // Tangguhkan coroutine saat ini, tunggu semua tugas selesai, lalu lanjutkan
    $wg->wait();
    // Di sini $result berisi hasil 2 tugas
    var_dump($result);
});
```
