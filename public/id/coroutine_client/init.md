# Klien Coroutine <!-- {docsify-ignore-all} -->

Berikut adalah kelas bawaan Swoole untuk klien coroutine. Yang ditandai dengan ⚠️ tidak disarankan untuk digunakan lagi. Anda bisa menggunakan fungsi native PHP + [one-click coroutine](/runtime).

* [Klien TCP/UDP/UnixSocket](coroutine_client/client.md)
* [Klien Socket](coroutine_client/socket.md)
* [Klien HTTP/WebSocket](coroutine_client/http_client.md)
* [Klien HTTP2](coroutine_client/http2_client.md)
* [Klien PostgreSQL](coroutine_client/postgresql.md)
* [Klien FastCGI](coroutine_client/fastcgi.md)
* ⚠️ [Klien Redis](coroutine_client/redis.md)
* ⚠️ [Klien MySQL](coroutine_client/mysql.md)
* [System](/coroutine/system) API Sistem

## Aturan Timeout

Semua permintaan jaringan (membangun koneksi, mengirim data, menerima data) bisa mengalami timeout. `Swoole` klien coroutine menyediakan tiga cara untuk mengatur timeout:

1. Mengirimkan durasi timeout sebagai parameter method, seperti [Co\Client->connect()](/coroutine_client/client?id=connect), [Co\Http\Client->recv()](/coroutine_client/http_client?id=recv), [Co\MySQL->query()](/coroutine_client/mysql?id=query), dan lain-lain.

!> Cara ini memiliki lingkup dampak terkecil (hanya berlaku untuk pemanggilan fungsi saat ini), dengan prioritas tertinggi (pemanggilan fungsi saat ini akan mengabaikan pengaturan timeout `2` dan `3` di bawah).

2. Mengatur timeout melalui method `set()` atau `setOption()` dari kelas klien coroutine `Swoole`, contoh:

```php
$client = new Co\Client(SWOOLE_SOCK_TCP);
//atau
$client = new Co\Http\Client("127.0.0.1", 80);
//atau
$client = new Co\Http2\Client("127.0.0.1", 443, true);
$client->set(array(
    'timeout' => 0.5,//total timeout, termasuk koneksi, pengiriman, dan penerimaan
    'connect_timeout' => 1.0,//timeout koneksi, akan menimpa total timeout pertama
    'write_timeout' => 10.0,//timeout pengiriman, akan menimpa total timeout pertama
    'read_timeout' => 0.5,//timeout penerimaan, akan menimpa total timeout pertama
));

//Co\Redis() tidak memiliki konfigurasi write_timeout dan read_timeout
$client = new Co\Redis();
$client->setOption(array(
    'timeout' => 1.0,//total timeout, termasuk koneksi, pengiriman, dan penerimaan
    'connect_timeout' => 0.5,//timeout koneksi, akan menimpa total timeout pertama
));

//Co\MySQL() tidak memiliki fitur konfigurasi set
$client = new Co\MySQL();

//Co\Socket dikonfigurasi melalui setOption
$socket = new Co\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = array('sec'=>1, 'usec'=>500000);
$socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout);//timeout penerimaan data
$socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout);//konfigurasi timeout koneksi dan pengiriman data
```

!> Cara ini hanya memengaruhi kelas saat ini dan akan ditimpa oleh cara ke-`1`, mengabaikan konfigurasi cara ke-`3` di bawah.

3. Seperti yang terlihat, aturan pengaturan timeout pada `2` cara di atas rumit dan tidak konsisten. Untuk menghindari kehati-hatian berlebihan dari pengembang, mulai versi `v4.2.10` semua klien coroutine menyediakan aturan pengaturan timeout global yang seragam dengan dampak terbesar dan prioritas terendah, sebagai berikut:

```php
Co::set([
    'socket_timeout' => 5,
    'socket_connect_timeout' => 1,
    'socket_read_timeout' => 1,
    'socket_write_timeout' => 1,
]);
```

+ `-1`: menunjukkan tidak pernah timeout
+ `0`: menunjukkan tidak mengubah timeout
+ `nilai lain > 0`: menunjukkan pengaturan timer timeout untuk jumlah detik yang sesuai, dengan presisi maksimum `1 milidetik`, bertipe float, `0.5` berarti `500 milidetik`
+ `socket_connect_timeout`: menunjukkan timeout untuk membangun koneksi TCP, **default `1 detik`** , mulai versi `v4.5.x` **default `2 detik`**
+ `socket_timeout`: menunjukkan timeout untuk operasi baca/tulis TCP, **default `-1`** , mulai versi `v4.5.x` **default `60 detik`**. Jika ingin mengatur baca dan tulis secara terpisah, lihat konfigurasi di bawah
+ `socket_read_timeout`: ditambahkan di `v4.3`, menunjukkan timeout operasi **baca** TCP, **default `-1`** , mulai versi `v4.5.x` **default `60 detik`**
+ `socket_write_timeout`: ditambahkan di `v4.3`, menunjukkan timeout operasi **tulis** TCP, **default `-1`** , mulai versi `v4.5.x` **default `60 detik`**

!> **Artinya:** sebelum versi `v4.5.x` dari klien coroutine `Swoole`, jika tidak ada timeout yang diatur menggunakan cara `1` atau `2` di atas, default timeout koneksi adalah `1 detik`, sedangkan operasi baca/tulis tidak pernah timeout;  
mulai versi `v4.5.x`, default timeout koneksi adalah `60 detik`, dan timeout operasi baca/tulis adalah `60 detik`;  
Jika timeout global diubah di tengah jalan, itu tidak akan berlaku untuk socket yang sudah dibuat.

### Timeout Library Jaringan Resmi PHP

Selain klien coroutine yang disediakan `Swoole` di atas, dalam [one-click coroutine](/runtime) menggunakan method native PHP, dan timeout-nya dipengaruhi oleh konfigurasi [default_socket_timeout](http://php.net/manual/zh/filesystem.configuration.php). Pengembang dapat mengaturnya secara terpisah melalui `ini_set('default_socket_timeout', 60)`, dengan nilai default 60.
