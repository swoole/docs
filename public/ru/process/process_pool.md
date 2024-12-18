# Swoole\Process\Pool

Процессный пул, реализованный на основе Manager'а [Swoole\Server](/server/init), управляет несколькими рабочими процессами. Основная функция этого модуля - управление процессами. По сравнению с реализацией `Process`, `Process\Pool` проще, имеет более высокую уровень абстракции, и разработчикам не нужно писать много кода для реализации функций управления процессами. В сочетании с [Co\Server](/coroutine/server?id=完整示例) можно создать сервер, полностью основанный на корутинах, который может использовать многоядерный CPU.

## Коммуникация между процессами

`Swoole\Process\Pool` предоставляет три способа коммуникации между процессами:

### 消息队列
Если во втором аргументе конструктора `Swoole\Process\Pool`设置为 `SWOOLE_IPC_MSGQUEUE`, то используется коммуникация через сообщение в очереди. Можно использовать расширение `php sysvmsg` для отправки сообщений, максимальный размер сообщения не должен превышать `65536`.

* **Примечание**

  * Для использования расширения `sysvmsg` в конструкторе необходимо передать `msgqueue_key`
  * Сло地层 Swoole не поддерживает второй аргумент `mtype` функции `msg_send` расширения `sysvmsg`, пожалуйста, передайте любой не `0` значение

### Сocket коммуникация
Если во втором аргументе конструктора `Swoole\Process\Pool`设置为 `SWOOLE_IPC_SOCKET`, то используется `Socket коммуникация`. Если ваш клиент и сервер не находятся на одном компьютере, то можно использовать этот способ коммуникации.

С помощью метода [Swoole\Process\Pool->listen()](/process/process_pool?id=listen) можно слушать порт, с помощью события [Message](/process/process_pool?id=on) можно принимать данные от клиента, а с помощью метода [Swoole\Process\Pool->write()](/process/process_pool?id=write) можно отправлять ответ клиенту.

Swoole требует, чтобы клиент отправлял данные в этом формате, добавляя перед реальными данными 4 байта - длину данных в сетевом порядке.
```php
$msg = 'Hello Swoole';
$packet = pack('N', strlen($msg)) . $msg;
```

### UnixSocket
Если во втором аргументе конструктора `Swoole\Process\Pool`设置为 `SWOOLE_IPC_UNIXSOCK`, то используется [UnixSocket](/learn?id=什么是IPC). **Желательно использовать этот способ для коммуникации между процессами**.

Этот способ довольно прост, достаточно использовать метод [Swoole\Process\Pool->sendMessage()](/process/process_pool?id=sendMessage) и событие [Message](/process/process_pool?id=on) для завершения коммуникации между процессами.

Или после включения `корутинного режима` можно получить объект `Swoole\Process` с помощью метода [Swoole\Process\Pool->getProcess()](/process/process_pool?id=getProcess), а затем получить объект `Swoole\Coroutine\Socket` с помощью метода [Swoole\Process\Pool->exportsocket()](/process/process?id=exportsocket). Используя этот объект, можно реализовать коммуникацию между рабочими процессами. Однако в этом случае нельзя устанавливать событие [Message](/process/process_pool?id=on)

!> Подробная информация о параметрах и конфигурации окружающей среды можно найти в [конструкторе](/process/process_pool?id=__construct) и [конфигурационных параметрах](/process/process_pool?id=set)

## Константы

Константа | Описание
---|---
SWOOLE_IPC_MSGQUEUE | Коммуникация через систему сообщений (/learn?id=什么是IPC)
SWOOLE_IPC_SOCKET | Коммуникация через Socket
SWOOLE_IPC_UNIXSOCK | Коммуникация через UnixSocket (/learn?id=什么是IPC) (v4.4+)

## Поддержка корутин

В версии `v4.4.0` добавлена поддержка корутин. Смотрите [Swoole\Process\Pool::__construct](/process/process_pool?id=__construct)

