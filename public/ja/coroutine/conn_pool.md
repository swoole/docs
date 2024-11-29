# 接続プール

Swooleは`v4.4.13`バージョンから内蔵のコロニーの接続プールを提供しています。このセクションでは、対応する接続プールを使用する方法を説明します。


## ConnectionPool

[ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php)、元の接続プールはChannelに基づいて自動的にスケジュールされ、任意のコンストラクタ（`callable`）を受け入れます。コンストラクタは接続オブジェクトを返す必要があります。

* `get`方法は接続を取得します（接続プールが満たない場合は新しい接続を作成します）
* `put`方法は接続をリサイクルします
* `fill`方法は接続プールを事前に作成します（接続を前もって作成します）
* `close`は接続プールを閉じます

!>[Simps フレームワーク](https://simps.io)の[DBコンポーネント](https://github.com/simple-swoole/db)はDatabaseに基づいて封装され、自動的に接続を返還し、トランザクションなどの機能を実現しています。参考になるかもしれませんし、直接使用することもできます。具体的なことは[Simpsドキュメント](https://simps.io/#/zh-cn/database/mysql)をご覧ください。


## Database

様々なデータベース接続プールとオブジェクトプロキサーの高度な封装で、自動的に切断して再接続する機能がサポートされています。現在、PDO、Mysqli、Redisの3種類のデータベースに対応しています：

* `PDOConfig`, `PDOProxy`, `PDOPool`
* `MysqliConfig`, `MysqliProxy`, `MysqliPool`
* `RedisConfig`, `RedisProxy`, `RedisPool`

!> 1. MySQLの切断して再接続は、ほとんどの接続コンテキスト（fetchモード、既に設定されたattribute、既にコンパiledされたStatementなど）を自動的に回復できますが、トランザクションなどのコンテキストは回復できません。トランザクション中の接続が切断された場合、例外が投げられますので、再接続の信頼性を自己評価してください；  
2. トランザクション中の接続を接続プールに返還することは未定義の行為であり、開発者は返還された接続が再利用可能であることを保証する必要があります；  
3. 接続オブジェクトが例外を起こして再利用できない場合、開発者は`$pool->put(null);`を呼び出して空の接続を返還し、接続プールの数量のバランスを保つ必要があります。


### PDOPool/MysqliPool/RedisPool :id=pool

接続プールオブジェクトを作成するために使用され、2つのパラメータがあります。それぞれが対応するConfigオブジェクトと接続プールのsizeです。

```php
$pool = new \Swoole\Database\PDOPool(Swoole\Database\PDOConfig $config, int $size);

$pool = new \Swoole\Database\MysqliPool(Swoole\Database\MysqliConfig $config, int $size);

$pool = new \Swoole\Database\RedisPool(Swoole\Database\RedisConfig $config, int $size);
```

  * **パラメータ** 

    * **`$config`**
      * **機能**：対応するConfigオブジェクトで、具体的な使用方法は以下の[使用例](/coroutine/conn_pool?id=使用例)を参照してください
      * **デフォルト値**：なし
      * **その他の値**：【[PDOConfig](https://github.com/swoole/library/blob/master/src/core/Database/PDOConfig.php)、[RedisConfig](https://github.com/swoole/library/blob/master/src/core/Database/RedisConfig.php)、[MysqliConfig](https://github.com/swoole/library/blob/master/src/core/Database/MysqliConfig.php)】
      
    * **`int $size`**
      * **機能**：接続プールの数
      * **デフォルト値**：64
      * **その他の値**：なし


## 使用示例


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
