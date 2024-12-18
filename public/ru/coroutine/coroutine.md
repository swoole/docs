# API координационных функций

> Рекомендуется сначала посмотреть на [Обзор](/coroutine), чтобы понять основные понятия координации, прежде чем обратиться к этой секции.


## Методы


### set()

Установка координационных параметров.

```php
Swoole\Coroutine::set(array $options);
```


Параметры | Стабильная версия после этого | Функция 
---|---|---
max_coroutine | - | Установить максимальное количество координаций в глобальном масштабе, после достижения лимита нижестоящая слой не сможет создавать новые координации, в Server это будет перекрыто с [server->max_coroutine](/server/setting?id=max_coroutine).
stack_size/c_stack_size | - | Установить начальный размер памяти для C стека одной координации, по умолчанию 2M
log_level | v4.0.0 | Уровень логирования [Подробности смотрите](/consts?id=уровни_логирования)
trace_flags | v4.0.0 | метки отслеживания [Подробности смотрите](/consts?id=метки_отслеживания)
socket_connect_timeout | v4.2.10 | Время ожидания подключения, **Смотрите [правила таймаута клиентов](/coroutine_client/init?id=правила_таймаута)**
socket_read_timeout | v4.3.0 | Таймаут чтения, **Смотрите [правила таймаута клиентов](/coroutine_client/init?id=правила_таймаута)**
socket_write_timeout | v4.3.0 | Таймаут письма, **Смотрите [правила таймаута клиентов](/coroutine_client/init?id=правила_таймаута)**
socket_dns_timeout | v4.4.0 | Таймаут разрешения имени DNS, **Смотрите [правила таймаута клиентов](/coroutine_client/init?id=правила_таймаута)**
socket_timeout | v4.2.10 | Таймаут отправки/получения, **Смотрите [правила таймаута клиентов](/coroutine_client/init?id=правила_таймаута)**
dns_cache_expire | v4.2.11 | Установить время жизни DNS-кэша swoole, единица времени - секунды, по умолчанию 60 секунд
dns_cache_capacity | v4.2.11 | Установить вместимость DNS-кэша swoole, по умолчанию 1000
hook_flags | v4.4.0 | Конфигурация大范围 hooks для однокорпоративной координации, смотрите [однокорпоративная координация](/runtime)
enable_preemptive_scheduler | v4.4.0 | Установить активацию предварительного планировщика координации, максимальное время выполнения координации составляет 10 мс, это перекроет [ini-конфигурацию](/other/config)
dns_server | v4.5.0 | Установить сервер дляDNS-запросов, по умолчанию "8.8.8.8"
exit_condition | v4.5.0 | Передать `callable`, который возвращает `bool`, можно настроить условия выхода реактора. Например: я хочу, чтобы программа завершилась, когда количество координаций равно нулю, тогда можно написать `Co::set(['exit_condition' => function () {return Co::stats()['coroutine_num'] === 0;}]);`
enable_deadlock_check | v4.6.0 | Установить, включать ли проверку за Deadlock координации, по умолчанию включено
deadlock_check_disable_trace | v4.6.0 | Установить, выводить ли стек фреймов для проверки за Deadlock координации
deadlock_check_limit | v4.6.0 | Ограничить максимальное количество выведенных стеков фреймов при проверке за Deadlock координации
deadlock_check_depth | v4.6.0 | Ограничить количество стеков фреймов, возвращаемых при проверке за Deadlock координации
max_concurrency | v4.8.2 | Максимальная количество одновременных запросов


### getOptions()

Получить установленные координационные параметры.

!> Версия Swoole >= `v4.6.0` доступна

```php
Swoole\Coroutine::getOptions(): null|array;
```


### create()

Создать новую координацию и немедленно выполнить ее.

```php
Swoole\Coroutine::create(callable $function, ...$args): int|false
go(callable $function, ...$args): int|false // Смотрите конфигурацию use_shortname в php.ini
```

* **Параметры**

    * **`callable $function`**
      * **Функция**: код, который будет выполняться в координации, должен быть `callable`, общее количество координаций, которые система может создать, ограничено настройкой [server->max_coroutine](/server/setting?id=max_coroutine)
      * **По умолчанию**: нет
      * **Другие значения**: нет

* **Возвращаемое значение**

    * Возвращается `false`, если создание gagal
    * Возвращается `ID` созданной координации

