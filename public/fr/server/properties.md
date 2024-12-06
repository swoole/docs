# Propriétés


### $setting

Les paramètres définis par la fonction [Server->set()](/server/methods?id=set) sont sauvegardés dans la propriété `Server->$setting`. Vous pouvez accéder aux valeurs des paramètres de runtime dans la fonction de rappel. Cette propriété est un tableau d'arrays.

```php
Swoole\Server->setting
```

  * **Exemple**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(array('worker_num' => 4));

echo $server->setting['worker_num'];
```


### $connections

Itérateur des connexions `TCP`, vous pouvez itérer sur toutes les connexions actuelles du serveur en utilisant `foreach`. La fonction de cette propriété est identique à celle de [Server->getClientList](/server/methods?id=getclientlist), mais elle est plus conviviale.

Les éléments itérés sont les `fd` de la connexion individuelle.

```php
Swoole\Server->connections
```

!> La propriété `$connections` est un objet itérateur, et non un tableau PHP, donc vous ne pouvez pas l'accéder avec `var_dump` ou des indices de tableau, mais seulement itérer à travers `foreach`.

  * **Mode Base**

    * Dans le mode [SWOOLE_BASE](/learn?id=swoole_base), les opérations TCP interprocessus ne sont pas prises en charge, donc dans le mode `BASE`, vous ne pouvez utiliser l'itérateur `$connections` que dans le processus actuel.

  * **Exemple**

```php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "Le serveur actuel a un total de " . count($server->connections) . " connexions\n";
```


### $host

Retourne l'adresse `host` du serveur actuel en écoute, cette propriété est une chaîne de caractères (`string`).

```php
Swoole\Server->host
```


### $port

Retourne le port `port` sur lequel le serveur actuel est en écoute, cette propriété est un entier (`int`).

```php
Swoole\Server->port
```


### $type

Retourne le type `type` du serveur actuel, cette propriété est un entier (`int`).

```php
Swoole\Server->type
```

!> Cette propriété retourne l'un des valeurs suivants

- `SWOOLE_SOCK_TCP` socket TCP ipv4

- `SWOOLE_SOCK_TCP6` socket TCP ipv6

- `SWOOLE_SOCK_UDP` socket UDP ipv4

- `SWOOLE_SOCK_UDP6` socket UDP ipv6

- `SWOOLE_SOCK_UNIX_DGRAM` socket unix datagramme
- `SWOOLE_SOCK_UNIX_STREAM` socket unix stream 


### $ssl

Retourne si le serveur actuel a démarré `ssl`, cette propriété est un booléen (`bool`).

```php
Swoole\Server->ssl
```


### $mode

Retourne le mode de processus du serveur actuel `mode`, cette propriété est un entier (`int`).

```php
Swoole\Server->mode
```


!> Cette propriété retourne l'un des valeurs suivants

- `SWOOLE_BASE` mode de processus unique
- `SWOOLE_PROCESS` mode de processus multiple


### $ports

Tableau des ports en écoute, si le serveur écoute plusieurs ports, vous pouvez itérer sur `Server::$ports` pour obtenir tous les objets `Swoole\Server\Port`.

Parmi eux, `swoole_server::$ports[0]` est le port principal défini par la méthode de construction.

  * **Exemple**

```php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on('Receive', function () {
    //callback
});
```


### $master_pid

Retourne le `PID` du processus maître du serveur actuel.

```php
Swoole\Server->master_pid
```

!> Vous ne pouvez l'obtenir qu'après `onStart/onWorkerStart`

  * **Exemple**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->master_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```


### $manager_pid

Retourne le `PID` du processus de gestion du serveur actuel, cette propriété est un entier (`int`).

```php
Swoole\Server->manager_pid
```

!> Vous ne pouvez l'obtenir qu'après `onStart/onWorkerStart`

  * **Exemple**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('start', function ($server){
    echo $server->manager_pid;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->start();
```    


### $worker_id

Obtenir le numéro du processus `Worker` actuel, y compris les [processus Task](/learn?id=taskworker进程), cette propriété est un entier (`int`).

```php
Swoole\Server->worker_id
```
  * **Exemple**

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num' => 8,
    'task_worker_num' => 4,
]);
$server->on('WorkerStart', function ($server, int $workerId) {
    if ($server->taskworker) {
        echo "task workerId：{$workerId}\n";
        echo "task worker_id：{$server->worker_id}\n";
    } else {
        echo "workerId：{$workerId}\n";
        echo "worker_id：{$server->worker_id}\n";
    }
});
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
});
$server->on('Task', function ($serv, $task_id, $reactor_id, $data) {
});
$server->start();
```

  * **Avertissement**

    * Cette propriété est identique à `$workerId` lors de [onWorkerStart](/server/events?id=onworkerstart).
    * Le numéro de processus `Worker` est dans la plage `[0, $server->setting['worker_num'] - 1]`
    * Le numéro de processus [Task](/learn?id=taskworker进程) est dans la plage `[$server->setting['worker_num'], $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1]`

!> Après le redémarrage du processus Worker, la valeur de `worker_id` reste inchangée


### $taskworker

Le processus actuel est-il un processus `Task`, cette propriété est un booléen (`bool`).

```php
Swoole\Server->taskworker
```

  * **Valeur de retour**

    * `true` indique que le processus actuel est un processus de travail Task
    * `false` indique que le processus actuel est un processus Worker


### $worker_pid

Obtenir l'ID du processus système du processus `Worker` actuel. Identique au retour de `posix_getpid()`, cette propriété est un entier (`int`).

```php
Swoole\Server->worker_pid
```
