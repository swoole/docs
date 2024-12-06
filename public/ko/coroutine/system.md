# 코루틴\시스템

시스템 관련 `API`의 코루틴 포장입니다. 이 모듈은 `v4.4.6` 정식 버전 이후에 사용 가능합니다. 대부분의 `API`는 `AIO` 스레드 풀을 기반으로 구현되어 있습니다.

!> `v4.4.6` 이전 버전의 경우, `Co` 심볼릭 이름이나 `Swoole\Coroutine`를 사용하여 호출하세요. 예: `Co::sleep` 또는 `Swoole\Coroutine::sleep`  
`v4.4.6` 및 이후 버전의 경우 공식적으로 **권장**되는 방법은 `Co\System::sleep` 또는 `Swoole\Coroutine\System::sleep`입니다.  
이 변경은 네임스페이스 규칙을 통일시키기 위한 것이지만, 동시에 하향 호환성을 보장합니다 (즉, `v4.4.6` 이전의 코드는 여전히 사용할 수 있으며, 변경할 필요가 없습니다).


## 방법


### statvfs()

파일 시스템 정보를 가져옵니다.

!> Swoole 버전 >= v4.2.5에서 사용할 수 있습니다.

```php
Swoole\Coroutine\System::statvfs(string $path): array|false
```

  * **매개변수** 

    * **`string $path`**
      * **기능**: 파일 시스템 마운트된 디렉터리 【예: `/` , `df` 및 `mount -l` 명령어로 얻을 수 있습니다】
      * **기본값**: 없음
      * **기타 값**: 없음

  * **사용 예제**

    ```php
    Swoole\Coroutine\run(function () {
        var_dump(Swoole\Coroutine\System::statvfs('/'));
    });
    ```
  * **출력 예제**
    
    ```php
    array(11) {
      ["bsize"]=>
      int(4096)
      ["frsize"]=>
      int(4096)
      ["blocks"]=>
      int(61068098)
      ["bfree"]=>
      int(45753580)
      ["bavail"]=>
      int(42645728)
      ["files"]=>
      int(15523840)
      ["ffree"]=>
      int(14909927)
      ["favail"]=>
      int(14909927)
      ["fsid"]=>
      int(1002377915335522995)
      ["flag"]=>
      int(4096)
      ["namemax"]=>
      int(255)
    }
    ```


### fread()

코루틴 방식으로 파일을 읽습니다.

```php
Swoole\Coroutine\System::fread(resource $handle, int $length = 0): string|false
```

!> `v4.0.4` 이전 버전의 `fread` 방법은 `STDIN`、`Socket`와 같은 비 파일 유형의 `stream`을 지원하지 않습니다. 이러한 자원을 `fread`로 조작하지 마십시오.  
`v4.0.4` 이후 버전의 `fread` 방법은 비 파일 유형의 `stream` 자원을 지원하며, 기본적으로 `stream` 유형에 따라 `AIO` 스레드 풀 또는 [EventLoop](/learn?id=무엇이eventloop인가요)를 선택하여 구현합니다.

!> 이 방법은 `5.0` 버전에서 이미 폐기되었으며, `6.0` 버전에서 제거되었습니다.

  * **매개변수** 

    * **`resource $handle`**
      * **기능**: 파일 핸들 【`fopen`으로 열어진 파일 유형의 `stream` 자원이어야 합니다】
      * **기본값**: 없음
      * **기타 값**: 없음

    * **`int $length`**
      * **기능**: 읽을 길이 【기본적으로 `0`로, 파일의 전체 내용을 읽을 것을 나타냅니다】
      * **기본값**: `0`
      * **기타 값**: 없음

  * **반환값** 

    * 성공 시 문자열 내용을 반환하고, 실패 시 `false`를 반환합니다.

  * **사용 예제**  

    ```php
    $fp = fopen(__FILE__, "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fread($fp);
        var_dump($r);
    });
    ```


### fwrite()

코루틴 방식으로 파일에 데이터를 쓰습니다.

```php
Swoole\Coroutine\System::fwrite(resource $handle, string $data, int $length = 0): int|false
```

