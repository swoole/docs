```php
# コロニーのバリア

[Swoole Library](https://github.com/swoole/library)では、より便利なコリューションメスナントツールが底层に提供されています。それは`Coroutine\Barrier`またはコリューションバリアと呼ばれています。PHPの参照カウントとCoroutine APIに基づいて実現されています。

[Coroutine\WaitGroup](/coroutine/wait_group)と比較して、`Coroutine\Barrier`は使用が簡単で、パラメータを渡すか、クロージーの`use`文法を通じて、サブコリューションフィクションに引入するだけで済みます。

!> Swooleバージョン >= v4.5.5での利用が推奨されます。

## 使用例

```php
use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;

run(function () {
    $barrier = Barrier::make();

    $count = 0;
    $N = 4;

    foreach (range(1, $N) as $i) {
        Coroutine::create(function () use ($barrier, &$count) {
            System::sleep(0.5);
            $count++;
        });
    }

    Barrier::wait($barrier);
    
    assert($count == $N);
});
```

## 実行の流れ

* まず`Barrier::make()`で新しいコリューションバリアを作成する
*サブコリューションの中でバリアを`use`文法で渡し、参照カウントを増やす
* 待つべき位置で`Barrier::wait($barrier)`を加えると、現在のコリューションは自動的に挂起され、そのバリアを参照するサブコリューションが終了するのを待つ
* サブコリューションが終了すると、`$barrier`オブジェクトの参照カウントが減少し、0になる
* 全てのサブコリューションがタスクを処理し終了すると、`$barrier`オブジェクトの参照カウントが0になり、そのオブジェクトのデストラクタ関数の中で底层は自動的に挂起されたコリューションを復元し、`Barrier::wait($barrier)`関数から戻る

`Coroutine\Barrier`は[WaitGroup](/coroutine/wait_group)や[Channel](/coroutine/channel)よりも使いやすく、PHPの並行プログラミングのユーザーテ用户体验を大幅に向上させています。
```
