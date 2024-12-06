# 코루틴\Http2\클라이언트

코루틴 Http2 클라이언트


## 사용 예제

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $domain = 'www.zhihu.com';
    $cli = new Client($domain, 443, true);
    $cli->set([
        'timeout' => -1,
        'ssl_host_name' => $domain
    ]);
    $cli->connect();
    $req = new Request();
    $req->method = 'POST';
    $req->path = '/api/v4/answers/300000000/voters';
    $req->headers = [
        'host' => $domain,
        'user-agent' => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip'
    ];
    $req->data = '{"type":"up"}';
    $cli->send($req);
    $response = $cli->recv();
    var_dump(assert(json_decode($response->data)->error->code === 10002));
});
```


## 방법


### __construct()

생성자 방법.

```php
Swoole\Coroutine\Http2\Client::__construct(string $host, int $port, bool $open_ssl = false): void
```

  * **매개변수** 

    * **`string $host`**
      * **기능** : 대상 호스트의 IP 주소 【 `$host`이 도메인 이름이라면 `DNS` 조회가进行一次 】
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $port`**
      * **기능** : 대상 포트 【 `Http`는 일반적으로 `80`포트, `Https`는 일반적으로 `443`포트 】
      * **기본값** : 없음
      * **기타값** : 없음

    * **`bool $open_ssl`**
      * **기능** : `TLS/SSL` 터널 암호화 사용 여부 【 `https` 웹사이트는 반드시 `true`로 설정해야 함 】
      * **기본값** : `false`
      * **기타값** : `true`

  * **주의**

    !> -외부 네트워크 URL에 대한 요청을 할 경우 `timeout`을 더 큰 값으로 변경해야 합니다. 자세한 내용은 [클라이언트超时 규칙](/coroutine_client/init?id=超时规则)을 참고하세요.  
    - `$ssl`는 `openssl`에 의존하며, `Swoole`编译 시 [--enable-openssl](/environment?id=编译选项) 옵션을 활성화해야 합니다.


### set()

클라이언트 매개변수를 설정합니다. 자세한 설정 항목은 [Swoole\Client::set](/client?id=配置)을 참고하세요.

```php
Swoole\Coroutine\Http2\Client->set(array $options): void
```


### connect()

대상 서버에 연결합니다. 이 방법은 어떠한 매개변수도 없습니다.

!> `connect`를 호출하면, 내부적으로 자동으로 [코루틴 스케줄러](/coroutine?id=协程调度)가 진행되며, 연결이 성공하거나 실패할 때 `connect`가 반환됩니다. 연결이 완료되면 `send` 메서드를 통해 서버에 요청을 보낼 수 있습니다.

```php
Swoole\Coroutine\Http2\Client->connect(): bool
```

  * **반환값**

    * 성공 시 `true` 반환
    * 실패 시 `false` 반환하며, `errCode` 속성을 통해 오류 코드를 확인할 수 있습니다.


### stats()

스트림 상태를 가져옵니다.

```php
Swoole\Coroutine\Http2\Client->stats([$key]): array|bool
```

  * **예제**

```php
var_dump($client->stats(), $client->stats()['local_settings'], $client->stats('local_settings'));
```


### isStreamExist()

지정된 스트림이 존재하는지를 확인합니다.

```php
Swoole\Coroutine\Http2\Client->isStreamExist(int $stream_id): bool
```


### send()

서버에 요청을 보냅니다. 내부적으로 자동으로 `Http2`의 `stream`이 생성됩니다. 동시에 여러 개의 요청을 보낼 수 있습니다.

```php
Swoole\Coroutine\Http2\Client->send(Swoole\Http2\Request $request): int|false
```

  * **매개변수** 

    * **`Swoole\Http2\Request $request`**
      * **기능** : Swoole\Http2\Request 객체를 보냅니다.
      * **기본값** : 없음
      * **기타값** : 없음

  * **반환값**

    * 성공 시 스트림 번호를 반환하며, 번호는 `1`부터 시작하는 홀수로 증가합니다.
    * 실패 시 `false` 반환

  * **주의**

    * **Request 객체**

      !> `Swoole\Http2\Request` 객체에는 어떠한 메서드도 없습니다. 객체 속성을 설정하여 요청 관련 정보를 작성합니다.

      * `headers` 배열, `HTTP` 헤더
      * `method` 문자열, 요청 방법을 설정합니다. 예: `GET`, `POST`
      * `path` 문자열, `URL` 경로를 설정합니다. 예: `/index.php?a=1&b=2`는 시작字符 `/`가 반드시 필요합니다.
      * `cookies` 배열, `COOKIES`를 설정합니다.
      * `data` 요청의 `body`을 설정합니다. 문자열이면 직접 `RAW form-data`로 전송됩니다.
      * `data`가 배열이면, 내부적으로 자동으로 `x-www-form-urlencoded` 형식의 `POST` 내용을 포장하고 `Content-Type`을 `application/x-www-form-urlencoded`로 설정합니다.
      * `pipeline` 布尔형, `true`로 설정하면 `$request`를 보낸 후에도 `stream`을 닫지 않고, 데이터 프레임을 계속해서 작성할 수 있습니다.

    * **pipeline**

      * 기본적으로 `send` 메서드는 요청을 보낸 후 현재의 `Http2 Stream`을 종료합니다. `pipeline`를 활성화하면, 내부적으로 `stream`을 유지하고, 여러 번의 `write` 메서드를 호출하여 서버에 데이터 프레임을 보낼 수 있습니다. 자세한 내용은 `write` 메서드를 참고하세요.


