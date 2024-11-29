# HTTP サーバー

## プログラムコード

以下のコードを httpServer.php に書き込んでください。

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();
```

`HTTP`サーバーはリクエストの応答にのみ関与するため、[onRequest](/http_server?id=on)イベントを監視するだけで十分です。新しい`HTTP`リクエストが入るとこのイベントがトリガーされます。イベントの回调関数は`2`つのパラメータを持ちます。一つは`$request`オブジェクトで、GET/POSTリクエストのデータなどのリクエストに関する情報を含みます。

もう一つは`response`オブジェクトで、`request`に対する応答は`response`オブジェクトを操作することによって完了できます。`$response->end()`方法は一段の`HTML`コンテンツを出力し、このリクエストを終了することを意味します。

* `0.0.0.0`はすべての`IP`アドレスを監視することを意味し、一つのサーバーには複数の`IP`がある可能性があります。例えば`127.0.0.1`はローカルループバック`IP`、`192.168.1.100`は局所ネットワーク`IP`、`210.127.20.2`は外部ネットワーク`IP`です。ここでは個別に監視する`IP`を指定することもできます。
* `9501`は監視するポートで、他のプロセスが占有している場合、プログラムは致命的なエラーを抛出し、実行を中断します。

## サービスの起動

```shell
php httpServer.php
```
* ブラウザを開いて`http://127.0.0.1:9501`をアクセスするとプログラムの結果を確認できます。
* Apacheの`ab`ツールを使用してサーバーに負荷テストを行うこともできます。

## Chromeでの2回リクエスト問題

`Chrome`ブラウザでサーバーにアクセスすると、追加の`/favicon.ico`のリクエストが発生しますが、これは404エラーで応答することができます。

```php
$http->on('Request', function ($request, $response) {
	if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
	}
    var_dump($request->get, $request->post);
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});
```

## URLルーティング

アプリケーションは`$request->server['request_uri']`に基づいてルーティングを実現することができます。例えば：`http://127.0.0.1:9501/test/index/?a=1`のように、コードでは次のように`URL`ルーティングを実現できます。

```php
$http->on('Request', function ($request, $response) {
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	// $controller, $actionに基づいて異なるコントローラークラスと方法をマッピングします。
	(new $controller)->$action($request, $response);
});
```
