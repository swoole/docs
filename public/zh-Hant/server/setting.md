# 配置

[Swoole\Server->set()](/server/methods?id=set) 函数用于设置`Server`运行时的各项参数。本节所有的子页面均为配置数组的元素。

!> 从 [v4.5.5](/version/log?id=v455) 版本起，底层会检测设置的配置项是否正确，如果设置了不是`Swoole`提供的配置项，则会产生一个Warning。

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```

### debug_mode

?> 设置日志模式为`debug`调试模式，只有编译时开启了`--enable-debug`才有作用。

```php
$server->set([
  'debug_mode' => true
])
```

### trace_flags

?> 设置跟踪日志的标签，仅打印部分跟踪日志。`trace_flags` 支持使用 `|` 或操作符设置多个跟踪项。，只有编译时开启了`--enable-trace-log`才有作用。

底层支持以下跟踪项，可使用`SWOOLE_TRACE_ALL`表示跟踪所有项目：

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`

### log_file

?> **指定`Swoole`错误日志文件**

?> 在`Swoole`运行期发生的异常信息会记录到这个文件中，默认会打印到屏幕。  
开启守护进程模式后`(daemonize => true)`，标准输出将会被重定向到`log_file`。在PHP代码中`echo/var_dump/print`等打印到屏幕的内容会写入到`log_file`文件。

  * **提示**

    * `log_file`中的日志仅仅是做运行时错误记录，没有长久存储的必要。

    * **日志标号**

      ?> 在日志信息中，进程ID前会加一些标号，表示日志产生的线程/进程类型。

        * `#` Master进程
        * `$` Manager进程
        * `*` Worker进程
        * `^` Task进程

    * **重新打开日志文件**

      ?> 在服务器程序运行期间日志文件被`mv`移动或`unlink`删除后，日志信息将无法正常写入，这时可以向`Server`发送`SIGRTMIN`信号实现重新打开日志文件。

      * 仅支持`Linux`平台
      * 不支持[UserProcess](/server/methods?id=addProcess)进程

  * **注意**

    !> `log_file`不会自动切分文件，所以需要定期清理此文件。观察`log_file`的输出，可以得到服务器的各类异常信息和警告。

### log_level

?> **设置`Server`错误日志打印的等级，范围是`0-6`。低于`log_level`设置的日志信息不会抛出。**【默认值：`SWOOLE_LOG_INFO`】

对应级别常量参考[日志等级](/consts?id=日志等级)

  * **注意**

    !> `SWOOLE_LOG_DEBUG`和`SWOOLE_LOG_TRACE`仅在编译为[--enable-debug-log](/environment?id=debug参数)和[--enable-trace-log](/environment?id=debug参数)版本时可用；  
    在开启`daemonize`守护进程时，底层将把程序中的所有打印屏幕的输出内容写入到[log_file](/server/setting?id=log_file)，这部分内容不受`log_level`控制。

### log_date_format

