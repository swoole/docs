# Méthodes


## __construct()

Crée un objet `Server` TCP à usage d'I/O asynchrone.

```php
Swoole\Server::__construct(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```

  * **Paramètres**

    * `string $host`

      * Fonction : Spécifier l'adresse IP à écouter.
      * Valeur par défaut : None.
      * Autres valeurs : None.

      !> Pour IPv4, utiliser `127.0.0.1` pour écouter le本地, `0.0.0.0` pour écouter toutes les adresses.
      Pour IPv6, utiliser `::1` pour écouter le local, `::` (équivalent à `0:0:0:0:0:0:0:0`) pour écouter toutes les adresses.

    * `int $port`

      * Fonction : Spécifier le port à écouter, par exemple `9501`.
      * Valeur par défaut : None.
      * Autres valeurs : None.

      !> Si la valeur de `$sockType` est [UnixSocket Stream/Dgram](/learn?id=什么是IPC), ce paramètre sera ignoré.
      Écouter des ports inférieurs à `1024` nécessite les droits `root`.
      Si ce port est occupé, `server->start` échouera.

    * `int $mode`

      * Fonction : Spécifier le mode de fonctionnement.
      * Valeur par défaut : [SWOOLE_PROCESS](/learn?id=swoole_process) mode de processus (par défaut).
      * Autres valeurs : [SWOOLE_BASE](/learn?id=swoole_base) mode de base, [SWOOLE_THREAD](/learn?id=swoole_thread) mode de thread (disponible à partir de Swoole 6.0).

      ?> Dans le mode `SWOOLE_THREAD`, vous pouvez cliquer ici [Thread + Serveur (style asynchrone)](/thread/thread?id=thread-serveur-style-asynchrone) pour voir comment établir un serveur dans un mode multithread.

      !> À partir de Swoole 5, la valeur par défaut du mode de fonctionnement est `SWOOLE_BASE`.

    * `int $sockType`

      * Fonction : Spécifier le type de ce groupe de `Server`.
      * Valeur par défaut : None.
      * Autres valeurs :
        * `SWOOLE_TCP/SWOOLE_SOCK_TCP` socket TCP IPv4
        * `SWOOLE_TCP6/SWOOLE_SOCK_TCP6` socket TCP IPv6
        * `SWOOLE_UDP/SWOOLE_SOCK_UDP` socket UDP IPv4
        * `SWOOLE_UDP6/SWOOLE_SOCK_UDP6` socket UDP IPv6
        * [SWOOLE_UNIX_DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) socket Unix datagramme
        * [SWOOLE_UNIX_STREAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/stream_server.php) socket Unix stream 

      !> En utilisant `$sock_type` | `SWOOLE_SSL`, vous pouvez activer la cryptographie de tunnel SSL. Une fois SSL activé, il doit être configuré. [ssl_key_file](/server/setting?id=ssl_cert_file) et [ssl_cert_file](/server/setting?id=ssl_cert_file)

  * **Exemple**

```php
$server = new \Swoole\Server($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP);

// Vous pouvez mélanger UDP/TCP, écouter des ports internes et externes en même temps, pour l'écoute sur plusieurs ports, veuillez consulter la section addlistener.
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP); // Ajouter TCP
$server->addlistener("192.168.1.100", 9503, SWOOLE_SOCK_TCP); // Ajouter Web Socket
$server->addlistener("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDP
$server->addlistener("/var/run/myserv.sock", 0, SWOOLE_UNIX_STREAM); //UnixSocket Stream
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP | SWOOLE_SSL); //TCP + SSL

$port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP); // Le système alloue un port au hasard, la valeur de retour est le port alloué au hasard
echo $port->port;
```
  

## set()

Utilisé pour configurer divers paramètres en temps réel. Après le démarrage du serveur, les paramètres définis par la méthode `Server->set` peuvent être accessibles via `$serv->setting`.

```php
Swoole\Server->set(array $setting): void
```

!> La méthode `Server->set` doit être appelée avant `Server->start`, veuillez consulter [cette section](/server/setting) pour la signification de chaque configuration.

  * **Exemple**

```php
$server->set(array(
    'reactor_num'   => 2,     // Nombre de threads
    'worker_num'    => 4,     // Nombre de processus
    'backlog'       => 128,   // Longueur de la file d'attente Listen
    'max_request'   => 50,    // Nombre maximal de demandes acceptées par processus
    'dispatch_mode' => 1,     // Stratégie de distribution des paquets
));
```


## on()

Enregistre une fonction de rappel pour un événement du `Server`.

```php
Swoole\Server->on(string $event, callable $callback): bool
```

!> L'appel répété de la méthode `on`覆盖a la configuration précédente.

