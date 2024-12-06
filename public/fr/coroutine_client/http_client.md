# Client HTTP/WebSocket asynchrone

Le client HTTP asynchrone est écrit en pure C et ne dépend d'aucune bibliothèque tierce, offrant des performances extrêmement élevées.

* Prend en charge les fonctionnalités Http-Chunk et Keep-Alive, et le format form-data
* La version du protocole HTTP est HTTP/1.1
* Prend en charge l'upgrade en client WebSocket
* Le format de compression gzip nécessite la dépendance de la bibliothèque zlib
* Le client ne met en œuvre que les fonctionnalités clés, il est conseillé d'utiliser [Saber](https://github.com/swlib/saber) pour les projets réels


## Propriétés


### errCode

Code d'erreur. Lorsqu'une connexion/envoyé/reçu/fermeture échoue ou qu'un timeout se produit, la valeur de `Swoole\Coroutine\Http\Client->errCode` est automatiquement définie.

```php
Swoole\Coroutine\Http\Client->errCode: int
```

La valeur de `errCode` est égale à l'errno Linux. Vous pouvez utiliser `socket_strerror` pour transformer le code d'erreur en message d'erreur.

```php
// Si la connexion est refusée, le code d'erreur est 111
// Si le timeout se produit, le code d'erreur est 110
echo socket_strerror($client->errCode);
```

