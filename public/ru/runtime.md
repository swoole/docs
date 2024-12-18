# Время выполнения

По сравнению с `Swoole1.x`, `Swoole4+` предлагает powerful tool - coroutine, все бизнес-кода синхронный, но основное IO - асинхронное, что обеспечивает одновременно возможность многозадачности и избегает разрозненной логики кода и проблемы с поддержанием кода, возникающих в результате многослойной обратной связи в традиционных асинхронных подходах. Чтобы достичь этого эффекта, необходимо, чтобы все `IO` запросы были [асинхронными IO](/learn?id=syncioasyncio), в то время как клиенты, такие как `MySQL`, `Redis` и другие, предоставляемые в эпоху `Swoole1.x`, были асинхронными IO, но использовали подход программирования через обратную связь, а не через coroutines, поэтому в эпоху `Swoole4` эти клиенты были удалены.

Для решения проблемы поддержки coroutines этими клиентами группа разработчиков Swoole проделала большую работу:

- Сначала для каждого типа клиентов был создан coroutine клиент, подробности см. в [Coroutine Client](/coroutine_client/init), но это имело три проблемы:

  * Реализация сложна, каждая клиентская реализация имеет свои сложные протоколы, и чтобы поддерживать их идеально, требуется огромный труд.
  * Пользователям приходится вносить много изменений в код, например, если раньше для запросов в `MySQL` использовался PHP-подобный `PDO`, то теперь нужно использовать методы из [Swoole\Coroutine\MySQL](/coroutine_client/mysql).
  * Трудно охватить все операции, например, функции `proc_open()`, `sleep()` и другие также могут заблокировать, что приводит к тому, что программа становится синхронно блокирующей.


- В ответ на вышеупомянутые проблемы группа разработчиков Swoole изменила подход к реализации, используя способ `Hook` для оригинальных PHP функций для создания coroutine клиентов, с помощью одной строки кода можно превратить существующий синхронный IO код в [координационный план](/coroutine?id=coordination), то есть `однократная координация`.

!> Эта функция стала стабильной после версии `v4.3`, и все больше и больше функций стали `координируемыми`, поэтому некоторые ранее написанные coroutine клиенты больше не рекомендуется использовать, подробности см. в [Coroutine Client](/coroutine_client/init), например: в `v4.3+` поддерживается `координация` для операций с файлами (`file_get_contents`, `fread` и т.д.), если вы используете версию `v4.3+`, то можно напрямую использовать `координированную` версию, а не Swoole предоставленную [координированную операцию с файлами](/coroutine/system).


## Функция прототипа

Установка диапазона функций для `координации` с помощью `flags`

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // используется в версиях v4.4+.
// Или
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

Для активации нескольких `flags` используется операционный оператор `|`

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

!> Функции, которые будут `Hooked`, должны использоваться в [координационном контейнере](/coroutine/scheduler)

#### Часто задаваемые вопросы :id=runtime-qa

!> **Как использовать `Swoole\Runtime::enableCoroutine()` и `Co::set(['hook_flags'])`**
  
* `Swoole\Runtime::enableCoroutine()`, можно динамически устанавливать flags после запуска службы (во время выполнения), после вызова метода они будут действовать глобально в текущем процессе, и должны быть вызваны в начале всего проекта, чтобы получить 100% покрытия;
* `Co::set()` можно сравнить с `ini_set()` в PHP, необходимо вызвать его до [Server->start()](/server/methods?id=start) или до [Co\run()](/coroutine/scheduler), иначе установленные `hook_flags` не будут действовать, в версиях `v4.4+` следует использовать этот способ установки `flags`;
* Будь то `Co::set(['hook_flags'])` или `Swoole\Runtime::enableCoroutine()`, их следует вызывать только один раз, повторные вызовы будут перезаписаны.


## Опции

Поддерживаемые `flags` options включают:


### SWOOLE_HOOK_ALL

Включает все виды flags (за исключением CURL)

!> Начиная с версии v4.5.4, `SWOOLE_HOOK_ALL` включает `SWOOLE_HOOK_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); //не включает CURL
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); //истинная координация всех типов, включая CURL
```


### SWOOLE_HOOK_TCP

