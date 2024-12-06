# Écoute de plusieurs ports

L'objet `Swoole\Server` peut écouter plusieurs ports, chaque port pouvant avoir une manière de traiter différents protocoles, par exemple le port 80 traite le protocole HTTP, et le port 9507 traite le protocole TCP. L'chiffrement de la transmission `SSL/TLS` peut également être activé uniquement pour des ports spécifiques.

!> Par exemple, si le serveur principal utilise le protocole WebSocket ou HTTP, un nouveau port TCP (valeur de retour de la méthode [listen](/server/methods?id=listen), c'est-à-dire l'objet [Swoole\Server\Port](server/server_port.md), ici dénommé port) héritera par défaut des paramètres de protocole du serveur principal. Il est nécessaire d'appeler séparément la méthode `set` de l'objet port et la méthode `on` pour activer un nouveau protocole.

## Écoute d'un nouveau port

```php
// Renvoie un objet port
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```

## Paramètres de réseau

```php
// Appel de la méthode set de l'objet port
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

## Paramètres de rappel

```php
// Paramètres de rappel pour chaque port
$port1->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
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

## HTTP/WebSocket

L'objet `Swoole\Http\Server` et l'objet `Swoole\WebSocket\Server` sont implémentés en utilisant des sous-classes, donc il n'est pas possible de créer un serveur HTTP ou WebSocket en appelant la méthode `listen` sur une instance de `Swoole\Server`.

Si le serveur a pour fonction principale le `RPC`, mais qu'il est souhaité fournir une interface de gestion Web simple. Dans ce cas, on peut d'abord créer un serveur HTTP/WebSocket, puis écouter un port TCP native pour fournir un service TCP.

### Exemple

```php
$http_server = new Swoole\Http\Server('0.0.0.0',9998);
$http_server->set(['daemonize'=> false]);
$http_server->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

// Écoute multiple d'un port TCP, ouvre un service TCP à l'extérieur, et configure le rappel du serveur TCP
$tcp_server = $http_server->listen('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
// Par défaut, le nouveau port écouté 9999 héritera des paramètres du serveur principal, qui est également le protocole HTTP
// Il est nécessaire d'appeler la méthode set pour surmonter les paramètres du serveur principal
$tcp_server->set([]);
$tcp_server->on('receive', function ($server, $fd, $threadId, $data) {
    echo $data;
});

$http_server->start();
```

Avec ce code, il est possible de créer un serveur qui offre à la fois un service HTTP et un service TCP. Les combinaisons de codes plus spécifiques sont à votre disposition pour l'implémenter.

## Paramètres de configuration composite pour plusieurs ports TCP, HTTP, WebSocket

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_websocket_protocol' => true, // Permet d'activer le protocole WebSocket pour ce port
]);
```

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_http_protocol' => false, // Désactive la fonction de protocole HTTP pour ce port
]);
```

De même, il y a : `open_http_protocol`, `open_http2_protocol`, `open_mqtt_protocol`, etc.

## Paramètres optionnels

* Si le port d'écoute `port` n'a pas appelé la méthode `set`, le port qui définit les options de traitement du protocole héritera des paramètres de configuration du serveur principal.
* Si le serveur principal est un serveur HTTP/WebSocket et que aucun paramètre de protocole n'est défini, le port écouté sera toujours configuré pour le protocole HTTP ou WebSocket et ne mettra pas en œuvre le rappel [onReceive](/server/events?id=onreceive) pour le port.
* Si le serveur principal est un serveur HTTP/WebSocket et que le port écouté appelle la méthode `set` pour configurer les paramètres, cela effacera les paramètres de protocole du serveur principal. Le port écouté deviendra un protocole TCP. Si le port écouté souhaite toujours utiliser le protocole HTTP/WebSocket, il est nécessaire d'ajouter dans la configuration `open_http_protocol => true` et `open_websocket_protocol => true`.

**Les paramètres que `port` peut définir avec `set` incluent :**

* Paramètres de socket : comme `backlog`, `open_tcp_keepalive`, `open_tcp_nodelay`, `tcp_defer_accept`, etc.
* Paramètres liés au protocole : comme `open_length_check`, `open_eof_check`, `package_length_type`, etc.
* Paramètres liés aux certificats SSL : comme `ssl_cert_file`, `ssl_key_file`, etc.

Pour plus de détails, veuillez consulter la [section des configurations](/server/setting).

## Rappel optionnel

Si le port d'écoute `port` n'a pas appelé la méthode `on`, le port qui définit les rappels de fonction héritera des rappels du serveur principal. Les rappels que `port` peut définir avec la méthode `on` incluent :

### Serveur TCP

* onConnect
* onClose
* onReceive

### Serveur UDP

* onPacket
* onReceive

### Serveur HTTP

* onRequest

### Serveur WebSocket

* onMessage
* onOpen
* onHandshake

!> Les rappels de fonction pour différents ports d'écoute s'exécutent toujours dans le même espace de processus `Worker`.

## Itération des connexions sous plusieurs ports

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9514, SWOOLE_BASE);

$tcp = $server->listen("0.0.0.0", 9515, SWOOLE_SOCK_TCP);
$tcp->set([]);

$server->on("open", function ($serv, $req) {
    echo "nouvel client WebSocket, fd={$req->fd}\n";
});

$server->on("message", function ($serv, $frame) {
    echo "réception de la part de {$frame->fd}:{$frame->data}, opcode:{$frame->opcode}, fin:{$frame->finish}\n";
    $serv->push($frame->fd, "ceci est le serveur onMessage");
});

$tcp->on('receive', function ($server, $fd, $reactor_id, $data) {
    // Itère uniquement les connexions du port 9514, car c'est avec $server que nous travaillons, pas avec $tcp
    $websocket = $server->ports[0];
    foreach ($websocket->connections as $_fd) {
        var_dump($_fd);
        if ($server->exist($_fd)) {
            $server->push($_fd, "ceci est le serveur onReceive");
        }
    }
    $server->send($fd, 'réception: '.$data);
});

$server->start();
```
