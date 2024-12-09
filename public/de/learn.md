# Grundlagen

## Viere Möglichkeiten, Rückruffunktionen einzustellen

* **Anonyme Funktionen**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
!> Mit `use` können Parameter an die anonyme Funktion übergeben werden

* **Klassensystem静态 Methoden**

```php
class A
{
    static function test($req, $resp)
    {
        echo "hello world";
    }
}
$server->on('Request', 'A::Test');
$server->on('Request', array('A', 'Test'));
```
!> Die entsprechende statische Methode muss `public` sein

* **Funktionen**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **Objektmethoden**

```php
class A
{
    function test($req, $resp)
    {
        echo "hello world";
    }
}

$object = new A();
$server->on('Request', array($object, 'test'));
```

!> Die entsprechende Methode muss `public` sein


## Synchrone/Asynchrone IO

Unter `Swoole4+` ist der gesamte Geschäftscode synchron geschrieben (asynchrones Schreiben wurde in der `Swoole1.x` Ära unterstützt, wurde aber mittlerweile entfernt, da asynchrone Clients nicht mehr benötigt werden, und entsprechende Anforderungen können vollständig mit Coroutine-Clients umgesetzt werden), was völlig den menschlichen Denkgewohnheiten entspricht, aber der synchrone Schreibstil hat unter der Oberfläche möglicherweise eine Unterscheidung zwischen `synchrone IO/asynchrone IO`.

Egal ob es sich um synchrone IO oder asynchrone IO handelt, `Swoole/Server` kann eine große Anzahl von `TCP`-Clientverbindungen aufrechterhalten (siehe [SWOOLE_PROCESS-Modus](/learn?id=swoole_process)). Ob Ihr Dienst blockiert oder nicht, bedarf es keiner speziellen Konfigurationsparameter, es hängt davon ab, ob Ihr Code synchrone IO-Operationen enthält.

**Was ist synchrone IO:**

Ein einfacher Beispiel ist das Ausführen von `MySQL->query`, bei dem der Prozess nichts tut und auf die Rückkehr des Ergebnisses von MySQL wartet, bevor er weitercode führt, daher ist die parallele Fähigkeit eines synchrone IO-Dienstes sehr schlecht.

**Welcher Code ist synchrone IO:**

* Wenn [一键协程化](/runtime) nicht aktiviert ist, dann sind die meisten IO-Operationen in Ihrem Code synchron IO, nachdem sie koordiniert wurden, werden sie zu asynchron IO, der Prozess wird nicht dumm dort warten, siehe [协程调度](/coroutine?id=协程调度).
* Einige `IO` können nicht einfach koordiniert werden, um synchrone IO in asynchron IO zu verwandeln, wie zum Beispiel `MongoDB` (ich glaube, `Swoole` wird dieses Problem lösen), achten Sie beim Schreiben von Code darauf.

!> [协程](/coroutine) dient dem Zweck der Steigerung der parallelen Fähigkeiten, wenn mein Anwendungsfall keine hohe Parallelität hat oder bestimmte IO-Operationen verwendet werden müssen, die nicht asynchronisiert werden können (wie im obigen MongoDB-Beispiel), dann können Sie ganz einfach [一键协程化](/runtime) deaktivieren, [enable_coroutine](/server/setting?id=enable_coroutine) ausschalten und mehr `Worker` Prozesse starten, was genau das gleiche Modell wie `Fpm/Apache` ist. Es ist erwähnenswert, dass aufgrund der Tatsache, dass `Swoole` ständig läuft, selbst wenn die Leistung von synchrone IO erheblich steigt, viele Unternehmen in der Praxis dies tun.


### Synchrone IO in asynchrones IO umwandeln

[Der obige Abschnitt](/learn?id=同步io异步io) hat erklärt, was synchrone/asynchrone IO ist. Unter `Swoole` können einige synchrone IO-Operationen in asynchrones IO umgewandelt werden.

 - Nachdem [一键协程化](/runtime) aktiviert wurde, werden Operationen wie `MySQL`, `Redis`, `Curl` usw. zu asynchronem IO.
 - Verwenden Sie das [Event](/event)-Modul, um Ereignisse manuell zu verwalten, indem Sie den fd zum [EventLoop](/learn?id=什么是eventloop) hinzufügen, um ihn in asynchrones IO zu verwandeln, Beispiel:

