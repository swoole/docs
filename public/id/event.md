# Event

Ekstensi `Swoole` juga menyediakan antarmuka untuk mengoperasikan event loop level bawah `epoll/kqueue/poll/select` secara langsung. `Socket` yang dibuat oleh ekstensi lain, `socket` yang dibuat oleh ekstensi `stream/socket` dalam kode `PHP` dll dapat ditambahkan ke [EventLoop](/learn?id=什么是eventloop) Swoole.
Jika tidak, `$fd` pihak ketiga yang merupakan IO sinkron akan menyebabkan EventLoop Swoole tidak dapat dijalankan, [lihat studi kasus](/learn?id=同步io转换成异步io).

!> Modul `Event` cukup level bawah, merupakan enkapsulasi dasar `epoll`. Pengguna disarankan memiliki pengalaman pemrograman IO multiplexing.

## Prioritas Event

1. Fungsi callback penanganan sinyal yang diatur melalui `Process::signal`
2. Fungsi callback timer yang diatur melalui `Timer::tick` dan `Timer::after`
3. Fungsi eksekusi tertunda yang diatur melalui `Event::defer`
4. Fungsi callback periodik yang diatur melalui `Event::cycle`

## Method

### add()

Menambahkan `socket` ke pendengar event `reactor` level bawah. Fungsi ini dapat digunakan di mode `Server` atau `Client`.

```php
Swoole\Event::add(mixed $sock, callable $read_callback, callable $write_callback = null, int $flags = null): bool
```

!> Saat digunakan di program `Server`, harus digunakan setelah proses `Worker` dimulai. Jangan memanggil antarmuka `IO` asinkron sebelum `Server::start`.

