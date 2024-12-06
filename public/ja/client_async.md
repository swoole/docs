```
# Swoole\Async\Client

`Swoole\Async\Client`は以下のように略称されます。`Client`は非同期非ブロッキングの`TCP/UDP/UnixSocket`ネットワーククライアントであり、非同期クライアントではイベントのコールバック関数を設定する必要があります。同期待機ではありません。



- 非同期クライアントは`Swoole\Client`のサブクラスであり、一部の同期ブロッキングクライアントの方法呼び出し可能  
- `6.0`以上のバージョンでのみ利用可能



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


## 方法

このページでは`Swoole\Client`と異なる方法のみをリストアップしています。サブクラスで変更されていない方法は、[同期ブロッキングクライアント](client.md)を参照してください。


### __construct()

コンストラクタ方法で、親クラスのコンストラクタ方法を参照してください。

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> 非同期クライアントの2番目のパラメータは必ず`true`でなければなりません。


### on()

`Client`のイベントコールバック関数を登録します。

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> `on`方法を繰り返し呼び出すと、前の設定が上書きされます。

  * **パラメータ**

    * `string $event`

      * 機能：コールバックイベント名、大小写不敏感
      * 既定値：なし
      * その他：なし

    * `callable $callback`

      * 機能：コールバック関数
      * 既定値：なし
      * その他：なし

      !> 関数の名前の文字列、クラスの静的方法、オブジェクトの方法の配列、アノテーション関数を参照[このセクション](/learn?id=コールバック関数のいくつかの設定方法)。
  
  * **戻り値**

    * 操作が成功した場合は`true`を返し、操作に失敗した場合は`false`を返します。



### isConnected()
現在クライアントがサーバーと接続しているかどうかを判断します。

```php
Swoole\Async\Client->isConnected(): bool
```

* `true`を返す場合、接続が確立していることを意味し、`false`を返す場合、接続が確立していないことを意味します。


### sleep()
一時的にデータの受信を停止し、呼び出された後、イベントループから離れ、データ受信イベントを触発しません。`wakeup()`メソッドを呼び出すと再び受信を開始できます。

```php
Swoole\Async\Client->sleep(): bool
```

* 操作が成功した場合は`true`を返し、操作に失敗した場合は`false`を返します。


### wakeup()
データの受信を再開し、呼び出された後、イベントループに再加入されます。

```php
Swoole\Async\Client->wakeup(): bool
```

* 操作が成功した場合は`true`を返し、操作に失敗した場合は`false`を返します。



### enableSSL()
動的に`SSL/TLS`暗号化を有効にし、通常は`startTLS`クライアントに使用されます。接続が確立された後、まず明文化データを送信し、その後暗号化通信を開始します。

```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* この関数は`connect`が成功した後でなければ呼び出せません。
* 非同期クライアントでは必ず`$callback`を設定し、`SSL`ハンドシェイクが完了した後にこの関数をコールバックします。
* 操作が成功した場合は`true`を返し、操作に失敗した場合は`false`を返します。


## コールバックイベント


### connect
接続が確立された後にトリガーされ、HTTPまたはSocks5プロキシやSSLトンネル暗号化が設定されている場合は、プロキシハンドシェイクが完了し、SSL暗号化ハンドシェイクが完了した後にトリガーされます。

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

このイベントのコールバックの後で`isConnected()`来判断すると`true`が返ります。



### error 
接続が確立失败した後にトリガーされ、`$client->errCode`を取得してエラー情報を得ることができます。
```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```



- `connect`と`error`はどちらか一方しかトリガーされません。接続が成功したり失敗したりしても、一つの結果しか存在しません。

- `Client::connect()`は直接`false`を返すことがあり、これは接続が失敗したことを意味し、この時`error`のコールバックは実行されません。必ず`connect`の呼び出し返回値をチェックしてください。

- `error`イベントは非同期の結果であり、接続を发起してから`error`イベントがトリガーされるまでに一定の`IO`等待時間があります。
- `connect`が失敗返回することは即時失敗を意味し、このエラーはオペレーティングシステムが直接トリガーするため、中间にはいかなる`IO`等待時間も存在しません。


### receive
データを受信した後にトリガーされます。

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```



- どのプロトコルも設定していない場合、例えば`EOF`または`LENGTH`の場合、最大受信データ長さは`64K`です。

- プロトコルの処理パラメータが設定されている場合は、最大データ長さは`package_max_length`パラメータが設定しており、デフォルトは`2M`です。
- `$data`は必ず空ではありません。システムエラーや接続が閉じられた場合、`close`イベントがトリガーされます。

### close
接続が閉じられた後にトリガーされます。

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```
