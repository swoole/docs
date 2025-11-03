# Runtime

相对于`Swoole1.x`，`Swoole4+`提供了协程这个大杀器，所有业务代码都是同步的，但底层的`IO`却是异步的，保证并发的同时避免了传统异步回调所带来的离散的代码逻辑和陷入多层回调中导致代码无法维护，要达到这个效果必须所有的`IO`请求都是[异步IO](/learn?id=同步io异步io)，而`Swoole1.x`时代提供的`MySQL`、`Redis`等客户端虽然是异步IO，但是是异步回调的编程方式，不是协程方式，所以在`Swoole4`时代移除了这些客户端。

为了解决这些客户端的协程支持问题Swoole开发组做了大量的工作：

- 刚开始，针对每种类型的客户端都做了一个协程客户端，详见[协程客户端](/coroutine_client/init)，但这样做有3个问题：

  * 实现复杂，每个客户端细枝末节的协议都很复杂，想都完美的支持工作量巨大。
  * 用户需要更改的代码比较多，比如原来查询`MySQL`是用的PHP原生的`PDO`，那么现在需要用[Swoole\Coroutine\MySQL](/coroutine_client/mysql)的方法。
  * 很难覆盖到所有的操作，比如`proc_open()`、`sleep()`函数等等也可能阻塞住导致程序变成同步阻塞的。

- 针对上述问题，Swoole开发组换了实现思路，采用`Hook`原生PHP函数的方式实现协程客户端，通过一行代码就可以让原来的同步IO的代码变成可以[协程调度](/coroutine?id=协程调度)的[异步IO](/learn?id=同步io异步io)，即`一键协程化`。

!> 此特性在`v4.3`版本后开始稳定，能`协程化`的函数也越来越多，所以有些之前写的协程客户端已经不再推荐使用了，详情查看[协程客户端](/coroutine_client/init)，例如：在`v4.3+`支持了文件操作(`file_get_contents`、`fread`等)的`协程化`，如果使用的是`v4.3+`版本就可以直接使用`协程化`而不是使用Swoole提供的[协程文件操作](/coroutine/system)了。

## 函数原型

通过`flags`设置要`协程化`的函数的范围

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // v4.4+版本使用此方法。
// 或
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

同时开启多个`flags`需要使用`|`操作

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

!> 被`Hook`的函数需要在[协程容器](/coroutine/scheduler)中使用

#### 常见问题 :id=runtime-qa

!> **`Swoole\Runtime::enableCoroutine()` 和 `Co::set(['hook_flags'])`用哪个**
  
* `Swoole\Runtime::enableCoroutine()`，可以在服务启动后(运行时)动态设置flags，调用方法后当前进程内全局生效，应该放在整个项目开始以获得100%覆盖的效果；
* `Co::set()`可以理解为PHP的`ini_set()`，需要在[Server->start()](/server/methods?id=start)前或[Co\run()](/coroutine/scheduler)前调用，否则设置的`hook_flags`不会生效，在`v4.4+`版本应该用此种方式设置`flags`；
* 无论是`Co::set(['hook_flags'])`还是`Swoole\Runtime::enableCoroutine()`都应该只调用一次，重复调用会被覆盖。

## 选项

`flags`支持的选项有：

### SWOOLE_HOOK_ALL

打开下述所有类型的flags (不包括CURL)

!> 从 v4.5.4 版本起，`SWOOLE_HOOK_ALL` 包括 `SWOOLE_HOOK_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); //不包括CURL
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); //真正的协程化所有类型，包括CURL
```

### SWOOLE_HOOK_TCP

`v4.1`开始支持，TCP Socket类型的stream，包括最常见的`Redis`、`PDO`、`Mysqli`以及用PHP的 [streams](https://www.php.net/streams) 系列函数操作TCP连接的操作，都可以`Hook`，示例：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//创建100个协程
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//此处产生协程调度，cpu切到下一个协程，不会阻塞进程
            $redis->get('key');//此处产生协程调度，cpu切到下一个协程，不会阻塞进程
        });
    }
});
```

上述代码使用的就是原生的`Redis`类，但是其实已经变成了`异步IO`，`Co\run()`是创建了[协程容器](/coroutine/scheduler)，`go()`是创建协程，这两个操作在`Swoole`提供的[Swoole\Server类簇](/server/init)都是自动做好的，不需要手动做，参考[enable_coroutine](/server/setting?id=enable_coroutine)。

也就是说传统的`PHP`程序员用最熟悉的逻辑代码就能写出高并发、高性能的程序，如下：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
      $redis->get('key');//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
});

$http->start();
```

### SWOOLE_HOOK_UNIX

`v4.2`开始支持。`Unix Stream Socket`类型的stream，示例：

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

`v4.2`开始支持。UDP Socket类型的stream，示例：

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

`v4.2`开始支持。Unix Dgram Socket类型的stream，示例：

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

