# Swoole\Process

Module de gestion des processus fourni par Swoole, destiné à remplacer la `pcntl` PHP  

> Ce module est assez bas niveau, c'est une encapsulation de la gestion des processus par l'opération système, et l'utilisateur doit avoir des expériences en programmation multiprocessus sous Linux.

La `pcntl` fournie par PHP a de nombreuses insuffisances, telles que :

* Ne pas fournir de fonction de communication entre processus
* Ne pas supporter le redirection des entrées et sorties standards
* Seulement fournir des interfaces primitives comme `fork`, ce qui est facile à utiliser mal

Le `Process` offre des fonctionnalités plus puissantes que la `pcntl` et une API plus facile à utiliser, rendant la programmation multiprocessus en PHP beaucoup plus aisée.

Le `Process` offre les caractéristiques suivantes :

* Permet de réaliser facilement la communication entre processus
* Supporte le redirection des entrées et sorties standards, dans les sous-processus, `echo` ne affiche pas l'écran, mais écrit dans le tuyau, et la lecture de l'entrée clavier peut être redirectionnée pour lire les données à partir du tuyau
* Offre une interface [exec](/process/process?id=exec), le processus créé peut exécuter d'autres programmes, et il est facile de communiquer avec le processus parent PHP original
* Ne pas utiliser le module `Process` dans un environnement de coroutines, peut utiliser `runtime hook` + `proc_open` pour réaliser, voir [gestion de processus coroutines](/coroutine/proc_open)


### Exemple d'utilisation

  * Créer 3 sous-processus, le processus principal utilise `wait` pour récupérer les processus
  * Lorsque le processus principal se termine de manière exceptionnelle, les sous-processus continueront d'exécuter et termineront toutes les tâches avant de se terminer

```php
use Swoole\Process;

for ($n = 1; $n <= 3; $n++) {
    $process = new Process(function () use ($n) {
        echo 'Enfant #' . getmypid() . " commence et dort {$n}s" . PHP_EOL;
        sleep($n);
        echo 'Enfant #' . getmypid() . ' se termine' . PHP_EOL;
    });
    $process->start();
}
for ($n = 3; $n--;) {
    $status = Process::wait(true);
    echo "Récyclé #{$status['pid']}, code={$status['code']}, signal={$status['signal']}" . PHP_EOL;
}
echo 'Parent #' . getmypid() . ' se termine' . PHP_EOL;
```


## Propriétés


### pipe

Déscription du descripteur de fichier de [unixSocket](/learn?id=什么是IPC).

```php
public int $pipe;
```


### msgQueueId

`id` de la file de message.

```php
public int $msgQueueId;
```


### msgQueueKey

`key` de la file de message.

```php
public string $msgQueueKey;
```


### pid

`pid` du processus actuel.

```php
public int $pid;
```


### id

`id` du processus actuel.

```php
public int $id;
```


## Constantes

Paramètre | Effet
---|---
Swoole\Process::IPC_NOWAIT | Quand la file de message est vide, revenir immédiatement
Swoole\Process::PIPE_READ | Fermer le socket de lecture
Swoole\Process::PIPE_WRITE | Fermer le socket d'écriture


## Méthodes


### __construct()

Constructeur.

```php
Swoole\Process->__construct(callable $function, bool $redirect_stdin_stdout = false, int $pipe_type = SOCK_DGRAM, bool $enable_coroutine = false)
```

