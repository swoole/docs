# Configuration

La fonction [`Swoole\Server->set()`](/server/methods?id=set) est utilisée pour configurer les différents paramètres de fonctionnement du `Server`. Toutes les sous-pages de cette section sont des éléments de l'array de configuration.

!> À partir de la version [v4.5.5](/version/log?id=v455), le niveau inférieur détecte si les paramètres de configuration définis sont corrects. Si un paramètre de configuration non fourni par `Swoole` est défini, une alerte sera générée.

```shell
PHP Warning:  option [foo] non prise en charge in @swoole-src/library/core/Server/Helper.php
```


### debug_mode

?> Réglez le mode de journalisation en `debug` pour le mode de débogage. Cela n'a d'effet que si la compilation a été effectuée avec `--enable-debug`.

```php
$server->set([
  'debug_mode' => true
])
```


### trace_flags

?> Réglez les étiquettes des journaux de trace pour n'imprimer qu'une partie des journaux de trace. Les `trace_flags` prennent en charge l'utilisation de la `|` ou de l'opérateur pour configurer plusieurs éléments de trace. Cela n'a d'effet que si la compilation a été effectuée avec `--enable-trace-log`.

Le niveau inférieur prend en charge les éléments de trace suivants, où `SWOOLE_TRACE_ALL` représente la trace de tous les éléments :

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`


### log_file

?> **Spécifiez le fichier de journal des erreurs `Swoole`**

?> Les informations sur les exceptions se produisant pendant le fonctionnement de `Swoole` seront enregistrées dans ce fichier, par défaut, elles seront affichées à l'écran.  
Après avoir activé le mode démoniaque `(daemonize => true)`, la sortie standard sera redirigée vers `log_file`. Les contenus affichés à l'écran par `echo/var_dump/print` dans le code PHP seront écrits dans le fichier `log_file`.

  * **Remarque**

    * Les journaux dans `log_file` ne sont que pour enregistrer les erreurs de fonctionnement et il n'est pas nécessaire de les conserver longtemps.

    * **Numéro de journal**

      ?> Avant les informations de journal, un numéro sera ajouté pour indiquer le type de thread/process qui a généré le journal.

        * `#` Processus maître
        * `$` Processus manager
        * `*` Processus worker
        * `^` Processus task

    * **Réouverture du fichier de journal**

      ?> Si le fichier de journal est déplacé ou supprimé par un `mv` pendant le fonctionnement du programme de serveur, les informations de journal ne pourront pas être écrites normalement. Dans ce cas, vous pouvez envoyer le signal `SIGRTMIN` au `Server` pour rouvrir le fichier de journal.

      * Seulement supporté sur la plateforme `Linux`
      * Ne prend pas en charge le processus [UserProcess](/server/methods?id=addProcess)

  * **Note**

    !> `log_file` ne se divise pas automatiquement en fichiers, il est donc nécessaire de nettoyer régulièrement ce fichier. En observant la sortie de `log_file`, vous pouvez obtenir diverses informations d'erreur et d'alerte du serveur.


### log_level

?> **Réglez le niveau d'impression des journaux d'erreur du `Server`, qui va de `0` à `6`. Les informations de journal inférieures au niveau de `log_level` défini ne seront pas diffusées.**【Valeur par défaut : `SWOOLE_LOG_INFO`】

Pour les constantes de niveau de journal correspondant, veuillez consulter [Niveaux de journal](/consts?id=log_level).

  * **Note**

    !> `SWOOLE_LOG_DEBUG` et `SWOOLE_LOG_TRACE` ne sont disponibles que si la compilation a été effectuée avec [--enable-debug-log](/environment?id=debug_param) et [--enable-trace-log](/environment?id=debug_param) ;  
    Lorsque le mode démoniaque `daemonize` est activé, tout le contenu de l'impression à l'écran dans le programme sera écrit dans le [log_file](/server/setting?id=log_file), ce contenu n'est pas contrôlé par `log_level`.


### log_date_format

