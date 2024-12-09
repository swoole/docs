# Datentypen
Hier sind die Datentypen aufgelistet, die zwischen Threads übergeben und geteilt werden können.


## Grundtypen
Variablen der Typen `null/bool/int/float` mit einer Größe von weniger als `16 Bytes` werden als Werte übergeben.


## Strings
Bei Strings wird eine **Speicherduplikation** vorgenommen, sie werden in `ArrayList`, `Queue`, `Map` abgelegt.


## Socket-Ressourcen



### Unterstützte Typenliste

- `Co\Socket`

- `PHP Stream`
- `PHP Socket(ext-sockets)`, muss mit `--enable-sockets` kompilierungsoption aktiviert werden



### Nicht unterstützte Typen

- `Swoole\Client`

- `Swoole\Server`

- `Swoole\Coroutine\Client`

- `Swoole\Coroutine\Http\Client`

- `pdo` Verbindung

- `redis` Verbindung
- andere spezielle `Socket` Ressourcentypen


### Ressourcenreplikation



- Bei Schreiben wird eine `dup(fd)` Operation durchgeführt, um die Ressource von der ursprünglichen zu trennen, ohne sich gegenseitig zu beeinflussen. Die Schließung der ursprünglichen Ressource beeinträchtigt die neue Ressource nicht.

- Bei Lesen wird eine `dup(fd)` Operation durchgeführt, um innerhalb des Lesebahns `VM` eine neue `Socket` Ressource zu erstellen.
- Bei Löschung wird eine `close(fd)` Operation durchgeführt, um die Dateideskriptor zu freisetzen.


Dies bedeutet, dass eine `Socket` Ressource drei Referenzzählungen hat:

- Die Thread, in dem die `Socket` Ressource ursprünglich erstellt wurde

- Die Containers `ArrayList`, `Queue`, `Map`

- Der Thread, der den `ArrayList`, `Queue`, `Map` Containers liest

Wenn keine Thread oder Container die Ressource hält, wird die Referenzzählung auf `0` reduziert und die `Socket` Ressource wird wirklich freigesetzt. Wenn die Referenzzählung nicht `0` ist,
 selbst wenn die `close` Operation ausgeführt wird, wird die Verbindung nicht geschlossen und es wird keine Auswirkungen auf andere Threads oder Datencontainer, die die `Socket` Ressource halten.


Wenn Sie die Referenzzählung ignorieren und die `Socket` direkt schließen möchten, können Sie die `shutdown()` Methode verwenden, zum Beispiel:

- `stream_socket_shutdown()`

- `Socket::shutdown()`
- `socket_shutdown()`

> Die `shutdown` Operation wird alle `Socket` Ressourcen beeinflussen, die von allen Threads gehalten werden, und ist nach der Ausführung nicht mehr verwendbar und kann keine `read/write` Operationen ausführen.


## Arrays
Verwenden Sie `array_is_list()` um den Typ des Arrays zu bestimmen. Wenn es ein numerisches Indexarray ist, wird es in `ArrayList` umgewandelt, wenn es ein assoziiertes Indexarray ist, wird es in `Map` umgewandelt.



- Es wird das gesamte Array durchlaufen und die Elemente in `ArrayList` oder `Map` eingefügt
- Es werden mehrdimensionale Arrays unterstützt, die rekursiv durchlaufen werden, um zu einer嵌套en Struktur von `ArrayList` oder `Map` zu werden

Beispiel:
```php
$array = [
    'a' => random_int(1, 999999999999999999),
    'b' => random_bytes(128),
    'c' => uniqid(),
    'd' => time(),
    'e' => [
        'key' => 'value',
        'hello' => 'world',
    ];
];

$map = new Map($array);

// $map['e'] ist ein neuer Map-Objekt, das zwei Elemente enthält, key und hello, mit den Werten 'value' und 'world'
var_dump($map['e']);
```


## Objekte

### Thread-Ressourcen-Objekte

`Thread\Lock`, `Thread\Atomic`, `Thread\ArrayList`, `Thread\Map` und andere Thread-Ressourcen-Objekte können direkt in `ArrayList`, `Queue`, `Map` gelagert werden.
Diese Operation ist nur die Speicherung einer Referenz zum Objekt im Container, keine Kopie des Objekts wird durchgeführt.

Wenn ein Objekt in `ArrayList` oder `Map` geschrieben wird, wird nur die Referenz zur Thread-Ressource um ein erhöht, ohne eine Kopie zu erstellen. Wenn die Referenzzählung des Objekts auf `0` liegt, wird es freigesetzt.

Beispiel:

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // Derzeit ist die Referenzzählung 1
$map['lock'] = $lock; // Die Referenzzählung ist jetzt 2
unset($map['lock']); // Die Referenzzählung ist jetzt 1
unset($lock); // Die Referenzzählung ist jetzt 0, das Lock-Objekt wird freigesetzt
```

Unterstützte Listen:



- `Thread\Lock`

- `Thread\Atomic`

- `Thread\Atomic\Long`

- `Thread\Barrier`

- `Thread\ArrayList`

- `Thread\Map`
- `Thread\Queue`

Bitte beachten Sie, dass `Thread`-Objekte nicht serialisierbar und nicht übertragbar sind und nur im Elternthread verfügbar sind.

### Normaler PHP-Objekt
Wird bei der Schreibung automatisch serialisiert und bei der Lektüre wieder deserialisiert. Bitte beachten Sie, dass, wenn das Objekt einen nicht serialisierbaren Typ enthält, eine Ausnahme抛出 wird.
