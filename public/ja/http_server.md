# Http\Server

?> `Http\Server`は[Server](/server/init)から派生しているため、[Server](/server/init)が提供するすべての`API`と設定項目を使用でき、プロセスモデルも同じです。[Server](/server/init)のセクションを参照してください。

内蔵の`HTTP`サーバーのサポートを使用して、数行のコードで高並行性、高パフォーマンス、[非同期IO](/learn?id=同步io异步io)の多プロセス`HTTP`サーバーを作成できます。

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

`Apache bench`ツールを使用してストレージテストを行うと、`Inter Core-I5 4核 + 8Gメモリ`の普通のPCマシン上で、`Http\Server`は約`11万QPS`に達することができます。

`PHP-FPM`、`Golang`、`Node.js`の組み込み`Http`サーバーをはるかに超えています。パフォーマンスはほぼ`Nginx`の静的ファイル処理に近いです。

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **HTTP2プロトコルの使用**

  * `SSL`での`HTTP2`プロトコルを使用するには`openssl`をインストールする必要があり、高バージョンの`openssl`は`TLS1.2`、`ALPN`、`NPN`をサポートする必要があります。
  * 编成時には`--enable-http2`を使用して開启する必要があります。
  * Swoole5からは、http2プロトコルがデフォルトで有効になっています。

```shell
./configure --enable-openssl --enable-http2
```

`HTTP`サーバーの[open_http2_protocol](/http_server?id=open_http2_protocol)を`true`に設定します。

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Nginx + Swooleの設定**

!> `Http\Server`は`HTTP`プロトコルのサポートが完全ではないため、動的リクエスト処理にのみ使用し、前端に`Nginx`を代理として追加することをお勧めします。

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> `$request->header['x-real-ip']`を読み取ることで、クライアントの実際の`IP`を取得できます。
## 方法
### on()

?> **イベントハンドラ関数を登録します。**

?> [Serverのハンドラ](/server/events)と同じですが、異なる点は以下の通りです：

  * `Http\Server->on`は[onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)ハンドラ設定を受け付けません
  * `Http\Server->on`は新しいイベントタイプ`onRequest`を追加で受け付け、クライアントからのリクエストは`Request`イベントの実行になります

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

完全なHTTPリクエストを受け取った後、この関数が呼び出されます。ハンドラ関数には2つのパラメータがあります：

* [Swoole\Http\Request](/http_server?id=httpRequest)、`HTTP`リクエスト情報オブジェクト、`header/get/post/cookie`などの関連情報を含んでいます
* [Swoole\Http\Response](/http_server?id=httpResponse)、`HTTP`応答オブジェクト、`cookie/header/status`などの`HTTP`操作をサポートしています

!> [onRequest](/http_server?id=on)ハンドラ関数が返されたとき、下層は`$request`と`$response`オブジェクトを破棄します
### start()

?> **HTTPサーバーを起動します**

?> 启動後、ポートを聞き、新しい`HTTP`リクエストを受け取ります。

```php
Swoole\Http\Server->start();
```
## Swoole\Http\Request

`HTTP`リクエストオブジェクトは、`HTTP`クライアントリクエストに関連する情報を保存しており、`GET`、`POST`、`COOKIE`、`Header`などが含まれています。

!> `$Http Request`オブジェクトを`&`記号で引用しないでください
### header

?> **`HTTP`リクエストのヘッダ情報です。タイプは配列で、すべての`key`は小文字です。**

```php
Swoole\Http\Request->header: array
```

