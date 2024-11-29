# 安全な並行コンテナ List

スレッドパラメータとして渡すことができる並行可能な `List` 構造体を構築します。読み書きは他のスレッドで可见です。

## 特性
- `Map`、`ArrayList`、`Queue` は自動的にメモリを割り当てるため、`Table`のように固定で割り当てる必要はありません。

- 下層では自動的にロックがかけられ、スレッドセーフです。

- 渡すことができる変数のタイプは [データタイプ](thread/transfer.md) を参照してください。

- イテレーターはサポートされていませんが、`toArray()` を使用して代替できます。

- `Map`、`ArrayList`、`Queue` 对象はスレッドを作成する前にスレッドパラメータとして子スレッドに渡す必要があります。

- `Thread\ArrayList`は `ArrayAccess` および `Countable` インターフェースを実現しており、直接配列として操作できます。

- `Thread\ArrayList`は数字索引でのみ操作でき、非数字は強制変換されます。

## 例
```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new ArrayList;
    $thread = new Thread(__FILE__, $i, $list);
    sleep(1);
    $list[] = unique();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```

- 追加または変更：`$list[$index] = $value`

- 削除：`unset($list[$index])`

- 読み取り：`$value = $list[$index]`
- 長さ取得：`count($list)`

## 削除
削除操作は `List`の批量前進操作を引き起こします。例えば、`List`に`1000`個の要素がある場合、`unset($list[4])`を行うと、`$list[5:999]`の批量移行操作が必要になり、`$list[4]`の削除による空位を埋める必要があります。しかし、要素は深く複製されず、指針のみが移動します。

> `List`が大きいために、前方の要素を削除すると、多くの`CPU`リソースを消費する可能性があります。

## 方法

### __construct()
安全な並行コンテナ `ArrayList` 構築関数

```php
Swoole\Thread\ArrayList->__construct(?array $values = null)
```

- `$values`はオプションで、配列を走査して配列の値を `ArrayList`に追加します。

- 連想配列は受け付けられません。そうでなければ例外が投げられます。
- 連想配列は `array_values`を使って `list`タイプの配列に変換する必要があります。

### incr()
`ArrayList`内のデータを安全に自増させます。浮点数または整数をサポートし、他のタイプに対して自増操作を行うと、自動的に整数に変わり、初期値は`0`で自増操作が行われます。

```php
Swoole\Thread\ArrayList->incr(int $index, mixed $value = 1) : int | float
```

* **パラメータ**
    * `int $index`
        * 機能：索引番号で、有効な索引アドレスでなければ例外が投げられます。
        * 默认値：なし。
        * その他：なし。

    * `mixed $value`
        * 機能：自増する値です。
        * 默认値：1。
        * その他：なし。

* **戻り値**
    * 自増後の値を返します。

### decr()
`ArrayList`内のデータを安全に自減させます。浮点数または整数をサポートし、他のタイプに対して自減操作を行うと、自動的に整数に変わり、初期値は`0`で自減操作が行われます。

```php
Swoole\Thread\ArrayList->(int $index, $value = 1) : int | float
```

* **パラメータ**
    * `int $index`
        * 機能：索引番号で、有効な索引アドレスでなければ例外が投げられます。
        * 默认値：なし。
        * その他：なし。

    * `mixed $value`
        * 機能：自減する値です。
        * 默认値：1。
        * その他：なし。

* **戻り値**
    * 自減後の値を返します。

### count()
`ArrayList`の要素数を取得します。

```php
Swoole\Thread\ArrayList()->count(): int
```

* **戻り値**
    * Listの要素数を返します。

### toArray()
`ArrayList`を配列に変換します。

```php
Swoole\Thread\ArrayList()->toArray(): array
```

* **戻り値**
    * `ArrayList`内のすべての要素を返すタイプ配列です。

### clean()
すべての要素をクリアします。

```php
Swoole\Thread\ArrayList()->clean(): void
```
