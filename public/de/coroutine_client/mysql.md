# Coroutine\MySQL

Kürzervariable MySQL-Client.

!> Dieser Client wird nicht mehr empfohlen. Es wird empfohlen, die Kombination aus `Swoole\Runtime::enableCoroutine` und `pdo_mysql` oder `mysqli` zu verwenden, also den nativen `MySQL`-Client [ein Klick für die Kürzervariable](/runtime).  
!> Ab `Swoole 6.0` wurde dieser kürzervariable MySQL-Client entfernt


## Beispielverwendung

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    var_dump($res);
});
```


## defer-Feature

Bitte beziehen Sie sich auf die [Kapitel zur parallelen Client](/coroutine/multi_call).


## Stored Procedures

Seit der Version `4.0.0` werden MySQL-Stored Procedures und die Retrieval mehrerer Ergebnisse unterstützt.


## MySQL8.0

`Swoole-4.0.1` oder höher unterstützen alle Sicherheitsvalidierungsfähigkeiten von `MySQL8`. Sie können den Client direkt normal verwenden, ohne auf die Passwortumgebung zurückzugehen.


### Unterversionen ab 4.0.1

`MySQL-8.0` verwendet standardmäßig den sichereren `caching_sha2_password` Plugin. Wenn Sie von `5.x` aufgerüstet wurden, können Sie alle MySQL-Funktionen direkt verwenden. Wenn ein neuer MySQL-Server eingerichtet wurde, müssen Sie im MySQL-Befehlsbuch folgende Aktionen ausführen, um die Kompatibilität zu gewährleisten:

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

Ersetzen Sie in den Anweisungen `'root'@'localhost'` mit Ihrem eigenen Benutzer und `password` mit Ihrem Passwort.

Wenn Sie es immer noch nicht verwenden können, sollten Sie in der my.cnf die Einstellung `default_authentication_plugin = mysql_native_password` vornehmen.


## Eigenschaften


### serverInfo

Verbindungsinformationen,保存的是传递给连接函数的数组。


### sock

Der Dateideskriptor, der für die Verbindung verwendet wird.


### connected

Ob eine Verbindung zum MySQL-Server hergestellt wurde.

!> Referenz[connected Eigenschaft und Verbindungsstatus unkonform](/question/use?id=connected属性和连接状态不一致)


### connect_error

Fehlerinformationen beim Ausführen des `connect`-Befehls zur Verbindung mit dem Server.


### connect_errno

Fehlercode beim Ausführen des `connect`-Befehls zur Verbindung mit dem Server, Typ als Integer.


### error

Fehlerinformationen, die vom Server zurückgegeben werden, wenn ein MySQL-Befehl ausgeführt wird.


### errno

Fehlercode, der vom Server zurückgegeben wird, wenn ein MySQL-Befehl ausgeführt wird, Typ als Integer.


### affected_rows

Anzahl der betroffenen Zeilen.


### insert_id

Der `id` des letztes Inserted Records.


## Methoden


### connect()

Eine MySQL-Verbindung herstellen.

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

!> `$serverInfo`：Parameter werden als Array übergeben

```php
[
    'host'        => 'MySQL IP-Adresse', // Wenn es sich um eine lokale UNIX Socket handelt, sollte es in der Form `unix://tmp/your_file.sock` angegeben werden
    'user'        => 'Datenbenutzer',
    'password'    => 'Datenbankpasswort',
    'database'    => 'Datenbankname',
    'port'        => 'MySQL Port Standard 3306 Optional Parameter',
    'timeout'     => 'Verbindungszeitüberschreitung', // beeinflusst nur die Verbindungszeitüberschreitung, nicht die query und execute Methoden, siehe `Client-Zeitüberschreitungsvorschriften`
    'charset'     => 'Zeichencodierung',
    'strict_type' => false, // strict Mode aktivieren, die von query zurückgelieferten Daten werden auch zu starkem Typ umgewandelt
    'fetch_mode'  => true,  // fetch Mode aktivieren, kann wie bei pdo row für row oder alle Ergebnisse im einmaligen Aufruf verwenden (ab Version 4.0)
]
```


### query()

Eine SQL-Befehl ausführen.

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **Parameter** 

    * **`string $sql`**
      * **Funktion**：SQL-Befehl
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`float $timeout`**
      * **Funktion**：Zeitüberschreitung 【Wenn der MySQL-Server innerhalb der festgelegten Zeit keine Daten zurückgibt, wird der untere Layer `false` zurückgeben, einen Fehlercode von `110` setzen und die Verbindung trennen】
      * **Einheit der Werte**：Sekunden,最小ene Genauigkeit von Millisekunden (`0.001` Sekunde)
      * **Standardwert**：`0`
      * **Andere Werte**：Keine
      * **Referenz[Client-Zeitüberschreitungsvorschriften](/coroutine_client/init?id=Zeitüberschreitungsvorschriften)**


  * **Rückgabewert**

    * Bei Timeout/Fehler `false` zurückgeben, sonst `array` Form des Abfrageergebnisses zurückgeben

  * **Verzögerte Empfang**

  !> Wenn `defer` festgelegt ist, wird der Aufruf von `query` direkt `true` zurückgeben. Um das Ergebnis zu empfangen, muss `recv` aufgerufen werden.

  * **Beispiel**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('show tables');
    if ($res === false) {
        return;
    }
    var_dump($res);
});
```


