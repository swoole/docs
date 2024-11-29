```
# コロニアル・ソケット

`Swoole\Coroutine\Socket`モジュールは、[コロニアルスタイルのサーバー](/server/co_init)や[コロニアルクライアント](/coroutine_client/init)に関連するモジュールの`Socket`と比較して、より細粒度のいくつかの`IO`操作を実現することができます。

!> `Co\Socket`の短い名前を使用してクラス名を短縮することができます。このモジュールは比較的低レベルであり、使用者はソケットプログラミングの経験を持つことをお勧めします。


## 完全な例

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9601);
    while ($retval)
    {
        $n = $socket->send('hello');
        var_dump($n);

        $data = $socket->recv();
        var_dump($data);

        //エラーが発生したり、相手方が接続を閉じたら、こちらも接続を閉じる必要があります
        if ($data === '' || $data === false) {
            echo "errCode: {$socket->errCode}\n";
            $socket->close();
            break;
        }

        Coroutine::sleep(1.0);
    }

    var_dump($retval, $socket->errCode, $socket->errMsg);
});
```


## コロニアルスケジュール

`Coroutine\Socket`モジュールが提供する`IO`操作のインターフェースはすべて同期プログラミングスタイルであり、低レベルでは自動的に[コロニアルスケジュール](/coroutine?id=协程调度)器を使用して[非同期IO](/learn?id=同步io异步io)を実現します。


## エラーコード

`socket`関連のシステム呼び出しを実行すると、-1のエラーが返される可能性があります。低レベルでは`Coroutine\Socket->errCode`属性にシステムエラー番号`errno`を設定しますので、対応する`man`ドキュメントを参照してください。例えば、`$socket->accept()`がエラーを返した場合、`errCode`の意味は`man accept`で列挙されているエラーコードのドキュメントを参照してください。


## プロパティ


### fd

`socket`に対応するファイル記述子の`ID`


### errCode

エラーコード


## メソッド


### __construct()

コンストラクタです。`Coroutine\Socket`オブジェクトを構築します。

```php
Swoole\Coroutine\Socket::__construct(int $domain, int $type, int $protocol);
```

!> 詳細は`man socket`ドキュメントを参照してください。

  * **パラメータ** 

    * **`int $domain`**
      * **機能**：プロトコル領域【`AF_INET`、`AF_INET6`、`AF_UNIX`を使用できます】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $type`**
      * **機能**：タイプ【`SOCK_STREAM`、`SOCK_DGRAM`、`SOCK_RAW`を使用できます】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $protocol`**
      * **機能**：プロトコル【`IPPROTO_TCP`、`IPPROTO_UDP`、`IPPROTO_STCP`、`IPPROTO_TIPC`、`0`を使用できます】
      * **デフォルト値**：なし
      * **その他の値**：なし

!> コンストラクタでは`socket`システム呼び出しを调用して`socket`ハンドラを作成します。呼び出しに失敗すると`Swoole\Coroutine\Socket\Exception`例外が投げられ、`$socket->errCode`属性が設定されます。この属性的値に基づいてシステム呼び出しが失敗した理由を得ることができます。


### getOption()

設定を取得します。

