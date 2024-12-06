# Swoole\Process\Pool

Le pool de processus est une implémentation de gestion de processus basée sur le Manager de [Swoole\Server](/server/init). Il permet de gérer plusieurs processus de travail. La fonction centrale de ce module est la gestion des processus. Contrairement à la mise en œuvre de multiples processus avec `Process`, `Process\Pool` est plus simple, avec un niveau d'encapsulation plus élevé, permettant aux développeurs d'implémenter des fonctionnalités de gestion de processus sans avoir à écrire trop de code. En combinaison avec [Co\Server](/coroutine/server?id=exemple complet), il est possible de créer un serveur côté client qui utilise pleinement les coroutines et peut tirer parti des multiple cores de l'CPU.

## Communication entre processus

`Swoole\Process\Pool` offre trois modes de communication entre processus :

### Queue de messages
Lorsque le deuxième argument de `Swoole\Process\Pool->__construct` est set à `SWOOLE_IPC_MSGQUEUE`, cela indique que la communication entre processus se fera via une queue de messages. Les informations peuvent être envoyées via l'extension `php sysvmsg`, et la taille maximale du message ne doit pas dépasser `65536`.

* **Note**

  * Si vous utilisez l'extension `sysvmsg` pour envoyer des informations, vous devez fournir un `msgqueue_key` dans le constructeur.
  * Le sous-système Swoole ne prend pas en charge le deuxième argument `mtype` de la fonction `msg_send` de l'extension `sysvmsg`, veuillez fournir une valeur non nulle.

### Communication par socket
Lorsque le deuxième argument de `Swoole\Process\Pool->__construct` est set à `SWOOLE_IPC_SOCKET`, cela indique que la communication entre processus se fera via des sockets. Si votre client et votre serveur ne sont pas sur le même ordinateur, vous pouvez utiliser cette méthode pour communiquer.

En utilisant la méthode `Swoole\Process\Pool->listen()` pour écouter sur un port, en utilisant l'événement `Message` pour recevoir les données envoyées par les clients, et en utilisant la méthode `Swoole\Process\Pool->write()` pour envoyer une réponse aux clients.

Swoole exige que les clients utilisent cette méthode pour envoyer des données, ils doivent ajouter 4 octets, la valeur de la taille en byte order réseau, avant les données réelles.
```php
$msg = 'Bonjour Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```

### UnixSocket
Lorsque le deuxième argument de `Swoole\Process\Pool->__construct` est set à `SWOOLE_IPC_UNIXSOCK`, cela indique que la communication entre processus se fera via un [UnixSocket](/learn?id=qu'est-ce que l'IPC).

Cette méthode est assez simple, il suffit d'utiliser la méthode `Swoole\Process\Pool->sendMessage()` et l'événement `Message` pour compléter la communication entre processus.

Ou bien, après avoir activé le mode coroutine, vous pouvez également obtenir un objet `Swoole\Process` en utilisant la méthode `Swoole\Process\Pool->getProcess()` et obtenir un objet `Swoole\Coroutine\Socket` en utilisant la méthode `Swoole\Process\Process::exportsocket()`. Vous pouvez utiliser cet objet pour réaliser la communication entre processus. Cependant, vous ne pouvez pas configurer l'événement `Message` à ce moment-là.

!> Pour les paramètres et la configuration de l'environnement, veuillez consulter la [construction](/process/process_pool?id=__construct) et les [paramètres de configuration](/process/process_pool?id=set).

## Constantes


Constante | Description
---|---
SWOOLE_IPC_MSGQUEUE | Communication via [queue de messages](/learn?id=qu'est-ce que l'IPC)
SWOOLE_IPC_SOCKET | Communication via socket
SWOOLE_IPC_UNIXSOCK | Communication via [UnixSocket](/learn?id=qu'est-ce que l'IPC) (v4.4+)


## Support pour les coroutines

À partir de la version `v4.4.0`, le support pour les coroutines a été ajouté. Veuillez consulter [Swoole\Process\Pool::__construct](/process/process_pool?id=__construct).


