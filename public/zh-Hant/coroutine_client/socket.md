# 协程\Socket

`Swoole\Coroutine\Socket`模塊相比於[協程風格服務端](/server/co_init)和[協程客戶端](/coroutine_client/init)相關模塊`Socket`可以實現更細粒度的一些`IO`操作。

!> 可使用`Co\Socket`短命名簡化類名。此模塊比較底層，使用者最好有Socket編程經驗。

## 完整示例

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9601);
    while ($retval)
    {
        $n = $socket->send('hello');
        var_dump($n);

        $data = $socket->recv();
        var_dump($data);

        //發生錯誤或對端關閉連接，本端也需要關閉
        if ($data === '' || $data === false) {
            echo "errCode: {$socket->errCode}\n";
            $socket->close();
            break;
        }

        Coroutine::sleep(1.0);
    }

    var_dump($retval, $socket->errCode, $socket->errMsg);
});
```

## 協程調度

`Coroutine\Socket`模塊提供的`IO`操作接口均為同步編程風格，底層自動使用[協程調度](/coroutine?id=協程調度)器實現[異步IO](/learn?id=同步io異步io)。

## 錯誤碼

在執行`socket`相關系統調用時，可能返回-1錯誤，底層會設置`Coroutine\Socket->errCode`屬性為系統錯誤編號`errno`，請參考響應的`man`文檔。如`$socket->accept()`返回錯誤時，`errCode`含義可以參考`man accept`中列出的錯誤碼文檔。

## 屬性

### fd

`socket`對應的文件描述符`ID`

### errCode

錯誤碼

## 方法

### __construct()

構造方法。構造`Coroutine\Socket`對象。

```php
Swoole\Coroutine\Socket::__construct(int $domain, int $type, int $protocol);
```

!> 詳情可參見`man socket`文檔。

  * **參數** 

    * **`int $domain`**
      * **功能**：協議域【可使用`AF_INET`、`AF_INET6`、`AF_UNIX`】
      * **默認值**：無
      * **其它值**：無

    * **`int $type`**
      * **功能**：類型【可使用`SOCK_STREAM`、`SOCK_DGRAM`、`SOCK_RAW`】
      * **默認值**：無
      * **其它值**：無

    * **`int $protocol`**
      * **功能**：協議【可使用`IPPROTO_TCP`、`IPPROTO_UDP`、`IPPROTO_STCP`、`IPPROTO_TIPC`，`0`】
      * **默認值**：無
      * **其它值**：無

!> 構造方法會調用`socket`系統調用創建一個`socket`句柄。調用失敗時會拋出`Swoole\Coroutine\Socket\Exception`異常。並設置`$socket->errCode`屬性。可根據該屬性的值得到系統調用失敗的原因。

### getOption()

獲取配置。

!> 此方法對應`getsockopt`系統調用, 詳情可參見`man getsockopt`文檔。  
此方法和`sockets`擴展的`socket_get_option`功能等價, 可以參見[PHP文檔](https://www.php.net/manual/zh/function.socket-get-option.php)。

!> Swoole版本 >= v4.3.2

```php
Swoole\Coroutine\Socket->getOption(int $level, int $optname): mixed
```

  * **參數** 

    * **`int $level`**
      * **功能**：指定選項所在的協議級別
      * **默認值**：無
      * **其它值**：無

      !> 例如，要在套接字級別檢索選項，將使用`SOL_SOCKET`的 `level` 參數。  
      可以通過指定該級別的協議編號來使用其它級別，例如`TCP`。可以使用[getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php)函數找到協議號。

    * **`int $optname`**
      * **功能**：可用的套接字選項與[socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)函數的套接字選項相同
      * **默認值**：無
      * **其它值**：無

### setOption()

設置配置。

!> 此方法對應`setsockopt`系統調用, 詳情可參見`man setsockopt`文檔。此方法和`sockets`擴展的`socket_set_option`功能等價, 可以參見[PHP文檔](https://www.php.net/manual/zh/function.socket-set-option.php)

!> Swoole版本 >= v4.3.2

```php
Swoole\Coroutine\Socket->setOption(int $level, int $optname, mixed $optval): bool
```

  * **參數** 

    * **`int $level`**
      * **功能**：指定選項所在的協議級別
      * **默認值**：無
      * **其它值**：無

      !> 例如，要在套接字級別檢索選項，將使用`SOL_SOCKET`的 `level` 參數。  
      可以通過指定該級別的協議編號來使用其它級別，例如`TCP`。可以使用[getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php)函數找到協議號。

    * **`int $optname`**
      * **功能**：可用的套接字選項與[socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)函數的套接字選項相同
      * **默認值**：無
      * **其它值**：無

    * **`int $optval`**
      * **功能**：選項的值 【可以是`int`、`bool`、`string`、`array`。根據`level`和`optname`決定。】
      * **默認值**：無
      * **其它值**：無

### setProtocol()

使`socket`獲得協議處理能力，可以配置是否開啟`SSL`加密傳輸和解決 [TCP數據包邊界問題](/learn?id=tcp數據包邊界問題) 等

!> Swoole版本 >= v4.3.2

```php
Swoole\Coroutine\Socket->setProtocol(array $settings): bool
```

  * **$settings 支持的參數**

參數 | 類型
---|---
open_ssl | bool
ssl_cert_file | string
ssl_key_file | string
open_eof_check | bool
open_eof_split | bool
open_mqtt_protocol | bool
open_fastcgi_protocol | bool
open_length_check | bool
package_eof | string
package_length_type | string
package_length_offset | int
package_body_offset | int
package_length_func | callable
package_max_length | int

!> 上述所有參數的意義和[Server->set()](/server/setting?id=open_eof_check)完全一致，在此不再贅述。

  * **示例**

```php
$socket->setProtocol([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,
    'package_body_offset'   => 4,
]);
```

### bind()

綁定地址和端口。

!> 此方法沒有`IO`操作，不會引起協程切換

```php
Swoole\Coroutine\Socket->bind(string $address, int $port = 0): bool
```

  * **參數** 

    * **`string $address`**
      * **功能**：綁定的地址【如`0.0.0.0`、`127.0.0.1`】
      * **默認值**：無
      * **其它值**：無

    * **`int $port`**
      * **功能**：綁定的端口【默認為`0`，系統會隨機綁定一個可用端口，可使用[getsockname](/coroutine_client/socket?id=getsockname)方法得到系統分配的`port`】
      * **默認值**：`0`
      * **其它值**：無

  * **返回值** 

    * 綁定成功返回`true`
    * 綁定失敗返回`false`，請檢查`errCode`屬性獲取失敗原因
### listen()

監聽`Socket`。

!> 此方法沒有`IO`操作，不會引起協程切換

```php
Swoole\Coroutine\Socket->listen(int $backlog = 0): bool
```

  * **參數** 

    * **`int $backlog`**
      * **功能**：監聽佇列的長度【默認為`0`，系統底層使用`epoll`實現了異步`IO`，不存在阻塞，因此`backlog`的重要程度並不高】
      * **默認值**：`0`
      * **其它值**：無

      !> 如果應用中存在阻塞或耗時邏輯，`accept`接受連接不及時，新創建的連接就會堆積在`backlog`監聽佇列中，如超出`backlog`長度，服務就會拒絕新的連接進入

  * **返回值** 

    * 綁定成功返回`true`
    * 綁定失敗返回`false`，請檢查`errCode`屬性獲取失敗原因

  * **內核參數** 

    `backlog`的最大值受限於內核參數`net.core.somaxconn`，而`Linux`中可以工具`sysctl`來動態調整所有的`kernel`參數。動態調整是內核參數值修改後即時生效。但是這個生效僅限於`OS`層面，必須重啟應用才能真正生效，命令`sysctl -a`會顯示所有的內核參數及值。

    ```shell
    sysctl -w net.core.somaxconn=2048
    ```

    以上命令將內核參數`net.core.somaxconn`的值改成了`2048`。這樣的改動雖然可以立即生效，但是重啟機器後會恢復默認值。為了永久保留改動，需要修改`/etc/sysctl.conf`，增加`net.core.somaxconn=2048`然後執行命令`sysctl -p`生效。


### accept()

接受客戶端發起的連接。

調用此方法會立即掛起當前協程，並加入[EventLoop](/learn?id=什麼是eventloop)監聽可讀事件，當`Socket`可讀有到來的連接時自動喚醒該協程，並返回對應客戶端連接的`Socket`對象。

!> 該方法必須在使用`listen`方法後使用，適用於`Server`端。

```php
Swoole\Coroutine\Socket->accept(float $timeout = 0): Coroutine\Socket|false;
```

  * **參數** 

    * **`float $timeout`**
      * **功能**：設置超時【設置超時參數後，底層會設置定時器，在規定的時間沒有客戶端連接到來，`accept`方法將返回`false`】
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值** 

    * 超時或`accept`系統調用報錯時返回`false`，可使用`errCode`屬性獲取錯誤碼，其中超時錯誤碼為`ETIMEDOUT`
    * 成功返回客戶端連接的`socket`，類型同樣為`Swoole\Coroutine\Socket`對象。可對其執行`send`、`recv`、`close`等操作

  * **示例**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
$socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
$socket->bind('127.0.0.1', 9601);
$socket->listen(128);

    while(true) {
        echo "Accept: \n";
        $client = $socket->accept();
        if ($client === false) {
            var_dump($socket->errCode);
        } else {
            var_dump($client);
        }
    }
});
```