!> この方法は`getsockopt`システム呼び出しに対応し、詳細は`man getsockopt`ドキュメントを参照してください。  
この方法は`sockets`拡張の`socket_get_option`機能と同等であり、[PHPドキュメント](https://www.php.net/manual/zh/function.socket-get-option.php)を参照してください。

!> Swooleバージョン >= v4.3.2

```php
Swoole\Coroutine\Socket->getOption(int $level, int $optname): mixed
```

  * **パラメータ** 

    * **`int $level`**
      * **機能**：オプションが位置するプロトコルレベルを指定します
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> 例として、ソケットレベルでオプションを検索する場合、`SOL_SOCKET`の `level` パラメータを使用します。  
      他のレベル（例えば`TCP`）を使用するためには、そのレベルのプロトコル番号を指定することができます。例えば、[getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php)関数を使用してプロトコル番号を見つけることができます。

    * **`int $optname`**
      * **機能**：使用可能なソケットオプションは[socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)関数のソケットオプションと同じです
      * **デフォルト値**：なし
      * **その他の値**：なし


### setOption()

設定を設定します。

!> この方法は`setsockopt`システム呼び出しに対応し、詳細は`man setsockopt`ドキュメントを参照してください。この方法は`sockets`拡張の`socket_set_option`機能と同等であり、[PHPドキュメント](https://www.php.net/manual/zh/function.socket-set-option.php)を参照してください。

!> Swooleバージョン >= v4.3.2

```php
Swoole\Coroutine\Socket->setOption(int $level, int $optname, mixed $optval): bool
```

  * **パラメータ** 

    * **`int $level`**
      * **機能**：オプションが位置するプロトコルレベルを指定します
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> 例として、ソケットレベルでオプションを設定する場合、`SOL_SOCKET`の `level` パラメータを使用します。  
      他のレベル（例えば`TCP`）を使用するためには、そのレベルのプロトコル番号を指定することができます。例えば、[getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php)関数を使用してプロトコル番号を見つけることができます。

    * **`int $optname`**
      * **機能**：使用可能なソケットオプションは[socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)関数のソケットオプションと同じです
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $optval`**
      * **機能**：オプションの値 【`int`、`bool`、`string`、`array` olabilir。`level`と`optname`に応じて異なります。】
      * **デフォルト値**：なし
      * **その他の値**：なし


### setProtocol()

`socket`にプロトコル処理能力を取得させることができます。SSL暗号化伝送を有効にするか、TCPデータパケットの境界問題を解決するかなど、設定できます。

!> Swooleバージョン >= v4.3.2

```php
Swoole\Coroutine\Socket->setProtocol(array $settings): bool
```

  * **$settings 支持されるパラメータ**


パラメータ | 型
---|---
open_ssl | bool
ssl_cert_file | string
ssl_key_file | string
open_eof_check | bool
open_eof_split | bool
open_mqtt_protocol | bool
open_fastcgi_protocol | bool
open_length_check | bool
package_eof | string
package_length_type | string
package_length_offset | int
package_body_offset | int
package_length_func | callable
package_max_length | int

!> 上記のすべてのパラメータの意味は[Server->set()](/server/setting?id=open_eof_check)と完全に一致しているため、ここでは詳述しません。

  * **例**

```php
$socket->setProtocol([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,
    'package_body_offset'   => 4,
]);
```


### bind()

アドレスとポートをバインドします。

!> この方法は`IO`操作がなく、コロニアルの切り替えを引き起こすことはありません

```php
Swoole\Coroutine\Socket->bind(string $address, int $port = 0): bool
```

  * **パラメータ** 

    * **`string $address`**
      * **機能**：バインドされるアドレス【例えば`0.0.0.0`、`127.0.0.1`】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：バインドされるポート【デフォルトは`0`で、システムはランダムに利用可能なポートを割り当てます。利用可能なポートは[getsockname](/coroutine_client/socket?id=getsockname)方法で取得できます】
      * **デフォルト値**：`0`
      * **その他の値**：なし

  * **戻り値** 

    *バインドに成功すると`true`が返ります
    * バインドに失敗すると`false`が返りますが、`errCode`属性を参照して失敗の理由を得ることができます
```
### listen()

ソケットを待ち受ける。

> この方法はIO操作がなく、コーラブの切り替えを引き起こさない

```php
Swoole\Coroutine\Socket->listen(int $backlog = 0): bool
```

  * **引数** 

    * **`int $backlog`**
      * **機能**：待ち受けキューの長さ【デフォルトは`0`で、システム底层は`epoll`を使用して非同期IOを実現しており、ブロッキングがないため、`backlog`の重要性はそれほど高くない】
      * **デフォルト値**：`0`
      * **その他の値**：なし

      >  응용中にブロッキングまたは時間がかかるロジックが存在し、`accept`が接続を受け付けられない場合、新しく作成された接続は`backlog`の待ち受けキューに蓄積され、`backlog`の長さを超えると、サービスは新しい接続を受け付けないようになります

  * **戻り値** 

    * 绑定に成功すると`true`を返す
    * 绑定に失敗すると`false`を返し、`errCode`属性を使用して失敗の原因を取得する

  * **カーネルパラメータ** 

    `backlog`の最大値はカーネルパラメータ`net.core.somaxconn`によって制限されており、Linuxではツール`sysctl`を使用してすべての`kernel`パラメータを動的に調整できます。動的調整はカーネルパラメータ値が変更された後すぐに有効になります。しかし、この有効性はOSレベルでのみであり、アプリケーションを再起動して本当に有効になる必要があります。コマンド`sysctl -a`はすべてのカーネルパラメータとその値を表示します。

    ```shell
    sysctl -w net.core.somaxconn=2048
    ```

    上のコマンドはカーネルパラメータ`net.core.somaxconn`の値を`2048`に変更しました。このような変更は即座に有効になりますが、マシンを再起動するとデフォルト値に戻ります。変更を永久に保持するには、`/etc/sysctl.conf`を編集し、`net.core.somaxconn=2048`を追加し、`sysctl -p`を実行して効果を発揮する必要があります。


### accept()

クライアントからの接続を受け入れる。

この方法を呼び出すと、現在のコーラブは直ちにフックされ、[EventLoop](/learn?id=什么是eventloop)で読み取り可能イベントを監視します。ソケットが読み取り可能で接続が到来すると、自動的にそのコーラブを呼び覚まし、対応するクライアント接続のソケットオブジェクトを返します。

> この方法は`listen`方法を使用した後に使用し、サーバー側で使用する必要があります。

```php
Swoole\Coroutine\Socket->accept(float $timeout = 0): Coroutine\Socket|false;
```

  * **引数** 

    * **`float $timeout`**
      * **機能**：タイムアウトを設定する【タイムアウトパラメータを設定した後、底层はタイマーを設定し、指定の時間内にクライアント接続が到来しなければ、`accept`方法は`false`を返す】
      * **値の単位**：秒【浮点数をサポートし、例えば`1.5`は`1s`+`500ms`を表す】
      * **デフォルト値**：[クライアントタイムアウトルール](/coroutine_client/init?id=超时规则)を参照
      * **その他の値**：なし

  * **戻り値** 

    * タイムアウトまたは`accept`システム呼び出しがエラーを報告した場合に`false`を返し、`errCode`属性を使用してエラーコードを取得することができます。タイムアウトエラーコードは`ETIMEDOUT`です
    * 成功した場合は、クライアント接続の`socket`を返し、同様に`Swoole\Coroutine\Socket`オブジェクトです。これに対して`send`、`recv`、`close`などの操作を行うことができます

  * **例**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
$socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
$socket->bind('127.0.0.1', 9601);
$socket->listen(128);

    while(true) {
        echo "Accept: \n";
        $client = $socket->accept();
        if ($client === false) {
            var_dump($socket->errCode);
        } else {
            var_dump($client);
        }
    }
});
```


### connect()

ターゲットサーバーに接続する。

この方法を呼び出すと、非同期の`connect`システム呼び出しが開始され、現在のコーラブがフックされます。底层は書き取り可能を監視し、接続が完了するか失敗すると、そのコーラブを復帰させます。

この方法はクライアント側で使用し、`IPv4`、`IPv6`、[unixSocket](/learn?id=什么是IPC)をサポートしています。

```php
Swoole\Coroutine\Socket->connect(string $host, int $port = 0, float $timeout = 0): bool
```

  * **引数** 

    * **`string $host`**
      * **機能**：ターゲットサーバーのアドレス【例えば`127.0.0.1`、`192.168.1.100`、`/tmp/php-fpm.sock`、`www.baidu.com`など、IPアドレス、Unix Socketパス、またはドメイン名を渡すことができます。ドメイン名を渡すと、底层は自動的に非同期のDNS解析を行い、ブロッキングを引き起こしません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：ターゲットサーバーのポート【`Socket`の`domain`が`AF_INET`、`AF_INET6`の場合に必ずポートを指定する必要がある】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：接続タイムアウト時間を設定する【底层はタイマーを設定し、指定の時間内に接続が確立されなければ、`connect`は`false`を返す】
      * **値の単位**：秒【浮点数をサポートし、例えば`1.5`は`1s`+`500ms`を表す】
      * **デフォルト値**：[クライアントタイムアウトルール](/coroutine_client/init?id=超时规则)を参照
      * **その他の値**：なし

  * **戻り値** 

    * タイムアウトまたは`connect`システム呼び出しがエラーを報告した場合に`false`を返し、`errCode`属性を使用してエラーコードを取得することができます。タイムアウトエラーコードは`ETIMEDOUT`です
    * 成功した場合は`true`を返す


### checkLiveness()

システム呼び出しを通じて接続が生きているかどうかを確認する（異常な切断時には無効であり、対端が通常通りcloseした接続の切断のみを検出しることができます）

> Swooleバージョン >= `v4.5.0`で利用可能

```php
Swoole\Coroutine\Socket->checkLiveness(): bool
```

  * **戻り値** 

    * 接続が生きている場合は`true`を返し、そうでなければ`false`を返す


### send()

対方にデータを送信する。

> `send`方法はすぐに`send`システム呼び出しを行いデータを送信しますが、`send`システム呼び出しがエラー`EAGAIN`を報告した場合、底层は自動的に書き取り可能イベントを監視し、現在のコーラブをフックします。書き取り可能イベントが発生すると、再び`send`システム呼び出しを行いデータを送信し、そのコーラブを呼び覚まします。  

> `send`が速すぎたり、`recv`が遅すぎると、最終的にはオペレーティングシステムのバッファが満たされることになり、現在のコーラブは`send`方法でフックされます。バッファの大きさを適切に調整する必要があります。[/proc/sys/net/core/wmem_maxとSO_SNDBUF](https://stackoverflow.com/questions/21856517/whats-the-practical-limit-on-the-size-of-single-packet-transmitted-over-domain)

```php
Swoole\Coroutine\Socket->send(string $data, float $timeout = 0): int|false
```

  * **引数** 

    * **`string $data`**
      * **機能**：送信したいデータ内容【テキストまたはバイナリデータ均可】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **値の単位**：秒【浮点数をサポートし、例えば`1.5`は`1s`+`500ms`を表す】
      * **デフォルト値**：[クライアントタイムアウトルール](/coroutine_client/init?id=超时规则)を参照
      * **その他の値**：なし

  * **戻り値** 

    * 送信に成功した場合は、書き込まれたバイト数を返します。**実際には送信されたデータの長さが`$data`パラメータの長さと等しくない可能性があります。アプリケーション層のコードでは、戻り値と`strlen($data)`を比較して送信が完了したかどうかを判断する必要があります**
    * 送信に失敗した場合は`false`を返し、`errCode`属性を設定します

### sendAll()

対端にデータを送信します。`send`メソッドと異なり、`sendAll`はできるだけ完全なデータを送信し、全てのデータが成功して送信されるか、エラーが発生して中止されるまで続けます。

!> `sendAll`メソッドは、すぐに何度も`send`システム呼び出しを行いデータを送信します。`send`システム呼び出しがエラー`EAGAIN`を返した場合、基層は自動的に書き込めるイベントを監視し、現在の协程を挂起して待ちます。書き込めるイベントが発生した時、再び`send`システム呼び出しを行いデータを送信し、データの送信が完了するかエラーが発生するまで繰り返します。  

!> Swooleバージョン >= v4.3.0

```php
Swoole\Coroutine\Socket->sendAll(string $data, float $timeout = 0) : int | false;
```

  * **パラメータ** 

    * **`string $data`**
      * **機能**：送信したいデータ内容【テキストまたはバイナリデータ可】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数もサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値** 

    * `sendAll`はデータが全て成功して送信されると保証しますが、`sendAll`の間に対端が接続を切断する可能性があります。その場合、一部のデータが成功して送信されたかもしれませんが、戻り値はその成功したデータの長さを返します。アプリケーション層のコードは、戻り値と`strlen($data)`が等しいかどうかを比較して、送信が完了したかどうかを判断し、ビジネスの要件に応じて再送が必要かどうかを決定する必要があります。
    * 送信に失敗すると`false`を返し、`errCode`プロパティを設定します。


### peek()

読みバッファ中のデータを覗き見ます。これはシステム呼び出しにおける`recv(length, MSG_PEEK)`に相当します。

!> `peek`は即座に完了し、协程を挂起しませんが、システム呼び出しのコストは発生します

```php
Swoole\Coroutine\Socket->peek(int $length = 65535): string|false
```

  * **パラメータ** 

    * **`int $length`**
      * **機能**：覗き見したいデータの内存サイズを指定します (注意：ここでは内存を割り当てますが、大きすぎる長さは内存不足を引き起こす可能性があります)
      * **単位**：字节
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値** 

    * 覗き見に成功するとデータが返されます
    * 覗き見に失敗すると`false`を返し、`errCode`プロパティを設定します


### recv()

データを受信します。

!> `recv`メソッドは、現在の协程を即座に挂起し、読み取り可能イベントを監視し、対端からデータを送信した後に、読み取り可能イベントが発生した時に`recv`システム呼び出しを行い、`socket`バッファ内のデータを取得し、その协程を呼び覚まします。

```php
Swoole\Coroutine\Socket->recv(int $length = 65535, float $timeout = 0): string|false
```

  * **パラメータ** 

    * **`int $length`**
      * **機能**：受信したいデータの内存サイズを指定します (注意：ここでは内存を割り当てますが、大きすぎる長さは内存不足を引き起こす可能性があります)
      * **単位**：字节
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数もサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値** 

    * 受信に成功すると実際のデータが返されます
    * 受信に失敗すると`false`を返し、`errCode`プロパティを設定します
    * 受信タイムアウトでは、エラーコードは`ETIMEDOUT`です

!> 戻り値は必ずしも予想される長さとは等しくありません。この呼び出しで受信したデータの長さを自ら確認する必要があります。一度の呼び出しで指定された長さのデータを取得する必要がある場合は、`recvAll`メソッドを使用するか、自らループして取得する必要があります。  
TCPパケットの境界問題については、`setProtocol()`メソッドを参照するか、`sendto()`を使用してください；


### recvAll()

データを受信します。`recv`と異なり、`recvAll`はできるだけ完全な応答長さのデータを受信し、受信が完了するかエラーが発生するまで続けます。

!> `recvAll`メソッドは、現在の协程を即座に挂起し、読み取り可能イベントを監視し、対端からデータを送信した後に、読み取り可能イベントが発生した時に`recv`システム呼び出しを行い、`socket`バッファ内のデータを取得し、その行為を繰り返して指定長さのデータを受信するかエラーが発生するまで続け、その协程を呼び覚まします。

!> Swooleバージョン >= v4.3.0

```php
Swoole\Coroutine\Socket->recvAll(int $length = 65535, float $timeout = 0): string|false
```

  * **パラメータ** 

    * **`int $length`**
      * **機能**：受信したいデータの大きさ【请注意、ここでは内存を割り当てますが、大きすぎる長さは内存不足を引き起こす可能性があります】
      * **単位**：字节
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数もサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値** 

    * 受信に成功すると実際のデータが返され、戻り値の文字列の長さはパラメータの長さと一致します
    * 受信に失敗すると`false`を返し、`errCode`プロパティを設定します
    * 受信タイムアウトでは、エラーコードは`ETIMEDOUT`です


### readVector()

データを分段して受信します。

!> `readVector`メソッドは、すぐに`readv`システム呼び出しを行いデータを読み取ります。`readv`システム呼び出しがエラー`EAGAIN`を返した場合、基層は自動的に読み取り可能イベントを監視し、現在の协程を挂起して待ちます。読み取り可能イベントが発生した時、再び`readv`システム呼び出しを行いデータを読み取り、その协程を呼び覚まします。  

!> Swooleバージョン >= v4.5.7

```php
Swoole\Coroutine\Socket->readVector(array $io_vector, float $timeout = 0): array|false
```

  * **パラメータ** 

    * **`array $io_vector`**
      * **機能**：分段して受信したいデータの大きさ
      * **単位**：字节
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数もサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値**

    * 受信に成功すると分段して受信したデータが返されます
    * 受信に失敗すると空の配列が返され、`errCode`プロパティが設定されます
    * 受信タイムアウトでは、エラーコードは`ETIMEDOUT`です

  * **例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
//もし対端から"helloworld"を送ってきた場合
$ret = $socket->readVector([5, 5]);
//すると、$retは['hello', 'world']になります
```


