# Konfiguration

Die [Swoole\Server->set()](/server/methoden?id=set)-Funktion wird zum Einstellen verschiedener Parameter für die Laufzeit des `Server` verwendet. Alle Unterseiten dieser Seite sind Elemente des Konfigurationsarrays.

!> Ab der Version [v4.5.5](/version/log?id=v455) erkennt das Underlying System, ob die eingestellten Konfigurationsoptionen korrekt sind. Wenn eine nicht von `Swoole` bereitgestellte Konfigurationsoption eingestellt ist, wird eine Warning generiert.

```shell
PHP Warning:  Unsupported option [foo] in @swoole-src/library/core/Server/Helper.php
```


### debug_mode

?> Legt den Logging-Modus auf `debug` fest, um den Debug-Modus zu aktivieren. Dies hat nur Wirkung, wenn der编译-Option `--enable-debug` aktiviert wurde.

```php
$server->set([
  'debug_mode' => true
])
```


### trace_flags

?> Legt die Tags für den Trace-Logging fest und druckt nur einen Teil der Trace-Logs aus. `trace_flags` unterstützt die Verwendung von `|` oder dem Operator zum Einstellen mehrerer Trace-Items. Dies hat nur Wirkung, wenn die Compile-Option `--enable-trace-log` aktiviert wurde.

Das Underlying System unterstützt die folgenden Trace-Items, wobei `SWOOLE_TRACE_ALL` alle Items angibt:

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


### log_file

?> **Gibt den Dateinamen für das `Swoole`-Fehler-Log an**

?> Ausnahmemeldungen, die während der Laufzeit des `Swoole`-Programms auftreten, werden in dieser Datei aufgezeichnet. Standardmäßig werden sie auf den Bildschirm ausgegeben. Wenn derDaemonisierungsmodus aktiviert ist `(daemonize => true)`, wird die Standardausgabe umgeleitet zu `log_file`. In PHP-Code werden Inhalte, die auf den Bildschirm ausgegeben werden, wie `echo/var_dump/print`, in die `log_file` geschrieben.

  * **Hinweis**

    * Die Logs in `log_file` dienen nur dem Aufzeichnen von Laufzeitfehlern und müssen nicht dauerhaft gespeichert werden.

    * **Log-Marken**

      ?> Vor den Log-meldungen wird eine Nummer hinzugefügt, die den Thread-/Prozesstyp angibt, der den Log-Eintrag generiert hat.

        * `#` Master-Prozess
        * `$` Manager-Prozess
        * `*` Worker-Prozess
        * `^` Task-Prozess

    * **Log-Datei neu öffnen**

      ?> Wenn die Log-Datei während des Betriebs des Serverprogramms mit `mv` verschoben oder mit `unlink` gelöscht wird, können die Log-meldungen nicht mehr normal geschrieben werden. In diesem Fall kann ein `SIGRTMIN`-Signal an den `Server` gesendet werden, um die Log-Datei neu zu öffnen.

      * Unterstützt nur Linux-Plattformen
      * Nicht für [UserProcess](/server/methoden?id=addProcess)-Prozesse geeignet

  * **Wichtigkeit**

    !> `log_file` wird nicht automatisch aufgeteilt, daher ist es notwendig, diese Datei regelmäßig zu bereinigen. Das Betrachten der Ausgaben von `log_file` kann verschiedene Arten von Ausnahmen und Warnungen des Servers liefern.


### log_level

?> **Legt das Niveau fest, auf das Fehlerlogs des `Server` ausgegeben werden sollen, im Bereich von `0-6`. Logs, die unterhalb des festgelegten `log_level` liegen, werden nicht ausgegeben.**【Standardwert: `SWOOLE_LOG_INFO`】

Referenz für entsprechende Level-Konstanten [Log-Levels](/constants?id=log_levels)

  * **Wichtigkeit**

    !> `SWOOLE_LOG_DEBUG` und `SWOOLE_LOG_TRACE` sind nur für Versionen verfügbar, die mit [--enable-debug-log](/environment?id=debug-parameter) und [--enable-trace-log](/environment?id=debug-parameter) compiliert wurden;  
    Wenn der Daemonisierungsmodus aktiviert ist, wird der gesamte Bildschirmausgabe in den [log_file](/server/setting?id=log_file) geschrieben, unabhängig vom `log_level`.


### log_date_format

