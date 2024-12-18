# Swoole\Server\TaskResult

Вот подробное описание `Swoole\Server\TaskResult`.

## Свойства


### $task_id
Возвращает идентификатор `Reactor` потока, в котором было выполнено задание. Это свойство является целым числом типа `int`.

```php
Swoole\Server\TaskResult->task_id
```


### $task_worker_id
Возвращает идентификатор процесса `task`, от которого было выполнено это задание. Это свойство является целым числом типа `int`.

```php
Swoole\Server\TaskResult->task_worker_id
```


### $dispatch_time
Возвращает время отправки данных для этой связи. Это свойство может быть строкой или `null`.

```php
Swoole\Server\TaskResult->dispatch_time
```

### $data
Возвращает данные, переданные для этой связи. Это свойство является строкой.

```php
Swoole\Server\StatusInfo->data
```
