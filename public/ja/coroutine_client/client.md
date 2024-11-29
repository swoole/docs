```
# CoTCP/UDPクライアント

`Coroutine\Client`はTCP、UDP、[unixSocket](/learn?id=何 IPC)伝送プロトコルの[Socketクライアント](/coroutine_client/socket)の封装コードを提供し、使用する際には単に`new Swoole\Coroutine\Client`を行う必要があります。

* **実現原理**

    * `Coroutine\Client`のネットワークリクエストに関わるすべての方法は、Swooleが[co_schedule](/coroutine?id=co_schedule)を行い、ビジネス層はそれを認識する必要はありません。
    * 使用方法と[Client](/client)の同期モードの方法完全に同じです。
    * `connect`のタイムアウト設定は`Connect`、`Recv`、`Send`のタイムアウトにも同時に作用します。

* **継承関係**

    * `Coroutine\Client`と[Client](/client)は継承関係ではありませんが、Clientが提供する方法はすべて`Coroutine\Client`で使用できます。[Swoole\Client](/client?id=方法)を参照してください。ここでは詳述しません。
    * `Coroutine\Client`では`set`方法を使用して[設定オプション](/client?id=設定)を設定でき、使用方法は`Client->set`と完全に同じです。異なる機能を使用する場合は、`set()`関数の小節で個別に説明します。

* **使用例**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv();
    $client->close();
});
```

* **プロトコルの処理**

coクライアントは長さと`EOF`プロトコルの処理もサポートしており、設定方法は[Swoole\Client](/client?id=設定)と完全に同じです。

```php
$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0, //第N个字节がパケット長さの値です
    'package_body_offset'   => 4, //どの字节から長さ計算するかを指定します
    'package_max_length'    => 2000000, //プロトコルの最大長さを設定します
));
```

### connect()

リモートサーバーに接続します。

```php
Swoole\Coroutine\Client->connect(string $host, int $port, float $timeout = 0.5): bool
```

  * **パラメータ** 

    * **`string $host`**
      * **機能**：リモートサーバーのアドレス【底層は自動的にcoを切り替えてドメイン名をIPアドレスに解析します】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：リモートサーバーのポート番号
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：ネットワークIOのタイムアウト時間；`connect/send/recv`を含みます。タイムアウトが発生した場合、接続は自動的に`close`されます。参考：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)
      * **値の単位**：秒【浮点型をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：`0.5s`
      * **その他の値**：なし

* **ヒント**

    * 接続に失敗した場合、`false`が返ります。
    * タイムアウト後返回し、`$cli->errCode`を検査して`110`が表示されることを確認してください。

* **失敗時の再試行**

!> `connect`で接続に失敗した場合、直接再接続することはできません。既存の`socket`を`close`してから再度`connect`を試みる必要があります。

```php
//接続に失敗した場合
if ($cli->connect('127.0.0.1', 9501) == false) {
    //既存のsocketをcloseする
    $cli->close();
    //再試行
    $cli->connect('127.0.0.1', 9501);
}
```

* **例**

```php
if ($cli->connect('127.0.0.1', 9501)) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}

if ($cli->connect('/tmp/rpc.sock')) {
    $cli->send('data');
} else {
    echo 'connect failed.';
}
```


### isConnected()

Clientの接続状態を返します。

```php
Swoole\Coroutine\Client->isConnected(): bool
```

  * **戻り値**

    * `false`を返す場合、現在サーバーに接続していません。
    * `true`を返す場合、現在サーバーに接続しています。
    
!> `isConnected`方法はアプリケーション層の状態を返すものであり、`Client`が`connect`を実行して成功裏にサーバーに接続し、そして`close`を呼び出して接続を閉じないことを意味します。`Client`は`send`、`recv`、`close`などの操作を行うことができますが、再度`connect`を行うことはできません。  
これは必ずしも接続が利用可能であることを意味するものではありません。`send`または`recv`を実行すると、依然としてエラーが返される可能性があります。なぜなら、アプリケーション層は底層のTCP接続の状態を得ることができず、`send`または`recv`を実行するとアプリケーション層とカーネルが対話して、実際の接続可利用状態を得る必要があるからです。


### send()

データを送信します。

