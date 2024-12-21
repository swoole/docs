# Типы данных
Здесь перечислены типы данных, которые могут быть переданы и поделены между нитями.

## Основные типы
Переменные типов `null/bool/int/float`, размер памяти меньше `16 Bytes`, передаются как значения.

## Строки
Для строк выполняется **памятьное копирование**, хранятся в `ArrayList`, `Queue`, `Map`.

## Ресурсы сокетов

### Список поддерживаемых типов

- `Co\Socket`

- `PHP Stream`
- `PHP Socket(ext-sockets)`, необходимо включить параметры `--enable-sockets` при сборке

### Не поддерживаемые типы

- `Swoole\Client`

- `Swoole\Server`

- `Swoole\Coroutine\Client`

- `Swoole\Coroutine\Http\Client`

- соединения `pdo`

- соединения `redis`
- другие специальные виды ресурсов сокетов

### Копирование ресурсов

- При написании выполняется операция `dup(fd)`, что отделяет ресурсы друг от друга, не влияя друг на друга, и операции по закрытию исходного ресурса не будут влиять на новый ресурс

- При чтении выполняется операция `dup(fd)`, в дочернем потоке `VM` строится новый ресурс сокета
- При удалении выполняется операция `close(fd)`, освобождается файловый дескриптор

Это означает, что ресурс сокета будет иметь `3` ссылки:

- нить, в которой был создан ресурс сокета

- Containers `ArrayList`, `Queue`, `Map`

- Дочерние нити, читающие из `ArrayList`, `Queue`, `Map`

Когда никакая нить или контейнер не держит этот ресурс, количество ссылок уменьшается до `0`, и только тогда ресурс сокета будет действительно освобожден. Когда количество ссылок не равно `0`, даже если выполнена операция `close`, соединение не будет закрыто и не повлияет на другие нити или контейнеры, держащие ресурс сокета.

Если вы хотите игнорировать количество ссылок и напрямую закрыть сокет, используйте метод `shutdown()`, например:

- `stream_socket_shutdown()`

- `Socket::shutdown()`
- `socket_shutdown()`

> Операция `shutdown` повлияет на все сокеты, держащиеся в нитях, и после ее выполнения они больше не будут доступны для чтения/записи

## Массивы
Используйте функцию `array_is_list()` для определения типа массива, если это числовые индексированные массивы, они превращаются в `ArrayList`, а ассоциативные индексированные массивы - в `Map`.

- Будет пройдена вся массив, элементы будут вставлены в `ArrayList` или `Map`
- Поддерживается многомерный массив, рекурсивное обход многомерного массива превращает его в вложенную структуру `ArrayList` или `Map`

Пример:
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

// $map['e'] - это новый объект Map, содержащий два элемента, key и hello, с значениями 'value' и 'world'
var_dump($map['e']);
```

## Объекты

### Объекты ресурсов нитей

Объекты ресурсов нитей, такие как `Thread\Lock`, `Thread\Atomic`, `Thread\ArrayList`, `Thread\Map`, могут быть напрямую хранимы в `ArrayList`, `Queue`, `Map`.
Это действие просто сохраняет ссылку на объект в контейнере, не делая копию объекта.

При написании объекта в `ArrayList` или `Map` увеличивается счетчик ссылок на ресурс нити, но не создается копия. Когда счетчик ссылок на объект достигает `0`, он освобождается.

Пример:

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // текущий счетчик ссылок - 1
$map['lock'] = $lock; // текущий счетчик ссылок - 2
unset($map['lock']); // текущий счетчик ссылок - 1
unset($lock); // текущий счетчик ссылок - 0, объект Lock освобождается
```

Поддерживаемый список:

- `Thread\Lock`

- `Thread\Atomic`

- `Thread\Atomic\Long`

- `Thread\Barrier`

- `Thread\ArrayList`

- `Thread\Map`
- `Thread\Queue`

Обратите внимание, что объекты нитей `Thread` не могут быть序列изованы и переданы, они доступны только в родительской нити.

### Обычные PHP объекты
При написании автоматически序列изируются, при чтении - десериализуются. Обратите внимание, что если объект содержит нес序列изируемые типы, будет выброшено исключение.