## Пример использования

```php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(5);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** Текущий рабочий процесс */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n";
    while ($running) {
        Coroutine::sleep(1);
        echo "sleep 1\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
```

## Методы

### __construct()

Конструктор.

```php
Swoole\Process\Pool::__construct(int $worker_num, int $ipc_type = SWOOLE_IPC_NONE, int $msgqueue_key = 0, bool $enable_coroutine = false);
```

* **Параметры** 

  * **`int $worker_num`**
    * **Функция**: Указывает количество рабочих процессов
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $ipc_type`**
    * **Функция**: Модель коммуникации между процессами【по умолчанию `SWOOLE_IPC_NONE`, означает не использование никаких особенностей коммуникации между процессами】
    * **По умолчанию**: `SWOOLE_IPC_NONE`
    * **Другие значения**: `SWOOLE_IPC_MSGQUEUE`, `SWOOLE_IPC_SOCKET`, `SWOOLE_IPC_UNIXSOCK`

    !> - Когда установлено как `SWOOLE_IPC_NONE`, необходимо установить обратный вызов `onWorkerStart`, и в `onWorkerStart` необходимо реализовать логику цикла. Когда функция `onWorkerStart` выходит, рабочий процесс немедленно завершает работу, после чего Manager процесс вновь поднимает процесс;  
    - Установлено как `SWOOLE_IPC_MSGQUEUE`, означает использование коммуникации через систему сообщений, можно установить `$msgqueue_key` для указания `KEY` сообщения в очереди, если не установлен ключ сообщения в очереди, будет выделена частная очередь;  
    - Установлено как `SWOOLE_IPC_SOCKET`, означает использование Socket для коммуникации, необходимо использовать метод [listen](/process/process_pool?id=listen) для указания адреса и порта для прослушивания;  
    - Установлено как `SWOOLE_IPC_UNIXSOCK`, означает использование [unixSocket](/learn?id=什么是IPC) для коммуникации, используется в режиме корутин, **с强烈推荐анием использовать этот способ для коммуникации между процессами**, конкретное использование смотрите ниже;  
    - Когда установлено не как `SWOOLE_IPC_NONE`, необходимо установить обратный вызов `onMessage`, `onWorkerStart` становится необязательным.

  * **`int $msgqueue_key`**
    * **Функция**: Ключ сообщения в очереди
    * **По умолчанию**: `0`
    * **Другие значения**: Нет

  * **`bool $enable_coroutine`**
    * **Функция**: Включает поддержку корутин【После включения корутин невозможно установить обратный вызов `onMessage`】
    * **По умолчанию**: `false`
    * **Другие значения**: `true`

* **Режим корутин**
    
В версии `v4.4.0` модуль `Process\Pool` Swoole добавил поддержку корутин, можно настроить четвертый параметр как `true` для включения. После включения корутин на уровне底层 автоматически создается корутина и [корутинный контейнер](/coroutine/scheduler) при `onWorkerStart`, и в обратном вызове можно напрямую использовать корутинные соответствующие `API`, например:

```php
$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    while (true) {
        Co::sleep(0.5);
        echo "hello world\n";
    }
});

$pool->start();
```

После включения корутин Swoole запрещает установка обратного вызова события `onMessage`, и для коммуникации между процессами необходимо настроить второй параметр как `SWOOLE_IPC_UNIXSOCK`, что означает использование [unixSocket](/learn?id=什么是IPC) для коммуникации, затем использовать `$pool->getProcess()->exportSocket()` для экспорта объекта [Swoole\Coroutine\Socket](/coroutine_client/socket), чтобы реализовать коммуникацию между рабочими процессами. Например:

 ```php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo $socket->recv();
        $socket->send("hello proc1\n");
        echo "proc0 stop\n";
    } else {
        $socket->send("hello proc0\n");
        echo $socket->recv();
        echo "proc1 stop\n";
        $pool->shutdown();
    }
});