### readVectorAll()

データを分段して受信します。

!> `readVectorAll`メソッドは、すぐに何度も`readv`システム呼び出しを行いデータを読み取ります。`readv`システム呼び出しがエラー`EAGAIN`を返した場合、基層は自動的に読み取り可能イベントを監視し、現在の协程を挂起して待ちます。読み取り可能イベントが発生した時、再び`readv`システム呼び出しを行いデータを読み取り、データの読み取りが完了するかエラーが発生するまで繰り返します。

!> Swooleバージョン >= v4.5.7

```php
Swoole\Coroutine\Socket->readVectorAll(array $io_vector, float $timeout = 0): array|false
```

  * **パラメータ** 

    * **`array $io_vector`**
      * **機能**：分段して受信したいデータの大きさ
      * **単位**：字节
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数もサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値**

    * 受信に成功すると分段して受信したデータが返されます
    * 受信に失敗すると空の配列が返され、`errCode`プロパティが設定されます
    * 受信タイムアウトでは、エラーコードは`ETIMEDOUT`です
### writeVector()

分割してデータを送信します。

>`writeVector`方法はすぐに`writev`システム呼び出しでデータを送信し、`writev`システム呼び出しがエラー`EAGAIN`を返した場合、基層は自動的に writableイベントを監視し、現在の协程を挂起して、writableイベントが発生したときに`writev`システム呼び出しを再実行してデータを送信し、その协程を呼び覚まします。  

