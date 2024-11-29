```
# 協程HTTP/WebSocketクライアント

協程版の`HTTP`クライアントは純粋に`C`で書かれており、どんなサードパーティの拡張ライブラリにも依存していません。非常に高いパフォーマンスを持っています。

* `Http-Chunk`、`Keep-Alive`特性をサポートし、`form-data`フォーマットに対応しています
* `HTTP`プロトコルのバージョンは`HTTP/1.1`です
* `WebSocket`クライアントにアップグレードする機能がサポートされています
* `gzip`圧縮フォーマットは`zlib`ライブラリに依存する必要があります
* クライアントは核心的な機能のみを実現しており、実際のプロジェクトでは[Saber](https://github.com/swlib/saber)を使用することをお勧めします


## 属性


### errCode

エラー状態コードです。`connect/send/recv/close`が失敗したりタイムアウトになったりした場合、自動的に`Swoole\Coroutine\Http\Client->errCode`の値が設定されます

```php
Swoole\Coroutine\Http\Client->errCode: int
```

`errCode`の値は`Linux errno`に等しく、`socket_strerror`関数を使用してエラーコードをエラー情報に変換することができます。

```php
// connectが拒否された場合、エラーコードは111です
//タイムアウトした場合、エラーコードは110です
echo socket_strerror($client->errCode);
```

!> 参考：[Linuxエラーコード一覧](/other/errno?id=linux)


### body

前回のリクエストのレスポンスボディを保存しています。

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```


### statusCode

