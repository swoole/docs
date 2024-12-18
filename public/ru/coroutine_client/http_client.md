# Корутинный HTTP/WebSocket клиент

Базовый уровень корутинного HTTP-клиента написан на чистом C и не зависит от каких-либо сторонних расширений библиотеки, обеспечивая сверхвысокую производительность.

* Поддерживается HTTP-Chunks и Keep-Alive, поддерживается формат form-data
* Версия HTTP-протокола - HTTP/1.1
* Поддерживается переход в клиент WebSocket
* Поддержка сжатия gzip требует зависимости от библиотеки zlib
* Клиент реализует только основные функции, для реальных проектов рекомендуется использовать [Saber](https://github.com/swlib/saber)


## Свойства


### errCode

Ошибочный статусный код. Когда `connect/send/recv/close` терпят неудачу или происходит тайм-аут, автоматически устанавливается значение `Swoole\Coroutine\Http\Client->errCode`

```php
Swoole\Coroutine\Http\Client->errCode: int
```

Значение `errCode` равно `Linux errno`. Можно использовать `socket_strerror` для преобразования ошибки в сообщение.

```php
// Если connect отвергается, ошибка имеет код 111
// Если происходит тайм-аут, ошибка имеет код 110
echo socket_strerror($client->errCode);
```

!> Смотрите: [Список Linux ошибок](/other/errno?id=linux)


### body

Сохраняет тело ответа последнего запроса.

```php
Swoole\Coroutine\Http\Client->body: string
```

  * **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->get('/get');
    echo $cli->body;
    $cli->close();
});
```


### statusCode

HTTP-статусный код, например 200, 404 и т.д. Если статусный код отрицательный, это указывает на проблемы с подключением. [Смотреть больше](/coroutine_client/http_client?id=getstatuscode)

```php
Swoole\Coroutine\Http\Client->statusCode: int
```


## Методы


### __construct()

Конструктор.

```php
Swoole\Coroutine\Http\Client::__construct(string $host, int $port, bool $ssl = false);
```

  * **Параметры** 

    * **`string $host`**
      * **Функция**: Адрес целевого сервера 【может быть IP или домен, нижний уровень автоматически выполняет DNS-разрешение, если это локальный UNIX Socket, то следует указать в форме `unix://tmp/your_file.sock`; если это домен, не нужно указывать протокол в виде `http://` или `https://`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`int $port`**
      * **Функция**: Порт целевого сервера
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`bool $ssl`**
      * **Функция**: Активировать ли SSL/TLS туннельное шифрование, если целевой сервер использует https, необходимо установить параметр `$ssl` как `true`
      * **По умолчанию**: `false`
      * **Другие значения**: нет

  * **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/index.php');
    echo $client->body;
    $client->close();
});
```


### set()

Установка параметров клиента.

```php
Swoole\Coroutine\Http\Client->set(array $options);
```

Этот метод принимает те же параметры, что и `Swoole\Client->set`, можно посмотреть документацию по [Swoole\Client->set](/client?id=set) методу.

Клиент `Swoole\Coroutine\Http\Client` дополнительно поддерживает некоторые опции для управления HTTP и WebSocket клиентами.

#### Дополнительные опции

##### Управление тайм-аутом

Установка опции `timeout`, активация обнаружения тайм-аута HTTP-запросов. Единицы измерения - секунды, минимальная точность - миллиsekунда.

```php
$http->set(['timeout' => 3.0]);
```

* Если соединение超时 или сервер закрывает его, `statusCode` будет установлен на `-1`
* Если сервер не отвечает в течение установленного времени, запрос тайм-аутит, `statusCode` будет установлен на `-2`
* После тайм-аута нижний уровень автоматически закроет соединение
* Смотрите [правила тайм-аута клиента](/coroutine_client/init?id=правила тайм-аута)

##### keep_alive

Установка опции `keep_alive`, активация или禁用 HTTP-длительных подключений.

```php
$http->set(['keep_alive' => false]);
```

##### websocket_mask

> Согласно RFC, начиная с v4.4.0, эта настройка по умолчанию включена, но это может привести к потере производительности, если сервер не требует это, можно禁用

Включение или отключение маски для WebSocket-клиента. По умолчанию включено. Когда включено, данные, отправленные WebSocket-клиентом, будут преобразованы с помощью маски.

```php
$http->set(['websocket_mask' => false]);
```

##### websocket_compression

> Требуется v4.4.12 или более поздняя версия

Если установлено как `true`, разрешается сжатие фреймов с использованием zlib, возможно ли сжатие зависит от способности сервера обрабатывать сжатие (определяется на основе информации о HANDSHAKE, см. `RFC-7692`)

Для настоящего сжатия конкретного фрейма необходимо использовать флаг `SWOOLE_WEBSOCKET_FLAG_COMPRESS`, подробности использования см. [эту раздел](/websocket_server?id=websocket帧压缩-（rfc-7692））]

```php
$http->set(['websocket_compression' => true]);
```

##### write_func
> Требуется v5.1.0 или более поздняя версия

Установка обратной функции `write_func`, подобно опции `WRITE_FUNCTION` cURL, может использоваться для обработки потока ответов, например, содержание `Event Stream` от `OpenAI ChatGPT`.

> После установки `write_func` невозможно использовать метод `getContent()` для получения содержания ответа, и `$client->body` также будет пустым  
> В обратной функции `write_func` можно использовать `$client->close()` для остановки приема содержания ответа и закрытия соединения

```php
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->set(['write_func' => function ($client, $data) {
    var_dump($data);
}]);
$cli->get('/');
```


### setMethod()

Установка метода запроса. Установлено только для текущего запроса, после отправки запросаSetting метод будет немедленно удален.

```php
Swoole\Coroutine\Http\Client->setMethod(string $method): void
```

  * **Параметры** 

    * **`string $method`**
      * **Функция**: Установить метод 
      * **По умолчанию**: нет
      * **Другие значения**: нет

      !> Необходимо использовать имя метода, соответствующее стандартам HTTP, неправильное значение `$method` может привести к отказу от запроса HTTP-сервером

  * **Пример**

```php
$http->setMethod("PUT");
```


### setHeaders()

Установка HTTP-заголовков запроса.

```php
Swoole\Coroutine\Http\Client->setHeaders(array $headers): void
```

  * **Параметры** 

    * **`array $headers`**
      * **Функция**: Установить заголовки запроса 【должен быть массивом с ключами и значениями, нижний уровень автоматически преобразует его в стандартный HTTP-формат заголовка `$key`: `$value`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

!> заголовки HTTP, установленные с помощью `setHeaders`, остаются действительными для каждого запроса в течение жизни объекта `Coroutine\Http\Client`; повторное использование `setHeaders` заменит предыдущие настройки


### setCookies()

Установка `Cookie`, значение будет закодировано с использованием `urlencode`, если необходимо сохранить исходную информацию, пожалуйста, самостоятельно установите заголовок `Cookie` с помощью `setHeaders`.

```php
Swoole\Coroutine\Http\Client->setCookies(array $cookies): void
```

  * **Параметры** 

    * **`array $cookies`**
      * **Функция**: Установить `COOKIE` 【должен быть массивом с ключами и значениями】
      * **По умолчанию**: нет
      * **Другие значения**: нет
!> После установки `КОНТАКТА` он будет сохраняться в течение срока существования объекта на стороне клиента  

- `КОНТАКТА`, установленный на стороне сервера, будет интегрирован в массив `cookies`, и информация о `КОНТАКТАх` текущего HTTP-клиента может быть получена через свойство `$client->cookies`  

- повторное вызов метода `setCookies` заменит текущее состояние `Cookies`, что приведет к потере ранее отправленных сервером `КОНТАКТА` и `КОНТАКТА`, установленных вручную


### setData()

Установка тела HTTP-запроса.

```php
Swoole\Coroutine\Http\Client->setData(string|array $data): void
```

  * **Параметры** 

    * **`string|array $data`**
      * **Функция**: Установление тела запроса
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Примечания**

    * После установки `$data` и если не установлено `$method`,底层 автоматически установит POST
    * Если `$data` является массивом и `Content-Type` является формой `urlencoded`,底层 автоматически будет выполнять `http_build_query`
    * Если используется `addFile` или `addData`, что приводит к включению формы `data`, значение `$data` будет игнорироваться (из-за различной формы), но если `$data` является массивом,底层 будет добавить поля массива в форму `data`


### addFile()

Добавление файла для POST-запроса.

!> Использование `addFile` автоматически изменяет `Content-Type` POST на `form-data`. Под底层ом `addFile` основан на `sendfile`, что поддерживает асинхронное отправку больших файлов.

```php
Swoole\Coroutine\Http\Client->addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void
```

  * **Параметры** 

    * **`string $path`**
      * **Функция**: путь к файлу【Обязательный параметр, не может быть пустым или несуществующим файлом】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $name`**
      * **Функция**: имя формы【Обязательный параметр, `key` в параметре `FILES`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $mimeType`**
      * **Функция**: MIME-формат файла【По выбору параметр,底层 автоматически deduces из расширения файла】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $filename`**
      * **Функция**: имя файла【По выбору параметр】
      * **По умолчанию**: `basename($path)`
      * **Другие значения**: нет

    * **`int $offset`**
      * **Функция**: смещение отправляемого файла【По выбору параметр, можно указать начало передачи данных из середины файла. Эта функция может использоваться для поддержки продолжения передачи данных после перезапуска.]**
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`int $length`**
      * **Функция**: размер отправляемых данных【По выбору параметр】
      * **По умолчанию**: по умолчанию - размер всего файла
      * **Другие значения**: нет

  * **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('httpbin.org', 80);
    $cli->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $cli->set(['timeout' => -1]);
    $cli->addFile(__FILE__, 'file1', 'text/plain');
    $cli->post('/post', ['foo' => 'bar']);
    echo $cli->body;
    $cli->close();
});
```


### addData()

Создание содержания файла для отправки с использованием строки. 

!> `addData` доступен начиная с версии `v4.1.0`

```php
Swoole\Coroutine\Http\Client->addData(string $data, string $name, string $mimeType = null, string $filename = null): void
```

  * **Параметры** 

    * **`string $data`**
      * **Функция**: содержание данных【Обязательный параметр, максимальная длина не должна превышать [buffer_output_size](/server/setting?id=buffer_output_size)】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $name`**
      * **Функция**: имя формы【Обязательный параметр, `$_FILES` параметр中的`key`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $mimeType`**
      * **Функция**: MIME-формат файла【По выбору параметр, по умолчанию `application/octet-stream`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

    * **`string $filename`**
      * **Функция**: имя файла【По выбору параметр, по умолчанию `name`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('httpbin.org', 80);
    $client->setHeaders([
        'Host' => 'httpbin.org'
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file1', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    echo $client->body;
    $client->close();
});
```