```php
// Verwenden Sie inotify, um Dateiveränderungen zu überwachen
$fd = inotify_init();
// Fügen Sie den $fd zum Swoole EventLoop hinzu
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd);// Nach einer Dateiveränderung wird der veränderte Inhalt der Datei gelesen.
    var_dump($var);
});
```

Wenn der obige Code ohne die Anrufung von `Swoole\Event::add` asynchronisiert wird, wird die direkte `inotify_read()` den Worker-Prozess blockieren, und andere Anforderungen werden nicht verarbeitet.

 - Verwenden Sie die [sendMessage()](/server/methods?id=sendMessage)-Methode des `Swoole\Server` für die Prozess-zu-Prozess-Kommunikation, bei der `sendMessage` standardmäßig synchrone IO ist, aber unter bestimmten Umständen wird es von `Swoole` in asynchrones IO umgewandelt, nehmen wir zum Beispiel den [User-Prozess](/server/methods?id=addprocess) als Beispiel:

```php
$serv = new Swoole\Server("0.0.0.0", 9501, SWOOLE_BASE);
$serv->set(
    [
        'worker_num' => 1,
    ]
);

$serv->on('pipeMessage', function ($serv, $src_worker_id, $data) {
    echo "#{$serv->worker_id} message from #$src_worker_id: $data\n";
    sleep(10);// Erhalten Sie keine Daten von sendMessage, der Puffer wird schnell vollgeschrieben
});

$serv->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data) {

});

// Situation 1: Synchrone IO (Standardverhalten)
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));// Standardmäßig wird hier blockiert, wenn der Puffer voll ist
    }
}, false);

// Situation 2: Wenn die Unterstützung für Coroutine im UserProcess-Prozess durch den enable_coroutine-Parameter aktiviert ist, um zu verhindern, dass andere Coroutinen nicht vom EventLoop ab调度,
// wandelt Swoole sendMessage in asynchrones IO um
$enable_coroutine = true;
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));// Wenn der Puffer voll ist, wird der Prozess nicht blockiert, es wird ein Fehler gemeldet
    }
}, false, 1, $enable_coroutine);

// Situation 3: Wenn im UserProcess-Prozess asynchrone Rückruffunktionen eingerichtet sind (z.B. Timer-Einstellung, Swoole\Event::add usw.),
// um zu verhindern, dass andere Rückruffunktionen nicht vom EventLoop ab调度, wandelt Swoole sendMessage in asynchrones IO um
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    swoole_timer_tick(2000, function ($interval) use ($worker, $serv) {
        echo "timer\n";
    });
    while (1) {
        var_dump($serv->sendMessage("big string", 0));// Wenn der Puffer voll ist, wird der Prozess nicht blockiert, es wird ein Fehler gemeldet
    }
}, false);

$serv->addProcess($userProcess);

$serv->start();
```

 - Ebenso ist die Kommunikation zwischen [Task-Prozess](/learn?id=taskworker进程) und [sendMessage()](/server/methods?id=sendMessage) einander ähnlich, der Unterschied besteht darin, dass die Unterstützung für Coroutine im Task-Prozess durch die Server-Konfiguration `[task_enable_coroutine](/server/setting?id=task_enable_coroutine)` aktiviert wird und es keine Situation 3 gibt, das heißt, der Task-Prozess wird nicht wegen der Aktivierung asynchroner Rückruffunktionen in sendMessage asynchrones IO.


## Was ist EventLoop

Der sogenannte `EventLoop` ist ein Ereigniszyklus, der sich简单地 als epoll_wait vorstellen lässt, der alle Handles (fd), die Ereignisse auslösen sollen, in epoll_wait aufnimmt. Diese Ereignisse umfassen Lesbarkeit, Schreibbarkeit, Fehler usw.

Der entsprechende Prozess blockiert sich an der kernel-Funktion epoll_wait auf, und wenn ein Ereignis (oder eine Zeitüberschreitung) eintritt, beendet epoll_wait den Block und gibt Ergebnisse zurück, so dass der entsprechende PHP-Funktion aufgerufen werden kann, zum Beispiel, um Daten zu empfangen, die von einem Client gesendet wurden, und die `onReceive` Rückruffunktion aufzurufen.

