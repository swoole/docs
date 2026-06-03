# Rencana Dukungan

| Branch                                                         | Versi PHP    | Tanggal Mulai | Dukungan Aktif Berakhir | Pemeliharaan Keamanan Berakhir |
|---------------------------------------------------------------|-------------|---------------|-------------------------|-------------------------------|
| [v5.1.x](https://github.com/swoole/swoole-src/tree/5.1.x)     | 8.0 - 8.3   | 2023-11-29    | 2024-11-29              | 2025-04-29                    |
| [v6.0.x](https://github.com/swoole/swoole-src/tree/master)    | 8.1 - 8.4   | 2024-12-31    | 2025-12-31              | 2026-06-31                    |

- **Dukungan Aktif**: Mendapat dukungan aktif dari tim pengembang resmi, bug yang dilaporkan dan masalah keamanan akan segera diperbaiki, dan rilis resmi akan dikeluarkan sesuai proses reguler.
- **Pemeliharaan Keamanan**: Hanya perbaikan masalah keamanan kritis yang didukung, dan rilis resmi hanya dilakukan jika diperlukan.

Referensi: [https://github.com/swoole/swoole-src/blob/master/docs/SUPPORTED.md](https://github.com/swoole/swoole-src/blob/master/docs/SUPPORTED.md)

## Branch yang Tidak Lagi Didukung

!> Versi-versi ini tidak lagi didukung oleh tim resmi. Pengguna yang masih menggunakan versi berikut harus segera meningkatkan versi karena mereka mungkin menghadapi kerentanan keamanan yang belum diperbaiki.

- `v1.x` (2012-7-1 ~ 2018-05-14)
- `v2.x` (2016-12-30 ~ 2018-05-23)
- `v3.x` (tidak digunakan)
- `v4.0.x`, `v4.1.x`, `v4.2.x`, `v4.3.x` (2018-06-14 ~ 2019-12-31)
- `v4.4.x` (2019-04-15 ~ 2020-04-30)
- `v4.5.x` (2019-12-20 ~ 2021-01-06)
- `v4.6.x`, `v4.7.x` (2021-01-06 ~ 2021-12-31)
- `v4.8.x` (2021-10-14 ~ 2024-06-30)
- `v5.0.x` (2022-01-20 ~ 2023-07-20)

## Karakteristik Versi
- `v1.x`: Mode callback asinkron.
- `v2.x`: Coroutine single-stack berbasis `setjmp/longjmp`, implementasi masih berbasis callback asinkron, beralih tumpukan panggilan `PHP` setelah event callback terpicu.
- `v4.0-v4.3`: Coroutine dual-stack berbasis `boost context asm`, kernel mengimplementasikan coroutine secara komprehensif, dengan penjadwal coroutine berbasis `EventLoop`.
- `v4.4-v4.8`: Mengimplementasikan `runtime coroutine hook`, secara otomatis mengganti fungsi sinkron blokir bawaan PHP dengan mode asinkron non-blocking untuk coroutine, membuat Coroutine Swoole kompatibel dengan sebagian besar library PHP.
- `v5.0`: Coroutine penuh, menghapus modul non-coroutine; tipe kuat, menghapus banyak beban historis; menyediakan mode runtime `swoole-cli` yang baru.
- `v5.1`: Mendukung coroutine `pdo_pgsql`, `pdo_oci`, `pdo_odbc`, `pdo_sqlite`, meningkatkan kinerja `Http\Server`.
- `v6.0`: Mendukung mode multi-thread.
