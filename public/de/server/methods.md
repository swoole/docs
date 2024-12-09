# Methoden


## __construct() 

Erstellt ein Objekt für einen [Asynchronen IO](/learn?id=同步io异步io) TCP Server.

```php
Swoole\Server::__construct(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```

  * **Parameter**

    * `string $host`

      * Funktion: Legt die IP-Adresse fest, auf der der Server lauscht.
      * Standardwert: Keiner.
      * Weitere Werte: Keiner.

      !> Bei IPv4 wird `127.0.0.1` verwendet, um den lokalen Computer zu erreichen, und `0.0.0.0` für alle Adressen.
      Bei IPv6 wird `::1` für den lokalen Computer verwendet und `::` (entsprechend `0:0:0:0:0:0:0:0`) für alle Adressen.

    * `int $port`

      * Funktion: Legt den Port fest, auf dem der Server lauscht, wie zum Beispiel `9501`.
      * Standardwert: Keiner.
      * Weitere Werte: Keiner.

      !> Wenn der Wert von `$sockType` [UnixSocket Stream/Dgram](/learn?id=什么是IPC) ist, wird dieser Parameter ignoriert.
      Um Ports unter `1024` zu lauschen, ist `root` Berechtigung erforderlich.
      Wenn dieser Port bereits besetzt ist, wird der Start des Servers mit `$server->start` fehlschlagen.

    * `int $mode`

      * Funktion: Legt den Betriebmodus fest.
      * Standardwert: [SWOOLE_PROCESS](/learn?id=swoole_process) Mehrprozessmodus (Standard).
      * Weitere Werte: [SWOOLE_BASE](/learn?id=swoole_base) Basismodus, [SWOOLE_THREAD](/learn?id=swoole_thread) Mehrthreadmodus (verfügbar ab Swoole 6.0).

      ?> Im `SWOOLE_THREAD` Modus können Sie hier [Thread + Server (asynchrones Stil](/thread/thread?id=线程-服务端（异步风格）)) sehen, wie man im Mehrthreadmodus einen Server einrichtet.

      !> Ab Swoole 5 ist der Standardwert für den Betriebmodus `SWOOLE_BASE`.

    * `int $sockType`

      * Funktion: Legt den Typ des Server-Sets fest.
      * Standardwert: Keiner.
      * Weitere Werte:
        * `SWOOLE_TCP/SWOOLE_SOCK_TCP` tcp ipv4 socket
        * `SWOOLE_TCP6/SWOOLE_SOCK_TCP6` tcp ipv6 socket
        * `SWOOLE_UDP/SWOOLE_SOCK_UDP` udp ipv4 socket
        * `SWOOLE_UDP6/SWOOLE_SOCK_UDP6` udp ipv6 socket
        * [SWOOLE_UNIX_DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) unix socket dgram
        * [SWOOLE_UNIX_STREAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/stream_server.php) unix socket stream 

      !> Mit `$sock_type` | `SWOOLE_SSL` kann man einen `SSL` Tunnelverschluss aktivieren. Nach der Aktivierung von `SSL` muss konfiguriert werden. [ssl_key_file](/server/setting?id=ssl_cert_file) und [ssl_cert_file](/server/setting?id=ssl_cert_file)

  * **Beispiel**

```php
$server = new \Swoole\Server($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP);

// Man kann sowohl UDP als auch TCP mischen, gleichzeitig Innen- und Außennetzeports überwachen, Mehrportüberwachung siehe Abschnitt addlistener.
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP); // TCP hinzufügen
$server->addlistener("192.168.1.100", 9503, SWOOLE_SOCK_TCP); // Websocket hinzufügen
$server->addlistener("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDP
$server->addlistener("/var/run/myserv.sock", 0, SWOOLE_UNIX_STREAM); // UnixSocket Stream
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP | SWOOLE_SSL); // TCP + SSL

$port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP); // Der System legt zufällig einen Port zu, der zurückgelegte Wert ist der zufällig zugewiesene Port
echo $port->port;
```
  

## set()

Wird zur Einstellung verschiedener Parameter während des Betriebs verwendet. Nach dem Start des Servers können die durch `$serv->setting`访问en Parameterarray durch die Methode `Server->set` geändert werden.

```php
Swoole\Server->set(array $setting): void
```

!> Die Methode `Server->set` muss vor dem Starten des Servers aufgerufen werden, die Bedeutung jeder einzelnen Einstellung finden Sie unter [dieser Abschnitt](/server/setting).

  * **Beispiel**

```php
$server->set(array(
    'reactor_num'   => 2,     // Anzahl der Threads
    'worker_num'    => 4,     // Anzahl der Prozesse
    'backlog'       => 128,   // Länge des Listen-Queues festlegen
    'max_request'   => 50,    // Maximale Anzahl der Anfragen pro Prozess
    'dispatch_mode' => 1,     // Datenpaketverteilungspolitik
));
```


## on()

Registriert eine Ereignishandlers回调funktion für den `Server`.

```php
Swoole\Server->on(string $event, callable $callback): bool
```

!> Bei wiederholtem Aufrufen der `on` Methode wird die vorherige Einstellung überschrieben