Wenn eine große Anzahl von fd in epoll_wait aufgenommen werden und gleichzeitig viele Ereignisse auftreten, wird epoll_wait bei seiner Rückkehr nacheinander die entsprechenden Rückruffunktionen aufrufen, was als eine Runde des Ereigniszyklus bezeichnet wird, also IO-Multiplexierung, und dann blockiert es erneut, um epoll_wait für die nächste Runde des Ereigniszyklus aufzurufen.
## TCP-Paket-Grenzenprobleme

Ohne Concurrency kann der Code im [Schnellstart](/start/start_tcp_server) normal funktionieren, aber bei hoher Concurrency treten Probleme mit den Grenzen der TCP-Paketes auf. Das TCP-Protokoll löst auf der unteren Ebene das Problem der Reihenfolge und des Wiederholens verlorener Pakete des UDP-Protokolls, bringt aber im Vergleich zum UDP neue Probleme mit sich. Das TCP-Protokoll ist Stream-orientiert, es gibt keine packet boundaries für die Datenpakete, und Anwendungen, die TCP für die Kommunikation verwenden, müssen sich diese Herausforderungen stellen, was als TCP-Sticky-Packet-Problem bekannt ist.

Da die TCP-Kommunikation Stream-orientiert ist, kann ein großes Datenpaket bei Empfang in mehrere Pakete zerlegt werden, die dann gesendet werden. Auch unterhalb von mehreren `Send`-Befehlen können Pakete zu einem zusammen gefassten Paket zusammengefasst werden. Hier sind zwei Aktionen erforderlich, um dies zu lösen:

* Packetisierung: Der `Server` empfängt mehrere Pakete und muss sie zerlegen.
* Packen: Die vom `Server` empfangenen Daten sind nur ein Teil eines Pakets, es muss Daten zwischengespeichert und zu einem vollständigen Paket zusammengefasst werden.

Daher ist es notwendig, beim TCP-Netzwerkkommunizieren ein Kommunikationsprotokoll festzulegen. Zu den gängigsten allgemeinen TCP-Netzwerkprotokollen gehören `HTTP`, `HTTPS`, `FTP`, `SMTP`, `POP3`, `IMAP`, `SSH`, `Redis`, `Memcache`, `MySQL`.

Es ist erwähnenswert, dass Swoole viele gängige allgemeine Protokolle integriert hat, um die Probleme mit den packet boundaries der TCP-Datenpakete für diese Protokolle zu lösen. Es bedarf nur einer einfachen Konfiguration, siehe [open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol).

Neben allgemeinen Protokollen können auch benutzerdefinierte Protokolle verwendet werden. Swoole unterstützt zwei Arten von benutzerdefinierten Netzwerkkommunikationsprotokollen.

* **EOF-Endloszeichen-Protokoll**

Das Prinzip des EOF-Protokolls ist, dass jeder Datenpaket am Ende mit einer speziellen字符-Reihe endet, die angibt, dass das Paket abgeschlossen ist. Zum Beispiel verwenden `Memcache`, `FTP` und `SMTP` beide `\r\n` als Endloszeichen. Beim Senden von Daten muss nur `\r\n` am Ende des Pakets hinzugefügt werden. Bei der Verwendung des EOF-Protokolls muss sichergestellt werden, dass das EOF innerhalb des Pakets nicht auftritt, sonst kann es zu Fehlern bei der packetisierung kommen.

In den Codes des `Servers` und der `Client` müssen nur zwei Parameter festgelegt werden, um das EOF-Protokoll zu verwenden.

```php
$server->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
```

Die oben genannte EOF-Konfiguration ist jedoch in Bezug auf Leistung nicht sehr gut. Swoole wird jedes Byte durchlaufen, um zu sehen, ob es `\r\n` ist. Neben der oben genannten Methode kann es auch so eingestellt werden.

