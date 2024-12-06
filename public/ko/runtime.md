# 런타임

`Swoole1.x`에 비해 `Swoole4+`는 코루outine라는 강력한 도구를 제공합니다. 모든 비즈니스 코드는 동기적이지만, 기본적인 IO는 비동기적입니다. 이는 병렬을 보장하면서 전통적인 비동기 콜백이 가져오는 분산된 코드 논리와多层 콜백에 빠져 코드를 유지하기 어려운 문제를 피합니다. 이러한 효과를 달성하기 위해서는 모든 `IO` 요청이 [비동기 IO](/learn?id=同步io异步io)여야 하며, `Swoole1.x` 시대에 제공된 `MySQL`、`Redis` 등의 클라이언트는 비동기 IO였지만 비동기 콜백 방식이었고, 코루outine 방식이 아니었기 때문에 `Swoole4` 시대에는 이러한 클라이언트가 제거되었습니다.

이러한 클라이언트의 코루outine 지원 문제를 해결하기 위해 Swoole 개발팀은 많은 일을 했습니다:

- 처음에는 각 유형의 클라이언트에 대한 코루outine 클라이언트를 하나씩 만들었습니다. 자세한 내용은 [코루outine 클라이언트](/coroutine_client/init)를 보세요. 하지만 이렇게 하는 데는 3가지 문제가 있었습니다:

  * 구현이 복잡합니다. 각 클라이언트의 세부 프로토콜은 매우 복잡하고 완벽하게 지원하려면 엄청난 작업량이 필요합니다.
  * 사용자가 변경해야 할 코드가 많습니다. 예를 들어, 이전에 `MySQL`查询는 PHP 원래의 `PDO`를 사용했는데, 이제는 [Swoole\Coroutine\MySQL](/coroutine_client/mysql)의 방법을 사용해야 합니다.
  * 모든 운영을 커버하기 어렵습니다. 예를 들어, `proc_open()`、`sleep()` 함수 등이 막힐 수 있으며, 이로 인해 프로그램이 동기적으로 막힐 수 있습니다.


- 위의 문제들을 해결하기 위해 Swoole 개발팀은 구현 방식을 변경하고, 원래 PHP 함수를 `Hook`하는 방식으로 코루outine 클라이언트를 구현했습니다. 한 줄의 코드로 원래의 동기적 IO 코드를 [코루outine 스케줄링](/coroutine?id=协程调度)이 가능한 [비동기 IO](/learn?id=同步io异步io)로 바꿀 수 있습니다. 즉, '단일 클릭으로 코루outine화'가 가능합니다.

!> 이 기능은 `v4.3` 버전 이후부터 안정적으로 적용되었으며, '코루outine화'가 가능한 함수도 점점 더 많아지고 있습니다. 그래서 이전에 작성한 일부 코루outine 클라이언트는 더 이상 사용하지 않는 것이 좋습니다. 자세한 내용은 [코루outine 클라이언트](/coroutine_client/init)를 보세요. 예를 들어, `v4.3+`에서 파일操作(`file_get_contents`、`fread` 등)의 '코루outine화'가 지원되었으므로, `v4.3+` 버전을 사용하는 경우에는 Swoole가 제공하는 [코루outine 파일操作](/coroutine/system)를 사용하지 않고 바로 '코루outine화'를 사용할 수 있습니다.


## 함수 프로토타입

`flags`를 통해 '코루outine화'할 함수의 범위를 설정합니다

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // v4.4+ 버전에서 사용합니다.
// 또는
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

다중 `flags`를 동시에 사용하려면 `|` 연산자를 사용합니다

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

!> `Hook`된 함수는 [코루outine 컨테이너](/coroutine/scheduler)에서 사용해야 합니다.

#### 자주 묻는 질문 :id=runtime-qa

!> **`Swoole\Runtime::enableCoroutine()`와 `Co::set(['hook_flags'])` 중哪一个 사용해야 하나요**
  
