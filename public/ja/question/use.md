# 利用について

## Swooleの性能はどのようになっていますか？

> QPSの比較

Apache-Benchツール(ab)を使用して、Nginxの静的なページ、GolangのHTTPプログラム、PHP7+SwooleのHTTPプログラムに対して圧力テストを行いました。同じマシンで、100并发で合計100万件のHTTPリクエストのベンチマークテストを行い、QPSの比較は以下の通りです：

|ソフトウェア|QPS|ソフトウェアバージョン|
|---|---|---|
|Nginx|164489.92|nginx/1.4.6 (Ubuntu)|
|Golang|166838.68|go version go1.5.2 linux/amd64|
|PHP7+Swoole|287104.12|Swoole-1.7.22-alpha|
|Nginx-1.9.9|245058.70|nginx/1.9.9|

!> 注：Nginx-1.9.9のテストでは、access_logをオフにし、open_file_cacheを有効にして静的なファイルをメモリにキャッシュしました。

> テスト環境

* CPU：Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* メモリ：16G
* ディスク：128G SSD
* OS：Ubuntu14.04 (Linux 3.16.0-55-generic)

>圧力テスト方法

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> VHOST設定

```nginx
server {
    listen 80 default_server;
    root /data/webroot;
    index index.html;
}
```

> テストページ

```html
<h1>Hello World!</h1>
```

> プロセス数

Nginxは4つのWorkerプロセスを開始しました
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12月07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12月07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12月07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12月07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12月07   0:45 nginx: worker process
```

> Golang

テストコード

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

PHP7では`OPcache`acceleratorが有効になっています。

テストコード

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **世界のWebフレームワークの権威あるパフォーマンステスト Techempower Web Framework Benchmarks**

最新のスコアテスト結果の場所: [techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swooleは**動的言語第1位**をリードしています

データベースIO操作のテストでは、基本的なビジネスコードを使用しており、特別な最適化はありません。

**MySQLを使用したすべての静的な言語フレームワークよりも性能が優れています**(PostgreSQLではなくMySQLを使用)


## SwooleはTCP長接続をどのように維持するのか？

TCP長接続の維持については、2つの設定[tcp_keepalive](/server/setting?id=open_tcp_keepalive)と[heartbeat](/server/setting?id=heartbeat_check_interval)があります。


## Swooleはどのようにしてサービスを正しく再起動するか？

日常開発では、PHPコードを変更した後、しばしばサービスを再起動してコードを有効にする必要があります。忙しいバックエンドサーバーは常にリクエストを処理しており、管理者が`kill`プロセスとしてサービスを終了させようとすると、ちょうどコードが半分実行されているときに終了し、ビジネスロジックの完全性を保証することはできません。

Swooleは柔軟な終了/再起動のメカニズムを提供しており、管理者はServerに特定のシグナルを送信したり、`reload`メソッドを呼び出すだけで、ワークプロセスを終了させ、再起動することができます。具体的なことは[reload()](/server/methods?id=reload)を参照してください。

しかし、いくつかの点に注意が必要です：

まず、新しく変更されたコードは`OnWorkerStart`イベントで再読み込みされる必要があり、例えば、あるクラスが`OnWorkerStart`の前にComposerのautoloadによって読み込まれていると、それはできません。

次に、`reload`は[max_wait_time](/server/setting?id=max_wait_time)と[reload_async](/server/setting?id=reload_async)の2つのパラメータと組み合わせて使用する必要があります。これらを設定した後、`非同期安全再起動`を実現できます。

この特性がなければ、Workerプロセスが再起動信号を受け取ったり、`max_request](/server/setting?id=max_request)に達したりすると、すぐにサービスを停止し、その時点でWorkerプロセス内にはまだイベント监听器が存在する可能性があります。これらの非同期タスクは丢弃されるでしょう。上記のパラメータを設定した後、まず新しいWorkerが作成され、古いWorkerはすべてのイベントを完了した後に自ら退出します。つまり`reload_async`です。