!> Ab PHP 8.2 wird die direkte Einstellung dynamischer Eigenschaften nicht mehr unterstützt, und wenn `$event` kein vom Swoole festgelegtes Ereignis ist, wird eine Warnung geworfen

  * **Parameter**

    * `string $event`

      * Funktion: Name des Ereignishandlers
      * Standardwert: Keiner
      * Weitere Werte: Keiner

      !> Case-insensitive, welche Ereignishandler回调funktionen verfügbar sind, siehe [dieser Abschnitt](/server/events), Ereignisnamenstrings sollten nicht mit `on` enden

    * `callable $callback`

      * Funktion: Die回调funktion
      * Standardwert: Keiner
      * Weitere Werte: Keiner

      !> Kann ein String mit dem Namen einer Funktion sein, eine statische Methode eines Klasses, ein Methodenarray eines Objekts, eine anonyme Funktion Referenz [dieser Abschnitt](/learn?id=einige Möglichkeiten,回调funktionen einzustellen).
  
  * **Rückgabe**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlschlägt.

  * **Beispiel**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('connect', function ($server, $fd){
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();
```


## addListener()

Fügt einen weiteren lauschen Port hinzu. In Geschäftscode kann man durch Aufrufen der Methode [Swoole\Server->getClientInfo](/server/methods?id=getclientinfo) herausfinden, von welchem Port eine Verbindung stammt.

```php
Swoole\Server->addListener(string $host, int $port, int $sockType): bool|Swoole\Server\Port
```

!> Um Ports unter `1024` zu lauschen, ist `root` Berechtigung erforderlich  
Der Hauptserver ist ein `WebSocket` oder `HTTP` Protokoll, und der neu lauschende `TCP` Port übernimmt standardmäßig die Protokolleinstellungen des Hauptservers. Um ein neues Protokoll zu aktivieren, muss die Methode `set` separat aufgerufen werden [Details finden Sie hier](/server/port).
Sie können [hier](/server/server_port) den umfassenden Bericht zu `Swoole\Server\Port` betrachten. 

  * **Parameter**

    * `string $host`

      * Funktion: identisch mit `$host` in `__construct()`
      * Standardwert: identisch mit `$host` in `__construct()`
      * Weitere Werte: identisch mit `$host` in `__construct()`

    * `int $port`

      * Funktion: identisch mit `$port` in `__construct()`
      * Standardwert: identisch mit `$port` in `__construct()`
      * Weitere Werte: identisch mit `$port` in `__construct()`

    * `int $sockType`

      * Funktion: identisch mit `$sockType` in `__construct()`
      * Standardwert: identisch mit `$sockType` in `__construct()`
      * Weitere Werte: identisch mit `$sockType` in `__construct()`
  
  * **Rückgabe**

    * Gibt `Swoole\Server\Port` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlschlägt.
!> - Unter dem `Unix Socket`-Modus muss der `$host`-Parameter mit einem zugänglichen Dateipfad angegeben werden, der `$port`-Parameter wird ignoriert  

- Unter dem `Unix Socket`-Modus wird der Client-`$fd` nicht mehr als Zahl, sondern als String mit dem Dateipfad des сокels verwendet  
- Unter Linux kann man auch eine Verbindung über eine IPv4-Adresse herstellen, nachdem man einen IPv6-Port gehört hat


## listen()

Diese Methode ist ein Synonym für `addlistener`.

```php
Swoole\Server->listen(string $host, int $port, int $type): bool|Swoole\Server\Port
```


## addProcess()

Füge einen benutzerdefinierten Arbeitsprozess hinzu. Diese Funktion wird normalerweise verwendet, um einen speziellen Arbeitsprozess zu erstellen, der für Überwachung, Berichterstattung oder andere spezielle Aufgaben verwendet wird.

```php
Swoole\Server->addProcess(Swoole\Process $process): int
```

!> Es ist nicht erforderlich, `start` auszuführen. Wenn der `Server` gestartet wird, werden Prozesse automatisch 创建 und die angegebenen Unterprozesswaysfunctions ausgeführt

  * **Parameter**
  
    * [Swoole\Process](/process/process)

      * Funktion: Ein `Swoole\Process` Objekt
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückgabewert**

    * Gibt die Prozess-ID zurück, um den Erfolg der Operation anzuzeigen, andernfalls wird ein tödlicher Fehler im Programm ausgelöst.

  * **Hinweise**

    !> - Die von einem benutzerdefinierten Prozess geschaffenen Unterprozesse können die verschiedenen Methoden des `$server`-Objekts aufrufen, wie `getClientList/getClientInfo/stats`.                                   
    - In `Worker/Task` Prozessen können Methoden des `$process` bereitgestellt werden, um mit Unterprozessen zu kommunizieren.        
    - In benutzerdefinierten Prozessen kann die Methode `$server->sendMessage` verwendet werden, um mit `Worker/Task` Prozessen zu kommunizieren.      
    - In benutzerdefinierten Prozessen darf die Schnittstelle `Server->task/taskwait` nicht verwendet werden.              
    - In benutzerdefinierten Prozessen können Schnittstellen wie `Server->send/close` verwendet werden.         
    - In benutzerdefinierten Prozessen sollte ein `while(true)`-Loop (wie im folgenden Beispiel) oder ein [EventLoop](/learn?id=was-ist-ein-eventloop) (z.B. um einen Timer zu erstellen) durchgeführt werden, sonst wird der benutzerdefinierte Prozess ständig beendet und neu gestartet.         

  * **Lebenszyklus**

    ?> - Der Lebenszyklus eines benutzerdefinierten Prozesses ist der gleiche wie der des `Master` und des [Manager](/learn?id=manager-prozess), er wird durch das [reload](/server/methods?id=reload) nicht beeinflusst.     
    - Benutzerdefinierte Prozesse werden vom `reload`-Befehl nicht kontrolliert, und beim `reload` wird den benutzerdefinierten Prozessen keine Information gesendet.        
    - Wenn der Server mit `shutdown` geschlossen wird, wird dem benutzerdefinierten Prozess ein `SIGTERM`-Signal gesendet, um den benutzerdefinierten Prozess zu beenden.            
    - Benutzerdefinierte Prozesse werden von einem `Manager`-Prozess gehostet, und wenn ein tödlicher Fehler auftritt, wird der `Manager`-Prozess einen neuen erstellen.         
    - Benutzerdefinierte Prozesse lösen auch keine Ereignisse wie `onWorkerStop` aus. 

  * **Beispiel**

    ```php
    $server = new Swoole\Server('127.0.0.1', 9501);
    
    /**
     * Ein benutzerdefinierter Prozess realisiert die Übertragung von Informationen, umringt sich ständig mit Nachrichten über unixSocket und sendet diese gleichzeitig an alle Verbindungen des Servers
     */
    $process = new Swoole\Process(function ($process) use ($server) {
        $socket = $process->exportSocket();
        while (true) {
            $msg = $socket->recv();
            foreach ($server->connections as $conn) {
                $server->send($conn, $msg);
            }
        }
    }, false, 2, 1);
    
    $server->addProcess($process);
    
    $server->on('receive', function ($serv, $fd, $reactor_id, $data) use ($process) {
        // Übertragen Sie alle erhaltenen Nachrichten
        $socket = $process->exportSocket();
        $socket->send($data);
    });
    
    $server->start();
    ```

    Referenz [Prozess-Interprocess Communication Kapitel](/process/process?id=exportsocket).


## start()

Starte den Server und lausche auf alle `TCP/UDP` Ports.

```php
Swoole\Server->start(): bool
```

!> Hinweis: Im Folgenden wird das Beispiel unter dem [SWOOLE_PROCESS](/learn?id=swoole_process)-Modus dargestellt

  * **Hinweise**

    - Nach dem erfolgreichen Start werden `worker_num+2` Prozesse erstellt. Der `Master`-Prozess + der `Manager`-Prozess + `serv->worker_num` `Worker`-Prozesse.  
    - Ein erfolgreicher Start gibt sofort `false` zurück.
    - Nach dem erfolgreichen Start wird ein Ereignis-Schleifen eingeleitet und auf Anfragen von Clients gewartet. Der Code nach der `start` Methode wird nicht ausgeführt.  
    - Nachdem der Server geschlossen wurde, gibt die `start` Funktion `true` zurück und setzt den Fortschritt fort.  
    - Wenn `task_worker_num` festgelegt ist, wird die Anzahl der [Taskprozesse](/learn?id=taskworker-prozess) entsprechend erhöht.   
    - Methoden vor der `start` Methode können nur vor der `start` Anrufung verwendet werden, Methoden nach der `start` Methode können nur in Ereignis-Rückruffunktionen wie [onWorkerStart](/server/events?id=onworkerstart), [onReceive](/server/events?id=onreceive) usw. verwendet werden.

  * **Erweiterungen**
  
    * Master Hauptprozess

      * Im Hauptprozess gibt es mehrere [Reactor](/learn?id=reactor-thread)-Threads, die auf `epoll/kqueue/select` basierend für das Round Robin-Netzwerkereignis-Polling verwendet werden. Nach Erhalt von Daten wird diese an den `Worker`-Prozess weitergeleitet, um sie zu verarbeiten.
    
    * Manager Prozess

      * Verwaltet alle `Worker`-Prozesse, und wenn ein `Worker`-Prozess sein Lebenszyklus beendet oder ein Fehler auftritt, wird er automatisch recycelt und ein neuer `Worker`-Prozess wird erstellt.
    
    * Worker Prozess

      * Verarbeitet die erhaltenen Daten, einschließlich der Parsing von Protokollen und der Antwort auf Anfragen. Wenn `worker_num` nicht festgelegt ist, werden unter der Erde so viele `Worker`-Prozesse gestartet, wie es CPU-核心 gibt.
      * Ein erfolgreicher Start in der Erweiterung führt zu einem tödlichen Fehler, bitte überprüfen die entsprechenden Informationen im PHP-Fehlerlog. `errno={number}` ist der Standard-Linux Errno, siehe entsprechende Dokumentation.
      * Wenn das `log_file`-Setting aktiviert ist, werden Informationen in das angegebene `Log`-File gedruckt.

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist
       
  * **Häufige Fehlermeldungen beim Start**

    * Das `bind`-Port ist fehlgeschlagen, weil dieser Port bereits von einem anderen Prozess besetzt ist.
    * Es wurde kein Pflichten-Rückruffunktionsparameter festgelegt, der Start ist fehlgeschlagen.
    * Es gibt einen tödlichen Fehler in der PHP-Code, bitte überprüfen die PHP-Fehlerinformationen im `php_errors.log`.
    * Führen Sie `ulimit -c unlimited` aus, um einen Core Dump zu öffnen und zu sehen, ob ein Segmentfehler auftritt.
    - Schließen Sie `daemonize` und `log` ab, damit Fehlermeldungen auf den Bildschirm gedruckt werden können.


## reload()

Starte alle Worker/Task Prozesse sicher neu.

```php
Swoole\Server->reload(bool $only_reload_taskworker = false): bool
```

!> Zum Beispiel: Ein belebter Hintergrundserver bearbeitet ständig Anfragen, und wenn ein Administrator den Prozess durch das `kill`-Befehl beendet/wiederstartet, kann dies dazu führen, dass der Code genau in der Mitte abgebrochen wird.  
In solchen Fällen kann es zu Ungleichheiten bei den Daten kommen. Zum Beispiel im Transaktionssystem ist der nächste Schritt nach der Zahlungslogik der Versand, und wenn der Prozess nach der Zahlungslogik beendet wird, führt dies dazu, dass der Nutzer Geld bezahlt hat, aber keinen Versand durchgeführt hat, was sehr ernst ist.  
Swoole bietet ein flexibles Mechanismus zum endgültigen Beenden/Wiederstarten, und Administratoren müssen nur spezielle Signale an den Server senden, damit die Worker-Prozesse des Servers sicher beendet werden können. Referenz [Wie man den Service richtig neu startet](/question/use?id=swoole-wie-man-den-service-richtig-neu-startet).

  * **Parameter**
  
    * `bool $only_reload_taskworker`

      * Funktion: Ob nur [Taskprozesse](/learn?id=taskworker-prozess) neu gestartet werden sollen
      * Standardwert: false
      * Andere Werte: true


!> - Das `reload` hat einen Schutzmechanismus, wenn ein `reload` gerade stattfindet und ein neues Signal für das `reload` empfangen wird, wird dieses ignoriert.

- Wenn `user/group` festgelegt ist, haben die Worker-Prozesse möglicherweise keine Berechtigung, Informationen an den master-Prozess zu senden, in diesem Fall muss der Root-Account verwendet werden, um den `kill`-Befehl im Shell auszuführen, um den Server neu zu starten.
- Der `reload`-Befehl ist für Prozesse, die mit [addProcess](/server/methods?id=addProcess) hinzugefügt wurden, nicht wirksam.

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist
       
  * **Erweiterungen**
  
    * **Senden von Signalen**
    
        * `SIGTERM`: Sende dieses Signal an den Hauptprozess/Verwaltungsvorgang, der Server wird sicher beendet.
        * In PHP-Code kann die Methode `$serv->shutdown()` verwendet werden, um dies zu erreichen.
        * `SIGUSR1`: Sende das `SIGUSR1`-Signal an den Hauptprozess/Verwaltungsvorgang, um alle `Worker`-Prozesse und `TaskWorker`-Prozesse stabil zu `restart`.
        * `SIGUSR2`: Sende das `SIGUSR2`-Signal an den Hauptprozess/Verwaltungsvorgang, um alle `Task`-Prozesse stabil zu neu starten.
        * In PHP-Code kann die Methode `$serv->reload()` verwendet werden, um dies zu erreichen.
        
    ```shell
    # Neu starten aller worker Prozesse
    kill -USR1 主进程PID
    
    # Nur task Prozesse neu starten
    kill -USR2 主进程PID
    ```
      
      > [Referenz: Linux Signallisten](/other/signal)

    * **Prozessmodus**
    
        In Prozessen, die mit `Process` gestartet wurden, werden TCP-Verbindungen von Clients im `Master`-Prozess aufrechterhalten, und der Neustart und das plötzliche Ausfallen von `worker`-Prozessen beeinflussen die Verbindungen selbst nicht.

    * **Basismodus**
    
        In `Basismodus` werden Clientverbindungen direkt im `Worker`-Prozess aufrechterhalten, daher werden alle Verbindungen unter `reload` abgeschnitten.

    !> Der `Basismodus` unterstützt kein `reload` für [Taskprozesse](/learn?id=taskworker-prozess)
    
    * **Gültigkeitsbereich von Reload**

      Das `Reload`-Operieren kann nur PHP-Dateien, die nach dem Start des `Worker`-Prozesses geladen wurden, neu laden. Verwenden Sie die Funktion `get_included_files`, um anzuzeigen, welche Dateien vor dem `WorkerStart` geladen wurden. PHP-Dateien in dieser Liste können auch nach einem `Reload` nicht neu geladen werden. Um dies zu erreichen, muss der Server geschlossen und neu gestartet werden.

    ```php
    $serv->on('WorkerStart', function(Swoole\Server $server, int $workerId) {
        var_dump(get_included_files()); // Diese Array-Dateien wurden vor dem Prozessstart geladen, daher können sie nicht neu geladen werden
    });
    ```

    * **APC/OPcache**
    
        Wenn PHP APC/OPcache aktiviert ist, kann das `Reload`-Neuladen beeinträchtigt werden, es gibt zwei Lösungen.
        
        * Schalten Sie die `stat`-Überwachung von APC/OPcache ein, wenn Dateien aktualisiert werden, wird APC/OPcache automatisch die OPCode aktualisieren.
        - Führen Sie vor dem Laden von Dateien (require, include usw.) in `onWorkerStart` die `apc_clear_cache` oder `opcache_reset` durch, um den OPCode-Cache zu leeren.

  * **Hinweise**

    !> - Das flache Neustarten ist nur für PHP-Dateien wirksam, die in `Worker`-Prozessen mit `include/require` verwendet werden, wie [onWorkerStart](/server/events?id=onworkerstart) oder [onReceive](/server/events?id=onreceive).
    - PHP-Dateien, die vor dem Start des Servers `include/require` wurden, können nicht durch flaches Neustarten neu geladen werden.
    - Für die Konfiguration des Servers, also die durch `$serv->set()` übergebenen Parameter, müssen der gesamte Server geschlossen/neu gestartet werden, um sie neu zu laden.
    - Der Server kann einen internen Netzwerkport überwachen und dann Fernsteuerungsbefehle empfangen, um alle `Worker`-Prozesse neu zu starten.
## stop()

Stellt den aktuellen `Worker`-Prozess zum Beenden ein und löst sofort die `onWorkerStop`-Rückruffunktion aus.

```php
Swoole\Server->stop(int $workerId = -1, bool $waitEvent = false): bool
```

  * **Parameter**

    * `int $workerId`

      * Funktion: Legt den `worker id` fest
      * Standardwert: -1, bedeutet den aktuellen Prozess
      * Andere Werte: Keine

    * `bool $waitEvent`

      * Funktion: kontrolliert die Ausstiegspolitik, `false` bedeutet sofortige Ausführung, `true` bedeutet Ausführung warten, bis der Ereigniszyklus leer ist
      * Standardwert: false
      * Andere Werte: true

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist

  * **Hinweis**

    !> -Ein asynchroner IO-Server (z.B. [Swoole\MySQL->query](/learn?id=同步io异步io)) kann nach dem Aufrufen von `stop` und dem Beenden eines Prozesses immer noch Ereignisse warten. Zum Beispiel wurde mit `Swoole\MySQL->query` eine `SQL`-Anfrage gesendet, aber der Rückkehrwert des `MySQL`-Servers wird noch erwartet. Wenn der Prozess zwangsweise beendet wird, geht der Ausführungsresultat der `SQL` verloren.  
    - Wenn `$waitEvent = true` festgelegt ist, verwendet das Underlying-System eine [asynchron sicherer Neustart](/question/use?id=swoole如何正确的重启服务)-Strategie. Es wird zuerst der `Manager`-Prozess informiert, um einen neuen `Worker` zu starten, der neue Anforderungen verarbeitet. Der alte `Worker` wartet auf Ereignisse, bis der Ereigniszyklus leer ist oder die `max_wait_time` überschritten wurde, und verlässt dann den Prozess, um die Sicherheit asynchroner Ereignisse zu gewährleisten.


## shutdown()

Schließt den Dienst.

```php
Swoole\Server->shutdown(): bool
```

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist

  * **Hinweis**

    * Diese Funktion kann innerhalb eines `Worker`-Prozesses verwendet werden.
    * Das Senden von `SIGTERM` an den Hauptkernel kann auch dazu dienen, den Dienst zu schließen.

```shell
kill -15 HauptprozessPID
```


## tick()

Fügt einen `tick`-Timer hinzu, für den man eine benutzerdefinierte Rückruffunktion angeben kann. Diese Funktion ist ein Synonym zu [Swoole\Timer::tick](/timer?id=tick).

```php
Swoole\Server->tick(int $millisecond, callable $callback): void
```

  * **Parameter**

    * `int $millisecond`

      * Funktion: Abstandzeit 【Millisekunden】
      * Standardwert: Keine
      * Andere Werte: Keine

    * `callable $callback`

      * Funktion: Rückruffunktion
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Hinweis**
  
    !> -Nachdem der `Worker`-Prozess beendet wurde, werden alle Timer automatisch zerstört  
    -`tick/after`-Timer können nicht vor dem Starten des `Server->start` verwendet werden  
    -Ab `Swoole5` wurde die Verwendung dieses Synonyms entfernt, bitte verwenden Sie direkt `Swoole\Timer::tick()`

  * **Beispiel**

    * Verwenden Sie es innerhalb von [onReceive](/server/events?id=onreceive)

    ```php
    function onReceive(Swoole\Server $server, int $fd, int $reactorId, mixed $data)
    {
        $server->tick(1000, function () use ($server, $fd) {
            $server->send($fd, "hello world");
        });
    }
    ```

    * Verwenden Sie es innerhalb von [onWorkerStart](/server/events?id=onworkerstart)

    ```php
    function onWorkerStart(Swoole\Server $server, int $workerId)
    {
        if (!$server->taskworker) {
            $server->tick(1000, function ($id) {
              var_dump($id);
            });
        } else {
            //task
            $server->tick(1000);
        }
    }
    ```


## after()

Fügt einen einmaligen Timer hinzu, der nach seiner Ausführung zerstört wird. Diese Funktion ist ein Synonym zu [Swoole\Timer::after](/timer?id=after).

```php
Swoole\Server->after(int $millisecond, callable $callback)
```

  * **Parameter**

    * `int $millisecond`

      * Funktion: Ausführungszeit 【Millisekunden】
      * Standardwert: Keine
      * Andere Werte: Keine
      * Versionsverteilung: Unter `Swoole v4.2.10` darf der Höchstwert nicht überschritten werden `86400000`

    * `callable $callback`

      * Funktion: Rückruffunktion, muss aufrufbar sein, die `callback`-Funktion akzeptiert keine Parameter
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Hinweis**
  
    !> -Das Leben eines Timers ist Prozessebene, wenn der Prozess mit `reload` oder `kill` neu gestartet oder geschlossen wird, werden alle Timer zerstört  
    -Wenn einige Timer wichtige Logik und Daten enthalten, sollten sie in der `onWorkerStop`-Rückruffunktion implementiert werden oder nach [wie man den Dienst richtig neu startet](/question/use?id=swoole如何正确的重启服务) referenziert werden  
    -Ab `Swoole5` wurde die Verwendung dieses Synonyms entfernt, bitte verwenden Sie direkt `Swoole\Timer::after()`


## defer()

Verzögert die Ausführung einer Funktion, ist ein Synonym zu [Swoole\Event::defer](/event?id=defer).

```php
Swoole\Server->defer(Callable $callback): void
```

  * **Parameter**

    * `Callable $callback`

      * Funktion: Rückruffunktion【Pflicht】
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Hinweis**

    !> -Die Funktion wird unterhalb des [EventLoop](/learn?id=什么是eventloop)-Zyklusausführungsende ausgeführt. Der Zweck dieser Funktion ist es, bestimmte PHP-Code zu verzögern, damit der Prozess zuerst andere `IO`-Ereignisse verarbeitet. Zum Beispiel, wenn eine Rückruffunktion CPU-intensiven Berechnungen beinhaltet und nicht sehr dringend ist, kann der Prozess andere Ereignisse verarbeiten, bevor er zu CPU-intensiven Berechnungen übergeht  
    -Die Funktion wird von unten nicht garantiert sofort ausgeführt, wenn es sich um kritische Systemlogik handelt, die sofortige Ausführung erfordert, verwenden Sie den `after`-Timer  
    -Wenn `defer` in der `onWorkerStart`-Rückruffunktion ausgeführt wird, muss darauf gewartet werden, dass ein Ereignis auftritt, bevor der Rückruf stattfindet  
    -Ab `Swoole5` wurde die Verwendung dieses Synonyms entfernt, bitte verwenden Sie direkt `Swoole\Event::defer()`

  * **Beispiel**

```php
function query($server, $db) {
    $server->defer(function() use ($db) {
        $db->close();
    });
}
```


## clearTimer()

Löscht den `tick/after`-Timer, diese Funktion ist ein Synonym zu [Swoole\Timer::clear](/timer?id=clear).

```php
Swoole\Server->clearTimer(int $timerId): bool
```

  * **Parameter**

    * `int $timerId`

      * Funktion: Legt den Timer-ID fest
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist

  * **Hinweis**

    !> -`clearTimer` kann nur für den Löschvorgang von Timern des aktuellen Prozesses verwendet werden     
    -Ab `Swoole5` wurde die Verwendung dieses Synonyms entfernt, bitte verwenden Sie direkt `Swoole\Timer::clear()` 

  * **Beispiel**

```php
$timerId = $server->tick(1000, function ($timerId) use ($server) {
    $server->clearTimer($timerId);//$id ist der Timer-ID
});
```


## close()

Schließt die Verbindung zum Client.

```php
Swoole\Server->close(int $fd, bool $reset = false): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Legt den zu schließenden `fd` (Dateideskriptor) fest
      * Standardwert: Keine
      * Andere Werte: Keine

    * `bool $reset`

      * Funktion: Wenn auf `true` gesetzt, wird die Verbindung zwangsweise geschlossen, und Daten in der Sendequeue werden verworfen
      * Standardwert: false
      * Andere Werte: true

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist

  * **Hinweis**
  !> Wenn der `Server` die Verbindung aktiv schließt, wird ebenfalls das [onClose](/server/events?id=onclose)-Ereignis ausgelöst  

- Verwenden Sie nach dem Schließen keine Cleanup-Logik. Diese sollte in der [onClose](/server/events?id=onclose)-Callback-Funktion behandelt werden  
- Der `fd` des `HTTP\Server` wird im oberen Callback-Methoden `response` erhalten

  * **Beispiel**

```php
$server->on('request', function ($request, $response) use ($server) {
    $server->close($response->fd);
});
```


## send()

Senden Sie Daten an den Client.

```php
Swoole\Server->send(int|string $fd, string $data, int $serverSocket = -1): bool
```

  * **Parameter**

    * `int|string $fd`

      * Funktion: Geben Sie den Dateideskriptor des Clients oder den Pfad des Unix-Sockets an
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `string $data`

      * Funktion: Die zu sendenden Daten, die für das `TCP`-Protokoll maximal 2M betragen darf, können durch das Ausführen von [buffer_output_size](/server/setting?id=buffer_output_size) geändert werden, um die maximal zulässige Paketsize zu ändern
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `int $serverSocket`

      * Funktion:必需的 für das Senden von Daten an den gegenüberliegenden Endpunkt eines [UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php), nicht für TCP-Clients erforderlich
      * Standardwert: -1, bedeutet den derzeit 监听的udp-Port
      * Andere Werte: Keiner

  * **Rückgabewert**

    * Return `true`, wenn die Operation erfolgreich ist, return `false`, wenn sie fehlschlägt

  * **Hinweise**

    !> Der Sendeprozess ist asynchron, der untere Layer überwacht automatisch das Schreiben und sendet die Daten schrittweise an den Client, das heißt, der Client erhält die Daten nicht sofort nach dem Rückkehr des `send`-Befehls.

    * Sicherheit
      * Die `send`-Operation ist atomarisch, wenn mehrere Prozesse gleichzeitig `send` aufrufen, um Daten an dieselbe `TCP`-Verbindung zu senden, kommt es nicht zu Datenverschmelzung

    * Längenbeschränkungen
      * Um Daten über 2M zu senden, können Sie die Daten in eine temporäre Datei schreiben und dann über die `sendfile`-Schnittstelle senden
      * Durch das Einstellen des [buffer_output_size](/server/setting?id=buffer_output_size)-Parameters können Sie die Längenbeschränkung für das Senden ändern
      * Beim Senden von Daten über 8K aktiviert der untere Layer den Arbeitsspeicher der `Worker`-Prozesse, und es muss eine `Mutex->lock`-Operation durchgeführt werden

    * Puffer
      * Wenn der [unixSocket](/learn?id=什么是IPC) Puffer des `Worker`-Prozesses voll ist, wird beim Senden von 8K Daten der temporäre Dateistandard aktiviert
      * Wenn Sie kontinuierlich große Mengen an Daten an denselben Client senden und der Client die Daten nicht schnell genug empfangen kann, wird der `Socket`-SpeicherkBuffer voll, und der Swoole-Unterlayer wird sofort `false` zurückgeben. Wenn `false`, können Sie die Daten auf dem Datenträger speichern und warten, bis der Client die gesendeten Daten empfangen hat, bevor Sie weiter senden

    * [Coroutine-Betriebszeit](/coroutine?id=协程调度)
      * Wenn in der Coroutine-Modus die [send_yield](/server/setting?id=send_yield)-Einstellung aktiviert ist und der `send`-Befehl den Puffer voll findet, wird er automatisch aufgehängt, und wenn ein Teil der Daten vom Client gelesen wurde, wird die Coroutine fortgesetzt und das Senden von Daten fortgesetzt.

    * [UnixSocket](/learn?id=什么是IPC)
      * Beim Auflisten des [UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php)-Ports können Sie Daten an den gegenüberliegenden Endpunkt senden.

      ```php
      $server->on("packet", function (Swoole\Server $server, $data, $addr){
          $server->send($addr['address'], 'SUCCESS', $addr['server_socket']);
      });
      ```


## sendfile()

Senden Sie eine Datei an die TCP-Clientverbindung.

```php
Swoole\Server->sendfile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Geben Sie den Dateideskriptor des Clients an
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `string $filename`

      * Funktion: Der Pfad zur zu sendenden Datei, wenn die Datei nicht existiert, wird `false` zurückgegeben
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `int $offset`

      * Funktion: Geben Sie den Dateieffset an, von dem aus die Daten gesendet werden sollen
      * Standardwert: 0 【Standardwert ist `0`, was bedeutet, dass von der Kopfposition der Datei gesendet wird】
      * Andere Werte: Keiner

    * `int $length`

      * Funktion: Geben Sie die Länge der zu sendenden Daten an
      * Standardwert: Größe der Datei
      * Andere Werte: Keiner

  * **Rückgabewert**

    * Return `true`, wenn die Operation erfolgreich ist, return `false`, wenn sie fehlschlägt

  * **Hinweise**

  !> Diese Funktion und `Server->send` senden beide Daten an den Client, der Unterschied besteht darin, dass die Daten für `sendfile` aus einer spezifischen Datei stammen


## sendto()

Senden Sie ein UDP-Datenpaket an ein beliebiges Client-IP:PORT.

```php
Swoole\Server->sendto(string $ip, int $port, string $data, int $serverSocket = -1): bool
```

  * **Parameter**

    * `string $ip`

      * Funktion: Geben Sie die IP-Adresse des Clients an
      * Standardwert: Keiner
      * Andere Werte: Keiner

      ?> `$ip` ist ein String für IPv4 oder IPv6, wie `192.168.1.102`. Wenn die IP-Adresse ungültig ist, wird ein Fehler zurückgegeben

    * `int $port`

      * Funktion: Geben Sie den Port des Clients an
      * Standardwert: Keiner
      * Andere Werte: Keiner

      ?> `$port` ist ein Netzwerkport von 1 bis 65535, wenn der Port falsch ist, wird das Senden fehlschlagen

    * `string $data`

      * Funktion: Die zu sendenden Dateninhalt, der sowohl Text als auch binäre Inhalte sein können
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `int $serverSocket`

      * Funktion: Geben Sie den Port an, der zum Senden des Datenpakets verwendet werden soll, das entsprechende `server_socket`-Deskriptor 【kann in der [onPacket-Ereignis](/server/events?id=onpacket) `$clientInfo` erhalten】
      * Standardwert: -1, bedeutet den derzeit 监听的udp-Port
      * Andere Werte: Keiner

  * **Rückgabewert**

    * Return `true`, wenn die Operation erfolgreich ist, return `false`, wenn sie fehlschlägt

      ?> Der Server kann gleichzeitig mehrere UDP-Ports überwachen, siehe [mehrere Ports überwachen](/server/port), dieser Parameter kann angeben, welchen Port zum Senden des Datenpakets verwendet werden soll

  * **Hinweise**

  !> Sie müssen einen UDP-Port überwachen, um Daten an eine IPv4-Adresse zu senden  
  Sie müssen einen UDP6-Port überwachen, um Daten an eine IPv6-Adresse zu senden

  * **Beispiel**

```php
//Senden Sie eine "hello world"-字符串 an die IP-Adresse 220.181.57.216 und den Port 9502.
$server->sendto('220.181.57.216', 9502, "hello world");
//Senden Sie ein UDP-Datenpaket an ein IPv6-Server
$server->sendto('2600:3c00::f03c:91ff:fe73:e98f', 9501, "hello world");
```


## sendwait()

Senden Sie synchron Daten an den Client.

```php
Swoole\Server->sendwait(int $fd, string $data): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Geben Sie den Dateideskriptor des Clients an
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `string $data`

      * Funktion: Die zu sendenden Daten
      * Standardwert: Keiner
      * Andere Werte: Keiner

  * **Rückgabewert**

    * Return `true`, wenn die Operation erfolgreich ist, return `false`, wenn sie fehlschlägt

  * **Hinweise**

    * In einigen besonderen Szenarien muss der `Server` kontinuierlich Daten an den Client senden, und die Datensendefunktion `Server->send` ist rein asynchron, was zu einem vollen Senden-Warteschlang im Speicher führen kann, wenn große Mengen von Daten gesendet werden.

    * Die Verwendung von `Server->sendwait` kann dieses Problem lösen, da `Server->sendwait` darauf wartet, dass die Verbindung schreibbar ist. Erst wenn die Daten vollständig gesendet sind, wird zurückgegeben.

  * **Hinweise**

  !> `sendwait` kann derzeit nur im [SWOOLE_BASE](/learn?id=swoole_base)-Modus verwendet werden  
  `sendwait` ist nur für lokale oder Intranet-Kommunikation geeignet, bitte verwenden Sie `sendwait` nicht für externe Verbindungen, und verwenden Sie diese Funktion auch nicht, wenn `enable_coroutine`=>true (Standard开启) ist, da dies andere Coroutines blockieren kann. Nur synchron blockierende Server sollten diese Funktion verwenden.
## sendMessage()

Sende eine Nachricht an ein beliebiges `Worker`-Prozess oder einen [Task-Prozess](/learn?id=taskworker进程). Kann in nicht-Hauptprozessen und Verwaltungsprozessen aufgerufen werden. Der Empfangende Prozess löst das Ereignis `onPipeMessage` aus.

```php
Swoole\Server->sendMessage(mixed $message, int $workerId): bool
```

  * **Parameter**

    * `mixed $message`

      * Funktion: Enthält den Inhalt der gesendeten Nachricht, es gibt keine Längenbeschränkung, aber wenn er über `8K` hinausgeht, wird ein temporärer Speicherfile für Erinnerung verwendet
      * Standardwert: Keine
      * Andere Werte: Keine

    * `int $workerId`

      * Funktion: Die `ID` des Zielprozesses, Referenz zur [worker_id](/server/properties?id=worker_id)
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Hinweise**

    * Wenn `sendMessage()` innerhalb eines `Worker`-Prozesses aufgerufen wird, ist dies eine [Asynchrone IO](/learn?id=同步io异步io)-Operation, die Nachricht wird zuerst in einen Puffer gelagert und kann an der [unixSocket](/learn?id=什么是IPC) gesendet werden, wenn sie geschrieben werden kann
    * Wenn `sendMessage()` innerhalb eines [Task-Prozesses](/learn?id=taskworker进程) aufgerufen wird, ist dies standardmäßig eine [Synchrone IO](/learn?id=同步io异步io)-Operation, aber in einigen Fällen wird sie automatisch in eine Asynchrone IO umgewandelt, siehe [Synchrone IO in Asynchrone IO umwandeln](/learn?id=同步io转换成异步io)
    * Wenn `sendMessage()` innerhalb eines [User-Prozesses](/server/methods?id=addprocess) aufgerufen wird, ist dies wie bei Task standardmäßig synchron und blockierend, siehe [Synchrone IO in Asynchrone IO umwandeln](/learn?id=同步io转换成异步io)

  * **Beachten Sie**


  !> - Wenn `sendMessage()` eine [Asynchrone IO](/learn?id=同步io转换成异步io)-Operation ist und der empfangende Prozess aus irgendeinem Grund keine Daten annimmt, sollten Sie nicht ständig `sendMessage()` aufrufen, da dies zu einer erheblichen Nutzung von Speicherressourcen führen kann. Sie können ein Antwortmechanismus hinzufügen, wenn das andere Ende nicht antwortet, den Aufruf zu pausieren;  

- Unter `MacOS/FreeBSD` wird für über `2K` ein temporärer Dateispeicher verwendet;  

- Um [sendMessage](/server/methods?id=sendMessage) zu verwenden, müssen Sie eine Ereignishandlerschleife für `onPipeMessage` registrieren;  
- Wenn [task_ipc_mode](/server/setting?id=task_ipc_mode) auf 3 festgelegt ist, ist es nicht möglich, Nachrichten an ein bestimmtes task-Prozess mit [sendMessage](/server/methods?id=sendMessage) zu senden.

  * **Beispiel**

```php
$server = new Swoole\Server('0.0.0.0', 9501);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 2,
));
$server->on('pipeMessage', function ($server, $src_worker_id, $data) {
    echo "#{$server->worker_id} message from #$src_worker_id: $data\n";
});
$server->on('task', function ($server, $task_id, $src_worker_id, $data) {
    var_dump($task_id, $src_worker_id, $data);
});
$server->on('finish', function ($server, $task_id, $data) {

});
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    if (trim($data) == 'task') {
        $server->task("async task coming");
    } else {
        $worker_id = 1 - $server->worker_id;
        $server->sendMessage("hello task process", $worker_id);
    }
});

$server->start();
```


## exist()

Prüft, ob die Verbindung mit dem angegebenen `fd` besteht.

```php
Swoole\Server->exist(int $fd): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Dateideskriptor
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Verbindung besteht, gibt `false` zurück, wenn sie nicht besteht

  * **Hinweise**  

    * Diese Schnittstelle basiert auf der Berechnung des gemeinsamen Speichers und beinhaltet keine `IO`-Operationen


## pause()

Hält das Empfangen von Daten an.

```php
Swoole\Server->pause(int $fd): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Angabe des Dateideskriptors
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn sie fehlschlägt

  * **Hinweise**

    * Nach dem Aufrufen dieser Funktion wird die Verbindung aus dem [EventLoop](/learn?id=什么是eventloop) entfernt und es werden keine Clientendaten mehr empfangen.
    * Diese Funktion beeinflusst nicht die Verarbeitung der Sendewarteschlange
    * Kann nur im `SWOOLE_PROCESS`-Modus verwendet werden, nachdem `pause` aufgerufen wurde, können einige Daten bereits im `Worker`-Prozess angekommen sein, daher kann das Ereignis [onReceive](/server/events?id=onreceive) immer noch ausgelöst werden


## resume()

Wird das Empfang von Daten fortgesetzt. Wird paarweise mit der `pause`-Methode verwendet.

```php
Swoole\Server->resume(int $fd): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Angabe des Dateideskriptors
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn sie fehlschlägt

  * **Hinweise**

    * Nach dem Aufrufen dieser Funktion wird die Verbindung erneut dem [EventLoop](/learn?id=什么是eventloop) hinzugefügt und das Empfang von Clientendaten wird fortgesetzt


## getCallback()

Holt die vom Server für einen bestimmten Namen eingestellte Rückruffunktion

```php
Swoole\Server->getCallback(string $event_name): \Closure|string|null|array
```

  * **Parameter**

    * `string $event_name`

      * Funktion: Ereignisname, muss nicht mit `on` beginnen, ist nicht case-sensitive
      * Standardwert: Keine
      * Andere Werte: Referenz zu [Ereignissen](/server/events)

  * **Rückgabewert**

    * Gibt `Closure` / `string` / `array` zurück, wenn die entsprechende Rückruffunktion vorhanden ist
    * Gibt `null` zurück, wenn die entsprechende Rückruffunktion nicht vorhanden ist


## getClientInfo()

Holt Informationen über die Verbindung, auch bekannt als `Swoole\Server->connection_info()`

```php
Swoole\Server->getClientInfo(int $fd, int $reactorId = -1, bool $ignoreError = false): false|array
```

  * **Parameter**

    * `int $fd`

      * Funktion: Angabe des Dateideskriptors
      * Standardwert: Keine
      * Andere Werte: Keine

    * `int $reactorId`

      * Funktion: Die `ID` des [Reactor](/learn?id=reactor线程)-Threads, an dem die Verbindung besteht, hat derzeit keine Funktion, dient nur dazu, die API-Kompatibilität zu gewährleisten
      * Standardwert: -1
      * Andere Werte: Keine

    * `bool $ignoreError`

      * Funktion: Ob Fehler ignoriert werden sollen, wenn auf `true` festgelegt ist, wird auch dann Verbindungsinformationen zurückgegeben, wenn die Verbindung geschlossen ist, `false` bedeutet, dass bei geschlossener Verbindung `false` zurückgegeben wird
      * Standardwert: false
      * Andere Werte: Keine

  * **Hinweise**

    * Clientzertifikate

      * können nur in Prozessen, die durch das [onConnect](/server/events?id=onconnect)-Ereignis ausgelöst wurden, erhalten werden
      *格式为`x509`格式，可使用`openssl_x509_parse`函数获取到证书信息

    * Wenn [dispatch_mode](/server/setting?id=dispatch_mode) auf 1/3 festgelegt ist, wird berücksichtigt, dass diese Datagrammverteilungspolitik für stateless Services verwendet wird, und wenn die Verbindung geschlossen ist, werden die entsprechenden Informationen direkt aus dem Speicher gelöscht, daher können Sie keine Verbindungsinformationen mit `Server->getClientInfo` erhalten.

  * **Rückgabewert**

    * Gibt `false` zurück, wenn der Aufruf fehlschlägt
    * Gibt ein `array` mit Clientinformationen zurück, wenn der Aufruf erfolgreich ist

```php
$fd_info = $server->getClientInfo($fd);
var_dump($fd_info);

array(15) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(25)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(39136)
  ["remote_ip"]=>
  string(9) "127.0.0.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1677322106)
  ["last_time"]=>
  int(1677322106)
  ["last_recv_time"]=>
  float(1677322106.901918)
  ["last_send_time"]=>
  float(0)
  ["last_dispatch_time"]=>
  float(0)
  ["close_errno"]=>
  int(0)
  ["recv_queued_bytes"]=>
  int(78)
  ["send_queued_bytes"]=>
  int(0)
}
```
Parameter | Funktion
---|---
server_port | Der Port, auf dem der Server lauscht
server_fd | Der Server-fd
socket_fd | Der Client-fd
socket_type | Die Art des Sockets
remote_port | Der Client-Port
remote_ip | Die IP-Adresse des Clients
reactor_id | Aus welchem Reactor-Thread stammt das Ereignis
connect_time | Die Zeit, in der der Client zum Server verbunden wurde, in Sekunden, setzt der Master-Prozess fest
last_time | Die letzte Zeit, zu der Daten empfangen wurden, in Sekunden, setzt der Master-Prozess fest
last_recv_time | Die letzte Zeit, zu der Daten empfangen wurden, in Sekunden, setzt der Master-Prozess fest
last_send_time | Die letzte Zeit, zu der Daten gesendet wurden, in Sekunden, setzt der Master-Prozess fest
last_dispatch_time | Die Zeit, zu der der Worker-Prozess Daten empfangen hat
close_errno | Der Fehlercode für den geschlossenen Verbindung, wenn die Verbindung abnormale Weise geschlossen wurde, ist der Wert des close_errno nicht Null, siehe Liste der Linux-Fehlerinformationen
recv_queued_bytes | Die Menge an Daten, die auf等待处理 (Warten auf Verarbeitung)
send_queued_bytes | Die Menge an Daten, die auf等待发送 (Warten auf Senden)
websocket_status | [Optional] Der Zustand der WebSocket-Verbindung, wenn der Server ein Swoole\WebSocket\Server ist, wird diese Information ergänzend hinzugefügt
uid | [Optional] Wenn mit bind ein Benutzer-ID gebunden wurde, wird diese Information ergänzend hinzugefügt
ssl_client_cert | [Optional] Wenn eine SSL-Tunneling-Verschlüsselung verwendet wird und der Client ein Zertifikat festgelegt hat, wird diese Information ergänzend hinzugefügt

## getClientList()

Durchläuft alle aktuellen Client-Verbindungen des `Server`. Die Methode `Server::getClientList` basiert auf gemeinsamer Speicher und es gibt keine `IOWait`, daher ist die Durchquerung sehr schnell. Darüber hinaus gibt `getClientList` alle `TCP`-Verbindungen zurück, nicht nur die `TCP`-Verbindungen des aktuellen `Worker`-Prozesses. Der Alias ist `Swoole\Server->connection_list()`

```php
Swoole\Server->getClientList(int $start_fd = 0, int $pageSize = 10): false|array
```

  * **Parameter**

    * `int $start_fd`

      * Funktion: Legt den Start-fd fest
      * Standardwert: 0
      * Andere Werte: Keine

    * `int $pageSize`

      * Funktion: Wie viele Einträge pro Seite, darf nicht mehr als `100` betragen
      * Standardwert: 10
      * Andere Werte: Keine

  * **Rückgabewert**

    * Wenn der Aufruf erfolgreich ist, wird eine numerische Indizes-Array zurückgegeben, dessen Elemente die erhaltenen `$fd` sind. Das Array wird in aufsteigender Reihenfolge sortiert. Der letzte `$fd` dient als neuer Start-fd für einen erneuten Versuch der Abfrage
    * Wenn der Aufruf fehlschlägt, wird `false` zurückgegeben

  * **Hinweise**

    * Es wird empfohlen, die [Server::$connections](/server/properties?id=connections)-Iteratore zur Durchquerung der Verbindungen zu verwenden
    * `getClientList` kann nur für `TCP`-Clients verwendet werden, `UDP`-Server müssen Client-Informationen selbst speichern
    * Im [SWOOLE_BASE](/learn?id=swoole_base)-Modus kann nur die Verbindung des aktuellen Prozesses abgerufen werden

  * **Beispiel**
  
```php
$start_fd = 0;
while (true) {
  $conn_list = $server->getClientList($start_fd, 10);
  if ($conn_list === false || count($conn_list) === 0) {
      echo "finish\n";
      break;
  }
  $start_fd = end($conn_list);
  var_dump($conn_list);
  foreach ($conn_list as $fd) {
      $server->send($fd, "broadcast");
  }
}
```

## bind()

Binds eine Verbindung an einen vom Benutzer definierte `UID`. Es ist möglich, den [dispatch_mode](/server/setting?id=dispatch_mode) auf `5` zu setzen, um diese Werte für eine `hash`-Fixverteilung zu verwenden. Dies kann sicherstellen, dass alle Verbindungen mit einem bestimmten `UID` immer zum gleichen `Worker`-Prozess verteilt werden.

```php
Swoole\Server->bind(int $fd, int $uid): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Legt den `fd` der Verbindung fest
      * Standardwert: Keine
      * Andere Werte: Keine

    * `int $uid`

      * Funktion: Der zu bindiende `UID`, muss ein nicht-nulles Zahl
      * Standardwert: Keine
      * Andere Werte: `UID` darf nicht größer als `4294967295` und nicht kleiner als `-2147483648` sein

  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn sie fehlschlägt

  * **Hinweise**

    * Mit `$serv->getClientInfo($fd)` kann der gebundene `UID` der Verbindung eingesehen werden
    * Unter dem Standard-Setting des [dispatch_mode](/server/setting?id=dispatch_mode)=2 verteilt der `Server` die Verbindungsdaten auf verschiedene `Worker`-Prozesse basierend auf dem `socket fd`. Da das `fd` instabil ist, ändert sich das `fd`, wenn ein Client die Verbindung trennt und neu verbindet. In diesem Fall werden die Daten dieses Clients an einen anderen `Worker` verteilt. Nachdem die Verbindung gebunden wurde, werden neue Datenpakete nach dem `UID` verteilt.
    * Zeitabläufe

      * Nach der Verbindung eines Clients zum Server und dem Senden mehrerer Pakete kann es zu Zeitabläufen kommen. Bei der `bind`-Operation sind mögliche nachfolgende Pakete bereits `dispatch`, und diese Pakete werden immer noch nach dem `fd` gemoduliert an den aktuellen Prozess verteilt. Nur neue Pakete, die nach der `bind`-Operation empfangen werden, werden nach dem `UID` gemoduliert verteilt.
      * Daher muss das Netzwerkprotokoll, wenn die `bind`-Mechanismus verwendet werden soll, einen Handshake-Schritt enthalten. Nach erfolgreicher Verbindung des Clients sendet der Client zuerst einen Handshake-Request, danach sollte der Client keine Pakete mehr senden. Nachdem der Server die `bind`-Operation abgeschlossen hat und geantwortet hat, sendet der Client neue Requests.

    * Umbinden

      * In einigen Fällen muss das Geschäftslogik die Verbindung des Benutzers zum Umbinden des `UID` erzwingen. In diesem Fall kann die Verbindung unterbrochen werden, eine neue `TCP`-Verbindung eingeleitet und der Handshake durchgeführt werden, um an einem neuen `UID` gebunden zu werden.

    * Bindung eines negativen `UID`

      * Wenn ein gebundener `UID` negativ ist, wird er von der unteren Ebene in einen `32-Bit-Unsignierten Integer` umgewandelt, und die PHP-Schicht muss ihn in einen `32-Bit-Signierten Integer` umwandeln, wobei dies mit dem folgenden Code erreicht werden kann:
      
  ```php
  $uid = -10;
  $server->bind($fd, $uid);
  $bindUid = $server->connection_info($fd)['uid'];
  $bindUid = $bindUid >> 31 ? (~($bindUid - 1) & 0xFFFFFFFF) * -1 : $bindUid;
  var_dump($bindUid === $uid);
  ```

  * **Hinweise**


!> - Nur wirksam, wenn `dispatch_mode=5` festgelegt ist  

- Wenn kein `UID` gebunden ist, wird standardmäßig nach dem `fd` gemoduliert verteilt  
- Eine Verbindung kann nur einmal gebunden werden, wenn bereits ein `UID` gebunden ist, wird eine weitere `bind`-Anfrage `false` zurückgeben

  * **Beispiel**

```php
$serv = new Swoole\Server('0.0.0.0', 9501);

$serv->fdlist = [];

$serv->set([
    'worker_num' => 4,
    'dispatch_mode' => 5,   //uid dispatch
]);

$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "{$fd} connect, worker:" . $serv->worker_id . PHP_EOL;
});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    $conn = $serv->connection_info($fd);
    print_r($conn);
    echo "worker_id: " . $serv->worker_id . PHP_EOL;
    if (empty($conn['uid'])) {
        $uid = $fd + 1;
        if ($serv->bind($fd, $uid)) {
            $serv->send($fd, "bind {$uid} success");
        }
    } else {
        if (!isset($serv->fdlist[$fd])) {
            $serv->fdlist[$fd] = $conn['uid'];
        }
        print_r($serv->fdlist);
        foreach ($serv->fdlist as $_fd => $uid) {
            $serv->send($_fd, "{$fd} say:" . $data);
        }
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "{$fd} Close". PHP_EOL;
    unset($serv->fdlist[$fd]);
});

$serv->start();
```
## stats()

Erhalten Sie Informationen über die Anzahl der aktiven TCP-Verbindungen, den Startzeitpunkt usw. des aktuellen `Servers`, sowie die Gesamtanzahl von `accept/close` (Verbindung aufnehmen/Schließen).

```php
Swoole\Server->stats(): array
```

  * **Beispiel**

```php
array(25) {
  ["start_time"]=>
  int(1677310656)
  ["connection_num"]=>
  int(1)
  ["abort_count"]=>
  int(0)
  ["accept_count"]=>
  int(1)
  ["close_count"]=>
  int(0)
  ["worker_num"]=>
  int(2)
  ["task_worker_num"]=>
  int(4)
  ["user_worker_num"]=>
  int(0)
  ["idle_worker_num"]=>
  int(1)
  ["dispatch_count"]=>
  int(1)
  ["request_count"]=>
  int(0)
  ["response_count"]=>
  int(1)
  ["total_recv_bytes"]=>
  int(78)
  ["total_send_bytes"]=>
  int(165)
  ["pipe_packet_msg_id"]=>
  int(3)
  ["session_round"]=>
  int(1)
  ["min_fd"]=>
  int(4)
  ["max_fd"]=>
  int(25)
  ["worker_request_count"]=>
  int(0)
  ["worker_response_count"]=>
  int(1)
  ["worker_dispatch_count"]=>
  int(1)
  ["task_idle_worker_num"]=>
  int(4)
  ["tasking_num"]=>
  int(0)
  ["coroutine_num"]=>
  int(1)
  ["coroutine_peek_num"]=>
  int(1)
  ["task_queue_num"]=>
  int(1)
  ["task_queue_bytes"]=>
  int(1)
}
```


Parameter | Funktion
---|---
start_time | Die Zeit, zu der der Server gestartet wurde
connection_num | Die Anzahl der aktuellen Verbindungen
abort_count | Die Anzahl der abgelehnten Verbindungen
accept_count | Die Anzahl der angenommenen Verbindungen
close_count | Die Anzahl der geschlossenen Verbindungen
worker_num  | Die Anzahl der gestarteten Worker-Prozesse
task_worker_num  | Die Anzahl der gestarteten task_worker-Prozesse【`v4.5.7` Available】
user_worker_num  | Die Anzahl der gestarteten task worker-Prozesse
idle_worker_num | Die Anzahl der freien Worker-Prozesse
dispatch_count | Die Anzahl der Pakete, die der Server an Worker sendet【`v4.5.7` Available, nur in [SWOOLE_PROCESS](/learn?id=swoole_process) Modus wirksam】
request_count | Die Anzahl der erhaltenen Anforderungen des Servers【Nur für onReceive, onMessage, onRequest, onPacket四种 Datenanforderungen wird request_count berechnet】
response_count | Die Anzahl der zurückgegebenen Antworten des Servers
total_recv_bytes| Die Gesamtanzahl der empfangenen Daten
total_send_bytes | Die Gesamtanzahl der gesendeten Daten
pipe_packet_msg_id | Die Prozess-zu-Prozess-Kommunikations-ID
session_round | Die Anfangssession-ID
min_fd | Die kleinste Verbindungfd
max_fd | Die größte Verbindungfd
worker_request_count | Die Anzahl der erhaltenen Anforderungen der aktuellen Worker-Prozesse【Wenn worker_request_count über max_request hinausgeht, wird der Arbeitsprozess beendet】
worker_response_count | Die Anzahl der Antworten der aktuellen Worker-Prozesse
worker_dispatch_count | Die Anzahl der Aufgaben, die der Master-Prozess an den aktuellen Worker-Prozess übergibt, wird beim Dispatchen durch den [master-Prozess](/learn?id=reactor-threads) erhöht
task_idle_worker_num | Die Anzahl der freien task-Prozesse
tasking_num | Die Anzahl der arbeitenden task-Prozesse
coroutine_num | Die Anzahl der aktuellen Coroutinen【für Coroutine】, um mehr Informationen zu erhalten, siehe [dieser Abschnitt](/coroutine/gdb)
coroutine_peek_num | Die Gesamtzahl der Coroutinen
task_queue_num | Die Anzahl der Tasks in der Nachrichtenschlange【für Task】
task_queue_bytes | Die Größe der Erinnerungsschlange in Byte【für Task】


## task()

Stellen Sie eine asynchrone Aufgabe in den `task_worker` Pool ein. Diese Funktion ist nicht blockierend und wird sofort zurückgegeben, nachdem sie ausgeführt wurde. Der `Worker` Prozess kann weiterhin neue Anforderungen verarbeiten. Um die `Task`-Funktion zu verwenden, müssen Sie zuerst `task_worker_num` festlegen und die [onTask](/server/events?id=ontask) und [onFinish](/server/events?id=onfinish) Ereignis-Rückruffunktionen des `Servers` einrichten.

```php
Swoole\Server->task(mixed $data, int $dstWorkerId = -1, callable $finishCallback): int
```

  * **Parameter**

    * `mixed $data`

      * Funktion: Die zu liefernde Aufgabe-Daten, muss ein serialisierbarer PHP-Variabel sein
      * Standardwert: Keine
      * Andere Werte: Keine

    * `int $dstWorkerId`

      * Funktion: Sie können angeben, zu welchem [Task-Prozess](/learn?id=taskworker-prozess) die Aufgabe geliefert werden soll, geben Sie einfach die `ID` des Task-Prozesses ein, der Bereich ist `[0, $server->setting['task_worker_num']-1]`
      * Standardwert: `-1`【Standardwert ist `-1`, was bedeutet, dass die Aufgabe zufällig geliefert wird, der Boden wählt automatisch einen freien [Task-Prozess](/learn?id=taskworker-prozess)】
      * Andere Werte: `[0, $server->setting['task_worker_num']-1]`

    * `callable $finishCallback`

      * Funktion: Der `finish` Rückruffunktions, wenn für die Aufgabe ein Rückruffunktions festgelegt ist, wird das Ergebnis der Aufgabe direkt durch den angegebenen Rückruffunktions ausgegeben, ohne dass der [onFinish](/server/events?id=onfinish) Rückruf der `Server` ausgeführt wird, nur wenn die Aufgabe im `Worker` Prozess geliefert wird, wird dies ausgelöst
      * Standardwert: `null`
      * Andere Werte: Keine

  * **Rückgabewert**

    * Bei erfolgreicher Ausführung wird ein ganzzahliger Wert `$task_id` zurückgegeben, der die `ID` der Aufgabe darstellt. Wenn ein `finish` Rückruffunktions festgelegt ist, wird der `$task_id` Parameter im [onFinish](/server/events?id=onfinish) Rückruf mitgeliefert
    * Bei Misserfolg wird `false` zurückgegeben, `$task_id` könnte `0` sein, daher muss mit `===` geprüft werden, ob es fehlgeschlagen ist

  * **Hinweise**

    * Diese Funktion wird verwendet, um langsame Aufgaben asynchron auszuführen, zum Beispiel um eine Chatroom-Server zu nutzen, um Sendebroadcasts durchzuführen. Wenn die Aufgabe abgeschlossen ist, ruft man im [task-Prozess](/learn?id=taskworker-prozess) `$serv->finish("finish")` aus, um dem `worker` Prozess mitzuteilen, dass die Aufgabe abgeschlossen ist. Natürlich ist `Swoole\Server->finish` optional.
    * `task` nutzt unter dem Deckmantel [unixSocket](/learn?id=was-ist-ipc) Kommunikation, ist voll in RAM und hat keine `IO`-Verbrauch. Die Lesegeschwindigkeit eines einzelnen Prozesses kann bis zu `1 Million/s` erreichen, verschiedene Prozesse verwenden unterschiedliche `unixSocket` Kommunikation, um die Nutzung mehrerer Kerne zu maximieren.
    * Wenn kein Ziel-[Task-Prozess](/learn?id=taskworker-prozess) angegeben ist, wird bei der Aufruf der `task` Methode der Zustand der [Task-Prozesse](/learn?id=taskworker-prozess) überprüft, der Boden wird nur Aufgaben an freie [Task-Prozesse](/learn?id=taskworker-prozess) liefern. Wenn alle [Task-Prozesse](/learn?id=taskworker-prozess) beschäftigt sind, wird der Boden的任务 in alle Prozesse nacheinander liefern. Sie können die Anzahl der gerade wartenden Aufgaben mit der [server->stats](/server/methods?id=stats) Methode abrufen.
    * Der dritte Parameter kann direkt die [onFinish](/server/events?id=onfinish) Funktion festlegen, wenn für die Aufgabe ein Rückruffunktions festgelegt ist, wird das Ergebnis der Aufgabe direkt durch den angegebenen Rückruffunktions ausgegeben, ohne dass der [onFinish](/server/events?id=onfinish) Rückruf der `Server` ausgeführt wird, nur wenn die Aufgabe im `Worker` Prozess geliefert wird, wird dies ausgelöst

    ```php
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    ```

    * `$task_id` ist ein Integer von `0-42 Milliarden`, einzigartig innerhalb des aktuellen Prozesses
    * Die `task` Funktion ist standardmäßig nicht aktiviert, sie muss manuell mit `task_worker_num` gestartet werden
    * Die Anzahl der `TaskWorker` kann im [Server->set()](/server/methods?id=set) Parameter angepasst werden, zum Beispiel `task_worker_num => 64`, bedeutet das Starten von `64` Prozessen zur Empfang von asynchronen Aufgaben

  * **Konfigurationsparameter**

    * Die `Server->task/taskwait/finish` drei Methoden verwenden temporäre Dateien zum Speichern, wenn die eingehenden `$data` Daten über `8K` betragen. Wenn der Inhalt der temporären Datei über
    [server->package_max_length](/server/setting?id=package_max_length) hinausgeht, wirft der Boden eine Warnung aus. Diese Warnung beeinträchtigt nicht das Versenden von Daten, große `Tasks` können jedoch Performanceprobleme aufweisen.
    
    ```shell
    WARN: task package is too big.
    ```

  * **Einseitige Aufgaben**

    * Von `Master`, `Manager`, `UserProcess` Prozessen gelieferte Aufgaben sind einseitig und können im `TaskWorker` Prozess nicht mit `return` oder `Server->finish()` verwendet werden, um Ergebnisse zurückzugeben.

  * **Bitte beachten Sie**
 ```php
  !> -`task`Methode kann nicht in der [Task-Prozess](/lernen?id=taskworkerprozess) aufgerufen werden  

-Der Einsatz von `task` erfordert, dass für den `Server` [onTask](/server/ereignisse?id=ontask) und [onFinish](/server/ereignisse?id=onfinish) Rückruffunktionen festgelegt sind, sonst wird die `Server->start` Methode fehlschlagen  

-Die Anzahl der `task` Operationen muss kleiner als die Verarbeitungsgeschwindigkeit von [onTask](/server/ereignisse?id=ontask) sein. Wenn die Übertragungskapazität die Verarbeitungsfähigkeit übertrifft, wird die `task` Daten vollgestopft in den Cache, was zu einem Blockieren des `Worker` Prozesses führt. Der `Worker` Prozess kann keine neuen Anforderungen empfangen  
-Verwenden Sie [addProcess](/server/methode?id=addProcess) zum Hinzufügen von Benutzungsprozessen, die `task` können einseitig Aufgaben an die [Worker/Task-Prozesse](/lernen?id=worker-task-prozess) übergeben, aber keine Ergebnismeldungen zurückgeben. Verwenden Sie die [sendMessage](/server/methoden?id=sendMessage)-Schnittstelle zur Kommunikation mit dem `Worker/Task-Prozess`

  * **Beispiel**

```php
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 4,
));

