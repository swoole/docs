# Swoole\Process

Swooleが提供するプロセス管理モジュールで、PHPの`pcntl`を置き換えます。

!> このモジュールは比較的低レベルで、オペレーティングシステムのプロセス管理を封装しており、使用者は`Linux`システムのマルチプロセスプログラミングの経験を持つ必要があります。

PHPに付属している`pcntl`には多くの不足があります。例えば：

* プロセス間の通信機能が提供されていません
*標準入力と標準出力の重定向をサポートしていません
* `fork`という原始的なインターフェースしか提供しておらず、誤用しやすいです

`Process`は`pcntl`よりも強力な機能と使いやすいAPIを提供し、PHPのマルチプロセスプログラミングをより簡単にします。

`Process`は以下の特性を提供します：

* プロセス間の通信を容易に実現できます
*標準入力と標準出力の重定向をサポートし、子プロセス内の`echo`は画面に印刷されず、パイプに書き込まれます。キーボード入力はパイプからデータを読み取ることができます
* [exec](/process/process?id=exec)インターフェースを提供し、创建されたプロセスは他のプログラムを実行でき、元のPHP親プロセスと容易に通信できます
* コーoutine環境では`Process`モジュールを使用することはできませんが、`runtime hook`+`proc_open`を使用して実現できます。参考：[コーンプロセス管理](/coroutine/proc_open)

### 使用例

  * 3つの子プロセスを创建し、親プロセスでwaitでプロセスを回収します
  * 親プロセスが異常に退出した場合でも、子プロセスは引き続き実行され、すべてのタスクを完了した後に退出します

```php
use Swoole\Process;

for ($n = 1; $n <= 3; $n++) {
    $process = new Process(function () use ($n) {
        echo 'Child #' . getmypid() . " start and sleep {$n}s" . PHP_EOL;
        sleep($n);
        echo 'Child #' . getmypid() . ' exit' . PHP_EOL;
    });
    $process->start();
}
for ($n = 3; $n--;) {
    $status = Process::wait(true);
    echo "Recycled #{$status['pid']}, code={$status['code']}, signal={$status['signal']}" . PHP_EOL;
}
echo 'Parent #' . getmypid() . ' exit' . PHP_EOL;
```

## 属性


### pipe

[unixSocket](/learn?id=什么是IPC)のファイル記述子です。

```php
public int $pipe;
```


### msgQueueId

メッセージキーの`id`です。

```php
public int $msgQueueId;
```


### msgQueueKey

メッセージキーの`key`です。

```php
public string $msgQueueKey;
```


### pid

現在のプロセスの`pid`です。

```php
public int $pid;
```


### id

現在のプロセスの`id`です。

```php
public int $id;
```


## 定数

パラメータ | 効果
---|---
Swoole\Process::IPC_NOWAIT | メッセージキューにデータがない場合、すぐに戻ります
Swoole\Process::PIPE_READ | 読み取りソケットを閉じます
Swoole\Process::PIPE_WRITE | 書き込みソケットを閉じます


## 方法


### __construct()

コンストラクタです。

```php
Swoole\Process->__construct(callable $function, bool $redirect_stdin_stdout = false, int $pipe_type = SOCK_DGRAM, bool $enable_coroutine = false)
```