### prepare()

Eine SQL- Vorverarbeitungsanfrage an den MySQL-Server senden.

!> `prepare` muss mit `execute` kombiniert werden. Nach erfolgreicher Vorverarbeitungsanfrage muss die `execute` Methode verwendet werden, um Datenparameter an den MySQL-Server zu senden.

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **Parameter** 

    * **`string $sql`**
      * **Funktion**：Vorverarbeitungsvorschlag 【Verwenden Sie `?` als Platzhalter für Parameter】
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

    * **`float $timeout`**
      * **Funktion**：Zeitüberschreitung 
      * **Einheit der Werte**：Sekunden,最小ene Genauigkeit von Millisekunden (`0.001` Sekunde)
      * **Standardwert**：`0`
      * **Andere Werte**：Keine
      * **Referenz[Client-Zeitüberschreitungsvorschriften](/coroutine_client/init?id=Zeitüberschreitungsvorschriften)**


  * **Rückgabewert**

    * Bei Misserfolg `false` zurückgeben, können Sie `$db->error` und `$db->errno` überprüfen, um den Grund des Fehlers zu bestimmen
    * Bei Erfolg `Coroutine\MySQL\Statement` Objekt zurückgeben, das [execute](/coroutine_client/mysql?id=statement-gtexecute) Methode des Objekts aufrufen, um Parameter zu senden

  * **Beispiel**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10));
        var_dump($ret2);
    }
});
```


### escape()

Spezielle Zeichen in SQL-Befehlen escapen, um SQL-Angriffe zu vermeiden. Der untere Layer basiert auf Funktionen von `mysqlnd` und benötigt die `mysqlnd` Erweiterung von PHP.

!> Beim编译 müssen [--enable-mysqlnd](/environment?id=编译选项) aktiviert werden, um dies zu ermöglichen.

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

  * **Parameter** 

    * **`string $str`**
      * **Funktion**：Escapierte Zeichen
      * **Standardwert**：Kein
      * **Andere Werte**：Keine

  * **Beispielverwendung**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $data = $db->escape("abc'efg\r\n");
});
```
### begin()

Eine Transaktion starten. Wird in Kombination mit `commit` und `rollback` zur Umsetzung des Transaktionsmanagements in MySQL verwendet.

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> Startet eine MySQL-Transaktion,返回`true` bei Erfolg, `false` bei Misserfolg. Bitte überprüfen Sie `$db->errno`, um den Fehlercode zu erhalten.
  
