# Coroutine\PostgreSQL 旧版

Client pour `PostgreSQL` en coroutines. Il est nécessaire de compiler l'extension [ext-postgresql](https://github.com/swoole/ext-postgresql) pour activer cette fonction.

> Cet文档 est uniquement applicable à Swoole < 5.0


## Compilation et installation

Téléchargez le code source : [https://github.com/swoole/ext-postgresql](https://github.com/swoole/ext-postgresql), vous devez installer la version releases correspondant à votre version de Swoole.

* Assurez-vous que la bibliothèque `libpq` est installée sur votre système
* Sur `mac`, après avoir installé `postgresql`, la bibliothèque `libpq` est fournie par défaut, il existe des différences entre les environnements, `ubuntu` peut nécessiter `apt-get install libpq-dev`, `centos` peut nécessiter `yum install postgresql10-devel`
* Vous pouvez également spécifier manuellement le répertoire de la bibliothèque `libpq`, par exemple : `./configure --with-libpq-dir=/etc/postgresql`


## Exemple d'utilisation

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    if (!$conn) {
        var_dump($pg->error);
        return;
    }
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchAll($result);
    var_dump($arr);
});
```


### Gestion des transactions

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    $pg->query('BEGIN');
    $result = $pg->query('SELECT * FROM test');
    $arr = $pg->fetchAll($result);
    $pg->query('COMMIT');
    var_dump($arr);
});
```


## Propriétés


### error

Obtenir les informations d'erreur.


## Méthodes


### connect()

Establir une connexion non bloquante en coroutines à `postgresql`.

```php
Swoole\Coroutine\PostgreSQL->connect(string $connection_string): bool
```

!> `$connection_string` est une chaîne de connexion, retourne `true` si la connexion est réussie, `false` si elle échoue, vous pouvez utiliser la propriété [error](/coroutine_client/postgresql?id=error) pour obtenir les informations d'erreur.
  * **Exemple**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
    var_dump($pg->error, $conn);
});
```


### query()

Exécuter une commande SQL asynchrone non bloquante en coroutines.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): resource;
```

  * **Paramètres** 

    * **`string $sql`**
      * **Fonction** : Commande SQL
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Exemples**

    * **select**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
        var_dump($arr);
    });
    ```

    * **retourner l'id inséré**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
        $result = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $pg->fetchRow($result);
        var_dump($arr);
    });
    ```

    * **transaction**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $pg->query('BEGIN;');
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
        $pg->query('COMMIT;');
        var_dump($arr);
    });
    ```


### fetchAll()

```php
Swoole\Coroutine\PostgreSQL->fetchAll(resource $queryResult, $resultType = SW_PGSQL_ASSOC):? array;
```

  * **Paramètres**
    * **$resultType**
      * **Fonction** : Constante. Paramètre optionnel qui contrôle la manière dont les valeurs de retour sont initialisées.
      * **Valeur par défaut** : `SW_PGSQL_ASSOC`
      * **Autres valeurs** : Aucun

      Valeur | Retour
      ---|---
      SW_PGSQL_ASSOC | Retourne un tableau associatif avec les noms des champs comme clés.
      SW_PGSQL_NUM | Retourne un tableau avec les numéros de fields comme clés.
      SW_PGSQL_BOTH | Retourne un tableau avec les deux comme clés.

  * **Retour**

    * Retourne tous les enregistrements de la résultat extraits comme un tableau. Si il n'y a plus de lignes à extraire, retourne `false`.


### affectedRows()

Retourne le nombre d'enregistrements affectés. 

```php
Swoole\Coroutine\PostgreSQL->affectedRows(resource $queryResult): int
```


### numRows()

Retourne le nombre de lignes.

```php
Swoole\Coroutine\PostgreSQL->numRows(resource $queryResult): int
```


### fetchObject()

Extraire une ligne comme objet. 

```php
Swoole\Coroutine\PostgreSQL->fetchObject(resource $queryResult, int $row): object;
```

  * **Exemple**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    for ($row = 0; $row < $pg->numRows($result); $row++) {
        $data = $pg->fetchObject($result, $row);
        echo $data->id . " \n ";
    }
});
```
```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    while ($data = $pg->fetchObject($result, $row)) {
        echo $data->id . " \n ";
        $row++;
    }
});
```


