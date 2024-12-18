# Сwoole\Server\PipeMessage

Вот подробное описание `Swoole\Server\PipeMessage`.

## Свойства

### $source_worker_id
Возвращает идентификатор `worker`进程, от которого пришел данные, это свойство является целым числом типа `int`.

```php
Swoole\Server\PipeMessage->source_worker_id
```

### $dispatch_time
Возвращает время прибытия данных этого запроса `dispatch_time`, это свойство является числом двойного типа.

```php
Swoole\Server\PipeMessage->dispatch_time
```

### $data
Возвращает данные, несущиеся по этой связи `data`, это свойство является строкой типа `string`.

```php
Swoole\Server\PipeMessage->data
```