Поддерживается начиная с `v4.1`, stream типа TCP Socket, включая самые распространенные `Redis`, `PDO`, `Mysqli` и операции с TCP-соединениями, выполненные с использованием PHP [Streams](https://www.php.net/streams) серия функций, все это может быть `Hooked`, пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//создание 100 coro
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//здесь происходит координация, CPU переключается на следующий coro, процесс не блокируется
            $redis->get('key');//здесь происходит координация, CPU переключается на следующий coro, процесс не блокируется
        });
    }
});
```

Вышеупомянутый код использует оригинальный класс `Redis`, но на самом деле он стал `асинхронным IO`, `Co\run()` создает [координационный контейнер](/coroutine/scheduler), `go()` создает coro, эти операции в [Классах сервера Swoole](/server/init) автоматически реализованы и не требуют ручного выполнения, см. [enable_coroutine](/server/setting?id=enable_coroutine).

Это означает, что традиционные PHP программисты могут писать программы с высокой степенью параллельности и высокой производительность, используя самую знакомую им логику кода, как показано ниже:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);//здесь происходит координация, CPU переключается на следующий coro (на следующий запрос), процесс не блокируется
      $redis->get('key');//здесь происходит координация, CPU переключается на следующий coro (на следующий запрос), процесс не блокируется
});

$http->start();
```


### SWOOLE_HOOK_UNIX

Поддерживается начиная с `v4.2`. Stream типа Unix Stream Socket, пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UNIX]);

Co\run(function () {
    $socket = stream_socket_server(
        'unix://swoole.sock',
        $errno,
        $errstr,
        STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_accept($socket)) {
    }
});
```


### SWOOLE_HOOK_UDP

Поддерживается начиная с `v4.2`. Stream типа UDP Socket, пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDP]);

Co\run(function () {
    $socket = stream_socket_server(
        'udp://0.0.0.0:6666',
        $errno,
        $errstr,
        STREAM_SERVER_BIND
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_recvfrom($socket, 1, 0)) {
    }
});
```
### SWOOLE_HOOK_UDG

Начиная с `v4.2`, поддерживается Stream типа Unix Domain Socket, пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDG]);

Co\run(function () {
    $socket = stream_socket_server(
        'udg://swoole.sock',
        $errno,
        $errstr,
        STREAM_SERVER_BIND
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_recvfrom($socket, 1, 0)) {
    }
});
```

### SWOOLE_HOOK_SSL

Начиная с `v4.2`, поддерживается Stream типа SSL Socket, пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SSL]);

Co\run(function () {
    $host = 'host.domain.tld';
    $port = 1234;
    $timeout = 10;
    $cert = '/path/to/your/certchain/certchain.pem';
    $context = stream_context_create(
        array(
            'ssl' => array(
                'local_cert' => $cert,
            )
        )
    );
    if ($fp = stream_socket_client(
        'ssl://' . $host . ':' . $port,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    )) {
        echo "connected\n";
    } else {
        echo "ERROR: $errno - $errstr \n";
    }
});
```

### SWOOLE_HOOK_TLS

Начиная с `v4.2`, поддерживается Stream типа TLS Socket, [с参考](https://www.php.net/manual/en/context.ssl.php).

Пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```

### SWOOLE_HOOK_SLEEP

Начиная с `v4.2`, поддерживается `Hook` функции `sleep`, включая `sleep`, `usleep`, `time_nanosleep`, `time_sleep_until`, поскольку минимальнаяGranularity таймера подложного планировщика составляет `1ms`, при использовании функций высокой точности сна, таких как `usleep`, если установлен値 ниже `1ms`, будет использоваться системный вызов `sleep`. Это может привести к очень короткому блокированию сна. Пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SLEEP]);

Co\run(function () {
    go(function () {
        sleep(1);
        echo '1' . PHP_EOL;
    });
    go(function () {
        echo '2' . PHP_EOL;
    });
});
//вывод
2
1
```

### SWOOLE_HOOK_FILE

Начиная с `v4.3`, поддерживается.

* **Корoutine-обработка операций с Files, поддерживаемые функции:**

    * `fopen`
    * `fread`/`fgets`
    * `fwrite`/`fputs`
    * `file_get_contents`, `file_put_contents`
    * `unlink`
    * `mkdir`
    * `rmdir`

Пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```

### SWOOLE_HOOK_STREAM_FUNCTION

Начиная с `v4.4`, поддерживается `Hook` функции `stream_select()`, пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_STREAM_FUNCTION]);

Co\run(function () {
    $fp1 = stream_socket_client("tcp://www.baidu.com:80", $errno, $errstr, 30);
    $fp2 = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
    if (!$fp1) {
        echo "$errstr ($errno) \n";
    } else {
        fwrite($fp1, "GET / HTTP/1.0\r\nHost: www.baidu.com\r\nUser-Agent: curl/7.58.0\r\nAccept: */*\r\n\r\n");
        $r_array = [$fp1, $fp2];
        $w_array = $e_array = null;
        $n = stream_select($r_array, $w_array, $e_array, 10);
        $html = '';
        while (!feof($fp1)) {
            $html .= fgets($fp1, 1024);
        }
        fclose($fp1);
    }
});
```

### SWOOLE_HOOK_BLOCKING_FUNCTION

Начиная с `v4.4`, поддерживается. Здесь `blocking function` включает: `gethostbyname`, `exec`, `shell_exec`, пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```

### SWOOLE_HOOK_PROC

Начиная с `v4.4`, поддерживается корoutine-обработка функций `proc*`, включая: `proc_open`, `proc_close`, `proc_get_status`, `proc_terminate`.

Пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PROC]);

Co\run(function () {
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin, child process read from it
        1 => array("pipe", "w"),  // stdout, child process write to it
    );
    $process = proc_open('php', $descriptorspec, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], 'I am process');
        fclose($pipes[0]);

        while (true) {
            echo fread($pipes[1], 1024);
        }

        fclose($pipes[1]);
        $return_value = proc_close($process);
        echo "command returned $return_value" . PHP_EOL;
    }
});
```

### SWOOLE_HOOK_CURL

[Beginnning с v4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) или `v4.5` для официальной поддержки.

* **HOOK для cURL, поддерживаемые функции:**

     * curl_init
     * curl_setopt
     * curl_exec
     * curl_multi_getcontent
     * curl_setopt_array
     * curl_error
     * curl_getinfo
     * curl_errno
     * curl_close
     * curl_reset

Пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_CURL]);

