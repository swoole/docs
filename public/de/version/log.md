# Versionsgeschichte

Seit der Version `v1.5` wird eine strenge Versionsgeschichte geführt. Derzeit beträgt die durchschnittliche Iterationszeit ein großes Update alle sechs Monate und ein kleines Update alle `2-4` Wochen.

## Empfohlene PHP-Versionen

* 8.0
* 8.1
* 8.2
* 8.3

## Empfohlene Swoole-Versionen
`Swoole6.x` und `Swoole5.x`

Der Unterschied besteht darin, dass `v6.x` die aktive Iterationslinie ist, während `v5.x** nicht** eine aktive Iterationslinie ist und nur **BUGS** repariert.

!> Versionen ab `v4.x` können durch das Einstellen von [enable_coroutine](/server/setting?id=enable_coroutine) die Coroutine-Funktion deaktiviert werden, um sie zu einer Nicht-Coroutine-Version zu machen

## Versionstypen

* `alpha` Feature-Vorschauversion, bedeutet, dass die in der Entwicklungsplanung未完成的任务 abgeschlossen sind, um eine offene Vorschau zu gewähren, es kann viele **BUGS** geben
* `beta` Testversion, bedeutet, dass sie bereits für Entwicklungsumgebungen getestet werden kann, es können **BUGS** geben
* `rc[1-n]` Kandidatener Veröffentlichungsversion, bedeutet, dass sie in den Veröffentlichungszyklus eingetreten ist und eine breite Tests wird durchgeführt, in dieser Zeit können weiterhin **BUGS** entdeckt werden
* Keine Nachschrift bedeutet die stabile Version, bedeutet, dass diese Version fertig entwickelt ist und offiziell in den Einsatz genommen werden kann

##查看当前版本信息

```shell
php --ri swoole
```

## v6.0.0

### Neues Feature

- `Swoole` unterstützt jetzt den Mehr线程modus, der aktiviert werden kann, wenn PHP im ZTS (Zend Thread Safety) Modus läuft und beim Kompilieren von `Swoole` die Option `--enable-swoole-thread` angegeben wird.

- Neue Thread-Verwaltungsklasse `Swoole\Thread`. @matyhtf

- Neuer Thread-Schloss `Swoole\Thread\Lock`. @matyhtf

- Neue Thread-Atomzählung `Swoole\Thread\Atomic`, `Swoole\Thread\Atomic\Long`. @matyhtf

- Neue sichere并发容器`Swoole\Thread\Map`, `Swoole\Thread\ArrayList`, `Swoole\Thread\Queue`. @matyhtf

- Unterstützung für asynchrone Dateiverkehrsoperationen unter Verwendung von `iouring` als untere Ebene, Installation von `liburing` und Aktivierung von `--enable-iouring` beim Kompilieren von `Swoole`, asynchrone Operationen für `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize` werden durch `iouring` implementiert. @matyhtf @NathanFreeman
- Verbesserung der `Boost Context` Version auf 1.84. Jetzt können auch Longlong-CPU von Loongson Coroutinen nutzen. @NathanFreeman

### Bug Fixes

- Fix für das Problem, dass `Swoole` nicht über `pecl` installiert werden konnte. @remicollet

- Fix für das Problem, dass der `Swoole\Coroutine\FastCGI\Client` keine Keepalive-Einstellung für die Verbindung einrichten konnte. @NathanFreeman

- Fix für das Problem, bei dem ein Prozess ständig neu gestartet wurde, wenn die Anzahl der Anforderungsparameter die `max_input_vars` überschritt. @NathanFreeman

- Fix für unbekannte Probleme, die beim Einsatz von `Swoole\Event::wait()` in Coroutinen auftraten. @matyhtf

- Fix für das Problem, dass `proc_open` in Coroutinen keine PTY-Unterstützung bot. @matyhtf

- Fix für das Problem, dass bei Verwendung von `pdo_sqlite` in PHP 8.3 eine Segfault auftrat. @NathanFreeman

- Fix für unnötige Warnungen beim Kompilieren von `Swoole`. @Appla @NathanFreeman

- Fix für das Problem, dass ein Fehler auftrat, wenn `STDOUT/STDERR` bereits geschlossen waren und die Funktion `zend_fetch_resource2_ex` aufgerufen wurde. @Appla @matyhtf

- Fix für eine ungültige `set_tcp_nodelay` Konfiguration. @matyhtf

- Fix für das Problem, das gelegentlich beim Dateiaufbau auf einen unerreichbaren Branch auftrat. @NathanFreeman

- Fix für das Problem, dass das Einstellen einer `dispatch_func` dazu führte, dass ein Fehler in der PHP-Kernebene auftrat. @NathanFreeman

- Fix für das Problem, dass AC_PROG_CC_C99 in Autoconf-Versionen ab 2.70 veraltet ist. @petk

- Fangen von Ausnahmen, wenn das Erstellen eines Threads fehlschlägt. @matyhtf

- Fix für das Problem, dass `_tsrm_ls_cache` nicht definiert war. @jingjingxyk
- Fix für einen tödlichen Fehler, der beim Kompilieren mit `GCC 14` auftrat. @remicollet

### Kernoptimierungen

- Entfernung unnötiger Überprüfungen von `socket structs`. @petk

- Verbesserung der Swoole Library. @deminy

- Unterstützung für den HTTP-Statuscode 451 in `Swoole\Http\Response`. @abnegate

- Synchronisierung der `file`-Operationscode für verschiedene PHP-Versionen. @NathanFreeman

- Synchronisierung der `pdo`-Operationscode für verschiedene PHP-Versionen. @NathanFreeman

- Optimierung des Codes für `Socket::ssl_recv()`. @matyhtf

- Optimierung von config.m4, einige Konfigurationen können über `pkg-config` die Position der Abhängigkeitsbibliotheken festgelegt werden. @NathanFreeman

- Optimierung des Problems beim `Anlagen von Anforderungshüten` mit dynamischen Arrays. @NathanFreeman

- Optimierung des Lebenszyklus von Dateideskriptoren `fd` im Mehr线程modus. @matyhtf

- Optimierung einiger grundlegender Coroutine-Logiken. @matyhtf

### Ab废弃

- Unterstützung für `PHP 8.0` wird eingestellt.

- Unterstützung für die Coroutine-Clients `Swoole\Coroutine\MySQL`, `Swoole\Coroutine\Redis` und `Swoole\Coroutine\PostgreSQL` wird eingestellt.
- Behebung des Fehlers mit ungültigem errno nach `Server::Manager::wait()`.
- Behebung eines Schreibfehlers bei HTTP2.



### Optimierungen

- Optimierung der Leistung des HTTP-Servers.
- Hinzufügen von `CLOSE_SERVICE_RESTART`, `CLOSE_TRY_AGAIN_LATER`, `CLOSE_BAD_GATEWAY` als gültige Schließgründe für WebSockets


## v5.1.1



### Bugbehebungen

- Behebung des Memory-Leak-Problems beim `http-coroutine-client`.

- Behebung des Problems, dass `pdo_odbc` nicht koordiniert werden kann.

- Behebung des Ausführungsfehlers bei `socket_import_stream()`.

- Behebung des Problems, dass `Context::parse_multipart_data()` leere Anforderungskörper nicht verarbeiten kann.

- Behebung des Problems, dass die Parameter für den `PostgreSQL-coroutine-client` nicht wirksam sind.

- Behebung des Crash-Problems beim Destrukturieren von `curl`.

- Behebung des Inkompatibilitätsproblems zwischen `Swoole5.x` und der neuen Version von `xdebug`.

- Behebung des Problems, bei dem während des Prozesses der automatischen Klassenladung eine Koordinationsumschaltung verursacht, was zu einem Hinweis auf eine nicht vorhandene Klasse führt.
- Behebung des Problems beim Kompilieren von `swoole` unter `OpenBSD`.


## v5.1.0




### Neue Funktionen

- Unterstützung für die Koordination von `pdo_pgsql` hinzugefügt

- Unterstützung für die Koordination von `pdo_odbc` hinzugefügt

- Unterstützung für die Koordination von `pdo_oci` hinzugefügt

- Unterstützung für die Koordination von `pdo_sqlite` hinzugefügt
- Konfigurationsmöglichkeiten für Poolverbindungen für `pdo_pgsql`, `pdo_odbc`, `pdo_oci` und `pdo_sqlite` hinzugefügt



### Verbesserungen
- Verbesserung der Leistung von `Http\Server`, bei giới hạnten Situationen bis zu `60%`



### Behebungen

- Behebung des Memory-Leak-Problems, das durch den `WebSocket`-coroutine-client bei jedem Request verursacht wird

- Behebung des Problems, bei dem der `http-coroutine-server` sich elegant verabschiedet und die Clientverbindungen nicht beendet, was zu einem Verbleib der Clients führt

- Behebung des Problems, bei dem das Kompilieren mit der Option `--enable-thread-context` dazu führt, dass `Process::signal()` nicht funktioniert

- Behebung des Problems mit fehlerhaften Verbindungszählungen, wenn der Prozess unter `SWOOLE_BASE` nicht normal beendet wird

- Behebung des Fehlers bei der Signatur der `stream_select()`-Funktion

- Behebung des Problems mit der Case-Sensitivität von MIME-Informationen von Dateien

- Behebung des Schreibfehlers bei `Http2\Request::$usePipelineRead`, der in einer PHP8.2 Umgebung zu einer Warnung führt

- Behebung des Memory-Leak-Problems unter `SWOOLE_BASE`

- Behebung des Memory-Leak-Problems, das durch das Einstellen einer Cookie-Ablaufzeit mit `Http\Response::cookie()` verursacht wird

- Behebung des Connection-Leak-Problems unter `SWOOLE_BASE`




### Kern

- Behebung des Problems mit der Signatur der `php_url_encode`-Funktion von swoole unter PHP8.3

- Behebung des Problems mit den Einheitentestoptionen

- Optimierung und Refactoring des Codes

- Kompatibilität mit PHP8.3
- Nichtunterstützung für das Kompilieren auf 32-Bit-Betriebssystemen


## v5.0.3




### Verbesserungen

- Hinzufügen der `--with-nghttp2_dir`-Option, um die Nutzung der `nghttp2`-Bibliothek im System zu ermöglichen

- Unterstützung für Optionen, die mit字节länge oder Größe zusammenhängen

- Hinzufügen der `Process\Pool::sendMessage()`-Funktion

- Unterstützung von `max-age` für `Http\Response:cookie()`




### Behebungen

- Behebung des Memory-Leak-Problems durch das Ereignis `Server task/pipemessage/finish`




### Kern

- Konflikte mit `http`-Antwortheadern werden nicht mehr zu Fehlern führen
- Schließungen von `Server`-Verbindungen werden nicht mehr zu Fehlern führen


## v5.0.2




### Verbesserungen

- Unterstützung für die Konfiguration der Standardwerte für `http2`

- Unterstützung für `xdebug` Version 8.1 oder höher

- Überarbeitung des nativen curls, um Unterstützung für cURL-Handle mit mehreren Sockets zu bieten, wie zum Beispiel cURL FTP

- Hinzufügen des `who` Parameters zu `Process::setPriority/getPriority`

- Hinzufügen der `Coroutine\Socket::getBoundCid()`-Methode

- Anpassung des Standardwerts des `length` Parameters für die Methoden `Coroutine\Socket::recvLine/recvWithBuffer` auf `65536`

- Überarbeitung der 跨协程退出特性, um sichereres Memory-Freisetzen zu gewährleisten und Totalschlagprobleme zu lösen

- Hinzufügen der `socket` Eigenschaft zu `Coroutine\Client`, `Coroutine\Http\Client` und `Coroutine\Http2\Client`, um direkte Operationen auf `socket` Ressourcen zu ermöglichen

- Unterstützung von `Http\Server` für das Senden leerer Dateien an `http2`-Clients

- Unterstützung für den eleganten Restart von `Coroutine\Http\Server`. Wenn der Server geschlossen wird, werden die Clientverbindungen nicht mehr zwangsläufig geschlossen, es wird nur auf neue Anforderungen gewartet

- Hinzufügen von `pcntl_rfork` und `pcntl_sigwaitinfo` zur Liste der unsicheren Funktionen und Schließen dieser Funktionen beim Start des Koordinationsk容器

- Überarbeitung des Prozessmanagermanagers im `SWOOLE_BASE`-Modus, so dass Schließ- und Nachladebehavior mit `SWOOLE_PROCESS` übereinstimmt


## v5.0.1




### Verbesserungen

- Unterstützung für `PHP-8.2`, Verbesserung der Koordination von Ausnahmen, Kompatibilität mit `ext-soap`

- Hinzufügen der Unterstützung für LOBs für den `pgsql`-coroutine-client

- Verbesserung des `websocket`-clients, Upgrade der Kopfzeile mit `websocket` anstelle von `=`

- Optimierung des `http client`, bei Empfang von `connection close` vom Server die `keep-alive`-Option deaktiviert

- Optimierung des `http client`, bei Fehlen einer Komprimierungsbibliothek das Hinzufügen der `Accept-Encoding`-Kopfzeile unterbunden

- Verbesserung der Debugging-Informationen, unter `PHP-8.2` Passwörter als sensibles Parameter festgelegt

- Stärkung von `Server::taskWaitMulti()`, in einer Koordinationsumgebung nicht blockierend

- Optimierung der Logging-Funktionen, bei Fehlgeschlagenen Schreibversuchen auf das Logfile nicht mehr auf den Bildschirm ausgegeben




### Behebungen

- Behebung des Parameterkompatibilitätsproblems für `Coroutine::printBackTrace()` und `debug_print_backtrace()`

- Behebung der Unterstützung für Socketressourcen bei `Event::add()`

- Behebung des Kompilierungsfehlers bei Fehlen von `zlib`

- Behebung des Crash-Problems bei der Entschlüsselung unerwarteter Zeichenfolgen bei der Analyse von Serveraufgaben

- Behebung des Problems, bei dem Timer mit einer Dauer von weniger als `1ms` zwangsläufig auf `0` gesetzt wird

- Behebung des Crash-Problems, das beim Hinzufügen von Spalten vor der Verwendung von `Table::getMemorySize()` auftritt

- Änderung des Namens des Ablaufparameters für die `expires`-Option in der `Http\Response::setCookie()`-Methode


## v5.0.0




### Neue Funktionen

- Hinzufügen der `max_concurrency`-Option für den `Server`

- Hinzufügen der `max_retries`-Option für den `Coroutine\Http\Client`

- Hinzufügen der globalen `name_resolver`-Option. Hinzufügen der `upload_max_filesize`-Option für den `Server`

- Hinzufügen der `Coroutine::getExecuteTime()`-Methode

- Hinzufügen der `SWOOLE_DISPATCH_CONCURRENT_LB`-`dispatch_mode` für den `Server`

- Stärkung des Typensystems, Hinzufügen von Typen zu allen Funktionen, Parametern und Rückgängen

- Optimierung der Fehlbehandlung, alle Konstruktoren werfen Ausnahmen bei Scheitern

- Anpassung des Standardmodells für den `Server`, default ist jetzt das `SWOOLE_BASE`-Modell
- Verschiebung des `pgsql`-coroutine-clients in die Kernbibliothek. Enthält alle `bug`-Fixes aus der `4.8.x`-分支




### Entfernung

- Entfernung der PSR-0-Stil-Klassennamen

- Entfernung der automatischen Hinzufügung von `Event::wait()` in Schließfunktionen

- Entfernung der Aliase für `Server::tick/after/clearTimer/defer`

- Entfernung von `--enable-http2/--enable-swoole-json`, 默认 aktiviert




### Ab废弃

- Die默认en coroutine-Clients `Coroutine\Redis` und `Coroutine\MySQL` sind ab废弃


## v4.8.13
### Verbessern

- Umstrukturierung des nativen cURL um Unterstützung für cURL-Handles mit mehreren Sockets zu ermöglichen, wie zum Beispiel cURL FTP Protokoll

- Unterstützung für manuelles Einstellen von `http2` Einstellungen

- Verbesserung des `WebSocket-Clients`, Upgrade-Header beinhaltet jetzt `websocket` anstelle von `equal`

- Optimierung des HTTP-Clients, `keep-alive` wird deaktiviert, wenn der Server die Verbindung schließt

- Verbesserung der Debugging-Informationen, Passwörter werden unter PHP-8.2 als sensibles Parameter festgelegt

- Unterstützung für `HTTP Range Requests`




### Beheben

- Behebung der Kompatibilitätsprobleme mit den Parametern von `Coroutine::printBackTrace()` und `debug_print_backtrace()`

- Behebung des Problems mit der falschen Parsing-Länge des `WebSocket` Servers, wenn gleichzeitig `HTTP2` und `WebSocket` Protokoll aktiviert sind

- Behebung des Memory-Leak-Problems bei `Server::send()`, `Http\Response::end()`, `Http\Response::write()` und `WebSocket/Server::push()` bei Auftreten von `send_yield`

- Behebung des Crashing-Problems, das auftritt, wenn `Table::getMemorySize()` vor dem Hinzufügen von Spalten verwendet wird.


## v4.8.12




### Verbessern

- Unterstützung für PHP8.2

- Die `Event::add()` Funktion unterstützt jetzt `sockets resources`

- Die `Http\Client::sendfile()` Funktion unterstützt Dateien über 4GB

- Die `Server::taskWaitMulti()` Funktion unterstützt die Coroutine-Umwelt




### Beheben

- Behebung des Fehlers, der抛出, wenn ein falsches `multipart body` empfangen wird

- Behebung des Fehlers, der verursacht wird, wenn die Timeoutzeit des Timers weniger als `1ms` beträgt

- Behebung des Deadlock-Problems, das durch einen vollen Datenträger verursacht wird


## v4.8.11




### Verbessern

- Unterstützung für das `Intel CET` Sicherheits防御机制

- Hinzufügen der `Server::$ssl` Eigenschaft

- Beim编译 von `swoole` mit `pecl` wurde die `enable-cares` Eigenschaft hinzugefügt

- Umstrukturierung des `multipart_parser` Interpreters




### Beheben

- Behebung des Segmentierungsfehlers, der durch persistente Verbindungen mit `pdo` verursacht wird

- Behebung des Segmentierungsfehlers, der durch die Verwendung von Coroutinen in der Destruktorenfunktion verursacht wird

- Behebung der falschen Fehlermeldung von `Server::close()`


## v4.8.10


### Beheben



- Wenn der Timeoutparameter von `stream_select` weniger als `1ms` beträgt, wird er auf `0` zurückgesetzt

- Behebung des Problems, dass das Hinzufügen von `-Werror=format-security` bei der Kompilierung zum编译失败 führt

- Behebung des Segmentierungsfehlers, der durch die Verwendung von `curl` beim Start des `Swoole\Coroutine\Http\Server` verursacht wird


## v4.8.9


### Verbessern



- Unterstützung für die `http_auto_index` Option unter `Http2` Servern


### Beheben



- Optimierung des `Cookie` Parsers, Unterstützung für das Ein传递 `HttpOnly` Option

- Behebung von #4657, Hook `socket_create` Methode Rückgabetyp Problem

- Behebung des Memory-Leak-Problems von `stream_select`


### CLI Updates



- Unter `CygWin` wurde ein SSL Zertifikatschain mitgeliefert, um das SSL-Authentifizierungsproblem zu lösen

- Update auf `PHP-8.1.5`


## v4.8.8


### Optimieren



- SW_IPC_BUFFER_MAX_SIZE auf 64k reduzieren

- Optimierung der http2 header_table_size Einstellung


### Beheben



- Behebung des Problems mit vielen Sockets, wenn enable_static_handler zum Herunterladen statischer Dateien verwendet wird

- Behebung des NPN Fehlers des http2 server


## v4.8.7


### Verbessern



- Hinzufügen der Unterstützung für curl_share


### Beheben



- Behebung des undefinierten Symbolfehlerproblems unter ARM32 Architektur

- Behebung der Kompatibilität von `clock_gettime()`

- Behebung des Problems, bei dem der PROCESS-Modus Server gesendet失败, wenn das Kernsystem nicht genügend großes RAM hat


## v4.8.6


### Beheben



- Voranstellung des API-Namens für die boost/context API

- Optimierung der Konfigurationsoptionen


## v4.8.5


### Beheben



- Rückkehr der Parameterarten für Table

- Behebung des Crashs, wenn mit dem Websocket-Protokoll falsche Daten empfangen werden


## v4.8.4


### Beheben



- Behebung der Kompatibilität von sockets hook und PHP-8.1

- Behebung der Kompatibilität von Table und PHP-8.1

- Behebung des Problems, bei dem der Coroutine-Stil HTTP Server `Content-Type` als `application/x-www-form-urlencoded` für `POST` Parameter interpretieren kann, nicht wie erwartet ist


## v4.8.3


### Neue API


- Hinzufügen der `Coroutine\Socket::isClosed()` Methode


### Beheben



- Behebung der Kompatibilitätsprobleme der curl native hook unter PHP8.1

- Behebung der Kompatibilitätsprobleme der sockets hook unter PHP8

- Behebung des Fehlers der sockets hook Funktion Rückgabetyp

- Behebung des Problems, dass sendfile im Http2Server nicht content-type einstellen kann

- Optimierung der Leistung von HttpServer date header, Cache hinzugefügt


## v4.8.2


### Beheben



- Behebung des Memory-Leak-Problems der proc_open hook

- Behebung der Kompatibilitätsprobleme der curl native hook mit PHP-8.0 und PHP-8.1

- Behebung des Problems, dass die Manager-Prozess die Verbindung nicht normal schließen kann

- Behebung des Problems, dass der Manager-Prozess sendMessage nicht verwenden kann

- Behebung des Ausnahmeproblemes beim Empfang von riesigen POST-Daten durch den `Coroutine\Http\Server`

- Behebung des Problems, dass ein tödlicher Fehler in der PHP8 Umgebung nicht direkt beendet werden kann

- Anpassung der coroutine `max_concurrency` Konfigurationsoption, nur noch in `Co::set()` erlaubt

- Anpassung von `Coroutine::join()`, um fehlende Coroutinen zu ignorieren


## v4.8.1


### Neue API


- Hinzufügen der Funktionen `swoole_error_log_ex()` und `swoole_ignore_error()` (#4440) (@matyhtf)


### Verbessern



- Umzug der admin api aus ext-swoole_plus zu ext-swoole (#4441) (@matyhtf)

- admin server hinzugefügt get_composer_packages Befehl (swoole/library@07763f46) (swoole/library@8805dc05) (swoole/library@175f1797) (@sy-records) (@yunbaoi)

- Hinzugefügt die POST-Methode für Write-Operationen Request Limits (swoole/library@ac16927c) (@yunbaoi)

- admin server unterstützt die Abfrage von Klassenmethodeinformationen (swoole/library@690a1952) (@djw1028769140) (@sy-records)

- Optimierung des admin server Codes (swoole/library#128) (swoole/library#131) (@sy-records)

- admin server unterstützt die gleichzeitige Anforderung mehrerer Ziele und die gleichzeitige Anforderung mehrerer APIs (swoole/library#124) (@sy-records)

- admin server unterstützt die Abfrage von Schnittstelleninformationen (swoole/library#130) (@sy-records)
- SWOOLE_HOOK_CURL unterstützt CURLOPT_HTTPPROXYTUNNEL (swoole/library#126) (@sy-records)


### Beheben



- join Methode verbietet den gleichzeitigen Aufruf derselben Coroutine (#4442) (@matyhtf)

- Behebung des Problems mit der unbeabsichtigten Freisetzung des Table atomischen Locks (#4446) (@Txhua) (@matyhtf)

- Behebung des verlorenen helper options (swoole/library#123) (@sy-records)

- Behebung des Fehlers mit den falschen Befehlsparametern für get_static_property_value (swoole/library#129) (@sy-records)


## v4.8.0


### Unterschneidende Veränderungen



- Unter der base-Modus wird der onStart Callback immer beim Start des ersten Arbeitsprozesses (Arbeitsprozess-ID 0) aufgerufen, vor onWorkerStart (#4389) (@matyhtf)


### Neue API



- Hinzufügen der `Co::getStackUsage()` Methode (#4398) (@matyhtf) (@twose)

- Hinzufügen einiger API für `Coroutine\Redis` (#4390) (@chrysanthemum)

- Hinzufügen der `Table::stats()` Methode (#4405) (@matyhtf)
- Hinzufügen der `Coroutine::join()` Methode (#4406) (@matyhtf)


### Neue Funktionen



- Unterstützung für server command (#4389) (@matyhtf)
- Unterstützung für das `Server::onBeforeShutdown` Ereignis Callback (#4415) (@matyhtf)
### Verbessern



- Wenn ein Websocket-Paket fehlschlägt, wird ein Fehlercode festgelegt (swoole/swoole-src@d27c5a5) (@matyhtf)

-新增了`Timer::exec_count`字段(#4402) (@matyhtf)

- Hooks für mkdir unterstützen die Verwendung der open_basedir ini-Konfiguration (#4407) (@NathanFreeman)

- Die Bibliothek fügt das vendor_init.php-Skript hinzu (swoole/library@6c40b02) (@matyhtf)

- SWOOLE_HOOK_CURL unterstützt CURLOPT_UNIX_SOCKET_PATH (swoole/library#121) (@sy-records)

- Der Client unterstützt die Einstellung der ssl_ciphers Konfigurationsoption (#4432) (@amuluowin)
- Einige neue Informationen wurden für `Server::stats()` hinzugefügt (#4410) (#4412) (@matyhtf)


### Beheben



- Behebung des unnötigen URL-Decodings von Dateinamen beim Dateiaufbau (swoole/swoole-src@a73780e) (@matyhtf)

- Behebung des Problems mit max_frame_size im HTTP/2 (#4394) (@twose)

- Behebung des bugs bei curl_multi_select (#4393) (#4418) (@matyhtf)

- Behebung des Verlusts von coroutine-options (#4425) (@sy-records)
- Behebung des Problems, dass eine Verbindung nicht geschlossen werden kann, wenn der Sendebufffer voll ist (swoole/swoole-src@2198378) (@matyhtf)


## v4.7.1


### Verbessern



- `System::dnsLookup` unterstützt die Abfrage von `/etc/hosts` (#4341) (#4349) (@zmyWL) (@NathanFreeman)

- Unterstützung für boost context für mips64 hinzugefügt (#4358) (@dixyes)

- `SWOOLE_HOOK_CURL` unterstützt die `CURLOPT_RESOLVE` Option (swoole/library#107) (@sy-records)

- `SWOOLE_HOOK_CURL` unterstützt die `CURLOPT_NOPROGRESS` Option (swoole/library#117) (@sy-records)
- Unterstützung für boost context für riscv64 hinzugefügt (#4375) (@dixyes)


### Beheben



- Behebung des Memory-Errors, der beim Schließen in PHP-8.1 auftritt (#4325) (@twose)

- Behebung der nicht serialisierbaren Klasse aus 8.1.0beta1 (#4335) (@remicollet)

- Behebung des Problems, bei dem mehrere Coroutines rekursiv Verzeichnisse erstellen und scheitern (#4337) (@NathanFreeman)

- Behebung des Problems, bei dem native curl große Dateien über das Internet sendet und gelegentlich Timeout-Fälle aufweist, sowie des Problems, bei dem ein Crash beim Nutzen der Coroutine-Datei-API in der CURL WRITEFUNCTION auftritt (#4360) (@matyhtf)
- Behebung des Problems, dass `PDOStatement::bindParam()` erwartet, dass der erste Parameter ein String ist (swoole/library#116) (@sy-records)


## v4.7.0


### Neue API



-新增`Process\Pool::detach()`方法(#4221) (@matyhtf)

- `Server` unterstützt die `onDisconnect` Rückruffunktion (#4230) (@matyhtf)

- 新增 `Coroutine::cancel()` und `Coroutine::isCanceled()` Methoden (#4247) (#4249) (@matyhtf)
- `Http\Client` unterstützt die Optionen `http_compression` und `body_decompression` (#4299) (@matyhtf)


### Verbessern



- Unterstützung für Coroutine MySQL-Clients bei der Vorbereitung für strenge Typen der Felder (#4238) (@Yurunsoft)

- DNS unterstützt die `c-ares` Bibliothek (#4275) (@matyhtf)

- `Server` unterstützt das Konfigurieren von Heartbeat-Prüfzeiten für verschiedene Ports beim Multiport-Hören (#4290) (@matyhtf)

- Die `dispatch_mode` von `Server` unterstützt die Modi `SWOOLE_DISPATCH_CO_CONN_LB` und `SWOOLE_DISPATCH_CO_REQ_LB` (#4318) (@matyhtf)

- `ConnectionPool::get()` unterstützt den `timeout` Parameter (swoole/library#108) (@leocavalcante)

- Hook Curl unterstützt die `CURLOPT_PRIVATE` Option (swoole/library#112) (@sy-records)
- Optimierung der Funktionsanweisung für die Methode `PDOStatementProxy::setFetchMode()` (swoole/library#109) (@yespire)


### Beheben



- Behebung des Ausnahms, bei dem beim Einsatz des Thread-Kontextes eine große Anzahl von Coroutines nicht erstellt werden kann (8ce5041) (@matyhtf)

- Behebung des Problems mit dem Verlust des php_swoole.h-Headers beim Installieren von Swoole (#4239) (@sy-records)

- Behebung des Problems der Nicht-Kompatibilität mit EVENT_HANDSHAKE (#4248) (@sy-records)

- Behebung des Problems, dass die SW_LOCK_CHECK_RETURN-Makro möglicherweise die Funktion zweimal aufruft (#4302) (@zmyWL)

- Behebung des Problems unter M1-Chips mit `Atomic\Long` (e6fae2e) (@matyhtf)

- Behebung des Problems des Verlusts der Rückkehrwerte von `Coroutine\go()` (swoole/library@1ed49db) (@matyhtf)
- Behebung des Problems mit der Rückkehrwertart von `StringObject` (swoole/library#111) (swoole/library#113) (@leocavalcante) (@sy-records)


### Kern


- Verhinderung des Hooks für Funktionen, die bereits von PHP禁用 sind (#4283) (@twose)


### Tests



- Hinzufügen des Buildens unter dem `Cygwin`-Umwelt (#4222) (@sy-records)
- Hinzufügen der Compilettests für `alpine 3.13` und `3.14` (#4309) (@limingxinleo)


## v4.6.7


### Verbessern


- Manager-Prozess und Task-Synchronisierungsvorgänge unterstützen die Funktion `Process::signal()` (#4190) (@matyhtf)


### Beheben



- Behebung des Problems, dass Signale nicht mehrmals registriert werden können (#4170) (@matyhtf)

- Behebung des Problems beim编译失败 auf OpenBSD/NetBSD (#4188) (#4194) (@devnexen)

- Behebung des Problems, dass das onClose-Event unter besonderen Umständen verloren geht, wenn auf Schreibbar-Ereignisse wartet (#4204) (@matyhtf)

- Behebung des Problems beim Einsatz von native curl mit Symfony HttpClient (#4204) (@matyhtf)

- Behebung des Problems, dass die Methode `Http\Response::end()` immer true zurückgibt (swoole/swoole-src@66fcc35) (@matyhtf)
- Behebung der PDOException, die durch `PDOStatementProxy` verursacht wird (swoole/library#104) (@twose)


### Kern



- Überarbeitung des worker buffer, additionale msg id Markierung für event data (#4163) (@matyhtf)

- Änderung des Log-Levels für Request Entity Too Large auf warning (#4175) (@sy-records)

- Ersetzung von inet_ntoa und inet_aton Funktionen (#4199) (@remicollet)
- Änderung des Standardwerts für output_buffer_size auf UINT_MAX (swoole/swoole-src@46ab345) (@matyhtf)


## v4.6.6


### Verbessern



- Unterstützung für das Senden von SIGTERM-Signalen an den Manager-Prozess nach dem Abbruch des Master-Prozesses unter FreeBSD (#4150) (@devnexen)

- Unterstützung für die statische Kompilierung von Swoole in PHP (#4153) (@matyhtf)
- Unterstützung für SNI mit HTTP-Proxy (#4158) (@matyhtf)


### Beheben



- Behebung des Fehlers bei der asynchronen Verbindung eines synchronen Clients (#4152) (@matyhtf)
- Behebung des Memory-Leaks durch Hooking native curl multi (swoole/swoole-src@91bf243) (@matyhtf)


## v4.6.5


### Neue API


- Hinzufügen der count-Methode zur WaitGroup (swoole/library#100) (@sy-records) (@deminy)


### Verbessern



- Unterstützung für native curl multi (#4093) (#4099) (#4101) (#4105) (#4113) (#4121) (#4147) (swoole/swoole-src@cd7f51c) (@matyhtf) (@sy-records) (@huanghantao)
- Erlaubt es, im Response von HTTP/2 Array-Werte für die Einstellung von Headers zu verwenden


### Beheben



- Behebung des Problems beim Builden unter NetBSD (#4080) (@devnexen)

- Behebung des Problems beim Builden unter OpenBSD (#4108) (@devnexen)

- Behebung des Problems beim Builden unter illumos/solaris, nur Member Aliases (#4109) (@devnexen)
- Behebung des Problems mit dem Heartbeat-检测结果 bei unvollendeter Handshake (#4114) (@matyhtf)

- Behebung des Fehlers, der durch das Vorhandensein von `host:port` in `host` beim Einsatz eines Proxys bei Http\Client verursacht wird (#4124) (@Yurunsoft)
- Behebung der Einstellung von header und cookie in Swoole\Coroutine\Http::request (swoole/library#103) (@leocavalcante) (@deminy)


### Kern



- Unterstützung für ASM Kontext auf BSD (#4082) (@devnexen)

- Verwendung von arc4random_buf unter FreeBSD um getrandom zu implementieren (#4096) (@devnexen)
- Optimierung des darwin arm64 Kontexts: Löschung des Workarounds und Verwendung von label (#4127) (@devnexen)


### Tests


- Hinzufügen des Build-Skripts für Alpine (#4104) (@limingxinleo)


## v4.6.4


### Neue API


- Hinzufügen der Funktionen Coroutine\Http::request, Coroutine\Http::post, Coroutine\Http::get (swoole/library#97) (@matyhtf)


### Verbesserungen



- Unterstützung für ARM 64 Build (#4057) (@devnexen)

- Unterstützung für die Einstellung der open_http_protocol in Swoole TCP Server (#4063) (@matyhtf)

- Unterstützung für die Einstellung von nur certificate bei ssl-Kunden (91704ac) (@matyhtf)
- Unterstützung für die tcp_defer_accept-Option unter FreeBSD (#4049) (@devnexen)


### Behebung



- Behebung des Problems mit dem Fehlen von Proxy-Autorisierung bei Verwendung von Coroutine\Http\Client (edc0552) (@matyhtf)

- Behebung des Memory-Allocation-Problems von Swoole\Table (3e7770f) (@matyhtf)

- Behebung des Crashs bei gleichzeitigen Verbindungen des Coroutine\Http2\Client (630536d) (@matyhtf)

- Behebung des Problems mit der Aktivierung von ssl_encrypt bei DTLS (842733b) (@matyhtf)

- Behebung des Memory-Leaks von Coroutine\Barrier (swoole/library#94) (@Appla) (@FMiS)

- Behebung des Offset-Fehlers, der durch die Reihenfolge von CURLOPT_PORT und CURLOPT_URL verursacht wird (swoole/library#96) (@sy-records)

- Behebung des Fehlers bei der Verwendung von `Table::get($key, $field)` wenn der Feldtyp float ist (08ea20c) (@matyhtf)
- Behebung des Memory-Leaks von Swoole\Table (d78ca8c) (@matyhtf)


## v4.4.24


### Behebung


- Behebung des Crashs bei gleichzeitigen Verbindungen des http2-Kunden (#4079)


## v4.6.3


### Neue API



- Hinzufügen der Funktionen Coroutine\Http::request, Coroutine\Http::post, Coroutine\Http::get (swoole/library@82f63be) (@matyhtf)
- Hinzufügen der Funktionen Coroutine\Coroutine\defer (swoole/library@92fd0de) (@matyhtf)


### Verbesserungen



- Hinzufügen der compression_min_length-Option für HTTP-Server (#4033) (@matyhtf)
- Erlaubt die Einstellung der Content-Length-HTTP-Header auf Anwendungsebene (#4041) (@doubaokun)


### Behebung



- Behebung des Core-Dumps, wenn das Programm die Grenze für die Anzahl offener Dateien erreicht (#4033) (@matyhtf)

- Behebung des Problems mit der Deaktivering von JIT (#4029) (@twose)
- Behebung des Problems mit falschen Parametern bei der Verwendung von `Response::create()` (swoole/swoole-src@a630b5b) (@matyhtf)
- Behebung des Fehlers bei der Fehlermeldung von task_worker_id bei der Bereitstellung von Aufgaben unter ARM-Plattform (#4040) (@doubaokun)
- Behebung des Core-Dumps, wenn PHP8 die native curl hook aktiviert (#4042)(#4045) (@Yurunsoft) (@matyhtf)
- Behebung des Memory-Out-of-Bounds-Fehlers während des shutdown-Stages bei fatal error (#4050) (@matyhtf)


### Kern



- Optimierung von ssl_connect/ssl_shutdown (#4030) (@matyhtf)
- Prozessabbau direkt bei Auftreten eines fatal error (#4053) (@matyhtf)


## v4.6.2


### Neue API



- Hinzufügen der Methode `Http\Request\getMethod()` (#3987) (@luolaifa000)
- Hinzufügen der Methode `Coroutine\Socket->recvLine()` (#4014) (@matyhtf)
- Hinzufügen der Methode `Coroutine\Socket->readWithBuffer()` (#4017) (@matyhtf)


### Verbesserungen



- Verbesserung der Methode `Response::create()`, die unabhängig von Server verwendet werden kann (#3998) (@matyhtf)

- Unterstützung für den Rückgabewert bool für `Coroutine\Redis->hExists` nach Einstellung der compatibility_mode (swoole/swoole-src@b8cce7c) (@matyhtf)
- Unterstützung für die Einstellung der PHP_NORMAL_READ-Option für socket_read (swoole/swoole-src@b1a0dcc) (@matyhtf)


### Behebung



- Behebung des Core-Dumps von `Coroutine::defer` unter PHP8 (#3997) (@huanghantao)

- Behebung des Problems mit falscher Einstellung von `Coroutine\Socket::errCode`, wenn thread context verwendet wird (swoole/swoole-src@004d08a) (@matyhtf)

- Behebung des Problems mit dem编译失败 von Swoole unter dem neuesten macos (#4007) (@matyhtf)
- Behebung des Problems mit dem leeren Pointer für PHP stream context, wenn der md5_file-Parameter eine URL übergibt (#4016) (@ZhiyangLeeCN)


### Kern



- Verwendung von AIO-Thread-Pool hook stdio (Lösung des Problems, das zuvor durch die Wahrnehmung von stdio als socket verursacht wurde, was zu Problemen mit gleichzeitiger Lesung und Schreiben in mehreren Coroutinen führte) (#4002) (@matyhtf)

- Rekonstruktion von HttpContext (#3998) (@matyhtf)
- Rekonstruktion von `Process::wait()` (#4019) (@matyhtf)


## v4.6.1


### Verbesserungen



- Hinzufügen der编译选项 `--enable-thread-context` (#3970) (@matyhtf)
- Überprüfung der Verbindung beim Umgang mit session_id (#3993) (@matyhtf)
- Verbesserung von CURLOPT_PROXY (swoole/library#87) (@sy-records)


### Behebung



- Behebung des Problems mit der MindestrPHP-Version für pecl-Installation (#3979) (@remicollet)
- Behebung des Problems bei der pecl-Installation, dass nicht die Optionen `--enable-swoole-json` und `--enable-swoole-curl` angegeben wurden (#3980) (@sy-records)
- Behebung des Problems mit der threadingssicherheit von openssl (b516d69f) (@matyhtf)
- Behebung des Core-Dumps bei Aktivierung von enableSSL (#3990) (@huanghantao)


### Kern


- Optimierung von ipc writev, um ein Core-Dump zu vermeiden, wenn die Ereignisdaten leer sind (9647678) (@matyhtf)


## v4.5.11


### Verbesserungen



- Optimierung von Swoole\Table (#3959) (@matyhtf)
- Verbesserung von CURLOPT_PROXY (swoole/library#87) (@sy-records)


### Behebung



- Behebung des Problems, bei dem das Hinzufügen und Abnehmen von Spalten bei Table nicht alle Spalten leer setzt (#3956) (@matyhtf) (@sy-records)
- Behebung des Fehlers mit `clock_id_t` bei der Kompilierung (49fea171) (@matyhtf)
- Behebung von fread bugs (#3972) (@matyhtf)
- Behebung des Crashs bei ssl in mehreren Threads (7ee2c1a0) (@matyhtf)
- Kompatibilität mit URI-Formatfehlern, die zu Fehlern beim Aufrufen von foreach führen (swoole/library#80) (@sy-records)
- Behebung des Fehlers mit falschen Parametern für trigger_error (swoole/library#86) (@sy-records)


## v4.6.0


### Rückwärtsinkompatibilitätsveränderungen



- Löschung der maximale Begrenzung für `session id`, keine Wiederholung mehr (#3879) (@matyhtf)

- Deaktivierung von unsicheren Funktionen bei Verwendung von Coroutine, einschließlich `pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait` (#3880) (@matyhtf)
- Standardmäßige Aktivierung des coroutine hook (#3903) (@matyhtf)


### Löschungen


- Nicht mehr Unterstützung für PHP7.1 (4a963df) (9de8d9e) (@matyhtf)


### Ab废弃ungen


- Markierung von `Event::rshutdown()` als abgekommen, bitte verwenden Sie stattdessen Coroutine\run (#3881) (@matyhtf)
### 新增 API

- Unterstützt die Einstellung und Abfrage der Priorität (#3876) (@matyhtf)
- Unterstützt die native-curl-Hook (#3863) (@matyhtf) (@huanghantao)
- Unterstützt die Übertragung von Objekts风格的参数 bei Server-Ereignishandlern, standardmäßig werden keine Objekts风格的参数 übertragen (#3888) (@matyhtf)
- Unterstützt die Erweiterung von hook sockets (#3898) (@matyhtf)
- Unterstützt doppelte Header (#3905) (@matyhtf)
- Unterstützt SSL sni (#3908) (@matyhtf)
- Unterstützt die Hooking von stdio (#3924) (@matyhtf)
- Unterstützt die Capture_peer_cert Option für stream_socket (#3930) (@matyhtf)
- Füge Http\Request::create/parse/isCompleted hinzu (#3938) (@matyhtf)
- Füge Http\Response::isWritable hinzu (db56827) (@matyhtf)

### Verbesserungen

- Alle Zeiten für Server wurden von int zu double geändert (#3882) (@matyhtf)
- Überprüft im swoole_client_select-Funktions die EINTR-Situation des poll-Funktions (#3909) (@shiguangqi)
- Füge die Überwachung von Deadlocks in Coroutinen hinzu (#3911) (@matyhtf)
- Unterstützt die Verwendung des SWOOLE_BASE-Modells zum Schließen von Verbindungen in einem anderen Prozess (#3916) (@matyhtf)
- Optimierte die Leistung der Kommunikation zwischen dem Server-Master-Prozess und den Worker-Prozessen, um Memory-Kopien zu reduzieren (#3910) (@huanghantao) (@matyhtf)

### Behebung

- Wenn ein Coroutine\Channel geschlossen wird, werden alle darin enthaltenen Daten ausgehoben (960431d) (@matyhtf)
- Behebe den Speicherfehler bei der Verwendung von JIT (#3907) (@twose)
- Behebe den Compilerfehler bei der Verwendung von dtls (#3947) (@Yurunsoft)
- Behebe den Fehler in der connection_list (#3948) (@sy-records)
- Behebe die SSL-Verifizierung (#3954) (@matyhtf)
- Behebe das Problem, dass beim Zuwachs und Abzug von Swoole\Table nicht alle Spalten gelöscht werden (#3956) (@matyhtf) (@sy-records)
- Behebe den编译失败 mit LibreSSL 2.7.5 (#3962) (@matyhtf)
- Behebe die undefinierten Konstanten CURLOPT_HEADEROPT und CURLOPT_PROXYHEADER (swoole/library#77) (@sy-records)

### Kern

- Ignoriere standardmäßig das SIGPIPE-Signal (9647678) (@matyhtf)
- Unterstützt den gleichzeitigen Betrieb von PHP-Coroutinen und C-Coroutinen (c94bfd8) (@matyhtf)
- Füge die get_elapsed-Tests hinzu (#3961) (@luolaifa000)
- Füge die get_init_msec-Tests hinzu (#3964) (@luffluo)

## v4.5.10

### Behebung

- Behebe den Core-Dump, der beim Verwenden von Event::cycle entsteht (93901dc) (@matyhtf)
- Kompatibilität mit PHP8 (f0dc6d3) (@matyhtf)
- Behebe den Fehler in der connection_list (#3948) (@sy-records)

## v4.4.23

### Behebung

- Behebe den Fehler bei der Abnahme von Swoole\Table (bcd4f60d)(0d5e72e7) (@matyhtf)
- Behebe die Fehlermeldungen des synchronen Clients (#3784)
- Behebe das Problem des Memory-Overflows bei der Analyse von Formulendaten (#3858)
- Behebe den Bug des Channels, nach dem Schließen kann man keine Daten mehr aus pop herausnehmen

## v4.5.9

### Verbesserungen

- Füge der Coroutine\Http\Client die Konstante SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED hinzu (#3873) (@sy-records)

### Behebung

- Kompatibilität mit PHP8 (#3868) (#3869) (#3872) (@twose) (@huanghantao) (@doubaokun)
- Behebe die undefinierten Konstanten CURLOPT_HEADEROPT und CURLOPT_PROXYHEADER (swoole/library#77) (@sy-records)
- Behebe CURLOPT_USERPWD (swoole/library@7952a7b) (@twose)

## v4.5.8

### 新增 API

- Füge die swoole_error_log-Funktion hinzu, um die log_rotation zu optimieren (swoole/swoole-src@67d2bff) (@matyhtf)
- ReadVector und WriteVector unterstützen SSL (#3857) (@huanghantao)

### Verbesserungen

- Lassen Sie System::wait nach dem Verlassen eines Subprozesses blockiert退出 (#3832) (@matyhtf)
- DTLS unterstützt Pakete von 16K (#3849) (@matyhtf)
- Die Response::cookie-Methode unterstützt das priority-Parameter (#3854) (@matyhtf)
- Unterstützt mehr CURL-Optionen (swoole/library#71) (@sy-records)
- Behandle das Problem, dass HTTP header beim Parsen nicht case-sensitiv sind und daher überschrieben werden (#3858) (@filakhtov) (@twose) (@sy-records)

### Behebung

- Behebe das Problem mit EAGAIN bei readv_all und writev_all (#3830) (@huanghantao)
- Behebe die PHP8-Compilerwarnung (#3830) (@matyhtf)
- Behebe das Sicherheitsproblem von Swoole\Table im Binary-Modus (#3842) (@twose)
- Behebe das Problem des Über 写入 von Dateien mit System::writeFile unter MacOS (#3842) (@matyhtf)
- Behebe die CURLOPT_WRITEFUNCTION von CURL (swoole/library#74) (swoole/library#75) (@sy-records)
- Behebe das Problem des Memory-Overflows bei der Analyse von HTTP form-data (#3858) (@twose)
- Behebe das Problem, dass is_callable() in PHP8 keine privaten Methoden von Klassen erreichen kann (#3859) (@twose)

### Kern

- Erneuere die Memory-allocation-Funktionen, verwenden Sie SwooleG.std_allocator (#3853) (@matyhtf)
- Erneuere die Pipes (#3841) (@matyhtf)

## v4.5.7

### 新增 API

- Füge den Coroutine\Socket-Client neue Methoden writeVector, writeVectorAll, readVector und readVectorAll hinzu (#3764) (@huanghantao)

### Verbesserungen

- Füge task_worker_num und dispatch_count zu server->stats hinzu (#3771) (#3806) (@sy-records) (@matyhtf)
- Füge zusätzliche Abhängigkeiten hinzu, einschließlich json, mysqlnd, sockets (#3789) (@remicollet)
- Limitiere den minimalen Wert für uid bei server->bind auf INT32_MIN (#3785) (@sy-records)
- Füge die Kompilationsoptionen für swoole_substr_json_decode hinzu, um negative Offsets zu unterstützen (#3809) (@matyhtf)
- Unterstützt die CURLOPT_TCP_NODELAY-Option von CURL (swoole/library#65) (@sy-records) (@deminy)

### Behebung

- Behebe den Fehler bei der Verbindungsinformation des synchronen Clients (#3784) (@twose)
- Behebe das Problem der hook scandir-Funktion (#3793) (@twose)
- Behebe den Fehler in der Coroutine-Barriere (swoole/library#68) (@sy-records)

### Kern

- Verwende boost.stacktrace zur Optimierung von print-backtrace (#3788) (@matyhtf)

## v4.5.6

### 新增 API

- Füge [swoole_substr_unserialize](/functions?id=swoole_substr_unserialize) und [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode) hinzu (#3762) (@matyhtf)

### Verbesserungen

- Ändere die onAccept-Methode von Coroutine\Http\Server in eine private (#dfcc83b) (@matyhtf)

### Behebung

- Behebe das Problem von coverity (#3737) (#3740) (@matyhtf)
- Behebe einige Probleme unter Alpine (#3738) (@matyhtf)
- Behebe swMutex_lockwait (0fc5665) (@matyhtf)
- Behebe das Installationsproblem von PHP-8.1 (#3757) (@twose)

### Kern

- Füge zur Socket::read/write/shutdown-Funktion eine Aktivitätsprüfung hinzu (#3735) (@matyhtf)
- Ändere die Typen von session_id und task_id in int64 (#3756) (@matyhtf)
## v4.5.5

!> Diese Version fügt eine [Konfigurationsoption](/server/setting) Überwachung hinzu, die ein Warning erzeugen wird, wenn eine Option festgelegt ist, die nicht von Swoole bereitgestellt wird.

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->set(['foo' => 'bar']);

$http->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hallo Swoole. #".rand(1000, 9999)."</h1>");
});

$http->start();
```


