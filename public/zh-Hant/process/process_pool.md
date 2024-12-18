# Swoole\Process\Pool

进程池，基于[Swoole\Server](/server/init)的Manager管理进程模块实现。可管理多个工作进程。该模块的核心功能为进程管理，相比`Process`实现多进程，`Process\Pool`更加简单，封装层次更高，开发者无需编写过多代码即可实现进程管理功能，配合[Co\Server](/coroutine/server?id=完整示例)可以创建纯协程风格的，能利用多核CPU的服务端程序。

## 进程间通信

`Swoole\Process\Pool`一共提供了三种进程间通信的方式：

### 消息队列
`Swoole\Process\Pool->__construct`的第二个参数设置为`SWOOLE_IPC_MSGQUEUE`时，表示使用消息队列进行进程间通信。可以通过`php sysvmsg`这个扩展投递信息，消息最大不能超过`65536`。

* **注意**

  * 如果要使用`sysvmsg`扩展投递信息，构造函数中必须传入`msgqueue_key`
  * `Swoole`底层不支持`sysvmsg`扩展`msg_send`的第二个参数`mtype`，请传入任意非`0`值

### Socket通信
`Swoole\Process\Pool->__construct`的第二个参数设置为`SWOOLE_IPC_SOCKET`时，表示使用`Socket通信`，如果你的客户端和服务端不在同一台机器上，那就可以使用这个方式进行通信。

通过[Swoole\Process\Pool->listen()](/process/process_pool?id=listen)方法监听端口，使用[Message事件](/process/process_pool?id=on)接收客户端发过来的数据，使用[Swoole\Process\Pool->write()](/process/process_pool?id=write)方法返回响应给客户端。

`Swoole`要求客户端使用这方方式发送数据时，必须在实际数据前增加 4 字节、网络字节序的长度值。
```php
$msg = 'Hello Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```

### UnixSocket
`Swoole\Process\Pool->__construct`的第二个参数设置为`SWOOLE_IPC_UNIXSOCK`时，表示使用[UnixSocket](/learn?id=什么是IPC)，**强烈推荐用此种方式进程间通讯**。

这种方式比较简单，只需要通过[Swoole\Process\Pool->sendMessage()](/process/process_pool?id=sendMessage)方法和[Message事件](/process/process_pool?id=on)就可以完成进程间通信。

或者开启`协程模式`后，也可以通过[Swoole\Process\Pool->getProcess()](/process/process_pool?id=getProcess)获得`Swoole\Process`对象，[Swoole\Process->exportsocket()](/process/process?id=exportsocket)获得`Swoole\Coroutine\Socket`对象，使用这个对象实现进程间通信。不过此时不能设置[Message事件](/process/process_pool?id=on)

!> 参数和环境配置可以看看[构造函数](/process/process_pool?id=__construct)和[配置参数](/process/process_pool?id=set)

## 常量

常量 | 说明
---|---
SWOOLE_IPC_MSGQUEUE | 系统[消息队列](/learn?id=什么是IPC)通信
SWOOLE_IPC_SOCKET | SOCKET通信
SWOOLE_IPC_UNIXSOCK | [UnixSocket](/learn?id=什么是IPC)通信(v4.4+)

## 协程支持

在`v4.4.0`版本中增加了对协程的支持，请参考 [Swoole\Process\Pool::__construct](/process/process_pool?id=__construct)

