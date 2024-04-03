# Error Code

You can use `swoole_last_error()` to get the current error code;

You can use `swoole_strerror(int $errno, 9);` to convert the Swoole underlying error code into textual error information;

```php
echo swoole_strerror(swoole_last_error(), 9) . PHP_EOL;
echo swoole_strerror(SWOOLE_ERROR_MALLOC_FAIL, 9) . PHP_EOL;
```
## Linux Error Code List

| C Name          | Value | Description                                  | Meaning                        |
| --------------- | ----- | -------------------------------------------- | ------------------------------ |
| Success         | 0     | Success                                      | Success                        |
| EPERM           | 1     | Operation not permitted                      | Operation not permitted        |
| ENOENT          | 2     | No such file or directory                    | No such file or directory      |
| ESRCH           | 3     | No such process                              | No such process                |
| EINTR           | 4     | Interrupted system call                      | Interrupted system call        |
| EIO             | 5     | I/O error                                    | I/O error                      |
| ENXIO           | 6     | No such device or address                    | No such device or address      |
| E2BIG           | 7     | Arg list too long                            | Arg list too long              |
| ENOEXEC         | 8     | Exec format error                            | Exec format error              |
| EBADF           | 9     | Bad file number                              | Bad file number                |
| ECHILD          | 10    | No child processes                           | No child processes             |
| EAGAIN          | 11    | Try again                                    | Try again                      |
| ENOMEM          | 12    | Out of memory                                | Out of memory                  |
| EACCES          | 13    | Permission denied                            | Permission denied              |
| EFAULT          | 14    | Bad address                                  | Bad address                    |
| ENOTBLK         | 15    | Block device required                        | Block device required          |
| EBUSY           | 16    | Device or resource busy                      | Device or resource busy        |
| EEXIST          | 17    | File exists                                  | File exists                    |
| EXDEV           | 18    | Cross-device link                            | Cross-device link              |
| ENODEV          | 19    | No such device                               | No such device                 |
| ENOTDIR         | 20    | Not a directory                              | Not a directory                |
| EISDIR          | 21    | Is a directory                               | Is a directory                 |
| EINVAL          | 22    | Invalid argument                             | Invalid argument               |
| ENFILE          | 23    | File table overflow                          | File table overflow            |
| EMFILE          | 24    | Too many open files                          | Too many open files            |
| ENOTTY          | 25    | Not a tty device                             | Not a tty device               |
| ETXTBSY         | 26    | Text file busy                               | Text file busy                 |
| EFBIG           | 27    | File too large                               | File too large                 |
| ENOSPC          | 28    | No space left on device                      | No space left on device        |
| ESPIPE          | 29    | Illegal seek                                 | Illegal seek                   |
| EROFS           | 30    | Read-only file system                        | Read-only file system          |
| EMLINK          | 31    | Too many links                               | Too many links                 |
| EPIPE           | 32    | Broken pipe                                  | Broken pipe                    |
| EDOM            | 33    | Math argument out of domain                  | Math argument out of domain    |
| ERANGE          | 34    | Math result not representable                | Math result not representable  |
| EDEADLK         | 35    | Resource deadlock would occur                | Resource deadlock would occur  |
| ENAMETOOLONG    | 36    | Filename too long                            | Filename too long              |
| ENOLCK          | 37    | No record locks available                    | No record locks available      |
| ENOSYS          | 38    | Function not implemented                     | Function not implemented       |
| ENOTEMPTY       | 39    | Directory not empty                          | Directory not empty            |
| ELOOP           | 40    | Too many symbolic links encountered          | Too many symbolic links encountered |
| EWOULDBLOCK     | 41    | Same as EAGAIN                               | Same as EAGAIN                 |
| ENOMSG          | 42    | No message of desired type                   | No message of desired type     |
| EIDRM           | 43    | Identifier removed                           | Identifier removed             |
| ECHRNG          | 44    | Channel number out of range                  | Channel number out of range    |
| EL2NSYNC        | 45    | Level 2 not synchronized                     | Level 2 not synchronized       |
| EL3HLT          | 46    | Level 3 halted                               | Level 3 halted                 |
| EL3RST          | 47    | Level 3 reset                                | Level 3 reset                  |
| ELNRNG          | 48    | Link number out of range                     | Link number out of range       |
| EUNATCH         | 49    | Protocol driver not attached                 | Protocol driver not attached   |
| ENOCSI          | 50    | No CSI structure available                   | No CSI structure available     |
| EL2HLT          | 51    | Level 2 halted                               | Level 2 halted                 |
| EBADE           | 52    | Invalid exchange                             | Invalid exchange               |
| EBADR           | 53    | Invalid request descriptor                   | Invalid request descriptor     |
| EXFULL          | 54    | Exchange full                                | Exchange full                  |
| ENOANO          | 55    | No anode                                     | No anode                       |
| EBADRQC         | 56    | Invalid request code                         | Invalid request code           |
| EBADSLT         | 57    | Invalid slot                                 | Invalid slot                   |
| EDEADLOCK       | 58    | Same as EDEADLK                              | Same as EDEADLK                |
| EBFONT          | 59    | Bad font file format                         | Bad font file format           |
- ENOSTR: Device not a stream
- ENODATA: No data available
- ETIME: Timer expired
- ENOSR: Out of streams resources
- ENONET: Machine is not on the network
- ENOPKG: Package not installed
- EREMOTE: Object is remote
- ENOLINK: Link has been severed
- EADV: Advertise error
- ESRMNT: Srmount error
- ECOMM: Communication error on send
- EPROTO: Protocol error
- EMULTIHOP: Multihop attempted
- EDOTDOT: RFS specific error
- EBADMSG: Not a data message
- EOVERFLOW: Value too large for defined data type
- ENOTUNIQ: Name not unique on network
- EBADFD: File descriptor in bad state
- EREMCHG: Remote address changed
- ELIBACC: Cannot access a needed shared library
- ELIBBAD: Accessing a corrupted shared library
- ELIBSCN: A .lib section in an .out is corrupted
- ELIBMAX: Linking in too many shared libraries
- ELIBEXEC: Cannot exec a shared library directly
- EILSEQ: Illegal byte sequence
- ERESTART: Interrupted system call should be restarted
- ESTRPIPE: Streams pipe error
- EUSERS: Too many users
- ENOTSOCK: Socket operation on non-socket
- EDESTADDRREQ: Destination address required
- EMSGSIZE: Message too long
- EPROTOTYPE: Protocol wrong type for socket
- ENOPROTOOPT: Protocol not available
- EPROTONOSUPPORT: Protocol not supported
- ESOCKTNOSUPPORT: Socket type not supported
- EOPNOTSUPP: Operation not supported on transport
- EPFNOSUPPORT: Protocol family not supported
- EAFNOSUPPORT: Address family not supported by protocol
- EADDRINUSE: Address already in use
- EADDRNOTAVAIL: Cannot assign requested address
- ENETDOWN: Network is down
- ENETUNREACH: Network is unreachable
- ENETRESET: Network dropped
- ECONNABORTED: Software caused connection
- ECONNRESET: Connection reset by
- ENOBUFS: No buffer space available
- EISCONN: Transport endpoint is already connected
- ENOTCONN: Transport endpoint is not connected
- ESHUTDOWN: Cannot send after transport endpoint shutdown
- ETOOMANYREFS: Too many references: cannot splice
- ETIMEDOUT: Connection timed
- ECONNREFUSED: Connection refused
- EHOSTDOWN: Host is down
- EHOSTUNREACH: No route to host
- EALREADY: Operation already
- EINPROGRESS: Operation now in
- ESTALE: Stale NFS file handle
| EUCLEAN         | 117   | Structure needs cleaning                     | 结构需要清洗                   |
| ENOTNAM         | 118   | Not a XENIX-named                            | 不是 XENIX 命名的              |
| ENAVAIL         | 119   | No XENIX semaphores                          | 没有 XENIX 信号量              |
| EISNAM          | 120   | Is a named type file                         | 是一个命名的文件类型           |
| EREMOTEIO       | 121   | Remote I/O error                             | 远程输入/输出错误              |
| EDQUOT          | 122   | Quota exceeded                               | 超出磁盘配额                   |
| ENOMEDIUM       | 123   | No medium found                              | 没有磁盘被发现                 |
| EMEDIUMTYPE     | 124   | Wrong medium type                            | 错误的媒体类型                 |
| ECANCELED       | 125   | Operation Canceled                           | 取消操作                       |
| ENOKEY          | 126   | Required key not available                   | 所需键不可用                   |
| EKEYEXPIRED     | 127   | Key has expired                              | 关键已过期                     |
| EKEYREVOKED     | 128   | Key has been revoked                         | 关键被撤销                     |
| EKEYREJECTED    | 129   | Key was rejected by service                  | 关键被拒绝服务                 |
| EOWNERDEAD      | 130   | Owner died                                   | 所有者死亡                     |
| ENOTRECOVERABLE | 131   | State not recoverable                        | 状态不可恢复                   |
| ERFKILL         | 132   | Operation not possible due to RF-kill        | 由于 RF-kill 而无法操作        |
| EHWPOISON       | 133   | Memory page has hardware error               | 分页硬件错误                   |
## Linux Error Code List :id=linux

