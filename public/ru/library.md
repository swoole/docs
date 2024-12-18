# Библиотека

После версии v4 Swoole встроил [Библиотеку](https://github.com/swoole/library) - модуль, который позволяет **писать ядро на PHP-коде**, что делает основные системы более стабильными и надежными.

!> Этот модуль также можно установить отдельно с помощью Composer. При отдельной установке необходимо настроить `php.ini` для отключения встроенной библиотеки с помощью `swoole.enable_library=Off`.

В настоящее время представлены следующие инструменты и компоненты:

- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) для ожидания задач сConcurrent coroutines, [документация](/coroutine/wait_group)

- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI) - клиент FastCGI, [документация](/coroutine_client/fastcgi)

- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php) - сервер с coroutines, [документация](/coroutine/server)

- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php) - барьер для coroutines, [документация](/coroutine/barrier)

- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl) - coroutine-based CURL, [документация](/runtime?id=swoole_hook_curl)

- [Database](https://github.com/swoole/library/tree/master/src/core/Database) - высокосортированные обернутые封装 для различных баз данных и proxies для объектов, [документация](/coroutine/conn_pool?id=database)

- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) - первичный connection pool, [документация](/coroutine/conn_pool?id=connectionpool)

- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php) - менеджер процессов, [документация](/process/process_manager)

- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php), [ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php), [MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php) - объектно-ориентированный стиль программирования с Array и String

- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php) - некоторые функции для coroutines, [документация](/coroutine/coroutine?id=функции)

- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php) - распространенные константы настроек

- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php) - HTTP статусные коды
