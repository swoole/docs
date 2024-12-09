# Mehrport-Überwachung

`Swoole\Server` kann mehrere Ports überwachen, und jeder Port kann auf unterschiedliche Protokollverarbeitungsweisen eingerichtet werden, zum Beispiel Port 80 für HTTP-Protokolle und Port 9507 für TCP-Protokolle. Auch die Übertragungssicherheit mit `SSL/TLS` kann nur für spezifische Ports aktiviert werden.

!> Wenn zum Beispiel der Hauptserver ein WebSocket- oder HTTP-Protokoll verwendet, werden neu überwachte TCP-Ports (die Rückkehrwerte des [listen](/server/methods?id=listen)-Verfahrens, also [Swoole\Server\Port](server/server_port.md)-Objekte, hier als Port bezeichnet) standardmäßig die Protokolle des Hauptservers übernehmen. Um ein neues Protokoll zu aktivieren, muss der `Port`-Objekt separat die `set`-Methode und die `on`-Methode aufgerufen werden.

## Neue Ports überwachen

```php
// Gibt ein Port-Objekt zurück
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```

## Netzwerkprotokolle einrichten

```php
// Aufrufen der set-Methode des Port-Objekts
$port1->set([
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0,
    'package_max_length' => 800000,
]);

$port3->set([
    'open_eof_split' => true,
    'package_eof' => "\r\n",
    'ssl_cert_file' => 'ssl.cert',
    'ssl_key_file' => 'ssl.key',
]);
```

## Rückruffunktionen einrichten

```php
// Rückruffunktionen für jeden Port einrichten
$port1->on('connect', function ($serv, $fd){
    echo "Client: Connect.\n";
});

$port1->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});

$port1->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$port2->on('packet', function ($serv, $data, $addr) {
    var_dump($data, $addr);
});
```

## Http/WebSocket

Da `Swoole\Http\Server` und `Swoole\WebSocket\Server` durch die Verwendung von abgeleiteten Klassen implementiert werden, können keine HTTP- oder WebSocket-Server durch das Aufrufen der `listen`-Methode einer `Swoole\Server`-Instanz 创建 werden.

Wenn zum Beispiel die Hauptfunktion des Servers `RPC` ist, aber eine einfache Web-Verwaltungsschnittstelle bereitgestellt werden soll, kann in solchen Szenarien zuerst ein HTTP/WebSocket-Server erstellt und dann ein natürlicher TCP-Port überwacht werden.

### Beispiel

```php
$http_server = new Swoole\Http\Server('0.0.0.0',9998);
$http_server->set(['daemonize'=> false]);
$http_server->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

// Mehrere TCP-Ports überwachen, externen TCP-Dienst bereitstellen und TCP-Server-Rückruf einrichten
$tcp_server = $http_server->listen('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
// Standardmäßig wird der neu überwachte Port 9999 die Einstellungen des Hauptservers übernehmen, die auch HTTP-Protokoll sind
// Um die Einstellungen des Hauptservers zu überschreiben, muss die set-Methode aufgerufen werden
$tcp_server->set([]);
$tcp_server->on('receive', function ($server, $fd, $threadId, $data) {
    echo $data;
});

$http_server->start();
```

Mit diesem Code kann ein Server eingerichtet werden, der sowohl HTTP-Dienste als auch TCP-Dienste anbietet. Die spezifischere elegante Code-Kombination wird von dir selbst umgesetzt.

## Zusammensetzung von mehreren Protokollen für TCP, HTTP, WebSocket-Ports

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_websocket_protocol' => true, // Legt fest, dass dieser Port das WebSocket-Protokoll unterstützt
]);
```

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_http_protocol' => false, // Legt fest, dass dieser Port die HTTP-Protokollfunktion deaktiviert
]);
```

Es gibt auch: `open_http_protocol`, `open_http2_protocol`, `open_mqtt_protocol` und andere Parameter

## Zusätzliche Optionen

* Wenn der überwachte Port `port` die `set`-Methode nicht aufgerufen hat, werden die für die Protokollverarbeitungoptionen eingestellten Ports die entsprechenden Einstellungen des Hauptservers übernehmen
* Wenn der Hauptserver ein HTTP/WebSocket-Server ist und keine Protokollparameter festgelegt sind, werden die überwachten Ports standardmäßig als HTTP- oder WebSocket-Protokolle eingerichtet und es wird der für den Port festgelegte [onReceive](/server/events?id=onreceive)-Rückruf nicht ausgeführt
* Wenn der Hauptserver ein HTTP/WebSocket-Server ist und der überwachte Port die `set`-Methode aufgerufen hat, um Einstellungen zu konfigurieren, werden die Protokolle des Hauptservers gelöscht. Der überwachte Port wird zu einem TCP-Protokoll. Wenn der überwachte Port weiterhin das HTTP/WebSocket-Protokoll verwenden möchte, müssen im Konfigurieren `open_http_protocol => true` und `open_websocket_protocol => true` hinzugefügt werden

**Die durch `set` für `port` einstellbaren Parameter umfassen:**

* Socket-Parameter: wie `backlog`, `open_tcp_keepalive`, `open_tcp_nodelay`, `tcp_defer_accept` usw.
* Protokollbezogene Parameter: wie `open_length_check`, `open_eof_check`, `package_length_type` usw.
* SSL-Zertifikatsparameter: wie `ssl_cert_file`, `ssl_key_file` usw.

Für spezifische Referenzen siehe [Konfigurationsabschnitt](/server/setting)

## Zusätzliche Rückruffunktionen

Wenn der `port` die `on`-Methode nicht aufgerufen hat, werden für den überwachten Port standardmäßig die Rückruffunktionen des Hauptservers verwendet. Die durch `on` für `port` einstellbaren Rückruffunktionen umfassen:

### TCP-Server

* onConnect
* onClose
* onReceive

### UDP-Server

* onPacket
* onReceive

### HTTP-Server

* onRequest

### WebSocket-Server

* onMessage
* onOpen
* onHandshake

!> Die Rückruffunktionen für verschiedene überwachte Ports werden innerhalb desselben `Worker` -Prozessraums ausgeführt

## Durchführung von Verbindungen unter mehreren Ports

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9514, SWOOLE_BASE);

$tcp = $server->listen("0.0.0.0", 9515, SWOOLE_SOCK_TCP);
$tcp->set([]);

$server->on("open", function ($serv, $req) {
    echo "new WebSocket Client, fd={$req->fd}\n";
});

$server->on("message", function ($serv, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $serv->push($frame->fd, "this is server OnMessage");
});

$tcp->on('receive', function ($server, $fd, $reactor_id, $data) {
    // Nur die Verbindungen des Ports 9514 durchlaufen, da es sich um $server handelt und nicht um $tcp
    $websocket = $server->ports[0];
    foreach ($websocket->connections as $_fd) {
        var_dump($_fd);
        if ($server->exist($_fd)) {
            $server->push($_fd, "this is server onReceive");
        }
    }
    $server->send($fd, 'receive: '.$data);
});

$server->start();
```
