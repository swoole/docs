# Http\Server

?> `Http\Server` hérite de [Server](/server/init), donc toutes les `API` et les paramètres de configuration offerts par `Server` peuvent être utilisés, et le modèle de processus est également le même. Veuillez consulter la section [Server](/server/init).

Le support du serveur HTTP intégré permet d'écrire un serveur HTTP multiprocessus à haute concurrency et haute performance avec [IO asynchrone](/learn?id=io-asynchrone) en quelques lignes de code.

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Bonjour Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

En utilisant l'outil `Apache bench` pour des tests de charge, sur un ordinateur ordinaire avec un processeur Intel Core i5 à 4 coeurs et 8 Go de RAM, le `Http\Server` peut atteindre près de `110 000 QPS`.

Il dépasse de loin les serveurs HTTP intégrés de `PHP-FPM`, `Golang` et `Node.js`. Sa performance est presque comparable à celle de `Nginx` pour le traitement des fichiers statiques.

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **Utilisation du protocole HTTP/2**

  * Pour utiliser le protocole HTTP/2 sous SSL, il est nécessaire d'installer `openssl`, et une version élevée d'openssl doit supporter TLS1.2, ALPN, NPN
  * Lors de la compilation, il faut utiliser l'option [--enable-http2](/environment?id=options-de-compilation) pour l'activer
  * À partir de Swoole 5, le protocole HTTP/2 est activé par défaut

```shell
./configure --enable-openssl --enable-http2
```

Définissez [open_http2_protocol](/http_server?id=open_http2_protocol) du serveur HTTP à `true`

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Configuration Nginx + Swoole**

!> Étant donné que le support du serveur HTTP par `Http\Server` n'est pas complet, il est conseillé d'utiliser ce serveur uniquement comme serveur d'applications pour traiter les demandes dynamiques, et d'ajouter `Nginx` en tant que proxy en avant.

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> Il est possible d'obtenir l'adresse IP réelle du client en lisant `$request->header['x-real-ip']`


## Méthodes


### on()

?> **Enregister une fonction de rappel pour un événement.**

