# Использование вопросов

## Каковы характеристики Swoole

> Сравнение QPS

Используя инструмент Apache-Bench (ab), была проведена нагрузка на статические страницы Nginx, программу HTTP на Go и программу HTTP на PHP7 с Swoole. На одной и той же машине, в базовом тесте с 100 одновременно работающими процессами и 1 миллионами HTTP-запросов, сравнение QPS следующее:

| Программное обеспечение | QPS | Версия программного обеспечения |
| --- | --- | --- |
| Nginx | 164489.92 | nginx/1.4.6 (Ubuntu) |
| Go | 166838.68 | go version go1.5.2 linux/amd64 |
| PHP7+Swoole | 287104.12 | Swoole-1.7.22-alpha |
| Nginx-1.9.9 | 245058.70 | nginx/1.9.9 |

!> Примечание: В тесте с Nginx-1.9.9 access_log был выключен, а static files были кешированы в памяти с использованием open_file_cache

> Тестовая среда

* CPU: Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* Память: 16G
* Диск: 128G SSD
* Операционная система: Ubuntu14.04 (Linux 3.16.0-55-generic)

> Метод нагрузочного тестирования

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> Конфигурация VHOST

```nginx
server {
    listen 80 default_server;
    root /data/webroot;
    index index.html;
}
```

> Тестовая страница

```html
<h1>Привет, мир!</h1>
```

> Количество процессов