* **例**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```
### server

?> **`HTTP`リクエストに関連するサーバー情報です。**

?> `PHP`の`$_SERVER`配列に相当します。`HTTP`リクエストの方法、`URL`パス、クライアント`IP`などの情報を含んでいます。

```php
Swoole\Http\Request->server: array
```

配列の`key`はすべて小文字であり、`PHP`の`$_SERVER`配列と一致しています。

* **例**

```php
echo $request->server['request_time'];
```
key | 说明
---|---
query_string | GETのパラメータ、例：id=1&cid=2。GETのパラメータがない場合、この項目は存在しません。
request_method | request_methodは、GET/POSTなどです。
request_uri | GETのパラメータがないアクセスURL、例：/favicon.ico
path_info | request_uriと同じです。
request_time | request_timeはWorkerで設定され、SWOOLE_PROCESSモードでdispatchプロセスが存在するため、実際のパケット受信時間とは異なる可能性があります。特に、リクエスト量がサーバーの処理能力を超える場合、request_timeは実際のパケット受信時間よりも大幅に遅れることがあります。これは、$server->getClientInfoメソッドを使用してlast_timeを取得することで正確なパケット受信時間を得ることができます。
request_time_float | request_startのタイムスタンプ、微秒単位、floatタイプ、例：1576220199.2725
server_protocol | server_protocolは、serverのプロトコルバージョン番号です。HTTPはHTTP/1.0またはHTTP/1.1、HTTP2はHTTP/2です。
server_port | server_portは、serverが聞き取るポート番号です。
remote_port | clientのポート番号です。
remote_addr | clientのIPアドレスです。
master_time | connection上次通信時間
### get

?> **HTTP请求のGETパラメータは、PHPの$_GETに相当し、配列の形式をとります。**

```php
Swoole\Http\Request->get: array
```

* **例**

```php
// 例：index.php?hello=123
echo $request->get['hello'];
// 全てのGETパラメータを取得
var_dump($request->get);
```

* **注意**

!> HASH攻撃を防ぐため、GETパラメータは最大で128個を超えてはなりません
### post

?> **HTTP请求のPOSTパラメータは、配列の形式をとります**

```php
Swoole\Http\Request->post: array
```

* **例**

```php
echo $request->post['hello'];
```

* **注意**
!> - POSTとHeaderの合計サイズは、package_max_lengthの設定を超えてはならず、そうでなければ悪意のあるリクエストとみなされます  
- POSTパラメータの数は最大で128個を超えてはなりません
### cookie

?> **HTTP请求に付随するCOOKIE情報は、キーと値のペアの配列の形式をとります。**

```php
Swoole\Http\Request->cookie: array
```

* **例**

```php
echo $request->cookie['username'];
```
### files

?> **アップロードされたファイルの情報です。**

?> formという名前のkeyを持つ二次元配列の型です。PHPの$_FILESと同じです。最大ファイルサイズはpackage_max_lengthの設定を超えてはなりません。Swooleは報文を解析する際にメモリを消費するため、報文が大きくなるほどメモリが消費されます。そのため、大きなファイルアップロードをSwoole\Http\Serverで処理したり、ユーザーが自分で断片継続アクセス機能を設計したりしないでください。

```php
Swoole\Http\Request->files: array
```

* **例**

```php
Array
(
    [name] => facepalm.jpg // ブラウザでアップロードしたときに渡されたファイル名前
    [type] => image/jpeg // MIMEタイプ
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // アップロードされた一時的なファイル、ファイル名は/tmp/swoole.upfileで始まります
    [error] => 0
    [size] => 15476 // ファイルのサイズ
)
```

* **注意**

!> Swoole\Http\Requestオブジェクトが破棄されると、アップロードされた一時的なファイルは自動的に削除されます
### getContent()

!> Swooleバージョン >= `v4.5.0`で利用可能です。低バージョンでは别名`rawContent`（この别名は永久に残り、向下兼容性を持っています）

?> **原始のPOSTパケット体を取得します。**

?> `application/x-www-form-urlencoded`形式でないHTTP POSTリクエストに使用されます。原始POSTデータを返し、この関数はPHPの`fopen('php://input')`と同等です。

```php
Swoole\Http\Request->getContent(): string|false
```

  * **戻り値**

    * 成功した場合は報文を返し、コンテキスト接続がない場合は`false`を返します

!> 一部のシナリオでは、サーバーはHTTP POSTリクエストパラメータを解析する必要がないため、[http_parse_post](/http_server?id=http_parse_post)の設定を通じて、POSTデータ解析を無効にすることができます。
### getData()

?> **完整的原始Http请求报文を取得します。Http2では使用できません。Http HeaderとHttp Bodyを含みます**

```php
Swoole\Http\Request->getData(): string|false
```

  * **戻り値**

    * 成功した場合は報文を返し、コンテキスト接続がないかHttp2モードでの場合は`false`を返します
### create()

?> **Swoole\Http\Requestオブジェクトを作成します。**

!> Swooleバージョン >= `v4.6.0`で利用可能です

```php
Swoole\Http\Request->create(array $options): Swoole\Http\Request
```

  * **パラメータ**

    * **`array $options`**
      * **機能**：オプションパラメータで、Requestオブジェクトの設定を設定します

| パラメータ | デフォルト | 説明 |
| --- | --- | --- |
| [parse_cookie](/http_server?id=http_parse_cookie) | true | Cookieを解析するかどうかを設定します。 |
| [parse_body](/http_server?id=http_parse_post) | true | Http Bodyを解析するかどうかを設定します。 |
| [parse_files](/http_server?id=http_parse_files) | true | 上传文件解析開關を設定します。 |
| enable_compression | true、もしサーバーが圧縮されたパケットをサポートしていない場合はデフォルト値falseです。 | 压縮を有効にするかどうかを設定します。 |
| compression_level | 1 | 压縮レベルを設定します。範囲は1-9で、レベルが高いほど圧縮後のサイズが小さくなりますが、CPUの消費も多くなります。 |
| upload_tmp_dir | /tmp | 一時的なファイルの保存先として、ファイルアップロードに使用します。 |

  * **戻り値**

    * Swoole\Http\Requestオブジェクトを返します。

* **例**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```
### parse()