?> **设置`Server`日志时间格式**，格式参考 [strftime](https://www.php.net/manual/zh/function.strftime.php) 的`format`

```php
$server->set([
    'log_date_format' => '%Y-%m-%d %H:%M:%S',
]);
```

### log_date_with_microseconds

?> **设置`Server`日志精度，是否带微秒**【默认值：`false`】

### log_rotation

?> **设置`Server`日志分割**【默认值：`SWOOLE_LOG_ROTATION_SINGLE`】

| 常量                             | 说明   | 版本信息 |
| -------------------------------- | ------ | -------- |
| SWOOLE_LOG_ROTATION_SINGLE       | 不启用 | -        |
| SWOOLE_LOG_ROTATION_MONTHLY      | 每月   | v4.5.8   |
| SWOOLE_LOG_ROTATION_DAILY        | 每日   | v4.5.2   |
| SWOOLE_LOG_ROTATION_HOURLY       | 每小时 | v4.5.8   |
| SWOOLE_LOG_ROTATION_EVERY_MINUTE | 每分钟 | v4.5.8   |

### display_errors

?> 开启 / 关闭 `Swoole` 错误信息。

```php
$server->set([
  'display_errors' => true
])
```

### dns_server

?> 设置`dns`查询的`ip`地址。

### socket_dns_timeout

?> 域名解析超时时间，如果在服务端启用协程客户端，该参数可以控制客户端的域名解析超时时间，单位为秒。

### socket_connect_timeout

?> 客户端连接超时时间，如果在服务端启用协程客户端，该参数可以控制客户端的连接超时时间，单位为秒。

### socket_write_timeout / socket_send_timeout

?> 客户端写超时时间，如果在服务端启用协程客户端，该参数可以控制客户端的写超时时间，单位为秒。   
该配置也能用于控制`协程化`之后的`shell_exec`或者[Swoole\Coroutine\System::exec()](/coroutine/system?id=exec)的执行超时时间。   

### socket_read_timeout / socket_recv_timeout

?> 客户端读超时时间，如果在服务端启用协程客户端，该参数可以控制客户端的读超时时间，单位为秒。

### max_coroutine / max_coro_num :id=max_coroutine

?> **设置当前工作进程最大协程数量。**【默认值：`100000`，Swoole版本小于`v4.4.0-beta` 时默认值为`3000`】

?> 超过`max_coroutine`底层将无法创建新的协程，服务端的Swoole会抛出`exceed max number of coroutine`错误，`TCP Server`会直接关闭连接，`Http Server`会返回Http的503状态码。

?> 在`Server`程序中实际最大可创建协程数量等于 `worker_num * max_coroutine`，task进程和UserProcess进程的协程数量单独计算。

```php
$server->set(array(
    'max_coroutine' => 3000,
));
```

### enable_deadlock_check

?> 打开协程死锁检测。

```php
$server->set([
  'enable_deadlock_check' => true
]);
```

### hook_flags

?> **设置`一键协程化`Hook的函数范围。**【默认值：不hook】

!> Swoole版本为 `v4.5+` 或 [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 可用，详情参考[一键协程化](/runtime)

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```
底层支持以下协程化项，可使用`SWOOLE_HOOK_ALL`表示协程化全部：

* `SWOOLE_HOOK_TCP`
* `SWOOLE_HOOK_UNIX`
* `SWOOLE_HOOK_UDP`
* `SWOOLE_HOOK_UDG`
* `SWOOLE_HOOK_SSL`
* `SWOOLE_HOOK_TLS`
* `SWOOLE_HOOK_SLEEP`
* `SWOOLE_HOOK_FILE`
* `SWOOLE_HOOK_STREAM_FUNCTION`
* `SWOOLE_HOOK_BLOCKING_FUNCTION`
* `SWOOLE_HOOK_PROC`
* `SWOOLE_HOOK_CURL`
* `SWOOLE_HOOK_NATIVE_CURL`
* `SWOOLE_HOOK_SOCKETS`
* `SWOOLE_HOOK_STDIO`
* `SWOOLE_HOOK_PDO_PGSQL`
* `SWOOLE_HOOK_PDO_ODBC`
* `SWOOLE_HOOK_PDO_ORACLE`
* `SWOOLE_HOOK_PDO_SQLITE`
* `SWOOLE_HOOK_ALL`
### 啟用預佔式排程器

?> 啟用协程的預佔式排程，以避免一個協程執行時間過長導致其他協程餓死，協程的最大執行時間為`10ms`。

```php
$server->set([
  'enable_preemptive_scheduler' => true
]);
```

### c_stack_size / 堆疊大小

?> 設定單個協程初始 C 堆疊的內存尺寸，預設為 2M。

### aio_core_worker_num

?> 設定`AIO`最小工作線程數，預設值為`cpu`核心數。

### aio_worker_num 

?> 設定`AIO`最大工作線程數，預設值為`cpu`核心數 * 8。

### aio_max_wait_time

?> 工作線程等待任務的最大時間，單位為秒。

### aio_max_idle_time

?> 工作線程最大空閒時間，單位為秒。

### reactor_num

?> **設定啟動的 [Reactor](/learn?id=reactor線程) 線程數。**【預設值：`CPU`核心數】

?> 通過此參數來調控主進程內事件處理線程的數量，以充分利用工多核。預設會啟用`CPU`核心數相同的數量。  
`Reactor`線程是可以利用多核，如：機器有`128`核，那麼底層會啟動`128`線程。  
每個線程都能夠維持一個[EventLoop](/learn?id=什麼是eventloop)。線程之間是無鎖的，指令可以被`128`核`CPU`並列執行。  
考慮到作業系統調度存在一定程度的性能損失，可以設定為CPU核心數*2，以便最大化利用CPU的每一個核。

  * **提示**

    * `reactor_num`建議設定為`CPU`核心數的`1-4`倍
    * `reactor_num`最大不得超過 [swoole_cpu_num()](/functions?id=swoole_cpu_num) * 4

  * **注意**


  !> -`reactor_num`必須小於或等於`worker_num` ；  

-如果設定的`reactor_num`大於`worker_num`，會自動調整使`reactor_num`等於`worker_num` ；  
-在超過`8`核的機器上`reactor_num`預設設定為`8`。
	

### worker_num

?> **設定啟動的`Worker`進程數。**【預設值：`CPU`核心數】

?> 如`1`個請求耗時`100ms`，要提供`1000QPS`的處理能力，那必須配置`100`個進程或更多。  
但開的進程越多，佔用的內存就會大大增加，而且進程間切換的開銷就會越來越大。所以這裡適當即可。不要配置過大。

  * **提示**

    * 如果業務代碼是全[異步IO](/learn?id=同步io異步io)的，這裡設定為`CPU`核心數的`1-4`倍最合理
    * 如果業務代碼為[同步IO](/learn?id=同步io異步io)，需要根據請求響應時間和系統負載來調整，例如：`100-500`
    * 預設設定為[swoole_cpu_num()](/functions?id=swoole_cpu_num)，最大不得超過[swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000
    * 假設每個進程占用`40M`內存，`100`個進程就需要占用`4G`內存。


### max_request

?> **設定`worker`進程的最大任務數。**【預設值：`0` 即不會退出進程】

?> 一個`worker`進程在處理完超過此數值的任務後將自動退出，進程退出後會釋放所有內存和資源

!> 這個參數的主要作用是解決由於程式碼編碼不規範導致的PHP進程內存洩漏問題。PHP應用程序有緩慢的內存洩漏，但無法定位到具體原因、無法解決，可以通過設定`max_request`臨時解決，需要找到內存洩漏的程式碼並修復，而不是通過此方案，可以使用Swoole Tracker發現洩漏的程式碼。

  * **提示**

    * 達到max_request不一定馬上關閉進程，參考[max_wait_time](/server/setting?id=max_wait_time)。
    * [SWOOLE_BASE](/learn?id=swoole_base)下，達到max_request重啟進程會導致客戶端連接斷開。

  !> 當`worker`進程內發生致命錯誤或者人工執行`exit`時，進程會自動退出。`master`進程會重新啟動一個新的`worker`進程來繼續處理請求


### max_conn / 最大連接數

?> **伺服器程序，最大允許的連接數。**【預設值：`ulimit -n`】

?> 如`max_connection => 10000`, 此參數用來設定`Server`最大允許維持多少個`TCP`連接。超過此數量後，新進入的連接將被拒絕。

  * **提示**

    * **預設設定**

      * 應用層未設定`max_connection`，底層將使用`ulimit -n`的值作為缺省設定
      * 在`4.2.9`或更高版本，當底層檢測到`ulimit -n`超過`100000`時將預設設定為`100000`，原因是某些系統設定了`ulimit -n`為`100萬`，需要分配大量內存，導致啟動失敗

    * **最大上限**

      * 請勿設定`max_connection`超過`1M`

    * **最小設定**    
      
      * 此選項設定過小底層會拋出錯誤，並設定為`ulimit -n`的值。
      * 最小值為`(worker_num + task_worker_num) * 2 + 32`

    ```shell
    serv->max_connection is too small.
    ```

    * **內存占用**

      * `max_connection`參數不要調整的過大，根據機器內存的實際情況來設定。`Swoole`會根據此數值一次性分配一大塊大內存來保存`Connection`資訊，一個`TCP`連接的`Connection`資訊，需要占用`224`字节。

  * **注意**

  !> `max_connection`最大不得超過作業系統`ulimit -n`的值，否則會報一條警告資訊，並重置為`ulimit -n`的值

  ```shell
  WARN swServer_start_check: serv->max_conn is exceed the maximum value[100000].

  WARNING set_max_connection: max_connection is exceed the maximum value, it's reset to 10240
  ```


### task_worker_num

?> **配置 [Task進程](/learn?id=taskworker進程) 的數量。**

?> 配置此參數後將會啟用`task`功能。所以`Server`務必要註冊[onTask](/server/events?id=ontask)、[onFinish](/server/events?id=onfinish) 2 個事件回調函數。如果沒有註冊，伺服器程序將無法啟動。

  * **提示**

    *  [Task進程](/learn?id=taskworker進程) 是同步阻塞的

    * 最大值不得超過[swoole_cpu_num()](/functions?id=swoole_cpu_num) * 1000
    
    * **計算方法**
      * 單個`task`的處理耗時，如`100ms`，那一個進程1秒就可以處理`1/0.1=10`個task
      * `task`投遞的速度，如每秒產生`2000`個`task`
      * `2000/10=200`，需要設定`task_worker_num => 200`，啟用`200`個Task進程

  * **注意**

    !> - [Task進程](/learn?id=taskworker進程)內不能使用`Swoole\Server->task`方法
### task_ipc_mode

?> **設定 [Task進程](/learn?id=taskworker進程)與`Worker`進程之間的溝通方式。**【預設值：`1`】 
 
?> 請先閱讀[Swoole下的IPC通訊](/learn?id=什麼是IPC)。


模式 | 作用
---|---
1 | 使用`Unix Socket`通訊【預設模式】
2 | 使用`sysvmsg`訊息隊列通訊
3 | 使用`sysvmsg`訊息隊列通訊，並設定為爭搶模式

  * **提示**

    * **模式`1`**
      * 使用模式`1`時，支援定向投遞，可在[task](/server/methods?id=task)和[taskwait](/server/methods?id=taskwait)方法中使用`dst_worker_id`，指定目標 `Task進程`。
      * `dst_worker_id`設定為`-1`時，底層會判斷每個 [Task進程](/learn?id=taskworker進程)的狀態，向當前狀態為的空閒的進程投遞任務。

    * **模式`2`、`3`**
      * 訊息隊列模式使用作業系統提供的內存隊列儲存數據，未指定 `mssage_queue_key` 訊息隊列`Key`，將使用私有隊列，在`Server`程序終止後會刪除訊息隊列。
      * 指定訊息隊列`Key`後`Server`程序終止後，訊息隊列中的數據不會刪除，因此進程重啟後仍然能取到數據
      * 可使用`ipcrm -q`訊息隊列`ID`手動刪除訊息隊列數據
      * `模式2`和`模式3`的不同之處是，`模式2`支援定向投遞，`$serv->task($data, $task_worker_id)` 可以指定投遞到哪個 [task進程](/learn?id=taskworker進程)。`模式3`是完全爭搶模式， [task進程](/learn?id=taskworker進程)會爭搶隊列，將無法使用定向投遞，`task/taskwait`將無法指定目標進程`ID`，即使指定了`$task_worker_id`，在`模式3`下也是無效的。

  * **注意**

    !> -`模式3`會影響[sendMessage](/server/methods?id=sendMessage)方法，使[sendMessage](/server/methods?id=sendMessage)發送的訊息會隨機被某一個 [task進程](/learn?id=taskworker進程)取得。  
    -使用訊息隊列通訊，如果 `Task進程` 處理能力低於投遞速度，可能會引起`Worker`進程阻塞。  
    -使用訊息隊列通訊後task進程無法支援協程(開啟[task_enable_coroutine](/server/setting?id=task_enable_coroutine))。  


### task_max_request

?> **設定 [task進程](/learn?id=taskworker進程)的最大任務數。**【預設值：`0`】

設定task進程的最大任務數。一個task進程在處理完超過此數值的任務後將自動退出。這個參數是為了防止PHP進程內存溢出。如果不希望進程自動退出可以設定為0。


### task_tmpdir

?> **設定task的數據臨時目錄。**【預設值：Linux `/tmp` 目錄】

?> 在`Server`中，如果投遞的數據超過`8180`字節，將啟用臨時檔案來保存數據。這裡的`task_tmpdir`就是用來設定臨時檔案保存的位置。

  * **提示**

    * 底層默認會使用`/tmp`目錄儲存`task`數據，如果你的`Linux`内核版本過低，`/tmp`目錄不是內存檔案系統，可以設定為 `/dev/shm/`
    * `task_tmpdir`目錄不存在，底層會嘗試自動創建

  * **注意**

    !> -創建失敗時，`Server->start`會失敗


### task_enable_coroutine

?> **開啟 `Task` 協程支持。**【預設值：`false`】，v4.2.12起支援

?> 開啟後自動在[onTask](/server/events?id=ontask)回調中創建協程和[協程容器](/coroutine/scheduler)，`PHP`代碼可以直接使用協程`API`。

  * **示例**

```php
$server->on('Task', function ($serv, Swoole\Server\Task $task) {
    //來自哪個 Worker 進程
    $task->worker_id;
    //任務的編號
    $task->id;
    //任務的類型，taskwait, task, taskCo, taskWaitMulti 可能使用不同的 flags
    $task->flags;
    //任務的數據
    $task->data;
    //投遞時間，v4.6.0版本增加
    $task->dispatch_time;
    //協程 API
    co::sleep(0.2);
    //完成任務，結束並返回數據
    $task->finish([123, 'hello']);
});
```

  * **注意**

    !> -`task_enable_coroutine`必須在[enable_coroutine](/server/setting?id=enable_coroutine)為`true`時才可以使用  
    -開啟`task_enable_coroutine`，`Task`工作進程支援協程  
    -未開啟`task_enable_coroutine`，僅支援同步阻塞


### task_use_object/task_object :id=task_use_object

?> **使用面向對象風格的Task回調格式。**【預設值：`false`】

?> 設定為`true`時，[onTask](/server/events?id=ontask)回調將變成物件模式。

  * **示例**

```php
<?php

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 3,
    'task_use_object' => true,
//    'task_object' => true, // v4.6.0版本增加的別名
]);
$server->on('receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd' => $fd,]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    //此处$task是Swoole\Server\Task物件
    $server->send($task->data['fd'], json_encode($server->stats()));
});
$server->start();
```


### dispatch_mode

?> **數據包分發策略。**【預設值：`2`】


模式值 | 模式 | 作用
---|---|---
1 | 輪循模式 | 收到會輪循分配給每一個`Worker`進程
2 | 固定模式 | 根據連接的檔案描述符分配`Worker`。這樣可以保證同一個連接發來的數據只會被同一個`Worker`處理
3 | 爭搶模式 | 主進程會根據`Worker`的忙閒狀態選擇投遞，只會投遞給處於空閒狀態的`Worker`
4 | IP分配 | 根據客戶端`IP`進行取模`hash`，分配給一個固定的`Worker`進程。<br>可以保證同一個來源IP的連接數據總會被分配到同一個`Worker`進程。算法為 `inet_addr_mod(ClientIP, worker_num)`
5 | UID分配 | 需要用戶代碼中調用 [Server->bind()](/server/methods?id=bind) 將一個連接綁定`1`個`uid`。然後底層根據`UID`的值分配到不同的`Worker`進程。<br>算法為 `UID % worker_num`，如果需要使用字符串作為`UID`，可以使用`crc32(UID_STRING)`
7 | stream模式 | 空閒的`Worker`會`accept`連接，並接受[Reactor](/learn?id=reactor線程)的新請求

  * **提示**

    * **使用建議**
    
      * 無狀態`Server`可以使用`1`或`3`，同步阻塞`Server`使用`3`，異步非阻塞`Server`使用`1`
      * 有狀態使用`2`、`4`、`5`
      
    * **UDP協議**

      * `dispatch_mode=2/4/5`時為固定分配，底層使用客戶端`IP`取模散列到不同的`Worker`進程
      * `dispatch_mode=1/3`時隨機分配到不同的`Worker`進程
      * `inet_addr_mod`函數

```
    function inet_addr_mod($ip, $worker_num) {
        $ip_parts = explode('.', $ip);
        if (count($ip_parts) != 4) {
            return false;
        }
        $ip_parts = array_reverse($ip_parts);
    
        $ip_long = 0;
        foreach ($ip_parts as $part) {
            $ip_long <<= 8;
            $ip_long |= (int) $part;
        }
    
        return $ip_long % $worker_num;
    }
