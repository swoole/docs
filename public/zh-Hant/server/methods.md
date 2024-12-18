# 方法


## __construct() 

建立一個[異步IO](/learn?id=同步io異步io)的TCP Server物件。

```php
Swoole\Server::__construct(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```

  * **參數**

    * `string $host`

      * 功能：指定監聽的ip位址。
      * 默认值：無。
      * 其它值：無。

      !> IPv4使用 `127.0.0.1`表示監聽本機，`0.0.0.0`表示監聽所有地址。
      IPv6使用`::1`表示監聽本機，`::` (相當於`0:0:0:0:0:0:0:0`) 表示監聽所有地址。

    * `int $port`

      * 功能：指定監聽的端口，如`9501`。
      * 默认值：無。
      * 其它值：無。

      !> 如果 `$sockType` 值的 [UnixSocket Stream/Dgram](/learn?id=什麼是IPC)，此參數將被忽略。
      監聽小於`1024`端口需要`root`權限。
      如果此端口被占用 `server->start` 時會失敗。

    * `int $mode`

      * 功能：指定運行模式。
      * 默认值：[SWOOLE_PROCESS](/learn?id=swoole_process) 多進程模式（默認）。
      * 其它值：[SWOOLE_BASE](/learn?id=swoole_base) 基本模式，[SWOOLE_THREAD](/learn?id=swoole_thread) 多線程模式（Swoole 6.0可用）。

      ?> `SWOOLE_THREAD`模式下，可以點擊這裡[線程 + 服務端（異步風格）](/thread/thread?id=線程-服務端（異步風格）)查看如何在多線程模式下建立一個服務端。

      !> 從Swoole5開始，運行模式的默認值為`SWOOLE_BASE`。

    * `int $sockType`

      * 功能：指定這組Server的類型。
      * 默认值：無。
      * 其它值：
        * `SWOOLE_TCP/SWOOLE_SOCK_TCP` tcp ipv4 socket
        * `SWOOLE_TCP6/SWOOLE_SOCK_TCP6` tcp ipv6 socket
        * `SWOOLE_UDP/SWOOLE_SOCK_UDP` udp ipv4 socket
        * `SWOOLE_UDP6/SWOOLE_SOCK_UDP6` udp ipv6 socket
        * [SWOOLE_UNIX_DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php) unix socket dgram
        * [SWOOLE_UNIX_STREAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/stream_server.php) unix socket stream 

      !> 使用 `$sock_type` | `SWOOLE_SSL` 可以啟用 `SSL` 隧道加密。啟用 `SSL` 后必須配置。 [ssl_key_file](/server/setting?id=ssl_cert_file) 和 [ssl_cert_file](/server/setting?id=ssl_cert_file)

  * **示例**

```php
$server = new \Swoole\Server($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP);

// 可以混合使用UDP/TCP，同時監聽內網和外網端口，多端口監聽參考 addlistener 小節。
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP); // 添加 TCP
$server->addlistener("192.168.1.100", 9503, SWOOLE_SOCK_TCP); // 添加 Web Socket
$server->addlistener("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDP
$server->addlistener("/var/run/myserv.sock", 0, SWOOLE_UNIX_STREAM); //UnixSocket Stream
$server->addlistener("127.0.0.1", 9502, SWOOLE_SOCK_TCP | SWOOLE_SSL); //TCP + SSL

$port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP); // 系統隨機分配端口，返回值為隨機分配的端口
echo $port->port;
```
  

## set()

用於設置運行時的各項參數。伺服器啟動後通過`$serv->setting`來訪問`Server->set`方法設置的參數數組。

```php
Swoole\Server->set(array $setting): void
```

!> `Server->set` 必須在 `Server->start` 前調用，具體每個配置的意義請參考[此節](/server/setting)

  * **示例**

```php
$server->set(array(
    'reactor_num'   => 2,     // 線程數
    'worker_num'    => 4,     // 進程數
    'backlog'       => 128,   // 設置Listen隊列長度
    'max_request'   => 50,    // 每個進程最大接受請求數
    'dispatch_mode' => 1,     // 數據包分發策略
));
```


## on()

註冊`Server`的事件回調函數。

```php
Swoole\Server->on(string $event, callable $callback): bool
```

!> 重複調用`on`方法時會覆蓋上一次的設定