?> **Réglez la format de date des journaux du `Server`**, en suivant le `format` de la fonction [strftime](https://www.php.net/manual/zh/function.strftime.php)

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```


### log_date_with_microseconds

?> **Réglez la précision des journaux du `Server`, si elle doit inclure les microsecondes**【Valeur par défaut : `false`】


### log_rotation

?> **Réglez la division des journaux du `Server`**【Valeur par défaut : `SWOOLE_LOG_ROTATION_SINGLE`】

| Constante                        | Description | Informations de version |
| -------------------------------- | ----------- | ----------------------- |
| SWOOLE_LOG_ROTATION_SINGLE       | Désactivé   | -                       |
| SWOOLE_LOG_ROTATION_MONTHLY      | Mois       | v4.5.8                  |
| SWOOLE_LOG_ROTATION_DAILY        | Journalière | v4.5.2                  |
| SWOOLE_LOG_ROTATION_HOURLY       | Horaires    | v4.5.8                  |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE | Minutier   | v4.5.8                  |


### display_errors

?> Activer / Désactiver l'affichage des informations d'erreur `Swoole`.

```php
$server->set([
  'display_errors' => true
])
```


### dns_server

?> Réglez l'adresse `IP` pour les requêtes `DNS`.


### socket_dns_timeout

?> Timeout pour la résolution de nom de domaine, si le client coroutine est activé au niveau du serveur, ce paramètre peut contrôler le temps de timeout pour la résolution de nom de domaine du client, en secondes.


### socket_connect_timeout

?> Timeout pour la connexion du client, si le client coroutine est activé au niveau du serveur, ce paramètre peut contrôler le temps de timeout pour la connexion du client, en secondes.


### socket_write_timeout / socket_send_timeout

?> Timeout pour l'écriture du client, si le client coroutine est activé au niveau du serveur, ce paramètre peut contrôler le temps de timeout pour l'écriture du client, en secondes.   
Cette configuration peut également être utilisée pour contrôler le temps de timeout d'exécution de `shell_exec` ou de [Swoole\Coroutine\System::exec()](/coroutine/system?id=exec) après la coroutineisation.   


### socket_read_timeout / socket_recv_timeout

?> Timeout pour la lecture du client, si le client coroutine est activé au niveau du serveur, ce paramètre peut contrôler le temps de timeout pour la lecture du client, en secondes.


### max_coroutine / max_coro_num :id=max_coroutine

?> **Réglez le nombre maximal de coroutines pour le processus de travail actuel.**【Valeur par défaut : `100000`, la valeur par défaut pour les versions de Swoole inférieures à `v4.4.0-beta` est `3000`】

?> Si le nombre de coroutines dépasse `max_coroutine`, il sera impossible de créer de nouvelles coroutines au niveau inférieur, Swoole au niveau du serveur lancera une erreur `exceed max number of coroutine`, le `TCP Server` fermera directement la connexion, et le `Http Server` retournera un code d'état HTTP 503.

?> Le nombre maximal de coroutines réellement créables dans le programme du `Server` est égal à `worker_num * max_coroutine`, les nombres de coroutines pour les processus task et UserProcess sont calculés séparément.

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```


### enable_deadlock_check

?> Activer la détection de deadlock coroutine.

```php
$server->set([
  'enable_deadlock_check' => true
]);
```


### hook_flags

?> **Réglez la portée des fonctions de hook pour la 'co-routineisation' en une seule commande.**【Valeur par défaut : pas de hook】

!> Les versions de Swoole `v4.5+` ou [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) sont disponibles, veuillez consulter [Co-routineisation en une seule commande](/runtime) pour plus de détails.

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
Le niveau inférieur prend en charge les éléments de co-routineisation suivants, où `SWOOLE_HOOK_ALL` représente la co-routineisation de tous :

* `SWOOLE_HOOK_TCP`
* `SWOOLE_HOOK_UNIX`
* `SWOOLE_HOOK_UDP`
* `SWOOLE_HOOK_UDG`
* `SWOOLE_HOOK_SSL`
* `SWOOLE_HOOK_TLS`
* `SWOOLE_HOOK_SLEEP`
* `SWOOLE_HOOK_FILE`
* `SWOOLE_HOOK_STREAM_FUNCTION`
* `SWOOLE_HOOK_BLOCKING_FUNCTION`
* `SWOOLE_HOOK_PROC`
* `SWOOLE_HOOK_CURL`
* `SWOOLE_HOOK_NATIVE_CURL`
* `SWOOLE_HOOK_SOCKETS`
* `SWOOLE_HOOK_STDIO`
* `SWOOLE_HOOK_PDO_PGSQL`
* `SWOOLE_HOOK_PDO_ODBC`
* `SWOOLE_HOOK_PDO_ORACLE`
* `SWOOLE_HOOK_PDO_SQLITE`
* `SWOOLE_HOOK_ALL`
### activer_le_planificateur_préemptif

?> Activer le planificateur préemptif des coroutines pour éviter que l'exécution d'une coroutine ne prenne trop de temps et fasse mourir les autres coroutines par assouplissement, la durée maximale d'exécution d'une coroutine est de `10ms`.

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```

### c_taille_stack / taille_stack

?> Régler la taille de la mémoire de la pile C initiale pour chaque coroutine, par défaut à 2M.

### aio_nb_threads_core

?> Régler le nombre minimum de threads de travail AIO, valeur par défaut égale au nombre de cœurs CPU.

### aio_nb_threads_max

?> Régler le nombre maximum de threads de travail AIO, valeur par défaut égale au nombre de cœurs CPU * 8.

### aio_max_attente_time

?> Temps maximal d'attente pour les threads de travail en secondes.

### aio_max_idle_time

?> Temps maximal d'inactivité pour les threads de travail en secondes.

### nb_threads_reacteur

?> **Régler le nombre de threads de réacteur démarrés.** 【Valeur par défaut : nombre de cœurs CPU】

?> Ce paramètre permet d'ajuster le nombre de threads de traitement d'événements dans le processus principal pour tirer pleinement parti des multi-cœurs. Par défaut, le nombre de threads est égal au nombre de cœurs CPU.  
Les threads de réacteur peuvent utiliser plusieurs cœurs, par exemple : si un machine a 128 cœurs, alors le niveau inférieur lancera 128 threads.  
Chaque thread maintiendra une [EventLoop](/learn?id=qu'est-ce-qu'un-eventloop). Il n'y a pas de verrouillage entre les threads, et les instructions peuvent être exécutées en parallèle par les 128 cœurs CPU.  
Compte tenu du fait qu'il existe certaines pertes de performance due à la planification du système d'exploitation, il est possible de le configurer sur le double du nombre de cœurs CPU afin de maximiser l'utilisation de chaque cœur CPU.

  * **Astuce**

    * Il est conseillé de régler `nb_threads_reacteur` entre 1 et 4 fois le nombre de cœurs CPU
    * La valeur maximale de `nb_threads_reacteur` ne doit pas dépasser 4 fois le nombre de cœurs CPU obtenu avec [swoole_cpu_num()](/functions?id=swoole_cpu_num)

  * **Attention**

  !> - `nb_threads_reacteur` doit être inférieur ou égal à `nb_workers` ;  

- Si `nb_threads_reacteur` est supérieur à `nb_workers`, il sera automatiquement ajusté pour que `nb_threads_reacteur` soit égal à `nb_workers` ;  
- Sur des machines ayant plus de 8 cœurs, `nb_threads_reacteur` est par défaut fixé à 8.
	

### nb_workers

?> **Régler le nombre de processus Worker démarrés.** 【Valeur par défaut : nombre de cœurs CPU】

?> Si une demande prend 100ms pour être traitée et qu'il est nécessaire de fournir une capacité de traitement de 1000 QPS, alors il est nécessaire de configurer 100 processus ou plus.  
Mais plus vous avez de processus en cours, plus la mémoire occupée augmente considérablement, et plus le coût de l'échange entre les processus devient important. Il est donc approprié de ne pas configurer trop grand.

  * **Astuce**

    * Si le code métier est entièrement [ASYNchrone IO](/learn?id=io-Synchronie-et-Asynchrone), il est le plus raisonnable de régler ici entre 1 et 4 fois le nombre de cœurs CPU
    * Si le code métier est [Synchronisé IO](/learn?id=io-Synchronie-et-Asynchrone), il faut ajuster en fonction du temps de réponse des demandes et de la charge système, par exemple : 100-500
    * Valeur par défaut fixée à [swoole_cpu_num()](/functions?id=swoole_cpu_num), ne dépassant pas 1000 fois [swoole_cpu_num()](/functions?id=swoole_cpu_num)
    * Supposons que chaque processus occupe 40M de mémoire, 100 processus nécessiteraient 4G de mémoire.


### max_requests

?> **Régler le nombre maximal de tâches pour les processus Worker.** 【Valeur par défaut : 0, c'est-à-dire que le processus ne se fermera pas】

?> Un processus Worker qui a traité plus de tâches que cette valeur quittera automatiquement, et après la sortie du processus, toutes les mémoires et ressources seront libérées

!> Ce paramètre sert principalement à résoudre le problème de fuite de mémoire PHP dans les processus causé par une mauvaise encodage du programme. Les applications PHP ont une fuite de mémoire lente, mais il est impossible de localiser la cause spécifique ou de résoudre le problème, ce qui peut être temporairement résolu en établissant `max_requests`. Il est nécessaire de trouver et de corriger le code qui cause la fuite de mémoire, plutôt que de résoudre le problème avec cette solution. Il est possible d'utiliser Swoole Tracker pour découvrir le code qui fuyait la mémoire.

  * **Astuce**

    * Ne pas atteindre max_requests ne signifie pas nécessairement fermer immédiatement le processus, veuillez consulter [max_wait_time](/server/setting?id=max_wait_time).
    * Sous [SWOOLE_BASE](/learn?id=swoole_base), atteindre max_requests et redémarrer le processus entraînera la rupture des connexions avec les clients.

  !> Lorsque des erreurs fatales se produisent à l'intérieur d'un processus Worker ou que `exit` est exécuté manuellement, le processus se fermera automatiquement. Le processus maître redémarrera un nouveau processus Worker pour continuer à traiter les demandes


### max_conn / max_connection

?> **Pour le programme server, le nombre maximal de connexions autorisées.** 【Valeur par défaut : `ulimit -n`】

?> Comme `max_connection => 10000`, ce paramètre est utilisé pour configurer le nombre maximal de connexions TCP que le Server peut maintenir. Après avoir dépassé ce nombre, les nouvelles connexions seront rejetées.

  * **Astuce**

    * **Configuration par défaut**

      * Si l'application n'a pas établi de `max_connection`, le niveau inférieur utilisera la valeur de `ulimit -n` comme configuration par défaut
      * Dans les versions 4.2.9 ou supérieures, lorsque le niveau inférieur détecte que `ulimit -n` dépasse 100000, il sera par défaut fixé à 100000, car certains systèmes ont établi `ulimit -n` à 1 million, nécessitant une grande quantité de mémoire, ce qui conduit à un échec du démarrage

    * **Limite supérieure**

      * Veuillez ne pas établir `max_connection` au-delà de 1M

    * **Limite minimale**    
      
      * Si cette option est définie trop petite, le niveau inférieur lancera une erreur et utilisera la valeur de `ulimit -n`.
      * La valeur minimale est de `(nb_workers + nb_task_workers) * 2 + 32`

    ```shell
    serv->max_connection est trop petit.
    ```

    * **Occupation de mémoire**

      * Veuillez ne pas ajuster `max_connection` trop grandement, en fonction de la réalité de la mémoire de la machine. Swoole allouera une grande quantité de mémoire en une seule fois en fonction de cette valeur pour stocker les informations de connexion, et les informations de connexion pour une connexion TCP nécessitent 224 octets.

  * **Attention**

  !> `max_connection` ne doit pas dépasser la valeur de `ulimit -n` du système d'exploitation, sinon une alerte sera affichée et elle sera réinitialisée à la valeur de `ulimit -n`

  ```shell
  WARN swServer_start_check: serv->max_conn dépasse la valeur maximale [100000].

  WARNING set_max_connection: max_connection dépasse la valeur maximale, elle est réinitialisée à 10240
  ```


### nb_task_workers

?> **Configurer le nombre de [Processus Task](/learn?id=taskworkerprocess).**

?> Après avoir configuré ce paramètre, la fonction `task` sera activée. Par conséquent, il est essentiel que le `Server` enregistre les deux fonctions d'événement de rappel [onTask](/server/events?id=ontask) et [onFinish](/server/events?id=onfinish). Sans enregistrement, le programme server ne pourra pas être démarré.

  * **Astuce**

    * Les [Processus Task](/learn?id=taskworkerprocess) sont synchrones et bloquants

    * La valeur maximale ne doit pas dépasser 1000 fois le nombre de cœurs CPU obtenu avec [swoole_cpu_num()](/functions?id=swoole_cpu_num)
    
    * **Méthode de calcul**
      * Si le traitement d'une seule tâche prend 100ms, alors un processus peut traiter 10 tâches par seconde
      * La vitesse à laquelle les tâches sont livrées, par exemple, 2000 tâches par seconde
      * `2000/10=200`, il est nécessaire de configurer `nb_task_workers => 200`, en activant 200 processus Task

  * **Attention**

    !> - À l'intérieur des [Processus Task](/learn?id=taskworkerprocess), il n'est pas possible d'utiliser la méthode `Swoole\Server->task`
### task_ipc_mode

?> **Définissez la manière de communiquer entre le processus [Task](/apprendres?id=processus-taskworker) et les processus `Worker`.**【Valeur par défaut : `1`】 
 
?> Veuillez lire d'abord la [communication IPC sous Swoole](/apprendres?id=qu-est-ce-qu-ipc).


Mode | Effet
---|---
1 | Utiliser la communication via le `socket Unix`【Mode par défaut】
2 | Utiliser le message queue `sysvmsg` pour la communication
3 | Utiliser le message queue `sysvmsg` pour la communication, et设置为 mode de lutte pour la ressource

  * **Avertissement**

    * **Mode `1`**
      * Lorsque vous utilisez le mode `1`, la livraison dirigée est prise en charge, vous pouvez utiliser `dst_worker_id` dans les méthodes [task](/server/methods?id=task) et [taskwait](/server/methods?id=taskwait) pour spécifier le processus `Task` cible.
      * Lorsque `dst_worker_id` est mis à `-1`, le sous-système déterminera automatiquement l'état de chaque processus [Task](/apprendres?id=processus-taskworker) et livrera les tâches au processus en état d'attente.

    * **Mode `2` et `3`**
      * Le mode de message queue utilise les queues de mémoire fournies par l'opération système pour stocker les données, si `message_queue_key` n'est pas spécifié, une queue privée sera utilisée et sera supprimée après la fin du programme `Server`.
      * Après avoir spécifié la clé de message queue, les données de la queue ne seront pas supprimées après la fin du programme `Server`, donc même après le redémarrage du processus, elles peuvent encore être récupérées.
      * Vous pouvez utiliser la commande `ipcrm -q` avec l'ID de la queue de message pour supprimer manuellement les données de la queue de message.
      * La différence entre le mode `2` et le mode `3` est que le mode `2` prend en charge la livraison dirigée, `$serv->task($data, $task_worker_id)` peut spécifier à quel processus [task](/apprendres?id=processus-taskworker) la livraison doit être dirigée. Le mode `3` est entièrement en mode de lutte pour la ressource, les processus [task](/apprendres?id=processus-taskworker) se battront pour la queue, la livraison dirigée ne sera pas prise en charge, et `task/taskwait` ne pourra pas spécifier l'ID du processus cible, même si `$task_worker_id` est spécifié, cela sera invalide dans le mode `3`.

  * **Note**

    !> - Le mode `3` affectera la méthode [sendMessage](/server/methods?id=sendMessage), faisant en sorte que les messages envoyés par [sendMessage](/server/methods?id=sendMessage) soient aléatoirement pris en charge par un processus [task](/apprendres?id=processus-taskworker).  
    - Lors de la communication avec le message queue, si la capacité de traitement du processus `Task` est inférieure à la vitesse de livraison, cela peut entraîner un blocage du processus `Worker`.  
    - Après avoir utilisé la communication avec le message queue, les processus `task` ne peuvent plus soutenir les coroutines (l'activation de [task_enable_coroutine](/server/settings?id=task_enable_coroutine)).  


### task_max_request

?> **Définissez le nombre maximal de tâches pour le processus [task](/apprendres?id=processus-taskworker).**【Valeur par défaut : `0`】

Définissez le nombre maximal de tâches pour le processus `task`. Un processus `task` se retirera automatiquement après avoir traité plus de tâches que ce nombre. Ce paramètre est destiné à empêcher le dépassement de la mémoire du processus PHP. Si vous ne souhaitez pas que le processus se retire automatiquement, vous pouvez le configurer sur `0`.


### task_tmpdir

?> **Définissez le répertoire temporaire pour les données du task.**【Valeur par défaut : Directoire `/tmp` sous Linux】

?> Dans le `Server`, si les données livrées dépassent `8180` octets, des fichiers temporaires seront utilisés pour stocker les données. Le `task_tmpdir` est utilisé pour configurer l'emplacement où les fichiers temporaires seront sauvegardés.

  * **Avertissement**

    * Le sous-système utilise par défaut le répertoire `/tmp` pour stocker les données du `task`, si votre version du noyau Linux est trop basse et que le répertoire `/tmp` n'est pas un système de fichiers en mémoire, vous pouvez le configurer sur `/dev/shm/`
    * Si le répertoire `task_tmpdir` n'existe pas, le sous-système tentera automatiquement de le créer

  * **Note**

    !> - Si la création échoue, le démarrage du `Server->start` échouera


### task_enable_coroutine

?> **Activer le support des coroutines pour les tâches.**【Valeur par défaut : `false`】, support à partir de la version v4.2.12

?> Une fois activé, des coroutines et un conteneur de coroutines sont automatiquement créés dans la回调[onTask](/server/events?id=ontask), et le code PHP peut utiliser directement l'API des coroutines.

  * **Exemple**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    // ID du Worker process de provenance
    $task->worker_id;
    // Numéro de la tâche
    $task->id;
    // Type de tâche, taskwait, task, taskCo, taskWaitMulti peuvent utiliser différents flags
    $task->flags;
    // Données de la tâche
    $task->data;
    // Temps de délivrance, ajouté dans la version v4.6.0
    $task->dispatch_time;
    // API coroutine
    co::sleep(0.2);
    // Finir la tâche, terminer et retourner des données
    $task->finish([123, 'hello']);
});
```

  * **Note**

    !> - `task_enable_coroutine` ne peut être utilisé que si [enable_coroutine](/server/settings?id=enable_coroutine) est `true`  
    - Activer `task_enable_coroutine` permet aux processus de travail Task de soutenir les coroutines  
    - Sans activer `task_enable_coroutine`, seuls les modes de blocage synchrone sont pris en charge


### task_use_object/task_object :id=task_use_object

?> **Utiliser le style de callback Task orienté objet.**【Valeur par défaut : `false`】

?> Lorsque vous le setez à `true`, la callback [onTask](/server/events?id=ontask) deviendra un mode objet.

  * **Exemple**

```php
<?php

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 3,
    'task_use_object' => true,
//    'task_object' => true, // Alias ajouté dans la version v4.6.0
]);
$server->on('receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd' => $fd,]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    // Ici, $task est un objet Swoole\Server\Task
    $server->send($task->data['fd'], json_encode($server->stats()));
});
$server->start();
```


### dispatch_mode

?> **Stratégie de distribution des paquets de données.**【Valeur par défaut : `2`】


Valeur du mode | Mode | Effet
---|---|---
1 | Mode de rotation | Les paquets reçus sont distribués en rotation à chaque processus `Worker`
2 | Mode fixe | Les processus `Worker` sont attribués en fonction du descripteur de fichier de la connexion. Cela peut garantir que les données envoyées par la même connexion sont traitées par le même processus `Worker`
3 | Mode de prélèvement | Le processus principal choisira de livrer en fonction de l'état occupé/libre des processus `Worker`, ne livrant que aux processus en état d'attente
4 | Attribution basée sur l'IP | Les connexions sont attribuées à un processus `Worker` fixe en fonction du hash modulo de l'IP client. Cela peut garantir que les données de connexions provenant de la même source IP sont toujours attribuées au même processus `Worker`. Algorithme : `inet_addr_mod(ClientIP, worker_num)`
5 | Attribution basée sur UID | Il est nécessaire d'appeler [Server->bind()](/server/methods?id=bind) dans le code utilisateur pour lier une connexion à un `uid`. Ensuite, le sous-système attribue différents processus `Worker` en fonction de la valeur de `UID`. Algorithme : `UID % worker_num`, si vous devez utiliser une chaîne comme `UID`, vous pouvez utiliser `crc32(UID_STRING)`
7 | Mode de flux | Les processus `Worker` inactifs accepteront les connexions et accepteront de nouvelles demandes du [Reactor](/apprendres?id=reactor-thread)

  * **Avertissement**

    * **Suggestions d'utilisation**
    
      * Pour les serveurs sans état, utilisez `1` ou `3`, pour les serveurs synchrone bloquant utilisez `3`, pour les serveurs asynchrones non bloquants utilisez `1`
      * Pour les serveurs avec état, utilisez `2`, `4`, `5`
      
    * **Protocole UDP**

      * Lorsque `dispatch_mode=2/4/5`, l'attribution est fixe, le sous-système utilise l'IP client pour effectuer un hash modulo et attribuer à différents processus `Worker`
      * Lorsque `dispatch_mode=1/3`, les attributions sont aléatoires à différents processus `Worker`
      * Fonction `inet_addr_mod`

```
    function inet_addr_mod($ip, $worker_num) {
        $ip_parts = explode('.', $ip);
        if (count($ip_parts) != 4) {
            return false;
        }
        $ip_parts = array_reverse($ip_parts);
    
        $ip_long = 0;
        foreach ($ip_parts as $part) {
            $ip_long <<= 8;
            $ip_long |= (int) $part;
        }
    
        return $ip_long % $worker_num;
    }
```
  * **Mode de base**
    * La configuration de `dispatch_mode` dans le mode [SWOOLE_BASE](/apprendres?id=swoole_base) est invalide, car le mode BASE n'existe pas pour la distribution des tâches, lorsqu'un client envoie des données, le sous-système répond immédiatement dans le contexte actuel de thread/process avec la callback [onReceive](/server/events?id=onreceive), sans besoin de distribution aux processus `Worker`.

  * **Note**

    !> - Lorsque `dispatch_mode=1/3`, le sous-système masquera les événements `onConnect/onClose`, car ces deux modes ne peuvent pas garantir l'ordre des événements `onConnect/onClose/onReceive` ;  
    - Pour les programmes de serveur qui ne sont pas responsifs aux demandes, veuillez ne pas utiliser le mode `1` ou `3`. Par exemple : les services HTTP sont responsifs et peuvent utiliser `1` ou `3`, ceux avec des connexions TCP à long terme ne peuvent pas utiliser `1` ou `3`.
### dispatch_func

?> En setting la fonction `dispatch`, le moteur Swoole intègre six modes de [dispatch](/server/setting?id=dispatch_mode). Si cela ne répond toujours pas aux besoins, vous pouvez utiliser la rédaction de fonctions en C++ ou en PHP pour mettre en œuvre la logique de dispatch.

  * **Méthode d'utilisation**

```php
$server->set(array(
  'dispatch_func' => 'my_dispatch_function',
));
```

  * **Astuces**

    * Après avoir défini `dispatch_func`, le moteur ignore automatiquement la configuration de `dispatch_mode` en dessous.
    * Si la fonction correspondant à `dispatch_func` n'existe pas, le moteur provoquera une erreur fatale.
    * Si vous devez dispatch un paquet dépassant 8K, `dispatch_func` ne pourra obtenir que les premiers 8180字节 du contenu.

  * **Écrire une fonction PHP**

    ?> Comme le moteur ZendVM ne peut pas soutenir un environnement multithread, même si plusieurs threads de [Reactor](/learn?id=reactor线程) sont définis, seule une `dispatch_func` peut être exécutée à la fois. Par conséquent, le moteur effectue des opérations de verrouillage lors de l'exécution de cette fonction PHP, ce qui peut poser des problèmes de concurrence pour les verrous. Veuillez ne pas exécuter d'opérations bloquantes dans `dispatch_func`, sinon cela peut entraîner l'arrêt du groupe de threads Reactor.

    ```php
    $server->set(array(
        'dispatch_func' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd` est l'identifiant unique de la connexion client, accessible via `Server::getClientInfo`.
    * `$type` est le type de données, `0` représente la transmission de données de la part du client, `4` représente l'établissement d'une connexion client, `3` représente la fermeture d'une connexion client.
    * `$data` contient le contenu des données. Il est important de noter que si des paramètres tels que `HTTP`, `EOF`, `Length` sont activés, le moteur effectuera le拼接 des paquets. Cependant, dans la fonction `dispatch_func`, seuls les premiers 8K du contenu du paquet peuvent être transmis, et pas le contenu complet du paquet.
    * **Il est essentiel** de retourner un nombre entre `0` et `(server->worker_num - 1)`, qui représente l'ID du processus de travail cible pour la livraison du paquet.
    * Un ID inférieur à `0` ou supérieur ou égal à `server->worker_num` est considéré comme un ID cible anormal, et les données dispatchées seront abandonnées.

  * **Écrire une fonction C++**

    **Dans d'autres extensions PHP, utilisez swoole_add_function pour enregistrer une fonction de longueur dans le moteur Swoole.**

    ?> Lors de l'appel de la fonction C++, le moteur ne verrouille pas en dessous, et il est à la charge de l'appelant de garantir la sécurité线程.

    ```c++
    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data);

    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data)
    {
        printf("cpp, type=%d, size=%d\n", data->info.type, data->info.len);
        return data->info.len % serv->worker_num;
    }

    int register_dispatch_function(swModule *module)
    {
        swoole_add_function("my_dispatch_function", (void *) dispatch_function);
    }
    ```

    * La fonction `dispatch` doit retourner l'ID du processus de travail cible pour la livraison.
    * Le `worker_id` retourné ne doit pas dépasser `server->worker_num`, sinon le moteur provoquera une erreur de segmentation.
    * Le retourner un nombre négatif `(return -1)` signifie abandonner ce paquet de données.
    * `data` peut être utilisé pour lire le type et la longueur de l'événement.
    * `conn` contient des informations sur la connexion. Si c'est un paquet UDP, `conn` est `NULL`.

  * **Remarques**

    !> - La fonction `dispatch_func` est uniquement valide dans le mode [SWOOLE_PROCESS](/learn?id=swoole_process), et est applicable aux serveurs de type [UDP/TCP/UnixSocket](/server/methods?id=__construct).  
    - Le `worker_id` retourné ne doit pas dépasser `server->worker_num`, sinon le moteur provoquera une erreur de segmentation.


### message_queue_key

?> **Définissez la clé de la file de message.**【Valeur par défaut : `ftok($php_script_file, 1)`】

?> Utilisé uniquement lorsque [task_ipc_mode](/server/setting?id=task_ipc_mode) est égal à 2 ou 3. La clé définie sert uniquement comme clé pour la file de message des tâches Task, voir [la communication IPC sous Swoole](/learn?id=什么是IPC).

?> La file de tâches Task ne sera pas détruite après la fin du serveur, et les tâches dans la file seront continuellement traitées par les processus TaskWorker après le redémarrage du programme. Si vous ne souhaitez pas que les anciennes tâches Task soient exécutées après le redémarrage du programme, vous pouvez supprimer manuellement cette file de message.

```shell
ipcs -q 
ipcrm -Q [msgkey]
```


### daemonize

?> **Daemonisation**【Valeur par défaut : `false`】

?> Lorsque `daemonize => true` est défini, le programme se transformera en un service en arrière-plan fonctionnant en tant que démon. Il est essentiel pour les programmes de serveur à long terme d'activer cette option.  
Si la daemonisation n'est pas activée, le programme sera arrêté lorsque la session SSH est fermée.

  * **Astuces**

    * Après avoir activé la daemonisation, l'entrée standard et la sortie seront redirigées vers `log_file`.
    * Si `log_file` n'est pas défini, elles seront redirigées vers `/dev/null`, et toutes les informations affichées à l'écran seront ignorées.
    * Après avoir activé la daemonisation, la valeur de l'environnement variable `CWD` (dossier actuel) changera, ce qui peut entraîner des erreurs lors de l'écriture et de la lecture de fichiers avec des chemins absolus. Dans les programmes PHP, il est impératif d'utiliser des chemins absolus.

    * **systemd**

      * Lorsque vous gérez le service Swoole avec `systemd` ou `supervisord`, ne définissez pas `daemonize => true`. La raison principale est que le mécanisme de `systemd` diffère de celui de `init`. Le processus `init` a un PID de `1`, et après que le programme a utilisé `daemonize`, il se sépare du terminal et est finalement géré par le processus `init`, devenant ainsi un processus fils.
      * Cependant, `systemd` lance un processus de fond séparé qui gère d'autres processus de service en se forkant lui-même, donc il n'est pas nécessaire de utiliser `daemonize`. Au contraire, l'utilisation de `daemonize => true` peut faire perdre au programme Swoole sa relation de processus fils avec ce gestionnaire de processus.


### backlog

?> **Définissez la longueur de la file d'attente `Listen`**

?> Par exemple, si `backlog => 128`, cette paramètre déterminera le nombre maximal de connexions en attente d'acceptation simultanées.

  * **À propos du `backlog` pour TCP**

    ?> Le TCP comprend un processus de trois mains de handshake, le client `syn=>serveur` `syn+ack=>client` `ack`, lorsque le serveur reçoit l'ack du client, il met la connexion dans une file appelée `queue d'acceptation` (note 1),  
    La taille de la file est déterminée par la valeur du paramètre `backlog` et la valeur minimale de la configuration `somaxconn`, vous pouvez utiliser la commande `ss -lt` pour voir la taille finale de la file d'acceptation, le processus principal Swoole appelle `accept` (note 2)  
    pour en retirer. Lorsque la file d'acceptation est pleine, les connexions peuvent réussir (note 4),  
    ou échouer, et l'apparence du client en cas d'échec est que la connexion est réinitialisée (note 3)  
    ou qu'elle expire en temps imparti, et le serveur enregistrera l'échec. Vous pouvez voir les journaux en utilisant la commande `netstat -s|grep 'times the listen queue of a socket overflowed'`. Si vous voyez ce phénomène, vous devriez augmenter cette valeur. Heureusement, le mode SWOOLE_PROCESS de Swoole est différent de celui de PHP-FPM/Apache et d'autres logiciels, et ne dépend pas de `backlog` pour résoudre les problèmes de file d'attente des connexions. Ainsi, vous ne rencontrez généralement pas ce phénomène.

    * Note 1: Après la version `linux2.2`, le processus de handshake est divisé en deux files : `queue de syn` et `queue d'acceptation`, la longueur de la file `queue de syn` est déterminée par la configuration `tcp_max_syn_backlog`.
    * Note 2: Les versions kernel plus récentes utilisent `accept4` pour économiser une invocation système `set no block`.
    * Note 3: Lorsque le client reçoit le paquet `syn+ack`, il considère que la connexion est réussie, mais en réalité, le serveur est toujours dans un état de connexion semi-établie et peut envoyer un paquet `rst` au client, ce qui entraînera l'apparence de la connexion comme étant réinitialisée par l'autre partie.
    * Note 4: Le succès est réalisé par le mécanisme de redépartage TCP, les configurations connexes incluent `tcp_synack_retries` et `tcp_abort_on_overflow`.
### open_tcp_keepalive

?> Dans le `TCP`, il existe un mécanisme `Keep-Alive` qui peut détecter les connexions mortes. Si l'application n'est pas sensible au cycle des connexions mortes ou n'a pas mis en œuvre un mécanisme de battement de cœur, elle peut utiliser le mécanisme `keepalive` fourni par l'opération system pour éliminer les connexions mortes.
Dans la configuration de [Server->set()](/server/methods?id=set), ajouter `open_tcp_keepalive => true` signifie activer le `TCP keepalive`.
De plus, il y a 3 options qui peuvent être ajustées pour les détails du `keepalive`.

  * **Options**

     * **tcp_keepidle**

        En secondes, si une connexion n'a pas de demande de données pendant `n` secondes, elle commencera à détecter cette connexion.

     * **tcp_keepcount**

        Le nombre de détections, après avoir dépassé ce nombre, la connexion sera fermée.

     * **tcp_keepinterval**

        L'intervalle de détection, en secondes.

  * **Exemple**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, //4s sans transmission de données pour commencer la détection
    'tcp_keepinterval' => 1, //détection toutes les 1s
    'tcp_keepcount' => 5, //number de détections, si plus de 5 sans réponse, fermer la connexion
));