!> Поскольку нижестоящий уровень будет сначала выполнять код дочерних координаций, `Coroutine::create` вернет только тогда, когда дочерняя координация будет заморожена, и продолжит выполнение кода текущей координации.

  * **Порядок выполнения**

    В одной координации использовать `go` для создания новой координации. Поскольку координации Swoole являются моделью одного процесса и одного потока, следовательно:

    * Дочерние координации, созданные с помощью `go`, будут выполняться в первую очередь, и когда дочерние координации будут завершены или заморожены, они вернутся к родительской координации и продолжат выполнение кода вниз по дереву
    * Если дочерняя координация заморожена, а родительская координация завершена, это не повлияет на выполнение дочерней координации

    ```php
    \Co\run(function() {
        go(function () {
            go(function () {
                Co::sleep(3.0);
                go(function () {
                    Co::sleep(2.0);
                    echo "co[3] end\n";
                });
                echo "co[2] end\n";
            });

            Co::sleep(1.0);
            echo "co[1] end\n";
        });
    });
    ```

* **Тратка на координации**

  Каждая координация является независимой друг от друга и требует создания отдельного пространства памяти (стек памяти). В версии `PHP-7.2` нижестоящий уровень назначит `8K` для хранения переменных в стеке координации, размер `zval` составляет `16字节`, поэтому `8K` стека могут хранить до `512` переменных. Если использование стека памяти координации превышает `8K`, `ZendVM` автоматически увеличит его размер.

  Когда координация завершается, она освобождает выделенную для нее `stack` память.

  * По умолчанию в `PHP-7.1` и `PHP-7.0` назначается `256K` стека памяти
  * Можно вызвать `Co::set(['stack_size' => 4096])` для изменения默认ного размера стека памяти



### defer()

`defer` используется для освобождения ресурсов, он будет вызван **до закрытия координации** (то есть после завершения выполнения функции координации), даже если возникнет исключение, зарегистрированные `defer` будут выполнены.

!> Версия Swoole >= 4.2.9

```php
Swoole\Coroutine::defer(callable $function);
defer(callable $function); // Краткое название API
```

!> Стоит отметить, что порядок их вызова обратный (последующие первыми), то есть те, которые зарегистрированы позже, будут выполнены первыми, что соответствует правильной логике освобождения ресурсов: ресурсы, зарегистрированные позже, могут зависеть от ресурсов, зарегистрированных ранее, например, если сначала освободить ресурсы, зарегистрированные ранее, то ресурсы, зарегистрированные позже, могут быть трудно освобождены.

  * **Пример**

```php
go(function () {
    defer(function () use ($db) {
        $db->close();
    });
});
```


### exists()

Проверить, существует ли указанная координация.

```php
Swoole\Coroutine::exists(int $cid = 0): bool
```

!> Версия Swoole >= v4.3.0

  * **Пример**

```php
\Co\run(function () {
    go(function () {
        go(function () {
            Co::sleep(0.001);
            var_dump(Co::exists(Co::getPcid())); // 1: true
        });
        go(function () {
            Co::sleep(0.003);
            var_dump(Co::exists(Co::getPcid())); // 3: false
        });
        Co::sleep(0.002);
        var_dump(Co::exists(Co::getPcid())); // 2: false
    });
});
```


### getCid()

Получить уникальный `ID` текущей координации, его другое название - `getuid`, это уникальный положительный整数 в рамках процесса.

```php
Swoole\Coroutine::getCid(): int
```

* **Возвращаемое значение**

    * При успехе возвращается `ID` текущей координации
    * Если текущая среда не является контекстом координации, то возвращается `-1`
### getPcid()

Получить идентификатор родительской корутины текущего процесса.

```php
Swoole\Coroutine::getPcid([$cid]): int
```

!> Версия Swoole >= v4.3.0

* **Параметры**

    * **`int $cid`**
      * **Функция**: идентификатор корутины cid, параметр по умолчанию, может быть передан идентификатором какой-либо корутины для получения её родительского `id`
      * **По умолчанию**: текущая корутина
      * **Другие значения**: нет

  * **Пример**

```php
var_dump(Co::getPcid());
\Co\run(function () {
    var_dump(Co::getPcid());
    go(function () {
        var_dump(Co::getPcid());
        go(function () {
            var_dump(Co::getPcid());
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
        });
        var_dump(Co::getPcid());
    });
    var_dump(Co::getPcid());
});
var_dump(Co::getPcid());

// --Ожидаемый результат--

// bool(false)
// int(-1)
// int(1)
// int(2)
// int(3)
// int(3)
// int(3)
// int(1)
// int(-1)
// bool(false)
```

