# 코루틴 HTTP/WebSocket 클라이언트

코루틴 버전의 `HTTP` 클라이언트는 순수한 `C`로 작성되어 있으며, 어떠한 제3자 확장 라이브러리에도 의존하지 않아 매우 높은 성능을 자랑합니다.

* `Http-Chunk`, `Keep-Alive` 기능을 지원하며, `form-data` 형식을 지원합니다.
* `HTTP` 프로토콜 버전은 `HTTP/1.1`입니다.
* `WebSocket` 클라이언트로 업그레이드할 수 있습니다.
* `gzip` 압축 형식은 `zlib` 라이브러리를 의존하여 지원됩니다.
* 클라이언트는 핵심 기능만 구현했으며, 실제 프로젝트에서는 [Saber](https://github.com/swlib/saber)를 사용하는 것이 권장됩니다.


## 속성


### errCode

오류 상태 코드입니다. `connect/send/recv/close`가 실패하거나 타임아웃될 경우 자동으로 `Swoole\Coroutine\Http\Client->errCode`의 값이 설정됩니다.

```php
Swoole\Coroutine\Http\Client->errCode: int
```

`errCode`의 값은 `Linux errno`와 동일합니다. 오류 코드를 오류 메시지로 변환하려면 `socket_strerror`를 사용할 수 있습니다.

```php
// connect가 거절될 경우, 오류 코드는 111
// 타임아웃될 경우, 오류 코드는 110
echo socket_strerror($client->errCode);
```

!> 참고: [Linux 오류 코드 목록](/other/errno?id=linux)


### body

마지막 요청의 응답 본체를 저장합니다.

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **예제**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```


### statusCode

HTTP 상태 코드, 예를 들어 200, 404 등입니다. 상태 코드가 음수인 경우 연결에 문제가 있는 것을 나타냅니다.[더보기](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```


## 방법


### __construct()

생성자입니다.

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

  * **매개변수** 

    * **`string $host`**
      * **기능**：목표 서버 호스트 주소【IP나 도메인이 될 수 있으며, 하단에서 자동으로 도메인 해석을 진행합니다. 만약 로컬 UNIX 소켓이라면 `unix://tmp/your_file.sock`와 같은 형식으로 작성해야 합니다; 도메인이라면 `http://` 혹은 `https://` 프로토콜 헤더를 작성할 필요가 없습니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`int $port`**
      * **기능**：목표 서버 호스트 포트
      * **기본값**：없음
      * **기타 값**：없음

    * **`bool $ssl`**
      * **기능**：`SSL/TLS` 터널 암호화 사용 여부를 설정합니다. 대상 서버가 https라면 `$ssl` 매개변수를 `true`로 설정해야 합니다
      * **기본값**：`false`
      * **기타 값**：없음

  * **예제**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```


### set()

클라이언트 매개변수를 설정합니다.

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

이 방법은 `Swoole\Client->set`이 받는 매개변수와 완전히 동일하며, [Swoole\Client->set](/client?id=set) 방법의 문서를 참고하실 수 있습니다.

`Swoole\Coroutine\Http\Client`은 추가적인 옵션을 제공하여 `HTTP` 및 `WebSocket` 클라이언트를 제어할 수 있습니다.

#### 추가 옵션

##### 타임아웃 제어

`timeout` 옵션을 설정하여 HTTP 요청의 타임아웃 검사를 활성화합니다. 단위는 초이며, 최소 단위는 밀리초입니다.

```php
$http->set(['timeout' => 3.0]);
```

* 연결이 타임아웃되거나 서버가 연결을 종료하면 `statusCode`는 `-1`로 설정됩니다.
* 약속된 시간 내에 서버가 응답을 반환하지 않으면 요청이 타임아웃되어 `statusCode`는 `-2`로 설정됩니다.
* 요청이 타임아웃되면 하단에서 자동으로 연결을 끊습니다.
* 참고: [클라이언트 타임아웃 규칙](/coroutine_client/init?id=타임아웃 규칙)

##### keep_alive

`keep_alive` 옵션을 설정하여 HTTP 장기 연결을 활성화하거나 비활성화합니다.

```php
$http->set(['keep_alive' => false]);
```

##### websocket_mask

> RFC 규정에 따라, v4.4.0 이후에는 이 설정이 기본적으로 적용되지만, 성능 손실을 초래할 수 있습니다. 만약 서버 측에서 강제 요구가 없다면 `false`로 설정하여 비활성화할 수 있습니다

`WebSocket` 클라이언트에서 마스크를 활성화하거나 비활성화합니다. 기본적으로 활성화되어 있습니다. 활성화되면 `WebSocket` 클라이언트가 보낸 데이터에 마스크를 사용하여 데이터 변환을 합니다.

```php
$http->set(['websocket_mask' => false]);
```

##### websocket_compression

> v4.4.12 이상 버전이 필요합니다.

`true`로 설정하면 프레임에 대해 zlib 압축을 허용합니다. 구체적으로 압축이 가능할지는 서버 측이 압축을 처리할 수 있는지에 달려 있습니다(헤스팅 정보에 따라 결정되며, 참조 `RFC-7692`)

프레임에 대해 실제로 압축을 수행하려면 flags 매개변수 `SWOOLE_WEBSOCKET_FLAG_COMPRESS`를 함께 사용해야 합니다. 구체적인 사용 방법은 [해당 섹션](/websocket_server?id=websocket 프레임 압축-(rfc-7692))에서 확인하실 수 있습니다

```php
$http->set(['websocket_compression' => true]);
```

##### write_func
> v5.1.0 이상 버전이 필요합니다.

`write_func` 콜백 함수를 설정하여 스트림형 응답 내용을 처리할 수 있습니다. 예를 들어 `OpenAI ChatGPT`의 `Event Stream` 출력 내용을 처리할 수 있습니다.

> `write_func`를 설정하면 `getContent()`方法与을 사용하여 응답 내용을 가져올 수 없으며, `$client->body`도 비워집니다.  
> `write_func` 콜백 함수 내에서 `$client->close()`를 사용하여 응답 내용을 더 이상 수신하지 않고 연결을 종료할 수 있습니다

```php
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->set(['write_func' => function ($client, $data) {
    var_dump($data);
}]);
$cli->get('/');
```


### setMethod()

요청 방법을 설정합니다. 현재 요청에만 유효하며, 요청을 보낸 후에는 즉시 method 설정을 제거합니다.

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **매개변수** 

    * **`string $method`**
      * **기능**：설정 방법 
      * **기본값**：없음
      * **기타 값**：없음

      !> `HTTP` 표준에 부합하는 방법 이름이어야 하며, `$method`가 잘못 설정되어 있다면 `HTTP` 서버가 요청을 거절할 수 있습니다

  * **예제**

```php
$http->setMethod("PUT");
```


### setHeaders()

HTTP 요청 헤더를 설정합니다.

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **매개변수** 

    * **`array $headers`**
      * **기능**：요청 헤더 설정 【key와 value가 일치하는 배열이어야 하며, 하단에서 자동으로 `$key`: `$value` 형식의 `HTTP` 표준 헤더 형식으로 매핑됩니다】
      * **기본값**：없음
      * **기타 값**：없음

!> `setHeaders`로 설정된 `HTTP` 헤더는 `Coroutine\Http\Client` 객체가 생존하는 동안 모든 요청에 영구적으로 유효하며, `setHeaders`를 재조정하면 마지막 설정은 덮여집니다


### setCookies()

`Cookie`를 설정합니다. 값은 `urlencode`로 인코딩됩니다. 원래 정보를 유지하고 싶으시다면, `setHeaders`를 사용하여 `Cookie`라는 헤더를 직접 설정하실 수 있습니다.

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **매개변수** 

    * **`array $cookies`**
      * **기능**：`COOKIE` 설정 【key와 value가 일치하는 배열이어야 합니다】
      * **기본값**：없음
      * **기타 값**：없음
!> `COOKIE`를 설정하면 클라이언트 객체가 살아있을 동안 지속적으로 저장됩니다.  

- 서버에서 직접 설정한 `COOKIE`는 `cookies` 배열에 합산되어 `$client->cookies` 속성으로 현재 HTTP 클라이언트의 `COOKIE` 정보를读取할 수 있습니다.  
- `setCookies` 메서드를 중복 호출하면 현재의 `Cookies` 상태를 덮어씌우며, 이는 이전에 서버에서 전송한 `COOKIE`와 직접 설정한 `COOKIE`를 버립니다.


### setData()

HTTP 요청의 바디를 설정합니다.

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **매개변수** 

    * **`string|array $data`**
      * **기능**: 요청의 바디를 설정합니다
      * **기본값**: 없음
      * **기타값**: 없음

  * **알림**

    * `$data`를 설정하고 `$method`가 설정되지 않은 경우,底层은 자동으로 POST로 설정합니다.
    * `$data`가 배열일 때 `Content-Type`이 `urlencoded` 형식이면,底层은 자동으로 `http_build_query`을 수행합니다.
    * `addFile` 또는 `addData`를 사용하여 `form-data` 형식을 활성화하면, `$data`가 문자열일 경우 무시됩니다(형식이 다르기 때문), 하지만 배열일 경우底层은 `form-data` 형식으로 배열의 필드를 추가합니다.


### addFile()

POST 파일을 추가합니다.

!> `addFile`를 사용하면 POST의 `Content-Type`이 자동으로 `form-data`로 변경됩니다. `addFile`底层은 `sendfile`을 기반으로 하여 대량 파일을 비동기적으로 전송할 수 있습니다.

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **매개변수** 

    * **`string $path`**
      * **기능**: 파일의 경로【필수 매개변수, 빈 파일 또는 존재하지 않는 파일은 올 수 없습니다】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $name`**
      * **기능**: 양식의 이름【필수 매개변수, `FILES` 매개변수의 `key`】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $mimeType`**
      * **기능**: 파일의 MIME 형식【선택적 매개변수,底层은 파일의 확장자를 자동으로 추정합니다】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $filename`**
      * **기능**: 파일 이름【선택적 매개변수】
      * **기본값**: `basename($path)`
      * **기타값**: 없음

    * **`int $offset`**
      * **기능**: 업로드 파일의 오프셋【선택적 매개변수, 파일의 중간 부분부터 데이터 전송을 시작할 수 있습니다. 이 기능은 중단된 전송을 지원하는 데 사용할 수 있습니다.】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`int $length`**
      * **기능**: 전송 데이터의 크기【선택적 매개변수】
      * **기본값**: 전체 파일의 크기
      * **기타값**: 없음

  * **예시**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```


### addData()

문자열을 사용하여 업로드 파일 내용을 구축합니다. 

!> `addData`는 `v4.1.0` 이상 버전에서 사용할 수 있습니다

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **매개변수** 

    * **`string $data`**
      * **기능**: 데이터 내용【필수 매개변수, 최대 길이는 [buffer_output_size](/server/setting?id=buffer_output_size)를 초과할 수 없습니다】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $name`**
      * **기능**: 양식의 이름【필수 매개변수, `$_FILES` 매개변수의 `key`】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $mimeType`**
      * **기능**: 파일의 MIME 형식【선택적 매개변수, 기본값은 `application/octet-stream`】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`string $filename`**
      * **기능**: 파일 이름【선택적 매개변수, 기본값은 `$name`】
      * **기본값**: 없음
      * **기타값**: 없음

  * **예시**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```


### get()

GET 요청을 시작합니다.

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **매개변수** 

    * **`string $path`**
      * **기능**: `URL` 경로를 설정합니다【예: `/index.html`, 여기서는 `http://domain`를 전달할 수 없습니다】
      * **기본값**: 없음
      * **기타값**: 없음

  * **예시**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

!> `get`를 사용하면 `setMethod`에서 설정한 요청 방법을 무시하고 강제로 `GET`을 사용합니다


### post()

POST 요청을 시작합니다.

```php
Swoole\Coroutine\Http\Client->post(string $path, mixed $data): void
```

  * **매개변수** 

    * **`string $path`**
      * **기능**: `URL` 경로를 설정합니다【예: `/index.html`, 여기서는 `http://domain`를 전달할 수 없습니다】
      * **기본값**: 없음
      * **기타값**: 없음

    * **`mixed $data`**
      * **기능**: 요청의 바디 데이터
      * **기본값**: 없음
      * **기타값**: 없음

      !> `$data`가 배열일 경우底层은 자동으로 `x-www-form-urlencoded` 형식의 POST 내용을 포장하고 `Content-Type`을 `application/x-www-form-urlencoded`로 설정합니다

  * **주의**

    !> `post`를 사용하면 `setMethod`에서 설정한 요청 방법을 무시하고 강제로 `POST`를 사용합니다

  * **예시**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```


### upgrade()

WebSocket 연결으로 업그레이드합니다.

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **매개변수** 

    * **`string $path`**
      * **기능**: `URL` 경로를 설정합니다【예: `/`】
      * **기본값**: 없음
      * **기타값**: 없음

  * **알림**

    * 어떤 경우에는 요청이 성공했지만 `upgrade`가 `true`를 반환했지만, 서버는 HTTP 상태코드를 `101`로 설정하지 않고 `200` 또는 `403`로 설정하는 경우가 있습니다. 이는 서버가 핸드셋 요청을 거절했다는 것을 의미합니다.
    * WebSocket 핸드셋이 성공한 후에는 `push` 메서드를 사용하여 서버에 메시지를 전송하거나 `recv`를 호출하여 메시지를 수신할 수 있습니다.
    * `upgrade`는 [코루outine 스케줄러](/coroutine?id=코루outine%E8%B7%A8%E5%8A%A8)를 한 번 생성합니다.

  * **예시**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### 푸시()

WebSocket 서버에 메시지를 전송합니다.

!> `push` 방법은 `upgrade` 성공 후에만 실행할 수 있습니다  
`push` 방법은 [코루outine 스케줄러](/coroutine?id=코루outine_스케줄러)를 생성하지 않으며, 보낸 메시지를 전송 캐시로 쓴 후 즉시 반환합니다

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **매개변수** 

    * **`mixed $data`**
      * **기능** : 전송할 데이터 내용【기본적으로 `UTF-8` 텍스트 포맷이며, 다른 포맷의 인코딩이나 이진 데이터인 경우 `WEBSOCKET_OPCODE_BINARY`을 사용하세요】
      * **기본값** : 없음
      * **기타 값** : 없음

      !> Swoole 버전 >= v4.2.0 `$data`은 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) 객체를 사용할 수 있으며, 다양한 프레임 유형을 지원합니다

    * **`int $opcode`**
      * **기능** : 운영 유형
      * **기본값** : `WEBSOCKET_OPCODE_TEXT`
      * **기타 값** : 없음

      !> `$opcode`는 유효한 `WebSocket OPCode`여야 하며, 그렇지 않으면 실패하고 오류 메시지를 출력합니다 `opcode max 10`

    * **`int|bool $finish`**
      * **기능** : 운영 유형
      * **기본값** : `SWOOLE_WEBSOCKET_FLAG_FIN`
      * **기타 값** : 없음

      !> v4.4.12 버전부터 `finish` 매개변수(``bool`` 유형)는 `flags`(``int`` 유형)로 변경되어 `WebSocket` 압축을 지원하게 되었습니다. `finish`은 `SWOOLE_WEBSOCKET_FLAG_FIN` 값이 `1`일 때 해당하며, 기존의 ``bool`` 유형은 암시적으로 ``int`` 유형으로 변환됩니다. 이 변경은 하향 호환에 영향을 주지 않습니다. 또한 압축 `flag`은 `SWOOLE_WEBSOCKET_FLAG_COMPRESS`입니다.

  * **반환값**

    * 성공 시 `true` 반환
    * 연결이 존재하지 않거나 이미 닫혀 있거나 `WebSocket`가 완료되지 않은 경우 실패하여 `false` 반환

  * **오류 코드**


오류 코드 | 설명
---|---
8502 | 잘못된 OPCode
8503 | 서버에 연결하지 못하거나 연결이 이미 닫혀 있음
8504 | 핸드셋 실패


### 수신()

메시지를 수신합니다. WebSocket 전용이며, `upgrade()`와 함께 사용해야 합니다. 예를 보세요

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능** : `upgrade()`를 호출하여 WebSocket 연결으로 업그레이드할 때만 유효합니다
      * **값 단위** : 초【소수도 지원하며, 예를 들어 `1.5`는 `1초 + 500ms`을 나타냅니다】
      * **기본값** : [클라이언트超时 규칙](/coroutine_client/init?id=超时规则) 참조
      * **기타 값** : 없음

      !> 超时被 설정하면 지정된 매개변수를 우선 사용하고, 그 다음으로는 `set` 방법에서 전달한 `timeout` 구성이 사용됩니다
  
  * **반환값**

    * 성공 시 프레임 객체 반환
    * 실패 시 `false` 반환하며, `Swoole\Coroutine\Http\Client`의 `errCode` 속성을 확인합니다. 코루outine 클라이언트에 `onClose` 콜백이 없을 경우, 연결이 닫힐 때 `recv`를 호출하면 `false` 반환하고 `errCode=0`입니다 
 
  * **예시**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```


### 다운로드()

HTTP를 통해 파일을 다운로드합니다.

!> download과 get 방법의 차이점은 download이 데이터를 수신하면 디스크에 쓰기보다는 메모리에서 HTTP Body를 연결하는 것이 아닙니다. 따라서 download은 작은 양의 메모리만으로도 매우 큰 파일의 다운로드를 완료할 수 있습니다.

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename,  int $offset = 0): bool
```

  * **매개변수** 

    * **`string $path`**
      * **기능** : URL 경로 설정
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`string $filename`**
      * **기능** : 다운로드 내용을 쓰는 파일 경로 지정【자동으로 `downloadFile` 속성에 쓰입니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`int $offset`**
      * **기능** : 파일에 쓰는 오프셋 지정【해당 옵션은 중단 후 재개를 지원하며, HTTP 헤더 `Range:bytes=$offset`와 함께 사용할 수 있습니다】
      * **기본값** : 없음
      * **기타 값** : 없음

      !> `$offset`가 `0`일 경우 파일이 이미 존재하는 경우, 하단에서 자동으로 해당 파일을 비워줍니다

  * **반환값**

    * 성공 시 `true` 반환
    * 파일 열기 실패 또는 하단 `fseek()` 파일 실패 시 `false` 반환

  * **예시**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```


