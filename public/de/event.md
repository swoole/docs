# Ereignis

Die `Swoole`-Erweiterung bietet auch Schnittstellen zum direkten Bedienen des unteren `epoll/kqueue`-Ereigniskreises. Man kann `socket`-Objekte, die von anderen Erweiterungen erstellt wurden, oder `socket`-Objekte, die von der `stream/socket`-Erweiterung in PHP-Code erstellt wurden, dem [EventLoop](/learn?id=Was ist ein Eventloop) von Swoole hinzufügen. Andernfalls kann bei synchronem I/O ein drittes Party-$fd dazu führen, dass der EventLoop von Swoole nicht ausgeführt wird. [Beispiel zur Umwandlung von synchronem I/O in asynchronem I/O](/learn?id=Synchroner I/O in asynchronen I/O).

!> Das `Event`-Modul ist ziemlich niedriglevel und eine einfache Verpackung für `epoll`. Es ist ratsam, dass die Nutzer über Erfahrung im Bereich der I/O-Multiplexing-Programmierung verfügen.

## Ereignispriorität

1. Signalbehandlungskallbackfunktionen, die über `Process::signal` festgelegt wurden
2. Timer-Callbackfunktionen, die über `Timer::tick` und `Timer::after` festgelegt wurden
3. Verzögerte Ausführungsscriptfunktionen, die über `Event::defer` festgelegt wurden
4. Zyklische Callbackfunktionen, die über `Event::cycle` festgelegt wurden

## Methoden


### add()

Fügt einen `socket` dem unteren Reaktor-Ereignisüberwachung hinzu. Diese Funktion kann sowohl im `Server`- als auch im `Client`-Modus verwendet werden.
```php
Swoole\Event::add(mixed $sock, callable $read_callback, callable $write_callback = null, int $flags = null): bool
```

!> Bei Verwendung in einem `Server`-Programm muss dies nach dem Start des `Worker`-Prozesses und vor dem Aufrufen von `Server::start` erfolgen. Keine asynchronen I/O-Schnittstellen dürfen vor dieser Zeit aufgerufen werden

