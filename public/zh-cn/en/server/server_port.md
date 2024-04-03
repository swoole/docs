# Swoole\Server\Port

Here is a detailed introduction to `Swoole\Server\Port`.

## Properties

### $host
Returns the host address being listened on, this property is a `string` type.

```php
Swoole\Server\Port->host
```

### $port
Returns the host port being listened on, this property is an `int` type.

```php
Swoole\Server\Port->port
```

### $type
Returns the type of this `server`. This property is an enumeration, and it returns one of `SWOOLE_TCP`, `SWOOLE_TCP6`, `SWOOLE_UDP`, `SWOOLE_UDP6`, `SWOOLE_UNIX_DGRAM`, `SWOOLE_UNIX_STREAM`.

```php
Swoole\Server\Port->type
```

### $sock
Returns the socket being listened on, this property is an `int` type.

```php
Swoole\Server\Port->sock
```

### $ssl
Returns whether SSL encryption is enabled, this property is a `bool` type.

```php
Swoole\Server\Port->ssl
```

### $setting
Returns the settings for this port, this property is an `array`.

```php
Swoole\Server\Port->setting
```

### $connections
Returns all connections to this port, this property is an iterator.

```php
Swoole\Server\Port->connections
```

## Methods

### set()

Used to set various parameters for `Swoole\Server\Port` at runtime, usage is the same as [Swoole\Server->set()](/server/methods?id=set).

```php
Swoole\Server\Port->set(array $setting): void
```

### on()

Used to set callback functions for `Swoole\Server\Port`, usage is the same as [Swoole\Server->on()](/server/methods?id=on).

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```

### getCallback()

Returns the set callback function.

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

  * **Parameters**

    * `string $name`

      * Function: Name of the callback event
      * Default Value: None
      * Other Values: None

  * **Return Value**

    * Returns the callback function if successful, returns `null` if the callback function does not exist.


### getSocket()

Converts the current socket `fd` into a PHP `Socket` object.

```php
Swoole\Server\Port->getSocket(): Socket|false
```

  * **Return Value**

    * Returns a `Socket` object if successful, returns `false` if unsuccessful.

!> Note that this function can only be used if the `--enable-sockets` flag was enabled during the compilation of Swoole.
