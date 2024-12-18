# Http\Server

?> `Http\Server`继承自[Server](/server/init)，所以`Server`提供的所有`API`和配置项都可以使用，进程模型也是一致的。请参考[Server](/server/init)章节。

内置`HTTP`服务器的支持，通过几行代码即可写出一个高并发，高性能，[异步IO](/learn?id=同步io异步io)的多进程`HTTP`服务器。

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

通过使用`Apache bench`工具进行压力测试，在`Inter Core-I5 4核 + 8G内存`的普通PC机器上，`Http\Server`可以达到近`11万QPS`。

远远超过`PHP-FPM`、`Golang`、`Node.js`自带`Http`服务器。性能几乎接近与`Nginx`的静态文件处理。

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **使用 HTTP2 协议**

  * 使用`SSL`下的`HTTP2`协议必须安装`openssl`, 且需要高版本`openssl`必须支持`TLS1.2`、`ALPN`、`NPN`
  * 编译时需要使用[--enable-http2](/environment?id=编译选项)开启
  * 从Swoole5开始，默认启用http2协议

```shell
./configure --enable-openssl --enable-http2
```

设置`HTTP`服务器的[open_http2_protocol](/http_server?id=open_http2_protocol)为`true`

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Nginx + Swoole 配置**

!> 由于`Http\Server`对`HTTP`协议的支持并不完整，建议仅作为应用服务器，用于处理动态请求，并且在前端增加`Nginx`作为代理。

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> 可以通过读取`$request->header['x-real-ip']`来获取客户端的真实`IP`

## 方法

### on()

?> **注册事件回调函数。**

?> 与 [Server的回调](/server/events) 相同，不同之处是：

  * `Http\Server->on`不接受[onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)回调设置
  * `Http\Server->on`额外接受1种新的事件类型`onRequest`，客户端发来的请求就在`Request`事件执行

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

在收到一个完整的HTTP请求后，会回调此函数。回调函数共有`2`个参数：

* [Swoole\Http\Request](/http_server?id=httpRequest)，`HTTP`请求信息对象，包含了`header/get/post/cookie`等相关信息
* [Swoole\Http\Response](/http_server?id=httpResponse)，`HTTP`响应对象，支持`cookie/header/status`等`HTTP`操作

!> 在[onRequest](/http_server?id=on)回调函数返回时底层会销毁`$request`和`$response`对象

### start()

?> **启动HTTP服务器**

?> 启动后开始监听端口，并接收新的`HTTP`请求。

```php
Swoole\Http\Server->start();
```

## Swoole\Http\Request

`HTTP`请求对象，保存了`HTTP`客户端请求的相关信息，包括`GET`、`POST`、`COOKIE`、`Header`等。

!> 请勿使用`&`符号引用`Http\Request`对象

### header

?> **`HTTP`请求的头部信息。类型为数组，所有`key`均为小写。**

```php
Swoole\Http\Request->header: array
```

