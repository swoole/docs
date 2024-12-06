# 코루틴 Redis 클라이언트

!> 이 클라이언트는 더 이상 사용되지 않으며, `Swoole\Runtime::enableCoroutine + phpredis` 또는 `predis` 방식, 즉 [하이브리드 코루틴화](/runtime) 원시 PHP의 `redis` 클라이언트를 사용하는 것이 권장됩니다.

!> Swoole 6.0 이후, 이 코루틴 Redis 클라이언트는 제거되었습니다.


## 사용 예제

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});
```

!> `subscribe` `pSubscribe`는 `defer(true)`의 경우에 사용할 수 없습니다.


## 방법

!> 방법은 기본적으로 [phpredis](https://github.com/phpredis/phpredis)와 일치합니다.

다음 설명은 [phpredis](https://github.com/phpredis/phpredis)와 다른 구현입니다:

1. 아직 구현되지 않은 Redis 명령: `scan object sort migrate hscan sscan zscan`;

2. `subscribe pSubscribe`의 사용 방식은 콜백 함수를 설정할 필요가 없습니다;

3. PHP 변수의 직렬화 지원, `connect()` 메서드의 세 번째 매개변수를 `true`로 설정하면 PHP 변수의 직렬화 특성을 활성화합니다, 기본은 `false`입니다


### __construct()

Redis 코루틴 클라이언트의 생성자, Redis 연결의 구성 옵션을 설정할 수 있으며, `setOptions()` 메서드 매개변수와 일치합니다.

```php
Swoole\Coroutine\Redis::__construct(array $options = null);
```


### setOptions()

4.2.10 버전 이후에 추가된 이 메서드는 생성 및 연결 후 Redis 클라이언트의 일부 구성을 설정하는 데 사용됩니다

이 함수는 Swoole 스타일이며, `Key-Value` 키-값 쌍 배열을 통해 구성해야 합니다

```php
Swoole\Coroutine\Redis->setOptions(array $options): void
```

  * **구성 가능 옵션**


key | 설명
---|---
`connect_timeout` | 연결의 시간 제한, 기본은 전역 코루틴 `socket_connect_timeout` (1초)
`timeout` | 시간 제한, 기본은 전역 코루틴 `socket_timeout`, 참고 [클라이언트 시간 제한 규칙](/coroutine_client/init?id=시간제한규칙)
`serialize` | 자동 직렬화, 기본적으로 비활성화
`reconnect` | 자동 연결 시도 횟수, 연결이 시간 초과 등의 이유로 정상적으로 `close`되었을 경우, 다음 요청을 보낼 때 자동으로 연결 시도한 다음 요청을 보냅니다, 기본은 `1`회 (`true`), 한 번 실패하면 지정된 횟수만큼 더 시도하지 않고 수동으로 재연결해야 합니다. 이 패턴은 연결 유지에만 사용되며, 요청을 재발송하여 비幂등 인터페이스가 잘못된 문제를 일으키지 않습니다
`compatibility_mode` | `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore` 함수의 반환 결과가 `php-redis`와 다르다는 호환성 해결책, 활성화 시 `Co\Redis`와 `php-redis`의 반환 결과가 일치합니다, 기본적으로 비활성화 【해당 구성 항목은 `v4.4.0` 이상 버전에서 사용할 수 있습니다】


### set()

데이터를 저장합니다.

```php
Swoole\Coroutine\Redis->set(string $key, mixed $value, array|int $option): bool
```

  * **매개변수** 

    * **`string $key`**
      * **기능**：데이터의 키
      * **기본값**：없음
      * **기타값**：없음

    * **`string $value`**
      * **기능**：데이터 내용【비문자열 유형은 자동으로 직렬화됩니다】
      * **기본값**：없음
      * **기타값**：없음

    * **`string $options`**
      * **기능**：옵션
      * **기본값**：없음
      * **기타값**：없음

      !> `$option` 설명:  
      `정수형`：지급 시간을 설정합니다, 예를 들어 `3600`  
      `배열형`：고급 지급 설정, 예를 들어 `['nx', 'ex' => 10]` 、`['xx', 'px' => 1000]`

      !> `px`: 밀리초 단위 지급 시간을 나타냅니다  
      `ex`: 초 단위 지급 시간을 나타냅니다  
      `nx`: 존재하지 않을 경우에 지급 시간을 설정합니다  
      `xx`: 존재할 경우에 지급 시간을 설정합니다


### request()

Redis 서버에 사용자 정의 명령을 보냅니다. phpredis의 rawCommand와 유사합니다.

```php
Swoole\Coroutine\Redis->request(array $args): void
```

  * **매개변수** 

    * **`array $args`**
      * **기능**：매개변수 리스트, 반드시 배열 형식의 매개변수여야 합니다.【첫 번째 요소는 반드시 `Redis` 명령이며, 나머지 요소는 명령의 매개변수입니다, 하단은 자동으로 `Redis` 프로토콜 요청을 포장하여 보냅니다.】
      * **기본값**：없음
      * **기타값**：없음

  * **반환값** 

`Redis` 서버가 명령에 대한 처리 방식에 따라 숫자, 부울형, 문자열, 배열 등의 유형을 반환할 수 있습니다.

  * **사용 예제** 

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379); // 로컬 UNIX 소켓의 경우에는 호스트 매개변수를 `unix://tmp/your_file.sock`와 같은 형식으로 작성해야 합니다
    $res = $redis->request(['object', 'encoding', 'key1']);
    var_dump($res);
});
```


## 속성


### errCode

오류 코드입니다.


오류 코드 | 설명
---|---
1 | 읽기 또는 쓰기 오류
2 | 기타...
3 | 파일 끝
4 | 프로토콜 오류
5 | 메모리 부족


### errMsg

오류 메시지입니다.


### connected

현재 Redis 클라이언트가 서버에 연결되어 있는지를 나타냅니다.


## 상수

`multi($mode)` 메서드에 사용되며, 기본은 `SWOOLE_REDIS_MODE_MULTI` 모드입니다:

* SWOOLE_REDIS_MODE_MULTI
* SWOOLE_REDIS_MODE_PIPELINE

`type()` 명령의 반환값을 판단하는 데 사용됩니다:

* SWOOLE_REDIS_TYPE_NOT_FOUND
* SWOOLE_REDIS_TYPE_STRING
* SWOOLE_REDIS_TYPE_SET
* SWOOLE_REDIS_TYPE_LIST
* SWOOLE_REDIS_TYPE_ZSET
* SWOOLE_REDIS_TYPE_HASH


## 트랜잭션 모드

`multi`와 `exec`를 사용하여 Redis의 트랜잭션 모드를 구현할 수 있습니다.

  * **알림**

    * `multi` 명령어로 트랜잭션을 시작하면 이후 모든 명령어가 대기열에 추가되어 실행을 기다립니다
    * `exec` 명령어로 트랜잭션 중 모든 작업을 실행하고 결과를 한 번에 반환합니다

  * **사용 예제**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key3', 'rango');
    $redis->get('key1');
    $redis->get('key2');
    $redis->get('key3');

    $result = $redis->exec();
    var_dump($result);
});
```