## Exemple d'utilisation

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** Actuellement un processus Worker */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo "[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n";
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo "[Worker #{$workerId}] WorkerStop\n";
});
$pool->start();
```


## Méthodes


### __construct()

Constructeur.

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **Paramètres** 

  * **`int $worker_num`**
    * **Fonction** : Spécifier le nombre de processus de travail
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`int $ipc_type`**
    * **Fonction** : Mode de communication entre processus 【Par défaut, aucun mode de communication entre processus n'est utilisé】
    * **Valeur par défaut** : `SWOOLE_IPC_NONE`
    * **Autres valeurs** : `SWOOLE_IPC_MSGQUEUE`, `SWOOLE_IPC_SOCKET`, `SWOOLE_IPC_UNIXSOCK`

    !> - Lorsqu'il est set à `SWOOLE_IPC_NONE`, il est impératif de configurer la callback `onWorkerStart` et de mettre en œuvre un loop logique dans `onWorkerStart`. Lorsque la fonction `onWorkerStart` se termine, le processus de travail quitte immédiatement et est ensuite relancé par le processus Manager ;  
    - Lorsqu'il est set à `SWOOLE_IPC_MSGQUEUE`, cela indique que la communication entre processus se fera via une queue de messages système, et vous pouvez configurer `$msgqueue_key` pour spécifier la `KEY` de la queue de messages. Si la `KEY` de la queue de messages n'est pas définie, une queue privée sera demandée ;  
    - Lorsqu'il est set à `SWOOLE_IPC_SOCKET`, cela indique que la communication se fera via des sockets. Il est nécessaire d'utiliser la méthode `listen()` pour spécifier l'adresse et le port à écouter ;  
    - Lorsqu'il est set à `SWOOLE_IPC_UNIXSOCK`, cela indique que la communication se fera via un [UnixSocket](/learn?id=qu'est-ce que l'IPC). Utilisé en mode coroutine, **c'est fortement recommandé d'utiliser cette méthode pour la communication entre processus**, voir la documentation ci-dessous pour les détails ;  
    - Lorsqu'il est configuré avec une valeur autre que `SWOOLE_IPC_NONE`, il est impératif de configurer la callback `onMessage`, et la callback `onWorkerStart` devient optionnelle.

  * **`int $msgqueue_key`**
    * **Fonction** : La `KEY` de la queue de messages
    * **Valeur par défaut** : `0`
    * **Autres valeurs** : Aucun

  * **`bool $enable_coroutine`**
    * **Fonction** : Activer le support pour les coroutines 【Lorsqu'il est activé, il est impossible de configurer la callback `onMessage`】
    * **Valeur par défaut** : `false`
    * **Autres valeurs** : `true`

* **Mode coroutine**
    
À partir de la version `v4.4.0`, le module `Process\Pool` de Swoole a ajouté un soutien pour les coroutines, qui peut être activé en configurant le quatrième paramètre en `true`. Une fois les coroutines activées, Swoole créera automatiquement un coroutine et un [conteneur de coroutines](/coroutine/scheduler) lors du démarrage du processus Worker. Dans les fonctions de rappel, vous pouvez utiliser directement les API liées aux coroutines, par exemple :

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

Après avoir activé les coroutines, Swoole interdit la configuration de l'événement de callback `onMessage`. Si vous avez besoin de communiquer entre les processus, vous devez configurer le deuxième argument en `SWOOLE_IPC_UNIXSOCK` pour indiquer que la communication se fera via un [UnixSocket](/learn?id=qu'est-ce que l'IPC), puis utiliser la méthode `$pool->getProcess()->exportSocket()` pour obtenir un objet `Swoole\Coroutine\Socket` et réaliser ainsi la communication entre les processus Worker. Par exemple :

 ```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> Pour plus d'informations sur l'utilisation, veuillez consulter les chapitres [Swoole\Coroutine\Socket](/coroutine_client/socket) et [Swoole\Process](/process/process?id=exportsocket).

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```
### set()

Configure parameters.

```php
Swoole\Process\Pool->set(array $settings): void
```

**Optional Parameters** | **Type** | **Function** | **Default Value**
---|---|---|---
enable_coroutine | bool | Control whether to enable coroutines | false
enable_message_bus | bool | Enable the message bus; if true, when sending large data, the underlying will split it into smaller chunks before sending them. | false
max_package_size | int | Limits the maximum amount of data a process can receive | 2 * 1024 * 1024

* **Note**

  * If `enable_message_bus` is true, `max_package_size` has no effect because the underlying will split the data into smaller chunks for sending and receiving.
  * In `SWOOLE_IPC_MSGQUEUE` mode, `max_package_size` also has no effect; the underlying can receive up to 65536 bytes at most at once.
  * In `SWOOLE_IPC_SOCKET` mode, if `enable_message_bus` is false and the amount of data received exceeds `max_package_size`, the underlying will terminate the connection.
  * In `SWOOLE_IPC_UNIXSOCK` mode, if `enable_message_bus` is false and the data exceeds `max_package_size`, the excess data will be truncated.
  * If coroutines are enabled, `enable_message_bus` being true also makes `max_package_size` ineffective. The underlying will handle data splitting (sending) and merging (receiving), otherwise, it will limit the amount of data received based on `max_package_size`.

!> Available from Swoole version >= v4.4.4

### on()

Set a callback function for the process pool.

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **Parameters**

  * **`string $event`**
    * **Function**: Specify the event
    * **Default Value**: None
    * **Other Values**: None

  * **`callable $function`**
    * **Function**: Callback function
    * **Default Value**: None
    * **Other Values**: None

* **Events**

  * **onWorkerStart** Subprocess startup

    ```php
    /**
    * @param \Swoole\Process\Pool $pool Pool object
    * @param int $workerId WorkerId of the current working process; the underlying will number the subprocesses
    */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
        echo "Worker#{$workerId} is started\n";
    });
    ```

  * **onWorkerStop** Subprocess termination

    ```php
    /**
    * @param \Swoole\Process\Pool $pool Pool object
    * @param int $workerId WorkerId of the current working process; the underlying will number the subprocesses
    */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
        echo "Worker#{$workerId} stop\n";
    });
    ```

  * **onMessage** Message reception

    !> Messages are received from an external source. A connection can only deliver a message once; this is similar to the short-connection mechanism of `PHP-FPM`.

    ```php
    /**
      * @param \Swoole\Process\Pool $pool Pool object
      * @param string $data Content of the message
     */
    $pool = new Swoole\Process\Pool(2);
    $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
        var_dump($data);
    });
    ```

    !> Event names are case-insensitive; `WorkerStart`, `workerStart`, or `workerstart` are all the same.

### listen()

Listen for `SOCKET` connections, must be used when `$ipc_mode = SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **Parameters**

  * **`string $host`**
    * **Function**: Address to listen on (supports both `TCP` and [unixSocket](/learn?id=什么是IPC)). `127.0.0.1` indicates a TCP address and requires specifying `$port`. `unix:/tmp/php.sock` listens on a [unixSocket](/learn?id=什么是IPC) address.
    * **Default Value**: None
    * **Other Values**: None

  * **`int $port`**
    * **Function**: Port to listen on (required for TCP mode)
    * **Default Value**: `0`
    * **Other Values**: None

  * **`int $backlog`**
    * **Function**: Length of the listening queue
    * **Default Value**: `2048`
    * **Other Values**: None

