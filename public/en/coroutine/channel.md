# Coroutine\Channel

> It is recommended to refer to the [overview](/coroutine) first to understand some basic concepts of coroutine before reading this section.

Channel is used for communication between coroutines, supporting multiple producer coroutines and multiple consumer coroutines. The underlying system automatically handles coroutine switching and scheduling.
## Implementation Principle

- Channels are similar to the `Array` class in `PHP`, occupying only memory without any additional resource allocation. All operations are memory operations with no I/O overhead.
- The underlying implementation uses PHP reference counting, avoiding memory copies. Even passing huge strings or arrays does not result in extra performance overhead.
- Channels are zero-copy based on reference counting.
## Usage Example

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;

run(function(){
    $channel = new Channel(1);
    Coroutine::create(function () use ($channel) {
        for($i = 0; $i < 10; $i++) {
            Coroutine::sleep(1.0);
            $channel->push(['rand' => rand(1000, 9999), 'index' => $i]);
            echo "{$i}\n";
        }
    });
    Coroutine::create(function () use ($channel) {
        while(1) {
            $data = $channel->pop(2.0);
            if ($data) {
                var_dump($data);
            } else {
                assert($channel->errCode === SWOOLE_CHANNEL_TIMEOUT);
                break;
            }
        }
    });
});
```
## Methods
### __construct()

Channel constructor method.

```php
Swoole\Coroutine\Channel::__construct(int $capacity = 1)
```

  * **Parameters** 

    * **`int $capacity`**
      * **Functionality**: set the capacity [must be an integer greater than or equal to `1`]
      * **Default value**: `1`
      * **Other values**: N/A

!> The underlying uses PHP reference counting to save variables, the cache area only needs to occupy `$capacity * sizeof(zval)` bytes of memory, where `zval` is `16` bytes in PHP7. For example, when `$capacity = 1024`, `Channel` will occupy a maximum of `16K` memory.

!> It must be created after [onWorkerStart](/server/events?id=onworkerstart) when used in `Server`.
### push()

Write data to the channel.

```php
Swoole\Coroutine\Channel->push(mixed $data, float $timeout = -1): bool
```

  * **Parameters**

    * **`mixed $data`**
      * **Function**: push data 【Can be any type of PHP variable, including anonymous functions and resources】
      * **Default**: None
      * **Other values**: None

      !> To avoid ambiguity, do not write empty data to the channel, such as `0`, `false`, `empty string`, `null`

    * **`float $timeout`**
      * **Function**: set timeout
      * **Unit**: seconds 【Supports floating point numbers, e.g., `1.5` represents `1s`+`500ms`】
      * **Default**: `-1`
      * **Other values**: None
      * **Version Impact**: Swoole version >= v4.2.12

      !> When the channel is full, `push` will suspend the current coroutine. If there are no consumers to consume the data within the specified time, a timeout will occur. The underlying system will resume the current coroutine, and the `push` call will immediately return `false`, indicating a write failure.

  * **Return Value**

    * Returns `true` on success
    * Returns `false` on failure when the channel is closed. You can use `$channel->errCode` to get the error code.

  * **Extensions**

    * **Channel Full**

      * Automatically `yield` the current coroutine. Once other consumer coroutines `pop` data, space becomes available for writing, and the current coroutine is `resume`-d.
      * When multiple producer coroutines `push` simultaneously, the underlying system automatically queues them and `resume`s them in order.

    * **Channel Empty**

      * Automatically wakes up one of the consumer coroutines.
      * When multiple consumer coroutines `pop` simultaneously, the underlying system queues them and `resume`s them in order.

!> `Coroutine\Channel` uses local memory, and memory is isolated between different processes. You can only perform `push` and `pop` operations within different coroutines of the same process.
### pop()

Reads data from the channel.

```php
Swoole\Coroutine\Channel->pop(float $timeout = -1): mixed
```

  * **Parameters** 

    * **`float $timeout`**
      * **Function**：Set the timeout
      * **Unit**：Seconds【Supports floating point numbers, such as `1.5` which represents `1s`+`500ms`】
      * **Default**：`-1`【Indicates no timeout】
      * **Other values**：None
      * **Version Impact**：Swoole version >= v4.0.3

  * **Return Values**

    * The return value can be of any PHP variable types, including anonymous functions and resources.
    * When the channel is closed, failure to execute returns `false`.

  * **Extensions**

    * **Channel is Full**

      * After consuming data with `pop`, it will automatically wake up one of the producer coroutines to write new data.
      * When multiple producer coroutines `push` data concurrently, the underlying system automatically queues them and resumes these producer coroutines in order.

    * **Channel is Empty**

      * Automatically `yield` the current coroutine. When other producer coroutines `push` data to produce, the channel becomes readable, and it will resume the current coroutine again.
      * When multiple consumer coroutines `pop` concurrently, the underlying system automatically queues them and resumes them in order.
### stats()

Get the status of the channel.

```php
Swoole\Coroutine\Channel->stats(): array
```

  * **Return Value**

    Returns an array, buffered channels include `4` items of information, unbuffered channels return `2` items of information
    
    - `consumer_num` Number of consumers, indicates the current channel is empty, `N` coroutines are waiting for other coroutines to call the `push` method to produce data
    - `producer_num` Number of producers, indicates the current channel is full, `N` coroutines are waiting for other coroutines to call the `pop` method to consume data
    - `queue_num` Number of elements in the channel

```php
array(
  "consumer_num" => 0,
  "producer_num" => 1,
  "queue_num" => 10
);
```
### close()

Close the channel and wake up all coroutines waiting for read and write operations.

```php
Swoole\Coroutine\Channel->close(): bool
```

!> Wake up all producer coroutines, causing `push` method to return `false`; wake up all consumer coroutines, causing `pop` method to return `false`
### length()

Get the number of elements in the channel.

```php
Swoole\Coroutine\Channel->length(): int
```
### isEmpty()

Determine whether the current channel is empty.

```php
Swoole\Coroutine\Channel->isEmpty(): bool
```
### isFull()

Determine if the current channel is full.

```php
Swoole\Coroutine\Channel->isFull(): bool
```
## Properties
### capacity

Capacity of the channel buffer.

The capacity set in the [constructor](/coroutine/channel?id=__construct) will be stored here, but **if the set capacity is less than 1**, this variable will be equal to 1.

```php
Swoole\Coroutine\Channel->capacity: int
```
### errCode

Get the error code.

```php
Swoole\Coroutine\Channel->errCode: int
```

  * **Return Values**

Value | Corresponding Constant | Description
---|---|---
0 | SWOOLE_CHANNEL_OK | Default success
-1 | SWOOLE_CHANNEL_TIMEOUT | Pop failed due to timeout
-2 | SWOOLE_CHANNEL_CLOSED | The channel is closed, continuing to operate on the channel
