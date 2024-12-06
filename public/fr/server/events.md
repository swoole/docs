# Événements

Cette section présentera toutes les fonctions de rappel de Swoole, chaque fonction de rappel est une fonction PHP correspondant à un événement.


## onStart

?> **Après le démarrage, cette fonction est appelée dans le thread principal du processus maître (master)**

```php
function onStart(Swoole\Server $server);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonctionnalité** : Objet Swoole\Server
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

* **Avant cet événement, le `Server` a déjà effectué les opérations suivantes**

    * Le processus de création du [Manager](/learn?id=manager进程) est terminé
    * Le processus de création du [Worker](/learn?id=worker进程) est terminé
    * Tous les ports TCP/UDP/[unixSocket](/learn?id=什么是IPC) sont en attente d'acceptation de connexions et de demandes, mais l'acceptation des connexions et des demandes n'a pas encore commencé
    * Les horloges ont été 监听és

* **Opérations à venir**

    * Le [Reactor](/learn?id=reactor线程) principal commence à recevoir des événements, les clients peuvent se connecter au `Server`

Dans le rappel `onStart`, il est uniquement permis d'utiliser `echo`, de prendre en charge `Log`, de modifier le nom du processus. Il n'est pas possible d'exécuter d'autres opérations (ne pas appeler de fonctions liées au `server`, etc., car le service n'est pas prêt). Les rappels `onWorkerStart` et `onStart` sont exécutés en parallèle dans différents processus, sans ordre spécifique.

Il est possible d'écrire dans le rappel `onStart` la valeur de `$server->master_pid` et de `$server->manager_pid` dans un fichier. Cela permet de créer des scripts qui peuvent envoyer des signaux à ces deux `PID` pour effectuer des opérations de fermeture et de redémarrage.

L'événement `onStart` est appelé dans le thread principal du processus `Master`.

!> Les objets de ressources globales créés dans `onStart` ne peuvent pas être utilisés dans les processus `Worker`, car l'appel à `onStart` se produit lorsque les processus `Worker` ont déjà été créés  
Les objets nouvellement créés se trouvent dans le processus principal, et les processus `Worker` ne peuvent pas accéder à cette zone de mémoire  
Par conséquent, le code de création d'objets globaux doit être placé avant `Server::start`, un exemple typique est [Swoole\Table](/memory/table?id=完整示例)

* **Avertissement de sécurité**

Dans le rappel `onStart`, il est possible d'utiliser les API asynchrones et coroutines, mais il est important de noter qu'il peut y avoir des conflits avec `dispatch_func` et `package_length_func`, **ne les utilisez pas simultanément**.

Veuillez ne pas démarrer de horloge dans `onStart`. Si vous exécutez l'opération `Swoole\Server::shutdown()` dans votre code, cela peut entraîner un programme qui ne peut pas se terminer car il y a toujours un horloge en cours d'exécution.

Avant de retourner dans le rappel `onStart`, le serveur ne acceptera aucune connexion client, donc vous pouvez utiliser des fonctions synchrones bloquantes en toute sécurité.

* **Mode BASE**

Dans le mode [SWOOLE_BASE](/learn?id=swoole_base), il n'y a pas de processus maître, donc l'événement `onStart` n'existe pas, veuillez ne pas utiliser la fonction de rappel `onStart` dans le mode BASE.

```
WARNING swReactorProcess_start: L'événement onStart avec SWOOLE_BASE est déprécié
```


## onBeforeShutdown

?> **Cet événement se produit avant que le `Server` ne se ferme normalement** 

!> Disponible à partir de la version Swoole >= `v4.8.0`. Dans cet événement, vous pouvez utiliser l'API coroutine.

```php
function onBeforeShutdown(Swoole\Server $server);
```


* **Paramètres**

    * **`Swoole\Server $server`**
        * **Fonctionnalité** : Objet Swoole\Server
        * **Valeur par défaut** : None
        * **Autres valeurs** : None


## onShutdown

?> **Cet événement se produit après que le `Server` se soit fermé normalement**

```php
function onShutdown(Swoole\Server $server);
```

  * **Paramètres**

    * **`Swoole\Server $server`**
      * **Fonctionnalité** : Objet Swoole\Server
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Avant cela, `Swoole\Server` a déjà effectué les opérations suivantes**

    * Tous les threads [Reactor](/learn?id=reactor线程), `HeartbeatCheck` threads, `UdpRecv` threads ont été fermés
    * Tous les processus `Worker`, [Task processes](/learn?id=taskworker进程), [User processes](/server/methods?id=addprocess) ont été fermés
    * Tous les ports d'écoute TCP/UDP/UnixSocket ont été fermés
    * Le [Reactor principal](/learn?id=reactor线程) a été fermé

  !> Forcer le processus à mourir ne va pas déclencher l'événement onShutdown, comme `kill -9`  
  Il est nécessaire d'envoyer le signal `SIGTERM` au processus principal avec `kill -15` pour terminer normalement  
  Dans la ligne de commande, appuyez sur `Ctrl+C` pour arrêter le programme, cela s'arrêtera immédiatement sans déclencher l'événement onShutdown

  * **Notes**

  !> Veuillez ne pas appeler d'API asynchrone ou coroutine liée dans l'événement onShutdown, car lorsque l'événement onShutdown est déclenché, tous les mécanismes d'événements de base ont été détruits ;  
À ce moment-là, il n'y a plus d'environnement coroutine, si les développeurs doivent utiliser des API coroutine, ils doivent manuellement appeler `Co\run` pour créer un [conteneur coroutine](/coroutine?id=什么是协程容器).


## onWorkerStart

?> **Cet événement se produit lorsque le processus Worker/ [Task process](/learn?id=taskworkerprocess) est démarré, les objets créés ici peuvent être utilisés tout au long de la vie du processus.**

```php
function onWorkerStart(Swoole\Server $server, int $workerId);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonctionnalité** : Objet Swoole\Server
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $workerId`**
      * **Fonctionnalité** : `ID` du processus `Worker` (non PID du processus)
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * `onWorkerStart/onStart` sont exécutés en parallèle, sans ordre spécifique
  * Vous pouvez déterminer si vous avez un processus Worker ou un [Task process](/learn?id=taskworkerprocess) en utilisant la propriété `$server->taskworker`
  * Lorsque `worker_num` et `task_worker_num` sont définis à plus de 1, chaque processus déclenche un événement `onWorkerStart`, qui peut être distingué en fonction de [$worker_id](/server/properties?id=worker_id)
  * Les processus Worker envoient des tâches aux processus Task, et après avoir traité toutes les tâches, les processus Task notifient les processus Worker via la fonction de rappel [onFinish](/server/events?id=onfinish). Par exemple, pour envoyer des e-mails de notification à cent mille utilisateurs en arrière-plan, après l'achèvement de l'opération, l'état de l'opération est affiché comme en cours d'envoi, à ce moment-là, vous pouvez continuer d'autres opérations, et après l'achèvement de l'envoi massif des e-mails, l'état de l'opération est automatiquement changé à envoyé.

  L'exemple suivant montre comment renommer un processus Worker/ [Task process](/learn?id=taskworkerprocess).

```php
$server->on('WorkerStart', function ($server, $worker_id){
    global $argv;
    if($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});
```

  Si vous souhaitez utiliser le mécanisme de [Reload](/server/methods?id=reload) pour recharger le code, vous devez `require` vos fichiers d'affaires dans `onWorkerStart`, et non à la tête du fichier. Les fichiers inclus avant l'appel à `onWorkerStart` ne seront pas rechargés.

  Vous pouvez placer les fichiers PHP communs et inaltérés avant `onWorkerStart`. Bien que vous ne puissiez pas recharger le code, tous les Workers partagent ces données et il n'est pas nécessaire d'utiliser de mémoire supplémentaire pour les stocker.
Le code après `onWorkerStart` doit être conservé en mémoire par chaque processus

  * `$worker_id` représente l'ID de ce processus Worker, la portée se réfère à [$worker_id](/server/properties?id=worker_id)
  * [$worker_id](/server/properties?id=worker_id) n'a aucun rapport avec le PID du processus, vous pouvez utiliser la fonction `posix_getpid` pour obtenir le PID

  * **Soutien aux coroutines**

    * Des coroutines sont automatiquement créées dans la fonction de rappel `onWorkerStart`, donc vous pouvez utiliser l'API coroutine dans `onWorkerStart`

  * **Note**

    !> Lorsqu'une erreur fatale se produit ou que `exit` est appelé volontairement dans le code, le processus Worker/Task quittera et un nouveau processus sera créé par le processus de gestion. Cela peut entraîner un cercle vicieux de création et de destruction de processus

## onWorkerStop

?> **Cette événement se produit lorsque le processus `Worker` se termine. Dans cette fonction, vous pouvez récupérer les divers types de ressources attribuées par le processus `Worker`.**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $workerId`**
      * **Fonction** : `id` du processus `Worker` (non PID du processus)
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Remarques**

    !> - Le processus se termine de manière anormale, comme être tué de force, erreur fatale, dump de cœur, le callback `onWorkerStop` ne peut pas être exécuté.  
    - Veuillez ne pas appeler d'aucune API asynchrone ou coroutinée dans `onWorkerStop`, car lorsque `onWorkerStop` est déclenché, le gestionnaire d'événements de base a déjà détruit toutes les installations de [l'événement-loop](/learn?id=qu'est-ce qu'uneventloop).


## onWorkerExit

?> **Seulement valide après avoir activé la caractéristique [reload_async](/server/setting?id=reload_async). Voir [Comment redémarrer correctement le service](/question/use?id=comment-redémarrer-swoole-correctement)**

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $workerId`**
      * **Fonction** : `id` du processus `Worker` (non PID du processus)
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Remarques**

    !> - Le processus `Worker` ne s'est pas éteint, `onWorkerExit` sera déclenché continuellement  
    - `onWorkerExit` se déclenche à l'intérieur du processus `Worker`, s'il y a un [événement-loop](/learn?id=qu'est-ce qu'uneventloop) dans le [Processus Task](/learn?id=taskworkerprocess), il se déclenche également  
    - Dans `onWorkerExit`, retirez/fermez autant que possible les connexions asynchrones `Socket`, et finalement, lorsque le gestionnaire d'événements de base détecte que le nombre de handles en attente d'événements dans l'événement-loop est de `0`, le processus se terminera  
    - Lorsque le processus n'a plus de handle en attente d'événements, ce callback ne sera pas appelé à la fin du processus  
    - `onWorkerStop` ne sera appelé qu'après que le processus `Worker` se soit éteint


## onConnect

?> **Lorsqu'une nouvelle connexion entre, elle est appelée dans le processus worker.**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $fd`**
      * **Fonction** : Descriptor de fichier de la connexion
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $reactorId`**
      * **Fonction** : `ID` du thread [Reactor](/learn?id=reactorthread) où se trouve la connexion
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Remarques**

    !> Les deux callbacks `onConnect/onClose` se produisent à l'intérieur du processus worker, et non dans le processus principal.  
    Sous le protocole `UDP`, il n'y a que l'événement [onReceive](/server/events?id=onreceive), et il n'y a pas d'événements `onConnect/onClose`

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

      * Dans ce mode, `onConnect/onReceive/onClose` peuvent être livrés à différents processus. Les données de l'objet PHP lié à la connexion ne peuvent pas être initialisées dans le callback [onConnect](/server/events?id=onconnect) et nettoyées dans [onClose](/server/events?id=onclose)
      * Les trois événements `onConnect/onReceive/onClose` peuvent s'exécuter en parallèle, ce qui peut entraîner des anomalies


## onReceive

?> **Cette fonction est appelée lorsqu'une donnée est reçue, elle se produit dans le processus worker.**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $fd`**
      * **Fonction** : Descriptor de fichier de la connexion
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $reactorId`**
      * **Fonction** : `ID` du thread [Reactor](/learn?id=reactorthread) où se trouve la connexion TCP
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $data`**
      * **Fonction** : Contenu de la donnée reçue, qui peut être du texte ou du contenu binaire
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Concernant l'intégrité des paquets sous le protocole TCP, veuillez consulter [Problème de bordure de paquet TCP](/learn?id=problème-de-bordure-de-paquet-tcp)**

    * L'utilisation des configurations fournies par le niveau inférieur telles que `open_eof_check/open_length_check/open_http_protocol` peut garantir l'intégrité des paquets
    * Sans utiliser le traitement du protocole de niveau inférieur, analyser les données dans le code PHP après [onReceive](/server/events?id=onreceive), fusionner/diviser les paquets.

    Par exemple : vous pouvez ajouter un `$buffer = array()` dans le code, utiliser `$fd` comme clé pour conserver les données de contexte. Chaque fois que des données sont reçues, les strings sont concatenées, `$buffer[$fd] .= $data`, puis vous déterminez si le string `$buffer[$fd]` est un paquet complet.

    Par défaut, le même `$fd` est attribué au même `Worker`, donc les données peuvent être concatenées. Lorsque [dispatch_mode](/server/setting?id=dispatch_mode) est égal à 3, les données de demande sont préemptives, et les données envoyées par le même `$fd` peuvent être attribuées à différents processus, donc vous ne pouvez pas utiliser la méthode de concatenation de paquets mentionnée ci-dessus.

  * **Écoute sur plusieurs ports, veuillez consulter [cette section](/server/port)**

    Lorsque le serveur principal a établi un protocole, les ports supplémentaires écoutés héritent par défaut des paramètres du serveur principal. Vous devez appeler explicitement la méthode `set` pour réétablir le protocole du port.    

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9501);
    $port2 = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $port2->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        echo "[#".$server->worker_id."]\tClient[$fd]: $data\n";
    });
    ```

    Bien que la méthode `on` ait été utilisée pour enregistrer le callback de l'événement [onReceive](/server/events?id=onreceive), comme la méthode `set` n'a pas été appelée pour remplacer le protocole du serveur principal, le port `9502` écouté de manière supplémentaire utilise toujours le protocole HTTP. Lorsque vous utilisez un client `telnet` pour vous connecter au port `9502` et envoyer des chaînes de caractères, le serveur ne déclenche pas l'événement [onReceive](/server/events?id=onreceive).

  * **Remarques**

    !> Si l'option d'automatisation du protocole n'est pas activée, la taille maximale des données reçues par une seule fois dans [onReceive](/server/events?id=onreceive) est de `64K`  
    Si l'option de traitement automatique du protocole est activée, [onReceive](/server/events?id=onreceive) recevra un paquet complet, ne dépassant pas la valeur maximale de [package_max_length](/server/setting?id=package_max_length)  
    Prend en charge la format binaire, `$data` peut être des données binaires
## surPacket

?> **Lorsque un paquet `UDP` est reçu, cette fonction est appelée en retour, elle se produit dans le processus `worker`.**

```php
function surPacket(Swoole\Server $server, string $data, array $clientInfo);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $data`**
      * **Fonction** : Contenu du données reçu, qui peut être du texte ou du contenu binaire
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`array $clientInfo`**
      * **Fonction** : Informations client telles que `adresse/port/socket_serveur`, etc., [voir serveur UDP](/start/start_udp_server)
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Remarque**

    !> Lorsque le serveur écoute à la fois les ports `TCP/UDP`, les données du protocole `TCP` reçues seront appelées en retour par [surReceive](/server/events?id=onreceive), et les paquets `UDP` reçus seront appelés en retour par `surPacket`. Les traitements automatiques de protocole tels que `EOF` ou `Longueur` définis par le serveur ([voir problème de frontière de paquet TCP](/learn?id=tcp数据包边界问题)) sont inefficaces pour le port `UDP`, car les paquets `UDP` ont une frontière de message en soi et nécessitent pas de traitement de protocole supplémentaire.


## surClose

?> **Après la fermeture d'une connexion client `TCP`, cette fonction est appelée en retour dans le processus `Worker`.**

```php
function surClose(Swoole\Server $server, int $fd, int $reactorId);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $fd`**
      * **Fonction** : Descriptor de fichier de la connexion
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $reactorId`**
      * **Fonction** : ID du `reactor` thread qui a initié la fermeture, négatif si la fermeture est active
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Suggérences**

    * **Fermeture active**

      * Lorsque le serveur ferme activement la connexion, cet argument est mis à `-1` par le sous-système, et vous pouvez distinguer si la fermeture est initiée par le côté serveur ou client en vérifiant si `$reactorId < 0`.
      * Une fermeture active est considérée lorsque la méthode `close` est appelée de manière proactive dans le code PHP.

    * **Détection de cœur**

      * La [détection de cœur](/server/setting?id=heartbeat_check_interval) est notifiée par le thread de détection de cœur pour la fermeture, et lors de la fermeture, l'argument `$reactorId` de la fonction [surClose](/server/events?id=onclose) n'est pas `-1`.

  * **Remarque**

    !> - La fonction de retour [surClose](/server/events?id=onclose) peut entraîner une fuite des connexions en cas d'erreur fatale. En utilisant la commande `netstat`, vous verrez un grand nombre de connexions TCP en état `CLOSE_WAIT`.
    - Que la fermeture soit initiée par le client ou que le serveur appelle activement `$server->close()` pour fermer la connexion, cet événement sera déclenché. Par conséquent, chaque fois qu'une connexion est fermée, cette fonction sera appelée.  
    - Dans [surClose](/server/events?id=onclose), vous pouvez toujours appeler la méthode [getClientInfo](/server/methods?id=getClientInfo) pour obtenir des informations sur la connexion, et la connexion TCP ne sera fermée qu'après l'exécution de la fonction de retour [surClose](/server/events?id=onclose).  
    - Ici, la fonction de retour [surClose](/server/events?id=onclose) indique que la connexion client est fermée, donc il n'est pas nécessaire d'exécuter `$server->close($fd)`. L'exécution de `$server->close($fd)` dans le code entraînera une alerte d'erreur PHP.


