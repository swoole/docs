# Konkurrierende Anrufe

[//]: # (
Die Eigenschaft `setDefer` wurde entfernt, da alle Client-Anwender, die `setDefer` unterstützen, empfohlen wird, mit einem Klick auf die Koordination zu erweitern.
)

Konkurrierende Anfragen werden mit `Unterkoordination (go)` + `Kanal (channel)` erreicht.

!> Es wird empfohlen, zuerst den [Übersicht](/coroutine) zu betrachten, um die grundlegenden Konzepte der Koordination zu verstehen, bevor Sie diesen Abschnitt lesen.


### Arbeitsprinzip

* In `onRequest` müssen zwei `HTTP`-Anfragen konkurrierend durchgeführt werden. Sie können die `go`-Funktion verwenden, um zwei Unterkoordinationen zu erstellen, die gleichzeitig mehrere `URLs` anfordern
* Ein `channel` wurde erstellt und mit der `use`-Klammereigenschaften-Syntax an die Unterkoordinationen übergeben
* Die Hauptk koordinierungscycle ruft `chan->pop` auf, wartet darauf, dass die Unterkoordinationen ihre Aufgaben beenden, und `yield` geht in einen Hängestaat über
* Wenn eine der konkurrierenden Unterkoordinationen ihre Anfrage beendet, wird mit `chan->push` die Daten an die Hauptk koordinierungscycle gepusht
* Nachdem die Unterkoordination die `URL`-Anfrage beendet hat, verlässt sie und die Hauptk koordinierungscycle kehrt aus dem Hängestaat zurück und setzt den Aufruf `$resp->end` fort, um das Antwortergebnis zu senden

### Beispiel für die Verwendung

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

!> Mit der von `Swoole` bereitgestellten [WaitGroup](/coroutine/wait_group)-Funktion ist es noch einfacher.
