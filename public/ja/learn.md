#的基础知识

##四种设置回调函数的方式

* **匿名函数**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
不使用`use`向匿名函数传递参数

* **类静态方法**

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
对应的静态方法必须为`public`

* **函数**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **对象方法**

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

对应的方法必须为`public`

##同步IO/异步IO

在`Swoole4+`下所有的业务代码都是同步写法（`Swoole1.x`时代才支持异步写法，现在已经移除了异步客户端，对应的需求完全可以用协程客户端实现），完全没有心智负担，符合人类思维习惯，但同步的写法底层可能有`同步IO/异步IO`之分。

无论是同步IO/异步IO，`Swoole/Server`都可以维持大量`TCP`客户端连接(参考[SWOOLE_PROCESS模式](/learn?id=swoole_process))。你的服务是阻塞还是非阻塞不需要单独的配置某些参数，取决于你的代码里面是否有同步IO的操作。

**什么是同步IO：**
 
简单的例子就是执行到`MySQL->query`的时候，这个进程什么事情都不做，等待MySQL返回结果，返回结果后再向下执行代码，所以同步IO的服务并发能力是很差的。

**什么样的代码是同步IO：**

 * 没有开启[一键协程化](/runtime)的时候，那么你的代码里面绝大部分涉及IO的操作都是同步IO的，协程化后，就会变成异步IO，进程不会傻等在那里，参考[协程调度](/coroutine?id=协程调度)。
 * 有些`IO`是没法一键协程化，没法将同步IO变为异步IO的，例如`MongoDB`(相信`Swoole`会解决这个问题)，需要写代码时候注意。

不使用[协程](/coroutine)是为了提高并发的，如果我的应用就没有高并发，或者必须要用某些无法异步化IO的操作(例如上文的MongoDB)，那么你完全可以不开启[一键协程化](/runtime)，关闭[enable_coroutine](/server/setting?id=enable_coroutine)，多开一些`Worker`进程，这就是和`Fpm/Apache`是一样的模型了，值得一提的是由于`Swoole`是常驻进程的，即使同步IO性能也会有很大提升，实际应用中也有很多公司这样做。

###同步IO转换成异步IO

[上小节](/learn?id=同步io异步io)介绍了什么是同步/异步IO，在`Swoole`下面，有些情况同步的`IO`操作是可以转换成异步IO的。
 
 - 开启[一键协程化](/runtime)后，`MySQL`、`Redis`、`Curl`等操作会变成异步IO。
 - 利用[Event](/event)模块手动管理事件，将fd加到[EventLoop](/learn?id=什么是eventloop)里面，变成异步IO，例子：

```php
//利用inotify监控文件变化
$fd = inotify_init();
//将$fd添加到Swoole的EventLoop
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd);//文件发生变化后读取变化的文件。
    var_dump($var);
});
```

上述代码如果不调用`Swoole\Event::add`将IO异步化，直接`inotify_read()`将阻塞Worker进程，其他的请求将得不到处理。

 - 使用`Swoole\Server`的[sendMessage()](/server/methods?id=sendMessage)方法进行进程间通讯，默认`sendMessage`是同步IO，但有些情况是会被`Swoole`转换成异步IO，用[User进程](/server/methods?id=addprocess)举例：

```php
$serv = new Swoole\Server("0.0.0.0", 9501, SWOOLE_BASE);
$serv->set(
    [
        'worker_num' => 1,
    ]
);

$serv->on('pipeMessage', function ($serv, $src_worker_id, $data) {
    echo "#{$serv->worker_id} message from #$src_worker_id: $data\n";
    sleep(10);//不接收sendMessage发来的数据，缓冲区将很快写满
});

$serv->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data) {

});

//情况1：同步IO(默认行为)
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//默认情况下，缓存区写满后，此处会阻塞
    }
}, false);

//情况2：通过enable_coroutine参数开启UserProcess进程的协程支持，为了防止其他协程得不到 EventLoop 的调度，
//Swoole会把sendMessage转换成异步IO
$enable_coroutine = true;
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//缓存区写满后，不会阻塞进程,会报错
    }
}, false, 1, $enable_coroutine);

//情况3：在UserProcess进程里面如果设置了异步回调(例如设置定时器、Swoole\Event::add等)，
//为了防止其他回调函数得不到 EventLoop 的调度，Swoole会把sendMessage转换成异步IO
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    swoole_timer_tick(2000, function ($interval) use ($worker, $serv) {
        echo "timer\n";
    });
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//缓存区写满后，不会阻塞进程,会报错
    }
}, false);

$serv->addProcess($userProcess);

$serv->start();
```

