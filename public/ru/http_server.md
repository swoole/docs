# Http\Server

?> `Http\Server` наследуется от [Server](/server/init), так что все `API` и настройки, предоставляемые `Server`, могут быть использованы, а модель процесса также одинакова. Пожалуйста, обратитесь к главе [Server](/server/init).

Поддержка встроенного `HTTP` сервера позволяет написать высокоConcurrency, высокоэффективный, [асинхронный IO](/learn?id=syncioasynchronousio) многопроцессный `HTTP` сервер с несколькими строками кода.

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Привет, Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

Используя инструмент `Apache bench` для нагрузочного тестирования, на обычном PC с `Inter Core-I5 4 ядра + 8G памяти`, `Http\Server` может достичь почти `110 тысяч QPS`.

Это значительно превосходит встроенные `HTTP` серверы `PHP-FPM`, `Golang`, `Node.js`.performans почти соответствует обработке статических файлов `Nginx`.

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **Использование протокола HTTP2**

  * Для использования `HTTP2` под `SSL` необходимо установить `openssl`, и требуется высокая версия `openssl`, которая должна поддерживать `TLS1.2`, `ALPN`, `NPN`
  * При сборке необходимо использовать [--enable-http2](/environment?id=编译选项) для включения
  * Начиная с Swoole5, по умолчанию включен протокол http2

```shell
./configure --enable-openssl --enable-http2
```

Установите [open_http2_protocol](/http_server?id=open_http2_protocol) `HTTP` сервера на `true`

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Конфигурация Nginx + Swoole**

!> Поскольку поддержка `HTTP` протокола у `Http\Server` не полна, рекомендуется использовать его только как приложение сервер для обработки динамических запросов, а также добавить передний край `Nginx` в качестве прокси.

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> Можно получить фактический `IP` клиента,读取 `header['x-real-ip']` от `$request`


## Методы


### on()

?> **Зарегистрировать функцию обратной связи события.**

?> Подобно [обратной связи Server](/server/events), отличие заключается в следующем:

  * `Http\Server->on` не принимает настройки обратной связи [onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)
  * `Http\Server->on` дополнительно принимает новый тип события `onRequest`, когда клиент отправляет запрос, он выполняется в `Request` событии

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

После получения полного HTTP запроса будет вызвана эта функция. У функции обратной связи два параметра:

* [Swoole\Http\Request](/http_server?id=httpRequest), объект информации о `HTTP` запросе, содержащий информацию о `header/get/post/cookie` и т.д.
* [Swoole\Http\Response](/http_server?id=httpResponse), объект ответа `HTTP`, поддерживающий `cookie/header/status` и другие `HTTP` операции

!> Когда функция обратной связи [onRequest](/http_server?id=on) возвращается, низкий уровень уничтожает объекты `$request` и `$response`


### start()

?> **Запустить HTTP сервер**

?> После запуска начинается прослушивание порта и прием новых `HTTP` запросов.

```php
Swoole\Http\Server->start();
```


## Swoole\Http\Request

Объект `HTTP` запроса, хранящий информацию о запросе от клиента `HTTP`, включая `GET`, `POST`, `COOKIE`, `Header` и т.д.

!> Не используйте символ `&` для ссылки на объект `Http\Request`


### header

?> **Информация о заголовках `HTTP` запроса. Тип - массив, все `key` в нижнем регистре.**

```php
Swoole\Http\Request->header: array
```

