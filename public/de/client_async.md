# Swoole\Async\Client

`Swoole\Async\Client` wird im Folgenden als `Client` bezeichnet und ist ein asynchroner, nicht blockierender Netzwerkclient für `TCP/UDP/UnixSocket`. Bei asynchronen Clients müssen Ereignis-Rückruffunktionen festgelegt werden, anstatt synchron zu warten.



- Asynchrone Clients sind eine Unterklasse von `Swoole\Client` und können einige Methoden des synchronen blockierenden Clients aufrufen  
- Nur in Version `6.0` oder höher verfügbar



## Vollständiges Beispiel

```php
$cli = new Swoole\Async\Client(SWOOLE_SOCK_TCP);

$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());
    $client->send(RandStr::gen(1024, RandStr::ALL));
});

$client->on("receive", function(Swoole\Async\Client $client, string $data){
    $recv_len = strlen($data);
    $client->send(RandStr::gen(1024, RandStr::ALL));
    $client->close();
    Assert::false($client->isConnected());
});

$client->on("error", function(Swoole\Async\Client $client) {
    echo "error";
});

$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});

$client->connect("127.0.0.1", 9501, 0.2);
```


## Methoden

In dieser Seite werden nur die Methoden aufgeführt, die sich von `Swoole\Client` unterscheiden. Für Methoden, die nicht von der Subklasse geändert wurden, siehe [Synchroner blockierender Client](client.md).


### __construct()

Konstruktormethod, siehe Elternklasse

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> Der zweite Parameter des asynchronen Clients muss `true` sein


### on()

Registriert einen Ereignis-Rückruffunktionshandler für den `Client`.

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> Wenn `on`-Methode wiederholt aufgerufen wird, wird der vorherige Einstellung überschrieben

  * **Parameter**

    * `string $event`

      * Funktion: Name des Rückrufereignisses, case-insensitive
      * Standardwert: Keiner
      * Andere Werte: Keiner

    * `callable $callback`

      * Funktion: Rückruffunktion
      * Standardwert: Keiner
      * Andere Werte: Keiner

      !> Kann ein String mit dem Namen einer Funktion sein, eine statische Methode einer Klasse, ein Array von Objektmethoden, eine anonyme Funktion Referenz[dieser Abschnitt](/learn?id=Several ways to set up callback functions).
  
  * **Rückgabewert**

    * Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist.



### isConnected()
Bestimmt, ob der aktuelle Client bereits eine Verbindung zum Server aufgebaut hat.

```php
Swoole\Async\Client->isConnected(): bool
```

* Gibt `true` zurück, wenn verbunden, gibt `false` zurück, wenn nicht verbunden


### sleep()
Horstet vorübergehend das Empfang von Daten an. Nach dem Aufrufen wird der Client aus dem Ereigniskreislauf entfernt und löst keine Datenempfangseigenschaften mehr aus, es sei denn, die `wakeup()`-Methode wird aufgerufen, um das Empfang wieder zu aktivieren.

```php
Swoole\Async\Client->sleep(): bool
```

* Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist


### wakeup()
Reaktiviert das Empfang von Daten und wird dem Ereigniskreislauf hinzugefügt.

```php
Swoole\Async\Client->wakeup(): bool
```

* Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist



### enableSSL()
Dynamisch SSL/TLS-Verschlüsselung aktivieren, die normalerweise für `startTLS`-Clients verwendet wird. Nach dem Herstellen der Verbindung wird zuerst Klartextdaten gesendet, bevor die verschlüsselte Übertragung aktiviert wird.

```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* Diese Funktion kann nur nach einem erfolgreichen `connect` aufgerufen werden
* Bei asynchronen Clients muss ein `$callback` festgelegt werden, der nach Abschluss des SSL-Handshake-Prozesses aufgerufen wird
* Gibt `true` zurück, wenn die Operation erfolgreich ist, gibt `false` zurück, wenn die Operation fehlgeschlagen ist


## Rückrufereignisse


### connect
Triggers nach dem Herstellen einer Verbindung. Wenn `HTTP`- oder `Socks5`-Agenten sowie `SSL`-Tunneling-Verschlüsselung festgelegt sind, wird dies nach Abschluss des Agenten-Handshake und des SSL-Verschlüsselungs-Handshakes ausgelöst.

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

Nach dem Auslösen dieses Ereignisereignisses wird `isConnected()` aufgerufen und wird `true` zurückgeben



### error 
Wird ausgelöst, wenn das Herstellen der Verbindung fehlschlägt. Sie können die Fehlermeldung durch Abrufen von `$client->errCode` erhalten.
```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```



- Bitte beachten Sie, dass sowohl `connect` als auch `error` nur eines der beiden Ereignisse auslösen werden, entweder das Herstellen der Verbindung ist erfolgreich oder es ist fehlgeschlagen, es kann nur eine Schlussfolgerung geben

- `Client::connect()` kann direkt `false` zurückgeben, was bedeutet, dass die Verbindung fehlgeschlagen ist. In diesem Fall wird der `error` Rückruf nicht ausgeführt. Bitte überprüfen Sie den Rückgabewert von `connect`

- Das `error` Ereignis ist ein asynchroner Ausdruck, es gibt eine gewisse `IO`-Wartetzeit von dem Zeitpunkt, an dem die Verbindung gestartet wurde, bis zum Auslösen des `error` Ereignisses

- Ein fehlgeschlagener `connect` bedeutet sofortige Fehlschläge, dieser Fehler wird direkt vom Betriebssystem ausgelöst, ohne jegliche `IO`-Wartetzeit dazwischen


### receive
Wird ausgelöst, nachdem Daten empfangen wurden

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```



- Wenn kein bestimmtes Protokoll festgelegt ist, wie `EOF` oder `LENGTH`, ist die maximale zurückgegebene Datenlänge `64K`

- Wenn Protokollparameter festgelegt sind, ist die maximale Datenlänge die vom `package_max_length` Parameter festgelegte, der standardmäßig bei `2M` liegt
- `$data` ist immer nicht leer, wenn ein Systemfehler oder eine Verbindungsschließung empfangen wurde, wird das `close` Ereignis ausgelöst

### close
Wird ausgelöst, wenn die Verbindung geschlossen wird

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```
