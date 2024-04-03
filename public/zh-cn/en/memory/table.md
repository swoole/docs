# High-performance shared memory Table

Since the `PHP` language does not support multithreading, `Swoole` uses a multi-process model. In the multi-process mode, there is process memory isolation, so modifying `global` global variables and superglobal variables within the working process is ineffective in other processes.

> When setting `worker_num=1`, there is no process isolation, and global variables can be used to store data.

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "connection open: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

Although `$fds` is a global variable, it is only valid within the current process. The `Swoole` server underlying will create multiple `Worker` processes, and when the values are printed out by `var_dump($fds)`, only a part of the connected `fd` will be shown.

The corresponding solution is to use external storage services:

* Databases, such as: `MySQL`, `MongoDB`
* Cache servers, such as: `Redis`, `Memcached`
* Disk files, require locking when accessing with multiple processes concurrently

Normal database and disk file operations involve a lot of `IO` waiting time. Therefore, it is recommended to use:

* `Redis` in-memory database, with very fast read and write speeds, but with TCP connection issues and not the highest performance.
* `/dev/shm` in-memory file system, all read and write operations are completed in memory, with no `IO` consumption, extremely high performance, but the data is not formatted and there are data synchronization issues.

?> In addition to using storage services as mentioned above, it is recommended to use shared memory to store data. `Swoole\Table` is a high-performance and concurrent data structure based on shared memory and locks. It is used to solve the problems of data sharing and synchronization locking between multiple processes/threads. The memory capacity of `Table` is not limited by `PHP`'s `memory_limit`.

!> Do not use the array way to read and write `Table`, be sure to use the API provided in the documentation for operations;
The `Table\Row` object taken out in an array manner is a disposable object, so do not rely on it for too many operations. Starting from version `v4.7.0`, reading and writing `Table` in an array manner is no longer supported, and the `Table\Row` object is removed.

* **Advantages**

  * Strong performance, single thread can read and write `2 million` times per second;
  * Application code does not need to be locked, `Table` has built-in row locks and spin locks, all operations are multi-threaded/multi-process safe. Users do not need to consider data synchronization issues at the user level;
  * Supports multiple processes, `Table` can be used to share data between multiple processes;
  * Uses row locks instead of global locks, only when two processes are reading the same data concurrently on the same `CPU` time will locking occur.

* **Traversal**

!> Do not perform deletion operations during traversal (all `keys` can be taken out for deletion after traversal)