!> 從`PHP 8.2`開始不再支持直接設置動態屬性，如果`$event`不是`Swoole`規定的事件，會拋出一個的警告

  * **參數**

    * `string $event`

      * 功能：回調事件名稱
      * 默认值：無
      * 其它值：無

      !> 大小寫不敏感，具體有哪些事件回調參考[此節](/server/events)，事件名稱字符串不要加`on`

    * `callable $callback`

      * 功能：回調函數
      * 默认值：無
      * 其它值：無

      !> 可以是函數名的字符串，類靜態方法，物件方法數組，匿名函數 參考[此節](/learn?id=幾種設置回調函數的方式)。
  
  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗。

  * **示例**

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->on('connect', function ($server, $fd){
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, 'Swoole: '.$data);
    $server->close($fd);
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();
```


## addListener()

增加監聽的端口。業務代碼中可以通過調用 [Swoole\Server->getClientInfo](/server/methods?id=getclientinfo) 來獲取某個連接來自於哪個端口。

```php
Swoole\Server->addListener(string $host, int $port, int $sockType): bool|Swoole\Server\Port
```

!> 監聽`1024`以下的端口需要`root`權限  
主伺服器是`WebSocket`或`HTTP`協議，新監聽的`TCP`端口默認會繼承主`Server`的協議設置。必須單獨調用`set`方法設置新的協議才會啟用新協議 [查看詳細說明 ](/server/port)。
可以點擊[這裡](/server/server_port)查看`Swoole\Server\Port`的詳細說明。 

  * **參數**

    * `string $host`

      * 功能：與 `__construct()` 的 `$host` 相同
      * 默认值：與 `__construct()` 的 `$host` 相同
      * 其它值：與 `__construct()` 的 `$host` 相同

    * `int $port`

      * 功能：與 `__construct()` 的 `$port` 相同
      * 默认值：與 `__construct()` 的 `$port` 相同
      * 其它值：與 `__construct()` 的 `$port` 相同

    * `int $sockType`

      * 功能：與 `__construct()` 的 `$sockType` 相同
      * 默认值：與 `__construct()` 的 `$sockType` 相同
      * 其它值：與 `__construct()` 的 `$sockType` 相同
  
  * **返回值**

    * 返回`Swoole\Server\Port`表示操作成功，返回`false`表示操作失敗。
!> - 在 `Unix Socket` 模式下，`$host` 参数必须填写可访问的文件路径，`$port` 参数将被忽略  

- 在 `Unix Socket` 模式下，客户端的 `$fd` 将不再是数字，而是一个文件路径的字符串  
- 在 `Linux` 系统下监听 `IPv6` 端口后，也可以使用 `IPv4` 地址进行连接

## listen()

此方法是 `addlistener` 的别名。

```php
Swoole\Server->listen(string $host, int $port, int $type): bool|Swoole\Server\Port
```

## addProcess()

添加一个用户自定义的工作进程。此函数通常用于创建一个特殊的工作进程，用于监控、上报或者其他特殊的任务。

```php
Swoole\Server->addProcess(Swoole\Process $process): int
```

!> 不需要执行 `start`。在 `Server` 启动时会自动创建进程，并执行指定的子进程函数

  * **参数**
  
    * [Swoole\Process](/process/process)

      * 功能：`Swoole\Process` 对象
      * 默认值：无
      * 其它值：无

  * **返回值**

    * 返回进程 id 编号表示操作成功，否则程序会抛出致命错误。

  * **注意**

    !> - 创建的子进程可以调用 `$server` 对象提供的各个方法，如 `getClientList/getClientInfo/stats`。                                   
    - 在 `Worker/Task` 进程中可以调用 `$process` 提供的方法与子进程进行通信。        
    - 在用户自定义进程中可以调用 `$server->sendMessage` 与 `Worker/Task` 进程通信。      
    - 用户进程内不能使用 `Server->task/taskwait` 接口。              
    - 用户进程内可以使用 `Server->send/close` 等接口。         
    - 用户进程内应当进行 `while(true)`(如下边的示例)或 [EventLoop](/learn?id=什么是eventloop) 循环(例如创建个定时器)，否则用户进程会不停地退出重启。         

  * **生命周期**

    ?> - 用户进程的生存周期与 `Master` 和 [Manager](/learn?id=manager进程) 是相同的，不会受到 [reload](/server/methods?id=reload) 影响。     
    - 用户进程不受 `reload` 指令控制，`reload` 时不会向用户进程发送任何信息。        
    - 在 `shutdown` 关闭服务器时，会向用户进程发送 `SIGTERM` 信号，关闭用户进程。            
    - 自定义进程会托管到 `Manager` 进程，如果发生致命错误，`Manager` 进程会重新创建一个。         
    - 自定义进程也不会触发 `onWorkerStop` 等事件。 

  * **示例**

    ```php
    $server = new Swoole\Server('127.0.0.1', 9501);
    
    /**
     * 用户进程实现了广播功能，循环接收 unixSocket 的消息，并发给服务器的所有连接
     */
    $process = new Swoole\Process(function ($process) use ($server) {
        $socket = $process->exportSocket();
        while (true) {
            $msg = $socket->recv();
            foreach ($server->connections as $conn) {
                $server->send($conn, $msg);
            }
        }
    }, false, 2, 1);
    
    $server->addProcess($process);
    
    $server->on('receive', function ($serv, $fd, $reactor_id, $data) use ($process) {
        // 群发收到的消息
        $socket = $process->exportSocket();
        $socket->send($data);
    });
    
    $server->start();
    ```

    参考 [Process 进程间通讯章节](/process/process?id=exportsocket)。

## start()

启动服务器，监听所有 `TCP/UDP` 端口。

```php
Swoole\Server->start(): bool
```

!> 提示:以下以 [SWOOLE_PROCESS](/learn?id=swoole_process) 模式为例

  * **提示**

    - 启动成功后会创建 `worker_num+2` 个进程。`Master` 进程 + `Manager` 进程 + `serv->worker_num` 个 `Worker` 进程。  
    - 启动失败会立即返回 `false`。
    - 启动成功后将进入事件循环，等待客户端连接请求。`start` 方法之后的代码不会执行。  
    - 服务器关闭后，`start` 函数返回 `true`，并继续向下执行。  
    - 设置了 `task_worker_num` 会增加相应数量的 [Task进程](/learn?id=taskworker进程)。   
    - 方法列表中 `start` 之前的方法仅可在 `start` 调用前使用，在 `start` 之后的方法仅可在 [onWorkerStart](/server/events?id=onworkerstart)、[onReceive](/server/events?id=onreceive) 等事件回调函数中使用。

  * **扩展**
  
    * Master 主进程

      * 主进程内有多个 [Reactor](/learn?id=reactor线程) 线程，基于 `epoll/kqueue/select` 进行网络事件轮询。收到数据后转发到 `Worker` 进程去处理。
    
    * Manager 进程

      * 对所有 `Worker` 进程进行管理，`Worker` 进程生命周期结束或者发生异常时自动回收，并创建新的 `Worker` 进程。
    
    * Worker 进程

      * 对收到的数据进行处理，包括协议解析和响应请求。未设置 `worker_num`，底层会启动与 `CPU` 数量一致的 `Worker` 进程。
      * 启动失败扩展内会抛出致命错误，请检查 `php error_log` 的相关信息。`errno={number}` 是标准的 `Linux Errno`，可参考相关文档。
      * 如果开启了 `log_file` 设置，信息会打印到指定的 `Log` 文件中。

  * **返回值**

    * 返回 `true` 表示操作成功，返回 `false` 表示操作失败

  * **启动失败常见错误**

    * `bind` 端口失败，原因是其他进程已占用了此端口。
    * 未设置必选回调函数，启动失败。
    * `PHP` 代码存在致命错误，请检查 PHP 错误信息 `php_errors.log`。
    * 执行 `ulimit -c unlimited`，打开 `core dump`，查看是否有段错误。
    * 关闭 `daemonize`，关闭 `log`，使错误信息可以打印到屏幕。

## reload()

安全地重启所有 Worker/Task 进程。

```php
Swoole\Server->reload(bool $only_reload_taskworker = false): bool
```

!> 例如：一台繁忙的后端服务器随时都在处理请求，如果管理员通过 `kill` 进程方式来终止/重启服务器程序，可能导致刚好代码执行到一半终止。  
这种情况下会产生数据的不一致。如交易系统中，支付逻辑的下一段是发货，假设在支付逻辑之后进程被终止了。会导致用户支付了货币，但并没有发货，后果非常严重。  
`Swoole` 提供了柔性终止/重启的机制，管理员只需要向 `Server` 发送特定的信号，`Server` 的 `Worker` 进程可以安全的结束。参考 [如何正确的重启服务](/question/use?id=swoole如何正确的重启服务)。

  * **参数**
  
    * `bool $only_reload_taskworker`

      * 功能：是否仅重启 [Task进程](/learn?id=taskworker进程)
      * 默认值：false
      * 其它值：true

!> - `reload` 有保护机制，当一次 `reload` 正在进行时，收到新的重启信号会丢弃。

- 如果设置了 `user/group`，`Worker` 进程可能没有权限向 `master` 进程发送信息，这种情况下必须使用 `root` 账户，在 `shell` 中执行 `kill` 指令进行重启。
- `reload` 指令对 [addProcess](/server/methods?id=addProcess) 添加的用户进程无效。

  * **返回值**

    * 返回 `true` 表示操作成功，返回 `false` 表示操作失败
       
  * **扩展**
  
    * **发送信号**
    
        * `SIGTERM`: 向主进程/管理进程发送此信号服务器将安全终止。
        * 在 PHP 代码中可以调用 `$serv->shutdown()` 完成此操作。
        * `SIGUSR1`: 向主进程/管理进程发送 `SIGUSR1` 信号，将平稳地 `restart` 所有 `Worker` 进程和 `TaskWorker` 进程。
        * `SIGUSR2`: 向主进程/管理进程发送 `SIGUSR2` 信号，将平稳地重启所有 `Task` 进程。
        * 在 PHP 代码中可以调用 `$serv->reload()` 完成此操作。
        
    ```shell
    # 重启所有worker进程
    kill -USR1 主进程PID
    
    # 仅重启task进程
    kill -USR2 主进程PID
    ```
      
      > [参考：Linux信号列表](/other/signal)

    * **Process模式**
    
        在 `Process` 启动的进程中，来自客户端的 `TCP` 连接是在 `Master` 进程内维持的，`worker` 进程的重启和异常退出，不会影响连接本身。

    * **Base模式**
    
        在 `Base` 模式下，客户端连接直接维持在 `Worker` 进程中，因此 `reload` 时会切断所有连接。

    !> `Base` 模式不支持 reload [Task进程](/learn?id=taskworker进程)
    
    * **Reload有效范围**

      `Reload` 操作只能重新载入 `Worker` 进程启动后加载的 PHP 文件，使用 `get_included_files` 函数来列出哪些文件是在 `WorkerStart` 之前就加载的 PHP 文件，在此列表中的 PHP 文件，即使进行了 `reload` 操作也无法重新载入。要关闭服务器重新启动才能生效。

    ```php
    $serv->on('WorkerStart', function(Swoole\Server $server, int $workerId) {
        var_dump(get_included_files()); //此数组中的文件表示进程启动前就加载了，所以无法reload
    });
    ```

    * **APC/OPcache**
    
        如果 `PHP` 开启了 `APC/OPcache`，`reload` 重载入时会受到影响，有 `2` 种解决方案。
        
        * 打开 `APC/OPcache` 的 `stat` 检测，如果发现文件更新 `APC/OPcache` 会自动更新 `OPCode`。
        * 在 `onWorkerStart` 中加载文件（require、include等函数）之前执行 `apc_clear_cache` 或 `opcache_reset` 刷新 `OPCode` 缓存。

  * **注意**

    !> - 平滑重启只对 [onWorkerStart](/server/events?id=onworkerstart) 或 [onReceive](/server/events?id=onreceive) 等在 `Worker` 进程中 `include/require` 的 PHP 文件有效。
    - `Server` 启动前就已经 `include/require` 的 PHP 文件，不能通过平滑重启重新加载。
    - 对于 `Server` 的配置即 `$serv->set()` 中传入的参数设置，必须关闭/重启整个 `Server` 才可以重新加载。
    - `Server` 可以监听一个内网端口，然后可以接收远程的控制命令，去重启所有 `Worker` 进程。
## 停止()

使当前`Worker`进程停止运行，并立即触发`onWorkerStop`回调函数。

```php
Swoole\Server->stop(int $workerId = -1, bool $waitEvent = false): bool
```

  * **参数**

    * `int $workerId`

      * 功能：指定 `worker id`
      * 默认值：-1，表示当前进程
      * 其它值：无

    * `bool $waitEvent`

      * 功能：控制退出策略，`false`表示立即退出，`true`表示等待事件循环为空时再退出
      * 默认值：false
      * 其它值：true

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失败

  * **提示**

    !> -[异步IO](/learn?id=同步io异步io)服务器在调用`stop`退出进程时，可能仍然有事件在等待。比如使用了`Swoole\MySQL->query`，发送了`SQL`语句，但还在等待`MySQL`服务器返回结果。这时如果进程强制退出，`SQL`的执行结果就会丢失了。  
    -设置`$waitEvent = true`后，底层会使用[异步安全重启](/question/use?id=swoole如何正确的重启服务)策略。先通知`Manager`进程，重新启动一个新的`Worker`来处理新的请求。当前旧的`Worker`会等待事件，直到事件循环为空或者超过`max_wait_time`后，退出进程，最大限度的保证异步事件的安全性。


## 关闭服务()

关闭服务。

```php
Swoole\Server->shutdown(): bool
```

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失败

  * **提示**

    * 此函数可以用在`Worker`进程内。
    * 向主进程发送`SIGTERM`也可以实现关闭服务。

```shell
kill -15 主进程PID
```


## 定时器()

添加`tick`定时器，可以自定义回调函数。此函数是 [Swoole\Timer::tick](/timer?id=tick) 的别名。

```php
Swoole\Server->tick(int $millisecond, callable $callback): void
```

  * **参数**

    * `int $millisecond`

      * 功能：间隔时间【毫秒】
      * 默认值：无
      * 其它值：无

    * `callable $callback`

      * 功能：回调函数
      * 默认值：无
      * 其它值：无

  * **注意**
  
    !> -`Worker`进程结束运行后，所有定时器都会自动销毁  
    -`tick/after`定时器不能在`Server->start`之前使用  
    -`Swoole5`之后，该别名使用方法已被删除，请直接使用`Swoole\Timer::tick()`

  * **示例**

    * 在 [onReceive](/server/events?id=onreceive) 中使用

    ```php
    function onReceive(Swoole\Server $server, int $fd, int $reactorId, mixed $data)
    {
        $server->tick(1000, function () use ($server, $fd) {
            $server->send($fd, "hello world");
        });
    }
    ```

    * 在 [onWorkerStart](/server/events?id=onworkerstart) 中使用

    ```php
    function onWorkerStart(Swoole\Server $server, int $workerId)
    {
        if (!$server->taskworker) {
            $server->tick(1000, function ($id) {
              var_dump($id);
            });
        } else {
            //task
            $server->tick(1000);
        }
    }
    ```


## 一次性定时器()

添加一个一次性定时器，执行完成后就会销毁。此函数是 [Swoole\Timer::after](/timer?id=after) 的别名。

```php
Swoole\Server->after(int $millisecond, callable $callback)
```

  * **参数**

    * `int $millisecond`

      * 功能：执行时间【毫秒】
      * 默认值：无
      * 其它值：无
      * 版本影响：在 `Swoole v4.2.10` 以下版本最大不得超过 `86400000`

    * `callable $callback`

      * 功能：回调函数，必须是可以调用的，`callback` 函数不接受任何参数
      * 默认值：无
      * 其它值：无

  * **注意**
  
    !> -定时器的生命周期是进程级的，当使用`reload`或`kill`重启关闭进程时，定时器会全部被销毁  
    -如果有某些定时器存在关键逻辑和数据，请在`onWorkerStop`回调函数中实现，或参考 [如何正确的重启服务](/question/use?id=swoole如何正确的重启服务)  
    -`Swoole5`之后，该别名使用方法已被删除，请直接使用`Swoole\Timer::after()`


## 延迟执行()

延后执行一个函数，是 [Swoole\Event::defer](/event?id=defer) 的别名。

```php
Swoole\Server->defer(Callable $callback): void
```

  * **参数**

    * `Callable $callback`

      * 功能：回调函数【必填】，可以是可执行的函数变量，可以是字符串、数组、匿名函数
      * 默认值：无
      * 其它值：无

  * **注意**

    !> -底层会在[EventLoop](/learn?id=什么是eventloop)循环完成后执行此函数。此函数的目的是为了让一些PHP代码延后执行，程序优先处理其他的`IO`事件。比如某个回调函数有CPU密集计算又不是很着急，可以让进程处理完其他的事件再去CPU密集计算  
    -底层不保证`defer`的函数会立即执行，如果是系统关键逻辑，需要尽快执行，请使用`after`定时器实现  
    -在`onWorkerStart`回调中执行`defer`时，必须要等到有事件发生才会回调
    -`Swoole5`之后，该别名使用方法已被删除，请直接使用`Swoole\Event::defer()`

  * **示例**

```php
function query($server, $db) {
    $server->defer(function() use ($db) {
        $db->close();
    });
}
```


## 清除定时器()

清除`tick/after`定时器，此函数是 [Swoole\Timer::clear](/timer?id=clear) 的别名。

```php
Swoole\Server->clearTimer(int $timerId): bool
```

  * **参数**

    * `int $timerId`

      * 功能：指定定时器id
      * 默认值：无
      * 其它值：无

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失败

  * **注意**

    !> -`clearTimer`仅可用于清除当前进程的定时器     
    -`Swoole5`之后，该别名使用方法已被删除，请直接使用`Swoole\Timer::clear()` 

  * **示例**

```php
$timerId = $server->tick(1000, function ($timerId) use ($server) {
    $server->clearTimer($timerId);//$id是定时器的id
});
```


## 关闭客户端连接()

关闭客户端连接。

```php
Swoole\Server->close(int $fd, bool $reset = false): bool
```

  * **参数**

    * `int $fd`

      * 功能：指定关闭的 `fd` (文件描述符)
      * 默认值：无
      * 其它值：无

    * `bool $reset`

      * 功能：设置为`true`会强制关闭连接，丢弃发送队列中的数据
      * 默认值：false
      * 其它值：true

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失败

  * **注意**
  !> -`伺服器`主動`關閉`連接，同樣會觸發[onClose](/server/events?id=onclose)事件  

-不要在手動關閉後寫清理邏輯。應當放到[onClose](/server/events?id=onclose)回調中處理  
-`HTTP\Server`的`fd`在上層回調方法的`response`中獲取

  * **示範**

```php
$server->on('request', function ($request, $response) use ($server) {
    $server->close($response->fd);
});
```


## send()

向客戶端發送數據。

```php
Swoole\Server->send(int|string $fd, string $data, int $serverSocket = -1): bool
```

  * **參數**

    * `int|string $fd`

      * 功能：指定客戶端的檔案描述符或者unix socket路徑
      * 默认值：無
      * 其它值：無

    * `string $data`

      * 功能：發送的數據，`TCP`協定最大不得超過`2M`，可修改 [buffer_output_size](/server/setting?id=buffer_output_size) 改變允許發送的最大包長度
      * 默认值：無
      * 其它值：無

    * `int $serverSocket`

      * 功能：向[UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php)對端發送數據時需要此參數，TCP客戶端不需要填寫
      * 默认值：-1，表示當前監聽的udp端口
      * 其它值：無

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

  * **提示**

    !> 發送過程是異步的，底層會自動監聽可寫，將數據逐步發送給客戶端，也就是說不是`send`返回後對端就收到數據了。

    * 安全性
      * `send`操作具有原子性，多個進程同時調用`send`向同一個`TCP`連接發送數據，不會發生數據混雜

    * 長度限制
      * 如果要發送超過`2M`的數據，可以將數據寫入臨時檔案，然後通過`sendfile`接口進行發送
      * 通過設置 [buffer_output_size](/server/setting?id=buffer_output_size) 參數可以修改發送長度的限制
      * 在發送超過`8K`的數據時，底層會啟用`Worker`進程的共享內存，需要進行一次`Mutex->lock`操作

    * 緩存區
      * 當`Worker`進程的[unixSocket](/learn?id=什麼是IPC)緩存區已满時，發送`8K`數據將啟用臨時檔案儲存
      * 如果連續向同一個客戶端發送大量數據，客戶端來不及接收會導致`Socket`內存緩存區塞滿，Swoole底層會立即返回`false`,`false`時可以將數據保存到磁盤，等待客戶端收完已發送的數據後再進行發送

    * [協程調度](/coroutine?id=協程調度)
      * 在協程模式下開啟了[send_yield](/server/setting?id=send_yield)情況下`send`遇到緩存區已满時會自動挂起，當數據被對端讀走一部分後恢復協程，繼續發送數據。

    * [UnixSocket](/learn?id=什麼是IPC)
      * 監聽[UnixSocket DGRAM](https://github.com/swoole/swoole-src/blob/master/examples/unixsock/dgram_server.php)端口時，可以使用`send`向對端發送數據。

      ```php
      $server->on("packet", function (Swoole\Server $server, $data, $addr){
          $server->send($addr['address'], 'SUCCESS', $addr['server_socket']);
      });
      ```


## sendfile()

將檔案發送到`TCP`客戶端連接。

```php
Swoole\Server->sendfile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
```

  * **參數**

    * `int $fd`

      * 功能：指定客戶端的檔案描述符
      * 默认值：無
      * 其它值：無

    * `string $filename`

      * 功能：要發送的檔案路徑，如果檔案不存在會返回`false`
      * 默认值：無
      * 其它值：無

    * `int $offset`

      * 功能：指定檔案偏移量，可以從檔案的某個位置起發送數據
      * 默认值：0 【默認為`0`，表示從檔案頭部開始發送】
      * 其它值：無

    * `int $length`

      * 功能：指定發送的长度
      * 默认值：檔案尺寸
      * 其它值：無

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

  * **注意**

  !> 此函數與`Server->send`都是向客戶端發送數據，不同的是`sendfile`的數據來自於指定的檔案


## sendto()

向任意的客戶端`IP:PORT`發送`UDP`數據包。

```php
Swoole\Server->sendto(string $ip, int $port, string $data, int $serverSocket = -1): bool
```

  * **參數**

    * `string $ip`

      * 功能：指定客戶端 `ip`
      * 默认值：無
      * 其它值：無

      ?> `$ip`為`IPv4`或`IPv6`字符串，如`192.168.1.102`。如果`IP`不合法會返回錯誤

    * `int $port`

      * 功能：指定客戶端 `port`
      * 默认值：無
      * 其它值：無

      ?> `$port`為 `1-65535`的網絡端口号，如果端口錯誤發送會失敗

    * `string $data`

      * 功能：要發送的數據內容，可以是文本或者二進制內容
      * 默认值：無
      * 其它值：無

    * `int $serverSocket`

      * 功能：指定使用哪個端口發送數據包的對應端口`server_socket`描述符【可以在[onPacket事件](/server/events?id=onpacket)的`$clientInfo`中獲取】
      * 默认值：-1，表示當前監聽的udp端口
      * 其它值：無

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

      ?> 伺服器可能會同時監聽多個`UDP`端口，參考[多端口監聽](/server/port)，此參數可以指定使用哪個端口發送數據包

  * **注意**

  !> 必須監聽了`UDP`的端口，才可以使用向`IPv4`地址發送數據  
  必須監聽了`UDP6`的端口，才可以使用向`IPv6`地址發送數據

  * **示範**

```php
//向IP地址為220.181.57.216主機的9502端口發送一個hello world字符串。
$server->sendto('220.181.57.216', 9502, "hello world");
//向IPv6伺服器發送UDP數據包
$server->sendto('2600:3c00::f03c:91ff:fe73:e98f', 9501, "hello world");
```


## sendwait()

同步地向客戶端發送數據。

```php
Swoole\Server->sendwait(int $fd, string $data): bool
```

  * **參數**

    * `int $fd`

      * 功能：指定客戶端的檔案描述符
      * 默认值：無
      * 其它值：無

    * `string $data`

      * 功能：要發送的數據
      * 默认值：無
      * 其它值：無

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

  * **提示**

    * 有著一些特殊的場景，`Server`需要連續向客戶端發送數據，而`Server->send`數據發送接口是純異步的，大量數據發送會導致內存發送隊列塞滿。

    * 使用`Server->sendwait`就可以解決此問題，`Server->sendwait`會等待連接可寫。直到數據發送完畢才會返回。

  * **注意**

  !> `sendwait`目前僅可用於[SWOOLE_BASE](/learn?id=swoole_base)模式  
  `sendwait`只用於本機或內網通信，外網連接請勿使用`sendwait`，在`enable_coroutine`=>true(默認開啟)的時候也不要用這個函數，會卡死其他協程，只有同步阻塞的伺服器才可以用。
## 發送消息()

向任意`Worker`进程或者 [Task进程](/learn?id=taskworker进程)發送消息。在非主进程和管理进程中可呼叫。收到消息的进程會觸發`onPipeMessage`事件。

```php
Swoole\Server->sendMessage(mixed $message, int $workerId): bool
```

  * **參數**

    * `mixed $message`

      * 功能：為發送的消息數據內容，沒有長度限制，但超過`8K`時會啟動內存臨時檔案
      * 默认值：無
      * 其它值：無

    * `int $workerId`

      * 功能：目標進程的`ID`，範圍參考[$worker_id](/server/properties?id=worker_id)
      * 默认值：無
      * 其它值：無

  * **提示**

    * 在`Worker`進程內呼叫`sendMessage`是[異步IO](/learn?id=同步io異步io)的，消息會先存到緩衝區，可寫時向[unixSocket](/learn?id=什麼是IPC)發送此消息
    * 在 [Task進程](/learn?id=taskworker進程) 內呼叫`sendMessage`默認是[同步IO](/learn?id=同步io異步io)，但有些情況會自動轉換成異步IO，參考[同步IO轉換成異步IO](/learn?id=同步io轉換成異步io)
    * 在 [User進程](/server/methods?id=addprocess) 內呼叫`sendMessage`和Task一樣，默認同步阻塞的，參考[同步IO轉換成異步IO](/learn?id=同步io轉換成異步io)

  * **注意**


  !> - 如果`sendMessage()`是[異步IO](/learn?id=同步io轉換成異步io)的，如果對端進程因為種種原因不接收數據，千萬不要一直呼叫`sendMessage()`，會導致佔用大量的內存資源。可以增加一個應答機制，如果對端不回應就暫停呼叫；  

-`MacOS/FreeBSD下`超過`2K`就會使用臨時檔案儲存；  

-使用[sendMessage](/server/methods?id=sendMessage)必須註冊`onPipeMessage`事件回調函數；  
-設定了 [task_ipc_mode](/server/setting?id=task_ipc_mode) = 3 將無法使用[sendMessage](/server/methods?id=sendMessage)向特定的task進程發送消息。

  * **示例**

```php
$server = new Swoole\Server('0.0.0.0', 9501);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 2,
));
$server->on('pipeMessage', function ($server, $src_worker_id, $data) {
    echo "#{$server->worker_id} message from #$src_worker_id: $data\n";
});
$server->on('task', function ($server, $task_id, $src_worker_id, $data) {
    var_dump($task_id, $src_worker_id, $data);
});
$server->on('finish', function ($server, $task_id, $data) {

});
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    if (trim($data) == 'task') {
        $server->task("async task coming");
    } else {
        $worker_id = 1 - $server->worker_id;
        $server->sendMessage("hello task process", $worker_id);
    }
});

