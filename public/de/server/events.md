# Ereignisse

Dieser Abschnitt wird alle Callbacks von Swoole vorstellen, jeder Callback ist eine PHP-Funktion, die einem Ereignis entspricht.


## onStart

?> **Nach dem Start wird diese Funktion im Hauptknoten des Hauptprozesses (master) aufgerufen**

```php
function onStart(Swoole\Server $server);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

* **Vor diesem Ereignis hat der `Server` bereits folgende Aktionen durchgeführt**

    * Start- und Creation-Vorgänge des [Manager-Prozesses](/learn?id=managerprozess) abgeschlossen
    * Start- und Creation-Vorgänge der [Worker-Subprozesse](/learn?id=workerprozess) abgeschlossen
    * Alle TCP/UDP/[unixSocket](/learn?id=was-ist-ipc)-Ports überwacht, aber aún keine Verbindungen und Anforderungen angenommen
    * Timer überwacht

* **Als nächstes auszuführen**

    * Der Hauptknoten des [Reactor](/learn?id=reactor-thread) beginnt, Ereignisse zu empfangen, und Kunden können sich `connect` zu `Server`

In der `onStart`-Callback ist es nur erlaubt, `echo` auszugeben, `Log` zu drucken, den Prozessnamen zu ändern. Es dürfen keine anderen Aktionen durchgeführt werden (es darf keine `server`-bezogenen Funktionen aufgerufen werden, da der Dienst noch nicht bereit ist). `onWorkerStart` und `onStart` werden in verschiedenen Prozessen parallel ausgeführt und haben keine Reihenfolge. **

In der `onStart`-Callback können Sie die Werte von `$server->master_pid` und `$server->manager_pid` in eine Datei speichern. Auf diese Weise können Sie Skripte schreiben, um diesen beiden `PID`en Signale zu senden, um Schließungen und Neustarts durchzuführen.

Das `onStart`-Ereignis wird im Hauptknoten des `Master`-Prozesses aufgerufen.

!> Globale Ressourcenobjekte, die in der `onStart`-Funktion erstellt werden, können nicht im `Worker`-Prozess verwendet werden, da beim Aufrufen der `onStart`-Funktion der `worker`-Prozess bereits erstellt wurde  
Neue Objekte werden im Hauptknoten erstellt, und der `Worker`-Prozess kann diesen Speicherbereich nicht erreichen  
Daher muss der Code zur Erstellung globaler Objekte vor dem Aufrufen von `Server::start` platziert werden, ein typisches Beispiel ist [Swoole\Table](/memory/table?id=vollständiges Beispiel)

* **Sicherheitshinweise**

In der `onStart`-Callback können Sie asynchrone undcoroutine-API verwenden, aber beachten Sie, dass dies potenzielle Konflikte mit `dispatch_func` und `package_length_func` geben könnte, **verwenden Sie sie nicht gleichzeitig**.

Bitte verwenden Sie in der `onStart`-Funktion keinen Timer starten. Wenn Sie in Ihrem Code die `Swoole\Server::shutdown()`-Methode ausführen, kann dies dazu führen, dass der Programm aufgrund eines ständig laufenden Timers nicht beendet werden kann.

Die `onStart`-Callback nimmt vor dem `return` keinen Client-Verbindungen an, daher können Sie sicher synchron blockierende Funktionen verwenden.

* **BASE-Modus**

Im [SWOOLE_BASE](/learn?id=swoole_base)-Modus gibt es keinen `master`-Prozess, daher gibt es kein `onStart`-Ereignis. Bitte verwenden Sie die `onStart`-Callback-Funktion nicht im BASE-Modus.

```
WARNING swReactorProcess_start: Die onStart-Ereignis mit SWOOLE_BASE ist veraltet
```


## onBeforeShutdown

?> **Dieses Ereignis tritt vor dem normalen Ende des `Server` auf** 

!> Swoole-Version >= `v4.8.0` ist verfügbar. In diesem Ereignis können Sie die coroutine-API verwenden.

```php
function onBeforeShutdown(Swoole\Server $server);
```


* **Parameter**

    * **`Swoole\Server $server`**
        * **Funktion**：Swoole\Server-Objekt
        * **Standardwert**：Kein
        * **Andere Werte**：Kein


## onShutdown

?> **Dieses Ereignis tritt nach dem normalen Ende des `Server` auf**

```php
function onShutdown(Swoole\Server $server);
```

  * **Parameter**

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

  * **Vorher hat der `Swoole\Server` bereits folgende Aktionen durchgeführt**

    * Alle [Reactor](/learn?id=reactor-thread)-Threads, `HeartbeatCheck`-Threads, `UdpRecv`-Threads geschlossen
    * Alle `Worker`-Prozesse, [Task-Prozesse](/learn?id=taskworker-prozess), [User-Prozesse](/server/methoden?id=addprocess) geschlossen
    * Alle `TCP/UDP/UnixSocket`-Überwachungsports geschlossen
    * Der Hauptknoten des [Reactor](/learn?id=reactor-thread) geschlossen

  !> Das Zwangskill-Prozess wird nicht die `onShutdown`-Callback aufrufen, wie zum Beispiel `kill -9`  
  Um den Hauptprozess normal zu beenden, muss das `SIGTERM`-Signal mit `kill -15` gesendet werden  
  Das Unterbrechen des Programms per Tastendruck in der Befehlslinie wird sofort stoppen, und der Boden wird den `onShutdown`-Callback nicht aufrufen

  * **Hinweise**

  !> Bitte rufen Sie in der `onShutdown`-Funktion keine asynchronen oder coroutine-bezogenen `API` auf, da der Boden alle Ereigniszyklen bereits zerstört hat, wenn das `onShutdown`-Ereignis ausgelöst wird;  
Zu diesem Zeitpunkt gibt es keine coroutine-Umwelt mehr, und wenn Entwickler die coroutine-bezogenen `API` benötigen, müssen sie manuell `Co\run` aufrufen, um einen [Coroutine-Container](/coroutine?id=was-ist-coroutine-container) zu erstellen.


## onWorkerStart

?> **Dieses Ereignis tritt beim Start des Worker-Prozesses/ [Task-Prozesses](/learn?id=taskworker-prozess) auf und hier werden Objekte erstellt, die während des Lebenszyklus des Prozesses verwendet werden können.**

```php
function onWorkerStart(Swoole\Server $server, int $workerId);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

    * **`int $workerId`**
      * **Funktion**：`Worker` 进程 `id` (nicht der Prozess-PID)
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

  * `onWorkerStart/onStart` werden parallel ausgeführt, es gibt keine Reihenfolge
  * Sie können durch das `$server->taskworker`-Eigenschaft überprüfen, ob es sich um einen `Worker`-Prozess oder um einen [Task-Prozess](/learn?id=taskworker-prozess) handelt
  * Wenn `worker_num` und `task_worker_num` über `1` hinausgesetzt sind, wird jedes Mal ein `onWorkerStart`-Ereignis ausgelöst, und Sie können die verschiedenen Arbeitsprozesse durch das Überprüfen von [$worker_id](/server/properties?id=worker_id) unterscheiden
  * Von `worker`-Prozessen werden Aufgaben an `task`-Prozesse gesendet, und nachdem der `task`-Prozess alle Aufgaben bearbeitet hat, wird er den `onFinish`-Callback-Funktion durch den [onFinish](/server/events?id=onfinish)-Ereignis informieren, um den `worker`-Prozess zu benachrichtigen. Zum Beispiel, wenn im Hintergrund eine Massenverteilung von Benachrichtigungs-E-Mails an hunderttausende von Nutzern durchgeführt wird, wird der Zustand des Vorgangs nach Abschluss der Operation als Senden angegeben, und zu diesem Zeitpunkt können andere Operationen fortgesetzt werden, bis die Massenverteilung der E-Mails abgeschlossen ist, und der Zustand des Vorgangs wird automatisch zu Gesendet geändert.