!> À partir de PHP 8.2, il n'est plus possible de définir des propriétés dynamiques directement, si `$event` n'est pas un événement défini par Swoole, cela provoquera une alerte.

  * **Paramètres**

    * `string $event`

      * Fonction : Nom de l'événement de rappel
      * Valeur par défaut : None
      * Autres valeurs : None

      !> Sans distinction de cas, veuillez consulter [cette section](/server/events) pour connaître les événements de rappel disponibles, les noms d'événements ne doivent pas être précédés de `on`.

    * `callable $callback`

      * Fonction : La fonction de rappel
      * Valeur par défaut : None
      * Autres valeurs : None

      !> Cela peut être une chaîne de nom de fonction, une méthode statique de classe, un tableau de méthodes d'objet, une fonction anonyme. Veuillez consulter [cette section](/learn?id=les différentes façons de configurer des fonctions de rappel) pour plus d'informations.
  
  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, retourne `false` si l'opération échoue.

  * **Exemple**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('connect', function ($server, $fd){
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();
```


## addListener()

Ajoute un port d'écoute. Dans le code commercial, vous pouvez utiliser la méthode [Swoole\Server->getClientInfo](/server/methods?id=getclientinfo) pour obtenir le port d'où provient une connexion donnée.

```php
Swoole\Server->addListener(string $host, int $port, int $sockType): bool|Swoole\Server\Port
```

!> Écouter des ports en dessous de `1024` nécessite les droits `root`.  
Le serveur principal est un serveur `WebSocket` ou `HTTP`, et les nouveaux ports TCP ajoutés inheritent par défaut les paramètres de protocole du serveur principal. Vous devez appeler séparément la méthode `set` pour configurer un nouveau protocole afin d'activer ce nouveau protocole [voir la description détaillée](/server/port).
Vous pouvez cliquer [ici](/server/server_port) pour voir la description détaillée de `Swoole\Server\Port`. 

  * **Paramètres**

    * `string $host`

      * Fonction : Identique à `$host` dans `__construct()`
      * Valeur par défaut : Identique à `$host` dans `__construct()`
      * Autres valeurs : Identique à `$host` dans `__construct()`

    * `int $port`

      * Fonction : Identique à `$port` dans `__construct()`
      * Valeur par défaut : Identique à `$port` dans `__construct()`
      * Autres valeurs : Identique à `$port` dans `__construct()`

    * `int $sockType`

      * Fonction : Identique à `$sockType` dans `__construct()`
      * Valeur par défaut : Identique à `$sockType` dans `__construct()`
      * Autres valeurs : Identique à `$sockType` dans `__construct()`
  
  * **Valeurs de retour**

    * Retourne `Swoole\Server\Port` si l'opération est réussie, retourne `false` si l'opération échoue.
!> - Dans le mode `Unix Socket`, l'argument `$host` doit être une voie de fichier accessible, et l'argument `$port` est ignoré  

- Dans le mode `Unix Socket`, le client `$fd` n'est plus un numéro, mais une chaîne de chemin de fichier  
- Sous le système `Linux`, il est possible de se connecter en utilisant une adresse `IPv4` après avoir écouté sur un port `IPv6`

## listen()

Cette méthode est un alias pour `addlistener`.

```php
Swoole\Server->listen(string $host, int $port, int $type): bool|Swoole\Server\Port
```

## addProcess()

Ajoute un processus de travail personnalisé. Cette fonction est généralement utilisée pour créer un processus de travail spécial, utilisé pour le monitoring, le rapport ou d'autres tâches spéciales.

```php
Swoole\Server->addProcess(Swoole\Process $process): int
```

!> Il n'est pas nécessaire d'exécuter `start`. Lorsque le `Server` est démarré, des processus sont automatiquement créés et la fonction de sous-processus spécifiée est exécutée

  * **Paramètres**
  
    * [Swoole\Process](/process/process)

      * Fonction : objet `Swoole\Process`
      * Valeur par défaut : aucune
      * Autres valeurs : aucune

  * **Valeur de retour**

    * Retourne l'ID du processus pour indiquer un succès, sinon l'exécution de l'application lancera une erreur fatale.

  * **Remarque**

    !> - Les sous-processus créés peuvent appeler diverses méthodes de l'objet `$server`, telles que `getClientList/getClientInfo/stats`.                                   
    - Dans les processus `Worker/Task`, vous pouvez utiliser les méthodes fournies par `$process` pour communiquer avec les sous-processus.        
    - Dans les processus personnalisés, vous pouvez utiliser `$server->sendMessage` pour communiquer avec les processus `Worker/Task`.      
    - Les processus utilisateur ne peuvent pas utiliser les interfaces `Server->task/taskwait`.              
    - Les processus utilisateur peuvent utiliser les interfaces telles que `Server->send/close`.         
    - Les processus utilisateur doivent exécuter un cycle `while(true)` (comme illustré ci-dessous) ou utiliser un [EventLoop](/learn?id=什么是eventloop) (par exemple, en créant un timer), sinon les processus utilisateur se termineront et se relanceront sans cesse.         

  * **Cycle de vie**

    ?> - Le cycle de vie des processus utilisateur est le même que celui du `Master` et du [Manager](/learn?id=manager进程), et n'est pas affecté par la [reload](/server/methods?id=reload).     
    - Les processus utilisateur ne sont pas contrôlés par la directive `reload`, et aucune information n'est envoyée aux processus utilisateur lors d'une `reload`.        
    - Lors de l'arrêt du serveur avec `shutdown`, un signal `SIGTERM` est envoyé aux processus utilisateur pour les arrêter.            
    - Les processus personnalisés sont hébergés par le processus `Manager`, et en cas d'erreur fatale, le processus `Manager` créera un nouveau.         
    - Les processus personnalisés ne déclenchent pas d'événements tels que `onWorkerStop`. 

  * **Exemple**

    ```php
    $server = new Swoole\Server('127.0.0.1', 9501);
    
    /**
     * Le processus utilisateur a réalisé la fonction de diffusion, en接收ant en boucle les messages du unixSocket et en les envoyant simultanément à toutes les connexions du serveur
     */
    $process = new Swoole\Process(function ($process) use ($server) {
        $socket = $process->exportSocket();
        while (true) {
            $msg = $socket->recv();
            foreach ($server->connections as $conn) {
                $server->send($conn, $msg);
            }
        }
    }, false, 2, 1);
    
    $server->addProcess($process);
    
    $server->on('receive', function ($serv, $fd, $reactor_id, $data) use ($process) {
        // Envoyer le message reçu à tous les clients
        $socket = $process->exportSocket();
        $socket->send($data);
    });
    
    $server->start();
    ```

    Référence la section [Process Communication](/process/process?id=exportsocket).


## start()

Démarrer le serveur, écouter tous les ports `TCP/UDP`.

```php
Swoole\Server->start(): bool
```

!> Remarque : Voici un exemple en mode [SWOOLE_PROCESS](/learn?id=swoole_process)

  * **Remarque**

    - Après un démarrage réussi, `worker_num+2` processus sont créés. Le processus `Master` + le processus `Manager` + `serv->worker_num` processus `Worker`.  
    - Un démarrage infructueux retourne immédiatement `false`.
    - Après un démarrage réussi, l'application entre dans un cycle d'événements et attend les demandes de connexion des clients. Le code après la méthode `start` n'est pas exécuté.  
    - Après l'arrêt du serveur, la fonction `start` retourne `true` et continue à exécuter le code suivant.  
    - Si la configuration `task_worker_num` est définie, le nombre de processus `Task` est augmenté de manière correspondante.   
    - Les méthodes listées avant `start` ne peuvent être utilisées qu'avant l'appel à `start`, et les méthodes après `start` ne peuvent être utilisées que dans des fonctions d'événement telles que [onWorkerStart](/server/events?id=onworkerstart) et [onReceive](/server/events?id=onreceive).

  * **Extensibilité**
  
    * Processus Principal

      * Il y a plusieurs threads [Reactor](/learn?id=reactor) dans le processus principal, qui effectuent un échantillonnage d'événements réseau basé sur `epoll/kqueue/select`. Après avoir reçu des données, elles sont redirigées vers les processus `Worker` pour traitement.
    
    * Processus Manager

      * Gère tous les processus `Worker`, qui sont automatiquement récupérés et des nouveaux processus `Worker` sont créés lorsque la vie cycle du processus `Worker` se termine ou qu'une anomalie se produit.
    
    * Processus Worker

      * Traite les données reçues, y compris l'analyse du protocole et la réponse aux demandes. Si `worker_num` n'est pas défini, le nombre de processus `Worker` démarrés est égal au nombre de CPU disponibles.
      * Un échec de démarrage dans l'extension lancera une erreur fatale, veuillez vérifier les informations relatives aux erreurs PHP dans le journal d'erreur PHP `php_errors.log`. `errno={number}` est le standard `Linux Errno`, veuillez consulter la documentation correspondante.
      * Si la configuration `log_file` est activée, les informations seront imprimées dans le fichier de log spécifié.

  * **Valeur de retour**

    * Retourne `true` pour indiquer un succès, `false` pour indiquer un échec

  * **Erreurs courantes lors du démarrage**

    * Échec de la liaison du port, car un autre processus occupe déjà ce port.
    * Absence de définition de la fonction de rappel obligatoire, échec du démarrage.
    * Code PHP contenant une erreur fatale, veuillez vérifier les informations d'erreur PHP dans le journal d'erreur PHP `php_errors.log`.
    * Exécution de `ulimit -c unlimited`, ouverture du dump de noyau, pour vérifier s'il y a des erreurs de segmentation.
    - Désactivez `daemonize`, désactivez `log`, afin que les informations d'erreur puissent être imprimées sur le écran.


## reload()

Redémarrer de manière sûre tous les processus Worker/Task.

```php
Swoole\Server->reload(bool $only_reload_taskworker = false): bool
```

!> Par exemple : un serveur arrière-plan très occupé gère constamment des demandes. Si l'administrateur utilise la méthode `kill` pour arrêter/redémarrer le processus du serveur, cela peut entraîner l'arrêt du code au milieu d'une transaction.  
Dans ce cas, une incohérence de données peut survenir. Par exemple, dans un système de paiement, la prochaine étape après la logique de paiement est le traitement des marchandises. Supposons que le processus soit arrêté après la logique de paiement. Cela pourrait entraîner un paiement effectué par l'utilisateur sans envoi de marchandises, avec des conséquences très graves.  
Swoole offre un mécanisme de terminaison/redémarrage souple, permettant à l'administrateur de envoyer un signal spécifique au serveur, permettant aux processus Worker du serveur de se terminer de manière sûre. Référence [Comment redémarrer correctement le service](/question/use?id=swoole如何正确的重启服务).

  * **Paramètres**
  
    * `bool $only_reload_taskworker`

      * Fonction : indiquer si seuls les processus [Task](/learn?id=taskworkerprocess) doivent être redémarrés
      * Valeur par défaut : false
      * Autres valeurs : true


!> - Le `reload` a un mécanisme de protection, si un nouveau signal de redémarrage est reçu pendant qu'un `reload` est en cours, il sera ignoré.

- Si `user/group` est configuré, les processus Worker peuvent ne pas avoir les autorisations pour envoyer des informations au processus maître, dans ce cas, il est nécessaire d'utiliser un compte root et de exécuter la commande `kill` dans un shell pour redémarrer.
- La directive `reload` est inefficace pour les processus ajoutés par [addProcess](/server/methods?id=addProcess).

  * **Valeur de retour**

    * Retourne `true` pour indiquer un succès, `false` pour indiquer un échec
       
  * **Extensibilité**
  
    * **Envoi de signaux**
    
        * `SIGTERM`: Envoyer ce signal au processus principal/gérant pour que le serveur se termine de manière sûre.
        * Dans le code PHP, vous pouvez utiliser `$serv->shutdown()` pour effectuer cette opération.
        * `SIGUSR1`: Envoyer le signal `SIGUSR1` au processus principal/gérant pour redémarrer de manière fluide tous les processus `Worker` et `TaskWorker`.
        * `SIGUSR2`: Envoyer le signal `SIGUSR2` au processus principal/gérant pour redémarrer de manière fluide tous les processus `Task`.
        * Dans le code PHP, vous pouvez utiliser `$serv->reload()` pour effectuer cette opération.
        
    ```shell
    # Redémarrer tous les processus Worker
    kill -USR1 PID du processus principal
    
    # Seulement redémarrer les processus Task
    kill -USR2 PID du processus principal
    ```
      
      > [Référence : Liste des signaux Linux](/other/signal)

    * **Mode Process**
    
        Dans les processus démarrés par `Process`, les connexions TCP provenant des clients sont maintenues dans le processus Principal, et le redémarrage des processus Worker et leur sortie anormale n'affectent pas les connexions elles-mêmes.

    * **Mode Base**
    
        Dans le mode Base, les connexions des clients sont directement maintenues dans les processus Worker, donc lors du redémarrage, toutes les connexions sont interrompues.

    !> Le mode Base ne prend pas en charge le redémarrage des processus [Task](/learn?id=taskworkerprocess)
    
    * **Portée efficace du redémarrage**

      L'opération de redémarrage ne peut recharger que les fichiers PHP chargés après le démarrage des processus Worker. Utilisez la fonction `get_included_files` pour obtenir l'列表 des fichiers qui ont été chargés avant le démarrage du processus Worker (`WorkerStart`). Les fichiers PHP dans cette liste ne peuvent pas être rechargés même après un redémarrage. Pour que cela prenne effet, il est nécessaire d'arrêter et de redémarrer le serveur.

    ```php
    $serv->on('WorkerStart', function(Swoole\Server $server, int $workerId) {
        var_dump(get_included_files()); // Ces fichiers sont chargés avant le démarrage du processus, donc ils ne peuvent pas être rechargés par le redémarrage
    });
    ```

    * **APC/OPcache**
    
        Si la configuration PHP `APC/OPcache` est activée, le redémarrage des chargements sera affecté. Il y a deux solutions.
        
        * Activer la surveillance de `stat` pour `APC/OPcache`, si des fichiers sont mis à jour, `APC/OPcache` met automatiquement à jour l'OPCode.
        * Avant de charger des fichiers (require, include, etc.) dans `onWorkerStart`, exécutez `apc_clear_cache` ou `opcache_reset` pour rafraîchir le cache de l'OPCode.

  * **Remarque**

    !> - Le redémarrage fluide est seulement efficace pour les fichiers PHP inclus/requêtés dans le processus Worker, tels que [onWorkerStart](/server/events?id=onworkerstart) ou [onReceive](/server/events?id=onreceive).
    - Les fichiers PHP inclus/requêtés avant le démarrage du serveur ne peuvent pas être rechargés par le redémarrage fluide.
    - Pour les paramètres de configuration du serveur, c'est-à-dire les paramètres passés à `$serv->set()`, il est nécessaire d'arrêter/redémarrer tout le serveur pour les recharger.
    - Le serveur peut écouter un port interne et recevoir des commandes de contrôle à distance pour redémarrer tous les processus Worker.
## stop()

Arrêtez le processus actuel de `Worker`, et déclenchez immédiatement la fonction de rappel `onWorkerStop`.

```php
Swoole\Server->stop(int $workerId = -1, bool $waitEvent = false): bool
```

  * **Paramètres**

    * `int $workerId`

      * Fonction : Spécifier l' `id worker`
      * Valeur par défaut : -1, représentant le processus actuel
      * Autres valeurs : aucune

    * `bool $waitEvent`

      * Fonction : Contrôle la stratégie de sortie, `false` signifie quitter immédiatement, `true` signifie attendre que le cycle des événements soit vide avant de sortir
      * Valeur par défaut : false
      * Autres valeurs : true

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, retourne `false` si l'opération échoue

  * **Avis**

    !> - Les serveurs d'I/O asynchrones ([/learn?id=同步io异步io]) peuvent encore avoir des événements en attente lorsqu'ils appellent `stop` pour quitter un processus. Par exemple, si vous avez utilisé `Swoole\MySQL->query`, envoyé une `SQL` statement, mais que vous attendez toujours la réponse du serveur MySQL. Dans ce cas, si le processus est forcé de sortir, les résultats de l'exécution de la `SQL` seront perdus.  
    - En mettant `$waitEvent = true`, la couche inférieure utilisera la stratégie de redémarrage sécurisée asynchrone ([/question/use?id=swoole如何正确的重启服务]). D'abord, notifiez le processus `Manager`, redémarrez un nouveau `Worker` pour gérer de nouveaux demandes. Le vieux `Worker` attendra les événements jusqu'à ce que le cycle des événements soit vide ou qu'il dépasse `max_wait_time`, puis quitte le processus, garantissant au maximum la sécurité des événements asynchrones.


## shutdown()

Ferme le service.

```php
Swoole\Server->shutdown(): bool
```

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, retourne `false` si l'opération échoue

  * **Avis**

    * Cette fonction peut être utilisée à l'intérieur d'un processus `Worker`.
    * Envoyer un `SIGTERM` au processus principal peut également être utilisé pour fermer le service.

```shell
kill -15 PID du processus principal
```


## tick()

Ajoute un minuteur `tick`, vous pouvez personnaliser la fonction de rappel. Cette fonction est l'alias de [Swoole\Timer::tick](/timer?id=tick).

```php
Swoole\Server->tick(int $millisecond, callable $callback): void
```

  * **Paramètres**

    * `int $millisecond`

      * Fonction : Intervalle de temps 【millisecond】
      * Valeur par défaut : aucune
      * Autres valeurs : aucune

    * `callable $callback`

      * Fonction : Rappel de fonction
      * Valeur par défaut : aucune
      * Autres valeurs : aucune

  * **Note**
  
    !> - Après la fin du processus `Worker`, tous les minuteurs sont automatiquement détruits  
    - Les minuteurs `tick/after` ne peuvent pas être utilisés avant `Server->start`  
    - Après `Swoole5`, cette méthode d'alias a été supprimée, veuillez utiliser directement `Swoole\Timer::tick()`

  * **Exemple**

    * Utilisé dans [onReceive](/server/events?id=onreceive)

    ```php
    function onReceive(Swoole\Server $server, int $fd, int $reactorId, mixed $data)
    {
        $server->tick(1000, function () use ($server, $fd) {
            $server->send($fd, "hello world");
        });
    }
    ```

    * Utilisé dans [onWorkerStart](/server/events?id=onworkerstart)

    ```php
    function onWorkerStart(Swoole\Server $server, int $workerId)
    {
        if (!$server->taskworker) {
            $server->tick(1000, function ($id) {
              var_dump($id);
            });
        } else {
            //task
            $server->tick(1000);
        }
    }
    ```


## after()

Ajoute un minuteur à usage unique, qui sera détruit après son exécution. Cette fonction est l'alias de [Swoole\Timer::after](/timer?id=after).

```php
Swoole\Server->after(int $millisecond, callable $callback)
```

  * **Paramètres**

    * `int $millisecond`

      * Fonction : Temps d'exécution 【millisecond】
      * Valeur par défaut : aucune
      * Autres valeurs : aucune
      * Impact de la version : Dans les versions inférieures à `Swoole v4.2.10`, le maximum ne doit pas dépasser `86400000`

    * `callable $callback`

      * Fonction : Rappel de fonction, doit être callable, la fonction `callback` n'accepte aucun argument
      * Valeur par défaut : aucune
      * Autres valeurs : aucune

  * **Note**
  
    !> - La durée de vie d'un minuteur est au niveau du processus, lorsque vous utilisez `reload` ou `kill` pour redémarrer ou fermer un processus, tous les minuteurs sont détruits  
    - Si certaines minuteurs contiennent des logiques clés et des données, veuillez les implémenter dans la fonction de rappel `onWorkerStop`, ou consulter [comment redémarrer correctement le service](/question/use?id=swoole如何正确的重启服务)  
    - Après `Swoole5`, cette méthode d'alias a été supprimée, veuillez utiliser directement `Swoole\Timer::after()`


## defer()

Postponez l'exécution d'une fonction, c'est l'alias de [Swoole\Event::defer](/event?id=defer).

```php
Swoole\Server->defer(Callable $callback): void
```

  * **Paramètres**

    * `Callable $callback`

      * Fonction : Rappel de fonction 【obligatoire】, peut être une variable de fonction exécutable, une chaîne, un tableau, une fonction anonyme
      * Valeur par défaut : aucune
      * Autres valeurs : aucune

  * **Note**

    !> - La fonction `defer` est exécutée après la fin du cycle de l'EventLoop ([/learn?id=什么是eventloop]). Son but est de permettre à certains codes PHP d'être exécutés en retard, la priorité est donnée aux autres événements `IO`. Par exemple, si une fonction de rappel a un calcul CPU intense et n'est pas très urgente, vous pouvez laisser le processus traiter d'autres événements avant de faire le calcul CPU intense  
    - La couche inférieure ne garantit pas que la fonction `defer` sera exécutée immédiatement, si c'est une logique clé du système qui doit être exécutée rapidement, veuillez utiliser le minuteur `after` pour l'implémenter  
    - Lors de l'exécution de `defer` dans la fonction de rappel `onWorkerStart`, il est nécessaire d'attendre qu'un événement se produise avant de faire le rappel
    - Après `Swoole5`, cette méthode d'alias a été supprimée, veuillez utiliser directement `Swoole\Event::defer()`

  * **Exemple**

```php
function query($server, $db) {
    $server->defer(function() use ($db) {
        $db->close();
    });
}
```


## clearTimer()

Effacez le minuteur `tick/after`, cette fonction est l'alias de [Swoole\Timer::clear](/timer?id=clear).

```php
Swoole\Server->clearTimer(int $timerId): bool
```

  * **Paramètres**

    * `int $timerId`

      * Fonction : Spécifier l' `id` du minuteur
      * Valeur par défaut : aucune
      * Autres valeurs : aucune

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, retourne `false` si l'opération échoue

  * **Note**

    !> - `clearTimer` ne peut être utilisé que pour effacer les minuteurs du processus actuel     
    - Après `Swoole5`, cette méthode d'alias a été supprimée, veuillez utiliser directement `Swoole\Timer::clear()` 

  * **Exemple**

```php
$timerId = $server->tick(1000, function ($timerId) use ($server) {
    $server->clearTimer($timerId);//$id est l'id du minuteur
});
```


## close()

Ferme la connexion client.

```php
Swoole\Server->close(int $fd, bool $reset = false): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Spécifier le `fd` (fichier décrit) à fermer
      * Valeur par défaut : aucune
      * Autres valeurs : aucune

    * `bool $reset`

      * Fonction : Set to `true` to forcefully close the connection, discarding data in the send queue
      * Valeur par défaut : false
      * Autres valeurs : true

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, retourne `false` si l'opération échoue

  * **Note**
  !> Le fait pour le `serveur` de fermer activement la connexion déclenche également l'événement [onClose](/server/events?id=onclose)  

- Ne pas écrire de logique de nettoyage après la fermeture. Il faut la placer dans la callback [onClose](/server/events?id=onclose) pour traitement  

- Le `fd` du `HTTP\Server` est obtenu dans la méthode de rappel de réponse de l'étage supérieur

  * **Exemple**

```php
$server->on('request', function ($request, $response) use ($server) {
    $server->close($response->fd);
});
```


## send()

Envoi de données au client.

```php
Swoole\Server->send(int|string $fd, string $data, int $serverSocket = -1): bool
```

  * **Paramètres**

    * `int|string $fd`

      * Fonction : Specifier le descripteur de fichier du client ou le chemin du socket UNIX
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `string $data`

      * Fonction : Données à envoyer, la taille maximale pour le protocol TCP ne doit pas dépasser `2M`, la taille maximale autorisée à envoyer peut être modifiée en changeant [buffer_output_size](/server/setting?id=buffer_output_size)
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `int $serverSocket`

      * Fonction : Necessaire pour envoyer des données à l'autre extrémité d'un [socket UNIX DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php), pas nécessaire pour les clients TCP
      * Valeur par défaut : -1, représentant le port UDP actuellement en écoute
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, `false` sinon

  * **Remarques**

    !> Le processus d'envoi est asynchrone, le niveau inférieur surveille automatiquement la writable et envoie progressivement les données au client, ce qui signifie que le client n'a pas reçu les données immédiatement après le retour de `send`.

    * Sécurité
      * L'opération `send` est atomique, si plusieurs processus appellent simultanément `send` pour envoyer des données à la même connexion TCP, il n'y aura pas de mélange de données

    * Limites de longueur
      * Si vous devez envoyer des données dépassant `2M`, vous pouvez écrire les données dans un fichier temporaire, puis utiliser l'interface `sendfile` pour les envoyer
      * La taille maximale d'envoi peut être modifiée en setting [buffer_output_size](/server/setting?id=buffer_output_size)
      * Lors de l'envoi de données dépassant `8K`, le niveau inférieur active la mémoire partagée des processus Worker, nécessitant une opération de verrouillage `Mutex->lock`

    * Cache
      * Lorsque le cache de socket UNIX du processus Worker est plein, l'envoi de `8K` de données active le stockage dans un fichier temporaire
      * Si vous envoiez une grande quantité de données à la même client à plusieurs reprises, le client ne pourra pas les recevoir en temps opportun, ce qui peut remplir le cache de socket, et le niveau inférieur de Swoole retournera immédiatement `false`. Lorsque `false`, vous pouvez sauver les données sur le disque, attendre que le client ait reçu toutes les données envoyées avant de procéder à nouveau à l'envoi

    * [Schémas de coroutines](/coroutine?id=schémas-de-coroutines)
      * Lorsque le mode de coroutines est activé avec [send_yield](/server/setting?id=send_yield), `send` s'arrêtera automatiquement lorsque le cache est plein, et reprendra la coroutine une fois que la partie de la donnée a été lue par l'autre extrémité, et continuera d'envoyer les données.

    * [Socket UNIX](/learn?id=qu'est-ce-que-ipc)
      * Lors de l'écoute du port [socket UNIX DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php), vous pouvez utiliser `send` pour envoyer des données à l'autre extrémité.

      ```php
      $server->on("packet", function (Swoole\Server $server, $data, $addr){
          $server->send($addr['address'], 'SUCCESS', $addr['server_socket']);
      });
      ```


## sendfile()

Envoi de fichier à la connexion client TCP.

```php
Swoole\Server->sendfile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Specifier le descripteur de fichier du client
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `string $filename`

      * Fonction : Chemin du fichier à envoyer, si le fichier n'existe, retourne `false`
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `int $offset`

      * Fonction : Specifier l'offset du fichier, vous pouvez commencer à envoyer les données à un certain emplacement dans le fichier
      * Valeur par défaut : 0 【Par défaut `0`, signifie commencer à envoyer à la tête du fichier】
      * Autres valeurs : Aucun

    * `int $length`

      * Fonction : Specifier la longueur de l'envoi
      * Valeur par défaut : Taille du fichier
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, `false` sinon

  * **Remarques**

  !> Cette fonction, comme `Server->send`, envoie des données au client, mais la différence est que les données de `sendfile` proviennent d'un fichier spécifié


## sendto()

Envoi de paquets UDP à tout client `IP:PORT`.

```php
Swoole\Server->sendto(string $ip, int $port, string $data, int $serverSocket = -1): bool
```

  * **Paramètres**

    * `string $ip`

      * Fonction : Specifier l' `ip` du client
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

      ?> `$ip` est une chaîne de caractères `IPv4` ou `IPv6`, comme `192.168.1.102`. Si l' `IP` est invalide, il retournera une erreur

    * `int $port`

      * Fonction : Specifier le `port` du client
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

      ?> `$port` est un numéro de port réseau de `1-65535`, si le port est incorrect, l'envoi échouera

    * `string $data`

      * Fonction : Contenu des données à envoyer, qui peut être du texte ou un contenu binaire
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `int $serverSocket`

      * Fonction : Specifier le port `server_socket` utilisé pour envoyer les paquets de données correspondant 【peut être obtenu dans la propriété `$clientInfo` de l'événement [onPacket](/server/events?id=onpacket)】
      * Valeur par défaut : -1, représentant le port UDP actuellement en écoute
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, `false` sinon

      ?> Le serveur peut écouter plusieurs ports UDP simultanément, voir [écoute de plusieurs ports](/server/port), ce paramètre peut specifier quel port utiliser pour envoyer les paquets de données

  * **Remarques**

  !> Vous devez écouter un port UDP pour pouvoir envoyer des données à une adresse IPv4  
  Vous devez écouter un port UDP6 pour pouvoir envoyer des données à une adresse IPv6

  * **Exemple**

```php
// Envoi de la chaîne "hello world" au serveur hôte avec l'adresse IP 220.181.57.216 et le port 9502.
$server->sendto('220.181.57.216', 9502, "hello world");
// Envoi de paquets UDP à un serveur IPv6
$server->sendto('2600:3c00::f03c:91ff:fe73:e98f', 9501, "hello world");
```


## sendwait()

Envoi synchrone de données au client.

```php
Swoole\Server->sendwait(int $fd, string $data): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Specifier le descripteur de fichier du client
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `string $data`

      * Fonction : Données à envoyer
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, `false` sinon

  * **Remarques**

    * Il existe certaines situations particulières où le `serveur` doit envoyer des données continuellement au client, mais l'interface de send de `Server->send` est purement asynchrone, et l'envoi de grandes quantités de données peut remplir la queue de stockage en mémoire.

    * L'utilisation de `Server->sendwait` peut résoudre ce problème, `Server->sendwait` attendra que la connexion soit writable. Il ne retournera que lorsque les données auront été envoyées.

  * **Remarques**

  !> Actuellement, `sendwait` ne peut être utilisé que dans le mode [SWOOLE_BASE](/learn?id=swoole_base)  
  `sendwait` est uniquement utilisé pour la communication locale ou Intranet, n'utilisez pas `sendwait` pour les connexions Internet, et ne utilisez pas cette fonction lorsque `enable_coroutine` est true (désactivé par défaut), cela peut bloquer les autres coroutines, seule une server synchrone peut l'utiliser.
## envoyerMessage()

Envoie un message à tout processus `Worker` ou [Processus Task](/apprendres?id=processus-task).可可调用的非 processus principal et processus de gestion. Le processus ayant reçu le message déclenche l'événement `onPipeMessage`.

```php
Swoole\Server->envoyerMessage(mixed $message, int $workerId): bool
```

  * **Paramètres**

    * `mixed $message`

      * Fonction : Contenu du message à envoyer, sans limite de longueur, mais si elle dépasse `8K`, un fichier temporaire de mémoire est démarré
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `int $workerId`

      * Fonction : `ID` du processus cible, pour la plage, voir [$worker_id](/server/properties?id=worker_id)
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Conseils**

    * Appeler `envoyerMessage` à l'intérieur d'un processus `Worker` est une [IO asynchrone](/apprendres?id=io-synchrone-io-asynchrone), le message est d'abord stocké dans le tampon, il est possible d'envoyer ce message via le [unixSocket](/apprendres?id=qu'est-ce-que-le-ipc) lorsque le tampon est écritable
    * Appeler `envoyerMessage` à l'intérieur d'un [Processus Task](/apprendres?id=processus-task) est généralement une [IO synchrone](/apprendres?id=io-synchrone-io-asynchrone), mais certaines situations peuvent automatiquement se transformer en IO asynchrone, voir [Conversion de l'IO synchrone en IO asynchrone](/apprendres?id=conversion-de-l-io-synchrone-en-io-asynchrone)
    * Appeler `envoyerMessage` à l'intérieur d'un [Processus Utilisateur](/server/méthodes?id=addprocess) est comme pour les Tasks, par défaut synchrone bloquant, voir [Conversion de l'IO synchrone en IO asynchrone](/apprendres?id=conversion-de-l-io-synchrone-en-io-asynchrone)

  * **Remarques**


  !> - Si `envoyerMessage()` est une [IO asynchrone](/apprendres?id=io-synchrone-io-asynchrone), si le processus distant refuse de recevoir des données pour diverses raisons, ne continuez pas à appeler `envoyerMessage()`, cela peut entraîner une occupation importante des ressources mémoire. Vous pouvez ajouter un mécanisme de réponse, si le côté distant ne répond pas, arrêtez temporairement l'appel ;  

- Sous `MacOS/FreeBSD`, si elle dépasse `2K`, elle utilisera un fichier temporaire pour le stockage ;  

- Pour utiliser [envoyerMessage](/server/méthodes?id=sendMessage), vous devez enregistrer la fonction de rappel `onPipeMessage` ;  
- Si vous avez mis [task_ipc_mode](/server/settings?id=task_ipc_mode) = 3, vous ne pourrez pas utiliser [sendMessage](/server/méthodes?id=sendMessage) pour envoyer des messages à un processus Task spécifique.

  * **Exemple**

```php
$server = new Swoole\Server('0.0.0.0', 9501);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 2,
));
$server->on('pipeMessage', function ($server, $src_worker_id, $data) {
    echo "#{$server->worker_id} message from #$src_worker_id: $data\n";
});
$server->on('task', function ($server, $task_id, $src_worker_id, $data) {
    var_dump($task_id, $src_worker_id, $data);
});
$server->on('finish', function ($server, $task_id, $data) {

});
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    if (trim($data) == 'task') {
        $server->task("async task coming");
    } else {
        $worker_id = 1 - $server->worker_id;
        $server->sendMessage("hello task process", $worker_id);
    }
});

$server->start();
```


## exister()

Vérifie si la connexion correspondant au `fd` existe.

```php
Swoole\Server->exister(int $fd): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Descriptor de fichier
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeur de retour**

    * Retourne `true` si elle existe, sinon `false`

  * **Conseils**
  
    * Cette interface est calculée sur la base de la mémoire partagée, sans aucune opération `IO`


## mettre en pause()

Arrête la réception de données.

```php
Swoole\Server->mettreEnPause(int $fd): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Désigne le descriptor de fichier
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeur de retour**

    * Retourne `true` si l'opération est réussie, sinon `false`

  * **Conseils**

    * Après avoir appelé cette fonction, la connexion est retirée de l'[EventLoop](/apprendres?id=qu'est-ce-que-c'est-l'eventloop)', elle ne recevra plus de données du client.
    * Cette fonction n'affecte pas le traitement de la file d'attente d'envoi
    * Seulement en mode `SWOOLE_PROCESS`, après avoir appelé `mettre en pause`, il est possible que certaines données aient déjà atteint le processus `Worker`, donc l'événement [onReceive](/server/evenements?id=onreceive) peut toujours être déclenché


## reprendre()

Récupère la réception de données. Utilisé en couple avec la méthode `mettre en pause`.

```php
Swoole\Server->reprendre(int $fd): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Désigne le descriptor de fichier
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeur de retour**

    * Retourne `true` si l'opération est réussie, sinon `false`

  * **Conseils**

    * Après avoir appelé cette fonction, la connexion est de nouveau ajoutée à l'[EventLoop](/apprendres?id=qu'est-ce-que-c'est-l'eventloop)', et la réception de données du client reprend


## getCallback()

Obtient la fonction de rappel nommée spécifiquement pour le serveur Server

```php
Swoole\Server->getCallback(string $event_name): \Closure|string|null|array
```

  * **Paramètres**

    * `string $event_name`

      * Fonction : Nom de l'événement, pas besoin de préfixe `on`, insensible à la casse
      * Valeur par défaut : Aucun
      * Autres valeurs : Voir [Événements](/server/evenements)

  * **Valeur de retour**

    * Si la fonction de rappel correspondante existe, elle retourne `Closure` / `string` / `array` selon les différentes façons de configurer la fonction de rappel [](/apprendres?id=quatre-façons-de-configurer-la-fonction-de-rappels)
    * Si la fonction de rappel correspondante n'existe pas, elle retourne `null`


## getClientInfo()

Obtient les informations de connexion, également appelé `Swoole\Server->connection_info()`

```php
Swoole\Server->getClientInfo(int $fd, int $reactorId = -1, bool $ignoreError = false): false|array
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Désigne le descriptor de fichier
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `int $reactorId`

      * Fonction : `ID` du thread [Reactor](/apprendres?id=reactor-thread) où se trouve la connexion, actuellement sans fonction, juste pour maintenir la compatibilité API
      * Valeur par défaut : -1
      * Autres valeurs : Aucun

    * `bool $ignoreError`

      * Fonction : Si la valeur est `true`, même si la connexion est fermée, les informations de connexion sont retournées, si `false`, la fonction retourne `false` si la connexion est fermée
      * Valeur par défaut : false
      * Autres valeurs : Aucun

  * **Conseils**

    * Certificat client

      * Seulement dans les processus déclenchés par [onConnect](/server/evenements?id=onconnect) peut-on obtenir le certificat
      * Format `x509`, la fonction `openssl_x509_parse` peut être utilisée pour obtenir les informations du certificat

    * Lorsque la configuration [dispatch_mode](/server/settings?id=dispatch_mode) = 1/3 est utilisée, étant donné que cette stratégie de distribution de paquets est utilisée pour des services sans état, lorsque la connexion est fermée, les informations connexes sont directement supprimées de la mémoire, donc `Server->getClientInfo` ne peut pas obtenir les informations de connexion connexes.

  * **Valeur de retour**

    * Si l'appel échoue, il retourne `false`
    * Si l'appel réussit, il retourne un `array` contenant les informations du client

```php
$fd_info = $server->getClientInfo($fd);
var_dump($fd_info);

array(15) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(25)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(39136)
  ["remote_ip"]=>
  string(9) "127.0.0.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1677322106)
  ["last_time"]=>
  int(1677322106)
  ["last_recv_time"]=>
  float(1677322106.901918)
  ["last_send_time"]=>
  float(0)
  ["last_dispatch_time"]=>
  float(0)
  ["close_errno"]=>
  int(0)
  ["recv_queued_bytes"]=>
  int(78)
  ["send_queued_bytes"]=>
  int(0)
}
```
Les paramètres et leurs fonctions sont les suivants :

| Paramètre | Function |
| --- | --- |
| server_port | Port sur lequel le serveur écoute |
| server_fd | Fd du serveur |
| socket_fd | Fd du client |
| socket_type | Type de socket |
| remote_port | Port du client |
| remote_ip | IP du client |
| reactor_id | ID du thread Reactor qui a créé la connexion |
| connect_time | Temps de connexion du client au serveur en secondes, défini par le processus maître |
| last_time | Dernière fois que des données ont été reçues en secondes, défini par le processus maître |
| last_recv_time | Dernière fois que des données ont été reçues en secondes, défini par le processus maître |
| last_send_time | Dernière fois que des données ont été envoyées en secondes, défini par le processus maître |
| last_dispatch_time | Temps où le processus worker a reçu des données |
| close_errno | Code d'erreur de fermeture de la connexion, non nul si la connexion est fermée de manière anormale, peut être référencé dans la liste des erreurs Linux |
| recv_queued_bytes | Quantité de données en attente de traitement |
| send_queued_bytes | Quantité de données en attente d'envoi |
| websocket_status | [Optionnel] État de la connexion WebSocket, ajouté si le serveur est un Swoole\WebSocket\Server |
| uid | [Optionnel] Si une connexion est liée à un UID utilisateur avec bind, cette information est ajoutée |
| ssl_client_cert | [Optionnel] Si une connexion est chiffrée avec un tunnel SSL et que le client a un certificat, cette information est ajoutée |

## getClientList()

Itère sur toutes les connexions clients actuelles du `Server`. La méthode `Server::getClientList` est basée sur la mémoire partagée et ne nécessite pas d'attente pour I/O, ce qui rend l'itération très rapide. De plus, `getClientList` retourne toutes les connexions TCP, pas seulement celles du processus Worker actuel. Son surnom est `Swoole\Server->connection_list()`.

```php
Swoole\Server->getClientList(int $start_fd = 0, int $pageSize = 10): false|array
```

  * **Paramètres**

    * `int $start_fd`

      * Fonction : Désigne le fd de départ
      * Valeur par défaut : 0
      * Autres valeurs : none

    * `int $pageSize`

      * Fonction : Nombre de éléments par page, maximum de 100
      * Valeur par défaut : 10
      * Autres valeurs : none

  * **Valeurs de retour**

    * Si la fonction réussit, elle retourne un tableau d'indices numériques, dont les éléments sont les fd obtenus. Le tableau est trié de la plus petite à la plus grande. Le dernier fd sert de nouveau de fd de départ pour une nouvelle tentative d'obtention
    * Si la fonction échoue, elle retourne `false`

  * **Conseils**

    * Il est recommandé d'utiliser l'itérateur [Server::$connections](/server/properties?id=connections) pour itérer sur les connexions
    * `getClientList` ne peut être utilisé que pour les clients TCP, les serveurs UDP doivent conserver eux-mêmes les informations des clients
    * Dans le mode [SWOOLE_BASE](/learn?id=swoole_base), seules les connexions du processus actuel peuvent être obtenues

  * **Exemple**
  
```php
$start_fd = 0;
while (true) {
  $conn_list = $server->getClientList($start_fd, 10);
  if ($conn_list === false || count($conn_list) === 0) {
      echo "finish\n";
      break;
  }
  $start_fd = end($conn_list);
  var_dump($conn_list);
  foreach ($conn_list as $fd) {
      $server->send($fd, "broadcast");
  }
}
```

## bind()

Ligue une connexion à un UID défini par l'utilisateur, permettant de configurer la [dispatch_mode](/server/setting?id=dispatch_mode)=5 pour une répartition fixe par hachage. Cela garantit que toutes les connexions d'un même UID seront attribuées au même processus Worker.

```php
Swoole\Server->bind(int $fd, int $uid): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Désigne le fd de la connexion
      * Valeur par défaut : none
      * Autres valeurs : none

    * `int $uid`

      * Fonction : UID à lier, doit être un nombre non nul
      * Valeur par défaut : none
      * Autres valeurs : Le UID ne doit pas dépasser 4294967295, ni être inférieur à -2147483648

  * **Valeurs de retour**

    * Retourne `true` si l'opération réussit, `false` sinon

  * **Conseils**

    * Utilisez `$serv->getClientInfo($fd)` pour afficher la valeur de l'UID lié à la connexion
    * Dans la configuration par défaut de [dispatch_mode](/server/setting?id=dispatch_mode)=2, le `Server` répartit les données des connexions entre différents processus Worker en fonction du fd socket. Comme le fd est instable, si un client se déconnecte et se reconnecte, le fd change et les données de ce client peuvent être attribuées à un autre Worker. L'utilisation de `bind` permet ensuite de répartir selon le UID défini par l'utilisateur. Même après une reconnexion, les données des connexions TCP du même UID seront attribuées au même processus Worker.

    * Question de temps

      * Après la connexion du client au serveur, si plusieurs paquets sont envoyés consécutivement, il peut y avoir un problème de temps. Lors de l'opération `bind`, les paquets suivants peuvent déjà être `dispatchés` et seront toujours attribués au processus actuel selon le fd. Seuls les paquets reçus après `bind` seront attribués selon le UID.
      * Par conséquent, si vous utilisez le mécanisme `bind`, votre protocole de communication réseau doit inclure une étape de handshake. Après une connexion réussie du client, envoiez d'abord une demande de handshake, puis ne envoyez plus aucun paquet par le client. Après que le serveur a répondu à la demande de handshake, envoiez une nouvelle demande.

    * Reconnnexion avec un nouveau UID

      * Dans certains cas, la logique métier peut nécessiter que le client se reconnecte et lie un nouveau UID. Dans ce cas, il est possible de couper la connexion, de se reconnecter avec un nouveau TCP et de réaliser un handshake pour lier un nouveau UID.

    * Liaison d'un UID négatif

      * Si le UID lié est négatif, il sera converti en un entier Unsigned 32 bits par le niveau inférieur, et le niveau PHP doit le transformer en un entier Signed 32 bits. Utilisez la suivante syntaxe :
      
  ```php
  $uid = -10;
  $server->bind($fd, $uid);
  $bindUid = $server->connection_info($fd)['uid'];
  $bindUid = $bindUid >> 31 ? (~($bindUid - 1) & 0xFFFFFFFF) * -1 : $bindUid;
  var_dump($bindUid === $uid);
  ```

  * **Note**


!> - Seulement valide lorsque `dispatch_mode=5` est configuré  

- Si aucun UID n'est lié, la répartition par défaut utilise le fd pour le modulo  

- Une connexion ne peut être liée qu'une seule fois, si un UID est déjà lié, une nouvelle invocation de `bind` retournera `false`

  * **Exemple**

```php
$serv = new Swoole\Server('0.0.0.0', 9501);

$serv->fdlist = [];

$serv->set([
    'worker_num' => 4,
    'dispatch_mode' => 5,   //uid dispatch
]);

$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "{$fd} connect, worker:" . $serv->worker_id . PHP_EOL;
});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    $conn = $serv->connection_info($fd);
    print_r($conn);
    echo "worker_id: " . $serv->worker_id . PHP_EOL;
    if (empty($conn['uid'])) {
        $uid = $fd + 1;
        if ($serv->bind($fd, $uid)) {
            $serv->send($fd, "bind {$uid} success");
        }
    } else {
        if (!isset($serv->fdlist[$fd])) {
            $serv->fdlist[$fd] = $conn['uid'];
        }
        print_r($serv->fdlist);
        foreach ($serv->fdlist as $_fd => $uid) {
            $serv->send($_fd, "{$fd} say:" . $data);
        }
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "{$fd} Close". PHP_EOL;
    unset($serv->fdlist[$fd]);
});

$serv->start();
```
## stats()

Obtenir le nombre actuel de connexions TCP actives du `Serveur`, la date de démarrage, ainsi que d'autres informations telles que le nombre total d'acceptations/fermetures de connexions (établissement de connexion/fermeture de connexion).

```php
Swoole\Server->stats(): array
```

  * **Exemple**

```php
array(25) {
  ["start_time"]=>
  int(1677310656)
  ["connection_num"]=>
  int(1)
  ["abort_count"]=>
  int(0)
  ["accept_count"]=>
  int(1)
  ["close_count"]=>
  int(0)
  ["worker_num"]=>
  int(2)
  ["task_worker_num"]=>
  int(4)
  ["user_worker_num"]=>
  int(0)
  ["idle_worker_num"]=>
  int(1)
  ["dispatch_count"]=>
  int(1)
  ["request_count"]=>
  int(0)
  ["response_count"]=>
  int(1)
  ["total_recv_bytes"]=>
  int(78)
  ["total_send_bytes"]=>
  int(165)
  ["pipe_packet_msg_id"]=>
  int(3)
  ["session_round"]=>
  int(1)
  ["min_fd"]=>
  int(4)
  ["max_fd"]=>
  int(25)
  ["worker_request_count"]=>
  int(0)
  ["worker_response_count"]=>
  int(1)
  ["worker_dispatch_count"]=>
  int(1)
  ["task_idle_worker_num"]=>
  int(4)
  ["tasking_num"]=>
  int(0)
  ["coroutine_num"]=>
  int(1)
  ["coroutine_peek_num"]=>
  int(1)
  ["task_queue_num"]=>
  int(1)
  ["task_queue_bytes"]=>
  int(1)
}
```

  * **Paramètres**

    * `start_time` : La date et l'heure à laquelle le serveur a été démarré
    * `connection_num` : Le nombre actuel de connexions
    * `abort_count` : Le nombre de connexions rejetées
    * `accept_count` : Le nombre de connexions acceptées
    * `close_count` : Le nombre de connexions fermées
    * `worker_num` : Le nombre de processus worker démarrés
    * `task_worker_num` : Le nombre de processus task worker démarrés [disponible à partir de la version `v4.5.7`]
    * `user_worker_num` : Le nombre de processus task worker démarrés
    * `idle_worker_num` : Le nombre de processus worker inactifs
    * `dispatch_count` : Le nombre de paquets envoyés par le serveur au worker [disponible à partir de la version `v4.5.7`, uniquement valide dans le mode [SWOOLE_PROCESS](/learn?id=swoole_process)]
    * `request_count` : Le nombre de demandes reçues par le serveur [le nombre de requests est calculé uniquement pour les quatre types de données de demande : onReceive, onMessage, onRequset, onPacket]
    * `response_count` : Le nombre de réponses envoyées par le serveur
    * `total_recv_bytes` : Le nombre total de bytes reçus
    * `total_send_bytes` : Le nombre total de bytes envoyés
    * `pipe_packet_msg_id` : L'ID du paquet de communication entre processus
    * `session_round` : L'ID de session initial
    * `min_fd` : Le plus petit ID de connexion
    * `max_fd` : Le plus grand ID de connexion
    * `worker_request_count` : Le nombre de demandes reçues par le processus worker actuel [si le nombre de worker_request_count dépasse max_request, le processus worker quittera]
    * `worker_response_count` : Le nombre de réponses envoyées par le processus worker actuel
    * `worker_dispatch_count` : Le nombre de tâches confiées par le processus maître au processus worker actuel, augmenté lorsque le processus maître effectue un dispatch [processus maître](/learn?id=reactor thread)
    * `task_idle_worker_num` : Le nombre de processus task inactifs
    * `tasking_num` : Le nombre de processus task en cours d'exécution
    * `coroutine_num` : Le nombre actuel de coroutines [utilisé pour Coroutine], pour plus d'informations, veuillez consulter [cette section](/coroutine/gdb)
    * `coroutine_peek_num` : Le nombre total de coroutines
    * `task_queue_num` : Le nombre de tâches dans la file d'attente [utilisé pour Task]
    * `task_queue_bytes` : La taille en bytes de la mémoire occupée par la file d'attente des tâches [utilisée pour Task]

## task()

Lancer une tâche asynchrone dans le pool de `task_worker`. Cette fonction est non bloquante et retourne immédiatement après l'exécution. Le processus `Worker` peut continuer à traiter de nouvelles demandes. Pour utiliser la fonction `Task`, il est nécessaire d'avoir d'abord défini `task_worker_num`, et il est impératif de configurer les fonctions de rappel d'événement [onTask](/server/events?id=ontask) et [onFinish](/server/events?id=onfinish) du `Server`.

```php
Swoole\Server->task(mixed $data, int $dstWorkerId = -1, callable $finishCallback): int
```

  * **Paramètres**

    * `mixed $data`

      * Fonction : Les données de la tâche à envoyer, qui doivent être des variables PHP sérialisables
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `int $dstWorkerId`

      * Fonction : Peut spécifier à quel [Processus Task](/learn?id=taskworkerprocess) envoyer la tâche, en fournissant l'ID du processus Task, qui doit être dans la plage `[0, $server->setting['task_worker_num']-1]`
      * Valeur par défaut : -1 [par défaut, `-1` signifie que la distribution est aléatoire, et le système choisira automatiquement un [Processus Task](/learn?id=taskworkerprocess) inactif]
      * Autres valeurs : `[0, $server->setting['task_worker_num']-1]`

    * `callable $finishCallback`

      * Fonction : La fonction de rappel `finish`, si une fonction de rappel est définie pour la tâche, la fonction de rappel spécifiée sera exécutée directement lorsque le résultat de la tâche est retourné, sans exécuter le rappel [onFinish](/server/events?id=onfinish) du serveur, et ce n'est que si la tâche est lancée dans un processus Worker que cela peut déclencher
      * Valeur par défaut : `null`
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Si la fonction réussit, elle retourne une valeur entière `$task_id`, qui représente l'ID de la tâche. Si une fonction de rappel est définie, le paramètre `$task_id` sera transmis dans le rappel [onFinish](/server/events?id=onfinish)
    * Si la fonction échoue, elle retourne `false`, et `$task_id` pourrait être `0`, donc il est nécessaire d'utiliser `===` pour vérifier l'échec

  * **Avis**

    * Cette fonction est utilisée pour exécuter asynchroneusement des tâches lentes, comme un serveur de chat, où il est possible d'utiliser pour envoyer des messages à tous les clients. Lorsque la tâche est terminée, appeler `$serv->finish("finish")` dans le processus [task worker](/learn?id=taskworkerprocess) informe le processus worker que cette tâche est terminée. Bien sûr, `Swoole\Server->finish` est optionnel.
    * Le `task` utilise en dessous la communication via [unixSocket](/learn?id=什么是IPC), qui est entièrement en mémoire et ne consomme pas d'IO. La performance de lecture/écriture par processus peut atteindre `1 million/s`, et différents processus utilisent des communications différentes via `unixSocket` pour maximiser l'utilisation des cœurs multiples.
    * Si l'ID du [Processus Task](/learn?id=taskworkerprocess) cible n'est pas spécifié, l'appel à la méthode `task` évalue l'état de charge des [Processus Task](/learn?id=taskworkerprocess), et le système ne délivrera des tâches qu'aux [Processus Task](/learn?id=taskworkerprocess) en état d'inactivité. Si tous les [Processus Task](/learn?id=taskworkerprocess) sont occupés, le système effectuera un Polling pour délivrer des tâches à chaque processus. Il est possible d'utiliser la méthode [server->stats](/server/methods?id=stats) pour obtenir le nombre actuel de tâches en attente dans la file d'attente.
    * Le troisième paramètre permet de définir directement la fonction [onFinish](/server/events?id=onfinish). Si une fonction de rappel est définie pour la tâche, la fonction de rappel spécifiée sera exécutée directement lorsque le résultat de la tâche est retourné, sans exécuter le rappel [onFinish](/server/events?id=onfinish) du serveur, et ce n'est que si la tâche est lancée dans un processus Worker que cela peut déclencher

    ```php
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    ```

    * `$task_id` est un entier de `0` à `4,294,967,295`, unique dans le processus actuel
    * Par défaut, la fonction `task` n'est pas démarrée, il est nécessaire de configurer manuellement `task_worker_num` pour activer cette fonction
    * Le nombre de `TaskWorker` peut être ajusté dans les paramètres de la méthode [Server->set](/server/methods?id=set), par exemple `task_worker_num => 64`, signifie démarrer `64` processus pour recevoir des tâches asynchrones

  * **Paramètres de configuration**

    * Les trois méthodes `Server->task/taskwait/finish` utiliseront un fichier temporaire pour stocker les données transmises lorsque la taille de `$data` dépasse `8K`. Lorsque le contenu du fichier temporaire dépasse
    [server->package_max_length](/server/setting?id=package_max_length), un avertissement sera lancé en dessous. Cet avertissement n'affecte pas le déploiement des données, mais une trop grande `Task` pourrait poser des problèmes de performance.
    
    ```shell
    WARN: task package is too big.
    ```

  * **Tâches unidirectionnelles**

    * Les tâches lancées depuis les processus `Master`, `Manager`, `UserProcess` sont unidirectionnelles et ne peuvent pas utiliser la méthode `return` ou `Server->finish()` dans le processus `TaskWorker` pour retourner des résultats de données.

  * **Note**
  !> -`méthode task` ne peut pas être appelée dans le processus [task](/apprendre?id=processus taskworker)  

- L'utilisation de `task` nécessite de configurer les回调 [onTask](/server/evenements?id=ontask) et [onFinish](/server/evenements?id=onfinish) pour le `Server`, sinon `Server->start` échouera  

- Le nombre d'opérations `task` doit être inférieur à la vitesse de traitement de [onTask](/server/evenements?id=ontask). Si la capacité de délivrance dépasse la capacité de traitement, les données de `task` rempliront le cache, bloquant le processus `Worker` et empêchant ce dernier d'accepter de nouvelles demandes.  

- Les processus utilisateur ajoutés avec [addProcess](/server/méthode?id=addProcess) peuvent utiliser `task` pour délivrer des tâches de manière unidirectionnelle, mais ne peuvent pas retourner de données de résultat. Veuillez utiliser l'interface [sendMessage](/server/méthodes?id=sendMessage) pour communiquer avec les processus `Worker/Task`  

  * **Exemple**

```php
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 4,
));

