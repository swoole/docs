# Masalah Penggunaan

## Bagaimana Performa Swoole

> Perbandingan QPS

Pakai Apache-Bench (ab) untuk stress test Nginx static page, Golang HTTP program, dan PHP7+Swoole HTTP program. Di mesin yang sama, dengan koncurrency 100 dan total 1 juta request HTTP, perbandingan QPS:

| Software | QPS | Versi Software |
| --- | --- | --- |
| Nginx | 164489.92 | nginx/1.4.6 (Ubuntu) |
| Golang | 166838.68 | go version go1.5.2 linux/amd64 |
| PHP7+Swoole | 287104.12 | Swoole-1.7.22-alpha |
| Nginx-1.9.9 | 245058.70 | nginx/1.9.9 |

!> Catatan: Di test Nginx-1.9.9, access_log dimatikan, open_file_cache diaktifkan untuk cache file statis ke memory

> Lingkungan Test

* CPU: Intel Core i5-4590 CPU @ 3.30GHz x 4
* Memory: 16G
* Disk: 128G SSD
* OS: Ubuntu 14.04 (Linux 3.16.0-55-generic)

> Metode Stress Test

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> Konfigurasi VHOST

```nginx
server {
    listen 80 default_server;

    location / {
        default_type text/html;
        return 200 "<h1>Hello World!</h1>";
    }
}
```

> Halaman Test

```html
<h1>Hello World!</h1>
```

> Jumlah Process

