# Einfache Beispiel

Die meisten Funktionen von `Swoole` können nur für die `cli`-Befehlskommandosphäre verwendet werden. Bitte stellen Sie zuerst eine `Linux Shell` Umgebung bereit. Sie können Code mit `Vim`, `Emacs`, `PhpStorm` oder anderen Editoren schreiben und den Programm über den folgenden Befehl in der Kommandozeile ausführen.

```shell
php /path/to/your_file.php
```

Nachdem der `Swoole` Serverprogramm erfolgreich ausgeführt wurde, wird auf dem Bildschirm keine Ausgabe erscheinen, wenn Ihr Code keine `echo` Anweisungen enthält, aber tatsächlich lauscht die Basis bereits einem Netzwerkport und wartet darauf, dass ein Client eine Verbindung einleitet. Sie können entsprechende Client-Tools und Programme verwenden, um den Server zu verbinden und Tests durchzuführen.

#### Prozessmanagement

Standardmäßig kann der `Swoole` Service nach dem Start über das Startfenster mit `CTRL+C` beendet werden, aber wenn das Fenster geschlossen wird, gibt es ein Problem. Der Service muss im Hintergrund gestartet werden, siehe [Daemonisierung](/server/setting?id=daemonize).

!> Die meisten Beispiele im einfachen Beispiel folgen einem asynchronen Programmierstil, und die gleiche Funktionalität kann auch mit einem Coroutine-Stil erreicht werden. Siehe [Server (Coroutine-Stil)](coroutine/server.md).

!> Die meisten von `Swoole` bereitgestellten Module können nur für die `cli`-Befehlskommandozeile verwendet werden. Derzeit ist nur der [Synchron blockierende Client](/client) für die Umgebung von `PHP-FPM` verfügbar.
