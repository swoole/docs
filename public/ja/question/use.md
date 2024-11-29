# 利用について
## Swooleの性能はどのようになっていますか？

> QPSの比較

Apache-Benchツール(ab)を使用して、Nginxの静的なページ、GolangのHTTPプログラム、PHP7+SwooleのHTTPプログラムに対してプレッシャーテストを行いました。同じマシンで、100并发で合計100万件のHTTPリクエストのベンチマークテストを行い、QPSの比較は以下の通りです：

|ソフトウェア|QPS|ソフトウェアバージョン|
|---|---|---|
|Nginx|164489.92|nginx/1.4.6 (Ubuntu)|
|Golang|166838.68|go version go1.5.2 linux/amd64|
|PHP7+Swoole|287104.12|Swoole-1.7.22-alpha|
|Nginx-1.9.9|245058.70|nginx/1.9.9|

!> 注：Nginx-1.9.9のテストでは、access_logをオフにし、open_file_cacheを有効にして静的なファイルをメモリにキャッシュしました

> テスト環境

* CPU：Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* メモリ：16G
* ディスク：128G SSD
* OS：Ubuntu14.04 (Linux 3.16.0-55-generic)

> テスト方法

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

Nginxは4つのWorkerプロセスを起動しています
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

PHP7では`OPcache` acelerーターを有効にしています。

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

最新のスコアテスト結果の場所：[techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swooleは**動的言語第1位**をリードしています

データベースIO操作のテストでは、基本的なビジネスコードを使用しており、特別な最適化はありません。

**MySQLではなくPostgreSQLを使用しているすべての静的な言語フレームワークよりもパフォーマンスが優れています**
## SwooleはTCP長接続をどのように維持するのか

TCP長接続の維持については、2つの設定[tcp_keepalive](/server/setting?id=open_tcp_keepalive)と[heartbeat](/server/setting?id=heartbeat_check_interval)があります。
## Swooleはどのようにしてサービスを正しく再起動するか

日常開発において、PHPコードを変更した後にはしばしばサービスを再起動してコードを有効にする必要があります。忙しいバックエンドサーバーは常にリクエストを処理しており、管理者がプロセスを`kill`してサービスを終了させたり再起動させたりすると、ちょうどコードが半分実行されているときに終了し、ビジネスロジックの完全性を保証することはできません。

Swooleは柔軟な終了/再起動のメカニズムを提供しており、管理者はServerに特定のシグナルを送信したり、`reload`メソッドを呼び出すだけで、ワークプロセスを終了させ、再起動することができます。具体的な方法は[reload()](/server/methods?id=reload)を参照してください。

ただし、いくつかの点に注意が必要です：

まず、新しく変更されたコードは`OnWorkerStart`イベントで再読み込みされる必要があり、例えばComposerのautoloadによって`OnWorkerStart`の前にロードされたクラスはできません。

次に、`reload`は[max_wait_time](/server/setting?id=max_wait_time)と[reload_async](/server/setting?id=reload_async)の2つのパラメータと組み合わせて使用する必要があります。これらを設定した後でなければ、`非同期安全再起動`を実現することはできません。

この特性がなければ、ワークプロセスが再起動信号を受け取ったり、`max_request](/server/setting?id=max_request)に達したりすると、すぐにサービスを停止し、その時点でワークプロセス内にはまだイベントが监听している可能性があり、これらの非同期タスクは丢弃されるでしょう。上記のパラメータを設定した後、まず新しいワークプロセスが作成され、古いワークプロセスがすべてのイベントを処理した後に自ら退出します。つまり`reload_async`です。

古いワークプロセスがなかなか退出しない場合、基層にはタイマーが追加されており、約定された時間(`max_wait_time](/server/setting?id=max_wait_time)秒)内に古いワークプロセスが退出しない場合、基層は強制的に終了し、[WARNING](/question/use?id=forced-to-terminate)エラーが発生します。

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

