# Http\Server

?> `Http\Server`는 [Server](/server/init)를 상속하기 때문에 `Server`가 제공하는 모든 `API`와 설정 항목을 사용할 수 있으며, 프로세스 모델도 동일합니다. 자세한 내용은 [Server](/server/init) 장을 참고하세요.

구성된 `HTTP` 서버의 지원은 몇 줄의 코드로 고성능, 고가용성의 [비동기IO](/learn?id=同步io异步io) 멀티 프로세스 `HTTP` 서버를 작성할 수 있습니다.

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

`Apache bench` 도구를 사용한 압박 테스트에서, `Inter Core-I5 4코어 + 8G 메모리`의 일반 PC 기계에서 `Http\Server`는 거의 `11만QPS`에 달할 수 있습니다.

`PHP-FPM`, `Golang`, `Node.js`의 내장 `HTTP` 서버를 훨씬 뛰어넘는 성능을 자랑합니다. 성능은 거의 `Nginx`의 정적 파일 처리와 비슷합니다.

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **HTTP2 프로토콜 사용**

  * `SSL` 하의 `HTTP2` 프로토콜 사용은 `openssl`를 설치해야 하며, 고 버전의 `openssl`은 `TLS1.2`, `ALPN`, `NPN`을 지원해야 합니다.
  * 컴파일 시 `--enable-http2](/environment?id=编译选项)` 옵션을 사용하여 활성화해야 합니다.
  * Swoole5부터는 기본적으로 http2 프로토콜이 활성화되어 있습니다.

```shell
./configure --enable-openssl --enable-http2
```

[open_http2_protocol](/http_server?id=open_http2_protocol)를 `true`로 설정합니다.

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Nginx + Swoole 구성**

!> `Http\Server`는 `HTTP` 프로토콜의 지원이 완전하지 않기 때문에, 동적으로 요청을 처리하기 위해 애플리케이션 서버로 사용하고, 프론트엔드에 `Nginx`를 프록시로 추가하는 것이 권장됩니다.

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> `Client`의 실제 `IP`를 얻을 수 있습니다.


## 방법


### on()

?> **이벤트回调 함수를 등록합니다.**

?> [Server의回调](/server/events)와 동일하지만, 차이점은 다음과 같습니다:

  * `Http\Server->on`은 [onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)回调 설정은 받지 않습니다.
  * `Http\Server->on`은 새로운 이벤트 유형 `onRequest`를 추가로 지원하며, 클라이언트에서 온 요청은 `Request` 이벤트에서 실행됩니다.

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

전체 `HTTP` 요청을 받은 후에 이 함수가回调됩니다.回调 함수에는 총 `2`개의 매개변수가 있습니다:

* [Swoole\Http\Request](/http_server?id=httpRequest), `HTTP` 요청 정보 객체, `header/get/post/cookie` 등 관련 정보를 포함합니다.
* [Swoole\Http\Response](/http_server?id=httpResponse), `HTTP` 응답 객체, `cookie/header/status` 등 `HTTP` 작업을 지원합니다.

!> [onRequest](/http_server?id=on)回调 함수에서 반환될 경우, 기본적으로 `$request`와 `$response` 객체를 파괴합니다.


### start()

?> **HTTP 서버를 시작합니다.**

?> 시작하면端口을 감시하고 새로운 `HTTP` 요청을 수락하기 시작합니다.

```php
Swoole\Http\Server->start();
```


## Swoole\Http\Request

`HTTP` 요청 객체로, `GET`, `POST`, `COOKIE`, `Header` 등과 같은 `HTTP` 클라이언트의 요청 관련 정보를 저장합니다.

!> `Http\Request` 객체를 참조할 때 `&` 기호를 사용하지 마십시오.


### header

?> **`HTTP` 요청의 헤더 정보입니다. 유형은 배열이며, 모든 `key`는 소문자입니다.**

```php
Swoole\Http\Request->header: array
```

