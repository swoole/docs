# Корутинный клиент для Redis

!> Данный клиент больше не рекомендуется использовать,推崇 использование сочетания `Swoole\Runtime::enableCoroutine + phpredis` или `predis`, то есть [односторонней корутинизации](/runtime)原生PHP Redis клиента.

!> После `Swoole 6.0` данный корутинный Redis клиент был удален.


## Примеры использования

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});
```

!> `subscribe` и `pSubscribe` не могут использоваться с `defer(true)`.


## Методы

!> Использование методов в основном保持一致о с [phpredis](https://github.com/phpredis/phpredis).

Ниже описаны различия реализации по сравнению с [phpredis](https://github.com/phpredis/phpredis):

1. Не реализованные команды Redis: `scan object sort migrate hscan sscan zscan`;

2. Использование `subscribe pSubscribe`, нет необходимости устанавливать функцию обратной связи;

3. Поддержка сериализации PHP переменных, при установленном третьем параметре `connect()` в `true`, активируется функция сериализации PHP переменных, по умолчанию `false`.


### __construct()

Конструктор корутинного клиента Redis, позволяет устанавливать настройки соединения с Redis, идентичные параметрам метода `setOptions()`.

```php
Swoole\Coroutine\Redis::__construct(array $options = null);
```


### setOptions()

После версии 4.2.10 был добавлен данный метод, используемый для установки некоторых настроек Redis клиента после конструктинга и подключения.

Эта функция в стиле Swoole, необходимо configurировать через массив ключевыми-значными парами.

```php
Swoole\Coroutine\Redis->setOptions(array $options): void
```

  * **Понятые параметры**


ключ | описание
---|---
`connect_timeout` | Время ожидания соединения, по умолчанию - глобальное значение协程 `socket_connect_timeout` (1 секунда)
`timeout` | Время ожидания, по умолчанию - глобальное значение协程 `socket_timeout`, смотрите [правила времени ожидания клиентов](/coroutine_client/init?id=правила%20времени%20ожидания)
`serialize` | Автоматическая序列изация, по умолчанию выключена
`reconnect` | Количество попыток автоматического подключения, если подключение прерывается по причинам, таким как превышение времени ожидания, и следующая отправка запроса происходит после нормального закрытия, то будет tentativamente attempt подключиться и отправить запрос снова, по умолчанию - 1 раз (`true`), после определенного количества неудачных попыток операции больше не продолжаются, необходимо вручную перезапустить соединение. Эта механика предназначена только для поддержания соединения, не пересылает запросы, что может привести к ошибкам в не-дискретных интерфейсах.
`compatibility_mode` | Решение совместимости функций `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore`, которые возвращают результаты, отличные от `php-redis`.啟ция этой функции делает результаты `Co\Redis` и `php-redis` идентичными, по умолчанию выключено 【эта настройка доступна в версиях >= v4.4.0】.


### set()

Запись данных.

```php
Swoole\Coroutine\Redis->set(string $key, mixed $value, array|int $option): bool
```

  * **パラメеты** 

    * **`string $key`**
      * **Функция**: Ключ для данных
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $value`**
      * **Функция**: содержимое данных【если тип не является строкой, он будет автоматически сериализован】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $options`**
      * **Функция**: опции
      * **По умолчанию**: нет
      * **Другие значения**: нет

      !> Описание `$option`:  
      `интегровый`: установление времени действия, например `3600`  
      `массив`: сложное установление времени действия, например `['nx', 'ex' => 10]` или `['xx', 'px' => 1000]`

      !> `px`: означает毫екondное время действия  
      `ex`: означает秒速ное время действия  
      `nx`: означает установление времени действия при отсутствии  
      `xx`: означает установление времени действия при наличии


### request()

Отправка пользовательского команды на сервер Redis. Аналогично `rawCommand` в `phpredis`.

```php
Swoole\Coroutine\Redis->request(array $args): void
```

  * **Пaramеты** 

    * **`array $args`**
      * **Функция**: список параметров, должен быть в виде массива. 【Первый элемент всегда должен быть команда Redis, остальные элементы - параметры этой команды, бэкенд автоматически упакует их в запрос на протокол Redis для отправки.]
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Возвращаемое значение** 

Зависит от того, как сервер Redis обрабатывает команду, может возвращаться число,布尔овый тип, строка, массив и другие типов.

  * **Пример использования** 

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379); // Если это локальный UNIX-сокет, то параметр host должен быть оформлен в форме, например, `unix://tmp/your_file.sock`
    $res = $redis->request(['object', 'encoding', 'key1']);
    var_dump($res);
});
```


## Свойства


### errCode

Кode ошибки.


Кode ошибки | Описание
---|---
1 | Ошибка чтения или записи
2 | Прочее...
3 | Конец файла
4 | Ошибка протокола
5 | Недостатка памяти


### errMsg

Сообщение об ошибке.


### connected

Определяет, подключен ли текущий клиент Redis к серверу.


## Константы

Используются для метода `multi($mode)`, по умолчанию используется режим `SWOOLE_REDIS_MODE_MULTI`:

* SWOOLE_REDIS_MODE_MULTI
* SWOOLE_REDIS_MODE_PIPELINE

Используются для определения возвращаемого значения команды `type()`:

* SWOOLE_REDIS_TYPE_NOT_FOUND
* SWOOLE_REDIS_TYPE_STRING
* SWOOLE_REDIS_TYPE_SET
* SWOOLE_REDIS_TYPE_LIST
* SWOOLE_REDIS_TYPE_ZSET
* SWOOLE_REDIS_TYPE_HASH


## режим транзакций

Можно использовать `multi` и `exec` для реализации режима транзакций Redis.

  * **Напоминание**

    * Используйте команду `multi` для начала транзакции, после этого все команды будут добавлены в очередь для выполнения
    * Используйте команду `exec` для выполнения всех операций в транзакции и возврата всех результатов за один раз

  * **Пример использования**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key3', 'rango');
    $redis->get('key1');
    $redis->get('key2');
    $redis->get('key3');

    $result = $redis->exec();
    var_dump($result);
});
```