$server->on('Receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    echo "Réception de données" . $data . "\n";
    $data    = trim($data);
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Callback de tâche : ";
        var_dump($task_id, $data);
    });
    $task_id = $server->task($data, 0);
    $server->send($fd, "Distribution de tâche, ID de tâche : $task_id\n");
});

$server->on('Task', function (Swoole\Server $server, $task_id, $reactor_id, $data) {
    echo "Processus Tasker reçoit des données";
    echo "#{$server->worker_id}\tonTask : [PID={$server->worker_pid}]: ID de tâche=$task_id, longueur de données=" . strlen($data) . "." . PHP_EOL;
    $server->finish($data);
});

$server->on('Finish', function (Swoole\Server $server, $task_id, $data) {
    echo "Tâche#$task_id terminée, longueur de données=" . strlen($data) . PHP_EOL;
});

$server->on('workerStart', function ($server, $worker_id) {
    global $argv;
    if ($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]}: task_worker");
    } else {
        swoole_set_process_name("php {$argv[0]}: worker");
    }
});

$server->start();
```


## taskwait()

`taskwait` est similaire à la méthode `task` et sert à délivrer une tâche asynchrone dans le pool de [task进程](/apprendre?id=processus taskworker) pour exécution. Contrairement à `task`, `taskwait` est synchrone et attend jusqu'à ce que la tâche soit terminée ou qu'elle expire. `$result` est le résultat de l'exécution de la tâche, envoyé par la fonction `$server->finish`. Si cette tâche expire, ici il retournera `false`.

```php
Swoole\Server->taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1): mixed
```

  * **Paramètres**

    * `mixed $data`

      * Fonction : Données de la tâche à délivrer, qui peuvent être de n'importe quel type, et sera sérialisées automatiquement si elles ne sont pas de type chaîne.
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `float $timeout`

      * Fonction : Temps d'attente en secondes, de type flottant, avec une granularité minimale de `1ms`. Si les données ne sont pas retournées par le [Task进程](/apprendre?id=processus taskworker) dans le délai spécifié, `taskwait` retournera `false` et ne traitera plus les résultats des tâches suivantes.
      * Valeur par défaut : 0.5
      * Autres valeurs : Aucun

    * `int $dstWorkerId`

      * Fonction : Désigne le [Task进程](/apprendre?id=processus taskworker) auquel la tâche doit être envoyée. Il suffit d'entrer l'ID du processus Task, qui doit être dans la plage `[0, $server->setting['task_worker_num']-1]`.
      * Valeur par défaut : -1【Par défaut, `-1` signifie qu'une sélection aléatoire de processus Task est effectuée automatiquement】
      * Autres valeurs : `[0, $server->setting['task_worker_num']-1]`

  * **Valeurs de retour**

      * Retourne `false` en cas d'échec de la délivrance.
      * Si dans l'événement `onTask` une méthode `finish` ou `return` est exécutée, alors `taskwait` retournera le résultat de la délivrance de `onTask`.

  * **Avis**

    * **Mode de coroutines**

      * À partir de la version `4.0.4`, la méthode `taskwait` prend en charge la [schedulation de coroutines](/coroutine?id=schedulation de coroutines). Lorsque `Server->taskwait()` est appelée dans une coroutine, elle sera automatiquement planifiée en utilisant la [schedulation de coroutines](/coroutine?id=schedulation de coroutines) et ne sera plus bloquée en attente.
      * Grâce à la [schedulation de coroutines](/coroutine?id=schedulation de coroutines), `taskwait` peut réaliser des appels en parallèle.
      * Il ne peut y avoir qu'une seule `return` ou une seule `Server->finish` dans l'événement `onTask`, sinon les appels supplémentaires de `return` ou de `Server->finish` après leur exécution entraîneront un avertissement de task[1] has expired.

    * **Mode synchrone**

      * Dans le mode synchrone bloqué, `taskwait` utilise la communication via [UnixSocket](/apprendre?id=qu'est-ce que l'IPC) et la mémoire partagée pour retourner les données au processus Worker, ce qui est un blocage synchrone.

    * **Exemple particulier**

      * Si dans [onTask](/server/evenements?id=ontask) il n'y a aucune opération d'[IO synchronisé](/apprendre?id=io synchronisé asynchrone), il n'y aura qu'un coût de deux changements de processus en bas, sans attente d'IO, donc dans ce cas, `taskwait` peut être considéré comme non bloquant. Des tests réels montrent que lors de l'exécution de `taskwait` sur des arrays PHP, pour 100 000 opérations de `taskwait`, le temps total consommé n'est que de 1 seconde, avec une moyenne de 10 microsecondes par appel.

  * **Note**


  !> - Ne utilisez pas `Swoole\Server::finish` avec `taskwait`  
- La méthode `taskwait` ne peut pas être appelée dans le processus [task](/apprendre?id=processus taskworker)  


## taskWaitMulti()

Exécute-t-on plusieurs tâches asynchrones `task` en parallèle, cette méthode ne prend pas en charge la [schedulation de coroutines](/coroutine?id=schedulation de coroutines), ce qui entraînera le début d'autres coroutines.

```php
Swoole\Server->taskWaitMulti(array $tasks, float $timeout = 0.5): false|array
```

  * **Paramètres**

    * `array $tasks`

      * Fonction : Doit être un tableau indexé par des chiffres, ne prend pas en charge les tableaux indexés par des clés associatives, le bas-fonds parcourra `$tasks` pour envoyer les tâches une par une au [Task进程](/apprendre?id=processus taskworker)
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `float $timeout`

      * Fonction : De type flottant, en secondes
      * Valeur par défaut : 0,5 seconde
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Si les tâches sont terminées ou qu'elles ont expiré, retourne un tableau de résultats. L'ordre des résultats dans le tableau de résultats correspond à celui des tâches dans `$tasks`, par exemple : le résultat de `$tasks[2]` est dans `$result[2]`
    * L'expiration d'une tâche n'affectera pas les autres tâches, et les résultats de retour ne contiennent pas la tâche qui a expiré

  * **Note**

  !> - Le nombre maximal de tâches en parallèle ne doit pas dépasser `1024`

  * **Exemple**

```php
$tasks[] = mt_rand(1000, 9999); // Tâche 1
$tasks[] = mt_rand(1000, 9999); // Tâche 2
$tasks[] = mt_rand(1000, 9999); // Tâche 3
var_dump($tasks);