```
  * **Base模式**
    * `dispatch_mode`配置在 [SWOOLE_BASE](/learn?id=swoole_base) 模式是無效的，因為`BASE`不存在投遞任務，當收到客戶端發來的數據後會立即在當前線程/進程回調[onReceive](/server/events?id=onreceive)，不需要投遞`Worker`進程。

  * **注意**

    !> -`dispatch_mode=1/3`時，底層會屏蔽`onConnect/onClose`事件，原因是這2種模式下無法保證`onConnect/onClose/onReceive`的順序；  
    -非請求響應式的服务器程序，請不要使用模式`1`或`3`。例如：http服務就是響應式的，可以使用`1`或`3`，有TCP長連接狀態的就不能使用`1`或`3`。
### dispatch_func

?> 設定`dispatch`函數，Swoole底層內建了`6`種[dispatch_mode](/server/setting?id=dispatch_mode)，如果仍舊無法滿足需求。可以使用編寫`C++`函數或`PHP`函數，實現`dispatch`邏輯。

  * **使用方法**

```php
$server->set(array(
  'dispatch_func' => 'my_dispatch_function',
));
```

  * **提示**

    * 設定`dispatch_func`後底層會自動忽略`dispatch_mode`配置
    * `dispatch_func`對應的函數不存在，底層將拋出致命錯誤
    * 如果需要`dispatch`一個超過8K的包，`dispatch_func`只能獲取到 `0-8180` 字節的內容

  * **編寫PHP函數**

    ?> 由於`ZendVM`無法支持多線程環境，即使設定了多個[Reactor](/learn?id=reactor线程)線程，同一時間只能執行一個`dispatch_func`。因此底層在執行此PHP函數時會進行加鎖操作，可能會存在鎖的爭搶問題。請勿在`dispatch_func`中執行任何阻塞操作，否則會導致`Reactor`線程組停止工作。

    ```php
    $server->set(array(
        'dispatch_func' => function ($server, $fd, $type, $data) {
            var_dump($fd, $type, $data);
            return intval($data[0]);
        },
    ));
    ```

    * `$fd`為客戶端連接的唯一識別符，可使用`Server::getClientInfo`獲取連接資訊
    * `$type`數據的類型，`0`表示來自客戶端的数据發送，`4`表示客戶端連接建立，`3`表示客戶端連接關閉
    * `$data`數據內容，需要注意：如果啟用了`HTTP`、`EOF`、`Length`等協議處理參數後，底層會進行包的拼接。但在`dispatch_func`函數中只能傳入數據包的前8K內容，不能得到完整的包內容。
    * **必須**返回一個`0 - (server->worker_num - 1)`的數字，表示數據包投遞的目標工作進程`ID`
    * 小於`0`或大於等於`server->worker_num`為異常目標`ID`，`dispatch`的數據將會被丢弃

  * **編寫C++函數**

    **在其他PHP擴展中，使用swoole_add_function註冊長度函數到Swoole引擎中。**

    ?> C++函數調用時底層不會加鎖，需要調用方自行保證線程安全性

    ```c++
    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data);

    int dispatch_function(swServer *serv, swConnection *conn, swEventData *data)
    {
        printf("cpp, type=%d, size=%d\n", data->info.type, data->info.len);
        return data->info.len % serv->worker_num;
    }

    int register_dispatch_function(swModule *module)
    {
        swoole_add_function("my_dispatch_function", (void *) dispatch_function);
    }
    ```

    * `dispatch`函數必須返回投遞的目標`worker`進程`id`
    * 返回的`worker_id`不得超過`server->worker_num`，否則底層會拋出段錯誤
    * 返回負數`（return -1）`表示丢弃此數據包
    * `data`可以讀取到事件的類型和長度
    * `conn`是連接的資訊，如果是`UDP`數據包，`conn`為`NULL`

  * **注意**

    !> -`dispatch_func`僅在[SWOOLE_PROCESS](/learn?id=swoole_process)模式下有效，[UDP/TCP/UnixSocket](/server/methods?id=__construct)類型的服务器均有效  
    -返回的`worker_id`不得超過`server->worker_num`，否則底層會拋出段錯誤


### message_queue_key

?> **設定消息隊列的`KEY`。**【默認值：`ftok($php_script_file, 1)`】

?> 仅在[task_ipc_mode](/server/setting?id=task_ipc_mode) = 2/3時使用。設定的`Key`僅作為`Task`任務隊列的`KEY`，參考[Swoole下的IPC通訊](/learn?id=什麼是IPC)。

?> `task`隊列在`server`結束後不會摧毀，重新啟動程式後， [task進程](/learn?id=taskworker進程)仍然會接著處理隊列中的任務。如果不想讓程式重新啟動後執行旧的`Task`任務。可以手動刪除此消息隊列。

```shell
ipcs -q 
ipcrm -Q [msgkey]
```


### daemonize

?> **守护進程化**【默認值：`false`】

?> 設定`daemonize => true`時，程式將轉入後台作為守護進程運行。長期運行的服務器端程式必須啟用此項。  
如果未啟用守護進程，當ssh終端退出後，程式將被終止運行。

  * **提示**

    * 啟用守護進程後，標準輸入和輸出会被重定向到 `log_file`
    * 如果未設定`log_file`，將重定向到 `/dev/null`，所有打印螢幕的信息都會被丟棄
    * 啟用守護進程後，`CWD`（當前目錄）環境變量的值會發生變更，相對路徑的文件讀寫會出錯。`PHP`程式中必須使用絕對路徑

    * **systemd**

      * 使用`systemd`或者`supervisord`管理`Swoole`服務時，請勿設定`daemonize => true`。主要原因是`systemd`的機制與`init`不同。`init`進程的`PID`為`1`，程式使用`daemonize`後，會脫離終端，最終被`init`進程托管，與`init`關係變為父子進程關係。
      * 但`systemd`是啟動了一個單獨的後台進程，自行`fork`管理其他服務進程，因此不需要`daemonize`，反而使用了`daemonize => true`會使得`Swoole`程式與該管理進程失去父子進程關係。


### backlog

?> **設定`Listen`隊列長度**

?> 如`backlog => 128`，此參數將決定最多同時有多少個等待`accept`的連接。

  * **關於`TCP`的`backlog`**

    ?> `TCP`有三次握手的過程，客戶端 `syn=>服務端` `syn+ack=>客戶端` `ack`，當服務器收到客戶端的`ack`後會將連接放到一個叫做`accept queue`的隊列裡面（注1），  
    隊列的大小由`backlog`參數和配置`somaxconn` 的最小值決定，可以通過`ss -lt`命令查看最終的`accept queue`隊列大小，`Swoole`的主進程調用`accept`（注2）  
    從`accept queue`裡面取走。 當`accept queue`滿了之後連接有可能成功（注4），  
    也有可能失敗，失敗後客戶端的表现就是連接被重置（注3）  
    或者連接超時，而服務端會記錄失敗的記錄，可以通過 `netstat -s|grep 'times the listen queue of a socket overflowed` 來查看日誌。如果出現了上述現象，你就應該調大該值了。 幸运的是`Swoole`的SWOOLE_PROCESS模式與`PHP-FPM/Apache`等軟件不同，並不依賴`backlog`來解決連接排隊的問題。所以基本不會遇到上述現象。

    * 注1:`linux2.2`之後握手過程分為`syn queue`和`accept queue`兩個隊列, `syn queue`長度由`tcp_max_syn_backlog`決定。
    * 注2:高版本內核調用的是`accept4`，為了節省一次`set no block`系統調用。
    * 注3:客戶端收到`syn+ack`包就認為連接成功了，實際上服務端還處於半連接狀態，有可能發送`rst`包給客戶端，客戶端的表现就是`Connection reset by peer`。
    * 注4:成功是通過TCP的重傳機制，相關的配置有`tcp_synack_retries`和`tcp_abort_on_overflow`。