同理，[Task进程](/learn?id=taskworker进程)通过`sendMessage()`进程间通讯是一样的，不同的是task进程开启协程支持是通过Server的[task_enable_coroutine](/server/setting?id=task_enable_coroutine)配置开启，并且不存在`情况3`，也就是说task进程不会因为开启异步回调就将sendMessage异步IO。

##什么是EventLoop

所谓`EventLoop`，即事件循环，可以简单的理解为epoll_wait，会把所有要发生事件的句柄（fd）加入到`epoll_wait`中，这些事件包括可读，可写，出错等。

对应的进程就阻塞在`epoll_wait`这个内核函数上，当发生了事件(或超时)后`epoll_wait`这个函数就会结束阻塞返回结果，就可以回调相应的PHP函数，例如，收到客户端发来的数据，回调`onReceive`回调函数。

当有大量的fd放入到了`epoll_wait`中，并且同时产生了大量的事件，`epoll_wait`函数返回的时候就会挨个调用相应的回调函数，叫做一轮事件循环，即IO多路复用，然后再次阻塞调用`epoll_wait`进行下一轮事件循环。
## TCPデータパケットの境界問題

並発がない場合[クイックスタート中のコード](/start/start_tcp_server)は正常に動作できますが、並発が高くなるとTCPデータパケットの境界問題が発生します。`TCP`プロトコルは基盤的なメカニズムで`UDP`プロトコルの順序とパケットの再送問題を解決していますが、`UDP`と比較して新たな問題も引き起こします。`TCP`プロトコルはストリーム型であり、データパケットには境界がありません。アプリケーションが`TCP`通信を使用すると、これらの難問に直面します。これらは俗にTCPの粘着パケット問題と呼ばれています。

`TCP`通信はストリーム型であるため、大きなデータパケットを受信すると、複数のデータパケットに分割されて送信される可能性があります。複数回の`Send`は基盤レベルで一度に送信される可能性もあります。ここでは2つの操作が必要です：

* パケット分割：`Server`が複数のデータパケットを受信し、データパケットを分割する必要があります。
* パケット結合：`Server`が受信したデータがパケットの一部であるため、データをキャッシュし、完全なパケットに結合する必要があります。

したがって、TCPネットワーク通信時には通信プロトコルを設定する必要があります。一般的なTCP汎用ネットワーク通信プロトコルには`HTTP`、`HTTPS`、`FTP`、`SMTP`、`POP3`、`IMAP`、`SSH`、`Redis`、`Memcache`、`MySQL`があります。

注目すべきは、Swooleが多くの一般的なプロトコルの解析を内蔵しており、これらのプロトコルのサーバーにおけるTCPデータパケットの境界問題を解決するのに非常に簡単です。単に設定するだけで済みます。[open_http_protocol](/server/setting?id=open_http_protocol)/[open_http2_protocol](/http_server?id=open_http2_protocol)/[open_websocket_protocol](/server/setting?id=open_websocket_protocol)/[open_mqtt_protocol](/server/setting?id=open_mqtt_protocol)を参照してください。

一般的なプロトコル以外にもカスタムプロトコルを定義することができます。`Swoole`は2種類のカスタムネットワーク通信プロトコルをサポートしています。

* **EOF終端文字列プロトコル**

`EOF`プロトコルの処理原理は、各データパケットの最後に特別な文字列を加えてパケットが終了したことを示します。例えば`Memcache`、`FTP`、`SMTP`は`\r\n`を終端文字列として使用しています。データを送信する際には、パケットの最後に`\r\n`を加えるだけで済みます。`EOF`プロトコルを使用する場合、データパケットの途中に`EOF`が現れることは絶対に避けなければなりません。そうでなければ分包エラーを引き起こす可能性があります。

`Server`と`Client`のコードでは、単に2つのパラメータを設定するだけで`EOF`プロトコルを使用して処理できます。

