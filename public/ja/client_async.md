# Swoole\Async\Client

`Swoole\Async\Client`ここでは`Client`と簡単に呼びます。これは非同期的で非ブロッキングの`TCP/UDP/UnixSocket`ネットワーククライアントで、非同期クライアントではイベントハンドラ関数を設定する必要があり、同期で待つのではありません。- 非同期クライアントは`Swoole\Client`のサブクラスで、一部の同期ブロッキングクライアントのメソッドを呼び出すことができます  
- 6.0またはそれ以上のバージョンで利用可能です。

## 完全な例

```php
$cli = new Swoole\Async\Client(SWOOLE_SOCK_TCP);

$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());
    $client->send(RandStr::gen(1024, RandStr::ALL));
});

$client->on("receive", function(Swoole\Async\Client $client, string $data){
    $recv_len = strlen($data);
    $client->send(RandStr::gen(1024, RandStr::ALL));
    $client->close();
    Assert::false($client->isConnected());
});

$client->on("error", function(Swoole\Async\Client $client) {
    echo "error";
});

$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});

$client->connect("127.0.0.1", 9501, 0.2);
```
## メソッド

このページでは、`Swoole\Client`との違いがあるメソッドだけを列挙しています。子クラスで修改されていないメソッドについては、[同期ブロッキングクライアント](client.md)を参照してください。
### __construct()

コンストラクターは、親クラスのコンストラクターを参照しています。

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> 非同期クライアントの2番目のパラメータは必ず`true`でなければなりません。
### on()

`Client`のイベントハンドラ関数を登録します。

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> `on`メソッドを繰り返し呼び出すと、前回の設定が上書きされます。

  * **パラメータ**

    * `string $event`

      * 機能：ハンドライベント名、大文字小文字に関係なく
      * デフォルト値：なし
      * その他の値：なし

    * `callable $callback`

      * 機能：ハンドラ関数
      * デフォルト値：なし
      * その他の値：なし

      !> 函数名の文字列、クラスの静的メソッド、オブジェクトの方法の配列、匿名関数が利用できます。[このセクション](/learn?id=いくつかのハンドラ関数設定方法)を参照してください。
  
  * **戻り値**

    * `true`を返すと操作が成功したことを表し、`false`を返すと操作が失敗したことを表します。

### isConnected()
現在のクライアントがサーバーとの接続を確立しているかどうかを判断します。

```php
Swoole\Async\Client->isConnected(): bool
```

* `true`を返すと接続が確立されており、`false`を返すと接続が確立されていない
### sleep()

データ受信を一時的に停止し、呼び出した後はイベントループから削除され、データ受信イベントは再びトリガーされません。`wakeup()`メソッドを呼び出して復帰させる必要があります。
```php
Swoole\Async\Client->sleep(): bool
```

* `true`を返すと操作が成功したことを表し、`false`を返すと操作が失敗したことを表します
### wakeup()

データ受信を再開し、呼び出した後はイベントループに組み込まれます。
```php
Swoole\Async\Client->wakeup(): bool
```

* `true`を返すと操作が成功したことを表し、`false`を返すと操作が失敗したことを表します

### enableSSL()

動的に`SSL/TLS`暗号化を開始します。通常は`startTLS`クライアントに使用されます。接続が確立した後に最初に明文データを送信し、その後に暗号化転送を開始します。
```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* この関数は`connect`が成功した後にのみ呼び出すことができます。
* 非同期クライアントは`$callback`を設定する必要があり、`SSL`ハンドショーが完了した後にこの関数を呼び出します。
* `true`を返すと操作が成功したことを表し、`false`を返すと操作が失敗したことを表します。
## ハンドライベント
### connect
接続が確立された後にトリガーされます。`HTTP`または`Socks5`プロキシーと`SSL`トンネル暗号化が設定されている場合は、プロキシーハンドショーが完了し、`SSL`暗号化ハンドショーが完了した後にトリガーされます。

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

このイベントハンドラの後に`isConnected()`を使用すると、`true`を返すことになります。

### error 
接続が確立された後に失敗した場合にトリガーされます。読み取り`$client->errCode`を取得してエラーメッセージを取得できます。
```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```- `connect`と`error`は一方だけがトリガーされます。接続が成功したり失敗したりするときは、一つの結果しか存在しません- `Client::connect()`は直接`false`を返す可能性があり、これは接続が失敗したことを意味します。この場合は`error`ハンドラは実行されませんので、`connect`の呼び出し値を確認する必要があります- `error`イベントは非同期の結果であり、開始から`error`イベントがトリガーされるまでには一定の`IO`待ち時間が存在します。
- `connect`が失敗した場合は即座に失敗し、このエラーはオペレーティングシステムによって直接トリガーされ、中間には`IO`待ち時間は存在しません。
### receive
データが受信された後にトリガーされます。

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```- プロトコルを設定していない場合（例えば`EOF`または`LENGTH`）、最大受信データ長さは`64K`です- プロトコル処理パラメータを設定している場合、最大データ長さは`package_max_length`パラメータの設定のみで、デフォルトは`2M`です。
- `$data`は必ず空ではありません。システムエラーが発生したり接続が閉じられたりした場合は、`close`イベントがトリガーされます。
### close
接続が閉じられた場合にトリガーされます。

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```
