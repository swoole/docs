# Client FastCGI Coroutine

PHP-FPM utilise un protocole binaire efficace : le `protocole FastCGI` pour communiquer. En utilisant un client FastCGI, il est possible d'interagir directement avec le service PHP-FPM sans passer par aucun proxy HTTP inversé.

[Dépôt source PHP](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)

## Exemple de utilisation simple

[Plus d'exemples de code](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> Les exemples de code suivants doivent être appelés en coroutines

### Appel rapide

```php
#greeter.php
echo 'Bonjour ' . ($_POST['qui'] ?? 'Monde');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // Adresse d'écoute FPM, peut également être une adresse UnixSocket comme unix:/tmp/php-cgi.sock
    '/tmp/greeter.php', // Fichier d'entrée à exécuter
    ['qui' => 'Swoole'] // Données POST attachées
);
```

### Style PSR

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1:9000', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withScriptFilename(__DIR__ . '/greeter.php')
        ->withMethod('POST')
        ->withBody(['qui' => 'Swoole']);
    $response = $client->execute($request);
    echo "Résultat : {$response->getBody()}\n";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Erreur : {$exception->getMessage()}\n";
}
```

### Appel complexe

```php
#var.php
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
```

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withDocumentRoot(__DIR__)
        ->withScriptFilename(__DIR__ . '/var.php')
        ->withScriptName('var.php')
        ->withMethod('POST')
        ->withUri('/var?foo=bar&bar=char')
        ->withHeader('X-Foo', 'bar')
        ->withHeader('X-Bar', 'char')
        ->withBody(['foo' => 'bar', 'bar' => 'char']);
    $response = $client->execute($request);
    echo "Résultat : \n{$response->getBody()}";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Erreur : {$exception->getMessage()}\n";
}
```

### Proxy WordPress en un clic

!> Cette utilisation n'a pas de sens productif, en production, le proxy peut être utilisé pour rediriger certaines demandes HTTP anciennes des API vers le vieux service FPM (plutôt que de proxyer toute la site)

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # Root du projet WordPress
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # Ici, le port doit être cohérent avec la configuration de WordPress, généralement pas spécifié spécifiquement, c'est 80
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], # Chemin des ressources statiques
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # Création d'un objet proxy
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # Proxying d'une demande en un clic
});
$server->start();
```

## Méthodes

### call

Méthode statique, crée directement une nouvelle connexion client, lance une demande au serveur FPM et reçoit le corps de la réponse

!> FPM ne prend en charge que les connexions courtes, donc généralement, il n'y a pas beaucoup de raison de créer des objets persistants

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **Paramètres** 

    * **`string $url`**
      * **Fonction** : Adresse d'écoute FPM【comme `127.0.0.1:9000`, `unix:/tmp/php-cgi.sock`, etc.】
      * **Valeur par défaut** : 无
      * **Autres valeurs** : 无

    * **`string $path`**
      * **Fonction** : Fichier d'entrée à exécuter
      * **Valeur par défaut** : 无
      * **Autres valeurs** : 无

    * **`$data`**
      * **Fonction** : Données de demande attachées
      * **Valeur par défaut** : 无
      * **Autres valeurs** : 无

    * **`float $timeout`**
      * **Fonction** :设置了urldélais【défaut -1 signifie jamais délais】
      * **Unité de valeur** : seconde【prendra en charge les nombres flottants, comme 1.5 signifie 1s+500ms】
      * **Valeur par défaut**：-1
      * **Autres valeurs** : 无

  * **Valeur de retour** 

    * Retourne le contenu principal(body) de la réponse du serveur
    * Une exception `Swoole\Coroutine\FastCGI\Client\Exception` sera lancée en cas d'erreur


### __construct

Constructeur de l'objet client, spécifiant le serveur FPM cible

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **Paramètres** 

    * **`string $host`**
      * **Fonction** : Adresse du serveur cible【comme `127.0.0.1`, `unix://tmp/php-fpm.sock`, etc.】
      * **Valeur par défaut** : 无
      * **Autres valeurs** : 无

    * **`int $port`**
      * **Fonction** : Port du serveur cible【pas besoin de passer si l'adresse est une UNIXSocket】
      * **Valeur par défaut** : 无
      * **Autres valeurs** : 无


### execute

Exécute la demande, retourne la réponse

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **Paramètres** 

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **Fonction** : Objet contenant les informations de demande, généralement utilisé `Swoole\FastCGI\HttpRequest` pour simuler une demande HTTP, utilisé `Swoole\FastCGI\Request` pour la demande originale du protocole FPM en cas de besoins spécifiques
      * **Valeur par défaut** : 无
      * **Autres valeurs** : 无

    * **`float $timeout`**
      * **Fonction** :设置了urldélais【défaut -1 signifie jamais délais】
      * **Unité de valeur** : seconde【prendra en charge les nombres flottants, comme 1.5 signifie 1s+500ms】
      * **Valeur par défaut**：-1
      * **Autres valeurs** : 无

  * **Valeur de retour** 

    * Retourne un objet Response correspondant à l'objet de demande, par exemple, `Swoole\FastCGI\HttpRequest` retournera un objet `Swoole\FastCGI\HttpResponse`, contenant les informations de réponse du serveur FPM
    * Une exception `Swoole\Coroutine\FastCGI\Client\Exception` sera lancée en cas d'erreur

## Classes de demande/réponse associées

Étant donné que la bibliothèque ne peut pas intégrer la dépendance庞大 de PSR et que le chargement de l'extension se fait toujours avant l'exécution du code PHP, les objets de demande et de réponse associés n'héritent pas des interfaces PSR, mais sont implémentés dans le style PSR afin que les développeurs puissent commencer à utiliser rapidement.

Les sources des classes de simulation de demande et de réponse FastCGI sont disponibles ci-dessous, très simples, le code est documenté :

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