### write()

서버에 더 많은 데이터 프레임을 보냅니다. 동일한 `stream`에 여러 번의 `write`를 호출하여 데이터 프레임을 작성할 수 있습니다.

```php
Swoole\Coroutine\Http2\Client->write(int $streamId, mixed $data, bool $end = false): bool
```

  * **매개변수** 

    * **`int $streamId`**
      * **기능** : 스트림 번호, `send` 메서드에서 반환됩니다.
      * **기본값** : 없음
      * **기타값** : 없음

    * **`mixed $data`**
      * **기능** : 데이터 프레임의 내용, 문자열이나 배열일 수 있습니다.
      * **기본값** : 없음
      * **기타값** : 없음

    * **`bool $end`**
      * **기능** : 스트림을 종료할지 여부
      * **기본값** : `false`
      * **기타값** : `true`

  * **사용 예제**

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9518);
    $cli->set(['timeout' => 1]);
    var_dump($cli->connect());

    $req3 = new Request();
    $req3->path = "/index.php";
    $req3->headers = [
        'host' => "localhost",
        "user-agent" => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip',
    ];
    $req3->pipeline = true;
    $req3->method = "POST";
    $streamId = $cli->send($req3);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    //end stream
    $cli->write($streamId, ['int' => rand(1000, 9999), 'end' => true], true);
    var_dump($cli->recv());
    $cli->close();
});
```

!> `write`를 사용하여 데이터 프레임을 분할 보내면, `send` 요청 시 `$request->pipeline`를 `true`로 설정해야 합니다.  
`end`가 `true`인 데이터 프레임을 보낸 후에는 스트림이 닫히며, 이후에는 해당 `stream`에 대한 `write`를 더 이상 호출할 수 없습니다.


### recv()

요청을 수신합니다.

!> 이 메서드를 호출하면 [코루틴 스케줄러](/coroutine?id=协程调度)가 발생합니다.

```php
Swoole\Coroutine\Http2\Client->recv(float $timeout): Swoole\Http2\Response;
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능** :超时 시간을 설정합니다. 자세한 내용은 [클라이언트超时 규칙](/coroutine_client/init?id=超时规则)을 참고하세요.
      * **값의 단위** : 초 【소수점이 지원됩니다. 예: `1.5`는 `1초`+`500ms`을 의미합니다】
      * **기본값** : 없음
      * **기타값** : 없음

  * **반환값**

    * 성공 시 Swoole\Http2\Response 객체를 반환합니다.

```php
/**@var $resp Swoole\Http2\Response */
var_dump($resp->statusCode); // 서버가 보낸 Http 상태코드, 예: 200, 502 등
var_dump($resp->headers); // 서버가 보낸 헤더 정보
var_dump($resp->cookies); // 서버가 설정한 COOKIE 정보
var_dump($resp->set_cookie_headers); // 서버에서 반환한 원시 COOKIE 정보, domain과 path 항목이 포함됩니다.
var_dump($resp->data); // 서버가 보낸 응답 바디
```

!> Swoole 버전이 [v4.0.4](/version/bc?id=_404) 이전의 경우, `data` 속성은 `body` 속성입니다; Swoole 버전이 [v4.0.3](/version/bc?id=_403) 이전의 경우, `headers`와 `cookies`는 단수 형식입니다.
### read()

`recv()`와 기본적으로 같으나, `pipeline` 타입의 응답에 대해서는 `read`가 여러 번에 나누어서 읽을 수 있으며, 메모리를 절약하거나 푸시 정보를 더 빨리 받으려면 부분적인 내용을 읽을 수 있습니다. 반면에 `recv`는 모든 프레임을 하나의 완전한 응답으로 합치기 전에 반환하지 않습니다.

!> 이 메서드를 호출하면 [코루outine 스케줄러](/coroutine?id=코루outine%E1%84%80%E1%83%92)가 발생합니다.

```php
Swoole\Coroutine\Http2\Client->read(float $timeout): Swoole\Http2\Response;
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능**:超时 시간을 설정합니다. [클라이언트超时 규칙](/coroutine_client/init?id=%EC%9D%B4%EC%8A%A4%E8%B3%87%E5%AE%9A) 참조
      * **값의 단위**: 초【소수형을 지원합니다. 예: `1.5`는 `1초`+`500밀리초`를 나타냅니다】
      * **기본값**: 없음
      * **기타 값**: 없음

  * **반환값**

    성공 시 Swoole\Http2\Response 객체를 반환합니다.


### goaway()

GOAWAY 프레임은 연결 종료를 시작하거나 심각한 오류 상태 신호를 보낼 때 사용됩니다.

```php
Swoole\Coroutine\Http2\Client->goaway(int $error_code = SWOOLE_HTTP2_ERROR_NO_ERROR, string $debug_data): bool
```


### ping()

PING 프레임은 발신처에서 최소 왕복 시간을 측정하고, 빈 연결이 여전히 유효한지를 결정하는 메커니즘입니다.

```php
Swoole\Coroutine\Http2\Client->ping(): bool
```

### close()

연결을 종료합니다.

```php
Swoole\Coroutine\Http2\Client->close(): bool
```
