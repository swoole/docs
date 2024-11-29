# 協程Redisクライアント

!> このクライアントはもう推奨されていません。`Swoole\Runtime::enableCoroutine + phpredis` や `predis` の方法を推奨します。つまり[ワンボタン協程化](/runtime)でネイティブな`PHP`の`redis`クライアントを使用します。

!> `Swoole 6.0` 以降、この協程Redisクライアントは削除されました。


## 使用例

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});
```

!> `subscribe` `pSubscribe`は`defer(true)`の場合には使用できません。


## メソッド

!> メソッドの使用は基本的に [phpredis](https://github.com/phpredis/phpredis) と同じです。

以下は[phpredis](https://github.com/phpredis/phpredis)と異なる実装です：

1. まだ実装されていないRedisコマンド：`scan object sort migrate hscan sscan zscan`；

2. `subscribe pSubscribe`の使用方法は、コールバック関数を設定する必要がありません；

3. PHP変数のシリアライズのサポートは、`connect()`メソッドの第三引数を`true`に設定すると、シリアライズ`PHP`変数の特性が有効になります。デフォルトは`false`です。


### __construct()

Redis協程クライアントのコンストラクタで、`Redis`接続の設定オプションを設定できます。`setOptions()`メソッドのパラメータと同じです。

```php
Swoole\Coroutine\Redis::__construct(array $options = null);
```


### setOptions()

4.2.10バージョン以降に追加されたこのメソッドは、構築後や接続後に`Redis`クライアントの設定を行うために使用されます。

この関数はSwooleスタイルで、`Key-Value`キー値対配列を通じて設定する必要があります。

```php
Swoole\Coroutine\Redis->setOptions(array $options): void
```

  * **設定可能なオプション**


key | 説明
---|---
`connect_timeout` | 接続のタイムアウト時間。デフォルトはグローバルな協程`socket_connect_timeout`(1秒)
`timeout` | タイムアウト時間。デフォルトはグローバルな協程`socket_timeout`。参照[クライアントタイムアウト規則](/coroutine_client/init?id=タイムアウト規則)
`serialize` | 自動シリアライズ。デフォルトは無効
`reconnect` | 自動再接続試行回数。接続がタイムアウトなどの理由で`close`で正常に切断された場合、次のリクエスト時に自動的に再接続を試みてからリクエストを送信します。デフォルトは`1`回(`true`)です。一度失敗した指定回数後は再試行せず、手動で再接続する必要があります。このメカニズムは接続の保持のためのものであり、リクエストを再送信してイディオムに反するインターフェースのエラーなどの問題は発生しません。
`compatibility_mode` | `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore` 関数の戻り値が`php-redis`と一致しない互換性の解決策です。有効にすると `Co\Redis` と `php-redis` の戻り値が一致します。デフォルトは無効です。【この設定項目は`v4.4.0`またはそれ以上のバージョンで使用可能】


### set()

データを保存します。

```php
Swoole\Coroutine\Redis->set(string $key, mixed $value, array|int $option): bool
```

  * **パラメータ** 

    * **`string $key`**
      * **機能**：データのキー
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $value`**
      * **機能**：データ内容【文字列以外のタイプは自動でシリアライズされます】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $options`**
      * **機能**：オプション
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> `$option` 説明：  
      `整数型`：有効期限を設定します。例えば`3600`  
      `配列`：高度な有効期限設定。例えば`['nx', 'ex' => 10]` 、`['xx', 'px' => 1000]`

      !> `px`: ミリ秒単位の有効期限を表します  
      `ex`: 秒単位の有効期限を表します  
      `nx`: 存在しない時に設定します  
      `xx`: 存在する時に設定します


### request()

Redisサーバーにカスタムの指令を送信します。phpredisのrawCommandに似ています。

```php
Swoole\Coroutine\Redis->request(array $args): void
```

  * **パラメータ** 

    * **`array $args`**
      * **機能**：パラメータリストで、必ず配列形式でなければなりません。【最初の要素は`Redis`指令で、他の要素は指令のパラメータです。下層では自動的に`Redis`プロトコルリクエストとしてパックされて送信されます。】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値** 

Redisサーバーが指令を処理する方法によって異なり、数字、ブール値、文字列、配列などの型を返す可能性があります。

  * **使用例** 

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379); // ローカルUNIXSocketの場合はhostパラメータを`unix://tmp/your_file.sock`の形式で記入する必要があります
    $res = $redis->request(['object', 'encoding', 'key1']);
    var_dump($res);
});
```


## プロパティ


### errCode

エラーコード。


エラーコード | 説明
---|---
1 | 読み取りまたは書き込みのエラー
2 | その他...
3 | ファイル終了
4 | プロトコルエラー
5 | メモリ不足


### errMsg

エラーメッセージ。


### connected

現在の`Redis`クライアントがサーバーに接続しているかどうかを判断します。


## 定数

`multi($mode)`メソッドで使用されるためのもので、デフォルトは`SWOOLE_REDIS_MODE_MULTI`モードです：

* SWOOLE_REDIS_MODE_MULTI
* SWOOLE_REDIS_MODE_PIPELINE

`type()`コマンドの戻り値を判断するためのものです：

* SWOOLE_REDIS_TYPE_NOT_FOUND
* SWOOLE_REDIS_TYPE_STRING
* SWOOLE_REDIS_TYPE_SET
* SWOOLE_REDIS_TYPE_LIST
* SWOOLE_REDIS_TYPE_ZSET
* SWOOLE_REDIS_TYPE_HASH


## トランザクションモード

`multi`と`exec`を使用して`Redis`のトランザクションモードを実現できます。

  * **ヒント**

    * `mutli`指令を使用してトランザクションを開始し、その後のすべての指令が実行を待つキューに追加されます
    * `exec`指令を使用してトランザクション内のすべての操作を実行し、結果を一度に返します

  * **使用例**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key3', 'rango');
    $redis->get('key1');
    $redis->get('key2');
    $redis->get('key3');

    $result = $redis->exec();
    var_dump($result);
});
```