>Swooleバージョン >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVector(array $io_vector, float $timeout = 0): int|false
```

  * **引数** 

    * **`array $io_vector`**
      * **機能**：送信したい分割されたデータ
      * **単位**：字节
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値**

    *送信に成功すると、書き込んだバイト数を返します。**実際に書き込んだデータは`$io_vector`引数の総長よりも小さい可能性があります**。アプリケーション層のコードでは、戻り値と`$io_vector`引数の総長を比較して、送信が完了したかどうかを判断する必要があります。
    *送信に失敗すると`false`を返し、`errCode`属性を設定します。

  * **例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 以下は、配列の順に対側に送信し、実際には"helloworld"を送信します
$socket->writeVector(['hello', 'world']);
```


### writeVectorAll()

対側にデータを送信します。`writeVector`方法与不同的是、`writeVectorAll`はできるだけ完全なデータを送信し、全てのデータを成功して送信するか、エラーで中止するまで続けます。

>`writeVectorAll`方法はすぐに何度も`writev`システム呼び出しでデータを送信し、`writev`システム呼び出しがエラー`EAGAIN`を返した場合、基層は自動的にwritableイベントを監視し、現在の协程を挂起して、writableイベントが発生したときに`writev`システム呼び出しを再実行してデータを送信し、データの送信が完了するかエラーが発生するまで続け、対応する协程を呼び覚まします。