### getCookies()

HTTP 응답의 cookie 내용을 가져옵니다.

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> Cookie 정보는 urldecode 디코딩을 거쳐 제공되며, 원본 Cookie 정보를 가져오려면 다음의 방법으로 스스로 파싱하세요

#### 중복된 Cookie 또는 Cookie 원본 헤더 정보를 가져옵니다

```php
var_dump($client->set_cookie_headers);
```


### getHeaders()

HTTP 응답의 헤더 정보를 반환합니다.

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```


### getStatusCode()

HTTP 응답의 상태 코드를 가져옵니다.

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **알림**

    * **상태 코드가 부정수인 경우, 연결에 문제가 발생했다는 것을 나타냅니다.**


상태 코드 | v4.2.10 이상 버전에서 해당하는 상수 | 설명

---|---|---

-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | 연결超时, 서버가 포트를 수신하지 않거나 네트워크가 손상되어, $errCode를 통해 구체적인 네트워크 오류 코드를 읽을 수 있습니다

-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | 요청超时, 서버가 정의된 timeout 시간 내에 응답을 반환하지 않았습니다

-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | 클라이언트의 요청이 발생한 후, 서버가 강제로 연결을 끊어버렸습니다
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | 클라이언트가 전송에 실패했습니다(해당 상수는 Swoole 버전이 `v4.5.9` 이상에서 사용할 수 있으며, 이 버전 이전의 경우 상태 코드를 사용하세요)


### getBody()

HTTP 응답의 바디 내용을 가져옵니다.

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```


### close()

연결을 닫습니다.

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> `close` 후에 `get`, `post` 등의 방법을 다시 호출하면, Swoole가 서버에 다시 연결해 드립니다.


### execute()

보다 하이레벨의 HTTP 요청 방법으로, 코드에서 [setMethod](/coroutine_client/http_client?id=setmethod)와 [setData](/coroutine_client/http_client?id=setdata) 등의 인터페이스를 통해 요청 방법과 데이터를 설정해야 합니다.

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **예시**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```
## 함수

`Coroutine\Http\Client`의 사용을 용이하게 하기 위해 다음 세 가지 함수가 추가되었습니다:

!> Swoole 버전 >= `v4.6.4`에서 사용할 수 있습니다.


### request()

특정 HTTP 방법으로 요청을 시작합니다.

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```


### post()

`POST` 요청을 시작하는 데 사용됩니다.

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```


### get()

`GET` 요청을 시작하는 데 사용됩니다.

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```

### 사용 예제

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
