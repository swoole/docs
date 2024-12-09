# Swoole\Server\StatusInfo

Hier ist eine detaillierte Beschreibung von `Swoole\Server\StatusInfo`.

## Eigenschaften


### $worker_id
Gibt die ID des aktuellen `worker` Prozesses zurück, dieser Eigenschaft ist ein `int`类型的 Integer.

```php
Swoole\Server\StatusInfo->worker_id
```


### $worker_pid
Gibt die ID des Elternprozesses des aktuellen `worker` Prozesses zurück, dieser Eigenschaft ist ein `int`类型的 Integer.

```php
Swoole\Server\StatusInfo->worker_pid
```


### $status
Gibt den Prozessstatus `status` zurück, dieser Eigenschaft ist ein `int`类型的 Integer.

```php
Swoole\Server\StatusInfo->status
```


### $exit_code
Gibt den Prozessexit-Statuscode `exit_code` zurück, dieser Eigenschaft ist ein `int`类型的 Integer, der einen Bereich von `0-255` hat.

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
Die vom Prozess gesendete Signal `signal`, dieser Eigenschaft ist ein `int`类型的 Integer.

```php
Swoole\Server\StatusInfo->signal
```
