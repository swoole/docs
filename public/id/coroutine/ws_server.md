# Server WebSocket

?> Implementasi server WebSocket yang sepenuhnya coroutine, mewarisi dari [Coroutine\Http\Server](/coroutine/http_server), infrastruktur menyediakan dukungan untuk protokol `WebSocket`. Tidak diulangi di sini, hanya perbedaannya.

!> Bagian ini tersedia setelah v4.4.13.

## Contoh Lengkap

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    $ws->close();
                    break;
                }
                $ws->push("Hello {$frame->data}!");
                $ws->push("How are you, {$frame->data}?");
            }
        }
    });

    $server->handle('/', function (Request $request, Response $response) {
        $response->end(<<<HTML
    <h1>Swoole WebSocket Server</h1>
    <script>
var wsServer = 'ws://127.0.0.1:9502/websocket';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("Connected to WebSocket server.");
    websocket.send('hello');
};

websocket.onclose = function (evt) {
    console.log("Disconnected");
};

websocket.onmessage = function (evt) {
    console.log('Retrieved data from server: ' + evt.data);
};

websocket.onerror = function (evt, e) {
    console.log('Error occured: ' + evt.data);
};
</script>
HTML
        );
    });

    $server->start();
});
```

### Contoh Kirim Massal

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        global $wsObjects;
        $objectId = spl_object_id($ws);
        $wsObjects[$objectId] = $ws;
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                unset($wsObjects[$objectId]);
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    unset($wsObjects[$objectId]);
                    $ws->close();
                    break;
                }
                foreach ($wsObjects as $obj) {
                    $obj->push("Server: {$frame->data}");
                }
            }
        }
    });
    $server->start();
});
```

## Alur Pemrosesan

* `$ws->upgrade()`: Mengirim pesan handshake `WebSocket` ke klien
* `while(true)` loop untuk memproses penerimaan dan pengiriman pesan
* `$ws->recv()` Menerima frame pesan `WebSocket`
* `$ws->push()` Mengirim data frame ke lawan bicara
* `$ws->close()` Menutup koneksi

!> `$ws` adalah objek `Swoole\Http\Response`, cara penggunaan masing-masing method lihat di bawah.

## Method

### upgrade()

Mengirim informasi keberhasilan handshake `WebSocket`.

!> Method ini jangan digunakan di server [gaya asinkron](/http_server).

```php
Swoole\Http\Response->upgrade(): bool
```

### recv()

Menerima pesan `WebSocket`.

!> Method ini jangan digunakan di server [gaya asinkron](/http_server). Saat memanggil method `recv`, coroutine saat ini akan [ditangguhkan](/coroutine?id=penjadwalan-coroutine), menunggu data datang lalu melanjutkan eksekusi coroutine.

```php
Swoole\Http\Response->recv(float $timeout = 0): Swoole\WebSocket\Frame | false | string
```

* **Return Value**

  * Berhasil menerima pesan, mengembalikan objek `Swoole\WebSocket\Frame`, lihat [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)
  * Mengembalikan `false`, gunakan [swoole_last_error()](/functions?id=swoole_last_error) untuk kode error
  * Koneksi ditutup mengembalikan string kosong
  * Cara menangani nilai kembali lihat [contoh kirim massal](/coroutine/ws_server?id=contoh-kirim-massal)

### push()

Mengirim data frame `WebSocket`.

!> Method ini jangan digunakan di server [gaya asinkron](/http_server). Saat mengirim paket data besar, perlu memantau kemampuan tulis, sehingga dapat menyebabkan banyak [perpindahan coroutine](/coroutine?id=penjadwalan-coroutine).

```php
Swoole\Http\Response->push(string|object $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

* **Parameter** 

  !> Jika `$data` yang dimasukkan adalah objek [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), parameter selanjutnya akan diabaikan, mendukung pengiriman berbagai tipe frame.

  * **`string|object $data`**

    * **Fungsi**: Konten yang akan dikirim
    * **Bawaan**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $opcode`**

    * **Fungsi**: Menentukan format data yang akan dikirim [bawaan teks]. Untuk mengirim konten biner, parameter `$opcode` perlu diatur ke `WEBSOCKET_OPCODE_BINARY`.
    * **Bawaan**: `WEBSOCKET_OPCODE_TEXT`
    * **Nilai Lain**: `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Fungsi**: Apakah pengiriman selesai
    * **Bawaan**: `true`
    * **Nilai Lain**: `false`

### ping()
Mengirim heartbeat `WebSocket`.

```php
Swoole\Http\Response->ping(string $data = ''): bool
```
* **Parameter**

  * **`string $data`**
    * **Fungsi**: Data heartbeat
    * **Bawaan**: String kosong
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Mengembalikan `true` jika berhasil
  * Koneksi tidak ada, sudah ditutup, atau belum menyelesaikan `WebSocket`, gagal mengirim mengembalikan `false`

### disconnect()
Memutus koneksi `WebSocket`.

```php
Swoole\Http\Response->disconnect(int $code = 1000, string $reason = ''): bool
```
* **Parameter**

  * **`int $code`**
    * **Fungsi**: Kode penutupan
    * **Bawaan**: `1000`
    * **Nilai Lain**: Tidak ada

  * **`string $reason`**
    * **Fungsi**: Alasan penutupan
    * **Bawaan**: String kosong
    * **Nilai Lain**: Tidak ada

* **Return Value**

  * Mengembalikan `true` jika berhasil
  * Koneksi tidak ada, sudah ditutup, atau belum menyelesaikan `WebSocket`, gagal mengembalikan `false`

### close()

Menutup koneksi `WebSocket`.

!> Method ini jangan digunakan di server [gaya asinkron](/http_server). Di versi sebelum v4.4.15, akan memunculkan `Warning` yang bisa diabaikan.

```php
Swoole\Http\Response->close(): bool
```

Method ini akan memutus koneksi `TCP` secara langsung, tidak mengirim frame `Close`, berbeda dengan method `WebSocket\Server::disconnect()`.
Bisa menggunakan method `push()` untuk mengirim frame `Close` sebelum menutup koneksi, untuk memberi tahu klien secara aktif.

```php
$frame = new Swoole\WebSocket\CloseFrame;
$frame->reason = 'close';
$ws->push($frame);
$ws->close();
```
