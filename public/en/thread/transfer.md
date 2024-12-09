# Data Types
Here are the data types that can be passed and shared between threads.


## Basic Types
Variables of `null/bool/int/float` types, with a memory size of less than `16 Bytes`, are passed as values.


## Strings
Strings are **memory copied**, stored in `ArrayList`, `Queue`, `Map`.


## Socket Resources



### Supported Types List

- `Co\Socket`

- `PHP Stream`
- `PHP Socket(ext-sockets)`, which requires the `--enable-sockets` compilation parameter to be enabled



### Unsupported Types

- `Swoole\Client`

- `Swoole\Server`

- `Swoole\Coroutine\Client`

- `Swoole\Coroutine\Http\Client`

- `pdo` connections

- `redis` connections
- Other special `Socket` resource types


### Resource Duplication



- During writing, a `dup(fd)` operation is performed to separate from the original resource, without affecting each other. Closing the original resource will not affect the new resource.

- During reading, a `dup(fd)` operation is performed to build a new `Socket` resource within the reading child thread's `VM`.
- During deletion, a `close(fd)` operation is performed to release the file handle.


This means that `Socket` resources will have three references:

- The thread where the `Socket` resource was initially created

- The `ArrayList`, `Queue`, `Map` container

- The child thread reading from the `ArrayList`, `Queue`, `Map` container

The `Socket` resource will only be truly released when there are no threads or containers holding it, and the reference count drops to `0`. Even if a `close` operation is performed when the reference count is not `0`, the connection will not be closed, and it will not affect other threads or data containers holding the `Socket` resource.


If you wish to ignore the reference count and directly close the `Socket`, you can use the `shutdown()` method, for example:

- `stream_socket_shutdown()`

- `Socket::shutdown()`
- `socket_shutdown()`

> `shutdown` operations will affect all threads holding the `Socket` resource and will be unusable after execution, preventing `read/write` operations.


## Arrays
Use `array_is_list()` to determine if an array is a list. If it is a numerically indexed array, it is converted to `ArrayList`, and an associative indexed array is converted to `Map`.



- The entire array will be traversed, and elements will be inserted into `ArrayList` or `Map`
- Multidimensional arrays are supported, and recursive traversal converts them into nested `ArrayList` or `Map` structures

Example:
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

// $map['e'] is a new Map object containing two elements, key and hello, with values 'value' and 'world'
var_dump($map['e']);
```


## Objects

### Thread Resource Objects

Thread resource objects such as `Thread\Lock`, `Thread\Atomic`, `Thread\ArrayList`, `Thread\Map`, etc., can be directly stored in `ArrayList`, `Queue`, `Map`.
This operation merely stores a reference to the object in the container and does not perform a copy of the object.

When writing an object to `ArrayList` or `Map`, it only increments the reference count for the thread resource by one and does not copy it. When the reference count of an object reaches `0`, it will be released.

Example:

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // Current reference count is 1
$map['lock'] = $lock; // Current reference count is 2
unset($map['lock']); // Current reference count is 1
unset($lock); // Current reference count is 0, Lock object is released
```

Supported lists:



- `Thread\Lock`

- `Thread\Atomic`

- `Thread\Atomic\Long`

- `Thread\Barrier`

- `Thread\ArrayList`

- `Thread\Map`
- `Thread\Queue`

Note that `Thread` thread objects are neither serializable nor transmissible and are only available in the parent thread.

### Regular PHP Objects
They will be automatically serialized when written and deserialized when read. Please note that if an object contains unserializable types, an exception will be thrown.