!> Référence : [Liste des codes d'erreur Linux](/other/errno?id=linux)


### body

Stocke le corps du paquet de réponse de la dernière demande.

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```


### statusCode

Code d'état HTTP, comme 200, 404, etc. Si le code d'état est négatif, cela indique qu'il y a un problème avec la connexion. [Afficher plus](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```


## Méthodes


### __construct()

Constructeur.

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

  * **Paramètres** 

    * **`string $host`**
      * **Fonction** : Adresse de l'hôte du serveur cible【peut être une IP ou un nom de domaine, la résolution de nom de domaine est automatiquement effectuée en dessous, si c'est une UNIX Socket locale, il doit être écrit sous la forme `unix://tmp/your_file.sock` ; si c'est un nom de domaine, il n'est pas nécessaire d'écrire l'en-tête `http://` ou `https://`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $port`**
      * **Fonction** : Port de l'hôte du serveur cible
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`bool $ssl`**
      * **Fonction** : Activer ou désactiver le tunnel d'encryption SSL/TLS, si le serveur cible est https, il doit être mis à `true`
      * **Valeur par défaut** : `false`
      * **Autres valeurs** : None

  * **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```


### set()

Mettre en place les paramètres du client.

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

Cette méthode prend exactement les mêmes paramètres que `Swoole\Client->set`, vous pouvez consulter la documentation de la méthode [Swoole\Client->set](/client?id=set).

`Swoole\Coroutine\Http\Client` ajoute quelques options supplémentaires pour contrôler le client HTTP et WebSocket.

#### Options supplémentaires

##### Contrôle du timeout

Mettre en place l'option `timeout`, activer la détection de timeout des demandes HTTP. La unité est la seconde, la plus petite granularité prend en charge la milliseconde.

```php
$http->set(['timeout' => 3.0]);
```

* Si la connexion est terminée par le serveur ou fermée par le serveur, `statusCode` sera mis à `-1`
* Si le serveur ne répond pas dans le délai convenu, la demande est timeout, `statusCode` sera mise à `-2`
* Après le timeout de la demande, la connexion sera automatiquement coupée en dessous
* Référence [Règles de timeout du client](/coroutine_client/init?id=timeout)

##### keep_alive

Mettre en place l'option `keep_alive`, activer ou désactiver la connexion HTTP à long terme.

```php
$http->set(['keep_alive' => false]);
```

##### websocket_mask

> En raison des réglementations RFC, cette configuration est activée par défaut à partir de la version 4.4.0, mais cela peut entraîner une perte de performance, si le serveur n'exige pas de manière obligatoire, vous pouvez la désactiver en la mettant à `false`

Activer ou désactiver le masquage pour les clients WebSocket. Par défaut, c'est activé. Une fois activé, le contenu des données envoyées par le client WebSocket sera transformé en utilisant un masque.

```php
$http->set(['websocket_mask' => false]);
```

##### websocket_compression

> Requis pour la version 4.4.12 ou supérieure

Pour `true**, **permet** de compresser les trames avec zlib, si la compression peut être effectuée dépend du serveur (décidé selon les informations de handshake, voir `RFC-7692`)

Pour vraiment compresser un cadre spécifique, vous devez utiliser la flag `SWOOLE_WEBSOCKET_FLAG_COMPRESS` avec la méthode, voir [cette section](/websocket_server?id=compression de trame WebSocket - (rfc-7692)) pour l'utilisation spécifique.

```php
$http->set(['websocket_compression' => true]);
```

##### write_func
> Requis pour la version 5.1.0 ou supérieure

Mettre en place la fonction de rappel `write_func`, similaire à l'option `WRITE_FUNCTION` de `CURL`, utilisable pour traiter le contenu de réponse en flux,
par exemple, le contenu de sortie de l'événement Stream d'OpenAI ChatGPT.

> Après avoir établi la `write_func`, il n'est plus possible d'utiliser la méthode `getContent()` pour obtenir le contenu de la réponse, et `$client->body` sera également vide  
> Dans la fonction de rappel `write_func`, vous pouvez utiliser `$client->close()` pour arrêter de recevoir le contenu de la réponse et fermer la connexion

```php
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->set(['write_func' => function ($client, $data) {
    var_dump($data);
}]);
$cli->get('/');
```


### setMethod()

Mettre en place la méthode de demande. Valide seulement pour la demande actuelle, la méthode est effacée immédiatement après l'envoi de la demande.

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **Paramètres** 

    * **`string $method`**
      * **Fonction** : Mettre en place la méthode 
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

      !> Doit être un nom de méthode conforme aux normes HTTP, si `$method` est mal mis en place, cela peut entraîner une demande rejetée par le serveur HTTP

  * **Exemple**

```php
$http->setMethod("PUT");
```


### setHeaders()

Mettre en place les en-têtes HTTP de demande.

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **Paramètres** 

    * **`array $headers`**
      * **Fonction** : Mettre en place les en-têtes de demande 【doit être un tableau avec des paires clé-valeur, la transformation en format standard HTTP `$key`: `$value` est automatiquement effectuée en dessous】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

!> Les en-têtes HTTP mis en place par `setHeaders` restent valides pour chaque demande tant que l'objet `Coroutine\Http\Client` est vivant ; appeler à nouveau `setHeaders` remplacera les précédentes installations


### setCookies()

Mettre en place les `Cookie`, les valeurs seront codées en `urlencode`, si vous souhaitez conserver l'information originale, veuillez utiliser `setHeaders` pour mettre en place un en-tête nommé `Cookie`.

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **Paramètres** 

    * **`array $cookies`**
      * **Fonction** : Mettre en place les `COOKIE` 【doit être un tableau avec des paires clé-valeur】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None
!> Après avoir établi un `COOKIE`, il sera conservé tant que l'objet client est actif  

- Les `COOKIES` activement définis par le serveur sont fusionnés dans l'array `cookies`, et vous pouvez obtenir les informations sur les `COOKIES` actuelles de l'HTTP client en lisant la propriété `$client->cookies`  
- Appeler à plusieurs reprises la méthode `setCookies` remplacera l'état actuel des `Cookies`, ce qui entraînera la perte des `COOKIES` précédemment envoyés par le serveur et des `COOKIES` activement définis


### setData()

Définir le corps de la demande HTTP.

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **Paramètres** 

    * **`string|array $data`**
      * **Fonction** : Définir le corps de la demande
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Notes**

    * Après avoir défini `$data` et s'il n'a pas été défini `$method`, le niveau inférieur将自动 le traiter comme POST
    * Si `$data` est un tableau et que `Content-Type` est de type `urlencoded`, le niveau inférieur procédera automatiquement à `http_build_query`
    * Si `addFile` ou `addData` est utilisé pour activer le format `form-data`, la valeur de `$data` sera ignorée si elle est une chaîne (car le format est différent), mais sera ajoutée au niveau inférieur sous la forme de `form-data` si elle est un tableau


### addFile()

Ajouter un fichier POST.

!> L'utilisation de `addFile` changera automatiquement le `Content-Type` POST en `form-data`. Le `addFile` repose sur `sendfile` et peut prendre en charge l'envoi asynchrone de très gros fichiers.

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **Paramètres** 

    * **`string $path`**
      * **Fonction** : Chemin du fichier【Paramètre obligatoire, ne pas utiliser de fichier vide ou inexistant】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $name`**
      * **Fonction** : Nom du formulaire【Paramètre obligatoire, `key` dans le paramètre `FILES`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $mimeType`**
      * **Fonction** : Format MIME du fichier【Paramètre optionnel, le niveau inférieur déduira automatiquement à partir de l'extension du fichier】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $filename`**
      * **Fonction** : Nom du fichier【Paramètre optionnel】
      * **Valeur par défaut** : `basename($path)`
      * **Autres valeurs** : Aucun

    * **`int $offset`**
      * **Fonction** : Offset de téléchargement du fichier【Paramètre optionnel, permet de specifier le début de la transmission des données à partir du milieu du fichier. Cette caractéristique peut être utilisée pour prendre en charge le transfert en continu.】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $length`**
      * **Fonction** : Taille des données envoyées【Paramètre optionnel】
      * **Valeur par défaut** : taille totale du fichier
      * **Autres valeurs** : Aucun

  * **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```


### addData()

Construire le contenu du fichier à télécharger en utilisant une chaîne. 

!> `addData` est disponible à partir de la version `v4.1.0`.

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **Paramètres** 

    * **`string $data`**
      * **Fonction** : Contenu des données【Paramètre obligatoire, la longueur maximale ne doit pas dépasser [buffer_output_size](/server/setting?id=buffer_output_size)】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $name`**
      * **Fonction** : Nom du formulaire【Paramètre obligatoire, `key` dans le paramètre `$_FILES`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $mimeType`**
      * **Fonction** : Format MIME du fichier【Paramètre optionnel, par défaut `application/octet-stream`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $filename`**
      * **Fonction** : Nom du fichier【Paramètre optionnel, par défaut `$name`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```


### get()

Lancer une demande GET.

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **Paramètres** 

    * **`string $path`**
      * **Fonction** : Définir la path de la `URL`【par exemple `/index.html`, veuillez noter que vous ne pouvez pas passer `http://domain`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

!> L'utilisation de `get` ignorera la méthode de demande définie par `setMethod` et forcera l'utilisation de `GET`


### post()

Lancer une demande POST.

```php
Swoole\Coroutine\Http\Client->post(string $path, mixed $data): void
```

  * **Paramètres** 

    * **`string $path`**
      * **Fonction** : Définir la path de la `URL`【par exemple `/index.html`, veuillez noter que vous ne pouvez pas passer `http://domain`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`mixed $data`**
      * **Fonction** : Données du corps de la demande
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

      !> Si `$data` est un tableau, le niveau inférieur encapsulera automatiquement les données dans un contenu POST au format `x-www-form-urlencoded` et établira `Content-Type` comme `application/x-www-form-urlencoded`

  * **Note**

    !> L'utilisation de `post` ignorera la méthode de demande définie par `setMethod` et forcera l'utilisation de `POST`

  * **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```


### upgrade()

Mettre à niveau en connexion WebSocket.

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **Paramètres** 

    * **`string $path`**
      * **Fonction** : Définir la path de la `URL`【par exemple `/`，veuillez noter que vous ne pouvez pas passer `http://domain`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Notes**

    * Dans certains cas, bien que la demande soit réussie, `upgrade` retourne `true`, mais le serveur n'établit pas l'état HTTP `101`, mais plutôt `200` ou `403`, ce qui indique que le serveur a refusé la demande de handshake
    * Après un succès de la handshake WebSocket, vous pouvez utiliser la méthode `push` pour envoyer des messages au serveur, et vous pouvez également appeler `recv` pour recevoir des messages
    * L'appel à `upgrade` génère une [gestion des coroutines](/coroutine?id=gestion-des-coroutines)

  * **Exemple**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### push()

Envoie un message au serveur `WebSocket`.

!> La méthode `push` ne peut être exécutée qu'après un succès de l'upgrade  
La méthode `push` ne génère pas de planification de coroutines, elle retourne immédiatement après avoir écrit dans le tampon de transmission

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **Paramètres** 

    * **`mixed $data`**
      * **Fonction** : Contenu du données à envoyer【Par défaut en format texte UTF-8, si c'est une autre format d'encodage ou de données binaires, veuillez utiliser `WEBSOCKET_OPCODE_BINARY`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

      !> Avec Swoole version >= v4.2.0, `$data` peut utiliser l'objet [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), soutenant leSend de divers types de trames

    * **`int $opcode`**
      * **Fonction** : Type d'opération
      * **Valeur par défaut** : `WEBSOCKET_OPCODE_TEXT`
      * **Autres valeurs** : Aucun

      !> `$opcode` doit être un code OPCode `WebSocket` valide, sinon il retournera un échec et affichera un message d'erreur `opcode max 10`

    * **`int|bool $finish`**
      * **Fonction** : Type d'opération
      * **Valeur par défaut** : `SWOOLE_WEBSOCKET_FLAG_FIN`
      * **Autres valeurs** : Aucun

      !> À partir de la version v4.4.12, le paramètre `finish` (de type bool) a été remplacé par `flags` (de type int) pour prendre en charge la compression `WebSocket`, où `finish` correspond à la valeur `1` de `SWOOLE_WEBSOCKET_FLAG_FIN`, et la valeur booléenne originale sera implicitement convertie en type int, cette modification est compatible avec les versions inférieures sans impact. De plus, le drapeau de compression est `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

  * **Valeurs de retour**

    * Retourne `true` en cas d'envoi réussi
    * Retourne `false` en cas de connexion不存在、déjà fermée ou de `WebSocket` non terminé, échouant lors de l'envoi

  * **Codes d'erreur**


Code d'erreur | Description
---|---
8502 | Code OPCode erroné
8503 | Non connecté au serveur ou connexion déjà fermée
8504 | Échec du handshake


### recv()

Réception de messages. Seulement utilisé pour `WebSocket`, doit être utilisé avec `upgrade()`, voir exemple

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Ce paramètre est valide uniquement lorsque l'appel à `upgrade()` est effectué pour passer en mode `WebSocket` connexion
      * **Unité de valeur** : seconde【Prend en charge les浮点数, comme `1.5` représente `1s`+`500ms`】
      * **Valeur par défaut** : Voir [règles de timeout du client](/coroutine_client/init?id=timeout_règles)
      * **Autres valeurs** : Aucun

      !> Pour设置了ur timeout, privilégier le paramètre spécifié, sinon utiliser le `timeout` configuration passé dans la méthode `set`
  
  * **Valeurs de retour**

    * Retourne un objet frame en cas d'exécution réussie
    * Retourne `false` en cas d'échec, et vérifier la propriété `errCode` de `Swoole\Coroutine\Http\Client`, si le client coroutine n'a pas de callback `onClose`, la connexion est fermée lors de la réception de recv et errCode=0
 
  * **Exemple**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```


### download()

Télécharge un fichier via HTTP.

!> La différence entre download et get est que download écrit les données reçues sur le disque, plutôt que de拼接HTTP Body dans la mémoire. Par conséquent, download utilise seulement une petite quantité de mémoire pour pouvoir télécharger des fichiers très grands.

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename,  int $offset = 0): bool
```

  * **Paramètres** 

    * **`string $path`**
      * **Fonction** : Définir le chemin `URL`
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $filename`**
      * **Fonction** : Désigner le chemin du fichier où écrire le contenu téléchargé【Écrit automatiquement dans la propriété `downloadFile`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $offset`**
      * **Fonction** : Désigner l'offset d'écriture dans le fichier【Cette option peut être utilisée pour prendre en charge la reprise de transfert, en utilisant avec la tête HTTP `Range:bytes=$offset`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

      !> Si `$offset` est `0`, le fichier existant sera automatiquement effacé par le système

  * **Valeurs de retour**

    * Retourne `true` en cas d'exécution réussie
    * Retourne `false` en cas d'échec de l'ouverture du fichier ou d'échec de la fonction `fseek()` du système sous-jacent, et vérifiez la propriété `errCode` de `Swoole\Coroutine\Http\Client`, si le client coroutine n'a pas de callback `onClose`, la connexion est fermée lors de la réception de recv et errCode=0
 
  * **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```