// Attendre que tous les résultats des tâches soient retournés, avec un délai de 10 secondes
$results = $server->taskWaitMulti($tasks, 10.0);

if (!isset($results[0])) {
    echo "La tâche 1 a expiré\n";
}
if (isset($results[1])) {
    echo "Le résultat de la tâche 2 est {$results[1]}\n";
}
if (isset($results[2])) {
    echo "Le résultat de la tâche 3 est {$results[2]}\n";
}
```
## taskCo()

Exécuter conjointement des `Task` et effectuer le [décalage de coroutines](/coroutine?id=décalage-de-coroutines) pour soutenir la fonction `taskWaitMulti` dans un environnement de coroutines.

```php
Swoole\Server->taskCo(array $tasks, float $timeout = 0.5): false|array
```
  
* `$tasks` liste des tâches, qui doit être un tableau. Le code sous-jacent parcourra le tableau et délivrera chaque élément comme une `task` au pool de processus `Task`
* `$timeout` temps d'attente, par défaut de `0.5` seconde. Si les tâches ne sont pas toutes terminées dans le délai imparti, elles sont immédiatement interrompues et le résultat est retourné
* Lorsque les tâches sont terminées ou qu'elles ont échoué par rapport au délai, un tableau de résultats est retourné. L'ordre des résultats dans ce tableau correspond à celui des tâches dans `$tasks`, par exemple : le résultat de `$tasks[2]` est `$result[2]`
* Si une tâche échoue ou dépasse le délai, l'élément correspondant dans le tableau de résultats est `false`, par exemple : si `$tasks[2]` échoue, alors la valeur de `$result[2]` est `false`

!> Le nombre maximal de tâches en cours de déploiement ne doit pas dépasser `1024`  

  * **Processus de planification**

    * Chaque tâche dans la liste `$tasks` est aléatoirement envoyée à un processus de travail `Task`. Après le déploiement, `yield` cède le contrôle actuel de la coroutine et établit un timer de `$timeout` secondes
    * Dans `onFinish`, collecter les résultats correspondants des tâches et les sauver dans un tableau de résultats. déterminer si toutes les tâches ont retourné des résultats. Si ce n'est pas le cas, continuer à attendre. Si c'est le cas, reprendre la coroutine correspondante pour redémarrer son exécution et effacer le timer de délai
    * Si les tâches ne sont pas toutes terminées dans le délai imparti, le timer est déclenché en premier, et l'état d'attente est effacé en dessous. Les résultats des tâches non terminées sont marqués comme `false` et la coroutine correspondante est immédiatement redémarrée

  * **Exemple**

```php
$server = new Swoole\Http\Server("127.0.0.1", 9502, SWOOLE_BASE);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $serv, $task_id, $worker_id, $data) {
    echo "#{$serv->worker_id}\tonTask: worker_id={$worker_id}, task_id=$task_id\n";
    if ($serv->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "hello world";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('Test End, Result: ' . var_export($result, true));
});