* **Parameter** 

  * **`mixed $sock`**
    * **Fungsi**: Deskriptor file, resource `stream`, resource `sockets`, `object`
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`callable $read_callback`**
    * **Fungsi**: Fungsi callback event dapat dibaca
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`callable $write_callback`**
    * **Fungsi**: Fungsi callback event dapat ditulis [parameter ini bisa berupa string nama fungsi, objek+method, method statis kelas, atau fungsi anonim. Saat `socket` ini dapat dibaca atau ditulis, fungsi yang ditentukan akan dipanggil.]
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $flags`**
    * **Fungsi**: Masker jenis event
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: `SWOOLE_EVENT_READ` mendengarkan event dapat dibaca, `SWOOLE_EVENT_WRITE` mendengarkan event dapat ditulis, `SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE` mendengarkan event dapat dibaca dan ditulis secara bersamaan

* **$sock 4 jenis**

Jenis | Penjelasan
---|---
int | Deskriptor file, termasuk `Swoole\Client->$sock`, `Swoole\Process->$pipe` atau `fd` lainnya
resource stream | Resource yang dibuat oleh `stream_socket_client`/`fsockopen`
resource sockets | Resource yang dibuat oleh `socket_create` di ekstensi `sockets`, perlu menambahkan [./configure --enable-sockets](/environment?id=编译选项) saat kompilasi
object | `Swoole\Process` atau `Swoole\Client`, level bawah otomatis dikonversi menjadi [UnixSocket](/learn?id=什么是IPC) (`Process`) atau `socket` koneksi klien (`Swoole\Client`)

* **Nilai Kembali**

  * Berhasil menambahkan pendengar event mengembalikan `true`
  * Gagal menambahkan mengembalikan `false`, gunakan `swoole_last_error` untuk mendapatkan kode error
  * `socket` yang sudah ditambahkan tidak bisa ditambahkan lagi. Menggunakan `swoole_event_set` dapat mengubah fungsi callback dan jenis event dari `socket`

  !> Saat menggunakan `Swoole\Event::add` untuk menambahkan `socket` ke pendengar event, level bawah akan otomatis mengatur `socket` tersebut ke mode non-blocking

* **Contoh Penggunaan**

```php
$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Swoole\Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    //Setelah pemrosesan socket selesai, hapus socket dari event epoll
    Swoole\Event::del($fp);
    fclose($fp);
});
echo "Finish\n";  //Swoole\Event::add tidak memblokir proses, baris ini akan dijalankan secara berurutan
```

* **Fungsi Callback**

  * Di fungsi callback event dapat dibaca `($read_callback)` harus menggunakan `fread`, `recv` dll untuk membaca data dari buffer `socket`, jika tidak event akan terus terpicu. Jika tidak ingin melanjutkan membaca, harus menggunakan `Swoole\Event::del` untuk menghapus pendengar event
  * Di fungsi callback event dapat ditulis `($write_callback)`, setelah menulis ke `socket` harus memanggil `Swoole\Event::del` untuk menghapus pendengar event, jika tidak event dapat ditulis akan terus terpicu
  * Jika `fread`, `socket_recv`, `socket_read`, `Swoole\Client::recv` mengembalikan `false` dan kode error `EAGAIN`, berarti buffer penerima `socket` saat ini tidak memiliki data. Maka perlu menambahkan pendengar dapat dibaca dan menunggu pemberitahuan [EventLoop](/learn?id=什么是eventloop)
  * Jika `fwrite`, `socket_write`, `socket_send`, `Swoole\Client::send` mengembalikan `false` dan kode error `EAGAIN`, berarti buffer pengirim `socket` sudah penuh, tidak bisa mengirim data sementara. Perlu mendengarkan event dapat ditulis dan menunggu pemberitahuan [EventLoop](/learn?id=什么是eventloop)

### set()

Mengubah fungsi callback dan masker pendengar event.

```php
Swoole\Event::set($fd, mixed $read_callback, mixed $write_callback, int $flags): bool
```

* **Parameter** 

  * Parameter sama persis dengan [Event::add](/event?id=add). Jika `$fd` yang dimasukkan tidak ada di [EventLoop](/learn?id=什么是eventloop), mengembalikan `false`.
  * Saat `$read_callback` tidak `null`, akan mengubah fungsi callback event dapat dibaca menjadi fungsi yang ditentukan.
  * Saat `$write_callback` tidak `null`, akan mengubah fungsi callback event dapat ditulis menjadi fungsi yang ditentukan.
  * Jika `$flags` adalah `SWOOLE_EVENT_READ`, berarti hanya mendengarkan event dapat dibaca, berhenti mendengarkan event dapat ditulis.
  * Jika `$flags` adalah `SWOOLE_EVENT_WRITE`, berarti hanya mendengarkan event dapat ditulis, berhenti mendengarkan event dapat dibaca.

  !> Perhatikan jika mendengarkan event `SWOOLE_EVENT_READ`, tetapi saat ini tidak mengatur `read_callback`, level bawah akan langsung mengembalikan `false`, penambahan gagal. `SWOOLE_EVENT_WRITE` juga sama.

* **Perubahan Status**

  * Jika menggunakan `Event::add` atau `Event::set` mengatur callback event dapat dibaca, tetapi tidak mendengarkan event dapat dibaca `SWOOLE_EVENT_READ`, level bawah hanya menyimpan informasi fungsi callback, tidak menghasilkan event callback apa pun.
  * Dapat menggunakan `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`, mengubah jenis event yang didengarkan, level bawah akan memicu event dapat dibaca.

* **Melepaskan Fungsi Callback**

!> Perhatikan `Event::set` hanya bisa mengganti fungsi callback, tetapi tidak bisa melepaskan fungsi callback event. Contoh: `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`, `read_callback` dan `write_callback` yang dimasukkan adalah `null`, berarti tidak mengubah fungsi callback yang diatur oleh `Event::add`, bukan mengatur fungsi callback event menjadi `null`.

Hanya saat memanggil `Event::del` untuk membersihkan pendengar event, level bawah akan melepaskan fungsi callback event `read_callback` dan `write_callback`.

### isset()

Memeriksa apakah `$fd` yang dimasukkan sudah ditambahkan ke pendengar event.

```php
Swoole\Event::isset(mixed $fd, int $events = SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE): bool
```

* **Parameter** 

  * **`mixed $fd`**
    * **Fungsi**: Deskriptor file socket apa pun [lihat dokumentasi [Event::add](/event?id=add)]
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`int $events`**
    * **Fungsi**: Jenis event yang diperiksa
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **$events**

Jenis Event | Penjelasan
---|---
`SWOOLE_EVENT_READ` | Apakah mendengarkan event dapat dibaca
`SWOOLE_EVENT_WRITE` | Apakah mendengarkan event dapat ditulis
`SWOOLE_EVENT_READ \| SWOOLE_EVENT_WRITE` | Mendengarkan event dapat dibaca atau ditulis

* **Contoh Penggunaan**

```php
use Swoole\Event;

$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    Swoole\Event::del($fp);
    fclose($fp);
}, null, SWOOLE_EVENT_READ);
var_dump(Event::isset($fp, SWOOLE_EVENT_READ)); //mengembalikan true
var_dump(Event::isset($fp, SWOOLE_EVENT_WRITE)); //mengembalikan false
var_dump(Event::isset($fp, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)); //mengembalikan true
```

### write()

Digunakan untuk socket yang dibuat dengan ekstensi `stream/sockets` bawaan PHP, menggunakan fungsi `fwrite/socket_send` dll untuk mengirim data ke lawan. Saat jumlah data yang dikirim besar dan buffer tulis socket penuh, akan terjadi blocking atau mengembalikan error [EAGAIN](/other/errno?id=linux).

Fungsi `Event::write` dapat membuat pengiriman data resource `stream/sockets` menjadi **asinkron**. Saat buffer penuh atau mengembalikan [EAGAIN](/other/errno?id=linux), level bawah Swoole akan menambahkan data ke antrian pengiriman, dan mendengarkan dapat ditulis. Saat socket dapat ditulis, level bawah Swoole akan otomatis menulis

```php
Swoole\Event::write(mixed $fd, miexd $data): bool
```

* **Parameter** 

  * **`mixed $fd`**
    * **Fungsi**: Deskriptor file socket apa pun [lihat dokumentasi [Event::add](/event?id=add)]
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`miexd $data`**
    * **Fungsi**: Data yang akan dikirim [panjang data yang dikirim tidak boleh melebihi ukuran buffer `Socket`]
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

!> `Event::write` tidak dapat digunakan untuk resource `stream/sockets` dengan enkripsi tunnel seperti `SSL/TLS`  
Setelah operasi `Event::write` berhasil, `$socket` akan otomatis diatur ke mode non-blocking

* **Contoh Penggunaan**

```php
use Swoole\Event;

