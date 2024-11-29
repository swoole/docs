# 実行時

`Swoole4+`は、`Swoole1.x`に比べて協程という大きな武器を提供しています。すべてのビジネスコードは同期であるにもかかわらず、バックエンドのIOは非同期であり、並行を保証しながら従来の非同期カスタムレシーブによる散発的なコードロジックや、多重レシーブに陥ってコードが維持できない問題を避けています。この効果を達成するためには、すべての`IO`リクエストが[非同期IO](/learn?id=同期io非同期io)でなければなりません。一方、`Swoole1.x`時代に提供された`MySQL`、`Redis`などのクライアントは非同期IOでしたが、非同期カスタムレシーブのプログラミング方式であり、協程方式ではありませんでした。そのため、`Swoole4`時代にはこれらのクライアントが廃止されました。

これらのクライアントの協程サポート問題を解決するために、Swoole開発チームは多くの作業を行いました：
- 最初は、各種タイプのクライアントに対して独自の協程クライアントを作成しましたが、以下の3つの問題がありました：

  * 実装が複雑で、各クライアントの細かいプロトコルが非常に複雑であり、完璧にサポートする作業量が膨大でした。
  * ユーザーが変更する必要のあるコードが多く、例えば以前は`MySQL`を検索する際に使用していたPHPのネイティブな`PDO`を、現在は[Swoole\Coroutine\MySQL](/coroutine_client/mysql)の方法で置き換える必要がありました。
  * すべての操作をカバーするのが難しく、例えば`proc_open()`、`sleep()`などの関数もブロッキングしてプログラムを同期ブロッキングにする可能性がありました。
- 上記の問題に対処するために、Swoole開発チームは実装思路を変え、`Hook`原生PHP関数の方法で協程クライアントを実現しました。一行のコードで元の同期IOのコードを[協程スケジュール](/coroutine?id=協程スケジュール)可能な[非同期IO](/learn?id=同期io非同期io)、つまり「ワンクリック協程化」にすることができます。

> この特徴は`v4.3`バージョン以降に安定し、「協程化」可能な関数も増え続けているため、以前に書いた協程クライアントはすでに推奨されていません。詳細は[協程クライアント](/coroutine_client/init)を参照してください。例えば、「v4.3+」ではファイル操作(`file_get_contents`、`fread`など)の「協程化」がサポートされており、「v4.3+」バージョンを使用していれば、Swooleが提供する[協程ファイル操作](/coroutine/system)を使用するのではなく、「協程化」を直接使用できます。
## ファンクター

`flags`を使用して、`協程化`する関数の範囲を設定します。

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // v4.4+バージョンでこの方法を使用します。
// または
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

複数の`flags`を同時に開始するには、`|`演算子を使用します。

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

> `Hook`される関数は[協程コンテナ](/coroutine/scheduler)で使用する必要があります。

#### 常見の質問 :id=runtime-qa

> **`Swoole\Runtime::enableCoroutine()`と`Co::set(['hook_flags'])`のどちらを使用すべきですか？**

* `Swoole\Runtime::enableCoroutine()`は、サービスの起動後（実行中）にリアルタイムでflagsを設定することができ、呼び出し方法を行った後、現在のプロセス内で全局に効果を発揮します。すべてのプロジェクトの開始時に行うべきで、100％のカバーを得る効果があります。
* `Co::set()`はPHPの`ini_set()`に相当し、[Server->start()](/server/methods?id=start)の前または[Co\run()](/coroutine/scheduler)の前で呼び出す必要があります。そうでなければ、設定した`hook_flags`は効果を発揮しません。「v4.4+」バージョンでは、この方法で`flags`を設定すべきです。
* `Co::set(['hook_flags'])`または`Swoole\Runtime::enableCoroutine()`は、一度だけ呼び出すべきで、繰り返し呼び出すと上書きされます。
## オプション

`flags`がサポートするオプションは以下の通りです：
### SWOOLE_HOOK_ALL

以下のすべてのタイプのflagsをオンにします（CURLを含まない）。