* **예시**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```


### server

?> **`HTTP` 요청 관련의 서버 정보입니다.**

?> PHP의 `$_SERVER` 배열과 같습니다. `HTTP` 요청의 방법, `URL` 경로, 클라이언트 `IP` 등의 정보를 포함합니다.

```php
Swoole\Http\Request->server: array
```

배열의 `key`는 모두 소문자이며, PHP의 `$_SERVER` 배열과 일관되어 있습니다.

* **예시**

```php
echo $request->server['request_time'];
```


key | 설명
---|---
query_string | 요청의 `GET` 매개변수, 예: `id=1&cid=2` `GET` 매개변수가 없을 경우 이 항목은 존재하지 않습니다
request_method | 요청 방법, `GET/POST` 등
request_uri | `GET` 매개변수가 없는 액세스 주소, 예: `/favicon.ico`
path_info | 동일 `request_uri`
request_time | `request_time`는 `Worker`에서 설정되며, [SWOOLE_PROCESS](/learn?id=swoole_process) 모드에서는 `dispatch` 과정이 존재하기 때문에 실제 수신 시간과 다를 수 있습니다. 특히 요청량이 서버 처리 능력을 초과할 경우, `request_time`는 실제 수신 시간보다 훨씬 뒤처질 수 있습니다. 정확한 수신 시간을 얻으려면 `$server->getClientInfo` 메서드를 사용하여 `last_time`을 가져올 수 있습니다.
request_time_float | 요청 시작 시간을 초로으로 나타낸 타임스탬프, 예: `1576220199.2725`
server_protocol | 서버 프로토콜 버전 번호, `HTTP`는: `HTTP/1.0` 또는 `HTTP/1.1`, `HTTP2`는: `HTTP/2`
server_port | 서버가 감시하는 포트 번호
remote_port | 클라이언트의 포트 번호
remote_addr | 클라이언트의 `IP` 주소
master_time | 마지막 통신 시간


### get

?> **`HTTP` 요청의 `GET` 매개변수, PHP의 `$_GET`과 같습니다. 형식은 배열입니다.**

```php
Swoole\Http\Request->get: array
```

* **예시**

```php
// 예: index.php?hello=123
echo $request->get['hello'];
// 모든 GET 매개변수를 가져옵니다.
var_dump($request->get);
```

* **주의**

!> `HASH` 공격을 방지하기 위해 `GET` 매개변수의 최대 개수는 `128`개를 초과할 수 없습니다.


### post

?> **`HTTP` 요청의 `POST` 매개변수, 형식은 배열입니다.**

```php
Swoole\Http\Request->post: array
```

* **예시**

```php
echo $request->post['hello'];
```

* **주의**


!> - `POST`와 `Header`의 합계 크기가 [package_max_length](/server/setting?id=package_max_length) 설정의 값을 초과하면 악의적인 요청으로 간주됩니다.  
- `POST` 매개변수의 최대 개수는 `128`개를 초과할 수 없습니다.


### cookie

?> **`HTTP` 요청에 포함된 `COOKIE` 정보, 형식은 키-값 쌍의 배열입니다.**

```php
Swoole\Http\Request->cookie: array
```

* **예시**

```php
echo $request->cookie['username'];
```


### files

?> **업로드된 파일 정보입니다.**

?> 형식은 `form` 이름을 `key`로 하는 2차원 배열입니다. PHP의 `$_FILES`와 같습니다. 최대 파일 크기는 [package_max_length](/server/setting?id=package_max_length) 설정의 값을 초과할 수 없습니다. Swoole이 메시지를 분석할 때 메모리를 차지하기 때문에, 메시지가 클수록 메모리 사용량이 증가합니다. 따라서 `Swoole\Http\Server`를 사용하여 대형 파일 업로드를 처리하거나 사용자가 직접 브레이크 포인트 재개 기능을 설계하는 것은 권장되지 않습니다.

```php
Swoole\Http\Request->files: array
```

* **예시**

```php
Array
(
    [name] => facepalm.jpg // 브라우저에서 업로드할 때 전달된 파일 이름
    [type] => image/jpeg // MIME 유형
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // 업로드된 임시 파일, 파일 이름은 /tmp/swoole.upfile로 시작합니다.
    [error] => 0
    [size] => 15476 // 파일 크기
)
```

* **주의**

!> `Swoole\Http\Request` 객체가 파괴될 경우, 자동으로 업로드된 임시 파일이 삭제됩니다.
### getContent()

!> Swoole 버전이 `v4.5.0` 이상일 경우 사용할 수 있으며, 저버전에서는 별명 `rawContent`을 사용할 수 있습니다(해당 별명은 영구히 유지되며, 즉 하향 호환성을 유지합니다).

?> **원본의 `POST` 패드 바디를 가져옵니다.**

?> `application/x-www-form-urlencoded` 형식이 아닌 HTTP `POST` 요청에 사용됩니다. 원본 `POST` 데이터를 반환하며, 이 함수는 PHP의 `fopen('php://input')`과 동일합니다.

```php
Swoole\Http\Request->getContent(): string|false
```

  * **반환값**

    * 성공 시 패드를 반환하고, 컨텍스트 연결이 존재하지 않을 경우 `false`을 반환합니다.

!> 일부 경우 서버는 HTTP `POST` 요청 매개변수를解析할 필요가 없습니다. [http_parse_post](/http_server?id=http_parse_post) 설정을 통해 `POST` 데이터解析를 비활성화할 수 있습니다.


### getData()

?> **전체 원본 `Http` 요청 패드를 가져옵니다. `Http2`에서 사용할 수 없습니다. `Http Header`과 `Http Body`를 포함합니다.**

```php
Swoole\Http\Request->getData(): string|false
```

  * **반환값**

    * 성공 시 패드를 반환하고, 컨텍스트 연결이 존재하지 않을 경우 또는 `Http2` 모드일 경우 `false`을 반환합니다.


### create()

?> **`Swoole\Http\Request` 객체를 만듭니다.**

!> Swoole 버전이 `v4.6.0` 이상일 경우 사용할 수 있습니다.

```php
Swoole\Http\Request->create(array $options): Swoole\Http\Request
```

  * **매개변수**

    * **`array $options`**
      * **기능** : 선택적 매개변수로 `Request` 객체의 설정을 설정합니다.

| 매개변수                                          | 기본값 | 설명                                                                |
| ------------------------------------------------- | ------ | ----------------------------------------------------------------- |
| [parse_cookie](/http_server?id=http_parse_cookie) | true   | `Cookie`解析 여부 설정                                            |
| [parse_body](/http_server?id=http_parse_post)      | true   | `Http Body`解析 여부 설정                                            |
| [parse_files](/http_server?id=http_parse_files)   | true   | 업로드 파일解析 스위치 설정                                          |
| enable_compression                                | true, Server가 압축된 메시지를 지원하지 않을 경우 기본값은false   | 압축 활성화 여부 설정                                                |
| compression_level                                 | 1      | 압축 레벨 설정, 범위는 1-9이며, 레벨이 높을수록 압축 후의 사이즈가 작아지지만 CPU 소모가 증가합니다        |
| upload_tmp_dir                                 | /tmp      | 임시 파일 저장 위치, 파일 업로드에 사용                                        |

  * **반환값**

    * `Swoole\Http\Request` 객체를 반환합니다.

* **예시**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```