```php
$server->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
```

しかし、上記の`EOF`設定の性能は比較的低く、Swooleは各バイトを走査してデータが`\r\n`かを確認します。上記の方法以外にも次のように設定できます。

```php
$server->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
```
この設定の性能はずっと良くなり、データの走査は必要ありませんが、分包問題を解決するだけで、合包問題は解決できません。つまり、`onReceive`で一度にクライアントから複数のリクエストを受け取った場合、自分で分包する必要があります。例えば`explode("\r\n", $data)`のように。この設定の最大の用途は、リクエスト応答型のサービス（例えばターミナルで命令を入力する）では、データの分割を考慮する必要がないことです。その理由は、クライアントが一度のリクエストを送信した後、サーバー側が現在のリクエストの応答データ返回するのを待ってから、次のリクエストを送りません。同時に2つのリクエストを送信することはありません。

* **固定ヘッダ+ボディプロトコル**

固定ヘッダの方法は非常に汎用的であり、サーバー側のプログラムでよく見られます。このプロトコルの特徴は、データパケットが常にヘッダ+ボディの2つの部分で構成されていることです。ヘッダはボディまたは全体のパケットの長さを指定するフィールドがあり、長さは通常、2バイト/4バイトの整数で表されます。サーバーはヘッダを受信した後、長さの値に基づいて、完全なデータパケットとしてさらにどれだけのデータを受信する必要があるかを正確に制御できます。Swooleの設定はこのようなプロトコルを非常によくサポートしており、柔軟に4つのパラメータを設定してすべての状況に対応できます。

`Server`は[onReceive](/server/events?id=onreceive)回调関数でデータパケットを処理し、プロトコル処理を設定した後、完全なデータパケットを受信したときにのみ[onReceive](/server/events?id=onreceive)イベントがトリガーされます。クライアントはプロトコル処理を設定した後、[$client->recv()](/client?id=recv)を呼び出す際に長さを渡す必要がなくなります。`recv`関数は完全なデータパケットを受信したり、エラーが発生した後に返回します。

```php
$server->set(array(
    'open_length_check' => true,
    'package_max_length' => 81920,
    'package_length_type' => 'n', //see php pack()
    'package_length_offset' => 0,
    'package_body_offset' => 2,
));
```

!> 各設定の具体的な意味は、「サーバー/クライアント」の章の[設定](/server/setting?id=open_length_check)小节を参照してください。

## IPCとは何か

同じホスト上の2つのプロセス間の通信（略称IPC）には多くの方法がありますが、Swooleでは`Unix Socket`と`sysvmsg`の2つの方法を使用しています。以下にそれぞれ紹介します：

- **Unix Socket**  

    全名 UNIX Domain Socket、略称`UDS`は、ソケットのAPI（socket、bind、listen、connect、read、write、closeなど）を使用し、TCP/IPとは異なりIPとポートを指定する必要はなく、ファイル名で表されます（例えばFPMとNginxの間の`/tmp/php-fcgi.sock`）。UDSはLinuxカーネルが実現する全メモリ通信であり、いかなる`IO`消費もありません。1プロセスがwriteし、別のプロセスがreadし、1024バイトのデータを100万回通信するテストでは、わずか1.02秒で完了し、非常に強力です。SwooleではデフォルトでこのIPC方法を使用しています。  
      
    * **`SOCK_STREAM`と`SOCK_DGRAM`**  

        - SwooleではUDS通信には2つのタイプがあり、`SOCK_STREAM`と`SOCK_DGRAM`です。これはTCPとUDPの違いを簡単に理解できます。`SOCK_STREAM`タイプを使用する場合も[TCPデータパケットの境界問題](/learn?id=tcp数据包边界问题)を考慮する必要があります。  
        - `SOCK_DGRAM`タイプを使用する場合、TCPデータパケットの境界問題を考慮する必要はありません。各`send()`のデータには境界があり、どれだけ大きなデータを送信しても受信したデータの大きさと同じであり、伝送過程でのパケットの損失や乱序の問題は発生しません。`send`の写入と`recv`の読み取りの順序は完全に一致しています。`send`が成功返回した後、必ず`recv`で受信できます。  

    IPCで伝送されるデータが小さい場合は、`SOCK_DGRAM`この方法が非常に適しています。**IPパケットには最大64kの制限があるため、SOCK_DGRAMでIPCを行う際には、一度に送信するデータは64kを超えることはできず、同時に受信速度が遅すぎてオペレーティングシステムのバッファが満たされてパケットが捨てられることに注意する必要があります。UDPはパケットの損失を許しているので、バッファを適切に大きくすることができます。**