## surTask

?> **Appelé à l'intérieur du processus `task`. Le processus `worker` peut utiliser la fonction [task](/server/methods?id=task) pour envoyer de nouvelles tâches au processus `task_worker`. Le processus [Task](/learn?id=taskworker进程) actuel, lorsqu'il appelle la fonction de retour [surTask](/server/events?id=ontask), change l'état du processus en occupé, à ce moment il ne recevra plus de nouvelles Tasks. Lorsque la fonction [surTask](/server/events?id=ontask) retourne, l'état du processus est changé en libre puis il continue de recevoir de nouvelles `Tasks`.**

```php
function surTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $task_id`**
      * **Fonction** : ID du processus `task` qui exécute la tâche【La combinaison de `$task_id` et `$src_worker_id` est unique dans l'ensemble, les tâches envoyées par différents processus `worker` peuvent avoir des IDs de tâche identiques】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $src_worker_id`**
      * **Fonction** : ID du processus `worker` qui envoie la tâche【La combinaison de `$task_id` et `$src_worker_id` est unique dans l'ensemble, les tâches envoyées par différents processus `worker` peuvent avoir des IDs de tâche identiques】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`mixed $data`**
      * **Fonction** : Contenu de la tâche
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Suggérences**

    * **À partir de la version 4.2.12, si la [task_enable_coroutine](/server/setting?id=task_enable_coroutine) est activée, la signature de la fonction de retour est**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); //Terminer la tâche, finir et retourner des données
      });
      ```

    * **Rendre le résultat de l'exécution au processus `worker`**

      * **Dans la fonction de retour [surTask](/server/events?id=ontask), `return` une chaîne, cela signifie que ce contenu sera rendu au processus `worker`. Dans le processus `worker`, la fonction [onFinish](/server/events?id=onfinish) sera déclenchée, indiquant que la `tâche` lancée est terminée. Bien sûr, vous pouvez également déclencher la fonction [onFinish](/server/events?id=onfinish) en utilisant `$server->finish()`, sans avoir besoin de `return`**

      * La variable de retour peut être n'importe quelle variable PHP non `null`

  * **Remarque**

    !> - Si la fonction de retour [surTask](/server/events?id=ontask) rencontre une erreur fatale et quitte l'exécution, ou est forcée de mourir par un processus externe, la tâche actuelle sera abandonnée, mais cela n'affectera pas les autres tâches en attente dans la file d'attente


## surFinish

?> **Cette fonction de retour est appelée dans le processus worker, lorsque la tâche confiée par le processus worker est terminée dans le processus task, le processus task [task进程](/learn?id=taskworker进程) enverra le résultat du traitement de la tâche au processus worker en utilisant la méthode `$server->finish()`.**

```php
function surFinish(Swoole\Server $server, int $task_id, mixed $data)
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $task_id`**
      * **Fonction** : ID du processus task qui exécute la tâche
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`mixed $data`**
      * **Fonction** : Contenu du résultat du traitement de la tâche
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Remarque**

    !> - Si dans l'événement [task](/server/events?id=ontask) du processus task [task进程](/learn?id=taskworker进程) la méthode `finish` n'est pas appelée ou que le résultat n'est pas rendu, le processus worker ne déclenchera pas [surFinish](/server/events?id=onfinish)  
    - Le processus worker qui exécute la logique de [surFinish](/server/events?id=onfinish) est le même que celui qui a lancé la tâche
## onPipeMessage

?> **Lorsque le processus de travail reçoit un message envoyé par `$server->sendMessage()` sur le [unixSocket](/learn?id=qu'est-ce que-IPC) (socket Unix), l'événement `onPipeMessage` est déclenché. Les processus `worker/task` peuvent tous déclencher l'événement `onPipeMessage`.**

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $src_worker_id`**
      * **Fonction** : ID du processus `Worker` d'où le message vient
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`mixed $message`**
      * **Fonction** : Contenu du message, qui peut être de n'importe quel type PHP
      * **Valeur par défaut** : None
      * **Autres valeurs** : None


