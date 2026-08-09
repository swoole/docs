# TCP Server

## Kode Program

Tulis kode berikut ke dalam tcpServer.php.

```php
// Buat object Server, listen di 127.0.0.1:9501.
$server = new Swoole\Server('127.0.0.1', 9501);

// Listen event Connect.
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// Listen event Receive (ada data masuk).
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// Listen event Close (koneksi ditutup).
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// Jalankan server
$server->start(); 
```

Ini bakal bikin server `TCP` yang listening di port `9501` di lokal. Logikanya sederhana: pas client ngirim string `hello` lewat socket, server bakal balas `Server: hello`.

Karena `Server` bersifat asynchronous, kita nulis program dengan cara listening ke event. Pas event tertentu terjadi, sistem di bawahan bakal manggil function yang udah ditentukan. Misalnya pas ada koneksi `TCP` baru masuk, bakal ngejalanin callback [onConnect](/server/events?id=onconnect); pas ada koneksi yang ngirim data ke server, bakal nge-panggil function [onReceive](/server/events?id=onreceive).

* Server bisa nanganin ribuan client sekaligus, `$fd` itu identifier unik buat setiap koneksi client.
* Panggil `$server->send()` buat ngirim data ke client tertentu, parameter `$fd` adalah identifier client-nya.
* Panggil `$server->close()` buat maksa nutup koneksi client tertentu.
* Client juga bisa nutup koneksi sendiri, nah ini bakal picu event callback [onClose](/server/events?id=onclose).

## Menjalankan Program

```shell
php tcpServer.php
```

Jalankan `server.php` di command line. Kalo udah berhasil, kamu bisa pake tool `netstat` buat liat port `9501` udah mulai listening.

Sekarang kamu bisa pake `telnet` atau `netcat` buat connect ke server.

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```

## Cara Sederhana Ngecek Kalo Gagal Connect

* Di `Linux`, pake `netstat -an | grep <port>` buat liat apakah port-nya udah terbuka dan dalam status `Listening`.
* Kalo udah cek, cek firewall.
* Perhatikan IP address yang dipake server. Kalo pake `127.0.0.1` (loopback), client cuma bisa connect lewat `127.0.0.1` juga.
* Kalo pake server Alibaba Cloud atau Tencent Cloud, pastiin port-nya udah dibuka di security group.

## Masalah Batasan Paket Data TCP

Lihat [TCP Data Packet Boundary Issue](/learn?id=tcp数据包边界问题).