* **Parameter** 

  * **`mixed $sock`**
    * **Funktion**: Dateideskriptor, `stream`-Ressource, `sockets`-Ressource, `object`
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`callable $read_callback`**
    * **Funktion**: Callbackfunktion für Lesevorgänge
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`callable $write_callback`**
    * **Funktion**: Callbackfunktion für Schreibvorgänge [Dieser Parameter kann eine String-Funktionsname, ein Objekt+Methode, eine Klassenstatic-Methode oder eine Anonymfunkt sein. Wenn dieser `socket` lesbar oder schreibbar ist, wird die angegebene Funktion aufgerufen.]
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $flags`**
    * **Funktion**: Maske für Ereignistypen [Man kann Lesevorgänge und Schreibvorgänge aktivieren/deaktivieren, wie `SWOOLE_EVENT_READ`, `SWOOLE_EVENT_WRITE` oder `SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE`]
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

* **4 Arten von `$sock`**


Typ | Beschreibung
---|---
int | Dateideskriptor, einschließlich `Swoole\Client->$sock`, `Swoole\Process->$pipe` oder anderer `$fd`
streamressource | Ressource, die von `stream_socket_client`/`fsockopen` erstellt wurde
socketsressource | Ressource, die von der `sockets`-Erweiterung mit `socket_create` erstellt wurde, muss bei der Kompilierung mit [./configure --enable-sockets](/environment?id=Kompilierungsoptionen) hinzugefügt werden
object | `Swoole\Process` oder `Swoole\Client`, wird unter der Erde automatisch zu einem [UnixSocket](/learn?id=Was ist ein IPC) (bei `Process`) oder zum Client-Verbindungs`socket` (bei `Swoole\Client`)

* **Rückkehrwert**

  * Erfolgreiche Hinzufügung der Ereignisüberwachung zurückgibt `true`
  * Fehlerhafte Hinzufügung der Ereignisüberwachung zurückgibt `false`, bitte verwenden Sie `swoole_last_error` um den Fehlercode zu erhalten
  * Ein bereits hinzugefügtes `socket` kann nicht erneut hinzugefügt werden, es ist möglich, den `socket` mit `swoole_event_set` zu ändern und die Callbackfunktion und den Ereignistyp des `socket`s anzupassen

  !> Wenn Sie ein `socket` mit `Swoole\Event::add` zur Ereignisüberwachung hinzufügen, wird der `socket` unter der Erde automatisch in einen nicht blockierenden Modus umgeschaltet

* **Verwendungsexempel**

```php
$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Swoole\Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    // Nachdem der socket verarbeitet wurde, wird er aus dem epoll-Ereignis entfernt
    Swoole\Event::del($fp);
    fclose($fp);
});
echo "Fertig\n";  // `Swoole\Event::add` blockiert den Prozess nicht, diese Zeile wird nacheinander ausgeführt
```

* **Callbackfunktionen**

  * In der Lesebildung ($read_callback) muss die Funktion `fread`, `recv` usw. verwendet werden, um Daten aus dem Cache des `socket` zu lesen, sonst wird das Ereignis weiterhin ausgelöst. Wenn Sie nicht weiterlesen möchten, müssen Sie das Ereignisüberwachung mit `Swoole\Event::del` entfernen
  * In der Schreib bildung ($write_callback) muss nach dem Schreiben in den `socket` die Ereignisüberwachung mit `Swoole\Event::del` entfernt werden, sonst wird das Schreibereignis weiterhin ausgelöst
  * Wenn `fread`, `socket_recv`, `socket_read`, `Swoole\Client::recv` mit `false` zurückgibt und der Fehlercode `EAGAIN` ist, bedeutet dies, dass es im Empfangscache des aktuellen `socket` keine Daten gibt. In diesem Fall muss eine Lesebildung hinzugefügt werden, um auf ein [EventLoop](/learn?id=Was ist ein Eventloop)-Benachrichtigungs zu warten
  * Wenn `fwrite`, `socket_write`, `socket_send`, `Swoole\Client::send` mit `false` zurückgibt und der Fehlercode `EAGAIN` ist, bedeutet dies, dass der Sendecache des aktuellen `socket` voll ist und vorübergehend keine Daten gesendet werden können. Es ist notwendig, auf ein Schreibereignis zu warten, um auf ein [EventLoop](/learn?id=Was ist ein Eventloop)-Benachrichtigungs zu warten


### set()

Ändert die Callbackfunktion und die Maske für die Ereignisüberwachung.

```php
Swoole\Event::set($fd, mixed $read_callback, mixed $write_callback, int $flags): bool
```

* **Parameter** 

  * Die Parameter sind genau wie bei [Event::add](/event?id=add). Wenn das übergebene `$fd` im [EventLoop](/learn?id=Was ist ein Eventloop) nicht vorhanden ist, wird `false` zurückgegeben.
  * Wenn `$read_callback` nicht `null` ist, wird die Lesebildung auf die angegebene Funktion geändert
  * Wenn `$write_callback` nicht `null` ist, wird die Schreib bildung auf die angegebene Funktion geändert
  * `$flags` kann aktiviert/deaktiviert werden, um die Überwachung von Lesevorgängen (SWOOLE_EVENT_READ) und Schreibvorgängen (SWOOLE_EVENT_WRITE) zu aktivieren/deaktivieren  

  !> Beachten Sie, dass wenn Sie das Ereignis `SWOOLE_EVENT_READ` überwachen und derzeit keine Lesebildung festgelegt ist, die untere Ebene nur die Informationen zur Callbackfunktion speichern wird und keine Ereignis回调function ausgelöst wird.
  * Sie können auch `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)` verwenden, um den überwachten Ereignistyp zu ändern. In diesem Fall wird die untere Ebene ein Lesevorgang auslösen.

* **Statusänderung**

  * Wenn Sie mit `Event::add` oder `Event::set` eine Lesebildung festlegen, aber keine Überwachung für das Lesevorgang `SWOOLE_EVENT_READ` aktiviert haben, speichern die untere Ebene nur Informationen zur Callbackfunktion und lösen keine Ereignis回调function aus.
  * Sie können auch `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)` verwenden, um den überwachten Ereignistyp zu ändern. In diesem Fall löst die untere Ebene ein Lesevorgang aus.

* **Freischaltung von Callbackfunktionen**

!> Beachten Sie, dass `Event::set` nur Callbackfunktionen ersetzen kann, aber nicht freisetzen kann. Zum Beispiel: `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`, wenn die in den Parametern angegebenen `read_callback` und `write_callback` `null` sind, bedeutet dies nicht, dass die Ereignis回调function auf `null` gesetzt wird, sondern dass sie nicht geändert wird.

Die unteren Ebenen werden die `read_callback` und `write_callback` Ereignis回调funktionen nur freisetzen, wenn Sie die Ereignisüberwachung mit `Event::del` entfernen.


### isset()

Prüft, ob das übergebene `$fd` bereits einer Ereignisüberwachung hinzugefügt wurde.

```php
Swoole\Event::isset(mixed $fd, int $events = SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE): bool
```

* **Parameter** 

  * **`mixed $fd`**
    * **Funktion**: Jeder beliebige socket-Dateideskriptor [Siehe [Event::add](/event?id=add) Dokumentation]
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

  * **`int $events`**
    * **Funktion**: Überprüfte Ereignistypen
    * **Standardwert**: Keiner
    * **Andere Werte**: Keiner

* **$events**
Ereignistyp | Beschreibung
---|---
`SWOOLE_EVENT_READ` | Ob die Lesereignisse überwacht werden
`SWOOLE_EVENT_WRITE` | Ob die Schreibereignisse überwacht werden
`SWOOLE_EVENT_READ \| SWOOLE_EVENT_WRITE` | Überwachen von Lesereignissen oder Schreibereignissen

* **Beispiel für die Verwendung**

```php
use Swoole\Event;

