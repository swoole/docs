# Client asynchrone <!-- {docsify-ignore-all} -->

Les clients asynchrones suivants sont des classes intégrées à Swoole, ceux marqués par ⚠️ ne sont plus recommandés pour utilisation et peuvent être remplacés par les fonctions natives PHP + [la synchronisation des asynchrones](/runtime).

* [Client TCP/UDP/UnixSocket](coroutine_client/client.md)
* [Client Socket](coroutine_client/socket.md)
* [Client HTTP/WebSocket](coroutine_client/http_client.md)
* [Client HTTP2](coroutine_client/http2_client.md)
* [Client PostgreSQL](coroutine_client/postgresql.md)
* [Client FastCGI](coroutine_client/fastcgi.md)
⚠️ [Client Redis](coroutine_client/redis.md)
⚠️ [Client MySQL](coroutine_client/mysql.md)
* [API System](/coroutine/system)


## Règles de timeout

Tous les demandes réseau (établissement de connexion, envoi de données, réception de données) peuvent faire l'objet d'un timeout. Il existe trois façons de configurer le timeout pour les clients asynchrones Swoole :

1. Passer le temps de timeout en tant qu'argument de la méthode, par exemple [Co\Client->connect()](/coroutine_client/client?id=connect), [Co\Http\Client->recv()](/coroutine_client/http_client?id=recv), [Co\MySQL->query()](/coroutine_client/mysql?id=query), etc.

!> Cette méthode a l'impact le plus limité (elle s'applique uniquement à la fonction actuelle), et a la priorité la plus élevée (la fonction actuelle ignorera les configurations suivantes `2` et `3`).

2. Utiliser la méthode `set()` ou `setOption()` de la classe client asynchrone Swoole pour configurer le timeout, par exemple :

```php
$client = new Co\Client(SWOOLE_SOCK_TCP);
// ou
$client = new Co\Http\Client("127.0.0.1", 80);
// ou
$client = new Co\Http2\Client("127.0.0.1", 443, true);
$client->set(array(
    'timeout' => 0.5, // timeout total, y compris connexion, envoi, réception de tous les timeouts
    'connect_timeout' => 1.0, // timeout de connexion, il couvre le premier timeout total
    'write_timeout' => 10.0, // timeout d'envoi, il couvre le premier timeout total
    'read_timeout' => 0.5, // timeout de réception, il couvre le premier timeout total
));

// Co\Redis() n'a pas de configuration pour write_timeout et read_timeout
$client = new Co\Redis();
$client->setOption(array(
    'timeout' => 1.0, // timeout total, y compris connexion, envoi, réception de tous les timeouts
    'connect_timeout' => 0.5, // timeout de connexion, il couvre le premier timeout total
));

// Co\MySQL() n'a pas la fonction de configuration set
$client = new Co\MySQL();

// Co\Socket est configuré via setOption
$socket = new Co\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = array('sec'=>1, 'usec'=>500000);
$socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout); // temps de timeout pour la réception de données
$socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout); // configuration du timeout de connexion et d'envoi de données
```

!> Cette méthode a un impact uniquement sur la classe actuelle et sera couverte par la première méthode, ignorant la configuration de la troisième méthode ci-dessous.

3. Comme on peut le voir dans les deux méthodes de configuration de timeout ci-dessus, les règles de configuration des timeouts sont compliquées et incohérentes. Afin d'éviter que les développeurs doivent être prudents dans toutes les configurations, à partir de la version `v4.2.10`, tous les clients asynchrones offrent une configuration unifiée globale des règles de timeout, qui a l'impact le plus important et la priorité la plus basse, comme suit :

```php
Co::set([
    'socket_timeout' => 5,
    'socket_connect_timeout' => 1,
    'socket_read_timeout' => 1,
    'socket_write_timeout' => 1,
]);
```

+ `-1` : indique qu'il n'y aura jamais de timeout
+ `0` : indique que le temps de timeout ne sera pas modifié
+ `autres valeurs supérieures à 0` : indiquent l'établissement d'un timer de timeout correspondant aux secondes spécifiées, avec une précision maximale de `1 milliseconde`, c'est un nombre à virgule flottante, `0.5` représente `500 millisecondes`
+ `socket_connect_timeout` : indique le temps de timeout pour l'établissement d'une connexion TCP, **par défaut `1 seconde`**, à partir de la version `v4.5.x` **par défaut `2 secondes`**
+ `socket_timeout` : indique le temps de timeout pour les opérations de lecture/écriture TCP, **par défaut `-1`**, à partir de la version `v4.5.x` **par défaut `60 secondes`** . Si l'on souhaite configurer séparément la lecture et l'écriture, veuillez consulter la configuration ci-dessous
+ `socket_read_timeout` : ajouté dans la version `v4.3`, indique le temps de timeout pour l'opération de **lectura** TCP, **par défaut `-1`**, à partir de la version `v4.5.x` **par défaut `60 secondes`**
+ `socket_write_timeout` : ajouté dans la version `v4.3`, indique le temps de timeout pour l'opération d'**écriture** TCP, **par défaut `-1`**, à partir de la version `v4.5.x` **par défaut `60 secondes`**

!> **Cela signifie :** Pour les versions antérieures à `v4.5.x` de tous les clients asynchrones Swoole fournis, si le timeout n'a pas été configuré en utilisant les premières méthodes `1` et `2`, le temps de timeout par défaut pour la connexion est de `1 seconde`, et les opérations de lecture/écriture ne feront jamais l'objet d'un timeout ;  
À partir de la version `v4.5.x`, le temps de timeout par défaut pour la connexion est de `60 secondes`, et le temps de timeout pour les opérations de lecture/écriture est également de `60 secondes` ;  
Si le timeout global est modifié au cours du processus, cela n'affectera pas les sockets déjà créés.

### Timeout du bibliothèque réseau officielle PHP

En plus des clients asynchrones Swoole fournis ci-dessus, ceux utilisés dans [la synchronisation des asynchrones](/runtime) sont basés sur les méthodes natives PHP. Leur temps de timeout est influencé par la configuration [default_socket_timeout](http://php.net/manual/zh/filesystem.configuration.php), et les développeurs peuvent le configurer séparément avec `ini_set('default_socket_timeout', 60)`, dont la valeur par défaut est de 60.
