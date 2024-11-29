# コルネリス PostgreSQL

コルネリス`PostgreSQL`クライアントです。

!> Swoole 5.0 バージョンで完全に再構築され、以前のバージョンとは使用方法が異なります。旧バージョンを使用している場合は、[旧版ドキュメント](/coroutine_client/postgresql-old.md)をご覧ください。

!> Swoole 6.0 以降では、コルネリス`PostgreSQL`クライアントは削除されました。[コルネリス化pdo_pgsql](/runtime?id=swoole_hook_pdo_pgsql)を使用してください。

## コンパイルとインストール

* システムに`libpq`ライブラリがインストールされていることを確認する必要があります。
* `mac`では`postgresql`をインストールすると`libpq`ライブラリが自動でインストールされますが、環境によって違いがあるため、`ubuntu`では`apt-get install libpq-dev`、`centos`では`yum install postgresql10-devel`が必要かもしれません。
* Swoole をコンパイルする際には、コンパイルオプションを追加します：`./configure --enable-swoole-pgsql`

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
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchAll();
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
    $stmt = $pg->query('SELECT * FROM test');
    $arr = $stmt->fetchAll();
    $pg->query('COMMIT');
    var_dump($arr);
});
```

## プロパティ

### error

エラー情報を取得します。

## メソッド

### connect()

非ブロッキングなコルネリス接続を`postgresql`に確立します。

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo`は接続情報で、接続成功時はtrue、失敗時はfalseを返し、[error](/coroutine_client/postgresql?id=error)プロパティを使用してエラー情報を取得できます。
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

SQL文を実行します。非ブロッキングなコルネリスコマンドを送信します。

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
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
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        var_dump($arr);
    });
    ```

    * **insert id の取得**

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

    * **トランザクション**

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

テーブルのメタデータを見ることができます。非ブロッキングなコルネリス版です。

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

準備します。

```php
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **使用例**

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

クラス名：`Swoole\Coroutine\PostgreSQLStatement`

すべてのクエリは `PostgreSQLStatement` オブジェクトを返します

### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **パラメータ**
    * **`$result_type`**
      * **機能**：定数。オプションのパラメータで、返される値の初期化方法を制御します。
      * **デフォルト値**：`SW_PGSQL_ASSOC`
      * **その他の値**：なし

      値 | 返り値
      ---|---
      SW_PGSQL_ASSOC | フィールド名をキーとする連想配列を返す
      SW_PGSQL_NUM | フィールド番号をキーとする配列を返す
      SW_PGSQL_BOTH | 両方をキーとする配列を返す

  * **返り値**

    * 結果のすべての行を配列として返します。

### affectedRows()

影響を受けたレコードの数を返します。

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```

### numRows()

行の数を返します。

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```

### fetchObject()

一行をオブジェクトとして取り出します。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **例**

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

一行を連想配列として取り出します。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```

### fetchArray()

一行を配列として取り出します。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **パラメータ**
    * **`int $row`**
      * **機能**：取得したい行（レコード）の番号です。最初の行は `0` です。
      * **デフォルト値**：なし
      * **その他の値**：なし
    * **`$result_type`**
      * **機能**：定数。オプションのパラメータで、返される値の初期化方法を制御します。
      * **デフォルト値**：`SW_PGSQL_BOTH`
      * **その他の値**：なし

      値 | 返り値
      ---|---
      SW_PGSQL_ASSOC | フィールド名をキーとする連想配列を返す
      SW_PGSQL_NUM | フィールド番号をキーとする配列を返す
      SW_PGSQL_BOTH | 両方をキーとする配列を返す

  * **返り値**

    * 取得した行（タプル/レコード）に一致する配列を返します。これ以上の行を取り出すことができない場合は `false` を返します。

  * **使用例**

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

指定された `result` リソースに基づいて一行のデータ（レコード）を配列として返します。各取得された列は順に配列に格納され、オフセット `0` から始まります。

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **パラメータ**
    * **`int $row`**
      * **機能**：取得したい行（レコード）の番号です。最初の行は `0` です。
      * **デフォルト値**：なし
      * **その他の値**：なし
    * **`$result_type`**
      * **機能**：定数。オプションのパラメータで、返される値の初期化方法を制御します。
      * **デフォルト値**：`SW_PGSQL_NUM`
      * **その他の値**：なし

      値 | 返り値
      ---|---
      SW_PGSQL_ASSOC | フィールド名をキーとする連想配列を返す
      SW_PGSQL_NUM | フィールド番号をキーとする配列を返す
      SW_PGSQL_BOTH | 両方をキーとする配列を返す

  * **返り値**

    * 返された配列は取得した行と一致します。これ以上の行 `row` を取り出すことができない場合は `false` を返します。

  * **使用例**

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
