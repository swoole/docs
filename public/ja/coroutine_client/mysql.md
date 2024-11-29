```
# コロニアル MySQL

コロニアル MySQLクライアントです。

> 本クライアントはもはや使用を推奨しません。Swoole\Runtime::enableCoroutine + pdo_mysqlまたはmysqliを使用することを推奨します。つまり、[ワンクリックでコロニアル化](/runtime)された原生のMySQLクライアントです。
> Swoole 6.0以降、このコロニアルMySQLクライアントは削除されました。

## 使用例

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

## defer特性

[並行Client](/coroutine/multi_call)のセクションを参照してください。

## ストアドプロセス

4.0.0バージョン以降、MySQLストレアドプロセスとマルチ結果セットの取得がサポートされています。

## MySQL8.0

Swoole-4.0.1またはそれ以降のバージョンはMySQL8のすべてのセキュリティ検証機能に対応しており、直接クライアントを使用できます。パスワード設定を後戻しする必要はありません。

### 4.0.1 以前のバージョン

MySQL-8.0はデフォルトでセキュリティが強化されたcaching_sha2_passwordプラグインを使用しています。5.xからアップグレードした場合は、すべてのMySQL機能を使用できます。新しく作成されたMySQLの場合は、MySQLコマンドラインに入り、以下のコマンドを実行して互換性を持たせることができます：

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

コマンド内の `'root'@'localhost'` を使用するユーザーと、`password` を使用するパスワードを置き換えます。

それでも使用できない場合は、my.cnfで `default_authentication_plugin = mysql_native_password`を設定する必要があります。

## プロパティ

### serverInfo

接続情報であり、接続関数に渡された配列を保存しています。

### sock

接続に使用されるファイル記述子です。

### connected

MySQLサーバーに接続されているかどうかです。

> [connectedプロパティと接続状態が一致しない](/question/use?id=connectedプロパティと接続状態が一致しない)についての参照です。

### connect_error

connectしてMySQLサーバーに接続したときのエラー情報です。

### connect_errno

connectしてMySQLサーバーに接続したときのエラーコードで、整型です。

### error

MySQL指令を実行したときのサーバーからのエラー情報です。

### errno

MySQL指令を実行したときのサーバーからのエラーコードで、整型です。

### affected_rows

影響された行数です。

### insert_id

最後に挿入されたレコードの`id`です。

## メソッド

### connect()

MySQL接続を確立します。

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

> `$serverInfo`：パラメータは配列で渡されます。

```php
[
    'host'        => 'MySQL IPアドレス', // UNIXSocketを使用する場合は、unix://tmp/your_file.sockの形式で記入してください
    'user'        => 'データユーザ',
    'password'    => 'データベースパスワード',
    'database'    => 'データベース名',
    'port'        => 'MySQLポート 默认3306 可选パラメータ',
    'timeout'     => '接続タイムアウト時間', // connectのタイムアウト時間のみに影響を与え、queryやexecuteメソッドには影響しません。[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照してください
    'charset'     => '文字集合',
    'strict_type' => false, //厳格モードを有効にすると、queryメソッドから返されるデータも強类型になります
    'fetch_mode'  => true,  //fetchモードを有効にすると、pdoと同様にfetch/fetchAllで逐行または全結果集を取得できます(4.0バージョン以上)
]
```

### query()

SQL文を実行します。

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **パラメータ** 

    * **`string $sql`**
      * **機能**：SQL文
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間 【指定された時間内にMySQLサーバーがデータ返回できない場合、下層はfalseを返回し、エラーコード110を設定し、接続を切断します】
      * **値の単位**：秒で、最小精度はミリ秒(0.001秒)です
      * **デフォルト値**：`0`
      * **その他の値**：なし
      * **[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)についての参照です**

  * **返回値**

    * タイムアウトまたはエラーの場合はfalseを返回し、そうでなければarray形式で查询結果を返回します

  * **遅延受信**

  !> deferを設定した後、queryを呼び出すと直接trueを返回します。recvを呼び出すと协程で待ち、查询結果が返されます。

  * **例**

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

MySQLサーバーにSQL準備要求を送信します。

!> `prepare`は`execute`と組み合わせて使用する必要があります。準備要求が成功した後、`execute`メソッドを呼び出してMySQLサーバーにデータパラメータを送信します。

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **パラメータ** 

    * **`string $sql`**
      * **機能**：準備文【`?`をパラメータ占位符として使用します】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間 
      * **値の単位**：秒で、最小精度はミリ秒(0.001秒)です
      * **デフォルト値**：`0`
      * **その他の値**：なし
      * **[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)についての参照です**

  * **返回値**

    * 失敗した場合はfalseを返回し、$db->errorと$db->errnoを検査してエラーの原因を判断できます
    * 成功した場合は`Coroutine\MySQL\Statement`オブジェクトを返回し、そのオブジェクトの[execute](/coroutine_client/mysql?id=statement-gtexecute)メソッドを呼び出してパラメータを送信できます

  * **例**

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

SQL文の特殊文字をエスケープし、SQL注入攻撃を防ぎます。下層はmysqlndが提供する関数に基づいており、PHPのmysqlnd拡張に依存する必要があります。

!> 编译する際には [--enable-mysqlnd](/environment?id=compile_options)を有効にする必要があります。

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

  * **パラメータ** 

    * **`string $str`**
      * **機能**：エスケープ文字
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **使用例**

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
```
### begin()

