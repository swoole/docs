# Prozess\Manager

Prozessmanager, basierend auf [Prozess\Pool](/process/process_pool) implementiert. Er kann mehrere Prozesse verwalten. Im Vergleich zu `Prozess\Pool` ist es sehr bequem, mehrere Prozesse zu erstellen, die unterschiedliche Aufgaben ausführen, und es ist möglich, zu steuern, ob jeder Prozess in einem Coroutine-Umwelt laufen soll.


## Versionsunterstützung

| Versionsnummer | Klassenname                          | Updateschilderung                                 |
| -------------- | ------------------------------------ | ------------------------------------------------ |
| v4.5.3         | Swoole\Process\ProcessManager        | -                                                 |
| v4.5.5         | Swoole\Process\Manager                | Umbenennung, ProcessManager als Synonym für Manager |

!> Ab Version `v4.5.3` verfügbar.


## Gebrauchsanweisung

```php
use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();

for ($i = 0; $i < 2; $i++) {
    $pm->add(function (Pool $pool, int $workerId) {
    });
}

$pm->start();
```


## Methoden


### __construct()

Konstruktor.

```php
Swoole\Process\Manager::__construct(int $ipcType = SWOOLE_IPC_NONE, int $msgQueueKey = 0);
```

* **Parameter**

  * **`int $ipcType`**
    * **Funktion**：Modus der Prozess间-Kommunikation, consistent mit `Process\Pool`s `$ipc_type`【Standardwert: `0`, bedeutet keine Nutzung jeglicher Prozess间-Kommunikationsmerkmale】
    * **Standardwert**：`0`
    * **Andere Werte**：Nicht vorhanden

  * **`int $msgQueueKey`**
    * **Funktion**：Schlüssel für die Nachrichtenschlange, consistent mit `Process\Pool`s `$msgqueue_key`
    * **Standardwert**：Nicht vorhanden
    * **Andere Werte**：Nicht vorhanden


### setIPCType()

Legt den Kommunikationsmodus zwischen Arbeitsprozessen fest.

```php
Swoole\Process\Manager->setIPCType(int $ipcType): self;
```

* **Parameter**

  * **`int $ipcType`**
    * **Funktion**：Modus der Prozess间-Kommunikation
    * **Standardwert**：Nicht vorhanden
    * **Andere Werte**：Nicht vorhanden


### getIPCType()

Holt den Kommunikationsmodus zwischen Arbeitsprozessen ab.

```php
Swoole\Process\Manager->getIPCType(): int;
```


### setMsgQueueKey()

Legt den Schlüssel für die Nachrichtenschlange fest.

```php
Swoole\Process\Manager->setMsgQueueKey(int $msgQueueKey): self;
```

* **Parameter**

  * **`int $msgQueueKey`**
    * **Funktion**：Schlüssel für die Nachrichtenschlange
    * **Standardwert**：Nicht vorhanden
    * **Andere Werte**：Nicht vorhanden


### getMsgQueueKey()

Holt den Schlüssel für die Nachrichtenschlange ab.

```php
Swoole\Process\Manager->getMsgQueueKey(): int;
```


### add()

Fügt einen Arbeitsprozess hinzu.

```php
Swoole\Process\Manager->add(callable $func, bool $enableCoroutine = false): self;
```

* **Parameter**

  * **`callable $func`**
    * **Funktion**：Der Rückruffunktions, der von diesem Prozess ausgeführt wird
    * **Standardwert**：Nicht vorhanden
    * **Andere Werte**：Nicht vorhanden

  * **`bool $enableCoroutine`**
    * **Funktion**: Ob für diesen Prozess ein Coroutine zum Ausführen des Rückruffunktions erstellt werden soll
    * **Standardwert**: false
    * **Andere Werte**: Nicht vorhanden


### addBatch()

Fügt mehreren Arbeitsprozessen hinzu.

```php
Swoole\Process\Manager->addBatch(int $workerNum, callable $func, bool $enableCoroutine = false): self;
```

* **Parameter**

  * **`int $workerNum`**
    * **Funktion**: Anzahl der zu hinzufügenden Prozesse
    * **Standardwert**: Nicht vorhanden
    * **Andere Werte**: Nicht vorhanden

  * **`callable $func`**
    * **Funktion**: Die Rückruffunktionen, die von diesen Prozessen ausgeführt werden
    * **Standardwert**: Nicht vorhanden
    * **Andere Werte**: Nicht vorhanden

  * **`bool $enableCoroutine`**
    * **Funktion**: Ob für diese Prozesse Coroutinen zum Ausführen der Rückruffunktionen erstellt werden sollen
    * **Standardwert**: Nicht vorhanden
    * **Andere Werte**: Nicht vorhanden

### start()

Startet die Arbeitsprozesse.

```php
Swoole\Process\Manager->start(): void;
```