### open_tcp_keepalive

?> 在TCP中有一个Keep-Alive的机制可以检测死连接，应用层如果对于死链接周期不敏感或者没有实现心跳机制，可以使用操作系统提供的keepalive机制来踢掉死链接。
在 [Server->set()](/server/methods?id=set) 配置中增加open_tcp_keepalive => true表示启用TCP keepalive。
另外，有3个选项可以对keepalive的细节进行调整。

  * **选项**

     * **tcp_keepidle**

        单位秒，连接在n秒内没有数据请求，将开始对此连接进行探测。

     * **tcp_keepcount**

        探测的次数，超过次数后将close此连接。

     * **tcp_keepinterval**

        探测的间隔时间，单位秒。

  * **示例**

```php
$serv = new Swoole\Server("192.168.2.194", 6666, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => true,
    'tcp_keepidle' => 4, //4s没有数据传输就进行检测
    'tcp_keepinterval' => 1, //1s探测一次
    'tcp_keepcount' => 5, //探测的次数，超过5次后还没回包close此连接
));

$serv->on('connect', function ($serv, $fd) {
    var_dump("Client:Connect $fd");
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    var_dump($data);
});

$serv->on('close', function ($serv, $fd) {
  var_dump("close fd $fd");
});

$serv->start();
```

### heartbeat_check_interval

?> **启用心跳检测**【默认值：false】

?> 此选项表示每隔多久轮循一次，单位为秒。如heartbeat_check_interval => 60，表示每60秒，遍历所有连接，如果该连接在120秒内（heartbeat_idle_time未设置时默认为interval的两倍），没有向服务器发送任何数据，此连接将被强制关闭。若未配置，则不会启用心跳，该配置默认关闭。

  * **提示**
    * Server并不会主动向客户端发送心跳包，而是被动等待客户端发送心跳。服务器端的heartbeat_check仅仅是检测连接上一次发送数据的时间，如果超过限制，将切断连接。
    * 被心跳检测切断的连接依然会触发[onClose](/server/events?id=onclose)事件回调

  * **注意**

    !> heartbeat_check仅支持TCP连接

### heartbeat_idle_time

?> **连接最大允许空闲的时间**

?> 需要与heartbeat_check_interval配合使用

```php
array(
    'heartbeat_idle_time'      => 600, // 表示一个连接如果600秒内未向服务器发送任何数据，此连接将被强制关闭
    'heartbeat_check_interval' => 60,  // 表示每60秒遍历一次
);
```

  * **提示**

    * 启用heartbeat_idle_time后，服务器并不会主动向客户端发送数据包
    * 如果只设置了heartbeat_idle_time未设置heartbeat_check_interval底层将不会创建心跳检测线程，PHP代码中可以调用heartbeat方法手动处理超时的连接

### open_eof_check

?> **打开EOF检测**【默认值：false】，参考[TCP数据包边界问题](/learn?id=tcp数据包边界问题)

?> 此选项将检测客户端连接发来的数据，当数据包结尾是指定的字符串时才会投递给Worker进程。否则会一直拼接数据包，直到超过缓存区或者超时才会中止。当出错时底层会认为是恶意连接，丢弃数据并强制关闭连接。  
常见的Memcache/SMTP/POP等协议都是以\r
结束的，就可以使用此配置。开启后可以保证Worker进程一次性总是收到一个或者多个完整的数据包。