$serv->on('connect', function ($serv, $fd) {
    var_dump("Client:Connect $fd");
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    var_dump($data);
});

$serv->on('close', function ($serv, $fd) {
  var_dump("close fd $fd");
});

$serv->start();
```


### heartbeat_check_interval

?> **Activer la détection de battement de cœur**【Valeur par défaut : `false`】

?> Cette option indique la fréquence de la vérification en secondes. Par exemple, `heartbeat_check_interval => 60` signifie que toutes les 60 secondes, toutes les connexions sont vérifiées. Si une connexion n'a pas envoyé de données au serveur pendant 120 secondes (lorsque `heartbeat_idle_time` n'est pas configuré, il est par défaut le double de `interval`), cette connexion sera fermée de force. Si elle n'est pas configurée, le battement de cœur n'est pas activé, et cette configuration est par défaut désactivée.

  * **Avis**
    * Le `Server` ne envoie pas activement de paquets de battement de cœur au client, mais attend passivement que le client envoie des paquets de battement de cœur. La détection de battement de cœur du côté serveur vérifie simplement quand la dernière donnée a été envoyée sur la connexion. Si elle dépasse la limite, la connexion sera coupée.
    * Les connexions coupées par la détection de battement de cœur déclenchent toujours l'événement de callback [onClose](/server/events?id=onclose).

  * **Remarque**

    !> La détection de battement de cœur ne prend en charge que les connexions `TCP`.


### heartbeat_idle_time

?> **Temps maximal autorisé pour une connexion inerte**

?> Il doit être utilisé conjointement avec `heartbeat_check_interval`.

```php
array(
    'heartbeat_idle_time'      => 600, // Une connexion sera fermée de force si elle n'envoie aucune donnée au serveur pendant 600 secondes
    'heartbeat_check_interval' => 60,  // Verifier toutes les 60 secondes
);
```

  * **Avis**

    * Après avoir activé `heartbeat_idle_time`, le serveur ne enverra pas activement de paquets de données au client.
    * Si vous avez seulement activé `heartbeat_idle_time` sans configurer `heartbeat_check_interval`, le côté基础设施 ne créera pas de thread de détection de battement de cœur, et vous pouvez appeler manuellement la méthode `heartbeat` dans votre code PHP pour gérer les connexions dépassées.


### open_eof_check

?> **Activer la détection EOF**【Valeur par défaut : `false`】, voir [Problème de frontière de paquet TCP](/learn?id=probleme-de-frontière-de-paquet-tcp)

?> Cette option détectera les données envoyées par la connexion client, et ne transmettra le paquet au processus Worker que lorsque la fin du paquet est la chaîne spécifiée. Sinon, il continuera à concatener les paquets jusqu'à ce qu'il dépasse la zone de stockage ou que le délai expire. Lorsqu'une erreur se produit, le côté基础设施 considérera cela comme une connexion malveillante, ignorera les données et fermera la connexion de force.  
Les protocoles couramment utilisés tels que `Memcache/SMTP/POP` se terminent par `\r\n`, et cette configuration peut être utilisée. En l'activant, vous pouvez vous assurer que le processus Worker reçoit toujours un ou plusieurs paquets complets d'un seul coup.

```php
array(
    'open_eof_check' => true,   //activer la détection EOF
    'package_eof'    => "\r\n", //configurer EOF
)
```

  * **Remarque**

    !> Cette configuration est uniquement valide pour les types de Sockets `STREAM`, comme [TCP, Unix Socket Stream](/server/methods?id=__construct)   
    La détection EOF ne cherchera pas la chaîne EOF dans le milieu des données, donc le processus Worker pourrait recevoir plusieurs paquets simultanément, et vous devez décomposer les paquets dans votre code d'application en utilisant `explode("\r\n", $data)`.


### open_eof_split

?> **Activer le splitting des paquets EOF**

?> Lorsque `open_eof_check` est activé, il est possible que plusieurs paquets soient fusionnés dans un seul paquet. L'argument `open_eof_split` peut résoudre ce problème, voir [Problème de frontière de paquet TCP](/learn?id=probleme-de-frontière-de-paquet-tcp).

?> La configuration de cet argument nécessite de parcourir tout le contenu du paquet pour rechercher l'EOF, ce qui consommera beaucoup de ressources CPU. Supposons que chaque paquet soit de `2M` et qu'il y ait `10000` demandes par seconde, cela pourrait générer `20G` instructions de correspondance de caractères CPU.

```php
array(
    'open_eof_split' => true,   //activer la détection du splitting des paquets EOF
    'package_eof'    => "\r\n", //configurer EOF
)
```

  * **Avis**

    * Après avoir activé l'argument `open_eof_split`, le côté基础设施 recherchera l'EOF dans le milieu des données et dividera les paquets. [onReceive](/server/events?id=onreceive) recevra uniquement un paquet terminé par la chaîne EOF à chaque fois.
    * Après avoir activé l'argument `open_eof_split`, peu importe si l'argument `open_eof_check` est activé ou non, `open_eof_split` sera effectif.

    * **Différence avec `open_eof_check`**
    
        * `open_eof_check` ne vérifie que la fin des données reçues est l'EOF, donc sa performance est la meilleure et il consomme presque rien.
        * `open_eof_check` ne peut pas résoudre le problème de la fusion de plusieurs paquets, par exemple, si deux paquets avec EOF sont envoyés simultanément, le côté基础设施 pourrait les renvoyer tous d'un seul coup.
        * `open_eof_split` comparera chaque byte des données de gauche à droite pour rechercher l'EOF et diviser les paquets, ce qui est moins performant. Mais il ne renverra qu'un paquet à la fois.


### package_eof

?> **Configurer la chaîne EOF.** Voir [Problème de frontière de paquet TCP](/learn?id=probleme-de-frontière-de-paquet-tcp)

?> Il doit être utilisé conjointement avec `open_eof_check` ou `open_eof_split`.

  * **Remarque**

    !> La chaîne EOF autorisée est au maximum de `8` octets.


### open_length_check

?> **Activer la caractéristique de vérification de longueur de paquet**【Valeur par défaut : `false`】, voir [Problème de frontière de paquet TCP](/learn?id=probleme-de-frontière-de-paquet-tcp)

?> La vérification de longueur de paquet offre une interprétation du protocole avec une tête fixe + un corps de paquet. Une fois activée, cela peut vous assurer que le processus Worker [onReceive](/server/events?id=onreceive) recevra toujours un paquet complet à chaque fois.  
Le protocole de vérification de longueur nécessite de calculer la longueur une seule fois, et le traitement des données ne fait que déplacer le curseur, ce qui est très performant, **recommandé**.

  * **Avis**

    * **Le protocole de longueur offre 3 options pour contrôler les détails du protocole.**

      ?> Cette configuration est uniquement valide pour les types de Sockets `STREAM`, comme [TCP, Unix Socket Stream](/server/methods?id=__construct)

      * **package_length_type**

        ?> Un champ dans la tête sert de valeur de longueur de paquet, le côté基础设施 prend en charge 10 types de longueurs. Veuillez consulter [package_length_type](/server/setting?id=package_length_type)

      * **package_body_offset**

        ?> À partir duquel octet commence-t-on à calculer la longueur, il y a généralement 2 cas :

        * La valeur de `length` comprend tout le paquet (tête + corps), `package_body_offset` est `0`
        * La longueur de la tête est de `N` octets, la valeur de `length` ne comprend pas la tête, elle comprend uniquement le corps du paquet, `package_body_offset` est fixé à `N`

      * **package_length_offset**

        ?> À quel octet dans la tête se trouve la valeur de la longueur.

        * Exemple :

        ```c
        struct
        {
            uint32_t type;
            uint32_t uid;
            uint32_t length;
            uint32_t serid;
            char body[0];
        }
        ```
        
    ?> Dans la conception de ces protocoles de communication, la longueur de la tête est de `4` entiers, `16` octets, et la valeur de la longueur se trouve à l'octet `3` de l'entier. Par conséquent, `package_length_offset` est fixé à `8`, les octets `0-3` sont pour `type`, les octets `4-7` pour `uid`, les octets `8-11` pour `length`, et les octets `12-15` pour `serid`.

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```
### package_length_type