?> **Legt das Datumsformat für die Server-Logs fest**, Referenz für das `format` in [strftime](https://www.php.net/manual/de/function.strftime.php)

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```


### log_date_with_microseconds

?> **Legt die Genauigkeit der Server-Logs fest, ob sie mit Mikrosekunden angegeben werden sollen**【Standardwert: `false`】


### log_rotation

?> **Legt die Protokollierung von Server-Logs fest**【Standardwert: `SWOOLE_LOG_ROTATION_SINGLE`】

| Konstante                         | Beschreibung   | Versionsinformationen |
| --------------------------------- | -------------- | --------------------- |
| SWOOLE_LOG_ROTATION_SINGLE       | Nicht aktiviert | -                     |
| SWOOLE_LOG_ROTATION_MONTHLY      | Monatlich     | v4.5.8                |
| SWOOLE_LOG_ROTATION_DAILY        | Täglich       | v4.5.2                |
| SWOOLE_LOG_ROTATION_HOURLY       | Stündlich     | v4.5.8                |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE | Minute für Minute | v4.5.8                |


### display_errors

?> Schaltet die Darstellung von `Swoole`-Fehlermeldungen ein / aus.

```php
$server->set([
  'display_errors' => true
])
```


### dns_server

?> Setzt die IP-Adresse für DNS-Abfrage.


### socket_dns_timeout

?> Die Zeit, nach der eine Domain-Namensauflösung timeoutet, wenn auf der Serverseite Coroutine-Clients aktiviert sind, kann dieses Parameter die Timeoutzeit für die Domain-Namensauflösung der Clients steuern, in Sekunden.


### socket_connect_timeout

?> Die Zeit, nach der eine Clientverbindung timeoutet, wenn auf der Serverseite Coroutine-Clients aktiviert sind, kann dieses Parameter die Timeoutzeit für die Clientverbindung steuern, in Sekunden.


### socket_write_timeout / socket_send_timeout

?> Die Zeit, nach der eine Clientverbindung timeoutet, wenn auf der Serverseite Coroutine-Clients aktiviert sind, kann dieses Parameter die Timeoutzeit für das Schreiben an die Clientverbindung steuern, in Sekunden.   
Diese Konfiguration kann auch verwendet werden, um die Timeoutzeit für die Ausführung von `shell_exec` oder [Swoole\Coroutine\System::exec()](/coroutine/system?id=exec) nach der Coroutineisierung zu steuern.   


### socket_read_timeout / socket_recv_timeout

?> Die Zeit, nach der eine Clientverbindung timeoutet, wenn auf der Serverseite Coroutine-Clients aktiviert sind, kann dieses Parameter die Timeoutzeit für das Lesen von der Clientverbindung steuern, in Sekunden.


### max_coroutine / max_coro_num :id=max_coroutine

?> **Legt die maximale Anzahl von Coroutinen für den aktuellen Arbeitsprozess fest.**【Standardwert: `100000`, bei Swoole-Versionen vor `v4.4.0-beta` ist der Standardwert `3000`】

?> Wenn die Anzahl der Coroutinen die `max_coroutine` übersteigt, kann das Underlying System keine neuen Coroutinen mehr erstellen. Ein `Swoole`-Server auf Serverseite wirft einen Fehler mit dem Text `exceed max number of coroutine` aus, ein TCP-Server schließt die Verbindung direkt, und ein HTTP-Server gibt den HTTP-Statuscode 503 zurück.

?> Die tatsächlich maximal 创建Coroutinen Anzahl im `Server`-Programm ist gleich `worker_num * max_coroutine`, die Anzahl der Coroutinen für task-Prozesse und UserProcess-Prozesse wird einzeln berechnet.

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```


### enable_deadlock_check

?> Schaltet die Überprüfung von Deadlocks bei Coroutinen ein.

```php
$server->set([
  'enable_deadlock_check' => true
]);
```


### hook_flags

?> **Legt den Bereich der Funktionen fest, die für die "One-Click"-Coroutineisierung Hooks verwendet werden sollen.**【Standardwert: Keine Hooks】

!> Swoole-Versionen ab `v4.5+` oder [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) sind verfügbar, 자세liche Informationen finden Sie unter [One-Click-Coroutineisierung](/runtime)

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
Das Underlying System unterstützt die folgenden Coroutineisierung-Items, wobei `SWOOLE_HOOK_ALL` alle Items angibt:

* `SWOOLE_HOOK_TCP`
* `SWOOLE_HOOK_UNIX`
* `SWOOLE_HOOK_UDP`
* `SWOOLE_HOOK_UDG`
* `SWOOLE_HOOK_SSL`
* `SWOOLE_HOOK_TLS`
* `SWOOLE_HOOK_SLEEP`
* `SWOOLE_HOOK_FILE`
* `SWOOLE_HOOK_STREAM_FUNCTION`
* `SWOOLE_HOOK_BLOCKING_FUNCTION`
* `SWOOLE_HOOK_PROC`
* `SWOOLE_HOOK_CURL`
* `SWOOLE_HOOK_NATIVE_CURL`
* `SWOOLE_HOOK_SOCKETS`
* `SWOOLE_HOOK_STDIO`
* `SWOOLE_HOOK_PDO_PGSQL`
* `SWOOLE_HOOK_PDO_ODBC`
* `SWOOLE_HOOK_PDO_ORACLE`
* `SWOOLE_HOOK_PDO_SQLITE`
* `SWOOLE_HOOK_ALL`
### enable_preemptive_scheduler

?> Um die Vorteilsnahme-Scheduler für Coroutinen zu aktivieren und zu vermeiden, dass eine Coroutine zu lange läuft und andere Coroutinen verhungern lässt, wird die maximale Ausführungszeit einer Coroutine auf `10ms` festgelegt.

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```

### c_stack_size / stack_size

?> Legt die Größe der anfänglichen C-Stack-Speicher für eine einzelne Coroutine fest, der Standardwert beträgt 2M.

### aio_core_worker_num

?> Legt die Anzahl der minimalen AIO-Arbeitsthreads fest, der Standardwert beträgt die Anzahl der CPU-Kerne.

### aio_worker_num 

?> Legt die Anzahl der maximalen AIO-Arbeitsthreads fest, der Standardwert beträgt die Anzahl der CPU-Kerne * 8.

### aio_max_wait_time

?> Die maximale Wartezeit für Arbeitsthreads in Sekunden.

### aio_max_idle_time

?> Die maximale Leerlaufzeit für Arbeitsthreads in Sekunden.

### reactor_num

?> **Legt die Anzahl der gestarteten [Reactor](/learn?id=reactor线程) Threads fest.**【Standardwert: Anzahl der CPU-Kerne】

?> Durch diesen Parameter kann die Anzahl der Ereignisverarbeitungsthreads im Hauptprozess reguliert werden, um die Nutzung mehrerer Kerne voll auszuschöpfen. Standardmäßig werden so viele Threads aktiviert, wie es CPU-Kerne gibt.  
Reactor-Threads können mehrere Kerne nutzen, zum Beispiel: Wenn ein Maschinen 128 Kerne hat, werden unterhalb 128 Threads gestartet.  
Jeder Thread hält eine [EventLoop](/learn?id=什么是eventloop) aufrecht. Zwischen den Threads herrscht kein Lock, und Anweisungen können von 128 Kern-CPUs parallel ausgeführt werden.  
Angesichts des Performance-Verlusts durch die Betriebssystem-Scheduler kann der Wert auf CPU-Kernanzahl * 2 gesetzt werden, um den gesamten Nutzung jedes Kerns der CPU zu maximieren.

  * **Hinweis**

    * `reactor_num` sollte auf `1-4` Mal der Anzahl der CPU-Kerne festgelegt werden
    * `reactor_num` darf nicht höher als [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 4 sein

  * **Beachtung**


  !> -`reactor_num` muss kleiner oder gleich `worker_num` sein;  

- Wenn `reactor_num` größer als `worker_num` ist, wird automatisch angepasst, sodass `reactor_num` gleich `worker_num` wird;  
- Bei Maschinen mit mehr als 8 Kernen ist `reactor_num` standardmäßig auf 8 gesetzt.
	

### worker_num

?> **Legt die Anzahl der gestarteten `Worker` Prozesse fest.**【Standardwert: Anzahl der CPU-Kerne】

?> Wenn ein Request 100ms dauert und eine Verarbeitungskapazität von 1000QPS bereitgestellt werden soll, müssen mindestens 100 Prozesse oder mehr konfiguriert werden.  
Aber je mehr Prozesse geöffnet werden, desto mehr RAM wird beansprucht, und die Kosten für Prozessewitching werden immer größer. Daher sollte dies angemessen sein und nicht zu groß sein.

  * **Hinweis**

    * Wenn das Geschäftslogikcode vollständig [asynchron IO](/learn?id=同步io异步io) ist, ist es am vernünftigsten, hier die Anzahl der CPU-Kerne auf `1-4` Mal festzulegen
    * Wenn das Geschäftslogikcode [synchron IO](/learn?id=同步io异步io) ist, muss es nach der Antwortzeit des Requests und der Systemlast angepasst werden, zum Beispiel: `100-500`
    * Der Standardwert ist [swoole_cpu_num()](/functions?id=swoole_cpu_num), und darf nicht höher als [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000 sein
    * Angenommen, jeder Prozess verbraucht 40M RAM, dann benötigen 100 Prozesse 4G RAM.


### max_request

?> **Legt die maximale Anzahl von Aufgaben für `worker` Prozesse fest.**【Standardwert: `0`, das heißt der Prozess wird nicht beendet】

?> Ein `worker` Prozess wird automatisch beendet, nachdem er mehr Aufgaben als dieser Wert bearbeitet hat, und der Prozess wird alle Erinnerungen und Ressourcen freisetzen, nachdem er beendet wurde

!> Dieser Parameter dient hauptsächlich dazu, Probleme mit PHP-Prozess-Speicherlecks durch mangelnde Programmcodierung zu lösen. PHP-Anwendungen haben einen langsamen Speicherleck, aber es ist nicht möglich, die spezifische Ursache zu identifizieren oder zu lösen. Dies kann vorübergehend durch das Festlegen von `max_request` gelöst werden, aber es ist notwendig, den Speicherleck im Code zu finden und zu reparieren, anstatt dies durch diesen Ansatz zu tun. Swoole Tracker kann verwendet werden, um den leckenden Code zu entdecken.

  * **Hinweis**

    * Das Erreichen von max_request bedeutet nicht unbedingt, dass der Prozess sofort geschlossen wird, siehe [max_wait_time](/server/setting?id=max_wait_time).
    * Unter [SWOOLE_BASE](/learn?id=swoole_base) führt das Herunterfahren des Prozesses aufgrund des Erreichens von max_request zum Unterbrechen der Clientverbindungen.

  !> Wenn ein tödlicher Fehler im `worker` Prozess auftritt oder der Prozess manuell mit `exit` beendet wird, wird der Prozess automatisch beendet. Der `master` Prozess wird einen neuen `worker` Prozess starten, um weiterhin Anfragen zu verarbeiten


### max_conn / max_connection

?> **Serverprogramm, maximale Anzahl zulässiger Verbindungen.**【Standardwert: `ulimit -n`】

?> Wenn `max_connection => 10000`, wird dieser Parameter verwendet, um die maximale Anzahl von TCP-Verbindungen zu bestimmen, die der Server aufrechterhalten darf. Wenn die Anzahl der Verbindungen diese Grenze überschreitet, werden neue Verbindungen abgelehnt.

  * **Hinweis**

    * **Standardauswahl**

      * Wenn die Anwendungsebene keine `max_connection` festlegt, wird der Standardwert von `ulimit -n` verwendet
      * In Versionen ab `4.2.9`, wenn die Basisebene feststellt, dass `ulimit -n` über `100000` liegt, wird der Standardwert auf `100000` gesetzt, da einige Systeme `ulimit -n` auf `1 Million` setzen und viel RAM benötigen, was das Starten des Prozesses verhindert

    * **Maximaler Höchstwert**

      * Bitte stellen Sie `max_connection` nicht höher als `1M`

    * **Minimaler Einstellung**    
      
      * Wenn dieser Wert zu klein festgelegt ist, wird eine Fehlermeldung ausgegeben und der Wert von `ulimit -n` verwendet.
      * Der Mindestwert beträgt `(worker_num + task_worker_num) * 2 + 32`

    ```shell
    serv->max_connection ist zu klein.
    ```

    * **Speicherkonsum**

      * Der `max_connection` Parameter sollte nicht zu groß angepasst werden, er sollte auf der tatsächlichen RAM-Verfügbarkeit des Geräts basieren. Swoole wird basierend auf diesem Wert einmalig einen großen Block an RAM zuweisen, um `Connection` Informationen zu speichern, die für eine TCP-Verbindung 224 Byte benötigen.

  * **Beachtung**

  !> `max_connection` darf nicht höher sein als der Wert von `ulimit -n` des Betriebssystems, sonst wird eine Warnung ausgegeben und es wird auf den Wert von `ulimit -n` zurückgesetzt.

  ```shell
  WARN swServer_start_check: serv->max_conn ist die maximale waarde überschritten[100000].

  WARNING set_max_connection: max_connection ist die maximale waarde überschritten, es wird auf 10240 zurückgesetzt
  ```


### task_worker_num

?> **Konfiguriert die Anzahl der [Taskprozesse](/learn?id=taskworker进程).**

?> Nachdem dieser Parameter konfiguriert wurde, wird die `task`-Funktion aktiviert. Daher muss der Server die Ereignis回调funktionen [onTask](/server/events?id=ontask) und [onFinish](/server/events?id=onfinish) registrieren. Wenn sie nicht registriert sind, kann das Serverprogramm nicht gestartet werden.

  * **Hinweis**

    * [Taskprozesse](/learn?id=taskworker进程) sind synchron und blockierend

    * Der Höchstwert darf nicht höher sein als [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000
    
    * **Berechnungsmethode**
      * Die Verarbeitungsdauer einer einzelnen `task`, zum Beispiel `100ms`, bedeutet, dass ein Prozess in 1 Sekunde `1/0.1=10` tasks verarbeiten kann
      * Die Geschwindigkeit der `task`-Übergabe, zum Beispiel 2000 tasks pro Sekunde
      * `2000/10=200`, daher muss `task_worker_num => 200` festgelegt werden, um 200 Taskprozesse zu aktivieren

  * **Beachtung**

    !> - [Taskprozesse](/learn?id=taskworker进程) dürfen die Methode `Swoole\Server->task` nicht innerhalb von [Taskprozessen](/learn?id=taskworker进程) verwenden
### task_ipc_mode

?> **Setzt die Art der Kommunikation zwischen dem [Task-Prozess](/learn?id=taskworkerprozess) und dem `Worker`-Prozess.**【Standardwert: `1`】 
 
?> Bitte lesen Sie zuerst über die IPC-Kommunikation unter [Swoole](/learn?id=was-ist-ipc).


Modus | Funktion
---|---
1 | Verwende `Unix Socket` für die Kommunikation【Standardmodus】
2 | Verwende `sysvmsg` Nachrichtenschleife für die Kommunikation
3 | Verwende `sysvmsg` Nachrichtenschleife für die Kommunikation und setzt sie auf Wettbewerbmodus

  * **Hinweis**

    * **Modus `1`**
      * Bei Verwendung von Modus `1` wird Unterstützung für gezielte Zustellung ermöglicht. Sie können im [task](/server/methods?id=task) und im [taskwait](/server/methods?id=taskwait) Methode den `dst_worker_id` verwenden, um das Ziel `Task-Prozess` anzugeben.
      * Wenn `dst_worker_id` auf `-1` gesetzt ist, wird der untere Layer den Zustand jedes [Task-Prozess](/learn?id=taskworkerprozess) überprüfen und Aufgaben nur an Prozesse mit dem aktuellen Zustand als frei zustellen.

    * **Modus `2` und `3`**
      * Nachrichtenschleifenmodus verwendet den von der Betriebssystem bereitgestellten Speicher für die Speicherung von Daten. Wenn kein `message_queue_key` für die Nachrichtenschleife angegeben ist, wird eine private Schleife verwendet, die nach dem Beenden des `Server`-Programms gelöscht wird.
      * Nachdem der `Server`-Programm mit einem angegebenen `message_queue_key` beendet wurde, werden die Daten in der Nachrichtenschleife nicht gelöscht, sodass die Prozesse die Daten nach dem Neustart immer noch abrufen können
      * Sie können Daten aus der Nachrichtenschleife manuell mit `ipcrm -q` und der Nachrichtenschleifen-ID löschen
      * Der Unterschied zwischen Modus `2` und Modus `3` besteht darin, dass Modus `2` gezielte Zustellung unterstützt, `$serv->task($data, $task_worker_id)` kann angeben, an welchen [task-Prozess](/learn?id=taskworkerprozess) die Aufgabe zugestellt werden soll. Modus `3` ist ein vollständiger Wettbewerbmodus, [task-Prozesse](/learn?id=taskworkerprozess) werden um die Schleife konkurrieren, es ist nicht möglich, gezielte Zustellung zu verwenden, und `task/taskwait` können das Ziel-Prozess-ID nicht angeben, selbst wenn `$task_worker_id` angegeben ist, ist es im Modus `3` ungültig.

  * **Hinweis**

    !> -Modus `3` kann die [sendMessage](/server/methods?id=sendMessage) Methode beeinflussen, sodass die Nachrichten, die mit [sendMessage](/server/methods?id=sendMessage) gesendet werden, zufällig von einem bestimmten [task-Prozess](/learn?id=taskworkerprozess) empfangen werden.  
    -Bei der Verwendung von Nachrichtenschleifen für die Kommunikation kann, wenn die Verarbeitungskapazität des `Task-Prozesses` unter der Geschwindigkeit der Zustellung liegt, zu einer Blockierung des `Worker-Prozesses` führen.  
    -Nachdem Nachrichtenschleifen für die Kommunikation verwendet wurden, können `task-Prozesse` keine Coroutinen unterstützen (siehe [task_enable_coroutine](/server/setting?id=task_enable_coroutine)).  


### task_max_request

?> **Setzt die maximale Anzahl von Aufgaben für den [task-Prozess](/learn?id=taskworkerprozess).**【Standardwert: `0`】

Stellt die maximale Anzahl von Aufgaben für den `task-Prozess` fest. Ein `task-Prozess` wird automatisch beendet, nachdem er mehr Aufgaben als diese Zahl verarbeitet hat. Dieser Parameter dient dazu, eine Überlaufung des PHP-Prozess-Speichers zu verhindern. Wenn Sie nicht möchten, dass der Prozess automatisch beendet wird, können Sie ihn auf `0` setzen.


### task_tmpdir

?> **Setzt den temporären Verzeichnis für task-Daten.**【Standardwert: Linux `/tmp` Directory】

?> Im `Server`, wenn die gesendeten Daten mehr als `8180` Byte betragen, werden temporäre Dateien zum Speichern der Daten aktiviert. Hier wird das `task_tmpdir` verwendet, um den Ort festzulegen, an dem die temporären Dateien gespeichert werden.

  * **Hinweis**

    * Der untere Layer verwendet standardmäßig das `/tmp` Directory zum Speichern von `task` Daten. Wenn Ihr Linux-Kernel-Version zu niedrig ist und das `/tmp` Directory kein Memory-File-System ist, kann es auf `/dev/shm/` gesetzt werden
    * Wenn das `task_tmpdir` Verzeichnis nicht existiert, wird der untere Layer versuchen, es automatisch zu erstellen

  * **Hinweis**

    !> -Falls die Erstellung fehlschlägt, wird der `Server->start` fehlschlagen


### task_enable_coroutine

?> **Aktiviert die Unterstützung für Task-Coroutinen.**【Standardwert: `false`】, ab v4.2.12 unterstützt

?> Wenn aktiviert, werden automatisch Coroutinen und [Coroutine-Containers](/coroutine/scheduler) in der [onTask](/server/events?id=ontask) Rückruf-Funktion erstellt, und PHP-Code kann direkt die Coroutine `API` verwenden.

  * **Beispiel**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    //von welchem Worker-Prozess kommt es
    $task->worker_id;
    //die Nummer der Aufgabe
    $task->id;
    //die Art der Aufgabe, taskwait, task, taskCo, taskWaitMulti könnten unterschiedliche Flags verwenden
    $task->flags;
    //die Daten der Aufgabe
    $task->data;
    //die Zeit der Zustellung, v4.6.0 version hinzugefügt
    $task->dispatch_time;
    //Coroutine API
    co::sleep(0.2);
    //Fertige Aufgabe beenden und Daten zurückgeben
    $task->finish([123, 'hello']);
});
```

  * **Hinweis**

    !> -`task_enable_coroutine` kann nur verwendet werden, wenn [enable_coroutine](/server/setting?id=enable_coroutine) auf `true` gesetzt ist  
    -Wenn `task_enable_coroutine` aktiviert ist, unterstützen `Task` Arbeitsprozesse Coroutinen  
    -Wenn `task_enable_coroutine` nicht aktiviert ist, wird nur synchrone Blockierung unterstützt


### task_use_object/task_object :id=task_use_object

?> **Verwende einen objektorientierten Stil für Task-Rückruf-Format.**【Standardwert: `false`】

?> Wenn auf `true` gesetzt, wird der [onTask](/server/events?id=ontask) Rückruf in einem Objektstil geändert.

  * **Beispiel**

```php
<?php

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 3,
    'task_use_object' => true,
//    'task_object' => true, // v4.6.0 version hinzugefügtes Synonym
]);
$server->on('receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd' => $fd,]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    //hier ist $task ein Swoole\Server\Task Objekt
    $server->send($task->data['fd'], json_encode($server->stats()));
});
$server->start();
```


### dispatch_mode

?> **Strategie für das Verteilung von Datenpaketen.**【Standardwert: `2`】


Wert des Modus | Modus | Funktion
---|---|---
1 | Rundumverteilung | Wird empfangen, wird es um rundum an jeden `Worker`-Prozess verteilt
2 | feste Verteilung | Verteilung anhand des Dateideskriptors der Verbindung. Dies kann sicherstellen, dass Daten, die über dieselbe Verbindung kommen, nur von einem bestimmten `Worker` verarbeitet werden
3 | Übernahme-Modus | Der Hauptprozess wählt basierend auf dem Arbeitsstatus des `Worker` aus, um zu liefern, und liefert nur an处于 idle-Statusen `Worker`
4 | IP-Verteilung | Verteilung basierend auf dem Client-IP durch Modulo-Hash, um einen festen `Worker`-Prozess zu vergeben.<br>Kann sicherstellen, dass Datenverbindungen aus derselben IP-Quelle immer an denselben `Worker`-Prozess verteilt werden. Algorithmus: `inet_addr_mod(ClientIP, worker_num)`
5 | UID-Verteilung | Bedarf es von Code im Benutzercode, um [Server->bind()](/server/methods?id=bind) aufzurufen und eine Verbindung mit einem `uid` zu binden. Dann verteilt der untere Layer basierend auf dem Wert von `UID` an verschiedene `Worker`-Prozesse.<br>Algorithmus: `UID % worker_num`, wenn ein String als `UID` verwendet werden soll, kann `crc32(UID_STRING)` verwendet werden
7 | Stream-Modus | Leere `Worker` werden Verbindungen akzeptieren und neue Anforderungen von [Reactor](/learn?id=reactor线程) akzeptieren

  * **Hinweis**

    * **Nutzungsempfehlung**
    
      * Bei statusfreien `Server` kann `1` oder `3` verwendet werden, bei synchron blockierenden `Server` kann `3` verwendet werden, bei asynchron nicht blockierenden `Server` kann `1` verwendet werden
      * Bei statusbehafteten verwendet man `2`, `4`, `5`
      
    * **UDP-Protokoll**

      * Bei `dispatch_mode=2/4/5` wird fest verteilt, der untere Layer verwendet den Client-IP für Modulo-Hashing zu verschiedenen `Worker`-Prozessen
      * Bei `dispatch_mode=1/3` wird zufällig zu verschiedenen `Worker`-Prozessen verteilt
      * `inet_addr_mod` Funktion

```
    function inet_addr_mod($ip, $worker_num) {
        $ip_parts = explode('.', $ip);
        if (count($ip_parts) != 4) {
            return false;
        }
        $ip_parts = array_reverse($ip_parts);
    
        $ip_long = 0;
        foreach ($ip_parts as $part) {
            $ip_long <<= 8;
            $ip_long |= (int) $part;
        }
    
        return $ip_long % $worker_num;
    }
```
  * **Base-Modus**
    * Bei der Konfiguration von `dispatch_mode` im [SWOOLE_BASE](/learn?id=swoole_base) Modus ist es nicht gültig, da im `BASE` Modus keine Aufgaben verteilt werden. Wenn Daten vom Client empfangen werden, wird sofort im aktuellen Thread/Prozess der [onReceive](/server/events?id=onreceive) Rückruf-Funktion behandelt und es ist nicht notwendig, die `Worker`-Prozesse zu verteilen.

  * **Hinweis**

    !> -Bei `dispatch_mode=1/3` werden untere Layer Ereignisse wie `onConnect/onClose` blockiert, da in diesen beiden Modi die Reihenfolge von `onConnect/onClose/onReceive` nicht garantiert werden kann;  
    -Für nicht-reagierende Serverprogramme sollten Sie bitte nicht den Modus `1` oder `3` verwenden. Zum Beispiel: HTTP-Dienste sind reagierend und können `1` oder `3` verwenden, TCP-Long-Connection-Statuse dürfen nicht `1` oder `3` verwenden.
### dispatch_func

?> Legen Sie die `dispatch`-Funktion fest, Swoole hat unter der Oberfläche sechs verschiedene [dispatch_mode](/server/setting?id=dispatch_mode) eingebaut, und wenn dies immer noch nicht ausreicht, können Sie eine `C++`-Funktion oder eine `PHP`-Funktion schreiben, um die `dispatch`-Logik zu implementieren.

  * **Verwendungsweise**

```php
$server->set(array(
  'dispatch_func' => 'my_dispatch_function',
));
```

  * **Hinweise**

    * Nachdem die `dispatch_func` festgelegt wurde, ignoriert die Basisunterseite automatisch die `dispatch_mode`-Konfiguration
    * Wenn die `dispatch_func`-entsprechende Funktion nicht vorhanden ist, wirft die Basisunterseite einen tödlichen Fehler
    * Wenn Sie einen Paket über 8K versenden möchten, kann die `dispatch_func` nur die Inhalte von `0-8180` Bytes abrufen

  * **Schreiben einer PHP-Funktion**

    ?> Da das `ZendVM` keine Multithreadumgebung unterstützen kann, kann auch bei der Einstellung mehrerer [Reactor](/learn?id=reactor线程) Threads nur eine `dispatch_func` gleichzeitig ausgeführt werden. Daher führt die Basisunterseite beim Ausführen dieser PHP-Funktion eine Lock-Operation durch, was möglicherweise zu Problemen mit dem Lock-Wettbewerb führen kann. Bitte führen Sie keine blockierenden Operationen in der `dispatch_func` durch, sonst wird die Gruppe der `Reactor`-Threads ihre Arbeit stoppen.

    ```php
    $server->set(array(
        'dispatch_func' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd` ist ein einzigartiges Identifikator für die Clientverbindung, der mit `Server::getClientInfo` abgerufen werden kann
    * `$type` ist die Datentyp der Daten, `0` bedeutet Daten von einem Client, `4` bedeutet dass eine Clientverbindung eingerichtet wurde, `3` bedeutet dass eine Clientverbindung geschlossen wurde
    * `$data` ist der Inhalt der Daten, beachten Sie: Wenn Protokollparameter wie `HTTP`, `EOF`, `Length` aktiviert sind, wird die Basisunterseite Pakete zusammenfügen. Aber in der `dispatch_func` kann nur der erste 8K des Datapakets übergeben werden, man kann den vollständigen Paketinhalt nicht erhalten.
    * **Muss** einen Zahlenwert zwischen `0` und `(server->worker_num - 1)` zurückgeben, der die ID des Zielarbeitss进程`ID` des Pakets darstellt
    * Ein Wert kleiner als `0` oder größer oder gleich `server->worker_num` ist einausgewählter `ID`, und die gesendeten Daten werden abgelehnt

  * **Schreiben einer C++-Funktion**

    **In anderen PHP-Erweiterungen müssen Sie die Länge der Funktion mit swoole_add_function registrieren, um sie in den Swoole-Motor einzuführen.**

    ?> Bei der Anrufung einer C++-Funktion wird die Basisunterseite keinen Lock durchführen, und die调用方 muss die Threadsicherheit selbst gewährleisten

    ```c++
    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data);

    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data)
    {
        printf("cpp, type=%d, size=%d\n", data->info.type, data->info.len);
        return data->info.len % serv->worker_num;
    }

    int register_dispatch_function(swModule *module)
    {
        swoole_add_function("my_dispatch_function", (void *) dispatch_function);
    }
    ```

    * Die `dispatch`-Funktion muss das Ziel `worker`进程`id` zurückgeben
    * Die zurückgegebene `worker_id` darf nicht größer als `server->worker_num` sein, sonst wirft die Basisunterseite einen Segfault
    * Ein negatives Ergebnis `(return -1)` bedeutet, dass dieses Paket abgelehnt wird
    * `data` kann den Typ und die Länge des Ereignisses lesen
    * `conn` ist Informationen über die Verbindung, wenn es sich um ein `UDP`-Paket handelt, ist `conn` `NULL`

  * **Hinweise**

    !> -`dispatch_func` ist nur in der [SWOOLE_PROCESS](/learn?id=swoole_process) Modus gültig, und es ist für Servertypen wie [UDP/TCP/UnixSocket](/server/methods?id=__construct) gültig  
    - Die zurückgegebene `worker_id` darf nicht größer als `server->worker_num` sein, sonst wirft die Basisunterseite einen Segfault


### message_queue_key

?> **Stellen Sie die `KEY` für die Nachrichtenschlange fest.**【Standardwert: `ftok($php_script_file, 1)`】

?> Wird nur verwendet, wenn [task_ipc_mode](/server/setting?id=task_ipc_mode) = 2/3 ist. Die festgelegte `Key` dient nur als `Key` für die Task-Task-Schlange, siehe [IPC-Kommunikation unter Swoole](/learn?id=Was ist IPC).

?> Die `task`-Schlange wird nach dem Beenden des `servers` nicht zerstört und wird nach der Wiederaufnahme des Programms von den [task-Prozessen](/learn?id=taskworker-Prozess) fortgesetzt. Wenn Sie nicht möchten, dass alte `Task`-Tasks nach dem Neustart des Programms ausgeführt werden, können Sie die Nachrichtenschlange manuell löschen.

```shell
ipcs -q 
ipcrm -Q [msgkey]
```


### daemonize

?> **Daemonisierung**【Standardwert: `false`】

?> Wenn Sie `daemonize => true` festlegen, wird das Programm im Hintergrund als Daemon ausgeführt. Für lang andauernde Serveranwendungen muss diese Option aktiviert werden.  
Wenn Sie keine Daemonisierung aktivieren, wird das Programm beendet, wenn die SSH-Terminal beendet wird.

  * **Hinweise**

    * Nachdem die Daemonisierung aktiviert wurde, werden Standardeingabe und -ausgabe an `log_file` umgeleitet
    * Wenn keine `log_file` festgelegt ist, werden sie an `/dev/null` umgeleitet, und alle Informationen, die auf dem Bildschirm ausgegeben werden, werden ignoriert
    * Nachdem die Daemonisierung aktiviert wurde, ändert sich der Wert der Umgebungsvariablen `CWD` (aktueller Arbeitsverzeichnis), und das Lesen und Schreiben von Dateien mit relativen Pfaden kann fehlgehen. In PHP-Programmen müssen absolute Pfade verwendet werden

    * **systemd**

      * Wenn Sie das Swoole-Dienst mit `systemd` oder `supervisord` verwalten, stellen Sie bitte keine `daemonize => true` fest. Der Hauptgrund dafür ist, dass das `systemd` -Prinzip anders ist als das `init` -System. Der `PID` des `init` -Prozesses ist `1`, und nachdem das Programm mit `daemonize` ausgeführt wurde, wird es vom Terminal getrennt und letztendlich vom `init` -Prozess verwaltet, was zu einer Vater-Kind-Beziehung mit dem `init` -Prozess wird.
      * Aber `systemd` hat einen eigenen Hintergrundprozess gestartet, der andere Dienstprozesse selbst verwaltet, indem er `fork` verwendet, daher ist keine `daemonize` erforderlich. Im Gegenteil, die Verwendung von `daemonize => true` kann dazu führen, dass das Swoole-Programm eine Vater-Kind-Beziehung zu diesem Verwaltungsprozess verliert.


### backlog

?> **Stellen Sie die Länge der `Listen`-Warteschlange fest.**

?> Wenn `backlog => 128`, wird dieser Parameter bestimmen, wie viele Verbindungen gleichzeitig auf `accept` warten können.

  * **Über die `TCP`-Warteschlange**

    ?> Bei TCP gibt es einen dreistufigen Handshakeprozess, Client `syn=>Server` `syn+ack=>Client` `ack`, wenn der Server das `ack` vom Client erhält, wird die Verbindung in eine sogenannte `accept queue` (Anmerkung 1) gelegt,  
    Die Größe der Warteschlange wird durch den `backlog`-Parameter und den minimalen Wert von `somaxconn` bestimmt, die endgültige Größe der `accept queue` kann mit dem Befehl `ss -lt` eingesehen werden, der Hauptprozess von Swoole ruft `accept` (Anmerkung 2)  
    Aus der `accept queue` genommen. Wenn die `accept queue` voll ist, können Verbindungen erfolgreich sein (Anmerkung 4),  
    Oder sie können fehlschlagen, und das Verhalten des Clients ist dann, dass die Verbindung zurückgesetzt wird (Anmerkung 3)  
    Oder die Verbindung wird Timeout haben, und der Server wird das Fehlverhalten aufzeichnen, man kann den Log durch `netstat -s|grep 'times the listen queue of a socket overflowed'` einsehen. Wenn eines dieser Phänomene auftritt, sollten Sie diesen Wert erhöhen. Glücklicherweise hängt der SWOOLE_PROCESS Modus von Swoole nicht von der `backlog` ab, um Probleme mit der Verbindungswarteschlange zu lösen, wie zum Beispiel PHP-FPM oder Apache, daher trifft man im Grunde nicht auf diese Art von Problemen.

    * Anmerkung 1: Ab Linux 2.2 wird der Handshakeprozess in zwei Warteschlangen unterteilt, die `syn queue` und die `accept queue`, die Länge der `syn queue` wird durch `tcp_max_syn_backlog` bestimmt.
    * Anmerkung 2: In höheren Kernelversionen wird die `accept4`-Funktion verwendet, um eine weitere `set no block` Systemanruf zu sparen.
    * Anmerkung 3: Wenn der Client das `syn+ack`-Paket erhält, glaubt er, dass die Verbindung erfolgreich ist, tatsächlich befindet sich der Server noch in einem halben Verbindungszustand, er könnte ein `rst`-Paket an den Client senden, und das Verhalten des Clients wäre dann `Connection reset by peer`.
    * Anmerkung 4: Erfolg wird durch das TCP-Wiederholungsmechanismus erreicht, die entsprechenden Einstellungen sind `tcp_synack_retries` und `tcp_abort_on_overflow`.
### open_tcp_keepalive

?> Bei TCP gibt es ein Keep-Alive-Mechanismus zur Detektion von toten Verbindungen. Wenn die Anwendungsschicht nicht empfindlich gegenüber toten Verbindungen ist oder keinen Herzschlagmechanismus implementiert hat, kann sie den Keepalive-Mechanismus des Betriebssystems verwenden, um tote Verbindungen zu kicken.
In der Konfiguration von [Server->set()](/server/methods?id=set) fügen Sie `open_tcp_keepalive => true` hinzu, um TCP Keepalive zu aktivieren.
Es gibt auch drei Optionen, um die Details des Keepalive zu anpassen.

  * **Optionen**

     * **tcp_keepidle**

        In Sekunden, wenn eine Verbindung in `n` Sekunden keinen Datenrequest erhält, wird mit dieser Verbindung begonnen, Proben zu machen.

     * **tcp_keepcount**

        Die Anzahl der Probezyklen, nach der die Verbindung geschlossen wird.

     * **tcp_keepinterval**

        Die Intervalzeit für die Proben, in Sekunden.

  * **Beispiel**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, //4s ohne Daten传输 wird getestet
    'tcp_keepinterval' => 1, //1s wird einmal getestet
    'tcp_keepcount' => 5, //Nach 5 Mal ohne Antwort wird die Verbindung geschlossen
));

