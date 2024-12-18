# Корутины\Планировщик

?> Все [корутины](/coroutine) должны быть [created](/coroutine/coroutine?id=create) внутри [корутинного контейнера](/coroutine/scheduler). В большинстве случаев при запуске программы с [Swoole], корутинный контейнер создается автоматически. Существует три способа запуска программы с использованием Swoole:

   - Call the [async style](/server/init) server program's [start](/server/methods?id=start) method, which creates a coroutines container in the event callback, see [enable_coroutine](/server/setting?id=enable_coroutine).
   - Call the [start](/process/process_pool?id=start) method of the two process management modules provided by Swoole, [Process](/process/process) and [Process\Pool](/process/process_pool), which creates a coroutines container when processes start, see the `enable_coroutine` parameter in the constructors of these modules.
   - Other ways to directly write and start coroutines without using a coroutine container are not recommended. For example:

* **Запустить HTTP-сервис, работающий на полностью корутинах**
```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
echo 1;//Ничего не будет выполнено
```

* **Создать две корутины, которые будут выполняться одновременно, и сделать что-то**
```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function() {
        var_dump(file_get_contents("http://www.xinhuanet.com/"));
    });

    Coroutine::create(function() {
        Coroutine::sleep(1);
        echo "done\n";
    });
});
echo 1;//Можно увидеть выполнения
```

!> Доступно в версии Swoole v4.4+.

!> Нельзя использовать вложенные вызовы `Coroutine\run()`. Если в `Coroutine\run()` есть незаработанные события после выполнения, то следующая часть кода не будет выполнена. Напротив, если событий больше нет, скрипт продолжит работу и можно снова вызвать `Coroutine\run()`.

Функция `Coroutine\run()` на самом деле является обернутой версией класса `Swoole\Coroutine\Scheduler` (класс планировщика корутин), и для тех, кто хочет узнать подробности, можно посмотреть методы класса `Swoole\Coroutine\Scheduler`:


### set()

?> **Установить параметры выполнения корутин.**

?> Это aliас метода `Coroutine::set`. Для справки смотрите [Coroutine::set](/coroutine/coroutine?id=set).

```php
Swoole\Coroutine\Scheduler->set(array $options): bool
```

* **Пример**

```php
$sch = new Swoole\Coroutine\Scheduler;
$sch->set(['max_coroutine' => 100]);
```


### getOptions()

?> **Получить установленные параметры выполнения корутин.** Доступно с версии Swoole >= v4.6.0

?> Это aliас метода `Coroutine::getOptions`. Для справки смотрите [Coroutine::getOptions](/coroutine/coroutine?id=getoptions).

```php
Swoole\Coroutine\Scheduler->getOptions(): null|array
```


### add()

?> **Добавьте задание.**

```php
Swoole\Coroutine\Scheduler->add(callable $fn, ... $args): bool
```

* **Параметры**

    * **`callable $fn`**
      * **Функция**: обратный вызов функции
      * **Значение по умолчанию**: нет
      * **Другое**: нет

    * **`... $args`**
      * **Функция**: дополнительные аргументы, которые будут переданы корутине
      * **Значение по умолчанию**: нет
      * **Другое**: нет

* **Пример**
```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;
$scheduler->add(function($a, $b) {
    Coroutine::sleep(1);
    echo assert($a == 'hello') . PHP_EOL;
    echo assert($b == 12345) . PHP_EOL;
    echo "Done.\n";
}, "hello", 12345);

$scheduler->start();
```
  
  * **Примечание**

    !> В отличие от функции `go`, корутины, добавленные здесь, не начнут выполняться сразу, а будут ждать, когда будет вызван метод `start`, чтобы начать их одновременно сExecuting. Если в программе только добавлены корутины и не вызван метод `start`, чтобы запуститься, функция `$fn` корутины не будет выполнена.


### parallel()

?> **Добавьте параллельные задания.**

?> В отличие от метода `add`, метод `parallel` создает параллельные корутины. Когда начинается, сразу же запускаются `$num` корутин `$fn`, которые выполняются параллельно.

```php
Swoole\Coroutine\Scheduler->parallel(int $num, callable $fn, ... $args): bool
```

  * **Параметры**

    * **`int $num`**
      * **Функция**: количество корутин для запуска
      * **Значение по умолчанию**: нет
      * **Другое**: нет

    * **`callable $fn`**
      * **Функция**: обратный вызов функции
      * **Значение по умолчанию**: нет
      * **Другое**: нет

    * **`... $args`**
      * **Функция**: дополнительные аргументы, которые будут переданы корутинам
      * **Значение по умолчанию**: нет
      * **Другое**: нет

  * **Пример**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;

$scheduler->parallel(10, function ($t, $n) {
    Coroutine::sleep($t);
    echo "Co ".Coroutine::getCid()."\n";
}, 0.05, 'A');

$scheduler->start();
```

### start()

?> **Запустить программу.**

?> Перебирает задания корутин, добавленные методом `add` и `parallel`, и выполняет их.

```php
Swoole\Coroutine\Scheduler->start(): bool
```

  * **Возвращаемое значение**

    * Если запуск успешно, будут выполнены все добавленные задания, и метод `start` вернет `true`, когда все корутины закончат работу.
    * Если запуск失败, вернет `false`, возможно, потому что уже начался или созданный другой планировщик не позволяет создать еще один.