### parse()

?> **`HTTP` 요청 데이터 패드를解析합니다. 성공 시解析된 패드의 길이를 반환합니다.**

!> Swoole 버전이 `v4.6.0` 이상일 경우 사용할 수 있습니다.

```php
Swoole\Http\Request->parse(string $data): int|false
```

  * **매개변수**

    * **`string $data`**
      * 解析할 패드

  * **반환값**

    * 解析 성공 시解析된 패드의 길이를 반환하고, 연결 컨텍스트가 존재하지 않을 경우 또는 컨텍스트가 이미 종료된 경우 `false`을 반환합니다.


### isCompleted()

?> **현재의 `HTTP` 요청 데이터 패드가 끝에 도달했는지를 가져옵니다.**

!> Swoole 버전이 `v4.6.0` 이상일 경우 사용할 수 있습니다.

```php
Swoole\Http\Request->isCompleted(): bool
```

  * **반환값**

    * `true`이면 이미 끝에 도달했음을 나타내고, `false`이면 연결 컨텍스트가 종료되었거나 끝에 도달하지 않았음을 나타냅니다.

* **예시**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n";
$data .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n";
$data .= "Accept-Encoding: gzip, deflate, br\r\n";
$data .= "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,ja;q=0.6\r\n";
$data .= "Cookie: env=pretest; phpsessid=fcccs2af8673a2f343a61a96551c8523d79ea; username=hantianfeng\r\n";

/** @var Request $req */
$req = Request::create(['parse_cookie' => false]);
var_dump($req);

var_dump($req->isCompleted());
var_dump($req->parse($data));

var_dump($req->parse("\r\n"));
var_dump($req->isCompleted());

var_dump($req);
// Cookie解析가 중지되었기 때문에 null이 될 것입니다.
var_dump($req->cookie);
```


### getMethod()

?> **현재의 `HTTP` 요청의 요청 방식을 가져옵니다.**

!> Swoole 버전이 `v4.6.2` 이상일 경우 사용할 수 있습니다.

```php
Swoole\Http\Request->getMethod(): string|false
```
  * **반환값**

    * 성공 시 대문으로 된 요청 방식을 반환하고, 연결 컨텍스트가 존재하지 않을 경우 `false`을 반환합니다.

```php
var_dump($request->server['request_method']);
var_dump($request->getMethod());
```


## Swoole\Http\Response

`HTTP` 응답 객체로, 이 객체의 메서드를 호출하여 `HTTP` 응답을 전송합니다.

?> `Response` 객체가 소멸될 때, `end](/http_server?id=end)` 메서드를 호출하지 않고 `HTTP` 응답을 전송하지 않은 경우, 하단에서 자동으로 `end("")`를 실행합니다;

!> `Http\Response` 객체를 `&` 기호로 참조하지 마십시오.


### header() :id=setheader

?> **HTTP 응답의 헤더 정보를 설정합니다.**【별명 `setHeader`】

```php
Swoole\Http\Response->header(string $key, string $value, bool $format = true): bool;
```

* **매개변수** 

  * **`string $key`**
    * **기능** : `HTTP` 헤더의 `Key`
    * **기본값** : 없음
    * **기타값** : 없음

  * **`string $value`**
    * **기능** : `HTTP` 헤더의 `value`
    * **기본값** : 없음
    * **기타값** : 없음

  * **`bool $format`**
    * **기능** : `Key`에 대해 `HTTP` 약속 형식을 할 필요가 있는지 여부 【기본 `true`이면 자동으로 형식화합니다】
    * **기본값** : `true`
    * **기타값** : 없음

* **반환값** 

  * 설정 실패 시 `false`를 반환합니다.
  * 설정 성공 시 `true`를 반환합니다.
* **주의사항**

   -`header` 설정은 `end` 메서드 이전에 해야 합니다.
   -`$key`는 반드시 `HTTP` 약속에 따라 완전히 맞춰져야 합니다. 각 단어의 첫 글자는 대문이어야 하고, 중문자, 밑줄 또는 기타 특수 문자가 포함되어서는 안 됩니다.  
   -`$value`는 반드시 작성되어야 합니다.  
   -`$ucwords`를 `true`로 설정하면, 하단에서 자동으로 `$key`에 대해 약속 형식을 적용합니다.  
   -같은 `$key`의 `HTTP` 헤더를 중복하여 설정하면, 마지막 설정된 값이 덮어씌워지고, 마지막 설정된 값만 사용됩니다.  
   -클라이언트가 `Accept-Encoding`을 설정한 경우, 서버는 `Content-Length` 응답을 설정할 수 없습니다. `Swoole`는 이러한 상황을 감지하고 `Content-Length`의 값을 무시하며 경고를 발생시킵니다.   
   -`Content-Length` 응답이 설정된 경우, `Swoole\Http\Response::write()`를 호출할 수 없습니다. `Swoole`는 이러한 상황을 감지하고 `Content-Length`의 값을 무시하며 경고를 발생시킵니다.

!> Swoole 버전이 `v4.6.0` 이상일 경우, 같은 `$key`의 `HTTP` 헤더를 중복하여 설정할 수 있으며, `$value`는 다양한 유형을 지원합니다. 예를 들어 `array`, `object`, `int`, `float` 등이 있으며, 하단에서 `toString` 변환을 수행하고,末尾의 공백과换行자를 제거합니다.

* **예시**

```php
$response->header('content-type', 'image/jpeg', true);

