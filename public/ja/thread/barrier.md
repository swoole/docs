# スレッド同期実行のバリアー Barrier

`Thread\Barrier`はスレッド同期のメカニズムの一種です。特定の点で複数のスレッドを同期させ、すべてのスレッドがあるクリティカルポイント（バリアー）に到達する前に自分のタスクを完了することを保証します。すべての参加しているスレッドがこのバリアーに到達した時のみ、後続のコードを実行することができます。

例えば、私たちは`4`つのスレッドを作成し、これらのスレッドが準備が整ったら一緒にタスクを実行したいと考えています。これはまるでランニングレースで審判の射撃のように、信号を発射した後に同時にスタートします。これは`Thread\Barrier`を使用して実現できます。

## 例
```php
use Swoole\Thread;
use Swoole\Thread\Barrier;

const N = 4;
$args = Thread::getArguments();

if (empty($args)) {
    $barrier = new Barrier(N);
    $n = N;
    $threads = [];
    while($n--) {
        $threads[] = new Thread(__FILE__, $barrier, $n);
    }
} else {
    $barrier = $args[0];
    $n = $args[1];
    // すべてのスレッドが準備が整うのを待つ
    $barrier->wait();
    echo "thread $n is running\n";
}
```

## 方法

### __construct()
構築方法

```php
Thread\Barrier()->__construct(int $count): void
```

  * **パラメータ**
      * `int $count`
          * 機能：スレッドの数。`1`未満は許されません。
          * 默认値：なし。
          * その他：なし。
  
`wait`操作を実行するスレッドの数は設定された計数と一致しなければならず、そうでなければすべてのスレッドがブロックされます。

### wait()

他のスレッドをブロックして待ち、すべてのスレッドが`wait`状態になるまで待ちます。その後、すべての待っているスレッドを同時に呼び覚まし、以降のコードを実行します。

```php
Thread\Barrier()->wait(): void
```
