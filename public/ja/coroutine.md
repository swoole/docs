# 协程 <!-- {docsify-ignore-all} -->

このセクションでは、いくつかの協程の基本的な概念と一般的な問題について説明します。

4.0バージョンからは、`Swoole`は完全な`協程（Coroutine）`+`チャネル（Channel）`特性を提供し、新しい`CSP`プログラミングモデルを導入しました。

1.開発者は同期的なコード編集方式で[非同期IO](/learn?id=同期io非同期io)の効果とパフォーマンスを実現することができ、従来の非同期カスタムレシーブによる離散したコードロジックや複数のレシーブによってコードが維持できない状況を避けることができます。
2.同時に、下層が協程を封じ込めているため、従来の`PHP`レシーブフレームワークと比較して、開発者は`yield`キーワードを使用して協程の`IO`操作を識別する必要がなくなり、`yield`の意味を深く理解する必要もなく、各レベルで`yield`を使用する必要もなくなり、これにより開発効率が大幅に向上します。
3.様々なタイプの完全な[協程クライアント](/coroutine_client/init)を提供し、ほとんどの開発者のニーズを満たすことができます。
## 協程とは何か

協程は単純にスレッドと理解できますが、このスレッドはユーザーレベルであり、オペレーティングシステムの参加不要で、作成、破棄、切り替えのコストが非常に低いです。スレッドと異なり、協程はマルチコアCPUを利用することができませんが、マルチコアCPUを利用したい場合は`Swoole`のマルチプロセスモデルに依存する必要があります。
## Channelとは何か

`Channel`はメッセージキューと理解できますが、それは協程間のメッセージキューであり、複数の協程が`push`と`pop`操作を通じてキュー内の生産メッセージと消費メッセージを行い、協程間のデータ送信や受信に使用されます。注意すべき点は`Channel`はプロセスを越えることができず、`Swoole`プロセス内の協程間のみ通信が可能であり、最も典型的な応用は[接続プール](/coroutine/conn_pool)と[並行呼び出し](/coroutine/multi_call)です。
## 協程コンテナとは何か

`Coroutine::create`または`go()`メソッドを使用して協程を作成する（[別名のセクション](/other/alias?id=協程の短い名前)を参照），作成された協程の中でのみ協程APIを使用できます。そして、協程は必ず協程コンテナの中に作成されなければなりません（[協程コンテナ](/coroutine/scheduler)を参照）。
## 協程スケジューリング

ここでは、できるだけわかりやすく、何が協程スケジューリングかを説明します。まず、各協程は単純に一つのスレッドとして理解できます。多スレッドはプログラムの並行性を高めるためですが、同様に多協程も並行性を高めるためです。

ユーザーの各リクエストは協程を作成し、リクエストが終了すると協程も終了します。同時に何千もの並行リクエストがある場合、ある瞬間に特定のプロセス内には何千もの協程が存在する可能性があります。そうするとCPU資源が限られており、どの協程のコードを実行するべきでしょうか？

CPUがどの協程のコードを実行するべきかを決定する決定プロセスが`協程スケジューリング`です。そして、`Swoole`のスケジューリング戦略はどのようなものでしょうか？
- まず、ある協程のコードを実行している過程で、この行のコードが`Co::sleep()`に遭遇したり、ネットワーク`IO`が発生した場合（例えば`MySQL->query()`）、それは確かに時間がかかるプロセスです。そのため`Swoole`はこのMySQL接続のFdを[EventLoop](/learn?id=何がeventloop)に置きます。
    
    * その後、その協程のCPUを他の協程に譲ります：**つまり`yield`(挂起)**
    * MySQLのデータが戻ってきた後にこの協程を再開します：**つまり`resume`(復活)**
- 次に、もし協程のコードにCPUインタラクティブなコードがある場合は、[enable_preemptive_scheduler](/other/config)を開始することができます。Swooleは強制的にこの協程のCPUを譲ります。
## 子协程と親协程の優先順位

子協程（つまり`go()`の中の論理）を優先的に実行し、`协程`yield（Co::sleep()のところ）が発生するまで、そして[协程调度](/coroutine?id=协程调度)が外側の协程に移動します。

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

echo "main start\n";
run(function () {
    echo "coro " . Coroutine::getcid() . " start\n";
    Coroutine::create(function () {
        echo "coro " . Coroutine::getcid() . " start\n";
        Coroutine::sleep(.2);
        echo "coro " . Coroutine::getcid() . " end\n";
    });
    echo "coro " . Coroutine::getcid() . " do not wait children coroutine\n";
    Coroutine::sleep(.1);
    echo "coro " . Coroutine::getcid() . " end\n";
});
echo "end\n";

/*
main start
coro 1 start
coro 2 start
coro 1 do not wait children coroutine
coro 1 end
coro 2 end
end
*/
```
## 注意事項

Swooleプログラミングを使用する前に注意すべき点：
### 全局変数

協程は元の非同期ロジックを同期化しますが、協程間の切り替えは暗黙的に発生します。したがって、協程の切り替え前後で全局変数や`static`変数の一貫性を保証することはできません。

`PHP-FPM`の下では、全局変数から取得できるリクエストパラメータやサーバーのパラメータなどを、`Swoole`内では`$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`など`$_`で始まる変数を通じては、どんな属性パラメータも取得できません。

[context](/coroutine/coroutine?id=getcontext)を使用して協程IDを使って隔離し、全局変数の隔離を実現することができます。
### 多協程が共有するTCP接続

[参考](/question/use?id=client-has-already-been-bound-to-another-coroutine)
