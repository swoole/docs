## プロセス/スレッド間のロック Lock

* PHPでは、データの同期を実現するために簡単にロック`Swoole\Lock`を作成することができます。`Lock`クラスは5種類のロックタイプをサポートしています。
* 多线程モードでは`Swoole\Thread\Lock`を使用する必要がありますが、命名空間が異なるだけで、そのインターフェースは`Swoole\Lock`と完全に同じです。


ロックタイプ | 説明
---|---
SWOOLE_MUTEX | 互斥ロック
SWOOLE_RWLOCK | 読み書きロック
SWOOLE_SPINLOCK | 스핀ロック
SWOOLE_FILELOCK | ファイルロック（廃止）
SWOOLE_SEM | シグナル（廃止）

!>[onReceive](/server/events?id=onreceive)などの回调関数でロックを作成しないでください。そうするとメモリが継続的に増加し、メモリリークを引き起こす可能性があります。


## 使用例

```php
$lock = new Swoole\Lock(SWOOLE_MUTEX);
echo "[Master]create lock\n";
$lock->lock();
if (pcntl_fork() > 0)
{
  sleep(1);
  $lock->unlock();
} 
else
{
  echo "[Child] Wait Lock\n";
  $lock->lock();
  echo "[Child] Get Lock\n";
  $lock->unlock();
  exit("[Child] exit\n");
}
echo "[Master]release lock\n";
unset($lock);
sleep(1);
echo "[Master]exit\n";
```


## 警告

!> コルoutineではロックを使用することができません。慎重に使用し、`lock`と`unlock`の操作の間で协程の切り替えを引き起こす可能性のあるAPIを使用しないでください。


### 错误例

!> このコードは协程モードで`100%`デッドロックになります。

```php
$lock = new Swoole\Lock();
$c = 2;

while ($c--) {
  go(function () use ($lock) {
      $lock->lock();
      Co::sleep(1);
      $lock->unlock();
  });
}
```


## 方法


### __construct()

コンストラクタです。

```php
Swoole\Lock::__construct(int $type = SWOOLE_MUTEX, string $lockfile = '');
```

!> ロックの对象を循環して作成/破壊しないでください。そうするとメモリリークが発生します。

  * **パラメータ** 

    * **`int $type`**
      * **機能**：ロックの種類
      * **デフォルト値**：`SWOOLE_MUTEX`【互斥ロック】
      * **その他の値**：なし

    * **`string $lockfile`**
      * **機能**：ファイルロックの路径を指定する【`SWOOLE_FILELOCK`の場合にのみ必要】
      * **デフォルト値**：なし
      * **その他の値**：なし

!> 各種類のロックがサポートする方法は異なります。例えば、読み書きロックやファイルロックは`$lock->lock_read()`をサポートしています。また、ファイルロック以外の他の種類のロックは親プロセス内で作成する必要があります。そうすることで、`fork`で生成された子プロセス間でロックを争うことができます。


### lock()

ロックをかけ操作です。他のプロセスがロックを持っている場合、ここではブロックし、ロックを持っているプロセスが`unlock()`でロックを解放するまで待ちます。

```php
Swoole\Lock->lock(): bool
```


### trylock()

ロックをかけ操作です。`lock`メソッドと異なり、`trylock()`はブロックせず、すぐに戻ります。

```php
Swoole\Lock->trylock(): bool
```

  * **戻り値**

    * ロックをかけ成功したら`true`を戻し、その時点で共有変数を変更できます
    * ロックをかけに失敗したら`false`を戻し、他のプロセスがロックを持っていることを示します

!> `SWOOlE_SEM`シグナルには`trylock`方法がありません


### unlock()

ロックを解放操作です。

```php
Swoole\Lock->unlock(): bool
```


### lock_read()

読み取り専用ロックをかけ操作です。

```php
Swoole\Lock->lock_read(): bool
```

* 读取ロックを持っている間、他のプロセスは读取ロックを取得し、読み取り操作を続けることができます；
* しかし、`$lock->lock()`や`$lock->trylock()`はできません。これらは独占ロックを取得するもので、独占ロックをかけた時点で、他のプロセスはどんなロックをかけ操作もできなくなります、读取ロックを含みます；
* 他のプロセスが独占ロックを取得した場合（`$lock->lock()`/`$lock->trylock()`を呼び出す）、`$lock->lock_read()`はブロックし、独占ロックを持っているプロセスがロックを解放するまで待ちます。

!> `SWOOLE_RWLOCK`と`SWOOLE_FILELOCK`の種類のロックだけが读取ロックをかけをサポートしています


### trylock_read()

ロックをかけ操作です。この方法は`lock_read()`と同じですが、非ブロックです。

```php
Swoole\Lock->trylock_read(): bool
```

!> 呼び出されるとすぐに戻りますので、戻り値をチェックしてロックを取得したかどうかを確認する必要があります。

### lockwait()

ロックをかけ操作です。`lock()`メソッドと同じ機能ですが、`lockwait()`はタイムアウト時間を設定できます。

```php
Swoole\Lock->lockwait(float $timeout = 1.0): bool
```

  * **パラメータ** 

    * **`float $timeout`**
      * **機能**：タイムアウト時間を設定する
      * **値の単位**：秒【浮点数をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：`1`
      * **その他の値**：なし

  * **戻り値**

    * 指定された時間内にロックを取得できなければ`false`を戻します
    * ロックをかけに成功したら`true`を戻します

!> `Mutex`種類のロックだけが`lockwait`をサポートしています
