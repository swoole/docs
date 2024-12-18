# сервер (асинхронный стиль)

Простое создание асинхронного серверного программы, поддерживающего 3 типа сокетов: `TCP`, `UDP`, [unixSocket](/learn?id=что такое IPC), поддержку `IPv4` и `IPv6`, поддержку туннелирования сертификатов `SSL/TLS` для одностороннего и двустороннего шифрования. Пользователи не должны заботиться о деталях реализации, им просто нужно установить обратный вызов для сетевых [событий](/server/events), пример смотрите в [быстрой настройке](/start/start_tcp_server).

!> Только стиль `Server` является асинхронным (то есть все события требуют установки обратного вызова), но он также поддерживает корутины, после включения [enable_coroutine](/server/setting?id=enable_coroutine) они становятся поддерживаемыми (по умолчанию включены), и все бизнес-коды в рамках [корутин](/coroutine) написаны синхронно.

Пусть узнаем:

[Обзор трех режимов работы сервера](/learn?id=обзор-трех-режимов-работы-сервера ':target=_blank')  
[Какие различия между Process, ProcessPool, UserProcess](/learn?id=process-diff ':target=_blank')  
[Какие различия и связи между Master процессом, Reactor线程ами, Worker процессами, Task процессами, Manager процессами](/learn?id=diff-process ':target=_blank')  


### Схема рабочего процесса <!-- {docsify-ignore} --> 

![running_process](https://wiki.swoole.com/_images/server/running_process.png ':size=800xauto')

### Схема структуры процессов/потоков <!-- {docsify-ignore} --> 

![process_structure](https://wiki.swoole.com/_images/server/process_structure.png ':size=800xauto')

![process_structure_2](https://wiki.swoole.com/_images/server/process_structure_2.png)
