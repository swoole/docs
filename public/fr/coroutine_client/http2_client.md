```php
# Coroutine\Http2\Client

Client HTTP/2 en coroutines

## Exemple d'utilisation

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $domain = 'www.zhihu.com';
    $cli = new Client($domain, 443, true);
    $cli->set([
        'timeout' => -1,
        'ssl_host_name' => $domain
    ]);
    $cli->connect();
    $req = new Request();
    $req->method = 'POST';
    $req->path = '/api/v4/answers/300000000/voters';
    $req->headers = [
        'host' => $domain,
        'user-agent' => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip'
    ];
    $req->data = '{"type":"up"}';
    $cli->send($req);
    $response = $cli->recv();
    var_dump(assert(json_decode($response->data)->error->code === 10002));
});
```

## Méthodes

### __construct()

Constructeur.

```php
Swoole\Coroutine\Http2\Client::__construct(string $host, int $port, bool $open_ssl = false): void
```

  * **Paramètres** 

    * **`string $host`**
      * **Fonction** : IP de la machine cible 【Si `$host` est un nom de domaine, une recherche DNS est effectuée】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $port`**
      * **Fonction** : Port cible 【Pour `Http`, c'est généralement le port `80`, pour `Https`, c'est généralement le port `443`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`bool $open_ssl`**
      * **Fonction** : Activer ou non la sécurisation TLS/SSL 【Pour les sites `https`, il doit être réglé sur `true`】
      * **Valeur par défaut** : `false`
      * **Autres valeurs** : `true`

  * **Remarque**

    !> - Si vous devez demander un URL en ligne,修改z `timeout` pour une valeur plus grande, voir [Règles de timeout client](/coroutine_client/init?id=règles de timeout)  
    - `$ssl` nécessite la dépendance `openssl`, qui doit être activée lors de la compilation de `Swoole` [--enable-openssl](/environment?id=options de compilation)


### set()

Définir les paramètres du client, pour d'autres détails de configuration, veuillez consulter [Swoole\Client::set](/client?id=configuration) options de configuration

```php
Swoole\Coroutine\Http2\Client->set(array $options): void
```


### connect()

Se connecter au serveur cible. Cette méthode n'a aucun paramètre.

!> Après avoir lancé `connect`, le sous-système Coroutine effectue automatiquement [gestion de coroutines](/coroutine?id=gestion de coroutines), et `connect` retourne lorsque la connexion est réussie ou échoue. Une fois la connexion établie, vous pouvez appeler la méthode `send` pour envoyer une demande au serveur.

```php
Swoole\Coroutine\Http2\Client->connect(): bool
```

  * **Valeur de retour**

    * Connexion réussie, retourne `true`
    * Connexion échouée, retourne `false`, veuillez vérifier la propriété `errCode` pour obtenir l'code d'erreur


### stats()

Obtenir les statistiques du flux.

```php
Swoole\Coroutine\Http2\Client->stats([$key]): array|bool
```

  * **Exemple**

```php
var_dump($client->stats(), $client->stats()['local_settings'], $client->stats('local_settings'));
```


### isStreamExist()

Déterminer si le flux spécifié existe.

```php
Swoole\Coroutine\Http2\Client->isStreamExist(int $stream_id): bool
```


### send()

Envoyer une demande au serveur, le sous-système底层 établira automatiquement un `Http2` stream. Plusieurs demandes peuvent être lancées simultanément.

```php
Swoole\Coroutine\Http2\Client->send(Swoole\Http2\Request $request): int|false
```

  * **Paramètres** 

    * **`Swoole\Http2\Request $request`**
      * **Fonction** : Envoyer un objet Swoole\Http2\Request
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeur de retour**

    * Réussite, retourne le numéro du flux, un nombre impair commençant par `1` et augmentant de `1` à chaque fois
    * Échec, retourne `false`

  * **Avertissement**

    * **Objet Request**

      !> L'objet `Swoole\Http2\Request` n'a aucune méthode, l'information de la demande est écrite en setting les propriétés de l'objet.

      *数组 `headers`, `HTTP` headers
      * string `method`,设置了请求方法，如`GET`、`POST`
      * string `path`,设置了URL路径，如`/index.php?a=1&b=2`，必须以/作为开始
      * array `cookies`,设置了`COOKIES`
      * mixed `data`设置了请求的`body`，如果为字符串时将直接作为`RAW form-data`进行发送
      * array `data`时，底层自动会打包为`x-www-form-urlencoded`格式的`POST`内容，并设置`Content-Type为application/x-www-form-urlencoded`
      * bool `pipeline`,如果设置为`true`，发送完`$request`后，不关闭`stream`，可以继续写入数据内容

    * **pipeline**

      * Par défaut, la méthode `send` termine le courant `Http2 Stream` après avoir envoyé la demande, l'activation de `pipeline` maintient le stream, vous pouvez appeler à plusieurs reprises la méthode `write`, envoyer des trames de données au serveur, veuillez consulter la méthode `write`.


### write()

Envoyer plus de trames de données au serveur, vous pouvez appeler à plusieurs reprises `write` pour écrire des trames de données dans le même stream.

```php
Swoole\Coroutine\Http2\Client->write(int $streamId, mixed $data, bool $end = false): bool
```

  * **Paramètres** 

    * **`int $streamId`**
      * **Fonction** : Numéro du flux, retourné par la méthode `send`
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`mixed $data`**
      * **Fonction** : Contenu de la trame de données, peut être une chaîne ou un tableau
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`bool $end`**
      * **Fonction** : Fermer le flux
      * **Valeur par défaut** : `false`
      * **Autres valeurs** : `true`

  * **Exemple d'utilisation**

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9518);
    $cli->set(['timeout' => 1]);
    var_dump($cli->connect());

    $req3 = new Request();
    $req3->path = "/index.php";
    $req3->headers = [
        'host' => "localhost",
        "user-agent" => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip',
    ];
    $req3->pipeline = true;
    $req3->method = "POST";
    $streamId = $cli->send($req3);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    //end stream
    $cli->write($streamId, ['int' => rand(1000, 9999), 'end' => true], true);
    var_dump($cli->recv());
    $cli->close();
});
```