$server->start();
```


## 存在()

檢測`fd`對應的連接是否存在。

```php
Swoole\Server->exist(int $fd): bool
```

  * **參數**

    * `int $fd`

      * 功能：文件描述符
      * 默认值：無
      * 其它值：無

  * **返回值**

    * 返回`true`表示存在，返回`false`表示不存在

  * **提示**  

    * 此接口是基於共享內存計算，沒有任何`IO`操作


## 暫停()

停止接收數據。

```php
Swoole\Server->pause(int $fd): bool
```

  * **參數**

    * `int $fd`

      * 功能：指定文件描述符
      * 默认值：無
      * 其它值：無

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

  * **提示**

    * 呼叫此函數後會將連接從[EventLoop](/learn?id=什麼是eventloop)中移除，不再接收客戶端數據。
    * 此函數不影響發送隊列的處理
    * 只能在`SWOOLE_PROCESS`模式下，呼叫`pause`後，可能有部分數據已經到達`Worker`進程，因此仍然可能會觸發[onReceive](/server/events?id=onreceive)事件


## 恢復()

恢復數據接收。與`pause`方法成對使用。

```php
Swoole\Server->resume(int $fd): bool
```

  * **參數**

    * `int $fd`

      * 功能：指定文件描述符
      * 默认值：無
      * 其它值：無

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

  * **提示**

    * 呼叫此函數後會將連接重新加入[EventLoop](/learn?id=什麼是eventloop)中，繼續接收客戶端數據


## 取得回調()

取得 Server 指定名稱的回調函數

```php
Swoole\Server->getCallback(string $event_name): \Closure|string|null|array
```

  * **參數**

    * `string $event_name`

      * 功能：事件名稱，不需要加`on`，不區分大小寫
      * 默认值：無
      * 其它值：參考 [事件](/server/events)

  * **返回值**

    * 對應回調函數存在時，根據不同的[回調函數設定方式](/learn?id=四種設定回調函數的方式)返回 `Closure` / `string` / `array`
    * 對應回調函數不存在時，返回`null`


## 取得客戶端資訊()

取得連接的資訊，別名是`Swoole\Server->connection_info()`

```php
Swoole\Server->getClientInfo(int $fd, int $reactorId = -1, bool $ignoreError = false): false|array
```

  * **參數**

    * `int $fd`

      * 功能：指定文件描述符
      * 默认值：無
      * 其它值：無

    * `int $reactorId`

      * 功能：連接所在的[Reactor](/learn?id=reactor線程)線程`ID`，目前沒有任何作用，僅僅是為了保持API兼容
      * 默认值：-1
      * 其它值：無

    * `bool $ignoreError`

      * 功能：是否忽略錯誤，如果設置為`true`，即使連接關閉也會返回連接的資訊，`false`表示連接關閉就返回false
      * 默认值：false
      * 其它值：無

  * **提示**

    * 客戶端證書

      * 仅在[onConnect](/server/events?id=onconnect)觸發的進程中才能取得到證書
      * 格式為`x509`格式，可使用`openssl_x509_parse`函數取得到證書資訊

    * 當使用 [dispatch_mode](/server/setting?id=dispatch_mode) = 1/3 配置時，考慮到這種數據包分發策略用於無狀態服務，當連接斷開後相關資訊會直接從內存中刪除，所以`Server->getClientInfo`是無法取得相關連接資訊的。

  * **返回值**

    * 呼叫失敗返回`false`
    * 呼叫成功返回包含客戶端資訊的`array`

```php
$fd_info = $server->getClientInfo($fd);
var_dump($fd_info);

