# Unterstützungsplan

|Branch|PHP Version|Startzeit|Aktives Unterstützung bis|Sicherheitswartung bis|
|-------|------------|----------|------------------------|----------------------|
| [v4.8.x](https://github.com/swoole/swoole-src/tree/4.8.x)|7.2 - 8.2|2021-10-14|2023-10-14|2024-06-30|
| [v5.0.x](https://github.com/swoole/swoole-src/tree/5.0.x)|8.0 - 8.2|2022-01-20|2023-01-20|2023-07-20|
| [v5.1.x](https://github.com/swoole/swoole-src/tree/master)|8.0 - 8.2|2023-09-30|2025-09-30|2026-09-30|
| [v6.0.x](https://github.com/swoole/swoole-src/tree/master)|8.1 - 8.3|2024-06-23|2026-06-23|2027-06-23|

|Aktives Unterstützung|Wird aktiv von der offiziellen Entwicklergruppe unterstützt, werden gemeldete Fehler und Sicherheitsprobleme sofort behoben und nach dem normalen Prozess als offizielle Version veröffentlicht.|
|----------------------|-----------------------------------------------------------------------------------------------|
|Sicherheitswartung|Wird nur für die Behebung kritischer Sicherheitsprobleme unterstützt und nur dann offizielle Versionen veröffentlicht, wenn es notwendig ist.|


## Nicht mehr unterstützte Branches

!> Diese Versionen werden nicht mehr offiziell unterstützt, und Nutzer, die weiterhin die folgenden Versionen verwenden, sollten so schnell wie möglich auf升级, da sie möglicherweise ungeschützte Sicherheitslücken ausgesetzt sind.



- `v1.x` (2012-7-1 ~ 2018-05-14)

- `v2.x` (2016-12-30 ~ 2018-05-23)

- `v3.x` (aufgegeben)

- `v4.0.x`, `v4.1.x`, `v4.2.x`, `v4.3.x` (2018-06-14 ~ 2019-12-31)

- `v4.4.x` (2019-04-15 ~ 2020-04-30)

- `v4.5.x` (2019-12-20 ~ 2021-01-06)
- `v4.6.x`, `v4.7.x` (2021-01-06 ~ 2021-12-31)



## Versionsmerkmale

- `v1.x`: Asynchroner Rückrufmodus.

- `v2.x`: Basis auf `setjmp/longjmp` implementierte Einstack-Coroutinen, die untere Ebene ist immer noch eine asynchrone Rückrufimplementierung, die den PHP-Rufstack wechselt, nachdem ein Rückrufereignis ausgelöst wurde.

- `v4.0-v4.3`: Basis auf `boost context asm` implementierte Dualstack-Coroutinen, der Kern hat eine vollständige Coroutine-Implementierung erreicht und einen Coroutine-Scheduler auf der Grundlage von `EventLoop` implementiert.

- `v4.4-v4.8`: Implementierung von `runtime coroutine hook`, der automatisch synchron blockierende PHP-Funktionen durch asynchrone, nicht blockierende Coroutine-Funktionen ersetzt, sodass Swoole-Coroutinen die meisten PHP-Bibliotheken unterstützen können.

- `v5.0`: Vollständige Coroutine-Implementierung, Entfernung nicht-coroutine-Modul; starke Typisierung, Entfernung vieler historischer Lasten; Einführung eines neuen Swoole-cli-Betriebsmodells.
- `v5.1`: Unterstützung für Coroutine von `pdo_pgsql`, `pdo_oci`, `pdo_odbc`, `pdo_sqlite`, Verbesserung der Leistung von `Http\Server`.
- `v6.0`: Unterstützung für Mehr线程-Modus.
