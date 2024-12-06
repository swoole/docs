# 코루틴\PostgreSQL 구버전

코루틴 `PostgreSQL` 고객端입니다. [ext-postgresql](https://github.com/swoole/ext-postgresql) 확장을 컴파일하여 이 기능을 활성화해야 합니다.

> 이 문서는 Swoole < 5.0에만 적합합니다.


## 컴파일 설치

소스 코드 다운로드: [https://github.com/swoole/ext-postgresql](https://github.com/swoole/ext-postgresql)，Swoole 버전에 맞는 releases 버전을 설치해야 합니다.

* 시스템에 `libpq` 라이브러리가 이미 설치되어 있는지 확인해야 합니다.
* `mac`에서는 `postgresql`가 제공하는 `libpq` 라이브러리가 이미 설치되어 있지만, 환경 간에 차이가 있습니다. `ubuntu`의 경우 `apt-get install libpq-dev`가 필요할 수 있고, `centos`의 경우 `yum install postgresql10-devel`가 필요할 수 있습니다.
* 또한 별도로 `libpq` 라이브러리 디렉터리를 지정할 수도 있습니다. 예: `./configure --with-libpq-dir=/etc/postgresql`


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
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchAll($result);
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
    $result = $pg->query('SELECT * FROM test');
    $arr = $pg->fetchAll($result);
    $pg->query('COMMIT');
    var_dump($arr);
});
```


## 속성


### error

오류 정보를 가져옵니다.


## 방법


### connect()

비동기 코루틴을 통해 `postgresql`에 연결합니다.

```php
Swoole\Coroutine\PostgreSQL->connect(string $connection_string): bool
```

!> `$connection_string`은 연결 정보를 나타내며, 성공 시 `true`를, 실패 시 `false`를 반환합니다. [error](/coroutine_client/postgresql?id=error) 속성으로 오류 정보를 가져올 수 있습니다.
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

SQL 문을 실행합니다. 비동기 비协程 명령을 보냅니다.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): resource;
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
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
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
        $result = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $pg->fetchRow($result);
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
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
        $pg->query('COMMIT;');
        var_dump($arr);
    });
    ```


### fetchAll()

```php
Swoole\Coroutine\PostgreSQL->fetchAll(resource $queryResult, $resultType = SW_PGSQL_ASSOC):? array;
```

  * **매개변수**
    * **`$resultType`**
      * **기능**: 상수입니다. 선택적 매개변수로, 반환값을 초기화하는 방식을 제어합니다.
      * **기본값**: `SW_PGSQL_ASSOC`
      * **기타값**: 없음

      取值 | 반환값
      ---|---
      SW_PGSQL_ASSOC | 필드명이 키로 사용되는 연관 배열을 반환합니다.
      SW_PGSQL_NUM | 필드 번호가 키로 사용됩니다.
      SW_PGSQL_BOTH | 두 가지 모두가 키로 사용됩니다.

  * **반환값**

    * 결과의 모든 행을 배열로 반환합니다.


### affectedRows()

수정된 레코드 수를 반환합니다. 

```php
Swoole\Coroutine\PostgreSQL->affectedRows(resource $queryResult): int
```


### numRows()

행의 수를 반환합니다.

```php
Swoole\Coroutine\PostgreSQL->numRows(resource $queryResult): int
```


### fetchObject()

한 행을 객체로 추출합니다. 

```php
Swoole\Coroutine\PostgreSQL->fetchObject(resource $queryResult, int $row): object;
```

  * **예제**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    for ($row = 0; $row < $pg->numRows($result); $row++) {
        $data = $pg->fetchObject($result, $row);
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
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    while ($data = $pg->fetchObject($result, $row)) {
        echo $data->id . " \n ";
        $row++;
    }
});
```


### fetchAssoc()

한 행을 연관 배열로 추출합니다.

```php
Swoole\Coroutine\PostgreSQL->fetchAssoc(resource $queryResult, int $row): array
```


### fetchArray()

한 행을 배열로 추출합니다.

```php
Swoole\Coroutine\PostgreSQL->fetchArray(resource $queryResult, int $row, $resultType = SW_PGSQL_BOTH): array|false
```

  * **매개변수**
    * **`int $row`**
      * **기능**: `$row`는 추출하고자 하는 행(레코드)의 번호입니다. 첫 번째 행은 `0`입니다.
      * **기본값**: 없음
      * **기타값**: 없음
    * **`$resultType`**
      * **기능**: 상수입니다. 선택적 매개변수로, 반환값을 초기화하는 방식을 제어합니다.
      * **기본값**: `SW_PGSQL_BOTH`
      * **기타값**: 없음

      取值 | 반환값
      ---|---
      SW_PGSQL_ASSOC | 필드명이 키로 사용되는 연관 배열을 반환합니다.
      SW_PGSQL_NUM | 필드 번호가 키로 사용됩니다.
      SW_PGSQL_BOTH | 두 가지 모두가 키로 사용됩니다.

  * **반환값**

    * 추출한 행( Tuple/레코드)과 일치하는 배열을 반환합니다. 더 이상 추출할 수 있는 행이 없다면 `false`를 반환합니다.

  * **사용 예제**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchArray($result, 1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```
### fetchRow()

특정 `result` 자원을 기반으로 한 한 줄의 데이터(레코드)를 배열로 반환합니다. 각각의 얻어진 열은 배열에 순차적으로 저장되며, 오프셋은 `0`에서 시작합니다.

```php
Swoole\Coroutine\PostgreSQL->fetchRow(resource $queryResult, int $row, $resultType = SW_PGSQL_NUM): array|false
```

  * **매개변수**
    * **`int $row`**
      * **기능**: `row`는 얻고자 하는 행(레코드)의 번호입니다. 첫 번째 행은 `0`입니다.
      * **기본값**: 없음
      * **기타값**: 없음
    * **`$resultType`**
      * **기능**: 상수입니다. 선택적 매개변수로, 반환값을 초기화하는 방식을 제어합니다.
      * **기본값**: `SW_PGSQL_NUM`
      * **기타값**: 없음

      값 | 반환값
      --- | ---
      SW_PGSQL_ASSOC | 필드명이 키로 사용되는 연관 배열을 반환합니다.
      SW_PGSQL_NUM | 필드 번호가 키로 사용됩니다.
      SW_PGSQL_BOTH | 두 가지 모두 키로 사용됩니다.

  * **반환값**

    * 반환된 배열은 추출한 행과 일치합니다. 더 이상 추출할 수 있는 행이 없다면 `false`를 반환합니다.

  * **사용 예시**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    while ($row = $pg->fetchRow($result)) {
        echo "name: $row[0]  mobile: $row[1]" . PHP_EOL;
    }
});
```

### metaData()

테이블의 메타데이터를 확인합니다. 비동기 비블록 코어 버전입니다.

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```    
  * **사용 예시**

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

준비(preparation).

```php
Swoole\Coroutine\PostgreSQL->prepare(string $name, string $sql);
Swoole\Coroutine\PostgreSQL->execute(string $name, array $bind);
```

  * **사용 예시**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $pg->prepare("my_query", "select * from  test where id > $1 and id < $2");
    $res = $pg->execute("my_query", array(1, 3));
    $arr = $pg->fetchAll($res);
    var_dump($arr);
});
```