!> `v4.0.4` 이전 버전의 `fwrite` 방법은 `STDIN`、`Socket`와 같은 비 파일 유형의 `stream`을 지원하지 않습니다. 이러한 자원을 `fwrite`로 조작하지 마십시오.  
`v4.0.4` 이후 버전의 `fwrite` 방법은 비 파일 유형의 `stream` 자원을 지원하며, 기본적으로 `stream` 유형에 따라 `AIO` 스레드 풀 또는 [EventLoop](/learn?id=무엇이eventloop인가요)를 선택하여 구현합니다.

!> 이 방법은 `5.0` 버전에서 이미 폐기되었으며, `6.0` 버전에서 제거되었습니다.

  * **매개변수** 

    * **`resource $handle`**
      * **기능**: 파일 핸들 【`fopen`으로 열어진 파일 유형의 `stream` 자원이어야 합니다】
      * **기본값**: 없음
      * **기타 값**: 없음

    * **`string $data`**
      * **기능**: 쓰을 데이터 내용 【텍스트 또는 이진 데이터일 수 있습니다】
      * **기본값**: 없음
      * **기타 값**: 없음

    * **`int $length`**
      * **기능**: 쓰는 길이 【기본적으로 `0`로, `$data`의 전체 내용을 쓰을 것을 나타냅니다. `$length`은 `$data`의 길이를 초과할 수 없습니다】
      * **기본값**: `0`
      * **기타 값**: 없음

  * **반환값** 

    * 성공 시 데이터 길이를 반환하고, 실패 시 `false`를 반환합니다.

  * **사용 예제**  

    ```php
    $fp = fopen(__DIR__ . "/test.data", "a+");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fwrite($fp, "hello world\n", 5);
        var_dump($r);
    });
    ```


### fgets()

코루틴 방식으로 파일 내용을 줄단위로 읽습니다.

밑바닥에는 `php_stream` 캐시 영역이 사용되며, 기본 크기는 `8192` 바이트입니다. 캐시 영역 크기를 설정하려면 `stream_set_chunk_size`를 사용할 수 있습니다.

```php
Swoole\Coroutine\System::fgets(resource $handle): string|false
```

!> `fgets` 함수는 파일 유형의 `stream` 자원에만 사용할 수 있으며, Swoole 버전 >= `v4.4.4`에서 사용할 수 있습니다.

!> 이 방법은 `5.0` 버전에서 이미 폐기되었으며, `6.0` 버전에서 제거되었습니다.

  * **매개변수** 

    * **`resource $handle`**
      * **기능**: 파일 핸들 【`fopen`으로 열어진 파일 유형의 `stream` 자원이어야 합니다】
      * **기본값**: 없음
      * **기타 값**: 없음

  * **반환값** 

    * `EOL` (`\r` 또는 `\n`)에 도달하면 한 줄의 데이터를 반환하며, `EOL`도 포함됩니다.
    * `EOL`에 도달하지 못했지만 내용 길이가 `php_stream` 캐시 영역 `8192` 바이트를 초과하면, `8192` 바이트의 데이터를 반환하며, `EOL`는 포함되지 않습니다.
    * 파일 끝에 도달했을 경우 (`EOF`), 빈 문자열을 반환하며, `feof`를 사용하여 파일이 완전히 읽혔는지 확인할 수 있습니다.
    * 실패 시 `false`를 반환하며, [swoole_last_error](/functions?id=swoole_last_error) 함수를 사용하여 오류 코드를 얻을 수 있습니다.

  * **사용 예제**  

    ```php
    $fp = fopen(__DIR__ . "/defer_client.php", "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fgets($fp);
        var_dump($r);
    });
    ```


### readFile()

코루틴 방식으로 파일을 읽습니다.