Nginx menjalankan 4 Worker process
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12Des07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12Des07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12Des07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12Des07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12Des07   0:45 nginx: worker process
```

> Golang

Kode test

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

PHP7 sudah mengaktifkan `OPcache`.

Kode test

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **TechEmpower Web Framework Benchmarks — Tolok Ukur Performa Framework Web Global**

Hasil benchmark terbaru: [techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swoole memimpin **peringkat pertama di bahasa dinamis**

Test operasi IO database, pakai kode bisnis dasar tanpa optimasi khusus

**Performa melebihi semua framework bahasa statis (pakai MySQL bukan PostgreSQL)**

## Bagaimana Swoole Menjaga TCP Long Connection

Ada 2 konfigurasi untuk menjaga TCP long connection: [tcp_keepalive](/server/setting?id=open_tcp_keepalive) dan [heartbeat](/server/setting?id=heartbeat_check_interval).

## Cara Restart Service Swoole yang Benar

Di pengembangan sehari-hari, setelah mengubah kode PHP sering perlu restart service biar kode berlaku. Server backend yang sibuk terus memproses request. Kalau admin matiin/restart server pake `kill`, bisa aja kode lagi jalan setengah, nggak bisa jamin integritas logika bisnis.

`Swoole` punya mekanisme graceful termination/restart. Admin cukup kirim sinyal tertentu ke `Server` atau panggil method `reload`, worker process bakal berhenti dan dijalankan ulang. Detailnya lihat [reload()](/server/methods?id=reload)

Tapi ada beberapa yang perlu diperhatikan:

Pertama, kode yang baru diubah harus di-load ulang di event `OnWorkerStart` baru berlaku. Misalnya suatu class di-load lewat composer autoload sebelum `OnWorkerStart`, itu nggak bakal jalan.

Kedua, `reload` perlu dipasangin sama dua parameter ini: [max_wait_time](/server/setting?id=max_wait_time) dan [reload_async](/server/setting?id=reload_async). Kalau udah diset, bisa mengimplementasikan `async safe restart`.

Tanpa fitur ini, pas Worker process dapet sinyal restart atau mencapai [max_request](/server/setting?id=max_request), service langsung berhenti. Padahal di dalam `Worker` mungkin masih ada event listener, task async bakal ilang. Kalo set parameter di atas, `Worker` baru bakal dibuat dulu, `Worker` lama bakal keluar sendiri setelah semua event selesai, yaitu `reload_async`.

Kalau `Worker` lama nggak keluar-keluar, level bawah nambahin timer. Dalam waktu yang ditentukan ([max_wait_time](/server/setting?id=max_wait_time) detik), kalo `Worker` lama nggak keluar, level bawah bakal paksa berhenti dan muncul [WARNING](/question/use?id=forced-to-terminate).

Contoh:

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tClient[$fd] receive data: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

Misalnya kode di atas, kalo nggak ada `reload_async`, timer yang dibuat di `onReceive` bakal ilang, nggak ada kesempatan buat proses callback timer.

### Event Exit Process

Untuk dukung fitur async restart, level bawah nambahin event [onWorkerExit](/server/events?id=onWorkerExit). Pas `Worker` lama mau keluar, event `onWorkerExit` bakal dipicu. Di callback event ini, aplikasi bisa bersihin koneksi `Socket` yang umurnya panjang, sampai [event loop](/learn?id=apa-itu-eventloop) nggak ada fd atau udah mencapai [max_wait_time](/server/setting?id=max_wait_time) baru exit.

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

Selain itu, di [Swoole Plus](https://www.swoole.com/swoole_plus) ada fitur deteksi perubahan file. Jadi nggak perlu manual reload atau kirim sinyal, worker bakal restart otomatis kalo file berubah.

## Kenapa send lalu langsung close itu tidak aman

Send lalu langsung close itu tidak aman, baik di sisi server maupun client.

Operasi send berhasil cuma berarti data berhasil ditulis ke buffer socket OS, bukan berarti penerima beneran nerima data. Apakah OS berhasil ngirim, apakah server tujuan nerima, apakah program server ngolah — semua nggak ada jaminan.

> Soal logika setelah close, lihat pengaturan linger di bawah

Logikanya sama kayak telepon. A ngomong sesuatu ke B, A selesai ngomong langsung tutup telepon. B denger atau nggak, A nggak tau. Kalo A selesai ngomong, B bilang "siap", baru B tutup telepon, itu baru aman.

Pengaturan linger

Pas `socket` di-close, kalo buffer masih ada data, OS level bawah bakal menentukan berdasarkan pengaturan `linger`.

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0, pas close langsung balik, level bawah bakal kirim dulu data yang belum terkirim baru lepas resource, yaitu graceful exit.
* l_onoff != 0, l_linger = 0, pas close langsung balik, tapi data yang belum terkirim nggak dikirim, malah paksa tutup socket descriptor pake paket RST, yaitu force exit.
* l_onoff != 0, l_linger > 0, pas close nggak langsung balik, kernel nunda beberapa saat sesuai nilai l_linger. Kalo sebelum timeout data berhasil dikirim (termasuk paket FIN) dan dikonfirmasi pihak lawan, close balik sukses, socket descriptor graceful exit. Kalo nggak, close balik error, data hilang, socket descriptor dipaksa keluar. Kalo socket descriptor di-set non-blocking, close langsung balik value.

## client has already been bound to another coroutine

Untuk satu koneksi `TCP`, level bawah Swoole cuma ngizinin satu coroutine buat baca dan satu coroutine buat tulis dalam satu waktu. Artinya nggak boleh ada banyak coroutine yang baca/tulis ke satu TCP yang sama, level bawah bakal lempar error binding:

```shell
Fatal error: Uncaught Swoole\Error: Socket#6 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed 
```

Kode reproduksi:

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

Solusi: https://wenda.swoole.com/detail/107474

!> Batasan ini berlaku di semua lingkungan multi-coroutine. Paling sering terjadi di callback [onReceive](/server/events?id=onreceive) yang pake satu koneksi TCP barengan, karena callback ini otomatis bikin coroutine. Kalau butuh connection pool, `Swoole` punya [connection pool](/coroutine/conn_pool) bawaan yang bisa langsung dipake, atau bikin sendiri pake `channel`.

## Call to undefined function Co\run()

Kebanyakan contoh di dokumen ini pake `Co\run()` untuk bikin coroutine container. [Pelajari apa itu coroutine container](/coroutine?id=apa-itu-coroutine-container)

Kalo nemu error kayak gini:

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

Artinya versi ekstensi `Swoole` kamu di bawah `v4.4.0` atau kamu matiin [coroutine short name](/other/alias?id=coroutine-short-name). Ini solusinya:

* Kalo versi terlalu rendah, upgrade ke `>= v4.4.0` atau pake `go` ganti `Co\run` buat bikin coroutine;
* Kalo coroutine short name dimatiin, hidupkan [coroutine short name](/other/alias?id=coroutine-short-name);
* Pake [Coroutine::create](/coroutine/coroutine?id=create) ganti `Co\run` atau `go` buat bikin coroutine;
* Pake nama lengkap: `Swoole\Coroutine\run`;

## Bolehkah pake 1 koneksi Redis atau MySQL barengan

Sama sekali nggak boleh. Setiap process harus bikin koneksi `Redis`, `MySQL`, `PDO` sendiri-sendiri. Client storage lain juga sama. Soalnya kalo pake 1 koneksi barengan, nggak bisa dijamin proses mana yang bakal nerima hasilnya. Semua process yang megang koneksi secara teori bisa baca/tulis koneksi itu, jadinya data kacau.

**Pokoknya antar process, jangan pernah pake koneksi barengan**

* Di [Swoole\Server](/server/init), koneksi harus dibuat di [onWorkerStart](/server/events?id=onworkerstart)
* Di [Swoole\Process](/process/process), koneksi harus dibuat di callback child process setelah [Swoole\Process->start](/process/process?id=start)
* Info ini juga berlaku buat program yang pake `pcntl_fork`

Contoh:

```php
$server = new Swoole\Server('0.0.0.0', 9502);

