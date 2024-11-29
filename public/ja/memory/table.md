# 高性能共有メモリ テーブル

PHP言語はマルチスレッドをサポートしていないため、Swooleはマルチプロセスモードを使用しています。マルチプロセスモードではプロセス間のメモリ隔離が存在し、ワークプロセス内で`global`またはハイパーバイラル変数を変更しても、他のプロセスには無効です。

> `worker_num=1`を設定すると、プロセス隔離は存在せず、データはグローバル変数に保存できます。

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "connection open: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

$fdsはグローバル変数ですが、現在のプロセス内でのみ有効です。Swooleサーバーは底層で複数のWorkerプロセスを作成し、`var_dump($fds)`でプリントされた値は、接続されたfdの一部のみです。

対応する解決策は外部ストレージサービスを使用することです：

* 数据库、例えば：MySQL、MongoDB
* キャッシュサーバー、例えば：Redis、Memcache
* ディスクファイル、マルチプロセスが並行して読み書きする際はロックを必要とします

通常の数据库やディスクファイル操作では、多くのI/O待機時間が発生します。したがって、以下を推奨します：

* Redisメモリデータベース、読み書き速度は非常に速いですが、TCP接続などの問題があり、性能が最高ではありません。
* /dev/shm 内存ファイルシステム、読み書き操作はすべてメモリ内で完了し、I/O消費がなく、性能は非常に高いですが、データはフォーマット化されておらず、データ同期の問題があります。

?> 上記のストレージの使用に加えて、データ保存のために共有メモリを使用することを推奨します。Swoole\Tableは共有メモリとロックに基づいて実現された超高性能で、並行データ構造です。マルチプロセス/マルチスレッドでのデータ共有と同期ロック問題を解決するために使用されます。Tableのメモリ容量はPHPのmemory_limit制御を受けません。

!> Tableを配列形式で読み書きしないでください。必ずドキュメントに提供されているAPIを使用して操作してください。配列形式で取り出したTable\Rowオブジェクトは一回限りのオブジェクトであり、それに頼って多くの操作を行うことはできません。
v4.7.0バージョンから、Tableを配列形式で読み書きすることはサポートされなくなり、Table\Rowオブジェクトが削除されました。

* **利点**

  * 性能が非常に高く、単线程で毎秒200万件の読み書きが可能です。
  * アプリケーションコードにロックを加える必要がなく、Tableは内蔵の行ロックスピンロックを持ち、すべての操作はマルチプロセス/マルチスレッドで安全です。ユーザー層はデータ同期の問題を全く考慮する必要はありません。
  * マルチプロセスをサポートし、Tableはマルチプロセス間でデータを共有するために使用できます。
  * 行ロックを使用し、グローバルロックではありません。2つのプロセスが同じCPU時間で並行して同じデータを読み取り才会でロック争奪が発生します。

* **遍历**

!> 遍历中に削除操作を行わないでください（すべてのkeyを取り出した後に削除することができます）

TableクラスはイテレーターとCountableインターフェースを実現しており、foreachを使用して遍历し、countを使用して現在の行数を計算できます。

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```

## 属性


### size

テーブルの最大行数を取得します。

```php
Swoole\Table->size;
```


### memorySize

実際に使用しているメモリサイズを取得します。単位はバイトです。

```php
Swoole\Table->memorySize;
```


## 方法


### __construct()

メモリテーブルを作成します。

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **パラメータ** 

    * **`int $size`**
      * **機能**：テーブルの最大行数を指定します
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> Tableは底層で共有メモリに基づいて構築されているため、動的に拡張することはできません。そのため、$sizeは作成前に自分で計算して設定する必要があります。Tableが保存できる最大行数は$sizeと正の相関がありますが、完全には一致しません。例えば、$sizeが1024の場合、実際に保存できる行数は1024**未満**になります。$sizeが大きすぎると、マシンメモリが不足し、Tableの作成に失敗します。  

    * **`float $conflict_proportion`**
      * **機能**：ハッシュ衝突の最大比率を指定します
      * **デフォルト値**：`0.2` (つまり`20%`)
      * **その他の値**：最小は`0.2`、最大は`1`

  * **容量計算**

      * $sizeが2のN乗でなければ、例えば1024、8192、65536など、底層は自動的に近い数字に調整されます。1024未満の場合はデフォルトで1024になります。つまり1024が最小値です。v4.4.6バージョンから最小値は64に変更されました。
      * Tableが占有するメモリ総量は (HashTable構造体の長さ + KEYの長さ64バイト + $size値) * (1 + $conflict_proportion値をハッシュ衝突として使用) * (列のサイズ)です。
      * あなたのデータKeyとハッシュ衝突率が20%を超える場合、予約された衝突メモリブロックの容量が不足し、新しいデータをsetすると`Unable to allocate memory`エラーが発生し、falseを返して保存に失敗します。この場合は$size値を大きくしてサービスを再起動する必要があります。
      * メモリが十分にある場合は、できるだけこの値を大きく設定してください。


### column()

メモリテーブルに列を追加します。

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **パラメータ** 

    * **`string $name`**
      * **機能**：列の名前を指定します
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $type`**
      * **機能**：列のタイプを指定します
      * **デフォルト値**：なし
      * **その他の値**：`Table::TYPE_INT`, `Table::TYPE_FLOAT`, `Table::TYPE_STRING`

    * **`int $size`**
      * **機能**：文字列列の最大長さを指定します【文字列タイプの列は$sizeを指定しなければなりません】
      * **値の単位**：バイト
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **$type タイプの説明**