$response->header('Content-Length', '100002 ');
$response->header('Test-Value', [
    "a\r\n",
    'd5678',
    "e  \n ",
    null,
    5678,
    3.1415926,
]);
$response->header('Foo', new SplFileInfo('bar'));
```
### 트레일러()

?> **HTTP 응답의 끝에 ` 헤더` 정보를 추가합니다. HTTP/2에서만 사용 가능하며, 메시지 완전성 검사, 디지털 서명 등에 사용됩니다.**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **매개변수** 

  * **`string $key`**
    * **기능**：HTTP 헤더의 `Key`
    * **기본값**：없음
    * **기타 값**：없음

  * **`string $value`**
    * **기능**：HTTP 헤더의 `value`
    * **기본값**：없음
    * **기타 값**：없음

* **반환값** 

  * 설정 실패 시 `false` 반환
  * 설정 성공 시 `true` 반환

* **주의**

  !> 동일한 `$key`의 HTTP 헤더를 중복 설정하면 마지막 설정값이 덮여집니다.

* **예시**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```


### 쿠키()

?> **HTTP 응답에 `쿠키` 정보를 설정합니다. 별명은 `setCookie`입니다. 이 방법의 매개변수는 PHP의 `setcookie`와 일치합니다.**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **매개변수** 

    * **`string $key`**
      * **기능**：쿠키의 `Key`
      * **기본값**：없음
      * **기타 값**：없음

    * **`string $value`**
      * **기능**：쿠키의 `value`
      * **기본값**：없음
      * **기타 값**：없음
  
    * **`int $expire`**
      * **기능**：쿠키의 `만료 시간`
      * **기본값**：0, 만료되지 않음
      * **기타 값**：없음

    * **`string $path`**
      * **기능**：쿠키의 서비스자 경로를 규정합니다.
      * **기본값**：/
      * **기타 값**：없음

    * **`string $domain`**
      * **기능**：쿠키의 도메인을 규정합니다.
      * **기본값**：''
      * **기타 값**：없음

    * **`bool $secure`**
      * **기능**：HTTPS 연결을 통해 쿠키를 전송할지 여부를 규정합니다.
      * **기본값**：''
      * **기타 값**：없음

    * **`bool $httponly`**
      * **기능**：浏览器的 JavaScript가 HttpOnly 속성을 가진 쿠키에 액세스할 수 있는지 여부를 규정합니다. `true`는 액세스가 허용되지 않음을 나타내며, `false`는 허용합니다.
      * **기본값**：false
      * **기타 값**：없음

    * **`string $samesite`**
      * **기능**：세 번째 파티의 쿠키를 제한하여 보안 위험을 줄입니다. 선택 가능한 값은 `Strict`, `Lax`, `None`입니다.
      * **기본값**：''
      * **기타 값**：없음

    * **`string $priority`**
      * **기능**：쿠키 우선순위를 설정합니다. 쿠키 수가 제한에 도달하면 낮은 우선순위의 쿠키가 먼저 삭제됩니다. 선택 가능한 값은 `Low`, `Medium`, `High`입니다.
      * **기본값**：''
      * **기타 값**：없음
  
  * **반환값** 

    * 설정 실패 시 `false` 반환
    * 설정 성공 시 `true` 반환

* **주의**

  !> - `cookie` 설정은 [$response->end()](/http_server?id=end) 방법 이전에 해야 합니다.  
  - `$samesite` 매개변수는 `v4.4.6` 버전부터 지원되며, `$priority` 매개변수는 `v4.5.8` 버전부터 지원됩니다.  
  - `Swoole`는 자동으로 `$value`에 `urlencode` 인코딩을 적용합니다. `$value`에 대한 인코딩 처리를 비활성화하려면 `rawCookie()` 방법을 사용할 수 있습니다.  
  - `Swoole`는 동일한 `$key`의 `COOKIE`를 여러 개 설정할 수 있습니다.


### rawCookie()

?> **HTTP 응답에 `쿠키` 정보를 설정합니다.**

!> `rawCookie()`의 매개변수는 상문의 `cookie()`와 동일하지만 인코딩 처리는 이루어지지 않습니다.


### 상태()

?> **HTTP 상태 코드를 보냅니다. 별명은 `setStatusCode()`입니다.**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **매개변수** 

  * **`int $http_status_code`**
    * **기능**：HttpCode 설정
    * **기본값**：없음
    * **기타 값**：없음

  * **`string $reason`**
    * **기능**：상태코드 이유
    * **기본값**：''
    * **기타 값**：없음

  * **반환값** 

    * 설정 실패 시 `false` 반환
    * 설정 성공 시 `true` 반환

