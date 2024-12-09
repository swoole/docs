# Swoole\Server\Port

Hier ist eine detaillierte Beschreibung von `Swoole\Server\Port`.

## Eigenschaften


### $host
Gibt die IP-Adresse des angehörten Hosts zurück, diese Eigenschaft ist ein `string`.

```php
Swoole\Server\Port->host
```


### $port
Gibt den angehörten Host-Port zurück, diese Eigenschaft ist ein `int`.

```php
Swoole\Server\Port->port
```


### $type
Gibt den Typ der Servergruppe zurück. Diese Eigenschaft ist ein Enumerationswert und kann `SWOOLE_TCP`, `SWOOLE_TCP6`, `SWOOLE_UDP`, `SWOOLE_UDP6`, `SWOOLE_UNIX_DGRAM` oder `SWOOLE_UNIX_STREAM` sein.

```php
Swoole\Server\Port->type
```


### $sock
Gibt das angehörten Socket zurück, diese Eigenschaft ist ein `int`.

```php
Swoole\Server\Port->sock
```


### $ssl
Gibt an, ob SSL-Verschlüsselung aktiviert ist, diese Eigenschaft ist ein `bool`.

```php
Swoole\Server\Port->ssl
```


### $setting
Gibt die Einstellungen für diesen Port zurück, diese Eigenschaft ist ein `array`.

```php
Swoole\Server\Port->setting
```


### $connections
Gibt alle Verbindungen zurück, die zu diesem Port verbunden sind, diese Eigenschaft ist ein Iterator.

```php
Swoole\Server\Port->connections
```


## Methoden


### set() 

Wird verwendet, um verschiedene Parameter für den Betrieb von `Swoole\Server\Port` einzustellen, die Verwendung ist gleich wie bei [Swoole\Server->set()](/server/methods?id=set).

```php
Swoole\Server\Port->set(array $setting): void
```


### on() 

Wird verwendet, um Callbacks für `Swoole\Server\Port` einzustellen, die Verwendung ist gleich wie bei [Swoole\Server->on()](/server/methods?id=on).

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```


### getCallback() 

Gibt den eingestellten Callback zurück.

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

  * **Parameter**

    * `string $name`

      * Funktion: Name des Callback-Events
      * Standardwert: Keiner
      * Andere Werte: Keiner

  * **Rückkehrwert**

    * Gibt den Callback zurück, wenn die Operation erfolgreich ist, gibt es `null`, wenn der Callback nicht existiert.


### getSocket() 

Konvertiert das aktuelle Socket `fd` in ein PHP `Socket` Objekt.

```php
Swoole\Server\Port->getSocket(): Socket|false
```

  * **Rückkehrwert**

    * Gibt ein `Socket` Objekt zurück, wenn die Operation erfolgreich ist, gibt es `false`, wenn die Operation fehlschlägt.

!> Hinweis: Diese Funktion kann nur verwendet werden, wenn beim编译en von `Swoole` die Option `--enable-sockets` aktiviert wurde.
