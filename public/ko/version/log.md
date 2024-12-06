# 버전 업데이트 기록

`v1.5` 버전부터 엄격한 버전 업데이트 기록을 유지하고 있습니다. 현재 평균 반복 시간은 매년 한 번의 주요 버전과 `2-4주`당 한 번의 소규모 버제로 유지되고 있습니다.


## 권장하는 PHP 버전

* 8.0
* 8.1
* 8.2
* 8.3


## 권장하는 Swoole 버전
`Swoole6.x`와 `Swoole5.x`

두 버전의 차이점은 다음과 같습니다: `v6.x`는 적극적인 반복 분기이며, `v5.x`는 **비** 적극적인 반복 분기로, 오직 `BUG` 수정만을 포함합니다.

!> `v4.x` 이상 버전은 [enable_coroutine](/server/setting?id=enable_coroutine)를 설정하여 코루틴 기능을 비활성화할 수 있으며, 이를 비코루틴 버전으로 만듭니다.


## 버전 유형

* `alpha` 특성 미리보기 버전으로, 개발 계획 중의 작업이 완료되어 공개 미리보기가 진행 중이며, 많은 `BUG`이 발생할 수 있습니다.
* `beta` 테스트 버전으로, 이미 개발 환경에서 테스트 가능하며, `BUG`이 발생할 수 있습니다.
* `rc[1-n]` 후보 출시 버전으로, 출시 주기에 진입하여 대규모 테스트를 진행 중이며, 이 기간 동안에도 `BUG`이 발견될 수 있습니다.
* 후缀가 없으면 안정 버전으로, 해당 버전이 완전히 개발되어 정식으로 사용될 수 있습니다.


## 현재 버전 정보를 확인하기

```shell
php --ri swoole
```


## v6.0.0




### 새로운 특징

- `Swoole`은 멀티스레드 모드를 지원하며, PHP가 `zts` 모드일 때, `Swoole`을编译할 때 `--enable-swoole-thread` 옵션을 적용하면 멀티스레드 모드를 사용할 수 있습니다.

- 새로 추가된 스레드 관리 클래스 `Swoole\Thread` @matyhtf

- 새로 추가된 스레드 잠금 `Swoole\Thread\Lock` @matyhtf

- 새로 추가된 스레드 원자 계산 `Swoole\Thread\Atomic`, `Swoole\Thread\Atomic\Long` @matyhtf

- 새로 추가된 안전 병렬 컨테이너 `Swoole\Thread\Map`, `Swoole\Thread\ArrayList`, `Swoole\Thread\Queue` @matyhtf

- 파일 비동기 조작에 `iouring`를 기본 엔진으로 지원하며, `liburing`을 설치하고 `Swoole`编译 시 `--enable-iouring` 옵션을 적용하면, `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize` 등의 비동기 조작이 `iouring`로 구현됩니다. @matyhtf @NathanFreeman
- `Boost Context` 버전을 1.84로 업데이트하였습니다. 이제, 롱코어 CPU에서도 코루틴을 사용할 수 있게 되었습니다. @NathanFreeman




### Bug 수정

- `pecl`을 통해 설치할 수 없는 문제를 수정하였습니다. @remicollet

- `Swoole\Coroutine\FastCGI\Client` 클라이언트의 keepalive 설정이 불가능한 문제를 수정하였습니다. @NathanFreeman

- 요청 파라미터가 `max_input_vars`을 초과할 경우 프로세스가 계속 재시작하는 오류를 발생시켰던 문제를 수정하였습니다. @NathanFreeman

- 코루틴에서 `Swoole\Event::wait()`를 사용하면 알 수 없는 문제가 발생하는 문제를 수정하였습니다. @matyhtf

- 코루틴화 시 `proc_open`이 pty를 지원하지 않는 문제를 수정하였습니다. @matyhtf

- PHP8.3에서 `pdo_sqlite`이 segfault를 일으키는 문제를 수정하였습니다. @NathanFreeman

- `Swoole`编译 시 발생하는 무용한 경고를 수정하였습니다. @Appla @NathanFreeman

- `STDOUT/STDERR`가 이미 닫혀 있을 경우, zend_fetch_resource2_ex를底层에서 호출하면 오류가 발생하는 문제를 수정하였습니다. @Appla @matyhtf

- 무효한 `set_tcp_nodelay` 설정이 발생하는 문제를 수정하였습니다. @matyhtf

- 파일 업로드 시 가끔 도달할 수 없는 분岐 문제를 발생시켰던 문제를 수정하였습니다. @NathanFreeman

- `dispatch_func`를 설정하면 PHP底层에서 오류가 발생하는 문제를 수정하였습니다. @NathanFreeman

- autoconf >= 2.70 버전에서는 AC_PROG_CC_C99이 이미 구식이 되었음을 수정하였습니다. @petk

- 스레드 생성이 실패할 경우 발생하는 예외를 포착하였습니다. @matyhtf

- `_tsrm_ls_cache`가 정의되지 않는 문제를 수정하였습니다. @jingjingxyk
- `GCC 14`에서编译될 경우 치명적인 오류가 발생하는 문제를 수정하였습니다. @remicollet




### 커널 최적화

- `socket structs`에 대한 무용한 검사를 제거하였습니다. @petk

- swoole Library을 업데이트하였습니다. @deminy

- `Swoole\Http\Response`에 451 상태코드 지원이 추가되었습니다. @abnegate

- PHP의 다양한 버전의 `file` 조작 코드를 동기화하였습니다. @NathanFreeman

- PHP의 다양한 버전의 `pdo` 조작 코드를 동기화하였습니다. @NathanFreeman

- `Socket::ssl_recv()`의 코드를 최적화하였습니다. @matyhtf

- config.m4를 최적화하여 일부 설정에 대해 `pkg-config`를 통해 의존 라이브러리 위치를 설정할 수 있도록 변경하였습니다. @NathanFreeman

- 요청 헤드를 `解析`할 때 동적 배열을 사용하는 문제를 최적화하였습니다. @NathanFreeman

- 멀티스레드 모드에서 파일 디스크립터 `fd`의 수명 주기 문제를 최적화하였습니다. @matyhtf
- 코루틴의 일부 기본 논리를 최적화하였습니다. @matyhtf




### 버림

- 더 이상 `PHP 8.0` 지원이 중단됩니다.

- 더 이상 `Swoole\Coroutine\MySQL` 코루틴 클라이언트 지원이 중단됩니다.

- 더 이상 `Swoole\Coroutine\Redis` 코루틴 클라이언트 지원이 중단됩니다.

- 더 이상 `Swoole\Coroutine\PostgreSQL` 코루틴 클라이언트 지원이 중단됩니다.


## v5.1.3



### Bug 수정:

- `pecl`을 통해 설치할 수 없는 문제를 수정하였습니다.

- `Swoole\Coroutine\FastCGI\Client` 클라이언트의 keepalive 설정이 불가능한 문제를 수정하였습니다.

- 요청 파라미터가 `max_input_vars`을 초과할 경우 프로세스가 계속 재시작하는 오류를 발생시켰던 문제를 수정하였습니다.

- 코루틴에서 `Swoole\Event::wait()`를 사용하면 알 수 없는 문제가 발생하는 문제를 수정하였습니다.

- 코루틴화 시 `proc_open`이 pty를 지원하지 않는 문제를 수정하였습니다.

- PHP8.3에서 `pdo_sqlite`이 segfault를 일으키는 문제를 수정하였습니다.

- `Swoole`编译 시 발생하는 무용한 경고를 수정하였습니다.

- `STDOUT/STDERR`가 이미 닫혀 있을 경우, zend_fetch_resource2_ex를底层에서 호출하면 오류가 발생하는 문제를 수정하였습니다.

- 무효한 `set_tcp_nodelay` 설정이 발생하는 문제를 수정하였습니다.

- 파일 업로드 시 가끔 도달할 수 없는 분岐 문제를 발생시켰던 문제를 수정하였습니다.

- `dispatch_func`를 설정하면 PHP底层에서 오류가 발생하는 문제를 수정하였습니다.
- autoconf >= 2.70 버전에서는 AC_PROG_CC_C99이 이미 구식이 되었음을 수정하였습니다.




### 커널 최적화:

- `socket structs`에 대한 무용한 검사를 제거하였습니다.

- swoole Library을 업데이트하였습니다.

- `Swoole\Http\Response`에 451 상태코드 지원이 추가되었습니다.

- PHP의 다양한 버전의 `file` 조작 코드를 동기화하였습니다.

- PHP의 다양한 버전의 `pdo` 조작 코드를 동기화하였습니다.

- `Socket::ssl_recv()`의 코드를 최적화하였습니다.

- config.m4를 최적화하여 일부 설정에 대해 `pkg-config`를 통해 의존 라이브러리 위치를 설정할 수 있도록 변경하였습니다. 
- 요청 헤드를 `解析`할 때 동적 배열을 사용하는 문제를 최적화하였습니다. 


## v5.1.2




### Bug 수정

- 내장 sapi 지원이 추가되었습니다.

- PHP 8.3에서 ZEND_CHECK_STACK_LIMIT의 호환성 문제가 수정되었습니다.

- 범위 요청에서 파일 전체 내용을 반환할 때 Content-Range 응답 헤드가 없는 오류가 수정되었습니다.

- 잘려진 cookie가 수정되었습니다.

- PHP 8.3에서 native-curl이 충돌하는 문제가 수정되었습니다.
- Server::Manager::wait() 후의 무효한 errno 오류가 수정되었습니다.
- HTTP2의 철자 오류가 수정되었습니다.



### 최적화

- HTTP 서버 성능이 최적화되었습니다.
- websocket의 유효한 종료 이유로 CLOSE_SERVICE_RESTART, CLOSE_TRY_AGAIN_LATER, CLOSE_BAD_GATEWAY가 추가되었습니다.


## v5.1.1



### 버그 수정

- `http 커스텀 클라이언트`의 메모리 누수 문제가 수정되었습니다.

- `pdo_odbc`의 커스텀화 문제가 수정되었습니다.

- `socket_import_stream()` 실행 오류가 수정되었습니다.

- `Context::parse_multipart_data()`가 빈 요청체를 처리하지 못하는 문제가 수정되었습니다.

- `PostgreSQL 커스텀 클라이언트`의 매개변수가 작동하지 않는 문제가 수정되었습니다.

- `curl`이析构될 때崩溃하는 버그가 수정되었습니다.

- `Swoole5.x`와 새로운 버전의 `xdebug`와 호환성이 없는 문제가 수정되었습니다.

- `자동 로딩` 과정에서 커스텀 전환이 일어나 `클래스가 존재하지 않습니다`라는 메시지가 뜨는 문제가 수정되었습니다.
- `OpenBSD`에서 `swoole`를编译할 수 없는 문제가 수정되었습니다.


