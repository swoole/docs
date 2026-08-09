# Swoole\WebSocket\Server

?> Dengan dukungan server `WebSocket` bawaan, hanya dengan beberapa baris kode `PHP` dapat menulis server `WebSocket` multi-proses [IO asinkron](/learn?id=同步io异步io).

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});

$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();
```

* **Klien**

  * Browser `Chrome/Firefox/IE/Safari` versi tinggi memiliki klien `WebSocket` bahasa `JS` bawaan
  * Klien `WebSocket` bawaan framework pengembangan mini program WeChat
  * Di program `PHP` [IO asinkron](/learn?id=同步io异步io) dapat menggunakan [Swoole\Coroutine\Http](/coroutine_client/http_client) sebagai klien `WebSocket`
  * Di program `PHP` sinkron blocking seperti `Apache/PHP-FPM` dapat menggunakan [klien WebSocket sinkron](https://github.com/matyhtf/framework/blob/master/libs/Swoole/Client/WebSocket.php) yang disediakan `swoole/framework`
  * Klien non-`WebSocket` tidak bisa berkomunikasi dengan server `WebSocket`

* **Cara menentukan apakah koneksi adalah klien WebSocket**

?> Dengan menggunakan [contoh berikut](/server/methods?id=getclientinfo) untuk mendapatkan informasi koneksi, array yang dikembalikan memiliki item [websocket_status](/websocket_server?id=连接状态), berdasarkan status ini dapat menentukan apakah itu klien `WebSocket`.
```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    $client = $server->getClientInfo($frame->fd);
    // Atau $client = $server->connection_info($frame->fd);
    if (isset($client['websocket_status'])) {
        echo "Ini koneksi websocket";
    } else {
        echo "Bukan koneksi websocket";
    }
});
```

## Event

?> Selain menerima fungsi callback dari kelas dasar [Swoole\Server](/server/methods) dan [Swoole\Http\Server](/http_server), server `WebSocket` menambahkan `4` pengaturan fungsi callback. Di antaranya:

* Fungsi callback `onMessage` wajib
* Fungsi callback `onOpen`, `onHandShake` dan `onBeforeHandShakeResponse` (disediakan Swoole5) opsional

### onBeforeHandshakeResponse

!> Swoole versi >= `v5.0.0` tersedia

?> **Terjadi sebelum koneksi `WebSocket` dibuat. Jika Anda tidak perlu menangani proses jabat tangan secara kustom, tetapi ingin mengatur beberapa informasi `http header` ke header respons, maka bisa memanggil event ini.**

```php
onBeforeHandshakeResponse(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

### onHandShake

?> **Melakukan jabat tangan setelah koneksi `WebSocket` dibuat. Server `WebSocket` akan otomatis melakukan proses jabat tangan. Jika pengguna ingin menangani sendiri proses jabat tangan, dapat mengatur fungsi callback event `onHandShake`.**

```php
onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

* **Catatan**

  * Callback event `onHandShake` opsional
  * Setelah mengatur fungsi callback `onHandShake`, event `onOpen` tidak akan terpicu lagi, kode aplikasi harus menangani sendiri, dapat menggunakan `$server->defer` untuk memanggil logika `onOpen`
  * Di `onHandShake` harus memanggil [response->status()](/http_server?id=status) untuk mengatur kode status menjadi `101` dan memanggil [response->end()](/http_server?id=end) sebagai respons, jika tidak jabat tangan akan gagal
  * Protokol jabat tangan bawaan adalah `Sec-WebSocket-Version: 13`, browser versi rendah perlu mengimplementasikan jabat tangan sendiri

* **Perhatian**

!> Jika perlu menangani `handshake` sendiri, baru atur fungsi callback ini. Jika tidak perlu proses jabat tangan "kustom", jangan atur callback ini, gunakan jabat tangan default `Swoole`. Berikut adalah yang harus ada dalam fungsi callback event `handshake` "kustom":

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // print_r( $request->header );
    // if (Jika tidak memenuhi kondisi kustom saya, maka return end output, return false, jabat tangan gagal) {
    //    $response->end();
    //     return false;
    // }

    // Verifikasi algoritma koneksi jabat tangan websocket
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(
        sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        )
    );

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $response->end();
});
```

!> Setelah mengatur fungsi callback `onHandShake`, event `onOpen` tidak akan terpicu lagi, kode aplikasi harus menangani sendiri, dapat menggunakan `$server->defer` untuk memanggil logika `onOpen`

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // Melewatkan konten jabat tangan
    $response->status(101);
    $response->end();

    global $server;
    $fd = $request->fd;
    $server->defer(function () use ($fd, $server)
    {
      echo "Client connected\n";
      $server->push($fd, "hello, welcome\n");
    });
});
```

### onOpen

?> **Fungsi ini akan dipanggil saat klien `WebSocket` berhasil terhubung dengan server dan menyelesaikan jabat tangan.**

```php
onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request);
```

* **Catatan**

    * `$request` adalah objek request [HTTP](/http_server?id=httprequest), berisi informasi request jabat tangan yang dikirim klien
    * Di fungsi event `onOpen` dapat memanggil [push](/websocket_server?id=push) untuk mengirim data ke klien atau [close](/server/methods?id=close) untuk menutup koneksi
    * Callback event `onOpen` opsional

### onMessage

?> **Fungsi ini akan dipanggil saat server menerima frame data dari klien.**

```php
onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
```

* **Catatan**

  * `$frame` adalah objek [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), berisi informasi frame data yang dikirim klien
  * Callback `onMessage` harus diatur, jika tidak server tidak dapat dijalankan
  * Frame `ping` yang dikirim klien tidak akan memicu `onMessage`, level bawah akan otomatis membalas paket `pong`, dapat juga mengatur parameter [open_websocket_ping_frame](/websocket_server?id=open_websocket_ping_frame) untuk penanganan manual

!> Jika `$frame->data` bertipe teks, format encoding pasti `UTF-8`, ini ditentukan oleh protokol `WebSocket`

### onRequest

?> `Swoole\WebSocket\Server` mewarisi dari [Swoole\Http\Server](/http_server), jadi semua `API` dan opsi konfigurasi yang disediakan `Http\Server` dapat digunakan. Silakan lihat bab [Swoole\Http\Server](/http_server).

* Jika mengatur callback [onRequest](/http_server?id=on), `WebSocket\Server` juga dapat berfungsi sebagai server `HTTP`
* Jika tidak mengatur callback [onRequest](/http_server?id=on), `WebSocket\Server` akan mengembalikan halaman error `HTTP 400` saat menerima request `HTTP`
* Jika ingin memicu semua push `WebSocket` melalui penerimaan `HTTP`, perhatikan masalah lingkup. Untuk gaya prosedural gunakan `global` untuk mereferensi `Swoole\WebSocket\Server`, untuk gaya berorientasi objek dapat mengatur `Swoole\WebSocket\Server` sebagai properti anggota

#### Gaya Kode Prosedural

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});
$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    global $server;//memanggil server eksternal
    // $server->connections iterasi semua fd pengguna koneksi websocket, push ke semua pengguna
    foreach ($server->connections as $fd) {
        // Perlu memeriksa dulu apakah koneksi websocket yang benar, jika tidak bisa gagal push
        if ($server->isEstablished($fd)) {
            $server->push($fd, $request->get['message']);
        }
    }
});
$server->start();
```

