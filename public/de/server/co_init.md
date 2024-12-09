# Server (Coroutine Style) <!-- {docsify-ignore-all} -->

Der Unterschied zwischen `Swoole\Coroutine\Server` und dem asynchronen Stil des Servers ([/server/init](/server/init)) ist, dass `Swoole\Coroutine\Server` ein vollständig koordiniertes Server implementiert ist, siehe [vollständiges Beispiel](/coroutine/server?id=vollständiges-beispiel).

## Vorteile:

- Es ist nicht erforderlich, Ereignis-Callback-Funktionen einzurichten. Das Herstellen von Verbindungen, Empfang von Daten, Senden von Daten und Schließen von Verbindungen sind sequenziell, ohne die并发-Probleme des asynchronen Stils ([/server/init](/server/init)), zum Beispiel:

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

// Warten auf das Connect-Ereignis
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);// Die Co-Kürzel in OnConnect wird hängen bleiben
    Co::sleep(5);// Hier wird sleep verwendet, um ein langsames Connect-Szenario zu simulieren
    $redis->set($fd,"fd $fd connected");
});

// Warten auf das Receive-Ereignis
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);// Die Co-Kürzel in onReceive wird hängen bleiben
    var_dump($redis->get($fd));// Es ist möglich, dass die Redis-Verbindung von onReceive zuerst eingerichtet wurde, bevor der oben genannte set ausgeführt wurde, und der get hier false zurückgeben könnte, was zu einem logischen Fehler führt
});

// Warten auf das Close-Ereignis
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Starten des Servers
$serv->start();
```

Der oben genannte asynchrone Server kann nicht die Reihenfolge der Ereignisse gewährleisten, das heißt, es kann nicht garantiert werden, dass `onConnect` erst nach Abschluss von `onReceive` durchgeführt wird. Da nach dem Aktivieren der Koordination, sowohl `onConnect` als auch `onReceive` Callbacks automatisch eine Koordination erstellen, wird bei IO eine [Koordination-Planung](/coroutine?id=koordination-planung) durchgeführt, und der asynchrone Stil kann die Planungreihenfolge nicht gewährleisten, während der koordinierten Stil des Servers dieses Problem nicht hat.

- Der koordinierten Stil des Servers kann den Service dynamisch starten und stoppen, während der asynchrone Stil des Servers nach dem Aufrufen von `start()` nichts mehr tut.

## Nachteile:

- Der koordinierten Stil des Servers wird nicht automatisch mehrere Prozesse erstellen, er muss mit dem [Process\Pool](/process/process_pool)-Modul verwendet werden, um die Nutzung von mehreren Kernen zu nutzen.
- Der koordinierten Stil des Servers ist tatsächlich eine Verpackung des [Co\Socket](/coroutine_client/socket)-Moduls, daher ist Erfahrung im Socket-Programmieren erforderlich, wenn man den koordinierten Stil verwendet.
- Derzeit ist die Verpackungsstufe des koordinierten Stils des Servers nicht so hoch wie die des asynchronen Stils des Servers, einige Dinge müssen selbst implementiert werden, zum Beispiel muss die `reload`-Funktion selbst mit dem Empfang von Signalen überwacht werden, um die Logik durchzuführen.