## v5.1.0




### 신규 기능

- `pdo_pgsql`에 대한 커스텀화 지원이 추가되었습니다.

- `pdo_odbc`에 대한 커스텀화 지원이 추가되었습니다.

- `pdo_oci`에 대한 커스텀화 지원이 추가되었습니다.

- `pdo_sqlite`에 대한 커스텀화 지원이 추가되었습니다.
- `pdo_pgsql`, `pdo_odbc`, `pdo_oci`, `pdo_sqlite`에 대한 커넥션 풀 설정이 추가되었습니다.




### 강화
- `Http\Server`의 성능이 개선되어, 극한 상황에서 `60%`까지 향상됩니다.




### 수정

- `WebSocket` 커스텀 클라이언트의 요청마다 메모리가 누수되는 문제가 수정되었습니다.

- `http 커스텀 서버`의 우아한 종료로 인해 클라이언트가 종료되지 않는 문제가 수정되었습니다.

-编译時に `--enable-thread-context` 옵션을 추가하면 `Process::signal()`가 작동하지 않는 문제가 수정되었습니다.

- `SWOOLE_BASE` 모드에서 프로세스가 비정상적으로 종료될 때, 연결 수 통계 오류가 수정되었습니다.

- `stream_select()` 함수의 서명 오류가 수정되었습니다.

- 파일 MIME 정보의 대소문자 민감성 오류가 수정되었습니다.

- `Http2\Request::$usePipelineRead`의 철자 오류로 인해 PHP8.2 환경에서 경고가 발생하는 문제가 수정되었습니다.

- `SWOOLE_BASE` 모드에서의 메모리 누수 문제가 수정되었습니다.

- `Http\Response::cookie()`가 쿠키의 만료 시간을 설정하면 메모리가 누수되는 문제가 수정되었습니다.

- `SWOOLE_BASE` 모드에서의 연결 누수 문제가 수정되었습니다.




### 커널

- swoole이 php8.3에서 php_url_encode의 함수 서명 문제를 수정하였습니다.

- 단위 테스트 옵션 문제를 수정하였습니다.

- 코드를 최적화하고 재구성하였습니다.

- PHP8.3와 호환됩니다.
- 32비트 운영 체제에서编译되지 않습니다.


## v5.0.3




### 강화

- `--with-nghttp2_dir` 옵션을 추가하여 시스템의 `nghttp2` 라이브러리를 사용할 수 있도록 변경하였습니다.

- 바이트 길이 또는 크기에 관련된 옵션이 추가되었습니다.

- `Process\Pool::sendMessage()` 함수가 추가되었습니다.

- `Http\Response:cookie()`가 `max-age`를 지원하도록 변경하였습니다.




### 수정
- `Server task/pipemessage/finish` 이벤트로 인한 메모리 누수 문제를 수정하였습니다.




### 커널

- `http` 응답 헤더 충돌로 인해 오류가 발생하지 않도록 변경하였습니다.

- `Server` 연결이 닫힐 경우 오류가 발생하지 않도록 변경하였습니다.


## v5.0.2




### 강화

- `http2`의 기본 설정을 구성할 수 있는 기능이 추가되었습니다.

- 8.1 또는 그 이상 버전의 `xdebug`를 지원하도록 변경하였습니다.

- 원래의 cURL을 재구성하여 여러 소켓을 가진 cURL 핸들을 지원하도록 변경하였습니다. 예: cURL ftp 프로토콜

- `Process::setPriority/getPriority`에 `who` 매개변수가 추가되었습니다.

- `Coroutine\Socket::getBoundCid()` 메서드가 추가되었습니다.

- `Coroutine\Socket::recvLine/recvWithBuffer` 메서드의 `length` 매개변수의 기본값을 `65536`로 변경하였습니다.

- 커스텀 전환 종료 특성을 재구성하여 메모리 해제가 더 안전하게 변경되었으며, 치명적인 오류가 발생할 때의 충돌 문제를 해결하였습니다.

- `Coroutine\Client`, `Coroutine\Http\Client`, `Coroutine\Http2\Client`에 `socket` 속성이 추가되어 직접 `socket` 자원을 조작할 수 있도록 변경하였습니다.

- `Http\Server`가 `http2` 클라이언트에게 빈 파일을 보낼 수 있도록 변경하였습니다.

- `Coroutine\Http\Server`의 우아한 재시작을 지원합니다. 서버가 닫힐 경우, 클라이언트 연결은 강제적으로 닫히지 않고 새로운 요청만 중지합니다.

- `pcntl_rfork`와 `pcntl_sigwaitinfo`를 불안정 함수 리스트에 추가하고, 커스텀 컨테이너가 시작될 때 이 함수들이 kapat됩니다.

- `SWOOLE_BASE` 모드 프로세스 관리자를 재구성하여, 닫기 및 재载入 행위가 `SWOOLE_PROCESS`와 일치하도록 변경하였습니다.


## v5.0.1




### 강화

- `PHP-8.2`에 대한 지원이 추가되었으며, 커스텀 예외 처리를 개선하여 `ext-soap`와 호환되도록 변경하였습니다.

- `pgsql` 커스텀 클라이언트의 LOB 지원이 추가되었습니다.

- `websocket` 클라이언트가 개선되어, 헤더에 `websocket`가 포함되도록 변경하였습니다.

- `http 클라이언트`가 최적화되어, 서버에서 `connection close`를 보낼 경우 `keep-alive`가 비활성화됩니다.

- `http 클라이언트`가 최적화되어, 압축 라이브러리가 없는 상황에서 `Accept-Encoding` 헤더를 추가하는 것을 금지합니다.

- 디버그 정보가 개선되어, `PHP-8.2`에서 암호를 민감한 매개변수로 설정합니다.

- `Server::taskWaitMulti()`가 강화되어, 커스텀 환경에서는 block되지 않습니다.

- 로깅 함수가 최적화되어, 로그 파일에 쓰기 실패 시에는 더 이상 화면에 출력되지 않습니다.




### 수정

- `Coroutine::printBackTrace()`와 `debug_print_backtrace()`의 매개변수 호환성 문제가 수정되었습니다.

- `Event::add()`이 소켓 자원에 대한 지원이 수정되었습니다.

- `zlib`가 없는 상황에서의编译错误가 수정되었습니다.

- 예상치 못한 문자열로 해석될 때 서버 작업이 충돌하는 문제가 수정되었습니다.

- `1ms` 미만의 타이머를 추가하는 것이 강제적으로 `0`로 설정되는 문제가 수정되었습니다.

- 열을 추가하기 전에 `Table::getMemorySize()`를 사용하면 충돌하는 문제가 수정되었습니다.

- `Http\Response::setCookie()` 메서드의 만료 시간 매개변수 이름이 `expires`로 변경되었습니다.


## v5.0.0




### 신규 기능

- `Server`에 `max_concurrency` 옵션이 추가되었습니다.

- `Coroutine\Http\Client`에 `max_retries` 옵션이 추가되었습니다.

- `name_resolver` 글로벌 옵션이 추가되었습니다. `Server`에 `upload_max_filesize` 옵션이 추가되었습니다.

- `Coroutine::getExecuteTime()` 메서드가 추가되었습니다.

- `Server`에 `SWOOLE_DISPATCH_CONCURRENT_LB`의 `dispatch_mode`가 추가되었습니다.

- 유형 시스템이 강화되어, 모든 함수의 매개변수와 반환값에 유형이 추가되었습니다.

- 오류 처리가 최적화되어, 모든 생성자가 실패할 경우 예외를 던집니다.

- `Server`의 기본 모드가 조정되어, 기본적으로 `SWOOLE_BASE` 모드로 설정됩니다.

- `pgsql` 커스텀 클라이언트가 핵심 라이브러리로 이관되었습니다. `4.8.x` 브랜치의 모든 `bug`가 수정되었습니다.




### 제거

- `PSR-0` 스타일의 클래스명이 제거되었습니다.

- 닫기 함수에서 자동으로 `Event::wait()`를 추가하는 기능이 제거되었습니다.

- `Server::tick/after/clearTimer/defer`의 별명이 제거되었습니다.

- `--enable-http2/--enable-swoole-json`가 제거되고, 기본적으로 활성화되도록 변경되었습니다.




### 폐기

- 커스텀 클라이언트 `Coroutine\Redis`와 `Coroutine\MySQL`가 기본적으로 폐기됩니다.


## v4.8.13

