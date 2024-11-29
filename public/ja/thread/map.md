# 安全な並行コンテナ Map

スレッドに渡すために使用できる並行可能な `Map` 構造体を構築します。他のスレッドでの読み書きは可见です。

## 特性
- `Map`、`ArrayList`、`Queue` は自動的にメモリを割り当てるため、`Table`のように固定で割り当てる必要はありません。

- 内部的に自動的にロックがかかり、スレッドセーフです。

- 渡せる変数のタイプは [データタイプ](thread/transfer.md) を参照してください。

- イテレーターはサポートされていませんが、`keys()`、`values()`、`toArray()` を使って代替できます。

- `Map`、`ArrayList`、`Queue` 对象は、スレッドが生成される前にスレッド引数として子スレッドに渡さなければなりません。

- `Thread\Map`は `ArrayAccess` および `Countable` インターフェースを実現しており、直接配列として操作できます。

## 例
```php
use Swoole\Thread;
use Swoole\Thread\Map;

$args = Thread::getArguments();
if (empty($args)) {
    $map = new Map;
    $thread = new Thread(__FILE__, $i, $map);
    sleep(1);
    $map['test'] = unique();
    $thread->join();
} else {
    $map = $args[1];
    sleep(2);
    var_dump($map['test']);
}
```

- 追加または変更：`$map[$key] = $value`

- 削除：`unset($map[$key])`

- 読み取り：`$value = $map[$key]`
- 要素数を取得：`count($map)`

## 方法

### __construct()
安全な並行コンテナ `Map` のコンストラクタ

```php
Swoole\Thread\Map->__construct(?array $values = null)
```

- `$values`はオプションで、配列を走査して配列の値を`Map`に追加します。

### add()
`Map`にデータを書き込む

```php
Swoole\Thread\Map->add(mixed $key, mixed $value) : bool
```
  * **引数**
      * `mixed $key`
          * 機能：追加したいkey。
          * 默认値：なし。
          * その他：なし。
  
      * `mixed $value`
          * 機能：追加したい値。
          * 默认値：なし。
          * その他：なし。
  
  * **戻り値**
      * `$key`が既に存在する場合は`false`を返し、そうでなければ追加に成功したことを`true`で返します。


### update()
`Map`のデータを更新する

```php
Swoole\Thread\Map->update(mixed $key, mixed $value) : bool
```

  * **引数**
      * `mixed $key`
          * 機能：更新したいkey。
          * 默认値：なし。
          * その他：なし。
  
      * `mixed $value`
          * 機能：更新したい値。
          * 默认値：なし。
          * その他：なし。
  
  * **戻り値**
      * `$key`が存在しない場合は`false`を返し、そうでなければ更新に成功したことを`true`で返します。


### incr()
`Map`のデータを安全に増やす

```php
Swoole\Thread\Map->incr(mixed $key, mixed $value = 1) : int | float
```
* **引数**
    * `mixed $key`
        * 機能：増やすべきkey。存在しなければ自動的に作成され、初期値は`0`になります。
        * 默认値：なし。
        * その他：なし。

    * `mixed $value`
        * 機能：増やすべき値。
        * 默认値：1。
        * その他：なし。

* **戻り値**
    * 増えた後の値を返します。


### decr()
`Map`のデータを安全に減らす

```php
Swoole\Thread\Map->decr(mixed $key, mixed $value = 1) : int | float
```
* **引数**
    * `mixed $key`
        * 機能：減らすべきkey。存在しなければ自動的に作成され、初期値は`0`になります。
        * 默认値：なし。
        * その他：なし。

    * `mixed $value`
        * 機能：減らすべき値。
        * 默认値：1。
        * その他：なし。

* **戻り値**
    * 減らした後の値を返します。


### count()
要素の数を取得する

```php
Swoole\Thread\Map()->count(): int
```

  * **戻り値**
      * `Map`の要素数を返します。


### keys()
すべての `key` 返回する

```php
Swoole\Thread\Map()->keys(): array
```

  * **戻り値**
    * `Map`のすべての `key`を返します。


### values()
すべての `value` 返回する

```php
Swoole\Thread\Map()->values(): array
```

* **戻り値**
    * `Map`のすべての `value`を返します。


### toArray()
`Map`を配列に変換する

```php
Swoole\Thread\Map()->toArray(): array
```

### clean()
すべての要素をクリアする

```php
Swoole\Thread\Map()->clean(): void
```