#### Gaya Kode Berorientasi Objek

```php
class WebSocketServer
{
    public $server;

    public function __construct()
    {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
        $this->server->on('open', function (Swoole\WebSocket\Server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // Menerima http request, ambil nilai parameter message dari get, push ke pengguna
            // $this->server->connections iterasi semua fd koneksi websocket, push ke semua pengguna
            foreach ($this->server->connections as $fd) {
                // Perlu memeriksa dulu apakah koneksi websocket yang benar, jika tidak bisa gagal push
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        $this->server->start();
    }
}

new WebSocketServer();
```

### onDisconnect

?> **Hanya terpicu saat koneksi non-WebSocket ditutup.**

!> Swoole versi >= `v4.7.0` tersedia

```php
onDisconnect(Swoole\WebSocket\Server $server, int $fd)
```

!> Jika mengatur callback event `onDisconnect`, request non-WebSocket atau saat memanggil method `$response->close()` di [onRequest](/websocket_server?id=onrequest), akan memicu callback `onDisconnect`. Sedangkan di event [onRequest](/websocket_server?id=onrequest) yang berakhir normal tidak akan memanggil event `onClose` atau `onDisconnect`.  

## Method

`Swoole\WebSocket\Server` adalah subclass dari [Swoole\Server](/server/methods), jadi dapat memanggil semua method `Server`.