$server->start();
```


## finish()

Utilisé pour informer le processus `Worker` dans le [Processus Task](/learn?id=processus-task) que la tâche confiée est terminée. Cette fonction peut transmettre des données de résultat au processus `Worker`.

```php
Swoole\Server->finish(mixed $data): bool
```

  * **Paramètres**

    * `mixed $data`

      * Fonction : Contenu du résultat de l'exécution de la tâche
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Retourne `true` pour indiquer que l'opération a réussi, `false` pour indiquer qu'elle a échoué

  * **Notes**

    * La méthode `finish` peut être appelée plusieurs fois consécutives, et le processus `Worker` déclenche à plusieurs reprises l'événement [onFinish](/server/events?id=onfinish).
    * Après avoir appelé la méthode `finish` dans la fonction de rappel [onTask](/server/events?id=ontask), les données de retourne `return` déclencheront également l'événement [onFinish](/server/events?id=onfinish).
    * La méthode `Server->finish` est optionnelle. Si le processus `Worker` ne se soucie pas du résultat de l'exécution de la tâche, il n'est pas nécessaire d'appeler cette fonction.
    * Dans la fonction de rappel [onTask](/server/events?id=ontask), retourner une chaîne équivaut à appeler `finish`

  * **Remarques**

  !> L'utilisation de la fonction `Server->finish` doit nécessiter l'établissement d'un rappel [onFinish](/server/events?id=onfinish) pour le `Server`. Cette fonction ne peut être utilisée que dans le contexte du rappel [onTask](/server/events?id=ontask) du [Processus Task](/learn?id=processus-task)


## heartbeat()

Contrairement à la détection passive de [heartbeat_check_interval](/server/setting?id=heartbeat_check_interval), cette méthode détecte activement toutes les connexions du serveur et identifie celles qui ont dépassé le temps convenu. Si `if_close_connection` est spécifié, les connexions dépassées sont automatiquement fermées. Sans spécification, seule une liste des `fd` des connexions est retournée.

```php
Swoole\Server->heartbeat(bool $ifCloseConnection = true): bool|array
```

  * **Paramètres**

    * `bool $ifCloseConnection`

      * Fonction : Fermer automatiquement les connexions dépassées
      * Valeur par défaut : true
      * Autres valeurs : false

  * **Valeurs de retour**

    * Si la fonction réussit, elle retourne un tableau continu des `$fd` fermés
    * Si la fonction échoue, elle retourne `false`

  * **Exemple**

```php
$closeFdArrary = $server->heartbeat();
```


## getLastError()

Obtenir le code d'erreur de la dernière opération erronée. Le code d'erreur peut être utilisé pour exécuter différentes logiques selon le type d'erreur dans le code commercial.

```php
Swoole\Server->getLastError(): int
```

  * **Valeurs de retour**


Code d'erreur | Explication
---|---
1001 | La connexion a déjà été fermée par le côté `Server`. Cet error se produit généralement lorsque la connexion a été fermée avec `$server->close()` dans le code, mais que `$server->send()` est toujours appelé pour envoyer des données à cette connexion
1002 | La connexion a été fermée par le côté `Client`, le `Socket` est fermé et il est impossible d'envoyer des données à l'autre extrémité
1003 | En train d'exécuter `close`, ne pas utiliser `$server->send()` dans la fonction de rappel [onClose](/server/events?id=onclose)
1004 | La connexion est fermée
1005 | La connexion n'existe pas, `$fd` passé pourrait être incorrect
1007 | Récupération de données dépassées, après la fermeture de la connexion `TCP`, il peut y avoir des données résiduelles dans le cache de [unixSocket](/learn?id=qu'est-ce-que-ipc), ces données seront abandonnées
1008 | Échec de l'opération `send` en raison du plein du tampon d'envoi, cet error indique que l'autre extrémité de la connexion ne peut pas recevoir les données à temps, ce qui a fait que le tampon d'envoi est plein
1202 | Les données envoyées dépassent la taille définie par [server->buffer_output_size](/server/setting?id=buffer_output_size)
9007 | Se produit uniquement lorsque [dispatch_mode](/server/setting?id=dispatch_mode)=3, cela indique qu'il n'y a actuellement pas de processus disponible, il est possible d'augmenter le nombre de processus `worker_num`


## getSocket()

Appeler cette méthode permet d'obtenir une handle de `socket` sous-jacent, et l'objet retourné est un handle de ressource `sockets`.

```php
Swoole\Server->getSocket(): false|\Socket
```

!> Cette méthode nécessite la extension PHP `sockets` et doit avoir été compilée avec l'option `--enable-sockets` lors de la compilation de `Swoole`

  * **Écoute de port**

    * Les ports ajoutés avec la méthode `listen` peuvent utiliser la méthode `getSocket` de l'objet `Port` de `Swoole\Server\Port`.

    ```php
    $port = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $socket = $port->getSocket();
    ```

    * La fonction `socket_set_option` peut être utilisée pour configurer certains paramètres `socket` plus bas.

    ```php
    $socket = $server->getSocket();
    if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
        echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
    }
    ```

  * **Soutien au Multicast**

    * En utilisant `socket_set_option` pour configurer le paramètre `MCAST_JOIN_GROUP`, il est possible de rejoindre un groupe de multicast avec le `Socket` et d'écouter les paquets de données de groupe multicast.

```php
$server = new Swoole\Server('0.0.0.0', 9905, SWOOLE_BASE, SWOOLE_SOCK_UDP);
$server->set(['worker_num' => 1]);
$socket = $server->getSocket();

