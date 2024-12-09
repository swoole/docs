# Funktionenliste

Neben Funktionen zur Netzwerkkommunikation bietet Swoole auch einige Funktionen zur Gewinnung von Systeminformationen an, die von PHP-Programmen verwendet werden können.


## swoole_set_process_name()

Diese Funktion wird zum Einstellen des Namens des Prozesses verwendet. Nachdem der Prozessname geändert wurde, wird im Ergebnis der `ps`-Befehl nicht mehr `php your_file.php` gezeigt, sondern der festgelegte String.

Diese Funktion akzeptiert einen String-Parameter.

Diese Funktion ist identisch mit der in PHP 5.5 bereitgestellten Funktion [cli_set_process_title](https://www.php.net/manual/zh/function.cli-set-process-title.php). Der Unterschied ist, dass `swoole_set_process_name` für alle Versionen von PHP ab 5.2 verwendet werden kann, während `cli_set_process_title` nur für PHP 5.5 und höher verfügbar ist. Die Kompatibilität von `swoole_set_process_name` ist geringer als die von `cli_set_process_title`. Wenn die Funktion `cli_set_process_title` vorhanden ist, sollte sie bevorzugt verwendet werden.

```php
function swoole_set_process_name(string $name): void
```

Beispiel für die Verwendung:

```php
swoole_set_process_name("swoole server");
```


### Wie man den Namen jedes Swoole Servers umbenennt <!-- {docsify-ignore} -->

* Beim Aufrufen von [onStart](/server/events?id=onstart) wird der Name des Hauptkerns geändert
* Beim Aufrufen von [onManagerStart](/server/events?id=onmanagerstart) wird der Name des Verwaltungskerns (`manager`) geändert
* Beim Aufrufen von [onWorkerStart](/server/events?id=onworkerstart) wird der Name des Worker-Kerns geändert
 
!> Unteren Linux-Kernel und Mac OSX werden Prozessnamen nicht unterstützt  


## swoole_strerror()

Diese Funktion wandelt einen Fehlercode in eine Fehlermeldung um.

Funktionsprototyp:

```php
function swoole_strerror(int $errno, int $error_type = 1): string
```

Fehlertypen:

* `1`: Standard-`Unix Errno`, verursacht durch systemcall-Fehler wie `EAGAIN`, `ETIMEDOUT` usw.
* `2`: Fehlercodes von `getaddrinfo`, verursacht durch DNS-Operationen
* `9`: Swoole-Unterrichtsfehler, kann mit `swoole_last_error()` erhalten werden

Beispiel für die Verwendung:

```php
var_dump(swoole_strerror(swoole_last_error(), 9));
```


## swoole_version()

Diese Funktion gibt die Version von Swoole an, wie zum Beispiel `1.6.10`.

```php
function swoole_version(): string
```

Beispiel für die Verwendung:

```php
var_dump(SWOOLE_VERSION); // Die globale Variable SWOOLE_VERSION zeigt ebenfalls die Swoole-Version an
var_dump(swoole_version());
/**
Rückkehrwerte:
string(6) "1.9.23"
string(6) "1.9.23"
**/
```


## swoole_errno()

Diese Funktion gibt den letzten systemcall-Fehlercode zurück, ähnlich wie das `errno`-Variable in `C/C++`.

```php
function swoole_errno(): int
```

Die Werte der Fehlercodes hängen vom Betriebssystem ab. Sie können mit `swoole_strerror` in Fehlermeldungen umgewandelt werden.


## swoole_get_local_ip()

Diese Funktion wird zum Abrufen der IP-Adressen aller lokalen Netzwerkschnittstellen verwendet.

```php
function swoole_get_local_ip(): array
```

Beispiel für die Verwendung:

```php
// Abrufen der IP-Adressen aller lokalen Netzwerkschnittstellen
$list = swoole_get_local_ip();
print_r($list);
/**
Rückkehrwerte
Array
(
      [eno1] => 10.10.28.228
      [br-1e72ecd47449] => 172.20.0.1
      [docker0] => 172.17.0.1
)
**/
```

!> Hinweis
* Derzeit werden nur IPv4-Adressen zurückgegeben, und lokale Loop-Adressen wie 127.0.0.1 werden gefiltert.
* Das zurückgegebene Array ist ein assoziiertes Array, dessen Schlüssel die Schnittstellennamen sind. Zum Beispiel `array("eth0" => "192.168.1.100")`
* Diese Funktion ruft rechtzeitig die `ioctl`-Systemanforderung aus, um Schnittstelleninformationen zu erhalten, und hat keine unteren Ebenen-Speicher.


## swoole_clear_dns_cache()

Diese Funktion löscht den internen DNS-Cache von Swoole, der für `swoole_client` und `swoole_async_dns_lookup` wirksam ist.

```php
function swoole_clear_dns_cache()
```


## swoole_get_local_mac()

Diese Funktion wird zum Abrufen der MAC-Adresse der lokalen Netzwerkschnittstellen verwendet.

```php
function swoole_get_local_mac(): array
```

* Bei erfolgreicher Rückkehr werden alle Netzwerkschnittstellen MAC-Adressen zurückgegeben

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

Diese Funktion wird zum Abrufen der Anzahl der CPU-Kerne des lokalen Systems verwendet.

```php
function swoole_cpu_num(): int
```

* Bei erfolgreicher Rückkehr wird die Anzahl der CPU-Kerne zurückgegeben, zum Beispiel:

```shell
php -r "echo swoole_cpu_num();"
```


## swoole_last_error()

Diese Funktion gibt den letzten Fehlercode von Swoole zurück.

```php
function swoole_last_error(): int
```

Möglich ist die Umwandlung des Fehlers in eine Fehlermeldung mit `swoole_strerror(swoole_last_error(), 9)`. Eine vollständige Liste der Fehlercodes finden Sie unter [Swoole Fehlercode-Liste](/other/errno?id=swoole).


## swoole_mime_type_add()

Diese Funktion fügt neue MIME-Typen zur internen MIME-Typentabelle hinzu.

```php
function swoole_mime_type_add(string $suffix, string $mime_type): bool
```


## swoole_mime_type_set()

Diese Funktion ändert einen bestimmten MIME-Typ, und wenn dies fehlschlägt (da er nicht existiert), wird `false` zurückgegeben.

```php
function swoole_mime_type_set(string $suffix, string $mime_type): bool
```


## swoole_mime_type_delete()

Diese Funktion löscht einen bestimmten MIME-Typ, und wenn dies fehlschlägt (da er nicht existiert), wird `false` zurückgegeben.

```php
function swoole_mime_type_delete(string $suffix): bool
```


## swoole_mime_type_get()

Diese Funktion gibt den MIME-Typ zurück, der einem Dateinamen entspricht.

```php
function swoole_mime_type_get(string $filename): string
```


## swoole_mime_type_exists()

Diese Funktion überprüft, ob ein bestimmter Suffix einem MIME-Typ zugeordnet ist.

```php
function swoole_mime_type_exists(string $suffix): bool
```


## swoole_substr_json_decode()

Zero-copy JSON-Deserialisierung, abgesehen von `$offset` und `$length`, sind die anderen Parameter identisch mit [json_decode](https://www.php.net/manual/en/function.json-decode.php).

!> Swoole Version >= `v4.5.6` verfügbar, ab Version `v4.5.7` muss beim编译en die Option [--enable-swoole-json](/environment?id=allgemeine-Parameter) angegeben werden. Referenz für Nutzungsszenarien siehe [Swoole 4.5.6 Unterstützung für Zero-copy JSON oder PHP-Deserialisierung](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_json_decode(string $packet, int $offset, int $length, bool $assoc = false, int $depth = 512, int $options = 0)
```

  * **Beispiel**

```php
$val = json_encode(['hello' => 'swoole']);
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(json_decode(substr($str, 4, $l), true));
var_dump(swoole_substr_json_decode($str, 4, $l, true));
```


## swoole_substr_unserialize()

Zero-copy PHP-Deserialisierung, abgesehen von `$offset` und `$length`, sind die anderen Parameter identisch mit [unserialize](https://www.php.net/manual/en/function.unserialize.php).

!> Swoole Version >= `v4.5.6` verfügbar. Referenz für Nutzungsszenarien siehe [Swoole 4.5.6 Unterstützung für Zero-copy JSON oder PHP-Deserialisierung](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_unserialize(string $packet, int $offset, int $length, array $options= [])
```

  * **Beispiel**

```php
$val = serialize('hello');
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(unserialize(substr($str, 4, $l)));
var_dump(swoole_substr_unserialize($str, 4, $l));
```


## swoole_error_log()

Diese Funktion gibt Fehlermeldungen in das Log aus. `$level` ist ein [Log-Level](/consts?id=log-level).

!> Swoole Version >= `v4.5.8` verfügbar

```php
function swoole_error_log(int $level, string $msg)
```
## swoole_clear_error()

Entfernt Fehler oder den letzten Fehlercode des Sockets.

!> Swoole Version >= `v4.6.0` verfügbar

```php
function swoole_clear_error()
```

## swoole_coroutine_socketpair()

Die koordinierten Version von [socket_create_pair](https://www.php.net/manual/en/function.socket-create-pair.php).

!> Swoole Version >= `v4.6.0` verfügbar

```php
function swoole_coroutine_socketpair(int $domain , int $type , int $protocol): array|bool
```

## swoole_async_set

Diese Funktion kann Optionen für asynchrone `IO`-Aktivitäten festlegen.

```php
function swoole_async_set(array $settings)
```

- `enable_signalfd`: Schaltet die Verwendung der `signalfd`-Funktion ein und aus

- `enable_coroutine`: Schaltet die Verwendung built-in Coroutinen ein und aus, [siehe](/server/setting?id=enable_coroutine)

- `aio_core_worker_num`: Legt die minimale Anzahl von AIO-Arbeitsprozessen fest

- `aio_worker_num`: Legt die maximale Anzahl von AIO-Arbeitsprozessen fest


## swoole_error_log_ex()

Schreibt ein Log mit einer bestimmten Ebene und einem Fehlercode.

```php
function swoole_error_log_ex(int $level, int $error, string $msg)
```

!> Swoole Version >= `v4.8.1` verfügbar

## swoole_ignore_error()

Ignoriert Fehlerlogs für die angegebene Fehlercode.

```php
function swoole_ignore_error(int $error)
```

!> Swoole Version >= `v4.8.1` verfügbar
