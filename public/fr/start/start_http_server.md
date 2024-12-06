# Serveur HTTP


## Code du programme

Veuillez écrire le code suivant dans httpServer.php.

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Bonjour Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

Le serveur `HTTP` ne doit se concentrer que sur la réponse aux demandes, il suffit donc d'écouter un événement [onRequest](/http_server?id=on). Lorsque de nouvelles demandes `HTTP` entrent, cet événement est déclenché. La fonction de rappel de l'événement a deux paramètres : le premier est un objet `$request` qui contient des informations sur la demande, telles que les données de la demande `GET/POST`.

Le deuxième est un objet `$response` qui permet de répondre à la demande en manipulant l'objet `$response`. La méthode `$response->end()` signifie envoyer un contenu `HTML` et terminer la demande.

* `'0.0.0.0'` signifie écouter tous les adresses IP, un serveur peut avoir plusieurs IP à la fois, comme `127.0.0.1` l'adresse locale de retournement, `192.168.1.100` une IP de réseau local, `210.127.20.2` une IP extérieure, ici on peut également specifier expressément d'écouter une seule IP
* `9501` est le port d'écoute, si ce port est occupé, le programme lancera une erreur fatale et arrêtera l'exécution.


## Déclenchement du service

```shell
php httpServer.php
```
* Vous pouvez ouvrir un navigateur et visiter `http://127.0.0.1:9501` pour voir le résultat du programme.
* Vous pouvez également utiliser la tool Apache `ab` pour effectuer des tests de charge sur le serveur.


## Question des deux demandes Chrome

En utilisant le navigateur Chrome pour accéder au serveur, une demande supplémentaire est générée, `/favicon.ico`, vous pouvez répondre à cette demande par un code `404` dans le code.

```php
$http->on('Request', function ($request, $response) {
	if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
	}
    var_dump($request->get, $request->post);
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Bonjour Swoole. #' . rand(1000, 9999) . '</h1>');
});
```

## Routing des URL

L'application peut réaliser le routing des URLs en fonction de `$request->server['request_uri']`. Par exemple : `http://127.0.0.1:9501/test/index/?a=1`, le code peut réaliser le routing des URLs comme suit.

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	//MAP $controller, $action à différents classes et méthodes de contrôleur.
	(new $controller)->$action($request, $response);
});
```