?> Similar à la [rappel d'événement de Server](/server/events), à la différence que :

  * `Http\Server->on` ne prend pas en charge les settings de rappel pour [onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)
  * `Http\Server->on` prend en charge un nouveau type d'événement, `onRequest`, qui est déclenché pour chaque demande envoyée par le client

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

Après avoir reçu une demande HTTP complète, cette fonction est appelée en retour. La fonction de rappel a deux paramètres :

* [Swoole\Http\Request](/http_server?id=httpRequest), objet contenant les informations de la demande HTTP, y compris `header/get/post/cookie`, etc.
* [Swoole\Http\Response](/http_server?id=httpResponse), objet de réponse HTTP, supportant des opérations HTTP telles que `cookie/header/status`

!> Lorsque la fonction de rappel [onRequest](/http_server?id=on) est retournée, les objets `$request` et `$response` sont détruits en dessous du niveau


### start()

?> **Démarrer le serveur HTTP**

?> Après le démarrage, le serveur commence à écouter le port et à recevoir de nouvelles demandes HTTP.

```php
Swoole\Http\Server->start();
```


## Swoole\Http\Request

Objet de demande HTTP, qui conserve les informations relatives à la demande HTTP envoyée par le client, y compris `GET`, `POST`, `COOKIE`, `Header`, etc.

!> Veuillez ne pas utiliser le symbole `&` pour faire référence à l'objet `Http\Request`


### header

?> **Les informations de tête de la demande HTTP. De type tableau, toutes les `clés` sont en minuscule. **

```php
Swoole\Http\Request->header: array
```

* **Exemple**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```


### server

?> **Les informations du serveur liées à la demande HTTP.**

?> Similaire à l'array `$_SERVER` en PHP. Il contient les méthodes de demande HTTP, l'URI de la route, l'adresse IP du client, etc.

```php
Swoole\Http\Request->server: array
```

Les clés de l'array sont toutes en minuscule et sont cohérentes avec l'array `$_SERVER` en PHP

* **Exemple**

```php
echo $request->server['request_time'];
```


Clé | Description
---|---
query_string | Les paramètres `GET` de la demande, par exemple : `id=1&cid=2` Si il n'y a pas de paramètres `GET`, cette entrée n'existe pas
request_method | La méthode de demande, `GET/POST`, etc.
request_uri | L'adresse d'accès sans paramètres `GET`, par exemple `/favicon.ico`
path_info | Idem que `request_uri`
request_time | `request_time` est défini par le `Worker`, et dans le mode [SWOOLE_PROCESS](/learn?id=swoole_process), il existe un processus de `dispatch`, donc il peut y avoir un décalage par rapport au temps réel de réception des paquets. Cela est particulièrement vrai lorsque le volume des demandes dépasse la capacité de traitement du serveur, le `request_time` peut être bien plus tardif que le temps réel de réception des paquets. Il est possible d'obtenir l'heure exacte de réception des paquets en utilisant la méthode `$server->getClientInfo` pour obtenir `last_time`.
request_time_float | Le timestamp du début de la demande, en microsecondes, de type `float`, par exemple `1576220199.2725`
server_protocol | La version du protocole du serveur, pour HTTP : `HTTP/1.0` ou `HTTP/1.1`, pour HTTP2 : `HTTP/2`
server_port | Le port sur lequel le serveur écoute
remote_port | Le port du client
remote_addr | L'adresse IP du client
master_time | Le temps de la dernière communication avec le maître


### get

?> **Les paramètres `GET` de la demande HTTP, équivalents à `$_GET` en PHP, sous forme d'array. **

```php
Swoole\Http\Request->get: array
```

* **Exemple**

```php
// Comme : index.php?hello=123
echo $request->get['hello'];
// Obtenir tous les paramètres GET
var_dump($request->get);
```

* **Note**

!> Afin de prévenir les attaques par hachage, la taille maximale des paramètres `GET` n'est pas autorisée à dépasser `128`


### post

?> **Les paramètres `POST` de la demande HTTP, sous forme d'array**

```php
Swoole\Http\Request->post: array
```

* **Exemple**

```php
echo $request->post['hello'];
```

* **Note**


!> - La taille totale des `POST` et des `Header` ne doit pas dépasser la valeur de [package_max_length](/server/setting?id=package_max_length) définie, sinon cela sera considéré comme une demande malveillante  
- Le nombre maximal de paramètres `POST` ne doit pas dépasser `128`


### cookie

?> **Les informations de `COOKIE` transportées dans la demande HTTP, sous forme d'array de paires clé-valeur. **

```php
Swoole\Http\Request->cookie: array
```

* **Exemple**

```php
echo $request->cookie['username'];
```


### files

?> **Les informations des fichiers téléchargés. **

?> De type tableau bidimensionnel dont la clé est le nom du formulaire. Similaire à `$_FILES` en PHP. La taille maximale du fichier ne doit pas dépasser la valeur de [package_max_length](/server/setting?id=package_max_length) définie. Comme Swoole occupe de la mémoire lors de l'analyse des messages, plus le message est grand, plus la mémoire utilisée est importante, donc veuillez ne pas utiliser `Swoole\Http\Server` pour traiter le téléchargement de gros fichiers ou concevoir une fonction de reprise de transmission par les utilisateurs eux-mêmes.

```php
Swoole\Http\Request->files: array
```

* **Exemple**

```php
Array
(
    [name] => facepalm.jpg // Nom du fichier uploaded par le navigateur
    [type] => image/jpeg // Type MIME
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // Fichier temporaire téléchargé, nom de fichier commençant par /tmp/swoole.upfile
    [error] => 0
    [size] => 15476 // Taille du fichier
)
```

* **Note**

!> Lorsque l'objet `Swoole\Http\Request` est détruit, les fichiers temporaires téléchargés sont automatiquement supprimés


### getContent()

!> La version Swoole >= `v4.5.0` est disponible, pour les versions inférieures, utilisez l'alias `rawContent` (cet alias sera conservé de manière permanente, c'est-à-dire compatible avec les versions antérieures)

?> **Obtenir le corps du paquet `POST` original.**

?> Utilisé pour les demandes HTTP `POST` qui ne sont pas au format `application/x-www-form-urlencoded`. Renvoie les données `POST` originelles, cette fonction est équivalente à `fopen('php://input')` en PHP

```php
Swoole\Http\Request->getContent(): string|false
```

  * **Valeurs de retour**

    * Si l'exécution est réussie, retourne le message, sinon si la connexion de contexte n'existe pas, retourne `false`

!> Dans certains cas, le serveur n'a pas besoin d'analyser les paramètres de la demande HTTP `POST`. À travers la configuration de [http_parse_post](/http_server?id=http_parse_post), vous pouvez désactiver l'analyse des données `POST`.


### getData()

?> **Obtenir le message complet du paquet de demande HTTP original, en note que cela ne fonctionne pas sous Http2. Cela inclut les en-têtes HTTP et le corps du message.**

```php
Swoole\Http\Request->getData(): string|false
```

  * **Valeurs de retour**

    * Si l'exécution est réussie, retourne le message, sinon si la connexion de contexte n'existe pas ou si vous êtes en mode Http2, retourne `false`


### create()

?> **Créer un objet Swoole\Http\Request.**

!> La version Swoole >= `v4.6.0` est disponible

```php
Swoole\Http\Request->create(array $options): Swoole\Http\Request
```

  * **Paramètres**

    * **`array $options`**
      * **Fonction** : Paramètres optionnels pour configurer l'objet `Request`

| Paramètre                                          | Valeur par défaut | Description                                                         |
| ------------------------------------------------- | ------------------ | ------------------------------------------------------------------- |
| [parse_cookie](/http_server?id=http_parse_cookie) | true               | Établir si les cookies doivent être analysés                           |
| [parse_body](/http_server?id=http_parse_post)      | true               | Établir si le corps du message HTTP doit être analysé                 |
| [parse_files](/http_server?id=http_parse_files)   | true               | Établir si les fichiers téléchargés doivent être analysés              |
| enable_compression                                | true, si le serveur ne prend pas en charge la compression des messages, la valeur par défaut est false | Établir si la compression est activée                                   |
| compression_level                                 | 1                  | Niveau de compression, l'échelle va de 1 à 9, plus le niveau est élevé, plus la taille du message comprimé est petite, mais plus le CPU est occupé        |
| upload_tmp_dir                                 | /tmp               | Chemin de stockage des fichiers temporaires, utilisé pour les téléchargements de fichiers        |

  * **Valeurs de retour**

    * Retourne un objet Swoole\Http\Request

* **Exemple**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```


### parse()

?> **Analyser le paquet de données de demande HTTP, cela retournera la longueur du paquet de données analysé avec succès.**

!> La version Swoole >= `v4.6.0` est disponible

```php
Swoole\Http\Request->parse(string $data): int|false
```

  * **Paramètres**

    * **`string $data`**
      * Le paquet de données à analyser

  * **Valeurs de retour**

    * Si l'analyse est réussie, retourne la longueur du paquet de données analysé, sinon si la connexion de contexte n'existe pas ou si le contexte a déjà été terminé, retourne `false`


### isCompleted()

?> **Obtenir si le paquet de données de demande HTTP actuel est arrivé à la fin.**

!> La version Swoole >= `v4.6.0` est disponible

```php
Swoole\Http\Request->isCompleted(): bool
```

  * **Valeurs de retour**

    * `true` signifie que c'est déjà la fin, `false` signifie que le contexte de connexion est terminé ou que la fin du paquet n'est pas encore arrivée

* **Exemple**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n";
$data .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n";
$data .= "Accept-Encoding: gzip, deflate, br\r\n";
$data .= "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,ja;q=0.6\r\n";
$data .= "Cookie: env=pretest; phpsessid=fcccs2af8673a2f343a61a96551c8523d79ea; username=hantianfeng\r\n";

/** @var Request $req */
$req = Request::create(['parse_cookie' => false]);
var_dump($req);

var_dump($req->isCompleted());
var_dump($req->parse($data));

var_dump($req->parse("\r\n"));
var_dump($req->isCompleted());

var_dump($req);
// La cookie a été fermée, donc elle sera null
var_dump($req->cookie);
```


### getMethod()

?> **Obtenir la méthode de demande HTTP actuelle.**

!> La version Swoole >= `v4.6.2` est disponible

```php
Swoole\Http\Request->getMethod(): string|false
```
  * **Valeurs de retour**

    * Retourne la méthode de demande en majuscules, `false` signifie que le contexte de connexion n'existe pas

```php
var_dump($request->server['request_method']);
var_dump($request->getMethod());
```


## Swoole\Http\Response

L'objet de réponse HTTP, en appelant les méthodes de cet objet, vous pouvez envoyer des réponses HTTP.

?> Lorsque l'objet Response est détruit, si la méthode [end](/http_server?id=end) n'a pas été appelée pour envoyer la réponse HTTP, le niveau inférieur exécutera automatiquement `end("")`;

!> Veuillez ne pas utiliser le symbole `&` pour faire référence à l'objet Http\Response


### header() :id=setheader

?> **Définir les informations de tête HTTP de la réponse**【alias `setHeader`】

```php
Swoole\Http\Response->header(string $key, string $value, bool $format = true): bool;
```

* **Paramètres** 

  * **`string $key`**
    * **Fonction** : La clé de la tête HTTP
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`string $value`**
    * **Fonction** : La valeur de la tête HTTP
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`bool $format`**
    * **Fonction** : Indicatez s'il faut formater la clé selon les règles HTTP【par défaut `true`, la formatisation est automatique】
    * **Valeur par défaut** : `true`
    * **Autres valeurs** : Aucun

* **Valeurs de retour** 

  * Échec de la définition, retourne `false`
  * Succès de la définition, retourne `true`
* **Note**

   - La définition de `header` doit se faire avant la méthode `end`
   - `$key` doit strictement respecter les règles HTTP, chaque mot commence par une majuscule, ne contient pas de caractères chinois, d'underscore ou d'autres caractères spéciaux  
   - `$value` doit être fournie  
   - Si `$ucwords` est mis à `true`, le niveau inférieur formatera automatiquement la clé selon les règles HTTP  
   - La définition de la même clé HTTP répétée覆盖, la dernière défini sera prise  
   - Si le client a设置了`Accept-Encoding`, alors le serveur ne peut pas définir la réponse `Content-Length`, Swoole détectera cette situation et ignorera la valeur de `Content-Length`, et lancera une alerte   
   - Une fois que la réponse `Content-Length` est définie, il n'est pas possible d'appeler `Swoole\Http\Response::write()`, Swoole détectera cette situation et ignorera la valeur de `Content-Length`, et lancera une alerte

!> Lorsque la version Swoole est >= `v4.6.0`, il est possible de définir à plusieurs reprises la même clé HTTP, et la valeur `$value` prend en charge plusieurs types, tels que `array`, `object`, `int`, `float`, le niveau inférieur effectuera une conversion en `toString`, et éliminera les espaces vides et les sauts de ligne à la fin.

* **Exemple**

```php
$response->header('content-type', 'image/jpeg', true);

$response->header('Content-Length', '100002 ');
$response->header('Test-Value', [
    "a\r\n",
    'd5678',
    "e  \n ",
    null,
    5678,
    3.1415926,
]);
$response->header('Foo', new SplFileInfo('bar'));
```
### trail()

?> ** Ajoutez les informations de `Header` à la fin de la réponse HTTP, disponible uniquement en HTTP/2, utilisée pour les vérifications d'intégrité des messages, les signatures numériques, etc.**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **Paramètres** 

  * **`string $key`**
    * **Fonction** : Clé de la tête HTTP
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`string $value`**
    * **Fonction** : Valeur de la tête HTTP
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

* **Valeurs de retour** 

  * Échec de l'établissement, retourne `false`
  * Succès de l'établissement, retourne `true`

* **Remarque**

  !> La configuration répétée de la même clé `$key` de la tête HTTP remplacera la précédente.

* **Exemple**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```


### cookie()

?> **Setz les informations du cookie HTTP dans la réponse.** Synonyme `setCookie`. Les paramètres de cette méthode sont cohérents avec ceux de `setcookie` PHP.

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **Paramètres** 

    * **`string $key`**
      * **Fonction** : Clé du cookie
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $value`**
      * **Fonction** : Valeur du cookie
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun
  
    * **`int $expire`**
      * **Fonction** : Expiration du cookie
      * **Valeur par défaut** : 0, pas expiré
      * **Autres valeurs** : Aucun

    * **`string $path`**
      * **Fonction** : Définit le chemin de service du cookie.
      * **Valeur par défaut** : /
      * **Autres valeurs** : Aucun

    * **`string $domain`**
      * **Fonction** : Définit le domaine du cookie
      * **Valeur par défaut** : ''
      * **Autres valeurs** : Aucun

    * **`bool $secure`**
      * **Fonction** : Définit si le cookie doit être transmis via une connexion HTTPS sécurisée
      * **Valeur par défaut** : ''
      * **Autres valeurs** : Aucun

    * **`bool $httponly`**
      * **Fonction** : Permet ou non à la JavaScript du navigateur d'accéder aux cookies avec la propriété HttpOnly, `true` signifie interdit, `false` signifie autorisé
      * **Valeur par défaut** : false
      * **Autres valeurs** : Aucun

    * **`string $samesite`**
      * **Fonction** : Limite les cookies tiers pour réduire les risques de sécurité, les valeurs possibles sont `Strict`, `Lax`, `None`
      * **Valeur par défaut** : ''
      * **Autres valeurs** : Aucun

    * **`string $priority`**
      * **Fonction** : Priorité du cookie, lorsque le nombre de cookies dépasse la limite spécifiée, les cookies à faible priorité seront supprimés en premier
      * **Valeur par défaut** : ''
      * **Autres valeurs** : Aucun
  
  * **Valeurs de retour** 

    * Échec de l'établissement, retourne `false`
    * Succès de l'établissement, retourne `true`

* **Remarque**

  !> - La configuration du `cookie` doit être effectuée avant la méthode [end](/http_server?id=end)  
  - Le paramètre `$samesite` est pris en charge à partir de la version `v4.4.6`, et le paramètre `$priority` est pris en charge à partir de la version `v4.5.8`  
  - Swoole effectuera automatiquement l'encodage URL de `$value`, mais vous pouvez utiliser la méthode `rawCookie()` pour désactiver l'encodage de `$value`  
  - Swoole permet de configurer plusieurs cookies avec la même `$key`


### rawCookie()

?> **Setz les informations du cookie HTTP dans la réponse**

!> Les paramètres de la méthode `rawCookie()` sont les mêmes que ceux de la méthode précédente `cookie()`, sauf qu'ils ne sont pas encodés.


### status()

?> **Envoie un code d'état HTTP. Synonyme `setStatusCode()`**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **Paramètres** 

  * **`int $http_status_code`**
    * **Fonction** : Établissez le `HttpCode`
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`string $reason`**
    * **Fonction** : Raison du code d'état
    * **Valeur par défaut** : ''
    * **Autres valeurs** : Aucun

  * **Valeurs de retour** 

    * Échec de l'établissement, retourne `false`
    * Succès de l'établissement, retourne `true`

* **Aide**

  * Si vous ne passez que le premier paramètre `$http_status_code`, il doit être un code d'état HTTP valide, comme `200`, `502`, `301`, `404`, etc., sinon il sera établi sur le code d'état `200`.
  * Si vous passez le deuxième paramètre `$reason`, `$http_status_code` peut être n'importe quelle valeur numérique, y compris des codes d'état HTTP non définis, comme `499`.
  * La méthode `status` doit être exécutée avant d'envoyer du contenu avec [$response->end()](/http_server?id=end).


### gzip()

!> Cette méthode a été dépréciée dans la version `4.1.0` ou plus récente, veuillez vous référer à [http_compression](/http_server?id=http_compression) ; dans les nouvelles versions, l'option de configuration `http_compression` remplace la méthode `gzip`.  
La raison principale est que la méthode `gzip()` ne vérifie pas la tête `Accept-Encoding` envoyée par le client browser. Si le client ne prend pas en charge la compression gzip, son utilisation forcée peut entraîner une décompression impossible par le client.  
La nouvelle option de configuration `http_compression` choisira automatiquement s'il faut compresser en fonction de la tête `Accept-Encoding` du client, et choisira automatiquement l'algorithme de compression le plus efficace.

?> **Activer la compression HTTP GZIP. La compression peut réduire la taille du contenu HTML, économiser efficacement la bande passante réseau et améliorer le temps de réponse.** Il faut exécuter `gzip` avant d'envoyer du contenu avec `write/end`, sinon une erreur sera levée. **
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **Paramètres** 
   
     * **`int $level`**
       * **Fonction** : Niveau de compression, plus le niveau est élevé, plus la taille du contenu comprimé est petite, mais plus la consommation de CPU est élevée.
       * **Valeur par défaut** : 1
       * **Autres valeurs** : `1-9`

!> Après avoir appelé la méthode `gzip`, la couche inférieure ajoutera automatiquement la tête HTTP de compression, et il ne faut pas configurer à nouveau les têtes HTTP pertinentes dans le code PHP ; les images au format jpg/png/gif sont déjà compressées et n'ont pas besoin d'être comprimées à nouveau

!> La fonction `gzip` dépend de la bibliothèque `zlib`, qui est détectée automatiquement par Swoole lors de la compilation. Si la bibliothèque `zlib` n'est pas présente sur le système, la méthode `gzip` ne sera pas disponible. Vous pouvez installer la bibliothèque `zlib` en utilisant `yum` ou `apt-get` :

```shell
sudo apt-get install libz-dev
```


### redirect()

?> **Envoie une redirection HTTP. En appelant cette méthode, l'envoi et la fin de la réponse sont automatiquement terminés.**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **Paramètres** 
* **Paramètres** 
  * **Paramètres** 
  * **Paramètres** 

    * **`string $url`**
      * **Fonction** : nouvelle adresse de redirection, envoyée comme tête `Location`
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $http_code`**
      * **Fonction** : code d'état【par défaut `302` pour une redirection temporaire, passez `301` pour une redirection permanente】
      * **Valeur par défaut** : `302`
      * **Autres valeurs** : Aucun

  * **Valeurs de retour** 

    * appel réussi, retourne `true`, appel échoué ou contexte de connexion inexistant, retourne `false`

* **Exemple**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```
### Écrire()

?> **Activer la segmentation HTTP pour envoyer le contenu correspondant au navigateur.**

?> Pour plus d'informations sur la segmentation HTTP, veuillez consulter la documentation standard du protocole HTTP.

```php
Swoole\Http\Response->write(string $data): bool
```

  * **Paramètres** 

    * **`string $data`**
      * **Fonction** : Contenu à envoyer【La longueur maximale ne doit pas dépasser `2M`, contrôlée par l'option de configuration [buffer_output_size](/server/setting?id=buffer_output_size)】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeurs de retour** 
  
    * Succès de la fonction, retourne `true`, échec de la fonction ou contexte de connexion不存在, retourne `false`

* **Avis**

  * Après avoir utilisé la méthode `write` pour envoyer des données par segments, la méthode [end](/http_server?id=end) ne acceptera aucun paramètre. Appeler `end` ne fera que发送un segment de `Chunk` de longueur `0`, indiquant la fin du transfert de données.
  * Si la méthode `Swoole\Http\Response::header()` a déjà établi la `Content-Length`, puis que cette méthode est appelée, Swoole ignorera l'établissement de `Content-Length` et lancera une alerte.
  * La fonction HTTP/2 ne peut pas utiliser cette fonction, sinon une alerte sera lancée.
  * Si le client prend en charge la compression de réponse, `Swoole\Http\Response::write()` forcera l'ouverture de la compression.


### envoyer_fichier()

?> **Envoyer un fichier au navigateur.**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **Paramètres** 

    * **`string $filename`**
      * **Fonction** : Nom du fichier à envoyer【Si le fichier n'existe pas ou que l'utilisateur n'a pas les droits d'accès, `sendfile` échouera】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $offset`**
      * **Fonction** : Offsetting du fichier à envoyer【Il est possible de spécifier le début du transfert de données à partir de la partie médiane du fichier. Cette caractéristique peut être utilisée pour prendre en charge la reprise de transfert】
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : None

    * **`int $length`**
      * **Fonction** : Taille des données à envoyer
      * **Valeur par défaut** : Taille du fichier
      * **Autres valeurs** : None

  * **Valeurs de retour** 

      * Succès de la fonction, retourne `true`, échec de la fonction ou contexte de connexion不存在, retourne `false`

* **Avis**

  * Le niveau inférieur ne peut pas déduire le format MIME du fichier à envoyer donc il est nécessaire que l'application code spécifie `Content-Type`
  * Avant d'appeler `sendfile`, il ne faut pas utiliser la méthode `write` pour envoyer des `Http-Chunk`
  * Après avoir appelé `sendfile`, le niveau inférieur exécutera automatiquement `end`
  * `sendfile` ne prend pas en charge la compression `gzip`

* **Exemple**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```


### fin()

?> **Envoyer le corps de la réponse HTTP et terminer le traitement de la demande.**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **Paramètres** 
  
    * **`string $html`**
      * **Fonction** : Contenu à envoyer
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Succès de la fonction, retourne `true`, échec de la fonction ou contexte de connexion不存在, retourne `false`

* **Avis**

  * La méthode `end` ne peut être appelée qu'une seule fois. Si vous devez envoyer des données au client plusieurs fois, veuillez utiliser la méthode [write](/http_server?id=write)
  * Si le client a activé [KeepAlive](/coroutine_client/http_client?id=keep_alive), la connexion sera maintenue et le serveur attendra la prochaine demande
  * Si le client n'a pas activé `KeepAlive`, le serveur coupera la connexion
  * Le contenu à envoyer avec `end` est limité par la taille de l'output buffer, qui est par défaut de `2M`. Si elle dépasse cette limite, la réponse échouera et l'erreur suivante sera lancée :

!> La solution consiste à utiliser [sendfile](/http_server?id=sendfile), [write](/http_server?id=write) ou ajuster la taille de l'output buffer [output_buffer_size](/server/setting?id=buffer_output_size)

```bash
WARNING finish (ERRNO 1203): La longueur des données [262144] dépasse la taille de l'output buffer [131072], veuillez utiliser sendfile, mode de transfert en blocs ou ajuster la taille de l'output_buffer_size
```


### détacher()

?> **Séparer l'objet de réponse.** Après avoir utilisé cette méthode, l'objet `$response` ne sera pas détruit automatiquement par [end](/http_server?id=httpresponse), utilisé en combinaison avec [Http\Response::create](/http_server?id=create) et [Server->send](/server/methods?id=send).

```php
Swoole\Http\Response->detach(): bool
```

  * **Valeurs de retour** 

    * Succès de la fonction, retourne `true`, échec de la fonction ou contexte de connexion不存在, retourne `false`

* **Exemple** 

  * **Réponse à travers les processus**

  ?> Dans certains cas, il est nécessaire de répondre au client dans un [Processus Task](/learn?id=taskworker进程). Dans ce cas, on peut utiliser `detach` pour rendre l'objet `$response` indépendant. Dans le [Processus Task](/learn?id=taskworker进程), on peut reconstruire `$response`, lancer une demande HTTP et répondre. 

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->set(['task_worker_num' => 1, 'worker_num' => 1]);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->task(strval($resp->fd));
  });

  $http->on('finish', function () {
      echo "task finish";
  });

  $http->on('task', function ($serv, $task_id, $worker_id, $data) {
      var_dump($data);
      $resp = Swoole\Http\Response::create($data);
      $resp->end("in task");
      echo "async task\n";
  });

  $http->start();
  ```

  * **Envoi de contenu quelconque**

  ?> Dans certains cas spéciaux, il est nécessaire d'envoyer un contenu de réponse spécial au client. La méthode `end` intégrée de l'objet `Http\Response` ne peut pas répondre aux besoins, on peut utiliser `detach` pour séparer l'objet de réponse, puis assembler manuellement les données de réponse du protocole HTTP et utiliser `Server->send` pour envoyer les données.

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```


### créer()

?> **Crée un nouvel objet `Swoole\Http\Response`.**

!> Veuillez utiliser cette méthode avant tout d'appeler la méthode `detach` pour séparer l'ancien objet `$response`, sinon il est possible de envoyer deux fois le contenu de réponse à la même demande.

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

  * **Paramètres** 

    * **`int $server`**
      * **Fonction** : Objet `Swoole\Server` ou `Swoole\Coroutine\Socket`, tableau (le tableau ne peut avoir que deux paramètres, le premier est un objet `Swoole\Server`, le deuxième est un objet `Swoole\Http\Request`), ou des descriptors de fichiers
      * **Valeur par défaut** : -1
      * **Autres valeurs** : None

    * **`int $fd`**
      * **Fonction** : Descriptor de fichier. Si le paramètre `$server` est un objet `Swoole\Server`, `$fd` est obligatoire
      * **Valeur par défaut** : -1
      * 
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Succès de la fonction, retourne un nouvel objet `Swoole\Http\Response`, échec de la fonction, retourne `false`

* **Exemple**

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->detach();
    // Exemple 1
    $resp2 = Swoole\Http\Response::create($req->fd);
    // Exemple 2
    $resp2 = Swoole\Http\Response::create($http, $req->fd);
    // Exemple 3
    $resp2 = Swoole\Http\Response::create([$http, $req]);
    // Exemple 4
    $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, IPPROTO_IP);
    $socket->connect('127.0.0.1', 9501)
    $resp2 = Swoole\Http\Response::create($socket);
    $resp2->end("hello world");
});

$http->start();
```
### estWritable()

?> **Déterminer si l'objet `Swoole\Http\Response` a été terminé (`end`) ou détaché (`detach`).**

```php
Swoole\Http\Response->estWritable(): bool
```

  * **Valeur de retour** 

    * Si l'objet `Swoole\Http\Response` n'a pas été terminé ou détaché, retourne `true`, sinon retourne `false`


!> Disponible pour les versions de Swoole >= `v4.6.0`

* **Exemple**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->estWritable()); // true
    $resp->end('bonjour');
    var_dump($resp->estWritable()); // false
    $resp->setStatusCode(403); // la réponse HTTP est indisponible (peut-être qu'elle a été terminée ou détachée)
});

