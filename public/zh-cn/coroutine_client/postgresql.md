# Coroutine\PostgreSQL

协程`PostgreSQL`客户端。

在 Swoole 5.0 版本全新重构，与旧版本用法完全不同。如果你正在使用旧版本，请查看[旧版文档](/coroutine_client/postgresql-old.md)。

## 编译安装

* 需要确保系统中已安装`libpq`库
* `mac`安装完`postgresql`自带`libpq`库，环境之间有差异，`ubuntu`可能需要`apt-get install libpq-dev`，`centos`可能需要`yum install postgresql10-devel`
* 编译 Swoole 时添加编译选项：`./configure --enable-swoole-pgsql`

## 使用示例

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

### 事务处理

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

## 属性

### error

获取错误信息。

## 方法

### connect()

建立`postgresql`非阻塞的协程连接。

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo` 为连接信息，连接成功返回true，连接失败返回false，可以使用[error](/coroutine_client/postgresql?id=error)属性获取错误信息。
  * **示例**

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

执行SQL语句。发送异步非阻塞协程命令。

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
```

  * **参数** 

    * **`string $sql`**
      * **功能**：SQL语句
      * **默认值**：无
      * **其它值**：无

  * **示例**

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

    * **返回insert id**

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

查看表的元数据。异步非阻塞协程版。

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```
    
  * **使用示例**

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

预处理。

```php
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **使用示例**

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

类名：`Swoole\Coroutine\PostgreSQLStatement`

所有查询都会返回 `PostgreSQLStatement` 对象

### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **参数**
    * **`$result_type`**
      * **功能**：常量。可选参数，控制着怎样初始化返回值。
      * **默认值**：`SW_PGSQL_ASSOC`
      * **其它值**：无

      取值 | 返回值
      ---|---
      SW_PGSQL_ASSOC | 返回用字段名作为键值索引的关联数组
      SW_PGSQL_NUM | 返回用字段编号作为键值
      SW_PGSQL_BOTH | 返回同时用两者作为键值

  * **返回值**

    * 提取结果中所有行作为一个数组返回。

### affectedRows()

返回受影响的记录数目。 

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```

### numRows()

返回行的数目。

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```

### fetchObject()

提取一行作为对象。 

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **示例**

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

提取一行作为关联数组。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```

### fetchArray()

提取一行作为数组。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **参数**
    * **`int $row`**
      * **功能**：`row` 是想要取得的行（记录）的编号。第一行为 `0`。
      * **默认值**：无
      * **其它值**：无
    * **`$result_type`**
      * **功能**：常量。可选参数，控制着怎样初始化返回值。
      * **默认值**：`SW_PGSQL_BOTH`
      * **其它值**：无

      取值 | 返回值
      ---|---
      SW_PGSQL_ASSOC | 返回用字段名作为键值索引的关联数组
      SW_PGSQL_NUM | 返回用字段编号作为键值
      SW_PGSQL_BOTH | 返回同时用两者作为键值

  * **返回值**

    * 返回一个与所提取的行（元组/记录）相一致的数组。如果没有更多行可供提取，则返回 `false`。

  * **使用示例**

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

根据指定的 `result` 资源提取一行数据（记录）作为数组返回。每个得到的列依次存放在数组中，从偏移量 `0` 开始。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **参数**
    * **`int $row`**
      * **功能**：`row` 是想要取得的行（记录）的编号。第一行为 `0`。
      * **默认值**：无
      * **其它值**：无
    * **`$result_type`**
      * **功能**：常量。可选参数，控制着怎样初始化返回值。
      * **默认值**：`SW_PGSQL_NUM`
      * **其它值**：无

      取值 | 返回值
      ---|---
      SW_PGSQL_ASSOC | 返回用字段名作为键值索引的关联数组
      SW_PGSQL_NUM | 返回用字段编号作为键值
      SW_PGSQL_BOTH | 返回同时用两者作为键值

  * **返回值**

    * 返回的数组和提取的行相一致。如果没有更多行 `row` 可提取，则返回 `false`。

  * **使用示例**

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
