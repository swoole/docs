```
# TCPサーバー

?> `Swoole\Coroutine\Server`は完全に[協程](/coroutine)化されたクラスで、協程`TCP`サーバーを作成するために使用され、TCPと[unixSocket](/learn?id=何 IPC)タイプをサポートしています。

[Server](/server/tcp_init)モジュールとの違い：

* 動的に作成・破壊し、実行中に動的にポートを聴くことができ、サーバーを動的に閉じることができます
* 接続処理は完全に同期されており、プログラムは`Connect`、`Receive`、`Close`イベントを順序的に処理することができます

!> 4.4以降のバージョンで利用可能

## 短名

`Co\Server`という短名を使用できます。

## 方法

### __construct()

?> **構築方法です。** 

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **パラメータ** 

    * **`string $host`**
      * **機能**：聴くアドレス
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：聴くポート【0であればオペレーティングシステムがランダムにポートを割り当てる】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`bool $ssl`**
      * **機能**：SSL暗号化を有効にするかどうか
      * **デフォルト値**：`false`
      * **その他の値**：`true`

    * **`bool $reuse_port`**
      * **機能**：ポート再利用を有効にするかどうか、効果は[このセクション](/server/setting?id=enable_reuse_port)の設定と同じです
      * **デフォルト値**：`false`
      * **その他の値**：`true`
      * **バージョンへの影響**：Swooleバージョン >= v4.4.4

  * **ヒント**

    * **$hostパラメータは3つのフォーマットをサポートしています**

      * `0.0.0.0/127.0.0.1`: IPv4アドレス
      * `::/::1`: IPv6アドレス
      * `unix:/tmp/test.sock`: [UnixSocket](/learn?id=何 IPC)アドレス

    * **例外**

      * パラメータエラー、バインドアドレスとポートの失敗、`listen`の失敗時には`Swoole\Exception`例外が投げられます。


### set()

?> **プロトコル処理パラメータを設定します。** 

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **設定パラメータ**

    * パラメータ`$options`は一次元の関連索引配列でなければならず、[setprotocol](/coroutine_client/socket?id=setprotocol) 方法が受け入れる設定項目と完全に一致しています。

    !> [start()](/coroutine/server?id=start) 方法の前にパラメータを設定しなければなりません

    * **長さプロトコル**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      * package_length_offset' => 0,
      * package_body_offset' => 4,
    ]);
    ```

    * **SSL証明書設定**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      * ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```


### handle()

?> **接続処理関数を設定します。** 

!> [start()](/coroutine/server?id=start)の前に処理関数を設定しなければなりません

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **パラメータ** 

    * **`callable $fn`**
      * **機能**：接続処理関数を設定する
      * **デフォルト値**：なし
      * **その他の値**：なし
      
  * **例** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            * data = $conn->recv();
        }
    });
    ```

    !> -サーバーは`Accept`(接続建立)が成功した後、自動的に[協程](/coroutine?id=协程调度)を作成して$fnを実行します；  
    - `$fn`は新しいサブ协程空間内で実行されるため、関数内で再び協程を作成する必要はありません；  
    - `$fn`は1つのパラメータを受け取り、そのタイプは[Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection)オブジェクトです；  
    - [exportSocket()](/coroutine/server?id=exportsocket)を使用して、現在の接続のSocketオブジェクトを取得することができます


### shutdown()

?> **サーバーを終了します。** 

?> 底部では`start`と`shutdown`を何度も呼び出すことができます

```php
Swoole\Coroutine\Server->shutdown(): bool
```


### start()

?> **サーバーを開始します。** 

```php
Swoole\Coroutine\Server->start(): bool
```

  * **戻り値**

    * 起動に失敗すると`false`を返し、`errCode`プロパティを設定します
    * 起動に成功するとループに入り、`Accept`接続
    * `Accept`(接続建立)後には新しい協程が作成され、その協程内でhandle方法で指定された関数が実行されます

  * **エラー処理**

    * `Accept`(接続建立)で`Too many open file`エラーが発生したり、サブ协程を创建できない場合は、`1`秒間待ってから再び`Accept`を続けます
    * エラーが発生した場合は、`start()`方法が戻り、エラー情報は`Warning`として報告されます。


## オブジェクト


### Coroutine\Server\Connection

`Swoole\Coroutine\Server\Connection`オブジェクトは4つの方法を提供しています：
 
#### recv()

データを受信し、プロトコル処理が設定されている場合は、毎回完全なパケットを返します

```php
function recv(float $timeout = 0)
```

#### send()

データを送信します

```php
function send(string $data)
```

#### close()

接続を閉じます

```php
function close(): bool
```

#### exportSocket()

現在の接続のSocketオブジェクトを取得します。より多くの低層の方法を呼び出すことができますので、[Swoole\Coroutine\Socket](/coroutine_client/socket)を参照してください。

```php
function exportSocket(): Swoole\Coroutine\Socket
```

## 完全な例

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

// 多プロセス管理モジュール
$pool = new Process\Pool(2);
// 各OnWorkerStart回调で自動的に協程を作成する
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    // 各プロセスが9501ポートを聴く
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    // 15番のシグナルを受け取ってサービスを閉じる
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    // 新しい接続要求を受け取り、自動的に協程を作成する
    $server->handle(function (Connection $conn) {
        while (true) {
            // データを受信する
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            // データを送信する
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    // ポートを聴き始める
    $server->start();
});
$pool->start();
```

!> Cygwin環境で実行する場合は、単一プロセスに変更してください。`$pool = new Swoole\Process\Pool(1);`
