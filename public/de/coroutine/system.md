# Coroutine\System

Die Coroutine-Abstraktion für systembezogene `APIs`. Diese Module sind ab der offiziellen Version `v4.4.6` verfügbar. Die meisten `APIs` basieren auf dem AIO-Threadpool.

!> Für Versionen vor `v4.4.6` wird die Verwendung des Kurznamens `Co` oder `Swoole\Coroutine` empfohlen, wie zum Beispiel `Co::sleep` oder `Swoole\Coroutine::sleep`.  
Ab der Version `v4.4.6` wird offiziell **empfohlen**, `Co\System::sleep` oder `Swoole\Coroutine\System::sleep` zu verwenden.  
Diese Änderung zielt darauf ab, die Namensräume zu standardisieren, sichert aber gleichzeitig die Abwärtskompatibilität (d.h., es ist auch nach der Version `v4.4.6` möglich, die vorherige Schreibweise zu verwenden, keine Änderungen sind erforderlich).


## Methoden


### statvfs()

Holt Informationen über das Dateisystem.

!> Available in Swoole-Versionen >= v4.2.5

```php
Swoole\Coroutine\System::statvfs(string $path): array|false
```

  * **Parameter** 

    * **`string $path`**
      * **Funktion**: Der Mountpunkt des Dateisystems (z.B. `/`, kann mit `df` und `mount -l` Commands erhalten werden)
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Beispiel**

    ```php
    Swoole\Coroutine\run(function () {
        var_dump(Swoole\Coroutine\System::statvfs('/'));
    });
    ```
  * **Ausgabe-Beispiel**
    
    ```php
    array(11) {
      ["bsize"]=>
      int(4096)
      ["frsize"]=>
      int(4096)
      ["blocks"]=>
      int(61068098)
      ["bfree"]=>
      int(45753580)
      ["bavail"]=>
      int(42645728)
      ["files"]=>
      int(15523840)
      ["ffree"]=>
      int(14909927)
      ["favail"]=>
      int(14909927)
      ["fsid"]=>
      int(1002377915335522995)
      ["flag"]=>
      int(4096)
      ["namemax"]=>
      int(255)
    }
    ```


### fread()

Liest Dateien asynchron in Coroutine-Weise.

```php
Swoole\Coroutine\System::fread(resource $handle, int $length = 0): string|false
```

!> In Versionen unter `v4.0.4` unterstützt die `fread` Methode keine nicht-dateitypischen `streams`, wie `STDIN` oder `Socket`. Bitte verwenden Sie die `fread` Methode nicht für solche Ressourcen.  
In Versionen ab `v4.0.4` unterstützt die `fread` Methode auch nicht-dateitypische `stream` Ressourcen. Der Boden会自动 basierend auf dem `stream` Typ wählen, ob der AIO-Threadpool oder die [EventLoop](/learn?id=什么是eventloop) verwendet wird.

!> Diese Methode wurde in der `5.0` Version abgeschafft und ist in der `6.0` Version entfernt worden

  * **Parameter** 

    * **`resource $handle`**
      * **Funktion**: Ein Dateihand (muss ein durch `fopen` geöffnetes dateitypisches `stream` Ressource sein)
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $length`**
      * **Funktion**: Die Länge des zu lesenden Daten (Standard ist `0`, was bedeutet, den gesamten Inhalt der Datei zu lesen)
      * **Standardwert**: `0`
      * **Andere Werte**: Keiner

  * **Rückgabetyp** 

    * Bei erfolgreicher Lese返回String-Inhalt, bei Fehlgeschlagen返回`false`

  * **Beispiel**  

    ```php
    $fp = fopen(__FILE__, "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fread($fp);
        var_dump($r);
    });
    ```


### fwrite()

Schreibt Daten asynchron in eine Datei.

```php
Swoole\Coroutine\System::fwrite(resource $handle, string $data, int $length = 0): int|false
```

!> In Versionen unter `v4.0.4` unterstützt die `fwrite` Methode keine nicht-dateitypischen `streams`, wie `STDIN` oder `Socket`. Bitte verwenden Sie die `fwrite` Methode nicht für solche Ressourcen.  
In Versionen ab `v4.0.4` unterstützt die `fwrite` Methode auch nicht-dateitypische `stream` Ressourcen. Der Boden会自动 basierend auf dem `stream` Typ wählen, ob der AIO-Threadpool oder die [EventLoop](/learn?id=什么是eventloop) verwendet wird.

!> Diese Methode wurde in der `5.0` Version abgeschafft und ist in der `6.0` Version entfernt worden

  * **Parameter** 

    * **`resource $handle`**
      * **Funktion**: Ein Dateihand (muss ein durch `fopen` geöffnetes dateitypisches `stream` Ressource sein)
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`string $data`**
      * **Funktion**: Die zu schreibenden Dateninhalte (können Text oder Binärdaten sein)
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $length`**
      * **Funktion**: Die Länge des zu schreibenden Daten (Standard ist `0`, was bedeutet, den gesamten Inhalt von `$data` zu schreiben, `$length` muss kleiner als die Länge von `$data` sein)
      * **Standardwert**: `0`
      * **Andere Werte**: Keiner

  * **Rückgabetyp** 

    * Bei erfolgreicher Schreiboperation返回die Länge der geschriebenen Daten, bei Fehlgeschlagen返回`false`

  * **Beispiel**  

    ```php
    $fp = fopen(__DIR__ . "/test.data", "a+");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fwrite($fp, "hello world\n", 5);
        var_dump($r);
    });
    ```


