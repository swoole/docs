# Klien Redis Coroutine

!> Klien ini tidak lagi direkomendasikan. Disarankan menggunakan `Swoole\Runtime::enableCoroutine + phpredis` atau `predis`, yaitu [one-click coroutine](/runtime) untuk klien `redis` native `PHP`.

!> Setelah `Swoole 6.0`, klien Redis coroutine ini telah dihapus.

## Contoh Penggunaan

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});
```

!> `subscribe` `pSubscribe` tidak dapat digunakan dengan `defer(true)`.

## Method

!> Penggunaan method pada dasarnya konsisten dengan [phpredis](https://github.com/phpredis/phpredis).

Berikut adalah penjelasan yang berbeda dari implementasi [phpredis](https://github.com/phpredis/phpredis):

1. Perintah Redis yang belum diimplementasikan: `scan object sort migrate hscan sscan zscan`;

2. Penggunaan `subscribe pSubscribe`, tidak perlu mengatur fungsi callback;

3. Dukungan untuk serialisasi variabel PHP, atur parameter ketiga method `connect()` ke `true` untuk mengaktifkan serialisasi variabel `PHP`, default `false`

### __construct()

Method konstruktor klien Redis coroutine, dapat mengatur opsi konfigurasi koneksi `Redis`, konsisten dengan parameter method `setOptions()`.

```php
Swoole\Coroutine\Redis::__construct(array $options = null);
```

### setOptions()

Method ini ditambahkan setelah versi 4.2.10, digunakan untuk mengatur beberapa konfigurasi klien `Redis` setelah konstruksi dan koneksi.

Fungsi ini bergaya Swoole, perlu dikonfigurasi melalui array pasangan `Key-Value`.

```php
Swoole\Coroutine\Redis->setOptions(array $options): void
```

  * **Opsi yang Dapat Dikonfigurasi**

Key | Keterangan
---|---
`connect_timeout` | Timeout koneksi, default adalah `socket_connect_timeout` coroutine global (1 detik)
`timeout` | Timeout, default adalah `socket_timeout` coroutine global, lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)
`serialize` | Serialisasi otomatis, default nonaktif
`reconnect` | Jumlah percobaan koneksi otomatis. Jika koneksi ditutup normal karena timeout atau alasan lain, saat permintaan berikutnya, akan secara otomatis mencoba menyambung sebelum mengirim permintaan. Default `1` kali (`true`). Setelah gagal dengan jumlah yang ditentukan, tidak akan terus mencoba dan perlu menyambung manual. Mekanisme ini hanya digunakan untuk menjaga koneksi, tidak akan mengirim ulang permintaan yang menyebabkan error pada interface non-idempoten.
`compatibility_mode` | Solusi kompatibilitas untuk ketidaksesuaian hasil fungsi `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore` dengan `php-redis`. Setelah diaktifkan, `Co\Redis` dan `php-redis` mengembalikan hasil yang konsisten. Default nonaktif【Opsi ini tersedia di `v4.4.0` atau versi lebih tinggi】

### set()

Menyimpan data.

```php
Swoole\Coroutine\Redis->set(string $key, mixed $value, array|int $option): bool
```

  * **Parameter**

    * **`string $key`**
      * **Fungsi**: Kunci data
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $value`**
      * **Fungsi**: Konten data【tipe non-string akan otomatis diserialisasi】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`string $options`**
      * **Fungsi**: Opsi
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

      !> Penjelasan `$option`:  
      `int`: Mengatur waktu kedaluwarsa, mis. `3600`  
      `array`: Pengaturan kedaluwarsa lanjutan, mis. `['nx', 'ex' => 10]` 、`['xx', 'px' => 1000]`

      !> `px`: Menunjukkan waktu kedaluwarsa dalam milidetik  
      `ex`: Menunjukkan waktu kedaluwarsa dalam detik  
      `nx`: Mengatur timeout jika tidak ada  
      `xx`: Mengatur timeout jika sudah ada

### request()

Mengirim perintah khusus ke server Redis. Mirip dengan rawCommand di phpredis.

```php
Swoole\Coroutine\Redis->request(array $args): void
```

  * **Parameter**

    * **`array $args`**
      * **Fungsi**: Daftar argumen, harus dalam format array.【Elemen pertama harus berupa perintah `Redis`, elemen lainnya adalah parameter perintah, yang akan secara otomatis dikemas menjadi permintaan protokol `Redis` untuk dikirim.】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Nilai Kembali**

