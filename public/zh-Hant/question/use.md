# 使用问题

## Swoole性能如何

> QPS对比

使用 Apache-Bench工具(ab) 对Nginx静态页、Golang HTTP程序、PHP7+Swoole HTTP程序进行压力测试。在同一台机器上，进行并发100共100万次HTTP请求的基准测试中，QPS对比如下：

| 软件 | QPS | 软件版本 |
| --- | --- | --- |
| Nginx | 164489.92	| nginx/1.4.6 (Ubuntu) |
| Golang |	166838.68 |	go version go1.5.2 linux/amd64 |
| PHP7+Swoole |	287104.12 |	Swoole-1.7.22-alpha |
| Nginx-1.9.9 |	245058.70 |	nginx/1.9.9 |

!> 注：Nginx-1.9.9的测试中，已关闭access_log，启用open_file_cache缓存静态文件到内存

> 测试环境

* CPU：Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* 内存：16G
* 磁盘：128G SSD
* 操作系统：Ubuntu14.04 (Linux 3.16.0-55-generic)

> 压测方法

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> VHOST配置

```nginx
server {
    listen 80 default_server;
    root /data/webroot;
    index index.html;
}
```

> 测试页面

```html
<h1>Hello World!</h1>
```

> 进程数量

Nginx开启了4个Worker进程
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12月07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12月07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12月07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12月07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12月07   0:45 nginx: worker process
```

> Golang

测试代码

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

PHP7已启用`OPcache`加速器。

测试代码

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **全球Web框架权威性能测试 Techempower Web Framework Benchmarks**

最新跑分测试结果地址: [techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swoole领跑**动态语言第一**

数据库IO操作测试, 使用基本业务代码无特殊优化

**性能超过所有静态语言框架(使用MySQL而不是PostgreSQL)**

## Swoole如何维持TCP长连接

关于TCP长连接维持有2组配置[tcp_keepalive](/server/setting?id=open_tcp_keepalive)和[heartbeat](/server/setting?id=heartbeat_check_interval)。

## Swoole如何正确的重启服务

在日常开发中，修改了PHP代码后经常需要重启服务让代码生效，一台繁忙的后端服务器随时都在处理请求，如果管理员通过`kill`进程方式来终止/重启服务器程序，可能导致刚好代码执行到一半终止，没法保证整个业务逻辑的完整性。

`Swoole`提供了柔性终止/重启的机制，管理员只需要向`Server`发送特定的信号或者调用`reload`方法，工作进程就可以结束，并重新拉起。具体请参考[reload()](/server/methods?id=reload)
 
但有几点要注意：

首先要注意新修改的代码必须要在`OnWorkerStart`事件中重新载入才会生效，比如某个类在`OnWorkerStart`之前就通过composer的autoload载入了就是不可以的。

其次`reload`还要配合这两个参数[max_wait_time](/server/setting?id=max_wait_time)和[reload_async](/server/setting?id=reload_async)，设置了这两个参数之后就能实现`异步安全重启`。

如果没有此特性，Worker进程收到重启信号或达到[max_request](/server/setting?id=max_request)时，会立即停止服务，这时`Worker`进程内可能仍然有事件监听，这些异步任务将会被丢弃。设置上述参数后会先创建新的`Worker`，旧的`Worker`在完成所有事件之后自行退出，即`reload_async`。

如果旧的`Worker`一直不退出，底层还增加了一个定时器，在约定的时间([max_wait_time](/server/setting?id=max_wait_time)秒)内旧的`Worker`没有退出，底层会强行终止，并会产生一个 [WARNING](/question/use?id=forced-to-terminate) 报错。

示例：

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tClient[$fd] receive data: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

例如上面的代码，如果没有 reload_async 那么 onReceive 中创建的定时器将丢失，没有机会处理定时器中的回调函数。

### 进程退出事件

为了支持异步重启特性，底层新增了一个[onWorkerExit](/server/events?id=onWorkerExit)事件，当旧的`Worker`即将退出时，会触发`onWorkerExit`事件，在此事件回调函数中，应用层可以尝试清理某些长连接`Socket`，直到[事件循环](/learn?id=什么是eventloop)中没有fd或者达到了[max_wait_time](/server/setting?id=max_wait_time)退出进程。

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

同时在 [Swoole Plus](https://www.swoole.com/swoole_plus) 中增加了检测文件变化的功能，可以不用手动reload或者发送信号，文件变更自动重启worker。
## 為什麼不要send完後立即close就是不安全的

send完後立即close就是不安全的，無論是伺服器端還是客戶端。

send操作成功只是表示數據成功地寫入到操作系統socket緩存區，不代表對端真的接收到了數據。究竟操作系統有沒有發送成功，對方伺服器是否收到，伺服器端程序是否處理，都無法確切保證。

> close後的邏輯請看下面的linger設置相關

這個邏輯和電話溝通是一個道理，A告訴B一件事情，A說完了就掛掉電話。那麼B聽到沒有，A是不知道的。如果A說完事情，B說好，然後B掛掉電話，就絕對是安全的。

linger設置

一個`socket`在close時，如果發現緩衝區仍然有數據，操作系統底層會根據`linger`設置決定如何處理

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0，close時立刻返回，底層會將未發送完的數據發送完成後再釋放資源，也就是優雅地退出。
* l_onoff != 0，l_linger = 0，close時會立刻返回，但不會發送未發送完成的數據，而是通過一個RST包強制地關閉socket描述符，也就是強制地退出。
* l_onoff !=0，l_linger > 0，closes時不會立刻返回，內核會延遲一段時間，這個時間就由l_linger的值來決定。如果超時時間到達之前，發送完未發送的數據(包括FIN包)並得到另一端的確認，close會返回正確，socket描述符優雅性退出。否則close會直接返回錯誤值，未發送數據丟失，socket描述符被強制性退出。如果socket描述符被設置為非阻塞型，則close會直接返回值。

## client has already been bound to another coroutine

對於一個`TCP`連接來說Swoole底層允許同時只能有一個協程進行讀操作、一個協程進行寫操作。也就是說不能有多个協程對一個TCP進行讀/寫操作，底層會拋出綁定錯誤:

```shell
Fatal error: Uncaught Swoole\Error: Socket#6 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed 
```

重現代碼：

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

解決方案參考：https://wenda.swoole.com/detail/107474

!> 此限制對於所有多協程環境都有效，最常见的就是在[onReceive](/server/events?id=onreceive)等回調函數中去共用一個TCP連接，因為此類回調函數會自動創建一個協程，
那有連接池需求怎麼辦？`Swoole`內置了[連接池](/coroutine/conn_pool)可以直接使用，或手動用`channel`封裝連接池。

## Call to undefined function Co\run()

本文檔中的大部分示例都使用了`Co\run()`來創建一個協程容器，[了解什麼是協程容器](/coroutine?id=什麼是協程容器)

如果遇到如下錯誤：

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

說明你的`Swoole`擴展版本小於`v4.4.0`或者手動關閉了[協程短名稱](/other/alias?id=協程短名稱)，提供以下解決方法

* 如果是版本過低，則請升級擴展版本至`>= v4.4.0`或使用`go`關鍵字替換`Co\run`來創建協程；
* 如果是關閉了協程短名稱，則請打開[協程短名稱](/other/alias?id=協程短名稱)；
* 使用[Coroutine::create](/coroutine/coroutine?id=create)方法替換`Co\run`或`go`來創建協程；
* 使用全名：`Swoole\Coroutine\run`；

## 是否可以共用1個Redis或MySQL連接

絕對不可以。必須每個進程單獨創建`Redis`、`MySQL`、`PDO`連接，其他的存儲客戶端同樣也是如此。原因是如果共用1個連接，那麼返回的結果無法保證被哪個進程處理，持有連接的進程理論上都可以對這個連接進行讀寫，這樣數據就發生錯亂了。

**所以在多個進程之間，一定不能共用連接**

* 在[Swoole\Server](/server/init)中，應當在[onWorkerStart](/server/events?id=onworkerstart)中創建連接對象
* 在[Swoole\Process](/process/process)中，應當在[Swoole\Process->start](/process/process?id=start)後，子進程的回調函數中創建連接對象
* 此問題所述信息對使用`pcntl_fork`的程序同樣有效

示例：

```php
$server = new Swoole\Server('0.0.0.0', 9502);

