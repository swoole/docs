# UDP Server

## Kode Program

Tulis kode berikut ke dalam udpServer.php.

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

// Listen event Packet (ada paket data masuk).
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Server: {$data}");
});

// Jalankan server
$server->start();
```

UDP Server beda sama TCP Server. UDP nggak punya konsep koneksi. Setelah server jalan, client nggak perlu Connect dulu, langsung bisa ngirim paket data ke port 9502 yang di-dengarkan server. Event yang dipake adalah `onPacket`.

* `$clientInfo` berisi informasi client, berupa array yang punya IP dan port client.
* Panggil `$server->sendto` buat ngirim data balik ke client.

!> Secara default Docker pake protokol TCP buat komunikasi. Kalo mau pake UDP, kamu harus konfigurasi network Docker-nya.
```shell
docker run -p 9502:9502/udp <image-name>
```

## Menjalankan Service

```shell
php udpServer.php
```

Kamu bisa pake `netcat -u` buat connect dan test UDP server.

```shell
netcat -u 127.0.0.1 9502
hello
Server: hello
```