```php
$server->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
```
Diese Gruppe von Einstellungen ist viel effizienter, da sie nicht durch die Daten iteriert werden müssen, aber sie können nur das Problem der packetisierung lösen und nicht das des packens. Das bedeutet, dass möglicherweise mehrere Anfragen, die von einem Client an einen Server gesendet werden, in einem `onReceive`-Ereignis empfangen werden müssen, und es ist notwendig, sie selbst zu verpacken, zum Beispiel mit `explode("\r\n", $data)`. Die Hauptverwendung dieser Gruppe von Einstellungen ist, wenn man auf eine Antwortende Anfrage-Antwort-Service (z.B. ein Terminal, das Befehle eingibt) angewiesen ist, da man sich keine Sorgen über die Zerlegung der Daten machen muss. Der Grund dafür ist, dass der Client nach dem Senden einer Anfrage darauf warten muss, dass der Server die Antwort auf die aktuelle Anfrage zurücksendet, bevor er eine zweite Anfrage sendet, und es wird nicht gleichzeitig zwei Anfragen gesendet.

* **Festheader + Paketbody-Protokoll**

Die Methode mit festem Header ist sehr allgemein und wird oft in Serverprogrammen verwendet. Diese Art von Protokoll zeichnet sich durch zwei Teile aus: einen Header und ein Paketbody. Der Header wird durch ein Feld angegeben, das die Länge des Paketbody oder des gesamten Pakets festlegt. Die Länge wird in der Regel mit einem 2-Byte-/4-Byte-Integer dargestellt. Nachdem der Server den Header empfangen hat, kann er basierend auf dem Wert der Länge genau bestimmen, wie viel Daten noch empfangen werden muss, um ein vollständiges Paket zu erhalten. Die Konfiguration von Swoole kann diese Art von Protokoll sehr gut unterstützen und vier Parameter flexibel einstellen, um alle Fälle zu bewältigen.

Der `Server` verarbeitet Pakete im [onReceive](/server/events?id=onreceive)-Rückruffunktions. Nachdem das Protokoll festgelegt wurde, wird das [onReceive](/server/events?id=onreceive)-Ereignis nur ausgelöst, wenn ein vollständiges Paket empfangen wurde. Nachdem der Client das Protokoll festgelegt hat, muss er beim Aufrufen von [$client->recv()](/client?id=recv) keine Länge mehr angeben. Die `recv` Funktion gibt zurück, nachdem sie ein vollständiges Paket empfangen hat oder ein Fehler aufgetreten ist.

```php
$server->set(array(
    'open_length_check' => true,
    'package_max_length' => 81920,
    'package_length_type' => 'n', //see php pack()
    'package_length_offset' => 0,
    'package_body_offset' => 2,
));
```

!>具体内容, was jeder Konfigurationsbereich bedeutet, siehe die [Konfiguration](/server/setting?id=open_length_check)-Abschnitt im Kapitel "Server/Client".


## Was ist IPC?

Es gibt viele Möglichkeiten der Kommunikation zwischen zwei Prozessen auf demselben Host (kurz IPC). Unter Swoole werden zwei Methoden verwendet: `Unix Socket` und `sysvmsg`. Lassen Sie uns diese jeweils详细介绍:


- **Unix Socket**  

    Der vollständige Name ist UNIX Domain Socket, kurz UDS. Er verwendet die API des Sockets (socket, bind, listen, connect, read, write, close usw.), im Gegensatz zu TCP/IP muss keine IP-Adresse und Port angegeben werden, sondern es wird durch einen Dateinamen dargestellt (z.B. `/tmp/php-fcgi.sock` zwischen FPM und Nginx). UDS ist eine volles Memory-Kommunikation, die von der Linux-Kernel implementiert wird und keine `IO`-Verbrauch hat. Bei einem Test mit `write` und `read` von jeweils 1024 Byte Daten durch einen Prozess, benötigen 1 Million Kommunikationen nur 1,02 Sekunden, und es ist sehr leistungsstark. Unter Swoole wird standardmäßig diese Art von IPC verwendet.  
      
    * **`SOCK_STREAM` und `SOCK_DGRAM`**  

        Unter Swoole gibt es zwei Arten von UDS-Kommunikation, `SOCK_STREAM` und `SOCK_DGRAM`, die sich im Wesentlichen wie TCP und UDP verhalten können. Wenn `SOCK_STREAM` verwendet wird, muss man sich auch um das Problem der [TCP-Paketgrenzen](/learn?id=tcp数据包边界问题) kümmern.   
        Wenn `SOCK_DGRAM` verwendet wird, muss man sich nicht um die TCP-Paketgrenzen kümmern. Jedes `send()`-Datenpaket hat Grenzen, und der empfangene Datenmenge entspricht der gesendeten Größe. Es gibt keine Probleme mit dem Verlust von Paketen oder der Reihenfolge während des Transports, und die Reihenfolge von `send`-Schreiben und `recv`-Lesen ist vollständig identisch. Ein erfolgreicher `send`-Befehl bedeutet definitiv, dass man ein `recv`-Datenpaket empfangen kann. 

    Für kleine IPC-Daten ist die Verwendung von `SOCK_DGRAM` sehr geeignet, da es eine maximale Größe von 64k für IP-Pakete gibt, daher darf bei der Verwendung von `SOCK_DGRAM` für IPC die gesendete Datenmenge pro Sendebefehl nicht größer als 64k sein. Gleichzeitig muss darauf geachtet werden, dass das Empfangstempo zu langsam ist und der Betriebssystem-Puffer voll ist, was zu einem丢弃(verwerfen) der Pakete führen kann, da UDP Pakete zulässt. Es ist möglich, den Puffer angemessen zu vergrößern.


