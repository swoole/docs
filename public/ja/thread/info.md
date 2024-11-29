# 方法とプロパティ

## 方法

### __construct()
マルチスレッドの構築方法

```php
Swoole\Thread->__construct(string $script_file, mixed ...$args)
```
* **引数**
    * `string $script_file`
        * 機能：スレッドが開始された後に実行されるファイルです。
        *デフォルト値：なし。
        *その他の値：なし。

    * `mixed $args`
        * 機能：親スレッドが子スレッドに渡す共有データで、子スレッドでは `Swoole\Thread::getArguments()` で取得できます。
        *デフォルト値：なし。
        *その他の値：なし。

!> スレッドの作成に失敗すると `Swoole\Exception`が投げられ、`try catch`でそれをキャッチすることができます。

### join()
親スレッドが子スレッドの終了を待つ。子スレッドがまだ実行中の場合、`join()`はブロックし、子スレッドが終了するまで待ちます。

```php
Swoole\Thread->join(): bool
```
* **戻り値**
    * `true` 表示操作が成功し、`false` 表示操作に失敗しました。

### joinable()
子スレッドがまだ終了していないかどうかを確認します。

```php
Swoole\Thread->joinable(): bool
```

#### 戻り値

- `true` 表示子スレッドはすでに終了しており、この時 `join()`を呼びてもブロックしません。
- `false` 表示まだ終了していません。

### detach()
子スレッドを親スレッドの管理から離れさせ、もはや `join()`でスレッドの終了を待つ必要はありません。

```php
Swoole\Thread->detach(): bool
```
* **戻り値**
    * `true` 表示操作が成功し、`false` 表示操作に失敗しました。

### getId()
静的な方法で、現在のスレッドの `ID`を取得します。

```php
Swoole\Thread::getId(): int
```
* **戻り値**
    * 整数类型的で、現在のスレッドのidを返します。

### getArguments()
静的な方法で、親スレッドが `new Swoole\Thread()` を使用して渡した共有データを取得し、子スレッドで呼び出します。

```php
Swoole\Thread::getArguments(): ?array
```

* **戻り値**
    * 子スレッドでは、親プロセスから渡された共有データが返されます。

?> 親スレッドにはスレッドパラメータがなく、スレッドパラメータが空かどうかで親子スレッドを区別し、異なるロジックを実行できます
```php
use Swoole\Thread;

$args = Thread::getArguments(); // 親スレッドであれば $argsは空で、子スレッドであれば $argsは空ではありません
if (empty($args)) {
    # 親スレッド
    new Thread(__FILE__, 'child thread'); // スレッドパラメータを渡す
    echo "main thread\n";
} else {
    # 子スレッド
    var_dump($args); // 出力: ['child thread']
}
```

### getInfo()
静的な方法で、現在のマルチスレッド環境の情報を取得します。

```php
Swoole\Thread::getInfo(): array
```
返される配列の情報は以下の通りです：

- `is_main_thread`：現在のバッチが親スレッドかどうか
- `is_shutdown`：スレッドがすでにシャットダウンしているかどうか
- `thread_num`：現在アクティブなスレッドの数

### getPriority()
静的な方法で、現在のスレッドのスケジュール情報を取得します。

```php
Swoole\Thread->getPriority(): array
```
返される配列の情報は以下の通りです：

- `policy`：スレッドスケジュール戦略
- `priority`：スレッドのスケジュール優先度

### setPriority()
静的な方法で、現在のスレッドのスケジュール優先度と戦略を設定します。

?> `root`ユーザーのみ設定でき、非`root`ユーザーの操作は拒否されます

```php
Swoole\Thread->setPriority(int $priority, int $policy = -1): bool
```

* **引数**
    * `int $priority`
        * 機能：スレッドスケジュール優先度を設定します。
        *デフォルト値：なし。
        *その他の値：なし。

    * `mixed $policy`
        * 機能：スレッドスケジュール優先戦略を設定します。
        *デフォルト値：`-1`、スケジュール戦略を調整しないことを意味します。
        *その他の値：`Thread::SCHED_*`関連の定数。

* **戻り値**
    * 成功した場合は `true` 返回し、失敗した場合は `false` 返回します。エラー情報を取得するには `swoole_last_error()` を使用してください。

> `SCHED_BATCH/SCHED_ISO/SCHED_IDLE/SCHED_DEADLINE`は `Linux`システムでのみ使用できます。  

