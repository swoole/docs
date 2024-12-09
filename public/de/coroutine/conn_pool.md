# Verbindungspool

Ab der Version `v4.4.13` bietet Swoole einen eingebauten Koroutinenverbindungspool an, der in diesem Kapitel erklärt wird, wie man ihn verwendet.


## Verbindungspool

[Verbindungspool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php), der ursprüngliche Verbindungspool, basiert auf Channel für automatischen Scheduling und unterstützt die Übertragung beliebiger Konstruktoren (`callable`), die einen Verbindungsgegenstand zurückgeben müssen.

* Die `get` Methode obtaint eine Verbindung (wird eine neue Verbindung erstellt, wenn der Pool voll ist)
* Die `put` Methode recyceln Sie die Verbindung
* Die `fill` Methode füllt den Verbindungspool (erstellt Verbindungen im Voraus)
* Die `close` Methode schließt den Verbindungspool

!> [Simps Framework](https://simps.io) [DB Komponente](https://github.com/simple-swoole/db) ist auf der Basis von Database封装 und implementiert Funktionen wie automatisches Zurückgeben von Verbindungen und Transaktionen, die als Referenz oder direkt verwendet werden können, siehe [Simps Dokumentation](https://simps.io/#/zh-cn/database/mysql) für spezifische Informationen.


## Database

Ersteige Einpackungen für verschiedene Datenbankverbindungspools und Objektagenten, die automatisches Unterbrechen und yeniden Herstellen von Verbindungen unterstützen. Derzeit werden Unterstützung für PDO, MySQLi und Redis-Datenbanken bereitgestellt:

* `PDOConfig`, `PDOProxy`, `PDOPool`
* `MysqliConfig`, `MysqliProxy`, `MysqliPool`
* `RedisConfig`, `RedisProxy`, `RedisPool`

!> 1. Bei MySQL kann das automatische Unterbrechen und yeniden Herstellen der meisten Verbindungskontexte (Fetch-Modus, festgelegte Attribute, prekompilierte Statements usw.) automatisch wiederhergestellt werden, aber Kontexte wie Transaktionen können nicht wiederhergestellt werden. Wenn eine Verbindung in einer Transaktion unterbrochen wird, wird eine Ausnahme geworfen. Bitte bewerten selbst die Zuverlässigkeit des yeniden Herstellungsprozesses;  
2. Das Zurückgeben einer Verbindung, die in einer Transaktion ist, an den Verbindungspool ist eine undefinierten Handlung. Entwickler müssen sicherstellen, dass die zurückgegebene Verbindung wiederverwendbar ist;  
3. Wenn ein Verbindungsgegenstand eine Ausnahme wirft und nicht wiederverwendbar ist, muss der Entwickler die Methode `$pool->put(null);` aufrufen, um eine leere Verbindung zurückzugeben, um das Gleichgewicht der Anzahl der Verbindungen im Pool zu gewährleisten.


### PDOPool/MysqliPool/RedisPool :id=pool

Gebraucht, um Verbindungspool-Objekte zu erstellen, gibt es zwei Parameter, nämlich das entsprechende Config-Objekt und die Größe des Verbindungspools.

```php
$pool = new \Swoole\Database\PDOPool(Swoole\Database\PDOConfig $config, int $size);

$pool = new \Swoole\Database\MysqliPool(Swoole\Database\MysqliConfig $config, int $size);

$pool = new \Swoole\Database\RedisPool(Swoole\Database\RedisConfig $config, int $size);
```

  * **Parameter** 

    * **`$config`**
      * **Funktion**: Das entsprechende Config-Objekt, die spezifische Verwendung kann im folgenden [Beispiel](/coroutine/conn_pool?id=Beispiel) nachgelesen werden
      * **Standardwert**: Keiner
      * **Andere Werte**: [[PDOConfig](https://github.com/swoole/library/blob/master/src/core/Database/PDOConfig.php), [RedisConfig](https://github.com/swoole/library/blob/master/src/core/Database/RedisConfig.php), [MysqliConfig](https://github.com/swoole/library/blob/master/src/core/Database/MysqliConfig.php)]
      
    * **`int $size`**
      * **Funktion**: Größe des Verbindungspools
      * **Standardwert**: 64
      * **Andere Werte**: Keiner


## Beispielverwendung


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
echo 'Verwendet ' . $s . 's für ' . N . ' Abfrage' . PHP_EOL;
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
echo 'Verwendet ' . $s . 's für ' . (N * 2) . ' Abfrage' . PHP_EOL;
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
echo 'Verwendet ' . $s . 's für ' . N . ' Abfrage' . PHP_EOL;
```