Das folgende Beispiel zeigt, wie man den Namen für Worker-Prozesse/ [Task-Prozesse](/learn?id=taskworker-prozess) ändert.

```php
$server->on('WorkerStart', function ($server, $worker_id){
    global $argv;
    if($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});
```

Wenn Sie das [Reload](/server/methoden?id=reload)-Prinzip zum erneuten Laden von Code verwenden möchten, müssen Sie Ihre Geschäftsdateien in der `onWorkerStart`-Funktion `require`, nicht am Anfang des Dateien. Dateien, die vor dem Aufrufen von `onWorkerStart` einbezogen wurden, werden nicht erneut geladen.

Öffentliche, unveränderliche PHP-Dateien können vor der `onWorkerStart` platziert werden. Obwohl Code nicht erneut geladen werden kann, sind alle `Worker` geteilt, und es ist nicht notwendig, zusätzliche Speicherplatz für diese Daten zu sparen.
Der Code nach `onWorkerStart` muss in jedem Prozess im Speicher gespeichert werden

  * `$worker_id` steht für die `ID` dieses `Worker`-Prozesses, siehe [$worker_id](/server/properties?id=worker_id) für den Bereich
  * [$worker_id](/server/properties?id=worker_id) hat nichts mit dem Prozess-PID zu tun, Sie können die `posix_getpid`-Funktion verwenden, um den PID zu erhalten

  * **Coroutine-Unterstützung**

    * In der `onWorkerStart`-Callback-Funktion werden automatisch Coroutinen erstellt, daher kann die `onWorkerStart`-Funktion die Coroutine-API aufrufen

  * **Hinweise**

    !> Wenn ein tödlicher Fehler auftritt oder der Code explizit `exit` aufruft, wird der `Worker/Task`-Prozess beendet, und der Verwalzungsprozess wird einen neuen Prozess erstellen. Dies kann zu einem endlosen Kreislauf führen, bei dem Prozesse ständig erstellt und zerstört werden
