# Клиент FastCGI с协程ами

PHP-FPM использует эффективный бинарный протокол: `Protocols FastCGI` для коммуникации, и через клиент FastCGI можно напрямую взаимодействовать с сервисом PHP-FPM без необходимости через какой-либо HTTP обратный прокси.

[Каталог исходного кода PHP](https://github.com/swoole/library/blob/master/src/core/Coroutine/FastCGI)

## Простой пример использования

[Более подробные примеры кода](https://github.com/swoole/library/tree/master/examples/fastcgi)

!> Следующий пример кода должен быть вызван в контексте协程

### Быстрое использование

```php
#greeter.php
echo 'Привет, ' . ($_POST['кто'] ?? 'Мир');
```

```php
echo \Swoole\Coroutine\FastCGI\Client::call(
    '127.0.0.1:9000', // Адрес, на котором слушает FPM, также может быть адрес Unix-сocket, такой как unix:/tmp/php-cgi.sock
    '/tmp/greeter.php', // Файл входа, который хочется выполнить
    ['кто' => 'Swoole'] //Additional POST-данные
);
```

### В соответствии со стандартом PSR

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1:9000', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withScriptFilename(__DIR__ . '/greeter.php')
        ->withMethod('POST')
        ->withBody(['кто' => 'Swoole']);
    $response = $client->execute($request);
    echo "Результат: {$response->getBody()}\n";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Ошибка: {$exception->getMessage()}\n";
}
```

### Сложное использование

```php
#var.php
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
```

```php
try {
    $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1', 9000);
    $request = (new \Swoole\FastCGI\HttpRequest())
        ->withDocumentRoot(__DIR__)
        ->withScriptFilename(__DIR__ . '/var.php')
        ->withScriptName('var.php')
        ->withMethod('POST')
        ->withUri('/var?foo=bar&bar=char')
        ->withHeader('X-Foo', 'bar')
        ->withHeader('X-Bar', 'char')
        ->withBody(['foo' => 'bar', 'bar' => 'char']);
    $response = $client->execute($request);
    echo "Результат: \n{$response->getBody()}";
} catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
    echo "Ошибка: {$exception->getMessage()}\n";
}
```

### Один клик для прокси WordPress

!> Этот способ использования не имеет производственного смысла, в производстве proxy может использоваться для прокси части старых API-интерфейсов HTTP-запросов на старый FPM-сервис (а не для прокси всей станции)

```php
use Swoole\Constant;
use Swoole\Coroutine\FastCGI\Proxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$documentRoot = '/var/www/html'; # Корневая директория проекта WordPress
$server = new Server('0.0.0.0', 80, SWOOLE_BASE); # Здесь порт должен соответствовать конфигурации WordPress, обычно не указывается явно, это 80
$server->set([
    Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
    Constant::OPTION_HTTP_PARSE_COOKIE => false,
    Constant::OPTION_HTTP_PARSE_POST => false,
    Constant::OPTION_DOCUMENT_ROOT => $documentRoot,
    Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/wp-admin', '/wp-content', '/wp-includes'], # Путь к статическим ресурсам
]);
$proxy = new Proxy('127.0.0.1:9000', $documentRoot); # Создание объекта proxy
$server->on('request', function (Request $request, Response $response) use ($proxy) {
    $proxy->pass($request, $response); # Один клик для прокси запросов
});
$server->start();
```

## Методы


### call

Статический метод, создает новый клиентский соединение, отправляет запрос на сервер FPM и получает тело ответа

!> FPM поддерживает только короткие соединения, поэтому обычно нет большого смысла создавать постоянные объекты

```php
Swoole\Coroutine\FastCGI\Client::call(string $url, string $path, $data = '', float $timeout = -1): string
```

  * **Параметры** 

    * **`string $url`**
      * **Функция**: Адрес, на котором слушает FPM (например, `127.0.0.1:9000`, `unix:/tmp/php-cgi.sock` и т.д.)
      * **Значение по умолчанию**: Нет
      * **Другие значения**: Нет

    * **`string $path`**
      * **Функция**: Файл входа, который хочется выполнить
      * **Значение по умолчанию**: Нет
      * **Другие значения**: Нет

    * **`$data`**
      * **Функция**:Additional данные запроса
      * **Значение по умолчанию**: Нет
      * **Другие значения**: Нет

    * **`float $timeout`**
      * **Функция**: Установить время ожидания【 по умолчанию -1 означает никогда не истекать】
      * **Единица измерения значений**: секунды【Поддерживается дробное значение, например, 1.5 означает 1 секунда + 500 мс】
      * **Значение по умолчанию**: `-1`
      * **Другие значения**: Нет

  * **Возвращаемое значение** 

    * Возвращает тело ответа сервера (body)
    * В случае ошибки будет выброшена исключение `Swoole\Coroutine\FastCGI\Client\Exception`


### __construct

Конструктор объекта клиента, указывает целевой сервер FPM

```php
Swoole\Coroutine\FastCGI\Client::__construct(string $host, int $port = 0)
```

  * **Параметры** 

    * **`string $host`**
      * **Функция**: Адрес целевого сервера【например, `127.0.0.1`, `unix://tmp/php-fpm.sock` и т.д.】
      * **Значение по умолчанию**: Нет
      * **Другие значения**: Нет

    * **`int $port`**
      * **Функция**: Порт целевого сервера【Не требуется при использовании UNIX-Socket как адрес】
      * **Значение по умолчанию**: Нет
      * **Другие значения**: Нет


### execute

Выполнить запрос, вернуть ответ

```php
Swoole\Coroutine\FastCGI\Client->execute(Request $request, float $timeout = -1): Response
```

  * **Параметры** 

    * **`Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest $request`**
      * **Функция**: Объект, содержащий информацию о запросе, обычно используется `Swoole\FastCGI\HttpRequest` для имитации HTTP-запроса, в особых случаях используется класс исходного протокола FPM `Swoole\FastCGI\Request`
      * **Значение по умолчанию**: Нет
      * **Другие значения**: Нет

    * **`float $timeout`**
      * **Функция**: Установить время ожидания【 по умолчанию -1 означает никогда не истекать】
      * **Единица измерения значений**: секунды【Поддерживается дробное значение, например, 1.5 означает 1 секунда + 500 мс】
      * **Значение по умолчанию**: `-1`
      * **Другие значения**: Нет

  * **Возвращаемое значение** 

    * Возвращает объект ответа, соответствующего типу объекта запроса, например, если запрос был HTTP, то будет возвращен объект ответа HTTP `Swoole\FastCGI\HttpResponse`, содержащий информацию о ответе от сервера FPM
    * В случае ошибки будет выброшена исключение `Swoole\Coroutine\FastCGI\Client\Exception`

## Связанные классы запросов/ответов

Поскольку библиотека не может ввозить огромные зависимости PSR и загрузка расширений всегда происходит до выполнения PHP-кода, соответствующие классы запросов и ответов не наследуют PSR-интерфейсы, но создаются в стиле PSR в надежде, что разработчики смогут быстро научиться их использовать

Исходный код классов для имитации HTTP-запросов и ответов FastCGI очень прост, код является документацией:

[Swoole\FastCGI\HttpRequest](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpRequest.php)
[Swoole\FastCGI\HttpResponse](https://github.com/swoole/library/blob/master/src/core/FastCGI/HttpResponse.php)