* **피드백**

  * 첫 번째 매개변수 `$http_status_code`만 전달된 경우, 유효한 HttpCode여야 합니다. 예를 들어 `200`, `502`, `301`, `404` 등이 있으며, 그렇지 않으면 `200` 상태코드로 설정됩니다.
  * 두 번째 매개변수 `$reason`가 설정된 경우, `$http_status_code`는 정의되지 않은 HttpCode인任意의 수치일 수 있습니다. 예를 들어 `499`
  * `$status` 방법은 [$response->end()](/http_server?id=end) 이전에 실행해야 합니다.


### 압축()

!> 이 방법은 `4.1.0` 이상 버전에서 이미 폐기되었습니다. [http_compression](/http_server?id=http_compression)로 이동해 주세요; 새로운 버전에서는 `http_compression` 구성 요소로 `gzip` 방법을 대체했습니다.  
주요 이유는 `gzip()` 방법이 브라우저 클라이언트가 전달한 `Accept-Encoding` 헤더를 판단하지 않아, 클라이언트가 `gzip` 압축을 지원하지 않으면 강제 사용하면 클라이언트가 압축을 풀 수 없게 됩니다.  
새로운 `http_compression` 구성 요소는 클라이언트의 `Accept-Encoding` 헤더에 따라 자동으로 압축 여부를 선택하고 최적의 압축 알고리즘을 선택합니다.

?> **HTTP GZIP 압축을 활성화합니다. 압축은 HTML 콘텐츠의 크기를 줄일 수 있으며, 네트워크 대역폭을 효과적으로 절약하고 응답 시간을 향상시킵니다. `write/end`에서 콘텐츠를 전송하기 전에 `gzip`를 실행해야 하며, 그렇지 않으면 오류가 발생합니다.**
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **매개변수** 
   
     * **`int $level`**
       * **기능**：압축 레벨, 레벨이 높을수록 압축 후의 크기가 작아지지만 CPU 소모가 더 많습니다.
       * **기본값**：1
       * **기타 값**：`1-9`

!> `gzip` 방법을 호출한 후에는 기본적으로 HTTP 헤더가 추가되므로, PHP 코드에서는 관련 HTTP 헤더를 더 이상 설정하지 않아야 합니다; `jpg/png/gif` 포맷의 이미지는 이미 압축되어 있어 다시 압축할 필요가 없습니다

!> `gzip` 기능은 `zlib` 라이브러리에 의존합니다. Swoole를 컴파일할 때 기본적으로 시스템에 `zlib`가 있는지를 검사합니다. `zlib`가 존재하지 않으면 `gzip` 방법이 사용할 수 없습니다. `yum` 또는 `apt-get`을 사용하여 `zlib` 라이브러리를 설치할 수 있습니다:

```shell
sudo apt-get install libz-dev
```


### 리디렉션()

?> **HTTP 리디렉션을 보냅니다. 이 방법을 호출하면 자동으로 `end`를 호출하여 응답을 종료합니다.**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **매개변수** 
* **매개변수** 
  * **매개변수** 
  * **매개변수** 

    * **`string $url`**
      * **기능**：리디렉션할 새로운 주소로, `Location` 헤더를 통해 전송됩니다.
      * **기본값**：없음
      * **기타 값**：없음

    * **`int $http_code`**
      * **기능**：상태코드【기본적으로 `302`로 임시 리디렉션을 보냅니다. `301`을 전달하면 영구 리디렉션을 보냅니다】
      * **기본값**：`302`
      * **기타 값**：없음

  * **반환값** 

    * 성공 시 `true` 반환, 실패하거나 연결이 존재하지 않을 경우 `false` 반환

