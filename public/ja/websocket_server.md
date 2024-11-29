# Swoole\WebSocket\Server

?> 内置の`WebSocket`サーバーのサポートを通じて、数行の`PHP`コードで[非同期IO](/learn?id=同期io非同期io)のマルチプロセス`WebSocket`サーバーを書くことができます。

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});

$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();
```

* **クライアント**

  * `Chrome/Firefox/`高バージョンの`IE/Safari`などのブラウザには、`JS`言語の`WebSocket`クライアントが組み込まれています
  * 微信小程序開発フレームワークには、`WebSocket`クライアントが組み込まれています
  * [非同期IO](/learn?id=同期io非同期io)の`PHP`プログラムでは、[Swoole\Coroutine\Http](/coroutine_client/http_client)を`WebSocket`クライアントとして使用できます
  * `Apache/PHP-FPM`または他の同期ブロッキングの`PHP`プログラムでは、`swoole/framework`が提供する[同期WebSocketクライアント](https://github.com/matyhtf/framework/blob/master/libs/Swoole/Client/WebSocket.php)を使用できます
  * `WebSocket`クライアントではないものは`WebSocket`サーバーと通信できません

* **WebSocketクライアントかどうかを判断する方法**

?> [以下の例](/server/methods?id=getclientinfo)を使用して接続情報を取得し、返される配列の中に`[websocket_status](/websocket_server?id=连接状态)`があれば、この状態に基づいて`WebSocket`クライアントかどうかを判断できます。
```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    $client = $server->getClientInfo($frame->fd);
    // または $client = $server->connection_info($frame->fd);
    if (isset($client['websocket_status'])) {
        echo "是websocket 连接";
    } else {
        echo "不是websocket 连接";
    }
});
```

## イベント

?> `WebSocket`サーバーは、`Swoole\Server`や[Swoole\Http\Server](/http_server)の基類のカスタムハンドラ以外にも、`4`つのカスタムハンドラを追加しています。その中で：

* `onMessage`カスタムハンドラは必須です
* `onOpen`、`onHandShake`、`onBeforeHandShakeResponse`（Swoole5で提供されるイベント）カスタムハンドラはオプションです
### onBeforeHandshakeResponse

!> Swooleバージョン >= `v5.0.0` で利用可能

?> **`WebSocket`の接続が確立される前に発生します。もし自分でハンドショー処理を必要としていないが、いくつかの`http header`情報を応答ヘッジに設定したい場合は、このイベントを呼び出すことができます。**

```php
onBeforeHandshakeResponse(Swoole\Http\Request $request, Swoole\Http\Response $response);
```
### onHandShake

?> **`WebSocket`の接続が確立された後に行われるハンドショーです。`WebSocket`サーバーは自動的に`handshake`ハンドショーのプロセスを行いますが、ユーザーが自らハンドショー処理を行いたい場合は、`onHandShake`イベントのカスタムハンドラを設定することができます。**

```php
onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

* **ヒント**

  * `onHandShake`イベントのカスタムハンドラはオプションです
  * `onHandShake`カスタムハンドラを設定した後は、`onOpen`イベントは再びトリガーされません。応用コードで自分で処理する必要があります。`$server->defer`を使用して`onOpen`ロジックを呼び出すことができます
  * `onHandShake`の中で必ず`response->status(101)`を呼び出して状態コードを`101`に設定し、`response->end()`で応答を終了させる必要があります。そうでなければハンドショーは失敗します。
  * 内置のハンドショープロトコルは`Sec-WebSocket-Version: 13`で、低バージョンのブラウザでは自分でハンドショーを実装する必要があります

* **注意**

!> `handshake`を自分で処理する必要がある場合は、このカスタムハンドラを設定してください。もし「自分で」ハンドショー処理を必要としない場合は、このカスタムハンドラを設定せずに、Swooleのデフォルトのハンドショーを使用してください。以下は「自分で」`handshake`イベントのカスタムハンドラで必要なものです：

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // print_r( $request->header );
    // if (如果不満足我某些自定义的需求条件，那么返回end输出，返回false，握手失败) {
    //    $response->end();
    //     return false;
    // }

    // websocket握手连接算法验证
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(
        sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        )
    );

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $response->end();
});
```

!> `onHandShake`カスタムハンドラを設定した後は、`onOpen`イベントは再びトリガーされません。応用コードで自分で処理する必要があります。`$server->defer`を使用して`onOpen`ロジックを呼び出すことができます。

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // 省略了握手内容
    $response->status(101);
    $response->end();

    global $server;
    $fd = $request->fd;
    $server->defer(function () use ($fd, $server)
    {
      echo "Client connected\n";
      $server->push($fd, "hello, welcome\n");
    });
});
```
### onOpen