$serv->on('connect', function ($serv, $fd) {
    var_dump("Client:Connect $fd");
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    var_dump($data);
});

$serv->on('close', function ($serv, $fd) {
  var_dump("close fd $fd");
});

$serv->start();
```


### heartbeat_check_interval

?> **Herzschlagprüfung aktivieren**【Standardwert: `false`】

?> Diese Option gibt an, wie oft in Sekunden eine Runde durch alle Verbindungen durchgeführt wird. Zum Beispiel bedeutet `heartbeat_check_interval => 60`, dass alle 60 Sekunden alle Verbindungen überprüft werden, und wenn eine Verbindung in 120 Sekunden (der Standardwert für `heartbeat_idle_time` ist das Doppelte des Intervals) keinen Daten an den Server sendet, wird die Verbindung zwangsweise geschlossen. Wenn nicht konfiguriert, wird keine Herzschlagprüfung durchgeführt, und diese Einstellung ist standardmäßig geschlossen.

  * **Hinweise**
    * Der `Server` sendet keinen Herzschlag-Paket proaktiv an den Client, sondern wartet passiv darauf, dass der Client einen Herzschlag sendet. Die Herzschlagprüfung auf Serverseite überprüft nur, wann das letzte Mal Daten an die Verbindung gesendet wurden. Wenn dies die Grenze überschreitet, wird die Verbindung abgeschnitten.
    * Verbindungen, die durch Herzschlagprüfung abgeschnitten werden, lösen dennoch das [onClose](/server/events?id=onclose) Ereignis aus

  * **Wichtig**

    !> `heartbeat_check` unterstützt nur `TCP`-Verbindungen


### heartbeat_idle_time

?> **Maximale zulässige Leerlaufzeit für Verbindung**

?> muss mit `heartbeat_check_interval` kombiniert werden

```php
array(
    'heartbeat_idle_time'      => 600, // Eine Verbindung wird zwangsweise geschlossen, wenn sie in 600 Sekunden keinen Daten an den Server sendet
    'heartbeat_check_interval' => 60,  // Jede 60 Sekunden wird eine Runde durchgeführt
);
```

  * **Hinweise**

    * Nachdem `heartbeat_idle_time` aktiviert wurde, sendet der Server keinen Datenpaket proaktiv an den Client
    * Wenn nur `heartbeat_idle_time` festgelegt ist und nicht `heartbeat_check_interval`, wird im Hintergrund kein Herzschlag-Prüfungs-Thread erstellt. In der PHP-Code kann die `heartbeat` Methode manuell verwendet werden, um überzählige Verbindungen zu bearbeiten


### open_eof_check

?> **EOF-Prüfung aktivieren**【Standardwert: `false`】, siehe [TCP-Paket-Grenzen](/learn?id=tcp-paket-grenzen)

?> Diese Option wird verwendet, um zu überprüfen, ob die von einem Client-Connection gesendeten Daten das angegebene Endezeichen enthalten. Nur wenn das Endezeichen erreicht wird, wird der Datenpaket an den Worker-Prozess weitergeleitet. Andernfalls wird das Paket weiter zusammengefügt, bis es den Puffer überschreitet oder eine Zeit abläuft. Bei Fehlern wird von der Basisseite angenommen, dass es sich um eine böswillige Verbindung handelt, die Daten wird ignoriert und die Verbindung wird zwangsweise geschlossen.  
Common Protocols wie Memcache/SMTP/POP enden normalerweise mit \r\n, und diese Einstellung kann verwendet werden. Nach dem Aktivieren kann sichergestellt werden, dass der Worker-Prozess immer ein oder mehrere vollständige Pakete erhält.

```php
array(
    'open_eof_check' => true,   //EOF-Prüfung aktivieren
    'package_eof'    => "\r\n", //EOF festlegen
)
```

  * **Wichtig**

    !> Diese Einstellung ist nur für `STREAM`-Typen von Sockets (z.B. TCP, Unix Socket Stream) gültig   
    Bei der EOF-Prüfung wird nicht in den Daten nach dem EOF-String gesucht, daher kann der Worker-Prozess gleichzeitig mehrere Pakete erhalten. In der Anwendungscode muss man selbst `explode("\r\n", $data)` verwenden, um die Pakete zu zerlegen


### open_eof_split

?> **Automatische Zerlegung von EOF aktivieren**

?> Wenn die `open_eof_check` festgelegt ist, können mehrere Pakete in einem Paket zusammengefasst werden. Die `open_eof_split`-Parameter kann dieses Problem lösen, siehe [TCP-Paket-Grenzen](/learn?id=tcp-paket-grenzen).

?> Um diesen Parameter zu setzen, muss der gesamte Inhalt des Pakets durchlaufen und nach dem EOF gesucht werden, was viel CPU-Ressourcen verbraucht. Angenommen, jedes Paket ist 2M groß und es gibt 10000 Anforderungen pro Sekunde, was möglicherweise zu 20G CPU-Charaktermatch-Befehlen führen kann.

```php
array(
    'open_eof_split' => true,   //EOF-SPLIT-Prüfung aktivieren
    'package_eof'    => "\r\n", //EOF festlegen
)
```

  * **Hinweise**

    * Nachdem die `open_eof_split`-Parameter aktiviert wurde, sucht die Basisseite von links nach rechts im Datenpaket nach dem EOF und zerlegt das Paket. Bei [onReceive](/server/events?id=onreceive) wird jedes Mal nur ein Paket mit dem EOF-String am Ende erhalten.
    * Nachdem die `open_eof_split`-Parameter aktiviert wurde, wird der Parameter `open_eof_check`, ob er festgelegt ist oder nicht, wirksam.

    * **Unterschiede zu `open_eof_check`**
    
        * `open_eof_check` überprüft nur, ob das Ende des empfangenen Datenpakets das EOF ist, daher ist seine Leistung am besten und es verbraucht fast keine Ressourcen
        * `open_eof_check` kann das Problem nicht lösen, bei dem mehrere Pakete mit EOF zusammengefasst werden, zum Beispiel wenn gleichzeitig zwei Pakete mit EOF gesendet werden, könnte die Basisseite beide zurückgeben
        * `open_eof_split` vergleicht den Daten von links nach rechts, sucht nach dem EOF im Datenpaket und zerlegt das Paket. Die Leistung ist schlechter. Aber jedes Mal wird nur ein Paket zurückgegeben


### package_eof

?> **EOF-String festlegen.** Siehe [TCP-Paket-Grenzen](/learn?id=tcp-paket-grenzen)

?> Muss mit `open_eof_check` oder `open_eof_split` kombiniert werden.

  * **Wichtig**

    !> Der `package_eof` darf höchstens einen String von 8 Byte lang sein


### open_length_check

?> **Paketlängenprüfung aktivieren**【Standardwert: `false`】, siehe [TCP-Paket-Grenzen](/learn?id=tcp-paket-grenzen)

?> Die Paketlängenprüfung bietet eine Parsing-Funktion für Protokolle mit fester Kopf- und Paktergebnisformat. Nachdem sie aktiviert wurde, kann sichergestellt werden, dass der Worker-Prozess bei jedem [onReceive](/server/events?id=onreceive) ein vollständiges Paket erhält.  
Die Längenprotokollprüfung benötigt nur einmal die Berechnung der Länge, die Datenverarbeitung umfasst nur Zeigerabzug, die Leistung ist sehr hoch, **empfohlen**.

  * **Hinweise**

    * **Das Längenprotokoll bietet drei Optionen zur Kontrolle der Protokolldetails.**

      ?> Diese Einstellung ist nur für `STREAM`-Typen von Sockets (z.B. TCP, Unix Socket Stream) gültig

      * **package_length_type**

        ?> Ein Feld im Kopf wird als Wert für die Paketlänge verwendet, die Basisseite unterstützt 10 verschiedene Längenarten. Bitte siehe [package_length_type](/server/setting?id=package_length_type)

      * **package_body_offset**

        ?> Von welchem Byte an wird die Länge berechnet, es gibt in der Regel zwei Szenarien:

        * Der Wert von `length` umfasst das gesamte Paket (Kopf+Paketkörper), `package_body_offset` ist `0`
        * Die Kopfgröße beträgt `N` Byte, der Wert von `length` umfasst keinen Kopf, nur den Paketkörper, `package_body_offset` wird auf `N` gesetzt

      * **package_length_offset**

        ?> An welchem Byte im Kopf befindet sich der Wert der `length`.

        * Beispiel:

        ```c
        struct
        {
            uint32_t type;
            uint32_t uid;
            uint32_t length;
            uint32_t serid;
            char body[0];
        }
        ```
        
    ?> In dem oben genannten Kommunikationsprotokolldesign beträgt die Kopfgröße 4 Integer-Werte, 16 Byte, der Wert der `length` befindet sich am dritten Integer-Wert. Daher wird `package_length_offset` auf `8` gesetzt, die Bytes `0-3` für `type`, `4-7` für `uid`, `8-11` für `length` und `12-15` für `serid`.

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```
### package_length_type

