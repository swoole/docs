# Coroutine\MySQL

Coroutine MySQL client.

!> This client is no longer recommended. It is recommended to use `Swoole\Runtime::enableCoroutine` with PDO or Mysqli to enable native PHP MySQL client for coroutine support.

!> Do not use the async callback style from the `Swoole 1.x` era and this Coroutine MySQL client at the same time.
## Usage Example

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    var_dump($res);
});
```
## defer feature

Please refer to the section on [Concurrent Client](/coroutine/multi_call).
## Stored Procedures

Since version `4.0.0`, support for `MySQL` stored procedures and fetching multiple result sets has been added.
## MySQL8.0

`Swoole-4.0.1` or higher version supports all the security verification capabilities of `MySQL8`, so you can directly use the client without having to fall back to password settings.
### Versions below 4.0.1

`MySQL-8.0` defaults to using the more secure `caching_sha2_password` plugin. If you are upgrading from `5.x`, you can directly use all `MySQL` features. For newly created `MySQL` instances, you need to run the following commands in the `MySQL` command line to make it compatible:

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

Replace `'root'@'localhost'` in the statement with the user you are using and replace `password` with the user's password.

If you still cannot connect, you should set `default_authentication_plugin = mysql_native_password` in my.cnf.
## Properties
### serverInfo

Connection information, saved as an array passed to the connection function.
### sock

File descriptor used for connection.
### connected

Whether the `MySQL` server is connected.

!> Refer to [Inconsistency between the connected property and the connection status](/question/use?id=connected属性和连接状态不一致)
### connect_error

Error message when executing `connect` to the server.
### connect_errno

The error code when executing the `connect` function to connect to the server, with the data type being an integer.
### error

Error message returned by the server when executing a `MySQL` command.
### errno

The error code returned by the server when executing MySQL commands, with the data type being an integer.
### affected_rows

Number of rows affected.
### insert_id

The id of the last record inserted.
## Methods
### connect()

Establish MySQL connection.

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

!> `$serverInfo`: Parameters are passed in an array format.

```php
[
    'host'        => 'MySQL IP address', // If it is a local UNIX socket, it should be filled in the format like 'unix://tmp/your_file.sock'
    'user'        => 'data user',
    'password'    => 'database password',
    'database'    => 'database name',
    'port'        => 'MySQL port default 3306 optional parameter',
    'timeout'     => 'establish connection timeout', // Only affects the connection timeout, not query and execute methods, refer to `client timeout rules`
    'charset'     => 'character set',
    'strict_type' => false, // Enable strict mode, data returned by the query method will also be converted to strong type
    'fetch_mode'  => true,  // Enable fetch mode, similar to pdo, use fetch/fetchAll line by line or get all result set (4.0 version and above)
]
```
### query()

Execute SQL statements.

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **Parameters** 

    * **`string $sql`**
      * **Function**：SQL statement
      * **Default**：N/A
      * **Other values**：N/A

    * **`float $timeout`**
      * **Function**：Timeout duration 【If the `MySQL` server fails to return data within the specified time, it will return `false`, set error code to `110`, and disconnect the connection】
      * **Unit**：Seconds, with minimum precision of milliseconds (0.001 seconds)
      * **Default**：`0`
      * **Other values**：N/A
      * **Refer to [client timeout rules](/coroutine_client/init?id=timeout-rules)**


  * **Return Value**

    * Returns `false` in case of timeout/error, else returns query result in the form of an `array`

  * **Delayed Receive**

  !> After setting `defer`, calling `query` will return `true` directly. Calling `recv` will then enter coroutine waiting and return the query results.

  * **Example**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('show tables');
    if ($res === false) {
        return;
    }
    var_dump($res);
});
```
### prepare()

Send a SQL prepared request to the MySQL server.

