# Корoutine\Http2\Client

Корoutine HTTP/2 клиент

## Примеры использования

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $domain = 'www.zhihu.com';
    $cli = new Client($domain, 443, true);
    $cli->set([
        'timeout' => -1,
        'ssl_host_name' => $domain
    ]);
    $cli->connect();
    $req = new Request();
    $req->method = 'POST';
    $req->path = '/api/v4/answers/300000000/voters';
    $req->headers = [
        'host' => $domain,
        'user-agent' => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip'
    ];
    $req->data = '{"type":"up"}';
    $cli->send($req);
    $response = $cli->recv();
    var_dump(assert(json_decode($response->data)->error->code === 10002));
});
```

## Методы

### __construct()

Конструктор.

```php
Swoole\Coroutine\Http2\Client::__construct(string $host, int $port, bool $open_ssl = false): void
```

  * **Параметры** 

    * **`string $host`**
      * **Функция**: IP-адрес целевого хоста【Если `$host` представляет собой домен, необходимо выполнить `DNS` запрос】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`int $port`**
      * **Функция**: целевой порт【Для `Http` обычно используется порт `80`, для `Https` обычно используется порт `443`】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`bool $open_ssl`**
      * **Функция**: Отключить или включить туннель шифрования TLS/SSL 【Для сайтов `https` необходимо установить на `true`】
      * **По умолчанию**: `false`
      * **Другие значения**: `true`

  * **Примечание**

    !> - Если вам нужно запросить внешний URL, измените `timeout` на более большое значение, см. [правила таймаута клиента](/coroutine_client/init?id=правила таймаута)  
    - `$ssl` требует зависимости от `openssl`, необходимо включить `--enable-openssl` при сборке `Swoole` 【/environment?id=Сборочные опции】


### set()

Установка параметров клиента, для других подробных настроек смотрите [Swoole\Client::set](/client?id=настройки)

```php
Swoole\Coroutine\Http2\Client->set(array $options): void
```


### connect()

Соединение с целевым сервером. Этот метод не принимает никаких параметров.

!> После начала `connect`, на нижнем уровне автоматически происходит [координационный расписание](/coroutine?id=координационное расписание), и `connect` возвращается, когда соединение успешно или неудачно. После установления соединения можно вызвать метод `send`, чтобы отправить запрос на сервер.

```php
Swoole\Coroutine\Http2\Client->connect(): bool
```

  * **Возвращаемое значение**

    * Успешное соединение возвращает `true`
    * Неудача соединения возвращает `false`, пожалуйста, проверьте свойство `errCode` для получения ошибки


### stats()

Получение состояния потока.

```php
Swoole\Coroutine\Http2\Client->stats([$key]): array|bool
```

  * **Пример**

```php
var_dump($client->stats(), $client->stats()['local_settings'], $client->stats('local_settings'));
```


### isStreamExist()

Проверка наличия указанного потока.

```php
Swoole\Coroutine\Http2\Client->isStreamExist(int $stream_id): bool
```


### send()

Отправка запроса на сервер, на нижнем уровне автоматически создается `Http2` потока. Можно отправить одновременно несколько запросов.

```php
Swoole\Coroutine\Http2\Client->send(Swoole\Http2\Request $request): int|false
```

  * **Параметры** 

    * **`Swoole\Http2\Request $request`**
      * **Функция**: Отправка объекта Swoole\Http2\Request
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

  * **Возвращаемое значение**

    * Успешное возвращение номера потока, номер потока начинается с `1` и увеличивается поочередно от нечетных чисел
    * Неудача возвращает `false`

  * **Примечание**

    * **Объект Request**

      !> Объект `Swoole\Http2\Request` не имеет никаких методов, информация о запросе записывается путем установки свойств объекта.

      * массив `headers`, HTTP-заголовки
      * строка `method`, установка метода запроса, например `GET`, `POST`
      * строка `path`, установка пути URL, например `/index.php?a=1&b=2`, должен начинаться с `/`
      * массив `cookies`, установка COOKIES
      * `data` 设置 тело запроса, если это строка, то она будет напрямую отправлена в виде `RAW form-data`
      * Если `data` - массив, на нижнем уровне автоматически упакует его в формат `x-www-form-urlencoded` для POST-запроса и установит `Content-Type` как `application/x-www-form-urlencoded`
      *布尔ое значение `pipeline`, если установлено как `true`, после отправки `$request` не закрывать поток, можно продолжать писать данные

    * **pipeline**

      * По умолчанию метод `send` после отправки запроса завершает текущий `Http2 Stream`, после включения `pipeline` на нижнем уровне сохраняется поток, можно неоднократно вызвать метод `write`, чтобы отправить данные фрейма на сервер, смотрите метод `write`.


### write()

Отправка большего количества данных фреймов на сервер, можно неоднократно вызвать `write`, чтобы отправить данные фрейма на тот же поток.

```php
Swoole\Coroutine\Http2\Client->write(int $streamId, mixed $data, bool $end = false): bool
```

  * **Параметры** 

    * **`int $streamId`**
      * **Функция**: Номер потока, возвращенный методом `send`
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`mixed $data`**
      * **Функция**: Содержание данных фрейма, может быть строкой или массивом
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

    * **`bool $end`**
      * **Функция**: Закрыть поток
      * **По умолчанию**: `false`
      * **Другие значения**: `true`

  * **Пример использования**

```php
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9518);
    $cli->set(['timeout' => 1]);
    var_dump($cli->connect());

    $req3 = new Request();
    $req3->path = "/index.php";
    $req3->headers = [
        'host' => "localhost",
        "user-agent" => 'Chrome/49.0.2587.3',
        'accept' => 'text/html,application/xhtml+xml,application/xml',
        'accept-encoding' => 'gzip',
    ];
    $req3->pipeline = true;
    $req3->method = "POST";
    $streamId = $cli->send($req3);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    $cli->write($streamId, ['int' => rand(1000, 9999)]);
    //закрыть поток
    $cli->write($streamId, ['int' => rand(1000, 9999), 'end' => true], true);
    var_dump($cli->recv());
    $cli->close();
});
```

!> Для использования `write` для отправки данных фреймов по частям, необходимо установить `pipeline` в `true` в `send` запросе  
После отправки фрейма с `end` равным `true`, поток будет закрыт, и после этого нельзя больше использовать `write`, чтобы отправить данные на этот `stream`.


### recv()

Получение запроса.

!> Когда этот метод вызван, происходит [координационное расписание](/coroutine?id=координационное расписание)

```php
Swoole\Coroutine\Http2\Client->recv(float $timeout): Swoole\Http2\Response;
```

  * **Параметры** 

    * **`float $timeout`**
      * **Функция**: Установка времени ожидания, см. [правила таймаута клиента](/coroutine_client/init?id=правила таймаута)
      * **Единица измерения**: секунды 【Поддерживается плавающая точка, например `1.5` означает `1s`+`500ms`】
      * **По умолчанию**: Нет
      * **Другие значения**: Нет

  * **Возвращаемое значение**

Успешное возвращение объекта Swoole\Http2\Response

```php
/**@var $resp Swoole\Http2\Response */
var_dump($resp->statusCode); // HTTP-статусный код, отправленный сервером, например 200, 502 и т.д.
var_dump($resp->headers); // Заголовок информации, отправленный сервером
var_dump($resp->cookies); // Информация о COOKIES, установленная сервером
var_dump($resp->set_cookie_headers); // Оригинальная информация о COOKIES, отправленная сервером, включая domain и path
var_dump($resp->data); // Содержание ответа, отправленное сервером
```

!> При Swoole версии < [v4.0.4](/version/bc?id=_404) свойство `data` является свойством `body`; при Swoole версии < [v4.0.3](/version/bc?id=_403) заголовки и COOKIES представлены в единственной форме.
### read()

Идеально похож на `recv()`, отличие заключается в том, что для ответов типа `pipeline` `read` может читать в несколько этапов, каждый раз получая часть содержимого для экономии памяти или чтобы как можно скорее получить информацию о push. В то время как `recv` всегда соединяет все фреймы в один полный ответ, прежде чем вернуть его.

!> Когда этот метод вызывается, происходит [координационный диспетчер](/coroutine?id=координационный-диспетчер)

```php
Swoole\Coroutine\Http2\Client->read(float $timeout): Swoole\Http2\Response;
```

  * **Параметры** 

    * **`float $timeout`**
      * **Функция**: Установить время ожидания, см. [правила таймаута клиента](/coroutine_client/init?id=правила-таймаута)
      * **Единица измерения**: секунды【Поддерживается плавающая точка, например, `1.5` означает `1s`+`500ms`】
      * **По умолчанию**: нет
      * **Другие значения**: нет

  * **Возвращаемое значение**

    В случае успеха возвращается объект класса Swoole\Http2\Response


### goaway()

Фрейм GOAWAY используется для инициирования закрытия соединения или передачи сигнала о серьезном ошибочном состоянии.

```php
Swoole\Coroutine\Http2\Client->goaway(int $error_code = SWOOLE_HTTP2_ERROR_NO_ERROR, string $debug_data): bool
```


### ping()

Фрейм PING является механизмом для измерения минимального времени обратной связи от отправителя и определения, все ли свободные соединения все еще действительны.

```php
Swoole\Coroutine\Http2\Client->ping(): bool
```

### close()

Завершить соединение.

```php
Swoole\Coroutine\Http2\Client->close(): bool
```
