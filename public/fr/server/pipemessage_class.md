# Swoole\Server\PipeMessage

Voici une présentation détaillée de `Swoole\Server\PipeMessage`.

## Propriétés


### $source_worker_id
Retourne l'ID du processus `worker` d'où provient les données, cette propriété est un entier de type `int`.

```php
Swoole\Server\PipeMessage->source_worker_id
```

### $dispatch_time
Retourne le temps de réception des données de la demande `dispatch_time`, cette propriété est un nombre à double précision.

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
Retourne les données transportées par cette connexion `data`, cette propriété est une chaîne de type `string`.

```php
Swoole\Server\PipeMessage->data
```