?> **HTTP请求データパケットを解析し、成功した解析のデータパケットの長さを返します。**

!> Swooleバージョン >= `v4.6.0`で利用可能です

```php
Swoole\Http\Request->parse(string $data): int|false
```

  * **パラメータ**

    * **`string $data`**
      * 解析するパケット

  * **戻り値**

    * 解析が成功した場合は解析されたパケットの長さを返し、コンテキスト接続がないかコンテキストが終了しているか`false`を返します
### isCompleted()

?> **現在のHTTP请求データパケットが終わりに達しているかどうかを取得します。**

!> Swooleバージョン >= `v4.6.0`で利用可能です

```php
Swoole\Http\Request->isCompleted(): bool
```

  * **戻り値**

    * `true`は既に終わっていることを意味し、`false`はコンテキスト接続が終了しているか終わりに達していないことを意味します。

* **例**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/5
### trailer()

> **`Header`情報を`HTTP`応答の末尾に追加します。`HTTP2`でのみ利用可能で、メッセージの完全性検査や数字署名などに使用されます。**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **パラメータ**

  * **`string $key`**
    * **機能**：`HTTP`ヘッダーの`Key`
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`string $value`**
    * **機能**：`HTTP`ヘッダーの`value`
    * **デフォルト値**：なし
    * **その他の値**：なし

* **戻り値**

  * 設定に失敗した場合は`false`を返し
  * 設定に成功した場合は`true`を返し

* **注意**

  !> 同一の`$key`で複数設定された`Http`ヘッダーは上書きされ、最後に設定されたものが使用されます。

