# Concurrent Map

Create a concurrent `Map` structure that can be passed as a thread parameter to a child thread. It should be visible in other threads during read and write operations.

## Features
- `Map`, `ArrayList`, and `Queue` will be automatically allocated memory, and there is no need for fixed allocations like `Table`
- Automatic locking at the underlying level for thread safety
- Supports only `null/bool/int/float/string` types; other types will be automatically serialized upon writing and deserialized upon reading
- Does not support iterators; deleting elements within an iterator may lead to memory errors
- `Map`, `ArrayList`, and `Queue` objects must be passed as thread parameters to child threads before creating the threads

## Usage
`Thread\Map` implements the `ArrayAccess` interface, allowing it to be used as an array operation directly.

## Example

```php
use Swoole\Thread;
use Swoole\Thread\Map;

$args = Thread::getArguments();
if (empty($args)) {
    $map = new Map;
    $thread = Thread::exec(__FILE__, $i, $map);
    sleep(1);
    $map['test'] = unique();
    $thread->join();
} else {
    $map = $args[1];
    sleep(2);
    var_dump($map['test']);
}
```

## Methods

### Map::count()
Get the number of elements

### Map::keys()
Return all `keys`

### Map::clean()
Clear all elements
