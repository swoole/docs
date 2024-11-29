```
# コロニアル WaitGroup

Swoole4では、[チャネル](/coroutine/channel)を使用してコーラル間の通信、依存管理、コーラルの同期を実現できます。チャネルに基づいて、Goの`sync.WaitGroup`機能を簡単に実現できます。

## 実現コード

> この機能はPHPで書かれたものであり、C/C++コードではありません。実現源コードは [ライブラリ](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php)にあります。

* `add`メソッドはカウンターを増やします。
* `done`はタスクが完了したことを示します。
* `wait`はすべてのタスクが完了するのを待って、現在のコーラルの実行を再開します。
* `WaitGroup`オブジェクトは再利用でき、`add`、`done`、`wait`の後でも再び使用できます。

## 使用例

```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $wg = new WaitGroup();
    $result = [];

    $wg->add();
    //最初のコーラルを開始
    Coroutine::create(function () use ($wg, &$result) {
        //コーラルクライアントclientを起動し、タオバオのホームページにリクエストを送ります。
        $cli = new Client('www.taobao.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.taobao.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['taobao'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    $wg->add();
    //2番目のコーラルを開始
    Coroutine::create(function () use ($wg, &$result) {
        //コーラルクライアントclientを起動し、百度のホームページにリクエストを送ります。
        $cli = new Client('www.baidu.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.baidu.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['baidu'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    //現在のコーラルを保留し、すべてのタスクが完了するのを待って再開します。
    $wg->wait();
    //ここで $resultには2つのタスクの実行結果が含まれています。
    var_dump($result);
});
```
