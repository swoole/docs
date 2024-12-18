# 基礎知識

## 四種設置回調函數的方式

* **匿名函數**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
!> 可使用`use`向匿名函數傳遞參數

* **類靜態方法**

```php
class A
{
    static function test($req, $resp)
    {
        echo "hello world";
    }
}
$server->on('Request', 'A::Test');
$server->on('Request', array('A', 'Test'));
```
!> 對應的靜態方法必須為`public`

* **函數**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **物件方法**

```php
class A
{
    function test($req, $resp)
    {
        echo "hello world";
    }
}

$object = new A();
$server->on('Request', array($object, 'test'));
```

!> 對應的方法必須為`public`


## 同步IO/非同步IO

在`Swoole4+`下所有的業務代碼都是同步寫法（`Swoole1.x`時代才支持非同步寫法，現在已經移除了非同步客戶端，相應的需求完全可以用车嶺客戶端實現），完全沒有心智負擔，符合人類思維習慣，但同步的寫法底層可能有`同步IO/非同步IO`之分。

無論是同步IO/非同步IO，`Swoole/Server`都可以維持大量`TCP`客戶端連接(參考[SWOOLE_PROCESS模式](/learn?id=swoole_process))。你的服務是阻塞還是非阻塞不需要單獨的配置某些參數，取決於你的代碼裡面是否有同步IO的操作。

**什麼是同步IO：**
 
簡單的例子就是執行到`MySQL->query`的時候，這個進程什麼事情都不做，等待MySQL返回結果，返回結果後再向下執行代碼，所以同步IO的服務並發能力是很差的。

**什麼樣的代碼是同步IO：**

 * 沒有開啟[一鍵協程化](/runtime)的時候，那麼你的代碼裡面絕大部分涉及IO的操作都是同步IO的，協程化後，就會變成非同步IO，進程不會傻等在那裡，參考[協程調度](/coroutine?id=協程調度)。
 * 有些`IO`是沒法一鍵協程化，沒法將同步IO變成非同步IO的，例如`MongoDB`(相信`Swoole`會解決這個問題)，需要寫代碼時候注意。

!> [協程](/coroutine) 是為了提高並發的，如果我的應用就沒有高並發，或者必須要用某些無法非同步化IO的操作(例如上文的MongoDB)，那麼你完全可以不開啟[一鍵協程化](/runtime)，關閉[enable_coroutine](/server/setting?id=enable_coroutine)，多開一些`Worker`進程，這就是和`Fpm/Apache`是一樣的模型了，值得一提的是由於`Swoole`是常驻進程的，即使同步IO性能也會有很大提升，實際應用中也有很多公司這樣做。


### 同步IO轉換成非同步IO

[上小節](/learn?id=同步io非同步io)介紹了什麼是同步/非同步IO，在`Swoole`下面，有些情況同步的`IO`操作是可以轉換成非同步IO的。
 
 - 開啟[一鍵協程化](/runtime)後，`MySQL`、`Redis`、`Curl`等操作會變成非同步IO。
 - 利用[Event](/event)模塊手動管理事件，將fd加到[EventLoop](/learn?id=什麼是eventloop)裡面，變成非同步IO，例子：

```php
//利用inotify監控文件變化
$fd = inotify_init();
//將$fd添加到Swoole的EventLoop
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd);//文件發生變化後讀取變化的文件。
    var_dump($var);
});
```

上述代碼如果不調用`Swoole\Event::add`將IO非同步化，直接`inotify_read()`將阻塞Worker進程，其他的請求將得不到處理。

 - 使用`Swoole\Server`的[sendMessage()](/server/methods?id=sendMessage)方法進行進程間通訊，默認`sendMessage`是同步IO，但有些情況是會被`Swoole`轉換成非同步IO，用[User進程](/server/methods?id=addprocess)舉例：