* **Return Value**

  * Returns `true` if listening is successful
  * Returns `false` if listening fails; you can call `swoole_errno` to get the error code. If listening fails, calling `start` will immediately return `false`.

* **Communication Protocol**

    When sending data to the listening port, the client must first add a 4-byte length value in network byte order before the request. The protocol format is:

```php
// $msg Data to be sent
$packet = pack('N', strlen($msg)) . $msg;
```

* **Usage Example**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```

### write()

Write data to the peer, can only be used when `$ipc_mode` is `SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->write(string $data): bool
```

!> This method operates on memory and has no `IO` consumption; the data sending operation is synchronous and blocking `IO`.

* **Parameters**

  * **`string $data`**
    * **Function**: Data to be written [you can call `write` multiple times; the underlying will write all the data to the `socket` after the `onMessage` function exits and then close the connection]
    * **Default Value**: None
    * **Other Values**: None

* **Usage Example**

  * **Server-side**

    ```php
    $pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
    
    $pool->on("Message", function ($pool, $message) {
        echo "Message: {$message}\n";
        $pool->write("hello ");
        $pool->write("world ");
        $pool->write("\n");
    });
    
    $pool->listen('127.0.0.1', 8089);
    $pool->start();
    ```

  * **Client-side**

    ```php
    $fp = stream_socket_client("tcp://127.0.0.1:8089", $errno, $errstr) or die("error: $errstr\n");
    $msg = json_encode(['data' => 'hello', 'uid' => 1991]);
    fwrite($fp, pack('N', strlen($msg)) . $msg);
    sleep(1);
    // Will display hello world\n
    $data = fread($fp, 8192);
    var_dump(substr($data, 4, unpack('N', substr($data, 0, 4))[1]));
    fclose($fp);
    ```

### sendMessage()

Send data to a target process, can only be used when `$ipc_mode` is `SWOOLE_IPC_UNIXSOCK`.

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **Parameters**

  * **`string $data`**
    * **Function**: Data to be sent
    * **Default Value**: None
    * **Other Values**: None

  * **`int $dst_worker_id`**
    * **Function**: Target process ID
    * **Default Value**: `0`
    * **Other Values**: None

* **Return Value**

  * Returns `true` if sending is successful
  * Returns `false` if sending fails

* **Note**

  * If the sent data exceeds `max_package_size` and `enable_message_bus` is `false`, the target process will truncate the data when receiving it.

```php
<?php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => false, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(2048)