//Koneksi redis/mysql harus dibuat di callback onWorkerStart
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```

## Masalah Koneksi Sudah Ditutup

Kayak pesan di bawah ini

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

Pas server mau ngirim respon, client udah nutup koneksi.

Biasanya terjadi karena:

* Browser di-refresh terus (belum selesai load udah ditutup)
* Stress test ab dibatalkan di tengah jalan
* Stress test wrk berdasarkan waktu (request yang belum selesai dibatalkan pas waktu habis)

Semua kasus di atas itu wajar, bisa diabaikan. Makanya level error-nya NOTICE

Kalo tiba-tiba ada banyak koneksi yang putus tanpa alasan jelas, baru perlu diperhatiin

```bash
WARNING swWorker_discard_data (ERRNO 1007): [2] received the wrong data[21 bytes] from socket#75

WARNING Worker_discard_data (ERRNO 1007): [2] ignore data[5 bytes] received from session#2
```

Sama, error ini juga artinya koneksi udah ditutup, data yang diterima bakal dibuang. Lihat [discard_timeout_request](/server/setting?id=discard_timeout_request)

## Properti connected dan status koneksi tidak sinkron

Sejak versi coroutine 4.x, properti `connected` nggak akan diupdate realtime lagi, method [isConnect](/client?id=isconnected) nggak bisa diandalkan.

### Penyebab

Tujuan coroutine adalah konsisten sama model programming synchronous blocking. Di model synchronous blocking, nggak ada konsep update status koneksi realtime. Kayak PDO, curl, dll — nggak ada konsep koneksi. Koneksi putus baru ketahuan pas operasi IO balikin error atau lempar exception.

Pendekatan umum di level bawah Swoole adalah, pas terjadi error IO, balikin false (atau konten kosong) dan set error code serta error message di object client.

### Catatan

Meskipun versi asynchronous sebelumnya dukung update properti `connected` secara "real-time", sebenarnya itu nggak reliable. Koneksi bisa aja putus pas setelah kamu cek.

## Connection refused itu apa

Pas telnet 127.0.0.1 9501 muncul Connection refused, artinya server nggak mendengarkan port ini.

* Cek apakah program jalan: ps aux
* Cek apakah port didengarkan: netstat -lp
* Cek proses komunikasi network: tcpdump traceroute

## Resource temporarily unavailable [11]

Client `swoole_client` pas `recv` error

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```

Error ini artinya server nggak balik data dalam waktu yang ditentukan, receive timeout.

* Bisa pake tcpdump buat cek proses komunikasi network, periksa apakah server ngirim data
* Fungsi `$serv->send` di server perlu dicek apakah balikin true
* Komunikasi internet butuh waktu lebih, perlu perbesar timeout swoole_client

