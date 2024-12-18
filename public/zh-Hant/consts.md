# 常數

!> 此處未包含所有常數，如需查看所有常數請訪問或安裝：[ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)


## Swoole


常數 | 作用
---|---
SWOOLE_VERSION | 當前Swoole的版本號，字符串類型，如1.6.0


## 构造方法參數


常數 | 作用
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | 使用Base模式，業務代碼在Reactor進程中直接執行
[SWOOLE_PROCESS](/learn?id=swoole_process) | 使用進程模式，業務代碼在Worker進程中執行


## Socket類型


常數 | 作用
---|---
SWOOLE_SOCK_TCP | 建立tcp socket
SWOOLE_SOCK_TCP6 | 建立tcp ipv6 socket
SWOOLE_SOCK_UDP | 建立udp socket
SWOOLE_SOCK_UDP6 | 建立udp ipv6 socket
SWOOLE_SOCK_UNIX_DGRAM | 建立unix dgram socket
SWOOLE_SOCK_UNIX_STREAM | 建立unix stream socket
SWOOLE_SOCK_SYNC | 同步客戶端


## SSL加密方法


常數 | 作用
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD（默認加密方法） | -
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

!> `SWOOLE_DTLSv1_METHOD`、`SWOOLE_DTLSv1_SERVER_METHOD`、`SWOOLE_DTLSv1_CLIENT_METHOD`已在 Swoole 版本 >= `v4.5.0` 中移除。


## SSL協議


常數 | 作用
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

!> Swoole版本 >= `v4.5.4` 可用


## 日志等級


常數 | 作用
---|---
SWOOLE_LOG_DEBUG | 調試日誌，僅作為內核開發調試使用
SWOOLE_LOG_TRACE | 跟蹤日誌，可用於追蹤系統問題，調試日誌是經過精心設置的，會攜帶關鍵性資訊
SWOOLE_LOG_INFO | 普通資訊，僅作為資訊展示
SWOOLE_LOG_NOTICE | 提示資訊，系統可能存在某些行為，如重啟、關閉
SWOOLE_LOG_WARNING | 警告資訊，系統可能存在某些問題
SWOOLE_LOG_ERROR | 錯誤資訊，系統發生了某些關鍵性的錯誤，需要即時解決
SWOOLE_LOG_NONE | 等同於關閉日誌資訊，日誌資訊不會抛出

!> `SWOOLE_LOG_DEBUG`和`SWOOLE_LOG_TRACE`兩種日誌，必須在編譯Swoole擴展時使用[--enable-debug-log](/environment?id=debug參數)或[--enable-trace-log](/environment?id=debug參數)後才可以使用。正常版本中即使設置了`log_level = SWOOLE_LOG_TRACE`也是無法打印此類日誌的。

## 跟蹤標籤

線上運行的服務，隨時都有大量請求在處理，底層拋出的日誌數量非常巨大。可使用的`trace_flags`設置跟蹤日誌的標籤，僅打印部分跟蹤日誌。`trace_flags`支持使用`|`或操作符設置多個跟蹤項。

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

底層支持的以下跟蹤項，可使用`SWOOLE_TRACE_ALL`表示跟蹤所有項目：

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
