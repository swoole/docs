# 프로세스\관리자

프로세스 관리자, [프로세스\ 풀](/process/process_pool)을 기반으로 구현되었습니다. 여러 프로세스를 관리할 수 있습니다. `프로세스\풀`와 비교할 때, 다양한 작업을 수행하는 여러 프로세스를 매우 쉽게 만들 수 있으며, 각 프로세스가 코루틴 환경에 있는지를 제어할 수 있습니다.


## 버전 지원 상황

| 버전 번호 | 클래스명                          | 업데이트 설명                                 |
| ------ | ----------------------------- | ---------------------------------------- |
| v4.5.3 | Swoole\Process\ProcessManager | -                                        |
| v4.5.5 | Swoole\Process\Manager        | 개명, ProcessManager이 Manager의 별명으로 변경됨 |

!> v4.5.3 이상 버전에서 사용할 수 있습니다.


## 사용 예시

```php
use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();

for ($i = 0; $i < 2; $i++) {
    $pm->add(function (Pool $pool, int $workerId) {
    });
}

$pm->start();
```


## 방법


### __construct()

생성자입니다.

```php
Swoole\Process\Manager::__construct(int $ipcType = SWOOLE_IPC_NONE, int $msgQueueKey = 0);
```

* **매개변수**

  * **`int $ipcType`**
    * **기능** : 프로세스 간 통신의 방식, `Process\Pool`의 `$ipc_type`와 동일【기본값은 `0`로 모든 프로세스 간 통신 기능을 사용하지 않음】
    * **기본값** : `0`
    * **기타 값** : 없음

  * **`int $msgQueueKey`**
    * **기능** : 메시지 큐의 `key`, `Process\Pool`의 `$msgqueue_key`와 동일
    * **기본값** : 없음
    * **기타 값** : 없음


### setIPCType()

작업 프로세스 간의 통신 방식을 설정합니다.

```php
Swoole\Process\Manager->setIPCType(int $ipcType): self;
```

* **매개변수**

  * **`int $ipcType`**
    * **기능** : 프로세스 간 통신의 방식
    * **기본값** : 없음
    * **기타 값** : 없음


### getIPCType()

작업 프로세스 간의 통신 방식을 가져옵니다.

```php
Swoole\Process\Manager->getIPCType(): int;
```


### setMsgQueueKey()

메시지 큐의 `key`를 설정합니다.

```php
Swoole\Process\Manager->setMsgQueueKey(int $msgQueueKey): self;
```

* **매개변수**

  * **`int $msgQueueKey`**
    * **기능** : 메시지 큐의 `key`
    * **기본값** : 없음
    * **기타 값** : 없음


### getMsgQueueKey()

메시지 큐의 `key`를 가져옵니다.

```php
Swoole\Process\Manager->getMsgQueueKey(): int;
```


### add()

작업 프로세스를 하나 추가합니다.

```php
Swoole\Process\Manager->add(callable $func, bool $enableCoroutine = false): self;
```

* **매개변수**

  * **`callable $func`**
    * **기능** : 현재 프로세스가 실행하는 콜백 함수
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`bool $enableCoroutine`**
    * **기능** : 이 프로세스에 콜백 함수를 실행하기 위해 코루틴을 만들 것인지 여부
    * **기본값** : false
    * **기타 값** : 없음


### addBatch()

작업 프로세스를 일괄적으로 추가합니다.

```php
Swoole\Process\Manager->addBatch(int $workerNum, callable $func, bool $enableCoroutine = false): self
```

* **매개변수**

  * **`int $workerNum`**
    * **기능** : 일괄적으로 추가할 프로세스의 개수
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`callable $func`**
    * **기능** : 이 프로세스들이 실행하는 콜백 함수
    * **기본값** : 없음
    * **기타 값** : 없음

  * **`bool $enableCoroutine`**
    * **기능** : 이 프로세스들에게 콜백 함수를 실행하기 위해 코루틴을 만들 것인지 여부
    * **기본값** : 없음
    * **기타 값** : 없음

### start()

작업 프로세스를 시작합니다.

```php
Swoole\Process\Manager->start(): void
```
