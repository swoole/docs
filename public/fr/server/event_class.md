# Swoole\Server\Event

Voici une présentation détaillée de `Swoole\Server\Event`.

## Propriétés


### $reactor_id
Retourne l'ID du thread `Reactor` où se trouve cette instance, cette propriété est un entier de type `int`.

```php
Swoole\Server\Event->reactor_id
```


### $fd
Retourne le descripteur de fichier `fd` de la connexion, cette propriété est un entier de type `int`.

```php
Swoole\Server\Event->fd
```


### $dispatch_time
Retourne le temps de dispatch de la demande `dispatch_time`, cette propriété est un type `double`. Cette propriété n'est pas `0` que dans l'événement `onReceive`.

```php
Swoole\Server\Event->dispatch_time
```

### $data
Retourne les données envoyées par le client `data`, cette propriété est une chaîne de type `string`. Cette propriété n'est pas `null` que dans l'événement `onReceive`.