## режим подписки

!> Доступно с версии Swoole >= v4.2.13, **для версий 4.2.12 и ниже в режиме подписки есть BUG**


### Подписка

В отличие от `phpredis`, `subscribe/psubscribe` представлены в стиле корутин.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // Или используйте psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg - это объект, содержащий следующую информацию
            // $type # Тип возвращаемого значения: показывает успех подписки
            // $name # Название канала для подписки или источник канала
            // $info  # Количество подписанных каналов на данный момент или информация о канале
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') { // или psubscribe
                // сообщение о успехе подписки канала, количество подписанных каналов равно числу Canalей
            } else if ($type == 'unsubscribe' && $info == 0){ // или punsubscribe
                break; // Получено сообщение об отписке и количество оставшихся подписанных каналов равно нулю, больше не принимайте, 종료 цикла
            } else if ($type == 'message') {  // Если это psubscribe, здесь будет pmessage
                var_dump($name); // Отпечаток имени канала источника
                var_dump($info); // Отпечаток сообщения
                // balabalaba.... // Обработка сообщения
                if ($need_unsubscribe) { // В определенных случаях необходимо отписаться
                    $redis->unsubscribe(); // Продолжить прием до завершения отписки
                }
            }
        }
    }
});
```
### Отписаться

Отписаться можно с помощью `unsubscribe/punsubscribe`, `$redis->unsubscribe(['channel1'])`

В это время `$redis->recv()` будет получать сообщение об отписании, и если вы отписываетеся от нескольких каналов, то вы получите несколько сообщений.
    
!> Обратите внимание: после отписания обязательно продолжайте `recv()` до получения последнего сообщения об отписании ( `$msg[2] == 0` ), и только после получения этого сообщения вы можете выйти из режима подписки

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // или use psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg is an array containing the following information
            // $type # return type: show subscription success
            // $name # subscribed channel name or source channel name
            // $info  # the number of channels or information content currently subscribed
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') // or psubscribe
            {
                // channel subscription success message
            }
            else if ($type == 'unsubscribe' && $info == 0) // or punsubscribe
            {
                break; // received the unsubscribe message, and the number of channels remaining for the subscription is 0, no longer received, break the loop
            }
            else if ($type == 'message') // if it's psubscribe, here is pmessage
            {
                // print source channel name
                var_dump($name);
                // print message
                var_dump($info);
                // handle messsage
                if ($need_unsubscribe) // in some cases, you need to unsubscribe
                {
                    $redis->unsubscribe(); // continue recv to wait unsubscribe finished
                }
            }
        }
    }
});
```

## КомPatибильность режима

Проблема несоответствия возвращаемых результатов команд `hmGet/hGetAll/zrange/zrevrange/zrangebyscore/zrevrangebyscore` от `Co\Redis` и расширений `phpredis` была решена [#2529](https://github.com/swoole/swoole-src/pull/2529).

Для совместимости со старыми версиями, после настройки `$redis->setOptions(['compatibility_mode' => true]);`, можно гарантировать, что результаты от `Co\Redis` и `phpredis` будут одинаковыми.

!> Версия Swoole >= `v4.4.0` доступна

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->setOptions(['compatibility_mode' => true]);
    $redis->connect('127.0.0.1', 6379);

    $co_get_val = $redis->get('novalue');
    $co_zrank_val = $redis->zRank('novalue', 1);
    $co_hgetall_val = $redis->hGetAll('hkey');
    $co_hmget_val = $redis->hmGet('hkey', array(3, 5));
    $co_zrange_val = $redis->zRange('zkey', 0, 99, true);
    $co_zrevrange_val = $redis->zRevRange('zkey', 0, 99, true);
    $co_zrangebyscore_val = $redis->zRangeByScore('zkey', 0, 99, ['withscores' => true]);
    $co_zrevrangebyscore_val = $redis->zRevRangeByScore('zkey', 99, 0, ['withscores' => true]);
});
```
