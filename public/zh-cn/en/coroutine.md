# Coroutine <!-- {docsify-ignore-all} -->

This section introduces some basic concepts and common issues of coroutines, which can also be viewed through the [Swoole video tutorial](https://course.swoole-cloud.com/course-video/6).

Starting from version 4.0, `Swoole` provides a complete feature set of `Coroutine` + `Channel`, bringing a brand new `CSP` programming model.

1. Developers can achieve the effect and performance of asynchronous IO with a synchronous coding style without awareness, avoiding the discrete code logic brought by traditional asynchronous callbacks and getting stuck in multiple layers of callbacks that make the code unmanageable.
2. As the coroutine is encapsulated at the bottom, compared to traditional `PHP` layer coroutine frameworks, developers do not need to use the [yield](https://www.php.net/manual/zh/language.generators.syntax.php) keyword to identify a coroutine `IO` operation, thus no longer needing to deeply understand the semantics of `yield` or modify every level of call to `yield`, greatly improving development efficiency.
3. Various types of comprehensive [coroutine clients](/coroutine_client/init) are provided to meet the needs of most developers.
## What is a coroutine

Coroutines can be simply understood as threads, except that these threads are in user space and do not require involvement from the operating system. The cost of creating, destroying, and switching coroutines is very low. Unlike threads, coroutines cannot utilize multi-core CPUs. To utilize multi-core CPUs, you need to rely on `Swoole`'s multi-process model.
## What is Channel

`Channel` can be understood as a message queue, specifically for coroutines. Multiple coroutines send and receive messages in this queue through `push` and `pop` operations, enabling communication between coroutines by exchanging data. It is important to note that a `Channel` cannot be used for inter-process communication. It is limited to communication between coroutines within a single `Swoole` process. The most typical applications include connection pooling and concurrent calls.
## What is a coroutine container

Use `Coroutine::create` or `go()` method to create a coroutine (refer to the [Aliases Section](/other/alias?id=coroutine-short-names)), the coroutine `API` can only be used in the created coroutine, and the coroutine must be created within a coroutine container, refer to [coroutine container](/coroutine/scheduler).
## Coroutine Scheduling

Here we will try to explain in simple terms what coroutine scheduling is. First of all, each coroutine can be understood as a thread. We know that multithreading is used to improve program concurrency, and similarly, multicoroutines are used for the same purpose.

Each user request creates a coroutine, and the coroutine ends when the request is finished. If there are thousands of concurrent requests at the same time, at one moment there may exist thousands of coroutines inside a process. Since CPU resources are limited, which coroutine's code should be executed?

The decision-making process of which coroutine's code should be executed is called `coroutine scheduling`. What is Swoole's scheduling strategy like?

- First, during the execution of a coroutine's code, if it encounters `Co::sleep()` or produces network I/O, such as `MySQL->query()`, which is definitely a time-consuming process, Swoole will place the file descriptor of this MySQL connection into the [EventLoop](/learn?id=what-is-eventloop).

    * Then, the CPU of this coroutine is yielded to other coroutines: **i.e. `yield` (suspend)**
    * It waits for MySQL data to return before continuing the execution of this coroutine: **i.e. `resume` (restore)**

- Secondly, if a coroutine's code contains CPU-intensive code, you can enable [enable_preemptive_scheduler](/other/config), and Swoole will forcefully make this coroutine yield the CPU.
## Parent-child coroutine priority

Child coroutines (i.e., the logic inside `go()`) are executed first until a coroutine `yield` occurs (at `Co::sleep()`), then the coroutine is scheduled back to the outer coroutine.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

echo "main start\n";
run(function () {
    echo "coro " . Coroutine::getcid() . " start\n";
    Coroutine::create(function () {
        echo "coro " . Coroutine::getcid() . " start\n";
        Coroutine::sleep(.2);
        echo "coro " . Coroutine::getcid() . " end\n";
    });
    echo "coro " . Coroutine::getcid() . " do not wait children coroutine\n";
    Coroutine::sleep(.1);
    echo "coro " . Coroutine::getcid() . " end\n";
});
echo "end\n";

/*
main start
coro 1 start
coro 2 start
coro 1 do not wait children coroutine
coro 1 end
coro 2 end
end
*/
```
## Precautions

Things to pay attention to before programming with Swoole:
### Global Variables

Coroutines make the original asynchronous logic synchronous, but the switching between coroutines happens implicitly, so the consistency of global variables and `static` variables cannot be guaranteed before and after coroutine switching.

In `PHP-FPM`, you can access request parameters, server parameters, etc., using global variables. However, in `Swoole`, you **cannot** access any attribute parameters using variables prefixed with `$_` like `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`.

You can use [context](/coroutine/coroutine?id=getcontext) to isolate global variables with coroutine ID, achieving isolation of global variables.
### Multiple coroutines sharing a TCP connection

[Reference](/question/use?id=client-has-already-been-bound-to-another-coroutine)
