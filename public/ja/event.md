# 事件

`Swoole`拡張は、底層の`epoll/kqueue`イベントループを直接操作するインターフェースも提供しています。他の拡張で作成された`socket`や、`PHP`コード内の`stream/socket`拡張で作成された`socket`などを`Swoole`の[EventLoop](/learn?id=何がeventloop)に加えることができます。
そうでなければ、サードパーティの$fdが同期IOである場合、SwooleのEventLoopは実行されない可能性があります。[参考ケース](/learn?id=同期ioを異步ioに変換)

!> `Event`モジュールは比較的基礎的で、`epoll`の初級カプセルです。使用者はIOマルチフォームプログラミングの経験がある方が最善です。
## 事件優先级

1. `Process::signal`で設定された信号処理のカーボンディフレクター関数
2. `Timer::tick`と`Timer::after`で設定されたタイムアウト関数
3. `Event::defer`で設定された遅延実行関数
4. `Event::cycle`で設定された周期関数
## メソッド
### add()

`socket`を底層の`reactor`イベントリスナーに追加します。この関数は`Server`または`Client`モードで使用できます。
```php
Swoole\Event::add(mixed $sock, callable $read_callback, callable $write_callback = null, int $flags = null): bool
```

!> `Server`プログラムで使用する場合は、`Worker`プロセスが起動した後に使用しなければなりません。`Server::start`の前には、どんな異步`IO`インターフェースも呼び出してはいけません。

* **パラメータ**

  * **`mixed $sock`**
    * **機能**：ファイルディスクリプト、`stream`リソース、`sockets`リソース、`object`
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`callable $read_callback`**
    * **機能**：可読イベントのカーボンディフレクター関数
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`callable $write_callback`**
    * **機能**：書き込みイベントのカーボンディフレクター関数【このパラメータは、文字列関数名、オブジェクト+方法、クラスの静的メソッド、または匿名関数であり、この`socket`が可読または書き込み可能な場合に指定された関数を呼び出します。】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $flags`**
    * **機能**：イベントタイプのマスク【可読/書き込みイベントをオフ/オンにすることができます。例えば`SWOOLE_EVENT_READ`、`SWOOLE_EVENT_WRITE`、または`SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE`です。】
    * **デフォルト値**：なし
    * **その他の値**：なし

* **$sock 4種類**
タイプ | 説明
---|---
int | ファイルディスクリプト、`Swoole\Client->$sock`、`Swoole\Process->$pipe`、またはその他の`fd`
streamリソース | `stream_socket_client`/`fsockopen`で作成されたリソース
socketsリソース | `sockets`拡張で`socket_create`で作成されたリソース、编译時に[./configure --enable-sockets](/environment?id=编译选项)を加える必要があります
object | `Swoole\Process`または`Swoole\Client`、底層が自動的に[UnixSocket](/learn?id=何がIPC)（`Process`）に変換されたり、クライアント接続の`socket`（`Swoole\Client`）に変換されたりします。

* **戻り値**

  *イベントリスナーの追加に成功した場合は`true`を戻ります
  *追加に失敗した場合は`false`を戻ります。エラーコードを取得するには`swoole_last_error`を使用してください
  *既に追加された`socket`は再び追加することはできません。`swoole_event_set`を使用して`socket`に対応するカーボンディフレクターとイベントタイプを変更することができます

  !> `Swoole\Event::add`を使用して`socket`をイベントリスナーに追加した後、底層は自動的にその`socket`を非ブロッキングモードに設定します。

* **使用例**

```php
$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Swoole\Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    //socket処理完了後、epollイベントからsocketを削除
    Swoole\Event::del($fp);
    fclose($fp);
});
echo "Finish\n";  //Swoole\Event::addはプロセスをブロッキングしないため、この行は順序良く実行されます
```

* **カーボンディフレクター**

  *可読`($read_callback)`イベントのカーボンディフレクターの中で、`fread`、`recv`などの関数を使用して`socket`のキャッシュからデータを読み取る必要があります。そうでなければ、イベントは継続してトリガーされ、読み続けたくない場合は`Swoole\Event::del`を使用してイベントリスナーを削除する必要があります。
  *書き込み`($write_callback)`イベントのカーボンディフレクターの中で、`socket`にデータを書き込んだ後は、`Swoole\Event::del`を使用してイベントリスナーを削除する必要があります。そうでなければ、書き込みイベントは継続してトリガーされます。
  * `fread`、`socekt_recv`、`socket_read`、`Swoole\Client::recv`が`false`を戻り、エラーコードが`EAGAIN`である場合、現在の`socket`の受信キャッシュにはデータがないことを意味します。この場合は、イベントリスナーに追加して待つ必要があります[EventLoop](/learn?id=何がeventloop)の通知を受けます。
  * `fwrite`、`socket_write`、`socket_send`、`Swoole\Client::send`が`false`を戻り、エラーコードが`EAGAIN`である場合、現在の`socket`の送信キャッシュが満杯で、一時的にデータを送信できないことを意味します。書き込みイベントに待つ必要があります[EventLoop](/learn?id=何がeventloop)の通知を受けます。
### set()

イベントリスナーのカーボンディフレクターとマスクを設定します。

```php
Swoole\Event::set($fd, mixed $read_callback, mixed $write_callback, int $flags): bool
```

* **パラメータ** 

  * [Event::add](/event?id=add)と同じパラメータです。もし`$fd`が[EventLoop](/learn?id=何がeventloop)に存在しない場合は`false`となります。
  * `$read_callback`が`null`でない場合は、可読イベントのカーボンディフレクターを指定された関数に変更します
  * `$write_callback`が`null`でない場合は、可書きイベントのカーボンディフレクターを指定された関数に変更します
  * `$flags`はオン/オフでき、可書き（`SWOOLE_EVENT_READ`）と可読（`SWOOLE_EVENT_WRITE`）イベントのリスナーを開閉できます  

  !> 注意：`SWOOLE_EVENT_READ`イベントをリスナーに設定している場合、`read_callback`が設定されていない場合、底層は直接`false`を返し、追加に失敗します。`SWOOLE_EVENT_WRITE`も同様です。

* **状態変更**

  * `Event::add`または`Event::set`で可読イベントのカーボンディフレクターを設定しましたが、`SWOOLE_EVENT_READ`イベントをリスナーに設定していない場合、底層はカーボンディフレクターの情報だけを保存し、イベントカーボンディフレクターを発生させません。
  * `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`を使用して、イベントをリスナーに設定するイベントタイプを変更することができます。この場合、底層は可読イベントをトリガーします。

* **カーボンディフレクターの解放**

!> `Event::set`はカーボンディフレクターを置き換えることはできますが、イベントカーボンディフレクターを解放することはできません。例えば、`Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`というパラメータで、`read_callback`と`write_callback`が`null`になっている場合、`Event::add`で設定されたカーボンディフレクターを変更しないことを意味し、イベントカーボンディフレクターを`null`に設定するのではありません。

`Event::del`を呼び出してイベントリスナーをクリアする場合にのみ、底層は`read_callback`と`write_callback`のイベントカーボンディフレクターを解放します。
### isset()

入力された`$fd`がイベントリスナーに追加されているかどうかを検出します。

```php
Swoole\Event::isset(mixed $fd, int $events = SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE): bool
```

* **パラメータ** 

  * **`mixed $fd`**
    * **機能**：任意のsocketファイルディスクリプト【[Event::add](/event?id=add)文書を参照】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $events`**
    * **機能**：検出されるイベントタイプ
    * **デフォルト値**：なし
    * **その他の値**：なし