Perlu diperhatikan server `WebSocket` mengirim data ke klien harus menggunakan method `Swoole\WebSocket\Server::push`, method ini akan melakukan pengemasan protokol `WebSocket`. Sedangkan method [Swoole\Server->send()](/server/methods?id=send) adalah antarmuka pengiriman `TCP` mentah.

Method [Swoole\WebSocket\Server->disconnect()](/websocket_server?id=disconnect) dapat menutup koneksi `WebSocket` dari sisi server secara aktif, dapat menentukan [kode status penutupan](/websocket_server?id=websocket关闭帧状态码) (menurut protokol `WebSocket`, kode status yang dapat digunakan adalah integer desimal, nilai dapat `1000` atau `4000-4999`) dan alasan penutupan (string encoding `utf-8`, panjang byte tidak melebihi `125`). Jika tidak ditentukan, kode status `1000`, alasan penutupan kosong.

### push

?> **Mengirim data ke koneksi klien `WebSocket`, panjang maksimum tidak boleh melebihi `2M`.**

```php
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool

// v4.4.12 diubah menjadi parameter flags
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): bool
```

* **Parameter** 

  * **`int $fd`**
    * **Fungsi**: `ID` koneksi klien [jika `$fd` yang ditentukan bukan klien `WebSocket`, pengiriman akan gagal]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`Swoole\WebSocket\Frame|string $data`**
    * **Fungsi**: Data yang akan dikirim
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  !> Swoole versi >= v4.2.0, jika `$data` yang dimasukkan adalah objek [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), parameter selanjutnya akan diabaikan

  * **`int $opcode`**
    * **Fungsi**: Menentukan format data yang dikirim [default teks. Untuk mengirim konten biner, parameter `$opcode` perlu diatur ke `WEBSOCKET_OPCODE_BINARY`]
    * **Default**: `WEBSOCKET_OPCODE_TEXT`
    * **Nilai Lain**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**
    * **Fungsi**: Apakah pengiriman selesai
    * **Default**: `true`
    * **Nilai Lain**: `false`

* **Return Value**

  * Operasi berhasil mengembalikan `true`, gagal mengembalikan `false`

!> Sejak versi `v4.4.12`, parameter `finish` (tipe `bool`) diubah menjadi parameter `flags` (tipe `int`) untuk mendukung kompresi `WebSocket`, `finish` sesuai dengan `SWOOLE_WEBSOCKET_FLAG_FIN` nilai `1`, nilai `bool` asli akan dikonversi implisit ke `int`, perubahan ini kompatibel ke bawah tanpa efek. Selain itu, `flag` kompresi adalah `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

!> [Mode BASE](/learn?id=base模式的限制：) tidak mendukung `push` data lintas proses.

### exist

?> **Menentukan apakah klien `WebSocket` ada dan statusnya `Active`.**

!> Sejak `v4.3.0`, `API` ini hanya digunakan untuk menentukan apakah koneksi ada, gunakan `isEstablished` untuk menentukan apakah itu koneksi `WebSocket`

```php
Swoole\WebSocket\Server->exist(int $fd): bool
```

* **Return Value**

  * Koneksi ada dan sudah menyelesaikan jabat tangan `WebSocket`, mengembalikan `true`
  * Koneksi tidak ada atau belum menyelesaikan jabat tangan, mengembalikan `false`

### pack

?> **Mengemas pesan WebSocket.**

```php
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true, bool $mask = false): string