Nginx работает с 4 рабочими процессами
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12月07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12月07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12月07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12月07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12月07   0:45 nginx: worker process
```

> Go

Тестовый код

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

В PHP7 был включен ускоритель `OPcache`.

Тестовый код

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **Международные权威ные тесты производительности веб-фреймворков Techempower Web Framework Benchmarks**

Последние результаты тестов: [techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swoole лидирует в **первом месте среди динамических языков**

Тесты операций с базой данных IO, использование базового бизнес-кода без особых оптимизаций

**Производительность превышает все статические языковые фреймворки (используется MySQL вместо PostgreSQL)**


## Как Swoole поддерживает длинные TCP-соединения

Для поддержания длинных TCP-соединений есть две группы настроек: [tcp_keepalive](/server/setting?id=open_tcp_keepalive) и [heartbeat](/server/setting?id=heartbeat_check_interval).


## Как правильно перезапустить service Swoole

В повседневной разработке, после изменения PHP-кода часто требуется перезапустить service, чтобы изменения вступили в силу. На одном и том же繁忙 backend-сервере постоянно обрабатываются запросы, и если управляющий перезапустит серверный процесс с помощью команды `kill`, это может привести к тому, что код будет прерван в середине выполнения, и не можно гарантировать целостность всей бизнес-логики.

Swoole предоставляет гибкий механизм для мягкого завершения/перезапуска, управляющий просто должен отправить серверу определенный сигнал или вызвать метод `reload`, чтобы рабочие процессы завершились и были перезапущены. Для подробностей смотрите [reload()](/server/methods?id=reload).

Однако есть несколько важных моментов:

Во-первых, необходимо, чтобы измененный код был пересмотрен в события `OnWorkerStart`, чтобы он вступил в силу. Например, если некоторый класс был загружен с помощью autoload Composer до `OnWorkerStart`, то это не будет работать.

Во-вторых, для `reload` необходимо использовать два параметра: [max_wait_time](/server/setting?id=max_wait_time) и [reload_async](/server/setting?id=reload_async). После установки этих параметров можно достичь `безопасного асинхронного перезапуска`.

Если эта функция отсутствует, рабочие процессы, получив сигнал о перезапуске или достигнув [max_request](/server/setting?id=max_request), немедленно прекратят обслуживание, и в этот момент в рабочих процессах могут быть еще слушатели событий, и эти асинхронные задания будут потеряны. После установки вышеуказанных параметров сначала создаются новые рабочие процессы, а старые рабочие процессы самостоятельно завершаются после выполнения всех событий, то есть `reload_async`.

Если старый рабочий процесс не выходит, в нижней части добавлен таймер, и если старый рабочий процесс не выходит в течение установленного времени ([max_wait_time](/server/setting?id=max_wait_time) секунд), нижняя часть принудительно прекратит его и создадет [WARNING](/question/use?id=forced-to-terminate) сообщение об ошибке.

Пример:

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tКлиент[$fd] получает данные: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

Например, в вышеуказанном коде, если нет `reload_async`, то таймер, созданный в `onReceive`, будет потерян и не будет возможности обработать обратную функцию таймера.

### События выхода процесса

Для поддержки функции асинхронного перезапуска в нижней части был добавлен новый事件 [onWorkerExit](/server/events?id=onWorkerExit), который будет активирован, когда старый рабочий процесс собирается выйти. В обратной функции этого события приложная слой может попытаться очистить некоторые длинные соединения `Socket`, пока в [событии цикла](/learn?id=что такоеeventloop) нет фидов или достигается [max_wait_time](/server/setting?id=max_wait_time), чтобы выйти из процесса.

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

В то же время в [Swoole Plus](https://www.swoole.com/swoole_plus) была добавлена функция обнаружения изменений в файлах, которая позволяет автоматически перезапустить рабочих без необходимости вручную перезапустить или отправить сигнал.
## Почему не безопасно закрыть сокет сразу после отправки данных

Завершение отправки данных сразу после закрытия сокета является небезопасным, как на стороне сервера, так и на стороне клиента.

Успешная отправка данных означает только то, что данные успешно записаны в буфер операционной системы сокета, но это не означает, что противоположная сторона действительно получила данные. Нельзя точно гарантировать, что операционная система действительно отправила данные, сервер-получатель их получил, а также что программы на стороне сервера их обработали.

> Для логики после закрытия смотрите следующие настройки linger

Эта логика аналогична разговору по телефону: A говорит B о чем-то, после чего挂机ает. Тогда B не знает, услышал ли он A. Если A говорит о чем-то, B отвечает, что понял, и затем挂机ает, это абсолютно безопасно.

Установка linger

Когда `сокет` закрывается, если в буфере все еще есть данные, операционная система в зависимости от настроек linger решит, как это сделать

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* l_onoff = 0, закрытие происходит немедленно, и операционная система отправит незавершенные данные, а затем освободит ресурсы, то есть выходит изящно.
* l_onoff != 0, l_linger = 0, закрытие происходит немедленно, но незавершенные данные не отправляются, а сокет описатель强制но закрывается с помощью пакета RST, то есть выход осуществляется принудительно.
* l_onoff !=0, l_linger > 0, закрытие не происходит сразу, ядро задержится на определенное время, которое определяет значение l_linger. Если до истечения времени отправка незавершенных данных (включая пакет FIN) и получение подтверждения от другой стороны не произойдет, закрытие будет успешным, и сокет описатель выходит изящно. В противном случае закрытие вернет ошибку, незавершенные данные будут потеряны, и сокет описатель будет принудительно закрыт. Если сокет описатель установлен为非блокирующим, то закрытие вернет значение сразу.

## client has already been bound to another coroutine

Для `TCP` соединения Swoole позволяет одновременно работать только одной coroutine для чтения и одной coroutine для письма. То есть нельзя одновременно читать/писать с одного TCP несколько coroutines, в底层 будет выброшен ошибка о связывании:

```shell
Fatal error: Uncaught Swoole\Error: Socket#6 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed 
```

Современный код:

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

Решение можно посмотреть здесь: https://wenda.swoole.com/detail/107474

!> Это ограничение применимо ко всем многопроцессным окружениям, самое распространенное - это использование одного и того же TCP-соединения в таких обратных вызовах, как [onReceive](/server/events?id=onreceive), потому что такие обратные вызовы автоматически создают coroutine,
Что делать, если есть необходимость в пулах соединений? В `Swoole` встроен [пул соединений](/coroutine/conn_pool), который можно использовать прямо, или можно вручную упаковать пул соединений с помощью `channel`.

## Call to undefined function Co\run()

Большинство примеров в этой документации используют `Co\run()` для создания контейнера coroutine, [изучите, что такое контейнер coroutine](/coroutine?id=что такое контейнер coroutine)

Если вы столкнулись со следующей ошибкой:

```bash
PHP Fatal error:  Uncaught Error: Call to undefined function Co\run()