```php
Swoole\Coroutine\Client->send(string $data): int|bool
```

  * **パラメータ** 

    * **`string $data`**
    
      * **機能**：送信するデータであり、文字列タイプでなければならず、バイナリデータもサポートされています。
      * **デフォルト値**：なし
      * **その他の値**：なし

  * 送信に成功すると、Socketバッファに書き込まれたバイト数を返します。底層はできるだけすべてのデータを送信しようとします。返されたバイト数が渡された`$data`の長さと異なる場合、Socketが相手方にすでに閉じられている可能性があり、次の`send`または`recv`呼び出し時には対応するエラーコードが返されます。

  * 送信に失敗すると`false`が返され、`$client->errCode`を使用してエラーの原因を取得することができます。


### recv()

データを受信します。

```php
Swoole\Coroutine\Client->recv(float $timeout = 0): string|bool
```

  * **パラメータ** 

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定します。
      * **値の単位**：秒【浮点型をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照してください。
      * **その他の値**：なし

    !> タイムアウトを設定した場合、指定されたパラメータが優先され、次に`set`方法で設定された`timeout`設定が使用されます。タイムアウトが発生したエラーコードは`ETIMEDOUT`です。

  * **戻り値**

    * [通信プロトコル](/client?id=プロトコル解析)が設定されている場合、`recv`は完全なデータを返します。長さは[package_max_length](/server/setting?id=package_max_length)に制限されます。
    * 通信プロトコルが設定されていない場合、`recv`は最大64KBのデータを返します。
    * 通信プロトコルが設定されていない場合、原始的なデータが返され、PHPコードでネットワークプロトコルの処理を自行実施する必要があります。
    * `recv`が空文字列を返した場合、サービス側が接続を自ら閉じたと考えられます。これを`close`する必要があります。
    * `recv`が失敗した場合、`false`が返され、`$client->errCode`を使用してエラーの原因を取得し、処理方法は以下の[完全な例](/coroutine_client/client?id=完全な例)を参照してください。


### close()

接続を閉じます。

!> `close`は非阻塞であり、すぐに戻ります。閉じる操作にはcoの切り替えはありません。

```php
Swoole\Coroutine\Client->close(): bool
```


### peek()

データを覗き見ます。

!> `peek`方法は直接socketを操作するため、coのスケジュールを引き起こすことはありません。

```php
Swoole\Coroutine\Client->peek(int $length = 65535): string
```

  * **ヒント**

    * `peek`方法はカーネルのsocketバッファ内のデータを覗き見るため、オフセットは行いません。`peek`を使用した後も、`recv`を呼び出してこの部分のデータを读取することができます。
    * `peek`方法は非阻塞であり、すぐに戻ります。socketバッファにデータがある場合は、データの内容が返されます。バッファが空の場合は`false`を返し、`$client->errCode`を設定します。
    * 接続がすでに閉じられている場合、`peek`は空文字列を返します。


### set()

クライアントパラメータを設定します。

```php
Swoole\Coroutine\Client->set(array $settings): bool
```

  * **設定パラメータ**

    * [Swoole\Client](/client?id=set)を参照してください。

* **[Swoole\Client](/client?id=set)との違い**
    
    coクライアントはより細粒度のタイムアウト制御を提供します。以下の設定が可能です：
    
    * `timeout`：総タイムアウトであり、接続、送信、受信のすべてのタイムアウトを含みます。
    * `connect_timeout`：接続タイムアウト
    * `read_timeout`：受信タイムアウト
    * `write_timeout`：送信タイムアウト
    * [クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照してください。

* **例**

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    $client->set(array(
        'timeout' => 0.5,
        'connect_timeout' => 1.0,
        'write_timeout' => 10.0,
        'read_timeout' => 0.5,
    ));

    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // 全等して空なら直接接続を閉じる
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // 业务ロジックとエラーコードに基づいて処理することができます。例えば：
                    // タイムアウトの場合には接続を閉じないが、他の場合は直接接続を閉じる
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```

### 完全な例

```php
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5)) {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    while (true) {
        $data = $client->recv();
        if (strlen($data) > 0) {
            echo $data;
            $client->send(time() . PHP_EOL);
        } else {
            if ($data === '') {
                // 全等して空なら直接接続を閉じる
                $client->close();
                break;
            } else {
                if ($data === false) {
                    // 业务ロジックとエラーコードに基づいて処理することができます。例えば：
                    // タイムアウトの場合には接続を閉じないが、他の場合は直接接続を閉じる
                    if ($client->errCode !== SOCKET_ETIMEDOUT) {
                        $client->close();
                        break;
                    }
                } else {
                    $client->close();
                    break;
                }
            }
        }
        \Co::sleep(1);
    }
});
```
