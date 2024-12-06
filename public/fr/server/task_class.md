# Swoole\Server\Task

Voici une présentation détaillée de `Swoole\Server\Task`. Cette classe est très simple, mais vous ne pouvez pas obtenir un objet `Task` en utilisant `new Swoole\Server\Task()`, car cet objet ne contient aucune information sur le serveur et l'exécution de n'importe quel méthode de `Swoole\Server\Task` entraînera une erreur fatale.

```shell
Instance invalide de Swoole\Server\Task dans /home/task.php sur la ligne 3
```

## Propriétés


### $data
Les données `data` transmises par le processus `worker` au processus `task`, cette propriété est une chaîne de type `string`.

```php
Swoole\Server\Task->data
```


### $dispatch_time
Le temps auquel ces données sont arrivées au processus `task`, cette propriété est un nombre à double précision.

```php
Swoole\Server\Task->dispatch_time
```


### $id
Le temps auquel ces données sont arrivées au processus `task`, cette propriété est un entier de type `int`.

```php
Swoole\Server\Task->id
```


### $worker_id
Le processus `worker` d'où proviennent ces données, cette propriété est un entier de type `int`.

```php
Swoole\Server\Task->worker_id
```


### $flags
Certaines informations sur les drapeaux de cette tâche asynchrone `flags`, cette propriété est un entier de type `int`.

```php
Swoole\Server\Task->flags
```

?> Les résultats de la propriété `flags` sont les suivants :  
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCK indique que ce n'est pas le processus `Worker` qui a envoyé cette tâche au processus `task`, et si vous appelez `Swoole\Server::finish()` dans l'événement `onTask`, un avertissement sera émis.  
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCK indique que la dernière fonction de rappel dans `Swoole\Server::finish()` n'est pas null, l'événement `onFinish` ne sera pas exécuté, et seule cette fonction de rappel sera exécutée. 
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCK indique que la tâche sera traitée sous forme de coroutines. 
  - SW_TASK_NONBLOCK est la valeur par défaut, lorsque aucune des trois premières situations n'est présente.


## Méthodes


### finish()

Utilisé pour informer le processus `Worker` dans le [Processus Task](/learn?id=taskworkerprocess) que la tâche confiée est terminée. Cette fonction peut transmettre des données de résultat au processus `Worker`.

```php
Swoole\Server\Task->finish(mixed $data): bool
```

  * **Paramètres**

    * `mixed $data`

      * Fonction : Contenu du résultat de l'exécution de la tâche
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Notes**
    * La méthode `finish` peut être appelée plusieurs fois de suite, le processus `Worker` déclenche multiple fois l'événement [onFinish](/server/events?id=onfinish).
    * Si la méthode `finish` est appelée dans la fonction de rappel [onTask](/server/events?id=ontask), les données `return` déclencheront toujours l'événement [onFinish](/server/events?id=onfinish).
    * La méthode `Swoole\Server\Task->finish` est optionnelle. Si le processus `Worker` ne se soucie pas du résultat de l'exécution de la tâche, il n'est pas nécessaire d'appeler cette fonction.
    * Return une chaîne dans la fonction de rappel [onTask](/server/events?id=ontask) est équivalent à appeler `finish`.

  * **Attention**

  !> L'utilisation de la fonction `Swoole\Server\Task->finish` doit avoir une fonction de rappel [onFinish](/server/events?id=onfinish) définie pour le `Server`. Cette fonction ne peut être utilisée que dans le contexte du rappel [onTask](/server/events?id=ontask) du [Processus Task](/learn?id=taskworkerprocess).



### pack()

序列iser les données données.

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

  * **Paramètres**

    * `mixed $data`

      * Fonction : Contenu du résultat de l'exécution de la tâche
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeurs de retour**
    * Return le résultat序列isé en cas d'appel réussi. 


### unpack()

Dés序列iser les données données.

```php
Swoole\Server\Task->unpack(string $data): mixed
```

  * **Paramètres**

    * `string $data`

      * Fonction : Données à dés序列iser
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeurs de retour**
    * Return le résultat dés序列isé en cas d'appel réussi. 

## Exemple d'utilisation
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
