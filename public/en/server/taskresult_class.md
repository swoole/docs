# Swoole\Server\TaskResult

This is a detailed introduction to `Swoole\Server\TaskResult`.

## Properties

### $task_id
Returns the id of the `Reactor` thread where the task is located. This property is an integer of type `int`.

```php
Swoole\Server\TaskResult->task_id
```

### $task_worker_id
Returns the id of the `task` process from which the execution result comes. This property is an integer of type `int`.

```php
Swoole\Server\TaskResult->task_worker_id
```

### $dispatch_time
Returns the data carried by the connection, which is of type `?string`.

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
Returns the data carried by the connection, which is of type `string`.

```php
Swoole\Server\StatusInfo->data
```