!> Si vous utilisez `write` pour envoyer des trames de données par sections, vous devez设置了 `$request->pipeline` à `true` lors de l'envoi de la demande avec `$request`.  
Après avoir envoyé une trame de données avec `$end` comme `true`, le flux sera fermé et il ne sera plus possible d'appeler `write` pour envoyer des données à ce flux.


### recv()

Réceptionner la demande.

!> L'appel de cette méthode génère une [gestion de coroutines](/coroutine?id=gestion de coroutines)

```php
Swoole\Coroutine\Http2\Client->recv(float $timeout): Swoole\Http2\Response;
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Définir le temps de timeout, voir [Règles de timeout client](/coroutine_client/init?id=règles de timeout)
      * **Unité de valeur** : seconde 【Prend en charge les nombres à virgule flottante, comme `1.5` signifie `1s`+`500ms`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeur de retour**

Réussite, retourne un objet Swoole\Http2\Response

```php
/**@var $resp Swoole\Http2\Response */
var_dump($resp->statusCode); // Code HTTP envoyé par le serveur, comme 200, 502, etc.
var_dump($resp->headers); // Informations Headers envoyées par le serveur
var_dump($resp->cookies); // Informations Cookies mises en place par le serveur
var_dump($resp->set_cookie_headers); // Informations Cookies d'origine envoyées par le serveur, y compris les domaines et les chemins
var_dump($resp->data); // Corps de la réponse envoyée par le serveur
```

!> Pour les versions de Swoole inférieures à [v4.0.4](/version/bc?id=_404), la propriété `data` est la propriété `body`; pour les versions de Swoole inférieures à [v4.0.3](/version/bc?id=_403), les propriétés `headers` et `cookies` sont au singulier.
```
### read()

Consiste essentiellement en la même chose que `recv()`, la différence réside dans le fait que pour les réponses de type `pipeline`, `read` peut être lu à plusieurs reprises, chaque fois pour lire une partie du contenu afin d'économiser la mémoire ou d'accueillir rapidement les informations push, tandis que `recv` ne renvoie jamais la réponse complète qu'après avoir assemblé tous les frames.

!> L'appel de cette méthode génère un [schéma de planification de coroutines](/coroutine?id=schéma-de-planification-de-coroutines)

```php
Swoole\Coroutine\Http2\Client->read(float $timeout): Swoole\Http2\Response;
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Établir le temps d'attente, voir [règles de timeout client](/coroutine_client/init?id=règles-de-timeout)
      * **Unité de valeur** : seconde 【Prend en charge les nombres à virgule flottante, comme `1.5` signifie `1s`+`500ms`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeur de retour**

    Renvoie un objet Swoole\Http2\Response en cas de succès


### goaway()

Le frame GOAWAY est utilisé pour lancer la fermeture de la connexion ou envoyer un signal d'état d'erreur grave.

```php
Swoole\Coroutine\Http2\Client->goaway(int $error_code = SWOOLE_HTTP2_ERROR_NO_ERROR, string $debug_data): bool
```


### ping()

Le frame PING est un mécanisme utilisé pour mesurer le temps de retour minimal du côté émetteur et déterminer si la connexion inactif est toujours valide.

```php
Swoole\Coroutine\Http2\Client->ping(): bool
```

### close()

Ferme la connexion.

```php
Swoole\Coroutine\Http2\Client->close(): bool
```
