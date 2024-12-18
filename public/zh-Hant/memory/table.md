# 高性能共享内存 Table

由于 PHP 语言不支持多线程，因此 Swoole 使用多进程模式，在多进程模式下存在进程内存隔离，在工作进程内修改 global 全局变量和超全局变量时，在其他进程是无效的。

> 设置 worker_num=1 时，不存在进程隔离，可以使用全局变量保存数据

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "connection open: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

$fds 虽然是全局变量，但只在当前的进程内有效。Swoole 服务器底层会创建多个 Worker 进程，在 var_dump($fds) 打印出来的值，只有部分连接的 fd。

对应的解决方案就是使用外部存储服务：

* 数据库，如：MySQL、MongoDB
* 缓存服务器，如：Redis、Memcache
* 磁盘文件，多进程并发读写时需要加锁

普通的数据库和磁盘文件操作，存在较多 IO 等待时间。因此推荐使用：

* Redis 内存数据库，读写速度非常快，但是有 TCP 连接等问题，性能也不是最高的。
* /dev/shm 内存文件系统，读写操作全部在内存中完成，无 IO 消耗，性能极高，但是数据不是格式化的，还有数据同步的问题。

?> 除了上述使用存储之外，推荐使用共享内存来保存数据，Swoole\Table 是一个基于共享内存和锁实现的超高性能，并发数据结构。用于解决多进程/多线程数据共享和同步加锁问题。Table 的内存容量不受 PHP 的 memory_limit 控制

!> 不要使用数组方式读写 Table，一定要使用文档中提供的 API 来进行操作；  
数组方式取出的 Table\Row 对象为一次性对象，请勿依赖其进行过多操作。
从 v4.7.0 版本开始，不再支持以数组的方式读写 Table，并移除了 Table\Row 对象。

* **优势**

  * 性能强悍，单线程每秒可读写 200 万次；
  * 应用代码无需加锁，Table 内置行锁自旋锁，所有操作均是多线程/多进程安全。用户层完全不需要考虑数据同步问题；
  * 支持多进程，Table 可以用于多进程之间共享数据；
  * 使用行锁，而不是全局锁，仅当 2 个进程在同一 CPU 时间，并发读取同一条数据才会发生抢锁。

* **遍历**

!> 请勿在遍历期间进行删除操作（可将所有 key 取出后进行删除）

