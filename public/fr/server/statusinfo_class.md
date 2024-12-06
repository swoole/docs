# Informations sur Swoole\Server\StatusInfo

Voici une présentation détaillée de `Swoole\Server\StatusInfo`.

## Propriétés


### $worker_id
Retourne l'ID du processus `worker` actuel, cette propriété est un entier de type `int`.

```php
Swoole\Server\StatusInfo->worker_id
```


### $worker_pid
Retourne l'ID du processus parent du processus `worker` actuel, cette propriété est un entier de type `int`.

```php
Swoole\Server\StatusInfo->worker_pid
```


### $status
Retourne l'état du processus `status`, cette propriété est un entier de type `int`.

```php
Swoole\Server\StatusInfo->status
```


### $exit_code
Retourne le code d'état de sortie du processus `exit_code`, cette propriété est un entier de type `int`, dans la plage de `0-255`.

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
Le signal qui a provoqué la sortie du processus `signal`, cette propriété est un entier de type `int`.