```


### 강화

- 원시 cURL을 재구성하여 FTP 프로토콜과 같이 여러 소켓을 지원하는 cURL 핸들을 지원하도록 변경

- `http2` 설정을 수동으로 설정할 수 있도록 개선

- `WebSocket 클라이언트`를 개선하고, 헤더에 `websocket`가 포함되어 `equal`이 아닌 형식으로 업데이트

- HTTP 클라이언트를 최적화하여, 서버에서 연결 종료를 보낼 때 `keep-alive`가 비활성화되도록 변경

- 디버그 정보를 개선하여, PHP-8.2에서 비밀번호를 민감한 매개변수로 설정하도록 변경

- `HTTP Range Requests` 지원 추가



### 수정

- `Coroutine::printBackTrace()`와 `debug_print_backtrace()`의 매개변수 호환성 문제를 수정

- `WebSocket` 서버에서 동시에 `HTTP2`와 `WebSocket` 프로토콜을 활성화할 때 길이 오류가 발생하는 문제를 수정

- `Server::send()`, `Http\Response::end()`, `Http\Response::write()`, `WebSocket/Server::push()`에서 `send_yield`이 발생할 때 메모리 누수 문제가 발생하는 문제를 수정

- 열을 추가하기 전에 `Table::getMemorySize()`를 사용하면 충돌이 발생하는 문제를 수정


## v4.8.12




### 강화

- PHP8.2 지원 추가

- `Event::add()` 함수에서 `sockets resources` 지원 추가

- `Http\Client::sendfile()`에서 4GB 초과의 파일을 지원하도록 변경

- `Server::taskWaitMulti()`에서 코어 환경을 지원하도록 변경




### 수정

- 잘못된 `multipart body`를 수신했을 때 오류 메시지가 발생하는 문제를 수정

- `1ms` 미만의 타이머超时 시간으로 인한 오류를 수정

- 디스크가 가득 차서 데드록이 발생하는 문제를 수정


## v4.8.11




### 강화

- `Intel CET` 보안 방어 메커니즘 지원 추가

- `Server::$ssl` 속성 추가

- `pecl`으로 `swoole`를编译할 때 `enable-cares` 옵션을 추가

- `multipart_parser` 해석기를 재구성




### 수정

- `pdo` 지속 연결을 사용하면 예외가 발생해 segfault가 나는 문제를 수정

- 디스펄션이 코어인 경우 segfault가 나는 문제를 수정

- `Server::close()`의 잘못된 오류 메시지를 수정


## v4.8.10


### 수정



- `stream_select`의超时 매개변수가 `1ms` 미만일 때를 `0`로 재설정

- `-Werror=format-security`를编译 시 사용하면编译이 실패하는 문제를 수정

- `curl`을 사용하면 `Swoole\Coroutine\Http\Server`에서 segfault가 나는 문제를 수정


## v4.8.9


### 강화

- `Http2` 서버에서 `http_auto_index` 옵션을 지원하도록 변경


### 수정



- `Cookie` 해석기를 최적화하여 `HttpOnly` 옵션을 전달할 수 있도록 변경

- #4657를 수정하고, `socket_create` 메서드의 리턴 타입 문제를 수정

- `stream_select` 메모리 누수 문제를 수정


### CLI 업데이트



- `CygWin`에서 SSL 인증서 체인을 포함시켜 SSL 인증이 잘못되는 문제를 해결

- PHP-8.1.5로 업데이트


## v4.8.8


### 최적화



- SW_IPC_BUFFER_MAX_SIZE를 64k로 줄이고, http2의 header_table_size 설정을 최적화


### 수정



- `enable_static_handler`를 사용하여 정적 파일을 다운로드할 때 많은 소켓 오류가 발생하는 문제를 수정

- http2 server의 NPN 오류를 수정


## v4.8.7


### 강화

- curl_share 지원 추가


### 수정



- arm32 아키텍처에서 정의되지 않은 상징 오류를 수정

- `clock_gettime()` 호환성을 수정

- 커널에서 큰 블록 메모리가 부족할 때 PROCESS 모드 서버에서 전송이 실패하는 문제를 수정


## v4.8.6


### 수정



- boost/context API 이름에 접두사 추가

- 구성 옵션 최적화


## v4.8.5


### 수정



- Table의 매개변수 타입을 복원

- Websocket 프로토콜을 사용하여 잘못된 데이터를 수신했을 때 crash가 나는 문제를 수정


## v4.8.4


### 수정



- sockets hook이 PHP-8.1과의 호환성 문제를 수정

- Table이 PHP-8.1과의 호환성 문제를 수정

- 일부 상황에서 코어 스타일의 HTTP 서버가 `Content-Type`이 `application/x-www-form-urlencoded`인 `POST` 매개변수를 예상치 못하게 해석하는 문제를 수정


## v4.8.3


### 신규 API



- `Coroutine\Socket::isClosed()` 메서드 추가


### 수정



- curl native hook이 PHP8.1 버전에서 호환성 문제가 발생하는 문제를 수정

- sockets hook이 PHP8에서 호환성 문제가 발생하는 문제를 수정

- sockets hook 함수의 반환값이 잘못되는 문제를 수정

- Http2Server sendfile에서 content-type가 설정되지 않는 문제를 수정

- HttpServer date header의 성능을 최적화하고, 캐시를 추가


## v4.8.2


### 수정



- proc_open hook의 메모리 누수 문제를 수정

- curl native hook이 PHP-8.0 및 PHP-8.1과의 호환성 문제가 발생하는 문제를 수정

- Manager 프로세스에서 연결을 정상적으로 종료하지 못하는 문제를 수정

- Manager 프로세스에서 sendMessage을 사용할 수 없는 문제를 수정

- `Coroutine\Http\Server`이 초과한 POST 데이터를 수신했을 때解析이 예외로 발생하는 문제를 수정

- PHP8 환경에서 치명적인 오류가 발생했을 때 직접 종료할 수 없는 문제를 수정

- coroutine의 `max_concurrency` 구성 항목을 조정하여, Co::set()에서만 사용하도록 변경

- `Coroutine::join()`에서 존재하지 않는 코어를 무시하도록 변경


## v4.8.1


### 신규 API



- `swoole_error_log_ex()`와 `swoole_ignore_error()` 함수 추가 (#4440) (@matyhtf)


### 강화



- ext-swoole_plus의 admin api를 ext-swoole으로 이관 (#4441) (@matyhtf)

- admin server에 get_composer_packages 명령어 추가 (swoole/library@07763f46) (swoole/library@8805dc05) (swoole/library@175f1797) (@sy-records) (@yunbaoi)

- POST 방식의 작동에 대한 제한을 추가 (swoole/library@ac16927c) (@yunbaoi)

- admin server에서 클래스 메서드 정보를 가져오는 기능 추가 (swoole/library@690a1952) (@djw1028769140) (@sy-records)

- admin server 코드를 최적화 (swoole/library#128) (swoole/library#131) (@sy-records)

- admin server가 동시에 여러 목표와 여러 API에 대한 병행 요청을 지원하도록 변경 (swoole/library#124) (@sy-records)

- admin server에서 인터페이스 정보를 가져오는 기능 추가 (swoole/library#130) (@sy-records)

- SWOOLE_HOOK_CURL에서 CURLOPT_HTTPPROXYTUNNEL을 지원하도록 변경 (swoole/library#126) (@sy-records)


### 수정



- join 메서드가 동시에 같은 코어를 병행 호출하는 것을 금지 (#4442) (@matyhtf)

- Table 원자적 잠금이 예상치 못하게 해제되는 문제를 수정 (#4446) (@Txhua) (@matyhtf)

- 손실된 helper options (swoole/library#123) (@sy-records)

- get_static_property_value 명령어 매개변수 오류 (swoole/library#129) (@sy-records)


## v4.8.0


### 하향 호환성 변경



- base 모드에서 onStart 콜백이 항상 첫 번째 작업 프로세스 (worker id가 0)가 시작될 때 콜백되며, onWorkerStart보다 먼저 실행됩니다 (#4389) (@matyhtf)


### 신규 API



- `Co::getStackUsage()` 메서드 추가 (#4398) (@matyhtf) (@twose)

- `Coroutine\Redis`의 일부 API 추가 (#4390) (@chrysanthemum)

- `Table::stats()` 메서드 추가 (#4405) (@matyhtf)

- `Coroutine::join()` 메서드 추가 (#4406) (@matyhtf)


### 신규 기능



- server command 지원 (#4389) (@matyhtf)

- `Server::onBeforeShutdown` 이벤트 콜백 지원 (#4415) (@matyhtf)

```
### 강화



- WebSocket 패킷 실패시 오류코드 설정 (swoole/swoole-src@d27c5a5) (@matyhtf)