The `Table` class implements the iterator and `Countable` interfaces, allowing traversal using `foreach`, and computing the current number of rows using `count`.

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```
## Properties
### size

Get the maximum number of rows in the table.

```php
Swoole\Table->size;
```
### memorySize

Get the actual memory size occupied, measured in bytes.

```php
Swoole\Table->memorySize;
```
## Method
### \_\_construct()

Create a memory table.

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **Parameters** 

    * **`int $size`**
      * **Function**: Specify the maximum number of rows in the table.
      * **Default Value**: None
      * **Other Values**: None

      !> Since the `Table` is built on shared memory, it cannot be dynamically expanded. Therefore, the `$size` must be calculated and set before creation. The maximum number of rows that `Table` can store is directly related to `$size`, but not exactly the same. For example, if `$size` is set to `1024`, the actual number of rows that can be stored is **less than** `1024`. If `$size` is too large and the machine's memory is insufficient, the creation of `Table` will fail.

    * **`float $conflict_proportion`**
      * **Function**: The maximum ratio of hash conflicts.
      * **Default Value**: `0.2` (i.e., `20%`)
      * **Other Values**: Minimum is `0.2`, and maximum is `1`

  * **Capacity Calculation**

      * If `$size` is not a power of `2` such as `1024`, `8192`, `65536`, etc., the underlying system will automatically adjust it to the closest number. If it is less than `1024`, it will default to `1024`, which is the minimum value. Starting from version `v4.4.6`, the minimum value is `64`.
      * The total memory occupied by the `Table` is calculated as (`HashTable structure length` + `KEY length 64 bytes` + `$size value`) * (`1 + $conflict_proportion value as hash conflict`) * (`column size`).
      * If your data keys and hash conflict rate exceed `20%`, causing the allocated conflict memory block capacity to be insufficient, attempting to `set` new data will result in an `Unable to allocate memory` error, returning `false` and failing to store the data. In this case, you need to increase the `$size` value and restart the service.
      * If there is enough memory available, try to set this value larger.
### column()

Add a column to the memory table.

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **Parameters** 

    * **`string $name`**
      * **Description**: Specify the name of the field
      * **Default value**: None
      * **Other values**: None

    * **`int $type`**
      * **Description**: Specify the field type
      * **Default value**: None
      * **Other values**: `Table::TYPE_INT`, `Table::TYPE_FLOAT`, `Table::TYPE_STRING`

    * **`int $size`**
      * **Description**: Specify the maximum length of the string field. 【Size must be specified for string type fields】
      * **Unit**: Bytes
      * **Default value**: None
      * **Other values**: None

  * **Explanation of `$type`**

Type | Description
---|---
Table::TYPE_INT | Default is 8 bytes
Table::TYPE_STRING | Once set, the string's length should not exceed the maximum length specified by `$size`
Table::TYPE_FLOAT | Will occupy 8 bytes of memory
### create()

Create an in-memory table. After defining the structure of the table, execute `create` to request memory from the operating system and create the table.

```php
Swoole\Table->create(): bool
```

After creating the table using the `create` method, you can read the [memorySize](/memory/table?id=memorysize) property to get the actual size of memory used.

  * **Tips** 

    * Before calling `create`, you cannot use data read/write methods like `set` or `get`.
    * After calling `create`, you cannot use the `column` method to add new fields.
    * If there is insufficient system memory and the allocation fails, `create` returns `false`.
    * If memory allocation is successful, `create` returns `true`.

    !> `Table` uses shared memory to store data. It is essential to execute `Table->create()` before creating child processes;  
    When using `Table` in a `Server`, `Table->create()` must be executed before `Server->start()`.

  * **Usage Example**

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

Sets the data of a row. `Table` accesses data using the `key-value` method.

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **Parameters** 

    * **`string $key`**
      * **Functionality**: Key of the data
      * **Default value**: None
      * **Other values**: None

      !> The same `$key` corresponds to the same row of data. If `set` with the same `key`, it will overwrite the previous data. The maximum length of the key must not exceed 63 bytes.

    * **`array $value`**
      * **Functionality**: Value of the data
      * **Default value**: None
      * **Other values**: None

      !> It must be an array and must match the `$name` defined in the fields.

  * **Return Value**

    * Returns `true` on successful set
    * Returns `false` on failure, may be due to too many hash conflicts causing dynamic space to run out of memory. It can be resolved by increasing the second parameter in the constructor method.

!> - `Table->set()` can set values for all fields or only modify some fields.
   - Before `Table->set()` is called, all fields of the row data are empty.
   - `set`/`get`/`del` come with row locks, so there is no need to call `lock` to lock.
   - **The key is not binary safe, must be of string type, and binary data must not be passed in.**
    
  * **Usage Example**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **Setting Strings Exceeding Maximum Length**
    
    If the length of the string passed exceeds the maximum size set when defining the column, the underlying system will automatically truncate it.
    
    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * The `str_value` column has a maximum size of 5 bytes, but a string exceeding `5` bytes is set using `set`.
    * The system automatically truncates the data to 5 bytes, so the final value of `str_value` will be `world`.

!> Starting from `v4.3`, the underlying system aligns the length of memory. The string length must be a multiple of 8, so a length of 5 will be automatically aligned to 8 bytes. Hence the value of `str_value` will be `world 12`.
### incr()

Atomic increment operation.

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **Parameters**

    * **`string $key`**
      * **Description**: Key for the data【If the row corresponding to `$key` does not exist, the default value of the column is `0`】
      * **Default Value**: None
      * **Other Values**: None

    * **`string $column`**
      * **Description**: Specify the column name【Only support float and integer fields】
      * **Default Value**: None
      * **Other Values**: None

    * **`string $incrby`**
      * **Description**: Increment value【If the column is `int`, `$incrby` must be of type `int`, if the column is `float`, `$incrby` must be of type `float`】
      * **Default Value**: `1`
      * **Other Values**: None

  * **Return Value**

    Returns the final result number.
### decr()

Atomic decrement operation.

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **Parameters**

    * **`string $key`**
      * **Function**: Key of the data【If the row corresponding to `$key` does not exist, the default value of the column is `0`】
      * **Default Value**: None
      * **Other Values**: None

    * **`string $column`**
      * **Function**: Specify the column name【Supports only float and integer fields】
      * **Default Value**: None
      * **Other Values**: None

    * **`string $decrby`**
      * **Function**: Increment【If the column is `int`, `$decrby` must be `int`, if the column is `float`, `$decrby` must be of `float` type】
      * **Default Value**: `1`
      * **Other Values**: None

  * **Return Value**

    Returns the final result number

    !> If the number is `0`, decrementing will result in a negative number
### get()

Get a row of data.

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **Parameters** 

    * **`string $key`**
      * **Function**: Key of the data 【Must be of string type】
      * **Default**: None
      * **Other values**: None

    * **`string $field`**
      * **Function**: If `$field` is specified, only return the value of that field, not the entire record
      * **Default**: None
      * **Other values**: None
      
  * **Return Value**

    * If `$key` does not exist, `false` will be returned
    * Return the result array if successful
    * If `$field` is specified, only return the value of that field, not the entire record
### exist()

Check if a key exists in the table.

```php
Swoole\Table->exist(string $key): bool
```

  * **Parameters** 

    * **`string $key`**
      * **Description**: The key of the data (must be a string).
      * **Default value**: None
      * **Other values**: None
### count()

Returns the number of entries in the table.

```php
Swoole\Table->count(): int
```
### del()

Delete data.

!> The `key` is not binary safe and must be a string type. It cannot be binary data. **Do not delete while iterating**.

```php
Swoole\Table->del(string $key): bool
```

  * **Return Values**

    * If the data corresponding to `$key` does not exist, `false` will be returned.
    * If deletion is successful, `true` will be returned.
### stats()

Get the `Swoole\Table` status.

```php
Swoole\Table->stats(): array
```

!> Available since Swoole version >= `v4.8.0`
## Helper function: id = swoole_table

Convenient for users to quickly create a `Swoole\Table`.

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Available in Swoole version >= `v4.6.0`. The `$fields` format is `foo:i/foo:s:num/foo:f`

| Short Name | Long Name | Type               |
| ---------- | --------- | ------------------ |
| i          | int       | Table::TYPE_INT    |
| s          | string    | Table::TYPE_STRING |
| f          | float     | Table::TYPE_FLOAT  |

Example:

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
## Complete Example

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
