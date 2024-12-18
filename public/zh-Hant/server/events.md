# 事件

本节将介绍Swoole的所有回调函数，每个回调函数都是一个PHP函数，对应一个事件。

## onStart

?> **在主进程（master）的主线程启动后回调此函数**

```php
function onStart(Swoole\Server $server);
```

  * **参数** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server对象
      * **默认值**：无
      * **其它值**：无

* **在此事件之前`Server`已进行了如下操作**

    * 启动创建完成[Manager 进程](/learn?id=manager进程)
    * 启动创建完成[Worker 子进程](/learn?id=worker进程)
    * 监听所有TCP/UDP/[unixSocket](/learn?id=什么是IPC)端口，但未开始Accept连接和请求
    * 监听了定时器

* **接下来要执行**

    * 主[Reactor](/learn?id=reactor线程)开始接收事件，客户端可以`connect`到`Server`

**`onStart`回调中，仅允许`echo`、打印`Log`、修改进程名称。不得执行其他操作(不能调用`server`相关函数等操作，因为服务尚未就绪)。`onWorkerStart`和`onStart`回调是在不同进程中并行执行的，不存在先后顺序。**

可以在`onStart`回调中，将`$server->master_pid`和`$server->manager_pid`的值保存到一个文件中。这样可以编写脚本，向这两个`PID`发送信号来实现关闭和重启的操作。

`onStart`事件在`Master`进程的主线程中被调用。

!> 在`onStart`中创建的全局资源对象不能在`Worker`进程中被使用，因为发生`onStart`调用时，`worker`进程已经创建好了  
新创建的对象在主进程内，`Worker`进程无法访问到此内存区域  
因此全局对象创建的代码需要放置在`Server::start`之前，典型的例子是[Swoole\Table](/memory/table?id=完整示例)

* **安全提示**

在`onStart`回调中可以使用异步和协程的API，但需要注意这可能会与`dispatch_func`和`package_length_func`存在冲突，**请勿同时使用**。

请不要在`onStart`中启动定时器，如果在代码中执行了`Swoole\Server::shutdown()`操作，会因为始终有一个定时器在执行导致程序无法退出。

`onStart`回调在`return`之前服务器程序不会接受任何客户端连接，因此可以安全地使用同步阻塞的函数。

* **BASE 模式**

[SWOOLE_BASE](/learn?id=swoole_base)模式下没有`master`进程，因此不存在`onStart`事件，请不要在`BASE`模式中使用`onStart`回调函数。

```
WARNING swReactorProcess_start: The onStart event with SWOOLE_BASE is deprecated
```

## onBeforeShutdown

?> **此事件在`Server`正常结束前发生** 

!> Swoole版本 >= `v4.8.0` 可用。在此事件中可以使用协程API。

```php
function onBeforeShutdown(Swoole\Server $server);
```

* **参数**

    * **`Swoole\Server $server`**
        * **功能**：Swoole\Server对象
        * **默认值**：无
        * **其它值**：无

## onShutdown

?> **此事件在`Server`正常结束时发生**

```php
function onShutdown(Swoole\Server $server);
```

  * **参数**

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server对象
      * **默认值**：无
      * **其它值**：无

  * **在此之前`Swoole\Server`已进行了如下操作**

    * 已关闭所有[Reactor](/learn?id=reactor线程)线程、`HeartbeatCheck`线程、`UdpRecv`线程
    * 已关闭所有`Worker`进程、 [Task进程](/learn?id=taskworker进程)、[User进程](/server/methods?id=addprocess)
    * 已`close`所有`TCP/UDP/UnixSocket`监听端口
    * 已关闭主[Reactor](/learn?id=reactor线程)

  !> 强制`kill`进程不会回调`onShutdown`，如`kill -9`  
  需要使用`kill -15`来发送`SIGTERM`信号到主进程才能按照正常的流程终止  
  在命令行中使用`Ctrl+C`中断程序会立即停止，底层不会回调`onShutdown`

  * **注意事项**

  !> 请勿在`onShutdown`中调用任何异步或协程相关`API`，触发`onShutdown`时底层已销毁了所有事件循环设施；  
此时已经不存在协程环境，如果开发者需要使用协程相关`API`需要手动调用`Co\run`来创建[协程容器](/coroutine?id=什么是协程容器)。

## onWorkerStart

?> **此事件在 Worker进程/ [Task进程](/learn?id=taskworker进程) 启动时发生，这里创建的对象可以在进程生命周期内使用。**