* **例**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```
### cookie()

> **`HTTP`応答の`cookie`情報を設定します。別名は`setCookie`です。この方法のパラメータは`PHP`の`setcookie`と同じです。**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **パラメータ**

    * **`string $key`**
      * **機能**：`Cookie`の`Key`
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $value`**
      * **機能**：`Cookie`の`value`
      * **デフォルト値**：なし
      * **その他の値**：なし
  
    * **`int $expire`**
      * **機能**：`Cookie`の`过期时间`
      * **デフォルト値**：0、即時有効
      * **その他の値**：なし

    * **`string $path`**
      * **機能**：`Cookie`のサーバーパスを規定します。`
      * **デフォルト値**：/
      * **その他の値**：なし

    * **`string $domain`**
      * **機能**：`Cookie`のドメインを規定します。`
      * **デフォルト値**：''
      * **その他の値**：なし

    * **`bool $secure`**
      * **機能**：`Cookie`を安全なHTTPS接続を通じて伝送するかどうかを規定します`
      * **デフォルト値**：''
      * **その他の値**：なし

    * **`bool $httponly`**
      * **機能**：`HttpOnly`属性を持つ`Cookie`に対して、ブラウザのJavaScriptがアクセスできるかどうかを規定します。`true`は許可しない、`false`は許可する
      * **デフォルト値**：false
      * **その他の値**：なし

    * **`string $samesite`**
      * **機能**：`Cookie`をサードパーティから制限し、セキュリティリスクを減少させることができます。値としては`Strict`、`Lax`、`None`があります`
      * **デフォルト値**：''
      * **その他の値**：なし

    * **`string $priority`**
      * **機能**：`Cookie`の優先順位を規定します。`Cookie`の数が上限を超えた場合、低い優先順位のものが先に削除されます。値としては`Low`、`Medium`、`High`があります`
      * **デフォルト値**：''
      * **その他の値**：なし
  
  * **戻り値**

    * 設定に失敗した場合は`false`を返し
    * 設定に成功した場合は`true`を返し

* **注意**

  !> - `cookie`の設定は[end](/http_server?id=end)方法の前でなければなりません  
  - `$samesite`パラメータは`v4.4.6`バージョンからサポートされ、`$priority`パラメータは`v4.5.8`バージョンからサポートされます  
  - `Swoole`は自動的に`$value`に`urlencode`変換を行いますが、`rawCookie()`方法を使用すると`$value`の変換処理をオフにできます  
  - `Swoole`では複数の同じ`$key`の`COOKIE`を設定することができます

### rawCookie()

> **`HTTP`応答の`cookie`情報を設定します**

!> `rawCookie()`のパラメータは上記の`cookie()`と同じですが、変換処理は行いません
### status()

> **`Http`状態コードを送信します。別名は`setStatusCode()`です**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **パラメータ**

  * **`int $http_status_code`**
    * **機能**：`HttpCode`を設定します
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`string $reason`**
    * **機能**：状態コードの理由
    * **デフォルト値**：''
    * **その他の値**：なし

  * **戻り値**

    * 設定に失敗した場合は`false`を返し
    * 設定に成功した場合は`true`を返し

* **ヒント**

  * `$http_status_code`が最初のパラメータとして伝えられた場合は、合法的な`HttpCode`でなければならず、例えば`200`、`502`、`301`、`404`などが必要です。そうでなければ`200`状態コードとして設定されます
  * `$reason`が第二のパラメータとして設定された場合は、任意の数値で`HttpCode`を指定できます。例えば未定義の`HttpCode`である`499`も可能です
  * `$status`方法は[$response->end()](/http_server?id=end)の前で実行する必要があります

### gzip()

!> この方法は`4.1.0`またはそれ以上のバージョンで廃止されました。[http_compression](/http_server?id=http_compression)に移動してください。新しいバージョンでは`http_compression`の設定項目が`gzip`方法に代わっています。
主な理由は、`gzip()`方法がブラウザからの`Accept-Encoding`ヘッダーを判断していないことです。もしクライアントが`gzip`圧縮をサポートしていない場合、強制的に使用するとクライアントが解圧できなくなる可能性があります。
新しい`http_compression`の設定項目は、ブラウザの`Accept-Encoding`ヘッダーに基づいて自動的に圧縮を選択し、最適な圧縮アルゴリズムを自動的に選択します。

> **`Http GZIP`圧縮を有効にします。圧縮は`HTML`コンテンツのサイズを小さくし、ネットワーク帯域幅を効果的に節約し、応答時間を向上させることができます。`write/end`で内容を送信する前に`gzip`を実行する必要があります。そうでなければエラーが発生します。**
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **パラメータ**

     * **`int $level`**
       * **機能**：圧縮レベルを指定します。レベルが高いほど圧縮後のサイズが小さくなりますが、`CPU`の消費も増えます。
       * **デフォルト値**：1
       * **その他の値**：1-9

!> `gzip()`方法を呼出した後、下層は自動的に`Http`エンコーディングヘッダーを追加します。PHPコードでは関連する`Http`ヘッダーを再設定する必要はありません。`jpg/png/gif`形式の画像はすでに圧縮されており、再度圧縮する必要はありません。

!> `gzip`機能は`zlib`ライブラリに依存しており、swooleを構築する際には、システムに`zlib`が存在するかどうかを検出します。もし`zlib`がない場合、`gzip()`方法は利用できません。`yum`または`apt-get`を使用して`zlib`ライブラリをインストールすることができます：

```shell
sudo apt-get install libz-dev
```
### redirect()

> **`Http`リダイレージを送信します。この方法を呼び出すと、自動的に応答を終了し、接続を終了します。**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **パラメータ**

    * **`string $url`**
      * **機能**：リダイレージの新しいアドレスであり、`Location`ヘッダーとして送信されます
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $http_code`**
      * **機能**：状態コード（デフォルトでは`302`、一時的なリダイレージとして、`301`を伝入すると永久リダイレージとして）
      * **デフォルト値**：`302`
      * **その他の値**：なし

  * **戻り値**

    * 正常に呼び出された場合は`true`を返し、呼び出しが失敗したり接続上下文が存在しない場合は`false`を返し