$server->on('Receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    echo "Empfangen von Daten" . $data . "\n";
    $data    = trim($data);
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    $task_id = $server->task($data, 0);
    $server->send($fd, "Aufgabe verteilt, Task-ID ist $task_id\n");
});

$server->on('Task', function (Swoole\Server $server, $task_id, $reactor_id, $data) {
    echo "Task-Prozess erhält Daten";
    echo "#{$server->worker_id}\tonTask: [PID={$server->worker_pid}]: task_id=$task_id, data_len=" . strlen($data) . "." . PHP_EOL;
    $server->finish($data);
});

$server->on('Finish', function (Swoole\Server $server, $task_id, $data) {
    echo "Task#$task_id beendet, data_len=" . strlen($data) . PHP_EOL;
});

$server->on('workerStart', function ($server, $worker_id) {
    global $argv;
    if ($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]}: task_worker");
    } else {
        swoole_set_process_name("php {$argv[0]}: worker");
    }
});

$server->start();
```


## taskwait()

`taskwait` hat die gleiche Funktion wie die `task` Methode und wird zum Einreichen einer asynchronen Aufgabe an den [Task-Prozess](/lernen?id=taskworkerprozess) verwendet. Im Gegensatz zu `task` wartet `taskwait` synchron bis die Aufgabe abgeschlossen ist oder eine Zeitüberschreitung eintritt. `$result` ist das Ergebnis der Aufgabeausführung, das von der `$server->finish` Funktion ausgegeben wird. Wenn diese Aufgabe eine Zeitüberschreitung erlebt, wird hier `false` zurückgegeben.

```php
Swoole\Server->taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1): mixed
```

  * **Parameter**

    * `mixed $data`

      * Funktion: Die zu übertragende Aufgabe-Daten können jedes Typ sein, für nicht-String-Typen wird automatisch eine Serialisierung durchgeführt
      * Standardwert: Keine
      * Andere Werte: Keine

    * `float $timeout`

      * Funktion: Die Timeoutzeit in Sekunden, als Fließkommazahl, unterstützt bis zu `1ms` Granularität, wenn der [Task-Prozess](/lernen?id=taskworkerprozess) innerhalb der festgelegten Zeit keine Daten zurückgibt, wird `taskwait` `false` zurückgeben und keine weiteren Aufgabeergebnisse verarbeitet
      * Standardwert: 0,5
      * Andere Werte: Keine

    * `int $dstWorkerId`

      * Funktion: Geben Sie den [Task-Prozess](/lernen?id=taskworkerprozess) an, dem die Aufgabe übergeben werden soll, geben Sie einfach die `ID` des Task-Prozesses ein, der Bereich liegt zwischen `[0, $server->setting['task_worker_num']-1]`
      * Standardwert: -1【Standardwert ist `-1`, was bedeutet, dass es zufällig verteilt wird, und der底层 automatisch einen freien [Task-Prozess](/lernen?id=taskworkerprozess) auswählt】
      * Andere Werte: `[0, $server->setting['task_worker_num']-1]`

  * **Rückgabewert**

      * Rückkehr von `false` bedeutet, dass die Übertragung fehlgeschlagen ist
      * Wenn in der `onTask` Ereignis eine `finish` Methode oder ein `return` ausgeführt wurde, dann wird `taskwait` das Ergebnis der von `onTask` übergebenen Aufgabe zurückgeben.

  * **Hinweise**

    * **Coroutine-Modus**

      * Ab Version `4.0.4` unterstützt die `taskwait` Methode die [Coroutine-Planung](/coroutine?id=coroutine-scheduling), und wenn `Server->taskwait()` in einem Coroutine aufgerufen wird, wird automatisch eine [Coroutine-Planung](/coroutine?id=coroutine-scheduling) durchgeführt, ohne Blockierung zu warten.
      * Mit Hilfe des [Coroutine-Planungs](/coroutine?id=coroutine-scheduling)-Controllers kann `taskwait` konsekutive Aufrufe durchführen.
      * In der `onTask` Ereignis darf es nur einen `return` oder einen `Server->finish` geben, sonst wird nach dem Ausführen von mehreren `return` oder `Server->finish` eine Warnung mit dem Hinweis task[1] hat expired ausgegeben.

    * **Synchroner Modus**

      * Im synchronen Blocking-Modus muss `taskwait` über [UnixSocket](/lernen?id=was-ist-ipc) Kommunikation und gemeinsame Erinnerungen verfügen, um Daten an den `Worker` Prozess zurückzugeben, dieser Prozess ist synchron blockierend.

    * **Besonderheiten**

      * Wenn es in der [onTask](/server/ereignisse?id=ontask) Ereignis keine [Synchron-IO](/lernen?id=synchronio-asynchronio)-Operationen gibt, besteht der untere Prozessswitch-Aufwand nur aus `2` Mal, es wird kein `IO` Warten verursacht, daher kann in diesem Fall `taskwait` als nicht blockierend betrachtet werden. Tatsächliche Tests zeigen, dass bei der Ausführung von nur Lesevorgängen und Schreibvorgängen in einem PHP-Array und `100.000` Aufrufen von `taskwait` die Gesamtzeit nur `1` Sekunde beträgt, mit einer durchschnittlichen Zeit von `10` Mikrosekunden pro Aufruf

  * **Hinweise**


  !> -`Swoole\Server::finish`, verwenden Sie nicht `taskwait`  
-Die `taskwait` Methode kann nicht in der [Task-Prozess](/lernen?id=taskworkerprozess) aufgerufen werden


## taskWaitMulti()

Führen Sie mehrere `task` asynchrone Aufgaben gleichzeitig aus, diese Methode unterstützt keine [Coroutine-Planung](/coroutine?id=coroutine-scheduling), was dazu führen kann, dass andere Coroutinen beginnen, unter Coroutine-Umwelt sollten Sie die unten genannten `taskCo` verwenden.

```php
Swoole\Server->taskWaitMulti(array $tasks, float $timeout = 0.5): false|array
```

  * **Parameter**

    * `array $tasks`

      * Funktion: Muss ein numerischer Index-Array sein, assoziative Index-Arrays werden nicht unterstützt, der底层 wird die `$tasks` durchlaufen und die Aufgaben einzeln an die [Task-Prozesse](/lernen?id=taskworkerprozess) übergeben
      * Standardwert: Keine
      * Andere Werte: Keine

    * `float $timeout`

      * Funktion: Als Fließkommazahl in Sekunden
      * Standardwert: 0,5 Sekunden
      * Andere Werte: Keine

  * **Rückgabewert**

    * Aufgabe abgeschlossen oder Timeout, zurückgeben Sie ein Ergebnisarray. Die Reihenfolge der Ergebnisse in dem Ergebnisarray entspricht dem `$tasks`, zum Beispiel: `$tasks[2]` entspricht dem Ergebnis von `$result[2]`
    * Eine Aufgabe, die eine Zeitüberschreitung erlebt, beeinflusst andere Aufgaben nicht, das zurückgegebene Ergebnisdata wird nicht enthalten, wenn eine Aufgabe eine Zeitüberschreitung erlebt hat

  * **Hinweise**

  !> -Die maximale Anzahl der gleichzeitigen Aufgaben darf nicht mehr als `1024` betragen

  * **Beispiel**

```php
$tasks[] = mt_rand(1000, 9999); // Aufgabe 1
$tasks[] = mt_rand(1000, 9999); // Aufgabe 2
$tasks[] = mt_rand(1000, 9999); // Aufgabe 3
var_dump($tasks);

