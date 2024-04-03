# Runtime

Compared to `Swoole 1.x`, `Swoole 4+` introduces the coroutine feature, making all business logic synchronous while the underlying I/O operations are asynchronous. This ensures concurrency while avoiding the scattered code logic and code maintenance challenges that traditional asynchronous callbacks bring. To achieve this effect, all `IO` requests must be [asynchronous IO](/learn?id=sync-io-async-io). In the `Swoole 1.x` era, clients such as `MySQL` and `Redis` provided asynchronous IO, but they used asynchronous callback programming instead of coroutine programming. Therefore, in the `Swoole 4` era, these clients were removed.

To address the lack of coroutine support for these clients, the Swoole development team did a lot of work:

- Initially, a coroutine client was created for each type of client, as detailed in [Coroutine Client](/coroutine_client/init). However, this approach had three issues:

  * Complex implementation: Each client's specific protocol is complex, making it challenging to support all scenarios perfectly.
  * Users needed to make significant code changes. For example, if the original `MySQL` queries were using PHP's native `PDO`, now they needed to use the methods of [Swoole\Coroutine\MySQL](/coroutine_client/mysql).
  * It was difficult to cover all operations; functions like `proc_open()` and `sleep()` could block and turn the program into a synchronous, blocking one.

- To tackle the above problems, the Swoole development team changed their implementation approach and adopted the `Hook` method to turn PHP native functions into coroutine clients. With just one line of code, synchronous IO code could be transformed into [asynchronous IO](/learn?id=sync-io-async-io) that can be scheduled using coroutines – a concept known as `One-Click Coroutine`.

!> This feature became stable starting from version `v4.3`. As the number of functions that can be made coroutine-aware continues to grow, some coroutine clients previously created may no longer be recommended. For more details, refer to the [Coroutine Client](/coroutine_client/init). For example, in `v4.3+`, file operations (`file_get_contents`, `fread`, etc.) were made coroutine-friendly. If you are using version `v4.3+`, you can directly use coroutine operations instead of using Swoole's provided [coroutine file operations](/coroutine/system).
## Function Prototype

Set the range of functions to be `coroutinized` through `flags`.

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // Use this method in v4.4 and later versions.
// Or
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

To enable multiple `flags` at the same time, use the `|` operator.

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

!> Functions that are `Hook`ed need to be used in the [Coroutine container](/coroutine/scheduler).
#### Frequently Asked Questions :id=runtime-qa

!> **Which one to use between `Swoole\Runtime::enableCoroutine()` and `Co::set(['hook_flags'])`**

* `Swoole\Runtime::enableCoroutine()` can dynamically set flags after the service starts (at runtime). Once called, it will take effect globally in the current process and should be placed at the beginning of the project to achieve 100% coverage.
* `Co::set()` can be understood as PHP's `ini_set()`. It needs to be called before [Server->start()](/server/methods?id=start) or [Co\run()](/coroutine/scheduler) to take effect. In versions `v4.4+`, this method should be used to set flags.
* Regardless of using `Co::set(['hook_flags'])` or `Swoole\Runtime::enableCoroutine()`, it should only be called once. Repeated calls will be overridden.
## Options

The options supported by `flags` are:
### SWOOLE_HOOK_ALL

Open all types of flags below (excluding CURL)

!> Starting from version 4.5.4, `SWOOLE_HOOK_ALL` includes `SWOOLE_HOOK_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); // excluding CURL
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); // truly coroutine all types, including CURL
```
### SWOOLE_HOOK_TCP

Supported since `v4.1`, the TCP Socket type stream, including the most common ones like `Redis`, `PDO`, `Mysqli`, and operations on TCP connections using PHP's [streams](https://www.php.net/streams) series functions can all be `Hooked`. Example:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//creating 100 coroutines
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//at this point coroutine scheduling occurs, CPU switches to the next coroutine, does not block the process
            $redis->get('key');//at this point coroutine scheduling occurs, CPU switches to the next coroutine, does not block the process
        });
    }
});
```

The above code uses the native `Redis` class, but it has actually become `asynchronous IO`. `Co\run()` creates a [coroutine container](/coroutine/scheduler), and `go()` creates a coroutine. These two operations are automatically performed in the [Swoole\Server class family](/server/init) provided by Swoole, and do not need to be done manually, refer to [enable_coroutine](/server/setting?id=enable_coroutine).

In other words, traditional `PHP` programmers can write high-concurrency, high-performance programs using the most familiar logic code, as shown below:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);//at this point coroutine scheduling occurs, CPU switches to the next coroutine (next request), does not block the process
      $redis->get('key');//at this point coroutine scheduling occurs, CPU switches to the next coroutine (next request), does not block the process
});

$http->start();
```
### SWOOLE_HOOK_UNIX

Supported since `v4.2`. Example of stream for `Unix Stream Socket` type:

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

Introduced since `v4.2`. Supports stream type of UDP Socket, for example:

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

Supported since `v4.2`. Stream for Unix Dgram Socket type, example:

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

Support since `v4.2`. Example of stream with SSL Socket type:

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