!> Вызов `getPcid` не вложенной корутиной вернет `-1` (созданной из не корутинного пространства)  
Вызов `getPcid` внутри не корутинного пространства вернет `false` (нет родительской корутины)  
`0` используется как резервный `id`, и не появится в возвращаемом значении

!> Корутины между собой не имеют фактической непрерывной иерархии родитель-дочерний, корутины изолированы друг от друга и работают независимо, этот `Pcid` можно понимать как идентификатор корутины, которая создала текущую корутину

  * **Применение**

    * **Соединение нескольких стеков вызовов корутин**

```php
\Co\run(function () {
    go(function () {
        $ptrace = Co::getBackTrace(Co::getPcid());
        // balababala
        var_dump(array_merge($ptrace, Co::getBackTrace(Co::getCid())));
    });
});
```


### getContext()

Получить объект контекста текущей корутины.

```php
Swoole\Coroutine::getContext([int $cid = 0]): Swoole\Coroutine\Context
```

!> Версия Swoole >= v4.3.0

* **Параметры**

    * **`int $cid`**
      * **Функция**: корутина `CID`, опциональный параметр
      * **По умолчанию**: текущая корутина `CID`
      * **Другие значения**: нет

  * **Функции**

    * После завершения корутины контекст автоматически очищается (если нет других корутин или глобальных переменных)
    * Нет开销 от регистрации и вызова `defer` (не нужно регистрировать методы очистки, не нужно вызывать функции для очистки)
    * Нет开销 от вычисления хэша контекста, реализованного на основе PHP-массивов (имеет преимущества при большом количестве корутин)
    * `Co\Context` использует `ArrayObject`, удовлетворяет различным потребностям хранения (это объект, но также можно работать с ним как с массивом)

  * **Пример**

```php
function func(callable $fn, ...$args)
{
    go(function () use ($fn, $args) {
        $fn(...$args);
        echo 'Coroutine#' . Co::getCid() . ' exit' . PHP_EOL;
    });
}

/**
* Compatibility for lower version
* @param object|Resource $object
* @return int
*/
function php_object_id($object)
{
    static $id = 0;
    static $map = [];
    $hash = spl_object_hash($object);
    return $map[$hash] ?? ($map[$hash] = ++$id);
}

class Resource
{
    public function __construct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' constructed' . PHP_EOL;
    }

    public function __destruct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' destructed' . PHP_EOL;
    }
}

$context = new Co\Context();
assert($context instanceof ArrayObject);
assert(Co::getContext() === null);
func(function () {
    $context = Co::getContext();
    assert($context instanceof Co\Context);
    $context['resource1'] = new Resource;
    $context->resource2 = new Resource;
    func(function () {
        Co::getContext()['resource3'] = new Resource;
        Co::yield();
        Co::getContext()['resource3']->resource4 = new Resource;
        Co::getContext()->resource5 = new Resource;
    });
});
Co::resume(2);

Swoole\Event::wait();

// --Ожидаемый результат--
// Resource#1 constructed
// Resource#2 constructed
// Resource#3 constructed
// Coroutine#1 exit
// Resource#2 destructed
// Resource#1 destructed
// Resource#4 constructed
// Resource#5 constructed
// Coroutine#2 exit
// Resource#5 destructed
// Resource#3 destructed
// Resource#4 destructed
```


### yield()

Вручную уступить право выполнения текущей корутины. Вместо [корутинного расписания](/coroutine?id=корутинное-расписание), основанного на IO.

Эта метод имеет другое имя: `Coroutine::suspend()`.

!> Необходимо использовать в паре с методом `Coroutine::resume()`. После того как корутина `yield`, её необходимо возобновить другой внешней корутиной, иначе произойдёт утечка корутин, и замороженная корутина никогда не будет выполнена.

```php
Swoole\Coroutine::yield();
```

  * **Пример**

```php
$cid = go(function () {
    echo "co 1 start\n";
    Co::yield();
    echo "co 1 end\n";
});

go(function () use ($cid) {
    echo "co 2 start\n";
    Co::sleep(0.5);
    Co::resume($cid);
    echo "co 2 end\n";
});
Swoole\Event::wait();
```


### resume()

Вручную возобновить определенную корутину, чтобы она продолжила работу, не основанное на IO [корутинное расписание](/coroutine?id=корутинное-расписание).

