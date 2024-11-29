## 并発呼び出し

[//]: # (
setDefer特性は削除されました。setDeferをサポートするクライアントは、一键协程化を推奨されています。
)

子协程(go)+チャネル(channel)を使用して並発リクエストを実現します。

!>まずは[概観](/coroutine)を見て、协程の基本概念を理解した後にこのセクションを読むことをお勧めします。

### 実現原理

* `onRequest`内で2つのHTTPリクエストを並発させるために、go関数を使用して2つの子协程を作成し、複数のURLに並発してリクエストを行います。
* チャネルを作成し、use闭包引用文法を使用して子协程に渡します。
* メインロoutineはchan->popを繰り返し呼び出し、子协程がタスクを完了するのを待ちます。yieldで挂起状態に入ります。
* 並発している2つの子协程のいずれかがリクエストを完了すると、chan->pushを使用してデータをメインロoutineにプッシュします。
* 子协程がURLリクエストを完了した後、退出し、メインロoutineは挂起状態から復帰し、以下のように呼び出されます$resp->endで応答結果を送信します。

### 使用例

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

!> Swooleが提供する[WaitGroup](/coroutine/wait_group)機能を使用すると、もっと簡単になります。