?> **Type de valeur de longueur**, qui accepte un paramètre de caractère, conforme à la fonction [pack](http://php.net/manual/zh/function.pack.php) de PHP.

Actuellement, Swoole prend en charge 10 types :


Paramètre de caractère | Effet
---|---
c | Signé, 1 octet
C | Unsigned, 1 octet
s | Signé, big-endian, 2 octets
S | Unsigned, big-endian, 2 octets
n | Unsigned, network byte order, 2 octets
N | Unsigned, network byte order, 4 octets
l | Signé, big-endian, 4 octets (petit L)
L | Unsigned, big-endian, 4 octets (grand L)
v | Unsigned, little-endian, 2 octets
V | Unsigned, little-endian, 4 octets


### package_length_func

?> **Définir la fonction de résolution de longueur**

?> Prend en charge 2 types de fonctions, soit en C++ soit en PHP. La fonction de longueur doit retourner un entier.


Valeur de retour | Effet
---|---
Retourne 0 | Les données de longueur ne sont pas suffisantes, il est nécessaire de recevoir plus de données
Retourne -1 | Error de données, le niveau inférieur fermera automatiquement la connexion
Retourne la valeur de longueur du paquet (y compris la longueur du header et du corps du paquet) | Le niveau inférieur assemblera automatiquement le paquet et le retournera à la fonction de rappel

  * **Aide**

    * **Méthode d'utilisation**

    ?> La principle de réalisation est d'abord de lire une petite partie de données, qui contient une valeur de longueur. Ensuite, cette valeur de longueur est retournée au niveau inférieur. Ensuite, le niveau inférieur termine la réception des données restantes et les assemble en un paquet pour le dispatch.

    * **Fonction de résolution de longueur PHP**

    ?> Comme le moteur ZendVM ne prend pas en charge l'exécution dans un environnement multithread, le niveau inférieur utilise automatiquement un verrou Mutex pour verrouiller la fonction de longueur PHP, afin d'éviter l'exécution conjointe des fonctions PHP. Disponible à partir de la version 1.9.3 ou supérieure.

    !> Veuillez ne pas effectuer d'opérations IO bloquantes dans la fonction de résolution de longueur, car cela pourrait entraîner le blocage de tous les threads Reactor (/learn?id=reactor).

    ```php
    $server = new Swoole\Server("127.0.0.1", 9501);
    
    $server->set(array(
        'open_length_check'   => true,
        'dispatch_mode'       => 1,
        'package_length_func' => function ($data) {
          if (strlen($data) < 8) {
              return 0;
          }
          $length = intval(trim(substr($data, 0, 8)));
          if ($length <= 0) {
              return -1;
          }
          return $length + 8;
        },
        'package_max_length'  => 2000000,  //Longueur maximale du protocole
    ));
    
    $server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        var_dump($data);
        echo "#{$server->worker_id}>> received length=" . strlen($data) . "\n";
    });
    
    $server->start();
    ```

    * **Fonction de résolution de longueur C++**

    ?> Dans d'autres extensions PHP, utilisez `swoole_add_function` pour enregistrer la fonction de longueur dans le moteur Swoole.
    
    !> Lorsque la fonction de longueur C++ est appelée, le niveau inférieur ne verrouille pas automatiquement, il est donc nécessaire que la partie appelante assure elle-même la sécurité thread.
    
    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"
    
    using namespace std;
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);
    
    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW_OK;
    }
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length)
    {
        printf("cpp, size=%d\n", length);
        return 100;
    }
    ```


### package_max_length

?> **Définir la taille maximale du paquet en octets.**【Valeur par défaut : `2M` c'est-à-dire `2 * 1024 * 1024`, la valeur minimale est de `64K`】

?> Après avoir activé la résolution des protocoles [open_length_check](/server/setting?id=open_length_check)/[open_eof_check](/server/setting?id=open_eof_check)/[open_eof_split](/server/setting?id=open_eof_split)/[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol), le niveau inférieur de Swoole effectue le assemblage des paquets. À ce moment-là, tant que le paquet n'est pas entièrement reçu, toutes les données sont stockées en mémoire.
Il est donc nécessaire de définir `package_max_length`, la taille maximale autorisée pour un paquet en mémoire. Si 10 000 connexions TCP envoient des données, chaque paquet de 2M, alors dans le cas extrême, cela occupera 20G d'espace mémoire.

  * **Aide**

    * `open_length_check` : Lorsque la longueur du paquet dépasse `package_max_length`, le paquet sera directement abandonné et la connexion fermée, ne occupant aucune mémoire ;
    * `open_eof_check` : Comme il est impossible de connaître à l'avance la longueur du paquet, les données reçues sont toujours stockées en mémoire et augmentent de manière continue. Lorsque l'utilisation de la mémoire dépasse `package_max_length`, le paquet sera directement abandonné et la connexion fermée ;
    * `open_http_protocol` : La demande HTTP `GET` autorise une taille maximale de 8K, et il est impossible de modifier la configuration. La demande HTTP `POST` détecte la `Content-Length`, et si la `Content-Length` dépasse `package_max_length`, le paquet sera directement abandonné, une erreur HTTP 400 sera envoyée et la connexion fermée ;

  * **Note**

    !> Il ne faut pas fixer cette paramètre trop élevé, sinon cela occupera beaucoup d'espace mémoire


### open_http_protocol

?> **Activer le traitement du protocole HTTP.**【Valeur par défaut : `false`】

?> Activer le traitement du protocole HTTP, l'option [Swoole\Http\Server](/http_server) est automatiquement activée. La configuration en `false` signifie désactiver le traitement du protocole HTTP.


### open_mqtt_protocol

?> **Activer le traitement du protocole MQTT.**【Valeur par défaut : `false`】

?> Une fois activé, il interprète le header MQTT, et chaque worker process [onReceive](/server/events?id=onreceive) retourne un paquet MQTT complet.

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```