// Warten Sie auf die Rückkehr aller Task-Ergebnisse, die Timeoutzeit beträgt 10 Sekunden
$results = $server->taskWaitMulti($tasks, 10.0);

if (!isset($results[0])) {
    echo "Aufgabe 1 ist über die Zeit hinausgegangen\n";
}
if (isset($results[1])) {
    echo "Das Ausführungsergebnis von Aufgabe 2 ist {$results[1]}\n";
}
if (isset($results[2])) {
    echo "Das Ausführungsergebnis von Aufgabe 3 ist {$results[2]}\n";
}
```
```
## taskCo()

Gleichzeitig `Task` ausführen und [Coroutine- scheduling](/coroutine?id=协程调度) durchführen, um die `taskWaitMulti`-Funktion im Kontext der Coroutinen zu unterstützen.

```php
Swoole\Server->taskCo(array $tasks, float $timeout = 0.5): false|array
```

* `$tasks`: Eine Liste von Aufgaben, die zwangsläufig ein Array sein muss. Im Hintergrund wird das Array durchlaufen, und jeder Element wird als `task` an den `Task` -Prozesspool übermittelt
* `$timeout`: Die Timeoutzeit, die standardmäßig bei `0,5` Sekunden liegt. Wenn die Aufgaben innerhalb der festgelegten Zeit nicht alle abgeschlossen sind, wird die Operation sofort abgebrochen und das Ergebnis zurückgegeben
* Wenn die Aufgabe abgeschlossen ist oder Timeout auftritt, wird ein Ergebnisarray zurückgegeben. Die Reihenfolge der Ergebnisse in dem Ergebnisarray entspricht der in `$tasks`, zum Beispiel: Das Ergebnis für `$tasks[2]` ist `$result[2]`
* Wenn eine Aufgabe fehlschlägt oder timeoutet, ist der entsprechende Eintrag im Ergebnisarray `false`, zum Beispiel: Wenn `$tasks[2]` fehlschlägt, ist der Wert von `$result[2]` `false`