// v4.4.12 diubah menjadi parameter flags
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string

Swoole\WebSocket\Frame::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
```

* **Parameter** 

  * **`Swoole\WebSocket\Frame|string $data $data`**
    * **Fungsi**: Konten pesan
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $opcode`**
    * **Fungsi**: Menentukan format data yang akan dikirim [default teks. Untuk mengirim konten biner, parameter `$opcode` perlu diatur ke `WEBSOCKET_OPCODE_BINARY`]
    * **Default**: `WEBSOCKET_OPCODE_TEXT`
    * **Nilai Lain**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**
    * **Fungsi**: Apakah frame selesai
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

    !> Sejak versi `v4.4.12`, parameter `finish` (tipe `bool`) diubah menjadi parameter `flags` (tipe `int`) untuk mendukung kompresi `WebSocket`, `finish` sesuai dengan `SWOOLE_WEBSOCKET_FLAG_FIN` nilai `1`, nilai `bool` asli akan dikonversi implisit ke `int`, perubahan ini kompatibel ke bawah tanpa efek.

  * **`bool $mask`**
    * **Fungsi**: Apakah mengatur mask [`v4.4.12` telah menghapus parameter ini]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Mengembalikan paket `WebSocket` yang sudah dikemas, dapat dikirim ke lawan melalui [send()](/server/methods?id=send) dari kelas dasar `Swoole\Server`

* **Contoh**

```php
$ws = new Swoole\Server('127.0.0.1', 9501 , SWOOLE_BASE);

$ws->set(array(
    'log_file' => '/dev/null'
));

$ws->on('WorkerStart', function (\Swoole\Server $serv) {
});

$ws->on('receive', function ($serv, $fd, $threadId, $data) {
    $sendData = "HTTP/1.1 101 Switching Protocols\r\n";
    $sendData .= "Upgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: IFpdKwYy9wdo4gTldFLHFh3xQE0=\r\n";
    $sendData .= "Sec-WebSocket-Version: 13\r\nServer: swoole-http-server\r\n\r\n";
    $sendData .= Swoole\WebSocket\Server::pack("hello world\n");
    $serv->send($fd, $sendData);
});

$ws->start();
```

### unpack

?> **Mengurai frame data `WebSocket`.**

```php
Swoole\WebSocket\Server::unpack(string $data): Swoole\WebSocket\Frame|false;
```

* **Parameter** 

  * **`string $data`**
    * **Fungsi**: Konten pesan
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Gagal mengurai mengembalikan `false`, berhasil mengurai mengembalikan objek [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)

### disconnect

?> **Mengirim frame penutupan ke klien `WebSocket` secara aktif dan menutup koneksi tersebut.**

!> Swoole versi >= `v4.0.3` tersedia

```php
Swoole\WebSocket\Server->disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
```

