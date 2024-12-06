# Coroutine\PostgreSQL

Client pour PostgreSQL en coroutines.

!> Reconçu complètement dans la version 5.0 de Swoole, avec une utilisation complètement différente des anciennes versions. Si vous utilisez une version ancienne, veuillez consulter la [documentation ancienne](/coroutine_client/postgresql-old.md).

!> Après la version 6.0 de Swoole, le client PostgreSQL en coroutines a été supprimé. Veuillez utiliser [la connection PDO_PGSQL en coroutines](/runtime?id=swoole_hook_pdo_pgsql) à la place.


## Compilation et installation

* Assurez-vous que la bibliothèque `libpq` est installée sur votre système.
* Après l'installation de `postgresql` sur `mac`, la bibliothèque `libpq` est fournie par défaut. Il existe des différences entre les environnements, par exemple, sur `ubuntu`, vous pourriez avoir besoin d'exécuter `apt-get install libpq-dev`, et sur `centos`, vous pourriez avoir besoin d'exécuter `yum install postgresql10-devel`.
* Lors de la compilation de Swoole, ajoutez l'option de compilation : `./configure --enable-swoole-pgsql`.


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
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchAll();
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
    $stmt = $pg->query('SELECT * FROM test');
    $arr = $stmt->fetchAll();
    $pg->query('COMMIT');
    var_dump($arr);
});
```


## Propriétés


### error

Obtenir les informations d'erreur.


## Méthodes


### connect()

Établir une connexion non bloquante en coroutines PostgreSQL.

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo` est une information de connexion. Si la connexion est réussie, elle retourne true, sinon false. Vous pouvez utiliser la propriété [error](/coroutine_client/postgresql?id=error) pour obtenir les informations d'erreur.
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
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
```

  * **Paramètres** 

    * **`string $sql`**
      * **Fonction** : Commande SQL
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Exemples**

    * **select**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        var_dump($arr);
    });
    ```

    * **retourner l'ID inséré**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
        $stmt = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $stmt->fetchRow();
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
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        $pg->query('COMMIT;');
        var_dump($arr);
    });
    ```


### metaData()

Obtenir les métadonnées de la table. Version asynchrone non bloquante en coroutines.

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
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $stmt = $pg->prepare("select * from test where id > $1 and id < $2");
    $res = $stmt->execute(array(1, 3));
    $arr = $stmt->fetchAll();
    var_dump($arr);
});
```


## PostgreSQLStatement

Classe : `Swoole\Coroutine\PostgreSQLStatement`

Toutes les requêtes retournent un objet `PostgreSQLStatement`.


### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **Paramètres**
    * **`$result_type`**
      * **Fonction** : Constant. Paramètre optionnel qui contrôle la manière dont les valeurs de retour sont initialisées.
      * **Valeur par défaut** : `SW_PGSQL_ASSOC`
      * **Autres valeurs** : None

      Valeur | Retour
      --- | ---
      SW_PGSQL_ASSOC | Retourne un tableau associatif avec les noms des champs comme clés.
      SW_PGSQL_NUM | Retourne un tableau avec les numéros de champs comme clés.
      SW_PGSQL_BOTH | Retourne un tableau avec les noms des champs et les numéros de champs comme clés.

  * **Retour**

    * Retourne tous les enregistrements de la résultat comme un tableau.


### affectedRows()

Retourne le nombre d'enregistrements affectés. 

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```


### numRows()

Retourne le nombre de lignes.

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```


### fetchObject()

Extraire une ligne comme objet. 

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **Exemple**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    for ($row = 0; $row < $stmt->numRows(); $row++) {
        $data = $stmt->fetchObject($row);
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
    $stmt = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    while ($data = $stmt->fetchObject($row)) {
        echo $data->id . " \n ";
        $row++;
    }
});
```


### fetchAssoc()

Extraire une ligne comme un tableau associatif.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```


### fetchArray()

Extraire une ligne comme un tableau.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **Paramètres**
    * **`int $row`**
      * **Fonction** : `$row` est le numéro de la ligne (enregistrement) que l'on souhaite obtenir. La première ligne est `0`.
      * **Valeur par défaut** : None
      * **Autres valeurs** : None
    * **`$result_type`**
      * **Fonction** : Constant. Paramètre optionnel qui contrôle la manière dont les valeurs de retour sont initialisées.
      * **Valeur par défaut** : `SW_PGSQL_BOTH`
      * **Autres valeurs** : None

      Valeur | Retour
      --- | ---
      SW_PGSQL_ASSOC | Retourne un tableau avec les noms des champs comme clés.
      SW_PGSQL_NUM | Retourne un tableau avec les numéros de champs comme clés.
      SW_PGSQL_BOTH | Retourne un tableau avec les noms des champs et les numéros de champs comme clés.

  * **Retour**

    * Retourne un tableau cohérent avec la ligne (tuple/enregistrement) extraite. Si il n'y a plus de lignes à extraire, retourne `false`.

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchArray(1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```
### fetchRow()

Récupère une ligne de données (enregistrement) à partir du résultat spécifié en tant qu'array et la renvoie. Chaque colonne obtenue est stockée dans l'array à partir de l'offset `0`.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **Paramètres**
    * **`int $row`**
      * **Fonction** : `row` est le numéro de la ligne (enregistrement) que l'on souhaite obtenir. La première ligne est `0`.
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun
    * **`$result_type`**
      * **Fonction** : Constante. Paramètre optionnel qui contrôle la manière dont la valeur de retour est initialisée.
      * **Valeur par défaut** : `SW_PGSQL_NUM`
      * **Autres valeurs** : Aucun

      Valeur | Retour
      --- | ---
      SW_PGSQL_ASSOC | Retourne un array associatif avec les noms des champs comme clés.
      SW_PGSQL_NUM | Retourne un array avec les numéros de fields comme clés.
      SW_PGSQL_BOTH | Retourne un array avec les deux comme clés.

  * **Retour**

    * L'array de retour correspond à la ligne extraite. Si il n'y a plus de lignes à extraire pour `row`, alors `false` est retourné.

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    while ($row = $stmt->fetchRow()) {
        echo "name: $row[0]  mobile: $row[1]" . PHP_EOL;
    }
});
```