* **パラメータ** 

  * **`callable $function`**
    * **機能**：子プロセスが创建された後に実行される関数【底層では自動的に関数をオブジェクトの`callback`属性に保存しますが、この属性は`private`です】。
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`bool $redirect_stdin_stdout`**
    * **機能**：子プロセスの標準入力と標準出力をリダイレクトします。【このオプションを有効にすると、子プロセス内の出力内容は画面に印刷されるのではなく、親プロセスのパイプに書き込まれます。キーボード入力はパイプからデータを読み取るようになります。デフォルトはブロッキング読み取りです。[exec()](/process/process?id=exec)メソッドの内容を参照してください】
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $pipe_type`**
    * **機能**：[unixSocket](/learn?id=什么是IPC)タイプ【`$redirect_stdin_stdout`を有効にした後、このオプションはユーザーパラメータを無視し、強制的に`SOCK_STREAM`になります。子プロセス内でプロセス間通信がない場合は、`0`に設定できます】
    * **デフォルト値**：`SOCK_DGRAM`
    * **その他の値**：`0`、`SOCK_STREAM`

  * **`bool $enable_coroutine`**
    * **機能**：`callback function`内でコーンを有効にします。有効にすると、子プロセスの関数内でのコーンAPIを直接使用できます
    * **デフォルト値**：`false`
    * **その他の値**：`true`
    * **バージョンへの影響**：Swooleバージョン >= v4.3.0

* **[unixSocket](/learn?id=什么是IPC)タイプ**


unixSocketタイプ | 説明
---|---
0 | 作成しない
1 | `SOCK_STREAM](/learn?id=什么是IPC)`タイプのunixSocketを作成する
2 | `SOCK_DGRAM](/learn?id=什么是IPC)`タイプのunixSocketを作成する



### useQueue()

プロセス間通信にメッセージキューを使用します。

```php
Swoole\Process->useQueue(int $key = 0, int $mode = SWOOLE_MSGQUEUE_BALANCE, int $capacity = -1): bool
```

* **パラメータ** 

  * **`int $key`**
    * **機能**：メッセージキューのkeyです。もし渡された値が0以下の場合、底層では`ftok`関数を使用し、現在実行中のファイル名をパラメータとして生成されたkeyを返します。
    * **デフォルト値**：`0`
    * **その他の値**：なし

  * **`int $mode`**
    * **機能**：プロセス間通信モードです。
    * **デフォルト値**：`SWOOLE_MSGQUEUE_BALANCE`で、`Swoole\Process::pop()`はキューの最初のメッセージを返し、`Swoole\Process::push()`はメッセージに特定のタイプを追加しません。
    * **その他の値**：`SWOOLE_MSGQUEUE_ORIENT`で、`Swoole\Process::pop()`はキュー内のタイプが`プロセスid + 1`のメッセージを特定して返し、`Swoole\Process::push()`はメッセージに`プロセスid + 1`のタイプを追加します。

  * **`int $capacity`**
    * **機能**：メッセージキューが保持できるメッセージの最大数です。
    * **デフォルト値**：`-1`
    * **その他の値**：なし

* **注意**

  * メッセージキューにデータがない場合、`Swoole\Porcess->pop()`は常にブロッキングし、またはメッセージキューに新しいデータを格納するスペースがない場合、`Swoole\Porcess->push()`も常にブロッキングします。ブロッキングしたくない場合は、`$mode`の値は`SWOOLE_MSGQUEUE_BALANCE|Swoole\Process::IPC_NOWAIT`または`SWOOLE_MSGQUEUE_ORIENT|Swoole\Process::IPC_NOWAIT`でなければなりません。


### statQueue()

メッセージキューの状態を取得します。

```php
Swoole\Process->statQueue(): array|false
```

* **戻り値** 

  * 成功すると、配列が返ります。配列には2つのキー値ペアが含まれます。`queue_num`は現在キュー内のメッセージの総数を表し、`queue_bytes`は現在キュー内のメッセージの総サイズを表します。
  * 失敗すると`false`が返ります。


### freeQueue()

メッセージキューを破壊します。

```php
Swoole\Process->freeQueue(): bool
```

* **戻り値** 

  * 成功すると`true`が返ります。
  * 失敗すると`false`が返ります。


### pop()

メッセージキューからデータを取得します。

```php
Swoole\Process->pop(int $size = 65536): string|false
```

* **パラメータ** 

  * **`int $size`**
    * **機能**：取得するデータの大きさです。
    * **デフォルト値**：`65536`
    * **その他の値**：なし


* **戻り値** 

  * 成功すると`string`が返ります。
  * 失敗すると`false`が返ります。

* **注意**

  * メッセージキューのタイプが`SW_MSGQUEUE_BALANCE`の場合、キューの最初の情報を返します。
  * メッセージキューのタイプが`SW_MSGQUEUE_ORIENT`の場合、キューの最初のタイプが現在の`プロセスid + 1`の情報です。


### push()

メッセージキューにデータを送信します。

```php
Swoole\Process->push(string $data): bool
```

* **パラメータ** 

  * **`string $data`**
    * **機能**：送信するデータです。
    * **デフォルト値**：``
    * **その他の値**：なし


* **戻り値** 

  * 成功すると`true`が返ります。
  * 失敗すると`false`が返ります。

* **注意**

  * メッセージキューのタイプが`SW_MSGQUEUE_BALANCE`の場合、データは直接メッセージキューに挿入されます。
  * メッセージキューのタイプが`SW_MSGQUEUE_ORIENT`の場合、データには現在の`プロセスid + 1`のタイプが追加されます。


### setTimeout()

メッセージキューの読み取り/書き込みのタイムアウトを設定します。

```php
Swoole\Process->setTimeout(float $seconds): bool
```

* **パラメータ** 

  * **`float $seconds`**
    * **機能**：タイムアウト時間です。
    * **デフォルト値**：`无`
    * **その他の値**：`无`


* **戻り値** 

  * 成功すると`true`が返ります。
  * 失敗すると`false`が返ります。


### setBlocking()

メッセージキューのソケットがブロッキングかどうかを設定します。

```php
Swoole\Process->setBlocking(bool $$blocking): void
```

* **パラメータ** 

  * **`bool $blocking`**
    * **機能**：ブロッキングかどうかです。`true`はブロッキングし、`false`は非ブロッキングです
    * **デフォルト値**：`无`
    * **その他の値**：`无`

* **注意**

  * 新しく创建されたプロセスのソケットはデフォルトでブロッキングです。そのため、UNIXドメインソケット通信を行う際には、メッセージを送信または受信するとプロセスがブロッキングします。


### write()

親プロセスと子プロセス間のメッセージの写入（UNIXドメインソケット）。

```php
Swoole\Process->write(string $data): false|int
```

* **パラメータ** 

  * **`string $data`**
    * **機能**：写入するデータです。
    * **デフォルト値**：`无`
    * **その他の値**：`无`


* **戻り値** 

  * 成功すると`int`が返ります。成功した写入字节数を表します。
  * 失敗すると`false`が返ります。


### read()

親プロセスと子プロセス間のメッセージの読み取り（UNIXドメインソケット）。

```php
Swoole\Process->read(int $size = 8192): false|string
```

* **パラメータ** 

  * **`int $size`**
    * **機能**：読み取るデータの大きさです。
    * **デフォルト値**：`8192`
    * **その他の値**：`无`


* **戻り値** 

  * 成功すると`string`が返ります。
  * 失敗すると`false`が返ります。


### set()

パラメータを設定します。

```php
Swoole\Process->set(array $settings): void
```

`enable_coroutine`を使用してコーンを制御し、コンストラクタの第四パラメータと同じ機能です。

```php
Swoole\Process->set(['enable_coroutine' => true]);
```

!> Swooleバージョン >= v4.4.4で利用可能


### start()

`fork`システム呼び出しを実行し、子プロセスを起動します。Linuxシステムでプロセスを作成するには数百マイクロ秒かかります。

```php
Swoole\Process->start(): int|false
```

* **戻り値**

  * 成功すると子プロセスの`PID`が返ります。
  * 失敗すると`false`が返ります。エラーコードとエラー情報を取得するには[swoole_errno](/functions?id=swoole_errno)と[swoole_strerror](/functions?id=swoole_strerror)を使用してください。

* **注意**

  * 子プロセスは親プロセスのメモリとファイルハンドラを継承します
  * 子プロセスは起動時に親プロセスから継承された[EventLoop](/learn?id=什么是eventloop)、[Signal](/process/process?id=signal)、[Timer](/timer)をクリアします
  
  !> 执行後子プロセスは親プロセスのメモリとリソースを保持します。例えば親プロセス内でredis接続が创建された場合、子プロセスもその接続を保持し、すべての操作は同じ接続に対して行われます。以下に例を示します

```php
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);

