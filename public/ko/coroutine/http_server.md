# HTTP 서버

?> 완전히 코루틴 기반의 HTTP 서버 구현으로, `Co\Http\Server`은 HTTP 해석 성능을 위해 C++로 작성되어 PHP가 작성한 [Co\Server](/coroutine/server)의 서브클래스가 아닙니다.

[Http\Server](/http_server)와의 차이점:

* 실행 중에 동적으로 생성 및 파괴 가능
* 연결 처리는 별도의 서브 코루틴에서 완료되며, 클라이언트 연결의 `Connect`, `Request`, `Response`, `Close`는 완전히 직렬화됩니다.

!> `v4.4.0` 이상 버전을 필요로 합니다.

!>编译時に [HTTP2启用](/environment?id=编译选项)이 설정되어 있다면, HTTP2 프로토콜 지원이 기본적으로 적용되며, `Swoole\Http\Server`처럼 [open_http2_protocol](/http_server?id=open_http2_protocol)을 설정할 필요가 없습니다. (주의: **v4.4.16 이전 버전의 HTTP2 지원에는 알려진 버그가 존재합니다. 업데이트 후 사용해 주십시오**)


## 짧은 이름

`Co\Http\Server`의 짧은 이름을 사용할 수 있습니다.


## 방법


### __construct()

```php
Swoole\Coroutine\Http\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **매개변수** 

    * **`string $host`**
      * **기능**: 청취하는 IP 주소 【로컬 UNIX 소켓이면 `unix://tmp/your_file.sock`와 같은 형식으로 작성해야 합니다】
      * **기본값**: 없음
      * **기타 값**: 없음

    * **`int $port`**
      * **기능**: 청취하는 포트 
      * **기본값**: 0 (임의의 여유 포트를 청취)
      * **기타 값**: 0~65535

    * **`bool $ssl`**
      * **기능**: `SSL/TLS` 터널 암호화 사용 여부
      * **기본값**: false
      * **기타 값**: true
      
    * **`bool $reuse_port`**
      * **기능**: 포트 재사용 기능 사용 여부,开启后可以 여러 서비스가 동일한 포트를 공유
      * **기본값**: false
      * **기타 값**: true


### handle()

매개변수 `$pattern`에 지정된 경로의 HTTP 요청을 처리하기 위한 콜백 함수를 등록합니다.

```php
Swoole\Coroutine\Http\Server->handle(string $pattern, callable $fn): void
```

!> [Server::start](/coroutine/server?id=start) 전에 처리 함수를 설정해야 합니다.

  * **매개변수** 

    * **`string $pattern`**
      * **기능**: `URL` 경로 설정 【예: `/index.html`, 여기서는 `http://domain`를 전달할 수 없습니다】
      * **기본값**: 없음
      * **기타 값**: 없음

    * **`callable $fn`**
      * **기능**: 처리 함수, `Swoole\Http\Server`의 [OnRequest](/http_server?id=on) 콜백을 참고하여 사용하며, 여기서 다시 설명하지 않겠습니다.
      * **기본값**: 없음
      * **기타 값**: 없음      

      예시:

      ```php
      function callback(Swoole\Http\Request $req, Swoole\Http\Response $resp) {
          $resp->end("hello world");
      }
      ```

  * **알림**

    * 서버는 `Accept`(연결 구축) 성공 후 자동으로 코루틴을 생성하고 `HTTP` 요청을 수락합니다
    * `$fn`은 새로운 서브 코루틴 공간에서 실행되므로, 함수 내에서 코루틴을 다시 생성할 필요가 없습니다
    * 클라이언트는 [KeepAlive](/coroutine_client/http_client?id=keep_alive)를 지원하며, 서브 코루틴은 새로운 요청을 수락하며 끊임없이 반복됩니다.
    * 클라이언트가 `KeepAlive`를 지원하지 않으면, 서브 코루틴은 요청 수락을 중단하고 연결을 닫습니다.

  * **주의**

    !> -`$pattern`가 동일한 경로를 설정할 경우, 새로운 설정은 기존 설정을 덮어냅니다;  
    - `/根路径` 처리 함수가 설정되지 않았으며 요청의 경로에 일치하는 `$pattern`이 찾히지 않으면 Swoole는 `404` 오류를 반환합니다;  
    - `$pattern`은 문자열 매칭 방식으로 사용되며, 대괄호와 정규 표현식의 지원이 없으며, 대소문자를 구분하지 않고, 매칭 알고리즘은 전치 매칭입니다. 예를 들어, url이 `/test111`인 경우 `/test` 규칙에 매치하며, 매치되면 이후의 설정을 무시합니다;  
    - `/根路径` 처리 함수를 설정하고, 콜백 함수에서 `$request->server['request_uri']`를 사용하여 요청을 라우팅하는 것이 좋습니다.


### start()

?> **서버 시작.** 

```php
Swoole\Coroutine\Http\Server->start();
```


### shutdown()

?> **서버 종료.** 

```php
Swoole\Coroutine\Http\Server->shutdown();
```

## 전체 예시

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
```
