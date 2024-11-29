```
# コロニアル・HTTP2・クライアント

コロニアルHTTP2クライアント

## 使用例

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $domain = 'www.zhihu.com';
    $cli = new Client($domain, 443, true);
    $cli->set([
        'timeout' => -1,
        'ssl_host_name' => $domain
    ]);
    $cli->connect();
    $req = new Request();
    $req->method = 'POST';
    $req->path = '/api/v4/answers/300000000/voters';
    $req->headers = [
        'host' => $domain,
        'user-agent' => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip'
    ];
    $req->data = '{"type":"up"}';
    $cli->send($req);
    $response = $cli->recv();
    var_dump(assert(json_decode($response->data)->error->code === 10002));
});
```

## 方法

### __construct()

コンストラクタです。

```php
Swoole\Coroutine\Http2\Client::__construct(string $host, int $port, bool $open_ssl = false): void
```

  * **引数** 

    * **`string $host`**
      * **機能**：ターゲットホストのIPアドレス【`$host`がドメイン名であれば、DNSクエリを行います】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：ターゲットポート【`Http`は通常`80`ポート、`Https`は通常`443`ポートです】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`bool $open_ssl`**
      * **機能**：TLS/SSLトンネル暗号化を有効にするかどうか【`https`ウェブサイトは必ず`true`に設定する必要があります】
      * **デフォルト値**：`false`
      * **その他の値**：`true`

  * **注意**

    !> -外部URLにリクエストを送信する必要がある場合は、`timeout`をより大きな値に変更してください。参考：[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)  
    - `$ssl`は`openssl`に依存しており、`Swoole`をコンパイルする際には [--enable-openssl](/environment?id=コンパイルオプション)を有効にする必要があります。

### set()

クライアントパラメータを設定します。その他の詳細な設定項目については、[Swoole\Client::set](/client?id=設定) 設定オプションを参照してください。

```php
Swoole\Coroutine\Http2\Client->set(array $options): void
```

### connect()

ターゲットサーバーに接続します。この方法は引数がありません。

!> `connect`を呼び出した後、下層では自動的に[コリアントスケジュール](/coroutine?id=コリアントスケジュール)が行われ、接続が成功または失敗したときに`connect`が返値します。接続が確立された後は、`send`方法でサーバーにリクエストを送信することができます。

```php
Swoole\Coroutine\Http2\Client->connect(): bool
```

  * **戻り値**

    * 接続が成功すると`true`が返ります。
    * 接続が失敗すると`false`が返ります。エラーコードは`errCode`プロパティで確認できます。

### stats()

ストリームの状態を取得します。

```php
Swoole\Coroutine\Http2\Client->stats([$key]): array|bool
```

  * **例**

```php
var_dump($client->stats(), $client->stats()['local_settings'], $client->stats('local_settings'));
```

### isStreamExist()

指定されたストリームが存在するかどうかを判断します。

```php
Swoole\Coroutine\Http2\Client->isStreamExist(int $stream_id): bool
```

### send()

サーバーにリクエストを送信します。下層では自動的にHTTP2の`stream`が構築されます。同時に複数のリクエストを送信することができます。

