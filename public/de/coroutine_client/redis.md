# Coroutine Redis Client

!> Dieser Client wird nicht mehr empfohlen. Es wird empfohlen, die Verwendung von `Swoole\Runtime::enableCoroutine + phpredis` oder `predis` zu nutzen, also die native PHP `redis` Client mit einem Klick zu coroutinisieren.

!> Nach Swoole 6.0 wurde dieser coroutinierte Redis Client entfernt.


## Beispiel usage

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});
```

!> `subscribe` und `pSubscribe` können nicht für Situationen verwendet werden, in denen `defer(true)` aufgerufen wird.


## Methoden

!> Die Verwendung der Methoden ist im Großen und Ganzen mit [phpredis](https://github.com/phpredis/phpredis) konsistent.

Die folgenden Erklärungen unterscheiden sich von der Implementierung von [phpredis](https://github.com/phpredis/phpredis):

1.尚未实现的Redis命令：`scan object sort migrate hscan sscan zscan`；

2.Die Verwendung von `subscribe` und `pSubscribe` erfordert keine Einstellung einer Rückruffunktion;

3.Unterstützung für die Serialisierung von PHP-Variablen, wenn der dritte Parameter der `connect()` Methode auf `true` festgelegt ist, wird die Eigenschaft zur automatischen Serialisierung von PHP-Variablen aktiviert, standardmäßig ist sie deaktiviert.


### __construct()

Der Konstruktor des Redis-Coroutine-Clients ermöglicht es, Konfigurationsoptionen für die Verbindung zum `Redis` festzulegen, die identisch mit den Parametern der `setOptions()` Methode sind.

```php
Swoole\Coroutine\Redis::__construct(array $options = null);
```


### setOptions()

Seit Version 4.2.10 gibt es diese Methode neu, die verwendet wird, um einige Konfigurationen des `Redis` Clients nach dem Konstrukt und der Verbindung zu setzen

Diese Funktion ist im Swoole-Stil und muss durch ein Array aus `Schlüssel-Wert-Paaren` konfiguriert werden

```php
Swoole\Coroutine\Redis->setOptions(array $options): void
```

  * **Konfigurierbare Optionen**


Schlüssel | Beschreibung
---|---
`connect_timeout` | Die Timeoutzeit für die Verbindung, standardmäßig ist es die globale Coroutine `socket_connect_timeout` (1 Sekunde)
`timeout` | Die Timeoutzeit, standardmäßig ist es die globale Coroutine `socket_timeout`, siehe [Client-Timeout-Regeln](/coroutine_client/init?id=Timeout-Regeln)
`serialize` | Automatische Serialisierung, standardmäßig ausgeschaltet
`reconnect` | Anzahl der automatischen Verbindungversuche, wenn die Verbindung aufgrund von Timeouts usw. normal geschlossen wird, wird beim nächsten Request automatisch versucht, sich zu verbinden und dann den Request zu senden, standardmäßig ist es `1` Mal (`true`), wenn es einmal fehlschlägt, wird nicht weiter versucht, man muss manually eine Verbindung wiederherstellen. Dieses Mechanismus wird nur für die Verbindungsbewahrung verwendet und führt nicht zu Problemen mit nicht idempotenten Schnittstellen, da die Requests nicht erneut gesendet werden.
`compatibility_mode` | Eine Kompatibilitätslösung für die Rückkehrwerte der Funktionen `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore`, die nicht mit `php-redis` übereinstimmen. Wenn diese Einstellung aktiviert ist, sind die Rückkehrwerte von `Co\Redis` und `php-redis` identisch, standardmäßig ist sie ausgeschaltet 【Diese Konfigurationsoption ist in Versionen `v4.4.0` oder höher verfügbar】


### set()

Speichere Daten ab.

```php
Swoole\Coroutine\Redis->set(string $key, mixed $value, array|int $option): bool
```

  * **Parameter** 

    * **`string $key`**
      * **Funktion**: Der Schlüssel für die Daten
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`string $value`**
      * **Funktion**: Der Inhalt der Daten【Wenn der Typ nicht ein String ist, wird er automatisch serialisiert】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

    * **`string $options`**
      * **Funktion**: Optionen
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

      !> Beschreibung von `$option`:  
      `ganzer Integer`: Legt die Verfallszeit fest, zum Beispiel `3600`  
      `Array`: Advanced Verfallszeit-Einstellungen, wie `['nx', 'ex' => 10]` oder `['xx', 'px' => 1000]`

      !> `px`: Gibt die Verfallszeit in Millisekunden an  
      `ex`: Gibt die Verfallszeit in Sekunden an  
      `nx`: Gibt an, dass die Verfallszeit nur festgelegt wird, wenn das Element nicht existiert  
      `xx`: Gibt an, dass die Verfallszeit nur festgelegt wird, wenn das Element bereits existiert


### request()

Sende ein benutzerdefiniertes Befehl an den Redis-Server. Ähnlich wie bei phpredis der rawCommand.

```php
Swoole\Coroutine\Redis->request(array $args): void
```

  * **Parameter** 

    * **`array $args`**
      * **Funktion**: Eine Parameterliste, die immer ein Array-Format sein muss. 【Der erste Element muss ein Redis-Befehl sein, die anderen Elemente sind die Parameter des Befehls, die von unten automatisch zu einem Redis-Protokoll-Request verpackt werden, um gesendet zu werden.】
      * **Standardwert**: Keiner
      * **Andere Werte**: Keiner

  * **Rückgabewert** 

Vermutlich wird je nach Behandlungsweise des Befehls durch den Redis-Server eine Zahl, ein Boolean, ein String, ein Array oder andere Typen zurückgegeben.

  * **Beispiel usage** 

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379); // Wenn es sich um eine lokale UNIX Socket handelt, sollte der Host-Parameter im Format `unix://tmp/your_file.sock` angegeben werden
    $res = $redis->request(['object', 'encoding', 'key1']);
    var_dump($res);
});
```


## Eigenschaften


### errCode

Fehlercode.


Fehlercode | Beschreibung
---|---
1 | Fehler beim Lesen oder Schreiben
2 | Alles andere...
3 | Ende des Dateis
4 | Protokollfehler
5 | Out of memory


### errMsg

Fehlermeldung.


### connected

Bestimmt, ob der aktuelle `Redis` Client eine Verbindung zum Server aufgebaut hat.


## Konstanten

Für die Methode `multi($mode)` verwendet, standardmäßig im `SWOOLE_REDIS_MODE_MULTI` Modus:

* SWOOLE_REDIS_MODE_MULTI
* SWOOLE_REDIS_MODE_PIPELINE

Für die Bestimmung des Rückgabewertes der `type()` Methode verwendet:

* SWOOLE_REDIS_TYPE_NOT_FOUND
* SWOOLE_REDIS_TYPE_STRING
* SWOOLE_REDIS_TYPE_SET
* SWOOLE_REDIS_TYPE_LIST
* SWOOLE_REDIS_TYPE_ZSET
* SWOOLE_REDIS_TYPE_HASH


## Transaktionmodus

Möglich macht die Verwendung von `multi` und `exec` den Transaktionmodus für `Redis`.

  * **Hinweis**

    * Verwende den `multi` Befehl, um eine Transaktion zu starten, danach werden alle Befehle in eine Warteschlange eingefügt und warten auf Ausführung
    * Verwende den `exec` Befehl, um alle Operationen in der Transaktion auszuführen und alle Ergebnisse gleichzeitig zurückzugeben

  * **Beispiel usage**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key3', 'rango');
    $redis->get('key1');
    $redis->get('key2');
    $redis->get('key3');

    $result = $redis->exec();
    var_dump($result);
});
```