- **sysvmsg**
     
    Linuxが提供する「メッセージキュー」は、このIPC方法で、ファイル名を`key`として通信します。この方法は非常に非柔軟で、実際のプロジェクトでの使用はそれほど多くありません。詳細は省略します。

    * **このIPC方法が役立つシーンは以下の2つだけです:**

        - データの損失を防ぐため、もしサービス全体が停止しても、キューに残されたメッセージは残り、消費を続けることができます。**しかし、やはり汚れたデータの問題があります。**
        - 外部からデータを投入することができ、例えばSwooleの`Workerプロセス`が`Taskプロセス`にメッセージキューを通じてタスクを投入したり、サードパーティのプロセスもタスクをキューに投入してTaskが消費したり、コマンドラインから手動でメッセージをキューに追加することもできます。
## Masterプロセス、Reactorスレッド、Workerプロセス、Taskプロセス、Managerプロセスの違いと関連性 :id=diff-process

### Masterプロセス

* Masterプロセスはマルチスレッドプロセスで、[プロセス/スレッド構造図](/server/init?id=プロセススレッド構造図)を参照してください。

### Reactorスレッド

* ReactorスレッドはMasterプロセス内で作成されるスレッドです。
* クライアントの`TCP`接続を維持し、ネットワーク`IO`を処理し、プロトコルを処理し、データを送受信します。
* PHPコードは実行しません。
* `TCP`クライアントからのデータをバッファし、組み立て、分割して完全な一つのリクエストデータパケットにします。

### Workerプロセス

* Reactorスレッドから投与されたリクエストデータパケットを受け取り、PHP回调関数を実行してデータを処理します。
* 応答データを生成し、Reactorスレッドに同時に送信し、Reactorスレッドが`TCP`クライアントに送信します。
* 异步非阻塞モードでも同步阻塞モードでも実行できます。
* Workerはマルチプロセスで動作します。

### TaskWorkerプロセス

* WorkerプロセスがSwoole\Server->task/[taskwait]/[taskCo]/[taskWaitMulti]メソッドを通じて投与されたタスクを受け取り、タスクを処理し、結果データをWorkerプロセスに戻します（Swoole\Server->finishメソッドを使用）。
* 完全に**同步阻塞**モードです。
* TaskWorkerはマルチプロセスで動作し、taskの完全な例は[start/start_task]を参照してください。

### Managerプロセス

* worker/taskプロセスの作成/回収を担当します。

彼らの関係はReactorがnginxであり、WorkerがPHP-FPMであると理解できます。Reactorスレッドは异步に並行的にネットワークリクエストを処理し、それらをWorkerプロセスに転送して処理します。ReactorとWorkerの間では[unixSocket](/learn?id=什么是IPC)を使用して通信します。

PHP-FPMのアプリケーションでは、よくタスクをRedisなどのキューに异步に投与し、バックグラウンドでいくつかのPHPプロセスを异步にこれらのタスクを処理します。Swooleが提供するTaskWorkerは、タスクの投与、キュー、PHPタスク処理プロセスの管理を一体化したより完全なソリューションです。底层の提供されるAPIを使用して、异步タスクの処理を非常に簡単に実現できます。また、TaskWorkerはタスク実行完了後に、Workerに結果をフィードバックすることもできます。

SwooleのReactor、Worker、TaskWorkerは密接に組み合わさることができ、より高度な使用方法を提供します。

もっと一般的な比喩では、Serverが工場であるならば、Reactorはセールスマンであり、顧客の注文を受け付けます。Workerは労働者であり、セールスマンが注文を受け取った後、Workerが働き、顧客が必要とするものを生産します。TaskWorkerは行政職員と理解でき、Workerが雑事を手伝い、Workerが集中して働くことができます。

図：

![process_demo](_images/server/process_demo.png)