* **예시**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```
### 쓰기()

?> **浏览기에 해당 내용을 전송하기 위해 `Http Chunk` 세그먼트를 활성화합니다.**

?> `Http Chunk`에 대한 자세한 내용은 `Http` 프로토콜 표준 문서를 참고하세요.

```php
Swoole\Http\Response->write(string $data): bool
```

  * **매개변수** 

    * **`string $data`**
      * **기능**: 전송할 데이터 내용【최대 길이는 `2M`을 초과할 수 없으며, [buffer_output_size](/server/setting?id=buffer_output_size) 설정에 의해 제어됩니다】
      * **기본값**: 없음
      * **기타 값**: 없음

  * **반환값** 
  
    * 성공 시 `true`를 반환하고, 실패하거나 연결 맥락이 존재하지 않을 경우 `false`를 반환합니다

* **알림**

  * `write`를 사용하여 데이터를 세그먼트로 전송한 후에는 [끝](/http_server?id=end) 메서드는 어떠한 매개변수도 받아들이지 않으며, `end`를 호출하는 것은 데이터 전송이 완료되었음을 나타내는 길이가 `0`의 `Chunk`을 보낼 뿐입니다
  * Swoole\Http\Response::header() 메서드를 통해 `Content-Length`가 설정되어 있다면, 이 메서드를 호출하면 Swoole은 `Content-Length` 설정을 무시하고 경고를 던집니다
  * `Http2`에서는 이 함수를 사용할 수 없으며, 그렇지 않으면 경고가 발생합니다
  * 클라이언트가 응답 압축을 지원하는 경우, Swoole\Http\Response::write()는 압축을 강제로 종료합니다


### sendfile()

?> **파일을 브라우저에 전송합니다.**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **매개변수** 

    * **`string $filename`**
      * **기능**: 전송할 파일 이름【파일이 존재하지 않거나 접근 권한이 없으면 `sendfile`가 실패합니다】
      * **기본값**: 없음
      * **기타 값**: 없음

    * **`int $offset`**
      * **기능**: 업로드 파일의 오프셋【파일의 중간 부분부터 데이터 전송을 시작할 수 있는 기능입니다. 이 특성은 중단 후 재개를 지원하는 데 사용할 수 있습니다】
      * **기본값**: `0`
      * **기타 값**: 없음

    * **`int $length`**
      * **기능**: 전송할 데이터의 크기
      * **기본값**: 파일의 크기
      * **기타 값**: 없음

  * **반환값** 

      * 성공 시 `true`를 반환하고, 실패하거나 연결 맥락이 존재하지 않을 경우 `false`를 반환합니다

* **알림**

  * 하단에서 전송할 파일의 MIME 형식을 추론할 수 없으므로 응용 코드에서 `Content-Type`을 명시해야 합니다
  * `sendfile`를 호출하기 전에 `write` 메서드를 사용하여 `Http-Chunk`을 전송해서는 안 됩니다
  * `sendfile`를 호출한 후에는 하단에서 자동으로 `end`를 실행합니다
  * `sendfile`는 `gzip` 압축을 지원하지 않습니다

* **예시**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```


### 끝()

?> **`Http` 응답 본체를 전송하고, 요청 처리를 종료합니다.**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **매개변수** 
  
    * **`string $html`**
      * **기능**: 전송할 내용
      * **기본값**: 없음
      * **기타 값**: 없음

  * **반환값** 

    * 성공 시 `true`를 반환하고, 실패하거나 연결 맥락이 존재하지 않을 경우 `false`를 반환합니다

* **알림**

  * `end`는 한 번만 호출할 수 있으며, 클라이언트에게 여러 차례 데이터를 전송해야 하는 경우 [쓰기](/http_server?id=write) 메서드를 사용하세요
  * 클라이언트가 [KeepAlive](/coroutine_client/http_client?id=keep_alive)를 활성화하면 연결이 유지되며, 서버는 다음 요청을 기다립니다
  * 클라이언트가 `KeepAlive`를 비활성화하면 서버는 연결을 종료합니다
  * `end`로 전송할 내용은 [output_buffer_size](/server/setting?id=buffer_output_size)에 의해 제한되며, 기본값은 `2M`입니다. 이 제한을 초과하면 응답이 실패하고 다음과 같은 오류가 발생합니다:

!> 해결책은 [sendfile](/http_server?id=sendfile)、[쓰기](/http_server?id=write)를 사용하거나 [output_buffer_size](/server/setting?id=buffer_output_size)를 조정하는 것입니다

```bash
WARNING finish (ERRNO 1203): The length of data [262144] exceeds the output buffer size[131072], please use the sendfile, chunked transfer mode or adjust the output_buffer_size
```


### 분리()

?> **응답 객체를 분리합니다.** 이 방법을 사용하면, `$response` 객체가 파괴될 때 자동으로 [끝](/http_server?id=httpresponse)를 호출하지 않으므로, [Http\Response::create](/http_server?id=create)와 [Server->send](/server/methods?id=send)와 함께 사용할 때 유용합니다.

```php
Swoole\Http\Response->detach(): bool
```

  * **반환값** 

    * 성공 시 `true`를 반환하고, 실패하거나 연결 맥락이 존재하지 않을 경우 `false`를 반환합니다

* **예시** 

  * **스레드 간 응답**

  ?> 어떤 경우에는 [Task 프로세스](/learn?id=taskworker进程)에서 클라이언트에 응답을 보내야 합니다. 이때 `detach`를 사용하여 `$response` 객체를 독립시킬 수 있습니다. [Task 프로세스](/learn?id=taskworker进程)에서 `$response` 객체를 다시 구축하고, `Http` 요청 응답을 시작할 수 있습니다. 

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->set(['task_worker_num' => 1, 'worker_num' => 1]);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->task(strval($resp->fd));
  });

  $http->on('finish', function () {
      echo "task finish";
  });

  $http->on('task', function ($serv, $task_id, $worker_id, $data) {
      var_dump($data);
      $resp = Swoole\Http\Response::create($data);
      $resp->end("in task");
      echo "async task\n";
  });

  $http->start();
  ```

  * **임의 내용 전송**

  ?> 특수한 상황에서 클라이언트에 특별한 응답 내용을 전송해야 할 때가 있습니다. `Http\Response` 객체가 제공하는 `end` 메서드는 요구를 충족시키지 못하므로, `detach`를 사용하여 응답 객체를 분리하고, 직접 HTTP 프로토콜 응답 데이터를 조립한 다음, `Server->send`를 사용하여 데이터를 전송할 수 있습니다.

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```


### create()

?> **새로운 `Swoole\Http\Response` 객체를 생성합니다.**