function callback_function() {
    swoole_timer_after(1000, function () {
        echo "hello world\n";
    });
    global $redis;//同じ接続
};

swoole_timer_tick(1000, function () {
    echo "parent timer\n";
});//継承しません

Swoole\Process::signal(SIGCHLD, function ($sig) {
    while ($ret = Swoole\Process::wait(false)) {
        // create a new child process
        $p = new Swoole\Process('callback_function');
        $p->start();
    }
});

// create a new child process
$p = new Swoole\Process('callback_function');

$p->start();
```

!> 1. 子プロセスは起動後に自動的に親プロセスから[Swoole\Timer::tick](/timer?id=tick)で创建されたタイマー、[Process::signal](/process/process?id=signal)で監視されるシグナル、[Swoole\Event::add](/event?id=add)で追加されたイベントリスナーをクリアします；  
2. 子プロセスは親プロセスが创建した`$redis`接続对象を継承し、親プロセスと子プロセスで使用される接続は同じです。


### exportSocket()

`unixSocket`を`Swoole\Coroutine\Socket`对象として导出し、その後`Swoole\Coroutine\socket`对象の方法を利用してプロセス間通信を行います。具体的な使用方法は[Coroutine\socket](/coroutine_client/socket)と[IPC通讯](/learn?id=什么是IPC)を参照してください。

```php
Swoole\Process->exportSocket(): Swoole\Coroutine\Socket|false
```

!> この方法を何度も呼び出すと、返される对象は同じです；  
`exportSocket()`で导出された`socket`は新しい`fd`であり、导出した`socket`を閉じてもプロセス内の元のパイプに影響を与えません。  
`Swoole\Coroutine\Socket`对象であるため、必须在[协程容器](/coroutine/scheduler)中使用されるので、Swoole\Processのコンストラクタの`$enable_coroutine`パラメータは必ず`true`でなければなりません。  
同じ親プロセスが`Swoole\Coroutine\Socket`对象を使用したい場合は、手動で`Coroutine\run()`して协程容器を作成する必要があります。

* **戻り値**

  * 成功すると`Coroutine\Socket`对象が返ります。
  * プロセスがunixSocketを作成していない場合、操作に失敗し、`false`が返ります。

* **使用
### name()

プロセスの名前を変更します。この関数は[swoole_set_process_name](/functions?id=swoole_set_process_name)の別名です。

```php
Swoole\Process->name(string $name): bool
```

!> `exec`を実行した後、プロセスの名前は新しいプログラムによって再設定されます。`name`メソッドは`start`の後の子プロセス回调関数で使用されるべきです。


### exec()

外部プログラムを実行します。この関数は`exec`システム呼び出しを封装しています。

```php
Swoole\Process->exec(string $execfile, array $args);
```

* **引数** 

  * **`string $execfile`**
    * **機能**：実行可能なファイルの絶対パスを指定します。例えば `"/usr/bin/python"`
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`array $args`**
    * **機能**：`exec`の引数リスト【例えば `array('test.py', 123)`は `python test.py 123`に相当します】
    * **デフォルト値**：なし
    * **その他の値**：なし

成功すると、現在のプロセスのコード段は新しいプログラムによって置き換えられます。子プロセスは別のプログラムに変わります。親プロセスと現在のプロセスは依然として父子プロセス関係にあります。

親プロセスと新しいプロセスは標準入力出力を通じて通信でき、標準入力出力のリダイレクトを有効にする必要があります。

!> `$execfile`は絶対パスを使用しなければならず、そうでなければファイルが存在しないエラーが発生します。  
`exec`システム呼び出しは指定されたプログラムによって現在のプログラムを置き換えるため、子プロセスは標準出力を読み取り、親プロセスと通信する必要があります。  
`redirect_stdin_stdout = true`を指定しなければ、`exec`を実行した後、子プロセスと親プロセスは通信できません。

* **使用例**

例 1：Swoole\Processで作成された子プロセスで[Swoole\Server](/server/init)を使用できますが、安全のために `$process->start`でプロセスを作成した後に `$worker->exec()`を呼び出して実行する必要があります。コードは以下の通りです：

```php
$process = new Swoole\Process('callback_function', true);

