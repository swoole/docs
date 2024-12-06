# Connexion Pooling

À partir de la version `v4.4.13`, Swoole offre un pool de connexions co-résolues intégré. Ce chapitre explique comment utiliser ce pool de connexions.

## ConnectionPool

[ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php), le pool de connexions original, est basé sur l'ordonnancement automatique des canaux et prend en charge n'importe quel constructeur (`callable`). Le constructeur doit retourner un objet de connexion.

* La méthode `get` obtient une connexion (une nouvelle connexion est créée si le pool n'est pas plein)
* La méthode `put` récupère une connexion
* La méthode `fill` remplit le pool de connexions (création de connexions à l'avance)
* La méthode `close` ferme le pool de connexions

!> Le [framework Simps](https://simps.io) et son [component DB](https://github.com/simple-swoole/db), qui encapsulent la base de données, offrent des fonctionnalités telles que le retour automatique des connexions et les transactions. Ils peuvent être utilisés comme référence ou directement, en particulier en consultant la [documentation Simps](https://simps.io/#/zh-cn/database/mysql).

## Database

Des encapsulations avancées de divers pools de connexions de bases de données et de proxies d'objets, avec prise en charge de la reconnexion automatique en cas de déconnexion. Actuellement, cela inclut les types de bases de données suivants :

* `PDOConfig`, `PDOProxy`, `PDOPool`
* `MysqliConfig`, `MysqliProxy`, `MysqliPool`
* `RedisConfig`, `RedisProxy`, `RedisPool`

!> 1. La reconnexion automatique pour MySQL peut restaurer la plupart des contextes de connexion (mode fetch, attributs définis, statements compilés, etc.), mais certains contextes tels que les transactions ne peuvent pas être restaurés. Si une connexion en transaction est déconnectée, une exception sera levée. Veuillez évaluer la fiabilité de la reconnexion ;  
2. Rendre une connexion en transaction au pool de connexions est une behaviour non définie. Les développeurs doivent s'assurer que la connexion rendue est réutilisable ;  
3. Si un objet de connexion rencontre une exception et n'est pas réutilisable, le développeur doit appeler `$pool->put(null);` pour rendre une connexion vide afin d'équilibrer le nombre de connexions dans le pool.

### PDOPool/MysqliPool/RedisPool :id=pool

Utilisé pour créer un objet de pool de connexions, il existe deux paramètres : l'objet Config correspondant et la taille du pool de connexions.

```php
$pool = new \Swoole\Database\PDOPool(new \Swoole\Database\PDOConfig $config, int $size);

$pool = new \Swoole\Database\MysqliPool(new \Swoole\Database\MysqliConfig $config, int $size);

$pool = new \Swoole\Database\RedisPool(new \Swoole\Database\RedisConfig $config, int $size);
```

  * **Paramètres** 

    * **`$config`**
      * **Fonctionnalité** : L'objet Config correspondant, pour une utilisation spécifique, veuillez consulter l'exemple d'utilisation ci-dessous [/coroutine/conn_pool?id=exemple d'utilisation]
      * **Valeur par défaut** : None
      * **Autres valeurs** : [[PDOConfig](https://github.com/swoole/library/blob/master/src/core/Database/PDOConfig.php), [RedisConfig](https://github.com/swoole/library/blob/master/src/core/Database/RedisConfig.php), [MysqliConfig](https://github.com/swoole/library/blob/master/src/core/Database/MysqliConfig.php)]
      
    * **`int $size`**
      * **Fonctionnalité** : La taille du pool de connexions
      * **Valeur par défaut** : 64
      * **Autres valeurs** : None


## Exemple d'utilisation


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
echo 'Utilisation de ' . $s . 's pour ' . N . ' requêtes' . PHP_EOL;
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
echo 'Utilisation de ' . $s . 's pour ' . (N * 2) . ' requêtes' . PHP_EOL;
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
echo 'Utilisation de ' . $s . 's pour ' . N . ' requêtes' . PHP_EOL;
```