* **示例**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```

### server

?> **`HTTP`请求相关的服务器信息。**

?> 相当于`PHP`的`$_SERVER`数组。包含了`HTTP`请求的方法，`URL`路径，客户端`IP`等信息。

```php
Swoole\Http\Request->server: array
```

数组的`key`全部为小写，并且与`PHP`的`$_SERVER`数组保持一致

* **示例**

```php
echo $request->server['request_time'];
```

key | 说明
---|---
query_string | 请求的 `GET` 参数，如：`id=1&cid=2` 如果没有 `GET` 参数，该项不存在
request_method | 请求方法，`GET/POST`等
request_uri | 无 `GET` 参数的访问地址，如`/favicon.ico`
path_info | 同 `request_uri`
request_time | `request_time`是在`Worker`设置的，在[SWOOLE_PROCESS](/learn?id=swoole_process)模式下存在`dispatch`过程，因此可能会与实际收包时间存在偏差。尤其是当请求量超过服务器处理能力时，`request_time`可能远滞后于实际收包时间。可以通过`$server->getClientInfo`方法获取`last_time`获得准确的收包时间。
request_time_float | 请求开始的时间戳，以微秒为单位，`float`类型，如`1576220199.2725`
server_protocol | 服务器协议版本号，`HTTP` 是：`HTTP/1.0` 或 `HTTP/1.1`，`HTTP2` 是：`HTTP/2`
server_port | 服务器监听的端口
remote_port | 客户端的端口
remote_addr | 客户端的 `IP` 地址
master_time | 连接上次通讯时间

### get

?> **`HTTP`请求的`GET`参数，相当于`PHP`中的`$_GET`，格式为数组。**

```php
Swoole\Http\Request->get: array
```

* **示例**

```php
// 如：index.php?hello=123
echo $request->get['hello'];
// 获取所有GET参数
var_dump($request->get);
```

* **注意**

!> 为防止`HASH`攻击，`GET`参数最大不允许超过`128`个

### post

?> **`HTTP`请求的`POST`参数，格式为数组**

```php
Swoole\Http\Request->post: array
```

* **示例**

```php
echo $request->post['hello'];
```

* **注意**

!> -`POST`与`Header`加起来的尺寸不得超过[package_max_length](/server/setting?id=package_max_length)的设置，否则会认为是恶意请求  
-`POST`参数的个数最大不超过`128`个

### cookie

?> **`HTTP`请求携带的`COOKIE`信息，格式为键值对数组。**

```php
Swoole\Http\Request->cookie: array
```

* **示例**

```php
echo $request->cookie['username'];
```

### files

?> **上传文件信息。**

?> 类型为以`form`名称为`key`的二维数组。与`PHP`的`$_FILES`相同。最大文件尺寸不得超过[package_max_length](/server/setting?id=package_max_length)设置的值。因为Swoole在解析报文的时候是会占用内存的，报文越大，内存占用越大，因此请勿使用`Swoole\Http\Server`处理大文件上传或者由用户自行设计断点续传的功能。

```php
Swoole\Http\Request->files: array
```

* **示例**

```php
Array
(
    [name] => facepalm.jpg // 浏览器上传时传入的文件名称
    [type] => image/jpeg // MIME类型
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // 上传的临时文件，文件名以/tmp/swoole.upfile开头
    [error] => 0
    [size] => 15476 // 文件尺寸
)
```

* **注意**

!> 当`Swoole\Http\Request`对象销毁时，会自动删除上传的临时文件
### getContent()

!> Swoole版本 >= `v4.5.0` 可用, 在低版本可使用别名`rawContent` (此别名将永久保留, 即向下兼容)

?> **获取原始的`POST`包体。**

?> 用于非`application/x-www-form-urlencoded`格式的HTTP `POST`请求。返回原始`POST`数据，此函数等同于`PHP`的`fopen('php://input')`

```php
Swoole\Http\Request->getContent(): string|false
```

  * **返回值**

    * 执行成功返回报文，如果上下文连接不存在返回`false`

!> 有些情况下服务器不需要解析HTTP `POST`请求参数，通过[http_parse_post](/http_server?id=http_parse_post) 配置，可以关闭`POST`数据解析。

### getData()

?> **获取完整的原始`Http`请求报文，注意`Http2`下无法使用。包括`Http Header`和`Http Body`**

```php
Swoole\Http\Request->getData(): string|false
```

  * **返回值**

    * 执行成功返回报文，如果上下文连接不存在或者在`Http2`模式下返回`false`

### create()

?> **创建一个`Swoole\Http\Request`对象。**

!> Swoole版本 >= `v4.6.0` 可用

```php
Swoole\Http\Request->create(array $options): Swoole\Http\Request
```

  * **参数**

    * **`array $options`**
      * **功能**：可选参数，用于设置 `Request` 对象的配置

| 参数                                              | 默认值 | 说明                                                                |
| ------------------------------------------------- | ------ | ----------------------------------------------------------------- |
| [parse_cookie](/http_server?id=http_parse_cookie) | true   | 设置是否解析`Cookie`                                                |
| [parse_body](/http_server?id=http_parse_post)      | true   | 设置是否解析`Http Body`                                             |
| [parse_files](/http_server?id=http_parse_files)   | true   | 设置上传文件解析开关                                                 |
| enable_compression                                | true，如果服务器不支持压缩报文，默认值为false   | 设置是否启用压缩                                                    |
| compression_level                                 | 1      | 设置压缩级别，范围是 1-9，等级越高压缩后的尺寸越小，但 CPU 消耗更多        |
| upload_tmp_dir                                 | /tmp      | 临时文件存储位置，文件上传用        |

  * **返回值**

    * 返回一个`Swoole\Http\Request`对象

* **示例**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```