* **例**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```
### write()

> **`Http Chunk`を段階的にブラウザに送信することを有効にします。**

> `Http Chunk`については、`Http`プロトコルの標準文書を参照してください。

```php
Swoole\Http\Response->write(string $data): bool
```

  * **パラメータ**

    * **`string $data`**
      * **機能**：送信するデータ内容です【最大長さは`2M`を超えてはならず、[buffer_output_size](/server/setting?id=buffer_output_size)の設定によって制御されます】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値**

    * 正常に呼び出された場合は`true`を返し、呼び出しが失敗したり接続上下文が存在しない場合は`false`を返し

* **ヒント**

  * `write`で段階的にデータを送信した後、`end`方法は何のパラメータも受け付けません。`end`を呼ぶことは、データ転送が完了したことを示すために長さが`0`の`Chunk`を送信するだけです
  * Swoole\Http\Response::header()方法で`Content-Length`を設定した後にこの方法を呼ぶと、Swooleは`Content-Length`の設定を無視し、警告を発生させます
  * `Http2`ではこの方法を使用することはできず、そうすると警告が発生します
  * クライアントが応答圧縮をサポートしている場合、Swoole\Http.Response::write()は圧縮を強制的に終了します
### sendfile()

> **ファイルをブラウザに送信します。**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **パラメータ**

    * **`string $filename`**
      * **機能**：送信するファイルの名前です。【ファイルが存在しないか、アクセス権限がない場合は`sendfile`が失敗します】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $offset`**
      * **機能**：アップロードするファイルのオフセットです。【ファイルの中央部からデータを転送することができます。この特性は断片継続転送をサポートするために使用できます】
      * **デフォルト値**：`0`
      * **その他の値**：なし

    * **`int $length`**
      * **機能**：送信するデータのサイズです。
      * **デフォルト値**：ファイルのサイズ
      * **その他の値**：なし

  * **戻り値**

      * 正常に呼び出された場合は`true`を返し、呼び出しが失敗したり接続上下文が存在しない場合は`false`を返し

* **ヒント**

  * ファイルのMIMEタイプをシステムが推測できないため、コードで`Content-Type`を指定する必要があります
  * `sendfile`を呼ぶ前には`write`方法で`Http-Chunk`を送信してはいけません
  * `sendfile`を呼ぶ後、システムは自動的に`end`を実行します
  * `sendfile`は`gzip`圧縮をサポートしていません