### open_redis_protocol

?> **Activer le traitement du protocole Redis.**【Valeur par défaut : `false`】

?> Une fois activé, il interprète le protocole Redis, et chaque worker process [onReceive](/server/events?id=onreceive) retourne un paquet Redis complet. Il est préférable d'utiliser directement [Redis\Server](/redis_server).

```php
$server->set(array(
  'open_redis_protocol' => true
));
```


### open_websocket_protocol

?> **Activer le traitement du protocole WebSocket.**【Valeur par défaut : `false`】

?> Une fois activé, le traitement du protocole websocket est automatiquement activé avec [Swoole\WebSocket\Server](websocket_server). La configuration en `false` signifie désactiver le traitement du websocket.  
Lorsque l'option `open_websocket_protocol` est définie à `true`, elle active automatiquement l'option `open_http_protocol`.


### open_websocket_close_frame

?> **Activer les帧 de fermeture dans le protocole websocket.**【Valeur par défaut : `false`】

?> (le frame avec l'opcode `0x08`) Récupéré dans la回调 `onMessage`

?> Une fois activé, vous pouvez recevoir des frames de fermeture envoyés par le client ou le serveur dans la回调 `onMessage` de `WebSocketServer`. Les développeurs peuvent les gérer eux-mêmes.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->set(array("open_websocket_close_frame" => true));

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Frame de fermeture reçu : Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message reçu : {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {});

$server->start();
```
### open_tcp_nodelay

?> **Activer `open_tcp_nodelay`.** 【Valeur par défaut : `false`】

?> Lorsqu'il est activé, la transmission de données sur les connexions TCP désactive l'algorithme de fusion Nagle et envoie immédiatement les données au côté TCP correspondant. Dans certains cas, comme pour une interface de commande en ligne, il est nécessaire d'envoyer un commandement immédiatement à la server pour améliorer la vitesse de réponse. Veuillez consulter la documentation sur l'algorithme Nagle sur Google.


### open_cpu_affinity 

?> **Activer la configuration de affinité CPU.** 【Valeur par défaut `false`】

?> Sur des plateformes硬件 à plusieurs cœurs, activer cette caractéristique lie les threads du réacteur / processus worker de Swoole à un cœur fixe. Cela peut éviter le changement de runtime de processus/threads entre plusieurs cœurs, améliorant le taux de hit du cache CPU.

  * **Astuce**

    * **Utiliser la commande taskset pour voir la configuration d'affinité CPU d'un processus :**

    ```bash
    taskset -p 进程ID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > Le masque est un nombre binaire, chaque bit correspondant à un cœur CPU. Si un bit est à `0`, cela signifie que le processus est lié à ce cœur et sera planifié sur ce cœur CPU. Si un bit est à `1`, cela signifie que le processus n'est pas planifié sur ce cœur CPU. Dans l'exemple, le processus avec pid `24666` a un masque `f`, ce qui signifie qu'il n'est pas lié à un cœur CPU et que l'opération system le planifiera sur n'importe quel cœur CPU. Le processus avec pid `24901` a un masque `8` en binaire, qui est `1000`, ce qui signifie qu'il est lié au quatrième cœur CPU.


### cpu_affinity_ignore

?> **Dans les programmes à forte charge I/O, tous les interrupts réseau sont traités par le CPU0. Si les I/O réseau sont lourds, une charge élevée du CPU0 peut entraîner un traitement en retard des interrupts réseau, réduisant ainsi la capacité de réception et d'envoi de paquets réseau.**

?> Si cette option n'est pas définie, Swoole utilisera tous les cœurs CPU. Le niveau inférieur établira la liaison CPU en fonction de reactor_id ou worker_id modulo le nombre de cœurs CPU. Si le noyau et le chipset réseau ont des caractéristiques de plusieurs files d'attente, les interruits réseau seront répartis sur plusieurs cœurs, ce qui peut soulager la pression des interruits réseau.

```php
array('cpu_affinity_ignore' => array(0, 1)) // Prendre un tableau en tant que paramètre, array(0, 1) signifie ne pas utiliser le CPU0, CPU1, mais les laisser spécifiquement pour gérer les interruits réseau.
```

  * **Astuce**

    * **Vérifier les interruits réseau**

```shell
[~]$ cat /proc/interrupts 
           CPU0       CPU1       CPU2       CPU3       
  0: 1383283707          0          0          0    IO-APIC-edge  timer
  1:          3          0          0          0    IO-APIC-edge  i8042
  3:         11          0          0          0    IO-APIC-edge  serial
  8:          1          0          0          0    IO-APIC-edge  rtc
  9:          0          0          0          0   IO-APIC-level  acpi
 12:          4          0          0          0    IO-APIC-edge  i8042
 14:         25          0          0          0    IO-APIC-edge  ide0
 82:         85          0          0          0   IO-APIC-level  uhci_hcd:usb5
 90:         96          0          0          0   IO-APIC-level  uhci_hcd:usb6
114:    1067499          0          0          0       PCI-MSI-X  cciss0
130:   96508322          0          0          0         PCI-MSI  eth0
138:     384295          0          0          0         PCI-MSI  eth1
169:          0          0          0          0   IO-APIC-level  ehci_hcd:usb1, uhci_hcd:usb2
177:          0          0          0          0   IO-APIC-level  uhci_hcd:usb3
185:          0          0          0          0   IO-APIC-level  uhci_hcd:usb4
NMI:      11370       6399       6845       6300 
LOC: 1383174675 1383278112 1383174810 1383277705 
ERR:          0
MIS:          0
```

`eth0/eth1` représente le nombre d'interrupts réseau. Si `CPU0 - CPU3` est réparti de manière égale, cela indique que le chipset réseau a des caractéristiques de plusieurs files d'attente. Si tout est concentré sur un seul cœur, cela signifie que tous les interruits réseau sont traités par ce CPU. Une fois que ce CPU dépasse `100%`, le système ne pourra plus traiter les demandes réseau. Dans ce cas, il est nécessaire d'utiliser la configuration `cpu_affinity_ignore` pour laisser ce CPU libre et spécifiquement pour gérer les interruits réseau.

Comme illustré ci-dessus, il faut configurer `cpu_affinity_ignore => array(0)`

?> Vous pouvez utiliser la commande `top` et entrer `1` pour voir l'utilisation de chaque cœur.

  * **Attention**

    !> Cette option doit être définie conjointement avec `open_cpu_affinity` pour être efficace.


### tcp_defer_accept

?> **Activer la caractéristique `tcp_defer_accept`.** 【Valeur par défaut : `false`】

?> Cela peut être défini comme un nombre indiquant que l'acceptation est déclenchée uniquement lorsque le côté TCP a des données à envoyer.

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

  * **Astuce**

    * **Après avoir activé la caractéristique `tcp_defer_accept`, le temps entre l'acceptation et les événements [onConnect](/server/events?id=onconnect) et [onReceive](/server/events?id=onreceive) changera. Si la valeur est fixée à `5` secondes :**

      * Après que le client se soit connecté au serveur, l'acceptation n'est pas déclenchée immédiatement.
      * Si le client envoie des données dans les `5` secondes, cela déclenche l'acceptation, l'événement [onConnect](/server/events?id=onconnect) et l'événement [onReceive](/server/events?id=onreceive) dans l'ordre.
      * Si le client n'envoie pas de données dans les `5` secondes, cela déclenche seulement l'acceptation et l'événement [onConnect](/server/events?id=onconnect).


### ssl_cert_file / ssl_key_file :id=ssl_cert_file

?> **Configurer l' encryption de tunnel SSL.**

?> La valeur doit être une chaîne de caractères représentant le chemin des certificats cert et des clés privées key.

  * **Astuce**

    * **Conversion de PEM en DER**

    ```shell
    openssl x509 -in cert.crt -outform der -out cert.der
    ```

    * **Conversion de DER en PEM**

    ```shell
    openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
    ```

  * **Attention**

    !> Pour les applications HTTPS, le navigateur doit être en mesure de信任 le certificat pour pouvoir consulter les pages web ;  
    - Pour les applications wss, la page qui lance la connexion WebSocket doit utiliser HTTPS ;  
    - Si le navigateur ne信任 pas le certificat SSL, il ne pourra pas utiliser wss ;  
    - Les fichiers doivent être au format PEM, le format DER n'est pas supporté, utilisez la commande openssl pour effectuer la conversion.

    !> Pour utiliser SSL, il est nécessaire d'ajouter l'option [--enable-openssl](/environment?id=编译选项) lors de la compilation de Swoole.

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```


### ssl_method

!> Cet paramètre a été supprimé dans la version [v4.5.4](/version/bc?id=_454). Veuillez utiliser `ssl_protocols`

?> **Configurer l'algorithme d'encryption de tunnel OpenSSL.** 【Valeur par défaut : `SWOOLE_SSLv23_METHOD`】, veuillez consulter [Les méthodes d'encryption SSL](/consts?id=ssl-加密方法) pour les types pris en charge.

?> Les algorithmes utilisés par le `Server` et le `Client` doivent être identiques, sinon la négociation SSL/TLS échouera et la connexion sera coupée.

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```
### ssl_protocols

?> **Définissez les protocoles de chiffrement pour les tunnels OpenSSL.** 【Valeur par défaut : `0`, prend en charge tous les protocoles】, pour les types pris en charge, veuillez consulter la [Confidentialité des protocoles SSL](/consts?id=ssl-protocole)

!> Disponible à partir de la version Swoole `v4.5.4`

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```


### ssl_sni_certs

?> **Définissez les certificats SNI (Server Name Identification)**

!> Disponible à partir de la version Swoole `v4.6.0`

```php
$server->set([
    'ssl_cert_file' => __DIR__ . '/server.crt',
    'ssl_key_file' => __DIR__ . '/server.key',
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'ssl_sni_certs' => [
        'cs.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_cs_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_cs_key.pem',
        ],
        'uk.php.net' => [
            'ssl_cert_file' =>  __DIR__ . '/sni_server_uk_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_uk_key.pem',
        ],
        'us.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_us_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_us_key.pem',
        ],
    ]
]);
```


### ssl_ciphers

?> **Définissez les algorithmes de chiffrement OpenSSL.** 【Valeur par défaut : `EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

  * **Avertissement**

    * Lorsque `ssl_ciphers` est configuré à une chaîne vide, `openssl` choisira automatiquement les algorithmes de chiffrement.