### parse()

?> **解析`HTTP`请求数据包，会返回成功解析的数据包长度。**

!> Swoole版本 >= `v4.6.0` 可用

```php
Swoole\Http\Request->parse(string $data): int|false
```

  * **参数**

    * **`string $data`**
      * 要解析的报文

  * **返回值**

    * 解析成功返回解析的报文长度，连接上下文不存在或者上下文已经结束返回`false`

### isCompleted()

?> **获取当前的`HTTP`请求数据包是否已到达结尾。**

!> Swoole版本 >= `v4.6.0` 可用

```php
Swoole\Http\Request->isCompleted(): bool
```

  * **返回值**

    * `true`表示已经是结尾，`false`表示连接上下文已经结束或者未到结尾

* **示例**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n";
$data .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n";
$data .= "Accept-Encoding: gzip, deflate, br\r\n";
$data .= "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,ja;q=0.6\r\n";
$data .= "Cookie: env=pretest; phpsessid=fcccs2af8673a2f343a61a96551c8523d79ea; username=hantianfeng\r\n";

/** @var Request $req */
$req = Request::create(['parse_cookie' => false]);
var_dump($req);

var_dump($req->isCompleted());
var_dump($req->parse($data));

var_dump($req->parse("\r\n"));
var_dump($req->isCompleted());

var_dump($req);
// 关闭了解析cookie，所以会是null
var_dump($req->cookie);
```

### getMethod()

?> **获取当前的`HTTP`请求的请求方式。**

!> Swoole版本 >= `v4.6.2` 可用

```php
Swoole\Http\Request->getMethod(): string|false
```
  * **返回值**

    * 成返回大写的请求方式，`false`表示连接上下文不存在

```php
var_dump($request->server['request_method']);
var_dump($request->getMethod());
```

## Swoole\Http\Response

`HTTP`响应对象，通过调用此对象的方法，实现`HTTP`响应发送。

?> 当`Response`对象销毁时，如果未调用[end](/http_server?id=end)发送`HTTP`响应，底层会自动执行`end("")`;

!> 请勿使用`&`符号引用`Http\Response`对象

### header() :id=setheader

?> **设置HTTP响应的Header信息**【别名`setHeader`】

```php
Swoole\Http\Response->header(string $key, string $value, bool $format = true): bool;
```

* **参数** 

  * **`string $key`**
    * **功能**：`HTTP`头的`Key`
    * **默认值**：无
    * **其它值**：无

  * **`string $value`**
    * **功能**：`HTTP`头的`value`
    * **默认值**：无
    * **其它值**：无

  * **`bool $format`**
    * **功能**：是否需要对`Key`进行`HTTP`约定格式化【默认`true`会自动格式化】
    * **默认值**：`true`
    * **其它值**：无

* **返回值** 

  * 设置失败，返回`false`
  * 设置成功，返回`true`
* **注意**

   -`header`设置必须在`end`方法之前
   -`$key`必须完全符合`HTTP`的约定，每个单词首字母大写，不得包含中文，下划线或者其他特殊字符  
   -`$value`必须填写  
   -`$ucwords` 设为 `true`，底层会自动对`$key`进行约定格式化  
   -重复设置相同`$key`的`HTTP`头会覆盖，取最后一次  
   -如果客户端设置了`Accept-Encoding`，那么服务端不能设置`Content-Length`响应, `Swoole`检测到这种情况会忽略`Content-Length`的值，并且抛出一个警告   
   -设置了`Content-Length`响应不能调用`Swoole\Http\Response::write()`，`Swoole`检测到这种情况会忽略`Content-Length`的值，并且抛出一个警告

!> Swoole 版本 >= `v4.6.0`时，支持重复设置相同`$key`的`HTTP`头，并且`$value`支持多种类型，如`array`、`object`、`int`、`float`，底层会进行`toString`转换，并且会移除末尾的空格以及换行。

* **示例**

```php
$response->header('content-type', 'image/jpeg', true);

