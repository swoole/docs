# Verwendungsmuster

## Wie gut ist die Leistung von Swoole?

> Vergleich der QPS

Mit dem ApacheBench-Tool (ab) wurden Drucktests auf statische Seiten von Nginx, ein Golang HTTP-Programm und ein PHP7+Swoole HTTP-Programm durchgeführt. Auf demselben机器 wurden基准tests mit bis zu 100 parallelen Prozessen und insgesamt 1 Million HTTP-Anfragen durchgeführt, wobei der QPS wie folgt abgewogen wurde:

| Software | QPS | Softwareversion |
| --- | --- | --- |
| Nginx | 164489.92 | nginx/1.4.6 (Ubuntu) |
| Golang | 166838.68 | go version go1.5.2 linux/amd64 |
| PHP7+Swoole | 287104.12 | Swoole-1.7.22-alpha |
| Nginx-1.9.9 | 245058.70 | nginx/1.9.9 |

!> Hinweis: Bei den Tests mit Nginx-1.9.9 wurde die access_log deaktiviert und der open_file_cache für statische Dateien in RAM aktiviert

> Testumgebung

* CPU: Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* RAM: 16G
* Festplatte: 128G SSD
* Betriebssystem: Ubuntu14.04 (Linux 3.16.0-55-generic)

> Drucktestmethode

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> VHOST-Konfiguration

```nginx
server {
    listen 80 default_server;
    root /data/webroot;
    index index.html;
}
```

> Testseite

```html
<h1>Hallo Welt!</h1>
```

> Anzahl der Prozesse

