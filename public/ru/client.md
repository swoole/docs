# Swoole\Client

`Swoole\Client`, в просторечии именуемый `Client`, предоставляет обернутый код для клиентов `TCP/UDP/UnixSocket`. Для использования достаточно создать новый объект `Swoole\Client`. Он подходит для среды `FPM/Apache`. По сравнению с традиционными функциями серии [streams](https://www.php.net/streams), он имеет несколько преимуществ:

  * Функция `stream` имеет默认ное время ожидания, которое может привести к длительному блокированию при слишком долгом отклике от другой стороны
  * Функция `stream` имеет默认ный размер буфера для чтения в `8192` байтов, что не позволяет поддерживать большие пакеты `UDP`
  * `Client` поддерживает `waitall`, что позволяет получить все пакеты сразу, когда длина пакета известна, без необходимости циклического чтения
  * `Client` поддерживает `UDP Connect`, решая проблему с пакетами `UDP`
  * `Client` написан на чистом `C`, специализирован на обработке `sockets`, в то время как функции `stream` очень сложны. У `Client` лучший Performance
  * Можно использовать функцию [swoole_client_select](/client?id=swoole_client_select) для реализации управления параллельными запросами к нескольким `Client`


### Полный пример

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


## Методы


### __construct()

Конструктор

```php
Swoole\Client::__construct(int $sock_type, bool $is_sync = false, string $key);
```

* **Параметры** 

  * **`int $sock_type`**
    * **Функция**: Определяет тип `socket`【поддерживаются `SWOOLE_SOCK_TCP`, `SWOOLE_SOCK_TCP6`, `SWOOLE_SOCK_UDP`, `SWOOLE_SOCK_UDP6`】Подробная информация смотрите в этой главе [/server/methods?id=__construct]
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`bool $is_sync`**
    * **Функция**: Синхронный блокирующий режим, может быть установлен только как `false`. Для использования асинхронного обратного вызова используйте `Swoole\Async\Client`
    * **По умолчанию**: `false`
    * **Другие значения**: Нет

  * **`string $key`**
    * **Функция**: Используется для ключа в долгосрочных соединениях【по умолчанию используется `IP:PORT` в качестве ключа. Такие же ключи, даже если создать их дважды, будут использовать только один TCP-соединение】
    * **По умолчанию**: `IP:PORT`
    * **Другие значения**: Нет

!> Можно использовать нижестоящие макрос для указания типа, смотрите [Константы](/consts)

#### Создание долгосрочного соединения в PHP-FPM/Apache

```php
$cli = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
```

После добавления флага [SWOOLE_KEEP](/client?id=swoole_keep), созданные `TCP` соединения не будут закрыты после окончания PHP-запроса или при вызове `$cli->close()`. В следующем вызове `connect` будет использоваться предыдущее соединение. Способы сохранения долгосрочных соединений по умолчанию основаны на `ServerHost:ServerPort` в качестве ключа. Ключ может быть указан во втором параметре.

Объект `Client` автоматически закрывает `socket`, используя метод [close](/client?id=close) при своем уничтожении

#### Использование Client в Server

  * `Client` должен использоваться в callback-функциях событий [回调函数](/server/events).
  * `Server` может быть соединен с `socket client`, написанным любым языком. Точно так же `Client` может подключаться к `socket server`, написанному любым языком

!> Использование этого `Client` в среде协程 Swoole4+ приведет к возврату к [синхронной модели](/learn?id=同步io异步io).


### set()

Установка параметров клиента, должна быть выполнена перед [connect](/client?id=connect).

```php
Swoole\Client->set(array $settings);
```

Список доступных настроек смотрите в Client - [Конфигурационные опции](/client?id=配置)


### connect()

Соединение с удаленным сервером.

```php
Swoole\Client->connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
```

* **Параметры** 

  * **`string $host`**
    * **Функция**: Адрес сервера【поддерживается автоматическое асинхронное разрешение домена, `$host` может быть传入 как домен】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $port`**
    * **Функция**: Порт сервера
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`float $timeout`**
    * **Функция**: Установление времени ожидания
    * **Единица измерения**: секунды【поддерживается дробное значение, например, `1.5` означает `1s`+`500ms`】
    * **По умолчанию**: `0.5`
    * **Другие значения**: Нет

  * **`int $sock_flag`**
    - В случае использования `UDP` это указывает на включение `udp_connect`. После установки этого флага `$host` и `$port` будут привязаны, и этот `UDP` будет игнорировать пакеты, не принадлежащие указанному `host/port`.
    - В случае использования `TCP`, `$sock_flag=1` указывает на установку неблокирующего `socket`, после чего этот файл descriptor станет [асинхронным IO](/learn?id=同步io异步io), и `connect` немедленно вернет результат. Если `$sock_flag` установлен как `1`, то перед `send/recv` необходимо использовать [swoole_client_select](/client?id=swoole_client_select) для проверки завершения соединения.

* **Возвращаемое значение**

  * Успешное возвращение `true`
  * Неудача возвращает `false`, пожалуйста, проверьте свойство `errCode` для получения причины неудачи

* **Синхронный режим**

Метод `connect` будет блокировать до успешного соединения и возврата `true`. Теперь можно отправлять данные на сервер или принимать данные.

```php
if ($cli->connect('127.0.0.1', 9501)) {
      $cli->send("data");
} else {
      echo "connect failed.";
}
```

Если соединение потерпело неудачу, то вернется `false`

> Синхронный `TCP` клиент после выполнения `close` может снова вызвать `Connect`, чтобы создать новое соединение с сервером

* **Пере reconnection после неудачи**

Если после неудачи `connect` вы хотите попробовать снова соединиться, вам сначала необходимо закрыть старый `socket` с помощью `close`, иначе будет возвращаться ошибка `EINPROCESS`, поскольку текущий `socket` находится в процессе подключения к серверу, и клиент не знает, успешно ли соединение было установлено, поэтому он не может снова вызвать `connect`. вызовы `close` закрывают текущий `socket`, а нижестоящий уровень создает новый `socket` для подключения.

!> После включения долгосрочного соединения с использованием [SWOOLE_KEEP](/client?id=swoole_keep), первый параметр вызова `close` должен быть установлен как `true`, чтобы принудительно уничтожить долгосрочный `socket`

```php
if ($socket->connect('127.0.0.1', 9502) === false) {
    $socket->close(true);
    $socket->connect('127.0.0.1', 9502);
}
```

* **UDP Connect**

По умолчанию, нижестоящий уровень не включает `udp connect`. Когда `UDP` клиент вызывает `connect`, после создания `socket` нижестоящий уровень сразу же возвращает успех. В это время привязанный адрес этого `socket` является `0.0.0.0`, и другие машины, такие как `192.168.1.101`, также могут отправлять пакеты на этот порт.

Например, `$client->connect('192.168.1.100', 9502)`, в это время операционная система назначает случайный порт `58232` для этого `socket` клиента, и другие машины, такие как `192.168.1.101`, также могут отправлять пакеты на этот порт.

?> Если `udp connect` не включено, функция `getsockname` вернет `host` как `0.0.0.0`

Установив четвертый параметр как `1`, можно включить `udp connect`, `$client->connect('192.168.1.100', 9502, 1, 1)`. В это время будет установлен绑定 между клиентом и сервером, и нижестоящий уровень привяжет адрес `socket` на основе адреса сервера. Например, если соединение установлено с `192.168.1.100`, текущий `socket` будет привязан к локальному адресу на основе `192.168.1.*`. После включения `udp connect` клиент больше не будет принимать пакеты, отправленные другими машинами на этот порт.
### recv()

Получает данные от сервера.

```php
Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
```

* **Параметры**

  * **`int $size`**
    * **Функция**: Максимальная длина буфера для приема данных【Не следует устанавливать слишком большую значение, иначе это потребует большой объема памяти】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $flags`**
    * **Функция**: Можно устанавливать дополнительные параметры【например, [Client::MSG_WAITALL](/client?id=clientmsg_waitall)】, подробности смотрите в [этой главе](/client?id=константы)
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Если успешно получены данные, возвращается строка
  * Если соединение закрыто, возвращается пустая строка
  * Если失败, возвращается `false`, и устанавливается свойство `$client->errCode`

* **Протокол EOF/Длинна**

  * После того как клиент активировал обнаружение EOF/Длинны, не нужно устанавливать параметры `$size` и `$waitall`. Расширение вернет полный пакет данных или вернет `false`, подробности смотрите в главе [анализ протокола](/client?id=анализ_протокола).
  * Когда получен неправильный заголовок пакета или длина в заголовке превышает значение [package_max_length](/server/setting?id=package_max_length), `recv` вернет пустую строку, и в PHP-коде следует закрыть это соединение.


### send()

Отправляет данные на удаленный сервер, можно отправлять данные только после установления соединения.

```php
Swoole\Client->send(string $data): int|false
```

* **Параметры**

  * **`string $data`**
    * **Функция**: Отправляемый контент【Поддерживается бинарная данные】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Если отправка успешно, возвращается длина отправленных данных
  * Если失败, возвращается `false`, и устанавливается свойство `errCode`

* **Примечание**

  * Если `connect` не вызван, вызов `send` вызовет предупреждение
  * Не существует ограничения на длину отправляемых данных
  * Если отправляемые данные слишком большие и буфер сокета переполняется, программа будет заблокирована в ожидании возможности писать


### sendfile()

Отправляет файл на сервер, эта функция реализована на основе операционной системы `sendfile`

```php
Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

!> `sendfile` не может использоваться для клиентов UDP и для SSL туннельных соединений с шифрованием

* **Параметры**

  * **`string $filename`**
    * **Функция**: Указывает путь к отправляемому файлу
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $offset`**
    * **Функция**: Отсчет от начала отправляемого файла【Можно указать начало передачи данных из середины файла. Эта особенность может использоваться для поддержки продолжения передачи после прерывания】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $length`**
    * **Функция**: Размер отправляемых данных【По умолчанию - размер всего файла】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

* **Возвращаемое значение**

  * Если указанный файл не существует, будет возвращаться `false`
  * Если выполнение успешно, будет возвращаться `true`

* **Примечание**

  * `sendfile` будет блокироваться до тех пор, пока весь файл не будет отправлен или произойдет критическая ошибка



### sendto()

Отправляет `UDP` пакет на любой `IP:PORT` хоста, поддерживается только тип `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6`

```php
Swoole\Client->sendto(string $ip, int $port, string $data): bool
```

* **Параметры**

  * **`string $ip`**
    * **Функция**: IP-адрес целевого хоста, поддерживает `IPv4/IPv6`
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`int $port`**
    * **Функция**: Порт целевого хоста
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

  * **`string $data`**
    * **Функция**: Содержание отправляемых данных【Не более `64K`】
    * **По умолчанию**: Нет
    * **Другие значения**: Нет


### enableSSL()

Динамически активирует SSL туннельное шифрование, функция может быть использована только если при сборке `swoole` была включена опция `--enable-openssl`.

```php
Swoole\Client->enableSSL(): bool
```

Если клиент создает соединение в明文овом режиме и затем хочет изменить его на SSL туннельное шифрование, можно использовать метод `enableSSL`. Если соединение从一开始 было SSL, смотрите [SSL конфигурация](/client?id=ssl_related). Для динамического включения SSL туннельного шифрования необходимо выполнить два условия:

  * Тип клиента при создании не должен быть SSL
  * Клиент уже установил соединение с сервером

Вызов `enableSSL` будет блокировать ожидание завершения SSL рукопожатия.

* **Пример**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
// Активация SSL туннального шифрования
if ($client->enableSSL())
{
    // Рукопожатие завершено, теперь отправленные и полученные данные шифрованы
    $client->send("hello world\n");
    echo $client->recv();
}
$client->close();
```



### getPeerCert()

Получает информацию о сертификате сервера, функция может быть использована только если при сборке `swoole` была включена опция `--enable-openssl`.

```php
Swoole\Client->getPeerCert(): string|false
```

* **Возвращаемое значение**

  * Если успешно, возвращается информация о сертификате в виде строки `X509`
  * Если失败, возвращается `false`

!> Метод может быть вызван только после завершения SSL рукопожатия.
  
Используйте функцию `openssl_x509_parse` из расширением `openssl` для анализа информации о сертификате.

!> При сборке swoole необходимо включить [--enable-openssl](/environment?id=сборка_опций)


### verifyPeerCert()

Проверяет сертификат сервера, функция может быть использована только если при сборке `swoole` была включена опция `--enable-openssl`.

```php
Swoole\Client->verifyPeerCert()
```


### isConnected()

Возвращает состояние соединения клиента

* Возвращает `false`, если сейчас не подключено к серверу
* Возвращает `true`, если сейчас подключено к серверу

```php
Swoole\Client->isConnected(): bool
```

!> Метод `isConnected` возвращает состояние на уровне приложения, он только показывает, что `Client` выполнил `connect` и успешно подключился к `Server`, и не выполнил `close` для закрытия соединения. `Client` может выполнять операции `send`, `recv`, `close` и т.д., но не может снова выполнять `connect`.  
Это не означает, что соединение обязательно可用, когда выполняются операции `send` или `recv`, возможно возвращение ошибки, потому что приложение не может получить состояние нижестоящего `TCP` соединения, и для выполнения `send` или `recv` приложение взаимодействует с ядром, чтобы получить истинное состояние доступности соединения.


### getSockName()

Используется для получения локального хоста:порта сокета клиента.

!> Необходимо использовать после подключения

```php
Swoole\Client->getsockname(): array|false
```

* **Возвращаемое значение**

```php
array('host' => '127.0.0.1', 'port' => 53652);
```


### getPeerName()

Получает IP-адрес и порт противоположного сокета

!> Поддерживается только тип `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM`

```php
Swoole\Client->getpeername(): array|false
```

После отправки пакета данных на сервер с использованием протокола `UDP`, ответ может быть отправлен не от этого сервера на клиент. Используйте метод `getpeername` для получения фактического IP:порта сервера, который ответил.

!> Этот метод должен быть вызван после `$client->recv()`

###Закрыть соединение

```php
Swoole\Client->close(bool $force = false): bool
```

* **Параметры**

  * **`bool $force`**
    * **Функция**: forcibly close the connection (can be used to close [SWOOLE_KEEP](/client?id=swoole_keep) long connections)
    * **По умолчанию**: Нет
    * **Другие значения**: Нет

После того как `swoole_client` соединение закрыто с помощью `close`, не следует снова устанавливать `connect`. Правильный подход заключается в уничтожении текущего `Client`, создании нового `Client` и установлении нового соединения.

Объект `Client` автоматически закроет соединение при своем уничтожении.


### закрыть клиент

```php
Swoole\Client->shutdown(int $how): bool
```

* **Параметры**

  * **`int $how`**
    * **Функция**: устанавливать, как закрыть клиент
    * **По умолчанию**: Нет
    * **Другие значения**: Swoole\Client::SHUT_RDWR (закрыть读写), SHUT_RD (закрыть чтение), Swoole\Client::SHUT_WR (закрыть запись)


### получить сокет

Получить под底层 `socket` управление, возвращаемый объект является ресурсом сокета.

!> Эта функция требует зависимости от расширения `sockets` и при сборке необходимо включить опцию [--enable-sockets](/environment?id=compile options)

```php
Swoole\Client->getSocket()
```

Используйте функцию `socket_set_option` для установки более низкого уровня некоторых параметров `socket`.

```php
$sock = $client->getSocket();
if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Не удалось установить опцию на сокете: '. socket_strerror(socket_last_error()) . PHP_EOL;
}
```


### swoole_client_select

В параллельном обработке Swoole\Client используется системный вызов select для реализации [IO event loop](/learn?id=什么是eventloop), а не epoll_wait. В отличие от [Event модуль](/event), эта функция используется в синхронном IO окружении (если она будет вызвана в процессе рабочего worker Swoole, это приведет к тому, что собственный epoll Swoole [IO event loop](/learn?id=什么是eventloop) не получит возможности выполнить).

Функция прототип:

```php
int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);
```

* `swoole_client_select` принимает 4 параметра: `$read`, `$write`, `$error` - этоそれぞれ файловие дескрипторы для чтения/записи/ошибок.  
* Эти 3 параметра должны быть ссылками на массивы. Элементы этих массивов должны быть объектами `swoole_client`.
* Эта функция основана на системе вызовов select, максимально поддерживает 1024 сокетов
* Параметр `$timeout` - это время ожидания для системного вызова select в секундах, принимает числовые числа с плавающей точкой
* Функциональность идентична原生ой функции PHP `stream_select()`, отличие в том, что stream_select поддерживает только тип streams PHP и имеет плохую производительность.

После успешного вызова, будет возвращен номер событий и будут изменены массивы `$read`/`$write`/`$error`. Используйте foreach для traversal массива, затем выполните `$item->recv`/`$item->send` для приема и передачи данных. Или вызвайте `$item->close()` или `unset($item)` чтобы закрыть `socket`.

`swoole_client_select` возвращает `0`, что означает, что в течение установленного времени не было никаких IO доступных, вызов select истек.

!> Эта функция может использоваться в окружении `Apache/PHP-FPM`    

```php
$clients = array();

for($i=0; $i< 20; $i++)
{
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); // синхронный блокирующий
    $ret = $client->connect('127.0.0.1', 9501, 0.5, 0);
    if(!$ret)
    {
        echo "Не удалось подключиться к серверу.errCode=".$client->errCode;
    }
    else
    {
    	$client->send("HELLO WORLD\n");
    	$clients[$client->sock] = $client;
    }
}

while (!empty($clients))
{
    $write = $error = array();
    $read = array_values($clients);
    $n = swoole_client_select($read, $write, $error, 0.6);
    if ($n > 0)
    {
        foreach ($read as $index => $c)
        {
            echo "Получено #{$c->sock}: " . $c->recv() . "\n";
            unset($clients[$c->sock]);
        }
    }
}
```


## Свойства


### errCode

Ошибочный код

```php
Swoole\Client->errCode: int
```

Когда `connect/send/recv/close` терпят неудачу, автоматически устанавливается значение `$swoole_client->errCode`.

Исходное значение `$errCode` равно `Linux errno`. Используйте `socket_strerror` для преобразования ошибочного кода в информацию об ошибке.

```php
echo socket_strerror($client->errCode);
```

Для справки можно посмотреть на [Список linux ошибок](/other/errno?id=linux)


### sock

Фiles descriptor соединения socket.

```php
Swoole\Client->sock;
```

В PHP-коде можно использовать

```php
$sock = fopen("php://fd/".$swoole_client->sock); 
```

* Convert `Swoole\Client` socket to a `stream socket`. Functions like `fread/fwrite/fclose` can be used for process operations.
* [Swoole\Server](/server/methods?id=__construct) `$fd` не может быть преобразован с помощью этой методы, потому что `$fd` - это просто число, и `$fd` файлы descriptor принадлежит главному процессу, см. [SWOOLE_PROCESS](/learn?id=swoole_process) режим.
* `$swoole_client->sock` можно преобразовать в int и использовать как ключ массива.

!> Следует отметить: значение свойства `$swoole_client->sock`, можно получить только после того, как `$swoole_client->connect` будет вызван. До подключения к серверу это свойство равно `null`.


### reuse

Indicates whether this connection is newly created or reused from an existing one. Used in conjunction with [SWOOLE_KEEP](/client?id=swoole_keep).

#### Use Cases

After a `WebSocket` client establishes a connection with the server, it needs to perform a handshake. If the connection is reused, then there is no need to perform the handshake again, and you can directly send `WebSocket` data frames.

```php
if ($client->reuse) {
    $client->send($data);
} else {
    $client->doHandShake();
    $client->send($data);
}
```


### reuseCount

Indicates how many times this connection has been reused. Used in conjunction with [SWOOLE_KEEP](/client?id=swoole_keep).

```php
Swoole\Client->reuseCount;
```


### type

Indicates the type of `socket`, which returns the value of `$sock_type` from `Swoole\Client::__construct()`.

```php
Swoole\Client->type;
```


### id

Returns the value of `$key` from `Swoole\Client::__construct()`, used in conjunction with [SWOOLE_KEEP](/client?id=swoole_keep).

```php
Swoole\Client->id;
```


### setting

Returns the configurations set by `Swoole\Client::set()`.

```php
Swoole\Client->setting;
```


## Константы


### SWOOLE_KEEP

Swoole\Client поддерживает создание долгосрочного TCP-соединения с сервером в `PHP-FPM/Apache`. Как использовать:

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
```

После включения опции `SWOOLE_KEEP`, сокет не закроется после завершения одного запроса и будет автоматически повторно использоваться в следующем `connect`. Если при `connect` пытаются установить соединение, которое уже было закрыто сервером, то будет создан новый соединение.

?> преимущества SWOOLE_KEEP

* `TCP` долгосрочное соединение может уменьшить дополнительный IO из-за трехстороннего рукопожатия `connect` и четырехстороннего прощания `close`
* Уменьшает количество `close` и `connect` операций на стороне сервера
### Swoole\Client::MSG_DONTWAIT

Неблокирующий прием данных, возвращает немедленно независимо от наличия данных.

### Swoole\Client::MSG_PEEK

Прокрутка данных в буфере сокета. После установки параметра `MSG_PEEK`, метод `recv` читает данные без изменения указателя, поэтому следующий вызов `recv` все равно будет возвращать данные, начиная с последнего положения.

### Swoole\Client::MSG_OOB

Чтение данных за пределами обрыва, пожалуйста, ищите информацию о "`TCP данных за пределами обрыва`".

### Swoole\Client::SHUT_RDWR

Завершение чтения и письма на клиентском сокете.

### Swoole\Client::SHUT_RD

Завершение чтения на клиентском сокете.

### Swoole\Client::SHUT_WR

Завершение письма на клиентском сокете.

## Конфигурация

Клиент может использовать метод `set` для настройки некоторых параметров и включения определенных функций.

### декодирование протокола

?> Декодирование протокола используется для решения проблемы границы TCP-пакетов (/learn?id=tcp%E8%AF%B7%E6%B1%82), значение соответствующих настроек аналогично `Swoole\Server`, подробности смотрите в разделе настройки [Swoole\Server протокола](/server/setting?id=open_eof_check).

* **Проверка конца пакета**

```php
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n\r\n",
    'package_max_length' => 1024 * 1024 * 2,
));
```

* **Проверка длины**

```php
$client->set(array(
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0, //N-й байт содержит длину пакета
    'package_body_offset' => 4, //С какого байта начинается расчет длины
    'package_max_length' => 2000000, //Максимальная длина протокола
));
```

!> В настоящее время поддерживаются автоматические функции обработки протокола [open_length_check](/server/setting?id=open_length_check) и [open_eof_check](/server/setting?id=open_eof_check);  
После настройки декодирования протокола метод `recv()` клиента больше не будет принимать параметр длины, и каждый раз он обязательно вернет полный пакет данных.

* **Протокол MQTT**

!> Активация декодирования протокола MQTT, callback [onReceive](/server/events?id=onreceive) будет получать полный пакет данных MQTT.

```php
$client->set(array(
    'open_mqtt_protocol' => true,
));
```

* **Размер буфера сокета**	

!> Включает в себя буфер операционной системы на уровне сокета, буфер для приема данных на уровне приложения и буфер для отправки данных на уровне приложения.	

```php	
$client->set(array(	
    'socket_buffer_size' => 1024 * 1024 * 2, // 2M буфера	
));	
```

* **Отключение алгоритма слияния Nagle**

```php
$client->set(array(
    'open_tcp_nodelay' => true,
));
```


### SSL-соответствующее

* **Конфигурация сертификатов SSL/TLS**

```php
$client->set(array(
    'ssl_cert_file' => $your_ssl_cert_file_path,
    'ssl_key_file' => $your_ssl_key_file_path,
));
```

* **ssl_verify_peer**

Проверка сертификата сервера.

```php
$client->set([
    'ssl_verify_peer' => true,
]);
```

Включение проверяет, соответствует ли сертификат и имя хоста, и если нет, автоматически закрывает соединение.

* **Самодействующие сертификаты**

Можно установить `ssl_allow_self_signed` в `true`, чтобы разрешить использование самодействующих сертификатов.

```php
$client->set([
    'ssl_verify_peer' => true,
    'ssl_allow_self_signed' => true,
]);
```

* **ssl_host_name**

Установка имени хоста сервера, используется совместно с конфигурацией `ssl_verify_peer` или [Client::verifyPeerCert](/client?id=verifypeercert).

```php
$client->set([
    'ssl_host_name' => 'www.google.com',
]);
```

* **ssl_cafile**

Когда `ssl_verify_peer` установлен в `true`, используется для проверки сертификатов CA, используемых для подтвержденияRemote certificates. Значение этого параметра - полный путь и имя файла CA-сертификата на местном файловом системе.

```php
$client->set([
    'ssl_cafile' => '/etc/CA',
]);
```

* **ssl_capath**

Если `ssl_cafile` не установлен или указанный файл отсутствует, поиск подходящих сертификатов будет проводиться в указанном каталоге `ssl_capath`.目录 должен быть уже обработанным хешированием.

```php
$client->set([
    'ssl_capath' => '/etc/capath/',
])
```

* **ssl_passphrase**

Пароль для местного сертификата [ssl_cert_file](/server/setting?id=ssl_cert_file).

* **Пример**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_SSL);

$client->set(array(
    'ssl_cert_file' => __DIR__.'/ca/client-cert.pem',
    'ssl_key_file' => __DIR__.'/ca/client-key.pem',
    'ssl_allow_self_signed' => true,
    'ssl_verify_peer' => true,
    'ssl_cafile' => __DIR__.'/ca/ca-cert.pem',
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
echo "connect ok\n";
$client->send("hello world-" . str_repeat('A', $i) . "\n");
echo $client->recv();
```


