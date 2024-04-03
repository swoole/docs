# Swoole\Server\PipeMessage

Here is a detailed introduction to `Swoole\Server\PipeMessage`.

## Properties

### $source_worker_id
Returns the `worker` process id of the data source, and this property is an `int` type integer.

```php
Swoole\Server\PipeMessage->source_worker_id
```

### $dispatch_time
Returns the arrival time `dispatch_time` of the request data, and this property is a `double` type.

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
Returns the data carried by the connection `data`, and this property is a `string` type string.

```php
Swoole\Server\PipeMessage->data
```
