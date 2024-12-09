# Programmieranweisungen

Dieser Abschnitt wird detailliert die Unterschiede zwischen Coroutine-Programmierung und synchroner Programmierung sowie wichtige Anmerkungen erläutern.


## Anmerkungen

* Führen Sie `sleep` oder andere Schlaffunktionen nicht in Ihrem Code aus, da dies den gesamten Prozess blockieren kann; in Coroutinen können Sie [Co::sleep()](/coroutine/system?id=sleep) verwenden oder nach der [Einkey-Coroutine-Verkehrung](/runtime) `sleep` verwenden; Referenz: [Auswirkungen von sleep/usleep](/getting_started/notice?id=sleepusleep的影响)
* `exit/die` sind gefährlich und können dazu führen, dass der `Worker`-Prozess beendet wird; Referenz: [Auswirkungen von exit/die-Funktionen](/getting_started/notice?id=exitdie函数的影响)
* Sie können tödliche Fehler mit `register_shutdown_function` erfassen und bei unnormalem Prozessabgang einige Reinigungskräfte ausführen; Referenz: [Fangen Sie tödliche Fehler während des Betriebs des Servers ein](/getting_started/notice?id=捕获server运行期致命错误)
* Wenn in Ihrem PHP-Code eine Ausnahme geworfen wird, müssen Sie die Ausnahme in der Rückruffunktion mit `try/catch` erfassen, sonst wird der Arbeitsprozess beendet; Referenz: [Fangen Sie Ausnahmen und Fehler ein](/getting_started/notice?id=捕获异常和错误)
* `set_exception_handler` wird nicht unterstützt, Sie müssen Ausnahmen mit `try/catch` behandeln;
* `Worker`-Prozesse dürfen keinen gemeinsamen `Redis` oder `MySQL` oder anderen Netzwerkdienstclient verwenden, die Code für die Erstellung von Verbindungen zu `Redis/MySQL` kann in der `onWorkerStart` Rückruffunktion platziert werden. Referenz: [Kann man einen gemeinsamen Redis oder MySQL Verbindung verwenden?](/question/use?id=是否可以共用一个redis或mysql连接)


## Coroutine-Programmierung

Bitte lesen Sie die [Coroutine-Programmierungsanweisungen](/coroutine/notice) sorgfältig, wenn Sie die `Coroutine`-Funktionen verwenden.


## Koncurrentielle Programmierung

Bitte beachten Sie, dass im Gegensatz zum synchronen Blockierungsmodus der Programm in einem **koncurrenten Modus** ausgeführt wird. Während desselben Zeitraums kann der `Server` mehrere Anforderungen verarbeiten, daher **muss die Anwendung für jeden Kunden oder jede Anforderung unterschiedliche Ressourcen und Kontexte erstellen**. Andernfalls können zwischen verschiedenen Kunden und Anforderungen Daten- und Logikfehler entstehen.


## Verdoppelte Klassendefinitionen/Funktionsdefinitionen

Neulinge machen diesen Fehler sehr leicht, da `Swoole` im Speicher verbleibt, werden die Dateien, die Klassendefinitionen/Funktionsdefinitionen laden, nicht freigesetzt. Daher müssen Sie beim Einführen von PHP-Dateien, die Klassendefinitionen/Funktionen enthalten, `include_once` oder `require_once` verwenden, sonst wird ein tödlicher Fehler mit `cannot redeclare function/class` verursacht.


## Speicherverwaltung

!> Bei der Erstellung von `Servern` oder anderen dauerhaften Prozessen ist dies besonders zu beachten.

Das Lebenszyklus- und Speicherverwaltungssystem von PHP-Daemonprozessen unterscheidet sich völlig von herkömmlichen Webprogrammen. Die grundlegenden Prinzipien der Speicherverwaltung nach dem Start des `Servern` sind identisch mit herkömmlichen php-cli-Programmen. Bitte beziehen Sie sich für spezifische Informationen auf Artikel über die Speicherverwaltung von `Zend VM`.


### lokale Variablen

