# Client TCP/UDP coroutines

Le `Coroutine\Client` fournit une encapsulation de la client Socket pour les protocoles de transport `TCP`, `UDP`, et [unixSocket](/learn?id=什么是IPC), en utilisant simplement `new Swoole\Coroutine\Client`.

* **Principe de réalisation**

    * Tous les méthodes impliquées dans les demandes réseau du `Coroutine\Client` sont orchestrées par le [système de coroutines](/coroutine?id=协程调度) de Swoole, sans que le niveau d'application ne soit conscient
    * L'utilisation est entièrement conforme aux méthodes de mode synchrone du [Client](/client)
    * La configuration du timeout pour `connect` s'applique également aux timeouts de `Connect`, `Recv` et `Send`

* **Relation hiérarchique**

    * Le `Coroutine\Client` n'est pas hérité du [Client](/client), mais toutes les méthodes offertes par le `Client` peuvent être utilisées dans le `Coroutine\Client`. Veuillez consulter [Swoole\Client](/client?id=方法) pour plus d'informations, qui n'est pas répétée ici.
    * Dans le `Coroutine\Client`, utilisez la méthode `set` pour configurer les [options](/client?id=配置), et l'utilisation est entièrement conforme à celle de `Client->set`. Pour les fonctions qui présentent des différences d'utilisation, une explication séparée sera donnée dans la section des fonctions `set()`.

* **Exemple d'utilisation**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

* **Gestion des protocoles**

La client coroutine prend également en charge la gestion des protocoles de longueur et `EOF`, et la configuration est entièrement conforme à celle du [Swoole\Client](/client?id=配置).

```php
$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0, //Le Nème byte est la valeur de la longueur du paquet
    'package_body_offset'   => 4, //Quels字节 commencent à calculer la longueur
    'package_max_length'    => 2000000, //La longueur maximale du protocole
));
```


### connect()

Se connecter au serveur distant.

```php
Swoole\Coroutine\Client->connect(string $host, int $port, float $timeout = 0.5): bool
```

  * **Paramètres** 

    * **`string $host`**
      * **Fonction** : Adresse du serveur distant【Le système底层 effectue automatiquement le changement de coroutines pour résoudre le nom de domaine en IP】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $port`**
      * **Fonction** : Port du serveur distant
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`float $timeout`**
      * **Fonction** : Temps d'attente pour l'IO réseau ; comprend `connect/send/recv`, si le temps expire, la connexion sera automatiquement `close`, veuillez consulter [règles de timeout client](/coroutine_client/init?id=超时规则)
      * **Unité de valeur** : seconde【Soutient des fractions, comme `1.5` signifie `1s`+`500ms`】
      * **Valeur par défaut** : `0.5s`
      * **Autres valeurs** : Aucun

* **Avis**

    * Si la connexion échoue, elle retournera `false`
    * Retour après expiration, vérifiez `errCode` de `$cli` pour `110`

* **Retour à l'essai après échec**

!> Après un échec de `connect`, il n'est pas possible de se reconnecter directement. Il faut d'abord utiliser `close` pour fermer l'existing `socket`, puis effectuer un nouveau `connect`.

```php
//Échec de la connexion
if ($cli->connect('127.0.0.1', 9501) == false) {
    //Fermer l'existing socket
    $cli->close();
    // Réessayer la connexion
    $cli->connect('127.0.0.1', 9501);
}
```

* **Exemple**

```php
if ($cli->connect('127.0.0.1', 9501)) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}

