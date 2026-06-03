# Mendengarkan Banyak Port

`Swoole\Server` dapat mendengarkan banyak port, dan setiap port dapat mengatur cara penanganan protokol yang berbeda, misalnya port 80 menangani protokol HTTP, port 9507 menangani protokol TCP. Enkripsi transmisi `SSL/TLS` juga dapat diaktifkan hanya untuk port tertentu.

!> Misalnya server utama adalah protokol WebSocket atau HTTP, port TCP baru yang didengarkan (nilai kembalian dari [listen](/server/methods?id=listen), yaitu objek [Swoole\Server\Port](server/server_port.md), selanjutnya disebut port) secara default akan mewarisi pengaturan protokol dari Server utama. Harus memanggil method `set` dan `on` dari objek `port` secara terpisah untuk mengatur protokol baru agar protokol baru diaktifkan.

## Mendengarkan Port Baru

```php
//mengembalikan objek port
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```

## Mengatur Protokol Jaringan

```php
//memanggil method set pada objek port
$port1->set([
	'open_length_check' => true,
	'package_length_type' => 'N',
	'package_length_offset' => 0,
	'package_max_length' => 800000,
]);

$port3->set([
	'open_eof_split' => true,
	'package_eof' => "\r\n",
	'ssl_cert_file' => 'ssl.cert',
	'ssl_key_file' => 'ssl.key',
]);
```

## Mengatur Fungsi Callback

```php
//mengatur fungsi callback untuk setiap port
$port1->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});

$port1->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});

$port1->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$port2->on('packet', function ($serv, $data, $addr) {
    var_dump($data, $addr);
});
```

## Http/WebSocket

`Swoole\Http\Server` dan `Swoole\WebSocket\Server` karena diimplementasikan menggunakan subclass, tidak dapat membuat server HTTP atau WebSocket melalui method `listen` pada instance `Swoole\Server`.

Jika fungsi utama server adalah `RPC`, tetapi ingin menyediakan antarmuka manajemen Web sederhana. Dalam skenario seperti itu, Anda dapat membuat server `HTTP/WebSocket` terlebih dahulu, kemudian melakukan `listen` untuk port TCP asli.

### Contoh

```php
$http_server = new Swoole\Http\Server('0.0.0.0',9998);
$http_server->set(['daemonize'=> false]);
$http_server->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

//mendengarkan port TCP tambahan, membuka layanan TCP eksternal, dan mengatur callback server TCP
$tcp_server = $http_server->listen('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
//secara default port baru 9999 akan mewarisi pengaturan server utama, yaitu protokol HTTP
//perlu memanggil method set untuk menimpa pengaturan server utama
$tcp_server->set([]);
$tcp_server->on('receive', function ($server, $fd, $threadId, $data) {
    echo $data;
});

$http_server->start();
```

Dengan kode seperti ini, Anda dapat membuat Server yang menyediakan layanan HTTP sekaligus layanan TCP. Kombinasi kode yang lebih elegan terserah Anda.

## Pengaturan Gabungan Port Multi-Protokol TCP, HTTP, WebSocket

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_websocket_protocol' => true, // mengatur agar port ini mendukung protokol WebSocket
]);
```

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_http_protocol' => false, // mengatur port ini untuk menonaktifkan fungsi protokol HTTP
]);
```

Begitu juga dengan: `open_http_protocol`, `open_http2_protocol`, `open_mqtt_protocol`, dll.

## Parameter Opsional

* Port listening yang tidak memanggil method `set` untuk mengatur opsi pemrosesan protokol, akan mewarisi konfigurasi terkait dari server utama
* Server utama adalah server `HTTP/WebSocket`, jika parameter protokol tidak diatur, port yang didengarkan tetap akan diatur sebagai protokol `HTTP` atau `WebSocket`, dan tidak akan menjalankan callback [onReceive](/server/events?id=onreceive) yang diatur untuk port tersebut
* Server utama adalah server `HTTP/WebSocket`, port listening memanggil `set` untuk mengatur parameter konfigurasi, akan menghapus pengaturan protokol server utama. Port listening akan berubah menjadi protokol `TCP`. Jika port listening ingin tetap menggunakan protokol `HTTP/WebSocket`, perlu menambahkan `open_http_protocol => true` dan `open_websocket_protocol => true` dalam konfigurasi

**`port` dapat mengatur parameter melalui `set`:**

* Parameter socket: seperti `backlog`, `open_tcp_keepalive`, `open_tcp_nodelay`, `tcp_defer_accept`, dll.
* Terkait protokol: seperti `open_length_check`, `open_eof_check`, `package_length_type`, dll.
* Terkait sertifikat SSL: seperti `ssl_cert_file`, `ssl_key_file`, dll.

Detail lebih lanjut dapat merujuk ke [bab Konfigurasi](/server/setting)

## Callback Opsional

`port` yang tidak memanggil method `on` untuk mengatur fungsi callback, secara default menggunakan fungsi callback server utama. `port` dapat mengatur callback berikut melalui method `on`:

### Server TCP

* onConnect
* onClose
* onReceive

### Server UDP

* onPacket
* onReceive

### Server HTTP

* onRequest

### Server WebSocket

* onMessage
* onOpen
* onHandshake

!> Fungsi callback dari port listening yang berbeda tetap dieksekusi dalam ruang proses `Worker` yang sama

## Iterasi Koneksi di Banyak Port

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9514, SWOOLE_BASE);

$tcp = $server->listen("0.0.0.0", 9515, SWOOLE_SOCK_TCP);
$tcp->set([]);

$server->on("open", function ($serv, $req) {
    echo "new WebSocket Client, fd={$req->fd}\n";
});

$server->on("message", function ($serv, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $serv->push($frame->fd, "this is server OnMessage");
});

$tcp->on('receive', function ($server, $fd, $reactor_id, $data) {
    //hanya mengiterasi koneksi port 9514, karena menggunakan $server, bukan $tcp
    $websocket = $server->ports[0];
    foreach ($websocket->connections as $_fd) {
        var_dump($_fd);
        if ($server->exist($_fd)) {
            $server->push($_fd, "this is server onReceive");
        }
    }
    $server->send($fd, 'receive: '.$data);
});

$server->start();
```