## 使用示例

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** 当前是 Worker 进程 */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
```

## 方法

### __construct()

构造方法。

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **参数** 

  * **`int $worker_num`**
    * **功能**：指定工作进程的数量
    * **默认值**：无
    * **其它值**：无

  * **`int $ipc_type`**
    * **功能**：进程间通信的模式【默认为`SWOOLE_IPC_NONE`表示不使用任何进程间通信特性】
    * **默认值**：`SWOOLE_IPC_NONE`
    * **其它值**：`SWOOLE_IPC_MSGQUEUE`，`SWOOLE_IPC_SOCKET`，`SWOOLE_IPC_UNIXSOCK`

    !> -设置为`SWOOLE_IPC_NONE`时必须设置`onWorkerStart`回调，并且必须在`onWorkerStart`中实现循环逻辑，当`onWorkerStart`函数退出时工作进程会立即退出，之后会由`Manager`进程重新拉起进程；  
    -设置为`SWOOLE_IPC_MSGQUEUE`表示使用系统消息队列通信，可设置`$msgqueue_key`指定消息队列的`KEY`，未设置消息队列`KEY`，将申请私有队列；  
    -设置为`SWOOLE_IPC_SOCKET`表示使用`Socket`进行通信，需要使用[listen](/process/process_pool?id=listen)方法指定监听的地址和端口；  
    -设置为`SWOOLE_IPC_UNIXSOCK`表示使用[unixSocket](/learn?id=什么是IPC)进行通信，协程模式下使用，**强烈推荐用此种方式进程间通讯**，具体用法见下文；  
    -使用非`SWOOLE_IPC_NONE`设置时，必须设置`onMessage`回调，`onWorkerStart`变更为可选。

  * **`int $msgqueue_key`**
    * **功能**：消息队列的 `key`
    * **默认值**：`0`
    * **其它值**：无

  * **`bool $enable_coroutine`**
    * **功能**：是否开启协程支持【使用协程后将无法设置`onMessage`回调】
    * **默认值**：`false`
    * **其它值**：`true`

* **协程模式**
    
在`v4.4.0`版本中`Process\Pool`模块增加了对协程的支持，可以配置第`4`个参数为`true`来启用。启用协程后底层会在`onWorkerStart`时自动创建一个协程和[协程容器](/coroutine/scheduler)，在回调函数中可直接使用协程相关`API`，例如：

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

开启协程后Swoole会禁止设置`onMessage`事件回调，需要进程间通讯的话需要将第二个设置为`SWOOLE_IPC_UNIXSOCK`表示使用[unixSocket](/learn?id=什么是IPC)进行通信，然后使用`$pool->getProcess()->exportSocket()`导出[Swoole\Coroutine\Socket](/coroutine_client/socket)对象，实现`Worker`进程间通信。例如：

 ```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> 具体用法可以参考[Swoole\Coroutine\Socket](/coroutine_client/socket)和[Swoole\Process](/process/process?id=exportsocket)相关章节。

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```
### set()

設定參數。

```php
Swoole\Process\Pool->set(array $settings): void
```


可選參數|類型|作用|預設值
---|---|----|----
enable_coroutine|bool|控制是否啟用協程|false
enable_message_bus|bool|啟用消息總線，該值為`true`時，如果發送大數據時，底層會切分成小塊數據，再一塊塊發送到對端|false
max_package_size|int|限制了進程所能接收的最大數據量|2 * 1024 * 1024

* **注意**

  * `enable_message_bus`為`true`時，`max_package_size`是沒有作用的，因為底層會將數據拆成小塊數據，發送出去，接收數據也是一樣的。
  * `SWOOLE_IPC_MSGQUEUE`模式下，`max_package_size`也是沒有作用的，底層一次最多接收`65536`的數據量。
  * `SWOOLE_IPC_SOCKET`模式下，`enable_message_bus`為`false`時，如果獲取的数据量大於`max_package_size`，底層會直接中斷連接。
  * `SWOOLE_IPC_UNIXSOCK`模式下，`enable_message_bus`為`false`時，如果數據大於`max_package_size`，超過`max_package_size`的數據會被截斷。
  * 如果開啟了協程模式，`enable_message_bus`為`true`時，`max_package_size`也是沒有作用的。底層會做好數據的拆分（發送）和合併（接收），否則就會根據`max_package_size`限制接收數據量

!> Swoole版本 >= v4.4.4 可用


### on()

設定進程池回調函數。

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **參數** 

  * **`string $event`**
    * **功能**：指定事件
    * **預設值**：無
    * **其它值**：無

  * **`callable $function`**
    * **功能**：回調函數
    * **預設值**：無
    * **其它值**：無

* **事件**

  * **onWorkerStart** 子進程啟動

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Pool對象
  * @param int $workerId   WorkerId當前工作進程的編號，底層會對子進程進行標號
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} is started\n";
  });
  ```

  * **onWorkerStop** 子進程結束

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Pool對象
  * @param int $workerId   WorkerId當前工作進程的編號，底層會對子進程進行標號
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Worker#{$workerId} stop\n";
  });
  ```

  * **onMessage** 消息接收

  !> 收到外部投遞的消息。 一次連接只能投遞一次消息, 類似於`PHP-FPM`的短連接機制

  ```php
  /**
    * @param \Swoole\Process\Pool $pool Pool對象
    * @param string $data 消息數據內容
   */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
    var_dump($data);
  });
  ```

  !> 事件名稱不區分大小寫，`WorkerStart`，`workerStart`或者`workerstart`都一樣的


### listen()

監聽`SOCKET`，必須在`$ipc_mode = SWOOLE_IPC_SOCKET`時才能使用。

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **參數** 

  * **`string $host`**
    * **功能**：監聽的地址【支持`TCP`和[unixSocket](/learn?id=什麼是IPC)兩種類型。`127.0.0.1`表示監聽`TCP`地址，需要指定`$port`。`unix:/tmp/php.sock`監聽[unixSocket](/learn?id=什麼是IPC)地址】
    * **預設值**：無
    * **其它值**：無

  * **`int $port`**
    * **功能**：監聽的端口【在`TCP`模式下需要指定】
    * **預設值**：`0`
    * **其它值**：無

  * **`int $backlog`**
    * **功能**：監聽的隊列長度
    * **預設值**：`2048`
    * **其它值**：無

* **返回值**

  * 成功監聽返回`true`
  * 監聽失敗返回`false`，可調用`swoole_errno`獲取錯誤碼。監聽失敗後，調用`start`時會立即返回`false`

* **通信協議**

    向監聽端口發送數據時，客戶端必須在請求前增加4字节、網絡字節序的長度值。協議格式為：

```php
// $msg 需要發送的數據
$packet = pack('N', strlen($msg)) . $msg;
```

* **使用示例**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```


