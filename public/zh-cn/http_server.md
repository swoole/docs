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

?> **将`Header`信息附加到`HTTP`响应的末尾，仅在`HTTP2`中可用，用于消息完整性检查，数字签名等。**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
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

* **返回值** 

  * 设置失败，返回`false`
  * 设置成功，返回`true`

* **注意**

  !> 重复设置相同`$key`的`Http`头会覆盖，取最后一次。

* **示例**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```

### cookie()

?> **设置`HTTP`响应的`cookie`信息。别名`setCookie`。此方法参数与`PHP`的`setcookie`一致。**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **参数** 

    * **`string $key`**
      * **功能**：`Cookie`的`Key`
      * **默认值**：无
      * **其它值**：无

    * **`string $value`**
      * **功能**：`Cookie`的`value`
      * **默认值**：无
      * **其它值**：无
  
    * **`int $expire`**
      * **功能**：`Cookie`的`过期时间`
      * **默认值**：0，不过期
      * **其它值**：无

    * **`string $path`**
      * **功能**：`规定 Cookie 的服务器路径。`
      * **默认值**：/
      * **其它值**：无

    * **`string $domain`**
      * **功能**：`规定 Cookie 的域名`
      * **默认值**：''
      * **其它值**：无

    * **`bool $secure`**
      * **功能**：`规定是否通过安全的 HTTPS 连接来传输 Cookie`
      * **默认值**：''
      * **其它值**：无

    * **`bool $httponly`**
      * **功能**：`是否允许浏览器的JavaScript访问带有 HttpOnly 属性的 Cookie`，`true`表示不允许，`false`表示允许
      * **默认值**：false
      * **其它值**：无

    * **`string $samesite`**
      * **功能**：`限制第三方 Cookie，从而减少安全风险`，可选值为`Strict`，`Lax`，`None`
      * **默认值**：''
      * **其它值**：无

    * **`string $priority`**
      * **功能**：`Cookie优先级，当Cookie数量超过规定，低优先级的会先被删除`，可选值为`Low`，`Medium`，`High`
      * **默认值**：''
      * **其它值**：无
  
  * **返回值** 

    * 设置失败，返回`false`
    * 设置成功，返回`true`

* **注意**

  !> -`cookie`设置必须在[end](/http_server?id=end)方法之前  
  -`$samesite` 参数从 `v4.4.6` 版本开始支持，`$priority` 参数从 `v4.5.8` 版本开始支持  
  -`Swoole`会自动会对`$value`进行`urlencode`编码，可使用`rawCookie()`方法关闭对`$value`的编码处理  
  -`Swoole`允许设置多个相同`$key`的`COOKIE`

### rawCookie()

?> **设置`HTTP`响应的`cookie`信息**

!> `rawCookie()`的参数和上文的`cookie()`一致，只不过不进行编码处理

### status()

?> **发送`Http`状态码。别名`setStatusCode()`**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **参数** 

  * **`int $http_status_code`**
    * **功能**：设置 `HttpCode`
    * **默认值**：无
    * **其它值**：无

  * **`string $reason`**
    * **功能**：状态码原因
    * **默认值**：''
    * **其它值**：无

  * **返回值** 

    * 设置失败，返回`false`
    * 设置成功，返回`true`

* **提示**

  * 如果只传入了第一个参数 `$http_status_code`必须为合法的`HttpCode`，如`200`、`502`、`301`、`404`等，否则会设置为`200`状态码
  * 如果设置了第二个参数`$reason`，`$http_status_code`可以为任意的数值，包括未定义的`HttpCode`，如`499`
  * 必须在 [$response->end()](/http_server?id=end) 之前执行`status`方法

### gzip()

!> 此方法在`4.1.0`或更高版本中已废弃, 请移步[http_compression](/http_server?id=http_compression)；在新版本中使用`http_compression`配置项取代了`gzip`方法。  
主要原因是`gzip()`方法未判断浏览器客户端传入的`Accept-Encoding`头，如果客户端不支持`gzip`压缩，强行使用会导致客户端无法解压。  
全新的`http_compression`配置项会根据客户端`Accept-Encoding`头，自动选择是否压缩，并自动选择最佳的压缩算法。

?> **启用`Http GZIP`压缩。压缩可以减小`HTML`内容的尺寸，有效节省网络带宽，提高响应时间。必须在`write/end`发送内容之前执行`gzip`，否则会抛出错误。**
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **参数** 
   
     * **`int $level`**
       * **功能**：压缩等级，等级越高压缩后的尺寸越小，但`CPU`消耗更多。
       * **默认值**：1
       * **其它值**：`1-9`

!> 调用`gzip`方法后，底层会自动添加`Http`编码头，PHP代码中不应当再行设置相关`Http`头；`jpg/png/gif`格式的图片已经经过压缩，无需再次压缩

!> `gzip`功能依赖`zlib`库，在编译swoole时底层会检测系统是否存在`zlib`，如果不存在，`gzip`方法将不可用。可以使用`yum`或`apt-get`安装`zlib`库：

```shell
sudo apt-get install libz-dev
```

### redirect()

?> **发送`Http`跳转。调用此方法会自动`end`发送并结束响应。**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **参数** 
* **参数** 
  * **参数** 
* **参数** 
  * **参数** 

    * **`string $url`**
      * **功能**：跳转的新地址，作为`Location`头进行发送
      * **默认值**：无
      * **其它值**：无

    * **`int $http_code`**
      * **功能**：状态码【默认为`302`临时跳转，传入`301`表示永久跳转】
      * **默认值**：`302`
      * **其它值**：无

  * **返回值** 

    * 调用成功，返回`true`，调用失败或连接上下文不存在，返回`false`

* **示例**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```

