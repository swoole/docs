# Serveur WebSocket


## Code du programme

Veuillez écrire le code suivant dans websocketServer.php.

```php
// Créez un objet Serveur WebSocket, écoutant sur le port 9502 de 0.0.0.0.
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

// Écoutez l'événement d'ouverture de la connexion WebSocket.
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "bonjour, bienvenue\n");
});

// Écoutez l'événement de message WebSocket.
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "serveur: {$frame->data}");
});

// Écoutez l'événement de fermeture de la connexion WebSocket.
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} est fermé\n";
});

$ws->start();
```

* Lorsque le client envoie des informations au serveur, l'événement `onMessage` est déclenché sur le côté serveur.
* Le serveur peut appeler `$server->push()` pour envoyer un message à un client spécifique (identifié par `$fd`).


## Exécution du programme

```shell
php websocketServer.php
```

Vous pouvez utiliser le navigateur Chrome pour tester, avec le code JavaScript suivant :

```javascript
var wsServer = 'ws://127.0.0.1:9502';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
	console.log("Connexion établie avec le serveur WebSocket.");
};

websocket.onclose = function (evt) {
	console.log("Déconnexion");
};

websocket.onmessage = function (evt) {
	console.log('Données récupérées du serveur: ' + evt.data);
};

websocket.onerror = function (evt, e) {
	console.log('Erreur survenue: ' + evt.data);
};
```

## Comet

Outre les fonctionnalités WebSocket, le serveur WebSocket peut également gérer les connexions HTTP à long terme, ce qui correspond au schéma Comet. Il suffit d'ajouter une écoute de l'événement [onRequest](/http_server?id=on) pour mettre en œuvre la longue request HTTP Comet.

!> Pour une utilisation détaillée, veuillez consulter [Swoole\WebSocket](/websocket_server).
