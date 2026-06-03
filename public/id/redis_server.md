# Redis\Server

Kelas `Server` yang kompatibel dengan protokol sisi server `Redis`, dapat digunakan untuk mengimplementasikan program server berdasarkan protokol `Redis`.

?> `Swoole\Redis\Server` mewarisi dari [Server](/server/tcp_init), jadi semua `API` dan opsi konfigurasi yang disediakan oleh `Server` dapat digunakan, dan model prosesnya juga sama. Silakan merujuk ke bagian [Server](/server/init).

* **Klien yang Tersedia**

  * Klien `redis` dalam bahasa pemrograman apa pun, termasuk ekstensi `redis` PHP dan pustaka `phpredis`
  * [Swoole\Coroutine\Redis](/coroutine_client/redis) klien korutin
  * Alat baris perintah yang disediakan oleh `Redis`, termasuk `redis-cli`, `redis-benchmark`

## Metode

`Swoole\Redis\Server` mewarisi dari `Swoole\Server`, dapat menggunakan semua metode yang disediakan oleh kelas induk.

### setHandler

?> **Mengatur penangan untuk perintah `Redis`.**

!> `Redis\Server` tidak perlu mengatur callback [onReceive](/server/events?id=onreceive). Cukup gunakan metode `setHandler` untuk mengatur fungsi penangan untuk perintah yang sesuai. Setelah menerima perintah yang tidak didukung, secara otomatis akan mengirimkan respons `ERROR` ke klien dengan pesan `ERR unknown command '$command'`.

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

* **Parameter** 

  * **`string $command`**
    * **Fungsi**: Nama perintah
    * **Nilai default**: Tidak ada
    * **Nilai lainnya**: Tidak ada

  * **`callable $callback`**
    * **Fungsi**: Fungsi penangan untuk perintah [ketika fungsi callback mengembalikan tipe string, akan otomatis dikirim ke klien]
    * **Nilai default**: Tidak ada
    * **Nilai lainnya**: Tidak ada

    !> Data yang dikembalikan harus dalam format `Redis`, dapat menggunakan metode statis `format` untuk mengemasnya

### format

?> **Memformat data respons perintah.**

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **Parameter** 

  * **`int $type`**
    * **Fungsi**: Tipe data, konstanta terkait dapat dilihat di [Konstanta Parameter Format](/redis_server?id=格式参数常量).
    * **Nilai default**: Tidak ada
    * **Nilai lainnya**: Tidak ada
    
    !> Ketika `$type` adalah tipe `NIL`, `$value` tidak diperlukan; `$value` opsional untuk tipe `ERROR` dan `STATUS`; wajib untuk `INT`, `STRING`, `SET`, `MAP`.

  * **`mixed $value`**
    * **Fungsi**: Nilai
    * **Nilai default**: Tidak ada
    * **Nilai lainnya**: Tidak ada

### send

?> **Menggunakan metode `send()` dari [Swoole\Server](/server/methods?id=send) untuk mengirim data ke klien.**

```php
Swoole\Server->send(int $fd, string $data): bool
```

## Konstanta

### Konstanta Parameter Format

Terutama digunakan untuk fungsi `format` dalam mengemas data respons `Redis`

Konstanta | Keterangan
---|---
Server::NIL | Mengembalikan data nil
Server::ERROR | Mengembalikan kode kesalahan
Server::STATUS | Mengembalikan status
Server::INT | Mengembalikan integer, `format` harus memasukkan nilai parameter, tipe harus integer
Server::STRING | Mengembalikan string, `format` harus memasukkan nilai parameter, tipe harus string
Server::SET | Mengembalikan daftar, `format` harus memasukkan nilai parameter, tipe harus array
Server::MAP | Mengembalikan Map, `format` harus memasukkan nilai parameter, tipe harus array asosiatif

## Contoh Penggunaan

### Server

```php
use Swoole\Redis\Server;

define('DB_FILE', __DIR__ . '/db');

$server = new Server("127.0.0.1", 9501, SWOOLE_BASE);

if (is_file(DB_FILE)) {
    $server->data = unserialize(file_get_contents(DB_FILE));
} else {
    $server->data = array();
}

$server->setHandler('GET', function ($fd, $data) use ($server) {
    if (count($data) == 0) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'GET' command"));
    }

    $key = $data[0];
    if (empty($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    } else {
        return $server->send($fd, Server::format(Server::STRING, $server->data[$key]));
    }
});

$server->setHandler('SET', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'SET' command"));
    }

    $key = $data[0];
    $server->data[$key] = $data[1];
    return $server->send($fd, Server::format(Server::STATUS, "OK"));
});

$server->setHandler('sAdd', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sAdd' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }

    $count = 0;
    for ($i = 1; $i < count($data); $i++) {
        $value = $data[$i];
        if (!isset($server->data[$key][$value])) {
            $server->data[$key][$value] = 1;
            $count++;
        }
    }

    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('sMembers', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sMembers' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::SET, array_keys($server->data[$key])));
});

$server->setHandler('hSet', function ($fd, $data) use ($server) {
    if (count($data) < 3) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hSet' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }
    $field = $data[1];
    $value = $data[2];
    $count = !isset($server->data[$key][$field]) ? 1 : 0;
    $server->data[$key][$field] = $value;
    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('hGetAll', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hGetAll' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::MAP, $server->data[$key]));
});

$server->on('WorkerStart', function ($server) {
    $server->tick(10000, function () use ($server) {
        file_put_contents(DB_FILE, serialize($server->data));
    });
});

$server->start();
```

### Klien

```shell
$ redis-cli -h 127.0.0.1 -p 9501
127.0.0.1:9501> set name swoole
OK
127.0.0.1:9501> get name
"swoole"
127.0.0.1:9501> sadd swooler rango
(integer) 1
127.0.0.1:9501> sadd swooler twosee guoxinhua
(integer) 2
127.0.0.1:9501> smembers swooler
1) "rango"
2) "twosee"
3) "guoxinhua"
127.0.0.1:9501> hset website swoole "www.swoole.com"
(integer) 1
127.0.0.1:9501> hset website swoole "swoole.com"
(integer) 0
127.0.0.1:9501> hgetall website
1) "swoole"
2) "swoole.com"
127.0.0.1:9501> test
(error) ERR unknown command 'test'
127.0.0.1:9501>
```