```php
function onWorkerStart(Swoole\Server $server, int $workerId);
```

  * **参数** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server对象
      * **默认值**：无
      * **其它值**：无

    * **`int $workerId`**
      * **功能**：`Worker` 进程 `id`（非进程的 PID）
      * **默认值**：无
      * **其它值**：无

  * `onWorkerStart/onStart`是并发执行的，没有先后顺序
  * 可以通过`$server->taskworker`属性来判断当前是`Worker`进程还是 [Task进程](/learn?id=taskworker进程)
  * 设置了`worker_num`和`task_worker_num`超过`1`时，每个进程都会触发一次`onWorkerStart`事件，可通过判断[$worker_id](/server/properties?id=worker_id)区分不同的工作进程
  * 由 `worker` 进程向 `task` 进程发送任务，`task` 进程处理完全部任务之后通过[onFinish](/server/events?id=onfinish)回调函数通知 `worker` 进程。例如，在后台操作向十万个用户群发通知邮件，操作完成后操作的状态显示为发送中，这时可以继续其他操作，等邮件群发完毕后，操作的状态自动改为已发送。

  下面的示例用于为 Worker 进程/ [Task进程](/learn?id=taskworker进程)重命名。

```php
$server->on('WorkerStart', function ($server, $worker_id){
    global $argv;
    if($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});
```

  如果想使用[Reload](/server/methods?id=reload)机制实现代码重载入，必须在`onWorkerStart`中`require`你的业务文件，而不是在文件头部。在`onWorkerStart`调用之前已包含的文件，不会重新载入代码。

  可以将公用的、不易变的php文件放置到`onWorkerStart`之前。这样虽然不能重载入代码，但所有`Worker`是共享的，不需要额外的内存来保存这些数据。
`onWorkerStart`之后的代码每个进程都需要在内存中保存一份

  * `$worker_id`表示这个`Worker`进程的`ID`，范围参考[$worker_id](/server/properties?id=worker_id)
  * [$worker_id](/server/properties?id=worker_id)和进程`PID`没有任何关系，可使用`posix_getpid`函数获取`PID`

  * **协程支持**

    * 在`onWorkerStart`回调函数中会自动创建协程，所以`onWorkerStart`可以调用协程`API`

  * **注意**

    !> 发生致命错误或者代码中主动调用`exit`时，`Worker/Task`进程会退出，管理进程会重新创建新的进程。这可能导致死循环，不停地创建销毁进程
## onWorkerStop

?> **此事件在`Worker`进程终止时发生。在此函数中可以回收`Worker`进程申请的各类资源。**

```php
function onWorkerStop(Swoole\Server $server, int $workerId);
```

  * **参数** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server对象
      * **默认值**：无
      * **其它值**：无

    * **`int $workerId`**
      * **功能**：`Worker` 进程 `id`（非进程的 PID）
      * **默认值**：无
      * **其它值**：无

  * **注意**

    !> -进程异常结束，如被强制`kill`、致命错误、`core dump`时无法执行`onWorkerStop`回调函数。  
    -请勿在`onWorkerStop`中调用任何异步或协程相关`API`，触发`onWorkerStop`时底层已销毁了所有[事件循环](/learn?id=什么是eventloop)设施。


## onWorkerExit

?> **仅在开启[reload_async](/server/setting?id=reload_async)特性后有效。参见 [如何正确的重启服务](/question/use?id=swoole如何正确的重启服务)**

```php
function onWorkerExit(Swoole\Server $server, int $workerId);
```

  * **参数** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server对象
      * **默认值**：无
      * **其它值**：无

    * **`int $workerId`**
      * **功能**：`Worker` 进程 `id`（非进程的 PID）
      * **默认值**：无
      * **其它值**：无

  * **注意**

    !> -`Worker`进程未退出，`onWorkerExit`会持续触发  
    -`onWorkerExit`会在`Worker`进程内触发， [Task进程](/learn?id=taskworker进程)中如果存在[事件循环](/learn?id=什么是eventloop)也会触发  
    -在`onWorkerExit`中尽可能地移除/关闭异步的`Socket`连接，最终底层检测到[事件循环](/learn?id=什么是eventloop)中事件监听的句柄数量为`0`时退出进程  
    -当进程没有事件句柄在监听时，进程结束时将不会回调此函数  
    -等待`Worker`进程退出后才会执行`onWorkerStop`事件回调