$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    Swoole\Event::del($fp);
    fclose($fp);
}, null, SWOOLE_EVENT_READ);
var_dump(Event::isset($fp, SWOOLE_EVENT_READ)); //returns true
var_dump(Event::isset($fp, SWOOLE_EVENT_WRITE)); //returns false
var_dump(Event::isset($fp, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)); //returns true
```


### write()

Gebraucht für Sockets, die mit der PHP-eigene `stream/sockets`-Erweiterung erstellt wurden, um Daten an die Peer-Seite zu senden. Wenn die gesendeten Datenmenge groß ist und der Socket-Schreibcache voll ist, wird die Sendung blockiert oder ein [EAGAIN](/other/errno?id=linux)-Fehler zurückgegeben.

Die `Event::write`-Funktion kann das Senden von Daten über `stream/sockets`-Ressourcen in **Asynchronie** umwandeln. Wenn der Cache voll ist oder ein [EAGAIN](/other/errno?id=linux) zurückgegeben wird, wird die Swoole-Unterseite die Daten in den Sendeschrank aufnehmen und auf Schreibbereitschaft überwachen. Wenn der Socket schreibbar ist, wird die Swoole-Unterseite automatisch schreiben.

```php
Swoole\Event::write(mixed $fd, miexd $data): bool
```

* **Parameter** 

  * **`mixed $fd`**
    * **Funktion**: beliebiges Socket-Dateideskriptor 【Siehe [Event::add](/event?id=add) Dokumentation】
    * **Standardwert**: Keine
    * **Andere Werte**: Keine

  * **`miexd $data`**
    * **Funktion**: Die zu sendenden Daten 【Die Länge der gesendeten Daten darf nicht die Größe des `Socket`-Caches überschreiten】
    * **Standardwert**: Keine
    * **Andere Werte**: Keine

!> `Event::write` kann nicht für `SSL/TLS` und andere `stream/sockets`-Ressourcen mit Tunnel-Verschlüsselung verwendet werden  
Nach erfolgreicher `Event::write`-Operation wird der `$socket` automatisch in Blockiers模式 gesetzt

* **Beispiel für die Verwendung**

```php
use Swoole\Event;

$fp = stream_socket_client('tcp://127.0.0.1:9501');
$data = str_repeat('A', 1024 * 1024*2);

Event::add($fp, function($fp) {
     echo fread($fp);
});

