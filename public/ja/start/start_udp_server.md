# UDP サーバー

## プログラムコード

以下のコードを udpServer.php に書き込んでください。

```php
$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

//データ受信イベントを監視します。
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], "Server：{$data}");
});

//サーバーを開始します。
$server->start();
```

UDPサーバーは TCP サーバーとは異なり、接続の概念がありません。サーバーを開始した後、クライアントは Connectする必要がなく、直接にサーバーがlistenしている9502ポートにデータパケットを送ることができます。対応するイベントは onPacket です。

* `$clientInfo` はクライアントに関する情報であり、IPとポートなどの内容を含む配列です。
* `$server->sendto` メソッドを呼び出してクライアントにデータを送信します。
!> Docker 默认では TCPプロトコルを使用して通信しますが、UDPプロトコルを使用する必要がある場合は、Dockerネットワークを構成して実現する必要があります。  
```shell
docker run -p 9502:9502/udp <image-name>
```

## サービスを開始する

```shell
php udpServer.php
```

UDPサーバーは `netcat -u` を使用して接続テストを行うことができます。

```shell
netcat -u 127.0.0.1 9502
hello
Server: hello
```
