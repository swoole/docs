# 基礎知識
## 四種設定コールバック関数の方法

* **匿名関数**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
!> `use`を使って匿名関数にパラメータを渡すことができる

* **クラスの静的メソッド**

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
!>対応する静的メソッドは`public`でなければならない

* **関数**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **オブジェクトのメソッド**

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

!> 対応するメソッドは`public`でなければならない
## 同步IO/非同期IO

`Swoole4+`の下では、すべてのビジネスコードが同期書きであり（`Swoole1.x`時代には非同期書きがサポートされていましたが、現在は非同期クライアントが移除され、対応するニーズは完全にキャプチャされたキャプチャクライアントで実現できます）、心の負担がまったくなく、人間の思考習慣に合っていますが、同期書きの下層には「同期IO/非同期IO」が存在する可能性があります。

同期IO/非同期IOにかかわらず、「Swoole/Server」は多くの`TCP`クライアント接続を維持することができます（[SWOOLE_PROCESSモード](/learn?id=swoole_process)を参照）。あなたのサービスがブロッキングなのか非ブロッキングなのかは、コード内に同期IOの操作があるかどうかによって異なり、特別なパラメータを設定する必要はありません。

**同期IOとは何ですか：**

単純な例として、「MySQL->query」が実行されたとき、このプロセスは何もしないで、MySQLからの結果を待ちます。結果が返ってきた後、次のコードを実行します。したがって、同期IOのサービスの並行能力は非常に悪いです。

**同期IOのコードとは何ですか：**

 * [ワンタッチキャプチャ](/runtime)を開始していない場合、コードのほとんどのIO操作が同期IOであり、キャプチャ化された後、それは非同期IOになります。プロセスはそこで無駄に待つことはありません。キャプチャスケジュールについて[参考](/coroutine?id=协程调度)を参照してください。
 * 一部の「IO」はワンタッチキャプチャではできず、同期IOを非同期IOに変えることができないため、例えば「MongoDB」（「Swoole」がこの問題を解決すると信じられる）などです。コードを書く際に注意が必要です。

!> [キャプチャ](/coroutine)は並行性を高めるためのものですが、もし私のアプリケーションに高並行性がなかったり、非同期化できないIO操作（上記のMongoDBのような）が必要な場合は、「ワンタッチキャプチャ」を開始せずに、「enable_coroutine」を「off」にし、より多くの「Worker」プロセスを開始することができます。これは「Fpm/Apache」と同じモデルです。注目すべきは、「Swoole」が常駐プロセスであるため、同期IOの性能も大幅に向上し、実際には多くの企業がこの方法を採用していることです。
### 同期IOから非同期IOへの変換

前節では、同期/非同期IOが何であるかについて説明しましたが、「Swoole」の下では、同期の「IO」操作が非同期IOに変換できる場合があります。
 
 - [ワンタッチキャプチャ](/runtime)を有効にした後、「MySQL」、「Redis」、「Curl」などの操作は非同期IOになります。
 - [Event](/event)モジュールを利用して手動でイベントを管理し、fdを[EventLoop](/learn?id=何がeventloop)に追加することで、非同期IOになります。例：

```php
//inotifyを利用してファイルの変化を監視
$fd = inotify_init();
//$fdをSwooleのEventLoopに追加
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd);//ファイルが変化した後、変化したファイルを読み出す。
    var_dump($var);
});
```

上記のコードを実行しないと、IOは非同期にならず、「inotify_read()」はWorkerプロセスをブロックし、他のリクエストは処理されません。

 - `Swoole\Server`の[sendMessage()](/server/methods?id=sendMessage)メソッドを使用してプロセス間通信を行いますが、デフォルトの`sendMessage`は同期IOですが、場合によっては`Swoole`によって非同期IOに変換されます。例えば[Userプロセス](/server/methods?id=addprocess)を使って：