| C Name          | Value | Description                                  | Meaning                        |
| --------------- | ----- | -------------------------------------------- | ------------------------------ |
| Success         | 0     | Success                                      | Success                        |
| EPERM           | 1     | Operation not permitted                      | Operation not permitted        |
| ENOENT          | 2     | No such file or directory                    | No such file or directory      |
| ESRCH           | 3     | No such process                              | No such process                |
| EINTR           | 4     | Interrupted system call                      | Interrupted system call        |
| EIO             | 5     | I/O error                                    | I/O error                      |
| ENXIO           | 6     | No such device or address                    | No such device or address      |
| E2BIG           | 7     | Arg list too long                            | Arg list too long              |
| ENOEXEC         | 8     | Exec format error                            | Exec format error              |
| EBADF           | 9     | Bad file number                              | Bad file number                |
| ECHILD          | 10    | No child processes                           | No child processes             |
| EAGAIN          | 11    | Try again                                    | Try again                      |
| ENOMEM          | 12    | Out of memory                                | Out of memory                  |
| EACCES          | 13    | Permission denied                            | Permission denied              |
| EFAULT          | 14    | Bad address                                  | Bad address                    |
| ENOTBLK         | 15    | Block device required                        | Block device required          |
| EBUSY           | 16    | Device or resource busy                      | Device or resource busy        |
| EEXIST          | 17    | File exists                                  | File exists                    |
| EXDEV           | 18    | Cross-device link                            | Cross-device link              |
| ENODEV          | 19    | No such device                               | No such device                 |
| ENOTDIR         | 20    | Not a directory                              | Not a directory                |
| EISDIR          | 21    | Is a directory                               | Is a directory                 |
| EINVAL          | 22    | Invalid argument                             | Invalid argument               |
| ENFILE          | 23    | File table overflow                          | File table overflow            |
| EMFILE          | 24    | Too many open files                          | Too many open files            |
| ENOTTY          | 25    | Not a tty device                             | Not a tty device               |
| ETXTBSY         | 26    | Text file busy                               | Text file busy                 |
| EFBIG           | 27    | File too large                               | File too large                 |
| ENOSPC          | 28    | No space left on device                      | No space left on device        |
| ESPIPE          | 29    | Illegal seek                                 | Illegal seek                   |
| EROFS           | 30    | Read-only file system                        | Read-only file system          |
| EMLINK          | 31    | Too many links                               | Too many links                 |
| EPIPE           | 32    | Broken pipe                                  | Broken pipe                    |
| EDOM            | 33    | Math argument out of domain                  | Math argument out of domain    |
| ERANGE          | 34    | Math result not representable                | Math result not representable  |
| EDEADLK         | 35    | Resource deadlock would occur                | Resource deadlock would occur  |
| ENAMETOOLONG    | 36    | Filename too long                            | Filename too long              |
| ENOLCK          | 37    | No record locks available                    | No record locks available      |
| ENOSYS          | 38    | Function not implemented                     | Function not implemented       |
| ENOTEMPTY       | 39    | Directory not empty                          | Directory not empty            |
| ELOOP           | 40    | Too many symbolic links encountered          | Too many symbolic links encountered |
| EWOULDBLOCK     | 41    | Same as EAGAIN                               | Same as EAGAIN                 |
| ENOMSG          | 42    | No message of desired type                   | No message of desired type     |
| EIDRM           | 43    | Identifier removed                           | Identifier removed             |
| ECHRNG          | 44    | Channel number out of range                  | Channel number out of range    |
| EL2NSYNC        | 45    | Level 2 not synchronized                     | Level 2 not synchronized       |
| EL3HLT          | 46    | Level 3 halted                               | Level 3 halted                 |
| EL3RST          | 47    | Level 3 reset                                | Level 3 reset                  |
| ELNRNG          | 48    | Link number out of range                     | Link number out of range       |
| EUNATCH         | 49    | Protocol driver not attached                 | Protocol driver not attached   |
| ENOCSI          | 50    | No CSI structure available                   | No CSI structure available     |
| EL2HLT          | 51    | Level 2 halted                               | Level 2 halted                 |
| EBADE           | 52    | Invalid exchange                             | Invalid exchange               |
| EBADR           | 53    | Invalid request descriptor                   | Invalid request descriptor     |
| EXFULL          | 54    | Exchange full                                | Exchange full                  |
| ENOANO          | 55    | No anode                                     | No anode                       |
| EBADRQC         | 56    | Invalid request code                         | Invalid request code           |
| EBADSLT         | 57    | Invalid slot                                 | Invalid slot                   |
| EDEADLOCK       | 58    | Same as EDEADLK                              | Same as EDEADLK                |
| EBFONT          | 59    | Bad font file format                         | Bad font file format           |
| ENOSTR          | 60    | Device not a stream                          | 设备不是字符流                 |
| ENODATA         | 61    | No data available                            | 无可用数据                     |
| ETIME           | 62    | Timer expired                                | 计时器过期                     |
| ENOSR           | 63    | Out of streams resources                     | 流资源溢出                     |
| ENONET          | 64    | Machine is not on the network                | 机器不上网                     |
| ENOPKG          | 65    | Package not installed                        | 没有安装软件包                 |
| EREMOTE         | 66    | Object is remote                             | 对象是远程的                   |
| ENOLINK         | 67    | Link has been severed                        | 联系被切断                     |
| EADV            | 68    | Advertise error                              | 广告的错误                     |
| ESRMNT          | 69    | Srmount error                                | srmount 错误                   |
| ECOMM           | 70    | Communication error on send                  | 发送时的通讯错误               |
| EPROTO          | 71    | Protocol error                               | 协议错误                       |
| EMULTIHOP       | 72    | Multihop attempted                           | 多跳尝试                       |
| EDOTDOT         | 73    | RFS specific error                           | RFS 特定的错误                 |
| EBADMSG         | 74    | Not a data message                           | 非数据消息                     |
| EOVERFLOW       | 75    | Value too large for defined data type        | 值太大,对于定义数据类型        |
| ENOTUNIQ        | 76    | Name not unique on network                   | 名不是唯一的网络               |
| EBADFD          | 77    | File descriptor in bad state                 | 文件描述符在坏状态             |
| EREMCHG         | 78    | Remote address changed                       | 远程地址改变了                 |
| ELIBACC         | 79    | Cannot access a needed shared library        | 无法访问必要的共享库           |
| ELIBBAD         | 80    | Accessing a corrupted shared library         | 访问损坏的共享库               |
| ELIBSCN         | 81    | A .lib section in an .out is corrupted       | 库段. out 损坏                 |
| ELIBMAX         | 82    | Linking in too many shared libraries         | 试图链接太多的共享库           |
| ELIBEXEC        | 83    | Cannot exec a shared library directly        | 不能直接执行一个共享库         |
| EILSEQ          | 84    | Illegal byte sequence                        | 无效的或不完整的多字节或宽字符 |
| ERESTART        | 85    | Interrupted system call should be restarted  | 应该重新启动中断的系统调用     |
| ESTRPIPE        | 86    | Streams pipe error                           | 流管错误                       |
| EUSERS          | 87    | Too many users                               | 用户太多                       |
| ENOTSOCK        | 88    | Socket operation on non-socket               | 套接字操作在非套接字上         |
| EDESTADDRREQ    | 89    | Destination address required                 | 需要目标地址                   |
| EMSGSIZE        | 90    | Message too long                             | 消息太长                       |
| EPROTOTYPE      | 91    | Protocol wrong type for socket               | socket 协议类型错误            |
| ENOPROTOOPT     | 92    | Protocol not available                       | 协议不可用                     |
| EPROTONOSUPPORT | 93    | Protocol not supported                       | 不支持的协议                   |
| ESOCKTNOSUPPORT | 94    | Socket type not supported                    | 套接字类型不受支持             |
| EOPNOTSUPP      | 95    | Operation not supported on transport         | 不支持的操作                   |
| EPFNOSUPPORT    | 96    | Protocol family not supported                | 不支持的协议族                 |
| EAFNOSUPPORT    | 97    | Address family not supported by protocol     | 协议不支持的地址               |
| EADDRINUSE      | 98    | Address already in use                       | 地址已在使用                   |
| EADDRNOTAVAIL   | 99    | Cannot assign requested address              | 无法分配请求的地址             |
| ENETDOWN        | 100   | Network is down                              | 网络瘫痪                       |
| ENETUNREACH     | 101   | Network is unreachable                       | 网络不可达                     |
| ENETRESET       | 102   | Network dropped                              | 网络连接丢失                   |
| ECONNABORTED    | 103   | Software caused connection                   | 软件导致连接中断               |
| ECONNRESET      | 104   | Connection reset by                          | 连接被重置                     |
| ENOBUFS         | 105   | No buffer space available                    | 没有可用的缓冲空间             |
| EISCONN         | 106   | Transport endpoint is already connected      | 传输端点已经连接               |
| ENOTCONN        | 107   | Transport endpoint is not connected          | 传输终点没有连接               |
| ESHUTDOWN       | 108   | Cannot send after transport endpoint shutdown| 传输后无法发送                 |
| ETOOMANYREFS    | 109   | Too many references: cannot splice           | 太多的参考                     |
| ETIMEDOUT       | 110   | Connection timed                             | 连接超时                       |
| ECONNREFUSED    | 111   | Connection refused                           | 拒绝连接                       |
| EHOSTDOWN       | 112   | Host is down                                 | 主机已关闭                     |
| EHOSTUNREACH    | 113   | No route to host                             | 没有主机的路由                 |
| EALREADY        | 114   | Operation already                            | 已运行                         |
| EINPROGRESS     | 115   | Operation now in                             | 正在运行                       |
| ESTALE          | 116   | Stale NFS file handle                        | 陈旧的 NFS 文件句柄            |
| EUCLEAN         | 117   | Structure needs cleaning                     | 结构需要清洗                   |
| ENOTNAM         | 118   | Not a XENIX-named                            | 不是 XENIX 命名的              |
| ENAVAIL         | 119   | No XENIX semaphores                          | 没有 XENIX 信号量              |
| EISNAM          | 120   | Is a named type file                         | 是一个命名的文件类型           |
| EREMOTEIO       | 121   | Remote I/O error                             | 远程输入/输出错误              |
| EDQUOT          | 122   | Quota exceeded                               | 超出磁盘配额                   |
| ENOMEDIUM       | 123   | No medium found                              | 没有磁盘被发现                 |
| EMEDIUMTYPE     | 124   | Wrong medium type                            | 错误的媒体类型                 |
| ECANCELED       | 125   | Operation Canceled                           | 取消操作                       |
| ENOKEY          | 126   | Required key not available                   | 所需键不可用                   |
| EKEYEXPIRED     | 127   | Key has expired                              | 关键已过期                     |
| EKEYREVOKED     | 128   | Key has been revoked                         | 关键被撤销                     |
| EKEYREJECTED    | 129   | Key was rejected by service                  | 关键被拒绝服务                 |
| EOWNERDEAD      | 130   | Owner died                                   | 所有者死亡                     |
| ENOTRECOVERABLE | 131   | State not recoverable                        | 状态不可恢复                   |
| ERFKILL         | 132   | Operation not possible due to RF-kill        | 由于 RF-kill 而无法操作        |
| EHWPOISON       | 133   | Memory page has hardware error               | 分页硬件错误                   |
## Swoole Error Code List