* **Parameter** 

  * **`int $fd`**
    * **Fungsi**: `ID` koneksi klien [jika `$fd` yang ditentukan bukan klien `WebSocket`, pengiriman akan gagal]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $code`**
    * **Fungsi**: Kode status penutupan koneksi [menurut `RFC6455`, untuk kode status penutupan koneksi aplikasi, rentang nilai `1000` atau antara `4000-4999`]
    * **Default**: `SWOOLE_WEBSOCKET_CLOSE_NORMAL`
    * **Nilai Lain**: Tidak ada

  * **`string $reason`**
    * **Fungsi**: Alasan penutupan koneksi [string format `utf-8`, panjang byte tidak melebihi `125`]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Berhasil dikirim mengembalikan `true`, gagal dikirim atau kode status ilegal mengembalikan `false`

### isEstablished

?> **Memeriksa apakah koneksi adalah koneksi klien `WebSocket` yang valid.**

?> Fungsi ini berbeda dengan method `exist`, method `exist` hanya menentukan apakah itu koneksi `TCP`, tidak bisa menentukan apakah itu klien `WebSocket` yang sudah menyelesaikan jabat tangan.

```php
Swoole\WebSocket\Server->isEstablished(int $fd): bool
```

* **Parameter** 

  * **`int $fd`**
    * **Fungsi**: `ID` koneksi klien [jika `$fd` yang ditentukan bukan klien `WebSocket`, pengiriman akan gagal]
    * **Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Jika koneksi valid mengembalikan `true`, jika tidak mengembalikan `false`

## Kelas Frame Data Websocket

### Swoole\WebSocket\Frame

?> Di versi `v4.2.0`, ditambahkan dukungan pengiriman objek [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) dari server dan klien  
Di versi `v4.4.12`, ditambahkan properti `flags` untuk mendukung frame kompresi `WebSocket`, dan menambahkan subclass baru [Swoole\WebSocket\CloseFrame](/websocket_server?id=swoolewebsocketcloseframe)

Objek `frame` biasa memiliki properti berikut:

Konstanta | Penjelasan 
---|---
fd | `Socket id` klien, digunakan saat mengirim data dengan `$server->push`
data | Konten data, bisa teks atau data biner, dapat ditentukan melalui nilai `opcode`
opcode | [Jenis frame data](/websocket_server?id=数据帧类型) `WebSocket`, dapat merujuk ke dokumen standar protokol `WebSocket`
finish | Menunjukkan apakah frame data lengkap, request `WebSocket` dapat dibagi menjadi beberapa frame data untuk dikirim (level bawah sudah mengimplementasikan penggabungan frame data otomatis, jadi tidak perlu khawatir frame data yang diterima tidak lengkap)

Kelas ini memiliki [Swoole\WebSocket\Frame::pack()](/websocket_server?id=pack) dan [Swoole\WebSocket\Frame::unpack()](/websocket_server?id=unpack), untuk mengemas dan membongkar pesan `websocket`, penjelasan parameter sama dengan `Swoole\WebSocket\Server::pack()` dan `Swoole\WebSocket\Server::unpack()`

### Swoole\WebSocket\CloseFrame

Objek `close frame` biasa memiliki properti berikut:

Konstanta | Penjelasan 
---|---
opcode | [Jenis frame data](/websocket_server?id=数据帧类型) `WebSocket`, dapat merujuk ke dokumen standar protokol `WebSocket`
code | [Kode status frame penutupan](/websocket_server?id=WebSocket断开状态码) `WebSocket`, dapat merujuk ke kode error yang ditentukan dalam [protokol websocket](https://developer.mozilla.org/zh-CN/docs/Web/API/CloseEvent)
reason | Alasan penutupan, jika tidak diberikan secara jelas, kosong

Jika server perlu menerima `close frame`, perlu mengaktifkan parameter [open_websocket_close_frame](/websocket_server?id=open_websocket_close_frame) melalui `$server->set`

## Konstanta

### Jenis Frame Data

Konstanta | Nilai | Penjelasan
---|---|---
WEBSOCKET_OPCODE_TEXT | 0x1 | Data karakter teks UTF-8
WEBSOCKET_OPCODE_BINARY | 0x2 | Data biner
WEBSOCKET_OPCODE_CLOSE | 0x8 | Data tipe frame penutupan
WEBSOCKET_OPCODE_PING | 0x9 | Data tipe ping
WEBSOCKET_OPCODE_PONG | 0xa | Data tipe pong

### Status Koneksi

Konstanta | Nilai | Penjelasan
---|---|---
WEBSOCKET_STATUS_CONNECTION | 1 | Koneksi masuk menunggu jabat tangan
WEBSOCKET_STATUS_HANDSHAKE | 2 | Sedang menjabat tangan
WEBSOCKET_STATUS_ACTIVE | 3 | Jabat tangan berhasil, menunggu browser mengirim frame data
WEBSOCKET_STATUS_CLOSING | 4 | Koneksi sedang melakukan jabat tangan penutupan, akan segera ditutup

### Kode Status Frame Penutupan WebSocket

Konstanta | Nilai | Penjelasan
---|---|---
WEBSOCKET_CLOSE_NORMAL | 1000 | Penutupan normal, koneksi sudah menyelesaikan tugas
WEBSOCKET_CLOSE_GOING_AWAY | 1001 | Server memutus koneksi
WEBSOCKET_CLOSE_PROTOCOL_ERROR | 1002 | Error protokol, koneksi terputus
WEBSOCKET_CLOSE_DATA_ERROR | 1003 | Error data, misalnya membutuhkan data teks, tapi menerima data biner
WEBSOCKET_CLOSE_STATUS_ERROR | 1005 | Menunjukkan tidak menerima kode status yang diharapkan
WEBSOCKET_CLOSE_ABNORMAL | 1006 | Tidak mengirim frame penutupan
WEBSOCKET_CLOSE_MESSAGE_ERROR | 1007 | Koneksi terputus karena menerima data format tidak sesuai (seperti data non UTF-8 dalam pesan teks)
WEBSOCKET_CLOSE_POLICY_ERROR | 1008 | Koneksi terputus karena menerima data yang tidak sesuai kesepakatan. Ini adalah kode status umum, untuk skenario yang tidak cocok dengan kode status 1003 dan 1009
WEBSOCKET_CLOSE_MESSAGE_TOO_BIG | 1009 | Koneksi terputus karena menerima frame data terlalu besar
WEBSOCKET_CLOSE_EXTENSION_MISSING | 1010 | Klien mengharapkan server menyetujui satu atau lebih ekstensi, tetapi server tidak memprosesnya, sehingga klien memutus koneksi
WEBSOCKET_CLOSE_SERVER_ERROR | 1011 | Klien karena menghadapi situasi tak terduga yang mencegahnya menyelesaikan request, sehingga server memutus koneksi
WEBSOCKET_CLOSE_TLS | 1015 | Dicadangkan. Menunjukkan koneksi ditutup karena tidak dapat menyelesaikan jabat tangan TLS (misalnya tidak dapat memverifikasi sertifikat server)

## Opsi

?> `Swoole\WebSocket\Server` adalah subclass dari `Server`, dapat menggunakan method [Swoole\WebSocket\Server::set()](/server/methods?id=set) untuk memasukkan opsi konfigurasi, mengatur parameter tertentu.

### websocket_subprotocol

?> **Mengatur sub-protokol `WebSocket`.**

?> Setelah diatur, header `HTTP` respons jabat tangan akan menambahkan `Sec-WebSocket-Protocol: {$websocket_subprotocol}`. Cara penggunaan spesifik silakan merujuk ke dokumen `RFC` terkait protokol `WebSocket`.

```php
$server->set([
    'websocket_subprotocol' => 'chat',
]);
```

### open_websocket_close_frame

?> **Mengaktifkan penerimaan frame penutupan (`opcode` `0x08`) di callback `onMessage` dalam protokol `WebSocket`, default `false`.**

?> Setelah diaktifkan, dapat menerima frame penutupan yang dikirim klien atau server di callback `onMessage` di `Swoole\WebSocket\Server`, pengembang dapat menanganinya sendiri.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_close_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```