$response->header('Content-Length', '100002 ');
$response->header('Test-Value', [
    "a\r\n",
    'd5678',
    "e  \n ",
    null,
    5678,
    3.1415926,
]);
$response->header('Foo', new SplFileInfo('bar'));
```
### trailer()

?> **將`Header`資訊附加到`HTTP`響應的末尾，僅在`HTTP2`中可用，用於消息完整性檢查，數位簽名等。**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **參數** 

  * **`string $key`**
    * **功能**：`HTTP`頭的`Key`
    * **預設值**：無
    * **其它值**：無

  * **`string $value`**
    * **功能**：`HTTP`頭的`value`
    * **預設值**：無
    * **其它值**：無

* **返回值** 

  * 設置失敗，返回`false`
  * 設置成功，返回`true`

* **注意**

  !> 重複設置相同`$key`的`Http`頭會覆蓋，取最後一次。

* **示例**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```

### cookie()

?> **設定`HTTP`響應的`cookie`資訊。別名`setCookie`。此方法參數與`PHP`的`setcookie`一致。**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **參數** 

    * **`string $key`**
      * **功能**：`Cookie`的`Key`
      * **預設值**：無
      * **其它值**：無

    * **`string $value`**
      * **功能**：`Cookie`的`value`
      * **預設值**：無
      * **其它值**：無
  
    * **`int $expire`**
      * **功能**：`Cookie`的`過期時間`
      * **預設值**：0，不过期
      * **其它值**：無

    * **`string $path`**
      * **功能**：`規定 Cookie 的服务器路徑。`
      * **預設值**：/
      * **其它值**：無

    * **`string $domain`**
      * **功能**：`規定 Cookie 的域名`
      * **預設值**：''
      * **其它值**：無

    * **`bool $secure`**
      * **功能**：`規定是否通過安全的 HTTPS 連接來傳輸 Cookie`
      * **預設值**：''
      * **其它值**：無

    * **`bool $httponly`**
      * **功能**：`是否允許瀏覽器的JavaScript訪問帶有 HttpOnly 属性的 Cookie`，`true`表示不允許，`false`表示允許
      * **預設值**：false
      * **其它值**：無

    * **`string $samesite`**
      * **功能**：`限制第三方 Cookie，從而減少安全風險`，可選值為`Strict`，`Lax`，`None`
      * **預設值**：''
      * **其它值**：無

    * **`string $priority`**
      * **功能**：`Cookie優先級，當Cookie數量超過規定，低優先級的會先被刪除`，可選值為`Low`，`Medium`，`High`
      * **預設值**：''
      * **其它值**：無
  
  * **返回值** 

    * 設置失敗，返回`false`
    * 設置成功，返回`true`

* **注意**

  !> -`cookie`設定必須在[end](/http_server?id=end)方法之前  
  -`$samesite` 參數從 `v4.4.6` 版本開始支持，`$priority` 參數從 `v4.5.8` 版本開始支持  
  -`Swoole`會自動對`$value`進行`urlencode`編碼，可使用`rawCookie()`方法關閉對`$value`的編碼處理  
  -`Swoole`允許設置多個相同`$key`的`COOKIE`

### rawCookie()

?> **設定`HTTP`響應的`cookie`資訊**

!> `rawCookie()`的參數和上文的`cookie()`一致，只不過不進行編碼處理

### status()

