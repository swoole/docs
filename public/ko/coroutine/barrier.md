# 코루틴/배리어

[Swoole Library](https://github.com/swoole/library)에서 제공하는 보다 편리한 코루틴 병렬 관리 도구인 `Coroutine\Barrier` 코루틴 배리어를 소개합니다. 이는 코루틴 배리어로도 불립니다. 이는 PHP의 참조 카운팅 및 코루틴 API를 기반으로 구현되었습니다.

[Coroutine\WaitGroup](/coroutine/wait_group)와 비교할 때, `Coroutine\Barrier`는 사용이 간단합니다. 매개변수 전달이나 클로저의 `use` 문법을 통해 자코루틴 함수를 인스턴스화하면 됩니다.

!> Swoole 버전 >= v4.5.5에서 사용할 수 있습니다.

## 사용 예제

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

## 실행 흐름

* 먼저 `Barrier::make()`를 사용하여 새로운 코루틴 배리어를 생성합니다.
* 자코루틴에서는 `use` 문법을 통해 배리어를 전달하고 참조 카운팅을 증가시킵니다.
* 기다릴 필요가 있는 위치에서 `Barrier::wait($barrier)`를 호출하면, 현재 코루틴이 자동으로 정지하고 해당 배리어를 참조하는 자코루틴이 종료될 때까지 기다립니다.
* 자코루틴이 종료될 때마다 `$barrier` 객체의 참조 카운팅이 감소하며, 0이 되면 모든 자코루틴이 작업을 완료하고 종료합니다.
* `$barrier` 객체의 참조 카운팅이 0이 되면, `$barrier` 객체의 디스펄션 메서드에서 자동으로 정지된 코루틴을 복구하고, `Barrier::wait($barrier)` 함수에서 반환됩니다.

`Coroutine\Barrier`는 [WaitGroup](/coroutine/wait_group)와 [Channel](/coroutine/channel)보다 사용하기 쉬운 병렬 제어기로, PHP 병렬 프로그래밍의 사용자 경험을 크게 향상시킵니다.