Tergantung pada cara server `Redis` menangani perintah. Mungkin mengembalikan tipe angka, boolean, string, array, dll.

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379); // Jika UNIX Socket lokal, parameter host harus diisi dengan format seperti `unix://tmp/your_file.sock`
    $res = $redis->request(['object', 'encoding', 'key1']);
    var_dump($res);
});
```

## Properti

### errCode

Kode error.

Kode Error | Keterangan
---|---
1 | Error in read or write
2 | Everything else...
3 | End of file
4 | Protocol error
5 | Out of memory

### errMsg

Pesan error.

### connected

Memeriksa apakah klien `Redis` saat ini terhubung ke server.

## Konstanta

Digunakan untuk method `multi($mode)`, default mode `SWOOLE_REDIS_MODE_MULTI`:

* SWOOLE_REDIS_MODE_MULTI
* SWOOLE_REDIS_MODE_PIPELINE

Digunakan untuk menentukan nilai kembali perintah `type()`:

* SWOOLE_REDIS_TYPE_NOT_FOUND
* SWOOLE_REDIS_TYPE_STRING
* SWOOLE_REDIS_TYPE_SET
* SWOOLE_REDIS_TYPE_LIST
* SWOOLE_REDIS_TYPE_ZSET
* SWOOLE_REDIS_TYPE_HASH

## Mode Transaksi

Mode transaksi Redis dapat diimplementasikan menggunakan `multi` dan `exec`.

  * **Petunjuk**

    * Gunakan perintah `multi` untuk memulai transaksi, lalu semua perintah akan diantrekan untuk dieksekusi
    * Gunakan perintah `exec` untuk mengeksekusi semua operasi dalam transaksi dan mengembalikan semua hasil sekaligus

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key3', 'rango');
    $redis->get('key1');
    $redis->get('key2');
    $redis->get('key3');

    $result = $redis->exec();
    var_dump($result);
});
```

## Mode Langganan

!> Tersedia di Swoole versi >= v4.2.13, **versi 4.2.12 ke bawah ada BUG pada mode langganan**

### Berlangganan

Berbeda dengan `phpredis`, `subscribe/psubscribe` bergaya coroutine.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // atau gunakan psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg adalah array, berisi informasi berikut
            // $type # tipe nilai kembali: menunjukkan keberhasilan langganan
            // $name # nama channel yang dilanggan atau nama channel sumber
            // $info  # jumlah channel yang sudah dilanggan atau konten informasi
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') { // atau psubscribe
                // Pesan keberhasilan langganan channel, satu untuk setiap channel
            } else if ($type == 'unsubscribe' && $info == 0){ // atau punsubscribe
                break; // Menerima pesan berhenti berlangganan, dan sisa channel yang dilanggan adalah 0, berhenti menerima, keluar dari loop
            } else if ($type == 'message') {  // Jika psubscribe, ini adalah pmessage
                var_dump($name); // Cetak nama channel sumber
                var_dump($info); // Cetak pesan
                // balabalaba.... // Proses pesan
                if ($need_unsubscribe) { // Dalam kondisi tertentu perlu berhenti berlangganan
                    $redis->unsubscribe(); // Lanjutkan recv menunggu proses berhenti berlangganan selesai
                }
            }
        }
    }
});
```

### Berhenti Berlangganan

Berhenti berlangganan menggunakan `unsubscribe/punsubscribe`, `$redis->unsubscribe(['channel1'])`

Pada saat ini, `$redis->recv()` akan menerima pesan berhenti berlangganan. Jika berhenti berlangganan beberapa channel, akan menerima beberapa pesan.

!> Catatan: Setelah berhenti berlangganan, pastikan untuk melanjutkan `recv()` sampai menerima pesan berhenti berlangganan terakhir (`$msg[2] == 0`). Setelah menerima pesan ini, mode langganan akan keluar.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // or use psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg is an array containing the following information
            // $type # return type: show subscription success
            // $name # subscribed channel name or source channel name
            // $info  # the number of channels or information content currently subscribed
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') // or psubscribe
            {
                // channel subscription success message
            }
            else if ($type == 'unsubscribe' && $info == 0) // or punsubscribe
            {
                break; // received the unsubscribe message, and the number of channels remaining for the subscription is 0, no longer received, break the loop
            }
            else if ($type == 'message') // if it's psubscribe，here is pmessage
            {
                // print source channel name
                var_dump($name);
                // print message
                var_dump($info);
                // handle message
                if ($need_unsubscribe) // in some cases, you need to unsubscribe
                {
                    $redis->unsubscribe(); // continue recv to wait unsubscribe finished
                }
            }
        }
    }
});
```

## Mode Kompatibilitas

Masalah ketidaksesuaian format hasil yang dikembalikan oleh perintah `Co\Redis` `hmGet/hGetAll/zrange/zrevrange/zrangebyscore/zrevrangebyscore` dengan ekstensi `phpredis` telah diselesaikan [#2529](https://github.com/swoole/swoole-src/pull/2529).

Untuk memastikan konsistensi hasil antara `Co\Redis` dan `phpredis` untuk kompatibilitas dengan versi lama, tambahkan konfigurasi `$redis->setOptions(['compatibility_mode' => true]);`, setelah itu hasil akan konsisten.

!> Tersedia di Swoole versi >= `v4.4.0`

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->setOptions(['compatibility_mode' => true]);
    $redis->connect('127.0.0.1', 6379);

    $co_get_val = $redis->get('novalue');
    $co_zrank_val = $redis->zRank('novalue', 1);
    $co_hgetall_val = $redis->hGetAll('hkey');
    $co_hmget_val = $redis->hmGet('hkey', array(3, 5));
    $co_zrange_val = $redis->zRange('zkey', 0, 99, true);
    $co_zrevrange_val = $redis->zRevRange('zkey', 0, 99, true);
    $co_zrangebyscore_val = $redis->zRangeByScore('zkey', 0, 99, ['withscores' => true]);
    $co_zrevrangebyscore_val = $redis->zRevRangeByScore('zkey', 99, 0, ['withscores' => true]);
});
```
