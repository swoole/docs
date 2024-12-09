# Laufzeit

Im Vergleich zu `Swoole1.x` bietet `Swoole4+` die große Waffe der Coroutinen. Alle Geschäftslogiken sind synchron, aber der untere IO ist asynchron, was die Konzentration ermöglicht, während es die dissoziierte Code-Logik und das Eintauchen in mehrere回调schichten vermeiden kann, die die Pflege des Codes erschweren. Um diesen Effekt zu erzielen, müssen alle `IO`-Anfragen [asynchron IO](/learn?id=同步io异步io) sein. Die in der `Swoole1.x`-Ära bereitgestellten Clients wie `MySQL` und `Redis` waren zwar asynchron IO, aber sie benutzten eine asynchron回调-Programmierungsmethode, nicht eine coroutinenweise. Daher wurden diese Clients in der `Swoole4`-Ära entfernt.

Um das Problem der Coroutinenunterstützung für diese Clients zu lösen, hat die Swoole Entwicklungsgruppe viel Arbeit geleistet:

- Anfangs wurde für jeden Typ von Client ein Coroutine-Client entwickelt, wie im [Coroutine-Client](/coroutine_client/init) zu sehen ist, aber dies hatte drei Probleme:

  * Implementierung kompliziert, die Protokolle jedes Clients sind sehr komplex, und die vollständige Unterstützung ist eine enorme Aufgabe.
  * Die Nutzer müssen viel Code ändern, zum Beispiel wenn man zuvor mit dem nativen PHP `PDO` zur Abfrage von `MySQL` gelangt war, muss man jetzt die Methoden aus [Swoole\Coroutine\MySQL](/coroutine_client/mysql) verwenden.
  * Es ist schwierig, alle Operationen abzudecken, zum Beispiel `proc_open()`, `sleep()` und andere Funktionen können auch blockieren und den Programm synchron blockieren lassen.


- Angesichts der oben genannten Probleme hat die Swoole Entwicklungsgruppe eine andere Implementierungsmethode gewählt und verwendet die `Hook`-Funktion des nativen PHP-Funktions `Hook` um Coroutine-Clients zu implementieren. Mit einer Zeile Code kann der ursprüngliche synchron IO-Code in einen [asynchron IO](/learn?id=同步io异步io), der [coroutinenweise geplant](/coroutine?id=协程调度) werden kann, also eine Art 'One-Click Coroutine-化和'.

!> Diese Funktion wurde in der `v4.3`-Version stabilisiert und es gibt immer mehr Funktionen, die 'coroutinenfähig' gemacht werden können, daher werden einige der zuvor geschriebenen Coroutine-Clients nicht mehr empfohlen. Details finden Sie im [Coroutine-Client](/coroutine_client/init), zum Beispiel: In der `v4.3+`-Version wurde die 'coroutinenfähige' Unterstützung für Dateiverkehr (Funktionen wie `file_get_contents`, `fread`) hinzugefügt. Wenn Sie die `v4.3+`-Version verwenden, können Sie direkt 'coroutinenfähig' sein, anstatt die von Swoole bereitgestellten [Coroutine-Dateiverkehrsfunktionen](/coroutine/system) zu verwenden.


## Funktionen原型

Die Scope der zu 'coroutinenfähigen' Funktionen wird durch `flags` festgelegt

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // v4.4+ Version verwendet diese Methode.
// Oder
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

Um mehrere `flags` gleichzeitig zu aktivieren, muss die `|`-Operation verwendet werden

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

!> Die 'Hooked' Funktionen müssen im [Coroutine-Scheduler](/coroutine/scheduler) verwendet werden

#### Häufige Fragen :id=runtime-qa

!> **Welche Methode sollte ich verwenden, `Swoole\Runtime::enableCoroutine()` oder `Co::set(['hook_flags'])`?**
  