* **Paramètres** 

  * **`callable $function`**
    * **Fonction** : La fonction à exécuter après la création du sous-processus réussie【Le framework enregistre automatiquement la fonction sur l'attribut `callback` de l'objet, notez que cet attribut est `private`.】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`bool $redirect_stdin_stdout`**
    * **Fonction** : Rediriger les entrées et sorties standards du sous-processus.【Si cette option est activée, le contenu imprimé à l'intérieur du sous-processus ne sera pas affiché à l'écran, mais écrit dans le tuyau du processus principal. La lecture de l'entrée clavier deviendra la lecture de données à partir du tuyau. Par défaut, la lecture est bloquée. Pour plus d'informations, voir la méthode [exec()](/process/process?id=exec)】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $pipe_type`**
    * **Fonction** : Type de [unixSocket](/learn?id=什么是IPC)【Si `$redirect_stdin_stdout` est activé, cet argument sera ignoré et forcé à `SOCK_STREAM`. Si il n'y a pas de communication entre processus dans le sous-processus, il peut être défini comme `0`.】
    * **Valeur par défaut** : `SOCK_DGRAM`
    * **Autres valeurs** : `0`, `SOCK_STREAM`

  * **`bool $enable_coroutine`**
    * **Fonction** : Activer les coroutines dans la `callback function`, une fois activées, il est possible d'utiliser directement les API de coroutines dans la fonction du sous-processus
    * **Valeur par défaut** : `false`
    * **Autres valeurs** : `true`
    * **Impact de la version** : Swoole version >= v4.3.0

* **Type de [unixSocket](/learn?id=什么是IPC)**


Type de unixSocket | Description
---|---
0 | Ne pas créer
1 | Créer un unixSocket de type [SOCK_STREAM](/learn?id=什么是IPC)
2 | Créer un unixSocket de type [SOCK_DGRAM](/learn?id=什么是IPC)



### useQueue()

Utiliser la file de message pour la communication entre processus.

```php
Swoole\Process->useQueue(int $key = 0, int $mode = SWOOLE_MSGQUEUE_BALANCE, int $capacity = -1): bool
```

* **Paramètres** 

  * **`int $key`**
    * **Fonction** : La clé de la file de message, si une valeur inférieure ou égale à 0 est passée, le framework utilisera la fonction `ftok`, avec le nom du fichier actuel comme argument, pour générer la clé correspondante.
    * **Valeur par défaut** : `0`
    * **Autres valeurs** : None

  * **`int $mode`**
    * **Fonction** : Mode de communication entre processus,
    * **Valeur par défaut** : `SWOOLE_MSGQUEUE_BALANCE`, la méthode `Swoole\Process::pop()` retournera le premier message de la file, la méthode `Swoole\Process::push()` ne pas ajouter un type spécifique au message.
    * **Autres valeurs** : `SWOOLE_MSGQUEUE_ORIENT`, la méthode `Swoole\Process::pop()` retournera le premier message du type `pid + 1` de la file, la méthode `Swoole\Process::push()` ajoutera le type `pid + 1` au message.

  * **`int $capacity`**
    * **Fonction** : La taille maximale autorisée pour les messages stockés dans la file de message.
    * **Valeur par défaut** : `-1`
    * **Autres valeurs** : None

* **Note**

  * Quand la file de message est vide, la méthode `Swoole\Porcess->pop()` restera bloquée, ou si la file de message n'a pas d'espace pour accueillir de nouvelles données, la méthode `Swoole\Porcess->push()` restera également bloquée. Si vous ne voulez pas bloquer, la valeur de `$mode` doit être `SWOOLE_MSGQUEUE_BALANCE|Swoole\Process::IPC_NOWAIT` ou `SWOOLE_MSGQUEUE_ORIENT|Swoole\Process::IPC_NOWAIT`.


### statQueue()

Obtenir l'état de la file de message

```php
Swoole\Process->statQueue(): array|false
```

* **Valeur de retour** 

  * Retourne un tableau pour succès, le tableau contient deux paires de clés-valeurs, `queue_num` représente le nombre total de messages dans la file, `queue_bytes` représente la taille totale des messages dans la file.
  * Retourne `false` pour échouer.


### freeQueue()

Destruire la file de message.

```php
Swoole\Process->freeQueue(): bool
```

* **Valeur de retour** 

  * Retourne `true` pour succès.
  * Retourne `false` pour échouer.


### pop()

Obtenir des données de la file de message.

```php
Swoole\Process->pop(int $size = 65536): string|false
```

* **Paramètres** 

  * **`int $size`**
    * **Fonction** : La taille des données obtenues.
    * **Valeur par défaut** : `65536`
    * **Autres valeurs** : None


* **Valeur de retour** 

  * Retourne une `string` pour succès.
  * Retourne `false` pour échouer.

* **Note**

  * Quand le type de file de message est `SW_MSGQUEUE_BALANCE`, retourne le premier message de la file.
  * Quand le type de file de message est `SW_MSGQUEUE_ORIENT`, retourne le premier message du type `pid + 1` de la file.
### push()

Envoie des données dans la file d'attente de messages.

```php
Swoole\Process->push(string $data): bool
```

* **Paramètres**

  * **`string $data`**
    * **Fonction** : La donnée à envoyer.
    * **Valeur par défaut** : ``
    * **Autres valeurs** : `aucune`


* **Valeurs de retour**

  * Retourne `true` en cas de succès.
  * Retourne `false` en cas d'échec.

* **Remarque**

  * Lorsque le type de file d'attente de messages est `SW_MSGQUEUE_BALANCE`, les données sont directement insérées dans la file d'attente.
  * Lorsque le type de file d'attente de messages est `SW_MSGQUEUE_ORIENT`, les données sont dotées d'un type, qui est le `pid` actuel du processus + 1.


### setTimeout()

Établit une limite de temps pour la lecture et l'écriture de la file d'attente de messages.

```php
Swoole\Process->setTimeout(float $seconds): bool
```

* **Paramètres**

  * **`float $seconds`**
    * **Fonction** : Le temps de dépassement.
    * **Valeur par défaut** : `aucune`
    * **Autres valeurs** : `aucune`


* **Valeurs de retour**

  * Retourne `true` en cas de succès.
  * Retourne `false` en cas d'échec.


### setBlocking()

Établit si le socket de la file d'attente de messages est bloqué.

```php
Swoole\Process->setBlocking(bool $$blocking): void
```

* **Paramètres**

  * **`bool $blocking`**
    * **Fonction** : Indique si le socket est bloqué (`true`) ou non (`false`).
    * **Valeur par défaut** : `aucune`
    * **Autres valeurs** : `aucune`

* **Remarque**

  * Les sockets de processus nouvellement créés sont par défaut bloqués, donc lors de la communication avec des sockets Unix domain, envoyer ou lire des messages peut bloquer le processus.


### write()

Écrit des messages entre processus père et fils (sockets Unix domain).

```php
Swoole\Process->write(string $data): false|int
```

* **Paramètres**

  * **`string $data`**
    * **Fonction** : Les données à écrire.
    * **Valeur par défaut** : `aucune`
    * **Autres valeurs** : `aucune`


* **Valeurs de retour**

  * Retourne `int` en cas de succès, représentant le nombre de字节 écrits avec succès.
  * Retourne `false` en cas d'échec.


### read()

Lit des messages entre processus père et fils (sockets Unix domain).

```php
Swoole\Process->read(int $size = 8192): false|string
```

* **Paramètres**

  * **`int $size`**
    * **Fonction** : La taille des données à lire.
    * **Valeur par défaut** : `8192`
    * **Autres valeurs** : `aucune`


* **Valeurs de retour**

  * Retourne `string` en cas de succès.
  * Retourne `false` en cas d'échec.


### set()

Établit des paramètres.

```php
Swoole\Process->set(array $settings): void
```

Utilisez `enable_coroutine` pour contrôler l'activation des coroutines, ce qui est cohérent avec le quatrième paramètre du constructeur.

```php
Swoole\Process->set(['enable_coroutine' => true]);
```

!> Disponible à partir de la version Swoole v4.4.4.


### start()

Exécute la system call `fork` pour créer un processus fils. La création d'un processus sur un système Linux peut nécessiter plusieurs centaines de microsecondes.

```php
Swoole\Process->start(): int|false
```

* **Valeurs de retour**

  * Retourne le `PID` du processus fils en cas de succès.
  * Retourne `false` en cas d'échec. Utilisez [swoole_errno](/functions?id=swoole_errno) et [swoole_strerror](/functions?id=swoole_strerror) pour obtenir l'code d'erreur et l'information d'erreur.

* **Remarque**

  * Le processus fils hérite des ressources memory et des handles de fichiers du processus père.
  * Lorsque le processus fils est lancé, il élimine les EventLoop, Signal et Timer hérités du processus père.
  
  !> Après l'exécution, le processus fils maintiendra la mémoire et les ressources du processus père. Par exemple, si un lien Redis est créé dans le processus père, il sera conservé dans le processus fils, et toutes les opérations seront effectuées sur le même lien. Voici un exemple pour illustrer cela :

```php
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);

