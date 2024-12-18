# Список функций

Помимо функций, связанных с сетевым коммуникационным общением, Сwoole предоставляет также ряд функций для получения информации о системе, которые могут использоваться в PHP-приложениях.


## swoole_set_process_name()

Используется для установки имени процесса. После изменения имени процесса, команда `ps` будет видеть не `php your_file.php`, а установленную строку.

Эта функция принимает один параметр - строку.

Эта функция аналогична функции [cli_set_process_title](https://www.php.net/manual/zh/function.cli-set-process-title.php), предоставляемой с PHP5.5. Однако `swoole_set_process_name` может использоваться в любой версии PHP, начиная с PHP5.2. compatibilность `swoole_set_process_name` хуже, чем у `cli_set_process_title`, и если существует функция `cli_set_process_title`, то предпочтение отдается ей.

```php
function swoole_set_process_name(string $name): void
```

Пример использования:

```php
swoole_set_process_name("swoole server");
```


### Как переименовать отдельные процессы Сwoole Server <!-- {docsify-ignore} -->

* Изменить имя основного процесса при [onStart](/server/events?id=onstart)
* Изменить имя управляющего процесса (`manager`) при [onManagerStart](/server/events?id=onmanagerstart)
* Изменить имя рабочего процесса при [onWorkerStart](/server/events?id=onworkerstart)
 
!> Низкие версии Linux-ядра и Mac OSX не поддерживают переименование процессов  


## swoole_strerror()

Конвертирует код ошибки в сообщение об ошибке.

Функция принимает два параметра:

- `$errno` - числовой код ошибки
- `$error_type` - тип ошибки (по умолчанию `1`)

Список типов ошибок:

- `1` - стандартный `Unix Errno`, возникающий в результате ошибок системных вызовов, таких как `EAGAIN`, `ETIMEDOUT` и т.д.
- `2` - коды ошибок `getaddrinfo`, возникающие в результате операций с DNS.
- `9` - внутренние коды ошибок Swoole, полученные с помощью функции `swoole_last_error()`.

Пример использования:

```php
var_dump(swoole_strerror(swoole_last_error(), 9));
```


## swoole_version()

Получает номер версии расширения Swoole, например `1.6.10`.

```php
function swoole_version(): string
```

Пример использования:

```php
var_dump(SWOOLE_VERSION); // Глобальная переменная SWOOLE_VERSION также обозначает номер версии расширения Swoole
var_dump(swoole_version());
/**
Возвращаемое значение:
string(6) "1.9.23"
string(6) "1.9.23"
**/
```


## swoole_errno()

Получает последний код ошибки системного вызова, аналогичный переменной `errno` в `C/C++`.

```php
function swoole_errno(): int
```

Значение кода ошибки зависит от операционной системы. Используйте функцию `swoole_strerror` для преобразования ошибки в сообщение об ошибке.


## swoole_get_local_ip()

Эта функция используется для получения IP-адресов всех сетевых интерфейсов локального компьютера.

```php
function swoole_get_local_ip(): array
```

Пример использования:

```php
// Получение IP-адресов всех сетевых интерфейсов локального компьютера
$list = swoole_get_local_ip();
print_r($list);
/**
Возвращаемое значение
Array
(
      [eno1] => 10.10.28.228
      [br-1e72ecd47449] => 172.20.0.1
      [docker0] => 172.17.0.1
)
**/
```

!> Примечание
* В настоящее время возвращаются только IPv4-адреса, и в результате будет пропущен локальный адрес loop 127.0.0.1.
* Результаты представлены в виде ассоциативного массива с названиями интерфейсов в качестве ключей. Например, `array("eth0" => "192.168.1.100")`
* Эта функция использует системный вызов `ioctl` для получения информации о интерфейсе в реальном времени, без внутренней缓存


## swoole_clear_dns_cache()

Очищает встроенный DNS-кэш Swoole, действующий для `swoole_client` и `swoole_async_dns_lookup`.

```php
function swoole_clear_dns_cache()
```


## swoole_get_local_mac()

Получает MAC-адрес сетевого адаптера локального компьютера.

```php
function swoole_get_local_mac(): array
```

* В случае успешного вызова возвращаются MAC-адреса всех сетевых адаптеров

```php
array(4) {
  ["lo"]=>
  string(17) "00:00:00:00:00:00"
  ["eno1"]=>
  string(17) "64:00:6A:65:51:32"
  ["docker0"]=>
  string(17) "02:42:21:9B:12:05"
  ["vboxnet0"]=>
  string(17) "0A:00:27:00:00:00"
}
```


## swoole_cpu_num()

Получает количество ядер CPU на локальном компьютере.

```php
function swoole_cpu_num(): int
```

* В случае успешного вызова возвращается количество ядер CPU, например:

```shell
php -r "echo swoole_cpu_num();"
```


## swoole_last_error()

Получает последний код ошибки на уровне Swoole.

```php
function swoole_last_error(): int
```

Используйте функцию `swoole_strerror(swoole_last_error(), 9)` для преобразования ошибки в сообщение об ошибке, полный список ошибок смотрите в [Списке ошибок Swoole](/other/errno?id=swoole).


## swoole_mime_type_add()

Добавляет новый MIME-тип в встроенную таблицу MIME-типов.

```php
function swoole_mime_type_add(string $suffix, string $mime_type): bool
```


## swoole_mime_type_set()

Изменяет определенный MIME-тип, в случае неудачи (если он не существует) возвращает `false`.

```php
function swoole_mime_type_set(string $suffix, string $mime_type): bool
```


## swoole_mime_type_delete()

Удаляет определенный MIME-тип, в случае неудачи (если он не существует) возвращает `false`.

```php
function swoole_mime_type_delete(string $suffix): bool
```


## swoole_mime_type_get()

Получает MIME-тип файла по его имени.

```php
function swoole_mime_type_get(string $filename): string
```


## swoole_mime_type_exists()

Проверяет наличие MIME-типа по его суффиксу.

```php
function swoole_mime_type_exists(string $suffix): bool
```


## swoole_substr_json_decode()

Цельной переход JSON декодирования, кроме `$offset` и `$length`, остальные параметры совпадают с [json_decode](https://www.php.net/manual/en/function.json-decode.php).

!> Сwoole версия >= `v4.5.6` доступна, начиная с версии `v4.5.7` необходимо добавить опцию [--enable-swoole-json](/environment?id=Общие параметры) при сборке для включения. Для примеров использования смотрите [Swoole 4.5.6 Поддержка целевого перехода JSON или декодирования PHP](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_json_decode(string $packet, int $offset, int $length, bool $assoc = false, int $depth = 512, int $options = 0)
```

  * **Пример**

```php
$val = json_encode(['hello' => 'swoole']);
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(json_decode(substr($str, 4, $l), true));
var_dump(swoole_substr_json_decode($str, 4, $l, true));
```


## swoole_substr_unserialize()

Цельной переход декодирования PHP, кроме `$offset` и `$length`, остальные параметры совпадают с [unserialize](https://www.php.net/manual/en/function.unserialize.php).

!> Сwoole версия >= `v4.5.6` доступна. Для примеров использования смотрите [Swoole 4.5.6 Поддержка целевого перехода JSON или декодирования PHP](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_unserialize(string $packet, int $offset, int $length, array $options= [])
```

  * **Пример**

```php
$val = serialize('hello');
$str = pack('N', strlen($val)) + $val + "\r\n";
$l = strlen($str) - 6;
var_dump(unserialize(substr($str, 4, $l)));
var_dump(swoole_substr_unserialize($str, 4, $l));
```


## swoole_error_log()

Выводит сообщение об ошибке в лог. `$level` представляет уровень лога [Уровень логов](/consts?id=Уровень логов).

!> Сwoole версия >= `v4.5.8` доступна

```php
function swoole_error_log(int $level, string $msg)
```
## swoole_clear_error()

Очищает ошибки сокета или последние ошибка кодов на последнем ошибочном коде.

!> Версия Swoole >= `v4.6.0` доступна

```php
function swoole_clear_error()
```


## swoole_coroutine_socketpair()

Версия функции [socket_create_pair](https://www.php.net/manual/en/function.socket-create-pair.php) для корутин.

!> Версия Swoole >= `v4.6.0` доступна

```php
function swoole_coroutine_socketpair(int $domain , int $type , int $protocol): array|bool
```


## swoole_async_set

Эта функция позволяет устанавливать опции, связанные с асинхронным `IO`.

```php
function swoole_async_set(array $settings)
```



- `enable_signalfd` включение и отключение использования функции `signalfd`

- `enable_coroutine` переключение встроенных корутин, [подробности](/server/setting?id=enable_coroutine)

- `aio_core_worker_num` установление минимального числа рабочих процессов AIO
- `aio_worker_num` установление максимального числа рабочих процессов AIO


## swoole_error_log_ex()

Запись лога с определенным уровнем и ошибочным кодом.

```php
function swoole_error_log_ex(int $level, int $error, string $msg)
```

!> Версия Swoole >= `v4.8.1` доступна

## swoole_ignore_error()

Игнорирование ошибок с определенными ошибочными кодами в логах.

```php
function swoole_ignore_error(int $error)
```

!> Версия Swoole >= `v4.8.1` доступна