```php
$serv = new Swoole\Server("0.0.0.0", 9501, SWOOLE_BASE);
$serv->set(
    [
        'worker_num' => 1,
    ]
);

$serv->on('pipeMessage', function ($serv, $src_worker_id, $data) {
    echo "#{$serv->worker_id} message from #$src_worker_id: $data\n";
    sleep(10);//不接收sendMessage發來的數據，緩衝區將很快寫滿
});

$serv->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data) {

});

//情況1：同步IO(默認行為)
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//默認情況下，緩衝區寫滿後，此处會阻塞
    }
}, false);

//情況2：通過enable_coroutine參數開啟UserProcess進程的協程支持，為了防止其他協程得不到 EventLoop 的調度，
//Swoole會把sendMessage轉換成非同步IO
$enable_coroutine = true;
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//緩衝區寫滿後，不會阻塞進程,會報錯
    }
}, false, 1, $enable_coroutine);

//情況3：在UserProcess進程裡面如果設定了非同步回調(例如設定定時器、Swoole\Event::add等)，
//為了防止其他回調函數得不到 EventLoop 的調度，Swoole會把sendMessage轉換成非同步IO
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    swoole_timer_tick(2000, function ($interval) use ($worker, $serv) {
        echo "timer\n";
    });
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//緩衝區寫滿後，不會阻塞進程,會報錯
    }
}, false);

$serv->addProcess($userProcess);

$serv->start();
```

 - 同理，[Task進程](/learn?id=taskworker進程)通過`sendMessage()`進程間通訊是一樣的，不同的是task進程開啟協程支持是通過Server的[task_enable_coroutine](/server/setting?id=task_enable_coroutine)配置開啟，並且不存在`情況3`，也就是說task進程不會因為開啟非同步回調就將sendMessage非同步IO。


## 什麼是EventLoop

所謂`EventLoop`，即事件循環，可以簡單地理解為epoll_wait，會把所有要發生事件的句柄（fd）加入到`epoll_wait`中，這些事件包括可讀，可寫，出錯等。

對應的進程就阻塞在`epoll_wait`這個內核函數上，當發生了事件(或超時)後`epoll_wait`這個函數就會結束阻塞返回結果，就可以回調相應的PHP函數，例如，收到客戶端發來的數據，回調`onReceive`回調函數。

當有大量的fd放入到了`epoll_wait`中，並且同時產生了大量的事件，`epoll_wait`函數返回的時候就會挨個調用相應的回調函數，叫做一輪事件循環，即IO多路複用，然後再次阻塞調用`epoll_wait`進行下一輪事件循環。
## TCP数据包边界问题

在没有并发的情况下，[快速启动中的代码](/start/start_tcp_server)可以正常运行，但是并发高了就会有TCP数据包边界问题。TCP协议在底层机制上解决了UDP协议的顺序和丢包重传问题，但相比UDP又带来了新的问题。TCP协议是流式的，数据包没有边界，应用程序使用TCP通信就会面临这些难题，俗称TCP粘包问题。

因为TCP通信是流式的，在接收1个大数据包时，可能会被拆分成多个数据包发送。多次Send底层也可能会合并成一次进行发送。这里就需要2个操作来解决：

* 分包：Server收到了多个数据包，需要拆分数据包
* 合包：Server收到的数据只是包的一部分，需要缓存数据，合并成完整的包

所以TCP网络通信时需要设定通信协议。常见的TCP通用网络通信协议有HTTP、HTTPS、FTP、SMTP、POP3、IMAP、SSH、Redis、Memcache、MySQL等。

值得一提的是，Swoole内置了很多常见通用协议的解析，来解决这些协议的服务器的TCP数据包边界问题，只需要简单的配置即可，参考[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol)。

除了通用协议外还可以自定义协议，Swoole支持了2种类型的自定义网络通信协议。

* **EOF结束符协议**

EOF协议处理的原理是每个数据包结尾加一串特殊字符表示包已结束。如Memcache、FTP、SMTP都使用\r
作为结束符。发送数据时只需要在包末尾增加\r
即可。使用EOF协议处理，一定要确保数据包中间不会出现EOF，否则会造成分包错误。

在Server和Client的代码中只需要设置2个参数就可以使用EOF协议处理。

```php
$server->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r
",
));
$client->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r
",
));
```

但上述EOF的配置性能会比较差，Swoole会遍历每个字节，查看数据是否是\r
，除了上述方式还可以这样设置。

