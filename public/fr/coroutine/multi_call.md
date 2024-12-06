# Appels concurrents

[//]: # (
La caractéristique `setDefer` a été supprimée car les clients qui prennent en charge `setDefer` recommande tous l'utilisation de la coroutinisation en une seule commande.
)

Utilisez `sub-coroutine(go)` + `canal(channel)` pour réaliser des demandes concurrentielles.

!> Il est conseillé de consulter d'abord la [Vue d'ensemble](/coroutine) pour comprendre les concepts de base de la coroutine avant de lire cette section.


### Principe de réalisation

* Dans `onRequest`, il est nécessaire de lancer deux demandes HTTP en parallèle. Utilisez la fonction `go` pour créer 2 sous-coroutines qui demandent simultanément à plusieurs URLs
* Un canal a été créé et, en utilisant la syntaxe de référence de fermeture `use`, il a été transmis aux sous-coroutines
* La coroutine principale tourne en appelant `chan->pop` pour attendre que les sous-coroutines terminent leur travail, puis elle entre dans un état suspendu avec `yield`
* Lorsque l'une des deux sous-coroutines terminées effectue la demande, elle appelle `chan->push` pour envoyer les données à la coroutine principale
* Les sous-coroutines se terminent après avoir effectué la demande de URL et quittent, la coroutine principale reprend son état suspendu et continue à exécuter en appelant `$resp->end` pour envoyer le résultat de la réponse

### Exemple d'utilisation

```php
$serv = new Swoole\Http\Server("127.0.0.1", 9503, SWOOLE_BASE);

$serv->on('request', function ($req, $resp) {
	$chan = new Channel(2);
	go(function () use ($chan) {
		$cli = new Swoole\Coroutine\Http\Client('www.qq.com', 80);
			$cli->set(['timeout' => 10]);
			$cli->setHeaders([
			'Host' => "www.qq.com",
			"User-Agent" => 'Chrome/49.0.2587.3',
			'Accept' => 'text/html,application/xhtml+xml,application/xml',
			'Accept-Encoding' => 'gzip',
		]);
		$ret = $cli->get('/');
		$chan->push(['www.qq.com' => $cli->body]);
	});

	go(function () use ($chan) {
		$cli = new Swoole\Coroutine\Http\Client('www.163.com', 80);
		$cli->set(['timeout' => 10]);
		$cli->setHeaders([
			'Host' => "www.163.com",
			"User-Agent" => 'Chrome/49.0.2587.3',
			'Accept' => 'text/html,application/xhtml+xml,application/xml',
			'Accept-Encoding' => 'gzip',
		]);
		$ret = $cli->get('/');
		$chan->push(['www.163.com' => $cli->body]);
	});
	
	$result = [];
	for ($i = 0; $i < 2; $i++)
	{
		$result += $chan->pop();
	}
	$resp->end(json_encode($result));
});
$serv->start();
```

!> En utilisant la fonction [WaitGroup](/coroutine/wait_group) fournie par `Swoole`, cela sera beaucoup plus simple.