if ($cli->connect('/tmp/rpc.sock')) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}
```


### isConnected()

Retourne l'état de connexion du Client

```php
Swoole\Coroutine\Client->isConnected(): bool
```

  * **Valeur de retour**

    * Retourne `false`, indiquant qu'il n'est actuellement pas connecté au serveur
    * Retourne `true`, indiquant qu'il est actuellement connecté au serveur
    
!> La méthode `isConnected` retourne l'état d'application, indiquant seulement que le `Client` a exécuté `connect` et s'est connecté avec succès au `Server`, et n'a pas exécuté `close` pour fermer la connexion. Le `Client` peut exécuter des opérations telles que `send`, `recv`, `close`, etc., mais ne peut pas exécuter à nouveau `connect`.
Ce n'implique pas que la connexion soit certainement utilisable, car il est possible de recevoir une erreur lors de l'exécution de `send` ou `recv`, car l'application ne peut pas obtenir l'état réel de la connexion TCP de base. Lors de l'exécution de `send` ou `recv`, l'application interagit avec le noyau pour obtenir l'état réel de la connexion utilisable.


### send()

Envoi de données.

```php
Swoole\Coroutine\Client->send(string $data): int|bool
```

  * **Paramètres** 

    * **`string $data`**
    
      * **Fonction** : Données à envoyer, doivent être de type chaîne, prennent en charge les données binaires
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * Le succès de l'envoi retourne le nombre de字节 écrits dans le tampon du `Socket`, le niveau inférieur essaiera autant que possible d'envoyer toutes les données. Si le nombre de字节 retourné est différent de la longueur de `$data` passée, cela peut signifier que le `Socket` a été fermé par l'autre côté, et lors de la prochaine invocation de `send` ou `recv`, le code d'erreur approprié sera retourné.

  * Échec de l'envoi retourne `false`, vous pouvez utiliser `$client->errCode` pour obtenir la raison de l'erreur.


### recv()

La méthode `recv` est utilisée pour recevoir des données du serveur.

```php
Swoole\Coroutine\Client->recv(float $timeout = 0): string|bool
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** :设置了超时时间
      * **Valeur unité** : seconde【支持浮点型，如`1.5`表示`1s`+`500ms`】
      * **Valeur par défaut** : 参考[客户端超时规则](/coroutine_client/init?id=超时规则)
      * **Autres valeurs** : 无

    !> Lorsqu'un timeout est défini, il est prioritaire par rapport au paramètre spécifié, suivi de la configuration `timeout` passée à la méthode `set`. Le code d'erreur pour un timeout est `ETIMEDOUT`

  * **Valeur de retour**

    * Si la [communication protocol](/client?id=协议解析) est définie, `recv` retournera des données complètes, la longueur est limitée par [package_max_length](/server/setting?id=package_max_length)
    * Sans définition de protocol de communication, `recv` retourne au maximum `64K` de données
    * Sans définition de protocol de communication, `recv` retourne les données brutes, nécessitant une gestion du protocole réseau dans le code PHP
    * `recv` retourne une chaîne vide si le serveur ferme activement la connexion, nécessitant un `close`
    * Échec de `recv`, retourne `false`, vérifiez `$client->errCode` pour obtenir la raison de l'erreur, veuillez consulter l'exemple complet ci-dessous pour la manière de gérer.


### close()

Fermer la connexion.

!> `close` n'est pas bloquant, il retournera immédiatement. L'opération de fermeture ne fait pas de changement de coroutines.

```php
Swoole\Coroutine\Client->close(): bool
```


### peek()

Vue prématurée des données.

!> La méthode `peek` manipule directement le `socket`, elle n'induit donc pas un changement de coroutines.

```php
Swoole\Coroutine\Client->peek(int $length = 65535): string
```

  * **Avis**

    * La méthode `peek` est utilisée uniquement pour observer les données dans le tampon du `socket` du noyau, sans décalage. Après avoir utilisé `peek`, vous pouvez encore utiliser `recv` pour lire ces données
    * La méthode `peek` est non bloquante, elle retournera immédiatement. Lorsque le tampon du `socket` contient des données, elle retournera le contenu des données. Si le tampon est vide, elle retournera `false` et établira `errCode` de `$client`
    * Si la connexion est fermée, `peek` retournera une chaîne vide

### set()

Configure les paramètres du client.

```php
Swoole\Coroutine\Client->set(array $settings): bool
```

  * **Paramètres de configuration**

    * Veuillez consulter [Swoole\Client](/client?id=set).

* **Différence avec [Swoole\Client](/client?id=set)**
    
    Le client coroutine offre un contrôle plus granulaire des délais de timeout. Il est possible de configurer :
    
    * `timeout` : timeout global, y compris le temps de connexion, d'envoi, de réception
    * `connect_timeout` : timeout de connexion
    * `read_timeout` : timeout de réception
    * `write_timeout` : timeout d'envoi
    * Veuillez consulter [Règles de timeout des clients](/coroutine_client/init?id=règles_de_timeout)

* **Exemple**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'timeout' => 0.5,
        'connect_timeout' => 1.0,
        'write_timeout' => 10.0,
        'read_timeout' => 0.5,
    ));

    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

### Exemple complet

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // Equivaut à vide, ferme directement la connexion
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // Peut être géré en fonction de la logique commerciale et des codes d'erreur, par exemple :
                    // Si timeout, ne ferme pas la connexion, sinon ferme directement
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```