?> **Typ der Längenwerte**, akzeptiert einen Zeichenparameter, consistent mit der [pack](http://php.net/manual/zh/function.pack.php)-Funktion von `PHP`.

Derzeit unterstützt `Swoole` 10 Typen:


Charakterparameter | Wirkung
---|---
c | signed, 1 Byte
C | unsigned, 1 Byte
s | signed, host byte order, 2 Bytes
S | unsigned, host byte order, 2 Bytes
n | unsigned, network byte order, 2 Bytes
N | unsigned, network byte order, 4 Bytes
l | signed, host byte order, 4 Bytes (kleines L)
L | unsigned, host byte order, 4 Bytes (großes L)
v | unsigned, little-endian byte order, 2 Bytes
V | unsigned, little-endian byte order, 4 Bytes


### package_length_func

?> **Legt die Funktion zur Längeninterpretation fest**

?> Unterstützt zwei Arten von Funktionen, `C++` oder `PHP`. Die Längenfunktion muss einen Integer zurückgeben.


Rückgabewert | Wirkung
---|---
0 zurückgeben | Nicht genug Längendaten, weitere Daten müssen empfangen werden
-1 zurückgeben | Datenfehler, die Basisautomatisch die Verbindung schließen
Die Länge des Pakets (einschließlich Header und Payload) zurückgeben | Die Basis kombiniert automatisch das Paket und übergibt es an die Rückruffunktion

  * **Hinweis**

    * **Anwendungsweise**

    ?> Das Prinzip ist es, zuerst eine kleine Menge an Daten zu lesen, die eine Längenwert enthält. Dann wird dieser Längenwert an die Basis zurückgegeben. Anschließend empfängt die Basis den Rest der Daten und kombiniert sie zu einem Paket für die `dispatch`.

    * **PHP Längeninterpretationsfunktion**

    ?> Da der `ZendVM` keine Multithreadingumgebung unterstützt, verwendet die Basis automatisch einen `Mutex` zur Verhinderung des gleichzeitigen Ausführens von `PHP`-Funktionen. Ab Version `1.9.3` ist dies verfügbar.

    !> Bitte führen Sie keine blockierende `IO`-Operationen in der Längeninterpretationsfunktion durch, da dies alle [Reactor](/learn?id=reactor线程) Threads blockieren kann

    ```php
    $server = new Swoole\Server("127.0.0.1", 9501);
    
    $server->set(array(
        'open_length_check'   => true,
        'dispatch_mode'       => 1,
        'package_length_func' => function ($data) {
          if (strlen($data) < 8) {
              return 0;
          }
          $length = intval(trim(substr($data, 0, 8)));
          if ($length <= 0) {
              return -1;
          }
          return $length + 8;
        },
        'package_max_length'  => 2000000,  //Maximaler Protokolllänge
    ));
    
    $server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        var_dump($data);
        echo "#{$server->worker_id}>> received length=" . strlen($data) . "\n";
    });
    
    $server->start();
    ```

    * **C++ Längeninterpretationsfunktion**

    ?> In anderen PHP-Erweiterungen werden Längenfunktionen mit `swoole_add_function` in den `Swoole`-Motor registriert.
    
    !> Bei der Ausführung von C++-Längenfunktionen wird die Basis nicht versiegelt, daher muss die Aufruferin die Threadsicherheit selbst gewährleisten
    
    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"
    
    using namespace std;
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);
    
    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW_OK;
    }
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length)
    {
        printf("cpp, size=%d\n", length);
        return 100;
    }
    ```


### package_max_length

?> **Legt die maximale Größe eines Datenpakets fest, gemessen in Bytes. **【Standardwert: `2M` also `2 * 1024 * 1024`, minimalwert: `64K`】

?> Wenn [open_length_check](/server/setting?id=open_length_check)/[open_eof_check](/server/setting?id=open_eof_check)/[open_eof_split](/server/setting?id=open_eof_split)/[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol) und andere Protokollanalysen aktiviert sind, wird die `Swoole`-Basis Datenpakete zusammenfügen. Wenn ein Datenpaket nicht vollständig empfangen wurde, werden alle Daten im Speicher保存.  
Daher ist es notwendig, `package_max_length` festzulegen, die maximale Größe des Speichers, den ein Datenpaket maximal einnehmen darf. Wenn gleichzeitig 10.000 `TCP`-Verbindungen Daten senden und jedes Paket `2M` groß ist, würde unter extremer Situation `20G` an Speicherplatz benötigt werden.

  * **Hinweis**

    * `open_length_check`: Wenn die Paketlänge die `package_max_length` überschreitet, wird dieses Paket direkt ignoriert und die Verbindung geschlossen, ohne dass Speicherplatz beansprucht wird;
    * `open_eof_check`: Da die Größe des Datenpakets im Voraus nicht bekannt ist, werden empfangene Daten dennoch im Speicher保存 und kontinuierlich anwachsen. Wenn der Speicherplatz die `package_max_length` überschreitet, wird dieses Paket direkt ignoriert und die Verbindung geschlossen;
    * `open_http_protocol`: Die maximale Größe für `GET`-Anfragen beträgt `8K`, und es ist nicht möglich, die Konfiguration zu ändern. Für `POST`-Anfragen wird der `Content-Length` überprüft. Wenn der `Content-Length` die `package_max_length` überschreitet, wird dieses Paket direkt ignoriert, eine `http 400` Fehlermeldung gesendet und die Verbindung geschlossen;

  * **Wichtigkeit**

    !> Dieser Parameter sollte nicht zu groß festgelegt werden, sonst kann viel Speicherplatz beansprucht werden


### open_http_protocol

?> **Aktiviert die Behandlung des HTTP-Protokolls.**【Standardwert: `false`】

?> Wenn die Behandlung des HTTP-Protokolls aktiviert ist, wird die Option [Swoole\Http\Server](/http_server) automatisch aktiviert. Wenn sie auf `false` gesetzt ist, wird die Behandlung des HTTP-Protokolls deaktiviert.


### open_mqtt_protocol

?> **Aktiviert die Behandlung des MQTT-Protokolls.**【Standardwert: `false`】

?> Wenn aktiviert, wird der MQTT-Header analysiert, und der `worker` Prozess [onReceive](/server/events?id=onreceive) gibt jedes Mal ein vollständiges MQTT-Datenpaket zurück.

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```