array(15) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(25)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(39136)
  ["remote_ip"]=>
  string(9) "127.0.0.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1677322106)
  ["last_time"]=>
  int(1677322106)
  ["last_recv_time"]=>
  float(1677322106.901918)
  ["last_send_time"]=>
  float(0)
  ["last_dispatch_time"]=>
  float(0)
  ["close_errno"]=>
  int(0)
  ["recv_queued_bytes"]=>
  int(78)
  ["send_queued_bytes"]=>
  int(0)
}
```
參數 | 作用
---|---
server_port | 服務端監聽端口
server_fd | 服務端fd
socket_fd | 客戶端fd
socket_type | 套接字類型
remote_port | 客戶端端口
remote_ip | 客戶端IP
reactor_id | 來自哪個Reactor線程
connect_time | 客戶端連接到Server的時間，單位秒，由master進程設置
last_time | 最後一次收到數據的時間，單位秒，由master進程設置
last_recv_time | 最後一次收到數據的時間，單位秒，由master進程設置
last_send_time | 最後一次發送數據的時間，單位秒，由master進程設置
last_dispatch_time | worker進程接收數據的時間
close_errno | 連接關閉的錯誤碼，如果連接異常關閉，close_errno的值是非零，可以參考Linux錯誤信息列表
recv_queued_bytes | 等待處理的數據量
send_queued_bytes | 等待發送的數據量
websocket_status | [可選選項] WebSocket連接狀態，當服務器是Swoole\WebSocket\Server時會額外增加此項信息
uid | [可選選項] 使用bind綁定了用戶ID時會額外增加此項信息
ssl_client_cert | [可選選項] 使用SSL隧道加密，並且客戶端設置了證書時會額外添加此項信息




## getClientList()

遍歷當前`Server`所有的客戶端連接，`Server::getClientList`方法是基于共享內存的，不存在`IOWait`，遍歷的速度很快。另外`getClientList`會返回所有`TCP`連接，而不僅僅是當前`Worker`進程的`TCP`連接。別名是`Swoole\Server->connection_list()`

```php
Swoole\Server->getClientList(int $start_fd = 0, int $pageSize = 10): false|array
```

  * **參數**

    * `int $start_fd`

      * 功能：指定起始`fd`
      * 默认值：0
      * 其它值：無

    * `int $pageSize`

      * 功能：每頁取多少條，最大不得超過`100`
      * 默认值：10
      * 其它值：無

  * **返回值**

    * 調用成功將返回一個數字索引陣列，元素是取到的`$fd`。陣列會按從小到大排序。最後一個`$fd`作為新的`start_fd`再次嘗試獲取
    * 調用失敗返回`false`

  * **提示**

    * 推薦使用 [Server::$connections](/server/properties?id=connections) 迭代器來遍歷連接
    * `getClientList`僅可用於`TCP`客戶端，`UDP`服務器需要自行保存客戶端信息
    * [SWOOLE_BASE](/learn?id=swoole_base)模式下只能獲取當前進程的連接

  * **示例**
  
```php
$start_fd = 0;
while (true) {
  $conn_list = $server->getClientList($start_fd, 10);
  if ($conn_list === false || count($conn_list) === 0) {
      echo "finish\n";
      break;
  }
  $start_fd = end($conn_list);
  var_dump($conn_list);
  foreach ($conn_list as $fd) {
      $server->send($fd, "broadcast");
  }
}
```


## bind()

將連接綁定一個用戶定義的`UID`，可以設置[dispatch_mode](/server/setting?id=dispatch_mode)=5設置以此值進行`hash`固定分配。可以保證某一個`UID`的連接全部會分配到同一個`Worker`進程。

```php
Swoole\Server->bind(int $fd, int $uid): bool
```

  * **參數**

    * `int $fd`

      * 功能：指定連接的 `fd`
      * 默认值：無
      * 其它值：無

    * `int $uid`

      * 功能：要綁定的`UID`，必須為非`0`的數字
      * 默认值：無
      * 其它值：`UID`最大不能超過`4294967295`，最小不能小於`-2147483648`

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

  * **提示**

    * 可以使用`$serv->getClientInfo($fd)` 查看連接所綁定`UID`的值
    * 在默认的[dispatch_mode](/server/setting?id=dispatch_mode)=2設置下，`Server`會按照`socket fd`來分配連接數據到不同的`Worker`進程。因為`fd`是不穩定的，一個客戶端斷開後重新連接，`fd`會發生改變。這樣這個客戶端的數據就會被分配到別的`Worker`。使用`bind`之後就可以按照用戶定義的`UID`進行分配。即使斷線重連，相同`UID`的`TCP`連接數據會被分配相同的`Worker`進程。

    * 時序問題

      * 客戶端連接服務器後，連續發送多個包，可能會存在時序問題。在`bind`操作時，後續的包可能已經`dispatch`，這些數據包仍然會按照`fd`取模分配到當前進程。只有在`bind`之後新收到的数据包才會按照`UID`取模分配。
      * 因此如果要使用`bind`機制，網絡通訊協議需要設計握手步驟。客戶端連接成功後，先發一個握手請求，之後客戶端不要發任何包。在服務器`bind`完後，並回應之後。客戶端再發送新的請求。

    * 重新綁定

      * 某些情況下，業務邏輯需要用戶連接重新綁定`UID`。這時可以切斷連接，重新建立`TCP`連接並握手，綁定到新的`UID`。

    * 綁定負數`UID`

      * 如果綁定的`UID`為負數，會被底層轉換為`32位無符號整數`，PHP層需要轉為`32位有符號整數`，可使用：
      
  ```php
  $uid = -10;
  $server->bind($fd, $uid);
  $bindUid = $server->connection_info($fd)['uid'];
  $bindUid = $bindUid >> 31 ? (~($bindUid - 1) & 0xFFFFFFFF) * -1 : $bindUid;
  var_dump($bindUid === $uid);
  ```

  * **注意**


!> -僅在設置`dispatch_mode=5`時有效  

-未綁定`UID`時默认使用`fd`取模進行分配  
-同一個連接只能被`bind`一次，如果已經綁定了`UID`，再次調用`bind`會返回`false`

  * **示例**

```php
$serv = new Swoole\Server('0.0.0.0', 9501);

