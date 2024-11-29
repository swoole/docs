# 安全な並行コンテナ Queue

スレッドに渡すために使用できる並行可能な `Queue` 構造体を構築します。読み書きは他のスレッドで可见です。

## 特性
- `Thread\Queue` は先进先出（FIFO）データ構造です。

- `Map`、`ArrayList`、`Queue` は自動的にメモリを割り当てられますので、`Table`のように固定して割り当てる必要はありません。

- 下層は自動的にロックをかけられており、スレッドセーフです。

- 渡すことができる変数のタイプについては [スレッドパラメータの伝達](thread/transfer.md)を参照してください。

- イテレーターはサポートされておらず、下層では `C++ std::queue` 使用されており、先进先出の操作のみがサポートされています。

- `Map`、`ArrayList`、`Queue` オブジェクトはスレッドを作成する前にスレッドパラメータとして子スレッドに渡す必要があります。

- `Thread\Queue` は要素を押し込むことと取り出すことしかできず、ランダムな要素アクセスはできません。

- `Thread\Queue`には内蔵されたスレッド的条件変数があり、`push/pop` 操作で他のスレッドを呼び覚まし、待つことができます。

## 例

```php
use Swoole\Thread;
use Swoole\Thread\Queue;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $queue = new Queue;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i, $queue);
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16)), Queue::NOTIFY_ONE);
        usleep(random_int(10000, 100000));
    }
    $n = 4;
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1];
    while (1) {
        $job = $queue->pop(-1);
        if (!$job) {
            break;
        }
        var_dump($job);
    }
}
```

## 定数



名前 | 効果
---|---
`Queue::NOTIFY_ONE` | 一つのスレッドを呼び覚ます
`Queue::NOTIFY_ALL` | 全てのスレッドを呼び覚ます


## 方法リスト


### __construct()
安全な並行コンテナ `Queue` のコンストラクタ

```php
Swoole\Thread\Queue->__construct()
```


### push()
キーの末尾にデータを書き込む

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

  * **パラメータ**
      * `mixed $value`
          * 機能：書き込むデータ内容。
          * 默认値：なし。
          * その他の値：なし。

      !> チャネルに `null`や`false`を書かないように注意してください。それらは曖昧さを招く可能性があります。
  
      * `int $notify`
          * 機能：読み取りを待つスレッドを呼び覚ますかどうか。
          * 默认値：`0`、 أيスレッドを呼び覚まさない。
          * その他の値：`Swoole\Thread\Queue::NOTIFY_ONE`で一つのスレッドを呼び覚まし、`Swoole\Thread\Queue::NOTIFY_ALL`で全てのスレッドを呼び覚ます。



### pop()
キーの先頭からデータを抽出する

```php
Swoole\Thread\Queue()->pop(float $timeout = 0): mixed
```

* **パラメータ**
    * `float $wait`
        * 機能：タイムアウト時間。
        * 默认値：`0`、つまり待ちません。
        * その他の値：もし`0`でなければ、キーの空の時に `$timeout` 秒間プロデューサーの `push()` 操作を待っていましたが、負の数は永遠にタイムアウトしません。

* **戻り値**
    * キーの先頭のデータを戻します。キーが空の場合、直接 `NULL` を戻します。

> `Queue::NOTIFY_ALL`で全てのスレッドを呼び覚ますとき、`push()` 操作で書き込まれたデータは一つのスレッドだけが取得できます。


### count()
キーの要素の数を取得する

```php
Swoole\Thread\Queue()->count(): int
```

* **戻り値**
    * キーの要素の数を戻します。

### clean()
全ての要素をクリアする

```php
Swoole\Thread\Queue()->clean(): void
```
