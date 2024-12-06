# Swoole\Thread <!-- {docsify-ignore-all} -->

À partir de la version `6.0`, Swoole offre une prise en charge multithread, permettant l'utilisation des API threads pour remplacer les processus multiprocessus. Par rapport aux processus multiprocessus, les Threads offrent un ensemble de conteneurs de données concourantes plus riche, ce qui est plus pratique pour le développement de serveurs de jeux et de serveurs de communication.

- PHP doit être en mode ZTS (Zend Thread Safety), et l'option `--enable-zts` doit être ajoutée lors de la compilation de PHP.
- Lors de la compilation de Swoole, l'option `--enable-swoole-thread` doit être ajoutée.

## Isolement des ressources

Les threads Swoole sont similaires aux threads Worker Thread de Node.js. Un nouvel environnement ZendVM est créé dans les sous-threads. Les sous-threads ne héritent de aucune ressource de leur thread parent, donc le contenu suivant est effacé dans les sous-threads et doit être recréé ou réinitialisé.

- Les fichiers PHP chargés doivent être rechargés avec `include/require`.
- La fonction `autoload` doit être reregistered.
- Les classes, les fonctions et les constantes seront effacées et doivent être recréées en chargeant de nouveau les fichiers PHP.
- Les variables globales, telles que `$GLOBALS`, `$_GET/$_POST`, etc., seront réinitialisées.
- Les propriétés statiques des classes et les variables statiques des fonctions seront réinitialisées à leur valeur initiale.
- Certaines options de `php.ini`, telles que `error_reporting()`, doivent être réconfigurées dans les sous-threads.

## Caractéristiques Indisponibles

En mode multithread, les caractéristiques suivantes ne peuvent être opérées que dans le thread principal et ne peuvent pas être exécutées dans les sous-threads :

- `swoole_async_set()` pour modifier les paramètres du thread.
- `Swoole\Runtime::enableCoroutine()` et `Swoole\Runtime::setHookFlags()`.
- Seul le thread principal peut établir des écouteurs de signal, y compris `Process::signal()` et `Coroutine\System::waitSignal()`, qui ne peuvent pas être utilisés dans les sous-threads.
- Seul le thread principal peut créer des serveurs asynchrones, y compris `Server`, `Http\Server`, `WebSocket\Server`, etc., qui ne peuvent pas être utilisés dans les sous-threads.

En outre, une fois que l'hook Runtime est activé en mode multithread, il ne peut pas être désactivé.

## Erreur FATAL
Lorsque le thread principal se termine, s'il y a encore des threads actifs, une erreur fatale sera levée avec le statut de sortie `200` et l'information d'erreur suivante :
```
Fatal Error: 2 active threads are running, cannot exit safely.
```

## Vérifier si la prise en charge des threads est activée

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)` indique que la sécurité par thread est activée.

```shell
php --ri swoole

swoole
Swoole => enabled
thread => enabled
```

`thread => enabled` indique que la prise en charge multithread est activée.

### Création de threads multiples
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

// Le thread principal n'a pas de paramètres de thread, donc $args est null
if (empty($args)) {
    # Thread principal
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # Sous-thread
    echo "Thread #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```

### Threads + Serveur (style asynchrone)

- Tous les processus de travail utiliseront des threads pour fonctionner, y compris `Worker`, `Task Worker`, `User Process`.
- Un nouveau mode de fonctionnement `SWOOLE_THREAD` a été ajouté, qui utilise des threads à la place de processus une fois activé.
- Deux nouvelles configurations ont été ajoutées, [bootstrap](/server/setting?id=bootstrap) et [init_arguments](/server/setting?id=init_arguments), pour configurer le fichier d'entrée des threads de travail et les données partagées entre les threads.
- Le `Server` doit être créé dans le thread principal, et de nouveaux threads peuvent être créés dans les fonctions de rappel pour exécuter d'autres tâches.
- Les objets de processus `Server::addProcess()` ne prennent pas en charge la redirection standard des entrées/sorties.

```php
use Swoole\Process;
use Swoole\Thread;
use Swoole\Http\Server;

$http = new Server("0.0.0.0", 9503, SWOOLE_THREAD);
$http->set([
    'worker_num' => 2,
    'task_worker_num' => 3,
    'bootstrap' => __FILE__,
    // Utiliser init_arguments pour réaliser le partage de données entre les threads.
    'init_arguments' => function () use ($http) {
        $map = new Swoole\Thread\Map;
        return [$map];
    }
]);

$http->on('Request', function ($req, $resp) use ($http) {
    $resp->end('hello world');
});

$http->on('pipeMessage', function ($http, $srcWorkerId, $msg) {
    echo "[worker#" . $http->getWorkerId() . "]\treceived pipe message[$msg] from " . $srcWorkerId . "\n";
});

$http->addProcess(new Process(function () {
   echo "user process, id=" . Thread::getId();
   sleep(2000);
}));

$http->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
    var_dump($taskId, $srcWorkerId, $data);
    return ['result' => uniqid()];
});

$http->on('Finish', function ($server, $taskId, $data) {
    var_dump($taskId, $data);
});

$http->on('WorkerStart', function ($serv, $wid) {
    // Obtenir les données partagées transmises via init_arguments en utilisant Swoole\Thread::getArguments()
    var_dump(Thread::getArguments(), $wid);
});

$http->on('WorkerStop', function ($serv, $wid) {
    var_dump('stop: T' . Thread::getId());
});

$http->start();
```