$serv->fdlist = [];

$serv->set([
    'worker_num' => 4,
    'dispatch_mode' => 5,   //uid dispatch
]);

$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "{$fd} connect, worker:" . $serv->worker_id . PHP_EOL;
});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    $conn = $serv->connection_info($fd);
    print_r($conn);
    echo "worker_id: " . $serv->worker_id . PHP_EOL;
    if (empty($conn['uid'])) {
        $uid = $fd + 1;
        if ($serv->bind($fd, $uid)) {
            $serv->send($fd, "bind {$uid} success");
        }
    } else {
        if (!isset($serv->fdlist[$fd])) {
            $serv->fdlist[$fd] = $conn['uid'];
        }
        print_r($serv->fdlist);
        foreach ($serv->fdlist as $_fd => $uid) {
            $serv->send($_fd, "{$fd} say:" . $data);
        }
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
    echo "{$fd} Close". PHP_EOL;
    unset($serv->fdlist[$fd]);
});

$serv->start();
```
## stats()

取得目前`Server`的活跃`TCP`连线數、啟動時間等資訊，`accept/close`(建立連線/關閉連線)的總次數等資訊。

```php
Swoole\Server->stats(): array
```

  * **範例**

```php
array(25) {
  ["start_time"]=>
  int(1677310656)
  ["connection_num"]=>
  int(1)
  ["abort_count"]=>
  int(0)
  ["accept_count"]=>
  int(1)
  ["close_count"]=>
  int(0)
  ["worker_num"]=>
  int(2)
  ["task_worker_num"]=>
  int(4)
  ["user_worker_num"]=>
  int(0)
  ["idle_worker_num"]=>
  int(1)
  ["dispatch_count"]=>
  int(1)
  ["request_count"]=>
  int(0)
  ["response_count"]=>
  int(1)
  ["total_recv_bytes"]=>
  int(78)
  ["total_send_bytes"]=>
  int(165)
  ["pipe_packet_msg_id"]=>
  int(3)
  ["session_round"]=>
  int(1)
  ["min_fd"]=>
  int(4)
  ["max_fd"]=>
  int(25)
  ["worker_request_count"]=>
  int(0)
  ["worker_response_count"]=>
  int(1)
  ["worker_dispatch_count"]=>
  int(1)
  ["task_idle_worker_num"]=>
  int(4)
  ["tasking_num"]=>
  int(0)
  ["coroutine_num"]=>
  int(1)
  ["coroutine_peek_num"]=>
  int(1)
  ["task_queue_num"]=>
  int(1)
  ["task_queue_bytes"]=>
  int(1)
}
```


參數 | 作用
---|---
start_time | 服務器啟動的時間
connection_num | 當前連線的數量
abort_count | 拒絕了多少個連線
accept_count | 接受了多少個連線
close_count | 關閉的連線數量
worker_num  | 開啟了多少個worker進程
task_worker_num  | 開啟了多少個task_worker進程【`v4.5.7`可用】
user_worker_num  | 開啟了多少個task worker進程
idle_worker_num | 空閒的worker進程數
dispatch_count | Server發送到Worker的包數量【`v4.5.7`可用，僅在[SWOOLE_PROCESS](/learn?id=swoole_process)模式下有效】
request_count | Server收到的請求次數【只有onReceive、onMessage、onRequset、onPacket四種數據請求計算request_count】
response_count | Server返回的響應次數
total_recv_bytes| 數據接收總數
total_send_bytes | 數據發送總數
pipe_packet_msg_id | 進程間通訊id
session_round | 起始session id
min_fd | 最小的連接fd
max_fd | 最大的連接fd
worker_request_count | 當前Worker進程收到的請求次數【worker_request_count超過max_request時工作進程將退出】
worker_response_count | 當前Worker進程響應次數
worker_dispatch_count | master進程向當前Worker進程投遞任務的計數，在[master進程](/learn?id=reactor線程)進行dispatch時增加計數
task_idle_worker_num | 空閒的task進程數
tasking_num | 正在工作的task進程數
coroutine_num | 當前協程數量【用於Coroutine】，想獲取更多資訊參考[此節](/coroutine/gdb)
coroutine_peek_num | 全部協程數量
task_queue_num | 消息隊列中的 task 數量【用於 Task】
task_queue_bytes | 消息隊列的內存占用字節數【用於 Task】


## task()

投遞一個異步任務到`task_worker`池中。此函數是非阻塞的，執行完畢會立即返回。`Worker`進程可以繼續處理新的請求。使用`Task`功能，必須先設置 `task_worker_num`，並且必須設置`Server`的[onTask](/server/events?id=ontask)和[onFinish](/server/events?id=onfinish)事件回調函數。

```php
Swoole\Server->task(mixed $data, int $dstWorkerId = -1, callable $finishCallback): int
```

  * **參數**

    * `mixed $data`

      * 功能：要投遞的任務數據，必須是可序列化的PHP變量
      * 默认值：無
      * 其它值：無

    * `int $dstWorkerId`

      * 功能：可以指定要投遞給哪個 [Task進程](/learn?id=taskworker進程)，傳入 Task 進程的`ID`即可，範圍為`[0, $server->setting['task_worker_num']-1]`
      * 默认值：-1【默認為`-1`表示隨機投遞，底層會自動選擇一個空閒 [Task進程](/learn?id=taskworker進程)】
      * 其它值：`[0, $server->setting['task_worker_num']-1]`

    * `callable $finishCallback`

      * 功能：`finish` 回調函數，如果任務設置了回調函數，`Task`返回結果時會直接執行指定的回調函數，不再執行`Server`的[onFinish](/server/events?id=onfinish)回調，只有在`Worker`進程中投遞任務才可觸發
      * 默认值：`null`
      * 其它值：無

  * **返回值**

    * 調用成功，返回值為整數`$task_id`，表示此任務的`ID`。如果有`finish`回調，[onFinish](/server/events?id=onfinish)回調中會攜帶`$task_id`參數
    * 調用失敗，返回值為`false`，`$task_id`可能為`0`，因此必須使用`===`判斷是否失敗

  * **提示**

    * 此功能用於將慢速的任務異步地去執行，比如一個聊天室伺服器，可以用它來進行發送廣播。當任務完成時，在[task進程](/learn?id=taskworker進程)中調用`$serv->finish("finish")`告訴`worker`進程此任務已完成。當然`Swoole\Server->finish`是可选的。
    * `task`底層使用[unixSocket](/learn?id=什麼是IPC)通訊，是全內存的，沒有`IO`消耗。單進程讀寫性能可達`100萬/s`，不同的進程使用不同的`unixSocket`通訊，可以最大化利用多核。
    * 未指定目標[Task進程](/learn?id=taskworker進程)，調用`task`方法會判斷 [Task進程](/learn?id=taskworker進程)的忙閒狀態，底層只會向處於空閒狀態的[Task進程](/learn?id=taskworker進程)投遞任務。如果所有[Task進程](/learn?id=taskworker進程)均處於忙的狀態，底層會輪詢投遞任務到各個進程。可以使用 [server->stats](/server/methods?id=stats) 方法獲取當前正在排隊的任務數量。
    * 第三個參數，可以直接設置[onFinish](/server/events?id=onfinish)函數，如果任務設置了回調函數，`Task`返回結果時會直接執行指定的回調函數，不再執行`Server`的[onFinish](/server/events?id=onfinish)回調，只有在`Worker`進程中投遞任務才可觸發

    ```php
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    ```

    * `$task_id`是從`0-42`億的整數，在當前進程內是唯一
    * 默认不啟動`task`功能，需要在手動設置`task_worker_num`來啟動此功能
    * `TaskWorker`的數量在[Server->set()](/server/methods?id=set)參數中調整，如`task_worker_num => 64`，表示啟動`64`個進程來接收異步任務

  * **配置參數**

    * `Server->task/taskwait/finish` `3`個方法當傳入的`$data`數據超過`8K`時會啟用臨時文件來保存。當臨時文件內容超過
[server->package_max_length](/server/setting?id=package_max_length) 时底層會拋出一個警告。此警告不影響數據的投遞，過大的`Task`可能會存在性能問題。
    
    ```shell
    WARN: task package is too big.
    ```

  * **單向任務**

    * 從`Master`、`Manager`、`UserProcess`進程中投遞的任務，是單向的，在`TaskWorker`進程中無法使用`return`或`Server->finish()`方法返回結果數據。

  * **注意**
  !> -`task`方法不能在[task进程](/learn?id=taskworker进程)中调用  

-使用`task`必须为`Server`设置[onTask](/server/events?id=ontask)和[onFinish](/server/events?id=onfinish)回调，否则`Server->start`会失败  

-`task`操作的次数必须小于[onTask](/server/events?id=ontask)处理速度，如果投递容量超过处理能力，`task`数据会塞满缓存区，导致`Worker`进程发生阻塞。`Worker`进程将无法接收新的请求  
-使用[addProcess](/server/method?id=addProcess)添加的用户进程中可以使用`task`单向投递任务，但不能返回结果数据。请使用[sendMessage](/server/methods?id=sendMessage)接口与`Worker/Task`进程通信

  * **示例**

```php
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);