PHP Fatal error:  Uncaught Error: Call to undefined function go()
```

Это означает, что ваша версия расширения Swoole меньше `v4.4.0` или вы вручную отключили [короткое имя coroutine](/other/alias?id=короткое имя coroutine), вот решения:

* Если версия слишком низкая, пожалуйста, обновите расширение до `>= v4.4.0` или используйте ключевое слово `go` вместо `Co\run` для создания coroutine;
* Если вы отключили короткое имя coroutine, пожалуйста, включите [короткое имя coroutine](/other/alias?id=короткое имя coroutine);
* Используйте метод [Coroutine::create](/coroutine/coroutine?id=create) вместо `Co\run` или `go` для создания coroutine;
* Используйте полное имя: `Swoole\Coroutine\run`;

## Можно ли использовать один и тот же Redis или MySQL соединение

Нельзя. Каждый процесс должен создавать отдельные соединения для Redis, MySQL, PDO, и то же самое касается других клиентов хранения. Причина в том, что если использовать одно соединение, то результат возврата невозможно гарантировать, который процесс его обработает, и процесс, который держит соединение, теоретически может читать и писать в это соединение, что приведет к путанице с данными.

**Поэтому между несколькими процессами нельзя делить соединения**

* В [Swoole\Server](/server/init) следует создавать объекты соединений в обратном вызове [onWorkerStart](/server/events?id=onworkerstart)
* В [Swoole\Process](/process/process) следует создавать объекты соединений в обратном вызове после старта [Swoole\Process->start](/process/process?id=start) в функции обратного вызова дочернего процесса
* Эта информация также применима к программам, использующим `pcntl_fork`

Пример:

```php
$server = new Swoole\Server('0.0.0.0', 9502);

// Необходимо создать соединение с Redis в обратном вызове onWorkerStart
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```

## Проблема закрытого соединения

Как показано ниже

```bash
NOTICE swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=123] is closed

NOTICE swFactoryProcess_finish (ERROR 1005): connection[fd=123] does not exists
```

Ответ от сервера происходит, когда клиент уже отключился

Часто встречается:

* Браузер безумно обновляет страницу (до того, как она полностью загружена, он ее обновляет)
* ab-тестирование中止ено на полпути
* wrk-тестирование по времени (неоконченные запросы отменяются после истечения времени)

Вышеупомянутое относится к нормальным явлениям, их можно игнорировать, поэтому уровень этой ошибки - NOTICE

Если из-за других причин внезапно появляется множество отключений соединений, следует обратить внимание

```bash
WARNING swWorker_discard_data (ERRNO 1007): [2] received the wrong data[21 bytes] from socket#75

WARNING Worker_discard_data (ERRNO 1007): [2] ignore data[5 bytes] received from session#2
```

Точно так же, эта ошибка также означает, что соединение уже закрыто, и полученная данные будут отброшены. Смотрите [discard_timeout_request](/server/setting?id=discard_timeout_request)
### Внимание

Несмотря на то, что предыдущая асинхронная версия поддерживала "реaltime" обновление свойства `connected`, на самом деле это было ненадежно, и соединение могло быть прерано сразу после проверки

## Что происходит при отказе в подключении

Когда вы пытаетесь подключиться к `telnet 127.0.0.1 9501`, вы получите сообщение о отказе в подключении, что означает, что сервер не слушает этот порт.

* Проверить, успешно ли выполняется программа: `ps aux`
* Проверить, слушает ли порт: `netstat -lp`
* посмотреть, нормально ли происходит сетевое общение: `tcpdump traceroute`

## Ресурсы временно недоступны [11]

Клиент `swoole_client` при `recv` получает

```shell
swoole_client::recv(): recv() failed. Error: Ресурсы временно недоступны [11]
```

Этот ошибка означает, что сервер не вернул данные в установленном времени, и超时 при приеме.

* Можно использовать `tcpdump` для просмотра процесса сетевого общения и проверки, отправил ли сервер данные
* Функция `send` сервера `$serv->send` должна проверять, вернулась ли она с `true`
* При внешнем сетевом общении, если время独占鳌ит, необходимо увеличить время ожидания `swoole_client`

## Отказ работы Worker из-за истечения времени, вынужденное завершение :id=forced-to-terminate

Обнаруживается следующая ошибка:

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): отказ работы Worker из-за истечения времени, вынужденное завершение
```

Это означает, что Worker не вышел в течение установленного времени ([max_wait_time](/server/setting?id=max_wait_time) секунд), и Swoole принудительно завершает этот процесс.

Используйте следующий код для воспроизведения:

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```

## Невозможно найти функцию обратной связи для сигнала Разорванный трубка: 13

Обнаруживается следующая ошибка:

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Невозможно найти функцию обратной связи для сигнала Разорванный трубка: 13
```