`v4.2`开始支持。SSL Socket类型的stream，示例：

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

`v4.2`开始支持。`TLS Socket`类型的`stream`，[参考](https://www.php.net/manual/en/context.ssl.php)。

示例：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```

### SWOOLE_HOOK_SLEEP

`v4.2`开始支持。`sleep`函数的`Hook`，包括了`sleep`、`usleep`、`time_nanosleep`、`time_sleep_until`，由于底层的定时器最小粒度是`1ms`，因此使用`usleep`等高精度睡眠函数时，如果设置为低于`1ms`时，将直接使用`sleep`系统调用。可能会引起非常短暂的睡眠阻塞。示例：

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
```
输出
```
2
1
```

### SWOOLE_HOOK_FILE

`v4.3`开始支持。

#### 文件操作的`协程化处理`，支持的函数有：

* `fopen`
* `fread`/`fgets`
* `fwrite`/`fputs`
* `file_get_contents`、`file_put_contents`
* `unlink`
* `mkdir`
* `rmdir`

示例：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```

#### 关闭文件 `HOOK`

由于开启文件`HOOK`后，所有文件操作都会变成异步非阻塞的，包括`autoload`和`include`等操作，
如果这些操作发生在协程调度点上，可能会引起不可预期的问题，这时可关闭文件`hook`的选项：

```php
# 开启除了文件 HOOK 之外的所有协程 HOOK 选项
Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL & ~SWOOLE_HOOK_FILE);
```

在关闭文件`HOOK`后，所有文件操作将变成同步阻塞的，不会发生协程切换，可安全地用于`autoload`、`include`或其他`PHP`源代码分析处理的逻辑。
在关闭文件`HOOK`的状态下，可以用下面的方式来实现异步文件读写：
- `System::readFile()`：异步读取文件内容
- `System::writeFile()`：异步写入文件内容

在`6.1`版本之后，还可以使用`fopen('async.file://path/to/file', 'rw')`打开异步文件流。
```php
Co\run(function () {
    # 这是一个异步文件流，所有对此资源的读写操作都是异步非阻塞的
    $fp = fopen("async.file:///tmp/test.txt", "w+");
    fwrite($fp, "Hello World\n");
    fdatasync($fp);
    fclose($fp);
});
```

#### 文件锁
在并发读写文件时，需要使用文件锁来保证数据一致性，在调用`flock()`会产生协程调度，示例：

```php
Co\run(function () {
    $fp = fopen("/tmp/test.txt", "w+");
    flock($fp, LOCK_EX);
    fwrite($fp, "Hello World\n");
    flock($fp, LOCK_UN);
    fclose($fp);
});
```

`flock()`支持`LOCK_SH`、`LOCK_EX`、`LOCK_UN`三种锁类型。只读操作时使用`LOCK_SH`，读写操作时使用`LOCK_EX`，释放锁时使用`LOCK_UN`。

请注意`flock()`不受`SWOOLE_HOOK_FILE`选项控制，而是`SWOOLE_HOOK_STDIO`。因此关闭`SWOOLE_HOOK_FILE`后，`flock()`仍然是协程化的。
如果需要关闭`flock()`的协程化，可以关闭`SWOOLE_HOOK_STDIO`选项。

```php
Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL & ~SWOOLE_HOOK_FILE & ~SWOOLE_HOOK_STDIO);
```

### SWOOLE_HOOK_STREAM_FUNCTION

`v4.4`开始支持。`stream_select()`的`Hook`，示例：

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

`v4.4`开始支持。这里的`blocking function`包括了：`gethostbyname`、`exec`、`shell_exec`，示例：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```

### SWOOLE_HOOK_PROC

`v4.4`开始支持。协程化 `proc*` 函数，包括了：`proc_open`、`proc_close`、`proc_get_status`、`proc_terminate`。

示例：

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

[v4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x)后或`v4.5`开始正式支持。

#### 支持的`CURL`函数包括：

* `curl_init`
* `curl_setopt`
* `curl_exec`
* `curl_multi_getcontent`
* `curl_setopt_array`
* `curl_error`
* `curl_getinfo`
* `curl_errno`
* `curl_close`
* `curl_reset`

示例：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_CURL]);

Co\run(function () {
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, "https://www.xinhuanet.com/");  
    curl_setopt($ch, CURLOPT_HEADER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);  
    curl_close($ch);
    var_dump($result);
});
```

### SWOOLE_HOOK_NATIVE_CURL

对`CURL`的`协程化处理`，与`SWOOLE_HOOK_CURL`不同的是，`SWOOLE_HOOK_NATIVE_CURL`基于`libcurl`库实现，支持所有`CURL`功能。

- 使用前需要在编译时开启 [--enable-swoole-curl](/environment?id=通用参数) 选项
- 此选项与 [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_all) 为互斥关系，不能同时开启
- 在使用`SWOOLE_HOOK_ALL`选项时，优先使用`SWOOLE_HOOK_NATIVE_CURL`

!> `Swoole`版本 >= `v4.6.0` 可用

```php
Co::set(['hook_flags' => SWOOLE_HOOK_NATIVE_CURL]);

Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_NATIVE_CURL]);
```

示例：

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

对 `sockets` 扩展的`协程化处理`。

!> `Swoole`版本 >= `v4.6.0` 可用

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SOCKETS]);
```

