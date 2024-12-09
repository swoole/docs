# Thread Synchronization Barrier

`Thread\Barrier` is a mechanism for thread synchronization. It allows multiple threads to synchronize at a specific point, ensuring that all threads have completed their tasks before reaching a critical point (barrier). Only when all participating threads reach this barrier can they continue executing subsequent code.

For example, let's create `4` threads that we want to start executing their tasks together after all are ready. This is like the starting pistol in a race, where all runners start at the same time after the signal is given. This can be achieved using `Thread\Barrier`.

## Example
```php
use Swoole\Thread;
use Swoole\Thread\Barrier;

const N = 4;
$args = Thread::getArguments();

if (empty($args)) {
    $barrier = new Barrier(N);
    $n = N;
    $threads = [];
    while($n--) {
        $threads[] = new Thread(__FILE__, $barrier, $n);
    }
} else {
    $barrier = $args[0];
    $n = $args[1];
    // Wait for all threads to be ready
    $barrier->wait();
    echo "thread $n is running\n";
}
```

## Methods

### __construct()
Constructor

```php
Thread\Barrier()->__construct(int $count): void
```

  * **Parameters**
      * `int $count`
          * Function: The number of threads, which must be greater than `1`.
          * Default value: None.
          * Other values: None.
  
The number of threads executing the `wait` operation must match the set count; otherwise, all threads will block.

### wait()

Block and wait for other threads; once all threads are in a `wait` state, they will be awakened simultaneously and continue executing downstream.

```php
Thread\Barrier()->wait(): void
```
