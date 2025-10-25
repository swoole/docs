# Concurrent Map

The `Swoole\Thread\Map` is a thread-safe, concurrent map that allows key-value data to be shared and modified across multiple threads.
It provides a simple and efficient way to store and access data in a `multithreaded` environment without requiring manual locking.

## Features
- `Map`, `ArrayList`, and `Queue` will be automatically allocated memory, and there is no need for fixed allocations like `Table`
- Automatic locking at the underlying level for thread safety
- Supports only `null/bool/int/float/string` types; other types will be automatically serialized upon writing and deserialized upon reading
- Does not support iterators; deleting elements within an iterator may lead to memory errors
- `Map`, `ArrayList`, and `Queue` objects must be passed as thread parameters to child threads before creating the threads
- `Thread\Map` implements both the `ArrayAccess` and `Countable` interfaces, allowing array-style operations directly.

## Example

```php
use Swoole\Thread;
use Swoole\Thread\Map;

const THREAD_COUNT = 4;
const TASKS_PER_THREAD = 3;

$map = new Map();
$args = Thread::getArguments();

if (empty($args)) {
    # Parent thread
    $threads = [];
    for ($i = 0; $i < THREAD_COUNT; $i++) {
        $threads[] = new Thread(__FILE__, $i, $map);
    }

    foreach ($threads as $thread) {
        $thread->join();
    }

    foreach ($map->toArray() as $task => $status) {
        echo "{$task} => {$status}" . PHP_EOL;
    }

} else {
    # Child thread
    [$threadId, $sharedMap] = $args;

    for ($taskId = 1; $taskId <= TASKS_PER_THREAD; $taskId++) {
        sleep(1); // simulate work
        $sharedMap->add("thread_{$threadId}_task_{$taskId}", "completed");
    }
}

```

## Methods

### __construct()

Initializes the map. Optionally accepts an array to populate the map.
```php
Swoole\Thread\Map::__construct(array $array = []);
```
* **Parameters**
  * `array $array`: If an array is provided, it initializes the map with the key-value pairs.

### add()
Adds a new key-value pair to the map.
```php
Swoole\Thread\Map->add(mixed $key, mixed $value) : bool
```
* **Parameters**
  * `mixed $key`: The key to add to the Map.
  * `mixed $value`: The value to associate with the key.

* **Return Value**
  * Returns `true` the value was successfully added.
  * Returns `false`when the `$key` already exists.

### update()
Updates the value associated with a specific key in the `Map`. This method ensures thread-safe modification of existing entries.
If the key does not exist, the update will fail and return `false`.
```php
Swoole\Thread\Map->update(mixed $key, mixed $value) : bool
```
* **Parameters**
  * `mixed $key`: The key whose value needs to be updated.
  * `mixed $value`: The new value to assign to the key.

* **Return Value**
  * Returns true if the value was successfully updated.
  * Returns false if the $key does not exist.

### incr()
Safely increments a value in the Map. Supports `integer` and `float` types.
If the value is of another type, it will be automatically converted to integer, initialized to `0`, and then incremented.

```php
Swoole\Thread\Map->incr(mixed $key, mixed $value = 1) : int | float
```
* **Parameters**
  * `mixed $key`: The key to increment. If the key does not exist, it will be automatically created and initialized to 0.
  * `mixed $value`: The amount to increment by. Default is `1`.

* **Return Value**
  * Returns the `value` after incrementing.

### decr()
Safely decrements a value in the Map. Supports integer and float types.
If the value is of another type, it will be automatically converted to integer, initialized to 0, and then decremented.

```php
Swoole\Thread\Map->decr(mixed $key, mixed $value = 1) : int | float
```
* **Parameters**
  * `mixed $key`: The key to decrement. If the key does not exist, it will be automatically created and initialized to 0.
  * `mixed $value`: The amount to decrement by. Default is 1.

* **Return Value**
  * Returns the value after decrementing.

### count()
Returns the total number of elements currently stored in the Map.
```php
Swoole\Thread\Map()->count(): int
```

### keys()
Retrieve all `keys` in the Map.
```php
Swoole\Thread\Map()->keys(): array
```

### values()
Retrieve all values in the Map.
```php
Swoole\Thread\Map()->values(): array

```

### toArray()
Converts the Map into a standard PHP associative `array` containing all key-value pairs.
```php
use Swoole\Thread\Map;

$map = new Map();

# Inspect the contents of a Map
$array = $map->toArray();
print_r($array);
```

### clean()
Clear all elements from the Map. No return value. After calling this method, the Map will be empty.
```php
Swoole\Thread\Map()->clean(): void
```

### sort()
Sort the elements in the Map by value while preserving the key-value association. The sorting behavior is consistent with PHP's `asort()` function.
```php
Swoole\Thread\Map()->sort(): void
```

### offsetGet()
Retrieve a value from the Map using the specified offset.
```php
Swoole\Thread\Map()->offsetGet(mixed $offset): mixed
```

* **Parameters**
  * `mixed $offset`: The key or offset whose value you want to retrieve from the Map.

* **Return Value**
  * Returns the value associated with the specified offset if it exists.
  * Returns `NULL` if the offset does not exist in the Map.

### offsetSet()
Set a value in the Map at the specified offset.
```php
Swoole\Thread\Map()->offsetSet(mixed $key, mixed $value): void
```
* **Parameters**
  * `mixed $key`: The key or offset to assign the value to.
  * `mixed $value`: The value to set at the specified key.

### offsetUnset()
Offset to unset.
```php
Swoole\Thread\Map()->offsetUnset(mixed $key): void
```
* **Parameters**
  * `mixed $key`: The key or offset to remove from the Map.

### offsetExists()
Check whether a key or offset exists in the Map.
```php
Swoole\Thread\Map()->offsetExists(mixed $key): bool
```
* **Parameters**
  * `mixed $key`: The key or offset to check for existence in the Map.

* **Return Value**
  * Returns true if the key exists.
  * Returns false if the key does not exist.

### find()
Find the first key in the Map that matches a given value. If multiple keys have the same value, only the first matching key is returned.
```php
Swoole\Thread\Map->find(mixed $value): mixed
```
* **Parameters**
  * `mixed $value`: The value to search for in the Map.

* **Return Value**
  * Returns the first key whose value equals the specified value.
  * Returns `NULL` if no matching value is found.