### fgets()

Liest den Inhalt einer Datei asynchron Zeile für Zeile.

Der Boden verwendet einen `php_stream` Puffer, der standardmäßig eine Größe von `8192` Byte hat. Die Größe des Puffers kann mit `stream_set_chunk_size` festgelegt werden.

```php
Swoole\Coroutine\System::fgets(resource $handle): string|false
```

!> Die `fgets` Funktion kann nur für dateitypische `stream` Ressourcen verwendet werden, Swoole-Version >= `v4.4.4` verfügbar

!> Diese Methode wurde in der `5.0` Version abgeschafft und ist in der `6.0` Version entfernt worden

  * **Parameter** 

    * **`resource $handle`**
      * **Funktion**: Ein Dateihand (muss ein durch `fopen` geöffnetes dateitypisches `stream` Ressource sein)
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Rückgabetyp** 

    * Wenn eine `EOL` (`\r` oder `\n`) erreicht wird, wird eine Zeile mit der `EOL` zurückgegeben
    * Wenn keine `EOL` erreicht wurde, aber die Länge des Inhalts die `php_stream` Puffergröße von `8192` Byte überschreitet, wird ein Stück von `8192` Byte Daten ohne `EOL` zurückgegeben
    * Wenn das Ende der Datei (`EOF`) erreicht wird, wird eine leere Zeichenfolge zurückgegeben, die `feof`-Funktion kann verwendet werden, um zu überprüfen, ob die Datei bereits gelesen wurde
    * Bei Fehlgeschlagen returns `false`, die [swoole_last_error](/functions?id=swoole_last_error) Funktion kann verwendet werden, um die Fehlercode zu erhalten

  * **Beispiel**  

    ```php
    $fp = fopen(__DIR__ . "/defer_client.php", "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fgets($fp);
        var_dump($r);
    });
    ```


### readFile()

Liest eine Datei asynchron.

