# Penyesuaian Parameter Kernel

## Pengaturan ulimit

`ulimit -n` harus disesuaikan menjadi 100000 atau lebih besar. Jalankan `ulimit -n 100000` di command line untuk mengubahnya. Jika tidak bisa diubah, perlu mengatur `/etc/security/limits.conf`:

```
* soft nofile 262140
* hard nofile 262140
root soft nofile 262140
root hard nofile 262140
* soft core unlimited
* hard core unlimited
root soft core unlimited
root hard core unlimited
```

Perhatikan, setelah mengubah file `limits.conf`, perlu restart sistem untuk berlaku.

## Pengaturan Kernel

Ada 3 cara mengubah parameter kernel di `Linux`:

- Ubah file `/etc/sysctl.conf`, tambahkan opsi konfigurasi dengan format `key = value`, simpan lalu jalankan `sysctl -p` untuk memuat konfigurasi baru
- Gunakan perintah `sysctl` untuk perubahan sementara, misalnya: `sysctl -w net.ipv4.tcp_mem="379008 505344 758016"`
- Langsung ubah file di direktori `/proc/sys/`, misalnya: `echo "379008 505344 758016" > /proc/sys/net/ipv4/tcp_mem`

> Cara pertama akan otomatis berlaku setelah restart OS, cara kedua dan ketiga akan hilang setelah restart

### net.unix.max_dgram_qlen = 100

swoole menggunakan unix socket dgram untuk komunikasi antar proses. Jika volume request besar, perlu menyesuaikan parameter ini. Default sistem adalah 10, bisa diatur ke 100 atau lebih besar. Atau menambah jumlah worker process untuk mengurangi beban request per worker.

### net.core.wmem_max

Ubah parameter ini untuk menambah ukuran memori buffer socket.

```
net.ipv4.tcp_mem  =   379008       505344  758016
net.ipv4.tcp_wmem = 4096        16384   4194304
net.ipv4.tcp_rmem = 4096          87380   4194304
net.core.wmem_default = 8388608
net.core.rmem_default = 8388608
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
```

### net.ipv4.tcp_tw_reuse

Apakah socket reuse diaktifkan. Fungsinya agar Server bisa cepat menggunakan kembali port yang didengarkan saat restart. Tanpa parameter ini, server bisa gagal start karena port belum dilepaskan tepat waktu.

### net.ipv4.tcp_tw_recycle

Mengaktifkan daur ulang socket cepat. Server koneksi pendek perlu mengaktifkan parameter ini. Parameter ini mengaktifkan daur ulang cepat socket TIME-WAIT di koneksi TCP. Default Linux adalah 0 (mati). Mengaktifkan ini bisa menyebabkan koneksi NAT tidak stabil, harap uji dengan hati-hati.

## Pengaturan Message Queue

Saat menggunakan message queue sebagai komunikasi antar proses, perlu menyesuaikan parameter kernel ini:

- kernel.msgmnb = 4203520, ukuran maksimum message queue (bytes)
- kernel.msgmni = 64, jumlah maksimum message queue yang bisa dibuat
- kernel.msgmax = 8192, panjang maksimum satu data message queue

## FreeBSD/MacOS

- sysctl -w net.local.dgram.maxdgram=8192
- sysctl -w net.local.dgram.recvspace=200000
  Ubah ukuran buffer Unix Socket

## Mengaktifkan CoreDump

Atur parameter kernel:

```
kernel.core_pattern = /data/core_files/core-%e-%p-%t
```

Periksa batas file coredump saat ini dengan perintah `ulimit -c`:

```shell
ulimit -c
```

Jika 0, perlu mengubah `/etc/security/limits.conf` untuk pengaturan limit.

> Setelah mengaktifkan core-dump, saat program mengalami abnormal, proses akan diekspor ke file. Sangat membantu untuk menyelidiki masalah program.

## Konfigurasi Penting Lainnya

- net.ipv4.tcp_syncookies=1
- net.ipv4.tcp_max_syn_backlog=81920
- net.ipv4.tcp_synack_retries=3
- net.ipv4.tcp_syn_retries=3
- net.ipv4.tcp_fin_timeout = 30
- net.ipv4.tcp_keepalive_time = 300
- net.ipv4.tcp_tw_reuse = 1
- net.ipv4.tcp_tw_recycle = 1
- net.ipv4.ip_local_port_range = 20000 65000
- net.ipv4.tcp_max_tw_buckets = 200000
- net.ipv4.route.max_size = 5242880

## Memeriksa Apakah Konfigurasi Berlaku

Misalnya, setelah mengubah `net.unix.max_dgram_qlen = 100`, periksa dengan:

```shell
cat /proc/sys/net/unix/max_dgram_qlen
```

Jika berhasil, akan menampilkan nilai yang baru.