$http->start();
```


## Options de configuration


### http_parse_cookie

?> **Configuration pour l'objet `Swoole\Http\Request` : désactivez la parsing des `Cookies` et conserverez les informations originales des `Cookies` non traitées dans les `header`. Par défaut, activé**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```


### http_parse_post

?> **Configuration pour l'objet `Swoole\Http\Request` : activez ou désactivez la parsing des messages POST. Par défaut, activé**

* Lorsque mis à `true`, le corps du message avec `Content-Type: application/x-www-form-urlencoded` est automatiquement analysé et ajouté à l'array `POST`.
* Lorsque mis à `false`, la parsing POST est désactivée.

```php
$server->set([
    'http_parse_post' => false,
]);
```


### http_parse_files

?> **Configuration pour l'objet `Swoole\Http\Request` : activez ou désactivez la parsing des fichiers téléchargés. Par défaut, activé**

```php
$server->set([
    'http_parse_files' => false,
]);
```


### http_compression

?> **Configuration pour l'objet `Swoole\Http\Response` : activez la compression. Par défaut, activé.**


!> - La compression par chunks HTTP ne prend pas en charge la compression séparée par segment. Si vous utilisez la méthode [write](/http_server?id=write), la compression sera forcée de se désactiver.  
- La configuration `http_compression` est disponible à partir de la version `v4.1.0`.

