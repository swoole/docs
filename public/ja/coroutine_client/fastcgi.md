# コロニアルファCGIクライアント

PHP-FPMは効率的なバイナリプロトコルである「FastCGIプロトコル」を用いて通信を行います。FastCGIクライアントを通じて、PHP-FPMサービスと直接インタラクションを行うことができ、HTTPリバースプロキシを経由する必要はありません。

[PHPソースディレクトリ](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)

## 간단な使用例

[さらに多くの例コード](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> 以下の例コードは协程内で呼び出さなければなりません

###クイック呼び出し

```php
#greeter.php
echo 'Hello ' . ($_POST['who'] ?? 'World');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // FPMリスニングアドレス、またはunix:/tmp/php-cgi.sockのようなunixsocketアドレス
    '/tmp/greeter.php', // 実行したいエントリーファイル
    ['who' => 'Swoole'] // 付加されたPOSTデータ
);
```

### PSRスタイル

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1:9000', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withScriptFilename(__DIR__ . '/greeter.php')
        ->withMethod('POST')
        ->withBody(['who' => 'Swoole']);
    $response = $client->execute($request);
    echo "Result: {$response->getBody()}\n";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### 複雑な呼び出し

```php
#var.php
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
```

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withDocumentRoot(__DIR__)
        ->withScriptFilename(__DIR__ . '/var.php')
        ->withScriptName('var.php')
        ->withMethod('POST')
        ->withUri('/var?foo=bar&bar=char')
        ->withHeader('X-Foo', 'bar')
        ->withHeader('X-Bar', 'char')
        ->withBody(['foo' => 'bar', 'bar' => 'char']);
    $response = $client->execute($request);
    echo "Result: \n{$response->getBody()}";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Error: {$exception->getMessage()}\n";
}
```

### WordPressをワンクリックでプロキシ

!> この使用方法は生産的な意味はありません。生産では、一部の古いAPIインターフェースのHTTPリクエストを古いFPMサービスに代理するためにproxyを使用することができます（ウェブサイト全体を代理するのではなく）

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # WordPressプロジェクトのルートディレクトリ
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # ここでのポートはWordPressの設定と一致している必要があり、通常は特定のポートを指定しません。つまり80です。
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], # 静的なリソースのpath
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # プロキシオブジェクトを作成する
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # リクエストをワンクリックで代理する
});
$server->start();
```

## 方法

### call

静的な方法で、新しいクライアント接続を直接作成し、FPMサーバーにリクエストを送り、応答の本文を受け取ります。

!> FPMは短期間の接続のみをサポートするため、通常、持続的なオブジェクトを作成することにはあまり意味がありません。

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **パラメータ** 

    * **`string $url`**
      * **機能**：FPMのリスニングアドレス【例えば`127.0.0.1:9000`、`unix:/tmp/php-cgi.sock`など】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $path`**
      * **機能**：実行したいエントリーファイル
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`$data`**
      * **機能**：付加されたリクエストデータ
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する【デフォルトは `-1` 表示永遠にタイムアウトしない】
      * **値の単位**：秒【浮点数をサポートしており、例えば `1.5` 表示 `1s`+`500ms`】
      * **デフォルト値**：`-1`
      * **その他の値**：なし

  * **戻り値** 

    * サーバーからの応答の本文内容(body)を返す
    * 错误が発生した場合は `Swoole\Coroutine\FastCGI\Client\Exception` 例外が投げられる


### __construct

クライアントオブジェクトのコンストラクタで、ターゲットFPMサーバーを指定します。

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **パラメータ** 

    * **`string $host`**
      * **機能**：ターゲットサーバーのアドレス【例えば`127.0.0.1`、`unix://tmp/php-fpm.sock`など】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：ターゲットサーバーのポート【UNIXSocketをターゲットとしていない場合は必要ない】
      * **デフォルト値**：なし
      * **その他の値**：なし


### execute

リクエストを実行し、応答を返します。

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **パラメータ** 

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **機能**：リクエスト情報を含むオブジェクトで、通常は `Swoole\FastCGI\HttpRequest` を使用してHTTPリクエストをシミュレートし、特別なニーズがある場合にのみFPMプロトコルの元のリクエストクラス `Swoole\FastCGI\Request` を使用します
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する【デフォルトは `-1` 表示永遠にタイムアウトしない】
      * **値の単位**：秒【浮点数をサポートしており、例えば `1.5` 表示 `1s`+`500ms`】
      * **デフォルト値**：`-1`
      * **その他の値**：なし

  * **戻り値** 

    * リクエストオブジェクトのタイプに対応するResponseオブジェクトを返す。例えば `Swoole\FastCGI\HttpRequest` は `Swoole\FastCGI\HttpResponse` オブジェクトを返し、FPMサーバーの応答情報を含む
    * 错误が発生した場合は `Swoole\Coroutine\FastCGI\Client\Exception` 例外が投げられる

## 関連するリクエスト/応答クラス

libraryはPSRの広範な依存関係を導入することができず、拡張読み込みは常にPHPコードの実行前に行われるため、関連するリクエスト応答オブジェクトはPSRインターフェースを継承していません。しかし、PSRのスタイルで実装することで、開発者が迅速に使いこなせるように努めています。

FastCGIでHTTPリクエストと応答をシミュレートするクラスに関する関連ソースコードの場所は以下の通りです。非常にシンプルで、コードがドキュメントとなっています:

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
