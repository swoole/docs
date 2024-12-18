# TCP сервер

?> `Swoole\Coroutine\Server` - это полностью [кооперативная](/coroutine) класс, предназначенный для создания кооперативных `TCP` серверов, поддерживающих TCP и [unixSocket](/learn?id=что такое IPC) типа.

Отличие от [Server](/server/tcp_init) модуля:

* Динамическое создание и уничтожение, в процессе работы можно динамически слушать порты, а также динамически закрывать сервер
* Процесс обработки подключений полностью синхронный, программы могут последовательно обрабатывать события `Connect`, `Receive`, `Close`

!> Доступно в версиях 4.4 и выше


## Короткое название

Можно использовать короткое название `Co\Server`.


## Методы


### __construct()

?> **Конструктор.** 

```php
Swoole\Coroutine\Server::__construct(string $host, int $port = 0, bool $ssl = false, bool $reuse_port = false);
```

  * **Параметры** 

    * **`string $host`**
      * **Функция**: Адрес для прослушивания
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`int $port`**
      * **Функция**: Порт для прослушивания【если равно 0, система будет динамически выбирать порт】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`bool $ssl`**
      * **Функция**: Включить SSL шифрование
      * **По умолчанию**: `false`
      * **Другие значения**: `true`

    * **`bool $reuse_port`**
      * **Функция**: Включить повторное использование порта, эффект такой же, как и в [этой секции](/server/setting?id=enable_reuse_port) настройки
      * **По умолчанию**: `false`
      * **Другие значения**: `true`
      * **Влияние версии**: Swoole версия >= v4.4.4

  * **Примечание**

    * **Параметр $host поддерживает 3 формы**

      * `0.0.0.0/127.0.0.1`: IP-адрес IPv4
      * `::/::1`: IP-адрес IPv6
      * `unix:/tmp/test.sock`: [UnixSocket](/learn?id=что такое IPC) адрес

    * **Ошибки**

      * В случае ошибки параметров, неудачи при связывании адреса и порта, неудачи при `listen` будет выброшена исключение `Swoole\Exception`.


### set()

?> **Установить параметры обработки протокола.** 

```php
Swoole\Coroutine\Server->set(array $options);
```

  * **Конфигурационные параметры**

    * Параметр `$options` должен быть одноуровневым ассоциативным индексным массивом, полностью соответствующим элементам конфигурации, принятым методом [setprotocol](/coroutine_client/socket?id=setprotocol).

    !> Должен быть установлен перед [start()](/coroutine/server?id=start) методом

    * **Длинный протокол**

    ```php
    $server = new Swoole\Coroutine\Server('127.0.0.1', $port, $ssl);
    $server->set([
      'open_length_check' => true,
      'package_max_length' => 1024 * 1024,
      'package_length_type' => 'N',
      * `package_length_offset` => 0,
      * `package_body_offset` => 4,
    ]);
    ```

    * **Установка сертификатов SSL**

    ```php
    $server->set([
      'ssl_cert_file' => dirname(__DIR__) . '/ssl/server.crt',
      'ssl_key_file' => dirname(__DIR__) . '/ssl/server.key',
    ]);
    ```


### handle()

?> **Установить функцию обработки подключения.** 

!> Должен быть установлен перед [start()](/coroutine/server?id=start) методом обработки функций

```php
Swoole\Coroutine\Server->handle(callable $fn);
```

  * **Параметры** 

    * **`callable $fn`**
      * **Функция**: Установить функцию обработки подключения
      * **По умолчанию**: Нет
      * **Другие значения**: Нет
      
  * **Пример** 

    ```php
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            $data = $conn->recv();
        }
    });
    ```

    !> - После успешного `Accept` (установления соединения) сервер автоматически создает [кооператив](/coroutine?id=кооперативная диспетчеризация) и выполняет `$fn` ;  
    - `$fn` выполняется в новом пространстве дочерних кооперативов, поэтому внутри функции не нужно снова создавать кооперативы;  
    - `$fn` принимает один параметр, типа объекта [Swoole\Coroutine\Server\Connection](/coroutine/server?id=coroutineserverconnection) ;  
    - Можно получить объект Socket текущего соединения с помощью [exportSocket()](/coroutine/server?id=exportsocket)


### shutdown()

?> **Завершить сервер.** 

?> В основе поддерживается многократное использование `start` и `shutdown`

```php
Swoole\Coroutine\Server->shutdown(): bool
```


### start()

?> **Запустить сервер.** 

```php
Swoole\Coroutine\Server->start(): bool
```

  * **Возвращаемое значение**

    * Если запуск неудачен, возвращается `false`, и устанавливается свойство `errCode`
    * Если запуск успешен,将进入 цикл, `Accept` подключения
    * После `Accept` (установления соединения) будет создан новый кооператив, и в кооперативе будет вызвана функция, указанная в `handle` методе

  * **Ошибочное управление**

    * Когда `Accept` (установление соединения) возникает ошибка `Too many open file`, или невозможно создать дочерний кооператив, будет приостановлен на `1` секунду, а затем продолжено `Accept`
    * При ошибке метод `start()` вернет `false`, и ошибка будет сообщаться в виде `Warning`.


## Объекты


### Coroutine\Server\Connection

Объект `Swoole\Coroutine\Server\Connection` предоставляет четыре метода:
 
#### recv()

Получить данные, если установлена обработка протокола, то будет возвращаться полный пакет за раз

```php
function recv(float $timeout = 0)
```

#### send()

Отправка данных

```php
function send(string $data)
```

#### close()

Завершение соединения

```php
function close(): bool
```

#### exportSocket()

Получение объекта Socket текущего соединения. Можно вызвать больше методов на нижнем уровне, пожалуйста, обратитесь к [Swoole\Coroutine\Socket](/coroutine_client/socket)

```php
function exportSocket(): Swoole\Coroutine\Socket
```

## Полный пример

```php
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

// Модуль управления многопроцессами
$pool = new Process\Pool(2);
//这样就保证了 каждый callback OnWorkerStart автоматически создает кооператив
$pool->set(['enable_coroutine' => true]);
$pool->on('workerStart', function ($pool, $id) {
    // Каждый процесс слушает на порте 9501
    $server = new Swoole\Coroutine\Server('127.0.0.1', 9501, false, true);

    // Получение сигнала 15 закрывает сервис
    Process::signal(SIGTERM, function () use ($server) {
        $server->shutdown();
    });

    // Получение нового запроса на подключение и автоматическое создание кооператива
    $server->handle(function (Connection $conn) {
        while (true) {
            // Получение данных
            $data = $conn->recv(1);

            if ($data === '' || $data === false) {
                $errCode = swoole_last_error();
                $errMsg = socket_strerror($errCode);
                echo "errCode: {$errCode}, errMsg: {$errMsg}\n";
                $conn->close();
                break;
            }

            // Отправка данных
            $conn->send('hello');

            Coroutine::sleep(1);
        }
    });

    // Начало прослушивания порта
    $server->start();
});
$pool->start();
```

!> Если вы выполняете в среде Cygwin, пожалуйста, измените на однопроцессовый. `$pool = new Swoole\Process\Pool(1);`
