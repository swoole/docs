# Properti

### $setting

Parameter yang diatur oleh fungsi [Server->set()](/server/methods?id=set) akan disimpan ke properti `Server->$setting`. Di dalam fungsi callback, nilai dari parameter operasional dapat diakses. Properti ini adalah array dengan tipe `array`.

```php
Swoole\Server->setting
```

* **Contoh**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```

### $connections

Iterator koneksi `TCP`, dapat menggunakan `foreach` untuk melintasi semua koneksi server saat ini. Fungsionalitas properti ini sama dengan [Server->getClientList](/server/methods?id=getclientlist), namun lebih mudah digunakan.

Elemen yang dilintasi adalah `fd` dari masing-masing koneksi.

```php
Swoole\Server->connections
```

!> Properti `$connections` adalah objek iterator, bukan array PHP, jadi tidak bisa diakses dengan `var_dump` atau indeks array, hanya bisa dilintasi dengan `foreach`

* **Mode Base**

    * Dalam mode [SWOOLE_BASE](/learn?id=swoole_base), operasi lintas proses pada koneksi `TCP` tidak didukung. Oleh karena itu, dalam mode `BASE`, iterator `$connections` hanya dapat digunakan di dalam proses saat ini.

* **Contoh**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "Server saat ini memiliki " . count($server->connections) . " koneksi\n";
```

### $host

Mengembalikan `host` dari alamat host yang didengarkan server saat ini. Properti ini adalah string dengan tipe `string`.

```php
Swoole\Server->host
```

### $port

Mengembalikan `port` dari port yang didengarkan server saat ini. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server->port
```

### $type

Mengembalikan `type` dari Server saat ini. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server->type
```

!> Properti ini mengembalikan salah satu dari nilai berikut:
- `SWOOLE_SOCK_TCP` tcp ipv4 socket
- `SWOOLE_SOCK_TCP6` tcp ipv6 socket
- `SWOOLE_SOCK_UDP` udp ipv4 socket
- `SWOOLE_SOCK_UDP6` udp ipv6 socket
- `SWOOLE_SOCK_UNIX_DGRAM` unix socket dgram
- `SWOOLE_SOCK_UNIX_STREAM` unix socket stream

### $ssl

Mengembalikan apakah server saat ini mengaktifkan `ssl`. Properti ini adalah `bool`.

```php
Swoole\Server->ssl
```

### $mode

Mengembalikan mode proses `mode` dari server saat ini. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server->mode
```

!> Properti ini mengembalikan salah satu dari nilai berikut:
- `SWOOLE_BASE` mode proses tunggal
- `SWOOLE_PROCESS` mode multi-proses

### $ports

Array port yang didengarkan. Jika server mendengarkan beberapa port, dapat melintasi `Server::$ports` untuk mendapatkan semua objek `Swoole\Server\Port`.

`swoole_server::$ports[0]` adalah port server utama yang diatur oleh method konstruktor.

* **Contoh**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```

### $master_pid

Mengembalikan `PID` dari proses utama server saat ini.

```php
Swoole\Server->master_pid
```

!> Hanya bisa didapatkan setelah `onStart/onWorkerStart`

* **Contoh**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->master_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```

### $manager_pid

Mengembalikan `PID` dari proses manajemen server saat ini. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server->manager_pid
```

!> Hanya bisa didapatkan setelah `onStart/onWorkerStart`

* **Contoh**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->manager_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```

### $worker_id

Mendapatkan nomor proses `Worker` saat ini, termasuk [Task process](/learn?id=taskworker-process). Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server->worker_id
```

* **Contoh**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num' => 8,
    'task_worker_num' => 4,
]);
$server->on('WorkerStart', function ($server, int $workerId) {
    if ($server->taskworker) {
        echo "task workerId：{$workerId}\n";
        echo "task worker_id：{$server->worker_id}\n";
    } else {
        echo "workerId：{$workerId}\n";
        echo "worker_id：{$server->worker_id}\n";
    }
});
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
});
$server->on('Task', function ($serv, $task_id, $reactor_id, $data) {
});
$server->start();
```

* **Tips**

    * Properti ini sama dengan `$workerId` saat [onWorkerStart](/server/events?id=onworkerstart).
    * Rentang nomor proses `Worker` adalah `[0, $server->setting['worker_num'] - 1]`
    * Rentang nomor [Task process](/learn?id=taskworker-process) adalah `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`

!> Nilai `worker_id` tidak berubah setelah proses worker di-restart

### $taskworker

Apakah proses saat ini adalah proses `Task`. Properti ini adalah `bool`.

```php
Swoole\Server->taskworker
```

* **Return Value**

    * `true` berarti proses saat ini adalah proses kerja `Task`
    * `false` berarti proses saat ini adalah proses `Worker`

### $worker_pid

Mendapatkan `ID` proses sistem operasi dari proses `Worker` saat ini. Sama dengan nilai kembalian `posix_getpid()`. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server->worker_pid
```
