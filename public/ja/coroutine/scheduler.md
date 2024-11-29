```
# コロニアル/スケジュール

?> 全ての[コロニアル](/coroutine)は`コロニアルコンテナ`内で[作成](/coroutine/coroutine?id=create)しなければなりません。`Swoole`プログラムが起動する際、ほとんどのケースでは自動的に`コロニアルコンテナ`が作成されます。`Swoole`でプログラムを起動する方法は三つあります：

   - [非同期スタイル](/server/init)のサーバープログラムの[start](/server/methods?id=start)方法を呼びます。この起動方法はイベント回调内で`コロニアルコンテナ`を创建します。参考：[enable_coroutine](/server/setting?id=enable_coroutine)。
   - `Swoole`が提供する2つのプロセス管理モジュール[Process](/process/process)と[Process\Pool](/process/process_pool)の[start](/process/process_pool?id=start)方法を呼びます。この起動方法はプロセスが起動する際に`コロニアルコンテナ`を创建します。これらモジュールのコンストラクタの`enable_coroutine`パラメータを参照してください。
   - その他の直接コロニアルを裸で書く方法でプログラムを起動する場合、まず`コロニアルコンテナ`を作成する必要があります（`Coroutine\run()`関数、JavaやCの`main`関数と理解できます）。例えば：

* **完全なコロニアルHTTPサービスを起動する**

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
echo 1;//実行されません
```

* **2つのコロニアルを添加し、同時に何かをする**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function() {
        var_dump(file_get_contents("http://www.xinhuanet.com/"));
    });

    Coroutine::create(function() {
        Coroutine::sleep(1);
        echo "done\n";
    });
});
echo 1;//実行されます
```

!> `Swoole v4.4+`で利用できます。

!> `Coroutine\run()`をネストすることはできません。  
`Coroutine\run()`内のロジックに未処理のイベントがある場合、`Coroutine\run()`の後に行われる[EventLoop](learn?id=什么是eventloop)では、後のコードは実行されません。逆に、イベントがなければ、コードは続き、再び`Coroutine\run()`を行うことができます。

上記の`Coroutine\run()`関数は実際には`Swoole\Coroutine\Scheduler`クラス（コロニアルスケジュール器クラス）の封装です。詳細を知りたい方は`Swoole\Coroutine\Scheduler`の方法をご覧ください：


### set()

?> **コロニアル実行時のパラメータを設定します。** 

?> `Coroutine::set`メソッドの別名です。参考：[Coroutine::set](/coroutine/coroutine?id=set) 文档

```php
Swoole\Coroutine\Scheduler->set(array $options): bool
```

  * **例**

```php
$sch = new Swoole\Coroutine\Scheduler;
$sch->set(['max_coroutine' => 100]);
```


### getOptions()

?> **設定されたコロニアル実行時のパラメータを取得します。** Swooleバージョン >= `v4.6.0`で利用できます

?> `Coroutine::getOptions`メソッドの別名です。参考：[Coroutine::getOptions](/coroutine/coroutine?id=getoptions) 文档

```php
Swoole\Coroutine\Scheduler->getOptions(): null|array
```


### add()

?> **タスクを追加します。** 

```php
Swoole\Coroutine\Scheduler->add(callable $fn, ... $args): bool
```

  * **パラメータ** 

    * **`callable $fn`**
      * **機能**：回调関数
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`... $args`**
      * **機能**：オプションパラメータで、コロニアルに渡されます
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **例**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;
$scheduler->add(function ($a, $b) {
    Coroutine::sleep(1);
    echo assert($a == 'hello') . PHP_EOL;
    echo assert($b == 12345) . PHP_EOL;
    echo "Done.\n";
}, "hello", 12345);

$scheduler->start();
```
  
  * **注意**

    !> `go`関数とは異なり、ここで追加されたコロニアルは直ちに実行されず、`start`方法が呼び出された時に一緒に起動して実行されます。プログラムにコロニアルのみ追加され、`start`を呼び出していない場合、コロニアル関数`$fn`は実行されません。


### parallel()

?> **並列タスクを追加します。** 

?> `add`メソッドとは異なり、`parallel`メソッドは並列コロニアルを作成します。`start`時には同時に`$num`個の`$fn`コロニアルを起動し、並列して実行されます。

```php
Swoole\Coroutine\Scheduler->parallel(int $num, callable $fn, ... $args): bool
```

  * **パラメータ** 

    * **`int $num`**
      * **機能**：起動するコロニアルの数
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`callable $fn`**
      * **機能**：回调関数
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`... $args`**
      * **機能**：オプションパラメータで、コロニアルに渡されます
      * **デフォルト値**：なし
      * **その他の値**：なし

  * **例**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;

$scheduler->parallel(10, function ($t, $n) {
    Coroutine::sleep($t);
    echo "Co ".Coroutine::getCid()."\n";
}, 0.05, 'A');

$scheduler->start();
```

### start()

?> **プログラムを起動します。** 

?> `add`と`parallel`方法で追加されたコロニアルタスクを遍历し、実行します。

```php
Swoole\Coroutine\Scheduler->start(): bool
```

  * **戻り値**

    * 起動に成功し、すべての追加されたタスクが実行され、すべてのコロニアルが退出した時、`start`は`true`を返します
    * 起動に失敗し`false`を返します。原因はすでに起動しているか、他のスケジュール器が既に存在して再度作成できないためです。
```