!> Die maximale Anzahl von gleichzeitig laufenden Aufgaben darf nicht mehr als `1024` betragen  

  * **Schedulingprozess**

    * Jede Aufgabe in der `$tasks` -Liste wird zufällig an einen `Task` -Arbeitsprozess übermittelt. Nach der Übermittlung wird der aktuelle Coroutine mit `yield` freigelassen und ein `$timeout` -Sekundentimer eingerichtet
    * In `onFinish` werden die entsprechenden Aufgabenergebnisse gesammelt und in das Ergebnisarray保存. Es wird entschieden, ob alle Aufgaben Ergebnisse zurückgegeben haben. Wenn nicht, wird weiter gewartet. Wenn ja, wird der entsprechende Coroutine mit `resume` fortgesetzt und der Timeout-Timer gelöscht
    * Wenn die Aufgaben innerhalb der festgelegten Zeit nicht alle abgeschlossen sind, wird der Timer zuerst ausgelöst, und der Hintergrund löscht den Wartestatus. Die Ergebnisse der nicht abgeschlossenen Aufgaben werden als `false` markiert und der entsprechende Coroutine wird sofort fortgesetzt

  * **Beispiel**

```php
$server = new Swoole\Http\Server("127.0.0.1", 9502, SWOOLE_BASE);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $serv, $task_id, $worker_id, $data) {
    echo "#{$serv->worker_id}\tonTask: worker_id={$worker_id}, task_id=$task_id\n";
    if ($serv->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "hello world";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('Test End, Result: ' . var_export($result, true));
});

$server->start();
```