### package_length_func

Установка функции расчета длины, 使用方法 полностью идентично [package_length_func](/server/setting?id=package_length_func) `Swoole\Server`. Используется совместно с [open_length_check](/server/setting?id=open_length_check). Функция длины должна возвращать целое число.

* Возвращение `0` означает, что данные недостаточны, необходимо принять больше данных
* Возвращение `-1` означает, что данные ошибочны, низший уровень автоматически закроет соединение
* Возвращение общей длины пакета (включая заголовок и тело пакета), низший уровень автоматически склеит пакет и вернет его обратному фрейму

По умолчанию низший уровень максимально читает `8K` данных, и если длина заголовка меньше, это может привести к потреблению памяти из-за копирования. Можно установить параметр `package_body_offset`, чтобы низший уровень читал только заголовок для анализа длины.

* **Пример**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check' => true,
    'package_length_func' => function ($data) {
        if (strlen($data) < 8) {
            return 0;
        }
        $length = intval(trim(substr($data, 0, 8)));
        if ($length <= 0) {
            return -1;
        }
        return $length + 8;
    },
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


### socks5_proxy

Конфигурация SOCKS5 прокси.

!> Установление только одного параметра бесполезно, необходимо установить одновременно `host` и `port`; `socks5_username`, `socks5_password` являются необязательными параметрами. `socks5_port`, `socks5_password` не могут быть `null`.