```php
Swoole\Coroutine\System::readFile(string $filename): string|false
```

  * **매개변수** 

    * **`string $filename`**
      * **기능**: 파일 이름
      * **기본값**: 없음
      * **기타 값**: 없음

  * **반환값** 

    * 성공 시 문자열 내용을 반환하고, 실패 시 `false`를 반환합니다. [swoole_last_error](/functions?id=swoole_last_error)를 사용하여 오류 정보를 얻을 수 있습니다.
    * `readFile` 방법은 크기 제한이 없으며, 읽힌 내용은 메모리에 보존되므로, 매우 큰 파일을 읽을 경우 메모리가 많이 차지할 수 있습니다.

  * **사용 예제**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $r = Swoole\Coroutine\System::readFile($filename);
        var_dump($r);
    });
    ```
### writeFile()

코루outine 방식으로 파일을 작성합니다.

```php
Swoole\Coroutine\System::writeFile(string $filename, string $fileContent, int $flags): bool
```

  * **매개변수** 

    * **`string $filename`**
      * **기능** : 파일 이름【수정 가능 권한이 있어야 하며, 파일이 존재하지 않을 경우 자동으로 생성됩니다. 파일 열기가 실패하면 즉시 `false`를 반환합니다】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`string $fileContent`**
      * **기능** : 파일에 쓰일 내용【최대 4MB까지 작성 가능】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`int $flags`**
      * **기능** : 작성 옵션【기본적으로 현재 파일 내용을 비우고, `FILE_APPEND`를 사용하면 파일 끝에 추가합니다】
      * **기본값** : 없음
      * **기타 값** : 없음

  * **반환값** 

    * 성공 시 `true`를 반환합니다
    * 실패 시 `false`를 반환합니다

  * **사용 예시**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $w = Swoole\Coroutine\System::writeFile($filename, "hello swoole!");
        var_dump($w);
    });
    ```


### sleep()

대기 상태로 돌입합니다.

PHP의 `sleep` 함수와 동일한 기능이지만, `Coroutine::sleep`는 [코루outine 스케줄러](/coroutine?id=코루outine 스케줄러)로 구현되어 있으며, 기본적으로 현재 코루outine을 `yield`하고 시간을 나누어 주며, 비동기 타이머를 추가합니다. 타이머가 만료될 경우 현재 코루outine을 `resume`하여 실행을 재개합니다.

`sleep` 인터페이스를 사용하면 편리하게 시간 초과 대기 기능을 구현할 수 있습니다.