| Constants Name                                 | Value | Description                       |
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

...

| SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_ERROR        | 7101  | Http proxy handshake error        |
| SWOOLE_ERROR_HTTP_INVALID_PROTOCOL             | 7102  | Http invalid protocol             |
| SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_FAILED       | 7103  | Http proxy handshake failed       |
| SWOOLE_ERROR_HTTP_PROXY_BAD_RESPONSE           | 7104  | Http proxy bad response           |
| SWOOLE_ERROR_WEBSOCKET_BAD_CLIENT              | 8501  | Websocket bad client              |
| SWOOLE_ERROR_WEBSOCKET_BAD_OPCODE              | 8502  | Websocket bad opcode              |
| SWOOLE_ERROR_WEBSOCKET_UNCONNECTED             | 8503  | Websocket unconnected             |
| SWOOLE_ERROR_WEBSOCKET_HANDSHAKE_FAILED        | 8504  | Websocket handshake failed        |
| SWOOLE_ERROR_WEBSOCKET_PACK_FAILED             | 8505  | Websocket pack failed             |

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

| SWOOLE_ERROR_SERVER_WORKER_EXIT_TIMEOUT        | 9101  | Server worker exit timeout        |
| SWOOLE_ERROR_SERVER_WORKER_ABNORMAL_PIPE_DATA  | 9102  | Server worker abnormal pipe data  |
| SWOOLE_ERROR_SERVER_WORKER_UNPROCESSED_DATA    | 9103  | Server worker unprocessed data    |

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