?> **發送`Http`狀態碼。別名`setStatusCode()`**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **參數** 

  * **`int $http_status_code`**
    * **功能**：設置 `HttpCode`
    * **預設值**：無
    * **其它值**：無

  * **`string $reason`**
    * **功能**：狀態碼原因
    * **預設值**：''
    * **其它值**：無

  * **返回值** 

    * 設置失敗，返回`false`
    * 設置成功，返回`true`

* **提示**

  * 如果只傳入了第一個參數 `$http_status_code`必須為合法的`HttpCode`，如`200`、`502`、`301`、`404`等，否則會設置為`200`狀態碼
  * 如果設置了第二個參數`$reason`，`$http_status_code`可以為任意的數值，包括未定義的`HttpCode`，如`499`
  * 必須在 [$response->end()](/http_server?id=end) 之前執行`status`方法

### gzip()

!> 此方法在`4.1.0`或更高版本中已廢止, 請移步[http_compression](/http_server?id=http_compression)；在新版本中使用`http_compression`配置項取代了`gzip`方法。  
主要原因是`gzip()`方法未判斷瀏覽器客戶端傳入的`Accept-Encoding`頭，如果客戶端不支持`gzip`壓縮，強行使用會導致客戶端無法解壓。  
全新的`http_compression`配置項會根據客戶端`Accept-Encoding`頭，自動選擇是否壓縮，並自動選擇最佳的壓縮算法。

?> **啟用`Http GZIP`壓縮。壓縮可以減小`HTML`內容的尺寸，有效節省網絡帶寬，提高響應時間。必須在`write/end`發送內容之前執行`gzip`，否則會拋出錯誤。**
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **參數** 
   
     * **`int $level`**
       * **功能**：壓縮等級，等級越高壓縮後的尺寸越小，但`CPU`消耗更多。
       * **預設值**：1
       * **其它值**：`1-9`

!> 調用`gzip`方法後，底層會自動添加`Http`編碼頭，PHP代碼中不應再行設置相關`Http`頭；`jpg/png/gif`格式的圖片已經經過壓縮，無需再次壓縮

!> `gzip`功能依賴`zlib`庫，在編譯swoole時底層會檢測系統是否存在`zlib`，如果不存在，`gzip`方法將不可用。可以使用`yum`或`apt-get`安裝`zlib`庫：

```shell
sudo apt-get install libz-dev
```

### redirect()

?> **發送`Http`轉向。調用此方法會自動`end`發送並結束響應。**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **參數** 
* **參數** 
  * **參數** 
  * **參數** 

    * **`string $url`**
      * **功能**：轉向的新地址，作為`Location`頭進行發送
      * **預設值**：無
      * **其它值**：無

    * **`int $http_code`**
      * **功能**：狀態碼【默認為`302`臨時轉向，傳入`301`表示永久轉向】
      * **預設值**：`302`
      * **其它值**：無

  * **返回值** 

    * 調用成功，返回`true`，調用失敗或連接上下文不存在，返回`false`