### write()

?> **启用`Http Chunk`分段向浏览器发送相应内容。**

?> 关于`Http Chunk`可以参考`Http`协议标准文档。

```php
Swoole\Http\Response->write(string $data): bool
```

  * **参数** 

    * **`string $data`**
      * **功能**：要发送的数据内容【最大长度不得超过`2M`，受[buffer_output_size](/server/setting?id=buffer_output_size)配置项控制】
      * **默认值**：无
      * **其它值**：无

  * **返回值** 
  
    * 调用成功，返回`true`，调用失败或连接上下文不存在，返回`false`

* **提示**

  * 使用`write`分段发送数据后，[end](/http_server?id=end)方法将不接受任何参数，调用`end`只是会发送一个长度为`0`的`Chunk`表示数据传输完毕
  * 如果通过Swoole\Http\Response::header()方法设置了`Content-Length`，然后又调用这个方法，`Swoole`会忽略`Content-Length`的设置，并抛出一个警告
  * `Http2`不能使用这个函数，否则会抛出一个警告
  * 如果客户端支持响应压缩，`Swoole\Http\Response::write()`会强制关闭压缩

### sendfile()

?> **发送文件到浏览器。**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **参数** 

    * **`string $filename`**
      * **功能**：要发送的文件名称【文件不存在或没有访问权限`sendfile`会失败】
      * **默认值**：无
      * **其它值**：无

    * **`int $offset`**
      * **功能**：上传文件的偏移量【可以指定从文件的中间部分开始传输数据。此特性可用于支持断点续传】
      * **默认值**：`0`
      * **其它值**：无

    * **`int $length`**
      * **功能**：发送数据的尺寸
      * **默认值**：文件的尺寸
      * **其它值**：无

  * **返回值** 

      * 调用成功，返回`true`，调用失败或连接上下文不存在，返回`false`

* **提示**

  * 底层无法推断要发送文件的MIME格式因此需要应用代码指定`Content-Type`
  * 调用`sendfile`前不得使用`write`方法发送`Http-Chunk`
  * 调用`sendfile`后底层会自动执行`end`
  * `sendfile`不支持`gzip`压缩

