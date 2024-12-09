# Fehlercodes

Möglicherweise können Sie den aktuellen Fehlercode mit `swoole_last_error()` erhalten;

Möglicherweise können Sie den unteren Swoole-Fehlercode in einen textlichen Fehlerbericht umwandeln, indem Sie `swoole_strerror(int $errno, 9);` verwenden;

```php
echo swoole_strerror(swoole_last_error(), 9) . PHP_EOL;
echo swoole_strerror(SWOOLE_ERROR_MALLOC_FAIL, 9) . PHP_EOL;
```

## Liste der Linux-Fehlercodes :id=linux

| C Name          | Value | Beschreibung                                  | Bedeutung                           |
| --------------- | ----- | -------------------------------------------- | ------------------------------ |
| Success         | 0     | Success                                      | Erfolg                           |
| EPERM           | 1     | Operation not permitted                      | Betrieb nicht erlaubt             |
| ENOENT          | 2     | No such file or directory                    | Kein solcher Datei oder Verzeichnis |
| ESRCH           | 3     | No such process                              | Kein solcher Prozess              |
| EINTR           | 4     | Interrupted system call                      | Systemanruf unterbrochen          |
| EIO             | 5     | I/O error                                    | I/O-Fehler                        |
| ENXIO           | 6     | No such device or address                    | Kein solches Gerät oder Adresse    |
| E2BIG           | 7     | Arg list too long                            | Argumentliste zu lang             |
| ENOEXEC         | 8     | Exec format error                            | Ausführungsformat-Fehler          |
| EBADF           | 9     | Bad file number                              | Falsches Dateideskriptor           |
| ECHILD          | 10    | No child processes                           | Kein Kindprozess                 |
| EAGAIN          | 11    | Try again                                    | Versuchen Sie es erneut             |
| ENOMEM          | 12    | Out of memory                                | Ausgeht der Speicher              |
| EACCES          | 13    | Permission denied                            | Berechtigung verweigert            |
| EFAULT          | 14    | Bad address                                  | Falsche Adresse                  |
| ENOTBLK         | 15    | Block device required                        | Blockgerät erforderlich           |
| EBUSY           | 16    | Device or resource busy                      | Gerät oder Ressource beschäftigt   |
| EEXIST          | 17    | File exists                                  | Datei existiert                   |
| EXDEV           | 18    | Cross-device link                            | Unzulässiger grenzübergreifender Link |
| ENODEV          | 19    | No such device                               | Kein solches Gerät               |
| ENOTDIR         | 20    | Not a directory                              | Nicht ein Verzeichnis             |
| EISDIR          | 21    | Is a directory                               | Ist ein Verzeichnis               |
| EINVAL          | 22    | Invalid argument                             | Ungültiger Argument               |
| ENFILE          | 23    | File table overflow                          | Überlauf der Dateitabelle         |
| EMFILE          | 24    | Too many open files                          | Zu viele offene Dateien            |
| ENOTTY          | 25    | Not a tty device                             | Nicht ein TTY-Gerät               |
| ETXTBSY         | 26    | Text file busy                               | Textdatei beschäftigt             |
| EFBIG           | 27    | File too large                               | Datei zu groß                     |
| ENOSPC          | 28    | No space left on device                      | Keine Platz mehr auf Gerät         |
| ESPIPE          | 29    | Illegal seek                                 | Illegaler Suchvorgang              |
| EROFS           | 30    | Read-only file system                        | Nur Lesebetrieb supported        |
| EMLINK          | 31    | Too many links                               | Zu viele Links                   |
| EPIPE           | 32    | Broken pipe                                  | Rohrpipe gebrochen                |
| EDOM            | 33    | Math argument out of domain                  | mathematischer Argument außerhalb des Bereichs |
| ERANGE          | 34    | Math result not representable                | mathematischer Ergebnis nicht repräsentierbar |
| EDEADLK         | 35    | Resource deadlock would occur                | Ressourcen-Deadlock-Fehler       |
| ENAMETOOLONG    | 36    | Filename too long                            | Dateiname zu lang                 |
| ENOLCK          | 37    | No record locks available                    | Keine Lock-Recorde verfügbar      |
| ENOSYS          | 38    | Function not implemented                     | Funktion nicht implementiert      |
| ENOTEMPTY       | 39    | Directory not empty                          | Verzeichnis nicht leer            |
| ELOOP           | 40    | Too many symbolic links encountered          | Zu viele symbolische Links getroffen |
| EWOULDBLOCK     | 41    | Same as EAGAIN                               | Ebenso wie EAGAIN                |
| ENOMSG          | 42    | No message of desired type                   | Kein Message des gewünschten Typs |
| EIDRM           | 43    | Identifier removed                           | Identifikator entfernt            |
| ECHRNG          | 44    | Channel number out of range                  | Kanalnummer außerhalb des Bereichs |
| EL2NSYNC        | 45    | Level 2 not synchronized                     | Level 2 nicht synchronisiert       |
| EL3HLT          | 46    | Level 3 halted                               | Level 3 angehalten               |
| EL3RST          | 47    | Level 3 reset                                | Level 3 zurückgesetzt             |
| ELNRNG          | 48    | Link number out of range                     | Linknummer außerhalb des Bereichs |
| EUNATCH         | 49    | Protocol driver not attached                 | Protokoll-Treiber nicht angeschlossen |
| ENOCSI          | 50    | No CSI structure available                   | Keine CSI-Struktur verfügbar      |
| EL2HLT          | 51    | Level 2 halted                               | Level 2 angehalten               |
| EBADE           | 52    | Invalid exchange                             | Ungültige Austausch              |
| EBADR           | 53    | Invalid request descriptor                   | Ungültiges Anforderungsbeschreibungsfeld |
| EXFULL          | 54    | Exchange full                                | Austausch voll                    |
| ENOANO          | 55    | No anode                                     | Kein Anode                       |
| EBADRQC         | 56    | Invalid request code                         | Ungültiger Anforderungscode       |
| EBADSLT         | 57    | Invalid slot                                 | Ungültiger Slot                  |
| EDEADLOCK       | 58    | Same as EDEADLK                              | Ebenso wie EDEADLK                |
| EBFONT          | 59    | Bad font file format                         | Falsches Schriftartenformat       |
| ENOSTR          | 60    | Device not a stream                          | Gerät ist kein Stream             |
| ENODATA         | 61    | No data available                            | Kein Daten verfügbar              |
| ETIME           | 62    | Timer expired                                | Timer abgelaufen                 |
| ENOSR           | 63    | Out of streams resources                     | Ressourcen für Streams ausgegangen |
| ENONET          | 64    | Machine is not on the network                | Maschine ist nicht auf dem Netzwerk  |
| ENOPKG          | 65    | Package not installed                        | Paket nicht installiert            |
| EREMOTE         | 66    | Object is remote                             | Objekt ist Remote                |
| ENOLINK         | 67    | Link has been severed                        | Verbindung unterbrochen            |
| EADV            | 68    | Advertise error                              | Werbefehler                      |
| ESRMNT          | 69    | Srmount error                                | Srmount-Fehler                   |
| ECOMM           | 70    | Communication error on send                  | Kommunikationsfehler beim Senden |
| EPROTO          | 71    | Protocol error                               | Protokollfehler                   |
| EMULTIHOP       | 72    | Multihop attempted                           | Versucht von Multihop              |
| EDOTDOT         | 73    | RFS specific error                           | RFS-spezifischer Fehler            |
| EBADMSG         | 74    | Not a data message                           | Nicht ein Datenmessage             |
| EOVERFLOW       | 75    | Value too large for defined data type        | Wert zu groß für definierte Datentypen |
| ENOTUNIQ        | 76    | Name not unique on network                   | Name nicht einzigartig im Netzwerk  |
| EBADFD          | 77    | File descriptor in bad state                 | Falsches Dateideskriptor im Zustand |
| EREMCHG         | 78    | Remote address changed                       | Remotebewohner geändert           |
| ELIBACC         | 79    | Cannot access a needed shared library        | Kann keinen benötigten Teilungsbibliothek访问 |
| ELIBBAD         | 80    | Accessing a corrupted shared library         | Besucht eine beschädigte Teilungsbibliothek |
| ELIBSCN         | 81    | A .lib section in an .out is corrupted       | Ein .lib Abschnitt in einem .out ist beschädigt |
| ELIBMAX         | 82    | Linking in too many shared libraries         | Zu viele Teilungsbibliotheken verknüpft |
| ELIBEXEC        | 83    | Cannot exec a shared library directly        | Kann eine Teilungsbibliothek nicht direkt ausführen |
| EILSEQ          | 84    | Illegal byte sequence                        | Illegales Byte-Sequenz            |
| ERESTART        | 85    | Interrupted system call should be restarted  | Systemanruf unterbrochen und neu gestartet werden sollte |
| ESTRPIPE        | 86    | Streams pipe error                           | Fehler in der Pipelineschleife       |
| EUSERS          | 87    | Too many users                               | Zu viele Nutzer                  |
| ENOTSOCK        | 88    | Socket operation on non-socket               | Socket-Operation an Nicht-Socket   |
| EDESTADDRREQ    | 89    | Destination address required                | Zieladresse erforderlich           |
| EMSGSIZE        | 90    | Message too long                             | Nachricht zu lang                 |
| EPROTOTYPE      | 91    | Protocol wrong type for socket               | Falscher Protokolltyp für Socket   |
| ENOPROTOOPT     | 92    | Protocol not available                       | Protokoll nicht verfügbar           |
| EPROTONOSUPPORT | 93    | Protocol not supported                       | Protokoll nicht unterstützt         |
| ESOCKTNOSUPPORT | 94    | Socket type not supported                    | Socket-Typ nicht unterstützt      |
| EOPNOTSUPP      | 95    | Operation not supported on transport         | Operation nicht auf Transportunterstützung supported |
| EPFNOSUPPORT    | 96    | Protocol family not supported                | Protokollfamilie nicht unterstützt |
| EAFNOSUPPORT    | 97    | Address family not supported by protocol     | Protokoll unterstützt die Adressefamilie nicht |
| EADDRINUSE      | 98    | Address already in use                       | Adresse bereits in Verwendung     |
| EADDRNOTAVAIL   | 99    | Cannot assign requested address              | Kann die angeforderte Adresse nicht zuweisen |
| ENETDOWN        | 100   | Network is down                              | Netzwerk ist heruntergefahren       |
| ENETUNREACH     | 101   | Network is unreachable                       | Netzwerk ist nicht erschlossen       |
| ENETRESET       | 102   | Network dropped                              | Netzwerk Connection wurde abgebrochen |
| ECONNABORTED    | 103   | Software caused connection                   | Verbindung wurde durch Software abgebrochen |
| ECONNRESET      | 104   | Connection reset by                          | Verbindung wurde von            |
| ENOBUFS         | 105   | No buffer space available                    | Keine Pufferplatz mehr verfügbar     |
| EISCONN         | 106   | Transport endpoint is already connected      | Transport-Endpunkt ist bereits verbunden |
| ENOTCONN        | 107   | Transport endpoint is not connected          | Transport-Endpunkt ist nicht verbunden |
| ESHUTDOWN       | 108   | Cannot send after transport endpoint shutdown| Kann nach Schließen des Transport-Endpunkts nicht gesendet werden |
| ETOOMANYREFS    | 109   | Too many references: cannot splice           | Zu viele Referenzen: Nicht möglich, zu verschieben |
| ETIMEDOUT       | 110   | Connection timed                             | Verbindung wurde abgebrochen, da die Zeit abgelaufen ist |
| ECONNREFUSED    | 111   | Connection refused                           | Verbindung wurde abgelehnt |
| EHOSTDOWN       | 112   | Host is down                                 | Host ist heruntergefahren            |
| EHOSTUNREACH    | 113   | No route to host                             | Keine Route zum Host              |
| EALREADY        | 114   | Operation already                            | Operation ist bereits in Betrieb    |
| EINPROGRESS     | 115   | Operation now in                             | Operation läuft gerade            |
| ESTALE          | 116   | Stale NFS file handle                        | NFS-Dateihand handle ist veraltet  |
| EUCLEAN         | 117   | Structure needs cleaning                     | Struktur benötigt Reinigung        |
| ENOTNAM         | 118   | Not a XENIX-named                            | Nicht ein XENIX-benannter Name    |
| ENAVAIL         | 119   | No XENIX semaphores                          | Keine XENIX-Semaphore             |
| EISNAM          | 120   | Is a named type file                         | Ist ein benannter Dateityp         |
| EREMOTEIO       | 121   | Remote I/O error                             | Remotes I/O-Fehler               |
| EDQUOT          | 122   | Quota exceeded                               | Quoten überschritten              |
| ENOMEDIUM       | 123   | No medium found                              | Kein Medium gefunden              |
| EMEDIUMTYPE     | 124   | Wrong medium type                            | Falscher Mediumtyp                |
| ECANCELED       | 125   | Operation Canceled                           | Operation abgebrochen             |
| ENOKEY          | 126   | Required key not available                   | Erforderliche Taste nicht verfügbar |
| EKEYEXPIRED     | 127   | Key has expired                              | Taste ist abgelaufen              |
| EKEYREVOKED     | 128   | Key has been revoked                         | Taste wurde zurückgezogen         |
| EKEYREJECTED    | 129   | Key was rejected by service                  | Taste wurde von Service abgelehnt |
| EOWNERDEAD      | 130   | Owner died                                   | Besitzer ist verstorben             |
| ENOTRECOVERABLE | 131   | State not recoverable                        | Zustand kann nicht wiederhergestellt werden |
| ERFKILL         | 132   | Operation not possible due to RF-kill        | Operation nicht möglich aufgrund von RF-Kill |
| EHWPOISON       | 133   | Memory page has hardware error               | Speicherseite hat einen Hardware-Fehler |
## Swoole Fehlercode-Liste :id=swoole

