# Swoole\Server\Port

Voici une présentation détaillée de `Swoole\Server\Port`.

## Propriétés


### $host
Retourne l'adresse de l'hôte en écoute, cette propriété est une chaîne de type `string`.

```php
Swoole\Server\Port->host
```


### $port
Retourne le port de l'hôte en écoute, cette propriété est un entier de type `int`.

```php
Swoole\Server\Port->port
```


### $type
Retourne le type de server. Cette propriété est une énumération qui retourne `SWOOLE_TCP`, `SWOOLE_TCP6`, `SWOOLE_UDP`, `SWOOLE_UDP6`, `SWOOLE_UNIX_DGRAM`, `SWOOLE_UNIX_STREAM` l'un desquels.

```php
Swoole\Server\Port->type
```


### $sock
Retourne le socket en écoute, cette propriété est un entier de type `int`.

```php
Swoole\Server\Port->sock
```


### $ssl
Retourne si l'encryptage `ssl` est activé, cette propriété est un booléen.

```php
Swoole\Server\Port->ssl
```


### $setting
Retourne les paramètres de ce port, cette propriété est un tableau d'arrays.

```php
Swoole\Server\Port->setting
```


### $connections
Retourne toutes les connexions à ce port, cette propriété est un itérateur.

```php
Swoole\Server\Port->connections
```


## Méthodes


### set() 

Utilisé pour configurer les divers paramètres de fonctionnement de `Swoole\Server\Port`, l'utilisation est la même que [Swoole\Server->set()](/server/methods?id=set).

```php
Swoole\Server\Port->set(array $setting): void
```


### on() 

Utilisé pour configurer les fonctions de rappel de `Swoole\Server\Port`, l'utilisation est la même que [Swoole\Server->on()](/server/methods?id=on).

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```


### getCallback() 

Retourne la fonction de rappel définie.

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

  * **Paramètres**

    * `string $name`

      * Fonction : Nom de l'événement de rappel
      * Valeur par défaut : Aucun
      * Autres valeurs : Aucun

  * **Valeurs de retour**

    * Retourne la fonction de rappel indiquant un succès, retourne `null` si la fonction de rappel n'existe pas.


### getSocket() 

Convertit le socket actuel `fd` en objet `Socket` PHP.

```php
Swoole\Server\Port->getSocket(): Socket|false
```

  * **Valeurs de retour**

    * Retourne un objet `Socket` indiquant un succès, retourne `false` en cas d'échec.

!> Remarque, cette fonction ne peut être utilisée que si le compilant `Swoole` a été effectué avec l'option `--enable-sockets`.
