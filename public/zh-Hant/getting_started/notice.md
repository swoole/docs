# 程式設計須知

本章节將詳細介紹協程程式設計與同步程式設計的不同之處以及需要注意的事項。


## 注意事項

* 不要在程式碼中執行`sleep`以及其他睡眠函數，這樣會導致整個進程阻塞；協程中可以使用[Co::sleep()](/coroutine/system?id=sleep)或在[一鍵協程化](/runtime)後使用`sleep`；參考：[sleep/usleep的影響](/getting_started/notice?id=sleepusleep的影響)
* `exit/die`是危險的，會導致`Worker`進程退出；參考：[exit/die函數的影響](/getting_started/notice?id=exitdie函數的影響)
* 可透過`register_shutdown_function`來捕獲致命錯誤，在進程異常退出時做一些清理工作；參考：[捕獲Server運行期致命錯誤](/getting_started/notice?id=捕獲server運行期致命錯誤)
* `PHP`程式碼中如果有例外拋出，必須在回調函數中進行`try/catch`捕獲例外，否則會導致工作進程退出；參考：[捕獲例外和錯誤](/getting_started/notice?id=捕獲例外和錯誤)
* 不支援`set_exception_handler`，必須使用`try/catch`方式處理例外；
* `Worker`進程不得共用同一個`Redis`或`MySQL`等網絡服務客戶端，`Redis/MySQL`建立連接的相關程式碼可以放到`onWorkerStart`回調函數中。參考 [是否可以共用1個Redis或MySQL連接](/question/use?id=是否可以共用1個redis或mysql連接)


## 協程程式設計

使用`Coroutine`特性，請認真閱讀 [協程程式設計須知](/coroutine/notice)


## 並發程式設計

務必注意與`同步阻塞`模式不同，`協程`模式下程式是**並發執行**的，在同一時間內`Server`會存在多個請求，因此**應用程序必須為每個客戶端或請求，建立不同的資源和上下文**。否則不同的客戶端和請求之間可能會產生數據和邏輯錯亂。


## 類別/函數重複定義

新手非常容易犯這個錯誤，由於`Swoole`是常驻內存的，所以加載類別/函數定義的檔案後不會釋放。因此引入類別/函數的php檔案時必須要使用`include_once`或`require_once`，否則會發生`cannot redeclare function/class` 的致命錯誤。


## 記憶體管理

!> 編寫`Server`或其他常驻進程時需要特別注意。

`PHP`守護進程與普通`Web`程式的變數生命周期、記憶體管理方式完全不同。`Server`啟動後記憶體管理的底層原理與普通php-cli程式一致。具體請參考`Zend VM`記憶體管理方面的文章。


### 局部變數

在事件回調函數返回後，所有局部物件和變數會全部回收，不需要`unset`。如果變數是一個資源類型，那麼對應的資源也會被PHP底層釋放。

```php
function test()
{
	$a = new Object;
	$b = fopen('/data/t.log', 'r+');
	$c = new swoole_client(SWOOLE_SYNC);
	$d = new swoole_client(SWOOLE_SYNC);
	global $e;
	$e['client'] = $d;
}
```

* `$a`, `$b`, `$c` 都是局部變數，當此函數`return`時，這`3`個變數會立即釋放，對應的內存會立即釋放，打開的IO資源檔案句柄會立即關閉。
* `$d` 也是局部變數，但是在`return`前將它保存到了全局變量`$e`，所以不會釋放。當執行`unset($e['client'])`時，並且沒有任何其他`PHP變數`仍然在引用`$d`變量，那麼`$d`就會被釋放。


### 全局變數

在`PHP`中，有`3`類全局變數。

* 使用`global`關鍵詞宣告的變數
* 使用`static`關鍵詞宣告的類靜態變數、函數靜態變數
* `PHP`的超全局變數，包括`$_GET`、`$_POST`、`$GLOBALS`等

全局變數和物件，類靜態變數，保存在`Server`物件上的變數不會被釋放。需要程式員自行處理這些變數和物件的銷毀工作。

```php
class Test
{
	static $array = array();
	static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
	Test::$array[] = $fd;
	Test::$string .= $data;
}
```

* 在事件回調函數中需要特別注意非局部變量的`array`類型值，某些操作如  `TestClass::$array[] = "string"` 可能会造成記憶體洩漏，嚴重時可能發生記憶體溢出，必要時應當注意清理大陣列。

* 在事件回調函數中，非局部變量的字符串進行串接操作是必須小心記憶體洩漏，如 `TestClass::$string .= $data`，可能會有記憶體洩漏，嚴重時可能發生記憶體溢出。


### 解決方法