### open_redis_protocol

?> **Aktiviert die Behandlung des Redis-Protokolls.**【Standardwert: `false`】

?> Wenn aktiviert, wird der Redis-Protokoll analysiert, und der `worker` Prozess [onReceive](/server/events?id=onreceive) gibt jedes Mal ein vollständiges Redis-Datenpaket zurück. Es wird empfohlen, direkt die [Redis\Server](/redis_server) zu verwenden.

```php
$server->set(array(
  'open_redis_protocol' => true
));
```


### open_websocket_protocol

?> **Aktiviert die Behandlung des WebSocket-Protokolls.**【Standardwert: `false`】

?> Wenn die Behandlung des WebSocket-Protokolls aktiviert ist, wird die Option [Swoole\WebSocket\Server](websocket_server) automatisch aktiviert. Wenn sie auf `false` gesetzt ist, wird die Behandlung des websocket-Protokolls deaktiviert.  
Wenn die Option `open_websocket_protocol` auf `true` gesetzt ist, wird auch die Option `open_http_protocol` automatisch auf `true` gesetzt.


### open_websocket_close_frame

?> **Aktiviert das Schließen von Frames im WebSocket-Protokoll.**【Standardwert: `false`】

?> (Frame mit `opcode` `0x08`) Empfangen in der `onMessage`-Rückruffunktion

?> Wenn aktiviert, können in der `onMessage`-Rückruffunktion des `WebSocketServer`-Objekts Schließframes, die von einem Client oder dem Server gesendet werden, empfangen werden. Die Entwickler können diese selbst verarbeiten.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->set(array("open_websocket_close_frame" => true));

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {});

$server->start();
```
### open_tcp_nodelay

?> **Aktivieren Sie `open_tcp_nodelay`.** 【Standardwert: `false`】

?> Wenn aktiviert, schaltet die TCP-Verbindung das Nagle-Algorithmus aus und sendet Daten sofort an die peer TCP-Verbindung. In einigen Szenarien, wie zum Beispiel bei einem Commandline-Terminal, ist es notwendig, dass eine Anweisung sofort an den Server gesendet wird, um die Antwortzeit zu verbessern. Bitte suchen Sie nach dem Nagle-Algorithmus unter Google.


### open_cpu_affinity 

?> **Aktivieren Sie die CPU-Affinitäts-Einstellung.** 【Standard `false`】

?> Auf multi-core Hardware-Plattformen, wenn diese Funktion aktiviert ist, wird der Reaktor-Thread/Worker-Prozess von Swoole an einen festen Kern gebunden. Dies kann vermeiden, dass der Prozess/Thread zwischen verschiedenen Kernen hin und her wechselt und die CPU-Cache-Trefferrate verbessert.

  * **Hinweis**

    * **Verwenden Sie den taskset-Befehl, um die CPU-Affinitäts-Einstellung eines Prozesses anzuzeigen:**

    ```bash
    taskset -p ProzessID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > Das Mask-Wert ist eine Maske-Zahl, bei der jeder Bit einem CPU-Kern entspricht. Wenn ein Bit auf `0` steht, bedeutet dies, dass der Kern gebunden ist, und der Prozess wird auf diesem CPU-Kern platziert. Wenn ein Bit auf `0` steht, bedeutet dies, dass der Prozess nicht auf diesem CPU-Kern platziert wird. Im Beispiel hat der Prozess mit dem PID `24666` ein Mask-Wert von `f`, was bedeutet, dass er nicht an einen CPU-Kern gebunden ist und der Betriebssystem den Prozess auf einem beliebigen CPU-Kern platziert wird. Der Prozess mit dem PID `24901` hat ein Mask-Wert von `8`, und `8` in Binär ist `1000`, was bedeutet, dass der Prozess an den vierten CPU-Kern gebunden ist.