$ret = socket_set_option(
    $socket,
    IPPROTO_IP,
    MCAST_JOIN_GROUP,
    array(
        'group' => '224.10.20.30', // Indicates the multicast address
        'interface' => 'eth0' // Indicates the name of the network interface, which can be a number or a string, such as eth0, wlan0
    )
);

if ($ret === false) {
    throw new RuntimeException('Unable to join multicast group');
}

$server->on('Packet', function (Swoole\Server $server, $data, $addr) {
    $server->sendto($addr['address'], $addr['port'], "Swoole: $data");
    var_dump($addr, strlen($data));
});

$server->start();
```
## protéger()

Configure la connexion client en mode protégé, empêchant qu'elle ne soit interrompue par le thread de ping.

```php
Swoole\Server->protect(int $fd, bool $is_protected = true): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Spécifier l'identifiant de connexion client `$fd`
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

    * `bool $is_protected`

      * Statut configuré
      * Valeur par défaut : true 【indique un état protégé】
      * Autres valeurs : false 【indique un état non protégé】

  * **Valeurs de retour**

    * Retourne `true` si l'opération est réussie, `false` sinon


## confirmer()

Confirmez la connexion, utilisé en combinaison avec [enable_delay_receive](/server/setting?id=enable_delay_receive). Lorsque le client établit une connexion, il ne监it pas les événements lisibles, mais déclenche uniquement l'événement de callback [onConnect](/server/events?id=onconnect). Dans le callback [onConnect](/server/events?id=onconnect), exécutez `confirmer` pour confirmer la connexion, ce qui permet au serveur de commencer à écouter les événements lisibles et de recevoir les données envoyées par la connexion client.