## 구독 모드

!> Swoole 버전 >= v4.2.13에서 사용할 수 있으며, **4.2.12 이하 버전의 구독 모드는 BUG가 존재합니다**


### 구독

`phpredis`와 달리 `subscribe/psubscribe`는 코루틴 스타일입니다.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // 또는 psubscribe를 사용하여 구독합니다
    {
        while ($msg = $redis->recv()) {
            // msg는 배열로 구성되며 다음 정보를 포함합니다
            // $type # 반환값의 유형: 구독 성공을 나타냅니다
            // $name # 구독한 채널 이름 또는 원래 채널 이름
            // $info  # 현재 구독 중인 채널 수 또는 정보 내용
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') { // 또는 psubscribe
                // 채널 구독 성공 메시지, 구독한 채널 수 만큼 반환됩니다
            } else if ($type == 'unsubscribe' && $info == 0){ // 또는 punsubscribe
                break; // 구독 취소 메시지를 받고, 남은 구독 채널 수가 0이면 더 이상 수신하지 않고 루프를 종료합니다
            } else if ($type == 'message') {  // psubscribe의 경우에는 pmessage입니다
                var_dump($name); // 원래 채널 이름을 출력합니다
                var_dump($info); // 메시지를 출력합니다
                // balabalaba.... # 메시지를 처리합니다
                if ($need_unsubscribe) { // 특정 상황에서 구독을 취소해야 할 때
                    $redis->unsubscribe(); // 계속해서 수신하여 구독 취소가 완료되도록 기다립니다
                }
            }
        }
    }
});
```
### 탈퇴

탈퇴는 `unsubscribe/punsubscribe`를 사용하며, `$redis->unsubscribe(['channel1'])`를 수행합니다.

이때 `$redis->recv()`는 탈퇴 메시지를 받게 됩니다. 여러 채널을 탈퇴하면 여러 메시지를 받게 됩니다.
    
!> 주의: 탈퇴 후에는 마지막 탈퇴 메시지를 받기까지 계속해서 `recv()`을 수행해야 합니다( `$msg[2] == 0` ). 이 메시지를 받은 후에야 탈퇴 모드를 종료할 수 있습니다.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // or use psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg is an array containing the following information
            // $type # return type: show subscription success
            // $name # subscribed channel name or source channel name
            // $info  # the number of channels or information content currently subscribed
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') // or psubscribe
            {
                // channel subscription success message
            }
            else if ($type == 'unsubscribe' && $info == 0) // or punsubscribe
            {
                break; // received the unsubscribe message, and the number of channels remaining for the subscription is 0, no longer received, break the loop
            }
            else if ($type == 'message') // if it's psubscribe，here is pmessage
            {
                // print source channel name
                var_dump($name);
                // print message
                var_dump($info);
                // handle messsage
                if ($need_unsubscribe) // in some cases, you need to unsubscribe
                {
                    $redis->unsubscribe(); // continue recv to wait unsubscribe finished
                }
            }
        }
    }
});
```