!> `prepare` must be used in conjunction with `execute`. After the preparation request is successful, call the `execute` method to send data parameters to the `MySQL` server.

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **Parameters** 

    * **`string $sql`**
      * **Function** : Prepared statement [using `?` as a parameter placeholder]
      * **Default** : None
      * **Other values** : None

    * **`float $timeout`**
      * **Function** : Timeout time
      * **Value unit** : Seconds, with a minimum precision of milliseconds (`0.001` seconds)
      * **Default** : `0`
      * **Other values** : None
      * **Reference [client timeout rules](/coroutine_client/init?id=timeout-rules)**


  * **Return Value**

    * Returns `false` on failure, you can check `$db->error` and `$db->errno` to determine the cause of the error
    * Returns a `Coroutine\MySQL\Statement` object on success, you can call the [execute](/coroutine_client/mysql?id=statement-gtexecute) method of the object to send parameters

  * **Example**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10));
        var_dump($ret2);
    }
});
```
### escape()

Escape special characters in SQL statements to prevent SQL injection attacks. Implemented based on the functions provided by `mysqlnd` and requires the `mysqlnd` extension in `PHP`.

!> [--enable-mysqlnd](/environment?id=compilation-options) needs to be added during compilation to enable it.

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

* **Parameters** 

  * **`string $str`**
    * **Function**: Escapes characters
    * **Default value**: None
    * **Other values**: None

* **Usage example**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $data = $db->escape("abc'efg\r\n");
});
```
### begin()

Starts a transaction. Combines with `commit` and `rollback` to achieve transaction processing in `MySQL`.

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> Starts a `MySQL` transaction. Returns `true` on success and `false` on failure. Check `$db->errno` for error code.

!> With the same `MySQL` connection object, only one transaction can be started at a time;  
you must wait until the previous transaction is `commit`ed or `rollback`ed before starting a new transaction;  
otherwise, the underlying system will throw a `Swoole\MySQL\Exception` exception with an error `code` of `21`.

  * **Example**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```
### commit()

Submit the transaction.

!> Must be used in conjunction with `begin`.

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> Returns `true` on success and `false` on failure. Please check `$db->errno` to retrieve the error code.
### rollback()

Rolls back the transaction.

!> Must be used in conjunction with `begin`.

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> Returns `true` on success, `false` on failure. Please check `$db->errno` for the error code.
### Statement->execute()

Send SQL prepared data parameters to the MySQL server.

!> `execute` must be used in combination with `prepare`, and `prepare` must be called before `execute` to initiate a prepared request.

!> The `execute` method can be called multiple times.

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **Parameters** 

    * **`array $params`**
      * **Function** : Prepared data parameters 【Must be the same number of parameters as the `prepare` statement. `$params` must be an array with numeric indexes, in the same order as the `prepare` statement】
      * **Default Value** : None
      * **Other values** : None

    * **`float $timeout`**
      * **Function** : Timeout time 【If the `MySQL` server does not return data within the specified time, it will return `false`, set the error code to `110`, and disconnect the connection】
      * **Value Unit** : Seconds, with a minimum precision of milliseconds (0.001 seconds)
      * **Default Value** : `-1`
      * **Other values** : None
      * **Refer to [Client Timeout Rules](/coroutine_client/init?id=timeout-rules)**

  * **Return Value** 

    * Returns `true` on success, if the `fetch_mode` parameter of `connect` is set to `true`
    * Returns an array of data sets on success, in other cases
    * Returns `false` on failure, and you can check `$db->error` and `$db->errno` to determine the cause of the error

  * **Usage Example** 

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=? and name=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10, 'rango'));
        var_dump($ret2);

        $ret3 = $stmt->execute(array(13, 'alvin'));
        var_dump($ret3);
    }
});
```
### Statement->fetch()

Retrieve the next row from the result set.

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> Swoole version >= `4.0-rc1`, need to include the option `fetch_mode => true` when connecting

  * **Example** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> Starting from the new `MySQL` driver in version `v4.4.0`, `fetch` must be used in the manner shown in the example code until it reads `NULL`, otherwise new requests cannot be initiated (due to the on-demand reading mechanism which helps save memory)
### Statement->fetchAll()

Returns an array containing all rows in the result set.

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> Swoole version >= `4.0-rc1`, the `fetch_mode => true` option needs to be added during `connect`.

  * **Example**

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```
### Statement->nextResult()

Advance to the next response result within a multi-response statement handle (e.g., multiple result returns from a stored procedure).

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

* **Return Value**

    * Returns `TRUE` on success
    * Returns `FALSE` on failure
    * Returns `NULL` if there is no next result

* **Example**

    * **Non-fetch mode**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **Fetch mode**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> Starting from the new `MySQL` driver in `v4.4.0`, the `fetch` must be read to `NULL` using the method shown in the sample code to be able to initiate new requests; otherwise, new requests cannot be made (due to the on-demand read mechanism, memory can be saved).
