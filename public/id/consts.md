# Konstanta

!> Ini tidak mencakup semua konstanta. Untuk melihat semua konstanta, silakan kunjungi atau instal: [ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)

## Swoole

Konstanta | Keterangan
---|---
SWOOLE_VERSION | Nomor versi Swoole saat ini, dalam format string, misalnya 1.6.0

## Parameter Konstruktor

Konstanta | Keterangan
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | Menggunakan mode Base, kode bisnis berjalan langsung di proses Reactor
[SWOOLE_PROCESS](/learn?id=swoole_process) | Menggunakan mode Process, kode bisnis berjalan di proses Worker

## Tipe Socket

Konstanta | Keterangan
---|---
SWOOLE_SOCK_TCP | Membuat socket tcp
SWOOLE_SOCK_TCP6 | Membuat socket tcp ipv6
SWOOLE_SOCK_UDP | Membuat socket udp
SWOOLE_SOCK_UDP6 | Membuat socket udp ipv6
SWOOLE_SOCK_UNIX_DGRAM | Membuat socket unix dgram
SWOOLE_SOCK_UNIX_STREAM | Membuat socket unix stream
SWOOLE_SOCK_SYNC | Klien sinkron

## Metode Enkripsi SSL

Konstanta | Keterangan
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD (Metode enkripsi default) | -
SWOOLE_SSLv23_SERVER_METHOD | -
SWOOLE_SSLv23_CLIENT_METHOD | -
SWOOLE_TLSv1_METHOD | -
SWOOLE_TLSv1_SERVER_METHOD | -
SWOOLE_TLSv1_CLIENT_METHOD | -
SWOOLE_TLSv1_1_METHOD | -
SWOOLE_TLSv1_1_SERVER_METHOD | -
SWOOLE_TLSv1_1_CLIENT_METHOD | -
SWOOLE_TLSv1_2_METHOD | -
SWOOLE_TLSv1_2_SERVER_METHOD | -
SWOOLE_TLSv1_2_CLIENT_METHOD | -
SWOOLE_DTLSv1_METHOD | -
SWOOLE_DTLSv1_SERVER_METHOD | -
SWOOLE_DTLSv1_CLIENT_METHOD | -
SWOOLE_DTLS_SERVER_METHOD | -
SWOOLE_DTLS_CLIENT_METHOD | -

!> `SWOOLE_DTLSv1_METHOD`, `SWOOLE_DTLSv1_SERVER_METHOD`, `SWOOLE_DTLSv1_CLIENT_METHOD` telah dihapus di Swoole versi >= `v4.5.0`.

## Protokol SSL

Konstanta | Keterangan
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

!> Tersedia di Swoole versi >= `v4.5.4`

## Level Log

Konstanta | Keterangan
---|---
SWOOLE_LOG_DEBUG | Log debug, hanya untuk pengembangan dan debugging kernel
SWOOLE_LOG_TRACE | Log trace, dapat digunakan untuk melacak masalah sistem, log debug diatur secara hati-hati untuk membawa informasi penting
SWOOLE_LOG_INFO | Informasi umum, hanya untuk tampilan informasi
SWOOLE_LOG_NOTICE | Informasi pemberitahuan, sistem mungkin memiliki beberapa perilaku seperti restart, shutdown
SWOOLE_LOG_WARNING | Informasi peringatan, sistem mungkin memiliki beberapa masalah
SWOOLE_LOG_ERROR | Informasi kesalahan, terjadi kesalahan kritis pada sistem, perlu segera ditangani
SWOOLE_LOG_NONE | Setara dengan menonaktifkan informasi log, informasi log tidak akan dikeluarkan

!> Log `SWOOLE_LOG_DEBUG` dan `SWOOLE_LOG_TRACE` harus digunakan setelah mengompilasi ekstensi Swoole dengan [--enable-debug-log](/environment?id=debug参数) atau [--enable-trace-log](/environment?id=debug参数). Dalam versi normal, meskipun `log_level = SWOOLE_LOG_TRACE` diatur, log tersebut tidak dapat dicetak.

## Label Lacak

Layanan yang berjalan di produksi selalu memproses banyak permintaan setiap saat, jumlah log yang dikeluarkan di tingkat bawah sangat besar. Anda dapat menggunakan `trace_flags` untuk mengatur label log trace, hanya mencetak sebagian log trace. `trace_flags` mendukung pengaturan beberapa item trace menggunakan operator `|` OR.

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

Item trace berikut didukung di tingkat bawah, Anda dapat menggunakan `SWOOLE_TRACE_ALL` untuk melacak semua item:

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`