```php
$server->set([
    'http_compression' => false,
]);
```

Actuellement, les formats de compression supportés sont `gzip`, `br` et `deflate`. Le choix de la méthode de compression est automatiquement fait en fonction de l'en-tête `Accept-Encoding` envoyé par le client browser (priorité des algorithmes de compression : `br` > `gzip` > `deflate`).

**Dependencies :**

Pour `gzip` et `deflate`, la bibliothèque `zlib` est nécessaire. Lors de la compilation de Swoole, il est vérifié si la bibliothèque `zlib` est présente dans le système.

Vous pouvez installer la bibliothèque `zlib` avec `yum` ou `apt-get` :

```shell
sudo apt-get install libz-dev
```

Pour le format de compression `br`, qui dépend de la bibliothèque `brotli` de Google, veuillez rechercher comment `install brotli on linux`. Lors de la compilation de Swoole, il est vérifié si la bibliothèque `brotli` est présente dans le système.


### http_compression_level / compression_level / http_gzip_level

?> **Niveau de compression, configuration pour l'objet `Swoole\Http\Response`**
  
!> `$level` Niveau de compression, l'échelle va de `1` à `9`, plus le niveau est élevé, plus la taille du fichier comprimé est petite, mais plus le CPU est consommé. Par défaut, `1`, maximum `9`



