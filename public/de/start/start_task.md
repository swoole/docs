# Ausführen von Hintergrundaufgaben (Tasks)

In Server-Programmen sind langwierige Operationen erforderlich, wie zum Beispiel das Senden von Broadcasts in einem Chatserver oder das Senden von E-Mails in einem Webserver. Wenn man diese Funktionen direkt ausführt, wird der aktuelle Prozess blockiert, was zu einer Verlangsamung der Serverantwort führt.

Swoole bietet die Funktion zur asynchronen Taskverarbeitung an, mit der man eine asynchrone Aufgabe in den TaskWorker-Prozesspool einreichen und ausführen kann, ohne die Verarbeitungsgeschwindigkeit der aktuellen Anforderung zu beeinträchtigen.

## Programmcode

Basierend auf dem ersten TCP-Server muss man nur noch die beiden Ereignis-Rückruffunktionen [onTask](/server/events?id=ontask) und [onFinish](/server/events?id=onfinish) hinzufügen. Außerdem muss die Anzahl der Task-Prozesse festgelegt werden, die je nach Dauer der Aufgabe und der Arbeitsbelastung angemessen konfiguriert werden kann.

Bitte führen Sie den folgenden Code in task.php aus.

```php
$serv = new Swoole\Server('127.0.0.1', 9501);

// Legt die Anzahl der Arbeitsprozesse für asynchrone Aufgaben fest.
$serv->set([
    'task_worker_num' => 4
]);

// Diese Rückruffunktion wird im Worker-Prozess ausgeführt.
$serv->on('Receive', function($serv, $fd, $reactor_id, $data) {
    // Asynchrone Aufgabe einreichen
    $task_id = $serv->task($data);
    echo "Asynchrone Aufgabe abgegeben: id={$task_id}\n";
});

// Behandelt asynchrone Aufgaben (diese Rückruffunktion wird im Task-Prozess ausgeführt).
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    echo "Neue asynchrone Aufgabe[id={$task_id}]".PHP_EOL;
    // 返回任务执行的结果
    $serv->finish("{$data} -> OK");
});

// Behandelt das Ergebnis einer asynchronen Aufgabe (diese Rückruffunktion wird im Worker-Prozess ausgeführt).
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "Asynchrone Aufgabe[{$task_id}] beendet: {$data}".PHP_EOL;
});

$serv->start();
```

Nachdem `$serv->task()` aufgerufen wurde, kehrt das Programm sofort zurück und führt den Code weiter aus. Die onTask-Rückruffunktion wird asynchron im Task-Prozesspool ausgeführt. Nach der Abschluss der Ausführung wird mit `$serv->finish()` das Ergebnis zurückgegeben.

!> Das Finish-Operieren ist optional und kann auch ohne jegliches Ergebnis durchgeführt werden. Wenn in der onTask-Ereignis ein Ergebnis durch `return` zurückgegeben wird, entspricht dies dem Aufrufen der `Swoole\Server::finish()`-Operation.
