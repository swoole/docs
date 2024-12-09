# Swoole\Thread <!-- {docsify-ignore-all} -->

Ab der Version `6.0` wird Unterstützung für Mehr线程 bereitgestellt, die man mit der `Thread API` ersetzen kann, um Mehrprozess-Technologie zu vermeiden. Im Vergleich zu Mehrprozessen bietet `Thread` eine breitere Palette an konsekrierten Datencontainer, die die Entwicklung von Spiele-Servern und Kommunikationsservern erleichtert.

- PHP muss im ZTS (Zend Thread Safety) Modus betrieben werden, beim编译en von PHP muss die Option `--enable-zts` angegeben werden.
- Beim编译en von Swoole muss die Compile-Option `--enable-swoole-thread` hinzugefügt werden.

## Ressourcenisolierung

Swoole-Threads ähneln Node.js Worker Threads und schaffen in einem Unterthread eine völlig neue ZendVM Umgebung. Unterthreads erben keine Ressourcen von den Elternthreads, daher wurden im Unterthread die folgenden Inhalte gelöscht und müssen neu erstellt oder festgelegt werden.

- Es müssen die bereits geladenen PHP-Dateien erneut mit `include/require` geladen werden.
- Die `autoload` Funktion muss erneut registriert werden.
- Klassen, Funktionen und Konstanten werden geleert und müssen erneut mit PHP-Dateien geladen und erstellt werden.
- Globale Variablen, wie `$GLOBALS`, `$_GET/$_POST` usw., werden zurückgesetzt.
- Static Eigenschaften von Klassen und static Variablen von Funktionen werden auf ihre anfänglichen Werte zurückgesetzt.
- Einige `php.ini` Optionen müssen im Unterthread erneut festgelegt werden, wie zum Beispiel `error_reporting()`.

## Nicht verfügbaren Funktionen

Im Mehrthreadmodus sind die folgenden Funktionen nur im Haupthread verfügbar und können nicht in Unterthreads ausgeführt werden:

- `swoole_async_set()` um Threadparameter zu ändern
- `Swoole\Runtime::enableCoroutine()` und `Swoole\Runtime::setHookFlags()`
- Nur der Haupthread kann Signalüberwachung einrichten, einschließlich `Process::signal()` und `Coroutine\System::waitSignal()`, die nicht in Unterthreads verwendet werden können
- Nur der Haupthread kann asynchrone Server erstellen, einschließlich `Server`, `Http\Server`, `WebSocket\Server`, die nicht in Unterthreads verwendet werden können

Abgesehen davon kann nach dem Aktivieren von Runtime Hooks im Mehrthreadmodus diese nicht mehr deaktiviert werden.

## Tödlicher Fehler
Wenn der Haupthread beendet wird und es aktive Unterthreads gibt, wird ein tödlicher Fehler geworfen, mit dem Statuscode `200`:
```
Fatal Error: 2 active threads are running, cannot exit safely.
```

## Überprüfung ob Threadunterstützung aktiviert ist

```shell
php -v
PHP 8.1.23 (cli) (built: Mar 20 2024 19:48:19) (ZTS)
Copyright (c) The PHP Group
Zend Engine v4.1.23, Copyright (c) Zend Technologies
```

`(ZTS)` zeigt an, dass Thread Safety aktiviert ist

```shell
php --ri swoole

swoole
Swoole => enabled
thread => enabled
```

`thread => enabled` zeigt an, dass Mehr线程unterstützung aktiviert ist

### Erstellung von Mehrthreads
```php
use Swoole\Thread;

$args = Thread::getArguments();
$c = 4;

// Der Haupthread hat keine Threadparameter, $args ist null
if (empty($args)) {
    # Haupthread
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
} else {
    # Unterthread
    echo "Thread #" . $args[0] . "\n";
    while (1) {
        sleep(1);
        file_get_contents('https://www.baidu.com/');
    }
}
```

### Threads + Serverseite (asynchrones Stil)

- Alle Arbeitsprozesse werden mit Threads ausgeführt, einschließlich `Worker`, `Task Worker`, `User Process`

- Die neue `SWOOLE_THREAD` Laufzeitmodus wurde hinzugefügt, der启用 wird, um Threads anstelle von Prozessen zu verwenden

- Es wurden die Konfigurationsoptionen [bootstrap](/server/setting?id=bootstrap) und [init_arguments](/server/setting?id=init_arguments) hinzugefügt, um den Einstiegsscript für Arbeitsthreads und geteilte Daten zwischen Threads zu definieren
- Der `Server` muss im Haupthread erstellt werden, aber neue `Thread` können in Rückruffunktionen verwendet werden, um andere Aufgaben auszuführen
- `Server::addProcess()` unterstützt keine Standardinput- und Standardoutput-Redirection für Prozessobjekte

```php
use Swoole\Process;
use Swoole\Thread;
use Swoole\Http\Server;

$http = new Server("0.0.0.0", 9503, SWOOLE_THREAD);
$http->set([
    'worker_num' => 2,
    'task_worker_num' => 3,
    'bootstrap' => __FILE__,
    // Durch init_arguments wird die Datenteilung zwischen den Threads erreicht.
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
    // Durch Swoole\Thread::getArguments() werden die im Konfigurieren über init_arguments übertragenen geteilten Daten für die SharedData abgerufen.
    var_dump(Thread::getArguments(), $wid);
});

$http->on('WorkerStop', function ($serv, $wid) {
    var_dump('stop: T' . Thread::getId());
});

$http->start();
```