Nginx hat 4 Worker-Prozesse gestartet
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12月07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12月07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12月07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12月07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12月07   0:45 nginx: worker process
```

> Golang

Testcode

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

PHP7 hat die `OPcache`-Optimierung aktiviert.

Testcode

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **Weltweites权威Web-Framework-Leistungstests Techempower Web Framework Benchmarks**

Aktuelle Testergebnisse finden Sie unter: [techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swoole führt in der Kategorie der dynamischen Sprachen an

Datenbank-IO-Operationstests, ohne besondere Optimierungen mit grundlegendem Geschäftslogikcode

**Leistung über alle statischen Sprachframeworks hinaus (mit MySQL anstelle von PostgreSQL)**


## Wie hält Swoole TCP-Langlebigverbindungen aufrecht?

Es gibt zwei Konfigurationsgruppen für das Aufrechterhalten von TCP-Langlebigverbindungen: [tcp_keepalive](/server/setting?id=open_tcp_keepalive) und [heartbeat](/server/setting?id=heartbeat_check_interval).


## Wie startet Swoole den Dienst richtig?

Im täglichen Entwickeln müssen nach der Änderung von PHP-Code oft der Dienst neu gestartet werden, damit der Code wirksam wird. Ein belebter Hintergrundserver bearbeitet ständig Anfragen. Wenn ein Administrator den Prozess durch das `kill`-Befehl beendet oder neu startet, kann dies dazu führen, dass der Code in der Mitte einer Ausführung abgebrochen wird und die Integrität der gesamten Geschäftslogik nicht gewährleistet werden kann.

Swoole bietet ein flexibles Mechanismus zum endgültigen Beenden/Neu starten des Diensts. Administratoren müssen nur spezifische Signale an den Server senden oder die `reload`-Methode aufrufen, und die Arbeitsprozesse können beendet und neu gestartet werden. Weitere Informationen finden Sie unter [reload()](/server/methods?id=reload).

Es gibt jedoch einige Punkte zu beachten:

Zuerst ist zu beachten, dass der neu modifizierte Code erst wirksam wird, wenn er im `OnWorkerStart`-Ereignis neu geladen wird. Zum Beispiel ist es nicht möglich, wenn eine Klasse bereits durch Composer's Autoloader geladen wurde, bevor sie im `OnWorkerStart`-Ereignis verwendet wird.

Zweitens sollte die `reload`-Methode mit den beiden Parametern [max_wait_time](/server/setting?id=max_wait_time) und [reload_async](/server/setting?id=reload_async) kombiniert werden, um eine asynchrone sichere Wiederherstellung zu ermöglichen.

Wenn diese Funktion nicht vorhanden ist, werden Arbeitsprozesse, die ein Restart-Signal erhalten oder die [max_request](/server/setting?id=max_request) erreichen, sofort den Dienst stoppen. Zu diesem Zeitpunkt können im Arbeitsprozess noch Ereignislistener aktiv sein, und diese asynchronen Aufgaben werden abgelehnt. Nachdem die oben genannten Parameter festgelegt wurden, werden zuerst neue Worker-Prozesse erstellt, und die alten Worker-Prozesse werden nach Abschluss aller Ereignisse selbst beenden, das heißt `reload_async`.

Wenn die alten Worker-Prozesse nicht aussteigen, wird im Hintergrund ein Timer hinzugefügt, der in einem festgelegten Zeitraum ([max_wait_time](/server/setting?id=max_wait_time) Sekunden) nach dem alten Worker-Prozess nicht ausgestiegen ist, der Hintergrund wird den Worker-Prozess zwingen, auszusteigen, und ein [WARNING](/question/use?id=forced-to-terminate) Fehler wird generiert.

Beispiel:

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tClient[$fd] receive data: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

Zum Beispiel würde im obigen Code der Timer, der im `onReceive`-Ereignis erstellt wurde, verloren gehen und es wäre keine Chance, die Callback-Funktion des Timers zu verarbeiten.


### Prozessaustrittsevent

Um die asynchrone Wiederherstellungsfunktion zu unterstützen, wurde im Hintergrund ein [onWorkerExit](/server/events?id=onWorkerExit)-Ereignis hinzugefügt. Wenn der alte Worker-Prozess kurz vor dem Ausstieg ist, wird das `onWorkerExit`-Ereignis ausgelöst. In der Rückruffunktion dieses Ereignisses kann die Anwendungsseite versuchen, bestimmte langlebige Verbindungen (`Socket`) zu bereinigen, bis es keine FileDescriptors mehr im EventLoop gibt oder die [max_wait_time](/server/setting?id=max_wait_time) erreicht ist und der Prozess den Dienst verlässt.

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

Gleichzeitig wurde in [Swoole Plus](https://www.swoole.com/swoole_plus) die Funktion zur Überwachung von Dateiveränderungen hinzugefügt, die es ermöglicht, Worker ohne manuellen Reload oder Signal zu neu starten, wenn sich Dateien ändern.
## Warum ist es unsicher, sofort nach dem Senden zu schließen?

Es ist unsicher, sofort nach dem Senden zu schließen, sowohl auf Serverseite als auch auf Clientseite.

Das erfolgreiche Senden bedeutet nur, dass die Daten erfolgreich in den OS-Socket-Puffer geschrieben wurden, aber es bedeutet nicht, dass das andere Ende die Daten tatsächlich empfangen hat. Ob die Operation erfolgreich von der OS durchgeführt wurde, ob das Remote-Server die Daten erhalten hat und ob das Servergeschäft die Daten verarbeitet hat, kann nicht sicher garantiert werden.

> Zum Nachvollziehen der Logik nach dem Schließen siehe die folgenden Linger-Einstellungen.

Diese Logik ist vergleichbar mit einem Telefonat: A erzählt B etwas, und nachdem A fertig gesprochen hat, legt er auf. Dann weiß A nicht, ob B es gehört hat. Wenn A nach dem Sprechen fertig ist, sagt B ja und legt dann auf, ist das definitiv sicher.

Linger-Einstellungen

Wenn ein `socket` geschlossen wird und der Puffer noch Daten enthält, entscheidet die untere Ebene des Betriebssystems basierend auf den Linger-Einstellungen, wie dies gehandhabt wird.

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0: Bei Schließen wird sofort zurückgegeben, die untere Ebene sendet die ungeschickten Daten ab und freistellt dann die Ressourcen, was eine elegante Abreise darstellt.
* l_onoff != 0, l_linger = 0: Bei Schließen wird sofort zurückgegeben, aber die ungeschickten Daten werden nicht gesendet, sondern der Socket-Deskriptor wird durch ein RST-Paket zwangsweise geschlossen, was eine zwangsweise Abreise darstellt.
* l_onoff != 0, l_linger > 0: Bei Schließen wird nicht sofort zurückgegeben, der Kern wartet eine Weile, und diese Zeit wird durch den Wert von l_linger bestimmt. Wenn die Timeoutzeit erreicht wird, bevor die ungeschickten Daten (einschließlich des FIN-Paket) gesendet und von der anderen Seite bestätigt wurden, wird das Schließen erfolgreich zurückgegeben, und der Socket-Deskriptor verlässt sich elegant. Andernfalls wird das Schließen direkt mit einem Fehlerwert zurückgegeben, die ungeschickten Daten gehen verloren, und der Socket-Deskriptor wird zwangsweise verlassen. Wenn der Socket-Deskriptor auf nicht blockierende Art eingestellt ist, wird das Schließen direkt mit einem Wert zurückgegeben.

## client has already been bound to another coroutine

Für eine TCP-Verbindung erlaubt Swoole unter der Basis nur, dass eine Coroutine lesend und eine Coroutine schreibend ist. Das bedeutet, dass es nicht möglich ist, mehrere Coroutinen gleichzeitig auf dieselbe TCP-Verbindung zu lesen/schreiben, da dies von der Basis eine Bindungsfehler auslöst:

```shell
Fatal error: Uncaught Swoole\Error: Socket#6 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed 
```

Neu modernisierte Code:

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

Lösungsreferenz: https://wenda.swoole.com/detail/107474

!> Diese Einschränkung gilt für alle multi-coroutinenumgebung, am häufigsten ist es, in [onReceive](/server/events?id=onreceive) und anderen Rückruffunktionen eine TCP-Verbindung gemeinsam zu nutzen, da solche Rückruffunktionen automatisch eine Coroutine erstellen.
Was tun, wenn man eine Verbindungspooling-Bedürfnis hat? Swoole bietet ein [Verbindungspooling](/coroutine/conn_pool), das direkt verwendet werden kann, oder man kann manually einen Pool aus Channels um die Verbindung zu verpacken.

## Call to undefined function Co\run()

Die meisten Beispiele in diesem Dokument verwenden `Co\run()` , um einen Coroutine-Container zu erstellen, [was ist ein Coroutine-Container?](/coroutine?id=was-ist-ein-coroutine-container)

Wenn Sie das folgende Fehler erhalten:

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

Es bedeutet, dass Ihre Swoole-Erweiterungversion niedriger als v4.4.0 ist oder dass Sie den [Coroutine-Shortname](/other/alias?id=coroutineschortname) manuell geschlossen haben. Die folgenden Lösungen bieten sich an:

* Wenn die Version zu niedrig ist, bitte aktualisieren Sie die Erweiterung auf `>= v4.4.0` oder verwenden Sie das `go`-Schlüsselwort, um stattdessen Coroutinen zu erstellen;
* Wenn der Coroutine-Shortname geschlossen wurde, bitte öffnen Sie den [Coroutine-Shortname](/other/alias?id=coroutineschortname);
* Verwenden Sie die [Coroutine::create](/coroutine/coroutine?id=create)-Methode, um stattdessen Coroutinen zu erstellen;
* Verwenden Sie den vollständigen Namen: `Swoole\Coroutine\run`;


## Kann man eine Redis- oder MySQL-Verbindung gemeinsam nutzen?

Absolut nicht. Jede Prozess muss separate Redis-, MySQL-, PDO-Verbindungen erstellen, und das gilt auch für andere Speicherkunden. Der Grund dafür ist, dass, wenn man eine Verbindung gemeinsam nutzt, die Rückkehrergebnisse nicht garantiert werden können, von welchem Prozess sie verarbeitet werden. Der Prozess, der die Verbindung hält, könnte theoretisch sowohl lesen als auch schreiben, was zu Datensalat führt.

**Daher darf man zwischen mehreren Prozessen keine Verbindungen gemeinsam nutzen**

* In [Swoole\Server](/server/init) sollte die Verbindung in der [onWorkerStart](/server/events?id=onworkerstart)-Rückruffunktion erstellt werden
* In [Swoole\Process](/process/process) sollte die Verbindung im Callback-Funktions Körper nach dem Starten des Prozesses in [Swoole\Process->start](/process/process?id=start) erstellt werden
* Diese Informationen sind ebenfalls für Programme, die `pcntl_fork` verwenden, relevant

Beispiel:

```php
$server = new Swoole\Server('0.0.0.0', 9502);