function callback_function() {
    swoole_timer_after(1000, function () {
        echo "hello world\n";
    });
    global $redis;//même lien
};

swoole_timer_tick(1000, function () {
    echo "parent timer\n";
});//ne pas hériter

Swoole\Process::signal(SIGCHLD, function ($sig) {
    while ($ret = Swoole\Process::wait(false)) {
        // créer un nouveau processus fils
        $p = new Swoole\Process('callback_function');
        $p->start();
    }
});

// créer un nouveau processus fils
$p = new Swoole\Process('callback_function');

$p->start();
```

!> 1. Après le lancement du processus fils, les timers créés par [Swoole\Timer::tick](/timer?id=tick), les signaux écoutés par [Process::signal](/process/process?id=signal) et les événements ajoutés par [Swoole\Event::add](/event?id=add) sont automatiquement éliminés ;  
2. Le processus fils hérite de l'objet lien Redis créé par le processus père, et les processus père et fils utilisent le même lien.


### exportSocket()

Export the `unixSocket` as an object `Swoole\Coroutine\Socket`, then use the methods of the `Swoole\Coroutine\socket` object for inter-process communication. For specific usage, please refer to [Coroutine\socket](/coroutine_client/socket) and [IPC communication](/learn?id=什么是IPC).

```php
Swoole\Process->exportSocket(): Swoole\Coroutine\Socket|false
```

!> Multiple calls to this method return the same object;  
The `socket` exported by `exportSocket()` is a new `fd`, and closing the exported `socket` will not affect the original pipe of the process.  
Since it is an object of `Swoole\Coroutine\Socket`, it must be used within a coroutine container, so the `$enable_coroutine` parameter of the Swoole\Process constructor must be true.  
To use the `Swoole\Coroutine\Socket` object in the same parent process, you need to manually create a coroutine container with `Coroutine\run()`.

* **Return value**

  * Returns a `Coroutine\Socket` object on success
  * Returns `false` if the process has not created a unixSocket and the operation fails

* **Usage example**

A simple example of communication between parent and child processes:  

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    echo $socket->recv();
    $socket->send("hello master\n");
    echo "proc1 stop\n";
}, false, 1, true);

$proc1->start();

// Parent process creates a coroutine container
run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    $socket->send("hello pro1\n");
    var_dump($socket->recv());
});
Process::wait(true);
```

