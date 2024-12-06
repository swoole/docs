# 프로세스/스레드 간 무소켓 카운터 Atomic

`Atomic`는 `Swoole`의 기본적으로 제공하는 원자 계산 운영 클래스로, 정수의 무소켓 원자적 증가/감소가 용이합니다.

* 공유 메모리를 사용하여 다른 프로세스 간에서 카운터를 조작할 수 있습니다.
* `gcc/clang`이 제공하는 `CPU` 원자 명령을 기반으로 하므로 잠금을 추가할 필요가 없습니다.
* 서버 프로그램에서는 `Server->start` 전에 생성해야만 `Worker` 프로세스에서 사용할 수 있습니다.
* 기본적으로 `32`비트 무소속 유형을 사용하며, `64`비트 유리 정수 유형이 필요하면 `Swoole\Atomic\Long`을 사용할 수 있습니다.
* 멀티스레드 모드에서는 `Swoole\Thread\Atomic`와 `Swoole\Thread\Atomic\Long`을 사용해야 하며, 명령 공간이 다르지만 인터페이스는 `Swoole\Atomic`와 `Swoole\Atomic\Long`과 완전히 동일합니다.

!> [onReceive](/server/events?id=onreceive) 등의 콜백 함수에서 카운터를 생성하지 마십시오. 그렇지 않으면 메모리가 지속적으로 증가하여 메모리 누수가 발생할 수 있습니다.

!> `64`비트 유리 정수 원자 계산을 지원하며, 생성하려면 `new Swoole\Atomic\Long`을 사용해야 합니다. `Atomic\Long`은 `wait`와 `wakeup` 메서드를 지원하지 않습니다.


## 전체 예제

```php
$atomic = new Swoole\Atomic();

$serv = new Swoole\Server('127.0.0.1', '9501');
$serv->set([
    'worker_num' => 1,
    'log_file' => '/dev/null'
]);
$serv->on("start", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStart", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStop", function ($serv) {
    echo "shutdown\n";
});
$serv->on("Receive", function () {
    
});
$serv->start();
```


## 메서드


### __construct()

생성자입니다. 원자 계산 객체를 만듭니다.

```php
Swoole\Atomic::__construct(int $init_value = 0);
```

  * **매개변수** 

    * **`int $init_value`**
      * **기능** : 초기화할 수 있는 값을 지정합니다.
      * **기본값** : `0`
      * **기타 값** : 없음


!> -`Atomic`은 `32`비트 무소속 정수만 조작할 수 있으며, 최대 `42억`까지 지원하며, 부정수는 지원되지 않습니다;  

- `Server`에서 원자 계산기를 사용하려면 `Server->start` 전에 생성해야 합니다;  
- `Process`에서 원자 계산기를 사용하려면 `Process->start` 전에 생성해야 합니다.


### add()

카운트를 증가시킵니다.

```php
Swoole\Atomic->add(int $add_value = 1): int
```

  * **매개변수** 

    * **`int $add_value`**
      * **기능** : 증가할 값을 지정합니다. 【양수여야 함】
      * **기본값** : `1`
      * **기타 값** : 없음

  * **반환값**

    * 성공 시 결과 값을 반환합니다.

!> 원래 값과 더하면 `42억`을 넘으면 오버플로우가 발생하며, 높은 위치의 값이 버려집니다.


### sub()

카운트를 감소시킵니다.

```php
Swoole\Atomic->sub(int $sub_value = 1): int
```

  * **매개변수** 

    * **`int $sub_value`**
      * **기능** : 감소할 값을 지정합니다. 【양수여야 함】
      * **기본값** : `1`
      * **기타 값** : 없음

  * **반환값**

    * 성공 시 결과 값을 반환합니다.

!> 원래 값에서 빼면 `0`이하가 되면 오버플로우가 발생하며, 높은 위치의 값이 버려집니다.


### get()

현재 카운트의 값을 가져옵니다.

```php
Swoole\Atomic->get(): int
```

  * **반환값**

    * 현재 값을 반환합니다.


### set()

현재 값을 지정한 숫자로 설정합니다.

```php
Swoole\Atomic->set(int $value): void
```

  * **매개변수** 

    * **`int $value`**
      * **기능** : 설정할 대상 값을 지정합니다.
      * **기본값** : 없음
      * **기타 값** : 없음