### Neue API



- Hinzufügen von Process\Manager, Bearbeitung von Process\ProcessManager als Synonym (swoole/library#eac1ac5) (@matyhtf)

- Unterstützung für HTTP2 Server GOAWAY (#3710) (@doubaokun)
- Hinzufügen der `Co\map()` Funktion (swoole/library#57) (@leocavalcante)


### Verbesserungen



- Unterstützung für HTTP2 Unix-Socket-Clients (#3668) (@sy-records)

- Nach dem Verlassen eines Worker-Prozesses wird der Zustand des Worker-Prozesses auf SW_WORKER_EXIT gesetzt (#3724) (@matyhtf)

- In den Rückgabewerten von `Server::getClientInfo()` werden send_queued_bytes und recv_queued_bytes hinzugefügt (#3721) (#3731) (@matyhtf) (@Yurunsoft)
- Server unterstützt die config-Option stats_file (#3725) (@matyhtf) (@Yurunsoft)


### Behebung



- Behebung des编译问题 unter PHP8 (zend_compile_string change) (#3670) (@twose)

- Behebung des编译问题 unter PHP8 (ext/sockets compatibility) (#3684) (@twose)

- Behebung des编译问题 unter PHP8 (php_url_encode_hash_ex change) (#3713) (@remicollet)

- Behebung des falschen Typwandels von 'const char*' zu 'char*' (#3686) (@remicollet)

- Behebung des Problems, dass HTTP2-Clients unter HTTP-Proxies nicht funktionieren (#3677) (@matyhtf) (@twose)

- Behebung des Problems mit chaotischen Daten bei erneuter Verbindung von PDO (#3688) (@sy-records)

- Behebung des Problems mit falscher Portanalyse bei Verwendung von IPv6 für UDP-Server
- Behebung des Problems mit无效的 lockwait 超时


## v4.5.4


### Unterschneidende Veränderungen nach unten



- SWOOLE_HOOK_ALL beinhaltet SWOOLE_HOOK_CURL (#3606) (@matyhtf)
- Removal von ssl_method, Hinzufügen von ssl_protocols (#3639) (@Yurunsoft)


### Neue API


- Hinzufügen der Methoden firstKey und lastKey für Arrays (swoole/library#51) (@sy-records)


### Verbesserungen


- Hinzufügen der Konfigurationsoptionen open_websocket_ping_frame und open_websocket_pong_frame für Websocket-Server (#3600) (@Yurunsoft)


### Behebung



- Behebung des Problems mit fseek und ftell bei Dateien größer als 2G (#3619) (@Yurunsoft)

- Behebung des Problems mit Socket barrier (#3627) (@matyhtf)

- Behebung des Problems mit HTTP proxy handshake (#3630) (@matyhtf)

- Behebung des Problems mit falscher HTTP Header Parsing bei Empfang von chunk data von der anderen Seite (#3633) (@matyhtf)

- Behebung des Problems mit zend_hash_clean Assertion failure (#3634) (@twose)

- Behebung des Problems mit dem移除 broken fd aus dem Event Loop (#3650) (@matyhtf)

- Behebung des Problems mit coredump bei Empfang eines无效的 packet (#3653) (@matyhtf)
- Behebung des Bugs von array_key_last (swoole/library#46) (@sy-records)


### Kernel



- Codeoptimierung (#3615) (#3617) (#3622) (#3635) (#3640) (#3641) (#3642) (#3645) (#3658) (@matyhtf)

- Bei der Schreiben von Daten in eine Swoole Table wird eine unnötige Speicheroperation reduziert (#3620) (@matyhtf)

- Umgestaltung von AIO (#3624) (@Yurunsoft)

- Unterstützung für readlink/opendir/readdir/closedir hook (#3628) (@matyhtf)
- Optimierung von swMutex_create, Unterstützung für SW_MUTEX_ROBUST (#3646) (@matyhtf)


## v4.5.3


### Neue API



- Hinzufügen von `Swoole\Process\ProcessManager` (swoole/library#88f147b) (@huanghantao)

- Hinzufügen von ArrayObject::append, StringObject::equals (swoole/library#f28556f) (@matyhtf)

- Hinzufügen von [Coroutine::parallel](/coroutine/coroutine?id=parallel) (swoole/library#6aa89a9) (@matyhtf)
- Hinzufügen von [Coroutine\Barrier](/coroutine/barrier) (swoole/library#2988b2a) (@matyhtf)


### Verbesserungen



- Hinzufügen von usePipelineRead zur Unterstützung von http2 client streaming (#3354) (@twose)

- Bei der HTTP-Client-Datei-Empfang wird vor dem Empfang von Daten keine Datei erstellt (#3381) (@twose)

- HTTP-Client unterstützt die Konfigurationsoptionen `bind_address` und `bind_port` (#3390) (@huanghantao)

- HTTP-Client unterstützt die Konfigurationsoption `lowercase_header` (#3399) (@matyhtf)

- `Swoole\Server` unterstützt die Konfigurationsoption `tcp_user_timeout` (#3404) (@huanghantao)

- `Coroutine\Socket` fügt event barrier hinzu, um die Anzahl der Coroutine-Wechsel zu reduzieren (#3409) (@matyhtf)

- Für spezifische swString wird ein `memory allocator` hinzugefügt (#3418) (@matyhtf)

- cURL unterstützt `__toString` (swoole/library#38) (@twose)

- Unterstützung für das direkte Einstellen des `wait count` im Konstruktor von WaitGroup (swoole/library#2fb228b8) (@matyhtf)

- Hinzufügen von `CURLOPT_REDIR_PROTOCOLS` (swoole/library#46) (@sy-records)

- HTTP1.1 Server unterstützt Trailer (#3485) (@huanghantao)

- Wenn die Coroutine-Schlafzeit weniger als 1ms beträgt, wird die aktuelle Coroutine yield (#3487) (@Yurunsoft)

- HTTP static handler unterstützt Dateien mit soft links (#3569) (@LeiZhang-Hunter)

- Unmittelbar nach dem Aufrufen des close-Methoden von Server wird die WebSocket-Verbindung geschlossen (#3570) (@matyhtf)

- Unterstützung für hook stream_set_blocking (#3585) (@Yurunsoft)

- Asynchroner HTTP2 server unterstützt Stream Control (#3486) (@huanghantao) (@matyhtf)
- Socket-Buffer wird freigesetzt, nachdem die onPackage Rückruffunktion ausgeführt wurde (#3551) (@huanghantao) (@matyhtf)


### Behebung



- Behebung des Problems mit dem coredump von WebSocket, wenn ein Protokollfehler auftritt (#3359) (@twose)

- Behebung des Problems mit dem swSignalfd_setup-Funktion und dem wait_signal-Funktion, wenn ein Nullzeiger-Fehler auftritt (#3360) (@twose)

- Behebung des Problems, bei dem ein Fehler auftritt, wenn dispatch_func festgelegt ist und Swoole\Server::close aufgerufen wird (#3365) (@twose)

- Behebung des Problems mit der Initialisierung von format_buffer in der Swoole\Redis\Server::format-Funktion (#3369) (@matyhtf) (@twose)

- Behebung des Problems beim Abrufen der MAC-Adresse unter MacOS (#3372) (@twose)

- Behebung der MySQL-Testfälle (#3374) (@qiqizjl)

- Behebung von mehreren PHP8-Kompatibilitätsproblemen (#3384) (#3458) (#3578) (#3598) (@twose)

- Behebung des Problems mit dem Verlust von php_error_docref, timeout_event und Rückgabewert bei hook socket write (#3383) (@twose)

- Behebung des Problems daran, dass der asynchrone Server den Server nicht schließen kann, wenn er in der WorkerStart-Rückruffunktion aufgerufen wird (#3382) (@huanghantao)

- Behebung des Problems mit dem Coredump, das auftreten kann, wenn der Heartbeat-Thread den conn->socket bedient (#3396) (@huanghantao)

- Behebung des logischen Problems von send_yield (#3397) (@twose) (@matyhtf)
- Behebung des Compilierungsproblems auf Cygwin64 (#3400) (@twose)

- Behebung des Problems mit dem无效en finish Attribut von WebSocket (#3410) (@matyhtf)

- Behebung des fehlenden MySQL transaction Fehlerstatus (#3429) (@twose)

- Behebung des Problems mit dem unkonformierten Verhalten von `stream_select` nach dem hook und dem Rückgabewert vor dem hook (#3440) (@Yurunsoft)

- Behebung des Problems mit dem Verlust des `SIGCHLD` Signals beim Erstellen von Unterprozessen mit `Coroutine\System` (#3446) (@huanghantao)

- Behebung des Problems mit der Unterstützung von SSL für `sendwait` (#3459) (@huanghantao)

- Behebung verschiedener Probleme bei `ArrayObject` und `StringObject` (swoole/library#44) (@matyhtf)

- Behebung des Problems mit falschen Ausnahminformationen bei mysqli (swoole/library#45) (@sy-records)

- Behebung des Problems damit dass `Swoole\Client` nach dem Einstellen von `open_eof_check` keine korrekte `errCode` zurückgeben kann (#3478) (@huanghantao)

- Behebung verschiedener Probleme mit `atomic->wait()`/`wakeup()` unter MacOS (#3476) (@Yurunsoft)

- Behebung des Problems damit dass `Client::connect` bei Ablehnung der Verbindung einen erfolgreichen Status zurückgibt (#3484) (@matyhtf)

- Behebung des Problems mit der nicht deklarierten nullptr_t in der alpine Umgebung (#3488) (@limingxinleo)

- Behebung des Problems mit dem double-free beim Herunterladen von Dateien mit dem HTTP Client (#3489) (@Yurunsoft)

- Behebung des Problems mit der nicht freigesetzten `Server\Port` beim Zerstören des `Server` was zu einem Memory Leak führt (#3507) (@twose)

- Behebung des Problems mit der Parsing-Fehl des MQTT-Protokolls (318e33a) (84d8214) (80327b3) (efe6c63) (@GXhua) (@sy-records)

- Behebung des Problems mit dem coredump, der durch die Methode `Coroutine\Http\Client->getHeaderOut` verursacht wird (#3534) (@matyhtf)

- Behebung des Problems mit dem Verlust von Fehlerinformationen nach einem erfolgreichen SSL-Überprüfung (#3535) (@twose)

- Behebung des Problems mit dem falschen Link in der README zum Swoole benchmark (#3536) (@sy-records) (@santalex)

- Behebung des Problems mit dem `header` Injection durch die Verwendung von `CRLF` in `HTTP header/cookie` (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)

- Behebung des Problems mit dem Variable-Fehler, der in Issue #3463 erwähnt wurde (#3547) (chromium1337) (@huanghantao)

- Behebung des Problems mit dem Typo, der in pr #3463 erwähnt wurde (#3547) (@deminy)

- Behebung des Problems damit dass das frame->fd für den coroutine WebSocket Server leer ist (#3549) (@huanghantao)

- Behebung des Problems mit dem falschen Verbindungszustand, der durch das fehlerhafte Urteilen des Heartbeat-Threads verursacht wird und zu einem Connection Leak führt (#3534) (@matyhtf)

- Behebung des Problems mit dem Blockieren von Signalen in `Process\Pool` (#3582) (@huanghantao) (@matyhtf)

- Behebung des Problems mit dem Senden von Headers in der `SAPI` (#3571) (@twose) (@sshymko)

- Behebung des Problems damit dass `errCode` und `errMsg` nicht festgelegt werden, wenn die `CURLOPT_POSTFIELDS` nicht erfolgreich sind (swoole/library#1b6c65e) (@sy-records)

- Behebung des Problems mit dem coredump, der beim Aufrufen der `setProtocol` Methode in `swoole_socket_coro` beim Accept-Vorgang auftritt (#3591) (@matyhtf)


### Kern



- Verwendung des C++ Stils (#3349) (#3351) (#3454) (#3479) (#3490) (@huanghantao) (@matyhtf)

- Hinzufügen von `Swoole known strings` zur Verbesserung der Leistung beim Lesen von PHP-Objekten (#3363) (@huanghantao)

- Mehrere Code-Optimierungen (#3350) (#3356) (#3357) (#3423) (#3426) (#3461) (#3463) (#3472) (#3557) (#3583) (@huanghantao) (@twose) (@matyhtf)

- Mehrere Optimierungen des Testcode (#3416) (#3481) (#3558) (@matyhtf)

- Vereinfachung des `int` Typs für `Swoole\Table` (#3407) (@matyhtf)

- Hinzufügen von `sw_memset_zero` und Ersatz des `bzero` Functions (#3419) (@CismonX)

- Optimierung des Logging-Moduls (#3432) (@matyhtf)

- Mehrere libswoole-Restructurierungen (#3448) (#3473) (#3475) (#3492) (#3494) (#3497) (#3498) (#3526) (@matyhtf)

- Mehrere Überarbeitungen von Headerfile-Einstellungen (#3457) (@matyhtf) (@huanghantao)

- Hinzufügen von `Channel::count()` und `Channel::get_bytes()` (f001581) (@matyhtf)

- Hinzufügen von `scope guard` (#3504) (@huanghantao)

- Hinzufügen von libswoole-Abdeckungstests (#3431) (@huanghantao)

- Hinzufügen von lib-swoole/ext-swoole Tests für die MacOS Umgebung (#3521) (@huanghantao)

- Hinzufügen von lib-swoole/ext-swoole Tests für die Alpine Umgebung (#3537) (@limingxinleo)


## v4.5.2

[v4.5.2](https://github.com/swoole/swoole-src/releases/tag/v4.5.2), dies ist eine BUG-Fix-Version, es gibt keine nach unten nicht kompatible Änderungen


### Verbesserungen



- Unterstützung für `Server->set(['log_rotation' => SWOOLE_LOG_ROTATION_DAILY])` um Tageweise Protokollierung zu generieren (#3311) (@matyhtf)

- Unterstützung für `swoole_async_set(['wait_signal' => true])`, wenn ein Signal-Listener vorhanden ist, wird der Reaktor nicht verlassen (#3314) (@matyhtf)

- Unterstützung für `Server->sendfile` um leere Dateien zu senden (#3318) (@twose)

- Optimierung der Worker-Warnungsinformationen (#3328) (@huanghantao)

- Optimierung der Host-Header-Konfiguration unter HTTPS-Proxy (Nutzung von ssl_host_name zum Konfigurieren) (#3343) (@twose)

- SSL verwendet standardmäßig den ecdh auto Modus (#3316) (@matyhtf)
- SSL-Kunden verlassen sich im Verbindungszustand, wenn die Verbindung unterbrochen wird (#3342) (@huanghantao)


### Fixes



- Behebung des Problems mit `Server->taskWait` unter OSX (#3330) (@matyhtf)

- Behebung des Bugs mit der falschen Parsing-Funktion des MQTT-Protokolls (8dbf506b) (@guoxinhua) (2ae8eb32) (@twose)

- Behebung des Problems mit dem Überlauf des int-Typs für Content-Length (#3346) (@twose)

- Behebung des Problems mit dem fehlenden Check für die Länge des PRI-Pakets (#3348) (@twose)

- Behebung des Problems damit dass `CURLOPT_POSTFIELDS` nicht leer gemacht werden kann (swoole/library@ed192f64) (@twose)

- Behebung des Problems damit dass das neueste Verbindungsteam vor Empfang der nächsten Verbindung nicht freigesetzt werden kann (swoole/library@1ef79339) (@twose)


### Kern



- Socket-Schreiben mit Zero-Copy-Funktion (#3327) (@twose)
- Verwendung von swoole_get_last_error/swoole_set_last_error anstelle der globalen Variablen für Lesebuchungen und Schreibungen (e25f262a) (@matyhtf) (#3315) (@huanghantao)
- Unterstützung für die Konfiguration von `log_date_format`, um das Datumsformat in den Logs zu ändern, und `log_date_with_microseconds` zum Anzeigen von Mikrosekundentimestempeln in den Logs (baf895bc) (@matyhtf)

- Unterstützung für CURLOPT_CAINFO und CURLOPT_CAPATH (swoole/library#32) (@sy-records)
- Unterstützung für CURLOPT_FORBID_REUSE (swoole/library#33) (@sy-records)


### Behebung



- Behebung des Fehlens beim 32-Bit-Build (#3276) (#3277) (@remicollet) (@twose)

- Behebung des Problems, dass bei erneuter Verbindung eines Coroutine Client keine EISCONN-Fehlerinformation generiert wurde (#3280) (@codinghuang)

- Behebung eines potenziellen Bugs im Table-Modul (d7b87b65) (@matyhtf)

- Behebung eines NULL-Pointer-Falls im Server aufgrund undefinierten Verhaltens (#3304) (#3305) (@twose)

- Behebung des Problems mit NULL-Pointer-Fällen nach Aktivierung des Heartbeat-Konfigurations (#3307) (@twose)

- Behebung des Problems mit nicht wirksamer MySQLi-Konfiguration (swoole/library#35)
- Behebung des Problems bei der Parsing von Responses mit unregelmäßigen Headers (Fehlende Lücken) (swoole/library#27) (@Yurunsoft)


### Ab废弃


- Die Methoden `Coroutine\System::(fread/fgets/fwrite)` und andere werden als ab废弃 markiert (Bitte verwenden Sie die Hook-Funktion als Ersatz, und verwenden Sie direkt die von PHP bereitgestellten Dateifunktionen) (c7c9bb40) (@twose)


### Kern



- Verwendung von `zend_object_alloc` für die Allokation von Speicher für benutzerdefinierte Objekte (cf1afb25) (@twose)

- Einige Optimierungen, zusätzliche Konfigurationsmöglichkeiten für das Logging-Modul (#3296) (@matyhtf)
- Eine große Menge an Code-Optimierungen und die Hinzufügung von Unit-Tests (swoole/library) (@deminy)


## v4.5.0

[v4.5.0](https://github.com/swoole/swoole-src/releases/tag/v4.5.0), dies ist eine große Versionsupdate, die lediglich einige bereits in v4.4.x als ab废弃 gekennzeichnete Module entfernt hat


### Neue API



- Unterstützung für DTLS, jetzt können Sie diese Funktion zum Aufbau von WebRTC-Anwendungen verwenden (#3188) (@matyhtf)

- Ein eingebauter `FastCGI`-Client, der per Zeile Code verwendet werden kann, um Anforderungen an FPM zu proxyen oder FPM-Anwendungen aufzurufen (swoole/library#17) (@twose)

- `Co::wait`, `Co::waitPid` (für die Rückgewinnung von Tochterprozessen) `Co::waitSignal` (für das Warten auf Signale) (#3158) (@twose)

- `Co::waitEvent` (für das Warten auf spezifische Ereignisse auf Sockets) (#3197) (@twose)

- `Co::set(['exit_condition' => $callable])` (für die benutzerdefinierte Bedingung für den Programmabbruch) (#2918) (#3012) (@twose)

- `Co::getElapsed` (für die Erlangung der Laufzeit von Coroutinen zur Analyse von Statistiken oder zur Identifizierung von Zombie-Coroutinen) (#3162) (@doubaokun)

- `Socket::checkLiveness` (für das Bestimmen der Lebendigkeit von Verbindungen durch systematische Aufrufe), `Socket::peek` (für das Einsehen in den Lesebuffern) (#3057) (@twose)

- `Socket->setProtocol(['open_fastcgi_protocol' => $bool])` (für die eingebürgerte Unterstützung für FastCGI-Decapsulation) (#3103) (@twose)

- `Server::get(Master|Manager|Worker)Pid`, `Server::getWorkerId` (für die Erlangung der Instanz und Informationen des asynchronen Servers) (#2793) (#3019) (@matyhtf)

- `Server::getWorkerStatus` (für den Erhalt des Status der Worker-Prozesse, gibt Constants SWOOLE_WORKER_BUSY, SWOOLE_WORKER_IDLE zurück, um den Arbeitsstatus anzuzeigen) (#3225) (@matyhtf)

- `Server->on('beforeReload', $callable)` und `Server->on('afterReload', $callable)` (für Ereignisse beim Server-Reloading, die im Manager-Prozess auftreten) (#3130) (@hantaohuang)

- Der statische Dateihandler für `Http\Server` unterstützt nun `http_index_files` und `http_autoindex` Konfigurationen (#3171) (@hantaohuang)

- Die Methode `Http2\Client->read(float $timeout = -1)` unterstützt das Lesen von fließenden Antworten (#3011) (#3117) (@twose)

- `Http\Request->getContent` (als Synonym für die rawContent-Methode) (#3128) (@hantaohuang)
- `swoole_mime_type_(add|set|delete|get|exists)()` (MIME-相关的 APIs, können eingebauter MIME-Typen hinzufügen, entfernen, ändern, suchen und ändern) (#3134) (@twose)


### Verbesserung



- Optimierung des Speicherkopfes zwischen Master- und Worker-Prozessen (im extremen Fall eine vierfache Leistungssteigerung) (#3075) (#3087) (@hantaohuang)

- Optimierung des WebSocket-Sendeverhaltens (#3076) (@matyhtf)

- Optimierung des einmaligen Speicherkopfes beim Bau von WebSocket-Frame (#3097) (@matyhtf)

- Optimierung des SSL-Verifizierungsmoduls (#3226) (@matyhtf)

- Trennung von SSL-Akzept und SSL-Handshake, um das Problem zu lösen, dass langsame SSL-Clients potenziell einen falschen Tod des Coroutine-Servers verursachen können (#3214) (@twose)

- Unterstützung für MIPS-Architektur (#3196) (@ekongyun)

- UDP-Clients können jetzt automatisch eingehende Domainnamen auflösen (#3236) (#3239) (@huanghantao)

- `Coroutine\Http\Server` unterstützt nun einige häufig verwendete Optionen (#3257) (@twose)

- Unterstützung für die Einstellung von Cookies während des WebSocket-Handshake (#3270) (#3272) (@twose)

- Unterstützung für CURLOPT_FAILONERROR (swoole/library#20) (@sy-records)

- Unterstützung für CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY (swoole/library#22) (@sy-records)
- Unterstützung für CURLOPT_HTTPGET (swoole/library@d730bd08) (@shiguangqi)


### Entfernen



- Entfernen der Methode `Runtime::enableStrictMode` (b45838e3) (@twose)
- Entfernen der `Buffer`-Klasse (559a49a8) (@twose)


### Kern-Verwandte



- Neue C++-API: Die Funktion `coroutine::async` akzeptiert eine Lambda-Funktion als Parameter, um asynchrone Threadaufgaben zu starten (#3127) (@matyhtf)

- Umgestaltung des unteren event-API, indem integer-based File Descriptors zu swSocket-Objekten umgewandelt werden (#3030) (@matyhtf)

- Alle Kern-C-Dateien wurden in C++-Dateien umgewandelt (#3030) (71f987f3) (@matyhtf)

- Eine Reihe von Code-Optimierungen (#3063) (#3067) (#3115) (#3135) (#3138) (#3139) (#3151) (#3168) (@hantaohuang)

- Optimierung der Kopfdateien (#3051) (@matyhtf)

- Umgestaltung des Konfigurationsitems `enable_reuse_port`, um es standardisierter zu machen (#3192) (@matyhtf)

- Umgestaltung der Socket-相关的 API, um sie standardisierter zu machen (#3193) (@matyhtf)

- Vorhersage von Buffern zur Reduzierung eines unnötigen Systemaufrufs (#3b5aa85d) (@matyhtf)

- Entfernen des unteren刷新定时器 swServerGS::now, und direkt die Nutzung der Zeitfunktionen zum Erhalt der Zeit (#3152) (@hantaohuang)

- Optimierung des Protokollconfigurators (#3108) (@twose)

- Bessercompatibility für die Initialisierung von C-Strukturen (#3069) (@twose)

- Bitfelder werden einheitlich als uchar-Typ (#3071) (@twose)
- Unterstützung für parallele Tests, schneller (#3215) (@twose)


### Behebung



- Behebung des Problems, dass `enable_delay_receive` aktiviert ist und `onConnect` nicht ausgelöst wird (#3221) (#3224) (@matyhtf)
- Alle anderen Bug-Fixes wurden in die v4.4.x-Branche integriert und sind im Update-Log dargestellt, daher werden sie hier nicht weiter ausgeführt


## v4.4.22


### Behebung



- Behebung des Problems, dass der HTTP2-Client unter einem HTTP-Proxy nicht funktioniert (#3677) (@matyhtf) (@twose)
- Behebung des Problems mit chaotischen Daten bei der Wiederverbindung nach einemPDO-Abbau (swoole/library#54) (@sy-records)

- Behebung von swMutex_lockwait (0fc5665) (@matyhtf)

- Behebung des Fehlers bei der Portinterpretation für UDP-Server mit IPv6
- Behebung des Problems mit systemd fds

## v4.4.20

[v4.4.20](https://github.com/swoole/swoole-src/releases/tag/v4.4.20), dies ist eine BUG-Behebungsversion, ohne jegliche nach unten nicht kompatible Veränderungen

### Behebung

- Behebung des Fehlers, bei dem ein Fehler beim Aufrufen von `Swoole\Server::close` auftritt, wenn dispatch_func festgelegt ist (#3365) (@twose)

- Behebung des Initialisierungproblems von format_buffer in der Funktion `Swoole\Redis\Server::format` (#3369) (@matyhtf) (@twose)

- Behebung des Problems beim Abrufen der MAC-Adresse unter MacOS (#3372) (@twose)

- Behebung der MySQL-Testfälle (#3374) (@qiqizjl)

- Behebung des Problems daran, dass der asynchrone Server den Server nicht im回调funktions `WorkerStart` schließen kann (#3382) (@huanghantao)

- Behebung des übersehenen MySQL transaction Fehlermeldungszustands (#3429) (@twose)

- Behebung des double-free Problems beim Herunterladen von Dateien mit dem HTTP Client (#3489) (@Yurunsoft)

- Behebung des coredump Problems, das durch die Methode `Coroutine\Http\Client->getHeaderOut` verursacht wird (#3534) (@matyhtf)

- Behebung des `header`-Injectionsproblems, das durch die Verwendung von `CRLF` in `HTTP header/cookie` verursacht wird (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)

- Behebung des Problems, bei dem frame->fd für den coroutine WebSocket Server leer ist (#3549) (@huanghantao)

- Behebung des Problems mit dem read error on connection, das durch die hook phpredis verursacht wird (#3579) (@twose)

- Behebung des Probleme mit der Parsing von MQTT-Protokollen (#3573) (#3517) (9ad2b455) (@GXhua) (@sy-records)

## v4.4.19

[v4.4.19](https://github.com/swoole/swoole-src/releases/tag/v4.4.19), dies ist eine BUG-Behebungsversion, ohne jegliche nach unten nicht kompatible Veränderungen

!> Hinweis: v4.4.x ist nicht mehr die Hauptversion für Wartungen, nur BUGs werden bei Bedarf behoben

### Behebung

- Alle BUG-Behebungspatches aus v4.5.2 zusammengeführt

## v4.4.18

[v4.4.18](https://github.com/swoole/swoole-src/releases/tag/v4.4.18), dies ist eine BUG-Behebungsversion, ohne jegliche nach unten nicht kompatible Veränderungen

### Verbesserung

- UDP-Kunden können jetzt automatisch eingehende Domainnamen Parsen (#3236) (#3239) (@huanghantao)

- Im CLI-Modus werden stdout und stderr nicht mehr geschlossen (Fehlerprotokolle werden nach dem Schließen gezeigt) (#3249) (@twose)

- Coroutine\Http\Server unterstützt jetzt einige häufig verwendete Optionen (#3257) (@twose)

- Unterstützung für das Einstellen von Cookies während des WebSocket-Handshake (#3270) (#3272) (@twose)

- Unterstützung für CURLOPT_FAILONERROR (swoole/library#20) (@sy-records)

- Unterstützung für CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY (swoole/library#22) (@sy-records)

- Unterstützung für CURLOPT_HTTPGET (swoole/library@d730bd08) (@shiguangqi)

- Maximum Kompatibilität mit allen Versionen der PHP-Redis-Erweiterung (verschiedene Konstruktorenparameter bei verschiedenen Versionen) (swoole/library#24) (@twose)
- Verhinderung des Klonens von Verbindungsgegenständen (swoole/library#23) (@deminy)

### Behebung

- Behebung des Problems mit dem gescheiterten SSL-Handshake (dc5ac29a) (@twose)

- Behebung des Memory-Errors, der beim Generieren von Fehlermessungen auftritt (#3229) (@twose)

- Behebung des leeren Proxy-Verifizierungsinformations (#3243) (@twose)

- Behebung des Memory-Leaks des Channels (kein echtes Memory-Leak) (#3260) (@twose)

- Behebung des One-Time-Memory-Leaks, das durch Zyklische Referenzen beim Co\Http\Server verursacht wird (#3271) (@twose)

- Behebung des Schreibfehlers in `ConnectionPool->fill` (swoole/library#18) (@NHZEX)

- Behebung des Problems, dass der curl-Client bei Redirects die Verbindung nicht aktualisiert (#3217) (@doubaokun)

- Behebung des Problemes mit dem Null-Pointer, das beim Auslösen einer ioException auftritt (swoole/library@4d15a4c3) (@twose)

- Behebung des Deadlock-Problems, das durch das Übergeben von null an `ConnectionPool@put` und das Nicht-Rückgabe neuer Verbindungen verursacht wird (swoole/library#25) (@Sinute)

- Behebung des write_property Fehlers, der durch die mysqli Proxy-Implementierung verursacht wird (swoole/library#26) (@twose)

## v4.4.17

[v4.4.17](https://github.com/swoole/swoole-src/releases/tag/v4.4.17), dies ist eine BUG-Behebungsversion, ohne jegliche nach unten nicht kompatible Veränderungen

### Verbesserung

- Verbesserung der Leistung des SSL-Servers (#3077) (85a9a595) (@matyhtf)

- Beseitigung der Beschränkung der HTTP-Headergröße (#3187) limitation (@twose)

- Unterstützung für MIPS (#3196) (@ekongyun)

- Unterstützung für CURLOPT_HTTPAUTH (swoole/library@570318be) (@twose)

### Behebung

- Behebung des Verhaltens von package_length_func und des möglichen One-Time-Memory-Leaks (#3111) (@twose)

- Behebung des Fehlverhaltens unter HTTP-Statuscode 304 (#3118) (#3120) (@twose)

- Behebung des Memory-Errors, der durch falsches Makro-Erweiterungsausführen von Trace-Logs verursacht wird (#3142) (@twose)

- Behebung der OpenSSL-Funktionssignaturen (#3154) (#3155) (@twose)

- Behebung der SSL-Fehlermeldungen (#3172) (@matyhtf) (@twose)

- Behebung der Kompatibilität mit PHP-7.4 (@twose) (@matyhtf)

- Behebung des Problems mit der Längenanalyse von HTTP-chunks (#3189) (#3191) (@twose)

- Behebung des Verhaltens des Parser für multipart-Anfragen im chunked-Modus (3692d9de) (@twose)

- Behebung des Fehlers bei der Assertion von ZEND_ASSUME im PHP-Debug-Modus (fc0982be) (@twose)

- Behebung der Socket-Fehleradresse (d72c5e3a) (@twose)

- Behebung des Problems mit dem Getname von Sockets (#3177) (#3179) (@matyhtf)

- Behebung des Fehlverhaltens des statischen Dateihandlers bei leeren Dateien (#3182) (@twose)

- Behebung des Problems mit dem Hochladen von Dateien auf dem Coroutine\Http\Server (#3189) (#3191) (@twose)

- Behebung des möglichen Memory-Errors während des Schließens (#44aef60a) (@matyhtf)

- Behebung des heartbeat von Server->heartbeat (#3203) (@matyhtf)

- Behebung des Problems, dass der CPU-Scheduler einen Deadloop möglicherweise nicht verarbeiten kann (#3207) (@twose)

- Behebung der无效的写入操作 auf unveränderlichen Arrays (#3212) (@twose)

- Behebung des Problems mit mehreren wait-Anforderungen an WaitGroup (swoole/library@537a82e1) (@twose)

- Behebung des Problems mit leeren headers (konform zu cURL) (swoole/library@7c92ed5a) (@twose)

- Behebung des Problems, bei dem ein falscher Rückgabewert einer nicht-IO-Methode eine Ausnahme auslöst (#swoole/library@f6997394) (@twose)

- Behebung des Problems, bei dem die Proxy-Portnummer mehrmals zu den Headern hinzugefügt wird, während der cURL-hook aktiv ist (swoole/library@5e94e5da) (@twose)

## v4.4.16

[v4.4.16](https://github.com/swoole/swoole-src/releases/tag/v4.4.16), dies ist eine BUG-Behebungsversion, ohne jegliche nach unten nicht kompatible Veränderungen

### Verbesserung

- Verbesserung der Leistung des SSL-Servers (#3077) (85a9a595) (@matyhtf)

- Beseitigung der Beschränkung der Größe von HTTP-Headern (#3187) (@twose)

- Unterstützung für MIPS (#3196) (@ekongyun)

- Unterstützung für CURLOPT_HTTPAUTH (swoole/library@570318be) (@twose)
Jetzt können Sie die [Swoole Versionunterstützungsinformation](https://github.com/swoole/swoole-src/blob/master/SUPPORTED.md) erhalten.

- Besserer Fehlermeldung (0412f442) (09a48835) (@twose)

- Vorbeugung vor Systemaufruf-Endlosschleifen auf bestimmten speziellen Systemen (069a0092) (@matyhtf)
- Hinzufügen von Treibervariablen in PDOConfig (swoole/library#8) (@jcheron)

### Behebung

- Behebung des Speicherfehlers im http2_session.default_ctx (bddbb9b1) (@twose)

- Behebung der nicht initialisierten http_context (ce77c641) (@twose)

- Behebung eines Schreibfehlers im Table-Modul (kann zu Speicherfehlern führen) (db4eec17) (@twose)

- Behebung eines potenziellen Problems bei task-reload im Server (e4378278) (@GXhua)

- Behebung des unvollständigen Coroutine HTTP Server Request Originaltextes (#3079) (#3085) (@hantaohuang)

- Behebung des static handlers (Wenn das File leer ist, sollte keine 404 Antwort zurückgegeben werden) (#3084) (@Yurunsoft)

- Behebung des Problems mit der nicht funktionierenden http_compression_level Konfiguration (16f9274e) (@twose)

- Behebung des Nullpointerfehlers im Coroutine HTTP2 Server aufgrund der fehlenden Registrierung von handle (ed680989) (@twose)

- Behebung des Problems mit der nicht funktionierenden socket_dontwait Konfiguration (27589376) (@matyhtf)

- Behebung des Problems, dass zend::eval möglicherweise mehrmals ausgeführt wird (#3099) (@GXhua)

- Behebung des Nullpointerfehlers im HTTP2 Server aufgrund der Antwort nach der Verbindungsschließung (#3110) (@twose)

- Behebung des适配problems von PDOStatementProxy::setFetchMode (swoole/library#13) (@jcheron)
