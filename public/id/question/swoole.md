# Asal-usul Proyek Swoole dan Asal Nama

!> Halaman ini ditulis oleh Rango, founder proyek open source Swoole. Hanya mewakili pandangan pribadinya.

## Asal-usul Proyek

Gagasan awal proyek Swoole berasal dari proyek software enterprise yang saya kerjakan sebelumnya. Sekitar akhir tahun 2010, produk perusahaan punya kebutuhan di mana pengguna bisa membuat alamat email secara bebas, lalu pengguna lain bisa kirim email ke alamat tersebut, dan backend bisa mengurai isi email jadi data secara real-time dan langsung notifikasi pengguna. Waktu itu proyek pakai PHP. Pas implementasi fitur ini, ketemu masalah: PHP cuma bisa andalkan SMTP server lain, dan harus polling email baru secara berkala lewat protokol pop3 — jadinya nggak real-time. Kalau mau sistem real-time, harus bikin sendiri `TCP Socket Server` yang implement protokol `SMTP` buat nerima data. Waktu itu `PHP` di bidang ini masih kosong, belum ada framework komunikasi network yang matang. Buat mewujudkan kebutuhan ini, saya belajar dari `socket` sampe `TCP/IP`, IO multiplexing, `libevent`, multiprocess, dan akhirnya berhasil bikin program ini. Setelah selesai, saya ingin buka source program ini, berharap bisa bantu developer PHP lain yang punya masalah serupa. Dengan adanya framework kayak gini, PHP nggak cuma bisa bikin website doang, tapi bisa meluas ke ranah yang lebih besar.

## Masalah Performa

Alasan penting lainnya adalah masalah performa program PHP. Awalnya saya belajar Java, baru jadi programmer PHP setelah kerja. Selama ngembangin pake PHP, saya terus mikir: apa sih kelebihan utama PHP dibanding Java? Simple dan efisien. Setelah request selesai, PHP melepaskan semua resource dan memory, nggak perlu khawatir memory leak. Kode berkualitas rendah pun jalan lancar. Tapi ini juga kelemahan fatal PHP. Begitu jumlah request naik dan concurrency tinggi, bikin resource cepet-cepet terus dilepas, efisiensi program PHP turun drastis. Selain itu, makin kompleks fitur dan makin banyak kode, itu bencana buat PHP. Makanya framework PHP nggak diterima luas sama programmer PHP, beda sama Java yang nggak punya masalah ini. Framework sebagus apapun bakal terbebani sama cara nggak efisien ini, bikin sistem lemot. Makanya saya mikir, gimana kalo pake PHP buat bikin application server PHP sendiri, biar kode PHP di-load ke memory dan punya umur lebih panjang. Dengan begitu, koneksi database dan object besar lain nggak dilepas. Setiap request cuma perlu ngolah sedikit kode, dan kode itu cuma dikompilasi sekali pas pertama jalan, lalu menetap di memory. Plus, yang tadinya nggak mungkin di PHP — object persistence, connection pool database, cache connection pool — semua bisa diwujudkan. Efisiensi sistem bakal naik drastis.

Setelah riset beberapa waktu, akhirnya berhasil diwujudkan. Saya bikin HTTP server pake PHP sendiri, jalan sebagai server mandiri. Waktu eksekusi satu halaman program (ada object generation, koneksi database, operasi template smarty) turun dari 0.0x detik jadi 0.00x detik. Pake Apache AB concurrency 100, Request per Second lebih tinggi 10 kali lipat dari cara LAMP tradisional. Di mesin test saya (Ubuntu10.04 Intel Core E5300 + 2G RAM), Apache cuma 83 RPS. Swoole Server bisa sampe 1150+ RPS.

Proyek ini adalah cikal bakal Swoole. Versi ini saya maintenance selama 2 tahun lebih. Selama itu, saya makin paham masalah dari pendekatan teknis ini, kayak performa jelek, banyak batasan yang nggak bisa langsung panggil interface OS, dan manajemen memory yang nggak efisien.

## Masuk Tencent

Akhir 2011 saya masuk Tencent, bertanggung jawab ngembangin platform PHP untuk Pengyou Wang (friendster). Kaget juga pas lihat rekan-rekan di Pengyou Wang bukan cuma mikirin hal yang sama, tapi mereka langsung implementasi. Tim Pengyou Wang udah pake solusi ini di production. Pengyou Wang punya tiga komponen utama: pertama PWS, WebServer murni PHP. Lebih dari 600 server di Pengyou Wang jalan di PWS, tanpa pake Apache, PHP-FPM, dll. Kedua SAPS, antrian distributed murni PHP, waktu itu sekitar 150 server. Banyak logic kayak image cropping, avatar processing, messaging, data sync — semuanya pake SAPS buat asinkronisasi. Ketiga PSF, framework server PHP. Banyak server logic layer di Pengyou Wang berbasis PSF. Sekitar 300 server jalanin program server PSF. Selama di Pengyou Wang, saya banyak belajar Linux internal dan komunikasi network, dapet banyak pengalaman tracking dan debugging komunikasi network di lingkungan cluster besar dengan concurrency tinggi. Ini jadi fondasi yang bagus buat ngembangin Swoole.

## Ngembangin Swoole

Selama periode itu juga saya belajar dan paham solusi teknis keren kayak Node.js, Golang, dapet banyak inspirasi. Tahun 2012, saya punya ide baru dan mutusin pake bahasa C buat implement ulang versi yang lebih kuat dan lebih kenceng. Inilah ekstensi Swoole yang sekarang.

Sekarang Swoole udah dipake banyak tim teknis PHP buat proyek nyata, baik di China maupun luar. Di China yang terkenal: Baidu Order Center, Baidu Maps, Tencent QQ Official Account dan Enterprise QQ, Zhanqi live streaming, 360, Dangdang, Qyer, dll. Selain itu, banyak proyek IoT, hardware, dan game juga pake Swoole. Framework open source berbasis Swoole juga makin banyak, kayak TSF, Blink, swPromise, dll. Di GitHub juga banyak proyek dan kode terkait Swoole.

## Asal Nama

Nama Swoole bukan kata bahasa Inggris. Itu kata yang saya ciptakan, mirip bunyi. Awalnya saya mau namain `sword-server`, melambangkan pedang tajam buat developer PHP. Tapi kemudian inget Google juga nama ciptaan, jadinya saya kasih nama `swoole`.
