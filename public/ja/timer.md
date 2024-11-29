# 定時器 Timer

ミリ秒の精度を持つ定時器です。基盤は `epoll_wait` と `setitimer` を使用しており、データ構造は `最小ヘッド`を使用しています。多くの定時器を追加することができます。

* 同期IOプロセスで `setitimer` とシグナルを使用して実現され、例えば `Manager` と `TaskWorker` プロセス
* 异步IOプロセスで `epoll_wait` / `kevent` / `poll` / `select`のタイムアウトを実現
## 性能

基盤は最小ヘッドデータ構造を使用して定時器を実現しており、定時器の追加と削除はすべてメモリ操作であるため、性能は非常に高いです。

> 官方のベンチテストスクリプト [timer.php](https://github.com/swoole/benchmark/blob/master/timer.php)では、ランダムな時間の10万個の定時器を追加または削除するのに約 `0.08s`かかります。

```shell
~/workspace/swoole/benchmark$ php timer.php
add 100000 timer :0.091133117675781s
del 100000 timer :0.084658145904541s
```

!> 定時器はメモリ操作であり、IO消費はありません
## 比較

`Timer`は `PHP`自身の `pcntl_alarm` とは異なります。`pcntl_alarm`は `時計信号 + tick` 函数に基づいて実現されており、いくつかの欠点があります：

  * 最大で秒までサポートされていますが、`Timer`はミリ秒レベルまでサポートされています
  * 同時に複数の定時器プログラムを設定することはサポートされていません
  * `pcntl_alarm`は `declare(ticks = 1)`に依存しており、性能が悪いです
## 0ミリ秒の定時器

基盤は時間パラメータが `0`の定時器をサポートしていません。これは `Node.js`などの言語とは異なります。`Swoole`では [Swoole\Event::defer](/event?id=defer)を使用して同様の機能を実現できます。

```php
Swoole\Event::defer(function () {
  echo "hello\n";
});
```

!> 上記のコードは `JS`の `setTimeout(func, 0)`と同じ効果です。
## 別名

`tick()`、`after()`、`clear()`はすべて関数スタイルの別名を持っています
クラスの静的メソッド | 関数スタイルの別名
---|---
`Swoole\Timer::tick()` | `swoole_timer_tick()`
`Swoole\Timer::after()` | `swoole_timer_after()`
`Swoole\Timer::clear()` | `swoole_timer_clear()`
## メソッド
### tick()

間隔クロック定時器を設定します。

`after`定時器とは異なり、`tick`定時器は継続してトリガーされ、[Timer::clear](/timer?id=clear)を呼んでクリアするまで続きます。

```php
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int
```

!> 1. 定時器は現在のプロセススペース内で有効です  
   2. 定時器は純粋に非同期で実現されており、[同期IO](/learn?id=同步io异步io)の関数と一緒に使用することはできません。そうすると定時器の実行時間が混乱します  
   3. 定時器の実行中にはある程度の誤差が存在する可能性があります

  * **パラメータ**

    * **`int $msec`**
      * **機能**：時間を指定します
      * **値の単位**：ミリ秒【例えば`1000`は`1`秒を表し、`v4.2.10`以下のバージョンでは最大で`86400000`を超えてはなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`callable $callback_function`**
      * **機能**：時間が切れた後に実行される関数で、呼び出すことができるものでなければなりません
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`...$params`**
      * **機能**：実行される関数にデータを渡す【このパラメータもオプションです】
      * **デフォルト値**：なし
      * **その他の値**：なし
      
      !> 匿名関数の `use` 文法を使用して呼び出し関数にパラメータを渡すことができます

  * **$callback_function による呼び出し関数**

    ```php
    callbackFunction(int $timer_id, ...$params);
    ```

      * **`int $timer_id`**
        * **機能**：定時器の `ID`【[Timer::clear](/timer?id=clear)でこの定時器をクリアするために使用できます】
        * **デフォルト値**：なし
        * **その他の値**：なし

      * **`...$params`**
        * **機能**：`Timer::tick`によって渡された第三のパラメータ `$param`
        * **デフォルト値**：なし
        * **その他の値**：なし
      
      !> 匿名関数の `use` 文法を使用して呼び出し関数にパラメータを渡すことができます

  * **拡張**

    * **定時器の校正**

      定時器の呼び出し関数の実行時間は次の定時器の実行時間に影響しません。例えば、`0.002s`で`10ms`の`tick`定時器を設定し、最初は`0.012s`で呼び出し関数を実行しますが、呼び出し関数が`5ms`実行した場合、次の定時器は`0.022s`でトリガーされますが、実際には`0.027s`で定時器が戻ります。基盤は時間を校正し、過期の行動を捨て、次のタイミングで呼び出し関数を再びトリガーします。上記の例では、`0.012s`での呼び出し関数が`15ms`実行した場合、本来ならば`0.022s`で定時器がトリガーされるべきですが、実際には`0.027s`で定時器が戻りました。基盤は`0.032s`で再び定時器の呼び出しをトリガーします。
    
    * **キューモード**

      キュー環境下では `Timer::tick` の呼び出しコールバック内で自動的にキューが作成され、キュー関連の API を直接使用することができ、`go` を呼ぶことなくキューを作成する必要がありません。
      
      !> [enable_coroutine](/timer?id=close-timer-co)を設定することで、自動的にキューを作成することを無効にすることができます。

  * **使用例**

    ```php
    Swoole\Timer::tick(1000, function(){
        echo "timeout\n";
    });
    ```

    * **正しい例**

    ```php
    Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
        echo "timer_id #$timer_id, after 3000ms.\n";
        echo "param1 is $param1, param2 is $param2.\n";

        Swoole\Timer::tick(14000, function ($timer_id) {
            echo "timer_id #$timer_id, after 14000ms.\n";
        });
    }, "A", "B");
    ```

    * **誤った例**

    ```php
    Swoole\Timer::tick(3000, function () {
        echo "after 3000ms.\n";
        sleep(14);
        echo "after 14000ms.\n";
    });
    ```
### after()

指定された時間後に関数を実行します。`Swoole\Timer::after`関数は一回限りの定時器で、実行完了すると破棄されます。

この関数は `PHP`標準ライブラリが提供する `sleep`関数とは異なり、`after`は非ブロッキングです。一方で `sleep`の呼び出しは現在のプロセスをブロッキングにし、新しいリクエストを処理することができません。

```php
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int
```

  * **パラメータ** 

    * **`int $msec`**
      * **機能**：時間を指定します
      * **値の単位**：ミリ秒【例えば`1000`は`1`秒を表し、`v4.2.10`以下のバージョンでは最大で`86400000`を超えてはなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`callable $callback_function`**
      * **機能**：時間が切れた後に実行される関数で、呼び出すことができるものでなければなりません。
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`...$params`**
      * **機能**：実行される関数にデータを渡す【このパラメータもオプションです】
      * **デフォルト値**：なし
      * **その他の値**：なし
      
      !> 匿名関数の use 文法を使用して呼び出し関数にパラメータを渡すことができます

  * **戻り値**

    * 定時器が成功裏に実行された場合は定時器の `ID`を返し、定時器をキャンセルした場合は [Swoole\Timer::clear](/timer?id=clear)を呼ぶことができます。

  * **拡張**

    * **キューモード**

      キュー環境下では [Swoole\Timer::after](/timer?id=after) の呼び出しコールバック内で自動的にキューが作成され、キュー関連の API を直接使用することができ、`go` を呼ぶことなくキューを作成する必要がありません。
      
      !> [enable_coroutine](/timer?id=close-timer-co)を設定することで、自動的にキューを作成することを無効にすることができます。

  * **使用例**

```php
$str = "Swoole";
Swoole\Timer::after(1000, function() use ($str) {
    echo "Hello, $str\n";
});
```
### clear()

定時器の `ID`を使用して定時器を削除します。

```php
Swoole\Timer::clear(int $timer_id): bool
```

  * **パラメータ** 

    * **`int $timer_id`**
      * **機能**：定時器の `ID`【[Timer::tick](/timer?id=tick)、[Timer::after](/timer?id=after)を呼んだ後に整数の `ID`が返されます】
      * **デフォルト値**：なし
      * **その他の値**：なし

!> `Swoole\Timer::clear`は他のプロセスの定時器を削除するためのものではなく、現在のプロセスにのみ作用します。

  * **使用例**

```php
$timer = Swoole\Timer::after(1000, function () {
    echo "timeout\n";
});

var_dump(Swoole\Timer::clear($timer));
var_dump($timer);

// 出力：bool(true) int(1)
// 出力なし：timeout
```
### clearAll()

現在の Worker プロセス内のすべての定時器を削除します。

!> Swooleバージョン >= `v4.4.0` で利用可能

```php
Swoole\Timer::clearAll(): bool
```
### info()

`timer`の情報を返します。

!> Swooleバージョン >= `v4.4.0` で利用可能

```php
Swoole\Timer::info(int $timer_id): array
```

  * **戻り値**

```php
array(5) {
  ["exec_msec"]=>
  int(6000)
  ["exec_count"]=> // v4.8.0 添加
  int(5)
  ["interval"]=>
  int(1000)
  ["round"]=>
  int(0)
  ["removed"]=>
  bool(false)
}
```
### list()

定時器のイテレータを返し、`foreach`を使用して現在の Worker プロセス内のすべての `timer`の IDを列挙することができます。

!> Swooleバージョン >= `v4.4.0` で利用可能

```php
Swoole\Timer::list(): Swoole\Timer\Iterator
```

  * **使用例**

```php
foreach (Swoole\Timer::list() as $timer_id) {
    var_dump(Swoole\Timer::info($timer_id));
}
```
### stats()

定時器の状態を確認します。

!> Swooleバージョン >= `v4.4.0` で利用可能

```php
Swoole\Timer::stats(): array
```

  * **戻り値**

```php
array(3) {
  ["initialized"]=>
  bool(true)
  ["num"]=>
  int(1000)
  ["round"]=>
  int(1)
}
```
### set()

定時器関連のパラメータを設定します。

```php
Swoole\Timer::set(array $array): void
```

!> この方法は `v4.6.0`バージョンから廃止されました。
## 協程の閉鎖 :id=close-timer-co

デフォルトの定時器は、呼び出し関数を実行する際に自動的に協程を作成しますが、定時器の協程を個別に閉鎖することができます。

```php
swoole_async_set([
  'enable_coroutine' => false,
]);
```

