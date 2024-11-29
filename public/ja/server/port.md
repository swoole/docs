# 多ポート監視

`Swoole\Server`は複数のポートを監視することができ、各ポートには異なるプロトコルの処理方法を設定することができます。例えば、ポート80はHTTPプロトコルを処理し、ポート9507はTCPプロトコルを処理します。`SSL/TLS`伝送暗号化も特定のポートにのみ有効にすることができます。

例えば、メインブラットフォームがWebSocketまたはHTTPプロトコルで、新しく監視されるTCPポート（[listen](/server/methods?id=listen)の戻り値、すなわち[Swoole\Server\Port](server/server_port.md)オブジェクト、以下「ポート」と略称）はデフォルトでメインノードのプロトコル設定を継承しますが、新しいプロトコルを有効にするためには、ポートオブジェクトの`set`メソッドと`on`メソッドを個別に呼び出さなければなりません。
## 新ポートの監視

```php
//ポートオブジェクトを返す
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```
## ネットワークプロトコルの設定

```php
//ポートオブジェクトのsetメソッドを呼び出す
$port1->set([
	'open_length_check' => true,
	'package_length_type' => 'N',
	'package_length_offset' => 0,
	'package_max_length' => 800000,
]);

$port3->set([
	'open_eof_split' => true,
	'package_eof' => "\r\n",
	'ssl_cert_file' => 'ssl.cert',
	'ssl_key_file' => 'ssl.key',
]);
```
## コールバック関数の設定

```php
//各ポートの回调関数を設定する
$port1->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});

$port1->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});

$port1->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$port2->on('packet', function ($serv, $data, $addr) {
    var_dump($data, $addr);
});
```
## Http/WebSocket

`Swoole\Http\Server`と`Swoole\WebSocket\Server`は継承子クラスを使用して実現されているため、`Swoole\Server`インスタンスの`listen`方法を呼び出してHTTPまたはWebSocketサーバーを作成することはできません。

例えば、サーバーの主要な機能が`RPC`であるが、簡単なWeb管理画面を提供したい場合などがあります。このようなシナリオでは、まずHTTP/WebSocketサーバーを作成し、その後で原生TCPのポートで`listen`して監視することができます。
### 示例

```php
$http_server = new Swoole\Http\Server('0.0.0.0',9998);
$http_server->set(['daemonize'=> false]);
$http_server->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

//多监听一个TCP端口，对外开启TCP服务，并设置TCP服务器的回调
$tcp_server = $http_server->listen('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
//默认新监听的端口 9999 会继承主服务器的设置，也是 HTTP 协议
//需要调用 set 方法覆盖主服务器的设置
$tcp_server->set([]);
$tcp_server->on('receive', function ($server, $fd, $threadId, $data) {
    echo $data;
});

$http_server->start();
```

このようなコードを通じて、HTTPサービスとTCPサービスを同時に提供するサーバーを構築することができます。より具体的な優雅なコードの組み合わせは、自分で実現する必要があります。
## TCP、HTTP、WebSocket多プロトコルポートの複合設定

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_websocket_protocol' => true, // このポートをWebSocketプロトコルに対応させる
]);
```

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port1->set([
    'open_http_protocol' => false, // このポートのHTTPプロトコル機能をオフにする
]);
```
同様に、`open_http_protocol`、`open_http2_protocol`、`open_mqtt_protocol`などのパラメータがあります。
## 可選パラメータ

* `listen`ポートが`set`メソッドを呼び出さなかった場合、プロトコル処理オプションを設定した`listen`ポートは、メインノードの関連設定を継承します。
* メインノードが`HTTP/WebSocket`サーバーの場合、プロトコルパラメータを指定していなければ、`listen`ポートはデフォルトで`HTTP`または`WebSocket`プロトコルを設定され、ポートで設定された[onReceive](/server/events?id=onreceive)コールバックは実行されません。
* メインノードが`HTTP/WebSocket`サーバーの場合、`listen`ポートが`set`で設定パラメータを呼び出した場合、メインノードのプロトコル設定がクリアされます。`listen`ポートはTCPプロトコルになります。`listen`ポートで`HTTP/WebSocket`プロトコルを維持したい場合は、設定に`open_http_protocol => true`と`open_websocket_protocol => true`を追加する必要があります。

**`port`が`set`で設定可能なパラメータには以下が含まれます:**

* socketパラメータ: 例として`backlog`、`open_tcp_keepalive`、`open_tcp_nodelay`、`tcp_defer_accept`など
* プロトコル関連: 例として`open_length_check`、`open_eof_check`、`package_length_type`など
* SSL証明書関連: 例として`ssl_cert_file`、`ssl_key_file`など

具体的な内容は[設定章](/server/setting)を参照してください。
## 可选コールバック

`port`が`on`メソッドを呼び出さなかった場合、コールバック関数を設定した`listen`ポートは、デフォルトでメインノードのコールバック関数を使用します。`port`が`on`で設定可能なコールバックには以下が含まれます:
### TCPサーバー

* onConnect
* onClose
* onReceive
### UDPサーバー

* onPacket
* onReceive
### HTTPサーバー

* onRequest
### WebSocketサーバー

* onMessage
* onOpen
* onHandshake

!> 異なるlistenポートのコールバック関数は、同じ`Worker`プロセス空間内で実行されます。
## 多ポート下の接続の遍历

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9514, SWOOLE_BASE);

$tcp = $server->listen("0.0.0.0", 9515, SWOOLE_SOCK_TCP);
$tcp->set([]);

$server->on("open", function ($serv, $req) {
    echo "new WebSocket Client, fd={$req->fd}\n";
});

$server->on("message", function ($serv, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $serv->push($frame->fd, "this is server OnMessage");
});

$tcp->on('receive', function ($server, $fd, $reactor_id, $data) {
    //仅遍历 9514 端口的连接，因为是用的$server，不是$tcp
    $websocket = $server->ports[0];
    foreach ($websocket->connections as $_fd) {
        var_dump($_fd);
        if ($server->exist($_fd)) {
            $server->push($_fd, "this is server onReceive");
        }
    }
    $server->send($fd, 'receive: '.$data);
});

$server->start();
```
