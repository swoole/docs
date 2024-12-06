# Client Redis Coroutine

!> Ce client n'est plus recommandé pour utilisation, il est préférable d'utiliser la méthode `Swoole\Runtime::enableCoroutine + phpredis` ou `predis`, c'est-à-dire [l'utilisation en un clic de coroutines](/runtime) pour la client native PHP `redis`.

!> Après la version `Swoole 6.0`, ce client Redis coroutine a été supprimé.


## Exemple d'utilisation

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});
```

!> `subscribe` et `pSubscribe` ne peuvent pas être utilisés avec `defer(true)`.


## Méthodes

!> L'utilisation des méthodes est essentiellement conforme à [phpredis](https://github.com/phpredis/phpredis).

Les explications suivantes diffèrent de la mise en œuvre de [phpredis](https://github.com/phpredis/phpredis) :

1. Commands Redis non encore implémentés : `scan object sort migrate hscan sscan zscan` ;

2. Utilisation de `subscribe pSubscribe`, sans avoir à configurer de fonction de rappel ;

3. Support de la sérialisation des variables PHP, lorsque le troisième argument de la méthode `connect()` est set à `true`, la caractéristique de sérialisation des variables PHP est activée, par défaut elle est désactivée.


### __construct()

Constructeur du client Redis coroutine, qui permet de configurer les options de connexion au `Redis`, et est conforme aux paramètres de la méthode `setOptions()`.

```php
Swoole\Coroutine\Redis::__construct(array $options = null);
```


### setOptions()

Après la version 4.2.10, cette méthode a été ajoutée pour configurer certaines options du client `Redis` après la construction et la connexion.

Cette fonction est au style Swoole, et doit être configurée avec un tableau de paires clé-val.

```php
Swoole\Coroutine\Redis->setOptions(array $options): void
```

  * **Options Configurables**


clé | Description
---|---
`connect_timeout` | Temps de connexion, par défaut est le `socket_connect_timeout` global des coroutines (1 seconde)
`timeout` | Temps de temps, par défaut est le `socket_timeout` global des coroutines, voir [règles de temps de client](/coroutine_client/init?id=règles de temps)
`serialize` | 自动 sérialisation, par défaut désactivé
`reconnect` | Nombre d'essais de connexion automatique, si la connexion est fermée normalement en raison d'un timeout ou d'une autre raison, lors de la prochaine demande, un essai de connexion automatique sera effectué avant d'envoyer la demande, par défaut est `1` fois (`true`), une fois échoué après le nombre spécifié d'essais, l'essai ne continuera plus, il est nécessaire de se reconnecter manuellement. Ce mécanisme est uniquement utilisé pour le maintien de la connexion, il ne resend pas les demandes et ne cause pas d'erreurs d'interfaces non idempotentes.
`compatibility_mode` | Résolution de compatibilité pour les fonctions `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore` qui retournent des résultats différents de `php-redis`, l'activation de cette option fait que les résultats de `Co\Redis` et `php-redis` sont cohérents, par défaut désactivé 【Cette configuration est disponible dans les versions `v4.4.0` ou supérieures】


### set()

Enregister une donnée.

```php
Swoole\Coroutine\Redis->set(string $key, mixed $value, array|int $option): bool
```

  * **Paramètres** 

    * **`string $key`**
      * **Fonction** : La clé de la donnée
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $value`**
      * **Fonction** : Le contenu de la donnée 【Les types non strings seront automatiquement sérialisés】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $options`**
      * **Fonction** : Options
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

      !> `$option` Explication :  
      `entiers` : Établir une durée de vie, par exemple `3600`  
      `tableaux` : Paramètres avancés de durée de vie, par exemple `['nx', 'ex' => 10]` 、`['xx', 'px' => 1000]`

      !> `px`: Indique une durée de vie en millisecondes  
      `ex`: Indique une durée de vie en secondes  
      `nx`: Indique l'établissement d'une durée de vie seulement si elle n'existe pas  
      `xx`: Indique l'établissement d'une durée de vie seulement si elle existe


### request()

Envoie une commande personnalisée au serveur Redis. Similaire à `rawCommand` de phpredis.

```php
Swoole\Coroutine\Redis->request(array $args): void
```

  * **Paramètres** 

    * **`array $args`**
      * **Fonction** : Liste des paramètres, qui doit être un tableau formaté. 【Le premier élément doit être la commande Redis, les autres éléments sont les paramètres de la commande, le sous-système底层将自动 emballer en tant que demande de protocol Redis pour l'envoi.】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeur de retour** 

Dépend de la manière dont le serveur Redis gère la commande, cela peut retourner un nombre, un booléen, une chaîne, un tableau, etc.

  * **Exemple d'utilisation** 

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379); // Si c'est un socket UNIX local, l'argument host doit être填写 sous la forme `unix://tmp/your_file.sock`
    $res = $redis->request(['object', 'encoding', 'key1']);
    var_dump($res);
});
```


## Propriétés


### errCode

Code d'erreur.


Code d'erreur | Explication
---|---
1 | Échec de la lecture ou de l'écriture
2 | Tout le reste...
3 | Fin du fichier
4 | Échec du protocole
5 | Mémoire épuisée


### errMsg

Message d'erreur.


### connected

Détermine si le client Redis actuel est connecté au serveur.


## Constantes

Utilisées pour la méthode `multi($mode)`, par défaut en mode `SWOOLE_REDIS_MODE_MULTI` :

* SWOOLE_REDIS_MODE_MULTI
* SWOOLE_REDIS_MODE_PIPELINE

Utilisées pour juger de la valeur de retour de la commande `type()`:

* SWOOLE_REDIS_TYPE_NOT_FOUND
* SWOOLE_REDIS_TYPE_STRING
* SWOOLE_REDIS_TYPE_SET
* SWOOLE_REDIS_TYPE_LIST
* SWOOLE_REDIS_TYPE_ZSET
* SWOOLE_REDIS_TYPE_HASH


## Mode de transaction

Il est possible d'utiliser `multi` et `exec` pour mettre en œuvre le mode de transaction Redis.

  * **Avertissement**

    * Utilisez la commande `multi` pour commencer une transaction, toutes les commandes suivantes seront ajoutées à la file d'attente pour l'exécution
    * Utilisez la commande `exec` pour exécuter toutes les opérations de la transaction et retourner tous les résultats d'un seul coup

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key3', 'rango');
    $redis->get('key1');
    $redis->get('key2');
    $redis->get('key3');

    $result = $redis->exec();
    var_dump($result);
});
```


