# Coroutine\PostgreSQL

Korreutinen `PostgreSQL`-Client.

!> Bei der Überarbeitung in Swoole 5.0 wurde die Syntax komplett geändert. Wenn Sie die alte Version verwenden, sehen Sie sich bitte die [alten Dokumente](/coroutine_client/postgresql-old.md) an.

!> Ab Swoole 6.0 wurde der Koroutine `PostgreSQL`-Client entfernt. Bitte verwenden Sie stattdessen den [koroutineisierten pdo_pgsql](/runtime?id=swoole_hook_pdo_pgsql).


## Kompilierung und Installation

* Es ist sicherzustellen, dass die `libpq`-Bibliothek auf dem System installiert ist.
* Bei `macOS` wird die `libpq`-Bibliothek mit PostgreSQL geliefert. Es gibt Unterschiede zwischen den Umgebungen. Bei `ubuntu` könnte man `apt-get install libpq-dev` benötigen, bei `centos` möglicherweise `yum install postgresql10-devel`.
* Beim Kompilieren von Swoole fügen Sie die编译选项 hinzu: `./configure --enable-swoole-pgsql`


## Beispielverwendung

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


### Transaktionshandling

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


## Eigenschaften


### error

Fehlerinformationen abrufen.


## Methoden


### connect()

Eine asynchrone, nicht blockierende Koroutine-Verbindung zum `postgresql` herstellen.

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo` ist die Verbindungsinformation. Bei erfolgreicher Verbindung wird `true` zurückgegeben, bei Fehlgeschlagenen Verbindung `false`. Fehlerinformationen können mit der [error](/coroutine_client/postgresql?id=error)-Eigenschaft abgerufen werden.
  * **Beispiel**

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

Eine SQL-Anweisung ausführen. Asynchron und nicht blockierend.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
```

  * **Parameter** 

    * **`string $sql`**
      * **Funktion**：Die SQL-Anweisung.
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

  * **Beispiel**

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

    * **Return insert id**

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

Metadaten einer Tabelle betrachten. Asynchron und nicht blockierend.

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```    
  * **Beispiel**

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

Vorbereiten.

```php
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **Beispiel**

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

Klasse: `Swoole\Coroutine\PostgreSQLStatement`

Alle Abfrageergebnisse werden als `PostgreSQLStatement`-Objekte zurückgegeben


### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **Parameter**
    * **$result_type**
      * **Funktion**：Konstante. Optionaler Parameter, der bestimmt, wie die Rückkehrwerte initialisiert werden.
      * **Standardwert**：`SW_PGSQL_ASSOC`
      * **Andere Werte**：Kein

      Wert | Rückkehrwert
      --- | ---
      SW_PGSQL_ASSOC | Gibt ein assoziiertes Array zurück, dessen Indizes die Feldnamen sind.
      SW_PGSQL_NUM | Gibt ein Array zurück, dessen Indizes die Feldnummern sind.
      SW_PGSQL_BOTH | Gibt ein Array zurück, dessen Indizes sowohl die Feldnamen als auch die Feldnummern sind.

  * **Rückkehrwert**

    * Gibt alle Ergebnisse als ein Array zurück.


### affectedRows()

Die Anzahl der betroffenen Datensätze zurückgeben. 

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```


### numRows()

Die Anzahl der Zeilen zurückgeben.

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```


### fetchObject()

Eine Zeile als Objekt extrahieren. 

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **Beispiel**

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

Eine Zeile als assoziiertes Array extrahieren.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```


### fetchArray()

Eine Zeile als Array extrahieren.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **Parameter**
    * **`int $row`**
      * **Funktion**：`row` ist die Nummer der Zeile (des Datensatzes), die extrahiert werden soll. Die erste Zeile hat die Nummer `0`.
      * **Standardwert**：Kein
      * **Andere Werte**：Kein
    * **$result_type`**
      * **Funktion**：Konstante. Optionaler Parameter, der bestimmt, wie die Rückkehrwerte initialisiert werden.
      * **Standardwert**：`SW_PGSQL_BOTH`
      * **Andere Werte**：Kein

      Wert | Rückkehrwert
      --- | ---
      SW_PGSQL_ASSOC | Gibt ein assoziiertes Array zurück, dessen Indizes die Feldnamen sind.
      SW_PGSQL_NUM | Gibt ein Array zurück, dessen Indizes die Feldnummern sind.
      SW_PGSQL_BOTH | Gibt ein Array zurück, dessen Indizes sowohl die Feldnamen als auch die Feldnummern sind.

  * **Rückkehrwert**

    * Gibt ein Array zurück, das dem extrahierten Datensatz (Tuple/Record) entspricht. Wenn keine weiteren Zeilen mehr vorhanden sind, wird `false` zurückgegeben.

  * **Beispiel**

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

Extrahiert eine Zeile Daten (ein Record) aus dem angegebenen `result` Resource und gibt sie als Array zurück. Jede erhaltene Spalte wird nacheinander im Array abgelegt, beginnend mit der Offset-Position `0`.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **Parameter**
    * **`int $row`**
      * **Funktion**: `row` ist die Nummer der Zeile (des Records), die extrahiert werden soll. Die erste Zeile ist `0`.
      * **Standardwert**: Keine
      * **Andere Werte**: Keine
    * **`$result_type`**
      * **Funktion**: Konstante. Optionaler Parameter, der steuert, wie der Rückgabewert initialisiert wird.
      * **Standardwert**: `SW_PGSQL_NUM`
      * **Andere Werte**: Keine

      Wert | Rückgabewert
      --- | ---
      SW_PGSQL_ASSOC | Gibt ein assoziiertes Array zurück, wobei die Felderamen als Schlüssel verwendet werden.
      SW_PGSQL_NUM | Gibt ein Array zurück, wobei die Feldernummern als Schlüssel verwendet werden.
      SW_PGSQL_BOTH | Gibt ein Array zurück, das sowohl Feldernamen als auch Feldernummern als Schlüssel verwendet.

  * **Rückgabewert**

    * Das zurückgegebene Array ist konsistent mit der extrahierten Zeile. Wenn keine weiteren Zeilen für `row` extrahierbar sind, wird `false` zurückgegeben.

  * **Beispiel für die Verwendung**

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