$pool->start();
 ```

!> Конкретное использование можно посмотреть в разделах [Swoole\Coroutine\Socket](/coroutine_client/socket) и [Swoole\Process](/process/process?id=exportsocket).

```php
$q = msg_get_queue($key);
foreach (range(1, 100) as $i) {
    $data = json_encode(['data' => base64_encode(random_bytes(1024)), 'id' => uniqid(), 'index' => $i,]);
    msg_send($q, $i, $data, false);
}
```
### set()

Установка параметров.

```php
Swoole\Process\Pool->set(array $settings): void
```


Пояснительные параметры | Тип | Функция | По умолчанию
---|---|---|---
enable_coroutine |布尔 | Управление включением корутин | false
enable_message_bus |布尔 | Включение шины сообщений, если эта значение равно `true`, при отправке больших данных,底层 будет разделять данные на маленькие кусочки, а затем отправлять их по одному к получателю | false
max_package_size |整数 | Ограничение максимального объема данных, который процесс может принять | 2 * 1024 * 1024

* **Примечание**

  * Когда `enable_message_bus` равно `true`, `max_package_size` не имеет эффекта, поскольку底层 разделяет данные на маленькие кусочки для отправки и приема.
  * В режиме `SWOOLE_IPC_MSGQUEUE` `max_package_size` также не имеет эффекта, поскольку底层 может принять до `65536` данных за один раз.
  * В режиме `SWOOLE_IPC_SOCKET`, если `enable_message_bus` равно `false` и объем принятых данных превышает `max_package_size`,底层 будет немедленно прерывать соединение.
  * В режиме `SWOOLE_IPC_UNIXSOCK`, если `enable_message_bus` равно `false` и данные превышают `max_package_size`, часть данных, превышающая `max_package_size`, будет обрезана.
  * Если включена режим корутин, то когда `enable_message_bus` равно `true`, `max_package_size` также не имеет эффекта. Под地层 будет разделять (отправлять) и объединять (получать) данные, иначе объем принятых данных будет ограничен по `max_package_size`.

!> Версия Swoole >= v4.4.4 доступна


### on()

Установка обратного вызова для процессного пула.

```php
Swoole\Process\Pool->on(string $event, callable $function): bool;
```

* **Параметры** 

  * **`string $event`**
    * **Функция**: Указывает событие
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`callable $function`**
    * **Функция**: Обратный вызов
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **События**

  * **onWorkerStart** Начало работы дочернего процесса

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Объект пула
  * @param int $workerId   Идентификатор рабочего процесса,底层 будет нумеровать дочерние процессы
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStart', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Рабочий#{$workerId} запущен\n";
  });
  ```

  * **onWorkerStop** Окончание работы дочернего процесса

  ```php
  /**
  * @param \Swoole\Process\Pool $pool Объект пула
  * @param int $workerId   Идентификатор рабочего процесса,底层 будет нумеровать дочерние процессы
  */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('WorkerStop', function(Swoole\Process\Pool $pool, int $workerId){
    echo "Рабочий#{$workerId} остановлен\n";
  });
  ```

  * **onMessage** Получение сообщения

  !> Получено внешнее отправленное сообщение. Одна связь может отправить только одно сообщение, аналогично механизму короткой связи `PHP-FPM`

  ```php
  /**
    * @param \Swoole\Process\Pool $pool Объект пула
    * @param string $data Содержание сообщения
   */
  $pool = new Swoole\Process\Pool(2);
  $pool->on('Message', function(Swoole\Process\Pool $pool, string $data){
    var_dump($data);
  });
  ```

  !> Название события не различается в верхнем и нижнем регистре, `WorkerStart`, `workerStart` или `workerstart` одинаковы


### listen()

Слушать `SOCKET`, можно использовать только при `$ipc_mode = SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->listen(string $host, int $port = 0, int $backlog = 2048): bool
```

