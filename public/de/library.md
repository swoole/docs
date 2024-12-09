# Bibliothek

Nach der Version v4 ist Swoole mit dem [Bibliothek](https://github.com/swoole/library)-Modul eingebaut, das **Kernfunktionen in PHP-Code schreibt**, um die Grundlegenden Einrichtungen stabiler und zuverlässiger zu machen.

!> Dieses Modul kann auch einzeln über Composer installiert werden. Bei der Installation über Composer muss in der `php.ini` die Einstellung `swoole.enable_library=Off` gemacht werden, um die in der Erweiterung eingebauten Library-Funktionen zu deaktivieren.

Derzeit werden folgende Werkzeugkomponenten bereitgestellt:

- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) zum Warten auf parallele Coroutine-Tasks, [Dokumentation](/coroutine/wait_group)

- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI) ein FastCGI-Client, [Dokumentation](/coroutine_client/fastcgi)

- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php) ein Coroutine-Server, [Dokumentation](/coroutine/server)

- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php) eine Coroutine-Barriere, [Dokumentation](/coroutine/barrier)

- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl) für die Coroutine-化和 von CURL, [Dokumentation](/runtime?id=swoole_hook_curl)

- [Database](https://github.com/swoole/library/tree/master/src/core/Database) für die fortgeschrittenen Umhüllungen verschiedener Datenbankverbindungspools und Objekt-Agenten, [Dokumentation](/coroutine/conn_pool?id=database)

- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) für Rohdatenbankverbindungspools, [Dokumentation](/coroutine/conn_pool?id=connectionpool)

- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php) ein Prozessmanager, [Dokumentation](/process/process_manager)

- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php), [ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php) und [MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php) für objektorientierte Array- und String-Programmierung

- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php) einige bereitgestellte Coroutine-Funktionen, [Dokumentation](/coroutine/coroutine?id=函数)

- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php) für häufig verwendete Konfigurationskonstanten

- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php) für HTTP-Statuscodes

## Beispielcode

[Beispiele](https://github.com/swoole/library/tree/master/examples)