## onWorkerError

?> **Lorsqu'un `Worker/Task` process rencontre une exception, cette fonction est appelée en回调 dans le processus `Manager`.**

!> Cette fonction est principalement utilisée pour l'alerte et la surveillance. Une fois qu'un processus Worker quitte de manière anormale, il est très probable qu'il y ait rencontré une erreur fatale ou un dump de processus Core. En enregistrant des logs ou en envoyant des informations d'alerte, on informe les développeurs de prendre les mesures appropriées.

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

  * **Paramètres** 

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $worker_id`**
      * **Fonction** : ID du processus Worker en échec
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $worker_pid`**
      * **Fonction** : PID du processus Worker en échec
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $exit_code`**
      * **Fonction** : Code d'exit, qui va de `0` à `255`
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $signal`**
      * **Fonction** : Signal qui a provoqué la sortie du processus
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Erreurs courantes**

    * `signal = 11` : Cela indique qu'un processus Worker a rencontré une erreur de segmentation (segment fault), ce qui pourrait avoir déclenché un bug en dessous. Veuillez collecter des informations sur le dump de processus et les logs de détection de mémoire de valgrind, et [contacter l'équipe de développement Swoole](/other/issue) pour signaler ce problème.
    * `exit_code = 255` : Cela indique qu'un processus Worker a rencontré une erreur fatale. Veuillez vérifier les logs d'erreur PHP pour trouver le code PHP problématique et le résoudre.
    * `signal = 9` : Cela indique que le processus Worker a été tué par le système. Veuillez vérifier s'il y a eu une opération de kill -9 intentionnelle, et rechercher dans les informations de dmesg des signes de manque de mémoire (OOM).
    * Si OOM est présent, une trop grande quantité de mémoire a été allouée. 1. Veuillez vérifier les paramètres de configuration du `Server`, tels que [socket_buffer_size](/server/setting?id=socket_buffer_size), etc., pour voir s'ils ont été alloués de manière excessive ; 2. Veuillez vérifier si un très grand module de mémoire [Swoole\Table](/memory/table) a été créé.


