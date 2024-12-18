# Coroutine\MySQL

協程MySQL客戶端。

!> 本客戶端不再推薦使用，推薦使用 `Swoole\Runtime::enableCoroutine` + `pdo_mysql`或 `mysqli` 方式，即[一鍵協程化](/runtime)原生 `MySQL` 客戶端  
!> `Swoole 6.0`之後，该協程 `MySQL` 客戶端已被移除

## 使用示例

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

請參考[並發Client](/coroutine/multi_call)一節。

## 儲存過程

從`4.0.0`版本後, 支持`MySQL`儲存過程和多結果集獲取。

## MySQL8.0

`Swoole-4.0.1`或更高版本支持了`MySQL8`所有的安全驗證能力, 可以直接正常使用客戶端，而無需回退密碼設定

### 4.0.1 以下版本

`MySQL-8.0`默認使用了安全性更強的`caching_sha2_password`插件, 如果是從`5.x`升級上來的, 可以直接使用所有`MySQL`功能, 如是新建的`MySQL`, 需要進入`MySQL`命令行執行以下操作來兼容:

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

將語句中的 `'root'@'localhost'` 替換成你所使用的用戶, `password` 替換成其密碼。

如仍無法使用, 應在my.cnf中設置 `default_authentication_plugin = mysql_native_password`

## 屬性

### serverInfo

連接信息，保存的是傳遞給連接函數的數組。

### sock

連接使用的文件描述符。

### connected

是否連接上了`MySQL`伺服器。

!> 參考[connected 屬性和連接狀態不一致](/question/use?id=connected屬性和連接狀態不一致)

### connect_error

執行`connect`連接伺服器時的錯誤信息。

### connect_errno

執行`connect`連接伺服器時的錯誤碼，類型為整型。

### error

執行`MySQL`指令時，伺服器返回的錯誤信息。

### errno

執行`MySQL`指令時，伺服器返回的錯誤碼，類型為整型。

### affected_rows

影響的行數。

### insert_id

最後一個插入的記錄`id`。

## 方法

### connect()

建立MySQL連接。

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

!> `$serverInfo`：參數以數組形式傳遞

```php
[
    'host'        => 'MySQL IP地址', // 若是本地UNIXSocket則應以形如`unix://tmp/your_file.sock`的格式填寫
    'user'        => '數據用戶',
    'password'    => '數據庫密碼',
    'database'    => '數據庫名',
    'port'        => 'MySQL端口 默認3306 可選參數',
    'timeout'     => '建立連接超時時間', // 僅影響connect超時時間，不影響query和execute方法,參考`客戶端超時規則`
    'charset'     => '字符集',
    'strict_type' => false, //開啟嚴格模式，query方法返回的數據也將轉為強類型
    'fetch_mode'  => true,  //開啟fetch模式, 可與pdo一樣使用fetch/fetchAll逐行或獲取全部結果集(4.0版本以上)
]
```

### query()

執行SQL語句。

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **參數** 

    * **`string $sql`**
      * **功能**：SQL語句
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：超時時間 【在規定的時間內`MySQL`伺服器未能返回數據，底層將返回`false`，設置錯誤碼為`110`，並切斷連接】
      * **值單位**：秒，最小精度為毫秒（`0.001`秒）
      * **默認值**：`0`
      * **其它值**：無
      * **參考[客戶端超時規則](/coroutine_client/init?id=超時規則)**

  * **返回值**

    * 超時/出錯返回`false`，否則 `array` 形式返回查詢結果

  * **延遲接收**

  !> 設定`defer`後，調用`query`會直接返回`true`。調用`recv`才會進入協程等待，返回查詢的結果。

  * **示例**

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

向MySQL伺服器發送SQL預處理請求。

!> `prepare`必須與`execute`配合使用。預處理請求成功後，調用`execute`方法向`MySQL`伺服器發送數據參數。

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **參數** 

    * **`string $sql`**
      * **功能**：預處理語句【使用`?`作為參數佔位符】
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：超時時間 
      * **值單位**：秒，最小精度為毫秒（`0.001`秒）
      * **默認值**：`0`
      * **其它值**：無
      * **參考[客戶端超時規則](/coroutine_client/init?id=超時規則)**

  * **返回值**

    * 失敗返回`false`，可檢查`$db->error`和`$db->errno`判斷錯誤原因
    * 成功返回`Coroutine\MySQL\Statement`對象，可調用對象的[execute](/coroutine_client/mysql?id=statement-gtexecute)方法發送參數

  * **示例**

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

轉義SQL語句中的特殊字符，避免SQL注入攻擊。底層基於`mysqlnd`提供的函數實現，需要依賴`PHP`的`mysqlnd`擴展。

!> 編譯時需要增加[--enable-mysqlnd](/environment?id=編譯選項)來啟用。

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

  * **參數** 

    * **`string $str`**
      * **功能**：轉義字符
      * **默認值**：無
      * **其它值**：無

  * **使用示例**

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

開啟事務。與`commit`和`rollback`結合實現`MySQL`事務處理。

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> 啟動一個`MySQL`事務，成功返回`true`，失敗返回`false`，請檢查`$db->errno`獲取錯誤碼。
  
!> 同一個`MySQL`連接對象，同一時間只能啟動一個事務；  
必須等到上一個事務`commit`或`rollback`才能繼續啟動新事務；  
否則底層會拋出`Swoole\MySQL\Exception`異常，異常`code`為`21`。

  * **示例**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```


