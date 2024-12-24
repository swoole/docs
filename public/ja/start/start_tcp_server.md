# TCPサーバー

## プログラムコード

以下のコードをtcpServer.phpとして書き込んでください。

```php
// Serverオブジェクトを作成し、127.0.0.1の9501ポートをlistenします。
$server = new Swoole\Server('127.0.0.1', 9501);

// 接続が入ったイベントをlistenします。
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

// データを受け取ったイベントをlistenします。
$server->on('Receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Server: {$data}");
});

// 接続が閉まったイベントをlistenします。
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

// サーバーを起動します。
$server->start(); 
```

これにより、ローカルな`9501`ポートをlistenする`TCP`サーバーが一つ作成されました。そのロジックは非常にシンプルで、クライアントの`Socket`がネットワークを通じて`hello`という文字列を送信すると、サーバーは`Server: hello`という文字列を受信し返します。

`Server`は非同期サーバーですので、イベントをlistenする方法でプログラムを書くことになります。対応するイベントが発生すると、下層部が自動的に指定された関数をcallbacksします。例えば、新しい`TCP`接続が入るたびに[onConnect](/server/events?id=onconnect)イベントCallbackが実行され、某个接続がサーバーにデータを送信すると[onReceive](/server/events?id=onreceive)関数がcallbackされます。
* サーバーは同時に何千ものクライアント接続を受け入れることができ、`$fd`はクライアント接続のユニークな識別子です。
* `$server->send()`方法を呼び出してクライアント接続にデータを送信し、参数は`$fd`クライアント識別子です。
* `$server->close()`方法を呼び出して、特定のクライアント接続を強制的に閉じることができます。
* クライアントが自ら接続を切断することもありますが、この場合[onClose](/server/events?id=onclose)イベントCallbackがトリガーされます。

## プログラムを実行する

```shell
php tcpServer.php
```

`server.php`プログラムを実行するためにコマンドラインを操作し、成功して起動した後には `netstat` ツールを使用して、すでに`9501`ポートをlistenしていることが確認できます。

その後は、`telnet/netcat`ツールを使用してサーバーに接続することができます。

```shell
telnet 127.0.0.1 9501
hello
Server: hello
```

## サーバーに接続できない简单的な検出方法

* `Linux`では、`netstat -an | grep Port`を使用して、ポートが既に開いており `Listening`状態にあるかどうかを確認します。
* 前のステップで確認した後、FIREWALLの問題をチェックしてください。
* サーバーが使用しているIPアドレスに注意し、もし`127.0.0.1`というループバックアドレスを使用している場合は、クライアントは `127.0.0.1`のみを使用して接続できます。
* 阿里云サービスや腾讯サービスを使用している場合は、開発用のポートをセキュリティ権限グループで設定する必要があります。

## TCPデータパックの境界問題。

[TCPデータパックの境界問題](/learn?id=tcpデータパックの境界問題)を参照してください。