```php
Swoole\Coroutine\System::readFile(string $filename): string|false
```

  * **Parameter** 

    * **`string $filename`**
      * **Funktion**: Der Dateiname
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Rückgabetyp** 

    * Bei erfolgreicher Lese返回String-Inhalt, bei Fehlgeschlagen returns `false`, die [swoole_last_error](/functions?id=swoole_last_error) Funktion kann verwendet werden, um Fehlerinformationen zu erhalten
    * Die `readFile` Methode hat keine Größebeschränkung, der gelesene Inhalt wird im Speicher gespeichert, daher kann beim Lesen einer sehr großen Datei zu viel Speicher verbraucht werden

  * **Beispiel**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $r = Swoole\Coroutine\System::readFile($filename);
        var_dump($r);
    });
    ```
### writeFile()

Kobaltweise Schreiben an eine Datei.

```php
Swoole\Coroutine\System::writeFile(string $filename, string $fileContent, int $flags): bool
```

  * **Parameter** 

    * **`string $filename`**
      * **Funktion**: Der Name der Datei【Muss schreibbar sein, wird automatisch erstellt, wenn die Datei nicht existiert. Ein Versuch, die Datei zu öffnen, führt sofort zum Rückkehren von `false`】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`string $fileContent`**
      * **Funktion**: Der Inhalt, der in die Datei geschrieben wird【Maximal 4M】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $flags`**
      * **Funktion**: Die Optionen für das Schreiben【Standardmäßig wird der aktuelle Inhalt der Datei gelöscht, kann mit `FILE_APPEND` angegeben werden, um den Inhalt am Ende der Datei anzufügen】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Rückkehrwert** 

    * Erfolgreich zurückkehrt `true`
    * thấtglos zurückkehrt `false`

  * **Beispiel für die Verwendung**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $w = Swoole\Coroutine\System::writeFile($filename, "hello swoole!");
        var_dump($w);
    });
    ```


### sleep()

In einen Wartezustand eintreten.

Entsprechend der `PHP`-Funktion `sleep`, unterscheidet sich `Coroutine::sleep` darin, dass es von einem [Kobalt-Scheduler](/coroutine?id=Kobalt-Scheduler) implementiert wird, der den aktuellen Kobalt unterbricht, um Zeitblöcke zu übergeben und ein asynchrones Timer hinzufügt. Wenn die Timeoutzeit erreicht ist, wird der aktuelle Kobalt wieder fortgesetzt und der Betrieb resumed.

Die Verwendung der `sleep`-Schnittstelle ermöglicht es bequem, Timeoutwarten zu implementieren.

```php
Swoole\Coroutine\System::sleep(float $seconds): void
```

  * **Parameter** 

    * **`float $seconds`**
      * **Funktion**: Die Dauer des Schlafes【Muss größer als `0` sein, darf nicht länger als einen Tag (86400 Sekunden)】
      * **Einheit für Werte**: Sekunden, minimaler Präzisionsgrad von Millisekunden (0,001 Sekunden)
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Beispiel für die Verwendung**  

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9502);

    $server->on('Request', function($request, $response) {
        //Warten Sie 200ms, dann senden Sie eine Antwort an den Browser
        Swoole\Coroutine\System::sleep(0.2);
        $response->end("<h1>Hallo Swoole!</h1>");
    });

    $server->start();
    ```


### exec()

Eine Shell-Befehl ausführen. Der Boden führt automatisch [Kobalt-Scheduler](/coroutine?id=Kobalt-Scheduler) durch.

```php
Swoole\Coroutine\System::exec(string $cmd): array
```

  * **Parameter** 

    * **`string $cmd`**
      * **Funktion**: Der zu ausführende `Shell`-Befehl
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Rückkehrwert**

    * 실패 führt zu `false` zurückkehren, Erfolg führt zu einem Array zurückkehren, das den Prozessstatuscode, das Signal und den Ausgabekontent enthält.

    ```php
    array(
        'code'   => 0,  // Prozessstatuscode
        'signal' => 0,  // Signal
        'output' => '', // Ausgabekontent
    );
    ```

  * **Beispiel für die Verwendung**  

    ```php
    Swoole\Coroutine\run(function() {
        $ret = Swoole\Coroutine\System::exec("md5sum ".__FILE__);
    });
    ```

  * **Hinweis**

  !> Wenn die Ausführung von Scriptbefehl zu lange dauert, wird dies zu einem Timeout führen und der Prozess wird beendet. In diesem Fall kann das Problem durch Erhöhen der [socket_read_timeout](/coroutine_client/init?id=Timeout-Regeln) gelöst werden.


### gethostbyname()

Ein Domainname in eine IP-Adresse umwandeln. Basierend auf einem synchronen Threadpool simuliert, führt der Boden automatisch [Kobalt-Scheduler](/coroutine?id=Kobalt-Scheduler) durch.

