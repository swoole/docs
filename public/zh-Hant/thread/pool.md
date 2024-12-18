# 线程池

线程池可以维持多个工作线程的运行，自动创建、重启、关闭子线程。

## 方法

### __construct()

构造方法。

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **参数** 
  * `string $workerThreadClass`：工作线程运行的类
  * `int $worker_num`：指定工作线程的数量

### withArguments()

设置工作线程的参数，在`run($args)`方法中可获取到此参数。

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```

### withAutoloader()

加载`autoload`文件

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **参数** 
  * `string $autoloader`：`autoload`的`PHP`文件路径

> 若使用了`Composer`，底层可自动推断并在工作进程中加载`vendor/autoload.php`，不需要手动指定

### withClassDefinitionFile()

设置工作线程类的定义文件，**此文件必须只包含`namespace`、`use`、`class定义`代码，不得包含可执行的代码片段**。

工作线程类必须继承自`Swoole\Thread\Runnable`基类，并实现`run(array $args)`方法。

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **参数** 
  * `string $classFile`：定义工作线程类的`PHP`文件路径

若工作线程类在`autoload`路径中，可不设置

### start()

启动所有工作线程

```php
Swoole\Thread\Pool::start(): void;
```

### shutdown()
关闭线程池

```php
Swoole\Thread\Pool::shutdown(): void;
```

## 示例
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```

## Thread\Runnable

工作线程类必须继承此类。

### run(array $args)

必须重写此方法，`$args`是线程池对象使用`withArguments()`方法传入的参数。

### shutdown()
关闭线程池

### $id 
当前线程的编号，范围是`0~(线程总数-1)`。线程重启时新的继任线程与旧的线程序号是一致的。

### 示例

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