```php
$serv = new Swoole\Server("0.0.0.0", 9501, SWOOLE_BASE);
$serv->set(
    [
        'worker_num' => 1,
    ]
);

$serv->on('pipeMessage', function ($serv, $src_worker_id, $data) {
    echo "#{$serv->worker_id} message from #$src_worker_id: $data\n";
    sleep(10);//sendMessageからのデータを受信しないで、バッファーはすぐに満たされます
});

$serv->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data) {

});

//状況1：同期IO（デフォルト行動）
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//デフォルトでは、バッファーが満たされた後、ここでブロックされます
    }
}, false);

//状況2：enable_coroutineパラメータを有効にしてUserProcessプロセスのキャプチャサポートを開始し、他のキャプチャがEventLoopをスケジュールできないように、
//SwooleはsendMessageを非同期IOに変換します
$enable_coroutine = true;
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//バッファーが満たされた後、プロセスはブロックされず、エラーが発生します
    }
}, false, 1, $enable_coroutine);

//状況3：UserProcessプロセス内で異步コールバックを設定（例えば、タイマーを設定したり、Swoole\Event::add()などを設定したり）場合、
//他のコールバック関数がEventLoopをスケジュールできないように、SwooleはsendMessageを非同期IOに変換します
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    swoole_timer_tick(2000, function ($interval) use ($worker, $serv) {
        echo "timer\n";
    });
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//バッファーが満たされた後、プロセスはブロックされず、エラーが発生します
    }
}, false);

$serv->addProcess($userProcess);

$serv->start();
```

 - 同様に、[Taskプロセス](/learn?id=taskworkerプロセス)間の通信も`sendMessage()`を通じて行われますが、違いはTaskプロセスがキャプチャサポートを開始するのはServerの[task_enable_coroutine](/server/setting?id=task_enable_coroutine)設定によって開始され、状況3は存在しません。つまり、Taskプロセスが異步コールバックを開始した場合でも、sendMessageは非同期IOにはなりません。
## EventLoopとは何ですか

「EventLoop」とは、イベントループのことで、簡単に言えばepoll_waitで、すべてのイベントが発生するハンドル（fd）をepoll_waitに追加します。これらのイベントには、読み込み可能、書き込み可能、エラーなどが含まれます。

対応するプロセスはepoll_waitという内核関数によってブロックされ、イベント（またはタイムアウト）が発生した後、epoll_waitはブロックを解除して結果を返し、それによって対応するPHP関数を呼び出します。例えば、クライアントからのデータを受信し、「onReceive」のコールバック関数に呼び出します。

大量のfdがepoll_waitに追加され、同時に多くのイベントが発生した場合、epoll_waitが返したときには、対応するコールバック関数が順番に呼ばれます。これを「イベントループ」と呼び、IOマルチポートを実現します。その後、再びepoll_waitによってブロックされ、次のイベントループを行います。
## TCPデータパケットの境界問題

並行性がない場合は、「スピードアップ中のコード」（/start/start_tcp_server）は正常に動作できますが、並行性が高くなるとTCPデータパケットの境界問題が発生します。TCPプロトコルは、UDPプロトコルの順序とパケットの損失リピーターの問題を低レベルで解決していますが、UDPに比べて新しい問題が生じます。TCPプロトコルは流式的であり、データパケットには境界がありません。そのため、アプリケーションがTCPで通信する際にはこれらの難問に直面します。これを俗にTCP粘包問題と呼びます。

TCP通信が流式的であるため、大きなデータパケットを受信するときに、それが複数のデータパケットに分割されて送信される可能性があります。複数回の「Send」操作は、下層で一度に送信されることもあります。これを解決するためには2つの操作が必要です：

* パケット分割：「Server」が複数のデータパケットを受信し、それを分割する必要があります。
* パケット合計：「Server」が受信したデータはパケットの一部に過ぎず、データを緩衝して完全なパケットにまとめる必要があります。

したがって、TCPネットワーク通信では、通信プロトコルを設定する必要があります。一般的なTCPネットワーク通信プロトコルには、「HTTP」、「HTTPS」、「FTP」、「SMTP」、「POP3」、「IMAP」、「SSH」、「Redis」、「Memcache」、「MySQL」があります。

注目すべきは、Swooleが多くの一般的なプロトコルの解析を内置しており、これらのプロトコルのサーバーのTCPデータパケットの境界問題を解決するために、単純な設定で済みます。参考になるのは、「open_http_protocol」（/server/setting?id=open_http_protocol）、「open_http2_protocol」（/http_server?id=open_http2_protocol）、「open_websocket_protocol」（/server/setting?id=open_websocket_protocol）、「open_mqtt_protocol」（/server/setting?id=open_mqtt_protocol）です。

一般的なプロトコルに加えて、独自のプロトコルも可能です。「Swoole」では2種類のタイプの独自のネットワーク通信プロトコルをサポートしています。

* **EOF終了符プロトコル**

「EOF」プロトコルの処理原理は、各データパケットの終わりに特別な文字列を追加してパケットが終了したことを示します。例えば、「Memcache」、「FTP」、「SMTP」はすべて`\r\n`を終了符として使用しています。データを送信する際には、パケットの終わりに`\r\n`を追加するだけで十分です。「EOF」プロトコルを使用する場合は、データパケットの中に「EOF」が発生しないことを確認する必要があります。