* **示例**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```
### write()

?> **啟用`Http Chunk`分段向瀏覽器發送相應內容。**

?> 關於`Http Chunk`可參考`Http`協議標準文檔。

```php
Swoole\Http\Response->write(string $data): bool
```

  * **參數** 

    * **`string $data`**
      * **功能**：要發送的數據內容【最大長度不得超過`2M`，受[buffer_output_size](/server/setting?id=buffer_output_size)配置項控制】
      * **默認值**：無
      * **其它值**：無

  * **返回值** 
  
    * 調用成功，返回`true`，調用失敗或連接上下文不存在，返回`false`

* **提示**

  * 使用`write`分段發送數據後，[end](/http_server?id=end)方法將不接受任何參數，調用`end`只是會發送一個長度為`0`的`Chunk`表示數據傳輸完畢
  * 如果通過Swoole\Http\Response::header()方法設置了`Content-Length`，然後又調用這個方法，`Swoole`會忽略`Content-Length`的設置，並拋出一個警告
  * `Http2`不能使用這個函數，否則會拋出一個警告
  * 如果客戶端支持響應壓縮，`Swoole\Http\Response::write()`會強制關閉壓縮


### sendfile()

?> **將文件發送到瀏覽器。**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **參數** 

    * **`string $filename`**
      * **功能**：要發送的文件名稱【文件不存在或沒有訪問權限`sendfile`會失敗】
      * **默認值**：無
      * **其它值**：無

    * **`int $offset`**
      * **功能**：上傳文件的偏移量【可以指定從文件的中间部分開始傳輸數據。此特性可用於支持斷點續傳】
      * **默認值**：`0`
      * **其它值**：無

    * **`int $length`**
      * **功能**：發送數據的尺寸
      * **默認值**：文件的尺寸
      * **其它值**：無

  * **返回值** 

      * 調用成功，返回`true`，調用失敗或連接上下文不存在，返回`false`

* **提示**

  * 底层無法推測要發送文件的MIME格式因此需要應用代碼指定`Content-Type`
  * 調用`sendfile`前不得使用`write`方法發送`Http-Chunk`
  * 調用`sendfile`後底层會自動執行`end`
  * `sendfile`不支持`gzip`壓縮

* **示例**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```


### end()

?> **發送`Http`響應體，並結束請求處理。**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **參數** 
  
    * **`string $html`**
      * **功能**：要發送的內容
      * **默認值**：無
      * **其它值**：無

  * **返回值** 

    * 調用成功，返回`true`，調用失敗或連接上下文不存在，返回`false`

* **提示**

  * `end`只能調用一次，如果需要分多次向客戶端發送數據，請使用[write](/http_server?id=write)方法
  * 客戶端開啟了[KeepAlive](/coroutine_client/http_client?id=keep_alive)，連接將會保持，服務器會等待下一次請求
  * 客戶端未開啟`KeepAlive`，服務器將會切斷連接
  * `end`要發送的內容，由於受到[output_buffer_size](/server/setting?id=buffer_output_size)的限制，默認為`2M`，如果大於這個限制則會響應失敗，並拋出如下錯誤：

!> 解決方法為：使用[sendfile](/http_server?id=sendfile)、[write](/http_server?id=write)或調整[output_buffer_size](/server/setting?id=buffer_output_size)

```bash
WARNING finish (ERRNO 1203): The length of data [262144] exceeds the output buffer size[131072], please use the sendfile, chunked transfer mode or adjust the output_buffer_size
```


### detach()

?> **分離響應對象。**使用此方法後，`$response`對象銷毀時不會自動[end](/http_server?id=httpresponse)，與 [Http\Response::create](/http_server?id=create) 和 [Server->send](/server/methods?id=send) 配合使用。

```php
Swoole\Http\Response->detach(): bool
```

  * **返回值** 

    * 調用成功，返回`true`，調用失敗或連接上下文不存在，返回`false`

* **示例** 

  * **跨進程響應**

  ?> 某些情況下，需要在 [Task進程](/learn?id=taskworker進程)中對客戶端發出響應。這時可以利用`detach`使`$response`對象獨立。在 [Task進程](/learn?id=taskworker進程)可以重新構建`$response`，發起`Http`請求響應。 

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->set(['task_worker_num' => 1, 'worker_num' => 1]);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->task(strval($resp->fd));
  });

  $http->on('finish', function () {
      echo "task finish";
  });

  $http->on('task', function ($serv, $task_id, $worker_id, $data) {
      var_dump($data);
      $resp = Swoole\Http\Response::create($data);
      $resp->end("in task");
      echo "async task\n";
  });

  $http->start();
  ```

  * **發送任意內容**

  ?> 某些特殊的場景下，需要對客戶端發送特殊的響應內容。`Http\Response`對象自帶的`end`方法無法滿足需求，可以使用`detach`分離響應對象，然後自行組裝HTTP協議響應數據，並使用`Server->send`發送數據。

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```


### create()

?> **構建新的`Swoole\Http\Response`對象。**

