# 스레드 풀

스레드 풀은 여러 작업 스레드를 유지하며 자동으로 자식 스레드를 생성, 재시작, 종료합니다.


## 방법


### __construct()

생성자입니다.

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **매개변수** 
  * `string $workerThreadClass` : 작업 스레드가 실행하는 클래스
  * `int $worker_num` : 작업 스레드의 수를 지정합니다.



### withArguments()

작업 스레드에 매개변수를 설정합니다. `run($args)` 메서드에서 이 매개변수를 사용할 수 있습니다.

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```



### withAutoloader()

`autoload` 파일을 로딩합니다.

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **매개변수** 
  * `string $autoloader` : `autoload`의 `PHP` 파일 경로


> `Composer`를 사용하면 기본적으로 작업 프로세스에서 자동으로 `vendor/autoload.php`를 로딩할 수 있으므로 직접 지정할 필요가 없습니다.


### withClassDefinitionFile()

작업 스레드 클래스의 정의 파일을 설정합니다. **해당 파일은 `namespace`, `use`, `class 정의` 코드만 포함할 수 있으며, 실행 가능한 코드 조각은 포함해서는 안 됩니다.**

작업 스레드 클래스는 `Swoole\Thread\Runnable` 베이스 클래스를 상속해야 하며, `run(array $args)` 메서드를 구현해야 합니다.

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **매개변수** 
  * `string $classFile` : 작업 스레드 클래스의 `PHP` 파일 경로

작업 스레드 클래스가 `autoload` 경로에 있을 경우에는 설정할 필요가 없습니다.


### start()

모든 작업 스레드를 시작합니다.

```php
Swoole\Thread\Pool::start(): void;
```



### shutdown()

스레드 풀을 종료합니다.

```php
Swoole\Thread\Pool::shutdown(): void;
```


## 예제
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```


## Thread\Runnable

작업 스레드 클래스는 이 클래스를 상속해야 합니다.


### run(array $args)

이 메서드는 반드시 재정의해야 합니다. `$args`는 스레드 풀 객체가 `withArguments()` 메서드를 통해 전달한 매개변수입니다.


### shutdown()
스레드 풀을 종료합니다.


### $id 
현재 스레드의 번호로, 범위는 `0~(스레드 총수-1)`입니다. 스레드가 재시작될 때 새로운 후계 스레드와 이전의 스레드 번호는 동일합니다.


### 예제

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
