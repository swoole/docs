# Penggunaan Tools

## yasd

[yasd](https://github.com/swoole/yasd)

Alat debugging step-by-step, bisa digunakan di lingkungan korutin `Swoole`, mendukung mode debugging `IDE` dan command line.

## tcpdump

tcpdump adalah alat penting saat men-debug program komunikasi jaringan. tcpdump sangat powerful, bisa melihat setiap detail komunikasi jaringan. Misalnya TCP, bisa melihat 3-way handshake, PUSH/ACK data push, close 4-way handshake, semua detailnya. Termasuk jumlah byte setiap paket yang diterima, waktu, dll.

### Cara Penggunaan

Contoh penggunaan paling sederhana:

```shell
sudo tcpdump -i any tcp port 9501
```
* Parameter `-i` menentukan network interface, `any` berarti semua interface
* `tcp` menentukan hanya memonitor protokol TCP
* `port` menentukan port yang dimonitor

!> tcpdump membutuhkan root privilege; untuk melihat konten data komunikasi, bisa tambahkan parameter `-Xnlps0`, parameter lain lihat artikel online

### Hasil

```
13:29:07.788802 IP localhost.42333 > localhost.9501: Flags [S], seq 828582357, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 0,nop,wscale 7], length 0
13:29:07.788815 IP localhost.9501 > localhost.42333: Flags [S.], seq 1242884615, ack 828582358, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 2207513,nop,wscale 7], length 0
13:29:07.788830 IP localhost.42333 > localhost.9501: Flags [.], ack 1, win 342, options [nop,nop,TS val 2207513 ecr 2207513], length 0
13:29:10.298686 IP localhost.42333 > localhost.9501: Flags [P.], seq 1:5, ack 1, win 342, options [nop,nop,TS val 2208141 ecr 2207513], length 4
13:29:10.298708 IP localhost.9501 > localhost.42333: Flags [.], ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:10.298795 IP localhost.9501 > localhost.42333: Flags [P.], seq 1:13, ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 12
13:29:10.298803 IP localhost.42333 > localhost.9501: Flags [.], ack 13, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:11.563361 IP localhost.42333 > localhost.9501: Flags [F.], seq 5, ack 13, win 342, options [nop,nop,TS val 2208457 ecr 2208141], length 0
13:29:11.563450 IP localhost.9501 > localhost.42333: Flags [F.], seq 13, ack 6, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
13:29:11.563473 IP localhost.42333 > localhost.9501: Flags [.], ack 14, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
```
* `13:29:11.563473` Waktu dengan presisi mikrodetik
* localhost.42333 > localhost.9501 menunjukkan arah komunikasi, 42333 adalah client, 9501 adalah server
* [S] menunjukkan ini adalah SYN request
* [.] menunjukkan ini adalah ACK packet, (client)SYN->(server)SYN->(client)ACK adalah proses 3-way handshake
* [P] menunjukkan ini adalah data push, bisa dari server ke client atau client ke server
* [F] menunjukkan ini adalah FIN packet, operasi tutup koneksi, client/server bisa memulai
* [R] menunjukkan ini adalah RST packet, fungsinya sama dengan F packet, tapi RST menandakan saat koneksi ditutup, masih ada data yang belum diproses. Bisa diartikan sebagai paksa putus koneksi
* win 342 adalah ukuran sliding window
* length 12 adalah ukuran paket data

## strace

strace bisa melacak eksekusi system call. Saat program bermasalah, bisa menggunakan strace untuk menganalisis dan melacak masalah.

!> Di FreeBSD/MacOS bisa menggunakan truss

### Cara Penggunaan

```shell
strace -o /tmp/strace.log -f -p $PID
```

* -f berarti melacak multi-thread dan multi-proses, tanpa parameter ini tidak bisa menangkap sub-proses dan sub-thread
* -o berarti output ke file
* -p $PID, menentukan process ID yang dilacak, bisa dilihat melalui ps aux
* -tt mencetak waktu terjadinya system call, presisi mikrodetik
* -s membatasi panjang string yang dicetak, misalnya data yang diterima recvfrom, default hanya 32 byte
* -c statistik real-time waktu setiap system call
* -T mencetak durasi setiap system call

## gdb

GDB adalah alat debugging program yang powerful di UNIX, dirilis oleh GNU Open Source. Bisa digunakan untuk men-debug program C/C++. PHP dan Swoole dikembangkan dengan C, jadi GDB bisa digunakan untuk men-debug program PHP+Swoole.

Debugging gdb bersifat interaktif command line, perlu menguasai perintah umum.

### Cara Penggunaan

```shell
gdb -p processID
gdb php
gdb php core
```

Ada 3 cara menggunakan gdb:

* Melacak program PHP yang sedang berjalan, gunakan `gdb -p processID`
* Menjalankan dan men-debug program PHP dengan gdb, gunakan `gdb php -> run server.php`
* Setelah program PHP mengalami coredump, gunakan gdb untuk me-load core memory image `gdb php core`

!> Jika php tidak ada di PATH environment variable, perlu menentukan path absolut saat menggunakan gdb, misalnya `gdb /usr/local/bin/php`

### Perintah Umum

* `p`: print, mencetak nilai variabel C
* `c`: continue, melanjutkan eksekusi program yang dihentikan
* `b`: breakpoint, memasang breakpoint, bisa berdasarkan nama fungsi seperti `b zif_php_function`, atau berdasarkan nomor baris source code seperti `b src/networker/Server.c:1000`
* `t`: thread, berpindah thread, jika process memiliki banyak thread, bisa menggunakan perintah t untuk berpindah
* `ctrl + c`: menginterupsi program yang sedang berjalan, digunakan bersama perintah c
* `n`: next, mengeksekusi baris berikutnya, debugging step-by-step
* `info threads`: melihat semua thread yang berjalan
* `l`: list, melihat source code, bisa menggunakan `l nama_fungsi` atau `l nomor_baris`
* `bt`: backtrace, melihat call stack fungsi saat runtime
* `finish`: menyelesaikan fungsi saat ini
* `f`: frame, digunakan dengan bt untuk berpindah ke level tertentu di call stack
* `r`: run, menjalankan program

### zbacktrace

zbacktrace adalah perintah gdb kustom yang disediakan oleh paket source PHP. Fungsinya mirip dengan bt, bedanya zbacktrace menampilkan call stack fungsi PHP, bukan C.

Download php-src, extract, cari file `.gdbinit` di root directory, lalu di gdb shell:

```shell
source .gdbinit
zbacktrace
```
`.gdbinit` juga menyediakan perintah lain, lihat source code untuk info lebih detail.

#### Melacak Infinite Loop dengan gdb+zbacktrace

```shell
gdb -p processID
```

* Gunakan `ps aux` untuk mencari Worker process ID yang mengalami infinite loop
* `gdb -p` melacak process yang ditentukan
* Tekan `ctrl + c`, `zbacktrace`, `c` berulang kali untuk melihat di bagian kode PHP mana loop terjadi
* Temukan kode PHP yang sesuai dan perbaiki

## lsof

Platform Linux menyediakan `lsof` untuk melihat file handle yang dibuka oleh suatu process. Bisa digunakan untuk melacak semua socket, file, resource yang dibuka oleh worker process Swoole.

### Cara Penggunaan

```shell
lsof -p [processID]
```

### Hasil

```shell
lsof -p 26821
lsof: WARNING: can't stat() tracefs file system /sys/kernel/debug/tracing
      Output information may be incomplete.
COMMAND   PID USER   FD      TYPE             DEVICE SIZE/OFF    NODE NAME
php     26821  htf  cwd       DIR                8,4     4096 5375979 /home/htf/workspace/swoole/examples
php     26821  htf  rtd       DIR                8,4     4096       2 /
php     26821  htf  txt       REG                8,4 24192400 6160666 /opt/php/php-5.6/bin/php
php     26821  htf  DEL       REG                0,5          7204965 /dev/zero
php     26821  htf  DEL       REG                0,5          7204960 /dev/zero
php     26821  htf  DEL       REG                0,5          7204958 /dev/zero
php     26821  htf  DEL       REG                0,5          7204957 /dev/zero
php     26821  htf  DEL       REG                0,5          7204945 /dev/zero
php     26821  htf  mem       REG                8,4   761912 6160770 /opt/php/php-5.6/lib/php/extensions/debug-zts-20131226/gd.so
php     26821  htf  mem       REG                8,4  2769230 2757968 /usr/local/lib/libcrypto.so.1.1
php     26821  htf  mem       REG                8,4   162632 6322346 /lib/x86_64-linux-gnu/ld-2.23.so
php     26821  htf  DEL       REG                0,5          7204959 /dev/zero
php     26821  htf    0u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    1u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    2u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    3r      CHR                1,9      0t0      11 /dev/urandom
php     26821  htf    4u     IPv4            7204948      0t0     TCP *:9501 (LISTEN)
php     26821  htf    5u     IPv4            7204949      0t0     UDP *:9502 
php     26821  htf    6u     IPv6            7204950      0t0     TCP *:9503 (LISTEN)
php     26821  htf    7u     IPv6            7204951      0t0     UDP *:9504 
php     26821  htf    8u     IPv4            7204952      0t0     TCP localhost:8000 (LISTEN)
php     26821  htf    9u     unix 0x0000000000000000      0t0 7204953 type=DGRAM
php     26821  htf   10u     unix 0x0000000000000000      0t0 7204954 type=DGRAM
php     26821  htf   11u     unix 0x0000000000000000      0t0 7204955 type=DGRAM
php     26821  htf   12u     unix 0x0000000000000000      0t0 7204956 type=DGRAM
php     26821  htf   13u  a_inode               0,11        0    9043 [eventfd]
php     26821  htf   14u     unix 0x0000000000000000      0t0 7204961 type=DGRAM
php     26821  htf   15u     unix 0x0000000000000000      0t0 7204962 type=DGRAM
php     26821  htf   16u     unix 0x0000000000000000      0t0 7204963 type=DGRAM
php     26821  htf   17u     unix 0x0000000000000000      0t0 7204964 type=DGRAM
php     26821  htf   18u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   19u  a_inode               0,11        0    9043 [signalfd]
php     26821  htf   20u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   22u     IPv4            7452776      0t0     TCP localhost:9501->localhost:59056 (ESTABLISHED)
```

* File so adalah dynamic link library yang dimuat process
* IPv4/IPv6 TCP (LISTEN) adalah port yang didengarkan server
* UDP adalah port UDP yang didengarkan server
* `unix type=DGRAM` adalah [unixSocket](/learn?id=apa-itu-IPC) yang dibuat process
* IPv4 (ESTABLISHED) menunjukkan TCP client yang terhubung ke server, berisi IP dan PORT client, serta status (ESTABLISHED)
* 9u / 10u menunjukkan nilai fd (file descriptor) dari file handle tersebut
* Informasi lain lihat manual lsof

## perf

`perf` adalah alat dynamic tracing yang sangat powerful dari Linux kernel. Perintah `perf top` bisa digunakan untuk menganalisis masalah performa program yang sedang berjalan secara real-time. Berbeda dengan `callgrind`, `xdebug`, `xhprof`, `perf` tidak perlu mengubah kode untuk mengekspor file hasil profile.

### Cara Penggunaan

```shell
perf top -p [processID]
```

### Output

![perf top output](/_images/other/perf.png)

Hasil perf menunjukkan dengan jelas waktu eksekusi setiap fungsi C di proses saat ini, bisa mengetahui fungsi C mana yang menggunakan banyak resource CPU.

Jika kamu familiar dengan Zend VM, terlalu banyak panggilan fungsi Zend tertentu bisa menunjukkan bahwa programmu banyak menggunakan fungsi tertentu, menyebabkan CPU usage tinggi. Lakukan optimasi yang sesuai.
