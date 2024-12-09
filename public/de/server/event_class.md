# Swoole\Server\Event

Hier ist eine detaillierte Beschreibung von `Swoole\Server\Event`.

## Eigenschaften


### $reactor_id
Gibt die ID des `Reactor`-Threads zurück, zu dem dieser Ereignis gehört. Diese Eigenschaft ist ein `int`-Zahl.

```php
Swoole\Server\Event->reactor_id
```


### $fd
Gibt den Dateideskriptor `fd` der Verbindung zurück. Diese Eigenschaft ist ein `int`-Zahl.

```php
Swoole\Server\Event->fd
```


### $dispatch_time
Gibt die Zeit zurück, zu der das Request-Daten到达. Diese Eigenschaft ist ein `double`. Nur in der `onReceive`-Ereignis ist diese Eigenschaft nicht `0`.

```php
Swoole\Server\Event->dispatch_time
```

### $data
Gibt die von dem Client gesendeten Daten `data` zurück. Diese Eigenschaft ist ein `string`. Nur in der `onReceive`-Ereignis ist diese Eigenschaft nicht `null`.