A more complex communication example:

```php
use Swoole\Process;
use Swoole\Timer;
use function Swoole\Coroutine\run;

$process = new Process(function ($proc) {
    Timer::tick(1000, function () use ($proc) {
        $socket = $proc->exportSocket();
        $socket->send("hello master\n");
        echo "child timer\n";
    });
}, false, 1, true);

$process->start();

run(function() use ($process) {
    Process::signal(SIGCHLD, static function ($sig) {
        while ($ret = Swoole\Process::wait(false)) {
            /* clean up then event loop will exit */
            Process::signal(SIGCHLD, null);
            Timer::clearAll();
        }
    });
    /* you can run your other async or coroutine code here */
    Timer::tick(500, function () {
        echo "parent timer\n";
    });

    $socket = $process->exportSocket();
    while (1) {
        var_dump($socket->recv());
    }
});
```
!> Note that the default type is `SOCK_STREAM`, and you need to handle the issues related to the boundaries of TCP packets. Refer to the `setProtocol()` method of [Coroutine\socket](/coroutine_client/socket).  

To use the `SOCK_DGRAM` type for IPC communication, you can avoid dealing with the boundaries of TCP packets. Refer to [IPC communication](/learn?id=什么是IPC):

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

// IPC communication even with SOCK_DGRAM type socket does not require using sendto / recvfrom functions, send/recv can be used instead.
$proc1 = new Process(function (Process $proc) {
    $socket = $proc->exportSocket();
    while (1) {
        var_dump($socket->send("hello master\n"));
    }
    echo "proc1 stop\n";
}, false, 2, 1);// Constructor pipe type set to 2, which is SOCK_DGRAM