!> 이 방법을 사용하기 전에 반드시 `detach` 메서드를 호출하여 이전의 `$response` 객체를 분리해야 하며, 그렇지 않으면 동일한 요청에 대해 두 번의 응답 내용을 전송할 수 있습니다.

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

  * **매개변수** 

    * **`int $server`**
      * **기능**: `Swoole\Server` 또는 `Swoole\Coroutine\Socket` 객체, 배열(배열은 두 개의 매개변수만 가능하며, 첫 번째는 `Swoole\Server` 객체이고 두 번째는 `Swoole\Http\Request` 객체), 또는 파일 디스크립터
      * **기본값**: -1
      * **기타 값**: 없음

    * **`int $fd`**
      * **기능**: 파일 디스크립터. 매개변수 `$server`가 `Swoole\Server` 객체인 경우, `$fd`는 필수입니다
      * **기본값**: -1
      * 
      * **기타 값**: 없음

  * **반환값** 

    * 성공 시 새로운 `Swoole\Http\Response` 객체를 반환하고, 실패 시 `false`를 반환합니다

* **예시**

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->detach();
    // 예시1
    $resp2 = Swoole\Http\Response::create($req->fd);
    // 예시2
    $resp2 = Swoole\Http\Response::create($http, $req->fd);
    // 예시3
    $resp2 = Swoole\Http\Response::create([$http, $req]);
    // 예시4
    $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, IPPROTO_IP);
    $socket->connect('127.0.0.1', 9501)
    $resp2 = Swoole\Http\Response::create($socket);
    $resp2->end("hello world");
});

$http->start();
```
### isWritable()

?> **Swoole\Http\Response 객체가 종료되었거나 분리되었는지를 판단합니다.**

```php
Swoole\Http\Response->isWritable(): bool
```

  * **반환값** 

    * `Swoole\Http\Response` 객체가 종료되지 않았거나 분리되지 않았을 경우 `true`를 반환하고, 그렇지 않을 경우 `false`를 반환합니다.


!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다.

* **예시**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->isWritable()); // true
    $resp->end('hello');
    var_dump($resp->isWritable()); // false
    $resp->setStatusCode(403); // http response is unavailable (maybe it has been ended or detached)
});

$http->start();
```


## 구성 옵션


### http_parse_cookie

?> **Swoole\Http\Request 객체에 대한 설정으로, `Cookie` 파싱을 비활성화하여 헤더에서 미처 처리되지 않은 원본 `Cookies` 정보를 유지합니다. 기본적으로는 활성화되어 있습니다.**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```


### http_parse_post

?> **Swoole\Http\Request 객체에 대한 설정으로, POST 메시지 파싱을 설정합니다. 기본적으로는 활성화되어 있습니다.**

* `true`로 설정하면 `Content-Type`이 `x-www-form-urlencoded`인 요청 본체를 자동으로 `POST` 배열로 파싱합니다.
* `false`로 설정하면 POST 파싱을 비활성화합니다.

```php
$server->set([
    'http_parse_post' => false,
]);
```


### http_parse_files

?> **Swoole\Http\Request 객체에 대한 설정으로, 업로드 파일 파싱을 설정합니다. 기본적으로는 활성화되어 있습니다.**

```php
$server->set([
    'http_parse_files' => false,
]);
```


### http_compression

?> **Swoole\Http\Response 객체에 대한 설정으로, 압축을 활성화합니다. 기본적으로는 활성화되어 있습니다.**


!> -`http-chunk`은 분할하여 압축을 지원하지 않으므로, [write](/http_server?id=write) 메서드를 사용하면 압축이 강제적으로 비활성화됩니다.  
-`http_compression`는 `v4.1.0` 이상 버전에서 사용할 수 있습니다.

```php
$server->set([
    'http_compression' => false,
]);
```

현재 `gzip`, `br`, `deflate` 세 가지 압축 형식을 지원하며, 하단은 브라우저 클라이언트가 전달하는 `Accept-Encoding` 헤더에 따라 자동으로 압축 방식을 선택합니다(압축 알고리즘 우선순위: `br` > `gzip` > `deflate` ).

**의존:**

`gzip`와 `deflate`는 `zlib` 라이브러리를 의존하며, `Swoole`을 컴파일할 때 하단은 시스템에 `zlib`가 있는지를 검사합니다.

`yum` 또는 `apt-get`을 사용하여 `zlib` 라이브러리를 설치할 수 있습니다:

```shell
sudo apt-get install libz-dev
```

`br` 압축 형식은 `google`의 `brotli` 라이브러리를 의존하며, 설치 방법은 `install brotli on linux`를 찾아보시기 바랍니다. `Swoole`을 컴파일할 때 하단은 시스템에 `brotli`가 있는지를 검사합니다.


### http_compression_level / compression_level / http_gzip_level

?> **압축 레벨, Swoole\Http\Response 객체에 대한 설정**
  
!> `$level` 압축 레벨은 `1-9`의 범위이며, 레벨이 높을수록 압축된 후의 사이즈가 작아지지만 `CPU` 소모가 더 많아집니다. 기본값은 `1`, 최고는 `9`입니다.



### http_compression_min_length / compression_min_length

?> **압축을 적용할 최소 바이트 수를 설정합니다, Swoole\Http\Response 객체에 대한 설정으로, 이 옵션의 값을 초과할 경우 압축이 적용됩니다. 기본값은 20바이트입니다.**

!> Swoole 버전 >= `v4.6.3`에서 사용할 수 있습니다.

```php
$server->set([
    'compression_min_length' => 128,
]);
```


### upload_tmp_dir

?> **업로드 파일의 임시 디렉토리를 설정합니다. 디렉토리의 최대 길이는 `220`바이트를 초과할 수 없습니다**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```


