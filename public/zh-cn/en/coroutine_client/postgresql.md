# Coroutine\PostgreSQL

Coroutine `PostgreSQL` client.

Completely refactored in Swoole 5.0 version, with usage completely different from the old version. If you are using the old version, please refer to the [old documentation](/coroutine_client/postgresql-old.md).
## Compilation and Installation

* Make sure the `libpq` library is installed on the system
* After installing `postgresql` on `mac`, the `libpq` library is included. There may be differences between environments - on `ubuntu`, you might need `apt-get install libpq-dev`, while on `centos`, you might need `yum install postgresql10-devel`
* When compiling Swoole, add the following compilation option: `./configure --enable-swoole-pgsql`
## Example of Use

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
### Transaction processing

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
## Properties
### error

Get error message.
## Methods
### connect()

Establish a non-blocking coroutine connection to `postgresql`.

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo` is the connection information. Returns true on successful connection, false on failure. You can use the [error](/coroutine_client/postgresql?id=error) property to obtain error information.
  * **Example**

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

Execute SQL statements. Send asynchronous non-blocking coroutine commands.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
```

  * **Parameters** 

    * **`string $sql`**
      * **Description**: SQL statement
      * **Default value**: None
      * **Other values**: None

  * **Examples**

    * **Select**

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

    * **Return insert ID**

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

    * **Transaction**

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

View the metadata of a table. Asynchronous non-blocking coroutine version.

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```
    
  * **Usage Example**

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

Preprocess.

```php
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **Example**

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

Class name: `Swoole\Coroutine\PostgreSQLStatement`

All queries will return a `PostgreSQLStatement` object
### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **Parameters**
    * **`$result_type`**
      * **Description**: Constant. Optional parameter that controls how the return value is initialized.
      * **Default**: `SW_PGSQL_ASSOC`
      * **Other values**: None

      Value | Return Value
      ---|---
      SW_PGSQL_ASSOC | Returns an associative array with field names as keys
      SW_PGSQL_NUM | Returns an array with field numbers as keys
      SW_PGSQL_BOTH | Returns an array with both field names and numbers as keys

  * **Return Value**

    * Fetch all rows from the result as an array.
### affectedRows()

Returns the number of affected records.

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```
### numRows()

Returns the number of rows.

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```
### fetchObject()

Extracts a row as an object.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **Example**

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

Fetches a row as an associative array.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```
### fetchArray()

Fetches a row as an array.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **Parameters**
    * **`int $row`**
      * **Description**: The row number to retrieve. The first row is `0`.
      * **Default**: None
      * **Other values**: None
    * **`$result_type`**
      * **Description**: Constants that control how to initialize the returned value.
      * **Default**: `SW_PGSQL_BOTH`
      * **Other values**: None

      Value | Return
      ---|---
      SW_PGSQL_ASSOC | Returns an associative array with field names as keys
      SW_PGSQL_NUM | Returns an array with field numbers as keys
      SW_PGSQL_BOTH | Returns an array with both field names and numbers as keys

  * **Return Value**

    * Returns an array consistent with the retrieved row (tuple/record). If no more rows are available to fetch, it returns `false`.

  * **Usage Example**

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

Retrieve a row of data (record) based on the specified `result` resource and return it as an array. Each column obtained is sequentially stored in the array, starting from offset `0`.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **Parameters**
    * **`int $row`**
      * **Description**: `row` is the number of the row (record) to be fetched. The first row is `0`.
      * **Default**: N/A
      * **Other values**: N/A
    * **`$result_type`**
      * **Description**: Constant. Optional parameter that controls how the return value is initialized.
      * **Default**: `SW_PGSQL_NUM`
      * **Other values**: N/A

      Value | Return
      ---|---
      SW_PGSQL_ASSOC | Returns an associative array indexed using field names
      SW_PGSQL_NUM | Returns an array indexed using field numbers
      SW_PGSQL_BOTH | Returns an array indexed using both field names and numbers

  * **Return Value**

    * The returned array corresponds to the extracted row. If there are no more rows (`row`) to fetch, it returns `false`.

  * **Usage Example**

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
