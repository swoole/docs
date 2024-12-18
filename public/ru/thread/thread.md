# Своле\Тред <!-- {docsify-ignore-all} -->

Начиная с версии `6.0`, поддерживается многоп线程ное выполнение, и можно использовать API线程 для замены многопроцессов. По сравнению с многопроцессами, `Тред` предоставляет более богатые контейнеры для синхронизации, что удобно при разработке игровых серверов и коммуникационных серверов.

- `PHP` должен работать в режиме ZTS, при компиляции `PHP` необходимо добавить `--enable-zts`
- При компиляции `Swoole` необходимо добавить опцию `--enable-swoole-thread`

## Изоляция ресурсов

Треды `Swoole` похожи на рабочие нити `Node.js`, в которых создается совершенно новый контекст `ZendVM`. У дочерних нитей нет наследования ресурсов от родительской нити, поэтому следующие ресурсы в дочерних нитях очищаются и должны быть заново созданы или установлены.

- Заменены на новые `include/require`

- Необходимо заново зарегистрировать функцию `autoload`

- Классам, функциям, константам будут очищены, необходимо заново载入 `PHP` файлы для их создания

- Глобальные переменные, такие как `$GLOBALS`, `$_GET/$_POST` и т.д., будут сброшены

- Статические свойства классов, статические переменные функций будут сброшены на начальные значения
- Некоторые опции `php.ini`, такие как `error_reporting()`, должны быть заново установлены в дочерних нитях

## Неисходимые функции

В многоп线程ном режиме следующие функции могут быть использованы только в главной нити, их нельзя выполнять в дочерних нитях:

- `swoole_async_set()` изменяет параметры нити

- `Swoole\Runtime::enableCoroutine()` и `Swoole\Runtime::setHookFlags()`

- Сигналы можно слушать только в главной нити, включая `Process::signal()` и `Coroutine\System::waitSignal()`, которые не могут использоваться в дочерних нитях
- асинхронные серверы можно создавать только в главной нити, включая `Server`, `Http\Server`, `WebSocket\Server` и т.д., их нельзя использовать в дочерних нитях

Кроме того, после включения `Runtime Hook` в многоп线程ном режиме он нельзя выключить.

## Смертельная ошибка
Когда основная нить завершает работу и существуют активные дочерние нити, будет выброшена смертельная ошибка, код завершения: `200`, сообщение об ошибке следующее:
```
Смертельная ошибка: 2 активных нити работают, невозможно безопасно завершиться.
```

## Проверка наличия поддержки нитей

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)` означает включена线程овая безопасность

```shell
php --ri swoole

swoole
Swoole => enabled
thread => enabled
```

`thread => enabled` означает включена поддержка многоп线程

### Создание многоп线程
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

// В главной нити нет параметров нити, $args равен null
if (empty($args)) {
    # Главная нить
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # Дочерняя нить
    echo "Нить #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```

### Нить + сервер (асинхронный стиль)

- Все рабочие процессы будут работать в нитях, включая `Worker`, `Task Worker`, `User Process`

- Добавлен режим выполнения `SWOOLE_THREAD`, после его включения он будет работать вместо процессов

- Добавлены настройки [bootstrap](/server/setting?id=bootstrap) и [init_arguments](/server/setting?id=init_arguments), которые используются для указания входа в рабочие нити и общего данных между нитями
- сервер должен быть создан в главной нити, новые нити можно создавать в обратных функциях для выполнения других задач
- Объекты процессов `Server::addProcess()` не поддерживают стандартное перенаправление ввода/вывода

```php
use Swoole\Process;
use Swoole\Thread;
use Swoole\Http\Server;

$http = new Server("0.0.0.0", 9503, SWOOLE_THREAD);
$http->set([
    'worker_num' => 2,
    'task_worker_num' => 3,
    'bootstrap' => __FILE__,
    // Использование init_arguments для реализации общего доступа к данным между нитями.
    'init_arguments' => function () use ($http) {
        $map = new Swoole\Thread\Map;
        return [$map];
    }
]);

$http->on('Request', function ($req, $resp) use ($http) {
    $resp->end('hello world');
});

$http->on('pipeMessage', function ($http, $srcWorkerId, $msg) {
    echo "[worker#" . $http->getWorkerId() . "]\treceived pipe message[$msg] from " . $srcWorkerId . "\n";
});

$http->addProcess(new Process(function () {
   echo "user process, id=" . Thread::getId();
   sleep(2000);
}));

$http->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
    var_dump($taskId, $srcWorkerId, $data);
    return ['result' => uniqid()];
});

$http->on('Finish', function ($server, $taskId, $data) {
    var_dump($taskId, $data);
});

$http->on('WorkerStart', function ($serv, $wid) {
    // Получение общего данных из init_arguments с помощью Swoole\Thread::getArguments() и worker ID.
    var_dump(Thread::getArguments(), $wid);
});

$http->on('WorkerStop', function ($serv, $wid) {
    var_dump('stop: T' . Thread::getId());
});

$http->start();
```