Nachdem die Ereignisrückruffunktion zurückgekehrt ist, werden alle lokalen Objekte und Variablen vollständig recycelt und es ist nicht erforderlich, sie mit `unset` zu befreien. Wenn eine Variable eine Ressourcentyp ist, wird auch die entsprechende Ressource von der PHP-Kernebene freigesetzt.

```php
function test()
{
	$a = new Object;
	$b = fopen('/data/t.log', 'r+');
	$c = new swoole_client(SWOOLE_SYNC);
	$d = new swoole_client(SWOOLE_SYNC);
	global $e;
	$e['client'] = $d;
}
```

* `$a`, `$b`, `$c` sind lokale Variablen, und wenn diese Funktion `return` gibt, werden diese drei Variablen sofort freigesetzt, der entsprechende Speicher wird sofort freigesetzt und die offene IO-Ressourcen-DateileHandle wird sofort geschlossen.
* `$d` ist auch eine lokale Variable, aber bevor `return` wird sie in der globalen Variable `$e` gespeichert, daher wird sie nicht freigesetzt. Wenn `unset($e['client'])` ausgeführt wird und keine anderen PHP-Variablen `$d` noch referenzieren, dann wird `$d` freigesetzt.


### globale Variablen

In PHP gibt es drei Arten von globalen Variablen.

* Variablen, die mit dem `global` Schlüssel deklariert wurden
* Klassenstatische Variablen und Funktionsstatische Variablen, die mit dem `static` Schlüssel deklariert wurden
* PHP-Superglobalvariablen, einschließlich `$_GET`, `$_POST`, `$GLOBALS`, usw.

Globale Variablen und Objekte, Klassenstatische Variablen und Variablen, die auf dem `Server`-Objekt保存 sind, werden nicht freigesetzt. Die Programmierer müssen sich um das Zerstören dieser Variablen und Objekte kümmern.

```php
class Test
{
	static $array = array();
	static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
	Test::$array[] = $fd;
	Test::$string .= $data;
}
```

* In Ereignisrückruffunktionen muss man besonders auf nicht lokale Variablen des `array`-Typs achten, bestimmte Operationen wie `TestClass::$array[] = "string"` können zu Speicherlecks führen, in schwerwiegenden Fällen kann ein Speicherüberlauf auftreten, und man sollte bei Bedarf auf die Reinigung großer Arrays achten.

* In Ereignisrückruffunktionen muss man bei der concatenation von nicht lokalen Variablen des `string`-Typs vorsichtig sein, da dies zu Speicherlecks führen kann, wie `TestClass::$string .= $data`, und in schwerwiegenden Fällen kann ein Speicherüberlauf auftreten.


### Lösungsansätze

* Synchron blockierende und reaktionslose `Server`-Programme können [max_request](/server/setting?id=max_request) und [task_max_request](/server/setting?id=task_max_request) festlegen, so dass der Prozess automatisch beendet wird, wenn der [Worker-Prozess](/learn?id=worker进程) / [Task-Prozess](/learn?id=taskworker进程) beendet wird oder die Task-Grenze erreicht ist, und alle Variablen/Objekte/Ressourcen des Prozesses werden freigesetzt und recycelt.
* Im Programm sollte man bei `onClose` oder beim Einstellen von Timern rechtzeitig `unset` verwenden, um Variablen zu bereinigen und Ressourcen zurückzugeben.


## Prozessisolierung

Prozessisolierung ist ein Problem, mit dem viele Neulinge oft konfrontiert sind. Warum ist der Wert einer globalen Variable nicht wirksam, wenn sie geändert wurde? Der Grund dafür ist, dass globale Variablen in verschiedenen Prozessen isoliert sind, daher sind sie ineffektiv.

Daher müssen Sie bei der Entwicklung von `Servern` mit `Swoole` das Problem der Prozessisolierung verstehen. Verschiedene `Worker`-Prozesse des `Swoole\Server`-Programms sind isoliert, und Operationen mit globalen Variablen, Timern und Ereignis监听ers sind nur im aktuellen Prozess wirksam.

