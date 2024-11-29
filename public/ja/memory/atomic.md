# プロセス/スレッド間のロックフリーカウンター Atomic

`Atomic`は`Swoole`の基盤として提供される原子的な計数操作クラスであり、整数のロックフリーな原子的な増加・減少を容易に実現できます。

* 共有メモリを使用することで、異なるプロセス間でカウンターを操作できます
* `gcc/clang`が提供する`CPU`の原子命令に基づいており、ロックを必要としません
* サーバープログラムでは、`Server->start`の前に作成しなければならず、`Worker`プロセスで使用できません
*デフォルトでは`32`位の無符号タイプを使用していますが、`64`位の符号付き整型が必要な場合は、`Swoole\Atomic\Long`を使用できます
* マルチスレッドモードでは、`Swoole\Thread\Atomic`と`Swoole\Thread\Atomic\Long`を使用する必要があります。命名空間は異なりますが、そのインターフェースは`Swoole\Atomic`や`Swoole\Atomic\Long`と完全に同じです。

!>[onReceive](/server/events?id=onreceive)などの回调関数の中でカウンターを作成してはいけません。そうするとメモリが継続的に増加し、メモリリークを引き起こす可能性があります。

!>[64]位の符号付き長整型のアトミックな計数はサポートされており、`new Swoole\Atomic\Long`を使用して作成する必要があります。`Atomic\Long`は`wait`と`wakeup`メソッドをサポートしていません。

## コンプリートな例

```php
$atomic = new Swoole\Atomic();

$serv = new Swoole\Server('127.0.0.1', '9501');
$serv->set([
    'worker_num' => 1,
    'log_file' => '/dev/null'
]);
$serv->on("start", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStart", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStop", function ($serv) {
    echo "shutdown\n";
});
$serv->on("Receive", function () {
    
});
$serv->start();
```

## メソッド


### __construct()

コンストラクタ。アトミックなカウンターオブジェクトを作成します。

```php
Swoole\Atomic::__construct(int $init_value = 0);
```

  * **パラメータ** 

    * **`int $init_value`**
      * **機能**：初期化される数値を指定する
      * **デフォルト値**：`0`
      * **その他の値**：なし


!>-`Atomic`は`32`位の無符号整数のみを操作でき、最大で`42`億まで支持し、負数はサポートしません；  

-サーバーでのアトミックなカウンターの使用には、`Server->start`の前に作成しなければなりません；  
-プロセスでのアトミックなカウンターの使用には、`Process->start`の前に作成しなければなりません。


### add()

カウンターを増やす。

```php
Swoole\Atomic->add(int $add_value = 1): int
```

  * **パラメータ** 

    * **`int $add_value`**
      * **機能**：増やす数値【正整数でなければならない】
      * **デフォルト値**：`1`
      * **その他の値**：なし

  * **戻り値**

    * `add`メソッドが成功した後に結果の数が返されます

!>元の値と合計すると`42`億を超えるとオーバーフローし、高い位の値が取り除かれます。


### sub()

カウンターを減らす。

```php
Swoole\Atomic->sub(int $sub_value = 1): int
```

  * **パラメータ** 

    * **`int $sub_value`**
      * **機能**：減らす数値【正整数でなければならない】
      * **デフォルト値**：`1`
      * **その他の値**：なし

  * **戻り値**

    * `sub`メソッドが成功した後に結果の数が返されます

!>元の値から引くと`0`を下回るとオーバーフローし、高い位の値が取り除かれます。


### get()

現在のカウンターの値を取得する。

```php
Swoole\Atomic->get(): int
```

  * **戻り値**

    *現在の数値が返されます


### set()

現在の値を指定された数字に設定する。

```php
Swoole\Atomic->set(int $value): void
```

  * **パラメータ** 

    * **`int $value`**
      * **機能**：設定したいターゲットの値を指定する
      * **デフォルト値**：なし
      * **その他の値**：なし


### cmpset()

現在の数値がパラメータ`1`と等しい場合、現在の数値をパラメータ`2`に設定します。   

```php
Swoole\Atomic->cmpset(int $cmp_value, int $set_value): bool
```

  * **パラメータ** 

    * **`int $cmp_value`**
      * **機能**：現在の数値が`$cmp_value`と等しい場合は`true`を返し、現在の数値を`$set_value`に設定します。等しくない場合は`false`を返します【`42`億以下の整数でなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし

    * **`int $set_value`**
      * **機能**：現在の数値が`$cmp_value`と等しい場合は`true`を返し、現在の数値を`$set_value`に設定します。等しくない場合は`false`を返します【`42`億以下の整数でなければなりません】
      * **デフォルト値**：なし
      * **その他の値**：なし


### wait()

wait状態に設定する。

!>[atomi]cカウンターの値が`0`の場合、プログラムはwait状態に入ります。他のプロセスが`wakeup`を呼び出すと、再びプログラムを呼び覚ますことができます。基盤は`Linux Futex`に基づいており、この特性を使用すると、wait、notify、lockの機能をたった`4`Byteのメモリで実現できます。`Futex`をサポートしていないプラットフォームでは、基盤はループ`usleep(1000)`でシミュレートして実現されます。

```php
Swoole\Atomic->wait(float $timeout = 1.0): bool
```

  * **パラメータ** 

    * **`float $timeout`**
      * **機能**：タイムアウト時間を指定する【`-1`を指定すると永遠にタイムアウトせず、他のプロセスが`wakeup`するまで継続して待ちます】
      * **値の単位**：秒【浮点数をサポートしており、例えば`1.5`は`1s`+`500ms`を意味します】
      * **デフォルト値**：`1`
      * **その他の値**：なし

  * **戻り値** 

    *タイムアウトした場合は`false`を返し、エラーコードは`EAGAIN`です。`swoole_errno`関数を使用して取得できます
    *成功した場合は`true`を返し、他のプロセスが`wakeup`によって現在のロックを成功させて呼び覚ましたことを示します

  * **コーン環境**

  `wait`はプロセス全体をブロックするため、コーン環境で`Atomic->wait()`を使用するとプロセスがhangすることがありますので、避けなければなりません。


!>[use] `wait/wakeup`特性を使用する際は、アトミックカウンターの値は`0`または`1`でなければならず、そうでなければ正常に使用できません；  
-もちろん、アトミックカウンターの値が`1`の場合、リソースが現在利用可能であることを意味し、`wait`関数は直ちに`true`を返します。

  * **使用例**

    ```php
    $n = new Swoole\Atomic;
    if (pcntl_fork() > 0) {
        echo "master start\n";
        $n->wait(1.5);
        echo "master end\n";
    } else {
        echo "child start\n";
        sleep(1);
        $n->wakeup();
        echo "child end\n";
    }
    ```

### wakeup()

wait状態にある他のプロセスを呼び覚ます。

```php
Swoole\Atomic->wakeup(int $n = 1): bool
```

  * **パラメータ** 

    * **`int $n`**
      * **機能**：呼び覚ますプロセスの数
      * **デフォルト値**：なし
      * **その他の値**：なし

*現在のアトミックカウンターが`0`の場合、プロセスがwaitしていることはなく、`wakeup`は直ちに`true`を返します；
*現在のアトミックカウンターが`1`の場合、現在プロセスがwaitしているため、`wakeup`は待っているプロセスを呼び覚まし、`true`を返します；
*呼び覚まされたプロセスが戻った後、アトミックカウンターは`0`に設定され、これにより、再び`wait`しているプロセスを呼び覚ますことができます。