古いWorkerがなかなか退出しない場合、下層にはタイマーが追加されており、約定された時間(`max_wait_time](/server/setting?id=max_wait_time)秒)内に古いWorkerが退出しない場合、下層は強制的にそれを終了し、[WARNING](/question/use?id=forced-to-terminate)エラーが発生します。

例：

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tClient[$fd] receive data: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

上記のコードでは、reload_asyncがなければ、onReceiveで作成されたタイマーは丢失し、タイマーの回调関数を処理する機会がありません。


### プロセス退出イベント

非同期再起動特性をサポートするために、下層には[onWorkerExit](/server/events?id=onWorkerExit)イベントが追加されました。古いWorkerが退出する直前には、`onWorkerExit`イベントがトリガーされ、このイベントの回调関数では、アプリケーション層はいくつかの長连接`Socket`をクリーンアップしようと試みることができます。これは、[イベントループ](/learn?id=什么是eventloop)にfdがないか、または[max_wait_time](/server/setting?id=max_wait_time)に達してプロセスを退出するまで続きます。

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

同時に [Swoole Plus](https://www.swoole.com/swoole_plus)では、ファイル変更の検出機能が追加されており、手動でreloadしたりシグナルを送信する必要はありません。ファイルの変更は自動的にworkerを再起動します。
##なぜsendを終えた後にすぐにcloseすることは安全ではないのか

sendを終えた後にすぐにcloseすることは、サーバー側でもクライアント側でも安全ではありません。

send操作が成功しただけでは、データがオペレーティングシステムのsocketバッファに成功して書き込まれたことを意味するだけで、相手側が実際にデータを受け取ったわけではありません。オペレーティングシステムが実際に送信に成功したかどうか、相手方のサーバーが受け取ったかどうか、サーバー側のプログラムが処理したかどうかは、確かに保証することはできません。

> close後のロジックについては、以下のlinger設定に関する内容をご覧ください。

このロジックは電話でのコミュニケーションと同じです。AがBに何かを伝えた後、Aが電話を切ります。ではBがそれを聞いたかどうか、Aは知りません。Aが物事を話し終えてBが「いいよ」と言って電話を切った場合、それは確実に安全です。

linger設定

socketをcloseするとき、バッファにはまだデータがある場合、オペレーティングシステムの基層はlinger設定に基づいてどのように処理するか決定します。

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0の場合、close時にすぐに戻ります。基層は未送信のデータを送信し終えた後にリソースを解放し、つまり礼儀正しく退出します。
* l_onoff != 0、l_linger = 0の場合、close時にすぐに戻りますが、未送信のデータを送信せず、RSTパケットを通じてsocket記述子を強制的に閉じます。つまり強制的に退出します。
* l_onoff !=0、l_linger > 0の場合、close時にすぐには戻らず、カーネルは一定の時間を遅らせます。この時間はl_lingerの値によって決定されます。タイムアウト時間が到达する前に、未送信のデータ（FINパケットを含む）を送信し、もう一方の端からの確認を得ることができれば、closeは正しい戻りを返し、socket記述子は礼儀正しく退出します。そうでなければcloseは直接エラー値を返し、未送信のデータが丢失し、socket記述子は強制的に退出します。socket記述子が非ブロッキングに設定されている場合、closeは直接値を返します。

## clientはすでに別のcoにbindされている

TCP接続にとって、Swooleの基層は同時に一つのcoが読み取り操作を行い、一つのcoが書き込み操作を行うことを許可しています。つまり、一つのTCPに対して複数のcoが読み取り/書き込み操作を行うことはできません。基層はbindエラーを投げます：

```shell
Fatal error: Uncaught Swoole\Error: Socket#6 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed 
```

更新後のコード：

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

解決策の参考：https://wenda.swoole.com/detail/107474

!> この制限はすべての多co環境に適用されます。最も一般的なのは、[onReceive](/server/events?id=onreceive)などの回调関数で一つのTCP接続を共有することです。なぜなら、このような回调関数は自動的にcoを作成するからです。
では、接続プールが必要な場合はどうでしょうか？Swooleには[接続プール](/coroutine/conn_pool)が組み込まれており、直接使用することができますし、手動でchannelを使用して接続プールを封装することもできます。

## 未定義の関数Co\run()への呼び出し

このドキュメントのほとんどの例では、`Co\run()`を使用してcoコンテナを作成しています。[coコンテナとは何か](/coroutine?id=什么是协程容器)を知っておいてください。

以下のエラーが発生した場合：

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

これは、Swoole拡張のバージョンがv4.4.0未満であるか、coの短名前缀を手動でオフにしたことを意味します。以下の解決策を提供します：

* バージョンが古い場合は、拡張のバージョンをv4.4.0以上にアップグレードするか、`go`キーワードを使用して`Co\run`を置き換えてcoを作成してください；
* coの短名前缀をオフにした場合は、[coの短名前缀](/other/alias?id=协程短名称)をオンにしてください；
* `Co\run`または`go`を使用してcoを作成するために[Coroutine::create](/coroutine/coroutine?id=create)方法を置き換えてください；
* フルネームを使用します：`Swoole\Coroutine\run`；

## RedisまたはMySQLの接続を共有することはできるのか

絶対にできません。各プロセスで個別にRedis、MySQL、PDOの接続を作成しなければなりません。その他のストレージクライアントも同様です。その理由は、一つの接続を共有すると、どのプロセスが結果を処理するか保証できず、接続を保持しているプロセスは理論上この接続に対して読み書きを行うことができ、そうするとデータが混乱します。

**したがって、複数のプロセス間で接続を共有することは絶対にできません**

* [Swoole\Server](/server/init)では、[onWorkerStart](/server/events?id=onworkerstart)の回调の中で接続オブジェクトを作成する必要があります。
* [Swoole\Process](/process/process)では、[Swoole\Process->start](/process/process?id=start)の後、子プロセスの回调関数の中で接続オブジェクトを作成する必要があります。
* 上記の問題に関する情報は、`pcntl_fork`を使用するプログラムにも同様に適用されます。

例：

```php
$server = new Swoole\Server('0.0.0.0', 9502);

//onWorkerStart回调の中でredis/mysql接続を作成する必要があります
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```

## 接続がすでに閉じられている問題

以下の通知が示すように

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

サーバーが応答するとき、クライアントはすでに接続を切断しているために発生します。
一般的なケースには、

* ブラウザがページを狂ったようにリフレッシュ（まだ読み込んでいないのにリフレッシュした）
* abプレッシャーテストを途中でキャンセル
* wrkベースの時間によるプレッシャーテスト（時間が経過しても完了していないリクエストはキャンセルされる）

これらの状況はすべて正常な現象であり、無視できます。したがって、このエラーのレベルはNOTICEです。
他の理由で無作為に多くの接続が断線する場合にのみ注意が必要です。

```bash
WARNING swWorker_discard_data (ERRNO 1007): [2] received the wrong data[21 bytes] from socket#75

WARNING Worker_discard_data (ERRNO 1007): [2] ignore data[5 bytes] received from session#2
```
同様に、このエラーも接続がすでに閉じられていることを示しており、受信されたデータは破棄されます。[discard_timeout_request](/server/setting?id=discard_timeout_request)を参照してください。

## connected属性と接続状態が一致しない

4.x coバージョン以降、`connected`属性はリアルタイムで更新されなくなり、[isConnect](/client?id=isconnected)方法は信頼できなくなりました。

### 原因

coの目標は、同期ブロッキングのプログラミングモデルと一致することです。同期ブロッキングモデルでは、リアルタイムで接続状態を更新する概念はありません。PDOやcurlなどは、接続という概念がなく、IO操作時にエラーを返すか例外を投げた後に接続が断たれることがわかります。

Swooleの基層では一般的な方法は、IOエラーが発生したときにfalse（または空内容で接続が断たれていることを示す）を返し、クライアントオブジェクトに適切なエラーコードとエラー情報を設定することです。
### 注意

以前の非同期バージョンでは「リアルタイム」で`connected`プロパティを更新することができたものの、実際には信頼できないものでした。接続はあなたが確認した後すぐに切断される可能性があります。

## 接続が拒否されるのはなぜでしょうか

telnet 127.0.0.1 9501 を試みると Connection refused になる場合、これはサーバーがこのポートをlistenしていないことを意味しています。

* プログラムが正常に実行されているかどうかを確認する: ps aux
* ポートがlistenしているかどうかを確認する: netstat -lp
* ネットワーク通信のプロセスが正常に機能しているかどうかを確認する: tcpdump traceroute

##リソースが一時的に利用できません [11]

クライアントのswoole_clientが`recv`時に以下のようなエラーが発生します。

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```
このエラーは、サーバー側が指定された時間内にデータを送信せず、受信タイムアウトしたことを意味しています。

* tcpdumpを使用してネットワーク通信のプロセスを確認し、サーバーがデータを送信しているかどうかを確認することができます。
* サーバーの`$serv->send`関数は、trueが返されたかどうかをチェックする必要があります。
* 外部ネットワーク通信では、時間がかかり较长いため、swoole_clientのタイムアウト時間を大きくする必要があります。

## worker exit timeout, forced to terminate :id=forced-to-terminate

以下ののようなエラーが発生しました：

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```
これは、約定された時間 ([max_wait_time](/server/setting?id=max_wait_time)秒) 内にこの Workerが退出しなかったため、Swooleの基層が強制的にこのプロセスを終了させました。

以下のコードで再現することができます：

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```

##シグナル Broken pipe: 13に対する回调関数が見つかりません

以下のようなエラーが発生しました：

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```
これは、切断された接続にデータを送信したことを意味し、通常は送信の戻り値を判断せず、送信に失敗しても続けて送信しているためです。

##Swooleを学ぶためにどのような基礎知識が必要か

### 多プロセス/多スレッド

* Linuxオペレーティングシステムのプロセスとスレッドの概念を理解する
* Linuxプロセス/スレッドの切り替えスケジュールに関する基本的な知識を理解する
* プロセス間の通信に関する基本的な知識を理解する、例えばパイプ、UnixSocket、メッセージキュー、共有メモリなど

### SOCKET

* SOCKETの基本的な操作であるaccept/connect、send/recv、close、listen、bindを理解する
* SOCKETの受信バッファ、送信バッファ、ブロック/非ブロック、タイムアウトなどの概念を理解する

### IO多重化

* select/poll/epollを理解する
* select/epollに基づいて実現されたイベントループ、Reactorモデルを理解する
* 可読イベント、可書式イベントを理解する

### TCP/IPネットワークプロトコル

* TCP/IPプロトコルを理解する
* TCP、UDP伝送プロトコルを理解する

### デバッグツール

* [gdb](/other/tools?id=gdb)を使用してLinuxプログラムをデバッグする
* [strace](/other/tools?id=strace)を使用してプロセスのシステム呼び出しを追跡する
* [tcpdump](/other/tools?id=tcpdump)を使用してネットワーク通信プロセスを追跡する
* その他のLinuxシステムツール、例えばps、[lsof](/other/tools?id=lsof)、top、vmstat、netstat、sar、ssなど

## Swoole\Curl\Handlerクラスのインスタンスをintに変換できない

[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を使用しているときに、以下のエラーが発生しました：

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```
その理由は、hook後のcurlはもはやresourceタイプではなく、objectタイプであるため、intタイプに変換できないからです。

!> `int`の問題はSDK側でコードを変更することをお勧めします。PHP8ではcurlはもはやresourceタイプではなく、objectタイプです。

解決策は3つあります：

1. [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を有効にしない。ただし、v4.5.4バージョンからは[SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all)がデフォルトで[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を含んでいるため、SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURLを設定することで[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を無効にすることができます。

2. GuzzleのSDKを使用し、Handlerを置き換えることで協程化を実現できます。

3. Swoole v4.6.0以降では[SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)を使用して[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を代替することができます。

## 一键协程化とGuzzle 7.0+を同時に使用した場合、リクエストを发起した後に結果を直接ターミナルに出力 :id=hook_guzzle

再現コードは以下の通りです。

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// v4.5.4以前のバージョン
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// リクエスト結果は直接出力され、打印されることはありません
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> 解決策は前の問題と同じです。ただし、この問題はSwooleバージョン >= `v4.5.8`で修正されました。

##エラー：バッファスペースがありません[55]

このエラーは無視できます。このエラーは [socket_buffer_size](/server/setting?id=socket_buffer_size) 設定が大きすぎるためであり、一部のシステムでは受け入れられません。しかし、プログラムの実行には影響しません。

##GET/POSTリクエストの最大サイズ

###GETリクエストの最大8192

GETリクエストにはHttpヘッダしかありません。Swooleの基層は固定サイズのメモリバッファ8Kを使用しており、変更することはできません。もしリクエストが正しいHttpリクエストでなければ、エラーが発生します。基層は以下のエラーを投げます：

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```
###POSTファイルアップロード

最大サイズは [package_max_length](/server/setting?id=package_max_length) 設定項によって制限されており、デフォルトは2Mです。新しい値を [Server->set](/server/methods?id=set) を通じて変更することができます。Swooleの基層は全メモリを使用しているため、大きすぎると多くの同時リクエストがサーバーのリソースを消耗させることがあります。

計算方法：`最大メモリ使用量` = `最大同時リクエスト数` * `package_max_length`