- **sysvmsg**
     
    Das ist das von Linux bereitgestellte `Message Queue`-IPC-System. Diese Art von IPC kommuniziert über einen Dateinamen als `key`. Diese Methode ist sehr unflexibel und wird in realen Projekten nicht oft verwendet, daher wird sie nicht weiter ausgeführt.

    * **Diese Art von IPC ist nur in zwei Szenarien nützlich:**

        - Um Datenverluste zu verhindern: Wenn das gesamte Dienst heruntergeht und wieder gestartet wird, sind die Nachrichten im队列 noch vorhanden und können weiter verarbeitet werden, **aber es gibt auch das Problem mit schmutzigen Daten**.
        - Es ist möglich, Daten von außen zu liefern, zum Beispiel können `Worker`-Prozesse unter Swoole Aufgaben an `Task`-Prozesse über eine Message Queue senden, und externe Prozesse können auch Aufgaben in die Queue einreichen, damit sie von `Task` verarbeitet werden können, oder man kann sogar Nachrichten manuell über die Befehlszeile in die Queue einreichen.
## Masterprozess, Reactor-Threads, Worker-Prozesse, Task-Prozesse und Manager-Prozesse: Unterschiede und Zusammenhänge :id=diff-process


### Masterprozess

* Der Masterprozess ist ein Mehrthread-Prozess, siehe [Prozess/Thread-Strukturbild](/server/init?id=Prozess-Thread-Strukturbild).


### Reactor-Threads

* Reactor-Threads werden im Masterprozess erstellt
* Sind für die Wartung von Client-TCP-Verbindungen, die Behandlung von Netzwerk-IO, die Behandlung von Protokollen und das Senden/Empfangen von Daten verantwortlich
* führen kein PHP-Code aus
* Buffern, zusammenfügen und in vollständige Request-Datenpakete zerlegen, die von TCP-Clients gesendet werden


### Worker-Prozesse

* Nimmt die von Reactor-Threads übergebenen Request-Datenpakete entgegen und führt PHP-Rückruffunktionen aus, um Daten zu verarbeiten
* Erstellt Antwortdaten, die asynchron an Reactor-Threads gesendet werden, von denen sie an TCP-Clients gesendet werden
* Kann sowohl asynchron und nicht blockierend als auch synchron und blockierend sein
* Worker laufen in mehreren Prozessen


### TaskWorker-Prozesse

* Nimmt Aufgaben entgegen, die von Worker-Prozessen über Swoole\Server->[task](/server/methods?id=task)/[taskwait](/server/methods?id=taskwait)/[taskCo](/server/methods?id=taskCo)/[taskWaitMulti](/server/methods?id=taskWaitMulti) Methoden übermittelt werden
* Behandelt Aufgaben und返回(mit [Swoole\Server->finish](/server/methods?id=finish)) die Ergebnisse an Worker-Prozesse
* Ist vollständig **synchron und blockierend**
* TaskWorker laufen in mehreren Prozessen, [task vollständiges Beispiel](/start/start_task)


