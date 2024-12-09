# Swoole\Server\TaskResult

Hier ist eine detaillierte Beschreibung von `Swoole\Server\TaskResult`.

## Eigenschaften


### $task_id
Gibt die ID des `Reactor`-Threads zurück, in dem die Aufgabe ausgeführt wurde. Diese Eigenschaft ist ein `int`-Zahl.

```php
Swoole\Server\TaskResult->task_id
```


### $task_worker_id
Gibt die ID des `task`-Prozesses zurück, aus dem das Ausführungsergebnis stammt. Diese Eigenschaft ist ein `int`-Zahl.

```php
Swoole\Server\TaskResult->task_worker_id
```


### $dispatch_time
Gibt die mit der Verbindung übertragene Daten `data` zurück. Diese Eigenschaft ist ein optionaler `string`.

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
Gibt die mit der Verbindung übertragene Daten `data` zurück. Diese Eigenschaft ist ein `string`.

```php
Swoole\Server\StatusInfo->data
```
