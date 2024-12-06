# 도서관

Swoole v4 이후로 내장적으로 [도서관](https://github.com/swoole/library) 모듈이 추가되었습니다. 이 모듈은 **PHP 코드로 커널 기능을 작성**할 수 있게 되어, 기본 인프라가 더욱 안정적이고 신뢰할 수 있게 되었습니다.

!> 이 모듈은 또한 Composer를 통해 별도로 설치할 수 있으며, 별도로 설치하여 사용하려면 `php.ini`에서 `swoole.enable_library=Off`을 설정하여 확장 내장된 도서관을 비활성화해야 합니다.

현재 제공되는 도구 구성 요소는 다음과 같습니다:

- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) : 병렬 코루outine 작업을 기다리는 데 사용됩니다. [문서](/coroutine/wait_group)

- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI) : FastCGI 클라이언트입니다. [문서](/coroutine_client/fastcgi)

- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php) : 코루outine Server입니다. [문서](/coroutine/server)

- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php) : 코루outine 배리器等. [문서](/coroutine/barrier)

- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl) : CURL의 코루outine화입니다. [문서](/runtime?id=swoole_hook_curl)

- [Database](https://github.com/swoole/library/tree/master/src/core/Database) : 각종 데이터베이스 연결 풀과 객체 대행의 고급 패키징입니다. [문서](/coroutine/conn_pool?id=database)

- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) : 원시 연결 풀입니다. [문서](/coroutine/conn_pool?id=connectionpool)

- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php) : 프로세스 관리자입니다. [문서](/process/process_manager)

- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php), [ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php), [MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php) : 객체 지향 스타일의 Array와 String 프로그래밍을 위한 것입니다.

- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php) : 제공되는 일부 코루outine 함수입니다. [문서](/coroutine/coroutine?id=函数)

- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php) : 일반적으로 사용되는 구성 상수입니다.

- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php) : HTTP 상태 코드입니다.

## 예제 코드

[예제](https://github.com/swoole/library/tree/master/examples)