## Abonnementmodus

!> Swoole Version >= v4.2.13 verfügbar, **4.2.12 und niedrigere Versionen haben BUGs im Abonnementmodus**


### Abonnement

Anders als bei phpredis sind `subscribe/psubscribe` coroutinestyle.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // Oder verwenden psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg ist ein Array, das folgende Informationen enthält
            // $type # Rückgabetyp: Anzeigung des erfolgreichen Abonnements
            // $name # Abonnement-Kanalname oder Quellkanalname
            // $info  # Anzahl der derzeit abonnierten Kanäle oder Informationsinhalt
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') { // Oder psubscribe
                // Kanalsubscription erfolgreich, je mehr Kanäle abonniert sind, desto mehr Nachrichten
            } else if ($type == 'unsubscribe' && $info == 0){ // Oder punsubscribe
                break; // Empfangen Sie eine Abonnement-Entfernungsmeldung und wenn die Anzahl der abonnierten Kanäle 0 ist, beenden Sie den Empfang nicht weiter, beenden Sie den Loop
            } else if ($type == 'message') {  // Wenn es sich um psubscribe handelt, ist dies hier pmessage
                var_dump($name); // Drucken Sie den Namen des Quellkanals
                var_dump($info); // Drucken Sie die Nachricht
                // balabalaba.... // Behandeln Sie die Nachricht
                if ($need_unsubscribe) { // Unter bestimmten Umständen ist eine Abonnemententfernung erforderlich
                    $redis->unsubscribe(); // Warten Sie weiterhin auf die Abonnemententfernung, um fertig zu sein
                }
            }
        }
    }
});
```
### Abonnement kündigen

Für das Kündigen wird `unsubscribe/punsubscribe` verwendet, `$redis->unsubscribe(['channel1'])`

In diesem Fall wird `$redis->recv()` eine Kündigungsnachricht erhalten. Wenn man mehrere Kanäle kündigt, wird man mehrere Nachrichten erhalten.
    
!> Hinweis: Nach dem Kündigen muss man weiterhin `recv()` bis zur letzten Kündigungsnachricht ( `$msg[2] == 0` ) warten, um den Abonnementmodus zu beenden

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // or use psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg ist ein Array, das folgende Informationen enthält
            // $type # Rückgabetyp: Show subscription success
            // $name # Abonnierter Kan姓名 oder Quellkan姓名
            // $info  # Die Anzahl der derzeit abonnierten Kanäle oder Informationsinhalt
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') // oder psubscribe
            {
                // Kanälakündigungserfolgsnachricht
            }
            else if ($type == 'unsubscribe' && $info == 0) // oder punsubscribe
            {
                break; // Die Kündigungsnachricht wurde erhalten, und die Anzahl der verbleibenden Kanäle für das Abonnement ist 0, es wird nicht mehr empfangen, breche den Loop
            }
            else if ($type == 'message') // wenn es psubscribe ist, hier ist pmessage
            {
                // Print Quellkan姓名
                var_dump($name);
                // Print Nachricht
                var_dump($info);
                // Behandle Nachricht
                if ($need_unsubscribe) // in einigen Fällen muss man sich abonnieren
                {
                    $redis->unsubscribe(); // Continue recv, um auf das Ende des Abonnements zu warten
                }
            }
        }
    }
});
```

