# Пункты подключения

С начала версии `v4.4.13` Swoole предоставляет встроенный кластер пунктов подключения с использованием корутин. В этом разделе будет описано, как использовать соответствующий кластер пунктов подключения.


## Кластер пунктов подключения

[Кластер пунктов подключения](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php), исходный кластер пунктов подключения, основанный на автоматическом расписании каналов, поддерживает передачу любого конструктора (`callable`), и конструктор должен возвращать объект соединения.

* Метод `get` получает соединение (если кластер пунктов подключения не полон, то создается новое соединение)
* Метод `put` возвращает соединение
* Метод `fill` заполняет кластер пунктов подключения (создает соединения заранее)
* Метод `close` закрывает кластер пунктов подключения

!> [Фреймворк Simps](https://simps.io) [Компонент DB](https://github.com/simple-swoole/db) упакован на основе базы данных, реализует функции автоматического возврата соединений, транзакций и т.д., можно использовать для справки или прямо, подробности смотрите в [Документации Simps](https://simps.io/#/zh-cn/database/mysql)


## База данных

Высокая упаковки различных кластеров пунктов подключения и объектов прокси для базы данных, поддерживается автоматическое reconnection после обрыва связи. В настоящее время поддерживаются три типа баз данных: PDO, MySQLi, Redis:

* `PDOConfig`, `PDOProxy`, `PDOPool`
* `MysqliConfig`, `MysqliProxy`, `MysqliPool`
* `RedisConfig`, `RedisProxy`, `RedisPool`

!> 1. Автоматическое восстановление большинства контекстов соединений после обрыва связи с MySQL (fetch режим, установленные атрибуты, скомпилированные Statement и т.д.), но такие контексты, как транзакции, не могут быть восстановлены. Если соединение, находящееся в транзакции, обрывается, будет выброшена исключение, пожалуйста, оцените надежность reconnection самостоятельно;  
2. Возврат соединений, находящихся в транзакции, в кластер пунктов подключения является неопределенным поведением, разработчики должны гарантировать, что возвращенные соединения могут быть повторно использованы;  
3. Если объект соединения抛出 исключение и не может быть повторно использован, разработчик должен вызвать `$pool->put(null);` чтобы вернуть пустое соединение и обеспечить баланс количество соединений в кластере.


### PDOPool/MysqliPool/RedisPool :id=pool

Используется для создания объектов кластера пунктов подключения, существуют два параметра: соответствующий объект Config и размер кластера пунктов подключения.

```php
$pool = new \Swoole\Database\PDOPool(Swoole\Database\PDOConfig $config, int $size);

$pool = new \Swoole\Database\MysqliPool(Swoole\Database\MysqliConfig $config, int $size);

$pool = new \Swoole\Database\RedisPool(Swoole\Database\RedisConfig $config, int $size);
```

  * **Параметры** 

    * **`$config`**
      * **Функция**: соответствующий объект Config, конкретное использование можно посмотреть в следующем разделе [Пример использования](/coroutine/conn_pool?id=example)
      * **По умолчанию**: нет
      * **Другие значения**: 【[PDOConfig](https://github.com/swoole/library/blob/master/src/core/Database/PDOConfig.php)、[RedisConfig](https://github.com/swoole/library/blob/master/src/core/Database/RedisConfig.php)、[MysqliConfig](https://github.com/swoole/library/blob/master/src/core/Database/MysqliConfig.php)】
      
    * **`int $size`**
      * **Функция**: Количество пунктов подключения
      * **По умолчанию**: 64
      * **Другие значения**: нет


## Пример использования


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
echo 'Использовано ' . $s . ' секунды для ' . N . ' запросов' . PHP_EOL;
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
echo 'Использовано ' . $s . ' секунды для ' . (N * 2) . ' запросов' . PHP_EOL;
```

### MySQLi

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
echo 'Использовано ' . $s . ' секунды для ' . N . ' запросов' . PHP_EOL;
```
