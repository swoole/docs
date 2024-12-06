# 코루틴\MySQL

코루틴 MySQL 클라이언트입니다.

!> 이 클라이언트는 더 이상 사용되지 않으며, `Swoole\Runtime::enableCoroutine` + `pdo_mysql` 또는 `mysqli` 방식으로 권장합니다. 즉, [하이브리드 코루틴화](/runtime)된 원래 `MySQL` 클라이언트를 사용하세요.  
!> Swoole 6.0 이후, 이 코루틴 MySQL 클라이언트는 제거되었습니다.


## 사용 예제

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    var_dump($res);
});
```


## defer 특성

[병렬 클라이언트](/coroutine/multi_call) 장을 참고하세요.


## 저장 과정

4.0.0 버전 이후로, MySQL 저장 과정과 멀티 결과集的 획득이 지원됩니다.


## MySQL8.0

Swoole-4.0.1 이상 버전은 MySQL8의 모든 보안 인증 능력을 지원하며, 클라이언트를 직접 사용할 수 있습니다. 비밀번호 설정을 되돌릴 필요가 없습니다.


### 4.0.1 이전 버전

MySQL-8.0은 기본적으로 보안성이 강화된 `caching_sha2_password` 플러그인을 사용합니다. 5.x에서 업그레이드한 경우, 모든 MySQL 기능을 직접 사용할 수 있습니다. 새로운 MySQL를 만드는 경우, 다음 명령어를 MySQL 커맨드라인에서 실행하여 호환성을 갖추어야 합니다:

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

명령어에서 `'root'@'localhost'`을 사용하는 계정으로, `password`를 사용하는 비밀번호로 바꿔주세요.

여전히 사용할 수 없다면, my.cnf에서 `default_authentication_plugin = mysql_native_password`를 설정해야 합니다.


## 속성


### serverInfo

연결 정보, 연결에 전달된 배열을 저장합니다.


### sock

연결에 사용하는 파일 디스크립터입니다.


### connected

MySQL 서버에 연결되어 있는지 여부입니다.

!> [connected 속성과 연결 상태가 일치하지 않는 것](/question/use?id=connected%E8%83%BD%E5%92%8C%E7%BB%93%E6%9E%9C%E4%B8%8D%E5%90%8C)을 참고하세요.


### connect_error

`connect`을 사용하여 MySQL 서버에 연결할 때 발생한 오류 정보를 저장합니다.


### connect_errno

`connect`을 사용하여 MySQL 서버에 연결할 때 발생한 오류 코드를 저장합니다. 정수 유형입니다.


### error

MySQL 명령을 실행할 때, 서버가 반환한 오류 정보를 저장합니다.


### errno

MySQL 명령을 실행할 때, 서버가 반환한 오류 코드를 저장합니다. 정수 유형입니다.


### affected_rows

변경된 행 수를 저장합니다.


### insert_id

마지막으로 삽입된 기록의 `id`를 저장합니다.


## 방법


### connect()

MySQL 연결을 구축합니다.

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

!> `$serverInfo`：매개변수로 배열을 전달합니다.

```php
[
    'host'        => 'MySQL IP 주소', // 로컬 UNIX Socket이면 `unix://tmp/your_file.sock` 형식으로 작성해야 합니다.
    'user'        => '데이터 사용자',
    'password'    => '데이터베이스 비밀번호',
    'database'    => '데이터베이스 이름',
    'port'        => 'MySQL 포트(기본 3306, 선택적 매개변수)',
    'timeout'     => '연결 시간 초과', // 오직 connect 시간 초과에 영향을 미치며, query와 execute 방법에는 영향을 미치지 않습니다. [클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간초과규칙)을 참고하세요.
    'charset'     => '캐릭터 세트',
    'strict_type' => false, // 엄격한 모드를 활성화하면, query 방법에서 반환되는 데이터도 강력한 유형으로 전환됩니다.
    'fetch_mode'  => true,  // fetch 모드를 활성화하면, pdo처럼 fetch/fetchAll을 사용하여 한 줄씩 또는 전체 결과 세트를 획득할 수 있습니다(4.0 버전 이상).
]
```


### query()

SQL 문을 실행합니다.

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **매개변수** 

    * **`string $sql`**
      * **기능**：SQL 문
      * **기본값**：없음
      * **기타 값**：없음

    * **`float $timeout`**
      * **기능**：시간 초과 【MySQL 서버가 지정한 시간 내에 데이터를 반환하지 않을 경우, 하단에서 `false`를 반환하고, 오류 코드를 `110`로 설정하며, 연결을 끊습니다】
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)입니다.
      * **기본값**：`0`
      * **기타 값**：없음
      * **참고[클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간초과규칙)**


  * **반환값**

    * 시간 초과/오류 경우 `false`를 반환하고, 그렇지 않으면 `array` 형태로 결과를 반환합니다.

  * **지연 수신**

  !> `defer`를 설정하면, `query`를 호출하면 즉시 `true`를 반환합니다. `recv`를 호출해야만 코루틴에서 기다리며, 결과를 반환합니다.

  * **예제**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('show tables');
    if ($res === false) {
        return;
    }
    var_dump($res);
});
```