## onWorkerStop

?> **Dieses Ereignis tritt beim Beenden des `Worker`-Prozesses auf. In dieser Funktion können verschiedene Ressourcen zurückgewonnen werden, die vom `Worker`-Prozess beantragt wurden.**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $workerId`**
      * **Funktion**：ID des `Worker`-Prozesses (nicht der Prozess-PID)
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Hinweise**

    !> - Wenn der Prozess abnormale Ende erleidet, wie zum Beispiel durch einen Zwang `kill`, einen tödlichen Fehler oder ein `core dump`, kann die `onWorkerStop`-Rückruffunktion nicht ausgeführt werden.  
    - Bitte rufen Sie in der `onWorkerStop`-Funktion keine asynchronen oder koordinierten `API` auf, da alle [Ereigniskreise](/learn?id=wasistseventloop) bereits zerstört sind, wenn die `onWorkerStop`-Funktion ausgelöst wird.


## onWorkerExit

?> **Gültig nur, wenn die [reload_async](/server/setting?id=reload_async)-Funktion eingeschaltet ist. Siehe [Wie man den Service richtig neu startet](/question/use?id=swoolewiemanrichtigneuestartet)**

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $workerId`**
      * **Funktion**：ID des `Worker`-Prozesses (nicht der Prozess-PID)
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Hinweise**

    !> - Wenn der `Worker`-Prozess nicht beendet wird, wird die `onWorkerExit`-Funktion kontinuierlich ausgelöst  
    - Die `onWorkerExit`-Funktion wird innerhalb des `Worker`-Prozesses ausgelöst, und wenn es [Task-Prozesse](/learn?id=taskworkerprozess) gibt und diese [Ereigniskreise](/learn?id=wasistseventloop) enthalten, werden sie ebenfalls ausgelöst  
    - In der `onWorkerExit`-Funktion sollten asynchrone `Socket`-Verbindungen so weit wie möglich entfernt/geschlossen werden, damit der Prozess schließlich beendet wird, wenn die Anzahl der Ereignishandle, die im [Ereigniskreis](/learn?id=wasistseventloop) auflisten, zu `0` wird  
    - Wenn der Prozess keine Ereignishandle zum Überwachen hat, wird diese Funktion nicht aufgerufen, wenn der Prozess beendet wird  
    - Die `onWorkerStop`-Ereignisrückruffunktion wird erst nach dem Beenden des `Worker`-Prozesses ausgeführt


## onConnect