### get()

Запуск запроса GET.

```php
Swoole\Coroutine\Http\Client->get(string $path): void
```

  * **Параметры** 

    * **`string $path`**
      * **Функция**: Установка пути `URL`【например, `/index.html`, обратите внимание, что здесь нельзя передавать `http://domain`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->setHeaders([
        'Host' => 'localhost',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        * `application/x-www-form-urlencoded`
      * **Обязательный параметр**: нет
      * **Другие значения**: нет

    * **`mixed $data`**
      * **Функция**: данные тела запроса
      * **По умолчанию**: нет
      * **Другие значения**: нет

      !> Если `$data` является массивом,底层 автоматически упакует его в содержание POST в форме `x-www-form-urlencoded` и установит `Content-Type` на `application/x-www-form-urlencoded`

  * **Примечание**

    !> Использование `post` игнорирует установленный методом `setMethod`, принудительно используя `POST`

  * **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 80);
    $client->post('/post.php', array('a' => '123', 'b' => '456'));
    echo $client->body;
    $client->close();
});
```


### upgrade()

Переход в соединение WebSocket.

```php
Swoole\Coroutine\Http\Client->upgrade(string $path): bool
```

  * **Параметры** 

    * **`string $path`**
      * **Функция**: Установка пути `URL`【например, `/`, обратите внимание, что здесь нельзя передавать `http://domain`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Примечания**

    * В некоторых случаях, хотя запрос и успешен, `upgrade` возвращает `true`, но сервер не устанавливает HTTP-состояния `101`, а `200` или `403`, что означает, что сервер отклонил запрос на рукопожатие
    * После успешного рукопожатия WebSocket можно использовать метод `push`, чтобы отправить сообщение на сервер, а также вызвать `recv`, чтобы принять сообщение
    * `upgrade` вызывает одно [координационное распределение](/coroutine?id=координационное распределение)

  * **Пример**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```
### push()

Отправка сообщения на сервер `WebSocket`.

!> Метод `push` может быть вызван только после успешного выполнения `upgrade`  
Метод `push` не создает [координацию协程](/coroutine?id=координация_协程), и возвращает сразу после написания в буфер отправки

```php
Swoole\Coroutine\Http\Client->push(mixed $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

  * **Параметры** 

    * **`mixed $data`**
      * **Функция**: Содержание данных для отправки【по умолчанию в текстовой форме UTF-8, если это другой формат кодирования или двоичные данные, используйте `WEBSOCKET_OPCODE_BINARY`】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

      !> При Swoole версии >= v4.2.0 `$data` может быть объектом [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), поддерживается отправка различных типов кадров

    * **`int $opcode`**
      * **Функция**: Тип операции
      * **По умолчанию**: `WEBSOCKET_OPCODE_TEXT`
      * **Другие значения**: Нет

      !> `$opcode` должен быть законным `WebSocket OPCode`, в противном случае возвращается неудача и печатается ошибка `opcode max 10`

    * **`int|bool $finish`**
      * **Функция**: Тип операции
      * **По умолчанию**: `SWOOLE_WEBSOCKET_FLAG_FIN`
      * **Другие значения**: Нет

      !> Начиная с версии v4.4.12, параметр `finish` (тип `bool`) изменен на `flags` (тип `int`) для поддержки сжатия `WebSocket`, `finish` соответствует значению `SWOOLE_WEBSOCKET_FLAG_FIN` в `1`, существующие значения типа `bool` будутimplicitly преобразованы в тип `int`, эта изменение совместимо и не влияет на работу ниже. Кроме того, флаг сжатия `flag` равен `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

  * **Возвращаемое значение**

    * Успешная отправка возвращает `true`
    * Неисходящее соединение, закрытое соединение, незавершенное `WebSocket`, неудача отправки возвращает `false`

  * **Коды ошибок**


Коды ошибок | Описание
---|---
8502 | Неправильный OPCode
8503 | Не подключен к серверу или соединение уже закрыто
8504 | handshake失败


### recv()

Получение сообщения. Используется только для `WebSocket`, должен использоваться в сочетании с `upgrade()`, см. пример

```php
Swoole\Coroutine\Http\Client->recv(float $timeout = 0)
```

  * **Параметры** 

    * **`float $timeout`**
      * **Функция**: Этот параметр действителен только при вызове `upgrade()` для повышения уровня до соединения `WebSocket`
      * **Единица измерения**: секунды【Поддерживается плавающий тип, например, `1.5` означает `1s`+`500ms`】
      * **По умолчанию**: См. [правила таймаута клиента](/coroutine_client/init?id=правила_таймаута)
      * **Другие значения**: Нет

      !> Установка таймаута, сначала используется указанный параметр, затем `timeout` из конфигурации `set` метода
  
  * **Возвращаемое значение**

    * Успешное выполнение возвращает объект frame
    * Неудача возвращает `false`, и проверяется свойство `errCode` класса `Swoole\Coroutine\Http\Client`, если у координационного клиента нет обратного вызова `onClose`, то при закрытии соединения `recv` возвращает `false` и `errCode=0`
 
  * **Пример**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $client = new Client('127.0.0.1', 9501);
    $ret = $client->upgrade('/');
    if ($ret) {
        while(true) {
            $client->push('hello');
            var_dump($client->recv());
            Coroutine::sleep(0.1);
        }
    }
});
```


### download()

Скачивание файла через HTTP.

!> Отличие `download` от метода `get` заключается в том, что после получения данных `download` записывает их на диск, а не конъюнктивно собирает тело HTTP в памяти. Таким образом, `download` может использовать небольшое количество памяти для скачивания больших файлов.

```php
Swoole\Coroutine\Http\Client->download(string $path, string $filename,  int $offset = 0): bool
```

  * **Параметры** 

    * **`string $path`**
      * **Функция**: Установка пути `URL`
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`string $filename`**
      * **Функция**: Указание пути для записи скачанного содержания【автоматически записывается в свойство `downloadFile`】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`int $offset`**
      * **Функция**: Указание смещения для записи в файле【Эта опция может использоваться для поддержки продолжения передачи через точки возобновления, можно использовать сHTTP заголовком `Range:bytes=$offset`】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

      !> Когда `$offset` равен `0`, и файл уже существует, низший уровень автоматически очистит этот файл

  * **Возвращаемое значение**

    * Успешное выполнение возвращает `true`
    * Неудача при открытии файла или неудача `fseek()` на низшем уровне возвращает `false`

  * **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $host = 'cdn.jsdelivr.net';
    $client = new Client($host, 443, true);
    $client->set(['timeout' => -1]);
    $client->setHeaders([
        'Host' => $host,
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => '*',
        'Accept-Encoding' => 'gzip'
    ]);
    $client->download('/gh/swoole/swoole-src/mascot.png', __DIR__ . '/logo.png');
});
```


### getCookies()

Получение содержания `cookie` HTTP-ответа.

```php
Swoole\Coroutine\Http\Client->getCookies(): array|false
```

!> Информация о `cookie` будет декодирована с использованием urldecode, чтобы получить исходную информацию о `cookie`, пожалуйста, проанализируйте самостоятельно по以下内容

#### Получение повторяющихся `Cookie` или исходной информации о `Cookie` заголовке

```php
var_dump($client->set_cookie_headers);
```


### getHeaders()

Возвращает заголовки HTTP-ответа.

```php
Swoole\Coroutine\Http\Client->getHeaders(): array|false
```


### getStatusCode()

Получение статуса HTTP-ответа.

```php
Swoole\Coroutine\Http\Client->getStatusCode(): int|false
```

  * **Напоминание**

    * **Если статусный код отрицательный, это означает проблемы с подключением.**


Статусный код | Константы, соответствующие v4.2.10 и выше | Описание

---|---|---

-1 | SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED | Соединение超时, сервер не слушает на порту или потеря сети, можно прочитать `$errCode` для получения конкретного сетевого ошибки

-2 | SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT | Запрос超时, сервер не возвращает ответ в течение установленного `timeout` времени

-3 | SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET | После отправки запроса клиенту сервер принудительно разрывает соединение
-4 | SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED | Неудача отправки клиента (эта константа доступна в Swoole версиях >= `v4.5.9`, для версий ниже используйте статусный код)


### getBody()

Получение содержания тела HTTP-ответа.

```php
Swoole\Coroutine\Http\Client->getBody(): string|false
```


### close()

Завершение соединения.

```php
Swoole\Coroutine\Http\Client->close(): bool
```

!> После `close`, если вы снова вызвате методы `get`, `post` и т.д., Swoole поможет вам пересоздать соединение с сервером.


### execute()

Более низкий уровень метода HTTP-запроса, требует вызова методов [setMethod](/coroutine_client/http_client?id=setmethod) и [setData](/coroutine_client/http_client?id=setdata) для установки метода и данных запроса в коде.

```php
Swoole\Coroutine\Http\Client->execute(string $path): bool
```

* **Пример**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $httpClient = new Client('httpbin.org', 80);
    $httpClient->setMethod('POST');
    $httpClient->setData('swoole');
    $status = $httpClient->execute('/post');
    var_dump($status);
    var_dump($httpClient->getBody());
});
```
## Функции

Для удобства использования `Coroutine\Http\Client` были добавлены три функции:

!> Версия Swoole >= `v4.6.4` доступна


### request()

Запускает запрос с указанным методом.

```php
function request(string $url, string $method, $data = null, array $options = null, array $headers = null, array $cookies = null)
```


### post()

Используется для отправки `POST` запроса.

```php
function post(string $url, $data, array $options = null, array $headers = null, array $cookies = null)
```


### get()

Используется для отправки `GET` запроса.

```php
function get(string $url, array $options = null, array $headers = null, array $cookies = null)
```

### Примеры использования

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\request;

run(function () {
    go(function () {
        $data = get('http://httpbin.org/get?hello=world');
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
    });
    go(function () {
        $random_data = base64_encode(random_bytes(128));
        $data = post('http://httpbin.org/post?hello=world', ['random_data' => $random_data]);
        $body = json_decode($data->getBody());
        assert($body->headers->Host === 'httpbin.org');
        assert($body->args->hello === 'world');
        assert($body->form->random_data === $random_data);
    });
});
```
