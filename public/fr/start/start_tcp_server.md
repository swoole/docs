# Serveur TCP


## Code du programme

Veuillez écrire le code suivant dans tcpServer.php.

```php
// Crée un objet Server qui écoute sur le port 9501 de l'adresse locale 127.0.0.1.
$server = new Swoole\Server('127.0.0.1', 9501);

// Écoute l'événement de connexion.
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// Écoute l'événement de réception de données.
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// Écoute l'événement de fermeture de connexion.
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// Démarre le serveur
$server->start(); 
```

Ainsi, un serveur `TCP` est créé qui écoute sur le port local `9501`. Sa logique est très simple : lorsque le client `Socket` envoie une chaîne `hello` via le réseau, le serveur répond par la chaîne `Server: hello`.

Le `Server` est un serveur asynchrone, c'est pourquoi le programme est écrit en écoutant les événements. Lorsque l'événement correspondant se produit, la fonction spécifiée est appelée automatiquement. Par exemple, lorsque de nouvelles connexions `TCP` sont établies, l'événement [onConnect](/server/events?id=onconnect) est appelé, et lorsque une connexion envoie des données au serveur, la fonction [onReceive](/server/events?id=onreceive) est appelée.

* Le serveur peut être connecté en même temps par des milliers de clients, et `$fd` est l'identifiant unique de la connexion client.
* Appeler la méthode `$server->send()` pour envoyer des données à la connexion client, l'argument est l'identifiant client `$fd`.
* Appeler la méthode `$server->close()` pour forcer la fermeture d'une connexion client.
* Le client peut se déconnecter de sa propre initiative, ce qui déclenche l'événement [onClose](/server/events?id=onclose).


## Exécution du programme

```shell
php tcpServer.php
```

Lancez le programme `server.php` depuis la ligne de commande, et après un démarrage réussi, vous pouvez utiliser la commande `netstat` pour voir si le serveur écoute déjà sur le port `9501`.

À ce moment-là, vous pouvez utiliser des outils tels que `telnet/netcat` pour vous connecter au serveur.

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```


## Méthode simple pour vérifier l'impossibilité de se connecter au serveur

* Sous `Linux`, utilisez `netstat -an | grep port` pour voir si le port est déjà ouvert et en état d'écoute.
* Après avoir confirmé le précédent point, vérifiez également les problèmes de pare-feu.
* Notez l'adresse IP utilisée par le serveur, si c'est l'adresse locale `127.0.0.1`, alors le client ne peut se connecter que using `127.0.0.1`.
* Si vous utilisez des services Alibaba Cloud ou Tencent, vous devez configurer les ports de développement dans le groupe de sécurité.

## Question de la frontière des paquets TCP.

Consultez [Question de la frontière des paquets TCP](/learn?id=tcp数据包边界问题).