* **Параметры** 

  * **`string $host`**
    * **Функция**: Адрес для прослушивания【поддерживается `TCP` и [unixSocket](/learn?id=что такое IPC) два типа. `127.0.0.1` указывает на прослушивание `TCP` адреса, необходимо указать `$port`. `unix:/tmp/php.sock` слушает [unixSocket](/learn?id=что такое IPC) адрес】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $port`**
    * **Функция**: Порт для прослушивания【необходимо указать в режиме `TCP`】
    * **По умолчанию**: `0`
    * **Другие значения**: Нет

  * **`int $backlog`**
    * **Функция**: Длина очереди для прослушивания
    * **По умолчанию**: `2048`
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Успешная прослушивание возвращает `true`
  * Неудача прослушивания возвращает `false`, можно использовать `swoole_errno` для получения ошибки. После неудачи прослушивания, когда будет вызван `start`, сразу же будет возвращено `false`

* **Протокол коммуникации**

    При отправке данных на прослушиваемый порт, клиент должен увеличить размер данных на 4 байта в сетевом порядке перед запросом. Формат протокола следующий:

```php
// $msg Данные, которые нужно отправить
$packet = pack('N', strlen($msg)) . $msg;
```

* **Пример использования**

```php
$pool->listen('127.0.0.1', 8089);
$pool->listen('unix:/tmp/php.sock');
```


### write()

Написать данные на противоположную сторону, можно использовать только при `$ipc_mode` равен `SWOOLE_IPC_SOCKET`.

```php
Swoole\Process\Pool->write(string $data): bool
```

!> Этот метод является операцией с памятью, без `IO` потребления, операция отправки данных является синхронной блокирующей `IO`

* **Параметры** 

  * **`string $data`**
    * **Функция**: Содержание написанных данных【можно несколько раз вызвать `write`,底层 будет после выхода из функции `onMessage` полностью написать данные в `socket` и `close` соединение】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Пример использования**

  * **Служебный**

    ```php
    $pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
    
    $pool->on("Message", function ($pool, $message) {
        echo "Message: {$message}\n";
        $pool->write("hello ");
        $pool->write("world ");
        $pool->write("\n");
    });
    
    $pool->listen('127.0.0.1', 8089);
    $pool->start();
    ```

  * **Клиентский**

    ```php
    $fp = stream_socket_client("tcp://127.0.0.1:8089", $errno, $errstr) or die("error: $errstr\n");
    $msg = json_encode(['data' => 'hello', 'uid' => 1991]);
    fwrite($fp, pack('N', strlen($msg)) . $msg);
    sleep(1);
    // будет показано hello world\n
    $data = fread($fp, 8192);
    var_dump(substr($data, 4, unpack('N', substr($data, 0, 4))[1]));
    fclose($fp);
    ```


### sendMessage()

Отправка данных в целевой процесс, можно использовать только при `$ipc_mode` равен `SWOOLE_IPC_UNIXSOCK`.

```php
Swoole\Process\Pool->sendMessage(string $data, int $dst_worker_id): bool
```

* **Параметры** 

  * **`string $data`**
    * **Функция**: Данные для отправки
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $dst_worker_id`**
    * **Функция**: Идентификатор целевого процесса
    * **По умолчанию**: `0`
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Успешная отправка возвращает `true`
  * Неудача отправки возвращает `false`

* **Примечание**

  * Если отправляемые данные превышают `max_package_size` и `enable_message_bus` равно `false`, то целевой процесс при приеме данных обрезает данные

```php
<?php
use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => false, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(2048)


$pool = new Process\Pool(2, SWOOLE_IPC_UNIXSOCK);
$pool->set(['enable_coroutine' => true, 'enable_message_bus' => true, 'max_package_size' => 2 * 1024]);

$pool->on('WorkerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    if ($workerId == 0) {
        $pool->sendMessage(str_repeat('a', 2 * 3000), 1);
    }
});

$pool->on('Message', function (Swoole\Process\Pool $pool, string $data) {
    var_dump(strlen($data));
});
$pool->start();

// int(6000)
```
### start()