>Swooleバージョン >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVectorAll(array $io_vector, float $timeout = 0): int|false
```

  * **引数** 

    * **`array $io_vector`**
      * **機能**：送信したい分割されたデータ
      * **単位**：字节
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値**

    * `writeVectorAll`はデータの完全な送信を保証しますが、`writeVectorAll`の間に対側が接続を切断する可能性があります。この場合、成功して送信されたデータの一部が返されるかもしれません。戻り値は成功して送信されたデータの長さを返します。アプリケーション層のコードでは、戻り値と`$io_vector`引数の総長を比較して、送信が完了したかどうかを判断する必要があります。ビジネス要件に応じて、再送が必要かどうかを判断する必要があります。
    *送信に失敗すると`false`を返し、`errCode`属性を設定します。

  * **例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// 以下は、配列の順に対側に送信し、実際には"helloworld"を送信します
$socket->writeVectorAll(['hello', 'world']);
```


### recvPacket()

`setProtocol`方法で設定されたプロトコルを持つSocket对象に対して、この方法で完全なプロトコルデータパケットを受け取ることができます。

>Swooleバージョン >= v4.4.0

```php
Swoole\Coroutine\Socket->recvPacket(float $timeout = 0): string|false
```

  * **引数** 
    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **単位**：秒【浮点数をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
      * **その他の値**：なし

  * **戻り値** 

    * 成功すると完全なプロトコルデータパケットを返します。
    * 失敗すると`false`を返し、`errCode`属性を設定します。
    * 受信タイムアウトでは、エラーコードは`ETIMEDOUT`です。


