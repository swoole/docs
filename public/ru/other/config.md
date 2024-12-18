# Конфигурация INI

Конфигурация | По умолчанию | Роль
---|---|---
swoole.enable_coroutine | On | Откройте/закройте встроенные корутины, [подробности смотрите здесь](/server/setting?id=enable_coroutine).
swoole.display_errors | On | Включите/выключите информацию об ошибках Swoole.
swoole.unixsock_buffer_size | 8M | Установите размер буфера Socket для межпроцессного общения, что эквивалентно [socket_buffer_size](/server/setting?id=socket_buffer_size).
swoole.use_shortname | On | Включите/выключите использование коротких алиасов, [подробности смотрите здесь](/other/alias?id=корутинные короткие имена).
swoole.enable_preemptive_scheduler | Off | Остановите возможность некоторых корутин занимать слишком много CPU времени (多于 10 мс), что может привести к тому, что другие корутины не получат [dispath](https://github.com/swoole/swoole-src/tree/master/tests/swoole_coroutine_scheduler/preemptive).
swoole.enable_library | On | Включите/выключите встроенные библиотеки расширения.