!> Version Swoole >= `v4.5.0` disponible

```php
Swoole\Server->confirm(int $fd): bool
```

  * **Paramètres**

    * `int $fd`

      * Fonction : Identificateur unique de la connexion
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeurs de retour**
  
    * Retourne `true` si la confirmation est réussie
    * Retourne `false` si la connexion `$fd` n'existe pas, est fermée ou est déjà en mode d'écoute, indicating un échec de la confirmation

  * **Utilisation**
  
    Cette méthode est généralement utilisée pour protéger le serveur contre les attaques par surcharge de trafic. Lorsque la connexion client est reçue, la fonction [onConnect](/server/events?id=onconnect) est déclenchée, permettant de vérifier l'origine `IP` et de déterminer si le serveur peut accepter des données envoyées.

  * **Exemple**
    
```php
// Création d'un objet Server, écoutant sur le port 127.0.0.1:9501
$serv = new Swoole\Server("127.0.0.1", 9501); 
$serv->set([
    'enable_delay_receive' => true,
]);

// Écoute de l'événement de connexion
$serv->on('Connect', function ($serv, $fd) {  
    // Vérifiez ici ce $fd avant de confirmer
    $serv->confirm($fd);
});

// Écoute de l'événement de réception de données
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "Server: ".$data);
});

// Écoute de l'événement de fermeture de connexion
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

// Démarrage du serveur
$serv->start(); 
```


