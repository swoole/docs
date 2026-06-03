# Swoole

?> `Swoole` adalah mesin komunikasi jaringan paralel berbasis event-driven asynchronous dan coroutine, ditulis dalam `C++`. Swoole menyediakan dukungan [coroutine](/coroutine) dan [high-performance](/question/use?id=how-is-the-performance-of-swoole) network programming untuk `PHP`. Dilengkapi dengan berbagai modul server dan klien untuk berbagai protokol komunikasi, kamu bisa dengan cepat membuat `TCP/UDP services`, `Web berperforma tinggi`, `WebSocket services`, `IoT`, `real-time communication`, `game`, `microservices`, dan lain-lain. `PHP` nggak lagi terbatas di web tradisional.

## Diagram Class Swoole

!>Klik link untuk langsung menuju halaman dokumentasi terkait

[//]: # (https://naotu.baidu.com/file/bd9d2ba7dfae326e6976f0c53f88b18c)

<embed src="/_images/swoole_class_id.svg" type="image/svg+xml" alt="Diagram Arsitektur Swoole" />

## Situs Resmi

* [Situs Resmi Swoole](//www.swoole.com)
* [Produk Komersial & Dukungan](//business.swoole.com)
* [Tanya Jawab Swoole](//wenda.swoole.com)

## Repositori

* [GitHub](//github.com/swoole/swoole-src) **(Kasih Star ya)**
* [Gitee](//gitee.com/swoole/swoole)
* [PECL](//pecl.php.net/package/swoole)

## Alat Pengembangan

* [IDE Helper](https://github.com/swoole/ide-helper)
* [Yasd](https://github.com/swoole/yasd)
* [debugger](https://github.com/swoole/debugger)
* [sdebug](https://github.com/swoole/sdebug)

## Hak Cipta

Konten asli dokumen ini berasal dari [dokumentasi Swoole versi lama](https://wiki.swoole.com/wiki/index/prid-1), yang dibuat untuk menyelesaikan masalah dokumentasi yang sudah lama dikeluhkan. Dokumentasi ini menggunakan format modern, hanya mencakup konten `Swoole4`, sudah memperbaiki banyak kesalahan dari dokumen lama, mengoptimalkan detail, dan menambahkan contoh kode serta materi pembelajaran biar lebih ramah buat pemula `Swoole`.

Seluruh konten dalam dokumen ini, termasuk teks, gambar, dan materi audio-visual, adalah hak cipta dari **Shanghai SWO Network Technology Co., Ltd**. Media, situs web, atau individu boleh mengutip dalam bentuk tautan eksternal, tapi dilarang menyalin atau mempublikasikan dalam bentuk apa pun tanpa izin.

## Penggagas Dokumen

* Yang Cai [GitHub](https://github.com/TTSimple)
* Guo Xinhua [Weibo](https://www.weibo.com/u/2661945152)
* [Lu Fei](https://github.com/sy-records) [Akun Resmi Weixin](http://go.qq52o.me/a/mp)

## Laporan Masalah

Kalau nemu masalah dengan konten dokumen ini (typo, kesalahan contoh kode, konten kurang, dll) atau punya saran, silakan buat [issue](https://github.com/swoole/docs/issues/new). Bisa juga langsung klik tombol [Edit](/?id=main) di pojok kanan atas buat kirim `Pull request`.

Kalau kontribusinya diterima, nama kamu akan ditambahkan ke daftar [Kontributor Dokumen](/CONTRIBUTING) sebagai tanda terima kasih.

## Prinsip Dokumentasi

Pakai bahasa yang lugas, **usahakan** seminimal mungkin menjelaskan detail teknis dan konsep internal `Swoole`. Konsep internalnya nanti bisa diurus di bagian `hack` terpisah;

Kalau ada konsep yang nggak bisa dihindari, **wajib** ada satu tempat khusus yang menjelaskan konsep itu, dan tempat lain tinggal nge-link ke sana. Contohnya: [Event Loop](/learn?id=what-is-eventloop);

Nulis dokumentasi harus dari sudut pandang pengguna, pastikan gampang dipahami;

Kalau ada perubahan fitur, **wajib** ubah semua bagian yang terkait, jangan cuma sebagian;

Setiap modul fitur **wajib** punya contoh kode yang lengkap.