$proc1->start();

run(function() use ($proc1) {
    $socket = $proc1->exportSocket();
    Swoole\Coroutine::sleep(5);
    var_dump(strlen($socket->recv()));// Only one "hello master\n" string will be received at a time, and multiple "hello master\n" strings will not appear
});

Process::wait(true);
```
### nom()

Modifie le nom du processus. Cette fonction est un alias de [swoole_set_process_name](/fonctions?id=swoole_set_process_name).

```php
Swoole\Process->nom(string $name): bool
```

!> Après l'exécution de `exec`, le nom du processus est réinitialisé par le nouveau programme ; la méthode `nom` doit être utilisée dans la fonction de rappel de processus fils après le démarrage.


### exec()

Exécute un programme externe, cette fonction est une encapsulation de la call system `exec`.

```php
Swoole\Process->exec(string $execfile, array $args);
```

* **Paramètres** 

  * **`string $execfile`**
    * **Fonction** : Spécifie l'chemin absolu du fichier exécutable, comme `"/usr/bin/python"`
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`array $args`**
    * **Fonction** : Liste des arguments pour `exec`【comme `array('test.py', 123)` , équivalent à `python test.py 123`】
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

Après un succès d'exécution, le code segment du processus actuel sera remplacé par le nouveau programme. Le processus fils se transforme en un autre programme. Le processus parent et le processus actuel restent dans une relation de parent-fils.

Il est possible de communiquer entre le processus parent et le nouveau processus via l'entrée standard et la sortie standard, il faut activer la redirection de l'entrée standard et de la sortie standard.

!> `$execfile` doit utiliser un chemin absolu, sinon une erreur de fichier inexistant sera générée ;  
Comme la call system `exec` utilise le programme spécifié pour remplacer le programme actuel, le processus fils doit lire et écrire sur l'entrée standard et la sortie standard pour communiquer avec le processus parent ;  
Si `redirect_stdin_stdout = true` n'est pas spécifié, après l'exécution de `exec`, le processus fils ne pourra pas communiquer avec le processus parent.

* **Exemples d'utilisation**

Exemple 1 : On peut utiliser [Swoole\Server](/server/init) dans un processus fils créé par `Swoole\Process`, mais pour des raisons de sécurité, il est nécessaire d'appeler `$worker->exec()` après la création du processus avec `$process->start()`. Le code est le suivant :

```php
$process = new Swoole\Process('callback_function', true);

$pid = $process->start();

function callback_function(Swoole\Process $worker)
{
    $worker->exec('/usr/local/bin/php', array(__DIR__.'/swoole_server.php'));
}

Swoole\Process::wait();
```

Exemple 2 : Démarrer un programme Yii

```php
$process = new \Swoole\Process(function (\Swoole\Process $childProcess) {
    // Cette écriture n'est pas prise en charge
    // $childProcess->exec('/usr/local/bin/php /var/www/project/yii-best-practice/cli/yii t/index -m=123 abc xyz');

    // Encapsuler la call system exec
    // Chemin absolu
    // Les arguments doivent être séparés dans un tableau
    $childProcess->exec('/usr/local/bin/php', ['/var/www/project/yii-best-practice/cli/yii', 't/index', '-m=123', 'abc', 'xyz']); // Call system exec
});
$process->start(); // Démarrer le processus fils
```

Exemple 3 : Communiquer avec le processus `exec` en utilisant l'entrée standard et la sortie standard :

```php
// exec - Communiquer avec le processus exec via une pipe
use Swoole\Process;
use function Swoole\Coroutine\run;

