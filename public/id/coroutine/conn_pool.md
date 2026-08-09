# Koneksi Pool

Swoole sejak versi `v4.4.13` menyediakan connection pool coroutine bawaan. Bab ini akan menjelaskan cara menggunakan connection pool yang sesuai.

## ConnectionPool

[ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php), connection pool dasar, berdasarkan Channel dengan penjadwalan otomatis, mendukung constructor apa pun (`callable`), constructor harus mengembalikan objek koneksi.

* Method `get` mendapatkan koneksi (akan membuat koneksi baru jika pool belum penuh)
* Method `put` mengembalikan koneksi
* Method `fill` mengisi connection pool (membuat koneksi terlebih dahulu)
* Method `close` menutup connection pool

!> [Komponen DB](https://github.com/simple-swoole/db) dari [framework Simps](https://simps.io) mengenkapsulasi Database, mengimplementasikan fungsi seperti pengembalian koneksi otomatis dan transaksi. Bisa dijadikan referensi atau langsung digunakan. Lihat [dokumentasi Simps](https://simps.io/#/zh-cn/database/mysql).

## Database

Enkapsulasi tingkat tinggi untuk berbagai connection pool database dan proxy objek, mendukung reconnect otomatis saat koneksi putus. Saat ini mendukung tiga tipe database: PDO, Mysqli, Redis.

* `PDOConfig`, `PDOProxy`, `PDOPool`
* `MysqliConfig`, `MysqliProxy`, `MysqliPool`
* `RedisConfig`, `RedisProxy`, `RedisPool`

!> 1. Reconnect MySQL dapat memulihkan sebagian besar konteks koneksi (fetch mode, attribute yang sudah diatur, Statement yang sudah dikompilasi, dll.), namun konteks seperti transaksi tidak bisa dipulihkan. Jika koneksi yang sedang dalam transaksi terputus, exception akan dilempar. Harap evaluasi sendiri keandalan reconnect;
2. Mengembalikan koneksi yang sedang dalam transaksi ke connection pool adalah perilaku tidak terdefinisi, developer harus memastikan koneksi yang dikembalikan bisa digunakan kembali;
3. Jika ada objek koneksi yang mengalami exception dan tidak bisa digunakan kembali, developer perlu memanggil `$pool->put(null);` untuk mengembalikan koneksi kosong demi menjaga keseimbangan jumlah pool.

### PDOPool/MysqliPool/RedisPool :id=pool

Digunakan untuk membuat objek connection pool, memiliki dua parameter, yaitu objek Config dan ukuran connection pool.

```php
$pool = new \Swoole\Database\PDOPool(Swoole\Database\PDOConfig $config, int $size);

$pool = new \Swoole\Database\MysqliPool(Swoole\Database\MysqliConfig $config, int $size);

$pool = new \Swoole\Database\RedisPool(Swoole\Database\RedisConfig $config, int $size);
```

  * **Parameter** 

    * **`$config`**
      * **Fungsi**: Objek Config yang sesuai, penggunaan detail bisa lihat [contoh penggunaan](/coroutine/conn_pool?id=contoh-penggunaan) di bawah
      * **Bawaan**: Tidak ada
      * **Nilai Lain**: [PDOConfig](https://github.com/swoole/library/blob/master/src/core/Database/PDOConfig.php), [RedisConfig](https://github.com/swoole/library/blob/master/src/core/Database/RedisConfig.php), [MysqliConfig](https://github.com/swoole/library/blob/master/src/core/Database/MysqliConfig.php)
      
    * **`int $size`**
      * **Fungsi**: Jumlah connection pool
      * **Bawaan**: 64
      * **Nilai Lain**: Tidak ada

## Contoh Penggunaan

### PDO

```php
<?php
declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Swoole\Runtime;

const N = 1024;

Runtime::enableCoroutine();
$s = microtime(true);
Coroutine\run(function () {
    $pool = new PDOPool((new PDOConfig)
        ->withHost('127.0.0.1')
        ->withPort(3306)
        // ->withUnixSocket('/tmp/mysql.sock')
        ->withDbName('test')
        ->withCharset('utf8mb4')
        ->withUsername('root')
        ->withPassword('root')
    );
    for ($n = N; $n--;) {
        Coroutine::create(function () use ($pool) {
            $pdo = $pool->get();
            $statement = $pdo->prepare('SELECT ? + ?');
            if (!$statement) {
                throw new RuntimeException('Prepare failed');
            }
            $a = mt_rand(1, 100);
            $b = mt_rand(1, 100);
            $result = $statement->execute([$a, $b]);
            if (!$result) {
                throw new RuntimeException('Execute failed');
            }
            $result = $statement->fetchAll();
            if ($a + $b !== (int)$result[0][0]) {
                throw new RuntimeException('Bad result');
            }
            $pool->put($pdo);
        });
    }
});
$s = microtime(true) - $s;
echo 'Use ' . $s . 's for ' . N . ' queries' . PHP_EOL;
```

### Redis

```php
<?php
declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;
use Swoole\Runtime;

const N = 1024;

Runtime::enableCoroutine();
$s = microtime(true);
Coroutine\run(function () {
    $pool = new RedisPool((new RedisConfig)
        ->withHost('127.0.0.1')
        ->withPort(6379)
        ->withAuth('')
        ->withDbIndex(0)
        ->withTimeout(1)
    );
    for ($n = N; $n--;) {
        Coroutine::create(function () use ($pool) {
            $redis = $pool->get();
            $result = $redis->set('foo', 'bar');
            if (!$result) {
                throw new RuntimeException('Set failed');
            }
            $result = $redis->get('foo');
            if ($result !== 'bar') {
                throw new RuntimeException('Get failed');
            }
            $pool->put($redis);
        });
    }
});
$s = microtime(true) - $s;
echo 'Use ' . $s . 's for ' . (N * 2) . ' queries' . PHP_EOL;
```

### Mysqli

```php
<?php
declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Database\MysqliConfig;
use Swoole\Database\MysqliPool;
use Swoole\Runtime;

const N = 1024;

Runtime::enableCoroutine();
$s = microtime(true);
Coroutine\run(function () {
    $pool = new MysqliPool((new MysqliConfig)
        ->withHost('127.0.0.1')
        ->withPort(3306)
        // ->withUnixSocket('/tmp/mysql.sock')
        ->withDbName('test')
        ->withCharset('utf8mb4')
        ->withUsername('root')
        ->withPassword('root')
    );
    for ($n = N; $n--;) {
        Coroutine::create(function () use ($pool) {
            $mysqli = $pool->get();
            $statement = $mysqli->prepare('SELECT ? + ?');
            if (!$statement) {
                throw new RuntimeException('Prepare failed');
            }
            $a = mt_rand(1, 100);
            $b = mt_rand(1, 100);
            if (!$statement->bind_param('dd', $a, $b)) {
                throw new RuntimeException('Bind param failed');
            }
            if (!$statement->execute()) {
                throw new RuntimeException('Execute failed');
            }
            if (!$statement->bind_result($result)) {
                throw new RuntimeException('Bind result failed');
            }
            if (!$statement->fetch()) {
                throw new RuntimeException('Fetch failed');
            }
            if ($a + $b !== (int)$result) {
                throw new RuntimeException('Bad result');
            }
            while ($statement->fetch()) {
                continue;
            }
            $pool->put($mysqli);
        });
    }
});
$s = microtime(true) - $s;
echo 'Use ' . $s . 's for ' . N . ' queries' . PHP_EOL;
```