## worker exit timeout, forced to terminate :id=forced-to-terminate

Nemu error kayak gini:

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```

Artinya dalam waktu yang ditentukan ([max_wait_time](/server/setting?id=max_wait_time) detik) Worker ini nggak keluar, level bawah Swoole paksa matiin process ini.

Bisa reproduksi pake kode ini:

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```

## Unable to find callback function for signal Broken pipe: 13

Nemu error kayak gini:

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```

Artinya ngirim data ke koneksi yang udah putus. Biasanya karena nggak ngecek return value send, tetep kirim padahal gagal.

## Pengetahuan Dasar yang Perlu Dikuasai buat Belajar Swoole

### Multiprocess/Multithread

* Paham konsep process dan thread di OS `Linux`
* Paham dasar-dasar switching dan scheduling process/thread di `Linux`
* Paham dasar-dasar komunikasi antar process, kayak pipe, `UnixSocket`, message queue, shared memory

### SOCKET

* Paham operasi dasar `SOCKET` kayak `accept/connect`, `send/recv`, `close`, `listen`, `bind`
* Paham konsep receive buffer, send buffer, blocking/non-blocking, timeout di `SOCKET`

### IO Multiplexing

* Paham `select`/`poll`/`epoll`
* Paham event loop berbasis `select`/`epoll`, model `Reactor`
* Paham readable event, writable event

### Protokol Jaringan TCP/IP

* Paham protokol `TCP/IP`
* Paham protokol transport `TCP`, `UDP`

### Alat Debug

* Pake [gdb](/other/tools?id=gdb) buat debug program `Linux`
* Pake [strace](/other/tools?id=strace) buat trace system call process
* Pake [tcpdump](/other/tools?id=tcpdump) buat trace proses komunikasi network
* Alat `Linux` lainnya kayak ps, [lsof](/other/tools?id=lsof), top, vmstat, netstat, sar, ss, dll

## Object of class Swoole\Curl\Handler could not be converted to int

Pas pake [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl), muncul error:

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```

Penyebabnya, curl setelah di-hook bukan tipe resource lagi, tapi object. Jadi nggak bisa di-convert ke int.

!> Soal `int` disaranin hubungi pihak SDK buat ubah kode. Di PHP8, curl udah bukan tipe resource, tapi object.

Ada tiga solusi:

1. Jangan aktifkan [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl). Tapi mulai [v4.5.4](/version/log?id=v454), [SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all) udah termasuk [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) secara default. Bisa set `SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL` buat matiin [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)

2. Pake SDK Guzzle, bisa ganti Handler biar pake coroutine

3. Mulai Swoole `v4.6.0`, bisa pake [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl) sebagai ganti [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)

## Pake one-click coroutine dan Guzzle 7.0+ bareng, hasil request langsung ke terminal :id=hook_guzzle

Kode reproduksi:

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// Sebelum v4.5.4
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// Hasil request langsung ke terminal, bukan di-print
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> Solusinya sama kayak masalah sebelumnya. Tapi masalah ini udah diperbaiki di Swoole >= `v4.5.8`.

## Error: No buffer space available[55]

Error ini bisa diabaikan. Error ini muncul karena opsi [socket_buffer_size](/server/setting?id=socket_buffer_size) kegedean, ada sistem yang nggak nerima. Nggak pengaruh ke jalan program.

## Ukuran Maksimum Request GET/POST

### GET request maksimal 8192

GET request cuma punya satu Http header. Level bawah Swoole pake buffer memori ukuran tetap 8K, nggak bisa diubah. Kalo request bukan Http yang benar, bakal error. Level bawah bakal lempar error ini:

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```

### Upload File POST

Ukuran maksimum dibatasi oleh konfigurasi [package_max_length](/server/setting?id=package_max_length), default 2M. Bisa panggil [Server->set](/server/methods?id=set) buat ganti nilainya. Level bawah Swoole pake memori semua, jadi kalo kegedean bisa bikin resource server habis karena banyak request bersamaan.

Rumus: `Maksimum pemakaian memory` = `Maksimum request bersamaan` * `package_max_length`