### recvLine()

[socket_read](https://www.php.net/manual/en/function.socket-read.php)の互換性問題 解决用

```php
Swoole\Coroutine\Socket->recvLine(int $length = 65535, float $timeout = 0): string|false
```


### recvWithBuffer()

`recv(1)`を逐字节受信すると、多くのシステム呼び出しが発生する問題を解決するため

```php
Swoole\Coroutine\Socket->recvWithBuffer(int $length = 65535, float $timeout = 0): string|false
```


### recvfrom()

データを受け取り、送信元ホストのアドレスとポートを設定します。`SOCK_DGRAM`タイプの`socket`に使用されます。

>この方法は[协程调度](/coroutine?id=协程调度)を引き起こし、基層は現在の协程を直ちに挂起し、読み取り可能イベントを監視します。読み取り可能イベントが発生し、データを受け取った後、`recvfrom`システム呼び出しを実行してデータパケットを取得します。

```php
Swoole\Coroutine\Socket->recvfrom(array &$peer, float $timeout = 0): string|false
```

* **引数**

    * **`array $peer`**
        * **機能**：相手方のアドレスとポート、参照タイプ。【関数が成功返回した場合は、配列が設定され、「address」と「port」の2つの要素が含まれます】
        * **デフォルト値**：なし
        * **その他の値**：なし

    * **`float $timeout`**
        * **機能**：タイムアウト時間を設定する【指定された時間内にデータが返されない場合、「recvfrom」方法は「false」を返します】
        * **単位**：秒【浮点数をサポートしており、例えば「1.5」は「1s」+「500ms」を意味します】
        * **デフォルト値**：[クライアントのタイムアウトルール](/coroutine_client/init?id=タイムアウトルール)を参照
        * **その他の値**：なし

* **戻り値**

    * 成功してデータを受け取ると、データ内容を返し、「$peer」を配列に設定します。
    * 失敗すると「false」を返し、「errCode」属性を設定しますが、「$peer」の内容は変更しません。

* **例**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    while (true) {
        $peer = null;
        $data = $socket->recvfrom($peer);
        echo "[Server] recvfrom[{$peer['address']}:{$peer['port']}] : $data\n";
        $socket->sendto($peer['address'], $peer['port'], "Swoole: $data");
    }
});
```


### sendto()

指定されたアドレスとポートにデータを送信します。`SOCK_DGRAM`タイプの`socket`に使用されます。

>この方法は[协程调度](/coroutine?id=协程调度)ありません。基層は直ちに`sendto`を呼び出してターゲットホストにデータを送信します。この方法は可書きイベントを監視しません。「sendto」はバッファが満ちているために「false」を返す可能性があります。自分で処理する必要があるか、または「send」方法を使用する必要があります。

```php
Swoole\Coroutine\Socket->sendto(string $address, int $port, string $data): int|false
```

  * **引数** 

    * **`string $address`**
      * **機能**：ターゲットホストの`IP`アドレスまたは[unixSocket](/learn?id=什么是IPC)パス【「sendto」はドメイン名をサポートしていません。`AF_INET`または`AF_INET6`を使用している場合、有効な`IP`アドレスを渡さなければ送信に失敗します】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $port`**
      * **機能**：ターゲットホストのポート【ブロードキャストを送信する場合は`0`即可】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $data`**
      * **機能**：送信するデータ【テキストまたはバイナリコンテンツ均可ですが、「SOCK_DGRAM」でのパケットの最大長さは`64K`です】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値** 

    * 成功すると送信したバイト数を返します。
    * 失敗すると「false」を返し、「errCode」属性を設定しますが、

  * **例** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
$socket->sendto('127.0.0.1', 9601, 'Hello');
```
### getsockname()

