# Konfigurasi ini

Konfigurasi | Nilai Default | Fungsi
---|---|---
swoole.enable_coroutine | On | `On`, `Off` mengaktifkan/mematikan korutin bawaan, [detail](/server/setting?id=enable_coroutine).
swoole.display_errors | On | Mengaktifkan/menonaktifkan pesan error `Swoole`.
swoole.unixsock_buffer_size | 8M | Mengatur ukuran buffer `Socket` untuk komunikasi antar proses, setara dengan [socket_buffer_size](/server/setting?id=socket_buffer_size).
swoole.use_shortname | On | Mengaktifkan/menonaktifkan alias pendek, [detail](/other/alias?id=coroutine-short-names).
swoole.enable_preemptive_scheduler | Off | Mencegah beberapa korutin menggunakan CPU terlalu lama (10ms CPU time) sehingga korutin lain tidak mendapat [jadwal](/coroutine?id=coroutine-scheduling), [contoh](https://github.com/swoole/swoole-src/tree/master/tests/swoole_coroutine_scheduler/preemptive).
swoole.enable_library | On | Mengaktifkan/menonaktifkan library bawaan ekstensi