トランザクションを開始します。`commit`と`rollback`と組み合わせてMySQLトランザクション処理を実現します。

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> MySQLトランザクションを起動し、成功すると`true`を返し、失敗すると`false`を返します。エラーコードは`$db->errno`で確認してください。
  
!> 同じMySQL接続オブジェクトに対しては、同時に一つのトランザクションしか開始できません。
前のトランザクションが`commit`されたり`rollback`されたりするまで、新しいトランザクションを始めることはできません。
そうでなければ、下層で`Swoole\MySQL\Exception`例外が投げられ、例外の`code`は`21`です。

  * **例**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```

### commit()

トランザクションをコミットします。

!> `begin`と組み合わせて使用する必要があります。

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> 成功すると`true`を返し、失敗すると`false`を返します。エラーコードは`$db->errno`で確認してください。

### rollback()

トランザクションをリールバックします。

!> `begin`と組み合わせて使用する必要があります。

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> 成功すると`true`を返し、失敗すると`false`を返します。エラーコードは`$db->errno`で確認してください。

### Statement->execute()

MySQLサーバーにSQL予処理データパラメータを送信します。

!> `execute`は`prepare`と組み合わせて使用し、`execute`を呼び出す前に必ず`prepare`で予処理リクエストを開始する必要があります。

!> `execute`方法は繰り返し呼び出すことができます。

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **パラメータ** 

    * **`array $params`**
      * **機能**：予処理データパラメータ 【`prepare`文のパラメータ数と同じです。`$params`は数字索引の配列でなければならず、パラメータの順序は`prepare`文と同じです】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間 【指定された時間内にMySQLサーバーがデータ返回できない場合、下層は`false`を返し、エラーコードは`110`に設定し、接続を切断します】
      * **値の単位**：秒、最小精度はミリ秒（`0.001`秒）
      * **デフォルト値**：`-1`
      * **その他の値**：なし
      * **参考[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)**

  * **戻り値** 

    * 成功すると `true`を返し、もし `connect`の `fetch_mode`パラメータを `true`に設定した場合
    * 成功すると `array`データセットの配列を返します。上述の状況でなければ、
    * 失敗すると`false`を返し、`$db->error`と`$db->errno`でエラーの原因を判断できます

  * **使用例** 

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

結果セットから次の行を取得します。

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> Swooleバージョン >= `4.0-rc1`では、`connect`時に`fetch_mode => true`オプションを加える必要があります

  * **例** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> `v4.4.0`の新しいMySQLドライバーからは、`fetch`は例示コードの方法で`NULL`まで読み取らなければならず、そうでなければ新しいリクエストを发起することができません (下層の要求に応じた読み取りメカニズムにより、メモリを節約できます)

### Statement->fetchAll()

結果セットのすべての行を含む配列を返します。

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> Swooleバージョン >= `4.0-rc1`では、`connect`時に`fetch_mode => true`オプションを加える必要があります

  * **例** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```

### Statement->nextResult()

マルチレスポンス結果セットハンドラーの中で次のレスポンス結果に進みます (例えば、ストレージプロセスのマルチ結果返却)。

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

  * **戻り値**

    * 成功すると `TRUE`を返します
    * 失敗すると `FALSE`を返します
    * 次の結果がない場合は`NULL`を返します

  * **例** 

    * **fetchモードでない場合**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **fetchモードの場合**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> `v4.4.0`の新しいMySQLドライバーからは、`fetch`は例示コードの方法で`NULL`まで読み取らなければならず、そうでなければ新しいリクエストを发起することができません (下層の要求に応じた読み取りメカニズムにより、メモリを節約できます)
