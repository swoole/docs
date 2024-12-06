# 고성능 공유 메모리 테이블

PHP 언어는 멀티스레딩을 지원하지 않기 때문에 Swoole은 멀티 프로세스 모드를 사용합니다. 멀티 프로세스 모드에서는 프로세스 메모리 격리가 존재하며, 작업 프로세스 내에서 global 전역 변수와 초기화 전역 변수를 수정하는 것은 다른 프로세스에서는 무효합니다.

> worker_num=1을 설정하면 프로세스 격리가 존재하지 않으며, 전역 변수를 사용하여 데이터를 저장할 수 있습니다.

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "connection open: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

$fds는 전역 변수이지만 현재 프로세스 내에서만 유효합니다. Swoole 서버의底层은 여러 Worker 프로세스를 만들며, var_dump($fds)가 출력하는 값은 연결된 fd 중 일부에 불과합니다.

해당 문제에 대한 해결책은 외부 저장 서비스를 사용하는 것입니다:

* 데이터베이스, 예: MySQL, MongoDB
* 캐시 서버, 예: Redis, Memcache
* 디스크 파일, 멀티 프로세스 병행读写 시 잠금을 추가해야 합니다.

일반적인 데이터베이스와 디스크 파일 작업은 많은 IO 대기 시간을 가지고 있습니다. 따라서 다음을 추천합니다:

* Redis 메모리 데이터베이스, 읽기 및 쓰기 속도가 매우 빠르지만, TCP 연결 문제 등이 있으며, 성능이 가장 높지는 않습니다.
* /dev/shm 메모리 파일 시스템, 읽기 및 쓰기 작업이 모두 메모리에서 완료되며, IO 소모가 없고 성능이 매우 높지만, 데이터가 형식화되지 않으며 데이터 동기화 문제가 있습니다.

?> 위의 저장 사용 외에도, 데이터를 저장하기 위해 공유 메모리를 사용하는 것이 추천됩니다. Swoole\Table는 공유 메모리와 잠금을 기반으로 한 초고성능, 병행 데이터 구조입니다. 이는 멀티 프로세스/멀티스레딩 데이터 공유 및 동기화 잠금 문제를 해결하기 위해 사용됩니다. Table의 메모리 용량은 PHP의 memory_limit에 의해 제어되지 않습니다.

!> Table을 배열 방식으로 읽기/쓰지 마십시오. 문서에서 제공하는 API를 사용하여 작업을 수행해야 합니다;  
배열 방식으로 추출된 Table\Row 객체는 일회성 객체로, 그에 의존하여 너무 많은 작업을 하지 마십시오.
v4.7.0 버전부터는 배열 방식으로 Table을 읽기/쓰는 것을 더 이상 지원하지 않으며, Table\Row 객체도 제거되었습니다.

* ** 장점**

  * 성능이 강력하며, 단일 스레드당 초당 200만 번의 읽기/쓰기가 가능합니다;
  * 응용 코드는 잠금을 추가할 필요가 없으며, Table은 내장된 행 잠금 스핀락 잠금을 가지고 있어 모든 작업이 멀티스레드/멀티프로세스 안전합니다. 사용자 계층은 데이터 동기화 문제를 전혀 고려할 필요가 없습니다;
  * 멀티 프로세스를 지원하며, Table은 멀티 프로세스 간에 데이터를 공유하는 데 사용할 수 있습니다;
  * 전역 잠금 대신 행 잠금을 사용하여, 두 프로세스가 동일한 CPU 시간에 병행하여 동일한 데이터를 읽을 경우에만 잠금 경쟁이 발생합니다.

* ** 순회**

!> 순회 중에 삭제 작업을 하지 마십시오(모든 키를 추출한 후에 삭제할 수 있습니다)

