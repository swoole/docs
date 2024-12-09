# Swoole\Server\PipeMessage

Hier ist eine detaillierte Beschreibung von `Swoole\Server\PipeMessage`.

## Eigenschaften


### $source_worker_id
Gibt die ID des `worker` Prozesses zurück, von dem die Daten stammen, dieser Eigenschaft ist ein integer-Wert.

```php
Swoole\Server\PipeMessage->source_worker_id
```


### $dispatch_time
Gibt die Zeit zurück, zu der das Request-Daten erreicht hat `dispatch_time`, diese Eigenschaft ist ein double-Wert.

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
Gibt die mit dieser Verbindung transportierten Daten `data` zurück, diese Eigenschaft ist ein string-Wert.

```php
Swoole\Server\PipeMessage->data
```