タイプ | 説明
---|---
Table::TYPE_INT | 默认で8バイトです
Table::TYPE_STRING | 設定すると、設定された文字列は$sizeで指定された最大長さを超えることはできません
Table::TYPE_FLOAT | 8バイトのメモリを占有します


### create()

メモリテーブルを作成します。表の構造を定義した後、createを実行してオペレーティングシステムにメモリを申請し、テーブルを作成します。

```php
Swoole\Table->create(): bool
```

create方法を使用してテーブルを作成した後、[memorySize](/memory/table?id=memorysize)属性を使用して実際に使用しているメモリサイズを取得できます

  * **ヒント** 

    * createを呼び出す前にset、getなどのデータ読み書き操作方法を使用することはできません
    * createを呼び出した後はcolumn方法を使用して新しい列を追加することはできません
    * システムメモリが不足し、申請に失敗した場合、createはfalseを返します
    * メモリ申請に成功した場合、createはtrueを返します

    !> Tableは共有メモリを使用してデータを保存するため、サブプロセスを作成する前に、必ずTable->create()を実行する必要があります。  
    ServerでTableを使用する場合、Server->start()の前にTable->create()を実行する必要があります。

  * **使用例**

```php
$table = new Swoole\Table(1024);
$table->column('id', Swoole\Table::TYPE_INT);
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->column('num', Swoole\Table::TYPE_FLOAT);
$table->create();

$worker = new Swoole\Process(function () {}, false, false);
$worker->start();

//$serv = new Swoole\Server('127.0.0.1', 9501);
//$serv->start();
```


### set()

行のデータを設定します。Tableはkey-value方式でデータをアクセスします。

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **パラメータ** 

    * **`string $key`**
      * **機能**：データのkey【もし$keyに対応する行が存在しない場合は、デフォルトの列の値は0です】
      * **デフォルト値**：なし
      * **その他の値**：なし

      !> 同じ$keyは同じ行のデータを対応しており、setと同じ$keyをすると、前回のデータを上書きします。$keyの最大長さは63バイトを超えてはなりません

    * **`array $value`**
      * **機能**：データのvalue【デフォルト値はなし】
      * **その他の値**：なし

      !> 配列でなければならず、字段定義の$nameと完全に同じでなければなりません

  * **戻り値**

    * 成功した場合はtrueを返します
    * 失敗した場合はfalseを返します。これはHash衝突が多すぎて動的スペースを割り当てることができないためかもしれません。createメソッドの2番目のパラメータを大きくすることができます。

!> -`Table->set()`はすべてのフィールドの値を設定することもできますし、一部のフィールドのみを修正することもできます。  
   -`Table->set()`は未設定の場合、その行のデータのすべてのフィールドは空です。  
   -set/get/delは内蔵の行ロックを持っているため、lockを呼び出してロックする必要はありません。  
   -**Keyは二进制安全ではなく、必ず文字列タイプでなければならず、二进制データは传入してはなりません。**
    
  * **使用例**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **最大長さを超えた文字列の設定**
    
    文字列の長さが列定義時に設定された最大サイズを超えた場合、底層は自動的に切り取ります。
    
    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * `str_value`列の最大サイズは5バイトですが、setで5バイトを超える文字列を設定しました
    * 底層は自動的に5バイトのデータを切り取り、最終的な`str_value`の値は`world`になります

!> v4.3バージョンから、底層はメモリ長さに对齐処理を行いました。文字列の長さは8の整数倍でなければならず、例えば長さが5の場合は自動的に8バイトに对齐されます。したがって、`str_value`の値は`world 12`になります