Table 类实现了迭代器和 Countable 接口，可以使用 foreach 进行遍历，使用 count 计算当前行数。

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```

## 属性


### size

获取表格的最大行数。

```php
Swoole\Table->size;
```


### memorySize

获取实际占用内存的尺寸，单位为字节。

```php
Swoole\Table->memorySize;
```


## 方法


### __construct()

创建内存表。

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **参数** 

    * **`int $size`**
      * **功能**：指定表格的最大行数
      * **默认值**：无
      * **其它值**：无

      !> 由于 Table 底层是建立在共享内存之上，所以无法动态扩容。所以 $size 必须在创建前自己计算设置好，Table 能存储的最大行数与 $size 正相关，但不完全一致，如 $size 为 1024 实际可存储的行数**小于** 1024，如果 $size 过大，机器内存不足 Table 会创建失败。  

    * **`float $conflict_proportion`**
      * **功能**：哈希冲突的最大比例
      * **默认值**：0.2 (即 20%)
      * **其它值**：最小为 0.2，最大为 1

  * **容量计算**

      * 如果 $size 不是为 2 的 N 次方，如 1024、8192、65536 等，底层会自动调整为接近的一个数字，如果小于 1024 则默认成 1024，即 1024 是最小值。从 v4.4.6 版本开始最小值为 64。
      * Table 占用的内存总数为 (HashTable 结构体长度 + KEY 长度 64 字节 + $size 值) * (1 + $conflict_proportion 值作为 hash 冲突) * (列尺寸)。
      * 如果你的数据 Key 和 Hash 冲突率超过 20%，预留的冲突内存块容量不足，set 新的数据就会报 Unable to allocate memory 错误，并返回 false，存储失败，此时需要调大 $size 值并重启服务。
      * 在内存足够的情况下尽量将此值设置的大一些。


### column()

内存表增加一列。

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **参数** 

    * **`string $name`**
      * **功能**：指定字段的名称
      * **默认值**：无
      * **其它值**：无

    * **`int $type`**
      * **功能**：指定字段类型
      * **默认值**：无
      * **其它值**：Table::TYPE_INT, Table::TYPE_FLOAT, Table::TYPE_STRING

    * **`int $size`**
      * **功能**：指定字符串字段的最大长度【字符串类型的字段必须指定 $size】
      * **值单位**：字节
      * **默认值**：无
      * **其它值**：无

  * **$type 类型说明**


类型 | 说明
---|---
Table::TYPE_INT | 默认为 8 个字节
Table::TYPE_STRING | 设置后，设置的字符串不能超过 $size 指定的最大长度
Table::TYPE_FLOAT | 会占用 8 个字节的内存


### create()

创建内存表。定义好表的结构后，执行 create 向操作系统申请内存，创建表。

```php
Swoole\Table->create(): bool
```

使用 create 方法创建表后，可以读取 [memorySize](/memory/table?id=memorysize) 属性获取实际占用内存的尺寸

  * **提示** 

    * 调用 create 之前不能使用 set、get 等数据读写操作方法
    * 调用 create 之后不能使用 column 方法添加新字段
    * 系统内存不足，申请失败，create 返回 false
    * 申请内存成功，create 返回 true

    !> Table 使用共享内存来保存数据，在创建子进程前，务必要执行 Table->create() ；  
    Server 中使用 Table，Table->create() 必须在 Server->start() 前执行。

  * **使用示例**

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

設定行的數據。`Table`使用`key-value`的方式來訪問數據。

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **參數** 

    * **`string $key`**
      * **功能**：數據的`key`
      * **預設值**：無
      * **其它值**：無

      !> 相似的`$key`對應同一行數據，如果`set`同一個`key`，會覆蓋上一次的數據，`key`最大長度不得超過63字節

    * **`array $value`**
      * **功能**：數據的`value`
      * **預設值**：無
      * **其它值**：無

      !> 必須是一個陣列，必須與字段定義的`$name`完全相同

  * **返回值**

    * 設定成功返回`true`
    * 失敗返回`false`，可能是由於Hash衝突過多導致動態空間無法分配內存，可以調大構造方法第二個參數

!> -`Table->set()` 可以設定全部字段的值，也可以只修改部分字段；  
   -`Table->set()` 未設定前，該行數據的所有字段均為空；  
   -`set`/`get`/`del` 是自帶行鎖，所以不需要調用`lock`加鎖；  
   -**Key 非二進制安全，必須為字符串類型，不得傳入二進制數據。**
    
  * **使用示範**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **設定超過最大長度字符串**
    
    如果傳入字符串長度超過了列定義時設定的最大尺寸，底層會自動截斷。
    
    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * `str_value`列最大尺寸為5字節，但`set`設定了超過`5`字節的字符串
    * 底層會自動截取5字節的數據，最終`str_value`的值为`world`

!> 從`v4.3`版本開始，底層對內存長度做了對齊處理。字符串長度必須為8的整數倍，如長度為5會自動對齊到8字節，所以`str_value`的值为`world 12`


### incr()

原子自增操作。

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **參數** 

    * **`string $key`**
      * **功能**：數據的`key`【如果`$key`對應的行不存在，預設列的值为`0`】
      * **預設值**：無
      * **其它值**：無

    * **`string $column`**
      * **功能**：指定列名【僅支持浮點型和整型字段】
      * **預設值**：無
      * **其它值**：無

    * **`string $incrby`**
      * **功能**：增量 【如果列为`int`，`$incrby`必須為`int`型，如果列为`float`型，`$incrby`必須為`float`類型】
      * **預設值**：`1`
      * **其它值**：無

  * **返回值**

    返回最終的結果數值


### decr()

原子自減操作。

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **參數** 

    * **`string $key`**
      * **功能**：數據的`key`【如果`$key`對應的行不存在，預設列的值为`0`】
      * **預設值**：無
      * **其它值**：無

    * **`string $column`**
      * **功能**：指定列名【僅支持浮點型和整型字段】
      * **預設值**：無
      * **其它值**：無

    * **`string $decrby`**
      * **功能**：增量 【如果列为`int`，`$decrby`必須為`int`型，如果列为`float`型，`$decrby`必須為`float`類型】
      * **預設值**：`1`
      * **其它值**：無

  * **返回值**

    返回最終的結果數值

    !> 數值為`0`時遞減會變成負數


### get()

獲取一行數據。

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **參數** 

    * **`string $key`**
      * **功能**：數據的`key`【必須為字符串類型】
      * **預設值**：無
      * **其它值**：無

    * **`string $field`**
      * **功能**：當指定了`$field`時僅返回該字段的值，而不是整個記錄
      * **預設值**：無
      * **其它值**：無
      
  * **返回值**

    * `$key`不存在，將返回`false`
    * 成功返回結果陣列
    * 當指定了`$field`時僅返回該字段的值，而不是整個記錄


### exist()

檢查table中是否存在某一個key。

```php
Swoole\Table->exist(string $key): bool
```

  * **參數** 

    * **`string $key`**
      * **功能**：數據的`key`【必須為字符串類型】
      * **預設值**：無
      * **其它值**：無


### count()

返回table中存在的條目數。

```php
Swoole\Table->count(): int
```


### del()

刪除數據。

!> `Key`非二進制安全，必須為字符串類型，不得傳入二進制數據；**請勿在遍歷時刪除**。

```php
Swoole\Table->del(string $key): bool
```

  * **返回值**

    * `$key`對應的數據不存在，將返回`false`
    * 成功刪除返回`true`


### stats()

獲取 `Swoole\Table` 狀態。

```php
Swoole\Table->stats(): array
```

!> Swoole版本 >= `v4.8.0` 可用


## 助手函數 :id=swoole_table

方便用戶快速創建一個`Swoole\Table`。

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Swoole版本 >= `v4.6.0` 可用。`$fields`格式為`foo:i/foo:s:num/foo:f`

| 短名 | 長名   | 類型               |
| ---- | ------ | ------------------ |
| i    | int    | Table::TYPE_INT    |
| s    | string | Table::TYPE_STRING |
| f    | float  | Table::TYPE_FLOAT  |

示範：

```php
$table = swoole_table(1024, 'fd:int, reactor_id:i, data:s:64');
var_dump($table);

$table = new Swoole\Table(1024, 0.25);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();
var_dump($table);
```

## 完整示範

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