## onConnect

?> **有新的连接进入时，在worker进程中回调。**

```php
function onConnect(Swoole\Server $server, int $fd, int $reactorId);
```

  * **参数** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server对象
      * **默认值**：无
      * **其它值**：无

    * **`int $fd`**
      * **功能**：连接的文件描述符
      * **默认值**：无
      * **其它值**：无

    * **`int $reactorId`**
      * **功能**：连接所在的[Reactor](/learn?id=reactor线程)线程`ID`
      * **默认值**：无
      * **其它值**：无

  * **注意**

    !> `onConnect/onClose`这`2`个回调发生在`Worker`进程内，而不是主进程。  
    `UDP`协议下只有[onReceive](/server/events?id=onreceive)事件，没有`onConnect/onClose`事件

    * **[dispatch_mode](/server/setting?id=dispatch_mode) = 1/3**

      * 在此模式下`onConnect/onReceive/onClose`可能会被投递到不同的进程。连接相关的`PHP`对象数据，无法实现在[onConnect](/server/events?id=onconnect)回调初始化数据，[onClose](/server/events?id=onclose)清理数据
      * `onConnect/onReceive/onClose`这3种事件可能会并发执行，可能会带来异常


## onReceive

?> **接收到数据时回调此函数，发生在`worker`进程中。**