* 同步阻塞並且請求響應式無狀態的`Server`程式可以設置[max_request](/server/setting?id=max_request)和[task_max_request](/server/setting?id=task_max_request)，當 [Worker進程](/learn?id=worker進程) / [Task進程](/learn?id=taskworker進程) 結束運行時或達到任務上限後進程自動退出，該進程的所有變數/物件/資源均會被釋放回收。
* 程式内在`onClose`或設置`定時器`及時使用`unset`清理變數，回收資源。


## 進程隔離

進程隔離也是很多新手經常遇到的問題。修改了全局變量的值，為什麼不生效？原因就是全局變數在不同的進程，內存空間是隔離的，所以無效。

所以使用`Swoole`開發`Server`程式需要了解`進程隔離`問題，`Swoole\Server`程式的不同`Worker`進程之間是隔離的，在編程時操作全局變數、定時器、事件監聽，僅在当前進程內有效。

* 不同的進程中PHP變數不是共享，即使是全局變數，在A進程內修改了它的值，在B進程內是無效的
* 如果需要在不同的Worker進程內共享數據，可以用`Redis`、`MySQL`、`檔案`、`Swoole\Table`、`APCu`、`shmget`等工具實現
* 不同進程的檔案句柄是隔離的，所以在A進程創建的Socket連接或打開的檔案，在B進程內是無效，即使是將它的fd發送到B進程也是不可用的

示範：

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$i = 1;

$server->on('Request', function ($request, $response) {
	global $i;
    $response->end($i++);
});

$server->start();
```

在多進程的伺服器中，`$i`變數雖然是全局變數(`global`)，但由於進程隔離的原因。假設有`4`個工作進程，在`進程1`中進行`$i++`，實際上只有`進程1`中的`$i`變成`2`了，其他另外`3`個進程內`$i`變量的值還是`1`。

正確的做法是使用`Swoole`提供的[Swoole\Atomic](/memory/atomic)或[Swoole\Table](/memory/table)數據結構來保存數據。如上述程式碼可以使用`Swoole\Atomic`實現。

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$atomic = new Swoole\Atomic(1);

$server->on('Request', function ($request, $response) use ($atomic) {
    $response->end($atomic->add(1));
});

$server->start();
```

!> `Swoole\Atomic`數據是建立在共享內存之上的，使用`add`方法加`1`時，在其他工作進程內也是有效的

`Swoole`提供的[Table](/memory/table)、[Atomic](/memory/atomic)、[Lock](/memory/lock)組件是可以用於多進程程式設計的，但必須在`Server->start`之前創建。另外`Server`維持的`TCP`客戶端連接也可以跨進程操作，如`Server->send`和`Server->close`。
## 統計快取清理

PHP底層對`stat`系統調用增加了`Cache`，在使用`stat`、`fstat`、`filemtime`等函數時，底層可能會命中快取，返回歷史數據。