### connect()

連接到目標伺服器。

調用此方法會發起異步的`connect`系統調用，並掛起當前協程，底層會監聽可寫，當連接完成或失敗後，恢復該協程。

該方法適用於`Client`端，支持`IPv4`、`IPv6`、[unixSocket](/learn?id=什麼是IPC)。

```php
Swoole\Coroutine\Socket->connect(string $host, int $port = 0, float $timeout = 0): bool
```

  * **參數** 

    * **`string $host`**
      * **功能**：目標伺服器的地址【如`127.0.0.1`、`192.168.1.100`、`/tmp/php-fpm.sock`、`www.baidu.com`等，可以傳入`IP`地址、`Unix Socket`路徑或域名。若為域名，底層會自動進行異步的`DNS`解析，不會引起阻塞】
      * **默認值**：無
      * **其它值**：無

    * **`int $port`**
      * **功能**：目標伺服器端口【`Socket`的`domain`為`AF_INET`、`AF_INET6`時必須設置端口】
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間【底層會設置定時器，在規定的時間內未能建立連接，`connect`將返回`false`】
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值** 

    * 超時或`connect`系統調用報錯時返回`false`，可使用`errCode`屬性獲取錯誤碼，其中超時錯誤碼為`ETIMEDOUT`
    * 成功返回`true`