Co\run(function () {
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, "http://www.xinhuanet.com/");  
    curl_setopt($ch, CURLOPT_HEADER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);  
    curl_close($ch);
    var_dump($result);
});
```

### SWOOLE_HOOK_NATIVE_CURL

Корoutine-обработка для оригинального cURL.

!> Версия Swoole >= `v4.6.0` доступна

!> Перед использованием необходимо включить опцию `--enable-swoole-curl` при сборке;  
При включении этой опции автоматически будет установлены `SWOOLE_HOOK_NATIVE_CURL`, `SWOOLE_HOOK_CURL` будет отключен;  
Кроме того, `SWOOLE_HOOK_ALL` включает в себя `SWOOLE_HOOK_NATIVE_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_NATIVE_CURL]);

Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_NATIVE_CURL]);
```

Пример:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

Co\run(function () {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://httpbin.org/get");
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    var_dump($result);
});
```

### SWOOLE_HOOK_SOCKETS

Корoutine-обработка для расширений сокетов.

!> Версия Swoole >= `v4.6.0` доступна

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SOCKETS]);
```

### SWOOLE_HOOK_STDIO

Корoutine-обработка для STDIO.

!> Версия Swoole >= `v4.6.2` доступна

```php
use Swoole\Process;
Co::set(['socket_read_timeout' => -1, 'hook_flags' => SWOOLE_HOOK_STDIO]);
$proc = new Process(function ($p) {
    Co\run(function () use($p) {
        $p->write('start'.PHP_EOL);
        go(function() {
            co::sleep(0.05);
            echo "sleep\n";
        });
        echo fread(STDIN, 1024);
    });
}, true, SOCK_STREAM);
$proc->start();
echo $proc->read();
usleep(100000);
$proc->write('hello world'.PHP_EOL);
echo $proc->read();
echo $proc->read();
Process::wait();
```
### SWOOLE_HOOK_PDO_PGSQL

Обработка `pdo_pgsql` с использованием `координации`.

!> Версия Swoole >= `v5.1.0` требуется

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

Пример:
```php
<?php
function test()
{
    $dbname   = "test";
    $username = "test";
    $password = "test";
    try {
        $dbh = new PDO("pgsql:dbname=$dbname;host=127.0.0.1:5432", $username, $password);
        $dbh->exec('create table test (id int)');
        $dbh->exec('insert into test values(1)');
        $dbh->exec('insert into test values(2)');
        $res = $dbh->query("select * from test");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['trace_flags' => SWOOLE_HOOK_PDO_PGSQL]);

Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_ODBC

Обработка `pdo_odbc` с использованием `координации`.

!> Версия Swoole >= `v5.1.0` требуется

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

Пример:
```php
<?php
function test()
{
    $username = "test";
    $password = "test";
    try {
        $dbh = new PDO("odbc:mysql-test");
        $res = $dbh->query("select sleep(1) s");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['trace_flags' => SWOOLE_TRACE_CO_ODBC, 'log_level' => SWOOLE_LOG_DEBUG]);

Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_ORACLE

Обработка `pdo_oci` с использованием `координации`.

!> Версия Swoole >= `v5.1.0` требуется

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

Пример:
```php
<?php
function test()
{
	$tsn = 'oci:dbname=127.0.0.1:1521/xe;charset=AL32UTF8';
	$username = "test";
	$password = "test";
    try {
        $dbh = new PDO($tsn, $username, $password);
        $dbh->exec('create table test (id int)');
        $dbh->exec('insert into test values(1)');
        $dbh->exec('insert into test values(2)');
        $res = $dbh->query("select * from test");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_SQLITE
Обработка `pdo_sqlite` с использованием `координации`.

!> Версия Swoole >= `v5.1.0` требуется

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_SQLITE]);
```

* **Примечание**

!> Поскольку `swoole` при координации `sqlite` базы данных использует `серийный` режим для обеспечения [безопасности в многопроцессном окружении](https://www.sqlite.org/threadsafe.html).  
Если при сборке `sqlite` базы данных был установлен режим многопроцессного线程ирования в один процесс, то `swoole` не сможет координировать `sqlite` и выбросит предупреждение, но это не повлияет на использование, в процессе добавления, удаления и изменения данных не будет происходить переключение между координациями. В таких случаях необходимо пересобрать `sqlite` с указанием режима线程ирования в `серийный` или `многопроцессный`, [по причине](https://www.sqlite.org/compile.html#threadsafe).     
Все соединения к `sqlite`, созданные в координационном окружении, являются `серийными`, а соединения к `sqlite`, созданные в некоординационном окружении, по умолчанию соответствуют режиму线程ирования `sqlite`.   
Если режим线程ирования `sqlite` установлен как `многопроцессный`, то соединения, созданные в некоординационном окружении, не могут быть поделены между несколькими координациями, поскольку в это время подключения к базе данных работают в режиме многопроцессного线程ирования, их использование в координационном окружении также не приведет к изменению на `серийный`.   
По умолчанию режим线程ирования `sqlite` установлен как `серийный`, [по описанию серийного режима](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized), [по умолчанию режим线程ирования](https://www.sqlite.org/compile.html#threadsafe).      

Пример:
```php
<?php
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

Co::set(['hook_flags'=> SWOOLE_HOOK_PDO_SQLITE]);

run(function() {
    for($i = 0; $i <= 5; $i++) {
        go(function() use ($i) {
            $db = new PDO('sqlite::memory:');
            $db->query('select randomblob(99999999)');
            var_dump($i);
        });
    }
});
```

## Методы


### setHookFlags()

Установка диапазона функций для `Hook` с помощью `flags`

!> Версия Swoole >= `v4.5.0` требуется

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```


### getHookFlags()

Получение текущих `flags` для `Hook` контента, которые могут отличаться от `flags`, переданных при включении `Hook` (так как `flags`, которые не удалось `Hook`, будут удалены)

!> Версия Swoole >= `v4.4.12` требуется

```php
Swoole\Runtime::getHookFlags(): int
```


## Обычный список Hooks


### Доступные списки

  * Расширение `redis`
  * Использование расширений `pdo_mysql` и `mysqli` в режиме `mysqlnd`, если `mysqlnd` не включен, поддержка координации не будет
  * Расширение `soap`
  * `file_get_contents`, `fopen`
  * `stream_socket_client` (`predis`, `php-amqplib`)
  * `stream_socket_server`
  * `stream_select` (требуется версия `4.3.2` и выше)
  * `fsockopen`
  * `proc_open` (требуется версия `4.4.0` и выше)
  * `curl`


### Непригодные списки

!> **Не поддерживается координация** означает, что координации будет снижена до блокирующего режима, в этом случае использование координации не имеет смысла

  * `mysql`：в качестве подложки используется `libmysqlclient`
  * `mongo`：в качестве подложки используется `mongo-c-client`
  * `pdo_pgsql`， после версии Swoole >= `v5.1.0` можно использовать `pdo_pgsql` для координации обработки
  * `pdo_oci`， после версии Swoole >= `v5.1.0` можно использовать `pdo_oci` для координации обработки
  * `pdo_odbc`， после версии Swoole >= `v5.1.0` можно использовать `pdo_odbc` для координации обработки
  * `pdo_firebird`
  * `php-amqp`


## Изменения в API

В версиях `v4.3` и ранее, функция `enableCoroutine` требует двух параметров.

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```


- `$enable`：включение или отключение координации.
- `$flags`：выбор типов для `координации`, можно выбирать несколько, по умолчанию выбираются все. Эффективно только при `$enable = true`.

!> `Runtime::enableCoroutine(false)` отключает все ранее установленные опции координации `Hook`.