* **$events**
イベントタイプ | 説明
---|---
`SWOOLE_EVENT_READ` | 可読イベントをリスナーにしているかどうか
`SWOOLE_EVENT_WRITE` | 可書きイベントをリスナーにしているかどうか
`SWOOLE_EVENT_READ \| SWOOLE_EVENT_WRITE` | 可読または可書きイベントをリスナーにしているかどうか

* **使用例**

```php
use Swoole\Event;

$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    Swoole\Event::del($fp);
    fclose($fp);
}, null, SWOOLE_EVENT_READ);
var_dump(Event::isset($fp, SWOOLE_EVENT_READ)); //返回 true
var_dump(Event::isset($fp, SWOOLE_EVENT_WRITE)); //返回 false
var_dump(Event::isset($fp, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)); //返回 true
```
### write()

PHPの組み込み`stream/sockets`拡張で作成されたsocketに対して、`fwrite/socket_send`などの関数を使用してデータを送信します。送信するデータ量が大きく、socketの書き込みキャッシュが満杯の場合、ブロッキング待ちまたは[EAGAIN](/other/errno?id=linux)エラーを返すことがあります。

`Event::write`関数は、`stream/sockets`リソースのデータ送信を**非同期**に変えることができます。キャッシュが満杯の場合や[EAGAIN](/other/errno?id=linux)を返す場合、Swooleの底層はデータを送信キューに追加し、可書きイベントをリスナーにします。socketが可書きの場合、Swooleの底層は自動的に書き込みます。

```php
Swoole\Event::write(mixed $fd, miexd $data): bool
```

* **パラメータ** 

  * **`mixed $fd`**
    * **機能**：任意のsocketファイルディスクリプト【[Event::add](/event?id=add)文書を参照】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`miexd $data`**
    * **機能**：送信するデータ【送信するデータの長さは`Socket`キャッシュのサイズを超えてはならない】
    * **デフォルト値**：なし
    * **その他の値**：なし

!> `Event::write`は`SSL/TLS`などのタンクエンジニアリングされた`stream/sockets`リソースには使用できません  
`Event::write`操作が成功した後、この`$socket`は自動的に非ブロッキングモードに設定されます。

* **使用例**

```php
use Swoole\Event;

$fp = stream_socket_client('tcp://127.0.0.1:9501');
$data = str_repeat('A', 1024 * 1024*2);

Event::add($fp, function($fp) {
     echo fread($fp);
});

Event::write($fp, $data);
```

#### SOCKETキャッシュが満杯になった後、Swooleの底層ロジック

持続的に`SOCKET`に書き込みを行っても、相手方が読み取りが速すぎない場合、`SOCKET`のキャッシュは溢れます。Swooleの底層はデータをメモリキャッシュに保存し、可書きイベントがトリガーされるまで`SOCKET`に書き込みません。

メモリキャッシュも満杯になった場合、Swooleの底層は`pipe buffer overflow, reactor will block.`エラーを抛出し、ブロッキング待ちに入ります。

!> キャッシュが満杯になったことは原子操作であり、全ての書き込みが成功するか、全てが失敗するかのどちらかです。
### del()

`reactor`からイベントを聞いている