## onManagerStart

?> **Cet événement est déclenché lorsque le processus de gestion commence.**

```php
function onManagerStart(Swoole\Server $server);
```

  * **Conseils**

    * Dans cette fonction de rappel, vous pouvez modifier le nom du processus de gestion.
    * Dans les versions antérieures à `4.2.12`, il n'était pas possible d'ajouter de timer dans le processus de gestion, de délivrer des tâches ou d'utiliser des coroutines.
    * Dans les versions `4.2.12` ou supérieures, le processus de gestion peut utiliser des timers basés sur des signaux pour la synchronisation.
    * Dans le processus de gestion, vous pouvez appeler l'interface [sendMessage](/server/methods?id=sendMessage) pour envoyer des messages à d'autres processus de travail.

    * **Ordre de démarrage**

      * Les processus `Task` et `Worker` ont été créés
      * L'état du processus `Master` est inconnu, car le gestionnaire et le maître sont parallèles, et il n'est pas possible de déterminer si le processus maître est prêt lorsque le rappel `onManagerStart` se produit.

    * **Mode BASE**

      * Dans le mode [SWOOLE_BASE](/learn?id=swoole_base), si les paramètres `worker_num`, `max_request` et `task_worker_num` sont définis, un processus de gestion sera créé en dessous pour gérer les processus de travail. Par conséquent, les événements de rappel `onManagerStart` et `onManagerStop` seront déclenchés.


