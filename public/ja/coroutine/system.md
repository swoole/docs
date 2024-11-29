```
# コロニアルシステム

システム関連の`API`を协程で封装しています。このモジュールは`v4.4.6`の正式版以降で利用できます。ほとんどの`API`は`AIO`スレッドプールに基づいて実現されています。

!> `v4.4.6`以前のバージョンは、`Co`の短名または`Swoole\Coroutine`を使って呼び出してください。例えば、`Co::sleep`や`Swoole\Coroutine::sleep`です。  
`v4.4.6`以降のバージョンでは、公式に**推奨される呼び方**は`Co\System::sleep`または`Swoole\Coroutine\System::sleep`です。  
この変更は命名空間を規範化することを目的としていますが、同時に下位互換性も保証しています（つまり、`v4.4.6`以前の書き方も大丈夫で、変更する必要はありません）。

## 方法

### statvfs()

ファイルシステム情報を取得します。

!> Swooleバージョン >= v4.2.5で利用可能

```php
Swoole\Coroutine\System::statvfs(string $path): array|false
```

  * **パラメータ** 

    * **`string $path`**
      * **機能**：ファイルシステムがマウントされたディレクトリ【例えば`/`は、dfや`mount -l`コマンドで取得できます】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **使用例**

    ```php
    Swoole\Coroutine\run(function () {
        var_dump(Swoole\Coroutine\System::statvfs('/'));
    });
    ```
  * **出力例**
    
    ```php
    array(11) {
      ["bsize"]=>
      int(4096)
      ["frsize"]=>
      int(4096)
      ["blocks"]=>
      int(61068098)
      ["bfree"]=>
      int(45753580)
      ["bavail"]=>
      int(42645728)
      ["files"]=>
      int(15523840)
      ["ffree"]=>
      int(14909927)
      ["favail"]=>
      int(14909927)
      ["fsid"]=>
      int(1002377915335522995)
      ["flag"]=>
      int(4096)
      ["namemax"]=>
      int(255)
    }
    ```

### fread()

ファイルを协程で読み取ります。

```php
Swoole\Coroutine\System::fread(resource $handle, int $length = 0): string|false
```

!> `v4.0.4`より前のバージョンでは、`fread`方法は非ファイルタイプの`stream`（例えば`STDIN`、`Socket`）をサポートしていませんので、このリソースで`fread`操作を行うことはお勧めしません。  
`v4.0.4`以降のバージョンでは、非ファイルタイプの`stream`リソースを`fread`方法でサポートしており、底層は自動的に`stream`のタイプに応じて`AIO`スレッドプールまたは[EventLoop](/learn?id=什么是eventloop)を選択して実現します。

!> この方法は `5.0`バージョンで廃止され、`6.0`バージョンで削除されました

  * **パラメータ** 

    * **`resource $handle`**
      * **機能**：ファイルハンドラ【`fopen`で開いたファイルタイプの`stream`リソースでなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $length`**
      * **機能**：読み取る長さ【デフォルトは`0`で、ファイルの全部内容を意味します】
      * **デフォルト値**：`0`
      * **その他の値**：なし

  * **戻り値** 

    * 読み取りに成功すると文字列内容が戻り、失敗すると`false`が戻ります

  * **使用例**  

    ```php
    $fp = fopen(__FILE__, "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fread($fp);
        var_dump($r);
    });
    ```

### fwrite()

ファイルに协程でデータを書き出します。

```php
Swoole\Coroutine\System::fwrite(resource $handle, string $data, int $length = 0): int|false
```

!> `v4.0.4`より前のバージョンでは、`fwrite`方法は非ファイルタイプの`stream`（例えば`STDIN`、`Socket`）をサポートしていませんので、このリソースで`fwrite`操作を行うことはお勧めしません。  
`v4.0.4`以降のバージョンでは、非ファイルタイプの`stream`リソースを`fwrite`方法でサポートしており、底層は自動的に`stream`のタイプに応じて`AIO`スレッドプールまたは[EventLoop](/learn?id=什么是eventloop)を選択して実現します。