$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => true, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(6000)
```
### start()

Démarrer le processus de travail.

```php
Swoole\Process\Pool->start(): bool
```

!> Le démarrage est successful, le processus actuel entre dans un état `wait`, gérant les processus de travail ;  
Le démarrage échoue, retourne `false`, l'erreur peut être obtenue avec `swoole_errno`.

* **Exemple d'utilisation**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} est démarré\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} est arrêté\n";
});

$pool->start();
```

* **Gestion des processus**

  * Lorsque certains processus de travail rencontrent une erreur fatale ou se retirent volontairement, le gestionnaire les récupérera pour éviter la création de processus zombis
  * Après la sortie d'un processus de travail, le gestionnaire créera automatiquement un nouveau processus de travail
  * Lorsque le processus principal reçoit un signal `SIGTERM`, il cessera de `fork` de nouveaux processus et `kill`a tous les processus de travail en cours
  * Lorsque le processus principal reçoit un signal `SIGUSR1`, il `kill`a successivement tous les processus de travail en cours et redémarre de nouveaux processus de travail

* **Gestion des signaux**

  Seul le processus principal (gestionnaire) a été configuré pour gérer les signaux, les processus de travail `Worker` n'ont pas été configurés pour écouter les signaux, il est donc nécessaire que les développeurs implémentent eux-mêmes l'écoute des signaux.

  - Pour les processus de travail en mode asynchrone, veuillez utiliser [Swoole\Process::signal](/process/process?id=signal) pour écouter les signaux
  - Pour les processus de travail en mode synchrone, veuillez utiliser `pcntl_signal` et `pcntl_signal_dispatch` pour écouter les signaux

  Dans les processus de travail, il est nécessaire d'écouter le signal `SIGTERM`, lorsque le processus principal souhaite arrêter ce processus, il enverra un signal `SIGTERM` à ce processus. Si le processus de travail n'écoute pas le signal `SIGTERM`, le système en bas arrêtera brusquement le processus actuel, entraînant la perte de certaines logiques.

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} est démarré\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```


### stop()

Retirer le socket actuel du processus de la boucle d'événements, cette fonction n'a un effet que si les coroutines sont démarrées

```php
Swoole\Process\Pool->stop(): bool
```


### shutdown()

Arrêter les processus de travail.

```php
Swoole\Process\Pool->shutdown(): bool
```


### getProcess()

Obtenir l'objet processus de travail actuel. Retourne un objet [Swoole\Process](/process/process).

!> Disponible à partir de la version Swoole `v4.2.0`

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **Paramètres** 

  * **`int $worker_id`**
    * **Fonction** : Spécifier l'obtention de `worker` 【Optionnel, par défaut le `worker` actuel】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

!> Doit être appelé après `start`, dans le `onWorkerStart` ou une autre fonction de rappel du processus de travail ;  
L'objet `Process` retourné est un modèle singleton, et de multiples appels à `getProcess()` dans le processus de travail retourneront le même objet.

* **Exemple d'utilisation**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```

### detach()

Détacher le processus Worker actuel de la gestion de la pool de processus, le système en bas créera immédiatement un nouveau processus, l'ancien processus ne traitera plus de données, la gestion de la vie de l'application doit être effectuée par le code de l'application lui-même.

!> Disponible à partir de la version Swoole `v4.7.0`

```php
Swoole\Process\Pool->detach(): bool
```
