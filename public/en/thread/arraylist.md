# Thread-Safe Concurrent Container: ArrayList

Creates a concurrent ArrayList structure that can be passed as a parameter to child threads. Read and write operations are visible across threads.

## Features
- `Map`, `ArrayList`, and `Queue` automatically allocate memory and do not require fixed sizing like Table.
- Built-in locking ensures thread safety at the underlying level.
- Supported variable types are listed in the Data Types documentation.
- Iterators are not supported. Use `toArray()` as an alternative.
- `Map`, `ArrayList`, and `Queue` objects must be passed to child threads as parameters before thread creation.
- `Thread\ArrayList` implements both the `ArrayAccess` and `Countable` interfaces, allowing array-style operations.
- Only numeric index access is supported. Non-numeric keys will be forcibly cast to integers.

## Example

```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();

if (empty($args)) {
    $list = new ArrayList();
    $thread = new Thread(__FILE__, 0, $list);
    sleep(1);
    $list[] = uniqid();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```

## Methods

### __construct()

Initializes the ArrayList. Optionally accepts a list-type array to populate the container.
```php
Swoole\Thread\ArrayList::__construct(array $values = [])
```
* **Parameters**
  * `array $valus`: Must be a list-type array (not associative). Use array_values() to convert associative arrays if needed.

### incr()

Atomically increments the value at a given index. If the index does not exist, it is initialized to 0.
```php
Swoole\Thread\ArrayList::incr(int $index, mixed $value = 1): int|float
```
* **Parameters**
  * `int $index`: Must be a valid numeric index; otherwise, an exception is thrown.
  * `mixed $value`: The amount to increment by (default is 1).

### decr()

Atomically decrements the value at a given index. If the index does not exist, it is initialized to 0.
```php
Swoole\Thread\ArrayList::decr(int $index, mixed $value = 1): int|float
```
* **Parameters**
  * `int $index`: Must be a valid numeric index; otherwise, an exception is thrown.
  * `mixed $value`: The amount to decrement by (default is 1).

### count()

Returns the number of elements in the ArrayList.
```php
Swoole\Thread\ArrayList::count(): int
```

* **Return Value**
  * `int`: Total number of elements.

### toArray()

Converts the ArrayList to a standard PHP array.
```php
Swoole\Thread\ArrayList::toArray(): array
```

* **Return Value**
  * `array`: All elements in the list.

### clean()

Removes all elements from the ArrayList.
```php
Swoole\Thread\ArrayList::clean(): void
```

### sort()

Sorts the elements in the ArrayList by value. Behavior is consistent with PHP’s `sort()`.
```php
Swoole\Thread\ArrayList::sort(): void
```
