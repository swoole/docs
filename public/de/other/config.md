# Ini Konfiguration

Konfiguration | Standardwert | Funktion
---|---|---
swoole.enable_coroutine | On | Schaltet die eingebauten Coroutine ein/aus, [Details](/server/setting?id=enable_coroutine).
swoole.display_errors | On | Schaltet die Fehlermeldungen von `Swoole` ein/aus.
swoole.unixsock_buffer_size | 8M | Legt die Größe des Caches für die Prozess-zu-Prozess-Kommunikation fest, entspricht [socket_buffer_size](/server/setting?id=socket_buffer_size).
swoole.use_shortname | On | Schaltet die Verwendung von kurzen Aliassen ein/aus, [Details](/other/alias?id=Coroutine-Short-Name).
swoole.enable_preemptive_scheduler | Off | Previent, dass einige Coroutine zu lange CPU-Zeit (10ms CPU-Zeit) beanspruchen und andere Coroutine den [Scheduler](/coroutine?id=Coroutine-Scheduler) nicht erhalten können, [Beispiel](https://github.com/swoole/swoole-src/tree/master/tests/swoole_coroutine_scheduler/preemptive).
swoole.enable_library | On | Schaltet die Erweiterungsinternen Library ein/aus.