> v4.5.4バージョンから、`SWOOLE_HOOK_ALL`には`SWOOLE_HOOK_CURL`が含まれます。

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); //CURLを含まない
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); //本当の協程化で全てのタイプを含む、CURLも含む
```
### SWOOLE_HOOK_TCP

v4.1からサポートされ、TCP Socketタイプのstream、最も一般的な`Redis`、`PDO`、`Mysqli`、およびPHPの[streams](https://www.php.net/streams)シリーズの関数を使用してTCP接続を操作する操作はすべて`Hook`できます。例示：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () { //100個の協程を作成
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379); //ここで協程スケジュールが発生し、CPUが次の協程に切り替わり、プロセスがブロッキングされません
            $redis->get('key'); //ここで協程スケジュールが発生し、CPUが次の協程に切り替わり、プロセスがブロッキングされません
        });
    }
});
```

上記のコードは元の`Redis`クラスを使用していますが、実際には`异步IO`に変わっています。`Co\run()`は[協程コンテナ](/coroutine/scheduler)を作成し、`go()`は協程を作成します。これらの操作はSwooleが提供する[Swoole\Serverクラスフレームワーク](/server/init)で自動的に行われており、手動で行う必要はありません。[enable_coroutine](/server/setting?id=enable_coroutine)を参照してください。

つまり、従来の`PHP`プログラマーは、最も馴染みのある論理的なコードで高並行性と高性能のプログラムを書くことができます。例えば：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379); //ここで協程スケジュールが発生し、CPUが次のリクエストに切り替わり、プロセスがブロッキングされません
      $redis->get('key'); //ここで協程スケジュールが発生し、CPUが次のリクエストに切り替わり、プロセスがブロッキングされません
});

$http->start();
```
### SWOOLE_HOOK_UNIX

v4.2からサポートされます。`Unix Stream Socket`タイプのstream、例示：

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

v4.2からサポートされます。UDP Socketタイプのstream、例示：

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

v4.2からサポートされます。Unix Dgram Socketタイプのstream、例示：

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

v4.2からサポートされます。SSL Socketタイプのstream、例示：

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

v4.2からサポートされます。TLS Socketタイプのstream、[参考](https://www.php.net/manual/en/context.ssl.php)。

例示：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```
### SWOOLE_HOOK_SLEEP

v4.2からサポートされます。「sleep」関数の「Hook」、含む「sleep」、「usleep」、「time_nanosleep」、「time_sleep_until」。底層のタイマーの最小粒度が「1ms」なので、「usleep」などの高精度の睡眠関数を使用する際、1ms未満に設定した場合は、「sleep」システムコールを直接使用します。非常に短い睡眠ブロッキングを引き起こす可能性があります。例示：

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
//出力 
2
1
```
### SWOOLE_HOOK_FILE

v4.3からサポートされます。

* **ファイル操作の「協程化処理」、サポートされる関数は：**

    * `fopen`
    * `fread`/`fgets`
    * `fwrite`/`fputs`
    * `file_get_contents`、`file_put_contents`
    * `unlink`
    * `mkdir`
    * `rmdir`

例示：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```
### SWOOLE_HOOK_STREAM_FUNCTION

v4.4からサポートされます。「stream_select()」の「Hook」、例示：

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

v4.4からサポートされます。ここでの「blocking function」には、「gethostbyname」、「exec」、「shell_exec」が含まれます、例示：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```
### SWOOLE_HOOK_PROC

v4.4からサポートされます。協程化された `proc*` 函数、含む：`proc_open`、`proc_close`、`proc_get_status`、`proc_terminate`。

例示：

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

[v4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x)以降または`v4.5`から正式にサポートされています。

* **CURLのHOOK、サポートされる関数は：**

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

例示：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_CURL]);

Co\run(function () {
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, "http://www.xinhuanet.com/");  
    curl_setopt($ch, CURLOPT_HEADER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch
### SWOOLE_HOOK_PDO_PGSQL

「pdo_pgsql」の「協程化処理」について。

> Swooleバージョンは `v5.1.0` 以降で利用可能です

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

例：
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

「pdo_odbc」の「協程化処理」について。

> Swooleバージョンは `v5.1.0` 以降で利用可能です

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

例：
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

「pdo_oci」の「協程化処理」について。

> Swooleバージョンは `v5.1.0` 以降で利用可能です

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

例：
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
「pdo_sqlite」の「協程化処理」について。

> Swooleバージョンは `v5.1.0` 以降で利用可能です

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_SQLITE]);
```

