# Ringkasan Alias Fungsi

## Nama Pendek Korutin

Menyederhanakan penulisan nama `API` terkait korutin. Bisa mengubah pengaturan `php.ini` `swoole.use_shortname=On/Off` untuk mengaktifkan/menonaktifkan nama pendek, default aktif.

Semua nama class dengan prefiks `Swoole\Coroutine` dipetakan ke `Co`. Selain itu ada beberapa pemetaan lain:

### Membuat Korutin

```php
// Swoole\Coroutine::create setara dengan fungsi go
go(function () {
    Co::sleep(0.5);
    echo 'hello';
});
go('test');
go([$object, 'method']);
```

### Operasi Channel

```php
// Coroutine\Channel bisa disingkat menjadi chan
$c = new chan(1);
$c->push($data);
$c->pop();
```

### Eksekusi Tertunda

```php
// Swoole\Coroutine::defer bisa langsung menggunakan defer
defer(function () use ($db) {
    $db->close();
});
```

## Method Nama Pendek

!> Cara berikut `go` dan `defer`, tersedia di Swoole versi >= `v4.6.3`

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\defer;

run(function () {
    defer(function () {
        echo "co1 end\n";
    });
    sleep(1);
    go(function () {
        usleep(100000);
        defer(function () {
            echo "co2 end\n";
        });
        echo "co2\n";
    });
    echo "co1\n";
});
```

## API Sistem Korutin

Di versi `4.4.4`, `API` korutin terkait operasi sistem dipindahkan dari class `Swoole\Coroutine` ke class `Swoole\Coroutine\System`. Menjadi modul baru yang independen. Untuk kompatibilitas ke bawah, method alias di atas class `Coroutine` tetap dipertahankan.

* Contoh `Swoole\Coroutine::sleep` setara dengan `Swoole\Coroutine\System::sleep`
* Contoh `Swoole\Coroutine::fgets` setara dengan `Swoole\Coroutine\System::fgets`

## Pemetaan Alias Class Pendek

!> Disarankan menggunakan gaya namespace.

| Gaya Class Underscore         | Gaya Namespace               |
| ----------------------------- | ---------------------------- |
| swoole_server                 | Swoole\Server                |
| swoole_client                 | Swoole\Client                |
| swoole_process                | Swoole\Process               |
| swoole_timer                  | Swoole\Timer                 |
| swoole_table                  | Swoole\Table                 |
| swoole_lock                   | Swoole\Lock                  |
| swoole_atomic                 | Swoole\Atomic                |
| swoole_atomic_long            | Swoole\Atomic\Long           |
| swoole_buffer                 | Swoole\Buffer                |
| swoole_redis                  | Swoole\Redis                 |
| swoole_error                  | Swoole\Error                 |
| swoole_event                  | Swoole\Event                 |
| swoole_http_server            | Swoole\Http\Server           |
| swoole_http_client            | Swoole\Http\Client           |
| swoole_http_request           | Swoole\Http\Request          |
| swoole_http_response          | Swoole\Http\Response         |
| swoole_websocket_server       | Swoole\WebSocket\Server      |
| swoole_connection_iterator    | Swoole\Connection\Iterator   |
| swoole_exception              | Swoole\Exception             |
| swoole_http2_request          | Swoole\Http2\Request         |
| swoole_http2_response         | Swoole\Http2\Response        |
| swoole_process_pool           | Swoole\Process\Pool          |
| swoole_redis_server           | Swoole\Redis\Server          |
| swoole_runtime                | Swoole\Runtime               |
| swoole_server_port            | Swoole\Server\Port           |
| swoole_server_task            | Swoole\Server\Task           |
| swoole_table_row              | Swoole\Table\Row             |
| swoole_timer_iterator         | Swoole\Timer\Iterator        |
| swoole_websocket_closeframe   | Swoole\Websocket\Closeframe  |
| swoole_websocket_frame        | Swoole\Websocket\Frame       |
