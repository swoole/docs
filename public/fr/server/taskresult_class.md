# Swoole\Server\TaskResult

Voici une présentation détaillée de `Swoole\Server\TaskResult`.

## Propriétés


### $task_id
Retourne l'identifiant du thread `Reactor` où se trouve la tâche, cette propriété est un entier de type `int`.

```php
Swoole\Server\TaskResult->task_id
```


### $task_worker_id
Retourne l'identifiant du processus `task` d'où provient ce résultat d'exécution, cette propriété est un entier de type `int`.

```php
Swoole\Server\TaskResult->task_worker_id
```


### $dispatch_time
Retourne les données `data` transportées par cette connexion, cette propriété est une chaîne de type `?string`.

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
Retourne les données `data` transportées par cette connexion, cette propriété est une chaîne de type `string`.

```php
Swoole\Server\StatusInfo->data
```
