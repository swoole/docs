# Coroutine\Channel

> Es wird empfohlen, zuerst die [Übersicht](/coroutine) zu betrachten, um einige grundlegende Konzepte von Coroutinen zu verstehen, bevor Sie diesen Abschnitt lesen.

Kanäle dienen zur Kommunikation zwischen Coroutinen und unterstützen sowohl mehr Produzenten- als auch mehr Verbraucher-Coroutinen. Der Boden implementiert automatisch den Wechsel und die Scheduling von Coroutinen.

## Implementierungsmechanismus

  * Kanäle ähneln in PHP Arrays und verbrauchen nur Memory, ohne andere zusätzliche Ressourcen. Alle Operationen sind Memory-Operationen, ohne IO-Verbrauch
  * Der Boden verwendet PHP Reference Counting, ohne Memory-Kopien. Selbst wenn große Strings oder Arrays übertragen werden, entsteht kein zusätzlicher Performance-Verbrauch
  * `channel` basiert auf Reference Counting und ist Zero-Copy

## Beispiel für die Verwendung

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;

run(function(){
    $channel = new Channel(1);
    Coroutine::create(function () use ($channel) {
        for($i = 0; $i < 10; $i++) {
            Coroutine::sleep(1.0);
            $channel->push(['rand' => rand(1000, 9999), 'index' => $i]);
            echo "{$i}\n";
        }
    });
    Coroutine::create(function () use ($channel) {
        while(1) {
            $data = $channel->pop(2.0);
            if ($data) {
                var_dump($data);
            } else {
                assert($channel->errCode === SWOOLE_CHANNEL_TIMEOUT);
                break;
            }
        }
    });
});
```

## Methoden


### __construct()

Konstruktor für den Kanal.

```php
Swoole\Coroutine\Channel::__construct(int $capacity = 1)
```

  * **Parameter** 

    * **`int $capacity`**
      * **Funktion**：Legt die Kapazität fest 【muss ein Integer sein, der größer oder gleich `1` ist】
      * **Standardwert**：`1`
      * **Andere Werte**：Keine

!> Der Boden verwendet PHP Reference Counting, um Variablen zu speichern, der Cache benötigt nur `$capacity * sizeof(zval)`字节 Memory, in PHP7 beträgt `zval` 16字节, zum Beispiel wenn `$capacity = 1024`, wird der `Channel` maximal 16K Memory benötigen

!> Wenn verwendet in einem `Server`, muss er nach dem [onWorkerStart](/server/events?id=onworkerstart) 创建


### push()

Schreibt Daten in den Kanal.

```php
Swoole\Coroutine\Channel->push(mixed $data, float $timeout = -1): bool
```

  * **Parameter** 

    * **`mixed $data`**
      * **Funktion**：push Daten 【kann jede Art von PHP-Variablen sein, einschließlich Anonymer Funktionen und Ressourcen】
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

      !> Um Missverständnisse zu vermeiden, geben Sie nicht `null` und `false` in den Kanal

    * **`float $timeout`**
      * **Funktion**：Legt die Timeoutzeit fest
      * **Einheit der Werte**：Sekunden 【unterstützt floating-point, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：`-1`
      * **Andere Werte**：Keine
      * **Versionseinfluss**：Swoole-Version >= v4.2.12

      !> Wenn der Kanal voll ist, wird die aktuelle Coroutine bei dem `push` Operation suspendieren, und wenn innerhalb der vereinbarten Zeit kein Verbraucher die Daten verbraucht, tritt ein Timeout auf. Der Boden setzt die aktuelle Coroutine wieder in Betrieb, und der `push` Aufruf gibt sofort `false` zurück, da das Schreiben fehlgeschlagen ist

  * **Rückgabewert**

    * Erfolgreich zurückgegeben `true`
    * Wenn der Kanal geschlossen ist, fehlgeschlagen zurückgegeben `false`, kann mit `$channel->errCode` der Fehlercode erhalten werden

  * **Erweiterungen**

    * **Kanal voll**

      * Automatisch die aktuelle Coroutine `yield`, nachdem andere Verbraucher-Coroutinen Daten verbraucht haben, kann der Kanal geschrieben werden, und die aktuelle Coroutine wird wieder `resume`
      * Wenn mehrere Produzenten-Coroutinen gleichzeitig `push`, führt der Boden automatisch eine Warteschlange durch und setzt diese Produzenten-Coroutinen nacheinander `resume`

    * **Kanal leer**

      * Automatisch eine Verbraucher-Coroutine wecken
      * Wenn mehrere Verbraucher-Coroutinen gleichzeitig `pop`, führt der Boden automatisch eine Warteschlange durch und setzt diese Verbraucher-Coroutinen nacheinander `resume`

!> `Coroutine\Channel` verwendet lokales Memory, und die Memory zwischen verschiedenen Prozessen ist isoliert. kann nur innerhalb desselben Prozesses in verschiedenen Coroutinen `push` und `pop` Operationen durchführen 


### pop()

Lese Daten aus dem Kanal.

```php
Swoole\Coroutine\Channel->pop(float $timeout = -1): mixed
```

  * **Parameter** 

    * **`float $timeout`**
      * **Funktion**：Legt die Timeoutzeit fest
      * **Einheit der Werte**：Sekunden 【unterstützt floating-point, wie `1.5` bedeutet `1s`+`500ms`】
      * **Standardwert**：`-1`【bedeutet nie Timeout】
      * **Andere Werte**：Keine
      * **Versionseinfluss**：Swoole-Version >= v4.0.3

  * **Rückgabewert**

    * Kann jede Art von PHP-Variablen sein, einschließlich Anonymer Funktionen und Ressourcen
    * Wenn der Kanal geschlossen ist, fehlgeschlagen zurückgegeben `false`

  * **Erweiterungen**

    * **Kanal voll**

      * Nachdem Daten mit `pop` verbraucht wurden, wird automatisch eine Produzenten-Coroutine geweckt, um neue Daten zu schreiben
      * Wenn mehrere Produzenten-Coroutinen gleichzeitig `push`, führt der Boden automatisch eine Warteschlange durch und setzt diese Produzenten-Coroutinen nacheinander `resume`

    * **Kanal leer**

      * Automatisch die aktuelle Coroutine `yield`, nachdem andere Produzenten-Coroutinen Daten produziert haben, kann der Kanal gelesen werden, und die aktuelle Coroutine wird wieder `resume`
      * Wenn mehrere Verbraucher-Coroutinen gleichzeitig `pop`, führt der Boden automatisch eine Warteschlange durch und setzt diese Verbraucher-Coroutinen nacheinander `resume`


### stats()

Erhalten Sie den Zustand des Kanals.

```php
Swoole\Coroutine\Channel->stats(): array
```

  * **Rückgabewert**

    Rückkehr ein Array, der Buffered Channel wird `4` Informationen beinhalten, der unbuffered Channel gibt `2` Informationen zurück
    
    - `consumer_num` Anzahl der Verbraucher, bedeutet, dass der Kanal leer ist, es gibt `N` Coroutinen, die auf andere Coroutinen warten, um die `push` Methode zu verwenden, um Daten zu produzieren
    - `producer_num` Anzahl der Produzenten, bedeutet, dass der Kanal voll ist, es gibt `N` Coroutinen, die auf andere Coroutinen warten, um die `pop` Methode zu verwenden, um Daten zu verbrauchen
    - `queue_num` Anzahl der Elemente im Kanal

```php
array(
  "consumer_num" => 0,
  "producer_num" => 1,
  "queue_num" => 10
);
```


### close()

Schließen Sie den Kanal. Und wecken Sie alle Coroutinen, die auf Lesen und Schreiben warten.

```php
Swoole\Coroutine\Channel->close(): bool
```

!> Wecken Sie alle Produzenten-Coroutinen, `push` gibt `false` zurück; Wecken Sie alle Verbraucher-Coroutinen, `pop` gibt `false` zurück


### length()

Erhalten Sie die Anzahl der Elemente im Kanal.

```php
Swoole\Coroutine\Channel->length(): int
```


### isEmpty()

Bestimmen Sie, ob der aktuelle Kanal leer ist.

```php
Swoole\Coroutine\Channel->isEmpty(): bool
```


### isFull()

Bestimmen Sie, ob der aktuelle Kanal voll ist.

```php
Swoole\Coroutine\Channel->isFull(): bool
```


## Eigenschaften


### capacity

Kapazität des Kanalbuffers.

Die im [Konstruktor](/coroutine/channel?id=__construct) festgelegte Kapazität wird in dieser Variable保存, aber wenn die festgelegte Kapazität kleiner als `1` ist, wird diese Variable gleich `1` sein

```php
Swoole\Coroutine\Channel->capacity: int
```
### errCode

Fehlercode abrufen.

```php
Swoole\Coroutine\Channel->errCode: int
```

  * **Rückgabe**


Wert | Konstante | Bedeutung
---|---|---

0 | SWOOLE_CHANNEL_OK | Standard erfolgreich
-1 | SWOOLE_CHANNEL_TIMEOUT | Timeout beim pop-Vorgang (Zeitüberschreitung)
-2 | SWOOLE_CHANNEL_CLOSED | Channel ist geschlossen, weitere Operationen am Channel sind nicht möglich