* **Пример**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```


### server

?> **Информация о сервере, связанной с `HTTP` запросом.**

?> Соответствует массиву `$_SERVER` в `PHP`. Содержание включает метод `HTTP` запроса, путь `URL`, IP-адрес клиента и т.д.

```php
Swoole\Http\Request->server: array
```

Ключи массива полностью в нижнем регистре и соответствуют массиву `$_SERVER` в `PHP`

* **Пример**

```php
echo $request->server['request_time'];
```


Ключ | Описание
---|---
query_string | Запросные параметры `GET`, например: `id=1&cid=2`. Если нет параметров `GET`, этот элемент отсутствует
request_method | Метод запроса, `GET/POST` и т.д.
request_uri | Адрес доступа без параметров `GET`, например `/favicon.ico`
path_info | То же самое, что и `request_uri`
request_time | `request_time` установлен на уровне `Worker`, в режиме [SWOOLE_PROCESS](/learn?id=swoole_process) существует процесс `dispatch`, поэтому может существовать разница между временем, когда запрос был получен, и временем его обработки. Особенно когда объем запросов превышает возможности сервера, `request_time` может значительно отстать от времени фактического приема пакета. accurate время приема пакета можно получить с помощью метода `$server->getClientInfo` `last_time`.
request_time_float | Время начала запроса в микросекундах, тип `float`, например `1576220199.2725`
server_protocol | Версия протокола сервера, для `HTTP`: `HTTP/1.0` или `HTTP/1.1`, для `HTTP2`: `HTTP/2`
server_port | Порт, на котором слушает сервер
remote_port | Порт клиента
remote_addr | IP-адрес клиента
master_time | Время последнего общения с мастер-сервером


### get

?> **Параметры запроса `GET` HTTP, аналогичны `$_GET` в PHP, формат - массив.**

```php
Swoole\Http\Request->get: array
```

* **Пример**

```php
// Например: index.php?hello=123
echo $request->get['hello'];
// Получение всех параметров GET
var_dump($request->get);
```

* **Примечание**

!> Чтобы предотвратить атаку `HASH`, максимальное количество параметров `GET` не должно превышать `128`


### post

?> **Параметры запроса `POST` HTTP, формат - массив**

```php
Swoole\Http\Request->post: array
```

* **Пример**

```php
echo $request->post['hello'];
```

* **Примечание**


!> - Summa размер `POST` и `Header` не должен превышать значение, установленное в [package_max_length](/server/setting?id=package_max_length), иначе это будет считаться злонамеренным запросом  
- Максимальное количество параметров `POST` не должно превышать `128`


### cookie

?> **Информация о `COOKIE` в запросе HTTP, формат - массив ключ-значение.**

```php
Swoole\Http\Request->cookie: array
```

* **Пример**

```php
echo $request->cookie['username'];
```


### files

?> **Информация о загруженных файлах. **

?> Формат - двумерный массив с ключом в виде `form` имени. Подобен `$_FILES` в PHP. Максимальная размер файла не должен превышать значение, установленное в [package_max_length](/server/setting?id=package_max_length). Поскольку Swoole занимает память при анализе сообщения, чем больше сообщение, тем больше памяти используется, поэтому не следует использовать `Swoole\Http\Server` для обработки больших загрузок файлов или реализовать функции перезапуска загрузки через пользовательский дизайн.

```php
Swoole\Http\Request->files: array
```

* **Пример**

```php
Array
(
    [name] => facepalm.jpg // Название файла, отправленного браузером при загрузке
    [type] => image/jpeg // MIME-тип
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // временный файл для загрузки, имя файла начинается с /tmp/swoole.upfile
    [error] => 0
    [size] => 15476 // Размер файла
)
```

* **Примечание**

!> Когда объект `Swoole\Http\Request` уничтожается, временные файлы для загрузки автоматически удаляются

### getContent()

!> С версии Swoole ≥ `v4.5.0` tersedia, для старых версий можно использовать псевдоним `rawContent` (этот псевдоним останется вечно, то есть будет совместим с прошлыми версиями).

?> **Получить原始的 `POST` пакетное тело.**

?> Используется для HTTP `POST` запросов, не соответствующих формату `application/x-www-form-urlencoded`. Возвращает原始 `POST` данные, эта функция эквивалентна `fopen('php://input')` в `PHP`.

```php
Swoole\Http\Request->getContent(): string|false
```

  * **Возвращаемое значение**

    * Если успешно, возвращает пакет, если контекст соединения отсутствует, возвращает `false`.

!> В некоторых случаях серверу не нужно анализировать параметры HTTP `POST` запросов, Configuring [http_parse_post](/http_server?id=http_parse_post), можно выключить анализ данных `POST`.

### getData()

?> **Получить полный原始的 `Http` запросный пакет, обратите внимание, что в `Http2`框架下 его использование невозможно. Включает в себя `Http Header` и `Http Body`**

```php
Swoole\Http\Request->getData(): string|false
```

  * **Возвращаемое значение**

    * Если успешно, возвращает пакет, если контекст соединения отсутствует или в режиме `Http2`, возвращает `false`.


### create()

?> **Создать объект `Swoole\Http\Request`.**

!> С версии Swoole ≥ `v4.6.0` доступен

```php
Swoole\Http\Request::create(array $options): Swoole\Http\Request;
```

  * **Параметры**

    * **`array $options`**
      * **Функция**: необязательный параметр, используется для настройки конфIGурации `Request` объекта

| Параметр                                          | По умолчанию | Описание                                                                |
| ------------------------------------------------- | ---------- | ----------------------------------------------------------------- |
| [parse_cookie](/http_server?id=http_parse_cookie) | истинно   | Установить, 解读овать ли `Cookie`                                        |
| [parse_body](/http_server?id=http_parse_post)      | истинно   | Установить, 解读овать ли `Http Body`                                     |
| [parse_files](/http_server?id=http_parse_files)   | истинно   | Установить, 解读овать ли загружаемые файлы                               |
| enable_compression                                | истинно, если сервер не поддерживает сжатие пакетов, по умолчанию - ложь | Установить, включить ли сжатие                                                 |
| compression_level                                 | 1          | Установить уровень сжатия, диапазон от 1 до 9, чем выше уровень, тем меньше размер сжатого объекта, но больше CPU потребления        |
| upload_tmp_dir                                 | /tmp       |斯图储藏 директория для временных файлов, используется для загрузки файлов        |

  * **Возвращаемое значение**

    * Возвращает объект `Swoole\Http\Request`

* **Пример**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```