## Serverの3つの運用モードの紹介

Swoole\Serverのコンストラクタの3番目のパラメータには、3つの定数値を填めることができます -- [SWOOLE_BASE](/learn?id=swoole_base)、[SWOOLE_PROCESS](/learn?id=swoole_process)および[SWOOLE_THREAD](/learn?id=swoole_thread)。以下では、これら3つのモードの違いと利点と欠点をそれぞれ紹介します。

### SWOOLE_PROCESS

SWOOLE_PROCESSモードのServerは、すべてのクライアントのTCP接続が[マスタープロセス](/learn?id=reactor线程)と構築されており、内部実装は複雑で、プロセス間の通信やプロセス管理メカニズムが大量に使用されています。ビジネスロジックが非常に複雑なシナリオに適しています。Swooleは完全なプロセス管理とメモリ保護メカニズムを提供しています。
ビジネスロジックが非常に複雑な場合でも、長期にわたって安定して実行できます。

Swooleは[Reactor](/learn?id=reactor线程)スレッド内でBuffer機能を提供しており、多くの低速接続や逐字节的悪意のあるクライアントに対応できます。

#### プロセスモードの利点：

* 接続とデータリクエストの送信は分離されており、一部の接続のデータ量が多く、別の接続のデータ量が少ないためにWorkerプロセスが不均衡にならない
* Workerプロセスが致命的なエラーが発生した場合でも、接続は切断されない
* 単一接続の並行性を実現でき、少量のTCP接続のみを維持し、リクエストは複数のWorkerプロセスで並行して処理される

#### プロセスモードの欠点：

* 2回のIPCコストがあり、masterプロセスとworkerプロセスはunixSocket（/learn?id=什么是IPC）を使用して通信する必要がある
* SWOOLE_PROCESSはPHP ZTSをサポートしていないため、この状況ではSWOOLE_BASEを使用するか、single_thread（/server/setting?id=single_thread）をtrueに設定する必要がある

### SWOOLE_BASE

SWOOLE_BASEモードは従来の异步非阻塞Serverです。NginxやNode.jsなどのプログラムと完全に同じです。

[worker_num](/server/setting?id=worker_num)パラメータはBASEモードでも有効であり、複数のWorkerプロセスを起動します。

TCP接続リクエストが来たら、すべてのWorkerプロセスがこの接続を奪い合い、最終的に一つのworkerプロセスが成功して直接クライアントとTCP接続を確立し、その接続のすべてのデータの送受信はこのworkerと直接通信し、masterプロセスのReactorスレッドを経由せずに行われます。

* BASEモードにはMasterプロセスの役割はなく、Managerプロセスの役割のみがあります。
* 各WorkerプロセスはSWOOLE_PROCESSモードのReactorスレッドとWorkerプロセスの2つの役割を同時に担っています。
* BASEモードではManagerプロセスはオプションであり、worker_num=1を設定し、TaskやMaxRequest特性を使用していない場合、基層は単独のWorkerプロセスを直接作成し、Managerプロセスは作成しません。

#### BASEモードの利点：

* BASEモードにはIPCコストがなく、性能が良い
* BASEモードのコードはシンプルで、間違いが少ない

#### BASEモードの欠点：

* TCP接続はWorkerプロセス内で維持されているため、あるWorkerプロセスが落ちた場合、そのWorker内のすべての接続が閉じられる
* 少量のTCP長接続はすべてのWorkerプロセスを利用できない
* TCP接続とWorkerは結びついているため、長接続アプリケーションでは一部の接続のデータ量が多く、これらの接続があるWorkerプロセスの負荷は非常に高くなります。しかし、一部の接続のデータ量が少ないため、Workerプロセスの負荷は非常に低くなります。異なるWorkerプロセス間でバランスが取れません。
* 回调関数にブロック操作が含まれていると、Serverは同期モードに退化し、TCPの[backlog](/server/setting?id=backlog)キューがいっぱいに塞がる問題が発生する可能性があります。

#### BASEモードの適用シナリオ：

クライアント間の対話が必要ない場合は、BASEモードを使用できます。例えばMemcacheやHTTPサーバーなどです。

#### BASEモードの制限：