Table 클래스는 이터레이터와 Countable 인터페이스를 구현하고 있어, foreach를 사용하여 순회하고 count를 사용하여 현재 행수를 계산할 수 있습니다.

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```


## 속성


### size

테이블의 최대 행수를 가져옵니다.

```php
Swoole\Table->size;
```


### memorySize

실제로 차지하는 메모리의 크기를 가져옵니다, 단위는 바이트입니다.

```php
Swoole\Table->memorySize;
```


## 방법


### __construct()

메모리 테이블을 만듭니다.

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **매개변수** 

    * **`int $size`**
      * **기능** : 테이블의 최대 행수를 지정합니다
      * **기본값** : 없음
      * **기타값** : 없음

      !> Table의底层은 공유 메모리에 구축되어 있기 때문에 동적으로 확장이 불가능합니다. 따라서 `$size`는 생성 전에 직접 계산하여 설정해야 하며, Table이 저장할 수 있는 최대 행수는 `$size`와 정비례하지만 완전히 일치하지 않습니다. 예를 들어 `$size`가 `1024`인 경우 실제로 저장할 수 있는 행수는 **`1024`보다 적습니다**. `$size`가 너무 큰 경우 기계 메모리가 부족하여 Table 생성이 실패할 수 있습니다.  

    * **`float $conflict_proportion`**
      * **기능** : 해시 충돌의 최대 비율
      * **기본값** : `0.2` (즉 `20%`)
      * **기타값** : 최소 `0.2`, 최대 `1`

  * **용량 계산**

      * `$size`가 `2`의 `N`차방程式이 아닐 경우, 예를 들어 `1024`, `8192`, `65536` 등,底层은 자동으로 가까운 숫자로 조정합니다. 만약 `1024`보다 작다면 기본적으로 `1024`로 설정되며, 즉 `1024`는 최소값입니다. v4.4.6 버전부터 최소값은 `64`입니다.
      * Table이 차지하는 메모리 총량은 (`HashTable 구조체 길이` + `KEY 길이 64바이트` + `$size 값`) * (`1 + $conflict_proportion 값을 hash 충돌로 사용`) * (`열 크기`)입니다.
      * 만약 당신의 데이터 `Key`와 Hash 충돌률이 `20%`를 초과하면, 예약된 충돌 메모리 블록 용량이 부족하여, 새로운 데이터를 `set`하려 하면 `Unable to allocate memory` 오류가 발생하고 `false`를 반환하며, 저장이 실패합니다. 이때 `$size` 값을 늘리고 서비스를 재시작해야 합니다.
      * 메모리가 충분한 경우 이 값을 최대한 크게 설정하는 것이 좋습니다.


### column()

메모리 테이블에 열을 추가합니다.

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **매개변수** 

    * **`string $name`**
      * **기능** : 열의 이름을 지정합니다
      * **기본값** : 없음
      * **기타값** : 없음

    * **`int $type`**
      * **기능** : 열의 유형을 지정합니다
      * **기본값** : 없음
      * **기타값** : `Table::TYPE_INT`, `Table::TYPE_FLOAT`, `Table::TYPE_STRING`

    * **`int $size`**
      * **기능** : 문자열 열의 최대 길이를 지정합니다【문자열 유형의 열은 반드시 `$size`를 지정해야 합니다】
      * **값 단위** : 바이트
      * **기본값** : 없음
      * **기타값** : 없음

  * ** `$type` 유형 설명**


유형 | 설명
---|---
Table::TYPE_INT | 기본적으로 8바이트입니다
Table::TYPE_STRING | 설정 후, 설정된 문자열은 `$size`가 지정한 최대 길이를 초과할 수 없습니다
Table::TYPE_FLOAT | 8바이트의 메모리를 차지합니다


### create()

메모리 테이블을 만듭니다. 테이블의 구조를 정의한 후, create를 호출하여 운영 체계에 메모리를 신청하고 테이블을 만듭니다.

```php
Swoole\Table->create(): bool
```

create를 사용하여 테이블을 만들면 [memorySize](/memory/table?id=memorysize) 속성을 사용하여 실제로 차지하는 메모리의 크기를 가져올 수 있습니다

  * **알림** 

    * create를 호출하기 전에 set, get 등 데이터 읽기/쓰기 작업을 사용할 수 없습니다
    * create를 호출한 후에는 column 메서드로 새로운 열을 추가할 수 없습니다
    * 시스템 메모리가 부족하여 신청이 실패하면, create는 false를 반환합니다
    * 메모리 신청에 성공하면, create는 true를 반환합니다

    !> Table은 공유 메모리를 사용하여 데이터를 저장하므로, 자식 프로세스를 만들기 전에 반드시 Table->create()를 실행해야 합니다;  
    Server에서 Table을 사용하려면, Server->start() 전에 Table->create()를 실행해야 합니다.

  * **사용 예시**

```php
$table = new Swoole\Table(1024);
$table->column('id', Swoole\Table::TYPE_INT);
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->column('num', Swoole\Table::TYPE_FLOAT);
$table->create();

