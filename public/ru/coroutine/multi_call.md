# Синхронные вызовы

[//]: # (
В данном случае функция `setDefer` удалена, поскольку клиенты, поддерживающие `setDefer`, рекомендуется использовать однокорутную синхронизацию.
)

Для реализации синхронных запросов используется комбинация `корутин(go)` и `канал(channel)`.

!> Рекомендуется сначала посмотреть [Обзор](/coroutine), чтобы понять основные концепции корутин, прежде чем читать этот раздел.

### Принцип работы

* В `onRequest` необходимо одновременно отправить два HTTP-запроса, для чего можно использовать функцию `go` для создания двух дочерних корутин, которые будут одновременно отправлять запросы на различные URL
* Создается канал `channel`, который передается дочерним корутинам с использованием замыкания через `use`
* Основной корутин в цикле вызывает `chan->pop`, ожидая завершения задач дочерними корутинами, `yield` вступает в состояние ожидания
* Когда одна из двух параллельных дочерних корутин завершает запрос, она отправляет данные на основной корутин с помощью `chan->push`
* После завершения запроса на URL дочерняя корутин завершает работу, основной корутин восстанавливается из состояния ожидания и продолжает выполнять следующие вызовы `$resp->end`, отправляя результаты ответа

### Пример использования

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

!> Для упрощения использования можно использовать функцию [WaitGroup](/coroutine/wait_group), предоставляемую `Swoole`.
