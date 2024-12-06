# Redis\Server

Une classe `Server` compatible avec le protocole du serveur `Redis`, permettant de réaliser un programme de serveur basé sur ce protocole.

?> La classe `Swoole\Redis\Server` hérite de [Server](/server/tcp_init), donc toutes les API et configurations offertes par `Server` peuvent être utilisées, et le modèle de processus est également identique. Veuillez consulter la section [Server](/server/init).

* **Clients disponibles**

  * Tous les clients `redis` de n'importe quel langage de programmation, y compris l'extension `redis` pour PHP et la bibliothèque `phpredis`
  * Le client coroutine [Swoole\Coroutine\Redis](/coroutine_client/redis)
  * L'outil de ligne de commande `Redis`, y compris `redis-cli` et `redis-benchmark`


## Méthodes

La classe `Swoole\Redis\Server` hérite de `Swoole\Server`, et peut utiliser toutes les méthodes fournies par la classe parent.


### setHandler

?> **Définir le gestionnaire pour la commande Redis.**

!> La classe `Redis\Server` n'a pas besoin de définir un callback pour [onReceive](/server/events?id=onreceive). Il suffit d'utiliser la méthode `setHandler` pour configurer la fonction de traitement de la commande correspondante. Lorsqu'une commande non prise en charge est reçue, une réponse `ERROR` est automatiquement envoyée au client, avec le message `ERR unknown command '$command'`.

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

* **Paramètres** 

  * **`string $command`**
    * **Fonction** : Nom de la commande
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`callable $callback`**
    * **Fonction** : Function de traitement de la commande [La fonction de retour doit être de type chaîne pour être automatiquement envoyée au client]
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

    !> Les données de retour doivent être au format `Redis`, et peuvent être encapsulées en utilisant la méthode statique `format`


### format

?> **Format les données de réponse de la commande.**

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **Paramètres** 

  * **`int $type`**
    * **Fonction** : Type de données, voir les constantes suivantes pour les paramètres de format [Constantes de paramètres de format](/redis_server?id=format_parameters_constants).
    * **Valeur par défaut** : None
    * **Autres valeurs** : None
    
    !> Lorsque `$type` est de type `NIL`, il n'est pas nécessaire de transmettre de `$value` ; pour `ERROR` et `STATUS`, `$value` est optionnel ; pour `INT`, `STRING`, `SET`, `MAP`, il est obligatoire.

  * **`mixed $value`**
    * **Fonction** : Valeur
    * **Valeur par défaut** : None
    * **Autres valeurs** : None


### send

?> **Utiliser la méthode `send()` de [Swoole\Server](/server/methods?id=send) pour envoyer des données au client.**

```php
Swoole\Server->send(int $fd, string $data): bool
```


## Constantes


### Constantes de paramètres de format

Utilisées principalement par la fonction `format` pour emballer les données de réponse `Redis`


Constante | Description
---|---
Server::NIL | Retourne une donnée nil
Server::ERROR | Retourne un code d'erreur
Server::STATUS | Retourne un statut
Server::INT | Retourne un entier, la fonction `format` doit transmettre une valeur et le type doit être un entier
Server::STRING | Retourne une chaîne, la fonction `format` doit transmettre une valeur et le type doit être une chaîne
Server::SET | Retourne une liste, la fonction `format` doit transmettre une valeur et le type doit être un tableau
Server::MAP | Retourne une Map, la fonction `format` doit transmettre une valeur et le type doit être un tableau d'index associatif


## Exemple d'utilisation


### Serveur

```php
use Swoole\Redis\Server;

define('DB_FILE', __DIR__ . '/db');

$server = new Server("127.0.0.1", 9501, SWOOLE_BASE);

if (is_file(DB_FILE)) {
    $server->data = unserialize(file_get_contents(DB_FILE));
} else {
    $server->data = array();
}

$server->setHandler('GET', function ($fd, $data) use ($server) {
    if (count($data) == 0) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'GET' command"));
    }

    $key = $data[0];
    if (empty($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    } else {
        return $server->send($fd, Server::format(Server::STRING, $server->data[$key]));
    }
});

$server->setHandler('SET', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'SET' command"));
    }

    $key = $data[0];
    $server->data[$key] = $data[1];
    return $server->send($fd, Server::format(Server::STATUS, "OK"));
});

$server->setHandler('sAdd', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sAdd' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }

    $count = 0;
    for ($i = 1; $i < count($data); $i++) {
        $value = $data[$i];
        if (!isset($server->data[$key][$value])) {
            $server->data[$key][$value] = 1;
            $count++;
        }
    }

    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('sMembers', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sMembers' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::SET, array_keys($server->data[$key])));
});

$server->setHandler('hSet', function ($fd, $data) use ($server) {
    if (count($data) < 3) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hSet' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }
    $field = $data[1];
    $value = $data[2];
    $count = !isset($server->data[$key][$field]) ? 1 : 0;
    $server->data[$key][$field] = $value;
    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('hGetAll', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hGetAll' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::MAP, $server->data[$key]));
});

$server->on('WorkerStart', function ($server) {
    $server->tick(10000, function () use ($server) {
        file_put_contents(DB_FILE, serialize($server->data));
    });
});

$server->start();
```

### Client

```shell
$ redis-cli -h 127.0.0.1 -p 9501
127.0.0.1:9501> set name swoole
OK
127.0.0.1:9501> get name
"swoole"
127.0.0.1:9501> sadd swooler rango
(integer) 1
127.0.0.1:9501> sadd swooler twosee guoxinhua
(integer) 2
127.0.0.1:9501> smembers swooler
1) "rango"
2) "twosee"
3) "guoxinhua"
127.0.0.1:9501> hset website swoole "www.swoole.com"
(integer) 1
127.0.0.1:9501> hset website swoole "swoole.com"
(integer) 0
127.0.0.1:9501> hgetall website
1) "swoole"
2) "swoole.com"
127.0.0.1:9501> test
(error) ERR unknown command 'test'
127.0.0.1:9501>
```
