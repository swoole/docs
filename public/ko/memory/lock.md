# 프로세스/스레드 간 잠금 Lock

* `PHP`코드에서는 편리하게 잠금 `Swoole\Lock`를 생성하여 데이터 동기화를 구현할 수 있습니다. `Lock` 클래스는 `5`가지 유형의 잠금을 지원합니다.
* 멀티스레드 모드에서는 `Swoole\Thread\Lock`를 사용해야 하며, 네임스페이스가 다르지만 인터페이스는 `Swoole\Lock`와 완전히 동일합니다.


잠금 유형 | 설명
---|---
SWOOLE_MUTEX | 상호 배타적 잠금
SWOOLE_RWLOCK | 읽기-쓰기 잠금
SWOOLE_SPINLOCK | 스핀락 잠금
SWOOLE_FILELOCK | 파일 잠금(비용제)
SWOOLE_SEM | 신호량(비용제)

!> [onReceive](/server/events?id=onreceive) 등의 콜백 함수에서 잠금을 생성하지 마십시오. 그렇지 않으면 메모리가 지속적으로 증가하여 메모리 누수로 이어질 수 있습니다.


## 사용 예시

```php
$lock = new Swoole\Lock(SWOOLE_MUTEX);
echo "[Master]create lock\n";
$lock->lock();
if (pcntl_fork() > 0)
{
  sleep(1);
  $lock->unlock();
} 
else
{
  echo "[Child] Wait Lock\n";
  $lock->lock();
  echo "[Child] Get Lock\n";
  $lock->unlock();
  exit("[Child] exit\n");
}
echo "[Master]release lock\n";
unset($lock);
sleep(1);
echo "[Master]exit\n";
```


## 경고

!> 코로outine에서는 잠금을 사용할 수 없으니 신중하게 사용하고, `lock`와 `unlock` 작업 사이에서 코로outine 전환을 유발할 수 있는 `API`를 사용하지 마십시오.


### 잘못된 예시

!> 이 코드는 코로outine 모드에서 `100%` 데드락합니다.

```php
$lock = new Swoole\Lock();
$c = 2;

while ($c--) {
  go(function () use ($lock) {
      $lock->lock();
      Co::sleep(1);
      $lock->unlock();
  });
}
```


## 방법


### __construct()

생성자입니다.

```php
Swoole\Lock::__construct(int $type = SWOOLE_MUTEX, string $lockfile = '');
```

!> 잠금 객체를 순환적으로 생성/소멸하지 마십시오. 그렇지 않으면 메모리 누수로 이어질 수 있습니다.

  * **매개변수** 

    * **`int $type`**
      * **기능**：잠금의 유형
      * **기본값**：`SWOOLE_MUTEX`【상호 배타적 잠금】
      * **기타 값**：없음

    * **`string $lockfile`**
      * **기능**：특정 파일 잠금의 경로 지정【`SWOOLE_FILELOCK` 유형일 때 반드시 전달해야 함】
      * **기본값**：없음
      * **기타 값**：없음

!> 각 유형의 잠금이 지원하는 방법은 다릅니다. 예를 들어 읽기-쓰기 잠금, 파일 잠금은 `$lock->lock_read()`를 지원할 수 있습니다. 또한 파일 잠금을 제외한 다른 유형의 잠금은 부모 프로세스 내에서 생성해야 하며, 이렇게 `fork`된 자식 프로세스끼리 잠금을 경쟁할 수 있습니다.


### lock()

잠금 추가 작업입니다. 다른 프로세스가 잠금을 가지고 있는 경우, 여기서는 차단되어 잠금을 가진 프로세스가 `unlock()`을 해제할 때까지 기다립니다.

```php
Swoole\Lock->lock(): bool
```


### trylock()

잠금 추가 작업입니다. `lock` 방법과 달리, `trylock()`는 차단되지 않고 즉시 반환합니다.

```php
Swoole\Lock->trylock(): bool
```

  * **반환값**

    * 잠금을 성공적으로 추가하면 `true`을 반환하며, 이때는 공유 변수를 수정할 수 있습니다
    * 잠금을 추가하지 못하면 `false`을 반환하며, 이는 다른 프로세스가 잠금을 가지고 있다는 것을 나타냅니다

!> `SWOOlE_SEM` 신호량은 `trylock` 방법이 없습니다


### unlock()

잠금 해제 작업입니다.

```php
Swoole\Lock->unlock(): bool
```


### lock_read()

독점적이지 않은 잠금 추가 작업입니다.

```php
Swoole\Lock->lock_read(): bool
```

* 독점적이지 않은 잠금을 가진 동안, 다른 프로세스는 여전히 독점적이지 않은 잠금을 얻을 수 있으며, 읽기 작업을 계속할 수 있습니다;
* 하지만 `$lock->lock()` 또는 `$lock->trylock()`를 사용할 수 없습니다. 이 두 방법은 독점적 잠금을 얻으려는 것이며, 독점적 잠금을 가질 때 다른 프로세스는 어떠한 잠금 추가 작업도 할 수 없으며, 독점적이지 않은 잠금도 마찬가지입니다;
* 또 다른 프로세스가 독점적 잠금을 얻었을 때( `$lock->lock()`/`$lock->trylock()`를 호출했을 때), `$lock->lock_read()`는 차단되어 독점적 잠금을 가진 프로세스가 잠금을 해제할 때까지 기다립니다.

!> `SWOOLE_RWLOCK`와 `SWOOLE_FILELOCK` 유형의 잠금만이 독점적이지 않은 잠금을 지원합니다


### trylock_read()

잠금 추가 작업입니다. 이 방법은 `$lock_read()`와 동일하지만 비차단적입니다.

```php
Swoole\Lock->trylock_read(): bool
```

!> 호출은 즉시 반환되며, 반환값을 확인하여 잠금을 얻었는지 확인해야 합니다.

### lockwait()

잠금 추가 작업입니다. `lock()` 방법과 동일한 기능을 수행하지만, `lockwait()`는超时 시간을 설정할 수 있습니다.

```php
Swoole\Lock->lockwait(float $timeout = 1.0): bool
```

  * **매개변수** 

    * **`float $timeout`**
      * **기능**：超时 시간 설정
      * **값 단위**：초【소수형을 지원합니다. 예를 들어 `1.5`은 `1초`+`500밀리초`를 나타냅니다】
      * **기본값**：`1`
      * **기타 값**：없음

  * **반환값**

    * 설정된 시간 내에 잠금을 얻지 못하면 `false`을 반환합니다
    * 잠금을 성공적으로 추가하면 `true`을 반환합니다

!> `Mutex` 유형의 잠금만이 `lockwait`를 지원합니다
