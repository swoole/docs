# 伺服器（協程風格）<!-- {docsify-ignore-all} -->

`Swoole\Coroutine\Server` 與 [非同步風格](/server/init) 的伺服器不同之處在於，`Swoole\Coroutine\Server` 是完全由協程實現的伺服器，請參考 [完整範例](/coroutine/server?id=完整示範)。
 

## 優點：


- 不需要設定事件回調函數。建立連接、接收資料、傳送資料、關閉連接都是順序的，沒有 [非同步風格](/server/init) 的並發問題，例如：

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

//監聽連接進入事件
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);//此处OnConnect的协程会挂起
    Co::sleep(5);//此处sleep模拟connect比较慢的情况
    $redis->set($fd,"fd $fd connected");
});

//監聽資料接收事件
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);//此处onReceive的协程会挂起
    var_dump($redis->get($fd));//有可能onReceive的协程的redis连接先建立好了，上面的set还没有执行，此处get会是false，产生逻辑错误
});

//監聽連接關閉事件
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

//啟動伺服器
$serv->start();
```

上述`非同步風格`的伺服器，無法保證事件的順序，即無法保證`onConnect`執行結束後才進入`onReceive`，因為在開啟協程化後，`onConnect`和`onReceive`回調都會自動創建協程，遇到IO會產生[協程調度](/coroutine?id=協程調度)，非同步風格的無法保證調度順序，而協程風格的服务端沒有這個問題。  


- 可以動態的開啟關閉服務，非同步風格的服务在`start()`被調用之後就什麼也干不了瞭，而協程風格的可以動態開啟關閉服務。  


## 缺點：



- 協程風格的服务不會自動創建多個進程，需要配合[Process\Pool](/process/process_pool)模組使用才能利用多核。  
- 協程風格服務其實是對[Co\Socket](/coroutine_client/socket)模組的封裝，所以用協程風格的需要對socket編程有一定經驗。  
- 目前封裝層級沒有非同步風格伺服器那麼高，有些東西需要自己手動實現，比如`reload`功能需要自己監聽信號來做邏輯。