```php
Swoole\Coroutine\System::gethostbyname(string $domain, int $family = AF_INET, float $timeout = -1): string|false
```

  * **Parameter** 

    * **`string $domain`**
      * **Funktion**: Der Domainname
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $family`**
      * **Funktion**: Die Domainfamilie【`AF_INET` gibt eine IPv4-Adresse zurück, `AF_INET6` gibt eine IPv6-Adresse zurück】
      * **Standardwert**: `AF_INET`
      * **Andere Werte**: `AF_INET6`

    * **`float $timeout`**
      * **Funktion**: Die Timeoutzeit
      * **Einheit für Werte**: Sekunden, minimaler Präzisionsgrad von Millisekunden (0,001 Sekunden)
      * **Standardwert**: `-1`
      * **Andere Werte**: Keiner

  * **Rückkehrwert**

    * Erfolgreich gibt es die IP-Adresse zurück, die dem Domainname entspricht, fehlgeschlagen gibt es `false` zurück, und man kann [swoole_last_error](/functions?id=swoole_last_error) verwenden, um Fehlerinformationen zu erhalten

    ```php
    array(
        'code'   => 0,  // Prozessstatuscode
        'signal' => 0,  // Signal
        'output' => '', // Ausgabekontent
    );
    ```

  * **Erweiterung**

    * **Timeout-Steuerung**

      Der `$timeout`-Parameter kann die Wartezeit für das Kobalt-Warten steuern. Wenn keine Ergebnisse innerhalb der festgelegten Zeit zurückkehren, wird der Kobalt sofort `false` zurückkehren und den Rest der Ausführung fortsetzen. Im unteren Implementierungslevel wird diese asynchrone Aufgabe als `cancel` markiert, und `gethostbyname` wird weiterhin im AIO-Threadpool ausgeführt.
      
      Sie können die [DNS-Resolving-Timeout und -Wiederholungszeit](/learn_other?id=DNS-Resolving-Timeout-und-Wiederholungszeit) für `gethostbyname` und `getaddrinfo` durch Bearbeiten von `/etc/resolv.conf` anpassen. Weitere Informationen finden Sie unter [DNS-Resolving-Timeout und -Wiederholungszeit einrichten](/learn_other?id=DNS-Resolving-Timeout-und-Wiederholungszeit).

  * **Beispiel für die Verwendung**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::gethostbyname("www.baidu.com", AF_INET, 0.5);
        echo $ip;
    });
    ```


### getaddrinfo()

DNS-Abfrage durchführen, um die IP-Adresse des Domainnamens zu ermitteln.

Im Gegensatz zu `gethostbyname` unterstützt `getaddrinfo` mehr Parameter und gibt mehrere IP-Ergebnisse zurück.

```php
Swoole\Coroutine\System::getaddrinfo(string $domain, int $family = AF_INET, int $socktype = SOCK_STREAM, int $protocol = STREAM_IPPROTO_TCP, string $service = null, float $timeout = -1): array|false
```

  * **Parameter** 

    * **`string $domain`**
      * **Funktion**: Der Domainname
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`int $family`**
      * **Funktion**: Die Domainfamilie【`AF_INET` gibt eine IPv4-Adresse zurück, `AF_INET6` gibt eine IPv6-Adresse zurück】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner
      
      !> Weitere Parameter settings finden Sie im `man getaddrinfo` Handbuch

    * **`int $socktype`**
      * **Funktion**: Protokolttyp
      * **Standardwert**: `SOCK_STREAM`
      * **Andere Werte**: `SOCK_DGRAM`, `SOCK_RAW`

    * **`int $protocol`**
      * **Funktion**: Protokoll
      * **Standardwert**: `STREAM_IPPROTO_TCP`
      * **Andere Werte**: `STREAM_IPPROTO_UDP`, `STREAM_IPPROTO_STCP`, `STREAM_IPPROTO_TIPC`, `0`

    * **`string $service`**
      * **Funktion**:
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`float $timeout`**
      * **Funktion**: Die Timeoutzeit
      * **Einheit für Werte**: Sekunden, minimaler Präzisionsgrad von Millisekunden (0,001 Sekunden)
      * **Standardwert**: `-1`
      * **Andere Werte**: Keiner

  * **Rückkehrwert**

    * Erfolgreich gibt es ein Array aus mehreren IP-Adressen zurück, fehlgeschlagen gibt es `false` zurück

  * **Beispiel für die Verwendung**  

    ```php
    Swoole\Coroutine\run(function () {
        $ips = Swoole\Coroutine\System::getaddrinfo("www.baidu.com");
        var_dump($ips);
    });
    ```
### dnsLookup()

Domainname-Adress-Abfrage.

Im Gegensatz zu `Coroutine\System::gethostbyname` wird `Coroutine\System::dnsLookup` direkt über das UDP-Netzwerk kommunizieren implementiert, anstatt die von `libc` bereitgestellte `gethostbyname` Funktion zu verwenden.

!> Swoole Version >= `v4.4.3` verfügbar, das untere Level liest `/etc/resolve.conf`, um die Adresse des DNS-Servers zu erhalten, derzeit werden nur DNS-Namensauflösungen für `AF_INET(IPv4)` unterstützt. Swoole Version >= `v4.7` ermöglicht die Verwendung des dritten Parameters, um `AF_INET6(IPv6)` zu unterstützen.