//必須在onWorkerStart回調中創建redis/mysql連接
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```

## 連接已關閉問題

如以下提示

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

服務端響應時，客戶端已經切斷了連接導致

常見於:

* 瀏覽器瘋狂刷新頁面(還沒加載完就刷掉了)
* ab壓測到一半取消
* wrk基於時間的壓測 (時間到了未完成的請求會被取消)

以上幾種情況均屬於正常現象，可以忽略，所以該錯誤的級別是NOTICE

如由於其它情況无缘無故出現大量連接斷開時，才需要注意

```bash
WARNING swWorker_discard_data (ERRNO 1007): [2] received the wrong data[21 bytes] from socket#75

WARNING Worker_discard_data (ERRNO 1007): [2] ignore data[5 bytes] received from session#2
```

同樣的，這個錯誤也表示連接已經關閉了，收到的數據會被丟棄。參考[discard_timeout_request](/server/setting?id=discard_timeout_request)

## connected屬性和連接狀態不一致

4.x協程版本後，`connected`屬性不再會實時更新，[isConnect](/client?id=isconnected)方法不再可靠

### 原因

協程的目標是和同步阻塞的編程模型一致，同步阻塞模型中不會有實時更新連接狀態的概念，如PDO, curl等，都沒有連接的概念，而是在IO操作時返回錯誤或拋出異常才能發現連接斷開

Swoole底層通用的做法是，IO錯誤時，返回false(或空白內容表示連接已斷開)，並在客戶端對象上設置相應的錯誤碼，錯誤信息
### 注意

雖然以前的異步版本支援「即時」更新`connected`屬性，但實際上並不可靠，連接可能會在你檢查後馬上就斷開了


## Connection refused是什麼回事

使用telnet連接到127.0.0.1的9501端口時發生Connection refused，這表示伺服器未監聽此端口。

* 檢查程式是否執行成功: ps aux
* 檢查端口是否在監聽: netstat -lp
* 查看網絡通信過程是否正常: tcpdump traceroute


## Resource temporarily unavailable [11]

客戶端swoole_client在`recv`時報告

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```