* **例**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```
### end()

> **`Http`応答体を送信し、リクエスト処理を終了します。**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **パラメータ**

    * **`string $html`**
      * **機能**：送信する内容です
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値**

    * 正常に呼び出された場合は`true`を返し、呼び出しが失敗したり接続上下文が存在しない場合は`false`を返し

* **ヒント**

  * `end`は一回だけ呼び出すことができます。クライアントに複数のデータを段階的に送信する
### isWritable()

> **`Swoole\Http\Response`オブジェクトが終了しているか、分離されているかを判断します。**

```php
Swoole\Http\Response->isWritable(): bool
```

* **戻り値**

    * `Swoole\Http\Response`オブジェクトが終了していないか、分離されていない場合は`true`を返し、そうでない場合は`false`を返します。


> Swooleバージョン `v4.6.0`以上が利用可能

* **例**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->isWritable()); // true
    $resp->end('hello');
    var_dump($resp->isWritable()); // false
    $resp->setStatusCode(403); // http response is unavailable (maybe it has been ended or detached)
});

$http->start();
```
## 配置選択
### http_parse_cookie

> **`Swoole\Http\Request`オブジェクトの設定において、`Cookie`の解析を無効にし、`header`に未処理の元の`Cookies`情報を保持します。デフォルトは開启されています**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```
### http_parse_post

> **`Swoole\Http\Request`オブジェクトの設定において、POSTメッセージの解析スイッチを設定します。デフォルトは開启されています**

* `Content-Type`が`x-www-form-urlencoded`の場合、自動的に`POST`アンケートを`POST`アレンジに解析します。
* `false`に設定すると`POST`解析を無効にします。

```php
$server->set([
    'http_parse_post' => false,
]);
```
### http_parse_files

> **`Swoole\Http\Request`オブジェクトの設定において、アップロードファイルの解析スイッチを設定します。デフォルトは開启されています**

```php
$server->set([
    'http_parse_files' => false,
]);
```
### http_compression

> **`Swoole\Http\Response`オブジェクトの設定において、圧縮を有効にします。デフォルトは開启されています。**
> - `http-chunk`は分段別々に圧縮をサポートしていません。`write`メソッドを使用する場合、圧縮が強制的に無効になります。
> - `http_compression`は`v4.1.0`またはそれ以上のバージョンで利用可能です。

```php
$server->set([
    'http_compression' => false,
]);
```

現在、`gzip`、`br`、`deflate`の3種類の圧縮形式をサポートしており、ベンチマークのブラウザクライアントが送信する`Accept-Encoding`ヘッダに基づいて自動的に圧縮方法を選択します（圧縮アルゴリズムの優先順位：`br` > `gzip` > `deflate`）。

**依存性：**

`gzip`と`deflate`は`zlib`ライブラリに依存しており、`Swoole`を構築する際にベンチマークシステムが`zlib`が存在するかどうかを検出します。

`zlib`ライブラリをインストールするには`yum`または`apt-get`を使用できます：

```shell
sudo apt-get install libz-dev
```

`br`圧縮形式は`google`の `brotli`ライブラリに依存しており、インストール方法は「Linuxでbrotliをインストールする」を自分で検索してください。`Swoole`を構築する際にベンチマークシステムが`brotli`が存在するかどうかを検出します。
### http_compression_level / compression_level / http_gzip_level

> **圧縮レベルを設定します。これは`Swoole\Http\Response`オブジェクトの設定に対応しています。**

> > `$level`圧縮レベルは1から9の範囲で、レベルが高いほど圧縮後のサイズが小さくなりますが、`CPU`の消費も増えます。デフォルトは1で、最大は9です。

### http_compression_min_length / compression_min_length

> **圧縮を有効にするための最小バイト数を設定します。これは`Swoole\Http.Response`オブジェクトの設定に対応しています。この値を超えると圧縮が有効になります。デフォルトは20バイトです。**

> Swooleバージョン `v4.6.3`以上が利用可能

```php
$server->set([
    'compression_min_length' => 128,
]);
```
### upload_tmp_dir

> **アップロードファイルの一時的なディレクトリを設定します。ディレクトリの最大長さは220バイトを超えてはなりません**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```
### upload_max_filesize