```php
Swoole\Coroutine\System::sleep(float $seconds): void
```

  * **매개변수** 

    * **`float $seconds`**
      * **기능** : 대기 시간【`0`보다 크고 하루 시간(86400초)을 초과할 수 없습니다】
      * **값 단위** : 초, 최소 정확도가 밀리초(0.001초)입니다
      * **기본값** : 없음
      * **기타 값** : 없음

  * **사용 예시**  

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9502);

    $server->on('Request', function($request, $response) {
        //200ms 대기 후 브라우저에 응답을 보냅니다
        Swoole\Coroutine\System::sleep(0.2);
        $response->end("<h1>Hello Swoole!</h1>");
    });

    $server->start();
    ```


### exec()

shell 명령을 실행합니다. 기본적으로 코루outine 스케줄러가 자동으로 작동합니다.

```php
Swoole\Coroutine\System::exec(string $cmd): array
```

  * **매개변수** 

    * **`string $cmd`**
      * **기능** : 실행할 shell 명령
      * **기본값** : 없음
      * **기타 값** : 없음

  * **반환값**

    * 실패 시 `false`를 반환하고, 성공 시 프로세스 종료 상태 코드, 신호, 출력을 포함하는 배열을 반환합니다.

    ```php
    array(
        'code'   => 0,  // 프로세스 종료 상태 코드
        'signal' => 0,  // 신호
        'output' => '', // 출력 내용
    );
    ```

  * **사용 예시**  

    ```php
    Swoole\Coroutine\run(function() {
        $ret = Swoole\Coroutine\System::exec("md5sum ".__FILE__);
    });
    ```

  * **주의**

  !>脚本 명령 실행 시간이 너무 길면 시간 초과로 종료될 수 있습니다. 이러한 경우 [socket_read_timeout](/coroutine_client/init?id=시간 초과 규칙)을 늘리면 문제를 해결할 수 있습니다.


### gethostbyname()

도메인을 IP 주소로 해석합니다. 동기적인 스레드 풀을 기반으로 모의 실현되며, 기본적으로 코루outine 스케줄러가 자동으로 작동합니다.

```php
Swoole\Coroutine\System::gethostbyname(string $domain, int $family = AF_INET, float $timeout = -1): string|false
```

  * **매개변수** 

    * **`string $domain`**
      * **기능** : 도메인
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`int $family`**
      * **기능** : 도메인 패밀리【`AF_INET`는 `IPv4` 주소를 반환하고, `AF_INET6`는 `IPv6` 주소를 반환합니다】
      * **기본값** : `AF_INET`
      * **기타 값** : `AF_INET6`

    * **`float $timeout`**
      * **기능** : 시간 초과
      * **값 단위** : 초, 최소 정확도가 밀리초(0.001초)입니다
      * **기본값** : `-1`
      * **기타 값** : 없음

  * **반환값**

    * 성공 시 도메인에 해당하는 `IP` 주소를 반환하고, 실패 시 `false`를 반환합니다. [swoole_last_error](/functions?id=swoole_last_error)를 사용하여 오류 정보를 확인할 수 있습니다.

    ```php
    array(
        'code'   => 0,  // 프로세스 종료 상태 코드
        'signal' => 0,  // 신호
        'output' => '', // 출력 내용
    );
    ```

  * **확장**

    * **시간 초과 제어**

      `$timeout` 매개변수는 코루outine이 기다리는 시간 초과를 제어할 수 있습니다. 지정된 시간 내에 결과가 반환되지 않으면 코루outine은 즉시 `false`를 반환하고 다음 실행을 계속합니다. 기본적인底层 구현에서는 이 비동기 작업을 `cancel`로 표시하고, `gethostbyname`는 여전히 `AIO` 스레드 풀에서 계속 실행됩니다.
      
      `/etc/resolv.conf`를 수정하여 `gethostbyname`와 `getaddrinfo`의底层C 함수의 시간 초과를 설정할 수 있습니다. 자세한 내용은 [DNS 해석 시간 초과 및 재시도 설정](/learn_other?id=DNS 해석 시간 초과 및 재시도 설정)을 참고하세요.

  * **사용 예시**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::gethostbyname("www.baidu.com", AF_INET, 0.5);
        echo $ip;
    });
    ```


### getaddrinfo()

DNS 해석을 수행하여 도메인에 해당하는 `IP` 주소를 조회합니다.

`gethostbyname`와 달리, `getaddrinfo`는 더 많은 매개변수 설정을 지원하며, 여러 개의 `IP` 결과를 반환합니다.

```php
Swoole\Coroutine\System::getaddrinfo(string $domain, int $family = AF_INET, int $socktype = SOCK_STREAM, int $protocol = STREAM_IPPROTO_TCP, string $service = null, float $timeout = -1): array|false
```

  * **매개변수** 

    * **`string $domain`**
      * **기능** : 도메인
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`int $family`**
      * **기능** : 도메인 패밀리【`AF_INET`는 `IPv4` 주소를 반환하고, `AF_INET6`는 `IPv6` 주소를 반환합니다】
      * **기본값** : 없음
      * **기타 값** : 없음
      
      !> 기타 매개변수 설정은 `man getaddrinfo` 문서를 참고하세요.

    * **`int $socktype`**
      * **기능** : 프로토콜 유형
      * **기본값** : `SOCK_STREAM`
      * **기타 값** : `SOCK_DGRAM`, `SOCK_RAW`

    * **`int $protocol`**
      * **기능** : 프로토콜
      * **기본값** : `STREAM_IPPROTO_TCP`
      * **기타 값** : `STREAM_IPPROTO_UDP`, `STREAM_IPPROTO_STCP`, `STREAM_IPPROTO_TIPC`, `0`

    * **`string $service`**
      * **기능** :
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`float $timeout`**
      * **기능** : 시간 초과
      * **값 단위** : 초, 최소 정확도가 밀리초(0.001초)입니다
      * **기본값** : `-1`
      * **기타 값** : 없음

  * **반환값**

    * 성공 시 여러 개의 `IP` 주소로 구성된 배열을 반환하고, 실패 시 `false`를 반환합니다

  * **사용 예시**  

    ```php
    Swoole\Coroutine\run(function () {
        $ips = Swoole\Coroutine\System::getaddrinfo("www.baidu.com");
        var_dump($ips);
    });
    ```