$fp = stream_socket_client('tcp://127.0.0.1:9501');
$data = str_repeat('A', 1024 * 1024*2);

Event::add($fp, function($fp) {
     echo fread($fp);
});

Event::write($fp, $data);
```

#### Logika level bawah Swoole setelah buffer socket penuh

Jika terus menulis ke `socket` dan lawan tidak membaca cukup cepat, buffer `socket` akan penuh. Level bawah `Swoole` akan menyimpan data di buffer memori, sampai event dapat ditulis terpicu lalu menulis ke `socket`.

Jika buffer memori juga penuh, level bawah `Swoole` akan melemparkan error `pipe buffer overflow, reactor will block.` dan masuk ke blocking.

!> Mengembalikan `false` saat buffer penuh adalah operasi atom, hanya akan terjadi semua berhasil atau semua gagal

### del()

Menghapus `socket` yang didengarkan dari `reactor`. `Event::del` harus digunakan berpasangan dengan `Event::add`.

```php
Swoole\Event::del(mixed $sock): bool
```

!> Harus menggunakan `Event::del` untuk menghapus pendengar event sebelum operasi `close` pada `socket`, jika tidak dapat menyebabkan kebocoran memori

* **Parameter** 

  * **`mixed $sock`**
    * **Fungsi**: Deskriptor file `socket`
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

### exit()

Keluar dari event loop.

!> Fungsi ini hanya valid di program `Client`

```php
Swoole\Event::exit(): void
```

### defer()

Menjalankan fungsi di awal event loop berikutnya.

```php
Swoole\Event::defer(mixed $callback_function);
```

!> Fungsi callback `Event::defer` akan dijalankan setelah event loop `EventLoop` saat ini selesai, sebelum event loop berikutnya dimulai.

* **Parameter** 

  * **`mixed $callback_function`**
    * **Fungsi**: Fungsi yang akan dijalankan saat waktu habis [harus dapat dipanggil. Fungsi callback tidak menerima parameter apa pun. Dapat menggunakan sintaks `use` fungsi anonim untuk meneruskan parameter ke fungsi callback; selama eksekusi fungsi `$callback_function`, menambahkan tugas `defer` baru tetap akan selesai dalam event loop saat ini]
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

* **Contoh Penggunaan**

```php
Swoole\Event::defer(function(){
    echo "After EventLoop\n";
});
```

### cycle()

Mendefinisikan fungsi eksekusi periodik event loop. Fungsi ini akan dipanggil di akhir setiap event loop.

```php
Swoole\Event::cycle(callable $callback, bool $before = false): bool
```

* **Parameter** 

  * **`callable $callback_function`**
    * **Fungsi**: Fungsi callback yang akan diatur [`$callback` menjadi `null` berarti menghapus fungsi `cycle`, jika sudah ada fungsi cycle, mengatur ulang akan menimpa pengaturan sebelumnya]
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

  * **`bool $before`**
    * **Fungsi**: Memanggil fungsi ini sebelum [EventLoop](/learn?id=什么是eventloop)
    * **Nilai Default**: Tidak ada
    * **Nilai Lain**: Tidak ada

!> Dapat memiliki dua fungsi callback `before=true` dan `before=false` secara bersamaan.

* **Contoh Penggunaan**

```php
Swoole\Timer::tick(2000, function ($id) {
    var_dump($id);
});

Swoole\Event::cycle(function () {
    echo "hello [1]\n";
    Swoole\Event::cycle(function () {
        echo "hello [2]\n";
        Swoole\Event::cycle(null);
    });
});
```

### wait()

Memulai pendengar event.

!> Letakkan fungsi ini di akhir program PHP

```php
Swoole\Event::wait();
```

* **Contoh Penggunaan**

```php
Swoole\Timer::tick(1000, function () {
    echo "hello\n";
});

Swoole\Event::wait();
```

### dispatch()

Memulai pendengar event.

!> Hanya menjalankan operasi `reactor->wait` sekali, setara dengan memanggil `epoll_wait` secara manual di platform `Linux`. Berbeda dengan `Event::dispatch`, `Event::wait` mempertahankan loop di dalam level bawah.

```php
Swoole\Event::dispatch();
```

* **Contoh Penggunaan**

```php
while(true)
{
    Event::dispatch();
}
```

Tujuan fungsi ini adalah untuk kompatibilitas dengan beberapa framework, seperti `amp`, yang mengontrol loop `reactor` sendiri di dalam framework. Dengan menggunakan `Event::wait`, level bawah Swoole mempertahankan kendali, sehingga tidak bisa diserahkan ke framework.