```php
$server->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r
",
));
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r
",
));
```
这组配置性能会好很多，不用遍历数据，但是只能解决分包问题，没法解决合包问题，也就是说可能onReceive一下收到客户端发来的好几个请求，需要自行分包，例如explode("\r
", $data)，这组配置的最大用途是，如果请求应答式的服务(例如终端敲命令)，无需考虑拆分数据的问题。原因是客户端在发起一次请求后，必须等到服务器端返回当前请求的响应数据，才会发起第二次请求，不会同时发送2个请求。

* **固定包头+包体协议**

固定包头的方法非常通用，在服务器端程序中经常能看到。这种协议的特点是一个数据包总是由包头+包体2部分组成。包头由一个字段指定了包体或整个包的长度，长度一般是使用2字节/4字节整数来表示。服务器收到包头后，可以根据长度值来精确控制需要再接收多少数据就是完整的数据包。Swoole的配置可以很好的支持这种协议，可以灵活地设置4项参数应对所有情况。

Server在onReceive回调函数中处理数据包，当设置了协议处理后，只有收到一个完整数据包时才会触发onReceive事件。客户端在设置了协议处理后，调用[$client->recv()](/client?id=recv)不再需要传入长度，recv函数在收到完整数据包或发生错误后返回。

```php
$server->set(array(
    'open_length_check' => true,
    'package_max_length' => 81920,
    'package_length_type' => 'n', //see php pack()
    'package_length_offset' => 0,
    'package_body_offset' => 2,
));
```

!> 具体每个配置的含义参见服务端/客户端章节的[配置](/server/setting?id=open_length_check)小节

## 什么是IPC

同一台主机上两个进程间通信(简称IPC)的方式有很多种，在Swoole下使用了2种方式Unix Socket和sysvmsg，下面分别介绍：

- **Unix Socket**  

    全名 UNIX Domain Socket，简称UDS，使用套接字的API(socket，bind，listen，connect，read，write，close等)，和TCP/IP不同的是不需要指定ip和port，而是通过一个文件名来表示(例如FPM和Nginx之间的/tmp/php-fcgi.sock)，UDS是Linux内核实现的全内存通信，无任何IO消耗。在1进程write，1进程read，每次读写1024字节数据的测试中，100万次通信仅需1.02秒，而且功能非常的强大，Swoole下默认用的就是这种IPC方式。  
      
    * **SOCK_STREAM 和 SOCK_DGRAM**  

        - Swoole下面使用UDS通讯有两种类型，SOCK_STREAM 和 SOCK_DGRAM，可以简单的理解为TCP和UDP的区别，当使用SOCK_STREAM类型的时候同样需要考虑[TCP数据包边界问题](/learn?id=tcp数据包边界问题)。   
        - 当使用SOCK_DGRAM类型的时候不需要考虑TCP数据包边界问题，每个send()的数据都是有边界的，发送多大的数据接收的时候就收到多大的数据，没有传输过程中的丢包、乱序问题，send写入和recv读取的顺序是完全一致的。send返回成功后一定是可以recv到。 

    在IPC传输的数据比较小时非常适合用SOCK_DGRAM这种方式，**由于IP包每个最大有64k的限制，所以用SOCK_DGRAM进行IPC时候单次发送数据不能大于64k，同时要注意收包速度太慢操作系统缓冲区满了会丢弃包，因为UDP是允许丢包的，可以适当调大缓冲区**。

- **sysvmsg**
     
    即Linux提供的消息队列，这种IPC方式通过一个文件名来作为key进行通讯，这种方式非常的不灵活，实际项目使用的并不多，不做过多介绍。

    * **此种IPC方式只有两个场景下有用:**

        - 防止丢数据，如果整个服务都挂掉，再次启动队列中的消息也在，可以继续消费，**但同样有脏数据的问题**。
        - 可以外部投递数据，比如Swoole下的Worker进程通过消息队列给Task进程投递任务，第三方的进程也可以投递任务到队列里面让Task消费，甚至可以在命令行手动添加消息到队列。
## 主进程、Reactor线程、Worker进程、Task进程、Manager进程的区别与联系 :id=diff-process

### 主进程

* 主进程是一个多线程进程，参考[进程/线程结构图](/server/init?id=进程线程结构图)

### Reactor线程

* Reactor线程是在主进程中创建的线程
* 负责维护客户端`TCP`连接、处理网络`IO`、处理协议、收发数据
* 不执行任何PHP代码
* 将`TCP`客户端发来的数据缓冲、拼接、拆分成完整的一个请求数据包

### Worker进程

* 接受由`Reactor`线程投递的请求数据包，并执行`PHP`回调函数处理数据
* 生成响应数据并发给`Reactor`线程，由`Reactor`线程发送给`TCP`客户端
* 可以是异步非阻塞模式，也可以是同步阻塞模式
* `Worker`以多进程的方式运行

### TaskWorker进程

* 接受由`Worker`进程通过 Swoole\Server->[task](/server/methods?id=task)/[taskwait](/server/methods?id=taskwait)/[taskCo](/server/methods?id=taskCo)/[taskWaitMulti](/server/methods?id=taskWaitMulti) 方法投递的任务
* 处理任务，并将结果数据返回（使用 [Swoole\Server->finish](/server/methods?id=finish)）给`Worker`进程
* 完全是**同步阻塞**模式
* `TaskWorker`以多进程的方式运行，[task完整示例](/start/start_task)

### Manager进程

* 负责创建/回收`worker`/`task`进程

他们之间的关系可以理解为`Reactor`就是`nginx`，`Worker`就是`PHP-FPM`。`Reactor`线程异步并行地处理网络请求，然后再转发给`Worker`进程中去处理。`Reactor`和`Worker`间通过[unixSocket](/learn?id=什么是IPC)进行通信。

在`PHP-FPM`的应用中，经常会将一个任务异步投递到`Redis`等队列中，并在后台启动一些`PHP`进程异步地处理这些任务。`Swoole`提供的`TaskWorker`是一套更完整的方案，将任务的投递、队列、`PHP`任务处理进程管理合为一体。通过底层提供的`API`可以非常简单地实现异步任务的处理。另外`TaskWorker`还可以在任务执行完成后，再返回一个结果反馈到`Worker`。

`Swoole`的`Reactor`、`Worker`、`TaskWorker`之间可以紧密地结合起来，提供更高级的使用方式。

一个更通俗的比喻，假设`Server`就是一个工厂，那`Reactor`就是销售，接受客户订单。而`Worker`就是工人，当销售接到订单后，`Worker`去工作生产出客户要的东西。而`TaskWorker`可以理解为行政人员，可以帮助`Worker`干些杂事，让`Worker`专心工作。

如图：

![process_demo](_images/server/process_demo.png)

## Server的三种运行模式介绍

在`Swoole\Server`构造函数的第三个参数，可以填3个常量值 -- [SWOOLE_BASE](/learn?id=swoole_base)，[SWOOLE_PROCESS](/learn?id=swoole_process)和[SWOOLE_THREAD](/learn?id=swoole_thread)，下面将分别介绍这三个模式的区别以及优缺点

### SWOOLE_PROCESS

SWOOLE_PROCESS模式的`Server`所有客户端的TCP连接都是和[主进程](/learn?id=reactor线程)建立的，内部实现比较复杂，用了大量的进程间通信、进程管理机制。适合业务逻辑非常复杂的场景。`Swoole`提供了完善的进程管理、内存保护机制。
在业务逻辑非常复杂的情况下，也可以长期稳定运行。

`Swoole`在[Reactor](/learn?id=reactor线程)线程中提供了`Buffer`的功能，可以应对大量慢速连接和逐字节的恶意客户端。

#### 进程模式的优点：

* 连接与数据请求发送是分离的，不会因为某些连接数据量大某些连接数据量小导致`Worker`进程不均衡
* `Worker`进程发生致命错误时，连接并不会被切断
* 可实现单连接并发，仅保持少量`TCP`连接，请求可以并发地在多个`Worker`进程中处理

#### 进程模式的缺点：

* 存在`2`次`IPC`的开销，`master`进程与`worker`进程需要使用[unixSocket](/learn?id=什么是IPC)进行通信
* `SWOOLE_PROCESS`不支持PHP ZTS，在这种情况下只能使用`SWOOLE_BASE`或者设置[single_thread](/server/setting?id=single_thread)为true

### SWOOLE_BASE

SWOOLE_BASE这种模式就是传统的异步非阻塞`Server`。与`Nginx`和`Node.js`等程序是完全一致的。

[worker_num](/server/setting?id=worker_num)参数对于`BASE`模式仍然有效，会启动多个`Worker`进程。

当有TCP连接请求进来的时候，所有的Worker进程去争抢这一个连接，并最终会有一个worker进程成功直接和客户端建立TCP连接，之后这个连接的所有数据收发直接和这个worker通讯，不经过主进程的Reactor线程转发。

* `BASE`模式下没有`Master`进程的角色，只有[Manager](/learn?id=manager进程)进程的角色。
* 每个`Worker`进程同时承担了[SWOOLE_PROCESS](/learn?id=swoole_process)模式下[Reactor](/learn?id=reactor线程)线程和`Worker`进程两部分职责。
* `BASE`模式下`Manager`进程是可选的，当设置了`worker_num=1`，并且没有使用`Task`和`MaxRequest`特性时，底层将直接创建一个单独的`Worker`进程，不创建`Manager`进程

#### BASE模式的优点：

* `BASE`模式没有`IPC`开销，性能更好
* `BASE`模式代码更简单，不容易出错

#### BASE模式的缺点：

* `TCP`连接是在`Worker`进程中维持的，所以当某个`Worker`进程挂掉时，此`Worker`内的所有连接都将被关闭
* 少量`TCP`长连接无法利用到所有`Worker`进程
* `TCP`连接与`Worker`是绑定的，长连接应用中某些连接的数据量大，这些连接所在的`Worker`进程负载会非常高。但某些连接数据量小，所以在`Worker`进程的负载会非常低，不同的`Worker`进程无法实现均衡。
* 如果回调函数中有阻塞操作会导致`Server`退化为同步模式，此时容易导致TCP的[backlog](/server/setting?id=backlog)队列塞满问题。

#### BASE模式的适用场景：

如果客户端连接之间不需要交互，可以使用`BASE`模式。如`Memcache`、`HTTP`服务器等。

#### BASE模式的限制：

在 `BASE` 模式下，[Server 方法](/server/methods)除了 [send](/server/methods?id=send) 和 [close](/server/methods?id=close)以外，其他的方法都**不支持**跨进程执行。

!> v4.5.x 版本的 `BASE` 模式下仅`send`方法支持跨进程执行；v4.6.x 版本中只有`send`和`close`方法支持。
### SWOOLE_THREAD

SWOOLE_THREAD是`Swoole 6.0`引入的新运行模式，借助`PHP zts`模式，我们现在可以开启多线程模式的服务。

[worker_num](/server/setting?id=worker_num)参数对于`THREAD`模式仍然有效，只不过会由创建多进程变成创建多线程，会启动多个`Worker`线程。

只会有一个进程，子进程会转化为子线程负责接收客户端的请求。

#### THREAD模式的优点：
* 进程间通信更加简单，没有额外的IPC通信消耗。
* 调试程序更加方便，由于只有一个进程，`gdb -p`会更简单。
* 拥有协程并发 IO 编程的便利，又可以拥有多线程并行执行、共享内存堆栈的优势。

#### THREAD模式的缺点：
* 发生 Crash 时或调用了 Process::exit() 整个进程都会退出，需要在客户端做好错误重试、断线重连等故障恢复逻辑，另外需要使用 supervisor 和 docker/k8s 在进程退出后自动重启。
* `ZTS` 和 锁的操作可能会额外的开销，性能可能会比 `NTS` 多进程并发模型差 10% 左右，如果是无状态的服务，仍建议使用 `NTS` 多进程的运行方式。
* 不支持线程之间传递对象和资源。

#### THREAD模式的适用场景：
* THREAD模式在开发游戏服务器、通信服务器方面更有效率。

## Process、Process\Pool、UserProcess的区别是什么 :id=process-diff

### Process

[Process](/process/process)是 Swoole 提供的进程管理模块，用来替代 PHP 的 `pcntl`。
 
* 可以方便的实现进程间通讯；
* 支持重定向标准输入和输出，在子进程内`echo`不会打印屏幕，而是写入管道，读键盘输入可以重定向为管道读取数据；
* 提供了[exec](/process/process?id=exec)接口，创建的进程可以执行其他程序，与原`PHP`父进程之间可以方便的通信；

!> 在协程环境中无法使用`Process`模块，可以使用`runtime hook`+`proc_open`实现，参考[协程进程管理](/coroutine/proc_open)

### Process\Pool

[Process\Pool](/process/process_pool)是将Server的进程管理模块封装成了PHP类，支持在PHP代码中使用Swoole的进程管理器。

在实际项目中经常需要写一些长期运行的脚本，如基于`Redis`、`Kafka`、`RabbitMQ`实现的多进程队列消费者，多进程爬虫等等，开发者需要使用`pcntl`和`posix`相关的扩展库实现多进程编程，但也需要开发者具备深厚的`Linux`系统编程功底，否则很容易出现问题，使用Swoole提供的进程管理器可大大简化多进程脚本编程工作。

* 保证工作进程的稳定性；
* 支持信号处理；
* 支持消息队列和`TCP-Socket`消息投递功能；

### UserProcess

`UserProcess`是使用[addProcess](/server/methods?id=addprocess)添加的一个用户自定义的工作进程，通常用于创建一个特殊的工作进程，用于监控、上报或者其他特殊的任务。

`UserProcess`虽然会托管到 [Manager进程](/learn?id=manager进程)，但是和 [Worker进程](/learn?id=worker进程) 相比是较为独立的进程，用于执行自定义功能。
