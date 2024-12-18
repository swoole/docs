# 協程TCP/UDP客戶端

`Coroutine\Client`提供了`TCP`、`UDP`、[unixSocket](/learn?id=什麼是IPC)傳輸協議的[Socket客戶端](/coroutine_client/socket)封裝代碼，使用時僅需`new Swoole\Coroutine\Client`即可。

* **實現原理**

    * `Coroutine\Client`的所有涉及網絡請求的方法，`Swoole`都會進行[協程調度](/coroutine?id=協程調度)，業務層無需感知
    * 使用方法和[Client](/client)同步模式方法完全一致
    * `connect`超時設置同時作用於`Connect`、`Recv`和`Send` 超時

* **繼承關係**

    * `Coroutine\Client`與[Client](/client)並不是繼承關係，但`Client`提供的方法均可在`Coroutine\Client`中使用。請參考 [Swoole\Client](/client?id=方法)，在此不再贅述 。
    * 在`Coroutine\Client`中可以使用`set`方法設置[配置選項](/client?id=配置)，使用方法和與`Client->set`完全一致，對於使用有區別的函數，在`set()`函數小節會單獨說明

* **使用示例**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

* **協議處理**

協程客戶端也支持長度和`EOF`協議處理，設置方法與 [Swoole\Client](/client?id=配置) 完全一致。

```php
$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0, //第N個字節是包長度的值
    'package_body_offset'   => 4, //第幾個字節開始計算長度
    'package_max_length'    => 2000000, //協議最大長度
));
```

### connect()

連接到遠程服務器。

```php
Swoole\Coroutine\Client->connect(string $host, int $port, float $timeout = 0.5): bool
```

  * **參數** 

    * **`string $host`**
      * **功能**：遠程服務器的地址【底層會自動進行協程切換解析域名為IP地址】
      * **默認值**：無
      * **其它值**：無

    * **`int $port`**
      * **功能**：遠程服務器端口
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：網絡IO的超时时間；包括`connect/send/recv`，超時發生時，連接會被自動`close`，參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：`0.5s`
      * **其它值**：無

* **提示**

    * 如果連接失敗，會返回`false`
    * 超時後返回，檢查`$cli->errCode`為`110`

* **失敗重試**

!> `connect`連接失敗後，不可直接進行重連。必須使用`close`關閉已有`socket`，然後再進行`connect`重試。

```php
//連接失敗
if ($cli->connect('127.0.0.1', 9501) == false) {
    //關閉已有socket
    $cli->close();
    //重試
    $cli->connect('127.0.0.1', 9501);
}
```

* **示例**

```php
if ($cli->connect('127.0.0.1', 9501)) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}

if ($cli->connect('/tmp/rpc.sock')) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}
```

### isConnected()

返回Client的連接狀態

```php
Swoole\Coroutine\Client->isConnected(): bool
```

  * **返回值**

    * 返回`false`，表示當前未連接到服務器
    * 返回`true`，表示當前已連接到服務器

!> `isConnected`方法返回的是應用層狀態，只表示`Client`執行了`connect`並成功連接到了`Server`，並且沒有執行`close`關閉連接。`Client`可以執行`send`、`recv`、`close`等操作，但不能再次執行`connect` 。  
這不代表連接一定是可用的，當執行`send`或`recv`時仍然有可能返回錯誤，因為應用層無法獲得底層`TCP`連接的狀態，執行`send`或`recv`時應用層與內核發生交互，才能得到真實的連接可用狀態。

### send()

發送數據。

```php
Swoole\Coroutine\Client->send(string $data): int|bool
```

  * **參數** 

    * **`string $data`**
    
      * **功能**：為發送的數據，必須為字符串類型，支持二進制數據
      * **默認值**：無
      * **其它值**：無

  * 發送成功返回寫入`Socket`緩存區的字節數，底層會儘可能地將所有數據發出。如果返回的字節數與傳入的`$data`長度不同，可能是`Socket`已被對端關閉，再下一次調用`send`或`recv`時將返回對應的錯誤碼。

  * 發送失敗返回false，可以使用 `$client->errCode` 獲取錯誤原因。

### recv()

recv方法用於從服務器端接收數據。

```php
Swoole\Coroutine\Client->recv(float $timeout = 0): string|bool
```

  * **參數** 

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

    !> 設置超時，優先使用指定的參數，其次使用`set`方法中傳入的`timeout`配置。發生超時的錯誤碼為`ETIMEDOUT`

  * **返回值**

    * 設置了[通信協議](/client?id=協議解析)，`recv`會返回完整的數據，長度受限於[package_max_length](/server/setting?id=package_max_length)
    * 未設置通信協議，`recv`最大返回`64K`數據
    * 未設置通信協議返回原始的數據，需要`PHP`代碼中自行實現網絡協議的處理
    * `recv`返回空字符串表示服務端主動關閉連接，需要`close`
    * `recv`失敗，返回`false`，檢測`$client->errCode`獲取錯誤原因，處理方式可參考下文的[完整示例](/coroutine_client/client?id=完整示例)

### close()

關閉連接。

!> `close`不存在阻塞，會立即返回。關閉操作沒有協程切換。

```php
Swoole\Coroutine\Client->close(): bool
```

### peek()

窺視數據。

!> `peek`方法直接操作`socket`，因此不會引起[協程調度](/coroutine?id=協程調度)。

```php
Swoole\Coroutine\Client->peek(int $length = 65535): string
```

  * **提示**

    * `peek`方法僅用於窺視內核`socket`緩存區中的數據，不進行偏移。使用`peek`之後，再調用`recv`仍然可以讀取到這部分數據
    * `peek`方法是非阻塞的，它會立即返回。當`socket`緩存區中有數據時，會返回數據內容。緩存區為空時返回`false`，並設置`$client->errCode`
    * 連接已被關閉`peek`會返回空字符串
### set()

設定客戶端參數。

```php
Swoole\Coroutine\Client->set(array $settings): bool
```

  * **配置參數**

    * 請參考 [Swoole\Client](/client?id=set) 。

* **和[Swoole\Client](/client?id=set)的差異**
    
    協程客戶端提供了更細粒度的超時控制。可以設定：
    
    * `timeout`：總超時，包括連接、發送、接收所有超時
    * `connect_timeout`：連接超時
    * `read_timeout`：接收超時
    * `write_timeout`：發送超時
    * 參考[客戶端超時規則](/coroutine_client/init?id=超時規則)

* **示例**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'timeout' => 0.5,
        'connect_timeout' => 1.0,
        'write_timeout' => 10.0,
        'read_timeout' => 0.5,
    ));

    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

### 完整示例

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // 全等於空 直接關閉連接
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // 可以自行根據業務邏輯和錯誤碼進行處理，例如：
                    // 如果超時則不關閉連接，其他情況直接關閉連接
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```
