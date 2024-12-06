# 연결 풀

Swoole은 `v4.4.13` 버전부터 내장 코루틴 연결 풀을 제공하고 있으며, 이 장은 해당 연결 풀을 어떻게 사용할지 설명합니다.


## ConnectionPool

[ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php), 원본 연결 풀은 Channel을 기반으로 자동으로 스케줄링하며, 임의의 생성자(`callable`)를 전달할 수 있습니다. 생성자는 연결 객체를 반환해야 합니다.

* `get` 메서드는 연결을 획득합니다(연결 풀이 부족할 경우 새로운 연결을 만듭니다)
* `put` 메서드는 연결을 반납합니다
* `fill` 메서드는 연결 풀을 미리 만듭니다(사전에 연결을 만듭니다)
* `close` 메서드는 연결 풀을 닫습니다

!> [Simps 프레임워크](https://simps.io)의 [DB 구성 요소](https://github.com/simple-swoole/db)는 Database를 기반으로 포장하여 자동으로 연결 반환, 트랜잭션 등의 기능을 구현했으며, 참고하거나 직접 사용할 수 있습니다. 자세한 내용은 [Simps 문서](https://simps.io/#/zh-cn/database/mysql)를 확인하세요.


## Database

각종 데이터베이스 연결 풀과 객체 대리가 고급 포장되어 있으며, 자동으로 연결 끊기 재연결을 지원합니다. 현재 PDO, Mysqli, Redis 세 가지 데이터베이스 유형을 지원합니다:

* `PDOConfig`, `PDOProxy`, `PDOPool`
* `MysqliConfig`, `MysqliProxy`, `MysqliPool`
* `RedisConfig`, `RedisProxy`, `RedisPool`

!> 1. MySQL 연결 끊기 재연결은 대부분의 연결 맥락(fetch 모드, 설정된 attribute, 이미编译된 Statement 등)을 자동으로 복구할 수 있지만, 트랜잭션과 같은 맥락은 복구할 수 없습니다. 트랜잭션 중인 연결이 끊어지면 예외가 발생합니다. 재연결의 신뢰성을 스스로 평가해야 합니다;  
2. 트랜잭션 중인 연결을 연결 풀에 반납하는 것은 미정의된 행동이며, 개발자는 반납한 연결이 재사용 가능한지 보장해야 합니다;  
3. 연결 객체가 예외를 일으켜 재사용 불가능할 경우, 개발자는 `$pool->put(null);`를 호출하여 빈 연결을 반납하여 연결 풀의 수량 균형을 유지해야 합니다.


### PDOPool/MysqliPool/RedisPool :id=pool

연결 풀 객체를 만드는 데 사용되며, 두 개의 매개변수가 있습니다. 각각 해당하는 Config 객체와 연결 풀의 size입니다

```php
$pool = new \Swoole\Database\PDOPool(Swoole\Database\PDOConfig $config, int $size);

$pool = new \Swoole\Database\MysqliPool(Swoole\Database\MysqliConfig $config, int $size);

$pool = new \Swoole\Database\RedisPool(Swoole\Database\RedisConfig $config, int $size);
```

  * **매개변수** 

    * **`$config`**
      * **기능**: 해당하는 Config 객체로, 구체적인 사용법은 아래의[사용 예시](/coroutine/conn_pool?id=사용 예시)를 참고하세요
      * **기본값**: 없음
      * **기타 값**: 【[PDOConfig](https://github.com/swoole/library/blob/master/src/core/Database/PDOConfig.php)、[RedisConfig](https://github.com/swoole/library/blob/master/src/core/Database/RedisConfig.php)、[MysqliConfig](https://github.com/swoole/library/blob/master/src/core/Database/MysqliConfig.php)】
      
    * **`int $size`**
      * **기능**: 연결 풀의 수량
      * **기본값**: 64
      * **기타 값**: 없음


## 사용 예시


### PDO

```php
<?php
declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Swoole\Runtime;

const N = 1024;

Runtime::enableCoroutine();
$s = microtime(true);
Coroutine\run(function () {
    $pool = new PDOPool((new PDOConfig)
        ->withHost('127.0.0.1')
        ->withPort(3306)
        // ->withUnixSocket('/tmp/mysql.sock')
        ->withDbName('test')
        ->withCharset('utf8mb4')
        ->withUsername('root')
        ->withPassword('root')
    );
    for ($n = N; $n--;) {
        Coroutine::create(function () use ($pool) {
            $pdo = $pool->get();
            $statement = $pdo->prepare('SELECT ? + ?');
            if (!$statement) {
                throw new RuntimeException('Prepare failed');
            }
            $a = mt_rand(1, 100);
            $b = mt_rand(1, 100);
            $result = $statement->execute([$a, $b]);
            if (!$result) {
                throw new RuntimeException('Execute failed');
            }
            $result = $statement->fetchAll();
            if ($a + $b !== (int)$result[0][0]) {
                throw new RuntimeException('Bad result');
            }
            $pool->put($pdo);
        });
    }
});
$s = microtime(true) - $s;
echo 'Use ' . $s . 's for ' . N . ' queries' . PHP_EOL;
```


### Redis

```php
<?php
declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;
use Swoole\Runtime;

const N = 1024;

Runtime::enableCoroutine();
$s = microtime(true);
Coroutine\run(function () {
    $pool = new RedisPool((new RedisConfig)
        ->withHost('127.0.0.1')
        ->withPort(6379)
        ->withAuth('')
        ->withDbIndex(0)
        ->withTimeout(1)
    );
    for ($n = N; $n--;) {
        Coroutine::create(function () use ($pool) {
            $redis = $pool->get();
            $result = $redis->set('foo', 'bar');
            if (!$result) {
                throw new RuntimeException('Set failed');
            }
            $result = $redis->get('foo');
            if ($result !== 'bar') {
                throw new RuntimeException('Get failed');
            }
            $pool->put($redis);
        });
    }
});
$s = microtime(true) - $s;
echo 'Use ' . $s . 's for ' . (N * 2) . ' queries' . PHP_EOL;
```

### Mysqli

```php
<?php
declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Database\MysqliConfig;
use Swoole\Database\MysqliPool;
use Swoole\Runtime;

const N = 1024;

Runtime::enableCoroutine();
$s = microtime(true);
Coroutine\run(function () {
    $pool = new MysqliPool((new MysqliConfig)
        ->withHost('127.0.0.1')
        ->withPort(3306)
        // ->withUnixSocket('/tmp/mysql.sock')
        ->withDbName('test')
        ->withCharset('utf8mb4')
        ->withUsername('root')
        ->withPassword('root')
    );
    for ($n = N; $n--;) {
        Coroutine::create(function () use ($pool) {
            $mysqli = $pool->get();
            $statement = $mysqli->prepare('SELECT ? + ?');
            if (!$statement) {
                throw new RuntimeException('Prepare failed');
            }
            $a = mt_rand(1, 100);
            $b = mt_rand(1, 100);
            if (!$statement->bind_param('dd', $a, $b)) {
                throw new RuntimeException('Bind param failed');
            }
            if (!$statement->execute()) {
                throw new RuntimeException('Execute failed');
            }
            if (!$statement->bind_result($result)) {
                throw new RuntimeException('Bind result failed');
            }
            if (!$statement->fetch()) {
                throw new RuntimeException('Fetch failed');
            }
            if ($a + $b !== (int)$result) {
                throw new RuntimeException('Bad result');
            }
            while ($statement->fetch()) {
                continue;
            }
            $pool->put($mysqli);
        });
    }
});
$s = microtime(true) - $s;
echo 'Use ' . $s . 's for ' . N . ' queries' . PHP_EOL;
```
