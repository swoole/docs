# UDP 伺服器

## 程式碼

請將以下程式碼寫入 udpServer.php。

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

// 監聽資料接收事件。
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Server：{$data}");
});

// 啟動伺服器
$server->start();
```

UDP 伺服器與 TCP 伺服器不同，UDP 沒有連接的概念。啟動 Server 後，客戶端無需 Connect，直接可以向 Server 監聽的 9502 端口發送資料包。對應的事件為 onPacket。

* `$clientInfo` 是客戶端的有關資訊，是一個陣列，有客戶端的 IP 和端口等內容。
* 調用 `$server->sendto` 方法向客戶端發送資料。
!> Docker 默認使用 TCP 協議來通信，如果你需要使用 UDP 協議，你需要通過配置 Docker 網絡來實現。  
```shell
docker run -p 9502:9502/udp <image-name>
```

## 啟動服務

```shell
php udpServer.php
```

UDP 伺服器可以使用 `netcat -u` 來連接測試。

```shell
netcat -u 127.0.0.1 9502
hello
Server: hello
```
