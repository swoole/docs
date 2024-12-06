# Serveur HTTP

?> Implementations de serveur HTTP entièrement coroutinées, `Co\Http\Server` est écrit en C++ en raison des performances de parsing HTTP, et n'est donc pas une sous-classe du [Co\Server](/coroutine/server) écrit en PHP.

Différences avec [Http\Server](/http_server) :

* Permet de créer et de détruire dynamiquement des connections en temps réel
* Le traitement des connexions se fait dans des sous-coroutines séparées, les événements `Connect`, `Request`, `Response`, `Close` des connexions clients sont entièrement séquentiels

!> Nécessite la version `v4.4.0` ou supérieure

!> Si la compilation [active le protocole HTTP2](/environment?id=options_de_compilation), le support du protocole HTTP2 est activé par défaut, il n'est pas nécessaire de configurer [open_http2_protocol](/http_server?id=open_http2_protocol) comme pour `Swoole\Http\Server` (note : les versions inférieures à `v4.4.16` présentent des bugs connus dans le support HTTP2, veuillez mettre à niveau avant utilisation)


## Nom abrégé

Utilisez le nom abrégé `Co\Http\Server`.


## Méthodes


### __construct()

```php
Swoole\Coroutine\Http\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Paramètres** 

    * **`string $host`**
      * **Fonction** : IP d'écoute【si c'est une UNIX Socket locale, l'adresse doit être填写 sous la forme `unix://tmp/your_file.sock`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $port`**
      * **Fonction** : Port d'écoute 
      * **Valeur par défaut** : 0 (écoute un port libre aléatoire)
      * **Autres valeurs** : 0~65535

    * **`bool $ssl`**
      * **Fonction** : Activer ou non le tunnel de cryptage `SSL/TLS`
      * **Valeur par défaut** : false
      * **Autres valeurs** : true
      
    * **`bool $reuse_port`**
      * **Fonction** : Activer ou non la caractéristique de réutilisation du port, une fois activée, plusieurs services peuvent partager le même port
      * **Valeur par défaut** : false
      * **Autres valeurs** : true


### handle()

Enregister une fonction de rappel pour traiter les demandes HTTP sous la path indiquée par le paramètre `$pattern`.

```php
Swoole\Coroutine\Http\Server->handle(string $pattern, callable $fn): void
```

!> Doit être défini avant [Server::start](/coroutine/server?id=start)

  * **Paramètres** 

    * **`string $pattern`**
      * **Fonction** : Régler la path `URL` 【comme `/index.html`, notez que l'on ne peut pas entrer `http://domain`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`callable $fn`**
      * **Fonction** : Fonction de traitement, voir la référence de [OnRequest](/http_server?id=on) dans `Swoole\Http\Server`, nous ne l'expliquerons pas ici
      * **Valeur par défaut** : None
      * **Autres valeurs** : None      

      Exemple :

      ```php
      function callback(Swoole\Http\Request $req, Swoole\Http\Response $resp) {
          $resp->end("bonjour le monde");
      }
      ```

  * **Astuce**

    * Après une connexion `Accept` (établissement de la connexion) réussie, le serveur crée automatiquement une coroutine et accepte les demandes `HTTP`
    * `$fn` est exécuté dans un espace de coroutines séparé, donc il n'est pas nécessaire de créer de nouvelles coroutines à l'intérieur de la fonction
    * Les clients prennent en charge [KeepAlive](/coroutine_client/http_client?id=keep_alive), les coroutines continueront à accepter de nouvelles demandes en boucle sans quitter
    * Si les clients ne prennent pas en charge `KeepAlive`, les coroutines cesseront d'accepter des demandes et se termineront pour fermer la connexion

  * **Note**

    !> - Lorsque `$pattern` est défini avec la même path, la nouvelle configuration remplace l'ancienne ;  
    - Si aucune fonction de traitement n'est définie pour la root path et que la demande ne trouve aucune correspondance avec `$pattern`, Swoole retournera une erreur `404` ;  
    - `$pattern` utilise une méthode de correspondance de chaînes, ne prend pas en charge les wildcard et les expressions régulières, est insensible à la casse, l'algorithme de correspondance est une correspondance de préfixe, par exemple : si l'url est `/test111`, cela correspondra à la règle `/test`, une fois matché, il quittera la correspondance et ignorera les configurations suivantes ;  
    - Il est recommandé de définir une fonction de traitement pour la root path et d'utiliser `$request->server['request_uri']` dans la fonction de rappel pour la redirection des demandes.


### start()

?> **Démarrer le serveur.** 

```php
Swoole\Coroutine\Http\Server->start();
```


### shutdown()

?> **Arrêter le serveur.** 

```php
Swoole\Coroutine\Http\Server->shutdown();
```

## Exemple complet

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
```