## getWorkerId()

Obtenir l'identifiant `Worker` actuel du processus (`id` non PID du processus), cohérent avec le `$workerId` lors de l'événement [onWorkerStart](/server/events?id=onworkerstart)

```php
Swoole\Server->getWorkerId(): int|false
```

!> Version Swoole >= `v4.5.0RC1` disponible


## getWorkerPid()

Obtenir le PID du processus Worker spécifié

```php
Swoole\Server->getWorkerPid(int $worker_id = -1): int|false
```

  * **Paramètres**

    * `int $worker_id`

      * Fonction : Obtenir le pid du processus spécifié
      * Valeur par défaut : -1 【indique le processus actuel】
      * Autres valeurs : Aucun

!> Version Swoole >= `v4.5.0RC1` disponible


## getWorkerStatus()

Obtenir l'état du processus Worker

```php
Swoole\Server->getWorkerStatus(int $worker_id = -1): int|false
```

!> Version Swoole >= `v4.5.0RC1` disponible

  * **Paramètres**

    * `int $worker_id`

      * Fonction : Obtenir l'état du processus
      * Valeur par défaut : -1 【indique le processus actuel】
      * Autres valeurs : Aucun

  * **Valeurs de retour**
  
    * Retourne l'état du processus Worker, voir les valeurs d'état du processus
    * Retourne `false` si ce n'est pas un processus Worker ou que le processus n'existe pas

  * **Valeurs d'état du processus**

    Constante | Valeur | Description | dépendance de la version
    ---|---|---|---
    SWOOLE_WORKER_BUSY | 1 | Occupé | v4.5.0RC1
    SWOOLE_WORKER_IDLE | 2 | Libre | v4.5.0RC1
    SWOOLE_WORKER_EXIT | 3 | Lorsque [reload_async](/server/setting?id=reload_async) est activé, il peut y avoir deux processus pour le même `worker_id`, un nouveau et un ancien. L'ancien processus aura un code d'état de EXIT lorsqu'il lit. | v4.5.5


## getManagerPid()

Obtenir le PID du processus Manager actuel du service

```php
Swoole\Server->getManagerPid(): int
```

!> Version Swoole >= `v4.5.0RC1` disponible


## getMasterPid()

Obtenir le PID du processus Master actuel du service

```php
Swoole\Server->getMasterPid(): int
```

!> Version Swoole >= `v4.5.0RC1` disponible


## addCommand()

Ajouter une commande personnalisée `command`

```php
Swoole\Server->addCommand(string $name, int $accepted_process_types, Callable $callback): bool
```

!> - Version Swoole >= `v4.8.0` disponible         
  - Cette fonction ne peut être appelée que avant le démarrage du service. Si une commande du même nom existe déjà, elle retournera directement `false`.

* **Paramètres**

    * `string $name`

        * Fonction : Nom de la `command`
        * Valeur par défaut : Aucun
        * Autres valeurs : Aucun

    * `int $accepted_process_types`

      * Fonction : Type de processus qui accepte la demande, vous pouvez utiliser le `|` pour en soutenir plusieurs, par exemple `SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_MANAGER`
      * Valeur par défaut : Aucun
      * Autres valeurs :
        * `SWOOLE_SERVER_COMMAND_MASTER` processus maître
        * `SWOOLE_SERVER_COMMAND_MANAGER` processus manager
        * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` processus worker
        * `SWOOLE_SERVER_COMMAND_TASK_WORKER` processus task

    * `callable $callback`

        * Fonction : Callback function, qui a deux paramètres, l'un est l'instance de la classe Swoole\Server et l'autre est une variable personnalisée passée via le quatrième argument de la méthode Swoole\Server::command().
        * Valeur par défaut : Aucun
        * Autres valeurs : Aucun

* **Valeurs de retour**

    * Retourne `true` si la commande personnalisée est ajoutée avec succès, `false` sinon

## command()

Appeler la commande personnalisée `command` définie

```php
Swoole\Server->command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true): false|string|array
```

!> Disponible à partir de la version Swoole >= `v4.8.0`, dans les modes `SWOOLE_PROCESS` et `SWOOLE_BASE`, cette fonction ne peut être utilisée que pour le processus maître.  


* **Paramètres**

    * `string $name`

        * Fonction : Nom de la `command`
        * Valeur par défaut : Aucun
        * Autres valeurs : Aucun

    * `int $process_id`

        * Fonction : ID du processus
        * Valeur par défaut : Aucun
        * Autres valeurs : Aucun

    * `int $process_type`

        * Fonction : Type de demande de processus, veuillez choisir l'une des autres valeurs.
        * Valeur par défaut : Aucun
        * Autres valeurs :
          * `SWOOLE_SERVER_COMMAND_MASTER` processus maître
          * `SWOOLE_SERVER_COMMAND_MANAGER` processus manager
          * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` processus worker
          * `SWOOLE_SERVER_COMMAND_TASK_WORKER` processus task

    * `mixed $data`

        * Fonction : Données de la demande, qui doivent être sérialisables
        * Valeur par défaut : Aucun
        * Autres valeurs : Aucun

    * `bool $json_decode`

        * Fonction : Utiliser `json_decode` pour la décodage
        * Valeur par défaut : true
        * Autres valeurs : false
  
  * **Exemple d'utilisation**
    ```php
    <?php
    use Swoole\Http\Server;
    use Swoole\Http\Request;
    use Swoole\Http\Response;

    $server = new Server('127.0.0.1', 9501, SWOOLE_BASE);
    $server->addCommand('test_getpid', SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_EVENT_WORKER,
        function ($server, $data) {
          var_dump($data);
          return json_encode(['pid' => posix_getpid()]);
        });
    $server->set([
        'log_file' => '/dev/null',
        'worker_num' => 2,
    ]);

    $server->on('start', function (Server $serv) {
        $result = $serv->command('test_getpid', 0, SWOOLE_SERVER_COMMAND_MASTER, ['type' => 'master']);
        Assert::eq($result['pid'], $serv->getMasterPid());
        $result = $serv->command('test_getpid', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::eq($result['pid'], $serv->getWorkerPid(1));
        $result = $serv->command('test_not_found', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::false($result);

        $serv->shutdown();
    });

    $server->on('request', function (Request $request, Response $response) {
    });
    $server->start();
    ```
