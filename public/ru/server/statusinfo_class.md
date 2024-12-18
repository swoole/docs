# Swoole\Server\StatusInfo

В этой статье приводится подробная информация об `Swoole\Server\StatusInfo`.

## Свойства


### $worker_id
Возвращает идентификатор текущего `worker` процесса, это свойство является целым числом типа `int`.

```php
Swoole\Server\StatusInfo->worker_id
```


### $worker_pid
Возвращает идентификатор родительского процесса текущего `worker`, это свойство является целым числом типа `int`.

```php
Swoole\Server\StatusInfo->worker_pid
```


### $status
Возвращает состояние процесса `status`, это свойство является целым числом типа `int`.

```php
Swoole\Server\StatusInfo->status
```


### $exit_code
Возвращает кода выхода процесса `exit_code`, это свойство является целым числом типа `int` и может варьироваться от `0` до `255`.

```php
Swoole\Server\StatusInfo->exit_code
```

### $signal
Сигнал о завершении процесса `signal`, это свойство является целым числом типа `int`.