### ssl_verify_peer

?> **Désactivez la vérification des certificats d'identité du côté du service SSL.** 【Valeur par défaut : `false`】

?> Par défaut, désactivé, ce qui signifie que le certificat client n'est pas vérifié. Si activé, il est nécessaire de configurer également l'option `ssl_client_cert_file`.


### ssl_allow_self_signed

?> **Permettre les certificats auto-signés.** 【Valeur par défaut : `false`】


### ssl_client_cert_file

?> **Certificat racine, utilisé pour vérifier le certificat client.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set(array(
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,
    'ssl_allow_self_signed' => true,
    'ssl_client_cert_file'  => __DIR__ . '/config/ca.crt',
));
```

!> Si la vérification échoue pour un service TCP, le lien sera fermé en aval par le niveau inférieur.


### ssl_compress

?> **Activez ou désactivez la compression `SSL/TLS`.** Lors de l'utilisation de [Co\Client](/coroutine_client/client), il a un alias `ssl_disable_compression`


### ssl_verify_depth

?> **Si la chaîne de certificats est trop profonde et dépasse la valeur spécifiée par cette option, la vérification est interrompue.**


### ssl_prefer_server_ciphers

?> **Activer la protection côté serveur pour prévenir les attaques BEAST.**


### ssl_dhparam

?> **Spécifiez les paramètres `Diffie-Hellman` pour le générateur de DES.**


### ssl_ecdh_curve

?> **Spécifiez la courbe utilisée dans l'échange de clés ECDH.**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_compress'                => true,
    'ssl_verify_depth'            => 10,
    'ssl_prefer_server_ciphers'   => true,
    'ssl_dhparam'                 => '',
    'ssl_ecdh_curve'              => '',
]);
```


### user

?> **Définissez l'utilisateur auquel appartiennent les processus `Worker/TaskWorker`.** 【Valeur par défaut : l'utilisateur exécutant le script】

?> Si le serveur doit écouter sur des ports inférieurs à `1024`, il doit avoir des droits `root`. Cependant, si le programme est exécuté en tant qu'utilisateur `root` et qu'il y a une faille dans le code, l'attaquant pourrait exécuter des commandes à distance en tant qu'utilisateur `root`, ce qui représente un grand risque. Après avoir configuré l'option `user`, le processus principal peut fonctionner avec des droits `root` et les processus enfants avec des droits d'utilisateur ordinaires.

```php
$server->set(array(
  'user' => 'Apache'
));
```

  * **Note**

    !> - Seulement valide lorsque le serveur est lancé en tant qu'utilisateur `root`  
    - Après avoir configuré l'option `user/group`, si le processus de travail est réglé sur un utilisateur ordinaire, il sera impossible d'utiliser la méthode `shutdown`/[reload](/server/methods?id=reload) dans le processus de travail pour fermer ou redémarrer le service. Seul l'utilisateur `root` peut utiliser la commande `kill` dans une console `shell`.


### group

?> **Définissez le groupe auquel appartiennent les processus `Worker/TaskWorker`.** 【Valeur par défaut : le groupe de l'utilisateur exécutant le script】

?> Comme la configuration `user`, cette configuration est utilisée pour changer le groupe auquel appartient le processus, améliorant la sécurité du programme serveur.