* `Swoole\Runtime::enableCoroutine()`는 서비스를 시작한 후(런타임) 동적으로 flags를 설정할 수 있으며, 호출 방법을 한 번만 하면 현재 프로세스 내에서 전역에 효과가 적용됩니다. 전체 프로젝트가 시작되기 전에 설정하여 100%의 커버리지를 얻으려면 이 방법을 사용해야 합니다;
* `Co::set()`는 PHP의 `ini_set()`과 같아야 하며, [Server->start()](/server/methods?id=start) 전에 또는 [Co\run()](/coroutine/scheduler) 전에 호출해야 합니다. 그렇지 않으면 설정된 `hook_flags`는 효과가 적용되지 않습니다. `v4.4+` 버전에서는 이 방법으로 `flags`를 설정해야 합니다;
* `Co::set(['hook_flags'])`나 `Swoole\Runtime::enableCoroutine()`는 단 한 번만 호출해야 하며, 중복 호출은 덮여집니다.


## 옵션

`flags`가 지원하는 옵션은 다음과 같습니다:


### SWOOLE_HOOK_ALL

아래의 모든 유형의 flags (CURL 제외)를 켭니다

!> v4.5.4 버전부터, `SWOOLE_HOOK_ALL`은 `SWOOLE_HOOK_CURL`도 포함합니다

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); //CURL 제외
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); //정말로 모든 유형을 코루outine화, CURL 포함
```


### SWOOLE_HOOK_TCP

`v4.1`부터 지원됩니다. TCP 소켓 유형의 stream, 가장 흔한 `Redis`、`PDO`、`Mysqli` 및 PHP의 [streams](https://www.php.net/streams) 시리즈 함수로 TCP 연결을 조작하는 작업 모두 `Hook` 가능합니다. 예시:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//100개의 코루outine을 만듭니다
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//여기서 코루outine 스케줄링이 일어나며, CPU가 다음 코루outine로 이동하여 프로세스를 막지 않습니다
            $redis->get('key');//여기서 코루outine 스케줄링이 일어나며, CPU가 다음 코루outine로 이동하여 프로세스를 막지 않습니다
        });
    }
});
```

위의 코드는 원래의 `Redis` 클래스를 사용していますが, 실제로는 이미 '비동기 IO'가 되었습니다. `Co\run()`은 [코루outine 컨테이너](/coroutine/scheduler)를 만들고, `go()`는 코루outine을 만듭니다. 이 두 가지 작업은 Swoole가 제공하는 [Swoole\Server 클래스 군](/server/init)에서 자동으로 잘 되어 있으며, 수동으로 할 필요가 없습니다. 자세한 내용은 [enable_coroutine](/server/setting?id=enable_coroutine)를 참고하세요.

즉, 전통적인 PHP 프로그래머가 가장 익숙한 논리적 코드로 고성능, 고수익의 프로그램을 작성할 수 있습니다. 다음은 예시입니다:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);//여기서 코루outine 스케줄링이 일어나며, CPU가 다음 코루outine(다음 요청)로 이동하여 프로세스를 막지 않습니다
      $redis->get('key');//여기서 코루outine 스케줄링이 일어나며, CPU가 다음 코루outine(다음 요청)로 이동하여 프로세스를 막지 않습니다
});

$http->start();
```


### SWOOLE_HOOK_UNIX

`v4.2`부터 지원됩니다. `Unix Stream Socket` 유형의 stream, 예시:

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

`v4.2`부터 지원됩니다. UDP Socket 유형의 stream, 예시:

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

`v4.2`부터 지원됩니다. Unix Dgram Socket 유형의 stream, 예시:

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

`v4.2`부터 지원됩니다. SSL Socket 유형의 stream, 예시:

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

`v4.2`부터 지원됩니다. TLS Socket 유형의 stream, [참조](https://www.php.net/manual/en/context.ssl.php).

예시:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```


### SWOOLE_HOOK_SLEEP