### fetchAssoc()

Extraire une ligne comme un tableau associatif.

```php
Swoole\Coroutine\PostgreSQL->fetchAssoc(resource $queryResult, int $row): array
```


### fetchArray()

Extraire une ligne comme un tableau.

```php
Swoole\Coroutine\PostgreSQL->fetchArray(resource $queryResult, int $row, $resultType = SW_PGSQL_BOTH): array|false
```

  * **Paramètres**
    * **$row**
      * **Fonction** : `$row` est le numéro de la ligne (enregistrement) que l'on souhaite extraire. La première ligne est `0`.
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun
    * **$resultType**
      * **Fonction** : Constante. Paramètre optionnel qui contrôle la manière dont les valeurs de retour sont initialisées.
      * **Valeur par défaut** : `SW_PGSQL_BOTH`
      * **Autres valeurs** : Aucun

      Valeur | Retour
      ---|---
      SW_PGSQL_ASSOC | Retourne un tableau associatif avec les noms des champs comme clés.
      SW_PGSQL_NUM | Retourne un tableau avec les numéros de fields comme clés.
      SW_PGSQL_BOTH | Retourne un tableau avec les deux comme clés.

  * **Retour**

    * Retourne un tableau cohérent avec la ligne (tuple/enregistrement) extraite. Si aucune autre ligne n'est disponible pour l'extraction, retourne `false`.

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchArray($result, 1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```
### fetchRow()

Extrait une ligne de données (enregistrement) d'après la ressource spécifiée `result` et la retourne sous forme d'un tableau. Chaque colonne obtenue est stockée dans le tableau à partir de l'offset `0`.

```php
Swoole\Coroutine\PostgreSQL->fetchRow(resource $queryResult, int $row, $resultType = SW_PGSQL_NUM): array|false
```

  * **Paramètres**
    * **`int $row`**
      * **Fonction** : `row` est le numéro de la ligne (enregistrement) que l'on souhaite obtenir. La première ligne est `0`.
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun
    * **`$resultType`**
      * **Fonction** : Constante. Paramètre optionnel qui contrôle la manière dont la valeur de retour est initialisée.
      * **Valeur par défaut** : `SW_PGSQL_NUM`
      * **Autres valeurs** : Aucun

      Valeur | Retour
      --- | ---
      SW_PGSQL_ASSOC | Retourne un tableau associatif dont les clés sont les noms de champs.
      SW_PGSQL_NUM | Retourne un tableau dont les clés sont les numéros de champs.
      SW_PGSQL_BOTH | Retourne un tableau dont les clés sont à la fois les noms de champs et les numéros de champs.

  * **Retour**

    * Le tableau retourné est conforme à la ligne extraite. Si il n'y a plus de lignes à extraire pour `row`, alors `false` est retourné.

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    while ($row = $pg->fetchRow($result)) {
        echo "name: $row[0]  mobile: $row[1]" . PHP_EOL;
    }
});
```

### metaData()

Affiche les métadonnées d'une table. Version asynchrone et non bloquante avec coroutines.

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```    
  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->metaData('test');
    var_dump($result);
});
```

### prepare()

Préparation.

```php
Swoole\Coroutine\PostgreSQL->prepare(string $name, string $sql);
Swoole\Coroutine\PostgreSQL->execute(string $name, array $bind);
```

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $pg->prepare("my_query", "select * from  test where id > $1 and id < $2");
    $res = $pg->execute("my_query", array(1, 3));
    $arr = $pg->fetchAll($res);
    var_dump($arr);
});
```
