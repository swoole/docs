# Swoole\Async\Client

`Swoole\Async\Client`, également appelé `Client`, est un client réseau TCP/UDP/UnixSocket asynchrone et non bloquant. Les clients asynchrones nécessitent l'établissement d'une fonction de rappel d'événement, plutôt que d'attendre de manière synchrone.

- Le client asynchrone est une sous-classe de `Swoole\Client` et peut utiliser certaines méthodes de client synchrone bloquant.  
- Disponible uniquement dans les versions `6.0` et supérieures.

## Exemple complet

```php
$cli = new Swoole\Async\Client(SWOOLE_SOCK_TCP);

$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());
    $client->send(RandStr::gen(1024, RandStr::ALL));
});

$client->on("receive", function(Swoole\Async\Client $client, string $data){
    $recv_len = strlen($data);
    $client->send(RandStr::gen(1024, RandStr::ALL));
    $client->close();
    Assert::false($client->isConnected());
});

$client->on("error", function(Swoole\Async\Client $client) {
    echo "error";
});

$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});

$client->connect("127.0.0.1", 9501, 0.2);
```

## Méthodes

Cette page ne liste que les méthodes qui diffèrent de `Swoole\Client`. Pour les méthodes non modifiées par la sous-classe, veuillez consulter la [documentation du client synchrone bloquant](client.md).

### __construct()

Constructeur, voir la méthode constructeur de la classe parent.

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> Le deuxième argument du client asynchrone doit être `true`.

### on()

Enregistre la fonction de rappel d'événement pour le `Client`.

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> L'appel répété de la méthode `on`覆盖a l'ancienne configuration.

  * **Paramètres**

    * `string $event`

      * Fonction : Nom de l'événement de rappel, insensible à la casse.
      * Valeur par défaut : Aucun.
      * Autres valeurs : Aucun.

    * `callable $callback`

      * Fonction : La fonction de rappel.
      * Valeur par défaut : Aucun.
      * Autres valeurs : Aucun.

      !> Peut être une chaîne de nom de fonction, une méthode statique de classe, un tableau de méthodes d'objet, une fonction anonyme. Pour référence [cette section](/learn?id=quelques-façons-de-mettre-en-place-la-fonction-de-rappel).
  
  * **Valeur de retour**

    * Retourne `true` pour indiquer un succès, `false` pour un échec.

### isConnected()
Détermine si le client est actuellement connecté au serveur.

```php
Swoole\Async\Client->isConnected(): bool
```

* Retourne `true` si connecté, `false` sinon.

### sleep()
Arrête temporairement la réception de données. Après l'appel, le client est retiré de l'événement loop et ne répond plus aux événements de réception de données, à moins qu'il ne soit réveillé avec la méthode `wakeup()`.

```php
Swoole\Async\Client->sleep(): bool
```

* Retourne `true` pour indiquer un succès, `false` pour un échec.

### wakeup()
Révient la réception de données et rejoint l'événement loop.

```php
Swoole\Async\Client->wakeup(): bool
```

* Retourne `true` pour indiquer un succès, `false` pour un échec.

### enableSSL()
Active dynamiquement l'encryption `SSL/TLS`, généralement utilisée pour les clients `startTLS`. Après la connexion établie, des données claires sont d'abord envoyées, puis la transmission encrypted commence.

```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* Cette fonction ne peut être appelée que après un succès de la connexion.
* Le client asynchrone doit établir un `$callback`, qui sera appelé après l'achèvement de la main-d'œuvre `SSL`.
* Retourne `true` pour indiquer un succès, `false` pour un échec.

## Événements de rappel

### connect
Déclenché après l'établissement d'une connexion. Si un proxy `HTTP` ou `Socks5` et une encryption de tunnel `SSL` sont définis, ils sont déclenchés après l'achèvement de la main-d'œuvre du proxy et de la main-d'œuvre de la sécurité `SSL`.

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

Après cet événement de rappel, l'utilisation de `isConnected()` retournera `true`.

### error 
Déclenché en cas d'échec de la connexion. Vous pouvez obtenir des informations sur l'erreur en accédant à `$client->errCode`.
```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```

- Veuillez noter que `connect` et `error` ne déclenchent qu'un seul des deux, il n'y a qu'un résultat possible : succès ou échec de la connexion.
- Le `Client::connect()` peut directement retourner `false`, indiquant un échec de la connexion, dans ce cas, le rappel `error` ne sera pas exécuté. Veuillez vérifier la valeur de retour de la méthode `connect`.
- L'événement `error` est un résultat asynchrone, il peut y avoir un certain temps d'attente `IO` entre le début de l'appel et le déclenchement de l'événement `error`.
- Un échec direct de la connexion signifie un échec immédiat, cet échec est déclenché directement par l'opération système, sans aucun temps d'attente `IO`.

### receive
Déclenché après la réception de données.

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```

- Si aucune协议 n'est définie, comme `EOF` ou `LENGTH`, la longueur maximale des données retournées est de `64K`.
- Si des paramètres de traitement de protocol sont définis, la longueur maximale des données est fixée par le paramètre `package_max_length`, qui a une valeur par défaut de `2M`.
- `$data` n'est jamais vide. Si une erreur du système ou une fermeture de connexion est reçue, l'événement `close` sera déclenché.

### close
Déclenché au moment de la fermeture de la connexion.

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```