### cpu_affinity_ignore

?> **In I/O-intensiven Programmen werden alle Netzwerk-Interruptionen mit CPU0 behandelt. Wenn die Netzwerk-I/O sehr intensiv ist und die CPU0-Belastung zu hoch ist, kann dies dazu führen, dass Netzwerk-Interruptionen nicht rechtzeitig behandelt werden können, was zu einer Verringerung der Fähigkeit zur Empfang und Übertragung von Netzwerkpaketen führt.**

?> Wenn diese Option nicht festgelegt ist, wird Swoole alle CPU-Kerne verwenden, und der untere Teil setzt die CPU-Bindung basierend auf reactor_id oder worker_id modulo der Anzahl der CPU-Kerne. Wenn das Kern-System und die Netzwerkschnittstelle über mehrere Queues verfügen, werden Netzwerk-Interruptionen auf mehrere Kerne verteilt, was die Belastung mit Netzwerk-Interruptionen verringern kann.

```php
array('cpu_affinity_ignore' => array(0, 1)) // akzeptiert ein Array als Parameter, array(0, 1) bedeutet, dass CPU0 und CPU1 nicht verwendet werden, sondern speziell für die Behandlung von Netzwerk-Interruptionen reserviert sind.
```

  * **Hinweis**

    * **Anzeigen von Netzwerk-Interruptionen**

```shell
[~]$ cat /proc/interrupts 
           CPU0       CPU1       CPU2       CPU3       
  0: 1383283707          0          0          0    IO-APIC-edge  timer
  1:          3          0          0          0    IO-APIC-edge  i8042
  3:         11          0          0          0    IO-APIC-edge  serial
  8:          1          0          0          0    IO-APIC-edge  rtc
  9:          0          0          0          0   IO-APIC-level  acpi
 12:          4          0          0          0    IO-APIC-edge  i8042
 14:         25          0          0          0    IO-APIC-edge  ide0
 82:         85          0          0          0   IO-APIC-level  uhci_hcd:usb5
 90:         96          0          0          0   IO-APIC-level  uhci_hcd:usb6
114:    1067499          0          0          0       PCI-MSI-X  cciss0
130:   96508322          0          0          0         PCI-MSI  eth0
138:     384295          0          0          0         PCI-MSI  eth1
169:          0          0          0          0   IO-APIC-level  ehci_hcd:usb1, uhci_hcd:usb2
177:          0          0          0          0   IO-APIC-level  uhci_hcd:usb3
185:          0          0          0          0   IO-APIC-level  uhci_hcd:usb4
NMI:      11370       6399       6845       6300 
LOC: 1383174675 1383278112 1383174810 1383277705 
ERR:          0
MIS:          0
```

`eth0/eth1` ist die Anzahl der Netzwerk-Interruptionen. Wenn `CPU0 - CPU3` gleichmäßig verteilt sind, bedeutet dies, dass die Netzwerkschnittstelle über mehrere Queues verfügt. Wenn sie sich alle auf einem einzelnen Kern konzentrieren, bedeutet dies, dass alle Netzwerk-Interruptionen von diesem CPU behandelt werden. Sobald dieser CPU mehr als `100%` erreicht, kann das System keine Netzwerkanforderungen verarbeiten. In diesem Fall ist es notwendig, die `cpu_affinity_ignore` Einstellung zu verwenden, um diesen CPU freizumachen und speziell für die Behandlung von Netzwerk-Interruptionen zu verwenden.

Wie im Bild dargestellt, sollte `cpu_affinity_ignore => array(0)` festgelegt werden.

?> Sie können den `top` Befehl verwenden `->` und `1` eingeben, um die Nutzung jedes Kerns anzuzeigen.

  * **Hinweis**

    !> Diese Option muss mit `open_cpu_affinity` gleichzeitig festgelegt werden, um wirksam zu sein.


### tcp_defer_accept

?> **Aktivieren Sie die `tcp_defer_accept`-Funktion**【Standardwert: `false`】

?> Sie können es einer Zahl zuweisen, die angibt, wann die `accept`-Funktion für eine TCP-Verbindung ausgelöst wird, wenn Daten gesendet werden.

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

  * **Hinweis**

    * **Nachdem die `tcp_defer_accept`-Funktion aktiviert wurde, ändert sich die Zeit, in der `accept` und [onConnect](/server/events?id=onconnect) aufgerufen werden. Wenn es auf `5` Sekunden festgelegt ist:**

      * Nach der Verbindung eines Clients zum Server wird die `accept`-Funktion nicht sofort ausgelöst.
      * Wenn der Client innerhalb von `5` Sekunden Daten sendet, werden `accept`, `onConnect` und `onReceive` nacheinander ausgelöst.
      * Wenn der Client innerhalb von `5` Sekunden keine Daten sendet, wird nur `accept` und `onConnect` ausgelöst.


### ssl_cert_file / ssl_key_file :id=ssl_cert_file

?> **SSL-Tunneling-Verschlüsselung einrichten.**

?> Geben Sie einen Dateinamen als Wert an, der den Pfad zum Zertifikat und zum privaten Schlüssel angibt.

  * **Hinweis**

    * **PEM zu DER-Format konvertieren**

    ```shell
    openssl x509 -in cert.crt -outform der -out cert.der
    ```

    * **DER zu PEM-Format konvertieren**

    ```shell
    openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
    ```

  * **Hinweis**

    !> - Für `HTTPS`-Anwendungen muss der Browser das Zertifikat vertrauen, um Webseiten besuchen zu können;  
    - Für `wss`-Anwendungen muss die Seite, die eine `WebSocket`-Verbindung initiiert, über `HTTPS` verfügen;  
    - Wenn der Browser das SSL-Zertifikat nicht vertraut, kann er keine `wss`-Verbindung verwenden;  
    - Die Datei muss im PEM-Format sein, nicht im DER-Format, und kann mit dem `openssl`-Tool konvertiert werden.

    !> Um `SSL` zu verwenden, müssen Sie beim Kompilieren von Swoole die [--enable-openssl](/environment?id=Kompilierungsoptionen) Option einschließen.

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```


### ssl_method

!> Dieser Parameter wurde in der Version [v4.5.4](/version/bc?id=_454) entfernt, bitte verwenden Sie `ssl_protocols`

?> **SSL-Verschlüsselungsalgorithmen für den OpenSSL-Tunneling einrichten.**【Standardwert: `SWOOLE_SSLv23_METHOD`】, die unterstützten Typen finden Sie unter [SSL-Verschlüsselungsmethoden](/consts?id=ssl-Verschlüsselungsmethoden)

?> Der Algorithmus, der vom `Server` und dem `Client` verwendet wird, muss identisch sein, sonst wird der SSL/TLS-Handshakes scheitern und die Verbindung wird abgeschnitten

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```
### ssl_protocols

?> **Setzt die Protokolle für die TLS-Tunneling-Verschlüsselung mit OpenSSL.** 【Standardwert: `0`, unterstützt alle Protokolle】, siehe unterstützte Typen unter [SSL-Protokolle](/consts?id=ssl-protokolle)

!> Swoole-Version >= `v4.5.4` verfügbar

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```


### ssl_sni_certs

?> **Setzt die SNI (Server Name Identification)-Zertifikate**

!> Swoole-Version >= `v4.6.0` verfügbar

```php
$server->set([
    'ssl_cert_file' => __DIR__ . '/server.crt',
    'ssl_key_file' => __DIR__ . '/server.key',
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'ssl_sni_certs' => [
        'cs.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_cs_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_cs_key.pem',
        ],
        'uk.php.net' => [
            'ssl_cert_file' =>  __DIR__ . '/sni_server_uk_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_uk_key.pem',
        ],
        'us.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_us_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_us_key.pem',
        ],
    ]
]);
```


### ssl_ciphers

?> **Setzt die Verschlüsselungsalgorithmen für OpenSSL.** 【Standardwert: `EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

  * **Hinweis**

    * Wenn `ssl_ciphers` leer eingestellt ist, wählt `openssl` selbst die Verschlüsselungsalgorithmen aus


### ssl_verify_peer

?> **Legt fest, ob SSL-Zertifikate des Peer-Dienstes überprüft werden. ** 【Standardwert: `false`】

?> Standardmäßig ausgeschaltet, d.h. kein Client-Zertifikat überprüft wird. Wenn eingeschaltet, muss auch die Option `ssl_client_cert_file` festgelegt werden


### ssl_allow_self_signed

?> **Erlaubt selbstgefertigte Zertifikate.** 【Standardwert: `false`】


### ssl_client_cert_file

?> **Zertifikat des Root-Zertifikats, zum Überprüfen des Client-Zertifikats.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set(array(
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,
    'ssl_allow_self_signed' => true,
    'ssl_client_cert_file'  => __DIR__ . '/config/ca.crt',
));
```

!> Wenn der TCP-Dienst die Überprüfung nicht bestanden hat, wird die Verbindung von unten her beendet.


### ssl_compress

?> **Legt fest, ob SSL/TLS-Kompression aktiviert ist.** Bei Verwendung von [Co\Client](/coroutine_client/client) gibt es einen Alias `ssl_disable_compression`


### ssl_verify_depth

?> **Wenn die Zertifikatsstruktur zu tief ist und die Einstellung dieses Options übertrifft, wird die Überprüfung abgebrochen.**


### ssl_prefer_server_ciphers

?> **Aktiviert Server-Seitenschutz, um BEAST-Angriffe zu verhindern.**


### ssl_dhparam

?> **Gibt die Diffie-Hellman-Parameter für den DHE-Schlüsseltausch an.**


### ssl_ecdh_curve

?> **Gibt die Kurve an, die für den ECDH-Schlüsseltausch verwendet wird.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_compress'                => true,
    'ssl_verify_depth'            => 10,
    'ssl_prefer_server_ciphers'   => true,
    'ssl_dhparam'                 => '',
    'ssl_ecdh_curve'              => '',
]);
```


### user

?> **Setzt den Besitzer der `Worker/TaskWorker`-Subprozesse.** 【Standardwert: Der Benutzer, der das Skript ausführt】

?> Wenn der Server Ports unter `1024` überwacht, muss er `root`-Berechtigungen haben. Wenn jedoch das Programm als `root`-Benutzer ausgeführt wird und es zu einem漏洞(vulnerability) im Code kommt, könnte ein Angreifer mit `root`-Rechten Remote-Befehle ausführen, was ein großes Sicherheitsrisiko darstellt. Nachdem die `user`-Option konfiguriert wurde, kann der Hauptprozess unter `root`-Rechten laufen, während die Subprozesse unter den Rechten eines gewöhnlichen Benutzers laufen.

```php
$server->set(array(
  'user' => 'Apache'
));
```

  * **Hinweis**

    !> -Gültig nur, wenn der Server mit `root`-Rechten gestartet wird  
    -Wenn die `user/group`-Konfigurationsoption verwendet wird, um die Arbeitsprozesse zu gewöhnlichen Benutzern zu setzen, kann der Arbeitsprozess nicht die Methoden `shutdown`/[reload](/server/methods?id=reload) zum Schließen oder Herunterfahren des Diensts verwenden. Nur der `root`-Konto kann im `shell`-Terminal das `kill`-Befehl ausführen.


### group

?> **Setzt die Prozessgruppe der `Worker/TaskWorker`-Subprozesse.** 【Standardwert: Die Gruppe, die das Skript ausführt】

?> Ähnlich wie bei der `user`-Konfiguration wird diese Einstellung verwendet, um die Prozessgruppe zu ändern, um die Sicherheit des Serverprogramms zu erhöhen.

```php
$server->set(array(
  'group' => 'www-data'
));
```

  * **Hinweis**

    !> Gültig nur, wenn der Server mit `root`-Rechten gestartet wird