!> この方法は `5.0`バージョンで廃止され、`6.0`バージョンで削除されました

  * **パラメータ** 

    * **`resource $handle`**
      * **機能**：ファイルハンドラ【`fopen`で開いたファイルタイプの`stream`リソースでなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $data`**
      * **機能**：書き込むデータ内容【テキストまたはバイナリデータ均可】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $length`**
      * **機能**：書き込む長さ【デフォルトは`0`で、`$data`の全部内容を意味します。`$length`は`$data`の長さよりも小さくなければなりません】
      * **デフォルト値**：`0`
      * **その他の値**：なし

  * **戻り値** 

    * 書き込みに成功するとデータの長さが戻り、失敗すると`false`が戻ります

  * **使用例**  

    ```php
    $fp = fopen(__DIR__ . "/test.data", "a+");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fwrite($fp, "hello world\n", 5);
        var_dump($r);
    });
    ```

### fgets()

ファイルの内容を协程で一行ずつ読み取ります。

底層では`php_stream`バッファを使用しており、デフォルトサイズは`8192`バイトです。バッファサイズは`stream_set_chunk_size`関数で設定できます。

```php
Swoole\Coroutine\System::fgets(resource $handle): string|false
```

!> `fgets`関数はファイルタイプの`stream`リソースでのみ使用できます。Swooleバージョン >= `v4.4.4`で利用可能

!> この方法は `5.0`バージョンで廃止され、`6.0`バージョンで削除されました

  * **パラメータ** 

    * **`resource $handle`**
      * **機能**：ファイルハンドラ【`fopen`で開いたファイルタイプの`stream`リソースでなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値** 

    * `EOL`（`\r`または`\n`）に達した場合は一行のデータを戻し、`EOL`を含みます
    * `EOL`に達していないが内容の長さが`php_stream`バッファの`8192`バイトを超える場合は、`8192`バイトのデータを戻し、`EOL`は含まれません
    * ファイルの末尾`EOF`に達した場合は空文字列を戻し、`feof`関数を使用してファイルが読み終わっているかどうかを判断できます
    * 読み取りに失敗すると`false`が戻ります。エラーコードは[swoole_last_error](/functions?id=swoole_last_error)関数で取得できます

  * **使用例**  

    ```php
    $fp = fopen(__DIR__ . "/defer_client.php", "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fgets($fp);
        var_dump($r);
    });
    ```

### readFile()

ファイルを协程で読み取ります。

```php
Swoole\Coroutine\System::readFile(string $filename): string|false
```

  * **パラメータ** 

    * **`string $filename`**
      * **機能**：ファイル名
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値** 

    * 読み取りに成功すると文字列内容が戻り、失敗すると`false`が戻ります。エラー情報は[swoole_last_error](/functions?id=swoole_last_error)関数で取得できます
    * `readFile`方法はサイズ制限がありません。読み取った内容はメモリに保存されるため、超大ファイルを读取すると大量のメモリを占有する可能性があります

  * **使用例**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $r = Swoole\Coroutine\System::readFile($filename);
        var_dump($r);
    });
    ```
```
### writeFile()

協程でファイルに書き込む。

```php
Swoole\Coroutine\System::writeFile(string $filename, string $fileContent, int $flags): bool
```

  * **引数** 

    * **`string $filename`**
      * **機能**：ファイル名【書き込む権限が必要で、ファイルが存在しない場合は自動で作成されます。ファイルを開け失败するとすぐに`false`を返します】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`string $fileContent`**
      * **機能**：ファイルに書き込む内容【最大で4Mまで書き込むことができます】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $flags`**
      * **機能**：書き込むオプション【デフォルトでは現在のファイルの内容を空にして書き込みますが、`FILE_APPEND`と使用するとファイルの末尾に追記されます】
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値** 

    * 成功した場合は`true`を返し、失敗した場合は`false`を返します。

  * **使用例**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $w = Swoole\Coroutine\System::writeFile($filename, "hello swoole!");
        var_dump($w);
    });
    ```


### sleep()

待機状態に入る。

PHPの`sleep`関数に相当しますが、Coroutine::sleepは[協程スケジュール](/coroutine?id=協程スケジュール)によって実現されており、下層では現在の協程を`yield`し、時間片を譲り、非同期タイマーを追加します。タイムアウト時間が到达すると、現在の協程を再び`resume`し、実行を再開します。

sleep接口を使用することで、タイムアウト待ち機能を簡単に実現できます。