### prepare()

MySQL 서버에 SQL 준비 요청을 보냅니다.

!> `prepare`는 반드시 `execute`와 함께 사용해야 합니다. 준비 요청이 성공하면, `execute` 메서드를 호출하여 MySQL 서버에 데이터 매개변수를 보냅니다.

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **매개변수** 

    * **`string $sql`**
      * **기능**：준비 문 【`?`를 매개변수 대신 사용합니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`float $timeout`**
      * **기능**：시간 초과 
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)입니다.
      * **기본값**：`0`
      * **기타 값**：없음
      * **참고[클라이언트 시간 초과 규칙](/coroutine_client/init?id=시간초과규칙)**


  * **반환값**

    * 실패 시 `false`를 반환하고, `$db->error`와 `$db->errno`를 확인하여 오류 원인을 판단할 수 있습니다.
    * 성공 시 `Coroutine\MySQL\Statement` 객체를 반환하며, 객체의 [execute](/coroutine_client/mysql?id=statement-gtexecute) 메서드를 호출하여 매개변수를 보낼 수 있습니다.

  * **예제**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10));
        var_dump($ret2);
    }
});
```


### escape()

SQL 문에서 특수 문자를 이스케이프하여 SQL 주입 공격을 방지합니다. 하단은 `mysqlnd`에서 제공하는 함수에 기반을 두고 있으며, PHP의 `mysqlnd` 확장을 필요로 합니다.

!> 컴파일 시 `--enable-mysqlnd` 옵션을 추가하여 활성화해야 합니다.

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

  * **매개변수** 

    * **`string $str`**
      * **기능**：이스케이프된 문자
      * **기본값**：없음
      * **기타 값**：없음

  * **사용 예제**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $data = $db->escape("abc'efg\r\n");
});
```
### 시작()

트랜잭션을 시작합니다. `commit`와 `rollback`과 함께 사용하여 `MySQL` 트랜잭션 처리를 구현합니다.

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> `MySQL` 트랜잭션을 시작하면 성공하여 `true`을 반환하고, 실패하면 `false`을 반환합니다. 오류 코드를 확인하려면 `$db->errno`를 확인하세요.
  
