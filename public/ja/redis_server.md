# Redis\Server

`Redis`サーバプロトコルに準拠した`Server`クラスであり、このクラスに基づいて`Redis`プロトコルのサーバプログラムを実現することができます。

> `Swoole\Redis\Server`は[Server](/server/tcp_init)から派生しており、Serverが提供するすべてのAPIと設定項目を使用できます。プロセスモデルも同じです。[Server](/server/init)の章を参照してください。

* **利用可能なクライアント**

  * 任意のプログラミング言語の`redis`クライアント、PHPの`redis`拡張と`phpredis`ライブラリを含む
  * [Swoole\Coroutine\Redis](/coroutine_client/redis)キューケースクライアント
  * `Redis`が提供するコマンドラインツール、`redis-cli`、`redis-benchmark`を含む
## メソッド

`Swoole\Redis\Server`は`Swoole\Server`から派生しており、親クラスが提供するすべてのメソッドを使用できます。
### setHandler

> **`Redis`コマンド文字のハンドラを設定します。**

> `Redis\Server`は[onReceive](/server/events?id=onreceive)のカーボンディングハンドラを設定する必要はありません。対応するコマンドの処理関数を`setHandler`メソッドで設定し、サポートされていないコマンドを受け取った後、自動的にクライアントに`ERROR`応答を送信します。メッセージは`ERR unknown command '$command'`です。

```php
Swoole\Redis\Server->setHandler(string $command, callable $callback);
```

* **パラメータ**

  * **`string $command`**
    * **機能**：コマンドの名前
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`callable $callback`**
    * **機能**：コマンドの処理関数【カーボンディングハンドラが字符串型を返す場合は自動的にクライアントに送信されます】
    * **デフォルト値**：なし
    * **その他の値**：なし

    > 返されるデータは`Redis`形式でなければならず、`format`の静的メソッドを使用してパッケージ化することができます。
### format

> **コマンド応答データをフォーマットします。**

```php
Swoole\Redis\Server::format(int $type, mixed $value = null);
```

* **パラメータ**

  * **`int $type`**
    * **機能**：データのタイプ、関連する常量は以下の[フォーマットパラメータ常量](/redis_server?id=格式参数常量)を参照してください。
    * **デフォルト値**：なし
    * **その他の値**：なし
    
    > `$type`が`NIL`タイプの場合は、`$value`を渡す必要はありません。`ERROR`と`STATUS`タイプの`$value`はオプションです。`INT`、`STRING`、`SET`、`MAP`は必須です。

  * **`mixed $value`**
    * **機能**：値
    * **デフォルト値**：なし
    * **その他の値**：なし
### send

> **[Swoole\Server](/server/methods?id=send)の`send()`メソッドを使用してデータをクライアントに送信します。**

```php
Swoole\Server->send(int $fd, string $data): bool
```
## 常量
### 格式パラメータ常量

`format`関数で`Redis`応答データをパッケージ化するために主に使用されます
常量 | 説明
---|---
Server::NIL | nilデータを返す
Server::ERROR | エラーコードを返す
Server::STATUS | ステータスを返す
Server::INT | 整数を返し、formatは必ずパラメータ値を渡さなければならず、タイプは整数でなければなりません
Server::STRING | 字符串を返し、formatは必ずパラメータ値を渡さなければならず、タイプは字符串でなければなりません
Server::SET | リストを返し、formatは必ずパラメータ値を渡さなければならず、タイプは数组でなければなりません
Server::MAP | Mapを返し、formatは必ずパラメータ値を渡さなければならず、タイプは関連付け索引数组でなければなりません
## 使用例
### サーバ側

```php
use Swoole\Redis\Server;

define('DB_FILE', __DIR__ . '/db');

$server = new Server("127.0.0.1", 9501, SWOOLE_BASE);

if (is_file(DB_FILE)) {
    $server->data = unserialize(file_get_contents(DB_FILE));
} else {
    $server->data = array();
}

$server->setHandler('GET', function ($fd, $data) use ($server) {
    if (count($data) == 0) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'GET' command"));
    }

    $key = $data[0];
    if (empty($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    } else {
        return $server->send($fd, Server::format(Server::STRING, $server->data[$key]));
    }
});

$server->setHandler('SET', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'SET' command"));
    }

    $key = $data[0];
    $server->data[$key] = $data[1];
    return $server->send($fd, Server::format(Server::STATUS, "OK"));
});

$server->setHandler('sAdd', function ($fd, $data) use ($server) {
    if (count($data) < 2) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sAdd' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }

    $count = 0;
    for ($i = 1; $i < count($data); $i++) {
        $value = $data[$i];
        if (!isset($server->data[$key][$value])) {
            $server->data[$key][$value] = 1;
            $count++;
        }
    }

    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('sMembers', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'sMembers' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::SET, array_keys($server->data[$key])));
});

$server->setHandler('hSet', function ($fd, $data) use ($server) {
    if (count($data) < 3) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hSet' command"));
    }

    $key = $data[0];
    if (!isset($server->data[$key])) {
        $array[$key] = array();
    }
    $field = $data[1];
    $value = $data[2];
    $count = !isset($server->data[$key][$field]) ? 1 : 0;
    $server->data[$key][$field] = $value;
    return $server->send($fd, Server::format(Server::INT, $count));
});

$server->setHandler('hGetAll', function ($fd, $data) use ($server) {
    if (count($data) < 1) {
        return $server->send($fd, Server::format(Server::ERROR, "ERR wrong number of arguments for 'hGetAll' command"));
    }
    $key = $data[0];
    if (!isset($server->data[$key])) {
        return $server->send($fd, Server::format(Server::NIL));
    }
    return $server->send($fd, Server::format(Server::MAP, $server->data[$key]));
});

$server->on('WorkerStart', function ($server) {
    $server->tick(10000, function () use ($server) {
        file_put_contents(DB_FILE, serialize($server->data));
    });
});

$server->start();
```
### クライアント

```shell
$ redis-cli -h 127.0.0.1 -p 9501
127.0.0.1:9501> set name swoole
OK
127.0.0.1:9501> get name
"swoole"
127.0.0.1:9501> sadd swooler rango
(integer) 1
127.0.0.1:9501> sadd swooler twosee guoxinhua
(integer) 2
127.0.0.1:9501> smembers swooler
1) "rango"
2) "twosee"
3) "guoxinhua"
127.0.0.1:9501> hset website swoole "www.swoole.com"
(integer) 1
127.0.0.1:9501> hset website swoole "swoole.com"
(integer) 0
127.0.0.1:9501> hgetall website
1) "swoole"
2) "swoole.com"
127.0.0.1:9501> test
(error) ERR unknown command 'test'
127.0.0.1:9501>
```