## onManagerStop

?> **Cet événement est déclenché lorsque le processus de gestion se termine.**

```php
function onManagerStop(Swoole\Server $server);
```

 * **Conseils**

  * Lorsque le rappel `onManagerStop` est déclenché, cela signifie que les processus `Task` et `Worker` ont cessé de fonctionner et ont été récupérés par le processus de gestion.


## onBeforeReload

?> **Cet événement est déclenché avant le `Reload` du processus Worker, et est appelé en回调 dans le processus Manager.**

```php
function onBeforeReload(Swoole\Server $server);
```

  * **Paramètres**

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : None
      * **Autres valeurs** : None


## onAfterReload

?> **Cet événement est déclenché après le `Reload` du processus Worker, et est appelé en回调 dans le processus Manager.**

```php
function onAfterReload(Swoole\Server $server);
```

  * **Paramètres**

    * **`Swoole\Server $server`**
      * **Fonction** : Objet Swoole\Server
      * **Valeur par défaut** : None
      * **Autres valeurs** : None


## Ordre d'exécution des événements

* Tous les rappels d'événements se produisent après le démarrage de `$server->start`
* Lors de la fermeture du programme serveur, l'événement dernier est `onShutdown`
* Après le succès du démarrage du serveur, `onStart/onManagerStart/onWorkerStart` s'exécutent de manière parallèle dans différents processus
* `onReceive/onConnect/onClose` se déclenchent dans les processus Worker
* Le début/la fin des processus `Worker/Task` appellent respectivement une fois `onWorkerStart/onWorkerStop`
* L'événement [onTask](/server/events?id=ontask) se produit uniquement dans le processus [task](/learn?id=taskworkerprocess)
* L'événement [onFinish](/server/events?id=onfinish) se produit uniquement dans les processus worker
* L'ordre d'exécution des événements `onStart/onManagerStart/onWorkerStart` est incertain