這個錯誤表示，伺服器端在規定的時間內沒有返回數據，接收超時了。

* 可以通過tcpdump查看網絡通信過程，檢查伺服器是否發送了數據
* 伺服器的`$serv->send`函數需要檢測是否返回了true
* 外網通信時，耗時較多需要調大swoole_client的超時時間


## worker exit timeout, forced to terminate :id=forced-to-terminate

發現形如以下的報錯：

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```

表示在約定的時間 ([max_wait_time](/server/setting?id=max_wait_time)秒) 內此 Worker 沒有退出，Swoole底層強行終止此進程。

可使用如下程式碼進行複現：

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```


## Unable to find callback function for signal Broken pipe: 13

發現形如以下的報錯：

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```

表示向已斷開的連接發送了數據，一般 是因為沒有判斷發送的回傳值，回傳失敗了還在繼續發送


## 學習Swoole需要掌握哪些基礎知識


### 多進程/多線程

* 了解`Linux`作業系統進程和線程的概念
* 了解`Linux`進程/線程切換調度的基本知識
* 了解進程間通信的基本知識，如管道、`UnixSocket`、消息隊列、共享內存


### SOCKET

* 了解`SOCKET`的基本操作如`accept/connect`、`send/recv`、`close`、`listen`、`bind`
* 了解`SOCKET`的接收緩衝區、發送緩衝區、阻塞/非阻塞、超時等概念


### IO復用

* 了解`select`/`poll`/`epoll`
* 了解基於`select`/`epoll`實現的事件循環，`Reactor`模型
* 了解可讀事件、可寫事件


### TCP/IP網絡協議

* 了解`TCP/IP`協議
* 了解`TCP`、`UDP`傳輸協議


### 調試工具

* 使用 [gdb](/other/tools?id=gdb) 調試`Linux`程式
* 使用 [strace](/other/tools?id=strace) 跟蹤進程的系統調用
* 使用 [tcpdump](/other/tools?id=tcpdump) 跟蹤網絡通信過程
* 其他`Linux`系統工具，如ps、[lsof](/other/tools?id=lsof)、top、vmstat、netstat、sar、ss等


## Object of class Swoole\Curl\Handler could not be converted to int

在使用 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) 時，發生報錯：

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```

原因是 hook 後的 curl 不再是一個 resource 型別，而是 object 型別，所以不支持轉換為 int 型別。

!> `int` 的问题建议联系 SDK 方修改代码，在PHP8中 curl 不再是 resource 类型，而是 object 类型。

解決方法有三種：

1. 不開啟 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)。不過從 [v4.5.4](/version/log?id=v454) 版本開始，[SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all) 默认包含了 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)，可以設置為`SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL`來關閉 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)

2. 使用 Guzzle 的SDK，可以替換 Handler 來實現協程化

3. 從Swoole `v4.6.0` 版本開始可以使用[SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)來代替[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)


## 同時使用一鍵協程化和Guzzle 7.0+的時候，發起請求後將結果直接輸出在終端 :id=hook_guzzle

複现代碼如下

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// v4.5.4之前的版本
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// 請求結果會直接輸出，而不是打印出來的
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> 解決方法和上一個問題一致。不過此問題已在 Swoole 版本 >= `v4.5.8` 中修復。


## Error: No buffer space available[55]

可以忽略此錯誤。這個錯誤就是 [socket_buffer_size](/server/setting?id=socket_buffer_size) 選項過大，個別系統不接受，並不影響程式的運行。


## GET/POST請求的最大尺寸


### GET請求最大8192

GET請求只有一個Http頭，Swoole底層使用固定大小的內存緩衝區8K，並且不可修改。如果請求不是正確的Http請求，將會出現錯誤。底層會拋出以下錯誤：

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```

### POST文件上傳

最大尺寸受到 [package_max_length](/server/setting?id=package_max_length) 配置項限制，默認為2M，可以調用 [Server->set](/server/methods?id=set) 傳入新的值修改尺寸。Swoole底層是全內存的，因此如果設置過大可能會導致大量並發請求將伺服器資源耗盡。

計算方法：`最大內存占用` = `最大並發請求數` * `package_max_length` 