### open_websocket_ping_frame

?> **Mengaktifkan penerimaan frame `Ping` (`opcode` `0x09`) di callback `onMessage` dalam protokol `WebSocket`, default `false`.**

?> Setelah diaktifkan, dapat menerima frame `Ping` yang dikirim klien atau server di callback `onMessage` di `Swoole\WebSocket\Server`, pengembang dapat menanganinya sendiri.

!> Swoole versi >= `v4.5.4` tersedia

```php
$server->set([
    'open_websocket_ping_frame' => true,
]);
```

!> Saat nilai `false`, level bawah akan otomatis membalas frame `Pong`, tetapi jika diatur `true` maka pengembang harus membalas frame `Pong` sendiri.

* **Contoh**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_ping_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x09) {
        echo "Ping frame received: Code {$frame->opcode}\n";
        // Balas frame Pong
        $pongFrame = new Swoole\WebSocket\Frame;
        $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
        $server->push($frame->fd, $pongFrame);
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```

### open_websocket_pong_frame

?> **Mengaktifkan penerimaan frame `Pong` (`opcode` `0x0A`) di callback `onMessage` dalam protokol `WebSocket`, default `false`.**

?> Setelah diaktifkan, dapat menerima frame `Pong` yang dikirim klien atau server di callback `onMessage` di `Swoole\WebSocket\Server`, pengembang dapat menanganinya sendiri.

!> Swoole versi >= `v4.5.4` tersedia

```php
$server->set([
    'open_websocket_pong_frame' => true,
]);
```

* **Contoh**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_pong_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0xa) {
        echo "Pong frame received: Code {$frame->opcode}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```

### websocket_compression

?> **Mengaktifkan kompresi data**

?> Jika `true`, frame dapat dikompresi menggunakan `zlib`. Apakah kompresi dapat dilakukan tergantung pada apakah klien dapat menangani kompresi (ditentukan berdasarkan informasi jabat tangan, lihat `RFC-7692`). Perlu digunakan bersama parameter `flags` `SWOOLE_WEBSOCKET_FLAG_COMPRESS` untuk benar-benar mengompresi frame tertentu. Cara penggunaan spesifik [lihat bagian ini](/websocket_server?id=websocket帧压缩-（rfc-7692）)

!> Swoole versi >= `v4.4.12` tersedia

## Lain-lain

!> Kode contoh terkait dapat ditemukan di [WebSocket unit test](https://github.com/swoole/swoole-src/tree/master/tests/swoole_websocket_server)

### Kompresi Frame WebSocket (RFC-7692)

?> Pertama, Anda perlu mengonfigurasi `'websocket_compression' => true` untuk mengaktifkan kompresi (saat jabat tangan `WebSocket` akan bertukar informasi dukungan kompresi dengan lawan), lalu Anda dapat menggunakan `flag SWOOLE_WEBSOCKET_FLAG_COMPRESS` untuk mengompresi frame tertentu.

#### Contoh

* **Server**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->set(['websocket_compression' => true]);
$server->on('message', function (Server $server, Frame $frame) {
    $server->push(
        $frame->fd,
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
    // $server->push($frame->fd, $frame); // Atau server bisa langsung meneruskan objek frame klien apa adanya
});
$server->start();
```

* **Klien**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->set(['websocket_compression' => true]);
    $cli->upgrade('/');
    $cli->push(
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
});
```

### Mengirim Frame Ping

?> Karena WebSocket adalah koneksi panjang, jika tidak ada komunikasi dalam waktu tertentu, koneksi mungkin terputus. Diperlukan mekanisme heartbeat. Protokol WebSocket mencakup dua frame, Ping dan Pong, dapat mengirim frame Ping secara periodik untuk menjaga koneksi tetap aktif.

#### Contoh

* **Server**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->on('message', function (Server $server, Frame $frame) {
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    $server->push($frame->fd, $pingFrame);
});
$server->start();
```

* **Klien**

```php
use Swoole\WebSocket\Frame;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->upgrade('/');
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    // Kirim PING
    $cli->push($pingFrame);
    
    // Terima PONG
    $pongFrame = $cli->recv();
    var_dump($pongFrame->opcode === WEBSOCKET_OPCODE_PONG);
});
```