Support was added in `v4.2`. For streams of TLS Socket type, refer to [this documentation](https://www.php.net/manual/en/context.ssl.php).

Example:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```
### SWOOLE_HOOK_SLEEP

Supported since `v4.2`. `Hook` for the `sleep` function, including `sleep`, `usleep`, `time_nanosleep`, `time_sleep_until`. Since the minimum granularity of the underlying timer is `1ms`, when using high-precision sleep functions such as `usleep`, if set to less than `1ms`, it will directly use the `sleep` system call. This may cause very brief sleep blocking. Example:

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
// Output
2
1
```
### SWOOLE_HOOK_FILE

Supported since `v4.3`.

* **Coroutine handling for file operations, functions supported include:**

    * `fopen`
    * `fread`/`fgets`
    * `fwrite`/`fputs`
    * `file_get_contents`, `file_put_contents`
    * `unlink`
    * `mkdir`
    * `rmdir`

Example:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```
### SWOOLE_HOOK_STREAM_FUNCTION

Introduced since version `v4.4`. `Hook` for `stream_select()`, example:

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

Support started from `v4.4`. The `blocking function` here includes: `gethostbyname`, `exec`, `shell_exec`, for example:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```
### SWOOLE_HOOK_PROC

Supported since `v4.4`. Coroutine `proc*` functions, including: `proc_open`, `proc_close`, `proc_get_status`, `proc_terminate`.

Example:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PROC]);

Co\run(function () {
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin, child process reads from it
        1 => array("pipe", "w"),  // stdout, child process writes to it
    );
    $process = proc_open('php', $descriptorspec, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], '<?php echo "I am process\n" ?>');
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

Supported since [v4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) or officially starting from `v4.5`.

* **CURL hooks support the following functions:**

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

Example:

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

`Coroutine handling` for native CURL.

!> Available for Swoole version >= `v4.6.0`

!> Before using, you need to enable the [--enable-swoole-curl](/environment?id=common-parameters) option at compile time;  
Enabling this option will automatically set `SWOOLE_HOOK_NATIVE_CURL` and disable [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_all);  
At the same time, `SWOOLE_HOOK_ALL` includes `SWOOLE_HOOK_NATIVE_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_NATIVE_CURL]);

Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_NATIVE_CURL]);
```

Example:

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

`Coroutine processing` for the sockets extension.

!> Available in Swoole version >= `v4.6.0`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SOCKETS]);
```
### SWOOLE_HOOK_STDIO

Coroutine processing for STDIO.

!> Available for Swoole version >= `v4.6.2`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_STDIO]);
```

Example:

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

Cooperative processing for `pdo_pgsql`.

!> Available in Swoole version >= `v5.1.0`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

Example:
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

Handling `pdo_odbc` in co-routines.

!> Swoole version >= `v5.1.0` is required.

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

Example:
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

Coroutine processing for `pdo_oci`.

!> Available in Swoole version >= `v5.1.0`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

Example:
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
Coroutines handling for `pdo_sqlite`.

!> Available in Swoole version >= `v5.1.0`.

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_SQLITE]);
```

* **Note**

!> When `swoole` coroutine-izes the `sqlite` database, it uses a `serialization` mode to ensure [thread safety](https://www.sqlite.org/threadsafe.html).  
If the `sqlite` database is compiled in single-thread mode, `swoole` cannot coroutine-ize `sqlite` and will throw a warning. However, this does not affect usage; it just means that coroutine switching will not occur during insert, delete, or update operations. In this situation, you must recompile `sqlite` and specify the thread mode as `serialization` or `multi-threaded`, [reason](https://www.sqlite.org/compile.html#threadsafe).  
In a coroutine environment, all `sqlite` connections created are `serialized`, while in a non-coroutine environment, `sqlite` connections are created with the default thread mode of `sqlite`.  
If the `sqlite` thread mode is `multi-threaded`, then connections created in a non-coroutine environment cannot be shared by multiple coroutines because the database connection is in `multi-threaded mode`, which will not be upgraded to `serialization` in a coroutine environment.  
The default thread mode for `sqlite` is `serialization`, [serialization explanation](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized), [default thread mode](https://www.sqlite.org/compile.html#threadsafe).  

Example:
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
## Methods
### setHookFlags()

Set the scope of functions to be hooked through `flags`.

!> Available for Swoole version >= `v4.5.0`

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```
### getHookFlags()

Get the current `flags` of the `Hook` content, which may not be consistent with the `flags` passed in when enabling the `Hook` (because the `flags` that were not successfully `Hook`ed will be cleared)

!> Available in Swoole version >= `v4.4.12`

```php
Swoole\Runtime::getHookFlags(): int
```
## Common Hook List
### Available List

  * `redis` extension
  * Use `pdo_mysql`, `mysqli` extensions with `mysqlnd` mode. Coroutine support will not be available if `mysqlnd` is not enabled
  * `soap` extension
  * `file_get_contents`, `fopen`
  * `stream_socket_client` (`predis`, `php-amqplib`)
  * `stream_socket_server`
  * `stream_select` (requires version `4.3.2` or above)
  * `fsockopen`
  * `proc_open` (requires version `4.4.0` or above)
  * `curl`
### Unavailable List

!> **Does not support coroutines** means that coroutines will degrade to blocking mode, in which case using coroutines has no practical meaning.

  * `mysql`: underlying library `libmysqlclient` used
  * `mongo`: underlying library `mongo-c-client` used
  * `pdo_pgsql`: starting from Swoole version `v5.1.0`, `pdo_pgsql` can be used for coroutine processing
  * `pdo_oci`: starting from Swoole version `v5.1.0`, `pdo_oci` can be used for coroutine processing
  * `pdo_odbc`: starting from Swoole version `v5.1.0`, `pdo_odbc` can be used for coroutine processing
  * `pdo_firebird`
  * `php-amqp`
## API Changes

In version `v4.3` and earlier, the `enableCoroutine` API requires 2 parameters.

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```

- `$enable`: turn coroutine on or off.
- `$flags`: choose the type(s) to be coroutine, multiple selections are allowed, default to select all. Only effective when `$enable = true`.

!> `Runtime::enableCoroutine(false)` disables all the coroutine `Hook` settings that were set previously.
