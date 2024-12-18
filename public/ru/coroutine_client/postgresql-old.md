# Корoutine\PostgreSQL Старая версия

Корoutine клиент для `PostgreSQL`. Для включения этой функции необходимо скомпилировать расширение [ext-postgresql](https://github.com/swoole/ext-postgresql).

> Этот документ применим только к Swoole < 5.0


## Сборка и установка

Скачайте исходный код: [https://github.com/swoole/ext-postgresql](https://github.com/swoole/ext-postgresql), необходимо установить версию releases, соответствующую версии Swoole.

* Необходимо убедиться, что на системе уже установлена библиотека `libpq`
* На `macOS` после установки `postgresql` уже есть встроенная библиотека `libpq`, существуют различия между окружениями, на `ubuntu` может потребоваться `apt-get install libpq-dev`, на `centos` может потребоваться `yum install postgresql10-devel`
* Также можно указать отдельную директорию с `libpq`, например: `./configure --with-libpq-dir=/etc/postgresql`


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
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchAll($result);
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
    $result = $pg->query('SELECT * FROM test');
    $arr = $pg->fetchAll($result);
    $pg->query('COMMIT');
    var_dump($arr);
});
```


## Свойства


### error

Получить информацию об ошибке.


## Методы


### connect()

Создать несинхронный корoutine соединение с `postgresql`.

```php
Swoole\Coroutine\PostgreSQL->connect(string $connection_string): bool
```

!> `$connection_string` содержит информацию о подключении, успешное соединение возвращает `true`, неудачное - `false`, можно использовать свойство [error](/coroutine_client/postgresql?id=error) для получения информации об ошибке.
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

Выполнить SQL-выражение. Отправить асинхронный несинхронный корoutine запрос.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): resource;
```

  * **Параметры** 

    * **`string $sql`**
      * **Функция**: SQL-выражение
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Пример**

    * **select**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
        var_dump($arr);
    });
    ```

    * **возвращение insert id**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
        $result = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $pg->fetchRow($result);
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
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
        $pg->query('COMMIT;');
        var_dump($arr);
    });
    ```


### fetchAll()

```php
Swoole\Coroutine\PostgreSQL->fetchAll(resource $queryResult, $resultType = SW_PGSQL_ASSOC):? array;
```

  * **Параметры**
    * **`$resultType`**
      * **Функция**: Константа. По умолчанию `SW_PGSQL_ASSOC`.
      * **Другие значения**: нет

      Значение | Возвращаемое значение
      ---|---
      SW_PGSQL_ASSOC | Возвращает ассоциативный массив с ключами в виде полевых имен
      SW_PGSQL_NUM | Возвращает массив с ключами в виде полевых номеров
      SW_PGSQL_BOTH | Возвращает массив с ключами в виде обоих

  * **Возвращаемое значение**

    * Возвращает все строки результата как один массив.


### affectedRows()

Возвращает количество измененных записей. 

```php
Swoole\Coroutine\PostgreSQL->affectedRows(resource $queryResult): int
```


### numRows()

Возвращает количество строк.

```php
Swoole\Coroutine\PostgreSQL->numRows(resource $queryResult): int
```


### fetchObject()

Извлекает одну строку как объект. 

```php
Swoole\Coroutine\PostgreSQL->fetchObject(resource $queryResult, int $row): object;
```

  * **Пример**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    for ($row = 0; $row < $pg->numRows($result); $row++) {
        $data = $pg->fetchObject($result, $row);
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
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    while ($data = $pg->fetchObject($result, $row)) {
        echo $data->id . " \n ";
        $row++;
    }
});
```


### fetchAssoc()

Извлекает одну строку как ассоциативный массив.

```php
Swoole\Coroutine\PostgreSQL->fetchAssoc(resource $queryResult, int $row): array
```


### fetchArray()

Извлекает одну строку как массив.

```php
Swoole\Coroutine\PostgreSQL->fetchArray(resource $queryResult, int $row, $resultType = SW_PGSQL_BOTH): array|false
```

  * **Параметры**
    * **`int $row`**
      * **Функция**: `$row` - номер строки (записи), которую хочется извлечь. Первая строка имеет номер `0`.
      * **По умолчанию**: нет
      * **Другие значения**: нет
    * **`$resultType`**
      * **Функция**: Константа. По умолчанию `SW_PGSQL_BOTH`.
      * **Другие значения**: нет

      Значение | Возвращаемое значение
      ---|---
      SW_PGSQL_ASSOC | Возвращает ассоциативный массив с ключами в виде полевых имен
      SW_PGSQL_NUM | Возвращает массив с ключами в виде полевых номеров
      SW_PGSQL_BOTH | Возвращает массив с ключами в виде обоих

  * **Возвращаемое значение**

    * Возвращает массив, соответствующий извлеченной строке (пара/записи). Если больше строк не осталось для извлечения, то возвращается `false`.

  * **Использование примера**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchArray($result, 1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```
### fetchRow()

Исходя из указанного ресурса `result`, извлекается одна строка данных (запись) в виде массива и возвращается. Каждая полученная колонка依次 размещается в массиве, начиная с смещения `0`.

```php
Swoole\Coroutine\PostgreSQL->fetchRow(resource $queryResult, int $row, $resultType = SW_PGSQL_NUM): array|false
```

  * **Параметры**
    * **`int $row`**
      * **Функция**: `row` - номер строки (записи), которую необходимо извлечь. Первая строка имеет номер `0`.
      * **По умолчанию**: нет
      * **Другие значения**: нет
    * **`$resultType`**
      * **Функция**: константа. オпциональный параметр, контролирующий способ инициализации возвращаемого значения.
      * **По умолчанию**: `SW_PGSQL_NUM`
      * **Другие значения**: нет

      Значение | Возвращаемое значение
      --- | ---
      `SW_PGSQL_ASSOC` | Возвращает связанный массив с ключами в виде полевых имен
      `SW_PGSQL_NUM` | Возвращает связанный массив с ключами в виде полевых номеров
      `SW_PGSQL_BOTH` | Возвращает связанный массив с ключами как из полевых имен, так и из полевых номеров

  * **Возвращаемое значение**

    * Возвращаемый массив соответствует извлеченной строке. Если нет больше строк для извлечения с номером `row`, то возвращается `false`.

  * **Пример использования**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    while ($row = $pg->fetchRow($result)) {
        echo "name: $row[0] mobile: $row[1]" . PHP_EOL;
    }
});
```

### metaData()

Вывести метаданные таблицы.的异步非阻塞协程版。

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

Подготовка.

```php
Swoole\Coroutine\PostgreSQL->prepare(string $name, string $sql);
Swoole\Coroutine\PostgreSQL->execute(string $name, array $bind);
```

  * **Пример использования**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $pg->prepare("my_query", "select * from  test where id > $1 and id < $2");
    $res = $pg->execute("my_query", array(1, 3));
    $arr = $pg->fetchAll($res);
    var_dump($arr);
});
```
