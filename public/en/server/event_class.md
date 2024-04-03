# Swoole\Server\Event

Here is a detailed introduction to `Swoole\Server\Event`.

## Properties

### $reactor_id
Returns the ID of the `Reactor` thread it belongs to. This property is an `int` type integer.

```php
Swoole\Server\Event->reactor_id
```

### $fd
Returns the file descriptor `fd` of the connection. This property is an `int` type integer.

```php
Swoole\Server\Event->fd
```

### $dispatch_time
Returns the time the request data arrived (`dispatch_time`). This property is a `double` type. This property is only non-zero in the `onReceive` event.

```php
Swoole\Server\Event->dispatch_time
```

### $data
Returns the data sent by the client (`data`). This property is a `string` type string. This property is not `null` only in the `onReceive` event.

```php
Swoole\Server\Event->data
```
