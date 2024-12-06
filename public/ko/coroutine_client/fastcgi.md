# 코루틴 FastCGI 클라이언트

PHP-FPM은 효율적인 이진 프로토콜인 `FastCGI 프로토콜`을 사용하여 통신합니다. FastCGI 클라이언트를 통해 PHP-FPM 서비스를 직접 대화할 수 있으므로 어떤 HTTP 리버스 프록시도 필요하지 않습니다.

[PHP 원본 소스 디렉터리](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)

## 간단한 사용 예제

[더 많은 예제 코드](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> 다음 예제 코드는 코루틴에서 호출해야 합니다.

### 빠른 호출

```php
#greeter.php
echo 'Hello ' . ($_POST['who'] ?? 'World');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // FPM监听地址, UnixSocket 주소로도 가능합니다.
    '/tmp/greeter.php', // 실행하고자 하는 진입 파일
    ['who' => 'Swoole'] // 추가 POST 정보
);
```

### PSR 스타일

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1:9000', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withScriptFilename(__DIR__ . '/greeter.php')
        ->withMethod('POST')
        ->withBody(['who' => 'Swoole']);
    $response = $client->execute($request);
    echo "Result: {$response->getBody()}\n";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### 복잡한 호출

```php
#var.php
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
```

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withDocumentRoot(__DIR__)
        ->withScriptFilename(__DIR__ . '/var.php')
        ->withScriptName('var.php')
        ->withMethod('POST')
        ->withUri('/var?foo=bar&bar=char')
        ->withHeader('X-Foo', 'bar')
        ->withHeader('X-Bar', 'char')
        ->withBody(['foo' => 'bar', 'bar' => 'char']);
    $response = $client->execute($request);
    echo "Result: \n{$response->getBody()}";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### 단일 클릭으로 WordPress 프록시

!> 이 용도는 생산에 의미가 없으며, 생산 환경에서는 프록시를 사용하여 일부 구 API 인터페이스의 HTTP 요청을 구 FPM 서비스로 프록시할 수 있습니다 (전체를 프록시하는 것이 아닙니다).

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # WordPress 프로젝트 루트 디렉터리
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # 여기 포트는 WordPress 설정과 일치해야 합니다. 일반적으로 포트를 특별히 지정하지 않으며, 대부분 80입니다.
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], # 정적 자원 경로
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # 프록시 객체를 만듭니다.
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # 단일 클릭으로 요청을 프록시합니다.
});
$server->start();
```

## 방법

### call

정적 방법으로, 새로운 클라이언트 연결을 직접 생성하고 FPM 서버에 요청을 보내 응답 본문을 수신합니다.

!> FPM은 단기 연결만 지원하기 때문에 일반적으로 지속 가능한 객체를 만드는 것은 큰 의미가 없습니다.

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **매개변수** 

    * **`string $url`**
      * **기능**: FPM监听地址【例如`127.0.0.1:9000`、`unix:/tmp/php-cgi.sock`等】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $path`**
      * **기능**: 실행하고자 하는 진입 파일
      * **기본값**: 없음
      * **기타값**: 없음

    * **`$data`**
      * **기능**: 추가의 요청 데이터
      * **기본값**: 없음
      * **기타값**: 없음

    * **`float $timeout`**
      * **기능**:超时 시간 설정【기본적으로 `-1`로 무제한】
      * **값 단위**: 초【소수형 지원, 예: `1.5`는 `1초+500ms`를 의미합니다】
      * **기본값**: `-1`
      * **기타값**: 없음

  * **반환값** 

    * 서버의 응답 본문 내용(body)을 반환합니다.
    * 오류 발생 시 `Swoole\Coroutine\FastCGI\Client\Exception` 예외가 발생합니다.


### __construct

클라이언트 객체의 생성자, 대상 FPM 서버를 지정합니다.

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **매개변수** 

    * **`string $host`**
      * **기능**: 대상 서버의 주소【예: `127.0.0.1`、`unix://tmp/php-fpm.sock` 등】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`int $port`**
      * **기능**: 대상 서버 포트【UNIXSocket 대상일 경우는 전달할 필요가 없습니다】
      * **기본값**: 없음
      * **기타값**: 없음


### execute

요청을 실행하고 응답을 반환합니다.

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **매개변수** 

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **기능**: 요청 정보를 포함하는 객체, 일반적으로 `Swoole\FastCGI\HttpRequest`를 사용하여 HTTP 요청을 모방하고, 특별한 요구가 있을 경우에만 FPM 프로토콜의 원시 요청 클래스 `Swoole\FastCGI\Request`를 사용할 수 있습니다.
      * **기본값**: 없음
      * **기타값**: 없음

    * **`float $timeout`**
      * **기능**:超时 시간 설정【기본적으로 `-1`로 무제한】
      * **값 단위**: 초【소수형 지원, 예: `1.5`는 `1초+500ms`를 의미합니다】
      * **기본값**: `-1`
      * **기타값**: 없음

  * **반환값** 

    * 요청 객체와 동일한 유형의 Response 객체를 반환합니다. 예를 들어 `Swoole\FastCGI\HttpRequest`는 `Swoole\FastCGI\HttpResponse` 객체를 반환하며, 이는 FPM 서버의 응답 정보를 포함합니다.
    * 오류 발생 시 `Swoole\Coroutine\FastCGI\Client\Exception` 예외가 발생합니다.

## 관련 요청/응답 클래스

library이 PSR의 거대한 의존을 도입할 수 없고 확장 로딩이 항상 PHP 코드 실행 전에 이루어지기 때문에 관련된 요청 응답 객체는 PSR 인터페이스를 상속하지 않았지만, PSR 스타일로 구현하여 개발자가 빠르게 사용할 수 있도록 노력하고 있습니다.

FastCGI로 HTTP 요청 응답을 모방하는 클래스의 관련 원본 소스 주소는 다음과 같으며, 매우 간단하며 코드는 문서입니다:

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
