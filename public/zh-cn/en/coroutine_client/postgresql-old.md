# Coroutine\PostgreSQL Old Version

Coroutine `PostgreSQL` client. You need to compile the [ext-postgresql](https://github.com/swoole/ext-postgresql) extension to enable this feature.

> This documentation is only applicable to Swoole < 5.0
## Compilation and Installation

Download the source code from: [https://github.com/swoole/ext-postgresql](https://github.com/swoole/ext-postgresql), make sure to install the releases version corresponding to the Swoole version.

* Ensure that the `libpq` library is installed on your system.
* On `mac`, `postgresql` comes with `libpq` library after installation, there may be differences in environments. On `ubuntu`, you may need to run `apt-get install libpq-dev`, and on `centos`, you might need to run `yum install postgresql10-devel`.
* You can also specify the directory of the `libpq` library separately, for example: `./configure --with-libpq-dir=/etc/postgresql`.
## Usage Example

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
### Transaction Processing

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
## Properties
### error

Retrieve error information.
## Methods
### connect()

Establish a non-blocking coroutine connection to `postgresql`.

```php
Swoole\Coroutine\PostgreSQL->connect(string $connection_string): bool
```

!> `$connection_string` is the connection information. Returns true if the connection is successful, false if the connection fails. You can use the [error](/coroutine_client/postgresql?id=error) property to get error information.
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

Execute SQL statement. Send async non-blocking coroutine command.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): resource;
```

  * **Parameters** 

    * **`string $sql`**
      * **Description**: SQL statement
      * **Default**: None
      * **Other values**: None

  * **Examples**

    * **Select**

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

    * **Return Insert ID**

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

    * **Transaction**

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

  * **Parameters**
    * **`$resultType`**
      * **Description**: Constant. Optional parameter that controls how the return value is initialized.
      * **Default value**: `SW_PGSQL_ASSOC`
      * **Other values**: None

      Value | Return
      ---|---
      SW_PGSQL_ASSOC | Returns an associative array using the field name as the key index
      SW_PGSQL_NUM | Returns an array using the field number as the key index
      SW_PGSQL_BOTH | Returns an array using both the field name and number as the key index

  * **Return Value**

    * Retrieve all rows from the result as an array.
### affectedRows()

Returns the number of affected records.

```php
Swoole\Coroutine\PostgreSQL->affectedRows(resource $queryResult): int
```
### numRows()

Returns the number of rows.

```php
Swoole\Coroutine\PostgreSQL->numRows(resource $queryResult): int
```
### fetchObject()

Extracts a row as an object.

```php
Swoole\Coroutine\PostgreSQL->fetchObject(resource $queryResult, int $row): object;
```

  * **Example**

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

Fetch a row as an associative array.

```php
Swoole\Coroutine\PostgreSQL->fetchAssoc(resource $queryResult, int $row): array
```
### fetchArray()

Fetches a row as an array.

```php
Swoole\Coroutine\PostgreSQL->fetchArray(resource $queryResult, int $row, $resultType = SW_PGSQL_BOTH): array|false
```

  * **Parameters**
    * **`int $row`**
      * **Description**: `row` is the number of the row (record) to retrieve. The first row is `0`.
      * **Default value**: None
      * **Other values**: None
    * **`$resultType`**
      * **Description**: Constant. Optional parameter that controls how the return value is initialized.
      * **Default value**: `SW_PGSQL_BOTH`
      * **Other values**: None

      Value | Return
      ---|---
      SW_PGSQL_ASSOC | Returns an associative array with field names as keys
      SW_PGSQL_NUM | Returns an array with field numbers as keys
      SW_PGSQL_BOTH | Returns an array with both field names and numbers as keys

  * **Return Value**

    Returns an array consistent with the retrieved row (tuple/record). Returns `false` if there are no more rows to fetch.

  * **Example**

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

Extracts a row of data(record) from the specified `result` resource and returns it as an array. Each obtained column is stored in the array starting from offset `0`.

```php
Swoole\Coroutine\PostgreSQL->fetchRow(resource $queryResult, int $row, $resultType = SW_PGSQL_NUM): array|false
```

  * **Parameters**
    * **`int $row`**
      * **Function**: `row` is the index of the row(record) to be retrieved. The first row is `0`.
      * **Default Value**: None
      * **Other Values**: None
    * **`$resultType`**
      * **Function**: Constant. Optional parameter that controls how the return value is initialized.
      * **Default Value**: `SW_PGSQL_NUM`
      * **Other Values**: None

      Value | Return
      ---|---
      SW_PGSQL_ASSOC | Returns an associative array using field names as keys
      SW_PGSQL_NUM | Returns an array using field numbers as keys
      SW_PGSQL_BOTH | Returns an array using both as keys

  * **Return Value**

    * The returned array corresponds to the extracted row. If there are no more rows `row` that can be retrieved, it returns `false`.

  * **Example**

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

View table metadata. Asynchronous non-blocking coroutine version.

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

Preprocessing.

```php
Swoole\Coroutine\PostgreSQL->prepare(string $name, string $sql);
Swoole\Coroutine\PostgreSQL->execute(string $name, array $bind);
```

* **Usage Example**

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