ソケットのアドレスとポート情報を取得します。

> この方法は[コリアブスケジュール](/coroutine?id=协程调度)のコストはかかりません。

```php
Swoole\Coroutine\Socket->getsockname(): array|false
```

  * **戻り値** 

    * 成功した場合は、`address`と`port`の配列が返ります
    * 失敗した場合は`false`が返り、`errCode`プロパティが設定されます


### getpeername()

ソケットの対端アドレスとポート情報を取得します。これは`SOCK_STREAM`タイプで接続されているソケットにのみ使用されます。

> この方法は[コリアブスケジュール](/coroutine?id=协程调度)のコストはかかりません。

```php
Swoole\Coroutine\Socket->getpeername(): array|false
```

  * **戻り値** 

    * 成功した場合は、`address`と`port`の配列が返ります
    * 失敗した場合は`false`が返り、`errCode`プロパティが設定されます


### close()

ソケットを閉じます。

> `Swoole\Coroutine\Socket`オブジェクトが析構される際に自動的に`close`が実行される場合は、この方法には[コリアブスケジュール](/coroutine?id=协程调度)のコストはかかりません。

```php
Swoole\Coroutine\Socket->close(): bool
```

  * **戻り値** 

    * 成功した場合は`true`が返ります
    * 失敗した場合は`false`が返ります
    

### isClosed()

ソケットが閉じられているかどうかを判定します。

```php
Swoole\Coroutine\Socket->isClosed(): bool
```

## 定数

`sockets`拡張で提供される定数と同等であり、`sockets`拡張と衝突することはありません。

> 異なるシステムでの値は異なります。以下のコードは例に過ぎず、使用しないでください。

