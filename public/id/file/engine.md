# Operasi File Asinkron

[Korutinisasi satu klik](/runtime) `Swoole` bisa mengubah operasi file `PHP` dari sinkron blocking menjadi eksekusi asinkron. `Swoole` memiliki dua strategi file asinkron bawaan.

## Thread Pool

* `Thread Pool` adalah operasi file asinkron default `Swoole`. Saat pengguna melakukan operasi file, `Swoole` akan mengirimkan operasi ini langsung ke `Thread Pool`, di mana child thread bertanggung jawab menyelesaikan operasi file. Setelah selesai, korutin akan dialihkan kembali.
* Semua fungsi operasi file `PHP` bisa diimplementasikan secara asinkron melalui `Thread Pool`, misalnya `file_get_contents`, `fopen`, dll.
* Tidak perlu library dependensi, kompatibilitas tinggi, bisa langsung digunakan.

## io_uring

* `io_uring` adalah strategi bawaan setelah `Swoole v6.0`, berdasarkan `io_uring` dan `epoll` untuk mencapai asinkron.
* Throughput tinggi, bisa menangani banyak operasi file asinkron.
* `io_uring` memiliki persyaratan tinggi untuk versi kernel Linux. Disarankan menggunakan Linux 5.12+, liburing 2.6+.
* Karena berdasarkan `file descriptor`, hanya mendukung beberapa fungsi operasi file `PHP`.

!> Hanya bisa digunakan setelah sistem menginstal `liburing` dan kompilasi `Swoole` mengaktifkan `--enable-iouring`.

!> Mengaktifkan `io_uring` tidak akan menggantikan mode `Thread Pool`. Beberapa fungsi yang tidak bisa di-korutinisasi oleh `io_uring` tetap akan diproses oleh `Thread Pool`.

!> `io_uring` hanya mendukung fungsi `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize`.
