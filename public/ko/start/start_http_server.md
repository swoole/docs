# HTTP 서버


## 프로그램 코드

다음 코드를 httpServer.php에 작성하세요.

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

`HTTP` 서버는 요청 응답만 신경 써야 하기 때문에 [onRequest](/http_server?id=on) 이벤트만 감시하면 됩니다. 새로운 `HTTP` 요청이 들어올 때마다 이 이벤트가 트리거됩니다. 이벤트 콜백 함수는 `2` 개의 매개변수를 가지고 있습니다. 하나는 `$request` 객체로, 요청 관련 정보를 포함하고 있습니다. 예를 들어 `GET/POST` 요청의 데이터와 같은 것입니다.

다른 하나는 `$response` 객체로, `$request`에 대한 응답은 `$response` 객체를 조작하여 완료할 수 있습니다. `$response->end()` 메서드는 한 구절의 `HTML` 내용을 출력하고 이 요청을 종료합니다.

* `0.0.0.0`는 모든 `IP` 주소를 감시하겠다는 것을 나타내며, 한 서버는 동시에 여러 개의 `IP`를 가질 수 있습니다. 예를 들어 `127.0.0.1`는 로컬 호스트 IP, `192.168.1.100`는 로컬 네트워크 IP, `210.127.20.2`는 외부 네트워크 IP입니다. 여기서는 개별적으로 감시할 `IP`를 지정할 수도 있습니다.
* `9501`는 감시할 포트 번호로, 다른 프로그램이 사용하는 경우 치명적인 오류를 발생시켜 실행을 중단합니다.


## 서비스를 시작합니다

```shell
php httpServer.php
```
* 브라우저를 열어 `http://127.0.0.1:9501`에 방문하여 프로그램의 결과를 확인할 수 있습니다.
* 또한 Apache의 `ab` 도구를 사용하여 서버에 부하 테스트를 할 수 있습니다.


## Chrome 요청 두 번 문제

`Chrome` 브라우저를 사용하여 서버에 액세스하면 추가로 `/favicon.ico`라는 요청이 발생합니다. 이 경우 코드에서 `404` 오류를 응답할 수 있습니다.

```php
$http->on('Request', function ($request, $response) {
	if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
	}
    var_dump($request->get, $request->post);
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});
```

## URL 라우팅

앱은 `$request->server['request_uri']`를 사용하여 라우팅을 구현할 수 있습니다. 예를 들어 `http://127.0.0.1:9501/test/index/?a=1`와 같은 경우, 코드에서 이렇게 `URL` 라우팅을 구현할 수 있습니다.

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	// $controller, $action을 기반으로 다양한 컨트롤러 클래스와 방법에 매핑합니다.
	(new $controller)->$action($request, $response);
});
```