## Mode de souscription

!> Disponible à partir de la version Swoole v4.2.13, **les versions 4.2.12 et inférieures présentent des BUGs dans le mode de souscription**


### Souscription

Contrairement à `phpredis`, `subscribe/psubscribe` est au style coroutines.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // Ou utiliser psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg est un tableau, contenant les informations suivantes
            // $type # Type de retour : indique succès de la souscription
            // $name # Nom du canal de souscription ou du canal d'origine
            // $info  # Nombre actuel de canaux de souscription ou informations de contenu
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') { // Ou psubscribe
                // Message de succès de la souscription, un canal de souscription pour chaque canal souscrit
            } else if ($type == 'unsubscribe' && $info == 0){ // Ou punsubscribe
                break; // Réception du message d'annulation de souscription, et le nombre de canaux de souscription restants est 0, cesse de recevoir, finit le cycle
            } else if ($type == 'message') {  // Si c'est psubscribe, ici c'est pmessage
                var_dump($name); // Imprime le nom du canal d'origine
                var_dump($info); // Imprime le message
                // balabalaba.... // Traite le message
                if ($need_unsubscribe) { // Dans certains cas, il est nécessaire de se délier
                    $redis->unsubscribe(); // Continue à recevoir en attendant la fin de l'annulation
                }
            }
        }
    }
});
```
### Annulation

Pour annuler une souscription, utilisez `unsubscribe/punsubscribe`, `$redis->unsubscribe(['channel1'])`

À ce moment-là, `$redis->recv()` recevra un message d'annulation de souscription. Si vous annulez la souscription à plusieurs canaux, vous recevrez plusieurs messages.
    
!> Remarque : Après l'annulation, assurez-vous de continuer à utiliser `recv()` jusqu'à ce que vous receviez le dernier message d'annulation de souscription ($msg[2] == 0). Ce message indique que vous êtes sorti du mode de souscription.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // ou utilisez psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg est un tableau contenant les informations suivantes
            // $type # type de retour : affichage du succès de la souscription
            // $name # nom du canal souscrit ou nom du canal source
            // $info  # le nombre de canaux actuellement souscrits ou le contenu de l'information
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') // ou psubscribe
            {
                // message de succès de la souscription du canal
            }
            else if ($type == 'unsubscribe' && $info == 0) // ou punsubscribe
            {
                break; // reçu le message d'annulation de souscription, et le nombre de canaux restants pour la souscription est 0, ne plus recevoir, sortir du loop
            }
            else if ($type == 'message') // si c'est psubscribe, ici est pmessage
            {
                // afficher le nom du canal source
                var_dump($name);
                // afficher le message
                var_dump($info);
                // gérer le message
                if ($need_unsubscribe) // dans certains cas, vous devez vous souscrire à l'annulation
                {
                    $redis->unsubscribe(); // continuer à recevoir pour attendre l'annulation terminée
                }
            }
        }
    }
});
```

## Mode de compatibilité

Le problème de l'incompatibilité des résultats de commandes telles que `hmGet/hGetAll/zrange/zrevrange/zrangebyscore/zrevrangebyscore` de `Co\Redis` avec ceux de l'extension `phpredis` a été résolu [#2529](https://github.com/swoole/swoole-src/pull/2529).

Pour être compatible avec les anciennes versions, ajoutez la configuration `$redis->setOptions(['compatibility_mode' => true]);` pour garantir que les résultats de `Co\Redis` et de `phpredis` soient cohérents.

!> Disponible pour les versions de Swoole >= `v4.4.0`

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->setOptions(['compatibility_mode' => true]);
    $redis->connect('127.0.0.1', 6379);

    $co_get_val = $redis->get('novalue');
    $co_zrank_val = $redis->zRank('novalue', 1);
    $co_hgetall_val = $redis->hGetAll('hkey');
    $co_hmget_val = $redis->hmGet('hkey', array(3, 5));
    $co_zrange_val = $redis->zRange('zkey', 0, 99, true);
    $co_zrevrange_val = $redis->zRevRange('zkey', 0, 99, true);
    $co_zrangebyscore_val = $redis->zRangeByScore('zkey', 0, 99, ['withscores' => true]);
    $co_zrevrangebyscore_val = $redis->zRevRangeByScore('zkey', 99, 0, ['withscores' => true]);
});
```