### commit()

提交事務。 

!> 必須與`begin`配合使用。

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> 成功返回`true`，失敗返回`false`，請檢查`$db->errno`獲取錯誤碼。


### rollback()

回滾事務。

!> 必須與`begin`配合使用。

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> 成功返回`true`，失敗返回`false`，請檢查`$db->errno`獲取錯誤碼。


### Statement->execute()

向MySQL伺服器發送SQL預處理數據參數。

!> `execute`必須與`prepare`配合使用，調用`execute`之前必須先調用`prepare`發起預處理請求。

!> `execute`方法可以重複調用。

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **參數** 

    * **`array $params`**
      * **功能**：預處理數據參數 【必須與`prepare`語句的參數個數相同。`$params`必須為數字索引的數組，參數的順序與`prepare`語句相同】
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：超時時間 【在規定的時間內`MySQL`伺服器未能返回數據，底層將返回`false`，設置錯誤碼為`110`，並切斷連接】
      * **值單位**：秒，最小精度為毫秒（`0.001`秒）
      * **默認值**：`-1`
      * **其它值**：無
      * **參考[客戶端超時規則](/coroutine_client/init?id=超時規則)**

  * **返回值** 

    * 成功時返回 `true`，如果設置 `connect` 的 `fetch_mode` 參數為 `true` 時
    * 成功時返回 `array` 數據集數組，如不是上述情況時，
    * 失敗返回`false`，可檢查`$db->error`和`$db->errno`判斷錯誤原因

  * **使用示例** 

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

從結果集中獲取下一行。

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> Swoole版本 >= `4.0-rc1`，需在`connect`時加入`fetch_mode => true`選項

  * **示例** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> 從`v4.4.0`的新`MySQL`驅動開始, `fetch`必須使用示例代碼的方式讀到`NULL`為止, 否則將無法發起新的請求 (由於底層按需讀取機制, 可節省內存)


### Statement->fetchAll()

返回一個包含結果集中所有行的數組。

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> Swoole版本 >= `4.0-rc1`，需在`connect`時加入`fetch_mode => true`選項

  * **示例** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```

### Statement->nextResult()

在一個多響應結果語句句柄中推進到下一個響應結果 (如存儲過程的多結果返回)。

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

  * **返回值**

    * 成功時返回 `TRUE`
    * 失敗時返回 `FALSE`
    * 無下一結果返回`NULL`

  * **示例** 

    * **非fetch模式**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **fetch模式**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> 從`v4.4.0`的新`MySQL`驅動開始, `fetch`必須使用示例代碼的方式讀到`NULL`為止, 否則將無法發起新的請求 (由於底層按需讀取機制, 可節省內存)