### parse()

?> **Анализировать HTTP запросный пакет данных, вернет длину успешно проанализированных данных.**

!> С версии Swoole ≥ `v4.6.0` доступен

```php
Swoole\Http\Request->parse(string $data): int|false
```

  * **Параметры**

    * **`string $data`**
      * Пакет, который необходимо проанализировать

  * **Возвращаемое значение**

    * Если анализ успешно, возвращает длину проанализированного пакета, если контекст подключения отсутствует или контекст уже завершен, возвращает `false`


### isCompleted()

?> **Получить текущее состояние HTTP запроса, был ли пакет dados полностью получен.**

!> С версии Swoole ≥ `v4.6.0` доступен

```php
Swoole\Http\Request->isCompleted(): bool
```

  * **Возвращаемое значение**

    * `true`, если пакет уже完整 получен, `false` - если контекст соединения завершен или пакет не достиг конца

* **Пример**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n";
$data .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n";
$data .= "Accept-Encoding: gzip, deflate, br\r\n";
$data .= "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,ja;q=0.6\r\n";
$data .= "Cookie: env=pretest; phpsessid=fcccs2af8673a2f343a61a96551c8523d79ea; username=hantianfeng\r\n";

/** @var Request $req */
$req = Request::create(['parse_cookie' => false]);
var_dump($req);

var_dump($req->isCompleted());
var_dump($req->parse($data));

var_dump($req->parse("\r\n"));
var_dump($req->isCompleted());

var_dump($req);
// cookie анализ был выключен, поэтому он `null`
var_dump($req->cookie);
```


### getMethod()

?> **Получить текущее HTTP-вопросительное действие.**

!> С версии Swoole ≥ `v4.6.2` доступно

```php
Swoole\Http\Request->getMethod(): string|false
```
  * **Возвращаемое значение**

    * Возвращает заглавные слова действия запроса, `false` - если контекст соединения отсутствует.

```php
var_dump($request->server['request_method']);
var_dump($request->getMethod());
```


## Swoole\Http\Response

Объект HTTP-ответа, с помощью методов этого объекта можно отправить HTTP-ответ.

?> Когда объект `Response` уничтожается и при этом не вызван метод [end](/http_server?id=end) для отправки HTTP-ответа, низший уровень автоматически выполнит `end("")`.

!> Не следует использовать символ `&` для ссылки на объект `Http\Response`


### header() :id=setheader

?> **Установить информацию оsetHeader HTTP-ответа** 【Псевдоним `setHeader`】

```php
Swoole\Http\Response->header(string $key, string $value, bool $format = true): bool;
```

* **Параметры** 

  * **`string $key`**
    * **Функция**: Ключ HTTP-header
    * **По умолчанию**: нет
    * **Другие значения**: нет

  * **`string $value`**
    * **Функция**: значение HTTP-header
    * **По умолчанию**: нет
    * **Другие значения**: нет

  * **`bool $format`**
    * **Функция**: нужно ли格式ировать ключ согласно HTTP- convencциям 【по умолчанию `true` - автоматически форматируется】
    * **По умолчанию**: `true`
    * **Другие значения**: нет

* **Возвращаемое значение** 

  * Если установление неудачно, возвращает `false`
  * Если успешно установлен, возвращает `true`
* **Примечание**

   - Установка `header` должна происходить до вызова метода `end`
   - `$key` должен полностью соответствовать HTTP- convencциям, каждый слово начинается с заглавной буквы, не должен содержать китайских символов, подчеркивания или прочие специальные символы
   - `$value` должно быть заполнено
   - Если установить `$ucwords` как `true`, низший уровень автоматически форматирует `$key` согласно convencциям
   - повторное установка одинаковых `$key` HTTP-header заменится последним
   - Если клиент установил `Accept-Encoding`, то сервер не должен устанавливать `Content-Length` в ответе, `Swoole` обнаружит эту ситуацию и игнорирует значение `Content-Length`,抛出 предупреждающий сигнал.
   - Установка `Content-Length` в ответе не позволяет вызвать метод `Swoole\Http\Response::write()`, `Swoole` обнаружит эту ситуацию и игнорирует значение `Content-Length`,抛出 предупреждающий сигнал.

!> При Swoole версии ≥ `v4.6.0` поддерживается повторная установка одинаковых `$key` HTTP-header, и `$value` поддерживает различные типы, такие как `array`, `object`, `int`, `float`. низший уровень будет выполнять преобразование в `toString` и удалять хвостовые пробелы и переводы строк.

* **Пример**

```php
$response->header('content-type', 'image/jpeg', true);