| Konstante Name                                 | Wert  | Beschreibung                       |
| ---------------------------------------------- |-------|-----------------------------------|
| SWOOLE_ERROR_MALLOC_FAIL                       | 501   | Malloc fail                       |
| SWOOLE_ERROR_SYSTEM_CALL_FAIL                  | 502   | System call fail                  |
| SWOOLE_ERROR_PHP_FATAL_ERROR                   | 503   | PHP fatal error                   |
| SWOOLE_ERROR_NAME_zu_lang                    | 504   | Name zu lang                     |
| SWOOLE_ERROR_INVALID_PARAMS                    | 505   | Invalid params                    |
| SWOOLE_ERROR_QUEUE_FULL                        | 506   | Queue full                        |
| SWOOLE_ERROR_OPERATION_NOT_SUPPORT             | 507   | Operation not support             |
| SWOOLE_ERROR_PROTOCOL_ERROR                    | 508   | Protocol error                    |
| SWOOLE_ERROR_WRONG_OPERATION                   | 509   | Wrong operation                   |
| -                                              |       |                                   |
| SWOOLE_ERROR_FILE_NOT_EXIST                    | 700   | File not exist                    |
| SWOOLE_ERROR_FILE_zu_groß                     | 701   | File zu groß                     |
| SWOOLE_ERROR_FILE_EMPTY                        | 702   | File empty                        |
| SWOOLE_ERROR_DNSLOOKUP_DUPLICATE_REQUEST       | 710   | DNS Lookup duplicate request      |
| SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED          | 711   | DNS Lookup resolve failed         |
| SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT         | 712   | DNS Lookup resolve timeout        |
| SWOOLE_ERROR_DNSLOOKUP_UNSUPPORTED             | 713   | DNS Lookup unsupported            |
| SWOOLE_ERROR_DNSLOOKUP_NO_SERVER               | 714   | DNS Lookup no server              |
| SWOOLE_ERROR_BAD_IPV6_ADDRESS                  | 720   | Bad ipv6 address                  |
| SWOOLE_ERROR_UNREGISTERED_SIGNAL               | 721   | Unregistered signal               |
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
| SWOOLE_ERROR_SSL_NOT_RDY                     | 1011  | SSL not ready                     |
| SWOOLE_ERROR_SSL_CANNOT_USE_SENFILE            | 1012  | SSL cannot use senfile            |
| SWOOLE_ERROR_SSL_EMPTY_PEER_CERTIFICATE        | 1013  | SSL empty peer certificate        |
| SWOOLE_ERROR_SSL_VERIFY_FAILED                 | 1014  | SSL verify failed                 |
| SWOOLE_ERROR_SSL_BAD_CLIENT                    | 1015  | SSL bad client                    |
| SWOOLE_ERROR_SSL_BAD_PROTOCOL                  | 1016  | SSL bad protocol                  |
| SWOOLE_ERROR_SSL_RESET                         | 1017  | SSL reset                         |
| SWOOLE_ERROR_SSL_HANDSHAKE_FAILED              | 1018  | SSL handshake failed              |
| -                                              |       |                                   |
| SWOOLE_ERROR_PACKAGE_LENGTH_zu_groß          | 1201  | Package length zu groß          |
| SWOOLE_ERROR_PACKAGE_LENGTH_NOT_FOUND          | 1202  | Package length not found          |
| SWOOLE_ERROR_DATA_LENGTH_zu_groß             | 1203  | Data length zu groß             |
| -                                              |       |                                   |
| SWOOLE_ERROR_TASK_PACKAGE_zu_groß              | 2001  | Task package zu groß              |
| SWOOLE_ERROR_TASK_DISPATCH_FAIL                | 2002  | Task dispatch fail                |
| SWOOLE_ERROR_TASK_TIMEOUT                      | 2003  | Task timeout                      |
| -                                              |       |                                   |
| SWOOLE_ERROR_HTTP2_STREAM_ID_zu_groß           | 3001  | Http2 stream id zu groß           |
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
| -                                              |       |                                   |
| SWOOLE_ERROR_WEBSOCKET_BAD_CLIENT              | 8501  | Websocket bad client              |
| SWOOLE_ERROR_WEBSOCKET_BAD_OPCODE              | 8502  | Websocket bad opcode              |
| SWOOLE_ERROR_WEBSOCKET_UNCONNECTED             | 8503  | Websocket unconnected             |
| SWOOLE_ERROR_WEBSOCKET_HANDSHAKE_FAILED        | 8504  | Websocket handshake failed        |
| SWOOLE_ERROR_WEBSOCKET_PACK_FAILED             | 8505  | Websocket pack failed             |
| -                                              |       |                                   |
| SWOOLE_ERROR_SERVER_MUST_CREATED_BEFORE_CLIENT | 9001  | Server must created before client |
| SWOOLE_ERROR_SERVER_zu_viele_SOCKET            | 9002  | Server zu viele socket            |
| SWOOLE_ERROR_SERVER_WORKER_TERMINATED          | 9003  | Server worker terminated          |
| SWOOLE_ERROR_SERVER_INVALID_LISTEN_PORT        | 9004  | Server invalid listen port        |
| SWOOLE_ERROR_SERVER_zu_viele_LISTEN_PORT       | 9005  | Server too many listen port       |
| SWOOLE_ERROR_SERVER_PIPE_BUFFER_FULL           | 9006  | Server pipe buffer full           |
| SWOOLE_ERROR_SERVER_NO_IDLE_WORKER             | 9007  | Server no idle worker             |
| SWOOLE_ERROR_SERVER_ONLY_START_ONE             | 9008  | Server only start one             |
| SWOOLE_ERROR_SERVER_SEND_IN_MASTER             | 9009  | Server send in master             |
| SWOOLE_ERROR_SERVER_INVALID_REQUEST            | 9010  | Server invalid request            |
| SWOOLE_ERROR_SERVER_CONNECT_FAIL               | 9011  | Server connect fail               |
| SWOOLE_ERROR_SERVER_INVALID_COMMAND            | 9012  | Server invalid command            |
| SWOOLE_ERROR_SERVER_IS_NOT_REGULAR_FILE        | 9013  | Server is not regular file        |
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
