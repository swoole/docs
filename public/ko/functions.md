# 함수 목록

Swoole는 네트워크 통신과 관련된 함수 외에도 PHP 프로그램에서 사용할 수 있는 시스템 정보를 얻을 수 있는 몇 가지 함수를 제공합니다.


## swoole_set_process_name()

프로세스 이름을 설정하는 데 사용됩니다. 프로세스 이름을 변경한 후에는 ps 명령어로 볼 때 `php your_file.php`가 아닌 설정된 문자열이 될 것입니다.

이 함수는 문자열 매개변수를 받습니다.

이 함수는 PHP5.5에서 제공되는 [cli_set_process_title](https://www.php.net/manual/ko/function.cli-set-process-title.php) 기능과 동일합니다. 하지만 `swoole_set_process_name`은 PHP5.2 이상의 모든 버전에서 사용할 수 있습니다. `swoole_set_process_name`의 호환성은 `cli_set_process_title`보다 떨어지며, `cli_set_process_title` 함수가 존재하는 경우에는 우선 `cli_set_process_title`을 사용합니다.

```php
function swoole_set_process_name(string $name): void
```

사용 예시:

```php
swoole_set_process_name("swoole server");
```


### Swoole Server의 각 프로세스 이름을 어떻게 개명할 수 있나요 <!-- {docsify-ignore} -->

* [onStart](/server/events?id=onstart) 호출 시 메인 프로세스 이름 변경
* [onManagerStart](/server/events?id=onmanagerstart) 호출 시 관리 프로세스(`manager`) 이름 변경
* [onWorkerStart](/server/events?id=onworkerstart) 호출 시 워커 프로세스 이름 변경
 
!> 낮은 버전의 Linux 커널과 Mac OSX는 프로세스 개명을 지원하지 않습니다  


## swoole_strerror()

오류 코드를 오류 메시지로 변환합니다.

함수原型:

```php
function swoole_strerror(int $errno, int $error_type = 1): string
```

오류 유형:

* `1` : 표준 `Unix Errno`, 시스템 호출 오류에서 발생합니다. 예를 들어 `EAGAIN`, `ETIMEDOUT` 등
* `2` : `getaddrinfo` 오류 코드, `DNS` 작업에서 발생합니다.
* `9` : `Swoole` 하위层次의 오류 코드, `swoole_last_error()`를 사용하여 얻을 수 있습니다.

사용 예시:

```php
var_dump(swoole_strerror(swoole_last_error(), 9));
```


## swoole_version()

Swoole 확장의 버전을 가져옵니다. 예를 들어 `1.6.10`

```php
function swoole_version(): string
```

사용 예시:

```php
var_dump(SWOOLE_VERSION); // 전역 변수 SWOOLE_VERSION도 동일하게 swoole 확장 버전을 나타냅니다.
var_dump(swoole_version());
/**
반환값:
string(6) "1.9.23"
string(6) "1.9.23"
**/
```


## swoole_errno()

최근 시스템 호출의 오류 코드를 가져옵니다. 이는 `C/C++`의 `errno` 변수와 동일합니다.

```php
function swoole_errno(): int
```

오류 코드의 값은 운영 체제와 관련이 있습니다. `swoole_strerror`를 사용하여 오류를 오류 메시지로 변환할 수 있습니다.


## swoole_get_local_ip()

본 기의 모든 네트워크 인터페이스의 IP 주소를 가져옵니다.

```php
function swoole_get_local_ip(): array
```

사용 예시:

```php
// 본 기의 모든 네트워크 인터페이스의 IP 주소를 가져옵니다.
$list = swoole_get_local_ip();
print_r($list);
/**
반환값
Array
(
      [eno1] => 10.10.28.228
      [br-1e72ecd47449] => 172.20.0.1
      [docker0] => 172.17.0.1
)
**/
```

!>주의
* 현재는 IPv4 주소만 반환하며, 결과는 로컬 루프 주소 127.0.0.1를 필터링합니다.
* 결과 배열은 인터페이스 이름을 키로 하는 연관 배열입니다. 예를 들어 `array("eth0" => "192.168.1.100")`
* 이 함수는 인터페이스 정보를 실시간으로 `ioctl` 시스템 호출을 통해 가져오며, 하위에서 캐시가 없습니다.


## swoole_clear_dns_cache()

Swoole 내장 DNS 캐시를 제거합니다. `swoole_client`와 `swoole_async_dns_lookup`에 효과적입니다.

```php
function swoole_clear_dns_cache()
```


## swoole_get_local_mac()

본 기 네트워크 카드의 `Mac` 주소를 가져옵니다.

```php
function swoole_get_local_mac(): array
```

* 성공 시 모든 네트워크 카드의 `Mac` 주소를 반환합니다.

```php
array(4) {
  ["lo"]=>
  string(17) "00:00:00:00:00:00"
  ["eno1"]=>
  string(17) "64:00:6A:65:51:32"
  ["docker0"]=>
  string(17) "02:42:21:9B:12:05"
  ["vboxnet0"]=>
  string(17) "0A:00:27:00:00:00"
}
```


## swoole_cpu_num()

본 기 CPU 코어 수를 가져옵니다.

```php
function swoole_cpu_num(): int
```

* 성공 시 CPU 코어 수를 반환합니다. 예를 들어:

```shell
php -r "echo swoole_cpu_num();"
```


## swoole_last_error()

최근 Swoole 하위层次의 오류 코드를 가져옵니다.

```php
function swoole_last_error(): int
```

`swoole_strerror(swoole_last_error(), 9)`를 사용하여 오류를 오류 메시지로 변환할 수 있으며, 전체 오류 메시지 목록은 [Swoole 오류 코드 목록](/other/errno?id=swoole)을 참고하세요.


## swoole_mime_type_add()

새로운 MIME 유형을 내장 MIME 유형 테이블에 추가합니다.

```php
function swoole_mime_type_add(string $suffix, string $mime_type): bool
```


## swoole_mime_type_set()

어떤 MIME 유형을 수정합니다. 실패(존재하지 않는 경우)는 `false`를 반환합니다.

```php
function swoole_mime_type_set(string $suffix, string $mime_type): bool
```


## swoole_mime_type_delete()

어떤 MIME 유형을 제거합니다. 실패(존재하지 않는 경우)는 `false`를 반환합니다.

```php
function swoole_mime_type_delete(string $suffix): bool
```


## swoole_mime_type_get()

파일 이름에 해당하는 MIME 유형을 가져옵니다.

```php
function swoole_mime_type_get(string $filename): string
```


## swoole_mime_type_exists()

후缀에 해당하는 MIME 유형이 존재하는지 확인합니다.

```php
function swoole_mime_type_exists(string $suffix): bool
```


## swoole_substr_json_decode()

Zero-copy JSON 역해상화, `$offset`와 `$length`를 제외하고는 [json_decode](https://www.php.net/manual/en/function.json-decode.php)와 다른 매개변수입니다.

!> Swoole 버전 >= `v4.5.6`에서 사용할 수 있으며, `v4.5.7` 버전부터는 컴파일 시 [--enable-swoole-json](/environment?id=通用参数) 옵션을 추가하여启用해야 합니다. 사용 시나리오는 [Swoole 4.5.6 Zero-copy JSON 또는 PHP 역해상화 지원](https://wenda.swoole.com/detail/107587)을 참고하세요.

```php
function swoole_substr_json_decode(string $packet, int $offset, int $length, bool $assoc = false, int $depth = 512, int $options = 0)
```

  * **예시**

```php
$val = json_encode(['hello' => 'swoole']);
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(json_decode(substr($str, 4, $l), true));
var_dump(swoole_substr_json_decode($str, 4, $l, true));
```


## swoole_substr_unserialize()

Zero-copy PHP 역해상화, `$offset`와 `$length`를 제외하고는 [unserialize](https://www.php.net/manual/en/function.unserialize.php)와 다른 매개변수입니다.

!> Swoole 버전 >= `v4.5.6`에서 사용할 수 있습니다. 사용 시나리오는 [Swoole 4.5.6 Zero-copy JSON 또는 PHP 역해상화 지원](https://wenda.swoole.com/detail/107587)을 참고하세요.

```php
function swoole_substr_unserialize(string $packet, int $offset, int $length, array $options= [])
```

  * **예시**

```php
$val = serialize('hello');
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(unserialize(substr($str, 4, $l)));
var_dump(swoole_substr_unserialize($str, 4, $l));
```


## swoole_error_log()

오류 메시지를 로그에 출력합니다. `$level`은 [로그 레벨](/consts?id=로그 레벨)입니다.

!> Swoole 버전 >= `v4.5.8`에서 사용할 수 있습니다

```php
function swoole_error_log(int $level, string $msg)
```
## swoole_clear_error()

소켓의 오류나 마지막 오류 코드에 대한 오류를 제거합니다.

!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다.

```php
function swoole_clear_error()
```


## swoole_coroutine_socketpair()

코루outine 버전의 [socket_create_pair](https://www.php.net/manual/ko/function.socket-create-pair.php) 함수입니다.

!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다.

```php
function swoole_coroutine_socketpair(int $domain , int $type , int $protocol): array|bool
```


## swoole_async_set

이 함수는 비동기 `IO` 관련 옵션을 설정할 수 있습니다.

```php
function swoole_async_set(array $settings)
```



- enable_signalfd: `signalfd` 기능의 사용을 켜고 끌수 있습니다.

- enable_coroutine: 내장 코루outine을 켜고 끌수 있습니다.[ 자세한 내용은(/server/setting?id=enable_coroutine) 참조]

- aio_core_worker_num: AIO 최소 프로세스 수를 설정합니다.

- aio_worker_num: AIO 최대 프로세스 수를 설정합니다.


## swoole_error_log_ex()

특정 레벨과 오류 코드의 로그를 작성합니다.

```php
function swoole_error_log_ex(int $level, int $error, string $msg)
```

!> Swoole 버전 >= `v4.8.1`에서 사용할 수 있습니다.

## swoole_ignore_error()

특정 오류 코드의 오류 로그를 무시합니다.

```php
function swoole_ignore_error(int $error)
```

!> Swoole 버전 >= `v4.8.1`에서 사용할 수 있습니다.