$response->header('Content-Length', '100002 ');
$response->header('Test-Value', [
    "a\r\n",
    'd5678',
    "e  \n ",
    null,
    5678,
    3.1415926,
]);
$response->header('Foo', new SplFileInfo('bar'));
```
### трейлер()

?> **Приложить информацию о `Header` к концу `HTTP` ответа, доступна только в `HTTP2`, используется для проверки целостности сообщения, цифровой подписи и т.д.**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **Параметры** 

  * **`string $key`**
    * **Функция**：Ключ `Header` HTTP
    * **По умолчанию**：нет
    * **Другие значения**：нет

  * **`string $value`**
    * **Функция**：Значение `Header` HTTP
    * **По умолчанию**：нет
    * **Другие значения**：нет

* **Возвращаемое значение** 

  * Если установка потерпела неудачу, возвращается `false`
  * Если установка прошла успешно, возвращается `true`

* **Примечание**

  !> Повторное установление одинаковых `$key` HTTP-голов не заменится, сохранится последняя.

* **Пример**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```


### cookie()

?> **Установить информацию о `cookie` HTTP-ответа. алиас `setCookie`. Параметры этого метода совпадают с `setcookie` PHP.**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **Параметры** 

    * **`string $key`**
      * **Функция**：Ключ `Cookie`
      * **По умолчанию**：нет
      * **Другие значения**：нет

    * **`string $value`**
      * **Функция**：Значение `Cookie`
      * **По умолчанию**：нет
      * **Другие значения**：нет
  
    * **`int $expire`**
      * **Функция**：Время истечения `Cookie`
      * **По умолчанию**：0, не истекает
      * **Другие значения**：нет

    * **`string $path`**
      * **Функция**：Установить путь для Cookie на сервере.
      * **По умолчанию**：/
      * **Другие значения**：нет

    * **`string $domain`**
      * **Функция**：Установить домен для Cookie
      * **По умолчанию**：''
      * **Другие значения**：нет

    * **`bool $secure`**
      * **Функция**：Установить, передавать ли Cookie через безопасное HTTPS-соединение
      * **По умолчанию**：''
      * **Другие значения**：нет

    * **`bool $httponly`**
      * **Функция**：Разрешить ли браузеру JavaScript доступ к Cookie с атрибутом HttpOnly, `true` - не разрешить, `false` - разрешить
      * **По умолчанию**：false
      * **Другие значения**：нет

    * **`string $samesite`**
      * **Функция**：Ограничить третьи стороны Cookie, чтобы уменьшить риски безопасности
      * **По умолчанию**：''
      * **Другие значения**：нет

    * **`string $priority`**
      * **Функция**：Приоритет Cookie, когда количество Cookie превышает установленные ограничения, Cookie с более низким приоритетом будет удален в первую очередь
      * **По умолчанию**：''
      * **Другие значения**：нет
  
  * **Возвращаемое значение** 

    * Если установка потерпела неудачу, возвращается `false`
    * Если установка прошла успешно, возвращается `true`

* **Примечание**

  !> - Установка `cookie` должна быть выполнена до метода [end](/http_server?id=end)  
  - Параметр `$samesite` поддерживается начиная с версии `v4.4.6`, параметр `$priority` поддерживается начиная с версии `v4.5.8`  
  - Swoole автоматически будет URL-кодировать `$value`, можно использовать метод `rawCookie()` чтобы отключить кодирование `$value`  
  - Swoole позволяет устанавливать несколько Cookie с одинаковым `$key`


### rawCookie()

?> **Установить информацию о `cookie` HTTP-ответа**

!> Параметры `rawCookie()` совпадают с предыдущим `cookie()`, только без обработки кодирования


### status()