`v4.2`부터 지원됩니다. `sleep` 함수의 `Hook`, 포함되는 함수는 `sleep`, `usleep`, `time_nanosleep`, `time_sleep_until`입니다.底层의 타이머 최소granularity이 `1ms`이기 때문에, `usleep`와 같이高精度의 수면 함수를 사용하고자 할 때, `1ms` 미만으로 설정하면 직접 `sleep` 시스템 호출을 사용하게 됩니다. 이는 매우 짧은 수면분산 차단을 유발할 수 있습니다. 예시:

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
//출력 
2
1
```


### SWOOLE_HOOK_FILE

`v4.3`부터 지원됩니다.

* **파일 조작의 `코루outine화 처리`에 지원되는 함수는 다음과 같습니다:**

    * `fopen`
    * `fread`/`fgets`
    * `fwrite`/`fputs`
    * `file_get_contents`、`file_put_contents`
    * `unlink`
    * `mkdir`
    * `rmdir`

예시:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```


### SWOOLE_HOOK_STREAM_FUNCTION

`v4.4`부터 지원됩니다. `stream_select()`의 `Hook`, 예시:

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

`v4.4`부터 지원됩니다. 여기의 `blocking function`에는 다음과 같은 함자가 포함됩니다: `gethostbyname`, `exec`, `shell_exec`, 예시:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```


### SWOOLE_HOOK_PROC

`v4.4`부터 지원됩니다. 코루outine화된 `proc*` 함수, 포함되는 함수는 `proc_open`, `proc_close`, `proc_get_status`, `proc_terminate`입니다.

예시:

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

[v4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 이후 또는 `v4.5`부터 정식으로 지원됩니다.

* **CURL의 HOOK, 지원되는 함수는 다음과 같습니다:**

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

예시:

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

원시 CURL의 `코루outine화 처리`입니다.

!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다

!> 사용 전에는 컴파일 시 [--enable-swoole-curl](/environment?id=通用参数) 옵션을 설정해야 합니다;  
해당 옵션을 설정하면 자동으로 `SWOOLE_HOOK_NATIVE_CURL`가 설정되고, [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_all)는 비활성화됩니다;  
또한 `SWOOLE_HOOK_ALL`는 `SWOOLE_HOOK_NATIVE_CURL`를 포함합니다

```php
Co::set(['hook_flags' => SWOOLE_HOOK_NATIVE_CURL]);

Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_NATIVE_CURL]);
```

예시:

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

sockets 확장의 `코루outine화 처리`입니다.

!> Swoole 버전 >= `v4.6.0`에서 사용할 수 있습니다

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SOCKETS]);
```


### SWOOLE_HOOK_STDIO

STDIO의 `코루outine화 처리`입니다.

!> Swoole 버전 >= `v4.6.2`에서 사용할 수 있습니다

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

`pdo_pgsql`의 `코루outine` 처리를 위한 기능입니다.

!> Swoole 버전 >= `v5.1.0`에서 사용할 수 있습니다.

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

예시:
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

`pdo_odbc`의 `코루outine` 처리를 위한 기능입니다.

!> Swoole 버전 >= `v5.1.0`에서 사용할 수 있습니다.

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

예시:
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

`pdo_oci`의 `코루outine` 처리를 위한 기능입니다.

!> Swoole 버전 >= `v5.1.0`에서 사용할 수 있습니다.

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

예시:
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
`pdo_sqlite`의 `코루outine` 처리를 위한 기능입니다.

!> Swoole 버전 >= `v5.1.0`에서 사용할 수 있습니다.

* **주의**

