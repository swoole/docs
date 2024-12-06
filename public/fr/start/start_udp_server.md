# Serveur UDP

## Code du programme

Veuillez écrire le code suivant dans udpServer.php.

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

// Écouter l'événement de réception de données.
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Server : {$data}");
});

// Démarrer le serveur
$server->start();
```

Le serveur UDP, contrairement au serveur TCP, ne connaît pas le concept de connexion. Après le démarrage du serveur, les clients n'ont pas besoin de se connecter, ils peuvent envoyer des paquets directement au port 9502 sur lequel le serveur est en attente. L'événement correspondant est `onPacket`.

* `$clientInfo` contient des informations sur le client, c'est un tableau qui comprend l'IP et le port du client.
* Appeler la méthode `$server->sendto` pour envoyer des données au client.
!> Par défaut, Docker utilise le protocole TCP pour la communication, mais si vous devez utiliser le protocole UDP, vous devez configurer les réseaux Docker pour y parvenir.
```shell
docker run -p 9502:9502/udp <image-name>
```

## Démarrer le service

```shell
php udpServer.php
```

Pour tester le serveur UDP, vous pouvez utiliser `netcat -u`.

```shell
netcat -u 127.0.0.1 9502
hello
Server: hello
```
