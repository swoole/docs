# Своле\Сервер\Эvent

Вот подробное описание `Swoole\Server\Event`.

## Свойства


### $reactor_id
Возвращает идентификатор `Reactor`线程, в котором происходит событие, это свойство является целым числом типа `int`.

```php
Swoole\Server\Event->reactor_id
```


### $fd
Возвращает файловый дескриптор `fd` этой связи, это свойство является целым числом типа `int`.

```php
Swoole\Server\Event->fd
```


### $dispatch_time
Возвращает время прибытия данных запроса `dispatch_time`, это свойство является числом двойного типа. Только в событии `onReceive` это свойство не равно `0`.

```php
Swoole\Server\Event->dispatch_time
```

### $data
Возвращает данные, отправленные клиентом `data`, это свойство является строкой типа `string`. Только в событии `onReceive` это свойство не равно `null`.