?> **Отправьте `Http`-status-код. алиас `setStatusCode()`**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **Параметры** 

  * **`int $http_status_code`**
    * **Функция**：Установить `HttpCode`
    * **По умолчанию**：нет
    * **Другие значения**：нет

  * **`string $reason`**
    * **Функция**：Причина status-кода
    * **По умолчанию**：''
    * **Другие значения**：нет

  * **Возвращаемое значение** 

    * Если установка потерпела неудачу, возвращается `false`
    * Если установка прошла успешно, возвращается `true`

* **Примечание**

  * Если передан только первый параметр `$http_status_code`, он должен быть действительным `HttpCode`, например `200`, `502`, `301`, `404` и т.д., иначе будет установлен статус-код `200`
  * Если установлен второй параметр `$reason`, `$http_status_code` может быть любым числом, включая неопределенный `HttpCode`, например `499`
  * Метод `status` должен быть вызван до [$response->end()](/http_server?id=end)


### gzip()

!> Этот метод был от废弃ен в версиях `4.1.0` или выше, пожалуйста, перейдите к [http_compression](/http_server?id=http_compression); в новых версиях для замены метода `gzip` используется настройка `http_compression`.  
Основная причина, по которой метод `gzip()` не проверяет传入 в заголовок `Accept-Encoding` браузера-клиент, заключается в том, что если клиент не поддерживает сжатие `gzip`, его принудительное использование приведет к тому, что клиент не сможет распаковать данные.  
Новая настройка `http_compression` автоматически выбирает, следует ли использовать сжатие в соответствии с заголовком `Accept-Encoding` клиента и выбирает лучшее сжатие.

?> **Включить сжатие HTTP GZIP. Сжатие может уменьшить размер содержания HTML, эффективно сэкономить сетевое带宽 и улучшить время отклика. Необходимо выполнить `gzip` перед отправкой контента в `write/end`, иначе возникнет ошибка.**
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **Параметры** 
   
     * **`int $level`**
       * **Функция**：Уровень сжатия, чем выше уровень, тем меньше размер сжатого контента, но больше потребления `CPU`.
       * **По умолчанию**：1
       * **Другие значения**：`1-9`

!> После вызова метода `gzip` нижестоящая система автоматически добавит заголовок `Http` для сжатия, и в PHP-коде не следует устанавливать соответствующие `Http`-заголовки; изображения в форматах `jpg/png/gif` уже сжаты и не требуют повторного сжатия

!> Функция `gzip` зависит от библиотеки `zlib`, и при сборке Swoole нижестоящая система проверяет наличие `zlib` в системе, и если оно отсутствует, метод `gzip` будет недоступен. Можно установить библиотеку `zlib` с помощью `yum` или `apt-get`:

```shell
sudo apt-get install libz-dev
```


### redirect()

?> **Отправьте HTTP-переход. После вызова этого метода автоматически будет вызван `end`, что приведет к завершению ответа.**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **Параметры** 
* **Параметры** 
  * **Параметры** 
  * **Параметры** 

    * **`string $url`**
      * **Функция**：Новая адресация для перенаправления, отправляется в качестве заголовка `Location`
      * **По умолчанию**：нет
      * **Другие значения**：нет

    * **`int $http_code`**
      * **Функция**：HTTP-код состояния 【по умолчанию `302` - временное перенаправление,传入 `301` - постоянное перенаправление】
      * **По умолчанию**：`302`
      * **Другие значения**：нет

  * **Возвращаемое значение** 

    * Если вызов успешно, возвращается `true`, если вызов потерпел неудачу или контекст соединения отсутствует, возвращается `false`

