# Daftar Sinyal Linux

## Tabel Perbandingan Lengkap

| Sinyal     | Nilai    | Aksi Default | Arti (Penyebab Sinyal)                        |
| ---------- | -------- | ------------ | --------------------------------------------- |
| SIGHUP     | 1        | Term         | Hangup terminal atau proses mati               |
| SIGINT     | 2        | Term         | Sinyal interupsi dari keyboard                 |
| SIGQUIT    | 3        | Core         | Sinyal quit dari keyboard                      |
| SIGILL     | 4        | Core         | Instruksi ilegal                               |
| SIGABRT    | 6        | Core         | Sinyal abnormal dari abort                     |
| SIGFPE     | 8        | Core         | Eksepsi floating-point                         |
| SIGKILL    | 9        | Term         | Mematikan proses                               |
| SIGSEGV    | 11       | Core         | Segmentasi ilegal (referensi memori tidak valid)|
| SIGPIPE    | 13       | Term         | Pipe rusak: menulis ke pipe tanpa pembaca      |
| SIGALRM    | 14       | Term         | Timer alarm kedaluwarsa                        |
| SIGTERM    | 15       | Term         | Terminasi                                      |
| SIGUSR1    | 30,10,16 | Term         | Sinyal kustom pengguna 1                       |
| SIGUSR2    | 31,12,17 | Term         | Sinyal kustom pengguna 2                       |
| SIGCHLD    | 20,17,18 | Ign          | Proses anak berhenti atau terminasi            |
| SIGCONT    | 19,18,25 | Cont         | Jika berhenti, lanjutkan eksekusi              |
| SIGSTOP    | 17,19,23 | Stop         | Sinyal berhenti bukan dari terminal            |
| SIGTSTP    | 18,20,24 | Stop         | Sinyal berhenti dari terminal                  |
| SIGTTIN    | 21,21,26 | Stop         | Proses latar belakang baca terminal            |
| SIGTTOU    | 22,22,27 | Stop         | Proses latar belakang tulis terminal           |
|            |          |              |                                               |
| SIGBUS     | 10,7,10  | Core         | Bus error (kesalahan akses memori)             |
| SIGPOLL    |          | Term         | Event Pollable terjadi (Sys V), sinonim SIGIO |
| SIGPROF    | 27,27,29 | Term         | Timer profiling kedaluwarsa                    |
| SIGSYS     | 12,-,12  | Core         | Panggilan sistem ilegal (SVr4)                 |
| SIGTRAP    | 5        | Core         | Trap/breakpoint                                |
| SIGURG     | 16,23,21 | Ign          | Sinyal urgent socket (4.2BSD)                  |
| SIGVTALRM  | 26,26,28 | Term         | Timer virtual kedaluwarsa (4.2BSD)             |
| SIGXCPU    | 24,24,30 | Core         | Melebihi batas CPU (4.2BSD)                    |
| SIGXFSZ    | 25,25,31 | Core         | Melebihi batas ukuran file (4.2BSD)            |
|            |          |              |                                               |
| SIGIOT     | 6        | Core         | IOT trap, sinonim dengan SIGABRT               |
| SIGEMT     | 7,-,7    |              | Term                                           |
| SIGSTKFLT  | -,16,-   | Term         | Stack fault pada koprosesor (tidak digunakan)   |
| SIGIO      | 23,29,22 | Term         | I/O dapat dilakukan pada deskriptor             |
| SIGCLD     | -,-,18   | Ign          | Sinonim dengan SIGCHLD                         |
| SIGPWR     | 29,30,19 | Term         | Kegagalan daya (System V)                      |
| SIGINFO    | 29,-,-   |              | Sinonim dengan SIGPWR                          |
| SIGLOST    | -,-,-    | Term         | File lock hilang                                |
| SIGWINCH   | 28,28,20 | Ign          | Ukuran jendela berubah (4.3BSD, Sun)           |
| SIGUNUSED  | -,31,-   | Term         | Sinyal tidak digunakan (akan jadi SIGSYS)      |

## Sinyal Tidak Andal

| Nama      | Deskripsi                        |
| --------- | -------------------------------- |
| SIGHUP    | Koneksi terputus                 |
| SIGINT    | Karakter interupsi terminal      |
| SIGQUIT   | Karakter quit terminal           |
| SIGILL    | Instruksi hardware ilegal        |
| SIGTRAP   | Kerusakan hardware               |
| SIGABRT   | Terminasi abnormal (abort)       |
| SIGBUS    | Kerusakan hardware               |
| SIGFPE    | Eksepsi aritmatika               |
| SIGKILL   | Terminasi                        |
| SIGUSR1   | Sinyal defined pengguna          |
| SIGUSR2   | Sinyal defined pengguna          |
| SIGSEGV   | Referensi memori tidak valid     |
| SIGPIPE   | Menulis ke pipe tanpa pembaca    |
| SIGALRM   | Timer kedaluwarsa (alarm)        |
| SIGTERM   | Terminasi                        |
| SIGCHLD   | Status proses anak berubah       |
| SIGCONT   | Melanjutkan proses yang dijeda   |
| SIGSTOP   | Berhenti                         |
| SIGTSTP   | Karakter stop terminal           |
| SIGTTIN   | Baca tty kontrol dari background |
| SIGTTOU   | Tulis ke tty kontrol dari background |
| SIGURG    | Kondisi darurat (socket)         |
| SIGXCPU   | Melebihi batas CPU (setrlimit)   |
| SIGXFSZ   | Melebihi batas ukuran file (setrlimit) |
| SIGVTALRM | Alarm waktu virtual (setitimer)  |
| SIGPROF   | Profiling time kedaluwarsa (setitimer) |
| SIGWINCH  | Ukuran jendela terminal berubah  |
| SIGIO     | I/O asinkron                     |
| SIGPWR    | Gagal daya/restart               |
| SIGSYS    | Panggilan sistem tidak valid     |

## Sinyal Andal

| Nama         | Defined Pengguna |
| ------------ | ---------------- |
| SIGRTMIN     |                  |
| SIGRTMIN+1   |                  |
| SIGRTMIN+2   |                  |
| SIGRTMIN+3   |                  |
| SIGRTMIN+4   |                  |
| SIGRTMIN+5   |                  |
| SIGRTMIN+6   |                  |
| SIGRTMIN+7   |                  |
| SIGRTMIN+8   |                  |
| SIGRTMIN+9   |                  |
| SIGRTMIN+10  |                  |
| SIGRTMIN+11  |                  |
| SIGRTMIN+12  |                  |
| SIGRTMIN+13  |                  |
| SIGRTMIN+14  |                  |
| SIGRTMIN+15  |                  |
| SIGRTMAX-14  |                  |
| SIGRTMAX-13  |                  |
| SIGRTMAX-12  |                  |
| SIGRTMAX-11  |                  |
| SIGRTMAX-10  |                  |
| SIGRTMAX-9   |                  |
| SIGRTMAX-8   |                  |
| SIGRTMAX-7   |                  |
| SIGRTMAX-6   |                  |
| SIGRTMAX-5   |                  |
| SIGRTMAX-4   |                  |
| SIGRTMAX-3   |                  |
| SIGRTMAX-2   |                  |
| SIGRTMAX-1   |                  |
| SIGRTMAX     |                  |
