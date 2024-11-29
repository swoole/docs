# コルネリスクライアント <!-- {docsify-ignore-all} -->

以下のコルネリスクライアントはSwooleに内蔵されているクラスで、⚠️マークが付いているものは推奨されず、PHPのネイティブ関数+[ワンボタンコルネリス化](/runtime)を使用することができます。

* [TCP/UDP/UnixSocketクライアント](coroutine_client/client.md)
* [ソケットクライアント](coroutine_client/socket.md)
* [HTTP/WebSocketクライアント](coroutine_client/http_client.md)
* [HTTP2クライアント](coroutine_client/http2_client.md)
* [PostgreSQLクライアント](coroutine_client/postgresql.md)
* [FastCGIクライアント](coroutine_client/fastcgi.md)
* ⚠️ [Redisクライアント](coroutine_client/redis.md)
* ⚠️ [MySQLクライアント](coroutine_client/mysql.md)
* [システム](/coroutine/system)システムAPI


## タイムアウトルール

すべてのネットワークリクエスト（接続の確立、データの送信、データの受信）はタイムアウトする可能性があります。`Swoole`コルネリスクライアントのタイムアウト設定方法には3種類あります：

1. メソッドのパラメータを通じてタイムアウト時間を入力する。例えば[Co\Client->connect()](/coroutine_client/client?id=connect)、[Co\Http\Client->recv()](/coroutine_client/http_client?id=recv)、[Co\MySQL->query()](/coroutine_client/mysql?id=query)など

!> この方法の影響範囲は最も小さく（現在のこの関数呼び出しにのみ適用され）、優先度が最も高い（現在のこの関数呼び出しは以下の`2`、`3`設定を無視します）。

2. `Swoole`コルネリスクライアントクラスの`set()`または`setOption()`メソッドを通じてタイムアウトを設定する。例えば：

```php
$client = new Co\Client(SWOOLE_SOCK_TCP);
//または
$client = new Co\Http\Client("127.0.0.1", 80);
//または
$client = new Co\Http2\Client("127.0.0.1", 443, true);
$client->set(array(
    'timeout' => 0.5,//総タイムアウト、接続、送信、受信のすべてのタイムアウトを含む
    'connect_timeout' => 1.0,//接続タイムアウト、最初の総タイムアウトを上書きする
    'write_timeout' => 10.0,//送信タイムアウト、最初の総タイムアウトを上書きする
    'read_timeout' => 0.5,//受信タイムアウト、最初の総タイムアウトを上書きする
));

//Co\Redis()にはwrite_timeoutとread_timeoutの設定がない
$client = new Co\Redis();
$client->setOption(array(
    'timeout' => 1.0,//総タイムアウト、接続、送信、受信のすべてのタイムアウトを含む
    'connect_timeout' => 0.5,//接続タイムアウト、最初の総タイムアウトを上書きする
));

//Co\MySQL()にはset設定の機能がない
$client = new Co\MySQL();

//Co\SocketはsetOptionを通じて設定
$socket = new Co\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = array('sec'=>1, 'usec'=>500000);
$socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout);//データ受信タイムアウト時間
$socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout);//接続タイムアウトとデータ送信タイムアウトの設定
```

!> この方法の影響は現在のクラスにのみ適用され、第`1`の方法によって上書きされ、以下の第`3`の方法の設定を無視します。

3. 上記の`2`の方法のタイムアウト設定ルールは非常に面倒で統一されていないため、開発者がどこでも慎重に設定する必要を避けるために、`v4.2.10`バージョンからすべてのコルネリスクライアントはグローバル統一タイムアウトルールの設定を提供しています。これは最も大きな影響を与え、優先度が最も低いです。以下の通り：

```php
Co::set([
    'socket_timeout' => 5,
    'socket_connect_timeout' => 1,
    'socket_read_timeout' => 1,
    'socket_write_timeout' => 1,
]);
```

+ `-1`：タイムアウトしないことを意味する
+ `0`：タイムアウト時間を変更しないことを意味する
+ `その他の0より大きい値`：対応する秒数のタイムアウトタイマーを設定する。最大精度は`1ミリ秒`で、浮動小数点型で、`0.5`は`500ミリ秒`を表す
+ `socket_connect_timeout`：TCP接続タイムアウト時間を表す。**デフォルトは`1秒`** で、`v4.5.x`バージョンから**デフォルトは`2秒`** になる
+ `socket_timeout`：TCP読み取り/書き込み操作のタイムアウト時間を表す。**デフォルトは`-1`** で、`v4.5.x`バージョンから**デフォルトは`60秒`** になる。読み取りと書き込みを分けて設定したい場合は、以下の設定を参照
+ `socket_read_timeout`：`v4.3`バージョンで追加され、TCP**読み取り**操作のタイムアウト時間を表す。**デフォルトは`-1`** で、`v4.5.x`バージョンから**デフォルトは`60秒`** になる
+ `socket_write_timeout`：`v4.3`バージョンで追加され、TCP**書き込み**操作のタイムアウト時間を表す。**デフォルトは`-1`** で、`v4.5.x`バージョンから**デフォルトは`60秒`** になる

!> **つまり：** `v4.5.x`以前のバージョンのすべての`Swoole`提供のコルネリスクライアントは、前述の第`1`、`2`の方法でタイムアウトを設定していない場合、デフォルトの接続タイムアウト時間は`1秒`で、読み取り/書き込み操作は永遠にタイムアウトしない；  
`v4.5.x`バージョンからはデフォルトの接続タイムアウト時間は`60秒`、読み取り/書き込み操作のタイムアウト時間は`60秒`になる；  
途中でグローバルタイムアウトを変更しても、既に作成されたソケットには適用されない。

### PHP公式ネットワークライブラリのタイムアウト

上記の`Swoole`提供のコルネリスクライアント以外に、[ワンボタンコルネリス化](/runtime)で使用されているのはネイティブPHPが提供する方法で、それらのタイムアウト時間は[default_socket_timeout](http://php.net/manual/zh/filesystem.configuration.php)設定の影響を受けます。開発者は`ini_set('default_socket_timeout', 60)`のようにして個別に設定することができ、そのデフォルト値は60です。
