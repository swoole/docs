```
# コロニアル <!-- {docsify-ignore-all} -->

このセクションでは、コロニアルの基本的な概念と一般的な問題について説明します。

Swoole 4.0バージョンから、「コロニアル（Coroutine）」+「チャネル（Channel）」の機能が完全に提供され、新しいCSPプログラミングモデルが導入されました。

1.開発者は、非感知的に同期コードで書くことで、[非同期IO](/learn?id=同步io异步io)の効果とパフォーマンスを達成し、従来の非同期回调による断片的なコードロジックや、多层の回调に陥り、コードが維持できなくなる問題を避けることができます。
2.また、低レベルでコロニアルを封装しているため、従来のPHP層のコロニアルフレームワークと比較して、開発者は[yield](https://www.php.net/manual/zh/language.generators.syntax.php)キーワードを使用してコロニアルのIO操作を識別する必要がありません。したがって、yieldの文法を深く理解する必要もなく、各レベルの呼び出しをyieldに変更する必要もありません。これにより、開発効率が大幅に向上します。
3.さまざまなタイプの完全な[コロニアルクライアント](/coroutine_client/init)が提供されており、ほとんどの開発者のニーズを満たすことができます。

## コロニアルとは何か

コロニアルは、スレッドを単純に理解することができますが、このスレッドはユーザーモードであり、オペレーティングシステムの参加を必要とせず、作成、破壊、切り替えのコストは非常に低いです。スレッドと異なり、コロニアルはマルチコアCPUを利用することはできません。マルチコアCPUを利用したい場合は、Swooleのマルチプロセスモデルに依存する必要があります。

## チャネルとは何か

「チャネル」とは、メッセージキューを理解することができますが、これはコロニアル間のメッセージキューです。複数のコロニアルは、「プッシュ」と「ポップ」操作を通じて、キュー内の生成メッセージと消費メッセージを交換し、コロニアル間の通信にデータを送信または受信するために使用されます。注意すべき点は、「チャネル」はプロセスをまたぐことはできず、Swooleプロセス内のコロニアル間でのみ通信できるということです。最も典型的な応用は[接続プール](/coroutine/conn_pool)と[並行呼び出し](/coroutine/multi_call)です。

## コロニアルコンテナとは何か

「Coroutine::create」または「go()」メソッドを使用してコロニアルを作成する（[別名セクション](/other/alias?id=コロニアルの略称)を参照）。创建されたコロニアル内でのみ、コロニアル「API」を使用できますが、コロニアルは必ずコロニアルコンテナ内で作成する必要があります。[コロニアルコンテナ](/coroutine/scheduler)を参照してください。

## コロニアルスケジュール

ここでは、できるだけ一般的にコロニアルスケジュールについて説明します。まず、各コロニアルを単なるスレッドと理解することができます。皆さんが知っているように、マルチスレッドはプロセスの並行性を高めるためにあります。同様に、マルチコロニアルも並行性を高めるためにあります。

ユーザーの各リクエストはコロニアルを作成し、リクエストが終了するとコロニアルが終了します。もし同時に何千もの並行リクエストがある場合、ある時点であるプロセス内部には何千ものコロニアルが存在します。では、CPUリソースは限られています。どのコロニアルのコードを実行すべきでしょうか？

CPUがどのコロニアルのコードを実行するかを決定する決定プロセスが「コロニアルスケジュール」です。では、Swooleのスケジュール戦略はどのようなものでしょうか？

- まず、あるコロニアルのコードを実行している過程で、このコードが「Co::sleep()」に遭遇したり、ネットワークIOが発生したりした場合、例えば「MySQL->query()」など、これは確実に時間がかかるプロセスです。SwooleはこのMySQL接続のFdを[EventLoop](/learn?id=什么是eventloop)に置きます。
      
    * その後、このコロニアルのCPUを他のコロニアルに譲ります：**つまり`yield`（挂起）**
    * MySQLデータが戻ってきた後、このコロニアルを再開します：**つまり`resume`（恢复）**


- 次に、コロニアルのコードにCPU集約型コードがある場合は、「enable_preemptive_scheduler](/other/config)」を有効にすると、Swooleはこのコロニアルに強制的にCPUを譲ります。


## 親子コロニアルの優先順位

まず、子コロニアル（つまり「go()」内のロジック）を優先して実行し、コロニアルが「yield」（Co::sleep()の場所）まで発生するまで続けます。その後、[コロニアルスケジュール](/coroutine?id=协程调度)が外側のコロニアルに移動します。

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
  

## 留意点

Swooleプログラミングを使用する前に注意すべき点：

###グローバル変数

コロニアルは従来の非同期ロジックを同期化しますが、コロニアル間の切り替えは暗黙のうちに発生するため、コロニアルの切り替え前後のグローバル変数や`static`変数の一貫性を保証することはできません。

PHP-FPMの下では、グローバル変数から取得できるリクエストパラメータやサーバーパラメータなどがありますが、Swoole内では、**$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`などの`$_`で始まる変数で、どの属性パラメータも取得することはできません。

[context](/coroutine/coroutine?id=getcontext)を使用してコロニアルIDを隔離し、グローバル変数の隔離を実現することができます。

### 多コロニアルが共有するTCP接続

[参照](/question/use?id=client-has-already-been-bound-to-another-coroutine)
```