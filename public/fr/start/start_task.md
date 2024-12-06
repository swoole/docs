# Exécution de tâches asynchrones

Dans les programmes de serveur, si des opérations longues à exécuter sont nécessaires, comme envoyer une diffusion dans un serveur de chat ou envoyer des e-mails dans un serveur Web, les fonctions directes ne doivent pas être appelées car cela bloquerait le processus actuel et ralentirait la réponse du serveur.

Swoole offre une fonction de traitement de tâches asynchrones qui permet de déposer une tâche asynchrone dans le pool de processus Worker Task pour exécution, sans affecter la vitesse de traitement des demandes actuelles.

## Code du programme

Basé sur le premier serveur TCP, il suffit d'ajouter deux fonctions de rappel d'événement, [onTask](/server/events?id=ontask) et [onFinish](/server/events?id=onfinish). De plus, il est nécessaire de configurer le nombre de processus task. Cela peut être fait en fonction de la durée et de la quantité de tâches.

Veuillez écrire le code suivant dans task.php.

```php
$serv = new Swoole\Server('127.0.0.1', 9501);

// Régler le nombre de processus worker pour les tâches asynchrones.
$serv->set([
    'task_worker_num' => 4
]);

// Cette fonction de rappel est exécutée dans le processus worker.
$serv->on('Receive', function($serv, $fd, $reactor_id, $data) {
    // Déposer une tâche asynchrone
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id={$task_id}\n";
});

// Traiter la tâche asynchrone (cette fonction de rappel est exécutée dans le processus task).
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id={$task_id}]".PHP_EOL;
    // Retourner le résultat de l'exécution de la tâche
    $serv->finish("{$data} -> OK");
});

// Traiter le résultat de la tâche asynchrone (cette fonction de rappel est exécutée dans le processus worker).
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[{$task_id}] Finish: {$data}".PHP_EOL;
});

$serv->start();
```

Après avoir appelé `$serv->task()`, le programme retourne immédiatement et continue à exécuter le code ci-dessous. La fonction de rappel onTask est exécutée asynchronement dans le pool de processus Task. Une fois terminée, l'appel à `$serv->finish()` retourne le résultat.

!> L'opération finish est optionnelle et peut également ne pas retourner de résultat. Si un résultat est retourné dans l'événement onTask par une `return`, cela équivaut à appeler l'opération `$Swoole\Server::finish()`.
