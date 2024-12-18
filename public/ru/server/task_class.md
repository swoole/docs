# Swoole\Server\Task

Вот подробное описание класса `Swoole\Server\Task`. Этот класс очень прост, но вы не можете получить объект `Task` с помощью `new Swoole\Server\Task()`, так как такой объект вообще не содержит никакой информации о сервере, и выполнение любого метода `Swoole\Server\Task` приведет к смертельному ошибке.

```shell
Недействительный инстанцирования Swoole\Server\Task в /home/task.php на линии 3
```

## Свойства

### $data
`data`, передаваемая `worker` процессу `task` процессу, является строкой типа `string`.

```php
Swoole\Server\Task->data
```

### $dispatch_time
Возвращает время отправки этой данных на `task` процесс, это свойство типа `double`.

```php
Swoole\Server\Task->dispatch_time
```

### $id
Возвращает время отправки этой данных на `task` процесс, это свойство типа `int`.

```php
Swoole\Server\Task->id
```

### $worker_id
Возвращает, из какого именно `worker` процесса пришла эта данные, это свойство типа `int`.

```php
Swoole\Server\Task->worker_id
```

### $flags
Некоторые флаги этой асинхронной задачи `flags`, это свойство типа `int`.

```php
Swoole\Server\Task->flags
```

?> Результаты возвращения `flags` следующие виды:  
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCK означает, что это не `Worker` процесс отправил `task` процессу, и в этом случае, если в событии `onTask` будет вызван `Swoole\Server::finish()`, будет выведен предупреждение.  
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCK означает, что последний callback-функция в `Swoole\Server::finish()` не null, и событие `onFinish` не будет выполнено, а будет выполнена только эта callback-функция. 
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCK означает, что задание будет обработано в форме协程. 
  - SW_TASK_NONBLOCK по умолчанию, когда ни одно из вышеуказанных случаев не применимо.

## Методы

### finish()

Используется для уведомления `Worker` процесса в [Task процессе](/learn?id=taskworkerprocess), что задание успешно завершено. Эта функция может передать результаты обработки `Worker` процессу.

```php
Swoole\Server\Task->finish(mixed $data): bool
```

  * **Параметры**

    * `mixed $data`

      * Функция: содержание результата обработки задания
      * По умолчанию: нет
      * Другие значения: нет

  * **Примечания**
    * Метод `finish` может быть вызван несколько раз подряд, и `Worker` процесс будет несколько раз активировать событие [onFinish](/server/events?id=onfinish).
    * После вызова метода `finish` в callback функции [onTask](/server/events?id=ontask), `return` данные все равно вызовут событие [onFinish](/server/events?id=onfinish).
    * Метод `Swoole\Server\Task->finish` необязателен. Если `Worker` процесс не интересуется результатом выполнения задания, нет необходимости его вызывать.
    * В callback функции [onTask](/server/events?id=ontask) `return`字符串 равносильно вызову `finish`.

  * **Важное примечание**

  !> Для использования функции `Swoole\Server\Task->finish` необходимо установить callback функцию [onFinish](/server/events?id=onfinish) для `Server`. Эта функция может использоваться только в callback функции [onTask](/server/events?id=ontask) [Task процесса](/learn?id=taskworkerprocess).

### pack()

Сериализует переданные данные.

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

  * **Параметры**

    * `mixed $data`

      * Функция: содержание результата обработки задания
      * По умолчанию: нет
      * Другие значения: нет

  * **Возвращаемое значение**
    * Если вызов успешен, возвращается сериализованный результат. 

### unpack()

Десериализует переданные данные.

```php
Swoole\Server\Task->unpack(string $data): mixed
```

  * **Параметры**

    * `string $data`

      * Функция: данные, которые нужно десериализовать
      * По умолчанию: нет
      * Другие значения: нет

  * **Возвращаемое значение**
    * Если вызов успешен, возвращается десериализованный результат. 

## Пример использования
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
