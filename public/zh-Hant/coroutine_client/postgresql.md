# 協程\PostgreSQL

協程`PostgreSQL`客戶端。

!> 在 Swoole 5.0 版本全新重構，與舊版本用法完全不同。如果你正在使用舊版本，請查看[舊版文檔](/coroutine_client/postgresql-old.md)。

!> 在 Swoole 6.0 之後，協程`PostgreSQL`客戶端已被移除，請使用[協程化pdo_pgsql](/runtime?id=swoole_hook_pdo_pgsql)代替


## 編譯安裝

* 需要確保系統中已安裝`libpq`庫
* `mac`安裝完`postgresql`自帶`libpq`庫，環境之間有差異，`ubuntu`可能需要`apt-get install libpq-dev`，`centos`可能需要`yum install postgresql10-devel`
* 編譯 Swoole 時添加編譯選項：`./configure --enable-swoole-pgsql`


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


### 事務處理

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


## 屬性


### error

獲取錯誤信息。


## 方法


### connect()

建立`postgresql`非阻塞的協程連接。

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo` 為連接信息，連接成功返回true，連接失敗返回false，可以使用[error](/coroutine_client/postgresql?id=error)屬性獲取錯誤信息。
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

執行SQL語句。發送異步非阻塞協程命令。

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
```

  * **參數** 

    * **`string $sql`**
      * **功能**：SQL語句
      * **默認值**：無
      * **其它值**：無

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

查看表的元數據。異步非阻塞協程版。

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

預處理。

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

類名：`Swoole\Coroutine\PostgreSQLStatement`

所有查詢都會返回 `PostgreSQLStatement` 對象


### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **參數**
    * **`$result_type`**
      * **功能**：常量。可選參數，控制著怎樣初始化返回值。
      * **默認值**：`SW_PGSQL_ASSOC`
      * **其它值**：無

      取值 | 返回值
      ---|---
      SW_PGSQL_ASSOC | 返回用字段名作為鍵值索引的關聯數組
      SW_PGSQL_NUM | 返回用字段編號作為鍵值
      SW_PGSQL_BOTH | 返回同時用兩者作為鍵值

  * **返回值**

    * 提取結果中所有行作為一個數組返回。


### affectedRows()

返回受影響的記錄數目。 

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```


### numRows()

返回行的數目。

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```


### fetchObject()

提取一行作為對象。 

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

提取一行作為關聯數組。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```


### fetchArray()

提取一行作為數組。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **參數**
    * **`int $row`**
      * **功能**：`row` 是想要取得的行（記錄）的編號。第一行為 `0`。
      * **默認值**：無
      * **其它值**：無
    * **`$result_type`**
      * **功能**：常量。可選參數，控制著怎樣初始化返回值。
      * **默認值**：`SW_PGSQL_BOTH`
      * **其它值**：無

      取值 | 返回值
      ---|---
      SW_PGSQL_ASSOC | 返回用字段名作為鍵值索引的關聯數組
      SW_PGSQL_NUM | 返回用字段編號作為鍵值
      SW_PGSQL_BOTH | 返回同時用兩者作為鍵值

  * **返回值**

    * 返回一個與所提取的行（元組/記錄）相一致的數組。如果沒有更多行可供提取，則返回 `false`。

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

根據指定的 `result` 資源提取一行數據（記錄）作為數組返回。每個得到的列依次存放在數組中，從偏移量 `0` 開始。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **參數**
    * **`int $row`**
      * **功能**：`row` 是想要取得的行（記錄）的編號。第一行為 `0`。
      * **默認值**：無
      * **其它值**：無
    * **`$result_type`**
      * **功能**：常量。可選參數，控制著怎樣初始化返回值。
      * **默認值**：`SW_PGSQL_NUM`
      * **其它值**：無

      取值 | 返回值
      ---|---
      SW_PGSQL_ASSOC | 返回用字段名作為鍵值索引的關聯數組
      SW_PGSQL_NUM | 返回用字段編號作為鍵值
      SW_PGSQL_BOTH | 返回同時用兩者作為鍵值

  * **返回值**

    * 返回的數組和提取的行相一致。如果沒有更多行 `row` 可提取，則返回 `false`。

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