?> **Wenn eine neue Verbindung eingeht, wird diese Funktion im Worker-Prozess aufgerufen.**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $fd`**
      * **Funktion**：Dateideskriptor der Verbindung
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $reactorId`**
      * **Funktion**：ID des [Reactor](/learn?id=reactorthread)-Threads, in dem die Verbindung besteht
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Hinweise**

    !> Die `onConnect` und `onClose` Rückruffunktionen treten innerhalb des Worker-Prozesses auf und nicht im Hauptprozess auf.  
    Unter dem UDP-Protokoll gibt es nur das [onReceive](/server/events?id=onreceive)-Ereignis und keine `onConnect`/`onClose`-Ereignisse

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

      * In diesem Modus können `onConnect`, `onReceive` und `onClose` auf verschiedene Prozesse verteilt werden. Die PHP-Objekte, die mit der Verbindung verbunden sind, können nicht initialisiert und bereinigt werden, während der `onConnect`-Rückruffunktionsaufruf und der `onClose`-Rückruffunktionsaufruf stattfinden.
      * Die Ereignisse `onConnect`, `onReceive` und `onClose` können gleichzeitig ausgeführt werden, was zu Ausnahmen führen kann.


## onReceive

?> **Wenn Daten empfangen werden, wird diese Funktion aufgerufen und tritt im Worker-Prozess auf.**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $fd`**
      * **Funktion**：Dateideskriptor der Verbindung
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $reactorId`**
      * **Funktion**：ID des [Reactor](/learn?id=reactorthread)-Threads, in dem die TCP-Verbindung besteht
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`string $data`**
      * **Funktion**：Inhalt der empfangenen Daten, die entweder Text oder Binärdaten sein können
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Informationen zur Integrität von TCP-Paketen**

    * Die Verwendung der von der Basisplattform bereitgestellten Konfigurationsoptionen wie `open_eof_check`, `open_length_check` und `open_http_protocol` kann die Integrität der Pakete gewährleisten.
    * Wenn man die Basisplattform-Protokollbehandlung nicht verwendet und die PHP-Code nach dem Empfang der Daten in der [onReceive](/server/events?id=onreceive)-Funktion selbst analysiert und Pakete zusammenfügt/aufteilt, kann dies zu Problemen führen.

    Zum Beispiel kann man einen `$buffer = array()` hinzufügen und den `$fd` als `key` verwenden, um Kontextdaten zu speichern. Jedes Mal, wenn Daten empfangen werden, wird der String mit `$data` angehängt: `$buffer[$fd] .= $data`. Dann wird überprüft, ob der String in `$buffer[$fd]` ein vollständiges Paket ist.

    Standardmäßig wird der gleiche `$fd` einem gleichen `Worker` zugewiesen, sodass die Daten zusammengefügt werden können. Wenn `[dispatch_mode](/server/setting?id=dispatch_mode)` auf `3` gesetzt ist, sind die empfangenen Daten Prioritätsdaten, und Daten, die über den gleichen `$fd` gesendet werden, können auf verschiedene Prozesse verteilt werden. Daher kann die oben beschriebene Pakete-Zusammenfügung nicht verwendet werden.

  * **Mehrport-Überwachung, siehe [diese Abschnitt](/server/port)**

    Wenn der Hauptserver das Protokoll festgelegt hat, werden die zusätzlich überwachten Ports standardmäßig die Einstellungen des Hauptservers übernehmen. Es ist notwendig, die `set`-Methode explizit aufzurufen, um die Protokolle der Ports neu zu setzen.    

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9501);
    $port2 = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $port2->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        echo "[#".$server->worker_id."]\tClient[$fd]: $data\n";
    });
    ```

    Obwohl hier die `on`-Methode verwendet wurde, um die [onReceive](/server/events?id=onreceive)-Rückruffunktion zu registrieren, wird der neue überwachte Port `9502` aufgrund der fehlenden Aufruf der `set`-Methode nicht das HTTP-Protokoll verwenden. Wenn man mit einem `telnet`-Client eine Verbindung zum Port `9502` herstellt und einen String sendet, wird der Server das [onReceive](/server/events?id=onreceive)-Ereignis nicht auslösen.

  * **Hinweise**

    !> Wenn die automatische Protokolloption nicht aktiviert ist, darf die [onReceive](/server/events?id=onreceive)-Funktion pro Empfang bis zu `64K` Daten verarbeiten  
    Wenn die automatische Protokollverarbeitung aktiviert ist, wird die [onReceive](/server/events?id=onreceive)-Funktion vollständige Pakete empfangen, die nicht länger als [package_max_length](/server/setting?id=package_max_length) sein dürfen  
    Binärdaten werden unterstützt, `$data` kann Binärdaten sein
## onPacket

?> **Wenn ein `UDP`-Paket empfangen wird, wird diese Funktion als Rückruf aufgerufen und findet im `worker`-Prozess statt.**

```php
function onPacket(Swoole\Server $server, string $data, array $clientInfo);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`string $data`**
      * **Funktion**：Der empfangene Dateninhalt, der möglicherweise Text oder Binärdaten enthalten kann
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`array $clientInfo`**
      * **Funktion**：Kundeninformationen umfassen `Adresse/Port/server_socket` und andere Kundendaten, [Referenz UDP-Server](/start/start_udp_server)
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Hinweis**

    !> Wenn der Server sowohl `TCP`- als auch `UDP`-Ports überwacht, werden Daten, die dem `TCP`-Protokoll entsprechen, auf [onReceive](/server/events?id=onreceive) zurückgerufen, und `UDP`-Paketreffe werden auf `onPacket` zurückgerufen. Die vom Server festgelegten automatischen Protokollverarbeitungen (z.B. [TCP-Paketgrenzen](/learn?id=tcp_packet_boundaries)) sind für den `UDP`-Port ungültig, da `UDP`-Pakete von Natur aus eine Nachrichtengrenze aufweisen und keine zusätzlichen Protokollverarbeitungen benötigen.