!> Когда текущая корутина находится в состоянии приостановки, другая корутина может использовать `resume`, чтобы снова разбудить текущую корутину.

```php
Swoole\Coroutine::resume(int $coroutineId);
```

* **Параметры**

    * **`int $coroutineId`**
      * **Функция**: идентификатор корутины `$coroutineId`, который необходимо возобновить
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Пример**

```php
$id = go(function(){
    $id = Co::getuid();
    echo "start coro $id\n";
    Co::suspend();
    echo "resume coro $id @1\n";
    Co::suspend();
    echo "resume coro $id @2\n";
});
echo "start to resume $id @1\n";
Co::resume($id);
echo "start to resume $id @2\n";
Co::resume($id);
echo "main\n";
Swoole\Event::wait();

// --Ожидаемый результат--
// start coro 1
// start to resume 1 @1
// resume coro 1 @1
// start to resume 1 @2
// resume coro 1 @2
// main
```


### list()

Пропустить через все корутины в текущем процессе.

```php
Swoole\Coroutine::list(): Swoole\Coroutine\Iterator
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> В версиях ниже `v4.3.0` необходимо использовать `listCoroutines`, в новых версиях метод был сокращён до `list` и `listCoroutines` стал его синонимом. Метод `list` стал доступен с версии `v4.1.0`.

* **Возвращаемое значение**

    * Возвращает итератор, который можно использовать для итерации с помощью `foreach`, или превратить в массив с помощью `iterator_to_array`

```php
$coros = Swoole\Coroutine::listCoroutines();
foreach($coros as $cid)
{
    var_dump(Swoole\Coroutine::getBackTrace($cid));
}
```


### stats()

Получить статистику корутин.

```php
Swoole\Coroutine::stats(): array
```

* **Возвращаемое значение**


Ключ | Функция
---|---
event_num | Количество событий в текущем реакторе
signal_listener_num | Количество слушателей сигналов
aio_task_num | Количество задач异步 IO (здесь aio относится к 文件IO или dns, не включает другие сетевые IO, далее то же самое)
aio_worker_num | Количество рабочих线程 для异步 IO
c_stack_size | Размер C стека каждой корутины
coroutine_num | Количество текущих chạyных корутин
coroutine_peak_num | Максимальное количество текущих chạyных корутин
coroutine_last_cid | Последний созданный идентификатор корутины

  * **Пример**

```php
var_dump(Swoole\Coroutine::stats());

array(1) {
  ["c_stack_size"]=>
  int(2097152)
  ["coroutine_num"]=>
  int(132)
  ["coroutine_peak_num"]=>
  int(2)
}
```
### getBackTrace()

Получить стек вызовов корутины.

```php
Swoole\Coroutine::getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
```

!> Версия Swoole >= v4.1.0

* **Параметры**

    * **`int $cid`**
      * **Функция**: `CID` корутины
      * **По умолчанию**: текущий `CID` корутины
      * **Другие значения**: нет

    * **`int $options`**
      * **Функция**: Установить опции
      * **По умолчанию**: `DEBUG_BACKTRACE_PROVIDE_OBJECT` 【Флаг, указывающий на заполнение индекса `object`】
      * **Другие значения**: `DEBUG_BACKTRACE_IGNORE_ARGS` 【Флаг, указывающий на игнорирование индексов `args`, включая все параметры функций/методов, что может сэкономить память】

    * **`int limit`**
      * **Функция**: Ограничить количество стека вызовов
      * **По умолчанию**: `0`
      * **Другие значения**: нет

* **Возвращаемое значение**

    * Если указанная корутина не существует, будет возвращено `false`
    * Успешное возвращение массива, формат которого такой же, как у функции [debug_backtrace](https://www.php.net/manual/zh/function.debug-backtrace.php)

  * **Пример**

```php
function test1() {
    test2();
}