### upload_max_filesize

?> **업로드 파일의 최대 크기를 설정합니다**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```


### enable_static_handler

정적 파일 요청 처리 기능을 활성화합니다. `document_root`와 함께 사용해야 합니다. 기본값은 `false`입니다.



### http_autoindex

`http autoindex` 기능을 활성화합니다. 기본적으로는 비활성화되어 있습니다.


### http_index_files

`http_autoindex`와 함께 사용하여 인덱스할 파일 목록을 지정합니다.

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```


### http_compression_types / compression_types

?> **압축할 응답 유형을 설정합니다, Swoole\Http\Response 객체에 대한 설정**

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Swoole 버전 >= `v4.8.12`에서 사용할 수 있습니다.



### static_handler_locations

?> **정적 처리기의 경로를 설정합니다. 유형은 배열이며, 기본적으로는 비활성화되어 있습니다.**

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* `Nginx`의 `location` 지시와 유사하게, 하나 이상의 경로를 정적 경로로 지정할 수 있습니다. URL이 지정된 경로에 있을 경우에만 정적 파일 처리기가 활성화되며, 그렇지 않을 경우 동적 요청으로 간주됩니다.
* `location` 항목은 `/`로 시작해야 합니다.
* 다단계 경로를 지원하며, 예를 들어 `/app/images`와 같습니다.
* `static_handler_locations`를 활성화하면, 요청에 해당하는 파일이 존재하지 않을 경우 직접 404 오류를 반환합니다.


### open_http2_protocol

?> **HTTP2 프로토콜 해석을 활성화합니다.**【기본값: `false`】

!> 컴파일 시 [--enable-http2](/environment?id=编译选项) 옵션을 사용해야 하며, `Swoole5`부터 기본적으로 http2가 컴파일됩니다.


### document_root

?> **정적 파일 루트 디렉토리를 설정합니다. `enable_static_handler`와 함께 사용해야 합니다.** 

!> 이 기능은 비교적 간단하므로, 공중 환경에서 직접 사용하지 않는 것이 좋습니다.

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // v4.4.0 이하 버전에서, 이곳은 반드시 절대 경로여야 합니다
    'enable_static_handler' => true,
]);
```

* `document_root`를 설정하고 `enable_static_handler`를 `true`로 설정하면, 하단이 `Http` 요청을 받았을 때 먼저 `document_root` 경로에 해당 파일이 있는지를 확인합니다. 해당 파일이 존재하는 경우에는 직접 파일 내용을 클라이언트에게 전송하고, 더 이상 [onRequest](/http_server?id=on) 콜백을 트리거하지 않습니다.
* 정적 파일 처리 기능을 사용하는 경우, 동적 PHP 코드와 정적 파일을 분리하여 두어야 하며, 정적 파일은 특정 디렉토리에 저장해야 합니다.


### max_concurrency

?> **`HTTP1/2` 서비스의 최대 병행 요청 수를 제한합니다. 초과하면 `503` 오류를 반환합니다. 기본값은 4294967295로, 즉 무符号 int의 최대값입니다.**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```


### worker_max_concurrency

?> **일괄 협상화가 활성화된 후, `worker` 프로세스는 요청을 끊임없이 받아들입니다. 부하가 너무 큰 경우, `worker` 프로세스의 요청 처리 수를 제한하기 위해 `worker_max_concurrency`를 설정할 수 있습니다. 요청 수가 이 값을 초과하면, `worker` 프로세스는 초과된 요청을 대기열에 보관합니다. 기본값은 4294967295로, 즉 무符号 int의 최대값입니다. `worker_max_concurrency`를 설정하지 않지만 `max_concurrency`를 설정한 경우, 하단은 자동으로 `worker_max_concurrency`을 `max_concurrency`와 동일하게 설정합니다.**

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Swoole 버전 >= `v5.0.0`에서 사용할 수 있습니다.
### http2_header_table_size

?> HTTP/2 네트워크 연결의 최대 `header table` 크기를 정의합니다.

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```


### http2_enable_push

?> 이 구성이 HTTP/2 푸시 기능을 활성화하거나 비활성화하는 데 사용됩니다.

```php
$server->set([
  'http2_enable_push' => 0x2
])
```


### http2_max_concurrent_streams

?> 각 HTTP/2 네트워크 연결에서 수용하는 멀티플렉스 스트림의 최대 수를 설정합니다.

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```


### http2_init_window_size

?> HTTP/2 트래픽 제어 창의 초기 크기를 설정합니다.

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```


### http2_max_frame_size

?> HTTP/2 네트워크 연결을 통해 전송되는 단일 HTTP/2 프로토콜 프레임의 본문의 최대 크기를 설정합니다.

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```

### http2_max_header_list_size

?> HTTP/2 스트림에서 요청에 보낼 수 있는 헤더의 최대 크기를 설정합니다. 

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