!> Auf derselben MySQL-Verbindungsobjekt kann nur eine Transaktion gleichzeitig gestartet werden;  
Es muss darauf gewartet werden, dass die vorherige Transaktion mit `commit` oder `rollback` beendet wird, bevor eine neue Transaktion gestartet werden kann;  
Sonst wird ein `Swoole\MySQL\Exception`-Exception mit dem Code `21` aus der unteren Ebene geworfen.

  * **Beispiel**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```


### commit()

Transaktion committen. 

!> Muss in Kombination mit `begin` verwendet werden.

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> Returniert `true` bei Erfolg, `false` bei Misserfolg. Bitte überprüfen Sie `$db->errno`, um den Fehlercode zu erhalten.


### rollback()

Transaktion zurückziehen.

!> Muss in Kombination mit `begin` verwendet werden.

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> Returniert `true` bei Erfolg, `false` bei Misserfolg. Bitte überprüfen Sie `$db->errno`, um den Fehlercode zu erhalten.


### Statement->execute()

SQL-Vorverarbeitung mit Parametern an den MySQL-Server senden.

!> `execute` muss in Kombination mit `prepare` verwendet werden, bevor `execute` aufgerufen werden kann, muss zuerst eine Vorverarbeitungsanfrage mit `prepare` gestartet werden.

!> Die `execute`-Methode kann wiederholt aufgerufen werden.

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **Parameter** 

    * **`array $params`**
      * **Funktion**: Vorverarbeitungsparamter 【Muss die Anzahl der Parameter mit der `prepare`-Anweisung übereinstimmen. `$params` muss ein array mit numerischen Indizes sein, die Reihenfolge der Parameter muss der der `prepare`-Anweisung entsprechen】
      * **Standardwert**: Kein
      * **Andere Werte**: Kein

    * **`float $timeout`**
      * **Funktion**: Zeitüberschreitung 【Wenn der MySQL-Server innerhalb der festgelegten Zeit keine Daten zurücksendet, wird unten `false` zurückgegeben, der Fehlercode wird auf `110` gesetzt und die Verbindung wird getrennt】
      * **Einheit**: Sekunde, die Mindestsicherheit beträgt Millisekunden (0,001 Sekunden)
      * **Standardwert**: `-1`
      * **Andere Werte**: Kein
      * **Referenz[Client-Zeitüberschreitungsvorschriften](/coroutine_client/init?id=Zeitüberschreitungsvorschriften)**

  * **Rückgabewert** 

    * Bei Erfolg wird `true` zurückgegeben, wenn das `fetch_mode`-Parameter für `connect` auf `true` gesetzt ist
    * Bei Erfolg wird ein Array mit Datensätzen zurückgegeben, wenn es nicht oben genannte Umstände sind,
    * Bei Misserfolg wird `false` zurückgegeben, man kann den Fehler durch `$db->error` und `$db->errno` bestimmen

  * **Verwendungsbeispiel** 

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=? and name=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10, 'rango'));
        var_dump($ret2);

        $ret3 = $stmt->execute(array(13, 'alvin'));
        var_dump($ret3);
    }
});
```


### Statement->fetch()

Die nächste Zeile aus dem Ergebnisset abrufen.

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> Swoole-Version >= `4.0-rc1`, muss beim `connect` die Option `fetch_mode => true` hinzugefügt werden

  * **Beispiel** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> Ab der neuen MySQL-Treiber-Version `v4.4.0` muss `fetch` verwendet werden, um bis zu `NULL` zu lesen, sonst kann keine neue Anfrage gestartet werden (aufgrund des unterenlevels按需读取-Mechanismus kann Speicher gespart werden)


### Statement->fetchAll()

Ein Array zurückgeben, das alle Zeilen aus dem Ergebnisset enthält.

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> Swoole-Version >= `4.0-rc1`, muss beim `connect` die Option `fetch_mode => true` hinzugefügt werden

  * **Beispiel** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```

### Statement->nextResult()

In einem mehrresponderergebnisses-Handle zum nächsten Ergebnis der Antwort vorantreiben (z.B. bei einer multiresultat returning Stored Procedure).

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

  * **Rückgabewert**

    * Bei Erfolg wird `TRUE` zurückgegeben
    * Bei Misserfolg wird `FALSE` zurückgegeben
    * Wenn kein weiteres Ergebnis vorhanden ist, wird `NULL` zurückgegeben

  * **Beispiel** 

    * **Ohne fetch-Modus**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **Mit fetch-Modus**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> Ab der neuen MySQL-Treiber-Version `v4.4.0` muss `fetch` verwendet werden, um bis zu `NULL` zu lesen, sonst kann keine neue Anfrage gestartet werden (aufgrund des unterenlevels按需读取-Mechanismus kann Speicher gespart werden)
