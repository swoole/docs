```php
// Server 对象を作成し、127.0.0.1:9501 端口で待ち受ける。
$server = new Swoole\Server('127.0.0.1', 9501);

// 连接が入るイベントを監視する。
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// データを受信するイベントを監視する。
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// 连接が閉じるイベントを監視する。
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// サーバーを起動する。
$server->start();
```

これを `tcpServer.php`に書き込むことで、`TCP`サーバーが本地の `9501` 端口で待ち受けるようになります。そのロジックは非常にシンプルで、クライアントの `Socket`がネットワークを通じて `hello` 文字列を送信すると、サーバーは `Server: hello` 文字列を返信します。

`Server`は非同期サーバーであるため、イベントを監視する方法でプログラムを書く必要があります。対応するイベントが発生すると、下層が自動的に指定された関数を回调します。例えば、新しい `TCP`连接が入る时会执行[onConnect](/server/events?id=onconnect)イベント回调、ある连接がサーバーにデータを送信时会回调[onReceive](/server/events?id=onreceive)関数。

* サーバーは同時に何千ものクライアント连接を処理することができ、`$fd`はクライアント连接のユニークな識別子です。
* `$server->send()` 方法を呼び出してクライアント连接にデータを送信し、パラメータは `$fd`クライアント識別子です。
* `$server->close()` 方法を呼び出して強制的にあるクライアント连接を閉じることができます。
* クライアントが自ら连接を切断すると、この時[onClose](/server/events?id=onclose)イベント回调が触发されます。

## プログラムを実行する

```shell
php tcpServer.php
```

`server.php`プログラムをコマンドラインで実行し、成功した後は `netstat` ツールを使用して `9501` 端口で待ち受けていることが確認できます。

その後は `telnet/netcat` ツールを使用してサーバーに接続することができます。

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```

## サーバーに接続できない簡単な検出方法

* `Linux`では、`netstat -an | grep 端口`を使用して、端口が既に開放されて `Listening`状態にあるかどうかを確認します。
* 前のステップで確認した後、ファイアウォールの問題を確認します。
* サーバーが使用しているIPアドレスに注意してください。もし `127.0.0.1` 回環アドレスを使用している場合は、クライアントは `127.0.0.1` でのみ接続できます。
* 阿里云サービスや腾讯サービスを使用している場合は、開発用のポートをセキュリティ権限グループで設定する必要があります。

## TCPデータ包の境界問題

[TCPデータ包の境界問題](/learn?id=tcp数据包边界问题)を参照してください。
```
