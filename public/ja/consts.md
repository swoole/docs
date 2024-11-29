# 常量

> ここにはすべての常量が含まれていません。すべての常量を確認したい場合は、[ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)を訪問またはインストールしてください。
## Swoole
常量 | 意義
---|---
SWOOLE_VERSION | 現在のSwooleのバージョン番号で、文字列型で、例えば1.6.0
## コンストラクタ引数
常量 | 意義
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | Baseモードを使用し、ビジネスコードがReactorプロセス内で直接実行されます
[SWOOLE_PROCESS](/learn?id=swoole_process) | プロセスモードを使用し、ビジネスコードがWorkerプロセス内で実行されます
## Socketタイプ
常量 | 意義
---|---
SWOOLE_SOCK_TCP | tcp socketを作成
SWOOLE_SOCK_TCP6 | tcp ipv6 socketを作成
SWOOLE_SOCK_UDP | udp socketを作成
SWOOLE_SOCK_UDP6 | udp ipv6 socketを作成
SWOOLE_SOCK_UNIX_DGRAM | unix dgram socketを作成
SWOOLE_SOCK_UNIX_STREAM | unix stream socketを作成
SWOOLE_SOCK_SYNC | 同期クライアント
## SSL暗号化方法
常量 | 意義
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD（デフォルトの暗号化方法） | -
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

> `SWOOLE_DTLSv1_METHOD`、`SWOOLE_DTLSv1_SERVER_METHOD`、`SWOOLE_DTLSv1_CLIENT_METHOD`はSwooleバージョンが`v4.5.0`以上で廃止されました。
## SSLプロトコル
常量 | 意義
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

> Swooleバージョンが`v4.5.4`以上で利用可能です。
## ログレベル
常量 | 意義
---|---
SWOOLE_LOG_DEBUG | デバッグログで、内部開発のデバッグにのみ使用されます
SWOOLE_LOG_TRACE |トレースログで、システム問題を追跡するために使用できます。デバッグログは慎重に設定されており、重要な情報を含むことがあります
SWOOLE_LOG_INFO | 通常の情報で、情報表示のためにのみ使用されます
SWOOLE_LOG_NOTICE | 通知情報で、システムにはいくつかの行動が存在する可能性があります。例えば、リスポーン、クローズなどです
SWOOLE_LOG_WARNING | 警告情報で、システムにはいくつかの問題が存在する可能性があります
SWOOLE_LOG_ERROR |エラー情報で、システムには重要なエラーが発生しており、即時解決が必要です
SWOOLE_LOG_NONE | ログ情報を閉じることに相当し、ログ情報は発生しません

> `SWOOLE_LOG_DEBUG`と`SWOOLE_LOG_TRACE`の2種類のログは、Swoole拡張をコンパイルする際に`--enable-debug-log`または`--enable-trace-log`を使用している場合にのみ使用できます。通常のバージョンでは、`log_level = SWOOLE_LOG_TRACE`を設定してもこのようなログを出すことはできません。
## トレースタグ

オンラインで実行されているサービスは、常に多くのリクエストが処理されており、下層で発生するログの量は非常に大きいです。`trace_flags`を使用してトレースログのタグを設定し、一部のトレースログだけを印刷します。`trace_flags`は`|`操作符を使用して複数のトレース項目を設定することをサポートしています。

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

下層では以下のトレース項目がサポートされており、`SWOOLE_TRACE_ALL`を使用してすべての項目をトレースすることができます：

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