BASEモードでは、Serverメソッド（/server/methods）はsend（/server/methods?id=send）とclose（/server/methods?id=close）以外の方法が**跨プロセス実行**をサポートしていません。

!> v4.5.xのバージョンでは、BASEモードではsendメソッドのみが跨プロセス実行をサポートしています。v4.6.xバージョンでは、sendとcloseメソッドのみがサポートされています。
### SWOOLE_THREAD

SWOOLE_THREADは`Swoole 6.0`で導入された新しい実行モードで、PHPのZTS（Zend Thread Safety）モードを利用して、今ではマルチスレッドモードのサービスを開始することができます。

[worker_num](/server/setting?id=worker_num)パラメータはTHREADモードでも有効ですが、マルチプロセスを生成することからマルチスレッドを生成することになり、複数のWorkerスレッドが起動します。

プロセスは一つだけで、子プロセスは子スレッドに変わり、クライアントからのリクエストを受け付けます。

#### THREADモードの利点：
* プロセス間の通信が簡単で、追加のIPC通信のコストがありません。
*デバッグがより簡単で、一つのプロセスがあるため、`gdb -p`がより簡単です。
* コルoutineの並列IOプログラミングの便利さがありながら、マルチスレッドの並行実行や共有メモリスタックの利点も持っています。

#### THREADモードの欠点：
*クラッシュが発生したり、Process::exit()が呼び出されたりすると、プロセス全体が終了します。クライアント側ではエラーの再試行や切断后再接続などのフェイルセーフなロジックを整える必要があります。また、supervisorやdocker/k8sを使用してプロセスが終了した後に自動的に再起動する必要があります。
* ZTSとロックの操作には追加のコストがかかり、パフォーマンスはNTSのマルチプロセス並行モデルより約10％悪化することがあります。ステートレスなサービスであれば、依然としてNTSマルチプロセスの実行方式を推奨します。
* スレッド間でオブジェクトやリソースを渡すことはできません。

#### THREADモードの適用シーン：
* THREADモードはゲームサーバーや通信サーバーの開発により効率的です。

## Process、Process\Pool、UserProcessの違いは何ですか :id=process-diff

### Process

[Process](/process/process)はSwooleが提供するプロセス管理モジュールで、PHPの`pcntl`を置き換えます。
 
* プロセス間の通信を容易に実現できます；
*標準入力と出力をリダイレクトし、子プロセス内の`echo`は画面に印刷されず、パイプに書き出されます。キーボード入力はパイプで読み取ることができます；
* [exec](/process/process?id=exec)インターフェースを提供し、作成されたプロセスは他のプログラムを実行でき、元のPHP親プロセスと容易に通信できます；

!>协程環境では`Process`モジュールを使用することはできませんが、`runtime hook`+`proc_open`を使用して実現できます。参考：[协程プロセス管理](/coroutine/proc_open)

### Process\Pool

[Process\Pool](/process/process_pool)はServerのプロセス管理モジュールをPHPクラスに封装し、PHPコードでSwooleのプロセス管理器を使用できるようにしました。

実際のプロジェクトでは、Redis、Kafka、RabbitMQなどに基づいて実装されたマルチプロセスキューのコンシューマーやマルチプロセスクローラーのような長期実行するスクリプトを書くことがよくあります。開発者は`pcntl`と`posix`関連の拡張ライブラリを使用してマルチプロセスプログラミングを実現する必要がありますが、それには深いLinuxシステムプログラミングの知識が必要であり、そうでなければ問題を容易に引き起こす可能性があります。Swooleが提供するプロセス管理器を使用することで、マルチプロセススクリプトプログラミングの作業を大幅に簡略化できます。

* 作業プロセスの安定性を保証します；
*シグナル処理をサポートします；
* メッセージキューとTCP-Socketメッセージ配信機能をサポートします；

### UserProcess

`UserProcess`は[addProcess](/server/methods?id=addprocess)を使用して添加されたユーザー定義の作業プロセスで、通常は監視、報告、またはその他の特殊なタスクのために特別な作業プロセスを作成するために使用されます。

`UserProcess`は[Managerプロセス](/learn?id=manager进程)にホストされるものの、[Workerプロセス](/learn?id=worker进程)と比較してより独立したプロセスであり、カスタム機能を実行するために使用されます。
