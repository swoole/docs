# TCP 伺服器

## 程式碼

請將以下程式碼寫入 tcpServer.php。

```php
// 建立 Server 物件，監聽 127.0.0.1:9501 端口。
$server = new Swoole\Server('127.0.0.1', 9501);

// 監聽連接進入事件。
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// 監聽資料接收事件。
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// 監聽連接關閉事件。
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// 啟動伺服器
$server->start(); 
```

這樣就建立了一個 `TCP` 伺服器，監聽本機 `9501` 端口。它的邏輯很簡單，當客戶端 `Socket` 透過網絡發送一個 `hello` 字串時，伺服器會回覆一個 `Server: hello` 字串。

`Server` 是異步伺服器，所以是透過監聽事件的方式來編寫程式的。當對應的事件發生時底層會主動回調指定的函數。如當有新的 `TCP` 連接進入時會執行[onConnect](/server/events?id=onconnect)事件回調，當某個連接向伺服器發送資料時會回調[onReceive](/server/events?id=onreceive)函數。

* 伺服器可以同時被成千上萬個客戶端連接，`$fd` 就是客戶端連接的唯一識別符。
* 呼叫 `$server->send()` 方法向客戶端連接發送資料，參數就是 `$fd` 客戶端識別符。
* 呼叫 `$server->close()` 方法可以強迫關閉某個客戶端連接。
* 客戶端可能會主動斷開連接，此時會觸發[onClose](/server/events?id=onclose)事件回調。

## 執行程式

```shell
php tcpServer.php
```

在命令列下運行 `server.php` 程式，啟動成功後可以使用 `netstat` 工具看到已經在監聽 `9501` 端口。

這時就可以使用 `telnet/netcat` 工具連接伺服器。

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```

## 無法連接到伺服器的簡單檢測手段

* 在 `Linux` 下，使用 `netstat -an | grep 端口`，查看端口是否已經被打開處於 `Listening` 狀態。
* 上一步確認後，再檢查防火牆問題。
* 注意伺服器所使用的 IP 地址，如果是 `127.0.0.1` 回環地址，則客戶端只能使用 `127.0.0.1` 才能連接上。
* 用的是阿里云服務或者騰訊服務，需要在安全權限組進行設置開發的端口。

## TCP 數據包邊界問題。

參考[TCP數據包邊界問題](/learn?id=tcp數據包邊界問題)。
