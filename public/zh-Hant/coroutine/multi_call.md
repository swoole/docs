# 並發調用

[//]: # (
此处刪除了setDefer特性，因為支持setDefer的客戶端都推薦用一鍵協程化了。
)

使用`子協程(go)`+`通道(channel)`實現並發請求。

!>建議先看[概覽](/coroutine)，了解協程基本概念再看此節。

### 實現原理

* 在`onRequest`中需要並發兩個`HTTP`請求，可使用`go`函數創建`2`個子協程，並發地請求多個`URL`
* 並創建了一個`channel`，使用`use`閉包引用語法，傳遞給子協程
* 主協程循環調用`chan->pop`，等待子協程完成任務，`yield`進入挂起狀態
* 並發的兩個子協程其中某個完成請求時，調用`chan->push`將數據推送給主協程
* 子協程完成`URL`請求后退出，主協程從挂起狀態中恢復，繼續向下執行調用`$resp->end`發送響應結果

### 使用示例

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

!> 使用`Swoole`提供的[WaitGroup](/coroutine/wait_group)功能，將更簡單一些。