!> Swoole이 코루outine화할 때 `sqlite` 데이터베이스는 `시리얼라이즈` 모드를 사용하여 [스레드 안전](https://www.sqlite.org/threadsafe.html)을 보장합니다.  
만약 `sqlite` 데이터베이스가 컴파일할 때 스레드 모드가 싱글스레드로 설정되어 있다면, Swoole은 `sqlite`를 코루outine화할 수 없으며 경고를 던집니다. 하지만 이것은 사용에 영향을 주지 않으며, 증删개조조회 과정에서는 코루outine 전환이 일어나지 않습니다. 이러한 경우에는 `sqlite`를 다시 컴파일하고 스레드 모드를 `시리얼라이즈` 또는 `멀티스레드`로 설정해야 합니다. [이유](https://www.sqlite.org/compile.html#threadsafe).     
코루outine 환경에서 생성된 `sqlite` 연결은 모두 `시리얼라이즈`로, 비코루outine 환경에서 생성된 `sqlite` 연결은 기본적으로 `sqlite`의 스레드 모드와 일치합니다.   
만약 `sqlite`의 스레드 모드가 `멀티스레드`라면, 비코루outine 환경에서 생성된 연결은 여러 코루outine가 공유할 수 없습니다. 왜냐하면 이 경우 데이터베이스 연결은 `멀티스레드` 모드이기 때문입니다. 코루outine 환경에서 사용해도 `시리얼라이즈`로 업그레이드되지 않습니다.   
`sqlite`의 기본 스레드 모드는 `시리얼라이즈`이며, [시리얼라이즈 설명](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized), [기본 스레드 모드](https://www.sqlite.org/compile.html#threadsafe).      

예시:
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


## 방법


### setHookFlags()

`Hook`할 함수의 범위를 `flags`로 설정합니다.

!> Swoole 버전 >= `v4.5.0`에서 사용할 수 있습니다.

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```


### getHookFlags()

현재 `Hook`된 내용의 `flags`를 가져옵니다. `Hook`을 활성화할 때 전달한 `flags`와 다를 수 있습니다(가장 중요한 부분만 `Hook`됩니다).

!> Swoole 버전 >= `v4.4.12`에서 사용할 수 있습니다.

```php
Swoole\Runtime::getHookFlags(): int
```


## 흔히 사용되는 Hook 목록


### 사용 가능한 목록

  * `redis` 확장
  * `mysqlnd` 모드를 사용하는 `pdo_mysql`, `mysqli` 확장 (mysqlnd가 비활성화되어 있다면 코루outine화가 지원되지 않습니다)
  * `soap` 확장
  * `file_get_contents`, `fopen`
  * `stream_socket_client` (`predis`, `php-amqplib`)
  * `stream_socket_server`
  * `stream_select` (버전 4.3.2 이상에서 사용할 수 있습니다)
  * `fsockopen`
  * `proc_open` (버전 4.4.0 이상에서 사용할 수 있습니다)
  * `curl`


### 사용할 수 없는 목록

!> **코루outine화가 지원되지 않는다**는 것은 코루outine이 비동기 모드로 강등되게 되며, 이 경우 코루outine을 사용하는 것이 의미가 없습니다.

  * `mysql` : 기본적으로 `libmysqlclient`을 사용합니다.
  * `mongo` : 기본적으로 `mongo-c-client`을 사용합니다.
  * `pdo_pgsql` : Swoole 버전 >= `v5.1.0` 이후에는 `pdo_pgsql`을 사용하여 코루outine화가 가능합니다.
  * `pdo_oci` : Swoole 버전 >= `v5.1.0` 이후에는 `pdo_oci`을 사용하여 코루outine화가 가능합니다.
  * `pdo_odbc` : Swoole 버전 >= `v5.1.0` 이후에는 `pdo_odbc`을 사용하여 코루outine화가 가능합니다.
  * `pdo_firebird`
  * `php-amqp`


## API 변경사항

`v4.3` 및 이전 버전에서, `enableCoroutine` API는 두 개의 매개변수를 필요로 합니다.

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```


- `$enable` : 코루outine화가 활성화되거나 비활성화됩니다.
- `$flags` : 코루outine화할 유형을 선택합니다. 여러 가지를 선택할 수 있으며, 기본적으로 모든 것을 선택합니다. `$enable = true`일 때만 유효합니다.

!> `Runtime::enableCoroutine(false)`는 이전에 설정된 모든 코루outine `Hook` 설정을 비활성화합니다.
