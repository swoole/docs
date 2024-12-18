# Пул线程

Пул线程 может поддерживать работу нескольких рабочих线程, автоматически создавая, перезапуская и закрывая дочерние线程.


## Методы


### __construct()

Конструктор.

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **Параметры** 
  * `string $workerThreadClass`: Класс рабочих线程
  * `int $worker_num`: Количество рабочих线程



### withArguments()

Установка параметров рабочих线程, которые можно получить в методе `run($args)`.

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```



### withAutoloader()

Загрузка файла `autoload`

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **Параметры** 
  * `string $autoloader`: Путь к PHP-файлу `autoload`


> Если используется `Composer`, то можно автоматически определить и载入 `vendor/autoload.php` в рабочих процессах без необходимости ручного указания


### withClassDefinitionFile()

Установка файла определения класса рабочего线程, **этот файл должен содержать только `namespace`, `use`, определение класса, не должен содержать executable code fragments**.

Класс рабочего线程 должен наследоваться от базового класса `Swoole\Thread\Runnable` и реализовать метод `run(array $args)`.

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **Параметры** 
  * `string $classFile`: Путь к PHP-файлу определения класса рабочего线程

Если класс рабочего线程 находится в пути `autoload`, его можно не указывать


### start()

Запуск всех рабочих线程

```php
Swoole\Thread\Pool::start(): void;
```



### shutdown()

Завершение работы пула线程

```php
Swoole\Thread\Pool::shutdown(): void;
```


## Пример
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```


## Thread\Runnable

Класс рабочего线程 должен наследоваться от этого класса.


### run(array $args)

Этот метод должен быть переписан, `$args` - это параметры, переданные объекту пула线程 с помощью метода `withArguments()`.


### shutdown()

Завершение работы пула线程


### $id 
Номер текущего потока, диапазон от `0` до `(общее количество потоков - 1)`. Когда поток перезапускается, новый преемник потока имеет тот же номер, что и старый поток.


### Пример

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