!> 使用此方法前務必調用`detach`方法將舊的`$response`對象分離，否則可能會造成對同一個請求發送兩次響應內容。

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

  * **參數** 

    * **`int $server`**
      * **功能**：`Swoole\Server`或者`Swoole\Coroutine\Socket`對象，數組（數組只能有兩個參數，第一個是`Swoole\Server`對象，第二個是`Swoole\Http\Request`對象），或者文件描述符
      * **默認值**：-1
      * **其它值**：無

    * **`int $fd`**
      * **功能**：文件描述符。如果參數`$server`是`Swoole\Server`對象，`$fd`是必填的
      * **默認值**：-1
      * 
      * **其它值**：無

  * **返回值** 

    * 調用成功返回一個新的`Swoole\Http\Response`對象，調用失敗返回`false`

* **示例**

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->detach();
    // 示例1
    $resp2 = Swoole\Http\Response::create($req->fd);
    // 示例2
    $resp2 = Swoole\Http\Response::create($http, $req->fd);
    // 示例3
    $resp2 = Swoole\Http\Response::create([$http, $req]);
    // 示例4
    $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, IPPROTO_IP);
    $socket->connect('127.0.0.1', 9501)
    $resp2 = Swoole\Http\Response::create($socket);
    $resp2->end("hello world");
});

$http->start();
```
### isWritable()

?> **判斷`Swoole\Http\Response`物件是否已結束(`end`)或已分離(`detach`)。**

```php
Swoole\Http\Response->isWritable(): bool
```

  * **回傳值** 

    * `Swoole\Http\Response`物件未結束或者未分離回傳`true`，否則回傳`false`


!> Swoole版本 >= `v4.6.0` 可用

* **範例**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->isWritable()); // true
    $resp->end('hello');
    var_dump($resp->isWritable()); // false
    $resp->setStatusCode(403); // http response is unavailable (maybe it has been ended or detached)
});

$http->start();
```


## 設定選項


### http_parse_cookie

?> **針對`Swoole\Http\Request`物件的設定，關閉`Cookie`解析，將在`header`中保留未經處理的原始的`Cookies`資訊。預設開啟**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```


### http_parse_post

?> **針對`Swoole\Http\Request`物件的設定，設定POST消息解析開關，預設開啟**

* 設定為`true`時自動將`Content-Type為x-www-form-urlencoded`的請求包體解析到`POST`數組。
* 設定為`false`時將關閉`POST`解析。

```php
$server->set([
    'http_parse_post' => false,
]);
```


### http_parse_files

?> **針對`Swoole\Http\Request`物件的設定，設定上傳檔案解析開關。預設開啟**

```php
$server->set([
    'http_parse_files' => false,
]);
```


### http_compression

?> **針對`Swoole\Http\Response`物件的設定，啟用壓縮。默認為開啟。**


!> -`http-chunk`不支援分段單獨壓縮, 若使用[write](/http_server?id=write)方法, 將會強迫關閉壓縮。  
-`http_compression`在`v4.1.0`或更高版本可用

```php
$server->set([
    'http_compression' => false,
]);
```

目前支援`gzip`、`br`、`deflate` 三種壓縮格式，底層會根據瀏覽器客戶端傳入的`Accept-Encoding`頭自動選擇壓縮方式（壓縮算法優先級：`br` > `gzip` > `deflate` ）。

**依賴：**

`gzip`和`deflate`依賴`zlib`庫，在編譯`Swoole`時底層會檢測系統是否存在`zlib`。

可以使用`yum`或`apt-get`安裝`zlib`庫：

```shell
sudo apt-get install libz-dev
```

`br`壓縮格式依賴`google`的 `brotli`庫，安裝方式請自行搜索`install brotli on linux`，在編譯`Swoole`時底層會檢測系統是否存在`brotli`。


### http_compression_level / compression_level / http_gzip_level

?> **壓縮等級，針對`Swoole\Http\Response`物件的設定**
  
!> `$level` 壓縮等級，範圍是`1-9`，等級越高壓縮後的尺寸越小，但`CPU`消耗更多。默認為`1`, 最高為`9`



### http_compression_min_length / compression_min_length

?> **設定開啟壓縮的最小字節，針對`Swoole\Http\Response`物件的設定，超過該選項值才開啟壓縮。預設20字節。**

!> Swoole版本 >= `v4.6.3` 可用

```php
$server->set([
    'compression_min_length' => 128,
]);
```


### upload_tmp_dir

?> **設定上傳檔案的臨時目錄。目錄最大長度不得超過`220`字節**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```