?> **`WebSocket`クライアントがサーバーと接続を確立し、ハンドショーを完了した後にこの関数が呼ばれます。**

```php
onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request);
```

* **ヒント**

    * `$request`は[HTTP](/http_server?id=httprequest)リクエストオブジェクトで、クライアントからのハンドショーリクエスト情報を含んでいます
    * `onOpen`イベントの関数内では、[push](/websocket_server?id=push)を使用してクライアントにデータを送信したり、[close](/server/methods?id=close)を使用して接続を閉じたりすることができます
    * `onOpen`イベントのカスタムハンドラはオプションです
### onMessage

?> **サーバーがクライアントからのデータフレームを受信した場合にこの関数が呼ばれます。**

```php
onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
```

* **ヒント**

  * `$frame`は[Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)オブジェクトで、クライアントからのデータフレーム情報を含んでいます
  * `onMessage`のカスタムハンドラは必須であり、設定されなければサーバーは起動できません
  * クライアントからの`ping`フレームは`onMessage`をトリガーしません。バックエンドは自動的に`pong`パケットを返信しますが、[open_websocket_ping_frame](/websocket_server?id=open_websocket_ping_frame)パラメータを手動で設定して処理することもできます

!> `$frame->data`がテキストタイプの場合、エンコーディングは必ず`UTF-8`でなければなりません。これは`WebSocket`プロトコルで規定されています
### onRequest

?> `Swoole\WebSocket\Server`は[Swoole\Http\Server](/http_server)の子クラスであるため、`Http\Server`が提供するすべての`API`と設定項目を使用することができます。Swoole.Http.Serverのセクションを参照してください。

* `[onRequest](/http_server?id=on)のカスタムハンドラを設定すると、`WebSocket\Server`は同時に`HTTP`サーバーとしても機能します
* `[onRequest](/http_server?id=on)のカスタムハンドラを設定しないと、`WebSocket\Server`が`HTTP`リクエストを受け取ると`HTTP 400`エラーページを返します
* `HTTP`を受信してすべての`WebSocket`プッシュをトリガーしたい場合は、スコープの問題に注意が必要です。プロセス指向の場合は`global`を使用して`Swoole\WebSocket\Server`を参照し、オブジェクト指向の場合は`Swoole\WebSocket\Server`をメンバー変数として設定します

#### プロセス指向のコード

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});
$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    global $server;//外部的なserverを呼び出す
    // $server->connections 遍历所有websocket连接用户的fd，给所有用户推送
    foreach ($server->connections as $fd) {
        // 需要先判断是否是正确的websocket连接，否则有可能会push失败
        if ($server->isEstablished($fd)) {
            $server->push($fd, $request->get['message']);
        }
    }
});
$server->start();
```

#### オブジェクト指向のコード

```php
class WebSocketServer
{
    public $server;

