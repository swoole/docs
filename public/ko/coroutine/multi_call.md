# 병렬 호출

[//]: # (
setDefer 특성을 제거했습니다. setDefer를 지원하는 고객들은 모두 일괄 코루틴화를 권장합니다.
)

코루틴(go) + 채널(channel)을 사용하여 병렬한 요청을 실행합니다.

!>먼저 [개요](/coroutine)를 보시고 코루틴의 기본 개념을 이해한 후 이 섹션을 본다.

### 구현 원리

* `onRequest`에서 두 개의 HTTP 요청을 병렬로 수행해야 합니다. go 함수를 사용하여 2개의 서브 코루틴을 생성하고 여러 URL을 병렬로 요청합니다.
* 채널을 생성하고, use 클로저 참조 문법을 사용하여 서브 코루틴에 전달합니다.
* 메인 코루틴은 채널에서 데이터를 꺼내기를 반복하며, 서브 코루틴이 작업을 완료하면 yield하여 일시정지 상태로 들어갑니다.
* 병렬로 실행 중인 두 서브 코루틴 중 하나가 요청을 완료하면, 채널에서 데이터를 푸시하여 메인 코루틴에 전달합니다.
* 서브 코루틴이 URL 요청을 완료하면 종료하고, 메인 코루틴은 일시정지 상태에서 복귀하여 다음 호출 `$resp->end`을 수행하여 응답 결과를 보냅니다.

### 사용 예제

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

!> Swoole에서 제공하는 [WaitGroup](/coroutine/wait_group) 기능을 사용하면 더 간단합니다.
