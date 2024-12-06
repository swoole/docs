# 상수

!> 여기에는 모든 상수가 포함되어 있지 않습니다. 모든 상수를 보려면 방문하거나 설치하세요: [ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)


## Swoole


상수 | 역할
---|---
SWOOLE_VERSION | 현재 Swoole의 버전 번호, 문자열 유형, 예: 1.6.0


## 생성자 매개변수


상수 | 역할
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | Base 모드 사용, 비즈니스 코드는 Reactor 프로세스에서 직접 실행
[SWOOLE_PROCESS](/learn?id=swoole_process) | 프로세스 모드 사용, 비즈니스 코드는 Worker 프로세스에서 실행


## 소켓 유형


상수 | 역할
---|---
SWOOLE_SOCK_TCP | tcp 소켓 생성
SWOOLE_SOCK_TCP6 | tcp ipv6 소켓 생성
SWOOLE_SOCK_UDP | udp 소켓 생성
SWOOLE_SOCK_UDP6 | udp ipv6 소켓 생성
SWOOLE_SOCK_UNIX_DGRAM | unix dgram 소켓 생성
SWOOLE_SOCK_UNIX_STREAM | unix stream 소켓 생성
SWOOLE_SOCK_SYNC | 동기화된 클라이언트


## SSL 암호화 방법


상수 | 역할
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD(기본 암호화 방법) | -
SWOOLE_SSLv23_SERVER_METHOD | -
SWOOLE_SSLv23_CLIENT_METHOD | -
SWOOLE_TLSv1_METHOD | -
SWOOLE_TLSv1_SERVER_METHOD | -
SWOOLE_TLSv1_CLIENT_METHOD | -
SWOOLE_TLSv1_1_METHOD | -
SWOOLE_TLSv1_1_SERVER_METHOD | -
SWOOLE_TLSv1_1_CLIENT_METHOD | -
SWOOLE_TLSv1_2_METHOD | -
SWOOLE_TLSv1_2_SERVER_METHOD | -
SWOOLE_TLSv1_2_CLIENT_METHOD | -
SWOOLE_DTLSv1_METHOD | -
SWOOLE_DTLSv1_SERVER_METHOD | -
SWOOLE_DTLSv1_CLIENT_METHOD | -
SWOOLE_DTLS_SERVER_METHOD | -
SWOOLE_DTLS_CLIENT_METHOD | -

!> `SWOOLE_DTLSv1_METHOD`, `SWOOLE_DTLSv1_SERVER_METHOD`, `SWOOLE_DTLSv1_CLIENT_METHOD`은 Swoole 버전 >= `v4.5.0`에서 제거되었습니다.


## SSL 프로토콜


상수 | 역할
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

!> Swoole 버전 >= `v4.5.4`에서 사용할 수 있습니다.


## 로깅 수준


상수 | 역할
---|---
SWOOLE_LOG_DEBUG | 디버그 로깅, 커널 개발 디버깅에만 사용
SWOOLE_LOG_TRACE | 트레이스 로깅, 시스템 문제를 추적하는 데 사용할 수 있으며, 디버그 로깅은 신중하게 설정되어 중요한 정보를 포함합니다
SWOOLE_LOG_INFO | 일반 정보, 정보 표시에만 사용
SWOOLE_LOG_NOTICE | 알림 정보, 시스템은 재시작, 종료와 같은 일부 행동을 할 수 있습니다
SWOOLE_LOG_WARNING | 경고 정보, 시스템에는 일부 문제가 발생할 수 있습니다
SWOOLE_LOG_ERROR | 오류 정보, 시스템에서 일부 중요한 오류가 발생했으며 즉시 해결해야 합니다
SWOOLE_LOG_NONE | 로깅 정보를 비활성화하는 것과 같으며, 로깅 정보는抛出되지 않습니다.

!> `SWOOLE_LOG_DEBUG`와 `SWOOLE_LOG_TRACE` 두 가지 로깅은 Swoole 확장을编译할 때 [--enable-debug-log](/environment?id=debug 매개변수) 또는 [--enable-trace-log](/environment?id=debug 매개변수)를 사용해야 사용할 수 있습니다. 정상 버전에서 `log_level = SWOOLE_LOG_TRACE`를 설정해도 이러한 로깅을 출력할 수 없습니다.

## 트레이스 태그

온라인으로 운영 중인 서비스는 항상 대량의 요청을 처리하고 있으며, 하단에서 발생하는 로깅 수량은 매우 큽니다. `trace_flags`를 사용하여 트레이스 로깅의 태그를 설정하고 일부 트레이스 로깅만 출력할 수 있습니다. `trace_flags`는 `|` 연산자를 사용하여 여러 트레이스 항목을 설정할 수 있습니다.

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

하단에서 다음 트레이스 항목을 지원하며, `SWOOLE_TRACE_ALL`을 사용하여 모든 항목을 추적할 수 있습니다:

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`
