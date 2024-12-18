# 協程HTTP/WebSocket客戶端

協程版`HTTP`客戶端的底層用純`C`編寫，不依賴任何第三方擴展庫，擁有超高的性能。

* 支持`Http-Chunk`、`Keep-Alive`特性，支持`form-data`格式
* `HTTP`協議版本為`HTTP/1.1`
* 支持升級為`WebSocket`客戶端
* `gzip`壓縮格式支持需要依賴`zlib`庫
* 客戶端僅實現核心的功能，實際項目建議使用 [Saber](https://github.com/swlib/saber)

## 屬性

### errCode

錯誤狀態碼。當`connect/send/recv/close`失敗或者超時，會自動設置`Swoole\Coroutine\Http\Client->errCode`的值

```php
Swoole\Coroutine\Http\Client->errCode: int
```

`errCode`的值等於`Linux errno`。可使用`socket_strerror`將錯誤碼轉為錯誤信息。

```php
// 如果connect拒絕，錯誤碼為111
// 如果超時，錯誤碼為110
echo socket_strerror($client->errCode);
```

!> 參考：[Linux 錯誤碼列表](/other/errno?id=linux)

### body

存儲上次請求的返回包體。

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```

### statusCode

HTTP狀態碼，如200、404等。狀態碼如果為負數，表示連接存在問題。[查看更多](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```

## 方法

### __construct()

構造方法。

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

  * **參數** 

    * **`string $host`**
      * **功能**：目標服務器主機地址【可以為IP或域名，底層自動進行域名解析，若是本地UNIXSocket則應以形如`unix://tmp/your_file.sock`的格式填寫；若是域名不需要填寫協議頭`http://`或`https://`】
      * **默認值**：無
      * **其它值**：無

    * **`int $port`**
      * **功能**：目標服務器主機端口
      * **默認值**：無
      * **其它值**：無

    * **`bool $ssl`**
      * **功能**：是否啟用`SSL/TLS`隧道加密，如果目標服務器是https必須設置`$ssl`參數為`true`
      * **默認值**：`false`
      * **其它值**：無

  * **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

### set()

設置客戶端參數。

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

此方法與`Swoole\Client->set`接收的參數完全一致，可參考 [Swoole\Client->set](/client?id=set) 方法的文檔。

`Swoole\Coroutine\Http\Client` 額外增加了一些選項，來控制`HTTP`和`WebSocket`客戶端。

#### 額外選項

##### 超時控制

設置`timeout`選項，啟用HTTP請求超時檢測。單位為秒，最小粒度支持毫秒。

```php
$http->set(['timeout' => 3.0]);
```

* 連接超時或被服務器關閉連接，`statusCode`將設置為`-1`
* 在約定的時間內服務器未返回響應，請求超時，`statusCode`將設置為`-2`
* 請求超時後底層會自動切斷連接
* 參考[客戶端超時規則](/coroutine_client/init?id=超時規則)

##### keep_alive

設置`keep_alive`選項，啟用或關閉HTTP長連接。

```php
$http->set(['keep_alive' => false]);
```

##### websocket_mask

> 由於RFC規定, v4.4.0後此配置默認開啟, 但會導致性能損耗, 如服務器端無強制要求可以設置false關閉

`WebSocket`客戶端啟用或關閉掩碼。默認為啟用。啟用後會對WebSocket客戶端發送的數據使用掩碼進行數據轉換。

```php
$http->set(['websocket_mask' => false]);
```

##### websocket_compression

> 需要`v4.4.12`或更高版本

為`true`時**允許**對幀進行zlib壓縮，具體是否能夠壓縮取決於服務端是否能夠處理壓縮（根據握手信息決定，參見`RFC-7692`）

需要配合flags參數`SWOOLE_WEBSOCKET_FLAG_COMPRESS`來真正地對具體的某個幀進行壓縮，具體使用方法[見此節](/websocket_server?id=websocket幀壓縮-（rfc-7692）)

```php
$http->set(['websocket_compression' => true]);
```

##### write_func
> 需要`v5.1.0`或更高版本

設置`write_func`回調函數，類似於 `CURL` 的 `WRITE_FUNCTION` 選項，可以用於處理串流響應內容，
例如 `OpenAI ChatGPT` 的 `Event Stream` 輸出內容。

> 設置 `write_func` 之後，将無法使用 `getContent()` 方法獲取響應內容，並且 `$client->body` 也將為空  
> 在 `write_func` 回調函數中，可以使用 `$client->close()` 停止接收響應內容，並關閉連接

```php
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->set(['write_func' => function ($client, $data) {
    var_dump($data);
}]);
$cli->get('/');
```

### setMethod()

設置請求方法。僅在當前請求有效，發送請求後會立刻清除method設置。

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **參數** 

    * **`string $method`**
      * **功能**：設置方法 
      * **默認值**：無
      * **其它值**：無

      !> 必須為符合`HTTP`標準的方法名稱，如果`$method`設置錯誤可能會被`HTTP`服務器拒絕請求

  * **示例**

```php
$http->setMethod("PUT");
```

### setHeaders()

設置HTTP請求頭。

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **參數** 

    * **`array $headers`**
      * **功能**：設置請求頭 【必須為鍵值對應的數組，底層會自動映射為`$key`: `$value`格式的`HTTP`標準頭格式】
      * **默認值**：無
      * **其它值**：無

!> `setHeaders`設置的`HTTP`頭在`Coroutine\Http\Client`對象存活期間的每次請求永久有效；重新調用`setHeaders`會覆蓋上一次的設置

### setCookies()

設置`Cookie`, 值將會被進行`urlencode`編碼, 若想保持原始信息, 請自行用`setHeaders`設置名為`Cookie`的`header`。

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **參數** 

    * **`array $cookies`**
      * **功能**：設置 `COOKIE` 【必須為鍵值對應數組】
      * **默認值**：無
      * **其它值**：無
!> - 設置`COOKIE`後在客戶端對象存活期間會持續保存  

- 伺服器端主動設置的`COOKIE`會合併到`cookies`數組中，可讀取`$client->cookies`屬性獲得當前`HTTP`客戶端的`COOKIE`信息  
- 重複調用`setCookies`方法，會覆蓋當前的`Cookies`狀態，這會丟棄之前伺服器端下發的`COOKIE`以及之前主動設置的`COOKIE`


### setData()

設置HTTP請求的包體。

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **參數** 

    * **`string|array $data`**
      * **功能**：設置請求的包體
      * **默認值**：無
      * **其它值**：無

  * **提示**

    * 設置`$data`後並且未設置`$method`，底層會自動設置為POST
    * 如果`$data`為數組時且`Content-Type`為`urlencoded`格式, 底層將會自動進行`http_build_query`
    * 如果使用了`addFile`或`addData`導致啟用了`form-data`格式, `$data`值為字符串時將會被忽略(因為格式不同), 但為數組時底層將會以`form-data`格式追加數組中的字段


### addFile()

添加POST文件。

!> 使用`addFile`會自動將`POST`的`Content-Type`將變更為`form-data`。`addFile`底層基於`sendfile`，可支持異步發送超大文件。

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **參數** 

    * **`string $path`**
      * **功能**：文件的路徑【必選參數，不能為空文件或者不存在的文件】
      * **默認值**：無
      * **其它值**：無

    * **`string $name`**
      * **功能**：表單的名稱【必選參數，`FILES`參數中的`key`】
      * **默認值**：無
      * **其它值**：無

    * **`string $mimeType`**
      * **功能**：文件的`MIME`格式，【可選參數，底層會根據文件的擴展名自動推斷】
      * **默認值**：無
      * **其它值**：無

    * **`string $filename`**
      * **功能**：文件名稱【可選參數】
      * **默認值**：`basename($path)`
      * **其它值**：無

    * **`int $offset`**
      * **功能**：上傳文件的偏移量【可選參數，可以指定從文件的中間部分開始傳輸數據。此特性可用於支持斷點續傳。】
      * **默認值**：無
      * **其它值**：無

    * **`int $length`**
      * **功能**：發送數據的尺寸【可選參數】
      * **默認值**：默認為整個文件的尺寸
      * **其它值**：無

  * **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```


### addData()

使用字符串構建上傳文件內容。 

!> `addData`在 `v4.1.0` 以上版本可用

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **參數** 

    * **`string $data`**
      * **功能**：數據內容【必選參數，最大長度不得超過[buffer_output_size](/server/setting?id=buffer_output_size)】
      * **默認值**：無
      * **其它值**：無

    * **`string $name`**
      * **功能**：表單的名稱【必選參數，`$_FILES`參數中的`key`】
      * **默認值**：無
      * **其它值**：無

    * **`string $mimeType`**
      * **功能**：文件的`MIME`格式【可選參數，默認為`application/octet-stream`】
      * **默認值**：無
      * **其它值**：無

    * **`string $filename`**
      * **功能**：文件名稱【可選參數，默認為`$name`】
      * **默認值**：無
      * **其它值**：無

  * **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```


### get()

發起 GET 請求。

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **參數** 

    * **`string $path`**
      * **功能**：設置`URL`路徑【如`/index.html`，注意這裡不能傳入`http://domain`】
      * **默認值**：無
      * **其它值**：無

  * **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

!> 使用`get`會忽略`setMethod`設置的請求方法，強制使用`GET`


### post()

發起 POST 請求。

```php
Swoole\Coroutine\Http\Client->post(string $path, mixed $data): void
```

  * **參數** 

    * **`string $path`**
      * **功能**：設置`URL`路徑【如`/index.html`，注意這裡不能傳入`http://domain`】
      * **默認值**：無
      * **其它值**：無

    * **`mixed $data`**
      * **功能**：請求的包體數據
      * **默認值**：無
      * **其它值**：無

      !> 如果`$data`為數組底層自動會打包為`x-www-form-urlencoded`格式的`POST`內容，並設置`Content-Type`為`application/x-www-form-urlencoded`

  * **注意**

    !> 使用`post`會忽略`setMethod`設置的請求方法，強制使用`POST`

  * **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```


### upgrade()

升級為`WebSocket`連接。

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **參數** 

    * **`string $path`**
      * **功能**：設置`URL`路徑【如`/`，注意這裡不能傳入`http://domain`】
      * **默認值**：無
      * **其它值**：無

  * **提示**

    * 某些情況下請求雖然是成功的，`upgrade`返回了`true`，但伺服器並未設置`HTTP`狀態碼為`101`，而是`200`或`403`，這說明伺服器拒絕了握手請求
    * `WebSocket`握手成功後可以使用`push`方法向伺服器端推送消息，也可以調用`recv`接收消息
    * `upgrade`會產生一次[協程調度](/coroutine?id=協程調度)

  * **示例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### push()

向`WebSocket`伺服器推送消息。

!> `push`方法必須在`upgrade`成功之後才能執行  
`push`方法不會產生[協程調度](/coroutine?id=協程調度)，寫入發送緩存區後會立即返回

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **參數** 

    * **`mixed $data`**
      * **功能**：要發送的數據內容【默認為`UTF-8`文本格式，如果為其他格式編碼或二進制數據，請使用`WEBSOCKET_OPCODE_BINARY`】
      * **默認值**：無
      * **其它值**：無

      !> Swoole版本 >= v4.2.0 `$data` 可以使用 [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)對象, 支援發送各種幀類型

    * **`int $opcode`**
      * **功能**：操作類型
      * **默認值**：`WEBSOCKET_OPCODE_TEXT`
      * **其它值**：無

      !> `$opcode`必須為合法的`WebSocket OPCode`，否則會返回失敗，並打印錯誤信息`opcode max 10`

    * **`int|bool $finish`**
      * **功能**：操作類型
      * **默認值**：`SWOOLE_WEBSOCKET_FLAG_FIN`
      * **其它值**：無

      !> 自`v4.4.12`版本起，`finish`參數（`bool`型）改為`flags`（`int`型）以支援`WebSocket`壓縮，`finish`對應`SWOOLE_WEBSOCKET_FLAG_FIN`值為`1`，原有`bool`型值會隱式轉換為`int`型，此改動向下兼容無影響。此外壓縮`flag`為`SWOOLE_WEBSOCKET_FLAG_COMPRESS`。

  * **返回值**

    * 發送成功，返回`true`
    * 連接不存在、已關閉、未完成`WebSocket`，發送失敗返回`false`

  * **錯誤碼**


錯誤碼 | 說明
---|---
8502 | 錯誤的OPCode
8503 | 未連接到伺服器或連接已被關閉
8504 | 握手失敗


### recv()

接收消息。只為`WebSocket`使用，需要配合`upgrade()`使用，見示例

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **參數** 

    * **`float $timeout`**
      * **功能**：調用`upgrade()`升級為`WebSocket`連接時此參數才有效
      * **值單位**：秒【支援浮點型，如`1.5`表示`1s`+`500ms`】
      * **默認值**：參考[客戶端超時規則](/coroutine_client/init?id=超時規則)
      * **其它值**：無

      !> 設置超時，優先使用指定的參數，其次使用`set`方法中傳入的`timeout`配置
  
  * **返回值**

    * 執行成功返回frame對象
    * 失敗返回`false`，並檢查`Swoole\Coroutine\Http\Client`的`errCode`屬性，協程客戶端沒有`onClose`回調，連接被關閉recv時返回false並且errCode=0
 
  * **示例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```


### download()

通過HTTP下載文件。

!> download與get方法的不同是download收到數據後會寫入到磁碟，而不是在內存中對HTTP Body進行拼接。因此download僅使用小量內存，就可以完成超大文件的下載。

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename,  int $offset = 0): bool
```

  * **參數** 

    * **`string $path`**
      * **功能**：設置`URL`路徑
      * **默認值**：無
      * **其它值**：無

    * **`string $filename`**
      * **功能**：指定下載內容寫入的文件路徑【會自動寫入到`downloadFile`屬性】
      * **默認值**：無
      * **其它值**：無

    * **`int $offset`**
      * **功能**：指定寫入文件的偏移量【此選項可用於支援斷點續傳，可配合`HTTP`頭`Range:bytes=$offset`實現】
      * **默認值**：無
      * **其它值**：無

      !> `$offset`為`0`時若文件已存在，底層會自動清空此文件

  * **返回值**

    * 執行成功返回`true`
    * 打開文件失敗或底層`fseek()`文件失敗返回`false`

  * **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```


### getCookies()

獲取`HTTP`回應的`cookie`內容。

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> Cookie信息將經過urldecode解碼, 想要獲取原始Cookie信息請按照下文自行解析

#### 獲取重名`Cookie`或`Cookie`原始頭信息

```php
var_dump($client->set_cookie_headers);
```


### getHeaders()

返回`HTTP`回應的頭信息。

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```


### getStatusCode()

獲取`HTTP`回應的狀態碼。

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **提示**

    * **狀態碼如果為負數，表示連接存在問題。**


狀態碼 | v4.2.10 以上版本對應常量 | 說明

---|---|---

-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | 連接超時，伺服器未監聽端口或網絡丟失，可以讀取$errCode獲取具體的網絡錯誤碼

-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | 請求超時，伺服器未在規定的timeout時間內返回response

-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | 客戶端請求發出後，伺服器強制切斷連接
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | 客戶端發送失敗(此常量Swoole版本>=`v4.5.9`可用，小於此版本請使用狀態碼)


### getBody()

獲取`HTTP`回應的包體內容。

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```


### close()

關閉連接。

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> `close`後如果再次請求 `get`、`post` 等方法時，Swoole會幫你重新連接伺服器。


### execute()

更底層的`HTTP`請求方法，需要代碼中調用[setMethod](/coroutine_client/http_client?id=setmethod)和[setData](/coroutine_client/http_client?id=setdata)等接口設置請求的方法和數據。

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **示例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```
## 函數

為了方便 `Coroutine\Http\Client` 的使用，增加了三個函數：

!> Swoole版本 >= `v4.6.4` 可用


### request()

發起一個指定請求方式的請求。

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```


### post()

用於發起一個 `POST` 請求。

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```


### get()

用於發起一個 `GET` 請求。

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```

### 使用示例

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