```php
define ('AF_UNIX', 1);
define ('AF_INET', 2);

/**
 * IPv6サポートがcompileされた場合にのみ利用できます。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('AF_INET6', 10);
define ('SOCK_STREAM', 1);
define ('SOCK_DGRAM', 2);
define ('SOCK_RAW', 3);
define ('SOCK_SEQPACKET', 5);
define ('SOCK_RDM', 4);
define ('MSG_OOB', 1);
define ('MSG_WAITALL', 256);
define ('MSG_CTRUNC', 8);
define ('MSG_TRUNC', 32);
define ('MSG_PEEK', 2);
define ('MSG_DONTROUTE', 4);

/**
 * Windowsプラットフォームでは利用できません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOR', 128);

/**
 * Windowsプラットフォームでは利用できません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOF', 512);
define ('MSG_CONFIRM', 2048);
define ('MSG_ERRQUEUE', 8192);
define ('MSG_NOSIGNAL', 16384);
define ('MSG_DONTWAIT', 64);
define ('MSG_MORE', 32768);
define ('MSG_WAITFORONE', 65536);
define ('MSG_CMSG_CLOEXEC', 1073741824);
define ('SO_DEBUG', 1);
define ('SO_REUSEADDR', 2);

/**
 * PHP 5.4.10以降のプラットフォームで利用可能であり、<b>SO_REUSEPORT</b>ソケットオプションをサポートしているプラットフォームにのみ利用できます。これにはMac OS XとFreeBSDが含まれますが、LinuxやWindowsは含まれません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SO_REUSEPORT', 15);
define ('SO_KEEPALIVE', 9);
define ('SO_DONTROUTE', 5);
define ('SO_LINGER', 13);
define ('SO_BROADCAST', 6);
define ('SO_OOBINLINE', 10);
define ('SO_SNDBUF', 7);
define ('SO_RCVBUF', 8);
define ('SO_SNDLOWAT', 19);
define ('SO_RCVLOWAT', 18);
define ('SO_SNDTIMEO', 21);
define ('SO_RCVTIMEO', 20);
define ('SO_TYPE', 3);
define ('SO_ERROR', 4);
define ('SO_BINDTODEVICE', 25);
define ('SOL_SOCKET', 1);
define ('SOMAXCONN', 128);

/**
 * Nagle TCPアルゴリズムを無効にするために使用されます。
 * PHP 5.2.7で追加されました。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('TCP_NODELAY', 1);
define ('PHP_NORMAL_READ', 1);
define ('PHP_BINARY_READ', 2);
define ('MCAST_JOIN_GROUP', 42);
define ('MCAST_LEAVE_GROUP', 45);
define ('MCAST_BLOCK_SOURCE', 43);
define ('MCAST_UNBLOCK_SOURCE', 44);
define ('MCAST_JOIN_SOURCE_GROUP', 46);
define ('MCAST_LEAVE_SOURCE_GROUP', 47);
define ('IP_MULTICAST_IF', 32);
define ('IP_MULTICAST_TTL', 33);
define ('IP_MULTICAST_LOOP', 34);
define ('IPV6_MULTICAST_IF', 17);
define ('IPV6_MULTICAST_HOPS', 18);
define ('IPV6_MULTICAST_LOOP', 19);
define ('IPV6_V6ONLY', 27);

/**
 * 操作が許可されていません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPERM', 1);

/**
 * 指定されたファイルやディレクトリが存在しません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOENT', 2);

/**
 * 中断されたシステム呼び出しです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINTR', 4);

/**
 * I/Oエラーです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIO', 5);

/**
 * 指定されたデバイスやアドレスが存在しません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENXIO', 6);

/**
 * 引数のリストが長すぎます。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_E2BIG', 7);

/**
 * 不正なファイル番号です。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADF', 9);

/**
 * 再試してください。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAGAIN', 11);

/**
 * メモリ不足です。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEM', 12);

/**
 * 許可がありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EACCES', 13);

/**
 * 不正なアドレスです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EFAULT', 14);

/**
 * ブロックデバイスが必要です。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTBLK', 15);

/**
 * デバイスやリソースが忙しいです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBUSY', 16);

/**
 * ファイルが存在します。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EEXIST', 17);

/**
 * クロスデバイスのリンクです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXDEV', 18);

/**
 * 指定されたデバイスが存在しません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODEV', 19);

/**
 *ディレクトリではありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTDIR', 20);

/**
 *ディレクトリです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISDIR', 21);

/**
 * 引数の种类が間違っています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINVAL', 22);

/**
 * ファイルテーブルが溢れています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENFILE', 23);

/**
 * 開いているファイルが多すぎます。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMFILE', 24);

/**
 * 打字機ではありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTTY', 25);

/**
 * デバイスにスペースがありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSPC', 28);

/**
 * 不正なシークです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESPIPE', 29);

/**
 * 只読ファイルシステムです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EROFS', 30);

/**
 * リンクが多すぎます。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMLINK', 31);

/**
 * パイプが壊れています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPIPE', 32);

/**
 * ファイル名が長すぎます。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENAMETOOLONG', 36);

/**
 * 必要なメッセージのタイプがありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLCK', 37);

/**
 * 機能が実現されていません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSYS', 38);

/**
 * ディレクトリが空ではありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTEMPTY', 39);

/**
 * 途中に多くのシンボリックリンクに遭遇しました。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELOOP', 40);

/**
 * 操作がブロックされるでしょう。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EWOULDBLOCK', 11);

/**
 * 必要なタイプのメッセージがありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMSG', 42);

/**
 *識別子が削除されました。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIDRM', 43);

/**
 *チャネル番号が範囲を超えています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ECHRNG', 44);

/**
 *レベル2が同期されていません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2NSYNC', 45);

/**
 *レベル3が停止しています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3HLT', 46);

/**
 *レベル3がリセットされました。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL3RST', 47);

/**
 *リンク番号が範囲を超えています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ELNRNG', 48);

/**
 *プロトコルドライバーが接続されていません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EUNATCH', 49);

/**
 * CSI構造体が利用できません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOCSI', 50);

/**
 *レベル2が停止しています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EL2HLT', 51);

/**
 * 無効な交換です。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADE', 52);

/**
 * 無効なリクエスト記述子です。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADR', 53);

/**
 * 交換が満ちています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXFULL', 54);

/**
 * アノードがありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOANO', 55);

/**
 * 無効なリクエストコードです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADRQC', 56);

/**
 * 無効なスロットです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADSLT', 57);

/**
 * デバイスがストリームではありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSTR', 60);

/**
 * データがありません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODATA', 61);

/**
 * タイマーが期限切れです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ETIME', 62);

/**
 * ストリームリソースが不足しています。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOSR', 63);

/**
 * マシンがネットワークに接続されていません。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENONET', 64);

/**
 * オブジェクトがリモートです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EREMOTE', 66);

/**
 * リンクが切断されました。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOLINK', 67);

/**
 * アンounceエラーです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EADV', 68);

/**
 * Srmountエラーです。
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ESRMNT', 69);

/**
 * 送信時のコミュニケーションエラーです。
 * @link http://