### checkLiveness()

通過系統調用檢查連接是否存活 (在異常斷開時無效, 僅能偵測到對端正常close下的連接斷開)

!> Swoole版本 >= `v4.5.0` 可用

```php
Swoole\Coroutine\Socket->checkLiveness(): bool
```

  * **返回值** 

    * 連接存活時返回`true`, 否則返回`false`


### send()

向對端發送數據。

!> `send`方法會立即執行`send`系統調用發送數據，當`send`系統調用返回錯誤`EAGAIN`時，底層將自動監聽可寫事件，並掛起當前協程，等待可寫事件觸發時，重新執行`send`系統調用發送數據，並喚醒該協程。  

!> 如果`send`過快，`recv`過慢最終會導致操作系統緩衝區寫滿，當前協程掛起在send方法，可以適當調大緩衝區，[/proc/sys/net/core/wmem_max和SO_SNDBUF](https://stackoverflow.com/questions/21856517/whats-the-practical-limit-on-the-size-of-single-packet-transmitted-over-domain)

```php
Swoole\Coroutine\Socket->send(string $data, float $timeout = 0): int|false
```

  * **參數** 

    * **`string $data`**
      * **功能**：要發送的數據內容【可以為文本或二進制數據】
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值** 

    * 發送成功返回寫入的字節數，**請注意實際寫入的數據可能小於`$data`參數的長度**，應用層代碼需要對比返回值與`strlen($data)`是否相等來判斷是否發送完成
    * 發送失敗返回`false`，並設置`errCode`屬性
### sendAll()

向對端發送數據。與`send`方法不同的是, `sendAll`會盡可能完整地發送數據, 直到成功發送全部數據或遇到錯誤中止。

!> `sendAll`方法會立即執行多次`send`系統調用發送數據，當`send`系統調用返回錯誤`EAGAIN`時，底層將自動監聽可寫事件，並掛起當前協程，等待可寫事件觸發時，重新執行`send`系統調用發送數據, 直到數據發送完成或遇到錯誤, 喚醒對應協程。  

!> Swoole版本 >= v4.3.0

```php
Swoole\Coroutine\Socket->sendAll(string $data, float $timeout = 0) : int | false;
```

  * **參數** 

    * **`string $data`**
      * **功能**：要發送的數據內容【可以為文本或二進制數據】
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值** 

    * `sendAll`會保證數據全部發送成功，但是`sendAll`期間對端有可能將連接斷開，此時可能發送成功了部分數據，返回值會返回這個成功數據的長度，應用層代碼需要對比返回值與`strlen($data)`是否相等來判斷是否發送完成，根據業務需求是否需要續傳。
    * 發送失敗返回`false`，並設置`errCode`屬性


### peek()

窺視讀緩衝區中的數據, 相當於系統調用中的`recv(length, MSG_PEEK)`。

!> `peek`是立即完成的, 不會掛起協程, 但有一次系統調用開銷

```php
Swoole\Coroutine\Socket->peek(int $length = 65535): string|false
```

  * **參數** 

    * **`int $length`**
      * **功能**：指定用於拷貝窺視到的數據的內存大小 (注意：這裡會分配內存, 過大的長度可能會導致內存耗尽)
      * **值單位**：字節
      * **默認值**：無
      * **其它值**：無

  * **返回值** 

    * 窺視成功返回數據
    * 窺視失敗返回`false`，並設置`errCode`屬性


### recv()

接收數據。

!> `recv`方法會立即掛起當前協程並監聽可讀事件，等待對端發送數據後，可讀事件觸發時，執行`recv`系統調用獲取`socket`緩存區中的數據，並喚醒該協程。

```php
Swoole\Coroutine\Socket->recv(int $length = 65535, float $timeout = 0): string|false
```

  * **參數** 

    * **`int $length`**
      * **功能**：指定用於接收數據的內存大小 (注意：這裡會分配內存, 過大的長度可能會導致內存耗盡)
      * **值單位**：字節
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值** 

    * 接收成功返回實際數據
    * 接收失敗返回`false`，並設置`errCode`屬性
    * 接收超時，錯誤碼為`ETIMEDOUT`

!> 返回值不一定等於預期長度, 需要自行檢查該次調用接收數據的長度, 如需要保證單次調用獲取到指定長度的數據, 請使用`recvAll`方法或自行循環獲取  
TCP數據包邊界問題請參考`setProtocol()`方法，或者用`sendto()`;


### recvAll()

接收數據。與`recv`不同的是, `recvAll`會盡可能完整地接收響應長度的數據, 直到接收完成或遇到錯誤失敗。

!> `recvAll`方法會立即掛起當前協程並監聽可讀事件，等待對端發送數據後，可讀事件觸發時，執行`recv`系統調用獲取`socket`緩存區中的數據, 重複該行為直到接收到指定長度的數據或遇到錯誤終止，並喚醒該協程。

!> Swoole版本 >= v4.3.0

```php
Swoole\Coroutine\Socket->recvAll(int $length = 65535, float $timeout = 0): string|false
```

  * **參數** 

    * **`int $length`**
      * **功能**：期望接收到的數據大小 (注意：這裡會分配內存, 過大的長度可能會導致內存耗盡)
      * **值單位**：字節
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值** 

    * 接收成功返回實際數據, 並且返回的字符串長度和參數長度一致
    * 接收失敗返回`false`，並設置`errCode`屬性
    * 接收超時，錯誤碼為`ETIMEDOUT`


### readVector()

分段接收數據。

!> `readVector`方法會立即執行`readv`系統調用讀取數據，當`readv`系統調用返回錯誤`EAGAIN`時，底層將自動監聽可讀事件，並掛起當前協程，等待可讀事件觸發時，重新執行`readv`系統調用讀取數據，並喚醒該協程。  

!> Swoole版本 >= v4.5.7

```php
Swoole\Coroutine\Socket->readVector(array $io_vector, float $timeout = 0): array|false
```

  * **參數** 

    * **`array $io_vector`**
      * **功能**：期望接收到的分段數據大小
      * **值單位**：字節
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值**

    * 接收成功返回的分段數據
    * 接收失敗返回空數組，並設置`errCode`屬性
    * 接收超時，錯誤碼為`ETIMEDOUT`

  * **示例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 如果對端發來了helloworld
$ret = $socket->readVector([5, 5]);
// 那麼，$ret是['hello', 'world']
```


### readVectorAll()

分段接收數據。

!> `readVectorAll`方法會立即執行多次`readv`系統調用讀取數據，當`readv`系統調用返回錯誤`EAGAIN`時，底層將自動監聽可讀事件，並掛起當前協程，等待可讀事件觸發時，重新執行`readv`系統調用讀取數據, 直到數據讀取完成或遇到錯誤, 喚醒對應協程。

!> Swoole版本 >= v4.5.7

```php
Swoole\Coroutine\Socket->readVectorAll(array $io_vector, float $timeout = 0): array|false
```

  * **參數** 

    * **`array $io_vector`**
      * **功能**：期望接收到的分段數據大小
      * **值單位**：字節
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值**

    * 接收成功返回的分段數據
    * 接收失敗返回空數組，並設置`errCode`屬性
    * 接收超時，錯誤碼為`ETIMEDOUT`
### writeVector()

分段發送數據。

!> `writeVector`方法會立即執行`writev`系統調用發送數據，當`writev`系統調用返回錯誤`EAGAIN`時，底層將自動監聽可寫事件，並掛起當前協程，等待可寫事件觸發時，重新執行`writev`系統調用發送數據，並喚醒該協程。  

!> Swoole版本 >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVector(array $io_vector, float $timeout = 0): int|false
```

  * **參數** 

    * **`array $io_vector`**
      * **功能**：期望發送的分段數據
      * **值單位**：字節
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值**

    * 發送成功返回寫入的字節數，**請注意實際寫入的數據可能小於`$io_vector`參數的總長度**，應用層代碼需要對比返回值與`$io_vector`參數的總長度是否相等來判斷是否發送完成
    * 發送失敗返回`false`，並設置`errCode`屬性

  * **示例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 此時會按照數組裡面的順序發送給對端，實際上就是發送helloworld
$socket->writeVector(['hello', 'world']);
```


### writeVectorAll()

向對端發送數據。與`writeVector`方法不同的是, `writeVectorAll`會儘可能完整地發送數據, 直到成功發送全部數據或遇到錯誤中止。

!> `writeVectorAll`方法會立即執行多次`writev`系統調用發送數據，當`writev`系統調用返回錯誤`EAGAIN`時，底層將自動監聽可寫事件，並掛起當前協程，等待可寫事件觸發時，重新執行`writev`系統調用發送數據, 直到數據發送完成或遇到錯誤, 喚醒對應協程。

!> Swoole版本 >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVectorAll(array $io_vector, float $timeout = 0): int|false
```

  * **參數** 

    * **`array $io_vector`**
      * **功能**：期望發送的分段數據
      * **值單位**：字節
      * **默認值**：無
      * **其它值**：無

    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值**

    * `writeVectorAll`會保證數據全部發送成功，但是`writeVectorAll`期間對端有可能將連接斷開，此時可能發送成功了部分數據，返回值會返回這個成功數據的長度，應用層代碼需要對比返回值與`$io_vector`參數的總長度是否相等來判斷是否發送完成，根據業務需求是否需要續傳。
    * 發送失敗返回`false`，並設置`errCode`屬性

  * **示例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 此時會按照數組裡面的順序發送給對端，實際上就是發送helloworld
$socket->writeVectorAll(['hello', 'world']);
```


### recvPacket()

對於已通過`setProtocol`方法設置協議的Socket對象, 可調用此方法接收一個完整的協議數據包

!> Swoole版本 >= v4.4.0

```php
Swoole\Coroutine\Socket->recvPacket(float $timeout = 0): string|false
```

  * **參數** 
    * **`float $timeout`**
      * **功能**：設置超時時間
      * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

  * **返回值** 

    * 接收成功返回一個完整協議數據包
    * 接收失敗返回`false`，並設置`errCode`屬性
    * 接收超時，錯誤碼為`ETIMEDOUT`


### recvLine()

用於解決 [socket_read](https://www.php.net/manual/en/function.socket-read.php) 兼容性問題

```php
Swoole\Coroutine\Socket->recvLine(int $length = 65535, float $timeout = 0): string|false
```


### recvWithBuffer()

用於解決使用 `recv(1)` 逐字節接收時產生大量系統調用問題

```php
Swoole\Coroutine\Socket->recvWithBuffer(int $length = 65535, float $timeout = 0): string|false
```


### recvfrom()

接收數據，並設置來源主機的地址和端口。用於`SOCK_DGRAM`類型的`socket`。

!> 此方法會引起[協程調度](/coroutine?id=協程調度)，底層會立即掛起當前協程，並監聽可讀事件。可讀事件觸發，收到數據後執行`recvfrom`系統調用獲取數據包。

```php
Swoole\Coroutine\Socket->recvfrom(array &$peer, float $timeout = 0): string|false
```

* **參數**

    * **`array $peer`**
        * **功能**：對端地址和端口，引用類型。【函數成功返回時會設置為數組，包括`address`和`port`兩個元素】
        * **默認值**：無
        * **其它值**：無

    * **`float $timeout`**
        * **功能**：設置超時時間【在規定的時間內未返回數據，`recvfrom`方法會返回`false`】
        * **值單位**：秒【支持浮點型，如`1.5`表示`1s`+`500ms`】
        * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
        * **其它值**：無

* **返回值**

    * 成功接收數據，返回數據內容，並設置`$peer`為數組
    * 失敗返回`false`，並設置`errCode`屬性，不修改`$peer`的內容

* **示例**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    while (true) {
        $peer = null;
        $data = $socket->recvfrom($peer);
        echo "[Server] recvfrom[{$peer['address']}:{$peer['port']}] : $data\n";
        $socket->sendto($peer['address'], $peer['port'], "Swoole: $data");
    }
});
```


### sendto()

向指定的地址和端口發送數據。用於`SOCK_DGRAM`類型的`socket`。

!> 此方法沒有[協程調度](/coroutine?id=協程調度)，底層會立即調用`sendto`向目標主機發送數據。此方法不會監聽可寫，`sendto`可能會因為緩衝區已滿而返回`false`，需要自行處理，或者使用`send`方法。

```php
Swoole\Coroutine\Socket->sendto(string $address, int $port, string $data): int|false
```

  * **參數** 

    * **`string $address`**
      * **功能**：目標主機的`IP`地址或[unixSocket](/learn?id=什麼是IPC)路徑【`sendto`不支持域名，使用`AF_INET`或`AF_INET6`時，必須傳入合法的`IP`地址，否則發送會返回失敗】
      * **默認值**：無
      * **其它值**：無

    * **`int $port`**
      * **功能**：目標主機的端口【發送廣播時可以為`0`】
      * **默認值**：無
      * **其它值**：無

    * **`string $data`**
      * **功能**：發送的數據【可以為文本或二進制內容，請注意`SOCK_DGRAM`發送包的最大長度為`64K`】
      * **默認值**：無
      * **其它值**：無

  * **返回值** 

    * 發送成功返回發送的字節數
    * 發送失敗返回`false`，並設置`errCode`屬性

  * **示例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
$socket->sendto('127.0.0.1', 9601, 'Hello');
```
### getsockname()

獲取socket的地址和端口信息。

!> 此方法沒有[協程調度](/coroutine?id=協程調度)開銷。

```php
Swoole\Coroutine\Socket->getsockname(): array|false
```

  * **返回值** 

    * 調用成功返回，包含`address`和`port`的數組
    * 調用失敗返回`false`，並設置`errCode`屬性


### getpeername()

獲取`socket`的對端地址和端口信息，僅用於`SOCK_STREAM`類型有連接的`socket`。

?> 此方法沒有[協程調度](/coroutine?id=協程調度)開銷。

```php
Swoole\Coroutine\Socket->getpeername(): array|false
```

  * **返回值** 

    * 調用成功返回，包含`address`和`port`的數組
    * 調用失敗返回`false`，並設置`errCode`屬性


### close()

關閉`Socket`。

!> `Swoole\Coroutine\Socket`對象析構時如果會自動執行`close`，此方法沒有[協程調度](/coroutine?id=協程調度)開銷。

```php
Swoole\Coroutine\Socket->close(): bool
```

  * **返回值** 

    * 關閉成功返回`true`
    * 失敗返回`false`
    

### isClosed()

`Socket`是否已關閉。

```php
Swoole\Coroutine\Socket->isClosed(): bool
```

## 常量

等價於`sockets`擴展提供的常量, 且不會與`sockets`擴展產生衝突

!> 在不同系統下的值會有出入, 以下代碼僅為示例, 請勿使用其值

```php
define ('AF_UNIX', 1);
define ('AF_INET', 2);

/**
 * Only available if compiled with IPv6 support.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('AF_INET6', 10);
define ('SOCK_STREAM', 1);
define ('SOCK_DGRAM', 2);
define ('SOCK_RAW', 3);
define ('SOCK_SEQPACKET', 5);
define ('SOCK_RDM', 4);
define ('MSG_OOB', 1);
define ('MSG_WAITALL', 256);
define ('MSG_CTRUNC', 8);
define ('MSG_TRUNC', 32);
define ('MSG_PEEK', 2);
define ('MSG_DONTROUTE', 4);

/**
 * Not available on Windows platforms.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOR', 128);

/**
 * Not available on Windows platforms.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOF', 512);
define ('MSG_CONFIRM', 2048);
define ('MSG_ERRQUEUE', 8192);
define ('MSG_NOSIGNAL', 16384);
define ('MSG_DONTWAIT', 64);
define ('MSG_MORE', 32768);
define ('MSG_WAITFORONE', 65536);
define ('MSG_CMSG_CLOEXEC', 1073741824);
define ('SO_DEBUG', 1);
define ('SO_REUSEADDR', 2);

/**
 * This constant is only available in PHP 5.4.10 or later on platforms that
 * support the <b>SO_REUSEPORT</b> socket option: this
 * includes Mac OS X and FreeBSD, but does not include Linux or Windows.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SO_REUSEPORT', 15);
define ('SO_KEEPALIVE', 9);
define ('SO_DONTROUTE', 5);
define ('SO_LINGER', 13);
define ('SO_BROADCAST', 6);
define ('SO_OOBINLINE', 10);
define ('SO_SNDBUF', 7);
define ('SO_RCVBUF', 8);
define ('SO_SNDLOWAT', 19);
define ('SO_RCVLOWAT', 18);
define ('SO_SNDTIMEO', 21);
define ('SO_RCVTIMEO', 20);
define ('SO_TYPE', 3);
define ('SO_ERROR', 4);
define ('SO_BINDTODEVICE', 25);
define ('SOL_SOCKET', 1);
define ('SOMAXCONN', 128);

/**
 * Used to disable Nagle TCP algorithm.
 * Added in PHP 5.2.7.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('TCP_NODELAY', 1);
define ('PHP_NORMAL_READ', 1);
define ('PHP_BINARY_READ', 2);
define ('MCAST_JOIN_GROUP', 42);
define ('MCAST_LEAVE_GROUP', 45);
define ('MCAST_BLOCK_SOURCE', 43);
define ('MCAST_UNBLOCK_SOURCE', 44);
define ('MCAST_JOIN_SOURCE_GROUP', 46);
define ('MCAST_LEAVE_SOURCE_GROUP', 47);
define ('IP_MULTICAST_IF', 32);
define ('IP_MULTICAST_TTL', 33);
define ('IP_MULTICAST_LOOP', 34);
define ('IPV6_MULTICAST_IF', 17);
define ('IPV6_MULTICAST_HOPS', 18);
define ('IPV6_MULTICAST_LOOP', 19);
define ('IPV6_V6ONLY', 27);

/**
 * Operation not permitted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPERM', 1);

/**
 * No such file or directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOENT', 2);

/**
 * Interrupted system call.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINTR', 4);

/**
 * I/O error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIO', 5);

/**
 * No such device or address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENXIO', 6);

/**
 * Arg list too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_E2BIG', 7);

/**
 * Bad file number.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADF', 9);

/**
 * Try again.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAGAIN', 11);

/**
 * Out of memory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEM', 12);

/**
 * Permission denied.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EACCES', 13);

/**
 * Bad address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EFAULT', 14);

/**
 * Block device required.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTBLK', 15);

/**
 * Device or resource busy.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBUSY', 16);

/**
 * File exists.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EEXIST', 17);

/**
 * Cross-device link.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXDEV', 18);

/**
 * No such device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODEV', 19);

/**
 * Not a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTDIR', 20);

/**
 * Is a directory.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISDIR', 21);

/**
 * Invalid argument.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINVAL', 22);

/**
 * File table overflow.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENFILE', 23);

/**
 * Too many open files.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMFILE', 24);

/**
 * Not a typewriter.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTTY', 25);

/**
 * No space left on device.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSPC', 28);

/**
 * Illegal seek.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESPIPE', 29);

/**
 * Read-only file system.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EROFS', 30);

/**
 * Too many links.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMLINK', 31);

/**
 * Broken pipe.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPIPE', 32);

/**
 * File name too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENAMETOOLONG', 36);

/**
 * No record locks available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLCK', 37);

/**
 * Function not implemented.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSYS', 38);

/**
 * Directory not empty.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTEMPTY', 39);

/**
 * Too many symbolic links encountered.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELOOP', 40);

/**
 * Operation would block.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EWOULDBLOCK', 11);

/**
 * No message of desired type.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMSG', 42);

/**
 * Identifier removed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIDRM', 43);

/**
 * Channel number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECHRNG', 44);

/**
 * Level 2 not synchronized.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2NSYNC', 45);

/**
 * Level 3 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3HLT', 46);

/**
 * Level 3 reset.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3RST', 47);

/**
 * Link number out of range.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELNRNG', 48);

/**
 * Protocol driver not attached.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUNATCH', 49);

/**
 * No CSI structure available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOCSI', 50);

/**
 * Level 2 halted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2HLT', 51);

/**
 * Invalid exchange.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADE', 52);

/**
 * Invalid request descriptor.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADR', 53);

/**
 * Exchange full.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXFULL', 54);

/**
 * No anode.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOANO', 55);

/**
 * Invalid request code.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADRQC', 56);

/**
 * Invalid slot.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADSLT', 57);

/**
 * Device not a stream.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSTR', 60);

/**
 * No data available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODATA', 61);

/**
 * Timer expired.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIME', 62);

/**
 * Out of streams resources.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSR', 63);

/**
 * Machine is not on the network.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENONET', 64);

/**
 * Object is remote.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMOTE', 66);

/**
 * Link has been severed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLINK', 67);

/**
 * Advertise error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EADV', 68);

/**
 * Srmount error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESRMNT', 69);

/**
 * Communication error on send.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECOMM', 70);

/**
 * Protocol error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTO', 71);

/**
 * Multihop attempted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMULTIHOP', 72);

/**
 * Not a data message.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADMSG', 74);

/**
 * Name not unique on network.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTUNIQ', 76);

/**
 * File descriptor in bad state.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADFD', 77);

/**
 * Remote address changed.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMCHG', 78);

/**
 * Interrupted system call should be restarted.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ERESTART', 85);

/**
 * Streams pipe error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESTRPIPE', 86);

/**
 * Too many users.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUSERS', 87);

/**
 * Socket operation on non-socket.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTSOCK', 88);

/**
 * Destination address required.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EDESTADDRREQ', 89);

/**
 * Message too long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMSGSIZE', 90);

/**
 * Protocol wrong type for socket.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTOTYPE', 91);
define ('SOCKET_ENOPROTOOPT', 92);

/**
 * Protocol not supported.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPROTONOSUPPORT', 93);

/**
 * Socket type not supported.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESOCKTNOSUPPORT', 94);

/**
 * Operation not supported on transport endpoint.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EOPNOTSUPP', 95);

/**
 * Protocol family not supported.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPFNOSUPPORT', 96);

/**
 * Address family not supported by protocol.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAFNOSUPPORT', 97);
define ('SOCKET_EADDRINUSE', 98);

/**
 * Cannot assign requested address.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EADDRNOTAVAIL', 99);

/**
 * Network is down.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENETDOWN', 100);

/**
 * Network is unreachable.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENETUNREACH', 101);

/**
 * Network dropped connection because of reset.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENETRESET', 102);

/**
 * Software caused connection abort.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECONNABORTED', 103);

/**
 * Connection reset by peer.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECONNRESET', 104);

/**
 * No buffer space available.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOBUFS', 105);

/**
 * Transport endpoint is already connected.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISCONN', 106);

/**
 * Transport endpoint is not connected.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTCONN', 107);

/**
 * Cannot send after transport endpoint shutdown.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESHUTDOWN', 108);

/**
 * Too many references: cannot splice.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETOOMANYREFS', 109);

/**
 * Connection timed out.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIMEDOUT', 110);

/**
 * Connection refused.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECONNREFUSED', 111);

/**
 * Host is down.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EHOSTDOWN', 112);

/**
 * No route to host.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EHOSTUNREACH', 113);

/**
 * Operation already in progress.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EALREADY', 114);

/**
 * Operation now in progress.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINPROGRESS', 115);

/**
 * Is a named type file.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISNAM', 120);

/**
 * Remote I/O error.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMOTEIO', 121);

/**
 * Quota exceeded.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EDQUOT', 122);

/**
 * No medium found.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEDIUM', 123);

/**
 * Wrong medium type.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMEDIUMTYPE', 124);
define ('IPPROTO_IP', 0);
define ('IPPROTO_IPV6', 41);
define ('SOL_TCP', 6);
define ('SOL_UDP', 17);
define ('IPV6_UNICAST_HOPS', 16);
define ('IPV6_RECVPKTINFO', 49);
define ('IPV6_PKTINFO', 50);
define ('IPV6_RECVHOPLIMIT', 51);
define ('IPV6_HOPLIMIT', 52);
define ('IPV6_RECVTCLASS', 66);
define ('IPV6_TCLASS', 67);
define ('SCM_RIGHTS', 1);
define ('SCM_CREDENTIALS', 2);
define ('SO_PASSCRED', 16);
```