function test2() {
    while(true) {
        Co::sleep(10);
        echo __FUNCTION__." \n";
    }
}
\Co\run(function () {
    $cid = go(function () {
        test1();
    });

    go(function () use ($cid) {
        while(true) {
            echo "BackTrace[$cid]:\n-----------------------------------------------\n";
            //возвращаем массив, который необходимо самостоятельно форматировать для вывода
            var_dump(Co::getBackTrace($cid))."\n";
            Co::sleep(3);
        }
    });
});
Swoole\Event::wait();
```


### printBackTrace()

Вывести стек вызовов корутины. Параметры такие же, как у `getBackTrace`.

!> Версия Swoole >= `v4.6.0` доступна

```php
Swoole\Coroutine::printBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0);
```


### getElapsed()

Получить время выполнения корутины для анализа статистики или выявления зомби-корутин

!> Версия Swoole >= `v4.5.0` доступна

```php
Swoole\Coroutine::getElapsed([$cid]): int
```
* **Параметры**

    * **`int $cid`**
      * **Функция**: По выбору параметр, `CID` корутины
      * **По умолчанию**: текущий `CID` корутины
      * **Другие значения**: нет

* **Возвращаемое значение**

    * Длительность выполнения корутины в浮点数ах, точность до миллисекунд


### cancel()

Использоваться для отмены определенной корутины, но нельзя отменять текущую корутину

!> Версия Swoole >= `v4.7.0` доступна

```php
Swoole\Coroutine::cancel($cid): bool
```
* **Параметры**

    * **`int $cid`**
        * **Функция**: `CID` корутины
        * **По умолчанию**: нет
        * **Другие значения**: нет

* **Возвращаемое значение**

    * Успешное возвращение `true`, неудача вернет `false`
    * Для изучения ошибки после неудачи отмены можно вызвать [swoole_last_error()](/functions?id=swoole_last_error)


### isCanceled()

Проверить, была ли текущая операция manually отменена

!> Версия Swoole >= `v4.7.0` доступна

```php
Swoole\Coroutine::isCanceled(): bool
```

* **Возвращаемое значение**

    * Если手动 отменка успешно завершилась, вернет `true`, в случае неудачи вернет `false`

#### Пример

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

run(function () {
    $chan = new Coroutine\Channel(1);
    $cid = Coroutine::getCid();
    go(function () use ($cid) {
        System::sleep(0.002);
        assert(Coroutine::cancel($cid) === true);
    });

    assert($chan->push("hello world [1]", 100) === true);
    assert(Coroutine::isCanceled() === false);
    assert($chan->errCode === SWOOLE_CHANNEL_OK);

    assert($chan->push("hello world [2]", 100) === false);
    assert(Coroutine::isCanceled() === true);
    assert($chan->errCode === SWOOLE_CHANNEL_CANCELED);

    echo "Done\n";
});
```


### enableScheduler()

Временное включение планировщика抢占式 выполнения корутин.

!> Версия Swoole >= `v4.4.0` доступна

```php
Swoole\Coroutine::enableScheduler();
```


### disableScheduler()

Временное отключение планировщика抢占式 выполнения корутин.

!> Версия Swoole >= `v4.4.0` доступна

```php
Swoole\Coroutine::disableScheduler();
```


### getStackUsage()

Получить потребление памяти текущего PHP стека.

!> Версия Swoole >= `v4.8.0` доступна

```php
Swoole\Coroutine::getStackUsage([$cid]): int
```

* **Параметры**

    * **`int $cid`**
        * **Функция**: По выбору параметр, `CID` корутины
        * **По умолчанию**: текущий `CID` корутины
        * **Другие значения**: нет


### join()

Воспроизведение нескольких корутин одновременно.

!> Версия Swoole >= `v4.8.0` доступна

```php
Swoole\Coroutine::join(array $cid_array, float $timeout = -1): bool
```

* **Параметры**

    * **`array $cid_array`**
        * **Функция**: массив `CID` корутин, которые необходимо выполнить
        * **По умолчанию**: нет
        * **Другие значения**: нет

    * **`float $timeout`**
        * **Функция**: Общий лимит времени, после истечения которого функция немедленно вернет результат. Однако Currently running coroutines продолжат выполнять работу до конца и не будут прерываться
        * **По умолчанию**: -1
        * **Другие значения**: нет

* **Возвращаемое значение**

    * Успешное возвращение `true`, неудача вернет `false`
    * Для изучения ошибки после неудачи можно вызвать [swoole_last_error()](/functions?id=swoole_last_error)

* **Пример использования**

```php
use Swoole\Coroutine;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

run(function () {
    $status = Coroutine::join([
        go(function () use (&$result) {
            $result['baidu'] = strlen(file_get_contents('https://www.baidu.com/'));
        }),
        go(function () use (&$result) {
            $result['google'] = strlen(file_get_contents('https://www.google.com/'));
        })
    ], 1);
    var_dump($result, $status, swoole_strerror(swoole_last_error(), 9));
});
```