$pid = $process->start();

function callback_function(Swoole\Process $worker)
{
    $worker->exec('/usr/local/bin/php', array(__DIR__.'/swoole_server.php'));
}

Swoole\Process::wait();
```

例 2：Yiiプログラムを起動します

```php
$process = new \Swoole\Process(function (\Swoole\Process $childProcess) {
    // 这种写法はサポートされていません
    // $childProcess->exec('/usr/local/bin/php /var/www/project/yii-best-practice/cli/yii t/index -m=123 abc xyz');

    // execシステム呼び出しを封装
    //絶対パス
    // 引数は配列に分けて渡す必要があります
    $childProcess->exec('/usr/local/bin/php', ['/var/www/project/yii-best-practice/cli/yii', 't/index', '-m=123', 'abc', 'xyz']); // execシステム呼び出し
});
$process->start(); // 子プロセスを起動
```

例3：親プロセスと`exec`子プロセスが標準入力出力で通信する:

```php
// exec - execプロセスとパイプ通信を行います
use Swoole\Process;
use function Swoole\Coroutine\run;

$process = new Process(function (Process $worker) {
    $worker->exec('/bin/echo', ['hello']);
}, true, 1, true); //標準入力出力のリダイレクトを有効にする必要があります

$process->start();

run(function() use($process) {
    $socket = $process->exportSocket();
    echo "from exec: " . $socket->recv() . "\n";
});
```

例4：シェルコマンドを実行します

`exec`メソッドはPHPの`shell_exec`とは異なり、より低レベルのシステム呼び出しの封装です。シェルコマンドを実行する必要がある場合は、以下の方法を使用してください：

```php
$worker->exec('/bin/sh', array('-c', "cp -rf /data/test/* /tmp/test/"));
```


### close()

作成された[unixSocket](/learn?id=什么是IPC)を閉じます。 

```php
Swoole\Process->close(int $which): bool
```

* **引数** 

  * **`int $which`**
    * **機能**：unixSocketは全二重であり、どの端を閉じるかを指定します【デフォルトは`0`で、読み取りと書き取りの両方を閉じます、`1`：書き取りを閉じます、`2`：読み取りを閉じます】
    * **デフォルト値**：`0`、読み取りと書き取りのソケットを閉じます。
    * **その他の値**：`Swoole/Process::SW_PIPE_CLOSE_READ` 読み取りソケットを閉じます、`Swoole/Process::SW_PIPE_CLOSE_WRITE` 書き取りソケットを閉じます、

!> 特定の状況では`Process`オブジェクトが解放できず、プロセスを継続して作成すると接続が漏れる可能性があります。この関数を呼び出すと、直接unixSocketを閉じ、リソースを解放することができます。


### exit()

子プロセスを終了します。

```php
Swoole\Process->exit(int $status = 0);
```

* **引数** 

  * **`int $status`**
    * **機能**：プロセスを終了する状態コード【`0`であれば通常に終了し、後処理を行います】
    * **デフォルト値**：`0`
    * **その他の値**：なし

!> 後処理には以下が含まれます：

  * PHPの`shutdown_function`
  * オブジェクトの析構(`__destruct`)
  * その他の拡張の`RSHUTDOWN`関数

`$status`が`0`でなければ、異常終了を意味し、プロセスは直ちに終了し、関連するプロセスの後処理は実行されません。

親プロセスでは、`Process::wait`を実行することで子プロセスの終了イベントと状態コードを取得できます。


### kill()

指定された`pid`のプロセスにシグナルを送信します。

```php
Swoole\Process::kill(int $pid, int $signo = SIGTERM): bool
```

* **引数** 

  * **`int $pid`**
    * **機能**：プロセスの `pid`
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $signo`**
    * **機能**：送信するシグナル【`$signo=0`ではプロセスが存在するかを確認し、シグナルは送信されません】
    * **デフォルト値**：`SIGTERM`
    * **その他の値**：なし