$process = new Process(function (Process $worker) {
    $worker->exec('/bin/echo', ['hello']);
}, true, 1, true); // Il faut activer la redirection de l'entrée standard et de la sortie standard

$process->start();

run(function() use($process) {
    $socket = $process->exportSocket();
    echo "de l'exec : " . $socket->recv() . "\n";
});
```

Exemple 4 : Exécuter une commande shell

La méthode `exec` est différente de `shell_exec` fournie par `PHP`, c'est une encapsulation plus basse du system call. Si vous devez exécuter une commande shell, veuillez utiliser la méthode suivante :

```php
$worker->exec('/bin/sh', array('-c', "cp -rf /data/test/* /tmp/test/"));
```


### close()

Utilisé pour fermer un [unixSocket](/learn?id=qu'est-ce-qu'un-ipc). 

```php
Swoole\Process->close(int $which): bool
```

* **Paramètres** 

  * **`int $which`**
    * **Fonction** : Étant donné que unixSocket est bidirectionnel, indique la fin à fermer【par défaut `0` signifie fermer la lecture et l'écriture en même temps, `1` : fermer l'écriture, `2` fermer la lecture】
    * **Valeur par défaut** : `0`, fermer le socket de lecture et d'écriture.
    * **Autres valeurs** : `Swoole/Process::SW_PIPE_CLOSE_READ` fermer le socket de lecture, `Swoole/Process::SW_PIPE_CLOSE_WRITE` fermer le socket d'écriture,

!> Il y a des cas spéciaux où l'objet `Process` ne peut pas être libéré, si vous continuez à créer des processus, cela peut entraîner une fuite des connexions. En appelant cette fonction, vous pouvez directement fermer le `unixSocket` et libérer les ressources.


### exit()

Faire sortir le processus fils.

```php
Swoole\Process->exit(int $status = 0);
```

* **Paramètres** 

  * **`int $status`**
    * **Fonction** : Code d'état de sortie du processus【si `0`, cela signifie une fin normale, le nettoyage continuera】
    * **Valeur par défaut** : `0`
    * **Autres valeurs** : Aucun

!> Le nettoyage comprend :

  * La fonction `shutdown_function` de `PHP`
  * La destruction des objets (`__destruct`)
  * Autres fonctions de fin de `RSHUTDOWN`

Si `$status` n'est pas `0`, cela signifie une sortie anormale, le processus sera immédiatement terminé sans exécuter le nettoyage lié à la fin du processus.

Dans le processus parent, en exécutant `Process::wait`, vous pouvez obtenir l'événement et le code d'état de sortie du processus fils.


### kill()

Envoie un signal à un processus avec le `pid` spécifié.

```php
Swoole\Process::kill(int $pid, int $signo = SIGTERM): bool
```

* **Paramètres** 

  * **`int $pid`**
    * **Fonction** : Processus `pid`
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`int $signo`**
    * **Fonction** : Signal envoyé【`$signo=0`, cela peut vérifier si le processus existe, sans envoyer de signal】
    * **Valeur par défaut** : `SIGTERM`
    * **Autres valeurs** : Aucun


### signal()

Établit une écoute asynchrone pour les signaux.

```php
Swoole\Process::signal(int $signo, callable $callback): bool
```

Cette méthode est basée sur `signalfd` et [EventLoop](/learn?id=qu'est-ce-qu'un-eventloop) est un `IO` asynchrone, elle ne peut pas être utilisée dans des programmes bloquants, cela peut entraîner que la fonction de rappel enregistrée ne sera pas déclenchée ;

Les programmes bloquants synchrones peuvent utiliser la fonction `pcntl_signal` fournie par l'extension `pcntl` ;

Si une fonction de rappel pour ce signal a déjà été définie, la réaffectation覆盖 l'historique de l'établissement.

* **Paramètres** 

  * **`int $signo`**
    * **Fonction** : Signal
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`callable $callback`**
    * **Fonction** : Récupérable de rappel【si `$callback` est `null`, cela signifie retirer l'écoute du signal】
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

!> Dans [Swoole\Server](/server/init), il n'est pas possible d'établir certaines écoutes de signaux, comme `SIGTERM` et `SIGALRM`

* **Exemples d'utilisation**

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
```