### dnsLookup()

도메인 주소 조회.

`Coroutine\System::gethostbyname`와 달리 `Coroutine\System::dnsLookup`는 직접적으로 `UDP` 클라이언트 네트워크 통신을 구현하여 사용하며, `libc`가 제공하는 `gethostbyname` 함수를 사용하지 않습니다.

!> Swoole 버전 >= `v4.4.3`에서 사용할 수 있으며, 기본적으로 `/etc/resolve.conf`에서 `DNS` 서버 주소를 읽어 들입니다. 현재는 `AF_INET(IPv4)` 도메인 해석만 지원됩니다. Swoole 버전 >= `v4.7`에서는 세 번째 매개변수를 사용하여 `AF_INET6(IPv6)`을 지원할 수 있습니다.

```php
Swoole\Coroutine\System::dnsLookup(string $domain, float $timeout = 5, int $type = AF_INET): string|false
```

  * **매개변수** 

    * **`string $domain`**
      * **기능**：도메인
      * **기본값**：없음
      * **기타값**：없음

    * **`float $timeout`**
      * **기능**：토out 시간
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)
      * **기본값**：`5`
      * **기타값**：없음

    * **`int $type`**
        * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)
        * **기본값**：`AF_INET`
        * **기타값**：`AF_INET6`

    !> `$type` 매개변수는 Swoole 버전 >= `v4.7`에서 사용할 수 있습니다.

  * **반환값**

    * 해석에 성공하면 해당 IP 주소를 반환합니다.
    * 실패하면 `false`를 반환하며, [swoole_last_error](/functions?id=swoole_last_error)를 사용하여 오류 정보를 얻을 수 있습니다.

  * **일반적인 오류**

    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED`：해당 도메인이 해석되지 못하고 조회 실패
    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT`：해석 timeout, DNS 서버에 문제가 있어 정해진 시간 내에 결과를 반환하지 못합니다.

  * **사용 예시**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::dnsLookup("www.baidu.com");
        echo $ip;
    });
    ```


### wait()

기존의 [Process::wait](/process/process?id=wait)와 대조적으로 이 API는 코로코outine 버전이며, 코로코outine를 중지시킵니다. `Swoole\Process::wait` 및 `pcntl_wait` 함수를 대체할 수 있습니다.

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\System::wait(float $timeout = -1): array|false
```

* **매개변수** 

    * **`float $timeout`**
      * **기능**：토out 시간, 부정수는 영원히 토out되지 않음
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)
      * **기본값**：`-1`
      * **기타값**：없음

* **반환값**

  * 성공 시 자식 프로세스의 `PID`, 종료 상태 코드, 어떤 신호로 `KILL`했는지에 대한 배열을 반환합니다.
  * 실패 시 `false`를 반환합니다.

!> 각 자식 프로세스가 시작된 후에는 부모 프로세스가 코로코outine를 파견하여 `wait()`(또는 `waitPid()`)를 호출하여 수거해야 합니다. 그렇지 않으면 자식 프로세스가 zombie 프로세스가 되어 운영체의 프로세스 자원을 낭비하게 됩니다.  
코로코outine를 사용하려면 먼저 프로세스를 생성하고, 그 안에서 코로코outine를 시작해야 합니다. 반대로는 안 되며, 그렇지 않으면 코로코outine를 동반한 fork 상황이 매우 복잡해져서 바닥층에서 처리하기 어렵습니다.

* **예시**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::wait();
    assert($status['pid'] === $process->pid);
    var_dump($status);
});
```


### waitPid()

위의 wait와 기본적으로 동일하지만, 이 API는 특정 프로세스를 기다릴 수 있습니다.

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\System::waitPid(int $pid, float $timeout = -1): array|false
```

