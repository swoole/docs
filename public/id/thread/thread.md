# Swoole\Thread <!-- {docsify-ignore-all} -->

Mulai dari versi `6.0` telah tersedia dukungan multi-thread, kamu bisa menggunakan `API` thread untuk menggantikan multi-proses. Dibanding multi-proses, `Thread` menyediakan container data konkuren yang lebih kaya, sehingga lebih nyaman saat mengembangkan game server dan communication server.

- `PHP` harus dalam mode `ZTS`, saat kompilasi `PHP` perlu menambahkan `--enable-zts`
- Kompilasi `Swoole` perlu menambahkan opsi `--enable-swoole-thread`

## Isolasi Resource

`Swoole` Thread mirip dengan `Node.js Worker Thread`, di dalam child thread akan dibuat lingkungan `ZendVM` yang benar-benar baru. Child thread tidak mewarisi resource apa pun dari parent thread, oleh karena itu konten berikut telah dikosongkan di child thread dan perlu dibuat ulang atau diatur.

- File `PHP` yang sudah dimuat, perlu di-load ulang dengan `include/require`
- Perlu mendaftarkan ulang fungsi `autoload`
- Class, function, constant akan dikosongkan, perlu me-load ulang file `PHP`
- Global variable, seperti `$GLOBALS`, `$_GET/$_POST`, dll, akan di-reset
- Static property class, static variable function, akan di-reset ke nilai awal
- Beberapa opsi `php.ini`, seperti `error_reporting()` perlu diatur ulang di child thread

## Fitur yang Tidak Tersedia

Dalam mode multi-thread, fitur berikut hanya bisa dioperasikan di main thread, tidak bisa dijalankan di child thread:

- `swoole_async_set()` mengubah parameter thread
- `Swoole\Runtime::enableCoroutine()` dan `Swoole\Runtime::setHookFlags()`
- Hanya main thread yang bisa mengatur signal listener, termasuk `Process::signal()` dan `Coroutine\System::waitSignal()` tidak bisa digunakan di child thread
- Hanya main thread yang bisa membuat asynchronous server, termasuk `Server`, `Http\Server`, `WebSocket\Server` dll tidak bisa digunakan di child thread

Selain itu, `Runtime Hook` setelah diaktifkan dalam mode multi-thread tidak bisa dimatikan.

## Fatal Error
Saat main thread keluar, jika masih ada child thread yang aktif, akan muncul fatal error, kode keluar: `200`, pesan error:
```
Fatal Error: 2 active threads are running, cannot exit safely.
```

## Mengecek Apakah Dukungan Thread Aktif

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)` menandakan thread safety sudah diaktifkan

```shell
php --ri swoole

swoole
Swoole => enabled
thread => enabled
```

`thread => enabled` menandakan dukungan multi-thread sudah aktif

### Membuat Multi-Thread
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

// Main thread tidak punya thread arguments, $args bernilai null
if (empty($args)) {
    # Main thread
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # Child thread
    echo "Thread #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```

### Thread + Server (Async Style)
- Semua worker process akan menggunakan thread untuk berjalan, termasuk `Worker`, `Task Worker`, `User Process`
- Mode `SWOOLE_THREAD` baru, setelah diaktifkan akan menggunakan thread sebagai pengganti process
- Menambahkan konfigurasi [bootstrap](/server/setting?id=bootstrap) dan [init_arguments](/server/setting?id=init_arguments) untuk mengatur file script entry worker thread dan data shared thread
- `Server` harus dibuat di main thread, bisa membuat `Thread` baru di callback untuk menjalankan tugas lain
- Object `Server::addProcess()` Process tidak mendukung redirect std I/O

```php
use Swoole\Process;
use Swoole\Thread;
use Swoole\Http\Server;

$http = new Server("0.0.0.0", 9503, SWOOLE_THREAD);
$http->set([
    'worker_num' => 2,
    'task_worker_num' => 3,
    'bootstrap' => __FILE__,
    'init_arguments' => function () use ($http) {
        $map = new Swoole\Thread\Map;
        return [$map];
    }
]);

$http->on('Request', function ($req, $resp) use ($http) {
    $resp->end('hello world');
});

$http->on('pipeMessage', function ($http, $srcWorkerId, $msg) {
    echo "[worker#" . $http->getWorkerId() . "]\treceived pipe message[$msg] from " . $srcWorkerId . "\n";
});

$http->addProcess(new Process(function () {
   echo "user process, id=" . Thread::getId();
   sleep(2000);
}));

$http->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
    var_dump($taskId, $srcWorkerId, $data);
    return ['result' => uniqid()];
});

$http->on('Finish', function ($server, $taskId, $data) {
    var_dump($taskId, $data);
});

$http->on('WorkerStart', function ($serv, $wid) {
    var_dump(Thread::getArguments(), $wid);
});

$http->on('WorkerStop', function ($serv, $wid) {
    var_dump('stop: T' . Thread::getId());
});

$http->start();
```