### signal()

非同期シグナルリスナーを設定します。

```php
Swoole\Process::signal(int $signo, callable $callback): bool
```

この方法は`signalfd`と[EventLoop](/learn?id=什么是eventloop)に基づいており、非同期`IO`です。ブロックするプログラムには使用できず、登録されたリスナー回调関数はスケジュールされない可能性があります。

同期でブロックするプログラムでは、`pcntl`拡張の`pcntl_signal`を使用できます。

このシグナルの回调関数を一度設定した後、再設定すると歴史的な設定が上書きされます。

* **引数** 

  * **`int $signo`**
    * **機能**：シグナル
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`callable $callback`**
    * **機能**：回调関数【`$callback`が`null`であれば、シグナルリスナーを移除します】
    * **デフォルト値**：なし
    * **その他の値**：なし

!> [Swoole\Server](/server/init)では一部のシグナルリスナーを設定することはできません。例えば`SIGTERM`や`SIGALRM`などです。

* **使用例**

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
```

!> `v4.4.0`バージョンでは、プロセスの[EventLoop](/learn?id=什么是eventloop)にシグナルリスナー以外のイベント（例えばTimerタイマーなど）がない場合、プロセスは直接終了します。

```php
Swoole\Process::signal(SIGTERM, function($signo) {
     echo "shutdown.";
});
Swoole\Event::wait();
```

上記のプロセスは[EventLoop](/learn?id=什么是eventloop)に入らず、`Swoole\Event::wait()`は直ちに戻り、プロセスを終了します。


### wait()

終了した子プロセスを回収します。

!> Swooleバージョン >= `v4.5.0` の場合、协程バージョンの`wait()`をお勧めします。参考：[Swoole\Coroutine\System::wait()](/coroutine/system?id=wait)

```php
Swoole\Process::wait(bool $blocking = true): array|false
```

* **引数** 

  * **`bool $blocking`**
    * **機能**：ブロックするかどうかを指定します【デフォルトはブロックします】
    * **デフォルト値**：`true`
    * **その他の値**：`false`

* **戻り値**

  * 成功すると、子プロセスの`PID`、退出状態コード、どのシグナルで`KILL`されたかを含む配列が返ります。
  * 失敗すると`false`が返ります。

!> 各子プロセスが終了した後、親プロセスは必ず一度`wait()`を呼び出して回収する必要があります。そうでなければ、子プロセスはゾンビプロセスになり、オペレーティングシステムのプロセスリソースを無駄にします。  
親プロセスが他のタスクをしなければならないため、`wait`をブロックさせて待つことができない場合、親プロセスは退出了プロセスに対する`SIGCHLD`シグナルを登録し、退出したプロセスに対して`wait`を実行する必要があります。  
SIGCHILDシグナルが発生すると同時に複数の子プロセスが退出することがあります。`wait()`を非ブロックに設定し、`wait`を繰り返し実行して`false`が返るまで続けなければなりません。

* **例**

```php
Swoole\Process::signal(SIGCHLD, function ($sig) {
    // 非阻塞モードでなければなりません
    while ($ret = Swoole\Process::wait(false)) {
        echo "PID={$ret['pid']}\n";
    }
});
```


### daemon()

現在のプロセスを守护プロセスに変換します。

```php
Swoole\Process::daemon(bool $nochdir = true, bool $noclose = true): bool
```

* **引数** 

  * **`bool $nochdir`**
    * **機能**：現在のディレクトリをrootディレクトリに変更するかどうかにします【`true`であれば現在のディレクトリをrootディレクトリに変更しません】
    * **デフォルト値**：`true`
    * **その他の値**：`false`

  * **`bool $noclose`**
    * **機能**：標準入力出力のファイル記述子を閉じるかどうかにします【`true`であれば標準入力出力のファイル記述子を閉じません】
    * **デフォルト値**：`true`
    * **その他の値**：`false`

!> 守护プロセスに変換すると、そのプロセスの`PID`が変わります。現在の`PID`を取得するには`getmypid()`を使用できます。


### alarm()

高精度なタイマーであり、オペレーティングシステムの`setitimer`システム呼び出しを封装しています。微秒レベルのタイマーを設定できます。タイマーがトリガーするとシグナルを発信し、[Process::signal](/process/process?id=signal)または`pcntl_signal`と組み合わせて使用する必要があります。

!> `alarm`は[Timer](/timer)と同時に使用することはできません

```php
Swoole\Process->alarm(int $time, int $type = 0): bool
```

* **引数** 

  * **`int $time`**
    * **機能**：タイマーの間隔時間【負数であればタイマーをクリアします】
    * **値の単位**：マイクロ秒
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`int $type`**
    * **機能**：タイマーのタイプ
    * **デフォルト値**：`0`
    * **その他の値**：


タイマーのタイプ | 説明
---|---
0 | 真時間で、`SIGALRM`シグナルを発信します
1 | ユーザーモードのCPU時間で、`SIGVTALAM`シグナルを発信します
2 | ユーザーモード+カーネルモードの時間で、`SIGPROF`シグナルを発信します

* **戻り値**

  * 成功すると`true`が返ります。
  * 失敗すると`false`が返ります。エラーコードは`swoole_errno`を使用して取得できます。

* **使用例**

```php
use Swoole\Process;
use function Swoole\Coroutine\run;

