# Coroutine\PostgreSQL 旧版

協程`PostgreSQL`クライアント。この機能を有効にするには、[ext-postgresql](https://github.com/swoole/ext-postgresql)拡張をコンパイルする必要があります。

> このドキュメントは Swoole < 5.0 にのみ適用されます。

## コンパイルとインストール

ソースコードをダウンロードしてください：[https://github.com/swoole/ext-postgresql](https://github.com/swoole/ext-postgresql)。Swoole のバージョンに対応する releases バージョンをインストールする必要があります。

* システムに`libpq`ライブラリがインストールされていることを確認する必要があります。
* `mac`では`postgresql`をインストールすると`libpq`ライブラリが自動的にインストールされますが、環境によって異なります。`ubuntu`では`apt-get install libpq-dev`が必要かもしれませんし、`centos`では`yum install postgresql10-devel`が必要かもしれません。
* `libpq`ライブラリのディレクトリを個別に指定することもできます。例えば：`./configure --with-libpq-dir=/etc/postgresql`

## 使用例

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

### トランザクション処理

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

## プロパティ

### error

エラー情報を取得します。

## メソッド

### connect()

非ブロッキングな協程接続を`postgresql`に確立します。

```php
Swoole\Coroutine\PostgreSQL->connect(string $connection_string): bool
```

!> `$connection_string`は接続情報で、接続に成功するとtrue、失敗するとfalseを返します。エラー情報を取得するには[error](/coroutine_client/postgresql?id=error)プロパティを使用できます。
  * **例**

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

SQL文を実行します。非ブロッキングな協程コマンドを送信します。

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): resource;
```

  * **パラメータ**

    * **`string $sql`**
      * **機能**：SQL文
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **例**

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

    * **戻り値 insert id**

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

    * **トランザクション**

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

  * **パラメータ**
    * **`$resultType`**
      * **機能**：定数。オプションのパラメータで、返却値の初期化方法を制御します。
      * **デフォルト値**：`SW_PGSQL_ASSOC`
      * **その他の値**：なし

      値 | 返却値
      ---|---
      SW_PGSQL_ASSOC | フィールド名をキーとする連想配列を返す
      SW_PGSQL_NUM | フィールド番号をキーとする配列を返す
      SW_PGSQL_BOTH | 両方をキーとする配列を返す

  * **返却値**

    * 結果の全ての行を配列として返却します。

### affectedRows()

影響を受けたレコードの数を返します。

```php
Swoole\Coroutine\PostgreSQL->affectedRows(resource $queryResult): int
```

### numRows()

行の数を返します。

```php
Swoole\Coroutine\PostgreSQL->numRows(resource $queryResult): int
```

### fetchObject()

一行をオブジェクトとして取り出します。

```php
Swoole\Coroutine\PostgreSQL->fetchObject(resource $queryResult, int $row): object;
```

  * **例**

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

一行を連想配列として取り出します。

```php
Swoole\Coroutine\PostgreSQL->fetchAssoc(resource $queryResult, int $row): array
```

### fetchArray()

一行を配列として取り出します。

```php
Swoole\Coroutine\PostgreSQL->fetchArray(resource $queryResult, int $row, $resultType = SW_PGSQL_BOTH): array|false
```

  * **パラメータ**
    * **`int $row`**
      * **機能**：取得したい行（レコード）の番号です。最初の行は `0`です。
      * **デフォルト値**：なし
      * **その他の値**：なし
    * **`$resultType`**
      * **機能**：定数。オプションのパラメータで、返却値の初期化方法を制御します。
      * **デフォルト値**：`SW_PGSQL_BOTH`
      * **その他の値**：なし

      値 | 返却値
      ---|---
      SW_PGSQL_ASSOC | フィールド名をキーとする連想配列を返す
      SW_PGSQL_NUM | フィールド番号をキーとする配列を返す
      SW_PGSQL_BOTH | 両方をキーとする配列を返す

  * **返却値**

    * 取得した行（タプル/レコード）に一致する配列を返します。これ以上の行を取り出すことができない場合は `false`を返します。

  * **使用例**

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

指定された `result`リソースに基づいて一行のデータ（レコード）を配列として取り出します。各取得したカラムは順に配列に配置され、オフセット `0`から始まります。

```php
Swoole\Coroutine\PostgreSQL->fetchRow(resource $queryResult, int $row, $resultType = SW_PGSQL_NUM): array|false
```

  * **パラメータ**
    * **`int $row`**
      * **機能**：取得したい行（レコード）の番号です。最初の行は `0`です。
      * **デフォルト値**：なし
      * **その他の値**：なし
    * **`$resultType`**
      * **機能**：定数。オプションのパラメータで、返却値の初期化方法を制御します。
      * **デフォルト値**：`SW_PGSQL_NUM`
      * **その他の値**：なし

      值 | 返却値
      ---|---
      SW_PGSQL_ASSOC | フィールド名をキーとする連想配列を返す
      SW_PGSQL_NUM | フィールド番号をキーとする配列を返す
      SW_PGSQL_BOTH | 両方をキーとする配列を返す

  * **返却値**

    * 返却された配列は取り出した行と一致します。これ以上の行 `row`を取り出すことができない場合は `false`を返します。

  * **使用例**

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

テーブルのメタデータを見ます。非同期非ブロッキング協程版です。

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```
    
  * **使用例**

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

プリペア。

```php
Swoole\Coroutine\PostgreSQL->prepare(string $name, string $sql);
Swoole\Coroutine\PostgreSQL->execute(string $name, array $bind);
```

  * **使用例**

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
