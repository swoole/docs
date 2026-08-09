# Tips Pemrograman

Bagian ini akan menjelaskan secara detail perbedaan antara pemrograman coroutine dan pemrograman sinkron, serta hal-hal yang perlu diperhatikan.

## Catatan Penting

* Jangan pakai `sleep` atau fungsi tidur lainnya di dalam kode, karena bakal bikin seluruh proses jadi blocking; di coroutine bisa pake [Co::sleep()](/coroutine/system?id=sleep) atau pake `sleep` setelah [one-click coroutine transformation](/runtime); referensi: [Dampak sleep/usleep](/getting_started/notice?id=dampak-sleepusleep)
* `exit/die` itu berbahaya, bisa bikin proses `Worker` keluar; referensi: [Dampak fungsi exit/die](/getting_started/notice?id=dampak-fungsi-exitdie)
* Bisa pake `register_shutdown_function` buat menangkap fatal error, biar bisa bersihin sesuatu saat proses keluar secara abnormal; referensi: [Menangkap fatal error saat Server jalan](/getting_started/notice?id=menangkap-fatal-error-saat-server-jalan)
* Kalau kode `PHP` ada exception yang thrown, wajib di-`try/catch` di dalam callback function, kalau nggak bisa bikin worker process keluar; referensi: [Menangkap exception dan error](/getting_started/notice?id=menangkap-exception-dan-error)
* `set_exception_handler` nggak didukung, harus pake `try/catch` buat handle exception;
* Worker process jangan saling pake client `Redis` atau `MySQL` yang sama; kode pembuatan koneksi `Redis/MySQL` bisa ditaruh di callback `onWorkerStart`. Referensi: [Apa boleh pake 1 koneksi Redis atau MySQL barengan](/question/use?id=apa-boleh-pake-1-koneksi-redis-atau-mysql-barengan)

## Pemrograman Coroutine

Mau pake fitur `Coroutine`, baca dulu [Panduan Pemrograman Coroutine](/coroutine/notice) dengan saksama.

## Pemrograman Konkuren

Harap diingat, beda sama mode `synchronous blocking`, di mode `coroutine` program jalan secara **concurrent**. Dalam waktu yang sama, `Server` bakal menghadapi banyak request, jadi **aplikasi harus bikin resource dan context yang berbeda-beda buat tiap client atau request**. Kalau nggak, bisa terjadi kekacauan data dan logika antar client atau request yang berbeda.

## Definisi Class/Fungsi Dobel

Kesalahan klasik yang sering dilakukan pemula. Karena `Swoole` itu menetap di memori, definisi class/fungsi yang sudah di-load dari file nggak bakal dilepas. Makanya, waktu include file PHP yang berisi definisi class/fungsi, WAJIB pake `include_once` atau `require_once`. Kalau nggak, bakal kena fatal error `cannot redeclare function/class`.

## Manajemen Memori

!> Perhatian ekstra waktu nulis `Server` atau proses menetap lainnya.

Siklus hidup variabel dan manajemen memori di PHP daemon process itu beda banget sama program Web biasa. Prinsip dasar manajemen memori setelah `Server` jalan itu sama kayak program PHP-cli biasa. Detailnya silakan baca artikel soal manajemen memori di `Zend VM`.

### Variabel Lokal

Setelah callback event selesai dijalankan, semua objek dan variabel lokal bakal kehapus otomatis, nggak perlu di-`unset`. Kalau variabelnya bertipe resource, resource terkait juga bakal dibebasin sama PHP.

```php
function test()
{
	$a = new Object;
	$b = fopen('/data/t.log', 'r+');
	$c = new swoole_client(SWOOLE_SYNC);
	$d = new swoole_client(SWOOLE_SYNC);
	global $e;
	$e['client'] = $d;
}
```

* `$a`, `$b`, `$c` adalah variabel lokal. Pas fungsi ini `return`, ke-`3` variabel ini bakal langsung dibebasin, memorinya langsung dilepas, handle file resource IO yang terbuka juga langsung ditutup.
* `$d` juga variabel lokal, tapi sebelum `return` dia disimpen ke variabel global `$e`, jadi nggak bakal dibebasin. Pas `unset($e['client'])` dijalankan dan udah nggak ada `variabel PHP` lain yang masih ngereferensi `$d`, baru `$d` bakal dibebasin.