!> Dans la version `v4.4.0`, si dans l'EventLoop du processus il n'y a que des événements d'écoute de signaux, sans autres événements (par exemple des horloges Timer, etc.), le processus quittera directement.

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
Swoole\Event::wait();
```

Le processus ci-dessus ne entrera pas dans l'EventLoop, `Swoole\Event::wait()` retournera immédiatement et le processus quittera.
### attente()

Récupère les processus enfants qui ont terminé leur exécution.

!> Lorsque la version Swoole est >= `v4.5.0`, il est recommandé d'utiliser la version coroutine de `attente()`, voir [Swoole\Coroutine\System::attente()](/coroutine/system?id=attente)

```php
Swoole\Process::attente(bool $bloquant = true) : array|false
```

* **Paramètres**

  * **`bool $bloquant`**
    * **Fonction** : Spécifie si l'attente est bloquante【Défaut : bloquant】
    * **Valeur par défaut** : `true`
    * **Autres valeurs** : `false`

* **Valeurs de retour**

  * Un succès retourne un tableau contenant le `PID` du processus enfant, le code d'état de sortie, et le signal `KILL` qui a été utilisé
  * Un échec retourne `false`

!> Après la fin de chaque processus enfant, le processus parent doit exécuter `attente()` pour les récupérer, sinon les processus enfants deviendront des processus fantômes, gaspillant les ressources de processus du système d'exploitation. Si le processus parent a d'autres tâches à accomplir et ne peut pas bloquer `attente`, il doit s'inscrire au signal `SIGCHLD` pour exécuter `attente` sur les processus qui se sont terminés. Lorsque le signal `SIGCHILD` se produit, plusieurs processus enfants peuvent se terminer simultanément ; `attente()` doit être mis en mode non bloquant et exécuté en boucle jusqu'à ce qu'il retourne `false`.

* **Exemple**

```php
Swoole\Process::signal(SIGCHLD, function ($sig) {
    // Doit être false, en mode non bloquant
    while ($ret = Swoole\Process::attente(false)) {
        echo "PID={$ret['pid']}\n";
    }
});
```

### daemon()

Transforme le processus actuel en un démon.

```php
Swoole\Process::daemon(bool $nochdir = true, bool $noclose = true) : bool
```

* **Paramètres**

  * **`bool $nochdir`**
    * **Fonction** : Spécifie si l'on change le répertoire courant au répertoire racine【`true` signifie ne pas changer le répertoire courant au répertoire racine】
    * **Valeur par défaut** : `true`
    * **Autres valeurs** : `false`

  * **`bool $noclose`**
    * **Fonction** : Spécifie si l'on ferme les descripteurs de fichiers standard d'entrée/sortie【`true` signifie ne pas fermer les descripteurs de fichiers standard d'entrée/sortie】
    * **Valeur par défaut** : `true`
    * **Autres valeurs** : `false`

!> Lorsque le processus se transforme en démon, son `PID` changera, et vous pouvez utiliser `getmypid()` pour obtenir le `PID` actuel.

### alarm()

Horloge à haute précision, une encapsulation du système d'appel `setitimer` du système d'exploitation, qui peut configurer des horloges à microsecondes. L'horloge déclenche un signal, qui doit être utilisé en combinaison avec [Process::signal](/process/process?id=signal) ou `pcntl_signal`.

!> `alarm` ne peut pas être utilisé avec [Timer](/timer)

```php
Swoole\Process->alarm(int $time, int $type = 0) : bool
```

* **Paramètres**

  * **`int $time`**
    * **Fonction** : Intervalle de l'horloge【Si négatif, cela signifie effacer l'horloge】
    * **Unité de valeur** : microsecondes
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $type`**
    * **Fonction** : Type d'horloge
    * **Valeur par défaut** : `0`
    * **Autres valeurs** :