### Manager-Prozesse

* Sind für die Erstellung/Recycling von worker/task Prozessen verantwortlich

Ihre Beziehung kann als Reactor = nginx, Worker = PHP-FPM verstanden werden. Reactor-Threads behandeln Netzwerkanfragen asynchron und parallel und leiten sie dann an Worker-Prozesse weiter. Zwischen Reactor und Worker wird über [unixSocket](/learn?id=Was ist IPC) kommuniziert.

In der Anwendung von PHP-FPM werden Aufgaben oft asynchron in Queues wie Redis eingereicht und im Hintergrund einige PHP-Prozesse gestartet, um diese Aufgaben asynchron zu bearbeiten. Swoole bietet das TaskWorker als ein umfassenderes Lösungspaket, das die Übertragung von Aufgaben, die Queue und die Verwaltung von PHP-Task-Prozessen integriert. Durch die unteren bereitgestellten APIs kann die asynchrone Behandlung von Aufgaben sehr einfach umgesetzt werden. Außerdem kann TaskWorker nach Abschluss der Aufgabe ein Ergebnis zurück an Worker senden.

Swooles Reactor, Worker und TaskWorker können eng kombiniert werden, um eine höhere Ebene der Nutzung zu bieten.

Eine noch einfachere Metapher: Nehmen wir an, dass das Server ein Fabrik ist, dann ist der Reactor der Verkäufer, der Kundenaufträge annimmt. Und der Worker ist der Arbeiter, der, wenn der Verkäufer den Auftrag erhält, zu arbeiten beginnt und das, was der Kunde will, herstellt. TaskWorker könnte als Büroangestellter verstanden werden, der dem Worker bei der Arbeit helfen kann, damit er sich auf seine Arbeit konzentrieren kann.

Wie im Bild:

![process_demo](_images/server/process_demo.png)


## Einführung in die drei Betriebsweisen des Servers

Im dritten Parameter des Swoole\Server-Konstruktors kann man drei Konstantenwerte einfüllen - [SWOOLE_BASE](/learn?id=swoole_base), [SWOOLE_PROCESS](/learn?id=swoole_process) und [SWOOLE_THREAD](/learn?id=swoole_thread). Im Folgenden werden die Unterschiede sowie Vor- und Nachteile dieser drei Modi einzeln vorgestellt.


### SWOOLE_PROCESS

Der Server im SWOOLE_PROCESS-Modus hat alle TCP-Verbindungen mit dem [Hauptknotenprozess](/learn?id=reactor-threads) eingerichtet, die innere Umsetzung ist ziemlich komplex und verwendet viel Prozess-zu-Prozess-Kommunikation und Prozess-Management-Mechanismen. Geeignet für Szenarien mit sehr komplexer Geschäftslogik. Swoole bietet ein umfassendes Prozessmanagement und Memory-Schutzmechanismus.
In Situationen mit sehr komplexer Geschäftslogik kann es auch langfristig stabil laufen.

Swoole bietet im [Reactor](/learn?id=reactor-threads)-Thread die Funktion von Buffern, die sich mit einer großen Anzahl von langsameren Verbindungen und byteweise bösen Clients auseinandersetzen können.

#### Vorteile des Prozessmodells:

* Verbindung und Datenanfragevergabe sind getrennt, sodass aufgrund großer oder kleiner Datenmengen bei einigen Verbindungen nicht zu unausgewogenen Worker-Prozessen führen kann
* Wenn ein Worker-Prozess einen tödlichen Fehler aufweist, werden die Verbindungen nicht abgeschnitten
* Es ist möglich, einzelne Verbindungen gleichzeitig zu bearbeiten, indem nur eine kleine Anzahl von TCP-Verbindungen erhalten bleibt und Anfragen gleichzeitig in mehreren Worker-Prozessen verarbeitet werden können

#### Nachteile des Prozessmodells:

* Es gibt eine Doppelte IPC-开销, Master-Prozesse und Worker-Prozesse müssen über [unixSocket](/learn?id=Was ist IPC) kommunizieren
* SWOOLE_PROCESS unterstützt keine PHP ZTS, in diesem Fall kann nur SWOOLE_BASE verwendet werden oder [single_thread](/server/setting?id=single_thread) auf true gesetzt werden