* PHP-Variablen sind in verschiedenen Prozessen nicht geteilt, selbst wenn es sich um globale Variablen handelt, ist der Wert von `$i` in Prozess A, wenn er erhöht wird, nur in Prozess A auf `2`, während der Wert von `$i` in den anderen drei Prozessen immer noch `1` ist.
* Wenn Sie Daten zwischen verschiedenen Worker-Prozessen teilen müssen, können Sie `Redis`, `MySQL`, `Dateien`, `Swoole\Table`, `APCu`, `shmget` und andere Werkzeuge verwenden
* Dateihandles sind in verschiedenen Prozessen isoliert, daher sind Sockets, die in Prozess A erstellt wurden oder Dateien, die in Prozess A geöffnet wurden, in Prozess B nicht wirksam, selbst wenn ihr fd in Prozess B gesendet wird, ist es nicht nutzbar

Beispiel:

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$i = 1;

$server->on('Request', function ($request, $response) {
	global $i;
    $response->end($i++);
});

$server->start();
```

In einem Mehrprozessenserver ist `$i` zwar eine globale Variable (`global`), aber aufgrund der Prozessisolierung. Angenommen, es gibt vier Arbeitsprozesse, und in Prozess 1 wird `$i++` ausgeführt, tatsächlich wird nur `$i` in Prozess 1 auf `2`, während der Wert von `$i` in den anderen drei Prozessen immer noch `1` ist.

Der richtige Ansatz ist die Verwendung von `Swoole`-geboten [Swoole\Atomic](/memory/atomic) oder [Swoole\Table](/memory/table) Datenstrukturen zum Speichern von Daten. Wie im obigen Code mit `Swoole\Atomic` umgesetzt.

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$atomic = new Swoole\Atomic(1);

$server->on('Request', function ($request, $response) use ($atomic) {
    $response->end($atomic->add(1));
});

$server->start();
```

!> `Swoole\Atomic` Daten sind auf gemeinsamen Speicher basierend, und wenn die `add` Methode mit `1` aufgerufen wird, ist dies auch in anderen Arbeitsprozessen wirksam

Die von `Swoole` bereitgestellten [Table](/memory/table)-, [Atomic](/memory/atomic)- und [Lock](/memory/lock)-Komponenten können für die Mehrprozessprogrammiersprache verwendet werden, aber sie müssen vor dem Start des `Servern` erstellt werden. Darüber hinaus können TCP-Clientverbindungen, die vom `Server` unterhalten werden, auch zwischen Prozessen operiert werden, wie `Server->send` und `Server->close`.
## stat缓存清理

Das PHP-Unterwerk hat den `stat`-Systemaufruf um einen `Cache` ergänzt, der bei Verwendung von Funktionen wie `stat`, `fstat`, `filemtime` usw. möglicherweise einen Cache treffen und historische Daten zurückgeben kann.