## Kompatibilitätsmodus

Das Problem, dass die Rückgabewerte der `Co\Redis` Befehle `hmGet/hGetAll/zrange/zrevrange/zrangebyscore/zrevrangebyscore` nicht mit den Werten der `phpredis` Erweiterung übereinstimmen, wurde gelöst [#2529](https://github.com/swoole/swoole-src/pull/2529).

Um alte Versionen zu unterstützen, fügt man die Konfiguration `$redis->setOptions(['compatibility_mode' => true]);` hinzu, um sicherzustellen, dass die Rückgabetypen von `Co\Redis` und `phpredis` übereinstimmen.

!> Swoole-Version >= `v4.4.0` ist verfügbar

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->setOptions(['compatibility_mode' => true]);
    $redis->connect('127.0.0.1', 6379);

    $co_get_val = $redis->get('novalue');
    $co_zrank_val = $redis->zRank('novalue', 1);
    $co_hgetall_val = $redis->hGetAll('hkey');
    $co_hmget_val = $redis->hmGet('hkey', array(3, 5));
    $co_zrange_val = $redis->zRange('zkey', 0, 99, true);
    $co_zrevrange_val = $redis->zRevRange('zkey', 0, 99, true);
    $co_zrangebyscore_val = $redis->zRangeByScore('zkey', 0, 99, ['withscores' => true]);
    $co_zrevrangebyscore_val = $redis->zRevRangeByScore('zkey', 99, 0, ['withscores' => true]);
});
```