### SWOOLE_BASE

Das SWOOLE_BASE-Modell ist ein traditioneller asynchroner, nicht blockierender Server. Er ist völlig identisch mit Programmen wie Nginx und Node.js.

Der [worker_num](/server/setting?id=worker_num)-Parameter ist für das BASE-Modell immer noch gültig und wird mehrere Worker-Prozesse starten.

Wenn eine TCP-Verbindungsanfrage eingeht, kämpfen alle Worker-Prozesse um diese Verbindung, und schließlich wird ein Worker-Prozess erfolgreich eine direkte TCP-Verbindung zum Client aufbauen. Danach wird alle Datenübertragung für diese Verbindung direkt mit diesem Worker kommunizieren, ohne dass sie durch den Reactor-Thread des Hauptprozesses weitergeleitet wird.

* Im BASE-Modell gibt es keine Rolle des Master-Prozess, nur die Rolle des [Manager](/learn?id=manager-prozess).
* Jeder Worker-Prozess übernimmt gleichzeitig die Rollen des Reactor-Threads und des Worker-Prozesses im SWOOLE_PROCESS-Modell.
* Im BASE-Modell ist der Manager-Prozess optional, wenn `worker_num=1` festgelegt ist und keine Tasks und MaxRequest-Funktionen verwendet werden, wird unten direkt ein einzelner Worker-Prozess erstellt, ohne einen Manager-Prozess zu erstellen.

#### Vorteile des BASE-Modells:

* Das BASE-Modell hat keine IPC-开销, die Leistung ist besser
* Das BASE-Modell hat einfacher Code, es ist weniger anfällig für Fehler

#### Nachteile des BASE-Modells:

* TCP-Verbindungen werden im Worker-Prozess aufrechterhalten, daher werden alle Verbindungen, die in einem Worker-Prozess geschlossen werden, wenn dieser Worker abstürzt, geschlossen
* Eine kleine Anzahl von langen TCP-Verbindungen kann nicht alle Worker-Prozesse nutzen
* TCP-Verbindungen sind mit Worker verbunden, in Anwendungen mit langen Verbindungen ist die Last für einige Verbindungen sehr hoch, da diese Verbindungen im Worker-Prozess liegen. Aber bei einigen Verbindungen ist die Datenmenge klein, sodass die Last für den Worker-Prozess sehr niedrig ist. Verschiedene Worker-Prozesse können keine Ausgleichung erreichen.
* Wenn die Rückruffunktion blockierende Operationen enthält, kann der Server in einen synchronen Modus zurückkehren, was zu einem Problem mit der Füllung der [backlog](/server/setting?id=backlog)-Warteschlange für TCP führen kann.

#### Anwendungsfälle für das BASE-Modell:

Wenn es keine Interaktion zwischen Clientverbindungen gibt, kann das BASE-Modell verwendet werden. Zum Beispiel Memcache, HTTP-Server usw.

#### Einschränkungen des BASE-Modells:

Im BASE-Modell werden alle Methoden des Servers außer [send](/server/methods?id=send) und [close](/server/methods?id=close)** nicht** zwischen Prozessen unterstützt.

!> In der v4.5.x Version des BASE-Modells wird nur die send-Methode zwischen Prozessen unterstützt; in der v4.6.x Version werden nur die send- und close-Methoden unterstützt.
### SWOOLE_THREAD

SWOOLE_THREAD ist ein neuer Betriebsmodus, der mit dem PHP ZTS-Modus eingeführt wurde und es uns jetzt ermöglicht, Dienstleistungen im Mehr线程-Modus zu starten.

Der Parameter [worker_num](/server/setting?id=worker_num) ist für den THREAD-Modus immer noch gültig, aber anstatt mehrere Prozesse zu erstellen, werden jetzt mehrere Threads erzeugt.

Es gibt nur einen Prozess, und die Tochterprozesse werden in Tochterthreads umgewandelt, um Anfragen von Kunden zu empfangen.