* **示例**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```

### end()

?> **发送`Http`响应体，并结束请求处理。**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **参数** 
  
    * **`string $html`**
      * **功能**：要发送的内容
      * **默认值**：无
      * **其它值**：无

  * **返回值** 

    * 调用成功，返回`true`，调用失败或连接上下文不存在，返回`false`

* **提示**

  * `end`只能调用一次，如果需要分多次向客户端发送数据，请使用[write](/http_server?id=write)方法
  * 客户端开启了[KeepAlive](/coroutine_client/http_client?id=keep_alive)，连接将会保持，服务器会等待下一次请求
  * 客户端未开启`KeepAlive`，服务器将会切断连接
  * `end`要发送的内容，由于受到[output_buffer_size](/server/setting?id=buffer_output_size)的限制，默认为`2M`，如果大于这个限制则会响应失败，并抛出如下错误：

!> 解决方法为：使用[sendfile](/http_server?id=sendfile)、[write](/http_server?id=write)或调整[output_buffer_size](/server/setting?id=buffer_output_size)

```bash
WARNING finish (ERRNO 1203): The length of data [262144] exceeds the output buffer size[131072], please use the sendfile, chunked transfer mode or adjust the output_buffer_size
```

### detach()

?> **分离响应对象。**使用此方法后，`$response`对象销毁时不会自动[end](/http_server?id=httpresponse)，与 [Http\Response::create](/http_server?id=create) 和 [Server->send](/server/methods?id=send) 配合使用。

```php
Swoole\Http\Response->detach(): bool
```

  * **返回值** 

    * 调用成功，返回`true`，调用失败或连接上下文不存在，返回`false`

* **示例** 

  * **跨进程响应**

  ?> 某些情况下，需要在 [Task进程](/learn?id=taskworker进程)中对客户端发出响应。这时可以利用`detach`使`$response`对象独立。在 [Task进程](/learn?id=taskworker进程)可以重新构建`$response`，发起`Http`请求响应。 

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

  * **发送任意内容**

  ?> 某些特殊的场景下，需要对客户端发送特殊的响应内容。`Http\Response`对象自带的`end`方法无法满足需求，可以使用`detach`分离响应对象，然后自行组装HTTP协议响应数据，并使用`Server->send`发送数据。

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```

### create()

?> **构造新的`Swoole\Http\Response`对象。**

!> 使用此方法前请务必调用`detach`方法将旧的`$response`对象分离，否则可能会造成对同一个请求发送两次响应内容。

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

  * **参数** 

    * **`int $server`**
      * **功能**：`Swoole\Server`或者`Swoole\Coroutine\Socket`对象，数组（数组只能有两个参数，第一个是`Swoole\Server`对象，第二个是`Swoole\Http\Request`对象），或者文件描述符
      * **默认值**：-1
      * **其它值**：无

    * **`int $fd`**
      * **功能**：文件描述符。如果参数`$server`是`Swoole\Server`对象，`$fd`是必填的
      * **默认值**：-1
      * 
      * **其它值**：无

  * **返回值** 

    * 调用成功返回一个新的`Swoole\Http\Response`对象，调用失败返回`false`

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

?> **判断`Swoole\Http\Response`对象是否已结束(`end`)或已分离(`detach`)。**

```php
Swoole\Http\Response->isWritable(): bool
```

  * **返回值** 

    * `Swoole\Http\Response`对象未结束或者未分离返回`true`，否则返回`false`


!> Swoole版本 >= `v4.6.0` 可用

* **示例**

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

## 配置选项

### http_parse_cookie

?> **针对`Swoole\Http\Request`对象的配置，关闭`Cookie`解析，将在`header`中保留未经处理的原始的`Cookies`信息。默认开启**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```

### http_parse_post

?> **针对`Swoole\Http\Request`对象的配置，设置POST消息解析开关，默认开启**

* 设置为`true`时自动将`Content-Type为x-www-form-urlencoded`的请求包体解析到`POST`数组。
* 设置为`false`时将关闭`POST`解析。

```php
$server->set([
    'http_parse_post' => false,
]);
```

### http_parse_files

?> **针对`Swoole\Http\Request`对象的配置，设置上传文件解析开关。默认开启**

```php
$server->set([
    'http_parse_files' => false,
]);
```

### http_compression

?> **针对`Swoole\Http\Response`对象的配置，启用压缩。默认为开启。**

!> -`http-chunk`不支持分段单独压缩, 若使用[write](/http_server?id=write)方法, 将会强制关闭压缩。  
-`http_compression`在`v4.1.0`或更高版本可用

```php
$server->set([
    'http_compression' => false,
]);
```

目前支持`gzip`、`br`、`deflate` 三种压缩格式，底层会根据浏览器客户端传入的`Accept-Encoding`头自动选择压缩方式（压缩算法优先级：`br` > `gzip` > `deflate` ）。

**依赖：**

`gzip`和`deflate`依赖`zlib`库，在编译`Swoole`时底层会检测系统是否存在`zlib`。

可以使用`yum`或`apt-get`安装`zlib`库：

```shell
sudo apt-get install libz-dev
```

`br`压缩格式依赖`google`的 `brotli`库，安装方式请自行搜索`install brotli on linux`，在编译`Swoole`时底层会检测系统是否存在`brotli`。

### http_compression_level / compression_level / http_gzip_level

?> **压缩级别，针对`Swoole\Http\Response`对象的配置**
  
!> `$level` 压缩等级，范围是`1-9`，等级越高压缩后的尺寸越小，但`CPU`消耗更多。默认为`1`, 最高为`9`


### http_compression_min_length / compression_min_length

?> **设置开启压缩的最小字节，针对`Swoole\Http\Response`对象的配置，超过该选项值才开启压缩。默认20字节。**

!> Swoole版本 >= `v4.6.3` 可用

```php
$server->set([
    'compression_min_length' => 128,
]);
```

### upload_tmp_dir

?> **设置上传文件的临时目录。目录最大长度不得超过`220`字节**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```

