# 데이터 유형
여기에는 스레드 간에 전달하고 공유할 수 있는 데이터 유형이 나와 있습니다.


## 기본 유형
`null/bool/int/float` 유형의 변수는 메모리 크기가 `16 Bytes` 미만으로, 값으로 전달됩니다.


## 문자열
문자열은 **메모리 복사**를 하여 `ArrayList`, `Queue`, `Map`에 저장합니다.


## 소켓 자원




### 지원되는 유형 리스트

- `Co\Socket`

- `PHP Stream`
- `PHP Socket(ext-sockets)`은 `--enable-sockets` 컴파일 매개변수를 활성화해야 합니다.




### 지원하지 않는 유형

- `Swoole\Client`

- `Swoole\Server`

- `Swoole\Coroutine\Client`

- `Swoole\Coroutine\Http\Client`

- `pdo` 연결

- `redis` 연결
- 기타 특수 `Socket` 자원 유형


### 자원 복제



- 쓰기 시 `dup(fd)` 작업을 수행하여 기존 자원과 분리하고, 서로 영향을 주지 않으며, 기존 자원에 대한 `close` 작업은 새로운 자원에 영향을 미치지 않습니다.

- 읽기 시 `dup(fd)` 작업을 수행하여 읽는 서브스레드 `VM` 내에서 새로운 `Socket` 자원을 구축합니다.
- 삭제 시 `close(fd)` 작업을 수행하여 파일 핸들을 해제합니다.


이는 `Socket` 자원이 `3` 개의 참조 카운트를 유지한다는 것을 의미합니다. 각각은 다음과 같습니다:

- `Socket` 자원이 처음 생성될 때所在的스레드

- `ArrayList`, `Queue`, `Map` 컨테이너
- `ArrayList`, `Queue`, `Map` 컨테이너를 읽는 서브스레드

해당 자원이 어떠한 스레드나 컨테이너에 의해 참조되지 않을 때, 참조 카운트가 `0`이 되면 `Socket` 자원이 실제로 해제됩니다. 참조 카운트가 `0`이 아닐 때,
비록 `close` 작업을 수행했을지라도 연결은 닫히지 않으며, 다른 스레드나 데이터 컨테이너가 유지하는 `Socket` 자원에 영향을 미치지 않습니다.


참조 카운트를 무시하고 직접 `Socket`를 닫고 싶다면, `shutdown()` 메서드를 사용할 수 있습니다. 예를 들어:

- `stream_socket_shutdown()`

- `Socket::shutdown()`
- `socket_shutdown()`

> `shutdown` 작업은 모든 스레드가 유지하는 `Socket` 자원에 영향을 미치며, 실행 후에는 더 이상 사용할 수 없으며, `read/write` 작업을 수행할 수 없습니다.


## 배열
배열의 유형을 판단하기 위해 `array_is_list()`을 사용합니다. 숫자 인덱스 배열은 `ArrayList`로 변환되고, 연관 인덱스 배열은 `Map`로 변환됩니다.



- 전체 배열을 탐색하여 요소를 `ArrayList` 또는 `Map`에 삽입합니다
- 다차원 배열을 지원하며, 다차원 배열을 재귀적으로 탐색하여 중첩된 구조의 `ArrayList` 또는 `Map`로 변환합니다

예시:
```php
$array = [
    'a' => random_int(1, 999999999999999999),
    'b' => random_bytes(128),
    'c' => uniqid(),
    'd' => time(),
    'e' => [
        'key' => 'value',
        'hello' => 'world',
    ];
];

$map = new Map($array);

// $map['e']는 새로운 Map 객체로, 두 개의 요소를 포함하고, key와 hello의 값은 'value'와 'world'입니다
var_dump($map['e']);
```


## 객체

### 스레드 자원 객체

`Thread\Lock`, `Thread\Atomic`, `Thread\ArrayList`, `Thread\Map` 등의 스레드 자원 객체는 직접 `ArrayList`, `Queue`, `Map`에 저장될 수 있습니다.
이 작업은 단지 객체의 참조를 컨테이너에 저장하는 것으로, 객체의 복제를 하지 않습니다.

객체를 `ArrayList` 또는 `Map`에 쓸 때는 스레드 자원에 참조 카운트를 한 번 증가시키는 것으로, 복제하지 않습니다. 객체의 참조 카운트가 `0`이 되면 해제됩니다.

예시:

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // 현재 참조 카운트는 1입니다
$map['lock'] = $lock; // 현재 참조 카운트는 2입니다
unset($map['lock']); // 현재 참조 카운트는 1입니다
unset($lock); // 현재 참조 카운트는 0이 되며, Lock 객체가 해제됩니다
```

지원되는 리스트:



- `Thread\Lock`

- `Thread\Atomic`

- `Thread\Atomic\Long`

- `Thread\Barrier`

- `Thread\ArrayList`

- `Thread\Map`
- `Thread\Queue`

주의하세요 `Thread` 스레드 객체는 직렬화할 수 없으며 전달할 수도 없으며, 부모 스레드에서만 사용할 수 있습니다.

### 일반 PHP 객체
쓰기 시 자동으로 직렬화되고, 읽기 시 반직렬화됩니다. 객체에 직렬화할 수 없는 유형이 포함되어 있다면 예외가 발생할 수 있습니다.