### incr()

原子的な自増操作です。

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **パラメータ** 

    * **`string $key`**
      * **機能**：データのkey【もし$keyに対応する行が存在しない場合は、デフォルトの列の値は0です】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $column`**
      * **機能**：列名を指定します【浮点型と整型フィールドのみサポートされています】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $incrby`**
      * **機能**：増量【整型フィールドの場合は$incrbyはint型でなければならず、浮点型フィールドの場合は$incrbyはfloat型でなければなりません】
      * **デフォルト値**：`1`
      * **その他の値**：なし

  * **戻り値**

    最終的な結果値を返します


### decr()

原子的な自減操作です。

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **パラメータ** 

    * **`string $key`**
      * **機能**：データのkey【もし$keyに対応する行が存在しない場合は、デフォルトの列の値は0です】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $column`**
      * **機能**：列名を指定します【浮点型と整型フィールドのみサポートされています】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $decrby`**
      * **機能**：増量【整型フィールドの場合は$decrbyはint型でなければならず、浮点型フィールドの場合は$decrbyはfloat型でなければなりません】
      * **デフォルト値**：`1`
      * **その他の値**：なし

  * **戻り値**

    最終的な結果値を返します

    !> 数値が0の場合、減少すると負数になります


### get()

一行のデータを取得します。

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **パラメータ** 

    * **`string $key`**
      * **機能**：データのkey【必ず文字列タイプでなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $field`**
      * **機能**：$fieldを指定した場合は、そのフィールドの値のみを返す而不是整个レコード
      * **デフォルト値**：なし
      * **その他の値**：なし
      
  * **戻り値**

    * `$key`が存在しない場合はfalseを返します
    * 成功した場合は結果配列を返します
    * `$field`を指定した場合は、そのフィールドの値のみを返す而不是整个レコード


### exist()

tableの中に特定のkeyが存在するかどうかを確認します。

```php
Swoole\Table->exist(string $key): bool
```

  * **パラメータ** 

    * **`string $key`**
      * **機能**：データのkey【必ず文字列タイプでなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし


### count()

tableに存在するエントリの数を返します。

```php
Swoole\Table->count(): int
```


### del()

データを削除します。

!> Keyは二进制安全ではなく、必ず文字列タイプでなければならず、二进制データは传入してはなりません。**遍历中には削除してはいけません**。

```php
Swoole\Table->del(string $key): bool
```

  * **戻り値**

    * `$key`に対応するデータが存在しない場合はfalseを返します
    * 成功した場合はtrueを返します


### stats()

Swoole\Tableの状態を取得します。

```php
Swoole\Table->stats(): array
```

!> Swooleバージョン >= `v4.8.0`で使用可能


## 助手関数 :id=swoole_table

ユーザーがSwoole\Tableを迅速に作成するのに便利です。

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Swooleバージョン >= `v4.6.0`で使用可能。$fieldsのフォーマットは`foo:i/foo:s:num/foo:f`です

| 短名 | 長名   |タイプ               |
| ---- | ------ | ------------------ |
| i    | int    | Table::TYPE_INT    |
| s    | string | Table::TYPE_STRING |
| f    | float  | Table::TYPE_FLOAT  |

例：

```php
$table = swoole_table(1024, 'fd:int, reactor_id:i, data:s:64');
var_dump($table);

$table = new Swoole\Table(1024, 0.25);

## 完全な例

```php
<?php
$table = new Swoole\Table(1024);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();

$serv = new Swoole\Server('127.0.0.1', 9501);
$serv->set(['dispatch_mode' => 1]);
$serv->table = $table;

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {

	$cmd = explode(" ", trim($data));

	//get
	if ($cmd[0] == 'get')
	{
		//get self
		if (count($cmd) < 2)
		{
			$cmd[1] = $fd;
		}
		$get_fd = intval($cmd[1]);
		$info = $serv->table->get($get_fd);
		$serv->send($fd, var_export($info, true)."\n");
	}
	//set
	elseif ($cmd[0] == 'set')
	{
		$ret = $serv->table->set($fd, array('reactor_id' => $data, 'fd' => $fd, 'data' => $cmd[1]));
		if ($ret === false)
		{
			$serv->send($fd, "ERROR\n");
		}
		else
		{
			$serv->send($fd, "OK\n");
		}
	}
	else
	{
		$serv->send($fd, "command error.\n");
	}
});

$serv->start();
```