* `Swoole\Runtime::enableCoroutine()`, kann nach dem Start des Dienstes (Laufzeit) dynamisch flags festlegen. Nach der Anrufung ist die Wirkung global im aktuellen Prozess wirksam und sollte am Anfang des gesamten Projekts verwendet werden, um eine vollständige Abdeckung zu erzielen;
* `Co::set()` kann als PHPs `ini_set()` betrachtet werden und muss vor dem [Server->start()](/server/methods?id=start) oder [Co\run()](/coroutine/scheduler) aufgerufen werden, sonst werden die festgelegten `hook_flags` nicht wirksam sein. In der `v4.4+`-Version sollte diese Methode zum Festlegen von `flags` verwendet werden;
* Sowohl `Co::set(['hook_flags'])` als auch `Swoole\Runtime::enableCoroutine()` sollten nur einmal aufgerufen werden, da eine Wiederholung überschrieben wird.


## Optionen

Die von `flags` unterstützten Optionen umfassen:


### SWOOLE_HOOK_ALL

Öffnet alle Arten von flags (außer CURL)

!> Ab der Version v4.5.4 umfasst `SWOOLE_HOOK_ALL` auch `SWOOLE_HOOK_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); //exklusive CURL
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); //echte Coroutine-Fähigkeit für alle Arten, einschließlich CURL
```


### SWOOLE_HOOK_TCP

Seit `v4.1` unterstützt, TCP Socket-Typen von streams, einschließlich der am häufigsten verwendeten `Redis`, `PDO`, `Mysqli` sowie Operationen, die TCP-Verbindungen mit PHPs [streams](https://www.php.net/streams)-Serie von Funktionen verarbeiten, können 'hooked' werden, Beispiel:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//Erstellen von 100 Coroutinen
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//Hier wird eine Coroutine-Planung verursacht, der CPU schaltet zur nächsten Coroutine, blockiert den Prozess nicht
            $redis->get('key');//Hier wird eine Coroutine-Planung verursacht, der CPU schaltet zur nächsten Coroutine, blockiert den Prozess nicht
        });
    }
});
```

Der oben genannte Code verwendet die native `Redis`-Klasse, aber tatsächlich ist sie zu 'asynchron IO' geworden. `Co\run()` schafft einen [Coroutine-Scheduler](/coroutine/scheduler), `go()` schafft eine Coroutine. Diese beiden Operationen werden in der von Swoole bereitgestellten [Swoole\Server-Klasse](/server/init) automatisch erledigt, man muss sie nicht manuell erledigen, siehe [enable_coroutine](/server/setting?id=enable_coroutine).

Das bedeutet, dass traditionelle PHP-Entwickler mit dem逻辑Code, mit dem sie am besten vertraut sind, Programme mit hoher Konzentration und hoher Leistung schreiben können, wie folgt:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);//Hier wird eine Coroutine-Planung verursacht, der CPU schaltet zur nächsten Coroutine (zum nächsten Request), blockiert den Prozess nicht
      $redis->get('key');//Hier wird eine Coroutine-Planung verursacht, der CPU schaltet zur nächsten Coroutine (zum nächsten Request), blockiert den Prozess nicht
});

$http->start();
```


### SWOOLE_HOOK_UNIX

Seit `v4.2` unterstützt. 'Unix Stream Socket'-Typen von streams, Beispiel:

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

Seit `v4.2` unterstützt. UDP Socket-Typen von streams, Beispiel:

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

Ab `v4.2` wird Unterstützung für Unix Domain Sockets (UDS) bereitgestellt. Ein Stream, der die Art von Stream, wie Unix Dgram Sockets, darstellt, ist ein Beispiel:

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

Ab `v4.2` wird Unterstützung für SSL-Streams bereitgestellt. Ein Beispiel:

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