* **注意**

> Swooleは「sqlite」データベースを協程化する際に、「串行化」モードを採用して[線程安全](https://www.sqlite.org/threadsafe.html)を保証します。  
もし「sqlite」データベースがコンパイル時に指定された線程モードが「単線程」の場合、Swooleは「sqlite」を協程化することができず、警告が発生しますが、使用には影響しません。ただし、増删改查の過程では協程切り替えが発生しません。このような場合は、「sqlite」を再コンパイルし、線程モードを「串行化」または「多線程」に指定する必要があります。[理由](https://www.sqlite.org/compile.html#threadsafe)。  
協程環境内で作成された「sqlite」接続はすべて「串行化」され、非協程環境内で作成された「sqlite」接続は、デフォルトで「sqlite」の線程モードと一致します。  
「sqlite」の線程モードが「多線程」の場合、非協程環境内で作成された接続は、複数の協程と共有することはできません。なぜなら、その時点でデータベース接続は「多線程」モードであり、協程化環境内でも「串行化」に昇格することはありません。  
「sqlite」のデフォルト線程モードは「串行化」であり、「串行化」の説明については[こちら](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized)、[デフォルト線程モード](https://www.sqlite.org/compile.html#threadsafe)については[こちら](https://www.sqlite.org/compile.html#threadsafe)をご覧ください。  

例：
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
## メソッド
### setHookFlags()

「flags」を指定して、Hookする関数の範囲を設定します。

> Swooleバージョンは `v4.5.0` 以降で利用可能です

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```
### getHookFlags()

現在Hookされている内容の「flags」を取得しますが、Hookを開始した時に指定された「flags」と一致しない場合があります（Hookに成功しなかった「flags」は削除されます）

> Swooleバージョンは `v4.4.12` 以降で利用可能です

```php
Swoole\Runtime::getHookFlags(): int
```
## 常見のHookリスト
### 利用可能なリスト

  * 「redis」拡張
  * 「mysqlnd」モードを使用する「pdo_mysql」、「mysqli」拡張は、「mysqlnd」が有効でなければ協程化されません
  * 「soap」拡張
  * 「file_get_contents」、「fopen」
  * 「stream_socket_client」（「predis」、「php-amqplib」を使用する場合）
  * 「stream_socket_server」
  * 「stream_select」は `4.3.2` 以上のバージョンが必要です
  * 「fsockopen」
  * 「proc_open」は `4.4.0` 以上のバージョンが必要です
  * 「curl」
### 利用できないリスト

> **協程化されない**と表示される場合、協程はブロッキングモードに降格し、その時点での協程化は実質的に無意味になります

  * 「mysql」：「libmysqlclient」を底层で使用しています
  * 「mongo」：「mongo-c-client」を底层で使用しています
  * 「pdo_pgsql」は、Swooleバージョンが `v5.1.0` 以降の場合、協程化処理が可能です
  * 「pdo_oci」は、Swooleバージョンが `v5.1.0` 以降の場合、協程化処理が可能です
  * 「pdo_odbc」は、Swooleバージョンが `v5.1.0` 以降の場合、協程化処理が可能です
  * 「pdo_firebird」
  * 「php-amqp」
## API変更

`v4.3`及びそれ以前のバージョンでは、「enableCoroutine」のAPIには2つのパラメータが必要です。

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```- `$enable`：協程化を有効にするかどうかを設定します。
- `$flags`：Hookするタイプを選択します。複数選択可能で、デフォルトは全て選択されます。`$enable = true`の場合にのみ有効です。

> `Runtime::enableCoroutine(false)`は、前回設定したすべてのOptionの協程Hookを無効にします。
