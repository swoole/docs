# 코루틴 PostgreSQL

코루틴 PostgreSQL 클라이언트입니다.

!> Swoole 5.0에서 완전히 재구성되어 이전 버전의 사용법과 완전히 다릅니다. 만약 당신이 이전 버전을 사용하고 있다면 [구버전 문서](/coroutine_client/postgresql-old.md)를 확인하세요.

!> Swoole 6.0 이후로 코루틴 PostgreSQL 클라이언트가 제거되었습니다. 대신 [코루틴화된 pdo_pgsql](/runtime?id=swoole_hook_pdo_pgsql)를 사용하세요.


## 컴파일 설치

* 시스템에 `libpq` 라이브러리가 이미 설치되어 있는지 확인해야 합니다.
* `macOS`에서 PostgreSQL이 제공하는 `libpq` 라이브러리가 이미 설치되어 있지만, 환경에 차이가 있을 수 있습니다. `ubuntu`의 경우 `apt-get install libpq-dev`가 필요할 수 있고, `centos`의 경우 `yum install postgresql10-devel`이 필요할 수 있습니다.
* Swoole를 컴파일할 때 `--enable-swoole-pgsql` 옵션을 추가하세요.


## 사용 예제

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    if (!$conn) {
        var_dump($pg->error);
        return;
    }
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchAll();
    var_dump($arr);
});
```


### 트랜잭션 처리

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    $pg->query('BEGIN');
    $stmt = $pg->query('SELECT * FROM test');
    $arr = $stmt->fetchAll();
    $pg->query('COMMIT');
    var_dump($arr);
});
```


## 속성


### error

오류 정보를 가져옵니다.


## 메서드


### connect()

비동기 코루틴을 사용한 PostgreSQL 연결을 구축합니다.

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo`는 연결 정보를 나타내며, 성공 시 `true`를, 실패 시 `false`를 반환합니다. [error](/coroutine_client/postgresql?id=error) 속성에서 오류 정보를 얻을 수 있습니다.
  * **예제**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
    var_dump($pg->error, $conn);
});
```


### query()

SQL 문을 실행합니다. 비동기 비구분 코루틴 명령을 보냅니다.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
```

  * **매개변수** 

    * **`string $sql`**
      * **기능**: SQL 문
      * **기본값**: 없음
      * **기타값**: 없음

  * **예제**

    * **select**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        var_dump($arr);
    });
    ```

    * **insert id 반환**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
        $stmt = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $stmt->fetchRow();
        var_dump($arr);
    });
    ```

    * **transaction**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $pg->query('BEGIN;');
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        $pg->query('COMMIT;');
        var_dump($arr);
    });
    ```


### metaData()

테이블의 메타데이터를 확인합니다. 비동기 비구분 코루틴 버전입니다.

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```    
  * **사용 예제**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->metaData('test');
    var_dump($result);
});
```


### prepare()

준비 (preparation)합니다.

```php
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **사용 예제**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $stmt = $pg->prepare("select * from test where id > $1 and id < $2");
    $res = $stmt->execute(array(1, 3));
    $arr = $stmt->fetchAll();
    var_dump($arr);
});
```


## PostgreSQLStatement

클래스 이름: `Swoole\Coroutine\PostgreSQLStatement`

모든 쿼리는 `PostgreSQLStatement` 객체를 반환합니다.


### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **매개변수**
    * **`$result_type`**
      * **기능**: 상수입니다. 선택적인 매개변수로, 반환값을 초기화하는 방식을 제어합니다.
      * **기본값**: `SW_PGSQL_ASSOC`
      * **기타값**: 없음

      取值 | 반환값
      ---|---
      SW_PGSQL_ASSOC | 필드명이 키로 사용되는 연관 배열을 반환합니다.
      SW_PGSQL_NUM | 필드 번호가 키로 사용됩니다.
      SW_PGSQL_BOTH | 두 가지 모두 키로 사용됩니다.

  * **반환값**

    * 결과의 모든 행을 한 배열로 반환합니다.


### affectedRows()

수정된 레코드 수를 반환합니다. 

```php
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```


### numRows()

행 수를 반환합니다.

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```


### fetchObject()

한 행을 객체로 추출합니다. 

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **예제**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    for ($row = 0; $row < $stmt->numRows(); $row++) {
        $data = $stmt->fetchObject($row);
        echo $data->id . " \n ";
    }
});
```
```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    while ($data = $stmt->fetchObject($row)) {
        echo $data->id . " \n ";
        $row++;
    }
});
```


### fetchAssoc()

한 행을 연관 배열로 추출합니다.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```


### fetchArray()

한 행을 배열로 추출합니다.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **매개변수**
    * **`int $row`**
      * **기능**: `$row`는 추출하고자 하는 행(레코드)의 번호입니다. 첫 번째 행은 `0`입니다.
      * **기본값**: 없음
      * **기타값**: 없음
    * **$result_type`**
      * **기능**: 상수입니다. 선택적인 매개변수로, 반환값을 초기화하는 방식을 제어합니다.
      * **기본값**: `SW_PGSQL_BOTH`
      * **기타값**: 없음

      取值 | 반환값
      ---|---
      SW_PGSQL_ASSOC | 필드명이 키로 사용되는 연관 배열을 반환합니다.
      SW_PGSQL_NUM | 필드 번호가 키로 사용됩니다.
      SW_PGSQL_BOTH | 두 가지 모두 키로 사용됩니다.

  * **반환값**

    * 추출한 행( Tuple/레코드 )와 일치하는 배열을 반환합니다. 더 이상 추출할 수 있는 행이 없다면 `false`를 반환합니다.

  * **사용 예제**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchArray(1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```
### fetchRow()

특정된 `result` 자원을 기반으로 한 한 줄의 데이터(기록)를 배열로 반환합니다. 각각 얻어진 열은 배열에 순차적으로 저장되며, 오프셋은 `0`에서 시작합니다.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **매개변수**
    * **`int $row`**
      * **기능**: `row`는 얻고자 하는 행(기록)의 번호입니다. 첫 번째 행은 `0`입니다.
      * **기본값**: 없음
      * **기타값**: 없음
    * **`$result_type`**
      * **기능**: 상수입니다. 선택적 매개변수로, 반환값을 초기화하는 방식을 제어합니다.
      * **기본값**: `SW_PGSQL_NUM`
      * **기타값**: 없음

      값 | 반환값
      ---|---
      SW_PGSQL_ASSOC | 필드명이 키로 사용되는 연관 배열을 반환합니다.
      SW_PGSQL_NUM | 필드 번호가 키로 사용됩니다.
      SW_PGSQL_BOTH | 두 가지 모두 키로 사용됩니다.

  * **반환값**

    * 반환된 배열은 추출한 행과 일치합니다. 더 이상 추출할 수 있는 행이 없을 경우 `false`를 반환합니다.

  * **사용 예시**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    while ($row = $stmt->fetchRow()) {
        echo "name: $row[0]  mobile: $row[1]" . PHP_EOL;
    }
});
```