## onClose

?> **Nachdem ein `TCP`-Kundenverbindung geschlossen wurde, wird diese Funktion im `Worker`-Prozess aufgerufen.**

```php
function onClose(Swoole\Server $server, int $fd, int $reactorId);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $fd`**
      * **Funktion**：Der Dateideskriptor der Verbindung
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $reactorId`**
      * **Funktion**：Von welchem `reactor`-Thread es kommt, bei aktiver `close` ist es negativ
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Tipps**

    * **Aktives Schließen**

      * Wenn der Server die Verbindung aktiv schließt, wird dieser Parameter von unten auf `-1` gesetzt. Man kann durch das Überprüfen von `$reactorId < 0` unterscheiden, ob die Schließung vom Server oder vom Kunden initiiert wurde.
      * Nur wenn die `close`-Methode im `PHP`-Code aktiv aufgerufen wird, wird dies als aktive Schließung angesehen.

    * **Herzschläge**

      * [Herzschläge](/server/setting?id=heartbeat_check_interval) werden von dem Herzschlag-Thread benachrichtigt, wenn eine Schließung stattfindet, und der `$reactorId`-Parameter von [onClose](/server/events?id=onclose) ist bei Schließung nicht `-1`.

  * **Hinweis**

    !> - Wenn die [onClose](/server/events?id=onclose)-Rückruffunktion einen tödlichen Fehler verursacht und nicht erfolgreich ist, kann dies zu einem Verbindungsleak führen. Mit dem `netstat`-Befehl können Sie eine große Anzahl von `TCP`-Verbindungen im Zustand `CLOSE_WAIT` sehen.
    - Ob die Schließung durch den Kunden initiiert wird oder ob der Server die Verbindung aktiv mit `$server->close()` schließt, wird dieses Ereignis auslösen. Daher wird diese Funktion immer aufgerufen, sobald eine Verbindung geschlossen wird.  
    - In der [onClose](/server/events?id=onclose)-Funktion können Sie immer noch die [getClientInfo](/server/methods?id=getClientInfo)-Methode verwenden, um Verbindungsinformationen zu erhalten. Die `TCP`-Verbindung wird erst nach Abschluss der [onClose](/server/events?id=onclose)-Rückruffunktion geschlossen.  
    - Hier wird in der [onClose](/server/events?id=onclose)-Funktion aufgerufen, was bedeutet, dass die Kundenverbindung bereits geschlossen ist, daher ist es nicht notwendig, `$server->close($fd)` auszuführen. Das Ausführen von `$server->close($fd)` im Code würde eine PHP-Fehlwarnung auslösen.


## onTask

?> **Wird innerhalb des `task`-Prozesses aufgerufen. `Worker`-Prozesse können die [task](/server/methods?id=task)-Funktion verwenden, um neue Aufgaben an den `task_worker`-Prozess zu übergeben. Der aktuelle [Task-Prozess](/learn?id=taskworker进程) wechselt beim Aufrufen der [onTask](/server/events?id=ontask)-Rückruffunktion den Prozessstatus in beschäftigt und nimmt keine neuen Aufgaben mehr an. Wenn die [onTask](/server/events?id=ontask)-Funktion zurückkehrt, wechselt der Prozessstatus wieder in frei und nimmt neue `Task` an.**

```php
function onTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $task_id`**
      * **Funktion**：Die `task`-Prozess-ID, die die Aufgabe ausführt【`$task_id` und `$src_worker_id` zusammen bilden die globale eindeutige ID, unterschiedliche `worker`-Prozesse können dieselben Task-IDs für Aufgaben haben】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $src_worker_id`**
      * **Funktion**：Die `worker`-Prozess-ID, die die Aufgabe übergibt【`$task_id` und `$src_worker_id` zusammen bilden die globale eindeutige ID, unterschiedliche `worker`-Prozesse können dieselben Task-IDs für Aufgaben haben】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`mixed $data`**
      * **Funktion**：Der Inhalt der Aufgabe
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Tipps**

    * **Ab v4.2.12, wenn [task_enable_coroutine](/server/setting?id=task_enable_coroutine) aktiviert ist, ist die Rückruffunktionform wie folgt:**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); // Aufgabe abschließen, beenden und Daten zurückgeben
      });
      ```

    * **Ergebnisse der Ausführung an den `worker`-Prozess zurückgeben**

      * **In der [onTask](/server/events?id=ontask)-Funktion kann mit `return` ein String zurückgegeben werden, der den `worker`-Prozess darstellt. Im `worker`-Prozess wird die [onFinish](/server/events?id=onfinish)-Funktion ausgelöst, was bedeutet, dass die übergebene `task` abgeschlossen ist. Natürlich können Sie auch die [Swoole\Server->finish()](/server/methods?id=finish)-Methode verwenden, um die [onFinish](/server/events?id=onfinish)-Funktion zu triggern, ohne zurückzugeben**

      * Die zurückgegebene Variable kann jedes nicht `null`-Wertige `PHP`-Variable sein

  * **Hinweis**

    !> - Wenn die [onTask](/server/events?id=ontask)-Funktion einen tödlichen Fehler verursacht und nicht erfolgreich ist oder von einem externen Prozess gewaltsam `kill` wird, wird die aktuelle Aufgabe verworfen, aber dies beeinträchtigt nicht andere anstehende `Task`


## onFinish

?> **Diese Rückruffunktion wird im Worker-Prozess aufgerufen, wenn die Aufgabe, die vom Worker-Prozess übergeben wurde, im Task-Prozess abgeschlossen ist. Der [Task-Prozess](/learn?id=taskworker进程) sendet das Ergebnis der Aufgabeverarbeitung an den Worker-Prozess, indem er die `Swoole\Server->finish()`-Methode verwendet.**

```php
function onFinish(Swoole\Server $server, int $task_id, mixed $data)
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Swoole\Server-Objekt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`int $task_id`**
      * **Funktion**：Die ID des Task-Prozesses, der die Aufgabe ausführt
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`mixed $data`**
      * **Funktion**：Der Inhalt des Ergebnisses der Aufgabeverarbeitung
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Hinweis**

    !> - Wenn im [Task-Prozess](/learn?id=taskworker进程) der [onTask](/server/events?id=ontask)-Ereignis die `finish`-Methode nicht aufgerufen wird oder kein Ergebnis zurückgegeben wird, wird der Worker-Prozess den [onFinish](/server/events?id=onfinish)-Prozess nicht auslösen  
    - Der Worker-Prozess, der das [onFinish](/server/events?id=onfinish)-Logik ausführt, ist derselbe Prozess, der die Aufgabe übergibt