### upload_max_filesize

?> **設定上傳檔案的最大值**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```


### enable_static_handler

開啟靜態檔案請求處理功能, 需配合`document_root`使用 預設`false`



### http_autoindex

開啟`http autoindex`功能 預設不開啟


### http_index_files

配合`http_autoindex`使用，指定需要被索引的檔案列表

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```


### http_compression_types / compression_types

?> **設定需要壓縮的響應類型，針對`Swoole\Http\Response`物件的設定**

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Swoole版本 >= `v4.8.12` 可用



### static_handler_locations

?> **設定靜態處理器的路徑。類型為數組，預設不啟用。**

!> Swoole版本 >= `v4.4.0` 可用

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* 類似於`Nginx`的`location`指令，可以指定一個或多個路徑為靜態路徑。只有`URL`在指定路徑下才會啟用靜態檔案處理器，否則會視為動態請求。
* `location`項必須以/開頭
* 支援多級路徑，如`/app/images`
* 啟用`static_handler_locations`後，如果請求對應的檔案不存在，將直接回傳404錯誤


### open_http2_protocol

?> **啟用`HTTP2`協定解析**【預設值：`false`】

!> 需要編譯時啟用 [--enable-http2](/environment?id=編譯選項) 選項，`Swoole5`開始預設編譯http2。


### document_root

?> **配置靜態檔案根目錄，與`enable_static_handler`配合使用。** 

!> 此功能較為簡陋, 請勿在公網環境直接使用

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // v4.4.0以下版本, 此處必須為絕對路徑
    'enable_static_handler' => true,
]);
```

* 設定`document_root`並設定`enable_static_handler`為`true`後，底層收到`Http`請求會先判斷document_root路徑下是否存在此檔案，如果存在會直接發送檔案內容給客戶端，不再觸發[onRequest](/http_server?id=on)回調。
* 使用靜態檔案處理特性時，應將動態PHP程式碼和靜態檔案進行隔離，靜態檔存放到特定的目錄


### max_concurrency

?> **可限制 `HTTP1/2` 服務的最大並發請求數量，超過之後回傳 `503` 錯誤，預設值為4294967295，即为無符號int的最大值**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```


### worker_max_concurrency

?> **開啟一鍵協程化之後，`worker`進程會源源不斷地接受請求，為了避免壓力過大，我們可以設定`worker_max_concurrency`限制`worker`進程的請求執行數，當請求數超過該值時，`worker`進程會將多余的請求暫存於隊列，預設值為4294967295，即为無符號int的最大值。如果沒有設定`worker_max_concurrency`，但是設定了`max_concurrency`的話，底層會自動設定`worker_max_concurrency`等於`max_concurrency`**

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Swoole版本 >= `v5.0.0` 可用
### http2_header_table_size

?> 定義HTTP/2網絡連接的最大`header table`大小。

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```

### http2_enable_push

?> 此配置用於啟用或禁用HTTP2推動。

```php
$server->set([
  'http2_enable_push' => 0x2
])
```

### http2_max_concurrent_streams

?> 設定每個HTTP/2網絡連接中接受的多路復用流的最大數量。

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```

### http2_init_window_size

?> 設定HTTP/2流量控制窗口的初始化大小。

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```

### http2_max_frame_size

?> 設定通過HTTP/2網絡連接發送的單個HTTP/2協議幀的主體的最大大小。

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```

### http2_max_header_list_size

?> 設定在HTTP/2流上的請求中可以發送的頭的最大大小。 

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
