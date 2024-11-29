## スレッドプール

スレッドプールは、複数の作業スレッドを維持し、自動的に子スレッドを作成、再起動、閉鎖することができます。

## 方法

### __construct()

構築方法です。

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **パラメータ**
  * `string $workerThreadClass`：作業スレッドが実行するクラス
  * `int $worker_num`：作業スレッドの数を指定する

### withArguments()

作業スレッドのパラメータを設定し、`run($args)`方法でこのパラメータを取得できます。

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```

### withAutoloader()

`autoload`ファイルをロードします。

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **パラメータ**
  * `string $autoloader`：`autoload`の`PHP`ファイルのpath

> Composerを使用している場合は、自動的に`vendor/autoload.php`を読み込みますので、手動で指定する必要はありません。

### withClassDefinitionFile()

作業スレッドクラスの定義ファイルを設定します。**このファイルは`namespace`、`use`、`class定義`のコードのみを含めることができ、実行可能なコードの断片は含まれてはなりません**。

作業スレッドクラスは`Swoole\Thread\Runnable`基クラスを継承し、`run(array $args)`方法を実装しなければなりません。

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **パラメータ**
  * `string $classFile`：作業スレッドクラスの`PHP`ファイルのpath

作業スレッドクラスが`autoload`pathにある場合は、設定する必要はありません。

### start()

すべての作業スレッドを起動します。

```php
Swoole\Thread\Pool::start(): void;
```

### shutdown()

スレッドプールを閉鎖します。

```php
Swoole\Thread\Pool::shutdown(): void;
```

## 例
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```

## Thread\Runnable

作業スレッドクラスはこのクラスを継承しなければなりません。

### run(array $args)

この方法をオーバーライドしなければなりません。`$args`はスレッドプールが`withArguments()`方法で渡したパラメータです。

### shutdown()

スレッドプールを閉鎖します。

### $id 
現在のスレッドの番号で、範囲は`0~(スレッド総数-1)`です。スレッドが再起動したとき、新しい後継スレッドは古いスレッド番号保持一致します。

### 例

```php
use Swoole\Thread\Runnable;

class TestThread extends Runnable
{
    public function run($uuid, $map): void
    {
        $map->incr('thread', 1);

        for ($i = 0; $i < 5; $i++) {
            usleep(10000);
            $map->incr('sleep');
        }

        if ($map['sleep'] > 50) {
            $this->shutdown();
        }
    }
}
```
