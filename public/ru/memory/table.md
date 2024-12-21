# Высокоскоростная shared memory Table

Поскольку язык PHP не поддерживает многопутевое выполнение, Swoole использует модель многопроцессов. В многопроцессной модели существует изоляция памяти между процессами, и изменения глобальных переменных и сверхглобальных переменных внутри рабочих процессов не будут действительными в других процессах.

> Когда устанавливаете `worker_num=1`, изоляция процессов отсутствует, и можно использовать глобальные переменные для хранения данных

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "connection open: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

Хотя `$fds` является глобальной переменной, она действительна только в текущем процессе. На нижнем уровне Swoole-сервер создает несколько рабочих процессов, и в результате выполнения `var_dump($fds)` будет выведен только часть соединенных `fd`.

Соответствующим решением является использование внешнего хранилища:

* Базы данных, такие как: `MySQL`, `MongoDB`
* Кэш-серверы, такие как: `Redis`, `Memcache`
* Файлы на диске, при одновременном чтении и письме в многопроцессной среде необходимо использовать блокировки

Обычные операции с базами данных и файлами на диске имеют много времени ожидания ввода/вывода (IO). Поэтому рекомендуется использовать:

* `Redis` - высокоскоростная память-база данных, скорость чтения и письма очень высока, но есть проблемы с TCP-соединением и т.д., и производительность не самая высокая.
* `/dev/shm` - память-файловая система, все операции чтения и письма полностью выполняются в памяти, без потребления IO, производительность очень высока, но данные не格式ированы, есть проблемы с синхронизацией данных.

Кроме вышеупомянутых способов хранения, рекомендуется использовать shared memory для хранения данных, `Swoole\Table` - это сверхвысокоэффективная структура данных для синхронизации и блокировки данных в многопроцессной/многопутевой среде, основанная на shared memory и блокировках. `Table` не контролируется PHP `memory_limit`

!> Не используйте способ чтения и письма массивом `Table`, обязательно используйте API, предоставляемый в документации;  
Объекты `Table\Row`, получаемые путем чтения массива, являются одноразовыми объектами, пожалуйста, не полагайтесь на них для множества операций.
Начиная с версии `v4.7.0`, поддержка чтения и письма `Table` в массовом виде была удалена, а также был удален объект `Table\Row`.

* **Преимущества**

  * Высокая производительность, одна нить может читать и писать 2 миллиона раз в секунду;
  * Application code не требует блокировок, `Table` встроенный рывок блокировки на строку, все операции безопасны для многопутевого/многопроцессного выполнения. Пользовательский уровень совсем не нуждается в проблемах с синхронизацией данных;
  * Поддержка многопроцессов, `Table` может использоваться для общего доступа к данным между многопроцессами;
  * Использование блокировки строки, а не глобальной блокировки, блокировка происходит только когда два процесса одновременно читают одну и ту же данные в течение одного CPU времени.

* **Пропуск**

!> Не进行删除 операций во время обхода (можно удалить все `key` после их получения)