### http_compression_min_length / compression_min_length

?> **Valeur minimale pour activer la compression, configuration pour l'objet `Swoole\Http\Response`**
  
!> Swoole version >= `v4.6.3` Disponible

```php
$server->set([
    'compression_min_length' => 128,
]);
```


### upload_tmp_dir

?> **Dossier temporaire pour les fichiers téléchargés. La longueur maximale du dossier ne doit pas dépasser `220` caractères**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```


### upload_max_filesize

?> **Valeur maximale pour les fichiers téléchargés**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```


### enable_static_handler

Activer la fonction de traitement des demandes de fichiers statiques, à utiliser en combinaison avec `document_root`. Par défaut, désactivé



### http_autoindex

Activer la fonction `http autoindex`. Par défaut, désactivé


### http_index_files

Utilisé avec `http_autoindex`, pour indiquer la liste des fichiers à indexer

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```


### http_compression_types / compression_types

?> **Types de réponse à compresser, configuration pour l'objet `Swoole\Http\Response`**

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Disponible pour les versions de Swoole >= `v4.8.12`



### static_handler_locations

?> **Définit les chemins des gestionnaires statiques. Type d'array, par défaut désactivé.**

!> Disponible pour les versions de Swoole >= `v4.4.0`

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* Similaire à la directive `location` de Nginx, permet d'indiquer un ou plusieurs chemins comme chemins statiques. Le gestionnaire de fichiers statiques est activé uniquement si l'URL correspondant se trouve dans les chemins spécifiés, sinon il est considéré comme une demande dynamique.
* Chaque élément de `location` doit commencer par `/`
* Prend en charge les chemins multigrades, comme `/app/images`
* Après avoir activé `static_handler_locations`, si le fichier demandé n'existe pas, un code d'erreur 404 est immédiatement retourné


### open_http2_protocol

?> **Activer la parsing du protocole HTTP/2**【Valeur par défaut : `false`】

!> Pour activer, il est nécessaire de compiler avec l'option [--enable-http2](/environment?id=compilation_options), et à partir de Swoole 5, HTTP/2 est activé par défaut lors de la compilation.


### document_root

?> **Définit le répertoire racine des fichiers statiques, utilisé en combinaison avec `enable_static_handler`.** 

!> Cette fonction est assez simple, veuillez ne pas l'utiliser directement dans un environnement public

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // Pour les versions inférieures à v4.4.0, ce chemin doit être absolu
    'enable_static_handler' => true,
]);
```

