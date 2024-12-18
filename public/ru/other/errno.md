# Ошибочные коды

Используйте `swoole_last_error()` для получения текущего ошибочного кода;

Используйте `swoole_strerror(int $errno, 9);` для преобразования базового ошибочного кода `Swoole` в текстовое описание ошибки;

```php
echo swoole_strerror(swoole_last_error(), 9) . PHP_EOL;
echo swoole_strerror(SWOOLE_ERROR_MALLOC_FAIL, 9) . PHP_EOL;
```

## Список ошибок Linux:id=linux

| C Название          | Значение | Описание                                  | Значение                           |
| --------------- | ----- | -------------------------------------------- | ------------------------------ |
| Success         | 0     | Успех                                      | Успех                           |
| EPERM           | 1     | Операция не разрешена                      | Операция не разрешена                     |
| ENOENT          | 2     | Нет такого файла или каталога                | Нет такого файла или каталога           |
| ESRCH           | 3     | Нет такого процесса                          | Нет такого процесса                 |
| EINTR           | 4     | Interrupted system call                      | Система call был прерван                 |
| EIO             | 5     | Ошибка ввода/вывода                         | Ошибка ввода/вывода                       |
| ENXIO           | 6     | Нет такого устройства или адреса             | Нет такого устройства или адреса           |
| E2BIG           | 7     | Аргумент списка слишком длинный               | Аргумент списка слишком длинный                   |
| ENOEXEC         | 8     | ОшибкаExec format                           | ОшибкаExec format                           |
| EBADF           | 9     | Плохой файловый номер                        | Плохой файловый номер                 |
| ECHILD          | 10    | Нетchild processes                           | Нетchild processes                           |
| EAGAIN          | 11    | Попытка снова                              | Ресурсы временно недоступны                 |
| ENOMEM          | 12    | Избыток памяти                              | Избыток памяти                       |
| EACCES          | 13    | Превышен доступ                              | Превышен доступ                       |
| EFAULT          | 14    | Плохой адрес                                  | Плохой адрес                     |
| ENOTBLK         | 15    | Требуется блоковое устройство                | Требуется блоковое устройство                |
| EBUSY           | 16    | Устройство или ресурс занят                  | Устройство или ресурс занят                   |
| EEXIST          | 17    | Файл существует                              | Файл существует                       |
| EXDEV           | 18    | Несоответствующий переход между устройствами| Несоответствующий переход между устройствами|
| ENODEV          | 19    | Нет такого устройства                         | Нет такого устройства                     |
| ENOTDIR         | 20    | Не директория                               | Не директория                       |
| EISDIR          | 21    | Это директория                               | Это директория                     |
| EINVAL          | 22    | Нелегальный аргумент                         | Нелегальный аргумент                     |
| ENFILE          | 23    | Переполнение файла таблицы                  | Переполнение файла таблицы                  |
| EMFILE          | 24    | Слишком много открытых файлов                | Слишком много открытых файлов                |
| ENOTTY          | 25    | Не tty устройство                             | Не tty устройство                  |
| ETXTBSY         | 26    | Файл текстовый и занят                      | Файл текстовый и занят                      |
| EFBIG           | 27    | Файл слишком большой                         | Файл слишком большой                       |
| ENOSPC          | 28    | Нет места на устройстве                      | Нет места на устройстве                      |
| ESPIPE          | 29    | Нелегальный поиск                           | Нелегальный поиск                           |
| EROFS           | 30    | Чтение-только файловая система                 | Чтение-только файловая система                 |
| EMLINK          | 31    | Слишком много ссылок                           | Слишком много ссылок                           |
| EPIPE           | 32    | Разорван трубка                              | Разорван трубка                              |
| EDOM            | 33    | Математический аргумент выходит за рамки области| Математический аргумент выходит за рамки области|
| ERANGE          | 34    | Математический результат не представим                   | Математический результат не представим                   |
| EDEADLK         | 35    | Ситуация сDeadlock ресурсов возникнет            | Ситуация сDeadlock ресурсов возникнет            |
| ENAMETOOLONG    | 36    | Название файла слишком длинное                | Название файла слишком длинное                |
| ENOLCK          | 37    | Нет доступных записей блокировки            | Нет доступных записей блокировки            |
| ENOSYS          | 38    | Функция не реализована                     | Функция не реализована                     |
| ENOTEMPTY       | 39    | Директория не пустая                          | Директория не пустая                          |
| ELOOP           | 40    | Слишком много символических ссылок            | Слишком много символических ссылок            |
| EWOULDBLOCK     | 41    | То же самое, что и EAGAIN                   | То же самое, что и EAGAIN                   |
| ENOMSG          | 42    | Нет сообщения ожидаемого типа               | Нет сообщения ожидаемого типа               |
| EIDRM           | 43    | Идентификатор удален                         | Идентификатор удален                         |
| ECHRNG          | 44    | Номер канала выходит за рамки диапазона         | Номер канала выходит за рамки диапазона         |
| EL2NSYNC        | 45    | Уровень 2 не синхронизирован                 | Уровень 2 не синхронизирован                 |
| EL3HLT          | 46    | Уровень 3 остановлен                         | Уровень 3 остановлен                         |
| EL3RST          | 47    | Уровень 3 сброшен                           | Уровень 3 сброшен                           |
| ELNRNG          | 48    | Номер связи выходит за рамки диапазона         | Номер связи выходит за рамки диапазона         |
| EUNATCH         | 49    | Протоколный драйвер не прикреплен                 | Протоколный драйвер не прикреплен                 |
| ENOCSI          | 50    | Нет доступной структуры CSI                 | Нет доступной структуры CSI                 |
| EL2HLT          | 51    | Уровень 2 остановлен                         | Уровень 2 остановлен                         |
| EBADE           | 52    | Неправильное обменное                             | Неправильное обменное                             |
| EBADR           | 53    | Неправильный запросовый дескриптор            | 'Неправильный запросовый дескриптор'            |
| EXFULL          | 54    | Обмен полон                                   | Обмен полон                                   |
| ENOANO          | 55    | Нет анода                                    | Нет анода                                    |
| EBADRQC         | 56    | Неправильный запросовый код                  | Неправильный запросовый код                  |
| EBADSLT         | 57    | Неправильный слот                             | Неправильный слот                             |
| EDEADLOCK       | 58    | То же самое, что и EDEADLK                  | То же самое, что и EDEADLK                  |
| EBFONT          | 59    | Неправильный формат шрифта файла            | Неправильный формат шрифта файла            |
| ENOSTR          | 60    | Устройство не является потоком                | Устройство не является потоком                |
| ENODATA         | 61    | Нет доступных данных                          | Нет доступных данных                          |
| ETIME           | 62    | Таймер истек                                 | Таймер истек                                 |
| ENOSR           | 63    | Избыток ресурсов потоков                     | Избыток ресурсов потоков                     |
| ENONET          | 64    | Машина не подключена к сети                  | Машина не подключена к сети                  |
| ENOPKG          | 65    | Пакет не установлен                           | Пакет не установлен                           |
| EREMOTE         | 66    | Объект находится в удаленном месте            | Объект находится в удаленном месте            |
| ENOLINK         | 67    | Соединение разорвано                         | Соединение разорвано                         |
| EADV            | 68    | Ошибка в рекламе                             | Ошибка в рекламе                             |
| ESRMNT          | 69    | Ошибка srmount                              | Ошибка srmount                              |
| ECOMM           | 70    | Ошибка связи при отправке                   | Ошибка связи при отправке                   |
| EPROTO          | 71    | Ошибка протокола                           | Ошибка протокола                           |
| EMULTIHOP       | 72    | Попытка многохоп                             | Попытка многохоп                             |
| EDOTDOT         | 73    | Ошибка RFS                                  | Ошибка RFS                                  |
| EBADMSG         | 74    | Не данные сообщение                           | Не данные сообщение                           |
| EOVERFLOW       | 75    | Значение слишком велико для заданного типа данных| Значение слишком велико для заданного типа данных|
| ENOTUNIQ        | 76    | Название не уникально в сети                | Название не уникально в сети                |
| EBADFD          | 77    | Файловый дескриптор в плохом состоянии         | Файловый дескриптор в плохом состоянии         |
| EREMCHG         | 78    | Изменился удаленный адрес                     | Изменился удаленный адрес                     |
| ELIBACC         | 79    | Невозможно получить доступ к необходимой shared library| Невозможно получить доступ к необходимой shared library|
| ELIBBAD         | 80    |ACCессing a corrupted shared library         |ACCессing a corrupted shared library         |
| ELIBSCN         | 81    | A .lib section in an .out is corrupted       | A .lib section in an .out is corrupted       |
| ELIBMAX         | 82    | Linking in too many shared libraries         | Linking in too many shared libraries         |
| ELIBEXEC        | 83    | Cannot exec a shared library directly        | Cannot exec a shared library directly        |
| EILSEQ          | 84    |Illegal byte sequence                        |Illegal byte sequence                        |
| ERESTART        | 85    |Interrupted system call should be restarted  |Interrupted system call should be restarted  |
| ESTRPIPE        | 86    | Streams pipe error                           | Streams pipe error                           |
| EUSERS          | 87    | Too many users                               | Too many users                               |
| ENOTSOCK        | 88    | Socket operation on non-socket               | Socket operation on non-socket               |
| EDESTADDRREQ    | 89    | Destination address required                | Destination address required                |
| EMSGSIZE        | 90    | Message too long                             | Message too long                             |
| EPROTOTYPE      | 91    | Protocol wrong type for socket               | Protocol wrong type for socket               |
| ENOPROTOOPT     | 92    | Protocol not available                       | Protocol not available                       |
| EPROTONOSUPPORT | 93    | Protocol not supported                       | Protocol not supported                       |
| ESOCKTNOSUPPORT | 94    | Socket type not supported                    | Socket type not supported                    |
| EOPNOTSUPP      | 95    | Operation not supported on transport         | Operation not supported on transport         |
| EPFNOSUPPORT    | 96    | Protocol family not supported                | Protocol family not supported                |
| EAFNOSUPPORT    | 97    | Address family not supported by protocol     | Address family not supported by protocol     |
| EADDRINUSE      | 98    | Address already in use                       | Address already in use                       |
| EADDRNOTAVAIL   | 99    | Cannot assign requested address              | Cannot assign requested address              |
| ENETDOWN        | 100   | Network is down                              | Network is down                              |
| ENETUNREACH     | 101   | Network is unreachable                       | Network is unreachable                       |
| ENETRESET       | 102   | Network dropped                              | Network dropped                              |
| ECONNABORTED    | 103   | Software caused connection                   | Software caused connection                   |
| ECONNRESET      | 104   | Connection reset by                          | Connection reset by                          |
| ENOBUFS         | 105   | No buffer space available                    | No buffer space available                    |
| EISCONN         | 106   | Transport endpoint is already connected      | Transport endpoint is already connected      |
| ENOTCONN        | 107   | Transport endpoint is not connected          | Transport endpoint is not connected          |
| ESHUTDOWN       | 108   | Cannot send after transport endpoint shutdown| Cannot send after transport endpoint shutdown|
| ETOOMANYREFS    | 109   | Too many references: cannot splice           | Too many references: cannot splice           |
| ETIMEDOUT       | 110   | Connection timed                             | Connection timed                             |
| ECONNREFUSED    | 111   | Connection refused                           | Connection refused                           |
| EHOSTDOWN       | 112   | Host is down                                 | Host is down                                 |
| EHOSTUNREACH    | 113   | No route to host                             | No route to host                             |
| EALREADY        | 114   | Operation already                            | Operation already                            |
| EINPROGRESS     | 115   | Operation now in                             | Operation now in                             |
| ESTALE          | 116   | Stale NFS file handle                        | Stale NFS file handle                        |
| EUCLEAN         | 117   | Structure needs cleaning                     | Structure needs cleaning                     |
| ENOTNAM         | 118   | Not a XENIX-named                            | Not a XENIX-named                            |
| ENAVAIL         | 119   | No XENIX semaphores                          | No XENIX semaphores                          |
| EISNAM          | 120   | Is a named type file                         | Is a named type file                         |
| EREMOTEIO       | 121   | Remote I/O error                             | Remote I/O error                             |
| EDQUOT          | 122   | Quota exceeded                               | Quota exceeded                               |
| ENOMEDIUM       | 123   | No medium found                              | No medium found                              |
| EMEDIUMTYPE     | 124   | Wrong medium type                            | Wrong medium type                            |
| ECANCELED       | 125   | Operation Canceled                           | Operation Canceled                           |
| ENOKEY          | 126   | Required key not available                   | Required key not available                   |
| EKEYEXPIRED     | 127   | Key has expired                              | Key has expired                              |
| EKEYREVOKED     | 128   | Key has been revoked                         | Key has been revoked                         |
| EKEYREJECTED    | 129   | Key was rejected by service                  | Key was rejected by service                  |
| EOWNERDEAD      | 130   | Owner died                                   | Owner died                                   |
| ENOTRECOVERABLE | 131   | State not recoverable                        | State not recoverable                        |
| ERFKILL         | 132   | Operation not possible due to RF-kill        | Operation not possible due to RF-kill        |
| EHWPOISON       | 133   | Memory page has hardware error               | Memory page has hardware error               |
## Список ошибок Swoole: id=swoole