## finish()

Wird verwendet, um im [Task-Prozess](/learn?id=taskworker进程) den [Worker-Prozess](/server/events?id=onfinish) zu informieren, dass die übermittelte Aufgabe abgeschlossen ist. Diese Funktion kann Ergebnisse an den [Worker-Prozess](/server/events?id=onfinish) übergeben.

```php
Swoole\Server->finish(mixed $data): bool
```

  * **Parameter**

    * `mixed $data`

      * Funktion: Das Ergebnis der Aufgabeverarbeitung
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückgabewert**

    * TRUE, wenn die Operation erfolgreich ist, FALSE, wenn sie fehlschlägt

  * **Hinweise**

    * Die `finish` -Methode kann mehrmals nacheinander aufgerufen werden, und der [Worker-Prozess](/server/events?id=onfinish) löst wiederholt das [onFinish](/server/events?id=onfinish) -Ereignis aus
    * Wenn die `finish` -Methode in der [onTask](/server/events?id=ontask) -Rückruffunktion aufgerufen wurde, wird das zurückgelegte Daten immer noch das [onFinish](/server/events?id=onfinish) -Ereignis auslösen
    * `Server->finish` ist optional. Wenn der [Worker-Prozess](/server/events?id=onfinish) sich nicht um das Ergebnis der Aufgabeverarbeitung kümmert, ist diese Funktion nicht erforderlich
    * Im [onTask](/server/events?id=ontask) -Rückruffunktions `return` ein String, ist gleichbedeutend mit dem Aufrufen von `finish`

  * **Hinweise**

  !> Um die `Server->finish` -Funktion zu verwenden, muss für den `Server` ein [onFinish](/server/events?id=onfinish) -Rückruffunktions festgelegt werden. Diese Funktion kann nur im [onTask](/server/events?id=ontask) -Rückruf der [Task-Prozess](/learn?id=taskworker进程) verwendet werden


