# Serveur TCP

?> `Swoole\Coroutine\Server` est une classe entièrement [co-routine](/coroutine)isée utilisée pour créer des serveurs TCP co-routines, supportant les types TCP et [unixSocket](/learn?id=什么是IPC).

Contrairement au module [Server](/server/tcp_init) :

* Crée et détruit dynamiquement, peut écouter des ports de manière dynamique pendant le fonctionnement, ainsi que fermer le serveur de manière dynamique
* Le processus de traitement des connexions est entièrement synchrone, l'application peut traiter les événements `Connect`, `Receive`, `Close` de manière séquentielle

!> Disponible à partir de la version 4.4


## Nom court

Utilisez le nom court `Co\Server`.


## Méthodes


### __construct()

?> **Constructeur.** 

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Paramètres** 

    * **`string $host`**
      * **Fonction** : Adresse à écouter
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $port`**
      * **Fonction** : Port à écouter【Si 0, un port aléatoire sera attribué par l'OS】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`bool $ssl`**
      * **Fonction** : Activer ou non le cryptage SSL
      * **Valeur par défaut** : `false`
      * **Autres valeurs** : `true`

    * **`bool $reuse_port`**
      * **Fonction** : Activer ou non la réutilisation du port, l'effet est le même que la configuration de [cette section](/server/setting?id=enable_reuse_port)
      * **Valeur par défaut** : `false`
      * **Autres valeurs** : `true`
      * **Impact de la version** : Swoole version >= v4.4.4

  * **Avis**

    * **Paramètre $host prend en charge 3 formats**

      * `0.0.0.0/127.0.0.1`: Adresse IPv4
      * `::/::1`: Adresse IPv6
      * `unix:/tmp/test.sock`: [Adresse UnixSocket](/learn?id=什么是IPC)

    * **Erreurs**

      * Des exceptions telles que des erreurs de paramètres, des échecs de liaison d'adresse et de port, ou des échecs d'écoute seront lancées en cas d'erreur.


### set()

?> **Définir les paramètres de traitement du protocole.** 

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **Paramètres de configuration**

    * Le paramètre `$options` doit être un tableau associatif unidimensionnel, identique aux éléments de configuration acceptés par la méthode [setprotocol](/coroutine_client/socket?id=setprotocol).

    !> Les paramètres doivent être définis avant de [commencer()](/coroutine/server?id=start)

    * **Protocole de longueur**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      * **`package_length_offset`** : 0,
      * **`package_body_offset`** : 4,
    ]);
    ```

    * **Configuration des certificats SSL**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      'ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```


### handle()

?> **Définir la fonction de traitement de la connexion.** 

!> La fonction de traitement doit être définie avant de [commencer()](/coroutine/server?id=start)

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **Paramètres** 

    * **`callable $fn`**
      * **Fonction** : Définir la fonction de traitement de la connexion
      * **Valeur par défaut** : None
      * **Autres valeurs** : None
      
  * **Exemple** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            $data = $conn->recv();
        }
    });
    ```

    !> - Après un succès dans `Accept` (établissement de connexion), le serveur créera automatiquement une [co-routine](/coroutine?id=协程调度) et exécutera la fonction `$fn` ;  
    - La fonction `$fn` est exécutée dans un nouveau espace de co-routine, donc il n'est pas nécessaire de créer de nouvelles co-routines à l'intérieur de la fonction ;  
    - La fonction `$fn` accepte un paramètre, de type [Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection) ;  
    - Vous pouvez utiliser [exportSocket()](/coroutine/server?id=exportsocket) pour obtenir l'objet Socket de la connexion actuelle


### shutdown()

?> **Arrêter le serveur.** 

?> Le support au niveau du code permet plusieurs appels à `start` et `shutdown`

```php
Swoole\Coroutine\Server->shutdown(): bool
```


### start()

?> **Démarrer le serveur.** 

```php
Swoole\Coroutine\Server->start(): bool
```

  * **Valeur de retour**

    * Un échec de démarrage retourne `false` et définit la propriété `errCode`
    * Un démarrage réussi entrera dans un cycle, `Accept` des connexions
    * Après un succès dans `Accept` (établissement de connexion), une nouvelle co-routine sera créée, et la fonction spécifiée par la méthode `handle` sera appelée dans cette co-routine

  * **Gestion des erreurs**

    * Lorsqu'une erreur `Too many open files` se produit lors de l' `Accept` (établissement de connexion), ou lorsqu'il est impossible de créer de nouvelles co-routines, l' `Accept` sera suspendu pendant `1` seconde avant de continuer
    * Lors d'une erreur, la méthode `start()` retournera, et l'information d'erreur sera signalée sous forme de `Warning`.


## Objets


### Coroutine\Server\Connection

L'objet `Swoole\Coroutine\Server\Connection` offre quatre méthodes :
 
#### recv()

Réception de données, si un traitement de protocole est défini, chaque retour sera un paquet complet

```php
function recv(float $timeout = 0)
```

#### send()

Envoi de données

```php
function send(string $data)
```

#### close()

Fermeture de la connexion

```php
function close(): bool
```

#### exportSocket()

Obtenir l'objet Socket de la connexion actuelle. Plus de méthodes de base peuvent être appelées, veuillez consulter [Swoole\Coroutine\Socket](/coroutine_client/socket)

```php
function exportSocket(): Swoole\Coroutine\Socket
```

## Exemple complet

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

//Module de gestion de processus multiples
$pool = new Process\Pool(2);
//Permettre à chaque callback OnWorkerStart de créer automatiquement une co-routine
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    //Chaque processus écoute le port 9501
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    //Réception du signal 15 pour fermer le service
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    //Réception d'une nouvelle demande de connexion et création automatique d'une co-routine
    $server->handle(function (Connection $conn) {
        while (true) {
            //Réception de données
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            //Envoi de données
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    //Début de l'écoute du port
    $server->start();
});
$pool->start();
```

!> Si vous exécutez dans un environnement Cygwin, veuillez modifier pour un seul processus. `$pool = new Swoole\Process\Pool(1);`
