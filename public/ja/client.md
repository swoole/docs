# Swoole\Client

`Swoole\Client`ここでは`Client`と簡単に呼びます。これは`TCP/UDP/UnixSocket`のクライアントを封じ込めたコードを提供しており、使用する際には`new Swoole\Client`を行うだけです。`FPM/Apache`環境で使用することができます。
従来の[streams](https://www.php.net/streams)シリーズの関数と比べて、いくつかの大きな利点があります：

  * `stream`関数にはデフォルトのタイムアウト時間が長く、相手方の応答時間が長い可能性があり、長時間のブロッキングにつながる可能性があります
  * `stream`関数の`fread`はデフォルトのバッファサイズが`8192`で、長いパケットをサポートできません
  * `Client`は`waitall`をサポートしており、確定したパケット長がある場合は一度に取得でき、ループで読み取る必要がありません
  * `Client`は`UDP Connect`をサポートしており、`UDP`のパケットを連続して送信する問題を解決しています
  * `Client`は純粋な`C`のコードで、`socket`を専門に処理しており、`stream`関数は非常に複雑です。`Client`の性能はより良いです
  * [swoole_client_select](/client?id=swoole_client_select)関数を使用して、複数の`Client`の並行制御を実現できます
### 完整な例

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```
## メソッド
### __construct()

構造函數

```php
Swoole\Client::__construct(int $sock_type, bool $is_sync = false, string $key);
```

* **パラメータ**

  * **`int $sock_type`**
    * **機能**：`socket`のタイプを表します【`SWOOLE_SOCK_TCP`、`SWOOLE_SOCK_TCP6`、`SWOOLE_SOCK_UDP`、`SWOOLE_SOCK_UDP6`をサポート】具体的な意味はこのセクションを参照してください[/server/methods?id=__construct]
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`bool $is_sync`**
    * **機能**：同期ブロッキングモードで、`false`にしか設定できません。非同期カスタマイズモードを使用したい場合は、`Swoole\Async\Client`を使用してください
    * **デフォルト値**：`false`
    * **その他の値**：なし

  * **`string $key`**
    * **機能**：長い接続に使用される`Key`です【デフォルトでは`IP:PORT`を`key`として使用します。同じ`key`を再度作成しても、TCP接続は1つだけ使用されます】
    * **デフォルト値**：`IP:PORT`
    * **その他の値**：なし

!> ベースラインでタイプを指定するためのマクロを使用できます。参照[定数定義](/consts)

#### PHP-FPM/Apacheで長い接続を作成する

```php
$cli = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
```

`SWOOLE_KEEP`マークを追加した後、PHPのリクエストが終了したり`$cli->close()`が呼ばれた時に、作成された`TCP`接続は閉じません。次の`connect`呼び出しでは、前回作成した接続を再利用します。長い接続の保存方法は、デフォルトで`ServerHost:ServerPort`を`key`としています。第3のパラメータで`key`を指定することができます。

`Client`オブジェクトが破棄されると、自動的に[close](/client?id=close)メソッドが呼ばれて`socket`が閉じられます。

#### ServerでClientを使用する

  * `Client`を使用するには、イベントの[カスタムハンドラ](/server/events)で使用する必要があります。
  * `Server`はどんな言語编成の`socket client`でも接続できます。同様に、`Client`もどんな言語编成の`socket server`にも接続できます。

!> `Swoole4+`の協程環境でこの`Client`を使用すると、同步模型に遷移します[/learn?id=同步io异步io]。
### set()

クライアントパラメータを設定します。接続を確立する前に実行する必要があります。

```php
Swoole\Client->set(array $settings);
```

利用可能な設定オプションはClient - [設定オプション](/client?id=配置)を参照してください。
### connect()

リモートサーバーに接続します。

```php
Swoole\Client->connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
```

* **パラメータ**

  * **`string $host`**
    * **機能**：サーバーのアドレス【自動的に異步的にドメイン名を解析できるように、$hostは直接入力できます】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $port`**
    * **機能**：サーバーのポート番号
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`float $timeout`**
    * **機能**：タイムアウト時間を設定します
    * **値の単位**：秒【浮動小数点で、例えば`1.5`は`1s+500ms`を意味します】
    * **デフォルト値**：`0.5`
    * **その他の値**：なし

  * **`int $sock_flag`**
    - `UDP`タイプの場合は、`udp_connect`を有効にするかどうかを示します。このオプションを設定した後、$hostと$portが結びつけられます。この`UDP`は、指定された$host/port以外のパケットを無視します。
    - `TCP`タイプでは、$sock_flag=1は非ブロッキング`socket`に設定され、その後このfdは[異步IO](/learn?id=同步io异步io)になります。`connect`はすぐに返ります。$sock_flagを1に設定した場合、send/recv前に[swoole_client_select](/client?id=swoole_client_select)を使用して接続が完了しているかを検出する必要があります。

* **戻り値**

  * 成功したら`true`を返します
  * 失敗したら`false`を返し、errCode属性をチェックして失敗の理由を取得してください

* **同期モード**

`connect`方法はブロッキングで、接続が成功して`true`を返ったら終了します。その時点で、サーバーにデータを送信したり受信したりすることができます。

```php
if ($cli->connect('127.0.0.1', 9501)) {
      $cli->send("data");
} else {
      echo "connect failed.";
}
```

接続が失敗したら`false`を返します。

> 同期`TCP`クライアントは`close`を実行した後、新しい接続をサーバーに再び`Connect`して作成することができます。

* **失敗したら再接続**

`connect`が失敗した後、もし再接続したい場合は、まず旧の`socket`を`close`して閉じる必要があります。そうでなければ、`EINPROCESS`エラーになります。なぜなら、現在の`socket`がサーバーに接続中であり、クライアントは接続が成功したかどうかを知りません。そのため、再び`connect`を実行することはできません。`close`を呼ぶと、現在の`socket`が閉じられ、下層で新しい`socket`が作られて接続が行われます。

!> `SWOOLE_KEEP`長い接続を有効にした後、`close`の最初のパラメータを`true`に設定して強制的に長い接続`socket`を破棄する必要があります。

```php
if ($socket->connect('127.0.0.1', 9502) === false) {
    $socket->close(true);
    $socket->connect('127.0.0.1', 9502);
}
```

* **UDP Connect**

デフォルトでは、下層は`udp_connect`を有効にしません。`UDP`クライアントが`connect`を実行すると、下層は`socket`を作成した後すぐに成功を返します。この時、この`socket`が結びつけられたアドレスは`0.0.0.0`であり、他のどの端末もこのポートにデータパケットを送信することができます。

例えば`$client->connect('192.168.1.100', 9502)`という場合、操作系统的にはクライアントの`socket`にランダムにポート番号`58232`が割り当てられます。他のマシン、例えば`192.168.1.101`もこのポートにデータパケットを送信することができます。

?> `udp_connect`が有効になっていない場合、`getsockname`が返す`host`項目は`0.0.0.0`になります

第`4`項目のパラメータを`1`に設定して`udp_connect`を有効にしましょう。この時、クライアントとサーバー端を結びつけ、下層はサーバー端のアドレスに基づいて`socket`の結びつけ先を結びつけます。例えば、`192.168.1.100`に接続した場合、現在の`socket`は`192.168.1.*`のローカルアドレスに結びつけられます。`udp_connect`が有効になると、クライアントは他のホストからこのポートに送信されるデータパケットを受信しません。
### recv()

サーバーからデータを受信します。

```php
Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
```

* **パラメータ**

  * **`int $size`**
    * **機能**：受信データのキャッシュバッファの最大長さです。【このパラメータを大きく設定しないでください。そうすると、大きなメモリを消費する可能性があります】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $flags`**
    * **機能**：追加のパラメータを設定できます。【例えば[Client::MSG_WAITALL](/client?id=clientmsg_waitall)】具体的にどのようなパラメータが使用可能かは、このセクションを参照してください[/client?id=常量]
    * **デフォルト値**：なし
    * **その他の値**：なし

* **戻り値**

  * 成功してデータを受信した場合は文字列を返します。
  * ログインが閉じた場合は空の文字列を返します。
  * 失敗した場合は `false`を返し、`$client->errCode`属性を設定します。

* **EOF/Lengthプロトコル**

  * クライアントが`EOF/Length`検出を有効にした場合、`$size`と`$waitall`のパラメータを設定する必要はありません。拡張レイヤーは完全なパケットを返すか、`false`を返します。パケット解析については、[プロトコル解析](/client?id=プロトコル解析)のセクションを参照してください。
  * パケットヘッダーが誤っているか、ヘッダー内の長さ値が[package_max_length](/server/setting?id=package_max_length)の設定を超えた場合、`recv`は空の文字列を返します。PHPコードではこの接続を閉じるべきです。
### send()

リモートサーバーにデータを送信します。接続が確立された後にのみ、相手方にデータを送信することができます。

```php
Swoole\Client->send(string $data): int|false
```

* **パラメータ**

  * **`string $data`**
    * **機能**：送信内容【バイナリデータもサポート】
    * **デフォルト値**：なし
    * **その他の値**：なし

* **戻り値**

  * 成功して送信した場合は、送信されたデータの長さを返します。
  * 失敗した場合は `false`を返し、`errCode`属性を設定します。

* **ヒント**

  * `connect`が実行されていない場合は、`send`を呼ぶと警告が発生します。
  * 送信されたデータには長さ制限はありません。
  * 送信されたデータが大きすぎてSocketのキャッシュバッファが満たされると、プログラムは書き込み可能になるのを待つためにブロッキングします。
### sendfile()

サーバーにファイルを送信します。この関数は`sendfile`オペレーティングコールを基に実装されています。

```php
Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

!> sendfileはUDPクライアントやSSLトンネルの暗号化接続には使用できません。

* **パラメータ**

  * **`string $filename`**
    * **機能**：送信するファイルのパスを指定します。
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $offset`**
    * **機能**：ファイルのオフセットを指定します。【ファイルの途中からデータを転送することができます。この機能は断点継続をサポートするために使用できます。】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $length`**
    * **機能**：送信するデータのサイズを指定します。【デフォルトではファイル全体
### getPeerName()

対端ソケットのIPアドレスとポートを取得する

!> `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM`タイプのみサポートされています

```php
Swoole\Client->getpeername(): array|false
```

`UDP`プロトコルの通信クライアントがサーバーにパケットを送信した後、そのサーバーからクライアントへの応答が返されない可能性があります。`getpeername`メソッドを使用して、実際に応答するサーバーの`IP:PORT`を取得することができます。

!> この関数は `$client->recv()`の後に呼び出す必要があります
### close()

接続を閉じる。

```php
Swoole\Client->close(bool $force = false): bool
```

* **パラメータ**

  * **`bool $force`**
    * **機能**：接続を強制的に閉じる【[SWOOLE_KEEP](/client?id=swoole_keep)長い接続を閉じるために使用】
    * **デフォルト値**：なし
    * **その他の値**：なし

`swoole_client`接続が`close`されたら、再度`connect`を行うべきではありません。正しい方法は、現在の`Client`を破棄し、新しい`Client`を作成して新しい接続を開始することです。

`Client`オブジェクトは破棄時に自動的に`close`されます。
### shutdown()

クライアントを閉じる

```php
Swoole\Client->shutdown(int $how): bool
```

* **パラメータ**

  * **`int $how`**
    * **機能**：クライアントをどのように閉じるかを設定する
    * **デフォルト値**：なし
    * **その他の値**：Swoole\Client::SHUT_RDWR（読み書きを閉じる）、SHUT_RD（読みを閉じる）、Swoole\Client::SHUT_WR（書きを閉じる）

### getSocket()

底層の`socket`ハンドルを取得し、返されるオブジェクトは`sockets`リソースハンドルです。

!> この方法は`sockets`拡張に依存しており、编译時に`--enable-sockets`オプションを有効にする必要があります

```php
Swoole\Client->getSocket()
```

`socket_set_option`関数を使用して、さらに低レベルの`socket`パラメータを設定することができます。

```php
$socket = $client->getSocket();
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
}
```
### swoole_client_select

Swoole\Clientの並行処理において、selectシステムカーネルを使用して[IOイベントループ](/learn?id=何がeventloop)を行う。epoll_waitではなく、[Eventモジュール](/event)と異なり、この関数は同期IO環境で使用される（SwooleのWorkerプロセス内で呼び出されると、Swoole自身のepoll [IOイベントループ](/learn?id=何がeventloop)が実行されない可能性があります）。

関数原型：

```php
int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);
```

* `swoole_client_select`は4つのパラメータを受け取り、`$read`, `$write`, `$error`はそれぞれ可読/可書/エラーの文件記述符です。  
* これら3つのパラメータは必ずアレイ変数でなければなりません。アレイの要素は`swoole_client`オブジェクトでなければなりません。
* この方法は`select`システムカーネルに基づいており、最大で`1024`個の`socket`をサポートします。
* `$timeout`パラメータは`select`システムカーネルのタイムアウト時間で、秒単位で浮動小数で表されます。
* 機能はPHPの元の`stream_select()`と似ていますが、違いはstream_selectはPHPのstream変数タイプだけをサポートしており、性能が低いことです。

成功した呼び出し後、イベントの数を返し、`$read`/`$write`/`$error`アレイを変更します。foreachを使用してアレイを遍历し、`$item->recv`/`$item->send`を実行してデータを送受信します。または、`$item->close()`または`unset($item)`を呼び出して`socket`を閉じることができます。

`swoole_client_select`が`0`を返すと、規定の時間内にIOが利用可能でないことを意味し、`select`呼び出しがタイムアウトしました。

!> この関数は`Apache/PHP-FPM`環境で使用できます    

```php
$clients = array();

for($i=0; $i< 20; $i++)
{
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同期ブロッキング
    $ret = $client->connect('127.0.0.1', 9501, 0.5, 0);
    if(!$ret)
    {
        echo "Connect Server fail.errCode=".$client->errCode;
    }
    else
    {
    	$client->send("HELLO WORLD\n");
    	$clients[$client->sock] = $client;
    }
}

while (!empty($clients))
{
    $write = $error = array();
    $read = array_values($clients);
    $n = swoole_client_select($read, $write, $error, 0.6);
    if ($n > 0)
    {
        foreach ($read as $index => $c)
        {
            echo "Recv #{$c->sock}: " . $c->recv() . "\n";
            unset($clients[$c->sock]);
        }
    }
}
```
## 属性
### errCode

エラーコード

```php
Swoole\Client->errCode: int
```

`connect/send/recv/close`が失敗した場合、自動的に`swoole_client->errCode`の値が設定されます。

`errCode`の値は`Linux errno`に等しく、`socket_strerror`を使用してエラーコードをエラーメッセージに変換することができます。

```php
echo socket_strerror($client->errCode);
```

参考：[Linuxエラーコードリスト](/other/errno?id=linux)
### sock

ソケット接続の文件記述符。

```php
Swoole\Client->sock;
```

PHPコードでは使用できます。

```php
$sock = fopen("php://fd/".$swoole_client->sock); 
```

* `Swoole\Client`の`socket`を`stream socket`に変換します。`fread/fwrite/fclose`などの関数を使用してプロセス操作を行うことができます。

* [Swoole\Server](/server/methods?id=__construct)の`$fd`はこの方法で変換することができません。なぜなら`$fd`は単なる数字であり、`$fd`の文件記述符は主プロセスに属しているからです。参考：[SWOOLE_PROCESS](/learn?id=swoole_process)モードを参照してください。

* `$swoole_client->sock`は数组の`key`としてintに変換することができます。

!> 注意すべき点：`$swoole_client->sock`属性の値は、`$swoole_client->connect`後にのみ取得できます。サーバーに接続される前には、この属性の値は`null`です。
### reuse

この接続が新しく作られたものか、既存のものを再利用しているかを示します。[SWOOLE_KEEP](/client?id=swoole_keep)と併用してください。

#### 使用シナリオ

`WebSocket`クライアントがサーバーと接続した後、ハンドシェイクを行う必要があります。接続が再利用されている場合は、ハンドシェイクを再度行う必要はなく、直接`WebSocket`データフレームを送信することができます。

```php
if ($client->reuse) {
    $client->send($data);
} else {
    $client->doHandShake();
    $client->send($data);
}
```
### reuseCount

この接続の再利用回数を示します。[SWOOLE_KEEP](/client?id=swoole_keep)と併用してください。

```php
Swoole\Client->reuseCount;
```
### type

`socket`のタイプを示し、`Swoole\Client::__construct()`の`$sock_type`の値を返します。

```php
Swoole\Client->type;
```
### id

`Swoole\Client::__construct()`の`$key`の値を返し、[SWOOLE_KEEP](/client?id=swoole_keep)と併用してください。

```php
Swoole\Client->id;
```
### setting

`Swoole\Client::set()`で設定されたクライアントの設定を返します。

```php
Swoole\Client->setting;
```
## 常量
### SWOOLE_KEEP

Swoole\Clientは`PHP-FPM/Apache`でTCP長い接続をサーバー側に作成することをサポートしています。使用方法：

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
```

`SWOOLE_KEEP`オプションを有効にした後、リクエストが終了しても`socket`は閉じません。次の`connect`呼び出し時には、自動的に前回作成した接続を再利用します。`connect`を実行しても接続が既にサーバーに閉じられていることを発見した場合、`connect`は新しい接続を作成します。

> SWOOLE_KEEPの利点

* `TCP`長い接続は、`connect`の3回のハンドシェイク/`close`の4回のウェイトシグナルの追加のIO消費を減らすことができます
* サーバー側の`close`/`connect`の回数を減らすことができます
### Swoole\Client::MSG_WAITALL

  * Client::MSG_WAITALLパラメータを設定した場合は、正確な`$size`を設定しなければなりません。そうでなければ、受信されるデータの長さが`$size`に達するまで待ち続けます
  * Client::MSG_WAITALLを設定していない場合、`$size`の最大値は`64K`
  * 正しい`$size`を設定していない場合、`recv`はタイムアウトし、`false`を返します
### Swoole\Client::MSG_DONTWAIT

非ブロッキングでデータを受信し、データがあってもなくてもすぐに返されます。
### Swoole\Client::MSG_PEEK

`socket`のキャッシュスペース内のデータを覗き見る。`MSG_PEEK`パラメータを設定した後、`recv`がデータを読み取る際に指針を変更せず、したがって次の`recv`调用では前回の位置からデータが返されます。
### Swoole\Client::MSG_OOB

外バイトデータを読み取る際は、「TCP外バイトデータ」を検索してください。
### Swoole\Client::SHUT_RDWR

クライアントの読み書き端を閉じます。
### Swoole\Client::SHUT_RD

クライアントの読み端を閉じます。
### Swoole\Client::SHUT_WR

クライアントの書き端を閉じます。
## 配置

`Client`は`set`メソッドを使用していくつかのオプションを設定し、特定の機能を有効にすることができます。
### プロトコル解析

?> プロトコル解析は、[TCPデータパケットの境界問題](/learn?id=tcp-data-packet-boundary-problem)を解決するためのものであり、関連する設定の意味は[Swoole\Server](/server/setting?id=open_eof-check)と同じです。詳細は[Swoole\Serverプロトコル](/server/setting?id=open_eof-check)設定セクションに移動してください。

* **エンディングフィルタリング**

```php
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n\r\n",
    'package_max_length' => 1024 * 1024 * 2,
));
```

* **長さ検査**

```php
$client->set(array(
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0, //第N個字节がパケット長さの値です
    'package_body_offset' => 4, //どの字节から長さを計算を始めます
    'package_max_length' => 2000000, //プロトコルの最大長さ
));
```

!> 現在、[open_length_check](/server/setting?id=open_length_check)と[open_eof_check](/server/setting?id=open_eof_check)の2種類の自動プロトコル処理機能がサポートされています；  
プロトコル解析を設定した後、クライアントの`recv()`メソッドは長さパラメータを受け付けず、毎回必ず完全なデータパケットを返します。

* **MQTTプロトコル**

!> `MQTT`プロトコル解析を有効にすると、[onReceive](/server/events?id=onreceive)カスタムハンドラは完全な`MQTT`データパケットを受信します。

```php
$client->set(array(
    'open_mqtt_protocol' => true,
));
```

* **Socketキャッシュスペースのサイズ**

!> `socket`の底層オペレーティングシステムのキャッシュスペース、アプリケーションレベルのデータ受信キャッシュスペース、アプリケーションレベルのデータ送信キャッシュスペースを含みます。**

```php	
$client->set(array(	
    'socket_buffer_size' => 1024 * 1024 * 2, // 2Mのキャッシュスペース	
));	
```

* **Nagle合并アルゴリズムの閉鎖**

```php
$client->set(array(
    'open_tcp_nodelay' => true,
));
```
### SSL関連

* **SSL/TLS証明書の設定**

```php
$client->set(array(
    'ssl_cert_file' => $your_ssl_cert_file_path,
    'ssl_key_file' => $your_ssl_key_file_path,
));
```

* **ssl_verify_peer**

サーバー側の証明書を検証する。

```php
$client->set([
    'ssl_verify_peer' => true,
]);
```

有効になると、証明書とホスト名が一致するかどうかを検証し、そうでない場合は自動的に接続を閉じます。

* **自己署名証明書**

`ssl_allow_self_signed`を`true`に設定することで、自己署名証明書を許可することができます。

```php
$client->set([
    '
### http_proxy

HTTPプロキシを設定します。

!> `http_proxy_port` および `http_proxy_password` は `null` であってはいけません。

* **基本設定**

```php
$client->set(array(
    'http_proxy_host' => '192.168.1.100',
    'http_proxy_port' => 1080,
));
```

* **設定の検証**

```php
$client->set(array(
    'http_proxy_user' => 'test',
    'http_proxy_password' => 'test_123456',
));
```
### bind

!> `bind_port`だけを設定することは無効です。同時に `bind_port` と `bind_address` を設定してください。

？> マシンに複数のネットカードがある場合は、`bind_address` パラメータを設定することでクライアントの `Socket` が特定のネットワークアドレスに固定されるようにします。  
`bind_port`を設定すると、クライアントの `Socket` が外部リソースに固定ポートで接続することができます。

```php
$client->set(array(
    'bind_address' => '192.168.1.100',
    'bind_port' => 36002,
));
```
### 作用範囲

上記の `Client` 設定項目は、以下のクライアントにも適用されます

  * [Swoole\Coroutine\Client](/coroutine_client/client)
  * [Swoole\Coroutine\Http\Client](/coroutine_client/http_client)
  * [Swoole\Coroutine\Http2\Client](/coroutine_client/http2_client)