// Die Verbindung muss im onWorkerStart-Rückruffunktions Körper erstellt werden
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```


## Problem mit bereits geschlossener Verbindung

Wie im folgenden Hinweis angegeben

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

Das Serverseitige Antwortte, als der Client bereits die Verbindung geknüpft hatte

Gängige Fälle sind:

* Das Browser-Fenster wird wahnsinnig geladen (bevor es fertig geladen ist, wird es abgetrennt)
* Ein ApacheBench-Test wird zur Hälfte abgebrochen
* Ein wrk-Test basierend auf der Zeit (unvollendete Anforderungen werden abgebrochen, wenn die Zeit erreicht ist)

Alle diese Situationen sind normal und können ignoriert werden, daher ist der Fehlerlevel NOTICE

Wenn aus anderen Gründen unerwartet viele Verbindungen unterbrochen werden, ist dies etwas zu beachten

```bash
WARNING swWorker_discard_data (ERRNO 1007): [2] received the wrong data[21 bytes] from socket#75

WARNING Worker_discard_data (ERRNO 1007): [2] ignore data[5 bytes] received from session#2
```

Ähnlich dazu zeigt dieser Fehler auch an, dass die Verbindung bereits geschlossen wurde, und die empfangenen Daten werden verworfen. Referenz [discard_timeout_request](/server/setting?id=discard_timeout_request)


## connected Eigenschaft und Verbindungszustand ungleich

Seit der 4.x-Version der Coroutine-Bibliothek wird die `connected`-Eigenschaft nicht mehr rechtzeitig aktualisiert, und die Methode `[isConnect](/client?id=isconnected)` ist nicht mehr zuverlässig


### Grund

Das Ziel von Coroutinen ist es, dem synchron blockierenden Programmierungsmodell zu entsprechen, in dem das Konzept einer rechtzeitigen Verbindungszustandsaktualisierung nicht existiert, wie bei PDO, curl usw., die keine Verbindung haben und nur Fehler oder Ausnahmen zurückgeben, um eine Verbindung zu erkennen, wenn sie unterbrochen wird

Die allgemeine Praxis von Swoole ist es, bei einem IO-Fehler false (oder einen leeren Inhalt, der angibt, dass die Verbindung geschlossen wurde) zurückzugeben und entsprechende Fehlermuster und Fehlermeldungen auf dem Client-Objekt einzustellen

### Hinweis

Obwohl die vorherige asynchrone Version die "reale-zeit"-Aktualisierung der `connected`-Eigenschaft unterstützt hat, war sie tatsächlich nicht zuverlässig, und die Verbindung konnte nach der Überprüfung sofort geschlossen werden.

## Was ist mit "Connection refused" los?

Wenn beim Ausführen von `telnet 127.0.0.1 9501` ein "Connection refused"-Fehler auftritt, bedeutet dies, dass der Server diesen Port nicht überwacht.

* Überprüfen Sie, ob das Programm erfolgreich ausgeführt wurde: `ps aux`
* Überprüfen Sie, ob der Port überwacht wird: `netstat -lp`
* Überwachen Sie den Netzwerkverkehr mit `tcpdump traceroute`

## Resourcen vorübergehend nicht verfügbar [11]

Beim Empfang durch den Client `swoole_client` wird ein Fehler gemeldet:

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```

