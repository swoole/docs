# データ型
ここでは、スレッド間で伝達し共有できるデータ型がリストアップされています。

## 基本型
`null/bool/int/float` 型の変数は、メモリサイズが `16 Bytes` 未満で、値として伝達されます。

## 文字列
文字列は**メモリコピー**を行い、`ArrayList`、`Queue`、`Map`に保存されます。

## Socketリソース

###サポートされるタイプリスト

- `Co\Socket`

- `PHP Stream`
- `PHP Socket(ext-sockets)`は、`--enable-sockets`コンパイルオプションを有効にする必要があります

###サポートされないタイプ

- `Swoole\Client`

- `Swoole\Server`

- `Swoole\Coroutine\Client`

- `Swoole\Coroutine\Http\Client`

- `pdo` 接続

- `redis` 接続
- その他の特殊な `Socket`リソースタイプ

###リソースの複製

- 書き込み時には `dup(fd)` 操作を行い、元のリソースから分離し、互いに影響を与えません。元のリソースを `close` 操作しても、新しいリソースには影響しません

- 読み取り時には `dup(fd)` 操作を行い、読み取りするサブスレッドの `VM` 内で新しい `Socket`リソースを構築します
- 削除時には `close(fd)` 操作を行い、ファイルハンドラを解放します

これは `Socket`リソースが `3` つの参照カウントを持つことを意味します。それぞれは以下の通りです：

- `Socket`リソースが最初に作成されたスレッド

- `ArrayList`、`Queue`、`Map`コンテナ
- `ArrayList`、`Queue`、`Map`コンテナを読取するサブスレッド

このリソースがどのスレッドやコンテナにも保持されていない場合、参照カウントが `0` になると、`Socket`リソースは実際に解放されます。参照カウントが `0` 以外であれば、たとえ `close` 操作被执行しても、接続は閉じられず、他のスレッドやデータコンテナが保持する `Socket`リソースに影響を与えません。

参照カウントを無視して直接 `Socket`を閉じたい場合は、`shutdown()` 方法を使用できます。例えば：

- `stream_socket_shutdown()`

- `Socket::shutdown()`

- `socket_shutdown()`

> `shutdown` 操作は、すべてのスレッドが保持する `Socket`リソースに影響を与え、実行後は使用できなくなり、`read/write` 操作はできません

## 配列
`array_is_list()` を使用して配列のタイプを判断します。数字索引の配列は `ArrayList`に、連想索引の配列は `Map`に変換されます。

- 配列全体を traversalし、要素を `ArrayList`または`Map`に挿入します
- 多次元配列は、再帰的に traversalし、嵌套構造の `ArrayList`または`Map`に変換されます

例：
```php
$array = [
    'a' => random_int(1, 999999999999999999),
    'b' => random_bytes(128),
    'c' => uniqid(),
    'd' => time(),
    'e' => [
        'key' => 'value',
        'hello' => 'world',
    ];
];

$map = new Map($array);

// $map['e']は、keyとhelloの2つの要素を持つ新しいMapオブジェクトで、値はそれぞれ'value'と'world'です
var_dump($map['e']);
```

## オブジェクト

### スレッドリソースオブジェクト

`Thread\Lock`、`Thread\Atomic`、`Thread\ArrayList`、`Thread\Map`などのスレッドリソースオブジェクトは、直接 `ArrayList`、`Queue`、`Map`に保存できます。
この操作は、オブジェクトの参照をコンテナに保存することだけであり、オブジェクトのコピーは行いません。

オブジェクトを `ArrayList`または`Map`に書き込みます。これは、スレッドリソースの参照カウントを1増やすだけであり、コピーは行いません。オブジェクトの参照カウントが0になると、解放されます。

例：

```php
$map = new Thread\Map;
$lock = new Thread\Lock; //現在の参照カウントは1です
$map['lock'] = $lock; //現在の参照カウントは2です
unset($map['lock']); //現在の参照カウントは1です
unset($lock); //現在の参照カウントは0になり、Lockオブジェクトが解放されます
```

サポートされるリスト：

- `Thread\Lock`

- `Thread\Atomic`

- `Thread\Atomic\Long`

- `Thread\Barrier`

- `Thread\ArrayList`

- `Thread\Map`

- `Thread\Queue`

请注意`Thread`スレッドオブジェクトは、シリアライズも伝達もできません。親スレッドでのみ使用できます。

### 通常のPHPオブジェクト
書き込み時には自動的にシリアライズされ、読み取り時には逆シリアライズされます。オブジェクトがシリアライズ不可能なタイプを含んでいる場合、例外が投げられます。
