# Server (Gaya Async)

Membuat program server asynchronous dengan mudah, mendukung 3 jenis socket: `TCP`, `UDP`, dan [unixSocket](/learn?id=apa-itu-ipc), dengan dukungan `IPv4` dan `IPv6`, serta enkripsi tunnel `SSL/TLS` satu arah maupun dua arah. Pengguna tidak perlu mempedulikan detail implementasi internal, cukup atur fungsi callback untuk [event](/server/events) jaringan saja, lihat contoh di [Mulai Cepat](/start/start_tcp_server).

!> Gaya `Server` ini bersifat asynchronous (semua event perlu mengatur fungsi callback), tetapi juga mendukung coroutine. Dengan mengaktifkan [enable_coroutine](/server/setting?id=enable_coroutine) (default aktif), coroutine dapat digunakan. Semua kode bisnis di bawah [coroutine](/coroutine) ditulis secara sinkron.

Pelajari lebih lanjut:

[Pengantar tiga mode operasi Server](/learn?id=pengantar-tiga-mode-operasi-server ':target=_blank')  
[Apa perbedaan antara Process, ProcessPool, dan UserProcess](/learn?id=apa-perbedaannya ':target=_blank')  
[Perbedaan dan hubungan antara Master Process, Reactor Thread, Worker Process, Task Process, dan Manager Process](/learn?id=perbedaan-dan-hubungan ':target=_blank')

### Diagram Alir Eksekusi <!-- {docsify-ignore} -->

![running_process](https://wiki.swoole.com/_images/server/running_process.png ':size=800xauto')

### Diagram Struktur Proses/Thread <!-- {docsify-ignore} -->

![process_structure](https://wiki.swoole.com/_images/server/process_structure.png ':size=800xauto')

![process_structure_2](https://wiki.swoole.com/_images/server/process_structure_2.png)
