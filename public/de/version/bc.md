# Unterschrittliche Änderungen nach unten


## v5.0.0
* Ändere den Standardbetriebsmodus des `Server` auf `SWOOLE_BASE`
* Minimum-PHP-Version-Anforderung auf `8.0` erhöht
* Alle Klassenmethoden und Funktionen haben jetzt Typbeschränkungen, um einen starken Typmodus zu verwenden
* Entfernte die Unterstrich-`PSR-0`-Klassennamen, behält nur die Namespaced-Stil-Klassennamen bei, z.B. `swoole_server` muss zu `Swoole\Server` geändert werden
* `Swoole\Coroutine\Redis` und `Swoole\Coroutine\MySQL` sind als veraltet gekennzeichnet, bitte verwenden Sie `Runtime Hook`+native `Redis`/`MySQL`-Clients



## v4.8.0


- Im `BASE` Modus wird der `onStart` Rückruf immer bei der Aktivierung des ersten Arbeitsprozesses (`workerId` ist `0`) aufgerufen, bevor der `onWorkerStart` aufgerufen wird. In der `onStart` Funktion kann immer die Coroutine `API` verwendet werden, und wenn der `Worker-0` einen tödlichen Fehler hat und neu gestartet wird, wird der `onStart` Rückruf erneut aufgerufen
In früheren Versionen wurde der `onStart` Rückruf im Falle eines einzigen Arbeitsprozesses im `Worker-0` aufgerufen. Mit mehreren Arbeitsprozessen wurde er im `Manager` Prozess ausgeführt.


## v4.7.0


- Entfernte `Table\Row`, die `Table` unterstützt keine Array-Lese- und Schreiboperationen mehr


## v4.6.0



- Entfernte die maximale Begrenzung für die `session id`, es wird nicht mehr wiederholt

- Bei Verwendung von Coroutinen werden unsichere Funktionen deaktiviert, einschließlich `pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait`

- Deaktiviert standardmäßig die Coroutine Hook

- Unterstützung für PHP7.1 wird nicht mehr bereitgestellt
- `Event::rshutdown()` wurde als veraltet gekennzeichnet, bitte verwenden Sie stattdessen Coroutine\run


## v4.5.4



- `SWOOLE_HOOK_ALL` umfasst `SWOOLE_HOOK_CURL`
- Entfernte `ssl_method`, unterstützt jetzt `ssl_protocols`


## v4.4.12


- Diese Version unterstützt die WebSocket-Frame-Kompression, der dritte Parameter der push-Methode wurde in flags geändert, wenn strict_types nicht festgelegt ist, ist die Code-Kompatibilität nicht beeinträchtigt, andernfalls tritt ein Typfehler auf, bei dem bool nicht implizit in int umgewandelt werden kann, dieser Fehler wird in v4.4.13 behoben


## v4.4.1


- Registrierte Signale dienen nicht mehr als Bedingung für das Aufrechterhalten des Ereigniskreises, **Wenn ein Programm nur Signale registriert hat und keine weiteren Arbeiten durchgeführt hat, wird es als frei angesehen und sofort beendet** (in diesem Fall kann durch die Registrierung eines Timers verhindert werden, dass der Prozess beendet wird)


## v4.4.0



- Gemäß der offiziellen PHP-Entwicklung, Unterstützung für PHP7.0 wird nicht mehr bereitgestellt (@matyhtf)

- Entfernte das `Serialize`-Modul, wird in der separaten [ext-serialize](https://github.com/swoole/ext-serialize)-Erweiterung gepflegt

- Entfernte das `PostgreSQL`-Modul, wird in der separaten [ext-postgresql](https://github.com/swoole/ext-postgresql)-Erweiterung gepflegt

- `Runtime::enableCoroutine` aktiviert sich nicht mehr automatisch für die Kompatibilität innerhalb und außerhalb von Coroutinen, sobald es aktiviert ist, müssen alle blockierenden Operationen innerhalb von Coroutinen aufgerufen werden (@matyhtf)
- Aufgrund der Einführung eines neuen Coroutine `MySQL`-Client-Treibers ist das untere Design genauer, aber es gibt einige kleine unscharfe Veränderungen nach unten (@matyhtf)


## v4.3.0


- Entfernte alle asynchronen Module, siehe [Unabhängige asynchrone Erweiterungen](https://wiki.swoole.com/wiki/page/p-async_ext.html) oder [4.3.0-Update-Log](https://wiki.swoole.com/wiki/page/p-4.3.0.html)


## v4.2.13

> Aufgrund unvermeidlicher inkompatibilitätsveränderungen aufgrund von Problemen im historischen API-Design

* Veränderung des Subskriptionsmodells des Coroutine Redis-Clients, siehe [Subskriptionsmodell](https://wiki.swoole.com/#/coroutine_client/redis?id=%e8%ae%a2%e9%98%85%e6%a8%a1%e5%bc%8f)


## v4.2.12

> Experimentelle Funktion + Aufgrund unvermeidlicher inkompatibilitätsveränderungen aufgrund von Problemen im historischen API-Design


- Entfernte den `task_async` Konfigurations项, ersetzt durch [task_enable_coroutine](https://wiki.swoole.com/#/server/setting?id=task_enable_coroutine)


## v4.2.5


- Entfernte die Unterstützung für `UDP`-Clients in `onReceive` und `Server::getClientInfo`


## v4.2.0


-修真彻底删除了异步`swoole_http2_client`, 请使用协程HTTP2客户端


## v4.0.4

Ab dieser Version wird der asynchrone `Http2\Client` eine `E_DEPRECATED`-Warnung auslösen und in der nächsten Version entfernt, bitte verwenden Sie `Coroutine\Http2\Client` als Ersatz

Die `body`-Eigenschaft von `Http2\Response` wurde in `data` umbenannt, dieser Wechsel dient dazu, die Einheit von `request` und `response` zu gewährleisten und entspricht den Frame-Typnamen des HTTP2-Protokolls

Ab dieser Version verfügt `Coroutine\Http2\Client` über eine relativ vollständige Unterstützung für das HTTP2-Protokoll, die die Produktionsumgebung von Unternehmensanwendungen erfüllen kann, wie z.B. `grpc`, `etcd`, usw., daher sind die Veränderungen im Zusammenhang mit HTTP2 sehr notwendig


## v4.0.3

Lassen Sie `swoole_http2_response` und `swoole_http2_request` konsistent sein, alle Eigennamen wurden in Plural geändert, einschließlich der folgenden Eigenschaften



- `headers`
- `cookies`


## v4.0.2

> Aufgrund der zu komplexen unteren Implementierung, die schwer zu pflegen ist und die Nutzer oft falsche Vorstellungen von ihrem Einsatz haben, werden die folgenden APIs vorübergehend entfernt:


- `Coroutine\Channel::select`

Gleichzeitig wurde der zweite Parameter von `Coroutine\Channel->pop` auf `timeout` geändert, um die Entwicklungsbedürfnisse zu erfüllen


## v4.0

> Aufgrund der Aktualisierung des Coroutine-Kern, kann Coroutine überall in einer beliebigen Funktion aufgerufen werden, ohne besondere Behandlung erforderlich zu sein, daher wurden die folgenden APIs entfernt


- `Coroutine::call_user_func`
- `Coroutine::call_user_func_array`