```php
$client->set(array(
    'socks5_host' => '192.168.1.100',
    'socks5_port' => 1080,
    'socks5_username' => 'username',
    'socks5_password' => 'password',
));
```


### http_proxy

Конфигурация HTTP прокси.

!> `http_proxy_port`, `http_proxy_password` не могут быть `null`.

* **Базовая настройка**

```php
$client->set(array(
    'http_proxy_host' => '192.168.1.100',
    'http_proxy_port' => 1080,
));
```

* **Проверочная настройка**

```php
$client->set(array(
    'http_proxy_user' => 'test',
    'http_proxy_password' => 'test_123456',
));
```


### bind

!> Установление только `bind_port` бесполезно, пожалуйста, установите одновременно `bind_port` и `bind_address`

?> В случае наличия нескольких сетевых адаптеров на машине, установка параметра `bind_address` может заставить клиентский `Socket` привязываться к определенному сетевому адресу.  
Установка `bind_port` позволяет клиентскому `Socket` использовать фиксированный порт для подключения к внешнему серверу.

```php
$client->set(array(
    'bind_address' => '192.168.1.100',
    'bind_port' => 36002,
));
```
### Сферы применения

Вышеуказанные настройки `Client` применимы также следующим клиентам:

  * [Swoole\Coroutine\Client](/coroutine_client/client)
  * [Swoole\Coroutine\Http\Client](/coroutine_client/http_client)
  * [Swoole\Coroutine\Http2\Client](/coroutine_client/http2_client)