### Variabel Global

Di `PHP`, ada `3` jenis variabel global.

* Variabel yang dideklarasi pake keyword `global`
* Class static variable dan function static variable yang dideklarasi pake keyword `static`
* Superglobal variable `PHP`, termasuk `$_GET`, `$_POST`, `$GLOBALS`, dll

Variabel global, objek, dan class static variable yang tersimpan di objek `Server` nggak bakal dibebasin otomatis. Developer harus ngurus sendiri penghancuran variabel dan objek ini.

```php
class Test
{
	static $array = array();
	static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
	Test::$array[] = $fd;
	Test::$string .= $data;
}
```

* Di callback event, hati-hati sama variabel non-lokal bertipe `array`. Operasi kayak `TestClass::$array[] = "string"` bisa bikin memory leak, parahnya bisa overflow. Kalo perlu, bersihin array gede-gede.
* Di callback event, concatenation string variabel non-lokal juga musti hati-hati soal memory leak, misalnya `TestClass::$string .= $data`. Bisa bocor memori, parahnya bisa overflow.

### Solusi

* Buat program `Server` yang synchronous blocking dan stateless, bisa set [max_request](/server/setting?id=max_request) dan [task_max_request](/server/setting?id=task_max_request). Pas [Worker process](/learn?id=worker-process) / [Task process](/learn?id=taskworker-process) selesai jalan atau mencapai batas tugas, proses bakal keluar otomatis, dan semua variabel/objek/resource di proses itu bakal dibebasin.
* Di dalem program, pake `unset` di `onClose` atau pake `timer` buat bersihin variabel dan resource tepat waktu.

## Isolasi Proses

Isolasi proses juga sering bikin pusing pemula. Kok nilai variabel global diubah tapi nggak keapply? Soalnya variabel global di proses yang beda-beda itu ruang memorinya terisolasi, jadi nggak ngaruh.

Makanya, waktu bikin `Server` pake `Swoole`, musti paham soal `isolasi proses`. Proses `Worker` yang beda di `Swoole\Server` itu terisolasi satu sama lain. Waktu ngoding, operasi variabel global, timer, event listener, cuman berlaku di proses saat itu aja.

* Variabel PHP nggak dishare antar proses yang beda. Bahkan variabel global sekalipun, kalo diubah di proses A, nggak bakal ngefek di proses B.
* Kalo perlu sharing data antar Worker process yang beda, bisa pake `Redis`, `MySQL`, `file`, `Swoole\Table`, `APCu`, `shmget`, dll.
* File handle di proses yang beda itu terisolasi. Koneksi Socket yang dibuat atau file yang dibuka di proses A, nggak valid di proses B. Bahkan kalo fd-nya dikirim ke proses B pun tetep nggak bisa dipake.

Contoh:

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$i = 1;

$server->on('Request', function ($request, $response) {
	global $i;
    $response->end($i++);
});

$server->start();
```

Di server multi-proses, variabel `$i` meskipun dideklarasi global (`global`), karena isolasi proses, misal ada `4` worker process, pas `$i++` di `proses 1`, cuman `$i` di `proses 1` aja yang jadi `2`. Tiga proses lainnya nilai `$i` tetep `1`.

Cara yang bener adalah pake struktur data [Swoole\Atomic](/memory/atomic) atau [Swoole\Table](/memory/table) yang disediain Swoole buat nyimpen data. Kayak kode di atas, bisa pake `Swoole\Atomic`.

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$atomic = new Swoole\Atomic(1);

$server->on('Request', function ($request, $response) use ($atomic) {
    $response->end($atomic->add(1));
});

$server->start();
```

!> Data `Swoole\Atomic` dibangun di atas shared memory. Pas pake method `add` buat nambah `1`, perubahannya juga berlaku di worker process lain.