$server->set(array(
    'worker_num'      => 2,
    'task_worker_num' => 4,
));

$server->on('Receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
    echo "接收数据" . $data . "\n";
    $data    = trim($data);
    $server->task($data, -1, function (Swoole\Server $server, $task_id, $data) {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });
    $task_id = $server->task($data, 0);
    $server->send($fd, "分发任务，任务id为$task_id\n");
});

$server->on('Task', function (Swoole\Server $server, $task_id, $reactor_id, $data) {
    echo "Tasker进程接收到数据";
    echo "#{$server->worker_id}\tonTask: [PID={$server->worker_pid}]: task_id=$task_id, data_len=" . strlen($data) . "." . PHP_EOL;
    $server->finish($data);
});

$server->on('Finish', function (Swoole\Server $server, $task_id, $data) {
    echo "Task#$task_id finished, data_len=" . strlen($data) . PHP_EOL;
});

$server->on('workerStart', function ($server, $worker_id) {
    global $argv;
    if ($worker_id >= $server->setting['worker_num']) {
        swoole_set_process_name("php {$argv[0]}: task_worker");
    } else {
        swoole_set_process_name("php {$argv[0]}: worker");
    }
});

$server->start();
```

## taskwait()

`taskwait`与`task`方法作用相同，用于投递一个异步的任务到 [task进程](/learn?id=taskworker进程)池去执行。与`task`不同的是`taskwait`是同步等待的，直到任务完成或者超时返回。`$result`为任务执行的结果，由`$server->finish`函数发出。如果此任务超时，这里会返回`false`。

```php
Swoole\Server->taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1): mixed
```

  * **参数**

    * `mixed $data`

      * 功能：投递的任务数据，可以是任意类型，非字符串类型底层会自动进行串化
      * 默认值：无
      * 其它值：无

    * `float $timeout`

      * 功能：超时时间，浮点型，单位为秒，最小支持`1ms`粒度，超过规定时间内 [Task进程](/learn?id=taskworker进程)未返回数据，`taskwait`将返回`false`，不再处理后续的任务结果数据
      * 默认值：0.5
      * 其它值：无

    * `int $dstWorkerId`

      * 功能：指定要给投递给哪个 [Task进程](/learn?id=taskworker进程)，传入 Task 进程的`ID`即可，范围为`[0, $server->setting['task_worker_num']-1]`
      * 默认值：-1【默认为`-1`表示随机投递，底层会自动选择一个空闲 [Task进程](/learn?id=taskworker进程)】
      * 其它值：`[0, $server->setting['task_worker_num']-1]`

  *  **返回值**

      * 返回false表示投递失败
      * 如果`onTask`事件中执行了`finish`方法或者`return`，那么`taskwait`将会返回`onTask`投递的结果。

  * **提示**

    * **协程模式**

      * 从`4.0.4`版本开始`taskwait`方法将支持[协程调度](/coroutine?id=协程调度)，在协程中调用`Server->taskwait()`时将自动进行[协程调度](/coroutine?id=协程调度)，不再阻塞等待。
      * 借助[协程调度](/coroutine?id=协程调度)器，`taskwait`可以实现并发调用。
      * `onTask`事件中只能存在一个return或者一个Server->finish，否则多余的return或者Server->finish执行之后就会提示task[1] has expired警告。

    * **同步模式**

      * 在同步阻塞模式下，`taskwait`需要使用[UnixSocket](/learn?id=什么是IPC)通信和共享内存，将数据返回给`Worker`进程，这个过程是同步阻塞的。

    * **特例**

      * 如果[onTask](/server/events?id=ontask)中没有任何[同步IO](/learn?id=同步io异步io)操作，底层仅有`2`次进程切换的开销，并不会产生`IO`等待，因此这种情况下 `taskwait` 可以视为非阻塞。实际测试[onTask](/server/events?id=ontask)中仅读写`PHP`数组，进行`10`万次`taskwait`操作，总耗时仅为`1`秒，平均每次消耗为`10`微秒

  * **注意**


  !> -`Swoole\Server::finish`,不要使用`taskwait`  
-`taskwait`方法不能在 [task进程](/learn?id=taskworker进程)中调用

## taskWaitMulti()

并发执行多个`task`异步任务，此方法不支持[协程调度](/coroutine?id=协程调度)，会导致其他协程开始，协程环境下需要用下文的`taskCo`。

```php
Swoole\Server->taskWaitMulti(array $tasks, float $timeout = 0.5): false|array
```

  * **参数**

    * `array $tasks`

      * 功能：必须为数字索引数组，不支持关联索引数组，底层会遍历`$tasks`将任务逐个投递到 [Task进程](/learn?id=taskworker进程)
      * 默认值：无
      * 其它值：无

    * `float $timeout`

      * 功能：为浮点型，单位为秒
      * 默认值：0.5秒
      * 其它值：无

  * **返回值**

    * 任务完成或超时，返回结果数组。结果数组中每个任务结果的顺序与`$tasks`对应，如：`$tasks[2]`对应的结果为`$result[2]`
    * 某个任务执行超时不会影响其他任务，返回的结果数据中将不包含超时的任务

  * **注意**

  !> -最大并发任务不得超过`1024`

  * **示例**

```php
$tasks[] = mt_rand(1000, 9999); //任务1
$tasks[] = mt_rand(1000, 9999); //任务2
$tasks[] = mt_rand(1000, 9999); //任务3
var_dump($tasks);

