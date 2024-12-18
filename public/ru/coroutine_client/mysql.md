# Корoutine\MySQL

Корoutine-клиент для MySQL.

!> Этот клиент больше не рекомендуется использовать, рекомендуется использовать `Swoole\Runtime::enableCoroutine` + `pdo_mysql` или `mysqli`, то есть [одно нажатие для корoutine-화](/runtime) нативного клиента MySQL  
!> После `Swoole 6.0` этот корoutine-клиент для MySQL был удален


## Примеры использования

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    var_dump($res);
});
```


## Свойство defer

Пожалуйста, обратитесь к разделу [并发Client](/coroutine/multi_call).


## Процедуры

Начиная с версии `4.0.0`, поддерживается выполнение процедур в MySQL и получение нескольких результатов.


## MySQL8.0

`Swoole-4.0.1` или более поздние версии поддерживают все возможности безопасности MySQL8, и вы можете использовать клиент напрямую без необходимости возвращения к настройкам паролей


### Версия ниже 4.0.1

По умолчанию MySQL8 использует более безопасный плагин `caching_sha2_password`. Если вы подняли из 5.x, вы можете использовать все функции MySQL напрямую. Если вы создали новый MySQL, вам нужно войти в командную строку MySQL и выполнить следующие операции для совместимости:

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

Замените `'root'@'localhost'` на ваш пользователь и `password` на его пароль.

Если вы все еще не можете использовать его, вы должны настроить в my.cnf `default_authentication_plugin = mysql_native_password`


## Свойства


### serverInfo

Информация о подключении, сохраняется массив переданный функции подключения.


### sock

Файловый дескриптор, используемый для подключения.


### connected

Проверено ли соединение с сервером MySQL.

!> Смотрите [свойство connected и состояние соединения не согласованы](/question/use?id=connected%E7%8A%B6%E6%80%81%E4%B8%8D%E5%90%8C)


### connect_error

Информация об ошибке при подключении к серверу MySQL.


### connect_errno

Ошибочный код при подключении к серверу MySQL, тип - целое число.


### error

Информация об ошибке, возвращенной сервером MySQL при выполнении команды.


### errno

Ошибочный код, возвращенный сервером MySQL при выполнении команды, тип - целое число.


### affected_rows

Количество затронутых строк.


### insert_id

Идентификационный номер последней вставленной записи.


## Методы


### connect()

Создать соединение с MySQL.

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

!> `$serverInfo`：параметры передаются в виде массива

```php
[
    'host'        => 'IP адрес MySQL', // Если это локальный UNIX Socket, то следует填写 в форме `unix://tmp/your_file.sock`
    'user'        => 'пользователь данных',
    'password'    => 'пароль базы данных',
    'database'    => 'имя базы данных',
    'port'        => ' порт MySQL по умолчанию 3306, необязательный параметр',
    'timeout'     => 'время ожидания подключения', // влияет только на время ожидания подключения, не влияет на методы query и execute, смотрите [правила времени ожидания клиента](/coroutine_client/init?id=правила%E8%BF%99%E9%92%AE))
    'charset'     => 'кодировка',
    'strict_type' => false, // Включить строгий режим, данные, возвращенные методом query, также будут преобразованы в строгие типы
    'fetch_mode'  => true,  // Включить режим fetch, можно использовать fetch/fetchAll для чтения строк по одной или получения всех результатов (версия 4.0 и выше)
]
```


### query()

Выполнить SQL-запрос.

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **Параметры** 

    * **`string $sql`**
      * **Функция**：SQL-запрос
      * **По умолчанию**：нет
      * **Другие значения**：нет

    * **`float $timeout`**
      * **Функция**：время ожидания 【Если в течение установленного времени сервер MySQL не возвращает данные, низший уровень вернет `false`, установит ошибочный код как `110` и разорвет соединение】
      * **Единица измерения**：секунда, минимальная точность - миллисекунда (`0.001` секунды)
      * **По умолчанию**：`0`
      * **Другие значения**：нет
      * **Смотрите [правила времени ожидания клиента](/coroutine_client/init?id=правила%E8%BF%99%E9%92%AE))**


  * **Возвращаемое значение**

    * В случае истечения времени/ошибки возвращается `false`, в противном случае `array` в виде результатов запроса

  * **Задержка при приеме**

  !> После установки `defer` вызов `query` сразу вернет `true`. Для получения результатов необходимо вызвать `recv`, который будет ждать в корoutine и вернуть результаты запроса.

  * **Пример**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('show tables');
    if ($res === false) {
        return;
    }
    var_dump($res);
});
```


### prepare()

Отправьте SQL-предварительный запрос на сервер MySQL.

