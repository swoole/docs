# IO-Uring

`iouring` adalah mekanisme panggilan `IO` konkuren baru yang disediakan oleh kernel `linux`. Berbeda dengan mekanisme multipleks `epoll`, `iouring` mendukung `IO` disk dan `IO` jaringan secara bersamaan.

Perlu dicatat bahwa `io_uring != epoll`. Ini adalah dua konsep yang sama sekali berbeda. `iouring` bukan mekanisme event polling, melainkan implementasi panggilan sistem asinkron. Dalam implementasi infrastruktur `Swoole`,
`iouring` memiliki ketergantungan kuat dengan coroutine, hanya tersedia di coroutine `Swoole`. `Swoole` menggunakan `iouring` untuk mengimplementasikan panggilan sistem di ruang pengguna, dipadukan dengan model thread ruang pengguna (coroutine),
menggantikan model `reactor/proactor` tradisional berbasis `epoll/kqueue`, mewujudkan arsitektur konkuren efisien generasi baru.

## `IO` Blocking Sinkron

Alur panggilan `IO` klasik adalah sebagai berikut:

```c
int sock = socket(AF_INET, SOCK_STREAM, 0);
struct sockaddr_in addr;
addr.sin_family = AF_INET;
addr.sin_port = htons(80);
addr.sin_addr.s_addr = inet_addr(dns_lookup("www.qq.com"));
connect(sock, (struct sockaddr *)&addr, sizeof(addr));
send(sock, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n", 49, 0);
recv(sock, buf, sizeof(buf), 0);
close(sock);
```

`socket`, `connect`, `send`, `recv`, `close` adalah fungsi pustaka standar `POSIX` bahasa `C`, yang bersifat blocking sinkron, akan memblokir thread saat ini sampai panggilan berhasil.
Karena itu perlu dikombinasikan dengan multi-thread atau multi-proses untuk mencapai konkurensi.

## `epoll` `IO` Non-Blocking Asinkron

Alur panggilan `IO` non-blocking asinkron berbasis `epoll` adalah sebagai berikut:
```cpp
auto epoll = new EpollReactor();
set_nonblocking(sock);
connect(sock, (struct sockaddr *)&addr, sizeof(addr));
epoll->add(sock, EPOLLOUT, []() {
    getsockopt(sock, SOL_SOCKET, SO_ERROR, &err, sizeof(err));
    if (err != 0) {
        // Koneksi gagal
        return;
    }
    send(sock, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n", 49, 0);
    epoll->add(sock, EPOLLIN, []() {
        // Menerima data respons
        recv(sock, buf, sizeof(buf), 0);
        close(sock);
    });
});
```

Perlu mendaftarkan listener event `EPOLLIN/EPOLLOUT`, mengatur `socket` ke mode non-blocking, lalu memproses event `IO` di callback event `epoll`.

## `iouring`

```c
Coroutine::create([&]() { 
  Iouring::connect(sock, (struct sockaddr *)&addr, sizeof(addr));
  Iouring::send(sock, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n", 49, 0);
  Iouring::recv(sock, buf, sizeof(buf), 0);
  Iouring::close(sock);
});
```

Menggunakan `iouring` untuk melakukan panggilan sistem seperti `connect`, `send`, `recv`, `close`. Panggilan sistem tidak lagi sinkron, melainkan asinkron, terdiri dari dua langkah:
1. Mengirim permintaan panggilan sistem ke antrean `SQE`, menangguhkan coroutine saat ini;
2. Menunggu sistem operasi menyelesaikan panggilan sistem, memanen antrean `CQE`, mendapatkan notifikasi `ioring`, membangunkan coroutine yang ditangguhkan, melanjutkan eksekusi.

Program model `IO` non-blocking asinkron tradisional `epoll` harus sering berpindah antara ruang pengguna dan ruang kernel.
Sedangkan pengiriman dan pemanenan panggilan sistem `iouring` dilakukan secara batch. Perilaku panggilan sistem akan berubah menjadi mode asinkron, tidak perlu berpindah ke ruang kernel, sepenuhnya berjalan di ruang pengguna. Kinerja konkurensi meningkat secara signifikan.
`iouring` mendukung banyak panggilan sistem, termasuk berikut ini:

| Kategori | Panggilan Sistem | Deskripsi |
|------|---|--------|
| Baca/Tulis File | open, read, write, close | Operasi sistem file |
| Jaringan | socket, connect, accept, send, recv, sendmsg, recvmsg, sendto, recvfrom, shutdown, close | Operasi jaringan |
| Mutex | futex_wait, futex_wakeup | Operasi kunci |
| Sistem File | rename, fstat, mkdir, unlink, rmdir, fsync, rmdir, fsync, fdatasync, ftruncate | Operasi sistem file |
| Proses | sleep, wait, waitpid | Operasi proses |

