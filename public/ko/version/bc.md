# 하향 호환성 변경사항


## v5.0.0
* `Server`의 기본 운영 모드를 `SWOOLE_BASE`로 수정
* 최소 `PHP` 버전 요구 사항을 `8.0`으로 상향
* 모든 클래스 방법과 함수에 유형 제한이 추가되어 강한 유형 모드로 변경됨
* underscore `PSR-0`의 카테고리명이 제거되며, namespace 스타일의 클래스명만 유지됨. 예: `swoole_server`는 `Swoole\Server`로 수정해야 함
* `Swoole\Coroutine\Redis`와 `Swoole\Coroutine\MySQL`가 비활성화되었으며, `Runtime Hook`+원시 `Redis`/`MySQL` 클라이언트를 사용해야 함


## v4.8.0


- `BASE` 모드에서, `onStart` 콜백은 항상 첫 번째 작업 프로세스(`workerId`가 `0`)가 시작될 때 콜백되어 `onWorkerStart`보다 먼저 실행됨. `onStart` 함수에서는 항상 코루outine `API`를 사용할 수 있으며, `Worker-0`이 치명적인 오류로 재시작될 경우 `onStart`가 다시 콜백됨
기존 버전에서, `onStart`는 단일 작업 프로세스인 경우 `Worker-0`에서 콜백되었고, 여러 작업 프로세스인 경우 `Manager` 프로세스에서 실행됨.


## v4.7.0


- `Table\Row`가 제거되어 `Table`이 더 이상 배열 방식으로 읽기/쓰기를 지원하지 않음


## v4.6.0



- `session id`의 최대 제한이 제거되어 중복되지 않음

- 코루outine 사용 시 안전하지 않은 기능이 비활성화되며, 이는 `pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait`를 포함함

- 코루outine hook가 기본적으로 활성화됨

- PHP7.1 지원이 중단됨
- `Event::rshutdown()`가 비활성화되어 Coroutine\run을 사용해야 함


## v4.5.4



- `SWOOLE_HOOK_ALL`은 `SWOOLE_HOOK_CURL`도 포함됨
- `ssl_method`가 제거되어 `ssl_protocols`만 지원됨


## v4.4.12


- 이 버전에서는 WebSocket 프레임 압축이 지원되며, push 방법의 세 번째 매개변수로 flags가 변경됨. strict_types가 설정되지 않은 경우 코드 호환성에 영향을 주지 않지만, 그렇지 않을 경우 bool이 int로 암시적으로 변환될 수 없는 유형 오류가 발생할 수 있습니다. 이 문제는 v4.4.13에서 수정될 예정입니다


## v4.4.1


- 등록된 신호는 더 이상 이벤트 루프를 유지하는 조건으로 사용되지 않으며, **프로그램이 신호만 등록하고 다른 작업을 하지 않는 경우 빈 상태로 간주되어 즉시 종료됩니다** (이때는 타이머를 등록하여 프로세스 종료를 방지할 수 있습니다)


## v4.4.0



- PHP 공식과 일치하도록 `PHP7.0` 지원이 중단됨 (@matyhtf)

- `Serialize` 모듈이 제거되어 별도의 [ext-serialize](https://github.com/swoole/ext-serialize) 확장에서 유지됨

- `PostgreSQL` 모듈이 제거되어 별도의 [ext-postgresql](https://github.com/swoole/ext-postgresql) 확장에서 유지됨

- `Runtime::enableCoroutine`가 더 이상 자동으로 코루outine 내외 환경에 호환되지 않으며, 한번 활성화되면 모든 블록링操作은 코루outine 내에서 호출해야 함 (@matyhtf)
- 새로운 코루outine `MySQL` 클라이언트 드라이버가 도입되어 기본 설계가 더욱 규범적이 되었지만, 몇 가지 작은 하향 호환성 변경 사항이 있음 ( 자세한 내용은 [4.4.0 업데이트 로그](https://wiki.swoole.com/wiki/page/p-4.4.0.html)를 참고하세요)


## v4.3.0


- 모든 비동기 모듈이 제거되었으며, 자세한 내용은 [독립 비동기 확장](https://wiki.swoole.com/wiki/page/p-async_ext.html) 또는 [4.3.0 업데이트 로그](https://wiki.swoole.com/wiki/page/p-4.3.0.html)를 참고하세요


## v4.2.13

> 역사적 API 설계의 문제로 인한 불가피한 호환성 변경

* 코루outine Redis 클라이언트 구독 모드 조작이 변경되었으며, 자세한 내용은 [구독 모드](https://wiki.swoole.com/#/coroutine_client/redis?id=%e8%ae%a2%e9%98%85%e6%a8%a1%e5%bc%8f)를 참고하세요


## v4.2.12

> 실험적 특성 + 역사적 API 설계의 문제로 인한 불가피한 호환성 변경


- `task_async` 구성 요소가 제거되어 [task_enable_coroutine](https://wiki.swoole.com/#/server/setting?id=task_enable_coroutine)로 대체되었음


## v4.2.5


- `onReceive`와 `Server::getClientInfo`가 `UDP` 클라이언트에 대한 지원을 제거했음


## v4.2.0


- 비동기 `swoole_http2_client`가 완전히 제거되었으며, 코루outine HTTP2 클라이언트를 사용해야 함


## v4.0.4

이 버전부터, 비동기 `Http2\Client`는 `E_DEPRECATED` 경고를 발행하고 다음 버전에서 삭제됩니다. `Coroutine\Http2\Client`를 사용하여 대체해야 합니다.

`Http2\Response`의 `body` 속성이 `data`로 재명명되었습니다. 이 변경은 `request`와 `response`의 통일성을 보장하고, HTTP2 프로토콜의 프레임 유형 이름에 더 일치하도록 하기 위함입니다.

이 버전부터, `Coroutine\Http2\Client`는 상대적으로 완전한 HTTP2 프로토콜 지원을 갖추고 있어, 기업급 생산 환경의 응용 요구를 충족시킬 수 있습니다. 예를 들어 `grpc`, `etcd` 등이 있으므로, HTTP2 관련의 일련의 변경 사항은 매우 필요합니다.


## v4.0.3

`swoole_http2_response`와 `swoole_http2_request`를 일관되게 유지하기 위해 모든 속성명이 복수형으로 수정되었습니다. 다음 속성이 포함됩니다.



- `headers`
- `cookies`


## v4.0.2

> 기본 구현이 너무 복잡하여 유지가 어려우며, 사용자들은 자주 잘못 사용하는 경우가 많기 때문에 다음 API를 임시로 제거합니다:


- `Coroutine\Channel::select`

그러나 동시에 `Coroutine\Channel->pop` 방법의 두 번째 매개변수로 `timeout`를 추가하여 개발 요구를 충족시킵니다.


## v4.0

> 코루outine 커널이 업데이트되어 모든 함수의 어디서나 코루outine를 호출할 수 있게 되어 특별한 처리 필요 없어 다음 API가 삭제됩니다.


- `Coroutine::call_user_func`
- `Coroutine::call_user_func_array`
