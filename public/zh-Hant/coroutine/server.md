# TCP伺服器

?> `Swoole\Coroutine\Server` 是一个完全[协程](/coroutine)化的类别，用於建立协程`TCP`伺服器，支援TCP和[unixSocket](/learn?id=什么是IPC)類型。

與[Server](/server/tcp_init)模組不同之處：

* 動態創建摧毀，在運行時可以動態監聽端口，也可以動態關閉伺服器
* 處理連接的過程是完全同步的，程式可以順序處理`Connect`、`Receive`、`Close`事件

!> 在4.4以上版本中可用


## 短命名

可使用`Co\Server`短名。


## 方法


### __construct()

?> **構建方法。** 

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **參數** 

    * **`string $host`**
      * **功能**：監聽的地址
      * **預設值**：無
      * **其它值**：無

    * **`int $port`**
      * **功能**：監聽的端口【如果為0將由作業系統隨機分配一個端口】
      * **預設值**：無
      * **其它值**：無

    * **`bool $ssl`**
      * **功能**：是否開啟SSL加密
      * **預設值**：`false`
      * **其它值**：`true`

    * **`bool $reuse_port`**
      * **功能**：是否開啟端口重用，效果和[此節](/server/setting?id=enable_reuse_port)的配置一樣
      * **預設值**：`false`
      * **其它值**：`true`
      * **版本影響**：Swoole版本 >= v4.4.4

  * **提示**

    * **$host 參數支援 3 種格式**

      * `0.0.0.0/127.0.0.1`: IPv4地址
      * `::/::1`: IPv6地址
      * `unix:/tmp/test.sock`: [UnixSocket](/learn?id=什麼是IPC)地址

    * **異常**

      * 參數錯誤、綁定地址和端口失敗、`listen`失敗時將拋出`Swoole\Exception`異常。


### set()

?> **設置協議處理參數。** 

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **配置參數**

    * 參數`$options`必須為一維的關聯索引數組，與 [setprotocol](/coroutine_client/socket?id=setprotocol) 方法接受的配置項完全一致。

    !> 必須在 [start()](/coroutine/server?id=start) 方法之前設置參數

    * **長度協議**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      * **`package_length_offset`**
        * **功能**：包體長度偏移量
        * **默认值**：0
        * **其他值**：无

      * **`package_body_offset`**
        * **功能**：包體內容偏移量
        * **默认值**：4
        * **其他值**：无

    ]);
    ```

    * **SSL證書設置**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      'ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```


### handle()

?> **設置連接處理函數。** 

!> 必須在 [start()](/coroutine/server?id=start) 之前設置處理函數

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **參數** 

    * **`callable $fn`**
      * **功能**：設置連接處理函數
      * **預設值**：無
      * **其它值**：無
      
  * **示例** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            $data = $conn->recv();
        }
    });
    ```

    !> -伺服器在`Accept`(建立連接)成功後，會自動創建[协程](/coroutine?id=协程调度)並執行`$fn` ；  
    -`$fn`是在新的子协程空間內執行，因此在函數內無需再次創建协程；  
    -`$fn`接受一個參數，類型為[Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection)物件;  
    -可以使用[exportSocket()](/coroutine/server?id=exportsocket)得到當前連接的Socket物件


### shutdown()

?> **終止伺服器。** 

?> 底層支持`start`和`shutdown`多次調用

```php
Swoole\Coroutine\Server->shutdown(): bool
```


### start()

?> **啟動伺服器。** 

```php
Swoole\Coroutine\Server->start(): bool
```

  * **返回值**

    * 啟動失敗會返回`false`，並設置`errCode`屬性
    * 啟動成功將進入循環，`Accept`連接
    * `Accept`(建立連接)後會創建一個新的协程，並在协程中調用handle方法指定的函數

  * **錯誤處理**

    * 當`Accept`(建立連接)發生`Too many open file`錯誤、或者無法創建子协程時，將暫停`1`秒後再繼續`Accept`
    * 發生錯誤時，`start()`方法將返回，錯誤信息將會以`Warning`的形式報出。


## 對象


### Coroutine\Server\Connection

`Swoole\Coroutine\Server\Connection`物件提供了四個方法：
 
#### recv()

接收數據，如果設置了協議處理，將每次返回完整的包

```php
function recv(float $timeout = 0)
```

#### send()

發送數據

```php
function send(string $data)
```

#### close()

關閉連接

```php
function close(): bool
```

#### exportSocket()

得到當前連接的Socket物件。可調用更多底層的方法，請參考 [Swoole\Coroutine\Socket](/coroutine_client/socket)

```php
function exportSocket(): Swoole\Coroutine\Socket
```

## 完整示例

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

//多進程管理模塊
$pool = new Process\Pool(2);
//讓每個OnWorkerStart回調都自動創建一個协程
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    //每個進程都監聽9501端口
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    //收到15信號關閉服務
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    //接收到新的連接請求 並自動創建一個协程
    $server->handle(function (Connection $conn) {
        while (true) {
            //接收數據
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            //發送數據
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    //開始監聽端口
    $server->start();
});
$pool->start();
```

!> 如果在Cygwin環境下運行請修改為單進程。`$pool = new Swoole\Process\Pool(1);`
