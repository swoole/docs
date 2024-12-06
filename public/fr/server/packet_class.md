# Swoole\Server\Packet

Voici une présentation détaillée de `Swoole\Server\Packet`.

## Propriétés


### $server_socket
Retourne le descripteur de fichier `fd` du serveur, cette propriété est un entier de type `int`.

```php
Swoole\Server\Packet->server_socket
```


### $server_port
Retourne le port d'écoute du serveur `server_port`, cette propriété est un entier de type `int`.

```php
Swoole\Server\Packet->server_port
```


### $dispatch_time
Retourne l'heure à laquelle les données de la demande ont été reçues `dispatch_time`, cette propriété est un nombre à double précision.

```php
Swoole\Server\Packet->dispatch_time
```


### $address
Retourne l'adresse du client `address`, cette propriété est une chaîne de type `string`.

```php
Swoole\Server\Packet->address
```


### $port
Retourne le port d'écoute du client `port`, cette propriété est un entier de type `int`.

```php
Swoole\Server\Packet->port
```

### $data
Retourne les données transmises par le client `data`, cette propriété est une chaîne de type `string`.

```php
Swoole\Server\Packet->data
```
