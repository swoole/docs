# Coroutine\Barrier

The underlying [Swoole Library](https://github.com/swoole/library) provides a more convenient coroutine concurrency management tool: `Coroutine\Barrier`, also known as coroutine barrier. It is implemented based on PHP reference counting and Coroutine API.

Compared to [Coroutine\WaitGroup](/coroutine/wait_group), `Coroutine\Barrier` is easier to use. You can simply introduce it to the sub-coroutine function through parameter passing or the `use` syntax of closures.

!> Available when Swoole version is >= v4.5.5.

## Usage Example

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

## Execution Flow

* First, create a new coroutine barrier using `Barrier::make()`.
* Pass the barrier using the `use` syntax in sub-coroutines to increase the reference count.
* Add `Barrier::wait($barrier)` at the position where you need to wait. This will automatically suspend the current coroutine, waiting for the sub-coroutines that reference this coroutine barrier to exit.
* When a sub-coroutine exits, it decreases the reference count of the `$barrier` object until it reaches `0`.
* When all sub-coroutines have completed their tasks and exited, the reference count of the `$barrier` object becomes `0`, and the `Coroutine\Barrier` will automatically resume the suspended coroutine from the `Barrier::wait($barrier)` function.

`Coroutine\Barrier` is a more user-friendly concurrency controller compared to [WaitGroup](/coroutine/wait_group) and [Channel](/coroutine/channel), greatly improving the user experience of concurrent programming in PHP.
