# Swoole\Server\Task

Hier ist eine detaillierte Einführung in die Klasse `Swoole\Server\Task`. Diese Klasse ist sehr einfach, aber man kann keinen `Task`-Objekt durch `new Swoole\Server\Task()` erhalten, da dieses Objekt keine Informationen über den Server enthält und die Ausführung jeglicher Methode von `Swoole\Server\Task` zu einem tödlichen Fehler führt.

```shell
Invalid instance of Swoole\Server\Task in /home/task.php on line 3
```

## Eigenschaften


### $data
Die durch den `worker`-Prozess an den `task`-Prozess übertragene Daten `data`, diese Eigenschaft ist ein `string`-Typ.

```php
Swoole\Server\Task->data
```


### $dispatch_time
Die Zeit, zu der diese Daten zum `task`-Prozess gelangt sind `dispatch_time`, diese Eigenschaft ist ein `double`-Typ.

```php
Swoole\Server\Task->dispatch_time
```


### $id
Die Zeit, zu der diese Daten zum `task`-Prozess gelangt sind `dispatch_time`, diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server\Task->id
```


### $worker_id
Der Identifikator des `worker`-Prozesses, von dem diese Daten stammen, diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server\Task->worker_id
```


### $flags
Einige Flags für diese asynchrone Aufgabe `flags`, diese Eigenschaft ist ein `int`-Typ.

```php
Swoole\Server\Task->flags
```

?> Die Rückkehrwerte von `flags` sind die folgenden Typen:  
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCK bedeutet, dass dies nicht von einem `Worker`-Prozess an den `task`-Prozess gesendet wurde, und wenn in der `onTask`-Ereignis `Swoole\Server::finish()` aufgerufen wird, wird eine Warnung ausgegeben.  
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCK bedeutet, dass der letzte Rückruffunktionsparameter in `Swoole\Server::finish()` nicht null ist, die `onFinish`-Ereignis wird nicht ausgeführt, sondern nur dieser Rückruffunktionsparameter. 
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCK bedeutet, dass die Aufgabe durch eine Coroutine bearbeitet wird. 
  - SW_TASK_NONBLOCK ist der Standardwert, wenn keine der oben genannten drei Fälle zutrifft.


## Methoden


### finish()

Gebraucht, um den `Worker`-Prozess im [Task-Prozess](/learn?id=taskworkerprozess) zu informieren, dass die übergebene Aufgabe abgeschlossen ist. Diese Funktion kann Ergebnisdaten an den `Worker`-Prozess übergeben.

```php
Swoole\Server\Task->finish(mixed $data): bool
```

  * **Parameter**

    * `mixed $data`

      * Funktion: Das Ergebnis der Aufgabebehandlung
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Hinweise**
    * Die `finish`-Methode kann mehrmals aufgerufen werden, der `Worker`-Prozess wird mehrmals das [onFinish](/server/events?id=onfinish)-Ereignis auslösen
    * Wenn in der [onTask](/server/events?id=ontask)-Rückruffunktion die `finish`-Methode aufgerufen wurde, wird das `return`-Daten immer noch das [onFinish](/server/events?id=onfinish)-Ereignis auslösen
    * `Swoole\Server\Task->finish` ist optional. Wenn der `Worker`-Prozess sich nicht um das Ergebnis der Aufgabe kümmert, ist es nicht notwendig, diese Funktion aufzurufen
    * Im [onTask](/server/events?id=ontask)-Rückruffunktionsparameter `return` ein String ist gleichbedeutend damit, die `finish`-Methode aufzurufen

  * **Wichtig**

  !> Um die `Swoole\Server\Task->finish`-Funktion zu verwenden, muss für den `Server` ein [onFinish](/server/events?id=onfinish)-Rückruffunktionsparameter festgelegt werden. Diese Funktion kann nur im [onTask](/server/events?id=ontask)-Rückruf der [Task-Prozess](/learn?id=taskworkerprozess) verwendet werden



### pack()

Die gegebenen Daten serialisieren.

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

  * **Parameter**

    * `mixed $data`

      * Funktion: Das Ergebnis der Aufgabebehandlung
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückkehrwert**
    * Bei erfolgreicher调用返回序列isierten Daten. 


### unpack()

Die gegebenen Daten deserialisieren.

```php
Swoole\Server\Task->unpack(string $data): mixed
```

  * **Parameter**

    * `string $data`

      * Funktion: Die Daten, die deserialisiert werden müssen
      * Standardwert: Keine
      * Andere Werte: Keine

  * **Rückkehrwert**
    * Bei erfolgreicher调用返回deserialisierte Daten. 

## Beispiel für die Verwendung
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