```php
array(
    'open_eof_check' => true,   //打开EOF检测
    'package_eof'    => "\r
", //设置EOF
)
```

  * **注意**

    !> 此配置仅对STREAM(流式的)类型的Socket有效，如[TCP 、Unix Socket Stream](/server/methods?id=__construct)   
    EOF检测不会从数据中间查找EOF字符串，所以Worker进程可能会同时收到多个数据包，需要在应用层代码中自行explode("\r
", $data) 来拆分数据包

### open_eof_split

?> **启用EOF自动分包**

?> 当设置open_eof_check后，可能会产生多条数据合并在一个包内 , open_eof_split参数可以解决这个问题，参考[TCP数据包边界问题](/learn?id=tcp数据包边界问题)。

?> 设置此参数需要遍历整个数据包的内容，查找EOF，因此会消耗大量CPU资源。假设每个数据包为2M，每秒10000个请求，这可能会产生20G条CPU字符匹配指令。

```php
array(
    'open_eof_split' => true,   //打开EOF_SPLIT检测
    'package_eof'    => "\r\n", //设置EOF
)
```

  * **提示**

    * 启用open_eof_split参数后，底层会从数据包中间查找EOF，并拆分数据包。[onReceive](/server/events?id=onreceive)每次仅收到一个以EOF字串结尾的数据包。
    * 启用open_eof_split参数后，无论参数open_eof_check是否设置，open_eof_split都将生效。

    * **与 open_eof_check 的差异**
    
        * open_eof_check 只检查接收数据的末尾是否为 EOF，因此它的性能最好，几乎没有消耗
        * open_eof_check 无法解决多个数据包合并的问题，比如同时发送两条带有 EOF 的数据，底层可能会一次全部返回
        * open_eof_split 会从左到右对数据进行逐字节对比，查找数据中的 EOF 进行分包，性能较差。但是每次只会返回一个数据包

### package_eof

?> **设置EOF字符串。** 参考[TCP数据包边界问题](/learn?id=tcp数据包边界问题)

?> 需要与 open_eof_check 或者 open_eof_split 配合使用。

  * **注意**

    !> package_eof最大只允许传入8个字节的字符串

### open_length_check

?> **打开包长检测特性**【默认值：false】，参考[TCP数据包边界问题](/learn?id=tcp数据包边界问题)

?> 包长检测提供了固定包头+包体这种格式协议的解析。启用后，可以保证Worker进程[onReceive](/server/events?id=onreceive)每次都会收到一个完整的数据包。  
长度检测协议，只需要计算一次长度，数据处理仅进行指针偏移，性能非常高，**推荐使用**。

  * **提示**

    * **长度协议提供了3个选项来控制协议细节。**

      ?> 此配置仅对STREAM类型的Socket有效，如[TCP、Unix Socket Stream](/server/methods?id=__construct)

      * **package_length_type**

        ?> 包头中某个字段作为包长度的值，底层支持了10种长度类型。请参考 [package_length_type](/server/setting?id=package_length_type)

      * **package_body_offset**

        ?> 从第几个字节开始计算长度，一般有2种情况：

        * length的值包含了整个包(包头+包体)，package_body_offset 为0
        * 包头长度为N字节，length的值不包含包头，仅包含包体，package_body_offset设置为N

      * **package_length_offset**

        ?> length长度值在包头的第几个字节。

        * 示例：

        ```c
        struct
        {
            uint32_t type;
            uint32_t uid;
            uint32_t length;
            uint32_t serid;
            char body[0];
        }
        ```
        
    ?> 以上通信协议的设计中，包头长度为4个整型，16字节，length长度值在第3个整型处。因此package_length_offset设置为8，0-3字节为type，4-7字节为uid，8-11字节为length，12-15字节为serid。

    ```php
    $server->set(array(
      'open_length_check'     => true,
      'package_max_length'    => 81920,
      'package_length_type'   => 'N',
      'package_length_offset' => 8,
      'package_body_offset'   => 16,
    ));
    ```
### package_length_type

?> **长度值的类型**，接受一个字符参数，与`PHP`的 [pack](http://php.net/manual/zh/function.pack.php) 函数一致。

目前`Swoole`支持`10`种类型：


字符参数 | 作用
---|---
c | 有符号、1字节
C | 无符号、1字节
s | 有符号、主机字节序、2字节
S | 无符号、主机字节序、2字节
n | 无符号、网络字节序、2字节
N | 无符号、网络字节序、4字节
l | 有符号、主机字节序、4字节（小写L）
L | 无符号、主机字节序、4字节（大写L）
v | 无符号、小端字节序、2字节
V | 无符号、小端字节序、4字节


### package_length_func

?> **设置长度解析函数**

?> 支持`C++`或`PHP`的`2`种类型的函数。长度函数必须返回一个整数。


返回数 | 作用
---|---
返回0 | 长度数据不足，需要接收更多数据
返回-1 | 数据错误，底层会自动关闭连接
返回包长度值（包括包头和包体的总长度）| 底层会自动将包拼好后返回给回调函数

  * **提示**

    * **使用方法**

    ?> 实现原理是先读取一小部分数据，在这段数据内包含了一个长度值。然后将这个长度返回给底层。然后由底层完成剩余数据的接收并组合成一个包进行`dispatch`。

    * **PHP长度解析函数**

    ?> 由于`ZendVM`不支持运行在多线程环境，因此底层会自动使用`Mutex`互斥锁对`PHP`长度函数进行加锁，避免并发执行`PHP`函数。在`1.9.3`或更高版本可用。

    !> 请勿在长度解析函数中执行阻塞`IO`操作，可能导致所有[Reactor](/learn?id=reactor线程)线程发生阻塞

    ```php
    $server = new Swoole\Server("127.0.0.1", 9501);
    
    $server->set(array(
        'open_length_check'   => true,
        'dispatch_mode'       => 1,
        'package_length_func' => function ($data) {
          if (strlen($data) < 8) {
              return 0;
          }
          $length = intval(trim(substr($data, 0, 8)));
          if ($length <= 0) {
              return -1;
          }
          return $length + 8;
        },
        'package_max_length'  => 2000000,  //协议最大长度
    ));
    
    $server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {
        var_dump($data);
        echo "#{$server->worker_id}>> received length=" . strlen($data) . "\n";
    });
    
    $server->start();
    ```

    * **C++长度解析函数**

    ?> 在其他PHP扩展中，使用`swoole_add_function`注册长度函数到`Swoole`引擎中。
    
    !> C++长度函数调用时底层不会加锁，需要调用方自行保证线程安全性
    
    ```c++
    #include <string>
    #include <iostream>
    #include "swoole.h"
    
    using namespace std;
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length);
    
    void register_length_function(void)
    {
        swoole_add_function((char *) "test_get_length", (void *) test_get_length);
        return SW_OK;
    }
    
    int test_get_length(swProtocol *protocol, swConnection *conn, char *data, uint32_t length)
    {
        printf("cpp, size=%d\n", length);
        return 100;
    }
    ```


### package_max_length

?> **设置最大数据包尺寸，单位为字节。**【默认值：`2M` 即 `2 * 1024 * 1024`，最小值为`64K`】

?> 开启[open_length_check](/server/setting?id=open_length_check)/[open_eof_check](/server/setting?id=open_eof_check)/[open_eof_split](/server/setting?id=open_eof_split)/[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol)等协议解析后，`Swoole`底层会进行数据包拼接，这时在数据包未收取完整时，所有数据都是保存在内存中的。  
所以需要设定`package_max_length`，一个数据包最大允许占用的内存尺寸。如果同时有1万个`TCP`连接在发送数据，每个数据包`2M`，那么最极限的情况下，就会占用`20G`的内存空间。

  * **提示**

    * `open_length_check`：当发现包长度超过`package_max_length`，将直接丢弃此数据，并关闭连接，不会占用任何内存；
    * `open_eof_check`：因为无法事先得知数据包长度，所以收到的数据还是会保存到内存中，持续增长。当发现内存占用已超过`package_max_length`时，将直接丢弃此数据，并关闭连接；
    * `open_http_protocol`：`GET`请求最大允许`8K`，而且无法修改配置。`POST`请求会检测`Content-Length`，如果`Content-Length`超过`package_max_length`，将直接丢弃此数据，发送`http 400`错误，并关闭连接；

  * **注意**

    !> 此参数不宜设置过大，否则会占用很大的内存


### open_http_protocol

?> **启用`HTTP`协议处理。**【默认值：`false`】

?> 启用`HTTP`协议处理，[Swoole\Http\Server](/http_server)会自动启用此选项。设置为`false`表示关闭`HTTP`协议处理。


### open_mqtt_protocol

?> **启用`MQTT`协议处理。**【默认值：`false`】

?> 启用后会解析`MQTT`包头，`worker`进程[onReceive](/server/events?id=onreceive)每次会返回一个完整的`MQTT`数据包。

```php
$server->set(array(
  'open_mqtt_protocol' => true
));
```


### open_redis_protocol

?> **启用`Redis`协议处理。**【默认值：`false`】

?> 启用后会解析`Redis`协议，`worker`进程[onReceive](/server/events?id=onreceive)每次会返回一个完整的`Redis`数据包。建议直接使用[Redis\Server](/redis_server)

```php
$server->set(array(
  'open_redis_protocol' => true
));
```


### open_websocket_protocol

?> **启用`WebSocket`协议处理。**【默认值：`false`】

?> 启用`WebSocket`协议处理，[Swoole\WebSocket\Server](websocket_server)会自动启用此选项。设置为`false`表示关闭`websocket`协议处理。  
设置`open_websocket_protocol`选项为`true`后，会自动设置`open_http_protocol`协议也为`true`。


### open_websocket_close_frame

?> **启用websocket协议中关闭帧。**【默认值：`false`】

?> （`opcode`为`0x08`的帧）在`onMessage`回调中接收

?> 开启后，可在`WebSocketServer`中的`onMessage`回调中接收到客户端或服务端发送的关闭帧，开发者可自行对其进行处理。

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->set(array("open_websocket_close_frame" => true));

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message received: {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {});

$server->start();
```
### open_tcp_nodelay

?> **啟用`open_tcp_nodelay`。**【預設值：`false`】

?> 啟用後，TCP連接在傳輸數據時會關閉Nagle合併演算法，立即發送到對端的TCP連接。在某些場景下，例如命令列終端機，輸入一個命令就需要馬上發送到伺服器，可以提升響應速度，請自行Google Nagle演算法。


### open_cpu_affinity 

?> **啟用CPU親和性設定。** 【預設 `false`】

?> 在多核心的硬體平台中，啟用此特性會將Swoole的reactor線程/worker進程綁定到固定的一個核心上。可以避免進程/線程的運行時在多個核心之間互相切換，提高CPU Cache的命中率。

  * **提示**

    * **使用taskset命令查看進程的CPU親和設定：**

    ```bash
    taskset -p 進程ID
    pid 24666's current affinity mask: f
    pid 24901's current affinity mask: 8
    ```

    > mask是一個掩碼數字，按bit計算每bit對應一個CPU核心，如果某一位為0表示綁定此核心，進程會被調度到此CPU上，為0表示進程不會被調度到此CPU。示例中pid為24666的進程mask = f 表示未綁定到CPU，作業系統會將此進程調度到任意一個CPU核心上。pid為24901的進程mask = 8，8轉為二進制是 1000，表示此進程綁定在第4個CPU核心上。


### cpu_affinity_ignore

?> **在I/O密集型的程序中，所有的网络中断都是用CPU0来处理，如果网络I/O很重，CPU0负载过高会导致网络中断无法及时处理，那网络收发包的能力就会下降。**

?> 如果不设置此选项，swoole将会使用全部CPU核心，底层根据reactor_id或worker_id与CPU核数取模来设置CPU绑定。如果内核与网卡有多队列特性，网络中断会分布到多核，可以缓解网络中断的压力

```php
array('cpu_affinity_ignore' => array(0, 1)) // 接受一个数组作为参数，array(0, 1) 表示不使用CPU0,CPU1，专门空出来处理网络中断。
```

  * **提示**

    * **查看网络中断**

```shell
[~]$ cat /proc/interrupts 
           CPU0       CPU1       CPU2       CPU3       
  0: 1383283707          0          0          0    IO-APIC-edge  timer
  1:          3          0          0          0    IO-APIC-edge  i8042
  3:         11          0          0          0    IO-APIC-edge  serial
  8:          1          0          0          0    IO-APIC-edge  rtc
  9:          0          0          0          0   IO-APIC-level  acpi
 12:          4          0          0          0    IO-APIC-edge  i8042
 14:         25          0          0          0    IO-APIC-edge  ide0
 82:         85          0          0          0   IO-APIC-level  uhci_hcd:usb5
 90:         96          0          0          0   IO-APIC-level  uhci_hcd:usb6
114:    1067499          0          0          0       PCI-MSI-X  cciss0
130:   96508322          0          0          0         PCI-MSI  eth0
138:     384295          0          0          0         PCI-MSI  eth1
169:          0          0          0          0   IO-APIC-level  ehci_hcd:usb1, uhci_hcd:usb2
177:          0          0          0          0   IO-APIC-level  uhci_hcd:usb3
185:          0          0          0          0   IO-APIC-level  uhci_hcd:usb4
NMI:      11370       6399       6845       6300 
LOC: 1383174675 1383278112 1383174810 1383277705 
ERR:          0
MIS:          0
```

`eth0/eth1`就是网络中断的次数，如果`CPU0 - CPU3` 是平均分布的，证明网卡有多队列特性。如果全部集中于某一个核，说明网络中断全部由此`CPU`进行处理，一旦此`CPU`超过`100%`，系统将无法处理网络请求。这时就需要使用 `cpu_affinity_ignore` 设置将此`CPU`空出，专门用于处理网络中断。

如图上的情况，应当设置 `cpu_affinity_ignore => array(0)`

?> 可以使用`top`指令 `->` 输入 `1`，查看到每个核的使用率

  * **注意**

    !> 此选项必须与`open_cpu_affinity`同时设置才会生效


### tcp_defer_accept

?> **启用`tcp_defer_accept`特性**【默认值：`false`】

?> 可以设置为一个数值，表示当一个TCP连接有数据发送时才触发accept。

```php
$server->set(array(
  'tcp_defer_accept' => 5
));
```

  * **提示**

    * **启用`tcp_defer_accept`特性后，accept和[onConnect](/server/events?id=onconnect)对应的时间会发生变化。如果设置为5秒：**

      * 客户端连接到服务器后不会立即触发accept
      * 在5秒内客户端发送数据，此时会同时顺序触发accept/onConnect/onReceive
      * 在5秒内客户端没有发送任何数据，此时会触发accept/onConnect


### ssl_cert_file / ssl_key_file :id=ssl_cert_file

?> **设置SSL隧道加密。**

?> 设置值为一个文件名字符串，指定cert证书和key私钥的路径。

  * **提示**

    * **PEM转DER格式**

    ```shell
    openssl x509 -in cert.crt -outform der -out cert.der
    ```

    * **DER转PEM格式**

    ```shell
    openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
    ```

  * **注意**

    !> - HTTPS应用浏览器必须信任证书才能浏览网页；  
    - wss应用中，发起WebSocket连接的页面必须使用 HTTPS；  
    - 浏览器不信任SSL证书将无法使用 wss；  
    - 文件必须为PEM格式，不支持DER格式，可使用openssl工具进行转换。

    !> 使用SSL必须在编译Swoole时加入[--enable-openssl](/environment?id=编译选项)选项

    ```php
    $server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(array(
        'ssl_cert_file' => __DIR__.'/config/ssl.crt',
        'ssl_key_file' => __DIR__.'/config/ssl.key',
    ));
    ```


### ssl_method

!> 此參數已在 [v4.5.4](/version/bc?id=_454) 版本移除，請使用ssl_protocols

?> **設置OpenSSL隧道加密的演算法。**【預設值：`SWOOLE_SSLv23_METHOD`】，支持的類型請參考[SSL 加密方法](/consts?id=ssl-加密方法)

?> Server與Client使用的演算法必須一致，否則SSL/TLS握手會失敗，連接會被切斷

```php
$server->set(array(
    'ssl_method' => SWOOLE_SSLv3_CLIENT_METHOD,
));
```
### ssl_protocols

?> **設定OpenSSL隧道加密的協議。**【預設值：`0`，支援全部協議】，支持的類型請參考[SSL 協議](/consts?id=ssl-協議)

!> Swoole版本 >= `v4.5.4` 可用

```php
$server->set(array(
    'ssl_protocols' => 0,
));
```

### ssl_sni_certs

?> **設定 SNI (Server Name Identification) 證書**

!> Swoole版本 >= `v4.6.0` 可用

```php
$server->set([
    'ssl_cert_file' => __DIR__ . '/server.crt',
    'ssl_key_file' => __DIR__ . '/server.key',
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'ssl_sni_certs' => [
        'cs.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_cs_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_cs_key.pem',
        ],
        'uk.php.net' => [
            'ssl_cert_file' =>  __DIR__ . '/sni_server_uk_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_uk_key.pem',
        ],
        'us.php.net' => [
            'ssl_cert_file' => __DIR__ . '/sni_server_us_cert.pem',
            'ssl_key_file' => __DIR__ . '/sni_server_us_key.pem',
        ],
    ]
]);
```

### ssl_ciphers

?> **設定 openssl 加密算法。**【預設值：`EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH`】

```php
$server->set(array(
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
));
```

  * **提示**

    * `ssl_ciphers` 設定為空字符串時，由`openssl`自行選擇加密算法

### ssl_verify_peer

?> **服務SSL設定驗證對端證書。**【預設值：`false`】

?> 預設關閉，即不驗證客戶端證書。若開啟，必須同時設定 `ssl_client_cert_file` 選項

### ssl_allow_self_signed

?> **允許自签名證書。**【預設值：`false`】

### ssl_client_cert_file

?> **根證書，用於驗證客戶端證書。**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set(array(
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,
    'ssl_allow_self_signed' => true,
    'ssl_client_cert_file'  => __DIR__ . '/config/ca.crt',
));
```

!> `TCP`服務若驗證失敗，會底層會主動關閉連接。

### ssl_compress

?> **設定是否啟用`SSL/TLS`壓縮。** 在[Co\Client](/coroutine_client/client)使用時，它有個別名`ssl_disable_compression`

### ssl_verify_depth

?> **如果證書鏈條層次太深，超過了本選項的設定值，則終止驗證。**

### ssl_prefer_server_ciphers

?> **啟用伺服器端保護，防止 BEAST 攻擊。**

### ssl_dhparam

?> **指定DHE密碼器的`Diffie-Hellman`參數。**

### ssl_ecdh_curve

?> **指定用在ECDH密鑰交換中的`curve`。**

```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_compress'                => true,
    'ssl_verify_depth'            => 10,
    'ssl_prefer_server_ciphers'   => true,
    'ssl_dhparam'                 => '',
    'ssl_ecdh_curve'              => '',
]);
```

### user

?> **設定`Worker/TaskWorker`子進程的所屬用戶。**【預設值：執行腳本用戶】

?> 伺服器如果需要監聽`1024`以下的端口，必須有`root`權限。但程式運行在`root`用戶下，程式碼中一旦有漏洞，攻擊者就可以以`root`的方式執行遠程指令，風險很大。配置了`user`項之後，可以讓主進程運行在`root`權限下，子進程運行在普通用戶權限下。

```php
$server->set(array(
  'user' => 'Apache'
));
```

  * **注意**

    !> -僅在使用`root`用戶啟動時有效  
    -使用`user/group`配置項將工作進程設置為普通用戶後，將無法在工作進程調用`shutdown`/[reload](/server/methods?id=reload)方法關閉或重啟服務。只能使用`root`賬戶在`shell`終端執行`kill`指令。

### group

?> **設定`Worker/TaskWorker`子進程的進程用戶組。**【預設值：執行腳本用戶組】

?> 與`user`配置相同，此配置是修改進程所屬用戶組，提升伺服器程式安全性。

```php
$server->set(array(
  'group' => 'www-data'
));
```

  * **注意**

    !> 仅在使用`root`用戶啟動時有效

### chroot

?> **重定向`Worker`進程的文件系統根目錄。**

?> 此設定可以使進程對文件系統的讀寫與實際的作業系統文件系統隔離。提升安全性。

```php
$server->set(array(
  'chroot' => '/data/server/'
));
```

### pid_file

?> **設定 pid 文件地址。**

?> 在`Server`啟動時自動將`master`進程的`PID`寫入到檔案，在`Server`關閉時自動刪除`PID`檔案。

```php
$server->set(array(
    'pid_file' => __DIR__.'/server.pid',
));
```

  * **注意**

    !> 使用時需要注意如果`Server`非正常結束，`PID`檔案不會刪除，需要使用[Swoole\Process::kill($pid, 0)](/process/process?id=kill)來偵測進程是否真的存在

### buffer_input_size / input_buffer_size :id=buffer_input_size

?> **配置接收輸入緩存區內存尺寸。**【預設值：`2M`】

```php
$server->set([
    'buffer_input_size' => 2 * 1024 * 1024,
]);
```

### buffer_output_size / output_buffer_size :id=buffer_output_size

?> **配置發送輸出緩存區內存尺寸。**【預設值：`2M`】

```php
$server->set([
    'buffer_output_size' => 32 * 1024 * 1024, //必須為數字
]);
```

  * **提示**

    !> Swoole 版本 >= `v4.6.7` 時，預設值為無符號INT最大值`UINT_MAX`

    * 單位為字节，默認為`2M`，如設定`32 * 1024 * 1024`表示，單次`Server->send`最大允許發送`32M`字節的數據
    * 調用`Server->send`，`Http\Server->end/write`，`WebSocket\Server->push`等發送數據指令時，`單次`最大發送的數據不得超過`buffer_output_size`配置。

    !> 此參數只針對[SWOOLE_PROCESS](/learn?id=swoole_process)模式生效，因為PROCESS模式下Worker進程的數據要發送给主進程再發送給客戶端，所以每個Worker進程會和主進程開闢一塊緩衝區。[參考](/learn?id=reactor线程)

### socket_buffer_size

?> **配置客戶端連接的緩存區長度。**【預設值：`2M`】

?> 不同於 `buffer_output_size`，`buffer_output_size` 是 worker 進程`單次`send` 的大小限制，`socket_buffer_size`是用於設定`Worker`和`Master`進程間通訊 buffer 总的大小，參考[SWOOLE_PROCESS](/learn?id=swoole_process)模式。

```php
$server->set([
    'socket_buffer_size' => 128 * 1024 *1024, //必須為數字，單位為字节，如128 * 1024 *1024表示每個TCP客戶端連接最大允許有128M待發送的數據
]);
```
- **資料傳送緩衝區**

    - 当Master進程向客戶端傳送大量資料時，並不能立即發出。此時傳送的資料會存放在伺服器端的內存緩衝區內。此參數可以調整內存緩衝區的大小。
    
    - 如果傳送的資料過多，資料占滿緩衝區後`Server`會報如下錯誤訊息：
    
    ```bash
    swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
    ```
    
    ?>傳送緩衝區塞滿導致`send`失敗，只會影響當前的客戶端，其他客戶端不受影響
    伺服器有大量的`TCP`連接時，最差的情況下將會佔用`serv->max_connection * socket_buffer_size`字節的內存
    
    -尤其是往外通訊的伺服器程式，網絡通訊較慢，如果持續連續傳送資料，緩衝區很快就會塞滿。傳送的資料會全部堆積在`Server`的內存裡。因此此類應用應從設計上考慮到網絡的傳輸能力，先將消息存入磁碟，等客戶端通知伺服器已接受完畢後，再傳送新的資料。
    
    - 如視頻直播服務，`A`用戶帶寬是 `100M`，`1`秒內傳送`10M`的資料是完全可以的。`B`用戶帶寬只有`1M`，如果`1`秒內傳送`10M`的資料，`B`用戶可能需要`100`秒才能接收完畢。此時資料會全部堆積在伺服器內存中。
    
    - 可以根據資料內容的類型，進行不同的處理。如果是可丟棄的內容，如視頻直播等業務，網絡差的情況下丟棄一些數據幀完全可以接受。如果內容是不可丟失的，如微信消息，可以先儲存到伺服器的磁碟中，按照`100`條消息為一組。當用戶接受完這一組消息後，再從磁碟中取出下一組消息傳送到客戶端。

### enable_unsafe_event

?> **啟用`onConnect/onClose`事件。**【預設值：`false`】

?> `Swoole`在配置 [dispatch_mode](/server/setting?id=dispatch_mode)=1 或`3`後，因為系統無法保證`onConnect/onReceive/onClose`的順序，預設關閉了`onConnect/onClose`事件；  
如果應用程序需要`onConnect/onClose`事件，並且能接受順序問題可能帶來的網絡安全風險，可以通過設置`enable_unsafe_event`為`true`，啟用`onConnect/onClose`事件。

### discard_timeout_request

?> **丟棄已關閉連接的數據請求。**【預設值：`true`】

?> `Swoole`在配置[dispatch_mode](/server/setting?id=dispatch_mode)=`1`或`3`後，系統無法保證`onConnect/onReceive/onClose`的順序，因此可能會有一些請求數據在連接關閉後，才能到達`Worker`進程。

  * **提示**

    * `discard_timeout_request`配置默認為`true`，表示如果`worker`進程收到了已關閉連接的數據請求，將自動丟棄。
    * `discard_timeout_request`如果設置為`false`，表示無論連接是否關閉`Worker`進程都會處理數據請求。

### enable_reuse_port

?> **設置端口重用。**【預設值：`false`】

?> 啟用端口重用後，可以重複啟動監聽同一個端口的 Server 程序

  * **提示**

    * `enable_reuse_port = true` 打開端口重用
    * `enable_reuse_port = false` 關閉端口重用

!> 仅在`Linux-3.9.0`以上版本的內核可用 `Swoole4.5`以上版本可用

### enable_delay_receive

?> **設置`accept`客戶端連接後將不會自動加入[EventLoop](/learn?id=什麼是eventloop)。**【預設值：`false`】

?> 設置此選項為`true`後，`accept`客戶端連接後將不會自動加入[EventLoop](/learn?id=什麼是eventloop)，僅觸發[onConnect](/server/events?id=onconnect)回調。`worker`進程可以調用 [$server->confirm($fd)](/server/methods?id=confirm)對連接進行確認，此時才會將`fd`加入[EventLoop](/learn?id=什麼是eventloop)開始進行數據收發，也可以調用`$server->close($fd)`關閉此連接。

```php
//開啟enable_delay_receive選項
$server->set(array(
    'enable_delay_receive' => true,
));

$server->on("Connect", function ($server, $fd, $reactorId) {
    $server->after(2000, function() use ($server, $fd) {
        //確認連接，開始接收數據
        $server->confirm($fd);
    });
});
```

### reload_async

?> **設置異步重啟開關。**【預設值：`true`】

?> 設置異步重啟開關。設置為`true`時，將啟用異步安全重啟特性，`Worker`進程會等待異步事件完成後再退出。詳細資訊請參見 [如何正確的重啟服務](/question/use?id=swoole如何正確的重啟服務)

?> `reload_async` 開啟的主要目的是為了保證服務重載時，協程或異步任務能正常結束。 

```php
$server->set([
  'reload_async' => true
]);
```

  * **協程模式**

    * 在`4.x`版本中開啟 [enable_coroutine](/server/setting?id=enable_coroutine)時，底層會額外增加一個協程數量的檢測，當前無任何協程時進程才會退出，開啟時即使`reload_async => false`也會強制打開`reload_async`。

### max_wait_time

?> **設置 `Worker` 進程收到停止服務通知後最大等待時間**【預設值：`3`】

?> 常會碰到由於`worker`阻塞卡頓導致`worker`無法正常`reload`, 無法滿足一些生產場景，例如發布代碼熱更新需要`reload`進程。所以，Swoole 加入了進程重啟超時時間的選項。詳細資訊請參見 [如何正確的重啟服務](/question/use?id=swoole如何正確的重啟服務)

  * **提示**

    * **管理進程收到重啟、關閉信號後或者達到`max_request`時，管理進程會重起該`worker`進程。分以下幾個步驟：**

      * 底層會增加一個(`max_wait_time`)秒的定時器，觸發定時器後，檢查進程是否仍存在，如果是，會強制殺掉，重新拉一個進程。
      * 需要在`onWorkerStop`回調裡面做收尾工作，需要在`max_wait_time`秒內做完收尾。
      * 依次向目標進程發送`SIGTERM`信號，殺掉進程。

  * **注意**

    !> `v4.4.x`以前默認為`30`秒

### tcp_fastopen

?> **開啟TCP快速握手特性。**【預設值：`false`】

?> 此項特性，可以提升`TCP`短連接的響應速度，在客戶端完成握手的第三步，發送`SYN`包時攜帶數據。

```php
$server->set([
  'tcp_fastopen' => true
]);
```

  * **提示**

    * 此參數可以設置到監聽端口上，想深入理解的同學可以查看[google論文](http://conferences.sigcomm.org/co-next/2011/papers/1569470463.pdf)

### request_slowlog_file

?> **開啟請求慢日誌。** 從`v4.4.8`版本開始[已移除](https://github.com/swoole/swoole-src/commit/b1a400f6cb2fba25efd2bd5142f403d0ae303366)

!> 由於這個慢日誌的方案只能在同步阻塞的進程裡生效，不能在協程環境用，而Swoole4預設就是開啟協程的，除非關閉`enable_coroutine`，所以不要使用了，使用 [Swoole Tracker](https://business.swoole.com/tracker/index) 的阻塞檢測工具。

?> 啟用後`Manager`進程會設置一個時鐘信號，定時偵測所有`Task`和`Worker`進程，一旦進程阻塞導致請求超過規定的時間，將自動打印進程的`PHP`函數調用栈。

?> 底層基於`ptrace`系統調用實現，某些系統可能關閉了`ptrace`，無法追蹤慢請求。請確認`kernel.yama.ptrace_scope`內核參數是否`0`。

```php
$server->set([
  'request_slowlog_file' => '/tmp/trace.log',
]);
```

  * **超時時間**

```php
$server->set([
    'request_slowlog_timeout' => 2, // 設置請求超時時間為2秒
    'request_slowlog_file' => '/tmp/trace.log',
]);
```

!> 必須是具有可寫權限的文件，否則創建文件失敗底層會拋出致命錯誤
### 啟用协程

?> **是否啟用异步風格服務器的協程支持**

?> `enable_coroutine` 在關閉時將不再於[事件回調函數](/server/events)中自動創建協程，如果不需要用協程關閉這個會提高一些性能。參考[什麼是Swoole協程](/coroutine)。

  * **設定方法**
    
    * 在`php.ini`設定 `swoole.enable_coroutine = 'Off'` (可見 [ini設定文檔](/other/config.md) )
    * `$server->set(['enable_coroutine' => false]);`優先級高於ini

  * **`enable_coroutine`選項影響範圍**

      * onWorkerStart
      * onConnect
      * onOpen
      * onReceive
      * [setHandler](/redis_server?id=sethandler)
      * onPacket
      * onRequest
      * onMessage
      * onPipeMessage
      * onFinish
      * onClose
      * tick/after 定時器

!> 啟用`enable_coroutine`後在上述回調函數中將自動創建協程

* 當`enable_coroutine`設定為`true`時，底層將自動在[onRequest](/http_server?id=on)回調中創建協程，開發者無需自行使用`go`函數[創建協程](/coroutine/coroutine?id=create)
* 當`enable_coroutine`設定為`false`時，底層不會自動創建協程，開發者如果要使用協程，必須使用`go`自行創建協程，如果不需要使用協程特性，則處理方式與`Swoole1.x`是100%一致的
* 注意，這個開啟只是說明Swoole會通過協程去處理請求，如果事件中含有阻塞函數，那需要提前開啟[一鍵協程化](/runtime)，將`sleep`，`mysqlnd`這些阻塞的函數或者擴展開啟協程化

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->set([
    //關閉內置協程
    'enable_coroutine' => false,
]);

$server->on("request", function ($request, $response) {
    if ($request->server['request_uri'] == '/coro') {
        go(function () use ($response) {
            co::sleep(0.2);
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });
    } else {
        $response->header("Content-Type", "text/plain");
        $response->end("Hello World\n");
    }
});

$server->start();
```


### send_yield

?> **當發送數據時緩衝區內存不足時，直接在當前協程內[yield](/coroutine?id=協程調度)，等待數據發送完成，緩衝區清空時，自動[resume](/coroutine?id=協程調度)當前協程，繼續`send`數據。**【默認值：在[dispatch_mod](/server/setting?id=dispatch_mode) 2/4時可用，並默認開啟】

* `Server/Client->send`返回`false`並且錯誤碼為`SW_ERROR_OUTPUT_BUFFER_OVERFLOW`時，不返回`false`到`PHP`層，而是[yield](/coroutine?id=協程調度)掛起當前協程
* `Server/Client`監聽緩衝區是否清空的事件，在該事件觸發後，緩衝區內的數據已被發送完畢，這時[resume](/coroutine?id=協程調度)對應的協程
* 協程恢復後，繼續調用`Server/Client->send`向緩衝區內寫入數據，這時因為緩衝區已空，發送必然是成功的

改進前

```php
for ($i = 0; $i < 100; $i++) {
    //在緩衝區塞滿時會直接返回 false，並報錯 output buffer overflow
    $server->send($fd, $data_2m);
}
```

改進後

```php
for ($i = 0; $i < 100; $i++) {
    //在緩衝區塞滿時會 yield 當前協程，發送完成後 resume 繼續向下執行
    $server->send($fd, $data_2m);
}
```

!> 此項特性會改變底層的默認行為，可以手動關閉

```php
$server->set([
    'send_yield' => false,
]);
```

  * __影響範圍__

    * [Swoole\Server::send](/server/methods?id=send)
    * [Swoole\Http\Response::write](/http_server?id=write)
    * [Swoole\WebSocket\Server::push](/websocket_server?id=push)
    * [Swoole\Coroutine\Client::send](/coroutine_client/client?id=send)
    * [Swoole\Coroutine\Http\Client::push](/coroutine_client/http_client?id=push)


### send_timeout

設定發送超時，與`send_yield`配合使用，當在規定的時間內，數據未能發送到緩衝區，底層返回`false`，並設置錯誤碼為`ETIMEDOUT`，可以使用 [getLastError()](/server/methods?id=getlasterror) 方法獲取錯誤碼。

> 類型為浮點型，單位為秒，最小粒度為毫秒

```php
$server->set([
    'send_yield' => true,
    'send_timeout' => 1.5, // 1.5秒
]);

for ($i = 0; $i < 100; $i++) {
    if ($server->send($fd, $data_2m) === false and $server->getLastError() == SOCKET_ETIMEDOUT) {
      echo "發送超時\n";
    }
}
```


### hook_flags

?> **設定`一鍵協程化`Hook的函數範圍。**【默認值：不hook】

!> Swoole版本為 `v4.5+` 或 [4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) 可用，詳情參考[一鍵協程化](/runtime)

```php
$server->set([
    'hook_flags' => SWOOLE_HOOK_SLEEP,
]);
```


### buffer_high_watermark

?> **設定緩衝區高水位線，單位為字节。**

```php
$server->set([
    'buffer_high_watermark' => 8 * 1024 * 1024,
]);
```


### buffer_low_watermark

?> **設定緩衝區低水位線，單位為字节。**

```php
$server->set([
    'buffer_low_watermark' => 1 * 1024 * 1024,
]);
```


### tcp_user_timeout

?> TCP_USER_TIMEOUT選項是TCP層的socket選項，值為數據包被發送後未接收到ACK確認的最大時長，以毫秒為單位。具體請查看man文檔

```php
$server->set([
    'tcp_user_timeout' => 10 * 1000, // 10秒
]);
```

!> Swoole版本 >= `v4.5.3-alpha` 可用


### stats_file

?> **指定[stats()](/server/methods?id=stats)內容寫入的文件路徑。設定後會自動在[onWorkerStart](/server/events?id=onworkerstart)時設定一個定時器，定時將[stats()](/server/methods?id=stats)的內容寫入指定文件中**

```php
$server->set([
    'stats_file' => __DIR__ . '/stats.log',
]);
```

!> Swoole版本 >= `v4.5.5` 可用


### event_object

?> **設定此選項後，事件回調將使用[物件風格](/server/events?id=回调物件)。**【默認值：`false`】

```php
$server->set([
    'event_object' => true,
]);
```

!> Swoole版本 >= `v4.6.0` 可用


### start_session_id

?> **設定起始 session ID**

```php
$server->set([
    'start_session_id' => 10,
]);
```

!> Swoole版本 >= `v4.6.0` 可用


### single_thread

?> **設定為單一線程。** 啟用後 Reactor 線程將會和 Master 進程中的 Master 線程合併，由 Master 線程處理邏輯，在PHP ZTS下，如果使用`SWOOLE_PROCESS`模式，一定要設定該值為`true`。

```php
$server->set([
    'single_thread' => true,
]);
```

!> Swoole版本 >= `v4.2.13` 可用
### max_queued_bytes

?> **設定接收緩衝區的最大隊列長度。** 如果超出，則停止接收。

```php
$server->set([
    'max_queued_bytes' => 1024 * 1024,
]);
```

!> Swoole版本 >= `v4.5.0` 可用

### admin_server

?> **設定admin_server服務，用於在 [Swoole Dashboard](http://dashboard.swoole.com/) 中查看服務信息等。**

```php
$server->set([
    'admin_server' => '0.0.0.0:9502',
]);
```

!> Swoole版本 >= `v4.8.0` 可用

### bootstrap

?> **多線程模式下的程式入口檔案，預設是當前執行的腳本檔案名稱。**

!> Swoole版本 >= `v6.0` ， `PHP`為`ZTS`模式，編譯`Swoole`時開啟了`--enable-swoole-thread`可用

```php
$server->set([
    'bootstrap' => __FILE__,
]);
```

### init_arguments

?> **設定多線程的數據共享數據，該配置需要一個回調函數，伺服器啟動時會自動執行該函數**

!> Swoole內建了許多線程安全容器，[並發Map](/thread/map)，[並發List](/thread/arraylist)，[並發隊列](/thread/queue)，不要在函數中返回不安全的變量。

!> Swoole版本 >= `v6.0` ， `PHP`為`ZTS`模式，編譯`Swoole`時開啟了`--enable-swoole-thread`可用

```php
$server->set([
    'init_arguments' => function() { return new Swoole\Thread\Map(); },
]);

$server->on('request', function($request, $response) {
    $map = Swoole\Thread::getArguments();
});
```
