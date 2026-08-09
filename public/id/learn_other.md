# Pengetahuan Lainnya

## Mengatur Timeout dan Retry DNS Resolution

Dalam pemrograman jaringan, `gethostbyname` dan `getaddrinfo` sering digunakan untuk resolusi domain. Dua fungsi `C` ini tidak menyediakan parameter timeout. Sebenarnya, Anda bisa mengubah `/etc/resolv.conf` untuk mengatur timeout dan logika retry.

!> Lihat dokumentasi `man resolv.conf`

### Multiple NameServer <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

Anda bisa mengonfigurasi beberapa `nameserver`. Sistem akan melakukan polling secara otomatis—jika query ke `nameserver` pertama gagal, akan beralih ke `nameserver` kedua untuk retry.

Konfigurasi `option rotate` berfungsi untuk melakukan load balancing `nameserver` menggunakan mode round-robin.

### Timeout Control <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

* `timeout`: Mengontrol timeout penerimaan `UDP`, dalam satuan detik, default `5` detik
* `attempts`: Mengontrol jumlah percobaan. Jika dikonfigurasi `2`, berarti maksimal `2` kali percobaan, default `5` kali

Misalkan ada `2` `nameserver`, `attempts` `2`, timeout `1` detik. Jika semua server DNS tidak merespons, waktu tunggu maksimal adalah `4` detik (`2x2x1`).

### Call Trace <!-- {docsify-ignore} -->

Gunakan [strace](/other/tools?id=strace) untuk melacak panggilan.

Atur `nameserver` ke dua `IP` yang tidak ada, lalu jalankan kode `PHP`: `var_dump(gethostbyname('www.baidu.com'));` untuk resolusi domain.

```
socket(AF_INET, SOCK_DGRAM|SOCK_CLOEXEC|SOCK_NONBLOCK, IPPROTO_IP) = 3
connect(3, {sa_family=AF_INET, sin_port=htons(53), sin_addr=inet_addr("10.20.128.16")}, 16) = 0
poll([{fd=3, events=POLLOUT}], 1, 0)    = 1 ([{fd=3, revents=POLLOUT}])
sendto(3, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=3, events=POLLIN}], 1, 1000

)  = 0 (Timeout)
socket(AF_INET, SOCK_DGRAM|SOCK_CLOEXEC|SOCK_NONBLOCK, IPPROTO_IP) = 4
connect(4, {sa_family=AF_INET, sin_port=htons(53), sin_addr=inet_addr("10.20.128.18")}, 16) = 0
poll([{fd=4, events=POLLOUT}], 1, 0)    = 1 ([{fd=4, revents=POLLOUT}])
sendto(4, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=4, events=POLLIN}], 1, 1000


)  = 0 (Timeout)
poll([{fd=3, events=POLLOUT}], 1, 0)    = 1 ([{fd=3, revents=POLLOUT}])
sendto(3, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=3, events=POLLIN}], 1, 1000


)  = 0 (Timeout)
poll([{fd=4, events=POLLOUT}], 1, 0)    = 1 ([{fd=4, revents=POLLOUT}])
sendto(4, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=4, events=POLLIN}], 1, 1000



)  = 0 (Timeout)
close(3)                                = 0
close(4)                                = 0
```

Terlihat di sini total ada `4` kali percobaan ulang, dengan timeout panggilan `poll` diatur ke `1000ms` (`1 detik`).
