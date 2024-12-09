# Konstanten

!> Hier sind nicht alle Konstanten enthalten, um alle Konstanten anzuzeigen, besuchen Sie oder installieren Sie bitte: [ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)


## Swoole


Konstante | Funktion
---|---
SWOOLE_VERSION | Die aktuelle Swoole-Version, String-Typ, wie 1.6.0


## Konstruktorparameter


Konstante | Funktion
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | Verwenden Sie das Basis-Modell, Business-Code wird direkt im Reactor-Prozess ausgeführt
[SWOOLE_PROCESS](/learn?id=swoole_process) | Verwenden Sie das Prozess-Modell, Business-Code wird im Worker-Prozess ausgeführt


## Socket-Typen


Konstante | Funktion
---|---
SWOOLE_SOCK_TCP | Erstellen Sie einen tcp-Socket
SWOOLE_SOCK_TCP6 | Erstellen Sie einen tcp ipv6-Socket
SWOOLE_SOCK_UDP | Erstellen Sie einen udp-Socket
SWOOLE_SOCK_UDP6 | Erstellen Sie einen udp ipv6-Socket
SWOOLE_SOCK_UNIX_DGRAM | Erstellen Sie einen unix dgram-Socket
SWOOLE_SOCK_UNIX_STREAM | Erstellen Sie einen unix stream-Socket
SWOOLE_SOCK_SYNC | Synchroner Client


## SSL-Verschlüsselungsmethoden


Konstante | Funktion
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD (Standardverschlüsselungsmethode) | -
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

!> `SWOOLE_DTLSv1_METHOD`, `SWOOLE_DTLSv1_SERVER_METHOD` und `SWOOLE_DTLSv1_CLIENT_METHOD` wurden in Swoole-Versionen >= `v4.5.0` entfernt.


## SSL-Protokolle


Konstante | Funktion
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

!> Swoole-Versionen >= `v4.5.4` sind verfügbar


## Protokollloglevel


Konstante | Funktion
---|---
SWOOLE_LOG_DEBUG | Debug-Log, nur für Kernentwicklung und Debugging verwendet
SWOOLE_LOG_TRACE |追踪日志，可用于跟踪系统问题，调试日志是经过精心设置的，会携带关键性信息
SWOOLE_LOG_INFO | Normaler Informationslog, nur für die Anzeige von Informationen verwendet
SWOOLE_LOG_NOTICE | Hinweis-Log, das System könnte bestimmte Verhaltensweisen aufweisen, wie Neustart, Schließen
SWOOLE_LOG_WARNING | Warnungslog, das System könnte bestimmte Probleme aufweisen
SWOOLE_LOG_ERROR | Fehlermeldungslog, das System hat bestimmte kritische Fehler aufgetreten, die sofortige Lösung erfordern
SWOOLE_LOG_NONE | entspricht dem Schließen von Log-Informationen, Log-Informationen werden nicht ausgegeben

!> `SWOOLE_LOG_DEBUG` und `SWOOLE_LOG_TRACE` können nur verwendet werden, wenn beim Kompilieren der Swoole-Erweiterung die Optionen [--enable-debug-log](/environment?id=debug) oder [--enable-trace-log](/environment?id=debug) verwendet werden. Selbst wenn im normalen版本 `log_level = SWOOLE_LOG_TRACE` festgelegt ist, können diese Arten von Logs nicht gedruckt werden.

##追踪标签

Im Online-Betrieb gibt es ständig eine große Anzahl von Anfragen, die verarbeitet werden, und die Anzahl der unteren Ausgaben für logs ist sehr groß. Mit `trace_flags` können Sie Tags für die追踪日志 einstellen und nur einen Teil der追踪日志 drucken. `trace_flags` unterstützen die Verwendung von `|` oder dem Operator zum Einstellen mehrerer追踪项。

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

Die folgenden追踪项 werden unter der Basisunterstützung unterstützt, und `SWOOLE_TRACE_ALL` kann verwendet werden, um alle Projekte zu追踪en:

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