### upload_max_filesize

?> **设置上传文件的最大值，设置该值后，上传文件大小大于`package_max_length`时程序不会报错，而是将会文件内容存储到临时文件中，不保存在内存中，方便大文件上传。**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```

### enable_static_handler

开启静态文件请求处理功能, 需配合`document_root`使用 默认`false`


### http_autoindex

开启`http autoindex`功能 默认不开启

### http_index_files

配合`http_autoindex`使用，指定需要被索引的文件列表

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```

### http_compression_types / compression_types

?> **设置需要压缩的响应类型，针对`Swoole\Http\Response`对象的配置**

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

?> **设置静态处理器的路径。类型为数组，默认不启用。**

!> Swoole版本 >= `v4.4.0` 可用

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* 类似于`Nginx`的`location`指令，可以指定一个或多个路径为静态路径。只有`URL`在指定路径下才会启用静态文件处理器，否则会视为动态请求。
* `location`项必须以/开头
* 支持多级路径，如`/app/images`
* 启用`static_handler_locations`后，如果请求对应的文件不存在，将直接返回404错误

### open_http2_protocol

?> **启用`HTTP2`协议解析**【默认值：`false`】

!> 需要编译时启用 [--enable-http2](/environment?id=编译选项) 选项，`Swoole5`开始默认编译http2。

### document_root

?> **配置静态文件根目录，与`enable_static_handler`配合使用。** 

!> 此功能较为简易, 请勿在公网环境直接使用

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // v4.4.0以下版本, 此处必须为绝对路径
    'enable_static_handler' => true,
]);
```

* 设置`document_root`并设置`enable_static_handler`为`true`后，底层收到`Http`请求会先判断document_root路径下是否存在此文件，如果存在会直接发送文件内容给客户端，不再触发[onRequest](/http_server?id=on)回调。
* 使用静态文件处理特性时，应当将动态PHP代码和静态文件进行隔离，静态文件存放到特定的目录

### max_concurrency

?> **可限制 `HTTP1/2` 服务的最大并发请求数量，超过之后返回 `503` 错误，默认值为4294967295，即为无符号int的最大值**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```

### worker_max_concurrency

?> **开启一键协程化之后，`worker`进程会源源不断的接受请求，为了避免压力过大，我们可以设置`worker_max_concurrency`限制`worker`进程的请求执行数，当请求数超过该值时，`worker`进程会将多余的请求暂存于队列，默认值为4294967295，即为无符号int的最大值。如果没有设置`worker_max_concurrency`，但是设置了`max_concurrency`的话，底层会自动设置`worker_max_concurrency`等于`max_concurrency`**

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Swoole版本 >= `v5.0.0` 可用

### http2_header_table_size

?> 定义HTTP/2网络连接的最大`header table`大小。

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```

### http2_enable_push

?> 该配置用于启用或禁用HTTP2推送。

```php
$server->set([
  'http2_enable_push' => 0x2
])
```

### http2_max_concurrent_streams

?> 设置每个HTTP/2网络连接中接受的多路复用流的最大数量。

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```

### http2_init_window_size

?> 设置HTTP/2流量控制窗口的初始化大小。

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```

### http2_max_frame_size

?> 设置通过HTTP/2网络连接发送的单个HTTP/2协议帧的主体的最大大小。

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```

### http2_max_header_list_size

?> 设置在HTTP/2流上的请求中可以发送的头的最大大小。 

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