### SWOOLE_HOOK_STDIO

对 `STDIO` 的`协程化处理`。

!> Swoole版本 >= `v4.6.2` 可用

```php
Co::set(['hook_flags' => SWOOLE_HOOK_STDIO]);
```

示例：

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

对 `pdo_pgsql` 的`协程化处理`。

!> `Swoole`版本 >= `v5.1.0` 可用

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

示例：
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

对 `pdo_odbc` 的`协程化处理`。

!> `Swoole`版本 >= `v5.1.0` 可用

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

示例：
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

对 `pdo_oci` 的`协程化处理`。

!> `Swoole`版本 >= `v5.1.0` 可用

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

示例：
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
对 `pdo_sqlite` 的`协程化处理`。

!> `Swoole`版本 >= `v5.1.0` 可用

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_SQLITE]);
```

* **注意**

!> 由于`swoole`在协程化`sqlite`数据库的时候，采用的是`串行化`模式保证[线程安全](https://www.sqlite.org/threadsafe.html)。  
如果`sqlite`数据库编译时指定的线程模式是单线程模式的话，`swoole`无法协程化`sqlite`，并且抛出一个警告，但是不影响使用，只是在增删改查的过程中不会发生协程切换。这种情况下只能重新编译`sqlite`并且指定线程模式为`串行化`或者`多线程`，[原因](https://www.sqlite.org/compile.html#threadsafe)。     
协程环境中创建的`sqlite`连接全部是`串行化的`，非协程环境中创建的`sqlite`连接默认与`sqlite`的线程模式一致。   
如果`sqlite`的线程模式是`多线程`，那么非协程环境下创建的连接是不能给多个协程共享的，因为此时数据库连接是`多线程模式`的，在协程化环境下使用也不会升级成`串行化`。   
`sqlite`默认线程模式就是`串行化`，[串行化说明](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized)，[默认线程模式](https://www.sqlite.org/compile.html#threadsafe)。      

示例：
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

## 方法

### setHookFlags()

通过`flags`设置要`Hook`的函数的范围

!> `Swoole`版本 >= `v4.5.0` 可用

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```

### getHookFlags()

获取当前已`Hook`内容的`flags`，可能会与开启`Hook`时传入的`flags`不一致（由于未`Hook`成功的`flags`将会被清除）

!> `Swoole`版本 >= `v4.4.12` 可用

```php
Swoole\Runtime::getHookFlags(): int
```

## 常见的Hook列表

### 可用列表

  * `redis`扩展
  * `mysqli`扩展、`pdo_mysql`扩展 （需启用`mysqlnd`）
  * `curl`扩展
  * `file_get_contents`、`fopen`
  * `stream_socket_client` (`predis`、`php-amqplib`)
  * `stream_socket_server`
  * `stream_select` (需要`4.3.2`以上版本)
  * `fsockopen`
  * `proc_open` (需要`4.4.0`以上版本)
  * `soap`扩展
  * `pdo_pgsql` (需要`v5.1.0`以上版本)
  * `pdo_oci` (需要`v5.1.0`以上版本)
  * `pdo_odbc` (需要`v5.1.0`以上版本)

### 不可用列表

!> **不支持协程化**表示会使协程降级为阻塞模式，此时使用协程无实际意义

  * `mysql`扩展：底层使用`libmysqlclient`
  * `mongodb`扩展：底层使用`mongo-c-client`
  * `pdo_firebird`，底层使用`firebird` `C` 客户端库，仅支持同步阻塞`IO`
  * `php-amqp`，底层使用`librabbitmq`，仅支持同步阻塞`IO`
  * `ftp`，底层使用了`poll()`等待`Socket`，不支持协程化

## API变更

`v4.3`及以前版本，`Runtime::enableCoroutine()`的 `API` 需要`2`个参数

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```

- `$enable`：打开或关闭协程化。
- `$flags`：选择要`协程化`的类型，可以多选，默认为全选。仅在`$enable = true`时有效。

!> `Runtime::enableCoroutine(false)`关闭上一次设置的所有选项协程`Hook`设置。

`v4.4`版本后，`Runtime::enableCoroutine()`的 `API` 变更为只需要`1`个参数

```php
Swoole\Runtime::enableCoroutine(int $flags = SWOOLE_HOOK_ALL);
```
- 变更：移除了`$enable`参数，`$flags`为`0`时表示关闭所有协程`Hook`设置。