## 호환성 모드

`Co\Redis`의 `hmGet/hGetAll/zrange/zrevrange/zrangebyscore/zrevrangebyscore` 명령이 `phpredis` 확장과 결과 형식이 일관되지 않는 문제는 이미 해결되었습니다 [#2529](https://github.com/swoole/swoole-src/pull/2529).

오래된 버전을 호환하기 위해 `$redis->setOptions(['compatibility_mode' => true]);` 설정을 추가하면 `Co\Redis`와 `phpredis`의 결과가 일관되도록 보장됩니다.

!> Swoole 버전 >= `v4.4.0`에서 사용할 수 있습니다.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->setOptions(['compatibility_mode' => true]);
    $redis->connect('127.0.0.1', 6379);

    $co_get_val = $redis->get('novalue');
    $co_zrank_val = $redis->zRank('novalue', 1);
    $co_hgetall_val = $redis->hGetAll('hkey');
    $co_hmget_val = $redis->hmGet('hkey', array(3, 5));
    $co_zrange_val = $redis->zRange('zkey', 0, 99, true);
    $co_zrevrange_val = $redis->zRevRange('zkey', 0, 99, true);
    $co_zrangebyscore_val = $redis->zRangeByScore('zkey', 0, 99, ['withscores' => true]);
    $co_zrevrangebyscore_val = $redis->zRevRangeByScore('zkey', 99, 0, ['withscores' => true]);
});
```