* Après avoir défini `document_root` et avoir activé `enable_static_handler`, lorsque Swoole reçoit une demande HTTP, il vérifie d'abord si le fichier demandé existe dans le chemin `document_root`. Si le fichier existe, il envoie directement le contenu du fichier au client sans déclencher la回调 [onRequest](/http_server?id=onRequest).
* Lors de l'utilisation des fonctionnalités de traitement des fichiers statiques, il est important de séparer les codes PHP dynamiques des fichiers statiques, et de placer les fichiers statiques dans des répertoires spécifiques.


### max_concurrency

?> ** Limite la quantité maximale de demandes en parallèle pour les services HTTP/1/2, au-delà de laquelle une erreur `503` est retournée. La valeur par défaut est 4294967295, soit la valeur maximale d'un entier sans signe.**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```


### worker_max_concurrency

?> **Après avoir activé la coroutining, les processus `worker` continuent d'accepter des demandes. Afin d'éviter une surcharge excessive, nous pouvons configurer `worker_max_concurrency` pour limiter le nombre de demandes exécutées par processus `worker`. Lorsque le nombre de demandes dépasse cette valeur, les processus `worker` en stockent le surplus dans une file d'attente. La valeur par défaut est 4294967295, soit la valeur maximale d'un entier sans signe. Si `worker_max_concurrency` n'est pas configuré, mais que `max_concurrency` est défini, Swoole将自动 configurer `worker_max_concurrency` à la valeur de `max_concurrency`.**

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Disponible pour les versions de Swoole >= `v5.0.0`
### http2_header_table_size

?> Définissez la taille maximale de la `table d'headers` pour les connexions HTTP/2.

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```


### http2_enable_push

?> Cette configuration est utilisée pour activer ou désactiver le push HTTP/2.

```php
$server->set([
  'http2_enable_push' => 0x2
])
```


### http2_max_concurrent_streams

?> Établissez le nombre maximal de streams multiples acceptés par chaque connexion HTTP/2.

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```


### http2_init_window_size

?> Établissez la taille initiale de la fenêtre de contrôle du trafic HTTP/2.

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```


### http2_max_frame_size

?> Établissez la taille maximale du corps du cadre HTTP/2 unique envoyé via la connexion HTTP/2.

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```

### http2_max_header_list_size

?> Établissez la taille maximale des en-têtes qui peuvent être envoyées dans une demande sur un flux HTTP/2.

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
