# Contoh Sederhana

Sebagian besar fitur `Swoole` cuma bisa dipakai di lingkungan `cli` command line. Pastikan kamu sudah punya lingkungan `Linux Shell`. Kamu bisa pakai editor kayak `Vim`, `Emacs`, `PhpStorm`, atau editor lainnya buat nulis kode, lalu jalankan programnya lewat command line dengan perintah berikut.

```shell
php /path/to/your_file.php
```

Setelah program server `Swoole` berhasil dijalankan, kalo kode kamu nggak ada perintah `echo`, layar nggak bakal nampilin apa-apa. Tapi sebenarnya sistem udah mulai listening di port jaringan, siap nunggu koneksi dari client. Kamu bisa pake tool dan program client yang sesuai buat connect ke server dan melakukan testing.

#### Manajemen Proses

Secara default, setelah kamu jalanin service `Swoole`, kamu bisa langsung matiin pake `CTRL+C` di terminal. Tapi kalo terminal-nya ditutup, bakal timbul masalah. Makanya perlu dijalankan sebagai background process. Detailnya lihat [Daemonize](/server/setting?id=daemonize).

!> Contoh-contoh di sini kebanyakan pake gaya pemrograman asynchronous. Kamu juga bisa mencapai fungsionalitas yang sama pake gaya coroutine. Lihat [Server (Coroutine Style)](coroutine/server.md).

!> Sebagian besar modul yang disediakan `Swoole` cuma bisa dipake di terminal `cli`. Saat ini cuma [Synchronous Blocking Client](/client) yang bisa dipake di lingkungan `PHP-FPM`.