例えば上記のコードでは、reload_asyncがなければ、onReceiveの中で作成されたタイマーは丢失し、タイマーの回调関数を処理する機会がありません。
### プロセス退出イベント

非同期再起動特性をサポートするために、基層には[onWorkerExit](/server/events?id=onWorkerExit)イベントが追加されました。古い`Worker`が退出する直前に、`onWorkerExit`イベントがトリガーされ、このイベントの回调関数では、アプリケーション層は長连接の`Socket`をクリーンアップしようと試みることができます。イベントループにfdがないか、または[max_wait_time](/server/setting?id=max_wait_time)に達してプロセスが退出するまで続けられます。

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

同時に [Swoole Plus](https://www.swoole.com/swoole_plus)では、ファイルの変更を検知する機能が追加され、手動でreloadしたりシグナルを送信したりすることなく、ファイルの変更に応じてworkerを自動的に再起動することができます。
## なぜsend完了後にすぐにcloseすることは安全ではないのか

send完了後にすぐにcloseすることは、サーバー側でもクライアント側でも安全ではありません。

send操作が成功しただけでは、データがオペレーティングシステムのsocketバッファに成功して書き込まれたことを意味するだけで、相手方が実際にデータを受け取ったかどうかは保証できません。オペレーティングシステムが実際に送信に成功したかどうか、相手方のサーバーが受け取ったかどうか、サーバー側のプログラムが処理したかどうかは、確かに保証できません。

> close後のロジックについては、以下のlinger設定に関するものを参照してください。

このロジックは電話でのコミュニケーションと同じです。AがBに何かを伝えた後、電話を切ります。ではBが聞いたかどうか、Aは知りません。Aが何かを伝えた後、Bが「いいよ」と言って電話を切ったら、それは確実に安全です。

linger設定

socketをcloseするとき、バッファにはまだデータがある場合、オペレーティングシステムの基層はlinger設定に基づいてどのように処理するか来决定します。

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0の場合、close時にすぐに戻ります。基層は未送信のデータを送信し終えた後、リソースを解放します。つまり、優雅に退出します。
* l_onoff != 0 且 l_linger = 0の場合、close時にすぐに戻りますが、未送信のデータを送信しません。代わりにRSTパケットを送り、socket記述子を強制的に閉じます。つまり、強制的に退出します。
* l_onoff !=0 且 l_linger > 0の場合、close時にすぐに戻りません。カーネルは一定の時間（l_lingerの値によって決定される）を遅らせます。この時間内に未送信のデータ（FINパケットを含む）を送信し、相手方の確認を得ることができれば、closeは正しく戻り、socket記述子は優雅に退出します。そうでなければ、closeは直接エラー値を返し、未送信のデータが丢失し、socket記述子は強制的に退出します。socket記述子が非ブロッキング型に設定されている場合、closeは直接値を返します。
## client has already been bound to another coroutine

TCP接続に対してSwoole基層は、同時に一つの协程が読み取り操作を行い、一つの协程が書き込み操作を行うことを許可しています。つまり、一つのTCPに対して複数の协程が読み取り/書き込み操作を行うことはできません。基層は bağエラーを抛出します:

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

解決策の参考：[link](https://wenda.swoole.com/detail/107474)

!> この制限はすべての多协程環境に適用され、最も一般的なのは[onReceive](/server/events?id=onreceive)などの回调関数で一つのTCP接続を共有することです。そのため、このような回调関数は自動的に协程を作成します。
では、コネクションプoolのニーズがある場合はどうでしょうか？Swooleには[コネクションプool](/coroutine/conn_pool)が内蔵されており、直接使用することができますし、手動でchannelを使用してコネクションプoolを封装することもできます。
## Call to undefined function Co\run()

このドキュメントのほとんどの例では、`Co\run()`を使用して协程コンテナを作成しています。[协程コンテナとは何か](/coroutine?id=什么是协程容器)を理解してください。

もし以下のエラーが発生した場合：

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

これはSwoole拡張のバージョンがv4.4.0未満であるか、协程短名(/other/alias?id=协程短名称)を手動でオフにしたことを意味します。以下の解決策を提供します：

* バージョンが低い場合は、拡張のバージョンをv4.4.0以上にアップグレードするか、`go`キーワードを使用して`Co\run`を置き換えて协程を作成してください；
* 协程短名をオフにした場合は、[协程短名称](/other/alias?id=协程短名称)をオンにしてください；
* `Co\run`または`go`を使用して协程を作成するために[Coroutine::create](/coroutine/coroutine?id=create)メソッドを使用してください；
* 全名を使用：`Swoole\Coroutine\run`；
## RedisやMySQLの接続を一つに共有することはできるのか

絶対にできません。各プロセスごとにRedis、MySQL、PDOの接続を個別に作成しなければなりません。他のストレージクライアントも同様です。その理由は、一つの接続を共有すると、どのプロセスが結果を処理するか保証できず、接続を保持しているプロセスは理論上その接続に対して読み取り/書き込みを行うことができ、そうするとデータが間違ってしまいます。

**したがって、複数のプロセス間で接続を共有することは絶対にできません**

* [Swoole\Server](/server/init)では、[onWorkerStart](/server/events?id=onworkerstart)の回调関数の中でRedis/MySQLの接続オブジェクトを作成する必要があります；
* [Swoole\Process](/process/process)では、[Swoole\Process->start](/process/process?id=start)の後に、子プロセスの回调関数の中で接続オブジェクトを作成する必要があります；
* 上記の情報は、`pcntl_fork`を使用するプログラムにも適用されます。

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

以下の通知が示される場合

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

これは、サーバーが応答する際に、クライアント
## connected属性と接続状態が一致しない

4.xコーンループバージョン以降、`connected`属性はリアルタイムで更新されなくなり、[isConnect](/client?id=isconnected)方法は信頼できなくなりました。
### 原因

コーンループの目標は、同期ブロッキングのプログラミングモデルと一致することです。同期ブロッキングモデルでは、リアルタイムで接続状態を更新する概念は存在しません。例えばPDOやcurlなどは、接続の概念がなく、IO操作時にエラーを返すか例外を投げた後に接続が切断されていることがわかります。

Swooleの基本的な方法は、IOエラー時にfalse（または空内容で接続が切断されたことを示す）を返し、クライアントオブジェクトに適切なエラーコードとエラー情報を設定することです。
### 注意

以前のアンチ阿拉伯版本では、「リアルタイム」で`connected`属性を更新することができたものの、実際には信頼できませんでした。接続はあなたがチェックした後すぐに切断される可能性があります。
## Connection refusedとは何か

telnet 127.0.0.1 9501時にConnection refusedが発生するのは、サーバーがこのポートを监听后いないことを意味します。

* プログラムが正常に実行されているかを確認する: ps aux
* ポートが监听しているかを確認する: netstat -lp
* ネットワーク通信プロセスが正常かどうかを確認する: tcpdump traceroute
## Resource temporarily unavailable [11]

クライアントのswoole_clientが`recv`時に次のようなエラーが発生します。

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```

このエラーは、サーバー側が指定された時間内にデータを送信せず、受信タイムアウトが発生したことを意味します。

* tcpdumpを使用してネットワーク通信プロセスを確認し、サーバーがデータを送信しているかを検証します
* サーバーの`$serv->send`関数は、trueが返されたかどうかをチェックする必要があります
* 外部ネットワーク通信時には、時間がかかり较长いため、swoole_clientのタイムアウト時間を長くする必要があります
## worker exit timeout, forced to terminate :id=forced-to-terminate

次のようなエラーが発生しました：

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```
これは、約定期間（[max_wait_time](/server/setting?id=max_wait_time)秒）内にこのWorkerが退出しなかったため、Swooleが下層で強制的にこのプロセスを終了させました。

次のようなコードで再現できます：

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
## Signal Broken pipe: 13の回调関数が見つからない

次のような警告が発生しました：

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```
これは、切断された接続にデータを送信したことを意味し、通常は送信の戻り値をチェックせず、送信に失敗しても続けると発生します。
## Swooleを学ぶために必要な基礎知識
### 多プロセス/多スレッド

* Linuxオペレーティングシステムのプロセスとスレッドの概念を理解する
* Linuxプロセス/スレッドの切り替えスケジュールを理解する
* プロセス間通信の基本を理解する。例えばパイプ、UnixSocket、メッセージキュー、共有メモリなど
### SOCKET

* SOCKETの基本操作であるaccept/connect、send/recv、close、listen、bindを理解する
* SOCKETの受信バッファ、送信バッファ、ブロッキング/非ブロッキング、タイムアウトなどの概念を理解する
### IOマルチプレクサ

* select/poll/epollを理解する
* select/epollに基づくイベントループ、Reactorモデルを理解する
* 可読イベント、可書式イベントを理解する
### TCP/IPネットワークプロトコル

* TCP/IPプロトコルを理解する
* TCP、UDP伝送プロトコルを理解する
### デバッグツール

* [gdb](/other/tools?id=gdb)を使用してLinuxプログラムをデバッグする
* [strace](/other/tools?id=strace)を使用してプロセスのシステム呼び出しをトレースする
* [tcpdump](/other/tools?id=tcpdump)を使用してネットワーク通信プロセスをトレースする
* その他のLinuxシステムツール、例えばps、[lsof](/other/tools?id=lsof)、top、vmstat、netstat、sar、ssなど
## Swoole\Curl\Handlerクラスのインスタンスをintに変換できない

[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を使用しているときに、次のようなエラーが発生しました：

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```
その理由は、hook後のcurlはもはやリソースタイプではなく、オブジェクトタイプであるため、intタイプに変換できないからです。

!> `int`の問題はSDK側でコードを変更することをお勧めします。PHP8ではcurlはもはやリソースタイプではなく、オブジェクトタイプです。

解決策は3つあります：

1. [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を有効にしない。ただし、v4.5.4バージョンからは[SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all)がデフォルトで[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を含んでいるため、SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURLを設定することで[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を無効にすることができます。

2. GuzzleのSDKを使用し、Handlerを置き換えて協程化を実現します。

3. Swoole v4.6.0バージョンからは[SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)を使用して[SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl)を置き換えることができます。
## 一键协程化とGuzzle 7.0+を同時に使用する場合、リクエストを发起した後に結果を直接ターミナルに出力 :id=hook_guzzle

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
## Error: No buffer space available[55]

このエラーは無視できます。このエラーは [socket_buffer_size](/server/setting?id=socket_buffer_size) 設定が大きすぎて、一部のシステムでは受け付けられないため、プログラムの実行に影響を与えません。
## GET/POSTリクエストの最大サイズ
### GETリクエストの最大サイズは8192

GETリクエストにはHttpヘッダしかなく、Swooleの基本的な使用は固定サイズのメモリキャッシュエリア8Kを使用しており、変更することはできません。もしリクエストが正しいHttpリクエストでなければ、エラーが発生します。基本的な使用は以下のエラーを投げます：

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```
### POSTファイルアップロード

最大サイズは [package_max_length](/server/setting?id=package_max_length) 設定項によって制限されており、デフォルトは2Mです。新しい値を [Server->set](/server/methods?id=set) を通じて変更することができます。Swooleの基本的な使用は全メモリを使用しているため、大きすぎると多くの同時リクエストがサーバーのリソースを消耗させることがあります。

計算方法：`最大メモリ使用量` = `最大同時リクエスト数` * `package_max_length`