Dieser Fehler bedeutet, dass der Server innerhalb der festgelegten Zeit keine Daten zurückgegeben hat und die Empfangsbeschleunigung abgelaufen ist.

* Verwenden Sie `tcpdump`, um den Netzwerkverkehr zu überwachen und zu überprüfen, ob der Server Daten gesendet hat
* Der `send`-Funktion des Servers muss überprüft werden, ob sie true zurückgibt
* Bei externen Netzwerkverbindungen, die viel Zeit benötigen, sollte die Übertragungszeit für `swoole_client` erhöht werden

## Worker exit timeout, gezwungen, abgebrochen zu werden :id=forced-to-terminate

Ein Fehler wie folgt wurde gefunden:

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```

Dies bedeutet, dass der Worker innerhalb der vereinbarten Zeit ([max_wait_time](/server/setting?id=max_wait_time) Sekunden) nicht ausgeschieden ist und das Swoole-Unter底层 diesen Prozess zwangsweise beendet hat.

Der folgende Code kann zum Nachstellen verwendet werden:

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```

## Unmöglich, eine Rückruffunktion für das Signal Broken pipe: 13 zu finden

Ein Fehler wie folgt wurde gefunden:

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```

Dies bedeutet, dass Daten an eine bereits geschlossene Verbindung gesendet wurden, was in der Regel daran liegt, dass die Rückgabe nicht überprüft wurde und der Sendeprozess weiterhin durchgeführt wird, auch wenn die Rückgabe fehlgeschlagen ist.

## Welche Grundlagen muss man für das Lernen von Swoole beherrschen?

### Mehrprozess/Mehrthread

* Verstehen Sie die Konzepte von Prozessen und Threads im `Linux`-Betriebssystem
* Verstehen Sie die grundlegenden Kenntnisse über den Prozess-/Thread-Wechsel und die Scheduling-Technologie im `Linux`
* Verstehen Sie die Grundkenntnisse der Prozess间通信, wie Pipes, `UnixSocket`, Message Queues, Shared Memory

### SOCKET

* Verstehen Sie die grundlegenden Operationen von `SOCKET` wie `accept/connect`, `send/recv`, `close`, `listen`, `bind`
* Verstehen Sie Konzepte wie Empfangs- und Sende-Buffern, Blockierung/Nicht-Blockierung, Timeouts usw. von `SOCKET`

### IO-Multippling

* Verstehen Sie `select`/`poll`/`epoll`
* Verstehen Sie Ereigniskreise, die auf `select`/`epoll` basieren, und das `Reactor`-Modell
* Verstehen Sie Lesevorgänge und Schreibvorgänge

### TCP/IP-Netzwerkprotokoll

* Verstehen Sie das `TCP/IP`-Protokoll
* Verstehen Sie die Übertragungsprotokolle `TCP` und `UDP`

### Debugging-Tools

* Verwenden Sie [gdb](/other/tools?id=gdb), um `Linux`-Programme zu debuggen
* Verwenden Sie [strace](/other/tools?id=strace), um die Systemaufrufe von Prozessen zu verfolgen
* Verwenden Sie [tcpdump](/other/tools?id=tcpdump), um den Netzwerkverkehr zu verfolgen
* Andere `Linux`-Systemwerkzeuge wie ps, [lsof](/other/tools?id=lsof), top, vmstat, netstat, sar, ss usw.

## Objekt der Klasse Swoole\Curl\Handler konnte nicht in int umgewandelt werden

Beim Verwenden von [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) tritt ein Fehler auf:

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```