* **Пример**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```
### write()

?> **Включает сегментацию `Http Chunk` для отправки соответствующего содержимого браузеру.**

?> Для информации о `Http Chunk` можно обратиться к стандартному документу протокола `Http`.

```php
Swoole\Http\Response->write(string $data): bool
```

  * **Параметры** 

    * **`string $data`**
      * **Функция** : Содержание данных для отправки [максимальная длина не должна превышать `2M`, контролируется параметрами [buffer_output_size](/server/setting?id=buffer_output_size)]
      * **Значение по умолчанию** : Нет
      * **Другие значения** : Нет

  * **Возвращаемое значение** 
  
    * Если вызовы успешно, возвращается `true`, в случае неудачи или отсутствия контекста соединения, возвращается `false`

* **Примечания**

  * После использования `write` для сегментированной отправки данных, метод [end](/http_server?id=end) не примет никаких параметров, вызов `end` просто отправит `Chunk` длиной `0` для обозначения завершения передачи данных
  * Если с помощью метода Swoole\Http\Response::header() установлено `Content-Length`, а затем вызван этот метод, Swoole忽略ет设置为 `Content-Length` и выдает предупреждение
  * `Http2` не поддерживает эту функцию, в противном случае будет вы edilо предупреждение
  * Если клиент поддерживает сжатие ответа, `Swoole\Http\Response::write()` принудительно отключит сжатие


### sendfile()

?> **Отправка файла в браузер.**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **Параметры** 

    * **`string $filename`**
      * **Функция** : Название файла для отправки [если файл отсутствует или нет разрешений на доступ, `sendfile` потерпит неудачу]
      * **Значение по умолчанию** : Нет
      * **Другие значения** : Нет

    * **`int $offset`**
      * **Функция** :Offset отправляемого файла [это позволяет начать передачу данных с середины файла. Эта особенность может использоваться для поддержки продолжения пересылки файлов]
      * **Значение по умолчанию** : `0`
      * **Другие значения** : Нет

    * **`int $length`**
      * **Функция** : Размеры отправляемых данных
      * **Значение по умолчанию** : Размеры файла
      * **Другие значения** : Нет

  * **Возвращаемое значение** 

      * Если вызовы успешно, возвращается `true`, в случае неудачи или отсутствия контекста соединения, возвращается `false`

* **Примечания**

  * из-за того что уровень cannot infer MIME type отправляемого файла, необходимо использовать код для определения `Content-Type`
  * Перед вызовом `sendfile` нельзя использовать метод `write` для отправки `Http-Chunk`
  * После вызова `sendfile` уровень автоматически выполнит `end`
  * `sendfile` не поддерживает `gzip` сжатие

* **Пример**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```


### end()

?> **Отправка тела HTTP ответа и завершение обработки запроса.**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **Параметры** 
  
    * **`string $html`**
      * **Функция** : Содержание для отправки
      * **Значение по умолчанию** : Нет
      * **Другие значения** : Нет

  * **Возвращаемое значение** 

    * Если вызовы успешно, возвращается `true`, в случае неудачи или отсутствия контекста соединения, возвращается `false`

* **Примечания**

  * `end` может быть вызван только один раз, если необходимо отправить данные клиенту в несколько этапов, используйте метод [write](/http_server?id=write)
  * Если клиент включил [KeepAlive](/coroutine_client/http_client?id=keep_alive), соединение будет сохраняться, сервер будет ждать следующего запроса
  * Если клиент не включил `KeepAlive`, сервер закроет соединение
  * Содержание, которое должно быть отправлено с помощью `end`, بسبب ограничений [output_buffer_size](/server/setting?id=buffer_output_size), по умолчанию составляет `2M`, если оно превышает этот предел, ответ будет失敗, и будет выдана следующая ошибка:

!> Решение: используйте [sendfile](/http_server?id=sendfile), [write](/http_server?id=write) или скорректируйте [output_buffer_size](/server/setting?id=buffer_output_size)

```bash
WARNING finish (ERRNO 1203): Длинный данные [262144] превышает размер выходного буфера [131072], пожалуйста, используйте sendfile, режим сегментированного передачи или скорректируйте размер выходного буфера
```


### detach()

?> **Отделение объекта ответа.** Использование этого метода после того, как объект `$response` будет уничтожен, не будет автоматически вызваться [end](/http_server?id=httpresponse), используется в сочетании с [Http\Response::create](/http_server?id=create) и [Server->send](/server/methods?id=send).

```php
Swoole\Http\Response->detach(): bool
```

  * **Возвращаемое значение** 

    * Если вызовы успешно, возвращается `true`, в случае неудачи или отсутствия контекста соединения, возвращается `false`

* **Пример** 

  * **Ответ между процессами**

  ?> В некоторых случаях необходимо отправить ответ клиенту в [Процесс Task](/learn?id=taskworker进程). В это время можно использовать `detach`, чтобы сделать объект `$response` независимым. В [Процесс Task](/learn?id=taskworker进程) можно вновь создать `$response`, отправить HTTP-ответ на запрос. 

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->set(['task_worker_num' => 1, 'worker_num' => 1]);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->task(strval($resp->fd));
  });

  $http->on('finish', function () {
      echo "task finish";
  });

  $http->on('task', function ($serv, $task_id, $worker_id, $data) {
      var_dump($data);
      $resp = Swoole\Http\Response::create($data);
      $resp->end("in task");
      echo "async task\n";
  });

  $http->start();
  ```

  * **Отправка любого содержания**

  ?> В некоторых специальных случаях необходимо отправить клиенту особый ответный контент. Метод end объекта Http\Response не удовлетворяет этим потребностям, можно использовать `detach`, чтобы отделить объект ответа, затем самостоятельно собрать данные HTTP-ответа и отправить данные с помощью Server->send.

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```