## サブスクリプションモード

!> Swooleバージョン >= v4.2.13 で使用可能です。**4.2.12およびそれ以前のバージョンではサブスクリプションモードにBUGがあります**


### サブスクリプション

`phpredis`とは異なり、`subscribe/psubscribe`は協程スタイルです。

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // またはpsubscribeを使用
    {
        while ($msg = $redis->recv()) {
            // msgは配列で、以下の情報を含みます
            // $type # 戻り値のタイプ：サブスクリプション成功を表示
            // $name # サブスクリプションしたチャンネル名またはソースチャンネル名
            // $info  # 現在サブスクリプションしているチャンネル数または情報内容
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') { // またはpsubscribe
                // チャンネルのサブスクリプション成功メッセージ。サブスクリプションしたチャンネル数だけメッセージがあります
            } else if ($type == 'unsubscribe' && $info == 0){ // またはpunsubscribe
                break; // サブスクリプション解除メッセージを受信し、残りのサブスクリプションチャンネル数が0になったため、これ以上受信せず、ループを終了します
            } else if ($type == 'message') {  // psubscribedの場合はここがpmessage
                var_dump($name); // ソースチャンネル名を出力
                var_dump($info); // メッセージを出力
                // balabalaba.... // メッセージを処理
                if ($need_unsubscribe) { // ある条件下で退订が必要
                    $redis->unsubscribe(); // 退订を続けてrecvを待ちます
                }
            }
        }
    }
});
```


### サブスクリプション解除

サブスクリプション解除には`unsubscribe/punsubscribe`を使用し、`$redis->unsubscribe(['channel1'])`

この時、`$redis->recv()`はサブスクリプション解除メッセージを受信します。複数のチャンネルを解除する場合は、複数のメッセージを受信します。
    
!> 注意：サブスクリプション解除後は必ず`recv()`を続けて最後のサブスクリプション解除メッセージ（`$msg[2] == 0`）を受信してください。このメッセージを受信した後に、サブスクリプションモードから退出します。

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // or use psubscribe
    {
        while ($msg = $redis->recv()) {
            // msgは配列で、以下の情報を含んでいます
            // $type # 戻り値のタイプ：サブスクリプション成功を示します
            // $name # サブスクリプションしたチャネル名またはソースチャネル名
            // $info  # 現在サブスクリプションしているチャネル数または情報内容
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') // psubscribeの場合はここがpsubscribe
            {
                // チャネルのサブスクリプション成功メッセージ
            }
            else if ($type == 'unsubscribe' && $info == 0) // punsubscribeの場合はここがpunsubscribe
            {
                break; // サブスクリプション解除メッセージを受信し、サブスクリプションしているチャネル数が0になったので、これ以上受信せず、ループを終了します
            }
            else if ($type == 'message') // psubscribeの場合はここがpmessage
            {
                // ソースチャネル名を出力
                var_dump($name);
                // メッセージを出力
                var_dump($info);
                // メッセージを処理
                if ($need_unsubscribe) // ある状況でサブスクリプションを解除する必要がある場合
                {
                    $redis->unsubscribe(); // サブスクリプション解除を続けてrecvを待ちます
                }
            }
        }
    }
});
```

## 互換モード

`Co\Redis` の `hmGet/hGetAll/zrange/zrevrange/zrangebyscore/zrevrangebyscore`指令の戻り値が`phpredis`拡張の戻り値の形式と一致しない問題は、[#2529](https://github.com/swoole/swoole-src/pull/2529)で解決されました。

古いバージョンとの互換性のために、`$redis->setOptions(['compatibility_mode' => true]);` 設定を加えると、`Co\Redis` と `phpredis` の戻り値が一致するようになります。

!> Swooleバージョン >= `v4.4.0` で使用可能

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->setOptions(['compatibility_mode' => true]);
    $redis->connect('127.0.0.1', 6379);

    $co_get_val = $redis->get('novalue');
    $co_zrank_val = $redis->zRank('novalue', 1);
    $co_hgetall_val = $redis->hGetAll('hkey');
    $co_hmget_val = $redis->hmGet('hkey', array(3, 5));
    $co_zrange_val = $redis->zRange('zkey', 0, 99, true);
    $co_zrevrange_val = $redis->zRevRange('zkey', 0, 99, true);
    $co_zrangebyscore_val = $redis->zRangeByScore('zkey', 0, 99, ['withscores' => true]);
    $co_zrevrangebyscore_val = $redis->zRevRangeByScore('zkey', 99
