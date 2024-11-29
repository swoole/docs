```
# Websocketサーバー

?> 完全协程化されたWebsocketサーバー実装で、[Coroutine\Http\Server](/coroutine/http_server)を継承しています。底層では`Websocket`プロトコルのサポートが提供されており、ここでは詳述しません。違いだけを述べます。

!> このセクションはv4.4.13以降で利用できます。

## 完全な例

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    $ws->close();
                    break;
                }
                $ws->push("Hello {$frame->data}!");
                $ws->push("How are you, {$frame->data}?");
            }
        }
    });

    $server->handle('/', function (Request $request, Response $response) {
        $response->end(<<<HTML
    <h1>Swoole Websocket Server</h1>
    <script>
var wsServer = 'ws://127.0.0.1:9502/websocket';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("Connected to Websocket server.");
    websocket.send('hello');
};

websocket.onclose = function (evt) {
    console.log("Disconnected");
};

websocket.onmessage = function (evt) {
    console.log('Retrieved data from server: ' + evt.data);
};

websocket.onerror = function (evt, e) {
    console.log('Error occured: ' + evt.data);
};
</script>
HTML
        );
    });

    $server->start();
});
```

## 群発例

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        global $wsObjects;
        $objectId = spl_object_id($ws);
        $wsObjects[$objectId] = $ws;
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                unset($wsObjects[$objectId]);
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    unset($wsObjects[$objectId]);
                    $ws->close();
                    break;
                }
                foreach ($wsObjects as $obj) {
                    $obj->push("Server：{$frame->data}");
                }
            }
        }
    });
    $server->start();
});
```

## 処理プロセス

* `$ws->upgrade()`：クライアントに`Websocket`のハンドシェイクメッセージを送信する
* `while(true)`ループでメッセージの受信と送信を処理する
* `$ws->recv()`：`Websocket`のメッセージフレームを受信する
* `$ws->push()`：対端にデータフレームを送信する
* `$ws->close()`：接続を閉じる

!> `$ws`は`Swoole\Http\Response`のオブジェクトであり、各メソッドの使用方法は以下を参照してください。

## メソッド

### upgrade()

`Websocket`ハンドシェイク成功メッセージを送信する。

!> [非同期スタイル](/http_server)のサーバーでは使用しないでください。

```php
Swoole\Http\Response->upgrade(): bool
```

### recv()

`Websocket`のメッセージを受信する。

!> [非同期スタイル](/http_server)のサーバーでは使用しないでください。`recv`メソッドを呼び出すと、現在の协程を[挂起](/coroutine?id=协程调度)し、データが到来するまで协程の実行を待つことになります。

```php
Swoole\Http\Response->recv(float $timeout = 0): Swoole\WebSocket\Frame | false | string
```

* **戻り値**

  * メッセージを成功受信した場合は、`Swoole\WebSocket\Frame`オブジェクトが返され、[Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)を参照してください。
  * 失敗した場合は`false`が返され、[swoole_last_error()](/functions?id=swoole_last_error)を使用してエラーコードを取得してください。
  * 接続が閉じられた場合は空文字が返されます。
  * 戻り値の処理は[群发示例](/coroutine/ws_server?id=群发示例)を参照してください。

### push()

`Websocket`データフレームを送信する。

!> [非同期スタイル](/http_server)のサーバーでは使用しないでください。大容量のパケットを送信するとき、可写イベントを監視する必要があり、それによって多次の[协程切换](/coroutine?id=协程调度)が発生することがあります。

```php
Swoole\Http\Response->push(string|object $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

* **パラメータ**

  !> 渡された`$data`が[Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)オブジェクトの場合、その後のパラメータは無視され、さまざまなフレームタイプを送信することができます。

  * **`string|object $data`**

    * **機能**：送信したい内容
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $opcode`**

    * **機能**：送信データの内容のフォーマットを指定する 【テキストを默认で送信します。バイナリコンテンツを送信する場合、`$opcode`パラメータを`WEBSOCKET_OPCODE_BINARY`に設定する必要があります】
    * **デフォルト値**：`WEBSOCKET_OPCODE_TEXT`
    * **その他の値**：`WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **機能**：送信が完了したかどうか
    * **デフォルト値**：`true`
    * **その他の値**：`false`

### close()

`Websocket`接続を閉じる。

!> [非同期スタイル](/http_server)のサーバーでは使用しないでください。v4.4.15以前のバージョンでは、`Warning`を誤って報告することがありますので、無視してください。

```php
Swoole\Http\Response->close(): bool
```

この方法は直接`TCP`接続を切断し、`Close`フレームを送信しません。これは`WebSocket\Server::disconnect()`メソッドとは異なります。
接続を閉じる前に`push()`メソッドを使用して`Close`フレームを送信し、クライアントに通知することができます。

```php
$frame = new Swoole\WebSocket\CloseFrame;
$frame->reason = 'close';
$ws->push($frame);
$ws->close();
```
