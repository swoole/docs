# Kode Error

Gunakan `swoole_last_error()` untuk mendapatkan kode error saat ini;

Gunakan `swoole_strerror(int $errno, 9);` untuk mengonversi kode error dasar Swoole menjadi teks pesan error;

```php
echo swoole_strerror(swoole_last_error(), 9) . PHP_EOL;
echo swoole_strerror(SWOOLE_ERROR_MALLOC_FAIL, 9) . PHP_EOL;
```

## Daftar Kode Error Linux :id=linux

| C Name          | Value | Description                                  | Arti                          |
| --------------- | ----- | -------------------------------------------- | ----------------------------- |
| Success         | 0     | Success                                      | Sukses                        |
| EPERM           | 1     | Operation not permitted                      | Operasi tidak diizinkan       |
| ENOENT          | 2     | No such file or directory                    | File atau direktori tidak ada |
| ESRCH           | 3     | No such process                              | Proses tidak ada              |
| EINTR           | 4     | Interrupted system call                      | Panggilan sistem terinterupsi |
| EIO             | 5     | I/O error                                    | Error I/O                     |
| ENXIO           | 6     | No such device or address                    | Device atau alamat tidak ada  |
| E2BIG           | 7     | Arg list too long                            | Daftar argumen terlalu panjang|
| ENOEXEC         | 8     | Exec format error                            | Error format eksekusi         |
| EBADF           | 9     | Bad file number                              | File descriptor rusak         |
| ECHILD          | 10    | No child processes                           | Tidak ada proses anak         |
| EAGAIN          | 11    | Try again                                    | Resource sementara tidak tersedia |
| ENOMEM          | 12    | Out of memory                                | Kehabisan memori              |
| EACCES          | 13    | Permission denied                            | Izin ditolak                  |
| EFAULT          | 14    | Bad address                                  | Alamat salah                  |
| ENOTBLK         | 15    | Block device required                        | Device block diperlukan       |
| EBUSY           | 16    | Device or resource busy                      | Device atau resource sibuk    |
| EEXIST          | 17    | File exists                                  | File sudah ada                |
| EXDEV           | 18    | Cross-device link                            | Tautan lintas device tidak valid |
| ENODEV          | 19    | No such device                               | Device tidak ada              |
| ENOTDIR         | 20    | Not a directory                              | Bukan direktori               |
| EISDIR          | 21    | Is a directory                               | Adalah direktori              |
| EINVAL          | 22    | Invalid argument                             | Argumen tidak valid           |
| ENFILE          | 23    | File table overflow                          | Tabel file overflow           |
| EMFILE          | 24    | Too many open files                          | Terlalu banyak file terbuka   |
| ENOTTY          | 25    | Not a tty device                             | Bukan device tty              |
| ETXTBSY         | 26    | Text file busy                               | File teks sibuk               |
| EFBIG           | 27    | File too large                               | File terlalu besar            |
| ENOSPC          | 28    | No space left on device                      | Tidak ada ruang di device     |
| ESPIPE          | 29    | Illegal seek                                 | Pencarian ilegal              |
| EROFS           | 30    | Read-only file system                        | Sistem file read-only         |
| EMLINK          | 31    | Too many links                               | Terlalu banyak tautan         |
| EPIPE           | 32    | Broken pipe                                  | Pipe rusak                    |
| EDOM            | 33    | Math argument out of domain                  | Argumen matematika di luar domain|
| ERANGE          | 34    | Math result not representable                | Hasil matematika tidak representatif |
| EDEADLK         | 35    | Resource deadlock would occur                | Deadlock resource             |
| ENAMETOOLONG    | 36    | Filename too long                            | Nama file terlalu panjang     |
| ENOLCK          | 37    | No record locks available                    | Tidak ada kunci tersedia      |
| ENOSYS          | 38    | Function not implemented                     | Fungsi tidak diimplementasikan|
| ENOTEMPTY       | 39    | Directory not empty                          | Direktori tidak kosong        |
| ELOOP           | 40    | Too many symbolic links encountered          | Terlalu banyak symbolic link  |
| EWOULDBLOCK     | 41    | Same as EAGAIN                               | Sama dengan EAGAIN            |
| ENOMSG          | 42    | No message of desired type                   | Tidak ada pesan tipe yang diinginkan|
| EIDRM           | 43    | Identifier removed                           | Identifier dihapus            |
| ECHRNG          | 44    | Channel number out of range                  | Nomor saluran di luar rentang |
| EL2NSYNC        | 45    | Level 2 not synchronized                     | Level 2 tidak sinkron         |
| EL3HLT          | 46    | Level 3 halted                               | Level 3 berhenti              |
| EL3RST          | 47    | Level 3 reset                                | Level 3 reset                 |
| ELNRNG          | 48    | Link number out of range                     | Jumlah tautan di luar rentang |
| EUNATCH         | 49    | Protocol driver not attached                 | Driver protokol tidak terpasang|
| ENOCSI          | 50    | No CSI structure available                   | Tidak ada struktur CSI        |
| EL2HLT          | 51    | Level 2 halted                               | Level 2 berhenti              |
| EBADE           | 52    | Invalid exchange                             | Tukar tidak valid             |
| EBADR           | 53    | Invalid request descriptor                   | Deskriptor request tidak valid|
| EXFULL          | 54    | Exchange full                                | Penuh                        |
| ENOANO          | 55    | No anode                                     | Tidak ada anode               |
| EBADRQC         | 56    | Invalid request code                         | Kode request tidak valid      |
| EBADSLT         | 57    | Invalid slot                                 | Slot tidak valid              |
| EDEADLOCK       | 58    | Same as EDEADLK                              | Sama dengan EDEADLK           |
| EBFONT          | 59    | Bad font file format                         | Format file font salah        |
| ENOSTR          | 60    | Device not a stream                          | Bukan stream                  |
| ENODATA         | 61    | No data available                            | Tidak ada data                |
| ETIME           | 62    | Timer expired                                | Timer kedaluwarsa             |
| ENOSR           | 63    | Out of streams resources                     | Resource stream habis         |
| ENONET          | 64    | Machine is not on the network                | Mesin tidak terhubung jaringan|
| ENOPKG          | 65    | Package not installed                        | Paket tidak terinstal         |
| EREMOTE         | 66    | Object is remote                             | Object jarak jauh             |
| ENOLINK         | 67    | Link has been severed                        | Tautan terputus               |
| EADV            | 68    | Advertise error                              | Error iklan                   |
| ESRMNT          | 69    | Srmount error                                | Error srmount                 |
| ECOMM           | 70    | Communication error on send                  | Error komunikasi saat kirim   |
| EPROTO          | 71    | Protocol error                               | Error protokol                |
| EMULTIHOP       | 72    | Multihop attempted                           | Percobaan multihop            |
| EDOTDOT         | 73    | RFS specific error                           | Error spesifik RFS            |
| EBADMSG         | 74    | Not a data message                           | Bukan pesan data              |
| EOVERFLOW       | 75    | Value too large for defined data type        | Nilai terlalu besar untuk tipe data |
| ENOTUNIQ        | 76    | Name not unique on network                   | Nama tidak unik di jaringan   |
| EBADFD          | 77    | File descriptor in bad state                 | File descriptor dalam keadaan rusak|
| EREMCHG         | 78    | Remote address changed                       | Alamat jarak jauh berubah     |
| ELIBACC         | 79    | Cannot access a needed shared library        | Tidak bisa akses shared library|
| ELIBBAD         | 80    | Accessing a corrupted shared library         | Shared library rusak          |
| ELIBSCN         | 81    | A .lib section in an .out is corrupted       | Bagian .lib di .out rusak     |
| ELIBMAX         | 82    | Linking in too many shared libraries         | Terlalu banyak shared library |
| ELIBEXEC        | 83    | Cannot exec a shared library directly        | Tidak bisa eksekusi shared library langsung|
| EILSEQ          | 84    | Illegal byte sequence                        | Urutan byte ilegal            |
| ERESTART        | 85    | Interrupted system call should be restarted  | Panggilan sistem harus di-restart|
| ESTRPIPE        | 86    | Streams pipe error                           | Error pipe stream             |
| EUSERS          | 87    | Too many users                               | Terlalu banyak pengguna       |
| ENOTSOCK        | 88    | Socket operation on non-socket               | Operasi socket di non-socket  |
| EDESTADDRREQ    | 89    | Destination address required                 | Alamat tujuan diperlukan      |
| EMSGSIZE        | 90    | Message too long                             | Pesan terlalu panjang         |
| EPROTOTYPE      | 91    | Protocol wrong type for socket               | Tipe protokol socket salah    |
| ENOPROTOOPT     | 92    | Protocol not available                       | Protokol tidak tersedia       |
| EPROTONOSUPPORT | 93    | Protocol not supported                       | Protokol tidak didukung       |
| ESOCKTNOSUPPORT | 94    | Socket type not supported                    | Tipe socket tidak didukung    |
| EOPNOTSUPP      | 95    | Operation not supported on transport         | Operasi tidak didukung        |
| EPFNOSUPPORT    | 96    | Protocol family not supported                | Keluarga protokol tidak didukung|
| EAFNOSUPPORT    | 97    | Address family not supported by protocol     | Alamat tidak didukung protokol|
| EADDRINUSE      | 98    | Address already in use                       | Alamat sudah digunakan        |
| EADDRNOTAVAIL   | 99    | Cannot assign requested address              | Tidak bisa memberikan alamat  |
| ENETDOWN        | 100   | Network is down                              | Jaringan mati                 |
| ENETUNREACH     | 101   | Network is unreachable                       | Jaringan tidak dapat dijangkau|
| ENETRESET       | 102   | Network dropped                              | Koneksi jaringan hilang       |
| ECONNABORTED    | 103   | Software caused connection                   | Software menyebabkan koneksi terputus|
| ECONNRESET      | 104   | Connection reset by                          | Koneksi di-reset              |
| ENOBUFS         | 105   | No buffer space available                    | Tidak ada buffer              |
| EISCONN         | 106   | Transport endpoint is already connected      | Endpoint sudah terhubung      |
| ENOTCONN        | 107   | Transport endpoint is not connected          | Endpoint tidak terhubung      |
| ESHUTDOWN       | 108   | Cannot send after transport endpoint shutdown| Tidak bisa kirim setelah shutdown|
| ETOOMANYREFS    | 109   | Too many references: cannot splice           | Terlalu banyak referensi      |
| ETIMEDOUT       | 110   | Connection timed                             | Koneksi timeout               |
| ECONNREFUSED    | 111   | Connection refused                           | Koneksi ditolak               |
| EHOSTDOWN       | 112   | Host is down                                 | Host mati                     |
| EHOSTUNREACH    | 113   | No route to host                             | Tidak ada rute ke host        |
| EALREADY        | 114   | Operation already                            | Operasi sudah berjalan        |
| EINPROGRESS     | 115   | Operation now in                             | Operasi sedang berlangsung    |
| ESTALE          | 116   | Stale NFS file handle                        | File handle NFS basi          |
| EUCLEAN         | 117   | Structure needs cleaning                     | Struktur perlu pembersihan    |
| ENOTNAM         | 118   | Not a XENIX-named                            | Bukan XENIX-named             |
| ENAVAIL         | 119   | No XENIX semaphores                          | Tidak ada semaphore XENIX     |
| EISNAM          | 120   | Is a named type file                         | File tipe bernama             |
| EREMOTEIO       | 121   | Remote I/O error                             | Error I/O jarak jauh          |
| EDQUOT          | 122   | Quota exceeded                               | Melebihi kuota disk           |
| ENOMEDIUM       | 123   | No medium found                              | Tidak ada media ditemukan     |
| EMEDIUMTYPE     | 124   | Wrong medium type                            | Tipe media salah              |
| ECANCELED       | 125   | Operation Canceled                           | Operasi dibatalkan            |
| ENOKEY          | 126   | Required key not available                   | Key diperlukan tidak tersedia |
| EKEYEXPIRED     | 127   | Key has expired                              | Key kedaluwarsa               |
| EKEYREVOKED     | 128   | Key has been revoked                         | Key dicabut                   |
| EKEYREJECTED    | 129   | Key was rejected by service                  | Key ditolak oleh service      |
| EOWNERDEAD      | 130   | Owner died                                   | Pemilik mati                  |
| ENOTRECOVERABLE | 131   | State not recoverable                        | Status tidak bisa dipulihkan  |
| ERFKILL         | 132   | Operation not possible due to RF-kill        | Operasi tidak bisa karena RF-kill|
| EHWPOISON       | 133   | Memory page has hardware error               | Halaman memori error hardware |

