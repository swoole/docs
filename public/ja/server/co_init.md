# サーバー（協程スタイル）<!-- {docsify-ignore-all} -->

`Swoole\Coroutine\Server`は、[非同期スタイル](/server/init)のサーバーとは異なり、完全に協程化された実装のサーバーです。[完全な例](/coroutine/server?id=完全な例)を参照してください。
## 利点：
- イベント回调関数を設定する必要はありません。接続的建立、データの受信、データの送信、接続の閉鎖は順序良く行われ、非同期スタイルの[問題](/server/init)である並行性がありません。例えば：

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

//接続进入イベントの监听
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);//此处OnConnectの协程会挂起
    Co::sleep(5);//此处sleep模拟connect比较慢的情况
    $redis->set($fd,"fd $fd connected");
});

//データ受信イベントの监听
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);//此处onReceive的协程会挂起
    var_dump($redis->get($fd));//有可能onReceive的协程的redis连接先建立好了，上面的set还没有执行，此处get会是false，产生逻辑错误
});

//接続閉鎖イベントの监听
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

//サーバーを開始
$serv->start();
```
上記の`非同期スタイル`のサーバーでは、イベントの順序を保証することができません。つまり、`onConnect`が実行された後に`onReceive`に入ることは保証されません。協程化を開始した後、`onConnect`と`onReceive`の回调は自動的に协程を作成し、IOが発生すると[协程スケジュール](/coroutine?id=协程调度)が行われます。非同期スタイルではスケジュール順序を保証することができませんが、協程スタイルのサーバーにはこの問題はありません。
- サービスを動的に開始または停止することができます。非同期スタイルのサービスは`start()`が呼び出された後、何もできません。しかし、協程スタイルのサービスは動的にサービスを開始または停止することができます。
## 欠点：
- 協程スタイルのサービスは自動的に複数のプロセスを作成することはなく、[Process\Pool](/process/process_pool)モジュールと組み合わせて使用してマルチコアを利用する必要があります。
- 協程スタイルのサービスは実際には[Co\Socket](/coroutine_client/socket)モジュールの封装であり、協程スタイルを使用するためにはsocketプログラミングについてある程度の経験が必要です。
- 現在、封装レベルは非同期スタイルのサーバーほど高くなく、いくつかのものは手動で実現する必要があります。例えば、`reload`機能は信号を監視して論理を行う必要があります。