!> `prepare` должен использоваться совместно с `execute`. После успешного предварительного запроса необходимо вызвать метод `execute`, чтобы отправить данные на сервер MySQL.

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **Параметры** 

    * **`string $sql`**
      * **Функция**：предварительный запрос 【Используйте `?` в качестве Platzочников для параметров】
      * **По умолчанию**：нет
      * **Другие значения**：нет

    * **`float $timeout`**
      * **Функция**：время ожидания 
      * **Единица измерения**：секунда, минимальная точность - миллисекунда (`0.001` секунды)
      * **По умолчанию**：`0`
      * **Другие значения**：нет
      * **Смотрите [правила времени ожидания клиента](/coroutine_client/init?id=правила%E8%BF%99%E9%92%AE))**


  * **Возвращаемое значение**

    * Если неудачно - возвращается `false`, можно проверить `$db->error` и `$db->errno`, чтобы узнать причину ошибки
    * Если успешно - возвращается объект `Swoole\Coroutine\MySQL\Statement`, можно вызвать метод [execute](/coroutine_client/mysql?id=statement-gtexecute) объекта для отправки параметров

  * **Пример**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10));
        var_dump($ret2);
    }
});
```


### escape()

Откройте специальные символы в SQL-запросе для предотвращения атаки SQL-инъекций. низший уровень реализован на основе функций, предоставляемых `mysqlnd`, и требует зависимости от расширения PHP `mysqlnd`.

!> При сборке необходимо добавить [--enable-mysqlnd](/environment?id=сборочные%20опции) для включения.

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

  * **Параметры** 

    * **`string $str`**
      * **Функция**：открытый символ
      * **По умолчанию**：нет
      * **Другие значения**：нет

  * **Пример использования**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $data = $db->escape("abc'efg\r\n");
});
```
### begin()

Начинает транзакцию. Используется в сочетании с `commit` и `rollback` для реализации управления транзакциями в `MySQL`.

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> Запускает транзакцию в `MySQL`, успешный результат возвращает `true`, неудачный - `false`. Для получения кода ошибки используйте `$db->errno`.

!> На одном объекте соединения `MySQL` можно начать только одну транзакцию одновременно;
Необходимо подождать, пока предыдущая транзакция будет завершена с помощью `commit` или `rollback`, прежде чем начать новую;
В противном случае под底层ной частью будет выброшена исключение `Swoole\MySQL\Exception` с кодом ошибки `21`.

  * **Пример**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```


### commit()

Относит транзакцию к завершенной стадии.

!> Необходимо использовать в сочетании с `begin`.

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> Успешное завершение возвращает `true`, неудачное - `false`. Для получения кода ошибки используйте `$db->errno`.


### rollback()

Откатывает транзакцию.

!> Необходимо использовать в сочетании с `begin`.

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> Успешное завершение возвращает `true`, неудачное - `false`. Для получения кода ошибки используйте `$db->errno`.


### Statement->execute()

Отправляет подготовленные данные в MySQL-сервер.

!> Метод `execute` должен использоваться в сочетании с `prepare`, и перед тем как вызвать `execute`, необходимо сначала вызвать `prepare` для отправки запроса на предварительное выполнение.

!> Метод `execute` может быть вызван повторно.

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **Параметры** 

    * **`array $params`**
      * **Функция**: Предварительные данные для выполнения 【необходимо соответствовать количеству параметров в команде `prepare`. `$params` должен быть массивом с числовыми индексами, порядок параметров должен совпадать с командой `prepare`】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`float $timeout`**
      * **Функция**: Время ожидания 【если за установленное время MySQL-сервер не вернет данные, низший уровень вернет `false`, установит код ошибки `110` и закроет соединение】
      * **Единица измерения**: секунды, минимальная точность до миллисекунд (0.001 секунды)
      * **По умолчанию**: `-1`
      * **Другие значения**: Нет
      * **Смотрите [правила таймаута клиентов](/coroutine_client/init?id=правила таймаута)**

  * **Возвращаемое значение** 

    * При успехе возвращается `true`, если при подключении был установлен параметр `fetch_mode` в `true`
    * При успехе возвращается `array` набора данных, в противном случае,
    * При неудаче возвращается `false`, можно проверить `$db->error` и `$db->errno` чтобы узнать причину ошибки

  * **Пример использования** 

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=? and name=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10, 'rango'));
        var_dump($ret2);

        $ret3 = $stmt->execute(array(13, 'alvin'));
        var_dump($ret3);
    }
});
```


### Statement->fetch()

Получает следующую строку из набора результатов.

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> Для версий Swoole >= `4.0-rc1`, необходимо добавить опцию `fetch_mode => true` при подключении

  * **Пример** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> Начиная с новой версии драйвера MySQL v4.4.0, `fetch` должен использоваться в примере, чтобы прочитать до `NULL`, иначе невозможно отправить новый запрос (из-за механизма чтения по требованию, что позволяет сэкономить память)


### Statement->fetchAll()

Возвращает массив, содержащий все строки набора результатов.

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> Для версий Swoole >= `4.0-rc1`, необходимо добавить опцию `fetch_mode => true` при подключении

  * **Пример** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```

### Statement->nextResult()

Продвигается к следующему результату в многoresponsивном результате команды (например, многoresультатный возврат в хранимом процессе).

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

  * **Возвращаемое значение**

    * При успехе возвращается `TRUE`
    * При неудаче возвращается `FALSE`
    * Если нет следующего результата, возвращается `NULL`

  * **Пример** 

    * **Без режима fetch**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **С режимом fetch**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> Начиная с новой версии драйвера MySQL v4.4.0, `fetch` должен использоваться в примере, чтобы прочитать до `NULL`, иначе невозможно отправить новый запрос (из-за механизма чтения по требованию, что позволяет сэкономить память)
