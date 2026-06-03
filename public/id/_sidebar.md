
* Instalasi
  * [Instalasi Swoole](environment.md)
  * [Konflik Ekstensi](getting_started/extension.md)

* [Contoh Sederhana](start/start_server.md)
  * [Server TCP](start/start_tcp_server.md)
  * [Server UDP](start/start_udp_server.md)
  * [Server HTTP](start/start_http_server.md)
  * [Server WebSocket](start/start_ws_server.md)
  * [Server MQTT (IoT)](start/start_mqtt.md)
  * [Jalankan Task Async (Task)](start/start_task.md)
  * [Pengenalan Coroutine](start/coroutine.md)

* [Server (Async Style)](server/init.md)
  * [Server TCP/UDP](server/tcp_init.md)
    * [Method](server/methods.md)
    * [Properti](server/properties.md)
    * [Konfigurasi](server/setting.md)
    * [Callback Event](server/events.md)
  * [Server HTTP](http_server.md)
  * [Server WebSocket](websocket_server.md)
  * [Server Redis](redis_server.md)
  * [Multi-port Listening](server/port.md)

* [Server (Coroutine Style)](server/co_init.md)
  * [Server TCP](coroutine/server.md)
  * [Server HTTP](coroutine/http_server.md)
  * [Server WebSocket](coroutine/ws_server.md)

* [Klien](client_init.md)
  * [Klien Sync Blocking](client.md)
  * [Klien Async Callback](client_async.md)
  * [Klien Coroutine](coroutine_client/init.md)
    * [Klien TCP/UDP](coroutine_client/client.md)
    * [Klien Socket](coroutine_client/socket.md)
    * [Klien HTTP/WebSocket](coroutine_client/http_client.md)
    * [Klien HTTP2](coroutine_client/http2_client.md)
    * [Klien PostgreSQL](coroutine_client/postgresql.md)
    * [Klien FastCGI](coroutine_client/fastcgi.md)
    * [Klien MySQL](coroutine_client/mysql.md)
    * [Klien Redis](coroutine_client/redis.md)

* [Coroutine](coroutine.md)
  * [Satu Klik Coroutine](runtime.md)
  * [API Inti](coroutine/coroutine.md)
  * [Coroutine Container](coroutine/scheduler.md)
  * [API Sistem](coroutine/system.md)
  * [API Proses](coroutine/proc_open.md)
  * [Channel](coroutine/channel.md)
  * [WaitGroup](coroutine/wait_group.md)
  * [Barrier](coroutine/barrier.md)
  * [Panggilan Konkuren](coroutine/multi_call.md)
  * [IO-Uring](coroutine/iouring.md)
  * [Connection Pool](coroutine/conn_pool.md)
  * [Library](library.md)
  * [Debug Coroutine](coroutine/gdb.md)
  * [Panduan Pemrograman](coroutine/notice.md)

* Operasi File Async
  * [Implementasi](file/engine.md)
  * [Konfigurasi](file/setting.md)

* Manajemen Thread
  * [Buat Thread](thread/thread.md)
  * [Thread Pool](thread/pool.md)
  * [Method & Properti](thread/info)
  * [Concurrent Map](thread/map.md)
  * [Concurrent List](thread/arraylist.md)
  * [Concurrent Queue](thread/queue.md)
  * [Sync Barrier](thread/barrier.md)
  * [Tipe Data](thread/transfer.md)

* Manajemen Proses
  * [Buat Proses](process/process.md)
  * [Process Pool](process/process_pool.md)
  * [Process Manager](process/process_manager.md)
  * [Shared Memory High-Performance (Table)](memory/table.md)

* Manajemen Konkurensi
  * [Lock](memory/lock.md)
  * [Coroutine Lock](memory/coroutine_lock.md)
  * [Atomic Counter](memory/atomic.md)

* [Event Loop](event.md)

* [Timer](timer.md)
* [Ekstensi PHP Standard Library](stdext/index.md)
  * [String](stdext/string.md)
  * [Array](stdext/array.md)
  * [Stream](stdext/stream.md)

* Lainnya
  * [Konstanta](consts.md)
  * [Kode Error](other/errno.md)
  * [Konfigurasi ini](other/config.md)
  * [Fungsi Lain](functions.md)
  * [Alat](other/tools.md)
  * [Alias Fungsi](other/alias.md)
  * [Lapor Bug](other/issue.md)
  * [Parameter Kernel](other/sysctl.md)
  * [Daftar Sinyal Linux](other/signal.md)
  * [Diskusi Online](other/discussion.md)
  * [Kontributor](CONTRIBUTING.md)
  * [Donasi](other/donate.md)
  * [Pengguna & Studi Kasus](case.md)

* Tanya Jawab
  * [Masalah Instalasi](question/install.md)
  * [Masalah Penggunaan](question/use.md)
  * [Tentang Swoole](question/swoole.md)

* Manajemen Versi
  * [Rencana Dukungan](version/supported.md)
  * [Perubahan Tidak Kompatibel](version/bc.md)
  * [Catatan Rilis](version/log.md)

* Belajar Swoole
  * [Dasar-dasar](learn.md)
  * [Panduan Pemrograman](getting_started/notice.md)
  * [Pengetahuan Lain](learn_other.md)
  * [Artikel Swoole](blog_list.md)
