# Swoole\Server\Task

Here is a detailed introduction to `Swoole\Server\Task`. This class is very simple, but you cannot obtain a `Task` object by using `new Swoole\Server\Task()`. This object does not contain any server information, and executing any method of `Swoole\Server\Task` will result in a fatal error.

```shell
Invalid instance of Swoole\Server\Task in /home/task.php on line 3
```

## Properties

### $data
The data `data` passed from the `worker` process to the `task` process. This property is a string type.

```php
Swoole\Server\Task->data
```

### $dispatch_time
Returns the time `dispatch_time` when the data reaches the `task` process. This property is of type double.

```php
Swoole\Server\Task->dispatch_time
```

### $id
Returns the time `dispatch_time` when the data reaches the `task` process. This property is an integer type.

```php
Swoole\Server\Task->id
```

### $worker_id
Returns from which `worker` process the data comes. This property is an integer type.

```php
Swoole\Server\Task->worker_id
```

### $flags
Some flag information `flags` of the asynchronous task. This property is an integer type.

```php
Swoole\Server\Task->flags
```

?> The results returned by `flags` are of the following types:
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCK indicates that this is not sent from the `Worker` process to the `task` process. If `Swoole\Server::finish()` is called in the `onTask` event, a warning will be issued.
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCK indicates that the last callback function in `Swoole\Server::finish()` is not null, and the `onFinish` event will not be executed, only this callback function will be executed.
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCK indicates that the task will be processed by coroutine.
  - SW_TASK_NONBLOCK is the default value when none of the above three conditions are met.

## Methods

### finish()

Used to notify the `Worker` process in the [Task process](/learn?id=taskworker-process) that the dispatched task has been completed. This function can pass result data to the `Worker` process.

```php
Swoole\Server\Task->finish(mixed $data): bool
```

  * **Parameters**

    * `mixed $data`

      * Functionality: Result content of the task processing
      * Default value: None
      * Other values: None

  * **Tips**
    * The `finish` method can be called multiple times in a row, and the `Worker` process will trigger the [onFinish](/server/events?id=onfinish) event multiple times.
    * After calling the `finish` method in the `onTask` callback function, the `return` data will still trigger the [onFinish](/server/events?id=onfinish) event.
    * `Swoole\Server\Task->finish` is optional. If the `Worker` process does not care about the task execution result, this function does not need to be called.
    * In the [onTask](/server/events?id=ontask) callback function, `return` a string is equivalent to calling `finish`.

  * **Note**

  !> The `Swoole\Server\Task->finish` function must have the [onFinish](/server/events?id=onfinish) callback function set for the `Server`. This function can only be used in the [Task process](/learn?id=taskworker-process)'s [onTask](/server/events?id=ontask) callback.

### pack()

Serialize the given data.

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

  * **Parameters**

    * `mixed $data`

      * Functionality: Result content of the task processing
      * Default value: None
      * Other values: None

  * **Return value**
    * Returns the serialized result if successful.

### unpack()

Deserialize the given data.

```php
Swoole\Server\Task->unpack(string $data): mixed
```

  * **Parameters**

    * `string $data`

      * Functionality: Data to be deserialized
      * Default value: None
      * Other values: None

  * **Return value**
    * Returns the deserialized result if successful.

## Usage Example
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
