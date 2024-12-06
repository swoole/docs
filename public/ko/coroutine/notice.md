# 코루틴 프로그래밍 참고사항

Swoole의 [코루틴](/coroutine) 기능을 사용하기 위해서는 이 장의 프로그래밍 참고사항을 주의 깊게 읽어 주십시오.

## 프로그래밍 패러다임

* 코루틴 내부에서는 전역 변수를 사용하는 것이 금지됩니다.
* 코루틴은 `use` 키워드를 사용하여 외부 변수를 현재 범위에 가져오는 것이 금지되며, 참조는 사용할 수 없습니다.
* 코루틴 간의 통신은 [채널](/coroutine/channel)을 사용해야 합니다.

!> 즉, 코루틴 간의 통신은 전역 변수나 현재 범위에 외부 변수를 참조하여서는 안 되며, 대신 `Channel`을 사용해야 합니다.

* 프로젝트에서 `zend_execute_ex` 또는 `zend_execute_internal`을 확장하여 `hook`한 경우, C 스택에 특별히 주의해야 합니다. [Co::set](/coroutine/coroutine?id=set)를 사용하여 C 스택 크기를 재설정할 수 있습니다.

!> 이 두 입구 함수를 `hook`한 후에는 대부분의 경우 평탄한 PHP 지시를 `C` 함수 호출로 변환하며, C 스택의 소모가 증가합니다.

## 코루틴 종료

Swoole의 저버전에서는 코루틴에서 `exit`를 사용하여 강제로 스크립트를 종료하면 메모리 오류로 인해 예상치 못한 결과나 `coredump`이 발생할 수 있습니다. Swoole 서비스에서 `exit`를 사용하면 전체 서비스 프로세스가 종료되고 내부의 코루틴이 모두 예외적으로 종료되어 심각한 문제가 발생합니다. Swoole는 오랫동안 개발자가 `exit`를 사용하는 것을 금지했지만, 개발자는 비정상적인 방식인 예외를 던지는 것을 사용하여, 상단의 `catch`에서 `exit`와 동일한 종료 논리를 구현할 수 있습니다.

!> v4.2.2 버전 이상에서는 (http_server가 생성되지 않은) 스크립트가 현재 코루틴만 있을 때 `exit`로 종료될 수 있습니다.

Swoole **v4.1.0** 버전 이상에서는 `코루틴`과 `서비스 이벤트 루프`에서 PHP의 `exit`를 직접 지원하며, 이때 하단은 자동으로 포착 가능한 `Swoole\ExitException`를 던집니다. 개발자는 필요한 위치에서 포착하여 원본 PHP와 동일한 종료 논리를 구현할 수 있습니다.

### Swoole\ExitException

`Swoole\ExitException`는 `Exception`를 상속하고 있으며, 두 가지新方法 `getStatus`와 `getFlags`를 추가했습니다:

```php
namespace Swoole;

class ExitException extends \Exception
{
	public function getStatus(): mixed
	public function getFlags(): int
}
```

#### getStatus()

`exit($status)`를 호출할 때 전달된 `status` 매개변수를 가져옵니다. 임의의 변수 유형을 지원합니다.

```php
public function getStatus(): mixed
```

#### getFlags()

`exit`를 호출할 때所处的 환경 정보 마스크를 가져옵니다.

```php
public function getFlags(): int
```

현재 다음과 같은 마스크가 있습니다:

| 상수 | 설명 |
| -- | -- |
| SWOOLE_EXIT_IN_COROUTINE | 코루틴에서 종료 |
| SWOOLE_EXIT_IN_SERVER | 서버에서 종료 |

### 사용 방법

#### 기본 사용

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

function route()
{
    controller();
}

function controller()
{
    your_code();
}

function your_code()
{
    Coroutine::sleep(.001);
    exit(1);
}

run(function () {
    try {
        route();
    } catch (\Swoole\ExitException $e) {
        var_dump($e->getMessage());
        var_dump($e->getStatus() === 1);
        var_dump($e->getFlags() === SWOOLE_EXIT_IN_COROUTINE);
    }
});
```

#### 상태코드를 가진 종료

```php
use function Swoole\Coroutine\run;