> `SCHED_FIFO/SCHED_RR`戦略のスレッドは通常リアルタイムスレッドであり、優先度が通常のスレッドよりも高く、より多くの `CPU`タスク時間を取得できます。

### getAffinity()
静的な方法で、現在のスレッドの `CPU`親和性を取得します。

```php
Swoole\Thread->getAffinity(): array
```
返される配列の要素は `CPU`コア数です。例えば、`[0, 1, 3, 4]` 表示される場合、このスレッドは `CPU`の `0/1/3/4`コアでスケジュールされます。

### setAffinity()
静的な方法で、現在のスレッドの `CPU`親和性を設定します。

```php
Swoole\Thread->setAffinity(array $cpu_set): bool
```

* **引数**
    * `array $cpu_set`
        * 機能：`CPU`コアのリストです。例えば `[0, 1, 3, 4]`
        *デフォルト値：なし。
        *その他の値：なし。

* **戻り値**
    * 成功した場合は `true` 返回し、失敗した場合は `false` returnします。エラー情報を取得するには `swoole_last_error()` を使用してください。

### setName()
静的な方法で、現在のスレッドの名前を設定します。`ps`や`gdb`などのツールを使用してスレッドを表示またはデバッグする際に、より親しみやすい表示を提供します。

```php
Swoole\Thread->setName(string $name): bool
```

* **引数**
    * `string $name`
        * 機能：スレッドの名前です。
        *デフォルト値：なし。
        *その他の値：なし。

* **戻り値**
    * 成功した場合は `true` 返回し、失敗した場合は `false` returnします。エラー情報を取得するには `swoole_last_error()` を使用してください。

```shell
$ ps aux|grep -v grep|grep pool.php
swoole  2226813  0.1  0.1 423860 49024  pts/6   Sl+  17:38  0:00 php pool.php

$ ps -T -p 2226813
   PID   SPID TTY          TIME CMD
2226813 2226813 pts/6     00:00:00 Master Thread
2226813 2226814 pts/6     00:00:00 Worker Thread 0
2226813 2226815 pts/6     00:00:00 Worker Thread 1
2226813 2226816 pts/6     00:00:00 Worker Thread 2
2226813 2226817 pts/6     00:00:00 Worker Thread 3
```

### getNativeId()
スレッドのシステム `ID`を取得し、整数を返します。これはプロセスの `PID`に似ています。

```php
Swoole\Thread->getNativeId(): int
```

この関数は `Linux`システムで `gettid()`システム呼び出しを行い、オペレーティングシステムのスレッド `ID`のような短整数を取得します。プロセスのスレッドが破壊された時にオペレーティングシステムによって取り消される可能性があります。

この `ID`は `gdb`や `strace`などのデバッグツールを使用して、例えば `gdb -p $tid` などの操作に使用できます。また、`/proc/{PID}/task/{ThreadNativeId}`读取してスレッドの実行情報を取得することもできます。

## プロパティ

### id

このプロパティを通じて子スレッドの `ID`を取得できます。このプロパティは `int`タイプです。

> このプロパティは親スレッドでのみ使用でき、子スレッドでは `$thread`オブジェクトを取得することはできません。スレッドの `ID`を取得するには `Thread::getId()` 静的なメソッドを使用してください。

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```

## 定数

名前 | 機能
---|---
`Thread::HARDWARE_CONCURRENCY` | ハードウェアの並行スレッド数、通常は `CPU`のコア数です。
`Thread::API_NAME` | スレッド `API` 名、例えば `POSIX Threads`。
`Thread::SCHED_OTHER` | スレッドスケジュール戦略 `SCHED_OTHER`。
`Thread::SCHED_FIFO` | スレッドスケジュール戦略 `SCHED_FIFO`。
`Thread::SCHED_RR` | スレッドスケジュール戦略 `SCHED_RR`。
`Thread::SCHED_BATCH` | スレッドスケジュール戦略 `SCHED_BATCH`。
`Thread::SCHED_ISO` | スレッドスケジュール戦略 `SCHED_ISO`。
`Thread::SCHED_IDLE` | スレッドスケジュール戦略 `SCHED_IDLE`。
`Thread::SCHED_DEADLINE` | スレッドスケジュール戦略 `SCHED_DEADLINE`。