$worker = new Swoole\Process(function () {}, false, false);
$worker->start();

//$serv = new Swoole\Server('127.0.0.1', 9501);
//$serv->start();
```
### set()

행의 데이터를 설정합니다. `Table`은 `key-value` 방식으로 데이터에 액세스합니다.

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **매개변수** 

    * **`string $key`**
      * **기능**：데이터의 `key`
      * **기본값**：없음
      * **기타값**：없음

      !> 동일한 `$key`이 동일한 행 데이터에 해당합니다. 만약 `set`을 같은 `key`로 사용하면 이전의 데이터가 덮여지고, `key`의 최대 길이는 63바이트를 초과할 수 없습니다.

    * **`array $value`**
      * **기능**：데이터의 `value`
      * **기본값**：없음
      * **기타값**：없음

      !> 반드시 배열이어야 하며, 필드 정의의 `$name`과 완전히 동일해야 합니다.

  * **반환값**

    * 성공 시 `true`를 반환합니다.
    * 실패 시 `false`를 반환합니다. 이는 해시 충돌이 너무 많아 동적으로 메모리를 할당할 수 없을 때 발생할 수 있습니다. 생성자의 두 번째 매개변수를 늘릴 수 있습니다.

!> -`Table->set()`은 모든 필드의 값을 설정할 수도 있고 일부 필드만 수정할 수도 있습니다;  
   -`Table->set()`이 설정되기 전에는 해당 행의 모든 필드가 비어 있습니다;  
   -`set`/`get`/`del`은 내장된 행 잠금을 가지고 있으므로 `lock`를 호출하여 잠금을 걸 필요가 없습니다;  
   -**Key는 비트 안전하지 않으며 반드시 문자열 타입이어야 하며, 이진 데이터를 전달해서는 안 됩니다.**
    
  * **사용 예시**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **최대 길이 초과 문자열 설정**
    
    전달된 문자열의 길이가 열 정의 시 설정된 최대 크기를 초과하면, 저수층은 자동으로 잘라냅니다.
    
    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * `str_value` 열의 최대 크기는 5바이트이지만 `set`은 5바이트를 초과하는 문자열을 설정했습니다
    * 저수층은 자동으로 5바이트의 데이터를 잘라내고, 최종적으로 `str_value`의 값은 `world`이 됩니다

!> v4.3 버전부터 저수층은 메모리 길이에 대해 정렬 처리를 진행합니다. 문자열 길이는 반드시 8의 배수여야 하며, 길이가 5인 경우 자동으로 8바이트로 정렬됩니다. 그래서 `str_value`의 값은 `world 12`가 됩니다.


### incr()

원자적 증폭 연산입니다.

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **매개변수** 

    * **`string $key`**
      * **기능**：데이터의 `key`【만약 `$key`에 해당하는 행이 존재하지 않으면 기본 열의 값은 `0`입니다】
      * **기본값**：없음
      * **기타값**：없음

    * **`string $column`**
      * **기능**：특정 열 이름 지정【부동소수와 부동정수 열만 지원합니다】
      * **기본값**：없음
      * **기타값**：없음

    * **`string $incrby`**
      * **기능**：증폭량【열이 `int`인 경우 `$incrby`는 반드시 `int` 타입이어야 하고, 열이 `float`인 경우 `$incrby`는 반드시 `float` 타입이어야 합니다】
      * **기본값**：`1`
      * **기타값**：없음

  * **반환값**

    최종 결과 수치를 반환합니다.


### decr()

원자적 감소 연산입니다.

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **매개변수** 

    * **`string $key`**
      * **기능**：데이터의 `key`【만약 `$key`에 해당하는 행이 존재하지 않으면 기본 열의 값은 `0`입니다】
      * **기본값**：없음
      * **기타값**：없음

    * **`string $column`**
      * **기능**：특정 열 이름 지정【부동소수와 부동정수 열만 지원합니다】
      * **기본값**：없음
      * **기타값**：없음

    * **`string $decrby`**
      * **기능**：감소량【열이 `int`인 경우 `$decrby`는 반드시 `int` 타입이어야 하고, 열이 `float`인 경우 `$decrby`는 반드시 `float` 타입이어야 합니다】
      * **기본값**：`1`
      * **기타값**：없음

  * **반환값**

    최종 결과 수치를 반환합니다.

    !> 수치가 `0`이 되면 감소하면 부정수가 됩니다.


### get()

행 데이터를 가져옵니다.

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **매개변수** 

    * **`string $key`**
      * **기능**：데이터의 `key`【반드시 문자열 타입이어야 합니다】
      * **기본값**：없음
      * **기타값**：없음

    * **`string $field`**
      * **기능**：`$field`가 지정될 경우 해당 필드의 값만 반환하며 전체 기록이 아닙니다
      * **기본값**：없음
      * **기타값**：없음
      
  * **반환값**

    * `$key`이 존재하지 않을 경우 `false`를 반환합니다.
    * 성공 시 결과 배열을 반환합니다.
    * `$field`가 지정될 경우 해당 필드의 값만 반환하며 전체 기록이 아닙니다.


### exist()

table에 특정 key가 존재하는지 확인합니다.

```php
Swoole\Table->exist(string $key): bool
```

  * **매개변수** 

    * **`string $key`**
      * **기능**：데이터의 `key`【반드시 문자열 타입이어야 합니다】
      * **기본값**：없음
      * **기타값**：없음


### count()

table에 존재하는条目수를 반환합니다.

```php
Swoole\Table->count(): int
```


### del()

데이터를 삭제합니다.

!> `Key`는 비트 안전하지 않으며 반드시 문자열 타입이어야 하며, 이진 데이터를 전달해서는 안 됩니다; **순차적으로 삭제하는 것은 금지됩니다**.

```php
Swoole\Table->del(string $key): bool
```

  * **반환값**

    * `$key`에 해당하는 데이터가 존재하지 않을 경우 `false`를 반환합니다.
    * 성공 시 삭제되어 `true`를 반환합니다.


### stats()

`Swoole\Table`의 상태를 가져옵니다.

```php
Swoole\Table->stats(): array
```

!> Swoole 버전이 `v4.8.0` 이상일 경우 사용할 수 있습니다.


## 도우미 함수 :id=swoole_table

사용자가 빠르게 `Swoole\Table`를 생성할 수 있도록 도와줍니다.

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Swoole 버전이 `v4.6.0` 이상일 경우 사용할 수 있습니다. `$fields`의 형식은 `foo:i/foo:s:num/foo:f`입니다.

| 준명 | 전체명   | 타입               |
| ---- | -------- | ------------------ |
| i    | int      | Table::TYPE_INT    |
| s    | string   | Table::TYPE_STRING |
| f    | float    | Table::TYPE_FLOAT  |

예시:

```php
$table = swoole_table(1024, 'fd:int, reactor_id:i, data:s:64');
var_dump($table);

$table = new Swoole\Table(1024, 0.25);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();
var_dump($table);
```

## 전체 예시

```php
<?php
$table = new Swoole\Table(1024);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();

$serv = new Swoole\Server('127.0.0.1', 9501);
$serv->set(['dispatch_mode' => 1]);
$serv->table = $table;

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {

	$cmd = explode(" ", trim($data));

	//get
	if ($cmd[0] == 'get')
	{
		//get self
		if (count($cmd) < 2)
		{
			$cmd[1] = $fd;
		}
		$get_fd = intval($cmd[1]);
		$info = $serv->table->get($get_fd);
		$serv->send($fd, var_export($info, true)."\n");
	}
	//set
	elseif ($cmd[0] == 'set')
	{
		$ret = $serv->table->set($fd, array('reactor_id' => $data, 'fd' => $fd, 'data' => $cmd[1]));
		if ($ret === false)
		{
			$serv->send($fd, "ERROR\n");
		}
		else
		{
			$serv->send($fd, "OK\n");
		}
	}
	else
	{
		$serv->send($fd, "command error.\n");
	}
});

$serv->start();
```