| Название константы                                | Значение | Описание                       |
| ---------------------------------------------- |-------|-----------------------------------|
| SWOOLE_ERROR_MALLOC_FAIL                       | 501   | Malloc fail                       |
| SWOOLE_ERROR_SYSTEM_CALL_FAIL                  | 502   | System call fail                  |
| SWOOLE_ERROR_PHP_FATAL_ERROR                   | 503   | PHP fatal error                   |
| SWOOLE_ERROR_NAME_TOO_LONG                     | 504   | Name too long                     |
| SWOOLE_ERROR_INVALID_PARAMS                    | 505   | Invalid params                    |
| SWOOLE_ERROR_QUEUE_FULL                        | 506   | Queue full                        |
| SWOOLE_ERROR_OPERATION_NOT_SUPPORT             | 507   | Operation not support             |
| SWOOLE_ERROR_PROTOCOL_ERROR                    | 508   | Protocol error                    |
| SWOOLE_ERROR_WRONG_OPERATION                   | 509   | Wrong operation                   |
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
| -                                              |       |                                   |
| SWOOLE_ERROR_WEBSOCKET_BAD_CLIENT              | 8501  | Websocket bad client              |
| SWOOLE_ERROR_WEBSOCKET_BAD_OPCODE              | 8502  | Websocket bad opcode              |
| SWOOLE_ERROR_WEBSOCKET_UNCONNECTED             | 8503  | Websocket unconnected             |
| SWOOLE_ERROR_WEBSOCKET_HANDSHAKE_FAILED        | 8504  | Websocket handshake failed        |
| SWOOLE_ERROR_WEBSOCKET_PACK_FAILED             | 8505  | Websocket pack failed             |
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
