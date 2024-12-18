# Swoole\Server\Port

Вот подробное описание `Swoole\Server\Port`.

## Свойства


### $host
Возвращает адрес хоста, на который слушается этот порт. Это свойство типа `string`.

```php
Swoole\Server\Port->host
```


### $port
Возвращает номер порта, на который слушается этот порт. Это свойство типа `int`.

```php
Swoole\Server\Port->port
```


### $type
Возвращает тип этого набора `server`, который может быть одним из следующих значений: `SWOOLE_TCP`, `SWOOLE_TCP6`, `SWOOLE_UDP`, `SWOOLE_UDP6`, `SWOOLE_UNIX_DGRAM`, `SWOOLE_UNIX_STREAM`. Это свойство является перечислением.

```php
Swoole\Server\Port->type
```


### $sock
Возвращает номер сокета, на котором слушается этот порт. Это свойство типа `int`.

```php
Swoole\Server\Port->sock
```


### $ssl
Возвращает информацию о том, включена ли SSL-шифрование для этого порта. Это свойство типа `bool`.

```php
Swoole\Server\Port->ssl
```


### $setting
Возвращает настройки этого порта. Это свойство является ассоциативным массивом.

```php
Swoole\Server\Port->setting
```


### $connections
Возвращает итератор, который позволяет обходить все соединения, установленные на этом порту.

```php
Swoole\Server\Port->connections
```


## Методы


### set() 

Используется для настройки различных параметров работы `Swoole\Server\Port`. Использование аналогично методу [Swoole\Server->set()](/server/methods?id=set).

```php
Swoole\Server\Port->set(array $setting): void
```


### on() 

Используется для настройки коллбеков `Swoole\Server\Port`. Использование аналогично методу [Swoole\Server->on()](/server/methods?id=on).

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```


### getCallback() 

Возвращает установленный кол贝克.

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

  * **Параметры**

    * `string $name`

      * Функция: Название события колбека
      * По умолчанию: нет
      * Другие значения: нет

  * **Возвращаемое значение**

    * Возвращает кол贝克, что означает успешное выполнение операции, возвращает `null`, если такой кол贝克 не установлен.


### getSocket() 

Конвертирует текущий сокет `fd` в объект `Socket` PHP.

```php
Swoole\Server\Port->getSocket(): Socket|false
```

  * **Возвращаемое значение**

    * Возвращает объект `Socket`, что означает успешное выполнение операции, возвращает `false`, если операция неуспешна.

!> Обратите внимание, что этот метод может быть использован только при сборке `Swoole` с включением опции `--enable-sockets`.