```php
function onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data);
```

  * **参数** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server对象
      * **默认值**：无
      * **其它值**：无

    * **`int $fd`**
      * **功能**：连接的文件描述符
      * **默认值**：无
      * **其它值**：无

    * **`int $reactorId`**
      * **功能**：`TCP`连接所在的[Reactor](/learn?id=reactor线程)线程`ID`
      * **默认值**：无
      * **其它值**：无

    * **`string $data`**
      * **功能**：收到的数据内容，可能是文本或者二进制内容
      * **默认值**：无
      * **其它值**：无

  * **关于`TCP`协议下包完整性，参考[TCP数据包边界问题](/learn?id=tcp数据包边界问题)**

    * 使用底层提供的`open_eof_check/open_length_check/open_http_protocol`等配置可以保证数据包的完整性
    * 不使用底层的协议处理，在[onReceive](/server/events?id=onreceive)后PHP代码中自行对数据分析，合并/拆分数据包。

    例如：代码中可以增加一个 `$buffer = array()`，使用`$fd`作为`key`，来保存上下文数据。 每次收到数据进行字符串拼接，`$buffer[$fd] .= $data`，然后在判断`$buffer[$fd]`字符串是否为一个完整的数据包。

    默认情况下，同一个`fd`会被分配到同一个`Worker`中，所以数据可以拼接起来。使用[dispatch_mode](/server/setting?id=dispatch_mode) = 3时，请求数据是抢占式的，同一个`fd`发来的数据可能会被分到不同的进程，所以无法使用上述的数据包拼接方法。

  * **多端口监听，参考[此节](/server/port)**

    当主服务器设置了协议后，额外监听的端口默认会继承主服务器的设置。需要显式调用`set`方法来重新设置端口的协议。    

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9501);
    $port2 = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $port2->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        echo "[#".$server->worker_id."]\tClient[$fd]: $data\n";
    });
    ```

    这里虽然调用了`on`方法注册了[onReceive](/server/events?id=onreceive)回调函数，但由于没有调用`set`方法覆盖主服务器的协议，新监听的`9502`端口依然使用`HTTP`协议。使用`telnet`客户端连接`9502`端口发送字符串时服务器不会触发[onReceive](/server/events?id=onreceive)。

  * **注意**

    !> 未开启自动协议选项，[onReceive](/server/events?id=onreceive)单次收到的数据最大为`64K`  
    开启了自动协议处理选项，[onReceive](/server/events?id=onreceive)将收到完整的数据包，最大不超过 [package_max_length](/server/setting?id=package_max_length)  
    支持二进制格式，`$data`可能是二进制数据
## onPacket

?> **當接收到`UDP`數據包時，此函數會在`worker`進程中被回調。**

```php
function onPacket(Swoole\Server $server, string $data, array $clientInfo);
```

  * **參數** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其他值**：無

    * **`string $data`**
      * **功能**：接收到的數據內容，可能是文本或者二進制內容
      * **預設值**：無
      * **其他值**：無

    * **`array $clientInfo`**
      * **功能**：客戶端資訊包括`address/port/server_socket`等多項客戶端資訊數據，[參考 UDP 伺服器](/start/start_udp_server)
      * **預設值**：無
      * **其他值**：無

  * **注意**

    !> 伺服器同時監聽`TCP/UDP`端口時，收到`TCP`協定的數據會回調[onReceive](/server/events?id=onreceive)，收到`UDP`數據包回調`onPacket`。 伺服器設定的`EOF`或`Length`等自動協定處理([參考TCP數據包邊界問題](/learn?id=tcp數據包邊界問題))，對`UDP`端口是無效的，因為`UDP`包本身存在消息邊界，不需要額外的協定處理。


## onClose

?> **`TCP`客戶端連接關閉後，在`Worker`進程中回調此函數。**

```php
function onClose(Swoole\Server $server, int $fd, int $reactorId);
```

  * **參數** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其他值**：無

    * **`int $fd`**
      * **功能**：連接的文件描述符
      * **預設值**：無
      * **其他值**：無

    * **`int $reactorId`**
      * **功能**：來自哪個`reactor`線程，主動`close`關閉時為負數
      * **預設值**：無
      * **其他值**：無

  * **提示**

    * **主動關閉**

      * 當伺服器主動關閉連接時，底層會設置此參數為`-1`，可以通過判斷`$reactorId < 0`來分辨關閉是由伺服器端還是客戶端發起的。
      * 只有在`PHP`代碼中主動調用`close`方法被視為主動關閉

    * **心跳檢測**

      * [心跳檢測](/server/setting?id=heartbeat_check_interval)是由心跳檢測線程通知關閉的, 關閉時[onClose](/server/events?id=onclose)的`$reactorId`參數不為`-1`

  * **注意**

    !> -[onClose](/server/events?id=onclose) 回調函數如果發生了致命錯誤，會導致連接洩漏。通過 `netstat` 命令會看到大量 `CLOSE_WAIT` 狀態的 `TCP` 連接。
    -無論由客戶端發起`close`還是伺服器端主動調用`$server->close()`關閉連接，都會觸發此事件。因此只要連接關閉，就一定會回調此函數。  
    -[onClose](/server/events?id=onclose)中依然可以調用[getClientInfo](/server/methods?id=getClientInfo)方法獲取到連接資訊，在[onClose](/server/events?id=onclose)回調函數執行完畢後才會調用`close`關閉`TCP`連接。  
    -這裡回調[onClose](/server/events?id=onclose)表示客戶端連接已經關閉，所以無需執行`$server->close($fd)`。代碼中執行`$server->close($fd)`會拋出`PHP`錯誤警告。


## onTask

?> **在`task`進程內被調用。`worker`進程可以使用[task](/server/methods?id=task)函數向`task_worker`進程投遞新的任務。當前的 [Task進程](/learn?id=taskworker進程)在調用[onTask](/server/events?id=ontask)回調函數時會將進程狀態切換為忙碌，這時將不再接收新的Task，當[onTask](/server/events?id=ontask)函數返回時會將進程狀態切換為空闲然後繼續接收新的`Task`。**

```php
function onTask(Swoole\Server $server, int $task_id, int $src_worker_id, mixed $data);
```

  * **參數** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其他值**：無

    * **`int $task_id`**
      * **功能**：執行任務的 `task` 進程 `id`【`$task_id`和`$src_worker_id`組合起來才是全局唯一的，不同的`worker`進程投遞的任務`ID`可能會有相同】
      * **預設值**：無
      * **其他值**：無

    * **`int $src_worker_id`**
      * **功能**：投遞任務的 `worker` 進程 `id`【`$task_id`和`$src_worker_id`組合起來才是全局唯一的，不同的`worker`進程投遞的任務`ID`可能會有相同】
      * **預設值**：無
      * **其他值**：無

    * **`mixed $data`**
      * **功能**：任務的數據內容
      * **預設值**：無
      * **其他值**：無

  * **提示**

    * **v4.2.12起如果開啟了 [task_enable_coroutine](/server/setting?id=task_enable_coroutine) 則回調函數原型是**

      ```php
      $server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
          var_dump($task);
          $task->finish([123, 'hello']); //完成任務，結束並返回數據
      });
      ```

    * **返回執行結果到`worker`進程**

      * **在[onTask](/server/events?id=ontask)函數中 `return` 字符串，表示將此內容返回給 `worker` 進程。`worker` 進程中會觸發 [onFinish](/server/events?id=onfinish) 函數，表示投遞的 `task` 已完成，當然你也可以通過 `Swoole\Server->finish()` 來觸發 [onFinish](/server/events?id=onfinish) 函數，而無需再 `return`**

      * `return` 的變量可以是任意非 `null` 的 `PHP` 變量

  * **注意**

    !> [onTask](/server/events?id=ontask)函數執行時遇到致命錯誤退出，或者被外部進程強製`kill`，當前的任務會被丢弃，但不會影響其他正在排隊的`Task`


## onFinish

?> **此回調函數在worker進程被調用，當`worker`進程投遞的任務在`task`進程中完成時， [task進程](/learn?id=taskworker進程)會通過`Swoole\Server->finish()`方法將任務處理的結果發送給`worker`進程。**

```php
function onFinish(Swoole\Server $server, int $task_id, mixed $data)
```

  * **參數** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其他值**：無

    * **`int $task_id`**
      * **功能**：執行任務的 `task` 進程 `id`
      * **預設值**：無
      * **其他值**：無

    * **`mixed $data`**
      * **功能**：任務處理的結果內容
      * **預設值**：無
      * **其他值**：無

  * **注意**

    !> - [task進程](/learn?id=taskworker進程)的[onTask](/server/events?id=ontask)事件中沒有調用`finish`方法或者`return`結果，`worker`進程不會觸發[onFinish](/server/events?id=onfinish)  
    -執行[onFinish](/server/events?id=onfinish)邏輯的`worker`進程與下發`task`任務的`worker`進程是同一个進程
## onPipeMessage

?> **當工作進程收到由 `$server->sendMessage()` 發送的[unixSocket](/learn?id=什麼是IPC)消息時會觸發 `onPipeMessage` 事件。`worker/task` 進程都可能會觸發 `onPipeMessage` 事件**

```php
function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message);
```

  * **參數** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其它值**：無

    * **`int $src_worker_id`**
      * **功能**：消息來自哪個`Worker`進程
      * **預設值**：無
      * **其它值**：無

    * **`mixed $message`**
      * **功能**：消息內容，可以是任意PHP類型
      * **預設值**：無
      * **其它值**：無


## onWorkerError

?> **當`Worker/Task`進程發生異常後會在`Manager`進程內回調此函數。**

!> 此函數主要用於報警和監控，一旦發現Worker進程異常退出，那麼很有可能是遇到了致命錯誤或者進程Core Dump。通過記錄日誌或者發送報警的信息來提示開發者進行相應的處理。

```php
function onWorkerError(Swoole\Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);
```

  * **參數** 

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其它值**：無

    * **`int $worker_id`**
      * **功能**：異常 `worker` 進程的 `id`
      * **預設值**：無
      * **其它值**：無

    * **`int $worker_pid`**
      * **功能**：異常 `worker` 進程的 `pid`
      * **預設值**：無
      * **其它值**：無

    * **`int $exit_code`**
      * **功能**：退出的狀態碼，範圍是 `0～255`
      * **預設值**：無
      * **其它值**：無

    * **`int $signal`**
      * **功能**：進程退出的信號
      * **預設值**：無
      * **其它值**：無

  * **常見錯誤**

    * `signal = 11`：說明`Worker`進程發生了`segment fault`段錯誤，可能觸發了底層的`BUG`，請收集`core dump`信息和`valgrind`內存檢測日誌，[向Swoole開發組反饋此問題](/other/issue)
    * `exit_code = 255`：說明Worker進程發生了`Fatal Error`致命錯誤，請檢查PHP的錯誤日誌，找到存在問題的PHP代碼，進行解決
    * `signal = 9`：說明`Worker`被系統強行`Kill`，請檢查是否有人為的`kill -9`操作，檢查`dmesg`信息中是否存在`OOM（Out of memory）`
    * 如果存在`OOM`，分配了過大的內存。1.檢查`Server`的`setting`配置，是否[socket_buffer_size](/server/setting?id=socket_buffer_size)等分配過大；2.是否創建了非常大的[Swoole\Table](/memory/table)內存模塊。


## onManagerStart

?> **當管理進程啟動時觸發此事件**

```php
function onManagerStart(Swoole\Server $server);
```

  * **提示**

    * 在這個回調函數中可以修改管理進程的名稱。
    * 在`4.2.12`以前的版本中`manager`進程中不能添加定時器，不能投遞task任務、不能用協程。
    * 在`4.2.12`或更高版本中`manager`進程可以使用基於信號實現的同步模式定時器
    * `manager`進程中可以調用[sendMessage](/server/methods?id=sendMessage)接口向其他工作進程發送消息

    * **啟動順序**

      * `Task`和`Worker`進程已創建
      * `Master`進程狀態不明，因為`Manager`與`Master`是並行的，`onManagerStart`回調發生是不能確定`Master`進程是否已就緒

    * **BASE 模式**

      * 在[SWOOLE_BASE](/learn?id=swoole_base) 模式下，如果設置了`worker_num`、`max_request`、`task_worker_num`參數，底層將創建`manager`進程來管理工作進程。因此會觸發`onManagerStart`和`onManagerStop`事件回調。


## onManagerStop

?> **當管理進程結束時觸發**

```php
function onManagerStop(Swoole\Server $server);
```

 * **提示**

  * `onManagerStop`觸發時，說明`Task`和`Worker`進程已結束運行，已被`Manager`進程回收。


## onBeforeReload

?> **Worker進程`Reload`之前觸發此事件，在Manager進程中回調**

```php
function onBeforeReload(Swoole\Server $server);
```

  * **參數**

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其它值**：無


## onAfterReload

?> **Worker進程`Reload`之後觸發此事件，在Manager進程中回調**

```php
function onAfterReload(Swoole\Server $server);
```

  * **參數**

    * **`Swoole\Server $server`**
      * **功能**：Swoole\Server物件
      * **預設值**：無
      * **其它值**：無


## 事件執行順序

* 所有事件回調均在`$server->start`後發生
* 服務器關閉程序終止時最後一次事件是`onShutdown`
* 服務器啟動成功後，`onStart/onManagerStart/onWorkerStart`會在不同的進程內並發執行
* `onReceive/onConnect/onClose`在`Worker`進程中觸發
* `Worker/Task`進程啟動/結束時會分別調用一次`onWorkerStart/onWorkerStop`
* [onTask](/server/events?id=ontask)事件僅在 [task進程](/learn?id=taskworker進程)中發生
* [onFinish](/server/events?id=onfinish)事件僅在`worker`進程中發生
* `onStart/onManagerStart/onWorkerStart` `3`個事件的執行順序是不確定的

## 面向對象風格

啟用[event_object](/server/setting?id=event_object)後，以下事件回調的參數將有所改變。

* 客戶端連接 [onConnect](/server/events?id=onconnect)
```php
$server->on('Connect', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* 接收數據 [onReceive](/server/events?id=onreceive)
```php
$server->on('Receive', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```

* 連接關閉 [onClose](/server/events?id=onclose)
```php
$server->on('Close', function (Swoole\Server $serv, Swoole\Server\Event $object) {
    var_dump($object);
});
```


* UDP收包 [onPacket](/server/events?id=onpacket)
```php
$server->on('Packet', function (Swoole\Server $serv, Swoole\Server\Packet $object) {
    var_dump($object);
});
```


* 進程間通信 [onPipeMessage](/server/events?id=onpipemessage)
```php
$server->on('PipeMessage', function (Swoole\Server $serv, Swoole\Server\PipeMessage $msg) {
    var_dump($msg);
    $object = $msg->data;
    $serv->sendto($object->address, $object->port, $object->data, $object->server_socket);
});
```


* 進程發生異常 [onWorkerError](/server/events?id=onworkererror)
```php
$serv->on('WorkerError', function (Swoole\Server $serv, Swoole\Server\StatusInfo $info) {
    var_dump($info);
});
```


* task進程接受任務 [onTask](/server/events?id=ontask)
```php
$server->on('Task', function (Swoole\Server $serv, Swoole\Server\Task $task) {
    var_dump($task);
});
```


* worker進程接收task進程的處理結果 [onFinish](/server/events?id=onfinish)
```php
$server->on('Finish', function (Swoole\Server $serv, Swoole\Server\TaskResult $result) {
    var_dump($result);
});
```

* [Swoole\Server\Event](/server/event_class)
* [Swoole\Server\Packet](/server/packet_class)
* [Swoole\Server\PipeMessage](/server/pipemessage_class)
* [Swoole\Server\StatusInfo](/server/statusinfo_class)
* [Swoole\Server\Task](/server/task_class)
* [Swoole\Server\TaskResult](/server/taskresult_class)