### cmpset()

현재 값이 매개변수 `1`과 같다면, 현재 값을 매개변수 `2`로 설정합니다.   

```php
Swoole\Atomic->cmpset(int $cmp_value, int $set_value): bool
```

  * **매개변수** 

    * **`int $cmp_value`**
      * **기능** : 현재 값이 `$cmp_value`와 같다면 `true`를 반환하고 현재 값을 `$set_value`로 설정합니다. 다르면 `false`를 반환합니다. 【`42억` 미만의 정수여야 함】
      * **기본값** : 없음
      * **기타 값** : 없음

    * **`int $set_value`**
      * **기능** : 현재 값이 `$cmp_value`와 같다면 `true`를 반환하고 현재 값을 `$set_value`로 설정합니다. 다르면 `false`를 반환합니다. 【`42억` 미만의 정수여야 함】
      * **기본값** : 없음
      * **기타 값** : 없음


### wait()

대기 상태로 설정합니다.

!> 원자 계산의 값이 `0`이면 프로그램이 대기 상태로 진입합니다. 다른 프로세스가 `wakeup`를 호출하면 다시 깨울 수 있습니다. 기본적으로 `Linux Futex`를 기반으로 구현되어 있으며, 이 기능을 사용하면 대기, 알림, 잠금 기능을 단지 `4`바이트의 메모리만으로 실현할 수 있습니다. `Futex`가 지원되지 않는 플랫폼에서는 기본적으로 순환 `usleep(1000)`을 사용하여 모의 실현을 합니다.

```php
Swoole\Atomic->wait(float $timeout = 1.0): bool
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능** :超时 시간을 지정합니다. 【`-1`로 설정하면 영원히 대기하며, 다른 프로세스가 `wakeup`하여 깨울 때까지 계속 대기합니다】
      * **값의 단위** : 초 【소수도 지원하며, 예를 들어 `1.5`은 `1초`+`500ms`을 의미합니다】
      * **기본값** : `1`
      * **기타 값** : 없음

  * **반환값** 

    *超时 시 `false`를 반환하고, 오류 코드는 `EAGAIN`입니다. `swoole_errno` 함수를 사용하여 얻을 수 있습니다.
    * 성공 시 `true`를 반환하며, 다른 프로세스가 `wakeup`하여 현재 잠금을 성공적으로 깨웠다는 것을 나타냅니다.

  * **코어 환경**

  `wait`는 전체 프로세스를 막기 때문에 코어 환경에서는 `Atomic->wait()`를 사용하지 않는 것이 좋습니다. 프로세스가 멈추지 않도록 하기 위해서입니다.


!> - `wait/wakeup` 기능을 사용할 때, 원자 계산의 값은 `0` 또는 `1`여야 합니다. 그렇지 않으면 정상적으로 사용할 수 없습니다;  
- 물론 원자 계산의 값이 `1`인 경우, 자원이 현재 사용 가능하다는 것을 나타내며, `wait` 함수는 즉시 `true`를 반환합니다.

  * **사용 예제**

    ```php
    $n = new Swoole\Atomic;
    if (pcntl_fork() > 0) {
        echo "master start\n";
        $n->wait(1.5);
        echo "master end\n";
    } else {
        echo "child start\n";
        sleep(1);
        $n->wakeup();
        echo "child end\n";
    }
    ```

### wakeup()

대기 상태인 다른 프로세스를 깨웁니다.

```php
Swoole\Atomic->wakeup(int $n = 1): bool
```

  * **매개변수** 

    * **`int $n`**
      * **기능** : 깨울 프로세스의 수를 지정합니다.
      * **기본값** : 없음
      * **기타 값** : 없음

* 현재 원자 계산이 `0`인 경우, 프로세스가 대기하고 있지 않음을 나타내며, `wakeup`는 즉시 `true`를 반환합니다;
* 현재 원자 계산이 `1`인 경우, 현재 프로세스가 대기하고 있음을 나타내며, `wakeup`는 대기 중인 프로세스를 깨우고 `true`를 반환합니다;
* 깨어난 프로세스가 반환되면, 원자 계산을 `0`로 설정합니다. 이때 다른 대기 중인 프로세스를 위해 `wakeup`를 다시 호출할 수 있습니다.