## Daftar Kode Error Swoole :id=swoole

| Constants Name                                 | Value | Description                       |
| ---------------------------------------------- | ----- | --------------------------------- |
| SWOOLE_ERROR_MALLOC_FAIL                       | 501   | Malloc fail                       |
| SWOOLE_ERROR_SYSTEM_CALL_FAIL                  | 502   | System call fail                  |
| SWOOLE_ERROR_PHP_FATAL_ERROR                   | 503   | PHP fatal error                   |
| SWOOLE_ERROR_NAME_TOO_LONG                     | 504   | Name too long                     |
| SWOOLE_ERROR_INVALID_PARAMS                    | 505   | Invalid params                    |
| SWOOLE_ERROR_QUEUE_FULL                        | 506   | Queue full                        |
| SWOOLE_ERROR_OPERATION_NOT_SUPPORT             | 507   | Operation not support             |
| SWOOLE_ERROR_PROTOCOL_ERROR                    | 508   | Protocol error                    |
| SWOOLE_ERROR_WRONG_OPERATION                   | 509   | Wrong operation                   |
| SWOOLE_ERROR_PHP_RUNTIME_NOTICE                | 510   | PHP runtime notice                |
| SWOOLE_ERROR_FOR_TEST                          | 511   | For test                          |
| SWOOLE_ERROR_NO_PAYLOAD                        | 550   | No payload                        |
| -                                              |       |                                   |
| SWOOLE_ERROR_UNDEFINED_BEHAVIOR                | 600   | Undefined behavior                |
| SWOOLE_ERROR_NOT_THREAD_SAFETY                 | 601   | Not thread safety                 |
| -                                              |       |                                   |
| SWOOLE_ERROR_FILE_NOT_EXIST                    | 700   | File not exist                    |
| SWOOLE_ERROR_FILE_TOO_LARGE                    | 701   | File too large                    |
| SWOOLE_ERROR_FILE_EMPTY                        | 702   | File empty                        |
| SWOOLE_ERROR_DNSLOOKUP_DUPLICATE_REQUEST       | 710   | DNS Lookup duplicate request      |
| SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED          | 711   | DNS Lookup resolve failed         |
| SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT         | 712   | DNS Lookup resolve timeout        |
| SWOOLE_ERROR_DNSLOOKUP_UNSUPPORTED             | 713   | DNS Lookup unsupported            |
| SWOOLE_ERROR_DNSLOOKUP_NO_SERVER               | 714   | DNS Lookup no server              |
| SWOOLE_ERROR_BAD_IPV6_ADDRESS                  | 720   | Bad ipv6 address                  |
| SWOOLE_ERROR_UNREGISTERED_SIGNAL               | 721   | Unregistered signal               |
| SWOOLE_ERROR_BAD_HOST_ADDR                     | 722   | Bad host addr                     |
| -                                              |       |                                   |
| SWOOLE_ERROR_EVENT_SOCKET_REMOVED              | 800   | Event socket removed              |
| -                                              |       |                                   |
| SWOOLE_ERROR_SESSION_CLOSED_BY_SERVER          | 1001  | Session closed by server          |
| SWOOLE_ERROR_SESSION_CLOSED_BY_CLIENT          | 1002  | Session closed by client          |
| SWOOLE_ERROR_SESSION_CLOSING                   | 1003  | Session closing                   |
| SWOOLE_ERROR_SESSION_CLOSED                    | 1004  | Session closed                    |
| SWOOLE_ERROR_SESSION_NOT_EXIST                 | 1005  | Session not exist                 |
| SWOOLE_ERROR_SESSION_INVALID_ID                | 1006  | Session invalid id                |
| SWOOLE_ERROR_SESSION_DISCARD_TIMEOUT_DATA      | 1007  | Session discard timeout data      |
| SWOOLE_ERROR_SESSION_DISCARD_DATA              | 1008  | Session discard data              |
| SWOOLE_ERROR_OUTPUT_BUFFER_OVERFLOW            | 1009  | Output buffer overflow            |
| SWOOLE_ERROR_OUTPUT_SEND_YIELD                 | 1010  | Output send yield                 |
| SWOOLE_ERROR_SSL_NOT_READY                     | 1011  | SSL not ready                     |
| SWOOLE_ERROR_SSL_CANNOT_USE_SENFILE            | 1012  | SSL cannot use senfile            |
| SWOOLE_ERROR_SSL_EMPTY_PEER_CERTIFICATE        | 1013  | SSL empty peer certificate        |
| SWOOLE_ERROR_SSL_VERIFY_FAILED                 | 1014  | SSL verify failed                 |
| SWOOLE_ERROR_SSL_BAD_CLIENT                    | 1015  | SSL bad client                    |
| SWOOLE_ERROR_SSL_BAD_PROTOCOL                  | 1016  | SSL bad protocol                  |
| SWOOLE_ERROR_SSL_RESET                         | 1017  | SSL reset                         |
| SWOOLE_ERROR_SSL_HANDSHAKE_FAILED              | 1018  | SSL handshake failed              |
| -                                              |       |                                   |
| SWOOLE_ERROR_PACKAGE_LENGTH_TOO_LARGE          | 1201  | Package length too large          |
| SWOOLE_ERROR_PACKAGE_LENGTH_NOT_FOUND          | 1202  | Package length not found          |
| SWOOLE_ERROR_DATA_LENGTH_TOO_LARGE             | 1203  | Data length too large             |
| SWOOLE_ERROR_PACKAGE_MALFORMED_DATA            | 1204  | Package malformed data            |
| -                                              |       |                                   |
| SWOOLE_ERROR_TASK_PACKAGE_TOO_BIG              | 2001  | Task package too big              |
| SWOOLE_ERROR_TASK_DISPATCH_FAIL                | 2002  | Task dispatch fail                |
| SWOOLE_ERROR_TASK_TIMEOUT                      | 2003  | Task timeout                      |
| -                                              |       |                                   |
| SWOOLE_ERROR_HTTP2_STREAM_ID_TOO_BIG           | 3001  | Http2 stream id too big           |
| SWOOLE_ERROR_HTTP2_STREAM_NO_HEADER            | 3002  | Http2 stream no header            |
| SWOOLE_ERROR_HTTP2_STREAM_NOT_FOUND            | 3003  | Http2 stream not found            |
| SWOOLE_ERROR_HTTP2_STREAM_IGNORE               | 3004  | Http2 stream ignore               |
| SWOOLE_ERROR_HTTP2_SEND_CONTROL_FRAME_FAILED   | 3005  | Http2 send control frame failed   |
| -                                              |       |                                   |
| SWOOLE_ERROR_AIO_BAD_REQUEST                   | 4001  | Aio bad request                   |
| SWOOLE_ERROR_AIO_CANCELED                      | 4002  | Aio canceled                      |
| SWOOLE_ERROR_AIO_TIMEOUT                       | 4003  | Aio timeout                       |
| -                                              |       |                                   |
| SWOOLE_ERROR_CLIENT_NO_CONNECTION              | 5001  | Client no connection              |
| -                                              |       |                                   |
| SWOOLE_ERROR_SOCKET_CLOSED                     | 6001  | Socket closed                     |
| SWOOLE_ERROR_SOCKET_POLL_TIMEOUT               | 6002  | Socket poll timeout               |
| -                                              |       |                                   |
| SWOOLE_ERROR_SOCKS5_UNSUPPORT_VERSION          | 7001  | Socks5 unsupport version          |
| SWOOLE_ERROR_SOCKS5_UNSUPPORT_METHOD           | 7002  | Socks5 unsupport method           |
| SWOOLE_ERROR_SOCKS5_AUTH_FAILED                | 7003  | Socks5 auth failed                |
| SWOOLE_ERROR_SOCKS5_SERVER_ERROR               | 7004  | Socks5 server error               |
| SWOOLE_ERROR_SOCKS5_HANDSHAKE_FAILED           | 7005  | Socks5 handshake failed           |
| -                                              |       |                                   |
| SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_ERROR        | 7101  | Http proxy handshake error        |
| SWOOLE_ERROR_HTTP_INVALID_PROTOCOL             | 7102  | Http invalid protocol             |
| SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_FAILED       | 7103  | Http proxy handshake failed       |
| SWOOLE_ERROR_HTTP_PROXY_BAD_RESPONSE           | 7104  | Http proxy bad response           |
| SWOOLE_ERROR_HTTP_CONFLICT_HEADER              | 7105  | Http conflict header              |
| SWOOLE_ERROR_HTTP_CONTEXT_UNAVAILABLE          | 7106  | Http context unavailable          |
| SWOOLE_ERROR_HTTP_COOKIE_UNAVAILABLE           | 7107  | Http cookie unavailable           |
| -                                              |       |                                   |
| SWOOLE_ERROR_WEBSOCKET_BAD_CLIENT              | 8501  | Websocket bad client              |
| SWOOLE_ERROR_WEBSOCKET_BAD_OPCODE              | 8502  | Websocket bad opcode              |
| SWOOLE_ERROR_WEBSOCKET_UNCONNECTED             | 8503  | Websocket unconnected             |
| SWOOLE_ERROR_WEBSOCKET_HANDSHAKE_FAILED        | 8504  | Websocket handshake failed        |
| SWOOLE_ERROR_WEBSOCKET_PACK_FAILED             | 8505  | Websocket pack failed             |
| SWOOLE_ERROR_WEBSOCKET_UNPACK_FAILED           | 8506  | Websocket unpack failed           |
| SWOOLE_ERROR_WEBSOCKET_INCOMPLETE_PACKET       | 8507  | Websocket incomplete packet       |
| -                                              |       |                                   |
| SWOOLE_ERROR_SERVER_MUST_CREATED_BEFORE_CLIENT | 9001  | Server must created before client |
| SWOOLE_ERROR_SERVER_TOO_MANY_SOCKET            | 9002  | Server too many socket            |
| SWOOLE_ERROR_SERVER_WORKER_TERMINATED          | 9003  | Server worker terminated          |
| SWOOLE_ERROR_SERVER_INVALID_LISTEN_PORT        | 9004  | Server invalid listen port        |
| SWOOLE_ERROR_SERVER_TOO_MANY_LISTEN_PORT       | 9005  | Server too many listen port       |
| SWOOLE_ERROR_SERVER_PIPE_BUFFER_FULL           | 9006  | Server pipe buffer full           |
| SWOOLE_ERROR_SERVER_NO_IDLE_WORKER             | 9007  | Server no idle worker             |
| SWOOLE_ERROR_SERVER_ONLY_START_ONE             | 9008  | Server only start one             |
| SWOOLE_ERROR_SERVER_SEND_IN_MASTER             | 9009  | Server send in master             |
| SWOOLE_ERROR_SERVER_INVALID_REQUEST            | 9010  | Server invalid request            |
| SWOOLE_ERROR_SERVER_CONNECT_FAIL               | 9011  | Server connect fail               |
| SWOOLE_ERROR_SERVER_INVALID_COMMAND            | 9012  | Server invalid command            |
| SWOOLE_ERROR_SERVER_IS_NOT_REGULAR_FILE        | 9013  | Server is not regular file        |
| SWOOLE_ERROR_SERVER_SEND_TO_WOKER_TIMEOUT      | 9014  | Server send to woker timeout      |
| SWOOLE_ERROR_SERVER_INVALID_CALLBACK           | 9015  | Server invalid callback           |
| SWOOLE_ERROR_SERVER_UNRELATED_THREAD           | 9016  | Server unrelated thread           |
| -                                              |       |                                   |
| SWOOLE_ERROR_SERVER_WORKER_EXIT_TIMEOUT        | 9101  | Server worker exit timeout        |
| SWOOLE_ERROR_SERVER_WORKER_ABNORMAL_PIPE_DATA  | 9102  | Server worker abnormal pipe data  |
| SWOOLE_ERROR_SERVER_WORKER_UNPROCESSED_DATA    | 9103  | Server worker unprocessed data    |
| -                                              |       |                                   |
| SWOOLE_ERROR_CO_OUT_OF_COROUTINE               | 10001 | Coroutine out of coroutine        |
| SWOOLE_ERROR_CO_HAS_BEEN_BOUND                 | 10002 | Coroutine has been bound          |
| SWOOLE_ERROR_CO_HAS_BEEN_DISCARDED             | 10003 | Coroutine has been discarded      |
| SWOOLE_ERROR_CO_MUTEX_DOUBLE_UNLOCK            | 10004 | Coroutine mutex double unlock     |
| SWOOLE_ERROR_CO_BLOCK_OBJECT_LOCKED            | 10005 | Coroutine block object locked     |
| SWOOLE_ERROR_CO_BLOCK_OBJECT_WAITING           | 10006 | Coroutine block object waiting    |
| SWOOLE_ERROR_CO_YIELD_FAILED                   | 10007 | Coroutine yield failed            |
| SWOOLE_ERROR_CO_GETCONTEXT_FAILED              | 10008 | Coroutine getcontext failed       |
| SWOOLE_ERROR_CO_SWAPCONTEXT_FAILED             | 10009 | Coroutine swapcontext failed      |
| SWOOLE_ERROR_CO_MAKECONTEXT_FAILED             | 10010 | Coroutine makecontext failed      |
| SWOOLE_ERROR_CO_IOCPINIT_FAILED                | 10011 | Coroutine iocpinit failed         |
| SWOOLE_ERROR_CO_PROTECT_STACK_FAILED           | 10012 | Coroutine protect stack failed    |
| SWOOLE_ERROR_CO_STD_THREAD_LINK_ERROR          | 10013 | Coroutine std thread link error   |
| SWOOLE_ERROR_CO_DISABLED_MULTI_THREAD          | 10014 | Coroutine disabled multi thread   |
| SWOOLE_ERROR_CO_CANNOT_CANCEL                  | 10015 | Coroutine cannot cancel           |
| SWOOLE_ERROR_CO_NOT_EXISTS                     | 10016 | Coroutine not exists              |
| SWOOLE_ERROR_CO_CANCELED                       | 10017 | Coroutine canceled                |
| SWOOLE_ERROR_CO_TIMEDOUT                       | 10018 | Coroutine timedout                |
| SWOOLE_ERROR_CO_SOCKET_CLOSE_WAIT              | 10019 | Coroutine socket close wait       |