Запуск worker процесса.

```php
Swoole\Process\Pool->start(): bool
```

!> Если запуск успешен, текущий процесс переходит в состояние `wait`, управляет worker процессами;  
Если запуск неудачен, возвращается `false`, можно использовать `swoole_errno` для получения ошибки.

* **Пример использования**

```php
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while (true) {
         $msg = $redis->brpop($key, 2);
         if ( $msg == null) continue;
         var_dump($msg);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
```

* **Управление процессами**

  * Если какой-либо worker процесс сталкивается с критической ошибкой или добровольно завершает работу, управляющий процесс будет его убирать, чтобы избежать появления зомби процессов
  * После завершения worker процесса управляющий процесс автоматически поднимает и создает новый worker процесс
  * Когда главный процесс получает сигнал `SIGTERM`, он перестанет `fork`ить новые процессы и `kill`ит все работающих worker процессы
  * Когда главный процесс получает сигнал `SIGUSR1`, он будет `kill`ить один за другим все работающих worker процессы и перезапустить новые

* **Обработка сигналов**

  На нижнем уровне установлены только сигналы для главного процесса (управляющего процесса), worker процессов не оснащены сигналами, их необходимо реализовать самостоятельно.

  - Если worker процесс работает в асинхронном режиме, используйте [Swoole\Process::signal](/process/process?id=signal) для прослушивания сигналов
  - Если worker процесс работает в синхронном режиме, используйте `pcntl_signal` и `pcntl_signal_dispatch` для прослушивания сигналов

  В worker процессе следует прослушивать сигнал `SIGTERM`, когда главный процесс хочет завершить этот процесс, он отправит ему сигнал `SIGTERM`. Если worker процесс не прослушивает сигнал `SIGTERM`, нижний уровень принудительно завершит текущий процесс, что приведет к потере части логики.

```php
$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msg = $redis->brpop($key);
         pcntl_signal_dispatch();
         if ( $msg == null) continue;
         var_dump($msg);
     }
});
```


### stop()

Удаляет текущий процесс из событийого цикла, этот метод имеет смысл только после запуска协程.

```php
Swoole\Process\Pool->stop(): bool
```


### shutdown()

Завершает работу worker процессов.

```php
Swoole\Process\Pool->shutdown(): bool
```


### getProcess()

Получает текущий объект worker процесса. Возвращает объект [Swoole\Process](/process/process).

!> С версией Swoole >= `v4.2.0` доступен

```php
Swoole\Process\Pool->getProcess(int $worker_id): Swoole\Process
```

* **Параметры** 

  * **`int $worker_id`**
    * **Функция**: Указывает на получение `worker` 【опциональный параметр, по умолчанию текущий `worker`】
    * **По умолчанию**: нет
    * **Другие значения**: нет

!> Необходимо вызвать после `start`, в `onWorkerStart` или других обратных функциях worker процесса;  
Возвращаемый объект `Process` является монопольным, повторное использование `getProcess()` в worker процессе вернет тот же объект.

* **Пример использования**

```php
$pool = new Swoole\Process\Pool(3);

$pool->on('WorkerStart', function ($pool, $workerId) {
    $process = $pool->getProcess();
    $process->exec('/usr/local/bin/php', ['-r', 'var_dump(swoole_version());']);
});

$pool->start();
```

### detach()

Отделяет текущего worker процесса из управления процессом, на нижнем уровне немедленно создается новый процесс, старый процесс больше не обрабатывает данные, их жизненный цикл должен управляться кодом приложения.

!> С версией Swoole >= `v4.7.0` доступен

```php
Swoole\Process\Pool->detach(): bool
```
