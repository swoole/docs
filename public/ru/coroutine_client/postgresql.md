# Корoutine\PostgreSQL

Корутина для клиента PostgreSQL.

!> В новой версии Swoole 5.0 полностью переписана и имеет совершенно другой подход к использованию, чем старая версия. Если вы используете старую версию, пожалуйста, ознакомьтесь с [старым документацией](/coroutine_client/postgresql-old.md).

!> После Swoole 6.0 клиент корутины PostgreSQL был удален, вместо этого используйте [корутинизированный pdo_pgsql](/runtime?id=swoole_hook_pdo_pgsql).

## Сборка и установка

* Необходимо Ensure, что на системе уже установлена библиотека `libpq`.
* Для `macOS` после установки PostgreSQL уже comes с library `libpq`, но существуют различия между окружениями, на `ubuntu` возможно потребоваться `apt-get install libpq-dev`, на `centos` - `yum install postgresql10-devel`.
* При сборке Swoole необходимо добавить опцию: `./configure --enable-swoole-pgsql`.

## Примеры использования

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    if (!$conn) {
        var_dump($pg->error);
        return;
    }
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchAll();
    var_dump($arr);
});
```

### Обработка транзакций

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    $pg->query('BEGIN');
    $stmt = $pg->query('SELECT * FROM test');
    $arr = $stmt->fetchAll();
    $pg->query('COMMIT');
    var_dump($arr);
});
```

## Свойства


### error

Получение информации об ошибке.


## Методы


### connect()

Создание несинхронного, корутинированного соединения с `postgresql`.

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo` содержит информацию о подключении, успешное создание соединения возвращает `true`, неудачное - `false`, для получения информации об ошибке используйте свойство [error](/coroutine_client/postgresql?id=error).
  * **Пример**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
    var_dump($pg->error, $conn);
});
```


### query()

Исполнение SQL-выражения. Отправка асинхронного, корутинированного команды.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
```

  * **Параметры** 

    * **`string $sql`**
      * **Функция**: SQL-выражение
      * **Значение по умолчанию**: нет
      * **Другие значения**: нет

  * **Пример**

    * **select**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        var_dump($arr);
    });
    ```

    * **возвращение id вставленного элемента**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
        $stmt = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $stmt->fetchRow();
        var_dump($arr);
    });
    ```

    * **transaction**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $pg->query('BEGIN;');
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        $pg->query('COMMIT;');
        var_dump($arr);
    });
    ```


### metaData()

Взглянуть на метаданные таблицы. асинхронная, корутинированная версия.

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```    
  * **Пример использования**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->metaData('test');
    var_dump($result);
});
```


### prepare()

Подготовление.

```php
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **Пример использования**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $stmt = $pg->prepare("select * from test where id > $1 and id < $2");
    $res = $stmt->execute(array(1, 3));
    $arr = $stmt->fetchAll();
    var_dump($arr);
});
```


## PostgreSQLStatement

Класс: `Swoole\Coroutine\PostgreSQLStatement`

Все запросы возвращают объект `PostgreSQLStatement`.


### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **Параметры**
    * **`$result_type`**:
      * **Функция**: Константина. По возможности, задается параметр, контролирующий способ инициализации возвращаемого значения.
      * **Значение по умолчанию**: `SW_PGSQL_ASSOC`
      * **Другие значения**: Нет

      Значения | Возвращаемые значения
      --- | ---
      `SW_PGSQL_ASSOC` | Возвращается ассоциативный массив, ключами которого являются имена полей.
      `SW_PGSQL_NUM` | Возвращается массив с индексами полей.
      `SW_PGSQL_BOTH` | Возвращается массив с ключами, включающими как имена полей, так и их номера.

  * **Возвращаемое значение**

    * Возвращает все строки результаты в виде массива.


### affectedRows()

Возвращает количество измененных записей. 

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```


### numRows()

Возвращает количество строк.

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```


### fetchObject()

Искать строку как объект. 

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **Пример**:

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    
    for ($row = 0; $row < $stmt->numRows(); $row++) {
        $data = $stmt->fetchObject($row);
        echo $data->id . " \n ";
    }
});
```
```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    
    while ($data = $stmt->fetchObject()) {
        echo $data->id . " \n ";
    }
});
```


### fetchAssoc()

Искать строку как ассоциативный массив.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```


### fetchArray()

Искать строку как массив.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **Параметры**
    * **`int $row`**:
      * **Функция**: `$row` - номер строки, которую хочется получить. Первая строка имеет номер `0`.
      * **Значение по умолчанию**: нет
      * **Другие значения**: нет
    * **`$result_type`**:
      * **Функция**: Константина. По возможности, задается параметр, контролирующий способ инициализации возвращаемого значения.
      * **Значение по умолчанию**: `SW_PGSQL_BOTH`
      * **Другие значения**: Нет

      Значения | Возвращаемые значения
      --- | ---
      `SW_PGSQL_ASSOC` | Возвращается ассоциативный массив, ключами которого являются имена полей.
      `SW_PGSQL_NUM` | Возвращается массив с индексами полей.
      `SW_PGSQL_BOTH` | Возвращается массив с ключами, включающими как имена полей, так и их номера.

  * **Возвращаемое значение**

    * Возвращает массив, соответствующего extracted строке/записью. Если больше строк не осталось для извлечения, то возвращается `false`.

  * **Пример использования**:

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchArray(1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```
### fetchRow()

Исходя из указанного ресурса `result`, функция извлекает одну строку данных (запись) в виде массива и возвращает его. Каждая полученная колонка依次 размещается в массиве, начиная с смещения `0`.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **Параметры**
    * **`int $row`**
      * **Функция**: `row` - номер строки (записи), которую хочется извлечь. Первая строка имеет номер `0`.
      * **По умолчанию**: нет
      * **Другие значения**: нет
    * **`$result_type`**
      * **Функция**: Константа. необязательный параметр, который контролирует初始化 возвращаемого значения.
      * **По умолчанию**: `SW_PGSQL_NUM`
      * **Другие значения**: нет

      Значения | Возвращаемое значение
      --- | ---
      `SW_PGSQL_ASSOC` | Возвращает ассоциативный массив с ключами, равными названиям полей.
      `SW_PGSQL_NUM` | Возвращает массив с ключами, равными номерам полей.
      `SW_PGSQL_BOTH` | Возвращает массив с ключами, равными как названиям полей, так и номерам полей.

  * **Возвращаемое значение**

    * Возвращаемый массив соответствует извлеченной строке. Если нет больше строк для извлечения с помощью `row`, то возвращается `false`.

  * **Пример использования**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    while ($row = $stmt->fetchRow()) {
        echo "name: $row[0]  mobile: $row[1]" . PHP_EOL;
    }
});
```