//等待所有Task结果返回，超时为10s
$results = $server->taskWaitMulti($tasks, 10.0);

if (!isset($results[0])) {
    echo "任务1执行超时了\n";
}
if (isset($results[1])) {
    echo "任务2的执行结果为{$results[1]}\n";
}
if (isset($results[2])) {
    echo "任务3的执行结果为{$results[2]}\n";
}
```
## taskCo()

並發執行`Task`並進行[協程調度](/coroutine?id=協程調度)，用於支持協程環境下的`taskWaitMulti`功能。

```php
Swoole\Server->taskCo(array $tasks, float $timeout = 0.5): false|array
```
  
* `$tasks`任務列表，必須為數組。底層會遍歷數組，將每個元素作為`task`投遞到`Task`進程池
* `$timeout` 超時時間，默認為`0.5`秒，當規定時間內任務沒有全部完成，立即中止並返回結果
* 任務完成或超時，返回結果數組。結果數組中每個任務結果的順序與`$tasks`對應，如：`$tasks[2]`對應的結果為`$result[2]`
* 某個任務執行失敗或超時，對應的結果數組項為`false`，如：`$tasks[2]`失敗了，那麼`$result[2]`的值为`false`

!> 最大並發任務不得超過`1024`  

  * **調度過程**

    * `$tasks`列表中的每個任務會隨機投遞到一個`Task`工作進程，投遞完畢後，`yield`讓出當前協程，並設置一個`$timeout`秒的定時器
    * 在`onFinish`中收集對應的任務結果，保存到結果數組中。判斷是否所有任務都返回了結果，如果為否，繼續等待。如果為是，進行`resume`恢復對應協程的運行，並清除超時定時器
    * 在規定時間內任務沒有全部完成，定時器先觸發，底層清除等待狀態。將未完成的任务結果標記為`false`，立即`resume`對應協程

  * **示例**

```php
$server = new Swoole\Http\Server("127.0.0.1", 9502, SWOOLE_BASE);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $serv, $task_id, $worker_id, $data) {
    echo "#{$serv->worker_id}\tonTask: worker_id={$worker_id}, task_id=$task_id\n";
    if ($serv->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "hello world";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('Test End, Result: ' . var_export($result, true));
});

$server->start();
```


## finish()

用於在 [Task進程](/learn?id=taskworker進程)中通知`Worker`進程，投遞的任務已完成。此函數可以傳遞結果數據給`Worker`進程。

```php
Swoole\Server->finish(mixed $data): bool
```

  * **參數**

    * `mixed $data`

      * 功能：任務處理的結果內容
      * 默认值：無
      * 其它值：無

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗

  * **提示**
    * `finish`方法可以連續多次調用，`Worker`進程會多次觸發[onFinish](/server/events?id=onfinish)事件
    * 在[onTask](/server/events?id=ontask)回調函數中調用過`finish`方法後，`return`數據依然會觸發[onFinish](/server/events?id=onfinish)事件
    * `Server->finish`是可选的。如果`Worker`進程不關心任務執行的結果，不需要調用此函數
    * 在[onTask](/server/events?id=ontask)回調函數中`return`字符串，等於調用`finish`

  * **注意**

  !> 使用`Server->finish`函數必須為`Server`設置[onFinish](/server/events?id=onfinish)回調函數。此函數只能用於 [Task進程](/learn?id=taskworker進程)的[onTask](/server/events?id=ontask)回調中


## heartbeat()

與[heartbeat_check_interval](/server/setting?id=heartbeat_check_interval)的被動檢測不同，此方法主動檢測服務器所有連接，並找出已經超過約定時間的連接。如果指定`if_close_connection`，則自動關閉超時的連接。未指定僅返回連接的`fd`數組。

```php
Swoole\Server->heartbeat(bool $ifCloseConnection = true): bool|array
```

  * **參數**

    * `bool $ifCloseConnection`

      * 功能：是否關閉超時的連接
      * 默认值：true
      * 其它值：false

  * **返回值**

    * 調用成功將返回一個連續數組，元素是已關閉的`$fd`
    * 調用失敗返回`false`

  * **示例**

```php
$closeFdArrary = $server->heartbeat();
```


## getLastError()

獲取最近一次操作錯誤的錯誤碼。業務代碼中可以根據錯誤碼類型執行不同的邏輯。

```php
Swoole\Server->getLastError(): int
```

  * **返回值**


錯誤碼 | 解釋
---|---
1001 | 連接已經被`Server`端關閉了，出現這個錯誤一般是代碼中已經執行了`$server->close()`關閉了某個連接，但仍舊調用`$server->send()`向這個連接發送數據
1002 | 連接已被`Client`端關閉了，`Socket`已關閉無法發送數據到對端
1003 | 正在執行`close`，[onClose](/server/events?id=onclose)回調函數中不得使用`$server->send()`
1004 | 連接已關閉
1005 | 連接不存在，傳入`$fd` 可能是錯誤的
1007 | 接收到了超時的數據，`TCP`關閉連接後，可能會有部分數據殘留在[unixSocket](/learn?id=什麼是IPC)緩存區內，這部分數據會被丢弃
1008 | 發送緩存區已滿無法執行`send`操作，出現這個錯誤表示這個連接的對端無法及時收數據導致發送緩存區已塞滿
1202 | 發送的數據超過了 [server->buffer_output_size](/server/setting?id=buffer_output_size) 設置
9007 |僅在使用[dispatch_mode](/server/setting?id=dispatch_mode)=3時出現，表示當前沒有可用的進程，可以調大`worker_num`進程數量


## getSocket()

調用此方法可以得到底層的`socket`句柄，返回的對象為`sockets`資源句柄。

```php
Swoole\Server->getSocket(): false|\Socket
```

!> 此方法需要依賴PHP的`sockets`擴展，並且編譯`Swoole`時需要開啟`--enable-sockets`選項

  * **監聽端口**

    * 使用`listen`方法增加的端口，可以使用`Swoole\Server\Port`對象提供的`getSocket`方法。

    ```php
    $port = $server->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
    $socket = $port->getSocket();
    ```

    * 使用`socket_set_option`函數可以設置更底層的一些`socket`參數。

    ```php
    $socket = $server->getSocket();
    if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
        echo '無法在socket上設置選項: '. socket_strerror(socket_last_error()) . PHP_EOL;
    }
    ```

  * **支持組播**

    * 使用`socket_set_option`設置`MCAST_JOIN_GROUP`參數可以將`Socket`加入組播，監聽網絡組播數據包。

```php
$server = new Swoole\Server('0.0.0.0', 9905, SWOOLE_BASE, SWOOLE_SOCK_UDP);
$server->set(['worker_num' => 1]);
$socket = $server->getSocket();