Type d'horloge | Description
---|---
0 | Représente le temps réel, déclenche le signal `SIGALRM`
1 | Représente le temps CPU utilisateur, déclenche le signal `SIGVTALAM`
2 | Représente le temps CPU utilisateur + noyau, déclenche le signal `SIGPROF`

* **Valeurs de retour**

  * Un succès retourne `true`
  * Un échec retourne `false`, et vous pouvez utiliser `swoole_errno` pour obtenir le code d'erreur.

* **Exemple d'utilisation**

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

run(function () {
    Process::signal(SIGALRM, function () {
        static $i = 0;
        echo "#{$i}\talarm\n";
        $i++;
        if ($i > 20) {
            Process::alarm(-1);
            Process::kill(getmypid());
        }
    });

    // 100ms
    Process::alarm(100 * 1000);

    while (true) {
        sleep(0.5);
    }
});
```

### setAffinity()

Configure l'affinité CPU, permettant de lier un processus à un ou plusieurs cœurs CPU spécifiques.

Cette fonction permet de faire fonctionner un processus uniquement sur certains cœurs CPU, libérant certaines ressources CPU pour d'autres programmes plus importants.

```php
Swoole\Process->setAffinity(array $cpus) : bool
```

* **Paramètres**

  * **`array $cpus`**
    * **Fonction** : Lier les cœurs CPU 【Par exemple, `array(0,2,3)` signifie lier les cœurs CPU0/CPU2/CPU3】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None


!> - Les éléments de `$cpus` ne doivent pas dépasser le nombre de cœurs CPU ;  

- Le `ID` du CPU ne doit pas dépasser (le nombre de cœurs CPU - 1) ;  

- Cette fonction nécessite le soutien du système d'exploitation pour la configuration de l'affinité CPU ;  
- Utilisez [swoole_cpu_num()](/functions?id=swoole_cpu_num) pour obtenir le nombre de cœurs CPU de l'actuel serveur.

### getAffinity()
Obtenir l'affinité CPU d'un processus

```php
Swoole\Process->getAffinity() : array
```
Le retour est un tableau, dont les éléments sont le nombre de cœurs CPU, par exemple : `[0, 1, 3, 4]` signifie que ce processus sera planifié pour fonctionner sur les cœurs CPU `0/1/3/4`.

### setPriority()

Configure la priorité des processus, des groupes de processus et des processus d'utilisateur.

!> Disponible à partir de la version Swoole `v4.5.9`

```php
Swoole\Process->setPriority(int $which, int $priority) : bool
```

* **Paramètres**

  * **`int $which`**
    * **Fonction** : Déterminer le type de modification de la priorité
    * **Valeur par défaut** : None
    * **Autres valeurs** :


| Constante     | Description     |
| ------------ | -------- |
| PRIO_PROCESS | Processus     |
| PRIO_PGRP    | Groupes de processus |
| PRIO_USER    | Processus d'utilisateur |

  * **`int $priority`**
    * **Fonction** : Priorité. Plus la valeur est petite, plus la priorité est élevée
    * **Valeur par défaut** : None
    * **Autres valeurs** : `[-20, 20]`

* **Valeurs de retour**

  * Si la fonction retourne `false`, vous pouvez utiliser [swoole_errno](/functions?id=swoole_errno) et [swoole_strerror](/functions?id=swoole_strerror) pour obtenir le code d'erreur et l'information d'erreur.

### getPriority()

Obtenir la priorité d'un processus.

!> Disponible à partir de la version Swoole `v4.5.9`

```php
Swoole\Process->getPriority(int $which) : int
```