## heartbeat()

Im Gegensatz zur passiven Überprüfung durch [heartbeat_check_interval](/server/setting?id=heartbeat_check_interval) überprüft diese Methode aktiv alle Verbindungen auf dem Server und findet diejenigen, die bereits über die vereinbarten Zeit hinausgegangen sind. Wenn `if_close_connection` angegeben ist, werden die überholten Verbindungen automatisch geschlossen. Wenn nicht angegeben, wird nur ein Array mit den `fd` der Verbindungen zurückgegeben.

```php
Swoole\Server->heartbeat(bool $ifCloseConnection = true): bool|array
```

  * **Parameter**

    * `bool $ifCloseConnection`

      * Funktion: Ob überholte Verbindungen geschlossen werden sollen
      * Standardwert: true
      * Andere Werte: false

  * **Rückgabewert**

    * Erfolgreich zurückgegeben wird ein kontinuierliches Array, dessen Elemente die geschlossenen `$fd` sind
    * Fehlgeschlagen zurückgegeben wird `false`

  * **Beispiel**

```php
$closeFdArrary = $server->heartbeat();
```


## getLastError()

Der letzte Fehlercode der Operation wird abgerufen. Im Geschäftslogikcode kann je nach Fehlercodetyp unterschiedliche Logiken ausgeführt werden.

