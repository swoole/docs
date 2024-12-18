# Корoutine\Barrier

В **[Swoole Library](https://github.com/swoole/library)** на основе提供了 более удобный инструмент для управления совместным запуском Корoutine: `Coroutine\Barrier`, или корoutine-барьер. Реализован на основе PHP Reference Counting и Coroutine API.

По сравнению с **[Coroutine\WaitGroup](/coroutine/wait_group)**, `Coroutine\Barrier` проще в использовании, нужно просто передать его в качестве параметра или использовать `use`语句 в замыкании, чтобы передать функцию подкорoutine.

!> Доступно для Swoole версий >= v4.5.5.


## Примеры использования

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

## Процесс выполнения

* Сначала создается новый корoutine-барьер с помощью `Barrier::make()`
* В подкорoutine с помощью `use`语句 передать барьер, увеличивая счетчик ссылок
* В месте, где нужно ждать, добавить `Barrier::wait($barrier)`, что автоматически замораживает текущую корoutine, ожидая, пока сыновья корoutine,引用该 корoutine-барьер, выйдут
* Когда сыновья корoutine выполнят свою работу и выйдут, счетчик ссылок для объекта `$barrier` становится равным `0`
* Когда все сыновья корoutine завершат работу и выйдут, счетчик ссылок для объекта `$barrier` будет равен `0`, и в деструкторе объекта `$barrier`low-level автоматически возобновит замороженные корoutine, и функция `Barrier::wait($barrier)` вернется

`Coroutine\Barrier` - это более удобной контроллер совместного выполнения, чем [WaitGroup](/coroutine/wait_group) и [Channel](/coroutine/channel), значительно улучшая пользовательский опыт PHP-программирования совместных задач.