```php
Swoole\Coroutine\System::dnsLookup(string $domain, float $timeout = 5, int $type = AF_INET): string|false
```

  * **Parameter** 

    * **`string $domain`**
      * **Funktion**：Domäne
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

    * **`float $timeout`**
      * **Funktion**：Zeitüberschreitung
      * **Einheit**：Sekunden,最小er Präzisionsbereich ist Millisekunden (0,001 Sekunden)
      * **Standardwert**：`5`
      * **Andere Werte**：Kein

    * **`int $type`**
        * **Einheit**：Sekunden,最小er Präzisionsbereich ist Millisekunden (0,001 Sekunden)
        * **Standardwert**：`AF_INET`
        * **Andere Werte**：`AF_INET6`

    !> Der `$type` Parameter ist in Swoole Version >= `v4.7` verfügbar.

  * **Rückkehrwert**

    * Bei erfolgreicher Auflösung wird die entsprechende IP-Adresse zurückgegeben
    * Bei Misserfolg wird `false` zurückgegeben, man kann [swoole_last_error](/functions?id=swoole_last_error) verwenden, um Fehlerinformationen zu erhalten

  * **Häufige Fehler**

    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED`：Dieses Domain-Namen konnte nicht aufgelöst werden, die Abfrage ist fehlgeschlagen
    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT`：Die Auflösung hat sich verzögert, der DNS-Server könnte fehlerhaft sein und konnte innerhalb der festgelegten Zeit keine Ergebnisse zurückgeben

  * **Beispiel**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::dnsLookup("www.baidu.com");
        echo $ip;
    });
    ```


### wait()

Entsprechen dem ursprünglichen [Process::wait](/process/process?id=wait), der Unterschied besteht darin, dass diese API eine Version für Coroutine ist und Coroutinen blockieren wird, sie kann die Funktionen `Swoole\Process::wait` und `pcntl_wait` ersetzen.

!> Swoole-Version >= `v4.5.0` verfügbar

```php
Swoole\Coroutine\System::wait(float $timeout = -1): array|false
```

* **Parameter** 

    * **`float $timeout`**
      * **Funktion**：Zeitüberschreitung, negative Zahlen bedeuten keine Zeitüberschreitung
      * **Einheit**：Sekunden, minimaler Präzisionsbereich ist Millisekunden (0,001 Sekunden)
      * **Standardwert**：`-1`
      * **Andere Werte**：Kein

* **Rückkehrwert**

  * Bei erfolgreicher Operation wird ein Array zurückgegeben, das die `PID` des Tochterprozesses, den Exitstatuscode und das Signal enthält, das zum `KILL` des Prozesses verwendet wurde
  * Bei Misserfolg wird `false` zurückgegeben

!> Nach dem Start jedes Tochterprozesses muss der Elternprozess eine Coroutine schicken, um `wait()` (oder `waitPid()`) aufzurufen, um sie zu sammeln, sonst wird der Tochterprozess zu einem Zombieprozess und verschwendet Systemressourcen für Prozesse.  
Wenn Coroutinen verwendet werden, muss zuerst ein Prozess erstellt werden, und dann innerhalb des Prozesses Coroutinen gestartet werden. Die Reihenfolge darf nicht umgekehrt werden, sonst ist die Situation mit dem Fork eines Prozesses mit Coroutinen sehr komplex und die Basis ist schwer zu handhaben.

* **Beispiel**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::wait();
    assert($status['pid'] === $process->pid);
    var_dump($status);
});
```


### waitPid()

Im Grunde genommen gleich wie die oben genannte wait-Methode, der Unterschied besteht darin, dass diese API spezifische Prozesse warten kann

!> Swoole-Version >= `v4.5.0` verfügbar

```php
Swoole\Coroutine\System::waitPid(int $pid, float $timeout = -1): array|false
```

* **Parameter** 

    * **`int $pid`**
      * **Funktion**：Prozess-ID
      * **Standardwert**：`-1` (bedeutet任意进程, entspricht in diesem Fall der wait-Methode)
      * **Andere Werte**：Jeder natürliche Zahl

    * **`float $timeout`**
      * **Funktion**：Zeitüberschreitung, negative Zahlen bedeuten keine Zeitüberschreitung
      * **Einheit**：Sekunden, minimaler Präzisionsbereich ist Millisekunden (0,001 Sekunden)
      * **Standardwert**：`-1`
      * **Andere Werte**：Kein