    public function __construct()
    {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
        $this->server->on('open', function (Swoole\WebSocket\Server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            foreach ($this->server->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        $this->server->start();
    }
}

new WebSocketServer();
```
### onDisconnect

?> **WebSocketクライアントとサーバーの接続が閉じられた場合にのみ、このイベントがトリガーされます。**

!> Swooleバージョン >= `v4.7.0`で利用可能

```php
onDisconnect(Swoole\WebSocket\Server $server, int $fd)
```

!> `onDisconnect`イベントのカスタムハンドラを設定すると、非WebSocketリクエストや`[onRequest](/websocket_server?id=onrequest)`で`$response->close()`メソッドを呼び出した場合、`onDisconnect`イベントがトリガーされます。一方で、`[onRequest](/websocket_server?id=onrequest)`イベントで正常に終了した場合は、`onClose`や`onDisconnect`イベントはトリガーされません。  
## メソッド

`Swoole\WebSocket\Server`は[Swoole\Server](/server/methods)のサブクラスであるため、`Server`のすべてのメソッドを呼び出すことができます。

`WebSocket`サーバーがクライアントにデータを送信する際は、`Swoole\WebSocket\Server::push`メソッドを使用する必要があります。このメソッドは`WebSocket`プロトコルのパッケージ化を行います。一方で、`[Swoole\Server->send()](/server/methods?id=send)`メソッドは元の`TCP`送信インターフェースです。

`[S
### exist

> **WebSocketクライアントが存在し、状態が`Active`であるかを判断します。**

> `v4.3.0`以降、この`API`は接続の存在を判断するためにのみ使用されます。`isEstablished`を使用して`WebSocket`接続かどうかを判断してください。

```php
Swoole\WebSocket\Server->exist(int $fd): bool
```

* **戻り値**

  * 接続が存在し、`WebSocket`ハンドショーが完了している場合は`true`を返します。
  * 接続が存在しないか、ハンドショーがまだ完了していない場合は`false`を返します。
### pack

> **WebSocketメッセージをパッケージ化します。**

```php
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true, bool $mask = false): string

// v4.4.12バージョンではflagsパラメータに変更されました
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string

Swoole\WebSocket\Frame::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
```

* **パラメータ**

  * **`Swoole\WebSocket\Frame|string $data $data`**

    * **機能**：メッセージ内容
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $opcode`**

    * **機能**：送信されるデータ内容の形式を指定します【デフォルトではテキストです。二進制コンテンツを送信する場合は、$opcodeパラメータをWEBSOCKET_OPCODE_BINARYに設定する必要があります】
    * **デフォルト値**：`WEBSOCKET_OPCODE_TEXT`
    * **その他の値**：`WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **機能**：フレームが完了したかどうか
    * **デフォルト値**：なし
    * **その他の値**：なし

    > `v4.4.12`バージョンから、`finish`パラメータ（`bool`型）は`flags`パラメータ（`int`型）に変更され、`WebSocket`圧縮をサポートします。`finish`は`SWOOLE_WEBSOCKET_FLAG_FIN`の値が`1`であることを意味し、元の`bool`型の値は暗黙的に`int`型に変換されます。この変更は下位互換性に影響しません。

  * **`bool $mask`**

    * **機能**：マスキングを設定するかどうか【`v4.4.12`ではこのパラメータが移除されました】
    * **デフォルト値**：なし
    * **その他の値**：なし

* **戻り値**

  * パッケージ化された`WebSocket`データフレームを返し、`Swoole\Server`クラスの[send()](/server/methods?id=send)メソッドを通じて相手方に送信することができます。

* **例**

```php
$ws = new Swoole\Server('127.0.0.1', 9501 , SWOOLE_BASE);

$ws->set(array(
    'log_file' => '/dev/null'
));

$ws->on('WorkerStart', function (\Swoole\Server $serv) {
});

$ws->on('receive', function ($serv, $fd, $threadId, $data) {
    $sendData = "HTTP/1.1 101 Switching Protocols\r\n";
    $sendData .= "Upgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: IFpdKwYy9wdo4gTldFLHFh3xQE0=\r\n";
    $sendData .= "Sec-WebSocket-Version: 13\r\nServer: swoole-http-server\r\n\r\n";
    $sendData .= Swoole\WebSocket\Server::pack("hello world\n");
    $serv->send($fd, $sendData);
});

$ws->start();
```
### unpack

> **WebSocketデータフレームを解析します。**

```php
Swoole\WebSocket\Server::unpack(string $data): Swoole\WebSocket\Frame|false;
```

* **パラメータ**

  * **`string $data`**

    * **機能**：メッセージ内容
    * **デフォルト値**：なし
    * **その他の値**：なし

* **戻り値**

  * 解析に失敗した場合は`false`を返し、解析に成功した場合は[Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)オブジェクトを返します。
### disconnect

> **WebSocketクライアントに対して閉鎖フレームを送信し、接続を閉じることを主动的に行います。**

> Swooleバージョン >= `v4.0.3`で使用可能

```php
Swoole\WebSocket\Server->disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
```

* **パラメータ**

  * **`int $fd`**

    * **機能**：クライアント接続の`ID`【指定された`$fd`に対応する`TCP`接続が`WebSocket`クライアントではない場合、送信は失敗します】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $code`**

    * **機能**：接続を閉じる状態コード【`RFC6455`に基づいて、アプリケーションによる接続の状態コードは、`1000`または`4000-4999`の範囲です】
    * **デフォルト値**：`SWOOLE_WEBSOCKET_CLOSE_NORMAL`
    * **その他の値**：なし

  * **`string $reason`**

    * **機能**：接続を閉じる理由【`utf-8`形式の文字列で、バイト長さは`125`を超えません】
    * **デフォルト値**：なし
    * **その他の値**：なし

* **戻り値**

  * 送信に成功した場合は`true`を返し、送信に失敗したり、状態コードが不正な場合は`false`を返します。
### isEstablished

> **接続が有効な`WebSocket`クライアント接続であるかを確認します。**

> この関数は`exist`メソッドと異なり、`exist`メソッドは`TCP`接続のみを判断し、ハンドショーが完了した`WebSocket`クライアントかどうかを判断することはできません。

```php
Swoole\WebSocket\Server->isEstablished(int $fd): bool
```

* **パラメータ**

  * **`int $fd`**

    * **機能**：クライアント接続の`ID`【指定された`$fd`に対応する`TCP`接続が`WebSocket`クライアントではない場合、送信は失敗します】
    * **デフォルト値**：なし
    * **その他の値**：なし

* **戻り値**

  * 有効な接続であれば`true`を返し、そうでなければ`false`を返します。
## Websocketデータフレームクラス
### Swoole\WebSocket\Frame

> **v4.2.0バージョンでは、サーバーとクライアントが[Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)オブジェクトを送信するサポートが追加されました。**
> **v4.4.12バージョンでは、「flags」属性が追加され、`WebSocket`圧縮フレームをサポートします。同時に、新しいサブクラス[Swoole\WebSocket\CloseFrame](/websocket_server?id=swoolewebsocketcloseframe)が追加されました。**

一般的な`frame`オブジェクトには以下の属性があります。
常量 | 说明 
---|--- 
fd |  クライアントの`socket id`で、`$server->push`を使用してデータをプッシュする際に必要です    
data |  データ内容で、テキストデータか二進制データかにより、`opcode`の値によって判断できます   
opcode |  `WebSocket`の[データフレームタイプ](/websocket_server?id=数据帧类型)、`WebSocket`プロトコル標準文書を参照してください    
finish |  データフレームが完全であるかどうかを示します。`WebSocket`リクエストは複数のデータフレームに分けて送信される可能性があります（下部はデータフレームの自動結合を実現していますので、受信したデータフレームが不完全であることを心配する必要はありません）。  

このクラスには、[Swoole\WebSocket\Frame::pack()](/websocket_server?id=pack)と[Swoole\WebSocket\Frame::unpack()](/websocket_server?id=unpack)が自動的に付いており、`websocket`メッセージのパッケージ化と解压缩を行います。パラメータの説明は、`Swoole\WebSocket\Server::pack()`および`Swoole\WebSocket\Server::unpack()`と同じです。
### Swoole\WebSocket\CloseFrame

一般的な`閉鎖フレーム close frame`オブジェクトには以下の属性があります。
常量 | 说明 
---|--- 
opcode |  `WebSocket`の[データフレームタイプ](/websocket_server?id=数据帧类型)、`WebSocket`プロトコル標準文書を参照してください    
code |  `WebSocket`の[閉鎖フレーム状態コード](/websocket_server?id=WebSocket断开状态码)、[websocketプロトコルで定義されたエラーコード](https://developer.mozilla.org/zh-CN/docs/Web/API/CloseEvent)を参照してください    
reason |  閉鎖の理由です。明確に指定されていない場合は空です。

サーバーが`close frame`を受信する必要がある場合は、`$server->set`を通じて[open_websocket_close_frame](/websocket_server?id=open_websocket_close_frame)パラメータを有効にする必要があります。
## 常量
### データフレームタイプ
常量 | 对应值 | 说明
---|---|---
WEBSOCKET_OPCODE_TEXT | 0x1 | UTF-8テキスト文字データ
WEBSOCKET_OPCODE_BINARY | 0x2 | 二進制データ
WEBSOCKET_OPCODE_CLOSE | 0x8 | 閉鎖フレームタイプデータ
WEBSOCKET_OPCODE_PING | 0x9 | pingタイプデータ
WEBSOCKET_OPCODE_PONG | 0xa | pongタイプデータ
### 接続状態
常量 | 对应值 | 说明
---|---|---
WEBSOCKET_STATUS_CONNECTION | 1 | 接続が待機ハンドショーに入る
WEBSOCKET_STATUS_HANDSHAKE | 2 | ハンドショー中
WEBSOCKET_STATUS_ACTIVE | 3 | ハンドショーが成功し、ブラウザからデータフレームを待っている
WEBSOCKET_STATUS_CLOSING | 4 | 接続が閉鎖ハンドショー中で、すぐに閉鎖される
### WebSocket閉鎖フレーム状態コード
常量 | 对应值 | 说明
---|---|---
WEBSOCKET_CLOSE_NORMAL | 1000 | 正常に閉鎖され、リンクはすでにタスクを完了しています
WEBSOCKET_CLOSE_GOING_AWAY | 1001 | サーバー側で切断
WEBSOCKET_CLOSE_PROTOCOL_ERROR | 1002 | プロトコルエラーで、接続が中断
WEBSOCKET_CLOSE_DATA_ERROR | 1003 | データエラーで、例えばテキストデータが必要なのに二進制データを受信した場合
WEBSOCKET_CLOSE_STATUS_ERROR | 1005 | 予期された状態コードを受信していないことを示す
WEBSOCKET_CLOSE_ABNORMAL | 1006 | 閉鎖フレームを送信していない
WEBSOCKET_CLOSE_MESSAGE_ERROR | 1007 | フォーマットに合わないデータを受信したために接続が断开される (例えばテキストメッセージにUTF-8以外のデータが含まれている)
WEBSOCKET_CLOSE_POLICY_ERROR | 1008 | 予約されたデータを受信したために接続が断开される。これは一般的な状態コードで、1003や1009の状態コードを使用するのに適さないシナリオを対応
WEBSOCKET_CLOSE_MESSAGE_TOO_BIG | 1009 | 過大なデータフレームを受信したために接続が断开される
WEBSOCKET_CLOSE_EXTENSION_MISSING | 1010 | クライアントがサーバーに1つまたは複数の拡張を要求するが、サーバーが処理しないため、クライアントが接続を断开する
WEBSOCKET_CLOSE_SERVER_ERROR | 1011 | クライアントが予期せぬ状況に遭遇し、リクエストを完了することを阻止したため、サーバーが接続を断开する
WEBSOCKET_CLOSE_TLS | 1015 | 保留。 TLSハンドショーを完了できないために接続が閉鎖される（例えば、サーバーコレクションを検証できない）。
## 选项

> `Swoole\WebSocket\Server`は`Server`のサブクラスであり、`Swoole\WebSocker\Server::set()`メソッドを使用して設定オプションを伝入し、特定のパラメータを設定することができます。
### websocket_subprotocol

> **`WebSocket`サブプロトコルを設定します。**

> 設定後、ハンドショー応答の`HTTP`ヘッダーには`Sec-WebSocket-Protocol: {$websocket_subprotocol}`が追加されます。具体的な使用方法は、「WebSocket」プロトコル関連の「RFC」文書を参照してください。

```php
$server->set([
    'websocket_subprotocol' => 'chat',
]);
```
### open_websocket_close_frame

> **`WebSocket`プロトコルでの閉鎖フレーム（`opcode`が`0x08`のフレーム）を`onMessage`カーネルで受信することを有効にします。デフォルトでは`false`です。**

> 有効に設定すると、`Swoole\WebSocket\Server`の`onMessage`カーネルで、クライアントまたはサーバーから送信された閉鎖フレームを受信できます。開発者はそれを自分の处理に置くことができます。

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_close_frame" => true));
$server->on('open', function
## その他

!> 関連する例程コードは [WebSocket テストユニット](https://github.com/swoole/swoole-src/tree/master/tests/swoole_websocket_server)で見つけることができます
### WebSocketフレーム圧縮（RFC-7692）

?>まず、「websocket_compression」=>trueを設定して圧縮を有効にする必要があります（WebSocketハンドショー時に対端と圧縮支援情報を交換します）。その後、具体的なフレームに圧縮を適用するために `flag SWOOLE_WEBSOCKET_FLAG_COMPRESS` を使用できます。

#### 例

* **サーバー**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->set(['websocket_compression' => true]);
$server->on('message', function (Server $server, Frame $frame) {
    $server->push(
        $frame->fd,
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
    // $server->push($frame->fd, $frame); //またはサーバーはクライアントのフレームオブジェクトをそのまま転送することもできます
});
$server->start();
```

* **クライアント**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->set(['websocket_compression' => true]);
    $cli->upgrade('/');
    $cli->push(
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
});
```
### Pingフレームの送信

?> WebSocketは長い接続であるため、一定時間通信がない場合、接続が切断される可能性があります。このような時にはホストメトリックスが必要です。WebSocketプロトコルにはPingとPongの2種類のフレームが含まれており、定期的にPingフレームを送信して長い接続を維持することができます。

#### 例

* **サーバー**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->on('message', function (Server $server, Frame $frame) {
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    $server->push($frame->fd, $pingFrame);
});
$server->start();
```

* **クライアント**

```php
use Swoole\WebSocket\Frame;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->set(['websocket_compression' => true]);
    $cli->upgrade('/');
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    // Pingを送信
    $cli->push($pingFrame);
    
    // PONGを受信
    $pongFrame = $cli->recv();
    var_dump($pongFrame->opcode === WEBSOCKET_OPCODE_PONG);
});
```