### chroot

?> **Weicht den Dateisystem-Root der `Worker`-Prozesse aus.**

?> Diese Einstellung kann dazu führen, dass die Prozesse für das Lesen und Schreiben von Dateien im Dateisystem isoliert sind und sich vom tatsächlichen Betriebssystem-Dateisystem unterscheiden. Dies erhöht die Sicherheit.

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```


### pid_file

?> **Setzt die Adresse des PID-Files.**

?> Beim Start des `Server` wird automatisch das `PID` des `master`-Prozess in eine Datei geschrieben und beim Schließen des `Server` wird das PID-File automatisch gelöscht.

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

  * **Hinweis**

    !> Bei Verwendung muss beachtet werden, dass das PID-File nicht gelöscht wird, wenn der `Server` nicht normal beendet wird. Es ist notwendig, den Prozess mit [Swoole\Process::kill($pid, 0)](/process/process?id=kill) zu überprüfen, ob der Prozess wirklich existiert


### buffer_input_size / input_buffer_size :id=buffer_input_size

?> **Konfiguriert die Größe des Empfangsbuffers.** 【Standardwert: `2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```


### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **Konfiguriert die Größe des Sende-Buffers.** 【Standardwert: `2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, //muss eine Zahl sein
]);
```

  * **Hinweis**

    !> Bei Swoole-Version >= `v4.6.7` ist der Standardwert der größte unsigned INT, `UINT_MAX`

    * Die Einheit ist Byte, der Standardwert ist `2M`, zum Beispiel `32 * 1024 * 1024` bedeutet, dass der Server bei einem einzelnen `Server->send`-Befehl bis zu `32M` Byte Daten senden darf
    * Wenn der `Server->send`, `Http\Server->end/write` oder `WebSocket\Server->push` Befehle zum Senden von Daten verwendet werden, darf die maximale Datenmenge pro Aufruf nicht über die `buffer_output_size`-Konfiguration hinausgehen.

    !> Diese Parameter gelten nur für das [SWOOLE_PROCESS](/learn?id=swoole_process)-Modell, da im PROCESS-Modell die Daten der Worker-Prozesse an den Hauptprozess gesendet werden müssen, bevor sie an den Client gesendet werden. Daher wird jeder Worker-Prozess einen eigenen Puffer mit dem Hauptprozess haben. [Referenz](/learn?id=reactor线程)


### socket_buffer_size

?> **Konfiguriert die Größe des Puffer für Client-Verbindungen.** 【Standardwert: `2M`】

?> Im Gegensatz zu `buffer_output_size` ist `buffer_output_size` die Größe des `send`-Befehls für den Worker-Prozess, während `socket_buffer_size` die Gesamtgröße des Puffers für die Kommunikation zwischen `Worker` und `Master`-Prozess ist, siehe [SWOOLE_PROCESS](/learn?id=swoole_process)-Modell.

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, //muss eine Zahl sein, die Einheit ist Byte, zum Beispiel 128 * 1024 *1024 bedeutet, dass jede TCP-Clientverbindung bis zu 128M Daten in Warteschlang haben darf, die gesendet werden können
]);
```
- **Datenübermittlungspuffer**

    - Wenn der Master-Prozess dem Client große Mengen an Daten sendet, kann dies nicht sofort geschehen. In diesem Fall werden die gesendeten Daten im Arbeitsspeicher-Puffer des Servers gelagert. Dieser Parameter kann die Größe des Arbeitsspeicher-Puffers anpassen.
    
    - Wenn zu viele Daten gesendet werden und der Puffer voll ist, wird der Server wie folgt einen Fehler melden:
    
    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```
    
    ?> Ein überfüllter Sendebuffert führt zum `send`-Fehler, der nur den aktuellen Client betrifft und andere Clients nicht beeinträchtigt. Wenn der Server eine große Anzahl von `TCP`-Verbindungen hat, kann er im schlimmsten Fall `serv->max_connection * socket_buffer_size`字节 an Speicher beanspruchen.
    
    - Insbesondere bei Serverprogrammen, die extern kommunizieren und eine langsame Netzwerkverbindung haben, wird der Puffer schnell überfüllt, wenn Daten kontinuierlich gesendet werden. Die gesendeten Daten werden alle im Speicher des Servers gesammelt. Daher sollten solche Anwendungen bei der Konzeption die Übertragungskapazität des Netzwerks berücksichtigen und zuerst Nachrichten auf den Datenträger speichern, und dann neue Daten senden, nachdem der Client dem Server mitgeteilt hat, dass sie empfangen wurden.
    
    - Zum Beispiel bei einem Video-Livestreaming-Service: Wenn der Benutzer A eine Bandbreite von `100M` hat und innerhalb von `1` Sekunde `10M` Daten sendet, ist das völlig akzeptabel. Wenn der Benutzer B nur eine Bandbreite von `1M` hat und innerhalb von `1` Sekunde `10M` Daten sendet, könnte der Benutzer B es可能需要 `100` Sekunden dauern, um alles zu empfangen. In diesem Fall werden alle Daten im Speicher des Servers gesammelt.
    
    - Basierend auf dem Typ des Inhalts der Daten kann unterschiedlich behandelt werden. Wenn es sich um verwerflichen Inhalt handelt, wie zum Beispiel Video-Livestreaming, ist es völlig akzeptabel, einige Datenframes im Falle eines schlechten Netzwerks zu verwerfen. Wenn der Inhalt nicht verloren gehen darf, wie zum Beispiel WeChat-Nachrichten, können sie zuerst auf dem Datenträger des Servers gespeichert werden, in Gruppen zu `100` Nachrichten pro Gruppe. Nachdem der Benutzer diese Gruppe von Nachrichten empfangen hat, werden die nächsten Nachrichten aus dem Datenträger geladen und an den Client gesendet.


### enable_unsafe_event

?> **Aktiviert die `onConnect/onClose` Ereignisse.**【Standardwert: `false`】

?> Nachdem das [dispatch_mode](/server/setting?id=dispatch_mode) auf `1` oder `3` eingestellt wurde, kann das System die Reihenfolge von `onConnect/onReceive/onClose` nicht gewährleisten, daher sind die `onConnect/onClose` Ereignisse standardmäßig deaktiviert; Wenn die Anwendung `onConnect/onClose` Ereignisse benötigt und die mögliche Sicherheitsrisiken der Reihenfolge akzeptieren kann, kann das `enable_unsafe_event` auf `true` gesetzt werden, um die `onConnect/onClose` Ereignisse zu aktivieren.


### discard_timeout_request

?> **Verwerfe Datenanforderungen für geschlossene Verbindungen.**【Standardwert: `true`】

?> Nachdem das [dispatch_mode](/server/setting?id=dispatch_mode) auf `1` oder `3` eingestellt wurde, kann das System die Reihenfolge von `onConnect/onReceive/onClose` nicht gewährleisten, daher könnten einige Anforderungsdaten erst nach Schließen der Verbindung das `Worker`-Prozess erreichen.

  * **Hinweis**

    * Der `discard_timeout_request` ist standardmäßig auf `true` eingestellt, was bedeutet, dass, wenn das `worker`-Prozess eine Anforderung für eine geschlossene Verbindung erhält, sie automatisch verworfen wird.
    * Wenn `discard_timeout_request` auf `false` gesetzt ist, wird das `worker`-Prozess unabhängig davon, ob die Verbindung geschlossen ist, die Datenanforderung verarbeiten.


### enable_reuse_port

?> **Portübertragung aktivieren.**【Standardwert: `false`】

?> Nachdem Portübertragung aktiviert wurde, kann ein Serverprogramm, das denselben Port überwacht, erneut gestartet werden

  * **Hinweis**

    * `enable_reuse_port = true` öffnet die Portübertragung
    * `enable_reuse_port = false` schließt die Portübertragung

!> Nur in Kerneln ab `Linux-3.9.0` und in Versionen von `Swoole` ab `4.5` verfügbar


### enable_delay_receive

?> **Legt fest, dass nach dem `accept` eines Clientverbindungen der Verbindung nicht automatisch dem [EventLoop](/learn?id=什么是eventloop) beigetreten wird.**【Standardwert: `false`】

?> Wenn dieser选项 auf `true` gesetzt ist, wird nach dem `accept` eines Clientverbindungen der Verbindung nicht automatisch dem [EventLoop](/learn?id=什么是eventloop) beigetreten, sondern nur der [onConnect](/server/events?id=onconnect)-Rückruf ausgelöst. Das `worker`-Prozess kann die Verbindung mit [$server->confirm($fd)](/server/methods?id=confirm) bestätigen, bevor der `fd` dem [EventLoop](/learn?id=什么是eventloop) beigetreten ist, um mit dem Datenaustausch zu beginnen, oder die Verbindung mit `$server->close($fd)` schließen.

```php
// Enable the enable_delay_receive option
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        // Confirm the connection and start receiving data
        $server->confirm($fd);
    });
});
```


### reload_async

?> **Setzt den Schalter für asynchronen Neustart.**【Standardwert: `true`】

?> Der Schalter für asynchronen Neustart wird festgelegt. Wenn auf `true` gesetzt ist, wird die asynchrone sichere Neustart-Funktion aktiviert, und das `Worker`-Prozess wird warten, bis alle asynchronen Ereignisse abgeschlossen sind, bevor es beendet wird. Weitere Informationen finden Sie unter [Wie man den Service richtig neu startet](/question/use?id=swoole如何正确的重启服务).

?> Der Hauptzweck der Einstellung von `reload_async` ist es, sicherzustellen, dass Coroutinen oder asynchrone Aufgaben normal beendet werden können, wenn der Service neu gestartet wird.

```php
$server->set([
  'reload_async' => true
]);
```

  * **Coroutine-Modus**

    * In Version `4.x`, wenn [enable_coroutine](/server/setting?id=enable_coroutine) aktiviert ist, wird eine zusätzliche Überprüfung der Anzahl von Coroutinen durchgeführt. Das Prozess wird nur beendet, wenn derzeit keine Coroutinen vorhanden sind. Wenn die Einstellung auf `true` gesetzt ist, wird der Schalter für `reload_async` auch bei `reload_async => false` zwangsweise geöffnet.


### max_wait_time

?> **Legt die maximale Wartezeit für das Empfangen von Stop-Benachrichtigungen durch das `Worker`-Prozess fest.**【Standardwert: `3`】

?> Oftmals wird das Problem encountered, dass das `worker`-Prozess aufgrund von Blockierungen oder Staus nicht normal `reload` werden kann und daher einige Produktionsszenarien nicht erfüllen kann, wie zum Beispiel die Bereitstellung von Code-Hotfixes, die den `reload`-Prozess erfordern. Deshalb hat Swoole die Option für eine Prozess-Neustart-Zeitüberschreitung hinzugefügt. Weitere Informationen finden Sie unter [Wie man den Service richtig neu startet](/question/use?id=swoole如何正确的重启服务).

  * **Hinweis**

    * **Wenn das Management-Prozess eine Neustart- oder Schließbenachrichtigung für den Prozess erhält oder wenn die maximale Anzahl von Anforderungen erreicht wird, wird das Management-Prozess diesen `worker`-Prozess neu starten.** Dieser Prozess gliedert sich wie folgt:

      * Ein Timer mit der Dauer von (`max_wait_time`) Sekunden wird hinzugefügt. Nach dem Auslösen des Timers wird überprüft, ob der Prozess noch existiert. Wenn er es tut, wird er zwangsweise getötet und ein neuer Prozess gezogen.
      * Es ist notwendig, nach dem `onWorkerStop`-Rückruf Endarbeiten durchzuführen, die innerhalb von `max_wait_time` Sekunden abgeschlossen werden müssen.
      * Es werden nacheinander `SIGTERM`-Signale an das Zielprozess gesendet, um den Prozess zu töten.

  * **Hinweis**

    !> Vor `v4.4.x` war der Standardwert `30` Sekunden


### tcp_fastopen

?> **Aktiviert die TCP-Schnellerstellung feature.**【Standardwert: `false`】

?> Diese Funktion kann die Antwortzeit für kurze TCP-Verbindungen verbessern. Wenn der Client den dritten Schritt des Handshakes abgeschlossen hat und das `SYN`-Paket sendet, trägt er Daten mit.

```php
$server->set([
  'tcp_fastopen' => true
]);
```

  * **Hinweis**

    * Dieser Parameter kann auf den Überwachungsport festgelegt werden. Wer tiefer versteht, kann das [google paper](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf) lesen.


### request_slowlog_file

?> **Aktiviert die Slowlog-Protokollierung für Anfragen.** Ab Version `v4.4.8` [entfernt](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366)

!> Da dieses Slowlog-Schema nur in synchronen und blockierenden Prozessen wirksam ist und nicht in einem Coroutine-Umwelt verwendet werden kann, und da Swoole4 standardmäßig Coroutinen aktiviert sind, es sei denn, `enable_coroutine` ist ausgeschaltet, sollte es nicht mehr verwendet werden. Verwenden Sie stattdessen das [Swoole Tracker](https://business.swoole.com/tracker/index)-Blockierungsdetektionswerkzeug.

?> Nachdem es aktiviert wurde, wird der `Manager`-Prozess einen Uhrensignal einrichten, um regelmäßig alle `Task`- und `Worker`-Prozesse zu überprüfen. Sobald ein Prozess blockiert wird und die Anfragen eine bestimmte Zeit überschreiten, wird automatisch der PHP-Funktionsaufrufstack des Prozesses ausgegeben.

?> Der Grundlegende Aufbau basiert auf der `ptrace`-Systemanforderung. Einige Systeme haben `ptrace` möglicherweise ausgeschaltet und können keine SlowRequests verfolgen. Bitte stellen Sie sicher, dass das Kernelsystemparameter `kernel.yama.ptrace_scope` auf `0` gesetzt ist.

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

  * **Zeitüberschreitung**

```php
$server->set([
    'request_slowlog_timeout' => 2, // Legt die Anfragen-Zeitüberschreitung auf 2 Sekunden fest
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

!> Der Dateipfad muss schreibbar sein, sonst wird beim Erstellen des Dateis ein tödlicher Fehler im unteren Layer抛出
    

### enable_coroutine

?> **Ob启用异步风格服务器的协程支持**

?> Wenn `enable_coroutine` ausgeschaltet ist, werden in den [Ereignishandlern](/server/events) keine Coroutinen automatisch mehr erstellt. Wenn Coroutinen für diese Aufgabe nicht benötigt werden, kann dies die Leistung verbessern. Weitere Informationen finden Sie unter [Was sind Swoole-Coroutinen](/coroutine).

  * **Konfigurationsmethoden**
    
    * In der `php.ini` Konfigurieren Sie `swoole.enable_coroutine = 'Off'` (siehe [ini-Konfigurationsdokument](/other/config.md))
    * `$server->set(['enable_coroutine' => false]);` hat einen höheren Priorität als die ini-Konfiguration

  * **Auswirkung von `enable_coroutine`**

      * onWorkerStart
      * onConnect
      * onOpen
      * onReceive
      * [setHandler](/redis_server?id=sethandler)
      * onPacket
      * onRequest
      * onMessage
      * onPipeMessage
      * onFinish
      * onClose
      * tick/after Timer

!> Wenn `enable_coroutine` eingeschaltet ist, werden in den oben genannten Rückruffunktionen automatisch Coroutinen erstellt

* Wenn `enable_coroutine` auf `true` festgelegt ist, wird unter der Hut automatisch eine Coroutine in der [onRequest](/http_server?id=on)-Rückruffunktion erstellt. Die Entwickler müssen nicht selbst die `go`-Funktion verwenden, um [Coroutinen zu erstellen](/coroutine/coroutine?id=create)
* Wenn `enable_coroutine` auf `false` festgelegt ist, werden keine Coroutinen automatisch erstellt. Wenn die Entwickler Coroutinen verwenden möchten, müssen sie dies selbst mit `go` tun. Wenn die Coroutine-Funktionen nicht benötigt werden, ist der Processing-Modus genau wie in `Swoole1.x`
* Bitte beachten Sie, dass dies nur bedeutet, dass Swoole Requests über Coroutinen verarbeitet. Wenn Ereignisse blockierende Funktionen enthalten, muss die [One-Click-Coroutine-化和](/runtime) zuerst eingeschaltet werden, um blockierende Funktionen wie `sleep`, `mysqlnd` oder Erweiterungen zu Coroutine-화

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    // Ausschalten des eingebauten Coroutines
    'enable_coroutine' => false,
]);

