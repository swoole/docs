```
# HTTPサーバー

?> 完全协程化されたHTTPサーバーの実装で、`Co\Http\Server`はHTTP解析のパフォーマンスのためにC++で書かれており、PHPで書かれた[Co\Server](/coroutine/server)のサブクラスではありません。

[Http\Server](/http_server)との違い：

* 実行時に動的に作成・破壊が可能
* 接続の処理は別の子协程で完了し、クライアントの接続の`Connect`、`Request`、`Response`、`Close`は完全に串行で行われます

!> `v4.4.0`またはそれ以上のバージョンが必要です

!> 编译時に[HTTP2を有効にする](/environment?id=compile_options)場合、HTTP2プロトコルのサポートがデフォルトで有効になります。`Swoole\Http\Server`のように[open_http2_protocol](/http_server?id=open_http2_protocol)を構成する必要はありません（注：**v4.4.16以下のバージョンではHTTP2サポートに既知のBUGがありますので、アップグレード後に使用してください**）

## 短名

`Co\Http\Server`の短名を使用できます。

## 方法

### __construct()

```php
Swoole\Coroutine\Http\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **パラメータ** 

    * **`string $host`**
      * **機能**：监听するIPアドレス【もし本地UNIXSocketであれば、`unix://tmp/your_file.sock`のような形式で記入してください】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：监听ポート 
      * **デフォルト値**：0（ランダムに空闲ポートを监听）
      * **その他の値**：0〜65535

    * **`bool $ssl`**
      * **機能**：SSL/TLSトンネル暗号化を有効にするかどうか
      * **デフォルト値**：false
      * **その他の値**：true
      
    * **`bool $reuse_port`**
      * **機能**：ポート再利用特性を有効にするかどうか、有効にすると複数のサービスが同じポートを使用できます
      * **デフォルト値**：false
      * **その他の値**：true


### handle()

パラメータ`$pattern`で指定されたパスに対するHTTPリクエストを処理する回调関数を登録します。

```php
Swoole\Coroutine\Http\Server->handle(string $pattern, callable $fn): void
```

!> [Server::start](/coroutine/server?id=start)の前に処理関数を設定する必要があります

  * **パラメータ** 

    * **`string $pattern`**
      * **機能**：URLパスを設定【例えば`/index.html`、ここで`http://domain`は传入できません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`callable $fn`**
      * **機能**：処理関数、使用方法は`Swoole\Http\Server`の[OnRequest](/http_server?id=on)回调を参照してください、ここでは詳述しません
      * **デフォルト値**：なし
      * **その他の値**：なし      

      例：

      ```php
      function callback(Swoole\Http\Request $req, Swoole\Http\Response $resp) {
          $resp->end("hello world");
      }
      ```

  * **ヒント**

    * サーバーは`Accept`（接続建立）が成功した後、自動的に协程を作成してHTTPリクエストを受け付けます
    * `$fn`は新しい子协程空間内で実行されるため、関数内で再び协程を作成する必要はありません
    * クライアントは[KeepAlive](/coroutine_client/http_client?id=keep_alive)をサポートしており、子协程は新しいリクエストを受け続け、退出しません
    * クライアントが`KeepAlive`をサポートしていない場合、子协程はリクエストの受け入れを停止し、接続を閉じて退出します

  * **注意**

    !> -`$pattern`が同じパスを設定した場合、新しい設定が古い設定を上書きします；  
    -根路径の処理関数を未設定で、リクエストのパスにどの`$pattern`も一致しない場合、Swooleは`404`エラーを返します；  
    -`$pattern`は文字列マッチングを使用しており、ワイルドカードや正規表現はサポートせず、大小写を区別せず、マッチングアルゴリズムはプレフィックスマッチングです。例えば、urlが`/test111`の場合、`/test`というルールに一致し、一致した後はマッチングを抜け出し、後ろの設定を無視します；  
    -根路径の処理関数を設定することをお勧めし、回调関数内で`$request->server['request_uri']`を使用してリクエストルーティングを行います。


### start()

?> **サーバーを開始します。** 

```php
Swoole\Coroutine\Http\Server->start();
```


### shutdown()

?> **サーバーを終了します。** 

```php
Swoole\Coroutine\Http\Server->shutdown();
```

## 完全な例

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
```
```