Event::write($fp, $data);
```

#### Logik der Swoole-Unterseite, wenn der SOCKET-Cache voll ist

Wenn weiterhin in den `SOCKET` geschrieben wird und die Peer-Seite nicht schnell genug liest, wird der `SOCKET`-Cache voll. Die Swoole-Unterseite wird die Daten in den RAM-Cache speichern, bis ein Schreibereignis ausgelöst wird, um sie dann in den `SOCKET` zu schreiben.

Wenn auch der RAM-Cache voll ist, wirft die Swoole-Unterseite den Fehler `pipe buffer overflow, reactor will block.` aus und betritt einen blockierenden Warteschleifen.

!> Ein Rückgang des Caches mit `false` ist eine atomare Operation und tritt nur auf, wenn alle Einträge erfolgreich geschrieben werden oder alle fehlschlagen


### del()

Entfernen Sie das überwachte `socket` vom `reactor`. `Event::del` sollte mit `Event::add`配对 verwendet werden.

```php
Swoole\Event::del(mixed $sock): bool
```

!> muss vor der `close`-Operation des `socket` verwendet werden, um das Ereignis zu entfernen, sonst kann eine Memory-Leake auftreten

* **Parameter** 

  * **`mixed $sock`**
    * **Funktion**: Das Dateideskriptor des `socket`
    * **Standardwert**: Keine
    * **Andere Werte**: Keine


### exit()

Beenden Sie das Ereignis-Polling.

!> Diese Funktion ist nur in `Client`-Programmen gültig

```php
Swoole\Event::exit(): void
```


### defer()

Führen Sie die Funktion aus, wenn der nächste Ereignis-Zyklus beginnt. 

```php
Swoole\Event::defer(mixed $callback_function);
```

!> Die Rückruffunktion von `Event::defer` wird nach dem Ende des aktuellen `EventLoop` und vor Beginn des nächsten Ereignis-Zyklus ausgeführt.

* **Parameter** 

  * **`mixed $callback_function`**
    * **Funktion**: Die Funktion, die nach Ablauf der Zeit ausgeführt wird 【Muss callable sein. Die Rückruffunktion akzeptiert keine Parameter, Sie können Parameter an die Rückruffunktion über das `use`-Syntax von anonymen Funktionen weitergeben; Wenn Sie während der Ausführung der `$callback_function`-Funktion neue `defer`-Aufgaben hinzufügen, werden sie immer innerhalb dieses Ereignis-Zyklus ausgeführt】
    * **Standardwert**: Keine
    * **Andere Werte**: Keine

* **Beispiel für die Verwendung**

```php
Swoole\Event::defer(function(){
    echo "After EventLoop\n";
});
```


### cycle()

Definieren Sie eine Funktion, die周期isch innerhalb eines Ereignis-Zyklus ausgeführt wird. Diese Funktion wird nach jedem Ereignis-Zyklus aufgerufen. 

```php
Swoole\Event::cycle(callable $callback, bool $before = false): bool
```

* **Parameter** 

  * **`callable $callback_function`**
    * **Funktion**: Die festgelegte Rückruffunktion 【Wenn `$callback` `null` ist, bedeutet dies, die `cycle`-Funktion zu löschen. Wenn die `cycle`-Funktion festgelegt ist und neu festgelegt wird, wird die vorherige Einstellung überschrieben】
    * **Standardwert**: Keine
    * **Andere Werte**: Keine

  * **`bool $before`**
    * **Funktion**: Die Funktion vor dem [EventLoop](/learn?id=什么是eventloop) aufrufen
    * **Standardwert**: Keine
    * **Andere Werte**: Keine

!> Es können sowohl `before=true` als auch `before=false` als Rückruffunktionen existieren.

  * **Beispiel für die Verwendung**

```php
Swoole\Timer::tick(2000, function ($id) {
    var_dump($id);
});

Swoole\Event::cycle(function () {
    echo "hello [1]\n";
    Swoole\Event::cycle(function () {
        echo "hello [2]\n";
        Swoole\Event::cycle(null);
    });
});
```


### wait()

Starten Sie das Ereignis-Überwachung.

!> Bitte stellen Sie diese Funktion am Ende Ihres PHP-Programms

```php
Swoole\Event::wait();
```

* **Beispiel für die Verwendung**

```php
Swoole\Timer::tick(1000, function () {
    echo "hello\n";
});

Swoole\Event::wait();
```

### dispatch()

Starten Sie das Ereignis-Überwachung.

!> Führt nur eine `reactor->wait`-Operation durch, was unter der Linux-Plattform dem manuellen Aufruf von `epoll_wait` entspricht. Im Gegensatz zu `Event::wait` hält die Swoole-Unterseite unter der Erde einen Loop bei.

```php
Swoole\Event::dispatch();
```

* **Beispiel für die Verwendung**

```php
while(true)
{
    Event::dispatch();
}
```

Ziel dieser Funktion ist es, einige Rahmen wie `amp` zu unterstützen, die den Loop der `reactor` intern selbst kontrollieren und daher nicht überlassen können, wenn Sie `Event::wait` verwenden, da die Swoole-Unterseite das Kontrollrecht behält.
