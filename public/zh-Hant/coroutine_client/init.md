# 協程客戶端 <!-- {docsify-ignore-all} -->

下列協程客戶端是Swoole內置的類，其中標有 ⚠️ 標誌的不推薦再繼續使用，可以使用PHP原生的函數+[一鍵協程化](/runtime)。

* [TCP/UDP/UnixSocket客戶端](coroutine_client/client.md)
* [Socket客戶端](coroutine_client/socket.md)
* [HTTP/WebSocket客戶端](coroutine_client/http_client.md)
* [HTTP2客戶端](coroutine_client/http2_client.md)
* [PostgreSQL客戶端](coroutine_client/postgresql.md)
* [FastCGI客戶端](coroutine_client/fastcgi.md)
* ⚠️ [Redis客戶端](coroutine_client/redis.md)
* ⚠️ [MySQL客戶端](coroutine_client/mysql.md)
* [System](/coroutine/system)系統API


## 超時規則

所有的網絡請求(建立連接，發送數據，接收數據)都有可能超時，`Swoole`協程客戶端設置超時的方式有三種：

1. 通過方法的參數傳入超時時間，例如[Co\Client->connect()](/coroutine_client/client?id=connect)、[Co\Http\Client->recv()](/coroutine_client/http_client?id=recv)、[Co\MySQL->query()](/coroutine_client/mysql?id=query)等

!> 這種方式的影響範圍最小(只針對當前這次函數調用生效)，優先級最高(當前這次函數調用將無視下面的`2`、`3`設置)。

2. 通過`Swoole`協程客戶端類的`set()`或`setOption()`方法設置超時，例如：

```php
$client = new Co\Client(SWOOLE_SOCK_TCP);
//或
$client = new Co\Http\Client("127.0.0.1", 80);
//或
$client = new Co\Http2\Client("127.0.0.1", 443, true);
$client->set(array(
    'timeout' => 0.5,//總超時，包括連接、發送、接收所有超時
    'connect_timeout' => 1.0,//連接超時，會覆蓋第一個總的 timeout
    'write_timeout' => 10.0,//發送超時，會覆蓋第一個總的 timeout
    'read_timeout' => 0.5,//接收超時，會覆蓋第一個總的 timeout
));

//Co\Redis() 沒有 write_timeout 和 read_timeout 配置
$client = new Co\Redis();
$client->setOption(array(
    'timeout' => 1.0,//總超時，包括連接、發送、接收所有超時
    'connect_timeout' => 0.5,//連接超時，會覆蓋第一個總的 timeout 
));

//Co\MySQL() 沒有 set 配置的功能
$client = new Co\MySQL();

//Co\Socket 透過 setOption 配置
$socket = new Co\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = array('sec'=>1, 'usec'=>500000);
$socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout);//接受數據超時時間
$socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout);//連接超時和發送數據超時的配置
```

!> 這種方式的影響只針對當前類生效，會被第`1`種方式覆蓋，無視下面的第`3`種方式配置。

3. 可以看到上面`2`種方式超時設置規則很麻煩且不統一，為了避免開發者需要處處謹慎設置，從`v4.2.10`版本開始所有協程客戶端提供了全局統一超時規則設置，這種影響最大，優先級最低，如下：

```php
Co::set([
    'socket_timeout' => 5,
    'socket_connect_timeout' => 1,
    'socket_read_timeout' => 1,
    'socket_write_timeout' => 1,
]);
```

+ `-1`：表示永不超時
+ `0`：表示不更改超時時間
+ `其它大於0的值`：表示設置相應秒數的超時定時器，最大精度為`1毫秒`，是浮點型，`0.5`代表`500毫秒`
+ `socket_connect_timeout`：表示建立TCP連接超時時間，**默認為`1秒`** ，從`v4.5.x`版本開始**默認為`2秒`**
+ `socket_timeout`：表示TCP讀/寫操作超時時間，**默認為`-1`** ，從`v4.5.x`版本開始**默認為`60秒`** 。如果想把讀和寫分開設置，參考下面的配置
+ `socket_read_timeout`：`v4.3`版本加入，表示TCP**讀**操作超時時間，**默認為`-1`** ，從`v4.5.x`版本開始**默認為`60秒`**
+ `socket_write_timeout`：`v4.3`版本加入，表示TCP**寫**操作超時時間，**默認為`-1`** ，從`v4.5.x`版本開始**默認為`60秒`**

!> **即：** `v4.5.x`之前的版本所有`Swoole`提供的協程客戶端，如果沒用前面的第`1`、`2`種方式設置超時，默認連接超時時間為`1s`，讀/寫操作則永不超時；  
從`v4.5.x`版本開始默認連接超時時間為`60秒`，讀/寫操作超時時間為`60秒`；  
如果中途修改了全局超時，對於已創建的socket是不生效的。

### PHP官方網絡庫超時

除了上述`Swoole`提供的協程客戶端，在[一鍵協程化](/runtime)裡面使用的是原生PHP提供的方法，它們的超时时間受 [default_socket_timeout](http://php.net/manual/zh/filesystem.configuration.php) 配置影響，開發者可以通過`ini_set('default_socket_timeout', 60)`這樣來單獨設置它，它的默認值是60。
