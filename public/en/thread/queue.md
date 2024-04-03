# Concurrent Queue

Create a concurrent `Queue` structure that can be passed as a thread argument to child threads. Visibility in other threads is ensured when reading and writing. For detailed features, refer to [Concurrent Map](thread/map.md).


## Usage
`Thread\Queue` is a first-in, first-out data structure with two core operations:
- `Queue::push($value)` writes data to the queue
- `Queue::pop()` extracts data from the head of the queue

## Notes
- `ArrayList` can only append elements and cannot randomly delete or assign values

## Example Code

```php
use Swoole\Thread;
use Swoole\Thread\Queue;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $queue = new Queue;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = Thread::exec(__FILE__, $i, $queue);
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16)), Queue::NOTIFY_ONE);
        usleep(random_int(10000, 100000));
    }
    $n = 4;
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1];
    while (1) {
        $job = $queue->pop(-1);
        if (!$job) {
            break;
        }
        var_dump($job);
    }
}
```

## Method List

### Queue::push()

Write data to the queue

```php
function Queue::push(mixed $value, int $notify = 0);
```

- `$value`: The content of the data to be written
- `$notify`: Whether to notify the threads waiting to read the data. `Queue::NOTIFY_ONE` wakes up one thread, `Queue::NOTIFY_ALL` wakes up all threads


### Queue::pop()

Extract data from the head of the queue

```php
function Queue::pop(double $timeout = 0);
```

- `$timeout=0`: Default value, indicates no waiting. When the queue is empty, it returns `NULL` directly
- `$timeout!=0`: Waits for producers to `push()` data when the queue is empty. If `$timeout` is negative, it means no timeout

### Queue::count()
Get the number of elements in the queue

### Queue::clean()
Clear all elements
