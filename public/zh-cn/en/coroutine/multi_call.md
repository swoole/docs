# Concurrent Calls

[//]: # (
The `setDefer` feature has been removed here, as clients that support `setDefer` are recommended to use one-click coroutine processing.
)

Using `sub-coroutines (go)` + `channels` to implement concurrent requests.

It is recommended to first read the [Overview](/coroutine) to understand the basic concepts of coroutines before reading this section.

### Implementation Principle

- In `onRequest`, two `HTTP` requests need to be made concurrently, which can be achieved by creating `2` sub-coroutines using the `go` function to concurrently request multiple URLs.
- A `channel` is created and passed to sub-coroutines using the `use` closure reference syntax.
- The main coroutine loops calling `chan->pop`, waiting for the sub-coroutines to complete their tasks, `yielding` into a suspended state.
- When one of the two concurrent sub-coroutines completes the request, it calls `chan->push` to push the data to the main coroutine.
- After a sub-coroutine completes the `URL` request, it exits, and the main coroutine resumes from the suspended state, continuing to execute and calling `$resp->end` to send the response.

### Usage Example

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

It is simpler to use `Swoole`'s provided [WaitGroup](/coroutine/wait_group) functionality.