「Server」と「Client」のコードでは、2つのパラメータを設定するだけで「EOF」プロトコルを使用することができます。

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

しかし、上記の「EOF」の設定は性能が低くなります。Swooleは各ビットを走査して、データが`\r\n`かどうかを確認し、上記の方法以外にも次のように設定することができます。

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
この設定は性能がずっと良くなります。データを走査することなく、パケットの分割問題だけを解決できますが、パケットの合計問題を解決できません。つまり、Clientから一度のリクエストを受け取った後、Server側がそのリクエストに対する応答データを返すのを待たなければならず、同時に2つのリクエストを送信することはありません。

* **固定ヘッダ+パケット体プロトコル**

固定ヘッダの方法は非常に一般的で、サーバ端末プログラムでよく見られます。このプロトコルの特徴は、データパケットが常にヘッダとパケット体の2部分から構成されていることです。ヘッダには、パケット体または全体のパケットの長さを示すフィールドがあり、長さは通常「2」バイト/「4」バイトの整数で表されます。サーバはヘッダを受信した後、長さの値に基づいて、必要なデータをどれだけ受信するかを正確に制御できます。Swooleの設定はこのプロトコルをよくサポートしており、すべての状況に対応するために柔軟に4つのパラメータを設定することができます。

「Server」は[onReceive](/server/events?id=onreceive)コールバック関数でデータパ
- **sysvmsg**
     
    Linuxによって提供される「メッセージキューシステム」で、この「IPC」方式はファイル名を「キー」として使用してコミュニケーションを行います。この方式は非常に柔軟ではなく、実際のプロジェクトでの使用は多くありません。ここでは詳しく説明しません。

    * **このIPC方式は2つのシナリオで役立ちます:**

        - データの損失を防ぐため、サービス全体が停止した場合でも、キュー内のメッセージは残り、消費を続けることができますが、同時にデータの歪みも発生します。
        - 外部からデータを投げ込むことができます。例えば、Swooleの「Workerプロセス」が「Taskプロセス」にメッセージを投げ、第三方的なプロセスもキューにタスクを投げ、Taskが消費することができます。さらに、コマンドラインから手動でメッセージをキューに追加することもできます。
## Masterプロセス、Reactorタイム、Workerプロセス、Taskプロセス、Managerプロセスの違いと関連性 :id=diff-process
### Masterプロセス

* Masterプロセスはマルチタスクプロセスであり、[プロセス/タスク構造図](/server/init?id=プロセスタスク構造図)を参照してください。
### Reactorタイム

* ReactorタイムはMasterプロセス内で作成されるタイムラインです。
* クライアントの「TCP」接続の維持、ネットワーク「IO」の処理、プロトコルの処理、データの送受信を担当します。
* PHPコードを実行しません。
* 「TCP」クライアントからのデータをバッファリングし、結合し、完整なリクエストパケットに分割します。
### Workerプロセス

* 「Reactor」タイムから投げられたリクエストパケットを受け取り、「PHP」カーボン回调関数でデータを処理します。
* レスポンスデータを生成して「Reactor」タイムに送り、そのタイムが「TCP」クライアントに送信します。
* アダプタメント非ブロッキングモードか、同期ブロッキングモードかのどちらかです。
* 「Worker」はマルチプロセスで運用されます。
### TaskWorkerプロセス

* 「Worker」プロセスがSwoole\Server->[task](/server/methods?id=task)/[taskwait](/server/methods?id=taskwait)/[taskCo](/server/methods?id=taskCo)/[taskWaitMulti](/server/methods?id=taskWaitMulti)メソッドを通じて投げたタスクを受け取り、タスクを処理し、結果データを「Worker」プロセスに返します。（[Swoole\Server->finish](/server/methods?id=finish)を使用）
* 完全に「同期ブロッキング」モードです。
* 「TaskWorker」はマルチプロセスで運用され、「task」の完全な例は[/start/start_task](/start/start_task)です。
### Managerプロセス

* 「worker」/「task」プロセスの作成/回収を担当します。

彼らの関係は、「Reactor」が「nginx」であり、「Worker」が「PHP-FPM」であると理解できます。「Reactor」タイムはネットワークリクエストを非同期的に並行して処理し、その後「Worker」プロセスに転送します。「Reactor」と「Worker」は[unixSocket](/learn?id=何がIPC)を介して通信します。