## onPipeMessage

?> **Wenn der Arbeitsprozess eine Nachricht über das [unixSocket](/learn?id=Was ist IPC) erhält, die von `$server->sendMessage()` gesendet wurde, wird das Ereignis `onPipeMessage` ausgelöst. sowohl `worker/task` Prozesse als auch der Manager-Prozess können das Ereignis `onPipeMessage` auslösen.**

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Ein Swoole\Server Objekt
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`int $src_worker_id`**
      * **Funktion**：ID des `Worker` Prozesses, von dem die Nachricht stammt
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`mixed $message`**
      * **Funktion**：Inhalt der Nachricht, kann jeder PHP-Typ sein
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden


## onWorkerError

?> **Wenn ein `Worker/Task` Prozess einen Fehler hat, wird diese Funktion im Manager-Prozess aufgerufen.**

!> Diese Funktion wird hauptsächlich zur Alarmierung und Überwachung verwendet. Sobald ein Worker-Prozess unerwartet beendet wird, ist es sehr wahrscheinlich, dass ein tödlicher Fehler oder ein Core Dump aufgetreten ist. Durch das Aufzeichnen von Protokollen oder das Senden von Alarminformationen wird den Entwicklern mitgeteilt, dass entsprechende Maßnahmen ergriffen werden müssen.

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

  * **Parameter** 

    * **`Swoole\Server $server`**
      * **Funktion**：Ein Swoole\Server Objekt
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`int $worker_id`**
      * **Funktion**：ID des fehlerhaften `worker` Prozesses
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`int $worker_pid`**
      * **Funktion**：PID des fehlerhaften `worker` Prozesses
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`int $exit_code`**
      * **Funktion**：Der Abbruchstatuscode, der Bereich ist `0～255`
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

    * **`int $signal`**
      * **Funktion**：Das Signal, das den Prozess beendete
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden

  * **Häufige Fehler**

    * `signal = 11`：Dies bedeutet, dass der `Worker` Prozess einen `segment fault` hat, was möglicherweise ein grundlegender `BUG` ausgelöst hat. Bitte sammle `core dump` Informationen und `valgrind` Speicherüberwachungsprotokolle und [feedbacks diese Issue an die Swoole Entwicklergruppe](/other/issue)
    * `exit_code = 255`：Dies bedeutet, dass der Worker-Prozess einen `Fatal Error` hat. Bitte überprüfe den PHP-Fehlerlog, um das Problematisches PHP-Code zu finden und zu lösen
    * `signal = 9`：Dies bedeutet, dass der `Worker` von einem Systemzwang beendet wurde. Bitte überprüfen, ob es eine manuelle `kill -9` Operation gab und ob im `dmesg` Informationen über ein `OOM (Out of memory)` bestehen
    * Wenn es ein OOM gibt, wurde zu viel Speicher zugeordnet. 1. Überprüfe die `Server` Einstellungen, ob [socket_buffer_size](/server/setting?id=socket_buffer_size) oder andere Zuordnungen zu groß sind; 2. Ob sehr große [Swoole\Table](/memory/table) Speichermodule erstellt wurden.