HTTP状態コードです。例えば200、404などです。状態コードが負数の場合、接続に問題があることを意味します。[もっと見る](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```


## 方法


### __construct()

コンストラクタです。

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

  * **パラメータ** 

    * **`string $host`**
      * **機能**：ターゲットサーバーホストアドレス【IPまたはドメインで、底層では自動的にドメイン解析を行います。もしローカルUNIXソケットであれば、`unix://tmp/your_file.sock`のような形式で記入する必要があります。ドメインであれば、プロトコルの頭`http://`または`https://`は必要ありません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：ターゲットサーバーホストポート
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`bool $ssl`**
      * **機能**：SSL/TLSトンネル暗号化を有効にするかどうかを指定します。ターゲットサーバーがhttpsの場合、`$ssl`パラメータを`true`に設定する必要があります
      * **デフォルト値**：`false`
      * **その他の値**：なし

  * **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```


### set()

クライアントパラメータを設定します。

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

この方法は`Swoole\Client->set`が受け取るパラメータと完全に同じであり、[Swoole\Client->set](/client?id=set) 方法のドキュメントを参照してください。

`Swoole\Coroutine\Http\Client`は追加でいくつかのオプションを提供し、HTTPおよびWebSocketクライアントを制御します。

#### 追加オプション

##### 超時制御

`timeout`オプションを設定し、HTTPリクエストのタイムアウト検出を有効にします。単位は秒で、最小粒度はミリ秒まで支持されています。

```php
$http->set(['timeout' => 3.0]);
```

* 接続タイムアウトまたはサーバーによって接続が閉じられた場合、`statusCode`は`-1`に設定されます
* 約束の時間内にサーバーがレスポンスを返答しなかった場合、リクエストがタイムアウトし、`statusCode`は`-2`に設定されます
* リクエストがタイムアウトした後、底層では自動的に接続を切断します
* [クライアントタイムアウトルール](/coroutine_client/init?id=超时规则)を参照

##### keep_alive

`keep_alive`オプションを設定し、HTTPの長连接を有効または無効にします。

```php
$http->set(['keep_alive' => false]);
```

##### websocket_mask

> RFCによると、v4.4.0以降はデフォルトで有効ですが、パフォーマンスの損失を引き起こす可能性があります。サーバー側で強制的に要求がない場合は、falseに設定して無効にすることができます

WebSocketクライアントでマスクを有効または無効にします。デフォルトは有効です。有効にすると、WebSocketクライアントが送信するデータに対してマスクを使用してデータ変換を行います。

```php
$http->set(['websocket_mask' => false]);
```

##### websocket_compression

> v4.4.12またはそれ以降のバージョンが必要です

trueにすると、フレームに対してzlib圧縮を**許可**しますが、圧縮が可能なかどうかはサーバーが圧縮を処理できるかどうかに依存します（ハンドシェイク情報に基づいて決定されます。RFC-7692を参照）。

特定のフレームに対して圧縮を実際に適用するには、flagsパラメータ`SWOOLE_WEBSOCKET_FLAG_COMPRESS`を必要とします。具体的な使用方法は[このセクション](/websocket_server?id=websocket帧压缩-（rfc-7692）)を参照してください。

```php
$http->set(['websocket_compression' => true]);
```

##### write_func
> v5.1.0またはそれ以降のバージョンが必要です

write_func回调関数を設定し、CURLのWRITE_FUNCTIONオプションに似ています。これはストリーム形式のレスポンス内容を処理するために使用できます。

例えば、OpenAI ChatGPTのEvent Stream出力内容です。

> write_funcを設定した後、getContent()メソッドを使用してレスポンス内容を取得することはできなくなります。また、$client->bodyも空になります。  
> write_func回调関数の中で、$client->close()を使用してレスポンス内容の受信を停止し、接続を閉じることができます

```php
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->set(['write_func' => function ($client, $data) {
    var_dump($data);
}]);
$cli->get('/');
```


### setMethod()

リクエスト方法を設定します。現在のリクエストにのみ有効で、リクエストを送信した後はすぐにmethod設定をクリアします。

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **パラメータ** 

    * **`string $method`**
      * **機能**：方法を設定します 
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> HTTP標準の方法名でなければならず、$methodが間違っている場合、HTTPサーバーがリクエストを拒否する可能性があります

  * **例**

```php
$http->setMethod("PUT");
```


### setHeaders()

HTTPリクエストヘッダを設定します。

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **パラメータ** 

    * **`array $headers`**
      * **機能**：リクエストヘッダを設定します 【必ずキーと値の対応する配列でなければなりません。底層では自動的に`$key`: `$value`形式のHTTP標準ヘッダフォーマットにマッピングされます】
      * **デフォルト値**：なし
      * **その他の値**：なし

!> setHeadersで設定されたHTTPヘッダは、Coroutine\Http\Clientオブジェクトが生存している間の各リクエストに永久に有効です。setHeadersを再呼びすると、前回の設定が上書きされます


### setCookies()

Cookieを設定します。値はurlencodeでエンコードされます。元の情報を保持したい場合は、Cookieという名前のheaderを自分でsetHeadersで設定してください。

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **パラメータ** 

    * **`array $cookies`**
      * **機能**：COOKIEを設定します 【必ずキーと値の対応する配列でなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし
```
> COOKIEを設定した後、クライアントオブジェクトが生存している間はずっと保持されます。  
>  
> サーバー側で積極的に設定されたCOOKIEは、cookies配列に合流し、$client->cookiesプロパティを読むことで、現在のHTTPクライアントのCOOKIE情報を取得できます。  
>  
> setCookiesメソッドを繰り返すと、現在のCookies状態を上書きし、それにより以前にサーバー側から送信されたCOOKIEや以前に積極的に設定されたCOOKIEが失われます。

### setData()

HTTPリクエストのパケットボディを設定します。

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **引数** 

    * **`string|array $data`**
      * **機能**：リクエストのパケットボディを設定する
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **ヒント**

    * `$data`を設定した後も`$method`が設定されていないと、底层は自動的にPOSTに設定されます。
    * `$data`が配列の場合且つContent-Typeがurlencodedフォーマットの場合、底层は自動的にhttp_build_queryを行います。
    * addFileまたはaddDataを使用してform-dataフォーマットを有効にした場合、$dataの値が文字列の場合には無視されます（フォーマットが異なるため）、しかし配列の場合には底层はform-dataフォーマットで配列のフィールドを追加します。

### addFile()

POSTでファイルを追加します。

!> addFileを使用すると、POSTのContent-Typeが自動的にform-dataに変更されます。addFileの底层はsendfileに基づいており、超大ファイルの非同期送信をサポートできます。

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **引数** 

    * **`string $path`**
      * **機能**：ファイルの路径【必須パラメータ、空ファイルや存在しないファイルは大吉】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $name`**
      * **機能**：フォームの名前【必須パラメータ、FILESパラメータのkey】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $mimeType`**
      * **機能**：ファイルのMIMEフォーマット【オプションパラメータ、底层はファイルの拡張名に基づいて自動的に推測されます】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $filename`**
      * **機能**：ファイル名【オプションパラメータ】
      * **デフォルト値**：$pathのbasename($path)
      * **その他の値**：なし

    * **`int $offset`**
      * **機能**：アップロードファイルのオフセット【オプションパラメータ、ファイルの途中からデータ転送を始めたい場合は指定できます。この特性は断点継伝をサポートするために使用できます。】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $length`**
      * **機能**：送信データのサイズ【オプションパラメータ】
      * **デフォルト値**：ファイルの全サイズ默认为
      * **その他の値**：なし

  * **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```

### addData()

文字列を使用してアップロードファイルの内容を構築します。 

!> addDataは `v4.1.0` 以上のバージョンで利用可能です

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **引数** 

    * **`string $data`**
      * **機能**：データ内容【必須パラメータ、最大長さは[buffer_output_size](/server/setting?id=buffer_output_size)を超えてはいけません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $name`**
      * **機能**：フォームの名前【必須パラメータ、$_FILESパラメータのkey】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $mimeType`**
      * **機能**：ファイルのMIMEフォーマット【オプションパラメータ、デフォルトは`application/octet-stream`】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $filename`**
      * **機能**：ファイル名【オプションパラメータ、デフォルトは$name】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```

### get()

GETリクエストを发起します。

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **引数** 

    * **`string $path`**
      * **機能**：URL路径を設定します【例えば`/index.html`、ここで`http://domain`は传入できません】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```

!> getを使用すると、setMethodで設定されたリクエスト方法を無視し、強制的にGETを使用します


### post()

POSTリクエストを发起します。

```php
Swoole\Coroutine\Http\Client->post(string $path, mixed $data): void
```

  * **引数** 

    * **`string $path`**
      * **機能**：URL路径を設定します【例えば`/index.html`、ここで`http://domain`は传入できません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`mixed $data`**
      * **機能**：リクエストのパケットボディデータ
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> $dataが配列の場合、底层は自動的にx-www-form-urlencodedフォーマットのPOSTコンテンツをパッケージ化し、Content-Typeをapplication/x-www-form-urlencodedに設定します

  * **注意**

    !> postを使用すると、setMethodで設定されたリクエスト方法を無視し、強制的にPOSTを使用します

  * **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```


### upgrade()

WebSocket接続にアップグレードします。

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **引数** 

    * **`string $path`**
      * **機能**：URL路径を設定します【例えば`/`、ここで`http://domain`は传入できません】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **ヒント**

    * いくつかの場合、リクエストは成功しているにもかかわらず、upgradeがtrueを返しましたが、サーバーはHTTPステータコードを101に設定せず、200または403にしていました。これは、サーバーがハンドシェイクリクエストを拒否していることを意味します。
    * WebSocketハンドシェイクが成功した後、pushメソッドを使用してサーバーにメッセージをプッシュしたり、recvを呼び出してメッセージを受信したりすることができます。
    * upgradeは[协程调度](/coroutine?id=协程调度)を一度発生させます。

  * **例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### push()

WebSocketサーバーにメッセージをプッシュします。

!> `push`方法は`upgrade`が成功した後にのみ実行できます  
`push`方法は[协程调度](/coroutine?id=协程调度)を生成せず、送信バッファに書き込むとすぐに戻ります

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **引数** 

    * **`mixed $data`**
      * **機能**：送信したいデータ内容【デフォルトは`UTF-8`テキスト形式です。他の形式のエンコードやバイナリデータの場合は`WEBSOCKET_OPCODE_BINARY`を使用してください】
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> Swooleバージョン >= v4.2.0 `$data`は[Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)オブジェクトを使用でき、さまざまなフレームタイプを送信できます

    * **`int $opcode`**
      * **機能**：操作タイプ
      * **デフォルト値**：`WEBSOCKET_OPCODE_TEXT`
      * **その他の値**：なし

      !> `$opcode`は有効な`WebSocket OPCode`でなければ失敗し、エラー情報を印刷します`opcode max 10`

    * **`int|bool $finish`**
      * **機能**：操作タイプ
      * **デフォルト値**：`SWOOLE_WEBSOCKET_FLAG_FIN`
      * **その他の値**：なし

      !> v4.4.12バージョンより、`finish`パラメータ（`bool`型）は`flags`（`int`型）に変更され、`WebSocket`圧縮をサポートしています。`finish`は`SWOOLE_WEBSOCKET_FLAG_FIN`の値が`1`になります。元の`bool`型値は暗黙的に`int`型に変換されます。この変更は下位互換性には影響しません。さらに圧縮`flag`は`SWOOLE_WEBSOCKET_FLAG_COMPRESS`です。

  * **戻り値**

    * 送信に成功すると`true`が返ります
    * 接続が存在しない、すでに閉じている、WebSocketが完了していない場合は送信に失敗し`false`が返ります

  * **エラーコード**


エラーコード | 説明
---|---
8502 | 誤ったOPCode
8503 | サーバーに接続されていないか、接続がすでに閉じられている
8504 | ハンドシェイクに失敗した


### recv()

メッセージを受信します。WebSocketのみに使用し、`upgrade()`と組み合わせて使用する必要があります。例を参照してください

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **引数** 

    * **`float $timeout`**
      * **機能**：`upgrade()`を呼び出してWebSocket接続にアップグレードする際にこのパラメータが有効です
      * **値の単位**：秒【浮点型をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照してください
      * **その他の値**：なし

      !>タイムアウトを設定するには、指定されたパラメータを優先的に使用し、次に`set`方法で传入された`timeout`設定を使用します  
  
  * **戻り値**

    * 成功した場合はフレームオブジェクトが返ります
    * 失敗した場合は`false`が返ります。また、`Swoole\Coroutine\Http\Client`の`errCode`属性をチェックしてください。协程クライアントには`onClose`回调がなく、接続が閉じられたときに`recv`が呼び出された場合、`false`が返ります并且`errCode=0`
 
  * **例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```


### download()

HTTPを通じてファイルをダウンロードします。

!> downloadとget方法の違いは、downloadはデータを受け取った後、磁盘に書き込むことであり、メモリ上でHTTP Bodyを接続するわけではありません。したがって、downloadは少量のメモリを使用即可で、超大規模なファイルのダウンロードを完了できます。

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename,  int $offset = 0): bool
```

  * **引数** 

    * **`string $path`**
      * **機能**：URLパスを設定します
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $filename`**
      * **機能**：ダウンロードされた内容を書き込むファイルのPathを指定します【自動的に`downloadFile`属性に書き出されます】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $offset`**
      * **機能**：ファイルに書き込むオフセットを指定します【このオプションは断点継伝をサポートするために使用でき、HTTPヘッダ`Range:bytes=$offset`と組み合わせて使用できます】
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> `$offset`が`0`の場合、ファイルが存在している場合は、基層は自動的にこのファイルをクリアします

  * **戻り値**

    * 成功した場合は`true`が返ります
    * ファイルを開け失败したり、基層の`fseek()`が失敗した場合は`false`が返ります

  * **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```


### getCookies()

HTTP応答のcookie内容を取得します。

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> Cookie情報はurldecodeでデコードされます。元のCookie情報を取得したい場合は、以下に従って自行解析してください

#### 重名する`Cookie`または`Cookie`の元のヘッダ情報获取

```php
var_dump($client->set_cookie_headers);
```


### getHeaders()

HTTP応答のヘッダ情報を返します。

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```


### getStatusCode()

HTTP応答の状態码を取得します。

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **ヒント**

    * **状態码が負数の場合、接続に問題があることを意味します。**


状態码 | v4.2.10 以上バージョンに対応する定数 | 説明

---|---|---

-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | 接続タイムアウト、サーバーがポートを聴いていないか、ネットワークが断線しています。具体的なネットワークエラーコードは$errCodeから読むことができます

-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | 要求タイムアウト、サーバーが指定されたtimeout時間内にresponseを返していません

-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | クライアントの要求を送信した後、サーバーが強制的に接続を切断しました
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | クライアントの送信に失敗しました(この定数はSwooleバージョン>=`v4.5.9`で使用できますが、それ以前のバージョンでは状態码を使用してください)


### getBody()

HTTP応答のパケット本体内容を取得します。

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```


### close()

接続を閉じます。

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> `close`後、もし再度`get`、`post`などの方法を求める場合、Swooleは自動的にサーバーに再接続してくれます。


### execute()

より低レベルのHTTP request方法であり、コード内で[setMethod](/coroutine_client/http_client?id=setmethod)と[setData](/coroutine_client/http_client?id=setdata)などのインターフェースを呼び出してrequestの方法とデータを設定する必要があります。

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **例**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```
## 関数

`Coroutine\Http\Client` の使用を容易にするために、3つの関数が追加されました：

!> Swooleバージョン >= `v4.6.4` で利用可能


### request()

指定されたHTTP methodでリクエストを送信します。

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```


### post()

`POST`リクエストを送信するために使用されます。

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```


### get()

`GET`リクエストを送信するために使用されます。

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```

### 使用例

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