$exit_status = 0;
run(function () {
    try {
        exit(123);
    } catch (\Swoole\ExitException $e) {
        global $exit_status;
        $exit_status = $e->getStatus();
    }
});
var_dump($exit_status);
```

## 예외 처리

코루틴 프로그래밍에서는 직접 `try/catch`를 사용하여 예외를 처리할 수 있습니다. **단, 코루틴 내에서만 포착해야 하며, 다른 코루틴으로 예외를 포착해서는 안 됩니다.**

!> 응용 계층에서 `throw`한 `Exception`뿐만 아니라, 하단의 일부 오류도 포착할 수 있습니다. 예를 들어 `function`, `class`, `method`이 존재하지 않는 경우 등입니다.

### 잘못된 예시

아래의 코드에서 `try/catch`와 `throw`은 다른 코루틴에 있으며, 코루틴 내에서는 이 예외를 포착할 수 없습니다. 코루틴이 종료될 때 포착되지 않은 예외가 발견되면 치명적인 오류가 발생합니다.

```bash
PHP Fatal error:  Uncaught RuntimeException
```

```php
try {
	Swoole\Coroutine::create(function () {
		throw new \RuntimeException(__FILE__, __LINE__);
	});
}
catch (\Throwable $e) {
	echo $e;
}
```

### 올바른 예시

코루틴 내에서 예외를 포착합니다.

```php
function test() {
	throw new \RuntimeException(__FILE__, __LINE__);
}

Swoole\Coroutine::create(function () {
	try {
		test();
	}
	catch (\Throwable $e) {
		echo $e;
	}
});
```

## `__get / __set` 마법 메서드에서 코루틴 전환을 생성하지 마십시오.

이유: [PHP7 커널 분석 참고](https://github.com/pangudashu/php7-internal/blob/40645cfe087b373c80738881911ae3b178818f11/3/zend_object.md)

> **Note:** 클래스에 `__get()` 메서드가 존재하는 경우, 객체를 인스턴화할 때 속성 메모리(즉: properties_table)를 할당할 때 하나의 zval이 추가로 할당됩니다. 유형은 HashTable이며, 매번 `__get($var)`를 호출할 때 입력된 `$var` 이름을 이 해시테이블에 저장합니다. 이렇게 하는 목적은 순환 호출을 방지하기 위함입니다. 예를 들어:
> 
> ***public function __get($var) { return $this->$var; }***
>
> 이러한 경우는 `__get()`를 호출할 때 존재하지 않는 속성을 다시 접근하는 것으로, 즉 `__get()` 메서드 내에서 순환 호출이 발생합니다. 요청한 `$var`에 대해 판단하지 않는다면 계속해서 순환 호출될 것입니다. 따라서 `__get()`를 호출하기 전에 먼저 현재 `$var`이 이미 `__get()`에 있는지 확인합니다. 만약 이미 있다면 다시는 `__get()`를 호출하지 않고, 그렇지 않다면 `$var`을 그 해시테이블의 키로 삽입하고 해시값을: *guard |= IN_ISSET로 설정합니다. `__get()`를 호출한 후에는 해시값을: *guard &= ~IN_ISSET로 설정합니다.
>
> 이 HashTable은 `__get()`에만 사용되는 것이 아니라, 다른 마법 메서드도 사용하기 때문에 해시값의 유형은 zend_long이며, 다른 마법 메서드는 다른 비트를 차지합니다. 또한, 모든 객체가 이 HashTable을 추가로 할당하는 것은 아닙니다. 객체를 생성할 때 `zend_class_entry.ce_flags`에 ***ZEND_ACC_USE_GUARDS***가 포함되어 있는지를 확인하여 할당합니다. 클래스가编译될 때 `__get()`, `__set()`, `__unset()`, `__isset()` 메서드가 정의되어 있다면 ce_flags에 이 마스크를 부여합니다.

코루틴이 전환된 후, 다음 호출은 순환 호출로 판단될 것이며, 이 문제는 PHP의 **특성**으로 인해 발생합니다. PHP 개발자와 소통한 후에도 아직 해결책이 없습니다.

주의: 마법 메서드에서는 코루틴 전환을 유발하는 코드가 없지만, 코루틴 강제 우선 배치가 활성화된 후에는 여전히 마법 메서드가 강제로 코루틴을 전환할 수 있습니다.

권장: 직접 `get`/`set` 메서드를 구현하여 명시적으로 호출합니다.

원래 문제 링크: [#2625](https://github.com/swoole/swoole-src/issues/2625)

## 심각한 오류

아래의 행위는 심각한 오류를 초래할 수 있습니다.

### 여러 코루틴이 동일한 연결을 공유하는 경우

동기적인 빅토리아 프로세스와 달리, 코루틴은 병렬로 요청을 처리하기 때문에 동시에 많은 요청이 병행하여 처리될 수 있습니다. 일단 고객 연결을 공유하면 다른 코루틴 사이에서 데이터가 혼란스러워질 수 있습니다. 참고: [여러 코루틴이 동일한 TCP 연결을 공유하는 것](/question/use?id=client-has-already-been-bound-to-another-coroutine)
### 클래스 정적 변수/전역 변수 사용 시 콘텍스트 보존 문제

여러 코루틴은 병렬적으로 실행되므로 코루틴 콘텍스트 내용을 보존하기 위해 클래스 정적 변수/전역 변수를 사용할 수 없습니다. 지역 변수를 사용하는 것이 안전하며, 지역 변수의 값은 자동으로 코루틴 스택에 보존되어 다른 코루틴이 코루틴의 지역 변수를 접근할 수 없습니다.

#### 잘못된 예제

```php
$server = new Swoole\Http\Server('127.0.0.1', 9501);