- `Timer::exec_count` 필드 추가 (#4402) (@matyhtf)

- mkdir hook에서 open_basedir ini 설정 사용 지원 (#4407) (@NathanFreeman)

- 라이브러리新增 vendor_init.php 스크립트 (swoole/library@6c40b02) (@matyhtf)

- SWOOLE_HOOK_CURL에서 CURLOPT_UNIX_SOCKET_PATH 지원 (swoole/library#121) (@sy-records)

- Client에서 ssl_ciphers 설정 지원 (#4432) (@amuluowin)
- Server::stats()에 새로운 정보 추가 (#4410) (#4412) (@matyhtf)


### 수정



- 파일 업로드 시 불필요한 URL 디코딩 (#4397) (@matyhtf)

- HTTP2 max_frame_size 문제 수정 (#4394) (@twose)

- curl_multi_select 버그 수정 #4393 (#4418) (@matyhtf)

- 실행 중지된 coroutines의 옵션 문제 수정 (#4425) (@sy-records)
- 보낸 버퍼가 꽉 차 있을 때 연결을 close할 수 없게 수정 (swoole/swoole-src@2198378) (@matyhtf)


## v4.7.1


### 강화



- `System::dnsLookup`에서 /etc/hosts 조회 지원 (#4341) (#4349) (@zmyWL) (@NathanFreeman)

- mips64의 boost context 지원 추가 (#4358) (@dixyes)

- `SWOOLE_HOOK_CURL`에서 CURLOPT_RESOLVE 옵션 지원 (swoole/library#107) (@sy-records)

- `SWOOLE_HOOK_CURL`에서 CURLOPT_NOPROGRESS 옵션 지원 (swoole/library#117) (@sy-records)
- riscv64의 boost context 지원 추가 (#4375) (@dixyes)


### 수정



- PHP-8.1이 shutdown시 발생하는 메모리 오류 수정 (#4325) (@twose)

- 8.1.0beta1의 unserializable class 수정 (#4335) (@remicollet)

- 여러 coroutines가 recursively 디렉토리를 만들 때 실패하는 문제 수정 (#4337) (@NathanFreeman)

- native curl이 외부 네트워크에서 큰 파일을 보낼 때 간헐적으로 timeout이 발생하는 문제 및 CURL WRITEFUNCTION에서 coroutine file API를 사용하면 crash하는 문제 수정 (#4360) (@matyhtf)
- `PDOStatement::bindParam()`에서 기대되는 파라미터 1이 문자열일 때의 문제 수정 (swoole/library#116) (@sy-records)


## v4.7.0


### 신규 API



- `Process\Pool::detach()` 메서드 추가 (#4221) (@matyhtf)

- `Server`에서 `onDisconnect` 콜백 함수 지원 (#4230) (@matyhtf)

- `Coroutine::cancel()` 및 `Coroutine::isCanceled()` 메서드 추가 (#4247) (#4249) (@matyhtf)
- `Http\Client`에서 `http_compression` 및 `body_decompression` 옵션 지원 (#4299) (@matyhtf)


### 강화



- coroutines MySQL client가 prepare 시 field의 엄격한 type을 지원 (#4238) (@Yurunsoft)

- DNS에서 c-ares 라이브러리 지원 (#4275) (@matyhtf)

- `Server`에서 여러 포트를 감시할 때 다른 포트에 heartbeat detection 시간을 설정할 수 있도록 지원 (#4290) (@matyhtf)

- `Server`의 `dispatch_mode`에서 `SWOOLE_DISPATCH_CO_CONN_LB` 및 `SWOOLE_DISPATCH_CO_REQ_LB` 모드 지원 (#4318) (@matyhtf)

- `ConnectionPool::get()`에서 `timeout` 매개변수 지원 (swoole/library#108) (@leocavalcante)

- Hook Curl에서 `CURLOPT_PRIVATE` 옵션 지원 (swoole/library#112) (@sy-records)
- `PDOStatementProxy::setFetchMode()` 메서드의 함수 선언을 최적화 (swoole/library#109) (@yespire)


### 수정



- 스레드 컨텍스트를 사용할 때 대량의 coroutines를 생성하면 스레드를 만들 수 없는 예외가 발생하는 문제 수정 (8ce5041) (@matyhtf)

- Swoole 설치 시 php_swoole.h 헤드 파일이 사라지는 문제 수정 (#4239) (@sy-records)

- EVENT_HANDSHAKE가 하위 호환되지 않는 문제 수정 (#4248) (@sy-records)

- SW_LOCK_CHECK_RETURN 매크로가 함수를 두 번 호출할 수 있는 문제 수정 (#4302) (@zmyWL)

- M1 칩에서 `Atomic\Long`이 작동하지 않는 문제 수정 (e6fae2e) (@matyhtf)

- `Coroutine\go()`가 반환값을 잃어버리는 문제 수정 (swoole/library@1ed49db) (@matyhtf)
- `StringObject`의 반환값 타입이 잘못된 문제 수정 (swoole/library#111) (swoole/library#113) (@leocavalcante) (@sy-records)


### 커널


- PHP에서 이미 비활성화된 함수에 대한 Hook을 금지 (#4283) (@twose)


### 테스트



- Cygwin 환경에서의 빌드 추가 (#4222) (@sy-records)
- alpine 3.13 및 3.14의编译 테스트 추가 (#4309) (@limingxinleo)


## v4.6.7


### 강화


- Manager 프로세스와 Task 동기 프로세스가 Process::signal() 함수를 호출할 수 있도록 지원 (#4190) (@matyhtf)


### 수정



- 신호가 중복 등록될 수 없는 문제 수정 (#4170) (@matyhtf)

- OpenBSD/NetBSD에서 빌드가 실패하는 문제 수정 (#4188) (#4194) (@devnexen)

- 쓰기 가능 이벤트를 감시할 때 onClose 이벤트가 유실되는 문제 수정 (#4204) (@matyhtf)

- Symfony HttpClient에서 native curl을 사용하는 문제 수정 (#4204) (@matyhtf)

- Http\Response::end() 메서드가 항상 true를 반환하는 문제 수정 (swoole/swoole-src@66fcc35) (@matyhtf)
- PDOStatementProxy가 발생하는 PDOException 수정 (swoole/library#104) (@twose)


### 커널



- worker buffer를 재구성하여 event data에 msg id 플래그를 추가 (#4163) (@matyhtf)

- Request Entity Too Large 로그 레벨을 warning으로 수정 (#4175) (@sy-records)

- inet_ntoa 및 inet_aton 함수를 교체 (#4199) (@remicollet)
- output_buffer_size의 默认값을 UINT_MAX로 수정 (swoole/swoole-src@46ab345) (@matyhtf)


## v4.6.6


### 강화



- FreeBSD에서 Master 프로세스가 종료된 후 Manager 프로세스에 SIGTERM 신호를 보낼 수 있도록 지원 (#4150) (@devnexen)

- Swoole를 PHP에 정적编译하여 설치할 수 있도록 지원 (#4153) (@matyhtf)
- SNI가 HTTP 프록시를 사용하도록 지원 (#4158) (@matyhtf)


### 수정



- 동기적 클라이언트의 비동기적 연결 오류 수정 (#4152) (@matyhtf)
- Hook로 인해 native curl multi로 인한 메모리 누수 문제 수정 (swoole/swoole-src@91bf243) (@matyhtf)


## v4.6.5


### 신규 API


- WaitGroup에 count 메서드 추가 (swoole/library#100) (@sy-records) (@deminy)


### 강화



- native curl multi 지원 (#4093) (#4099) (#4101) (#4105) (#4113) (#4121) (#4147) (swoole/swoole-src@cd7f51c) (@matyhtf) (@sy-records) (@huanghantao)
- HTTP/2의 Response에서 헤더를 배열로 설정할 수 있도록 지원


### 수정



- NetBSD 빌드 수정 (#4080) (@devnexen)

- OpenBSD 빌드 수정 (#4108) (@devnexen)

- illumos/solaris 빌드에서 멤버 별칭만 있는 문제 수정 (#4109) (@devnexen)
- 핸드셋이 완료되지 않았을 때 SSL 연결의 핑크빙이 작동하지 않는 문제 (#4114) (@matyhtf)

- Http\Client이 프록시를 사용할 때 `host`에 `host:port`가 포함되어 발생하는 오류 (#4124) (@Yurunsoft)
- Swoole\Coroutine\Http::request에서 헤더와 쿠키 설정 (#swoole/library#103) (@leocavalcante) (@deminy)


### 커널



- BSD에서 asm context 지원 (#4082) (@devnexen)

- FreeBSD에서 arc4random_buf를 사용하여 getrandom 실행 (#4096) (@devnexen)
- darwin arm64 context 최적화: workaround 삭제, 레이블 사용 (#4127) (@devnexen)


### 테스트


- alpine 빌드 스크립트 추가 (#4104) (@limingxinleo)


## v4.6.4


### 신규 API


- Coroutine\Http::request, Coroutine\Http::post, Coroutine\Http::get 함수 추가 (swoole/library#97) (@matyhtf)


### 개선



- ARM 64 빌드 지원 (#4057) (@devnexen)
- Swoole TCP 서버에서 open_http_protocol 설정 지원 (#4063) (@matyhtf)
- ssl 클라이언트에서 인증서만 설정 지원 (91704ac) (@matyhtf)
- FreeBSD의 tcp_defer_accept 옵션을 지원 (#4049) (@devnexen)


### 수정



- Coroutine\Http\Client 사용 시 프록시 인증이 빠진 문제 수정 (edc0552) (@matyhtf)
- Swoole\Table 메모리 할당 문제 수정 (3e7770f) (@matyhtf)
- Coroutine\Http2\Client 병렬 연결 시 크래시 문제 수정 (630536d) (@matyhtf)
- DTLS의 enable_ssl_encrypt 문제 수정 (842733b) (@matyhtf)
- Coroutine\Barrier 메모리 누수 문제 수정 (swoole/library#94) (@Appla) (@FMiS)
- CURLOPT_PORT와 CURLOPT_URL 순서로 인한 오프셋 문제 수정 (swoole/library#96) (@sy-records)
- Table::get($key, $field)에서 필드타입이 float일 때의 오류 수정 (08ea20c) (@matyhtf)
- Swoole\Table 메모리 누수 문제 수정 (d78ca8c) (@matyhtf)


## v4.4.24


### 수정


- http2 클라이언트 병렬 연결 시 크래시 문제 수정 (#4079)


## v4.6.3


### 신규 API



- Swoole\Coroutine\go 함수 추가 (swoole/library@82f63be) (@matyhtf)
- Swoole\Coroutine\defer 함수 추가 (swoole/library@92fd0de) (@matyhtf)


### 개선



- HTTP 서버에 compression_min_length 옵션을 추가 (#4033) (@matyhtf)
- 응용 계층에서 Content-Length HTTP 헤더 설정 가능하도록 변경 (#4041) (@doubaokun)


### 수정



- 파일 열기 제한에 도달한 경우 coredump 발생 문제 수정 (swoole/swoole-src@709813f) (@matyhtf)
- JIT이 비활성화된 문제 수정 (#4029) (@twose)
- Response::create() 매개변수 오류 문제 수정 (swoole/swoole-src@a630b5b) (@matyhtf)
- ARM 플랫폼에서 task 전달 시 task_worker_id 오도 문제 수정 (#4040) (@doubaokun)
- PHP8에서 native curl hook 활성화 시 coredump 발생 문제 수정 (#4042)(#4045) (@Yurunsoft) (@matyhtf)
- fatal error 시 shutdown 단계에서 메모리越界 문제 수정 (#4050) (@matyhtf)


### 커널



- ssl_connect/ssl_shutdown 최적화 (#4030) (@matyhtf)
- fatal error 발생 시 프로세스 직접 종료 (#4053) (@matyhtf)


## v4.6.2


### 신규 API



- Http\Request\getMethod() 메서드 추가 (#3987) (@luolaifa000)
- Coroutine\Socket->recvLine() 메서드 추가 (#4014) (@matyhtf)
- Coroutine\Socket->readWithBuffer() 메서드 추가 (#4017) (@matyhtf)


### 개선



- Response\create() 메서드를 Server와 독립적으로 사용할 수 있도록 강화 (#3998) (@matyhtf)
- Coroutine\Redis->hExists가 compatibility_mode가 설정된 후 bool 타입으로 반환하도록 지원 (swoole/swoole-src@b8cce7c) (@matyhtf)
- socket_read에 PHP_NORMAL_READ 옵션을 설정할 수 있도록 지원 (swoole/swoole-src@b1a0dcc) (@matyhtf)


### 수정



- Coroutine::defer가 PHP8에서 coredump 발생하는 문제 수정 (#3997) (@huanghantao)
- thread context 사용 시 Coroutine\Socket::errCode 오류 설정 문제 수정 (swoole/swoole-src@004d08a) (@matyhtf)
- 최신 macos에서 Swoole编译 실패 문제 수정 (#4007) (@matyhtf)
- md5_file 매개변수에 url을 전달했을 때 php stream context가 null 포인터로 발생하는 문제 수정 (#4016) (@ZhiyangLeeCN)


### 커널



- AIO 스레드 풀 hook stdio (이전에는 stdio를 socket로 취급하여 발생한 다중 코어 프로세스读写 문제를 해결) (#4002) (@matyhtf)
- HttpContext 재구성 (#3998) (@matyhtf)
- Process::wait() 재구성 (#4019) (@matyhtf)


## v4.6.1


### 강화



- --enable-thread-context 빌드 옵션 추가 (#3970) (@matyhtf)
- session_id操作 시 연결이 존재하는지 확인 (#3993) (@matyhtf)
- CURLOPT_PROXY 강화 (swoole/library#87) (@sy-records)


### 수정



- pecl 설치 중 최소 PHP 버전이 충족하지 않는 문제 수정 (#3979) (@remicollet)
- pecl 설치 시 --enable-swoole-json와 --enable-swoole-curl 옵션이 없을 때의 문제 수정 (#3980) (@sy-records)
- openssl 스레드安全问题 수정 (b516d69f) (@matyhtf)
- enableSSL coredump 수정 (#3990) (@huanghantao)


### 커널


- ipc writev 최적화, 이벤트 데이터가 비어 있을 때 coredump 발생 방지 (9647678) (@matyhtf)


## v4.5.11


### 강화



- Swoole\Table 최적화 (#3959) (@matyhtf)
- CURLOPT_PROXY 강화 (swoole/library#87) (@sy-records)


### 수정



- Table 증감 시 모든 열을 제거하지 못하는 문제 수정 (#3956) (@matyhtf) (@sy-records)
- 빌드 시 발생하는 `clock_id_t` 오류 수정 (49fea171) (@matyhtf)
- fread bugs 수정 (#3972) (@matyhtf)
- ssl 멀티스레드 크래시 수정 (7ee2c1a0) (@matyhtf)
- uri 형식 오류로 인한 Invalid argument supplied for foreach 에러 발생 문제 수정 (swoole/library#80) (@sy-records)
- trigger_error 매개변수 오류 수정 (swoole/library#86) (@sy-records)


## v4.6.0


### 하향 호환성 변경



- `session id`의 최대 제한이 제거되어 중복되지 않도록 변경 (#3879) (@matyhtf)
- 코어 사용 시 안전하지 않은 기능이 비활성화되며, pcntl_fork/pcntl_wait/pcntl_waitpid/pcntl_sigtimedwait 포함 (#3880) (@matyhtf)
- 默认로 coroutine hook가 활성화되도록 변경 (#3903) (@matyhtf)


### 제거


- PHP7.1 지원이 중단됩니다 (4a963df) (9de8d9e) (@matyhtf)


### 폐기


- Event::rshutdown()를标记 as deprecated로 변경하며, Coroutine\run을 사용해 주세요 (#3881) (@matyhtf)
### 신규 API



- setPriority/getPriority 지원 (#3876) (@matyhtf)

- native-curl hook 지원 (#3863) (@matyhtf) (@huanghantao)

- Server 이벤트 콜백 함수에서 객체 스타일의 매개변수를 전달할 수 있도록 변경 (기본적으로 객체 스타일의 매개변수를 전달하지 않습니다) (#3888) (@matyhtf)

- hook sockets 확장 지원 (#3898) (@matyhtf)

- 중복 header 지원 (#3905) (@matyhtf)

- SSL sni 지원 (#3908) (@matyhtf)

- hook stdio 지원 (#3924) (@matyhtf)

- stream_socket의 capture_peer_cert 옵션을 지원 (#3930) (@matyhtf)

- Http\Request::create/parse/isCompleted 추가 (#3938) (@matyhtf)
- Http\Response::isWritable 추가 (db56827) (@matyhtf)


### 개선



- Server의 모든 시간 정밀도가 int에서 double로 변경 (#3882) (@matyhtf)

- swoole_client_select 함수에서 poll 함수의 EINTR 상황을 검사 (#3909) (@shiguangqi)

- 코루틴 死锁 검출 기능 추가 (#3911) (@matyhtf)

- 다른 프로세스에서 연결을 닫을 수 있도록 SWOOLE_BASE 모드 사용 지원 (#3916) (@matyhtf)
- Server 마스터 프로세스와 워커 프로세스 간의 통신 성능을 최적화하고 메모리 복제를 줄입니다 (#3910) (@huanghantao) (@matyhtf)


### 수정



- Coroutine\Channel이 닫힐 경우 모든 데이터를 pop하여 제거 (#960431d) (@matyhtf)

- JIT 사용 시 메모리 오류 수정 (#3907) (@twose)

- dtls 编译错误修复 (#3947) (@Yurunsoft)

- connection_list错误修复 (#3948) (@sy-records)

- ssl verify修复 (#3954) (@matyhtf)

- Table增减列时清除所有列问题修复 (#3956) (@matyhtf) (@sy-records)

- LibreSSL 2.7.5编译失败修复 (#3962) (@matyhtf)
- CURLOPT_HEADEROPT와 CURLOPT_PROXYHEADER未定义常量修复 (swoole/library#77) (@sy-records)


### 커널



- 기본적으로 SIGPIPE 신호 무시 (#9647678) (@matyhtf)

- PHP协程와 C协程 동시에 실행 지원 (#c94bfd8) (@matyhtf)

- get_elapsed 테스트 추가 (#3961) (@luolaifa000)
- get_init_msec 테스트 추가 (#3964) (@luffluo)


## v4.5.10


### 수정



- Event::cycle 사용 시 발생하는 coredump 수정 (93901dc) (@matyhtf)

- PHP8 호환성 개선 (f0dc6d3) (@matyhtf)
- connection_list错误修复 (#3948) (@sy-records)


## v4.4.23


### 수정



- Swoole\Table 감소 시 데이터 오류 수정 (bcd4f60d)(0d5e72e7) (@matyhtf)

- 同步客户端错误信息修复 (#3784)

- 表单数据边界解析 시 메모리 오버플로우 수정 (#3858)
- channel关闭后无法pop已有数据的问题修复


## v4.5.9


### 강화


- Coroutine\Http\Client에 SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED 상수 추가 (#3873) (@sy-records)


### 수정



- PHP8 호환성 개선 (#3868) (#3869) (#3872) (@twose) (@huanghantao) (@doubaokun)

- CURLOPT_HEADEROPT와 CURLOPT_PROXYHEADER未定义常量修复 (swoole/library#77) (@sy-records)
- CURLOPT_USERPWD修复 (swoole/library@7952a7b) (@twose)


## v4.5.8


### 신규 API



- swoole_error_log 함수 추가, log_rotation 최적화 (swoole/swoole-src@67d2bff) (@matyhtf)
- readVector和writeVector支持SSL (#3857) (@huanghantao)


### 강화


- 자식 프로세스가 종료되면 System::wait가 멈추지 않도록 변경 (#3832) (@matyhtf)

- DTLS 16K 패킷 지원 (#3849) (@matyhtf)

- Response::cookie方法新增priority参数 (#3854) (@matyhtf)

- 더 많은CURL 옵션 지원 (swoole/library#71) (@sy-records)
- CURL HTTP header 이름의 크로스CasE가 적용되어覆盖되는 문제 처리 (swoole/library#76) (@filakhtov) (@twose) (@sy-records)


### 수정



- readv_all和writev_all错误处理EAGAIN问题修复 (#3830) (@huanghantao)

- PHP8编译警告问题修复 (swoole/swoole-src@03f3fb0) (@matyhtf)

- Swoole\Table二进制安全问题修复 (#3842) (@twose)

- MacOS下System::writeFile追加文件覆盖问题修复 (swoole/swoole-src@a71956d) (@matyhtf)

- CURL的CURLOPT_WRITEFUNCTION修复 (swoole/library#74) (swoole/library#75) (@sy-records)

- HTTP form-data解析时内存溢出问题修复 (#3858) (@twose)
- PHP8中`is_callable()`无法访问类私有方法问题修复 (#3859) (@twose)


### 커널



- 메모리 할당 함수 재구성, SwooleG.std_allocator 사용 (#3853) (@matyhtf)
- 파이프 재구성 (#3841) (@matyhtf)


## v4.5.7


### 신규 API


- Coroutine\Socket客户端新增writeVector, writeVectorAll, readVector, readVectorAll方法 (#3764) (@huanghantao)


### 강화


- server->stats에task_worker_num和dispatch_count 추가 (#3771) (#3806) (@sy-records) (@matyhtf)

- 확장 종속 항목 추가, json, mysqlnd, sockets 포함 (#3789) (@remicollet)

- server->bind의uid 최소값을INT32_MIN로 제한 (#3785) (@sy-records)

- swoole_substr_json_decode에编译选项 추가, 부정적 오프셋 지원 (#3809) (@matyhtf)
- CURL의CURLOPT_TCP_NODELAY 옵션을支援 (swoole/library#65) (@sy-records) (@deminy)


### 수정



- 同步客户端连接信息错误修复 (#3784) (@twose)

- hook scandir函数问题修复 (#3793) (@twose)
- 코루틴 배리어들 중의错误修复 (swoole/library#68) (@sy-records)


### 커널


- boost.stacktrace로print-backtrace 최적화 (#3788) (@matyhtf)


## v4.5.6


### 신규 API


- [swoole_substr_unserialize](/functions?id=swoole_substr_unserialize)와 [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode)新增 (#3762) (@matyhtf)


### 강화


- Coroutine\Http\Server의onAccept方法私有화 (dfcc83b) (@matyhtf)


### 수정



- coverity问题修复 (#3737) (#3740) (@matyhtf)

- Alpine环境下一些问题修复 (#3738) (@matyhtf)

- swMutex_lockwait(0fc5665)修复 (@matyhtf)
- PHP-8.1安装失败修复 (#3757) (@twose)


### 커널



- Socket::read/write/shutdown에활성화감지 추가 (#3735) (@matyhtf)
- session_id와task_id의 타입을int64로 변경 (#3756) (@matyhtf)
## v4.5.5

!> 이 버전에서는 [설정 항목](/server/setting) 검출 기능이 추가되었습니다. Swoole이 제공하지 않는 옵션을 설정하면 Warning가 발생합니다.

```shell
PHP Warning:  지원되지 않는 옵션 [foo] in @swoole-src/library/core/Server/Helper.php 
```

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->set(['foo' => 'bar']);

$http->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

$http->start();
```


### 신규 API



- Process\Manager 추가, Process\ProcessManager를 별명 (swoole/library#eac1ac5)로 변경 (@matyhtf)

- HTTP2 서버 GOAWAY 지원 (#3710) (@doubaokun)
- `Co\map()` 함수 추가 (swoole/library#57) (@leocavalcante)


### 개선



- http2 unix socket 클라이언트 지원 (#3668) (@sy-records)

- worker 프로세스가 종료된 후 worker 프로세스 상태를 SW_WORKER_EXIT로 설정 (#3724) (@matyhtf)

- `Server::getClientInfo()`의 반환값에 send_queued_bytes와 recv_queued_bytes 추가 (#3721) (#3731) (@matyhtf) (@Yurunsoft)
- Server에 stats_file 설정 옵션 지원 (#3725) (@matyhtf) (@Yurunsoft)


### 수정



- PHP8에서 컴파일 이슈 수정 (zend_compile_string 변경) (#3670) (@twose)

- PHP8에서 컴파일 이슈 수정 (ext/sockets 호환성) (#3684) (@twose)

- PHP8에서 컴파일 이슈 수정 (php_url_encode_hash_ex 변경) (#3713) (@remicollet)

- 'const char*'에서 'char*'로의 잘못된 형식 변환 수정 (#3686) (@remicollet)

- HTTP2 클라이언트가 HTTP 프록시에서 작동하지 않는 이슈 수정 (#3677) (@matyhtf) (@twose)

- PDO断线 재접속 시 데이터 혼란 이슈 수정 (swoole/library#54) (@sy-records)

- UDP 서버가 IPv6을 사용 시 포트 해석 오류 수정
- Lock::lockwait 超时이 적용되지 않는 이슈 수정


## v4.5.4


### 하향 호환성 변경



- SWOOLE_HOOK_ALL에 SWOOLE_HOOK_CURL 포함 (#3606) (@matyhtf)
- ssl_method 제거, ssl_protocols 추가 (#3639) (@Yurunsoft)


### 신규 API


- 배열의 firstKey와 lastKey 메서드 추가 (swoole/library#51) (@sy-records)


### 개선


- WebSocket 서버의 open_websocket_ping_frame, open_websocket_pong_frame 설정 항목 추가 (#3600) (@Yurunsoft)


### 수정



- 파일이 2GB보다 클 때 fseek, ftell이 정확하지 않아지는 이슈 수정 (#3619) (@Yurunsoft)

- Socket barrier 이슈 수정 (#3627) (@matyhtf)

- http proxy handshake 이슈 수정 (#3630) (@matyhtf)

- 상대방이 chunk 데이터를 보낼 때 HTTP Header 해석 오류 수정 (#3633) (@matyhtf)

- zend_hash_clean에서断言 실패 이슈 수정 (#3634) (@twose)

- 이벤트 루프에서 broken fd를 제거할 수 없는 이슈 수정 (#3650) (@matyhtf)

- 유효하지 않은 패킷을 받은 경우 core dump 발생 이슈 수정 (#3653) (@matyhtf)
- array_key_last의 버그 수정 (swoole/library#46) (@sy-records)


### 커널



- 코드 최적화 (#3615) (#3617) (#3622) (#3635) (#3640) (#3641) (#3642) (#3645) (#3658) (@matyhtf)

- Swoole Table에 데이터를写入할 때 불필요한 메모리 조작을 줄임 (#3620) (@matyhtf)

- AIO 재구성 (#3624) (@Yurunsoft)

- readlink/opendir/readdir/closedir hook 지원 (#3628) (@matyhtf)
- swMutex_create 최적화, SW_MUTEX_ROBUST 지원 (#3646) (@matyhtf)


## v4.5.3


### 신규 API



- `Swoole\Process\ProcessManager` 추가 (swoole/library#88f147b) (@huanghantao)

- ArrayObject::append, StringObject::equals 추가 (swoole/library#f28556f) (@matyhtf)

- [Coroutine::parallel](/coroutine/coroutine?id=parallel) 추가 (swoole/library#6aa89a9) (@matyhtf)
- [Coroutine\Barrier](/coroutine/barrier) 추가 (swoole/library#2988b2a) (@matyhtf)


### 개선



- usePipelineRead을 추가하여 http2 client streaming을 지원 (#3354) (@twose)

- http客户端가 파일을 다운로드할 때, 데이터를 수신하기 전에 파일을 생성하지 않음 (#3381) (@twose)

- http客户端가 `bind_address`와 `bind_port` 설정을 지원 (#3390) (@huanghantao)

- http客户端가 `lowercase_header` 설정을 지원 (#3399) (@matyhtf)

- `Swoole\Server`가 `tcp_user_timeout` 설정을 지원 (#3404) (@huanghantao)

- `Coroutine\Socket`에 event barrier를 추가하여 코루outine 교환을 줄임 (#3409) (@matyhtf)

- 특정 swString에 `memory allocator`를 추가 (#3418) (@matyhtf)

- cURL이 `__toString`를 지원 (swoole/library#38) (@twose)

- WaitGroup 생성자에서 직접 `wait count`를 설정할 수 있도록 변경 (swoole/library#2fb228b8) (@matyhtf)

- `CURLOPT_REDIR_PROTOCOLS`를 추가 (swoole/library#46) (@sy-records)

- http1.1 server가 trailer를 지원 (#3485) (@huanghantao)

- 코루outine sleep 시간이 1ms 미만일 경우 현재 코루outine을 yield하게 변경 (#3487) (@Yurunsoft)

- http static handler가 소프트 링크의 파일을 지원 (#3569) (@LeiZhang-Hunter)

- Server가 close 메서드를 호출한 후 즉시 WebSocket 연결을 닫음 (#3570) (@matyhtf)

- stream_set_blocking hook을 지원 (#3585) (@Yurunsoft)

- 비동기 HTTP2 server가 stream control을 지원 (#3486) (@huanghantao) (@matyhtf)
- socket buffer가 onPackage回调함수 실행이 완료된 후에 해제됨 (#3551) (@huanghantao) (@matyhtf)


### 수정



- WebSocket core dump, 프로토콜 오류 상태 처리 (#3359) (@twose)

- swSignalfd_setup 함수 및 wait_signal 함수에서 포인터 null 오류 수정 (#3360) (@twose)

- dispatch_func가 설정된 상태에서 Swoole\Server::close를 호출하면 오류가 발생하는 이슈 수정 (#3365) (@twose)

- Swoole\Redis\Server::format 함수에서 format_buffer 초기화 이슈 수정 (#3369) (@matyhtf) (@twose)

- MacOS에서 MAC 주소를 획득할 수 없는 이슈 수정 (#3372) (@twose)

- MySQL 테스트 케이스 수정 (#3374) (@qiqizjl)

- 여러 PHP8 호환성 이슈 수정 (#3384) (#3458) (#3578) (#3598) (@twose)

- hook의 socket write에서 php_error_docref, timeout_event 및 반환값이 누락되는 이슈 수정 (#3383) (@twose)

- WorkerStart回调함수에서 Server를 닫을 수 없는 비동기 Server 이슈 수정 (#3382) (@huanghantao)

- heartbeat线程이 conn->socket를操作할 때 core dump가 발생할 수 있는 이슈 수정 (#3396) (@huanghantao)

- send_yield의 논리 이슈 수정 (#3397) (@twose) (@matyhtf)
-Cygwin64에서 빌드 오류(#3400)(@twose)를 수정하였습니다.

-WebSocket의 finish 속성이 유효하지 않음 문제(#3410)(@matyhtf)를 수정하였습니다.

-MySQL 트랜잭션 오류 상태가 누락되어存在的问题(#3429)(@twose)를 수정하였습니다.

-후hook의`stream_select`와 hook 이전의 반환값이 일관되지 않음 문제(#3440)(@Yurunsoft)를 수정하였습니다.

- `Coroutine\System`을 이용하여 자식 프로세스 생성시 `SIGCHLD` 신호가 누락되는 문제(#3446)(@huanghantao)를 수정하였습니다.

- `sendwait`이 SSL을 지원하지 않는 문제(#3459)(@huanghantao)를 수정하였습니다.

- `ArrayObject`와 `StringObject`의 여러 가지 문제(swoole/library#44)(@matyhtf)를 수정하였습니다.

-mysqli의 예외 정보 오류(swoole/library#45)(@sy-records)를 수정하였습니다.

- `open_eof_check`를 설정한 후 `Swoole\Client`이 올바른 `errCode`를 가져오지 못하는 문제(#3478)(@huanghantao)를 수정하였습니다.

- MacOS에서 `atomic->wait()`/`wakeup()`의 여러 가지 문제(#3476)(@Yurunsoft)를 수정하였습니다.

- `Client::connect` 연결이 거절될 경우 성공 상태를 반환하는 문제(#3484)(@matyhtf)를 수정하였습니다.

-alpine 환경에서 `nullptr_t`가 선언되지 않은 문제(#3488)(@limingxinleo)를 수정하였습니다.

-HTTP Client이 파일을 다운로드할 때 double-free 문제가 발생하는 문제(#3489)(@Yurunsoft)를 수정하였습니다.

- `Server`가 파괴될 때, `Server\Port`이 해제되지 않아 메모리 누수 문제가 발생하는 문제(#3507)(@twose)를 수정하였습니다.

- MQTT 프로토콜 해석 문제(318e33a)(84d8214)(80327b3)(efe6c63)(@GXhua) (@sy-records)를 수정하였습니다.

- `Coroutine\Http\Client->getHeaderOut` 메서드로 인한 coredump 문제(#3534)(@matyhtf)를 수정하였습니다.

- SSL 검증에 실패했을 때, 오류 정보가 누락되는 문제(#3535)(@twose)를 수정하였습니다.

- README에서 `Swoole benchmark` 링크가 잘못된 문제(#3536)(@sy-records) (@santalex)를 수정하였습니다.

- `HTTP header/cookie`에서 `CRLF`를 사용한 후 발생하는 `header` injection 문제(#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)를 수정하였습니다.

- issue #3463에서 언급한 변수 오류 문제(#3547) (chromium1337) (@huanghantao)를 수정하였습니다.

- pr #3463에서 언급한 철자 오류 문제(#3547) (@deminy)를 수정하였습니다.

- co-routine WebSocket 서버에서 frame->fd가 비어있는 문제(#3549) (@huanghantao)를 수정하였습니다.

- 핫스테이 스레드에서 잘못된 연결 상태 판단으로 인한 연결 누수 문제(#3534) (@matyhtf)를 수정하였습니다.

- `Process\Pool`에서 신호가 막히는 문제(#3582) (@huanghantao) (@matyhtf)를 수정하였습니다.

- `SAPI`에서 headers를 보낼 때 발생하는 문제 (#3571) (@twose) (@sshymko)를 수정하였습니다.

- `CURL`이 실패했을 때, `errCode`와 `errMsg`가 설정되지 않은 문제 (swoole/library#1b6c65e) (@sy-records)를 수정하였습니다.

- `setProtocol` 메서드를 호출한 후, `swoole_socket_coro`accept가 coredump하는 문제 (#3591) (@matyhtf)를 수정하였습니다.


### 내핵



- C++ 스타일로 작성 (#3349) (#3351) (#3454) (#3479) (#3490) (@huanghantao) (@matyhtf)

- `Swoole known strings` 추가하여 PHP 객체의 read 속성을 향상시킵니다 (#3363) (@huanghantao)

- 여러 코드 최적화 (#3350) (#3356) (#3357) (#3423) (#3426) (#3461) (#3463) (#3472) (#3557) (#3583) (@huanghantao) (@twose) (@matyhtf)

- 여러 테스트 코드 최적화 (#3416) (#3481) (#3558) (@matyhtf)

- `Swoole\Table`의 `int` 타입을 간소화 (#3407) (@matyhtf)

- `sw_memset_zero`를 추가하고 `bzero` 함수를 대체합니다 (#3419) (@CismonX)

- 로깅 모듈을 최적화 (#3432) (@matyhtf)

- 여러 libswoole 리뉴얼 (#3448) (#3473) (#3475) (#3492) (#3494) (#3497) (#3498) (#3526) (@matyhtf)

- 여러 헤드 파일의 인스턴스화 작업 (#3457) (@matyhtf) (@huanghantao)

- `Channel::count()`와 `Channel::get_bytes()` (f001581) (@matyhtf)를 추가합니다.

- `scope guard`를 추가합니다 (#3504) (@huanghantao)

- libswoole 커버리지를 위한 테스트가 추가됩니다 (#3431) (@huanghantao)

- lib-swoole/ext-swoole MacOS 환경의 테스트가 추가됩니다 (#3521) (@huanghantao)

- lib-swoole/ext-swoole Alpine 환경의 테스트가 추가됩니다 (#3537) (@limingxinleo)


## v4.5.2

[v4.5.2](https://github.com/swoole/swoole-src/releases/tag/v4.5.2)，이것은 BUG 수정 버전이며, 어떠한 하향 호환성 변경도 없습니다.


### 기능 강화



- `Server->set(['log_rotation' => SWOOLE_LOG_ROTATION_DAILY])`를 통해 날짜에 따라 로그 생성을 지원합니다 (#3311) (@matyhtf)

- `swoole_async_set(['wait_signal' => true])`를 지원하여, 신호 监听기가 존재하는 경우 reactor가 종료되지 않습니다 (#3314) (@matyhtf)

- `Server->sendfile`을 통해 빈 파일을 전송할 수 있도록 지원합니다 (#3318) (@twose)

- worker의 바쁨/차분 경고 메시지를 최적화합니다 (#3328) (@huanghantao)

- HTTPS 프록시 환경에서 Host 헤드의 설정이 개선됩니다 (ssl_host_name을 이용하여 설정합니다) (#3343) (@twose)

- SSL의 기본 모드는 ecdh auto 모드로 변경됩니다 (#3316) (@matyhtf)
- SSL 클라이언트는 연결이 끊어졌을 때 무소음으로 종료합니다 (#3342) (@huanghantao)


### 버그 수정



- `Server->taskWait`이 OSX 플랫폼에서 발생하는 문제(#3330) (@matyhtf)를 수정하였습니다.

- MQTT 프로토콜 해석 오류(8dbf506b) (@guoxinhua) (2ae8eb32) (@twose)를 수정하였습니다.

- Content-Length의 int 타입 오버플로우 문제(#3346) (@twose)를 수정하였습니다.

- PRI 패킷 길이 검사 누락 문제(#3348) (@twose)를 수정하였습니다.

- CURLOPT_POSTFIELDS가 비워지지 않는 문제 (swoole/library@ed192f64) (@twose)를 수정하였습니다.

- 최신 연결 객체가 다음 연결을 받은 전까지 해제되지 않는 문제 (swoole/library@1ef79339) (@twose)를 수정하였습니다.


### 내핵



- 소켓의 Zero-copy 특성을 추가합니다 (#3327) (@twose)
- 전역 변수 대신 swoole_get_last_error/swoole_set_last_error 두 개를 사용합니다 (e25f262a) (@matyhtf) (#3315) (@huanghantao)


## v4.5.1

[v4.5.1](https://github.com/swoole/swoole-src/releases/tag/v4.5.1)，이것은 BUG 수정 버전이며, 본래 `v4.5.0`에서 도입될 예정이었던 System 파일 함수의 폐기 표시가 보충되었습니다.


### 기능 강화



- hook 아래의 socket_context의 bindto 설정이 지원됩니다 (#3275) (#3278) (@codinghuang)

- client::sendto가 자동으로 DNS 해석하여 주소를 지정합니다 (#3292) (@codinghuang)

- Process->exit(0)는 프로세스가 즉시 종료되도록 변경되었으며, shutdown_functions를 실행한 후에 종료하고자 한다면 PHP에서 제공하는 exit을 사용해야 합니다 (a732fe56) (@matyhtf)
- 로그 날짜 포맷을 변경할 수 있는 `log_date_format` 설정이 추가됩니다. `log_date_with_microseconds`는 로깅에서 마이크로초 시간戳을 표시합니다. (baf895bc) (@matyhtf)

- CURLOPT_CAINFO와 CURLOPT_CAPATH가 추가됩니다. (swoole/library#32) (@sy-records)
- CURLOPT_FORBID_REUSE가 추가됩니다. (swoole/library#33) (@sy-records)


### 수정 사항



- 32비트에서 빌드가 실패하는 문제 ( #3276 ) ( #3277 ) (@remicollet) (@twose)

- 코루outine Client가 중복 연결을 할 때 EISCONN 오류 메시지가 나오지 않는 문제 ( #3280 ) (@codinghuang)

- Table 모듈의 잠재적인 버그 (d7b87b65) (@matyhtf)

- Server에서 미정의 행동으로 인한的空指针(예방적 프로그래밍) ( #3304 ) ( #3305 ) (@twose)

- 핫스테이 구성이 활성화되었을 때 발생하는空指针错误 문제 ( #3307 ) (@twose)

- mysqli 구성이 적용되지 않는 문제 (swoole/library#35)
- response에서 비규범한 header(공백이 없는 경우)을解析할 때 발생하는 문제 (swoole/library#27) (@Yurunsoft)


### 제거


- Coroutine\System::(fread/fgets/fwrite) 등의 방법이 비권장되며, hook 특성으로 대체하고, 직접 PHP의 파일 함수를 사용해야 합니다. (c7c9bb40) (@twose)


### 커널



- 커스텀 객체에 메모리를 할당하기 위해 zend_object_alloc를 사용합니다. (cf1afb25) (@twose)

- 로깅 모듈에 대한 추가 설정이 추가됩니다. (#3296) (@matyhtf)
- 대량의 코드 최적화 작업 및 단위 테스트가 추가됩니다. (swoole/library) (@deminy)


## v4.5.0

[v4.5.0](https://github.com/swoole/swoole-src/releases/tag/v4.5.0)는 큰 버전 업데이트로, v4.4.x에서 이미 비권장된 모듈을 제거하였습니다.


### 신규 API



- DTLS 지원이 추가되어 WebRTC 앱을 구축할 수 있습니다. (#3188) (@matyhtf)

- 내장된 `FastCGI` 클라이언트가 추가되어 FPM에 프록시 요청을 하거나 FPM 앱을 호출할 수 있습니다. (swoole/library#17) (@twose)

- `Co::wait`, `Co::waitPid` (부모 프로세스 재개를 위한) `Co::waitSignal` (신호 대기 위한) (#3158) (@twose)

- `Co::waitEvent` (소켓에서 발생하는 특정 이벤트를 기다리는 것) (#3197) (@twose)

- `Co::set(['exit_condition' => $callable])` (프로그램 종료 조건을 사용자 정의하기) (#2918) (#3012) (@twose)

- `Co::getElapsed` (코루outine이 실행된 시간을 획득하여 분석, 통계 또는 좀비 코루outine을 찾기 위한) (#3162) (@doubaokun)

- `Socket::checkLiveness` (시스템 호출을 통해 연결이 활성화 여부를 확인하는 것), `Socket::peek` (读取缓冲区的内容) (#3057) (@twose)

- `Socket->setProtocol(['open_fastcgi_protocol' => $bool])` (FastCGI 해체 지원을 내장) (#3103) (@twose)

- `Server::get(Master|Manager|Worker)Pid`, `Server::getWorkerId` (비동기 Server의 단일 인스턴스 및 정보를 획득하기) (#2793) (#3019) (@matyhtf)

- `Server::getWorkerStatus` (worker 프로세스 상태를 획득하고, SWOOLE_WORKER_BUSY, SWOOLE_WORKER_IDLE을 반환하여 바쁨/자유 상태를 나타냅니다) (#3225) (@matyhtf)

- `Server->on('beforeReload', $callable)` 및 `Server->on('afterReload', $callable)` (서비스 재시전 이벤트, manager 프로세스에서 발생합니다) (#3130) (@hantaohuang)

- `Http\Server` 정적 파일 처리기가 이제 `http_index_files` 및 `http_autoindex` 설정을 지원합니다. (#3171) (@hantaohuang)

- `Http2\Client->read(float $timeout = -1)` 방법이 스트림형 응답을 읽는 것을 지원합니다. (#3011) (#3117) (@twose)

- `Http\Request->getContent` (rawContent 방법의 별명) (#3128) (@hantaohuang)
- `swoole_mime_type_(add|set|delete|get|exists)()` (mime 관련 APIs, 내장된 mime 유형을 추가, 수정, 삭제, 조회, 존재 확인이 가능합니다) (#3134) (@twose)


### 개선 사항



- master와 worker 프로세스 간의 메모리 복사가 최적화되어, 극단적인 경우 4배의 성능 향상을 보입니다. (#3075) (#3087) (@hantaohuang)

- WebSocket 전송 로직이 최적화됩니다. (#3076) (@matyhtf)

- WebSocket 구성 프레임 생성 시 한 번의 메모리 복사가 최적화됩니다. (#3097) (@matyhtf)

- SSL 검증 모듈이 최적화됩니다. (#3226) (@matyhtf)

- SSL 수락 및 SSL 핸드셋을 분리하여, 느린 SSL 클라이언트가 코루outine 서버를 잘못으로 멈추게 할 수 있는 문제를 해결합니다. (#3214) (@twose)

- MIPS 아키텍처 지원이 추가됩니다. (#3196) (@ekongyun)

- UDP 클라이언트가 이제 들어오는 도메인을 자동으로 해석할 수 있습니다. (#3236) (#3239) (@huanghantao)

- Coroutine\Http\Server에 몇 가지 일반적으로 사용되는 옵션이 추가됩니다. (#3257) (@twose)

- WebSocket 핸드셋 시 cookie를 설정할 수 있는 기능이 추가됩니다. (#3270) (#3272) (@twose)

- CURLOPT_FAILONERROR가 추가됩니다. (swoole/library#20) (@sy-records)

- CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY가 추가됩니다. (swoole/library#22) (@sy-records)
- CURLOPT_HTTPGET가 추가됩니다. (swoole/library@d730bd08) (@shiguangqi)


### 제거 사항



- `Runtime::enableStrictMode` 메서드가 제거됩니다. (b45838e3) (@twose)
- `Buffer` 클래스가 제거됩니다. (559a49a8) (@twose)


### 커널 관련



- 새로운 C++ API: coroutine::async 함수에 lambda를 전달하여 비동기 스레드 작업을 시작할 수 있습니다. (#3127) (@matyhtf)

- 기본 event-API의 정수형 fd를 swSocket 객체로 재구성합니다. (#3030) (@matyhtf)

- 모든 핵심 C 파일이 C++ 파일로 전환되었습니다. (#3030) (71f987f3) (@matyhtf)

- 일련의 코드 최적화 (#3063) (#3067) (#3115) (#3135) (#3138) (#3139) (#3151) (#3168) (@hantaohuang)

- 헤더 파일의 규범화 최적화 (#3051) (@matyhtf)

- `enable_reuse_port` 설정을 더욱 규범적으로 재구성합니다. (#3192) (@matyhtf)

- Socket 관련 API를 더욱 규범적으로 재구성합니다. (#3193) (@matyhtf)

- 버퍼 예측을 통해 불필요한 시스템 호출을 한 번 줄입니다. (3b5aa85d) (@matyhtf)

- 기본적인 리프레시 타이머 swServerGS::now를 제거하고, 시간 함수 직접 사용하여 시간을 획득합니다. (#3152) (@hantaohuang)

- 프로토콜 구성자가 최적화됩니다. (#3108) (@twose)

- 호환성이 더 좋은 C 구조체 초기화 문법 (#3069) (@twose)

- 비트 필드가 통일되어 uchar 유형으로 변경됩니다. (#3071) (@twose)
- 병행 테스트가 지원되어 속도가 향상됩니다. (#3215) (@twose)


### 수정 사항



- enable_delay_receive가 활성화되었을 때 onConnect이 발동하지 않는 문제 ( #3221 ) ( #3224 ) (@matyhtf)
- 그 외의 모든 버그 수정 사항이 v4.4.x 브래칭에 통합되어 업데이트 로그에 반영되어 여기서 다시 언급하지 않겠습니다.


## v4.4.22


### 수정 사항



- HTTP2 클라이언트가 HTTP 프록시에서 작동하지 않는 문제 ( #3677 ) (@matyhtf) (@twose)
- PDO 断线重连时数据混乱的问题 (swoole/library#54) (@sy-records)

- swMutex_lockwait (0fc5665) (@matyhtf)

- UDP Server 使用ipv6时端口解析错误

- systemd fds 的问题


## v4.4.20

[v4.4.20](https://github.com/swoole/swoole-src/releases/tag/v4.4.20)，이것은 BUG 수정 버전이며, 어떠한 하향 호환성 변화도 없습니다.


### 수정 사항



- dispatch_func가 설정되어 있을 때, Swoole\Server::close를 호출하면 오류가 발생하는 문제 (#3365) (@twose)

- Swoole\Redis\Server::format 함수에서 format_buffer 초기화 문제가 수정됩니다 (#3369) (@matyhtf) (@twose)

- MacOS에서 MAC 주소를 획득할 수 없는 문제 (#3372) (@twose)

- MySQL 테스트 케이스 (#3374) (@qiqizjl)

- 비동기 Server가 WorkerStart回调 함수에서 Server를 닫을 수 없는 문제 (#3382) (@huanghantao)

- 누락된 MySQL transaction 오류 상태 (#3429) (@twose)

- HTTP Client가 파일을 다운로드할 때 double-free 문제가 수정됩니다 (#3489) (@Yurunsoft)

- Coroutine\Http\Client->getHeaderOut 방법으로 인한 coredump 문제가 수정됩니다 (#3534) (@matyhtf)

- HTTP header/cookie에서 CRLF를 사용한 후 발생하는 header 주입 문제가 수정됩니다 (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)

- 코루틴 WebSocket Server에서 frame->fd가 비어 있는 문제 (#3549) (@huanghantao)

- hook phpredis가 발생하는 read error on connection 문제가 수정됩니다 (#3579) (@twose)

- MQTT 프로토콜 해석 문제가 수정됩니다 (#3573) (#3517) (9ad2b455) (@GXhua) (@sy-records)


## v4.4.19

[v4.4.19](https://github.com/swoole/swoole-src/releases/tag/v4.4.19)，이것은 BUG 수정 버전이며, 어떠한 하향 호환성 변화도 없습니다.

!> 주의: v4.4.x는 더 이상 주요한 유지 보수 버전이 아니며, 필요에 따라만 BUG가 수정됩니다.


### 수정 사항


- v4.5.2에서 모든 bug 수정 패치가 통합됩니다.


## v4.4.18

[v4.4.18](https://github.com/swoole/swoole-src/releases/tag/v4.4.18)，이것은 BUG 수정 버전이며, 어떠한 하향 호환성 변화도 없습니다.


### 기능 강화



- UDP 클라이언트가 이제 들어오는 도메인을 자동으로 해석할 수 있습니다 (#3236) (#3239) (@huanghantao)

- CLI 모드에서 더 이상 stdout과 stderr를 닫지 않습니다 (shutdown 이후 발생하는 오류 로그를 표시합니다) (#3249) (@twose)

- Coroutine\Http\Server는 몇 가지 일반적으로 사용되는 옵션을 지원합니다 (#3257) (@twose)

- WebSocket 핸드셋팅 sırasında cookie를 설정할 수 있습니다 (#3270) (#3272) (@twose)

- CURLOPT_FAILONERROR를 지원합니다 (swoole/library#20) (@sy-records)

- CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY를 지원합니다 (swoole/library#22) (@sy-records)

- CURLOPT_HTTPGET을 지원합니다 (swoole/library@d730bd08) (@shiguangqi)

- PHP-Redis 확장의 모든 버전을 가능한 한 호환합니다 (다양한 버전의 생성자 인수 매개변수가 다릅니다) (swoole/library#24) (@twose)
- 연결 객체를 복제하는 것을 금지합니다 (swoole/library#23) (@deminy)


### 수정 사항



- SSL 핸드셋팅 실패 문제 (dc5ac29a) (@twose)

- 잘못된 오류 메시지 생성 시 발생하는 메모리 오류 (#3229) (@twose)

- 공백인 proxy 검증 정보 (#3243) (@twose)

- Channel의 메모리 누수 문제 (실제 메모리 누수는 아님) (#3260) (@twose)

- Co\Http\Server가 순환 참조 시 발생하는 일회성 메모리 누수 (#3271) (@twose)

- ConnectionPool->fill 중의 문자열 오류 (swoole/library#18) (@NHZEX)

- curl 클라이언트가 리디렉션을 만나면 연결이 업데이트되지 않는 문제 (swoole/library#21) (@doubaokun)

- ioException 발생 시 포인터 오류 문제 (swoole/library@4d15a4c3) (@twose)

- ConnectionPool@put에 null을 전달했을 때 새 연결을 반납하지 않고 Deadlock이 발생하는 문제 (swoole/library#25) (@Sinute)
- mysqli 프록시 구현으로 인한 write_property 오류 (swoole/library#26) (@twose)


## v4.4.17

[v4.4.17](https://github.com/swoole/swoole-src/releases/tag/v4.4.17)，이것은 BUG 수정 버전이며, 어떠한 하향 호환성 변화도 없습니다.


### 기능 강화



- SSL 서버의 성능을 향상시킵니다 (#3077) (85a9a595) (@matyhtf)

- HTTP 헤드 크기 제한을 제거합니다 (#3187) limitation (@twose)

- MIPS 지원 (#3196) (@ekongyun)
- CURLOPT_HTTPAUTH를 지원합니다 (swoole/library@570318be) (@twose)


### 수정 사항



- package_length_func의 행동과 일회성 메모리 누수 문제를 수정합니다 (#3111) (@twose)

- HTTP 상태码 304의 잘못된 행동을 수정합니다 (#3118) (#3120) (@twose)

- Trace 로그의 잘못된 매크로 확장이 발생하는 메모리 오류를 수정합니다 (#3142) (@twose)

- OpenSSL 함수 서명 문제를 수정합니다 (#3154) (#3155) (@twose)

- SSL 오류 메시지를 수정합니다 (#3172) (@matyhtf) (@twose)

- PHP-7.4 호환성을 수정합니다 (@twose) (@matyhtf)

- HTTP-chunk의 길이 해석 문제를 수정합니다 (19a1c712) (@twose)

- chunked 모드의 multipart 요청 해석기 행동을 수정합니다 (3692d9de) (@twose)

- PHP-Debug 모드에서 ZEND_ASSUME 가정 실패를 수정합니다 (fc0982be) (@twose)

- Socket 오류 주소를 수정합니다 (d72c5e3a) (@twose)

- Socket getname (#3177) (#3179) (@matyhtf)

- 정적 파일 처리기가 빈 파일에 대한 잘못된 처리 문제를 수정합니다 (#3182) (@twose)

- Coroutine\Http\Server에서 파일 업로드 문제를 수정합니다 (#3189) (#3191) (@twose)

- shutdown 기간 동안 발생할 수 있는 메모리 오류를 수정합니다 (44aef60a) (@matyhtf)

- Server->heartbeat (#3203) (@matyhtf)

- CPU 스케줄러가 죽은 순환을 스케줄 수 없는 상황을 수정합니다 (#3207) (@twose)

- 불변 배열에 대한 무효한 쓰기 작업을 수정합니다 (#3212) (@twose)

- WaitGroup이 여러 번 wait하는 문제를 수정합니다 (swoole/library@537a82e1) (@twose)

- 빈 header 처리 (cURL와 일관되도록) (swoole/library@7c92ed5a) (@twose)

- 비 IO 방법이 false를 반환했을 때 예외를 던지는 문제를 수정합니다 (swoole/library@f6997394) (@twose)
- cURL-hook에서 proxy 포트 번호가 헤드에 여러 번 추가되는 문제를 수정합니다 (swoole/library@5e94e5da) (@twose)


## v4.4.16

[v4.4.16](https://github.com/swoole/swoole-src/releases/tag/v4.4.16)，이것은 BUG 수정 버전이며, 어떠한 하향 호환성 변화도 없습니다.


### 기능 강화
- 지금 당신은 [Swoole 버전 지원 정보](https://github.com/swoole/swoole-src/blob/master/SUPPORTED.md)를 얻을 수 있습니다.

- 더욱 친화적인 오류 메시지 (0412f442) (09a48835) (@twose)

- 특정 시스템에서 시스템 호출 데드락에 빠지지 않도록 예방 (069a0092) (@matyhtf)
- PDOConfig에서 드라이버 옵션을 추가 (swoole/library#8) (@jcheron)


### 수정 사항



- http2_session.default_ctx 메모리 오류 수정 (bddbb9b1) (@twose)

- 미初始화된 http_context 수정 (ce77c641) (@twose)

- Table 모듈의 문서 오류 수정 (메모리 오류가 발생할 수 있음) (db4eec17) (@twose)

- Server 중 task-reload의 잠재적 문제 수정 (e4378278) (@GXhua)

- 불완전한 코루outine HTTP 서버 요청 원문 수정 (#3079) (#3085) (@hantaohuang)

- static handler 수정 (파일이 비어 있을 때 404 응답을 돌려서는 안됨) (#3084) (@Yurunsoft)

- http_compression_level 설정이 정상적으로 작동하지 않는 문제 수정 (16f9274e) (@twose)

- Coroutine HTTP2 서버가 handle가 등록되지 않아 null 포인트 오류 발생하는 문제 수정 (ed680989) (@twose)

- socket_dontwait가 작동하지 않는 문제 수정 (27589376) (@matyhtf)

- zend::eval이 여러 번 실행될 수 있는 문제 수정 (#3099) (@GXhua)

- HTTP2 서버가 연결이 닫힌 후 응답으로 인해 발생하는 null 포인트 오류 수정 (#3110) (@twose)

- PDOStatementProxy::setFetchMode 적절히 적용되지 않는 문제 수정 (swoole/library#13) (@jcheron)