Ab `v4.2` wird Unterstützung für TLS-Streams bereitgestellt. [Referenz](https://www.php.net/manual/en/context.ssl.php). Ein Beispiel:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```

### SWOOLE_HOOK_SLEEP

Ab `v4.2` wird die `Hook` für die `sleep`-Funktion bereitgestellt, einschließlich `sleep`, `usleep`, `time_nanosleep`, `time_sleep_until`. Da die minimale Granularität des unteren Timers 1ms ist, wird bei Verwendung von Funktionen wie `usleep` mit einer Genauigkeit unter 1ms direkt der `sleep`-Systemruf verwendet. Dies kann zu sehr kurzzeitigen Schlafblockaden führen. Ein Beispiel:

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
//Ausgabe
2
1
```

### SWOOLE_HOOK_FILE

Ab `v4.3` wird Unterstützung bereitgestellt.

* **`file_operations` werden in `Coroutine` umgewandelt, die unterstützten Funktionen umfassen:**

    * `fopen`
    * `fread`/`fgets`
    * `fwrite`/`fputs`
    * `file_get_contents`, `file_put_contents`
    * `unlink`
    * `mkdir`
    * `rmdir`

Ein Beispiel:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```

### SWOOLE_HOOK_STREAM_FUNCTION

Ab `v4.4` wird die `Hook` für `stream_select()` bereitgestellt, ein Beispiel:

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

Ab `v4.4` wird die `Hook` für blockierende Funktionen bereitgestellt, einschließlich: `gethostbyname`, `exec`, `shell_exec`, ein Beispiel:

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```

### SWOOLE_HOOK_PROC

Ab `v4.4` wird die Coroutine-Verarbeitung von `proc*` Funktionen bereitgestellt, einschließlich: `proc_open`, `proc_close`, `proc_get_status`, `proc_terminate`. Ein Beispiel:

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

[Ab `v4.4LTS`](https://github.com/swoole/swoole-src/tree/v4.4.x) oder ab `v4.5` wird offiziell unterstützt.

* **HOOK für cURL, unterstützte Funktionen:**

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

Ein Beispiel:

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

Die Coroutine-Verarbeitung für native cURL.

!> Swoole-Version >= `v4.6.0` ist verfügbar

!> Um dies zu verwenden, muss beim编译en die Option [--enable-swoole-curl](/environment?id=allgemeine_Parameter) aktiviert werden;  
Wenn diese Option aktiviert ist, wird automatisch `SWOOLE_HOOK_NATIVE_CURL` festgelegt und [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_all) deaktiviert;  
Darüber hinaus beinhaltet `SWOOLE_HOOK_ALL` auch `SWOOLE_HOOK_NATIVE_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_NATIVE_CURL]);

Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_NATIVE_CURL]);
```

Ein Beispiel:

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

Die Coroutine-Verarbeitung für die sockets Erweiterung.

!> Swoole-Version >= `v4.6.0` ist verfügbar

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SOCKETS]);
```

### SWOOLE_HOOK_STDIO

Die Coroutine-Verarbeitung für STDIO.

!> Swoole-Version >= `v4.6.2` ist verfügbar

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

Die `koroutineierte Handhabung` von `pdo_pgsql`.

!> Swoole-Version >= `v5.1.0` verfügbar

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

Beispiel:
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

Die `koroutineierte Handhabung` von `pdo_odbc`.

!> Swoole-Version >= `v5.1.0` verfügbar

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

Beispiel:
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

Die `koroutineierte Handhabung` von `pdo_oci`.

!> Swoole-Version >= `v5.1.0` verfügbar

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

Beispiel:
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
Die `koroutineierte Handhabung` von `pdo_sqlite`.