「PHP-FPM」のアプリケーションでは、しばしばタスクを「Redis」などのキューに非同期的に投げ込み、後台でいくつかの「PHP」プロセスを異步的に実行してこれらのタスクを処理します。「Swoole」が提供する「TaskWorker」は、タスクの投げ込み、キュー、PHPタスク処理プロセスの管理を一体化したより完全なソリューションです。下位レベルのAPIを通じて、非同期的なタスク処理を非常に簡単に実現できます。さらに、「TaskWorker」はタスクが実行された後、結果を「Worker」にフィードバックすることもできます。

「Swoole」の「Reactor」、「Worker」、「TaskWorker」は密接に組み合わさり、より高度な使用方法を提供します。

もっと分かりやすい比喩をすると、「Server」は工場であり、「Reactor」は販売であり、顧客の注文を受けます。そして「Worker」は労働者であり、販売が注文を受けた後、「Worker」は仕事をして顧客が欲しいものを生産します。「TaskWorker」は行政スタッフと考えられることができ、労働者が雑用をして、労働者が専門的な仕事に集中できるように手伝います。

図のように：

![process_demo](_images/server/process_demo.png)
## Serverの3つの運用モードの紹介

「Swoole\Server」の構造函数の第三引数には、3つの常量値を埋めることができます -- [SWOOLE_BASE](/learn?id=swoole_base)、[SWOOLE_PROCESS](/learn?id=swoole_process)および[SWOOLE_THREAD](/learn?id=swoole_thread)。以下では、これら3つのモードの違いと利点についてそれぞれ説明します。
### SWOOLE_PROCESS

SWOOLE_PROCESSモードの「Server」は、すべてのクライアントのTCP接続が[マスタープロセス](/learn?id=reactor线程)と建立されます。内部実装はかなり複雑で、多くのプロセス間通信、プロセス管理メカニズムが使用されます。ビジネスロジックが非常に複雑なシナリオに適しています。「Swoole」は完全なプロセス管理、メモリ保護メカニズムを提供しています。
ビジネスロジックが非常に複雑な場合でも、長期にわたって安定して運用できます。

「Swoole」は[Reactor](/learn?id=reactor线程)线程で「Buffer」機能を提供し、多数の遅い接続や文字一滴ごとの悪意のあるクライアントに対応できます。

#### プロセスモードの利点：

* 接続とデータリクエストの送信は分離しており、特定の接続が多いか少ないかによって「Worker」プロセスが不均衡になることはありません。
* 「Worker」プロセスが致命的なエラーを発生した場合でも、接続は切断されません。
* 単一接続の並行性を実現し、わずかな「TCP」接続だけを維持し、リクエストは複数の「Worker」プロセスで並行して処理できます。

#### プロセスモードの欠点：

* 2回の「IPC」のオーバーヘッドがあり、マスタープロセスと「worker」プロセスは[unixSocket](/learn?id=何がIPC)を使用して通信する必要があります。
* 「SWOOLE_PROCESS」はPHP ZTSをサポートしていません。この場合は、「SWOOLE_BASE」を使用するか、[single_thread](/server/setting?id=single_thread)をtrueに設定するしかありません。
### SWOOLE_BASE

SWOOLE_BASEというモードは、従来の非同期非ブロッキング「Server」です。これは「Nginx」や「Node.js」などのプログラムと完全に一致しています。

[worker_num](/server/setting?id=worker_num)のパラメータは「BASE」モードで依然として有効で、複数の「Worker」プロセスが起動します。

TCP接続要求が入ってくると、すべての「Worker」プロセスがこの接続を争い、最終的に「Worker」プロセスが成功して直接クライアントとTCP接続を確立します。その後、この接続のすべてのデータの送受信は、「Worker」プロセスと直接通信し、マスタープロセスのReactor线程を経由して転送されません。

* 「BASE」モードでは「Master」プロセスの役割はなく、「Manager」プロセスの役割だけがあります。
* 各「Worker」プロセスは、[SWOOLE_PROCESS](/learn?id=swoole_process)モードでの[Reactor](/learn?id=reactor线程)线程と「Worker」プロセスの両方の責任を同時に担っています。
* 「BASE」モードでは「Manager」プロセスはオプションであり、`worker_num=1`が設定され、`Task`や`MaxRequest`の特性を使用していない場合、下層は単独の「Worker」プロセスを直接作成し、「Manager」プロセスを作成しません。

#### BASEモードの利点：