```php
Swoole\Coroutine\Http2\Client->send(Swoole\Http2\Request $request): int|false
```

  * **引数** 

    * **`Swoole\Http2\Request $request`**
      * **機能**：Swoole\Http2\Requestオブジェクトを送信します
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値**

    * 成功するとストリーム番号が返ります。番号は`1`から始まり、奇数で増加します。
    * 失敗すると`false`が返ります。

  * **ヒント**

    * **Requestオブジェクト**

      !> `Swoole\Http2\Request`オブジェクトには何の方法もありません。オブジェクトのプロパティを設定することで、リクエスト関連の情報を書きます。

      * `headers` 配列、HTTPヘッダ
      * `method` 文字列、リクエスト方法を設定します。例えば`GET`、`POST`など
      * `path` 文字列、URLパスを設定します。例えば`/index.php?a=1&b=2`など、/`で始まります
      * `cookies` 配列、COOKIESを設定します
      * `data`リクエストのボディを設定します。文字列の場合は、RAW form-dataとして直接送信されます
      * `data` 配列の場合、下層では自動的に`x-www-form-urlencoded`形式のPOSTコンテンツをパッケージ化し、`Content-Type`を`application/x-www-form-urlencoded`に設定します
      * `pipeline` 布林値、`true`に設定すると、`$request`を送信した後も、`stream`を閉じず、データフレームをさらに写入することができます。`write`方法を参照してください。

    * **pipeline**

      * 既定の`send`方法はリクエストを送信した後、現在のHTTP2 Streamを終了します。`pipeline`を有効にすると、下層では`stream`を維持し、何度も`write`方法を呼び出してサーバーにデータフレームを送信することができます。`write`方法を参照してください。

### write()

サーバーにより多くのデータフレームを送信します。同じstreamに何度も`write`を呼び出してデータフレームを写入することができます。

```php
Swoole\Coroutine\Http2\Client->write(int $streamId, mixed $data, bool $end = false): bool
```

  * **引数** 

    * **`int $streamId`**
      * **機能**：ストリーム番号、`send`方法によって返されるものです
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`mixed $data`**
      * **機能**：データフレームの内容であり、文字列または配列です
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`bool $end`**
      * **機能**：ストリームを閉じるかどうか
      * **デフォルト値**：`false`
      * **その他の値**：`true`

  * **使用例**

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9518);
    $cli->set(['timeout' => 1]);
    var_dump($cli->connect());

    $req3 = new Request();
    $req3->path = "/index.php";
    $req3->headers = [
        'host' => "localhost",
        "user-agent" => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip',
    ];
    $req3->pipeline = true;
    $req3->method = "POST";
    $streamId = $cli->send($req3);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    //end stream
    $cli->write($streamId, ['int' => rand(1000, 9999), 'end' => true], true);
    var_dump($cli->recv());
    $cli->close();
});
```

!> `write`を使用してデータフレームを分割して送信する場合、`send`リクエスト時に`$request->pipeline`を`true`に設定する必要があります。  
`end`が`true`のデータフレームを送信した後、streamは閉じられ、その後、このstreamにデータを書くことはできません。

### recv()

リクエストを受信します。

!> この方法を呼び出すと[コリアントスケジュール](/coroutine?id=コリアントスケジュール)が発生します。

```php
Swoole\Coroutine\Http2\Client->recv(float $timeout): Swoole\Http2\Response;
```

  * **引数** 

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定します。参考：[クライアントタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)
      * **値の単位**：秒【浮点数をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値**

成功するとSwoole\Http2\Responseオブジェクトが返ります。

```php
/**@var $resp Swoole\Http2\Response */
var_dump($resp->statusCode); // サーバーから送信されたHTTP状態コード、例えば200、502など
var_dump($resp->headers); // サーバーから送信されたHeader情報
var_dump($resp->cookies); // サーバーが設定したCOOKIE情報
var_dump($resp->set_cookie_headers); // サーバー側から返送された元のCOOKIE情報、domainとpathが含まれます
var_dump($resp->data); // サーバーから送信された応答ボディ
```

!> Swooleバージョン < [v4.0.4](/version/bc?id=_404) の場合、`data`プロパティは`body`プロパティです。Swooleバージョン < [v4.0.3](/version/bc?id=_403) の場合、`headers`と`cookies`は単数形です。
```
### read()

`recv()`とほぼ同じですが、`pipeline`タイプの応答に対しては、`read`は何度も読み取りを行い、一度に一部の内容を読んでメモリを節約したり、プッシュ通知をできるだけ早く受信したりすることができます。一方で、`recv`はすべてのフレームを一つに組み立てて完全な応答を返すまで待ちます。

!> このメソッドを呼び出すと[协程调度](/coroutine?id=协程调度)が発生します

```php
Swoole\Coroutine\Http2\Client->read(float $timeout): Swoole\Http2\Response;
```

  * **パラメータ** 

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定します。[クライアントタイムアウトルール](/coroutine_client/init?id=超时规则)を参照してください
      * **値の単位**：秒【浮点数をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値**

    成功すると Swoole\Http2\Response オブジェクトが返されます


### goaway()

GOAWAYフレームは、接続の閉鎖を開始したり、重大なエラー状態を送信するためのものです。

```php
Swoole\Coroutine\Http2\Client->goaway(int $error_code = SWOOLE_HTTP2_ERROR_NO_ERROR, string $debug_data): bool
```


### ping()

PINGフレームは、送信元からの最小リターントランザクション時間を測定し、空闲接続がまだ有効かどうかを判断するためのメカニズムです。

```php
Swoole\Coroutine\Http2\Client->ping(): bool
```

### close()

接続を閉じます。

```php
Swoole\Coroutine\Http2\Client->close(): bool
```
