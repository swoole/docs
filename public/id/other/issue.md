# Melaporkan Bug

## Informasi

Saat kamu merasa menemukan BUG di inti Swoole, harap laporkan. Pengembang inti Swoole mungkin belum tahu tentang masalah ini kecuali kamu melaporkannya. Kamu bisa melaporkan bug di [GitHub issue](https://github.com/swoole/swoole-src/issues) (klik tombol `New issue`). Laporan bug di sini akan diprioritaskan.

Jangan kirim laporan bug melalui email atau pesan pribadi. Kamu juga bisa mengajukan permintaan atau saran untuk Swoole di GitHub issue.

Sebelum melaporkan bug, harap baca **Cara Melaporkan Bug** di bawah.

## Membuat Issue Baru

Saat membuat issue, sistem akan memberikan template berikut. Harap isi dengan benar, jika tidak issue mungkin diabaikan karena kurang informasi:

```markdown

Please answer these questions before submitting your issue. Thanks!
> Silakan jawab pertanyaan berikut sebelum mengirim issue:

1. What did you do? If possible, provide a simple script for reproducing the error.
> Jelaskan proses munculnya masalah, tempelkan kode terkait, idealnya berikan script sederhana yang bisa mereproduksi masalah secara stabil.

2. What did you expect to see?
> Apa hasil yang diharapkan?

3. What did you see instead?
> Apa hasil yang sebenarnya?

4. What version of Swoole are you using (`php --ri swoole`)?
> Versi apa yang kamu gunakan? Tempelkan output dari `php --ri swoole`

5. What is your machine environment used (including the version of kernel & php & gcc)?
> Lingkungan sistem apa yang kamu gunakan (termasuk kernel, PHP, versi compiler gcc)?
> Bisa menggunakan perintah `uname -a`, `php -v`, `gcc -v`

```

Yang paling penting adalah menyediakan **script kode sederhana yang bisa mereproduksi masalah secara stabil**. Jika tidak, kamu harus memberikan informasi sebanyak mungkin untuk membantu pengembang menemukan penyebab error.

## Analisis Memori (Sangat Direkomendasikan)

Seringkali, Valgrind lebih efektif menemukan masalah memori daripada gdb. Jalankan programmu dengan perintah berikut sampai bug terpicu:

```shell
USE_ZEND_ALLOC=0 valgrind --log-file=/tmp/valgrind.log php your_file.php
```

* Saat terjadi error, bisa keluar dengan mengetik `ctrl+c`, lalu upload file `/tmp/valgrind.log` untuk membantu tim pengembang menemukan BUG.

## Tentang Segmentation Fault (Core Dump)

Dalam kasus khusus, kamu bisa menggunakan alat debugging untuk membantu pengembang menemukan masalah:

```shell
WARNING	swManager_check_exit_status: worker#1 abnormal exit, status=0, signal=11
```

Jika peringatan di atas muncul di log Swoole (signal 11), berarti program mengalami `core dump`. Kamu perlu menggunakan alat debugging untuk menentukan lokasi kejadian.

> Sebelum menggunakan `gdb` untuk melacak `swoole`, perlu menambahkan parameter `--enable-debug` saat kompilasi untuk menyimpan lebih banyak informasi

Aktifkan file core dump:
```shell
ulimit -c unlimited
```

Picu BUG, file core dump akan dihasilkan di direktori program, root direktori sistem, atau direktori `/cores` (tergantung konfigurasi sistem).

Masukkan perintah berikut untuk masuk ke gdb:

```
gdb php core
gdb php /tmp/core.1234
```

Lalu ketik `bt` dan enter untuk melihat call stack yang bermasalah:
```
(gdb) bt
```

Bisa melihat stack frame tertentu dengan mengetik `f angka`:
```
(gdb) f 1
(gdb) f 0
```

Tempelkan semua informasi di atas ke dalam issue.