```php
Swoole\Coroutine\System::sleep(float $seconds): void
```

  * **引数** 

    * **`float $seconds`**
      * **機能**：睡眠する時間【0以上でなければならず、最大で1日間の時間（86400秒）を超えてはいけません】
      * **単位**：秒、最小精度はミリ秒（0.001秒）
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **使用例**  

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9502);

    $server->on('Request', function($request, $response) {
        //200ms待ってからブラウザに応答を送信する
        Swoole\Coroutine\System::sleep(0.2);
        $response->end("<h1>Hello Swoole!</h1>");
    });

    $server->start();
    ```


### exec()

shellコマンドを実行する。下層では自動的に[協程スケジュール](/coroutine?id=協程スケジュール)が行われます。

```php
Swoole\Coroutine\System::exec(string $cmd): array
```

  * **引数** 

    * **`string $cmd`**
      * **機能**：実行するshellコマンド
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **戻り値**

    * 実行に失敗した場合は`false`を返し、成功した場合はプロセスの終了ステータスコード、シグナル、出力内容を格納した配列を返します。

    ```php
    array(
        'code'   => 0,  // プロセスの終了ステータスコード
        'signal' => 0,  // シグナル
        'output' => '', // 出力内容
    );
    ```

  * **使用例**  

    ```php
    Swoole\Coroutine\run(function() {
        $ret = Swoole\Coroutine\System::exec("md5sum ".__FILE__);
    });
    ```

  * **注意**

  !>もしスクリプトコマンドの実行時間が長すぎると、タイムアウトで退出することがあります。このような状況では、[socket_read_timeout](/coroutine_client/init?id=タイムアウトルール)を大きくすることによって問題を解決することができます。


### gethostbyname()

域名をIPアドレスに解析する。同期の线程プールを用いてシミュレーションを行い、下層では自動的に[協程スケジュール](/coroutine?id=協程スケジュール)が行われます。

```php
Swoole\Coroutine\System::gethostbyname(string $domain, int $family = AF_INET, float $timeout = -1): string|false
```

  * **引数** 

    * **`string $domain`**
      * **機能**：域名
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $family`**
      * **機能**：域族【`AF_INET`はIPv4アドレスを返すことを示し、`AF_INET6`を使用するとIPv6アドレスを返す】
      * **デフォルト値**：`AF_INET`
      * **その他の値**：`AF_INET6`

    * **`float $timeout`**
      * **機能**：タイムアウト時間
      * **単位**：秒、最小精度はミリ秒（0.001秒）
      * **デフォルト値**：`-1`
      * **その他の値**：なし

  * **戻り値**

    * 成功した場合は域名に対応するIPアドレスを返し、失敗した場合は`false`を返します。swoole_last_error(/functions?id=swoole_last_error)を使用してエラー情報を取得することができます。

    ```php
    array(
        'code'   => 0,  // プロセスの終了ステータスコード
        'signal' => 0,  // シグナル
        'output' => '', // 出力内容
    );
    ```

  * **拡張**

    * **タイムアウト制御**

      `$timeout`パラメータは、協程が待つタイムアウト時間を制御することができます。規定の時間内に結果が返されない場合、協程はすぐに`false`を返し、以下の処理を続けます。下層の実現では、この非同期タスクを`cancel`としてマークし、gethostbynameは引き続きAIO线程プールで実行されます。
      
      /etc/resolv.conf文件中でgethostbynameとgetaddrinfoの下層C関数のタイムアウト時間を設定することができます。具体的な方法は[DNS解析のタイムアウトと再試行の設定](/learn_other?id=设置dns解析超时和重试)を参照してください。

  * **使用例**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::gethostbyname("www.baidu.com", AF_INET, 0.5);
        echo $ip;
    });
    ```


### getaddrinfo()

DNS解析を行い、域名に対応するIPアドレスを照会する。

gethostbynameとは異なり、getaddrinfoはより多くのパラメータ設定をサポートし、複数のIP結果を返すことができます。

```php
Swoole\Coroutine\System::getaddrinfo(string $domain, int $family = AF_INET, int $socktype = SOCK_STREAM, int $protocol = STREAM_IPPROTO_TCP, string $service = null, float $timeout = -1): array|false
```

  * **引数** 

    * **`string $domain`**
      * **機能**：域名
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $family`**
      * **機能**：域族【`AF_INET`はIPv4アドレスを返すことを示し、`AF_INET6`を使用するとIPv6アドレスを返す】
      * **デフォルト値**：なし
      * **その他の値**：なし
      
      !>その他のパラメータ設定についてはman getaddrinfoのドキュメントを参照してください。

    * **`int $socktype`**
      * **機能**：プロトコルタイプ
      * **デフォルト値**：`SOCK_STREAM`
      * **その他の値**：`SOCK_DGRAM`、`SOCK_RAW`

    * **`int $protocol`**
      * **機能**：プロトコル
      * **デフォルト値**：`STREAM_IPPROTO_TCP`
      * **その他の値**：`STREAM_IPPROTO_UDP`、`STREAM_IPPROTO_STCP`、`STREAM_IPPROTO_TIPC`、`0`

    * **`string $service`**
      * **機能**：
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間
      * **単位**：秒、最小精度はミリ秒（0.001秒）
      * **デフォルト値**：`-1`
      * **その他の値**：なし

  * **戻り値**

    * 成功した場合は複数のIPアドレスからなる配列を返し、失敗した場合は`false`を返します。

  * **使用例**  

    ```php
    Swoole\Coroutine\run(function () {
        $ips = Swoole\Coroutine\System::getaddrinfo("www.baidu.com");
        var_dump($ips);
    });
    ```
