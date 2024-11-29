# コロニアルチャネル

> コロニーの基本的な概念を理解した後にこのセクションを読むことをお勧めします。[概要](/coroutine)をご覧ください。

チャネルは、コリン間の通信に使用され、複数のプロデューサーとコンシューマーのサポートが可能です。低層では自動的にコリンの切り替えとスケジュールが実現されています。

## 実装原理

  * チャネルはPHPの「Array」と似ており、メモリのみを占有し、他の追加リソースの申請はありません。すべての操作はメモリ操作であり、「IO」消費はありません。
  * 低層ではPHPの参照カウントを使用して実現されており、メモリコピーはありません。巨大な文字列や配列を渡しても追加のパフォーマンス消費は発生しません。
  * チャネルは参照カウントに基づいて実現されており、ゼロコピーです。

## 使用例

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;

run(function(){
    $channel = new Channel(1);
    Coroutine::create(function () use ($channel) {
        for($i = 0; $i < 10; $i++) {
            Coroutine::sleep(1.0);
            $channel->push(['rand' => rand(1000, 9999), 'index' => $i]);
            echo "{$i}\n";
        }
    });
    Coroutine::create(function () use ($channel) {
        while(1) {
            $data = $channel->pop(2.0);
            if ($data) {
                var_dump($data);
            } else {
                assert($channel->errCode === SWOOLE_CHANNEL_TIMEOUT);
                break;
            }
        }
    });
});
```

## 方法


### __construct()

チャネルの構築方法です。

```php
Swoole\Coroutine\Channel::__construct(int $capacity = 1)
```

  * **パラメータ** 

    * **`int $capacity`**
      * **機能**：容量を設定 【最小でも`1`の整数でなければなりません】
      * **デフォルト値**：`1`
      * **その他の値**：なし

!> 低層ではPHPの参照カウントを使用して変数を保存し、バッファは `$capacity * sizeof(zval)` byteのメモリのみを占有します。PHP7では`zval`が`16`byteですが、例えば`$capacity = 1024`の場合、チャネルは最大で`16K`のメモリを占有します。

!> `Server`での使用時には、必ず[onWorkerStart](/server/events?id=onworkerstart)の後に作成する必要があります。


### push()

チャネルにデータを書き出します。

```php
Swoole\Coroutine\Channel->push(mixed $data, float $timeout = -1): bool
```

  * **パラメータ** 

    * **`mixed $data`**
      * **機能**：データをpush 【PHP変数任意のタイプで、匿名関数やリソースを含むことができます】
      * **デフォルト値**：なし
      * **その他の値**：なし

      !>曖昧さを避けるために、チャネルに`null`や`false`を書き込むことはお勧めしません。

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定
      * **単位**：秒 【浮点数もサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：`-1` 【永遠にタイムアウトしないことを意味します】
      * **その他の値**：なし
      * **バージョンへの影響**：Swooleバージョン >= v4.2.12

      !>チャネルが満杯の場合、`push`は現在のコリンを挂起し、約定された時間内に他のコンシューマーがデータを消費しなければ、タイムアウトが発生し、低層で現在のコリンを復帰させ、`push`呼び出しは直ちに`false`を返し、書き込みに失敗します。

  * **戻り値**

    * 成功した場合は`true`を返します
    * チャネルが閉じられた場合、失敗して`false`を返し、`$channel->errCode`を使用してエラーコードを取得できます。

  * **拡張**

    * **チャネルが満杯**

      * 自動的に現在のコリンを`yield`し、他のコンシューマーがデータを読み取った後、チャネルは書き込むことができ、現在のコリンを再開します。
      * 複数のプロデューサーが同時に`push`すると、低層で自動的にキューイングが行われ、これらのプロデューサーは順番に一つずつ`resume`されます。

    * **チャネルが空**

      * 自動的に一つのコンシューマーを呼び覚まします。
      * 複数のコンシューマーが同時に`pop`すると、低層で自動的にキューイングが行われ、これらのコンシューマーは順番に一つずつ`resume`されます。

!> `Coroutine\Channel`はローカルメモリを使用しており、異なるプロセス間のメモリは隔離されています。同じプロセスの異なるコリンのみで`push`と`pop`操作を行うことができます。


### pop()

チャネルからデータを読み出します。

```php
Swoole\Coroutine\Channel->pop(float $timeout = -1): mixed
```

  * **パラメータ** 

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定
      * **単位**：秒 【浮点数もサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：`-1`【永遠にタイムアウトしないことを意味します】
      * **その他の値**：なし
      * **バージョンへの影響**：Swooleバージョン >= v4.0.3

  * **戻り値**

    * 任意のタイプのPHP変数、匿名関数、リソースを返すことができます
    * チャネルが閉じられた場合、失敗して`false`を返し、`$channel->errCode`を使用してエラーコードを取得できます。

  * **拡張**

    * **チャネルが満杯**

      * `pop`でデータを読み取った後、自動的に一つのプロデューサーを呼び覚まし、新しいデータを書き込むことができます。
      * 複数のプロデューサーが同時に`push`すると、低層で自動的にキューイングが行われ、これらのプロデューサーは順番に一つずつ`resume`されます。

    * **チャネルが空**

      * 自動的に現在のコリンを`yield`し、他のプロデューサーがデータを書き込む後、チャネルは読み取り可能になり、現在のコリンを再開します。
      * 複数のコンシューマーが同時に`pop`すると、低層で自動的にキューイングが行われ、これらのコンシューマーは順番に一つずつ`resume`されます。


### stats()

チャネルの状態を取得します。

```php
Swoole\Coroutine\Channel->stats(): array
```

  * **戻り値**

    返される配列には、バッファチャネルには`4`つの情報があり、バッファのないチャネルには`2`つの情報があります。
    
    - `consumer_num`コンシューマーの数、つまり現在チャネルが空であり、他のコリンが`push`メソッドを呼び出してデータを生産することを待っているコリンの数です。
    - `producer_num`プロデューサーの数、つまり現在チャネルが満杯であり、他のコリンが`pop`メソッドを呼び出してデータを消費することを待っているコリンの数です。
    - `queue_num`チャネル内の要素の数です。

```php
array(
  "consumer_num" => 0,
  "producer_num" => 1,
  "queue_num" => 10
);
```


### close()

チャネルを閉じます。そして、読み書きを待っているすべてのコリンを呼び覚まします。

```php
Swoole\Coroutine\Channel->close(): bool
```

!> すべてのプロデューサーコリンを呼び覚まし、`push`メソッドは`false`を返します。すべてのコンシューマーコリンを呼び覚まし、`pop`メソッドは`false`を返します。


### length()

チャネル内の要素の数を取得します。

```php
Swoole\Coroutine\Channel->length(): int
```


### isEmpty()

現在チャネルが空かどうかを判断します。

```php
Swoole\Coroutine\Channel->isEmpty(): bool
```


### isFull()

現在チャネルが満杯かどうかを判断します。

```php
Swoole\Coroutine\Channel->isFull(): bool
```


## プロパティ


### capacity

チャネルのバッファ容量です。

[構築方法](/coroutine/channel?id=__construct)で設定された容量がこの変数に保存されますが、**設定された容量が`1`未満の場合**、この変数は`1`になります。

```php
Swoole\Coroutine\Channel->capacity: int
```
### errcode

エラーコードを取得します。

```php
Swoole\Coroutine\Channel->errCode: int
```

  * **戻り値**


値 | 定義されている定数 | 効果
---|---|---

0 | SWOOLE_CHANNEL_OK |デフォルトで成功
-1 | SWOOLE_CHANNEL_TIMEOUT |タイムアウトしたポップに失敗した場合（タイムアウト）
-2 | SWOOLE_CHANNEL_CLOSED |チャネルが閉じており、チャネルにさらなる操作を続ける