Это означает, что данные были отправлены на уже закрытую связь, обычно это происходит из-за отсутствия проверки возврата отправки, и отправка продолжается, несмотря на неудачу.

## Основные знания, необходимые для изучения Swoole

### Множество процессов/множество线程

* Понимание концепции процессов и线程 в операционной системе Linux
* Освоение основных знаний о переключении и расписании процессов/线程 в Linux
* Понимание основных знаний о межпроцессном общении, таких как трубы, UnixSocket, очереди сообщений, совместная память

### Сocket

* Понимание основных операций с Socket, таких как `accept/connect`, `send/recv`, `close`, `listen`, `bind`
* Понимание концепций приемной/отправительной缓存的 Socket, блокировки/нелок阻塞, таймаутов и т.д.

### IO многозадачности

* Понимание `select`/`poll`/`epoll`
* Освоение событийного цикла на основе `select`/`epoll`, модели Reactor
* Понимание可读ных и написанных событий

### TCP/IP сетевые протоколы

* Понимание протоколов TCP/IP
* Понимание протоколов передачи TCP и UDP

### Инструменты для отладки

* Использование [gdb](/other/tools?id=gdb) для отладки программ на Linux
* Использование [strace](/other/tools?id=strace) для отслеживания системных вызовов процесса
* Использование [tcpdump](/other/tools?id=tcpdump) для отслеживания процесса сетевого общения
* Другие инструменты Linux, такие как ps, [lsof](/other/tools?id=lsof), top, vmstat, netstat, sar, ss и т.д.

## Объект класса Swoole\Curl\Handler не может быть преобразован в int

При использовании [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl) возникает ошибка:

```bash
PHP Notice:  Объект класса Swoole\Curl\Handler не может быть преобразован в int

PHP Warning: curl_multi_add_handle() ожидает параметр 2 быть ресурсом, объект предоставлен
```

Причина в том, что после хука curl больше не является ресурсом, а объектом, поэтому его нельзя преобразовать в int.

!> Проблема с `int` предлагается связаться с стороной SDK для изменения кода, поскольку в PHP8 curl больше не является ресурсом, а объектом.

Есть три решения:

1. Не включать [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl). Однако начиная с версии [v4.5.4](/version/log?id=v454), [SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all) по умолчанию включает [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl), и его можно настроить как `SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL`, чтобы отключить [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl).

2. Использовать SDK Guzzle, который позволяет заменить Handler для реализации синхронизации через кору.

3. Начиная с версии Swoole `v4.6.0`, можно использовать [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl) вместо [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl).

## Когда одновременно используются однокорневая синхронизация и Guzzle 7.0+, результаты запросов напрямую выводятся в терминал после отправки :id=hook_guzzle

Воспроизведение кода следующее

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// Версия до v4.5.4
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// Результаты запроса будут напрямую выведены, а не напечатаны
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> Решение одно и то же, что и в предыдущей проблеме. Однако эта проблема уже исправлена в версиях Swoole >= `v4.5.8`.

## Ошибка: Нет свободных缓冲ных пространств [55]

Эту ошибку можно игнорировать. Эта ошибка возникает из-за того, что значение опции [socket_buffer_size](/server/setting?id=socket_buffer_size) слишком велико, и некоторые системы его не принимают, но это не влияет на работу программы.

## Максимальная размер GET/POST запросов

### Максимальная размер GET-запроса 8192

GET-запросы имеют только один Http-хедер, и Swoole использует фиксированный буфер памяти размером 8K, который нельзя изменять. Если запрос не является правильным Http-запросом, то возникнет ошибка. В нижней части будет выброшено следующее сообщение:

```bash
WARN swReactorThread_onReceive_http_request: Http header слишком длинный.
```

### Отправка файлов в POST-запросе

Максимальный размер ограничен настройкой [package_max_length](/server/setting?id=package_max_length), которая по умолчанию составляет 2M. Можно изменить размер, передав новое значение в метод [Server->set](/server/methods?id=set). Swoole работает на основе всей памяти, поэтому слишком большой размер может привести к тому, что множество одновременных запросов истощат ресурсы сервера.

Формула расчета: `максимальное потребление памяти` = `максимальное количество одновременных запросов` * `package_max_length`