### create()

?> **Создание нового объекта Swoole\Http\Response.**

!> Перед использованием этого метода обязательно следует вызвать метод `detach`, чтобы отделить старый объект `$response`, иначе может произойти отправка ответа на один и тот же запрос дважды.

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

  * **Параметры** 

    * **`int $server`**
      * **Функция** : Объект Swoole\Server или Swoole\Coroutine\Socket, массив (массив может иметь только два параметра, первый - объект Swoole\Server, второй - объект Swoole\Http\Request), или дескриптор файла
      * **Значение по умолчанию** : `-1`
      * **Другие значения** : Нет

    * **`int $fd`**
      * **Функция** : Дескриптор файла. Если параметр `$server` является объектом Swoole\Server, `$fd` является обязательным
      * **Значение по умолчанию** : `-1`
      * 
      * **Другие значения** : Нет

  * **Возвращаемое значение** 

    * Если вызовы успешно, возвращается новый объект Swoole\Http\Response, в случае неудачи возвращается `false`

* **Пример**

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->detach();
    // Пример 1
    $resp2 = Swoole\Http\Response::create($req->fd);
    // Пример 2
    $resp2 = Swoole\Http\Response::create($http, $req->fd);
    // Пример 3
    $resp2 = Swoole\Http\Response::create([$http, $req]);
    // Пример 4
    $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, IPPROTO_IP);
    $socket->connect('127.0.0.1', 9501)
    $resp2 = Swoole\Http\Response::create($socket);
    $resp2->end("hello world");
});

$http->start();
```
### isWritable()

?> **Проверяет, что объект `Swoole\Http\Response` уже завершен (`end`) или отделен (`detach`).**

```php
Swoole\Http\Response->isWritable(): bool
```

  * **Возвращаемое значение** 

    * Если объект `Swoole\Http\Response` не завершен или не отделен, возвращается `true`, в противном случае - `false`.


!> Версия Swoole >= `v4.6.0` доступна

* **Пример**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->isWritable()); // true
    $resp->end('hello');
    var_dump($resp->isWritable()); // false
    $resp->setStatusCode(403); // http response is unavailable (maybe it has been ended or detached)
});

$http->start();
```


## Конфигурационные опции


### http_parse_cookie

?> **Конфигурация для объекта `Swoole\Http\Request`, отключение анализа `Cookies`, сохранение необработанных原始的 `Cookies` информации в `header`. По умолчанию включено**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```


### http_parse_post

?> **Конфигурация для объекта `Swoole\Http\Request`, установка переключателя анализа POST сообщения, по умолчанию включено**

* Когда установлено на `true`, автоматически анализирует тело запроса с `Content-Type` в `x-www-form-urlencoded` и преобразует его в массив `POST`.
* Когда установлено на `false`, отключает анализ `POST`.

```php
$server->set([
    'http_parse_post' => false,
]);
```


### http_parse_files

?> **Конфигурация для объекта `Swoole\Http\Request`, установка переключателя анализа上传的文件. По умолчанию включено**

```php
$server->set([
    'http_parse_files' => false,
]);
```


### http_compression

?> **Конфигурация для объекта `Swoole\Http\Response`, включение сжатия. По умолчанию включено.**


!> -`http-chunk` не поддерживает отдельное сжатие segmenов, если использовать метод [write](/http_server?id=write), то сжатие будет принудительно отключено.  
-`http_compression` доступна в `v4.1.0` или более поздних версиях

```php
$server->set([
    'http_compression' => false,
]);
```

В настоящее время поддерживаются три типа сжатия: `gzip`, `br`, `deflate`. Под层 автоматически выбирает метод сжатия на основе заголовка `Accept-Encoding`, передаваемого клиентом браузера (приоритет алгоритмов сжатия: `br` > `gzip` > `deflate`).

**Зависимости:**

Для `gzip` и `deflate` необходимо установить библиотеку `zlib`, и при сборке `Swoole` под低级 будет проверяться наличие `zlib` в системе.

Используйте `yum` или `apt-get` для установки библиотеки `zlib`:

```shell
sudo apt-get install libz-dev
```

Для сжатия `br`格式的依赖于 `google` библиотека `brotli`, способ установки смотрите `install brotli on linux`, при сборке `Swoole` под低级 будет проверяться наличие `brotli` в системе.


### http_compression_level / compression_level / http_gzip_level

?> **Уровень сжатия, конфигурация для объекта `Swoole\Http\Response`**
  
!> `$level` Уровень сжатия, диапазон от `1` до `9`, чем выше уровень, тем меньше размер сжатого файла, но больше потребления `CPU`. По умолчанию `1`, максимальное значение `9`



### http_compression_min_length / compression_min_length

?> **Установка минимального размера файла для включения сжатия, конфигурация для объекта `Swoole\Http\Response`, сжатие включается только если размер файла превышает этот параметр. По умолчанию 20 байтов.**

!> Версия Swoole >= `v4.6.3` доступна

```php
$server->set([
    'compression_min_length' => 128,
]);
```


### upload_tmp_dir

?> **Установка временной директории для загрузки файлов. Максимальная длина директории не должна превышать `220` байтов**

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```