## Enkapsulasi `Iouring`

Kelas `Iouring` `C++` mengenkapsulasi panggilan sistem `iouring`. Parameter dan nilai kembali fungsi-fungsi ini sama dengan fungsi pustaka standar `POSIX` `glibc`. Di dalam coroutine `Swoole`,
fungsi-fungsi ini bisa dipanggil langsung, tanpa perlu berpindah ke ruang kernel.

```cpp
using namespace swoole;
Coroutine::create([&]() {
  // Panggilan sistem Swoole Iouring, hanya menangguhkan coroutine saat ini, coroutine lain tetap bisa berjalan
  Iouring::sleep(1);
  // Panggilan sistem POSIX, akan berpindah ke ruang kernel, memblokir thread/proses saat ini, coroutine lain tidak bisa berjalan
  sleep(1);
});
```

Melihat kode sumber kelas ini, bisa memahami detail implementasi panggilan sistem `iouring`.
- `Iouring::dispatch()`: Mengirim permintaan panggilan sistem ke antrean `SQE`
- `Iouring::execute()`: Mengirim permintaan panggilan sistem ke antrean `SQE`, dan menangguhkan coroutine saat ini
- `Iouring::wakeup()`: Memanen antrean `CQE`, mendapatkan notifikasi `ioring`, membangunkan coroutine yang ditangguhkan

### Mekanisme Timeout
`socket` blocking sinkron biasanya perlu memanggil `setsockopt` untuk mengatur waktu timeout, contoh:

```cpp
int timeout = 1000;
setsockopt(sock, SOL_SOCKET, SO_RCVTIMEO, &timeout, sizeof(timeout));
```

Model `IO` non-blocking asinkron `epoll` menggunakan timer untuk mengimplementasikan timeout. Sedangkan `iouring` memiliki mekanisme timeout bawaan, tanpa perlu timer tambahan atau pengaturan parameter `socket`.
```cpp
ssize_t Iouring::recv(int fd, void *buf, size_t len, int flags, double timeout) {
    INIT_EVENT(IORING_OP_RECV);
    io_uring_prep_recv(&event.data, fd, buf, len, flags);
    event.set_timeout(timeout);
    return execute(&event);
}

void Iouring::dispatch(IouringEvent *event) {
    // ...
    io_uring_sqe *sqe = alloc_sqe();
    memcpy(sqe, &event->data, sizeof(event->data));
    io_uring_sqe_set_data(sqe, (void *) event);

    auto timeout_sqe = alloc_sqe();
    memset(timeout_sqe, 0, sizeof(*timeout_sqe));
    io_uring_prep_link_timeout(timeout_sqe, reinterpret_cast<__kernel_timespec *>(&event->timeout), 0);
    io_uring_sqe_set_data(timeout_sqe, reinterpret_cast<void *>(TIMEOUT_EVENT));
    sqe->flags |= IOSQE_IO_LINK;
    
    // ...
}
```

Di `Facde API` `Iouring`, parameter terakhir dari banyak interface adalah waktu timeout. Contoh: `Iouring::recv()`.
```cpp
ssize_t Iouring::recv(int fd, void *buf, size_t len, int flags, double timeout = -1);
```
Tipe parameter timeout adalah float, satuannya detik. Nilai bawaan `-1`, berarti tidak mengatur timeout. Granularitas minimum timeout `iouring` adalah nanodetik. Ini lebih presisi daripada timer `epoll`.

## Enkapsulasi `UringSocket`

`UringSocket` mengenkapsulasi panggilan sistem `iouring`, sebagai subclass dari `Swoole\Coroutine\Socket`, menggantikan method `connect`, `send`, `recv`, `close`,
Fungsi `IO` jaringan yang sebelumnya diimplementasikan dengan `epoll` IO non-blocking asinkron, sekarang akan menggunakan `iouring`. Contoh: method `read()` akan menggunakan `Iouring::read()`.

```cpp
ssize_t UringSocket::read(void *_buf, size_t _n) {
    if (sw_unlikely(!is_available(SW_EVENT_READ))) {
        return -1;
    }
    read_co = Coroutine::get_current_safe();
    ssize_t retval = Iouring::read(socket->get_fd(), _buf, _n, socket->read_timeout);
    read_co = nullptr;
    check_return_value(retval);
    return retval;
}
```

`UringSocket` mendukung `SSL`, sama seperti `Swoole\Coroutine\Socket`. Infrastruktur menggunakan `openssl BIO`.