```php
Swoole\Server->getLastError(): int
```

  * **Rückgabewert**


Fehlercode | Erklärung
---|---
1001 | Die Verbindung wurde bereits von der `Server` -Seite geschlossen. Dieser Fehler tritt in der Regel auf, wenn im Code bereits `$server->close()` aufgerufen wurde, um eine Verbindung zu schließen, und dann `$server->send()` darauf verwendet wird, Daten an diese Verbindung zu senden
1002 | Die Verbindung wurde von der `Client` -Seite geschlossen, der `Socket` ist geschlossen und es ist nicht möglich, Daten an den anderen Endpunkt zu senden
1003 | `close` wird gerade ausgeführt, im [onClose](/server/events?id=onclose) -Rückruffunktions kann `$server->send()` nicht verwendet werden
1004 | Die Verbindung wurde geschlossen
1005 | Die Verbindung existiert nicht, möglicherweise ist das übergebene `$fd` falsch
1007 | Überholte Daten wurden empfangen, nachdem die `TCP` -Verbindung geschlossen wurde, können einige Daten im [unixSocket](/learn?id=什么是IPC) -Pufferbereich verbleiben, und diese Daten werden verworfen
1008 | Der Senden-Puffer ist voll und es ist nicht möglich, die `send` -Operation durchzuführen. Dieser Fehler tritt auf, wenn der Empfänger die Daten nicht rechtzeitig empfangen kann, was dazu führt, dass der Senden-Puffer voll ist
1202 | Die gesendeten Daten übersteigen das [server->buffer_output_size](/server/setting?id=buffer_output_size) -Einstellung
9007 | Nur bei Verwendung von [dispatch_mode](/server/setting?id=dispatch_mode)=3 auftritt, bedeutet dies, dass derzeit keine verfügbaren Prozesse vorhanden sind, und es ist möglich, die Anzahl der `worker_num` -Prozesse zu erhöhen
## protect()

Legt die Clientverbindung in einen geschützten Zustand fest, sodass sie nicht von der Heartbeat-Thread abgeschnitten wird.

```php
Swoole\Server->protect(int $fd, bool $is_protected = true): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Bestimmt die Clientverbindung `fd`
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `bool $is_protected`

      * Funktion: Status, der festgelegt wird
      * Standardwert: true 【bedeutet geschützt】
      * Andere Werte: false 【bedeutet nicht geschützt】

  * **Rückgabewert**

    * Gibt true zurück, wenn die Operation erfolgreich ist, gibt false zurück, wenn sie fehlschlägt


## confirm()

Bestätigt die Verbindung, wird in Kombination mit [enable_delay_receive](/server/setting?id=enable_delay_receive) verwendet. Nachdem ein Client eine Verbindung eingerichtet hat, wird nicht auf das Lesbare-Ereignis gewartet, sondern nur der [onConnect](/server/events?id=onconnect)-Ereignishandler ausgelöst. In dem [onConnect](/server/events?id=onconnect)-Rückruf wird die Verbindung mit `confirm` bestätigt, erst dann wartet der Server auf Lesbare-Ereignisse und empfängt Daten aus der Clientverbindung.

!> Swoole-Version >= `v4.5.0` verfügbar

```php
Swoole\Server->confirm(int $fd): bool
```

  * **Parameter**

    * `int $fd`

      * Funktion: Einzigartiger Identifier für die Verbindung
      * Standardwert: Keiner
      * Andere Werte: Keiner

  * **Rückgabewert**
  
    * True zurückgeben, wenn die Bestätigung erfolgreich ist
    * False zurückgeben, wenn die Verbindung, die dem `$fd` entspricht, nicht existiert, geschlossen ist oder bereits im Wartungsmodus ist, Bestätigung fehlgeschlagen

  * **Nutzung**
  
    Diese Methode wird in der Regel zum Schutz des Servers verwendet, um Überlastungsangriffe zu vermeiden. Wenn eine Clientverbindung empfangen wird, wird die [onConnect](/server/events?id=onconnect)-Funktion ausgelöst, und es kann überprüft werden, ob die Quelle `IP` zulässig ist, Daten an den Server zu senden.

  * **Beispiel**
    
```php
// Erstellen eines Server-Objekts, das auf dem Port 127.0.0.1:9501 lauscht
$serv = new Swoole\Server("127.0.0.1", 9501); 
$serv->set([
    'enable_delay_receive' => true,
]);

// Warten auf das Ereignis der Verbindungseinleitung
$serv->on('Connect', function ($serv, $fd) {  
    // Hier wird dieser $fd überprüft, wenn alles in Ordnung ist, wird confirm aufgerufen
    $serv->confirm($fd);
});

// Warten auf das Ereignis des Datenerhaltens
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "Server: ".$data);
});

// Warten auf das Ereignis der Verbindungsschließung
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Starten des Servers
$serv->start(); 
```


## getWorkerId()

Erhalten Sie die `Worker`-Prozess-ID des aktuellen Prozesses (nicht der Prozess-PID), die identisch mit der `$workerId` bei [onWorkerStart](/server/events?id=onworkerstart) ist

```php
Swoole\Server->getWorkerId(): int|false
```

!> Swoole-Version >= `v4.5.0RC1` verfügbar


## getWorkerPid()

Erhalten Sie die PID des angegebenen `Worker`-Prozesses

```php
Swoole\Server->getWorkerPid(int $worker_id = -1): int|false
```

  * **Parameter**

    * `int $worker_id`

      * Funktion: PID des angegebenen Prozesses erhalten
      * Standardwert: -1 【-1 bedeutet den aktuellen Prozess】
      * Andere Werte: Keiner

!> Swoole-Version >= `v4.5.0RC1` verfügbar


## getWorkerStatus()

Erhalten Sie den Zustand des `Worker`-Prozesses

```php
Swoole\Server->getWorkerStatus(int $worker_id = -1): int|false
```

!> Swoole-Version >= `v4.5.0RC1` verfügbar

  * **Parameter**

    * `int $worker_id`

      * Funktion: Zustand des Prozesses erhalten
      * Standardwert: -1 【-1 bedeutet den aktuellen Prozess】
      * Andere Werte: Keiner

  * **Rückgabewert**
  
    * Gibt den Zustand des `Worker`-Prozesses zurück, siehe Prozessstatuswerte
    * Wenn es sich nicht um einen `Worker`-Prozess handelt oder der Prozess nicht existiert, wird false zurückgegeben

  * **Prozessstatuswerte**

    Konstante | Wert | Beschreibung | Versionsabhängigkeit
    ---|---|---|---
    SWOOLE_WORKER_BUSY | 1 | Belegt | v4.5.0RC1
    SWOOLE_WORKER_IDLE | 2 | Freies | v4.5.0RC1
    SWOOLE_WORKER_EXIT | 3 | Wenn [reload_async](/server/setting?id=reload_async) aktiviert ist, kann es einen neuen und einen alten Worker mit demselben worker_id geben, der alte Worker erhält den Statuscode EXIT. | v4.5.5


## getManagerPid()

Erhalten Sie die PID des aktuellen `Manager`-Prozesses des Services

```php
Swoole\Server->getManagerPid(): int
```

!> Swoole-Version >= `v4.5.0RC1` verfügbar


## getMasterPid()

Erhalten Sie die PID des aktuellen `Master`-Prozesses des Services

```php
Swoole\Server->getMasterPid(): int
```

!> Swoole-Version >= `v4.5.0RC1` verfügbar


## addCommand()

Fügen Sie einen benutzerdefinierten Befehl `command` hinzu

```php
Swoole\Server->addCommand(string $name, int $accepted_process_types, Callable $callback): bool
```

!> -Swoole-Version >= `v4.8.0` verfügbar         
  - Diese Funktion kann nur vor dem Starten des Servers aufgerufen werden, wenn bereits ein Befehl mit demselben Namen vorhanden ist, wird direkt false zurückgegeben

* **Parameter**

    * `string $name`

        * Funktion: Name des `command`
        * Standardwert: Keiner
        * Andere Werte: Keiner

    * `int $accepted_process_types`

      * Funktion: Typen von Prozessen, die den Request akzeptieren können, um mehrere Prozesstypen zu unterstützen, können Sie durch `|` verbunden werden, zum Beispiel `SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_MANAGER`
      * Standardwert: Keiner
      * Andere Werte:
        * `SWOOLE_SERVER_COMMAND_MASTER` master-Prozess
        * `SWOOLE_SERVER_COMMAND_MANAGER` manager-Prozess
        * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` worker-Prozess
        * `SWOOLE_SERVER_COMMAND_TASK_WORKER` task-Prozess

    * `callable $callback`

        * Funktion: Rückruffunktion, sie hat zwei Parameter, einen ist die Klasse `Swoole\Server` und der andere ist ein vom Benutzer definierter Variablen, der durch den vierten Parameter von `Swoole\Server::command()` übermittelt wird.
        * Standardwert: Keiner
        * Andere Werte: Keiner

* **Rückgabewert**

    * Gibt true zurück, wenn der benutzerdefinierte Befehl erfolgreich hinzugefügt wurde, gibt false zurück, wenn es fehlschlägt

## command()

Rufen Sie den definierten benutzerdefinierten Befehl `command` auf

```php
Swoole\Server->command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true): false|string|array
```

!>Swoole-Version >= `v4.8.0` verfügbar, unter `SWOOLE_PROCESS` und `SWOOLE_BASE` kann diese Funktion nur für den `master`-Prozess verwendet werden.  


* **Parameter**

    * `string $name`

        * Funktion: Name des `command`
        * Standardwert: Keiner
        * Andere Werte: Keiner

    * `int $process_id`

        * Funktion: Prozess-ID
        * Standardwert: Keiner
        * Andere Werte: Keiner

    * `int $process_type`

        * Funktion: Prozessanfragetyp, die folgenden anderen Werte können nur einander auswählen.
        * Standardwert: Keiner
        * Andere Werte:
          * `SWOOLE_SERVER_COMMAND_MASTER` master-Prozess
          * `SWOOLE_SERVER_COMMAND_MANAGER` manager-Prozess
          * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` worker-Prozess
          * `SWOOLE_SERVER_COMMAND_TASK_WORKER` task-Prozess

    * `mixed $data`

        * Funktion: Anfragedaten, diese Daten müssen serialisierbar sein
        * Standardwert: Keiner
        * Andere Werte: Keiner

    * `bool $json_decode`

        * Funktion: Ob `json_decode` verwendet werden soll
        * Standardwert: true
        * Andere Werte: false
  
  * **Verwendungsexempel**
    ```php
    <?php
    use Swoole\Http\Server;
    use Swoole\Http\Request;
    use Swoole\Http\Response;

    $server = new Server('127.0.0.1', 9501, SWOOLE_BASE);
    $server->addCommand('test_getpid', SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_EVENT_WORKER,
        function ($server, $data) {
          var_dump($data);
          return json_encode(['pid' => posix_getpid()]);
        });
    $server->set([
        'log_file' => '/dev/null',
        'worker_num' => 2,
    ]);

    $server->on('start', function (Server $serv) {
        $result = $serv->command('test_getpid', 0, SWOOLE_SERVER_COMMAND_MASTER, ['type' => 'master']);
        Assert::eq($result['pid'], $serv->getMasterPid());
        $result = $serv->command('test_getpid', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::eq($result['pid'], $serv->getWorkerPid(1));
        $result = $serv->command('test_not_found', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::false($result);

        $serv->shutdown();
    });

    $server->on('request', function (Request $request, Response $response) {
    });
    $server->start();
    ```