### upload_max_filesize

?> **Установка максимальной размера загружаемого файла**

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```


### enable_static_handler

Включение функции обработки статических файлов, необходимо использовать в сочетании с `document_root`. По умолчанию `false`



### http_autoindex

Включение функции `http autoindex`. По умолчанию не включено


### http_index_files

Используется в сочетании с `http_autoindex`, указывает список файлов, которые должны быть включены в индекс

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```


### http_compression_types / compression_types

?> **Установка типов ответов, которые следует сжатить, конфигурация для объекта `Swoole\Http\Response`**

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Версия Swoole >= `v4.8.12` доступна



### static_handler_locations

?> **Установка путей для статических обработчиков. Тип - массив, по умолчанию не включено.**

!> Версия Swoole >= `v4.4.0` доступна

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* Подобно `location` команде `Nginx`, можно указать один или несколько путей как статические пути. Статические файлы будут обрабатываться только если `URL` соответствует указанным путям, в противном случае они будут рассматриваться как динамические запросы.
* `location` элемент должен начинаться с `/`
* Поддерживается мног多层路径, например `/app/images`
* После включения `static_handler_locations`, если соответствующий файл не существует, будет возвращаться ошибка `404`


### open_http2_protocol

?> **Включение анализа протокола `HTTP2`**【По умолчанию: `false`】

!> Для включения необходимо использовать опцию [--enable-http2](/environment?id=compile_options) при сборке, начиная с `Swoole5`, по умолчанию включено http2.


### document_root

?> **Конфигурация корневой директории для статических файлов, используется в сочетании с `enable_static_handler`.** 

!> Эта функция довольно простая, пожалуйста, не используйте ее в общественных сетях напрямую

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // В версиях ниже v4.4.0, здесь должен быть абсолютный путь
    'enable_static_handler' => true,
]);
```

* После установки `document_root` и включения `enable_static_handler` на `true`, при получении HTTP запроса под低级 сначала проверяется наличие файла в пути `document_root`, и если файл существует, его содержание напрямую отправляется клиенту, и не вызывается callback [onRequest](/http_server?id=on).
* При использовании функции обработки статических файлов, динамический PHP код и статические файлы должны быть изолированы, статические файлы должны храниться в определенных директориях


### max_concurrency

?> **Можно ограничить максимальное количество одновременных запросов для `HTTP1/2` сервиса, после превышения возвращается ошибка `503`, по умолчанию 4294967295, что является максимальным значением беззнакового целого.**

```php
$server->set([
    'max_concurrency' => 1000,
]);
```


### worker_max_concurrency

?> **После включения функции "односторонней координации" `worker` процессы будут непрерывно принимать запросы, чтобы избежать чрезмерного нагрузки, мы можем установить `worker_max_concurrency`, чтобы ограничить количество выполняемых запросами `worker` процессов. Когда количество запросов превышает это значение, `worker` процессы будут временно хранить лишние запросы в очереди, по умолчанию 4294967295, что является максимальным значением беззнакового целого. Если `worker_max_concurrency` не установлен, но установлена `max_concurrency`, то под低级 автоматически установит `worker_max_concurrency` равным `max_concurrency`**

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Версия Swoole >= `v5.0.0` доступна
### http2_header_table_size

?> Определяет максимальный размер таблицы заголовков для сетевых соединений HTTP/2.

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```


### http2_enable_push

?> Эта настройка используется для включения или выключения HTTP2-пуша.

```php
$server->set([
  'http2_enable_push' => 0x2
])
```


### http2_max_concurrent_streams

?> Установляет максимальное количество одновременно принимаемых потоков в каждой сетевой соединении HTTP/2.

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```


### http2_init_window_size

?> Установляет начальный размер окна управления трафиком для HTTP/2.

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```


### http2_max_frame_size

?> Установляет максимальный размер тела одного фрейма HTTP/2, отправляемого через сетевое соединение HTTP/2.

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```

### http2_max_header_list_size

?> Установляет максимальный размер списка заголовков, которые можно отправить в запросе на потоке HTTP/2. 

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