### dnsLookup()

ドメイン名アドレスの照会。

`Coroutine\System::gethostbyname`とは異なり、`Coroutine\System::dnsLookup`は直接UDPクライアントネットワーク通信に基づいて実現されており、`libc`の`gethostbyname`関数を使用していません。

!> Swooleバージョン >= `v4.4.3`で利用可能で、下層では`/etc/resolve.conf`を読んでDNSサーバーアドレスを取得し、現在は`AF_INET(IPv4)`ドメイン解析のみをサポートしています。Swooleバージョン >= `v4.7`では、第三の引数を使用して`AF_INET6(IPv6)`をサポートすることができます。

```php
Swoole\Coroutine\System::dnsLookup(string $domain, float $timeout = 5, int $type = AF_INET): string|false
```

  * **引数** 

    * **`string $domain`**
      * **機能**：ドメイン名
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`float $timeout`**
      * **機能**：タイムアウト時間
      * **単位**：秒、最小精度はミリ秒(`0.001`秒)
      * **デフォルト値**：`5`
      * **その他の値**：なし

    * **`int $type`**
        * **単位**：秒、最小精度はミリ秒(`0.001`秒)
        * **デフォルト値**：`AF_INET`
        * **その他の値**：`AF_INET6`

    !> `$type`引数はSwooleバージョン >= `v4.7`で利用可能です。

  * **戻り値**

    * 解析に成功すると対応するIPアドレスが返されます
    * 失敗すると`false`が返され、[swoole_last_error](/functions?id=swoole_last_error)を使用してエラー情報を取得できます

  * **一般的なエラー**

    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED`：このドメイン名を解析できませんでした。照会に失敗しました
    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT`：解析タイムアウトしました。DNSサーバーには問題があるかもしれません。規定の時間内に結果が返されませんでした

  * **使用例**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::dnsLookup("www.baidu.com");
        echo $ip;
    });
    ```


### wait()

既存の[Process::wait](/process/process?id=wait)に対応するが、このAPIは协程バージョンであり、协程を挂起させます。`Swoole\Process::wait`や`pcntl_wait`関数を置き換えることができます。

!> Swooleバージョン >= `v4.5.0`で利用可能

```php
Swoole\Coroutine\System::wait(float $timeout = -1): array|false
```

* **引数** 

    * **`float $timeout`**
      * **機能**：タイムアウト時間、負数は永遠にタイムアウトしないことを意味します
      * **単位**：秒、最小精度はミリ秒(`0.001`秒)
      * **デフォルト値**：`-1`
      * **その他の値**：なし

* **戻り値**

  * 操作に成功すると、子プロセスの`PID`、退出状態コード、どの信号で`KILL`されたかを含む配列が返されます
  * 失敗すると`false`が返されます

!> 各子プロセスが起動した後、親プロセスは必ず协程を派遣して`wait()`(または`waitPid()`)を呼び、回収しなければなりません。そうでなければ、子プロセスはゾンビプロセスになり、オペレーティングシステムのプロセスリソースを無駄にします。  
协程を使用する場合、プロセスを先に作成し、その中に协程を開始する必要があります。逆を行うと、forkの状況が非常に複雑になり、下層で処理が困難になります。

* **例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::wait();
    assert($status['pid'] === $process->pid);
    var_dump($status);
});
```