### getCookies()

Obtenir le contenu des `cookie` de la réponse HTTP.

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> Les informations Cookie seront décodées en urldecode, si vous souhaitez obtenir les informations Cookie d'origine veuillez les analyser vous-même selon la documentation ci-dessous

#### Obtenir des `Cookie` de même nom ou des informations de tête originale des `Cookie`

```php
var_dump($client->set_cookie_headers);
```


### getHeaders()

Retourne les informations de tête de la réponse HTTP.

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```


### getStatusCode()

Obtenir le code d'état de la réponse HTTP.

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **Avertissement**

    * **Si le code d'état est négatif, cela indique qu'il y a un problème de connexion.**


Code d'état | Constante correspondante v4.2.10 et versions supérieures | Description

---|---|---

-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | Connexion超时, le serveur n'écoute pas sur le port ou la connexion est perdue, vous pouvez lire `$errCode` pour obtenir le code d'erreur réseau spécifique

-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | Demande超时, le serveur ne répond pas dans le délai `timeout` spécifié

-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | Après que la demande du client a été envoyée, le serveur coupe la connexion de force
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | Échec de l'envoi du client (cette constante est disponible à partir de la version Swoole `v4.5.9`, pour les versions inférieures veuillez utiliser le code d'état)


### getBody()

Obtenir le contenu du corps de la réponse HTTP.

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```


### close()

Ferme la connexion.

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> Après avoir appelé `close`, si vous effectuez à nouveau des demandes de méthodes telles que `get` ou `post`, Swoole se chargera de se reconnecter au serveur pour vous.


### execute()

Méthode HTTP plus bas niveau, nécessitant d'appeler des interfaces telles que [setMethod](/coroutine_client/http_client?id=setmethod) et [setData](/coroutine_client/http_client?id=setdata) dans le code pour configurer la méthode et les données de la demande.

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **Exemple**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```
## Fonctions

Afin de faciliter l'utilisation de `Coroutine\Http\Client`, trois fonctions ont été ajoutées :

!> La version Swoole doit être >= `v4.6.4` pour être disponible


### request()

Lancer une demande avec un méthode de demande spécifiée.

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```


### post()

Utilisé pour lancer une demande `POST`.

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```


### get()

Utilisé pour lancer une demande `GET`.

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```

### Exemple d'utilisation

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