* 「BASE」モードは「IPC」のオーバーヘッドがなく、性能が向上します。
* 「BASE」モードのコードはよりシンプルで、エラーが発生しにくいです。

#### BASEモードの欠点：

* 「TCP」接続は「Worker」プロセス内で維持されており、特定の「Worker」プロセスが停止した場合、その「Worker」内のすべての接続が閉じられます。
* 少数の「TCP」長い接続は、すべての「Worker」プロセスを利用できません。
* 「TCP」接続は「Worker」と結びついており、長い接続アプリケーションでは、接続の大きさによって「Worker」プロセスの負荷が非常に高くなります。しかし、接続の大きさによっては、「Worker」プロセスの負荷が非常に低くなります。異なる「Worker」プロセスでは均等に実現できません。
* 回调関数にブロッキング操作がある場合、Serverは同期モードに退化し、TCPの[backlog](/server/setting?id=backlog)キューが満たされる問題が発生しやすくなります。

#### BASEモードの適用シナリオ：

* クライアント間のインタラクションが不要な場合は、「BASE」モードを使用できます。例えば、「Memcache」、「HTTP」サーバーなどです。

#### BASEモードの制限：

「BASE」モードでは、「Server」の[方法](/server/methods)は[send](/server/methods?id=send)と[close](/server/methods?id=close)を除いて、他のすべての方法は**プロセス間実行**をサポートしていません。

!> v4.5.xバージョンの「BASE」モードでは「send」方法のみがプロセス間実行をサポートしています。v4.6.xバージョンでは「send」と「close」のみがサポートしています。
### SWOOLE_THREAD

SWOOLE_THREADは「Swoole 6.0」によって導入された新しい運用モードで、PHPのztsモードを利用して、多线程モードのサービスを開始することができます。

[worker_num](/server/setting?id=worker_num)のパラメータは「THREAD」モードに依然として有効であり、多プロセスを作成することから多线程を作成することに変わり、複数の「Worker」线程が起動します。

単一のプロセスだけがあり、子プロセスは子线程に変換され、クライアントのリクエストを受け取る責任があります。

#### THREADモードの利点：
* プロセス間のコミュニケーションがよりシンプルで、追加のIPCコミュニケーションのオーバーヘッドがありません。
* プログラムのデバッグがより容易で、単一のプロセスのため、「gdb -p」がよりシンプルになります。
* 協程並行IOプログラミングの利点を持ちながら、多线程並行実行や共有メモリスタックの利点もあります。

#### THREADモードの欠点：
* Crashが発生した場合やProcess::exit()が呼ばれた場合、プロセス全体が終了し、クライアント側でエラーのリトラストや接続再接続などの障害復旧ロジックをしっかりと実装する必要があります。また、supervisorやdocker/k8sを使用してプロセスが終了した後に自動的に再起動する必要があります。
* ZTSとロックの操作には追加のオーバーヘッドがあり、性能がNTS多プロセス並行モデルより約10%劣る可能性があります。無状態サービスの場合は、NTS多プロセス運用方式をお勧めします。
* プロセス間でオブジェクトやリソースを伝達することはサポートしていません。

#### THREADモードの適用シナリオ：
* THREADモードはゲームサーバーや通信サーバーの開発により効率的です。
## Process、Process\Pool、UserProcessの違いは何ですか :id=process-diff
### Process

[Process](/process/process)は Swooleが提供するプロセス管理モジュールで、PHPの`pcntl`を置き換えるものです。

* プロセス間のコミュニケーションを容易に実現できます；
* 標準入力と出力をリダイレートでき、子プロセス内で`echo`は画面に出力されず、パイプに書き込まれ、キーボード入力はパイプから読み取りデータにリダイレートできます；
* `exec`インターフェースを提供し、作成されたプロセスは他のプログラムを実行でき、元のPHP親プロセスと容易にコミュニケーションができます；

!> 協程環境では`Process`モジュールを使用することはできませんが、`runtime hook`+`proc_open`を使用して実現できます。参考になるのは[協程プロセス管理](/coroutine/proc_open)です。
### Process\Pool

[Process\Pool](/process/process_pool)は、Serverのプロセス管理モジュールをPHPクラスとして封じ込め、PHPコード内でSwooleのプロセス管理器を使用することをサポートしています。

実際のプロジェクトでは、より長期間運用するスクリプトを書く必要があります。例えば、「Redis」、「Kafka」、「RabbitMQ」を基にしたマルチプロセスキューシステムの消費者、マルチプロセスのスパイ