run(function () {
    Process::signal(SIGALRM, function () {
        static $i = 0;
        echo "#{$i}\talarm\n";
        $i++;
        if ($i > 20) {
            Process::alarm(-1);
            Process::kill(getmypid());
        }
    });

    //100ms
    Process::alarm(100 * 1000);

    while(true) {
        sleep(0.5);
    }
});
```


### setAffinity()

CPUアフィニティを設定し、プロセスを特定のCPUコアに縛り付けます。 

この関数は、プロセスを特定の数個のCPUコアで実行させ、他のCPUリソースをより重要なプログラムに割り当てることを目的としています。

```php
Swoole\Process->setAffinity(array $cpus): bool
```

* **引数** 

  * **`array $cpus`**
    * **機能**：CPUコアを縛り付ける 【例えば `array(0,2,3)` 表示 CPU0/CPU2/CPU3 を縛り付ける】
    * **デフォルト値**：なし
    * **その他の値**：なし


!> - `$cpus` 内の要素は CPU コアの数を超えてはいけません；  

- `CPU-ID` は (CPU コア数 - `1`) を超えてはいけません；  

- この関数はオペレーティングシステムが CPU 縛りを設定することをサポートしている必要があります；  
- [swoole_cpu_num()](/functions?id=swoole_cpu_num) を使用すると、現在のサーバーの CPU コア数を取得できます。


### getAffinity()
プロセスの CPU アフィニティを取得します。

```php
Swoole\Process->getAffinity(): array
```
戻り値は配列で、要素は CPU コア数です。例えば、`[0, 1, 3, 4]` 表示このプロセスは CPU の `0/1/3/4` コアで実行されることを意味します。


### setPriority()

プロセス、プロセスグループ、およびユーザープロセスの優先级を設定します。

!> Swooleバージョン >= `v4.5.9` 可用

```php
Swoole\Process->setPriority(int $which, int $priority): bool
```

* **引数** 

  * **`int $which`**
    * **機能**：優先级を変更するタイプを決定します
    * **デフォルト値**：无
    * **其它值**：

| 常量         | 说明     |
| ------------ | -------- |
| PRIO_PROCESS | 进程     |
| PRIO_PGRP    | 进程组   |
| PRIO_USER    | 用户进程 |

  * **`int $priority`**
    * **機能**：優先级。値が小さければ小さいほど優先级が高くなります
    * **デフォルト値**：无
    * **其它值**：`[-20, 20]`

* **返回值**

  * 如果返回`false`，可使用[swoole_errno](/functions?id=swoole_errno)和[swoole_strerror](/functions?id=swoole_strerror)得到错误码和错误信息。

### getPriority()

获取进程的优先级。

!> Swoole版本
