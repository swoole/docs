# Serveur (style coroutine) <!-- {docsify-ignore-all} -->

Le `Swoole\Coroutine\Server` se distingue du serveur à style asynchrone [décrit ici](/server/init) en ce qu'il est entièrement réalisé avec des coroutines, voir [exemple complet](/coroutine/server?id=exemple-complet).

## Avantages :

- Pas besoin de configurer de fonctions de rappel pour les événements. L'établissement de connexions, la réception de données, l'envoi de données et la fermeture des connexions se font de manière séquentielle, sans les problèmes de concurrency de l'style asynchrone [décrit ici](/server/init), par exemple :

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

// Écouter l'événement de connexion
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1", 6379); // La coroutine ici va suspendre
    Co::sleep(5); // Ici, sleep simule un ralentissement de la connexion
    $redis->set($fd, "fd $fd connecté");
});

// Écouter l'événement de réception de données
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1", 6379); // La coroutine ici va suspendre
    var_dump($redis->get($fd)); // Il est possible que la coroutine de onReceive établisse la connexion Redis en premier, et que le set ci-dessus n'ait pas encore été exécuté, ce qui ferait que get serait false et créeraient une erreur logique
});

// Écouter l'événement de fermeture de connexion
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Déclencher le serveur
$serv->start();
```

Le serveur à style asynchrone mentionné ci-dessus ne peut pas garantir l'ordre des événements, c'est-à-dire qu'il n'est pas possible d'assurer que `onConnect` se termine avant que `onReceive` ne commence, car après avoir activé les coroutines, les rappels `onConnect` et `onReceive` créent automatiquement des coroutines. Lorsqu'ils rencontrent un I/O, ils génèrent une [调度 de coroutines](/coroutine?id=dispatch-de-coroutines), et l'asynchrone ne peut pas garantir l'ordre du dispatch, tandis que le serveur au style coroutine n'a pas ce problème.

- Il est possible d'activer et de désactiver dynamiquement le service. Une fois que le serveur asynchrone est démarré avec la méthode `start()`, il ne peut plus rien faire, tandis que le serveur au style coroutine peut être activé et désactivé dynamiquement.

## Désavantages :

- Le serveur au style coroutine ne crée pas automatiquement plusieurs processus et doit être utilisé en combinaison avec le module [Process\Pool](/process/process_pool) pour tirer parti des multiples cœurs.
- Le serveur au style coroutine est en fait une encapsulation du module [Co\Socket](/coroutine_client/socket), donc pour utiliser le style coroutine, il est nécessaire d'avoir une certaine expérience en programmation socket.
- Actuellement, l'encapsulation n'est pas aussi élevée que celle du serveur asynchrone, certaines choses doivent être réalisées manuellement, par exemple, la fonction `reload` nécessite de surveiller les signaux pour mettre en œuvre la logique.
