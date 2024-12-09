# Server (Asynchronously Style)

Einfach einen asynchronen Serverprogramm erstellen, das `TCP`, `UDP` und [unixSocket](/learn?id=Was ist IPC) unterstützt, sowohl `IPv4` als auch `IPv6` unterstützt und Tunnel-Verschlüsselung mit einseitigen und beidseitigen Zertifikaten für `SSL/TLS` ermöglicht. Nutzer müssen sich nicht um die unteren Implementierungsdetails kümmern, sondern müssen nur die Rückruffunktionen für Netzwerk-[Ereignisse](/server/events) festlegen. Zum Beispiel finden Sie Referenzen im [Schnellstart](/start/start_tcp_server).

!> Nur der Stil des `Server`-Endes ist asynchron (d.h., alle Ereignisse müssen eine Rückruffunktion haben), aber es unterstützt auch Coroutinen. Nachdem [enable_coroutine](/server/setting?id=enable_coroutine) aktiviert wurde, werden Coroutinen unterstützt (standardmäßig aktiviert). Unter Coroutinen ist der gesamte Geschäftscode synchron geschrieben.

Lernen Sie mehr:

[Einführung in die drei Betriebsweisen des Servers](/learn?id=Einführung in die drei Betriebsweisen des Servers ':target=_blank')  
[Unterschiede zwischen Process, ProcessPool und UserProcess](/learn?id=process-diff ':target=_blank')  
[Unterschiede und Beziehungen zwischen Master-Prozess, Reactor-Thread, Worker-Prozess, Task-Prozess und Manager-Prozess](/learn?id=diff-process ':target=_blank')  


### Laufablaufbild <!-- {docsify-ignore} --> 

![running_process](https://wiki.swoole.com/_images/server/running_process.png ':size=800xauto')

### Prozess/Thread-Strukturbild <!-- {docsify-ignore} --> 

![process_structure](https://wiki.swoole.com/_images/server/process_structure.png ':size=800xauto')

![process_structure_2](https://wiki.swoole.com/_images/server/process_structure_2.png)