* **Rückkehrwert**

  * Bei erfolgreicher Operation wird ein Array zurückgegeben, das die `PID` des Tochterprozesses, den Exitstatuscode und das Signal enthält, das zum `KILL` des Prozesses verwendet wurde
  * Bei Misserfolg wird `false` zurückgegeben

!> Nach dem Start jedes Tochterprozesses muss der Elternprozess eine Coroutine schicken, um `wait()` (oder `waitPid()`) aufzurufen, um sie zu sammeln, sonst wird der Tochterprozess zu einem Zombieprozess und verschwendet Systemressourcen für Prozesse.

* **Beispiel**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::waitPid($process->pid);
    var_dump($status);
});
```


### waitSignal()

Coroutine-Version des Signal-Listener, der die aktuelle Coroutine blockiert, bis ein Signal ausgelöst wird, kann die Funktionen `Swoole\Process::signal` und `pcntl_signal` ersetzen.

!> Swoole-Version >= `v4.5.0` verfügbar

```php
Swoole\Coroutine\System::waitSignal(int $signo, float $timeout = -1): bool
```

  * **Parameter** 

    * **`int $signo`**
      * **Funktion**：Signal-Typ
      * **Standardwert**：Kein
      * **Andere Werte**：SIG-Serie von Konstanten, wie `SIGTERM`, `SIGKILL` usw.

    * **`float $timeout`**
      * **Funktion**：Zeitüberschreitung, negative Zahlen bedeuten keine Zeitüberschreitung
      * **Einheit**：Sekunden, minimaler Präzisionsbereich ist Millisekunden (0,001 Sekunden)
      * **Standardwert**：`-1`
      * **Andere Werte**：Kein

  * **Rückkehrwert**

    * Bei Empfang eines Signals wird `true` zurückgegeben
    * Bei Timeout ohne Empfang eines Signals wird `false` zurückgegeben

  * **Beispiel**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    Coroutine\run(function () {
        $bool = System::waitSignal(SIGUSR1);
        var_dump($bool);
    });
});
$process->start();
sleep(1);
$process::kill($process->pid, SIGUSR1);
```

### waitEvent()

Coroutine-Version des Signal-Listener, der die aktuelle Coroutine blockiert, bis ein Signal ausgelöst wird. Wartet auf IO-Ereignisse, kann die Funktionen der `swoole_event`-Serie ersetzen.

!> Swoole-Version >= `v4.5` verfügbar

```php
Swoole\Coroutine\System::waitEvent(mixed $socket, int $events = SWOOLE_EVENT_READ, float $timeout = -1): int | false
```

* **Parameter** 

    * **`mixed $socket`**
      * **Funktion**：Dateideskriptor (jeder Typ, der zu einem fd transformiert werden kann, wie Socket-Objekt, Ressource usw.)
      * **Standardwert**：Kein
      * **Andere Werte**：Kein

    * **`int $events`**
      * **Funktion**：Ereignistyp
      * **Standardwert**：`SWOOLE_EVENT_READ`
      * **Andere Werte**：`SWOOLE_EVENT_WRITE` oder `SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE`

    * **`float $timeout`**
      * **Funktion**：Zeitüberschreitung, negative Zahlen bedeuten keine Zeitüberschreitung
      * **Einheit**：Sekunden, minimaler Präzisionsbereich ist Millisekunden (0,001 Sekunden)
      * **Standardwert**：`-1`
      * **Andere Werte**：Kein

* **Rückkehrwert**

  * Gibt die Summe der ausgelösten Ereignistypen zurück (möglicherweise mehrere Bits), abhängig vom Wert des Parameters `$events`
  * Bei Misserfolg wird `false` zurückgegeben, man kann [swoole_last_error](/functions?id=swoole_last_error) verwenden, um Fehlerinformationen zu erhalten

* **Beispiel**

> Synchron blockierendes Code kann durch diese API zu einer coroutinen-non-blockierenden Transformation werden

```php
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = stream_socket_client('tcp://www.qq.com:80', $errno, $errstr, 30);
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
    assert($events === SWOOLE_EVENT_WRITE);
    fwrite($client, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ);
    assert($events === SWOOLE_EVENT_READ);
    $response = fread($client, 8192);
    echo $response;
});
```