## onManagerStart

?> **Dieses Ereignis wird ausgelöst, wenn der Verwalterprozess gestartet wird**

```php
function onManagerStart(Swoole\Server $server);
```

  * **Hinweise**

    * In dieser Rückruffunktion kann der Name des Verwalterprozesses geändert werden.
    * In Versionen vor `4.2.12` kann im `manager` Prozess keine Timer hinzugefügt, keine Aufgaben übermittelt oder keine Coroutine verwendet werden.
    * In Versionen `4.2.12` oder höher kann der `manager` Prozess Timer mit einem Signal-basierten Synchronisierungsmuster verwenden
    * Im `manager` Prozess kann die [sendMessage](/server/methods?id=sendMessage) Schnittstelle verwendet werden, um Nachrichten an andere Arbeitsprozesse zu senden

    * **Starter顺序**

      * `Task` und `Worker` Prozesse wurden bereits 创建
      * Der Zustand des `Master` Prozesses ist unklar, da der `Manager` und der `Master` parallel sind und das `onManagerStart` Rückrufereignis auftritt, kann nicht festgestellt werden, ob der `Master` Prozess bereit ist

    * **BASE 模式**

      * Im [SWOOLE_BASE](/learn?id=swoole_base) Modus, wenn Parameter wie `worker_num`, `max_request` und `task_worker_num` festgelegt sind, wird im Hintergrund ein `manager` Prozess zum Verwalten der Arbeitsprozesse erstellt. Daher werden die Ereignisrückrufe `onManagerStart` und `onManagerStop` ausgelöst.