Der [clearstatcache](https://www.php.net/manual/en/function.clearstatcache.php)-Funktion kann verwendet werden, um den File `stat`-Cache zu leeren.

## mt_rand随机数

In `Swoole`, wenn `mt_rand` innerhalb des Elternprozesses aufgerufen wird, sind die Ergebnisse von `mt_rand` in verschiedenen Tochterprozessen identisch, daher muss innerhalb jedes Tochterprozesses `mt_srand` aufgerufen werden, um das Saatgut erneut zu säen.

!> Funktionen wie `shuffle` und `array_rand`, die auf Zufallszahlen angewiesen sind, sind ebenfalls beeinträchtigt  

Beispiel:

```php
mt_rand(0, 1);

//begin
$worker_num = 16;

//fork Prozess
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

//asynchron ausführen von Prozessen
function child_async(Swoole\Process $worker) {
    mt_srand(); //wieder säen
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
```

## 捕获异常和错误

### 可捕获的异常/错误

In `PHP` gibt es grob gesagt drei Arten von fassbaren Ausnahmen/Fehlern

1. `Error`: Eine spezielle Art von Fehler, die vom PHP-Kernel geworfen wird, wie zum Beispiel das Nicht existieren von Klassen, Funktionen oder falsche Funktionsparameter. Diese Fehler sollten nicht mit der `Error`-Klasse in PHP-Code als Ausnahme geworfen werden
2. `Exception`: Die Grundklasse für Ausnahmen, die von Entwicklern verwendet werden sollten
3. `ErrorException`: Diese Ausnahmeklasse ist speziell dafür da, um `PHP`-Warnungen/Notices usw. über `set_error_handler` in Ausnahmen umzuwandeln. Die Zukunft von PHP beabsichtigt es sicherlich, alle Warnungen/Notices in Ausnahmen umzuwandeln, damit PHP-Programme besser und kontrollierbarer verschiedene Fehler behandeln können

!> Alle oben genannten Klassen implementieren das `Throwable`-Interface, das heißt, mit `try {} catch(Throwable $e) {}` können alle抛出 Ausnahmen/Fehler gefangen werden

Beispiel 1:
```php
try {
	test();
} 
catch(Throwable $e) {
	var_dump($e);
}
```
Beispiel 2:
```php
try {
	test();
}
catch (Error $e) {
	var_dump($e);
}
catch(Exception $e) {
	var_dump($e);
}
```

### 不可捕获的致命错误和异常

Eine wichtige Ebene von PHP-Fehlern ist der `E_ERROR`, der ein `Fatal Error` ist, wenn Ausnahmen/Fehler nicht gefangen werden, wenn nicht genug Speicher vorhanden ist oder bei einigen编译zeitfehlern (nicht existierende geerbte Klassen). Ein `Fatal Error` wird ausgelöst, wenn ein nicht zurückverfolgbarer Fehler im Programm auftritt. Ein PHP-Programm kann diesen Level eines Fehlers nicht fangen und kann nur mit `register_shutdown_function` später einige Handhabungsoperationen durchführen.

### 在协程中捕获运行时异常/错误

In der `Swoole4`-Korreutiprogrammierung führt das Auslösen eines Fehlers in einem Kontext zu einem Exit des gesamten Prozesses und zum Beenden der Ausführung aller Coroutinen im Prozess. In der obersten Ebene des Kontextes kann zuerst ein `try/catch` für Ausnahmen/Fehler durchgeführt werden, um nur den fehlerhaften Kontext zu beenden.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    //Der Fehler in Coroutine 1 beeinträchtigt nicht Coroutine 2
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
```

### 捕获Server运行期致命错误

Sobald ein tödlicher Fehler im Serverlaufzeit auftritt, können keine Antworten mehr an die Clientverbindungen bereitgestellt werden. Zum Beispiel sollte ein Webserver bei einem tödlichen Fehler den Client mit einer HTTP 500-Fehlerantwort informieren.

In PHP können mit `register_shutdown_function` und `error_get_last` zwei Funktionen tödliche Fehler gefangen und die Fehlermeldung an die Clientverbindungen gesendet werden.

Das spezifische Codebeispiel ist wie folgt:

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    register_shutdown_function(function () use ($response) {
        $error = error_get_last();
        var_dump($error);
        switch ($error['type'] ?? null) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                // log or send:
                // error_log($message);
                // $server->send($fd, $error['message']);
                $response->status(500);
                $response->end($error['message']);
                break;
        }
    });
    exit(0);
});
$http->start();
```

## 使用影响

### sleep/usleep的影响

In einem Programm mit asynchronem IO darf **nicht** `sleep/usleep/time_sleep_until/time_nanosleep` verwendet werden. (Im Folgenden wird `sleep` allgemein für alle Schlaffunktionen verwendet.)

* Die `sleep`-Funktion lässt den Prozess in einen schlafenden Block fallen
* Erst nach der angegebenen Zeit wird der aktuelle Prozess vom Betriebssystem wieder geweckt
* Während des `sleep`-Prozesses kann nur ein Signal den Schlaf unterbrechen
* Da das Signalhandling von Swoole auf der Grundlage von `signalfd` implementiert ist, kann selbst das Senden eines Signals den `sleep` nicht unterbrechen

Die von Swoole bereitgestellten Funktionen [Swoole\Event::add](/event?id=add), [Swoole\Timer::tick](/timer?id=tick), [Swoole\Timer::after](/timer?id=after) und [Swoole\Process::signal](/process/process?id=signal) werden nach dem `sleep`-Prozess gestoppt. [Swoole\Server](/server/tcp_init) kann keine neuen Anforderungen mehr verarbeiten.

#### Beispiel

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    sleep(100);
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> Wenn in der [onReceive](/server/events?id=onreceive)-Ereignis die `sleep`-Funktion ausgeführt wird, kann der Server in 100 Sekunden keine weiteren Clientanforderungen erhalten.

### exit/die函数的影响

In einem Swoole-Programm darf **nicht** `exit/die` verwendet werden. Wenn in der PHP-Code `exit/die` auftritt, wird der derzeit aktive [Worker进程](/learn?id=worker进程), [Task进程](/learn?id=taskworker进程), [User进程](/server/methods?id=addprocess) sowie der `Swoole\Process` Prozess sofort beendet.

Das Ausführen von `exit/die` führt dazu, dass der Workerprozess aufgrund eines Ausnahms beendet wird und vom Masterprozess erneut gestartet wird, was letztendlich zu einem endlosen Prozessstart und -ende führt und eine große Anzahl von Alarmprotokollen erzeugt.

Es wird empfohlen, anstelle von `exit/die` `try/catch` zu verwenden, um die Ausführung abzubrechen und aus der PHP-Funktionsrufstack zu springen.

```php
Swoole\Coroutine\run(function () {
    try
    {
        exit(0);
    } catch (Swoole\ExitException $e)
    {
        echo $e->getMessage()."\n";
    }
});
```

!> `Swoole\ExitException` wird in Swoole v4.1.0 und höher direkt unterstützt und ermöglicht es, die PHP-`exit`-Funktion in Coroutinen und Servers zu verwenden. In diesem Fall wird von unten automatisch ein fassbarer `Swoole\ExitException` geworfen, den Entwickler kann an der gewünschten Stelle fangen und die gleiche Ausnahm逻辑 wie im nativen PHP umsetzen. Weitere Informationen zum Verwenden finden Sie unter [Ausstieg aus der Coroutine](/coroutine/notice?id=退出协程);

Die Ausnahmebehandlung ist freundlichere als `exit/die`, da Ausnahmen kontrollierbar sind, während `exit/die` nicht kontrollierbar ist. Durch das Verwenden von `try/catch` an der äußersten Ebene können Ausnahmen gefangen werden, was nur den aktuellen Task beendet. Der Workerprozess kann weiterhin neue Anforderungen verarbeiten, während `exit/die` den Prozess direkt beendet und alle von ihm保存en Variablen und Ressourcen zerstört. Wenn es im Prozess noch andere Aufgaben gibt, die mit `exit/die` nicht mehr verarbeitet werden würden.
### Die Auswirkungen von `while` Zyklen

Wenn ein asynchroner Programm einen Deadlock erlebt, können Ereignisse nicht ausgelöst werden. Asynchrone IO-Programme verwenden das `Reactor-Modell`, bei dem während des Betriebs am Punkt `reactor->wait` gepollt werden muss. Wenn ein Deadlock auftritt, liegt die Kontrolle über das Programm im `while` Block, und der `reactor` kann die Kontrolle nicht übernehmen, um Ereignisse zu erkennen, sodass auch die IO-Ereignis-Rückruffunktion nicht ausgelöst werden kann.

!>密集计算的代码没有任何IO操作，因此不能称为阻塞  

#### 示例程序

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactorId, $data) {
    $i = 0;
    while(1)
    {
        $i++;
    }
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> Im [onReceive](/server/events?id=onreceive)-Ereignis wurde ein Deadlock durchgeführt, der `server` kann keine weiteren Client-Anfragen mehr entgegennehmen und muss warten, bis der Zyklus beendet ist, um neue Ereignisse zu verarbeiten.