```php
$server->set(array(
  'group' => 'www-data'
));
```

  * **Note**

    !> Seulement valide lorsque le serveur est lancé en tant qu'utilisateur `root`


### chroot

?> **Rediriger le répertoire racine du système de fichiers des processus `Worker`.**

?> Cette configuration permet de séparer les lectures et les écritures des processus sur le système de fichiers réel du système d'exploitation. Cela améliore la sécurité.

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```


### pid_file

?> **Définissez l'adresse du fichier PID.**

?> Lorsque le `Server` est lancé, il écrit automatiquement le `PID` du processus maître dans le fichier et supprime automatiquement le fichier PID lorsque le `Server` se ferme.

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

  * **Note**

    !> Lors de l'utilisation, veuillez noter que si le `Server` se termine de manière anormale, le fichier PID n'est pas supprimé et vous devez utiliser [Swoole\Process::kill($pid, 0)](/process/process?id=kill) pour vérifier si le processus existe réellement


### buffer_input_size / input_buffer_size :id=buffer_input_size

?> **Configurez la taille de la mémoire de la zone de stockage d'entrée.** 【Valeur par défaut : `2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```


### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **Configurez la taille de la mémoire de la zone de stockage de sortie.** 【Valeur par défaut : `2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, //doit être un nombre
]);
```

  * **Avertissement**

    !> À partir de la version Swoole `v4.6.7`, la valeur par défaut est la valeur maximale d'un entier sans signe `UINT_MAX`

    * Unité de byte, par défaut `2M`, comme `32 * 1024 * 1024` signifie que la taille maximale autorisée pour une seule invocation de `Server->send` est de `32M` de données
    * Lors de l'appel des commandes pour envoyer des données telles que `Server->send`, `Http\Server->end/write`, `WebSocket\Server->push`, etc., la taille maximale des données envoyées à la fois ne doit pas dépasser la configuration de `buffer_output_size`.

    !> Cette paramètre est uniquement applicable en mode [SWOOLE_PROCESS](/learn?id=swoole_process), car dans le mode PROCESS, les données du processus Worker doivent être envoyées au processus maître avant d'être envoyées au client, donc chaque processus Worker dispose d'une zone de stockage séparée avec le processus maître. [Référence](/learn?id=reactor线程)


### socket_buffer_size

?> **Configurez la taille de la mémoire de stockage des connexions clients.** 【Valeur par défaut : `2M`】

?> Contrairement à `buffer_output_size`, qui est la limitation de la taille d'envoi unique du processus Worker, `socket_buffer_size` est utilisé pour configurer la taille totale de la mémoire de stockage de communication entre les processus Worker et Master, en référence au mode [SWOOLE_PROCESS](/learn?id=swoole_process).

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, //doit être un nombre, unité de byte, comme 128 * 1024 *1024 signifie que la connexion TCP client autorisée a une capacité maximale de stockage en attente de 128M de données
]);
```
- **Cache de transmission de données**

    - Lorsque le processus maître envoie une grande quantité de données au client, elles ne peuvent pas être envoyées immédiatement. À ce moment-là, les données envoyées sont stockées dans la mémoire cache du serveur. Ce paramètre peut être ajusté pour changer la taille de la mémoire cache.
    
    - Si trop de données sont envoyées et remplissent la mémoire cache, le `Server` affichera l'erreur suivante :
    
    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```
    
    ?> L'envoi de la mémoire cache est plein, ce qui entraîne un échec de l'envoi, affectant uniquement le client actuel, les autres clients ne sont pas affectés
    Lorsque le serveur a un grand nombre de connexions `TCP`, dans le pire des cas, cela occupera `serv->max_connection * socket_buffer_size` octets de mémoire
    
    - Surtout pour les programmes de serveur qui communiqueent avec l'extérieur, la communication réseau est lente, et si vous continuez à envoyer des données de manière continue, la mémoire cache se remplira rapidement. Les données envoyées s'accumuleront toutes dans la mémoire du `Server`. Par conséquent, de tels applications devraient prendre en compte la capacité de transmission réseau du design, d'abord stocker les messages sur le disque, puis attendre que le client informe le serveur qu'il a accepté avant d'envoyer de nouvelles données.
    
    - Par exemple, pour un service de diffusion en direct vidéo, si l'utilisateur `A` a une bande passante de `100M`, il est tout à fait possible d'envoyer `10M` de données en une seconde. Si l'utilisateur `B` n'a qu'une bande passante de `1M`, il peut prendre jusqu'à `100` secondes pour recevoir `10M` de données en une seconde. À ce moment-là, les données s'accumuleront toutes dans la mémoire du serveur.
    
    - On peut traiter différemment selon le type de contenu des données. Si le contenu est abandonnable, comme pour un service de diffusion en direct vidéo, il est tout à fait acceptable d'abandonner certains frames de données si la qualité du réseau est mauvaise. Si le contenu est irréparable, comme pour les messages WeChat, on peut d'abord les stocker sur le disque du serveur, par groupes de `100` messages. Lorsque l'utilisateur a accepté ce groupe de messages, on peut alors sortir le prochain groupe de messages du disque et les envoyer au client.


### enable_unsafe_event

?> **Activer les événements `onConnect/onClose`.**【Valeur par défaut : `false`】

?> Après avoir configuré [dispatch_mode](/server/setting?id=dispatch_mode) à `1` ou `3`, le système ne peut pas garantir l'ordre des événements `onConnect/onReceive/onClose`, donc les événements `onConnect/onClose` sont désactivés par défaut; Si l'application a besoin des événements `onConnect/onClose` et peut accepter les risques de sécurité potentiels liés à l'ordre des événements, en setting `enable_unsafe_event` à `true`, les événements `onConnect/onClose` sont activés.


### discard_timeout_request

?> **Effacer les demandes de données d'une connexion fermée.**【Valeur par défaut : `true`】

?> Lorsque [dispatch_mode](/server/setting?id=dispatch_mode) est configuré à `1` ou `3`, le système ne peut pas garantir l'ordre des événements `onConnect/onReceive/onClose`, il est donc possible que certaines demandes de données atteignent le processus `Worker` après la fermeture de la connexion.

  * **Avis**

    * La configuration par défaut pour `discard_timeout_request` est `true`, ce qui signifie que si le processus `Worker` reçoit une demande de données d'une connexion fermée, il les ignorera automatiquement.
    * Si `discard_timeout_request` est réglé sur `false`, cela signifie que le processus `Worker` traitera la demande de données quelle que soit la situation de la connexion.


### enable_reuse_port

?> **Activer la réutilisation du port.**【Valeur par défaut : `false`】

?> Après avoir activé la réutilisation du port, il est possible de redémarrer un serveur qui écoute le même port.

  * **Avis**

    * `enable_reuse_port = true` active la réutilisation du port.
    * `enable_reuse_port = false` désactive la réutilisation du port.

!> Disponible uniquement dans les noyaux Linux version `3.9.0` et plus.


### enable_delay_receive

?> **Désactiver l'ajout automatique au [EventLoop](/learn?id=什么是eventloop) après avoir accepté une connexion client.**【Valeur par défaut : `false`】

?> En setting cette option à `true`, après avoir accepté une connexion client, elle ne sera pas automatiquement ajoutée au [EventLoop](/learn?id=什么是eventloop), déclenchant uniquement le回调 [onConnect](/server/events?id=onconnect). Le processus `Worker` peut appeler [$server->confirm($fd)](/server/methods?id=confirm) pour confirmer la connexion, ce qui ajoutera ensuite `$fd` au [EventLoop](/learn?id=什么是eventloop) pour commencer à recevoir et envoyer des données, ou appeler `$server->close($fd)` pour fermer la connexion.

```php
//Activer l'option enable_delay_receive
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        //Confirmez la connexion, commencez à recevoir des données
        $server->confirm($fd);
    });
});
```


### reload_async

?> **Régler l'éteignoir de redémarrage asynchrone.**【Valeur par défaut : `true`】

?> Réglez l'éteignoir de redémarrage asynchrone. Lorsque cela est réglé sur `true`, la caractéristique de redémarrage sécurisé asynchrone est activée, et le processus `Worker` attendra que les événements asynchrones soient terminés avant de quitter. Pour plus de détails, veuillez consulter [Comment redémarrer correctement le service](/question/use?id=swoole如何正确的重启服务).

?> Le principal objectif de l'activation de `reload_async` est de garantir que les coroutines ou les tâches asynchrones peuvent se terminer normalement lors du redémarrage du service.

```php
$server->set([
  'reload_async' => true
]);
```

  * **Mode de coroutine**

    * Dans la version `4.x`, lorsque [enable_coroutine](/server/setting?id=enable_coroutine) est activé, un détection supplémentaire du nombre de coroutines est effectuée en dessous, et le processus ne quitte que lorsqu'il n'y a plus de coroutines. Lorsque cela est activé, même si `reload_async => false`, il forcera l'ouverture de `reload_async`.


### max_wait_time

?> **Définir la période maximale d'attente pour le processus `Worker` après avoir reçu une notification de arrêt du service**【Valeur par défaut : `3`】

?> Il est fréquent de rencontrer des situations où le blocage ou le ralentissement du processus `worker` empêche le `worker` de se redémarrer normalement, ne répondant pas à certains scénarios de production, comme le besoin de réchauffer du code en temps réel pour les mises à jour. Par conséquent, Swoole a ajouté une option pour la période de temps de redémarrage du processus. Pour plus de détails, veuillez consulter [Comment redémarrer correctement le service](/question/use?id=swoole如何正确的重启服务).

  * **Avis**

    * **Lorsqu'un processus de gestion reçoit un signal de redémarrage/fermeture ou atteint `max_request`, le processus de gestion redémarrera ce processus `worker`. Cela se fait en plusieurs étapes :**

      * Un timer de (`max_wait_time`) secondes est ajouté en dessous. Après l'activation du timer, il vérifie si le processus existe encore, et s'il le fait, il le force à se tuer et à tirer un nouveau processus.
      * Il est nécessaire de faire des préparatifs dans la callback `onWorkerStop`, et de terminer cela dans les `max_wait_time` secondes.
      * Des signaux `SIGTERM` sont envoyés successivement au processus cible pour tuer le processus.

  * **Note**

    !> Avant `v4.4.x`, le défaut était de `30` secondes


### tcp_fastopen

?> **Activer la caractéristique de handshake rapide TCP.**【Valeur par défaut : `false`】

?> Cette caractéristique peut améliorer la vitesse de réponse des connexions TCP courtes, en envoyant des données avec la packet `SYN` lors de la troisième étape du handshake réalisé par le client.

```php
$server->set([
  'tcp_fastopen' => true
]);
```

  * **Avis**

    * Ce paramètre peut être réglé sur le port d'écoute. Pour comprendre plus profondément, veuillez consulter la [conférence SIGCOMM](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf)


### request_slowlog_file

?> **Activer le journal des demandes lentes.** À partir de la version `v4.4.8`, ce feature a été [supprimé](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366)

!> Étant donné que cette solution de journalisation lente ne fonctionne que dans des processus synchrones et bloqués, elle ne peut pas être utilisée dans un environnement de coroutines, et Swoole 4默认 utilise des coroutines, à moins de désactiver `enable_coroutine`, donc ne l'utilisez plus, utilisez plutôt l'outil de détection de blocage de [Swoole Tracker](https://business.swoole.com/tracker/index).

?> Une fois activé, le processus `Manager` établira un signal de horloge pour surveiller régulièrement tous les processus `Task` et `Worker`. Dès qu'un processus est bloqué, causant une demande dépassant la durée spécifiée, le processus de gestion enregistrera automatiquement l'appel de fonction PHP du processus bloqué.

?> La surveillance est basée sur la system call `ptrace`, certains systèmes peuvent avoir désactivé `ptrace`, rendant impossible la surveillance des demandes lentes. Veuillez vérifier si le paramètre de noyau `kernel.yama.ptrace_scope` est à `0`.

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

  * **Durée de dépassement**

```php
$server->set([
    'request_slowlog_timeout' => 2, // Définit la durée de dépassement des demandes en 2 secondes
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

!> Le fichier doit avoir les droits d'écriture, sinon l'échec de la création du fichier entraînera une erreur fatale en dessous.
### enable_coroutine

?> **Activer ou non le support des coroutines pour les serveurs d'architecture asynchrone**

?> Lorsque `enable_coroutine` est désactivé, les coroutines ne sont plus créées automatiquement dans les fonctions de rappel d'événement [ `/server/events`](/server/events) . Si vous n'avez pas besoin d'utiliser des coroutines pour cette fonction, cela peut améliorer certains performances. Pour plus d'informations, voir [Qu'est-ce que la coroutine Swoole](/coroutine).

  * **Méthodes de configuration**
    
    * Dans `php.ini`, configurez `swoole.enable_coroutine = 'Off'` (voir [documentation sur la configuration INI](/other/config.md))
    * `$server->set(['enable_coroutine' => false]);` a une priorité supérieure à celle de l'INI

  * **Domaine d'influence de l'option `enable_coroutine`**

      * onWorkerStart
      * onConnect
      * onOpen
      * onReceive
      * [setHandler](/redis_server?id=sethandler)
      * onPacket
      * onRequest
      * onMessage
      * onPipeMessage
      * onFinish
      * onClose
      * tick/after timers

!> Lorsque `enable_coroutine` est activé, des coroutines sont automatiquement créées dans les fonctions de rappel mentionnées ci-dessus

* Lorsque `enable_coroutine` est set à `true`, des coroutines sont automatiquement créées dans le rappel de `/http_server?id=on` en bas du niveau, et les développeurs n'ont pas besoin d'utiliser la fonction `go` [créer une coroutine](/coroutine/coroutine?id=create) par eux-mêmes
* Lorsque `enable_coroutine` est set à `false`, aucune coroutine n'est créée automatiquement en bas du niveau. Si les développeurs souhaitent utiliser des coroutines, ils doivent utiliser `go` pour créer des coroutines par eux-mêmes. Si vous n'avez pas besoin des fonctionnalités de coroutines, la manière de les gérer est entièrement compatible avec Swoole 1.x
* Notez que cette activation signifie simplement que Swoole traitera les demandes en utilisant des coroutines. Si un événement contient des fonctions bloquantes, vous devrez activer à l'avance la [co-programmation en une touche](/runtime), en activant la co-programmation pour des fonctions bloquantes telles que `sleep`, `mysqlnd`, etc., ou pour les extensions

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    //Désactive la coroutine intégrée
    'enable_coroutine' => false,
]);

$server->on("request", function ($request, $response) {
    if ($request->server['request_uri'] == '/coro') {
        go(function () use ($response) {
            co::sleep(0.2);
            $response->header("Content-Type", "text/plain");
            $response->end("Bonjour le monde\n");
        });
    } else {
        $response->header("Content-Type", "text/plain");
        $response->end("Bonjour le monde\n");
    }
});

$server->start();
```


### send_yield

?> **Lors de l'envoi de données, si la mémoire du tampon est insuffisante, le processus s'arrête immédiatement dans la coroutine actuelle [yield](/coroutine?id=协程调度), attendant que la transmission des données soit terminée et que le tampon soit vidé, puis la coroutine est automatiquement [resume](/coroutine?id=协程调度) pour continuer à envoyer les données.**【Valeur par défaut : disponible lorsque le mode de dispatch est 2/4 et activé par défaut】

* Lorsque `Server/Client->send` retourne `false` et que l'erreur est `SW_ERROR_OUTPUT_BUFFER_OVERFLOW`, il ne retourne pas `false` au niveau PHP, mais il [yield](/coroutine?id=协程调度) et suspend la coroutine actuelle
* `Server/Client` écoute l'événement de vidage du tampon. Après cet événement, les données du tampon ont été envoyées et la coroutine correspondante est [resume](/coroutine?id=协程调度)
* Après la reprise de la coroutine, continuez à appeler `Server/Client->send` pour écrire des données dans le tampon. Comme le tampon est maintenant vide, l'envoi est certain d'être réussi

Avant l'amélioration

```php
for ($i = 0; $i < 100; $i++) {
    //Si le tampon est plein, il retourne directement false et génère une erreur de tampon de sortie
    $server->send($fd, $data_2m);
}
```

Après l'amélioration

```php
for ($i = 0; $i < 100; $i++) {
    //Si le tampon est plein, il suspend la coroutine actuelle jusqu'à ce que les données soient envoyées, puis continue à exécuter le code après la reprise
    $server->send($fd, $data_2m);
}
```

!> Cette caractéristique change le comportement par défaut du niveau inférieur et peut être désactivée manuellement

```php
$server->set([
    'send_yield' => false,
]);
```

  * __Domaine d'influence__

    * [Swoole\Server::send](/server/methods?id=send)
    * [Swoole\Http\Response::write](/http_server?id=write)
    * [Swoole\WebSocket\Server::push](/websocket_server?id=push)
    * [Swoole\Coroutine\Client::send](/coroutine_client/client?id=send)
    * [Swoole\Coroutine\Http\Client::push](/coroutine_client/http_client?id=push)


### send_timeout

Configurez un timeout d'envoi. Utilisez-le en combinaison avec `send_yield`. Lorsque les données ne peuvent pas être envoyées dans le tampon dans le délai spécifié, le niveau inférieur retourne `false` et définit l'erreur comme `ETIMEDOUT`. Vous pouvez utiliser la méthode [getLastError()](/server/methods?id=getlasterror) pour obtenir l'erreur.

> Type : flottant, unité : seconde, granularité minimale : milliseconde

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1,5 seconde
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false && $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "Envoi timed out\n";
    }
}
```


### hook_flags

?> **Configurez la portée des fonctions pour le hook de 'co-programmation en une touche'.**【Valeur par défaut : pas de hook】

!> Disponible pour les versions Swoole `v4.5+` ou [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x), voir [co-programmation en une touche](/runtime) pour plus d'informations

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```


### buffer_high_watermark

?> **Configurez la limite supérieure du tampon en octets.**

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```


### buffer_low_watermark

?> **Configurez la limite inférieure du tampon en octets.**

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```


### tcp_user_timeout

?> L'option `tcp_user_timeout` est une option de socket au niveau TCP, qui représente la durée maximale après laquelle un paquet TCP est envoyé sans recevoir de ACK de confirmation. Pour plus d'informations, veuillez consulter la documentation man

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10 secondes
]);
```

!> Disponible pour les versions Swoole `v4.5.3-alpha` et versions ultérieures


### stats_file

?> **Spécifiez le chemin du fichier où les contenus de la méthode [stats()](/server/methods?id=stats) seront écrits. Une fois cette option définie, un timer est automatiquement mis en place lors du démarrage du worker pour écrire les contenus de [stats()](/server/methods?id=stats) dans le fichier spécifié.**

```php
$server->set([
    'stats_file' => __DIR__ . '/stats.log',
]);
```

!> Disponible pour les versions Swoole `v4.5.5` et versions ultérieures


### event_object

?> **Après avoir configuré cette option, les rappels d'événement utiliseront le style objet [](/server/events?id=回调对象).**【Valeur par défaut : `false`】

```php
$server->set([
    'event_object' => true,
]);
```

!> Disponible pour les versions Swoole `v4.6.0` et versions ultérieures


### start_session_id

?> **Configurez l'ID de session de départ**

```php
$server->set([
    'start_session_id' => 10,
]);
```

!> Disponible pour les versions Swoole `v4.6.0` et versions ultérieures


### single_thread

?> **Activez cette option pour utiliser un seul thread.** Une fois activée, le thread Reactor fusionnera avec le thread Master dans le processus Master, et le thread Master traitera la logique. Lorsque PHP est en mode ZTS et que vous utilisez la mode `SWOOLE_PROCESS`, il est essentiel de set cette valeur à `true`.

```php
$server->set([
    'single_thread' => true,
]);
```

!> Disponible pour les versions Swoole `v4.2.13` et versions ultérieures
### max_queued_bytes

?> **Définit la longueur maximale de la file d'attente de la mémoire tampon de réception.** Si elle est dépassée, la réception cesse.

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Disponible pour les versions Swoole >= `v4.5.0`


### admin_server

?> **Configurez l'administration du serveur pour utiliser le [tableau de bord Swoole](http://dashboard.swoole.com/) pour afficher des informations sur les services, etc.**

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Disponible pour les versions Swoole >= `v4.8.0`


### bootstrap

?> **Fichier d'entrée pour les modes multithread, par défaut est le nom du script actuellement exécuté.**

!> Disponible pour les versions Swoole >= `v6.0`, PHP en mode ZTS, Swoole compilé avec `--enable-swoole-thread`

```php
$server->set([
    'bootstrap' => __FILE__,
]);
```

### init_arguments

?> **Configurez les données partagées pour les threads multithread, cette configuration nécessite une fonction de rappel qui sera exécutée automatiquement au démarrage du serveur.**

!> Swoole intègre de nombreux conteneurs thread-safe, [Map concurrentiel](/thread/map), [List concurrentiel](/thread/arraylist), [Faire la queue concurrentiel](/thread/queue), ne retournez pas de variables insécurisées dans vos fonctions.

!> Disponible pour les versions Swoole >= `v6.0`, PHP en mode ZTS, Swoole compilé avec `--enable-swoole-thread`

```php
$server->set([
    'init_arguments' => function() { return new Swoole\Thread\Map(); },
]);

$server->on('request', function($request, $response) {
    $map = Swoole\Thread::getArguments();
});
```