#### Vorteile des THREAD-Modus:
* Zwischenprozesskommunikation ist einfacher, ohne zusätzliche IPC-Kommunikationskosten.
* Das Debuggen des Programms ist bequemer, da es nur einen Prozess gibt, ist `gdb -p` einfacher.
* Es bietet die Bequemlichkeit der asynchronen IO-Programmierung mit Coroutinen und die Vorteile der parallelen Ausführung von Mehr线程 und der gemeinsamen Memory-Stack.

#### Nachteile des THREAD-Modus:
* Im Falle eines Crashes oder wenn Process::exit() aufgerufen wird, verlässt der gesamte Prozess, und es ist notwendig, auf der Client-Seite gute Fehlerwiederholungs- und Disconnexionsverarbeitunglogiken zu implementieren. Darüber hinaus ist es notwendig, Supervisor und docker/k8s zu verwenden, um den Prozess nach dem Ausfall automatisch wieder zu starten.
* Es können zusätzliche Kosten für ZTS und Lock-Operationen entstehen, und die Leistung könnte etwa 10% schlechter sein als das NTS-Mehrprozess-Parallellaufmodell. Wenn es sich um eine stateless-Service handelt, wird immer noch empfohlen, den NTS-Mehrprozess-Betriebsmodus zu verwenden.
* Es wird keine Unterstützung für das Übertragen von Objekten und Ressourcen zwischen Threads geben.

#### Anwendungsbereiche des THREAD-Modus:
Der THREAD-Modus ist effizienter für die Entwicklung von Spielservern und Kommunikationsservern.

## Was ist der Unterschied zwischen Process, Process\Pool und UserProcess :id=process-diff


### Process

[Process](/process/process) ist ein Prozessverwaltungsmodul von Swoole, das den PHP pcntl ersetzt.
 
* Er ermöglicht eine bequeme Prozess间通讯;
* unterstützt die Umleitung von Standardeingabe und -ausgabe, in einem Tochterprozess wird `echo` nicht auf den Bildschirm gedruckt, sondern in eine Pipe geschrieben, und die Lesung von Tastatureingaben kann umgeleitet werden, um Daten aus einer Pipe zu lesen;
* bietet die [exec](/process/process?id=exec)-Schnittstelle, die ein durchgeführter Prozess andere Programme ausführen kann und eine bequeme Kommunikation mit dem ursprünglichen PHP-Vaterprozess ermöglicht;

!> In einem Coroutine-Umwelt kann das `Process`-Modul nicht verwendet werden, es kann jedoch mit `runtime hook`+`proc_open` umgesetzt werden, siehe [Coroutine Prozessverwaltung](/coroutine/proc_open)


### Process\Pool

[Process\Pool](/process/process_pool) ist eine PHP-Klasse, die den Prozessverwaltungsmodul des Servers verpackt und es den Entwicklern ermöglicht, Swooles Prozessmanager in PHP-Code zu verwenden.

In echten Projekten müssen Entwickler oft langlaufende Skripte schreiben, wie zum Beispiel Verbraucher für Mehrprozess-Warteschlangen basierend auf Redis, Kafka, RabbitMQ, Mehrprozess-Webscraper usw. Die Entwickler müssen die pcntl- und posix-Erweiterungslibraries verwenden, um Mehrprozess-Programmierung zu implementieren, aber sie müssen auch eine tiefe Kenntnis der Linux-Systemprogrammierung haben, sonst können Probleme leicht auftreten. Die Verwendung des Prozessmanagers von Swoole kann die Arbeit am Schreiben von Mehrprozess-Skripten erheblich vereinfachen.

* Garantiert die Stabilität der Arbeitsprozesse;
* Unterstützt Signalbehandlung;
* Unterstützt Message Queues und TCP-Socket-Nachrichtenübermittlung;

### UserProcess

`UserProcess` ist ein vom Benutzer自定义的工作进程, der mit [addProcess](/server/methods?id=addprocess) hinzugefügt wurde und normalerweise für die Erstellung eines speziellen Arbeitsprozesses verwendet wird, um Überwachung, Berichterstattung oder andere spezielle Aufgaben durchzuführen.

Obwohl `UserProcess` von [Managerprozess](/learn?id=manager进程) verwaltet wird, ist es im Vergleich zu [Workerprozess](/learn?id=worker进程) ein relativ unabhängiger Prozess, der für die Ausführung von benutzerdefinierten Funktionen verwendet wird.