可以使用 [clearstatcache](https://www.php.net/manual/en/function.clearstatcache.php) 函數清理檔案`stat`快取。


## mt_rand隨機數

在`Swoole`中如果在父進程內調用了`mt_rand`，不同的子進程內再調用`mt_rand`返回的結果會是相同的，所以必須在每個子進程內調用`mt_srand`重新種子。

!> `shuffle`和`array_rand`等依賴隨機數的`PHP`函數同樣會受到影響  

示範：

```php
mt_rand(0, 1);

//開始
$worker_num = 16;

//fork 進程
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

//異步執行進程
function child_async(Swoole\Process $worker) {
    mt_srand(); //重新種子
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
```


## 捕獲異常和錯誤


### 可捕獲的異常/錯誤

在`PHP`大約有兩種類型的可捕獲的異常/錯誤

1. `Error`：`PHP`核心拋出錯誤的專用類型，如類不存在，函數不存在，函數參數錯誤，都會拋出此類型的錯誤，`PHP`程式碼中不應該使用`Error類`來作為異常拋出
2. `Exception`：應用開發者應該使用的異常基類
3. `ErrorException`：此異常基類專門負責將`PHP`的`Warning`/`Notice`等信息通過`set_error_handler`轉化成異常，PHP未來的计划必然是將所有的`Warning`/`Notice`轉為異常，以便於`PHP`程式能夠更好更可控地處理各種錯誤

!> 以上所有類都實現了`Throwable`接口，也就是說，通過`try {} catch(Throwable $e) {}` 即可捕獲所有可拋出的異常/錯誤

示範1：
```php
try {
	test();
} 
catch(Throwable $e) {
	var_dump($e);
}
```
示範2：
```php
try {
	test();
}
catch (Error $e) {
	var_dump($e);
}
catch(Exception $e) {
	var_dump($e);
}
```


### 不可捕獲的致命錯誤和異常

`PHP`錯誤的一個重要級別，如異常/錯誤未捕獲時、內存不足時或是一些編譯期錯誤(繼承的類不存在)，將會以`E_ERROR`級別拋出一個`Fatal Error`，是在程式發生不可回溯的錯誤時才會觸發的，`PHP`程式無法捕獲這樣級別的一種錯誤，只能通過`register_shutdown_function`在後續進行一些處理操作。


### 在協程中捕獲運行時異常/錯誤

在`Swoole4`協程編程中，某個協程的程式碼中拋出錯誤，會導致整個進程退出，進程所有協程終止執行。在協程頂層空間可以先進行一次`try/catch`捕獲異常/錯誤，僅終止出错的協程。

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    //協程1的錯誤不影響協程2
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
```


### 捕獲Server運行時致命錯誤

`Server`運行時一旦發生致命錯誤，那客戶端連接將無法得到回應。如Web伺服器，如果有致命錯誤應向客戶端發送`HTTP 500`錯誤信息。

在PHP中可以通過 `register_shutdown_function` + `error_get_last` 兩個函數來捕獲致命錯誤，並將錯誤信息發送給客戶端連接。

具體程式碼示範如下：

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    register_shutdown_function(function () use ($response) {
        $error = error_get_last();
        var_dump($error);
        switch ($error['type'] ?? null) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                // log or send:
                // error_log($message);
                // $server->send($fd, $error['message']);
                $response->status(500);
                $response->end($error['message']);
                break;
        }
    });
    exit(0);
});
$http->start();
```


## 使用影響


### sleep/usleep的影響

在異步IO的程式中，**不得使用sleep/usleep/time_sleep_until/time_nanosleep**。（下文中使用`sleep`泛指所有睡眠函數）

* `sleep`函數會使進程陷入睡眠阻塞
* 直到指定的時間後操作系統才會重新喚醒當前的進程
* `sleep`過程中，只有信號可以 打斷
* 由於`Swoole`的信號處理是基於`signalfd`實現的，所以即使發送信號也無法中斷`sleep`

`Swoole`提供的[Swoole\Event::add](/event?id=add)、[Swoole\Timer::tick](/timer?id=tick)、[Swoole\Timer::after](/timer?id=after)、[Swoole\Process::signal](/process/process?id=signal) 在進程`sleep`後會停止工作。[Swoole\Server](/server/tcp_init)也無法再處理新的請求。

#### 示範

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    sleep(100);
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> 在[onReceive](/server/events?id=onreceive)事件中執行了`sleep`函數，`Server`在100秒內無法再收到任何客戶端請求。


### exit/die函數的影響

在`Swoole`程式中禁止使用`exit/die`，如果PHP程式碼中有`exit/die`，當前工作的[Worker進程](/learn?id=worker進程)、[Task進程](/learn?id=taskworker進程)、[User進程](/server/methods?id=addprocess)、以及`Swoole\Process`進程會立即退出。

使用`exit/die`後`Worker`進程會因為異常退出，被`master`進程再次拉起，最終造成進程不斷退出又不斷啟動和產生大量警報日誌.

建議使用`try/catch`的方式替換`exit/die`，實現中斷執行跳出`PHP`函數調用棧。

```php
Swoole\Coroutine\run(function () {
    try
    {
        exit(0);
    } catch (Swoole\ExitException $e)
    {
        echo $e->getMessage()."\n";
    }
});
```

!> `Swoole\ExitException`是Swoole`v4.1.0`版本及以上直接支持了在協程和`Server`中使用PHP的`exit`，此時底層會自動拋出一個可捕獲的`Swoole\ExitException`，開發者可以在需要的位置捕獲並實現與原生PHP一樣的退出邏輯。具體使用參考[退出協程](/coroutine/notice?id=退出協程);

異常處理的方式比`exit/die`更友好，因為異常是可控的，`exit/die`不可控。在最外層進行`try/catch`即可捕獲異常，僅終止當前的任務。`Worker`進程可以繼續處理新的請求，而`exit/die`會導致進程直接退出，當前進程保存的所有變量和資源都將被摧毀。如果進程內還有其他任務要處理，遇到`exit/die`也將全部丟棄。
### while循环的影响

如果异步程序遇到死循环，事件将无法触发。异步IO程序使用`Reactor模型`，在运行过程中必须在`reactor->wait`处进行轮询。如果出现死循环，程序的控制权就会掌握在`while`循环中，`reactor`将无法获得控制权，也就无法检测事件，因此IO事件回调函数也无法被触发。

!> 密集运算的代码没有任何IO操作，所以不能称为阻塞  

#### 实例程序

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactorId, $data) {
    $i = 0;
    while(1)
    {
        $i++;
    }
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> 在[onReceive](/server/events?id=onreceive)事件中执行了死循环，`server`将无法再接收任何客户端请求，必须等待循环结束后才能继续处理新的事件。