### waitPid()

上記のwait方法とほぼ同じですが、このAPIは特定のプロセスを待つことができます

!> Swooleバージョン >= `v4.5.0`で利用可能

```php
Swoole\Coroutine\System::waitPid(int $pid, float $timeout = -1): array|false
```

* **引数** 

    * **`int $pid`**
      * **機能**：プロセスID
      * **デフォルト値**：`-1` (任意のプロセスを意味し、この場合wait方法と同等です)
      * **その他の値**：任意の自然数

    * **`float $timeout`**
      * **機能**：タイムアウト時間、負数は永遠にタイムアウトしないことを意味します
      * **単位**：秒、最小精度はミリ秒(`0.001`秒)
      * **デフォルト値**：`-1`
      * **その他の値**：なし

* **戻り値**

  * 操作に成功すると、子プロセスの`PID`、退出状態コード、どの信号で`KILL`されたかを含む配列が返されます
  * 失敗すると`false`が返されます

!> 各子プロセスが起動した後、親プロセスは必ず协程を派遣して`wait()`(または`waitPid()`)を呼び、回収しなければなりません。そうでなければ、子プロセスはゾンビプロセスになり、オペレーティングシステムのプロセスリソースを無駄にします。

* **例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::waitPid($process->pid);
    var_dump($status);
});
```


### waitSignal()

协程バージョンのシグナルリスナーであり、現在の协程をブロックしてシグナルがトリガーされるまで待ちます。`Swoole\Process::signal`や`pcntl_signal`関数を置き換えることができます。

!> Swooleバージョン >= `v4.5.0`で利用可能

```php
Swoole\Coroutine\System::waitSignal(int $signo, float $timeout = -1): bool
```

  * **引数** 

    * **`int $signo`**
      * **機能**：シグナルタイプ
      * **デフォルト値**：なし
      * **その他の値**：SIGシリーズの定数、例えば`SIGTERM`、`SIGKILL`など

    * **`float $timeout`**
      * **機能**：タイムアウト時間、負数は永遠にタイムアウトしないことを意味します
      * **単位**：秒、最小精度はミリ秒(`0.001`秒)
      * **デフォルト値**：`-1`
      * **その他の値**：なし

  * **戻り値**

    * シグナルを受け取ったら`true`が返されます
    * タイムアウトでシグナルを受け取らなかったら`false`が返されます

  * **例**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    Coroutine\run(function () {
        $bool = System::waitSignal(SIGUSR1);
        var_dump($bool);
    });
});
$process->start();
sleep(1);
$process::kill($process->pid, SIGUSR1);
```

### waitEvent()

协程バージョンのシグナルリスナーであり、現在の协程をブロックしてシグナルがトリガーされるまで待ちます。IOイベントを待つことができます。`swoole_event`関連の関数を置き換えることができます。

!> Swooleバージョン >= `v4.5`で利用可能

```php
Swoole\Coroutine\System::waitEvent(mixed $socket, int $events = SWOOLE_EVENT_READ, float $timeout = -1): int | false
```

* **引数** 

    * **`mixed $socket`**
      * **機能**：ファイル記述子 (Socketオブジェクト、リソースなど、fdに変換できる任意のタイプ)
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $events`**
      * **機能**：イベントタイプ
      * **デフォルト値**：`SWOOLE_EVENT_READ`
      * **その他の値**：`SWOOLE_EVENT_WRITE`または`SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE`

    * **`float $timeout`**
      * **機能**：タイムアウト時間、負数は永遠にタイムアウトしないことを意味します
      * **単位**：秒、最小精度はミリ秒(`0.001`秒)
      * **デフォルト値**：`-1`
      * **その他の値**：なし

* **戻り値**

  * トリガーされたイベントタイプと(可能性のある複数のビット)が返され、引数`$events`に入力された値に関連しています
  * 失敗すると`false`が返され、[swoole_last_error](/functions?id=swoole_last_error)を使用してエラー情報を取得できます

* **例**

> 同期で非ブロックのコードはこのAPIを通じて协程非ブロックに変更できます

```php
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = stream_socket_client('tcp://www.qq.com:80', $errno, $errstr, 30);
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
    assert($events === SWOOLE_EVENT_WRITE);
    fwrite($client, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ);
    assert($events === SWOOLE_EVENT_READ);
    $response = fread($client, 8192);
    echo $response;
});
```