!> Swoole-Version >= `v5.1.0` verfügbar

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_SQLITE]);
```

* **Hinweis**

!> Da `swoole` bei der koroutineierten Handhabung von `sqlite` Datenbanken einen `seriellen Modus` verwendet, um die [Thread-Sicherheit](https://www.sqlite.org/threadsafe.html) zu gewährleisten.  
Wenn der bei der Kompilierung von `sqlite` angegebene Thread-Modus ein Einzelthread-Modus ist, kann `swoole` die `sqlite` Datenbank nicht koroutineieren und wirft eine Warnung aus, aber es beeinträchtigt den Gebrauch nicht. Es gibt jedoch keine Koroutine-Wechsel während der Einfügen, Löschen, Bearbeiten und Abfragevorgänge. In diesem Fall muss man `sqlite` neu kompilieren und den Thread-Modus auf `seriell` oder `mehr线程` setzen, [Grund](https://www.sqlite.org/compile.html#threadsafe).     
Alle in einem koroutineerten Umfeld erstellten `sqlite` Verbindungen sind `seriell`, und die in einem nicht koroutineerten Umfeld erstellten `sqlite` Verbindungen sind standardmäßig mit dem Thread-Modus von `sqlite` übereinstimmend.   
Wenn der Thread-Modus von `sqlite` ein Mehrthread-Modus ist, können die in einem nicht koroutineerten Umfeld erstellten Verbindungen nicht von mehreren Koroutinen gemeinsam genutzt werden, da die Verbindung im Mehrthread-Modus ist und im koroutineerten Umfeld verwendet wird, wird sie nicht auf `seriell` umgeschaltet.   
Der Standard-Thread-Modus von `sqlite` ist `seriell`, [serielle Erklärung](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized), [Standard-Thread-Modus](https://www.sqlite.org/compile.html#threadsafe).      

Beispiel:
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

## Methoden


### setHookFlags()

Legt durch `flags` den Bereich der zu `Hook`en Funktionen fest

!> Swoole-Version >= `v4.5.0` verfügbar

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```


### getHookFlags()

Erhält die derzeit `Hook`en Inhalte der `flags`, die möglicherweise nicht mit den bei der Aktivierung von `Hook`en eingegebenen `flags` übereinstimmen (da die nicht erfolgreich `Hook`en Flags gelöscht werden)

!> Swoole-Version >= `v4.4.12` verfügbar

```php
Swoole\Runtime::getHookFlags(): int
```


## Common Hook-Listenliste


### Verfügbare Liste

  * `redis` Erweiterung
  * `pdo_mysql` und `mysqli` Erweiterungen im `mysqlnd` Modus, wenn `mysqlnd` nicht aktiviert ist, wird die Koroutine-Unterstützung nicht unterstützt
  * `soap` Erweiterung
  * `file_get_contents`, `fopen`
  * `stream_socket_client` (`predis`, `php-amqplib`)
  * `stream_socket_server`
  * `stream_select` (erfordert Version `4.3.2` oder höher)
  * `fsockopen`
  * `proc_open` (erfordert Version `4.4.0` oder höher)
  * `curl`


### Nichtverfügbare Liste

!> **Nicht unterstützt für Koroutine** bedeutet, dass die Koroutine auf einen blockierenden Modus herabgestuft wird, bei dem die Verwendung von Koroutinen keine praktische Bedeutung hat

  * `mysql` : Der untere Layer verwendet `libmysqlclient`
  * `mongo` : Der untere Layer verwendet `mongo-c-client`
  * `pdo_pgsql` , Swoole-Version >= `v5.1.0` danach, kann mit `pdo_pgsql` die Koroutine-Handhabung erfolgen
  * `pdo_oci` , Swoole-Version >= `v5.1.0` danach, kann mit `pdo_oci` die Koroutine-Handhabung erfolgen
  * `pdo_odbc` , Swoole-Version >= `v5.1.0` danach, kann mit `pdo_odbc` die Koroutine-Handhabung erfolgen
  * `pdo_firebird`
  * `php-amqp`


## API-Änderungen

In Versionen `v4.3` und früher erforderte die API von `enableCoroutine` zwei Parameter.

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```


- `$enable`: Schaltet die Koroutine-Unterstützung ein oder aus.
- `$flags`: Wählt die zu `koroutineisierenden` Typen aus, kann mehrfach ausgewählt werden, standardmäßig alle ausgewählt. Nur wirksam, wenn `$enable = true`.

!> `Runtime::enableCoroutine(false)` schaltet alle zuvor festgelegten Optionen für die Koroutine-Hook-Einstellungen aus.