$server->on("request", function ($request, $response) {
    if ($request->server['request_uri'] == '/coro') {
        go(function () use ($response) {
            co::sleep(0.2);
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });
    } else {
        $response->header("Content-Type", "text/plain");
        $response->end("Hello World\n");
    }
});

$server->start();
```


### send_yield

?> **Wenn beim Senden von Daten der Puffer voll ist, wird direkt in der aktuellen Coroutine [yield](/coroutine?id=协程调度), bis die Daten gesendet sind und der Puffer leer ist. Dann wird die aktuelle Coroutine automatisch [resume](/coroutine?id=协程调度) und der `send`-Vorgang wird fortgesetzt.**【Standardwert: verfügbar bei dispatch_mod 2/4 und standardmäßig eingeschaltet】

* Wenn `Server/Client->send` `false` zurückgibt und der Fehlercode `SW_ERROR_OUTPUT_BUFFER_OVERFLOW` ist, wird nicht `false` an die PHP-Schicht zurückgegeben, sondern die aktuelle Coroutine wird [yield](/coroutine?id=协程调度)
* `Server/Client` hört auf das Ereignis, dass der Puffer leer ist. Nach dem Auslösen dieses Ereignisses wurden alle Daten im Puffer gesendet. Dann wird die entsprechende Coroutine [resume](/coroutine?id=协程调度)
* Nachdem die Coroutine wiederhergestellt wurde, wird der `Server/Client->send`-Vorgang fortgesetzt, um Daten in den Puffer zu schreiben. Da der Puffer jetzt leer ist, ist der Senden sicherlich erfolgreich

Vor der Verbesserung

```php
for ($i = 0; $i < 100; $i++) {
    // Wenn der Puffer voll ist, wird direkt false zurückgegeben und ein Fehler 报告 output buffer overflow
    $server->send($fd, $data_2m);
}
```

Nach der Verbesserung

```php
for ($i = 0; $i < 100; $i++) {
    // Wenn der Puffer voll ist, wird die aktuelle Coroutine yield, und nachdem die Daten gesendet wurden, wird die Coroutine resume und der Prozess geht weiter
    $server->send($fd, $data_2m);
}
```

!> Diese Funktion ändert das Standardverhalten der Basis und kann manuell ausgeschaltet werden

```php
$server->set([
    'send_yield' => false,
]);
```

  * __Auswirkung__

    * [Swoole\Server::send](/server/methods?id=send)
    * [Swoole\Http\Response::write](/http_server?id=write)
    * [Swoole\WebSocket\Server::push](/websocket_server?id=push)
    * [Swoole\Coroutine\Client::send](/coroutine_client/client?id=send)
    * [Swoole\Coroutine\Http\Client::push](/coroutine_client/http_client?id=push)


### send_timeout

Geben Sie eine Sendezeitlimit fest, das zusammen mit `send_yield` verwendet wird. Wenn innerhalb der festgelegten Zeit die Daten nicht in den Puffer gesendet werden können, gibt die Basis `false` zurück und setzt den Fehlercode auf `ETIMEDOUT`. Sie können den Fehlercode mit der Methode [getLastError()](/server/methods?id=getlasterror) abrufen.

> Typ: Fließend, Einheit: Sekunden,最小刻度: Millisekunden

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1,5 Sekunden
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false and $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "Sendet超时\n";
    }
}
```


### hook_flags

?> **Geben Sie die Funktionsh范围和 `一键协程化` Hook fest.**【Standardwert: Keine Hook】

!> Swoole-Version `v4.5+` oder [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) verfügbar, siehe [一键协程化](/runtime) für Details

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```


### buffer_high_watermark

?> **Geben Sie das Pufferhochwasserlimit in Bytes fest.**

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```


### buffer_low_watermark

?> **Geben Sie das Pufferniedrigwasserlimit in Bytes fest.**

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```


### tcp_user_timeout

?> Die TCP_USER_TIMEOUT-Option ist eine TCP-Schicht-Socket-Option und gibt die maximale Zeit in Millisekunden an, nachdem ein Datenpaket gesendet wurde und kein ACK-Bestätigung erhalten wurde. Weitere Informationen finden Sie im man-Dokument

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10 Sekunden
]);
```

!> Swoole-Version >= `v4.5.3-alpha` verfügbar


### stats_file

?> **Geben Sie den Dateipfad für das Schreiben von [stats()](/server/methods?id=stats)-Inhalten fest. Nach der Einstellung wird automatisch ein Timer auf `onWorkerStart](/server/events?id=onworkerstart)` eingerichtet, der regelmäßig den Inhalt von [stats()](/server/methods?id=stats) in die angegebene Datei schreibt**

```php
$server->set([
    'stats_file' => __DIR__ . '/stats.log',
]);
```

!> Swoole-Version >= `v4.5.5` verfügbar


### event_object

?> **Geben Sie diese Option ein, um die Ereignishandler in [Objektstil](/server/events?id=回调对象) zu verwenden.**【Standardwert: `false`】

```php
$server->set([
    'event_object' => true,
]);
```

!> Swoole-Version >= `v4.6.0` verfügbar


### start_session_id

?> **Geben Sie den Anfangs-Session-ID fest**

```php
$server->set([
    'start_session_id' => 10,
]);
```

!> Swoole-Version >= `v4.6.0` verfügbar


### single_thread

?> **Stellen Sie dies auf einen einzelnen Thread ein.** Wenn es aktiviert ist, werden die Reactor-Threads mit dem Master-Thread im Master-Prozess verschmolzen, und der Master-Thread wird für die Logik zuständig sein. Unter PHP ZTS muss, wenn der `SWOOLE_PROCESS`-Modus verwendet wird, dieser Wert auf `true` gesetzt werden.

```php
$server->set([
    'single_thread' => true,
]);
```

!> Swoole-Version >= `v4.2.13` verfügbar
### max_queued_bytes

?> **Legt die maximale Länge des Empfangsbuffers fest.** Wenn sie überschritten wird, wird das Empfangen gestoppt.

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Swoole-Version >= `v4.5.0` verfügbar


### admin_server

?> **Stellt den admin_server-Dienst ein, um Informationen zum Dienst in der [Swoole Dashboard](http://dashboard.swoole.com/) anzuzeigen.**

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Swoole-Version >= `v4.8.0` verfügbar


### bootstrap

?> **Der Eintrittspunkt für den Programm im Mehr线程-Modus ist standardmäßig der Name des derzeit ausgeführten Skripts.**

!> Swoole-Version >= `v6.0` , PHP als ZTS-Modus, beim Kompilieren von Swoole wurde `--enable-swoole-thread` aktiviert

```php
$server->set([
    'bootstrap' => __FILE__,
]);
```

### init_arguments

?> **Legt die gemeinsamen Daten für den Mehr线程-Modus fest. Diese Konfiguration erfordert einen Rückruffunktions, der automatisch bei Start des Servers ausgeführt wird**

!> Swoole bietet viele threadsichere Containers, wie [Konfliktfreies Map](/thread/map), [Konfliktfreies List](/thread/arraylist), [Konfliktfreie Queue](/thread/queue). Verwenden Sie keine unsicheren Variablen, die nicht aus der Funktion zurückgegeben werden.

!> Swoole-Version >= `v6.0` , PHP als ZTS-Modus, beim Kompilieren von Swoole wurde `--enable-swoole-thread` aktiviert

```php
$server->set([
    'init_arguments' => function() { return new Swoole\Thread\Map(); },
]);

$server->on('request', function($request, $response) {
    $map = Swoole\Thread::getArguments();
});
```