!> 같은 `MySQL` 연결 객체는 동시에 하나의 트랜잭션만 시작할 수 있습니다;  
직전에 시작한 트랜잭션이 `commit`되거나 `rollback`되기 전까지는 새로운 트랜잭션을 시작할 수 없습니다;  
그렇지 않으면 하단에서 `Swoole\MySQL\Exception` 예외가 발생하며, 예외의 `code`는 `21`입니다.

  * **예시**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```


### commit()

트랜잭션을 커밍합니다. 

!> `begin`와 함께 사용해야 합니다.

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> 성공하면 `true`을 반환하고, 실패하면 `false`을 반환합니다. 오류 코드를 확인하려면 `$db->errno`를 확인하세요.


### rollback()

트랜잭션을 롤백합니다.

!> `begin`와 함께 사용해야 합니다.

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> 성공하면 `true`을 반환하고, 실패하면 `false`을 반환합니다. 오류 코드를 확인하려면 `$db->errno`를 확인하세요.


### Statement->execute()

MySQL 서버에 SQL 프리퍼레시 데이터 매개변수를 보냅니다.

!> `execute`는 `prepare`와 함께 사용해야 하며, `execute`를 호출하기 전에 먼저 `prepare`를 호출하여 프리퍼레시 요청을 시작해야 합니다.

!> `execute` 方法는 반복해서 호출할 수 있습니다.

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **매개변수** 

    * **`array $params`**
      * **기능**：프리퍼시 데이터 매개변수 【`prepare` 문장의 매개변수 개수와 동일해야 합니다. `$params`는 숫자 인덱스数组여야 하며, 매개변수의 순서는 `prepare` 문장과 동일해야 합니다】
      * **기본값**：없음
      * **기타 값**：없음

    * **`float $timeout`**
      * **기능**：초기화 시간 【정해진 시간 내에 `MySQL` 서버가 데이터를 반환하지 않으면 하단에서 `false`을 반환하고, 오류 코드를 `110`로 설정하며, 연결을 끊습니다】
      * **값 단위**：초, 최소 정확도가 밀리초(`0.001`초)입니다
      * **기본값**：`-1`
      * **기타 값**：없음
      * **참조[클라이언트 초기화 규칙](/coroutine_client/init?id=초기화 규칙)**

  * **반환값** 

    * 성공 시 `true`을 반환하고, `connect`의 `fetch_mode` 매개변수를 `true`로 설정했을 때 성공 시
    * 성공 시 `array` 데이터 세트 배열을 반환하고, 그렇지 않은 경우에는
    * 실패 시 `false`을 반환하며, `$db->error`와 `$db->errno`를 확인하여 오류 원인을 판단할 수 있습니다

  * **사용 예시** 

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=? and name=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10, 'rango'));
        var_dump($ret2);

        $ret3 = $stmt->execute(array(13, 'alvin'));
        var_dump($ret3);
    }
});
```


### Statement->fetch()

결과 세트에서 다음 행을 가져옵니다.

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> Swoole 버전 >= `4.0-rc1`에서는 `connect` 시 `fetch_mode => true` 옵션을 추가해야 합니다

  * **예시** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> `v4.4.0` 이후의 새로운 `MySQL` 드라이버부터는 `fetch`는 예시 코드의 방식으로 `NULL`까지 읽어야 하며, 그렇지 않으면 새로운 요청을 시작할 수 없습니다 (하단의 요구에 따라 읽기 메커니즘 때문에 메모리를 절약할 수 있습니다)


### Statement->fetchAll()

결과 세트에 있는 모든 행이 포함된 배열을 반환합니다.

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> Swoole 버전 >= `4.0-rc1`에서는 `connect` 시 `fetch_mode => true` 옵션을 추가해야 합니다

  * **예시** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```

### Statement->nextResult()

다중 응답 결과 문장 핸들에서 다음 응답 결과로 전진합니다 (예: 저장 프로세스의 다중 결과 반환).

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

  * **반환값**

    * 성공 시 `TRUE`을 반환합니다
    * 실패 시 `FALSE`을 반환합니다
    * 다음 결과가 없을 시 `NULL`을 반환합니다

  * **예시** 

    * **fetch 모드가 아닐 때**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **fetch 모드일 때**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> `v4.4.0` 이후의 새로운 `MySQL` 드라이버부터는 `fetch`는 예시 코드의 방식으로 `NULL`까지 읽어야 하며, 그렇지 않으면 새로운 요청을 시작할 수 없습니다 (하단의 요구에 따라 읽기 메커니즘 때문에 메모리를 절약할 수 있습니다)
