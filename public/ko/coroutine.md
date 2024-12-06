# 코루틴 <!-- {docsify-ignore-all} -->

이 섹션에서는 코루틴의 기본 개념과 자주 묻는 질문에 대해 소개합니다.

Swoole 4.0 버전부터는 '코루틴(Coroutine)' + '채널(Channel)' 기능이 완전히 제공되어 새로운 CSP 프로그래밍 모델이 도입되었습니다.

1. 개발자는 동기적인 코딩 방식으로 [비동기 I/O](/learn?id=同步io异步io)의 효과와 성능을 느끼면서도, 전통적인 비동기 콜백이 가져다주는 분산된 코드 논리와 다층 콜백에 빠져 코드를 유지하기 어려운 문제를 피할 수 있습니다.
2. 또한, 코루틴이 기본적으로 포장되어 있어서 전통적인 PHP 계층의 코루틴 프레임워크에 비해, 개발자는 [yield](https://www.php.net/manual/zh/language.generators.syntax.php) 키워드를 사용하여 코루틴의 'I/O' 작업을 식별할 필요가 없게 되어, yield의 의미에 대해 깊이 이해할 필요가 없고, 각 수준의 호출을 모두 yield로 수정할 필요가 없게 되어, 이는 개발 효율성을 크게 향상시킵니다.
3. 다양한 유형의 완벽한 [코루틴 클라이언트](/coroutine_client/init)가 제공되어 대부분의 개발자의 요구를 충족시킵니다.

## 코루틴이란 무엇인가요?

코루틴은 간단히 스레드로 이해할 수 있으며, 이 스레드는 사용자 공간에서 이루어지며 운영체계의 참여가 필요 없어 창출, 소멸 및 전환 비용이 매우 낮습니다. 스레드와 달리 코루틴은 멀티코어 CPU를 이용할 수 없습니다. 멀티코어 CPU를 이용하려면 Swoole의 멀티 프로세스 모델에 의존해야 합니다.

## 채널이란 무엇인가요?

`Channel`는 메시징 쿼드로 이해할 수 있으며, 이는 코루틴 간의 메시징 쿼드입니다. 여러 코루틴이 `push`와 `pop` 연산을 통해 대기 중인 생산 메시지와 소비 메시지를 처리하여 코루틴 간에 데이터를 전송하거나 수신하는 데 사용됩니다. 주의해야 할 점은 `Channel`은 프로세스 간을 넘나들 수 없으며, 오직 Swoole 프로세스 내의 코루틴 간에만 통신할 수 있다는 것입니다. 가장 전형적인 응용은 [커넥션 풀](/coroutine/conn_pool)과 [병렬 호출](/coroutine/multi_call)입니다.

## 코루틴 컨테이너란 무엇인가요?

`Coroutine::create` 또는 `go()` 메서드를 사용하여 코루틴을 생성하면(참조 [별명 섹션](/other/alias?id=协程短名称)), 생성된 코루틴에서만 코루틴 `API`를 사용할 수 있습니다. 그리고 코루틴은 반드시 코루틴 컨테이너 안에서 생성되어야 합니다. 참조 [코루틴 컨테이너](/coroutine/scheduler).

## 코루틴 스케줄러

여기서 코루틴 스케줄러에 대해 비교적 쉽게 설명하겠습니다. 먼저 각 코루틴은 간단히 스레드로 이해할 수 있습니다. 모두가 다중스레딩이 프로그램의 병렬성을 향상시키기 위한 것이라는 것을 알고 있을 것입니다. 마찬가지로 다중코루틴도 병렬성을 향상시키기 위한 것입니다.

유저의 각 요청은 코루틴을 하나 생성하며, 요청이 종료되면 코루틴도 종료됩니다. 만약 동시에 수천 수만 개의 병렬 요청이 있다면, 어느 순간 어떤 프로세스 내부에는 수천 수만 개의 코루틴이 존재할 것입니다. 그렇다면 CPU 자원은 한정되어 있습니다. 어떤 코루틴의 코드를 실행해야 할까요?

CPU가 어떤 코루틴의 코드를 실행해야 하는지를 결정하는 결정 과정이 바로 `코루틴 스케줄러`입니다. 그렇다면 Swoole의 스케줄러 전략은 어떤 것일까요?

- 우선, 어떤 코루틴 코드 실행 중에 이 코드가 `Co::sleep()`를 만나거나 네트워크 `I/O`가 발생하는 것을 발견하면, 예를 들어 `MySQL->query()`와 같은 것은 분명히 시간이 소모되는 과정입니다. Swoole는 이 MySQL 연결의 Fd를 [EventLoop](/learn?id=什么是eventloop)에 넣습니다.
      
    * 그리고 이 코루틴의 CPU를 다른 코루틴에게 내주게 합니다: **즉 `yield`(일시정지)**
    * MySQL 데이터가 돌아오면 이 코루틴을 다시 실행하게 합니다: **즉 `resume`(상기)**


- 또한, 코루틴의 코드에 CPU 집약적인 코드가 있다면, [enable_preemptive_scheduler](/other/config)를 활성화할 수 있습니다. Swoole는 이 코루틴을 강제로 CPU를 내줍니다.


## 부모 코루틴 우선 순위

자식 코루틴(즉 `go()` 안의 논리)을 우선 실행하고, 코루틴 `yield`(Co::sleep() 处)이 발생할 때까지 기다린 다음 [코루틴 스케줄러](/coroutine?id=协程调度)로 외부 코루틴으로 이동합니다.

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
  

## 주의 사항

Swoole 프로그래밍을 하기 전에 주의해야 할 사항:


### 글로벌 변수

코루틴은 기존의 비동기 논리를 동기화시킵니다. 그러나 코루틴 간의 전환은 암시적으로 일어나므로, 코루틴 전환 전후에는 글로벌 변수 및 `static` 변수의 일관성이 보장되지 않습니다.

PHP-FPM에서 글로벌 변수로 요청 매개변수, 서버 매개변수 등을 가져올 수 있지만, Swoole 내에서는 `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER` 등 `_`로 시작하는 변수로는 어떠한 속성 매개변수를 가져올 수 없습니다.

[context](/coroutine/coroutine?id=getcontext)를 사용하여 코루틴 ID로 격리하고, 글로벌 변수의 격리를 실현할 수 있습니다.

### 다중 코루틴이 공유하는 TCP 연결

[참조](/question/use?id=client-has-already-been-bound-to-another-coroutine)