$ret = socket_set_option(
    $socket,
    IPPROTO_IP,
    MCAST_JOIN_GROUP,
    array(
        'group' => '224.10.20.30', // 表示組播地址
        'interface' => 'eth0' // 表示網絡接口的名稱，可以為數字或字符串，如eth0、wlan0
    )
);

if ($ret === false) {
    throw new RuntimeException('無法加入多播組');
}

$server->on('Packet', function (Swoole\Server $server, $data, $addr) {
    $server->sendto($addr['address'], $addr['port'], "Swoole: $data");
    var_dump($addr, strlen($data));
});

$server->start();
```
## 保護()

設定客戶端連接為保護狀態，不被心跳線程切斷。

```php
Swoole\Server->protect(int $fd, bool $is_protected = true): bool
```

  * **參數**

    * `int $fd`

      * 功能：指定客戶端連接`fd`
      * 默认值：無
      * 其它值：無

    * `bool $is_protected`

      * 功能：設置的狀態
      * 默认值：true 【表示保護狀態】
      * 其它值：false 【表示不保護】

  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失敗


## 確認()

確認連接，與[enable_delay_receive](/server/setting?id=enable_delay_receive)配合使用。當客戶端建立連接後，並不監聽可讀事件，僅觸發[onConnect](/server/events?id=onconnect)事件回調，在[onConnect](/server/events?id=onconnect)回調中執行`confirm`確認連接，這時服務器才會監聽可讀事件，接收來自客戶端連接的數據。

!> Swoole版本 >= `v4.5.0` 可用

```php
Swoole\Server->confirm(int $fd): bool
```

  * **參數**

    * `int $fd`

      * 功能：連接的唯一識別符
      * 默认值：無
      * 其它值：無

  * **返回值**  

    * 確認成功返回`true`
    * `$fd`對應的連接不存在、已關閉或已經處於監聽狀態時，返回`false`，確認失敗

  * **用途**  

    此方法一般用於保護服務器，避免收到流量過載攻擊。當收到客戶端連接時[onConnect](/server/events?id=onconnect)函數觸發，可判斷來源`IP`，是否允許向服務器發送數據。

  * **示例**  
    
```php
//創建Server對象，監聽 127.0.0.1:9501端口
$serv = new Swoole\Server("127.0.0.1", 9501); 
$serv->set([
    'enable_delay_receive' => true,
]);

//監聽連接進入事件
$serv->on('Connect', function ($serv, $fd) {  
    //在這裡檢測這個$fd，沒問題再confirm
    $serv->confirm($fd);
});

//監聽數據接收事件
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "Server: ".$data);
});

//監聽連接關閉事件
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

//啟動服務器
$serv->start(); 
```


## getWorkerId()

獲取當前`Worker`進程`id`（非進程的`PID`），和[onWorkerStart](/server/events?id=onworkerstart)時的`$workerId`一致

```php
Swoole\Server->getWorkerId(): int|false
```

!> Swoole版本 >= `v4.5.0RC1` 可用


## getWorkerPid()

獲取指定`Worker`進程的`PID`

```php
Swoole\Server->getWorkerPid(int $worker_id = -1): int|false
```

  * **參數**

    * `int $worker_id`

      * 功能：獲取指定進程的pid
      * 默认值：-1，【-1表示當前進程】
      * 其它值：無

!> Swoole版本 >= `v4.5.0RC1` 可用


## getWorkerStatus()

獲取`Worker`進程狀態

```php
Swoole\Server->getWorkerStatus(int $worker_id = -1): int|false
```

!> Swoole版本 >= `v4.5.0RC1` 可用

  * **參數**

    * `int $worker_id`

      * 功能：獲取進程狀態
      * 默认值：-1，【-1表示當前進程】
      * 其它值：無

  * **返回值**  
  
    * 返回`Worker`進程狀態，參考進程狀態值
    * 不是`Worker`進程或者進程不存在返回`false`

  * **進程狀態值**

    常量 | 值 | 說明 | 版本依賴
    ---|---|---|---
    SWOOLE_WORKER_BUSY | 1 | 忙碌 | v4.5.0RC1
    SWOOLE_WORKER_IDLE | 2 | 空閒 | v4.5.0RC1
    SWOOLE_WORKER_EXIT | 3 | [reload_async](/server/setting?id=reload_async)啟用的情况下，同一個worker_id可能有2個進程，一個新的一個老的，老進程讀取到的狀態碼是 EXIT。 | v4.5.5


## getManagerPid()

獲取當前服務的`Manager`進程`PID`

```php
Swoole\Server->getManagerPid(): int
```

!> Swoole版本 >= `v4.5.0RC1` 可用


## getMasterPid()

獲取當前服務的`Master`進程`PID`

```php
Swoole\Server->getMasterPid(): int
```

!> Swoole版本 >= `v4.5.0RC1` 可用


## addCommand()

添加一個自定義命令`command`

```php
Swoole\Server->addCommand(string $name, int $accepted_process_types, Callable $callback): bool
```

!> -Swoole版本 >= `v4.8.0` 可用         
  -該函數只能在服務未啟動前調用，存在同名命令的話會直接返回`false`

* **參數**

    * `string $name`

        * 功能：`command` 名稱
        * 默认值：無
        * 其它值：無

    * `int $accepted_process_types`

      * 功能：接受請求的進程類型，想支持多個進程類型的可以通過`|`連接，例如`SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_MANAGER`
      * 默认值：無
      * 其它值：
        * `SWOOLE_SERVER_COMMAND_MASTER` master進程
        * `SWOOLE_SERVER_COMMAND_MANAGER` manager進程
        * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` worker進程
        * `SWOOLE_SERVER_COMMAND_TASK_WORKER` task進程

    * `callable $callback`

        * 功能：回調函數，它擁有兩個入參，一個是`Swoole\Server`的類，另一個是用戶自定義的變量，該變量就是通過`Swoole\Server::command()`的第四個參數傳遞的。
        * 默认值：無
        * 其它值：無

* **返回值**

    * 返回`true`表示添加自定義命令成功，返回`false`表示失敗

## command()

調用定義的自定義命令`command`

```php
Swoole\Server->command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true): false|string|array
```

!>Swoole版本 >= `v4.8.0` 可用，`SWOOLE_PROCESS`和`SWOOLE_BASE`模式下，該函數只能用於`master`進程。  


* **參數**

    * `string $name`

        * 功能：`command` 名稱
        * 默认值：無
        * 其它值：無

    * `int $process_id`

        * 功能：進程ID
        * 默认值：無
        * 其它值：無

    * `int $process_type`

        * 功能：進程請求類型，下面的其他值只能選擇一個。
        * 默认值：無
        * 其它值：
          * `SWOOLE_SERVER_COMMAND_MASTER` master進程
          * `SWOOLE_SERVER_COMMAND_MANAGER` manager進程
          * `SWOOLE_SERVER_COMMAND_EVENT_WORKER` worker進程
          * `SWOOLE_SERVER_COMMAND_TASK_WORKER` task進程

    * `mixed $data`

        * 功能：請求的數據，該數據必須可以序列化
        * 默认值：無
        * 其它值：無

    * `bool $json_decode`

        * 功能：是否使用`json_decode`解析
        * 默认值：true
        * 其它值：false
  
  * **使用示例**
    ```php
    <?php
    use Swoole\Http\Server;
    use Swoole\Http\Request;
    use Swoole\Http\Response;

    $server = new Server('127.0.0.1', 9501, SWOOLE_BASE);
    $server->addCommand('test_getpid', SWOOLE_SERVER_COMMAND_MASTER | SWOOLE_SERVER_COMMAND_EVENT_WORKER,
        function ($server, $data) {
          var_dump($data);
          return json_encode(['pid' => posix_getpid()]);
        });
    $server->set([
        'log_file' => '/dev/null',
        *worker_num' => 2,
    ]);

    $server->on('start', function (Server $serv) {
        $result = $serv->command('test_getpid', 0, SWOOLE_SERVER_COMMAND_MASTER, ['type' => 'master']);
        Assert::eq($result['pid'], $serv->getMasterPid());
        $result = $serv->command('test_getpid', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::eq($result['pid'], $serv->getWorkerPid(1));
        $result = $serv->command('test_not_found', 1, SWOOLE_SERVER_COMMAND_EVENT_WORKER, ['type' => 'worker']);
        Assert::false($result);

        $serv->shutdown();
    });

    $server->on('request', function (Request $request, Response $response) {
    });
    $server->start();
    ```