Komponen [Table](/memory/table), [Atomic](/memory/atomic), dan [Lock](/memory/lock) yang disediain Swoole bisa dipake buat pemrograman multi-proses, tapi harus dibuat sebelum `Server->start`. Selain itu, koneksi TCP client yang dikelola `Server` juga bisa dioperasiin antar proses, kayak `Server->send` dan `Server->close`.

## Bersihin Cache stat

PHP nambahin `Cache` di level bawah buat panggilan sistem `stat`. Pas pake fungsi kayak `stat`, `fstat`, `filemtime`, dll, level bawah bisa kena cache dan balikin data histori.

Bisa pake fungsi [clearstatcache](https://www.php.net/manual/en/function.clearstatcache.php) buat bersihin cache `stat` file.

## Angka Acak mt_rand

Di `Swoole`, kalo `mt_rand` dipanggil di proses induk, pas dipanggil lagi di proses anak yang berbeda hasilnya bakal sama. Makanya musti panggil `mt_srand` buat reseed ulang di tiap proses anak.

!> Fungsi `PHP` kayak `shuffle` dan `array_rand` yang bergantung sama angka acak juga bakal kena dampaknya.

Contoh:

```php
mt_rand(0, 1);

//Mulai
$worker_num = 16;

//fork process
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

//jalankan proses secara asinkron
function child_async(Swoole\Process $worker) {
    mt_srand(); //reseed ulang
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
```

## Menangkap Exception dan Error

### Exception/Error yang Bisa Ditangkap

Di `PHP` kira-kira ada tiga jenis exception/error yang bisa ditangkap:

1. `Error`: tipe error khusus yang dilempar oleh kernel `PHP`, kayak class nggak ada, fungsi nggak ada, parameter fungsi salah. Di kode `PHP`, jangan pake `Error` buat nge-throw exception.
2. `Exception`: class dasar exception yang harus dipake developer aplikasi.
3. `ErrorException`: class exception khusus yang nugas buat ngonversi `Warning`/`Notice` `PHP` jadi exception lewat `set_error_handler`. Ke depannya, `PHP` berencana ngonversi semua `Warning`/`Notice` jadi exception biar program `PHP` bisa handle error dengan lebih baik dan terkontrol.

!> Semua class di atas implement interface `Throwable`. Artinya, pake `try {} catch(Throwable $e) {}` bisa nangkep semua exception/error yang bisa di-throw.

Contoh 1:
```php
try {
	test();
}
catch(Throwable $e) {
	var_dump($e);
}
```
Contoh 2:
```php
try {
	test();
}
catch (Error $e) {
	var_dump($e);
}
catch(Exception $e) {
	var_dump($e);
}
```

### Fatal Error dan Exception yang Nggak Bisa Ditangkap

Salah satu level error penting di `PHP`, kayak pas exception/error nggak ketangkep, memori habis, atau error waktu kompilasi (class yang di-inherit nggak ada), bakal ngelempar `Fatal Error` di level `E_ERROR`. Ini terjadi pas program kena error yang nggak bisa dipulihkan. `PHP` nggak bisa nangkep error level kayak gini, cuma bisa pake `register_shutdown_function` buat ngelakuin sesuatu setelahnya.

### Menangkap Runtime Exception/Error di Coroutine

Di pemrograman coroutine `Swoole4`, kalo ada error yang di-throw di kode suatu coroutine, bisa bikin seluruh proses keluar dan semua coroutine di proses itu berhenti. Di level teratas coroutine, bisa pake `try/catch` buat nangkep exception/error, dan cuman Coroutine yang error aja yang berhenti.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    //Error di Coroutine 1 nggak ngefek Coroutine 2
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
```

### Menangkap Fatal Error Saat Server Jalan

Pas `Server` jalan, kalo terjadi fatal error, koneksi client nggak bakal dapet respons. Misalnya web server, kalo ada fatal error, harusnya ngirim `HTTP 500` ke client.

Di PHP, bisa pake kombinasi fungsi `register_shutdown_function` + `error_get_last` buat nangkep fatal error, dan kirim info errornya ke koneksi client.

Kode contohnya:

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    register_shutdown_function(function () use ($response) {
        $error = error_get_last();
        var_dump($error);
        switch ($error['type'] ?? null) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                // log atau kirim:
                // error_log($message);
                // $server->send($fd, $error['message']);
                $response->status(500);
                $response->end($error['message']);
                break;
        }
    });
    exit(0);
});
$http->start();
```

## Dampak Penggunaan

### Dampak sleep/usleep

Di program asynchronous I/O, **jangan pake sleep/usleep/time_sleep_until/time_nanosleep**. (Selanjutnya `sleep` dipake buat nyebut semua fungsi tidur)

* Fungsi `sleep` bikin process masuk ke mode tidur dan blocking
* Sistem operasi bakal ngebangunin process setelah waktu yang ditentuin
* Selama `sleep`, cuman sinyal yang bisa ngeinterupsi
* Karena penanganan sinyal Swoole pake `signalfd`, ngirim sinyal pun nggak bisa ngeinterupsi `sleep`

[Swoole\Event::add](/event?id=add), [Swoole\Timer::tick](/timer?id=tick), [Swoole\Timer::after](/timer?id=after), dan [Swoole\Process::signal](/process/process?id=signal) yang disediain Swoole bakal berhenti kerja setelah process `sleep`. [Swoole\Server](/server/tcp_init) juga nggak bakal bisa handle request baru.

#### Contoh

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    sleep(100);
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> Di event [onReceive](/server/events?id=onreceive) jalanin fungsi `sleep`, `Server` nggak bakal bisa nerima request client selama 100 detik.

### Dampak Fungsi exit/die

Di program `Swoole`, `exit/die` dilarang dipake. Kalo di kode PHP ada `exit/die`, [Worker process](/learn?id=worker-process), [Task process](/learn?id=taskworker-process), [User process](/server/methods?id=addprocess), dan process `Swoole\Process` yang sedang jalan bakal langsung keluar.

Pas pake `exit/die`, `Worker` process bakal keluar karena abnormal, terus dihidupin lagi sama `master` process. Jadinya process keluar terus-terusan dan muncul banyak log alert.

Sebaiknya ganti `exit/die` pake `try/catch` buat interupsi eksekusi dan keluar dari call stack fungsi PHP.

```php
Swoole\Coroutine\run(function () {
    try
    {
        exit(0);
    } catch (Swoole\ExitException $e)
    {
        echo $e->getMessage()."\n";
    }
});
```

!> `Swoole\ExitException` didukung langsung di Swoole versi `v4.1.0` ke atas buat pake `exit` PHP di coroutine dan `Server`. Dalam kasus ini, level bawah bakal otomatis ngelempar `Swoole\ExitException` yang bisa ditangkap, jadi developer bisa nangkep dan ngelakuin logika exit kayak PHP asli. Detailnya lihat [Keluar dari Coroutine](/coroutine/notice?id=keluar-dari-coroutine);

Penanganan exception lebih bersahabat daripada `exit/die` karena exception itu terkontrol, sedangkan `exit/die` nggak terkontrol. Pake `try/catch` di lapisan paling luar buat nangkep exception, dan cuman tugas yang sedang jalan aja yang berhenti. Worker process bisa lanjut handle request baru. Sementara `exit/die` bikin process langsung keluar, semua variabel dan resource yang disimpen di process itu hancur. Kalo masih ada tugas lain yang harus dikerjain di dalem process, ketemu `exit/die` semuanya bakal kebuang.

### Dampak While Loop

Kalo program asynchronous kena infinite loop, event nggak bakal bisa kepicu. Program asynchronous I/O pake `Reactor model`, pas jalan musti nge-poll di `reactor->wait`. Kalo kena infinite loop, kendali program bakal nyangkut di `while`, `reactor` nggak dapet kendali, nggak bisa deteksi event, dan akhirnya callback function IO event nggak bakal kepicu.

!> Kode komputasi intensif tanpa operasi IO apa pun nggak bisa disebut blocking.

#### Contoh Program

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactorId, $data) {
    $i = 0;
    while(1)
    {
        $i++;
    }
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> Ada infinite loop di event [onReceive](/server/events?id=onreceive), `server` nggak bakal bisa nerima request client baru lagi. Server musti nunggu loop selesai dulu baru bisa lanjut proses event baru.