Der Grund ist, dass das hookte curl nicht mehr ein resource-Typ ist, sondern ein object-Typ, daher kann es nicht in einen int-Typ umgewandelt werden.

!> Das Problem mit `int` wird empfohlen, vom SDK-Unterstützer gelöst zu werden, indem der Code geändert wird. In PHP8 ist curl nicht mehr ein resource-Typ, sondern ein object-Typ.

Es gibt drei Lösungsansätze:

1. Schalten Sie [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) nicht ein. Ab der Version [v4.5.4](/version/log?id=v454) ist [SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all) standardmäßig mit [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) verbunden, Sie können es durch Festlegen von `SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL` ausschalten.

2. Verwenden Sie das Guzzle SDK, um den Handler zu ersetzen und die Konnektivität zu koroutine-化

3. Ab der Swoole-Version `v4.6.0` kann [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl) verwendet werden, um [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) zu ersetzen

## Während der Verwendung beider One-Click-Konnektivität und Guzzle 7.0+ werden die Ergebnisse direkt im Terminal ausgegeben :id=hook_guzzle

Der Nachstellungscode ist wie folgt:

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// Versions vor v4.5.4
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// Die Request-Ergebnisse werden direkt ausgegeben, anstatt gedruckt zu werden
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> Die Lösung ist identisch mit dem vorherigen Problem. Allerdings wurde dieses Problem in Swoole-Versionen >= `v4.5.8` behoben.

## Fehler: Keine Bufferkapazität verfügbar [55]

Dieser Fehler kann ignoriert werden. Dieser Fehler tritt auf, wenn die Option `[socket_buffer_size](/server/setting?id=socket_buffer_size)` zu groß ist und einige Systeme sie nicht akzeptieren, was das Programm nicht beeinträchtigt.

## Maximale Größe für GET/POST-Anfragen

### Maximale Größe für GET-Anfragen beträgt 8192

GET-Anfragen haben nur einen Http-Header, und das Swoole-Unter底层 verwendet eine festgelegte Größe von 8K für die Memory-Cache, die nicht geändert werden kann. Wenn die Anfrage keine korrekte Http-Anfrage ist, wird ein Fehler aufgetreten. Das Unter底层 wirft den folgenden Fehler aus:

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```

### POST-Dateian upload

Die maximale Größe ist durch die Konfigurationsoption `[package_max_length](/server/setting?id=package_max_length)` begrenzt, die standardmäßig bei 2M liegt. Sie können die Größe ändern, indem Sie `[Server->set](/server/methods?id=set)` mit einem neuen Wert übergeben. Da das Swoole-Unter底层 vollständig aus Memory besteht, kann eine zu große Einstellung dazu führen, dass eine große Anzahl von parallelen Anfragen die Serverressourcen erschöpft.

Berechnungsmethode: `maximale Memory-Nutzung` = `maximale Anzahl paralleler Anfragen` * `package_max_length`