Класс `Table` реализует интерфейс迭代器和`Countable`, можно использовать `foreach` для обхода, использовать `count` для подсчета текущего числа строк.

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```


## Свойства


### size

Получить максимальное количество строк в таблице.

```php
Swoole\Table->size;
```


### memorySize

Получить фактическую занимаемую память в字节ях.

```php
Swoole\Table->memorySize;
```


## Методы


### __construct()

Создать таблицу в памяти.

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **Параметры** 

    * **`int $size`**
      * **Функция**: Указать максимальное количество строк в таблице
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

      !> Поскольку `Table` основана на shared memory, ее нельзя динамически увеличивать в размерах. Поэтому `$size` должен быть рассчитан и установлен заранее перед созданием, максимальное количество строк, которое может хранить `Table`, положительно связано с `$size`, но не полностью идентично ему, например, если `$size` равно `1024`, фактическое количество строк, которые может хранить `Table**, **меньше**, чем `1024`. Если `$size` слишком велик, и у машины недостаточно памяти, создание `Table` может потерпеть неудачу.  

    * **`float $conflict_proportion`**
      * **Функция**: Максимальная доля конфликтов хэша
      * **По умолчанию**: `0.2` (то есть `20%`)
      * **Другие значения**: Минимальное значение `0.2`, максимальное значение `1`

  * **Расчет мощности**

      * Если `$size` не является степенью числа `2`, например `1024`, `8192`, `65536` и т.д.,底层 автоматически корректируется до близкого числа, если меньше `1024`, то по умолчанию становится `1024`, то есть `1024` - это минимальное значение. Начиная с версии `v4.4.6`, минимальное значение составляет `64`.
      * Общий объем памяти, занимаемой `Table`, составляет (`Длина структуры HashTable` + `Длина ключа 64 байтов` + `$size значение`) * (`1 + `$conflict_proportion значение как hash конфликт`) * (`Размер столбца`).
      * Если ваш ключ данных и скорость конфликта хэша превышают `20%`, а выделенная память для резервирования конфликтов недостаточна, то при попытке установить новые данные возникнет ошибка `Не удалось распределить память` и будет возвращено `false`, что означает неудачу хранения. В этом случае необходимо увеличить значение `$size` и перезапустить сервис.
      * При достатке памяти старайтесь устанавливать это значение как можно больше.


### column()

Добавьте столбец в памятьную таблицу.

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **Параметры** 

    * **`string $name`**
      * **Функция**: Указать имя столбца
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`int $type`**
      * **Функция**: Указать тип столбца
      * **По умолчанию**: Нет
      * **Другие значения**: `Table::TYPE_INT`, `Table::TYPE_FLOAT`, `Table::TYPE_STRING`

    * **`int $size`**
      * **Функция**: Указать максимальную длину строки для столбца字符串 (для столбцов типа string необходимо указать `$size`)
      * **Единица измерения**: Байты
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

  * **Описание типов `$type`**


Тип | Описание
---|---
Table::TYPE_INT | По умолчательно 8 байтов
Table::TYPE_STRING | После установки, установленная строка не должна превышать максимальную длину в `$size` указанном значении
Table::TYPE_FLOAT | Занимает 8 байтов памяти


### create()

Создать памятьную таблицу. После определения структуры таблицы выполнить `create`, чтобы запросить память у операционной системы и создать таблицу.

```php
Swoole\Table->create(): bool
```

Используйте метод `create` для создания таблицы, чтобы получить фактическую занимаемую память в байтах, используя свойство [memorySize](/memory/table?id=memorysize)

  * **Напоминание** 

    * Перед вызовом `create` нельзя использовать методы чтения и письма данных, такие как `set`, `get` и т.д.
    * После вызова `create` нельзя использовать метод `column` для добавления новых столбцов
    * Если системная память недостаточна, запрос на создание потерпит неудачу, `create` вернет `false`
    * Если запрос на память успешен, `create` вернет `true`

    !> `Table` использует shared memory для хранения данных, перед созданием дочернего процесса обязательно необходимо выполнить `Table->create()` ;  
    Когда используется `Server` с `Table`, `Table->create()` должен быть выполнен до `Server->start()`.

  * **Пример использования**

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

Установка данных для строки. `Таблица` использует способ доступа к данным `key-value`.

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **Параметры** 

    * **`string $key`**
      * **Функция**: Ключ данных
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет

      !> тот же `$key` соответствует одной и той же строке данных, если `set` один и тот же `key`, то будут перезаписаны предыдущие данные, максимальная длина `key` не должна превышать 63 байта

    * **`array $value`**
      * **Функция**: Значение данных
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет

      !> Должен быть массивом, должны полностью соответствовать определенным полям `$name`

  * **Возвращаемое значение**

    * Если установка成功的, то возвращается `true`
    * В случае неудачи возвращается `false`, может быть, из-за слишком многих столкновений хэша, невозможно распределить память для динамического пространства, можно увеличить второй параметр конструктора

!> -`Table->set()` может устанавливать все значения полей, а также изменять только часть полей;  
   - Перед `set`/`get`/`del`, эта строка данных и все ее поля пусты;  
   - `set`/`get`/`del` имеют встроенный ряд блокировки, поэтому не нужно вызывать `lock` для блокировки;  
   -**Ключ не безопасен для бинарных данных, должен быть строковым типом, нельзя passar бинарных данных.**
    
  * **Пример использования**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **Установка более длинной строки, чем максимальное значение**
    
    Если传入ая длинная строка превышает максимально заданное размерство для столбца, нижестоящая система автоматически обрезает его.
    
    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * Максимальный размер столбца `str_value` составляет 5 байтов, но `set` установил более длинную, чем `5` байтов, строку
    * Низовая система автоматически обрезает данные до 5 байтов, в итоге значение `str_value` будет `world`

!> Начиная с версии `v4.3`, нижестоящая система проводит выравнивание по памяти. Длина строки должна быть кратной 8, например, длина 5会自动 выравниваться до 8 байтов, так что value `str_value` будет `world 12`


### incr()

Атомическое увеличение операции.

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **Параметры** 

    * **`string $key`**
      * **Функция**: Ключ данных【если строка `$key` не对应的 строка существует, по умолчанию значение столбца равно `0`】
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет

    * **`string $column`**
      * **Функция**: указание имя столбца【поддерживается только для столбцов с типами `float` и `int`】
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет

    * **`string $incrby`**
      * **Функция**: приращение 【если столбец является `int`, `$incrby` должен быть типом `int`, если столбец является `float`, `$incrby` должен быть типом `float`, `】
      * **Значение по умолчанию**: `1`
      * **Другое**: Нет

  * **Возвращаемое значение**

    Возвращается конечное значение числовым结果.


### decr()

Атомическое уменьшение операции.

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **Параметры** 

    * **`string $key`**
      * **Функция**: Ключ данных【если строка `$key` не对应的 строка существует, по умолчанию значение столбца равно `0`】
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет

    * **`string $column`**
      * **Функция**: указание имя столбца【поддерживается только для столбцов с типами `float` и `int`】
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет

    * **`string $decrby`**
      * **Функция**: приращение 【если столбец является `int`, `$decrby` должен быть типом `int`, если столбец является `float`, `$decrby` должен быть типом `float`, `】
      * **Значение по умолчанию**: `1`
      * **Другое**: Нет

  * **Возвращаемое значение**

    Возвращается конечное значение числовым result.

    !> Когда результат равен `0`, уменьшение превратится в отрицательное число.


### get()

Получение данных для одной строки.

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **Параметры** 

    * **`string $key`**
      * **Функция**: Ключ данных【должен быть типом string】
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет

    * **`string $field`**
      * **Функция**: Когда `$field` указана, возвращается только значение этого поля, а не весь результат.
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет
      
  * **Возвращаемое значение**

    * Если `$key` не существует, будет возвращено `false`.
    * Успешный 返回result array.
    * Когда `$field` specified returns仅返回该字段的值,而不是整个记录.


### exist()

Проверка наличия определенного ключа в таблице.

```php
Swoole\Table->exist(string $key): bool
```

  * **Параметры** 

    * **`string $key`**
      * **Функция**: Ключ данных【должен быть типом string】
      * **Значение по умолчанию**: Нет
      * **Другое**: Нет


### count()

Возврат числа существующих записей в таблице.

```php
Swoole\Table->count(): int
```


### del()

Удаление данных.

!> `Ключ` не безопасен для бинарных данных, должен быть типом string, нельзя передавать бинарных данных; **не следует удалять в процессе итерации**。

```php
Swoole\Table->del(string $key): bool
```

  * **Возвращаемое значение**

    Если данные `$key` не существуют, то будет возвращено `false`. Успешное удаление возвращает `true`.


### stats()

Получение информации о состоянии `Swoole\Table`.

```php
Swoole\Table->stats(): array
```

!> Доступно, начиная с Swoole версии >= `v4.8.0`.


## Вспомогательные функции :id=swoole_table

Помогает пользователям быстро создать `Swoole\Table`.

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Доступно, начиная с Swoole версии >= `v4.6.0`. Формат `$fields` следующий: `foo:i/foo:s:num/foo:f`

| Acro | Full name   | Type               |
| ---- | ---------- | ------------------ |
| i    | int        | Table::TYPE_INT    |
| s    | string     | Table::TYPE_STRING |
| f    | float      | Table::TYPE_FLOAT  |

Пример:

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

## Полный пример

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