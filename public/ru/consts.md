# Константы

!> Здесь не представлены все константы, для просмотра всех констант пожалуйста посетите или установите: [ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)


## Swoole


Константа | Функция
---|---
SWOOLE_VERSION | Текущая версия Swoole, строковый тип, например 1.6.0


## Параметры конструктора


Константа | Функция
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | Использование режима Base, бизнес-кода выполняется непосредственно в процессе Reactor
[SWOOLE_PROCESS](/learn?id=swoole_process) | Использование режима Process, бизнес-кода выполняется в процессе Worker


## Типы сокетов


Константа | Функция
---|---
SWOOLE_SOCK_TCP | Создание tcp сокета
SWOOLE_SOCK_TCP6 | Создание tcp ipv6 сокета
SWOOLE_SOCK_UDP | Создание udp сокета
SWOOLE_SOCK_UDP6 | Создание udp ipv6 сокета
SWOOLE_SOCK_UNIX_DGRAM | Создание unix dgram сокета
SWOOLE_SOCK_UNIX_STREAM | Создание unix stream сокета
SWOOLE_SOCK_SYNC | Синхронный клиент


## Методы шифрования SSL


Константа | Функция
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD (по умолчанию) | -
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

!> `SWOOLE_DTLSv1_METHOD`, `SWOOLE_DTLSv1_SERVER_METHOD`, `SWOOLE_DTLSv1_CLIENT_METHOD` удалены в Swoole версии >= `v4.5.0`.


## Protocols SSL


Константа | Функция
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

!> С Swoole версии >= `v4.5.4` доступны


## Уровень логирования


Константа | Функция
---|---
SWOOLE_LOG_DEBUG |Debuggовый лог, используется только для разработки ядра
SWOOLE_LOG_TRACE |Текучий лог, может использоваться для отслеживания системных проблем, debug-лог тщательно настроен и несет ключевую информацию
SWOOLE_LOG_INFO |Обычная информация, используется только для демонстрации информации
SWOOLE_LOG_NOTICE |Уведомительная информация, в системе могут быть определенные действия, такие как перезапуск, закрытие
SWOOLE_LOG_WARNING |Предостережающая информация, в системе могут быть определенные проблемы
SWOOLE_LOG_ERROR |Ошибочная информация, в системе произошло что-то ключевое, требующее немедленного решения
SWOOLE_LOG_NONE |Соответствует отключению логической информации, логическая информация не будет вы抛出аться

!> Оба вида логов `SWOOLE_LOG_DEBUG` и `SWOOLE_LOG_TRACE` могут быть использованы только после сборки расширений Swoole с использованием [--enable-debug-log](/environment?id=debug) или [--enable-trace-log](/environment?id=debug) параметров. Даже если в нормальной версии установлен `log_level = SWOOLE_LOG_TRACE`, такие логи не будут печататься.

## Траiling метки

В онлайн-сервисах, которые постоянно обрабатывают большое количество запросов, количество логов, выброшенных на низком уровне, очень велико. Можно использовать `trace_flags` для настройки меток Trailing логов, чтобы печатать только часть Trailing логов. `trace_flags` поддерживает использование `|` или операционного символа для установки нескольких Trailing элементов.

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

Нижний уровень поддерживает следующие Trailing элементы, которые можно использовать для представления всех элементов с помощью `SWOOLE_TRACE_ALL`:

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