## onManagerStop

?> **Dieses Ereignis wird ausgelöst, wenn der Verwalterprozess beendet wird**

```php
function onManagerStop(Swoole\Server $server);
```

 * **Hinweise**

  * Wenn das `onManagerStop` Ereignis ausgelöst wird, bedeutet dies, dass die `Task` und `Worker` Prozesse bereits beendet wurden und vom `Manager` Prozess zurückgewonnen wurden.


## onBeforeReload

?> **Dieses Ereignis wird vor dem `Reload` des Workerprozesses ausgelöst und wird im Managerprozess aufgerufen**

```php
function onBeforeReload(Swoole\Server $server);
```

  * **Parameter**

    * **`Swoole\Server $server`**
      * **Funktion**：Ein Swoole\Server Objekt
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden


## onAfterReload

?> **Dieses Ereignis wird nach dem `Reload` des Workerprozesses ausgelöst und wird im Managerprozess aufgerufen**

```php
function onAfterReload(Swoole\Server $server);
```

  * **Parameter**

    * **`Swoole\Server $server`**
      * **Funktion**：Ein Swoole\Server Objekt
      * **Standardwert**：Nicht vorhanden
      * **Andere Werte**：Nicht vorhanden


## Ereignisausführungse顺序

* Alle Ereignisrückrufe treten nach dem Starten des `$server->start` auf
* Das letzte Ereignis beim Schließen des Servers ist `onShutdown`
* Nach dem erfolgreichen Start des Servers werden `onStart/onManagerStart/onWorkerStart` in verschiedenen Prozessen asynchron ausgeführt
* `onReceive/onConnect/onClose` werden im `Worker` Prozess ausgelöst
* Bei dem Start/Beenden von `Worker/Task` Prozessen werden jeweils einmal `onWorkerStart/onWorkerStop` aufgerufen
* Das [onTask](/server/events?id=ontask) Ereignis tritt nur im [task Prozess](/learn?id=taskworker进程) auf
* Das [onFinish](/server/events?id=onfinish) Ereignis tritt nur im `worker` Prozess auf
* Die Reihenfolge der Ausführung von `onStart/onManagerStart/onWorkerStart` ist unbestimmt

## Objektorientierter Stil

Wenn [event_object](/server/setting?id=event_object) aktiviert ist, ändern sich die Parameter der folgenden Ereignisrückrufe.

* Clientverbindung [onConnect](/server/events?id=onconnect)
```php
$server->on('Connect', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* Empfangsdaten [onReceive](/server/events?id=onreceive)
```php
$server->on('Receive', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* Verbindung schließen [onClose](/server/events?id=onclose)
```php
$server->on('Close', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```


* UDP-Paketempfang [onPacket](/server/events?id=onpacket)
```php
$server->on('Packet', function (Swoole\Server $serv, Swoole\Server\Packet $object) {
    var_dump($object);
});
```


* Prozess间通信 [onPipeMessage](/server/events?id=onpipemessage)
```php
$server->on('PipeMessage', function (Swoole\Server $serv, Swoole\Server\PipeMessage $msg) {
    var_dump($msg);
    $object = $msg->data;
    $serv->sendto($object->address, $object->port, $object->data, $object->server_socket);
});
```


* Prozessfehler [onWorkerError](/server/events?id=onworkererror)
```php
$serv->on('WorkerError', function (Swoole\Server $serv, Swoole\Server\StatusInfo $info) {
    var_dump($info);
});
```


* Taskprozess 任务接受 [onTask](/server/events?id=ontask)
```php
$server->on('Task', function (Swoole\Server $serv, Swoole\Server\Task $task) {
    var_dump($task);
});
```


* worker进程接收task进程的处理结果 [onFinish](/server/events?id=onfinish)
```php
$server->on('Finish', function (Swoole\Server $serv, Swoole\Server\TaskResult $result) {
    var_dump($result);
});
```

* [Swoole\Server\Event](/server/event_class)
* [Swoole\Server\Packet](/server/packet_class)
* [Swoole\Server\PipeMessage](/server/pipemessage_class)
* [Swoole\Server\StatusInfo](/server/statusinfo_class)
* [Swoole\Server\Task](/server/task_class)
* [Swoole\Server\TaskResult](/server/taskresult_class)