## Style orienté objet

Après avoir activé [event_object](/server/setting?id=event_object), les paramètres des rappels d'événements suivants changeront.

* Connexion client [onConnect](/server/events?id=onconnect)
```php
$server->on('Connect', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* Réception de données [onReceive](/server/events?id=onreceive)
```php
$server->on('Receive', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* Fermeture de connexion [onClose](/server/events?id=onclose)
```php
$server->on('Close', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```


* Réception de paquets UDP [onPacket](/server/events?id=onpacket)
```php
$server->on('Packet', function (Swoole\Server $serv, Swoole\Server\Packet $object) {
    var_dump($object);
});
```


* Communication entre processus [onPipeMessage](/server/events?id=onpipemessage)
```php
$server->on('PipeMessage', function (Swoole\Server $serv, Swoole\Server\PipeMessage $msg) {
    var_dump($msg);
    $object = $msg->data;
    $serv->sendto($object->address, $object->port, $object->data, $object->server_socket);
});
```


* Exception de processus [onWorkerError](/server/events?id=onworkererror)
```php
$serv->on('WorkerError', function (Swoole\Server $serv, Swoole\Server\StatusInfo $info) {
    var_dump($info);
});
```


* Acceptation de tâche par le processus task [onTask](/server/events?id=ontask)
```php
$server->on('Task', function (Swoole\Server $serv, Swoole\Server\Task $task) {
    var_dump($task);
});
```


* Résultat de traitement de la tâche par le processus worker [onFinish](/server/events?id=onfinish)
```php
$server->on('Finish', function (Swoole\Server $serv, Swoole\Server\TaskResult $result) {
    var_dump($result);
});
```

* [Swoole\Server\Event](/server/event_class)
* [Swoole\Server\Packet](/server/packet_class)
* [Swoole\Server\PipeMessage](/server/pipemessage_class)
* [Swoole\Server\StatusInfo](/server/statusinfo_class)
* [Swoole\Server\Task](/server/task_class)
* [Swoole\Server\TaskResult](/server/taskresult_class)
