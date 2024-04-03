# Concurrent List

Create a concurrent `List` structure that can be passed as a thread parameter to sub-threads. It should be visible to other threads when read or written. For detailed features, refer to [Concurrent Map](thread/map.md).

## Usage
`Thread\ArrayList` implements the `ArrayAccess` interface, so it can be used like an array directly.

## Notes
- `ArrayList` can only append elements and cannot perform random deletion or assignments.

## Example

```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new ArrayList;
    $thread = Thread::exec(__FILE__, $i, $list);
    sleep(1);
    $list[] = unique();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```