> **アップロードファイルの最大サイズを設定します**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```
### enable_static_handler

静的ファイルリクエスト処理機能を有効にします。これは`document_root`と併用する必要があります。デフォルトでは`false`です。

### http_autoindex

`http autoindex`機能を有効にします。デフォルトでは無効です。
### http_index_files

`http_autoindex`と併用して、索引される必要があるファイルリストを指定します。

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```
### http_compression_types / compression_types

> **圧縮する必要がある応答のタイプを設定します。これは`Swoole\Http\Response`オブジェクトの設定に対応しています。**

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Swooleバージョン `v4.8.12`以上が利用可能です。

### static_handler_locations

> **静的性能のあるハンドラのパスを設定します。タイプはアレンジで、デフォルトでは有効になりません。**

!> Swooleバージョン `v4.4.0`以上が利用可能です。

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* `Nginx`の`location`文のように、一つまたは複数のパスを静的性能のあるパスとして指定できます。指定されたパス下に`URL`がある場合にのみ静的性能のあるファイルハンドラが有効になり、そうでない場合は動的なリクエストとみなされます。
* `location`項は`/`で始まる必要があります
* `/app/images`のような多層的なパスをサポートしています
* `static_handler_locations`を有効にした後、対応するファイルが存在しない場合は、直接404エラーを返します
### open_http2_protocol

> **HTTP2プロトコル解析を有効にします**【デフォルト値：`false`】

!> 编集時に`--enable-http2`オプションを有効にする必要があります (`Swoole5`からはデフォルトでhttp2が有効になります)。
### document_root

> **静的性能のあるファイルのルートディレクトリを設定します。これは`enable_static_handler`と併用して使用されます。**

!> この機能は比較的シンプルで、公開インターネット環境で直接使用しないでください。

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // v4.4.0以下のバージョンでは、ここは絶対パスでなければなりません
    'enable_static_handler' => true,
]);
```

* `document_root`を設定し、`enable_static_handler`を`true`に設定した後、ベースラインは`Http`リクエストを受け取ったら、まず`document_root`のパス下にそのファイルが存在するかどうかを判断し、そのファイルが存在する場合は直接ファイル内容をクライアントに送信し、`onRequest`カーネルハンドラをトリガーしません。
* 静的性能のあるファイル処理機能を使用する際は、動的なPHPコードと静的性能のあるファイルを分けて隔離し、静的性能のあるファイルは特定のディレクトリに保存する必要があります。
### max_concurrency

> **HTTP1/2サービスの最大並行リクエスト数を制限できます。その数を超えると503エラーを返します。デフォルト値は4294967295で、これは無符号intの最大値です。**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```
### worker_max_concurrency

> **ワークローダーの一斉スレッド化を有効にした後、`worker`プロセスは絶えずリクエストを受け付けます。プレッシャーが大きすぎるのを避けるために、`worker_max_concurrency`を設定して`worker`プロセスのリクエスト実行数を制限できます。リクエスト数がその値を超えた場合、`worker`プロセスは余分なリクエストをキューに保存します。デフォルト値は4294967295で、これは無符号intの最大値です。`worker_max_concurrency`を設定していない場合でも、`max_concurrency`を設定した場合は、ベースラインは自動的に`worker_max_concurrency`を`max_concurrency`に設定します。**

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Swooleバージョン `v5.0.0`以上が利用可能です。
### http2_header_table_size

> HTTP/2ネットワーク接続の最大`header table`サイズを定義します。

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```
### http2_enable_push

> この設定はHTTP/2プッシュを有効にするかどうかを制御します。

```php
$server->set([
  'http2_enable_push' => 0x2
])
```
### http2_max_concurrent_streams

> 各HTTP/2ネットワーク接続で受け入れるマルチループの最大数を設定します。

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```
### http2_init_window_size

> HTTP/2トランスポートの流量制御ウィンドウの初期サイズを設定します。

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```
### http2_max_frame_size

> HTTP/2ネットワーク接続を通じて送信される単一HTTP/2プロトコルフレームのメッセージ体の最大サイズを設定します。

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```
### http2_max_header_list_size

> HTTP/2ストリーム上のリクエストで送信できるヘッダの最大サイズを設定します。 

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