$_array = [];
$server->on('request', function ($request, $response) {
    global $_array;
    //요청 /a(코루틴 1)
    if ($request->server['request_uri'] == '/a') {
        $_array['name'] = 'a';
        co::sleep(1.0);
        echo $_array['name'];
        $response->end($_array['name']);
    }
    //요청 /b(코루틴 2)
    else {
        $_array['name'] = 'b';
        $response->end();
    }
});
$server->start();
```

`2`개의 병렬 요청을 보냅니다.

```shell
curl http://127.0.0.1:9501/a
curl http://127.0.0.1:9501/b
```

* 코루틴 `1`에서 전역 변수 `$_array['name']`의 값을 `a`로 설정합니다.
* 코루틴 `1`에서 `co::sleep`를 호출하여 일시정지합니다.
* 코루틴 `2`가 실행되어 `$_array['name']`의 값을 `b`로 설정하고 코루틴 `2`가 종료됩니다.
* 이때 타이머가 돌아와 기본 수준에서 코루틴 `1`의 실행을 복구합니다. 그러나 코루틴 `1`의 논리에는 콘텍스트 의존 관계가 있습니다. 다시 `$_array['name']`의 값을 출력할 때, 프로그램은 `a`를 예상하지만, 이 값은 이미 코루틴 `2`에 의해 수정되었습니다. 실제 결과는 `b`이 되어 논리적 오류가 발생합니다.
* 마찬가지로, 클래스 정적 변수 `Class::$array`, 전역 객체 속성 `$object->array`, 기타 초전역 변수 `$GLOBALS` 등을 사용하여 코루틴 프로그램에서 콘텍스트 보존하는 것은 매우 위험합니다. 예상치 못한 행동이 발생할 수 있습니다.

![](../_images/coroutine/notice-1.png)

#### 올바른 예제: Context를 사용한 콘텍스트 관리

코루틴 콘텍스트를 관리하기 위해 `Context` 클래스를 사용할 수 있습니다. `Context` 클래스에서는 `Coroutine::getuid`를 사용하여 코루틴 `ID`를 가져온 다음, 다른 코루틴 간의 전역 변수를 격리하고, 코루틴이 종료될 때 콘텍스트 데이터를 청소합니다.

```php
use Swoole\Coroutine;

class Context
{
    protected static $pool = [];

    static function get($key)
    {
        $cid = Coroutine::getuid();
        if ($cid < 0)
        {
            return null;
        }
        if(isset(self::$pool[$cid][$key])){
            return self::$pool[$cid][$key];
        }
        return null;
    }

    static function put($key, $item)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            self::$pool[$cid][$key] = $item;
        }
    }

    static function delete($key = null)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            if($key){
                unset(self::$pool[$cid][$key]);
            }else{
                unset(self::$pool[$cid]);
            }
        }
    }
}
```

사용 예:

```php
use Swoole\Coroutine\Context;

$server = new Swoole\Http\Server('127.0.0.1', 9501);

$server->on('request', function ($request, $response) {
    if ($request->server['request_uri'] == '/a') {
        Context::put('name', 'a');
        co::sleep(1.0);
        echo Context::get('name');
        $response->end(Context::get('name'));
        //코루틴 종료 시 청소
        Context::delete('name');
    } else {
        Context::put('name', 'b');
        $response->end();
        //코루틴 종료 시 청소
        Context::delete();
    }
});
$server->start();
```