## Функции


### batch()

Воспроизведение нескольких корутин одновременно и получение результатов их методов через массив.

!> Версия Swoole >= `v4.5.2` доступна

```php
Swoole\Coroutine\batch(array $tasks, float $timeout = -1): array
```

* **Параметры**

    * **`array $tasks`**
      * **Функция**: массив функций-callbacks, которые необходимо выполнить. Если для `key` указано значение, то результаты этих функций будут关联ены с этим `key`
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`float $timeout`**
      * **Функция**: Общий лимит времени, после истечения которого функция немедленно вернет результат. Однако Currently running coroutines продолжат выполнять работу до конца и не будут прерываться
      * **По умолчанию**: -1
      * **Другие значения**: нет

* **Возвращаемое значение**

    * Возвращается массив, содержащий результаты выполнения функций-callbacks. Если для `$tasks` указано значение `key`, то результаты этих функций будут关联ены с этим `key`

* **Пример использования**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\batch;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = batch([
        'file_put_contents' => function () {
            return file_put_contents(__DIR__ . '/greeter.txt', "Hello,Swoole.");
        },
        'gethostbyname' => function () {
            return gethostbyname('localhost');
        },
        'file_get_contents' => function () {
            return file_get_contents(__DIR__ . '/greeter.txt');
        },
        'sleep' => function () {
            sleep(1);
            return true; // Возвращает `NULL` поскольку превышен установленный лимит времени в 0.1 секунды, после истечения времени функция немедленно вернет результат. Однако Currently running coroutines продолжат выполнять работу до конца и не будут прерываться.
        },
        'usleep' => function () {
            usleep(1000);
            return true;
        },
    ], 0.1);
    $use = microtime(true) - $use;
    echo "Использовано {$use} секунды, Результаты:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Использовано {$end_time} секунды, завершено\n";
```
### параллельно()

Конкурентно выполнять несколько корутинов.

!> Версия Swoole >= `v4.5.3` доступна

```php
Swoole\Coroutine\parallely(int $n, callable $fn): void
```

* **Параметры**

    * **`int $n`**
      * **Функция**: Установить максимальное количество корутинов в `$n`
      * **По умолчанию**: Нет
      * **Другое значение**: Нет

    * **`callable $fn`**
      * **Функция**:elynktива, которую необходимо выполнить для каждого элемента
      * **По умолчанию**: Нет
      * **Другое значение**: Нет

* **Пример использования**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\parallely;

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = [];
    parallely(2, function () use (&$results) {
        System::sleep(0.2);
        $results[] = System::gethostbyname('localhost');
    });
    $use = microtime(true) - $use;
    echo "Использовано {$use}c, Результаты:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Использовано {$end_time}c, Готово\n";
```

### map()

Подобно [array_map](https://www.php.net/manual/zh/function.array-map.php), применятьelynктиву ко всем элементам массива.

!> Версия Swoole >= `v4.5.5` доступна

```php
Swoole\Coroutine\map(array $list, callable $fn, float $timeout = -1): array
```

* **Параметры**

    * **`array $list`**
      * **Функция**: Массив, на котором будет работать функция `$fn`
      * **По умолчанию**: Нет
      * **Другое значение**: Нет

    * **`callable $fn`**
      * **Функция**:elynктива, которую необходимо выполнить для каждого элемента массива `$list`
      * **По умолчанию**: Нет
      * **Другое значение**: Нет

    * **`float $timeout`**
      * **Функция**: Общий тайм-out, после истечения которого функция будет немедленно возвращаться. Однако уже запущенные协程 будут продолжать работу до конца и не будут прерваны
      * **По умолчанию**: -1
      * **Другое значение**: Нет

* **Пример использования**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\map;

function факториал(int $n): int
{
    return array_product(range($n, 1));
}

Coroutine\run(function () {
    $results = map([2, 3, 4], 'факториал'); 
    print_r($results);
});
```

### deadlock_check()

Проверка заDeadlock корутинов, при вызове будет выведена информация о стеке;

По умолчанию **включено**, после завершения [EventLoop](learn?id=что-это-eventloop), если существуют Deadlock корутинов, они будут автоматически обнаружены в базовом слое;

Можно禁用 проверку через [Coroutine::set](/coroutine/coroutine?id=set) путем установки `enable_deadlock_check` к `false`.

!> Версия Swoole >= `v4.6.0` доступна

```php
Swoole\Coroutine\deadlock_check();
```