* **매개변수** 

    * **`int $pid`**
      * **기능**：프로세스 ID
      * **기본값**：`-1` (특정 프로세스가 아니라 모든 프로세스를 기다리는 것으로, 이때는 wait와 동일합니다)
      * **기타값**：임의의 자연수

    * **`float $timeout`**
      * **기능**：토out 시간, 부정수는 영원히 토out되지 않음
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)
      * **기본값**：`-1`
      * **기타값**：없음

* **반환값**

  * 성공 시 자식 프로세스의 `PID`, 종료 상태 코드, 어떤 신호로 `KILL`했는지에 대한 배열을 반환합니다.
  * 실패 시 `false`를 반환합니다.

!> 각 자식 프로세스가 시작된 후에는 부모 프로세스가 코로코outine를 파견하여 `wait()`(또는 `waitPid()`)를 호출하여 수거해야 합니다. 그렇지 않으면 자식 프로세스가 zombie 프로세스가 되어 운영체의 프로세스 자원을 낭비하게 됩니다.

* **예시**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::waitPid($process->pid);
    var_dump($status);
});
```


### waitSignal()

코로코outine 버전의 신호 리스너로, 현재 코로코outine를 신호가 발생할 때까지 막습니다. `Swoole\Process::signal` 및 `pcntl_signal` 함수를 대체할 수 있습니다.

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\System::waitSignal(int $signo, float $timeout = -1): bool
```

  * **매개변수** 

    * **`int $signo`**
      * **기능**：신호 유형
      * **기본값**：없음
      * **기타값**：SIG 시리즈 상수, 예를 들어 `SIGTERM`, `SIGKILL` 등

    * **`float $timeout`**
      * **기능**：토out 시간, 부정수는 영원히 토out되지 않음
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)
      * **기본값**：`-1`
      * **기타값**：없음

  * **반환값**

    * 신호를 받은 경우 `true`를 반환합니다.
    * 신호를 받지 못한 경우 `false`를 반환합니다.

  * **예시**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    Coroutine\run(function () {
        $bool = System::waitSignal(SIGUSR1);
        var_dump($bool);
    });
});
$process->start();
sleep(1);
$process::kill($process->pid, SIGUSR1);
```

### waitEvent()

코로코outine 버전의 신호 리스너로, 현재 코로코outine를 신호가 발생할 때까지 막습니다. IO 이벤트를 기다립니다. `swoole_event` 관련 함수를 대체할 수 있습니다.

!> Swoole 버전 >= `v4.5`에서 사용할 수 있습니다.

```php
Swoole\Coroutine\System::waitEvent(mixed $socket, int $events = SWOOLE_EVENT_READ, float $timeout = -1): int | false
```

* **매개변수** 

    * **`mixed $socket`**
      * **기능**： 파일 디스크립터 (대상으로 변환할 수 있는 모든 유형, 예를 들어 Socket 객체, 자원 등)
      * **기본값**：없음
      * **기타값**：없음

    * **`int $events`**
      * **기능**： 이벤트 유형
      * **기본값**：`SWOOLE_EVENT_READ`
      * **기타값**：`SWOOLE_EVENT_WRITE` 또는 `SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE`

    * **`float $timeout`**
      * **기능**： 토out 시간, 부정수는 영원히 토out되지 않음
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)
      * **기본값**：`-1`
      * **기타값**：없음

* **반환값**

  * 발생한 이벤트 유형의 비트 OR (`|`)을 반환합니다.
  * 실패 시 `false`를 반환하며, [swoole_last_error](/functions?id=swoole_last_error)를 사용하여 오류 정보를 얻을 수 있습니다.

* **예시**

> 동기적으로 막힌 코드는 이 API를 통해 코로코outine 비동기화할 수 있습니다.

```php
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = stream_socket_client('tcp://www.qq.com:80', $errno, $errstr, 30);
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
    assert($events === SWOOLE_EVENT_WRITE);
    fwrite($client, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ);
    assert($events === SWOOLE_EVENT_READ);
    $response = fread($client, 8192);
    echo $response;
});
```
