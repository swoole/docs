# Connection Pool

Swoole has provided a built-in coroutine connection pool since version `v4.4.13`. This section will explain how to use the corresponding connection pool.
## ConnectionPool

[ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) is an original connection pool based on Channel's automatic scheduling. It supports accepting any constructor (callable) that should return a connection object.

- The `get` method is used to get a connection (a new connection will be created when the pool is not full).
- The `put` method is used to recycle a connection.
- The `fill` method is used to fill the connection pool (pre-create connections).
- The `close` method is used to close the connection pool.

!> The [DB component](https://github.com/simple-swoole/db) of the [Simps framework](https://simps.io) is encapsulated based on the Database to achieve functions like automatic returning of connections and transactions. You can refer to or directly use it. For more details, please refer to the [Simps documentation](https://simps.io/#/zh-cn/database/mysql)
## Database

Advanced encapsulation of various database connection pools and object proxies, supporting automatic reconnection in case of disconnection. Currently, it includes support for three types of databases: 

* `PDOConfig`, `PDOProxy`, `PDOPool`
* `MysqliConfig`, `MysqliProxy`, `MysqliPool`
* `RedisConfig`, `RedisProxy`, `RedisPool`

!> 1. For MySQL, automatic reconnection can restore most connection contexts (fetch mode, set attributes, prepared statements, etc.) in case of disconnection. However, contexts like transactions cannot be restored. If a connection in a transaction is disconnected, an exception will be thrown. Please evaluate the reliability of reconnection on your own.
2. Returning a connection in a transaction back to the connection pool is an undefined behavior, and developers need to ensure that the returned connection is reusable.
3. If a connection object encounters an exception and cannot be reused, developers need to call `$pool->put(null);` to return a null connection to ensure a balanced connection pool.
### PDOPool/MysqliPool/RedisPool :id=pool

Used to create connection pool objects, there are two parameters, corresponding to the Config object and the size of the connection pool.

```php
$pool = new \Swoole\Database\PDOPool(Swoole\Database\PDOConfig $config, int $size);

$pool = new \Swoole\Database\MysqliPool(Swoole\Database\MysqliConfig $config, int $size);

$pool = new \Swoole\Database\RedisPool(Swoole\Database\RedisConfig $config, int $size);
```

  * **Parameters** 

    * **`$config`**
      * **Description**：Corresponding Config object, specific usage can be referred to the [example below](/coroutine/conn_pool?id=使用示例)
      * **Default value**：None
      * **Other values**：【[PDOConfig](https://github.com/swoole/library/blob/master/src/core/Database/PDOConfig.php)、[RedisConfig](https://github.com/swoole/library/blob/master/src/core/Database/RedisConfig.php)、[MysqliConfig](https://github.com/swoole/library/blob/master/src/core/Database/MysqliConfig.php)】
      
    * **`int $size`**
      * **Description**：Size of the connection pool
      * **Default value**：64
      * **Other values**：None
## Usage Example
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
The provided code is written in PHP using the Swoole extension for Coroutine support and Mysqli for database operations. This script sets up a connection pool with MySQL and performs a series of queries asynchronously.

The script creates a Mysqli pool with specific configurations like host, port, database name, charset, username, and password. It then runs a coroutine that handles multiple query operations concurrently. Each coroutine within the loop fetches a database connection from the pool, prepares a SELECT query to add two random numbers, binds parameters, executes the statement, fetches the result, and validates the result.

Finally, the script measures the time taken to perform N queries and displays the time taken in seconds for completion.

If you have any further questions or need additional information, feel free to ask!
