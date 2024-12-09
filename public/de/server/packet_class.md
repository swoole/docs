# Swoole\Server\Packet

Hier ist eine detaillierte Beschreibung von `Swoole\Server\Packet`.

## Eigenschaften


### $server_socket
Gibt den Dateideskriptor `fd` des Serversevers zurück, dieser Eigenschaft ist ein Integer der `int` Art.

```php
Swoole\Server\Packet->server_socket
```


### $server_port
Gibt den an dem Server lauschten Port `server_port` zurück, dieser Eigenschaft ist ein Integer der `int` Art.

```php
Swoole\Server\Packet->server_port
```


### $dispatch_time
Gibt die Ankunftszeit der Anfragedaten `dispatch_time` zurück, diese Eigenschaft ist ein Double der `double` Art.

```php
Swoole\Server\Packet->dispatch_time
```


### $address
Gibt die Adresse des Clients `address` zurück, diese Eigenschaft ist ein String der `string` Art.

```php
Swoole\Server\Packet->address
```


### $port
Gibt den an dem Client lauschten Port `port` zurück, diese Eigenschaft ist ein Integer der `int` Art.

```php
Swoole\Server\Packet->port
```

### $data
Gibt die übertragene Daten des Clients `data` zurück, diese Eigenschaft ist ein String der `string` Art.

```php
Swoole\Server\Packet->data
```
