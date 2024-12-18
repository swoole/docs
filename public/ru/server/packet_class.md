# Своле\Сервер\Пакет

Вот подробное описание `Swoole\Server\Packet`.

## Свойства


### $server_socket
Возвращает файловый дескриптор сервера `fd`, который является целым числом типа `int`.

```php
Swoole\Server\Packet->server_socket
```


### $server_port
Возвращает порт, на котором слушает сервер `server_port`, который является целым числом типа `int`.

```php
Swoole\Server\Packet->server_port
```


### $dispatch_time
Возвращает время прибытия данных запроса `dispatch_time`, который является двойным числом.

```php
Swoole\Server\Packet->dispatch_time
```


### $address
Возвращает адрес клиента `address`, который является строкой типа `string`.

```php
Swoole\Server\Packet->address
```


### $port
Возвращает порт, на котором слушает клиент `port`, который является целым числом типа `int`.

```php
Swoole\Server\Packet->port
```

### $data
Возвращает данные, переданные клиентом `data`, которые являются строкой типа `string`.

```php
Swoole\Server\Packet->data
```