### write()

向對端寫入數據，必須在`$ipc_mode`為`SWOOLE_IPC_SOCKET`時才能使用。

```php
Swoole\Process\Pool->write(string $data): bool
```

!> 此方法為內存操作，沒有`IO`消耗，發送數據操作是同步阻塞`IO`

* **參數** 

  * **`string $data`**
    * **功能**：寫入的數據內容【可多次調用`write`，底層會在`onMessage`函數退出後將數據全部寫入`socket`中，並`close`連接】
    * **預設值**：無
    * **其它值**：無

* **使用示例**

  * **服務端**

    ```php
    $pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
    
    $pool->on("Message", function ($pool, $message) {
        echo "Message: {$message}\n";
        $pool->write("hello ");
        $pool->write("world ");
        $pool->write("\n");
    });
    
    $pool->listen('127.0.0.1', 8089);
    $pool->start();
    ```

  * **調用端**

    ```php
    $fp = stream_socket_client("tcp://127.0.0.1:8089", $errno, $errstr) or die("error: $errstr\n");
    $msg = json_encode(['data' => 'hello', 'uid' => 1991]);
    fwrite($fp, pack('N', strlen($msg)) . $msg);
    sleep(1);
    //將顯示 hello world\n
    $data = fread($fp, 8192);
    var_dump(substr($data, 4, unpack('N', substr($data, 0, 4))[1]));
    fclose($fp);
    ```


### sendMessage()

往目標進程發送數據，必須在`$ipc_mode`為`SWOOLE_IPC_UNIXSOCK`時才能使用。

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **參數** 

  * **`string $data`**
    * **功能**：需要發送的數據
    * **預設值**：無
    * **其它值**：無

  * **`int $dst_worker_id`**
    * **功能**：目標進程id
    * **預設值**：`0`
    * **其它值**：無

* **返回值**

  * 發送成功返回`true`
  * 發送失敗返回`false`

* **注意**

  * 如果發送數據大於`max_package_size`並且`enable_message_bus`為`false`的話，目標進程在接收數據的時候會對數據進行截斷
### start()

启动工作进程。

```php
Swoole\Process\Pool->start(): bool
```

!> 如果启动成功，当前进程将进入`wait`状态，负责管理工作进程；  
如果启动失败，将返回`false`，可以使用`swoole_errno`来获取错误码。

* **使用示例**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
```

* **进程管理**

  * 如果某个工作进程遇到致命错误或主动退出，管理器会进行回收，以避免僵尸进程的出现
  * 工作进程退出后，管理器会自动拉起并创建一个新的工作进程
  * 当主进程收到`SIGTERM`信号时，将停止`fork`新进程，并`kill`所有正在运行的工作进程
  * 当主进程收到`SIGUSR1`信号时，将逐个`kill`正在运行的工作进程，并重新启动新的工作进程

* **信号处理**

  底层仅设置了主进程（管理进程）的信号处理，并未对`Worker`工作进程设置信号，因此需要开发者自行实现信号的监听。

  - 如果工作进程为异步模式，请使用 [Swoole\Process::signal](/process/process?id=signal) 来监听信号
  - 如果工作进程为同步模式，请使用`pcntl_signal`和`pcntl_signal_dispatch`来监听信号

  在工作进程中应当监听`SIGTERM`信号，以便当主进程需要终止该进程时，能够向此进程发送`SIGTERM`信号。如果工作进程未监听`SIGTERM`信号，底层会强行终止当前进程，可能会导致部分逻辑丢失。

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```

### stop()

将当前进程套接字移出事件循环，开启协程后，这个函数才有作用

```php
Swoole\Process\Pool->stop(): bool
```

### shutdown()

终止工作进程。

```php
Swoole\Process\Pool->shutdown(): bool
```

### getProcess()

获取当前工作进程对象。返回[Swoole\Process](/process/process)对象。

!> Swoole 版本 >= `v4.2.0` 可用

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **参数** 

  * **`int $worker_id`**
    * **功能**：指定获取 `worker` 【可选参数,  默认当前 `worker`】
    * **默认值**：无
    * **其它值**：无

!> 必须在`start`之后，在工作进程的`onWorkerStart`或其他回调函数中调用；  
返回的`Process`对象是单例模式，在工作进程中重复调用`getProcess()`将返回同一个对象。

* **使用示例**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```

### detach()

将进程池内当前 Worker 进程脱离管理，底层会立即创建新的进程，老的进程不再处理数据，由应用层代码自行管理生命周期。

!> Swoole 版本 >= `v4.7.0` 可用

```php
